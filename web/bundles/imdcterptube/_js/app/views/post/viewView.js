define([
    'model/model',
    'views/post/editView'
], function (Model, EditView) {
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

        $tt._instances.push(this);
    };

    ViewView.TAG = 'PostViewView';

    ViewView.Binder = {
        CONTAINER: '.post-container',
        TIMELINE_KEYPOINT: '.post-timeline-keypoint',
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
                this.$container.after(data.html);
            }.bind(this))
            .fail(function () {
                this.$new.show();
            }.bind(this));
    };

    ViewView.prototype._onClickEdit = function (e) {
        e.preventDefault();

        this.controller.edit(null)
            .done(function (data) {
                this.$container.replaceWith(data.html);
                this.controller.removeKeyPoint();
                var _self = this;
                _self = new EditView(this.controller, this.controller.options);
                this.controller.onViewLoaded();
                this.controller.editKeyPoint({cancel: false});
            }.bind(this));
    };

    ViewView.prototype._onClickDelete = function (e) {
        e.preventDefault();

        this.view.$delete.button('loading');
        this.controller.delete()
            .done(function (data) {
                this.$deleteModal
                    .find('.modal-body')
                    .html('Post deleted successfully.');

                if (this.$deleteModal.data('bs.modal').isShown) {
                    this.$deleteModal.on('hidden.bs.modal', function (e) {
                        this.$container.remove();
                    });
                    this.$deleteModal.modal('hide');
                    this.$container.fadeOut('slow');
                } else {
                    this.$container.fadeOut('slow', function (e) {
                        this.$container.remove();
                    });
                }
            }.bind(this))
            .fail(function () {
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
    };

    return ViewView;
});
