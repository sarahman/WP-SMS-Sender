<?php
//if (check_admin_referer('sender_admin_options_update')) {
if (!empty($_POST)) {
    send_sms_content_using_url($_POST);
}
?>

<div class="wrap">
    <div id="icon-plugins" class="icon32"><br/></div>
    <h2>Sender - Send SMS</h2>

    <div id="poststuff" class="metabox-holder has-right-sidebar">

        <div id="post-body" class="has-sidebar">
            <div id="post-body-content" class="has-sidebar-content">
                <div id="normal-sortables" class="meta-box-sortables">
                    <div class="postbox">
                        <h3 class="hndle"><span>Send SMS</span></h3>

                        <div class="inside">
                            <p>You can send sms to the multiple groups of users in this blog.</p>

                            <form action="" method="post">
                                <table width="100%">
                                    <tr>
                                        <td width="20%">Group(s):</td>
                                        <td width="80%"><input type="text" name="sms_groups" id='sms_groups'
                                                               value="<?php echo esc_attr(dealsWithNull($_POST, 'sms_groups')) ?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="20%">Description:</td>
                                        <td width="80%">
                                            <textarea name="sms_content" id='sms_content' rows="10" cols=""
                                                      style="width:90%"><?php echo esc_html(dealsWithNull($_POST, 'sms_content')) ?></textarea></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td><input type="submit" id='send-sms' class="button" value="Send"/></td>
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
