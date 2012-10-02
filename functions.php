<?php

function sender_add_file_into_wp_admin()
{
    if (get_option(SMS_SENDER_FLAG_FOR_FILE_COPY, false)) {
        delete_option(SMS_SENDER_FLAG_FOR_FILE_COPY);
        $mainNewUserFile = ABSPATH . 'wp-admin/user-new.php';
        @copy($mainNewUserFile, dirname(__FILE__) . '/user-new-built-in.php');
        @copy(dirname(__FILE__) . '/user-new.php', $mainNewUserFile);
        file_put_contents($mainNewUserFile, file_get_contents(dirname(__FILE__) . '/user-new.php'));
    }
}

function sender_replace_file_into_wp_admin()
{
    $currentNewUserFile = ABSPATH . 'wp-admin/user-new.php';
    $mainNewUserFile = dirname(__FILE__) . '/user-new-built-in.php';
    if (file_exists($mainNewUserFile)) {
        @copy($mainNewUserFile, $currentNewUserFile);
    }
}

function send_sms_content_using_url(array $data, ClickATell $clickATellObj)
{
    $filteredContacts = processContacts($data['sms_groups']);
    if (empty($filteredContacts)) {
        showMessage('No contact has been found.');
        return;
    }

    $clickATellObj->setContacts($filteredContacts);
    $clickATellObj->setSMSText($data['sms_content']);
    $response = $clickATellObj->sendSMS();

    if (empty($response['type'])) {
        $message = 'Message sending has failed for the following:<br />';
        $isError = false;
        foreach ($response AS $currentContact) {
            if (!is_array($currentContact)) {
                break;
            }
            if ($currentContact['type'] == 'error') {
                $isError = true;
                $message .= "<b>{$currentContact['contact']}</b>, because of {$currentContact['msg']}<br />";
            }
        }

        if ($isError) {
            showMessage($message . '<br />And sms sending to the rest of the contacts has been done.', 'error');
        } else {
            showMessage('SMS sending to all contacts has successfully done.');
        }
    } else {
        showMessage($response['msg'], $response['type']);
    }
}

function sender_trim_string(&$element)
{
    $element = trim($element);
}

function send_sms_content_using_email(array $data, ClickATell $clickATellObj)
{
    $filteredContacts = processContacts($data['sms_groups']);
    if (empty($filteredContacts)) {
        showMessage('No contact has been found.');
        return;
    }
    $clickATellObj->setContacts($filteredContacts);
    $clickATellObj->setSMSText($data['sms_content']);
    $email = $clickATellObj->getEmailDetail();

    add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
    wp_mail($email['to'], $email['subject'], $email['message']);

    global $phpmailer;
    if ($phpmailer->ErrorInfo != "") {
        showMessage($phpmailer->ErrorInfo, 'error');
    } else {
        showMessage('The email containing your sms has been sent.');
    }
}

function processContacts($groups)
{
    $groups = empty($groups) ? 'admin' : $groups;
    $contacts = get_contacts_by_groups($groups);
    $filteredContacts = array();
    foreach ($contacts AS $contact) {
        empty($contact->contact) || $filteredContacts[] = $contact->contact;
    }

    return $filteredContacts;
}

function failAuthentication()
{
    showMessage("Authentication has been failed. Please <a href='" .
        site_url('wp-admin/admin.php?page=sms-sender-configure') .
        "'>click here</a> to check it out.", 'error');
}

function showMessage($message, $status = 'success')
{
    switch($status) {
        case 'error': $messageClass = 'error'; break;
        case 'success':
        default: $messageClass = 'updated';
    }

    echo "<div class='{$messageClass}'><p>{$message}</p></div>";
}

function dealsWithNull($data, $index)
{
    return isset($data[$index]) ? $data[$index] : '';
}

/* BEGIN Custom User Info */
function sender_extra_user_info($contactMethods)
{
    $registrationFields = sender_get_extra_fields();
    $contactMethods = array_merge($registrationFields, $contactMethods);
    return $contactMethods;
}

/* END Custom User Info */

function sender_get_extra_fields()
{
    return array(
        'sms_sender_display_name' => 'Display Name',
        'sms_sender_full_name' => 'Full Name',
        'sms_sender_rank' => 'Rank',
        'sms_sender_address' => 'Address',
        'sms_sender_address_2' => 'Address 2',
        'sms_sender_city' => 'City',
        'sms_sender_state' => 'State',
        'sms_sender_zip' => 'Zip Code',
        'sms_sender_home_phone' => 'Home Phone',
        'sms_sender_cell_phone' => 'Cell Phone',
        'sms_sender_user_group' => 'User Group'
    );
//    return array(
//        '0' => array(
//            'type' => 'text',
//            'fields' => array(
//                array(
//                    'label' => 'Display Name',
//                    'name' => 'sms_sender_display_name'
//                ), array(
//                    'label' => 'Full Name',
//                    'name' => 'sms_sender_full_name'
//                ), array(
//                    'label' => 'Display Name',
//                    'name' => 'sms_sender_display_name'
//                ), array(
//                    'label' => 'Display Name',
//                    'name' => 'sms_sender_display_name'
//                ), array(
//                    'label' => 'Display Name',
//                    'name' => 'sms_sender_display_name'
//                ), array(
//                    'label' => 'Display Name',
//                    'name' => 'sms_sender_display_name'
//                ), array(
//                    'label' => 'Display Name',
//                    'name' => 'sms_sender_display_name'
//                )
//            )
//        )
//    );
}

function sender_check_and_set_empty_message($data, $field, $label, $errors)
{
    if (!dealsWithNull($data, $field)) {
        $errors->add($field, '<strong>'.__("ERROR").'</strong>: '.$label.' '.__('couldn&#8217;t be empty.'));
        return true;
    }

    return false;
}