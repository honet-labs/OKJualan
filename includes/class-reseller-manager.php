<?php
if (!defined('ABSPATH')) { exit; }

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
}
