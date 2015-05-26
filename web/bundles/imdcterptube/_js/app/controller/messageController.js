define([
    'factory/messageFactory'
], function (MessageFactory) {
    'use strict';

    var Message = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', Message.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    Message.TAG = 'Message';

    Message.prototype.onViewLoaded = function () {
        this.markAsRead();
    };

    Message.prototype.markAsRead = function () {
        return MessageFactory.edit(this.model);
    };

    return Message;
});
