define(['core/mediaChooser', 'controller/post'], function(MediaChooser, Post) {
    "use strict";

    var Thread = function(options) {
        console.log("%s: %s- options=%o", Thread.TAG, "constructor", options);

        this.page = options.page;
        this.tagitOptions = options.tagitOptions;
        this.threadId = options.threadId;
        this.posts = new Array();
        this.mediaChooser = null;
        this.player = null;
        this.playerOptions = null;
        this.keyPoints = new Array();
        this.videoSpeed = 0;

        this.bind__onClickPostTimelineKeyPoint = this._onClickPostTimelineKeyPoint.bind(this);
        this.bind__onMouseEnterPostTimelineKeyPoint = this._onMouseEnterPostTimelineKeyPoint.bind(this);
        this.bind__onMouseLeavePostTimelineKeyPoint = this._onMouseLeavePostTimelineKeyPoint.bind(this);
        this.bind__onClickPostReply = this._onClickPostReply.bind(this);
        this.bind__onClickPostEdit = this._onClickPostEdit.bind(this);
        this.bind__onPostForm = this._onPostForm.bind(this);
        this.bind__onPostSubmitSuccess = this._onPostSubmitSuccess.bind(this);
        this.bind__onPostReset = this._onPostReset.bind(this);
        this.bind__onPostCancel = this._onPostCancel.bind(this);
        this.bind__onPageLoaded = this._onPageLoaded.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);

        $tt._instances.push(this);
    };

    Thread.TAG = "Thread";

    Thread.Page = {
        NEW: 0,
        EDIT: 1,
        VIEW: 2
    };

    // this must be the same name defined in {bundle}/Form/Type/Thread[|Edit]FormType
    Thread.FORM_NAME = "Thread";

    Thread.prototype.getContainer = function() {
        return $("body");
    };

    Thread.prototype.getForm = function() {
        return this.getContainer().find("form[name^=" + Thread.FORM_NAME + "]");
    };

    Thread.prototype.getFormField = function(fieldName) {
        return this.getContainer().find("[id^=" + Thread.FORM_NAME + "]").filter("[id$=_" + fieldName + "]");
    };

    Thread.prototype.bindUIEvents = function() {
        console.log("%s: %s", Thread.TAG, "bindUIEvents");

        switch (this.page) {
            case Thread.Page.NEW:
            case Thread.Page.EDIT:
                this._bindUIEventsNewEdit(this.page == Thread.Page.EDIT);
                break;
            case Thread.Page.VIEW:
                this._bindUIEventsView();
                break;
        }
    };

    Thread.prototype._bindUIEventsNewEdit = function(isEdit) {
        console.log("%s: %s- isEdit=%s", Thread.TAG, "_bindUIEventsNewEdit", isEdit);

        this.mediaChooser = new MediaChooser();
        $(this.mediaChooser).on(MediaChooser.Event.PAGE_LOADED, this.bind__onPageLoaded);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.setContainer(this.getContainer());
        this.mediaChooser.bindUIEvents();

        this.getFormField("permissions_usersWithAccess").tagit(this.tagitOptions);
        $("ul.tagit").hide();

        $("#permissionRadioButtons div.radio").on("click", (function(e) {
            if (this.getFormField("permissions_accessLevel_2").is(':checked')) { // specific users
                $("ul.tagit").show();
                this.getFormField("permissions_userGroupsWithAccess").hide();
            } else if (this.getFormField("permissions_accessLevel_3").is(':checked')) { // specific groups
                this.getFormField("permissions_userGroupsWithAccess").show();
                $("ul.tagit").hide();
            } else { // hide all
                this.getFormField("permissions_userGroupsWithAccess").hide();
                $("ul.tagit").hide();
            }
        }).bind(this));

        $("#permissionRadioButtons").trigger("click");
    };

    Thread.prototype._bindUIEventsView = function() {
        console.log("%s: %s", Thread.TAG, "_bindUIEventsView");

        var post = new Post({
            page: Post.Page.NEW,
            id: -1,
            threadId: this.threadId
        });
        this.posts.push(post);
        $(post).on(Post.Event.RESET, this.bind__onPostReset);
        post.bindUIEvents();

        // change the video speed when the slowdown button is clicked
        $("#videoSpeed").on("click", (function(e) {
            e.preventDefault();

            this.videoSpeed = (this.videoSpeed+1)%3;
            switch (this.videoSpeed) {
                case 0:
                    this.playerOptions.mediaElement[0].playbackRate = 1.0;
                    $("#videoSpeed img").attr("src", this.playerOptions.speedImages.normal);
                    break;
                case 1:
                    this.playerOptions.mediaElement[0].playbackRate = 2.0;
                    $("#videoSpeed img").attr("src", this.playerOptions.speedImages.fast);
                    break;
                case 2:
                    this.playerOptions.mediaElement[0].playbackRate = 0.5;
                    $("#videoSpeed img").attr("src", this.playerOptions.speedImages.slow);
                    break;
                default:
                    this.playerOptions.mediaElement[0].playbackRate = 1.0;
                    $("#videoSpeed img").attr("src", this.playerOptions.speedImages.normal);
                    break;
            }
        }).bind(this));

        // change the captioning display when you click the captioning button
        $("#closedCaptions").on("click", (function(e) {
            e.preventDefault();

            //$("#closed-caption-button img").attr("src", this.playerOptions.captionImages.off);
            //$("#closed-caption-button img").attr("src", this.playerOptions.captionImages.on);
        }).bind(this));

        // clicking the clock icon will move the density bar to the comments time
        // and highlight the comment on the density bar
        $(".post-timeline-keypoint").on("click", this.bind__onClickPostTimelineKeyPoint);

        // mousing over the clock icon should highlight the comment on the density bar
        $(".post-timeline-keypoint").hover(
            this.bind__onMouseEnterPostTimelineKeyPoint,
            this.bind__onMouseLeavePostTimelineKeyPoint);

        $(".post-edit").on("click", this.bind__onClickPostEdit);

        // launch the modal dialog to delete a comment when you click the trash icon
        $(".post-delete").on("click", function(e) {
            e.preventDefault();

            var postId = $(this).data("pid");

            $("#modalDeleteButton").data("pid", postId);
            $("#modalCancelButton").data("pid", postId);
            $("#modaldiv").modal("toggle");
        });

        $("#modalDeleteButton").on("click", (function(e) {
            e.preventDefault();

            var postId = $("#modalDeleteButton").data("pid");
            var data = {pid: postId};

            $.ajax({
                url: Routing.generate('imdc_post_delete', data),
                type: "POST",
                contentType: "application/x-www-form-urlencoded",
                data: data,
                success: (function(data) {
                    console.log("%s: %s: %s", Thread.TAG, "#modalDeleteButton:click", "success");

                    var post = $(".tt-post-container[data-pid=" + postId + "]");

                    // create a feedback message
                    //TODO make me better
                    post.after('<div id="postDeleteSuccess" class="row"><div class="col-md-12"><p class="text-success"><i class="fa fa-check"></i> ' + data.feedback + '</p></div></div>');

                    // fade out the original comment
                    post.fadeOut('slow', function(){$(this).remove();});

                    // delete timeline region
                    this.player.removeKeyPoint(this._getKeyPoint(postId));

                    $("#modaldiv").modal('hide');

                    // wipe out the feedback message after 5 seconds
                    setTimeout(function(){
                        $("#postDeleteSuccess").fadeOut("slow", function(e) {
                            $(this).remove();
                        });
                    }, 5000);
                }).bind(this),
                error: function(request) {
                    console.log("%s: %s: %s", Thread.TAG, "#modalDeleteButton:click", "error");

                    console.log(request.statusText);

                    $("#modaldiv").modal('hide');
                }
            });
        }).bind(this));

        $(".post-reply").on("click", this.bind__onClickPostReply);
    };

    Thread.prototype._getPost = function(id) {
        console.log("%s: %s- id=%d", Thread.TAG, "_getPost", id);

        for (var p in this.posts) {
            var post = this.posts[p];
            if (post.id == id) {
                return post;
            }
        }
        return null;
    };

    Thread.prototype._onClickPostTimelineKeyPoint = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        var keyPoint = this._getKeyPoint($(e.currentTarget).data("pid"));
        keyPoint.paintHighlightedTimeout = true;
        keyPoint.paintHighlighted = true;
        this.player.seek(keyPoint.startTime);
        this.player.redrawKeyPoints = true;
        this.player.repaint();

        // clear the highlighted comment after 3 seconds
        setTimeout((function() {
            keyPoint.paintHighlightedTimeout = false;
            keyPoint.paintHighlighted = false;
            this.player.redrawKeyPoints = true;
            this.player.repaint();
        }).bind(this), 3000);
    };

    Thread.prototype._onMouseEnterPostTimelineKeyPoint = function(e) {
        // highlight the comment
        var keyPoint = this._getKeyPoint($(e.currentTarget).data("pid"));
        keyPoint.paintHighlighted = true;
        this.player.redrawKeyPoints = true;
        this.player.repaint();
    };

    Thread.prototype._onMouseLeavePostTimelineKeyPoint = function(e) {
        var keyPoint = this._getKeyPoint($(e.currentTarget).data("pid"));
        if (keyPoint.paintHighlightedTimeout == true) {
            return;
        }
        keyPoint.paintHighlighted = false;
        this.player.redrawKeyPoints = true;
        this.player.repaint();
    };

    Thread.prototype._onClickPostReply = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        var postId = $(e.currentTarget).data("pid");
        var post = this._getPost(postId);
        if (!post) {
            post = new Post({
                page: Post.Page.REPLY,
                id: postId,
                threadId: this.threadId
            });
            this.posts.push(post);
        }

        $(post).on(Post.Event.FORM, this.bind__onPostForm);
        $(post).on(Post.Event.RESET, this.bind__onPostReset);
        $(post).on(Post.Event.CANCEL, this.bind__onPostCancel);

        post.handlePage();
    };

    Thread.prototype._onClickPostEdit = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        var postId = $(e.currentTarget).data("pid");
        var post = this._getPost(postId);
        if (!post) {
            post = new Post({
                page: Post.Page.EDIT,
                id: postId,
                threadId: this.threadId
            });
            this.posts.push(post);
        }

        $(post).on(Post.Event.FORM, this.bind__onPostForm);
        $(post).on(Post.Event.SUBMIT_SUCCESS, this.bind__onPostSubmitSuccess);
        $(post).on(Post.Event.CANCEL, this.bind__onPostCancel);

        post.handlePage();
    };

    Thread.prototype._onPostForm = function(e) {
        if (e.post.page == Post.Page.EDIT)
            this._toggleTemporal(this._getKeyPoint(e.post.id), false);

        e.post.bindUIEvents();
    };

    Thread.prototype._toggleTemporal = function(keyPoint, disabled) {
        if (!disabled && keyPoint && keyPoint.options.drawOnTimeLine) {
            this.player.pause();
            this.player.setPlayHeadImage("");
            this.player.setVideoTime(keyPoint.startTime);
            this.player.setAreaSelectionStartTime(keyPoint.startTime);
            this.player.setAreaSelectionEndTime(keyPoint.endTime);
            this.player.setAreaSelectionEnabled(true);
        } else {
            this.player.pause();
            this.player.setPlayHeadImage(this.playerOptions.playHeadImage);
            this.player.setAreaSelectionEnabled(false);
        }
    };

    Thread.prototype._onPostSubmitSuccess = function(e) {
        if (e.post.page == Post.Page.EDIT) {
            var container = e.post._getElement(Post.Binder.CONTAINER_VIEW);
            if (container.data('istemporal')) {
                this.replaceKeyPoint(new KeyPoint(
                    container.data("pid"),
                    container.data("starttime"),
                    container.data("endtime"),
                    "", {
                        drawOnTimeLine: container.data("istemporal")
                    }
                ));
            }
        }

        this._toggleTemporal(this._getKeyPoint(e.post.id), true);
    };

    Thread.prototype._onPostReset = function(e) {
        // when editing posts don't disable, just reset the key point
        var disabled = e.post.page == Post.Page.EDIT ? false : true;

        this._toggleTemporal(this._getKeyPoint(e.post.id), disabled);
    };

    Thread.prototype._onPostCancel = function(e) {
        this._toggleTemporal(this._getKeyPoint(e.post.id), true);
    };

    Thread.prototype.createKeyPoints = function() {
        console.log("%s: %s", Thread.TAG, "createKeyPoints");

        this.keyPoints = new Array();
        $(Post.Binder.CONTAINER_VIEW).each((function(key, element) {
            this.keyPoints.push(new KeyPoint(
                $(element).data("pid"),
                $(element).data("starttime"),
                $(element).data("endtime"),
                "", {
                    drawOnTimeLine: $(element).data("istemporal")
                }
            ));
        }).bind(this));
    };

    Thread.prototype._getKeyPoint = function(postId) {
        console.log("%s: %s- postId=%d", Thread.TAG, "_getKeyPoint", postId);

        for (var kp in this.keyPoints) {
            var keyPoint = this.keyPoints[kp];
            if (keyPoint.id == postId) {
                return keyPoint;
            }
        }
        return null;
    };

    Thread.prototype.replaceKeyPoint = function(keyPoint) {
        console.log("%s: %s- keyPoint=%o", Thread.TAG, "replaceKeyPoint", keyPoint);

        for (var kp in this.keyPoints) {
            var oldKeyPoint = this.keyPoints[kp];
            if (oldKeyPoint.id == keyPoint.id) {
                this.keyPoints[kp] = keyPoint;
                break;
            }
        }

        if (this.player) {
            this.player.setKeyPoints(this.keyPoints);
            this.player.repaint();
            this.renderPostTimelinePoints();
        }

        $(".post-timeline-keypoint[data-pid=" + keyPoint.id + "]").on("click", this.bind__onClickPostTimelineKeyPoint);
        $(".post-timeline-keypoint[data-pid=" + keyPoint.id + "]").hover(
            this.bind__onMouseEnterPostTimelineKeyPoint,
            this.bind__onMouseLeavePostTimelineKeyPoint);
    };

    Thread.prototype.renderPostTimelinePoints = function() {
        console.log("%s: %s", Thread.TAG, "renderPostTimelinePoints");

        $(Post.Binder.CONTAINER_VIEW).each((function(key, element) {
            var duration = this.playerOptions.mediaElement[0].duration;
            var startTimePercentage = ((100 * $(element).data("starttime")) / duration).toFixed(2);
            var endTimePercentage = ((100 * $(element).data("endtime")) / duration).toFixed(2);
            var widthPercentage = (endTimePercentage - startTimePercentage).toFixed(2);

            $(".post-timeline-keypoint[data-pid=" + $(element).data("pid") + "]").css({
                left: startTimePercentage + "%",
                width: widthPercentage + "%"
            });
        }).bind(this));
    };

    /**
     *
     * @param {object} options { mediaElement {object} element for the player
     *                              playHeadImage {string} twig asset path to an image
     *                              speedImages {object}
     *                              captionImages {object} }
     */
    Thread.prototype.createPlayer = function(options) {
        console.log("%s: %s", Thread.TAG, "createPlayer");

        this.playerOptions = options;

        this.player = new Player(this.playerOptions.mediaElement, {
            areaSelectionEnabled: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
            audioBar: false,
            overlayControls: true,
            playHeadImage: this.playerOptions.playHeadImage,
            playHeadImageOnClick: (function() {
                var currentTime = this.player.getCurrentTime();
                this._toggleTemporal(new KeyPoint(-1, currentTime, currentTime, "", {drawOnTimeLine: true}), false)
            }).bind(this)
        });

        this.createKeyPoints();

        this.playerOptions.mediaElement.on("loadedmetadata", (function(e) {
            this.renderPostTimelinePoints();
        }).bind(this));

        if (!isNaN(this.playerOptions.mediaElement[0].duration)) {
            this.playerOptions.mediaElement.trigger("loadedmetadata");
        }

        this.player.setKeyPoints(this.keyPoints);
        this.player.createControls();

        $(this.player).on(Player.EVENT_AREA_SELECTION_CHANGED, (function(e) {
            console.log("%s: %s", Thread.TAG, Player.EVENT_AREA_SELECTION_CHANGED);

            // set times of all posts
            for (var p in this.posts) {
                var post = this.posts[p];
                var selection = this.player.getAreaSelectionTimes();
                post.getFormField("startTime").val(selection.minTime.toFixed(2));
                post.getFormField("endTime").val(selection.maxTime.toFixed(2));
            }
        }).bind(this));

        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OVER, function(e, keyPoint, coords) {
            console.log("%s: %s- keyPoint=%o, coords=%o", Thread.TAG, Player.EVENT_KEYPOINT_MOUSE_OVER, keyPoint, coords);

            $(".tt-post-container[data-pid=" + keyPoint.id + "]").addClass("tt-post-container-highlight");

            // avoid animating when key points are overlapped and multiple invokes of this event are called
            if (!$("#threadReplyContainer").is(':animated')) {
                $("#threadReplyContainer").animate({
                    scrollTop: $(".tt-post-container[data-pid=" + keyPoint.id + "]").position().top
                }, 200);
            }
        });

        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OUT, function(e, keyPoint) {
            console.log("%s: %s- keyPoint=%o", Thread.TAG, Player.EVENT_KEYPOINT_MOUSE_OUT, keyPoint);

            $(".tt-post-container[data-pid=" + keyPoint.id + "]").removeClass("tt-post-container-highlight");
        });

        $(this.player).on(Player.EVENT_KEYPOINT_CLICK, function(e, keyPoint, coords) {
            console.log("%s: %s- keyPoint=%o, coords=%o", Thread.TAG, Player.EVENT_KEYPOINT_CLICK, keyPoint, coords);
        });

        $(this.player).on(Player.EVENT_KEYPOINT_BEGIN, function(e, keyPoint) {
            console.log("%s: %s- keyPoint=%o", Thread.TAG, Player.EVENT_KEYPOINT_BEGIN, keyPoint);
        });

        $(this.player).on(Player.EVENT_KEYPOINT_END, function(e, keyPoint) {
            console.log("%s: %s- keyPoint=%o", Thread.TAG, Player.EVENT_KEYPOINT_END, keyPoint);
        });
    };

    Thread.prototype._onPageLoaded = function() {
        console.log("%s: %s", Thread.TAG, "_onPageLoaded");

        switch (this.mediaChooser.page) {
            case MediaChooser.Page.RECORD_VIDEO:
                this.mediaChooser.createVideoRecorder();
                break;
            case MediaChooser.Page.PREVIEW:
                if (e.data.media.type == MediaChooser.MEDIA_TYPE.VIDEO.id)
                    this.mediaChooser.createVideoPlayer();

                break;
        }
    };

    Thread.prototype._onSuccess = function(e) {
        this.getFormField("mediatextarea").val(e.media.id);
    };

    Thread.prototype._onReset = function(e) {
        this.getFormField("mediatextarea").val("");
    };

    return Thread;
});
