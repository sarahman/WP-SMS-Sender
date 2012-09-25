<?php require_once(ABSPATH . 'wp-admin/admin.php') ?>

<div class="wrap">
    <div id='icon-users' class="icon32"><br></div>
    <h2><?php echo esc_html('Manage Users') ?></h2>
</div>
<div id="col-container">

    <div id="col-right" style="width: 48%; padding-right: 30px">
        <div class="col-wrap sender-groups">
            <h2>
                <?php echo esc_html('Groups') ?>
<!--                <a href="" id='sender-add-group' class="button">--><?php //echo esc_html('Add New') ?><!--</a>-->
            </h2>

            <?php $groups = get_user_groups();
            if (empty($groups)) : ?>
                <p>No group has been created yet. You can create groups by the upper link.</p>
            <?php else: foreach($groups AS $group) : ?>

            <table class="sender-group widefat" cellspacing="0" style="margin-bottom: 20px;">
                <thead>
                <tr class="thead">
                    <th width="50%"><?php echo ucfirst($group->name) ?></th>
                    <th width="35%">&nbsp;</th>
                    <th width="15%"><!--<a href="" rel='<?php /*echo $group->id */?>'
                           class="button sender-remove-group">--><?php /*echo esc_html('Remove Group') */?></th>
                </tr>
                </thead>

                <tbody>
                    <?php if (!empty($group->users)) {
                        foreach ($group->users AS $user) {
                            echo <<<EOF
                            <tr>
                                <td>{$user->display_name}</td>
                                <td><span class='sender-contact-{$user->ID}'>{$user->contact}</span></td>
                                <td align='center'>
                                    <span class='sender-group-user-edit' rel='{$user->ID}'><a href=''>Edit</a></span> |
                                    <span class='sender-group-user-remove' rel='{$user->ID}'><a href=''>Delete</a></span>
                                </td>
                            </tr>
EOF;
                        }
                }?>

                </tbody>
            </table>
            <?php endforeach; endif ?>

        </div>
    </div>
    <!-- /col-right -->

    <div id="col-left" style="width: 48%">
        <div class="col-wrap">
            <h2><?php echo esc_html('Users');
                if (current_user_can('create_users')) : ?>
                <a href="<?php echo site_url('wp-admin/user-new.php') ?>" class="button"><?php echo esc_html('Add New') ?></a>
                <?php endif ?>
            </h2>

            <div id='post-body'>

                <div id='post-body-content'>

                    <form id="sender-form" action="" method="get">

                        <table id="sender-users" class="widefat" cellspacing="0" width="50%">
                            <thead>
                            <tr class="thead">
                                <th width="5%"><input type='checkbox' class='sender-all-users' /></th>
                                <th width="60%">Full Name</th>
                                <th width="35%" style="text-align: center">Contact</th>
                            </tr>
                            </thead>

                            <tfoot>
                            <tr class="thead">
                                <th width="5%"><input type='checkbox' class=' sender-all-users' /></th>
                                <th width="60%">Full Name</th>
                                <th width="35%" style="text-align: center">Contact</th>
                            </tr>
                            </tfoot>

                            <tbody>
                            <?php
                            $users = get_users_with_contacts();
                            foreach ($users AS $user) {
                                echo <<<EOF
                                <tr>
                                    <td><input type='checkbox' class='check-column sender-user' value='{$user->ID}' /></td>
                                    <td>{$user->display_name}</td>
                                    <td align="center">
                                        <span class='sender-contact-{$user->ID}'>{$user->contact}</span>
                                        <span class='sender-user-edit' rel='{$user->ID}'><a href=''>Edit</a></span>
                                    </td>
                                </tr>
EOF;
                            } ?>
                            </tbody>
                        </table>

                        <div class="tablenav bottom">
                            <div class="alignleft actions">
                                <select name='action' id='sender-groups' multiple="multiple">
                                    <option>- Select Group -</option>
                                    <?php if (!empty($groups)) : foreach($groups AS $group) :
                                        echo "<option value='{$group->id}'>" . ucfirst($group->name) ."</option>";
                                    endforeach; endif ?>
                                </select>
                                <input type="submit" name="" id="sender-assign" class="button-secondary" value="Assign" />
                            </div>
                            <div class="tablenav-pages one-page">
                                <span class="displaying-num"><?php echo count($users) ?> users</span>
                            </div>
                            <br class="clear">
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
    <!-- /col-left -->

</div>

<script type="text/javascript">

    var users = <?php echo json_encode($users) ?>;
    var groups = <?php echo json_encode($groups) ?>;

</script>