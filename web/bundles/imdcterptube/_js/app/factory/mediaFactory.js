define([
    'model/mediaModel',
    'underscore'
], function (MediaModel) {
    'use strict';

    var MediaFactory = {};

    MediaFactory.list = function (ids) {
        var deferred = $.Deferred();
        var settings = {
            url: Routing.generate('imdc_cget_media'),
            data: {}
        };

        if (ids) {
            settings.data.id = ids.join();
        }

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                data.media.forEach(function (element, index, array) {
                    array[index] = new MediaModel(element);
                });
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    MediaFactory.get = function (model) {
        var deferred = $.Deferred();
        var isModel = _.isObject(model);
        var id = isModel ? model.get('id') : model;
        var settings = {
            url: Routing.generate('imdc_get_media', {mediaId: id})
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                if (isModel) {
                    model.update(data.media);
                    data.media = model;
                } else {
                    data.media = new MediaModel(data.media);
                }
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    MediaFactory.edit = function (model) {
        var deferred = $.Deferred();
        var settings = {
            method: 'POST',
            url: Routing.generate('imdc_edit_media', {mediaId: model.get('id')}),
            data: {media: JSON.stringify(model.data)} // TODO add method to model to get json representation of underlying data
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                model.update(data.media);
                data.media = model;
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    MediaFactory.delete = function (model, confirmed) {
        var deferred = $.Deferred();
        var settings = {
            method: 'DELETE',
            url: Routing.generate('imdc_delete_media', {mediaId: model.get('id')}),
            data: {confirm: confirmed || false}
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);

                //TODO this will only make sense if 'confirmed' is false
                //TODO move. should be done at view level
                var data = jqXHR.responseJSON;
                var mediaInUseTexts = [];
                data.mediaInUse.forEach(function(element, index, array) {
                    mediaInUseTexts.push(
                        Translator.trans('filesGateway.deleteMediaInUseConfirmation.' + element)
                    );
                });
                data.confirmText = Translator.trans('filesGateway.deleteMediaInUseConfirmation.finalMessage', {
                    'mediaUsedLocations': mediaInUseTexts.join(', ')
                });

                deferred.reject(data);
            });

        return deferred.promise();
    };

    MediaFactory.trim = function (model, startTime, endTime) {
        var deferred = $.Deferred();
        var settings = {
            method: 'PATCH',
            url: Routing.generate('imdc_trim_media', {mediaId: model.get('id')}),
            data: {
                startTime: startTime,
                endTime: endTime
            }
        };

        $.ajax(settings)
            .then(function (data, textStatus, jqXHR) {
                model = new MediaModel(data.media);
                deferred.resolve(data);
            },
            function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
                deferred.reject(jqXHR.responseJSON);
            });

        return deferred.promise();
    };

    return MediaFactory;
});
