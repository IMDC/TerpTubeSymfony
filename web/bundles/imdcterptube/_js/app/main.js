/*!
 * version=#version
 */

define([
    // init
    'bootstrap',
    'service',

    // core
    'core/dust',
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
    'controller/homeController',
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
    'views/forum/listView',
    'views/forum/viewView',

    'views/group/newView',
    'views/group/editView',
    'views/group/listView',
    'views/group/viewView',
    'views/group/manageView',

    'views/home/indexView',

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

    var Dust = require('core/dust');
    var Helper = require('core/helper');

    Dust.inject();
    Helper.autoSize();
});
