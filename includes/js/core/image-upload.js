jQuery(document).ready(function($){
  
	$(document).on("click", ".custom_media_upload", function(){ 
	
		var send_attachment_bkp = wp.media.editor.send.attachment;
		var button = $(this);
	
		wp.media.editor.send.attachment = function(props, attachment) {
			
			//if this is an image, display the thumbnail above the upload button
			var ext = attachment.url.split('.').pop();
			if (ext == 'png' || ext == 'jpg'){
				$(button).parent().next().attr('src', attachment.url);
				$(button).parent().next().css('display', 'inline-block');
			}else{
				$(button).next().next().css('display', 'none');
			}
			
			//put the url of the file in the field just above the button
			$(button).prev().val(attachment.url);
	
			//Send the attachment
			wp.media.editor.send.attachment = send_attachment_bkp;
		}
	
		//Open the media editor
		wp.media.editor.open(button);
	
		return false;       
	});
	
});