jQuery(document).ready(function($){	
	$('#the-list').sortable({
        //When item is dropped
	    update: function(event, ui) {
			
			//Make counter worth the first value on the page
			counter = parseInt($('.mp_menu_order').first().val());
			
			$(this).find('.mp_menu_order').each(function(){
				
				//Make counter worth the least value on the page
				if ( counter > parseInt($(this).val())){
					counter = parseInt($(this).val());	
				}
				
			});
			
			$(this).find('.mp_menu_order').each(function(){
				$(this).val(counter);
				counter = counter+1;
			});
		
			
			//Submit the form
		  	$('#posts-filter').submit();
			
        },
		handle: '.menu-order-drag-button'
	});

});