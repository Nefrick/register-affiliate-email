<?php
/**
 * Translations Manager
 *
 * @package RegisterAffiliateEmail\Translations
 */

namespace RegisterAffiliateEmail\Translations;

class TranslationsManager {
    /**
     * Loaded translations cache
     *
     * @var array
     */
    private static $translations = [];

    /**
     * Load translations from file for specific locale
     *
     * @param string $locale Locale code (e.g., 'de_DE')
     * @return array Translations array
     */
    private static function loadTranslations($locale) {
        // Check if already loaded
        if (isset(self::$translations[$locale])) {
            return self::$translations[$locale];
        }

        // Build file path
        $file_path = RAE_PLUGIN_DIR . 'languages/' . $locale . '.php';

        // Load translations from file if exists
        if (file_exists($file_path)) {
            $translations = include $file_path;
            if (is_array($translations)) {
                self::$translations[$locale] = $translations;
                return $translations;
            }
        }

        // No translations found
        self::$translations[$locale] = [];
        return [];
    }

    /**
     * Translate field value based on field key and current locale
     *
     * @param string $key Field key (e.g., 'input_placeholder', 'button_text')
     * @param string $default_value Default value to return if no translation found
     * @return string Translated text or default value
     */
    public static function translateByKey($key, $default_value = '') {
        if (empty($key)) {
            return $default_value;
        }

        $locale = get_locale();
        
        // Load translations for current locale
        $translations = self::loadTranslations($locale);
        
        // Return translation if exists, otherwise default value
        return $translations[$key] ?? $default_value;
    }

    /**
     * Get available translation locales
     *
     * @return array Array of available locale codes
     */
    public static function getAvailableLocales() {
        $locales = [];
        $languages_dir = RAE_PLUGIN_DIR . 'languages/';
        
        if (is_dir($languages_dir)) {
            $files = glob($languages_dir . '*.php');
            foreach ($files as $file) {
                $locale = basename($file, '.php');
                if ($locale !== 'README') {
                    $locales[] = $locale;
                }
            }
        }
        
        return $locales;
    }

    /**
     * Add translation for a field key (runtime)
     *
     * @param string $locale Locale code (e.g., 'de_DE')
     * @param string $key Field key
     * @param string $translation Translated text
     */
    public static function addTranslation($locale, $key, $translation) {
        // Load existing translations if not loaded
        self::loadTranslations($locale);
        
        // Add new translation
        self::$translations[$locale][$key] = $translation;
    }

    /**
     * Translate static string (for use in templates instead of _e())
     *
     * @param string $key Translation key
     * @param string $default Default text
     * @return string Translated text
     */
    public static function __($key, $default = '') {
        return self::translateByKey($key, $default);
    }
}
