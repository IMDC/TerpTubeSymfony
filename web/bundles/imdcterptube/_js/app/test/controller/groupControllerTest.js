define([
    'chai',
    'controller/groupController',
    'jquery',
    'jquery-mockjax',
    'fos_routes',
    'es5-shim'
], function (chai, GroupController) {
    'use strict';

    var assert = chai.assert;

    describe('GroupController', function () {

        window.$tt = {};
        $tt._services = [];
        $tt._instances = [];

        var group;
        var pageUrl;
        var controller;

        before(function () {
            group = {id: 10};
            controller = new GroupController(group, {});
            controller.onViewLoaded();

            // override to prevent page reloads
            window.location.assign = function (url) {
                pageUrl = url;
            };
        });

        beforeEach(function () {
            pageUrl = null;
            $.mockjax.clear();
        });

        it('should not have redirected', function () {
            $.mockjax({
                url: Routing.generate('imdc_group_delete', {groupId: group.id}),
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
                    assert.isNull(pageUrl, 'pageUrl should be null');
                });
        });

        it('should have redirected', function () {
            $.mockjax({
                url: Routing.generate('imdc_group_delete', {groupId: group.id}),
                responseText: {
                    wasDeleted: true
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
            group = null;
            pageUrl = null;
            controller = null;
        });

    });

});
