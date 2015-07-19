define([
    'chai',
    'test/common',
    'model/mediaModel',
    'factory/mediaFactory',
    'jquery',
    'fos_routes',
    'bazinga_translations'
], function (chai, Common, MediaModel, MediaFactory) {
    'use strict';

    var assert = chai.assert;

    describe('MediaFactory', function () {

        this.timeout(Common.PAGE_LOAD_TIMEOUT * 8);

        Common.ajaxSetup();

        var mediaIds;
        var model;

        before(function (done) {
            // index 2 must not be in use. index 3 must be in use
            mediaIds = [4, 1, 392, 391];

            Common.login(done);

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should list all media', function (done) {
            return MediaFactory.list()
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.isArray(data.media, 'key:media should be an array');
                    assert.operator(data.media.length, '>=', 1, 'key:media should greater than or equal to 1');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should list the specified media', function (done) {
            return MediaFactory.list(mediaIds)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.isArray(data.media, 'key:media should be an array');
                    assert.lengthOf(data.media, mediaIds.length, 'key:media should be of length ' + mediaIds.length);

                    model = data.media[2];
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should get the specified media', function (done) {
            return MediaFactory.get(mediaIds[0])
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.equal(model.get('id'), mediaIds[0], 'media id should be equal');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should edit the media', function (done) {
            var oldId = model.get('id');
            var oldTitle = model.get('title');
            var newTitle = 'test:edit:' + Math.floor((Math.random() * 100000) + 1);

            model.set('title', newTitle);

            return MediaFactory.edit(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.equal(model.get('id'), oldId, 'media id should be equal');
                    assert.notEqual(model.get('title'), oldTitle,
                        'media title should not be equal to the old title');
                    assert.equal(model.get('title'), newTitle, 'media title should be equal');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should trim the media', function (done) {
            var oldId = model.get('id');

            return MediaFactory.trim(model, '0.4', '2.2')
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.equal(model.get('id'), oldId, 'media id should be equal');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should delete not in use media', function (done) {
            return MediaFactory.delete(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should not delete in use media', function (done) {
            return MediaFactory.delete(new MediaModel({id: mediaIds[3]}))
                .done(function (data) {
                    assert.fail('done', 'fail', 'request should have failed');
                    done();
                })
                .fail(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'in_use', 'result should have key:in_use');
                    assert.property(data, 'confirmText', 'result should have key:confirmText');

                    assert.isArray(data.in_use, 'key:in_use should be an array');
                    assert.isString(data.confirmText, 'key:confirmText should be a string');
                    done();
                });
        });

        it('should delete in use media', function (done) {
            return MediaFactory.delete(new MediaModel({id: mediaIds[3]}), true)
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
            mediaIds = null;
            model = null;
        });

    });

});
