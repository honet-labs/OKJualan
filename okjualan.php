<?php
/**
 * Plugin Name: OKJualan
 * Description: Platform All-in-One Penjualan Produk, POS Kasir, Pelacakan Layanan & Pembelian, Notifikasi & Reminder, Payment Gateway (SumoPod QRIS), dan Laporan Penjualan.
 * Version: 0.2.11
 * Author: HONET
 * License: GPLv2 or later
 * Text Domain: okjualan
 */

if (!defined('ABSPATH')) { exit; }

// Catch fatal shutdown errors during plugin loading or activation for easier troubleshooting
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $file = isset($error['file']) ? $error['file'] : '';
        if (stripos($file, 'okjualan') !== false || stripos($file, 'okjualin') !== false) {
            error_log('[OKJualan Fatal Error] ' . ($error['message'] ?? '') . ' in ' . $file . ' on line ' . ($error['line'] ?? 0));
        }
    }
});

if (!class_exists('OKJ_App')) {

class OKJ_App {
    const VERSION = '0.2.11';

    private static $instance = null;
    public static function instance() {
        if (self::$instance === null) {
            try {
                self::$instance = new self();
            } catch (\Throwable $e) {
                error_log('[OKJualan Bootstrap Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            }
        }
        return self::$instance;
    }

    /**
     * Get plugin settings safely from WordPress options
     *
     * @return array
     */
    public static function get_settings() {
        $opt = function_exists('get_option') ? get_option('okj_settings_v1', []) : [];
        return is_array($opt) ? $opt : [];
    }

    private function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init();
    }

    private function define_constants() {
        if (!defined('OKJ_PLUGIN_DIR')) {
            define('OKJ_PLUGIN_DIR', plugin_dir_path(__FILE__));
        }
        if (!defined('OKJ_PLUGIN_URL')) {
            define('OKJ_PLUGIN_URL', plugin_dir_url(__FILE__));
        }
    }

    private function includes() {
        $modules = [
            'includes/class-security.php',
            'includes/class-db.php',
            'includes/class-notifier.php',
            'includes/class-pdf-invoice.php',
            'includes/class-payment-gateway.php',
            'includes/class-backup.php',
            'includes/class-updater.php',
            'includes/class-woocommerce-sync.php',
            'includes/class-reseller-manager.php',
            'includes/class-admin.php',
        ];

        foreach ($modules as $mod) {
            $path = OKJ_PLUGIN_DIR . $mod;
            if (file_exists($path)) {
                require_once $path;
            } else {
                error_log('[OKJualan] Missing required file: ' . $path);
            }
        }
    }

    private function init() {
        try {
            // DB Upgrade handler
            add_action('admin_init', [$this, 'maybe_upgrade_db']);

            // Ensure capabilities are always provisioned (critical after rebranding)
            add_action('admin_init', function() {
                if (class_exists('OKJ_DB')) {
                    OKJ_DB::ensure_caps();
                }
            });

            // Initialize WooCommerce synchronization engine & gateway listeners
            add_action('plugins_loaded', function() {
                if (class_exists('OKJ_WC_Sync')) {
                    OKJ_WC_Sync::init();
                }
            }, 10);
            if (class_exists('OKJ_WC_Sync')) {
                OKJ_WC_Sync::init();
            }

            // Direct registration of SumoPod QRIS Payment Gateway for WooCommerce
            add_filter('woocommerce_payment_gateways', function($gateways) {
                $gateway_file = OKJ_PLUGIN_DIR . 'includes/class-wc-gateway-sumopod.php';
                if (file_exists($gateway_file)) {
                    require_once $gateway_file;
                    if (class_exists('OKJ_WC_Gateway_SumoPod') && !in_array('OKJ_WC_Gateway_SumoPod', $gateways, true)) {
                        $gateways[] = 'OKJ_WC_Gateway_SumoPod';
                    }
                }
                return $gateways;
            }, 10);

            // Listen to payment gateway webhooks (e.g. SumoPod, Midtrans, Tripay)
            if (class_exists('OKJ_Payment_Gateway')) {
                add_action('init', ['OKJ_Payment_Gateway', 'handle_webhook']);
                add_action('parse_request', ['OKJ_Payment_Gateway', 'handle_webhook']);
            }

            // Listen to shortlink redirects
            add_action('parse_request', [$this, 'handle_shortlink_redirect']);

            // Initialize modules
            if (is_admin() && class_exists('OKJ_Admin')) {
                new OKJ_Admin();
            }

            // Initialize GitHub auto-updater
            if (class_exists('OKJ_Updater')) {
                new OKJ_Updater(__FILE__);
            }

            // Cron Scheduling
            if (class_exists('OKJ_Reseller_Manager')) {
                add_action('okj_daily_cron', ['OKJ_Reseller_Manager', 'process_daily_cron']);
                if (!wp_next_scheduled('okj_daily_cron')) {
                    wp_schedule_event(time(), 'daily', 'okj_daily_cron');
                }
            }

            // Listen to self-service public order requests
            add_action('template_redirect', [$this, 'handle_public_order_page']);
        } catch (\Throwable $e) {
            error_log('[OKJualan Init Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    public function handle_shortlink_redirect() {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $path = parse_url($request_uri, PHP_URL_PATH);
        
        $pos = strpos($path, '/go/');
        if ($pos !== false) {
            $key = substr($path, $pos + 4);
            $key = trim($key, '/');
            
            if (!empty($key)) {
                global $wpdb;
                $table = $wpdb->prefix . 'okj_shortlinks';
                
                $link = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE short_key = %s", $key), ARRAY_A);
                if ($link) {
                    $wpdb->query($wpdb->prepare("UPDATE {$table} SET clicks = clicks + 1 WHERE id = %s", $link['id']));
                    
                    wp_redirect($link['destination_url']);
                    exit;
                }
            }
        }
    }

    public function handle_public_order_page() {
        if (isset($_GET['okj_order']) && class_exists('OKJ_DB')) {
            global $wpdb;
            
            // Fetch categories for public catalog
            $categories = $wpdb->get_col("SELECT DISTINCT category FROM " . OKJ_DB::get_table('product_prices') . " WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
            
            $path = OKJ_PLUGIN_DIR . 'templates/public-order.php';
            if (file_exists($path)) {
                include $path;
                exit;
            }
        }
    }

    public function maybe_upgrade_db() {
        try {
            if (!class_exists('OKJ_DB')) {
                return;
            }
            global $wpdb;
            $t_customers = OKJ_DB::get_table('customers');
            $t_renewals = OKJ_DB::get_table('active_product_renewals');

            $customers_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $t_customers)) === $t_customers;
            $renewals_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $t_renewals)) === $t_renewals;

            $db_ver = get_option('okj_db_version', '');
            if ($db_ver !== self::VERSION || !$customers_exists || !$renewals_exists) {
                OKJ_DB::install();
                if (class_exists('OKJ_Reseller_Manager')) {
                    OKJ_Reseller_Manager::sync_all_paid_transactions_to_active_products();
                }
                update_option('okj_db_version', self::VERSION);
            }
        } catch (\Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OKJualan DB Upgrade Error] ' . $e->getMessage());
            }
        }
    }

    public static function activate() {
        try {
            if (!class_exists('OKJ_DB')) {
                require_once dirname(__FILE__) . '/includes/class-db.php';
            }
            if (class_exists('OKJ_DB')) {
                OKJ_DB::install();
            }
            if (!wp_next_scheduled('okj_daily_cron')) {
                wp_schedule_event(time(), 'daily', 'okj_daily_cron');
            }
        } catch (\Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OKJualan Activation Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            }
            wp_die(
                '<div style="font-family:sans-serif;padding:24px;max-width:700px;margin:30px auto;background:#fff;border-left:4px solid #d63638;box-shadow:0 1px 4px rgba(0,0,0,0.1);">' .
                '<h2 style="color:#d63638;margin-top:0;">Gagal Mengaktifkan Plugin OKJualan</h2>' .
                '<p>Terjadi kesalahan saat inisialisasi database plugin:</p>' .
                '<pre style="background:#f6f7f7;padding:12px;border-radius:4px;overflow-x:auto;color:#1d2327;">' . esc_html($e->getMessage()) . "\n\nFile: " . esc_html($e->getFile()) . ':' . $e->getLine() . '</pre>' .
                '<p><a href="' . esc_url(admin_url('plugins.php')) . '" class="button button-primary">Kembali ke Daftar Plugin</a></p>' .
                '</div>',
                'Aktivasi Plugin Gagal',
                ['back_link' => true]
            );
        }
    }

    public static function deactivate() {
        wp_clear_scheduled_hook('okj_daily_cron');
    }

    /**
     * Format payment method code/slug to clean, human-friendly label
     *
     * @param string $method
     * @return string
     */
    public static function format_payment_method($method) {
        $raw = strtolower(trim((string)$method));
        if (empty($raw)) {
            return '-';
        }

        // SumoPod QRIS / Generic QRIS -> Show as clean 'QRIS'
        if (strpos($raw, 'qris') !== false || strpos($raw, 'sumopod') !== false) {
            return 'QRIS';
        }
        // Cash / Tunai / COD
        if (in_array($raw, ['cash', 'cod', 'tunai'])) {
            return 'Cash / Tunai';
        }
        // Transfer Bank / BACS
        if (in_array($raw, ['transfer', 'bacs', 'bank_transfer']) || strpos($raw, 'transfer') !== false || strpos($raw, 'bank') !== false) {
            return 'Transfer Bank';
        }
        // E-Payment Gateways
        if (strpos($raw, 'midtrans') !== false) {
            return 'Midtrans';
        }
        if (strpos($raw, 'tripay') !== false) {
            return 'Tripay';
        }
        if (strpos($raw, 'xendit') !== false) {
            return 'Xendit';
        }

        // Clean technical prefixes (okj_, wc_) and delimiters
        $clean = preg_replace('/^(okj_|wc_)/i', '', $raw);
        return ucwords(str_replace(['_', '-'], ' ', $clean));
    }

    /**
     * Safely format datetime string avoiding double-timezone offset issues in WordPress.
     *
     * @param string $datetime_str Datetime from database or order
     * @param string $format Target PHP/WP date format
     * @return string
     */
    public static function format_datetime($datetime_str, $format = 'd M Y, H:i') {
        if (empty($datetime_str) || $datetime_str === '0000-00-00 00:00:00') {
            return '-';
        }
        try {
            // If the string contains explicit timezone indicator (e.g. +07:00, Z)
            if (strpos($datetime_str, '+') !== false || strpos($datetime_str, 'Z') !== false) {
                $dt = new DateTime($datetime_str);
                $dt->setTimezone(wp_timezone());
                return wp_date($format, $dt->getTimestamp(), wp_timezone());
            }

            // Normal MySQL datetime string stored in site's local time (via current_time('mysql'))
            // Passing DateTimeZone('UTC') to wp_date ensures WordPress does not apply an extra offset
            // while still respecting localized month/day translations.
            $ts = strtotime($datetime_str);
            if ($ts === false) {
                return $datetime_str;
            }
            return wp_date($format, $ts, new DateTimeZone('UTC'));
        } catch (\Throwable $e) {
            return date($format, strtotime($datetime_str));
        }
    }
}

} // end if class_exists

if (!function_exists('okj_format_payment_method')) {
    function okj_format_payment_method($method) {
        return OKJ_App::format_payment_method($method);
    }
}

if (!function_exists('okj_format_datetime')) {
    function okj_format_datetime($datetime_str, $format = 'd M Y, H:i') {
        return OKJ_App::format_datetime($datetime_str, $format);
    }
}

if (!function_exists('okj_get_settings')) {
    function okj_get_settings() {
        return OKJ_App::get_settings();
    }
}

register_activation_hook(__FILE__, ['OKJ_App', 'activate']);
register_deactivation_hook(__FILE__, ['OKJ_App', 'deactivate']);

// Boot the application safely
if (class_exists('OKJ_App')) {
    OKJ_App::instance();
}
