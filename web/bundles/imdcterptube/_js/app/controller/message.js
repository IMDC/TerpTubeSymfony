define(function() {
    var Message = function() {

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
    Message.bindUIEvents = function(page) {
        console.log("%s: %s- page=%d", Message.TAG, "bindUIEvents", page);

        switch (page) {
            case Message.Page.NEW:
            case Message.Page.REPLY:
                Message._bindUIEventsNewReply(page);
                break;
            case Message.Page.VIEW:
                Message._bindUIEventsView();
                break;
        }
    };

    Message._bindUIEventsNewReply = function(page) {
        console.log("%s: %s- page=%d", Message.TAG, "_bindUIEventsNewReply", page);

        var prefix = page == Message.Page.REPLY ? "PrivateMessageReplyForm" : "PrivateMessageForm";

        $("#" + prefix + "_recipients").tagit();

        $("#showMediaButton").on("click", function(e) {
            e.preventDefault();

            $("#attachedMediaContainer").toggle();
        });
    };

    Message._bindUIEventsView = function() {
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
