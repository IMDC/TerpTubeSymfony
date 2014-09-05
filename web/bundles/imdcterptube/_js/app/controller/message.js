define(function() {
    var Message = function() {
        this.page = null;
    }

    Message.TAG = "Message";

    Message.Page = {
        NEW: 0,
        REPLY: 1,
        VIEW: 2
    };

    /**
     * ui element event bindings in order of appearance
     * @param {number} page
     */
    Message.prototype.bindUIEvents = function(page) {
        console.log("%s: %s- page=%d", Message.TAG, "bindUIEvents", page);

        this.page = page;

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
