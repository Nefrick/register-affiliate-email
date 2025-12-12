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
        $remote_readme = $this->getRemoteReadme();

        return (object) [
            'name' => 'Register Affiliate Email',
            'slug' => 'register-affiliate-email',
            'version' => $remote_version,
            'author' => '<a href="https://yourwebsite.com">Your Name</a>',
            'homepage' => "https://github.com/{$this->github_user}/{$this->github_repo}",
            'sections' => [
                'description' => $remote_readme,
            ],
            'download_link' => "https://github.com/{$this->github_user}/{$this->github_repo}/archive/refs/heads/main.zip",
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
}
