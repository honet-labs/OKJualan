<?php
if (!defined('ABSPATH')) { exit; }

/**
 * SumoPod QRIS WooCommerce Payment Gateway
 * Integrates SumoPod QRIS payment method into standard WooCommerce checkout flow.
 */
if (!class_exists('OKJ_WC_Gateway_SumoPod') && class_exists('WC_Payment_Gateway')) {

class OKJ_WC_Gateway_SumoPod extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'okj_sumopod_qris';
        $this->icon               = $this->get_qris_icon();
        $this->has_fields         = false;
        $this->method_title       = __('SumoPod QRIS (OKJualan)', 'okjualan');
        $this->method_description = __('Menerima pembayaran scan QRIS instan secara otomatis (BCA, Mandiri, BRI, BNI, GoPay, OVO, DANA, ShopeePay) melalui SumoPod Payment Gateway.', 'okjualan');
        $this->supports           = ['products'];

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        // Check master setting from OKJualan settings
        $okj_settings = get_option('okj_settings_v1', []);
        $master_enabled = !empty($okj_settings['sumopod_enabled']);

        $this->title       = $this->get_option('title', 'QRIS (Semua E-Wallet & Mobile Banking)');
        $this->description = $this->get_option('description', 'Bayar cepat dan otomatis terverifikasi menggunakan QRIS dari seluruh aplikasi e-wallet (GoPay, OVO, DANA, ShopeePay) atau Mobile Banking apa saja.');
        $this->enabled     = ($master_enabled && $this->get_option('enabled', 'yes') === 'yes') ? 'yes' : 'no';

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
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
     * Provide a clean QRIS badge icon for checkout UI
     */
    public function get_qris_icon() {
        // High-contrast clean QRIS badge
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 68 28" width="58" height="24" style="vertical-align: middle; margin-left: 8px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
            <rect width="68" height="28" fill="#e61b23" rx="4"/>
            <text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-weight="900" font-size="13" fill="#ffffff" letter-spacing="1">QRIS</text>
        </svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
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

        // Unique transaction reference with WC prefix and order id
        $sumopod_order_id = 'WC-' . $order_id . '-' . time();

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
            'cancel_url'     => $order->get_cancel_order_url(),
        ]);

        if (!empty($res['ok']) && !empty($res['payment_link_url'])) {
            // Store reference in WooCommerce order metadata
            $order->update_meta_data('_okj_sumopod_order_id', $sumopod_order_id);
            $order->update_meta_data('_okj_sumopod_payment_id', $res['payment_id'] ?? '');
            $order->update_meta_data('_okj_sumopod_payment_url', $res['payment_link_url']);
            $order->update_status('pending', __('Menunggu pembayaran QRIS via SumoPod.', 'okjualan'));
            $order->save();

            // Clear customer cart
            if (isset(WC()->cart)) {
                WC()->cart->empty_cart();
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
