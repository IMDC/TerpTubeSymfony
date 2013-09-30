$(document).ready(function() {

	$("#post-comment-button").click(function() {
		$("#comment-form-wrap").toggle();
		$(this).toggle();
	});
	
	$("a#post-comment-submit-button").click(function() {
		$("#PostFormFromThread_submit").click();
	});
	
	$("#cancelButton").click(function() {
		$("#comment-form-wrap").toggle();
		$("#post-comment-button").show();
	});
	
    $("a#post-comment-delete").click(function(event) {
    	
    	var p_id = $(this).data("pid");
    	var $theDeleteLink = $(this);
    	
    	// prevent the click from scrolling the page
    	event.preventDefault();
    	
    	//alert('hi');
    	
    	// fade out the comment in question        
        $(this).parents("[data-pid='" + p_id + "']").eq(0).fadeTo('medium', 0.5);
		
        // show a dialog box asking for confirmation of delete
        $( "#dialog-confirm" ).dialog({
            resizeable: false,
            height: 275,
            modal: true,
            buttons: {
                "Yes": function() {
                    $.ajax({
            			url : Routing.generate('imdc_post_delete_specific_ajax', {pid: p_id}),
            			type : "POST",
            			contentType : "application/x-www-form-urlencoded",
            			data :
            			{
            				pid : p_id
            			},
            			success : function(data)
            			{
            				console.log("success");
            				console.log(data);
            				$theDeleteLink.parents("[data-pid='" + p_id + "']").eq(0).fadeTo('slow', 0.0).remove();
                            //delete timeline region
            				// FIXME: get correct delete function from Martin
                            removeComment(commentID);
            			},
            			error : function(request)
            			{
            				console.log(request);
            				alert(request.statusText);
            			}
            		});
                    
                    $( this ).dialog( "close" );
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                    $theDeleteLink.parents("[data-cid='" + commentID + "']").eq(0).fadeTo('slow', 1.0);
                }
            }
        });
    });
    
    /**
     * This snippet of code looks for a post id named anchor in the url and scrolls
     * the list of posts on the right of the page to the comment BEFORE the one in
     * question (otherwise the post you're looking for is cut off vertically)
     */
    var $anchorname = window.location.hash.substring(1);
    if ($anchorname) {
    	$newanchor = parseInt($anchorname) - 1;
    	$("div#thread-reply-container").animate({
    		scrollTop: $(this).find("div#post-" + $newanchor).offset().top
    	}, 200);
    }
    
    
});

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