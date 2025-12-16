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
     * Clear translations cache
     */
    public static function clearCache() {
        self::$translations = [];
    }

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

        $translations = [];

        // 1. Load plugin translations first (base)
        $plugin_path = RAE_PLUGIN_DIR . 'languages/' . $locale . '.php';
        if (file_exists($plugin_path)) {
            $plugin_translations = include $plugin_path;
            if (is_array($plugin_translations)) {
                $translations = $plugin_translations;
            }
        }

        // 2. Load theme translations (override plugin translations)
        $theme_path = get_stylesheet_directory() . '/register-affiliate-email/languages/' . $locale . '.php';
        if (file_exists($theme_path)) {
            $theme_translations = include $theme_path;
            if (is_array($theme_translations)) {
                // Merge: theme translations override plugin translations
                $translations = array_merge($translations, $theme_translations);
            }
        }

        // Cache the merged translations
        self::$translations[$locale] = $translations;
        return $translations;
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

    /**
     * Load translations for a specific template
     *
     * @param string $template_slug Template slug (e.g., 'fortune')
     * @param string $locale Locale code (e.g., 'de_DE'). If null, uses current locale
     * @return array Template translations or empty array if not found
     */
    public static function loadTemplateTranslations($template_slug, $locale = null) {
        if ($locale === null) {
            $locale = get_locale();
        }

        // Build cache key
        $cache_key = "template_{$template_slug}_{$locale}";

        // Check if already loaded
        if (isset(self::$translations[$cache_key])) {
            return self::$translations[$cache_key];
        }

        $translations = [];

        // 1. Load plugin template translations first (base)
        $plugin_path = RAE_PLUGIN_DIR . "templates/{$template_slug}/languages/{$locale}.php";
        if (file_exists($plugin_path)) {
            $plugin_translations = include $plugin_path;
            if (is_array($plugin_translations)) {
                $translations = $plugin_translations;
            }
        }

        // 2. Load theme template translations (override plugin)
        $theme_path = get_stylesheet_directory() . "/register-affiliate-email/{$template_slug}/languages/{$locale}.php";
        $theme_file_exists = file_exists($theme_path);
        if ($theme_file_exists) {
            $theme_translations = include $theme_path;
            if (is_array($theme_translations)) {
                // Merge: theme translations override plugin translations
                $translations = array_merge($translations, $theme_translations);
            }
        }

        // DEBUG: временно добавим в кеш информацию о путях
        $translations['_debug_plugin_path'] = $plugin_path;
        $translations['_debug_theme_path'] = $theme_path;
        $translations['_debug_theme_exists'] = $theme_file_exists ? 'YES' : 'NO';

        // 3. Also merge with main plugin translations (template has higher priority!)
        $main_translations = self::loadTranslations($locale);
        $translations = array_merge($main_translations, $translations); // template overwrites main!

        // Cache the merged translations
        self::$translations[$cache_key] = $translations;
        return $translations;
    }
}
