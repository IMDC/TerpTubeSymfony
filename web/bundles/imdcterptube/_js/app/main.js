define(function(require) {
    var TerpTube = {};

    TerpTube.Controller = {};
    TerpTube.Controller.Forum = require('controller/forum');
    TerpTube.Controller.Thread = require('controller/thread');
    TerpTube.Controller.Post = require('controller/post');
    TerpTube.Controller.Group = require('controller/group');

    TerpTube.Core = {};
    TerpTube.Core.MediaChooser = require('core/mediaChooser');

    window.TerpTube = TerpTube;

    $tt = window.TerpTube;

    // make all elements with class 'autosize' expand to fit its contents
    $(".autosize").autosize();
});
