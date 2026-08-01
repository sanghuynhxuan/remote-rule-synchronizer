<?php
/**
 * Plugin Name:       UMS Rule Synchronizer
 * Plugin URI:        https://example.test
 * Description:       Synchronizes Ultimate Manga Scraper rules from a master site to slave sites.
 * Version:           1.0.0
 * Author:            Sang Huynh Xuan
 * Author URI:        https://example.test
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ums-rule-synchronizer
 * Domain Path:       /languages
 */

defined('ABSPATH') or die('No script kiddies please!');

define('UMS_SYNC_VERSION', '1.0.0');
define('UMS_SYNC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('UMS_SYNC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UMS_SYNC_TEXT_DOMAIN', 'ums-rule-synchronizer');

// Include necessary files
require_once UMS_SYNC_PLUGIN_DIR . 'includes/helpers.php';
require_once UMS_SYNC_PLUGIN_DIR . 'includes/plugin-activation.php';
require_once UMS_SYNC_PLUGIN_DIR . 'includes/admin-actions.php';
require_once UMS_SYNC_PLUGIN_DIR . 'includes/settings-page.php';
require_once UMS_SYNC_PLUGIN_DIR . 'includes/master-sync.php';
require_once UMS_SYNC_PLUGIN_DIR . 'includes/slave-sync.php';

// Activation and Deactivation Hooks
register_activation_hook(__FILE__, 'ums_sync_activate');
register_deactivation_hook(__FILE__, 'ums_sync_deactivate');

// Initialize Admin actions and REST API endpoints
if (is_admin()) {
    add_action('admin_menu', 'ums_sync_add_admin_menu');
    add_action('admin_init', 'ums_sync_settings_init');
}
add_action('rest_api_init', 'ums_sync_register_rest_routes');

// Initialize Master Sync Hooks
ums_sync_master_hooks_init();

// Load text domain for translation
function ums_sync_load_textdomain() {
    load_plugin_textdomain(UMS_SYNC_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'ums_sync_load_textdomain');

?>