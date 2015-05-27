define([
    'chai',
    'test/common',
    'model/messageModel',
    'factory/messageFactory',
    'jquery',
    'fos_routes'
], function (chai, Common, MessageModel, MessageFactory) {
    'use strict';

    var assert = chai.assert;

    describe('MessageFactory', function () {

        this.timeout(Common.PAGE_LOAD_TIMEOUT * 2);

        Common.ajaxSetup();

        var model;

        before(function (done) {
            model = new MessageModel({
                id: 22 // this must be set to an existing message id
            });

            Common.login(done);

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should mark message as read', function (done) {
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
