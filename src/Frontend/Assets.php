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
        if ($active_template !== 'default') {
            $css_url = $this->getTemplateAssetUrl($active_template, 'style.css');
            if ($css_url) {
                wp_enqueue_style(
                    'rae-' . $active_template,
                    $css_url,
                    ['rae-frontend'],
                    RAE_VERSION
                );
            }
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
        if ($active_template !== 'default') {
            $js_url = $this->getTemplateAssetUrl($active_template, 'script.js');
            if ($js_url) {
                wp_enqueue_script(
                    'rae-' . $active_template,
                    $js_url,
                    ['rae-frontend'],
                    RAE_VERSION,
                    true
                );
            }
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

    /**
     * Get template asset URL (checks theme first, then plugin)
     *
     * @param string $template_slug Template slug
     * @param string $filename Asset filename (e.g., 'style.css', 'script.js')
     * @return string|false Asset URL or false if not found
     */
    private function getTemplateAssetUrl($template_slug, $filename) {
        // Check theme directory first
        $theme_path = get_stylesheet_directory() . "/register-affiliate-email/{$template_slug}/assets/{$filename}";
        if (file_exists($theme_path)) {
            return get_stylesheet_directory_uri() . "/register-affiliate-email/{$template_slug}/assets/{$filename}";
        }

        // Check plugin directory
        $plugin_path = RAE_PLUGIN_DIR . "templates/{$template_slug}/assets/{$filename}";
        if (file_exists($plugin_path)) {
            return RAE_PLUGIN_URL . "templates/{$template_slug}/assets/{$filename}";
        }

        return false;
    }
}
