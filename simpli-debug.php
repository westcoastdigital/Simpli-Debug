<?php
/*
Plugin Name:  Simpli Debug
Plugin URI:   https://simpliweb.com.au
Description:  View and manage the debug.log
Version:      1.0.0
Author:       SimpliWeb
Author URI:   https://simpliweb.com.au
License:      GPL v2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  simpli-debug
Domain Path:  /languages
*/

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SIMPLI_DEBUG_VERSION', '1.0.0');
define('SIMPLI_DEBUG_PATH', plugin_dir_path(__FILE__));
define('SIMPLI_DEBUG_URL', plugin_dir_url(__FILE__));

// Add menu page under Tools
add_action('admin_menu', 'simpli_debug_add_menu');
function simpli_debug_add_menu() {
    add_management_page(
        __('Debug Log', 'simpli-debug'),
        __('Debug Log', 'simpli-debug'),
        'manage_options',
        'simpli-debug-log',
        'simpli_debug_admin_page'
    );
}

// Enqueue admin styles and scripts
add_action('admin_enqueue_scripts', 'simpli_debug_admin_assets');
function simpli_debug_admin_assets($hook) {
    if ($hook !== 'tools_page_simpli-debug-log') {
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
        'confirm_download' => __('Download debug.log', 'simpli-debug')
    ));
}

// Check if WP_DEBUG_LOG is enabled
function simpli_debug_is_enabled() {
    return defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
}

// Get debug.log file path
function simpli_debug_get_log_path() {
    if (defined('WP_DEBUG_LOG') && is_string(WP_DEBUG_LOG)) {
        return WP_DEBUG_LOG;
    }
    return WP_CONTENT_DIR . '/debug.log';
}

// Check if debug.log exists and has content
function simpli_debug_log_exists() {
    $log_path = simpli_debug_get_log_path();
    return file_exists($log_path) && filesize($log_path) > 0;
}

// Get debug.log content
function simpli_debug_get_log_content() {
    $log_path = simpli_debug_get_log_path();
    
    if (!file_exists($log_path)) {
        return '';
    }
    
    return file_get_contents($log_path);
}

// Get debug.log size
function simpli_debug_get_log_size() {
    $log_path = simpli_debug_get_log_path();
    
    if (!file_exists($log_path)) {
        return 0;
    }
    
    return filesize($log_path);
}

// Format file size
function simpli_debug_format_size($bytes) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

// AJAX handler to clear debug.log
add_action('wp_ajax_simpli_debug_clear_log', 'simpli_debug_clear_log_ajax');
function simpli_debug_clear_log_ajax() {
    check_ajax_referer('simpli_debug_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Permission denied', 'simpli-debug')));
    }
    
    $log_path = simpli_debug_get_log_path();
    
    if (!file_exists($log_path)) {
        wp_send_json_error(array('message' => __('Log file does not exist', 'simpli-debug')));
    }
    
    // Clear the file
    $result = file_put_contents($log_path, '');
    
    if ($result !== false) {
        wp_send_json_success(array('message' => __('Debug log cleared successfully', 'simpli-debug')));
    } else {
        wp_send_json_error(array('message' => __('Failed to clear debug log', 'simpli-debug')));
    }
}

// AJAX handler to download debug.log
add_action('wp_ajax_simpli_debug_download_log', 'simpli_debug_download_log_ajax');
function simpli_debug_download_log_ajax() {
    check_ajax_referer('simpli_debug_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Permission denied', 'simpli-debug'));
    }
    
    $log_path = simpli_debug_get_log_path();
    
    if (!file_exists($log_path)) {
        wp_die(__('Log file does not exist', 'simpli-debug'));
    }
    
    // Set headers for download
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="debug-' . date('Y-m-d-His') . '.log"');
    header('Content-Length: ' . filesize($log_path));
    
    // Output file content
    readfile($log_path);
    exit;
}

// Admin page callback
function simpli_debug_admin_page() {
    ?>
    <div class="wrap simpli-debug-wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <?php if (!simpli_debug_is_enabled()): ?>
            <!-- Debug logging is not enabled -->
            <div class="simpli-debug-notice notice notice-warning">
                <h2><?php _e('Debug Logging is Not Enabled', 'simpli-debug'); ?></h2>
                <p><?php _e('To enable debug logging, add the following code to your wp-config.php file:', 'simpli-debug'); ?></p>
            </div>
            
            <div class="simpli-debug-code-block">
                <div class="simpli-debug-code-header">
                    <span><?php _e('wp-config.php', 'simpli-debug'); ?></span>
                    <button type="button" class="button button-small simpli-debug-copy-btn" data-clipboard-target="#debug-config-code">
                        <?php _e('Copy Code', 'simpli-debug'); ?>
                    </button>
                </div>
                <pre id="debug-config-code"><code>// Enable WP_DEBUG mode
define( 'WP_DEBUG', true );

// Enable Debug logging to the /wp-content/debug.log file
define( 'WP_DEBUG_LOG', true );

// Disable display of errors and warnings
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

// Use dev versions of core JS and CSS files (only needed if you are modifying these core files)
define( 'SCRIPT_DEBUG', true );</code></pre>
            </div>
            
            <div class="simpli-debug-notice notice notice-info">
                <p><strong><?php _e('Important:', 'simpli-debug'); ?></strong> <?php _e('After adding this code, refresh this page to view your debug log.', 'simpli-debug'); ?></p>
                <p><?php _e('Make sure to place this code before the line that says "That\'s all, stop editing!"', 'simpli-debug'); ?></p>
            </div>
            
        <?php elseif (!simpli_debug_log_exists()): ?>
            <!-- Debug logging is enabled but no errors -->
            <div class="simpli-debug-success">
                <div class="simpli-debug-success-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <h2><?php _e('Congratulations! Your site has no errors', 'simpli-debug'); ?></h2>
                <p><?php _e('Debug logging is enabled, but no errors have been logged yet.', 'simpli-debug'); ?></p>
                <p class="description"><?php _e('This page will automatically display any PHP errors, warnings, or notices as they occur.', 'simpli-debug'); ?></p>
            </div>
            
        <?php else: ?>
            <!-- Debug log exists with content -->
            <div class="simpli-debug-header">
                <div class="simpli-debug-info">
                    <p>
                        <strong><?php _e('Log Size:', 'simpli-debug'); ?></strong> 
                        <span class="simpli-debug-size"><?php echo esc_html(simpli_debug_format_size(simpli_debug_get_log_size())); ?></span>
                    </p>
                    <p>
                        <strong><?php _e('Location:', 'simpli-debug'); ?></strong> 
                        <code><?php echo esc_html(simpli_debug_get_log_path()); ?></code>
                    </p>
                </div>
                <div class="simpli-debug-actions">
                    <button type="button" class="button button-primary simpli-debug-download-btn">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Download Log', 'simpli-debug'); ?>
                    </button>
                    <button type="button" class="button button-secondary simpli-debug-refresh-btn">
                        <span class="dashicons dashicons-update"></span>
                        <?php _e('Refresh', 'simpli-debug'); ?>
                    </button>
                    <button type="button" class="button button-link-delete simpli-debug-clear-btn">
                        <span class="dashicons dashicons-trash"></span>
                        <?php _e('Clear Log', 'simpli-debug'); ?>
                    </button>
                </div>
            </div>
            
            <div class="simpli-debug-log-container">
                <pre class="simpli-debug-log"><?php echo esc_html(simpli_debug_get_log_content()); ?></pre>
            </div>
            
        <?php endif; ?>
    </div>
    <?php
}