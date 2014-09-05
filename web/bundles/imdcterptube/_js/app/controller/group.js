define(['core/mediaChooser'], function(MediaChooser) {
    var Group = function() {
        this.page = null;
        this.mediaChooser = null;
        this.forwardButton = "<button class='forwardButton'></button>";

        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);
        this.bind_forwardFunction = this.forwardFunction.bind(this);
    }

    Group.TAG = "Group";

    Group.Page = {
        NEW: 0,
        EDIT: 1
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
        }
    };

    Group.prototype._bindUIEventsNewEdit = function() {
        console.log("%s: %s", Group.TAG, "_bindUIEventsNewEdit");

        this.mediaChooser = new MediaChooser(Group.mediaChooserOptions(Group.Page.NEW));
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.bindUIEvents();
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

        this.mediaChooser.destroyRecorder();

        this.mediaChooser.previewMedia({
            type: MediaChooser.TYPE_RECORD_VIDEO,
            mediaUrl: Routing.generate('imdc_files_gateway_preview', { mediaId: this.mediaChooser.media.id }),
            mediaId: this.mediaChooser.media.id,
            recording: true
        });
    };

    return Group;
});
