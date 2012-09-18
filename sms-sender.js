jQuery(function($) {
    $('.sender-user-edit').live('click', function() {
        var contact = prompt('Enter user mobile number: ');

        $.post(ajaxurl, { name:params.name, action: 'sender_insert_contact' }, function(response) {

            alert(response);
        }, 'json');
        alert(contact);
        return false;
    });
});