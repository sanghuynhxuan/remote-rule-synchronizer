<?php
defined('ABSPATH') or die('No script kiddies please!');

// Add settings page to the menu
function ums_sync_add_admin_menu() {
    add_options_page(
        __('UMS Rule Synchronizer', UMS_SYNC_TEXT_DOMAIN),
        __('UMS Rule Sync', UMS_SYNC_TEXT_DOMAIN),
        'manage_options',
        'ums_rule_synchronizer',
        'ums_sync_settings_page_html' // Callback function to render the page HTML
    );
}

// Initialize settings API
function ums_sync_settings_init() {
    // Register setting group
    register_setting('ums_sync_settings_group', 'ums_sync_settings_options', 'ums_sync_settings_sanitize'); // Added sanitize callback

    // --- Master Site Settings Section ---
    add_settings_section(
        'ums_sync_master_section',
        __('Master Site Configuration (Send Rules)', UMS_SYNC_TEXT_DOMAIN),
        'ums_sync_master_section_callback',
        'ums_rule_synchronizer'
    );

    add_settings_field(
        'ums_sync_master_enabled',
        __('Enable Sending Sync Data', UMS_SYNC_TEXT_DOMAIN),
        'ums_sync_master_enabled_render',
        'ums_rule_synchronizer',
        'ums_sync_master_section'
    );

    add_settings_field(
        'ums_sync_slave_url_1',
        __('Slave Site URL 1', UMS_SYNC_TEXT_DOMAIN),
        'ums_sync_slave_url_1_render',
        'ums_rule_synchronizer',
        'ums_sync_master_section'
    );

     add_settings_field(
        'ums_sync_slave_url_2',
        __('Slave Site URL 2', UMS_SYNC_TEXT_DOMAIN),
        'ums_sync_slave_url_2_render',
        'ums_rule_synchronizer',
        'ums_sync_master_section'
    );

     add_settings_field(
    'ums_sync_slave_url_3',                  // ID mới
    __('Slave Site URL 3', UMS_SYNC_TEXT_DOMAIN), // Nhãn mới
    'ums_sync_slave_url_3_render',           // Hàm render mới (sẽ tạo ở dưới)
    'ums_rule_synchronizer',
    'ums_sync_master_section'
);

     add_settings_field(
        'ums_sync_shared_secret_display', // Different ID for display
        __('Shared Secret Key', UMS_SYNC_TEXT_DOMAIN),
        'ums_sync_shared_secret_display_render', // Display callback
        'ums_rule_synchronizer',
        'ums_sync_master_section'
    );

     add_settings_field(
        'ums_sync_regenerate_secret',
        __('Regenerate Secret', UMS_SYNC_TEXT_DOMAIN),
        'ums_sync_regenerate_secret_render',
        'ums_rule_synchronizer',
        'ums_sync_master_section'
    );


    // --- Slave Site Settings Section ---
     add_settings_section(
        'ums_sync_slave_section',
        __('Slave Site Configuration (Receive Rules)', UMS_SYNC_TEXT_DOMAIN),
        'ums_sync_slave_section_callback',
        'ums_rule_synchronizer'
    );

     add_settings_field(
        'ums_sync_slave_enabled',
        __('Enable Receiving Sync Data', UMS_SYNC_TEXT_DOMAIN),
        'ums_sync_slave_enabled_render',
        'ums_rule_synchronizer',
        'ums_sync_slave_section'
    );

      add_settings_field(
        'ums_sync_shared_secret_input', // Different ID for input
        __('Shared Secret Key', UMS_SYNC_TEXT_DOMAIN),
        'ums_sync_shared_secret_input_render', // Input callback
        'ums_rule_synchronizer',
        'ums_sync_slave_section'
    );

}

// Sanitize settings data before saving
function ums_sync_settings_sanitize($input) {
    $sanitized_input = array();

    // Sanitize Master settings
    $sanitized_input['ums_sync_master_enabled'] = isset($input['ums_sync_master_enabled']) ? '1' : '0';
    $sanitized_input['ums_sync_slave_url_1'] = isset($input['ums_sync_slave_url_1']) ? esc_url_raw(trim($input['ums_sync_slave_url_1'])) : '';
    $sanitized_input['ums_sync_slave_url_2'] = isset($input['ums_sync_slave_url_2']) ? esc_url_raw(trim($input['ums_sync_slave_url_2'])) : '';
    $sanitized_input['ums_sync_slave_url_3'] = isset($input['ums_sync_slave_url_3']) ? esc_url_raw(trim($input['ums_sync_slave_url_3'])) : '';

    // Regenerate secret if requested (only on master)
    if (isset($input['ums_sync_regenerate_secret_trigger']) && $input['ums_sync_regenerate_secret_trigger'] === '1') {
        $new_secret = wp_generate_password(64, true, true);
         // We save the new secret directly to the option, not via the sanitized array
         update_option('ums_sync_shared_secret', $new_secret);
         add_settings_error('ums_sync_settings_options', 'secret_regenerated', __('Shared Secret has been regenerated. Remember to update it on your slave sites.', UMS_SYNC_TEXT_DOMAIN), 'updated');
         // Don't store the trigger in the options
    }

     // Sanitize Slave settings
    $sanitized_input['ums_sync_slave_enabled'] = isset($input['ums_sync_slave_enabled']) ? '1' : '0';
     // Shared secret on slave is saved directly via its own field name
     if (isset($input['ums_sync_shared_secret_input'])) {
         // Basic sanitization, could be stricter if needed
         update_option('ums_sync_shared_secret', sanitize_text_field($input['ums_sync_shared_secret_input']));
     }


    return $sanitized_input;
}


?>