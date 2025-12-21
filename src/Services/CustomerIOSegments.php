<?php
namespace RegisterAffiliateEmail\Services;

class CustomerIOSegments {
    public static function getSegments($api_key) {
        $cache_key = 'rae_customerio_segments';
        $segments = get_transient($cache_key);
        if ($segments !== false) {
            return $segments;
        }
        $endpoint = 'https://api.customer.io/v1/segments';
        $response = wp_remote_get($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Accept' => 'application/json',
            ],
            'timeout' => 20,
        ]);
        if (is_wp_error($response)) {
            return [];
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        $segments = isset($data['segments']) ? $data['segments'] : [];
        set_transient($cache_key, $segments, DAY_IN_SECONDS);
        return $segments;
    }
}
