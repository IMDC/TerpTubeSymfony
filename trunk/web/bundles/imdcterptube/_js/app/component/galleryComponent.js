define([
    'core/subscriber',
    //'core/mediaManager',
    'factory/mediaFactory',
    'component/recorderComponent',
    'core/helper',
    'extra'
], function (Subscriber, /*MediaManager, */MediaFactory, RecorderComponent, Helper) {
    'use strict';

    var GalleryComponent = function (options) {
        Subscriber.prototype.constructor.apply(this);

        this.options = options;
        this.activeMedia = null;

        this.thumbsContainerBounds = {
            width: 0,
            thumbWidth: 0,
            thumbsWidth: 0
        };

        this.bind__onClickAction = this._onClickAction.bind(this);
        this.bind__onClickNormalLeft = this._onClickNormalLeft.bind(this);
        this.bind__onClickNormalRight = this._onClickNormalRight.bind(this);
        this.bind__onClickThumbnail = this._onClickThumbnail.bind(this);
        this.bind__onRenderMedia = this._onRenderMedia.bind(this);
        this.bind__onRenderThumbnails = this._onRenderThumbnails.bind(this);
        /*this.bind__onGetMediaInfoSuccess = this._onGetMediaInfoSuccess.bind(this);
        this.bind__onGetMediaInfoError = this._onGetMediaInfoError.bind(this);*/

        this.$container = this.options.$container;
        this.$normal = this.$container.find(GalleryComponent.Binder.NORMAL);
        this.$preview = this.$container.find(GalleryComponent.Binder.PREVIEW);

        if (this.options.mode == GalleryComponent.Mode.PREVIEW) {
            this.$action = this.$container.find(GalleryComponent.Binder.PREVIEW_ACTION);
            this.$prev = this.$container.find(GalleryComponent.Binder.PREVIEW_PREV);
            this.$next = this.$container.find(GalleryComponent.Binder.PREVIEW_NEXT);
            this.$item = this.$container.find(GalleryComponent.Binder.PREVIEW_ITEM);
            this.$thumbs = this.$container.find(GalleryComponent.Binder.PREVIEW_THUMBS);

            $(window).on('resize', function (e) {
                var verticalAlign = function (index, value) {
                    return (($(window).height() - 70) / 2) - ($(this).height() / 2);
                };
                this.$prev.css('top', verticalAlign);
                this.$next.css('top', verticalAlign);

                this._resize();
            }.bind(this));

            $(window).trigger('resize');
        } else {
            this.$item = this.$container.find(GalleryComponent.Binder.NORMAL_ITEM);
            this.$action = this.$container.find(GalleryComponent.Binder.NORMAL_ACTION);
            this.$thumbs = this.$container.find(GalleryComponent.Binder.NORMAL_THUMBS);
            this.$left = this.$container.find(GalleryComponent.Binder.NORMAL_LEFT);
            this.$right = this.$container.find(GalleryComponent.Binder.NORMAL_RIGHT);

            this.$left.on('click', this.bind__onClickNormalLeft);
            this.$right.on('click', this.bind__onClickNormalRight);

            this._determineBounds();
        }

        this.$action.on('click', this.bind__onClickAction);

        this._populate();

        /*this.mediaManager = new MediaManager();
        $(this.mediaManager).on(MediaManager.Event.GET_INFO_SUCCESS, this.bind__onGetMediaInfoSuccess);
        $(this.mediaManager).on(MediaManager.Event.GET_INFO_ERROR, this.bind__onGetMediaInfoError);*/

        if (typeof this.options.mediaIds !== 'undefined') {
            //this.mediaManager.getInfo(this.options.mediaIds);
            MediaFactory.list(this.options.mediaIds)
                .done(function (data) {
                    this.options.media = data.media;
                    this._populate();
                }.bind(this))
                .fail(function () {
                    console.error('%s: media factory list', GalleryComponent.TAG);
                });
        }
    };

    GalleryComponent.extend(Subscriber);

    GalleryComponent.TAG = 'GalleryComponent';

    GalleryComponent.Mode = {
        PREVIEW: 0,
        INLINE: 1
    };

    GalleryComponent.Event = {
        DONE: 'eventDone',
        HIDDEN: 'eventHidden'
    };

    GalleryComponent.Binder = {
        NORMAL: '.gallery-normal',
        NORMAL_ITEM: '.gallery-normal-item',
        NORMAL_ACTION: '.gallery-normal-action a',
        NORMAL_THUMBS: '.gallery-normal-thumbs',
        NORMAL_LEFT: '.gallery-normal-left',
        NORMAL_RIGHT: '.gallery-normal-right',

        PREVIEW: '.gallery-preview',
        PREVIEW_ACTION: '.gallery-preview-action a',
        PREVIEW_PREV: '.gallery-preview-prev',
        PREVIEW_NEXT: '.gallery-preview-next',
        PREVIEW_ITEM: '.gallery-preview-item',
        PREVIEW_THUMBS: '.gallery-preview-thumbs'
    };

    GalleryComponent.prototype._launchRecorder = function (options, setMediaAsSource, setMediaAsRecorded) {
        RecorderComponent.render(options, function (e) {
            this.recorderCmp = e.recorderComponent;
            this.recorderCmp.subscribe(RecorderComponent.Event.DONE, function (e) {
                this.recorderCmp.hide();

                if (e.doPost)
                    window.location.assign(Routing.generate('imdc_thread_new_from_media', {mediaId: e.media.get('id')}));
            }.bind(this));
            this.recorderCmp.subscribe(RecorderComponent.Event.HIDDEN, function (e) {
                this.recorderCmp.destroy();
                this.show();
                this.paused = false;
            }.bind(this));

            this.paused = true;
            this.hide();
            if (setMediaAsSource)
                this.recorderCmp.setSourceMedia(this.activeMedia);
            if (setMediaAsRecorded)
                this.recorderCmp.setRecordedMedia(this.activeMedia);
            this.recorderCmp.show();
        }.bind(this));
    };

    GalleryComponent.prototype._onClickAction = function (e) {
        var action = $(e.currentTarget).data('action');

        switch (action) {
            case 1: // close
                this.hide();
                break;
            case 2: // full screen
                this._resize();
                Helper.toggleFullScreen(this.$item);
                break;
            case 3: // interp video
                this._launchRecorder({
                    tab: RecorderComponent.Tab.INTERPRETATION,
                    enableDoneAndPost: true
                }, true);
                break;
            case 4: // cut video
                this._launchRecorder({
                    tab: RecorderComponent.Tab.NORMAL,
                    mode: RecorderComponent.Mode.PREVIEW
                }, false, true);
                break;
        }
    };

    GalleryComponent.prototype._calcSlidePosition = function (goRight) {
        var cPos = parseInt(this.$thumbs.css('left'), 10);
        var nPos = cPos + this.thumbsContainerBounds.width;
        var bound;

        if (!goRight) {
            bound = Math.min(0, Math.max(cPos, nPos));
        } else {
            bound = Math.max(-this.thumbsContainerBounds.thumbsWidth, Math.min(cPos, nPos));
        }

        console.log(bound);

        return {cPos: cPos, nPos: nPos, bound: bound};
    };

    GalleryComponent.prototype._onClickNormalLeft = function (e) {
        e.preventDefault();

        var pos = this._calcSlidePosition();
        this.$thumbs.animate({left: pos.bound});
    };

    GalleryComponent.prototype._onClickNormalRight = function (e) {
        e.preventDefault();

        var pos = this._calcSlidePosition();
        this.$thumbs.animate({left: pos.bound});
    };

    GalleryComponent.prototype._resize = function () {
        var wh = $(window).height();
        //var ww = $(window).width();
        this.$item.css("height", Helper.isFullscreen() ? wh : wh - 210);
        this.$item.css("top", Helper.isFullscreen() ? 0 : 70);
        this.$item.css("background-color", Helper.isFullscreen() ? "#000" : "transparent");
        this.$item.css("width", Helper.isFullscreen() ? "100%" : "76%");
        //this.$item.css("left", Helper.isFullscreen() ? 0 : ww / 2 - $previewItem.width() / 2);
        this.$item.find("img").css("height", Helper.isFullscreen() ? "100%" : "auto");
    };

    GalleryComponent.prototype._determineBounds = function () {
        var $thumbs = this.$thumbs.find('li');

        this.thumbsContainerBounds.width = this.$thumbs.parent().width();
        this.thumbsContainerBounds.thumbWidth = $thumbs.first().width();
        this.thumbsContainerBounds.thumbsWidth = $thumbs.length * this.thumbsContainerBounds.thumbWidth;

        if (this.thumbsContainerBounds.thumbsWidth <= this.thumbsContainerBounds.width) {
            this.$left.attr('disabled', true).hide();
            this.$right.attr('disabled', true).hide();
        }
    };

    GalleryComponent.prototype._onClickThumbnail = function (e) {
        e.preventDefault();

        var mediaId = $(e.currentTarget).data('mid');
        this.activeMedia = $.grep(this.options.media, function (elementOfArray, indexInArray) {
            return elementOfArray.get('id') == mediaId;
        })[0];

        dust.render('gallery_media', {media: this.activeMedia.data}, this.bind__onRenderMedia);
    };

    GalleryComponent.prototype._onRenderMedia = function (err, out) {
        //TODO append instead and add slider so that media is loaded on demand but only once
        this.$item.html(out);

        var $recorderBtn = this.$action.filter('[data-action="3"]');
        var $cutBtn = this.$action.filter('[data-action="4"]');
        if (this.activeMedia.get('type') == 1) {
            $recorderBtn.show();
            if (this.options.mode == GalleryComponent.Mode.PREVIEW) {
                $cutBtn.show();
            }
        } else {
            $recorderBtn.hide();
            $cutBtn.hide();
        }

        $(window).trigger('resize');
    };

    GalleryComponent.prototype._onRenderThumbnails = function (err, out) {
        this.$thumbs.html(out);
        this.$thumbs.find('li').on('click', this.bind__onClickThumbnail);
    };

    GalleryComponent.prototype._populate = function () {
        if (typeof this.options.media === 'undefined' || this.options.media.length == 0)
            return;

        this.activeMedia = this.options.media[0];

        dust.render('gallery_media', {media: this.activeMedia.data}, this.bind__onRenderMedia);
        //TODO array representation of media models
        //dust.render('gallery_thumbnail', {media: this.options.media}, this.bind__onRenderThumbnails);
    };

    /*GalleryComponent.prototype._onGetMediaInfoSuccess = function (e) {
        //data, textStatus, jqXHR
        this.options.media = e.payload.media;
        this._populate();
        console.log('Success')
    };

    GalleryComponent.prototype._onGetMediaInfoError = function (e) {
        //jqXHR, textStatus, errorThrown
        console.log(e.jqXHR);
    };*/

    GalleryComponent.prototype.show = function () {
        if (this.options.mode != GalleryComponent.Mode.PREVIEW)
            return;

        this.$container.css({overflow: 'hidden'}); //FIXME css class

        this.$preview.fadeIn();
    };

    GalleryComponent.prototype.hide = function () {
        if (this.options.mode != GalleryComponent.Mode.PREVIEW)
            return;

        if (!this.paused)
            this.$container.removeAttr('style'); //FIXME css class

        this.$preview.fadeOut({
            complete: (function () {
                if (this.paused)
                    return;

                this._dispatch(GalleryComponent.Event.HIDDEN, {
                    galleryComponent: this
                });
            }).bind(this)
        });
    };

    GalleryComponent.prototype.destroy = function () {
        (this.options.mode == GalleryComponent.Mode.INLINE
            ? this.$normal
            : this.$preview).remove();
    };

    GalleryComponent.prototype.setMedia = function (media) {
        this.options.media = media;
        this._populate();
    };

    GalleryComponent.render = function (options, callback) {
        if (options.mode !== 'undefined') {
            if (options.mode == GalleryComponent.Mode.INLINE && options.$container === 'undefined') {
                throw new Error('Inline mode requires the $container option to be set explicitly.');
            }
        }

        var defaults = {
            $container: $('body'),
            mode: GalleryComponent.Mode.PREVIEW,
            media: []
        };

        options = options || defaults;
        for (var o in defaults) {
            options[o] = typeof options[o] != 'undefined' ? options[o] : defaults[o];
        }

        var template = options.mode == GalleryComponent.Mode.INLINE
            ? 'gallery_normal'
            : 'gallery_preview';
        dust.render(template, options.media, function (err, out) {
            options.$container.append(out);

            var cmp = new GalleryComponent(options);
            callback.call(cmp, {
                galleryComponent: cmp
            });
        });
    };

    return GalleryComponent;
});
