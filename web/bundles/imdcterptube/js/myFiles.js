function deleteFile(currentElement, message)
{
	var response = confirm(message);
	if (!response)
		return false;
	var address = $(currentElement).attr('data-url');

	$.ajax(
	{
		url : address,
		type : "POST",
		contentType : "application/x-www-form-urlencoded",
		data :
		{
			mediaId : $(currentElement).attr('data-val')
		},
		success : function(data)
		{
			if (data.responseCode == 200)
			{
				$(currentElement).parent().parent().remove();
			}
			else if (data.responseCode == 400)
			{ // bad request
				alert('Error: ' + data.feedback);
			}
			else
			{
				alert('An unexpected error occured');
			}
		},
		error : function(request)
		{
			console.log(request);
			alert(request.statusText);
		}
	});
	return false;
}

function recordVideo(destinationDivElement, address)
{
	$.ajax(
	{
		url : address,
		type : "POST",
		contentType : "application/x-www-form-urlencoded",
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

function previewFileLink(currentElement, destinationDivElement, isPopUp)
{
	var mediaId = $(currentElement).attr('data-val');
	var mediaURL = $(currentElement).attr('data-url'); // Used to obtain the URL for the media

	previewMediaFile(mediaId, mediaURL, destinationDivElement, isPopUp);
}

function popUp(destinationDivElement, functionName, title)
{
	destinationDivElement.dialog(
	{

		autoOpen : false,
		resizable : false,
		modal : true,
		draggable : false,
		closeOnEscape : true,
		closeText : "X",
		dialogClass : "player-dialog",
		open : function(event, ui)
		{
			// $(".ui-dialog-titlebar-close", this.parentNode).hide();
			functionName;
		},
		create : function(event, ui)
		{
			$(event.target).parent().css('position', 'relative'); // Dumb comment at this line!
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

	destinationDivElement.dialog("open");
}

function previewMediaFile(mediaId, mediaURL, destinationDivElement, isPopUp)
{
	if (isPopUp)
	{
		popUp(destinationDivElement, loadMediaPage(mediaId, mediaURL, destinationDivElement), "Preview");
	}
	else
	{
		loadMediaPage(mediaId, mediaURL, destinationDivElement);
	}

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
}