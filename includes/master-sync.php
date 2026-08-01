<?php
defined('ABSPATH') or die('No script kiddies please!');

// Initialize the hooks for master site actions
function ums_sync_master_hooks_init() {
    $sync_options = [
        'ums_rules_list',
        'ums_text_list',
        'ums_novel_list',
        'ums_vipnovel_list',
        'ums_novel_generic_list',
        'ums_manga_generic_list',
    ];

    foreach ($sync_options as $option_name) {
        add_action("update_option_{$option_name}", 'ums_sync_rules_on_update', 10, 3);
         // Also hook add_option for the first time a rule list is created (might be needed)
         add_action("add_option_{$option_name}", function($option, $value) use ($option_name) {
            // Call the update handler, passing null as the old value
            ums_sync_rules_on_update(null, $value, $option_name);
         }, 10, 2);
    }
}

// Callback function when a rule option is updated on the master site
function ums_sync_rules_on_update($old_value, $new_value, $option_name) {
    $settings = get_option('ums_sync_settings_options');
    $master_enabled = isset($settings['ums_sync_master_enabled']) ? $settings['ums_sync_master_enabled'] : '0';

    // Only proceed if master sync is enabled
    if ('1' !== $master_enabled) {
        return;
    }

    $slave_urls = [];
    if (!empty($settings['ums_sync_slave_url_1'])) {
        $slave_urls[] = trailingslashit($settings['ums_sync_slave_url_1']); // Ensure trailing slash
    }
    if (!empty($settings['ums_sync_slave_url_2'])) {
        $slave_urls[] = trailingslashit($settings['ums_sync_slave_url_2']); // Ensure trailing slash
    }
    if (!empty($settings['ums_sync_slave_url_3'])) {
        $slave_urls[] = trailingslashit($settings['ums_sync_slave_url_3']); // Ensure trailing slash
    }
    $shared_secret = get_option('ums_sync_shared_secret');

    // Only proceed if there are slave URLs and a secret key
    if (empty($slave_urls) || empty($shared_secret)) {
         if (function_exists('ums_sync_log')) { // Check if logger exists
            ums_sync_log("[UMS Sync Master] Error: Slave URLs or Shared Secret not configured. Sync aborted for option: " . $option_name);
         }
        return;
    }

    $rule_type = ums_sync_get_rule_type_from_option($option_name);
    if ($rule_type === false) {
        if (function_exists('ums_sync_log')) {
            ums_sync_log("[UMS Sync Master] Error: Could not determine rule type for option: " . $option_name);
        }
        return; // Not a rule option we sync
    }

    // Prepare the data to send (entire new rule set)
    $encoded_rules_data = ums_sync_prepare_data($new_value);
    if($encoded_rules_data === false) {
         if (function_exists('ums_sync_log')) {
            ums_sync_log("[UMS Sync Master] Error: Failed to encode rules data for option: " . $option_name);
         }
        return;
    }


    // Send the update to each slave site
    foreach ($slave_urls as $slave_url) {
        if (empty($slave_url) || !filter_var($slave_url, FILTER_VALIDATE_URL)) {
            if (function_exists('ums_sync_log')) {
                ums_sync_log("[UMS Sync Master] Error: Invalid Slave URL skipped: " . $slave_url);
            }
            continue;
        }
        // Define the target API endpoint on the slave site
        $api_endpoint = $slave_url . 'wp-json/ums-sync/v1/receive-rules';
        ums_sync_send_request($api_endpoint, $rule_type, $encoded_rules_data, $shared_secret, $option_name);
    }
}

// Function to send the actual request to a slave site
function ums_sync_send_request($api_endpoint, $rule_type, $encoded_rules_data, $shared_secret, $option_name_for_log = '') {
    $body = [
        'action'       => 'sync_rules',
        'rule_type'    => $rule_type,
        'rules_data'   => $encoded_rules_data,
        'security_key' => $shared_secret,
    ];

    $args = [
        'method'      => 'POST',
        'timeout'     => 30, // Increased timeout for potentially large data
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking'    => true, // Wait for response
        'headers'     => ['Content-Type' => 'application/json'], // Send as JSON
        'body'        => wp_json_encode($body), // Encode body as JSON
        'cookies'     => [],
        'sslverify'   => false // Set to true in production if possible, might need cert setup
    ];

     if (function_exists('ums_sync_log')) {
        ums_sync_log("[UMS Sync Master] Sending sync request for '{$option_name_for_log}' to: " . $api_endpoint);
     }

    $response = wp_remote_post($api_endpoint, $args);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
         if (function_exists('ums_sync_log')) {
            ums_sync_log("[UMS Sync Master] Error sending request to {$api_endpoint}: " . $error_message);
         }
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
         if (function_exists('ums_sync_log')) {
            ums_sync_log("[UMS Sync Master] Response from {$api_endpoint}: Code {$response_code} - Body: " . $response_body);
         }
         // You could add more detailed logging based on the response body here
    }
}

?>