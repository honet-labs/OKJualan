<?php
if (!defined('ABSPATH')) { exit; }

class OKJ_Payment_Gateway {

    /**
     * Get Payment Gateway settings
     */
    public static function get_settings() {
        $defaults = [
            // SumoPod Gateway
            'sumopod_enabled'       => 0,
            'sumopod_mode'          => 'sandbox', // sandbox | production
            'sumopod_api_key'       => '',
            'sumopod_webhook_secret'=> '',        // whsec_...
            'sumopod_webhook_token' => '',        // whtok_...
            'sumopod_default_method'=> 'QRIS',

            // Midtrans
            'midtrans_enabled'      => 0,
            'midtrans_mode'         => 'sandbox',
            'midtrans_server_key'   => '',
            'midtrans_client_key'   => '',

            // Tripay
            'tripay_enabled'        => 0,
            'tripay_mode'           => 'sandbox',
            'tripay_api_key'        => '',
            'tripay_private_key'    => '',
            'tripay_merchant_code'  => '',

            // Manual & QRIS
            'manual_transfer_enabled' => 1,
            'manual_bank_name'        => 'Bank BCA',
            'manual_account_number'   => '',
            'manual_account_holder'   => '',
            'qris_enabled'            => 1,
            'qris_image_url'          => '',
        ];

        $opt = get_option('okj_settings_v1', []);
        return array_merge($defaults, is_array($opt) ? $opt : []);
    }

    /**
     * Get webhook URL for payment gateways
     */
    public static function get_webhook_url() {
        return home_url('/?okj_webhook=payment');
    }

    /**
     * Create payment request via SumoPod API
     *
     * @param array $order [
     *    'order_id' => 'TR-260920-0001',
     *    'amount' => 50000,
     *    'customer_name' => 'Nama Customer',
     *    'customer_email' => 'cust@email.com',
     *    'customer_phone' => '08123456789',
     *    'items' => [ ['name' => 'Produk', 'price' => 50000, 'qty' => 1] ]
     * ]
     * @return array ['ok' => bool, 'payment_url' => string, 'payment_id' => string, 'error' => string]
     */
    public static function create_sumopod_payment($order) {
        $settings = self::get_settings();
        if (empty($settings['sumopod_enabled'])) {
            return ['ok' => false, 'error' => 'SumoPod Payment Gateway tidak aktif.'];
        }

        $api_key = trim((string)$settings['sumopod_api_key']);
        if (!$api_key) {
            return ['ok' => false, 'error' => 'SumoPod API Key belum dikonfigurasi.'];
        }

        $is_sandbox = ($settings['sumopod_mode'] ?? 'sandbox') === 'sandbox';
        $endpoint = $is_sandbox 
            ? 'https://api-pay-sandbox.sumopod.com/api/v1/payments'
            : 'https://api-pay.sumopod.com/api/v1/payments';

        $order_id = sanitize_text_field($order['order_id']);
        $amount = (int)round((float)$order['amount']);
        $success_url = home_url('/?okj_order=1&track_order=' . rawurlencode($order_id) . '&paid=1');
        $cancel_url = home_url('/?okj_order=1&track_order=' . rawurlencode($order_id) . '&cancelled=1');

        $payload = [
            'order_id'                 => $order_id,
            'amount'                   => $amount,
            'currency'                 => 'IDR',
            'expires_in_hours'         => 24,
            'success_return_url'       => $success_url,
            'cancel_return_url'        => $cancel_url,
            'payment_method_type_code' => !empty($settings['sumopod_default_method']) ? $settings['sumopod_default_method'] : 'QRIS',
        ];

        $resp = wp_remote_post($endpoint, [
            'timeout' => 25,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'X-Api-Key'    => $api_key,
            ],
            'body' => wp_json_encode($payload),
        ]);

        if (is_wp_error($resp)) {
            return ['ok' => false, 'error' => $resp->get_error_message()];
        }

        $code = (int)wp_remote_retrieve_response_code($resp);
        $body = wp_remote_retrieve_body($resp);
        $data = json_decode($body, true);

        if ($code >= 200 && $code < 300 && !empty($data['payment_link_url'])) {
            return [
                'ok'               => true,
                'gateway'          => 'sumopod',
                'payment_id'       => $data['payment_id'] ?? '',
                'payment_link_url' => $data['payment_link_url'],
                'fee'              => $data['fee'] ?? 0,
                'status'           => $data['status'] ?? 'pending',
                'expires_at'       => $data['expires_at'] ?? '',
            ];
        }

        $msg = !empty($data['message']) ? $data['message'] : 'Gagal membuat pembayaran via SumoPod (HTTP ' . $code . ')';
        return ['ok' => false, 'error' => $msg];
    }

    /**
     * Handle incoming webhook requests from payment gateways
     */
    public static function handle_webhook() {
        if (!isset($_GET['okj_webhook']) || $_GET['okj_webhook'] !== 'payment') {
            return;
        }

        $raw_body = file_get_contents('php://input');
        if (empty($raw_body)) {
            status_header(400);
            echo 'Empty payload';
            exit;
        }

        $settings = self::get_settings();

        // 1. Check SumoPod Webhook
        $is_sumopod = isset($_SERVER['HTTP_SVIX_ID']) || isset($_SERVER['HTTP_X_WEBHOOK_TOKEN']);
        if ($is_sumopod) {
            self::process_sumopod_webhook($raw_body, $settings);
            exit;
        }

        // 2. Generic fallback / JSON payload detection
        $event = json_decode($raw_body, true);
        if ($event && isset($event['event_type']) && strpos($event['event_type'], 'payment.') === 0) {
            self::process_sumopod_webhook($raw_body, $settings);
            exit;
        }

        status_header(200);
        echo 'OK';
        exit;
    }

    /**
     * Process SumoPod Webhook with signature & token verification
     */
    private static function process_sumopod_webhook($raw_body, $settings) {
        $secret = trim((string)($settings['sumopod_webhook_secret'] ?? ''));
        $token  = trim((string)($settings['sumopod_webhook_token'] ?? ''));

        $is_valid = false;

        // Verify via Token (Method 2)
        if ($token && isset($_SERVER['HTTP_X_WEBHOOK_TOKEN'])) {
            if (hash_equals($token, $_SERVER['HTTP_X_WEBHOOK_TOKEN'])) {
                $is_valid = true;
            }
        }

        // Verify via Svix Signature (Method 1)
        if (!$is_valid && $secret && isset($_SERVER['HTTP_SVIX_ID'], $_SERVER['HTTP_SVIX_TIMESTAMP'], $_SERVER['HTTP_SVIX_SIGNATURE'])) {
            $is_valid = self::verify_svix_signature(
                $secret,
                $_SERVER['HTTP_SVIX_ID'],
                $_SERVER['HTTP_SVIX_TIMESTAMP'],
                $_SERVER['HTTP_SVIX_SIGNATURE'],
                $raw_body
            );
        }

        // If no secret or token configured in settings, reject for security
        if (!$secret && !$token) {
            status_header(401);
            echo 'Webhook secret or token not configured in OKJualan settings';
            exit;
        }

        if (!$is_valid) {
            status_header(401);
            echo 'Invalid signature or token';
            exit;
        }

        $event = json_decode($raw_body, true);
        if (!$event || empty($event['event_type'])) {
            status_header(400);
            echo 'Invalid JSON event structure';
            exit;
        }

        $event_type = $event['event_type'];
        $data = $event['data'] ?? [];
        $order_id = $data['order_id'] ?? '';

        OKJ_Reseller_Manager::log(
            'sumopod_webhook',
            'pos_transaction',
            $order_id,
            "Received SumoPod Webhook: {$event_type} for order {$order_id}",
            $data
        );

        if ($event_type === 'payment.completed' && !empty($order_id)) {
            self::mark_order_paid($order_id, 'sumopod', $data);
        } elseif ($event_type === 'payment.failed' && !empty($order_id)) {
            self::mark_order_failed($order_id, 'sumopod', $data);
        } elseif ($event_type === 'payment.expired' && !empty($order_id)) {
            self::mark_order_expired($order_id, 'sumopod', $data);
        }

        status_header(200);
        echo 'Webhook verified: ' . esc_html($event_type);
        exit;
    }

    /**
     * Svix Signature Verification using HMAC-SHA256
     */
    private static function verify_svix_signature($secret, $svix_id, $svix_timestamp, $svix_signature, $raw_body) {
        $secret_clean = str_replace('whsec_', '', $secret);
        $secret_bytes = base64_decode($secret_clean, true);
        if ($secret_bytes === false) return false;

        $signed_content = "{$svix_id}.{$svix_timestamp}.{$raw_body}";
        $expected_signature = base64_encode(hash_hmac('sha256', $signed_content, $secret_bytes, true));

        // svix-signature may contain multiple space-separated "v1,<sig>" values
        $signatures = explode(' ', $svix_signature);
        foreach ($signatures as $part) {
            $split = explode(',', $part, 2);
            $sig = count($split) === 2 ? $split[1] : $split[0];
            if (hash_equals($expected_signature, $sig)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Mark an order as paid/completed across POS transactions & active products
     */
    public static function mark_order_paid($transaction_no, $gateway = 'manual', $meta = []) {
        global $wpdb;

        $t_tx = OKJ_DB::get_table('pos_transactions');
        $t_ap = OKJ_DB::get_table('active_products');
        $t_items = OKJ_DB::get_table('pos_transaction_items');

        // Find transaction
        $tx = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$t_tx} WHERE transaction_no = %s OR id = %s LIMIT 1",
            $transaction_no, $transaction_no
        ), ARRAY_A);

        if ($tx) {
            $wpdb->update($t_tx, [
                'payment_status' => 'paid',
                'payment_method' => $gateway,
                'updated_at'     => current_time('mysql'),
            ], ['id' => $tx['id']]);

            // Activate associated active products
            $wpdb->query($wpdb->prepare(
                "UPDATE {$t_ap} SET status = 'active', payment_status = 'paid', updated_at = %s 
                 WHERE notes LIKE %s OR id = %s",
                current_time('mysql'),
                '%' . $wpdb->esc_like($tx['transaction_no']) . '%',
                $tx['id']
            ));

            // Sync reminders for newly active products
            $active_rows = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$t_ap} WHERE notes LIKE %s OR id = %s",
                '%' . $wpdb->esc_like($tx['transaction_no']) . '%',
                $tx['id']
            ), ARRAY_A);

            foreach ($active_rows as $ar) {
                OKJ_Reseller_Manager::sync_reminders($ar);
            }

            // Deduct stock for products sold
            $items = $wpdb->get_results($wpdb->prepare(
                "SELECT product_id, qty FROM {$t_items} WHERE transaction_id = %s",
                $tx['id']
            ), ARRAY_A);

            foreach ($items as $it) {
                if (!empty($it['product_id']) && $it['qty'] > 0) {
                    self::deduct_product_stock($it['product_id'], (int)$it['qty']);
                }
            }

            // Trigger notification
            OKJ_Notifier::notify_payment_completed($tx);

            OKJ_Reseller_Manager::log('payment_completed', 'order', $tx['id'], "Order {$tx['transaction_no']} marked as PAID via {$gateway}");
        }
    }

    /**
     * Mark an order as failed
     */
    public static function mark_order_failed($transaction_no, $gateway = 'manual', $meta = []) {
        global $wpdb;
        $t_tx = OKJ_DB::get_table('pos_transactions');
        $wpdb->query($wpdb->prepare(
            "UPDATE {$t_tx} SET payment_status = 'failed', updated_at = %s WHERE transaction_no = %s OR id = %s",
            current_time('mysql'), $transaction_no, $transaction_no
        ));
    }

    /**
     * Mark an order as expired
     */
    public static function mark_order_expired($transaction_no, $gateway = 'manual', $meta = []) {
        global $wpdb;
        $t_tx = OKJ_DB::get_table('pos_transactions');
        $wpdb->query($wpdb->prepare(
            "UPDATE {$t_tx} SET payment_status = 'expired', updated_at = %s WHERE transaction_no = %s OR id = %s",
            current_time('mysql'), $transaction_no, $transaction_no
        ));
    }

    /**
     * Deduct stock for product (if not unlimited, i.e. stock >= 0)
     */
    public static function deduct_product_stock($product_id, $qty = 1) {
        global $wpdb;
        $t_prices = OKJ_DB::get_table('product_prices');

        $row = $wpdb->get_row($wpdb->prepare("SELECT id, name, stock, seller_id FROM {$t_prices} WHERE id = %s", $product_id), ARRAY_A);
        if ($row && (int)$row['stock'] >= 0) {
            $new_stock = max(0, (int)$row['stock'] - (int)$qty);
            $wpdb->update($t_prices, ['stock' => $new_stock, 'updated_at' => current_time('mysql')], ['id' => $product_id]);

            // Low stock alert check (threshold <= 3)
            if ($new_stock <= 3) {
                OKJ_Notifier::notify_seller_low_stock($row, $new_stock);
            }
        }
    }
}
