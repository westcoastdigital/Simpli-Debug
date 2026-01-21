<?php
/**
 * Activity Log page template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get unique users who have made changes
global $wpdb;
$table_name = Simpli_Debug_Database::get_table_name();
$users = $wpdb->get_results("
    SELECT DISTINCT user_id, user_name 
    FROM $table_name 
    WHERE user_id IS NOT NULL 
    ORDER BY user_name ASC
");

// Get unique post types
$post_types = $wpdb->get_col("
    SELECT DISTINCT post_type 
    FROM $table_name 
    WHERE post_type IS NOT NULL 
    ORDER BY post_type ASC
");
?>

<div class="wrap simpli-activity-wrap">
    <h1>
        <?php echo esc_html(get_admin_page_title()); ?>
        <a href="<?php echo esc_url(admin_url('tools.php?page=simpli-debug-log')); ?>" class="page-title-action">
            <?php _e('View Debug Log', 'simpli-debug'); ?>
        </a>
    </h1>
    
    <div class="simpli-activity-filters">
        <div class="simpli-filter-row">
            <div class="simpli-filter-group">
                <label for="filter-log-type"><?php _e('Type:', 'simpli-debug'); ?></label>
                <select id="filter-log-type" class="simpli-filter">
                    <option value=""><?php _e('All Types', 'simpli-debug'); ?></option>
                    <option value="post"><?php _e('Posts/Pages', 'simpli-debug'); ?></option>
                    <option value="plugin"><?php _e('Plugins', 'simpli-debug'); ?></option>
                    <option value="theme"><?php _e('Themes', 'simpli-debug'); ?></option>
                </select>
            </div>
            
            <div class="simpli-filter-group">
                <label for="filter-action"><?php _e('Action:', 'simpli-debug'); ?></label>
                <select id="filter-action" class="simpli-filter">
                    <option value=""><?php _e('All Actions', 'simpli-debug'); ?></option>
                    <option value="update"><?php _e('Update', 'simpli-debug'); ?></option>
                    <option value="delete"><?php _e('Delete', 'simpli-debug'); ?></option>
                    <option value="trash"><?php _e('Trash', 'simpli-debug'); ?></option>
                    <option value="restore"><?php _e('Restore', 'simpli-debug'); ?></option>
                    <option value="activate"><?php _e('Activate', 'simpli-debug'); ?></option>
                    <option value="deactivate"><?php _e('Deactivate', 'simpli-debug'); ?></option>
                    <option value="install"><?php _e('Install', 'simpli-debug'); ?></option>
                </select>
            </div>
            
            <div class="simpli-filter-group">
                <label for="filter-post-type"><?php _e('Post Type:', 'simpli-debug'); ?></label>
                <select id="filter-post-type" class="simpli-filter">
                    <option value=""><?php _e('All Post Types', 'simpli-debug'); ?></option>
                    <?php foreach ($post_types as $post_type): ?>
                        <option value="<?php echo esc_attr($post_type); ?>">
                            <?php echo esc_html(ucfirst($post_type)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="simpli-filter-group">
                <label for="filter-user"><?php _e('User:', 'simpli-debug'); ?></label>
                <select id="filter-user" class="simpli-filter">
                    <option value=""><?php _e('All Users', 'simpli-debug'); ?></option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo esc_attr($user->user_id); ?>">
                            <?php echo esc_html($user->user_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="simpli-filter-row">
            <div class="simpli-filter-group">
                <label for="filter-date-from"><?php _e('From:', 'simpli-debug'); ?></label>
                <input type="date" id="filter-date-from" class="simpli-filter">
            </div>
            
            <div class="simpli-filter-group">
                <label for="filter-date-to"><?php _e('To:', 'simpli-debug'); ?></label>
                <input type="date" id="filter-date-to" class="simpli-filter">
            </div>
            
            <div class="simpli-filter-group">
                <label for="filter-per-page"><?php _e('Show:', 'simpli-debug'); ?></label>
                <select id="filter-per-page" class="simpli-filter">
                    <option value="25">25</option>
                    <option value="50" selected>50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                </select>
            </div>
            
            <div class="simpli-filter-actions">
                <button type="button" class="button button-primary simpli-apply-filters">
                    <?php _e('Apply Filters', 'simpli-debug'); ?>
                </button>
                <button type="button" class="button button-secondary simpli-reset-filters">
                    <?php _e('Reset', 'simpli-debug'); ?>
                </button>
                <button type="button" class="button simpli-export-logs">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Export CSV', 'simpli-debug'); ?>
                </button>
                <button type="button" class="button button-link-delete simpli-reset-activity-log">
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Clear All Entries', 'simpli-debug'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <div class="simpli-activity-stats">
        <div class="simpli-stat">
            <span class="simpli-stat-label"><?php _e('Total Entries:', 'simpli-debug'); ?></span>
            <span class="simpli-stat-value" id="total-entries">-</span>
        </div>
        <div class="simpli-stat">
            <span class="simpli-stat-label"><?php _e('Showing:', 'simpli-debug'); ?></span>
            <span class="simpli-stat-value" id="showing-entries">-</span>
        </div>
    </div>
    
    <div class="simpli-activity-table-wrapper">
        <table class="wp-list-table widefat fixed striped simpli-activity-table">
            <thead>
                <tr>
                    <th class="column-date"><?php _e('Date/Time', 'simpli-debug'); ?></th>
                    <th class="column-type"><?php _e('Type', 'simpli-debug'); ?></th>
                    <th class="column-action"><?php _e('Action', 'simpli-debug'); ?></th>
                    <th class="column-object"><?php _e('Object', 'simpli-debug'); ?></th>
                    <th class="column-post-type"><?php _e('Post Type', 'simpli-debug'); ?></th>
                    <th class="column-user"><?php _e('User', 'simpli-debug'); ?></th>
                    <th class="column-details"><?php _e('Details', 'simpli-debug'); ?></th>
                </tr>
            </thead>
            <tbody id="activity-log-tbody">
                <tr class="simpli-loading-row">
                    <td colspan="7" class="simpli-loading">
                        <span class="spinner is-active"></span>
                        <?php _e('Loading activity log...', 'simpli-debug'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="simpli-pagination">
        <button type="button" class="button simpli-prev-page" disabled>
            <?php _e('« Previous', 'simpli-debug'); ?>
        </button>
        <span class="simpli-page-info">
            <span id="current-page">1</span> / <span id="total-pages">1</span>
        </span>
        <button type="button" class="button simpli-next-page" disabled>
            <?php _e('Next »', 'simpli-debug'); ?>
        </button>
    </div>
</div>