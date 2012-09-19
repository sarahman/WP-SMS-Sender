<?php

add_action('admin_print_scripts', 'sender_script');
function sender_script() {
    wp_enqueue_script('sms-sender', path_join(WP_PLUGIN_URL, basename(dirname(__FILE__)) . '/sms-sender.js'),
        array('jquery', 'jquery-ui-autocomplete'));
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