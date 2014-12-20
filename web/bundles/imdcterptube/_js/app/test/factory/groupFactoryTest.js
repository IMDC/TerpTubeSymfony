define([
    'chai',
    'test/common',
    'factory/groupFactory',
    'jquery',
    'fos_routes'
], function (chai, Common, GroupFactory) {
    'use strict';

    var assert = chai.assert;

    describe('GroupFactory', function () {

        this.timeout(Common.PAGE_LOAD_TIMEOUT * 2);

        Common.ajaxSetup();

        var group;

        before(function (done) {
            group = {
                id: 11 // this must be set to an existing group id
            };

            Common.login(done);

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should delete the group', function (done) {
            return GroupFactory.delete(group)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'wasDeleted', 'result should have key:wasDeleted');
                    assert.property(data, 'redirectUrl', 'result should have key:redirectUrl');

                    assert.isTrue(data.wasDeleted, 'key:wasDeleted should be true');
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        after(function () {
            group = null;
        });

    });

});
