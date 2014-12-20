define([
    'chai',
    'service/keyPointService'
], function (chai, KeyPointService) {
    'use strict';

    var expect = chai.expect;

    describe('KeyPointService', function () {

        var keyPoints;
        var args;
        var callbackResult;
        var callback;
        var keyPointService;

        before(function () {
            keyPoints = [
                {id: 11},
                {id: 12}
            ];
            args = {foo: 'bar'};
            callback = function (e) {
                callbackResult += e.foo;
            };
            keyPointService = new KeyPointService();
        });

        beforeEach(function () {
            callbackResult = '';
        });

        it('should have returned single key point key', function () {
            var kIndex = KeyPointService._kIndex(keyPoints[0].id);
            expect(kIndex).to.equal('K' + keyPoints[0].id);
        });

        it('should have returned "all" key point key', function () {
            var kIndex = KeyPointService._kIndex('all');
            expect(kIndex).to.equal('all');
        });

        it('should have registered key point', function () {
            var kIndex = KeyPointService._kIndex(keyPoints[0].id);
            keyPointService.register(keyPoints[0]);

            expect(keyPointService.keyPoints[kIndex]).to.equal(keyPoints[0]);
        });

        it('should have deregistered key point', function () {
            keyPointService.deregister(keyPoints[0].id);
            expect(_.size(keyPointService.keyPoints)).to.equal(0);
        });

        it('should have subscribed to key point', function () {
            var kIndex = KeyPointService._kIndex(keyPoints[0].id);
            keyPointService.subscribe(keyPoints[0].id, callback);

            expect(keyPointService.subscriptions[kIndex][0]).to.equal(callback);
        });

        it('should have unsubscribed from key point', function () {
            keyPointService.unsubscribe(keyPoints[0].id, callback);
            expect(_.size(keyPointService.subscriptions)).to.equal(0);
        });

        it('should have dispatched single key point with args', function () {
            keyPointService.register(keyPoints[0]);
            keyPointService.subscribe(keyPoints[0].id, callback);

            keyPointService.dispatch(keyPoints[0].id, KeyPointService.Event.TIMELINE, args);

            keyPointService.unsubscribe(keyPoints[0].id, callback);
            keyPointService.deregister(keyPoints[0].id);

            expect(callbackResult).to.equal(args.foo);
        });

        it('should have dispatched all key points with args', function () {
            keyPointService.register(keyPoints[0]);
            keyPointService.register(keyPoints[1]);
            keyPointService.subscribe('all', callback);

            keyPointService.dispatch('all', KeyPointService.Event.TIMELINE, args);

            keyPointService.unsubscribe('all', callback);
            keyPointService.deregister(keyPoints[1].id);
            keyPointService.deregister(keyPoints[0].id);

            expect(callbackResult).to.equal(args.foo + args.foo);
        });

        after(function () {
            keyPoints = null;
            args = null;
            callbackResult = null;
            callback = null;
            keyPointService = null;
        });

    });

});
