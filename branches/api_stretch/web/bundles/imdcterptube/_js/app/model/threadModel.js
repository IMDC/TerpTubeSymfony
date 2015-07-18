define([
    'model/model',
    'model/postModel',
    'extra',
    'underscore'
], function (Model, PostModel) {
    'use strict';

    var ThreadModel = function (data) {
        Model.prototype.constructor.apply(this, arguments);

        this.data.keyPoints = [];

        // replace key/value objects with models for all media
        // TODO consolidate under collection type?
        if (this.data.posts) {
            this.data.posts.forEach(function (element, index, array) {
                array[index] = new PostModel(element);
            });
        }
    };

    ThreadModel.extend(Model);

    ThreadModel.prototype.addKeyPoint = function (keyPoint) {
        this.removeKeyPoint(keyPoint.id);

        this.data.keyPoints.push(keyPoint);
        this._dispatch(Model.Event.CHANGE, 'keyPoints.' + (this.data.keyPoints.length - 1));
    };

    ThreadModel.prototype._findKeyPoint = function (keyPointId) {
        for (var index in this.data.keyPoints) {
            if (this.data.keyPoints[index].id == keyPointId) {
                return index;
            }
        }
    };

    ThreadModel.prototype.setKeyPointProperty = function (keyPointId, keyPath, value, doDispatch) {
        var index = this._findKeyPoint(keyPointId);
        if (index > -1)
            this.set('keyPoints.' + index + '.' + keyPath, value, doDispatch);
    };

    ThreadModel.prototype.removeKeyPoint = function (keyPointId) {
        var index = this._findKeyPoint(keyPointId);
        if (index > -1) {
            this.data.keyPoints.splice(index, 1);
            this._dispatch(Model.Event.CHANGE, 'keyPoints');
        }
    };

    ThreadModel.prototype.forceChangeKeyPoint = function (keyPointId, keyPath) {
        var index = this._findKeyPoint(keyPointId);
        if (index > -1)
            this.forceChange('keyPoints.' + index + '.' + keyPath);
    };

    ThreadModel.prototype.addPost = function (post, view) {
        //TODO add model.add method?
        var posts = this.get('posts', []);
        this.set('posts.' + posts.length, post, false);
        this.forceChange('posts.' + (posts.length - 1), {isNew: true, view: view});
    };

    ThreadModel.prototype.removePost = function (post, nested) {
        var posts = this.get('posts');
        var index = this.find(post.get('id'), 'id', posts);
        if (index > -1) {
            posts.splice(index, 1);
            var childPostsToRemove = [];
            _.each(posts, function (element, index, list) {
                if (element.get('parent_post_id') == post.get('id'))
                    childPostsToRemove.push(element);
            });
            this.set('posts', _.difference(posts, childPostsToRemove));
        }
    };

    ThreadModel.prototype.forceChangePost = function (post, view) {
        var index = this.find(post.get('id'), 'id', this.get('posts'));
        if (index > -1)
            this.forceChange('posts.' + index, {view: view});
    };

    return ThreadModel;
});
