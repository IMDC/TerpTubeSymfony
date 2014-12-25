define([
    'model/model',
    'views/post/editView'
], function (Model, EditView) {
    'use strict';

    var ViewView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickHoverTimelineKeyPoint = this._onClickHoverTimelineKeyPoint.bind(this);
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

        this.$timelineKeyPoint.on('click', this.bind__onClickHoverTimelineKeyPoint);
        this.$timelineKeyPoint.on('dblclick', this.bind__onClickHoverTimelineKeyPoint);
        this.$timelineKeyPoint.hover(
            this.bind__onClickHoverTimelineKeyPoint,
            this.bind__onClickHoverTimelineKeyPoint);
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
    ViewView.prototype._onClickHoverTimelineKeyPoint = function (e) {
        if (e && e.type == 'click')
            e.preventDefault();

        this.controller.interactKeyPoint(e.type);
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
                this.controller.editKeyPoint();
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

    ViewView.prototype._renderTimelineKeyPoint = function (startTime, endTime, videoDuration) {
        console.log('%s: %s', ViewView.TAG, '_renderTimelineKeyPoint');

        var startTimePercentage = ((100 * startTime) / videoDuration).toFixed(2);
        var endTimePercentage = ((100 * endTime) / videoDuration).toFixed(2);
        var widthPercentage = (endTimePercentage - startTimePercentage).toFixed(2);

        this.$timelineKeyPoint.css({
            left: startTimePercentage + '%',
            width: widthPercentage + '%'
        });
    };

    ViewView.prototype._onHoverKeyPoint = function (isHovering) {
        if (isHovering) {
            this.$container.addClass('tt-post-container-highlight');
        } else {
            this.$container.removeClass('tt-post-container-highlight');
        }
    };

    ViewView.prototype._onClickKeyPoint = function (isPlaying) {
        var video = this.$container.find('video')[0];
        if (video && isPlaying) {
            video.currentTime = 0;
            video.play();
        }
    };

    ViewView.prototype._onModelChange = function (e) {
        this._renderTimelineKeyPoint(
            e.model.get('keyPoint.startTime', ''),
            e.model.get('keyPoint.endTime', ''),
            e.model.get('keyPoint.videoDuration', '')
        );
        this._onHoverKeyPoint(
            e.model.get('keyPoint.isHovering', false)
        );
        this._onClickKeyPoint(
            e.model.get('keyPoint.isPlaying', false)
        );
    };

    return ViewView;
});
