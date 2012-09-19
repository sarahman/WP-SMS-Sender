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
        var $phoneID = $(this);
        var contact = $phoneID.val();
        $(this).suggest(ajaxurl + '?action=sender_suggest_contact&contact='+contact, { multiple:true, multipleSep: ","});
//jQuery('#' + id).suggest("<?php echo get_bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php?action=ajax-tag-search&tax=post_tag", {multiple:true, multipleSep: ","});
//        console.log(contact);
//        if (contact) {
//            $.post(ajaxurl, { contact: contact, action: 'sender_suggest_contact' }, function(response) {
//
//                console.log(response);
//            }, 'json');
//        }
        return false;
    });
});