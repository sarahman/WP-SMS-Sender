<?php

add_action('wp_ajax_sender_insert_contact', 'sender_insert_contact');
function sender_insert_contact() {
    $contact = dealsWithNull($_POST, 'contact');
    if (empty($contact) || empty($_POST['userId'])) {
        echo json_encode(array('status' => 'error', 'msg' => 'Data has not given.'));
        die;
    }

    $result = update_user_contact($_POST['userId'], $contact);
    echo json_encode(array('status' => !empty($result), 'msg' => 'Data has'.(!empty($result) ? ' ' : ' not ').'been updated.'));
    die();
}

add_action('wp_ajax_sender_suggest_group', 'sender_suggest_group');
function sender_suggest_group() {
    $group = dealsWithNull($_REQUEST, 'group');
    $groups = get_groups($group);
    $response = array();
    foreach($groups AS $group) {
        $response[] = $group->name;
    }

    echo json_encode($response);
    die;
}

add_action('wp_ajax_sender_add_user_in_group', 'sender_add_user_in_group');
function sender_add_user_in_group() {
    $users = get_existed_users($_POST['userIds']);
    $groups = get_existed_groups($_POST['groupIds']);
    $result = sender_insert_users_into_group($users, $groups);

    echo json_encode(array('status' => !empty($result)));
    die;
}

add_action('wp_ajax_sender_add_group', 'sender_add_group');
function sender_add_group() {
    $result = sender_insert_group($_POST);

    echo json_encode(array('status' => !empty($result)));
    die;
}

add_action('wp_ajax_sender_remove_group', 'sender_remove_group');
function sender_remove_group() {
    $result = sender_delete_group($_POST);

    echo json_encode(array('status' => !empty($result)));
    die;
}

add_action('wp_ajax_sender_remove_group_user', 'sender_remove_group_user');
function sender_remove_group_user() {
    $result = sender_delete_group_user($_POST);

    echo json_encode(array('status' => !empty($result)));
    die;
}