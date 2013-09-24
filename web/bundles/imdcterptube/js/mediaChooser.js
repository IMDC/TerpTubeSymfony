MediaChooser.TYPE_ALL = 0;
MediaChooser.TYPE_UPLOAD_AUDIO = 1;
MediaChooser.TYPE_UPLOAD_IMAGE = 2;
MediaChooser.TYPE_UPLOAD_OTHER = 3;
MediaChooser.TYPE_RECORD_VIDEO = 4;
MediaChooser.TYPE_RECORD_AUDIO = 5;
MediaChooser.TYPE_UPLOAD_VIDEO = 6;

MediaChooser.DIALOG_TITLE = "Choose a file";

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
MediaChooser.prototype.chooseMedia = function(type)
{
	this.type = (typeof type === "undefined") ? MediaChooser.TYPE_ALL : type;
	var instance = this;
	if (this.isPopUp)
	{
		this.popUp(function(){instance.loadChooserPage(instance.type);}, this.terminatingFunction, MediaChooser.DIALOG_TITLE);
	}
	else
	{
		this.loadChooserPage(this.type);
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

MediaChooser.prototype.loadChooserPage = function()
{
	var instance = this;
	console.log("Load Chooser Page");
	$.ajax(
			{
				url : Routing.generate('imdc_media_chooser_by_type', {type: instance.type}),
				type : "POST",
				contentType : "application/x-www-form-urlencoded",
				data :
				{
					type : instance.type
				},
				success : function(data)
				{
					console.log("Load Chooser Page");
					instance.element.html(data.page);
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

function recordVideo(destinationDivElement, address, recorderConfiguration)
{
	$.ajax(
	{
		url : address,
		type : "POST",
	//	contentType : "application/x-www-form-urlencoded",
		data: {recorderConfiguration: recorderConfiguration},
		success : function(data)
		{
			destinationDivElement.html(data);
		},
		error : function(request)
		{
			console.log(request);
			alert(request.statusText);
		}
	});
	return false;
}
/*
function previewFileLink(currentElement, destinationDivElement, isPopUp)
{
	var mediaId = $(currentElement).attr('data-val');
	var mediaURL = $(currentElement).attr('data-url'); // Used to obtain the URL for the media

	previewMediaFile(mediaId, mediaURL, destinationDivElement, isPopUp);
}

function loadMediaPage(mediaId, mediaURL, destinationDivElement)
{
	$.ajax(
	{
		url : mediaURL,
		type : "POST",
		contentType : "application/x-www-form-urlencoded",
		data :
		{
			mediaId : mediaId
		},
		success : function(data)
		{
			destinationDivElement.html(data);
		},
		error : function(request)
		{
			console.log(request);
			alert(request.statusText);
		}
	});
}*/