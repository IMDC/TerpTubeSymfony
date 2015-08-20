define([
    'component/galleryComponent'
], function (GalleryComponent) {
    'use strict';

    var ViewView = function (controller, options) {
        this.controller = controller;

        this.$container = options.container;
        this.$gallery = this.$container.find(ViewView.Binder.GALLERY);

        var media = this.controller.model.get('ordered_media');
        if (media.length > 0) {
            GalleryComponent.render({
                $container: this.$gallery,
                mode: GalleryComponent.Mode.INLINE,
                media: media
            }, function (e) {
                this.galleryCmp = e.galleryComponent;
                this.galleryCmp.show();
            }.bind(this));
        }

        $tt._instances.push(this);
    };

    ViewView.TAG = 'GroupViewView';

    ViewView.Binder = {
        GALLERY: '.group-gallery'
    };

    return ViewView;
});
