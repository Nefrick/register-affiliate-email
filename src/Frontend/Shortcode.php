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
        
        $background_style = '';
        if (!empty($settings['background_image'])) {
            $background_style = sprintf(
                'background-image: url(%s); background-size: cover; background-position: center;',
                esc_url($settings['background_image'])
            );
        }

        ob_start();
        ?>
        <div class="rae-form-container" style="<?php echo esc_attr($background_style); ?>">
            <form class="rae-subscription-form" data-rae-form>
                <div class="rae-form-inner">
                    <div class="rae-form-group">
                        <input 
                            type="email" 
                            name="email" 
                            class="rae-email-input" 
                            placeholder="<?php echo esc_attr($settings['input_placeholder']); ?>"
                            required
                        />
                        <button type="submit" class="rae-submit-button">
                            <?php echo esc_html($settings['button_text']); ?>
                        </button>
                    </div>
                    <div class="rae-message" data-rae-message style="display: none;"></div>
                    <div class="rae-loading" data-rae-loading style="display: none;">
                        <?php _e('Submitting...', 'register-affiliate-email'); ?>
                    </div>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
