define([
    'chai',
    'test/common',
    'thread/threadModel',
    'factory/threadFactory',
    'jquery',
    'fos_routes'
], function (chai, Common, ThreadModel, ThreadFactory) {
    'use strict';

    var assert = chai.assert;

    describe('ThreadFactory', function () {

        this.timeout(Common.PAGE_LOAD_TIMEOUT * 2);

        Common.ajaxSetup();

        var thread;

        before(function (done) {
            thread = new ThreadModel({
                id: 10 // this must be set to an existing thread id
            });

            Common.login(done);

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should delete the thread', function (done) {
            return ThreadFactory.delete(thread)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'redirectUrl', 'result should have key:redirectUrl');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        after(function () {
            thread = null;
        });

    });

});
