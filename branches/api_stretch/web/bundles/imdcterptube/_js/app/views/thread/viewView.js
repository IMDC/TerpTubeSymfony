define([
    'model/model',
    'core/helper',
    'bootstrap'
], function (Model, Helper, bootstrap) {
    'use strict';

    var ViewView = function (controller, options) {
        this.controller = controller;
        this.options = options;

        this.player = null;
        this.playingKeyPoint = null;

        this.bind__onClickVideoSpeed = this._onClickVideoSpeed.bind(this);
        this.bind__onClickClosedCaptions = this._onClickClosedCaptions.bind(this);
        this.bind__onAreaSelectionChanged = this._onAreaSelectionChanged.bind(this);
        this.bind__onMouseOverKeyPoint = this._onMouseOverKeyPoint.bind(this);
        this.bind__onMouseOutKeyPoint = this._onMouseOutKeyPoint.bind(this);
        this.bind__onRightClickKeyPoint = this._onRightClickKeyPoint.bind(this);
        this.bind__onEndKeyPoint = this._onEndKeyPoint.bind(this);
        this.bind__onPlaybackStarted = this._onPlaybackStarted.bind(this);
        this.bind__onPlaybackStopped = this._onPlaybackStopped.bind(this);
        this.bind__onSeek = this._onSeek.bind(this);
        this.bind__onModelChange = this._onModelChange.bind(this);

        this.$container = options.container;
        this.$postContainer = options.post_container;
        var $videos = this.$container.find(ViewView.Binder.MEDIA_ELEMENT + ' video');
        this.$video = $videos.eq($videos.length == 2 ? 1 : 0);
        this.$pipVideo = $videos.eq($videos.length == 2 ? 0 : 1);

        $('#videoSpeed').on('click', this.bind__onClickVideoSpeed);
        $('#closedCaptions').on('click', this.bind__onClickClosedCaptions);

        if (this.controller.model.get('type') === 1 && this.controller.model.get('ordered_media').length > 0) {
            this.$video.on('loadedmetadata', function (e) {
                this.controller.updateKeyPointDuration(e.target.duration);
            }.bind(this));

            this._createPlayer();

            var media = this.controller.model.get('ordered_media.0');
            if (media && media.is_interpretation) {
                this.$pipVideo[0].currentTime = media.source_start_time;
            }
        }

        this.controller.model.subscribe(Model.Event.CHANGE, this.bind__onModelChange);

        //TODO model: get collection data as json array
        var posts = [];
        _.each(this.controller.model.get('posts'), function (element, index, list) {
            posts.push(element.data);
        });

        dust.render('thread_view_posts', {posts: posts}, function (err, out) {
            this.$postContainer.html(out);
            _.each(this.controller.model.get('posts'), function (element, index, list) {
                bootstrap(element, 'post', 'post/view', {});
            });
        }.bind(this));

        $tt._instances.push(this);
    };

    ViewView.TAG = 'ThreadViewView';
    ViewView.DEFAULT_TEMPORAL_COMMENT_LENGTH = 3;

    ViewView.Binder = {
        MEDIA_ELEMENT: '.thread-media-element'
    };

    ViewView.prototype._createPlayer = function () {
        console.log("%s: %s", ViewView.TAG, "_createPlayer");

        this.player = new Player(this.$video, {
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

        $(this.player).on(Player.EVENT_AREA_SELECTION_CHANGED, this.bind__onAreaSelectionChanged);
        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OVER, this.bind__onMouseOverKeyPoint);
        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OUT, this.bind__onMouseOutKeyPoint);
        $(this.player).on(Player.EVENT_KEYPOINT_RIGHT_CLICK, this.bind__onRightClickKeyPoint);
        $(this.player).on(Player.EVENT_KEYPOINT_END, this.bind__onEndKeyPoint);
        $(this.player).on(Player.EVENT_PLAYBACK_STARTED, this.bind__onPlaybackStarted);
        $(this.player).on(Player.EVENT_PLAYBACK_STOPPED, this.bind__onPlaybackStopped);
        $(this.player).on(Player.EVENT_SEEK, this.bind__onSeek);

        this.player.createControls();
    };

    ViewView.prototype._onAreaSelectionChanged = function (e) {
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

    ViewView.prototype._onRightClickKeyPoint = function (e, keyPoint, coords) {
        console.log("%s: %s- keyPoint=%o, coords=%o", ViewView.TAG, Player.EVENT_KEYPOINT_RIGHT_CLICK, keyPoint, coords);

        this.controller.rightClickKeyPoint(keyPoint.id);
    };

    ViewView.prototype._onEndKeyPoint = function (e, keyPoint) {
        console.log("%s: %s- keyPoint=%o", ViewView.TAG, Player.EVENT_KEYPOINT_END, keyPoint);

        if (this.playingKeyPoint && this.playingKeyPoint.id == keyPoint.id) {
            this.player.pause();
        }
    };

    ViewView.prototype._onPlaybackStarted = function (e) {
        console.log("%s: %s", ViewView.TAG, Player.EVENT_PLAYBACK_STARTED);

        if (this.$pipVideo.length == 0)
            return;

        this.$pipVideo[0].play();
    };

    ViewView.prototype._onPlaybackStopped = function (e) {
        console.log("%s: %s", ViewView.TAG, Player.EVENT_PLAYBACK_STOPPED);

        if (this.$pipVideo.length == 0)
            return;

        this.$pipVideo[0].pause();
    };

    ViewView.prototype._onSeek = function (e, time) {
        console.log("%s: %s- time=%s", ViewView.TAG, Player.EVENT_SEEK, time);

        if (this.$pipVideo.length == 0)
            return;

        this.$pipVideo[0].currentTime = Math.min(
            this.player.getCurrentTime() + this.controller.model.get('ordered_media.0.source_start_time'),
            this.$pipVideo[0].duration
        );
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
        if (e.keyPath.indexOf('posts.') == 0 && e.view) { // trailing dot is for an element of the array/object
            var post = e.model.get(e.keyPath);

            if (e.view == 'new') {
                dust.render('post_new', post.data, function (err, out) {
                    var $parentPost = this.$postContainer.find('.post-container[data-pid="' + post.get('parent_post_id') + '"]');
                    $parentPost.after(out);
                    Helper.autoSize();
                    bootstrap(post, 'post', 'post/new', {});
                }.bind(this));
            }

            if (e.view == 'edit') {
                dust.render('post_edit', post.data, function (err, out) {
                    var $post = this.$postContainer.find('.post-container[data-pid="' + post.get('id') + '"]');
                    $post.replaceWith(out);
                    Helper.autoSize();
                    bootstrap(post, 'post', 'post/edit', {});
                }.bind(this));
            }

            if (e.view == 'view') {
                dust.render('post_view', post.data, function (err, out) {
                    if (e.isNew) {
                        //TODO make me better
                        var parentPostId = post.get('parent_post_id');
                        if (!parentPostId) {
                            //TODO make me better. empty the container if there are no existing replies
                            if (this.$postContainer.find('div.tt-post-container').length == 0)
                                this.$postContainer.find('.lead').remove();
                            this.$postContainer.find('#replyContainerSpacer').before(out);
                        } else {
                            var $lastPost = this.$postContainer.find('.post-container[data-ppid="' + parentPostId + '"]').last();
                            if ($lastPost.length == 0)
                                $lastPost = this.$postContainer.find('.post-container[data-pid="' + parentPostId + '"]');
                            $lastPost.after(out);
                        }
                    } else {
                        var $post = this.$postContainer.find('.post-container[data-pid="' + post.get('id') + '"]');
                        $post.replaceWith(out);
                    }
                    bootstrap(post, 'post', 'post/view', {});
                }.bind(this));
            }
        }

        if (e.keyPath == 'posts') {
            var $posts = this.$postContainer.find('.post-container[data-pid]');
            var postIds = [];
            var postsToRemove = [];

            _.each(e.model.get('posts'), function (element, index, list) {
                postIds.push(element.get('id'));
            });

            postsToRemove = _.filter($posts, function (post) {
                // ignore new post forms (pid < 0)
                return parseInt($(post).data('pid'), 10) > 0 && !_.contains(postIds, $(post).data('pid'));
            });

            _.each(postsToRemove, function (element, index, list) {
                var $post = $(element);
                $post.fadeOut('slow', function (e) {
                    $post.remove();
                });
            });
        }

        if (!this.player)
            return;

        this.player.setKeyPoints(e.model.get('keyPoints', []));

        this.controller.updateKeyPointDuration(this.$video[0].duration);

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
                        this.player.repaint();
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
        this.$video[0].playbackRate = rate.value;
        if (this.$pipVideo.length != 0) {
            this.$pipVideo[0].playbackRate = rate.value;
        }
        $(e.target).attr('src', rate.image);
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