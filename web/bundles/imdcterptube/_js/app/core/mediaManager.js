define(function() {

	var MediaManager = function() {
	};

	MediaManager.EVENT_DELETE_SUCCESS = "delete_media_success";
	MediaManager.EVENT_DELETE_ERROR = "delete_media_error";
	MediaManager.EVENT_UPDATE_SUCCESS = "update_media_success";
	MediaManager.EVENT_UPDATE_ERROR = "update_media_error";

    MediaManager.Event = {
        GET_INFO_SUCCESS: "eventGetInfoSuccess",
        GET_INFO_ERROR: "eventGetInfoError"
    };
	
	/**
	 * Used to ajax delete a media by its ID and provides a confirmation message
	 * to use
	 */
	MediaManager.prototype.deleteMedia = function(mediaID, confirmationMessage, confirmed) {
		var instance = this;
		if (typeof confirmationMessage !== "undefined" && confirmationMessage !== false) {

			var response = confirm(confirmationMessage);
			if (!response)
				return false;
		}
		if (typeof confirmed == "undefined") {
		    confirmed = false;
		}

		var address = Routing.generate('imdc_myfiles_delete', {
			'mediaId' : mediaID,
			'confirm' : confirmed
		});
		var data = {
			'mediaId' : mediaID,
			'confirm' : confirmed
		};
		$.ajax({
			url : address,
			type : "POST",
			contentType : "application/x-www-form-urlencoded",
			data : data,
			success : function(data) {
				if (data.responseCode == 200) {
					$(instance).trigger(MediaManager.EVENT_DELETE_SUCCESS);
				} else if (data.responseCode == 400) { // bad request
				    
				    var confirmText = "";
				    data.mediaInUse
						.forEach(function(element,
							index, array)
						{
						    if (index > 0)
							confirmText += ", ";
						    confirmText += Translator
							    .trans('filesGateway.deleteMediaInUseConfirmation.'
								    + element);
						});
					confirmText = Translator
						.trans(
							'filesGateway.deleteMediaInUseConfirmation.finalMessage',
							{
							    'mediaUsedLocations' : confirmText
							});
					console.log(data);
					if (confirm(confirmText))
					{
					    instance.deleteMedia(mediaID, false,  true);
					}
					
//					$(instance).trigger(MediaManager.EVENT_DELETE_ERROR,
//							{'feedback':data.feedback, 'mediaInUse': data.mediaInUse});
					console.log('Error: ' + data.feedback);
				} else {
					$(instance).trigger(MediaManager.EVENT_DELETE_ERROR,
							"Unknown Error");
					console.log('An unexpected error occured');
				}
			},
			error : function(request) {
				$(instance).trigger(MediaManager.EVENT_DELETE_ERROR, request);
				console.log(request);
			}
		});
	};

	/**
	 * Used to ajax trim the beginning and end of a media by its ID, startTime
	 * and endTime
	 * 
	 */
	MediaManager.prototype.trimMedia = function(mediaID, startTime, endTime) {
		var instance = this;

		var address = Routing.generate('imdc_myfiles_trim', {
			'mediaId' : mediaID,
			'startTime' : startTime,
			'endTime' : endTime
		});
		var data = {
			'mediaId' : mediaID,
			'startTime' : startTime,
			'endTime' : endTime
		};
		$.ajax({
			url : address,
			type : "POST",
			contentType : "application/x-www-form-urlencoded",
			data : data,
			success : function(data) {
				console.log(data);
				if (data.responseCode == 200) {
					// console.log(data);
					// $(instance).trigger(MediaManager.EVENT_DELETE_SUCCESS);
					instance.media = data.media;
				} else if (data.responseCode == 400) { // bad request
					// $(instance).trigger(MediaManager.EVENT_DELETE_ERROR,data.feedback);
					console.log('Error: ' + data.feedback);
				} else {
					// $(instance).trigger(MediaManager.EVENT_DELETE_ERROR,"Unknown
					// Error");
					console.log('An unexpected error occured');
				}
			},
			error : function(request) {
				// $(instance).trigger(MediaManager.EVENT_DELETE_ERROR,request);
				console.log(request);
			}
		});
	};

	/**
	 * Used to ajax update the media object. The id is in media.id. 
	 * Currently only used to update the title
	 */
	MediaManager.prototype.updateMedia = function(media) {
		var instance = this;
		if (typeof media == 'undefined') {
			$(instance).trigger(MediaManager.EVENT_UPDATE_ERROR,
					"Must send a media object");
			console.log('Error: ' + "Must send a media object");
		}
		var address = Routing.generate('imdc_myfiles_update', {
			'mediaId' : media.id
		});
		console.log(media);
		var data = {
			'mediaId' : media.id,
			'media' : JSON.stringify(media)
		};
		console.log(data);
		$.ajax({
			url : address,
			type : "POST",
			contentType : "application/x-www-form-urlencoded",
			data : data,
			success : function(data) {
				if (data.responseCode == 200) {
					$(instance).trigger(MediaManager.EVENT_UPDATE_SUCCESS);
				} else if (data.responseCode == 400) { // bad request
					$(instance).trigger(MediaManager.EVENT_UPDATE_ERROR,
							data.feedback);
					console.log('Error: ' + data.feedback);
				} else {
					$(instance).trigger(MediaManager.EVENT_UPDATE_ERROR,
							"Unknown Error");
					console.log('An unexpected error occured');
				}
			},
			error : function(request) {
				$(instance).trigger(MediaManager.EVENT_UPDATE_ERROR, request);
				console.log(request);
			}
		});
	};

    MediaManager.prototype.getInfo = function(mediaIds) {
        $.ajax({
            url: Routing.generate("imdc_myfiles_get_info"),
            data: {mediaIds: mediaIds},
            type: 'POST',
            success: (function(data, textStatus, jqXHR) {
                //console.log("%s: %s: %s", Post.TAG, "handlePage", "success");

                $(this).trigger($.Event(MediaManager.Event.GET_INFO_SUCCESS, {payload: data}));
            }).bind(this),
            error: (function(jqXHR, textStatus, errorThrown) {
                //console.log("%s: %s: %s", Post.TAG, "handlePage", "error");

                $(this).trigger($.Event(MediaManager.Event.GET_INFO_ERROR, {jqXHR: jqXHR}));
            }).bind(this)
        });
    };
	
	return MediaManager;
});