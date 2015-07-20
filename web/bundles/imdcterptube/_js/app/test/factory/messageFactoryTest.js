define([
    'chai',
    'test/common',
    'model/messageModel',
    'factory/messageFactory',
    'jquery',
    'jquery-mockjax',
    'fos_routes'
], function (chai, Common, MessageModel, MessageFactory) {
    'use strict';

    var assert = chai.assert;

    describe('MessageFactory', function () {

        var model;

        before(function () {
            model = new MessageModel({
                id: 1
            });
        });

        beforeEach(function () {
            $.mockjax.clear();
        });

        it('should mark message as read', function (done) {
            $.mockjax({
                method: 'POST',
                url: Routing.generate('imdc_message_mark_as_read', {messageid: model.get('id')}),
                responseText: {
                    wasEdited: true,
                    responseCode: 200,
                    feedback: ''
                }
            });

            return MessageFactory.edit(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'responseCode', 'result should have key:responseCode');
                    assert.property(data, 'feedback', 'result should have key:feedback');

                    assert.equal(data.responseCode, 200, 'key:responseCode should be OK');
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        after(function () {
            model = null;
        });

    });

});
