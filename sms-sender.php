<?php
/*
Plugin name: SMS Sender
Plugin URI: http://aabid048.com/SMS-Sender
Description: Sends SMS to multiple groups.
Author: Syed Abidur Rahman.
Version: 1.0.1
Author URI: http://aabid048.com
*/
//require_once 'lib/class.php';
require_once 'functions.php';
ini_set('max_execution_time', 900); //900 seconds = 15 minutes
define( 'SMS_SENDER_VERSION', '1.0.0' );
define( 'SMS_SENDER_GROUPS_TABLE', 'groups' );
define( 'SMS_SENDER_USERS_TABLE', 'users' );
define( 'SMS_SENDER_GROUPS_USERS_TABLE', 'groups_users' );

// Init options & tables during activation & deregister init option
register_activation_hook( __FILE__, 'sender_activate' );
register_deactivation_hook(__FILE__ , 'sender_deactivate');
add_action('admin_menu', 'sender_admin_action' );

function sender_activate() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sender_user_table = $wpdb->prefix . SMS_SENDER_USERS_TABLE;
    $sender_group_table = $wpdb->prefix . SMS_SENDER_GROUPS_TABLE;
    $sender_group_user_table = $wpdb->prefix . SMS_SENDER_GROUPS_USERS_TABLE;
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_user_table}'") == $sender_group_table) {
        $sql = "ALTER TABLE {$sender_user_table}
                    ADD  contact VARCHAR( 20 ) CHARACTER
                    SET utf8 COLLATE utf8_general_ci NULL
                    DEFAULT NULL AFTER  user_nicename;";
        dbDelta($sql);
    }
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_group_table}'") != $sender_group_table) {
        $sql = "CREATE TABLE {$sender_group_table} (
                    `id` TINYINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR( 100 ) NOT NULL
                ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
        dbDelta($sql);
    }
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_group_user_table}'") != $sender_group_user_table) {
        $sql = "CREATE TABLE {$sender_group_user_table} (
                    `group_id` TINYINT NOT NULL ,
                    `user_id` BIGINT NOT NULL ,
                    INDEX (  `group_id` ,  `user_id` )
                ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
        dbDelta($sql);
//        INSERT INTO `wordpress_me`.`wp_1_groups` (`id`, `name`) VALUES (NULL, 'Default'), (NULL, 'Admin');
    }
}

function sender_deactivate() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sender_user_table = $wpdb->prefix . SMS_SENDER_USERS_TABLE;
    $sender_group_table = $wpdb->prefix . SMS_SENDER_GROUPS_TABLE;
    $sender_group_user_table = $wpdb->prefix . SMS_SENDER_GROUPS_USERS_TABLE;
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_group_user_table}'")) {
        $sql = "DROP TABLE {$sender_group_user_table};";
        mysql_query($sql);
    }
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_group_table}'")) {
        $sql = "DROP TABLE {$sender_group_table};";
        mysql_query($sql);
    }
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_user_table}'")) {
        $sql = "ALTER TABLE {$sender_user_table} DROP  `contact`;";
        mysql_query($sql);
    }
}

if ( !function_exists( 'sender_admin_action' ) ) {
    function sender_admin_action() {
        if ( function_exists( 'add_menu_page' ) ) {
            add_menu_page('SMS Sender Plugin Options', 'SMS Sender', 'manage_options', 'sms-sender', 'sender_manage_users');
            add_submenu_page('sms-sender', 'Manage Users', 'Manage Users', 0, 'sms-sender-users', 'sender_manage_users' );
            add_submenu_page('sms-sender', 'Send SMS', 'Send SMS', 0, 'sms-sender-send', 'sender_send_sms' );
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

//add_action('wp_ajax_sender_insert_contact', 'sender_insert_contact');
//function sender_insert_contact() {
//    $contact = isset($_POST['contact']) ? $_POST['contact'] : null;
//    echo json_encode(array('msg' => $contact . ' - alhamdulillah'));
//    die();
//}
//
//add_action('admin_print_scripts-sms-sender-users.php', 'sender_script');
//function sender_script() {
//    wp_enqueue_script('sms-sender', path_join(WP_PLUGIN_URL, basename(dirname(__FILE__)) . '/sms-sender.js'), array('jquery'));
//}