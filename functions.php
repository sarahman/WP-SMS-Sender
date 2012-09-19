<?php

function get_users_with_contacts()
{
    global $wpdb;
    return $wpdb->get_results("SELECT ID, display_name, contact FROM {$wpdb->prefix}".SMS_SENDER_USERS_TABLE);
}

function get_users_groups()
{
    global $wpdb;
    $groups = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_TABLE);
    foreach($groups AS $key => $group) {
        $groups[$key]->users = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}".SMS_SENDER_GROUPS_USERS_TABLE.
            "WHERE `group_id`='{$group->id}'");
    }

    var_dump($groups);
    return $groups;
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

function send_sms_content(array $data)
{
    var_dump('about to send email');
    $data['phone'] = empty($data['phone']) ? '8801922441148' : $data['phone'];
    $gatewayUsername = get_option('sender_gateway_username');
    $gatewayPassword = get_option('sender_gateway_password');
    $gatewayApiID = get_option('sender_gateway_api_id');
    $mailBody = <<<EOF
user:{$gatewayUsername}
password:{$gatewayPassword}
api_id:{$gatewayApiID}
text:{$data['sms_content']}
to:{$data['sms_phone']}
EOF;
    var_dump(wp_mail('sms@messaging.clickatell.com', '', $mailBody));
    var_dump('email is sent');

    global $phpmailer;
    var_dump($phpmailer);
    if ( $phpmailer->ErrorInfo != "" ) {
        $error_message = '<p>' . $phpmailer->ErrorInfo . '</p>';
    } else {
        $error_message  = '<div class="updated"><p>Test e-mail sent.</p>';
        $error_message .= '<p>' . sprintf('The body of the e-mail includes this time-stamp: %s.', date('Y-m-d I:h:s') ) . '</p></div>';
    }
    var_dump($error_message);
}