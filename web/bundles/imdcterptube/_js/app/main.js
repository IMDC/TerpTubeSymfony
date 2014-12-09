define(function(require) {
    "use strict";

    var TerpTube = {};

    TerpTube.Controller = {};
    TerpTube.Controller.MyFiles = require('controller/myFiles');
    TerpTube.Controller.Forum = require('controller/forum');
    TerpTube.Controller.Thread = require('controller/thread_');
    TerpTube.Controller.Post = require('controller/post_');
    TerpTube.Controller.Group = require('controller/group');
    TerpTube.Controller.Message = require('controller/message');
    TerpTube.Controller.Profile = require('controller/profile');

    TerpTube.Core = {};
    TerpTube.Core.Helper = require('core/helper');
    TerpTube.Core.MediaManager = require('core/mediaManager');
    TerpTube.Core.MyFilesSelector = require('core/myFilesSelector');
    TerpTube.Core.Recorder = require('core/recorder');
    TerpTube.Core.MediaChooser = require('core/mediaChooser');
    TerpTube.Core.Gallery = require('core/gallery');

    TerpTube.Services = {};
    TerpTube.Services.KeyPoint = require('core/keyPointService');

    TerpTube._services = [];
    TerpTube._instances = [];

    window.TerpTube = TerpTube;
    window.$tt = window.TerpTube;

    ///////////////////////////////

    $tt.Core.Helper.autoSize();

    dust.helpers.generateUrl = function(chunk, context, bodies, params) {
        return chunk.write($tt.Core.Helper.generateUrl(params.path));
    };
});
