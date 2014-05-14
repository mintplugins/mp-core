jQuery(document).ready(function($){
	
	/**
	 * Handle "Repeaters"
	 */
	 
	//When we click the "Add New" button
	$(document).on("click", ".mp_duplicate", function(){ 
		
		//Store original div in variable
		var theoriginal = $(this).parent().parent();
		
		//Other variables
		var metabox_container = theoriginal.parent();
		var therepeaterclass = '.'+theoriginal.attr('class').split(' ')[0];
		var name_number = 0;
		
		//"Action Hook" trigger before repeater is cloned
		$(window).trigger("mp_core_duplicate_repeater_before", theoriginal );
		
		//TinyMCE fix - temporarily removes it from each TinyMCE area in this repeater
		metabox_container.find('.wp-editor-area').each(function(){
			tinyMCE.execCommand( 'mceRemoveEditor', true, $(this).attr('id') );
		});
		
		//Create clone of original
		var theclone = theoriginal.clone();
		
		//Set any of the clone's textbox values to be empty
		theclone.find('.mp_repeater').each(function() {
			this.value = '';		
		});	
		
		//Hide any of the clones media images
		theclone.find('.custom_media_image').each(function() {
			$(this).css('display', 'none');		
		});	
		
		//Hide any of the clones icon fonts
		theclone.find('.mp_font_icon_thumbnail').each(function() {
			$(this).css('display', 'none');		
		});	
		
		//Reset the wpColorPicker for each color field in this repeater
		theclone.find('.of-color').each(function() {
			clonecolor = $(this).clone();
			$(this).parent().parent().after(clonecolor);
			$(this).parent().parent().remove();
			clonecolor.wpColorPicker()
		});
				
		//Add the clone after the original
		$(theoriginal).after(theclone);
	
		//Reset the names, classes, hrefs, and ids for all fields		
		metabox_container.find(therepeaterclass).each(function(){
			if (name_number == 0){
				
				//Loop through all elements in this repeater and rename
				$(this).find('*').each(function() {
					//Re-initialize tinymce for each TInyMCE area in this repeater
					if ( this.className == 'wp-editor-area') {
						tinyMCE.execCommand( 'mceRemoveEditor', true, this.id );
						tinyMCE.execCommand( 'mceAddEditor', true, this.id );
					}
				});	
			}
			else{
				
				//Loop through all elements in this repeater and rename
				$(this).find('*').each(function() {
					if ( this.name ){
						this.name = this.name.replace('['+ (name_number-1) +']', '[' + (name_number) +']');
					}
					
					if ( this.id ){
						this.id= this.id.replace('AAAAA'+ (name_number-1) +'BBBBB', 'AAAAA' + (name_number) +'BBBBB');
					}
					
					if ( this.className ){
						this.className = this.className.replace('AAAAA'+ (name_number-1) +'BBBBB', 'AAAAA' + (name_number) +'BBBBB');
					}
					if ( this.href ){
						this.href = this.href.replace('AAAAA'+ (name_number-1) +'BBBBB', 'AAAAA' + (name_number) +'BBBBB');
					}
					
					//Re-initialize tinymce for each TInyMCE area in this repeater
					if ( this.className == 'wp-editor-area') {
						tinyMCE.execCommand( 'mceRemoveEditor', true, this.id );
						tinyMCE.execCommand( 'mceAddEditor', true, this.id );
					}
					
				});	
			}
			name_number = name_number + 1;
		});
		
		//Reset the textarea for each wp_editor/tinymce field in this repeater
		theclone.find('.wp-editor-area').each(function() {
			$(this).html("");
		});
		
		//Reset the body in the iframe for each wp_editor/tinymce field in this repeater
		theclone.find('.mce-container > iframe').contents().find('body').each(function() {
			$(this).html("");
		});
		
		name_repeaters();
		
		//"Action Hook" trigger after repeater is cloned
		$(window).trigger("mp_core_duplicate_repeater_after", [ theoriginal, theclone ] );
		
		return false;   
		    
	});
	
	//When we roll over the duplicate button
	$(document).on("hover", ".mp_duplicate", function(){ 
	
		var theoriginal = $(this).parent().parent();
		var metabox_container = theoriginal.parent();

		$(theoriginal).css( 'background-color', '#f7fff7' );
		$(theoriginal).css( 'border-color', '#008d00' );
		
		return false;   
		    
	});
	
	//When we roll out of the duplicate button
	$(document).on("mouseleave", ".mp_duplicate", function(){ 
	
		var theoriginal = $(this).parent().parent();
		var metabox_container = theoriginal.parent();
					
		$(theoriginal).css( 'background-color', '#fefff8');
		$(theoriginal).css( 'border-color', '#c2c59e' );
				
		return false;   
		    
	});
	
	//When we click the remove button
	$(document).on("click", ".mp_duplicate_remove", function(){ 
	
		var theoriginal = $(this).parent().parent();		
		var metabox_container = theoriginal.parent();
		var therepeaterclass = '.'+theoriginal.attr('class').split(' ')[0];
		var name_number = 0;
		
		//Remove this repeater if it isn't the only one on the page
		if ($(therepeaterclass).length > 1){
			$(theoriginal).remove();
		}
		
		//Reset the names and ids for all fields		
		metabox_container.find(therepeaterclass).each(function(){
			if (name_number == 0){
				$(this).find('*').each(function() {
					if ( this.name ){
						this.name = this.name.replace('[1]', '[0]');
					}
					
					//tinyMCE.execCommand( 'mceRemoveEditor', true, $(this).attr('id') );
					if ( this.id ){
						this.id= this.id.replace('AAAAA1BBBBB', 'AAAAA0BBBBB');
					}
					//tinyMCE.execCommand( 'mceAddEditor', true, $(this).attr('id') );
					
					if ( this.className ){
						this.className = this.className.replace('AAAAA1BBBBB', 'AAAAA0BBBBB');
					}
					if ( this.href ){
						this.href = this.href.replace('AAAAA1BBBBB', 'AAAAA0BBBBB');
					}
					//tinyMCE.execCommand( 'mceRemoveEditor', true, $(this).attr('id') );
				});	
			}else{
				$(this).find('*').each(function() {
					if ( this.name ){
						this.name = this.name.replace('['+ (name_number+1) +']', '[' + (name_number) +']');
					}
					
					//tinyMCE.execCommand( 'mceRemoveEditor', true, $(this).attr('id') );
					if ( this.id ){
						this.id= this.id.replace('AAAAA'+ (name_number+1) +'BBBBB', 'AAAAA' + (name_number) +'BBBBB');
					}
					//tinyMCE.execCommand( 'mceAddEditor', true, $(this).attr('id') );
					
					if ( this.className ){
						this.className = this.className.replace('AAAAA'+ (name_number+1) +'BBBBB', 'AAAAA' + (name_number) +'BBBBB');
					}
					if ( this.href ){
						this.href = this.href.replace('AAAAA'+ (name_number+1) +'BBBBB', 'AAAAA' + (name_number) +'BBBBB');
					}
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
		var therepeaterclass = '.'+theoriginal.attr('class').split(' ')[0];
		var name_number = 0;
		
		if ($(therepeaterclass).length > 1){
			//Remove this repeater if it isn't the only one on the page
			$(theoriginal).css( 'background-color', '#ffbdbd' );
			$(theoriginal).css( 'border-color', '#ff0000' );
		}
		else{
			$(this).html( mp_core_metabox_js.cantremove );	
		}
		
		return false;   
		    
	});
	
	//When we roll out of the remove button
	$(document).on("mouseleave", ".mp_duplicate_remove", function(){ 
	
		var theoriginal = $(this).parent().parent();
		var metabox_container = theoriginal.parent();
		var therepeaterclass = '.'+theoriginal.attr('class').split(' ')[0];
		var name_number = 0;
		
		if ($(therepeaterclass).length > 1){
			//Remove this repeater if it isn't the only one on the page
			$(theoriginal).css( 'background-color', '#fefff8');
			$(theoriginal).css( 'border-color', '#c2c59e' );
		}		
		
		$(this).html('Remove' );	
		
		return false;   
		    
	});
	
	//When we roll over this repeater
	$(document).on("hover", ".repeater_container li", function(){ 
	
		$(this).css( 'background-color', '#fefff8' );
		$(this).css( 'border-color', '#c2c59e' );
		
		return false;   
		    
	});
	
	//When we roll out of the remove button
	$(document).on("mouseleave", ".repeater_container li", function(){ 
		
		$(this).css( 'background-color', '' );
		$(this).css( 'border-color', '' );
		
		return false;   
		    
	});
	
	//On load, if only 1 repeater, show it. If more than 1, leave them minimized
	$('.repeater_container').each(function(){
			var number_of_li = $(this).find('li').length;
			if ( number_of_li == 1 ){
				$(this).find('li').css( 'height', 'inherit');
			}
	});
	
	//When we click on the toggle for this repeater - hide or show this repeater
	$(document).on("click", '.repeater_container .hndle, .repeater_container .handlediv, .postbox h3, .postbox .handlediv', function(){
		
		var theoriginal = $(this).parent();
		
		var closed = theoriginal.hasClass( "closed" );

													
		//This is closed so open it 
		if ( !closed ){
			
			theoriginal.removeClass("closed");
			
			//reveresed for dynamically created metaboxes...hopefully removed soon: https://core.trac.wordpress.org/ticket/27996
			theoriginal.find('.inside').css( 'display', 'block');
			
			theoriginal.css('height', 'inherit');
		}
		//This is open so close it
		else{

			theoriginal.addClass("closed");
			
			//reveresed for dynamically created metaboxes...hopefully removed soon: https://core.trac.wordpress.org/ticket/27996
			theoriginal.find('.inside').css( 'display', 'none');
			
			theoriginal.css('height', '35px');
		}
			
	});
	
	//Put the title of this repeater at the top of it based on what is inside of it's first field
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
	
	//Apply names of repeater metaboxes on ready
	name_repeaters();
	
	//Apply names of repeater metaboxes when typing in the first field	
	$( document ).on('keyup click blur focus change paste', '.repeater_container li > .mp_field input', function() {
		name_repeaters();
	});
	
	//Handle dragging and dropping of repeaters to re-order them. Uses the "sortable" jquery plugin
	$('.repeater_container').sortable({
		handle: '.mp_drag.hndle',
		axis: 'y',
		opacity: 0.5,
		
		start: function(e, ui){
			
			//Remove control for Tiny MCE
			$(this).find('.wp-editor-area').each(function(){
				tinyMCE.execCommand( 'mceRemoveEditor', true, $(this).attr('id') );
			});
			
		},
		update: function(e,ui) {
			
			name_number = 0;
						
			//Loop through all elements in this repeater and rename
			$(this).children().each(function() {
										
				$(this).find('*').each(function() {
									
						if ( this.name ){
							this.name = this.name.replace(/\[[0-9]\]/g, '[' + (name_number) +']');
						}
						
						if ( this.id ){
							this.id= this.id.replace(/\AAAAA[0-9]\BBBBB/g, 'AAAAA' + (name_number) +'BBBBB');
						}
						
						if ( this.className ){
							this.className = this.className.replace(/\AAAAA[0-9]\BBBBB/g, 'AAAAA' + (name_number) +'BBBBB');
						}
						if ( this.href ){
							this.href = this.href.replace(/\AAAAA[0-9]\BBBBB/g, 'AAAAA' + (name_number) +'BBBBB');
						}
						
						//Re-initialize tinymce for each TInyMCE area in this repeater
						if ( this.className == 'wp-editor-area') {
							tinyMCE.execCommand( 'mceRemoveEditor', true, this.id );
							tinyMCE.execCommand( 'mceAddEditor', true, this.id );
						}
				
				});
				name_number = name_number + 1;		
			});	
			
			
			//Submit the form
		  	//$('#post').submit();
		}
	});
	
	/**
	 * Icon Font Picker
	 */
	 
	//When Icon Font Picker item is clicked, put it's value in the field and close the thickbox
	$( 'body' ).on( 'click', '.mp_iconfontpicker_item', function(event){
		
		event.preventDefault();
		
		//Get the field ID of the input 
		var field_id = $(this).parent().attr('class');
		
		//Put the icon code selected into the field ID input field
		$( '#'+field_id ).val($(this).find(' > div > div').html());
		
		//Show the icon in the thumbnail area preview
		$( '.mp_field_' + field_id + ' .mp_font_icon_thumbnail > div' ).attr( 'class', $(this).find(' > div > div').html() );
		$( '.mp_field_' + field_id + ' .mp_font_icon_thumbnail' ).css( 'display', 'inline-block' );
		
		//Close the thickbox
		tb_remove();

	});
	
	
	/**
	 * Required Fields - make them red if they are empty
	 */
	 
	//Loop through all required fields
	$('.mp_required').each(function(){
		
		//If this field has a valuein it, make it white
		if( $(this).val() ){
								
			$(this).css('background-color', '#FFFFFF');	

		}
		
		//When we click on or away from this field
		$(this).on('blur', function() {
			
			//If there is a value
			if( $(this).val() ){
								
				$(this).removeAttr( 'style' );
				
				$(this).css('background-color', '#FFFFFF');	
			//If there isn't a value
			}else{
				
				//Make it red
				$(this).css('background-color', '#FFC8C8');	
				$(this).css('display', 'inline-block');
				
			}
			
		});
		
	});
	
	//When the publish button is clicked
	$("#publish").on('click', function(event){
		
		//Make sure all our required fields are visible
		$('.mp_required').each(function(){
			if( !$(this).val() ){
				$(this).css('display', 'inline-block');	
			}
		});
		
	});
	
	//Loop through all required fields
	$('.mp_required').each(function(){
		
		//If this field has a valuein it, make it white
		if( $(this).val() ){
								
			$(this).css('background-color', '#FFFFFF');	

		}
		
		//When we click on or away from this field
		$(this).on('blur', function() {
			
			//If there is a value
			if( $(this).val() ){
								
				$(this).removeAttr( 'style' );
				
				$(this).css('background-color', '#FFFFFF');	
			//If there isn't a value
			}else{
				
				//Make it red
				$(this).css('background-color', '#FFC8C8');	
				$(this).css('display', 'inline-block');
				
			}
			
		});
		
	});
	
	//When the publish button is clicked
	$("#publish").on('click', function(event){
		
		//Make sure all our required fields are visible
		$('.mp_required').each(function(){
			if( !$(this).val() ){
				$(this).css('display', 'inline-block');	
			}
		});
		
	});
	
	//When any metabox "help" button has been clicked
	$( '.mp_core_help a').on('click', function(event){
		
		var this_help_button = $(this);
		
		var help_url = this_help_button.attr('href');
		var help_type = this_help_button.attr('class').replace('mp_core_help_', '');
		
		if (help_type != 'directory'){
			
			event.preventDefault();
			
			//If this button doesn't say "Hide" or "Loading"
			if ( this_help_button.html() != mp_core_metabox_js.loading && this_help_button.html() != mp_core_metabox_js.hide ){
			
				this_help_button.attr('mp_core_help_original_text', this_help_button.html() );
				
				//Reset Help Text to "Hide" (localized in metabox class php)
				this_help_button.html(mp_core_metabox_js.loading);
			
				var postData = {
					action: 'mp_core_help_content_ajax',
					help_href: help_url,
					help_type: help_type 
				};
								
				$.ajax({
					type: "POST",
					data: postData,
					url: 'admin-ajax.php',
					success: function (response) {
						var help_ajax = $('<div class="mp_core_help_content_ajax mp_core_help_type_' + help_type + '">' + response + '</div><div style="clear: both;"></div>').appendTo(this_help_button.parent().parent().parent().parent());	
						this_help_button.html(mp_core_metabox_js.hide);					
					}
				}).fail(function (data) {
					console.log(data);
				});
			//If the button DOES say "Hide" or "Loading"	
			}else{
				
				this_help_button.parent().parent().parent().parent().find('.mp_core_help_content_ajax').detach();
				
				//Reset Help Text to "Hide" (localized in metabox class php)
				this_help_button.html(this_help_button.attr('mp_core_help_original_text'));
			}
		}
				
	});
	
	//Show range value beside range inputs
	$(function() {
		var el, newPoint, newPlace, offset;
		
		// Select all range inputs, watch for change
		$("input[type='range']").change(function() {
			
			// Cache this for efficiency
			el = $(this);
									
			// Set value
			el.next("output").text(el.val());
		})
		// Fake a change to position bubble at page load
		.trigger('change');
	});
	
	//Showhider function which shows and hides fields based on their parent showhider
	$( ".mp_core_showhider_button.closed" ).on('click', function(event){
			event.preventDefault;
			
			var this_button = $(this);
			
			//Get name of showhider group
			var showhidergroup = this_button.attr('showhidergroup');
			
			//Show fields in this showhider			
			$( '[showhider=' + showhidergroup + ']').css('display', 'block');
			
			//After showhider is open, update the classes
			setTimeout(function() {
				this_button.removeClass('closed').addClass('open');
			}, 300);
			
						
	});
	$(document).on('click', '.mp_core_showhider_button.open', function(event){
	//$( ".mp_core_showhider_button.open" ).on('click', function(event){
			event.preventDefault;
						
			var this_button = $(this);
						
			//Get name of showhider group
			var showhidergroup = this_button.attr('showhidergroup');
			
			//Hide fields in this showhider					
			$( '[showhider=' + showhidergroup + ']').css('display', 'none');
			
			//After showhider is open, update the classes
			setTimeout(function() {
				this_button.removeClass('open').addClass('closed');
			}, 300);						
	});

});