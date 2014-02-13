var recorder;

function deleteFile(currentElement, message) {
	var response = confirm(message);
	if (!response)
		return false;
	var address = $(currentElement).attr('data-url');

	$.ajax({
		url : address,
		type : "POST",
		contentType : "application/x-www-form-urlencoded",
		data : {
			mediaId : $(currentElement).attr('data-val')
		},
		success : function(data) {
			if (data.responseCode == 200) {
				$(currentElement).parent().parent().remove();
			} else if (data.responseCode == 400) { // bad request
				alert('Error: ' + data.feedback);
			} else {
				alert('An unexpected error occured');
			}
		},
		error : function(request) {
			console.log(request);
			alert(request.statusText);
		}
	});
	return false;
}

//function recordVideo(destinationDivElement, address, recorderConfiguration) {
//	$.ajax({
//		url : address,
//		type : "POST",
//		// contentType : "application/x-www-form-urlencoded",
//		data : {
//			recorderConfiguration : recorderConfiguration
//		},
//		success : function(data) {
//			destinationDivElement.html(data);
//		},
//		error : function(request) {
//			console.log(request);
//			alert(request.statusText);
//		}
//	});
//	return false;
//}

//function previewFileLink(currentElement, destinationDivElement, isPopUp) {
//	var mediaId = $(currentElement).attr('data-val');
//	var mediaURL = $(currentElement).attr('data-url'); // Used to obtain the
//														// URL for the media
//
//	previewMediaFile(mediaId, mediaURL, destinationDivElement, isPopUp);
//}

//function popUp(destinationDivElement, functionName, title) {
//	destinationDivElement.dialog({
//
//		autoOpen : false,
//		resizable : false,
//		modal : true,
//		draggable : false,
//		closeOnEscape : true,
//		dialogClass : "player-dialog",
//		open : function(event, ui) {
//			// $(".ui-dialog-titlebar-close", this.parentNode).hide();
//			functionName;
//		},
//		create : function(event, ui) {
//			$(event.target).parent().css('position', 'relative'); // Dumb
//																	// comment
//																	// at this
//																	// line!
//		},
//		position : {
//			my : "center top",
//			// at : "center top",
//			of : $("body")
//		},
//		show : "blind",
//		hide : "blind",
//		minWidth : 740,
//		title : title
//	});
//
//	destinationDivElement.dialog("open");
//}

//function previewMediaFile(mediaId, mediaURL, destinationDivElement, isPopUp) {
//	if (isPopUp) {
//		popUp(destinationDivElement, loadMediaPage(mediaId, mediaURL,
//				destinationDivElement), "Preview");
//	} else {
//		loadMediaPage(mediaId, mediaURL, destinationDivElement);
//	}
//
//}

//function loadMediaPage(mediaId, mediaURL, destinationDivElement) {
//	$.ajax({
//		url : mediaURL,
//		type : "POST",
//		contentType : "application/x-www-form-urlencoded",
//		data : {
//			mediaId : mediaId
//		},
//		success : function(data) {
//			destinationDivElement.html(data);
//		},
//		error : function(request) {
//			console.log(request);
//			alert(request.statusText);
//		}
//	});
//}

function hidePIP(pipDiv) {
	var clip = $(pipDiv);

	if (this.value == "show") {
		clip.show();
		var selectedAudio = $("input[type='radio']:checked").prop('value');
		if ((selectedAudio == "both") || (selectedAudio == "clip1")) {
			clip[0].volume = 1;
		}
		$(this).prop('value', 'hide').html("Hide PiP");
	} else {
		clip.hide();
		$(this).prop('value', 'show').html("Show PiP");
	}

}

function swapPIP(pipDiv, sourceDiv, hidePIPButton) {
	var clip1 = $(pipDiv);
	var clip2 = $(sourceDiv);

	var piprole;
	var sourcerole;

	if ((clip1.draggable("option", "disabled"))) {
		// clip 1 is acting as the source
		piprole = clip2;
		sourcerole = clip1;
	} else {
		piprole = clip1;
		sourcerole = clip2;
	}

	// get position, size, z-index of pip
	var piproleheight = piprole.height();
	var piprolewidth = piprole.width();
	var piprolezindex = piprole.css("z-index");
	var piproleposition = piprole.position(); // returns object accessible
												// with var.left or var.top
	var piprolepositionleft = piprole.css("left"); // need these explicit css
													// calls as .position
	var piprolepositiontop = piprole.css("top"); // doesn't return correct
													// values if elements are
													// hidden
	var piprolevisible = piprole.is(":visible");

	if (piprolepositionleft == "auto")
		piprolepositionleft = "0px";
	if (piprolepositiontop == "auto")
		piprolepositiontop = "0px";

	console.log(piprolepositionleft);
	// get position, size, z-index of source
	var sourceroleheight = sourcerole.height();
	var sourcerolewidth = sourcerole.width();
	var sourcerolezindex = sourcerole.css("z-index");
	var sourceroleposition = sourcerole.position(); // returns object accessible
													// with var.left or var.top
	var sourcerolepositionleft = sourcerole.css("left");
	var sourcerolepositiontop = sourcerole.css("top");

	// disable draggable and resizeable on pip
	piprole.draggable("option", "disabled", true);

	// switch css classes
	piprole.removeClass("pipstyle");
	piprole.addClass("sourcestyle");

	sourcerole.removeClass("sourcestyle");
	sourcerole.addClass("pipstyle");

	/** ************** PIP now acting as SOURCE from here ******** */
	/** ************** PIP now acting as SOURCE from here ******** */

	// swap positions
	// get position of container div element
	var vidcontainer = $("div#videoContainer");
	var vidcontainerpos = vidcontainer.offset();
	var vidcontainerpadding = vidcontainer.css("padding");

	var vcont = parseInt(vidcontainerpos.top, 10)
			+ parseInt(vidcontainerpadding, 10);
	var vconl = parseInt(vidcontainerpos.left, 10)
			+ parseInt(vidcontainerpadding, 10);

	// copy old pip's size and position attributes to 'new' pip
	// check if pip is hidden
	if (!piprolevisible) {
		// previous PIP video was hidden before swap started
		sourcerole.show();
		piprole.show();

		// reset show/hide pip button
		hidePIPButton.prop('value', 'hide').html("Hide PIP");
	}
	// fix for absolute position as resizable messes it up
	/*
	 * sourcerole.css({"height" : piproleheight, "width" : piprolewidth,
	 * "position" : "absolute", "left" : piprolepositionleft, "top" :
	 * piprolepositiontop});
	 *  // reset acting source movie dimensions, fix for absolute position
	 * piprole.css({ "position" : "absolute", "left" : vconl, "top" : vcont});
	 */

	sourcerole.css({
		"left" : piprolepositionleft,
		"top" : piprolepositiontop
	});

	// reset acting source movie dimensions, fix for absolute position
	piprole.css({
		"left" : 0,
		"top" : 0
	});

	// make sourcerole (now acting as pip) draggable and resizeable
	sourcerole.draggable("option", "disabled", false);

	// reveal close button element on new pip
	sourcerole.find('i').show();

	// hide close button element on old pip
	piprole.find('i').hide();

};