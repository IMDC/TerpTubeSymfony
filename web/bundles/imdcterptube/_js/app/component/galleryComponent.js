define([
    'core/subscriber',
    'factory/mediaFactory',
    'component/recorderComponent',
    'core/helper',
    'extra'
], function (Subscriber, MediaFactory, RecorderComponent, Helper) {
    'use strict';

    var GalleryComponent = function (options) {
        Subscriber.prototype.constructor.apply(this);

        this.options = options;
        this.activeMedia = null;

        this.thumbsBounds = {
            shiftWidth: 0,
            thumbsWidth: 0
        };

        this.bind__onClickPrevNext = this._onClickPrevNext.bind(this);
        this.bind__onClickAction = this._onClickAction.bind(this);
        this.bind__onClickLeftRight = this._onClickLeftRight.bind(this);
        this.bind__onClickThumbnail = this._onClickThumbnail.bind(this);
        this.bind__onRenderMedia = this._onRenderMedia.bind(this);
        this.bind__onRenderThumbnails = this._onRenderThumbnails.bind(this);
        this.bind__onWindowResize = this._onWindowResize.bind(this);

        this.$container = this.options.$container;
        this.$normal = this.$container.find(GalleryComponent.Binder.INLINE);
        this.$preview = this.$container.find(GalleryComponent.Binder.PREVIEW);

        this.$item = this.$container.find(GalleryComponent.Binder.ITEM);
        this.$prev = this.$container.find(GalleryComponent.Binder.PREV);
        this.$next = this.$container.find(GalleryComponent.Binder.NEXT);
        this.$action = this.$container.find(GalleryComponent.Binder.ACTION);
        this.$carousel = this.$container.find(GalleryComponent.Binder.CAROUSEL);
        this.$thumbs = this.$container.find(GalleryComponent.Binder.THUMBS);
        this.$left = this.$container.find(GalleryComponent.Binder.LEFT);
        this.$right = this.$container.find(GalleryComponent.Binder.RIGHT);

        this.$prev.find('i.fa').on('click', this.bind__onClickPrevNext);
        this.$next.find('i.fa').on('click', this.bind__onClickPrevNext);
        this.$action.on('click', this.bind__onClickAction);
        this.$left.find('i.fa').on('click', this.bind__onClickLeftRight);
        this.$right.find('i.fa').on('click', this.bind__onClickLeftRight);
        $(window).on('resize', this.bind__onWindowResize);

        this._populate();

        if (typeof this.options.mediaIds !== 'undefined') {
            $(window).trigger('resize');

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
        INLINE: '.gallery-normal',
        PREVIEW: '.gallery-preview',
        ITEM: '.gallery-item',
        PREV: '.gallery-prev',
        NEXT: '.gallery-next',
        ACTION: '.gallery-action a',
        CAROUSEL : '.gallery-carousel',
        THUMBS: '.gallery-thumbs',
        LEFT: '.gallery-left',
        RIGHT: '.gallery-right'
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

    GalleryComponent.prototype._onClickPrevNext = function (e) {
        e.preventDefault();

        var next = $(e.currentTarget).hasClass('next');

        var index = $.inArray(this.activeMedia, this.options.media);
        if (!next && (index - 1) < 0 || next && (index + 1) >= this.options.media.length) {
            return;
        }

        this.activeMedia = this.options.media[index + (next ? 1 : -1)];

        this._renderMedia();
    };

    GalleryComponent.prototype._onClickAction = function (e) {
        var action = $(e.currentTarget).data('action');

        switch (action) {
            case 1: // close
                this.hide();
                break;
            case 2: // fullscreen
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

    GalleryComponent.prototype._calcCarouselPosition = function (right) {
        var cPos = Math.abs(parseInt(this.$thumbs.find('ul').css('left'), 10));
        var nPos, // new
            tPos, // tail
            rNPos, // real new
            fPos; // final

        if (!right) {
            nPos = cPos - this.thumbsBounds.shiftWidth;
            fPos = Math.min(0, -nPos);
        } else {
            nPos = cPos + this.thumbsBounds.width;
            tPos = cPos + (this.thumbsBounds.thumbsWidth - nPos);
            rNPos = cPos + this.thumbsBounds.shiftWidth;
            fPos = -Math.min(rNPos, (nPos >= this.thumbsBounds.thumbsWidth) ? cPos : tPos);
        }

        return fPos;
    };

    GalleryComponent.prototype._onClickLeftRight = function (e) {
        e.preventDefault();

        var pos = this._calcCarouselPosition($(e.currentTarget).hasClass('right'));
        this._updateButtonStates(pos);
        this.$thumbs.find('ul').animate({left: pos});
    };

    GalleryComponent.prototype._onClickThumbnail = function (e) {
        e.preventDefault();

        var mediaId = $(e.currentTarget).data('mid');
        this.activeMedia = $.grep(this.options.media, function (elementOfArray, indexInArray) {
            return elementOfArray.get('id') == mediaId;
        })[0];

        this._renderMedia();
    };

    GalleryComponent.prototype._updateButtonStates = function (slidePosition) {
        if (!this.activeMedia) {
            this.$carousel.hide();
            return;
        }

        var $selThumbnail = this.$thumbs.find('[data-mid="' + this.activeMedia.get('id') + '"]');
        this.$thumbs.find('div').removeClass('selected');

        // action
        var $closeBtn = this.$action.filter('[data-action="1"]');
        var $fullscreenBtn = this.$action.filter('[data-action="2"]');
        var $recorderBtn = this.$action.filter('[data-action="3"]');
        var $cutBtn = this.$action.filter('[data-action="4"]');
        var isPreviewMode = this.options.mode == GalleryComponent.Mode.PREVIEW;

        $closeBtn.toggle(isPreviewMode);
        $fullscreenBtn.show();
        if (this.activeMedia.get('type') == 1) {
            $recorderBtn.show();
            $cutBtn.toggle(isPreviewMode);
        } else {
            $recorderBtn.hide();
            $cutBtn.hide();
        }

        // preview
        this.$prev.toggleClass('disabled', $selThumbnail.is(':first-child'));
        this.$next.toggleClass('disabled', $selThumbnail.is(':last-child'));

        // carousel
        var slidePosition = slidePosition !== undefined
            ? slidePosition
            : parseInt(this.$thumbs.find('ul').css('left'), 10);

        this.$left.find('i.fa').toggleClass('disabled', Math.abs(slidePosition) <= 0);
        this.$right.find('i.fa').toggleClass('disabled',
            (Math.abs(slidePosition) + this.thumbsBounds.width) >= this.thumbsBounds.thumbsWidth);
        $selThumbnail.find('div').addClass('selected');
        this.$carousel.show();
    };

    GalleryComponent.prototype._onWindowResize = function (e) {
        var containerHeight = this.$container.prop('tagName').toLowerCase() === 'body'
            ? $(window).height()
            : this.$container.height();
        var itemHeight = Helper.isFullscreen()
            ? $(window).height()
            : containerHeight - this.$carousel.outerHeight(true);

        // container widths/heights/positioning
        var thumbsWidth = this.$container.width() - (this.$left.outerWidth(true) + this.$right.outerWidth(true));
        var verticalAlign = function (index, value) {
            return (itemHeight / 2) - ($(this).height() / 2);
        };

        this.$item.css({height: itemHeight, 'line-height': itemHeight + 'px'});
        this.$thumbs.width(thumbsWidth);
        this.$prev.css('top', verticalAlign);
        this.$next.css('top', verticalAlign);

        // set media element widths/heights/positioning for gallery context
        this.$item.find('.tt-media-video').css({height: itemHeight});
        this.$item.find('.tt-media-img').css({'max-height': itemHeight});

        // inner widths
        var width = this.$thumbs.width();
        var $thumbnails = this.$thumbs.find('li');
        var initialThumbMargin = parseInt($thumbnails.eq(0).css('margin-left'), 10);
        var thumbWidth = $thumbnails.eq(0).outerWidth(true) - initialThumbMargin;
        var numThumbsWidth = Math.floor(width / thumbWidth);

        this.thumbsBounds.width = width;
        this.thumbsBounds.shiftWidth = thumbWidth * numThumbsWidth;
        this.thumbsBounds.thumbsWidth = (thumbWidth * $thumbnails.length) + initialThumbMargin;

        // button states
        this._updateButtonStates();

        // fullscreen
        this.$item.toggleClass('fullscreen', Helper.isFullscreen());
    };

    GalleryComponent.prototype._onRenderMedia = function (err, out) {
        //TODO append instead and add slider so that media is loaded on demand but only once
        this.$item.find('div').html(out);

        $(window).trigger('resize');
    };

    GalleryComponent.prototype._renderMedia = function () {
        dust.render('media_element', {
            media: this.activeMedia.data,
            enableControls: true,
            preload: false
        }, this.bind__onRenderMedia);
    };

    GalleryComponent.prototype._onRenderThumbnails = function (err, out) {
        this.$thumbs.find('ul').html(out);
        this.$thumbs.find('li').on('click', this.bind__onClickThumbnail);

        $(window).trigger('resize');
    };

    GalleryComponent.prototype._populate = function () {
        if (typeof this.options.media === 'undefined' || this.options.media.length == 0)
            return;

        this.activeMedia = this.options.media[0];

        this._renderMedia();
        //TODO array representation of media models
        dust.render('gallery_thumbnail', {media: this.options.media}, this.bind__onRenderThumbnails);
    };

    GalleryComponent.prototype.show = function () {
        if (this.options.mode != GalleryComponent.Mode.PREVIEW)
            return;

        this.$container.addClass('tt-gallery-modal');

        this.$preview.fadeIn();
    };

    GalleryComponent.prototype.hide = function () {
        if (this.options.mode != GalleryComponent.Mode.PREVIEW)
            return;

        if (!this.paused)
            this.$container.removeClass('tt-gallery-modal');

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
            ? 'gallery_inline'
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
