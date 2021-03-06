define([
    'chai',
    'q',
    'test/common',
    'service',
    'model/model',
    'model/threadModel',
    'controller/threadController',
    'service/keyPointService',
    'jquery',
    'jquery-mockjax',
    'fos_routes',
    'es5-shim'
], function (chai, Q, Common, Service, Model, ThreadModel, ThreadController, KeyPointService) {
    'use strict';

    var assert = chai.assert;

    describe('ThreadController', function () {

        window.$tt = {};
        $tt._services = [];
        $tt._instances = [];

        var keyPoint;
        var kpsCallbackResult;
        var kpsCallback;
        var keyPointService;
        var mCallbackResult;
        var mCallback;
        var model;
        var pageUrl;
        var controller;

        before(function () {
            keyPoint = new KeyPoint(1, 1.00, 4.00, '', {drawOnTimeLine: true});
            kpsCallback = function (e) {
                kpsCallbackResult = e;
            };
            keyPointService = Service.get('keyPoint');
            keyPointService.register(keyPoint);
            keyPointService.subscribe('all', kpsCallback);
            mCallback = function (e) {
                mCallbackResult = e;
            };
            model = new ThreadModel({
                id: 1
            });
            model.subscribe(Model.Event.CHANGE, mCallback);
            $.mockjaxSettings.logging = false;

            // override to prevent page reloads
            window.location.assign = function(url) {
                pageUrl = url;
            };
        });

        beforeEach(function () {
            kpsCallbackResult = null;
            mCallbackResult = null;
            pageUrl = undefined;
            $.mockjax.clear();
        });

        it('should have instantiated', function () {
            controller = new ThreadController(model, {});
            assert.isArray(controller.model.get('keyPoints'), 'key points should be an array');
            assert.lengthOf(controller.model.get('keyPoints'), 0, 'key points array should be empty');
        });

        it('should have subscribed to "all" key point events', function () {
            controller.onViewLoaded();
            assert.lengthOf(keyPointService.subscriptions['all'], 2, 'controller should have subscribed to key point events');
        });

        it('should have added key point', function () {
            keyPointService.dispatch(keyPoint.id, KeyPointService.Event.ADD);
            assert.deepEqual(mCallbackResult.model.get(mCallbackResult.keyPath), keyPoint, 'key points should deeply equal');
        });

        it('should not have dispatched "duration" key point event', function () {
            controller.updateKeyPointDuration('non duration');
            assert.isNull(kpsCallbackResult, 'key point event should be null');
        });

        it('should have dispatched "duration" key point event', function () {
            var duration = 10.00;
            var kIndex = KeyPointService._kIndex(model.get('keyPoints.0.id'));
            controller.updateKeyPointDuration(duration);

            assert.equal(kpsCallbackResult.type, KeyPointService.Event.DURATION, 'key point event type should equal');
            assert.equal(kpsCallbackResult.keyPoints[kIndex], keyPoint, 'key point should equal');
            assert.equal(kpsCallbackResult.duration, duration, 'key:duration should equal');
        });

        it('should have dispatched "selection times" key point event', function () {
            var kIndex = KeyPointService._kIndex(model.get('keyPoints.0.id'));
            controller.updateKeyPointSelectionTimes({minTime: 1.3456, maxTime: 4.346});

            assert.equal(kpsCallbackResult.type, KeyPointService.Event.SELECTION_TIMES, 'key point event type should equal');
            assert.equal(kpsCallbackResult.keyPoints[kIndex], keyPoint, 'key point should equal');
            assert.deepEqual(kpsCallbackResult.selection, {startTime: '1.35', endTime: '4.35'}, 'key:selection should deeply equal');
        });

        it('should have dispatched "hover" (true) key point event', function () {
            controller.hoverKeyPoint(keyPoint.id, {isMouseOver: true});

            assert.equal(kpsCallbackResult.type, KeyPointService.Event.HOVER, 'key point event type should equal');
            assert.equal(kpsCallbackResult.keyPoint, keyPoint, 'key point should equal');
            assert.isTrue(kpsCallbackResult.isMouseOver, 'key:isMouseOver should be true');

            assert.isTrue(mCallbackResult.model.get('keyPoints.0.isPlayerHovering'), 'key point property should be true');
        });

        it('should have dispatched "hover" (false) key point event', function () {
            controller.hoverKeyPoint(keyPoint.id, {isMouseOver: false});

            assert.equal(kpsCallbackResult.type, KeyPointService.Event.HOVER, 'key point event type should equal');
            assert.equal(kpsCallbackResult.keyPoint, keyPoint, 'key point should equal');
            assert.isFalse(kpsCallbackResult.isMouseOver, 'key:isMouseOver should be false');

            assert.isFalse(mCallbackResult.model.get('keyPoints.0.isPlayerHovering'), 'key point property should be false');
        });

        it('should have dispatched "right click" key point event', function () {
            controller.rightClickKeyPoint(keyPoint.id);

            assert.equal(kpsCallbackResult.type, KeyPointService.Event.CLICK, 'key point event type should equal');
            assert.equal(kpsCallbackResult.keyPoint, keyPoint, 'key point should equal');

            assert.isTrue(mCallbackResult.model.get('keyPoints.0.isPlayerPlaying'), 'key point property should be true');

            var promise = Q.promise(function(resolve) {
                setTimeout(function () {
                    resolve(mCallbackResult.model.get('keyPoints.0.isPlayerPlaying'));
                }, 200);
            });
            return assert.eventually.isFalse(promise, 'key point property should be false');
        });

        it('should have started editing key point', function () {
            keyPointService.dispatch(keyPoint.id, KeyPointService.Event.EDIT, {cancel: false});

            assert.isFalse(mCallbackResult.model.get('keyPoints.0.options.drawOnTimeLine'), 'key point property should be false');
            assert.isTrue(mCallbackResult.model.get('keyPoints.0.isEditing'), 'key point property should be true');
        });

        it('should have stopped editing key point', function () {
            keyPointService.dispatch(keyPoint.id, KeyPointService.Event.EDIT, {cancel: true});

            assert.isFalse(mCallbackResult.model.get('keyPoints.0.isEditing'), 'key point property should be false');
            assert.isTrue(mCallbackResult.model.get('keyPoints.0.options.drawOnTimeLine'), 'key point property should be true');
        });

        it('should have removed key point', function () {
            keyPointService.dispatch(keyPoint.id, KeyPointService.Event.REMOVE);

            var promise = Q.promise(function(resolve) {
                setTimeout(function () {
                    resolve(mCallbackResult.model.get(mCallbackResult.keyPath));
                }, 200);
            });
            return assert.eventually.lengthOf(promise, 0, 'key point array should be empty');
        });

        //TODO controller.adjustVideoSpeed()

        it('should not have redirected', function (done) {
            $.mockjax({
                url: Routing.generate('imdc_delete_thread', {threadId: model.get('id')}),
                status: 400
            });

            return controller.delete()
                .done(function (data) {
                    assert.fail('done', 'fail', 'request should have failed');
                    done();
                })
                .fail(function (data) {
                    assert.isUndefined(pageUrl, 'pageUrl should be undefined');
                    done();
                });
        });

        it('should have redirected', function (done) {
            $.mockjax({
                url: Routing.generate('imdc_delete_thread', {threadId: model.get('id')}),
                responseText: {
                    redirect_url: Common.BASE_URL + '/forum/'
                }
            });

            return controller.delete()
                .done(function (data) {
                    assert.equal(pageUrl, data.redirect_url, 'pageUrl should equal key:redirect_url');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        after(function () {
            keyPointService.unsubscribe('all', kpsCallback);
            keyPointService.deregister(keyPoint);
            keyPoint = null;
            kpsCallbackResult = null;
            kpsCallback = null;
            keyPointService = null;
            mCallbackResult = null;
            mCallback = null;
            model = null;
            pageUrl = null;
            controller = null;
        });

    });

});
