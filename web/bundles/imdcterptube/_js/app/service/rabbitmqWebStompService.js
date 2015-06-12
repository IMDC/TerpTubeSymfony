define([
    'core/subscriber',
    'sockjs',
    'stomp'
], function (Subscriber, SockJS, Stomp) {
    'use strict';

    var RabbitmqWebStompService = function () {
        Subscriber.prototype.constructor.apply(this);

        this.ws = null;
        this.client = null;
        this.subscribeIds = [];

        this.bind__messageCallback = this._messageCallback.bind(this);
    };

    RabbitmqWebStompService.extend(Subscriber);

    RabbitmqWebStompService.Event = {
        CONNECTED: 'eventConnected',
        DISCONNECTED: 'eventDisconnected',
        MESSAGE: 'eventMessage'
    };

    RabbitmqWebStompService.prototype.connect = function () {
        if (this.ws && this.client)
            return;

        //TODO proper url
        this.ws = new SockJS('https://' + window.location.hostname + '/stomp');
        this.client = Stomp.over(this.ws);

        // SockJS does not support heart-beat: disable heart-beats
        this.client.heartbeat.incoming = 0;
        this.client.heartbeat.outgoing = 0;

        var onConnect = function () {
            this._dispatch(RabbitmqWebStompService.Event.CONNECTED);
        }.bind(this);

        var onError = function (error) {
            console.error(error);
        };

        //TODO proper credentials
        this.client.connect('test', 'test', onConnect, onError, '/');
    };

    RabbitmqWebStompService.prototype.disconnect = function () {

        this.client.disconnect(function () {
            this._dispatch(RabbitmqWebStompService.Event.DISCONNECTED);
        }.bind(this));

        this.subscribeIds = -1;
        this.client = null;
        this.ws = null;
    };

    RabbitmqWebStompService.prototype._messageCallback = function (msg) {
        console.log(msg);

        this._dispatch(RabbitmqWebStompService.Event.MESSAGE, {
            message: JSON.parse(msg.body)
        });
    };

    RabbitmqWebStompService.prototype.subscribe = function (destination, event, callback) {
        Subscriber.prototype.subscribe.call(this, event, callback);

        if (!destination)
            return;

        var subscribeId = this.client.subscribe(destination, this.bind__messageCallback);
        this.subscribeIds.push(subscribeId);

        return subscribeId;
    };

    RabbitmqWebStompService.prototype.unsubscribe = function (subscribeId, callback) {
        Subscriber.prototype.unsubscribe.call(this, callback);

        this.client.unsubscribe(subscribeId);

        //TODO remove subscribeId from this.subscribeIds
    };

    return RabbitmqWebStompService;
});
