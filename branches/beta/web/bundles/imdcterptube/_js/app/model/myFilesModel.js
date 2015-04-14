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
            // special case: knp paginator 'items.getArrayCopy' returns associative array for page > 1
            // (spliced results > 0 in list) resulting in an object instead of an array after being serialized to json
            var old = this.data.media;
            this.data.media = [];

            _.each(old, function (element, index, list) {
                var media = new MediaModel(element);

                // subscribe to model events
                media.subscribe(Model.Event.CHANGE, function (e) {
                    var cIndex = _.findIndex(this.data.media, e.model);
                    if (cIndex > -1) {
                        // bubble for model changes. prefix this collection's keypath
                        this._dispatch(Model.Event.CHANGE, 'media.' + cIndex);
                    }
                }.bind(this));

                this.data.media.push(media);
            }, this);

            old = null;
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
