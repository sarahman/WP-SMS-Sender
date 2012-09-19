<?php
//if (check_admin_referer('sender_admin_options_update')) {
if (!empty($_POST)) {
    update_option('sender_gateway_username', esc_html($_POST['sender_gateway_username']));
    update_option('sender_gateway_password', esc_html($_POST['sender_gateway_password']));
    update_option('sender_gateway_api_id', esc_html($_POST['sender_gateway_api_id']));
    $success = true;
}
?>
<div class="wrap">
    <?php empty($success) || print("<div id='success' class='updated'>Credentials have been successfully saved.</div>") ?>
    <div id="icon-plugins" class="icon32"><br/></div>
    <h2>Sender - Configuration of Sender Info</h2>

    <div id="poststuff" class="metabox-holder has-right-sidebar">

        <div id="post-body" class="has-sidebar">
            <div id="post-body-content" class="has-sidebar-content">
                <div id="normal-sortables" class="meta-box-sortables">
                    <div class="postbox">
                        <h3 class="hndle"><span>Configure Info</span></h3>

                        <div class="inside">
                            <p>This is the information by which the sms is to be sent.</p>

                            <form action="" method="post">
                                <table width="100%">
                                    <tr>
                                        <td width="20%">Username:</td>
                                        <td width="80%"><input type="text" name="sender_gateway_username"
                                                               value="<?php echo esc_attr(get_option('sender_gateway_username')) ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="20%">Password:</td>
                                        <td width="80%"><input type="text" name="sender_gateway_password"
                                                               value="<?php echo esc_attr(get_option('sender_gateway_password')) ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="20%">API ID:</td>
                                        <td width="80%"><input type="text" name="sender_gateway_api_id"
                                                               value="<?php echo esc_attr(get_option('sender_gateway_api_id')) ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td><input type="submit" class="button" value="Send"/></td>
                                        <?php echo wp_nonce_field('sender_admin_options_update') ?>
                                    </tr>
                                </table>
                            </form>
                        </div>
                        <!--- class='inside' --->
                    </div>
                    <!--- class='postbox ' --->
                </div>
                <!--- class='meta-box-sortables' --->
            </div>
            <!--- class="has-sidebar-content" --->
        </div>
        <!--- class="has-sidebar" --->

    </div>
    <!--- class="metabox-holder" --->
</div> <!-- class="wrap" -->
