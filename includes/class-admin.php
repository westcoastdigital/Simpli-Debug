<?php
/**
 * Admin class for Simpli Debug
 */

if (!defined('ABSPATH')) {
    exit;
}

class Simpli_Debug_Admin {
    
    /**
     * Initialize admin hooks
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_menu_pages'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_simpli_debug_clear_log', array(__CLASS__, 'ajax_clear_log'));
        add_action('wp_ajax_simpli_debug_download_log', array(__CLASS__, 'ajax_download_log'));
        add_action('wp_ajax_simpli_get_activity_logs', array(__CLASS__, 'ajax_get_activity_logs'));
        add_action('wp_ajax_simpli_export_activity_logs', array(__CLASS__, 'ajax_export_activity_logs'));
        add_action('wp_ajax_simpli_reset_activity_logs', array(__CLASS__, 'ajax_reset_activity_logs')); // NEW
    }
        /**
     * Add menu pages
     */
    public static function add_menu_pages() {
        // Debug Log page
        add_management_page(
            __('Debug Log', 'simpli-debug'),
            __('Debug Log', 'simpli-debug'),
            'manage_options',
            'simpli-debug-log',
            array(__CLASS__, 'render_debug_log_page')
        );
        
        // Activity Log page
        add_management_page(
            __('Activity Log', 'simpli-debug'),
            __('Activity Log', 'simpli-debug'),
            'manage_options',
            'simpli-activity-log',
            array(__CLASS__, 'render_activity_log_page')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public static function enqueue_assets($hook) {
        if ($hook !== 'tools_page_simpli-debug-log' && $hook !== 'tools_page_simpli-activity-log') {
            return;
        }
        
        wp_enqueue_style(
            'simpli-debug-admin',
            SIMPLI_DEBUG_URL . 'assets/admin.css',
            array(),
            SIMPLI_DEBUG_VERSION
        );
        
        wp_enqueue_script(
            'simpli-debug-admin',
            SIMPLI_DEBUG_URL . 'assets/admin.js',
            array('jquery'),
            SIMPLI_DEBUG_VERSION,
            true
        );
        
        wp_localize_script('simpli-debug-admin', 'simpliDebug', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('simpli_debug_nonce'),
            'confirm_clear' => __('Are you sure you want to clear the debug log? This cannot be undone.', 'simpli-debug'),
            'confirm_reset' => __('Are you sure you want to reset the activity log? This will permanently delete ALL activity entries and cannot be undone.', 'simpli-debug'), // NEW
        ));
    }
    
    /**
     * Render debug log page
     */
    public static function render_debug_log_page() {
        require_once SIMPLI_DEBUG_PATH . 'includes/debug-log-page.php';
    }
    
    /**
     * Render activity log page
     */
    public static function render_activity_log_page() {
        require_once SIMPLI_DEBUG_PATH . 'includes/activity-log-page.php';
    }
    
    /**
     * AJAX: Clear debug log
     */
    public static function ajax_clear_log() {
        check_ajax_referer('simpli_debug_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'simpli-debug')));
        }
        
        $log_path = simpli_debug_get_log_path();
        
        if (!file_exists($log_path)) {
            wp_send_json_error(array('message' => __('Log file does not exist', 'simpli-debug')));
        }
        
        $result = file_put_contents($log_path, '');
        
        if ($result !== false) {
            wp_send_json_success(array('message' => __('Debug log cleared successfully', 'simpli-debug')));
        } else {
            wp_send_json_error(array('message' => __('Failed to clear debug log', 'simpli-debug')));
        }
    }
    
    /**
     * AJAX: Download debug log
     */
    public static function ajax_download_log() {
        check_ajax_referer('simpli_debug_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'simpli-debug'));
        }
        
        $log_path = simpli_debug_get_log_path();
        
        if (!file_exists($log_path)) {
            wp_die(__('Log file does not exist', 'simpli-debug'));
        }
        
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="debug-' . date('Y-m-d-His') . '.log"');
        header('Content-Length: ' . filesize($log_path));
        
        readfile($log_path);
        exit;
    }
    
    /**
     * AJAX: Get activity logs
     */
    public static function ajax_get_activity_logs() {
        check_ajax_referer('simpli_debug_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'simpli-debug')));
        }
        
        $filters = array(
            'log_type' => isset($_POST['log_type']) ? sanitize_text_field($_POST['log_type']) : '',
            'action' => isset($_POST['action_filter']) ? sanitize_text_field($_POST['action_filter']) : '',
            'post_type' => isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '',
            'user_id' => isset($_POST['user_id']) ? intval($_POST['user_id']) : '',
            'date_from' => isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '',
            'date_to' => isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '',
            'limit' => isset($_POST['per_page']) ? intval($_POST['per_page']) : 50,
            'offset' => isset($_POST['offset']) ? intval($_POST['offset']) : 0
        );
        
        $logs = Simpli_Debug_Database::get_logs($filters);
        $total = Simpli_Debug_Database::get_logs_count($filters);
        
        wp_send_json_success(array(
            'logs' => $logs,
            'total' => $total
        ));
    }
    
    /**
     * AJAX: Export activity logs
     */
    public static function ajax_export_activity_logs() {
        check_ajax_referer('simpli_debug_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'simpli-debug'));
        }
        
        $filters = array(
            'log_type' => isset($_GET['log_type']) ? sanitize_text_field($_GET['log_type']) : '',
            'action' => isset($_GET['action_filter']) ? sanitize_text_field($_GET['action_filter']) : '',
            'post_type' => isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '',
            'user_id' => isset($_GET['user_id']) ? intval($_GET['user_id']) : '',
            'date_from' => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '',
            'date_to' => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '',
            'limit' => 10000 // Export limit
        );
        
        $logs = Simpli_Debug_Database::get_logs($filters);
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="activity-log-' . date('Y-m-d-His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 support
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV headers
        fputcsv($output, array(
            'ID',
            'Date/Time',
            'Type',
            'Action',
            'Object',
            'Post Type',
            'User',
            'Old Value',
            'New Value'
        ));
        
        // CSV rows
        foreach ($logs as $log) {
            fputcsv($output, array(
                $log->id,
                $log->created_at,
                $log->log_type,
                $log->action,
                $log->object_title,
                $log->post_type,
                $log->user_name,
                $log->old_value,
                $log->new_value
            ));
        }
        
        fclose($output);
        exit;
    }

    /**
     * AJAX: Reset activity logs (truncate table)
     */
    public static function ajax_reset_activity_logs() {
        check_ajax_referer('simpli_debug_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'simpli-debug')));
        }
        
        $result = Simpli_Debug_Database::truncate_table();
        
        if ($result !== false) {
            wp_send_json_success(array('message' => __('Activity log reset successfully', 'simpli-debug')));
        } else {
            wp_send_json_error(array('message' => __('Failed to reset activity log', 'simpli-debug')));
        }
    }
}