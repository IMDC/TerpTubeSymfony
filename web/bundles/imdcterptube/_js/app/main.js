define([
    'controller/myFiles',
    'controller/forum',
    'controller/message',
    'controller/profile',

    'core/helper',
    'core/mediaManager',
    'core/myFilesSelector',
    'core/recorder',
    'core/mediaChooser',
    'core/gallery',



    'bootstrap',
    'service',

    // core
    'core/subscriber',

    // factory
    'factory/contactFactory',
    'factory/groupFactory',
    'factory/postFactory',
    'factory/threadFactory',

    // service
    'service/keyPointService',

    // model
    'model/groupModel',
    'model/postModel',
    'model/threadModel',

    // controller
    'controller/contactController',
    'controller/groupController',
    'controller/postController',
    'controller/threadController',

    // component
    'component/tableComponent',

    // view
    'views/contact/listView',

    'views/group/newView',
    'views/group/editView',
    'views/group/manageView',

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
    TerpTube.Controller.MyFiles = require('controller/myFiles');
    TerpTube.Controller.Forum = require('controller/forum');
    //TerpTube.Controller.Thread = require('controller/thread');
    //TerpTube.Controller.Post = require('controller/post');
    TerpTube.Controller.Message = require('controller/message');
    TerpTube.Controller.Profile = require('controller/profile');

    TerpTube.Core = {};
    TerpTube.Core.Helper = require('core/helper');
    TerpTube.Core.MediaManager = require('core/mediaManager');
    TerpTube.Core.MyFilesSelector = require('core/myFilesSelector');
    TerpTube.Core.Recorder = require('core/recorder');
    TerpTube.Core.MediaChooser = require('core/mediaChooser');
    TerpTube.Core.Gallery = require('core/gallery');

    TerpTube._services = [];
    TerpTube._instances = [];

    window.TerpTube = TerpTube;
    window.$tt = window.TerpTube;

    ///////////////////////////////

    $tt.Core.Helper.autoSize();

    dust.helpers.generateUrl = function (chunk, context, bodies, params) {
        return chunk.write($tt.Core.Helper.generateUrl(params.path));
    };
});
