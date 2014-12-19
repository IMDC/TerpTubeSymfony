define([
    'chai',
    'test/common',
    'factory/postFactory',
    'jquery',
    'fos_routes'
], function (chai, Common, PostFactory) {
    'use strict';

    var assert = chai.assert;

    describe('PostFactory checks', function () {

        this.timeout(Common.PAGE_LOAD_TIMEOUT * 7);

        Common.ajaxSetup();

        var post;
        var postForm;

        before(function (done) {
            post = {
                id: '0' + Math.floor((Math.random() * 100000) + 1),
                threadId: 17
            };
            postForm = '';

            Common.login(done);

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should get a new post form', function (done) {
            return PostFactory.new(post)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'wasReplied', 'result should have key:wasReplied');
                    assert.property(data, 'html', 'result should have key:html');

                    assert.isFalse(data.wasReplied, 'key:wasReplied should be false');
                    assert.include(data.html, 'form  name="post"', 'key:html should contain a post form');

                    postForm = $(data.html).find('form');
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should create a new post', function (done) {
            assert.notStrictEqual(postForm.length, 0, 'should not be of length 0. a previous test has failed');

            postForm.find('textarea[name="post[content]"]').val('testtest_new');

            return PostFactory.new(post, postForm[0])
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'wasReplied', 'result should have key:wasReplied');
                    assert.property(data, 'post', 'result should have key:post');
                    assert.property(data, 'redirectUrl', 'result should have key:redirectUrl');

                    assert.isTrue(data.wasReplied, 'key:wasReplied should be true');
                    assert.isNumber(data.post.id, 'value of key path:post.id should be a number');
                    assert.match(data.redirectUrl, new RegExp('.*\\/' + post.threadId + '.*'),
                        'key:redirectUrl should have matched');

                    post.id = data.post.id;
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });

            postForm = '';

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should get post', function (done) {
            return PostFactory.view(post)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'html', 'result should have key:html');

                    assert.include(data.html, 'data-pid="' + post.id + '"', 'key:html should contain the post id');
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should get edit post form', function (done) {
            return PostFactory.edit(post)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'wasEdited', 'result should have key:wasEdited');
                    assert.property(data, 'html', 'result should have key:html');

                    assert.isFalse(data.wasEdited, 'key:wasEdited should be false');
                    assert.include(data.html, 'form  name="post"', 'key:html should contain a post form');

                    postForm = $(data.html).find('form');
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should edit post form', function (done) {
            assert.notStrictEqual(postForm.length, 0, 'should not be of length 0. a previous test has failed');

            var content = postForm.find('textarea[name="post[content]"]');
            var contentVal = 'testtest_edit';
            content.val(contentVal);

            return PostFactory.edit(post, postForm[0])
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'wasEdited', 'result should have key:wasEdited');
                    assert.property(data, 'post', 'result should have key:post');
                    assert.property(data, 'html', 'result should have key:html');

                    assert.isTrue(data.wasEdited, 'key:wasEdited should be true');
                    assert.include(data.html, contentVal, 'key:html should contain submitted post content');

                    assert.isDefined(post.startTime, 'post key:startTime should be defined');
                    assert.isDefined(post.endTime, 'post key:endTime should be defined');
                    assert.isDefined(post.isTemporal, 'post key:isTemporal should be defined');
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });

            postForm = '';

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should delete the post', function (done) {
            return PostFactory.delete(post)
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
            post = null;
            postForm = null;
        });

    });

});
