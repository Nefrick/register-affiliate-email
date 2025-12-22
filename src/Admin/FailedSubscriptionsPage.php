<?php
/**
 * Admin page for failed subscriptions table
 */

namespace RegisterAffiliateEmail\Admin;

class FailedSubscriptionsPage {
    public static function register() {
        add_submenu_page(
            'register-affiliate-email',
            __('Failed Subscriptions', 'register-affiliate-email'),
            __('Failed Subscriptions', 'register-affiliate-email'),
            'manage_options',
            'rae-failed-subscriptions',
            [__CLASS__, 'render']
        );
    }

    public static function render() {
        // Handle CSV export BEFORE any output
        if (isset($_GET['rae_export_csv']) && current_user_can('manage_options')) {
            self::export_csv();
        }

        echo '<div class="wrap"><h1>' . esc_html__('Failed Subscriptions', 'register-affiliate-email') . '</h1>';
        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="rae-failed-subscriptions" />';
        echo '<p class="search-box">'
            . '<label class="screen-reader-text" for="failed-subs-search-input">' . esc_html__('Search Emails:', 'register-affiliate-email') . '</label>'
            . '<input type="search" id="failed-subs-search-input" name="s" value="' . esc_attr($_REQUEST['s'] ?? '') . '" />'
            . '<input type="submit" id="search-submit" class="button" value="' . esc_attr__('Search', 'register-affiliate-email') . '" />'
            . '</p>';
        echo '</form>';

        // Export button
        $export_url = add_query_arg(['page' => 'rae-failed-subscriptions', 'rae_export_csv' => 1], admin_url('admin.php'));
        echo '<a href="' . esc_url($export_url) . '" class="button button-secondary" style="margin-bottom:10px;">' . esc_html__('Export to CSV', 'register-affiliate-email') . '</a>';

        // Table
        require_once __DIR__ . '/FailedSubscriptionsListTable.php';
        $table = new FailedSubscriptionsListTable();
        $table->prepare_items();
        $table->display();
        echo '</div>';
    }

    public static function export_csv() {
        if (headers_sent()) return;
        global $wpdb;
        $table = $wpdb->prefix . 'rae_failed_subscriptions';
        $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=failed_subscriptions.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Email', 'Failed Services', 'Date']);
        foreach ($rows as $row) {
            fputcsv($output, [$row['email'], $row['failed_services'], $row['created_at']]);
        }
        fclose($output);
        exit;
    }
}
