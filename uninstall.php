<?php
if (!defined('WP_UNINSTALL_PLUGIN')) { exit; }

// Load plugin main to access uninstall_schema.
require_once __DIR__ . '/okjualin.php';

if (function_exists('okj_app')) {
    $app = okj_app();
    if ($app && method_exists($app, 'uninstall_schema')) {
        $app->uninstall_schema();
    }
}
