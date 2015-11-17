define([
    'component/galleryComponent'
], function (GalleryComponent) {
    'use strict';

    var ListView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickThumbnail = this._onClickThumbnail.bind(this);

        this.$container = options.container;
        this.$thumbnails = this.$container.find(ListView.Binder.THUMBNAILS);

        this.$thumbnails.on('click', this.bind__onClickThumbnail);

        $tt._instances.push(this);
    };

    ListView.TAG = 'ForumListView';

    ListView.Binder = {
        THUMBNAILS: '.tt-grid-div-body .expand'
    };

    ListView.prototype._onClickThumbnail = function (e) {
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

    return ListView;
});
