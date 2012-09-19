<?php

add_action('wp_ajax_sender_suggest_contact', 'sender_suggest_contact');
function sender_suggest_contact() {
    $contact = isset($_REQUEST['contact']) ? $_REQUEST['contact'] : null;
//    file_put_contents("E:\\hahaha.txt", print_r($_REQUEST), FILE_APPEND);
    $contacts = get_contacts($contact);
    $response = array();
    foreach($contacts AS $contact) {
        echo $contact->contact . ',';
    }

//    echo json_encode($response);
    die;
}