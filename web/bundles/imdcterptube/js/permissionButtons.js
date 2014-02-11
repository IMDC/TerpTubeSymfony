$(document).ready(function() {

    $("#permRadioButtons").click(function() {
        if ( $("#ThreadForm_permissions_accessLevel_2").is(':checked')) { // specific users
//             $("#ThreadForm_permissions_userListWithAccess").show();
            $("ul.tagit").show();
            $("#ThreadForm_permissions_userGroupsWithAccess").hide();
//             $("#ThreadForm_permissions_userFriendsWithAccess").hide();
        }
        else if ( $("#ThreadForm_permissions_accessLevel_3").is(':checked')) { // specific groups
            $("#ThreadForm_permissions_userGroupsWithAccess").show();
//             $("#ThreadForm_permissions_userFriendsWithAccess").hide();
            $("ul.tagit").hide();
        }
//         else if ( $("#ThreadForm_permissions_accessLevel_1").is(':checked')) { // user friends
//             $("#ThreadForm_permissions_userFriendsWithAccess").show();
//             $("#ThreadForm_permissions_userGroupsWithAccess").hide();
//             $("ul.tagit").hide();
//         }
        else { // hide all
            $("#ThreadForm_permissions_userFriendsWithAccess").hide();
            $("#ThreadForm_permissions_userGroupsWithAccess").hide();
//             $("#ThreadForm_permissions_userListWithAccess").hide();
            $("ul.tagit").hide();
        }
    });
});