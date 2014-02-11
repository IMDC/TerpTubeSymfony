$(document).ready(function() {
	
	$("#permRadioButtons").click(function() {
        if ( $("#ThreadEditForm_permissions_accessLevel_2").is(':checked')) { // specific users
//             $("#ThreadEditForm_permissions_userListWithAccess").show();
            $("ul.tagit").show();
            $("#ThreadEditForm_permissions_userGroupsWithAccess").hide();
//             $("#ThreadEditForm_permissions_userFriendsWithAccess").hide();
        }
        else if ( $("#ThreadEditForm_permissions_accessLevel_3").is(':checked')) { // specific groups
            $("#ThreadEditForm_permissions_userGroupsWithAccess").show();
//             $("#ThreadEditForm_permissions_userFriendsWithAccess").hide();
            $("ul.tagit").hide();
        }
//         else if ( $("#ThreadEditForm_permissions_accessLevel_1").is(':checked')) { // user friends
//             $("#ThreadEditForm_permissions_userFriendsWithAccess").show();
//             $("#ThreadEditForm_permissions_userGroupsWithAccess").hide();
//             $("ul.tagit").hide();
//         }
        else { // hide all
            $("#ThreadEditForm_permissions_userFriendsWithAccess").hide();
            $("#ThreadEditForm_permissions_userGroupsWithAccess").hide();
//             $("#ThreadEditForm_permissions_userListWithAccess").hide();
            $("ul.tagit").hide();
        }
    });
	
});