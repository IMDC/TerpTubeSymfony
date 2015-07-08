define([
    'chai',
    'test/common',
    'model/postModel',
    'factory/postFactory',
    'jquery',
    'fos_routes'
], function (chai, Common, PostModel, PostFactory) {
    'use strict';

    var assert = chai.assert;

    describe('PostFactory', function () {

        this.timeout(Common.PAGE_LOAD_TIMEOUT * 7);

        Common.ajaxSetup();

        var model;
        var form;

        before(function (done) {
            model = new PostModel({
                parent_thread: {
                    id: 17
                }
            });

            Common.login(done);

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should get a new post form', function (done) {
            form = null;

            //TODO check that the factory updated the model
            return PostFactory.new(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'post', 'result should have key:post');
                    assert.property(data, 'form', 'result should have key:form');

                    assert.include(data.form, 'form  name="post"', 'key:form should contain a post form');

                    form = $(data.form).find('form');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should create a new post', function (done) {
            assert.notStrictEqual(form.length, 0, 'should not be of length 0. a previous test has failed');

            form.find('textarea[name="post[content]"]').val('testtest_new');

            //TODO check that the factory updated the model
            return PostFactory.new(model, form[0])
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'post', 'result should have key:post');

                    assert.isNumber(data.post.id, 'value of key path:post.id should be a number');

                    model.set('id', data.post.id);
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should get post', function (done) {
            //TODO check that the factory updated the model
            return PostFactory.get(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should get edit post form', function (done) {
            form = null;

            //TODO check that the factory updated the model
            return PostFactory.edit(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'post', 'result should have key:post');
                    assert.property(data, 'form', 'result should have key:form');

                    assert.include(data.form, 'form  name="post"', 'key:form should contain a post form');

                    form = $(data.form).find('form');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should edit post', function (done) {
            assert.notStrictEqual(form.length, 0, 'should not be of length 0. a previous test has failed');

            var content = form.find('textarea[name="post[content]"]');
            var contentVal = 'testtest_edit';
            content.val(contentVal);

            //TODO check that the factory updated the model
            return PostFactory.edit(model, form[0])
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'post', 'result should have key:post');

                    assert.isUndefined(model.get('start_time'), 'post key:start_time should be undefined');
                    assert.isUndefined(model.get('end_time'), 'post key:end_time should be undefined');
                    assert.isFalse(model.get('is_temporal'), 'post key:is_temporal should be false');
                    assert.isUndefined(model.get('parent_post'), 'post key:parent_post should be undefined');
                    assert.isDefined(model.get('parent_thread'), 'post key:parent_thread should be defined');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should delete the post', function (done) {
            return PostFactory.delete(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        after(function () {
            model = null;
            form = null;
        });

    });

});
