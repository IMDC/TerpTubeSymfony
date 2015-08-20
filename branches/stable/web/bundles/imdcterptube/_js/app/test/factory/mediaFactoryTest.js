define([
    'chai',
    'test/common',
    'model/mediaModel',
    'factory/mediaFactory',
    'jquery',
    'jquery-mockjax',
    'fos_routes',
    'bazinga_translations'
], function (chai, Common, MediaModel, MediaFactory) {
    'use strict';

    var assert = chai.assert;

    describe('MediaFactory', function () {

        var media;
        var model;

        before(function () {
            $.mockjaxSettings.logging = false;
        });

        beforeEach(function () {
            media = [{id: 1}, {id: 2}];
            model = new MediaModel(_.clone(media[0]));
            $.mockjax.clear();
        });

        it('should list all media', function (done) {
            $.mockjax({
                url: Routing.generate('imdc_cget_media'),
                responseText: {
                    media: media
                }
            });

            return MediaFactory.list()
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.isArray(data.media, 'key:media should be an array');
                    assert.lengthOf(data.media, media.length, 'key:media should be of length ' + media.length);
                    _.each(data.media, function (element, index, list) {
                        assert.instanceOf(element, MediaModel, 'element should be an instance of MediaModel');
                    });
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should list the specified media', function (done) {
            var mediaIds = [media[0].id, media[1].id];

            $.mockjax({
                url: Routing.generate('imdc_cget_media'),
                data: {
                    id: mediaIds.join()
                },
                responseText: {
                    media: media
                }
            });

            return MediaFactory.list(mediaIds)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.isArray(data.media, 'key:media should be an array');
                    assert.lengthOf(data.media, media.length, 'key:media should be of length ' + media.length);
                    _.each(data.media, function (element, index, list) {
                        assert.instanceOf(element, MediaModel, 'element should be an instance of MediaModel');
                    });
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should get the specified media', function (done) {
            var mediaId = model.get('id');

            $.mockjax({
                url: Routing.generate('imdc_get_media', {mediaId: mediaId}),
                responseText: {
                    media: media[0]
                }
            });

            return MediaFactory.get(mediaId)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.instanceOf(data.media, MediaModel, 'key:media should be an instance of MediaModel');
                    assert.equal(data.media.get('id'), mediaId, 'media id should be equal');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should edit the media', function (done) {
            var mediaId = model.get('id');
            var oldTitle = model.get('title');
            media[0].title = 'test:edit';

            $.mockjax({
                method: 'PUT',
                url: Routing.generate('imdc_edit_media', {mediaId: mediaId}),
                responseText: {
                    media: media[0]
                }
            });

            return MediaFactory.edit(model)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.equal(model.get('id'), mediaId, 'media id should be equal');
                    assert.notEqual(model.get('title'), oldTitle,
                        'media title should not be equal to the old title');
                    assert.equal(model.get('title'), media[0].title, 'media title should be equal');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should trim the media', function (done) {
            var mediaId = model.get('id');

            $.mockjax({
                method: 'PATCH',
                url: Routing.generate('imdc_trim_media', {mediaId: mediaId}),
                responseText: {
                    media: media[0]
                }
            });

            return MediaFactory.trim(model, '0.4', '2.2')
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.equal(model.get('id'), mediaId, 'media id should be equal');
                    done();
                })
                .fail(function (data) {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should delete not in use media', function (done) {
            $.mockjax({
                method: 'DELETE',
                url: Routing.generate('imdc_delete_media', {mediaId: model.get('id')}),
                responseText: {
                    code: 200
                }
            });

            return MediaFactory.delete(model)
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

        it('should not delete in use media', function (done) {
            $.mockjax({
                method: 'DELETE',
                url: Routing.generate('imdc_delete_media', {mediaId: model.get('id')}),
                status: 400,
                responseText: {
                    code: 9203,
                    in_use: ['post']
                }
            });

            return MediaFactory.delete(model)
                .done(function (data) {
                    assert.fail('done', 'fail', 'request should have failed');
                    done();
                })
                .fail(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'code', 'result should have key:code');
                    assert.property(data, 'in_use', 'result should have key:in_use');
                    assert.property(data, 'confirmText', 'result should have key:confirmText');

                    assert.isArray(data.in_use, 'key:in_use should be an array');
                    assert.isString(data.confirmText, 'key:confirmText should be a string');
                    done();
                });
        });

        it('should delete in use media', function (done) {
            $.mockjax({
                method: 'DELETE',
                url: Routing.generate('imdc_delete_media', {mediaId: model.get('id')}),
                responseText: {
                    code: 200
                }
            });

            return MediaFactory.delete(model, true)
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
            media = null;
            model = null;
        });

    });

});
