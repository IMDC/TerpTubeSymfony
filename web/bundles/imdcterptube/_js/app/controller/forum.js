define(['core/mediaChooser'], function(MediaChooser) {
    var Forum = function() {
        this.player = null;
        this.forwardButton = "<button class='forwardButton'></button>";
        this.media = null;

        //TODO move to media chooser, as this may be a more general function
        this.bind_onRecordingSuccess = this.onRecordingSuccess.bind(this);
        this.bind_onRecordingError = this.onRecordingError.bind(this);
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
                    isPopUp: true,
                    callbacks: {
                        success: function(media) {
                            $("#ForumForm_mediatextarea").val(media.id);
                            //$("#ForumForm_mediaID").attr("data-mid", media.id); //TODO not used??
                        },
                        reset: function() {
                            $("#ForumForm_mediatextarea").val("");
                        }
                    }
                };
        }
    };

    /**
     * ui element event bindings in order of appearance
     * @param {number} page
     */
    Forum.bindUIEvents = function(page) {
        console.log("%s: %s- page=%d", Forum.TAG, "bindUIEvents", page);

        switch (page) {
            case Forum.Page.NEW:
            case Forum.Page.EDIT:
                Forum._bindUIEventsNewEdit();
                break;
            case Forum.Page.DELETE:
                Forum._bindUIEventsDelete();
                break;
        }
    };

    Forum._bindUIEventsNewEdit = function() {
        console.log("%s: %s", Forum.TAG, "_bindUIEventsNewEdit");

        MediaChooser.bindUIEvents(Forum.mediaChooserOptions(Forum.Page.NEW));

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

    /**
     * @param {object} videoElement
     */
        //TODO move to media chooser, as this may be a more general function
    Forum.prototype.createVideoRecorder = function(videoElement) {
        console.log("%s: %s", Forum.TAG, "createVideoRecorder");

        this.player = new Player(videoElement, {
            areaSelectionEnabled: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
            type: Player.DENSITY_BAR_TYPE_RECORDER,
            audioBar: false,
            volumeControl: false,
            recordingSuccessFunction: this.bind_onRecordingSuccess,
            recordingErrorFunction: this.bind_onRecordingError,
            recordingPostURL: Routing.generate('imdc_files_gateway_record'),
            forwardButtons: [this.forwardButton],
            forwardFunctions: [this.bind_forwardFunction]
        });
        this.player.createControls();

        //TODO revise
        videoElement.parents(".ui-dialog").on("dialogbeforeclose", (function(event, ui) {
            console.log("videoElement dialogbeforeclose");
            if (this.player != null) {
                this.player.destroyRecorder();
            }
        }).bind(this));
    }

    //TODO move to media chooser, as this may be a more general function
    Forum.prototype.onRecordingSuccess = function(data) {
        console.log("%s: %s- mediaId=%d", Forum.TAG, "onRecordingSuccess", data.media.id);

        this.media = data.media;
        //mediaChooser.setMedia(this.media);
    };

    //TODO move to media chooser, as this may be a more general function
    Forum.prototype.onRecordingError = function(e) {
        console.log("%s: %s- e=%s", Forum.TAG, "onRecordingError", e);
    };

    //TODO move to media chooser, as this may be a more general function
    Forum.prototype.forwardFunction = function() {
        console.log("%s: %s", Forum.TAG, "forwardFunction");

        this.player.destroyRecorder();

        /*mediaChooser.loadNextPage({
         url: Routing.generate('imdc_files_gateway_preview', {mediaId: this.media.id}),
         method: "POST",
         data: { mediaId: this.media.id }
         });*/

        mediaChooser.previewMedia({
            type: MediaChooser.TYPE_RECORD_VIDEO,
            mediaUrl: Routing.generate('imdc_files_gateway_preview', { mediaId: this.media.id }),
            mediaId: this.media.id,
            recording: true
        });
    };

    return Forum;
});
