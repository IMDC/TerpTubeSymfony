define(function() {
    "use strict";

    var Message = function(options) {
        console.log("%s: %s- options=%o", Message.TAG, "constructor", options);

        this.page = options.page;

        $tt._instances.push(this);
    };

    Message.TAG = "Message";

    Message.Page = {
        NEW: 0,
        REPLY: 1,
        VIEW: 2
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

        var prefix = isReply ? "PrivateMessageReplyForm" : "PrivateMessageForm";

        $("#" + prefix + "_recipients").tagit();

        $("#showMediaButton").on("click", function(e) {
            e.preventDefault();

            $("#attachedMediaContainer").toggle();
        });
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

    return Message;
});
