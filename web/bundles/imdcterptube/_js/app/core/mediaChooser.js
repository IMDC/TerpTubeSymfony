define([
    'core/mediaManager',
    'core/recorder',
    'core/helper',
    'core/myFilesSelector'
], function(MediaManager, Recorder, Helper, MyFilesSelector) {
    "use strict";

    var MediaChooser = function(options) {
        var defaults = {
            isFileSelection: true,
            enableDoneAndPost: false
        };

        options = options || defaults;
        for (var o in defaults) {
            this[o] = typeof options[o] != "undefined" ? options[o] : defaults[o];
        }

//        this.isFileSelection = options.isFileSelection;
//        this.enableDoneAndPost = options.enableDoneAndPost;

        /*if (typeof options == "undefined")
            options = defaults;

        this.isFileSelection = typeof options.isFileSelection != "undefined" ? options.isFileSelection
            : defaults.isFileSelection;
        this.enableDoneAndPost = typeof options.enableDoneAndPost != "undefined" ? options.enableDoneAndPost
            : defaults.enableDoneAndPost;*/

//        this.doneButton = "<button class='doneButton'></button>";
//        this.doneAndPostButton = "<button class='doneAndPostButton'></button>";
//        this.backButton = "<button class='backButton'></button>";

        this.container = null;
//        this.activeMedia = null;
        this.media = [];
//        this.modalDialog = null;
        this.recorder = null;
        this.player = null;
        this.mediaManager = new MediaManager();

        this.bind__onGetMediaInfoSuccess = this._onGetMediaInfoSuccess.bind(this);
        this.bind__onGetMediaInfoError = this._onGetMediaInfoError.bind(this);
        this.bind__onLoadPageSuccess = this._onLoadPageSuccess.bind(this);
//        this.bind__loadSelectFromMyFilesFunction = this._loadSelectFromMyFilesFunction.bind(this);
//        this.bind__terminatingFunction = this._terminatingFunction.bind(this);
        this.bind__onClickRemoveSelectedMedia = this._onClickRemoveSelectedMedia.bind(this);
//        this.bind__forwardFunction = this._forwardFunction.bind(this);
//        this.bind__doneAndPostFunction = this._doneAndPostFunction.bind(this);


        $(this.mediaManager).on(MediaManager.Event.GET_INFO_SUCCESS, this.bind__onGetMediaInfoSuccess);
        $(this.mediaManager).on(MediaManager.Event.GET_INFO_ERROR, this.bind__onGetMediaInfoError);
    };

    MediaChooser.TAG = "MediaChooser";

    MediaChooser.Page = {
        RECORD_VIDEO: "recordVideo",
        SELECT: "select",
        PREVIEW: "preview"
    };

    MediaChooser.Event = {
//        PAGE_LOADED: "eventPageLoaded",
        SUCCESS: "eventSuccess",
        SUCCESS_AND_POST: "eventSuccessAndPost",
        ERROR: "eventError",
        RESET: "eventReset"
//        DIALOG_CLOSE: "eventDialogClose"
    };

    MediaChooser.Binder = {
        CONTAINER_CHOOSE: ".mediachooser-container-choose",
        RECORD_VIDEO: ".mediachooser-record-video",
        UPLOAD_FILE: ".mediachooser-upload-file",
        SELECT: ".mediachooser-select",
//        CHOOSE: ".mediachooser-choose",
//        MODAL_DIALOG: ".mediachooser-modal",

        CONTAINER_UPLOAD: ".mediachooser-container-upload",
        UPLOAD_TITLE: ".mediachooser-upload-title",

        CONTAINER_SELECTED: ".mediachooser-container-selected",
        WORKING: ".mediachooser-working",
        SELECTED: ".mediachooser-selected",
        SELECTED_MEDIA: ".mediachooser-selected-media",
        REMOVE: ".mediachooser-remove"

//        CONTAINER_SELECT: ".mediachooser-container-select"
    };

    // this must be the same name defined in {bundle}/Form/Type/MediaType
    MediaChooser.FORM_NAME = "media";

    // int constants from Entity\Media
    MediaChooser.MEDIA_TYPE = {
        AUDIO: {id: 2, str: "audio"},
        VIDEO: {id: 1, str: "video"},
        IMAGE: {id: 0, str: "image"},
        OTHER: {id: 9, str: "other"}
    };

    MediaChooser.DIALOG_TITLE_SELECT = "Select from My Files";
    MediaChooser.DIALOG_TITLE_PREVIEW = "Preview";

    MediaChooser.prototype.getContainer = function() {
        return this.container;
    };

    MediaChooser.prototype._getElement = function(binder) {
        return this.getContainer().find(binder);
    };

    MediaChooser.prototype.getForm = function() {
        if (typeof this.uploadForm === "undefined")
            this.uploadForm = $(this._getElement(MediaChooser.Binder.CONTAINER_UPLOAD).data("form"));

        return this.uploadForm;
    };

    MediaChooser.prototype.getFormField = function(fieldName) {
        return this.getForm().find("#" + MediaChooser.FORM_NAME + "_" + fieldName);
    };

    MediaChooser.prototype.bindUIEvents = function() {
        console.log("%s: %s", MediaChooser.TAG, "bindUIEvents");

//        this.modalDialog = this._getElement(MediaChooser.Binder.MODAL_DIALOG).modal({show: false});
//        this.modalDialog.on("hidden.bs.modal", this.bind__terminatingFunction);

        this._getElement(MediaChooser.Binder.RECORD_VIDEO).on("click", (function(e) {
            e.preventDefault();

            this.page = MediaChooser.Page.RECORD_VIDEO;
            this.recorder = new Recorder({
                page: Recorder.Page.NORMAL,
                enableDoneAndPost: this.enableDoneAndPost
            });
            $(this.recorder).on(Recorder.Event.READY, (function(e) {
                this.recorder.show();
            }).bind(this));
            $(this.recorder).on(Recorder.Event.DONE, (function(e) {
                this.recorder.hide();
                //this.activeMedia = e.media;
                this._addSelectedMedia(e.media);
                this._invokeSuccess(e.doPost);
            }).bind(this));
            $(this.recorder).on(Recorder.Event.HIDDEN, (function(e) {
                this.recorder.destroy();
            }).bind(this));
            this.recorder.render();
        }).bind(this));

        this._getElement(MediaChooser.Binder.UPLOAD_FILE).on("click", (function(e) {
            e.preventDefault();

            this.getFormField("resource_file").click();
        }).bind(this));

        this.getFormField("resource_file").on("change", (function(e) {
            e.preventDefault();
                var maxSize = this.getFormField(
                    "resource_file").data('maxsize');
                var fileSize = this
                    .getFormField("resource_file")[0].files[0].size;
                if (fileSize > maxSize) {
                    alert(Translator
                        .trans(
                        'form.upload.maxFileSizeExceeded',
                        {
                            'fileSize': (fileSize / 1048576)
                                .toFixed(1)
                                + "MB",
                            'maxUploadSize': (maxSize / 1048576)
                                .toFixed(1)
                                + "MB"
                        }));
                    this.getFormField("resource_file").val(
                        "");
                    return;
                }

            this._loadPage({
                showPopup: false,
                url: Routing.generate("imdc_myfiles_add"),
                method: "POST",
                data: new FormData(this.getForm()[0]),
                uploadProgress: true
            });

            this.getFormField("resource_file").val("")
        }).bind(this));

        this._getElement(MediaChooser.Binder.SELECT).on("click", (function(e) {
            e.preventDefault();

            this.myFilesSelector = new MyFilesSelector();
            $(this.myFilesSelector).on(MyFilesSelector.Event.READY, (function(e) {
                this.myFilesSelector.show();
            }).bind(this));
            $(this.myFilesSelector).on(MyFilesSelector.Event.DONE, (function(e) {
                this.myFilesSelector.hide();
                $.each(e.media, (function(index, element) {
                    this._addSelectedMedia(element);
                }).bind(this));
                this._invokeSuccess();
            }).bind(this));
            $(this.myFilesSelector).on(MyFilesSelector.Event.HIDDEN, (function(e) {
                this.myFilesSelector.destroy();
            }).bind(this));
            this.myFilesSelector.render();
        }).bind(this));

        this._getElement(MediaChooser.Binder.SELECTED).sortable({
            update: (function(event, ui) {
                $(this).trigger($.Event(MediaChooser.Event.SUCCESS, {}));
            }).bind(this)
        });
    };

//    MediaChooser.prototype._bindUIEventsSelectFromMyFiles = function() {
//        console.log("%s: %s", MediaChooser.TAG, "_bindUIEventsSelectFromMyFiles");
//
//        var container = $(MediaChooser.Binder.CONTAINER_SELECT);
//        var instance = this;
//        container.find(".preview-button").on("click", (function(e) {
//            e.preventDefault();
//
//            if ($(e.currentTarget).hasClass("disabled"))
//                return false;
//
//            this.previewMedia($(e.currentTarget).data("val"));
//        }).bind(this));
//
//        container.find("span.edit-title").editable({
//            toggle: 'manual',
//            unsavedclass: null,
//            success: function(response, newValue) {
//                instance.mediaManager.updateMedia({
//                    id: $(this).data('val'),
//                    title: newValue
//                });
//            }
//        });
//
//        container.find("a.edit-title").on("click", function(e) {
//            e.stopPropagation();
//            $(this).prev().editable('toggle');
//        });
//
//        container.find(".delete-button").on("click", (function(e) {
//            e.preventDefault();
//            var file = $(e.target);
//
//            $(this.mediaManager).one(MediaManager.EVENT_DELETE_SUCCESS, function() {
//                file.parent().parent().parent().remove();
//            });
//            $(this.mediaManager).one(MediaManager.EVENT_DELETE_ERROR, function(error, e) {
//                if (e.status == 500) {
//                    alert(e.statusText);
//                } else {
//                    alert('Error: ' + error);
//                }
//            });
//
//            return this.mediaManager.deleteMedia(file.data("val"), Translator.trans('filesGateway.deleteConfirmMessage'));
//        }).bind(this));
//
//        container.find(".select-button").on("click", (function(e) {
//            e.preventDefault();
//
//            var isSelected = $.grep(this.media, function(elementOfArray, indexInArray) {
//                return elementOfArray.id == $(e.target).data("val");
//            }).length > 0;
//
//            if (!isSelected) {
//                this.activeMedia = {
//                    id: $(e.target).data("val"),
//                    title: $(e.target).data("title"),
//                    resource: {
//                        pathMPEG: $(e.target).data("resourcewebpath")
//                    }
//                };
//                this._invokeSuccess();
//            }
//
//            this._terminatingFunction();
//        }).bind(this));
//
//        // KnpPaginatorBundle:Pagination:twitter_bootstrap_v3_pagination.html.twig
//        // override pagination urls
//        container.find("ul.pagination li a").on("click", (function(e) {
//            e.preventDefault();
//
//            this._loadPage({
//                showPopup: true,
//                url: $(e.target).attr("href"),
//                method: "GET"
//            });
//        }).bind(this));
//    };

//    MediaChooser.prototype._bindUIEventsPreview = function() {
//        console.log("%s: %s", MediaChooser.TAG, "_bindUIEventsPreview");
//
//        $('#mediaPreviewTitle').blur((function(e) {
//            console.log('updated title');
//            this.activeMedia.title = $('#mediaPreviewTitle').val();
//            this.mediaManager.updateMedia(this.activeMedia);
//        }).bind(this));
//    };

//    MediaChooser.prototype.previewMedia = function(mediaId) {
//        console.log("%s: %s", MediaChooser.TAG, "previewMedia");
//
//        this.page = MediaChooser.Page.PREVIEW;
//        this._loadPage({
//            showPopup: true,
//            url: Routing.generate('imdc_myfiles_preview', {
//                mediaId: mediaId
//            }),
//            method: "GET"
//        });
//    };

//    MediaChooser.prototype._getPopupDialogTitle = function() {
//        console.log("%s: %s", MediaChooser.TAG, "_getPopupDialogTitle");
//
//        switch (this.page) {
//            case MediaChooser.Page.SELECT:
//                return MediaChooser.DIALOG_TITLE_SELECT;
//            case MediaChooser.Page.PREVIEW:
//                return MediaChooser.DIALOG_TITLE_PREVIEW;
//        }
//    };

    MediaChooser.prototype._loadPage = function(options) {
        console.log("%s: %s", MediaChooser.TAG, "_loadPage");

//        this.modalDialog.modal(options.showPopup ? "show" : "hide");
//        this.modalDialog.find(".modal-title").html(this._getPopupDialogTitle());
//        this.modalDialog.find(".modal-body").html("");

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

                        Helper.updateProgressBar(this._getElement(MediaChooser.Binder.CONTAINER_UPLOAD).show(),
                            Math.floor((e.loaded / e.total) * 100));
                    }).bind(this), false);

                    return xhr;
                }).bind(this);
            }
        }

        $.ajax(request);
    };

    MediaChooser.prototype._onLoadPageSuccess = function(data, textStatus, jqXHR) {
        console.log("%s: %s- finished=%s", MediaChooser.TAG, "_onLoadPageSuccess", data.finished);

        if (data.finished) {
            //this.activeMedia = data.media;
            this._addSelectedMedia(data.media);
            this._invokeSuccess();
            //this._terminatingFunction();
        } else {
//            this.modalDialog.find(".modal-body").html(data.page);
//
//            if (this.page == MediaChooser.Page.SELECT)
//                this._bindUIEventsSelectFromMyFiles();
//
//            if (this.page == MediaChooser.Page.PREVIEW) {
//                this.activeMedia = data.media;
//                this._bindUIEventsPreview();
//            }
        }

//        $(this).trigger($.Event(MediaChooser.Event.PAGE_LOADED, {
//            payload: data //FIXME some event listeners my still use 'data' instead of 'payload'. 'data' is reserved by jquery
//        }));
    };

//    MediaChooser.prototype.createVideoPlayer = function() {
//        console.log("%s: %s", MediaChooser.TAG, "createVideoPlayer");
//
//        var forwardButtons = [ this.doneButton ];
//        var forwardFunctions = [ this.bind__forwardFunction ];
//        if (this.enableDoneAndPost) {
//            forwardButtons.push(this.doneAndPostButton);
//            forwardFunctions.push(this.bind__doneAndPostFunction);
//        }
//
//        var backButtons;
//        var backFunctions;
//        if (this.isFileSelection) {
//            backButtons = [ this.backButton ];
//            backFunctions = [ this.bind__loadSelectFromMyFilesFunction ];
//        }
//
//        this.player = new Player($("#" + this.activeMedia.id), {
//            areaSelectionEnabled: true,
//            audioBar: false,
//            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_RELATIVE,
//            forwardButtons: forwardButtons,
//            forwardFunctions: forwardFunctions,
//            backButtons: backButtons,
//            backFunctions: backFunctions,
//            selectedRegionColor: "#0000ff"
//        });
//
//        this.player.createControls();
//
//        $(this.player.elementID).find(
//            ".videoControlsContainer.controlsBar.doneButton").eq(0)
//            .attr(
//            "title",
//            Translator
//                .trans('player.previewing.doneButton'))
//
//        $(this.player.elementID)
//            .find(
//            ".videoControlsContainer.controlsBar.doneAndPostButton")
//            .eq(0)
//            .attr(
//            "title",
//            Translator
//                .trans('player.previewing.doneAndPostButton'))
//
//        $(this.player.elementID)
//            .find(".videoControlsContainer.controlsBar.backButton")
//            .eq(0)
//            .attr(
//            "title",
//            Translator
//                .trans('player.previewing.backToRecordingButton'))
//    };

//    MediaChooser.prototype._forwardFunction = function(e) {
//        console.log("%s: %s", MediaChooser.TAG, "_forwardFunction");
//        e.preventDefault();
//
//        var previousMinMaxTimes = this.player.getCurrentMinMaxTime();
//        var currentMinMaxTimes = this.player.getAreaSelectionTimes();
//        this.player.setCurrentMinMaxTime(currentMinMaxTimes.minTime, currentMinMaxTimes.maxTime);
//
//        console.log("Current Min/Max Times %s %s", currentMinMaxTimes.minTime, currentMinMaxTimes.maxTime);
//        console.log("Cutting to Min/Max Times %s %s", currentMinMaxTimes.minTime - previousMinMaxTimes.minTime,
//                currentMinMaxTimes.maxTime - previousMinMaxTimes.minTime);
//
//        this.mediaManager.trimMedia(this.activeMedia.id, currentMinMaxTimes.minTime - previousMinMaxTimes.minTime,
//                currentMinMaxTimes.maxTime - previousMinMaxTimes.minTime);
//    };

//    MediaChooser.prototype._doneAndPostFunction = function(e) {
//        console.log("%s: %s", MediaChooser.TAG, "_doneAndPostFunction");
//        e.preventDefault();
//
//        this.destroyVideoRecorder();
//        if (this.activeMedia)
//            this._invokeSuccess(true);
//        this._terminatingFunction();
//    };

//    MediaChooser.prototype._loadSelectFromMyFilesFunction = function(e) {
//        if (e)
//            e.preventDefault();
//
//        this.page = MediaChooser.Page.SELECT;
//        this._loadPage({
//            showPopup: true,
//            url: Routing.generate("imdc_myfiles_list"),
//            method: "GET"
//        });
//    };

//    MediaChooser.prototype.destroyVideoRecorder = function() {
//        console.log("%s: %s", MediaChooser.TAG, "destroyVideoRecorder");
//
//        if (this.player !== null)
//            this.player.destroyRecorder();
//    };

//    MediaChooser.prototype._terminatingFunction = function() {
//        console.log("%s: %s", MediaChooser.TAG, "_terminatingFunction");
//
//        this.modalDialog.find(".modal-body").html("");
//
//        if (this.modalDialog.data("bs.modal").isShown) {
//            this.modalDialog.off("hidden.bs.modal");
//            this.modalDialog.modal("hide");
//        }
//
//        $(this).trigger($.Event(MediaChooser.Event.DIALOG_CLOSE, {
//            media: this.activeMedia
//        }));
//    };

    MediaChooser.prototype._onClickRemoveSelectedMedia = function(e) {
        e.preventDefault();

        var mediaId = $(e.currentTarget).data("mid");
        this._removeSelectedMedia(mediaId);

        $(this).trigger($.Event(MediaChooser.Event.SUCCESS, {}));
    };

    MediaChooser.prototype._addSelectedMedia = function(media) {
        var count = this._getElement(MediaChooser.Binder.SELECTED_MEDIA)
            .filter("[data-mid='" + media.id + "']")
            .length;

        if (count > 0)
            return; // exists

        var container = this._getElement(MediaChooser.Binder.SELECTED);
        var newSelectedMedia = container.data("prototype");
        newSelectedMedia = newSelectedMedia.replace(/__id__/g, media.id);
        newSelectedMedia = newSelectedMedia.replace(/__title__/g, media.title);
        newSelectedMedia = newSelectedMedia.replace(/__resource_webPath__/g, media.resource.web_path);
        container.append(newSelectedMedia);

        this._getElement(MediaChooser.Binder.SELECTED_MEDIA)
            .filter("[data-mid='" + media.id + "']")
            .find(MediaChooser.Binder.REMOVE)
            .on("click", this.bind__onClickRemoveSelectedMedia);

        this._addMedia(media);
    };

    MediaChooser.prototype._removeSelectedMedia = function(mediaId) {
        this._removeMedia(mediaId);

        this._getElement(MediaChooser.Binder.SELECTED_MEDIA)
            .filter("[data-mid='" + mediaId + "']")
            .remove();
    };

    MediaChooser.prototype._invokeSuccess = function(doPost) {
        console.log("%s: %s", MediaChooser.TAG, "_invokeSuccess");

        var event = {media: this.media};

        this._getElement(MediaChooser.Binder.UPLOAD_TITLE).html("");
        this._getElement(MediaChooser.Binder.CONTAINER_UPLOAD).hide();

        if (typeof doPost != "undefined" && doPost == true) {
            $(this).trigger($.Event(MediaChooser.Event.SUCCESS_AND_POST, event));
        } else {
            $(this).trigger($.Event(MediaChooser.Event.SUCCESS, event));
        }
    };

    MediaChooser.prototype._invokeError = function(jqXHR) {
        console.log("%s: %s- jqXHR=%o", MediaChooser.TAG, "_invokeError", jqXHR);

        this._getElement(MediaChooser.Binder.UPLOAD_TITLE).html("");
        this._getElement(MediaChooser.Binder.CONTAINER_UPLOAD).hide();

        $(this).trigger($.Event(MediaChooser.Event.ERROR, {
            jqXHR: jqXHR
        }));
    };

    MediaChooser.prototype._invokeReset = function() {
        console.log("%s: %s", MediaChooser.TAG, "_invokeReset");

        this._getElement(MediaChooser.Binder.UPLOAD_TITLE).html("");
        this._getElement(MediaChooser.Binder.CONTAINER_UPLOAD).hide();

//        this.activeMedia = null;
        this.media = [];

        $(this).trigger($.Event(MediaChooser.Event.RESET, {}));
    };

    MediaChooser.prototype.setContainer = function(container) {
        console.log("%s: %s", MediaChooser.TAG, "setContainer");

        this.container = typeof container != "undefined" ? container : this.container;
    };

    MediaChooser.prototype._addMedia = function(media) {
        console.log("%s: %s", MediaChooser.TAG, "_addMedia");

        if (typeof media === "undefined")
            return;

        this.media.push(media);
    };

    MediaChooser.prototype._removeMedia = function(mediaId) {
        console.log("%s: %s", MediaChooser.TAG, "_removeMedia");

        for (var m in this.media) {
            var media = this.media[m];
            if (media.id == mediaId) {
                this.media.splice(m, 1);
                break;
            }
        }
    };

    MediaChooser.prototype._toggleForm = function(disabled) {
        this._getElement(MediaChooser.Binder.RECORD_VIDEO).attr('disabled', disabled);
        this._getElement(MediaChooser.Binder.UPLOAD_FILE).attr('disabled', disabled);
        this._getElement(MediaChooser.Binder.SELECT).attr('disabled', disabled);
    };

    MediaChooser.prototype._onGetMediaInfoSuccess = function(e) {
        //data, textStatus, jqXHR
        this._getElement(MediaChooser.Binder.WORKING).hide();
        this._toggleForm(false);

        $.each(e.payload.media, (function(index, element) {
            this._addSelectedMedia(element);
        }).bind(this));
        this._invokeSuccess();
    };

    MediaChooser.prototype._onGetMediaInfoError = function(e) {
        //jqXHR, textStatus, errorThrown
        this._getElement(MediaChooser.Binder.WORKING).hide();
        this._toggleForm(false);

        console.log(e.jqXHR);
    };

    MediaChooser.prototype.setMedia = function(mediaIds) {
        console.log("%s: %s", MediaChooser.TAG, "setMedia");

        if (typeof mediaIds === "undefined")
            return;

        this._toggleForm(true);
        this._getElement(MediaChooser.Binder.WORKING).show();
        this._getElement(MediaChooser.Binder.SELECTED_MEDIA).html("");

        this.mediaManager.getInfo(mediaIds);

//        if (mediaIds.length > 0) {
//            //this.bindBlocked = true;
//
//            this._toggleForm(true);
//            this._getElement(MediaChooser.Binder.WORKING).show();
//            this._getElement(MediaChooser.Binder.SELECTED_MEDIA).html("");
//
//            $.ajax({
//                url: Routing.generate("imdc_myfiles_get_info"),
//                data: {mediaIds: mediaIds},
//                type: 'POST',
//                success: (function(data, textStatus, jqXHR) {
//                    //console.log("%s: %s: %s", Post.TAG, "handlePage", "success");
//
//                    this.media = data.media;
//                    if (this.isFileSelection) {
//                        $.each(this.media, (function(index, element) {
//                            this._addSelectedMedia(element);
//                        }).bind(this));
//                    }
//
//                    this._getElement(MediaChooser.Binder.WORKING).hide();
//                    this._toggleForm(false);
//
//                    /*this.bindBlocked = false;
//                    if (this.bindRequested) {
//                        this.bindUIEvents();
//                    }*/
//
//                    $(this).trigger($.Event(MediaChooser.Event.SUCCESS, {}));
//                }).bind(this),
//                error: (function(request) {
//                    //console.log("%s: %s: %s", Post.TAG, "handlePage", "error");
//
//                    console.log(request.statusText);
//
//                    this._getElement(MediaChooser.Binder.WORKING).hide();
//                    this._toggleForm(false);
//
//                    /*this.bindBlocked = false;
//                    if (this.bindRequested) {
//                        this.bindUIEvents();
//                    }*/
//                }).bind(this)
//            });
//        }
    };

    MediaChooser.prototype.generateFormData = function(prototype) {
        var media = "";

        this._getElement(MediaChooser.Binder.SELECTED_MEDIA).each(function(index, element) {
            var newMedia = $(prototype.replace(/__name__/g, index));
            newMedia.val($(element).data("mid"));

            media += newMedia[0].outerHTML;
        });

        return media;
    };

    MediaChooser.prototype.reset = function() {
        this._invokeReset();
    };

    return MediaChooser;
});
