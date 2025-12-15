<?php
/**
 * Multilanguage Support
 *
 * @package RegisterAffiliateEmail\Integrations
 */

namespace RegisterAffiliateEmail\Integrations;

class Multilanguage {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', [$this, 'registerSupport']);
    }

    /**
     * Register multilanguage plugin support
     */
    public function registerSupport() {
        // WPML Support
        if (defined('ICL_SITEPRESS_VERSION')) {
            $this->registerWPMLSupport();
        }

        // Polylang Support
        if (function_exists('pll_current_language')) {
            $this->registerPolylangSupport();
        }
    }

    /**
     * Register WPML support
     */
    private function registerWPMLSupport() {
        // Register strings for translation
        add_action('admin_init', function() {
            if (function_exists('icl_register_string')) {
                $settings = \RegisterAffiliateEmail\Admin\Settings::getSettings();
                
                icl_register_string('register-affiliate-email', 'input_placeholder', $settings['input_placeholder']);
                icl_register_string('register-affiliate-email', 'button_text', $settings['button_text']);
                icl_register_string('register-affiliate-email', 'form_heading', $settings['form_heading']);
                icl_register_string('register-affiliate-email', 'form_subheading', $settings['form_subheading']);
                icl_register_string('register-affiliate-email', 'agreement_text', $settings['agreement_text']);
                icl_register_string('register-affiliate-email', 'success_message', $settings['success_message']);
            }
        });

        // Translate strings on frontend
        add_filter('rae_setting_value', [$this, 'translateWPMLString'], 10, 2);
    }

    /**
     * Register Polylang support
     */
    private function registerPolylangSupport() {
        // Register strings for translation
        add_action('admin_init', function() {
            if (function_exists('pll_register_string')) {
                $settings = \RegisterAffiliateEmail\Admin\Settings::getSettings();
                
                pll_register_string('input_placeholder', $settings['input_placeholder'], 'register-affiliate-email');
                pll_register_string('button_text', $settings['button_text'], 'register-affiliate-email');
                pll_register_string('form_heading', $settings['form_heading'], 'register-affiliate-email');
                pll_register_string('form_subheading', $settings['form_subheading'], 'register-affiliate-email');
                pll_register_string('agreement_text', $settings['agreement_text'], 'register-affiliate-email');
                pll_register_string('success_message', $settings['success_message'], 'register-affiliate-email');
            }
        });

        // Translate strings on frontend
        add_filter('rae_setting_value', [$this, 'translatePolylangString'], 10, 2);
    }

    /**
     * Translate string via WPML
     *
     * @param string $value Original value
     * @param string $key Setting key
     * @return string Translated value
     */
    public function translateWPMLString($value, $key) {
        if (function_exists('icl_t')) {
            return icl_t('register-affiliate-email', $key, $value);
        }
        return $value;
    }

    /**
     * Translate string via Polylang
     *
     * @param string $value Original value
     * @param string $key Setting key
     * @return string Translated value
     */
    public function translatePolylangString($value, $key) {
        if (function_exists('pll__')) {
            return pll__($value);
        }
        return $value;
    }

    /**
     * Get current language code
     *
     * @return string Language code
     */
    public static function getCurrentLanguage() {
        // WPML
        if (defined('ICL_LANGUAGE_CODE')) {
            return ICL_LANGUAGE_CODE;
        }

        // Polylang
        if (function_exists('pll_current_language')) {
            return pll_current_language();
        }

        // WordPress locale fallback
        return get_locale();
    }
}
