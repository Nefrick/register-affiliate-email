<?php
/**
 * Plugin Name: Register Affiliate Email
 * Plugin URI: https://github.com/Nefrick/register-affiliate-email
 * Description: A flexible email subscription form with multiple service integrations (AWeber, Customer.io, etc.)
 * Version: 1.0.2
 * Author: Michael Chizhevskiy
 * Author URI: https://github.com/Nefrick/register-affiliate-email
 * Text Domain: register-affiliate-email
 * Domain Path: /languages
 * Requires at least: 5.8
 * Tested up to: 6.9
 * Requires PHP: 7.4
 */

namespace RegisterAffiliateEmail;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RAE_VERSION', '1.0.2');
define('RAE_PLUGIN_FILE', __FILE__);
define('RAE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RAE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RAE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
spl_autoload_register(static function ($class) {
    $prefix = 'RegisterAffiliateEmail\\';
    $baseDir = RAE_PLUGIN_DIR . 'src/';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (is_readable($file)) {
        require_once $file;
    }
});

// Initialize plugin
add_action('plugins_loaded', function() {
    Plugin::getInstance()->init();
    // Register AJAX export handler
    if (is_admin()) {
        \RegisterAffiliateEmail\Admin\FailedSubscriptionsPage::register_ajax();
    }
});

// Activation hook
register_activation_hook(__FILE__, [Plugin::class, 'activate']);

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    Plugin::getInstance()->deactivate();
});

