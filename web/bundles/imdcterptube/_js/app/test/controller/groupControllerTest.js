define([
    'chai',
    'test/common',
    'model/groupModel',
    'controller/groupController',
    'jquery',
    'jquery-mockjax',
    'fos_routes',
    'es5-shim'
], function (chai, Common, GroupModel, GroupController) {
    'use strict';

    var assert = chai.assert;

    describe('GroupController', function () {

        window.$tt = {};
        $tt._services = [];
        $tt._instances = [];

        var model;
        var pageUrl;
        var controller;

        before(function () {
            model = new GroupModel({
                id: 10
            });
            controller = new GroupController(model, {});
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
                url: Routing.generate('imdc_group_delete', {groupId: model.get('id')}),
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
                    assert.isUndefined(pageUrl, 'pageUrl should be null');
                });
        });

        it('should have redirected', function () {
            $.mockjax({
                url: Routing.generate('imdc_group_delete', {groupId: model.get('id')}),
                responseText: {
                    wasDeleted: true,
                    redirectUrl: Common.BASE_URL + '/groups'
                }
            });

            return controller.delete()
                .done(function (data) {
                    assert.equal(pageUrl, data.redirectUrl, 'pageUrl should equal key:redirectUrl');
                })
                .fail(function () {
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
