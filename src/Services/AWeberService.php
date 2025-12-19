<?php

namespace RegisterAffiliateEmail\Services;

class AWeberService extends AbstractService {
    /**
     * Diagnostics for AWeber connection: client_id, access_token, scopes, account_id, list_id
     * Logs all step results
     */
    public function diagnoseConnection() {
        //$client_id = $this->getConfig('client_id');
        $access_token = $this->getConfig('access_token');
        //$refresh_token = $this->getConfig('refresh_token');
        // $this->log('DIAG: client_id=' . substr($client_id,0,8) . '..., access_token=' . substr($access_token,0,8) . '..., refresh_token=' . substr($refresh_token,0,8) . '...');

        // 1. Check /accounts
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
            ],
            'timeout' => 15,
        ];
        $accounts = $this->makeRequest('https://api.aweber.com/1.0/accounts', $args);
        // $this->log('DIAG: /accounts response: ' . print_r($accounts, true));
        if (is_wp_error($accounts)) {
            // $this->log('DIAG: Error getting accounts: ' . $accounts->get_error_message(), 'error');
            return false;
        }
        if (empty($accounts['data']['entries'][0]['id'])) {
            // $this->log('DIAG: No accounts found for this token!', 'error');
            return false;
        }
        $account_id = $accounts['data']['entries'][0]['id'];
        // $this->log('DIAG: Found account_id=' . $account_id);

        // 2. Check /lists
        $lists = $this->makeRequest("https://api.aweber.com/1.0/accounts/{$account_id}/lists", $args);
        // $this->log('DIAG: /lists response: ' . print_r($lists, true));
        if (is_wp_error($lists)) {
            // $this->log('DIAG: Error getting lists: ' . $lists->get_error_message(), 'error');
            return false;
        }
        if (empty($lists['data']['entries'][0]['id'])) {
            // $this->log('DIAG: No lists found for this account!', 'error');
            return false;
        }
        $list_id = $lists['data']['entries'][0]['id'];
        // $this->log('DIAG: Found list_id=' . $list_id);

        // 3. Check scopes (if supported)
        // Try to get token info (AWeber may not always return it)
        $info_args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
            ],
            'timeout' => 15,
        ];
        $info = $this->makeRequest('https://api.aweber.com/1.0/oauth2/token/info', $info_args);
        // $this->log('DIAG: /oauth2/token/info response: ' . print_r($info, true));
        if (!is_wp_error($info) && !empty($info['data']['scope'])) {
            // $this->log('DIAG: token scopes: ' . $info['data']['scope']);
        } else {
            // $this->log('DIAG: Could not get token scopes (not critical)', 'warning');
        }

        // $this->log('DIAG: Diagnostics completed successfully!');
        return true;
    }

// ...existing code continues...
    /**
     * Get service type
     *
     * @return string
     */
    public function getType() {
        return 'aweber';
    }

    /**
     * Validate configuration
     *
     * @return bool|\WP_Error
     */
    public function validate() {
        $required_fields = ['client_id', 'access_token', 'refresh_token'];

        foreach ($required_fields as $field) {
            if (empty($this->getConfig($field))) {
                return new \WP_Error(
                    'invalid_config',
                    sprintf('Missing required field: %s', $field)
                );
            }
        }

        return true;
    }

    /**
     * Authenticate with AWeber
     *
     * @return bool|\WP_Error
     */
    public function authenticate() {
        // AWeber uses OAuth, token refresh happens in subscribe method if needed
        $validation = $this->validate();
        
        if (is_wp_error($validation)) {
            return $validation;
        }

        // $this->log('Authentication validated');
        return true;
    }

    /**
     * Subscribe email to AWeber
     *
     * @param string $email Email address
     * @param array $additional_data Additional subscriber data
     * @return bool|\WP_Error
     */
    public function subscribe($email, $additional_data = []) {

        // Get access_token and refresh_token from config
        $access_token = $this->getConfig('access_token');
        $refresh_token = $this->getConfig('refresh_token');
        $client_id = $this->getConfig('client_id');

        // Get account_id (from config or fetch), list_id жёстко для test_list
        $account_id = $this->getConfig('account_id');
        $list_id = '6755140'; // TODO test_list

        // $this->log('Current config: access_token=' . substr($access_token,0,8) . '..., refresh_token=' . substr($refresh_token,0,8) . '..., client_id=' . substr($client_id,0,8) . '...');
        // $this->log('Trying to get account_id and list_id...');
        // $this->log('account_id=' . var_export($account_id, true) . ', list_id=' . var_export($list_id, true));
        // $this->log("Subscribing email: {$email}");

        if (!$account_id) {
            $account_id = $this->fetchAccountId($access_token);
            // Save for future calls
            $this->saveConfig(['account_id' => $account_id]);
        }
        $subscribers_url = "https://api.aweber.com/1.0/accounts/{$account_id}/lists/{$list_id}/subscribers";

        // Only email and custom_fields (no client_id, refresh_token)
        $body = [
            'email' => $email,
        ];
        if (!empty($additional_data)) {
            $body['custom_fields'] = $additional_data;
        }

        $args = [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($body),
            'timeout' => 30,
        ];

        $response = $this->makeRequest($subscribers_url, $args);

        // If token is expired, try to refresh and retry
        if (is_wp_error($response)) {
            $error_data = $response->get_error_data();
            if (is_array($error_data) && isset($error_data['status_code']) && $error_data['status_code'] == 401 && $refresh_token) {
                $new_token = $this->refreshAccessToken($client_id, $refresh_token);
                if ($new_token && !empty($new_token['access_token'])) {
                    //$old_access = $this->getConfig('access_token');
                    //$old_refresh = $this->getConfig('refresh_token');
                    $access_token = $new_token['access_token'];
                    $new_refresh = $new_token['refresh_token'] ?? $refresh_token;
                    // $this->log('Token refresh: OLD access_token=' . substr($old_access,0,8) . '..., OLD refresh_token=' . substr($old_refresh,0,8) . '...');
                    // $this->log('Token refresh: NEW access_token=' . substr($access_token,0,8) . '..., NEW refresh_token=' . substr($new_refresh,0,8) . '...');
                    // После обновления токена — всегда заново получаем account_id и list_id
                    $account_id = $this->fetchAccountId($access_token);
                    $list_id = '6755140'; // test_list
                    $this->saveConfig([
                        'access_token' => $access_token,
                        'refresh_token' => $new_refresh,
                        'account_id' => $account_id
                    ]);
                    // $this->log('After token refresh: account_id=' . var_export($account_id, true) . ', list_id=' . var_export($list_id, true));
                    // Retry with new token and ids
                    $subscribers_url = "https://api.aweber.com/1.0/accounts/{$account_id}/lists/{$list_id}/subscribers";
                    $args['headers']['Authorization'] = 'Bearer ' . $access_token;
                    $response = $this->makeRequest($subscribers_url, $args);
                }
            }
        }

        if (is_wp_error($response)) {
            $error_data = $response->get_error_data();
            $debug_details = '';
            if (is_array($error_data)) {
                if (isset($error_data['status_code'])) {
                    $debug_details .= ' Status: ' . $error_data['status_code'] . '.';
                }
                if (isset($error_data['response'])) {
                    $debug_details .= ' Response: ' . print_r($error_data['response'], true);
                }
            }
            // $this->log('Subscription failed: ' . $response->get_error_message() . $debug_details, 'error');
            return $response;
        }

        // $this->log('Subscription successful');
        return true;
    }

    // Get account_id via API
    protected function fetchAccountId($access_token) {
        // $this->log('Fetching account_id with access_token=' . substr($access_token,0,8) . '...');
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
            ],
            'timeout' => 15,
        ];
        $response = $this->makeRequest('https://api.aweber.com/1.0/accounts', $args);
        // $this->log('AWeber /accounts response: ' . print_r($response, true));
        if (is_array($response) && !empty($response['data']['entries'][0]['id'])) {
            return $response['data']['entries'][0]['id'];
        }
        return null;
    }

    // Get list_id via API
    protected function fetchListId($access_token, $account_id) {
        // $this->log('Fetching list_id for account_id=' . var_export($account_id, true) . ' with access_token=' . substr($access_token,0,8) . '...');
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
            ],
            'timeout' => 15,
        ];
        $response = $this->makeRequest("https://api.aweber.com/1.0/accounts/{$account_id}/lists", $args);
        // $this->log('AWeber /lists response: ' . print_r($response, true));
        if (is_array($response) && !empty($response['data']['entries'][0]['id'])) {
            return $response['data']['entries'][0]['id'];
        }
        return null;
    }

    // Refresh access_token using refresh_token
    protected function refreshAccessToken($client_id, $refresh_token) {
        $body = http_build_query([
            'grant_type' => 'refresh_token',
            'client_id' => $client_id,
            'refresh_token' => $refresh_token,
        ]);
        $args = [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => $body,
            'timeout' => 15,
        ];
        $response = wp_remote_request('https://auth.aweber.com/oauth2/token', $args);
        if (is_wp_error($response)) {
            // $this->log('Token refresh failed: ' . $response->get_error_message(), 'error');
            return null;
        }
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($data['access_token'])) {
            // $this->log('Access token refreshed successfully');
            return $data;
        }
        // $this->log('Token refresh failed: ' . print_r($data, true), 'error');
        return null;
    }

    // Save new values to config (meta custom post type)
    protected function saveConfig($data) {
        // Get current config from meta (actual)
        $config_json = get_post_meta($this->service_id, '_rae_service_config', true);
        $config = is_array($this->config) ? $this->config : [];
        if ($config_json) {
            $meta_config = json_decode($config_json, true);
            if (is_array($meta_config)) {
                $config = array_merge($meta_config, $config);
            }
        }
        // Update only provided keys
        foreach ($data as $k => $v) {
            $config[$k] = $v;
        }
        // Save back
        update_post_meta($this->service_id, '_rae_service_config', json_encode($config));
        $this->config = $config;
    }
}
