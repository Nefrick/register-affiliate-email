<?php
/**
 * Frontend Shortcode
 *
 * @package RegisterAffiliateEmail\Frontend
 */

namespace RegisterAffiliateEmail\Frontend;

class Shortcode {
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('register_affiliate_email', [$this, 'render']);
    }

    /**
     * Render shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render($atts) {
        $settings = \RegisterAffiliateEmail\Admin\Settings::getSettings();
        
        // Honeypot field (hidden from users, catches bots)
        $honeypot = '<input type="text" name="website" value="" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">';
        
        // Get active template
        $template_slug = TemplateManager::getActiveTemplate();
        
        // Load template
        return TemplateManager::loadTemplate($template_slug, [
            'settings' => $settings,
            'nonce_field' => '', // Not needed for REST API
            'honeypot' => $honeypot
        ]);
    }
}
