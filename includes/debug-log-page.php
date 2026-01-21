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
    
    <?php if (!simpli_debug_is_enabled() && !simpli_debug_is_alternative_enabled()): ?>
        <!-- Debug logging is not enabled -->
        <div class="simpli-debug-notice notice notice-warning">
            <h2><?php _e('Debug Logging is Not Enabled', 'simpli-debug'); ?></h2>
            <p><?php _e('You have two options to enable debug logging:', 'simpli-debug'); ?></p>
        </div>
        
        <div class="simpli-debug-option-container">
            <div class="simpli-debug-option simpli-debug-option-recommended">
                <div class="simpli-debug-option-header">
                    <h3><?php _e('Option 1: Manual Configuration (Recommended)', 'simpli-debug'); ?></h3>
                    <span class="simpli-debug-badge simpli-debug-badge-recommended"><?php _e('Recommended', 'simpli-debug'); ?></span>
                </div>
                <p><?php _e('Add the following code to your wp-config.php file for full WordPress debug logging:', 'simpli-debug'); ?></p>
                
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
                
                <div class="simpli-debug-notice notice notice-info" style="margin-top: 15px;">
                    <p><strong><?php _e('Important:', 'simpli-debug'); ?></strong> <?php _e('Make sure to place this code before the line that says "That\'s all, stop editing!"', 'simpli-debug'); ?></p>
                </div>
            </div>
            
            <div class="simpli-debug-option">
                <div class="simpli-debug-option-header">
                    <h3><?php _e('Option 2: Quick Enable (Alternative Method)', 'simpli-debug'); ?></h3>
                </div>
                <p><?php _e('Enable error logging automatically without modifying wp-config.php. This creates a separate log file at:', 'simpli-debug'); ?></p>
                <p><code><?php echo esc_html(WP_CONTENT_DIR . '/simpli-debug.log'); ?></code></p>
                
                <div class="simpli-debug-notice notice notice-info" style="margin: 15px 0;">
                    <p><strong><?php _e('Note:', 'simpli-debug'); ?></strong> <?php _e('This method uses PHP\'s ini_set() and may not catch all WordPress-specific errors. The manual configuration method (Option 1) is more comprehensive.', 'simpli-debug'); ?></p>
                </div>
                
                <button type="button" class="button button-primary simpli-enable-alternative-logging">
                    <?php _e('Enable Alternative Debug Logging', 'simpli-debug'); ?>
                </button>
            </div>
        </div>
        
    <?php elseif (simpli_debug_is_alternative_enabled()): ?>
        <!-- Alternative debug logging is enabled -->
        <div class="simpli-debug-notice notice notice-info">
            <h2><?php _e('Alternative Debug Logging is Enabled', 'simpli-debug'); ?></h2>
            <p><?php _e('PHP error logging is currently enabled and writing to:', 'simpli-debug'); ?></p>
            <p><code><?php echo esc_html(simpli_debug_get_alternative_path()); ?></code></p>
            <p><?php _e('For more comprehensive debugging, consider using the manual wp-config.php method instead.', 'simpli-debug'); ?></p>
            <button type="button" class="button button-secondary simpli-disable-alternative-logging">
                <?php _e('Disable Alternative Logging', 'simpli-debug'); ?>
            </button>
        </div>
        
        <?php if (simpli_debug_log_exists()): ?>
            <!-- Show the log viewer if there's content -->
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
        <?php else: ?>
            <div class="simpli-debug-success">
                <div class="simpli-debug-success-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <h2><?php _e('No errors logged yet', 'simpli-debug'); ?></h2>
                <p><?php _e('Alternative debug logging is active. Any PHP errors will appear here.', 'simpli-debug'); ?></p>
            </div>
        <?php endif; ?>
        
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