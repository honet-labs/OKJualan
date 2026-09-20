<?php
if (!defined('ABSPATH')) { exit; }

/**
 * SumoPod QRIS WooCommerce Payment Gateway
 * Integrates SumoPod QRIS payment method into standard WooCommerce checkout flow.
 */
if (!class_exists('WC_Payment_Gateway')) {
    return;
}

if (!class_exists('OKJ_WC_Gateway_SumoPod')) {

class OKJ_WC_Gateway_SumoPod extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'okj_sumopod_qris';
        $this->icon               = '';
        $this->has_fields         = false;
        $this->method_title       = __('QRIS', 'okjualan');
        $this->method_description = __('Menerima pembayaran scan QRIS instan secara otomatis (BCA, Mandiri, BRI, BNI, GoPay, OVO, DANA, ShopeePay) melalui QRIS Payment Gateway.', 'okjualan');
        $this->supports           = ['products'];

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        $this->title       = $this->get_option('title', 'QRIS');
        $this->description = $this->get_option('description', 'Bayar cepat dan otomatis terverifikasi menggunakan QRIS dari seluruh aplikasi e-wallet (GoPay, OVO, DANA, ShopeePay) atau Mobile Banking apa saja.');
        $this->enabled     = $this->get_option('enabled', 'yes');
        $this->order_button_text = __('Bayar via QRIS', 'okjualan');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    /**
     * Check if gateway is available for checkout
     */
    public function is_available() {
        if ($this->enabled !== 'yes') {
            return false;
        }

        // Always show as available in WordPress admin preview/settings
        if (is_admin()) {
            return true;
        }

        // Check if SumoPod API Key is configured in OKJualan settings
        $settings = class_exists('OKJ_Payment_Gateway') ? OKJ_Payment_Gateway::get_settings() : [];
        if (empty($settings['sumopod_api_key'])) {
            return false;
        }

        return parent::is_available();
    }

    /**
     * Define WooCommerce admin gateway settings fields
     */
    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'   => __('Aktifkan/Nonaktifkan', 'okjualan'),
                'type'    => 'checkbox',
                'label'   => __('Aktifkan SumoPod QRIS di Checkout WooCommerce', 'okjualan'),
                'default' => 'yes',
            ],
            'title' => [
                'title'       => __('Judul Metode Pembayaran', 'okjualan'),
                'type'        => 'text',
                'description' => __('Judul yang dilihat oleh pembeli saat memilih metode pembayaran di halaman checkout.', 'okjualan'),
                'default'     => 'QRIS (Semua E-Wallet & Mobile Banking)',
                'desc_tip'    => true,
            ],
            'description' => [
                'title'       => __('Deskripsi Pembayaran', 'okjualan'),
                'type'        => 'textarea',
                'description' => __('Instruksi singkat yang ditampilkan kepada pembeli saat memilih opsi QRIS ini.', 'okjualan'),
                'default'     => 'Bayar cepat dan otomatis terverifikasi menggunakan QRIS dari seluruh aplikasi e-wallet (GoPay, OVO, DANA, ShopeePay) atau Mobile Banking apa saja.',
            ],
        ];
    }

    /**
     * Render WooCommerce settings form for SumoPod QRIS
     */
    public function admin_options() {
        ?>
        <h2><?php echo esc_html($this->method_title); ?></h2>
        <p><?php echo esc_html($this->method_description); ?></p>
        <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 14px; margin-bottom: 20px;">
            <p style="margin: 0 0 10px 0; color: #166534; font-weight: 600;">
                <span class="dashicons dashicons-yes-alt" style="color: #10b981; vertical-align: middle;"></span>
                Gateway ini otomatis terintegrasi dengan kredensial SumoPod (API Key, Webhook Secret, dan Mode) dari menu <strong>OKJualan &gt; Settings &gt; Payment Gateway</strong>.
            </p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=okj-settings&tab=gateways')); ?>" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 4px;">
                <span class="dashicons dashicons-admin-generic" style="font-size: 16px; width: 16px; height: 16px;"></span>
                Buka Pengaturan API SumoPod di OKJualan
            </a>
        </div>
        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
        </table>
        <?php
    }

    /**
     * Provide a clean QRIS badge icon for checkout UI
     */
    public function get_icon() {
        $icon_html = '<span style="display:inline-block; vertical-align:middle; margin-left:8px; line-height:1;">'
            . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 68 28" width="58" height="24" style="border-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,0.1);">'
            . '<rect width="68" height="28" fill="#e61b23" rx="4"/>'
            . '<text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-weight="900" font-size="13" fill="#ffffff" letter-spacing="1">QRIS</text>'
            . '</svg></span>';
        return apply_filters('woocommerce_gateway_icon', $icon_html, $this->id);
    }

    /**
     * Process checkout payment and redirect customer to SumoPod QRIS
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            wc_add_notice(__('Pesanan tidak ditemukan.', 'okjualan'), 'error');
            return ['result' => 'fail', 'redirect' => ''];
        }

        $amount = (int)round((float)$order->get_total());
        if ($amount < 1000) {
            wc_add_notice(
                sprintf(
                    __('Pembayaran Gagal: Nominal pesanan (Rp %s) terlalu kecil. Regulasi QRIS Indonesia mewajibkan nominal transaksi minimal Rp 1.000.', 'okjualan'),
                    number_format($amount, 0, ',', '.')
                ),
                'error'
            );
            return ['result' => 'fail', 'redirect' => ''];
        }

        // Standard invoice reference with INV- prefix matching SumoPod acquirer format
        $sumopod_order_id = 'INV-' . $order_id . '-' . round(microtime(true) * 1000) . '-' . strtoupper(wp_generate_password(4, false));

        $amount = (int)round((float)$order->get_total());
        $customer_name = trim($order->get_formatted_billing_full_name());
        if (!$customer_name) {
            $customer_name = 'Pelanggan #' . $order_id;
        }

        $items = [];
        foreach ($order->get_items() as $item) {
            $items[] = [
                'name'  => $item->get_name(),
                'price' => (int)round((float)$order->get_item_total($item, false)),
                'qty'   => $item->get_quantity(),
            ];
        }

        $res = OKJ_Payment_Gateway::create_sumopod_payment([
            'order_id'       => $sumopod_order_id,
            'amount'         => $amount,
            'customer_name'  => $customer_name,
            'customer_email' => $order->get_billing_email(),
            'customer_phone' => $order->get_billing_phone(),
            'items'          => $items,
            'success_url'    => $this->get_return_url($order),
            'cancel_url'     => wc_get_checkout_url(),
        ]);

        if (!empty($res['ok']) && !empty($res['payment_link_url'])) {
            // Store reference in WooCommerce order metadata
            $order->update_meta_data('_okj_sumopod_order_id', $sumopod_order_id);
            $order->update_meta_data('_okj_sumopod_payment_id', $res['payment_id'] ?? '');
            $order->update_meta_data('_okj_sumopod_payment_url', $res['payment_link_url']);
            if (!empty($res['qr_code_url'])) {
                $order->update_meta_data('_okj_sumopod_qr_url', $res['qr_code_url']);
            }
            $order->update_status('pending', __('Menunggu pembayaran QRIS.', 'okjualan'));
            $order->save();

            // Clear customer cart
            if (isset(WC()->cart)) {
                WC()->cart->empty_cart();
            }

            // Immediately sync order to OKJualan List Transaksi (pos_transactions) table as pending
            if (class_exists('OKJ_WC_Sync')) {
                OKJ_WC_Sync::sync_wc_order_to_pos_transaction($order_id, $sumopod_order_id);
            }

            return [
                'result'   => 'success',
                'redirect' => $res['payment_link_url'],
            ];
        }

        $err_msg = !empty($res['error']) ? $res['error'] : __('Terjadi kegagalan saat menghubungkan ke gateway pembayaran SumoPod.', 'okjualan');
        wc_add_notice(__('Pembayaran Gagal: ', 'okjualan') . esc_html($err_msg), 'error');

        return [
            'result'   => 'fail',
            'redirect' => '',
        ];
    }
}

}
