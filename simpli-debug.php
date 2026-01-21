<?php
/*
Plugin Name:  Simpli Debug
Plugin URI:   https://simpliweb.com.au
Description:  View and manage the debug.log and track site activity
Version:      2.0.0
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
define('SIMPLI_DEBUG_VERSION', '2.0.0');
define('SIMPLI_DEBUG_PATH', plugin_dir_path(__FILE__));
define('SIMPLI_DEBUG_URL', plugin_dir_url(__FILE__));

// Include required files
require_once SIMPLI_DEBUG_PATH . 'includes/class-database.php';
require_once SIMPLI_DEBUG_PATH . 'includes/class-logger.php';
require_once SIMPLI_DEBUG_PATH . 'includes/class-admin.php';
require_once SIMPLI_DEBUG_PATH . 'includes/debug-log-functions.php';

// Initialize database on plugin activation
register_activation_hook(__FILE__, array('Simpli_Debug_Database', 'create_table'));

// Drop database table on plugin deactivation
register_deactivation_hook(__FILE__, array('Simpli_Debug_Database', 'drop_table'));

// Initialize logger
add_action('plugins_loaded', array('Simpli_Debug_Logger', 'init'));

// Initialize admin
add_action('plugins_loaded', array('Simpli_Debug_Admin', 'init'));