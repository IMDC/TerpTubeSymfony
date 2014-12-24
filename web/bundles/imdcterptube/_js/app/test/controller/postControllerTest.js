define([
    'chai',
    'test/common',
    'service',
    'model/postModel',
    'controller/postController',
    'service/keyPointService',
    'jquery',
    'jquery-mockjax',
    'fos_routes',
    'es5-shim'
], function (chai, Common, Service, PostModel, PostController, KeyPointService) {
    'use strict';

    var assert = chai.assert;

    describe('PostController', function () {

        window.$tt = {};
        $tt._services = [];
        $tt._instances = [];

        var keyPointService;
        var keyPointEvent;
        var post;
        var pageUrl;
        var controller;

        before(function () {
            keyPointService = Service.get('keyPoint');
            keyPointService.subscribe('all', function (e) {
                keyPointEvent = e;
            });
            post = new PostModel({
                id: '0' + Math.floor((Math.random() * 100000) + 1),
                startTime: 100,
                endTime: 200,
                isTemporal: true,
                threadId: 17
            });

            // override to prevent page reloads
            window.location.replace = function(url) {
                pageUrl = url;
            };
        });

        beforeEach(function () {
            keyPointEvent = null;
            pageUrl = undefined;
            $.mockjax.clear();
        });

        it('should have instantiated', function () {
            controller = new PostController(post, {});
            assert.equal(controller.model.get('keyPoint.startTime'), post.get('startTime'), 'key point start times should equal');
        });

        it('should have registered and dispatched "add" key point event', function () {
            controller.onViewLoaded();

            var kIndex = KeyPointService._kIndex(post.get('id'));
            var keyPoint = keyPointService.keyPoints[kIndex];
            assert.equal(keyPoint, controller.model.get('keyPoint'), 'key point should have registered');

            assert.equal(keyPointEvent.type, KeyPointService.Event.TIMELINE, 'key point event type should equal');
            assert.equal(keyPointEvent.keyPoint.id, post.get('id'), 'key point id should equal post id');
            assert.equal(keyPointEvent.action, 'add', 'key point event action should equal');
        });

        it('should not have redirected', function () {
            $.mockjax({
                url: Routing.generate('imdc_post_new', {threadId: post.get('threadId'), pid: post.get('id')}),
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
                url: Routing.generate('imdc_post_new', {threadId: post.get('threadId'), pid: post.get('id')}),
                responseText: {
                    wasReplied: true,
                    post: {},
                    redirectUrl: Common.BASE_URL + '/thread/' + post.get('threadId')
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

        it('should be editing', function () {
            $.mockjax({
                url: Routing.generate('imdc_post_edit', {pid: post.get('id')}),
                responseText: {
                    wasEdited: false,
                    html: ''
                }
            });

            return controller.edit()
                .done(function (data) {
                    assert.isTrue(controller.options.editing, 'controller "editing" should be true');
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                });
        });

        it('should have dispatched "cancel" key point event', function () {
            $.mockjax({
                url: Routing.generate('imdc_post_edit', {pid: post.get('id')}),
                responseText: {
                    wasEdited: true,
                    post: {},
                    html: ''
                }
            });

            return controller.edit()
                .done(function (data) {
                    assert.equal(keyPointEvent.type, KeyPointService.Event.TIMELINE, 'key point event type should equal');
                    assert.equal(keyPointEvent.keyPoint.id, post.get('id'), 'key point id should equal post id');
                    assert.equal(keyPointEvent.action, 'cancel', 'key point event action should equal');

                    assert.isFalse(controller.options.editing, 'controller "editing" should be false');
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                });
        });

        it('should have deregistered and dispatched "remove" key point event', function () {
            controller.removeKeyPoint();

            assert.equal(keyPointEvent.type, KeyPointService.Event.TIMELINE, 'key point event type should equal');
            assert.equal(keyPointEvent.keyPoint.id, post.get('id'), 'key point id should equal post id');
            assert.equal(keyPointEvent.action, 'remove', 'key point event action should equal');
        });

        it('should not have deregistered and dispatched "remove" key point event via delete', function () {
            $.mockjax({
                url: Routing.generate('imdc_post_delete', {pid: post.get('id')}),
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
                    assert.isNull(keyPointEvent, 'key point event should be null');

                    var kIndex = KeyPointService._kIndex(post.get('id'));
                    var keyPoint = keyPointService.keyPoints[kIndex];
                    assert.equal(keyPoint, controller.model.get('keyPoint'), 'key point should still be registered');
                });
        });

        it('should have deregistered and dispatched "remove" key point event via delete', function () {
            $.mockjax({
                url: Routing.generate('imdc_post_delete', {pid: post.get('id')}),
                responseText: {
                    wasDeleted: true
                }
            });

            return controller.delete()
                .done(function (data) {
                    assert.equal(keyPointEvent.type, KeyPointService.Event.TIMELINE, 'key point event type should equal');
                    assert.equal(keyPointEvent.keyPoint.id, post.get('id'), 'key point id should equal post id');
                    assert.equal(keyPointEvent.action, 'remove', 'key point event action should equal');
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                });
        });

        after(function () {
            keyPointService = null;
            keyPointEvent = null;
            post = null;
            pageUrl = null;
            controller = null;
        });

    });

});
