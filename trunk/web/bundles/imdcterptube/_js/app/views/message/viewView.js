define([], function () {
    'use strict';

    var ViewView = function (controller, options) {
        this.controller = controller;

        this.$container = options.container;

        //TODO gallery

        //TODO move to message factory
        //TODO use message model
        var messageId = $(".tt-message-div").first().data("mid");

        $.post(
            Routing.generate('imdc_message_mark_as_read', {'messageid': messageId}),
            {
                msgId: messageId
            },
            function (data) {
                console.log("%s: %s: data=%o", "mark message as read", "success", data);
            }
        );

        $tt._instances.push(this);
    };

    ViewView.TAG = 'MessageViewView';

    return ViewView;
});
