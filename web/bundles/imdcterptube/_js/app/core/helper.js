define(function () {
    "use strict";

    var Helper = function () {

    };

    Helper.TAG = "Helper";

    Helper.MEDIA_TYPE_IMAGE = 0;
    Helper.MEDIA_TYPE_VIDEO = 1;
    Helper.MEDIA_TYPE_AUDIO = 2;
    Helper.MEDIA_TYPE_OTHER = 9;

    Helper.isFullscreen = function () {
        return (document.fullscreenElement ||
        document.mozFullScreenElement ||
        document.webkitFullscreenElement ||
        document.msFullscreenElement) ? true : false;
    };

    Helper.toggleFullScreen = function (element) {
        console.log("%s: %s", Helper.TAG, "toggleFullScreen");

        var htmlElelemnt = element[0];
        if (!Helper.isFullscreen()) {
            if (htmlElelemnt.requestFullscreen) {
                htmlElelemnt.requestFullscreen();
            } else if (htmlElelemnt.mozRequestFullScreen) {
                htmlElelemnt.mozRequestFullScreen();
            } else if (htmlElelemnt.webkitRequestFullscreen) {
                htmlElelemnt.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
            } else if (htmlElelemnt.msRequestFullscreen) {
                htmlElelemnt.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    };

    Helper.updateProgressBar = function (element, percentComplete) {
        var progressBar = element.find(".progress-bar");
        progressBar.attr("aria-valuenow", percentComplete);
        progressBar.css("width", percentComplete + "%");
        progressBar.html(percentComplete + "%");
    };

    Helper.generateUrl = function (path, root) {
        // ignore full and blob urls
        if (path.indexOf('://') > 0 || path.indexOf('blob:') == 0)
            return path;

        root = typeof root !== "undefined" ? root : false;
        var baseUrl = Routing.getBaseUrl() + "/";
        return root
            ? baseUrl + path
            : baseUrl.replace(/\w+\.php\/$/gi, "") + path;
    };

    Helper.formatSize = function (size) {
        if (size == -1)
            return size;
        return (Number(size) / 1024 / 1024).toFixed(2) + " MB";
    }
    Helper.getTimeDateString = function (dbTime) {
        var dateTime = new Date(dbTime);
        var amPM = "am";
        if (dateTime.getHours() > 11)
            amPM = "pm"
        var hours = dateTime.getHours() % 12;
        var minutes = dateTime.getMinutes();
        if (hours == 0)
            hours = 12;
        if (hours < 10)
            hours = "0" + hours;
        if (minutes < 10)
            minutes = "0" + minutes;
        var dateString = dateTime.toDateString();
        dateString = dateString.substring(dateString.indexOf(" ") + 1);
        return dateString + " " + hours + ":" + minutes + " " + amPM;

    }
    Helper.getIconForMediaType = function (type) {
        var icon;
        var text;
        switch (type) {
            case Helper.MEDIA_TYPE_IMAGE:
                icon = 'fa-file-photo-o';
                text = 'Image';
                break;
            case Helper.MEDIA_TYPE_VIDEO:
                icon = 'fa-file-video-o';
                text = 'Video';
                break;
            case Helper.MEDIA_TYPE_AUDIO:
                icon = 'fa-file-audio-o';
                text = 'Audio';
                break;
            case Helper.MEDIA_TYPE_OTHER:
                icon = 'fa-file-o';
                text = 'Other';
                break;
        }
        return {icon: icon, text: text};
    }

    Helper.autoSize = function () {
        // make all elements with class 'autosize' expand to fit its contents
        $(".autosize").autosize({append: ''});
    };

    return Helper;
});
