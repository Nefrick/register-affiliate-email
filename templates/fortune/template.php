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


// Clear cache to ensure fresh translations load
\RegisterAffiliateEmail\Translations\TranslationsManager::clearCache();

// Load template-specific translations
$current_locale = get_locale();
$template_translations = \RegisterAffiliateEmail\Translations\TranslationsManager::loadTemplateTranslations('fortune', $current_locale);


// Fortune-specific settings - translations override admin settings
$fortune_heading = $template_translations['form_heading'] ?? (!empty($settings['form_heading']) ? $settings['form_heading'] : __('Spin the Wheel!', 'register-affiliate-email'));
$fortune_subheading = $template_translations['form_subheading'] ?? (!empty($settings['form_subheading']) ? $settings['form_subheading'] : '');
$input_placeholder = $template_translations['input_placeholder'] ?? (!empty($settings['input_placeholder']) ? $settings['input_placeholder'] : '');
$button_text = $template_translations['button_text'] ?? (!empty($settings['button_text']) ? $settings['button_text'] : '');
$agreement_text = $template_translations['agreement_text'] ?? (!empty($settings['agreement_text']) ? $settings['agreement_text'] : '');
$initial_text = $template_translations['initial_text'] ?? 'Every spin is a win — rewards are guaranteed!';
$spin_button_text = $template_translations['spin_button'] ?? 'SPIN NOW';
?>

<div class="rae-fortune-container" data-spinning="false">
    <div class="rae-fortune-wheel">
        <div class="rae-fortune-content">
            <div class="rae-fortune-inner">
                <h2 class="rae-fortune-initial-text" style="color: <?php echo esc_attr($settings['form_text_color'] ?? '#000000'); ?>;">
                    <?php echo esc_html($initial_text); ?>
                </h2>

                <button type="button" class="rae-fortune-spin-btn">
                    <?php echo esc_html($spin_button_text); ?>
                </button>

                <!-- Form (hidden initially, shown after spin) -->
                <form class="rae-subscription-form rae-fortune-form" data-rae-form style="display: none;">
                    <input type="hidden" name="post_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                    <?php if (!empty($fortune_heading)) : ?>
                        <h2 class="rae-fortune-form-heading" style="color: <?php echo esc_attr($settings['form_text_color'] ?? '#000000'); ?>;">
                            <?php echo wp_kses_post($fortune_heading); ?>
                        </h2>
                    <?php endif; ?>

                    <?php if (!empty($fortune_subheading)) : ?>
                        <div class="rae-fortune-subheading" style="color: <?php echo esc_attr($settings['form_text_color'] ?? '#000000'); ?>;">
                            <?php echo wp_kses_post($fortune_subheading); ?>
                        </p>
                    <?php endif; ?>

                    <div class="rae-form-group">
                        <input 
                            type="email" 
                            name="email" 
                            class="rae-email-input" 
                            placeholder="<?php echo esc_attr($input_placeholder); ?>"
                            autocomplete="email"
                            required
                        />
                        <button type="submit" class="rae-submit-button" style="background: <?php echo esc_attr($settings['button_color'] ?? '#ff5722'); ?>; color: <?php echo esc_attr($settings['button_text_color'] ?? '#ffffff'); ?>;">
                            <?php echo esc_html($button_text); ?>
                        </button>
                    </div>

                    <?php if (!empty($settings['show_agreement']) && !empty($agreement_text)) : ?>
                        <div class="rae-agreement">
                            <label class="rae-checkbox">
                                <input type="checkbox" name="agreement" value="yes" required>
                                <span class="rae-checkbox-label" style="color: <?php echo esc_attr($settings['form_text_color'] ?? '#000000'); ?>;">
                                    <?php echo wp_kses_post($agreement_text); ?>
                                </span>
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="rae-message" data-rae-message style="display: none; color: <?php echo esc_attr($settings['form_text_color'] ?? '#000000'); ?>;"></div>
                    <div class="rae-loading" data-rae-loading style="display: none; color: <?php echo esc_attr($settings['form_text_color'] ?? '#000000'); ?>;">
                        <?php echo \RegisterAffiliateEmail\Translations\TranslationsManager::__('submitting', ''); ?>
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
