define(function () {
    'use strict';

    var ViewView = function (controller, options) {
        this.controller = controller;

        this.bind__onClickVideoSpeed = this._onClickVideoSpeed.bind(this);
        this.bind__onClickClosedCaptions = this._onClickClosedCaptions.bind(this);

        this.$container = options.container;

        $('#videoSpeed').on('click', this.bind__onClickVideoSpeed);
        $('#closedCaptions').on('click', this.bind__onClickClosedCaptions);

        $tt._instances.push(this);
    };

    ViewView.TAG = 'ThreadViewView';

    ViewView.Binder = {};

    // change the video speed when the slowdown button is clicked
    ViewView.prototype._onClickVideoSpeed = function (e) {
        e.preventDefault();

        var speedImage = this.controller.adjustVideoSpeed();
        $('#videoSpeed img').attr('src', speedImage);
    };

    // change the captioning display when you click the captioning button
    ViewView.prototype._onClickClosedCaptions = function (e) {
        e.preventDefault();

        var captionImage = this.controller.toggleClosedCaptions();
        $('#closed-caption-button img').attr('src', captionImage);
    };

    return ViewView;
});
