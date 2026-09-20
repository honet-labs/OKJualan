<?php
/**
 * OKJualan Uninstaller
 * Fired when the plugin is deleted via the WordPress admin plugins interface.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// 1. Drop all OKJualan custom database tables
$tables = [
    $wpdb->prefix . 'okj_logs',
    $wpdb->prefix . 'okj_shortlinks',
    $wpdb->prefix . 'okj_pos_transaction_items',
    $wpdb->prefix . 'okj_pos_transactions',
    $wpdb->prefix . 'okj_active_reminders',
    $wpdb->prefix . 'okj_active_product_renewals',
    $wpdb->prefix . 'okj_active_products',
    $wpdb->prefix . 'okj_reseller_products',
    $wpdb->prefix . 'okj_customers',
    $wpdb->prefix . 'okj_sellers',
    $wpdb->prefix . 'okj_product_prices',
];

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS {$table}");
}

// 2. Delete plugin options & transient data
delete_option('okj_settings_v1');
delete_option('okj_db_version');
delete_option('okj_version');

// 3. Clear scheduled cron tasks
wp_clear_scheduled_hook('okj_daily_cron');

// 4. Remove custom capabilities
$roles = ['administrator'];
$caps = [
    'okj_manage',
    'okj_view_dashboard',
    'okj_view_prices',
    'okj_manage_prices',
    'okj_view_resellers',
    'okj_manage_resellers',
    'okj_view_customers',
    'okj_manage_customers',
    'okj_view_sellers',
    'okj_manage_sellers',
    'okj_view_active_products',
    'okj_manage_active_products',
    'okj_view_reminders',
    'okj_manage_reminders',
    'okj_view_shortlinks',
    'okj_manage_shortlinks',
    'okj_view_reports',
    'okj_view_logs',
    'okj_manage_settings',
];

foreach ($roles as $role_name) {
    $role = get_role($role_name);
    if ($role) {
        foreach ($caps as $cap) {
            $role->remove_cap($cap);
        }
    }
}

// 5. Remove custom OKJualan manager role
remove_role('okj_manager');

