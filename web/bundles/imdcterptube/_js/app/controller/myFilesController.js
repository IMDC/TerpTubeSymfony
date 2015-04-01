define([
    'factory/mediaFactory'
], function (MediaFactory) {
    'use strict';

    var MyFilesController = function (model, options) {
        console.log('%s: %s- model=%o, options=%o', MyFilesController.TAG, 'constructor', model, options);

        this.model = model;
        this.options = options;

        $tt._instances.push(this);
    };

    MyFilesController.TAG = 'MyFiles';

    MyFilesController.prototype.onViewLoaded = function () {

    };

    MyFilesController.prototype.edit = function (mediaId, properties) {
        var media = this.model.getMedia(mediaId);
        if (!media) {
            throw new Error('media not found');
        }

        Object.keys(properties).forEach(function (key, index, array) {
            media.set(key, properties[key]);
        });

        return MediaFactory.edit(media);
    };

    MyFilesController.prototype.delete = function (mediaId, confirmed) {
        var media = this.model.getMedia(mediaId);
        if (!media) {
            throw new Error('media not found');
        }

        return MediaFactory.delete(media, confirmed);
    };

    return MyFilesController;
});
