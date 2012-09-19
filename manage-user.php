<?php require_once(ABSPATH . 'wp-admin/admin.php') ?>

<div class="wrap">
    <div id='icon-users' class="icon32"><br></div>
    <h2><?php echo esc_html('Manage Users');
        if (current_user_can('create_users')) : ?>
            <a href="<?php echo site_url('wp-admin/user-new.php') ?>"
               class="button add-new-h2"><?php echo esc_html_x('Add New', 'user'); ?></a>
            <?php endif ?>
    </h2>
</div>
<div id="col-container">

    <div id="col-right">
        <div class="col-wrap">

            <?php $groups = get_users_groups();
            foreach($groups AS $group) : ?>

            <table class="widefat fixed droppable" cellspacing="0" style="margin-bottom: 20px;">
                <thead>
                <tr class="thead">
                    <th width="20%" colspan="2"><?php echo $group->name ?></th>
                </tr>
                </thead>

                <tbody class="sender-groups">
                    <?php
                    $users = get_users_with_contacts();
                    foreach ($group->users AS $user) {
                        echo <<<EOF
                        <tr>
                        <span class='draggable'>
                            <td>{$user->display_name}</td>
                            <td>
                                <span class='sender-contact'>{$user->contact}</span>
                                <span class='sender-user-edit' rel='{$user->ID}'><a href=''>Edit</a></span>
                            </td>
                        </span>
                        </tr>
EOF;
                    } ?>

                </tbody>
            </table>
            <?php endforeach ?>

        </div>
    </div>
    <!-- /col-right -->

    <div id="col-left">
        <div class="col-wrap">

        <div id='post-body'>

            <div id='post-body-content'>

                <form id="sender-form" action="" method="get">

                    <table class="widefat fixed" cellspacing="0" width="50%">
                        <thead>
                        <tr class="thead">
                            <th width="20%">Full Name</th>
                            <th width="10%">Contact</th>
                        </tr>
                        </thead>

                        <tfoot>
                        <tr class="thead">
                            <th width="20%">Full Name</th>
                            <th width="10%">Contact</th>
                        </tr>
                        </tfoot>

                        <tbody id="sender-users">
                        <?php
                        $users = get_users_with_contacts();
                        foreach ($users AS $user) {
                            echo <<<EOF
                            <tr class='draggable'>
                                <td>{$user->display_name}</td>
                                <td>
                                    <span class='sender-contact'>{$user->contact}</span>
                                    <span class='sender-user-edit' rel='{$user->ID}'><a href=''>Edit</a></span>
                                </td>
                            </tr>
EOF;
                        } ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>

        </div>
    </div>
    <!-- /col-left -->

</div>