jQuery(document).ready(function($){
	
	var mp_ajax_popup;
	
	var mp_ajax_popup_positionofpopup_loop;
	
	$( document ).on('mp_ajax_popup_event', function( event, data ) {
		
		//Set the z-index of this parent to be higher than it's friends around it for the time being while the popup is active
		$(this).css('z-index', '999999' );
		
		var thisdiv = data.trigger_element;
		
		//Append the popup right away - but just showing a blown up version of what we rolledover
		popup = $('<div class="mp-ajax-popup" display:inline-block;">' + data.content + '</div>').prependTo(thisdiv);
		
		var loop_counter = 0;
		
		//Loop
		mp_ajax_popup_positionofpopup_loop = setInterval(function() {	
			
			//Increment counter
			loop_counter = loop_counter + 1;
			
			//Clear loop after 20 times
			if ( loop_counter > 20 ){
				clearInterval(mp_ajax_popup_positionofpopup_loop);
			}
		
			//Width of the popup
			popup_width = popup.outerWidth();			
			popup_height = popup.outerHeight();	
			
			console.log(popup_width);
			console.log(popup_height);
									
			var thisdiv_position = thisdiv.offset();
			var holder_position = thisdiv.parent().parent().parent().offset();
			
			//X Positions for the Article div
			xpos_left_trigger_element = thisdiv_position.left;		
			xpos_right_trigger_element = xpos_left_trigger_element + thisdiv.outerWidth();		
			
			//Y Positions for the Article div
			ypos_top_trigger_element = thisdiv_position.top;		
			ypos_bottom_trigger_element = ypos_top_trigger_element + thisdiv.outerHeight();		
			
			//X Positions for the Holder Parent div
			xpos_left_holder = holder_position.left;
			xpos_right_holder = xpos_left_holder + thisdiv.parent().parent().parent().outerWidth();
			
			//If we are on at least loop #5 - position the popup						
			if (loop_counter > 5){	
																												
				//Find appropriate X Pos
				//If there is enough space to the right 
				if ( (xpos_right_trigger_element + popup_width) < xpos_right_holder ){
					//Position popup to the right 
					popup.css({
						left: thisdiv.outerWidth(),
						visibility: 'visible',
						opacity:1
					});
					
					
				}
				//If there is enough space to the left 
				else if ( (xpos_left_trigger_element - popup_width) > xpos_left_holder ){
												
					//Position popup to the left 
					popup.css({
						left: -popup_width,
						visibility: 'visible',
						opacity:1
					});
				}
				else{
					//Position popup directly over
					popup.css({
						left: ((thisdiv.outerWidth()) / 2) - ( popup_width / 2 ),
						visibility: 'visible',
						opacity:1
					});
				}
				
				//Find appropriate Y Pos
				//If this entire article is in view
				if ( ypos_bottom_trigger_element < window.pageYOffset + $(window).outerHeight() && ypos_top_trigger_element > window.pageYOffset ){
					
					//Height of popup in half
					var half_height_popup = popup.outerHeight() / 2;
					
					//Position popup in the vertical middle of the article
					popup.css({
						top: ((thisdiv.outerHeight()) / 2) - ( half_height_popup ),
						visibility: 'visible',
						opacity:1
					});
				}
				//If the top of the article is cut-off
				else if( ypos_bottom_trigger_element < window.pageYOffset + $(window).outerHeight() && ypos_top_trigger_element < window.pageYOffset ){
					
					//Position popup below
					popup.css({
						top: (thisdiv.outerHeight()),
						visibility: 'visible',
						opacity:1
					});
				}
				//If the bottom of the article is cut-off
				else if( ypos_bottom_trigger_element > window.pageYOffset + $(window).outerHeight() && ypos_top_trigger_element > window.pageYOffset ){
					
					//Position popup above
					popup.css({
						top: -popup_height,
						visibility: 'visible',
						opacity:1
					});
				}
			}
		}, 25);			
													
	});
	
	var timeout;
    
	$( document ).on('mouseenter', '[mp_ajax_popup]', function(e) {
		
        var self = $(this);
        clearTimeout(timeout);
        timeout = setTimeout(function() {
			
			//Create the array of data we pass to the mp_ajax_popup event
			var data = {
				trigger_element: self,
				content: self.attr('mp_ajax_popup')
			};
			
			//Trigger the mp_ajax_popup_event event
            self.trigger('mp_ajax_popup_event', data);
			
        }, 900);
    });
	
    $(document).on('mouseleave', '[mp_ajax_popup]', function() {		

		clearTimeout(timeout);
		
		clearInterval(mp_ajax_popup_positionofpopup_loop);
		
		//Remove the z-index we added to this archive element
		$(this).css('z-index', '' );
		
        if (mp_ajax_popup) {
            mp_ajax_popup.abort();
            mp_ajax_popup = null;
        }
        $( 'body' ).find( ".mp-ajax-popup" ).detach();
		$(this).find('.mp-ajax-popup-loading').remove();
		
		
    });
	
});