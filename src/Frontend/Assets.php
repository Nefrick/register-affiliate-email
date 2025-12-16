<?php
/**
 * Frontend Assets Handler
 *
 * @package RegisterAffiliateEmail\Frontend
 */

namespace RegisterAffiliateEmail\Frontend;

class Assets {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueueAssets() {
        // Only enqueue if shortcode is present
        global $post;
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'register_affiliate_email')) {
            return;
        }

        // Get active template
        $settings = \RegisterAffiliateEmail\Admin\Settings::getSettings();
        $active_template = $settings['active_template'] ?? 'default';

        // Main frontend CSS
        wp_enqueue_style(
            'rae-frontend',
            RAE_PLUGIN_URL . 'assets/frontend.css',
            [],
            RAE_VERSION
        );

        // Template-specific CSS (if exists)
        if ($active_template === 'fortune' && file_exists(RAE_PLUGIN_DIR . 'templates/fortune/assets/style.css')) {
            wp_enqueue_style(
                'rae-fortune',
                RAE_PLUGIN_URL . 'templates/fortune/assets/style.css',
                ['rae-frontend'],
                RAE_VERSION
            );
        }

        // Main frontend JS
        wp_enqueue_script(
            'rae-frontend',
            RAE_PLUGIN_URL . 'assets/frontend.js',
            [],
            RAE_VERSION,
            true
        );

        // Template-specific JS (if exists)
        if ($active_template === 'fortune' && file_exists(RAE_PLUGIN_DIR . 'templates/fortune/assets/script.js')) {
            wp_enqueue_script(
                'rae-fortune',
                RAE_PLUGIN_URL . 'templates/fortune/assets/script.js',
                ['rae-frontend'],
                RAE_VERSION,
                true
            );
        }

        wp_localize_script('rae-frontend', 'raeConfig', [
            'apiUrl' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'messages' => [
                'required' => __('Please enter your email address.', 'register-affiliate-email'),
                'invalid' => __('Please enter a valid email address.', 'register-affiliate-email'),
                'agreement' => __('Please accept the agreement to continue.', 'register-affiliate-email'),
                'success' => __('Thank you for subscribing!', 'register-affiliate-email'),
                'error' => __('An error occurred. Please try again.', 'register-affiliate-email')
            ]
        ]);
    }
}
