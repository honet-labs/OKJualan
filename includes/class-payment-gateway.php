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

        // If accessed directly via web browser (GET request), display informative and friendly status page
        if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'GET') {
            self::render_webhook_status_page();
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
     * Render friendly status page when webhook endpoint is opened in a browser
     */
    private static function render_webhook_status_page() {
        status_header(200);
        header('Content-Type: text/html; charset=utf-8');

        $canonical_url = home_url('/?okj_webhook=payment');
        $clean_url     = home_url('/webhook');
        $site_name     = get_bloginfo('name');
        $admin_url     = admin_url('admin.php?page=okj-settings');
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>OKJualan Webhook Endpoint &mdash; <?php echo esc_html($site_name); ?></title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
                body {
                    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 24px 16px;
                    color: #334155;
                }
                .container {
                    background: #ffffff;
                    max-width: 680px;
                    width: 100%;
                    border-radius: 20px;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
                    overflow: hidden;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                }
                .header {
                    background: linear-gradient(135deg, #059669 0%, #047857 100%);
                    padding: 32px 28px;
                    color: #ffffff;
                    text-align: center;
                    position: relative;
                }
                .badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    background: rgba(255, 255, 255, 0.2);
                    padding: 6px 14px;
                    border-radius: 9999px;
                    font-size: 12px;
                    font-weight: 700;
                    letter-spacing: 0.5px;
                    text-transform: uppercase;
                    margin-bottom: 12px;
                    backdrop-filter: blur(4px);
                }
                .pulse-dot {
                    width: 8px;
                    height: 8px;
                    background-color: #34d399;
                    border-radius: 50%;
                    display: inline-block;
                    animation: pulse 1.8s infinite;
                }
                @keyframes pulse {
                    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7); }
                    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(52, 211, 153, 0); }
                    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); }
                }
                .header h1 { font-size: 24px; font-weight: 800; margin-bottom: 6px; }
                .header p { font-size: 13.5px; color: #d1fae5; line-height: 1.5; }
                .body { padding: 28px; }
                .info-box {
                    background: #f0fdf4;
                    border: 1.5px solid #bbf7d0;
                    border-radius: 12px;
                    padding: 18px;
                    margin-bottom: 22px;
                }
                .info-box h3 { color: #166534; font-size: 14.5px; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
                .info-box p { color: #15803d; font-size: 13px; line-height: 1.6; }
                .url-section { margin-bottom: 22px; }
                .url-label { font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
                .url-card {
                    background: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 10px;
                    padding: 12px 14px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 10px;
                    margin-bottom: 10px;
                }
                .url-card code { font-family: monospace; font-size: 13px; color: #0f172a; word-break: break-all; font-weight: 600; }
                .copy-btn {
                    background: #4f46e5;
                    color: white;
                    border: none;
                    padding: 7px 14px;
                    border-radius: 7px;
                    font-size: 12px;
                    font-weight: 700;
                    cursor: pointer;
                    white-space: nowrap;
                    transition: background 0.15s;
                }
                .copy-btn:hover { background: #4338ca; }
                .features-list { list-style: none; margin-bottom: 24px; }
                .features-list li {
                    display: flex;
                    align-items: flex-start;
                    gap: 10px;
                    font-size: 13px;
                    color: #475569;
                    margin-bottom: 10px;
                    line-height: 1.5;
                }
                .features-list li span.icon {
                    background: #e0e7ff;
                    color: #4f46e5;
                    width: 20px;
                    height: 20px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 11px;
                    font-weight: 800;
                    flex-shrink: 0;
                    margin-top: 1px;
                }
                .footer {
                    border-top: 1px solid #f1f5f9;
                    padding-top: 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 12px;
                }
                .btn-link {
                    color: #4f46e5;
                    font-size: 13px;
                    font-weight: 700;
                    text-decoration: none;
                }
                .btn-link:hover { text-decoration: underline; }
                .btn-home {
                    background: #f1f5f9;
                    color: #334155;
                    padding: 9px 18px;
                    border-radius: 8px;
                    font-size: 13px;
                    font-weight: 700;
                    text-decoration: none;
                    transition: background 0.15s;
                }
                .btn-home:hover { background: #e2e8f0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="badge"><span class="pulse-dot"></span> Endpoint Aktif &amp; Siap Menerima Data</div>
                    <h1>OKJualan Webhook Gateway</h1>
                    <p>Gerbang notifikasi otomatis untuk penerimaan konfirmasi pembayaran SumoPod QRIS &amp; e-commerce.</p>
                </div>
                <div class="body">
                    <div class="info-box">
                        <h3>✅ Webhook Berfungsi Normal</h3>
                        <p>Halaman ini adalah <strong>Endpoint Webhook API</strong> yang dirancang untuk menerima kiriman data callback (metode <code>POST</code>) dari payment gateway saat pelanggan Anda menyelesaikan pembayaran secara realtime.</p>
                    </div>

                    <div class="url-section">
                        <span class="url-label">URL Webhook Untuk Dashboard SumoPod:</span>
                        <div class="url-card">
                            <code id="can-url"><?php echo esc_html($canonical_url); ?></code>
                            <button type="button" class="copy-btn" onclick="navigator.clipboard.writeText('<?php echo esc_js($canonical_url); ?>'); this.innerText='Tersalin!'; setTimeout(()=>this.innerText='Salin', 1500);">Salin</button>
                        </div>
                        <div class="url-card">
                            <code id="clean-url"><?php echo esc_html($clean_url); ?></code>
                            <button type="button" class="copy-btn" style="background: #059669;" onclick="navigator.clipboard.writeText('<?php echo esc_js($clean_url); ?>'); this.innerText='Tersalin!'; setTimeout(()=>this.innerText='Salin', 1500);">Salin</button>
                        </div>
                        <small style="color: #64748b; font-size: 11.5px; display: block; margin-top: 4px;">Kedua URL di atas didukung penuh dan siap digunakan di menu Webhooks SumoPod.</small>
                    </div>

                    <ul class="features-list">
                        <li>
                            <span class="icon">✓</span>
                            <div><strong>Verifikasi Svix Signature:</strong> Mendukung validasi keamanan tinggi HMAC-SHA256 dengan secret key <code>whsec_...</code>.</div>
                        </li>
                        <li>
                            <span class="icon">✓</span>
                            <div><strong>Verifikasi Webhook Token:</strong> Mendukung fallback otentikasi header <code>X-Webhook-Token</code>.</div>
                        </li>
                        <li>
                            <span class="icon">✓</span>
                            <div><strong>Event Terintegrasi:</strong> Mendukung <code>payment.completed</code> (Lunas), <code>payment.failed</code>, <code>payment.expired</code>, dan <code>payment.test</code>.</div>
                        </li>
                    </ul>

                    <div class="footer">
                        <a href="<?php echo esc_url($admin_url); ?>" class="btn-link">⚙️ Buka Pengaturan OKJualan Admin &raquo;</a>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-home">&larr; Kembali ke Website</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
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
}
