<link type="text/css" href="<?php echo WPCLONE_URL_PLUGIN ?>lib/css/style.css" rel="stylesheet" />
<?php
include_once 'DirectoryTree.php';

function processRestoringBackup($url)
{
    $pathParts = pathinfo($url);

    $urlDir = WPCLONE_DIR_BACKUP . 'url/';
    file_exists($urlDir) || mkdir($urlDir, WPBACKUP_FILE_PERMISSION);

    /* Copy the file found from url to plugin root */
    $zipFilename = $urlDir . $pathParts['basename'];
    DirectoryTree::CopyDirectory($url, $zipFilename);

    $result = unzipBackupFile($zipFilename, WPCLONE_ROOT);

    if ($result) {

        $unzippedFolderPath = wpCloneSafePathMode(WPCLONE_ROOT . $pathParts['filename']);
        $configFileInZip = $unzippedFolderPath . '/wp-config.php';
        $databaseFile = $unzippedFolderPath . '/database.sql';

        $currentSiteUrl = processConfigAndDatabaseFile($configFileInZip, $databaseFile);

        !file_exists($databaseFile) || unlink($databaseFile);
        wpBackupFullCopy($unzippedFolderPath, WPCLONE_ROOT);
        DirectoryTree::DeleteAllDirectoryFiles($unzippedFolderPath, true);

        echo "<h1>Restore Successful!</h1>";

        echo "Visit your restored site [ <a href='{$currentSiteUrl}' target=blank>here</a> ]<br><br>";

        echo "<strong>You may need to re-save your permalink structure <a href='{$currentSiteUrl}/wp-admin/options-permalink.php' target=blank>Here</a></strong>";

    } else {

        echo "<h1>Restore unsuccessful!!!</h1>";

        echo "Please try again.";
    }

    !file_exists($urlDir) || DirectoryTree::DeleteAllDirectoryFiles($urlDir, true);
}

if (isset($_POST['createBackup'])) {

    get_currentuserinfo();

    $backupName = getBackupFileName();

    if ($_POST['createBackup'] == 'fullBackup') {

        /* Creating full backup */
        list($zipFileName, $zipSize) = CreateWPFullBackupZip($backupName);

    } elseif ($_POST['backupChoice'] == 'customBackup') {

        /* Creating custom backup */
        list($zipFileName, $zipSize) = CreateWPCustomBackupZip($backupName, $_POST['directory_folders']);

    } else {
        $zipFileName = $zipSize = '';
    }

    /* Creating plugin backup */
    $installerFileZip = CreateWPClonePluginBackupZip($backupName, $zipFileName);

    InsertData($zipFileName, $zipSize, $installerFileZip);
    $backZipPath = convertPathIntoUrl(WPCLONE_DIR_BACKUP . $zipFileName);

    echo <<<EOF

<h1>Backup Successful!</h1>

<br />

Here is your backup file : <br />

    <a href='{$backZipPath}'><span>{$backZipPath}</span></a> ( {$zipSize} ) &nbsp;&nbsp;|&nbsp;&nbsp;
    <input type='hidden' name='backupUrl' class='backupUrl' value="{$backZipPath}" />
    <a class='copy-button' href='#'>Copy URL</a> &nbsp;<br /><br />

    (Copy that link and paste it into the "Restore URL" of you new WordPress installation to clone this site)
EOF;


} elseif (!empty($_REQUEST['del'])) {

    $deleteRow = DeleteWPBackupZip($_REQUEST['del']);

    echo <<<EOT
        <h1>Deleted Successful!</h1> <br />

        {$deleteRow->backup_name} <br />

        {$deleteRow->installer_name} <br />

        File deleted from backup folder and database...
EOT;

} elseif (isset($_POST['restoreBackup'])) {

    foreach ($_POST['restoreBackup'] AS $url) {
        processRestoringBackup($url);
        break;
    }

}

elseif (isset($_POST['backupUrl'])) {

    $url = $_POST['restore_from_url'];
    processRestoringBackup($url);

}

else {

global $wpdb;

$result = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wp_clone ORDER BY id DESC", ARRAY_A);

?>

<div class="MainView">

    <h2>Welcome to WP Clone </h2>

    <p>You can use this tool to create a backup of this site and (optionally) restore it on another server.</p>

    <p><strong>Here is how it works:</strong> the "Backup" function will give you a URL that you can then copy and paste
        into the "Restore" dialog of a new WordPress site, which will clone the original site to the new site. You must
        install the plugin on the new site and then run the WP Clone > Restore function.</p>

    <p><a href="http://wpacademy.tv/forum/reference/wpclone/" target="_blank">Click here</a> for help. Commercial
        license holders may also access support <a href="http://wpacademy.tv/wpclone-download" target="_blank">click
            here</a>.</p>

    <p><strong>Choose your selection below:</strong> either create a backup of this site, or choose which backup you
        would like to restore.</p>

    <p>&nbsp;</p>

    <form id="backupForm" name="backupForm" action="<?php $_SERVER['SERVER_NAME']; ?>" method="post">

        <strong>Create Backup</strong>
        <input id="createBackup" name="createBackup" type="radio" value="fullBackup"/><br/><br/>
<!--        <div style="padding-left: 50px" id="backupChoices">-->
<!--            <strong>Full Backup</strong>-->
<!--            <input id="fullBackup" name="backupChoice" type="radio" value="fullBackup"/><br/>-->
<!---->
<!--<!--<!--            <strong>Custom Backup</strong> <input id="customBackup" name="backupChoice" type="radio" value="customBackup"/><br/>-->
<!--<!--<!--            <div id="file_directory"></div>-->
<!--        </div>-->

        <?php if (count($result) > 0) : ?>

        <div class="try">

            <?php foreach ($result AS $row) :

            $filename = convertPathIntoUrl(WPCLONE_DIR_BACKUP . $row['backup_name']) ?>

            <div class="restore-backup-options">
                <strong>Restore backup </strong>

                <input class="restoreBackup" name="restoreBackup[]" type="radio"
                       value="<?php echo $filename ?>" />&nbsp;

                <a href="<?php echo $filename ?>">
                    (&nbsp;<?php echo bytesToSize($row['backup_size']);?>&nbsp;)&nbsp; <?php echo $row['backup_name'] ?>
                </a>&nbsp;|&nbsp;

                <input type="hidden" name="backup_name" value="<?php echo $filename ?>" />

                <a class="copy-button" href="#">Copy URL</a> &nbsp;|&nbsp;
                <a href="<?php echo site_url()?>/wp-admin/options-general.php?page=wp-clone&del=<?php echo $row['id'];?>">Delete</a>
            </div>

            <?php endforeach ?>

        </div>

        <?php endif ?>

        <strong>Restore from URl:</strong><input id="backupUrl" name="backupUrl" type="radio" value="backupUrl"/>

        <input type="text" name="restore_from_url" class="Url" value="" size="80px"/><br/><br/>

        <div class="RestoreOptions" id="RestoreOptions">

            <input type="checkbox" name="approve" id="approve" /> I AGREE (Required for "Restore" function):<br/>

            1. You have nothing of value in your current site <strong>[<?php echo site_url() ?>]</strong><br/>

            2. Your current site at <strong>[<?php echo site_url() ?>]</strong> may become unusable in case of failure,
            and you will need to re-install WordPress<br/>

            <?php

            require_once(WPCLONE_ROOT . "wp-config.php");

            $dbInfo = getDbInfo(get_defined_vars());

            ?>

            3. Your WordPress database <strong>[<?php if (isset($dbInfo['dbname'])) {
            echo $dbInfo['dbname'];
        }?>]</strong> will be overwritten from the database in the backup file. <br/>

        </div>

        <input id="submit" name="submit" class="button-primary" type="submit" value="Create Backup"/>

    </form>

</div>

<?php } ?>


<script type="text/javascript" src="<?php echo WPCLONE_URL_PLUGIN ?>lib/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo WPCLONE_URL_PLUGIN ?>lib/js/jquery.zclip.min.js"></script>
<script type="text/javascript" src="<?php echo WPCLONE_URL_PLUGIN ?>lib/js/BackupManager.js"></script>
<script type="text/javascript">

    var folders = <?php echo json_encode(DirectoryTree::getDirectoryFolders(rtrim(WPCLONE_ROOT, "/\\"), 2)); ?>;
    var rootPath = '<?php echo dirname(rtrim(WPCLONE_ROOT, "/\\")) ?>';
    var root = '<?php echo basename(rtrim(WPCLONE_ROOT, "/\\")) ?>';

    $(function() {

        var fileTree = buildDirectoryFolderTreeWithCheckBox(folders, root, rootPath);

        fileTree.appendTo('#file_directory');

        $('.directory-component').click(function() {
            $(this).next().next().find('input[type="checkbox"]').attr('checked', $(this).is(':checked'));
            if ($(this).parent().attr('id') == 'file_directory') {
                return;
            }

            unCheckParent($(this));
            function unCheckParent(element) {
                if ($(element).parent().attr('id') == 'file_directory') {
                    return;
                }

                var parent = $(element).parent().parent().prev().prev().attr('checked', $(element).is(':checked')
                && ! ($(element).parent().siblings("li").children("li > input[type='checkbox']").not(':checked').length));

                if (parent.length) {
                    unCheckParent(parent);
                }
            }
        });

        $(".copy-button").zclip({
            path: "<?php echo WPCLONE_URL_PLUGIN ?>lib/js/ZeroClipboard.swf",
            copy: function(){
                return $(this).prev().val();
            }
        });

        $(".try pre.js").snippet("javascript",{
            style:'print',
            clipboard:'<?php echo WPCLONE_URL_PLUGIN ?>lib/js/ZeroClipboard.swf',
            collapse:'true',
            showMsg:'View Source Code',
            hideMsg:'Hide Source Code'
        });
        $("pre.js").snippet("javascript",{
            style:'print',
            clipboard:'<?php echo WPCLONE_URL_PLUGIN ?>lib/js/ZeroClipboard.swf'
        });
        $("pre.html").snippet("html",{
            style:'print',
            clipboard:'<?php echo WPCLONE_URL_PLUGIN ?>lib/js/ZeroClipboard.swf'
        });
        $("pre.css").snippet("css",{
            style:'print',
            clipboard:'<?php echo WPCLONE_URL_PLUGIN ?>lib/js/ZeroClipboard.swf'
        });

        $('a#copy-description').zclip({
            path:'<?php echo WPCLONE_URL_PLUGIN ?>lib/js/ZeroClipboard.swf',
            copy:$('p#description').text()
        });

        $('a#copy-dynamic').zclip({
            path:'<?php echo WPCLONE_URL_PLUGIN ?>lib/js/ZeroClipboard.swf',
            copy:function(){
                return $('input#dynamic').val();
            }
        });

        function buildDirectoryFolderTreeWithCheckBox(files, folderName, path) {

            var tree = $("<ul></ul>"), file, li;
            for (file in files) {

                if (typeof files[file] == "object") {

                    li = $('<li></li>').addClass('folder')
                                       .append(buildDirectoryFolderTreeWithCheckBox(files[file], file, path+'/'+folderName));

                }

                tree.append(li);
            }

            return $('<input />').attr({'type': 'checkbox', 'class': 'directory-component',
                                        'name': 'directory_folders[]', 'value': path+'/'+folderName})
                                 .after($('<span></span>').attr({'class': 'parent'}).html(folderName).click(function() {
                                        $(this).parent().find('ul:first').toggle();
                                    }))
                                 .after(tree.hide());
        }

    });

</script>