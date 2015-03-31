/*!
 * version=#version
 */

define([
    'controller/message',
    'controller/profile',



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
    'factory/myFilesFactory',
    'factory/postFactory',
    'factory/threadFactory',

    // service
    'service/keyPointService',
    'service/subscriberService',

    // model
    'model/forumModel',
    'model/groupModel',
    'model/mediaModel',
    'model/myFilesModel',
    'model/postModel',
    'model/threadModel',

    // controller
    'controller/contactController',
    'controller/forumController',
    'controller/groupController',
    'controller/myFilesController',
    'controller/postController',
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
    'views/group/manageView',

    'views/myFiles/listView',

    'views/post/newView',
    'views/post/editView',
    'views/post/viewView',

    'views/thread/newView',
    'views/thread/editView',
    'views/thread/viewView'
], function () {
    'use strict';

    var TerpTube = {};

    TerpTube.Controller = {};
    TerpTube.Controller.Message = require('controller/message');
    TerpTube.Controller.Profile = require('controller/profile');

    TerpTube._services = [];
    TerpTube._instances = [];

    window.TerpTube = TerpTube;
    window.$tt = window.TerpTube;

    ///////////////////////////////

    var Helper = require('core/helper');
    Helper.autoSize();

    dust.helpers.generateUrl = function (chunk, context, bodies, params) {
        return chunk.write(Helper.generateUrl(params.path));
    };
});
