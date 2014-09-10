Group.TAG = "Group: ";

function Group() {
	this.mediaChooserParams = {
		element: function() {
			return $("div#files");
		},
		callback: function(media) {
			$("#ForumForm_mediaID").attr('data-mid', media.id);
			$("#UserGroupForm_userGroupForum_mediatextarea").val(media.id);
			$("#post-comment-button").hide(); // shouldn't be visible, but ensure this is hidden
		},
		recorderConfiguration: {
			forwardButtons:  ["<button class='forwardButton'></button>"],
			forwardFunctions: [this.forwardFunction],
			recordingSuccessFunction: this.recordingSucceeded
		}
	};
	
	MediaChooser.getInstance(this.mediaChooserParams);
}

/**
 * ui element event bindings in order of appearance
 */
Group.prototype.bindUIEvents = function() {
	console.log(Group.TAG + "bindUIEvents");
	
	// (paul) since I hid the real 'submit' button to provide a nicely stylized button
    // I have to click the real button when you click on the fancy one
    $("#usergroup-form-submit-button").click(function(e) {
        e.preventDefault();
        
    	$("button#UserGroupForm_submit").click();
    });
	
    MediaChooser.bindUIEvents(this.mediaChooserParams);
};

Group.prototype.forwardFunction = function() {
	console.log(mediaChooser.media.id);
	var mediaURL = Routing.generate('imdc_myfiles_preview', {mediaId: mediaChooser.media.id});
	mediaChooser.loadMediaPage(mediaChooser.media.id, mediaURL);
};

Group.prototype.recordingSucceeded = function(data) {
	console.log("Great success!!! Media id is: " + data.media.id);
};

$(document).ready(function() {

	window.group = new Group();
	
	group.bindUIEvents();

});
