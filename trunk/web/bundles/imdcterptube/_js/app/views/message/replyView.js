define([
    'views/message/newView'
], function (NewView) {
    'use strict';

    var ReplyView = function (controller, options) {
        NewView.prototype.constructor.apply(this, arguments);
    };

    ReplyView.extend(NewView);

    ReplyView.TAG = 'MessageReplyView';

    return ReplyView;
});
