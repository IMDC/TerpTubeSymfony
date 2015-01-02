define([
    'model/model'
], function (Model) {
    'use strict';

    var ViewView = function (controller, options) {
        this.controller = controller;
        this.options = options;

        this.player = null;
        this.playingKeyPoint = null;

        this.bind__onClickVideoSpeed = this._onClickVideoSpeed.bind(this);
        this.bind__onClickClosedCaptions = this._onClickClosedCaptions.bind(this);
        this.bind__onAreaSelectionChangedKeyPoint = this._onAreaSelectionChangedKeyPoint.bind(this);
        this.bind__onMouseOverKeyPoint = this._onMouseOverKeyPoint.bind(this);
        this.bind__onMouseOutKeyPoint = this._onMouseOutKeyPoint.bind(this);
        this.bind__onClickKeyPoint = this._onClickKeyPoint.bind(this);
        this.bind__onEndKeyPoint = this._onEndKeyPoint.bind(this);
        this.bind__onModelChange = this._onModelChange.bind(this);

        this.$container = options.container;

        $('#videoSpeed').on('click', this.bind__onClickVideoSpeed);
        $('#closedCaptions').on('click', this.bind__onClickClosedCaptions);

        if (this.options.player) {
            this.options.player.mediaElement.on("loadedmetadata", function (e) {
                this.controller.updateKeyPointDuration(e.target.duration);
            });

            this._createPlayer();
        }

        this.controller.model.subscribe(Model.Event.CHANGE, this.bind__onModelChange);

        $tt._instances.push(this);
    };

    ViewView.TAG = 'ThreadViewView';
    ViewView.DEFAULT_TEMPORAL_COMMENT_LENGTH = 3;

    ViewView.Binder = {};

    ViewView.prototype._createPlayer = function () {
        console.log("%s: %s", ViewView.TAG, "_createPlayer");

        this.player = new Player(this.options.player.mediaElement, {
            areaSelectionEnabled: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
            audioBar: false,
            overlayControls: true,
            playHeadImage: this.options.player.playHeadImage,
            playHeadImageOnClick: (function () {
                var currentTime = this.player.getCurrentTime();
                var keyPoint = new KeyPoint(
                    -1, currentTime, currentTime + ViewView.DEFAULT_TEMPORAL_COMMENT_LENGTH,
                    "", {drawOnTimeLine: true}
                );
                this._toggleTemporal(false, keyPoint);
            }).bind(this)
        });

        $(this.player).on(Player.EVENT_AREA_SELECTION_CHANGED, this.bind__onAreaSelectionChangedKeyPoint);
        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OVER, this.bind__onMouseOverKeyPoint);
        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OUT, this.bind__onMouseOutKeyPoint);
        $(this.player).on(Player.EVENT_KEYPOINT_CLICK, this.bind__onClickKeyPoint);
        $(this.player).on(Player.EVENT_KEYPOINT_END, this.bind__onEndKeyPoint);

        this.player.createControls();
    };

    ViewView.prototype._onAreaSelectionChangedKeyPoint = function (e) {
        console.log("%s: %s", ViewView.TAG, Player.EVENT_AREA_SELECTION_CHANGED);

        this.controller.updateKeyPointSelectionTimes(this.player.getAreaSelectionTimes());
    };

    ViewView.prototype._onMouseOverKeyPoint = function (e, keyPoint, coords) {
        console.log("%s: %s- keyPoint=%o, coords=%o", ViewView.TAG, Player.EVENT_KEYPOINT_MOUSE_OVER, keyPoint, coords);

        this.controller.hoverKeyPoint(keyPoint.id, {isMouseOver: true});

        // avoid animating when key points are overlapped and multiple invokes of this event are called
        if (!$("#threadReplyContainer").is(':animated')) {
            $("#threadReplyContainer").animate({
                scrollTop: $(".post-container[data-pid=" + keyPoint.id + "]").position().top
            });
        }
    };

    ViewView.prototype._onMouseOutKeyPoint = function (e, keyPoint) {
        console.log("%s: %s- keyPoint=%o", ViewView.TAG, Player.EVENT_KEYPOINT_MOUSE_OUT, keyPoint);

        this.controller.hoverKeyPoint(keyPoint.id, {isMouseOver: false});
    };

    ViewView.prototype._onClickKeyPoint = function (e, keyPoint, coords) {
        console.log("%s: %s- keyPoint=%o, coords=%o", ViewView.TAG, Player.EVENT_KEYPOINT_CLICK, keyPoint, coords);

        this.controller.clickKeyPoint(keyPoint.id);
    };

    ViewView.prototype._onEndKeyPoint = function (e, keyPoint) {
        console.log("%s: %s- keyPoint=%o", ViewView.TAG, Player.EVENT_KEYPOINT_END, keyPoint);

        if (this.playingKeyPoint && this.playingKeyPoint.id == keyPoint.id) {
            this.player.pause();
        }
    };

    ViewView.prototype._toggleTemporal = function (disabled, keyPoint) {
        this.player.pause();
        this.playingKeyPoint = null;
        if (!disabled && keyPoint) {
            this.player.setPlayHeadImage('');
            this.player.seek(keyPoint.startTime);
            this.player.setAreaSelectionStartTime(keyPoint.startTime);
            this.player.setAreaSelectionEndTime(keyPoint.endTime);
            this.player.setAreaSelectionEnabled(true);
        } else {
            this.player.setPlayHeadImage(this.options.player.playHeadImage);
            this.player.setAreaSelectionEnabled(false);
        }
    };

    ViewView.prototype._onModelChange = function (e) {
        this.player.setKeyPoints(e.model.get('keyPoints', []));

        this.controller.updateKeyPointDuration(this.options.player.mediaElement[0].duration);

        // check if a key point was changed
        if (e.keyPath.indexOf('keyPoints.') == 0) { // trailing dot is for an element of the array/object
            // get the key point, not just the property of the key point that changed
            var keyPoint = e.model.get(e.keyPath.substr(0, e.keyPath.lastIndexOf('.')));

            if (keyPoint instanceof KeyPoint) {
                if (keyPoint.isEditing) {
                    this._toggleTemporal(!(keyPoint.startTime && keyPoint.endTime), keyPoint);
                } else {
                    this._toggleTemporal(true);
                }

                if (keyPoint.isSeeking) {
                    keyPoint.paintHighlightedTimeout = true;
                    keyPoint.paintHighlighted = true;
                    this.player.seek(keyPoint.startTime);
                    this.player.redrawKeyPoints = true;

                    // clear the highlighted comment after 3 seconds
                    setTimeout((function () {
                        keyPoint.paintHighlightedTimeout = false;
                        keyPoint.paintHighlighted = false;
                        this.player.redrawKeyPoints = true;
                    }).bind(this), 3000);
                }

                if (keyPoint.isPlaying) {
                    this.playingKeyPoint = keyPoint;
                    this.player.seek(keyPoint.startTime);
                    this.player.play();
                } else if (keyPoint.isHovering) {
                    // highlight the comment
                    keyPoint.paintHighlighted = true;
                    this.player.redrawKeyPoints = true;
                } else {
                    if (!keyPoint.paintHighlightedTimeout) {
                        keyPoint.paintHighlighted = false;
                        this.player.redrawKeyPoints = true;
                    }
                }

                this.player.repaint();
            }
        }
    };

    // change the video speed when the slowdown button is clicked
    ViewView.prototype._onClickVideoSpeed = function (e) {
        e.preventDefault();

        var rate = this.controller.adjustVideoSpeed();
        this.options.player.mediaElement[0].playbackRate = rate.value;
        $('#videoSpeed img').attr('src', rate.image);
    };

    // change the captioning display when you click the captioning button
    ViewView.prototype._onClickClosedCaptions = function (e) {
        e.preventDefault();

        var image = $('#closed-caption-button img').attr('src');
        image = image == this.options.player.captionImages.on
            ? this.options.player.captionImages.off
            : this.options.player.captionImages.on;
        $('#closed-caption-button img').attr('src', image);
    };

    return ViewView;
});
