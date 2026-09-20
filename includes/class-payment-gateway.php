<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('OKJ_Payment_Gateway')) {

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

        // Check minimum transaction amount required by Indonesian QRIS regulation
        if ($amount < 1000) {
            return [
                'ok'    => false,
                'error' => 'Nominal pembayaran (Rp ' . number_format($amount, 0, ',', '.') . ') terlalu kecil. Standar pembayaran QRIS mewajibkan nominal transaksi minimal Rp 1.000.',
            ];
        }

        $success_url = !empty($order['success_url']) ? esc_url_raw($order['success_url']) : home_url('/?okj_order=1&track_order=' . rawurlencode($order_id) . '&paid=1');
        $cancel_url = !empty($order['cancel_url']) ? esc_url_raw($order['cancel_url']) : home_url('/?okj_order=1&track_order=' . rawurlencode($order_id) . '&cancelled=1');

        $method_code = !empty($settings['sumopod_default_method']) ? strtoupper(trim($settings['sumopod_default_method'])) : 'QRIS';
        if ($method_code !== 'VA') {
            $method_code = 'QRIS';
        }

        $payload = [
            'order_id'                 => $order_id,
            'amount'                   => $amount,
            'currency'                 => 'IDR',
            'expires_in_hours'         => 24,
            'success_return_url'       => $success_url,
            'cancel_return_url'        => $cancel_url,
            'payment_method_type_code' => $method_code,
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
                'qr_code_url'      => $data['qr_code_url'] ?? ($data['qr_code'] ?? ($data['qr_string'] ?? '')),
                'fee'              => $data['fee'] ?? 0,
                'status'           => $data['status'] ?? 'pending',
                'expires_at'       => $data['expires_at'] ?? '',
                'raw_response'     => $data,
            ];
        }

        // Comprehensive error message extraction from SumoPod response
        $err_msg = '';
        if (is_array($data)) {
            if (!empty($data['message']) && is_string($data['message'])) {
                $err_msg = $data['message'];
            } elseif (!empty($data['error'])) {
                $err_msg = is_string($data['error']) ? $data['error'] : wp_json_encode($data['error']);
            } elseif (!empty($data['detail'])) {
                $err_msg = is_string($data['detail']) ? $data['detail'] : wp_json_encode($data['detail']);
            } elseif (!empty($data['errors'])) {
                if (is_array($data['errors'])) {
                    $flat = [];
                    foreach ($data['errors'] as $k => $v) {
                        if (is_array($v)) {
                            $flat[] = implode(', ', $v);
                        } else {
                            $flat[] = (string)$v;
                        }
                    }
                    $err_msg = implode('; ', $flat);
                } else {
                    $err_msg = (string)$data['errors'];
                }
            }
        }

        if (empty($err_msg)) {
            $raw_snippet = trim(strip_tags((string)$body));
            if (!empty($raw_snippet) && strlen($raw_snippet) < 150) {
                $err_msg = $raw_snippet;
            } else {
                $err_msg = 'Gagal membuat pembayaran via SumoPod';
            }
        }

        // Log error to OKJualan logs and PHP error log for troubleshooting
        if (class_exists('OKJ_Reseller_Manager')) {
            OKJ_Reseller_Manager::log(
                'sumopod_error',
                'payment',
                $order_id,
                "SumoPod API HTTP {$code}: {$err_msg}",
                ['payload' => $payload, 'response_body' => $body]
            );
        }
        error_log("[OKJualan SumoPod Error] HTTP {$code}: " . $body);

        return ['ok' => false, 'error' => $err_msg . ' (HTTP ' . $code . ')'];
    }

    /**
     * Fetch payment status and details from SumoPod API
     *
     * @param string $payment_id
     * @return array|null
     */
    public static function get_sumopod_payment($payment_id) {
        if (empty($payment_id)) {
            return null;
        }
        $settings = self::get_settings();
        if (empty($settings['sumopod_enabled']) || empty($settings['sumopod_api_key'])) {
            return null;
        }

        $is_sandbox = ($settings['sumopod_mode'] ?? 'sandbox') === 'sandbox';
        $endpoint = ($is_sandbox 
            ? 'https://api-pay-sandbox.sumopod.com/api/v1/payments/'
            : 'https://api-pay.sumopod.com/api/v1/payments/') . urlencode($payment_id);

        $resp = wp_remote_get($endpoint, [
            'timeout' => 8,
            'headers' => [
                'Accept'    => 'application/json',
                'X-Api-Key' => trim((string)$settings['sumopod_api_key']),
            ],
        ]);

        if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
            return null;
        }

        $body = wp_remote_retrieve_body($resp);
        $data = json_decode($body, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Check if current request matches webhook endpoint
     */
    public static function is_webhook_request() {
        if (isset($_GET['okj_webhook']) && $_GET['okj_webhook'] === 'payment') {
            return true;
        }

        if (!empty($_SERVER['REQUEST_URI'])) {
            $req_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $home_path = parse_url(home_url(), PHP_URL_PATH);
            if (!empty($home_path) && $home_path !== '/') {
                $req_path = substr($req_path, strlen(rtrim($home_path, '/')));
            }
            $clean_path = trim((string)$req_path, '/');
            if ($clean_path === 'webhook' || $clean_path === 'okj-webhook' || preg_match('#(^|/)webhook/?$#i', $clean_path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle incoming webhook requests from payment gateways
     */
    public static function handle_webhook() {
        if (!self::is_webhook_request()) {
            return;
        }

        // Webhooks strictly accept POST requests.
        // Direct browser GET requests will NOT expose any sensitive UI or internal information.
        $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
        if ($method !== 'POST') {
            // If an authenticated admin opens this in browser, redirect them directly to settings
            if (is_user_logged_in() && (current_user_can('manage_options') || current_user_can('okj_manage_settings'))) {
                wp_safe_redirect(admin_url('admin.php?page=okj-settings&tab=gateways'));
                exit;
            }

            // Public visitors / bots get a sterile 405 Method Not Allowed
            status_header(405);
            header('Content-Type: application/json; charset=utf-8');
            header('Allow: POST');
            echo json_encode([
                'status'  => 'error',
                'message' => 'Method Not Allowed. Webhook endpoint only accepts POST requests.'
            ]);
            exit;
        }

        $raw_body = file_get_contents('php://input');
        if (empty($raw_body)) {
            status_header(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Empty webhook payload received.']);
            exit;
        }

        $settings = self::get_settings();

        // 1. Check SumoPod Webhook (via Svix headers or Webhook Token)
        $is_sumopod = !empty(self::get_request_header('svix-id')) || !empty(self::get_request_header('x-webhook-token'));
        if ($is_sumopod) {
            self::process_sumopod_webhook($raw_body, $settings);
            exit;
        }

        // 2. Generic fallback / JSON payload detection
        $event = json_decode($raw_body, true);
        if ($event && isset($event['event_type']) && (strpos($event['event_type'], 'payment.') === 0 || in_array($event['event_type'], ['ping', 'test']))) {
            self::process_sumopod_webhook($raw_body, $settings);
            exit;
        }

        status_header(200);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'ok', 'message' => 'Webhook received successfully']);
        exit;
    }

    /**
     * Case-insensitive HTTP Header resolver (compatible with Apache, Nginx, LiteSpeed, Caddy)
     */
    public static function get_request_header($name) {
        $clean_name = strtolower(trim($name));
        $server_key = 'HTTP_' . strtoupper(str_replace('-', '_', $clean_name));
        if (!empty($_SERVER[$server_key])) {
            return trim($_SERVER[$server_key]);
        }
        $direct_key = strtoupper(str_replace('-', '_', $clean_name));
        if (!empty($_SERVER[$direct_key])) {
            return trim($_SERVER[$direct_key]);
        }
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (is_array($headers)) {
                foreach ($headers as $k => $v) {
                    if (strtolower($k) === $clean_name) {
                        return trim($v);
                    }
                }
            }
        }
        return '';
    }

    /**
     * Process SumoPod Webhook with signature & token verification
     */
    private static function process_sumopod_webhook($raw_body, $settings) {
        $secret = trim((string)($settings['sumopod_webhook_secret'] ?? ''));
        $token  = trim((string)($settings['sumopod_webhook_token'] ?? ''));

        $svix_id        = self::get_request_header('svix-id');
        $svix_timestamp = self::get_request_header('svix-timestamp');
        $svix_signature = self::get_request_header('svix-signature');
        $token_header   = self::get_request_header('x-webhook-token');

        $is_valid = false;

        // Method 1: Verify via Token (X-Webhook-Token header)
        if ($token && $token_header) {
            if (hash_equals($token, $token_header)) {
                $is_valid = true;
            }
        }

        // Method 2: Verify via Svix Signature (whsec_...)
        if (!$is_valid && $secret && $svix_id && $svix_timestamp && $svix_signature) {
            $is_valid = self::verify_svix_signature(
                $secret,
                $svix_id,
                $svix_timestamp,
                $svix_signature,
                $raw_body
            );
        }

        // If no secret or token configured in settings, reject for security
        if (!$secret && !$token) {
            status_header(401);
            echo 'Webhook secret (whsec_) or token not configured in OKJualan settings';
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
        } elseif ($event_type === 'payment.test') {
            status_header(200);
            echo 'Verified webhook test: ' . esc_html($event_type);
            exit;
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
            "SELECT * FROM {$t_tx} WHERE transaction_no = %s OR reference_no = %s OR id = %s LIMIT 1",
            $transaction_no, $transaction_no, $transaction_no
        ), ARRAY_A);

        // Fallback: If not in pos_transactions yet, attempt auto-sync from WooCommerce
        if (!$tx && class_exists('OKJ_WC_Sync')) {
            $wc_order_id = 0;
            if (preg_match('/^(?:WC|INV)-(\d+)/i', $transaction_no, $m)) {
                $wc_order_id = (int)$m[1];
            } elseif (function_exists('wc_get_orders')) {
                $found_orders = wc_get_orders([
                    'meta_key'   => '_okj_sumopod_order_id',
                    'meta_value' => $transaction_no,
                    'limit'      => 1,
                ]);
                if (!empty($found_orders)) {
                    $wc_order_id = $found_orders[0]->get_id();
                }
            }
            if ($wc_order_id > 0) {
                OKJ_WC_Sync::sync_wc_order_to_pos_transaction($wc_order_id, $transaction_no);
                $tx = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$t_tx} WHERE transaction_no = %s OR reference_no = %s OR id = %s LIMIT 1",
                    $transaction_no, $transaction_no, $transaction_no
                ), ARRAY_A);
            }
        }

        if ($tx) {
            $wpdb->update($t_tx, [
                'payment_status' => 'paid',
                'payment_method' => $gateway,
                'updated_at'     => current_time('mysql'),
            ], ['id' => $tx['id']]);

            // Ensure active products are created and activated for this transaction
            if (class_exists('OKJ_Reseller_Manager')) {
                OKJ_Reseller_Manager::sync_transaction_to_active_products($tx['id']);
            }

            // Also activate any pre-existing active products linked to this transaction
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

        // Handle WooCommerce orders if transaction matches WC or INV pattern, numeric ID, or meta lookup
        if (class_exists('WooCommerce') && function_exists('wc_get_order')) {
            $wc_order_id = 0;
            if (preg_match('/^(?:WC|INV)-(\d+)/i', $transaction_no, $m)) {
                $wc_order_id = (int)$m[1];
            } elseif (is_numeric($transaction_no)) {
                $check_wc = wc_get_order((int)$transaction_no);
                if ($check_wc) {
                    $wc_order_id = (int)$transaction_no;
                }
            }

            // Fallback: search WooCommerce order by stored SumoPod Order ID metadata
            if ($wc_order_id <= 0 && function_exists('wc_get_orders')) {
                $found_orders = wc_get_orders([
                    'meta_key'   => '_okj_sumopod_order_id',
                    'meta_value' => $transaction_no,
                    'limit'      => 1,
                ]);
                if (!empty($found_orders)) {
                    $wc_order_id = $found_orders[0]->get_id();
                }
            }

            if ($wc_order_id > 0) {
                $wc_order = wc_get_order($wc_order_id);
                if ($wc_order && !$wc_order->is_paid()) {
                    $payment_id = $meta['payment_id'] ?? '';
                    $wc_order->payment_complete($payment_id);
                    $wc_order->add_order_note(sprintf('Pembayaran otomatis lunas terverifikasi via SumoPod QRIS (Payment ID: %s, Ref: %s)', $payment_id, $transaction_no));
                    OKJ_Reseller_Manager::log('sumopod_wc_payment', 'wc_order', $wc_order_id, "WooCommerce Order #{$wc_order_id} lunas via SumoPod QRIS ({$transaction_no})");
                }
            }
        }
    }

    /**
     * Mark an order as failed
     */
    public static function mark_order_failed($transaction_no, $gateway = 'manual', $meta = []) {
        global $wpdb;
        $t_tx = OKJ_DB::get_table('pos_transactions');

        if (class_exists('WooCommerce') && function_exists('wc_get_order')) {
            $wc_order_id = 0;
            if (preg_match('/^(?:WC|INV)-(\d+)/i', $transaction_no, $m)) {
                $wc_order_id = (int)$m[1];
            } elseif (function_exists('wc_get_orders')) {
                $found_orders = wc_get_orders([
                    'meta_key'   => '_okj_sumopod_order_id',
                    'meta_value' => $transaction_no,
                    'limit'      => 1,
                ]);
                if (!empty($found_orders)) {
                    $wc_order_id = $found_orders[0]->get_id();
                }
            }

            if ($wc_order_id > 0) {
                if (class_exists('OKJ_WC_Sync')) {
                    OKJ_WC_Sync::sync_wc_order_to_pos_transaction($wc_order_id, $transaction_no);
                }
                $wc_order = wc_get_order($wc_order_id);
                if ($wc_order && $wc_order->has_status(['pending', 'on-hold'])) {
                    $wc_order->update_status('failed', 'Pembayaran QRIS via SumoPod dibatalkan atau gagal.');
                }
            }
        }

        $wpdb->query($wpdb->prepare(
            "UPDATE {$t_tx} SET payment_status = 'failed', updated_at = %s WHERE transaction_no = %s OR reference_no = %s OR id = %s",
            current_time('mysql'), $transaction_no, $transaction_no, $transaction_no
        ));
    }

    /**
     * Mark an order as expired
     */
    public static function mark_order_expired($transaction_no, $gateway = 'manual', $meta = []) {
        global $wpdb;
        $t_tx = OKJ_DB::get_table('pos_transactions');

        if (class_exists('WooCommerce') && function_exists('wc_get_order')) {
            $wc_order_id = 0;
            if (preg_match('/^(?:WC|INV)-(\d+)/i', $transaction_no, $m)) {
                $wc_order_id = (int)$m[1];
            } elseif (function_exists('wc_get_orders')) {
                $found_orders = wc_get_orders([
                    'meta_key'   => '_okj_sumopod_order_id',
                    'meta_value' => $transaction_no,
                    'limit'      => 1,
                ]);
                if (!empty($found_orders)) {
                    $wc_order_id = $found_orders[0]->get_id();
                }
            }

            if ($wc_order_id > 0) {
                if (class_exists('OKJ_WC_Sync')) {
                    OKJ_WC_Sync::sync_wc_order_to_pos_transaction($wc_order_id, $transaction_no);
                }
                $wc_order = wc_get_order($wc_order_id);
                if ($wc_order && $wc_order->has_status(['pending', 'on-hold'])) {
                    $wc_order->update_status('cancelled', 'Batas waktu pembayaran QRIS SumoPod telah kadaluwarsa.');
                }
            }
        }

        $wpdb->query($wpdb->prepare(
            "UPDATE {$t_tx} SET payment_status = 'expired', updated_at = %s WHERE transaction_no = %s OR reference_no = %s OR id = %s",
            current_time('mysql'), $transaction_no, $transaction_no, $transaction_no
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
}
