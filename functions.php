<?php

function get_users_with_contacts()
{
    global $wpdb;
    return $wpdb->get_results("SELECT ID, display_name, contact FROM {$wpdb->prefix}".SMS_SENDER_USERS_TABLE);
}

function get_users_groups()
{
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_TABLE);
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