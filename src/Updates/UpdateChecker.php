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
        $readme_data = $this->parseReadme();

        return (object) [
            'name' => 'Register Affiliate Email',
            'slug' => 'register-affiliate-email',
            'version' => $remote_version,
            'author' => '<a href="https://github.com/' . $this->github_user . '">Michael Chizhevskiy</a>',
            'homepage' => "https://github.com/{$this->github_user}/{$this->github_repo}",
            'requires' => '5.8',
            'tested' => '6.4',
            'requires_php' => '7.4',
            'sections' => $readme_data['sections'],
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
     * Get remote version from GitHub
     *
     * @return string|false Remote version or false on failure
     */
    private function getRemoteVersion() {
        $transient_key = 'rae_remote_version';
        $cached = get_transient($transient_key);
        
        if ($cached !== false) {
            return $cached;
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
     * Parse README.md into sections for plugin info
     *
     * @return array Parsed sections
     */
    private function parseReadme() {
        $readme = $this->getRemoteReadme();
        
        if (empty($readme) || $readme === 'No description available.') {
            return [
                'sections' => [
                    'description' => 'A flexible WordPress plugin for managing email subscription forms with multiple service integrations.',
                ],
            ];
        }

        // Parse markdown sections
        $sections = [];
        $current_section = '';
        $current_content = '';

        $lines = explode("\n", $readme);
        
        foreach ($lines as $line) {
            // Detect h2 headers (## Section Name)
            if (preg_match('/^## (.+)$/', $line, $matches)) {
                // Save previous section
                if ($current_section && $current_content) {
                    $sections[$current_section] = $this->markdownToHtml(trim($current_content));
                }
                
                // Start new section
                $current_section = strtolower(str_replace(' ', '_', $matches[1]));
                $current_content = '';
            } else {
                $current_content .= $line . "\n";
            }
        }

        // Save last section
        if ($current_section && $current_content) {
            $sections[$current_section] = $this->markdownToHtml(trim($current_content));
        }

        // Ensure description exists
        if (empty($sections['description'])) {
            $sections['description'] = 'A flexible WordPress plugin for managing email subscription forms.';
        }

        return ['sections' => $sections];
    }

    /**
     * Convert basic markdown to HTML
     *
     * @param string $markdown Markdown content
     * @return string HTML content
     */
    private function markdownToHtml($markdown) {
        // Convert headers
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $markdown);
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $markdown);
        
        // Convert bold
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        
        // Convert lists
        $html = preg_replace('/^\* (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);
        
        // Convert code blocks
        $html = preg_replace('/```(\w+)?\n(.*?)\n```/s', '<pre><code>$2</code></pre>', $html);
        $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
        
        // Convert links
        $html = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $html);
        
        // Convert line breaks
        $html = wpautop($html);
        
        return $html;
    }
}
