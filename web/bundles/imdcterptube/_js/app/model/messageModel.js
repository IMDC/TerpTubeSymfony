define([
    'model/model',
    'model/mediaModel',
    'extra'
], function (Model, MediaModel) {
    'use strict';

    var MessageModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);

        // replace key/value objects with models for all media
        // TODO consolidate under collection type?
        if (this.data.ordered_media) {
            this.data.ordered_media.forEach(function (element, index, array) {
                array[index] = new MediaModel(element);
            });
        }
    };

    MessageModel.extend(Model);

    return MessageModel;
});
