define([
    'chai',
    'test/common',
    'factory/threadFactory',
    'jquery',
    'fos_routes'
], function (chai, Common, ThreadFactory) {
    'use strict';

    var assert = chai.assert;

    describe('ThreadFactory', function () {

        this.timeout(Common.PAGE_LOAD_TIMEOUT * 2);

        Common.ajaxSetup();

        var thread;

        before(function (done) {
            thread = {
                id: 10 // this must be set to an existing thread id
            };

            Common.login(done);

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should delete the thread', function (done) {
            return ThreadFactory.delete(thread)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'wasDeleted', 'result should have key:wasDeleted');

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
            thread = null;
        });

    });

});
