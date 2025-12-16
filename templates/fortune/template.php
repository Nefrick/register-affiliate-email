<?php
/**
 * Template Name: Fortune Wheel
 * Description: Interactive wheel of fortune that reveals subscription form after spin
 *
 * @package RegisterAffiliateEmail
 * @var array $settings Form settings
 * @var string $nonce_field Nonce field HTML
 * @var string $honeypot Honeypot field HTML
 */

// Fortune-specific settings
$fortune_heading = !empty($settings['form_heading']) ? $settings['form_heading'] : __('Spin the Wheel!', 'register-affiliate-email');
$fortune_button = !empty($settings['button_text']) ? $settings['button_text'] : __('SPIN NOW', 'register-affiliate-email');
?>

<div class="rae-fortune-container" data-spinning="false">
    <div class="rae-fortune-wheel">
        <div class="rae-fortune-content">
            <div class="rae-fortune-inner">
                <h2 class="rae-fortune-initial-text">
                    Every spin is a win — rewards are guaranteed!
                </h2>

                <button type="button" class="rae-fortune-spin-btn">
                    <?php echo esc_html($fortune_button); ?>
                </button>

                <!-- Form (hidden initially, shown after spin) -->
                <form class="rae-subscription-form rae-fortune-form" data-rae-form style="display: none;">
                    <?php if (!empty($fortune_heading)) : ?>
                        <h2 class="rae-fortune-form-heading">
                            <?php echo wp_kses_post($fortune_heading); ?>
                        </h2>
                    <?php endif; ?>

                    <?php if (!empty($settings['form_subheading'])) : ?>
                        <p class="rae-fortune-form-subheading">
                            <?php echo wp_kses_post($settings['form_subheading']); ?>
                        </p>
                    <?php endif; ?>

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

                    <?php if (!empty($settings['show_agreement']) && !empty($settings['agreement_text'])) : ?>
                        <div class="rae-agreement">
                            <label class="rae-checkbox">
                                <input type="checkbox" name="agreement" value="yes" required>
                                <span class="rae-checkbox-label">
                                    <?php echo wp_kses_post($settings['agreement_text']); ?>
                                </span>
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="rae-message" data-rae-message style="display: none;"></div>
                    <div class="rae-loading" data-rae-loading style="display: none;">
                        <?php echo \RegisterAffiliateEmail\Translations\TranslationsManager::__('submitting', 'Submitting...'); ?>
                    </div>

                    <?php echo $honeypot; ?>
                    <?php echo $nonce_field; ?>
                </form>
            </div>

            <!-- Wheel -->
            <div class="rae-wheel-container">
                <div class="rae-wheel-pointer"></div>
                <div class="rae-wheel"></div>
            </div>
        </div>
    </div>
</div>
