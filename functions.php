<?php

function send_sms_content_using_url(array $data)
{
    $gatewayUsername = get_option('sender_gateway_username');
    $gatewayPassword = get_option('sender_gateway_password');
    $gatewayApiID = get_option('sender_gateway_api_id');
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
                $errorMsg .= 'no credit left';
            }
            showMessage($errorMsg, 'error');
        }
    } else {
        showMessage("Authentication failure: ". $response[0], 'error');
    }
}

function send_sms_content_using_email(array $data)
{
    $gatewayUsername = get_option('sender_gateway_username');
    $gatewayPassword = get_option('sender_gateway_password');
    $gatewayApiID = get_option('sender_gateway_api_id');
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