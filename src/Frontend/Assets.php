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

        wp_enqueue_style(
            'rae-frontend',
            RAE_PLUGIN_URL . 'assets/frontend.css',
            [],
            RAE_VERSION
        );

        wp_enqueue_script(
            'rae-frontend',
            RAE_PLUGIN_URL . 'assets/frontend.js',
            [],
            RAE_VERSION,
            true
        );

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
