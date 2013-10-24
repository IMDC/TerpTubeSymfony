$(document).ready(function() {
	var url = $("#messageRead").attr('href');
	var m_id = $("#messageRead").data("mid");
	$.post(url, {
		msgId : m_id
	}, function(data) {
		// the response is in the data variable
		
		if (data.responseCode == 200) {
			$('#readOutput').html(data.feedback);
			$('#readOutput').css('color', '#D38585');
		}
		else if (data.responseCode == 400) { // bad request
			$('#readOutput').html(data.feedback);
			$('#readOutput').css('color', 'red');
		}
		else {
			alert('An unexpected error occured');
			$('#output').html(data);
		}
	});
	return false;
});

