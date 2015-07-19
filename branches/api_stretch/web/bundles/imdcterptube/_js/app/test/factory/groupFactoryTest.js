define([
    'chai',
    'test/common',
    'model/groupModel',
    'factory/groupFactory',
    'jquery',
    'fos_routes'
], function (chai, Common, GroupModel, GroupFactory) {
    'use strict';

    var assert = chai.assert;

    describe('GroupFactory', function () {

        this.timeout(Common.PAGE_LOAD_TIMEOUT * 2);

        Common.ajaxSetup();

        var model;

        before(function (done) {
            model = new GroupModel({
                id: 41 // this must be set to an existing group id
            });

            Common.login(done);

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should delete the group', function (done) {
            return GroupFactory.delete(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'redirect_url', 'result should have key:redirect_url');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        after(function () {
            model = null;
        });

    });

});
