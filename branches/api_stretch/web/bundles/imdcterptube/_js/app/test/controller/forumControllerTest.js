define([
    'chai',
    'test/common',
    'model/forumModel',
    'controller/forumController',
    'jquery',
    'jquery-mockjax',
    'fos_routes',
    'es5-shim'
], function (chai, Common, ForumModel, ForumController) {
    'use strict';

    var assert = chai.assert;

    describe('ForumController', function () {

        window.$tt = {};
        $tt._services = [];
        $tt._instances = [];

        var model;
        var pageUrl;
        var controller;

        before(function () {
            model = new ForumModel({
                id: 10
            });
            controller = new ForumController(model, {});
            controller.onViewLoaded();
            $.mockjaxSettings.logging = false;

            // override to prevent page reloads
            window.location.assign = function (url) {
                pageUrl = url;
            };
        });

        beforeEach(function () {
            pageUrl = undefined;
            $.mockjax.clear();
        });

        it('should not have redirected', function () {
            $.mockjax({
                url: Routing.generate('imdc_forum_delete', {forumId: model.get('id')}),
                responseText: {}
            });

            // don't return promise
            controller.delete()
                .done(function (data) {
                    assert.fail('done', 'fail', 'request should have failed');
                })
                .fail(function (data) {
                    assert.isUndefined(pageUrl, 'pageUrl should be null');
                });
        });

        it('should have redirected', function () {
            $.mockjax({
                url: Routing.generate('imdc_forum_delete', {forumId: model.get('id')}),
                responseText: {
                    redirectUrl: Common.BASE_URL + '/forum/'
                }
            });

            return controller.delete()
                .done(function (data) {
                    assert.equal(pageUrl, data.redirectUrl, 'pageUrl should equal key:redirectUrl');
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                });
        });

        after(function () {
            model = null;
            pageUrl = null;
            controller = null;
        });

    });

});
