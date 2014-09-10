define(['core/mediaChooser'], function(MediaChooser) {
    var Post = function() {
        this.page = null;
        this.mediaChooser = null;
        this.forwardButton = "<button class='forwardButton'></button>";
        this.doneButton = "<button class='doneButton'></button>";
        this.doneAndPostButton = "<button class='doneAndPostButton'></button>";

        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onSuccessAndPost = this._onSuccessAndPost.bind(this);
        this.bind__onReset = this._onReset.bind(this);
        this.bind_forwardFunction = this.forwardFunction.bind(this);
        this.bind_doneFunction = this.doneFunction.bind(this);
        this.bind_doneAndPostFunction = this.doneAndPostFunction.bind(this);
    }

    Post.TAG = "Post";

    Post.Page = {
        EDIT: 0,
        REPLY: 1
    };

    /**
     * MediaChooser options for each related page that uses MediaChooser
     * @param {number} page
     * @param {number} postId
     */
    Post.mediaChooserOptions = function(page, postId) {
        switch (page) {
            case Post.Page.EDIT:
            case Post.Page.REPLY:
                return {
                    element: $("#filesPost" + postId),
                    isPopUp: true,
                    isPost: true,
                    postId: postId
                };
        }
    };

    /**
     * ui element event bindings in order of appearance
     * @param {number} page
     * @param {number} postId
     */
    Post.prototype.bindUIEvents = function(page, postId) {
        console.log("%s: %s- page=%d, postId=%d", Post.TAG, "bindUIEvents", page, postId);

        this.page = page;

        switch (this.page) {
            case Post.Page.EDIT:
                this._bindUIEventsEdit(postId);
                break;
            case Post.Page.REPLY:
                this._bindUIEventsReply(postId);
                break;
        }
    };

    Post.prototype._bindUIEventsEdit = function(postId) {
        console.log("%s: %s- postId=%d", Post.TAG, "_bindUIEventsEdit", postId);

        this.mediaChooser = new MediaChooser(Post.mediaChooserOptions(Post.Page.EDIT, postId));
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS_AND_POST, this.bind__onSuccessAndPost);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.bindUIEvents();

        $("#cancelEditPost" + postId).on("click", function(e) {
            e.preventDefault();

            $("#containerEditPost" + postId).html("");
            $("#containerEditPost" + postId).hide();

            $("#containerPost" + postId).show();

            if (thread.player) {
                enableTemporalComment(thread.player, false, $("#PostEditForm_startTime"), $("#PostEditForm_endTime"));
            }
        });
    };

    Post.prototype._bindUIEventsReply = function(postId) {
        console.log("%s: %s- postId=%d", Post.TAG, "_bindUIEventsReply", postId);

        this.mediaChooser = new MediaChooser(Post.mediaChooserOptions(Post.Page.REPLY, postId));
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS_AND_POST, this.bind__onSuccessAndPost);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.bindUIEvents();

        $("#cancelReplyPost" + postId).on("click", function(e) {
            e.preventDefault();

            $("#replyPostContainer" + postId).remove();

            // restore the comment reply link if you click the cancel button
            $(".post-reply[data-pid=" + postId + "]").show();
        });
    };

    Post.prototype._onSuccess = function(e) {
        switch (this.page) {
            case Post.Page.EDIT:
            case Post.Page.REPLY:
                //$("#PostEditForm_mediatextarea").val(e.media.id);
                $(".mediatextarea-post-" + e.postId).val(e.media.id);
                break;
        }
    };

    Post.prototype._onSuccessAndPost = function(e) {
        switch (this.page) {
            case Post.Page.EDIT:
            case Post.Page.REPLY:
                //$("#PostEditForm_mediatextarea").val(media.id);
                $(".mediatextarea-post-" + e.postId).val(e.media.id);
                //TODO do the post
                $("#PostReplyToPostForm_submit").trigger("click");
                break;
        }
    };

    Post.prototype._onReset = function(e) {
        switch (this.page) {
            case Post.Page.EDIT:
            case Post.Page.REPLY:
                //$("#PostEditForm_mediatextarea").val("");
                $(".mediatextarea-post-" + e.postId).val("");
                break;
        }
    };

    /**
     * @param {object} videoElement
     */
    Post.prototype.createVideoRecorder = function(videoElement) {
        console.log("%s: %s", Post.TAG, "createVideoRecorder");

        this.mediaChooser.createVideoRecorder({
            videoElement: videoElement,
            forwardButtons: [this.forwardButton, this.doneButton, this.doneAndPostButton],
            forwardFunctions: [this.bind_forwardFunction, this.bind_doneFunction, this.bind_doneAndPostFunction]
        });
    };

    Post.prototype.forwardFunction = function() {
        console.log("%s: %s", Post.TAG, "forwardFunction");

        this.mediaChooser.destroyVideoRecorder();

        this.mediaChooser.previewMedia({
            type: MediaChooser.TYPE_RECORD_VIDEO,
            mediaUrl: Routing.generate('imdc_files_gateway_preview', { mediaId: this.mediaChooser.media.id }),
            mediaId: this.mediaChooser.media.id,
            recording: true
        });
    };

    Post.prototype.doneFunction = function() {
        console.log("%s: %s", Post.TAG, "doneFunction");

        this.mediaChooser.destroyVideoRecorder();

        this.mediaChooser._previewVideoForwardFunctionDone();
    };

    Post.prototype.doneAndPostFunction = function() {
        console.log("%s: %s", Post.TAG, "doneAndPostFunction");

        this.mediaChooser.destroyVideoRecorder();

        this.mediaChooser._previewVideoForwardFunctionDoneAndPost();
    };

    return Post;
});
