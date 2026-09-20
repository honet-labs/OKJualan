<?php
if (!defined('ABSPATH')) { exit; }

if (!class_exists('OKJ_Security')) {

class OKJ_Security {

    /**
     * Get encryption key derived from WordPress auth salt
     */
    private static function get_encryption_key() {
        $salt = defined('AUTH_SALT') ? AUTH_SALT : (defined('AUTH_KEY') ? AUTH_KEY : 'okjualan-secret-key-fallback');
        return hash('sha256', $salt, true);
    }

    /**
     * Encrypt sensitive string (e.g. Gateway API keys, Tokens)
     */
    public static function encrypt($plaintext) {
        if (empty($plaintext)) return '';
        if (!function_exists('openssl_encrypt')) {
            return base64_encode($plaintext);
        }

        $key = self::get_encryption_key();
        try {
            $iv = function_exists('random_bytes') ? random_bytes(16) : (function_exists('openssl_random_pseudo_bytes') ? openssl_random_pseudo_bytes(16) : substr(md5(uniqid(mt_rand(), true)), 0, 16));
        } catch (\Throwable $e) {
            $iv = substr(md5(uniqid(mt_rand(), true)), 0, 16);
        }
        $cipher = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $cipher);
    }

    /**
     * Decrypt sensitive string
     */
    public static function decrypt($ciphertext) {
        if (empty($ciphertext)) return '';
        $raw = base64_decode($ciphertext, true);
        if ($raw === false) return $ciphertext;

        if (!function_exists('openssl_decrypt')) {
            return $raw;
        }

        $key = self::get_encryption_key();
        if (strlen($raw) <= 16) return $ciphertext;

        $iv = substr($raw, 0, 16);
        $cipher = substr($raw, 16);
        $plain = openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $plain !== false ? $plain : $ciphertext;
    }

    /**
     * Rate limiter for public endpoints (prevents spamming/abuse)
     * e.g. Max $max_requests per $window_seconds per IP
     */
    public static function check_rate_limit($action = 'public_order', $max_requests = 10, $window_seconds = 300) {
        $ip = self::get_client_ip();
        $transient_key = 'okj_rl_' . md5($action . '_' . $ip);

        $requests = (int)get_transient($transient_key);
        if ($requests >= $max_requests) {
            return false;
        }

        set_transient($transient_key, $requests + 1, $window_seconds);
        return true;
    }

    /**
     * Get validated client IP address
     */
    public static function get_client_ip() {
        $candidates = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($candidates as $k) {
            if (!empty($_SERVER[$k])) {
                $ip = sanitize_text_field($_SERVER[$k]);
                // In case of multiple IPs in X-Forwarded-For
                if (strpos($ip, ',') !== false) {
                    $parts = explode(',', $ip);
                    $ip = trim($parts[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '127.0.0.1';
    }
}
}
