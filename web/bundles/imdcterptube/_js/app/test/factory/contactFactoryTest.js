define([
    'chai',
    'test/common',
    'factory/contactFactory',
    'jquery',
    'jquery-mockjax',
    'fos_routes'
], function (chai, Common, ContactFactory) {
    'use strict';

    var assert = chai.assert;

    describe('ContactFactory', function () {

        var userIds;

        before(function () {
            userIds = [1, 2];
            $.mockjaxSettings.logging = false;
        });

        beforeEach(function () {
            $.mockjax.clear();
        });

        it('should not delete contacts', function (done) {
            $.mockjax({
                method: 'DELETE',
                url: Routing.generate('imdc_delete_contact'),
                data: {
                    userIds: userIds,
                    contactList: 'error'
                },
                status: 400,
                responseText: {
                    code: 9303,
                    message: ''
                }
            });

            return ContactFactory.delete(userIds, 'error')
                .done(function (data) {
                    assert.fail('done', 'fail', 'request should have failed');
                    done();
                })
                .fail(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'code', 'result should have key:code');
                    assert.property(data, 'message', 'result should have key:message');
                    done();
                });
        });

        it('should delete contacts', function (done) {
            $.mockjax({
                method: 'DELETE',
                url: Routing.generate('imdc_delete_contact'),
                data: {
                    userIds: userIds,
                    contactList: 'friends'
                },
                responseText: {
                    code: 200
                }
            });

            return ContactFactory.delete(userIds, 'friends')
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'code', 'result should have key:code');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        after(function () {
            userIds = null;
        });

    });

});
