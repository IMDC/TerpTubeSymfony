/*
 * included by:
 * Thread:viewthread.html.twig
 * Thread:viewthreadPrototype.hml.twig
 */

function Thread() {
	this.player = null;
	this.forwardButton = "<button class='forwardButton'></button>";
	this.doneButton = "<button class='doneButton'></button>";
	this.doneAndPostButton = "<button class='doneAndPostButton'></button>";
	this.media = null;
	
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
				    //$("#ThreadForm_mediaID").attr('data-mid', mid); //TODO not used?
				},
				successAndPost: function(media) {
					$("#ThreadForm_mediatextarea").val(media.id);
					console.log("successAndPost");
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
				},
				successAndPost: function(media) {
					$("#PostFormFromThread_mediatextarea").val(media.id);
					console.log("successAndPost");
					//TODO actually post here
				},
				reset: function() {
					$("#PostFormFromThread_mediatextarea").val("");
				}
			}
		};
	}
};

/**
 * ui element event bindings in order of appearance
 * @param {number} page
 * @param {number} postId
 */
Thread.bindUIEvents = function(page, postId) {
	console.log("%s: %s- page=%d", Thread.TAG, "bindUIEvents", page);
	
	switch (page) {
	case Thread.Page.NEW:
	case Thread.Page.EDIT:
		Thread._bindUIEventsNewEdit();
		break;
	case Thread.Page.VIEW_THREAD:
		Thread._bindUIEventsViewThread();
		break;
	}
};

Thread._bindUIEventsNewEdit = function() {
	console.log("%s: %s", Thread.TAG, "_bindUIEventsNewEdit");
	
	MediaChooser.bindUIEvents(Thread.mediaChooserOptions(Thread.Page.NEW));
	
	/*
	 * by paul: since I hid the real 'submit' button to provide a nicely stylized button
	 * I have to click the real button when you click on the fancy one
	 */
	$("#thread-form-submit-button").on("click", function(e) {
		e.preventDefault();
		
		$("#ThreadForm_submit").click();
	});
};

Thread._bindUIEventsViewThread = function() {
	console.log("%s: %s", Thread.TAG, "_bindUIEventsViewThread");
	
	/*$("#post-comment-button").on("click", function(e) {
		e.preventDefault();
		
		$("#comment-form-wrap").show();
		$(this).hide();
		$("#PostFormFromThread_content").focus();
	});*/
	
	MediaChooser.bindUIEvents(Thread.mediaChooserOptions(Thread.Page.VIEW_THREAD));
	
	$(".post-comment-edit").on("click", function(e) {
		e.preventDefault();
		
		var postId = $(this).data("pid");
		var data = {pid: postId};
		
		$(".cancel-post").trigger("click"); // edit and reply simultaneously not allowed. ensure that reply forms are cleared
		
		// ajax call to get the edit comment form
		$.ajax({
			url: Routing.generate('imdc_post_edit_ajax_specific', data),
			type: "POST",
			contentType: "application/x-www-form-urlencoded",
			data: data,
			success: function(data) {
				console.log("%s: %s: %s", Thread.TAG, "_bindUIEventsViewThread", "success");
				
				$("#containerPost" + postId).hide();
				
				$("#containerEditPost" + postId).html(data.form);
				$("#containerEditPost" + postId).show();
				
				var editCommentKeypoint = getKeyPointFromId(window["postArray"], postId);
				console.log(editCommentKeypoint.options.drawOnTimeline);
				
				if (editCommentKeypoint.options.drawOnTimeLine) { // drawOnTimeline is == isTemporal
					enableTemporalComment(globalPlayer, true, $("#PostEditForm_startTime"), $("#PostEditForm_endTime"));
					
					$("#PostFormFromThread_startTime").on('blur', startTimeBlurFunction($("#PostEditForm_startTime")));
					$("#PostFormFromThread_endTime").on('blur', endTimeBlurFunction($("#PostEditForm_endTime")));
				}
				
			},
			error: function(request) {
				console.log("%s: %s: %s", Thread.TAG, "_bindUIEventsViewThread", "error");
				
				console.log(request.statusText);
			}
		});
	});
	
	$(".post-comment-reply").click(function(e) {
		e.preventDefault();
		
    	var postId = $(this).data("pid");
    	var data = {pid: postId};
    	
    	$(".cancel-post").trigger("click"); // edit and reply simultaneously not allowed. ensure that reply forms are cleared
    	
    	//var replyLink = $(this);
    	
    	$.ajax({
    		url: Routing.generate('imdc_post_reply_ajax_specific', data),
    		type: "POST",
    		contentType: "application/x-www-form-urlencoded",
    		data: data,
    		success: function(data) {
    			console.log("%s: %s: %s", Thread.TAG, "_bindUIEventsViewThread", "success");
    			
				$("div#post-" + postId + "-wrap").append(data.form);
    			
				$(".post-comment-reply[data-pid=" + postId + "]").hide();
    		},
    		error: function(request) {
				console.log("%s: %s: %s", Thread.TAG, "_bindUIEventsViewThread", "error");
				
				console.log(request.statusText);
    		}
    	});

    });
};

/**
 * @param {object} videoElement
 */
//TODO move to media chooser, as this may be a more general function
Thread.prototype.createVideoRecorder = function(videoElement) {
	console.log("%s: %s", Thread.TAG, "createVideoRecorder");
	
	this.player = new Player(videoElement, {
		areaSelectionEnabled: false,
		updateTimeType: Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
		type: Player.DENSITY_BAR_TYPE_RECORDER,
		audioBar: false,
		volumeControl: false,
		recordingSuccessFunction: this.bind_onRecordingSuccess,
		recordingErrorFunction: this.bind_onRecordingError,
		recordingPostURL: Routing.generate('imdc_files_gateway_record'),
		forwardButtons: [this.forwardButton, this.doneButton, this.doneAndPostButton],
		forwardFunctions: [this.bind_forwardFunction, this.bind_doneFunction, this.bind_doneAndPostFunction],
	});
	this.player.createControls();

	//TODO revise
	videoElement.parents(".ui-dialog").on("dialogbeforeclose", (function(event, ui) {
		console.log("videoElement dialogbeforeclose");
		if (this.player != null) {
			this.player.destroyRecorder();
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
	
	this.player.destroyRecorder();
	
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
	
	this.player.destroyRecorder();
	
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
	
	this.player.destroyRecorder();
	mediaChooser.setMedia(this.media);
	/*mediaChooser.loadNextPage({
		url: Routing.generate('imdc_files_gateway_preview', {mediaId: this.media.id}),
		method: "POST",
		data: { mediaId: this.media.id }
	});*/
	
	mediaChooser._previewVideoForwardFunctionDoneAndPost();
};

var globalPlayer;
var globalPlayHeadImage;
var globalStartTimeInput;
var globalEndTimeInput;
var speedSlowNormalImagePath;
var speedSlowFastImagePath;
var speedSlowSlowImagePath;
var speedSlow;
var ccNormalImagePath;
var ccPressedImagePath;
var global_video_dom;

$(document).ready(function() {

	thread = new Thread();
	//TODO context
	context = thread;
	
	Thread.bindUIEvents(Thread.Page.VIEW_THREAD);
	
	/**
	 * When you are creating a new reply to a thread and you click the submit button,
	 * it is necessary to use js to 'click' the original hidden form submit button 
	 * as I'm hiding the original form submit button (because it is a button element)
	 * element and I can't style it with a font-awesome glyph
	 */
	$("a#post-comment-submit-button").click(function() {
		if ($("textarea#PostFormFromThread_content").val()) {
			$(this).after('<span><i class="fa fa-spinner fa-spin"></i> Sending...</span>');
			$(this).remove();
			$("#PostFormFromThread_submit").click();
		}
		else {
			// by clicking the form's original submit button, we trigger the html5 validation
			// on the empty textarea field
			$("#PostFormFromThread_submit").click();
		}
	});
	
	/*$("#cancelButton").click(function() {
		//clear form values for start and endtime
		$("#PostFormFromThread_startTime").val('');
		$("#PostFormFromThread_endTime").val('');
		// hide form
		$("#comment-form-wrap").hide();
		
		// show new reply button
		$("#post-comment-button").show();
		
		// deactivate temporal comment mode
		enableTemporalComment(globalPlayer, false, globalStartTimeInput, globalEndTimeInput);
		
	});*/
	
	
	
	/**
	 * launch the modal dialog to delete a comment when you click the trash icon
	 */
	$("a.post-comment-delete-modal").click(function(e) {
		e.preventDefault();
		var p_id = $(this).data('pid');
		
		$("#modalDeleteButton").attr('data-pid', p_id);
		$("#modalCancelButton").attr('data-pid', p_id);
		$("#modaldiv").modal('toggle');
	});
	
	
	
	/**
	 * When you click on the trash icon to delete a post, you have to confirm via
	 * a dialog and then it ajax deletes the post
	 * @deprecated
	 */
	$("a.post-comment-delete").click(function(event) {
		
		var p_id = $(this).data("pid");
		var $theDeleteLink = $(this);
		
		// prevent the click from scrolling the page
		event.preventDefault();
		
		// fade out the comment in question    
		var $theWholeComment = $(this).parents("[data-pid='" + p_id + "']").eq(0);
	    $theWholeComment.fadeTo('medium', 0.5);
		
	    // show a dialog box asking for confirmation of delete
	    $( "#dialog-confirm" ).dialog({
	        resizeable: false,
	        height: 275,
	        modal: true,
	        buttons: {
	            "Yes": function() {
	                $.ajax({
	        			url : Routing.generate('imdc_post_delete_specific_ajax', {pid: p_id}),
	        			type : "POST",
	        			contentType : "application/x-www-form-urlencoded",
	        			data :
	        			{
	        				pid : p_id
	        			},
	        			success : function(data)
	        			{
	        				console.log("success");
	        				console.log(data);
	        				
	        				//$theDeleteLink.parents("[data-pid='" + p_id + "']").eq(0).fadeOut('slow', function(){$(this).remove();});
	                        $theWholeComment.after('<div id="postDeleteSuccess" class="row-fluid"><div class="span12"><p class="text-success"><i class="fa fa-check"></i> ' + data.feedback + '</p></div></div>');
	        				$theWholeComment.fadeOut('slow', function(){$(this).remove();});
	                        
	        				//delete timeline region
	                        globalPlayer.removeComment(p_id);

	                        // scroll to the top of the page to see the feedback
	                        //$("html, body").animate({ scrollTop: 0 }, "slow");
	                        
	                        // wipe out the feedback message after 5 seconds
	                        setTimeout(function(){
	                    		$("#postDeleteSuccess").fadeOut('slow', function() {$(this).remove();});
	                    	}, 5000);
	        			},
	        			error : function(request)
	        			{
	        				console.log(request);
	        				alert(request.statusText);
	        			}
	        		});
	                
	                $( this ).dialog( "close" );
	            },
	            Cancel: function() {
	                $( this ).dialog( "close" );
	                $theDeleteLink.parents("[data-pid='" + p_id + "']").eq(0).fadeTo('slow', 1.0);
	            }
	        }
	    });
	});
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	// what is this?
    $("#7").on("timeupdate", function(event) {
    	checkKeyPointStartTime(this.currentTime);
    });
	
//	$("#video-speed").popover(
//	 {
//		 trigger: 'hover',
//		 placement: 'right',
//		 title: 'Video Playback Speed',
//		 content: "Click to toggle between 0.5x, 1x, and 1.5x video speed"
//	 });
	
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
	
	// make the content section grow automatically when necessary
	// todo: is this necessary anymore?
	//$("#PostFormFromThread_content").autosize();
	
	
	//Change the video speed when the slowdown button is clicked
    $("#video-speed").click(function(event) {
    	event.preventDefault();
    	speedSlow = (speedSlow+1)%3;
    	switch (speedSlow) {
		case 0:
			setVideoDomPlaybackSpeed(1.0);
	        $("#video-speed img").attr("src", speedSlowNormalImagePath);
			break;
		case 1:
			setVideoDomPlaybackSpeed(2.0);
			$("#video-speed img").attr("src", speedSlowFastImagePath);
			break;
		case 2:
			setVideoDomPlaybackSpeed(0.5);
		    $("#video-speed img").attr("src", speedSlowSlowImagePath);
			break;
		default:
			setVideoDomPlaybackSpeed(1.0);
        	$("#video-speed img").attr("src", speedSlowNormalImagePath);
			break;
		}
    });

    // change the captioning display when you click the captioning button
    $("#closed-caption-button").click(function() {
        // alert(video_dom.tracks[0].mode);
        if(global_video_dom.tracks[0].mode == 2){
            $("#closed-caption-button img").attr("src", ccNormalImagePath);
            global_video_dom.tracks[0].mode = CAPTION_HIDDEN;
        }
        else
        {
            $("#closed-caption-button img").attr("src", ccPressedImagePath);
            global_video_dom.tracks[0].mode = CAPTION_SHOW;

        }

    });
	
    // clicking the clock icon will move the density bar to the comments time
    // and highlight the comment on the density bar
    $(".temporal-post-start-link").click(function(event) {
    	event.preventDefault();
    	
//    	global_video_dom.currentTime = $(this).data('stime');
    	globalPlayer.seek($(this).data('stime'));
    	var post = getPostById($(this).data('pid'));
    	post.paintHighlighted = true;
    	globalPlayer.redrawKeyPoints = true;
    	globalPlayer.repaint();
    	post.paintHighlighedTimeout = true;
    	
    	// clear the highlighted comment after 3 seconds
    	setTimeout(function(){
    		post.paintHighlighedTimeout = false;
    		post.paintHighlighted = undefined;
    		globalPlayer.redrawKeyPoints = true;
    		globalPlayer.repaint();
    	}, 3000);
    	
    });
    
    // mousing over the clock icon should highlight the comment on the density bar
    $(".temporal-post-start-link").hover(
        // mouse over
        function() {
            //highlight comment on the density bar
            var comment = getPostById($(this).data('pid'));
            comment.paintHighlighted = true;
            
            globalPlayer.redrawKeyPoints = true;
    		globalPlayer.repaint();
        },
        // mouse out
        function() {
        	
            var comment = getPostById($(this).data('pid'));
            if (comment.paintHighlighedTimeout == true)
        		return;
            comment.paintHighlighted = undefined;
            
            globalPlayer.redrawKeyPoints = true;
    		globalPlayer.repaint();
        }
    );
    
    
    /**
     * This snippet of code looks for a post id anchor in the url and scrolls
     * the list of posts on the right of the page to the comment in
     * question, adjusted with a vertical offset otherwise the comment is cut off
     */
    var $anchorname = window.location.hash.substring(1);
    if ($anchorname) {
    	//$newanchor = parseInt($anchorname) - 1;
    	/*
    	$targetElement = "div#post-" + $anchorname + "-wrap";
    	var topofelement = $(this).find($targetElement).offset().top;
    	var adjustedTop = topofelement - 150; // offset
    	$("div#thread-reply-container").animate({
			scrollTop: adjustedTop
    	}, 200);
    	*/
    	scrollPostIntoView($anchorname);
    	
    	$($targetElement).css("background", "#56ef96").animate({
    		backgroundColor: "#ffffff"}, 1500
    	);

    }
    
//    if ( $("#comment-form-wrap").find("span.help*") ) {
//    	$("#comment-form-wrap").show();
//    }

}); // end of document.ready



/**
 * 
 * @returns {Boolean}
 */
function confirmPostDelete() {
	
	var $theDeleteLink = $("#modalDeleteButton");
	var p_id = $theDeleteLink.data('pid');
	
	var $theWholeComment = getPostWrapJQueryObject(p_id);
	
	/**
	 * Ajax request to delete a comment
	 */
	$.ajax({
		url : Routing.generate('imdc_post_delete_specific_ajax', {pid: p_id}),
		type : "POST",
		contentType : "application/x-www-form-urlencoded",
		data :
		{
			pid : p_id
		},
		success : function(data)
		{
			console.log("success");
			console.log(data);
			$("#modaldiv").modal('hide');
			
			// create a feedback message 
			//TODO: restyle
            $theWholeComment.after('<div id="postDeleteSuccess" class="row-fluid"><div class="span12"><p class="text-success"><i class="fa fa-check"></i> ' + data.feedback + '</p></div></div>');
            
            // fade out the original comment
			$theWholeComment.fadeOut('slow', function(){$(this).remove();});
            
			//delete timeline region
			var tempKeyPoint = new KeyPoint();
			tempKeyPoint = getKeyPointFromId(window["postArray"], p_id);
            globalPlayer.removeKeyPoint(tempKeyPoint);

            // scroll to the top of the page to see the feedback
            //$("html, body").animate({ scrollTop: 0 }, "slow");
            
            // wipe out the feedback message after 5 seconds
            setTimeout(function(){
        		$("#postDeleteSuccess").fadeOut('slow', function() {$(this).remove();});
        	}, 5000);
		},
		error : function(request)
		{
			console.log(request);
			alert(request.statusText);
			$("#modaldiv").modal('hide');
		}
	});
	
	// return false to prevent the click from scolling around the page
	return false;
}

function checkKeyPointStartTime(currentTime) {
	// do something on time update
}

function getPostWrapJQueryObject(postid) {
	targetElement = "div#post-" + postid + "-wrap";
	return $(targetElement);
}

function refreshPlayer()
{
	console.log("RefreshPlayer called");
	globalPlayer.redrawKeyPoints = true;
	globalPlayer.repaint();
}

function setVideoDomPlaybackSpeed(speed)
{
	global_video_dom.playbackRate = speed;
}

function initVideoSpeedFunction(videodomelement, normalimgpath, fastimgpath, slowimgpath)
{
	global_video_dom = videodomelement;
	speedSlowNormalImagePath = normalimgpath;
	speedSlowFastImagePath = fastimgpath;
	speedSlowSlowImagePath = slowimgpath;
	speedSlow = 0;
}


function initVideoCaptioningFunction(videodomelement, ccnormalimgpath, ccpressedimgpath)
{
	global_video_dom = videodomelement;
	ccNormalImagePath = ccnormalimgpath;
	ccPressedImagePath = ccpressedimgpath;
}

function startTimeBlurFunction(startTime) {

	var startOfComment = $(startTime).val();
	if (!startOfComment == '') {
		
		if (!globalPlayer.options.areaSelectionEnabled) { // if not making a temporal comment
			enableTemporalComment(globalPlayer, true, globalStartTimeInput, globalEndTimeInput);
		}
		globalPlayer.setAreaSelectionStartTime(startOfComment);
		globalPlayer.seek(startOfComment);
	}

}

function endTimeBlurFunction(endTime) {
	var endOfComment = $(endTime).val();
	if (!$(endTime).val() == '') {
		var startOfComment = $(endTime).parents().find("#PostFormFromThread_startTime").val();
		if (startOfComment == '') {
			$("#PostFormFromThread_startTime").val(endOfComment-1);
			globalPlayer.setAreaSelectionStartTime(endOfComment-1);
		}
//		if (endOfComment <= startOfComment) {
//			endOfComment = parseInt(startOfComment) + 1;
//		}
		if (endOfComment > globalPlayer.getDuration()) {
			endOfComment = globalPlayer.getDuration();
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
}

/**
 * 
 * @param player a Player object
 * @param playheadimage the path to the image to use on the player's playhead
 * @param startinput a jquery reference to the start time input element
 * @param endinput a jquery reference to the end time input element
 */
function initVars(player, playheadimage, startinput, endinput) {
	globalPlayer = player;
	globalPlayHeadImage = playheadimage;
	globalStartTimeInput = startinput;
	globalEndTimeInput = endinput;
}

//round number, num is number you want to round and dec is the number of decimal places
function roundNumber(num, dec) {
    var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
    return result;
}

/**
 * 
 * @param mediaId string name element for the player
 * @param playheadimage twig asset path to an image
 * @param startinput string name of element for the start input
 * @param endinput string name of element for the end input
 * @returns {___player0} the player object reference
 */
function createPlayer(mediaId, playheadimage, startinput, endinput) {
	
	var player = new Player($(mediaId));
	
	//form inputs
	var startTimeInput = $(startinput);
	var endTimeInput   = $(endinput);
	
	initVars(player, playheadimage, startTimeInput, endTimeInput);
	
	player.options.areaSelectionEnabled = false;
	player.options.updateTimeType = Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE;
//	player.options.backButtons = false;
//	player.options.forwardButtons = false;
	player.options.audioBar = false;
	player.options.overlayControls = true,
	// player.options.backFunction= function(){if (confirm("This will delete your current recording. Are you sure?")) {goBack('<?php echo $postType?>');}};
	// player.options.forwardFunction = function (){transcodeAjax('<?php echo basename($video) ?>', '<?php echo basename($outputVideoFile) ?>', <?php echo $keepVideoFile ?>, controls);};
	
	player.options.playHeadImage = playheadimage;
	player.options.playHeadImageOnClick = function() { 
//	    $("#post-comment-button").click();
	    $("#comment-form-wrap").show('medium').animate({backgroundColor:'rgba(103, 196, 103, 0.39)'}, 200).animate({backgroundColor:'#ffffff'}, 300);
		$("#post-comment-button").hide();
	    enableTemporalComment(player, true, startTimeInput, endTimeInput);
	};
	
//	player.setComments(postArray);
	player.setKeyPoints(postArray);
	player.createControls();

	$(player).on(Player.EVENT_AREA_SELECTION_CHANGED, function() {
		startTimeInput.val( roundNumber(player.currentMinTimeSelected, 2) );
	    endTimeInput.val( roundNumber(player.currentMaxTimeSelected, 2) );
	});
	
 	$(player).on(Player.EVENT_KEYPOINT_MOUSE_OVER, function(event, keyPoint, coords) {
// 		post = infoarray.keyPoint;
// 		var coords = infoarray.coords;
		highlightPostBorder(keyPoint);
		scrollPostIntoView(keyPoint.id);
		console.log('mouse over keypoint event, x: ' + coords.x + ', y: ' + coords.y);
	});
 	
 	$(player).on(Player.EVENT_KEYPOINT_MOUSE_OUT, function(event, post) {
 		dehighlightPostBorder(post);
	});
	
 	$(player).on(Player.EVENT_KEYPOINT_CLICK, function(event, keyPoint, coords) {
 		// do something
// 		var coords = infoarray[1];
 		console.log('keypoint click event x:' + coords.x + ', y: ' + coords.y);
 	});
	
 	$(player).on(Player.EVENT_KEYPOINT_BEGIN, function(event, post) {
 		// do something
 		console.log('keypoint begin event fired at ' + $("video:first")[0].currentTime + ' keypoint id: ' + post.id + ', startTime: ' + post.startTime + ', endTime: ' + post.endTime);
 	});
 	
 	$(player).on(Player.EVENT_KEYPOINT_END, function(event, post) {
 		// do something
 		console.log('keypoint end event fired at ' + $("video:first")[0].currentTime + ' keypoint id: ' + post.id + ', startTime: ' + post.startTime + ', endTime: ' + post.endTime);

 	});
 	
 	return player;

}




function highlightPostBorder(post) {
	$thepost = getPostDomObject(post);
	$thepost.addClass("postBorderHighlight");
}

function dehighlightPostBorder(post) {
	$thepost = getPostDomObject(post);
	$thepost.removeClass("postBorderHighlight");
}

/**
 * Using the id of a post object, scrolls the comment container on the right of the page
 * to show the selected post
 * @param postid the id of the post
 */
function scrollPostIntoView(postid) {
	var $postContainer = $("div#thread-reply-container");
	var $targetElement = "div#post-" + postid + "-wrap";
	var topofelement = $postContainer.find($targetElement).offset().top;
	console.log(topofelement);
	var adjustedTop = topofelement - 250; // offset
	
//	$postContainer.animate({
//		scrollTop: adjustedTop
//	}, 200);
	
	$postContainer.animate({
		scrollTop: $postContainer.scrollTop() + $($targetElement).position().top 
						- $postContainer.height()/2 + $($targetElement).height()/2
	}, 200);
	
//	$postContainer.scrollTop($postContainer.scrollTop() + $($targetElement).position().top 
//								- $postContainer.height()/2 + $($targetElement).height()/2);
}



function getPostDomObject(comment) {
	var postID = comment.id;
	return $("#post-" + postID + "-wrap");
}

function removePost(postId)
{
	for (var i=0; i< postArray.length; i++)
	{
		if (postArray[i].id == postId)
		{
			postArray.splice(i,1);
//			globalPlayer.redrawComments = true;
			globalPlayer.redrawKeyPoints = true;
    		globalPlayer.repaint();
			
			return;
		}
	}
}

function getPostById(postId)
{
	for (var i=0; i< postArray.length; i++)
	{
		if (postArray[i].id == postId)
		{
			return postArray[i];
		}
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
	  
	  startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
	  startTimeInput.on("change",function(){
		if (startTimeInput.val() >= controls.currentMaxTimeSelected - controls.options.minLinkTime)
		{
			if (startTimeInput.val() >= controls.getDuration()-controls.options.minLinkTime)
			{
				controls.currentMaxTimeSelected = controls.getDuration();
				controls.currentMinTimeSelected = controls.currentMaxTimeSelected - controls.options.minLinkTime;
				controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
				controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
				endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
				startTimeInput.val(roundNumber(controls.currentMinTimeSelected, 2));
			}
			else
			{
				controls.currentMinTimeSelected = startTimeInput.val();
				controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
				controls.currentMaxTimeSelected = Number(controls.currentMinTimeSelected) + controls.options.minLinkTime;
				controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
				endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
			}
		}
		else if (startTimeInput.val()<=0)
		{
			controls.currentMinTimeSelected = 0;
			controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
			startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
		}
		else
		{
			controls.currentMinTimeSelected = startTimeInput.val();
			controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
		}
		controls.setHighlightedRegion(controls.currentMinSelected, controls.currentMaxSelected);
		controls.seek(controls.currentMinTimeSelected);
	  });
	  
	  endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
	  endTimeInput.on("change", function(){
	  	if (endTimeInput.val() <= Number(controls.currentMinTimeSelected) + controls.options.minLinkTime)
	  	{
	  		if (endTimeInput.val()<controls.options.minLinkTime)
	  		{
	  			controls.currentMinTimeSelected = 0;
	  			controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
	  			startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
	  			controls.currentMaxTimeSelected = Number(controls.currentMinTimeSelected) + controls.options.minLinkTime;
	  			controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
	  			endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
	  		}
	  		else
	  		{
	  			controls.currentMaxTimeSelected = endTimeInput.val();
	  			controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
	  			endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
	  			controls.currentMinTimeSelected = controls.currentMaxTimeSelected - controls.options.minLinkTime;
	  			controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
	  			startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
	  		}
	  	}
	  	else if (endTimeInput.val()>=controls.getDuration())
	  	{
	  		controls.currentMaxTimeSelected = controls.getDuration();
	  		controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
	  		endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
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
	controls.setPlayHeadImage(globalPlayHeadImage);
	controls.setAreaSelectionEnabled(false);
	controls.currentMinSelected = controls.minSelected;
	controls.currentMinTimeSelected = controls.getTimeForX(controls.currentMinSelected);
	controls.currentMaxSelected = controls.maxSelected;
	controls.currentMaxTimeSelected = controls.getTimeForX(controls.currentMaxSelected);
	controls.repaint();
  	  
  }
  
}
  /**
   * 
   * @param post the post object
   */
function createPopover(post, event) {
	 alert(event);
	 $("#video-speed").popover(
	 {
		 trigger: 'focus',
		 placement: 'bottom',
		 title: 'Twitter Bootstrap Popover',
		 content: "It's so simple to create a tooltop for my website!"
	 });
}

function initMiniVideoTimeline(mediaFileId, postId, postStartTime, postEndTime) {
	$videoElement = $('video#'+mediaFileId);
    $timeComment = $('#time-comment-'+postId);
     
//    $videoElement[0].addEventListener('loadedmetadata', function(event) {
//        $duration = $videoElement[0].duration;
//        $timeComment = $('#time-comment-'+postId);
//
//        startTimePercentage = ((100*postStartTime)/$duration).toFixed(2);
//        endTimePercentage = ((100*postEndTime)/$duration).toFixed(2);
//        widthPercentage = (endTimePercentage - startTimePercentage).toFixed(2);
//        
//        $timeComment.css('left', startTimePercentage + '%');
//        $timeComment.css('width', widthPercentage + '%');
//        $timeComment.css('background', 'red');
//    }, true);
    
    $timeComment.on('click', function() {
    	$(this).focusin();
    });
    
    $timeComment.on('focusin', function() {
//    	$videoElement[0].currentTime = postStartTime;
    	globalPlayer.seek(postStartTime);
        var comment = getPostById(postId);
        comment.paintHighlighted = true;
        globalPlayer.redrawKeyPoints = true;
		globalPlayer.repaint();
    });
    
    $timeComment.on('focusout', function() {
        var comment = getPostById(postId);
        comment.paintHighlighted = false;
        
        globalPlayer.redrawKeyPoints = true;
		globalPlayer.repaint();
    });

//    $timeComment.on( "dblclick", postDoubleClickHandler(postId, postStartTime, postEndTime));
    
}

function postDoubleClickHandler(postId, postStartTime, postEndTime) {

	$(globalPlayer).one(Player.EVENT_KEYPOINT_END, function(event, keyPoint, coords) {
		if (keyPoint.id == postId) {
			console.log('end event triggered');
			globalPlayer.pause();
		}
	});
	// listen for seek event
//	globalPlayer.on(Player.EVENT_SEEK, function(event, keyPoint, coords) {
//		console.log('event seek triggered');
//		// detach double click handler
//		globalPlayer.off(Player.EVENT_SEEK, postDoubleClickHandler);
//	});
	// listen until seek event
	
	globalPlayer.play();
}


function getKeyPointFromId(keyPointArray, keyPointId) {
	
	for (var int = 0; int < keyPointArray.length; int++) {
		var array_element = keyPointArray[int];
		console.log(array_element);
		if (array_element.id == keyPointId) {
			console.log('keypoint match found');
			return array_element;
		}
	}
	return;
}


