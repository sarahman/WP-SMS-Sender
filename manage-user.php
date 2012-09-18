<?php require_once(ABSPATH . 'wp-admin/admin.php') ?>

<div class="wrap">
    <div id='icon-users' class="icon32"><br></div>
    <h2><?php echo esc_html('Manage Users');
        if (current_user_can('create_users')) : ?>
            <a href="<?php echo site_url('wp-admin/user-new.php') ?>"
               class="button add-new-h2"><?php echo esc_html_x('Add New', 'user'); ?></a>
            <?php endif ?>
    </h2>

    <div id='poststuff' class="metabox-holder has-right-sidebar">

        <div id="side-info-column" class="inner-sidebar">
            <div id="side-sortables" class="meta-box-sortables ui-sortable">
                <div id="linksubmitdiv" class="postbox">
                    <div class="handlediv" title="Click to toggle"><br></div>
                    <h3 class="hndle"><span>Groups</span></h3>

                    <div class="inside">

                        <table class="widefat fixed" cellspacing="0">

                            <tbody id="sender-groups">
                            <?php
                            $groups = get_users_groups();
                            $count = 0;
                            foreach ($groups AS $group) {
                                echo <<<EOF
                                <tr>
                                    <td>{$group->id}</td>
                                    <td>{$group->name}</td>
                                </tr>
EOF;
                            } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id='post-body'>

            <div id='post-body-content'>

                <form id="sender-form" action="" method="get">

                    <table class="widefat fixed" cellspacing="0" width="50%">
                        <thead>
                        <tr class="thead">
                            <?php for ($count = 1; $count <= 3; $count++) : ?>
                            <th width="20%">Full Name</th>
                            <th width="10%">Contact</th>
                            <?php endfor ?>
                        </tr>
                        </thead>

                        <tfoot>
                        <tr class="thead">
                            <?php for ($count = 1; $count <= 3; $count++) : ?>
                            <th width="20%">Full Name</th>
                            <th width="10%">Contact</th>
                            <?php endfor ?>
                        </tr>
                        </tfoot>

                        <tbody id="sender-users">
                        <?php
                        $users = get_users_with_contacts();
                        $count = 0;
                        foreach ($users AS $user) {
                            if ($count % 3 == 0) echo "<tr>";
                            echo <<<EOF
                            <span class='draggable'>
                                <td>{$user->display_name}</td>
                                <td>
                                    <span class='sender-contact'>{$user->contact}</span>
                                    <span class='sender-user-edit' rel='{$user->ID}'><a href=''>Edit</a></span>
                                </td>
                            </span>
EOF;
                            if (++$count % 3 == 0) echo "</tr>";
                        } ?>
                                <td>ABID</td>
                                <td>

                                <span class='sender-user-edit' rel='5'><a href=''>Edit</a></span>
                                </td>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

<!--<script type="text/javascript">-->
<!--    jQuery(function($) {-->
<!--        $('.sender-user-edit').live('click', function() {-->
<!--            var contact = prompt('Enter user mobile number: ');-->
<!---->
<!--            $.post('list/add', { name:params.name }, callback, 'json');-->
<!--            alert(contact);-->
<!--            return false;-->
<!--        });-->
<!--    });-->
<!--</script>-->