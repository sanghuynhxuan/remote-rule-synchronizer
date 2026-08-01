<?php
defined('ABSPATH') or die('No script kiddies please!');

// Function runs on plugin activation
function ums_sync_activate() {
    // Add default options if they don't exist
    if (get_option('ums_sync_master_enabled') === false) {
        add_option('ums_sync_master_enabled', '0'); // Disabled by default
    }
    if (get_option('ums_sync_slave_url_1') === false) {
        add_option('ums_sync_slave_url_1', '');
    }
     if (get_option('ums_sync_slave_url_2') === false) {
        add_option('ums_sync_slave_url_2', '');
    }
    if (get_option('ums_sync_slave_url_3') === false) {
    add_option('ums_sync_slave_url_3', '');
}
    // Generate a random secret key only if it doesn't exist
    if (get_option('ums_sync_shared_secret') === false) {
        add_option('ums_sync_shared_secret', wp_generate_password(64, true, true));
    }
    if (get_option('ums_sync_slave_enabled') === false) {
        add_option('ums_sync_slave_enabled', '0'); // Disabled by default
    }
}

// Function runs on plugin deactivation (optional cleanup)
function ums_sync_deactivate() {
    // You might want to remove options on deactivation, or leave them.
    // delete_option('ums_sync_master_enabled');
    // delete_option('ums_sync_slave_url_1');
    // delete_option('ums_sync_slave_url_2');
    // delete_option('ums_sync_shared_secret');
    // delete_option('ums_sync_slave_enabled');
}
?>