define(['model/model', 'extra', 'underscore'], function (Model) {
    'use strict';

    var ThreadModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);

        this.data.keyPoints = [];
    };

    ThreadModel.extend(Model);

    ThreadModel.prototype.addKeyPoint = function (keyPoint) {
        this.data.keyPoints.push(keyPoint);
        this._dispatch(Model.Event.CHANGE);
    };

    ThreadModel.prototype.setKeyPoint = function (keyPointId, keyPath, value, doDispatch) {
        _.each(this.data.keyPoints, function (element, index, list) {
            if (element.id == keyPointId) {
                this.set('keyPoints.' + index + '.' + keyPath, value, doDispatch);
                return;
            }
        }, this);
    };

    ThreadModel.prototype.removeKeyPoint = function (keyPointId, fully) {
        fully = typeof fully !== 'undefined' ? fully : true;

        _.each(this.data.keyPoints, function (keyPoint, index, list) {
            if (keyPoint.id == keyPointId) {
                if (fully) {
                    list.splice(index, 1);
                    this._dispatch(Model.Event.CHANGE);
                } else {
                    this.set('keyPoints.' + index + '.options.drawOnTimeLine', false);
                }
                return;
            }
        }, this);
    };

    return ThreadModel;
});
