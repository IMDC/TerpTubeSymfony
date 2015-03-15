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
            mediaIds = [4, 1, 362, 361];

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
                .fail(function () {
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
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should edit the media', function (done) {
            var oldModelData;

            model.set('title', 'test:edit:' + Math.floor((Math.random() * 100000) + 1));
            oldModelData = model.data;

            return MediaFactory.edit(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.equal(model.get('id'), oldModelData.id, 'media id should be equal');
                    assert.equal(model.get('title'), oldModelData.title, 'media title should be equal');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should trim the media', function (done) {
            var oldModelData = model.data;

            return MediaFactory.trim(model, '0.4', '2.2')
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.equal(model.get('id'), oldModelData.id, 'media id should be equal');
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
                    assert.property(data, 'mediaInUse', 'result should have key:mediaInUse');
                    assert.property(data, 'confirmText', 'result should have key:confirmText');

                    assert.isArray(data.mediaInUse, 'key:mediaInUse should be an array');
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
