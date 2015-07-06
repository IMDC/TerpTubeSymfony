define([
    'model/model',
    'extra',
    'underscore'
], function (Model) {
    'use strict';

    var UserModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);
    };

    UserModel.extend(Model);

    UserModel.prototype.isUserOnFriendsList = function (username) {
        return _.contains(this.get('friends_list'), username);
    };

    UserModel.prototype.isUserOnMentorList = function (username) {
        return _.contains(this.get('mentor_list'), username);
    };

    UserModel.prototype.isUserOnMenteeList = function (username) {
        return _.contains(this.get('mentee_list'), username);
    };

    UserModel.prototype.isUserOnInvitedMentorList = function (username) {
        var result = _.find(this.get('created_invitations'), function (invite) {
            return invite.recipient == username && !invite.isMentor && !invite.isAccepted && !invite.isCancelled && !invite.isDeclined;
        });

        return result !== 'undefined';
    };

    UserModel.prototype.isUserOnInvitedMenteeList = function (username) {
        var result = _.find(this.get('created_invitations'), function (invite) {
            return invite.recipient == username && !invite.isMentee && !invite.isAccepted && !invite.isCancelled && !invite.isDeclined;
        });

        return result !== 'undefined';
    };

    return UserModel;
});
