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