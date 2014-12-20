define([
    'factory/threadFactory',
    'service',
    'service/keyPointService'
], function(ThreadFactory, Service, KeyPointService) {
    "use strict";

    var Thread = function(model, options) {
        console.log("%s: %s- model=%o, options=%o", Thread.TAG, "constructor", model, options);

        this.model = model;
        this.options = options;

        this.keyPointService = Service.get('keyPoint');

        this.videoSpeed = 0;
        this.player = null;
        this.keyPoints = [];
        this.playingKeyPoint = null;

        this.bind__onTimelineKeyPoint = this._onTimelineKeyPoint.bind(this);

        $tt._instances.push(this);
    };

    Thread.TAG = "Thread";
    Thread.DEFAULT_TEMPORAL_COMMENT_LENGTH = 3;

    Thread.Event = {
        DURATION: "eventDuration",
        SELECTION_TIMES: "eventSelectionTimes",
        KEYPOINT_HOVER: "eventKeyPointHover",
        KEYPOINT_CLICK: "eventKeyPointClick"
    };

    Thread.prototype._toggleTemporal = function(disabled, keyPoint) {
        this.player.pause();
        this.playingKeyPoint = null;
        if (!disabled && keyPoint && keyPoint.options.drawOnTimeLine) {
            this.player.setPlayHeadImage("");
            this.player.seek(keyPoint.startTime);
            this.player.setAreaSelectionStartTime(keyPoint.startTime);
            this.player.setAreaSelectionEndTime(keyPoint.endTime);
            this.player.setAreaSelectionEnabled(true);
        } else {
            this.player.setPlayHeadImage(this.options.player.playHeadImage);
            this.player.setAreaSelectionEnabled(false);
        }
    };

    Thread.prototype._createPlayer = function() {
        console.log("%s: %s", Thread.TAG, "_createPlayer");

        this.player = new Player(this.options.player.mediaElement, {
            areaSelectionEnabled: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
            audioBar: false,
            overlayControls: true,
            playHeadImage: this.options.player.playHeadImage,
            playHeadImageOnClick: (function() {
                var currentTime = this.player.getCurrentTime();
                var keyPoint = new KeyPoint(
                    -1, currentTime, currentTime + Thread.DEFAULT_TEMPORAL_COMMENT_LENGTH,
                    "", {drawOnTimeLine: true}
                );
                this._toggleTemporal(false, keyPoint);
            }).bind(this)
        });

        this.player.setKeyPoints(this.keyPoints);
        this.player.createControls();

        $(this.player).on(Player.EVENT_AREA_SELECTION_CHANGED, (function(e) {
            console.log("%s: %s", Thread.TAG, Player.EVENT_AREA_SELECTION_CHANGED);

            var selection = this.player.getAreaSelectionTimes();
            /*$(this.keyPointService).trigger($.Event(Thread.Event.SELECTION_TIMES, {
                selection: {
                    startTime: parseFloat(selection.minTime).toFixed(2),
                    endTime: parseFloat(selection.maxTime).toFixed(2)
                }
            }));*/
            this.keyPointService.dispatch('all', KeyPointService.Event.SELECTION_TIMES, {
                selection: {
                    startTime: parseFloat(selection.minTime).toFixed(2),
                    endTime: parseFloat(selection.maxTime).toFixed(2)
                }
            });
        }).bind(this));

        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OVER, function(e, keyPoint, coords) {
            console.log("%s: %s- keyPoint=%o, coords=%o", Thread.TAG, Player.EVENT_KEYPOINT_MOUSE_OVER, keyPoint, coords);

            /*$(this.keyPointService).trigger($.Event(Thread.Event.KEYPOINT_HOVER, {
                keyPoint: keyPoint
            }));*/
            this.keyPointService.dispatch(keyPoint.id, KeyPointService.Event.HOVER, {isMouseOver: true});

            // avoid animating when key points are overlapped and multiple invokes of this event are called
            if (!$("#threadReplyContainer").is(':animated')) {
                $("#threadReplyContainer").animate({
                    scrollTop: $(".post-container[data-pid=" + keyPoint.id + "]").position().top
                });
            }
        }.bind(this));

        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OUT, function(e, keyPoint) {
            console.log("%s: %s- keyPoint=%o", Thread.TAG, Player.EVENT_KEYPOINT_MOUSE_OUT, keyPoint);

            /*$(this.keyPointService).trigger($.Event(Thread.Event.KEYPOINT_HOVER, {
                keyPoint: keyPoint
            }));*/
            this.keyPointService.dispatch(keyPoint.id, KeyPointService.Event.HOVER, {isMouseOver: false});
        }.bind(this));

        $(this.player).on(Player.EVENT_KEYPOINT_CLICK, function(e, keyPoint, coords) {
            console.log("%s: %s- keyPoint=%o, coords=%o", Thread.TAG, Player.EVENT_KEYPOINT_CLICK, keyPoint, coords);

            /*$(this.keyPointService).trigger($.Event(Thread.Event.KEYPOINT_CLICK, {
                keyPoint: keyPoint
            }));*/
            this.keyPointService.dispatch(keyPoint.id, KeyPointService.Event.CLICK);
        }.bind(this));

        $(this.player).on(Player.EVENT_KEYPOINT_BEGIN, function(e, keyPoint) {
            console.log("%s: %s- keyPoint=%o", Thread.TAG, Player.EVENT_KEYPOINT_BEGIN, keyPoint);
        });

        $(this.player).on(Player.EVENT_KEYPOINT_END, function(e, keyPoint) {
            console.log("%s: %s- keyPoint=%o", Thread.TAG, Player.EVENT_KEYPOINT_END, keyPoint);

            if (this.playingKeyPoint && this.playingKeyPoint.id == keyPoint.id) {
                this.player.pause();
            }
        }.bind(this));
    };

    Thread.prototype._removeKeyPoint = function(keyPoint) {
        for (var kp in this.keyPoints) {
            if (this.keyPoints[kp].id == keyPoint.id) {
                this.keyPoints.splice(kp, 1);
                break;
            }
        }
    };

    Thread.prototype._onTimelineKeyPoint = function(e) {
        if (e.action == "add") {
            this._removeKeyPoint(e.keyPoint);

            if (e.keyPoint.startTime && e.keyPoint.endTime)
                this.keyPoints.push(e.keyPoint);

            var duration = this.options.player.mediaElement[0].duration;
            if (!isNaN(duration)) {
                //$(this.keyPointService).trigger($.Event(Thread.Event.DURATION, {duration: duration}));
                this.keyPointService.dispatch('all', KeyPointService.Event.DURATION, {duration: duration});
            }
        }

        if (e.action == "edit") {
            this._removeKeyPoint(e.keyPoint);
            this._toggleTemporal(!(e.keyPoint.startTime && e.keyPoint.endTime), e.keyPoint);
        }

        if (e.action == "remove") {
            this._toggleTemporal(true);
            this._removeKeyPoint(e.keyPoint);
        }

        if (e.action == "cancel") {
            this._toggleTemporal(true);
        }

        if (e.action == "click") {
            e.keyPoint.paintHighlightedTimeout = true;
            e.keyPoint.paintHighlighted = true;
            this.player.seek(e.keyPoint.startTime);
            this.player.redrawKeyPoints = true;
            this.player.repaint();

            // clear the highlighted comment after 3 seconds
            setTimeout((function() {
                e.keyPoint.paintHighlightedTimeout = false;
                e.keyPoint.paintHighlighted = false;
                this.player.redrawKeyPoints = true;
                this.player.repaint();
            }).bind(this), 3000);
        }

        if (e.action == "dblclick") {
            this.playingKeyPoint = e.keyPoint;
            this.player.seek(e.keyPoint.startTime);
            this.player.play();
        }

        if (e.action == "mouseenter") {
            // highlight the comment
            e.keyPoint.paintHighlighted = true;
            this.player.redrawKeyPoints = true;
            this.player.repaint();
        }

        if (e.action == "mouseleave") {
            if (!e.keyPoint.paintHighlightedTimeout) {
                e.keyPoint.paintHighlighted = false;
                this.player.redrawKeyPoints = true;
                this.player.repaint();
            }
        }
    };

    Thread.prototype.onViewLoaded = function() {
        //$(this.keyPointService).on("eventTimelineKeyPoint", this.bind__onTimelineKeyPoint);
        this.keyPointService.subscribe('all', this.bind__onTimelineKeyPoint);

        if (this.options.player) {
            this.options.player.mediaElement.on("loadedmetadata", function(e) {
                //$(this.keyPointService).trigger($.Event(Thread.Event.DURATION, {duration: e.target.duration}));
                this.keyPointService.dispatch('all', KeyPointService.DURATION, {duration: e.target.duration});
            });

            this._createPlayer();
        }
    };

    Thread.prototype.delete = function(e) {
        return ThreadFactory.delete(this.model)
            .done(function(data) {
                window.location.assign(data.redirectUrl);
            }.bind(this));
    };

    Thread.prototype.adjustVideoSpeed = function() {
        this.videoSpeed = (this.videoSpeed+1)%3;
        switch (this.videoSpeed) {
            case 0:
                this.options.player.mediaElement[0].playbackRate = 1.0;
                return this.options.player.speedImages.normal;
            case 1:
                this.options.player.mediaElement[0].playbackRate = 2.0;
                return this.options.player.speedImages.fast;
            case 2:
                this.options.player.mediaElement[0].playbackRate = 0.5;
                return this.options.player.speedImages.slow;
            default:
                this.options.player.mediaElement[0].playbackRate = 1.0;
                return this.options.player.speedImages.normal;
        }
    };

    Thread.prototype.toggleClosedCaptions = function() {
        //$("#closed-caption-button img").attr("src", this.options.player.captionImages.off);
        //$("#closed-caption-button img").attr("src", this.options.player.captionImages.on);
    };

    return Thread;
});
