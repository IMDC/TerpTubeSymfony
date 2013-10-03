MediaManager.EVENT_DELETE_SUCCESS = "delete_media_success";
MediaManager.EVENT_DELETE_ERROR = "delete_media_error";

function MediaManager()
{
}

/**
 * Gateway function to all media choosing functions
 * 
 * @param type -
 *            the type of media to choose, defaults to MediaChooser.TYPE_ALL
 */
MediaManager.prototype.deleteMedia = function(mediaID, confirmationMessage)
{
	var instance = this;
	if (typeof confirmationMessage !== "undefined")
	{

		var response = confirm(confirmationMessage);
		if (!response)
			return false;
	}
	
	var address = Routing.generate('imdc_files_gateway_remove', {'mediaId': mediaID});
	var data = {'mediaId' : mediaID};
	$.ajax({
		url : address,
		type : "POST",
		contentType : "application/x-www-form-urlencoded",
		data : data,
		success : function(data)
		{
			if (data.responseCode == 200)
			{
				$(instance).trigger(MediaManager.EVENT_DELETE_SUCCESS);
			}
			else if (data.responseCode == 400)
			{ // bad request
				$(instance).trigger(MediaManager.EVENT_DELETE_ERROR,data.feedback);
				console.log('Error: ' + data.feedback);
			}
			else
			{
				$(instance).trigger(MediaManager.EVENT_DELETE_ERROR,"Unknown Error");
				console.log('An unexpected error occured');
			}
		},
		error : function(request)
		{
			$(instance).trigger(MediaManager.EVENT_DELETE_ERROR,request);
			console.log(request);
		}
	});
};
