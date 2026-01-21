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
    
    // Set PHP ini settings
    @ini_set('log_errors', 1);
    @ini_set('error_log', $log_path);
    
    // Save option
    update_option('simpli_debug_alternative_enabled', true);
    update_option('simpli_debug_alternative_path', $log_path);
    
    return true;
}

/**
 * Disable alternative debug logging
 */
function simpli_debug_disable_alternative() {
    // Remove options
    delete_option('simpli_debug_alternative_enabled');
    delete_option('simpli_debug_alternative_path');
    
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
        @ini_set('log_errors', 1);
        @ini_set('error_log', $log_path);
        @ini_set('display_errors', 0);
    }
}
add_action('init', 'simpli_debug_init_alternative', 1);