<?php
/*
Plugin name: SMS Sender
Plugin URI: http://aabid048.com/SMS-Sender
Description: Sends SMS to multiple groups.
Author: Syed Abidur Rahman.
Version: 1.0.1
Author URI: http://aabid048.com
*/
require_once dirname(__FILE__) . '/functions-with-db.php';
ini_set('max_execution_time', 900); //900 seconds = 15 minutes
define( 'SMS_SENDER_VERSION', '1.0.0' );
define( 'SMS_SENDER_GROUPS_TABLE', 'groups' );
define( 'SMS_SENDER_USERS_TABLE', 'users' );
define( 'SMS_SENDER_GROUPS_USERS_TABLE', 'groups_users' );

// Init options & tables during activation & deregister init option
register_activation_hook( __FILE__, 'sender_activate' );
register_deactivation_hook(__FILE__ , 'sender_deactivate');
add_action('admin_menu', 'sender_admin_action' );

if ( !function_exists( 'sender_admin_action' ) ) {
    function sender_admin_action() {
        if ( function_exists( 'add_menu_page' ) ) {
            add_menu_page('SMS Sender Plugin Options', 'SMS Sender', 'manage_options', 'sms-sender', 'sender_manage_users', '', 1);
            add_submenu_page('sms-sender', 'Manage Users', 'Manage Users', 0, 'sms-sender-users', 'sender_manage_users' );
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

add_action('wp_ajax_sender_insert_contact', 'sender_insert_contact');
function sender_insert_contact() {
    $contact = isset($_POST['contact']) ? $_POST['contact'] : null;
    if (empty($contact) || empty($_POST['userId'])) {
        echo json_encode(array('status' => 'error', 'msg' => 'Data has not given.'));
        die;
    }

    $result = update_user_contact($_POST['userId'], $contact);
    echo json_encode(array('status' => !empty($result), 'msg' => 'Data has'.(!empty($result) ? ' ' : ' not ').'been updated.'));
    die();
}

add_action('admin_print_scripts', 'sender_script');
function sender_script() {
    wp_enqueue_script('sms-sender', path_join(WP_PLUGIN_URL, basename(dirname(__FILE__)) . '/sms-sender.js'),
        array('jquery', 'jquery-ui-autocomplete', 'suggest'));
}

require_once dirname(__FILE__) . '/suggest-sender.php';