define([
    'chai',
    'test/common',
    'model/forumModel',
    'factory/forumFactory',
    'jquery',
    'fos_routes'
], function (chai, Common, ForumModel, ForumFactory) {
    'use strict';

    var assert = chai.assert;

    describe('ForumFactory', function () {

        this.timeout(Common.PAGE_LOAD_TIMEOUT * 2);

        Common.ajaxSetup();

        var model;

        before(function (done) {
            model = new ForumModel({
                id: 120 // this must be set to an existing forum id
            });

            Common.login(done);

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should delete the forum', function (done) {
            return ForumFactory.delete(model)
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
        });

        after(function () {
            model = null;
        });

    });

});
