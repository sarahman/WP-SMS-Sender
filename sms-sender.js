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

    $('.sender-all-users').click(function() {
        $('.sender-user').attr('checked', $(this).is(':checked'));
        $('.sender-all-users').attr('checked', $(this).is(':checked'));
    });

    $('input#sender-assign').live('click', function() {
        if ($('.sender-user:checked').length == 0) {
            alert('Please select users before assigning.');
        } else if ($('#sender-groups option:selected').length == 0) {
            alert('Please select a group to assign users in.');
        } else {
            var userIds = [], groupIds = [], countUser = 0, countGroup = 0;
            $('.sender-user:checked').each(function() {
                userIds[countUser++] = $(this).val();
            });
            $('#sender-groups option:selected').each(function() {
                groupIds[countGroup++] = $(this).val();
            });

            $.post(ajaxurl, {action: 'sender_add_user_in_group', userIds: userIds, groupIds: groupIds}, function(response) {
                if (response.status) {
//                    insertUserRowIntoGroupTable(userIds, groupIds);
                    window.location = '';
                } else {
                    alert('Something went wrong while assigning users in the group(s).');
                }
            }, 'json');
        }
        return false;

        function insertUserRowIntoGroupTable(userIds, groupIds) {

            var currentGroup, currentUser;
            for (currentGroup = 0; currentGroup < groupIds.length; ++currentGroup) {
                for (currentUser = 0; currentUser < userIds.length; ++currentUser) {
                    if (checkUserNotInGroupTable(userIds[currentUser], groupIds[currentGroup])
                        && checkUserInUserTable(userIds[currentUser])) {
                    }
                }
            }
        }

        function checkUserNotInGroupTable(userId, groupId) {
            for (var currentGroup = 0; currentGroup < groups.length; ++currentGroup) {

                console.log(userId, groupId, groups[currentGroup], groups[currentGroup].users(userId));

                if (groupId == groups[currentGroup].id) {
                    if (typeof groups[currentGroup].users.userId == undefined) {
                        return true;
                    }
                }
            }

            return false;
        }

        function checkUserInUserTable(userId) {
            for (var currentUser = 0; currentUser < users.length; ++currentUser) {
                if (userId == users[currentUser].ID) {
                    return true;
                }
            }

            return false;
        }
    });

    $('#sender-add-group').live('click', function() {
        var group = prompt("Enter group name: ");
        if (group) {
            $.post(ajaxurl, {action: 'sender_add_group', name: group}, function(response) {
                if (response.status) {
                    window.location = '';
                } else {
                    alert('Something went wrong while adding group.');
                }
            }, 'json');
        }
        return false;
    });

    $("#sms_phone")
        // don't navigate away from the field on tab when selecting an item
        .live( "keydown", function( event ) {
            if ( event.keyCode === $.ui.keyCode.TAB &&
                    $( this ).data( "autocomplete" ).menu.active ) {
                event.preventDefault();
            }
        })
        .autocomplete({
            minLength: 2,
            source: function( request, response ) {
                $.getJSON( ajaxurl, {
                    action: 'sender_suggest_contact', contact: extractLast( request.term )
                }, response );
            },
            search: function() {
                // custom minLength
                var term = extractLast( this.value );
                if ( term.length < 2 ) {
                    return false;
                }
            },
            focus: function() {
                // prevent value inserted on focus
                return false;
            },
            select: function( event, ui ) {
                var terms = split( this.value );
                // remove the current input
                terms.pop();
                // add the selected item
                terms.push( ui.item.value );
                // add placeholder to get the comma-and-space at the end
                terms.push( "" );
                this.value = terms.join( ", " );
                return false;
            }
        });
    function split( val ) {
        return val.split( /,\s*/ );
    }
    function extractLast( term ) {
        return split( term ).pop();
    }
});