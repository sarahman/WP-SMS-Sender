<?php

function sender_activate() {
    global $wpdb;
//    require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sender_user_table = $wpdb->prefix . SMS_SENDER_USERS_TABLE;
    $sender_group_table = $wpdb->prefix . SMS_SENDER_GROUPS_TABLE;
    $sender_group_user_table = $wpdb->prefix . SMS_SENDER_GROUPS_USERS_TABLE;
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_user_table}'") == $sender_user_table) {
        $sql = "ALTER TABLE {$sender_user_table}
                    ADD  contact VARCHAR( 20 ) CHARACTER
                    SET utf8 COLLATE utf8_general_ci NULL
                    DEFAULT NULL AFTER  user_nicename;";
        mysql_query($sql);
    }
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_group_table}'") != $sender_group_table) {
        $sql = "CREATE TABLE {$sender_group_table} (
                    `id` TINYINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR( 100 ) NOT NULL
                ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
        dbDelta($sql);
    }
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_group_user_table}'") != $sender_group_user_table) {
        $sql = "CREATE TABLE {$sender_group_user_table} (
                    `group_id` TINYINT NOT NULL ,
                    `user_id` BIGINT NOT NULL ,
                    INDEX (  `group_id` ,  `user_id` )
                ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
        dbDelta($sql);
//        INSERT INTO `wordpress_me`.`wp_1_groups` (`id`, `name`) VALUES (NULL, 'Default'), (NULL, 'Admin');
    }
}

function sender_deactivate() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sender_user_table = $wpdb->prefix . SMS_SENDER_USERS_TABLE;
    $sender_group_table = $wpdb->prefix . SMS_SENDER_GROUPS_TABLE;
    $sender_group_user_table = $wpdb->prefix . SMS_SENDER_GROUPS_USERS_TABLE;
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_group_user_table}'")) {
        $sql = "DROP TABLE {$sender_group_user_table};";
        mysql_query($sql);
    }
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_group_table}'")) {
        $sql = "DROP TABLE {$sender_group_table};";
        mysql_query($sql);
    }
    if ($wpdb->get_var( "SHOW TABLES LIKE '{$sender_user_table}'")) {
        $sql = "ALTER TABLE {$sender_user_table} DROP  `contact`;";
        mysql_query($sql);
    }
}

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

function get_contacts($contact)
{
    global $wpdb;
//    var_dump("SELECT contact FROM {$wpdb->prefix}".SMS_SENDER_USERS_TABLE.
//                              " WHERE contact LIKE '%{$contact}%'");
    return $wpdb->get_results("SELECT contact FROM {$wpdb->prefix}".SMS_SENDER_USERS_TABLE.
                              " WHERE contact LIKE '%{$contact}%'");
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

function send_sms_content_2(array $data)
{
    $gatewayUsername = get_option('sender_gateway_username');
    $gatewayPassword = get_option('sender_gateway_password');
    $gatewayApiID = get_option('sender_gateway_api_id');
    $baseUrl ="http://api.clickatell.com";

    $text = urlencode($data['sms_content']);
    $to = $data['phone'] = empty($data['phone']) ? '8801914886226' : $data['phone'];

    // auth call
    $url = "{$baseUrl}/http/auth?user={$gatewayUsername}&password={$gatewayPassword}&api_id={$gatewayApiID}";

    // do auth call
    $ret = file($url);

    // explode our response. return string is on first line of the data returned
    $sess = explode(":",$ret[0]);
    var_dump($url, $ret, $sess);
    if ($sess[0] == "OK") {

        $sess_id = trim($sess[1]); // remove any whitespace
        $url = "$baseUrl/http/sendmsg?session_id=$sess_id&to=$to&text=$text";

        // do sendmsg call
        $ret = file($url);
        $send = explode(":",$ret[0]);
var_dump($send);
        if ($send[0] == "ID") {
            echo "successnmessage ID: ". $send[1];
        } else {
            echo "send message failed";
        }
    } else {
        echo "Authentication failure: ". $ret[0];
    }

}