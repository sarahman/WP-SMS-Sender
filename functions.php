<?php

function get_users_with_contacts()
{
    global $wpdb;
    $users = $wpdb->get_results("SELECT ID, display_name, contact FROM {$wpdb->prefix}".SMS_SENDER_USERS_TABLE);
    var_dump("SELECT ID, display_name, contact FROM {$wpdb->prefix}".SMS_SENDER_USERS_TABLE);
    return $users;
}

function get_users_groups()
{
    global $wpdb;
    $groups = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_TABLE);
    var_dump("SELECT * FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_TABLE);
    return $groups;
}