<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('OKJ_Reseller_Manager')) {

class OKJ_Reseller_Manager {
    public static function log($action, $entity, $entity_id, $message, $meta = null) {
        global $wpdb;
        $ip = '';
        $candidates = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($candidates as $k) {
            if (!empty($_SERVER[$k])) {
                $ip = sanitize_text_field($_SERVER[$k]);
                break;
            }
        }

        $user = wp_get_current_user();
        $user_id = $user && $user->ID ? $user->ID : 0;
        $user_login = $user && $user->user_login ? $user->user_login : 'system';

        $wpdb->insert(OKJ_DB::get_table('logs'), [
            'happened_at' => current_time('mysql'),
            'user_id' => $user_id,
            'user_login' => $user_login,
            'action' => sanitize_text_field($action),
            'entity' => sanitize_text_field($entity),
            'entity_id' => sanitize_text_field($entity_id),
            'message' => sanitize_text_field($message),
            'meta' => $meta ? wp_json_encode($meta) : null,
            'ip' => $ip,
        ]);
    }

    public static function sync_reminders($active_row) {
        global $wpdb;
        $settings = get_option('okj_settings_v1', []);
        $offsets = !empty($settings['reminder_offsets']) ? (array)$settings['reminder_offsets'] : [7, 3, 1];

        $active_id = (string)$active_row['id'];
        $customer_id = (string)$active_row['customer_id'];
        $expires_at = (string)$active_row['expires_at'];

        foreach ($offsets as $d) {
            $d = (int)$d;
            if ($d <= 0) continue;

            $reminder_date = wp_date('Y-m-d', strtotime($expires_at . " -{$d} days"));
            $remaining = (int)floor((strtotime($expires_at) - strtotime(wp_date('Y-m-d'))) / DAY_IN_SECONDS);

            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT id, status FROM " . OKJ_DB::get_table('active_reminders') . " WHERE active_product_id = %s AND offset_days = %d LIMIT 1",
                $active_id, $d
            ), ARRAY_A);

            if ($existing) {
                $wpdb->update(OKJ_DB::get_table('active_reminders'), [
                    'customer_id' => $customer_id,
                    'reminder_date' => $reminder_date,
                    'remaining_days' => $remaining,
                    'updated_at' => current_time('mysql'),
                ], ['id' => $existing['id']]);
            } else {
                $wpdb->insert(OKJ_DB::get_table('active_reminders'), [
                    'id' => wp_generate_uuid4(),
                    'active_product_id' => $active_id,
                    'customer_id' => $customer_id,
                    'offset_days' => $d,
                    'reminder_date' => $reminder_date,
                    'remaining_days' => $remaining,
                    'status' => 'pending',
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ]);
            }
        }
    }

    public static function process_daily_cron() {
        global $wpdb;
        $today = wp_date('Y-m-d');
        $reminders = $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, a.product_label, a.start_date, a.duration_days, a.notes, c.name as customer_name, c.email as customer_email, c.phone as customer_phone, c.telegram as customer_telegram, c.whatsapp as customer_whatsapp, a.expires_at, a.price
             FROM " . OKJ_DB::get_table('active_reminders') . " r
             INNER JOIN " . OKJ_DB::get_table('active_products') . " a ON r.active_product_id = a.id
             INNER JOIN " . OKJ_DB::get_table('customers') . " c ON r.customer_id = c.id
             WHERE r.status = 'pending' AND r.reminder_date <= %s",
            $today
        ), ARRAY_A);

        if (empty($reminders)) return;

        $notifier = new OKJ_Notifier();
        $settings = get_option('okj_settings_v1', []);

        foreach ($reminders as $r) {
            $vars = [
                'customer_name' => $r['customer_name'],
                'customer_email' => $r['customer_email'],
                'customer_phone' => $r['customer_phone'],
                'customer_telegram' => $r['customer_telegram'],
                'customer_whatsapp' => $r['customer_whatsapp'],
                'product_label' => $r['product_label'],
                'expires_at' => date_i18n(get_option('date_format'), strtotime($r['expires_at'])),
                'price' => 'Rp ' . number_format_i18n((float)$r['price'], 0),
                'remaining_days' => $r['offset_days'],
                'start_date' => date_i18n(get_option('date_format'), strtotime($r['start_date'])),
                'duration_days' => $r['duration_days'],
                'notes' => $r['notes'] ?: '-',
                'invoice_url' => admin_url('admin-post.php?action=okj_invoice_pdf&id=' . $r['active_product_id']),
                'company_name' => !empty($settings['pdf_company_name']) ? $settings['pdf_company_name'] : get_bloginfo('name'),
                'company_address' => !empty($settings['pdf_company_address']) ? $settings['pdf_company_address'] : '',
                'company_phone' => !empty($settings['pdf_company_phone']) ? $settings['pdf_company_phone'] : '',
                'payment_details' => !empty($settings['pdf_payment_details']) ? $settings['pdf_payment_details'] : '',
            ];

            $sent_channels = [];
            $error_log = [];

            // Email
            if (!empty($settings['smtp_enabled']) && !empty($r['customer_email'])) {
                $sub_tpl = !empty($settings['email_subject']) ? $settings['email_subject'] : '[Reminder] {product_label} akan expired';
                $body_tpl = !empty($settings['email_template']) ? $settings['email_template'] : '';
                $res = $notifier->send_email($r['customer_email'], $sub_tpl, $body_tpl, $vars);
                if ($res['ok']) $sent_channels[] = 'email'; else $error_log[] = 'Email: ' . $res['error'];
            }

            // Telegram
            if (!empty($settings['telegram_enabled']) && !empty($r['customer_telegram'])) {
                $tele_tpl = !empty($settings['telegram_template']) ? $settings['telegram_template'] : '';
                $message = $notifier->render_template($tele_tpl, $vars);
                $res = $notifier->send_telegram($r['customer_telegram'], $message);
                if ($res['ok']) $sent_channels[] = 'telegram'; else $error_log[] = 'Telegram: ' . $res['error'];
            }

            // WhatsApp WAHA
            if (!empty($settings['waha_enabled']) && !empty($r['customer_whatsapp'])) {
                // Select milestone template
                $wa_tpl = '';
                if ($r['offset_days'] == 7) {
                    $wa_tpl = !empty($settings['whatsapp_template_h7']) ? $settings['whatsapp_template_h7'] : '';
                } elseif ($r['offset_days'] == 3) {
                    $wa_tpl = !empty($settings['whatsapp_template_h3']) ? $settings['whatsapp_template_h3'] : '';
                } elseif ($r['offset_days'] == 1) {
                    $wa_tpl = !empty($settings['whatsapp_template_h1']) ? $settings['whatsapp_template_h1'] : '';
                }
                if (!$wa_tpl) {
                    $wa_tpl = !empty($settings['whatsapp_template']) ? $settings['whatsapp_template'] : '';
                }

                $message = $notifier->render_template($wa_tpl, $vars);
                $res = $notifier->send_waha($r['customer_whatsapp'], $message);
                if ($res['ok']) $sent_channels[] = 'whatsapp'; else $error_log[] = 'WhatsApp: ' . $res['error'];
            }

            $now = current_time('mysql');
            if (!empty($sent_channels)) {
                $wpdb->update(OKJ_DB::get_table('active_reminders'), [
                    'status' => 'sent',
                    'sent_via' => implode(',', $sent_channels),
                    'sent_at' => $now,
                    'last_error' => !empty($error_log) ? implode('; ', $error_log) : null,
                    'updated_at' => $now,
                ], ['id' => $r['id']]);

                self::log('send_reminder', 'reminder', $r['id'], "Reminder sent to {$r['customer_name']} via " . implode(',', $sent_channels));
            } else {
                $wpdb->update(OKJ_DB::get_table('active_reminders'), [
                    'last_error' => implode('; ', $error_log),
                    'updated_at' => $now,
                ], ['id' => $r['id']]);

                self::log('send_reminder_fail', 'reminder', $r['id'], "Failed sending reminder to {$r['customer_name']}: " . implode('; ', $error_log));
            }
        }
    }

    /**
     * Synchronize a POS / WooCommerce transaction's purchased items into okj_active_products
     *
     * @param string|array $transaction_id_or_no
     * @return int Number of active products created or updated
     */
    public static function sync_transaction_to_active_products($transaction_id_or_no) {
        global $wpdb;
        $t_trans  = OKJ_DB::get_table('pos_transactions');
        $t_items  = OKJ_DB::get_table('pos_transaction_items');
        $t_active = OKJ_DB::get_table('active_products');
        $t_cust   = OKJ_DB::get_table('customers');
        $t_prices = OKJ_DB::get_table('product_prices');

        if (is_array($transaction_id_or_no)) {
            $tx = $transaction_id_or_no;
        } else {
            $tx = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$t_trans} WHERE id = %s OR transaction_no = %s OR reference_no = %s LIMIT 1",
                $transaction_id_or_no, $transaction_id_or_no, $transaction_id_or_no
            ), ARRAY_A);
        }

        if (!$tx) {
            return 0;
        }

        $tx_id = $tx['id'];
        $transaction_no = $tx['transaction_no'];
        $payment_status = strtolower($tx['payment_status'] ?? 'pending');
        $is_paid = in_array($payment_status, ['paid', 'completed', 'processing'], true);
        $is_cancelled = in_array($payment_status, ['cancelled', 'failed', 'refunded'], true);

        // Fetch transaction items
        $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$t_items} WHERE transaction_id = %s ORDER BY id ASC", $tx_id), ARRAY_A);
        if (empty($items)) {
            return 0;
        }

        // Fetch customer info
        $customer_id = $tx['customer_id'] ?? '';
        $customer_name = !empty($tx['customer_name']) ? $tx['customer_name'] : 'Pelanggan Umum';
        $cust_contact = '';

        if (!empty($customer_id)) {
            $cust = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t_cust} WHERE id = %s LIMIT 1", $customer_id), ARRAY_A);
            if ($cust) {
                if (!empty($cust['name'])) $customer_name = $cust['name'];
                $parts = [];
                if (!empty($cust['phone'])) $parts[] = 'Telp: ' . $cust['phone'];
                if (!empty($cust['whatsapp'])) $parts[] = 'WA: ' . $cust['whatsapp'];
                if (!empty($cust['telegram'])) $parts[] = 'TG: ' . $cust['telegram'];
                if (!empty($cust['email'])) $parts[] = 'Email: ' . $cust['email'];
                $cust_contact = implode(' | ', $parts);
            }
        }

        // Fallback: If customer_id is empty, create or link a default customer
        if (empty($customer_id)) {
            $customer_id = wp_generate_uuid4();
            $wpdb->insert($t_cust, [
                'id'         => $customer_id,
                'name'       => $customer_name,
                'status'     => 'active',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
                'updated_by' => 0,
            ]);
            $wpdb->update($t_trans, ['customer_id' => $customer_id], ['id' => $tx_id]);
        }

        $synced_count = 0;
        $created_date = !empty($tx['created_at']) ? substr($tx['created_at'], 0, 10) : wp_date('Y-m-d');

        foreach ($items as $it) {
            $product_name = $it['product_name'];
            $qty = !empty($it['qty']) ? (int)$it['qty'] : 1;
            $duration = isset($it['duration_days']) ? (int)$it['duration_days'] : 0;
            $item_price = (int)($it['subtotal'] ?? ($it['price'] * $qty));
            $product_id = $it['product_id'] ?? null;

            // If duration is 0, check if catalog price list has duration
            if ($duration <= 0) {
                $price_row = $wpdb->get_row($wpdb->prepare(
                    "SELECT id, duration_days FROM {$t_prices} WHERE id = %s OR wc_product_id = %s OR name = %s LIMIT 1",
                    $product_id, $product_id, $product_name
                ), ARRAY_A);
                if ($price_row && !empty($price_row['duration_days'])) {
                    $duration = (int)$price_row['duration_days'];
                    if (empty($product_id)) $product_id = $price_row['id'];
                    $wpdb->update($t_items, ['duration_days' => $duration], ['id' => $it['id']]);
                }
            }

            // Calculate expiration date
            $start_date = $created_date;
            if ($duration > 0) {
                $expires_at = wp_date('Y-m-d', strtotime($start_date . " +{$duration} days"));
            } else {
                $expires_at = '2099-12-31';
            }

            // Target status
            $target_status = 'pending';
            $target_pay_status = 'pending';
            if ($is_paid) {
                $target_pay_status = 'paid';
                $is_manual = self::requires_manual_fulfillment($product_id, $product_name);
                $target_status = $is_manual ? 'process' : 'active';
            } elseif ($is_cancelled) {
                $target_status = 'cancelled';
                $target_pay_status = 'cancelled';
            }

            // Check if already registered for this transaction and product
            $existing_ap = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$t_active} WHERE (transaction_no = %s OR notes LIKE %s OR notes LIKE %s) AND product_label = %s LIMIT 1",
                $transaction_no,
                '%' . $wpdb->esc_like('ID: ' . $tx_id) . '%',
                '%' . $wpdb->esc_like('(' . $transaction_no . ')') . '%',
                $product_name
            ), ARRAY_A);

            if ($existing_ap) {
                // If it was already active or completed by admin, preserve that status
                if ($is_paid && in_array($existing_ap['status'], ['active', 'completed'], true)) {
                    $target_status = $existing_ap['status'];
                }

                $wpdb->update($t_active, [
                    'transaction_no'   => $transaction_no,
                    'status'           => $target_status,
                    'payment_status'   => $target_pay_status,
                    'price'            => $item_price,
                    'qty'              => $qty,
                    'customer_id'      => $customer_id,
                    'customer_name'    => $customer_name,
                    'customer_contact' => $cust_contact,
                    'updated_at'       => current_time('mysql'),
                ], ['id' => $existing_ap['id']]);

                $saved_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t_active} WHERE id = %s", $existing_ap['id']), ARRAY_A);
                if ($saved_row) {
                    self::sync_reminders($saved_row);
                }
                $synced_count++;
            } else {
                $active_id = wp_generate_uuid4();
                $note_text = "Pembelian via {$transaction_no} (ID: {$tx_id})";

                $wpdb->insert($t_active, [
                    'id'                  => $active_id,
                    'transaction_no'      => $transaction_no,
                    'reseller_product_id' => '',
                    'product_id'          => $product_id,
                    'product_label'       => $product_name,
                    'customer_id'         => $customer_id,
                    'customer_name'       => $customer_name,
                    'customer_contact'    => $cust_contact,
                    'start_date'          => $start_date,
                    'qty'                 => $qty,
                    'duration_days'       => $duration,
                    'expires_at'          => $expires_at,
                    'status'              => $target_status,
                    'price'               => $item_price,
                    'payment_status'      => $target_pay_status,
                    'notes'               => $note_text,
                    'created_at'          => !empty($tx['created_at']) ? $tx['created_at'] : current_time('mysql'),
                    'updated_at'          => current_time('mysql'),
                    'updated_by'          => 0,
                ]);

                $saved_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t_active} WHERE id = %s", $active_id), ARRAY_A);
                if ($saved_row) {
                    self::sync_reminders($saved_row);
                }
                $synced_count++;
            }
        }

        return $synced_count;
    }

    /**
     * Batch auto-sync all paid POS/WooCommerce transactions to active products
     */
    public static function sync_all_paid_transactions_to_active_products($limit = 100) {
        global $wpdb;
        $t_trans = OKJ_DB::get_table('pos_transactions');
        $paid_txs = $wpdb->get_results($wpdb->prepare(
            "SELECT id FROM {$t_trans} WHERE payment_status IN ('paid', 'completed', 'processing') ORDER BY created_at DESC LIMIT %d",
            $limit
        ), ARRAY_A);

        if (empty($paid_txs)) {
            return 0;
        }

        $total_synced = 0;
        foreach ($paid_txs as $row) {
            $total_synced += self::sync_transaction_to_active_products($row['id']);
        }
        return $total_synced;
    }

    /**
     * Check if a product requires manual fulfillment (status 'process') based on configured tags.
     *
     * @param string|int|null $product_id
     * @param string $product_name
     * @return bool
     */
    public static function requires_manual_fulfillment($product_id = null, $product_name = '') {
        $settings = function_exists('get_option') ? get_option('okj_settings_v1', []) : [];
        if (!is_array($settings)) {
            $settings = [];
        }
        $raw_tags = $settings['manual_fulfillment_tags'] ?? 'netflix';
        if (empty(trim($raw_tags))) {
            return false;
        }

        $config_tags = array_filter(array_map('trim', explode(',', strtolower($raw_tags))));
        if (empty($config_tags)) {
            return false;
        }

        // 1. Check Product Name / Title
        $name_lower = strtolower($product_name ?? '');
        foreach ($config_tags as $ct) {
            if ($ct !== '' && strpos($name_lower, $ct) !== false) {
                return true;
            }
        }

        // 2. Check Database product_prices / reseller_products tags if product_id is provided
        if (!empty($product_id)) {
            global $wpdb;
            $t_prices = OKJ_DB::get_table('product_prices');
            $t_reseller = OKJ_DB::get_table('reseller_products');

            $row_tags = $wpdb->get_var($wpdb->prepare(
                "SELECT tags FROM {$t_prices} WHERE id = %s OR wc_product_id = %s LIMIT 1",
                $product_id, $product_id
            ));
            if ($row_tags) {
                $item_tags = array_filter(array_map('trim', explode(',', strtolower($row_tags))));
                foreach ($config_tags as $ct) {
                    if (in_array($ct, $item_tags, true)) {
                        return true;
                    }
                }
            }

            // Check reseller products
            $reseller_tags = $wpdb->get_var($wpdb->prepare(
                "SELECT tags FROM {$t_reseller} WHERE id = %s LIMIT 1",
                $product_id
            ));
            if ($reseller_tags) {
                $item_tags = array_filter(array_map('trim', explode(',', strtolower($reseller_tags))));
                foreach ($config_tags as $ct) {
                    if (in_array($ct, $item_tags, true)) {
                        return true;
                    }
                }
            }

            // 3. Check WooCommerce product_tag and product_cat taxonomy if WC is active
            $wc_check_id = 0;
            if (is_numeric($product_id)) {
                $wc_check_id = (int)$product_id;
            } elseif (!empty($product_id) && isset($t_prices)) {
                $linked_wc = $wpdb->get_var($wpdb->prepare("SELECT wc_product_id FROM {$t_prices} WHERE id = %s", $product_id));
                if (!empty($linked_wc)) {
                    $wc_check_id = (int)$linked_wc;
                }
            }

            if ($wc_check_id > 0 && function_exists('wp_get_post_terms')) {
                // Product Tags
                $wc_tags = wp_get_post_terms($wc_check_id, 'product_tag', ['fields' => 'names']);
                if (!is_wp_error($wc_tags) && !empty($wc_tags)) {
                    foreach ($wc_tags as $tag_name) {
                        $tag_name_lower = strtolower(trim($tag_name));
                        foreach ($config_tags as $ct) {
                            if ($ct === $tag_name_lower || strpos($tag_name_lower, $ct) !== false) {
                                return true;
                            }
                        }
                    }
                }

                // Product Categories
                $wc_cats = wp_get_post_terms($wc_check_id, 'product_cat', ['fields' => 'names']);
                if (!is_wp_error($wc_cats) && !empty($wc_cats)) {
                    foreach ($wc_cats as $cat_name) {
                        $cat_name_lower = strtolower(trim($cat_name));
                        foreach ($config_tags as $ct) {
                            if ($ct === $cat_name_lower || strpos($cat_name_lower, $ct) !== false) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }
}
}

