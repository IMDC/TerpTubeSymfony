define([
    'component/galleryComponent'
], function (GalleryComponent) {
    'use strict';

    var ViewView = function (controller, options) {
        this.controller = controller;

        GalleryComponent.render({
            $container: $('.gallery-container'),
            mode: GalleryComponent.Mode.INLINE,
            media: this.controller.model.get('ordered_media')
        }, function (e) {
            this.galleryCmp = e.galleryComponent;
        }.bind(this));

        $tt._instances.push(this);
    };

    ViewView.TAG = 'ForumViewView';

    return ViewView;
});
