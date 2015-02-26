define([
    'core/subscriber'
], function (Subscriber) {
    'use strict';

    var SubscriberService = function () {
        Subscriber.prototype.constructor.apply(this);
    };

    SubscriberService.extend(Subscriber);

    SubscriberService.prototype.dispatch = function (tag, event, args) {
        args = args || {};
        args.tag = tag;

        Subscriber.prototype._dispatch.call(this, event, args);
    };

    return SubscriberService;
});
