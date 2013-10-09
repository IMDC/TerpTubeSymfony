Player.DENSITY_BAR_TYPE_RECORDER = "recorder";
Player.DENSITY_BAR_TYPE_PLAYER = "player";
Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE = "absolute";
Player.DENSITY_BAR_UPDATE_TYPE_RELATIVE = "relative";

Player.EVENT_RECORDING_STARTED = "recording_started";
Player.EVENT_RECORDING_STOPPED = "recording_stopped";
Player.EVENT_RECORDING_UPLOAD_PROGRESS = "playback_progress";
Player.EVENT_RECORDING_UPLOADED = "recording_uploaded";

Player.EVENT_PLAYBACK_STARTED = "playback_started";
Player.EVENT_PLAYBACK_STOPPED = "playback_stopped";
Player.EVENT_PLAYBACK_FINISHED = "playback_finished";

Player.EVENT_INITIALIZED = "player_initialized";
Player.EVENT_SEEK = "player_seek";

// FIXME Add events for every action that the player does - e.g. onPlay, onStop, onRecordingStarted, onRecordingStopped
Player.prototype.createControls = function()
{
	/*
	 * options type can be player, record, preview
	 */
	var instance = this;

	this.elementID = this.options.controlBarElement;

	var el = $('<div class="videoControlsContainer"></div>');
	$(this.elementID).append(el);
	this.elementID = el;

	if (this.options.type == Player.DENSITY_BAR_TYPE_PLAYER)
	{
		this.setupVideo = this.setupVideoPlayback;
	}
	else if (this.options.type == Player.DENSITY_BAR_TYPE_RECORDER)
	{
		this.setupVideo = this.setupVideoRecording;
	}

	var trackElement = $('<div class="videoControlsContainer track"></div>');
	var canvasElement =$('<div class="videoControlsContainer track canvas"></div>');
	$(this.elementID).append(trackElement);
	trackElement.append(canvasElement);
	canvasElement.append(
			'<canvas class="videoControlsContainer track canvas densitybar"></canvas>').append(
			'<canvas class="videoControlsContainer track canvas selectedRegion"></canvas>').append(
			'<canvas class="videoControlsContainer track canvas thumb"></canvas>');
	trackElement.append(
			'<div class="videoControlsContainer track timeBox">0:00/0:00</div>');
	trackElement.append(
			'<button type="button" class="videoControlsContainer track fullScreenButton"></button>');
	if (this.options.type == Player.DENSITY_BAR_TYPE_RECORDER)
	{
		trackElement.append(
				'<button type="button" class="videoControlsContainer track selectCameraButton"></button>');
	}
	if (this.options.volumeControl)
	{
		trackElement.find(".videoControlsContainer.track.thumb").eq(0).mouseover(function()
		{
			instance.setVolumeBarVisible(false);
		});
		trackElement.append(
				'<div class="videoControlsContainer track volumeControl"></div>');
		trackElement.find(".videoControlsContainer.volumeControl").eq(0).mouseover(function()
		{
			instance.setVolumeBarVisible(true);
		});
		trackElement.find(".videoControlsContainer.volumeControl").eq(0).append(
				'<img alt="volume control" />').append(
				'<div class="videoControlsContainer track volumeControl volumeSlider"></div>');
		$(this.elementID).find(".videoControlsContainer.volumeControl img").eq(0).click(function()
		{
			instance.toggleMute();
		});

		$(function()
		{
			$(instance.elementID).find(".videoControlsContainer.volumeControl.volumeSlider").eq(0).slider(
					{
						orientation : "horizontal",
						range : "min",
						min : 0,
						max : 100,
						value : 100,
						slide : function(event, ui)
						{
							$(instance.videoID)[0].volume = ui.value / 100;
							if ($(instance.videoID)[0].volume > 0)
							{
								$(instance.elementID).find(".videoControlsContainer.volumeControl img").eq(0)
										.removeClass("mute");
							}
							else
							{
								$(instance.elementID).find(".videoControlsContainer.volumeControl img").eq(0)
										.addClass("mute");
							}
						}
					});
		});
	}
	$(this.elementID).append('<div class="videoControlsContainer controlsBar"></div>');
	$(this.elementID).find(".videoControlsContainer.controlsBar").eq(0).append(
			'<div class="videoControlsContainer controlsBar backButtons"></div>').append(
			'<div class="videoControlsContainer controlsBar forwardButtons"></div>').append(
			'<div class="videoControlsContainer controlsBar videoControls"></div>');
	if (this.options.backButton)
	{
		if (this.options.type == Player.DENSITY_BAR_TYPE_PLAYER)
		{
			$(this.elementID)
					.find(".videoControlsContainer.controlsBar.backButtons")
					.eq(0)
					.append(
							'<button type="button" class="videoControlsContainer controlsBar backButtons backButton"></button>');
		}
		else
		{
			$(this.elementID)
					.find(".videoControlsContainer.controlsBar.backButtons")
					.eq(0)
					.append(
							'<button type="button" class="videoControlsContainer controlsBar backButtons backButton record"></button>');
		}
		$(this.elementID).find(".videoControlsContainer.controlsBar.backButtons.backButton").eq(0).click(
				instance.options.backFunction);
	}
	if (this.options.type == Player.DENSITY_BAR_TYPE_PLAYER)
	{
		$(this.elementID)
				.find(".videoControlsContainer.controlsBar.videoControls")
				.eq(0)
				.append(
						'<button type="button" class="videoControlsContainer controlsBar videoControls beginButton"></button>')
				.append(
						'<button type="button" class="videoControlsContainer controlsBar videoControls stepBackwardButton"></button>')
				.append(
						'<button type="button" class="videoControlsContainer controlsBar videoControls playButton"></button>')
				.append(
						'<button type="button" class="videoControlsContainer controlsBar videoControls stepForwardButton"></button>')
				.append(
						'<button type="button" class="videoControlsContainer controlsBar videoControls endButton"></button>');
		$(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.beginButton").eq(0).click(function()
		{
			instance.jumpTo(0);
		});
		$(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.stepBackwardButton").eq(0).click(
				function()
				{
					instance.stepBackward();
				});
		$(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.playButton").eq(0).click(function()
		{
			instance.playPause();
		});
		$(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.stepForwardButton").eq(0).click(
				function()
				{
					instance.stepForward();
				});
		$(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.endButton").eq(0).click(function()
		{
			instance.jumpTo(1);
		});
	}
	else
	{
		$(this.elementID)
				.find(".videoControlsContainer.controlsBar.videoControls")
				.eq(0)
				.append(
						'<button type="button" class="videoControlsContainer controlsBar videoControls recordButton"></button>');
		$(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0).click(function()
		{
			instance.recording_toggleRecording();
		});
	}
	if (this.options.forwardButton)
	{
		if (this.options.type == Player.DENSITY_BAR_TYPE_PLAYER)
		{
			$(this.elementID)
					.find(".videoControlsContainer.controlsBar.forwardButtons")
					.eq(0)
					.append(
							'<button type="button" class="videoControlsContainer controlsBar forwardButtons forwardButton"></button>');
		}
		else
		{
			$(this.elementID)
					.find(".videoControlsContainer.controlsBar.forwardButtons")
					.eq(0)
					.append(
							'<button type="button" class="videoControlsContainer controlsBar forwardButtons forwardButton record"></button>');
		}
		$(this.elementID).find(".videoControlsContainer.controlsBar.forwardButtons.forwardButton").eq(0).click(
				instance.options.forwardFunction);
	}
	if (this.options.audioBar)
	{
		$(this.elementID).find(".videoControlsContainer.controlsBar").eq(0).append(
				'<div class="videoControlsContainer controlsBar audioButtonsBar"></div>');
		$(this.elementID)
				.find(".videoControlsContainer.controlsBar.audioButtonsBar")
				.eq(0)
				.append(
						'<div>Remove audio from the video?</div>'
								+ '<label class="audioOff" for="audioOff"><img width="30px" height="30px" alt="audio enabled" /> </label>'
								+ '<input type="radio" name="audioEnabled" value="false" id="audioOff" />'
								+ '<label class="audioOn" for="audioOn"><img width="30px" height="30px" alt="audio enabled" /> </label>'
								+ '<input type="radio" name="audioEnabled" value="true" class="preview-media" id="audioOn" checked="checked" />');
	}

	$(this.elementID).find(".videoControlsContainer.fullScreenButton").eq(0).click(function()
	{
		instance.toggleFullScreen();
	});
	$(this.elementID).find(".videoControlsContainer.selectCameraButton").eq(0).click(function()
	{
		instance.selectCamera();
	});

	this.trackPadding = 12;
	this.trackWidth = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0).width() - 2
			* this.trackPadding;
	this.trackHeight = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0).height() - 2
			* this.trackPadding;

	var densityBarElement = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var thumbElement = $(this.elementID).find(".videoControlsContainer.track.thumb").eq(0);
	var selectedRegionElement = $(this.elementID).find(".videoControlsContainer.track.selectedRegion").eq(0);

	densityBarElement[0].width = densityBarElement.width();
	densityBarElement[0].height = densityBarElement.height();
	thumbElement[0].width = thumbElement.width();
	thumbElement[0].height = thumbElement.height();
	selectedRegionElement[0].width = selectedRegionElement.width();
	selectedRegionElement[0].height = selectedRegionElement.height();

	// console.log("Track width:" + this.trackWidth);
	this.currentMinTimeSelected = 0;
	this.currentMaxTimeSelected = 0;
	this.durationSelected = 0;
	this.minSelected = this.trackPadding;
	this.maxSelected = this.trackPadding + this.trackWidth;
	this.currentMinSelected = this.minSelected;
	this.currentMaxSelected = this.maxSelected;
	this.triangleWidth = this.trackPadding;
	this.minTime = this.options.minLinkTime; // seconds
	this.minTimeCoordinate = 0;
	this.preview = false;
	this.stepSize = 0.1;
	this.maxSpeed = 2.0;
	this.timer;

	// var recording_minTime = 3 * 1000; //seconds
	this.recording_recordTimer;
	this.recording_transcodeTimer;

	// Set dimensions for the playHead Top
	if (this.options.playHeadImage)
	{
		this.setPlayHeadImage(this.options.playHeadImage);
	}
	// var recording_maxRecordingTime = 60*1000; //60 seconds
	// var recording_minRecordingTime = 1000*3;

	this.isRecording = false;
	this.isPastMinimumRecording = false;

	this.drawTrack();
	if (!this.options.controlBarVisible)
	{
		$(this.elementID).hide();
	}
	if (this.options.type == Player.DENSITY_BAR_TYPE_RECORDER)
	{
		this.setupVideo();
	}
	if (this.options.type == Player.DENSITY_BAR_TYPE_PLAYER)
	{
		// console.log($(this.videoID)[0].duration);

		// Check if we have already missed the metadata event
		$(this.videoID)[0].addEventListener('loadedmetadata', function()
		{
			instance.setupVideo();
		});
		if (!isNaN($(this.videoID)[0].duration))
		{
			instance.setupVideo();
		}
	}

};
Player.prototype.setPlayHeadImage = function(image)
{

	if (!image)
	{
		this.playHeadImage = image;
		return;
	}
	var instance = this;
	this.playHeadImage = new Image();
	this.playHeadImage.onload = function()
	{
		instance.repaint();
	};
	this.playHeadImage.src = image;
	this.playHeadImageHighlighted = false;
	this.playHeadImage.heightNormal = this.trackPadding * 1.5;
	this.playHeadImage.widthNormal = this.trackPadding * 1.5;
	this.playHeadImage.heightHighlighted = this.trackPadding * 1.8;
	this.playHeadImage.widthHighlighted = this.trackPadding * 1.8;
};

/*
 * Comments is an object array that contains - startTime:number, endTime:number, commentID:number, [
 */
Player.prototype.setComments = function(commentsArray)
{
	this.comments = commentsArray;
};

Player.prototype.addComment = function(comment)
{
	this.comments.push(comment);
	this.drawComments();
};

Player.prototype.drawComments = function()
{
	if (!this.comments)
		return;
	// Draw comments that are not highlighted first
	for ( var i = 0; i < this.comments.length; i++)
	{
		if (!this.comments[i].paintHighlighted)
			this.drawComment(this.comments[i]);
	}
	// Draw comments that are highlighted last so that they go on top of others
	for ( var i = 0; i < this.comments.length; i++)
	{
		if (this.comments[i].paintHighlighted == true)
			this.drawComment(this.comments[i]);
	}
};

Player.prototype.drawComment = function(comment)
{
	if (comment.isTemporal == 0)
		return;
	var densityBarElement = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var context = $(this.elementID).find(".videoControlsContainer.track.selectedRegion").eq(0)[0].getContext("2d");
	if (comment.paintHighlighted == true)
	{
		context.globalAlpha = 1;
		context.fillStyle = this.options.commentHighlightedColor;
	}
	else
	{
		context.globalAlpha = 0.4;
		context.fillStyle = comment.color;
	}
	var startX = this.getXForTime(comment.startTime);
	if (startX < this.trackPadding)
		startX = this.trackPadding;
	var endX = this.getXForTime(comment.endTime);
	if (endX > this.trackPadding + this.trackWidth)
		endX = this.trackPadding + this.trackWidth;
	context.fillRect(startX, this.trackPadding, endX - startX, densityBarElement.height() - 2 * this.trackPadding);
	context.globalAlpha = 1;
};

Player.prototype.drawSignLinks = function()
{
	if (!this.signLinks)
		return;
	for ( var i = 0; i < this.signLinks.length; i++)
	{
		this.drawSignLink(this.signLinks[i], this.options.signLinkColor);
	}
};

Player.prototype.drawSignLink = function(signlink, color)
{
	var densityBarElement = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var context = $(this.elementID).find(".videoControlsContainer.track.selectedRegion").eq(0)[0].getContext("2d");
	context.globalAlpha = 0.4;

	context.fillStyle = color;
	var startX = this.getXForTime(signlink.startTime);
	if (startX < this.trackPadding)
		startX = this.trackPadding;
	var endX = this.getXForTime(signlink.endTime);
	if (endX > this.trackPadding + this.trackWidth)
		endX = this.trackPadding + this.trackWidth;
	context.fillRect(startX, this.trackPadding, endX - startX, densityBarElement.height() - 2 * this.trackPadding);
	context.globalAlpha = 1;
};

Player.prototype.setSignLinks = function(signLinksArray)
{
	this.signLinks = signLinksArray;

};
Player.prototype.clearPlayer = function()
{
	var densityBarElement = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var context = $(this.elementID).find(".videoControlsContainer.track.selectedRegion").eq(0)[0].getContext("2d");
	context.clearRect(0, 0, densityBarElement.width(), densityBarElement.height());
};

function Player(videoID, options)
{
	this.videoID = videoID;
	$(this.videoID).wrap('<div class="video" />');
	this.elementID = $(this.videoID).parent();
	this.comments = new Array();
	// type can be player, recorder
	// playHeadImage - url of image to use as top of playhead
	// playHeadImageOnClick - function Player.prototype.to call on playheadImageClick
	// onAreaSelectionChanged - triggered when adjusting the selectionArea
	// signLinkColor - color for the signlinks
	// onCommentMouseOver(comment) - triggered when hovering over a comment
	// onCommentMouseOut(comment) - triggered when no longer hovering over a
	// comment
	// commentHighlightedColor - color to use when a comment is highlighted
	// controlBarElement - element where to construct the controlBar
	// additionalDataToPost - used to post more data to the recording script
	this.options = {
		volumeControl : true,
		type : Player.DENSITY_BAR_TYPE_PLAYER,
		recordingStream : null,
		updateTimeType : Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE,
		backButton : false,
		backFunction : function()
		{
			alert("Hello there Back");
		},
		forwardButton : false,
		forwardFunction : function()
		{
			alert("Forward");
		},
		recordingErrorFunction : function(e)
		{
			alert("Error:" + e);
		},
		recordingSuccessFunction : function(e)
		{
			alert('Success!', e);
		},
		audioBar : true,
		densityBarHeight : 40,
		areaSelectionEnabled : false,
		controlBarVisible : true,
		controlBarElement : $(this.elementID),
		minRecordingTime : 3,
		maxRecordingTime : 60,
		minLinkTime : 1,
		signLinkColor : "#0000FF",
		commentHighlightedColor : "#FF0000"
	};
	if (typeof options != 'undefined')
	{
		for (key in options)
		{
			this.options[key] = options[key];
		}
	}
}

Player.prototype.checkFeatures = function(format)
{
	if (format == "mp4")
		format = "h264";
	else if (format == "ogv")
		format = "ogg";
	if (!Modernizr.canvas || !Modernizr.video || !Modernizr.video[format])
		return false;
	return true;
};

Player.prototype.drawTrack = function()
{

	var densityBarElement = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var context = densityBarElement[0].getContext("2d");
	context.clearRect(0, 0, densityBarElement.width(), densityBarElement.height());
	context.lineJoin = "round";
	context.fillStyle = "#cccccc";
	context.strokeStyle = "#000000";
	context.strokeRect(0, 0, densityBarElement.width(), densityBarElement.height());
	context.fillRect(this.trackPadding, this.trackPadding, this.trackWidth, this.trackHeight);
	context.strokeRect(this.trackPadding, this.trackPadding, this.trackWidth, this.trackHeight);
};

Player.prototype.updateTimeBox = function(currentTime, duration)
{
	$(this.elementID).find(".videoControlsContainer.timeBox").eq(0).html(
			getTimeCodeFromSeconds(currentTime, duration, "/"));
};

Player.prototype.setPlayHeadHighlighted = function(flag)
{
	if (!this.playHeadImage)
		return;
	this.playHeadImageHighlighted = flag;
	this.repaint();
};

Player.prototype.paintThumb = function(time)
{
	var densityBarElement = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var context = $(this.elementID).find(".videoControlsContainer.track.thumb").eq(0)[0].getContext("2d");
	var position = this.getXForTime(time);
	context.clearRect(0, 0, densityBarElement.width(), densityBarElement.height());
	context.fillStyle = "#000000";
	context.strokeStyle = "#000000";
	// Draw the vertical line of the playhead
	context.lineWidth = 2;
	context.beginPath();
	context.moveTo(position, this.trackPadding);
	context.lineTo(position, densityBarElement.height() - this.trackPadding);
	context.closePath();
	context.stroke();

	// Draw the top part of the playHead
	if (!this.playHeadImage)
	{
		context.beginPath();
		context.moveTo(position - this.trackPadding, 0);
		context.lineTo(position + this.trackPadding, 0);
		context.lineTo(position, this.trackPadding);
		context.closePath();
		context.fill();
	}
	else
	{
		if (this.playHeadImageHighlighted)
		{
			// context.drawImage(this.playHeadImage,position-this.trackPadding*0.9,
			// 0, this.trackPadding*1.8, this.trackPadding*1.8);
			context.drawImage(this.playHeadImage, position - this.playHeadImage.heightHighlighted / 2, 0,
					this.playHeadImage.widthHighlighted, this.playHeadImage.heightHighlighted);
		}
		else
		{
			context.drawImage(this.playHeadImage, position - this.playHeadImage.heightNormal / 2, 0,
					this.playHeadImage.widthNormal, this.playHeadImage.heightNormal);
		}

	}
};

Player.prototype.drawLeftTriangle = function(position, context)
{
	context.fillStyle = "#FF0000";
	context.beginPath();
	context.moveTo(position - this.triangleWidth, 2 * this.trackPadding + this.trackHeight);
	context.lineTo(position, 2 * this.trackPadding + this.trackHeight);
	context.lineTo(position, this.trackPadding + this.trackHeight);
	context.closePath();
	context.fill();
};

Player.prototype.drawRightTriangle = function(position, context)
{
	context.fillStyle = "#FF0000";
	context.beginPath();
	context.moveTo(position + this.triangleWidth, 2 * this.trackPadding + this.trackHeight);
	context.lineTo(position, 2 * this.trackPadding + this.trackHeight);
	context.lineTo(position, this.trackPadding + this.trackHeight);
	context.closePath();
	context.fill();
};

Player.prototype.setAreaSelectionEnabled = function(flag)
{
	this.options.areaSelectionEnabled = flag;
	this.setHighlightedRegion(this.currentMinSelected, this.currentMaxSelected);
};

Player.prototype.getRelativeMouseCoordinates = function(event)
{

	var x = 0;
	var y = 0;
	if (event.offsetX !== undefined && event.offsetY !== undefined)
	{
		x = event.offsetX;
		y = event.offsetY;
	}
	else if (event.layerX !== undefined && event.layerY !== undefined)
	{
		x = event.layerX;
		y = event.layerY;
	}

	return {
		x : x,
		y : y
	};
};

Player.prototype.jumpTo = function(jumpPoint)
{
	if (jumpPoint == 0)
		this.setVideoTime(this.currentMinTimeSelected);
	else if (jumpPoint == 1)
		this.setVideoTime(this.currentMaxTimeSelected);

};

Player.prototype.checkStop = function()
{
	// if (video.paused)
	// return;
	if ($(this.videoID)[0].currentTime >= this.currentMaxTimeSelected)
	{
		this.pause();
		$(this.videoID)[0].currentTime = this.currentMaxTimeSelected;
		$(this).trigger(Player.EVENT_PLAYBACK_FINISHED);
	}
	this.repaint();
};

Player.prototype.play = function()
{
	if ($(this.videoID)[0].paused)
	{
		$(this.videoID)[0].play();
		$(this).trigger(Player.EVENT_PLAYBACK_STARTED);
	}
	// preview = false;
	// timer = setInterval("checkStop()", 100);
};

Player.prototype.pause = function()
{
	if (!$(this.videoID)[0].paused)
	{
		$(this.videoID)[0].pause();
		$(this).trigger(Player.EVENT_PLAYBACK_STOPPED);
	}
	// clearInterval(timer);
	this.preview = false;
};

Player.prototype.playPause = function()
{
	// Change icon on button
	if ($(this.videoID)[0].paused)
		this.play();
	else
		this.pause();
};

Player.prototype.setPlayButtonIconSelected = function(isPlayIcon)
{
	var playButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.playButton").eq(0);

	if (isPlayIcon)
	{
		// set the icon to the play icon
		playButton.removeClass("pause");
	}
	else
	{
		// set the icon to the pause icon
		playButton.addClass("pause");
	}
};

Player.prototype.repaint = function()
{
	time = this.getCurrentTime();
	if (time > this.getDuration())
		time = this.getDuration();
	this.paintThumb(time);
	if (this.options.type == Player.DENSITY_BAR_TYPE_PLAYER)
	{

		if (this.options.updateTimeType == Player.DENSITY_BAR_UPDATE_TYPE_RELATIVE)
		{
			var timeBoxCurrentTime = this.getCurrentTime() - this.currentMinTimeSelected;
			timeBoxCurrentTime = timeBoxCurrentTime <= 0 ? 0 : timeBoxCurrentTime;
			this.updateTimeBox(timeBoxCurrentTime, this.currentMaxTimeSelected - this.currentMinTimeSelected);
		}
		else if (this.options.updateTimeType == Player.DENSITY_BAR_UPDATE_TYPE_ABSOLUTE)
		{
			this.updateTimeBox(time, this.getDuration());
		}
		/*
		 * if (this.preview && this.getCurrentTime() >= this.currentMaxTimeSelected) { this.preview = false;
		 * this.pause(); this.setVideoTime(this.currentMaxTimeSelected); }
		 */
	}
	if (this.options.type == Player.DENSITY_BAR_TYPE_RECORDER)
	{

		this.currentMaxSelected = this.getXForTime(time);
		this.currentMaxTimeSelected = time;
		var timeBoxCurrentTime = time;
		this.updateTimeBox(timeBoxCurrentTime, this.getDuration());
		this.setHighlightedRegion(this.currentMinSelected, this.currentMaxSelected);
	}
	// setHighlightedRegion(currentMinSelected, currentMaxSelected);
};

Player.prototype.stepForward = function()
{
	if ($(this.videoID)[0].currentTime + this.stepSize > this.currentMaxTimeSelected)
	{
		$(this.videoID)[0].currentTime = this.currentMaxTimeSelected;
	}
	else
		$(this.videoID)[0].currentTime += this.stepSize;
	this.repaint();
};

Player.prototype.stepBackward = function()
{
	if ($(this.videoID)[0].currentTime - this.stepSize < this.currentMinTimeSelected)
	{
		$(this.videoID)[0].currentTime = this.currentMinTimeSelected;
	}
	else
		$(this.videoID)[0].currentTime -= this.stepSize;
	this.repaint();
};

Player.prototype.checkForPlayHeadClick = function(event)
{
	var coords = this.getRelativeMouseCoordinates(event);
	var currentTimeCoordinate = this.getXForTime(this.getCurrentTime());
	var oldTimeCoordinate = this.getXForTime(this.previousTime);
	if (oldTimeCoordinate == currentTimeCoordinate && coords.y < this.playHeadImage.heightHighlighted
			&& coords.x > currentTimeCoordinate - this.playHeadImage.widthHighlighted / 2
			&& coords.x < currentTimeCoordinate + this.playHeadImage.widthHighlighted / 2)
	{
		this.options.playHeadImageOnClick();
	}

};

Player.prototype.setMouseOutThumb = function(event)
{
	var instance = this;
	var densityBarThumbElement = $(this.elementID).find(".videoControlsContainer.track.thumb").eq(0);
	densityBarThumbElement.off('mousemove');
	densityBarThumbElement.on("mousemove", function(e)
	{
		instance.checkMouseOverFunctions(e);
	});
};

Player.prototype.checkMouseOverFunctions = function(e)
{
	this.setMouseOverThumb(e);
	if (!this.options.areaSelectionEnabled)
		this.onCommentMouseOver(e);
};

Player.prototype.onCommentMouseOver = function(event)
{
	var coords = this.getRelativeMouseCoordinates(event);
	for ( var i = 0; i < this.comments.length; i++)
	{
		var comment = this.comments[i];
		if (comment.isDeleted == true || comment.isTemporal == 0)
			continue;
		var startX = this.getXForTime(comment.startTime);
		var endX = this.getXForTime(comment.endTime);
		if (startX > coords.x || endX < coords.x || coords.y < this.trackPadding
				|| coords.y > this.trackPadding + this.trackHeight)
		{
			if (comment.highlighted == true)
			{
				comment.highlighted = undefined;
				this.options.onCommentMouseOut(comment);
			}
			continue;

		}
		if (comment.highlighted == true)
			continue;
		comment.highlighted = true;
		// console.log(this.comments[i]);
		this.options.onCommentMouseOver(comment);

	}
};

Player.prototype.setMouseOverThumb = function(event)
{
	// need to set the mousemove event to figure out if I am over the thumb to
	// highlight the playHeadImage
	var instance = this;
	if (!this.playHeadImage)
		return;
	var coords = this.getRelativeMouseCoordinates(event);
	if (coords.y < this.playHeadImage.heightHighlighted
			&& coords.x > this.getXForTime(this.getCurrentTime()) - this.playHeadImage.widthHighlighted / 2
			&& coords.x < this.getXForTime(this.getCurrentTime()) + this.playHeadImage.widthHighlighted / 2)
	{
		if (!instance.playHeadImageHighlighted)
			instance.setPlayHeadHighlighted(true);
	}
	else
	{
		if (instance.playHeadImageHighlighted)
			instance.setPlayHeadHighlighted(false);
	}
};
Player.prototype.setMouseDownThumb = function(event)
{
	event.preventDefault();
	var instance = this;
	var thumbCanvas = $(this.elementID).find(".videoControlsContainer.track.thumb").eq(0);
	var coords = this.getRelativeMouseCoordinates(event);
	this.preview = false;
	var currentTimeCoordinate = this.getXForTime(this.getCurrentTime());
	// this.mouseDownCoords = coords;

	if (this.playHeadImage && coords.y < this.playHeadImage.heightHighlighted
			&& coords.x > currentTimeCoordinate - this.playHeadImage.widthHighlighted / 2
			&& coords.x < currentTimeCoordinate + this.playHeadImage.widthHighlighted / 2)
	{
		// alert("down");
		this.previousTime = this.getCurrentTime();
		return;
	}
	else
	{
		this.previousTime = undefined;
	}
	if (coords.y < instance.trackPadding + instance.trackHeight)
	{ // Restrict
		// the
		// playhead
		// to only
		// within
		// the
		// selected
		// region
		// if (instance.playHeadImage && coords.y <
		// instance.playHeadImage.heightHighlighted)
		// {
		// return;
		// }
		thumbCanvas.on('mousemove', function(event)
		{
			var coords = instance.getRelativeMouseCoordinates(event);
			// if (coords.y < trackPadding + trackHeight)
			// {
			if (coords.x >= instance.currentMinSelected && coords.x <= instance.currentMaxSelected)
				instance.setVideoTimeFromCoordinate(coords.x);
			else if (coords.x < instance.currentMinSelected)
				instance.setVideoTime(instance.currentMinTimeSelected);
			else
				instance.setVideoTime(instance.currentMaxTimeSelected);
			// }
		});
		if (coords.x >= instance.currentMinSelected && coords.x <= instance.currentMaxSelected)
			instance.setVideoTimeFromCoordinate(coords.x);
	}
	else
	{
		if (!instance.options.areaSelectionEnabled)
		{
			return;
		}
		if (coords.x <= instance.currentMinSelected && coords.x >= instance.currentMinSelected - instance.triangleWidth)
		{
			// Left triangle selected
			var offset = instance.currentMinSelected - coords.x;
			thumbCanvas.on('mousemove', function(event)
			{
				var coords = instance.getRelativeMouseCoordinates(event);
				instance.currentMinSelected = coords.x + offset;
				if (instance.currentMinSelected < instance.minSelected)
				{
					instance.currentMinSelected = instance.minSelected;
				}
				else if (instance.currentMinSelected > instance.currentMaxSelected - instance.minTimeCoordinate)
				{
					instance.currentMinSelected = instance.currentMaxSelected - instance.minTimeCoordinate;
				}
				// else
				instance.currentMinTimeSelected = instance.getTimeForX(instance.currentMinSelected);

				instance.setHighlightedRegion(instance.currentMinSelected, instance.currentMaxSelected);
				instance.setVideoTime(instance.currentMinTimeSelected);
				if (instance.options.onAreaSelectionChanged)
					instance.options.onAreaSelectionChanged();
			});

		}
		else if (coords.x >= instance.currentMaxSelected
				&& coords.x <= instance.currentMaxSelected + instance.triangleWidth)
		{
			// Right triangle selected ;
			var offset = coords.x - instance.currentMaxSelected;
			thumbCanvas.on('mousemove', function(event)
			{
				var coords = instance.getRelativeMouseCoordinates(event);
				instance.currentMaxSelected = coords.x - offset;
				if (instance.currentMaxSelected > instance.maxSelected)
				{
					instance.currentMaxSelected = instance.maxSelected;
				}
				else if (instance.currentMaxSelected < instance.currentMinSelected + instance.minTimeCoordinate)
				{
					instance.currentMaxSelected = instance.currentMinSelected + instance.minTimeCoordinate;
				}
				// else
				instance.currentMaxTimeSelected = instance.getTimeForX(instance.currentMaxSelected);
				instance.setHighlightedRegion(instance.currentMinSelected, instance.currentMaxSelected);
				instance.setVideoTime(instance.currentMaxTimeSelected);
				if (instance.options.onAreaSelectionChanged)
					instance.options.onAreaSelectionChanged();
			});
		}
	}
};

Player.prototype.setHighlightedRegion = function(startX, endX)
{
	// alert (currentMinSelected +" "+startX);
	// if (currentMinSelected==startX && currentMaxSelected==endX)
	// return;
	var densityBarElement = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var context = $(this.elementID).find(".videoControlsContainer.track.selectedRegion").eq(0)[0].getContext("2d");
	context.clearRect(0, 0, densityBarElement.width(), densityBarElement.height());

	if (this.options.areaSelectionEnabled)
	{
		this.drawLeftTriangle(startX, context);
		this.drawRightTriangle(endX, context);
		context.fillStyle = "#00ff00";
		context.fillRect(startX, this.trackPadding, endX - startX, densityBarElement.height() - 2 * this.trackPadding);
	}
	else
	{
		if (this.options.type == Player.DENSITY_BAR_TYPE_RECORDER)
		{
			context.fillStyle = "#666666";
			context.fillRect(startX, this.trackPadding, endX - startX, densityBarElement.height() - 2
					* this.trackPadding);
		}
		// else
		// context.fillStyle = "#cccccc";
	}

};

Player.prototype.toggleFullScreen = function()
{
	if (!document.fullscreenElement && // alternative standard method
	!document.mozFullScreenElement && !document.webkitFullscreenElement)
	{ // current working methods

		var elem;
		if (this.options.type == Player.DENSITY_BAR_TYPE_RECORDER)
		{
			elem = $(this.videoID).parent().parent()[0];
		}
		else
			elem = $(this.videoID).parent()[0];
		if (elem.requestFullscreen)
		{
			elem.requestFullscreen();
		}
		else if (elem.mozRequestFullScreen)
		{
			elem.mozRequestFullScreen();
		}
		else if (elem.webkitRequestFullscreen)
		{
			elem.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
		}

	}
	else
	{
		if (document.cancelFullScreen)
		{
			document.cancelFullScreen();
		}
		else if (document.mozCancelFullScreen)
		{
			document.mozCancelFullScreen();
		}
		else if (document.webkitCancelFullScreen)
		{
			document.webkitCancelFullScreen();
		}
	}
};

Player.prototype.fullScreenChange = function(setFullScreen)
{
	// if (this.options.type == Player.DENSITY_BAR_TYPE_RECORDER)
	// {
	// elem = $(this.videoID).parent().parent()[0];
	// }
	var densityBarThumbElement = $(this.elementID).find(".videoControlsContainer.track.thumb").eq(0);
	var densityBarSelectedRegionElement = $(this.elementID).find(".videoControlsContainer.track.selectedRegion").eq(0);
	var densityBarElement = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var controlsBarElement = $(this.elementID).find(".videoControlsContainer.controlsBar").eq(0);

	var fullScreenElement = document.fullscreenElement || document.mozFullScreenElement
			|| document.webkitFullscreenElement;
	if (setFullScreen === true)
	{

		if ($(this.videoID).parents().filter(fullScreenElement).length == 0)
		{
			return;
		}
		$(fullScreenElement).addClass("fullScreen");
		$(fullScreenElement).find($(this.videoID)).addClass("fullScreen");

		var height = screen.height - (densityBarThumbElement.height() + controlsBarElement.height());
		if (this.options.type == Player.DENSITY_BAR_TYPE_RECORDER)
		{
			$(fullScreenElement).find("object").eq(0).addClass("fullScreen");
			$(fullScreenElement).find("object").eq(0).parent().addClass("fullScreen");
			$(fullScreenElement).find("object").eq(0).parent().css("max-height", height + "px");
		}
		else
		{
			$(this.videoID).css("max-height", height + "px");
		}

	}
	else
	{
		$("body").find(".fullScreen").removeClass("fullScreen");
		if (this.options.type == Player.DENSITY_BAR_TYPE_RECORDER)
			$(fullScreenElement).find("object").eq(0).parent().css("max-height", "");
		else
			$(this.videoID).css("max-height", "");
	}
	var width = densityBarThumbElement.width();
	densityBarThumbElement[0].width = width;
	densityBarSelectedRegionElement[0].width = width;
	densityBarElement[0].width = width;

	this.trackWidth = densityBarElement.width() - 2 * this.trackPadding;
	this.trackHeight = densityBarElement.height() - 2 * this.trackPadding;
	this.currentMinSelected = this.getXForTime(this.currentMinTimeSelected);
	this.currentMaxSelected = this.getXForTime(this.currentMaxTimeSelected);
	this.minSelected = this.trackPadding;
	this.maxSelected = this.trackPadding + this.trackWidth;

	this.drawTrack();
	this.repaint();
	if (this.options.areaSelectionEnabled)
		this.setHighlightedRegion(this.currentMinSelected, this.currentMaxSelected);
	else
		this.drawComments();
};

Player.prototype.setupVideoPlayback = function()
{
	var instance = this;
	$(this.videoID)[0].addEventListener('timeupdate', function()
	{
		instance.checkStop();
	}, false);
	$(this.videoID)[0].addEventListener('play', function()
	{
		instance.setPlayButtonIconSelected(false);
	}, false);
	$(this.videoID)[0].addEventListener('pause', function()
	{
		instance.setPlayButtonIconSelected(true);
	}, false);
	// Detect fullScreen Change
	// $(document).unbind("fullscreenchange mozfullscreenchange webkitfullscreenchange");
	$(document).on("fullscreenchange", function()
	{
		instance.fullScreenChange(document.fullscreen);
	});

	$(document).on("mozfullscreenchange", function()
	{
		instance.fullScreenChange(document.mozFullScreen);
	});

	$(document).on("webkitfullscreenchange", function()
	{
		instance.fullScreenChange(document.webkitIsFullScreen);
	});

	// this.minTimeCoordinate = this.getXForTime(this.minTime);
	this.minTimeCoordinate = this.getXForTime(this.minTime) - this.trackPadding;
	this.currentMinSelected = this.minSelected;
	this.currentMinTimeSelected = this.getTimeForX(this.currentMinSelected);
	this.currentMaxSelected = this.maxSelected;
	this.currentMaxTimeSelected = this.getTimeForX(this.currentMaxSelected);
	this.setHighlightedRegion(this.currentMinSelected, this.currentMaxSelected);
	this.drawComments();
	this.drawSignLinks();
	this.repaint();
	// this.paintThumb(0);
	$(this.elementID).find(".videoControlsContainer.track").eq(0).on('mouseleave', function()
	{
		instance.setVolumeBarVisible(false);
	});

	var densityBarThumbElement = $(this.elementID).find(".videoControlsContainer.track.thumb").eq(0);
	densityBarThumbElement.on('mousedown', function(e)
	{
		densityBarThumbElement.off("mousemove");
		instance.setMouseDownThumb(e);
	});
	densityBarThumbElement.on('mouseout', function(e)
	{
		instance.setMouseOutThumb(e);
	});
	densityBarThumbElement.on('mouseup', function(e)
	{
		densityBarThumbElement.off("mousemove");
		densityBarThumbElement.on("mousemove", function(e1)
		{
			instance.checkMouseOverFunctions(e1);
		});
	});
	densityBarThumbElement.on('mousemove', function(e)
	{
		instance.checkMouseOverFunctions(e);
	});
	densityBarThumbElement.on('click', function(e)
	{
		instance.checkForPlayHeadClick(e);
	});
	$(this).trigger(Player.EVENT_INITIALIZED);
};

Player.prototype.setupVideoRecording = function()
{
	var instance = this;
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	var forwardButton = $(this.elementID).find(".videoControlsContainer.controlsBar.forwardButtons.forwardButton")
			.eq(0);
	this.setInputEnabled(recordButton, false);
	this.setInputEnabled(forwardButton, false);
	this.minTimeCoordinate = this.getXForTime(this.minTime) - this.trackPadding;
	// this.minTimeCoordinate = this.getXForTime(this.minTime);
	this.currentMinSelected = this.minSelected;
	this.currentMinTimeSelected = this.getTimeForX(this.currentMinSelected);

	$(document).on("fullscreenchange", function()
	{
		instance.fullScreenChange(document.fullscreen);
	});

	$(document).on("mozfullscreenchange", function()
	{
		instance.fullScreenChange(document.mozFullScreen);
	});

	$(document).on("webkitfullscreenchange", function()
	{
		instance.fullScreenChange(document.webkitIsFullScreen);
	});
	// this.currentMaxSelected = this.maxSelected;
	// this.currentMaxTimeSelected = this.getTimeForX(this.currentMaxSelected);
	// this.setHighlightedRegion(this.currentMinSelected,
	// this.currentMaxSelected);
	this.hasRecorded = -1;
	this.recording_startTime = 0;
	this.paintThumb(0);
	this.repaint();

	window.URL = window.URL || window.webkitURL;
	navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia
			|| navigator.msGetUserMedia;
	if (navigator.getUserMedia)
	{ // TODO should expose the constraints in the options object.
		videoConstraints = {
			video : {
				mandatory : {
					minHeight : 480,
					minWidth : 640,
					maxWidth : 1024,
					maxHeight : 640
				}
			},
			audio : true
		};
		navigator.getUserMedia(videoConstraints, function(stream)
		{
			instance.stream = stream;
			$(instance.videoID).attr('src', window.URL.createObjectURL(stream));
			instance.recording_cameraReady(true);
			instance.recording_microphoneReady(true);
		}, instance.options.recordingErrorFunction);
	}
	else
	{
		videoElement.html('Problem with recording'); // fallback.
	}

	$(this).trigger(Player.EVENT_INITIALIZED);
};

Player.prototype.setInputEnabled = function(element, enabled)
{
	if (enabled)
	{
		element.attr("disabled", false);
		element.css('opacity', 1);
	}
	else
	{
		element.attr("disabled", true);
		element.css('opacity', 0.5);
	}
};

Player.prototype.selectCamera = function()
{
	$(this.videoID)[0].selectCamera();
};

Player.prototype.recording_checkStop = function()
{
	this.repaint();
	var time = this.getCurrentTime();
	if (!this.isPastMinimumRecording && time >= this.options.minRecordingTime)
	{
		this.isPastMinimumRecording = true;
		var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(
				0);
		this.setInputEnabled(recordButton, true);
	}
	if (time >= this.getDuration())
	{
		this.recording_stopRecording();
		this.set
	}

};

Player.prototype.recording_toggleRecording = function()
{
	// Change icon on button
	if (this.isRecording)
		this.recording_stopRecording();
	else
		this.recording_startRecording();

};

Player.prototype.recording_startRecording = function()
{
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	var forwardButton = $(this.elementID).find(".videoControlsContainer.controlsBar.forwardButtons.forwardButton")
			.eq(0);
	recordButton.addClass("recording");
	this.setInputEnabled(recordButton, false);
	this.setInputEnabled(forwardButton, false);
	this.currentMinSelected = this.minSelected;
	this.currentMinTimeSelected = this.getTimeForX(this.currentMinSelected);
	// this.currentMaxSelected =this.maxSelected;
	// this.currentMaxTimeSelected = this.getTimeForX(this.currentMaxSelected);
	// this.setHighlightedRegion(this.currentMinSelected,
	// this.currentMaxSelected);
	this.isRecording = true;
	this.hasRecorded = -1;
	this.recordAudio = RecordRTC(this.stream, {
	// bufferSize: 16384,
	// sampleRate: 45000
	});

	this.recordVideo = RecordRTC(this.stream, {
		type : 'video'
	});
	this.recordAudio.startRecording();
	this.recordVideo.startRecording();

	this.recording_recordingStarted();
	// $(this.videoID)[0].startRecording();
};

// Called by Flash when recording actually started
Player.prototype.recording_recordingStarted = function()
{
	var instance = this;
	this.recording_startTime = new Date().valueOf();
	if (this.recordTimer)
		clearInterval(this.recordTimer);
	this.recordTimer = setInterval(function()
	{
		instance.recording_checkStop();
	}, 100);
	$(this).trigger(Player.EVENT_RECORDING_STARTED);
};

Player.prototype.recording_stopRecording = function()
{
	// setBlur(true, "");
	this.recordTimer = clearInterval(this.recordTimer);
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	var backButton = $(this.elementID).find(".videoControlsContainer.controlsBar.backButtons.backButton").eq(0);
	var forwardButton = $(this.elementID).find(".videoControlsContainer.controlsBar.forwardButtons.forwardButton")
			.eq(0);

	this.setInputEnabled(recordButton, false);
	this.setInputEnabled(backButton, false);
	this.setInputEnabled(forwardButton, false);

	recordButton.removeClass("recording");
	this.hasRecorded = this.getCurrentTime();
	this.isRecording = false;
	this.isPastMinimumRecording = false;

	this.recordAudio.stopRecording();
	this.recordVideo.stopRecording();
	$(this).trigger(Player.EVENT_RECORDING_STOPPED);
	this.postRecordings(this.options.recordingPostURL, this.options.additionalDataToPost);

	// $(this.videoID)[0].stopRecording();
};

Player.prototype.postRecordings = function(address, additionalDataObject)
{
	// FormData
	var formData = new FormData();
	if (typeof additionalDataObject != "undefined" && additionalDataObject !== null)
	{
		Object.keys(additionalDataObject).forEach(function(key) //If AdditionalData is a JS object with key/value pairs
		{
			formData.append(key, additionalDataObject[key]);
		});
	}
	formData.append('audio-blob', this.recordAudio.getBlob());
	formData.append('video-blob', this.recordVideo.getBlob());
	var instance = this;
	// POST the Blobs
	$.ajax({
		url : address,
		type : "POST",
		contentType : false,
		data : formData,
		processData : false,
		xhr : function()
		{
			myXhr = $.ajaxSettings.xhr();
			if (myXhr.upload)
			{
				myXhr.upload.addEventListener('progress', function(evt)
				{
					if (evt.lengthComputable)
					{
						var percentComplete = (evt.loaded / evt.total).toFixed(2) * 100;
						// Do something with upload progress
						console.log("Uploading: " + percentComplete + "%");
						$(this).trigger(Player.EVENT_RECORDING_UPLOAD_PROGRESS, percentComplete);
					}
				}, false);
			}
			else
			{
				console.log("Upload progress is not supported.");
			}
			return myXhr;
		},
		success : function(data)
		{
			instance.recording_recordingStopped(true, data);
		},
		error : function(request)
		{
			instance.recording_recordingStopped(false, request);
			console.log(request);
			alert(request.statusText);
		}
	});
};

Player.prototype.recording_recordingStopped = function(success, data)
{
	// setBlur(false, "");
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	var backButton = $(this.elementID).find(".videoControlsContainer.controlsBar.backButtons.backButton").eq(0);
	var forwardButton = $(this.elementID).find(".videoControlsContainer.controlsBar.forwardButtons.forwardButton")
			.eq(0);
	this.setInputEnabled(recordButton, true);
	this.setInputEnabled(backButton, true);

	if (success)
	{
		this.setInputEnabled(forwardButton, true);
		this.options.recordingSuccessFunction(data);
	}
	else
	{
		this.options.recordingErrorFunction;
		alert("Recording failed!");
	}
	$(this).trigger(Player.EVENT_RECORDING_UPLOADED, [ data ]);
};

Player.prototype.recording_recordingUploadProgress = function(value)
{
	// $("#uploadProgress").html(value);
	setBlurText("Uploading: " + value + "%");
};

Player.prototype.recording_cameraReady = function(flag)
{
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	if (flag)
		this.setInputEnabled(recordButton, true);
	else
		this.setInputEnabled(recordButton, false);
};

Player.prototype.recording_microphoneReady = function(flag)
{
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	if (flag)
		this.setInputEnabled(recordButton, true);
	else
		this.setInputEnabled(recordButton, false);
};

Player.prototype.recording_goToPreviewing = function()
{
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	var backButton = $(this.elementID).find(".videoControlsContainer.controlsBar.backButtons.backButton").eq(0);
	var forwardButton = $(this.elementID).find(".videoControlsContainer.controlsBar.forwardButtons.forwardButton")
			.eq(0);
	this.setInputEnabled(forwardButton, false);
	this.setInputEnabled(recordButton, false);
	this.setInputEnabled(backButton, false);
	var blurText = "Converting video";
	setBlur(true, blurText);
	this.transcodeTimer = setInterval(function()
	{
		if (blurText.length > 20)
		{
			blurText = blurText.substring(0, 16);
		}
		else
		{
			blurText += ".";
		}

		setBlurText(blurText);
	}, 500);
	$(this.videoID)[0].startTranscoding();
};

Player.prototype.destroyRecorder = function()
{
	$(this.videoID)[0].src = "";
	this.stream.stop();
};

Player.prototype.recording_recordingTranscodingFinished = function(fileName)
{
	clearInterval(this.transcodeTimer);
	setBlur(false, "");
	if (fileName == null)
	{
		var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(
				0);
		var backButton = $(this.elementID).find(".videoControlsContainer.controlsBar.backButtons.backButton").eq(0);
		var forwardButton = $(this.elementID).find(".videoControlsContainer.controlsBar.forwardButtons.forwardButton")
				.eq(0);
		alert("Converting video failed! Please record again.");
		this.setInputEnabled(forwardButton, false);
		this.setInputEnabled(recordButton, true);
		this.setInputEnabled(backButton, true);
	}
	else
	{
		// alert("Transcoding finished successfully: "+fileName);
		refreshPage($(this.elementID).parent().attr("id"), "recordOrPreview/preview.php", 'vidfile=' + fileName
				+ '&type=record&keepvideofile=false');
	}
};

Player.prototype.getCurrentTime = function()
{
	if (this.options.type == Player.DENSITY_BAR_TYPE_RECORDER)
	{
		if (this.isRecording)
			return (new Date().valueOf() - this.recording_startTime) / 1000;
		else if (this.hasRecorded == -1)
			return 0;
		else
			return this.hasRecorded;
	}
	else if (this.options.type == Player.DENSITY_BAR_TYPE_PLAYER)
		return $(this.videoID)[0].currentTime;
};

Player.prototype.getDuration = function()
{
	if (this.options.type == Player.DENSITY_BAR_TYPE_RECORDER)
		return this.options.maxRecordingTime;
	else if (this.options.type == Player.DENSITY_BAR_TYPE_PLAYER)
		return $(this.videoID)[0].duration;
};

Player.prototype.getTimeForX = function(x)
{
	var time = (x - this.trackPadding) * this.getDuration() / this.trackWidth;
	return time;
};

Player.prototype.getXForTime = function(time)
{
	var x = this.trackPadding + time / this.getDuration() * this.trackWidth;
	return x;
};

Player.prototype.setVideoTimeFromCoordinate = function(position)
{
	var time = this.getTimeForX(position);
	if (time != $(this.videoID)[0].currentTime)
	{
		$(this.videoID)[0].currentTime = time;
		$(this).trigger(Player.EVENT_SEEK, [ time ]);
	}
	// this.repaint();
};

Player.prototype.setVideoTime = function(time)
{
	if (time != $(this.videoID)[0].currentTime)
		$(this.videoID)[0].currentTime = time;
	// this.repaint();
};

/*
 * function Player.prototype.transcodeAjax(inputVideoFile, outputVideoFile, keepVideoFile) { setControlsEnabled(false);
 * if (this.currentMinSelected == this.minSelected && this.currentMaxSelected == this.maxSelected) { //No need to trim
 * as the user has not moved the start/end points } setBlurText("Trimming Video..."); setBlur(true); $.ajax({ url:
 * "recordOrPreview/transcoder.php", type: "POST", data: { trim:"yes", inputVidFile: inputVideoFile, outputVidFile:
 * outputVideoFile, startTime: currentMinTimeSelected, endTime: currentMaxTimeSelected, keepInputFile: inputVideoFile},
 * success: function (data){transcodeSuccess(data);}, error: function Player.prototype.(data) {transcodeError(data);}
 * }); }
 * 
 */
Player.prototype.setControlsEnabled = function(flag)
{
	var instance = this;
	var elements = $(this.elementID).find(".videoControlsContainer :input");
	if (flag)
	{

		elements.each(function(index)
		{
			instance.setInputEnabled($(this), flag)
		});

		// elements.prop('disabled', false);
		$(this.elementID).find(".videoControlsContainer.track.thumb").eq(0).on('mousedown', function(e)
		{
			instance.setMouseDownThumb(e);
		});
	}
	else
	{
		elements.each(function(index)
		{
			instance.setInputEnabled($(this), flag)
		});
		// elements.prop('disabled', true);
		$(this.elementID).find(".videoControlsContainer.track.thumb").eq(0).off('mousedown');
	}
};

Player.prototype.setVolumeBarVisible = function(flag)
{
	if (flag)
		// $("#volumeSlider").css("display", "block");
		$(this.elementID).find(".videoControlsContainer.volumeControl.volumeSlider").eq(0).show('slide', {
			direction : "right"
		}, 200);
	else
		// $("#volumeSlider").css("display", "none");
		$(this.elementID).find(".videoControlsContainer.volumeControl.volumeSlider").eq(0).hide('slide', {
			direction : "right"
		}, 200);
};

Player.prototype.toggleMute = function()
{
	var instance = this;
	var imageElement = $(this.elementID).find(".videoControlsContainer.volumeControl img").eq(0);

	if (imageElement.hasClass("mute"))
	{
		imageElement.removeClass("mute");
		$(this.videoID)[0].muted = false;
		$(this.elementID).find(".videoControlsContainer.volumeControl.volumeSlider").eq(0).slider('value',
				$(instance.videoID)[0].volume * 100);

	}
	else
	{
		imageElement.addClass("mute");
		$(this.videoID)[0].muted = true;
		$(this.elementID).find(".videoControlsContainer.volumeControl.volumeSlider").eq(0).slider('value', 0);
	}
};

function getTimeCodeFromSeconds(time, duration, separator)
{
	time = Math.floor(time * 1000);
	time /= 1000;
	var sec = "" + Math.floor(time % 60);
	var min = "" + Math.floor((time / 60) % 60);
	var hrs = "" + Math.floor((time / 60) / 60) % 60;

	while (sec.length < 2)
		sec = "0" + sec;
	if (typeof (duration) == 'undefined')
	{
		while (min.length < 2)
			min = "0" + min;
		if (hrs == 0)
			return min + ":" + sec;
		else
			return hrs + ":" + min + ":" + sec;
	}

	duration = Math.floor(duration * 1000);
	duration /= 1000;
	var durationSec = "" + Math.floor(duration % 60);
	var durationMin = Math.floor((duration / 60) % 60);
	var durationHrs = Math.floor((duration / 60) / 60) % 60;

	while (durationSec.length < 2)
		durationSec = "0" + durationSec;

	var resultDuration = "" + durationSec;
	var result = "" + sec;

	if (durationHrs == 0)
	{
		if (durationMin == 0)
		{

		}
		else
		{
			if (durationMin > 9)
			{
				while (min.length < 2)
					min = "0" + min;
			}
			resultDuration = durationMin + ":" + durationSec;
			result = min + ":" + sec;
		}
		resultDuration = durationMin + ":" + durationSec;
		result = min + ":" + sec;
	}
	else
	{
		resultDuration = durationHrs + ":" + durationMin + ":" + durationSec;

		while (min.length < 2)
			min = "0" + min;
		while (hrs.length < 2)
			hrs = "0" + hrs;

		result = hrs + ":" + min + ":" + sec;
	}

	if (typeof (separator) == 'undefined')
		return result;
	else
		return result + separator + resultDuration;
}