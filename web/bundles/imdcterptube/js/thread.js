var globalPlayer;
var globalPlayHeadImage;
var globalStartTimeInput;
var globalEndTimeInput;
var mediaChooser;
var speedSlowNormalImagePath;
var speedSlowFastImagePath;
var speedSlowSlowImagePath;
var speedSlow;
var global_video_dom;

$(document).ready(function() {

	$("#post-comment-button").click(function() {
		$("#comment-form-wrap").toggle();
		$(this).toggle();
	});
	
	$("a#post-comment-submit-button").click(function() {
		$("#PostFormFromThread_submit").click();
	});
	
	$("#cancelButton").click(function() {
		$("#comment-form-wrap").hide();
		$("#post-comment-button").show();
		enableTemporalComment(globalPlayer, false, globalStartTimeInput, globalEndTimeInput);
		
	});
	
	// make the content section grow automatically when necessary
	$("#PostFormFromThread_content").autosize();
	
	
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
    	
    	if (speedSlow==0) {
    		
    	}
    	else {
    		
    	}
    });

    // change the captioning display when you click the captioning button
    $("#closed-caption-button").click(function() {
        // alert(video_dom.tracks[0].mode);
        if(video_dom.tracks[0].mode == 2){
            $("#closed-caption-button img").attr("src", 'images/closedCaptioning.jpg');
            video_dom.tracks[0].mode = CAPTION_HIDDEN;
        }
        else
        {
            $("#closed-caption-button img").attr("src", 'images/closedCaptioningDown.jpg');
            video_dom.tracks[0].mode = CAPTION_SHOW;

        }

    });
	
    // clicking the clock icon will move the density bar to the comments time
    // and highlight the comment on the density bar
    $(".temporal-post-start-link").click(function(event) {
    	event.preventDefault();
    	
    	global_video_dom.currentTime = $(this).data('stime');
    	var post = getPostById($(this).data('pid'));
    	post.paintHighlighted = true;
    	globalPlayer.clearPlayer();
//    	globalPlayer.drawSignlinks();
    	globalPlayer.drawComments();
    	
    	// clear the highlighted comment after 3 seconds
    	setTimeout(function(){
    		post.paintHighlighted = undefined;
        	globalPlayer.clearPlayer();
//        	globalPlayer.drawSignlinks();
        	globalPlayer.drawComments();
    	}, 3000);
    	
    });
    
    
    /**
     * This snippet of code looks for a post id named anchor in the url and scrolls
     * the list of posts on the right of the page to the comment in
     * question adjusted with a vertical offset otherwise the comment is cut off
     */
    var $anchorname = window.location.hash.substring(1);
    if ($anchorname) {
    	$newanchor = parseInt($anchorname) - 1;
    	$targetElement = "div#post-" + $anchorname + "-wrap";
    	var topofelement = $(this).find($targetElement).offset().top;
    	var adjustedTop = topofelement - 150;
    	$("div#thread-reply-container").animate({
			scrollTop: adjustedTop
    	}, 200);
    	$($targetElement).css("background", "#56ef96").animate({
    		backgroundColor: "#ffffff"}, 1500
    	);

    }
    
    
});
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

/**
 * When you click on the trash icon to delete a post, you have to confirm via
 * a dialog and then it ajax deletes the post
 */
$("a#post-comment-delete").click(function(event) {
	
	var p_id = $(this).data("pid");
	var $theDeleteLink = $(this);
	
	// prevent the click from scrolling the page
	event.preventDefault();
	
	//alert('hi');
	
	// fade out the comment in question        
    $(this).parents("[data-pid='" + p_id + "']").eq(0).fadeTo('medium', 0.5);
	
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
        				$theDeleteLink.parents("[data-pid='" + p_id + "']").eq(0).fadeTo('slow', 0.0).remove();
                        //delete timeline region
        				// FIXME: get correct delete function from Martin
                        removeComment(p_id);
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
	
//	var player = new Player($("#{{ mediaFile.getID }}"));
	var player = new Player($(mediaId));
	
	//form inputs
//	var startTimeInput = $("#PostFormFromThread_startTime");
	var startTimeInput = $(startinput);
//	var endTimeInput   = $("#PostFormFromThread_endTime");
	var endTimeInput   = $(endinput);
	
	initVars(player, playheadimage, startTimeInput, endTimeInput);
	
	player.options.areaSelectionEnabled = false;
	player.options.updateTimeType = Player.DENSITY_BAR_UPDATE_TYPE_RELATIVE;
	player.options.backButton = false;
	player.options.forwardButton = false;
	player.options.audioBar = false;
	// player.options.backFunction= function(){if (confirm("This will delete your current recording. Are you sure?")) {goBack('<?php echo $postType?>');}};
	// player.options.forwardFunction = function (){transcodeAjax('<?php echo basename($video) ?>', '<?php echo basename($outputVideoFile) ?>', <?php echo $keepVideoFile ?>, controls);};
	
	player.options.playHeadImage = playheadimage;
	player.options.playHeadImageOnClick = function() { 
//	    $("#post-comment-button").click();
	    $("#comment-form-wrap").show();
		$("#post-comment-button").hide();
	    enableTemporalComment(player, true, startTimeInput, endTimeInput);
	};
	
	player.createControls();
	
	player.options.onAreaSelectionChanged = function(){
		startTimeInput.val( roundNumber(player.currentMinTimeSelected, 2));
	    endTimeInput.val( roundNumber(player.currentMaxTimeSelected, 2));
	};
	
	player.setComments(postArray);
	player.drawComments();
	player.repaint();
	
	
	return player;

}


$("#selectFiles").click(function(e) {
	mediaChooser = new MediaChooser($("div#files"), function(mediaID)
		 	{
//		 		alert(mediaID);
	            setMediaID(mediaID);
	 		}, true);
	mediaChooser.chooseMedia();
});

function setMediaID(mid) {
    $("#PostFormFromThread_mediatextarea").val(mid);
 }


function removePost(postId)
{
	for (var i=0; i< postArray.length; i++)
	{
		if (postArray[i].id == postId)
		{
			postArray.splice(i,1);
			globalPlayer.clearDensityBar();
			globalPlayer.drawSignLinks();
			globalPlayer.drawComments();
			
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
	  if (Number(controls.getCurrentTime())+controls.options.minLinkTime>controls.getDuration())
	  {
	      controls.currentMinTimeSelected = controls.getDuration() - controls.options.minLinkTime;
	  }
	  else
	  {
	  	controls.currentMinTimeSelected = controls.getCurrentTime();
	  }
	  controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
	  controls.currentMaxTimeSelected = Number(controls.currentMinTimeSelected)+controls.options.minLinkTime;
	  controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
	  
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
			//		controls.currentMinTimeSelected = controls.currentMaxTimeSelected - controls.options.minLinkTime;
			//		controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
			//		startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
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
					controls.setVideoTime(controls.currentMinTimeSelected);
	   });
	  endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
	  endTimeInput.on("change",function(){
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
	controls.clearPlayer();
	controls.drawComments();
//	controls.drawSignLinks();
	controls.repaint();
  	  
  }
  
  
  
}

/*
$("a#post-comment-edit").click(function(event) {
	
	var p_id = $(this).data("pid");
	var $theEditLink = $(this);
	var $theWholePost = $("div#post-" + p_id + "-wrap");
	
	// prevent the click from scrolling the page
	event.preventDefault();
	
	$.ajax({
		url : Routing.generate('imdc_post_edit_specific_ajax', {pid: p_id}),
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
			
            // remove the entire post and replace it with the post form
			$theWholePost.append(data);
			$theWholePost.remove();
		},
		error : function(request)
		{
			console.log(request);
			alert(request.statusText);
		}
	});
	
});
*/