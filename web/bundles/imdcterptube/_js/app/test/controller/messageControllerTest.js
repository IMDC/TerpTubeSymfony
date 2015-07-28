define([
    'chai',
    'test/common',
    'model/messageModel',
    'controller/messageController',
    'jquery',
    'jquery-mockjax',
    'fos_routes',
    'es5-shim'
], function (chai, Common, MessageModel, MessageController) {
    'use strict';

    var assert = chai.assert;

    describe('MessageController', function () {

        window.$tt = {};
        $tt._services = [];
        $tt._instances = [];

        var model;
        var controller;

        before(function () {
            model = new MessageModel({
                id: 1
            });
            controller = new MessageController(model, {});
            controller.onViewLoaded();
            $.mockjaxSettings.logging = false;
        });

        beforeEach(function () {
            $.mockjax.clear();
        });

        it('should not have marked as read', function (done) {
            $.mockjax({
                method: 'POST',
                url: Routing.generate('imdc_message_mark_as_read', {messageid: model.get('id')}),
                responseText: {
                    wasEdited: false
                }
            });

            return controller.markAsRead()
                .done(function (data) {
                    assert.fail('done', 'fail', 'request should have failed');
                    done();
                })
                .fail(function (data) {
                    assert.isFalse(data.wasEdited, 'key:wasEdited should be false');
                    done();
                });
        });

        it('should have marked as read', function (done) {
            $.mockjax({
                method: 'POST',
                url: Routing.generate('imdc_message_mark_as_read', {messageid: model.get('id')}),
                responseText: {
                    wasEdited: true
                }
            });

            return controller.markAsRead()
                .done(function (data) {
                    assert.isTrue(data.wasEdited, 'key:wasEdited should be true');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        after(function () {
            model = null;
            controller = null;
        });

    });

});
