<?php
/**
 * DB helper for failed subscriptions
 */

namespace RegisterAffiliateEmail\Admin;

class FailedSubscriptionsDB {
    public static function insert($email, $failed_services) {
        global $wpdb;
        $table = $wpdb->prefix . 'rae_failed_subscriptions';
        $wpdb->insert($table, [
            'email' => $email,
            'failed_services' => $failed_services,
            'created_at' => current_time('mysql'),
        ]);
    }
}
