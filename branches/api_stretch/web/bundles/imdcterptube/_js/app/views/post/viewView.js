define([
    'model/model',
    'component/galleryComponent'
], function (Model, GalleryComponent) {
    'use strict';

    var ViewView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickHoverKeyPoint = this._onClickHoverKeyPoint.bind(this);
        this.bind__onClickNew = this._onClickNew.bind(this);
        this.bind__onClickEdit = this._onClickEdit.bind(this);
        this.bind__onClickDelete = this._onClickDelete.bind(this);
        this.bind__onModelChange = this._onModelChange.bind(this);

        this.$container = $(ViewView.Binder.CONTAINER + '[data-pid="' + this.controller.model.get('id') + '"]');
        this.$timelineKeyPoint = this.$container.find(ViewView.Binder.TIMELINE_KEYPOINT);
        this.$gallery = this.$container.find(ViewView.Binder.GALLERY);
        this.$new = this.$container.find(ViewView.Binder.NEW);
        this.$edit = this.$container.find(ViewView.Binder.EDIT);
        this.$deleteModal = this.$container.find(ViewView.Binder.DELETE_MODAL);
        this.$delete = this.$container.find(ViewView.Binder.DELETE);

        this.$timelineKeyPoint.on('click', this.bind__onClickHoverKeyPoint);
        this.$timelineKeyPoint.on('dblclick', this.bind__onClickHoverKeyPoint);
        this.$timelineKeyPoint.hover(
            this.bind__onClickHoverKeyPoint,
            this.bind__onClickHoverKeyPoint);
        this.$new.on('click', this.bind__onClickNew);
        this.$edit.on('click', this.bind__onClickEdit);
        this.$delete.on('click', this.bind__onClickDelete);

        this.controller.model.subscribe(Model.Event.CHANGE, this.bind__onModelChange);

        var media = this.controller.model.get('ordered_media');
        if (media && media.length > 0) {
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

    ViewView.TAG = 'PostViewView';

    ViewView.Binder = {
        CONTAINER: '.post-container',
        TIMELINE_KEYPOINT: '.post-timeline-keypoint',
        GALLERY: '.post-gallery',
        NEW: '.post-new',
        EDIT: '.post-edit',
        DELETE_MODAL: '.post-delete-modal',
        DELETE: '.post-delete'
    };

    // clicking the clock icon will move the density bar to the comments time
    // and highlight the comment on the density bar
    // mousing over the clock icon should highlight the comment on the density bar
    ViewView.prototype._onClickHoverKeyPoint = function (e) {
        switch (e.type) {
            case 'mouseenter':
            case 'mouseleave':
                this.controller.hoverKeyPoint({
                    isMouseOver: e.type == 'mouseenter'
                });
                break;
            case 'click':
            case 'dblclick':
                e.preventDefault();
                this.controller.clickKeyPoint({
                    isDblClick: e.type == 'dblclick'
                });
                break;
        }
    };

    ViewView.prototype._onClickNew = function (e) {
        e.preventDefault();

        this.$new.hide();
        this.controller.new(null)
            .done(function (data) {
                this.controller.addToThread(data.post);
            }.bind(this))
            .fail(function (data) {
                this.$new.show();
            }.bind(this));
    };

    ViewView.prototype._onClickEdit = function (e) {
        e.preventDefault();

        this.controller.edit(null)
            .done(function (data) {
                this.controller.updateInThread(true);

                if (this.controller.model.get('is_temporal', false)) {
                    this.controller.editKeyPoint({cancel: false});
                }
            }.bind(this))
            .fail(function (data) {
                //TODO
            });
    };

    ViewView.prototype._onClickDelete = function (e) {
        e.preventDefault();

        this.$delete.button('loading');
        this.controller.delete()
            .done(function (data) {
                this.$deleteModal
                    .find('.modal-body')
                    .html('Post deleted successfully.');

                if (this.$deleteModal.data('bs.modal').isShown) {
                    this.$deleteModal.on('hidden.bs.modal', function (e) {
                        this.controller.removeFromThread();
                    }.bind(this));
                    this.$deleteModal.modal('hide');
                } else {
                    this.controller.removeFromThread();
                }
            }.bind(this))
            .fail(function (data) {
                this.$container
                    .find('.modal-body')
                    .prepend('Something went wrong. Try again.');
                this.$delete.button('reset');
            }.bind(this));
    };

    ViewView.prototype._renderKeyPoint = function (startTime, endTime, videoDuration) {
        console.log('%s: %s', ViewView.TAG, '_renderKeyPoint');

        var startTimePercentage = ((100 * startTime) / videoDuration).toFixed(2);
        var endTimePercentage = ((100 * endTime) / videoDuration).toFixed(2);
        var widthPercentage = (endTimePercentage - startTimePercentage).toFixed(2);

        this.$timelineKeyPoint.css({
            left: startTimePercentage + '%',
            width: widthPercentage + '%'
        });
    };

    ViewView.prototype._hoverKeyPoint = function (isHovering) {
        if (isHovering) {
            this.$container.addClass('tt-post-container-highlight');
        } else {
            this.$container.removeClass('tt-post-container-highlight');
        }
    };

    ViewView.prototype._clickKeyPoint = function (isPlaying) {
        var video = this.$container.find('video')[0];
        if (video && isPlaying) {
            video.currentTime = 0;
            video.play();
        }
    };

    ViewView.prototype._onModelChange = function (e) {
        this._renderKeyPoint(
            e.model.get('keyPoint.startTime', ''),
            e.model.get('keyPoint.endTime', ''),
            e.model.get('keyPoint.videoDuration', '')
        );
        this._hoverKeyPoint(
            e.model.get('keyPoint.isPlayerHovering', false)
        );
        this._clickKeyPoint(
            e.model.get('keyPoint.isPlayerPlaying', false)
        );
        this.$new.show();
    };

    return ViewView;
});
