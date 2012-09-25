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

function send_sms_content_using_url(array $data)
{
    $gatewayUsername = get_option('sender_gateway_username');
    $gatewayPassword = get_option('sender_gateway_password');
    $gatewayApiID = get_option('sender_gateway_api_id');

    if (checkClickATellCredentialsNotOk($gatewayUsername, $gatewayPassword, $gatewayApiID)) {
        return;
    }

    $baseUrl ="http://api.clickatell.com";

    $text = urlencode($data['sms_content']);
    $data['sms_groups'] = empty($data['sms_groups']) ? '8801914886226' : $data['sms_groups'];
    $contacts = get_contacts_by_groups($data['sms_groups']);
    $to = '';
    foreach ($contacts AS $contact) {
        empty($contact->contact) || $to .= "{$contact->contact},";
    }
    if (empty($to)) {
        showMessage('No contact has been found.');
        return;
    }

    $to = substr($to, 0, strlen($to)-1);

    // auth call
    $url = "{$baseUrl}/http/auth?user={$gatewayUsername}&password={$gatewayPassword}&api_id={$gatewayApiID}";

    $response = file($url); // do auth call

    // explode our response. return string is on first line of the data returned
    $gatewaySession = explode(":", $response[0]);
    if ($gatewaySession[0] == "OK") {

        $sessionId = trim($gatewaySession[1]); // remove any whitespace
        $url = "{$baseUrl}/http/sendmsg?session_id={$sessionId}&to={$to}&text={$text}";

        $response = file($url);
        $send = explode(":", $response[0]);
        array_walk(&$send, function(&$element){ $element = trim($element); });

        if ($send[0] == "ID") {
            showMessage("The SMS has been successfully sent. You can check through the message ID: ". $send[1]);
        } else {
            $errorMsg = "Sending SMS has been failed, because of ";
            $response = explode(', ', $send[1]);
            if ($response[0] == '301') {
                $errorMsg .= 'no credit left.';
            }
            showMessage($errorMsg, 'error');
        }
    } else {
        $response = explode(', ', $gatewaySession[1]);
        showMessage("Authentication failure: ". $response[1] .
            ". Please <a href='" . site_url('wp-admin/admin.php?page=sms-sender-configure') .
            "'>click here</a> to check it out.", 'error');
    }
}

function send_sms_content_using_email(array $data)
{
    $gatewayUsername = get_option('sender_gateway_username');
    $gatewayPassword = get_option('sender_gateway_password');
    $gatewayApiID = get_option('sender_gateway_api_id');

    if (checkClickATellCredentialsNotOk($gatewayUsername, $gatewayPassword, $gatewayApiID)) {
        return;
    }

    $data['sms_groups'] = empty($data['sms_groups']) ? '8801914886226' : $data['sms_groups'];
    $contacts = get_contacts_by_groups($data['sms_groups']);
    $contactStr = '';
    foreach ($contacts AS $contact) {
        empty($contact->contact) || $contactStr .= "to:{$contact->contact}\n\r";
    }
    $mailBody = <<<EOF
api_id:{$gatewayApiID}
user:{$gatewayUsername}
password:{$gatewayPassword}
text:{$data['sms_content']}
{$contactStr}
EOF;

    add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
    wp_mail('sms@messaging.clickatell.com', '', $mailBody);

    global $phpmailer;
    if ( $phpmailer->ErrorInfo != "" ) {
        showMessage($phpmailer->ErrorInfo, 'error');
    } else {
        showMessage('The email containing your sms has been sent.');
    }
}

function checkClickATellCredentialsNotOk($username = '', $password = '', $apiId = '')
{
    if (empty($username) || empty($password) || empty($apiId)) {
        showMessage('The credentials info of clickatell.com is either unavailable or not complete. '.
            "Please <a href='" . site_url('wp-admin/admin.php?page=sms-sender-configure') .
            "'>click here</a> to check it out.", 'error');
        return true;
    }

    return false;
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

/* BEGIN Custom User Contact Info */
function extra_contact_info($contactMethods)
{
    $contactMethods = array_merge(array(SMS_SENDER_CONTACT => 'Phone Number'), $contactMethods);
    return $contactMethods;
}

/* END Custom User Contact Info */

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