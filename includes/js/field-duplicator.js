jQuery(document).ready(function($){
	
	$(document).on("click", ".mp_duplicate", function(){ 
	
		var theoriginal = $(this).parent();
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
		
		return false;   
		    
	});
	
	$(document).on("click", ".mp_duplicate_remove", function(){ 
	
		var theoriginal = $(this).parent();
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
	
});