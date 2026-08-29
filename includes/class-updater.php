<?php
if (!defined('ABSPATH')) { exit; }

class OKJ_Updater {
    private $file;
    private $slug;
    private $repo;
    private $token;

    public function __construct($file) {
        $this->file = $file;
        $this->slug = plugin_basename($file);

        $settings = get_option('okj_settings_v1', []);
        $this->repo = !empty($settings['github_repo']) ? trim((string)$settings['github_repo']) : '';
        $this->token = !empty($settings['github_token']) ? trim((string)$settings['github_token']) : '';

        if (!empty($this->repo)) {
            add_filter('pre_set_site_transient_update_plugins', [$this, 'check_update']);
            add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
            add_filter('upgrader_source_selection', [$this, 'upgrader_source_selection'], 10, 4);
            add_filter('http_request_args', [$this, 'authenticate_github_downloads'], 10, 2);
        }
    }

    /**
     * Attach Authorization header to GitHub API and download requests if token is configured
     */
    public function authenticate_github_downloads($args, $url) {
        if (!empty($this->token)) {
            if (is_string($url) && (strpos($url, 'api.github.com') !== false || strpos($url, 'codeload.github.com') !== false)) {
                if (!isset($args['headers']) || !is_array($args['headers'])) {
                    $args['headers'] = [];
                }
                $args['headers']['Authorization'] = 'token ' . $this->token;
            }
        }
        return $args;
    }

    /**
     * Rename the extracted folder from GitHub to match active plugin folder name
     */
    public function upgrader_source_selection($source, $remote_source, $upgrader, $hook_extra = null) {
        if (empty($hook_extra['plugin']) || strtolower($hook_extra['plugin']) !== strtolower($this->slug)) {
            return $source;
        }

        global $wp_filesystem;
        $plugin_dir = dirname($hook_extra['plugin']);
        $corrected_source = trailingslashit(dirname($source)) . $plugin_dir;

        // Move and rename the GitHub extracted folder to match active plugin folder name
        if ($wp_filesystem && $wp_filesystem->move($source, $corrected_source, true)) {
            return trailingslashit($corrected_source);
        }

        return $source;
    }

    /**
     * Hook into WordPress transient update checker (FAST & 100% CACHED)
     */
    public function check_update($transient) {
        if (empty($transient) || !is_object($transient) || empty($transient->checked)) {
            return $transient;
        }

        // Never make slow synchronous remote HTTP calls on every page load - use cache
        $remote = self::get_remote_info_cached($this->repo, $this->token);
        if (!$remote || empty($remote['version']) || empty($remote['download_url'])) {
            return $transient;
        }

        $current = OKJ_App::VERSION;
        $new_ver = $remote['version'];

        if (version_compare($current, $new_ver, '<')) {
            $obj = new stdClass();
            $obj->slug = 'okjualin';
            $obj->plugin = $this->slug;
            $obj->new_version = $new_ver;
            $obj->url = 'https://github.com/' . $this->repo;
            $obj->package = $remote['download_url'];
            $obj->tested = get_bloginfo('version');
            $obj->requires = '5.8';
            $obj->requires_php = '7.4';

            if (!isset($transient->response) || !is_array($transient->response)) {
                $transient->response = [];
            }
            $transient->response[$this->slug] = $obj;
        }

        return $transient;
    }

    /**
     * Provide detailed information for WordPress plugin modal popup
     */
    public function plugin_info($res, $action, $args) {
        if ($action !== 'plugin_information') return $res;
        if (empty($args->slug) || $args->slug !== 'okjualin') return $res;

        $remote = self::get_remote_info_cached($this->repo, $this->token);
        if (!$remote) return $res;

        $res = new stdClass();
        $res->name = 'OKJualin';
        $res->slug = 'okjualin';
        $res->version = $remote['version'];
        $res->author = 'HONET';
        $res->homepage = 'https://github.com/' . $this->repo;
        $res->download_link = $remote['download_url'];
        $res->sections = [
            'description' => 'Sistem Manajemen Reseller, Master Harga, Point of Sale (POS), dan Notifikasi Produk Aktif.',
            'changelog'   => !empty($remote['changelog']) ? $remote['changelog'] : 'Rilis pembaruan versi ' . $remote['version'],
        ];

        return $res;
    }

    /**
     * Cached remote info getter with transient (prevents API spamming & WSOD timeouts)
     */
    public static function get_remote_info_cached($repo, $token = '', $force = false) {
        if (empty($repo)) {
            return null;
        }

        $transient_key = 'okj_gh_rel_info_' . md5($repo);
        if (!$force) {
            $cached = get_transient($transient_key);
            if ($cached !== false) {
                return is_array($cached) ? $cached : null;
            }
        }

        $info = self::fetch_remote_info($repo, $token);

        if ($info && !empty($info['version'])) {
            // Cache valid info for 6 hours
            set_transient($transient_key, $info, 6 * HOUR_IN_SECONDS);
            return $info;
        }

        // Cache failure for 5 minutes so it doesn't retry on every page load
        set_transient($transient_key, 'none', 5 * MINUTE_IN_SECONDS);
        return null;
    }

    /**
     * Quick fetch for UI version display
     */
    public static function get_latest_version_cached($force = false) {
        $settings = get_option('okj_settings_v1', []);
        $repo = !empty($settings['github_repo']) ? trim((string)$settings['github_repo']) : '';
        if (!$repo) {
            return false;
        }

        $token = !empty($settings['github_token']) ? trim((string)$settings['github_token']) : '';
        $info = self::get_remote_info_cached($repo, $token, $force);

        return $info ? $info['version'] : 'unknown';
    }

    /**
     * Robust GitHub remote release detection with short timeout (5s)
     */
    private static function fetch_remote_info($repo, $token = '') {
        if (empty($repo)) return null;

        $headers = [
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
        ];
        if (!empty($token)) {
            $headers['Authorization'] = 'token ' . $token;
        }

        $args = [
            'timeout' => 5,
            'headers' => $headers,
        ];

        // 1. Try Releases API
        $url = 'https://api.github.com/repos/' . $repo . '/releases/latest';
        $resp = wp_remote_get($url, $args);

        if (!is_wp_error($resp) && (int)wp_remote_retrieve_response_code($resp) === 200) {
            $body = wp_remote_retrieve_body($resp);
            $data = json_decode($body, true);
            if (is_array($data) && !empty($data['tag_name'])) {
                $version = ltrim((string)$data['tag_name'], 'v');
                $download_url = $data['zipball_url'] ?? ('https://github.com/' . $repo . '/archive/refs/tags/' . $data['tag_name'] . '.zip');
                
                if (!empty($data['assets'][0]['browser_download_url'])) {
                    $download_url = $data['assets'][0]['browser_download_url'];
                }

                return [
                    'version'      => $version,
                    'tag_name'     => $data['tag_name'],
                    'download_url' => $download_url,
                    'changelog'    => $data['body'] ?? '',
                ];
            }
        }

        // 2. Try Tags API
        $url_tags = 'https://api.github.com/repos/' . $repo . '/tags';
        $resp_tags = wp_remote_get($url_tags, $args);

        if (!is_wp_error($resp_tags) && (int)wp_remote_retrieve_response_code($resp_tags) === 200) {
            $tags = json_decode(wp_remote_retrieve_body($resp_tags), true);
            if (is_array($tags) && !empty($tags[0]['name'])) {
                $tag_name = $tags[0]['name'];
                $version = ltrim((string)$tag_name, 'v');
                $download_url = $tags[0]['zipball_url'] ?? ('https://github.com/' . $repo . '/archive/refs/tags/' . $tag_name . '.zip');

                return [
                    'version'      => $version,
                    'tag_name'     => $tag_name,
                    'download_url' => $download_url,
                    'changelog'    => 'Rilis versi tag ' . $tag_name,
                ];
            }
        }

        // 3. Try Raw okjualin.php file from main branch
        $raw_url = 'https://raw.githubusercontent.com/' . $repo . '/main/okjualin.php';
        $raw_resp = wp_remote_get($raw_url, $args);

        if (!is_wp_error($raw_resp) && (int)wp_remote_retrieve_response_code($raw_resp) === 200) {
            $content = wp_remote_retrieve_body($raw_resp);
            if (preg_match('/Version:\s*([0-9\.]+)/i', $content, $m)) {
                $version = trim($m[1]);
                return [
                    'version'      => $version,
                    'tag_name'     => 'v' . $version,
                    'download_url' => 'https://github.com/' . $repo . '/archive/refs/heads/main.zip',
                    'changelog'    => 'Pembaruan terkini dari branch main.',
                ];
            }
        }

        return null;
    }

    /**
     * Execute 1-Click Update Programmatically via WordPress Upgrader API
     */
    public static function run_update() {
        if (!current_user_can('update_plugins')) {
            return new WP_Error('forbidden', 'Anda tidak memiliki hak akses yang cukup untuk memperbarui plugin ini.');
        }

        $settings = get_option('okj_settings_v1', []);
        $repo = !empty($settings['github_repo']) ? trim((string)$settings['github_repo']) : '';
        if (!$repo) {
            return new WP_Error('no_repo', 'Repositori GitHub belum dikonfigurasi pada menu pengaturan.');
        }

        $token = !empty($settings['github_token']) ? trim((string)$settings['github_token']) : '';

        // Force fetch remote release info
        $remote = self::get_remote_info_cached($repo, $token, true);
        if (!$remote || empty($remote['download_url'])) {
            return new WP_Error('no_package', 'Tidak dapat menemukan paket rilis atau berkas unduhan di GitHub. Pastikan nama repositori dan token valid.');
        }

        $current_ver = OKJ_App::VERSION;
        $new_ver = $remote['version'];

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $skin = new Automatic_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader($skin);

        // Temporarily hook source selection to fix folder name
        $plugin_file = OKJ_PLUGIN_BASENAME;
        add_filter('upgrader_source_selection', function($source, $remote_source, $upgrader_instance, $hook_extra = null) use ($plugin_file) {
            global $wp_filesystem;
            $plugin_dir = dirname($plugin_file);
            $corrected_source = trailingslashit(dirname($source)) . $plugin_dir;
            if ($wp_filesystem && $wp_filesystem->move($source, $corrected_source, true)) {
                return trailingslashit($corrected_source);
            }
            return $source;
        }, 10, 4);

        $result = $upgrader->upgrade($plugin_file, [
            'package'           => $remote['download_url'],
            'destination'       => WP_PLUGIN_DIR,
            'clear_destination' => true,
            'clear_working'     => true,
            'hook_extra'        => [
                'plugin' => $plugin_file,
                'type'   => 'plugin',
                'action' => 'update',
            ],
        ]);

        if (is_wp_error($result)) {
            return $result;
        }

        if ($result === false || !empty($skin->get_errors()->get_error_codes())) {
            $errors = $skin->get_errors();
            $msg = !empty($errors->get_error_message()) ? $errors->get_error_message() : 'Terjadi kegagalan saat mengekstrak dan memasang pembaruan.';
            return new WP_Error('upgrade_failed', $msg);
        }

        // Ensure plugin remains active
        if (!is_plugin_active($plugin_file)) {
            activate_plugin($plugin_file);
        }

        // Run database migrations and capabilities provisioning
        OKJ_DB::install();
        OKJ_DB::ensure_caps();
        update_option('okj_db_version', $new_ver);

        // Log the upgrade action
        OKJ_Reseller_Manager::log('system_update', 'plugin', $plugin_file, "Plugin OKJualin berhasil diperbarui dari v{$current_ver} ke v{$new_ver}");

        // Clear transient
        delete_transient('okj_gh_rel_info_' . md5($repo));
        delete_site_transient('update_plugins');

        return [
            'success'     => true,
            'old_version' => $current_ver,
            'new_version' => $new_ver,
            'message'     => "Selamat! OKJualin berhasil diperbarui ke versi v{$new_ver}.",
        ];
    }
}
