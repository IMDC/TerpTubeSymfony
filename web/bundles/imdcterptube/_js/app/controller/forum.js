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

        //[{media: {id: this.getFormField("mediatextarea").val()}}]

        var media = new Array();
        this.getFormField("titleMedia").children().each(function(index, element) {
            media.push({
                id: $(element).val(),
                title: "",
                resource: {
                    pathMPEG: ""
                }
            });
        });

        this.mediaChooser = new MediaChooser({media: media});
        $(this.mediaChooser).on(MediaChooser.Event.PAGE_LOADED, this.bind__onPageLoaded);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.setContainer(this.getContainer());
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

        this._getElement(".forum-submit").on("click", (function(e) {
            e.preventDefault();

            var media = this.getFormField("titleMedia");
            var count = 0;

            media.html("");
            $(MediaChooser.Binder.SELECTED_MEDIA).each((function(key, element) {
                var newMedia = media.data("prototype");
                newMedia = newMedia.replace(/__name__/g, key);
                media.append(newMedia);
                this.getFormField("titleMedia_" + key).val($(element).data("mid"));
                count++;
            }).bind(this));

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
