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
    foreach ($groups AS $key => $group) {
        $users = $wpdb->get_results(
            "SELECT ID, display_name, contact FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_USERS_TABLE.
            " gu JOIN {$wpdb->prefix}".SMS_SENDER_USERS_TABLE." u ON gu.user_id=u.ID WHERE `group_id`='{$group->id}';");
        foreach ($users AS $user) {
            $groups[$key]->users[$user->ID] = $user;
        }
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

function get_existed_users($userIds = array())
{
    if (empty($userIds)) {
        return $userIds;
    }

    global $wpdb;
    return $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}".SMS_SENDER_USERS_TABLE.
                              " WHERE ID IN (" . implode(', ', $userIds) . ");");
}

function get_existed_groups($groupsIds = array())
{
    if (empty($groupsIds)) {
        return $groupsIds;
    }

    global $wpdb;
    return $wpdb->get_results("SELECT id FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_TABLE.
                              " WHERE id IN (" . implode(', ', $groupsIds) . ");");
}

function sender_insert_users_into_group($users, $groups)
{
    if (empty($users) || empty($groups)) {
        return false;
    }

    global $wpdb;

    $groupIds = '';
    foreach ($groups AS $group) {
        $groupIds .= "'{$group->id}', ";
    }

    $userIds = '';
    foreach ($users AS $user) {
        $userIds .= "'{$user->ID}', ";
    }
    $groupIds = substr($groupIds, 0, strlen($groupIds)-2);
    $userIds = substr($userIds, 0, strlen($userIds)-2);

    $sql = "SELECT * FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_USERS_TABLE. "
            WHERE group_id IN ({$groupIds}) OR user_id IN ({$userIds});";
    $result = $wpdb->get_results($sql);
//    file_put_contents("E:\\ha.txt", print_r($users, true) . print_r($groups, true) . print_r($sql, true) . print_r($result, true));
    $valueStr = '';
    foreach ($users AS $user) {
        foreach ($groups AS $group) {
            checkUserAssignedInGroup($result, $user, $group) ||
                $valueStr .= "('{$user->ID}', '{$group->id}'), ";
        }
    }

    if (strlen($valueStr) == 0) {
        return false;
    }

    $sql = "INSERT INTO {$wpdb->prefix}".SMS_SENDER_GROUPS_USERS_TABLE."(user_id, group_id) VALUES".
                substr($valueStr, 0, strlen($valueStr)-2) . ";";
//    file_put_contents("E:\\ha.txt", print_r($sql, true), FILE_APPEND);
    return $wpdb->query($sql);
}

function checkUserAssignedInGroup($groupedUsers, $user, $group) {
    foreach ($groupedUsers AS $groupedUser) {
        if ($groupedUser->user_id == $user->ID && $groupedUser->group_id == $group->id) {
            return true;
        }
    }

    return false;
}

function sender_insert_group($data)
{
    if (empty($data['name'])) {
        return false;
    }

    $data['name'] = htmlentities($data['name']);
    global $wpdb;
    $sql = "INSERT INTO {$wpdb->prefix}".SMS_SENDER_GROUPS_TABLE."(name) VALUES('{$data['name']}');";
    return $wpdb->query($sql);
}