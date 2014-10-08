define(['core/mediaChooser'], function(MediaChooser) {
    "use strict";

    var Message = function(options) {
        console.log("%s: %s- options=%o", Message.TAG, "constructor", options);

        this.page = options.page;
        this.mediaChooser = null;

        this.bind__onPageLoaded = this._onPageLoaded.bind(this);
        this.bind__onSuccess = this._onSuccess.bind(this);
        this.bind__onReset = this._onReset.bind(this);

        $tt._instances.push(this);
    };

    Message.TAG = "Message";

    Message.Page = {
        NEW: 0,
        REPLY: 1,
        VIEW: 2
    };

    // this must be the same name defined in {bundle}/Form/Type/PrivateMessageType
    Message.FORM_NAME = "PrivateMessageForm";

    Message.prototype.getContainer = function() {
        return $("body");
    };

    Message.prototype.getForm = function() {
        return this.getContainer().find("form[name=" + Message.FORM_NAME + "]");
    };

    Message.prototype.getFormField = function(fieldName) {
        return this.getContainer().find("#" + Message.FORM_NAME + "_" + fieldName);
    };

    Message.prototype.bindUIEvents = function() {
        console.log("%s: %s", Message.TAG, "bindUIEvents");

        switch (this.page) {
            case Message.Page.NEW:
            case Message.Page.REPLY:
                this._bindUIEventsNewReply(this.page == Message.Page.REPLY);
                break;
            case Message.Page.VIEW:
                this._bindUIEventsView();
                break;
        }
    };

    Message.prototype._bindUIEventsNewReply = function(isReply) {
        console.log("%s: %s- isReply=%s", Message.TAG, "_bindUIEventsNewReply", isReply);

        this.mediaChooser = new MediaChooser();
        $(this.mediaChooser).on(MediaChooser.Event.PAGE_LOADED, this.bind__onPageLoaded);
        $(this.mediaChooser).on(MediaChooser.Event.SUCCESS, this.bind__onSuccess);
        $(this.mediaChooser).on(MediaChooser.Event.RESET, this.bind__onReset);
        this.mediaChooser.setContainer(this.getContainer());
        this.mediaChooser.bindUIEvents();

        this.getFormField("recipients").tagit();
    };

    Message.prototype._bindUIEventsView = function() {
        console.log("%s: %s", Message.TAG, "_bindUIEventsView");

        var messageId = $(".tt-message-div").first().data("mid");

        $.post(
            Routing.generate('imdc_message_mark_as_read', {'messageid': messageId}),
            {
                msgId: messageId
            },
            function(data) {
                console.log("%s: %s: data=%o", "mark message as read", "success", data);
            }
        );
    };

    Message.prototype._onPageLoaded = function(e) {
        console.log("%s: %s", Message.TAG, "_onPageLoaded");

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

    Message.prototype._onSuccess = function(e) {
        this.getFormField("mediatextarea").val(e.media.id);
    };

    Message.prototype._onReset = function(e) {
        this.getFormField("mediatextarea").val("");
    };

    return Message;
});
