<?php
/**
 * Plugin Update Checker
 *
 * @package RegisterAffiliateEmail\Updates
 */

namespace RegisterAffiliateEmail\Updates;

class UpdateChecker {
    /**
     * GitHub repository owner
     */
    private $github_user = 'Nefrick';

    /**
     * GitHub repository name
     */
    private $github_repo = 'register-affiliate-email';

    /**
     * Constructor
     */
    public function __construct() {
        add_filter('pre_set_site_transient_update_plugins', [$this, 'checkForUpdate']);
        add_filter('plugins_api', [$this, 'pluginInfo'], 20, 3);
    }

    /**
     * Check for plugin updates
     *
     * @param object $transient Update transient
     * @return object Modified transient
     */
    public function checkForUpdate($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote_version = $this->getRemoteVersion();
        
        if ($remote_version && version_compare(RAE_VERSION, $remote_version, '<')) {
            $plugin_slug = plugin_basename(RAE_PLUGIN_FILE);
            
            $transient->response[$plugin_slug] = (object) [
                'slug' => 'register-affiliate-email',
                'plugin' => $plugin_slug,
                'new_version' => $remote_version,
                'url' => "https://github.com/{$this->github_user}/{$this->github_repo}",
                'package' => "https://github.com/{$this->github_user}/{$this->github_repo}/releases/download/v{$remote_version}/register-affiliate-email.zip",
            ];
        }

        return $transient;
    }

    /**
     * Get plugin info for update screen
     *
     * @param false|object|array $result Plugin info
     * @param string $action Action type
     * @param object $args Arguments
     * @return false|object
     */
    public function pluginInfo($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if ($args->slug !== 'register-affiliate-email') {
            return $result;
        }

        $remote_version = $this->getRemoteVersion();
        $readme_sections = $this->parseReadme();

        return (object) [
            'name' => 'Register Affiliate Email',
            'slug' => 'register-affiliate-email',
            'version' => $remote_version,
            'author' => '<a href="https://github.com/' . $this->github_user . '">Michael Chizhevskiy</a>',
            'homepage' => "https://github.com/{$this->github_user}/{$this->github_repo}",
            'requires' => '5.8',
            'tested' => '6.9',
            'requires_php' => '7.4',
            'sections' => $readme_sections,
            'banners' => [
                'low' => "https://raw.githubusercontent.com/{$this->github_user}/{$this->github_repo}/main/assets/banner-772x250.png",
                'high' => "https://raw.githubusercontent.com/{$this->github_user}/{$this->github_repo}/main/assets/banner-1544x500.png",
            ],
            'icons' => [
                '1x' => "https://raw.githubusercontent.com/{$this->github_user}/{$this->github_repo}/main/assets/icon-128x128.png",
                '2x' => "https://raw.githubusercontent.com/{$this->github_user}/{$this->github_repo}/main/assets/icon-256x256.png",
            ],
            'download_link' => "https://github.com/{$this->github_user}/{$this->github_repo}/releases/download/v{$remote_version}/register-affiliate-email.zip",
        ];
    }

    /**
     * Get remote version from GitHub (public method for admin)
     *
     * @param bool $force Force refresh cache
     * @return string|false Remote version or false on failure
     */
    public function getRemoteVersionPublic($force = false) {
        return $this->getRemoteVersion($force);
    }

    /**
     * Get remote version from GitHub
     *
     * @param bool $force Force refresh cache
     * @return string|false Remote version or false on failure
     */
    private function getRemoteVersion($force = false) {
        $transient_key = 'rae_remote_version';
        
        if (!$force) {
            $cached = get_transient($transient_key);
            if ($cached !== false) {
                return $cached;
            }
        }

        $url = "https://api.github.com/repos/{$this->github_user}/{$this->github_repo}/releases/latest";
        $response = wp_remote_get($url, ['timeout' => 10]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['tag_name'])) {
            $version = ltrim($data['tag_name'], 'v');
            set_transient($transient_key, $version, HOUR_IN_SECONDS * 12);
            return $version;
        }

        return false;
    }

    /**
     * Get remote README content
     *
     * @return string README content
     */
    private function getRemoteReadme() {
        $url = "https://raw.githubusercontent.com/{$this->github_user}/{$this->github_repo}/main/README.md";
        $response = wp_remote_get($url, ['timeout' => 10]);

        if (is_wp_error($response)) {
            return 'No description available.';
        }

        return wp_remote_retrieve_body($response);
    }

    /**
     * Parse README into sections
     *
     * @return array Sections array
     */
    private function parseReadme() {
        $readme = $this->getRemoteReadme();
        
        if (empty($readme) || $readme === 'No description available.') {
            return ['description' => 'A flexible WordPress plugin for managing email subscription forms.'];
        }

        $sections = [];
        $lines = explode("\n", $readme);
        $current_section = 'description';
        $current_content = '';

        foreach ($lines as $line) {
            // H2 headers = new section
            if (preg_match('/^## (.+)$/', $line, $matches)) {
                // Save previous section
                if ($current_content) {
                    $sections[$current_section] = $this->parseMarkdown(trim($current_content));
                }
                
                $section_name = strtolower(str_replace([' ', '-'], '_', $matches[1]));
                $current_section = $section_name;
                $current_content = '';
            } else {
                $current_content .= $line . "\n";
            }
        }

        // Save last section
        if ($current_content) {
            $sections[$current_section] = $this->parseMarkdown(trim($current_content));
        }

        return $sections;
    }

    /**
     * Convert Markdown to HTML
     *
     * @param string $text Markdown text
     * @return string HTML
     */
    private function parseMarkdown($text) {
        // Headers
        $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
        $text = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^\*\*(.+)\*\*$/m', '<h4>$1</h4>', $text);
        
        // Bold and emphasis
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
        
        // Unordered lists
        $text = preg_replace_callback('/(?:^[\*\-] .+$\n?)+/m', function($matches) {
            $items = preg_replace('/^[\*\-] (.+)$/m', '<li>$1</li>', $matches[0]);
            return '<ul>' . $items . '</ul>';
        }, $text);
        
        // Code blocks
        $text = preg_replace('/```[\w]*\n(.*?)\n```/s', '<pre><code>$1</code></pre>', $text);
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
        
        // Links
        $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2" target="_blank">$1</a>', $text);
        
        // Paragraphs
        $text = preg_replace('/\n\n+/', '</p><p>', $text);
        $text = '<p>' . $text . '</p>';
        
        // Clean up empty paragraphs
        $text = preg_replace('/<p>\s*<\/p>/', '', $text);
        $text = preg_replace('/<p>(<[uo]l>.*?<\/[uo]l>)<\/p>/s', '$1', $text);
        $text = preg_replace('/<p>(<h[234]>.*?<\/h[234]>)<\/p>/s', '$1', $text);
        
        return $text;
    }
}
