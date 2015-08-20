define([
    'chai',
    'test/common',
    'model/postModel',
    'factory/postFactory',
    'jquery',
    'jquery-mockjax',
    'fos_routes'
], function (chai, Common, PostModel, PostFactory) {
    'use strict';

    var assert = chai.assert;

    describe('PostFactory', function () {

        var post;
        var model;

        before(function () {
            $.mockjaxSettings.logging = false;
        });

        beforeEach(function () {
            post = {
                is_temporal: false,
                parent_thread_id: 1
            };
            model = new PostModel(_.clone(post));
            $.mockjax.clear();
        });

        it('should get a new post form', function (done) {
            $.mockjax({
                url: Routing.generate('imdc_new_post', {
                    threadId: model.get('parent_thread_id'),
                    parentPostId: model.get('parent_post_id') || model.get('id')
                }),
                responseText: {
                    post: post,
                    form: 'form'
                }
            });

            return PostFactory.new(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'post', 'result should have key:post');
                    assert.property(data, 'form', 'result should have key:form');

                    assert.isDefined(data.post.get('form'), 'post key:form should be defined');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should create a new post', function (done) {
            $.mockjax({
                method: 'POST',
                url: Routing.generate('imdc_post_post', {
                    threadId: model.get('parent_thread_id'),
                    parentPostId: model.get('parent_post_id') || model.get('id')
                }),
                responseText: {
                    post: post
                }
            });

            return PostFactory.post(model, '')
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'post', 'result should have key:post');

                    assert.isFalse(model.get('is_temporal'), 'post key:is_temporal should be false');
                    assert.isDefined(model.get('parent_thread_id'), 'post key:parent_thread_id should be defined');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should get post', function (done) {
            $.mockjax({
                url: Routing.generate('imdc_get_post', {postId: model.get('id')}),
                responseText: {
                    post: post
                }
            });

            return PostFactory.get(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'post', 'result should have key:post');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should edit post', function (done) {
            $.mockjax({
                url: Routing.generate('imdc_edit_post', {postId: model.get('id')}),
                responseText: {
                    post: post,
                    form: 'form'
                }
            });

            return PostFactory.edit(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'post', 'result should have key:post');
                    assert.property(data, 'form', 'result should have key:form');

                    assert.isDefined(model.get('form'), 'post key:form should be defined');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should put post', function (done) {
            $.mockjax({
                method: 'POST',
                url: Routing.generate('imdc_put_post', {postId: model.get('id')}),
                responseText: {
                    post: post
                }
            });

            return PostFactory.put(model, '')
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'post', 'result should have key:post');

                    assert.isUndefined(model.get('start_time'), 'post key:start_time should be undefined');
                    assert.isUndefined(model.get('end_time'), 'post key:end_time should be undefined');
                    assert.isFalse(model.get('is_temporal'), 'post key:is_temporal should be false');
                    assert.isUndefined(model.get('parent_post'), 'post key:parent_post should be undefined');
                    assert.isDefined(model.get('parent_thread_id'), 'post key:parent_thread_id should be defined');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should delete the post', function (done) {
            $.mockjax({
                method: 'DELETE',
                url: Routing.generate('imdc_delete_post', {postId: model.get('id')}),
                responseText: {
                    code: 200
                }
            });

            return PostFactory.delete(model)
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
            post = null;
            model = null;
        });

    });

});
