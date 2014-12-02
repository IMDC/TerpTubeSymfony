define(['core/helper', 'core/mediaManager', 'core/myFilesSelector'], function(Helper, MediaManager, MyFilesSelector) {
    "use strict";

    var Recorder = function(options) {
        var defaults = {
            enableDoneAndPost: false
        };

        if (typeof options == "undefined") {
            options = defaults;
        } else {
            for (var o in defaults) {
                this[o] = typeof options[o] != "undefined" ? options[o] : defaults[o];
            }
        }

        this.container = options.container;
        this.page = options.page;

        this.inPreviewMode = false;
        this.player = null;
        this.recorder = null;
        this.sourceMedia = null;
        this.recordedMedia = null;
        this.mediaManager = new MediaManager();

        this.forwardButton = "<button class='forwardButton'></button>";
        this.doneButton = "<button class='doneButton'></button>";
        this.doneAndPostButton = "<button class='doneAndPostButton'></button>";
        this.backButton = "<button class='backButton'></button>";

        this.bind__onRender = this._onRender.bind(this);
        this.bind__onShownModal = this._onShownModal.bind(this);
        this.bind__onHiddenModal = this._onHiddenModal.bind(this);
        this.bind__onShowTab = this._onShowTab.bind(this);
        this.bind__onShownTab = this._onShownTab.bind(this);
        this.bind__onBlurPlayerTitle = this._onBlurPlayerTitle.bind(this);
        this.bind__onClickInterpSelect = this._onClickInterpSelect.bind(this);
        this.bind__onPageAnimate = this._onPageAnimate.bind(this);
        this.bind__onRecordingStarted = this._onRecordingStarted.bind(this);
        this.bind__onRecordingStopped = this._onRecordingStopped.bind(this);
        this.bind__onRecordingUploadProgress = this._onRecordingUploadProgress.bind(this);
        this.bind__onRecordingUploaded = this._onRecordingUploaded.bind(this);
        this.bind__onRecordingSuccess = this._onRecordingSuccess.bind(this);
        this.bind__onRecordingError = this._onRecordingError.bind(this);
        this.bind__preview = this._preview.bind(this);
        this.bind__cut = this._cut.bind(this);
        this.bind__back = this._back.bind(this);
        this.bind__done = this._done.bind(this);
        this.bind__doneAndPost = this._doneAndPost.bind(this);

        //dust.compileFn($("#recorder").html(), "recorder");
    };

    Recorder.TAG = "Recorder";

    Recorder.MAX_RECORDING_TIME = 720; // 12 Minutes

    Recorder.Page = {
        NORMAL: "normal",
        INTERPRETATION: "interpretation"
    };

    Recorder.Event = {
        READY: "eventReady",
        DONE: "eventDone"
    };

    Recorder.Binder = {
        MODAL_DIALOG: ".recorder-modal",
        NORMAL: ".recorder-normal",
        INTERP: ".recorder-interp",

        CONTAINER_RECORD: ".recorder-container-record",
        NORMAL_TITLE: ".recorder-normal-title",
        NORMAL_VIDEO: ".recorder-normal-video",
        INTERP_SELECT: ".recorder-interp-select",
        INTERP_MY_FILES_SELECTOR: ".recorder-interp-my-files-selector",
        INTERP_VIDEO_P: ".recorder-interp-video-p",
        INTERP_TITLE: ".recorder-interp-title",
        INTERP_VIDEO_R: ".recorder-interp-video-r",
        CONTROLS: ".recorder-controls",

        CONTAINER_UPLOAD: ".recorder-container-upload"
    };

    Recorder.prototype.getContainer = function() {
        return this.container;
    };

    Recorder.prototype._getElement = function(binder) {
        return this.getContainer().find(binder);
    };

    Recorder.prototype._createPlayer = function() {
        console.log("%s: %s", Recorder.TAG, "_createPlayer");

        //inPreviewMode = typeof inPreviewMode != "undefined" ? inPreviewMode : false;

        var forwardButtons = [];
        var forwardFunctions = [];
        if (this.page == Recorder.Page.NORMAL || this.inPreviewMode) {
            forwardButtons.push(this.doneButton);
            forwardFunctions.push(this.inPreviewMode ? this.bind__done : this.bind__cut);
        }
        if (this.enableDoneAndPost && this.inPreviewMode) {
            forwardButtons.push(this.doneAndPostButton);
            forwardFunctions.push(this.bind__doneAndPost);
        }

        var backButtons;
        var backFunctions;
        if (this.inPreviewMode) {
            backButtons = [this.backButton];
            backFunctions = [this.bind__back];
        }

        var container = this._getElement(this.page == Recorder.Page.NORMAL
            ? Recorder.Binder.NORMAL_VIDEO
            : this.inPreviewMode ? Recorder.Binder.INTERP_VIDEO_R : Recorder.Binder.INTERP_VIDEO_P);

        var player = new Player(container, {
            areaSelectionEnabled: this.inPreviewMode,
            audioBar: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_RELATIVE,
            controlBarElement: this._getElement(Recorder.Binder.CONTROLS),
            forwardButtons: forwardButtons,
            forwardFunctions: forwardFunctions,
            backButtons: backButtons,
            backFunctions: backFunctions,
            selectedRegionColor: "#0000ff"
        });
        player.createControls();

        $(player.elementID)
            .find(".videoControlsContainer.controlsBar.doneButton").eq(0)
            .attr("title", Translator.trans('player.previewing.doneButton'));

        $(player.elementID)
            .find(".videoControlsContainer.controlsBar.doneAndPostButton").eq(0)
            .attr("title", Translator.trans('player.previewing.doneAndPostButton'));

        $(player.elementID)
            .find(".videoControlsContainer.controlsBar.backButton").eq(0)
            .attr("title", Translator.trans('player.previewing.backToRecordingButton'));

        if (this.inPreviewMode) {
            this.recorder = player;
        } else {
            this.player = player;
        }
    };

    Recorder.prototype._cut = function(e) {
        console.log("%s: %s", Recorder.TAG, "_cut");
        e.preventDefault();

        var previousMinMaxTimes = this.player.getCurrentMinMaxTime();
        var currentMinMaxTimes = this.player.getAreaSelectionTimes();
        this.player.setCurrentMinMaxTime(currentMinMaxTimes.minTime, currentMinMaxTimes.maxTime);

        console.log("Current Min/Max Times %s %s", currentMinMaxTimes.minTime, currentMinMaxTimes.maxTime);
        console.log("Cutting to Min/Max Times %s %s", currentMinMaxTimes.minTime - previousMinMaxTimes.minTime,
                currentMinMaxTimes.maxTime - previousMinMaxTimes.minTime);

        this.mediaManager.trimMedia(this.activeMedia.id, currentMinMaxTimes.minTime - previousMinMaxTimes.minTime,
                currentMinMaxTimes.maxTime - previousMinMaxTimes.minTime);
    };

    Recorder.prototype._back = function(e) {
        console.log("%s: %s", Recorder.TAG, "_back");
        e.preventDefault();

        this._destroyPlayers();

        // delete the current media!
        this.mediaManager.deleteMedia(this.recordedMedia.id);

        // Go back to recording
        this.setRecordedMedia(null);
        this.inPreviewMode = false;
        if (this.page == Recorder.Page.INTERPRETATION)
            this._createPlayer();
        this._createRecorder();
    };

    Recorder.prototype._createRecorder = function() {
        console.log("%s: %s", Recorder.TAG, "_createRecorder");

        var forwardButtons = [this.forwardButton, this.doneButton];
        var forwardFunctions = [this.bind__preview, this.bind__done];
        if (this.enableDoneAndPost) {
            forwardButtons.push(this.doneAndPostButton);
            forwardFunctions.push(this.bind__doneAndPost);
        }

        var container;
        //var postUrl;
        if (this.page == Recorder.Page.NORMAL) {
            container = this._getElement(Recorder.Binder.NORMAL_VIDEO);
            //postUrl = Routing.generate("imdc_myfiles_add_recording");
        } else {
            container = this._getElement(Recorder.Binder.INTERP_VIDEO_R);
            //postUrl = Routing.generate("imdc_myfiles_add_simultaneous_recording");
        }

        this.recorder = new Player(container, {
            areaSelectionEnabled: false,
            audioBar: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
            controlBarElement: this._getElement(Recorder.Binder.CONTROLS),
            type: Player.DENSITY_BAR_TYPE_RECORDER,
            volumeControl: false,
            maxRecordingTime : Recorder.MAX_RECORDING_TIME,
            recordingSuccessFunction: this.bind__onRecordingSuccess,
            recordingErrorFunction: this.bind__onRecordingError,
            recordingPostURL: Routing.generate("imdc_myfiles_add_recording"), //postUrl,
            forwardButtons: forwardButtons,
            forwardFunctions: forwardFunctions
        });
        if (this.page == Recorder.Page.INTERPRETATION) {
            $(this.recorder).on(Player.EVENT_RECORDING_STARTED, this.bind__onRecordingStarted);
            $(this.recorder).on(Player.EVENT_RECORDING_STOPPED, this.bind__onRecordingStopped);
        }
        $(this.recorder).on(Player.EVENT_RECORDING_UPLOAD_PROGRESS, this.bind__onRecordingUploadProgress);
        $(this.recorder).on(Player.EVENT_RECORDING_UPLOADED, this.bind__onRecordingUploaded);
        this.recorder.createControls();

        $(this.recorder.elementID)
            .find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0)
            .attr("title", Translator.trans('player.recording.recordingButton'));

        $(this.recorder.elementID)
            .find(".videoControlsContainer.controlsBar.forwardButton").eq(0)
            .attr("title", Translator.trans('player.recording.forwardButton'));

        $(this.recorder.elementID)
            .find(".videoControlsContainer.controlsBar.doneButton").eq(0)
            .attr("title", Translator.trans('player.recording.doneButton'));

        $(this.recorder.elementID)
            .find(".videoControlsContainer.controlsBar.doneAndPostButton").eq(0)
            .attr("title", Translator.trans('player.recording.doneAndPostButton'));
    };

    Recorder.prototype._onRecordingStarted = function() {
        console.log("%s: %s", Recorder.TAG, "_onRecordingStarted");

        this.player.setControlsEnabled(false);
        /*this.recorder.options.additionalDataToPost = {
            sourceMediaID: this.sourceMedia.id,
            startTime: this.player.getCurrentTime()
        };*/
        this.player.play();
    };

    Recorder.prototype._onRecordingStopped = function() {
        this.player.pause();
        this.player.setControlsEnabled(true);
    };

    Recorder.prototype._onRecordingUploadProgress = function(e, percentComplete) {
        Helper.updateProgressBar(this._getElement(Recorder.Binder.CONTAINER_UPLOAD).show(), percentComplete);
    };

    Recorder.prototype._onRecordingUploaded = function(data) {
        Helper.updateProgressBar(this._getElement(Recorder.Binder.CONTAINER_UPLOAD).hide(), 0);
    };

    Recorder.prototype._onRecordingSuccess = function(data) {
        console.log("%s: %s- mediaId=%d", Recorder.TAG, "_onRecordingSuccess", data.media.id);

        //this.tempMedia = data.media;
        this.setRecordedMedia(data.media);
    };

    Recorder.prototype._onRecordingError = function(e) {
        console.log("%s: %s- e=%s", Recorder.TAG, "_onRecordingError", e);
    };

    Recorder.prototype._preview = function(e) {
        console.log("%s: %s", Recorder.TAG, "_preview");
        e.preventDefault();

        this._destroyPlayers();

        //this.setRecordedMedia(this.tempMedia);
        if (this.page == Recorder.Page.INTERPRETATION)
            this._createPlayer();
        this.inPreviewMode = true;
        this._createPlayer();
    };

    Recorder.prototype._done = function(e) {
        console.log("%s: %s", Recorder.TAG, "_done");
        e.preventDefault();

        $(this).trigger($.Event(Recorder.Event.DONE, {media: this.recordedMedia, doPost: false}));
    };

    Recorder.prototype._doneAndPost = function(e) {
        console.log("%s: %s", Recorder.TAG, "_doneAndPost");
        e.preventDefault();

        $(this).trigger($.Event(Recorder.Event.DONE, {media: this.recordedMedia, doPost: true}));
    };

    Recorder.prototype._destroyPlayers = function() {
        console.log("%s: %s", Recorder.TAG, "_destroyPlayers");

        var reset = (function(binder) {
            var normal = this._getElement(binder);
            var old = normal.parent();
            normal.removeAttr("src");
            old.hide();
            old.after(normal.detach());
            old.remove();
        }).bind(this);

        if (this.player != null) {
            this.player.destroyRecorder();
            if (this.page == Recorder.Page.NORMAL) {
                reset(Recorder.Binder.NORMAL_VIDEO);
            } else {
                if (this.sourceMedia != null)
                    reset(Recorder.Binder.INTERP_VIDEO_P);
            }
            this.player = null;
        }

        if (this.recorder != null) {
            this.recorder.destroyRecorder();
            if (this.page == Recorder.Page.NORMAL) {
                reset(Recorder.Binder.NORMAL_VIDEO);
            } else {
                if (this.sourceMedia != null)
                    reset(Recorder.Binder.INTERP_VIDEO_R);
            }
            this.recorder = null;
        }

        this._getElement(Recorder.Binder.CONTROLS).html("");
    };

    Recorder.prototype._onPageAnimate = function() {
        try {
            if (this.page == Recorder.Page.INTERPRETATION) {
                if (this.sourceMedia != null) {
                    this._createPlayer();
                } else {
                    return; // don't create the recorder
                }
            }

            this._createRecorder();
        } catch (err) {
            console.error("%s: %s- err=%o", Recorder.TAG, "_loadPage", err);
        }
    };

    Recorder.prototype._loadPage = function() {
        if (this.page == Recorder.Page.INTERPRETATION) {
            if (this.sourceMedia != null) {
                this._getElement(Recorder.Binder.INTERP_SELECT).parent().hide();
                this._getElement(Recorder.Binder.INTERP_VIDEO_P).show();
                this._getElement(Recorder.Binder.INTERP_VIDEO_R).show();
            } else {
                this._getElement(Recorder.Binder.INTERP_SELECT).parent().show();
                this._getElement(Recorder.Binder.INTERP_VIDEO_P).hide();
                this._getElement(Recorder.Binder.INTERP_VIDEO_R).hide();
            }
        }

        this._getElement(Recorder.Binder.MODAL_DIALOG).find(".modal-dialog").animate({
            width: this.page == Recorder.Page.NORMAL ? "900px" : (this.sourceMedia != null ? "90%" : "900px") //FIXME use css classes
        }, {
            complete: this.bind__onPageAnimate
        });
    };

    Recorder.prototype._onShownModal = function(e) {
        this._destroyPlayers();
        this._loadPage();
    };

    Recorder.prototype._onHiddenModal = function(e) {
        this._destroyPlayers();
    };

    Recorder.prototype._onShowTab = function(e) {
        this._destroyPlayers();

        this.page = $(e.target).attr("href") == Recorder.Binder.NORMAL
            ? Recorder.Page.NORMAL
            : Recorder.Page.INTERPRETATION;

        this._loadPage();
    };

    Recorder.prototype._onShownTab = function(e) {
        this._getElement(Recorder.Binder.INTERP_VIDEO_P)[0].load();
    };

    Recorder.prototype._onBlurPlayerTitle = function(e) {
        console.log('updated title');
        this.recordedMedia.title = $(e.target).val();
        this.mediaManager.updateMedia(this.recordedMedia);
    };

    Recorder.prototype._onClickInterpSelect = function(e) {
        e.preventDefault();

        this.myFilesSelector = new MyFilesSelector({
            container: this._getElement(Recorder.Binder.INTERP_MY_FILES_SELECTOR),
            multiSelect: false
        });
        $(this.myFilesSelector).on(MyFilesSelector.Event.READY, (function(e) {
            this.hide();
            this.myFilesSelector.show();
        }).bind(this));
        $(this.myFilesSelector).on(MyFilesSelector.Event.DONE, (function(e) {
            this.myFilesSelector.hide();
            this.show();
            this.setSourceMedia(e.media[0]);
        }).bind(this));
        $(this.myFilesSelector).on(MyFilesSelector.Event.HIDDEN, (function(e) {
            this.show();
        }).bind(this));
        this.myFilesSelector.render();
    };

    Recorder.prototype._bindUIEvents = function() {
        var modal = this._getElement(Recorder.Binder.MODAL_DIALOG);
        modal.modal({backdrop: "static", show: false});
        modal.on("shown.bs.modal", this.bind__onShownModal);
        modal.on("hidden.bs.modal", this.bind__onHiddenModal);

        var tabs = modal.find("a[data-toggle='tab']");
        tabs.on("show.bs.tab", this.bind__onShowTab);
        tabs.on("shown.bs.tab", this.bind__onShownTab);

        this._getElement(Recorder.Binder.NORMAL_TITLE).blur(this.bind__onBlurPlayerTitle);
        this._getElement(Recorder.Binder.INTERP_TITLE).blur(this.bind__onBlurPlayerTitle);

        this._getElement(Recorder.Binder.INTERP_SELECT).on("click", this.bind__onClickInterpSelect);
    };

    Recorder.prototype._onRender = function(err, out) {
        this.container.html(out);

        var tab = this.page == Recorder.Page.NORMAL ? Recorder.Binder.NORMAL : Recorder.Binder.INTERP;
        var modal = this._getElement(Recorder.Binder.MODAL_DIALOG);
        modal.find("a[href!='" + tab + "']").parent().removeClass("active");
        modal.find("a[href='" + tab + "']").parent().addClass("active");
        modal.find(".tab-pane:not('" + tab + "')").removeClass("active");
        modal.find(".tab-pane" + tab).addClass("active");

        this._bindUIEvents();

        $(this).trigger($.Event(Recorder.Event.READY, {}));
    };

    Recorder.prototype.render = function() {
        dust.render("recorder", {}, this.bind__onRender);
    };

    Recorder.prototype.show = function() {
        this._getElement(Recorder.Binder.MODAL_DIALOG).modal("show");
    };

    Recorder.prototype.hide = function() {
        this._getElement(Recorder.Binder.MODAL_DIALOG).modal("hide");
    };

    Recorder.prototype._injectMedia = function(binder, media) {
        var video = this._getElement(binder);
        var source = video.find("source");
        video.removeAttr("src");
        source.attr("src", Helper.generateUrl(media.resource.pathMPEG));
    };

    Recorder.prototype.setSourceMedia = function(media) {
        this.sourceMedia = media;

        if (this.sourceMedia != null) {
            this._injectMedia(Recorder.Binder.INTERP_VIDEO_P, this.sourceMedia);
        }
        this._destroyPlayers();
        this._loadPage();
    };

    Recorder.prototype._togglePlayerTitle = function() {
        var nTitle = this._getElement(Recorder.Binder.NORMAL_TITLE);
        var iTitle = this._getElement(Recorder.Binder.INTERP_TITLE);
        var title = this.recordedMedia != null ? this.recordedMedia.title : "";

        if (this.page == Recorder.Page.NORMAL) {
            iTitle.hide().val("");
            nTitle.toggle().val(title);
        } else {
            nTitle.hide().val("");
            iTitle.toggle().val(title);
        }
    };

    Recorder.prototype.setRecordedMedia = function(media) {
        this.recordedMedia = media;

        if (this.recordedMedia != null) {
            this._injectMedia(this.page == Recorder.Page.NORMAL
                    ? Recorder.Binder.NORMAL_VIDEO
                    : Recorder.Binder.INTERP_VIDEO_R,
                this.recordedMedia);
        }
        this._togglePlayerTitle();
    };

    return Recorder;
});
