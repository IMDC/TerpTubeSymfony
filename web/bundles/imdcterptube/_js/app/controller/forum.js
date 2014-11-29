define(['core/mediaChooser'], function(MediaChooser) {
    "use strict";

    var Forum = function(options) {
        this.page = options.page;
        this.id = options.id;

        this.mediaChooser = null;

        this.bind__onChangeAccessType = this._onChangeAccessType.bind(this);
        this.bind__onClickSubmit = this._onClickSubmit.bind(this);
        this.bind__onClickDelete = this._onClickDelete.bind(this);
        this.bind__onDeleteSuccess = this._onDeleteSuccess.bind(this);
        this.bind__onDeleteError = this._onDeleteError.bind(this);
        this.bind__onPageLoaded = this._onPageLoaded.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);

        $tt._instances.push(this);
    };

    Forum.TAG = "Forum";

    Forum.Page = {
        NEW: 0,
        EDIT: 1,
        DELETE: 2,
        VIEW: 3
    };

    Forum.Binder = {
        SUBMIT: ".forum-submit",
        DELETE_MODAL: ".forum-delete-modal",
        DELETE: ".forum-delete"
    };

    // this must be the same name defined in {bundle}/Form/Type/ForumType
    Forum.FORM_NAME = "forum";

    Forum.prototype.getContainer = function() {
        return $("body");
    };

    Forum.prototype._getElement = function(binder) {
        return this.getContainer().find(binder);
    };

    Forum.prototype.getForm = function() {
        return this.getContainer().find("form[name=" + Forum.FORM_NAME + "]");
    };

    Forum.prototype.getFormField = function(fieldName) {
        return this.getContainer().find("#" + Forum.FORM_NAME + "_" + fieldName);
    };

    Forum.prototype.bindUIEvents = function() {
        console.log("%s: %s", Forum.TAG, "bindUIEvents");

        switch (this.page) {
            case Forum.Page.NEW:
            case Forum.Page.EDIT:
                this._bindUIEventsNewEdit();
                break;
            case Forum.Page.VIEW:
                this._bindUIEventsView();
                break;
        }
    };

    Forum.prototype._bindUIEventsNewEdit = function() {
        console.log("%s: %s", Forum.TAG, "_bindUIEventsNewEdit");

        this.mediaChooser = new MediaChooser();
        $(this.mediaChooser).on(MediaChooser.Event.PAGE_LOADED, this.bind__onPageLoaded);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.setContainer(this.getContainer());
        this.mediaChooser.bindUIEvents();

        var mediaIds = [];
        this.getFormField("titleMedia").children().each(function(index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this._getElement(Forum.Binder.SUBMIT).attr("disabled", true);
            this.mediaChooser.setMedia(mediaIds);
        }

        this.getForm().find("input:radio").on("change", this.bind__onChangeAccessType);
        this._getElement(Forum.Binder.SUBMIT).on("click", this.bind__onClickSubmit);
        this._getElement(Forum.Binder.DELETE).on("click", this.bind__onClickDelete);

        this.getForm().find("input:radio:checked").trigger("change");
    };

    Forum.prototype._bindUIEventsView = function() {
        console.log("%s: %s", Forum.TAG, "_bindUIEventsView");

        this.gallery = new $tt.Core.Gallery({container: $(".gallery-container")});
        this.gallery.bindUIEvents();
    };

    Forum.prototype._onChangeAccessType = function(e) {
        var group = this.getFormField("group");
        var parent = group.parent();

        if ($(e.target).attr("id") == this.getForm().find("input:radio[value=6]").attr("id")) {
            parent.find("label").addClass("required");
            group.attr("required", true);
            parent.children().show();
        } else {
            parent.find("label").removeClass("required");
            group.attr("required", false);
            parent.children().hide();
        }
    };

    Forum.prototype._onClickSubmit = function(e) {
        $(e.target).button("loading");
    };

    Forum.prototype._onClickDelete = function(e) {
        $(e.target).button("loading");

        $.ajax({
            url: Routing.generate("imdc_forum_delete", {forumid: this.id}),
            type: "POST",
            success: this.bind__onDeleteSuccess,
            error: this.bind__onDeleteError
        });
    };

    Forum.prototype._onDeleteSuccess = function(data, textStatus, jqXHR) {
        if (!data.wasDeleted) {
            this._onDeleteError(jqXHR, textStatus, null);
            return;
        }

        this._getElement(Forum.Binder.DELETE_MODAL)
            .find(".modal-body")
            .html("Forum deleted successfully.");

        window.location.assign(data.redirectUrl);
    };

    Forum.prototype._onDeleteError = function(jqXHR, textStatus, errorThrown) {
        this._getElement(Forum.Binder.DELETE_MODAL)
            .find(".modal-body")
            .prepend("Something went wrong. Try again.");

        this._getElement(Forum.Binder.DELETE).button("reset");
    };

    Forum.prototype._onPageLoaded = function(e) {
        console.log("%s: %s", Forum.TAG, "_onPageLoaded");

        switch (this.mediaChooser.page) {
            case MediaChooser.Page.RECORD_VIDEO:
                this.mediaChooser.createVideoRecorder();
                break;
            case MediaChooser.Page.PREVIEW:
                if (e.payload.media.type == MediaChooser.MEDIA_TYPE.VIDEO.id)
                    this.mediaChooser.createVideoPlayer();

                break;
        }
    };

    Forum.prototype._onSuccess = function(e) {
        this._getElement(Forum.Binder.SUBMIT).attr("disabled", false);

        this.getFormField("titleText")
            .attr("required", false)
            .parent()
            .find("label")
            .removeClass("required");

        var formField = this.getFormField("titleMedia");
        formField.html(
            this.mediaChooser.generateFormData(
                formField.data("prototype")
            )
        );
    };

    Forum.prototype._onReset = function(e) {
        if (this.mediaChooser.media.length == 0)
            this.getFormField("titleText")
                .attr("required", true)
                .parent()
                .find("label")
                .addClass("required");
    };

    return Forum;
});
