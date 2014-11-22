define(['core/mediaChooser'], function(MediaChooser) {
    "use strict";

    var Forum = function(options) {
        this.page = options.page;
        this.mediaChooser = null;

        this.bind__onPageLoaded = this._onPageLoaded.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);

        $tt._instances.push(this);
    };

    Forum.TAG = "Forum";

    Forum.Page = {
        NEW: 0,
        EDIT: 1,
        DELETE: 2
    };

    Forum.Binder = {
        SUBMIT: ".forum-submit"
    };

    // this must be the same name defined in {bundle}/Form/Type/ForumFormType
    Forum.FORM_NAME = "ForumForm";

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

        this.getForm().find("input:radio").on("change", (function(e) {
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
        }).bind(this));

        this.getForm().find("input:radio:checked").trigger("change");
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
