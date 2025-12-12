<?php
/**
 * AWeber Service Integration
 *
 * @package RegisterAffiliateEmail\Services
 */

namespace RegisterAffiliateEmail\Services;

class AWeberService extends AbstractService {
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

        $this->log('Authentication validated');
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
        $this->log("Subscribing email: {$email}");

        $endpoint = $this->getConfig('endpoint', 'https://api.aweber.com/1.0/accounts/subscribers');
        
        $body = [
            'email' => $email,
            'client_id' => $this->getConfig('client_id'),
        ];

        // Merge additional data
        if (!empty($additional_data)) {
            $body = array_merge($body, $additional_data);
        }

        $args = [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getConfig('access_token'),
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($body),
            'timeout' => 30,
        ];

        $response = $this->makeRequest($endpoint, $args);

        if (is_wp_error($response)) {
            $this->log('Subscription failed: ' . $response->get_error_message(), 'error');
            return $response;
        }

        $this->log('Subscription successful');
        return true;
    }
}
