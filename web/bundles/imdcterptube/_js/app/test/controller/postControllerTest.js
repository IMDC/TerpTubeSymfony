define([
    'chai',
    'test/common',
    'service',
    'model/model',
    'model/postModel',
    'controller/postController',
    'service/keyPointService',
    'jquery',
    'jquery-mockjax',
    'fos_routes',
    'es5-shim'
], function (chai, Common, Service, Model, PostModel, PostController, KeyPointService) {
    'use strict';

    var assert = chai.assert;

    describe('PostController', function () {

        window.$tt = {};
        $tt._services = [];
        $tt._instances = [];

        var kpsCallbackResult;
        var kpsCallback;
        var keyPointService;
        var mCallbackResult;
        var mCallback;
        var model;
        var pageUrl;
        var controller;

        before(function () {
            kpsCallback = function (e) {
                kpsCallbackResult = e;
            };
            keyPointService = Service.get('keyPoint');
            keyPointService.subscribe('all', kpsCallback);
            mCallback = function (e) {
                mCallbackResult = e;
            };
            model = new PostModel({
                id: '0' + Math.floor((Math.random() * 100000) + 1),
                startTime: 100,
                endTime: 200,
                isTemporal: true,
                threadId: 17
            });
            model.subscribe(Model.Event.CHANGE, mCallback);
            $.mockjaxSettings.logging = false;

            // override to prevent page reloads
            window.location.replace = function(url) {
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
            controller = new PostController(model, {});
            assert.equal(controller.model.get('keyPoint.startTime'), model.get('startTime'), 'key point start times should equal');
        });

        it('should have registered and subscribed key point, and dispatched "add" key point event', function () {
            controller.onViewLoaded();

            var kIndex = KeyPointService._kIndex(model.get('id'));
            var keyPoint = keyPointService.keyPoints[kIndex];
            assert.lengthOf(keyPointService.subscriptions[kIndex], 1, 'controller should have subscribed to key point events');
            assert.equal(keyPoint, model.get('keyPoint'), 'key point should have registered');

            assert.equal(kpsCallbackResult.type, KeyPointService.Event.ADD, 'key point event type should equal');
            assert.equal(kpsCallbackResult.keyPoint, model.get('keyPoint'), 'key point should equal');
        });

        it('should have changed key point video duration', function () {
            var duration = 10.00;
            keyPointService.dispatch(model.get('keyPoint.id'), KeyPointService.Event.DURATION, {duration: duration});

            assert.equal(mCallbackResult.model.get(mCallbackResult.keyPath), duration, 'key point property should match');
        });

        it('should have changed key point selection', function () {
            var selection = {startTime: '1.35', endTime: '4.35'};
            keyPointService.dispatch(model.get('keyPoint.id'), KeyPointService.Event.SELECTION_TIMES, {selection: selection});

            assert.equal(mCallbackResult.model.get(mCallbackResult.keyPath), selection, 'key point property should match');
        });

        it('should have dispatched "hover" (true) key point event', function () {
            controller.hoverKeyPoint({isMouseOver: true});

            assert.equal(kpsCallbackResult.type, KeyPointService.Event.HOVER, 'key point event type should equal');
            assert.equal(kpsCallbackResult.keyPoint, model.get('keyPoint'), 'key point should equal');
            assert.isTrue(kpsCallbackResult.isMouseOver, 'key:isMouseOver should be true');

            assert.isTrue(mCallbackResult.model.get('keyPoint.isHovering'), 'key point property should be true');
        });

        it('should have dispatched "hover" (false) key point event', function () {
            controller.hoverKeyPoint({isMouseOver: false});

            assert.equal(kpsCallbackResult.type, KeyPointService.Event.HOVER, 'key point event type should equal');
            assert.equal(kpsCallbackResult.keyPoint, model.get('keyPoint'), 'key point should equal');
            assert.isFalse(kpsCallbackResult.isMouseOver, 'key:isMouseOver should be false');

            assert.isFalse(mCallbackResult.model.get('keyPoint.isHovering'), 'key point property should be false');
        });

        it('should have dispatched "click" key point event', function (done) {
            controller.clickKeyPoint({isDblClick: false});

            assert.equal(kpsCallbackResult.type, KeyPointService.Event.CLICK, 'key point event type should equal');
            assert.equal(kpsCallbackResult.keyPoint, model.get('keyPoint'), 'key point should equal');
            assert.isFalse(kpsCallbackResult.isDblClick, 'key:isDblClick should be false');

            assert.isTrue(mCallbackResult.model.get('keyPoint.isSeeking'), 'key point property should be true');
            setTimeout(function () {
                assert.isFalse(mCallbackResult.model.get('keyPoint.isSeeking'), 'key point property should be false');
                done();
            }, 200);
        });

        it('should have dispatched "click" (double) key point event', function (done) {
            controller.clickKeyPoint({isDblClick: true});

            assert.equal(kpsCallbackResult.type, KeyPointService.Event.CLICK, 'key point event type should equal');
            assert.equal(kpsCallbackResult.keyPoint, model.get('keyPoint'), 'key point should equal');
            assert.isTrue(kpsCallbackResult.isDblClick, 'key:isDblClick should be true');

            assert.isTrue(mCallbackResult.model.get('keyPoint.isPlaying'), 'key point property should be true');
            setTimeout(function () {
                assert.isFalse(mCallbackResult.model.get('keyPoint.isPlaying'), 'key point property should be false');
                done();
            }, 200);
        });

        it('should have dispatched "edit" key point event', function () {
            controller.editKeyPoint({cancel: false});

            assert.equal(kpsCallbackResult.type, KeyPointService.Event.EDIT, 'key point event type should equal');
            assert.equal(kpsCallbackResult.keyPoint, model.get('keyPoint'), 'key point should equal');
            assert.isFalse(kpsCallbackResult.cancel, 'key:cancel should be false');
        });

        it('should have dispatched "edit" (cancel) key point event', function () {
            controller.editKeyPoint({cancel: true});

            assert.equal(kpsCallbackResult.type, KeyPointService.Event.EDIT, 'key point event type should equal');
            assert.equal(kpsCallbackResult.keyPoint, model.get('keyPoint'), 'key point should equal');
            assert.isTrue(kpsCallbackResult.cancel, 'key:cancel should be true');
        });

        it('should have dispatched "remove" key point event, and unsubscribed and deregistered key point', function () {
            controller.removeKeyPoint();

            assert.equal(kpsCallbackResult.type, KeyPointService.Event.REMOVE, 'key point event type should equal');
            assert.equal(kpsCallbackResult.keyPoint, model.get('keyPoint'), 'key point should equal');
        });

        it('should not have redirected', function () {
            $.mockjax({
                url: Routing.generate('imdc_post_new', {threadId: model.get('threadId'), pid: model.get('id')}),
                responseText: {
                    wasReplied: false,
                    html: ''
                }
            });

            return controller.new()
                .done(function (data) {
                    assert.isUndefined(pageUrl, 'pageUrl should be null');
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                });
        });

        it('should have redirected', function () {
            $.mockjax({
                url: Routing.generate('imdc_post_new', {threadId: model.get('threadId'), pid: model.get('id')}),
                responseText: {
                    wasReplied: true,
                    post: {},
                    redirectUrl: Common.BASE_URL + '/thread/' + model.get('threadId')
                }
            });

            return controller.new()
                .done(function (data) {
                    assert.equal(pageUrl, data.redirectUrl, 'pageUrl should equal key:redirectUrl');
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                });
        });

        it('should not have edited', function () {
            $.mockjax({
                url: Routing.generate('imdc_post_edit', {pid: model.get('id')}),
                responseText: {
                    wasEdited: false,
                    html: ''
                }
            });

            return controller.edit()
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'wasEdited', 'result should have key:wasEdited');

                    assert.isFalse(data.wasEdited, 'key:wasEdited should be false');
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                });
        });

        it('should have edited', function () {
            $.mockjax({
                url: Routing.generate('imdc_post_edit', {pid: model.get('id')}),
                responseText: {
                    wasEdited: true,
                    post: {},
                    html: ''
                }
            });

            return controller.edit()
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'wasEdited', 'result should have key:wasEdited');

                    assert.isTrue(data.wasEdited, 'key:wasEdited should be true');
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                });
        });

        it('should not have deleted and deregistered and dispatched "remove" key point event', function () {
            $.mockjax({
                url: Routing.generate('imdc_post_delete', {pid: model.get('id')}),
                responseText: {
                    wasDeleted: false
                }
            });

            // don't return promise
            controller.delete()
                .done(function (data) {
                    assert.fail('done', 'fail', 'request should have failed');
                })
                .fail(function () {
                    assert.isNull(kpsCallbackResult, 'key point event should be null');

                    var kIndex = KeyPointService._kIndex(model.get('id'));
                    var keyPoint = keyPointService.keyPoints[kIndex];
                    assert.equal(keyPoint, controller.model.get('keyPoint'), 'key point should still be registered');
                });
        });

        it('should have deleted and deregistered and dispatched "remove" key point event', function () {
            $.mockjax({
                url: Routing.generate('imdc_post_delete', {pid: model.get('id')}),
                responseText: {
                    wasDeleted: true
                }
            });

            return controller.delete()
                .done(function (data) {
                    assert.equal(kpsCallbackResult.type, KeyPointService.Event.REMOVE, 'key point event type should equal');
                    assert.equal(kpsCallbackResult.keyPoint, model.get('keyPoint'), 'key point should equal');
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                });
        });

        after(function () {
            keyPointService.unsubscribe('all', kpsCallback);
            keyPointService = null;
            kpsCallback = null;
            kpsCallbackResult = null;
            mCallbackResult = null;
            mCallback = null;
            model = null;
            pageUrl = null;
            controller = null;
        });

    });

});
