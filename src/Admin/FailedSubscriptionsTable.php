<?php
/**
 * Install/uninstall DB table for failed subscriptions
 */

namespace RegisterAffiliateEmail\Admin;

class FailedSubscriptionsTable {
    public static function install() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rae_failed_subscriptions';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            email VARCHAR(255) NOT NULL,
            failed_services TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function uninstall() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'rae_failed_subscriptions';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
}
