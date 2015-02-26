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

        it('should not have found key point', function () {
            var index = model._findKeyPoint(keyPoint.id);
            expect(index).to.be.undefined();
        });

        it('should have added new key point', function () {
            model.addKeyPoint(keyPoint);
            expect(callbackResult).to.equal(keyPoint);
        });

        it('should have found key point', function () {
            var index = model._findKeyPoint(keyPoint.id);
            expect(index).to.equal('0');
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

        it('should not have dispatched change event', function () {
            model.forceChangeKeyPoint();
            expect(callbackResult).to.equal('');
        });

        it('should have dispatched change event', function () {
            model.forceChangeKeyPoint(keyPoint.id);
            expect(callbackResult).to.be.undefined();

            model.forceChangeKeyPoint(keyPoint.id, 'nested.nonExist');
            expect(callbackResult).to.be.undefined();

            model.forceChangeKeyPoint(keyPoint.id, 'startTime');
            expect(callbackResult).to.equal(keyPoint.startTime);
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
