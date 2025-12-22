<?php
/**
 * WP_List_Table for failed subscriptions
 */

namespace RegisterAffiliateEmail\Admin;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class FailedSubscriptionsListTable extends \WP_List_Table {
    public function __construct() {
        parent::__construct([
            'singular' => 'failed_subscription',
            'plural'   => 'failed_subscriptions',
            'ajax'     => false,
        ]);
    }

    public function display() {
        parent::display();
    }

    public function get_columns() {
        $cols = [
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'email' => __('Email', 'register-affiliate-email'),
            'failed_services' => __('Failed Services', 'register-affiliate-email'),
            'created_at' => __('Date', 'register-affiliate-email'),
        ];
        return $cols;
    }

    public function prepare_items() {
        global $wpdb;
        $table = $wpdb->prefix . 'rae_failed_subscriptions';

        $per_page = 20;
        $paged = max(1, isset($_REQUEST['paged']) ? intval($_REQUEST['paged']) : 1);
        $offset = ($paged - 1) * $per_page;

        $where = '';
        $search = isset($_REQUEST['s']) ? trim($_REQUEST['s']) : '';
        if ($search) {
            $where = $wpdb->prepare('WHERE email LIKE %s', '%' . $wpdb->esc_like($search) . '%');
        }

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page, $offset
        ), ARRAY_A);

        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns(), $this->get_primary_column_name()];
        $this->items = $items;
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    public function column_default($item, $column_name) {
        return esc_html($item[$column_name] ?? '');
    }

    /**
     * Render the email column (make it primary, like post title)
     */
    public function column_email($item) {
        return '<strong>' . esc_html($item['email']) . '</strong>';
    }
    public function column_cb($item) {
        return sprintf('<input type="checkbox" name="id[]" value="%s" />', $item['id']);
    }

    /**
     * Set the primary column for the table (for WP accessibility and style)
     */
    protected function get_primary_column_name() {
        return 'email';
    }



    public function get_bulk_actions() {
        return [
            'delete' => __('Delete', 'register-affiliate-email'),
        ];
    }
    public function no_items() {
        _e('No failed subscriptions found.', 'register-affiliate-email');
    }

    public function process_bulk_action() {
       
        if ($this->current_action() === 'delete' && current_user_can('manage_options')) {
            
            check_admin_referer('bulk-' . $this->_args['plural']);
            $ids = isset($_POST['id']) ? (array) $_POST['id'] : [];
            
            if (!empty($ids)) {
                global $wpdb;
                $table = $wpdb->prefix . 'rae_failed_subscriptions';
                $ids = array_map('intval', $ids);
                $in = implode(',', array_fill(0, count($ids), '%d'));
                $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE id IN ($in)", ...$ids));
               
                wp_redirect(add_query_arg(['page' => 'rae-failed-subscriptions', 'deleted' => count($ids)], admin_url('admin.php')));
                exit;
            }
        }
    }

    public function extra_tablenav($which) {
        if ($which === 'top') {
            echo '<input type="hidden" name="page" value="rae-failed-subscriptions" />';
        }
    }
}

