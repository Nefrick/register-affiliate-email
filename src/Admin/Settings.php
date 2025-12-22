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
        $settings = get_option('rae_form_settings', [
            'enable_rate_limit' => true,
            'input_placeholder' => __('Enter your email', 'register-affiliate-email'),
            'button_text' => __('Subscribe', 'register-affiliate-email'),
            'form_heading' => '',
            'form_subheading' => '',
            'background_image' => '',
            'button_color' => '#0073aa', // default button color (WP blue)
            'show_agreement' => false,
            'agreement_text' => __('By subscribing, I accept the Terms and Privacy Policy and confirm that I am at least 19 years old.', 'register-affiliate-email'),
            'success_message' => __('Thank you for subscribing! Please check your email for confirmation.', 'register-affiliate-email'),
            'active_template' => 'default',
            'enabled_services' => [],
            'enabled_post_types' => ['post'], // default:  post
            'submission_limit' => 100,
            'submission_period' => 'hour',
        ]);

        // Apply translations to dynamic content
        foreach (['input_placeholder', 'button_text', 'form_heading', 'form_subheading', 'agreement_text', 'success_message'] as $key) {
            if (isset($settings[$key]) && !empty($settings[$key])) {
                $settings[$key] = \RegisterAffiliateEmail\Translations\TranslationsManager::translateByKey($key, $settings[$key]);
            }
        }

        return $settings;
    }
}
