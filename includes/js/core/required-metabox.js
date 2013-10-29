jQuery(document).ready(function($){
		
	$('.mp_required').each(function(){
		
		//If this field has a valuein it, make it white
		if( $(this).val() ){
								
			$(this).css('background-color', '#FFFFFF');	

		}
		
		//When we click on or away from this field
		$(this).on('blur', function() {
					
			if( $(this).val() ){
								
				$(this).removeAttr( 'style' );
				
				$(this).css('background-color', '#FFFFFF');	
	
			}else{
				
				$(this).css('background-color', '#FFC8C8');	
				$(this).css('display', 'inline-block');
				
			}
			
		});
		
	});
	
	$("#publish").on('click', function(event){
					
		$('.mp_required').each(function(){
			if( !$(this).val() ){
				$(this).css('display', 'inline-block');	
			}
		});
		
	});
	
});