define(['core/mediaChooser', 'core/recorder'], function(MediaChooser, Recorder) {
    "use strict";

    var Gallery = function(options) {
        this.container = options.container;

        this.media = null;
        this.thumbsContainerBounds = {
            width: null,
            thumbWidth: null,
            thumbsWidth: null
        };

        this.bind__onClickArrowLeft = this._onClickArrowLeft.bind(this);
        this.bind__onClickArrowRight = this._onClickArrowRight.bind(this);
        this.bind__onClickThumbnail = this._onClickThumbnail.bind(this);

        dust.compileFn($("#mediaElementVideo").html(), "mediaElementVideo");
        dust.compileFn($("#mediaElementAudio").html(), "mediaElementAudio");
        dust.compileFn($("#mediaElementImage").html(), "mediaElementImage");
        dust.compileFn($("#mediaElementOther").html(), "mediaElementOther");
    };

    Gallery.TAG = "Gallery";

    Gallery.Binder = {
        WORKING: ".gallery-working",
        CONTAINER_INNER: ".gallery-container-inner",
        CONTENT: ".gallery-content",
        ARROW_LEFT: ".gallery-arrow-left",
        ARROW_RIGHT: ".gallery-arrow-right",
        CONTAINER_THUMBS: ".gallery-container-thumbs",
        RECORDER: ".gallery-recorder"
    };

    Gallery.prototype.getContainer = function() {
        return this.container;
    };

    Gallery.prototype._getElement = function(binder) {
        return this.getContainer().find(binder);
    };

    Gallery.prototype.bindUIEvents = function() {
        this._getElement(Gallery.Binder.ARROW_LEFT).on("click", this.bind__onClickArrowLeft);
        this._getElement(Gallery.Binder.ARROW_RIGHT).on("click", this.bind__onClickArrowRight);
        this._getElement(Gallery.Binder.CONTAINER_THUMBS).find("li").on("click", this.bind__onClickThumbnail);

        this._getMediaInfo();
        this._determineBounds();
    };

    Gallery.prototype._onClickArrowLeft = function(e) {
        e.preventDefault();

        var pos = this._calcSlidePosition();
        this._getElement(Gallery.Binder.CONTAINER_THUMBS).animate({left: pos.bound});
    };

    Gallery.prototype._onClickArrowRight = function(e) {
        e.preventDefault();

        var pos = this._calcSlidePosition();
        this._getElement(Gallery.Binder.CONTAINER_THUMBS).animate({left: pos.bound});
    };

    Gallery.prototype._onClickThumbnail = function(e) {
        e.preventDefault();

        var media = $.grep(this.media, function(elementOfArray, indexInArray) {
            return elementOfArray.id == $(e.currentTarget).data("mid");
        })[0];

        var typeToDustTemplate = function(type) {
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
        };

        dust.render(typeToDustTemplate(media.type), {media: media}, (function(err, out) {
            var container = this._getElement(Gallery.Binder.CONTENT);
            container.html(out); //TODO append instead and add slider so that media is loaded on demand but only once

            if (media.type == $tt.Core.MediaChooser.MEDIA_TYPE.VIDEO.id) { //FIXME
                container.find("button").on("click", (function(e) {
                    this.recorder = new $tt.Core.Recorder({
                        container: this._getElement(Gallery.Binder.RECORDER),
                        page: $tt.Core.Recorder.Page.INTERPRETATION,
                        enableDoneAndPost: true
                    });
                    $(this.recorder).on($tt.Core.Recorder.Event.READY, (function(e) {
                        this.recorder.setSourceMedia(media);
                        this.recorder.show();
                    }).bind(this));
                    $(this.recorder).on($tt.Core.Recorder.Event.DONE, (function(e) {
                        this.recorder.hide();
                        //this.recorder = null;
                        //this._getElement(Gallery.Binder.RECORDER).html("");

                        if (e.doPost)
                            window.location.assign(Routing.generate("imdc_thread_new_from_media", {mediaId: e.media.id}));
                    }).bind(this));
                    this.recorder.render();
                }).bind(this));
            }
        }).bind(this));
    };

    Gallery.prototype._getMediaInfo = function() {
        var mediaIds = [];
        this._getElement(Gallery.Binder.CONTAINER_THUMBS).find("li").each(function(index, element) {
            mediaIds.push($(element).data("mid"));
        });

        this._getElement(Gallery.Binder.WORKING).show();

        $.ajax({
            url: Routing.generate("imdc_myfiles_get_info"),
            data: {mediaIds: mediaIds},
            type: 'POST',
            success: (function(data, textStatus, jqXHR) {
                //console.log("%s: %s: %s", Post.TAG, "handlePage", "success");

                this.media = data.media;

                this._getElement(Gallery.Binder.WORKING).hide();
                this._getElement(Gallery.Binder.CONTAINER_INNER).show();
                this._getElement(Gallery.Binder.CONTAINER_THUMBS).find("li").first().click();
            }).bind(this),
            error: (function(request) {
                //console.log("%s: %s: %s", Post.TAG, "handlePage", "error");

                console.log(request.statusText);
            }).bind(this)
        });
    };

    Gallery.prototype._determineBounds = function() {
        var thumbsContainer = this._getElement(Gallery.Binder.CONTAINER_THUMBS);
        var thumbs = thumbsContainer.find("li");

        this.thumbsContainerBounds.width = this._getElement(Gallery.Binder.CONTAINER_THUMBS).width();
        this.thumbsContainerBounds.thumbWidth = thumbs.width();
        this.thumbsContainerBounds.thumbsWidth = thumbs.length * this.thumbsContainerBounds.thumbWidth;

        if (this.thumbsContainerBounds.thumbsWidth <= this.thumbsContainerBounds.width) {
            this._getElement(Gallery.Binder.ARROW_LEFT).attr("disabled", true).hide();
            this._getElement(Gallery.Binder.ARROW_RIGHT).attr("disabled", true).hide();
        }
    };

    Gallery.prototype._getThumbsSliderPosition = function() {
        return parseInt(this._getElement(Gallery.Binder.CONTAINER_THUMBS).find("ol").css("left"), 10);
    };

    Gallery.prototype._calcSlidePosition = function(goRight) {
        var cPos = this._getThumbsSliderPosition();
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

    return Gallery;
});
