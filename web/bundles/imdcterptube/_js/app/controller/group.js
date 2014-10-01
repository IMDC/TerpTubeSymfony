define(['core/mediaChooser'], function(MediaChooser) {
    "use strict";

    var Group = function(options) {
        console.log("%s: %s- options=%o", Group.TAG, "constructor", options);

        this.page = options.page;
        this.mediaChooser = null;

        this.bind__onClickUserSelect = this._onClickUserSelect.bind(this);
        this.bind__submitSelectedUsersForm = this._submitSelectedUsersForm.bind(this);
        this.bind__onPageLoaded = this._onPageLoaded.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);

        $tt._instances.push(this);
    };

    Group.TAG = "Group";

    Group.Page = {
        NEW: 0,
        EDIT: 1,
        ADD_MEMBERS: 2,
        VIEW: 3
    };

    Group.Binder = {
        TOGGLE_MEMBER_SELECT: ".group-toggle-member-select",
        USER_CONTAINER_SELECT: ".user-container-select", //TODO move to user controller
        USER_SELECT: ".user-select", //TODO move to user controller
        USER_SELECTED: ".user-selected" //TODO move to user controller
    };

    // this must be the same name defined in {bundle}/Form/Type/UserGroupType
    Group.FORM_NAME = "UserGroupForm_userGroupForum";

    Group.prototype.getContainer = function() {
        return $("body");
    };

    Group.prototype.getForm = function() {
        return this.getContainer().find("form[name=" + Group.FORM_NAME + "]");
    };

    Group.prototype.getFormField = function(fieldName) {
        return this.getContainer().find("#" + Group.FORM_NAME + "_" + fieldName);
    };

    Group.prototype.bindUIEvents = function() {
        console.log("%s: %s", Group.TAG, "bindUIEvents");

        switch (this.page) {
            case Group.Page.NEW:
            case Group.Page.EDIT:
                this._bindUIEventsNewEdit();
                break;
            case Group.Page.ADD_MEMBERS:
                this._bindUIEventsAddMembers();
                break;
            case Group.Page.VIEW:
                this._bindUIEventsView();
                break;
        }
    };

    Group.prototype._bindUIEventsNewEdit = function() {
        console.log("%s: %s", Group.TAG, "_bindUIEventsNewEdit");

        this.mediaChooser = new MediaChooser();
        $(this.mediaChooser).on(MediaChooser.Event.PAGE_LOADED, this.bind__onPageLoaded);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.setContainer(this.getContainer());
        this.mediaChooser.bindUIEvents();
    };

    Group.prototype._bindUIEventsAddMembers = function() {
        console.log("%s: %s", Group.TAG, "_bindUIEventsAddMembers");

        $(Group.Binder.USER_SELECT).on("click", this.bind__onClickUserSelect);

        $(Group.Binder.USER_SELECT).each(function(key, element) {
            $(element).html($(element).data("select"));
        });

        $("#addSelected").on("click", this.bind__submitSelectedUsersForm);
    };

    Group.prototype._bindUIEventsView = function() {
        console.log("%s: %s", Group.TAG, "_bindUIEventsView");

        $(Group.Binder.USER_SELECT).on("click", this.bind__onClickUserSelect);

        $(Group.Binder.USER_SELECT).each(function(key, element) {
            $(element).html($(element).data("select"));
        });

        $(Group.Binder.TOGGLE_MEMBER_SELECT).on("click", function(e) {
            e.preventDefault();

            $(Group.Binder.USER_CONTAINER_SELECT).toggle();
            $("#deleteSelected").parent().toggleClass("disabled");
        });

        $("#deleteSelected").on("click", this.bind__submitSelectedUsersForm);
    };

    Group.prototype._onClickUserSelect = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        var elem = $(e.target);
        var isSelected = elem.html() == elem.data("selected");

        elem.html(isSelected
                ? elem.data("select")
                : elem.data("selected")
        );

        elem.toggleClass("btn-default btn-success user-selected");
    };

    Group.prototype._submitSelectedUsersForm = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        if ($(e.target).parent().hasClass("disabled"))
            return false;

        if ($(Group.Binder.USER_SELECTED).length == 0) {
            alert("No members selected.");
            return true;
        }

        $(e.target).button("loading");
        $(e.target).parent().toggleClass("disabled");

        var userList = $("#selectedUsersForm .selected-users");
        var userCount = 0;

        userList.html("");
        $(Group.Binder.USER_SELECTED).each(function(key, element) {
            var newUser = userList.data("prototype");
            newUser = newUser.replace(/__name__/g, userCount);
            userList.append(newUser);
            $("." + userCount + "-id").val($(element).data("uid"));
            userCount++;
        });

        $("#selectedUsersForm form").submit();

        return true;
    };

    Group.prototype._onPageLoaded = function(e) {
        console.log("%s: %s", Group.TAG, "_onPageLoaded");

        switch (this.mediaChooser.page) {
            case MediaChooser.Page.RECORD_VIDEO:
                this.mediaChooser.createVideoRecorder();
                break;
            case MediaChooser.Page.PREVIEW:
                if (e.payload.media.type == MediaChooser.MEDIA_TYPE.VIDEO.id)
                    this.mediaChooser.createVideoPlayer();

                break;
        }
    };

    Group.prototype._onSuccess = function(e) {
        this.getFormField("mediatextarea").val(e.media.id);
    };

    Group.prototype._onReset = function(e) {
        this.getFormField("mediatextarea").val("");
    };

    return Group;
});
