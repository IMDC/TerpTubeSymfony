define(['core/mediaChooser'], function(MediaChooser) {
    var Group = function() {
        this.recorder = null;
        this.forwardButton = "<button class='forwardButton'></button>";
        this.media = null;

        //TODO move to media chooser, as this may be a more general function
        this.bind_onRecordingSuccess = this.onRecordingSuccess.bind(this);
        this.bind_onRecordingError = this.onRecordingError.bind(this);
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
                    isPopUp: true,
                    callbacks: {
                        success: function(media) {
                            $("#UserGroupForm_userGroupForum_mediatextarea").val(media.id);
                        },
                        reset: function() {
                            $("#UserGroupForm_userGroupForum_mediatextarea").val("");
                        }
                    }
                };
        }
    };

    /**
     * ui element event bindings in order of appearance
     * @param {number} page
     */
    Group.bindUIEvents = function(page) {
        console.log("%s: %s- page=%d", Group.TAG, "bindUIEvents", page);

        switch (page) {
            case Group.Page.NEW:
            case Group.Page.EDIT:
                Group._bindUIEventsNewEdit();
                break;
        }
    };

    Group._bindUIEventsNewEdit = function() {
        console.log("%s: %s", Group.TAG, "_bindUIEventsNewEdit");

        MediaChooser.bindUIEvents(Group.mediaChooserOptions(Group.Page.NEW));
    };

    /**
     * @param {object} videoElement
     */
        //TODO move to media chooser, as this may be a more general function
    Group.prototype.createVideoRecorder = function(videoElement) {
        console.log("%s: %s", Group.TAG, "createVideoRecorder");

        this.recorder = new Player(videoElement, {
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
        this.recorder.createControls();

        //TODO revise
        videoElement.parents(".ui-dialog").on("dialogbeforeclose", (function(event, ui) {
            console.log("videoElement dialogbeforeclose");
            if (this.recorder != null) {
                this.recorder.destroyRecorder();
            }
        }).bind(this));
    }

    //TODO move to media chooser, as this may be a more general function
    Group.prototype.onRecordingSuccess = function(data) {
        console.log("%s: %s- mediaId=%d", Group.TAG, "onRecordingSuccess", data.media.id);

        this.media = data.media;
        //mediaChooser.setMedia(this.media);
    };

    //TODO move to media chooser, as this may be a more general function
    Group.prototype.onRecordingError = function(e) {
        console.log("%s: %s- e=%s", Group.TAG, "onRecordingError", e);
    };

    //TODO move to media chooser, as this may be a more general function
    Group.prototype.forwardFunction = function() {
        console.log("%s: %s", Group.TAG, "forwardFunction");

        this.recorder.destroyRecorder();

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

    return Group;
});
