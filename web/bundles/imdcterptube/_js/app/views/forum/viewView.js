define([
    'core/gallery'
], function (Gallery) {
    'use strict';

    var ViewView = function (controller, options) {
        this.controller = controller;

        this.gallery = new Gallery({
            container: $('.gallery-container')
        });
        $(this.gallery).on($tt.Core.Gallery.Event.READY, (function (e) {
            this.gallery.setMedia(this.controller.model.get('title_media'));
        }).bind(this));
        this.gallery.render();

        $tt._instances.push(this);
    };

    ViewView.TAG = 'ForumViewView';

    return ViewView;
});
