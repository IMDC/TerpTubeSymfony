define([
    'core/subscriber',
    'service',
    'component/myFilesSelectorComponent',
    'model/mediaModel',
    'factory/mediaFactory',
    'service/rabbitmqWebStompService',
    'core/helper',
    'extra'
], function (Subscriber, Service, MyFilesSelectorComponent, MediaModel, MediaFactory, RabbitmqWebStompService, Helper) {
    'use strict';

    var RecorderComponent = function (options) {
        Subscriber.prototype.constructor.apply(this);

        this.options = options;
        //this.inPreviewMode = false;
        this.player = null;
        this.recorder = null;
        this.sourceMedia = null;
        this.recordedMedia = null;
        this.rabbitmqWebStompService = Service.get('rabbitmqWebStomp');

        this.forwardButton = '<button class="forwardButton"></button>';
        this.doneButton = '<button class="doneButton"></button>';
        this.doneAndPostButton = '<button class="doneAndPostButton"></button>';
        this.backButton = '<button class="backButton"></button>';

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

        this.$container = this.options.$container;
        this.$modalDialog = this.$container.find(RecorderComponent.Binder.MODAL_DIALOG);
        this.$tabPanes = this.$container.find(RecorderComponent.Binder.TAB_PANES);
        this.$normalTitle = this.$container.find(RecorderComponent.Binder.NORMAL_TITLE);
        this.$normalVideo = this.$container.find(RecorderComponent.Binder.NORMAL_VIDEO);
        this.$interpSelect = this.$container.find(RecorderComponent.Binder.INTERP_SELECT);
        this.$interpVideoP = this.$container.find(RecorderComponent.Binder.INTERP_VIDEO_P);
        this.$interpTitle = this.$container.find(RecorderComponent.Binder.INTERP_TITLE);
        this.$interpVideoR = this.$container.find(RecorderComponent.Binder.INTERP_VIDEO_R);
        this.$controls = this.$container.find(RecorderComponent.Binder.CONTROLS);

        var tab = this.options.tab == RecorderComponent.Tab.NORMAL
            ? RecorderComponent.Binder.NORMAL
            : RecorderComponent.Binder.INTERP;
        this.$modalDialog.find('a[href!="' + tab + '"]').parent().removeClass('active');
        this.$modalDialog.find('a[href="' + tab + '"]').parent().addClass('active');
        this.$modalDialog.find('.tab-pane:not("' + tab + '")').removeClass('active');
        this.$modalDialog.find('.tab-pane' + tab).addClass('active');
        this.$modalDialog.modal({backdrop: 'static', show: false});

        this.$modalDialog.on('shown.bs.modal', this.bind__onShownModal);
        this.$modalDialog.on('hidden.bs.modal', this.bind__onHiddenModal);
        this.$tabPanes.on('show.bs.tab', this.bind__onShowTab);
        this.$tabPanes.on('shown.bs.tab', this.bind__onShownTab);
        this.$normalTitle.blur(this.bind__onBlurPlayerTitle);
        this.$interpSelect.on('click', this.bind__onClickInterpSelect);
        this.$interpTitle.blur(this.bind__onBlurPlayerTitle);

        // listen for status updates
        this.messages = [];
        this.bind__onMessage = function (e) {
            console.log(e.message);

            // store the messages, since a request (_onRecordingSuccess) may not have completed yet
            this.messages.push(e.message);

            this._checkMessages();
        }.bind(this);
        this.bind__onConnect = function (e) {
            this.subscription = this.rabbitmqWebStompService.subscribe(
                '/exchange/entity-status',
                RabbitmqWebStompService.Event.MESSAGE, this.bind__onMessage);
        }.bind(this);

        if (!this.rabbitmqWebStompService.isConnected()) {
            this.rabbitmqWebStompService.subscribe(
                null,
                RabbitmqWebStompService.Event.CONNECTED, this.bind__onConnect);
            this.rabbitmqWebStompService.connect();
        } else {
            this.bind__onConnect(null);
        }

        //TODO interp preview/edit/trim
        if (this.options.mode == RecorderComponent.Mode.PREVIEW) {
            this.$modalDialog.find('a[href="' + RecorderComponent.Binder.INTERP + '"]').hide();
        }
    };

    RecorderComponent.extend(Subscriber);

    RecorderComponent.TAG = 'RecorderComponent';

    RecorderComponent.MAX_RECORDING_TIME = 720; // 12 Minutes

    RecorderComponent.Binder = {
        MODAL_DIALOG: '.recorder-modal',
        TAB_PANES: 'a[data-toggle="tab"]',
        NORMAL: '.recorder-normal',
        INTERP: '.recorder-interp',

        CONTAINER_RECORD: '.recorder-container-record',
        NORMAL_TITLE: '.recorder-normal-title',
        NORMAL_VIDEO: '.recorder-normal-video',
        INTERP_SELECT: '.recorder-interp-select',
        INTERP_MY_FILES_SELECTOR: '.recorder-interp-my-files-selector',
        INTERP_VIDEO_P: '.recorder-interp-video-p',
        INTERP_TITLE: '.recorder-interp-title',
        INTERP_VIDEO_R: '.recorder-interp-video-r',
        CONTROLS: '.recorder-controls',

        CONTAINER_UPLOAD: '.recorder-container-upload'
    };

    RecorderComponent.Tab = {
        NORMAL: 0,
        INTERPRETATION: 1
    };

    RecorderComponent.Mode = {
        RECORD: 0,
        PREVIEW: 1
    };

    RecorderComponent.Event = {
        DONE: 'eventDone',
        HIDDEN: 'eventHidden'
    };

    RecorderComponent.prototype._createPlayer = function (inPreviewMode) {
        console.log('%s: %s', RecorderComponent.TAG, '_createPlayer');

        inPreviewMode = typeof inPreviewMode != 'undefined' ? inPreviewMode : false;

        var forwardButtons = [];
        var forwardFunctions = [];
        if (this.options.tab == RecorderComponent.Tab.NORMAL || inPreviewMode) {
            forwardButtons.push(this.doneButton);
            forwardFunctions.push(inPreviewMode ? this.bind__cut : this.bind__done);
        }
        if (this.options.enableDoneAndPost && inPreviewMode) {
            forwardButtons.push(this.doneAndPostButton);
            forwardFunctions.push(this.bind__doneAndPost);
        }

        var backButtons;
        var backFunctions;
        if (inPreviewMode && this.wasRecording) {
            backButtons = [this.backButton];
            backFunctions = [this.bind__back];
        }

        var container = this.options.tab == RecorderComponent.Tab.NORMAL
            ? this.$normalVideo
            : inPreviewMode ? this.$interpVideoR : this.$interpVideoP;

        var player = new Player(container, {
            areaSelectionEnabled: inPreviewMode,
            audioBar: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_RELATIVE,
            controlBarElement: this.$controls,
            forwardButtons: forwardButtons,
            forwardFunctions: forwardFunctions,
            backButtons: backButtons,
            backFunctions: backFunctions,
            selectedRegionColor: '#0000ff'
        });
        player.createControls();

        $(player.elementID)
            .find('.videoControlsContainer.controlsBar.doneButton').eq(0)
            .attr('title', Translator.trans('player.previewing.doneButton'));

        $(player.elementID)
            .find('.videoControlsContainer.controlsBar.doneAndPostButton').eq(0)
            .attr('title', Translator.trans('player.previewing.doneAndPostButton'));

        $(player.elementID)
            .find('.videoControlsContainer.controlsBar.backButton').eq(0)
            .attr('title', Translator.trans('player.previewing.backToRecordingButton'));

        if (inPreviewMode) {
            this.recorder = player;
        } else {
            this.player = player;
        }
    };

    RecorderComponent.prototype._cut = function (e) {
        console.log('%s: %s', RecorderComponent.TAG, '_cut');
        e.preventDefault();

        var previousMinMaxTimes = this.recorder.getCurrentMinMaxTime();
        var currentMinMaxTimes = this.recorder.getAreaSelectionTimes();
        this.recorder.setCurrentMinMaxTime(currentMinMaxTimes.minTime, currentMinMaxTimes.maxTime);

        console.log('Current Min/Max Times %s %s', currentMinMaxTimes.minTime, currentMinMaxTimes.maxTime);
        console.log('Cutting to Min/Max Times %s %s', currentMinMaxTimes.minTime - previousMinMaxTimes.minTime,
            currentMinMaxTimes.maxTime - previousMinMaxTimes.minTime);

        MediaFactory.trim(this.recordedMedia,
            currentMinMaxTimes.minTime - previousMinMaxTimes.minTime,
            currentMinMaxTimes.maxTime - previousMinMaxTimes.minTime);

        //cut should always also call the done function
        this._done(e);
    };

    RecorderComponent.prototype._back = function (e) {
        console.log('%s: %s', RecorderComponent.TAG, '_back');
        e.preventDefault();

        this._destroyPlayers();

        // delete the current media!
        MediaFactory.delete(this.recordedMedia);

        // Go back to recording
        this.setRecordedMedia(null);
        this.wasRecording = false;
        this.options.mode = RecorderComponent.Mode.RECORD;
        //this.inPreviewMode = false;
        if (this.options.tab == RecorderComponent.Tab.INTERPRETATION)
            this._createPlayer();
        this._createRecorder();
    };

    RecorderComponent.prototype._createRecorder = function () {
        console.log('%s: %s', RecorderComponent.TAG, '_createRecorder');

        var forwardButtons = [this.forwardButton, this.doneButton];
        var forwardFunctions = [this.bind__preview, this.bind__done];
        if (this.options.enableDoneAndPost) {
            forwardButtons.push(this.doneAndPostButton);
            forwardFunctions.push(this.bind__doneAndPost);
        }

        var container;
        var additionalDataToPost = {};
        if (this.options.tab == RecorderComponent.Tab.NORMAL) {
            container = this.$normalVideo;
        } else {
            container = this.$interpVideoR;
            additionalDataToPost = {
                isInterpretation: true,
                sourceId: this.sourceMedia.get('id')
            }
        }

        this.recorder = new Player(container, {
            areaSelectionEnabled: false,
            audioBar: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
            controlBarElement: this.$controls,
            type: Player.DENSITY_BAR_TYPE_RECORDER,
            volumeControl: false,
            maxRecordingTime: RecorderComponent.MAX_RECORDING_TIME,
            recordingSuccessFunction: this.bind__onRecordingSuccess,
            recordingErrorFunction: this.bind__onRecordingError,
            recordingPostURL: Routing.generate('imdc_myfiles_add_recording'),
            additionalDataToPost: additionalDataToPost,
            forwardButtons: forwardButtons,
            forwardFunctions: forwardFunctions
        });
        if (this.options.tab == RecorderComponent.Tab.INTERPRETATION) {
            $(this.recorder).on(Player.EVENT_RECORDING_STARTED, this.bind__onRecordingStarted);
            $(this.recorder).on(Player.EVENT_RECORDING_STOPPED, this.bind__onRecordingStopped);
        }
        $(this.recorder).on(Player.EVENT_RECORDING_UPLOAD_PROGRESS, this.bind__onRecordingUploadProgress);
        $(this.recorder).on(Player.EVENT_RECORDING_UPLOADED, this.bind__onRecordingUploaded);
        this.recorder.createControls();

        $(this.recorder.elementID)
            .find('.videoControlsContainer.controlsBar.videoControls.recordButton').eq(0)
            .attr('title', Translator.trans('player.recording.recordingButton'));

        $(this.recorder.elementID)
            .find('.videoControlsContainer.controlsBar.forwardButton').eq(0)
            .attr('title', Translator.trans('player.recording.forwardButton'));

        $(this.recorder.elementID)
            .find('.videoControlsContainer.controlsBar.doneButton').eq(0)
            .attr('title', Translator.trans('player.recording.doneButton'));

        $(this.recorder.elementID)
            .find('.videoControlsContainer.controlsBar.doneAndPostButton').eq(0)
            .attr('title', Translator.trans('player.recording.doneAndPostButton'));
    };

    RecorderComponent.prototype._onRecordingStarted = function () {
        console.log('%s: %s', RecorderComponent.TAG, '_onRecordingStarted');

        this.player.setControlsEnabled(false);
        this.recorder.options.additionalDataToPost.sourceStartTime = this.player.getCurrentTime();
        this.player.play();
    };

    RecorderComponent.prototype._onRecordingStopped = function () {
        this.player.pause();
        this.player.setControlsEnabled(true);
    };

    RecorderComponent.prototype._onRecordingUploadProgress = function (e, percentComplete) {
        Helper.updateProgressBar(this._getElement(RecorderComponent.Binder.CONTAINER_UPLOAD).show(), percentComplete);
    };

    RecorderComponent.prototype._onRecordingUploaded = function (data) {
        Helper.updateProgressBar(this._getElement(RecorderComponent.Binder.CONTAINER_UPLOAD).hide(), 0);
    };

    RecorderComponent.prototype._onRecordingSuccess = function (data) {
        console.log('%s: %s- mediaId=%d', RecorderComponent.TAG, '_onRecordingSuccess', data.media.id);

        var media = new MediaModel(data.media);

        if (media.get('is_ready') == 2) {
            this.setRecordedMedia(media);
        } else {
            console.log('waiting for multiplex consumer');

            this.tempMedia = media;
            this._checkMessages();
        }
    };

    RecorderComponent.prototype._checkMessages = function () {
        if (!this.tempMedia)
            return;

        while (this.messages.length > 0) {
            var message = this.messages.pop();

            if (message.status == 'done' &&
                message.who.indexOf('\MultiplexConsumer') > -1 &&
                (message.what.indexOf('\Media') > -1 || message.what.indexOf('\Interpretation')) &&
                message.identifier == this.tempMedia.get('id')
            ) {
                console.log('done');

                //TODO replace with MediaFactory.get()
                MediaFactory.list([this.tempMedia.get('id')])
                    .done(function (data) {
                        this.tempMedia = null;
                        this.setRecordedMedia(data.media[0]);
                    }.bind(this))
                    .fail(function () {
                        //TODO
                    });
            }
        };
    };

    RecorderComponent.prototype._onRecordingError = function (e) {
        console.log('%s: %s- e=%s', RecorderComponent.TAG, '_onRecordingError', e);
    };

    RecorderComponent.prototype._preview = function (e) {
        console.log('%s: %s', RecorderComponent.TAG, '_preview');
        e.preventDefault();

        this._destroyPlayers();

        //this.setRecordedMedia(this.tempMedia);
        if (this.options.tab == RecorderComponent.Tab.INTERPRETATION)
            this._createPlayer();
        this.wasRecording = true;
        this.options.mode = RecorderComponent.Mode.PREVIEW;
        //this.inPreviewMode = true;
        this._createPlayer(true);
    };

    RecorderComponent.prototype._done = function (e) {
        console.log('%s: %s', RecorderComponent.TAG, '_done');
        e.preventDefault();

        this._dispatch(RecorderComponent.Event.DONE, {
            media: this.recordedMedia,
            doPost: false,
            recorderComponent: this
        });
    };

    RecorderComponent.prototype._doneAndPost = function (e) {
        console.log('%s: %s', RecorderComponent.TAG, '_doneAndPost');
        e.preventDefault();

        this._dispatch(RecorderComponent.Event.DONE, {
            media: this.recordedMedia,
            doPost: true,
            recorderComponent: this
        });
    };

    RecorderComponent.prototype._destroyPlayers = function () {
        console.log('%s: %s', RecorderComponent.TAG, '_destroyPlayers');

        var reset = (function (normal) {
            var old = normal.parent();
            normal.removeAttr('src');
            old.hide();
            old.after(normal.detach());
            old.remove();
        }).bind(this);

        if (this.player != null) {
            this.player.destroyRecorder();
            if (this.options.tab == RecorderComponent.Tab.NORMAL) {
                reset(this.$normalVideo);
            } else {
                if (this.sourceMedia != null)
                    reset(this.$interpVideoP);
            }
            this.player = null;
        }

        if (this.recorder != null) {
            this.recorder.destroyRecorder();
            if (this.options.tab == RecorderComponent.Tab.NORMAL) {
                reset(this.$normalVideo);
            } else {
                if (this.sourceMedia != null)
                    reset(this.$interpVideoR);
            }
            this.recorder = null;
        }

        this.$controls.html('');
    };

    RecorderComponent.prototype._onPageAnimate = function () {
        try {
            if (this.options.mode == RecorderComponent.Mode.PREVIEW && this.recordedMedia != null) {
                if (this.options.tab == RecorderComponent.Tab.INTERPRETATION && this.sourceMedia != null) {
                    return; //TODO interp preview/edit/trim
                }

                this._createPlayer(true);
            } else {
                if (this.options.tab == RecorderComponent.Tab.INTERPRETATION) {
                    if (this.sourceMedia != null) {
                        this._createPlayer();
                    } else {
                        return; // don't create the recorder
                    }
                }

                // $('.modal-content').css('height',$( window ).height()*0.9);
                // $('.modal-body').css('height','100%');
                // $('.modal-body').css('max-height','100%');
                //
                // var h = $( window ).height()*0.8 - 210;
                // var w = 4*h/3
                // console.log("height: " + h + "width: " + w );

                this._createRecorder();

                //console.log(this._getElement(Recorder.Binder.MODAL_DIALOG).find(".modal-dialog").height());
            }
        } catch (err) {
            console.error('%s: %s- err=%o', RecorderComponent.TAG, '_loadPage', err);
        }
    };

    RecorderComponent.prototype._loadPage = function () {
        if (this.options.tab == RecorderComponent.Tab.INTERPRETATION) {
            if (this.sourceMedia != null) {
                this.$interpSelect.parent().hide();
                this.$interpVideoP.show();
                this.$interpVideoR.show();
            } else {
                this.$interpSelect.parent().show();
                this.$interpVideoP.hide();
                this.$interpVideoR.hide();
            }
        }

        //console.log(this._getElement(Recorder.Binder.MODAL_DIALOG).find(".modal-dialog").height());

        this.$modalDialog.find('.modal-dialog').animate({
            //width: this.page == Recorder.Page.NORMAL ? "900px" : (this.sourceMedia != null ? "90%" : "900px") //FIXME use css classes

            // 220px are needed for all the other things in the window except the video. Then using 4*3 aspect ratio we
            // set the width of the pop-up
            width: 4 * ($(window).height() * 0.9 - 220) / 3
        }, {
            complete: this.bind__onPageAnimate
        });
    };

    RecorderComponent.prototype._onShownModal = function (e) {
        this._destroyPlayers();
        this._loadPage();
    };

    RecorderComponent.prototype._onHiddenModal = function (e) {
        this._destroyPlayers();

        if (this.paused)
            return;

        this._dispatch(RecorderComponent.Event.HIDDEN, {
            recorderComponent: this
        });
    };

    RecorderComponent.prototype._onShowTab = function (e) {
        this._destroyPlayers();

        this.options.tab = $(e.target).attr('href') == RecorderComponent.Binder.NORMAL
            ? RecorderComponent.Tab.NORMAL
            : RecorderComponent.Tab.INTERPRETATION;

        this._loadPage();
    };

    RecorderComponent.prototype._onShownTab = function (e) {
        this.$interpVideoP[0].load();
    };

    RecorderComponent.prototype._onBlurPlayerTitle = function (e) {
        console.log('updated title');
        this.recordedMedia.set('title', $(e.target).val());

        MediaFactory.edit(this.recordedMedia);
    };

    RecorderComponent.prototype._onClickInterpSelect = function (e) {
        e.preventDefault();

        MyFilesSelectorComponent.render({
            multiSelect: false,
            filter: {type: 1}
        }, function (e) {
            this.mfsCmp = e.myFilesSelectorComponent;
            this.mfsCmp.subscribe(MyFilesSelectorComponent.Event.DONE, (function (e) {
                this.mfsCmp.hide();
                this.setSourceMedia(e.media[0]);
            }).bind(this));
            this.mfsCmp.subscribe(MyFilesSelectorComponent.Event.HIDDEN, (function (e) {
                this.mfsCmp.destroy();
                this.show();
                this.paused = false;
            }).bind(this));

            this.paused = true;
            this.hide();
            this.mfsCmp.show();
        }.bind(this));
    };

    RecorderComponent.prototype.show = function () {
        this.$modalDialog.modal('show');
    };

    RecorderComponent.prototype.hide = function () {
        this.$modalDialog.modal('hide');
    };

    RecorderComponent.prototype.destroy = function () {
        this.rabbitmqWebStompService.unsubscribe(this.subscription, this.bind__onMessage);
        this.rabbitmqWebStompService.unsubscribe(null, this.bind__onConnect);

        this.$modalDialog.remove();
    };

    RecorderComponent.prototype._injectMedia = function (video, media, isRecording) {
        var options = {};

        options.resources = isRecording
                ? [media.get('source_resource')]
                : media.get('resources')

        dust.render('recorder_source', options, function (err, out) {
            video.html(out);
        });
    };

    RecorderComponent.prototype.setSourceMedia = function (media) {
        this.sourceMedia = media;

        if (this.sourceMedia != null) {
            this._injectMedia(this.$interpVideoP, this.sourceMedia, false);
        }
        this._destroyPlayers();
        this._loadPage();
    };

    RecorderComponent.prototype._togglePlayerTitle = function () {
        var title = this.recordedMedia != null ? this.recordedMedia.get('title') : '';

        if (this.options.tab == RecorderComponent.Tab.NORMAL) {
            this.$interpTitle.hide().val('');
            this.$normalTitle.toggle().val(title);
        } else {
            this.$normalTitle.hide().val('');
            this.$interpTitle.toggle().val(title);
        }
    };

    RecorderComponent.prototype.setRecordedMedia = function (media) {
        this.recordedMedia = media;

        if (this.recordedMedia != null) {
            this._injectMedia(this.options.tab == RecorderComponent.Tab.NORMAL
                    ? this.$normalVideo
                    : this.$interpVideoR,
                this.recordedMedia, true);
        }
        this._togglePlayerTitle();
    };

    RecorderComponent.prototype._getElement = function (binder) {
        return this.$container.find(binder);
    };

    RecorderComponent.render = function (options, callback) {
        var defaults = {
            $container: $('body'),
            tab: RecorderComponent.Tab.NORMAL,
            mode: RecorderComponent.Mode.RECORD,
            enableDoneAndPost: false
        };

        options = options || defaults;
        for (var o in defaults) {
            options[o] = typeof options[o] != 'undefined' ? options[o] : defaults[o];
        }

        dust.render('recorder', {}, function (err, out) {
            options.$container.append(out);

            var cmp = new RecorderComponent(options);
            callback.call(cmp, {
                recorderComponent: cmp
            });
        });
    };

    return RecorderComponent;
});
