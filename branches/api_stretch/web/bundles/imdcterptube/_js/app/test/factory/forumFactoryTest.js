define([
    'chai',
    'test/common',
    'model/forumModel',
    'factory/forumFactory',
    'jquery',
    'jquery-mockjax',
    'fos_routes'
], function (chai, Common, ForumModel, ForumFactory) {
    'use strict';

    var assert = chai.assert;

    describe('ForumFactory', function () {

        var model;

        before(function () {
            model = new ForumModel({
                id: 1
            });
            $.mockjaxSettings.logging = false;
        });

        beforeEach(function () {
            $.mockjax.clear();
        });

        it('should delete the forum', function (done) {
            $.mockjax({
                method: 'DELETE',
                url: Routing.generate('imdc_delete_forum', {forumId: model.get('id')}),
                responseText: {
                    code: 200,
                    redirect_url: Common.BASE_URL + '/forum/'
                }
            });

            return ForumFactory.delete(model)
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
