define([
    'chai',
    'test/common',
    'factory/myFilesFactory',
    'jquery',
    'fos_routes'
], function (chai, Common, MyFilesFactory) {
    'use strict';

    var assert = chai.assert;

    describe('MyFilesFactory', function () {

        this.timeout(Common.PAGE_LOAD_TIMEOUT * 3);

        Common.ajaxSetup();

        var video;
        var audio;
        var interpretationData;

        before(function (done) {
            //TODO separate test data for video and audio
            //FIXME test is for firefox
            // use native XMLHttpRequest
            var request = new XMLHttpRequest();
            request.onload = function () {
                audio = request.response;
                console.log(audio);
            };
            request.open('GET', Common.SCHEME_HOST + '/test_files/video_audio.webm');
            request.responseType = 'blob';
            request.send();

            interpretationData = {
                sourceStartTime: '0.2',
                sourceId: 4 // an existing media id
            };

            Common.login(done);

            setTimeout(done, Common.PAGE_LOAD_TIMEOUT);
        });

        it('should add recording', function (done) {
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
