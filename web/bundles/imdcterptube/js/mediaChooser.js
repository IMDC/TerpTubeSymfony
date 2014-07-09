MediaChooser.TAG = "MediaChooser: ";

MediaChooser.TYPE_ALL = 0;
MediaChooser.TYPE_UPLOAD_AUDIO = 1;
MediaChooser.TYPE_UPLOAD_IMAGE = 2;
MediaChooser.TYPE_UPLOAD_OTHER = 3;
MediaChooser.TYPE_RECORD_VIDEO = 4;
MediaChooser.TYPE_RECORD_AUDIO = 5;
MediaChooser.TYPE_UPLOAD_VIDEO = 6;

MediaChooser.DIALOG_TITLE_SELECT = "Select from My Files";
MediaChooser.DIALOG_TITLE_PREVIEW = "Preview";
MediaChooser.DIALOG_RECORD_VIDEO = "Record a new video";
MediaChooser.DIALOG_RECORD_AUDIO = "Record a new audio";

MediaChooser.MEDIA_TYPES = ["audio", "video", "image", "other"];

function MediaChooser(element, callBackFunction, isPopUp)
{
	this.element = element;
	this.element.dialog({ autoOpen: false });
	this.callBackFunction = callBackFunction;
	this.isPopUp = isPopUp;
	this.recorderConfiguration = {};
	this.recordingSucceededCallback = null;
	this.media = [];
	
	this.bind_recordingSucceeded = this.recordingSucceeded.bind(this);
}

MediaChooser.getInstance = function(params) {
	if (window.mediaChooser == null) {
		window.mediaChooser = new MediaChooser(params.element(), params.callback, true); // assume popUp for all
	} else {
		window.mediaChooser.element = params.element();
		window.mediaChooser.callBackFunction = params.callback;
	}
	
	window.mediaChooser.setRecorderConfiguration(params.recorderConfiguration);
	
	return window.mediaChooser;
};

/**
 * ui element event bindings in order of appearance
 */
MediaChooser.bindUIEvents = function(mediaChooserParams) {
	console.log($MC.TAG + "bindUIEvents");
	
	$("a#selectFile").click(function(e) {
		e.preventDefault();
		
		$MC.getInstance(mediaChooserParams).chooseMedia();
	});
	
	$("#record-link").click(function(e) {
		e.preventDefault();
		
		$MC.getInstance(mediaChooserParams).chooseMedia(MediaChooser.TYPE_RECORD_VIDEO);
	});
	
	$MC.bindUploadFileUIEvents($("#uploadForms"), $("#choose-media-wrap"), mediaChooserParams);
	
	$("#removeMedia").click(function(e) {
		e.preventDefault();
		
		$MC.reset();
	});
};

MediaChooser.bindUploadFileUIEvents = function(formRootElement, linkRootElement, mediaChooserParams) {
	console.log($MC.TAG + "bindUploadFileUIEvents");
	
	$.each(MediaChooser.MEDIA_TYPES, function(index, value) {
		var resourceFile = formRootElement.find("#imdc_terptube_" + value + "_media_resource_file");
		var title = formRootElement.find("#imdc_terptube_" + value + "_media_title");
		var form = formRootElement.find("form[name=imdc_terptube_" + value + "_media]");
		
		resourceFile.change(function(e) {
			if (resourceFile.val() == "") {
				return;
			}
			
			title.val(MediaChooser.cleanFileNameNoExt(resourceFile.val()));
			
			$MC.getInstance(mediaChooserParams).loadNextPage(Routing.generate("imdc_files_gateway_" + value), new FormData(form[0]), "POST");
			
			resourceFile.val("");
			title.val("");
		});
	});
	
	$.each(MediaChooser.MEDIA_TYPES, function(index, value) {
		var link = linkRootElement.find("#upload-" + value + "-link");
		var resourceFile = formRootElement.find("#imdc_terptube_" + value + "_media_resource_file");
		
		link.click(function(e) {
			e.preventDefault();
			
			//$MC.getInstance(mediaChooserParams).chooseMedia(value);
			resourceFile.click();
		});
	});
};

MediaChooser.cleanFileNameNoExt = function(fileName) {
	return (fileName.substr(0, fileName.lastIndexOf('.')) || fileName).replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '-');
};

MediaChooser.success = function(media) {
	$("#selectedMediaTitle").html(media['title']);
	$("#choose-media-wrap").hide();
	$("#selected-media-wrap").show();
};

MediaChooser.reset = function() {
	$("#selectedMediaTitle").html("");
	$("#selected-media-wrap").hide();
	$("#choose-media-wrap").show();
};

MediaChooser.getDialogTitle = function(type) {
	console.log($MC.TAG + "getDialogTitle");
	
	switch (type) {
	case MediaChooser.TYPE_ALL:
		return MediaChooser.DIALOG_TITLE_SELECT; break;
	case MediaChooser.TYPE_RECORD_VIDEO:
		return MediaChooser.DIALOG_RECORD_VIDEO; break;
	case MediaChooser.TYPE_RECORD_AUDIO:
		return MediaChooser.DIALOG_RECORD_AUDIO; break;
	case MediaChooser.TYPE_UPLOAD_AUDIO:
	case MediaChooser.TYPE_UPLOAD_IMAGE:
	case MediaChooser.TYPE_UPLOAD_OTHER:
	case MediaChooser.TYPE_UPLOAD_VIDEO:
	default:
		return "";
	}
};

MediaChooser.prototype.setRecorderConfiguration = function(recorderConfiguration) {
	console.log($MC.TAG + "setRecorderConfiguration");
	
	this.recorderConfiguration = recorderConfiguration;
	this.recordingSucceededCallback = this.recorderConfiguration.recordingSuccessFunction;
	this.recorderConfiguration.recordingSuccessFunction = this.bind_recordingSucceeded;
};

MediaChooser.prototype.recordingSucceeded = function(data) {
	console.log($MC.TAG + "recordingSucceeded");
	
	this.media = data.media;
	
	this.recordingSucceededCallback(data);
};

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
		this.popUp(function(){instance.loadChooserPage(data);}, this.terminatingFunction, MediaChooser.getDialogTitle(this.type));
	}
	else
	{
		this.loadChooserPage(data);
	}
};

MediaChooser.prototype.terminatingFunction = function(media)
{
	if (typeof media !== "undefined")
	{
		this.media = media;
	}
	console.log("Final function. Media id: " + this.media.id);
	
	if (this.isPopUp && this.element.dialog("isOpen"))
	{
		console.log("Dialog still open");
		this.element.off("dialogclose");
		this.element.dialog("close");
	}
	
	this.callBackFunction(this.media);
	
	$MC.success(this.media);
};

MediaChooser.prototype.loadChooserPage = function(data)
{
	var instance = this;
	var request = {method: "POST", contentType: "application/x-www-form-urlencoded"};
	/*if (typeof data === "undefined") {
		request.method = "GET";
		request.contentType = null;
	}*/
	//data = (typeof data === "undefined") ? {type: instance.type}: data;
	//data.type = (typeof type === "undefined") ? instance.type : data.type;
	console.log("Load Chooser Page");
	instance.element.html("");
	$.ajax(
			{
				url : Routing.generate('imdc_media_chooser_by_type', {type: instance.type}),
				type : request.method,
				contentType : request.contentType,
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
	console.log(data);
	$.ajax(
			{
				url :url,
				type : method,
				contentType : contentType,
				data : data, // Firefox errors sometimes if data is not stringified 
				processData: processData,
				success : function(data)
				{
					if (data.finished !== 'undefined' && data.finished===true)
					{
						
						console.log("Data finished: "+data.finished);
						instance.terminatingFunction(data.media);
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
			my : "center 20%",
			//my : "center top",
			//at : "center top",
			//of : $("body")
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

window.$MC = MediaChooser;
