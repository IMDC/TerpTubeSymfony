define([
    'component/galleryComponent'
], function (GalleryComponent) {
    'use strict';

    var ViewView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickThumbnail = this._onClickThumbnail.bind(this);

        this.$container = options.container;
        this.$gallery = this.$container.find(ViewView.Binder.GALLERY);
        this.$thumbnails = this.$container.find(ViewView.Binder.THUMBNAILS);

        this.$thumbnails.on('click', this.bind__onClickThumbnail);

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
        GALLERY: '.group-gallery',
        THUMBNAILS: '.tt-grid-div-body .expand'
    };

    ViewView.prototype._onClickThumbnail = function (e) {
        e.preventDefault();

        GalleryComponent.render({
            mediaIds: [$(e.currentTarget).data('mid')]
        }, function (e) {
            this.galleryCmp = e.galleryComponent;
            this.galleryCmp.subscribe(GalleryComponent.Event.HIDDEN, function (e) {
                this.galleryCmp.destroy();
            }.bind(this));
            this.galleryCmp.show();
        }.bind(this));
    };

    return ViewView;
});
