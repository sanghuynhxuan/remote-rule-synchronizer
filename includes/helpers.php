<?php
defined('ABSPATH') or die('No script kiddies please!');

// Get rule type number (0-5) from option name string
function ums_sync_get_rule_type_from_option($option_name) {
    switch ($option_name) {
        case 'ums_rules_list': return 0;
        case 'ums_text_list': return 1;
        case 'ums_novel_list': return 2;
        case 'ums_vipnovel_list': return 3;
        case 'ums_novel_generic_list': return 4;
        case 'ums_manga_generic_list': return 5;
        default: return false;
    }
}

// Get option name string from rule type number
function ums_sync_get_option_name_from_type($rule_type) {
     switch ($rule_type) {
        case 0: return 'ums_rules_list';
        case 1: return 'ums_text_list';
        case 2: return 'ums_novel_list';
        case 3: return 'ums_vipnovel_list';
        case 4: return 'ums_novel_generic_list';
        case 5: return 'ums_manga_generic_list';
        default: return false;
    }
}

// Prepare data for sending (JSON and Base64 encode)
function ums_sync_prepare_data($data) {
    // Ensure data is array, even if empty
    if (!is_array($data)) {
        $data = array();
    }
    $json_data = wp_json_encode($data);
    if ($json_data === false) {
        return false; // JSON encoding failed
    }
    return base64_encode($json_data);
}

// Decode received data (Base64 and JSON decode)
function ums_sync_decode_data($encoded_data) {
    $json_data = base64_decode($encoded_data);
    if ($json_data === false) {
        return null; // Base64 decoding failed
    }
    // Use true for associative array
    $decoded_data = json_decode($json_data, true);
    // json_decode returns null on error or for JSON null value, check explicitly
     if ($decoded_data === null && strtolower($json_data) !== 'null') {
         // Log json error maybe? json_last_error(), json_last_error_msg()
         if (function_exists('ums_sync_log')) {
             ums_sync_log("[UMS Sync Helper] JSON Decode Error: " . json_last_error_msg() . " for data: " . substr($json_data, 0, 100) . "...");
         }
         return null;
     }
    // Return the array (or null if the original JSON was 'null')
    return $decoded_data;
}

function ums_sync_log($message) {
    // Chỉ ghi log nếu logging được bật (có thể thêm tùy chọn riêng cho plugin sync sau này)
    // Hoặc đơn giản là luôn ghi log cho plugin này
    $log_file = WP_CONTENT_DIR . '/ums-sync-info.log'; // File log riêng
    $timestamp = date("Y-m-d H:i:s e");
    $log_entry = sprintf("[%s] %s\n", $timestamp, $message); // Dùng \n thay vì <br/> cho file text thuần
    error_log($log_entry, 3, $log_file);
}

?>