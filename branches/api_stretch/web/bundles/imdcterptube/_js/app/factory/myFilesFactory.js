define([
    'model/mediaModel'
], function (MediaModel) {
    'use strict';

    var MyFilesFactory = {};

    MyFilesFactory._prepForFormSubmit = function (form, settings, deferred) {
        settings.type = 'POST';
        settings.contentType = false;
        if (form) {
            settings.data = new FormData(form);
        }
        settings.processData = false;
        settings.xhr = function () {
            var xhr = $.ajaxSettings.xhr();
            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    deferred.notify(Math.floor((e.loaded / e.total) * 100));
                }
            }, false);

            return xhr;
        }
    };

    MyFilesFactory.addRecording = function (video, audio, interpretationData) {
        var formData = new FormData();
        var isFirefox = !!navigator.mozGetUserMedia;
        var deferred = $.Deferred();
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

        MyFilesFactory._prepForFormSubmit(null, settings, deferred);
        settings.data = formData;

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.responseCode == 200) {
                    data.media = new MediaModel(data.media);
                    deferred.resolve(data);
                } else {
                    deferred.reject(data);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject();
            });

        return deferred.promise();
    };

    MyFilesFactory.add = function (form) {
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_myfiles_add')
        };

        MyFilesFactory._prepForFormSubmit(form, settings, deferred);

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.wasUploaded) {
                    data.media = new MediaModel(data.media);
                    deferred.resolve(data);
                } else {
                    console.error(data.error);
                    deferred.reject(data);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject();
            });

        return deferred.promise();
    };

    return MyFilesFactory;
});