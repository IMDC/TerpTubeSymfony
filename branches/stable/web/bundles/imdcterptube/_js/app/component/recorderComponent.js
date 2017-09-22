define([
    'core/subscriber',
    'service',
    'component/myFilesSelectorComponent',
    'model/mediaModel',
    'factory/mediaFactory',
    'factory/myFilesFactory',
    'service/rabbitmqWebStompService',
    'core/helper',
    'extra'
], function (Subscriber, Service, MyFilesSelectorComponent, MediaModel, MediaFactory, MyFilesFactory,
             RabbitmqWebStompService, Helper) {
    'use strict';

    var RecorderComponent = function (options) {
        Subscriber.prototype.constructor.apply(this);

        this.options = options;
        this.isOnNormalTab = this.options.tab == RecorderComponent.Tab.NORMAL;
        this.isOnInterpTab = this.options.tab == RecorderComponent.Tab.INTERPRETATION;
        this.isInRecordMode = this.options.mode == RecorderComponent.Mode.RECORD;
        this.isInEditMode = this.options.mode == RecorderComponent.Mode.PREVIEW;
        this.player = null;
        this.recorder = null;
        this.currentRecording = null;
        this.currentTrim = null;
        this.sourceMedia = null;
        this.recordedMedia = null;
        this.messages = [];
        this.rabbitmqWebStompService = Service.get('rabbitmqWebStomp');
        this.isDonePostponed = false;
        this.doPost = false;

        this.forwardButton = '<button class="forwardButton"></button>';
        this.doneButton = '<button class="doneButton"></button>';
        this.doneAndPostButton = '<button class="doneAndPostButton"></button>';
        this.backButton = '<button class="backButton"></button>';

        this.bind__onShownModal = this._onShownModal.bind(this);
        this.bind__onShowModal = this._onShowModal.bind(this);
        this.bind__onHiddenModal = this._onHiddenModal.bind(this);
        this.bind__onShowTab = this._onShowTab.bind(this);
        this.bind__onShownTab = this._onShownTab.bind(this);
        this.bind__onBlurPlayerTitle = this._onBlurPlayerTitle.bind(this);
        this.bind__onClickInterpSelect = this._onClickInterpSelect.bind(this);
        this.bind__onPageAnimate = this._onPageAnimate.bind(this);
        this.bind__onRecordingStarted = this._onRecordingStarted.bind(this);
        this.bind__onRecordingStopped = this._onRecordingStopped.bind(this);
        this.bind__onClickTrim = this._onClickTrim.bind(this);
        this.bind__onClickBack = this._onClickBack.bind(this);
        this.bind__onClickDone = this._onClickDone.bind(this);
        this.bind__onClickDoneAndPost = this._onClickDoneAndPost.bind(this);

        this.$container = this.options.$container;
        this.$modalDialog = this.$container.find(RecorderComponent.Binder.MODAL_DIALOG);
        this.$tabPanes = this.$container.find(RecorderComponent.Binder.TAB_PANES);
        this.$containerRecord = this.$container.find(RecorderComponent.Binder.CONTAINER_RECORD);
        this.$normalTitle = this.$container.find(RecorderComponent.Binder.NORMAL_TITLE);
        this.$normalVideo = this.$container.find(RecorderComponent.Binder.NORMAL_VIDEO);
        this.$interpSelect = this.$container.find(RecorderComponent.Binder.INTERP_SELECT);
        this.$interpMain = this.$container.find(RecorderComponent.Binder.INTERP_MAIN);
        this.$interpVideoP = this.$container.find(RecorderComponent.Binder.INTERP_VIDEO_P);
        this.$interpTitle = this.$container.find(RecorderComponent.Binder.INTERP_TITLE);
        this.$interpVideoR = this.$container.find(RecorderComponent.Binder.INTERP_VIDEO_R);
        this.$controls = this.$container.find(RecorderComponent.Binder.CONTROLS);
        this.$containerUpload = this.$container.find(RecorderComponent.Binder.CONTAINER_UPLOAD);

        var tab = this.isOnNormalTab
            ? RecorderComponent.Binder.NORMAL
            : RecorderComponent.Binder.INTERP;
        this.$modalDialog.find('a[href!="' + tab + '"]').parent().removeClass('active');
        this.$modalDialog.find('a[href="' + tab + '"]').parent().addClass('active');
        this.$modalDialog.find('.tab-pane:not("' + tab + '")').removeClass('active');
        this.$modalDialog.find('.tab-pane' + tab).addClass('active');
        this.$modalDialog.modal({backdrop: 'static', show: false});

        this.$modalDialog.on('shown.bs.modal', this.bind__onShownModal);
        this.$modalDialog.on('show.bs.modal', this.bind__onShowModal);
        this.$modalDialog.on('hidden.bs.modal', this.bind__onHiddenModal);
        this.$tabPanes.on('show.bs.tab', this.bind__onShowTab);
        this.$tabPanes.on('shown.bs.tab', this.bind__onShownTab);
        this.$interpSelect.on('click', this.bind__onClickInterpSelect);
        // prevent media renaming if in record mode
        if (this.isInEditMode) {
            this.$normalTitle.blur(this.bind__onBlurPlayerTitle);
            this.$interpTitle.blur(this.bind__onBlurPlayerTitle);
        }

        this._clearCurrents();
        this._setupRabbitmq();

        //TODO interp preview/edit/trim
        if (this.isInEditMode) {
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
        INTERP_MAIN: '.recorder-interp-main',
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

    RecorderComponent.prototype._setupRabbitmq = function () {
        // listen for status updates
        this.bind__onMessage = function (e) {
            console.log(e.message);

            // store the messages, since a request (_onRecordingSuccess) may not have completed yet
            this.messages.push(e.message);

            this._checkMessages();
        }.bind(this);
        this.bind__onConnect = function (e) {
            this.subscription = this.rabbitmqWebStompService.subscribe(
                '/exchange/'+window.parameters.prefix+'entity-status',
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
    };

    RecorderComponent.prototype._cleanupRabbitmq = function () {
        this.rabbitmqWebStompService.unsubscribe(this.subscription, this.bind__onMessage);
        this.rabbitmqWebStompService.unsubscribe(null, this.bind__onConnect);
    };

    RecorderComponent.prototype._createPlayer = function (inPreviewMode, wasRecording) {
        console.log('%s: %s', RecorderComponent.TAG, '_createPlayer');

        inPreviewMode = typeof inPreviewMode != 'undefined' ? inPreviewMode : false;
        wasRecording = typeof wasRecording != 'undefined' ? wasRecording : false;

        var forwardButtons = [];
        var forwardFunctions = [];
        if (this.isOnNormalTab || inPreviewMode) {
            forwardButtons.push(this.doneButton);
            forwardFunctions.push(inPreviewMode ? this.bind__onClickTrim : this.bind__onClickDone);
        }
        if (this.options.enableDoneAndPost && inPreviewMode) {
            forwardButtons.push(this.doneAndPostButton);
            forwardFunctions.push(this.bind__onClickDoneAndPost);
        }

        var backButtons;
        var backFunctions;
        if (inPreviewMode && wasRecording) {
            backButtons = [this.backButton];
            backFunctions = [this.bind__onClickBack];
        }

        var container = this.isOnNormalTab
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

    RecorderComponent.prototype._onClickTrim = function (e) {
        console.log('%s: %s', RecorderComponent.TAG, '_onClickTrim');
        e.preventDefault();

        var previousMinMaxTimes = this.recorder.getCurrentMinMaxTime();
        var currentMinMaxTimes = this.recorder.getAreaSelectionTimes();
        this.recorder.setCurrentMinMaxTime(currentMinMaxTimes.minTime, currentMinMaxTimes.maxTime);

        console.log('Current Min/Max Times %s %s', currentMinMaxTimes.minTime, currentMinMaxTimes.maxTime);
        console.log('Cutting to Min/Max Times %s %s', currentMinMaxTimes.minTime - previousMinMaxTimes.minTime,
            currentMinMaxTimes.maxTime - previousMinMaxTimes.minTime);

        // a trim request will be attempted only if the recording is successfully uploaded
        this.currentTrim = {
            startTime: currentMinMaxTimes.minTime - previousMinMaxTimes.minTime,
            endTime: currentMinMaxTimes.maxTime - previousMinMaxTimes.minTime
        };

        //cut should always also call the done function
        this._onClickDone(e);
    };

    RecorderComponent.prototype._onClickBack = function (e) {
        console.log('%s: %s', RecorderComponent.TAG, '_onClickBack');
        e.preventDefault();

        this._destroyPlayers();

        // Go back to recording
        this._clearCurrents();
        this.setRecordedMedia(null);
        this.options.mode = RecorderComponent.Mode.RECORD;
        if (this.isOnInterpTab)
            this._createPlayer();
        this._createRecorder();
    };

    RecorderComponent.prototype._createRecorder = function () {
        console.log('%s: %s', RecorderComponent.TAG, '_createRecorder');

        var container = this.isOnNormalTab
            ? this.$normalVideo
            : this.$interpVideoR;

        this.recorder = new Player(container, {
            areaSelectionEnabled: false,
            audioBar: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
            controlBarElement: this.$controls,
            type: Player.DENSITY_BAR_TYPE_RECORDER,
            volumeControl: false,
            maxRecordingTime: RecorderComponent.MAX_RECORDING_TIME
        });
        $(this.recorder).on(Player.EVENT_RECORDING_STARTED, this.bind__onRecordingStarted);
        $(this.recorder).on(Player.EVENT_RECORDING_STOPPED, this.bind__onRecordingStopped);
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

        this._clearCurrents();

        if (this.isOnInterpTab) {
            this.currentRecording.sourceStartTime = this.player.getCurrentTime();
            this.player.setControlsEnabled(false);
            this.player.play();
        }
    };

    RecorderComponent.prototype._onRecordingStopped = function () {
        this.currentRecording.video = this.recorder.recordVideo.getBlob();
//        this.currentRecording.audio = this.recorder.recordAudio.getBlob();

        if (this.isOnInterpTab) {
            this.player.pause();
            this.player.setControlsEnabled(true);
        }

        this._destroyPlayers();

        this._injectCurrentRecording(this.isOnNormalTab
            ? this.$normalVideo
            : this.$interpVideoR);

        if (this.isOnInterpTab)
            this._createPlayer();
        this.options.mode = RecorderComponent.Mode.PREVIEW;
        this._createPlayer(true, true);
    };

    RecorderComponent.prototype._checkMessages = function () {
        if (!this.tempMedia)
            return;

        while (this.messages.length > 0) {
            var message = this.messages.pop();

            if (message.who.indexOf('\MultiplexConsumer') > -1 &&
                (message.what.indexOf('\Media') > -1 || message.what.indexOf('\Interpretation')) &&
                message.identifier == this.tempMedia.get('id')
            ) {
                if (message.status != 'Done') {
                    this.$containerUpload.find('label').eq(0).html(message.status + '...');
                } else {
                    console.log('done');
                    this.$containerUpload.find('label').eq(0).html('Cleaning up...');

                    MediaFactory.get(this.tempMedia.get('id'))
                        .done(function (data) {
                            this.tempMedia = null;
                            this.setRecordedMedia(data.media);

                            // was waiting for us?
                            if (this.isDonePostponed) {
                                setTimeout(function () {
                                    this._dispatchDone();
                                }.bind(this), 2000);
                            }
                        }.bind(this))
                        .fail(function (data) {
                            //TODO
                        });
                }
            }
        }
    };

    RecorderComponent.prototype._addRecording = function () {
        if (this.currentRecording.video == null && this.currentRecording.audio == null)
            return null;

        var params = {
            video: this.currentRecording.video,
//            audio: this.currentRecording.audio,
            title: this._getCurrentTitleElement().val()
        };

        if (this.isOnInterpTab) {
            params.isInterpretation = true;
            params.sourceStartTime = this.currentRecording.sourceStartTime;
            params.sourceId = this.sourceMedia.get('id');
        }

        //TODO progress updater
        this.$containerUpload.find('label').eq(0).html('Uploading recording...');

        return MyFilesFactory.addRecording(params)
            .progress(function (percent) {
                Helper.updateProgressBar(this.$containerUpload, percent);
            }.bind(this))
            .done(function (data) {
                console.log(data.media);

                if (data.media.get('state') == 2) {
                    this.setRecordedMedia(data.media);
                } else {
                    console.log('waiting for multiplex consumer');

                    //TODO progress updater
                    this.$containerUpload.find('.progress-bar').eq(0)
                        .addClass('progress-bar-striped active')
                        .html('');
                    this.$containerUpload.find('label').eq(0).html('Processing...');

                    this.tempMedia = data.media;
                    this._checkMessages();
                }
            }.bind(this))
            .fail(function () {
                //TODO
            });
    };

    RecorderComponent.prototype._trim = function (media) {
        if (this.currentTrim.startTime == null || this.currentTrim.endTime == null)
            return null;

        //TODO progress updater
        this.$containerUpload.find('label').eq(0).html('Queuing trim...');

        return MediaFactory.trim(media, this.currentTrim.startTime, this.currentTrim.endTime)
            .fail(function () {
                //TODO
            });
    };

    RecorderComponent.prototype._dispatchDone = function () {
        this._dispatch(RecorderComponent.Event.DONE, {
            media: this.recordedMedia,
            doPost: this.doPost,
            recorderComponent: this
        });
    };

    RecorderComponent.prototype._onClickDone = function (e) {
        console.log('%s: %s', RecorderComponent.TAG, '_onClickDone');
        e.preventDefault();

        this.$containerRecord.hide();
        this.$containerUpload.show();
        this._destroyPlayers();

        var done = function () {
            //TODO progress updater
            this.$containerUpload.find('label').eq(0).html('Cleaning up...');

            // if recorded media is not set, then
            // we're waiting to hear from the multiplex consumer
            if (this.recordedMedia) {
                setTimeout(function () {
                    this._dispatchDone();
                }.bind(this), 2000);
            } else {
                this.isDonePostponed = true;
            }
        }.bind(this);

        var arDone = function (media) {
            // send trim request, if any
            var tp = this._trim(media);
            if (!tp) {
                // nothing to trim, so finish up
                done();
                return;
            }

            tp.done(function (data) {
                // finish up
                done();
            });
        }.bind(this);

        var arp = this._addRecording();
        if (!arp) {
            // no recording to upload. go on to trim if in edit mode and recoding is set
            if (this.isInEditMode && this.recordedMedia) {
                //TODO progress updater
                Helper.updateProgressBar(this.$containerUpload, 100);
                this.$containerUpload.find('.progress-bar').eq(0)
                    .addClass('progress-bar-striped active')
                    .html('');
                arDone(this.recordedMedia);
            }
            return;
        }

        arp.done(function (data) {
            // go on to trim
            arDone(data.media);
        });
    };

    RecorderComponent.prototype._onClickDoneAndPost = function (e) {
        this.doPost = true;
        this._onClickDone(e);
    };

    RecorderComponent.prototype._clearCurrents = function () {
        this.currentRecording = {video: null, audio: null, sourceStartTime: null};
        this.currentTrim = {startTime: null, endTime: null};
    };

    RecorderComponent.prototype._destroyPlayers = function () {
        console.log('%s: %s', RecorderComponent.TAG, '_destroyPlayers');

        var reset = function ($normal) {
            var $old = $normal.parent();
            /*$.each($normal.find('source'), function (index, element) {
             var e = $(element);
             if (e.attr('src').indexOf('blob:') == 0)
             URL.revokeObjectURL(e.attr('src'));
             });*/
            $normal.removeAttr('src');
            $old.hide();
            $old.after($normal.detach());
            $old.remove();
        }.bind(this);

        if (this.player != null) {
            this.player.destroyRecorder();
            if (this.isOnNormalTab) {
                reset(this.$normalVideo);
            } else {
                if (this.sourceMedia != null)
                    reset(this.$interpVideoP);
            }
            this.player = null;
        }

        if (this.recorder != null) {
            this.recorder.destroyRecorder();
            if (this.isOnNormalTab) {
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
            if (this.isInEditMode && this.recordedMedia != null) {
                if (this.isOnInterpTab && this.sourceMedia != null) {
                    return; //TODO interp preview/edit/trim
                }

                this._createPlayer(true);
            } else {
                if (this.isOnInterpTab) {
                    if (this.sourceMedia != null) {
                        this._createPlayer();
                    } else {
                        return; // don't create the recorder
                    }
                }

                this._createRecorder();
            }
        } catch (err) {
            console.error('%s: %s- err=%o', RecorderComponent.TAG, '_loadPage', err);
        }
    };

    RecorderComponent.prototype._loadPage = function () {
        if (this.isOnInterpTab) {
            if (this.sourceMedia != null) {
                this.$interpSelect.parent().hide();
                this.$interpMain.show();
            } else {
                this.$interpSelect.parent().show();
                this.$interpMain.hide();
            }
        }

        this.$modalDialog.find('.modal-dialog').animate({
            //width: this.page == Recorder.Page.NORMAL ? "900px" : (this.sourceMedia != null ? "90%" : "900px") //FIXME use css classes

            // 256px are needed for all the other things in the window except the video. Then using 4*3 aspect ratio we
            // set the width of the pop-up
            width: 4 * ($(window).height() * 0.9 - 256) / 3
        }, {
            complete: this.bind__onPageAnimate
        });
    };

    RecorderComponent.prototype._onShownModal = function (e) {
        this._destroyPlayers();
        this._loadPage();
    };
    RecorderComponent.prototype._onShowModal = function (e) {
	// Width needs to be kept consistent with the loadPage modal animation width to avoid too many sizings of the modal
	 this.$modalDialog.find('.modal-dialog').width(4 * ($(window).height() * 0.9 - 256) / 3);
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

        this.isOnNormalTab = this.options.tab == RecorderComponent.Tab.NORMAL;
        this.isOnInterpTab = this.options.tab == RecorderComponent.Tab.INTERPRETATION;

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
        this._clearCurrents();
        this._cleanupRabbitmq();

        this.$modalDialog.remove();
    };

    RecorderComponent.prototype._injectCurrentRecording = function (element) {
        //FIXME only video will play audio would need its own element and then playback needs to synced
        //TODO test with firefox
        var options = {
            resources: [
                {web_path: URL.createObjectURL(this.currentRecording.video)},
//                {web_path: URL.createObjectURL(this.currentRecording.audio)}
            ]
        };

        dust.render('recorder_source', options, function (err, out) {
            element.html(out);
        });
    };

    RecorderComponent.prototype._injectMedia = function (element, media) {
        var options = {
            resources: this.isInRecordMode
                ? [media.get('source_resource')]
                : media.get('resources')
        };

        dust.render('recorder_source', options, function (err, out) {
            element.html(out);
        });
    };

    RecorderComponent.prototype._getCurrentTitleElement = function () {
        return this.isOnNormalTab ? this.$normalTitle : this.$interpTitle;
    };

    RecorderComponent.prototype.setSourceMedia = function (media) {
        this.sourceMedia = media;

        if (this.sourceMedia != null) {
            this._injectMedia(this.$interpVideoP, this.sourceMedia);
            this._getCurrentTitleElement().val(this.sourceMedia.get('title'));
        }

        this._destroyPlayers();
        this._loadPage();
    };

    RecorderComponent.prototype.setRecordedMedia = function (media) {
        this.recordedMedia = media;

        if (this.recordedMedia != null) {
            this._injectMedia(this.isOnNormalTab
                    ? this.$normalVideo
                    : this.$interpVideoR,
                this.recordedMedia);

            this._getCurrentTitleElement().val(this.recordedMedia.get('title'));
        }
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
