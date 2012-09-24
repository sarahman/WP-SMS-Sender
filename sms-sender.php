<?php
/*
Plugin name: SMS Sender
Plugin URI: http://aabid048.com/SMS-Sender
Description: Sends SMS to multiple groups.
Author: Syed Abidur Rahman.
Version: 1.0.1
Author URI: http://aabid048.com
*/
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/functions-with-db.php';
ini_set('max_execution_time', 900); //900 seconds = 15 minutes
define( 'SMS_SENDER_VERSION', '1.0.0' );
define( 'SMS_SENDER_GROUPS_TABLE', 'groups' );
define( 'SMS_SENDER_USERS_TABLE', 'users' );
define( 'SMS_SENDER_USER_META_TABLE', 'usermeta' );
define( 'SMS_SENDER_GROUPS_USERS_TABLE', 'groups_users' );
define( 'SMS_SENDER_CONTACT', 'sms_sender_contact' );
define( 'SMS_SENDER_FLAG_FOR_FILE_COPY', 'sender_copy_file' );

// Init options & tables during activation & deregister init option
register_activation_hook( __FILE__, 'sender_activate' );
if ( !function_exists( 'sender_activate' ) ) {
    function sender_activate()
    {
        sender_activate_database();
        add_option(SMS_SENDER_FLAG_FOR_FILE_COPY, true);
    }
}

register_deactivation_hook(__FILE__ , 'sender_deactivate');
if ( !function_exists( 'sender_deactivate' ) ) {
    function sender_deactivate()
    {
        sender_deactivate_database();
        sender_replace_file_into_wp_admin();
    }
}

add_action('admin_init', 'sender_add_file_into_wp_admin');
add_filter('user_contactmethods', 'extra_contact_info');

// add extra fields to registration form
add_action('register_form', 'sender_add_registration_field');
if ( !function_exists( 'sender_add_registration_field' ) ) {
    function sender_add_registration_field()
    { ?>
        <p>
            <label for="sms_sender_contact"><?php _e('Phone Number') ?><br />
            <input type="text" name="sms_sender_contact" id="sms_sender_contact" class="input" tabindex="21"
                   value="<?php echo esc_attr(stripslashes(dealsWithNull($_POST, SMS_SENDER_CONTACT))); ?>" size="25" /></label>
        </p>
        <?php
    }
}

// add update engine for extra fields to user's registration
add_action('user_register', 'sender_add_registration_data');
if ( !function_exists( 'sender_add_registration_data' ) ) {
    function sender_add_registration_data($user_id)
    {
        if (empty($user_id)) {
            return;
        }

        update_user_meta($user_id, SMS_SENDER_CONTACT, dealsWithNull($_POST, 'sms_sender_contact'));
    }
}

add_action('admin_menu', 'sender_admin_action' );
if ( !function_exists( 'sender_admin_action' ) ) {
    function sender_admin_action() {
        if ( function_exists( 'add_menu_page' ) ) {
            add_menu_page('SMS Sender Plugin Options', 'SMS Sender', 'manage_options', 'sms-sender', 'sender_manage_users', '');
            add_submenu_page('sms-sender', 'Manage Users', 'Manage Users', 0, 'sms-sender', 'sender_manage_users' );
            add_submenu_page('sms-sender', 'Send SMS', 'Send SMS', 0, 'sms-sender-send', 'sender_send_sms' );
            add_submenu_page('sms-sender', 'Configure', 'Configure', 0, 'sms-sender-configure', 'sender_configure' );
        }
    }
}

if ( !function_exists( 'sender_manage_users' ) ) {
    function sender_manage_users() {
        require_once dirname(__FILE__) . '/manage-user.php';
    }
}

if ( !function_exists( 'sender_send_sms' ) ) {
    function sender_send_sms() {
        require_once dirname(__FILE__) . '/send-sms.php';
    }
}

if ( !function_exists( 'sender_configure' ) ) {
    function sender_configure() {
        require_once dirname(__FILE__) . '/configure-sender.php';
    }
}

add_action('admin_print_scripts', 'sender_script');
function sender_script() {
    wp_enqueue_script('sms-sender', path_join(WP_PLUGIN_URL, basename(dirname(__FILE__)) . '/sms-sender.js'),
        array('jquery', 'jquery-ui-autocomplete'));
}

require_once dirname(__FILE__) . '/functions-with-ajax.php';