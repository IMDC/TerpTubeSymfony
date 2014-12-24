define([
    'chai',
    'model/threadModel'
], function (chai, ThreadModel) {
    'use strict';

    var expect = chai.expect;

    describe('ThreadModel', function () {

        var callbackResult;
        var callback;
        var data;
        var thread;

        before(function () {
            callback = function (e) {
                callbackResult = e.model.get('id');
            };
            data = {
                id: '0' + Math.floor((Math.random() * 100000) + 1),
                threadId: 17
            };
            thread = new ThreadModel(data);
        });

        beforeEach(function () {
            callbackResult = '';
        });

        after(function () {
            callbackResult = null;
            callback = null;
            data = null;
            thread = null;
        });

    });

});
