<?php
/**
 * Settings Handler
 *
 * @package RegisterAffiliateEmail\Admin
 */

namespace RegisterAffiliateEmail\Admin;

class Settings {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueueAdminAssets($hook) {
        if ($hook !== 'toplevel_page_register-affiliate-email') {
            return;
        }

        // Enqueue WordPress media uploader
        wp_enqueue_media();

        // Enqueue admin script
        wp_enqueue_script(
            'rae-admin',
            RAE_PLUGIN_URL . 'assets/admin.js',
            [],
            RAE_VERSION,
            true
        );

        wp_enqueue_style(
            'rae-admin',
            RAE_PLUGIN_URL . 'assets/admin.css',
            [],
            RAE_VERSION
        );
    }

    /**
     * Get current form settings
     *
     * @return array
     */
    public static function getSettings() {
        return get_option('rae_form_settings', [
            'input_placeholder' => __('Enter your email', 'register-affiliate-email'),
            'button_text' => __('Subscribe', 'register-affiliate-email'),
            'background_image' => '',
            'enabled_services' => []
        ]);
    }
}
