<?php
/**
 * Main Plugin Class
 *
 * @package RegisterAffiliateEmail
 */

namespace RegisterAffiliateEmail;

class Plugin {
    /**
     * Single instance
     *
     * @var Plugin
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return Plugin
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to enforce singleton
     */
    private function __construct() {
        // Prevent direct instantiation
    }

    /**
     * Initialize plugin components
     */
    public function init() {
        // Load text domain
        add_action('init', [$this, 'loadTextDomain']);

        // ...existing code...
        
        // Initialize components
        new Admin\Menu();
        new Admin\Settings();
        new Admin\MetaBox();
        new PostTypes\ServiceCPT();
        new Frontend\Shortcode();
        new Frontend\Assets();
        new Integrations\ServiceManager();
        new Integrations\Multilanguage();
        // Initialize REST API
        new API\SubscriptionEndpoint();
        // Initialize update checker
        if (is_admin()) {
            new Updates\UpdateChecker();
        }
    }

    /**
     * Load plugin text domain
     */
    public function loadTextDomain() {
        load_plugin_textdomain(
            'register-affiliate-email',
            false,
            dirname(RAE_PLUGIN_BASENAME) . '/languages'
        );
    }

    /**
     * Plugin activation
     */
    public static function activate() {
        // Register custom post type
        $cpt = new PostTypes\ServiceCPT();
        $cpt->register();

        // Create failed subscriptions table
        if (class_exists('RegisterAffiliateEmail\\Admin\\FailedSubscriptionsTable')) {
            \RegisterAffiliateEmail\Admin\FailedSubscriptionsTable::install();
        } else {
            require_once RAE_PLUGIN_DIR . 'src/Admin/FailedSubscriptionsTable.php';
            \RegisterAffiliateEmail\Admin\FailedSubscriptionsTable::install();
        }

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set default options
        if (!get_option('rae_form_settings')) {
            update_option('rae_form_settings', [
                'input_placeholder' => __('Enter your email', 'register-affiliate-email'),
                'button_text' => __('Subscribe', 'register-affiliate-email'),
                'background_image' => '',
                'enabled_services' => []
            ]);
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
