define(function(require) {
    "use strict";

    var TerpTube = {};

    TerpTube.Controller = {};
    TerpTube.Controller.MyFiles = require('controller/myFiles');
    TerpTube.Controller.Forum = require('controller/forum');
    TerpTube.Controller.Thread = require('controller/thread');
    TerpTube.Controller.Post = require('controller/post');
    TerpTube.Controller.Group = require('controller/group');
    TerpTube.Controller.Message = require('controller/message');
    TerpTube.Controller.Profile = require('controller/profile');

    TerpTube.Core = {};
    TerpTube.Core.MediaChooser = require('core/mediaChooser');
    TerpTube.Core.MediaManager = require('core/mediaManager');
    TerpTube.Core.Gallery = require('core/gallery');
    TerpTube.Core.Recorder = require('core/recorder');
    TerpTube.Core.Helper = require('core/helper');

    TerpTube._instances = [];

    window.TerpTube = TerpTube;

    window.$tt = window.TerpTube;

    // make all elements with class 'autosize' expand to fit its contents
    $(".autosize").autosize();
});
