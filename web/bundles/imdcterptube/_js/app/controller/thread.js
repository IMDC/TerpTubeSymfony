define(['core/mediaChooser', 'controller/post'], function(MediaChooser, Post) {
    "use strict";

    var Thread = function(options) {
        console.log("%s: %s- options=%o", Thread.TAG, "constructor", options);

        this.page = options.page;
        this.threadId = options.threadId;
        this.posts = new Array();
        this.mediaChooser = null;
        this.player = null;
        this.playerOptions = null;
        this.keyPoints = new Array();
        this.videoSpeed = 0;

        this.bind__onClickSubmit = this._onClickSubmit.bind(this);
        this.bind__onClickDelete = this._onClickDelete.bind(this);
        this.bind__onDeleteSuccess = this._onDeleteSuccess.bind(this);
        this.bind__onDeleteError = this._onDeleteError.bind(this);
        this.bind__onClickPostTimelineKeyPoint = this._onClickPostTimelineKeyPoint.bind(this);
        this.bind__onMouseEnterPostTimelineKeyPoint = this._onMouseEnterPostTimelineKeyPoint.bind(this);
        this.bind__onMouseLeavePostTimelineKeyPoint = this._onMouseLeavePostTimelineKeyPoint.bind(this);
        this.bind__onClickPostReply = this._onClickPostReply.bind(this);
        this.bind__onClickPostEdit = this._onClickPostEdit.bind(this);
        this.bind__onClickPostDelete = this._onClickPostDelete.bind(this);
        this.bind__onPostForm = this._onPostForm.bind(this);
        this.bind__onPostSubmitSuccess = this._onPostSubmitSuccess.bind(this);
        this.bind__onPostReset = this._onPostReset.bind(this);
        this.bind__onPostCancel = this._onPostCancel.bind(this);
//        this.bind__onPageLoaded = this._onPageLoaded.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);

        $tt._instances.push(this);
    };

    Thread.TAG = "Thread";
    Thread.DEFAULT_TEMPORAL_COMMENT_LENGTH = 3;

    Thread.Page = {
        NEW: 0,
        EDIT: 1,
        VIEW: 2
    };

    Thread.Binder = {
        SUBMIT: ".thread-submit",
        DELETE_MODAL: ".thread-delete-modal",
        DELETE: ".thread-delete"
    };

    // this must be the same name defined in {bundle}/Form/Type/ThreadType
    Thread.FORM_NAME = "thread";

    Thread.prototype.getContainer = function() {
        return $("body");
    };

    Thread.prototype._getElement = function(binder) {
        return this.getContainer().find(binder);
    };

    Thread.prototype.getForm = function() {
        return this.getContainer().find("form[name^=" + Thread.FORM_NAME + "]");
    };

    Thread.prototype.getFormField = function(fieldName) {
        return this.getForm().find("[id^=" + Thread.FORM_NAME + "]").filter("[id$=_" + fieldName + "]");
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
//        $(this.mediaChooser).on(MediaChooser.Event.PAGE_LOADED, this.bind__onPageLoaded);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.setContainer(this.getContainer());
        this.mediaChooser.bindUIEvents();

        var mediaIds = [];
        this.getFormField("mediaIncluded").children().each(function(index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this._getElement(Thread.Binder.SUBMIT).attr("disabled", true);
            this.mediaChooser.setMedia(mediaIds);
        }

        this._getElement(Thread.Binder.SUBMIT).on("click", this.bind__onClickSubmit);
        this._getElement(Thread.Binder.DELETE).on("click", this.bind__onClickDelete);
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
        $(".post-delete-A").on("click", this.bind__onClickPostDelete);

        $(".post-reply").on("click", this.bind__onClickPostReply);
    };

    Thread.prototype._onClickSubmit = function(e) {
        $(e.target).button("loading");
    };

    Thread.prototype._onClickDelete = function(e) {
        $(e.target).button("loading");

        $.ajax({
            url: Routing.generate("imdc_thread_delete", {threadid: this.threadId}),
            type: "POST",
            success: this.bind__onDeleteSuccess,
            error: this.bind__onDeleteError
        });
    };

    Thread.prototype._onDeleteSuccess = function(data, textStatus, jqXHR) {
        if (!data.wasDeleted) {
            this._onDeleteError(jqXHR, textStatus, null);
            return;
        }

        this._getElement(Thread.Binder.DELETE_MODAL)
            .find(".modal-body")
            .html("Topic deleted successfully.");

        window.location.assign(data.redirectUrl);
    };

    Thread.prototype._onDeleteError = function(jqXHR, textStatus, errorThrown) {
        this._getElement(Thread.Binder.DELETE_MODAL)
            .find(".modal-body")
            .prepend("Something went wrong. Try again.");

        this._getElement(Thread.Binder.DELETE).button("reset");
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

    Thread.prototype._deletePost = function(id) {
        console.log("%s: %s- id=%d", Thread.TAG, "_deletePost", id);

        for (var p in this.posts) {
            var post = this.posts[p];
            if (post.id == id) {
                this.posts.splice(p, 1);
                break;
            }
        }
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

    Thread.prototype._postEventBind = function(post, on) {
        if (on) {
            $(post).on(Post.Event.FORM, this.bind__onPostForm);
            if (post.page == Post.Page.REPLY) $(post).on(Post.Event.RESET, this.bind__onPostReset);
            $(post).on(Post.Event.SUBMIT_SUCCESS, this.bind__onPostSubmitSuccess);
            $(post).on(Post.Event.CANCEL, this.bind__onPostCancel);
        } else {
            $(post).off(Post.Event.FORM, this.bind__onPostForm);
            if (post.page == Post.Page.REPLY) $(post).off(Post.Event.RESET, this.bind__onPostReset);
            $(post).off(Post.Event.SUBMIT_SUCCESS, this.bind__onPostSubmitSuccess);
            $(post).off(Post.Event.CANCEL, this.bind__onPostCancel);
        }
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
        post.page = Post.Page.REPLY;

        this._postEventBind(post, false); // ensure its off to prevent double binding
        this._postEventBind(post, true);

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
        post.page = Post.Page.EDIT;

        this._postEventBind(post, false); // ensure its off to prevent double binding
        this._postEventBind(post, true);

        post.handlePage();
    };

    Thread.prototype._onClickPostDelete = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        var postId = $(e.currentTarget).data("pid");
        var post = this._getPost(postId);
        if (!post) {
            post = new Post({
                page: Post.Page.DELETE,
                id: postId,
                threadId: this.threadId
            });
            this.posts.push(post);
        }
        post.page = Post.Page.DELETE;

        this._postEventBind(post, false); // ensure its off to prevent double binding
        this._postEventBind(post, true);

        //$("#modalCancelButton").off("click", post.bind__onClickCancel);
        $(Post.Binder.DELETE).off("click", post.bind__onClickSubmit);

        //$("#modalCancelButton").on("click", post.bind__onClickCancel);
        $(Post.Binder.DELETE).on("click", post.bind__onClickSubmit);

        //$("#modaldiv").modal("toggle");
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
        switch (e.post.page) {
            case Post.Page.EDIT:
                var container = e.post._getElement(Post.Binder.CONTAINER_VIEW);
                if (container.data('istemporal')) {
                    this._replaceKeyPoint(new KeyPoint(
                        container.data("pid"),
                        container.data("starttime"),
                        container.data("endtime"),
                        "", {
                            drawOnTimeLine: container.data("istemporal")
                        }
                    ));
                }
                break;
            case Post.Page.DELETE:
                this._deleteKeyPoint(e.post.id);
                this._deletePost(e.post.id);

                $("#modaldiv").modal("hide");
                break;
        }
    };

    Thread.prototype._onPostReset = function(e) {
        // when editing posts don't disable, just reset the key point
        var disabled = e.post.page == Post.Page.EDIT ? false : true;

        this._toggleTemporal(this._getKeyPoint(e.post.id), disabled);
    };

    Thread.prototype._onPostCancel = function(e) {
        this._toggleTemporal(this._getKeyPoint(e.post.id), true);
    };

    Thread.prototype._createKeyPoints = function() {
        console.log("%s: %s", Thread.TAG, "_createKeyPoints");

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

    Thread.prototype._replaceKeyPoint = function(keyPoint) {
        console.log("%s: %s- keyPoint=%o", Thread.TAG, "_replaceKeyPoint", keyPoint);

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
            this._renderPostTimelinePoints();
        }

        $(".post-timeline-keypoint[data-pid=" + keyPoint.id + "]").on("click", this.bind__onClickPostTimelineKeyPoint);
        $(".post-timeline-keypoint[data-pid=" + keyPoint.id + "]").hover(
            this.bind__onMouseEnterPostTimelineKeyPoint,
            this.bind__onMouseLeavePostTimelineKeyPoint);
    };

    Thread.prototype._deleteKeyPoint = function(postId) {
        console.log("%s: %s- postId=%d", Thread.TAG, "_deleteKeyPoint", postId);

        for (var kp in this.keyPoints) {
            var keyPoint = this.keyPoints[kp];
            if (keyPoint.id == postId) {
                this.keyPoints.splice(kp, 1);
                break;
            }
        }

        if (this.player) {
            this.player.setKeyPoints(this.keyPoints);
            this.player.repaint();
            this._renderPostTimelinePoints();
        }
    };

    Thread.prototype._renderPostTimelinePoints = function() {
        console.log("%s: %s", Thread.TAG, "_renderPostTimelinePoints");

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
                this._toggleTemporal(new KeyPoint(-1, currentTime, currentTime + Thread.DEFAULT_TEMPORAL_COMMENT_LENGTH, "", {drawOnTimeLine: true}), false)
            }).bind(this)
        });

        this._createKeyPoints();

        this.playerOptions.mediaElement.on("loadedmetadata", (function(e) {
            this._renderPostTimelinePoints();
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
                post.getFormField("startTime").val(parseFloat(selection.minTime).toFixed(2));
                post.getFormField("endTime").val(parseFloat(selection.maxTime).toFixed(2));
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

    //thought this would of been useful at some point between page loads. guess not
//    Thread.prototype._onPageLoaded = function() {
//        console.log("%s: %s", Thread.TAG, "_onPageLoaded");
//
//        switch (this.mediaChooser.page) {
//            case MediaChooser.Page.RECORD_VIDEO:
//                this.mediaChooser.createVideoRecorder();
//                break;
//            case MediaChooser.Page.PREVIEW:
//                if (e.data.media.type == MediaChooser.MEDIA_TYPE.VIDEO.id)
//                    this.mediaChooser.createVideoPlayer();
//
//                break;
//        }
//    };

    Thread.prototype._updateForm = function() {
        var formField = this.getFormField("mediaIncluded");
        formField.html(
            this.mediaChooser.generateFormData(
                formField.data("prototype")
            )
        );
    };

    Thread.prototype._onSuccess = function(e) {
        this._getElement(Thread.Binder.SUBMIT).attr("disabled", false);

        this.getFormField("title")
            .attr("required", false)
            .parent()
            .find("label")
            .removeClass("required");

        this._updateForm();
    };

    Thread.prototype._onReset = function(e) {
        if (this.mediaChooser.media.length == 0)
            this.getFormField("title")
                .attr("required", true)
                .parent()
                .find("label")
                .addClass("required");

        this._updateForm();
    };

    return Thread;
});
