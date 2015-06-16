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

        this.bind__messageCallback = this._messageCallback.bind(this);
    };

    RabbitmqWebStompService.extend(Subscriber);

    RabbitmqWebStompService.Event = {
        CONNECTED: 'eventConnected',
        DISCONNECTED: 'eventDisconnected',
        MESSAGE: 'eventMessage'
    };

    RabbitmqWebStompService.prototype.isConnected = function () {
        return (this.ws && this.client);
    };

    RabbitmqWebStompService.prototype.connect = function () {
        if (this.isConnected())
            return;

        this.ws = new SockJS(window.location.protocol + '//' + window.location.hostname + '/stomp');
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

        return this.client.subscribe(destination, this.bind__messageCallback);
    };

    RabbitmqWebStompService.prototype.unsubscribe = function (subscription, callback) {
        Subscriber.prototype.unsubscribe.call(this, callback);

        if (subscription)
            this.client.unsubscribe(subscription.id);
    };

    return RabbitmqWebStompService;
});
