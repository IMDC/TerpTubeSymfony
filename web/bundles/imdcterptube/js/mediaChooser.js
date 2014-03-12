MediaChooser.TYPE_ALL = 0;
MediaChooser.TYPE_UPLOAD_AUDIO = 1;
MediaChooser.TYPE_UPLOAD_IMAGE = 2;
MediaChooser.TYPE_UPLOAD_OTHER = 3;
MediaChooser.TYPE_RECORD_VIDEO = 4;
MediaChooser.TYPE_RECORD_AUDIO = 5;
MediaChooser.TYPE_UPLOAD_VIDEO = 6;

MediaChooser.DIALOG_TITLE = "Record/Choose a file";
MediaChooser.DIALOG_TITLE_PREVIEW = "Preview";

function MediaChooser(element, callBackFunction, isPopUp)
{
	this.element = element;
	this.callBackFunction = callBackFunction;
	this.isPopUp = isPopUp;
	this.mediaID = -1;
}

/**
 * Gateway function to all media choosing functions
 * @param type - the type of media to choose, defaults to MediaChooser.TYPE_ALL
 */
MediaChooser.prototype.chooseMedia = function(type, data)
{
	this.type = (typeof type === "undefined") ? MediaChooser.TYPE_ALL : type;
	var instance = this;
	if (this.isPopUp)
	{
		this.popUp(function(){instance.loadChooserPage(data);}, this.terminatingFunction, MediaChooser.DIALOG_TITLE);
	}
	else
	{
		this.loadChooserPage(data);
	}
};

MediaChooser.prototype.terminatingFunction = function(mediaID)
{
	if (typeof mediaID !== "undefined")
	{
		this.mediaID = mediaID;
	}
	console.log("Final function. Media id: "+mediaID);
	
	if (this.isPopUp && this.element.dialog("isOpen"))
	{
		console.log("Dialog still open");
		this.element.off("dialogclose");
		this.element.dialog("close");
	}
	
	this.callBackFunction(mediaID);
	
};

MediaChooser.prototype.loadChooserPage = function(data)
{
	var instance = this;
	data = (typeof data === "undefined") ? {type: instance.type}: data;
	data.type = (typeof type === "undefined") ? instance.type : data.type;
	console.log("Load Chooser Page");
	$.ajax(
			{
				url : Routing.generate('imdc_media_chooser_by_type', {type: instance.type}),
				type : "POST",
				contentType : "application/x-www-form-urlencoded",
				data : data,
				success : function(data)
				{
					console.log("Loaded Chooser Page");
					instance.element.html(data.page);
				},
				error : function(request)
				{
					console.log(request);
					alert(request.statusText);
				}
			});
};

MediaChooser.prototype.previewMediaFile = function (mediaId, mediaURL, isPopUp) {
	var instance = this;
	if (isPopUp) {
		this.popUp(function(){instance.loadMediaPage(mediaId, mediaURL);}, this.terminatingFunction, MediaChooser.DIALOG_TITLE_PREVIEW);
	} else {
		this.loadMediaPage(mediaId, mediaURL);
	}

};

MediaChooser.prototype.loadMediaPage = function(mediaId, mediaURL) {
	this.loadNextPage(mediaURL, {mediaId: mediaId}, "POST");
};

MediaChooser.prototype.loadNextPage = function(url,data, method)
{
	var instance = this;
	if (typeof method === 'undefined')
		method = "GET";
	
	var contentType = "application/x-www-form-urlencoded";
	var processData = true;
	if (method!="GET")
	{
		 contentType = false;
		 processData = false;
	}
	console.log("Load Next Page");
	$.ajax(
			{
				url :url,
				type : method,
				contentType : contentType,
				data : data,
				processData: processData,
				success : function(data)
				{
					if (data.finished !== 'undefined' && data.finished===true)
					{
						
						console.log("Data finished: "+data.finished);
						instance.terminatingFunction(data.mediaID);
					}
					else
					{
						console.log("Next Page loaded");
						instance.element.html(data.page);	
					}
					
				},
				error : function(request)
				{
					console.log(request);
					alert(request.statusText);
				}
			});
};
MediaChooser.prototype.popUp = function (onOpenFunction, onCloseFunction, title)
{
	this.element.dialog(
	{

		autoOpen : false,
		resizable : false,
		modal : true,
		draggable : false,
		closeOnEscape : true,
		dialogClass : "popup-dialog",
		open : function(event, ui)
		{
			// $(".ui-dialog-titlebar-close", this.parentNode).hide();
			console.log("in dialog before function");
			onOpenFunction.apply();
		},
		create : function(event, ui)
		{
			$(event.target).parent().css('position', 'relative'); 
		},
		close : function(event, ui)
		{
			// $(".ui-dialog-titlebar-close", this.parentNode).hide();
			onCloseFunction;
		},
		position :
		{
			my : "center top",
			// at : "center top",
			of : $("body")
		},
		show : "blind",
		hide : "blind",
		minWidth : 740,
		title : title
	});

	this.element.dialog("open");
};

MediaChooser.prototype.recordVideo = function(address, recorderConfiguration)
{
	//FIXME need to get this to work for ajax calls
	var instance = this;
	$.ajax(
	{
		url : address,
		type : "POST",
	//	contentType : "application/x-www-form-urlencoded",
		data: {recorderConfiguration: recorderConfiguration},
		success : function(data)
		{
			instance.element.html(data);
			alert("success");
		},
		error : function(request)
		{
			console.log(request);
			alert(request.statusText);
		}
	});
	return false;
};

MediaChooser.prototype.getRecorder = function ()
{
	return this.recorder;
};

MediaChooser.prototype.setRecorder = function(newRecorder)
{
	this.recorder = newRecorder;
};
