define(['core/mediaChooser'], function(MediaChooser) {
    "use strict";

    var Post = function(options) {
        console.log("%s: %s- options=%o", Post.TAG, "constructor", options);

        var defaults = {
            id: -1
        };

        options = options || defaults;
        for (var o in defaults) {
            this[o] = typeof options[o] != "undefined" ? options[o] : defaults[o];
        }

        this.page = options.page;
        this.threadId = options.threadId;
        this.mediaChooser = null;

        this.bind__onClickSubmit = this._onClickSubmit.bind(this);
        this.bind__onSubmitSuccess = this._onSubmitSuccess.bind(this);
        this.bind__onSubmitError = this._onSubmitError.bind(this);
        this.bind__onClickCancel = this._onClickCancel.bind(this);
        this.bind__onClickReset = this._onClickReset.bind(this);
//        this.bind__onPageLoaded = this._onPageLoaded.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onSuccessAndPost = this._onSuccessAndPost.bind(this);
        this.bind__onReset = this._onReset.bind(this);

        $tt._instances.push(this);
    };

    Post.TAG = "Post";

    Post.Page = {
        NEW: 0,
        REPLY: 1,
        EDIT: 2,
        DELETE: 3
    };

    Post.Event = {
        FORM: "eventForm",
        SUBMIT_SUCCESS: "eventSubmitSuccess",
        RESET: "eventReset",
        CANCEL: "eventCancel"
    };

    Post.Binder = {
        CONTAINER: ".post-container",
        CONTAINER_VIEW: ".post-container-view",
        CONTAINER_REPLY: ".post-container-reply",
        CONTAINER_EDIT: ".post-container-edit",
        CONTAINER_DELETE: ".post-container-delete",
        REPLY_LINK: ".post-reply",
        SUBMIT: ".post-submit",
        RESET: ".post-reset",
        CANCEL: ".post-cancel",

        DELETE_MODAL: ".post-delete-modal",
        DELETE: ".post-delete"
    };

    // this must be the same name defined in {bundle}/Form/Type/PostType
    Post.FORM_NAME = "post";

    Post.prototype.getContainer = function() {
        return $(Post.Binder.CONTAINER + "[data-pid=" + this.id + "]");

        /*switch (this.page) {
            case Post.Page.NEW:
            case Post.Page.REPLY:
                return this._getElement(Post.Binder.CONTAINER_REPLY);
            case Post.Page.EDIT:
                return this._getElement(Post.Binder.CONTAINER_EDIT);
            case Post.Page.DELETE:
                return this._getElement(Post.Binder.CONTAINER);
        }*/
    };

    Post.prototype._getElement = function(binder) {
        //return $(binder + "[data-pid=" + this.id + "]");
        return this.getContainer().find(binder);
    };

    Post.prototype.getForm = function() {
        return this.getContainer().find("form[name=" + Post.FORM_NAME + "]");
    };

    Post.prototype.getFormField = function(fieldName) {
        return this.getForm().find("#" + Post.FORM_NAME + "_" + fieldName);
    };

    Post.prototype._getUrl = function() {
        switch (this.page) {
            case Post.Page.NEW:
                return Routing.generate('imdc_thread_new_post', {threadId: this.threadId});
            case Post.Page.REPLY:
                return Routing.generate('imdc_post_reply', {pid: this.id});
            case Post.Page.EDIT:
                return Routing.generate('imdc_post_edit', {pid: this.id});
            case Post.Page.DELETE:
                return Routing.generate('imdc_post_delete', {pid: this.id});
        }
    };

    Post.prototype.handlePage = function() {
        $.ajax({
            url: this._getUrl(),
            success: (function(data, textStatus, jqXHR) {
                console.log("%s: %s: %s", Post.TAG, "handlePage", "success");

                //var container = this.getContainer();

                switch (this.page) {
                    case Post.Page.REPLY:
                        this._getElement(Post.Binder.REPLY_LINK).hide();

                        var container = this._getElement(Post.Binder.CONTAINER_REPLY);
                        container.html(data.html);
                        container.show();
                        break;
                    case Post.Page.EDIT:
                        this._getElement(Post.Binder.CONTAINER_VIEW).hide();

                        var container = this._getElement(Post.Binder.CONTAINER_EDIT);
                        container.html(data.html);
                        container.show();
                        break;
                }

                $(this).trigger($.Event(Post.Event.FORM, {post: this}));
            }).bind(this),
            error: function(request) {
                console.log("%s: %s: %s", Post.TAG, "handlePage", "error");

                console.log(request.statusText);
            }
        });
    };

    Post.prototype.bindUIEvents = function() {
        console.log("%s: %s", Post.TAG, "bindUIEvents");

        this.mediaChooser = new MediaChooser({enableDoneAndPost: true});
//        $(this.mediaChooser).on(MediaChooser.Event.PAGE_LOADED, this.bind__onPageLoaded);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS_AND_POST, this.bind__onSuccessAndPost);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.setContainer(this.getContainer());
        this.mediaChooser.bindUIEvents();

        var mediaIds = [];
        this.getFormField("attachedFile").children().each(function(index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this._toggleForm(true);
            this.mediaChooser.setMedia(mediaIds);
        }

        this._getElement(Post.Binder.SUBMIT).on("click", this.bind__onClickSubmit);
        if (this.page != Post.Page.EDIT && this.page != Post.Page.DELETE)
            this._getElement(Post.Binder.RESET).on("click", this.bind__onClickReset);
        this._getElement(Post.Binder.CANCEL).on("click", this.bind__onClickCancel);
    };

    //thought this would of been useful at some point between page loads. guess not
//    Post.prototype._onPageLoaded = function(e) {
//        console.log("%s: %s", Post.TAG, "_onPageLoaded");
//
//        switch (this.mediaChooser.page) {
//            case MediaChooser.Page.RECORD_VIDEO:
//                this.mediaChooser.createVideoRecorder();
//                break;
//            case MediaChooser.Page.PREVIEW:
//                if (e.payload.media.type == MediaChooser.MEDIA_TYPE.VIDEO.id)
//                    this.mediaChooser.createVideoPlayer();
//
//                break;
//        }
//    };

    Post.prototype._onClickSubmit = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        if (this.page != Post.Page.DELETE &&
            this.getFormField("content").val() == "" && this.mediaChooser.media == null) {
            alert("Your post cannot be blank. You must either select a file or write a comment.");
            return;
        }

        this._toggleForm(true);

        if (this.page != Post.Page.DELETE) {
            var formField = this.getFormField("attachedFile");
            formField.html(
                this.mediaChooser.generateFormData(
                    formField.data("prototype")
                )
            );
        }

        if (this.page == Post.Page.DELETE) {
            $(e.target).button("loading");
        }

        $.ajax({
            url: this._getUrl(),
            type: "POST",
            contentType: false,
            data: new FormData(this.getForm()[0]),
            processData: false,
            success: this.bind__onSubmitSuccess,
            error: this.bind__onSubmitError
        });
    };

    Post.prototype._toggleForm = function(disabled) {
        this._getElement(Post.Binder.SUBMIT).button(disabled ? "loading" : "reset");
        if (this.page != Post.Page.EDIT && this.page != Post.Page.DELETE)
            this._getElement(Post.Binder.RESET).attr("disabled", disabled);
        this._getElement(Post.Binder.CANCEL).attr("disabled", disabled);
    };

    Post.prototype._onSubmitSuccess = function(data, textStatus, jqXHR) {
        console.log("%s: %s: %s", Post.TAG, "_onSubmitSuccess", "success");

        //var container = this.getContainer();

        switch (this.page) {
            case Post.Page.NEW:
            case Post.Page.REPLY:
                if (data.wasReplied) {
                    window.location.replace(data.redirectUrl);
                } else {
                    //container.html(data.html);
                    this._getElement(Post.Binder.CONTAINER_REPLY).html(data.html);
                    this.bindUIEvents();
                    this._toggleForm(false);
                }
                break;
            case Post.Page.EDIT:
                if (data.wasEdited) {
                    var viewContainer = this._getElement(Post.Binder.CONTAINER_VIEW);
                    viewContainer.data("starttime", data.startTime);
                    viewContainer.data("endtime", data.endTime);
                    viewContainer.data("istemporal", data.isTemporal);
                    viewContainer.html(data.html);

                    this._onClickCancel(); // simulate cancelling
                } else {
                    //container.html(data.html);
                    this._getElement(Post.Binder.CONTAINER_EDIT).html(data.html);
                    this.bindUIEvents();
                    this._toggleForm(false);
                }
                break;
            case Post.Page.DELETE:
                $(Post.Binder.DELETE).button("reset");

                $(Post.Binder.DELETE).off("click", this.bind__onClickSubmit); //FIXME temp fix

                if (!data.wasDeleted) {
                    break;
                }

                $(Post.Binder.DELETE_MODAL).modal("hide");

                this.getContainer().fadeOut("slow", function(e) {
                    $(this).remove();
                });

                break;
        }

        $(this).trigger($.Event(Post.Event.SUBMIT_SUCCESS, {post: this, payload: data}));
    };

    Post.prototype._onSubmitError = function(request) {
        console.log("%s: %s: %s", Post.TAG, "_onClickSubmit", "error");

        console.log(request.statusText);
        this._toggleForm(false);

        if (this.page == Post.Page.DELETE) {
            $(Post.Binder.DELETE).button("reset");
        }
    };

    Post.prototype._onClickReset = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        this.mediaChooser.reset();
        this.getFormField("content").val("");
        this.getFormField("startTime").val("");
        this.getFormField("endTime").val("");

        $(this).trigger($.Event(Post.Event.RESET, {post: this}));
    };

    Post.prototype._onClickCancel = function(e) {
        if (e && e.preventDefault)
            e.preventDefault();

        //var container = this.getContainer();

        switch (this.page) {
            case Post.Page.NEW:
            case Post.Page.REPLY:
                var container = this._getElement(Post.Binder.CONTAINER_REPLY);
                container.hide();
                container.html("");

                // restore the comment reply link if you click the cancel button
                this._getElement(Post.Binder.REPLY_LINK).show();
                break;
            case Post.Page.EDIT:
                var container = this._getElement(Post.Binder.CONTAINER_EDIT);
                container.hide();
                container.html("");

                this._getElement(Post.Binder.CONTAINER_VIEW).show();
                break;
        }

        $(this).trigger($.Event(Post.Event.CANCEL, {post: this}));
    };

    Post.prototype._updateForm = function() {
        var formField = this.getFormField("attachedFile");
        formField.html(
            this.mediaChooser.generateFormData(
                formField.data("prototype")
            )
        );
    };

    Post.prototype._onSuccess = function(e) {
        this._toggleForm(false);

        this._updateForm();
    };

    Post.prototype._onSuccessAndPost = function(e) {
        this._updateForm();

        this._getElement(Post.Binder.SUBMIT).trigger("click");
    };

    Post.prototype._onReset = function(e) {
        this._updateForm();
    };

    return Post;
});
