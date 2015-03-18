define([
    'model/model',
    'model/mediaModel',
    'extra',
    'underscore'
], function (Model, MediaModel) {
    'use strict';

    var MyFilesModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);

        // replace key/value objects with models for all media
        // TODO consolidate under collection type?
        if (this.data.media) {
            _.each(this.data.media, function (element, index, list) {
                list[index] = new MediaModel(element);

                // subscribe to model events
                list[index].subscribe(Model.Event.CHANGE, function (e) {
                    var cIndex = _.findIndex(this.data.media, e.model);
                    if (cIndex > -1) {
                        // bubble for model changes. prefix this collection's keypath
                        this._dispatch(Model.Event.CHANGE, 'media.' + cIndex);
                    }
                }.bind(this));
            }, this);
        }
    };

    MyFilesModel.extend(Model);

    MyFilesModel.prototype.getMedia = function (mediaId) {
        return _.find(this.data.media, function (media) {
            return media.get('id') == mediaId;
        });
    };

    return MyFilesModel;
});
