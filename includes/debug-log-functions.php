<?php
/**
 * Helper functions for debug log
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if WP_DEBUG_LOG is enabled
 */
function simpli_debug_is_enabled() {
    return defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
}

/**
 * Get debug.log file path
 */
function simpli_debug_get_log_path() {
    // Check if alternative logging is enabled
    if (simpli_debug_is_alternative_enabled()) {
        return simpli_debug_get_alternative_path();
    }
    
    if (defined('WP_DEBUG_LOG') && is_string(WP_DEBUG_LOG)) {
        return WP_DEBUG_LOG;
    }
    return WP_CONTENT_DIR . '/debug.log';
}

/**
 * Check if debug.log exists and has content
 */
function simpli_debug_log_exists() {
    $log_path = simpli_debug_get_log_path();
    return file_exists($log_path) && filesize($log_path) > 0;
}

/**
 * Get debug.log content
 */
function simpli_debug_get_log_content() {
    $log_path = simpli_debug_get_log_path();
    
    if (!file_exists($log_path)) {
        return '';
    }
    
    return file_get_contents($log_path);
}

/**
 * Get debug.log size
 */
function simpli_debug_get_log_size() {
    $log_path = simpli_debug_get_log_path();
    
    if (!file_exists($log_path)) {
        return 0;
    }
    
    return filesize($log_path);
}

/**
 * Format file size
 */
function simpli_debug_format_size($bytes) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Check if alternative debug logging is enabled
 */
function simpli_debug_is_alternative_enabled() {
    return get_option('simpli_debug_alternative_enabled', false);
}

/**
 * Enable alternative debug logging
 */
function simpli_debug_enable_alternative() {
    $log_path = WP_CONTENT_DIR . '/simpli-debug.log';
    
    // Check if wp-content is writable
    if (!is_writable(WP_CONTENT_DIR)) {
        error_log('Simpli Debug: wp-content directory is not writable');
        update_option('simpli_debug_last_error', 'wp_content_not_writable');
        return false;
    }
    
    // Try to create the log file if it doesn't exist
    if (!file_exists($log_path)) {
        $result = @file_put_contents($log_path, '');
        if ($result === false) {
            error_log('Simpli Debug: Could not create log file at ' . $log_path);
            update_option('simpli_debug_last_error', 'cannot_create_file');
            return false;
        }
    }
    
    // Verify the file exists and is writable
    if (!file_exists($log_path) || !is_writable($log_path)) {
        update_option('simpli_debug_last_error', 'file_not_writable');
        return false;
    }
    
    // Set PHP ini settings
    $log_errors = @ini_set('log_errors', '1');
    $error_log = @ini_set('error_log', $log_path);
    $display_errors = @ini_set('display_errors', '0');
    
    // Check if ini_set worked
    if ($log_errors === false || $error_log === false) {
        error_log('Simpli Debug: ini_set() failed - may be disabled on this server');
        update_option('simpli_debug_last_error', 'ini_set_failed');
    } else {
        delete_option('simpli_debug_last_error');
    }
    
    // Save options
    update_option('simpli_debug_alternative_enabled', true);
    update_option('simpli_debug_alternative_path', $log_path);
    
    // Log a test error to verify it's working
    @trigger_error('Simpli Debug: Alternative logging enabled successfully', E_USER_NOTICE);
    
    return true;
}

/**
 * Disable alternative debug logging
 */
function simpli_debug_disable_alternative() {
    // Remove options
    delete_option('simpli_debug_alternative_enabled');
    delete_option('simpli_debug_alternative_path');
    delete_option('simpli_debug_last_error');
    
    return true;
}

/**
 * Get alternative log path
 */
function simpli_debug_get_alternative_path() {
    return get_option('simpli_debug_alternative_path', WP_CONTENT_DIR . '/simpli-debug.log');
}

/**
 * Initialize alternative logging if enabled
 */
function simpli_debug_init_alternative() {
    if (simpli_debug_is_alternative_enabled()) {
        $log_path = simpli_debug_get_alternative_path();
        @ini_set('log_errors', '1');
        @ini_set('error_log', $log_path);
        @ini_set('display_errors', '0');
    }
}
add_action('init', 'simpli_debug_init_alternative', 1);