define(['core/mediaChooser'], function(MediaChooser) {
    var Group = function() {
        this.page = null;
        this.mediaChooser = null;
        this.forwardButton = "<button class='forwardButton'></button>";

        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);
        this.bind_forwardFunction = this.forwardFunction.bind(this);
    };

    Group.TAG = "Group";

    Group.Page = {
        NEW: 0,
        EDIT: 1,
        ADD_MEMBERS: 2
    };

    /**
     * MediaChooser params for each related page that uses MediaChooser
     */
    Group.mediaChooserOptions = function(page) {
        switch (page) {
            case Group.Page.NEW:
            case Group.Page.EDIT:
                return {
                    element: $("#files"),
                    isPopUp: true
                };
        }
    };

    /**
     * ui element event bindings in order of appearance
     * @param {number} page
     */
    Group.prototype.bindUIEvents = function(page) {
        console.log("%s: %s- page=%d", Group.TAG, "bindUIEvents", page);

        this.page = page;

        switch (this.page) {
            case Group.Page.NEW:
            case Group.Page.EDIT:
                this._bindUIEventsNewEdit();
                break;
            case Group.Page.ADD_MEMBERS:
                this._bindUIEventsAddMembers();
                break;
        }
    };

    Group.prototype._bindUIEventsNewEdit = function() {
        console.log("%s: %s", Group.TAG, "_bindUIEventsNewEdit");

        this.mediaChooser = new MediaChooser(Group.mediaChooserOptions(Group.Page.NEW));
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.bindUIEvents();
    };

    Group.prototype._bindUIEventsAddMembers = function() {
        console.log("%s: %s", Group.TAG, "_bindUIEventsAddMembers");

        $(".group-member").on("click", function(e) {
            var elem = $(e.target);
            var isSelected = elem.html() == elem.data("selected");

            elem.html(isSelected
                    ? elem.data("select")
                    : elem.data("selected")
            );

            elem.removeClass(isSelected ? "btn-success" : "btn-default");
            elem.addClass(isSelected ? "btn-default" : "btn-success");
        });

        $(".group-member").each(function(key, element) {
            $(element).html($(element).data("select"));
        });

        $("#addSelected").on("click", function(e) {
            e.preventDefault();

            $(e.target).button("loading");

            var userList = $("#addMembersForm .selected-users");
            var userCount = 0;

            userList.html("");
            $(".group-member").each(function(key, element) {
                if ($(element).html() != $(element).data("selected")) {
                    return;
                }

                var newUser = userList.data("prototype");
                newUser = newUser.replace(/__name__/g, userCount);
                userList.append(newUser);
                $("." + userCount + "-id").val($(element).data("uid"));
                userCount++;
            });

            //$("#addMembersForm").submit();
        });
    };

    Group.prototype._onSuccess = function(e) {
        switch (this.page) {
            case Group.Page.NEW:
            case Group.Page.EDIT:
                $("#UserGroupForm_userGroupForum_mediatextarea").val(e.media.id);
                break;
        }
    };

    Group.prototype._onReset = function(e) {
        switch (this.page) {
            case Group.Page.NEW:
            case Group.Page.EDIT:
                $("#UserGroupForm_userGroupForum_mediatextarea").val("");
                break;
        }
    };

    /**
     * @param {object} videoElement
     */
    Group.prototype.createVideoRecorder = function(videoElement) {
        console.log("%s: %s", Group.TAG, "createVideoRecorder");

        this.mediaChooser.createVideoRecorder({
            videoElement: videoElement,
            forwardButtons: [this.forwardButton],
            forwardFunctions: [this.bind_forwardFunction]
        });
    };

    Group.prototype.forwardFunction = function() {
        console.log("%s: %s", Group.TAG, "forwardFunction");

        this.mediaChooser.destroyVideoRecorder();

        this.mediaChooser.previewMedia({
            type: MediaChooser.TYPE_RECORD_VIDEO,
            mediaUrl: Routing.generate('imdc_myfiles_preview', { mediaId: this.mediaChooser.media.id }),
            mediaId: this.mediaChooser.media.id,
            recording: true
        });
    };

    return Group;
});
