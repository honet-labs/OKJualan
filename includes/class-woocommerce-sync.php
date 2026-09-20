<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('OKJ_WC_Sync')) {

class OKJ_WC_Sync {

    /**
     * Check if WooCommerce is installed and active
     */
    public static function is_active() {
        return class_exists('WooCommerce');
    }

    private static $initialized = false;
    private static $syncing_orders = [];

    /**
     * Initialize WooCommerce hooks and order listeners
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        // Listen to completed/processing WooCommerce orders to record customers in OKJualan
        add_action('woocommerce_order_status_completed', [__CLASS__, 'on_order_completed'], 10, 1);
        add_action('woocommerce_order_status_processing', [__CLASS__, 'on_order_completed'], 10, 1);

        // Listen to order creation & status change to sync into OKJualan List Transaksi (pos_transactions)
        add_action('woocommerce_checkout_order_processed', [__CLASS__, 'on_wc_order_created'], 10, 1);
        add_action('woocommerce_new_order', [__CLASS__, 'on_wc_order_created'], 10, 1);
        add_action('woocommerce_order_status_changed', [__CLASS__, 'on_order_status_changed'], 10, 3);

        // Register SumoPod QRIS Payment Gateway into WooCommerce
        add_filter('woocommerce_payment_gateways', [__CLASS__, 'register_payment_gateway']);
    }

    /**
     * Register SumoPod QRIS Gateway class into WooCommerce
     */
    public static function register_payment_gateway($gateways) {
        $gateway_file = dirname(__FILE__) . '/class-wc-gateway-sumopod.php';
        if (file_exists($gateway_file)) {
            require_once $gateway_file;
            if (class_exists('OKJ_WC_Gateway_SumoPod') && !in_array('OKJ_WC_Gateway_SumoPod', $gateways, true)) {
                $gateways[] = 'OKJ_WC_Gateway_SumoPod';
            }
        }
        return $gateways;
    }

    /**
     * Sync single OKJualan product price to WooCommerce product
     */
    public static function sync_product($price_id) {
        if (!self::is_active()) {
            return new WP_Error('wc_inactive', 'WooCommerce tidak aktif pada website ini.');
        }

        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . OKJ_DB::get_table('product_prices') . " WHERE id = %s",
            $price_id
        ), ARRAY_A);

        if (!$row) {
            return new WP_Error('not_found', 'Data master harga produk tidak ditemukan.');
        }

        $wc_product_id = !empty($row['wc_product_id']) ? (int)$row['wc_product_id'] : 0;
        $is_new = false;
        $product = null;

        // Try getting existing WC Product by stored ID
        if ($wc_product_id > 0) {
            $product = wc_get_product($wc_product_id);
        }

        // Fallback: search by meta key _okj_price_id
        if (!$product) {
            $existing_posts = get_posts([
                'post_type'      => 'product',
                'post_status'    => 'any',
                'meta_key'       => '_okj_price_id',
                'meta_value'     => $price_id,
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ]);

            if (!empty($existing_posts[0])) {
                $product = wc_get_product($existing_posts[0]);
                $wc_product_id = $existing_posts[0];
            }
        }

        if (!$product) {
            $product = new WC_Product_Simple();
            $is_new = true;
        }

        // Set Product Properties
        $product->set_name(sanitize_text_field($row['name']));
        $product->set_regular_price((string)$row['sale_price']);
        $product->set_price((string)$row['sale_price']);
        
        $desc = !empty($row['description']) ? wp_kses_post($row['description']) : '';
        $product->set_description($desc);

        $short_desc = !empty($row['duration_days']) ? "Durasi Layanan: " . (int)$row['duration_days'] . " Hari" : "";
        if (!empty($row['notes'])) {
            $short_desc .= (!empty($short_desc) ? " | " : "") . esc_html(wp_strip_all_tags($row['notes']));
        }
        $product->set_short_description($short_desc);

        // Virtual Product (Services / Digital subscriptions - no shipping required)
        $product->set_virtual(true);
        $product->set_status('publish');
        $product->set_catalog_visibility('visible');

        // Set SKU
        $sku = 'OKJ-' . strtoupper(substr(md5($price_id), 0, 8));
        try {
            $product->set_sku($sku);
        } catch (Exception $e) {
            // Sku may conflict if duplicate, proceed without throwing
        }

        // Handle Category
        if (!empty($row['category'])) {
            $cat_name = trim($row['category']);
            $term = term_exists($cat_name, 'product_cat');
            if (!$term) {
                $term = wp_insert_term($cat_name, 'product_cat');
            }
            if (!is_wp_error($term) && !empty($term['term_id'])) {
                $product->set_category_ids([(int)$term['term_id']]);
            }
        }

        // Handle Tags
        $tag_names = [];
        if (!empty($row['tags'])) {
            $tags_raw = array_map('trim', explode(',', $row['tags']));
            foreach ($tags_raw as $t) {
                if ($t !== '') {
                    $tag_names[] = $t;
                }
            }
        }
        if (!empty($row['category']) && !in_array($row['category'], $tag_names)) {
            $tag_names[] = $row['category'];
        }

        // Save WC Product
        $new_wc_id = $product->save();

        if (!$new_wc_id) {
            return new WP_Error('save_failed', 'Gagal menyimpan produk ke WooCommerce.');
        }

        // Set Tag taxonomy terms
        if (!empty($tag_names)) {
            wp_set_object_terms($new_wc_id, $tag_names, 'product_tag');
        }

        // Update post meta relationship
        update_post_meta($new_wc_id, '_okj_price_id', $price_id);
        update_post_meta($new_wc_id, '_okj_duration_days', (int)$row['duration_days']);
        update_post_meta($new_wc_id, '_okj_synced_at', current_time('mysql'));

        // Update OKJualan database record
        $wpdb->update(
            OKJ_DB::get_table('product_prices'),
            [
                'wc_product_id' => $new_wc_id,
                'sync_to_wc'    => 1,
                'updated_at'    => current_time('mysql'),
            ],
            ['id' => $price_id]
        );

        OKJ_Reseller_Manager::log(
            $is_new ? 'wc_product_create' : 'wc_product_update',
            'product_price',
            $price_id,
            ($is_new ? 'Dibuat produk baru di WooCommerce: ' : 'Diperbarui produk WooCommerce: ') . $row['name'] . " (ID #{$new_wc_id})"
        );

        return [
            'success'       => true,
            'is_new'        => $is_new,
            'wc_product_id' => $new_wc_id,
            'name'          => $row['name'],
            'price'         => $row['sale_price'],
            'edit_url'      => admin_url('post.php?post=' . $new_wc_id . '&action=edit'),
            'view_url'      => get_permalink($new_wc_id),
        ];
    }

    /**
     * Bulk Sync All Product Prices to WooCommerce
     */
    public static function sync_all_products() {
        if (!self::is_active()) {
            return new WP_Error('wc_inactive', 'WooCommerce belum terpasang atau tidak aktif di website ini.');
        }

        global $wpdb;
        $rows = $wpdb->get_results("SELECT id, name FROM " . OKJ_DB::get_table('product_prices') . " ORDER BY name ASC", ARRAY_A);

        if (empty($rows)) {
            return [
                'total'   => 0,
                'synced'  => 0,
                'created' => 0,
                'updated' => 0,
                'errors'  => [],
            ];
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $r) {
            $res = self::sync_product($r['id']);
            if (is_wp_error($res)) {
                $errors[] = $r['name'] . ': ' . $res->get_error_message();
            } else {
                if ($res['is_new']) {
                    $created++;
                } else {
                    $updated++;
                }
            }
        }

        return [
            'total'   => count($rows),
            'synced'  => ($created + $updated),
            'created' => $created,
            'updated' => $updated,
            'errors'  => $errors,
        ];
    }

    /**
     * Delete or Trash WooCommerce Product when OKJualan master price is deleted
     */
    public static function delete_synced_product($price_id) {
        if (!self::is_active()) return;

        global $wpdb;
        $existing_wc_id = $wpdb->get_var($wpdb->prepare(
            "SELECT wc_product_id FROM " . OKJ_DB::get_table('product_prices') . " WHERE id = %s",
            $price_id
        ));

        if ($existing_wc_id && get_post_type($existing_wc_id) === 'product') {
            wp_trash_post($existing_wc_id);
            OKJ_Reseller_Manager::log('wc_product_trash', 'product_price', $price_id, "WooCommerce Product ID #{$existing_wc_id} dipindahkan ke Trash.");
        }
    }

    /**
     * Get existing customer or create new customer in OKJualan from WC Order
     */
    public static function get_or_create_customer($order) {
        if (!$order) return null;

        global $wpdb;
        $t_customers = OKJ_DB::get_table('customers');

        $billing_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
        if (empty($billing_name)) {
            $billing_name = $order->get_formatted_billing_full_name() ?: ('WC Customer #' . $order->get_id());
        }
        $billing_email = sanitize_email($order->get_billing_email());
        $billing_phone = sanitize_text_field($order->get_billing_phone());

        $customer_id = '';

        if (!empty($billing_email)) {
            $existing_cust = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$t_customers} WHERE email = %s LIMIT 1", $billing_email), ARRAY_A);
            if ($existing_cust) {
                $customer_id = $existing_cust['id'];
            }
        }

        if (empty($customer_id) && !empty($billing_phone)) {
            $existing_cust = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$t_customers} WHERE phone = %s OR whatsapp = %s LIMIT 1", $billing_phone, $billing_phone), ARRAY_A);
            if ($existing_cust) {
                $customer_id = $existing_cust['id'];
            }
        }

        // If customer does not exist in OKJualan, automatically create them
        if (empty($customer_id)) {
            $customer_id = wp_generate_uuid4();
            $wpdb->insert($t_customers, [
                'id'         => $customer_id,
                'name'       => $billing_name,
                'email'      => $billing_email,
                'phone'      => $billing_phone,
                'telegram'   => '',
                'whatsapp'   => $billing_phone,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
                'updated_by' => 0,
            ]);
            OKJ_Reseller_Manager::log('create_customer_wc', 'customer', $customer_id, "Customer otomatis dibuat dari pesanan WooCommerce #{$order->get_id()}: {$billing_name}");
        }

        return [
            'id'    => $customer_id,
            'name'  => $billing_name,
            'email' => $billing_email,
            'phone' => $billing_phone,
        ];
    }

    /**
     * Synchronize a WooCommerce order to OKJualan POS transactions table
     */
    public static function sync_wc_order_to_pos_transaction($order_id, $override_tx_no = null) {
        if (!$order_id) return null;
        $order = is_a($order_id, 'WC_Order') ? $order_id : wc_get_order($order_id);
        if (!$order) return null;

        $order_id = $order->get_id();
        global $wpdb;
        $t_transactions = OKJ_DB::get_table('pos_transactions');
        $t_pos_items    = OKJ_DB::get_table('pos_transaction_items');

        // Self-healing database check
        if ($wpdb->get_var("SHOW TABLES LIKE '{$t_transactions}'") !== $t_transactions) {
            OKJ_DB::install();
        }

        // Determine Transaction Number & External Gateway Reference ID
        $sumopod_id = $order->get_meta('_okj_sumopod_order_id');
        if (!empty($override_tx_no)) {
            $sumopod_id = sanitize_text_field($override_tx_no);
            $order->update_meta_data('_okj_sumopod_order_id', $sumopod_id);
            if (!doing_action('woocommerce_new_order') && !doing_action('woocommerce_checkout_order_processed')) {
                $order->save();
            }
        }

        $transaction_no = 'WC-' . $order_id;
        $reference_no   = !empty($sumopod_id) ? $sumopod_id : '';

        // Map status
        $wc_status = $order->get_status();
        $payment_status = 'pending';
        if (in_array($wc_status, ['processing', 'completed'])) {
            $payment_status = 'paid';
        } elseif (in_array($wc_status, ['cancelled', 'refunded'])) {
            $payment_status = 'cancelled';
        } elseif ($wc_status === 'failed') {
            $payment_status = 'failed';
        } elseif ($wc_status === 'on-hold') {
            $payment_status = 'pending';
        }

        // Map payment method
        $wc_method = strtolower((string)$order->get_payment_method());
        $payment_method = 'sumopod';
        if (strpos($wc_method, 'sumopod') !== false || strpos($wc_method, 'qris') !== false) {
            $payment_method = 'sumopod';
        } elseif (in_array($wc_method, ['cod', 'cash', 'tunai'])) {
            $payment_method = 'cash';
        } elseif (in_array($wc_method, ['bacs', 'bank_transfer']) || strpos($wc_method, 'transfer') !== false || strpos($wc_method, 'bank') !== false) {
            $payment_method = 'transfer';
        } elseif (strpos($wc_method, 'midtrans') !== false) {
            $payment_method = 'midtrans';
        } elseif (strpos($wc_method, 'tripay') !== false) {
            $payment_method = 'tripay';
        } elseif (strpos($wc_method, 'xendit') !== false) {
            $payment_method = 'xendit';
        } elseif (!empty($wc_method)) {
            $payment_method = sanitize_text_field($wc_method);
        }

        // Customer details
        $cust_data = self::get_or_create_customer($order);
        $customer_id = $cust_data['id'] ?? null;
        $customer_name = $cust_data['name'] ?? ('Pelanggan #' . $order_id);

        // Amounts
        $total = (int)round((float)$order->get_total());
        $discount = (int)round((float)$order->get_discount_total());
        $tax = (int)round((float)$order->get_total_tax());
        $subtotal = $total + $discount - $tax;
        if ($subtotal < 0) $subtotal = $total;

        $notes = 'WooCommerce Order #' . $order_id;
        $customer_note = $order->get_customer_note();
        if (!empty($customer_note)) {
            $notes .= ' | Catatan: ' . $customer_note;
        }

        // Check if existing POS transaction exists
        $existing_tx_id = $order->get_meta('_okj_pos_transaction_id');
        $existing_tx = null;

        if (!empty($existing_tx_id)) {
            $existing_tx = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t_transactions} WHERE id = %s LIMIT 1", $existing_tx_id), ARRAY_A);
        }

        if (!$existing_tx && !empty($reference_no)) {
            $existing_tx = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t_transactions} WHERE reference_no = %s OR transaction_no = %s LIMIT 1", $reference_no, $reference_no), ARRAY_A);
        }

        if (!$existing_tx) {
            $existing_tx = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t_transactions} WHERE transaction_no = %s LIMIT 1", $transaction_no), ARRAY_A);
        }

        if (!$existing_tx) {
            $existing_tx = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t_transactions} WHERE notes LIKE %s LIMIT 1", 'WooCommerce Order #' . $order_id . '%'), ARRAY_A);
        }

        $created_time = current_time('mysql');
        if ($order->get_date_created()) {
            try {
                $wc_dt = clone $order->get_date_created();
                $wc_dt->setTimezone(wp_timezone());
                $created_time = $wc_dt->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                $created_time = $order->get_date_created()->date('Y-m-d H:i:s');
            }
        }

        if ($existing_tx) {
            $tx_id = $existing_tx['id'];
            $wpdb->update($t_transactions, [
                'transaction_no' => $transaction_no,
                'reference_no'   => $reference_no,
                'customer_id'    => $customer_id,
                'customer_name'  => $customer_name,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'payment_method' => $payment_method,
                'payment_status' => $payment_status,
                'notes'          => $notes,
                'updated_at'     => current_time('mysql'),
            ], ['id' => $tx_id]);
        } else {
            $tx_id = wp_generate_uuid4();
            $wpdb->insert($t_transactions, [
                'id'             => $tx_id,
                'transaction_no' => $transaction_no,
                'reference_no'   => $reference_no,
                'customer_id'    => $customer_id,
                'customer_name'  => $customer_name,
                'seller_id'      => null,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'tax'            => $tax,
                'total'          => $total,
                'payment_method' => $payment_method,
                'payment_status' => $payment_status,
                'notes'          => $notes,
                'created_at'     => $created_time,
                'updated_at'     => current_time('mysql'),
                'updated_by'     => 0,
            ]);
        }

        // Save linkage on WooCommerce Order safely without triggering recursive hooks
        if ($order->get_meta('_okj_pos_transaction_id') !== $tx_id) {
            $order->update_meta_data('_okj_pos_transaction_id', $tx_id);
            if (function_exists('update_post_meta')) {
                update_post_meta($order_id, '_okj_pos_transaction_id', $tx_id);
            }
            if (!doing_action('woocommerce_new_order') && !doing_action('woocommerce_checkout_order_processed')) {
                $order->save();
            }
        }

        // Sync items if not yet present
        $has_items = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$t_pos_items} WHERE transaction_id = %s", $tx_id));
        if ($has_items === 0) {
            foreach ($order->get_items() as $item) {
                $wc_prod_id = $item->get_product_id();
                $okj_price_id = get_post_meta($wc_prod_id, '_okj_price_id', true) ?: (string)$wc_prod_id;
                $duration = (int)get_post_meta($wc_prod_id, '_okj_duration_days', true);
                if ($duration <= 0) {
                    $price_row = $wpdb->get_row($wpdb->prepare(
                        "SELECT id, duration_days FROM " . OKJ_DB::get_table('product_prices') . " WHERE wc_product_id = %d OR id = %s OR name = %s LIMIT 1",
                        $wc_prod_id, $okj_price_id, $item->get_name()
                    ), ARRAY_A);
                    if ($price_row && !empty($price_row['duration_days'])) {
                        $duration = (int)$price_row['duration_days'];
                        if (empty($okj_price_id)) $okj_price_id = $price_row['id'];
                    }
                }
                $item_price = (int)round((float)$order->get_item_total($item, false));
                $item_subtotal = (int)round((float)$order->get_item_subtotal($item, false));
                $qty = (int)$item->get_quantity();

                $wpdb->insert($t_pos_items, [
                    'id'            => wp_generate_uuid4(),
                    'transaction_id'=> $tx_id,
                    'product_id'    => $okj_price_id,
                    'product_name'  => $item->get_name(),
                    'price'         => $item_price,
                    'qty'           => $qty,
                    'duration_days' => $duration,
                    'subtotal'      => $item_subtotal,
                    'created_at'    => $created_time,
                ]);
            }
        }

        // Auto-sync to Active Products if order is paid or completed
        if ($order->is_paid() || in_array($wc_status, ['processing', 'completed', 'paid'], true)) {
            if (class_exists('OKJ_Reseller_Manager')) {
                OKJ_Reseller_Manager::sync_transaction_to_active_products($tx_id);
            }
        }

        return $tx_id;
    }

    /**
     * Batch sync recent WooCommerce orders to ensure no orders are missing in List Transaksi
     */
    public static function sync_recent_wc_orders($limit = 30) {
        if (!self::is_active() || !function_exists('wc_get_orders')) {
            return;
        }

        $orders = wc_get_orders([
            'limit'   => $limit,
            'orderby' => 'date',
            'order'   => 'DESC',
        ]);

        if (!empty($orders)) {
            foreach ($orders as $order) {
                $tx_id = self::sync_wc_order_to_pos_transaction($order->get_id());
                if ($tx_id && class_exists('OKJ_Reseller_Manager') && in_array($order->get_status(), ['processing', 'completed', 'paid'], true)) {
                    OKJ_Reseller_Manager::sync_transaction_to_active_products($tx_id);
                }
            }
        }
    }

    /**
     * Listener for newly created WooCommerce orders
     */
    public static function on_wc_order_created($order_id) {
        if (!$order_id) return;
        if (!empty(self::$syncing_orders[$order_id])) {
            return;
        }
        self::$syncing_orders[$order_id] = true;
        try {
            self::sync_wc_order_to_pos_transaction($order_id);
        } catch (\Throwable $e) {
            error_log('[OKJualan WC Order Sync Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        } finally {
            unset(self::$syncing_orders[$order_id]);
        }
    }

    /**
     * Listener for WooCommerce order status changes
     */
    public static function on_order_status_changed($order_id, $old_status, $new_status) {
        if (!$order_id) return;
        try {
            $tx_id = self::sync_wc_order_to_pos_transaction($order_id);
            if ($tx_id && class_exists('OKJ_Reseller_Manager')) {
                OKJ_Reseller_Manager::sync_transaction_to_active_products($tx_id);
            }
            if (in_array($new_status, ['processing', 'completed'])) {
                self::on_order_completed($order_id);
            }
        } catch (\Throwable $e) {
            error_log('[OKJualan WC Status Change Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    /**
     * Auto capture customer and record order when WooCommerce order completes
     */
    public static function on_order_completed($order_id) {
        if (!$order_id) return;
        $order = wc_get_order($order_id);
        if (!$order) return;

        // Ensure transaction is synced and marked paid
        $tx_id = self::sync_wc_order_to_pos_transaction($order_id);
        if ($tx_id && class_exists('OKJ_Reseller_Manager')) {
            OKJ_Reseller_Manager::sync_transaction_to_active_products($tx_id);
        }

        // Check if already processed
        if (get_post_meta($order_id, '_okj_order_captured', true)) {
            return;
        }

        self::get_or_create_customer($order);

        // Check if any purchased item is an OKJualan synced product
        $synced_items = 0;
        foreach ($order->get_items() as $item) {
            $wc_prod_id = $item->get_product_id();
            $okj_price_id = get_post_meta($wc_prod_id, '_okj_price_id', true);

            if (!empty($okj_price_id)) {
                $synced_items++;
            }
        }

        $billing_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
        if ($synced_items > 0) {
            OKJ_Reseller_Manager::log('wc_order_processed', 'order', (string)$order_id, "Pesanan WooCommerce #{$order_id} ({$billing_name}) berhasil disinkronkan ke OKJualan.");
        }

        update_post_meta($order_id, '_okj_order_captured', current_time('mysql'));
    }
}
}
