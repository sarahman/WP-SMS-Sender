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

    // do auth call
    $response = file($url);

    // explode our response. return string is on first line of the data returned
    $sess = explode(":", $response[0]);
    if ($sess[0] == "OK") {

        $sess_id = trim($sess[1]); // remove any whitespace
        $url = "{$baseUrl}/http/sendmsg?session_id={$sess_id}&to={$to}&text={$text}";

        $response = file($url);
        $send = explode(":", $response[0]);
        if ($send[0] == "ID") {
            echo "success message ID: ". $send[1];
        } else {
            echo "send message failed";
        }
    } else {
        echo "Authentication failure: ". $response[0];
    }
}

function send_sms_content_multiple(array $data)
{
    $gatewayUsername = get_option('sender_gateway_username');
    $gatewayPassword = get_option('sender_gateway_password');
    $gatewayApiID = get_option('sender_gateway_api_id');
    $data['sms_phone'] = empty($data['sms_phone']) ? '8801922441148' : $data['sms_phone'];
    $contacts = explode(', ', $data['sms_phone']);
    $contactStr = '';
    foreach ($contacts AS $contact) {
        $contactStr .= "to:{$contact}\n\r";
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
        $error_message = '<p>' . $phpmailer->ErrorInfo . '</p>';
    } else {
        $error_message  = '<div class="updated"><p>Test e-mail sent.</p>';
        $error_message .= '<p>' . sprintf('The body of the e-mail includes this time-stamp: %s.', date('Y-m-d I:h:s') ) . '</p></div>';
    }
    echo ($error_message);
}