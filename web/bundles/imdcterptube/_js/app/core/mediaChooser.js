define([ 'core/mediaManager' ], function(MediaManager) {
    "use strict";

    var MediaChooser = function(options) {
        var defaults = {
            isFileSelection: true,
            enableDoneAndPost: false,
            media: null
        };

        if (typeof options == "undefined")
            options = defaults;

        this.isFileSelection = typeof options.isFileSelection != "undefined" ? options.isFileSelection
            : defaults.isFileSelection;
        this.enableDoneAndPost = typeof options.enableDoneAndPost != "undefined" ? options.enableDoneAndPost
            : defaults.enableDoneAndPost;
        this.media = typeof options.media != "undefined" ? options.media
            : defaults.media;

        this.forwardButton = "<button class='forwardButton'></button>";
        this.doneButton = "<button class='doneButton'></button>";
        this.doneAndPostButton = "<button class='doneAndPostButton'></button>";
        this.backButton = "<button class='backButton'></button>";

        this.container = null;
        //this.popupDialog = null;
        this.modalDialog = null;
        this.recorder = null;
        this.player = null;
        this.mediaManager = new MediaManager();
        this.wasRecording = false;

        this.bind__onLoadPageSuccess = this._onLoadPageSuccess.bind(this);
        this.bind__onRecordingSuccess = this._onRecordingSuccess.bind(this);
        this.bind__onRecordingError = this._onRecordingError.bind(this);
        this.bind__forwardFunction = this._forwardFunction.bind(this);
        this.bind__doneFunction = this._doneFunction.bind(this);
        this.bind__doneAndPostFunction = this._doneAndPostFunction.bind(this);
        this.bind__forwardFunctionCut = this._forwardFunctionCut.bind(this);
        this.bind__backFunction = this._backFunction.bind(this);
        this.bind__loadSelectFromMyFilesFunction = this.loadSelectFromMyFilesFunction.bind(this);
        this.bind__terminatingFunction = this._terminatingFunction.bind(this);

        this.bindBlocked = false;
        this.bindRequested = false;

        //TODO chooser form should be hidden (or replaced by a loader) until this completes
        if (this.media != null) {
            if (typeof this.media.title != "undefined") {
                this._invokeSuccess();
            } else {
                this.bindBlocked = true;

                $.ajax({
                    url: Routing.generate("imdc_myfiles_get_info", {mediaId: this.media.id}),
                    success: (function(data, textStatus, jqXHR) {
                        //console.log("%s: %s: %s", Post.TAG, "handlePage", "success");

                        this.setMedia(data.media);
                        this._invokeSuccess();
                        this.bindBlocked = false;
                        if (this.bindRequested) {
                            this.bindUIEvents();
                        }
                    }).bind(this),
                    error: (function(request) {
                        //console.log("%s: %s: %s", Post.TAG, "handlePage", "error");

                        console.log(request.statusText);

                        this.bindBlocked = false;
                        if (this.bindRequested) {
                            this.bindUIEvents();
                        }
                    }).bind(this)
                });
            }
        }
    };

    MediaChooser.TAG = "MediaChooser";

    MediaChooser.Page = {
        RECORD_VIDEO: "recordVideo",
        UPLOAD_AUDIO: "audio",
        UPLOAD_VIDEO: "video",
        UPLOAD_IMAGE: "image",
        UPLOAD_OTHER: "other",
        SELECT: "select",
        PREVIEW: "preview"
    };

    MediaChooser.Event = {
        PAGE_LOADED: "eventPageLoaded",
        SUCCESS: "eventSuccess",
        SUCCESS_AND_POST: "eventSuccessAndPost",
        ERROR: "eventError",
        RESET: "eventReset",
        DIALOG_CLOSE: "eventDialogClose"
    };

    MediaChooser.Binder = {
        CONTAINER_CHOOSE: ".mediachooser-container-choose",
        MODAL_DIALOG: ".mediachooser-modal",
        CONTAINER_RECORD_VIDEO: ".mediachooser-container-record-video",
        RECORD_VIDEO: ".mediachooser-record-video",
        CONTAINER_UPLOAD_VIDEO_RECORDING: ".mediachooser-container-upload-video-recording",
        UPLOAD_: ".mediachooser-upload-",
        SELECT: ".mediachooser-select",
        CONTAINER_UPLOAD: ".mediachooser-container-upload",
        UPLOAD_TITLE: ".mediachooser-upload-title",
        CONTAINER_SELECT: ".mediachooser-container-select",
        CONTAINER_SELECTED: ".mediachooser-container-selected",
        SELECTED: ".mediachooser-selected",
        REMOVE: ".mediachooser-remove",
        CONTAINER_DIALOG: ".mediachooser-container-dialog"
    };

    // this must be the same name defined in {bundle}/Form/Type/{type}MediaFormType
    MediaChooser.FORM_NAME = "MediaForm";

    // int constants from Entity\Media
    MediaChooser.MEDIA_TYPE = {
        AUDIO: {id: 0, str: "audio"},
        VIDEO: {id: 1, str: "video"},
        IMAGE: {id: 2, str: "image"},
        OTHER: {id: 9, str: "other"}
    };

    MediaChooser.DIALOG_TITLE_RECORD_VIDEO = "Record a new video";
    MediaChooser.DIALOG_TITLE_SELECT = "Select from My Files";
    MediaChooser.DIALOG_TITLE_PREVIEW = "Preview";

    MediaChooser.prototype._getElement = function(binder) {
        return this.container.find(binder);
    };

    MediaChooser.prototype.getContainer = function() {
        return this.container;
    };

    MediaChooser.prototype.getForm = function(mediaType) {
        return this.getContainer().find("form[name=" + MediaChooser.FORM_NAME + "_" + mediaType + "]");
    };

    MediaChooser.prototype.getFormField = function(mediaType, fieldName) {
        return this.getContainer().find("#" + MediaChooser.FORM_NAME + "_" + mediaType + "_" + fieldName);
    };

    MediaChooser.prototype.bindUIEvents = function() {
        console.log("%s: %s", MediaChooser.TAG, "bindUIEvents");

        if (this.bindBlocked) {
            this.bindRequested = true;
            return;
        }

        /*this.popupDialog = this._getElement(MediaChooser.Binder.CONTAINER_DIALOG).dialog({
            autoOpen: false
        });*/

        this.modalDialog = this._getElement(MediaChooser.Binder.MODAL_DIALOG).modal({show: false});
        this.modalDialog.on("hidden.bs.modal", this.bind__terminatingFunction);

        this._getElement(MediaChooser.Binder.RECORD_VIDEO).on("click", (function(e) {
            e.preventDefault();

            this.page = MediaChooser.Page.RECORD_VIDEO;
            this._loadPage({
                showPopup: true,
                url: Routing.generate("imdc_myfiles_add_recording"),
                method: "GET"
            });
        }).bind(this));

        $.each(MediaChooser.MEDIA_TYPE, (function(index, value) {
            var link = this._getElement(MediaChooser.Binder.UPLOAD_ + value.str);
            var resourceFile = this.getFormField(value.str, "resource_file");

            link.on("click", function(e) {
                e.preventDefault();

                resourceFile.click();
            });
        }).bind(this));

        $.each(MediaChooser.MEDIA_TYPE, (function(index, value) {
            var resourceFile = this.getFormField(value.str, "resource_file");
            var title = this.getFormField(value.str, "title");
            var form = this.getForm(value.str);

            resourceFile.on("change", (function(e) {
                if (resourceFile.val() == "")
                    return;

                title.val(MediaChooser._cleanFileNameNoExt(resourceFile.val()));

                this._getElement(MediaChooser.Binder.UPLOAD_TITLE).html(title.val());
                this._getElement(MediaChooser.Binder.CONTAINER_CHOOSE).hide();

                if (value.id == MediaChooser.MEDIA_TYPE.AUDIO.id)
                    this.page = MediaChooser.Page.UPLOAD_AUDIO;
                else if (value.id == MediaChooser.MEDIA_TYPE.VIDEO.id)
                    this.page = MediaChooser.Page.UPLOAD_VIDEO;
                else if (value.id == MediaChooser.MEDIA_TYPE.IMAGE.id)
                    this.page = MediaChooser.Page.UPLOAD_IMAGE;
                else if (value.id == MediaChooser.MEDIA_TYPE.OTHER.id)
                    this.page = MediaChooser.Page.UPLOAD_OTHER;

                this._loadPage({
                    showPopup: false,
                    url: Routing.generate("imdc_myfiles_add_" + value.str),
                    method: "POST",
                    data: new FormData(form[0]),
                    uploadProgress: true
                });

                resourceFile.val("");
                title.val("");
            }).bind(this));
        }).bind(this));

        this._getElement(MediaChooser.Binder.SELECT).on("click", (function(e) {
            e.preventDefault();
            this.bind__loadSelectFromMyFilesFunction();
            // this.page = MediaChooser.Page.SELECT;
            // this._loadPage({
            // showPopup: true,
            // url: Routing.generate("imdc_myfiles_list"),
            // method: "GET"
            // });
        }).bind(this));

        this._getElement(MediaChooser.Binder.REMOVE).on("click", (function(e) {
            e.preventDefault();

            this._invokeReset();
        }).bind(this));
    };

    MediaChooser.prototype._bindUIEventsSelectFromMyFiles = function() {
        console.log("%s: %s", MediaChooser.TAG, "_bindUIEventsSelectFromMyFiles");

        var container = $(MediaChooser.Binder.CONTAINER_SELECT);
        var instance = this;
        container.find(".preview-button").on("click", (function(e) {
            e.preventDefault();

            if ($(e.currentTarget).hasClass("disabled"))
                return false;

            this.setMedia({
                id: $(e.currentTarget).data("val")
            });
            this.previewMedia();
        }).bind(this));

        container.find("span.edit-title").editable({
            toggle: 'manual',
            unsavedclass: null,
            success: function(response, newValue) {
                instance.mediaManager.updateMedia({
                    id: $(this).data('val'),
                    title: newValue
                });
            }
        });

        container.find("a.edit-title").on("click", function(e) {
            e.stopPropagation();
            $(this).prev().editable('toggle');
        });

        container.find(".delete-button").on("click", (function(e) {
            e.preventDefault();
            var file = $(e.target);

            $(this.mediaManager).one(MediaManager.EVENT_DELETE_SUCCESS, function() {
                file.parent().parent().parent().remove();
            });
            $(this.mediaManager).one(MediaManager.EVENT_DELETE_ERROR, function(error, e) {
                if (e.status == 500) {
                    alert(e.statusText);
                } else {
                    alert('Error: ' + error);
                }
            });

            return this.mediaManager.deleteMedia(file.data("val"), $("#mediaDeleteConfirmMessage").html());
        }).bind(this));

        container.find(".select-button").on("click", (function(e) {
            e.preventDefault();

            this.setMedia({
                id: $(e.target).data("val"),
                title: $(e.target).data("title")
            });
            this._invokeSuccess();
            this._terminatingFunction();
        }).bind(this));

        // KnpPaginatorBundle:Pagination:twitter_bootstrap_v3_pagination.html.twig
        // override pagination urls
        container.find("ul.pagination li a").on("click", (function(e) {
            e.preventDefault();

            this._loadPage({
                showPopup: true,
                url: $(e.target).attr("href"),
                method: "GET"
            });
        }).bind(this));
    };

    MediaChooser.prototype._bindUIEventsPreview = function() {
        console.log("%s: %s", MediaChooser.TAG, "_bindUIEventsPreview");

        $('#mediaPreviewTitle').blur((function(e) {
            console.log('updated title');
            this.media.title = $('#mediaPreviewTitle').val();
            this.mediaManager.updateMedia(this.media);
        }).bind(this));
    };

    MediaChooser._cleanFileNameNoExt = function(fileName) {
        // FIXME extract proper file name
        fileName = fileName.split(/(\\|\/)/g).pop();
        return (fileName.substr(0, fileName.lastIndexOf('.')) || fileName).replace(/[^a-z0-9\s]/gi, '').replace(
            /[_\s]/g, '-');
    };

    MediaChooser.prototype._loadPage = function(options) {
        console.log("%s: %s", MediaChooser.TAG, "_loadPage");

        /*if (options.showPopup && !this.popupDialog.dialog("isOpen"))
            this._showPopupDialog();

        this.popupDialog.dialog("option", "title", this._getPopupDialogTitle());
        this.popupDialog.html("");*/

        this.modalDialog.modal(options.showPopup ? "show" : "hide");
        this.modalDialog.find(".modal-title").html(this._getPopupDialogTitle());
        this.modalDialog.find(".modal-body").html("");

        var request = {
            url: options.url,
            type: options.method,
            success: this.bind__onLoadPageSuccess,
            error: (function(jqXHR, textStatus, errorThrown) {
                console.log("%s: %s: %s", MediaChooser.TAG, "_loadPage", "error");

                this._invokeError(jqXHR);
            }).bind(this)
        };

        if (request.type != "GET") {
            request.processData = false;
            request.data = options.data;
            request.contentType = false;

            if (options.uploadProgress) {
                request.xhr = (function() {
                    var xhr = $.ajaxSettings.xhr();
                    xhr.upload.addEventListener("progress", (function(e) {
                        if (!e.lengthComputable)
                            return;

                        MediaChooser._updateUploadProgress(this._getElement(MediaChooser.Binder.CONTAINER_UPLOAD), Math
                            .floor((e.loaded / e.total) * 100));
                    }).bind(this), false);

                    return xhr;
                }).bind(this);
            }
        }

        $.ajax(request);
    };

    MediaChooser.prototype._getPopupDialogTitle = function() {
        console.log("%s: %s", MediaChooser.TAG, "_getPopupDialogTitle");

        switch (this.page) {
            case MediaChooser.Page.RECORD_VIDEO:
                return MediaChooser.DIALOG_TITLE_RECORD_VIDEO;
            case MediaChooser.Page.SELECT:
                return MediaChooser.DIALOG_TITLE_SELECT;
            default:
                return MediaChooser.DIALOG_TITLE_PREVIEW;
        }
    };

    /*MediaChooser.prototype._showPopupDialog = function(type) {
        console.log("%s: %s", MediaChooser.TAG, "_showPopupDialog");

        this.popupDialog.dialog({
            autoOpen: false,
            resizable: false,
            modal: true,
            draggable: false,
            closeOnEscape: true,
            dialogClass: "tt-popup-dialog",
            close: (function(event, ui) {
                console.log("%s: %s: %s", MediaChooser.TAG, "_showPopupDialog", "close");

                this._terminatingFunction();
            }).bind(this),
            show: "blind",
            hide: "blind",
            minWidth: 800,
            position: {
                at: "top",
                my: "top"
            }
        });

        this.popupDialog.dialog("open");
    };*/

    MediaChooser.prototype._onLoadPageSuccess = function(data, textStatus, jqXHR) {
        console.log("%s: %s- finished=%s", MediaChooser.TAG, "_onLoadPageSuccess", data.finished);

        if (data.finished) {
            this.setMedia(data.media);
            this._invokeSuccess();
            this._terminatingFunction();
        }
        else {
            //this.popupDialog.html(data.page);
            this.modalDialog.find(".modal-body").html(data.page);

            if (this.page == MediaChooser.Page.SELECT)
                this._bindUIEventsSelectFromMyFiles();

            if (this.page == MediaChooser.Page.PREVIEW) {
                this.setMedia(data.media);
                this._bindUIEventsPreview();
            }
        }

        $(this).trigger($.Event(MediaChooser.Event.PAGE_LOADED, {
            payload: data //FIXME some event listeners my still use 'data' instead of 'payload'. 'data' is reserved by jquery
        }));
    };

    /**
     * @param {object}
     *                options
     */
    MediaChooser.prototype.createVideoRecorder = function() {
        console.log("%s: %s", MediaChooser.TAG, "createVideoRecorder");

        var forwardButtons = [ this.forwardButton, this.doneButton ];
        var forwardFunctions = [ this.bind__forwardFunction, this.bind__doneFunction ];
        if (this.enableDoneAndPost) {
            forwardButtons.push(this.doneAndPostButton);
            forwardFunctions.push(this.bind__doneAndPostFunction);
        }

        this.recorder = new Player($(MediaChooser.Binder.CONTAINER_RECORD_VIDEO), {
            areaSelectionEnabled: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
            type: Player.DENSITY_BAR_TYPE_RECORDER,
            audioBar: false,
            volumeControl: false,
            recordingSuccessFunction: this.bind__onRecordingSuccess,
            recordingErrorFunction: this.bind__onRecordingError,
            recordingPostURL: Routing.generate('imdc_myfiles_add_recording'),
            forwardButtons: forwardButtons,
            forwardFunctions: forwardFunctions
        });

        $(this.recorder).on(
            Player.EVENT_RECORDING_UPLOAD_PROGRESS,
            function(e, percentComplete) {
                MediaChooser._updateUploadProgress($(MediaChooser.Binder.CONTAINER_UPLOAD_VIDEO_RECORDING),
                    percentComplete);
            });

        $(this.recorder).on(Player.EVENT_RECORDING_UPLOADED, function(data) {
            $(MediaChooser.Binder.CONTAINER_UPLOAD_VIDEO_RECORDING).hide();
        });

        this.recorder.createControls();
        $(this.recorder.elementID).find(
	".videoControlsContainer.controlsBar.videoControls.recordButton")
	.eq(0).attr("title", Translator.trans('player.recording.recordingButton'));
        
        $(this.recorder.elementID).find(
	".videoControlsContainer.controlsBar.doneButton")
	.eq(0).attr("title", Translator.trans('player.recording.doneButton'))
	
	$(this.recorder.elementID).find(
	".videoControlsContainer.controlsBar.doneAndPostButton")
	.eq(0).attr("title", Translator.trans('player.recording.doneAndPostButton'))
	
	$(this.recorder.elementID).find(
	".videoControlsContainer.controlsBar.forwardButton")
	.eq(0).attr("title", Translator.trans('player.recording.forwardButton'))
	
    };

    MediaChooser.prototype._onRecordingSuccess = function(data) {
        console.log("%s: %s- mediaId=%d", MediaChooser.TAG, "_onRecordingSuccess", data.media.id);

        this.setMedia(data.media);
    };

    MediaChooser.prototype._onRecordingError = function(e) {
        console.log("%s: %s- e=%s", MediaChooser.TAG, "_onRecordingError", e);
    };

    MediaChooser.prototype._forwardFunction = function() {
        console.log("%s: %s", MediaChooser.TAG, "_forwardFunction");

        this.destroyVideoRecorder();

        this.wasRecording = true;
        this.previewMedia();
    };

    MediaChooser.prototype._doneFunction = function() {
        console.log("%s: %s", MediaChooser.TAG, "_doneFunction");

        this.destroyVideoRecorder();
        if (this.page == MediaChooser.Page.PREVIEW) {
            this._forwardFunctionCut();
        }
        if (this.media)
            this._invokeSuccess();
        this._terminatingFunction();
    };

    MediaChooser.prototype._doneAndPostFunction = function() {
        console.log("%s: %s", MediaChooser.TAG, "_doneAndPostFunction");

        this.destroyVideoRecorder();
        if (this.page == MediaChooser.Page.PREVIEW) {
            this._forwardFunctionCut();
        }
        if (this.media)
            this._invokeSuccess(true);
        this._terminatingFunction();
    };

    MediaChooser.prototype.destroyVideoRecorder = function() {
        console.log("%s: %s", MediaChooser.TAG, "destroyVideoRecorder");

        if (this.recorder !== null)
            this.recorder.destroyRecorder();
    };

    MediaChooser._updateUploadProgress = function(element, percentComplete) {
        var progressBar = element.find(".progress-bar");

        element.show();

        progressBar.attr("aria-valuenow", percentComplete);
        progressBar.css("width", percentComplete + "%");
        progressBar.html(percentComplete + "%");
    };

    MediaChooser.prototype.previewMedia = function() {
        console.log("%s: %s", MediaChooser.TAG, "previewMedia");

        this.page = MediaChooser.Page.PREVIEW;
        this._loadPage({
            showPopup: true,
            url: Routing.generate('imdc_myfiles_preview', {
                mediaId: this.media.id
            }),
            method: "GET"
        });
    };

    MediaChooser.prototype.loadSelectFromMyFilesFunction = function() {
        this.page = MediaChooser.Page.SELECT;
        this._loadPage({
            showPopup: true,
            url: Routing.generate("imdc_myfiles_list"),
            method: "GET"
        });
    }

    MediaChooser.prototype.createVideoPlayer = function() {
        console.log("%s: %s", MediaChooser.TAG, "createVideoPlayer");

        var forwardButtons = [ this.doneButton ];
        var forwardFunctions = [ this.bind__doneFunction ];
        var backButtons;
        var backFunctions;

        if (this.enableDoneAndPost) {
            forwardButtons.push(this.doneAndPostButton);
            forwardFunctions.push(this.bind__doneAndPostFunction);
        }

        if (this.wasRecording) {
            backButtons = [ this.backButton ];
            backFunctions = [ this.bind__backFunction ];
        }
        else if (this.isFileSelection) {
            backButtons = [ this.backButton ];
            backFunctions = [ this.bind__loadSelectFromMyFilesFunction ]

        }
         

        this.player = new Player($("#" + this.media.id), {
            areaSelectionEnabled: true,
            audioBar: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_RELATIVE,
            forwardButtons: forwardButtons,
            forwardFunctions: forwardFunctions,
            backButtons: backButtons,
            backFunctions: backFunctions,
            selectedRegionColor: "#0000ff"
        });

        this.player.createControls();
        
        $(this.player.elementID).find(
	".videoControlsContainer.controlsBar.doneButton")
	.eq(0).attr("title", Translator.trans('player.previewing.doneButton'))
	
	$(this.player.elementID).find(
	".videoControlsContainer.controlsBar.doneAndPostButton")
	.eq(0).attr("title", Translator.trans('player.previewing.doneAndPostButton'))
	
	$(this.player.elementID).find(
	".videoControlsContainer.controlsBar.backButton")
	.eq(0).attr("title", Translator.trans('player.previewing.backToRecordingButton'))
    };

    MediaChooser.prototype._backFunction = function() {
        console.log("%s: %s", MediaChooser.TAG, "_backFunction");

        // delete the current media!
        this.mediaManager.deleteMedia(this.media.id);

        // Go back to recording
        this.setMedia(null);
        this.page = MediaChooser.Page.RECORD_VIDEO;
        this._loadPage({
            showPopup: true,
            url: Routing.generate("imdc_myfiles_add_recording"),
            method: "GET"
        });
    };

    MediaChooser.prototype._forwardFunctionCut = function() {
        console.log("%s: %s", MediaChooser.TAG, "_forwardFunctionCut");

        var previousMinMaxTimes = this.player.getCurrentMinMaxTime();
        var currentMinMaxTimes = this.player.getAreaSelectionTimes();
        this.player.setCurrentMinMaxTime(currentMinMaxTimes.minTime, currentMinMaxTimes.maxTime);

        console.log("Current Min/Max Times %s %s", currentMinMaxTimes.minTime, currentMinMaxTimes.maxTime);
        console.log("Cutting to Min/Max Times %s %s", currentMinMaxTimes.minTime - previousMinMaxTimes.minTime,
                currentMinMaxTimes.maxTime - previousMinMaxTimes.minTime);

        this.mediaManager.trimMedia(this.media.id, currentMinMaxTimes.minTime - previousMinMaxTimes.minTime,
                currentMinMaxTimes.maxTime - previousMinMaxTimes.minTime);
    };

    MediaChooser.prototype.reset = function() {
        this._invokeReset();
    };

    MediaChooser.prototype._terminatingFunction = function() {
        console.log("%s: %s", MediaChooser.TAG, "_terminatingFunction");

        //this.popupDialog.html("");
        this.modalDialog.find(".modal-body").html("");

        /*if (this.popupDialog.dialog("isOpen")) {
            this.popupDialog.off("dialogclose");
            this.popupDialog.dialog("close");
        }*/

        if (this.modalDialog.data("bs.modal").isShown) {
            this.modalDialog.off("hidden.bs.modal");
            this.modalDialog.modal("hide");
        }

        if (this.recorder)
            this.recorder.destroyRecorder();

        $(this).trigger($.Event(MediaChooser.Event.DIALOG_CLOSE, {
            media: this.media
        }));
    };

    MediaChooser.prototype._invokeSuccess = function(doPost) {
        console.log("%s: %s", MediaChooser.TAG, "_invokeSuccess");

        var event = {
            media: this.media
        };

        this._getElement(MediaChooser.Binder.UPLOAD_TITLE).html("");
        this._getElement(MediaChooser.Binder.CONTAINER_UPLOAD).hide();

        if (this.isFileSelection) {
            this._getElement(MediaChooser.Binder.CONTAINER_CHOOSE).hide();
            this._getElement(MediaChooser.Binder.SELECTED).html(this.media.title);
            this._getElement(MediaChooser.Binder.CONTAINER_SELECTED).show();
        }
        else {
            this._getElement(MediaChooser.Binder.CONTAINER_CHOOSE).show();
        }

        if (typeof doPost != "undefined" && doPost == true) {
            $(this).trigger($.Event(MediaChooser.Event.SUCCESS_AND_POST, event));
        }
        else {
            $(this).trigger($.Event(MediaChooser.Event.SUCCESS, event));
        }
    };

    MediaChooser.prototype._invokeError = function(jqXHR) {
        console.log("%s: %s- jqXHR=%o", MediaChooser.TAG, "_invokeError", jqXHR);

        this._getElement(MediaChooser.Binder.UPLOAD_TITLE).html("");
        this._getElement(MediaChooser.Binder.CONTAINER_UPLOAD).hide();
        this._getElement(MediaChooser.Binder.CONTAINER_CHOOSE).show();

        if (this.isFileSelection) {
            this._getElement(MediaChooser.Binder.CONTAINER_SELECTED).hide();
            this._getElement(MediaChooser.Binder.SELECTED).html("");
        }

        $(this).trigger($.Event(MediaChooser.Event.ERROR, {
            jqXHR: jqXHR
        }));
    };

    MediaChooser.prototype._invokeReset = function() {
        console.log("%s: %s", MediaChooser.TAG, "_invokeReset");

        this._getElement(MediaChooser.Binder.UPLOAD_TITLE).html("");
        this._getElement(MediaChooser.Binder.CONTAINER_UPLOAD).hide();
        this._getElement(MediaChooser.Binder.CONTAINER_CHOOSE).show();

        if (this.isFileSelection) {
            this._getElement(MediaChooser.Binder.CONTAINER_SELECTED).hide();
            this._getElement(MediaChooser.Binder.SELECTED).html("");
        }

        this.setMedia(null);

        $(this).trigger($.Event(MediaChooser.Event.RESET, {}));
    };

    MediaChooser.prototype.setContainer = function(container) {
        console.log("%s: %s", MediaChooser.TAG, "setContainer");

        this.container = typeof container != "undefined" ? container : this.container;
    };

    MediaChooser.prototype.setMedia = function(media) {
        console.log("%s: %s", MediaChooser.TAG, "setMedia");

        this.media = typeof media != "undefined" ? media : this.media;
    };

    return MediaChooser;
});
