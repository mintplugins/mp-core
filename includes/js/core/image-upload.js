jQuery(document).ready(function($){
  
	$(document).on("click", ".custom_media_upload", function(){ 
	
		var button = $(this);
		
		// create and open new file frame
		mp_core_file_frame = wp.media({
			//Title of media manager frame
			title: 'Select an item',
			button: {
				//Button text
				text: 'Use Item'
			},
			//Do not allow multiple files, if you want multiple, set true
			multiple: false,
		});
		
		//callback for selected image
		mp_core_file_frame.on('select', function() {
			
			var selection = mp_core_file_frame.state().get('selection');
			
			selection.map(function(attachment) {
				
				attachment = attachment.toJSON();
				
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
			 
			});
	 
		});
	 
		// open file frame
		mp_core_file_frame.open();
	
	});
	
});