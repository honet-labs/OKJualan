<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('OKJ_Backup')) {

class OKJ_Backup {
    public static function export_json() {
        if (!current_user_can('okj_manage_settings')) {
            wp_die(esc_html__('Forbidden', 'okjualin'));
        }

        global $wpdb;
        $tables = [
            'product_prices' => OKJ_DB::get_table('product_prices'),
            'reseller_products' => OKJ_DB::get_table('reseller_products'),
            'customers' => OKJ_DB::get_table('customers'),
            'sellers' => OKJ_DB::get_table('sellers'),
            'active_products' => OKJ_DB::get_table('active_products'),
            'active_reminders' => OKJ_DB::get_table('active_reminders'),
            'logs' => OKJ_DB::get_table('logs'),
            'active_product_renewals' => OKJ_DB::get_table('active_product_renewals'),
        ];

        $data = [];
        foreach ($tables as $key => $table) {
            $data[$key] = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);
        }

        $data['settings'] = get_option('okj_settings_v1', []);

        $json = wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $filename = 'okj-backup-' . wp_date('Y-m-d-His') . '.json';

        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json));
        echo $json;
        exit;
    }

    public static function import_json($file_path) {
        if (!current_user_can('okj_manage_settings')) {
            return ['ok' => false, 'error' => 'Forbidden'];
        }

        if (!file_exists($file_path)) {
            return ['ok' => false, 'error' => 'Uploaded file not found'];
        }

        $raw = file_get_contents($file_path);
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            return ['ok' => false, 'error' => 'Invalid backup JSON structure'];
        }

        global $wpdb;

        // Restore Settings
        if (isset($data['settings']) && is_array($data['settings'])) {
            update_option('okj_settings_v1', $data['settings']);
        }

        $tables = [
            'product_prices' => OKJ_DB::get_table('product_prices'),
            'reseller_products' => OKJ_DB::get_table('reseller_products'),
            'customers' => OKJ_DB::get_table('customers'),
            'sellers' => OKJ_DB::get_table('sellers'),
            'active_products' => OKJ_DB::get_table('active_products'),
            'active_reminders' => OKJ_DB::get_table('active_reminders'),
            'logs' => OKJ_DB::get_table('logs'),
            'active_product_renewals' => OKJ_DB::get_table('active_product_renewals'),
        ];

        foreach ($tables as $key => $table) {
            if (!isset($data[$key]) || !is_array($data[$key])) continue;

            $wpdb->query("TRUNCATE TABLE {$table}");

            foreach ($data[$key] as $row) {
                $wpdb->insert($table, $row);
            }
        }

        return ['ok' => true];
    }
}
}
