define([
    'core/subscriber',
    'core/mediaManager',
    'component/recorderComponent',
    'component/myFilesSelectorComponent',
    'core/helper',
    'extra'
], function (Subscriber, MediaManager, RecorderComponent, MyFilesSelectorComponent, Helper) {
    'use strict';

    var MediaChooserComponent = function (options) {
        Subscriber.prototype.constructor.apply(this);

        this.options = options;
        this.media = [];

        this.bind__onGetMediaInfoSuccess = this._onGetMediaInfoSuccess.bind(this);
        this.bind__onGetMediaInfoError = this._onGetMediaInfoError.bind(this);
        this.bind__onClickRecordVideo = this._onClickRecordVideo.bind(this);
        this.bind__onClickUploadFile = this._onClickUploadFile.bind(this);
        this.bind__onClickSelect = this._onClickSelect.bind(this);
        this.bind__onChangeResourceFile = this._onChangeResourceFile.bind(this);
        this.bind__onClickRemoveSelectedMedia = this._onClickRemoveSelectedMedia.bind(this);

        this.$container = this.options.$container;
        this.$containerUpload = this.$container.find(MediaChooserComponent.Binder.CONTAINER_UPLOAD);
        this.$uploadForm = $(this.$containerUpload.data('form'));
        this.$resourceFile = this._getFormField('resource_file');
        this.$recordVideo = this.$container.find(MediaChooserComponent.Binder.RECORD_VIDEO);
        this.$uploadFile = this.$container.find(MediaChooserComponent.Binder.UPLOAD_FILE);
        this.$select = this.$container.find(MediaChooserComponent.Binder.SELECT);
        this.$uploadTitle = this.$container.find(MediaChooserComponent.Binder.UPLOAD_TITLE);
        this.$working = this.$container.find(MediaChooserComponent.Binder.WORKING);
        this.$selected = this.$container.find(MediaChooserComponent.Binder.SELECTED);

        this.$resourceFile.on('change', this.bind__onChangeResourceFile);
        this.$recordVideo.on('click', this.bind__onClickRecordVideo);
        this.$uploadFile.on('click', this.bind__onClickUploadFile);
        this.$select.on('click', this.bind__onClickSelect);

        this.$selected.sortable({
            update: function (event, ui) {
                this._dispatch(MediaChooserComponent.Event.SUCCESS, {
                    mediaChooserComponent: this
                });
            }.bind(this)
        });

        this.mediaManager = new MediaManager();
        $(this.mediaManager).on(MediaManager.Event.GET_INFO_SUCCESS, this.bind__onGetMediaInfoSuccess);
        $(this.mediaManager).on(MediaManager.Event.GET_INFO_ERROR, this.bind__onGetMediaInfoError);
    };

    MediaChooserComponent.extend(Subscriber);

    MediaChooserComponent.TAG = 'MediaChooserComponent';

    MediaChooserComponent.Binder = {
        RECORD_VIDEO: '.mediachooser-record-video',
        UPLOAD_FILE: '.mediachooser-upload-file',
        SELECT: '.mediachooser-select',

        CONTAINER_UPLOAD: '.mediachooser-container-upload',
        UPLOAD_TITLE: '.mediachooser-upload-title',

        WORKING: '.mediachooser-working',
        SELECTED: '.mediachooser-selected',
        SELECTED_MEDIA: '.mediachooser-selected-media',
        REMOVE: '.mediachooser-remove'
    };

    MediaChooserComponent.Event = {
        SUCCESS: 'eventSuccess',
        ERROR: 'eventError',
        RESET: 'eventReset'
    };

    // this must be the same name defined in {bundle}/Form/Type/MediaType
    MediaChooserComponent.FORM_NAME = 'media';

    MediaChooserComponent.prototype._getFormField = function (fieldName) {
        return this.$uploadForm.find('#' + MediaChooserComponent.FORM_NAME + '_' + fieldName);
    };

    MediaChooserComponent.prototype._uploadFile = function (form) {
        console.log('%s: %s', MediaChooserComponent.TAG, '_loadPage');

        var settings = {
            url: Routing.generate('imdc_myfiles_add'),
            type: 'POST',
            processData: false,
            data: new FormData(form),
            contentType: false,
            xhr: function () {
                var xhr = $.ajaxSettings.xhr();
                xhr.upload.addEventListener('progress', function (e) {
                    if (!e.lengthComputable)
                        return;

                    Helper.updateProgressBar(this.$containerUpload.show(),
                        Math.floor((e.loaded / e.total) * 100));
                }.bind(this), false);

                return xhr;
            }.bind(this)
        };

        return $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                console.log('%s: %s- finished=%s', MediaChooserComponent.TAG, '_onLoadPageSuccess', data.finished);
                if (data.finished) {
                    return $.Deferred().resolve(data);
                } else {
                    return $.Deferred().reject();
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log('%s: %s: %s', MediaChooserComponent.TAG, '_loadPage', 'error');
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    MediaChooserComponent.prototype._addSelectedMedia = function (media) {
        var count = this.$selected.find(MediaChooserComponent.Binder.SELECTED_MEDIA)
            .filter('[data-mid="' + media.id + '"]')
            .length;

        if (count > 0)
            return; // exists

        var newSelectedMedia = this.$selected.data('prototype');
        newSelectedMedia = newSelectedMedia.replace(/__id__/g, media.id);
        newSelectedMedia = newSelectedMedia.replace(/__title__/g, media.title);
        newSelectedMedia = newSelectedMedia.replace(/__resource_webPath__/g, media.resource.web_path);
        this.$selected.append(newSelectedMedia);

        this.$selected.find(MediaChooserComponent.Binder.SELECTED_MEDIA)
            .filter('[data-mid="' + media.id + '"]')
            .find(MediaChooserComponent.Binder.REMOVE)
            .on('click', this.bind__onClickRemoveSelectedMedia);

        this.media.push(media);
    };

    MediaChooserComponent.prototype._removeSelectedMedia = function (mediaId) {
        for (var m in this.media) {
            var media = this.media[m];
            if (media.id == mediaId) {
                this.media.splice(m, 1);
                break;
            }
        }

        this.$selected.find(MediaChooserComponent.Binder.SELECTED_MEDIA)
            .filter('[data-mid="' + mediaId + '"]')
            .remove();
    };

    MediaChooserComponent.prototype._resetUpload = function () {
        this.$containerUpload.hide();
        this.$uploadTitle.html('');
    };

    MediaChooserComponent.prototype._invokeSuccess = function (doPost) {
        this._resetUpload();

        var event = (typeof doPost != 'undefined' && doPost == true)
            ? MediaChooserComponent.Event.SUCCESS_AND_POST
            : MediaChooserComponent.Event.SUCCESS;

        var args = {
            media: this.media,
            mediaChooserComponent: this
        };

        this._dispatch(event, args);
    };

    MediaChooserComponent.prototype._onClickRecordVideo = function (e) {
        e.preventDefault();

        RecorderComponent.render({
            enableDoneAndPost: this.options.enableDoneAndPost
        }, function (e) {
            this.recorderCmp = e.recorderComponent;
            this.recorderCmp.subscribe(RecorderComponent.Event.DONE, function (e) {
                this.recorderCmp.hide();
                this._addSelectedMedia(e.media);
                this._invokeSuccess(e.doPost);
            }.bind(this));
            this.recorderCmp.subscribe(RecorderComponent.Event.HIDDEN, function (e) {
                this.recorderCmp.destroy();
            }.bind(this));
            this.recorderCmp.show();
        }.bind(this));
    };

    MediaChooserComponent.prototype._onClickUploadFile = function (e) {
        e.preventDefault();

        this.$resourceFile.click();
    };

    MediaChooserComponent.prototype._onClickSelect = function (e) {
        e.preventDefault();

        MyFilesSelectorComponent.render({}, function (e) {
            this.mfsCmp = e.myFilesSelectorComponent;
            this.mfsCmp.subscribe(MyFilesSelectorComponent.Event.DONE, function (e) {
                this.mfsCmp.hide();
                $.each(e.media, (function (index, element) {
                    this._addSelectedMedia(element);
                }).bind(this));
                this._invokeSuccess();
            }.bind(this));
            this.mfsCmp.subscribe(MyFilesSelectorComponent.Event.HIDDEN, function (e) {
                this.mfsCmp.destroy();
            }.bind(this));
            this.mfsCmp.show();
        }.bind(this));
    };

    MediaChooserComponent.prototype._onChangeResourceFile = function (e) {
        e.preventDefault();

        var maxSize = this.$resourceFile.data('maxsize');
        var fileSize = this.$resourceFile[0].files[0].size;
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
            this.$resourceFile.val('');
            return;
        }

        this._uploadFile(this.$uploadForm[0])
            .done(function (data) {
                this._addSelectedMedia(data.media);
                this._invokeSuccess();
            }.bind(this))
            .fail(function () {
                this._resetUpload();

                this._dispatch(MediaChooserComponent.Event.ERROR, {
                    mediaChooserComponent: this
                });
            }.bind(this));

        this.$resourceFile.val('');
    };

    MediaChooserComponent.prototype._onClickRemoveSelectedMedia = function (e) {
        e.preventDefault();

        var mediaId = $(e.currentTarget).data('mid');
        this._removeSelectedMedia(mediaId);

        this._dispatch(MediaChooserComponent.Event.SUCCESS, {
            mediaChooserComponent: this
        });
    };

    MediaChooserComponent.prototype._toggleForm = function (disabled) {
        this.$recordVideo.attr('disabled', disabled);
        this.$uploadFile.attr('disabled', disabled);
        this.$select.attr('disabled', disabled);
    };

    MediaChooserComponent.prototype._onGetMediaInfoSuccess = function (e) {
        //data, textStatus, jqXHR
        this.$working.hide();
        this._toggleForm(false);

        $.each(e.payload.media, function (index, element) {
            this._addSelectedMedia(element);
        }.bind(this));
        this._invokeSuccess();
    };

    MediaChooserComponent.prototype._onGetMediaInfoError = function (e) {
        //jqXHR, textStatus, errorThrown
        this.$working.hide();
        this._toggleForm(false);

        console.log(e.jqXHR);
    };

    MediaChooserComponent.prototype.setMedia = function (mediaIds) {
        console.log('%s: %s', MediaChooserComponent.TAG, 'setMedia');

        if (typeof mediaIds === 'undefined')
            return;

        this._toggleForm(true);
        this.$working.show();
        this.$selected.html('');

        this.mediaManager.getInfo(mediaIds);
    };

    MediaChooserComponent.prototype.generateFormData = function (prototype) {
        var media = '';

        this.$selected.find(MediaChooserComponent.Binder.SELECTED_MEDIA)
            .each(function (index, element) {
                var newMedia = $(prototype.replace(/__name__/g, index));
                newMedia.val($(element).data('mid'));

                media += newMedia[0].outerHTML;
            });

        return media;
    };

    MediaChooserComponent.prototype.reset = function () {
        console.log('%s: %s', MediaChooserComponent.TAG, 'reset');

        this._resetUpload();

        this.media = [];

        this._dispatch(MediaChooserComponent.Event.RESET, {
            mediaChooserComponent: this
        });
    };

    MediaChooserComponent.render = function ($form, options) {
        var defaults = {
            enableDoneAndPost: false
        };

        options = options || defaults;
        for (var o in defaults) {
            options[o] = typeof options[o] != 'undefined' ? options[o] : defaults[o];
        }

        options.$container = $form;

        return new MediaChooserComponent(options);
    };

    return MediaChooserComponent;
});
