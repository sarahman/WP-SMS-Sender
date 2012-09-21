<?php

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

add_action('wp_ajax_sender_suggest_contact', 'sender_suggest_contact');
function sender_suggest_contact() {
    $contact = isset($_REQUEST['contact']) ? $_REQUEST['contact'] : null;
    $contacts = get_contacts($contact);
    $response = array();
    foreach($contacts AS $contact) {
        $response[] = $contact->contact;
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