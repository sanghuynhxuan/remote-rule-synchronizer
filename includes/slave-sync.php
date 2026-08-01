<?php
defined('ABSPATH') or die('No script kiddies please!');

// Register REST API routes for slave sites
function ums_sync_register_rest_routes() {
    register_rest_route('ums-sync/v1', '/receive-rules', array(
        'methods'             => 'POST',
        'callback'            => 'ums_sync_handle_receive_rules',
        'permission_callback' => '__return_true', // Allow public access, security check inside callback
         'args'                => [ // Define expected arguments (optional but good practice)
            'action' => [
                'required' => true,
                'validate_callback' => function($param, $request, $key) { return is_string($param); },
                'sanitize_callback' => 'sanitize_key',
            ],
            'rule_type' => [
                'required' => true,
                'validate_callback' => function($param, $request, $key) { return is_numeric($param) && $param >= 0 && $param <= 5; },
                'sanitize_callback' => 'absint',
            ],
            'rules_data' => [
                'required' => true,
                 'validate_callback' => function($param, $request, $key) { return is_string($param) && !empty($param); },
                 // No automatic sanitization here, we decode and validate later
            ],
             'security_key' => [
                 'required' => true,
                 'validate_callback' => function($param, $request, $key) { return is_string($param) && !empty($param); },
                 'sanitize_callback' => 'sanitize_text_field',
             ],
        ],
    ));
}

// Callback function to handle incoming rule sync requests
function ums_sync_handle_receive_rules(WP_REST_Request $request) {
    $settings = get_option('ums_sync_settings_options');
    $slave_enabled = isset($settings['ums_sync_slave_enabled']) ? $settings['ums_sync_slave_enabled'] : '0';
    $local_secret = get_option('ums_sync_shared_secret');

    // Check if receiving is enabled
    if ('1' !== $slave_enabled) {
        return new WP_Error('sync_disabled', __('Rule synchronization receiving is disabled on this site.', UMS_SYNC_TEXT_DOMAIN), array('status' => 403));
    }

    // Check if secret key is configured
    if (empty($local_secret)) {
        return new WP_Error('no_secret', __('Shared secret key is not configured on this slave site.', UMS_SYNC_TEXT_DOMAIN), array('status' => 500));
    }

    // Get parameters from the request
    $params = $request->get_params();
    $action = $params['action'] ?? '';
    $rule_type = $params['rule_type'] ?? null;
    $encoded_rules_data = $params['rules_data'] ?? '';
    $received_key = $params['security_key'] ?? '';

    // Basic validation
    if ($action !== 'sync_rules' || $rule_type === null || empty($encoded_rules_data) || empty($received_key)) {
        return new WP_Error('missing_params', __('Missing or invalid parameters in sync request.', UMS_SYNC_TEXT_DOMAIN), array('status' => 400));
    }

    // Security Check: Compare received key with local secret
    if (!hash_equals($local_secret, $received_key)) { // Use hash_equals for timing attack resistance
         if (function_exists('ums_sync_log')) {
            ums_sync_log("[UMS Sync Slave] Error: Invalid security key received. Request denied.");
         }
        return new WP_Error('invalid_key', __('Invalid security key.', UMS_SYNC_TEXT_DOMAIN), array('status' => 403));
    }

    // Get the corresponding option name
    $option_name = ums_sync_get_option_name_from_type($rule_type);
    if ($option_name === false) {
         if (function_exists('ums_sync_log')) {
             ums_sync_log("[UMS Sync Slave] Error: Invalid rule type received: " . $rule_type);
         }
        return new WP_Error('invalid_rule_type', __('Invalid rule type received.', UMS_SYNC_TEXT_DOMAIN), array('status' => 400));
    }

    // Decode the rules data
    $decoded_rules_data = ums_sync_decode_data($encoded_rules_data);
    if ($decoded_rules_data === null) { // Check if decoding failed
         if (function_exists('ums_sync_log')) {
             ums_sync_log("[UMS Sync Slave] Error: Failed to decode rules data for option: " . $option_name);
         }
        return new WP_Error('decoding_failed', __('Failed to decode rules data.', UMS_SYNC_TEXT_DOMAIN), array('status' => 500));
    }

    // Update the option on the slave site (overwrite completely)
    // The 'false' argument for autoload is generally recommended for large options
    $update_success = update_option($option_name, $decoded_rules_data, false);

    if ($update_success) {
         if (function_exists('ums_sync_log')) {
             ums_sync_log("[UMS Sync Slave] Successfully updated option '{$option_name}' from master site.");
         }
        return new WP_REST_Response(['success' => true, 'message' => __('Rules synchronized successfully.', UMS_SYNC_TEXT_DOMAIN)], 200);
    } else {
        // update_option returns false if the value is the same or if an error occurred.
        // We might assume if it returns false and the data IS different, an error occurred.
        $current_value = get_option($option_name);
        if ($current_value !== $decoded_rules_data) {
             if (function_exists('ums_sync_log')) {
                 ums_sync_log("[UMS Sync Slave] Error: Failed to update option '{$option_name}' even though data seems different.");
             }
             return new WP_Error('update_failed', __('Failed to update rules option on slave site.', UMS_SYNC_TEXT_DOMAIN), array('status' => 500));
        } else {
             // Data was the same, no real update needed, but acknowledge success.
             if (function_exists('ums_sync_log')) {
                 ums_sync_log("[UMS Sync Slave] Option '{$option_name}' data received was identical to current data. No update performed.");
             }
             return new WP_REST_Response(['success' => true, 'message' => __('Rules already up to date.', UMS_SYNC_TEXT_DOMAIN)], 200);
        }
    }
}
?>