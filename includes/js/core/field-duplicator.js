jQuery(document).ready(function($){
	
	//When we click the "Add New" button
	$(document).on("click", ".mp_duplicate", function(){ 
	
		var theoriginal = $(this).parent().parent();
		var theclone = theoriginal.clone();
		var metabox_container = theoriginal.parent();
		var therepeaterclass = '.'+theoriginal.attr('class');
		var name_number = 0;
				
		//Add the clone after the original
		$(theoriginal).after(theclone);
		
		//Set any of the clone's textbox values to be empty
		theclone.find('.mp_repeater').each(function() {
			this.value = '';		
		});	
		//Hide any of the clones media images
		theclone.find('.custom_media_image').each(function() {
			$(this).css('display', 'none');		
		});	
		
		//Reset the wpColorPicker for each color field in this repeater
		theclone.find('.of-color').each(function() {
			clonecolor = $(this).clone();
			$(this).parent().parent().after(clonecolor);
			$(this).parent().parent().remove();
			clonecolor.wpColorPicker()
		});

		//Reset the names and ids for all fields		
		metabox_container.find(therepeaterclass).each(function(){
			if (name_number != 0){
				$(this).find('.mp_repeater').each(function() {
					this.name= this.name.replace('['+ (name_number-1) +']', '[' + (name_number) +']');
					this.id= this.name.replace('['+ (name_number-1) +']', '[' + (name_number) +']');
					
				});	
			}
			name_number = name_number + 1;
		});
		
		name_repeaters();
		
		return false;   
		    
	});
	
	//When we click the remove button
	$(document).on("click", ".mp_duplicate_remove", function(){ 
	
		var theoriginal = $(this).parent().parent();
		var metabox_container = theoriginal.parent();
		var therepeaterclass = '.'+theoriginal.attr('class');
		var name_number = 0;
		
		if ($(therepeaterclass).length > 1){
			//Remove this repeater if it isn't the only one on the page
			$(theoriginal).remove();
		}
		
		//Reset the names and ids for all fields		
		metabox_container.find(therepeaterclass).each(function(){
			if (name_number == 0){
				$(this).find('.mp_repeater').each(function() {
					this.name= this.name.replace('[1]', '[0]');
					this.id= this.name.replace('[1]', '[0]');
				});	
			}else{
				$(this).find('.mp_repeater').each(function() {
					this.name= this.name.replace('['+ (name_number+1) +']', '[' + (name_number) +']');
					this.id= this.name.replace('['+ (name_number+1) +']', '[' + (name_number) +']');
				});	
			}
			name_number = name_number + 1;
		});
		
		return false;   
		    
	});
	
	//When we roll over the remove button
	$(document).on("hover", ".mp_duplicate_remove", function(){ 
	
		var theoriginal = $(this).parent().parent();
		var metabox_container = theoriginal.parent();
		var therepeaterclass = '.'+theoriginal.attr('class');
		var name_number = 0;
		
		if ($(therepeaterclass).length > 1){
			//Remove this repeater if it isn't the only one on the page
			$(theoriginal).css( 'background-color', '#ffbdbd' );
			$(theoriginal).css( 'border-color', '#ff0000' );
		}
		else{
			$(this).html('Can\'t Remove' );	
		}
		
		
		return false;   
		    
	});
	
	//When we roll out of the remove button
	$(document).on("mouseleave", ".mp_duplicate_remove", function(){ 
	
		var theoriginal = $(this).parent().parent();
		var metabox_container = theoriginal.parent();
		var therepeaterclass = '.'+theoriginal.attr('class');
		var name_number = 0;
		
		if ($(therepeaterclass).length > 1){
			//Remove this repeater if it isn't the only one on the page
			$(theoriginal).css( 'background-color', '');
			$(theoriginal).css( 'border-color', '' );
		}		
		
		$(this).html('Remove' );	
		
		return false;   
		    
	});
	
	//When we click on the toggle for this repeater - hide or show this repeater
	$(document).on("click", '.mp_repeater_handlediv', function(){
		
		var theoriginal = $(this).parent();
		
		var height = $(theoriginal).css('height');
		
		//Hide
		if ( height != '29px' ){
			$(theoriginal).css( 'height', '29px');
		}
		//Show
		else{
			$(theoriginal).css( 'height', 'inherit');
		}
		
	});
	
	function name_repeaters(){
		
		$('.repeater_container li').each(function(index) {
			
			var thetitle = $(this).find('> .mp_field strong').html();
			var thevalue = $(this).find('> .mp_field > input').val();
			
			if ( thevalue ){		
				$(this).find( '> .mp_drag > span').html(thetitle + ': ' + thevalue);
			}
			else{
				$(this).find( '> .mp_drag > span').html( 'Enter info:' );
			}
		
		});
			
	}
	
	name_repeaters();
		
	$('.repeater_container li > .mp_field input').on('keyup click blur focus change paste', function() {
		name_repeaters();
	});
	
});