define([
    'chai',
    'test/common',
    'factory/contactFactory',
    'jquery',
    'fos_routes'
], function (chai, Common, ContactFactory) {
    'use strict';

    var assert = chai.assert;

    describe('ContactFactory', function () {

        this.timeout(Common.PAGE_LOAD_TIMEOUT * 4);

        Common.ajaxSetup();

        var userIds;

        before(function (done) {
            userIds = [13, 14];

            Common.login(function () {
                // add users to friends list
                for (var index in userIds) {
                    $.get(Common.BASE_URL + '/member/friends/' + userIds[index] + '/add');
                }
                // don't call done(). let it timeout
            });

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT * 2);
        });

        it('should not delete contacts', function (done) {
            return ContactFactory.delete(userIds, 'error')
                .done(function (data) {
                    assert.fail('done', 'fail', 'request should have failed');
                    done();
                })
                .fail(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'success', 'result should have key:success');
                    assert.property(data, 'message', 'result should have key:message');

                    assert.isFalse(data.success, 'key:success should be false');
                    done();
                });

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should delete contacts', function (done) {
            return ContactFactory.delete(userIds, 'friends')
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'success', 'result should have key:success');

                    assert.isTrue(data.success, 'key:success should be true');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        after(function () {
            userIds = null;
        });

    });

});
