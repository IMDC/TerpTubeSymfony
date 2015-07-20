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
                id: 1
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

        it('should not have redirected', function (done) {
            $.mockjax({
                url: Routing.generate('imdc_delete_forum', {forumId: model.get('id')}),
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
                url: Routing.generate('imdc_delete_forum', {forumId: model.get('id')}),
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
            model = null;
            pageUrl = null;
            controller = null;
        });

    });

});
