define([
    'core/mediaChooser',
    'core/recorder',
    'core/mediaManager',
    'core/helper'
], function(MediaChooser, Recorder, MediaManager, Helper) {
    "use strict";

    var Gallery = function(options) {
        console.log("%s: %s- options=%o", Gallery.TAG, "constructor", options);

        var defaults = {
            mode: Gallery.Mode.NORMAL
        };

        options = options || defaults;
        for (var o in defaults) {
            this[o] = typeof options[o] != "undefined" ? options[o] : defaults[o];
        }

        this.container = options.container;

        this.mediaManager = new MediaManager();
        this.media = null;
        this.activeMedia = null;

        this.thumbsContainerBounds = {
            width: null,
            thumbWidth: null,
            thumbsWidth: null
        };

        this.bind__onRender = this._onRender.bind(this);
        this.bind__onClickAction = this._onClickAction.bind(this);
        this.bind__onGetMediaInfoSuccess = this._onGetMediaInfoSuccess.bind(this);
        this.bind__onGetMediaInfoError = this._onGetMediaInfoError.bind(this);
        this.bind__onRenderMedia = this._onRenderMedia.bind(this);
        this.bind__onRenderThumbnails = this._onRenderThumbnails.bind(this);
        this.bind__onClickThumbnail = this._onClickThumbnail.bind(this);
        this.bind__onClickNormalLeft = this._onClickNormalLeft.bind(this);
        this.bind__onClickNormalRight = this._onClickNormalRight.bind(this);

        $(this.mediaManager).on(MediaManager.Event.GET_INFO_SUCCESS, this.bind__onGetMediaInfoSuccess);
        $(this.mediaManager).on(MediaManager.Event.GET_INFO_ERROR, this.bind__onGetMediaInfoError);

        /*dust.compileFn($("#mediaElementVideo").html(), "mediaElementVideo");
        dust.compileFn($("#mediaElementAudio").html(), "mediaElementAudio");
        dust.compileFn($("#mediaElementImage").html(), "mediaElementImage");
        dust.compileFn($("#mediaElementOther").html(), "mediaElementOther");*/
    };

    Gallery.TAG = "Gallery";

    Gallery.Mode = {
        NORMAL: 0,
        PREVIEW: 1
    };

    Gallery.Event = {
        READY: "eventReady",
        DONE: "eventDone",
        HIDDEN: "eventHidden"
    };

    Gallery.Binder = {
        NORMAL: ".gallery-normal",
        NORMAL_ITEM: ".gallery-normal-item",
        NORMAL_ACTION: ".gallery-normal-action a",
        NORMAL_THUMBS: ".gallery-normal-thumbs",
        NORMAL_LEFT: ".gallery-normal-left",
        NORMAL_RIGHT: ".gallery-normal-right",

        PREVIEW: ".gallery-preview",
        PREVIEW_ACTION: ".gallery-preview-action a",
        PREVIEW_PREV: ".gallery-preview-prev",
        PREVIEW_NEXT: ".gallery-preview-next",
        PREVIEW_ITEM: ".gallery-preview-item",
        PREVIEW_THUMBS: ".gallery-preview-thumbs"
    };

    Gallery.prototype.getContainer = function() {
        return this.mode == Gallery.Mode.NORMAL ? this.container : $("body");
    };

    Gallery.prototype._getElement = function(binder) {
        return this.getContainer().find(binder);
    };

    Gallery.prototype._resize = function(e) {
        var $previewItem = this._getElement(Gallery.Binder.PREVIEW_ITEM);
        var wh = $(window).height();
        var ww = $(window).width();
        $previewItem.css("height", Helper.isFullscreen() ? wh : wh - 210);
        $previewItem.css("top", Helper.isFullscreen() ? 0 : 70);
        $previewItem.css("background-color", Helper.isFullscreen() ? "#000" : "transparent");
        $previewItem.css("width", Helper.isFullscreen() ? "100%" : "76%");
        //$previewItem.css("left", Helper.isFullscreen() ? 0 : ww / 2 - $previewItem.width() / 2);
        $previewItem.find("img").css("height", Helper.isFullscreen() ? "100%" : "auto");
    };

    Gallery.prototype._launchRecorder = function(options, setMediaAsSource, setMediaAsRecorded) {
        this.recorder = new Recorder(options);
        $(this.recorder).on(Recorder.Event.READY, (function(e) {
            this.paused = true;
            this.hide();
            if (setMediaAsSource)
                this.recorder.setSourceMedia(this.activeMedia);
            if (setMediaAsRecorded)
                this.recorder.setRecordedMedia(this.activeMedia);
            this.recorder.show();
        }).bind(this));
        $(this.recorder).on(Recorder.Event.DONE, (function(e) {
            this.recorder.hide();

            if (e.doPost)
                window.location.assign(Routing.generate("imdc_thread_new_from_media", {mediaId: e.media.id}));
        }).bind(this));
        $(this.recorder).on(Recorder.Event.HIDDEN, (function(e) {
            this.recorder.destroy();
            this.show();
            this.paused = false;
        }).bind(this));
        this.recorder.render();
    };

    Gallery.prototype._onClickAction = function(e) {
        var action = $(e.currentTarget).data("action");

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
                    page: Recorder.Page.INTERPRETATION,
                    enableDoneAndPost: true
                }, true);
                break;
            case 4: // cut video
                this._launchRecorder({
                    page: Recorder.Page.NORMAL,
                    mode: Recorder.Mode.PREVIEW
                }, false, true);
                break;
        }
    };

    Gallery.prototype._onClickThumbnail = function(e) {
        e.preventDefault();

        var mediaId = $(e.currentTarget).data("mid");
        this.activeMedia = $.grep(this.media, function(elementOfArray, indexInArray) {
            return elementOfArray.id == mediaId;
        })[0];

        /*var typeToDustTemplate = function(type) {
         switch (type) {
         //FIXME
         case $tt.Core.MediaChooser.MEDIA_TYPE.VIDEO.id:
         return "mediaElementVideo";
         case $tt.Core.MediaChooser.MEDIA_TYPE.AUDIO.id:
         return "mediaElementAudio";
         case $tt.Core.MediaChooser.MEDIA_TYPE.IMAGE.id:
         return "mediaElementImage";
         case $tt.Core.MediaChooser.MEDIA_TYPE.OTHER.id:
         return "mediaElementOther";
         }
         };*/

        dust.render("gallery_media", {media: this.activeMedia}, this.bind__onRenderMedia);
    };

    Gallery.prototype._calcSlidePosition = function(goRight) {
        var cPos = parseInt(this.$thumbs.css("left"), 10);
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

    Gallery.prototype._onClickNormalLeft = function(e) {
        e.preventDefault();

        var pos = this._calcSlidePosition();
        this.$thumbs.animate({left: pos.bound});
    };

    Gallery.prototype._onClickNormalRight = function(e) {
        e.preventDefault();

        var pos = this._calcSlidePosition();
        this.$thumbs.animate({left: pos.bound});
    };

    Gallery.prototype._onRenderMedia = function(err, out) {
        //TODO append instead and add slider so that media is loaded on demand but only once
        this.$item.html(out);

        var recorderBtn = this.$action.filter("[data-action=3]");
        var cutBtn = this.$action.filter("[data-action=4]");
        if (this.activeMedia.type == 1) {
            recorderBtn.show();
            if (this.mode == Gallery.Mode.PREVIEW) {
                cutBtn.show();
            }
        } else {
            recorderBtn.hide();
            cutBtn.hide();
        }

        $(window).trigger("resize");
    };

    Gallery.prototype._onRenderThumbnails = function(err, out) {
        this.$thumbs.html(out);

        this.$thumbs.find("li").on("click", this.bind__onClickThumbnail);
    };

    Gallery.prototype._populate = function() {
        if (typeof this.media === "undefined" || this.media.length == 0)
            return;

        this.activeMedia = this.media[0];

        dust.render("gallery_media", {media: this.activeMedia}, this.bind__onRenderMedia);
        dust.render("gallery_thumbnail", {media: this.media}, this.bind__onRenderThumbnails);
    };

    Gallery.prototype._determineBounds = function() {
        var thumbs = this.$thumbs.find("li");

        this.thumbsContainerBounds.width = this.$thumbs.parent().width();
        this.thumbsContainerBounds.thumbWidth = thumbs.first().width();
        this.thumbsContainerBounds.thumbsWidth = thumbs.length * this.thumbsContainerBounds.thumbWidth;

        if (this.thumbsContainerBounds.thumbsWidth <= this.thumbsContainerBounds.width) {
            this.$left.attr("disabled", true).hide();
            this.$right.attr("disabled", true).hide();
        }
    };

    Gallery.prototype._bindUIEventsNormal = function() {
        this.$left.on("click", this.bind__onClickNormalLeft);
        this.$right.on("click", this.bind__onClickNormalRight);

        this._determineBounds();
    };

    Gallery.prototype._bindUIEventsPreview = function() {
        $(window).on("resize", (function(e) {
            var verticalAlign = function(index, value) {
                return (($(window).height() - 70) / 2) - ($(this).height() / 2);
            };
            this.$prev.css('top', verticalAlign);
            this.$next.css('top', verticalAlign);

            this._resize();
        }).bind(this));

        $(window).trigger("resize");
    };

    Gallery.prototype._cacheDomObjects = function() {
        if (this.mode == Gallery.Mode.NORMAL) {
            this.$item = this._getElement(Gallery.Binder.NORMAL_ITEM);
            this.$action = this._getElement(Gallery.Binder.NORMAL_ACTION);
            this.$thumbs = this._getElement(Gallery.Binder.NORMAL_THUMBS);
            this.$left = this._getElement(Gallery.Binder.NORMAL_LEFT);
            this.$right = this._getElement(Gallery.Binder.NORMAL_RIGHT);
        } else {
            this.$action = this._getElement(Gallery.Binder.PREVIEW_ACTION);
            this.$prev = this._getElement(Gallery.Binder.PREVIEW_PREV);
            this.$next = this._getElement(Gallery.Binder.PREVIEW_NEXT);
            this.$item = this._getElement(Gallery.Binder.PREVIEW_ITEM);
            this.$thumbs = this._getElement(Gallery.Binder.PREVIEW_THUMBS);
        }
    };

    Gallery.prototype._bindUIEvents = function() {
        this.$action.on("click", this.bind__onClickAction);

        if (this.mode == Gallery.Mode.NORMAL) {
            this._bindUIEventsNormal();
        } else {
            this._bindUIEventsPreview();
        }
    };

    Gallery.prototype._onRender = function(err, out) {
        this.getContainer().append(out);
        this._cacheDomObjects();
        this._bindUIEvents();

        $(this).trigger($.Event(Gallery.Event.READY, {}));
    };

    Gallery.prototype._onGetMediaInfoSuccess = function(e) {
        //data, textStatus, jqXHR
        this.media = e.payload.media;
        this._populate();
        console.log("Success")
    };

    Gallery.prototype._onGetMediaInfoError = function(e) {
        //jqXHR, textStatus, errorThrown
        console.log(e.jqXHR);
    };

    Gallery.prototype.render = function(mediaIds) {
        dust.render(this.mode == Gallery.Mode.NORMAL ? "gallery_normal" : "gallery_preview", this.media, this.bind__onRender);

        if (typeof mediaIds !== "undefined")
            this.mediaManager.getInfo(mediaIds);
    };

    Gallery.prototype.show = function() {
        if (this.mode != Gallery.Mode.PREVIEW)
            return;

        this.getContainer().css({overflow: "hidden"}); //FIXME css class

        this._getElement(Gallery.Binder.PREVIEW).fadeIn();
    };

    Gallery.prototype.hide = function() {
        if (this.mode != Gallery.Mode.PREVIEW)
            return;

        if (!this.paused)
            this.getContainer().removeAttr("style"); //FIXME css class

        this._getElement(Gallery.Binder.PREVIEW).fadeOut({
            complete: (function() {
                if (this.paused)
                    return;

                $(this).trigger($.Event(Gallery.Event.HIDDEN, {}));
            }).bind(this)
        });
    };

    Gallery.prototype.destroy = function() {
        this._getElement(this.mode == Gallery.Mode.NORMAL
            ? Gallery.Binder.NORMAL
            : Gallery.Binder.PREVIEW).remove();
    };

    Gallery.prototype.setMedia = function(media) {
        this.media = media;
        this._populate();
    };

    return Gallery;
});
