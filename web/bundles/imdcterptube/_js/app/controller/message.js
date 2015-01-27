define([
    'component/mediaChooserComponent'
], function(MediaChooserComponent) {
    "use strict";

    var Message = function(options) {
        console.log("%s: %s- options=%o", Message.TAG, "constructor", options);

        this.page = options.page;
        this.mcCmp = null;

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

    Message.Binder = {
        SUBMIT: ".message-submit"
    };

    // this must be the same name defined in {bundle}/Form/Type/PrivateMessageType
    Message.FORM_NAME = "PrivateMessageForm";

    Message.prototype.getContainer = function() {
        return $("body");
    };

    Message.prototype._getElement = function(binder) {
        return this.getContainer().find(binder);
    };

    Message.prototype.getForm = function() {
        return this.getContainer().find("form[name=" + Message.FORM_NAME + "]");
    };

    Message.prototype.getFormField = function(fieldName) {
        return this.getForm().find("#" + Message.FORM_NAME + "_" + fieldName);
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

        this.mcCmp = MediaChooserComponent.render(this.getContainer());
        this.mcCmp.subscribe(MediaChooserComponent.Event.SUCCESS, this.bind__onSuccess);
        this.mcCmp.subscribe(MediaChooserComponent.Event.RESET, this.bind__onReset);

        var mediaIds = [];
        this.getFormField("attachedMedia").children().each(function(index, element) {
            mediaIds.push($(element).val());
        });
        if (mediaIds.length > 0) {
            this._getElement(Message.Binder.SUBMIT).attr("disabled", true);
            this.mcCmp.setMedia(mediaIds);
        }

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

    Message.prototype._updateForm = function() {
        var formField = this.getFormField("attachedMedia");
        formField.html(
            this.mcCmp.generateFormData(
                formField.data("prototype")
            )
        );
    };

    Message.prototype._onSuccess = function(e) {
        this._getElement(Message.Binder.SUBMIT).attr("disabled", false);

        this._updateForm();
    };

    Message.prototype._onReset = function(e) {
        this._updateForm();
    };

    return Message;
});
