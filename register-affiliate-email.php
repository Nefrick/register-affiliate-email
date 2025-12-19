<?php
/**
 * Plugin Name: Register Affiliate Email
 * Plugin URI: https://github.com/Nefrick/register-affiliate-email
 * Description: A flexible email subscription form with multiple service integrations (AWeber, Customer.io, etc.)
 * Version: 0.3.0
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
define('RAE_VERSION', '0.3.0');
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
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    Plugin::getInstance()->deactivate();
});

add_action('wp_enqueue_scripts', function() {
    // Get the active template slug
    if (class_exists('RegisterAffiliateEmail\\Frontend\\TemplateManager')) {
        $template_slug = \RegisterAffiliateEmail\Frontend\TemplateManager::getActiveTemplate();
    } else {
        $template_slug = 'default';
    }

    // Style paths
    $theme_style = get_stylesheet_directory() . "/register-affiliate-email/{$template_slug}/assets/style.css";
    $plugin_style = plugin_dir_path(__FILE__) . "templates/{$template_slug}/assets/style.css";

    // Enqueue style: theme first, then plugin fallback
    if (file_exists($theme_style)) {
        wp_enqueue_style('rae-template-style', get_stylesheet_directory_uri() . "/register-affiliate-email/{$template_slug}/assets/style.css", [], null);
    } elseif (file_exists($plugin_style)) {
        wp_enqueue_style('rae-template-style', plugins_url("templates/{$template_slug}/assets/style.css", __FILE__), [], null);
    }

    // JS paths
    $theme_js = get_stylesheet_directory() . "/register-affiliate-email/{$template_slug}/assets/frontend.js";
    $plugin_js = plugin_dir_path(__FILE__) . "templates/{$template_slug}/assets/frontend.js";

    $js_handle = 'rae-template-js';
    $js_loaded = false;
    if (file_exists($theme_js)) {
        wp_enqueue_script($js_handle, get_stylesheet_directory_uri() . "/register-affiliate-email/{$template_slug}/assets/frontend.js", [], null, true);
        $js_loaded = true;
    } elseif (file_exists($plugin_js)) {
        wp_enqueue_script($js_handle, plugins_url("templates/{$template_slug}/assets/frontend.js", __FILE__), [], null, true);
        $js_loaded = true;
    }

    // Localize raeConfig for the template JS
    if ($js_loaded) {
        $settings = class_exists('RegisterAffiliateEmail\\Admin\\Settings')
            ? \RegisterAffiliateEmail\Admin\Settings::getSettings()
            : [];
        wp_localize_script($js_handle, 'raeConfig', [
            'apiUrl' => esc_url_raw(rest_url()),
            'messages' => [
                'required'  => isset($settings['input_placeholder']) ? $settings['input_placeholder'] : __('Email required', 'register-affiliate-email'),
                'invalid'   => __('Invalid email', 'register-affiliate-email'),
                'agreement' => isset($settings['agreement_text']) ? $settings['agreement_text'] : __('Please accept the agreement', 'register-affiliate-email'),
                'success'   => isset($settings['success_message']) ? $settings['success_message'] : __('Thank you!', 'register-affiliate-email'),
                'error'     => __('Error. Try again.', 'register-affiliate-email'),
            ],
        ]);
    }
});
