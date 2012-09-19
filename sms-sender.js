jQuery(function($) {
    $('.sender-user-edit').live('click', function() {
        var editSpan = $(this);
        var userId = $(editSpan).attr('rel');
        var contact = prompt('Enter user mobile number: ', $(this).prev().text());

        if (contact) {
            $.post(ajaxurl, { userId: userId, contact: contact, action: 'sender_insert_contact' }, function(response) {

                if (response.status) {
                    $(editSpan).prev().fadeOut('fast', function() {
                        $(this).text(contact);
                        return $(this);
                    }).fadeIn('slow');
                } else {
                    alert(response.msg);
                }
            }, 'json');
        }
        return false;
    });


//    $( ".draggable" ).draggable();
//    $( ".droppable" ).droppable({
//        drop: function( event, ui ) {
//            $( this )
//                .addClass( "ui-state-highlight" );
//            alert('dropped');
//        }
//    });
    $('#sms_phone').live('keyup', function() {
        $(this).autocomplete({
            source: ajaxurl + '?action=sender_suggest_contact&contact='+$(this).val(),
            minLength: 2
        });
//        return false;
    });
});