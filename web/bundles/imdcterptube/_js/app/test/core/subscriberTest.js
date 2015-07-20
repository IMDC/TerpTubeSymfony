define([
    'chai',
    'core/subscriber'
], function (chai, Subscriber) {
    'use strict';

    var expect = chai.expect;

    describe('Subscriber', function () {

        var callbackResult;
        var callback;
        var event;
        var args;
        var subscriber;

        before(function () {
            callback = function (e) {
                callbackResult = e;
            };
            event = 'test';
            args = {foo: 'bar'};
            subscriber = new Subscriber();
        });

        beforeEach(function () {
            callbackResult = undefined;
        });

        it('should have subscribed to event', function () {
            subscriber.subscribe(event, callback);
            var subs = subscriber.subscriptions[event];

            expect(subs).to.not.be.undefined;
            expect(subs[0]).to.not.be.undefined;
            expect(subs[0]).to.equal(callback);
        });

        it('should have dispatched event with args', function () {
            subscriber._dispatch(event, args);

            expect(callbackResult).to.not.be.undefined;
            expect(callbackResult.foo).to.equal(args.foo);
        });

        it('should have unsubscribed from event', function () {
            subscriber.unsubscribe(callback);
            expect(_.size(subscriber.subscriptions[event])).to.equal(0);
        });

        after(function () {
            callbackResult = null;
            callback = null;
            event = null;
            args = null;
            subscriber = null;
        });

    });

});
