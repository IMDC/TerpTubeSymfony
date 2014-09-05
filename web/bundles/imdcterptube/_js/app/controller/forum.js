define(['core/mediaChooser'], function(MediaChooser) {
    var Forum = function() {
        this.page = null;
        this.mediaChooser = null;
        this.forwardButton = "<button class='forwardButton'></button>";

        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);
        this.bind_forwardFunction = this.forwardFunction.bind(this);
    }

    Forum.TAG = "Forum";

    Forum.Page = {
        NEW: 0,
        EDIT: 1,
        DELETE: 2
    };

    /**
     * MediaChooser params for each related page that uses MediaChooser
     */
    Forum.mediaChooserOptions = function(page) {
        switch (page) {
            case Forum.Page.NEW:
            case Forum.Page.EDIT:
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
    Forum.bindUIEvents = function(page) {
        console.log("%s: %s- page=%d", Forum.TAG, "bindUIEvents", page);

        this.page = page;

        switch (this.page) {
            case Forum.Page.NEW:
            case Forum.Page.EDIT:
                this._bindUIEventsNewEdit();
                break;
            case Forum.Page.DELETE:
                this._bindUIEventsDelete();
                break;
        }
    };

    Forum._bindUIEventsNewEdit = function() {
        console.log("%s: %s", Forum.TAG, "_bindUIEventsNewEdit");

        this.mediaChooser = new MediaChooser(Forum.mediaChooserOptions(Forum.Page.NEW));
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.bindUIEvents();

        /*
         * by paul: since I hid the real 'submit' button to provide a nicely stylized button
         * I have to click the real button when you click on the fancy one
         */
        /*$("#forum-form-submit-button").on("click", function(e) {
            e.preventDefault();

            $("#ForumForm_submit").click();
        });*/
    };

    Forum._bindUIEventsDelete = function() {
        /*
         * by paul: since I hid the real 'submit' button to provide a nicely stylized button
         * I have to click the real button when you click on the fancy one
         */
        /*$("#forum-form-submit-button").on("click", function(e) {
            e.preventDefault();

            $("#ForumFormDelete_submit").click();
        });*/
    };

    Forum.prototype._onSuccess = function(e) {
        switch (this.page) {
            case Forum.Page.NEW:
            case Forum.Page.EDIT:
                $("#ForumForm_mediatextarea").val(e.media.id);
                //$("#ForumForm_mediaID").attr("data-mid", e.media.id); //TODO not used??
                break;
        }
    };

    Forum.prototype._onReset = function(e) {
        switch (this.page) {
            case Forum.Page.NEW:
            case Forum.Page.EDIT:
                $("#ForumForm_mediatextarea").val("");
                break;
        }
    };

    /**
     * @param {object} videoElement
     */
    Forum.prototype.createVideoRecorder = function(videoElement) {
        console.log("%s: %s", Forum.TAG, "createVideoRecorder");

        this.mediaChooser.createVideoRecorder({
            videoElement: videoElement,
            forwardButtons: [this.forwardButton],
            forwardFunctions: [this.bind_forwardFunction]
        });
    };

    Forum.prototype.forwardFunction = function() {
        console.log("%s: %s", Forum.TAG, "forwardFunction");

        this.mediaChooser.destroyRecorder();

        this.mediaChooser.previewMedia({
            type: MediaChooser.TYPE_RECORD_VIDEO,
            mediaUrl: Routing.generate('imdc_files_gateway_preview', { mediaId: this.mediaChooser.media.id }),
            mediaId: this.mediaChooser.media.id,
            recording: true
        });
    };

    return Forum;
});
