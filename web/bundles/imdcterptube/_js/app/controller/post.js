define(['core/mediaChooser'], function(MediaChooser) {
    var Post = function() {
        this.page = null;
        this.postId = null;
        this.mediaChooser = null;
        this.forwardButton = "<button class='forwardButton'></button>";
        this.doneButton = "<button class='doneButton'></button>";
        this.doneAndPostButton = "<button class='doneAndPostButton'></button>";

        this.bind__onClickSubmitEditPost = this._onClickSubmitEditPost.bind(this);
        this.bind__onClickCancelEditPost = this._onClickCancelEditPost.bind(this);
        this.bind__onClickSubmitReplyPost = this._onClickSubmitReplyPost.bind(this);
        this.bind__onClickResetReplyPost = this._onClickResetReplyPost.bind(this);
        this.bind__onClickCancelReplyPost = this._onClickCancelReplyPost.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onSuccessAndPost = this._onSuccessAndPost.bind(this);
        this.bind__onReset = this._onReset.bind(this);
        this.bind_forwardFunction = this.forwardFunction.bind(this);
        this.bind_doneFunction = this.doneFunction.bind(this);
        this.bind_doneAndPostFunction = this.doneAndPostFunction.bind(this);
    }

    Post.TAG = "Post";

    Post.Page = {
        REPLY: 0,
        EDIT: 1
    };

    /**
     * MediaChooser options for each related page that uses MediaChooser
     * @param {number} page
     * @param {number} postId
     */
    Post.mediaChooserOptions = function(page, postId) {
        switch (page) {
            case Post.Page.REPLY:
            case Post.Page.EDIT:
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
        this.postId = postId;

        switch (this.page) {
            case Post.Page.REPLY:
                this._bindUIEventsReply();
                break;
            case Post.Page.EDIT:
                this._bindUIEventsEdit();
                break;
        }
    };

    Post.prototype._bindUIEventsReply = function() {
        console.log("%s: %s", Post.TAG, "_bindUIEventsReply");

        this.mediaChooser = new MediaChooser(Post.mediaChooserOptions(Post.Page.REPLY, this.postId));
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS_AND_POST, this.bind__onSuccessAndPost);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.bindUIEvents();

        $("#submitReplyPost" + this.postId).on("click", this.bind__onClickSubmitReplyPost);
        $("#resetReplyPost" + this.postId).on("click", this.bind__onClickResetReplyPost);
        $("#cancelReplyPost" + this.postId).on("click", this.bind__onClickCancelReplyPost);
    };

    Post.prototype._onClickSubmitReplyPost = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        if ($("#PostReplyToPostForm_content").val() == "" && this.mediaChooser.media == null) {
            alert("Your post cannot be blank. You must either select a file or write a comment.");
            return;
        }

        this._disableReplyForm(true);

        $.ajax({
            url: Routing.generate('imdc_post_reply', {pid: this.postId}),
            type: "POST",
            contentType: false,
            data: new FormData($("[name=PostReplyToPostForm]")[0]),
            processData: false,
            success: (function(data) {
                console.log("%s: %s: %s", Post.TAG, "_onClickSubmitReplyPost", "success");

                if (data.wasReplied) {
                    window.location.replace(data.redirectUrl);
                } else {
                    $("#containerReplyPost" + this.postId).html(data.html);
                    this._disableReplyForm(false);
                }
            }).bind(this),
            error: function(request) {
                console.log("%s: %s: %s", Post.TAG, "_onClickSubmitReplyPost", "error");

                console.log(request.statusText);
                this._disableReplyForm(false);
            }
        });
    };

    Post.prototype._disableReplyForm = function(disable) {
        $("#submitReplyPost" + this.postId).button(disable ? "loading" : "reset");
        $("#resetReplyPost" + this.postId).prop("disabled", disable);
        $("#cancelReplyPost" + this.postId).prop("disabled", disable);
    };

    Post.prototype._onClickResetReplyPost = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        this.mediaChooser.reset();
        $("#PostReplyToPostForm_content").val("");
    };

    Post.prototype._onClickCancelReplyPost = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        $("#containerReplyPost" + this.postId).html("");

        // restore the comment reply link if you click the cancel button
        $(".post-reply[data-pid=" + this.postId + "]").show();
    };

    Post.prototype._bindUIEventsEdit = function() {
        console.log("%s: %s", Post.TAG, "_bindUIEventsEdit");

        this.mediaChooser = new MediaChooser(Post.mediaChooserOptions(Post.Page.EDIT, this.postId));
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS_AND_POST, this.bind__onSuccessAndPost);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.bindUIEvents();

        $("#submitEditPost" + this.postId).on("click", this.bind__onClickSubmitEditPost);
        $("#cancelEditPost" + this.postId).on("click", this.bind__onClickCancelEditPost);
    };

    Post.prototype._onClickSubmitEditPost = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        if ($("#PostEditForm_content").val() == "" && this.mediaChooser.media == null) {
            alert("Your post cannot be blank. You must either select a file or write a comment.");
            return;
        }

        this._disableEditForm(true);

        $.ajax({
            url: Routing.generate('imdc_post_edit', {pid: this.postId}),
            type: "POST",
            contentType: false,
            data: new FormData($("[name=PostEditForm]")[0]),
            processData: false,
            success: (function(data) {
                console.log("%s: %s: %s", Post.TAG, "_onClickSubmitEditPost", "success");

                if (data.wasEdited) {
                    this._onClickCancelEditPost();

                    $("#containerPost" + this.postId).html(data.html);
                } else {
                    $("#containerEditPost" + this.postId).html(data.html);
                    this._disableEditForm(false);
                }
            }).bind(this),
            error: function(request) {
                console.log("%s: %s: %s", Post.TAG, "_onClickSubmitEditPost", "error");

                console.log(request.statusText);
                this._disableEditForm(false);
            }
        });
    };

    Post.prototype._disableEditForm = function(disable) {
        $("#submitEditPost" + this.postId).button(disable ? "loading" : "reset");
        $("#cancelEditPost" + this.postId).prop("disabled", disable);
    };

    Post.prototype._onClickCancelEditPost = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        $("#containerEditPost" + this.postId).hide();
        $("#containerEditPost" + this.postId).html("");

        if (context.player) {
            enableTemporalComment(context.player, false, $("#PostEditForm_startTime"), $("#PostEditForm_endTime"));
        }

        $("#containerPost" + this.postId).show();
    };

    Post.prototype._onSuccess = function(e) {
        switch (this.page) {
            case Post.Page.EDIT:
            case Post.Page.REPLY:
                $(".mediatextarea-post-" + e.postId).val(e.media.id);
                break;
        }
    };

    Post.prototype._onSuccessAndPost = function(e) {
        switch (this.page) {
            case Post.Page.EDIT:
            case Post.Page.REPLY:
                $(".mediatextarea-post-" + e.postId).val(e.media.id);
                $("#submitReplyPost" + e.postId).trigger("click");
                break;
        }
    };

    Post.prototype._onReset = function(e) {
        switch (this.page) {
            case Post.Page.EDIT:
            case Post.Page.REPLY:
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
            mediaUrl: Routing.generate('imdc_myfiles_preview', { mediaId: this.mediaChooser.media.id }),
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
