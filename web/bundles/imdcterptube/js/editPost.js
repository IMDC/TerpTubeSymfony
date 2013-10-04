 var mediaChooser;
 
 $("#selectFiles").click(function(e) {
	mediaChooser = new MediaChooser($("#files"), function(mediaID)
		 	{
		 		//alert(mediaID);
	            setMediaID(mediaID);
	 		}, true);
	mediaChooser.chooseMedia();
 });

 function setMediaID(mid) 
 {
	//$("#ThreadForm_mediaID").attr('data-mid', mid);
	$("#PostEditForm_mediatextarea").val(mid);
	
	//alert(mid);
 }

 /**
  * Adjust the size of a text area
  */
 $(document).ready(function() {
	$('#PostEditForm_content').autosize(); 
 });