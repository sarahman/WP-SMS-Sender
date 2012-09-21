<?php

function send_sms_content_single(array $data)
{
    $gatewayUsername = get_option('sender_gateway_username');
    $gatewayPassword = get_option('sender_gateway_password');
    $gatewayApiID = get_option('sender_gateway_api_id');
    $baseUrl ="http://api.clickatell.com";

    $text = urlencode($data['sms_content']);
    $to = $data['sms_phone'] = empty($data['sms_phone']) ? '8801914886226' : $data['sms_phone'];

    // auth call
    $url = "{$baseUrl}/http/auth?user={$gatewayUsername}&password={$gatewayPassword}&api_id={$gatewayApiID}";

    $response = file($url); // do auth call

    // explode our response. return string is on first line of the data returned
    $sess = explode(":", $response[0]);
    if ($sess[0] == "OK") {

        $sess_id = trim($sess[1]); // remove any whitespace
        $url = "{$baseUrl}/http/sendmsg?session_id={$sess_id}&to={$to}&text={$text}";

        $response = file($url);
        $send = explode(":", $response[0]);
        if ($send[0] == "ID") {
            showMessage("success message ID: ". $send[1]);
        } else {
            showMessage("send message failed", 'error');
        }
    } else {
        showMessage("Authentication failure: ". $response[0], 'error');
    }
}

function send_sms_content_multiple(array $data)
{
    $gatewayUsername = get_option('sender_gateway_username');
    $gatewayPassword = get_option('sender_gateway_password');
    $gatewayApiID = get_option('sender_gateway_api_id');
    $data['sms_groups'] = empty($data['sms_groups']) ? '8801922441148' : $data['sms_groups'];
    $contacts = get_contacts_by_groups($data['sms_groups']);
    $contactStr = '';
    foreach ($contacts AS $contact) {
        $contactStr .= "to:{$contact->contact}\n\r";
    }
    $mailBody = <<<EOF
user:{$gatewayUsername}
password:{$gatewayPassword}
api_id:{$gatewayApiID}
text:{$data['sms_content']}
{$contactStr}
EOF;
    wp_mail('sms@messaging.clickatell.com', '', $mailBody);

    global $phpmailer;
    if ( $phpmailer->ErrorInfo != "" ) {
        showMessage('<p>' . $phpmailer->ErrorInfo . '</p>', 'error');
    } else {
        $message  = '<div class="updated"><p>Test e-mail sent.</p>';
        $message .= '<p>' . sprintf('The body of the e-mail includes this time-stamp: %s.', date('Y-m-d I:h:s') ) . '</p></div>';
        showMessage($message);
    }
}

function showMessage($message, $status = 'success')
{
    switch($status) {
        case 'error': $messageClass = 'error'; break;
        case 'success':
        default: $messageClass = 'error';
    }

    echo "<div class='{$messageClass}'>{$message}</div>";
}

function dealsWithNull($data, $index)
{
    return isset($data[$index]) ? $data[$index] : '';
}