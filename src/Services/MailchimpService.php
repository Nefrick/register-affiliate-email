<?php
/**
 * Mailchimp Service Integration
 *
 * @package RegisterAffiliateEmail\Services
 */

namespace RegisterAffiliateEmail\Services;

class MailchimpService extends AbstractService {
    /**
     * Get service type
     *
     * @return string
     */
    public function getType() {
        return 'mailchimp';
    }

    /**
     * Validate configuration
     *
     * @return bool|\WP_Error
     */
    public function validate() {
        $required_fields = ['api_key', 'datacenter', 'list_id'];

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
     * Authenticate with Mailchimp
     *
     * @return bool|\WP_Error
     */
    public function authenticate() {
        $validation = $this->validate();
        
        if (is_wp_error($validation)) {
            return $validation;
        }

        $this->log('Authentication validated');
        return true;
    }

    /**
     * Subscribe email to Mailchimp
     *
     * @param string $email Email address
     * @param array $additional_data Additional subscriber data
     * @return bool|\WP_Error
     */
    public function subscribe($email, $additional_data = []) {
        $this->log("Subscribing email: {$email}");

        $datacenter = $this->getConfig('datacenter');
        $list_id = $this->getConfig('list_id');
        
        $endpoint = "https://{$datacenter}.api.mailchimp.com/3.0/lists/{$list_id}/members";

        // Calculate subscriber hash
        $subscriber_hash = md5(strtolower($email));

        $body = [
            'email_address' => $email,
            'status' => $this->getConfig('double_optin') ? 'pending' : 'subscribed',
        ];

        // Add tags if provided
        $tags = $this->getConfig('tags');
        if (!empty($tags)) {
            $body['tags'] = array_map('trim', explode(',', $tags));
        }

        // Merge additional data
        if (!empty($additional_data)) {
            if (isset($additional_data['merge_fields'])) {
                $body['merge_fields'] = $additional_data['merge_fields'];
            }
        }

        $args = [
            'method' => 'PUT',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getConfig('api_key'),
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($body),
            'timeout' => 30,
        ];

        // Use PUT to /members/{subscriber_hash} for upsert behavior
        $endpoint = "https://{$datacenter}.api.mailchimp.com/3.0/lists/{$list_id}/members/{$subscriber_hash}";

        $response = $this->makeRequest($endpoint, $args);

        if (is_wp_error($response)) {
            $this->log('Subscription failed: ' . $response->get_error_message(), 'error');
            return $response;
        }

        $this->log('Subscription successful');
        return true;
    }
}
