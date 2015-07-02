define([
    'model/model',
    'model/mediaModel',
    'extra'
], function (Model, MediaModel) {
    'use strict';

    var PostModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);

        this.data.keyPoint = null;

        // replace key/value objects with models for all media
        // TODO consolidate under collection type?
        if (this.data.ordered_media) {
            this.data.ordered_media.forEach(function (element, index, array) {
                array[index] = new MediaModel(element);
            });
        }
    };

    PostModel.extend(Model);

    PostModel.prototype.isNew = function () {
        return String(this.data.id).substr(0, 1) === '0';
    };

    return PostModel;
});
