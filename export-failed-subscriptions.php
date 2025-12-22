fclose($output);
<?php
// export-failed-subscriptions.php
// Download CSV with failed email subscriptions from rae_failed_subscriptions table

// Load WordPress core
$location = $_SERVER['DOCUMENT_ROOT'];
require_once($location . '/wp-load.php');
require_once($location . '/wp-config.php');
require_once($location . '/wp-includes/pluggable.php');

global $wpdb;

// Security: allow only logged-in admins
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('You do not have permission to access this file.');
}

// Clean all output buffers to prevent header issues
while (ob_get_level()) {
    ob_end_clean();
}

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=failed_subscriptions.csv');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');
// Write CSV header row
fputcsv($output, ['ID', 'Email', 'Failed Services', 'Date']);

// Query data from the custom table
$table = $wpdb->prefix . 'rae_failed_subscriptions';
$rows = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A);

// Write each row to CSV
foreach ($rows as $row) {
    fputcsv($output, [
        $row['id'],
        $row['email'],
        $row['failed_services'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
