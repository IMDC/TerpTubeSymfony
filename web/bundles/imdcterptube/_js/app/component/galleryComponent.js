define([
    'core/subscriber',
    'factory/mediaFactory',
    'component/recorderComponent',
    'core/helper',
    'Sortable',
    'extra'
], function (Subscriber, MediaFactory, RecorderComponent, Helper, Sortable) {
    'use strict';

    var GalleryComponent = function (options) {
        Subscriber.prototype.constructor.apply(this);

        this.options = options;
        this.activeMedia = this.options.activeMedia;
        this.thumbsBounds = {
            width: 0,
            shiftWidth: 0,
            thumbsWidth: 0
        };

        this.bind__onClickItemContainer = this._onClickItemContainer.bind(this);
        this.bind__onClickPrevNext = this._onClickPrevNext.bind(this);
        this.bind__onClickAction = this._onClickAction.bind(this);
        this.bind__onClickLeftRight = this._onClickLeftRight.bind(this);
        this.bind__onClickThumbnail = this._onClickThumbnail.bind(this);
        this.bind__onClickThumbRemove = this._onClickThumbRemove.bind(this);
        this.bind__onRenderMedia = this._onRenderMedia.bind(this);
        this.bind__onRenderThumbnails = this._onRenderThumbnails.bind(this);
        this.bind__onWindowResize = this._onWindowResize.bind(this);

        this.$container = this.options.$container;
        this.$inner = this.$container.find('[id="' + this.options.identifier + '"]' + GalleryComponent.Binder.INNER);
        this.$item = this.$inner.find(GalleryComponent.Binder.ITEM);
        this.$prev = this.$inner.find(GalleryComponent.Binder.PREV);
        this.$next = this.$inner.find(GalleryComponent.Binder.NEXT);
        this.$actions = this.$inner.find(GalleryComponent.Binder.ACTION);
        this.$carousel = this.$inner.find(GalleryComponent.Binder.CAROUSEL);
        this.$thumbs = this.$inner.find(GalleryComponent.Binder.THUMBS);
        this.$left = this.$inner.find(GalleryComponent.Binder.LEFT);
        this.$right = this.$inner.find(GalleryComponent.Binder.RIGHT);

        this.$item.on('click', this.bind__onClickItemContainer);
        this.$prev.find('i.fa').on('click', this.bind__onClickPrevNext);
        this.$next.find('i.fa').on('click', this.bind__onClickPrevNext);
        this.$actions.on('click', this.bind__onClickAction);
        this.$left.find('i.fa').on('click', this.bind__onClickLeftRight);
        this.$right.find('i.fa').on('click', this.bind__onClickLeftRight);
        $(window).on('resize', this.bind__onWindowResize);

        if (this.options.canEdit) {
            this.sortable = Sortable.create(this.$thumbs.find('ul')[0], {
                onSort: function (evt) {
                    var media = this.options.media[evt.oldIndex];
                    var swapped = this.options.media[evt.newIndex];
                    this.options.media[evt.newIndex] = media;
                    this.options.media[evt.oldIndex] = swapped;

                    this._updateButtonStates();

                    this._dispatch(GalleryComponent.Event.CHANGE, {
                        galleryComponent: this
                    });
                }.bind(this)
            });
        }

        if (typeof this.options.mediaIds !== 'undefined') {
            MediaFactory.list(this.options.mediaIds)
                .done(function (data) {
                    this.options.media = data.media;
                    this._populate();
                }.bind(this))
                .fail(function (data) {
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
        CHANGE: 'eventChange',
        REMOVED_MEDIA: 'eventRemovedMedia',
        DONE: 'eventDone',
        HIDDEN: 'eventHidden'
    };

    GalleryComponent.Binder = {
        INNER: '.gallery-inner',
        ITEM: '.gallery-item',
        PREV: '.gallery-prev',
        NEXT: '.gallery-next',
        ACTION: '.gallery-action a',
        CAROUSEL : '.gallery-carousel',
        THUMBS: '.gallery-thumbs',
        THUMB_REMOVE: '.gallery-thumb-remove',
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
                if (this.options.mode == GalleryComponent.Mode.PREVIEW) {
                    this.show();
                }
                this.paused = false;
            }.bind(this));

            this.paused = true;
            if (this.options.mode == GalleryComponent.Mode.PREVIEW)
                this.hide();
            if (setMediaAsSource)
                this.recorderCmp.setSourceMedia(this.activeMedia);
            if (setMediaAsRecorded)
                this.recorderCmp.setRecordedMedia(this.activeMedia);
            this.recorderCmp.show();
        }.bind(this));
    };

    GalleryComponent.prototype._onClickItemContainer = function (e) {
        if (this.options.mode != GalleryComponent.Mode.PREVIEW)
            return;

        if (e.target === this.$item[0]) {
            this.hide();
        }
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
        this.$thumbs.find('ul').css({left: pos});
    };

    GalleryComponent.prototype._onClickThumbnail = function (e) {
        e.preventDefault();

        //TODO consolidate
        var mediaId = $(e.currentTarget).data('mid');
        this.activeMedia = $.grep(this.options.media, function (elementOfArray, indexInArray) {
            return elementOfArray.get('id') == mediaId;
        })[0];

        this._renderMedia();
    };

    GalleryComponent.prototype._onClickThumbRemove = function (e) {
        e.preventDefault();

        //TODO consolidate
        var mediaId = $(e.currentTarget).data('mid');
        var media = $.grep(this.options.media, function (elementOfArray, indexInArray) {
            return elementOfArray.get('id') == mediaId;
        })[0];

        this.removeMedia(media);

        this._dispatch(GalleryComponent.Event.REMOVED_MEDIA, {
            media: media,
            galleryComponent: this
        });
    };
    
    GalleryComponent.prototype.clear = function() {
	for (var i in this.options.media)
	{
	    var media = this.options.media[i];
	    this.removeMedia(media);
	    this._dispatch(GalleryComponent.Event.REMOVED_MEDIA, {
	            media: media,
	            galleryComponent: this
	        });
	}
    }

    GalleryComponent.prototype._updateButtonStates = function (slidePosition) {
        var $selThumbnail = this.$thumbs.find('li[data-mid="' + this.activeMedia.get('id') + '"]');
        this.$thumbs.find('div').removeClass('selected');

        // actions
        var $closeBtn = this.$actions.filter('[data-action="1"]');
        var $fullscreenBtn = this.$actions.filter('[data-action="2"]');
        var $recorderBtn = this.$actions.filter('[data-action="3"]');
        var $cutBtn = this.$actions.filter('[data-action="4"]');
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
        var slidePosition = Math.abs(slidePosition !== undefined
            ? slidePosition
            : parseInt(this.$thumbs.find('ul').css('left'), 10));

        this.$left.find('i.fa').toggleClass('disabled', slidePosition <= 0);
        this.$right.find('i.fa').toggleClass('disabled',
            (slidePosition + this.thumbsBounds.width) >= this.thumbsBounds.thumbsWidth);
        $selThumbnail.find('div').addClass('selected');
    };

    GalleryComponent.prototype._focusSelectedThumbnail = function () {
        // bring selected thumbnail into view on carousel
        var $selThumbnail = this.$thumbs.find('li[data-mid="' + this.activeMedia.get('id') + '"]');
        var position = $selThumbnail.position();
        if (!position) {
            return;
        }

        var width = $selThumbnail.outerWidth(true);
        position.right = position.left + width;
        position.center = position.left + (width / 2);

        var slidePosition = Math.abs(parseInt(this.$thumbs.find('ul').css('left'), 10));
        var visibleRegion = {
            left: slidePosition,
            right: slidePosition + this.thumbsBounds.width,
            center: slidePosition + (this.thumbsBounds.width / 2)
        };
        var right = position.center > visibleRegion.center;
        var centerDiff = Math.abs(position.center - visibleRegion.center);
        var fPos = slidePosition + (right ? centerDiff : -centerDiff);

        if (!right) {
            fPos = Math.min(0, -fPos);
        } else {
            fPos = -Math.min(fPos, (visibleRegion.right >= this.thumbsBounds.thumbsWidth) ? visibleRegion.left : this.thumbsBounds.thumbsWidth - this.thumbsBounds.width);
        }

        this.$thumbs.find('ul').css({left: fPos});

        /*console.log(position);
        console.log(fPos);
        console.log(this.thumbsBounds.thumbsWidth);
        console.log(slidePosition);*/

        this._updateButtonStates(fPos);
    };

    GalleryComponent.prototype._onWindowResize = function (e) {
        // hide the carousel if there's less than two media
        this.options.media.length <= 1 && !this.options.canEdit
            ? this.$carousel.hide()
            : this.$carousel.show();

        var container = this.$container.prop('tagName').toLowerCase() === 'body'
            ? $(window)
            : this.$container;
        var carouselHeight = this.$carousel.is(':hidden') ? 0 : this.$carousel.outerHeight(true);
        var itemHeight = Helper.isFullscreen()
            ? $(window).height()
            : container.height() - carouselHeight;

        // container widths/heights/positioning
        var thumbsWidth = container.width() - (this.$left.outerWidth(true) + this.$right.outerWidth(true)) - 0.5;

        /*console.log(this.$left.outerWidth(true));
        console.log(this.$right.outerWidth(true));
        console.log(thumbsWidth);*/

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

        if (!this.activeMedia) {
            this.$actions.hide();
            this.$prev.addClass('disabled');
            this.$next.addClass('disabled');
            this.$left.find('i.fa').addClass('disabled');
            this.$right.find('i.fa').addClass('disabled');
            return;
        }

        // bring selected thumbnail into focus
        this._focusSelectedThumbnail();

        // fullscreen
        this.$item.toggleClass('fullscreen', Helper.isFullscreen());
    };

    GalleryComponent.prototype._onRenderMedia = function (err, out) {
        //TODO append instead and add slider so that media is loaded on demand but only once
        this.$item.html(out);

        $(window).trigger('resize');
    };

    GalleryComponent.prototype._onRenderThumbnails = function (err, out) {
        this.$thumbs.find('ul').html(out);
        this.$thumbs.find('li').on('click', this.bind__onClickThumbnail);
        this.$thumbs.find('li').find('span').on('click', this.bind__onClickThumbRemove);

        $(window).trigger('resize');
    };

    GalleryComponent.prototype._renderMedia = function () {
        dust.render('media_element', {
            media: this.activeMedia ? this.activeMedia.data : null,
            enable_controls: true,
            preload_media: false
        }, this.bind__onRenderMedia);
    };

    GalleryComponent.prototype._renderMediaAndThumbnails = function () {
        if (!this.activeMedia) {
            this.$item.html('');
            this.$thumbs.find('ul').html('');

            $(window).trigger('resize');
            return;
        }

        //TODO array representation of media models
        this._renderMedia();
        dust.render('gallery_thumbnail', {media: this.options.media, can_edit: this.options.canEdit}, this.bind__onRenderThumbnails);
    };

    GalleryComponent.prototype._populate = function () {
        this.activeMedia = this.options.media.length == 0
            ? null
            : !this.activeMedia
                ? this.options.media[0]
                : this.activeMedia;

        this._renderMediaAndThumbnails();
    };

    GalleryComponent.prototype.show = function (animate) {
        animate = animate !== undefined
            ? animate
            : (this.options.mode == GalleryComponent.Mode.PREVIEW);

        if (this.options.mode == GalleryComponent.Mode.PREVIEW) {
            this.$container.addClass('tt-gallery-modal');
        }

        var complete = function () {
            this._populate();
        }.bind(this);

        if (animate) {
            this.$inner.fadeIn()
                .promise()
                .done(complete);
        } else {
            this.$inner.show()
                .promise()
                .done(complete);
        }
    };

    GalleryComponent.prototype.hide = function (animate) {
        animate = animate !== undefined
            ? animate
            : (this.options.mode == GalleryComponent.Mode.PREVIEW);

        if (this.options.mode == GalleryComponent.Mode.PREVIEW && !this.paused) {
            this.$container.removeClass('tt-gallery-modal');
        }

        var complete = function () {
            if (this.paused)
                return;

            this._dispatch(GalleryComponent.Event.HIDDEN, {
                galleryComponent: this
            });
        }.bind(this);

        if (animate) {
            this.$inner.fadeOut()
                .promise()
                .done(complete);
        } else {
            this.$inner.show()
                .promise()
                .done(complete);
        }
    };

    GalleryComponent.prototype.destroy = function () {
        this.$inner.remove();
    };

    GalleryComponent.prototype.setMedia = function (media) {
        this.options.media = media;
        this._populate();
    };

    GalleryComponent.prototype.addMedia = function (media) {
        this.options.media.push(media);
        this._populate();
    };

    GalleryComponent.prototype.removeMedia = function (media) {
        var index = $.inArray(media, this.options.media);
        if (index == -1) {
            return;
        }

        this.options.media.splice(index, 1);

        if (this.options.media.length == 0) {
            this.activeMedia = null;
        } else {
            if (index >= this.options.media.length) {
                index--;
            }

            this.activeMedia = this.options.media[index];
        }

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
            identifier: Math.random(),
            mode: GalleryComponent.Mode.PREVIEW,
            canEdit: false,
            media: [],
            activeMedia: null
        };

        options = options || defaults;
        for (var o in defaults) {
            options[o] = typeof options[o] != 'undefined' ? options[o] : defaults[o];
        }

        var template = options.mode == GalleryComponent.Mode.INLINE
            ? 'gallery_inline'
            : 'gallery_preview';
        dust.render(template, {identifier: options.identifier}, function (err, out) {
            options.$container.append(out);

            var cmp = new GalleryComponent(options);
            callback.call(cmp, {
                galleryComponent: cmp
            });
        });
    };

    return GalleryComponent;
});
