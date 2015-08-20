define([
    'chai',
    'test/common',
    'model/threadModel',
    'factory/threadFactory',
    'jquery',
    'jquery-mockjax',
    'fos_routes'
], function (chai, Common, ThreadModel, ThreadFactory) {
    'use strict';

    var assert = chai.assert;

    describe('ThreadFactory', function () {

        var model;

        before(function () {
            model = new ThreadModel({
                id: 1
            });
        });

        beforeEach(function () {
            $.mockjax.clear();
        });

        it('should delete the thread', function (done) {
            $.mockjax({
                method: 'DELETE',
                url: Routing.generate('imdc_delete_thread', {threadId: model.get('id')}),
                responseText: {
                    code: 200,
                    redirect_url: Common.BASE_URL + '/forum/'
                }
            });

            return ThreadFactory.delete(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'code', 'result should have key:code');
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
