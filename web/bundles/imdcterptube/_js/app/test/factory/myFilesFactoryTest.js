define([
    'chai',
    'test/common',
    'factory/myFilesFactory',
    'jquery',
    'jquery-mockjax',
    'fos_routes'
], function (chai, Common, MyFilesFactory) {
    'use strict';

    var assert = chai.assert;

    describe('MyFilesFactory', function () {

        var video;
        var audio;
        var interpretationData;

        before(function () {
            video = new Blob(['video'], {type: 'video/webm'});
            audio = new Blob(['audio'], {type: 'audio/wav'});
            interpretationData = {
                sourceStartTime: '0.2',
                sourceId: 1
            };
        });

        beforeEach(function () {
            $.mockjax.clear();
        });

        it('should add recording', function (done) {
            var media = {
                id: 1,
                is_interpretation: false
            };

            $.mockjax({
                method: 'POST',
                url: Routing.generate('imdc_myfiles_add_recording'),
                responseText: {
                    media: media
                }
            });

            return MyFilesFactory.addRecording(video, audio)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.isFalse(data.media.get('is_interpretation'), 'media should not be an interpretation');
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        it('should add interpretation recording', function (done) {
            var media = {
                id: 1,
                is_interpretation: false,
                source_start_time: interpretationData.sourceStartTime,
                source: {
                    id: interpretationData.sourceId
                }
            };

            $.mockjax({
                method: 'POST',
                url: Routing.generate('imdc_myfiles_add_recording'),
                responseText: {
                    media: media
                }
            });

            return MyFilesFactory.addRecording(video, audio, interpretationData)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.isTrue(data.media.get('is_interpretation'), 'media should be an interpretation');
                    assert.equal(data.media.get('source_start_time'), interpretationData.sourceStartTime,
                        'source start time should equal');
                    assert.equal(data.media.get('source.id'), interpretationData.sourceId,
                        'source media id should equal');
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        after(function () {
            video = null;
            audio = null;
            interpretationData = null;
        });

    });

});
