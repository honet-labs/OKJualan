<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('OKJ_DB')) {

class OKJ_DB {
    public static function get_table($name) {
        global $wpdb;
        return $wpdb->prefix . 'okj_' . $name;
    }

    public static function install() {
        if (!function_exists('dbDelta')) {
            if (defined('ABSPATH') && file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            }
        }
        if (!function_exists('dbDelta')) {
            return;
        }

        global $wpdb;

        // Auto migrate old OKJ tables to new OKJ tables
        try {
            $old_prefix = 'wrp' . 'm_';
            $new_prefix = 'ok' . 'j_';
            $old_tables = ['product_prices', 'reseller_products', 'customers', 'sellers', 'active_products', 'active_reminders', 'logs', 'shortlinks'];
            foreach ($old_tables as $t) {
                $old_table = $wpdb->prefix . $old_prefix . $t;
                $new_table = $wpdb->prefix . $new_prefix . $t;
                if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $old_table)) === $old_table) {
                    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $new_table)) !== $new_table) {
                        $wpdb->query("RENAME TABLE {$old_table} TO {$new_table}");
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore rename exception
        }

        // Migrate old settings
        $old_settings_key = $old_prefix . 'settings_v1';
        $new_settings_key = $new_prefix . 'settings_v1';
        $old_settings = get_option($old_settings_key);
        if ($old_settings !== false && get_option($new_settings_key) === false) {
            update_option($new_settings_key, $old_settings);
        }

        // Migrate old db version
        $old_db_ver_key = $old_prefix . 'db_version';
        $new_db_ver_key = $new_prefix . 'db_version';
        $old_db_ver = get_option($old_db_ver_key);
        if ($old_db_ver !== false && get_option($new_db_ver_key) === false) {
            update_option($new_db_ver_key, $old_db_ver);
        }

        $charset = $wpdb->get_charset_collate();

        $t_prices = self::get_table('product_prices');
        $t_reseller = self::get_table('reseller_products');
        $t_customers = self::get_table('customers');
        $t_sellers = self::get_table('sellers');
        $t_active = self::get_table('active_products');
        $t_reminders = self::get_table('active_reminders');
        $t_logs = self::get_table('logs');
        $t_shortlinks = self::get_table('shortlinks');
        $t_pos_transactions = self::get_table('pos_transactions');
        $t_pos_items = self::get_table('pos_transaction_items');
        $t_renewals = self::get_table('active_product_renewals');

        $sql_prices = "CREATE TABLE {$t_prices} (
            id CHAR(36) NOT NULL,
            name VARCHAR(200) NOT NULL,
            category VARCHAR(100) NOT NULL DEFAULT '',
            tags TEXT NULL,
            seller_id CHAR(36) NULL,
            reseller_price BIGINT(20) NOT NULL DEFAULT 0,
            sale_price BIGINT(20) NOT NULL DEFAULT 0,
            stock INT(11) NOT NULL DEFAULT -1,
            duration_days INT(11) NOT NULL DEFAULT 0,
            image_url TEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            affiliate_url TEXT NULL,
            show_in_pos TINYINT(1) NOT NULL DEFAULT 1,
            sync_to_wc TINYINT(1) NOT NULL DEFAULT 0,
            wc_product_id BIGINT(20) NOT NULL DEFAULT 0,
            description LONGTEXT NULL,
            notes LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            updated_by BIGINT(20) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            KEY name (name(80)),
            KEY category (category(40)),
            KEY seller_id (seller_id),
            KEY status (status),
            KEY sync_to_wc (sync_to_wc),
            KEY wc_product_id (wc_product_id)
        ) {$charset};";

        $sql_shortlinks = "CREATE TABLE {$t_shortlinks} (
            id CHAR(36) NOT NULL,
            title VARCHAR(200) NOT NULL,
            short_key VARCHAR(50) NOT NULL,
            destination_url TEXT NOT NULL,
            clicks INT(11) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY short_key (short_key)
        ) {$charset};";

        $sql_reseller = "CREATE TABLE {$t_reseller} (
            id CHAR(36) NOT NULL,
            price_id CHAR(36) NOT NULL,
            product_name VARCHAR(200) NOT NULL,
            category VARCHAR(100) NOT NULL DEFAULT '',
            tags TEXT NULL,
            seller_id CHAR(36) NULL,
            reseller_name VARCHAR(200) NOT NULL DEFAULT '',
            reseller_contact VARCHAR(200) NOT NULL DEFAULT '',
            purchase_date DATE NULL,
            duration_days INT(11) NOT NULL DEFAULT 0,
            description LONGTEXT NULL,
            price BIGINT(20) NOT NULL DEFAULT 0,
            expires_at DATE NULL,
            payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
            payment_attachments LONGTEXT NULL,
            notes LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            updated_by BIGINT(20) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            KEY price_id (price_id),
            KEY seller_id (seller_id),
            KEY expires_at (expires_at),
            KEY payment_status (payment_status)
        ) {$charset};";

        $sql_customers = "CREATE TABLE {$t_customers} (
            id CHAR(36) NOT NULL,
            name VARCHAR(200) NOT NULL,
            email VARCHAR(190) NOT NULL DEFAULT '',
            phone VARCHAR(50) NOT NULL DEFAULT '',
            telegram VARCHAR(100) NOT NULL DEFAULT '',
            whatsapp VARCHAR(50) NOT NULL DEFAULT '',
            address TEXT NULL,
            notes LONGTEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            updated_by BIGINT(20) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            KEY email (email(80)),
            KEY name (name(80)),
            KEY status (status)
        ) {$charset};";

        $sql_sellers = "CREATE TABLE {$t_sellers} (
            id CHAR(36) NOT NULL,
            name VARCHAR(200) NOT NULL,
            email VARCHAR(190) NOT NULL DEFAULT '',
            phone VARCHAR(50) NOT NULL DEFAULT '',
            telegram VARCHAR(100) NOT NULL DEFAULT '',
            whatsapp VARCHAR(50) NOT NULL DEFAULT '',
            address TEXT NULL,
            notes LONGTEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            updated_by BIGINT(20) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            KEY email (email(80)),
            KEY name (name(80)),
            KEY status (status)
        ) {$charset};";

        $sql_active = "CREATE TABLE {$t_active} (
            id CHAR(36) NOT NULL,
            reseller_product_id CHAR(36) NOT NULL DEFAULT '',
            product_id CHAR(36) NULL,
            product_label VARCHAR(255) NOT NULL,
            customer_id CHAR(36) NOT NULL,
            customer_name VARCHAR(200) NOT NULL,
            customer_contact VARCHAR(200) NOT NULL DEFAULT '',
            start_date DATE NOT NULL,
            qty INT(11) NOT NULL DEFAULT 1,
            duration_days INT(11) NOT NULL DEFAULT 0,
            expires_at DATE NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            price BIGINT(20) NOT NULL DEFAULT 0,
            payment_status VARCHAR(20) NOT NULL DEFAULT 'pending',
            payment_attachments LONGTEXT NULL,
            notes LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            updated_by BIGINT(20) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            KEY expires_at (expires_at),
            KEY status (status),
            KEY customer_id (customer_id),
            KEY product_id (product_id),
            KEY reseller_product_id (reseller_product_id)
        ) {$charset};";

        $sql_reminders = "CREATE TABLE {$t_reminders} (
            id CHAR(36) NOT NULL,
            active_product_id CHAR(36) NOT NULL,
            customer_id CHAR(36) NOT NULL,
            offset_days INT(11) NOT NULL DEFAULT 0,
            reminder_date DATE NOT NULL,
            remaining_days INT(11) NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            sent_via VARCHAR(50) NOT NULL DEFAULT '',
            sent_at DATETIME NULL,
            last_error TEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY active_product_id (active_product_id),
            KEY reminder_date (reminder_date),
            KEY status (status)
        ) {$charset};";

        $sql_logs = "CREATE TABLE {$t_logs} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            happened_at DATETIME NOT NULL,
            user_id BIGINT(20) NOT NULL DEFAULT 0,
            user_login VARCHAR(60) NOT NULL DEFAULT '',
            action VARCHAR(120) NOT NULL DEFAULT '',
            entity VARCHAR(60) NOT NULL DEFAULT '',
            entity_id CHAR(36) NOT NULL DEFAULT '',
            message TEXT NULL,
            meta LONGTEXT NULL,
            ip VARCHAR(45) NOT NULL DEFAULT '',
            PRIMARY KEY  (id),
            KEY happened_at (happened_at),
            KEY action (action(60)),
            KEY entity (entity(40)),
            KEY entity_id (entity_id)
        ) {$charset};";

        $sql_pos_transactions = "CREATE TABLE {$t_pos_transactions} (
            id CHAR(36) NOT NULL,
            transaction_no VARCHAR(50) NOT NULL,
            customer_id CHAR(36) NULL,
            customer_name VARCHAR(200) NOT NULL,
            seller_id CHAR(36) NULL,
            subtotal BIGINT(20) NOT NULL DEFAULT 0,
            discount BIGINT(20) NOT NULL DEFAULT 0,
            tax BIGINT(20) NOT NULL DEFAULT 0,
            total BIGINT(20) NOT NULL DEFAULT 0,
            payment_method VARCHAR(50) NOT NULL,
            payment_status VARCHAR(20) NOT NULL DEFAULT 'paid',
            notes LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            updated_by BIGINT(20) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY transaction_no (transaction_no),
            KEY customer_id (customer_id),
            KEY seller_id (seller_id)
        ) {$charset};";

        $sql_pos_items = "CREATE TABLE {$t_pos_items} (
            id CHAR(36) NOT NULL,
            transaction_id CHAR(36) NOT NULL,
            product_id CHAR(36) NOT NULL,
            product_name VARCHAR(200) NOT NULL,
            price BIGINT(20) NOT NULL DEFAULT 0,
            qty INT(11) NOT NULL DEFAULT 1,
            duration_days INT(11) NOT NULL DEFAULT 0,
            subtotal BIGINT(20) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY transaction_id (transaction_id),
            KEY product_id (product_id)
        ) {$charset};";

        $sql_renewals = "CREATE TABLE {$t_renewals} (
            id CHAR(36) NOT NULL,
            active_product_id CHAR(36) NOT NULL,
            old_expires_at DATE NOT NULL,
            new_expires_at DATE NOT NULL,
            duration_days INT(11) NOT NULL DEFAULT 0,
            price BIGINT(20) NOT NULL DEFAULT 0,
            payment_status VARCHAR(20) NOT NULL DEFAULT 'paid',
            payment_attachments LONGTEXT NULL,
            notes LONGTEXT NULL,
            renewed_at DATETIME NOT NULL,
            updated_by BIGINT(20) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            KEY active_product_id (active_product_id),
            KEY renewed_at (renewed_at)
        ) {$charset};";

        dbDelta($sql_prices);
        dbDelta($sql_shortlinks);
        dbDelta($sql_reseller);
        dbDelta($sql_customers);
        dbDelta($sql_sellers);
        dbDelta($sql_active);
        dbDelta($sql_reminders);
        dbDelta($sql_logs);
        dbDelta($sql_pos_transactions);
        dbDelta($sql_pos_items);
        dbDelta($sql_renewals);

        // Safe auto-migration check for existing tables
        try {
            $existing_tables = $wpdb->get_col("SHOW TABLES");
            if (!is_array($existing_tables)) {
                $existing_tables = [];
            }

            // Prices columns
            if (in_array($t_prices, $existing_tables)) {
                $cols = $wpdb->get_col("SHOW COLUMNS FROM {$t_prices}");
                if (is_array($cols)) {
                    if (!in_array('sync_to_wc', $cols)) {
                        $wpdb->query("ALTER TABLE {$t_prices} ADD COLUMN sync_to_wc TINYINT(1) NOT NULL DEFAULT 0");
                    }
                    if (!in_array('wc_product_id', $cols)) {
                        $wpdb->query("ALTER TABLE {$t_prices} ADD COLUMN wc_product_id BIGINT(20) NOT NULL DEFAULT 0");
                    }
                    if (!in_array('stock', $cols)) {
                        $wpdb->query("ALTER TABLE {$t_prices} ADD COLUMN stock INT(11) NOT NULL DEFAULT -1");
                    }
                    if (!in_array('image_url', $cols)) {
                        $wpdb->query("ALTER TABLE {$t_prices} ADD COLUMN image_url TEXT NULL");
                    }
                    if (!in_array('status', $cols)) {
                        $wpdb->query("ALTER TABLE {$t_prices} ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'");
                    }
                }
            }

            // Sellers columns
            if (in_array($t_sellers, $existing_tables)) {
                $cols = $wpdb->get_col("SHOW COLUMNS FROM {$t_sellers}");
                if (is_array($cols)) {
                    if (!in_array('address', $cols)) {
                        $wpdb->query("ALTER TABLE {$t_sellers} ADD COLUMN address TEXT NULL");
                    }
                    if (!in_array('notes', $cols)) {
                        $wpdb->query("ALTER TABLE {$t_sellers} ADD COLUMN notes LONGTEXT NULL");
                    }
                    if (!in_array('status', $cols)) {
                        $wpdb->query("ALTER TABLE {$t_sellers} ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'");
                    }
                }
            }

            // Customers columns
            if (in_array($t_customers, $existing_tables)) {
                $cols = $wpdb->get_col("SHOW COLUMNS FROM {$t_customers}");
                if (is_array($cols)) {
                    if (!in_array('address', $cols)) {
                        $wpdb->query("ALTER TABLE {$t_customers} ADD COLUMN address TEXT NULL");
                    }
                    if (!in_array('notes', $cols)) {
                        $wpdb->query("ALTER TABLE {$t_customers} ADD COLUMN notes LONGTEXT NULL");
                    }
                    if (!in_array('status', $cols)) {
                        $wpdb->query("ALTER TABLE {$t_customers} ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'");
                    }
                }
            }

            // Active products columns
            if (in_array($t_active, $existing_tables)) {
                $cols = $wpdb->get_col("SHOW COLUMNS FROM {$t_active}");
                if (is_array($cols)) {
                    if (!in_array('product_id', $cols)) {
                        $wpdb->query("ALTER TABLE {$t_active} ADD COLUMN product_id CHAR(36) NULL");
                    }
                    if (!in_array('qty', $cols)) {
                        $wpdb->query("ALTER TABLE {$t_active} ADD COLUMN qty INT(11) NOT NULL DEFAULT 1");
                    }
                }
            }
        } catch (\Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('OKJualan DB auto-migration notice: ' . $e->getMessage());
            }
        }

        // Ensure capabilities and settings are initialized
        self::ensure_caps();
    }

    public static function uninstall() {
        global $wpdb;
        $tables = ['product_prices', 'reseller_products', 'customers', 'sellers', 'active_products', 'active_reminders', 'logs', 'shortlinks', 'pos_transactions', 'pos_transaction_items', 'active_product_renewals'];
        foreach ($tables as $t) {
            $wpdb->query("DROP TABLE IF EXISTS " . self::get_table($t));
        }
        delete_option('okj_settings_v1');
    }

    public static function ensure_caps() {
        try {
            $admin = get_role('administrator');
            if ($admin) {
                $admin->add_cap('okj_manage');
                $admin->add_cap('okj_view_reports');
                $admin->add_cap('okj_manage_settings');
                $admin->add_cap('okj_view_logs');
            }

            if (!get_role('okj_manager')) {
                add_role('okj_manager', 'OKJualan Manager', [
                    'okj_manage' => true,
                    'okj_view_reports' => true,
                    'okj_view_logs' => true,
                ]);
            }
        } catch (\Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('OKJualan ensure_caps notice: ' . $e->getMessage());
            }
        }
    }
}
}
