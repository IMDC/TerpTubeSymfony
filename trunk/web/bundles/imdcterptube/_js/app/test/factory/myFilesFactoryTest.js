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

        var interpretationData;
        var video;
        var audio;

        before(function () {
            interpretationData = {
                sourceStartTime: '0.2',
                sourceId: 1
            };
        });

        beforeEach(function () {
            video = new Blob(['video'], {type: 'video/webm'});
            audio = new Blob(['audio'], {type: 'audio/wav'});
            $.mockjax.clear();
        });

        it('should add recording', function (done) {
            var media = {
                id: 1,
                is_interpretation: false
            };
            var params = {
                video: video,
                audio: audio,
                title: 'hello'
            };

            $.mockjax({
                method: 'POST',
                url: Routing.generate('imdc_myfiles_add_recording'),
                responseText: {
                    responseCode: 200,
                    media: media
                }
            });

            return MyFilesFactory.addRecording(params)
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
                is_interpretation: true,
                source_start_time: interpretationData.sourceStartTime,
                source: {
                    id: interpretationData.sourceId
                }
            };
            var params = {
                video: video,
                audio: audio,
                title: 'hello',
                isInterpretation: media.is_interpretation,
                sourceStartTime: media.source_start_time,
                sourceId: media.source.id
            };

            $.mockjax({
                method: 'POST',
                url: Routing.generate('imdc_myfiles_add_recording'),
                responseText: {
                    responseCode: 200,
                    media: media
                }
            });

            return MyFilesFactory.addRecording(params)
                .done(function (data) {
                    assert.isObject(data, 'result should be an object');
                    assert.property(data, 'media', 'result should have key:media');

                    assert.isTrue(data.media.get('is_interpretation'), 'media should be an interpretation');
                    assert.equal(data.media.get('source_start_time'), media.source_start_time,
                        'source start time should equal');
                    assert.equal(data.media.get('source.id'), media.source.id,
                        'source media id should equal');
                    done();
                })
                .fail(function () {
                    assert.fail('fail', 'done', 'request should not have failed');
                    done();
                });
        });

        after(function () {
            interpretationData = null;
            video = null;
            audio = null;
        });

    });

});
