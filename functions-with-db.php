<?php

function sender_activate_database()
{
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sender_group_table = $wpdb->prefix . SMS_SENDER_GROUPS_TABLE;
    $sender_group_user_table = $wpdb->prefix . SMS_SENDER_GROUPS_USERS_TABLE;
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_group_table}'") != $sender_group_table) {
        $sql = "CREATE TABLE {$sender_group_table} (
                    `ID` TINYINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `groupname` VARCHAR( 100 ) NOT NULL
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
    }
}

function sender_deactivate_database()
{
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
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

    $registrationFields = sender_get_extra_fields();
    foreach ($registrationFields AS $field => $label) {
        delete_metadata('user', -1, $field, '', true);
    }

    delete_option('sender_gateway_username');
    delete_option('sender_gateway_password');
    delete_option('sender_gateway_api_id');
}

function getGroupTableName()
{
    global $wpdb;
    if ($wpdb->get_var( $wpdb->prepare("SHOW TABLES LIKE '{$wpdb->prefix}uam_accessgroups'"))) {
        return $wpdb->prefix . 'uam_accessgroups';
    }

    return $wpdb->prefix . SMS_SENDER_GROUPS_TABLE;
}

function get_users_with_contacts()
{
    global $wpdb;
    $sql = "SELECT `ID`, `display_name`, `contact` FROM {$wpdb->prefix}".SMS_SENDER_USERS_TABLE." `u`
            LEFT JOIN (SELECT `user_id`, `meta_value` AS `contact` FROM {$wpdb->prefix}".SMS_SENDER_USER_META_TABLE." `um`
                WHERE `um`.`meta_key`='".SMS_SENDER_CONTACT."') `um`
            ON `u`.`ID` =`um`.`user_id`";
    return $wpdb->get_results($sql);
}

function get_user_groups()
{
    $groupTable = getGroupTableName();
    global $wpdb;
    $groups = $wpdb->get_results("SELECT `ID` AS `id`, `groupname` AS `name` FROM {$groupTable}");
    foreach ($groups AS $key => $group) {
        $users = $wpdb->get_results(
            "SELECT `ID`, `display_name`, `contact` FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_USERS_TABLE. " `gu`
             JOIN (SELECT `ID`, `display_name`, `contact` FROM {$wpdb->prefix}".SMS_SENDER_USERS_TABLE." `u`
                 LEFT JOIN (SELECT `user_id`, `meta_value` AS `contact` FROM {$wpdb->prefix}".SMS_SENDER_USER_META_TABLE." `um`
                     WHERE `um`.`meta_key`='".SMS_SENDER_CONTACT."') `um`
                 ON `u`.`ID` =`um`.`user_id`) `u`
             ON `gu`.`user_id`=`u`.`ID` WHERE `group_id`='{$group->id}';");

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

    return update_user_meta($userId, SMS_SENDER_CONTACT, $contact);
}

function get_groups($group)
{
    global $wpdb;
    $groupTable = getGroupTableName();
    return $wpdb->get_results("SELECT `groupname` AS `name` FROM {$groupTable} WHERE `groupname` LIKE '%{$group}%'");
}

function get_contacts_by_groups($groups)
{
    global $wpdb;
    $groupTable = getGroupTableName();
    $groups = array_filter(explode(',', $groups));
    array_walk($groups, 'sender_trim_string');
    $groupStr = implode("', '", array_filter($groups));
    $sql = "SELECT DISTINCT(`contact`) FROM (SELECT `ID`, `contact` FROM {$wpdb->prefix}".SMS_SENDER_USERS_TABLE." `u`
                 LEFT JOIN (SELECT `user_id`, `meta_value` AS `contact` FROM {$wpdb->prefix}".SMS_SENDER_USER_META_TABLE." `um`
                     WHERE `um`.`meta_key`='". SMS_SENDER_CONTACT ."') `um`
                 ON `u`.`ID` =`um`.`user_id`) u
            JOIN {$wpdb->prefix}".SMS_SENDER_GROUPS_USERS_TABLE." gu ON gu.user_id=u.ID
            WHERE `group_id` IN (
                SELECT `ID` AS `id` FROM {$groupTable} WHERE `groupname` IN ('{$groupStr}'));";
    return $wpdb->get_results($sql);
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
    $groupTable = getGroupTableName();
    return $wpdb->get_results("SELECT `ID` AS `id` FROM {$groupTable}".
                              " WHERE `ID` IN (" . implode(', ', $groupsIds) . ");");
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
            WHERE `group_id` IN ({$groupIds}) OR `user_id` IN ({$userIds});";
    $result = $wpdb->get_results($sql);
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

    $sql = "INSERT INTO {$wpdb->prefix}".SMS_SENDER_GROUPS_USERS_TABLE."(`user_id`, `group_id`) VALUES".
                substr($valueStr, 0, strlen($valueStr)-2) . ";";
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
//    if (empty($data['name'])) {
//        return false;
//    }
//
//    $data['name'] = htmlentities($data['name']);
//    global $wpdb;
//    $sql = "INSERT INTO {$wpdb->prefix}".SMS_SENDER_GROUPS_TABLE."(`groupname`) VALUES('{$data['name']}');";
//    return $wpdb->query($sql);
    return true;
}

function sender_delete_group($data)
{
//    if (empty($data['id'])) {
//        return false;
//    }
//
//    global $wpdb;
//    $sql = "DELETE FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_USERS_TABLE." WHERE `group_id`='{$data['id']}';";
//    $result = $wpdb->query($sql);
//    $sql = "DELETE FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_TABLE." WHERE `id`='{$data['id']}';";
//    return $wpdb->query($sql) && $result;
    return true;
}

function sender_delete_group_user($data)
{
    if (empty($data['id'])) {
        return false;
    }

    global $wpdb;
    $sql = "DELETE FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_USERS_TABLE." WHERE `user_id`='{$data['id']}';";
    return $wpdb->query($sql);
}

function insert_registration_data_into_database($user_id, $data)
{
    global $wpdb;

    $wpdb->update($wpdb->users, array('display_name' => $data['sms_sender_display_name']), array('ID' => $user_id));

    $fields = sender_get_extra_fields();
    unset($fields['sms_sender_display_name']);

    foreach ($fields AS $field => $label) {
        if (!empty ($data[$field])) {
            update_user_meta($user_id, $field, $data[$field]);
        }
    }
}