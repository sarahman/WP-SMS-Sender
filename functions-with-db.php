<?php

function sender_activate() {
    global $wpdb;
//    require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sender_user_table = $wpdb->prefix . SMS_SENDER_USERS_TABLE;
    $sender_group_table = $wpdb->prefix . SMS_SENDER_GROUPS_TABLE;
    $sender_group_user_table = $wpdb->prefix . SMS_SENDER_GROUPS_USERS_TABLE;
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_user_table}'") == $sender_user_table) {
        $sql = "ALTER TABLE {$sender_user_table}
                    ADD  contact VARCHAR( 20 ) CHARACTER
                    SET utf8 COLLATE utf8_general_ci NULL
                    DEFAULT NULL AFTER  user_nicename;";
        mysql_query($sql);
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

function get_users_with_contacts()
{
    global $wpdb;
    return $wpdb->get_results("SELECT ID, display_name, contact FROM {$wpdb->prefix}".SMS_SENDER_USERS_TABLE);
}

function get_users_groups()
{
    global $wpdb;
    $groups = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_TABLE);
    foreach($groups AS $key => $group) {
        $groups[$key]->users = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_USERS_TABLE.
            "WHERE `group_id`='{$group->id}'");
    }

    return $groups;
}

function update_user_contact($userId, $contact = null)
{
    if (empty($userId) || empty($contact)) {
        return false;
    }

    global $wpdb;
    return $wpdb->query("UPDATE {$wpdb->prefix}".SMS_SENDER_USERS_TABLE."
                               SET `contact`='{$contact}' WHERE id={$userId}");
}

function get_contacts($contact)
{
    global $wpdb;
    return $wpdb->get_results("SELECT contact FROM {$wpdb->prefix}".SMS_SENDER_USERS_TABLE.
                              " WHERE contact LIKE '%{$contact}%'");
}