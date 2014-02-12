$(document).ready(function() {

    if ( $("#ThreadEditForm_permissions_accessLevel_2").is(':checked')) { // specific users
        $("ul.tagit").show();
        $("#ThreadEditForm_permissions_userGroupsWithAccess").hide();
    }
    else if ( $("#ThreadEditForm_permissions_accessLevel_3").is(':checked')) { // specific groups
        $("#ThreadEditForm_permissions_userGroupsWithAccess").show();
        $("ul.tagit").hide();
    }
    else { // hide all
        $("#ThreadEditForm_permissions_userGroupsWithAccess").hide();
        $("ul.tagit").hide();
    }
	
	$("#permRadioButtons").click(function() {
        if ( $("#ThreadEditForm_permissions_accessLevel_2").is(':checked')) { // specific users
//             $("#ThreadEditForm_permissions_usersWithAccess").show();
            $("ul.tagit").show();
            $("#ThreadEditForm_permissions_userGroupsWithAccess").hide();
        }
        else if ( $("#ThreadEditForm_permissions_accessLevel_3").is(':checked')) { // specific groups
            $("#ThreadEditForm_permissions_userGroupsWithAccess").show();
            $("ul.tagit").hide();
        }
        else { // hide all
//            $("#ThreadEditForm_permissions_userFriendsWithAccess").hide();
            $("#ThreadEditForm_permissions_userGroupsWithAccess").hide();
//             $("#ThreadEditForm_permissions_usersWithAccess").hide();
            $("ul.tagit").hide();
        }
    });
	
});