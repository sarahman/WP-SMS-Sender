
$(function() {

    initialize();
    bindActions();

    function initialize() {
        $("input[name$='backupChoice']").removeAttr('checked');
        checkCreateBackupOption();
    }

    function bindActions() {

        $("input[id='fullBackup']").click(function() {

            $("#RestoreOptions").hide("fast");
            $("#file_directory").hide("fast");
            $("input[name$='createBackup']").attr('checked', true);
            $("input[name$='backupUrl']").removeAttr('checked');
            $("#backupChoices").show("fast");
            $("input#submit").val("Create Backup");

        });

        $("input[id='customBackup']").click(function() {

            $("#RestoreOptions").hide("fast");
            $("#file_directory").show("fast");
            $("input[name$='createBackup']").attr('checked', true);
            $("input[name$='backupUrl']").removeAttr('checked');
            $("#backupChoices").show("fast");
            $("input#submit").val("Create Backup");

        });

        $("input[name$='createBackup']").click(function() {

            $("#RestoreOptions").hide("fast");
            $("input[name$='backupUrl']").attr('checked', false);
            $("input[class$='restoreBackup']").each(function(){ $(this).attr('checked', false) });
            checkCreateBackupOption();

        });

        $("input[class$='restoreBackup']").click(function() {

            $("#RestoreOptions").show("fast");
            $("input[name$='backupUrl']").attr('checked', false);
            $(this).attr('checked', true);
            unCheckCreateBackupOption();
            $("input#submit").val("Restore backup");

        });

        $("input[name$='backupUrl']").click(function() {
            prepareBackUrlOption();
        });

        $("input[name$='restore_from_url']").focus(function() {
            prepareBackUrlOption();
        });

        $("input#submit").click(function() {

            if ($('#backupUrl').is(':checked')) {

                if ($("input[name$='restore_from_url']").val() == '') {
                    alert('Please enter the url you want to restore from.');
                } else if (!$('#approve').is(':checked')) {
                    alert('Please confirm that you have agreed or not.');
                } else {
                    return getConfirmation('restore');
                }

                return false;

            } else if ($('input[class$="restoreBackup"]').is(':checked')) {

                if ($('#approve').is(':checked')) {
                    return getConfirmation('restore');
                }

                alert('Please confirm that you have agreed or not.');
                return false;

            } else {
                return getConfirmation('create backup');
            }

            function getConfirmation(toDo) {
                return confirm('This may take a few minutes. Do you proceed to ' + toDo + ' now?');
            }
        });

        function unCheckCreateBackupOption() {
            $("input[name$='createBackup']").attr('checked', false);
            $("#backupChoices").hide("fast");
        }

        function prepareBackUrlOption() {
            $("#RestoreOptions").show("fast");
            $("input[name$='backupUrl']").attr('checked', true);
            $("input[class$='restoreBackup']").attr('checked', false);
            unCheckCreateBackupOption();
            $("input#submit").val('Restore from URL');
        }

    }

    function checkCreateBackupOption() {
        $("input[name$='createBackup']").attr('checked', true);
        $("#backupChoices").show("fast");
        $("input#submit").val("Create Backup");
        $("input[id='fullBackup']").attr('checked',
            $("input[name$='createBackup']").is(':checked') && !$("input[id$='customBackup']").is(':checked'));
    }

});