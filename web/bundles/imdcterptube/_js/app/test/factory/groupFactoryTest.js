define([
    'chai',
    'test/common',
    'model/groupModel',
    'factory/groupFactory',
    'jquery',
    'jquery-mockjax',
    'fos_routes'
], function (chai, Common, GroupModel, GroupFactory) {
    'use strict';

    var assert = chai.assert;

    describe('GroupFactory', function () {

        var model;

        before(function () {
            model = new GroupModel({
                id: 1
            });
            $.mockjaxSettings.logging = false;
        });

        beforeEach(function () {
            $.mockjax.clear();
        });

        it('should delete the group', function (done) {
            $.mockjax({
                method: 'DELETE',
                url: Routing.generate('imdc_delete_user_group', {groupId: model.get('id')}),
                responseText: {
                    code: 200,
                    redirect_url: Common.BASE_URL + '/group/'
                }
            });

            return GroupFactory.delete(model)
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
