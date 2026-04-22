<?php

/**
 * Debug Log page template
 */

if (!defined('ABSPATH')) {
    exit;
}
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

    <?php elseif (!simpli_debug_log_exists() && !simpli_debug_log_has_only_notices()): ?>
        <!-- Debug logging is enabled but no errors -->
        <div class="simpli-debug-success">
            <div class="simpli-debug-success-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <h2><?php _e('Congratulations! Your site has no errors', 'simpli-debug'); ?></h2>
            <p><?php _e('Debug logging is enabled, but no errors have been logged yet.', 'simpli-debug'); ?></p>
            <p class="description"><?php _e('This page will automatically display any PHP errors, warnings, or notices as they occur.', 'simpli-debug'); ?></p>
        </div>

    <?php elseif (simpli_debug_log_has_only_notices()): ?>
        <!-- Log has content but it's all notices, currently filtered -->
        <div class="simpli-debug-success">
            <div class="simpli-debug-success-icon">
                <span class="dashicons dashicons-info"></span>
            </div>
            <h2><?php _e('No errors or warnings — only PHP Notices', 'simpli-debug'); ?></h2>
            <p><?php _e('Your log only contains PHP Notices, which are currently hidden.', 'simpli-debug'); ?></p>
            <p>
                <button type="button"
                    class="button button-secondary simpli-debug-toggle-notices-btn"
                    data-enabled="1">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php _e('Show PHP Notices', 'simpli-debug'); ?>
                </button>
            </p>
            <p class="description"><?php _e('Click above to reveal notices, or clear the log if they are no longer needed.', 'simpli-debug'); ?></p>
            <p>
                <button type="button" class="button button-link-delete simpli-debug-clear-btn">
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Clear Log', 'simpli-debug'); ?>
                </button>
            </p>
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
                <button type="button"
                    class="button simpli-debug-toggle-notices-btn <?php echo get_option('simpli_debug_exclude_notices', false) ? 'button-primary' : 'button-secondary'; ?>"
                    data-enabled="<?php echo get_option('simpli_debug_exclude_notices', false) ? '1' : '0'; ?>">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php echo get_option('simpli_debug_exclude_notices', false) ? __('Show PHP Notices', 'simpli-debug') : __('Exclude PHP Notices', 'simpli-debug'); ?>
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