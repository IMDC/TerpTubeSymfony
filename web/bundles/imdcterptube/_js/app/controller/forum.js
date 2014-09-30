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

    Forum.prototype.getForm = function() {
        return this.getContainer().find("form[name=" + Forum.FORM_NAME + "]");
    };

    Forum.prototype.getFormField = function(fieldName) {
        return this.getContainer().find("#" + Forum.FORM_NAME + "_" + fieldName);
    };

    Forum.prototype.bindUIEvents = function() {
        console.log("%s: %s", Forum.TAG, "bindUIEvents")

        this.mediaChooser = new MediaChooser();
        $(this.mediaChooser).on(MediaChooser.Event.PAGE_LOADED, this.bind__onPageLoaded);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.setContainer(this.getContainer());
        this.mediaChooser.bindUIEvents();
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
