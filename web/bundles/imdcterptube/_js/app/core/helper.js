define(function() {
    var Helper = function() {

    };

    Helper.TAG = "Helper";

    Helper.isFullscreen = function() {
        return (document.fullscreenElement ||
            document.mozFullScreenElement ||
            document.webkitFullscreenElement ||
            document.msFullscreenElement) ? true : false;
    };

    Helper.toggleFullScreen = function(element) {
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

    Helper.updateProgressBar = function(element, percentComplete) {
        var progressBar = element.find(".progress-bar");
        progressBar.attr("aria-valuenow", percentComplete);
        progressBar.css("width", percentComplete + "%");
        progressBar.html(percentComplete + "%");
    };

    return Helper;
});
