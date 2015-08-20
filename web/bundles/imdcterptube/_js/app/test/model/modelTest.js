define([
    'chai',
    'model/model'
], function (chai, Model) {
    'use strict';

    var expect = chai.expect;

    describe('Model', function () {

        var callbackResult;
        var callback;
        var data;
        var model;

        before(function () {
            callback = function (e) {
                callbackResult = e.model.get(e.keyPath);
            };
            data = {id: 1, name: 'hello', nested: {foo: 'bar', bar: {marco: 10}}};
        });

        beforeEach(function () {
            callbackResult = '';
        });

        it('should have returned key path array', function () {
            var keyPath = 'test';
            var path = Model._stringToKeyPath(keyPath);

            expect(path.length).to.equal(keyPath.split('.').length);
            expect(path[0]).to.equal(keyPath);
        });

        it('should have returned nested key path array', function () {
            var keyPath = 'nested.bar.marco';
            var path = Model._stringToKeyPath(keyPath);

            expect(path.length).to.equal(keyPath.split('.').length);
            expect(path[2]).to.equal('marco');
        });

        it('should be undefined', function () {
            try {
                model = new Model([]);
            } catch (err) {}
            expect(model).to.be.undefined;
        });

        it('should have data equal given data at instantiation', function () {
            model = new Model(data);
            model.subscribe(Model.Event.CHANGE, callback);
            expect(model.data).to.equal(data);
        });

        it('should equal retrieved value', function () {
            var marco = model.get('name');
            expect(marco).to.equal(data.name);
        });

        it('should equal nested retrieved value', function () {
            var marco = model.get('nested.bar');
            expect(marco).to.equal(data.nested.bar);
        });

        it('should equal default value', function () {
            var defaultValue = 30;
            var marco = model.get('nested.nonExist', defaultValue);
            expect(marco).to.equal(defaultValue);
        });

        it('should not equal default value, but undefined', function () {
            var marco = model.get('nested.nonExist');
            expect(marco).to.be.undefined;
        });

        it('should have set undefined key path to new value', function () {
            var newValue = null;
            model.set('test', newValue);
            var marco = model.get('test');

            expect(marco).to.equal(newValue);
        });

        it('should have set key path to new value', function () {
            var newVal = {polo: 20};
            model.set('nested.bar', newVal);

            expect(model.get('nested.bar')).to.equal(newVal);
        });

        it('should have dispatched event', function () {
            var newVal = 'world';
            model.set('name', newVal);

            expect(callbackResult).to.equal(newVal);
        });

        it('should not have dispatched event when dispatch is explicitly passed as false', function () {
            var newVal = 'hello world';
            model.set('name', newVal, false);

            expect(callbackResult).to.not.equal(newVal);
        });

        it('should not have dispatched event', function () {
            model.set('name', data.name);

            expect(callbackResult).to.not.equal(data.name);
        });

        it('should update model', function () {
            var newData = {name: 'hello:updated'};
            model.update(newData);

            expect(callbackResult).to.equal(newData.name);
        });

        it('should update model (nested)', function () {
            var newData = {nested: {bar: {macro: 20}}};
            model.update(newData);

            expect(callbackResult).to.equal(newData.nested.bar.macro);
        });

        it('should have dispatched change event', function () {
            model.forceChange();
            expect(callbackResult).to.be.undefined;

            model.forceChange('nested.nonExist');
            expect(callbackResult).to.be.undefined;

            model.forceChange('name');
            expect(callbackResult).to.equal(data.name);
        });

        after(function () {
            callbackResult = null;
            callback = null;
            data = null;
            model = null;
        });

    });

});
