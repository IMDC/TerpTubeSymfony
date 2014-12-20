define([
    'chai',
    'model/postModel'
], function (chai, PostModel) {
    'use strict';

    var expect = chai.expect;

    describe('PostModel', function () {

        var callbackResult;
        var callback;
        var data;
        var post;

        before(function () {
            callback = function (e) {
                callbackResult = e.model.get('id');
            };
            data = {
                id: '0' + Math.floor((Math.random() * 100000) + 1),
                threadId: 17
            };
            post = new PostModel(data);
        });

        beforeEach(function () {
            callbackResult = '';
        });

        it('should be a new post', function () {
            expect(post.isNew()).to.be.true();
        });

        after(function () {
            callbackResult = null;
            callback = null;
            data = null;
            post = null;
        });

    });

});
