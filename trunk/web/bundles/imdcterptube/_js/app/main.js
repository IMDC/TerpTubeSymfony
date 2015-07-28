/*!
 * version=#version
 */

define([
    // init
    'bootstrap',
    'service',

    // core
    'core/helper',
    'core/subscriber',

    // factory
    'factory/contactFactory',
    'factory/forumFactory',
    'factory/groupFactory',
    'factory/mediaFactory',
    'factory/messageFactory',
    'factory/myFilesFactory',
    'factory/postFactory',
    'factory/threadFactory',

    // service
    'service/keyPointService',
    'service/rabbitmqWebStompService',
    'service/subscriberService',
    'service/threadPostService',

    // model
    'model/forumModel',
    'model/groupModel',
    'model/mediaModel',
    'model/messageModel',
    'model/myFilesModel',
    'model/postModel',
    'model/profileModel',
    'model/threadModel',
    'model/userModel',

    // controller
    'controller/contactController',
    'controller/forumController',
    'controller/groupController',
    'controller/messageController',
    'controller/myFilesController',
    'controller/postController',
    'controller/profileController',
    'controller/threadController',

    // component
    'component/accessTypeComponent',
    'component/galleryComponent',
    'component/mediaChooserComponent',
    'component/myFilesSelectorComponent',
    'component/recorderComponent',
    'component/tableComponent',

    // view
    'views/contact/listView',

    'views/forum/newView',
    'views/forum/editView',
    'views/forum/viewView',

    'views/group/newView',
    'views/group/editView',
    'views/group/viewView',
    'views/group/manageView',

    'views/message/newView',
    'views/message/replyView',
    'views/message/viewView',

    'views/myFiles/listView',

    'views/post/newView',
    'views/post/editView',
    'views/post/viewView',

    'views/profile/editView',

    'views/thread/newView',
    'views/thread/editView',
    'views/thread/viewView'
], function () {
    'use strict';

    var TerpTube = {};

    TerpTube._services = [];
    TerpTube._instances = [];

    window.TerpTube = TerpTube;
    window.$tt = window.TerpTube;

    ///////////////////////////////

    var Helper = require('core/helper');
    Helper.autoSize();

    //TODO: move to core
    dust.helpers.generateUrl = function (chunk, context, bodies, params) {
        if (!params || !params.hasOwnProperty('path'))
            return chunk;

        return chunk.write(Helper.generateUrl(params.path));
    };

    dust.helpers.generateRouteUrl = function (chunk, context, bodies, params) {
        if (!params || !params.hasOwnProperty('name') || !params.hasOwnProperty('str_params'))
            return chunk;

        var str_params = params.str_params.split('|');
        var opt_params = {};

        str_params.forEach(function (element, index, array) {
            var keyContext = element.split(':');
            opt_params[keyContext[0]] = context.get(keyContext[1]);
        });

        return chunk.write(Routing.generate(params.name, opt_params, params.absolute));
    };

    // http://stackoverflow.com/a/21230338
    dust.helpers.sizeOf = function (chunk, context, bodies, params) {
        // pass a dummy chunk object so that @size doesn't write the resulting value,
        // so it doesn't render if bodies.block is present but its context(s) return(s) false
        var result;
        this.size({
            write: function (value) {
                result = value;
            }
        }, context, bodies, params);
        if (bodies && bodies.block) {
            chunk.render(bodies.block, context.push({key: result}));
        } else {
            chunk = chunk.write(result);
        }
        return chunk;
    };

    dust.helpers.isLoggedInUser = function (chunk, context, bodies, params) {
        if (!params || !params.hasOwnProperty('id') || !window.user)
            return chunk;

        var result = params.id == window.user.get('id');
        if (params.inverse)
            result = !result;
        if (bodies) {
            if (result && bodies.block)
                chunk.render(bodies.block, context);
            if (!result && bodies.else)
                chunk.render(bodies.else, context);
        } else {
            chunk = chunk.write(result ? 'Yes' : 'No');
        }
        return chunk;
    };

    dust.helpers.isNotLoggedInUser = function (chunk, context, bodies, params) {
        params.inverse = true;
        return this.isLoggedInUser(chunk, context, bodies, params);
    };

    dust.helpers.isInUserOnList = function (chunk, context, bodies, params) {
        if (!params || !params.hasOwnProperty('list') || !params.hasOwnProperty('username') || !window.user)
            return chunk;

        var result = window.user['isUserOn' + params.list + 'List'](params.username);
        if (bodies) {
            if (result && bodies.block)
                chunk.render(bodies.block, context);
            if (!result && bodies.else)
                chunk.render(bodies.else, context);
        } else {
            chunk = chunk.write(result ? 'Yes' : 'No');
        }
        return chunk;
    };

    dust.filters.nl2br = function (value) {
        return value.replace(/(?:\r\n|\r|\n)/g, '<br />');
    };
});
