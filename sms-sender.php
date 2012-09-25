<?php
/*
Plugin name: SMS Sender
Plugin URI: http://aabid048.com/SMS-Sender
Description: Sends SMS to multiple groups.
Author: Syed Abidur Rahman.
Version: 1.0.1
Author URI: http://aabid048.com
*/
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/functions-with-db.php';
ini_set('max_execution_time', 900); //900 seconds = 15 minutes
define( 'SMS_SENDER_VERSION', '1.0.0' );
define( 'SMS_SENDER_GROUPS_TABLE', 'groups' );
define( 'SMS_SENDER_USERS_TABLE', 'users' );
define( 'SMS_SENDER_USER_META_TABLE', 'usermeta' );
define( 'SMS_SENDER_GROUPS_USERS_TABLE', 'groups_users' );
define( 'SMS_SENDER_CONTACT', 'sms_sender_cell_phone' );
define( 'SMS_SENDER_FLAG_FOR_FILE_COPY', 'sender_copy_file' );

// Init options & tables during activation & deregister init option
register_activation_hook( __FILE__, 'sender_activate' );
if ( !function_exists( 'sender_activate' ) ) {
    function sender_activate()
    {
        sender_activate_database();
        add_option(SMS_SENDER_FLAG_FOR_FILE_COPY, true);
    }
}

register_deactivation_hook(__FILE__ , 'sender_deactivate');
if ( !function_exists( 'sender_deactivate' ) ) {
    function sender_deactivate()
    {
        sender_deactivate_database();
        sender_replace_file_into_wp_admin();
    }
}

add_action('admin_init', 'sender_add_file_into_wp_admin');
add_filter('user_contactmethods', 'sender_extra_user_info');

// add extra fields to registration form
add_action('register_form', 'sender_add_registration_field');
if ( !function_exists( 'sender_add_registration_field' ) ) {
    function sender_add_registration_field()
    { ?>
        <p>
            <label for="sms_sender_display_name"><?php _e('Display Name') ?><br />
                <input type="text" name="sms_sender_display_name" id="sms_sender_display_name" class="input" tabindex="21"
                       value="<?php echo esc_attr(stripslashes(dealsWithNull($_POST, 'sms_sender_display_name'))); ?>" size="25" />
            </label>
        </p>
        <p>
            <label for="sms_sender_full_name"><?php _e('Full Name') ?><br />
                <input type="text" name="sms_sender_full_name" id="sms_sender_full_name" class="input" tabindex="21"
                       value="<?php echo esc_attr(stripslashes(dealsWithNull($_POST, 'sms_sender_full_name'))); ?>" size="25" />
            </label>
        </p>
        <p>
            <label for="sms_sender_rank"><?php _e('Rank') ?><br />
                <input type="text" name="sms_sender_rank" id="sms_sender_rank" class="input" tabindex="21"
                       value="<?php echo esc_attr(stripslashes(dealsWithNull($_POST, 'sms_sender_rank'))); ?>" size="25" />
            </label>
        </p>
        <p>
            <label for="sms_sender_address"><?php _e('Address') ?><br />
                <textarea name="sms_sender_address" id="sms_sender_address" rows="5" cols="" style="width: 100%"
                          tabindex="21"><?php echo esc_attr(stripslashes(dealsWithNull($_POST, 'sms_sender_address'))) ?></textarea>
            </label>
        </p>
        <p>
            <label for="sms_sender_address_2"><?php _e('Address 2') ?><br />
                <textarea name="sms_sender_address_2" id="sms_sender_address_2" rows="5" cols="" style="width: 100%"
                          tabindex="21"><?php echo esc_attr(stripslashes(dealsWithNull($_POST, 'sms_sender_address_2'))) ?></textarea>
            </label>
        </p>
        <p>
            <label for="sms_sender_city"><?php _e('City') ?><br />
                <input type="text" name="sms_sender_city" id="sms_sender_city" class="input" tabindex="21"
                       value="<?php echo esc_attr(stripslashes(dealsWithNull($_POST, 'sms_sender_city'))); ?>" size="25" />
            </label>
        </p>
        <p>
            <label for="sms_sender_state"><?php _e('State') ?><br />
                <input type="text" name="sms_sender_state" id="sms_sender_state" class="input" tabindex="21"
                       value="<?php echo esc_attr(stripslashes(dealsWithNull($_POST, 'sms_sender_state'))); ?>" size="25" />
            </label>
        </p>
        <p>
            <label for="sms_sender_zip"><?php _e('Zip Code') ?><br />
                <input type="text" name="sms_sender_zip" id="sms_sender_zip" class="input" tabindex="21"
                       value="<?php echo esc_attr(stripslashes(dealsWithNull($_POST, 'sms_sender_zip'))); ?>" size="25" />
            </label>
        </p>
        <p>
            <label for="sms_sender_home_phone"><?php _e('Home Phone') ?><br />
                <input type="text" name="sms_sender_home_phone" id="sms_sender_home_phone" class="input" tabindex="21"
                       value="<?php echo esc_attr(stripslashes(dealsWithNull($_POST, 'sms_sender_home_phone'))); ?>" size="25" />
            </label>
        </p>
        <p>
            <label for="sms_sender_cell_phone"><?php _e('Cell Phone') ?><br />
                <input type="text" name="sms_sender_cell_phone" id="sms_sender_cell_phone" class="input" tabindex="21"
                       value="<?php echo esc_attr(stripslashes(dealsWithNull($_POST, 'sms_sender_cell_phone'))); ?>" size="25" />
            </label>
        </p>
        <p>
            <label for="sms_sender_user_group"><?php _e('User Group') ?><br />
                <select name="sms_sender_user_group" id="sms_sender_user_group" tabindex="21">
                <?php global $wpdb; $groups = $wpdb->get_results($wpdb->prepare("SELECT ID, groupname FROM {$wpdb->prefix}uam_accessgroups"));
                echo '<option value="">- Select Group -</option>';
                foreach ($groups AS $group) {
                    $isSelected = (!empty ($_POST['sms_sender_user_group']) && $_POST['sms_sender_user_group'] == $group->groupname)
                                    ? ' selected="selected"' : '';
                    echo "<option value='{$group->groupname}'{$isSelected}>{$group->groupname}</option>";
                }?>
            </select></label>
        </p>
        <?php
    }
}

add_action('register_post', 'sender_registration_check', 10, 3);

function sender_registration_check($user_login, $user_email, $errors) {

    // avoid to save stuff if user is being added from: /wp-admin/user-new.php and shit WP 3.1 changed the value just to create new bugs :@
    if (!empty($_POST["action"]) && ($_POST["action"] == "adduser" || $_POST["action"] == "createuser"))
        return $errors;

    // Now, the custom field validation is started from here.
    $fields = sender_get_extra_fields();
    foreach ($fields AS $field => $label) {
        switch ($field) {
            case 'sms_sender_display_name':
            case 'sms_sender_full_name':
            case 'sms_sender_rank':
            case 'sms_sender_address':
            case 'sms_sender_address_2':
            case 'sms_sender_city':
            case 'sms_sender_state':
                sender_check_and_set_empty_message($_POST, $field, $label, &$errors); break;

            case 'sms_sender_zip':
            case 'sms_sender_home_phone':
            case 'sms_sender_cell_phone':
                if (!sender_check_and_set_empty_message($_POST, $field, $label, &$errors)) {
                    if (!preg_match("/^([-0-9-])+$/i", $_POST[$field])) {
                        $errors->add($field, '<strong>'.__("ERROR").'</strong>: '.$label.' '.__(' can be with any number and dash.'));
                    }
                }
                break;

            case 'sms_sender_user_group':
                if (!dealsWithNull($_POST, $field)) {
                    $errors->add($field, '<strong>'.__("ERROR").'</strong>: '.$label.' '.__('must have to be selected.'));
                }
        }
    }

    return $errors;
}

// add update engine for extra fields to user's registration
add_action('user_register', 'sender_add_registration_data');
if ( !function_exists( 'sender_add_registration_data' ) ) {
    function sender_add_registration_data($user_id)
    {
        if (empty ($user_id) || empty ($_POST)) { return; }
        elseif (!empty($_POST["action"]) && ($_POST["action"] == "adduser" || $_POST["action"] == "createuser")) return;

        insert_registration_data_into_database($user_id, $_POST);
    }
}

add_action('admin_menu', 'sender_admin_action' );
if ( !function_exists( 'sender_admin_action' ) ) {
    function sender_admin_action() {
        if ( function_exists( 'add_menu_page' ) ) {
            add_menu_page('SMS Sender Plugin Options', 'SMS Sender', 'manage_options', 'sms-sender', 'sender_manage_users', '');
            add_submenu_page('sms-sender', 'Manage Users', 'Manage Users', 0, 'sms-sender', 'sender_manage_users' );
            add_submenu_page('sms-sender', 'Send SMS', 'Send SMS', 0, 'sms-sender-send', 'sender_send_sms' );
            add_submenu_page('sms-sender', 'Configure', 'Configure', 0, 'sms-sender-configure', 'sender_configure' );
        }
    }
}

if ( !function_exists( 'sender_manage_users' ) ) {
    function sender_manage_users() {
        require_once dirname(__FILE__) . '/manage-user.php';
    }
}

if ( !function_exists( 'sender_send_sms' ) ) {
    function sender_send_sms() {
        require_once dirname(__FILE__) . '/send-sms.php';
    }
}

if ( !function_exists( 'sender_configure' ) ) {
    function sender_configure() {
        require_once dirname(__FILE__) . '/configure-sender.php';
    }
}

add_action('admin_print_scripts', 'sender_script');
function sender_script() {
    wp_enqueue_script('sms-sender', path_join(WP_PLUGIN_URL, basename(dirname(__FILE__)) . '/sms-sender.js'),
        array('jquery', 'jquery-ui-autocomplete'));
}

require_once dirname(__FILE__) . '/functions-with-ajax.php';