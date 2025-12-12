# Simpli Debug

A professional WordPress plugin for viewing and managing your debug.log file directly from the WordPress admin panel.

## Description

Simpli Debug provides a clean, user-friendly interface for managing WordPress debug logs. Whether you're troubleshooting errors or monitoring your site's health, this plugin makes it easy to view, download, and clear your debug.log without touching your server files.

### Key Features

- **Smart Detection** - Automatically detects if debug logging is enabled
- **Easy Setup** - Copy-paste configuration code if debugging isn't enabled
- **Log Viewer** - Beautiful, dark-themed log viewer with syntax highlighting
- **One-Click Actions** - Clear, download, or refresh your log with a single click
- **File Information** - See log size and location at a glance
- **Safe Operations** - Confirmation dialogs prevent accidental data loss
- **Responsive Design** - Works perfectly on desktop, tablet, and mobile
- **WordPress Standards** - Built following WordPress coding standards and best practices

## Installation

### From WordPress Admin

1. Download the plugin zip file
2. Go to **Plugins > Add New** in your WordPress admin
3. Click **Upload Plugin** and choose the zip file
4. Click **Install Now**
5. Activate the plugin

### Manual Installation

1. Upload the `simpli-debug` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Navigate to **Tools > Debug Log** to use the plugin

## Usage

### Accessing the Debug Log

After activation, navigate to **Tools > Debug Log** in your WordPress admin menu.

### Scenario 1: Debug Logging Not Enabled

If `WP_DEBUG_LOG` is not enabled, you'll see:

- A clear message that debug logging is not active
- Ready-to-copy configuration code
- A "Copy Code" button for easy copying
- Instructions on where to paste the code in `wp-config.php`

**Configuration Code Provided:**
```php