<?php
if (!defined('ABSPATH')) { exit; }

class OKJ_WC_Sync {

    /**
     * Check if WooCommerce is installed and active
     */
    public static function is_active() {
        return class_exists('WooCommerce');
    }

    /**
     * Initialize WooCommerce hooks and order listeners
     */
    public static function init() {
        if (!self::is_active()) {
            return;
        }

        // Listen to completed/processing WooCommerce orders to record customers in OKJualin
        add_action('woocommerce_order_status_completed', [__CLASS__, 'on_order_completed'], 10, 1);
        add_action('woocommerce_order_status_processing', [__CLASS__, 'on_order_completed'], 10, 1);
    }

    /**
     * Sync single OKJualin product price to WooCommerce product
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

        // Update OKJualin database record
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
     * Delete or Trash WooCommerce Product when OKJualin master price is deleted
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
     * Auto capture customer and record order when WooCommerce order completes
     */
    public static function on_order_completed($order_id) {
        if (!$order_id) return;
        $order = wc_get_order($order_id);
        if (!$order) return;

        // Check if already processed to prevent duplicates
        if (get_post_meta($order_id, '_okj_order_captured', true)) {
            return;
        }

        global $wpdb;
        $t_customers = OKJ_DB::get_table('customers');

        // Customer details from WC Order
        $billing_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
        if (empty($billing_name)) {
            $billing_name = $order->get_formatted_billing_full_name() ?: 'WC Customer #' . $order_id;
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

        // If customer does not exist in OKJualin, automatically create them
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
            OKJ_Reseller_Manager::log('create_customer_wc', 'customer', $customer_id, "Customer otomatis dibuat dari pesanan WooCommerce #{$order_id}: {$billing_name}");
        }

        // Check if any purchased item is an OKJualin synced product
        $synced_items = 0;
        foreach ($order->get_items() as $item) {
            $wc_prod_id = $item->get_product_id();
            $okj_price_id = get_post_meta($wc_prod_id, '_okj_price_id', true);

            if (!empty($okj_price_id)) {
                $synced_items++;
            }
        }

        if ($synced_items > 0) {
            OKJ_Reseller_Manager::log('wc_order_processed', 'order', (string)$order_id, "Pesanan WooCommerce #{$order_id} ({$billing_name}) berhasil disinkronkan ke OKJualin.");
        }

        update_post_meta($order_id, '_okj_order_captured', current_time('mysql'));
    }
}
