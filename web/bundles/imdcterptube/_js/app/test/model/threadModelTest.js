define([
    'chai',
    'model/threadModel',
    'model/model'
], function (chai, ThreadModel, Model) {
    'use strict';

    var expect = chai.expect;

    describe('ThreadModel', function () {

        var callbackResult;
        var callback;
        var data;
        var model;
        var keyPoint;

        before(function () {
            callback = function (e) {
                callbackResult = e.model.get(e.keyPath);
            };
            data = {
                id: 17
            };
            model = new ThreadModel(data);
            model.subscribe(Model.Event.CHANGE, callback);
            keyPoint = new KeyPoint(1, 1.00, 4.00, '', {drawOnTimeLine: true});
        });

        beforeEach(function () {
            callbackResult = '';
        });

        it('should have added new key point', function () {
            model.addKeyPoint(keyPoint);
            expect(callbackResult).to.equal(keyPoint);
        });

        it('should have set key point property', function () {
            var newValue = 2.00;
            model.setKeyPointProperty(keyPoint.id, 'startTime', newValue);
            expect(callbackResult).to.equal(newValue);
        });

        it('should not have added key point twice', function () {
            model.addKeyPoint(keyPoint);
            expect(model.data.keyPoints).have.length(1);
        });

        it('should have removed key point', function () {
            model.removeKeyPoint(keyPoint.id);
            expect(model.data.keyPoints).have.length(0);
            expect(callbackResult).to.equal(model.data.keyPoints);
        });

        after(function () {
            callbackResult = null;
            callback = null;
            data = null;
            model = null;
            keyPoint = null;
        });

    });

});
