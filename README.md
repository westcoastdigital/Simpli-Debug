# Simpli Debug

A comprehensive WordPress plugin for debugging and activity logging with recovery mode support.

## Features

### 🔍 Debug Log Viewer
- View debug.log directly from WordPress admin
- **Two ways to enable debug logging:**
  - Manual wp-config.php configuration (recommended)
  - One-click alternative logging (no file editing required)
- One-click clear and download
- Real-time log size monitoring
- Smart detection of debug logging status
- Copy-paste WP_DEBUG configuration

### 📊 Activity Logging
- **Plugin/Theme Updates**: Track all plugin and theme installations and updates
- **Post Management**: Log all post, page, and custom post type updates and deletions
- **User Tracking**: Records which user made each change (or if it was auto-updated)
- **Advanced Filtering**: Filter by type, action, post type, user, and date range
- **CSV Export**: Export activity logs for reporting and analysis
- **Reset Functionality**: Clear all activity log entries with one click

### 🚨 Recovery Mode
- Access debug logs even when plugins are broken
- Emergency access via must-use plugin
- Safe mode that disables all other plugins
- Perfect for troubleshooting fatal errors

## Installation

### Standard Installation

1. Upload the `simpli-debug` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Navigate to **Tools > Debug Log** or **Tools > Activity Log**

### Recovery Mode Setup (Optional but Recommended)

For emergency access when your site is broken:

1. Copy `simpli-debug-recovery.php` to `/wp-content/mu-plugins/`
2. If the `mu-plugins` folder doesn't exist, create it
3. Access recovery mode by visiting: `yoursite.com/wp-admin/?simpli-recovery=1`

## Usage

### Debug Log

**Access:** Tools > Debug Log

#### Enabling Debug Logging

The plugin offers two methods to enable debug logging:

**Option 1: Manual Configuration (Recommended)**
- Most comprehensive debugging
- Captures all WordPress-specific errors and warnings
- Best for serious development and troubleshooting
- The plugin provides ready-to-copy code for your `wp-config.php`
- Click "Copy Code" and paste before the "That's all, stop editing!" line

**Option 2: Quick Enable (Alternative Method)**
- One-click activation - no file editing required
- Creates a separate log file at `/wp-content/simpli-debug.log`
- Uses PHP's `ini_set()` for error logging
- Great for quick checks and basic debugging
- May not catch all WordPress-specific errors
- Can be toggled on/off from the admin interface

**Which method should you use?**
- **For development & serious debugging:** Use Option 1 (wp-config.php)
- **For quick checks & basic logging:** Use Option 2 (Quick Enable)
- **For production sites:** Use Option 1 or neither (debugging should be off in production)

#### If Debug Logging is Disabled:
- The plugin displays both options clearly
- Option 1 shows the exact code to add to `wp-config.php`
- Option 2 provides a "Enable Alternative Debug Logging" button
- Choose the method that works best for your needs

#### If Alternative Logging is Enabled:
- Shows the current log file location
- Displays file size and contents
- Provides a "Disable Alternative Logging" button
- Suggests upgrading to manual method for comprehensive debugging

#### If Standard Logging is Enabled:
- **View Log**: See all errors, warnings, and notices
- **Download**: Save a timestamped copy of the log file
- **Clear**: Remove all entries from the log (with confirmation)
- **Refresh**: Reload the page to see new entries

### Activity Log

**Access:** Tools > Activity Log

#### Filter Options:
- **Type**: All Types, Posts/Pages, Plugins, Themes
- **Action**: Update, Delete, Trash, Restore, Activate, Deactivate, Install
- **Post Type**: Filter by specific post types (post, page, custom post types)
- **User**: Filter by specific users who made changes
- **Date Range**: From/To date filters
- **Results Per Page**: 25, 50, 100, 200

#### Management Options:
- **Apply Filters**: Apply your selected filters
- **Reset**: Clear all filters and reload
- **Export CSV**: Download filtered logs in spreadsheet format
- **Clear All Entries**: Permanently delete all activity log entries (requires confirmation)

#### What Gets Logged:

**Plugin Updates:**
- Plugin installations
- Plugin updates (manual and automatic)
- Plugin activations/deactivations
- Version numbers
- Who performed the action

**Theme Updates:**
- Theme installations
- Theme updates (manual and automatic)
- Version numbers
- Who performed the action

**Post Changes:**
- Post/page/CPT updates
- Post deletions
- Posts moved to trash
- Posts restored from trash
- Title changes
- Status changes
- Content modifications
- Who made the change

#### CSV Export:
Click "Export CSV" to download all filtered logs in spreadsheet format with columns:
- ID
- Date/Time
- Type
- Action
- Object (post title, plugin name, etc.)
- Post Type
- User
- Old Value
- New Value

### Recovery Mode

**Access:** `yoursite.com/wp-admin/?simpli-recovery=1`

When your site has a fatal error from a plugin:

1. Add `?simpli-recovery=1` to your admin URL
2. All plugins except Simpli Debug will be disabled
3. You can access the debug log to see what went wrong
4. Fix the issue, then click "Exit Recovery Mode"

**Requirements:**
- Must have `simpli-debug-recovery.php` in `/wp-content/mu-plugins/`
- Must be logged in as an administrator

## Debug Logging Methods Comparison

| Feature | Manual (wp-config.php) | Alternative (Quick Enable) |
|---------|------------------------|---------------------------|
| Setup | Requires file editing | One-click button |
| Error Coverage | All WordPress errors | PHP errors only |
| Best For | Development & serious debugging | Quick checks |
| Reversible | Manual file edit required | One-click disable |
| Log Location | `/wp-content/debug.log` | `/wp-content/simpli-debug.log` |
| WordPress Integration | Full integration | Basic logging |
| Recommended For | Production debugging, development | Testing, convenience |

## Database

The plugin creates one custom table: `wp_simpli_activity_log`

### Table Structure:
```sql
CREATE TABLE wp_simpli_activity_log (
    id                bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    log_type          varchar(50) NOT NULL,           -- 'post', 'plugin', 'theme'
    object_type       varchar(50),                     -- Type of object
    object_id         varchar(255),                    -- Post ID or plugin slug
    object_title      text,                            -- Post title or plugin name
    post_type         varchar(50),                     -- 'post', 'page', 'product', etc.
    user_id           bigint(20) unsigned,             -- User who made the change
    user_name         varchar(255),                    -- User's display name
    action            varchar(50) NOT NULL,            -- 'update', 'delete', 'activate', etc.
    old_value         text,                            -- Previous value/status
    new_value         text,                            -- New value/status
    additional_data   longtext,                        -- JSON encoded additional info
    created_at        datetime NOT NULL,               -- When the action occurred
    PRIMARY KEY (id),
    KEY log_type (log_type),
    KEY object_type (object_type),
    KEY post_type (post_type),
    KEY user_id (user_id),
    KEY created_at (created_at)
);
```

### Data Management:

**Resetting the Activity Log:**
- Click "Clear All Entries" in Tools > Activity Log
- This permanently deletes all entries but keeps the table structure
- Useful for starting fresh or managing database size

**Plugin Deactivation:**
- When you deactivate the plugin, the database table is automatically dropped
- All activity log data is permanently deleted
- Reactivating the plugin creates a fresh table

**Alternative Logging Settings:**
- Alternative logging settings are stored in wp_options
- Toggling alternative logging on/off updates these settings
- Settings persist until you change them or deactivate the plugin

**Automatic Cleanup (Optional):**

You can add this to your cron jobs to automatically delete old logs:
```php
// Delete logs older than 90 days
Simpli_Debug_Database::clear_old_logs(90);
```

## File Structure
```
simpli-debug/
├── simpli-debug.php              # Main plugin file
├── simpli-debug-recovery.php     # Must-use plugin for recovery mode
├── includes/
│   ├── class-database.php        # Database operations
│   ├── class-logger.php          # Activity logging hooks
│   ├── class-admin.php           # Admin pages and AJAX
│   ├── debug-log-functions.php   # Helper functions
│   ├── debug-log-page.php        # Debug log page template
│   └── activity-log-page.php     # Activity log page template
└── assets/
    ├── admin.css                 # Admin styles
    └── admin.js                  # Admin JavaScript
```

## Hooks and Filters

### Logged Actions:

**Plugin/Theme:**
- `upgrader_process_complete` - Plugin/theme installations and updates
- `activated_plugin` - Plugin activations
- `deactivated_plugin` - Plugin deactivations

**Posts/Pages:**
- `post_updated` - Post content or metadata changes
- `before_delete_post` - Permanent post deletions
- `wp_trash_post` - Posts moved to trash
- `untrash_post` - Posts restored from trash

### Custom Logging:

You can add your own custom logs:
```php
Simpli_Debug_Database::insert_log(array(
    'log_type' => 'custom',
    'object_type' => 'my_object',
    'object_id' => '123',
    'object_title' => 'My Custom Object',
    'action' => 'custom_action',
    'user_id' => get_current_user_id(),
    'user_name' => wp_get_current_user()->display_name,
    'additional_data' => array(
        'custom_field' => 'custom_value'
    )
));
```

### Database Management Functions:
```php
// Reset activity log (clear all entries, keep table)
Simpli_Debug_Database::truncate_table();

// Drop the activity log table completely
Simpli_Debug_Database::drop_table();

// Create the activity log table
Simpli_Debug_Database::create_table();

// Delete logs older than X days
Simpli_Debug_Database::clear_old_logs(90);
```

### Alternative Logging Functions:
```php
// Enable alternative debug logging
simpli_debug_enable_alternative();

// Disable alternative debug logging
simpli_debug_disable_alternative();

// Check if alternative logging is enabled
simpli_debug_is_alternative_enabled();

// Get alternative log path
simpli_debug_get_alternative_path();
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher

## Support

For support, please visit: https://simpliweb.com.au/support

## Changelog

### Version 2.0.0
- Added activity logging system
- Added custom database table for logs
- Added filterable activity log viewer
- Added CSV export functionality
- Added recovery mode support
- Added "Clear All Entries" reset button
- Added automatic table cleanup on deactivation
- **Added one-click alternative debug logging** (no wp-config.php editing required)
- Enhanced debug log viewer with two enable options
- Improved UI/UX with inline notices
- Fixed duplicate logging on trash/restore actions

### Version 1.0.0
- Initial release
- Basic debug log viewer
- Clear and download functionality

## Important Notes

### Data Persistence
- **Activity logs are deleted when the plugin is deactivated**
- **Alternative logging settings are removed when the plugin is deactivated**
- If you need to preserve your activity history, export to CSV before deactivating
- The debug log file itself is NOT affected by plugin deactivation

### Debug Logging Methods
- **Manual method (wp-config.php)** is recommended for comprehensive debugging
- **Alternative method (Quick Enable)** is convenient but may miss some WordPress-specific errors
- You can switch between methods at any time
- Both methods can coexist, but only one should be active at a time

### Security
- All AJAX actions are protected with nonces
- User capability checks on all admin functions
- SQL injection protection via prepared statements
- XSS prevention on all outputs
- Alternative logging requires admin privileges to enable/disable

### Performance
- Database table is optimized with proper indexes
- AJAX loading prevents page blocking
- Pagination limits data transfer
- Efficient query building for filters
- Alternative logging has minimal performance impact

### Server Compatibility
- Alternative logging uses PHP's `ini_set()` function
- Some servers may have `ini_set()` disabled for security
- If alternative logging doesn't work, use the manual wp-config.php method
- Check with your hosting provider if you have issues

## License

GPL v2 or later

## Author

SimpliWeb - https://simpliweb.com.au

## Credits

Developed by SimpliWeb for WordPress debugging and site monitoring.