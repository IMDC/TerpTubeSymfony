define([
    'core/threadManager'
], function(ThreadManager) {
    "use strict";

    var Thread = function(model, view, options) {
        console.log("%s: %s- model=%o, view=%o, options=%o", Thread.TAG, "constructor", model, view, options);

        this.model = model;
        this.view = view;
        this.options = options;

        this.videoSpeed = 0;
        this.player = null;
        this.keyPoints = [];
        this.playingKeyPoint = null;

        this.bind__onClickSubmit = this._onClickSubmit.bind(this);
        this.bind__onClickDelete = this._onClickDelete.bind(this);
        this.bind__onClickVideoSpeed = this._onClickVideoSpeed.bind(this);
        this.bind__onClickClosedCaptions = this._onClickClosedCaptions.bind(this);

        this.bind__onTimelineKeyPoint = this._onTimelineKeyPoint.bind(this);
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
            $(document).trigger($.Event(Thread.Event.SELECTION_TIMES, {
                selection: {
                    startTime: parseFloat(selection.minTime).toFixed(2),
                    endTime: parseFloat(selection.maxTime).toFixed(2)
                }
            }));
        }).bind(this));

        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OVER, function(e, keyPoint, coords) {
            console.log("%s: %s- keyPoint=%o, coords=%o", Thread.TAG, Player.EVENT_KEYPOINT_MOUSE_OVER, keyPoint, coords);

            $(document).trigger($.Event(Thread.Event.KEYPOINT_HOVER, {
                keyPoint: keyPoint
            }));

            // avoid animating when key points are overlapped and multiple invokes of this event are called
            if (!$("#threadReplyContainer").is(':animated')) {
                $("#threadReplyContainer").animate({
                    scrollTop: $(".post-container[data-pid=" + keyPoint.id + "]").position().top
                });
            }
        });

        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OUT, function(e, keyPoint) {
            console.log("%s: %s- keyPoint=%o", Thread.TAG, Player.EVENT_KEYPOINT_MOUSE_OUT, keyPoint);

            $(document).trigger($.Event(Thread.Event.KEYPOINT_HOVER, {
                keyPoint: keyPoint
            }));
        }.bind(this));

        $(this.player).on(Player.EVENT_KEYPOINT_CLICK, function(e, keyPoint, coords) {
            console.log("%s: %s- keyPoint=%o, coords=%o", Thread.TAG, Player.EVENT_KEYPOINT_CLICK, keyPoint, coords);

            $(document).trigger($.Event(Thread.Event.KEYPOINT_CLICK, {
                keyPoint: keyPoint
            }));
        });

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
                $(document).trigger($.Event(Thread.Event.DURATION, {duration: duration}));
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
        var mediaIds = [];
        this.view.getFormField("mediaIncluded").children().each(function(index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this.view.$submit.attr("disabled", true);
            this.view.mediaChooser.setMedia(mediaIds);
        }

        $(document).on("eventTimelineKeyPoint", this.bind__onTimelineKeyPoint);

        this.options.player.mediaElement.on("loadedmetadata", function(e) {
            $(document).trigger($.Event(Thread.Event.DURATION, {duration: e.target.duration}));
        });

        this._createPlayer();
    };

    Thread.prototype._onClickSubmit = function(e) {
        if (this.view.$form[0].checkValidity()) {
            $(e.target).button("loading");
        }
    };

    Thread.prototype._onClickDelete = function(e) {
        e.preventDefault();

        this.view.$delete.button("loading");
        ThreadManager.delete(this.model)
            .then(function(data, textStatus, jqXHR) {
                if (!data.wasDeleted) {
                    this.view.$deleteModal
                        .find(".modal-body")
                        .prepend("Something went wrong. Try again.");
                    this.view.$delete.button("reset");
                    return;
                }

                this.view.$deleteModal
                    .find(".modal-body")
                    .html("Topic deleted successfully.");

                window.location.assign(data.redirectUrl);
            }.bind(this),
            function(jqXHR, textStatus, errorThrown) {
                this.view.$container
                    .find(".modal-body")
                    .prepend("Something went wrong. Try again.");
                this.view.$delete.button("reset");
            }.bind(this));
    };

    // change the video speed when the slowdown button is clicked
    Thread.prototype._onClickVideoSpeed = function(e) {
        e.preventDefault();

        this.videoSpeed = (this.videoSpeed+1)%3;
        switch (this.videoSpeed) {
            case 0:
                this.options.player.mediaElement[0].playbackRate = 1.0;
                $("#videoSpeed img").attr("src", this.options.player.speedImages.normal);
                break;
            case 1:
                this.options.player.mediaElement[0].playbackRate = 2.0;
                $("#videoSpeed img").attr("src", this.options.player.speedImages.fast);
                break;
            case 2:
                this.options.player.mediaElement[0].playbackRate = 0.5;
                $("#videoSpeed img").attr("src", this.options.player.speedImages.slow);
                break;
            default:
                this.options.player.mediaElement[0].playbackRate = 1.0;
                $("#videoSpeed img").attr("src", this.options.player.speedImages.normal);
                break;
        }
    };

    // change the captioning display when you click the captioning button
    Thread.prototype._onClickClosedCaptions = function(e) {
        e.preventDefault();

        //$("#closed-caption-button img").attr("src", this.options.player.captionImages.off);
        //$("#closed-caption-button img").attr("src", this.options.player.captionImages.on);
    };

    Thread.prototype._updateForm = function() {
        var formField = this.view.getFormField("mediaIncluded");
        formField.html(
            this.view.mediaChooser.generateFormData(
                formField.data("prototype")
            )
        );
    };

    Thread.prototype._onSuccess = function(e) {
        this._updateForm();
        this.view.$submit.attr("disabled", false);
        if (this.view.mediaChooser.media.length > 0) {
            this.view.getFormField("title")
                .attr("required", false)
                .parent()
                .find("label")
                .removeClass("required");
        }
    };

    Thread.prototype._onReset = function(e) {
        this._updateForm();
        if (this.view.mediaChooser.media.length == 0) {
            this.view.getFormField("title")
                .attr("required", true)
                .parent()
                .find("label")
                .addClass("required");
        }
    };

    return Thread;
});
