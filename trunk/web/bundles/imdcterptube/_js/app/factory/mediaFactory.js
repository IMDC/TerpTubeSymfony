define([
    'model/mediaModel'
], function (MediaModel) {
    'use strict';

    var MediaFactory = {};

    MediaFactory.list = function (ids) {
        var settings = {
            url: Routing.generate('imdc_media_list'),
            data: {}
        };

        if (ids) {
            settings.data.id = ids.join();
        }

        return $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                data.media.forEach(function (element, index, array) {
                    array[index] = new MediaModel(element);
                });
                return $.Deferred().resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    MediaFactory.edit = function (model) {
        var settings = {
            url: Routing.generate('imdc_media_edit', {mediaId: model.get('id')}),
            type: 'POST',
            data: {media: JSON.stringify(model.data)} // TODO add method to model to get json representation of underlying data
        };

        return $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.responseCode == 200) {
                    model = new MediaModel(data.media);
                    return $.Deferred().resolve(data);
                } else {
                    console.error(data.feedback);
                    return $.Deferred().reject(data);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    MediaFactory.delete = function (model, confirmed) {
        var settings = {
            url: Routing.generate('imdc_media_delete', {mediaId: model.get('id')}),
            type: 'POST',
            data: {confirm: confirmed || false}
        };

        return $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.responseCode == 200) {
                    return $.Deferred().resolve(data);
                } else {
                    console.error(data.feedback);

                    var mediaInUseTexts = [];
                    data.mediaInUse.forEach(function(element, index, array) {
                        mediaInUseTexts.push(
                            Translator.trans('filesGateway.deleteMediaInUseConfirmation.' + element)
                        );
                    });
                    data.confirmText = Translator.trans('filesGateway.deleteMediaInUseConfirmation.finalMessage', {
                        'mediaUsedLocations': mediaInUseTexts.join(', ')
                    });

                    return $.Deferred().reject(data);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    MediaFactory.trim = function (model, startTime, endTime) {
        var settings = {
            url: Routing.generate('imdc_media_trim', {mediaId: model.get('id')}),
            type: 'POST',
            data: {
                startTime: startTime,
                endTime: endTime
            }
        };

        return $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (data.responseCode == 200) {
                    model = new MediaModel(data.media);
                    return $.Deferred().resolve(data);
                } else {
                    console.error(data.feedback);
                    return $.Deferred().reject(data);
                }
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                return $.Deferred().reject();
            });
    };

    return MediaFactory;
});
