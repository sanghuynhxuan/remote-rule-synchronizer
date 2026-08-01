<?php
defined('ABSPATH') or die('No script kiddies please!');

// Callback functions for settings sections
function ums_sync_master_section_callback() {
    echo '<p>' . esc_html__('Configure this site to send rule updates to slave sites.', UMS_SYNC_TEXT_DOMAIN) . '</p>';
    echo '<p><strong>' . esc_html__('Important:', UMS_SYNC_TEXT_DOMAIN) . '</strong> ' . esc_html__('Copy the Shared Secret Key below and paste it into the corresponding field on each slave site.', UMS_SYNC_TEXT_DOMAIN) . '</p>';

}

function ums_sync_slave_section_callback() {
    echo '<p>' . esc_html__('Configure this site to receive rule updates from the master site.', UMS_SYNC_TEXT_DOMAIN) . '</p>';
     echo '<p><strong>' . esc_html__('Important:', UMS_SYNC_TEXT_DOMAIN) . '</strong> ' . esc_html__('Paste the Shared Secret Key from your master site here.', UMS_SYNC_TEXT_DOMAIN) . '</p>';
}

// Callback functions for rendering settings fields
function ums_sync_master_enabled_render() {
    $options = get_option('ums_sync_settings_options');
    $value = isset($options['ums_sync_master_enabled']) ? $options['ums_sync_master_enabled'] : '0';
    ?>
    <input type='checkbox' name='ums_sync_settings_options[ums_sync_master_enabled]' <?php checked($value, '1'); ?> value='1'>
    <p class="description"><?php esc_html_e('Enable this to allow this site to send rule changes.', UMS_SYNC_TEXT_DOMAIN); ?></p>
    <?php
}

function ums_sync_slave_url_1_render() {
    $options = get_option('ums_sync_settings_options');
    $value = isset($options['ums_sync_slave_url_1']) ? $options['ums_sync_slave_url_1'] : '';
    ?>
    <input type='url' class='regular-text' name='ums_sync_settings_options[ums_sync_slave_url_1]' value='<?php echo esc_url($value); ?>' placeholder="https://example.test">
    <p class="description"><?php esc_html_e('Enter the full URL of the first slave site.', UMS_SYNC_TEXT_DOMAIN); ?></p>
    <?php
}

function ums_sync_slave_url_2_render() {
    $options = get_option('ums_sync_settings_options');
    $value = isset($options['ums_sync_slave_url_2']) ? $options['ums_sync_slave_url_2'] : '';
    ?>
    <input type='url' class='regular-text' name='ums_sync_settings_options[ums_sync_slave_url_2]' value='<?php echo esc_url($value); ?>' placeholder="https://example.test">
     <p class="description"><?php esc_html_e('Enter the full URL of the second slave site.', UMS_SYNC_TEXT_DOMAIN); ?></p>
    <?php
}

function ums_sync_slave_url_3_render() {
    $options = get_option('ums_sync_settings_options');
    $value = isset($options['ums_sync_slave_url_3']) ? $options['ums_sync_slave_url_3'] : '';
    ?>
    <input type='url' class='regular-text' name='ums_sync_settings_options[ums_sync_slave_url_3]' value='<?php echo esc_url($value); ?>' placeholder="https://example.test">
     <p class="description"><?php esc_html_e('Enter the full URL of the second slave site.', UMS_SYNC_TEXT_DOMAIN); ?></p>
    <?php
}

function ums_sync_shared_secret_display_render() {
    // Display only, saved in a separate option
    $secret = get_option('ums_sync_shared_secret');
    ?>
    <input type='text' class='regular-text' value='<?php echo esc_attr($secret); ?>' readonly>
     <p class="description"><?php esc_html_e('Copy this key to your slave sites. Use the button below to generate a new key if needed.', UMS_SYNC_TEXT_DOMAIN); ?></p>
    <?php
}

function ums_sync_regenerate_secret_render() {
     ?>
     <label>
        <input type='checkbox' name='ums_sync_settings_options[ums_sync_regenerate_secret_trigger]' value='1'>
        <?php esc_html_e('Check this box and save changes to generate a new Shared Secret Key.', UMS_SYNC_TEXT_DOMAIN); ?>
     </label>
     <p class="description"><?php esc_html_e('Warning: Regenerating the key will require you to update it on all slave sites.', UMS_SYNC_TEXT_DOMAIN); ?></p>
     <?php
}


function ums_sync_slave_enabled_render() {
    $options = get_option('ums_sync_settings_options');
    $value = isset($options['ums_sync_slave_enabled']) ? $options['ums_sync_slave_enabled'] : '0';
    ?>
    <input type='checkbox' name='ums_sync_settings_options[ums_sync_slave_enabled]' <?php checked($value, '1'); ?> value='1'>
     <p class="description"><?php esc_html_e('Enable this to allow this site to receive and apply rule changes from the master site.', UMS_SYNC_TEXT_DOMAIN); ?></p>
    <?php
}

function ums_sync_shared_secret_input_render() {
     // Input field for slave site
    $secret = get_option('ums_sync_shared_secret');
    ?>
    <input type='text' class='regular-text' name='ums_sync_settings_options[ums_sync_shared_secret_input]' value='<?php echo esc_attr($secret); ?>'>
     <p class="description"><?php esc_html_e('Paste the Shared Secret Key exactly as shown on the master site.', UMS_SYNC_TEXT_DOMAIN); ?></p>
    <?php
}


// HTML for the settings page
function ums_sync_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('ums_sync_settings_group'); // Nonce, action, etc.
            do_settings_sections('ums_rule_synchronizer'); // Renders sections and fields
            submit_button(__('Save Settings', UMS_SYNC_TEXT_DOMAIN));
            ?>
        </form>
    </div>
    <?php
}
?>