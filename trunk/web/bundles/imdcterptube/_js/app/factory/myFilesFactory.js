define([
    'model/mediaModel'
], function (MediaModel) {
    'use strict';

    var MyFilesFactory = {};

    MyFilesFactory._prepForFormPost = function (form, settings) {
        settings.type = 'POST';
        settings.contentType = false;
        if (form) {
            settings.data = new FormData(form);
        }
        settings.processData = false;
        settings.xhr = function () {
            var xhr = $.ajaxSettings.xhr();
            xhr.upload.addEventListener('progress', function (e) {
                if (!e.lengthComputable)
                    return;

                $.Deferred().notify(Math.floor((e.loaded / e.total) * 100));
            }.bind(this), false);

            return xhr;
        }
    };

    MyFilesFactory.addRecording = function (video, audio, interpretationData) {
        var formData = new FormData();
        var isFirefox = !!navigator.mozGetUserMedia;
        var settings = {
            url: Routing.generate('imdc_myfiles_add_recording')
        };

        formData.append('isFirefox', isFirefox);
        if (!isFirefox) {
            formData.append('video-blob', video);
        }
        formData.append('audio-blob', audio);

        if (interpretationData) {
            formData.append('isInterpretation', true);
            formData.append('sourceStartTime', interpretationData.sourceStartTime);
            formData.append('sourceId', interpretationData.sourceId);
        }

        MyFilesFactory._prepForFormPost(null, settings);

        settings.data = formData;

        return $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.responseCode == 200) {
                    data.media = new MediaModel(data.media);
                    return $.Deferred().resolve(data);
                } else {
                    return $.Deferred().reject(data);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    MyFilesFactory.add = function (form) {
        var settings = {
            url: Routing.generate('imdc_myfiles_add')
        };

        MyFilesFactory._prepForFormPost(form, settings);

        return $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.wasUploaded) {
                    data.media = new MediaModel(data.media);
                    return $.Deferred().resolve(data);
                } else {
                    console.error(data.error);
                    return $.Deferred().reject(data);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    return MyFilesFactory;
});
