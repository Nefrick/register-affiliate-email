<?php
/**
 * Template Name: Default Template
 * Description: Simple email subscription form
 *
 * @package RegisterAffiliateEmail
 * @var array $settings Form settings
 * @var string $nonce_field Nonce field HTML
 * @var string $honeypot Honeypot field HTML
 */

$background_style = '';
if (!empty($settings['background_image'])) {
    $background_style = sprintf(
        'background-image: url(%s); background-size: cover; background-position: center;',
        esc_url($settings['background_image'])
    );
}
?>

<div class="rae-form-container" style="<?php echo esc_attr($background_style); ?>">
    <form class="rae-subscription-form" data-rae-form>
        <div class="rae-form-inner">
            
            <?php if (!empty($settings['form_heading'])) : ?>
                <div class="rae-form-heading">
                    <?php echo wp_kses_post($settings['form_heading']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($settings['form_subheading'])) : ?>
                <div class="rae-form-subheading">
                    <?php echo wp_kses_post($settings['form_subheading']); ?>
                </div>
            <?php endif; ?>

            <div class="rae-form-group">
                <input 
                    type="email" 
                    name="email" 
                    class="rae-email-input" 
                    placeholder="<?php echo esc_attr($settings['input_placeholder']); ?>"
                    autocomplete="email"
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
                <?php echo \RegisterAffiliateEmail\Translations\TranslationsManager::__('submitting', ''); ?>
            </div>

            <?php echo $honeypot; ?>
            <?php echo $nonce_field; ?>
        </div>
    </form>
</div>
