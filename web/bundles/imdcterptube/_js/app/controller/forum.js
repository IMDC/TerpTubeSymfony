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

        var mediaIds = new Array();
        this.getFormField("titleMedia").children().each(function(index, element) {
            mediaIds.push($(element).val());
        });

        this.mediaChooser = new MediaChooser();
        $(this.mediaChooser).on(MediaChooser.Event.PAGE_LOADED, this.bind__onPageLoaded);
        //$(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        //$(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.setContainer(this.getContainer());
        this.mediaChooser.setMedia(mediaIds);
        this.mediaChooser.bindUIEvents();

        this.getForm().find("input:radio").on("change", (function(e) {
            var group = this.getFormField("group");
            var parent = group.parent();

            if ($(e.target).attr("id") == this.getForm().find("input:radio[value=6]").attr("id")) {
                parent.find("label").addClass("required");
                group.attr("required", "required")
                parent.children().show();
            } else {
                parent.find("label").removeClass("required");
                group.removeAttr("required");
                parent.children().hide();
            }
        }).bind(this));

        this.getForm().find("input:radio:checked").trigger("change");

        this._getElement(Forum.Binder.SUBMIT).on("click", (function(e) {
            e.preventDefault();

            var formField = this.getFormField("titleMedia");
            formField.html(
                this.mediaChooser.generateFormData(
                    formField.data("prototype")
                )
            );

            this.getForm().submit();
        }).bind(this));
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
        this.getFormField("mediatextarea").val(e.media.id);
    };

    Forum.prototype._onReset = function(e) {
        this.getFormField("mediatextarea").val("");
    };

    return Forum;
});
