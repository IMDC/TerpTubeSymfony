var mediaChooser;

$("#selectFiles").click(function(e) {
	mediaChooser = new MediaChooser($("div#files"), function(mediaID)
		 	{
//		 		alert(mediaID);
	            setMediaID(mediaID);
	 		}, true);
	mediaChooser.chooseMedia();
});

function setMediaID(mid) {
    //$("#PostFormFromThread_mediaID").attr('data-mid', mid);
    $("#PostFormFromThread_mediatextarea").val(mid);
    
//    alert(mid);
 }