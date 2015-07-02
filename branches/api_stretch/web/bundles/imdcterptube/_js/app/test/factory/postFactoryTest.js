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

            return PostFactory.new(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'wasReplied', 'result should have key:wasReplied');
                    assert.property(data, 'html', 'result should have key:html');

                    assert.isFalse(data.wasReplied, 'key:wasReplied should be false');
                    assert.include(data.html, 'form  name="post"', 'key:html should contain a post form');

                    form = $(data.html).find('form');
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should create a new post', function (done) {
            assert.notStrictEqual(form.length, 0, 'should not be of length 0. a previous test has failed');

            form.find('textarea[name="post[content]"]').val('testtest_new');

            return PostFactory.new(model, form[0])
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'wasReplied', 'result should have key:wasReplied');
                    assert.property(data, 'post', 'result should have key:post');
                    assert.property(data, 'redirectUrl', 'result should have key:redirectUrl');

                    assert.isTrue(data.wasReplied, 'key:wasReplied should be true');
                    assert.isNumber(data.post.id, 'value of key path:post.id should be a number');
                    assert.match(data.redirectUrl, new RegExp('.*\\/' + model.get('parent_thread.id') + '.*'),
                        'key:redirectUrl should have matched');

                    model.set('id', data.post.id);
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should get post', function (done) {
            return PostFactory.view(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'html', 'result should have key:html');

                    assert.include(data.html, 'data-pid="' + model.get('id') + '"', 'key:html should contain the post id');
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should get edit post form', function (done) {
            form = null;

            return PostFactory.edit(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'wasEdited', 'result should have key:wasEdited');
                    assert.property(data, 'html', 'result should have key:html');

                    assert.isFalse(data.wasEdited, 'key:wasEdited should be false');
                    assert.include(data.html, 'form  name="post"', 'key:html should contain a post form');

                    form = $(data.html).find('form');
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should edit post', function (done) {
            assert.notStrictEqual(form.length, 0, 'should not be of length 0. a previous test has failed');

            var content = form.find('textarea[name="post[content]"]');
            var contentVal = 'testtest_edit';
            content.val(contentVal);

            return PostFactory.edit(model, form[0])
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'wasEdited', 'result should have key:wasEdited');
                    assert.property(data, 'post', 'result should have key:post');
                    assert.property(data, 'html', 'result should have key:html');

                    assert.isTrue(data.wasEdited, 'key:wasEdited should be true');
                    assert.include(data.html, contentVal, 'key:html should contain submitted post content');

                    assert.isUndefined(model.get('start_time'), 'post key:start_time should be undefined');
                    assert.isUndefined(model.get('end_time'), 'post key:end_time should be undefined');
                    assert.isFalse(model.get('is_temporal'), 'post key:is_temporal should be false');
                    assert.isUndefined(model.get('parent_post'), 'post key:parent_post should be undefined');
                    assert.isDefined(model.get('parent_thread'), 'post key:parent_thread should be defined');
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should delete the post', function (done) {
            return PostFactory.delete(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'wasDeleted', 'result should have key:wasDeleted');

                    assert.isTrue(data.wasDeleted, 'key:wasDeleted should be true');
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
