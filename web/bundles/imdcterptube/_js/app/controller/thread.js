define(['core/mediaChooser'], function(MediaChooser) {
    var Thread = function() {
        this.keyPoints = new Array();
        this.player = null;
        this.recorder = null;
        this.forwardButton = "<button class='forwardButton'></button>";
        this.doneButton = "<button class='doneButton'></button>";
        this.doneAndPostButton = "<button class='doneAndPostButton'></button>";
        this.media = null;

        this.bind__onShowPostReplyFormClick = this._onShowPostReplyFormClick.bind(this);

        //TODO move to media chooser, as this may be a more general function
        this.bind_onRecordingSuccess = this.onRecordingSuccess.bind(this);
        this.bind_onRecordingError = this.onRecordingError.bind(this);
        this.bind_forwardFunction = this.forwardFunction.bind(this);
        this.bind_doneFunction = this.doneFunction.bind(this);
        this.bind_doneAndPostFunction = this.doneAndPostFunction.bind(this);
    }

    Thread.TAG = "Thread";

    Thread.Page = {
        NEW: 0,
        EDIT: 1,
        VIEW_THREAD: 2
    };

    /**
     * MediaChooser options for each related page that uses MediaChooser
     * @param {number} page
     * @param {number} postId
     */
    Thread.mediaChooserOptions = function(page, postId) {
        switch (page) {
            case Thread.Page.NEW:
            case Thread.Page.EDIT:
                return {
                    element: $("#files"),
                    isPopUp: true,
                    callbacks: {
                        success: function(media) {
                            $("#ThreadForm_mediatextarea").val(media.id);
				    console.log("successNEW/EDIT");
                            //$("#ThreadForm_mediaID").attr('data-mid', mid); //TODO not used?
                        },
                        successAndPost: function(media) {
                            $("#ThreadForm_mediatextarea").val(media.id);
					console.log("successAndPostNEW/EDIT");
					$("#postReplySubmit").trigger("click");
                            //TODO actually post here
                        },
                        reset: function() {
                            $("#ThreadForm_mediatextarea").val("");
                        }
                    }
                };
            case Thread.Page.VIEW_THREAD:
                return {
                    element: $("#files"),
                    isPopUp: true,
                    callbacks: {
                        success: function(media) {
                            $("#PostFormFromThread_mediatextarea").val(media.id);
                            //$("#post-comment-button").hide(); // shouldn't be visible, but ensure this is hidden
					console.log("successVIEW");
                        },
                        successAndPost: function(media) {
                            $("#PostFormFromThread_mediatextarea").val(media.id);
					console.log("successAndPostVIEW");
					$("#postReplySubmit").trigger("click");
                            //TODO actually post here
                        },
                        reset: function() {
                            $("#PostFormFromThread_mediatextarea").val("");
                        }
			},
			isNewPost: true
                };
        }
    };

    /**
     * ui element event bindings in order of appearance
     * @param {number} page
     */
    Thread.prototype.bindUIEvents = function(page) {
        console.log("%s: %s- page=%d", Thread.TAG, "bindUIEvents", page);

        switch (page) {
            case Thread.Page.NEW:
            case Thread.Page.EDIT:
                Thread._bindUIEventsNewEdit(page == Thread.Page.EDIT);
                break;
            case Thread.Page.VIEW_THREAD:
                this._bindUIEventsViewThread();
                break;
        }
    };

    Thread._bindUIEventsNewEdit = function(isEdit) {
        console.log("%s: %s", Thread.TAG, "_bindUIEventsNewEdit");

        MediaChooser.bindUIEvents(Thread.mediaChooserOptions(Thread.Page.NEW));

        /*
         * by paul: since I hid the real 'submit' button to provide a nicely stylized button
         * I have to click the real button when you click on the fancy one
         */
        /*$("#thread-form-submit-button").on("click", function(e) {
            e.preventDefault();

            $("#ThreadForm_submit").click();
        });*/

        $("ul.tagit").hide();

        var prefix = isEdit ? "ThreadEditForm" : "ThreadForm";

        $("#permissionRadioButtons div.radio").on("click", function(e) {
            if ($("#" + prefix + "_permissions_accessLevel_2").is(':checked')) { // specific users
                $("ul.tagit").show();
                $("#" + prefix + "_permissions_userGroupsWithAccess").hide();
            } else if ($("#" + prefix + "_permissions_accessLevel_3").is(':checked')) { // specific groups
                $("#" + prefix + "_permissions_userGroupsWithAccess").show();
                $("ul.tagit").hide();
            } else { // hide all
                $("#" + prefix + "_permissions_userGroupsWithAccess").hide();
                $("ul.tagit").hide();
            }
        });

        $("#permissionRadioButtons").trigger("click");
    };

    Thread.prototype._bindUIEventsViewThread = function() {
        console.log("%s: %s", Thread.TAG, "_bindUIEventsViewThread");

        MediaChooser.bindUIEvents(Thread.mediaChooserOptions(Thread.Page.VIEW_THREAD));

        // change the video speed when the slowdown button is clicked
        $("#videoSpeed").on("click", (function(e) {
            e.preventDefault();

            var speedSlow = 1 % 3;
            switch (speedSlow) {
                case 0:
                    this.playerOptions.mediaElement[0].playbackRate = 1.0;
                    $("#videoSpeed img").attr("src", this.playerOptions.speedImages.normal);
                    break;
                case 1:
                    this.playerOptions.mediaElement[0].playbackRate = 2.0;
                    $("#videoSpeed img").attr("src", this.playerOptions.speedImages.fast);
                    break;
                case 2:
                    this.playerOptions.mediaElement[0].playbackRate = 0.5;
                    $("#videoSpeed img").attr("src", this.playerOptions.speedImages.slow);
                    break;
                default:
                    this.playerOptions.mediaElement[0].playbackRate = 1.0;
                    $("#videoSpeed img").attr("src", this.playerOptions.speedImages.normal);
                    break;
            }
        }).bind(this));

        // change the captioning display when you click the captioning button
        $("#closedCaptions").on("click", (function(e) {
            e.preventDefault();

            //$("#closed-caption-button img").attr("src", this.playerOptions.captionImages.off);
            //$("#closed-caption-button img").attr("src", this.playerOptions.captionImages.on);
        }).bind(this));

        $("#showPostReplyForm").on("click", this.bind__onShowPostReplyFormClick);

        $("#postReplyCancel").on("click", (function(e) {
            e.preventDefault();

            // clear form values for start and end time
            $("#PostFormFromThread_startTime").val("");
            $("#PostFormFromThread_endTime").val("");

            $("#postReplyForm").hide();
            $("#showPostReplyForm").show();

            if (this.player) {
                enableTemporalComment(this.player, false, $("#PostFormFromThread_startTime"), $("#PostFormFromThread_endTime"));
            }
        }).bind(this));

        // clicking the clock icon will move the density bar to the comments time
        // and highlight the comment on the density bar
        $(".post-goto-timeline-keypoint").on("click", (function(e) {
            e.preventDefault();

            var keyPoint = this.getKeyPointForPostId($(e.currentTarget).data("pid"));
            keyPoint.paintHighlightedTimeout = true;
            keyPoint.paintHighlighted = true;
            this.player.seek(keyPoint.startTime);
            this.player.redrawKeyPoints = true;
            this.player.repaint();

            // clear the highlighted comment after 3 seconds
            setTimeout((function(){
                keyPoint.paintHighlightedTimeout = false;
                keyPoint.paintHighlighted = false;
                this.player.redrawKeyPoints = true;
                this.player.repaint();
            }).bind(this), 3000);
        }).bind(this));

        // mousing over the clock icon should highlight the comment on the density bar
        $(".post-goto-timeline-keypoint").hover(
            // mouseenter
            (function(e) {
                // highlight comment on the density bar
                var keyPoint = this.getKeyPointForPostId($(e.currentTarget).data("pid"));
                keyPoint.paintHighlighted = true;
                this.player.redrawKeyPoints = true;
                this.player.repaint();
            }).bind(this),
            // mouseleave
            (function(e) {
                var keyPoint = this.getKeyPointForPostId($(e.currentTarget).data("pid"));
                if (keyPoint.paintHighlightedTimeout == true) {
                    return;
                }
                keyPoint.paintHighlighted = false;
                this.player.redrawKeyPoints = true;
                this.player.repaint();
            }).bind(this)
        );

        $(".post-edit").on("click", (function(e) {
            e.preventDefault();

            var postId = $(e.currentTarget).data("pid");
            var data = {pid: postId};

            $(".post-cancel").trigger("click"); // edit and reply simultaneously not allowed. ensure that reply forms are cleared

            // ajax call to get the edit comment form
            $.ajax({
                url: Routing.generate('imdc_post_edit_ajax_specific', data),
                type: "POST",
                contentType: "application/x-www-form-urlencoded",
                data: data,
                success: (function(data) {
                    console.log("%s: %s: %s", Thread.TAG, ".post-edit:click", "success");

                    $("#containerPost" + postId).hide();

                    $("#containerEditPost" + postId).html(data.form);
                    $("#containerEditPost" + postId).show();

                    var keyPoint = this.getKeyPointForPostId(postId);
                    if (keyPoint != null && keyPoint.options.drawOnTimeLine) { // isTemporal
                        //TODO modify enableTemporalComment to properly do what these four lines do
                        this.player.seek(keyPoint.startTime);
                        this.player.currentMinTimeSelected = keyPoint.startTime;
                        this.player.currentMaxTimeSelected = keyPoint.endTime;
                        this.player.currentMinSelected = this.player.getXForTime(this.player.currentMinTimeSelected);
                        this.player.currentMaxSelected = this.player.getXForTime(this.player.currentMaxTimeSelected);

                        enableTemporalComment(this.player, true, $("#PostEditForm_startTime"), $("#PostEditForm_endTime"));
                    }
                }).bind(this),
                error: function(request) {
                    console.log("%s: %s: %s", Thread.TAG, ".post-edit:click", "error");

                    console.log(request.statusText);
                }
            });
        }).bind(this));

        // launch the modal dialog to delete a comment when you click the trash icon
        $(".post-delete").on("click", function(e) {
            e.preventDefault();

            var postId = $(this).data("pid");

            $("#modalDeleteButton").data("pid", postId);
            $("#modalCancelButton").data("pid", postId);
            $("#modaldiv").modal("toggle");
        });

        $("#modalDeleteButton").on("click", (function(e) {
            e.preventDefault();

            var postId = $("#modalDeleteButton").data("pid");
            var data = {pid: postId};

            $.ajax({
                url: Routing.generate('imdc_post_delete_specific_ajax', data),
                type: "POST",
                contentType: "application/x-www-form-urlencoded",
                data: data,
                success: (function(data) {
                    console.log("%s: %s: %s", Thread.TAG, "#modalDeleteButton:click", "success");

                    var post = $("#postContainer" + postId);

                    // create a feedback message
                    //TODO make me better
                    post.after('<div id="postDeleteSuccess" class="row"><div class="col-md-12"><p class="text-success"><i class="fa fa-check"></i> ' + data.feedback + '</p></div></div>');

                    // fade out the original comment
                    post.fadeOut('slow', function(){$(this).remove();});

                    // delete timeline region
                    this.player.removeKeyPoint(this.getKeyPointForPostId(postId));

                    $("#modaldiv").modal('hide');

                    // wipe out the feedback message after 5 seconds
                    setTimeout(function(){
                        $("#postDeleteSuccess").fadeOut("slow", function(e) {
                            $(this).remove();
                        });
                    }, 5000);
                }).bind(this),
                error: function(request) {
                    console.log("%s: %s: %s", Thread.TAG, "#modalDeleteButton:click", "error");

                    console.log(request.statusText);

                    $("#modaldiv").modal('hide');
                }
            });
        }).bind(this));

        $(".post-reply").on("click", function(e) {
            e.preventDefault();

            var postId = $(e.currentTarget).data("pid");
            var data = {pid: postId};

            $(".post-cancel").trigger("click"); // edit and reply simultaneously not allowed. ensure that reply forms are cleared

            $.ajax({
                url: Routing.generate('imdc_post_reply_ajax_specific', data),
                type: "POST",
                contentType: "application/x-www-form-urlencoded",
                data: data,
                success: function(data) {
                    console.log("%s: %s: %s", Thread.TAG, ".post-reply:click", "success");

                    $("#postContainer" + postId).append(data.form);

                    $(".post-reply[data-pid=" + postId + "]").hide();
                },
                error: function(request) {
                    console.log("%s: %s: %s", Thread.TAG, ".post-reply:click", "error");

                    console.log(request.statusText);
                }
            });
        });
    };

    Thread.prototype.createKeyPoints = function() {
        console.log("%s: %s", Thread.TAG, "createKeyPoints");

        $(".tt-post-container").each((function(key, element) {
            this.keyPoints.push(new KeyPoint(
                $(element).data("pid"),
                $(element).data("starttime"),
                $(element).data("endtime"),
                "comment",
                {
                    drawOnTimeLine: $(element).data("istemporal")
                }
            ));
        }).bind(this));
    };

    Thread.prototype.getKeyPointForPostId = function(postId) {
        console.log("%s: %s- postId=%d", Thread.TAG, "getKeyPointForId", postId);

        for (kp in this.keyPoints) {
            var keyPoint = this.keyPoints[kp];
            if (keyPoint.id == postId) {
                return keyPoint;
            }
        }
    };

    /**
     *
     * @param {object} options { mediaElement {object} element for the player
     *                              playHeadImage {string} twig asset path to an image
     *                              speedImages {object}
     *                              captionImages {object} }
     */
    Thread.prototype.createPlayer = function(options) {
        console.log("%s: %s", Thread.TAG, "createPlayer");

        this.playerOptions = options;

        this.player = new Player(options.mediaElement, {
            areaSelectionEnabled: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
            audioBar: false,
            overlayControls: true,
            playHeadImage: options.playHeadImage,
            playHeadImageOnClick: this.bind__onShowPostReplyFormClick
        });

        this.player.setKeyPoints(this.keyPoints);
        this.player.createControls();

        $(this.player).on(Player.EVENT_AREA_SELECTION_CHANGED, (function(e) {
            console.log("%s: %s", Thread.TAG, Player.EVENT_AREA_SELECTION_CHANGED);

            //TODO this can be improved
            $("#PostFormFromThread_startTime").val(this.player.currentMinTimeSelected.toFixed(2));
            $("#PostFormFromThread_endTime").val(this.player.currentMaxTimeSelected.toFixed(2));
            $("#PostEditForm_startTime").val(this.player.currentMinTimeSelected.toFixed(2));
            $("#PostEditForm_endTime").val(this.player.currentMaxTimeSelected.toFixed(2));
        }).bind(this));

        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OVER, function(e, keyPoint, coords) {
            console.log("%s: %s- keypoint=%o, coords=%o", Thread.TAG, Player.EVENT_KEYPOINT_MOUSE_OVER, keyPoint, coords);

            $("#postContainer" + keyPoint.id).addClass("tt-post-container-highlight");

            // avoid animating when key points are overlapped and multiple invokes of this event are called
            if (!$("#threadReplyContainer").is(':animated')) {
                $("#threadReplyContainer").animate({
                    scrollTop: $("#postContainer" + keyPoint.id).position().top
                }, 200);
            }
        });

        $(this.player).on(Player.EVENT_KEYPOINT_MOUSE_OUT, function(e, keyPoint) {
            console.log("%s: %s- keypoint=%o", Thread.TAG, Player.EVENT_KEYPOINT_MOUSE_OUT, keyPoint);

            $("#postContainer" + keyPoint.id).removeClass("tt-post-container-highlight");
        });

        $(this.player).on(Player.EVENT_KEYPOINT_CLICK, function(e, keyPoint, coords) {
            console.log("%s: %s- keypoint=%o, coords=%o", Thread.TAG, Player.EVENT_KEYPOINT_CLICK, keyPoint, coords);
        });

        $(this.player).on(Player.EVENT_KEYPOINT_BEGIN, function(e, keyPoint) {
            console.log("%s: %s- keypoint=%o", Thread.TAG, Player.EVENT_KEYPOINT_BEGIN, keyPoint);
        });

        $(this.player).on(Player.EVENT_KEYPOINT_END, function(e, keyPoint) {
            console.log("%s: %s- keypoint=%o", Thread.TAG, Player.EVENT_KEYPOINT_END, keyPoint);
        });
    };

    Thread.prototype._onShowPostReplyFormClick = function(e) {
        try { e.preventDefault(); } catch (e) { }

        $(".post-cancel").trigger("click"); // edit and reply simultaneously not allowed. ensure that reply forms are cleared

        $("#postReplyForm").show();
        $("#showPostReplyForm").hide();

        if (this.player) {
            enableTemporalComment(this.player, true, $("#PostFormFromThread_startTime"), $("#PostFormFromThread_endTime"));
        }
    };

    Thread.prototype.setPostTimelinePointForPost = function(mediaElement) {
        console.log("%s: %s", Thread.TAG, "setPostTimelinePointForPost");

        $(".tt-post-container").each((function(key, element) {
            var timeline = $("#postTimelinePoint" + $(element).data("pid"));

            mediaElement.on("loadedmetadata", function(e) {
                var duration = mediaElement[0].duration;
                var startTimePercentage = ((100 * $(element).data("starttime")) / duration).toFixed(2);
                var endTimePercentage = ((100 * $(element).data("endtime")) / duration).toFixed(2);
                var widthPercentage = (endTimePercentage - startTimePercentage).toFixed(2);

                timeline.css({
                    left: startTimePercentage + "%",
                    width: widthPercentage + "%"
                });
            });

            if (!isNaN(mediaElement[0].duration)) {
                mediaElement.trigger("loadedmetadata");
            }

            timeline.on("click", function(e) {
                $(".post-goto-timeline-keypoint[data-pid=" + $(this).data("pid") + "]").trigger("click");
            });

            timeline.hover(
                function(e) {
                    $(".post-goto-timeline-keypoint[data-pid=" + $(this).data("pid") + "]").trigger("mouseenter");
                },
                function(e) {
                    $(".post-goto-timeline-keypoint[data-pid=" + $(this).data("pid") + "]").trigger("mouseleave");
                }
            );
        }).bind(this));
    };

    /**
     * @param {object} videoElement
     */
        //TODO move to media chooser, as this may be a more general function
    Thread.prototype.createVideoRecorder = function(videoElement) {
        console.log("%s: %s", Thread.TAG, "createVideoRecorder");

        this.recorder = new Player(videoElement, {
            areaSelectionEnabled: false,
            updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
            type: Player.DENSITY_BAR_TYPE_RECORDER,
            audioBar: false,
            volumeControl: false,
            recordingSuccessFunction: this.bind_onRecordingSuccess,
            recordingErrorFunction: this.bind_onRecordingError,
            recordingPostURL: Routing.generate('imdc_files_gateway_record'),
            forwardButtons: [this.forwardButton, this.doneButton, this.doneAndPostButton],
            forwardFunctions: [this.bind_forwardFunction, this.bind_doneFunction, this.bind_doneAndPostFunction]
        });
        this.recorder.createControls();

        //TODO revise
        videoElement.parents(".ui-dialog").on("dialogbeforeclose", (function(event, ui) {
            console.log("videoElement dialogbeforeclose");
            if (this.recorder != null) {
                this.recorder.destroyRecorder();
            }
        }).bind(this));
    }

    //TODO move to media chooser, as this may be a more general function
    Thread.prototype.onRecordingSuccess = function(data) {
        console.log("%s: %s- mediaId=%d", Thread.TAG, "onRecordingSuccess", data.media.id);

        this.media = data.media;
        //	mediaChooser.setMedia(this.media);
    };

    //TODO move to media chooser, as this may be a more general function
    Thread.prototype.onRecordingError = function(e) {
        console.log("%s: %s- e=%s", Thread.TAG, "onRecordingError", e);
    };

    //TODO move to media chooser, as this may be a more general function
    Thread.prototype.forwardFunction = function() {
        console.log("%s: %s", Thread.TAG, "forwardFunction");

        this.recorder.destroyRecorder();

        /*mediaChooser.loadNextPage({
         url: Routing.generate('imdc_files_gateway_preview', {mediaId: this.media.id}),
         method: "POST",
         data: { mediaId: this.media.id }
         });*/

        mediaChooser.previewMedia({
            type: MediaChooser.TYPE_RECORD_VIDEO,
            mediaUrl: Routing.generate('imdc_files_gateway_preview', { mediaId: this.media.id }),
            mediaId: this.media.id
        });
    };

    Thread.prototype.doneFunction = function() {
        console.log("%s: %s", Thread.TAG, "doneFunction");

        this.recorder.destroyRecorder();

        /*mediaChooser.loadNextPage({
         url: Routing.generate('imdc_files_gateway_preview', {mediaId: this.media.id}),
         method: "POST",
         data: { mediaId: this.media.id }
         });*/

        mediaChooser.setMedia(this.media);
        mediaChooser._previewVideoForwardFunctionDone();
    };

    Thread.prototype.doneAndPostFunction = function() {
        console.log("%s: %s", Thread.TAG, "doneAndPostFunction");

        this.recorder.destroyRecorder();
        mediaChooser.setMedia(this.media);
        /*mediaChooser.loadNextPage({
         url: Routing.generate('imdc_files_gateway_preview', {mediaId: this.media.id}),
         method: "POST",
         data: { mediaId: this.media.id }
         });*/

        mediaChooser._previewVideoForwardFunctionDoneAndPost();
    };

    return Thread;
});














// when the user types in a value into the start time box,
// activate the temporal comment mode on the player
// at the time specified in the input start time box
$("#PostFormFromThread_startTime").on('blur', function() {
    var startOfComment = $(this).val();
    if (!startOfComment == '') {

        if (!globalPlayer.options.areaSelectionEnabled) { // if not making a temporal comment
            enableTemporalComment(globalPlayer, true, globalStartTimeInput, globalEndTimeInput);
        }
        globalPlayer.setAreaSelectionStartTime(startOfComment);
        globalPlayer.seek(startOfComment);
    }
});

// when the user types in a value into the end time box,
// activate the temporal comment mode on the player
// at the time specified in the input end time box if we aren't
// already creating a temporal comment
$("#PostFormFromThread_endTime").on('blur', function() {
    var endOfComment = $(this).val();
    var vidDuration = globalPlayer.getDuration();
    if (!$(this).val() == '') {
        var startOfComment = $("#PostFormFromThread_startTime").val();
        if (startOfComment == '') {
            $("#PostFormFromThread_startTime").val(endOfComment-1);
            globalPlayer.setAreaSelectionStartTime(endOfComment-1);
        }
//			if (endOfComment <= startOfComment) {
//				endOfComment = parseInt(startOfComment) + 1;
//			}
        if (endOfComment > vidDuration) {
            endOfComment = vidDuration;
        }

        if (!globalPlayer.options.areaSelectionEnabled) {
            globalPlayer.seek(Math.max(1, endOfComment-1));
            enableTemporalComment(globalPlayer, true, globalStartTimeInput, globalEndTimeInput);
        }
        else {
            globalPlayer.seek(Math.max(1, endOfComment));
        }
        globalPlayer.setAreaSelectionEndTime(endOfComment);
    }
});


function startTimeBlurFunction(startTime) {

    var startOfComment = $(startTime).val();
    if (!startOfComment == '') {

        if (!context.player.options.areaSelectionEnabled) { // if not making a temporal comment
            enableTemporalComment(context.player, true, globalStartTimeInput, globalEndTimeInput);
        }
        context.player.setAreaSelectionStartTime(startOfComment);
        context.player.seek(startOfComment);
    }

}

function endTimeBlurFunction(endTime) {
    var endOfComment = $(endTime).val();
    if (!$(endTime).val() == '') {
        var startOfComment = $(endTime).parents().find("#PostFormFromThread_startTime").val();
        if (startOfComment == '') {
            $("#PostFormFromThread_startTime").val(endOfComment-1);
            context.player.setAreaSelectionStartTime(endOfComment-1);
        }
//		if (endOfComment <= startOfComment) {
//			endOfComment = parseInt(startOfComment) + 1;
//		}
        if (endOfComment > context.player.getDuration()) {
            endOfComment = context.player.getDuration();
        }

        if (!context.player.options.areaSelectionEnabled) {
            context.player.seek(Math.max(1, endOfComment-1));
            enableTemporalComment(context.player, true, globalStartTimeInput, globalEndTimeInput);
        }
        else {
            context.player.seek(Math.max(1, endOfComment));
        }
        context.player.setAreaSelectionEndTime(endOfComment);
    }
}




/**
 *
 * @param player reference to the Player object
 * @param status true/false
 * @param startinput start time input element
 * @param endinput end time input element
 */
function enableTemporalComment(player, status, startinput, endinput) {

    // save time instead of renaming all the instances of 'control' to player
    controls = player;

    // save time
    startTimeInput = startinput;
    endTimeInput = endinput;


    player.pause();

    controls.oldPlayHeadImage = player.options.playHeadImage;

    // if we are enabling the temporal comment
    if (status) {
        // change plus icon on play head to nothing
        controls.playHeadImage = undefined;
        if (Number(controls.getCurrentTime())+controls.options.minLinkTime>controls.getDuration()) {
            controls.currentMinTimeSelected = controls.getDuration() - controls.options.minLinkTime;
        }
        else {
            controls.currentMinTimeSelected = controls.getCurrentTime();
        }
        controls.currentMinSelected 	  = controls.getXForTime(controls.currentMinTimeSelected);
        controls.currentMaxTimeSelected = Number(controls.currentMinTimeSelected)+controls.options.minLinkTime;
        controls.currentMaxSelected 	  = controls.getXForTime(controls.currentMaxTimeSelected);

        controls.setAreaSelectionEnabled(true);

        startTimeInput.val( Math.roundPrecise(controls.currentMinTimeSelected, 2));
        startTimeInput.on("change",function(){
            if (startTimeInput.val() >= controls.currentMaxTimeSelected - controls.options.minLinkTime)
            {
                if (startTimeInput.val() >= controls.getDuration()-controls.options.minLinkTime)
                {
                    controls.currentMaxTimeSelected = controls.getDuration();
                    controls.currentMinTimeSelected = controls.currentMaxTimeSelected - controls.options.minLinkTime;
                    controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
                    controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
                    endTimeInput.val( Math.roundPrecise(controls.currentMaxTimeSelected, 2));
                    startTimeInput.val(Math.roundPrecise(controls.currentMinTimeSelected, 2));
                }
                else
                {
                    controls.currentMinTimeSelected = startTimeInput.val();
                    controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
                    controls.currentMaxTimeSelected = Number(controls.currentMinTimeSelected) + controls.options.minLinkTime;
                    controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
                    endTimeInput.val( Math.roundPrecise(controls.currentMaxTimeSelected, 2));
                }
            }
            else if (startTimeInput.val()<=0)
            {
                controls.currentMinTimeSelected = 0;
                controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
                startTimeInput.val( Math.roundPrecise(controls.currentMinTimeSelected, 2));
            }
            else
            {
                controls.currentMinTimeSelected = startTimeInput.val();
                controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
            }
            controls.setHighlightedRegion(controls.currentMinSelected, controls.currentMaxSelected);
            controls.seek(controls.currentMinTimeSelected);
        });

        endTimeInput.val( Math.roundPrecise(controls.currentMaxTimeSelected, 2));
        endTimeInput.on("change", function(){
            if (endTimeInput.val() <= Number(controls.currentMinTimeSelected) + controls.options.minLinkTime)
            {
                if (endTimeInput.val()<controls.options.minLinkTime)
                {
                    controls.currentMinTimeSelected = 0;
                    controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
                    startTimeInput.val( Math.roundPrecise(controls.currentMinTimeSelected, 2));
                    controls.currentMaxTimeSelected = Number(controls.currentMinTimeSelected) + controls.options.minLinkTime;
                    controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
                    endTimeInput.val( Math.roundPrecise(controls.currentMaxTimeSelected, 2));
                }
                else
                {
                    controls.currentMaxTimeSelected = endTimeInput.val();
                    controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
                    endTimeInput.val( Math.roundPrecise(controls.currentMaxTimeSelected, 2));
                    controls.currentMinTimeSelected = controls.currentMaxTimeSelected - controls.options.minLinkTime;
                    controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
                    startTimeInput.val( Math.roundPrecise(controls.currentMinTimeSelected, 2));
                }
            }
            else if (endTimeInput.val()>=controls.getDuration())
            {
                controls.currentMaxTimeSelected = controls.getDuration();
                controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
                endTimeInput.val( Math.roundPrecise(controls.currentMaxTimeSelected, 2));
            }
            else
            {
                controls.currentMaxTimeSelected = endTimeInput.val();
                controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
            }
            controls.setHighlightedRegion(controls.currentMinSelected, controls.currentMaxSelected);
            controls.setVideoTime(controls.currentMaxTimeSelected);
        });
        controls.repaint();
    }
    // else we are disabling the temporal comment
    else {
        controls.setPlayHeadImage(context.playerOptions.playHeadImage);
        controls.setAreaSelectionEnabled(false);
        controls.currentMinSelected = controls.minSelected;
        controls.currentMinTimeSelected = controls.getTimeForX(controls.currentMinSelected);
        controls.currentMaxSelected = controls.maxSelected;
        controls.currentMaxTimeSelected = controls.getTimeForX(controls.currentMaxSelected);
        controls.repaint();

    }

}
