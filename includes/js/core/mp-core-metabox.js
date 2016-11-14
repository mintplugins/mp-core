jQuery(document).ready(function($){

	if ( $(document).find( '[mp_default_value]' ).length == 0 ){
		var mp_core_defaults_checked = true;
	}else{
		var mp_core_defaults_checked = false;
	}
	//Check to see if a field is different than its default value.
	$(document).on( 'submit', '#post', function( event ) {

		//If we have checked the defaults of each post already
		if ( mp_core_defaults_checked === true ){
			$(document).trigger('mp_core_post_submitted');
		}
		else{

			//Prevent the form from being submitted just yet
			event.preventDefault();

			$(document).find( '[mp_default_value]' ).each( function(){

				//If this field WAS something different than the default, and now its the default again
				if( $(this).attr( 'mp_default_value' ) == $(this).val() && $(this).attr( 'mp_saved_value' ) != $(this).val() ){
					//Let this field stay because it WAS something different than the default, and now its the default again - so it needs to be resaved
				}
				//If this field's value matches its default value OR it matches its saved value, don't submit it - it's a waste to submit it.
				else if ( $(this).attr( 'mp_saved_value' ) == $(this).val() || $(this).attr( 'mp_default_value' ) == $(this).val() ){
					$(this).remove();
				}

			});

			//Set our flag to true so we don't repeat this and create an endless loop
			mp_core_defaults_checked = true;

			//re-submit the form.
			$(document).find('#post').submit();
		}

	});

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
			//Also remove any "checked" attributes from checkboxes as the value is now empty
			$(this).removeAttr('checked');
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
					if ( this.hasAttribute( 'mp_conditional_field_id' ) ){
						this.setAttribute( 'mp_conditional_field_id', this.getAttribute( 'mp_conditional_field_id' ).replace('['+ (name_number-1) +']', '[' + (name_number) +']') );
					}
					if ( this.hasAttribute( 'showhider' ) ){
						this.setAttribute( 'showhider', this.getAttribute( 'showhider' ).replace('AAAAA'+ (name_number-1) +'BBBBB', 'AAAAA' + (name_number) +'BBBBB') );
					}
					if ( this.hasAttribute( 'showhidergroup' ) ){
						this.setAttribute( 'showhidergroup', this.getAttribute( 'showhidergroup' ).replace('AAAAA'+ (name_number-1) +'BBBBB', 'AAAAA' + (name_number) +'BBBBB') );
					}
					if ( $(this).attr( 'data-wp-editor-id' ) ){
						$(this).attr( 'data-wp-editor-id', $(this).attr( 'data-wp-editor-id').replace('AAAAA'+ (name_number-1) +'BBBBB', 'AAAAA' + (name_number) +'BBBBB') );
					}

				});
			}
			name_number = name_number + 1;
		});

		name_repeaters();

		//Reset all the wp_editors on the page
		mp_core_reset_all_wp_editors();

		//Reset the textarea for each wp_editor/tinymce field in this repeater
		theclone.find('.wp-editor-area').each(function() {
			$(this).html("");
		});

		//Reset the body in the iframe for each wp_editor/tinymce field in this repeater
		theclone.find('.mce-container > iframe').contents().find('body').each(function() {
			$(this).html("");
		});

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

					if ( this.className ){
						this.className = this.className.replace('AAAAA1BBBBB', 'AAAAA0BBBBB');
					}
					if ( this.href ){
						this.href = this.href.replace('AAAAA1BBBBB', 'AAAAA0BBBBB');
					}
					if ( this.hasAttribute( 'mp_conditional_field_id' ) ){
						this.setAttribute( 'mp_conditional_field_id', this.getAttribute( 'mp_conditional_field_id' ).replace('[1]', '[0]') );
					}
					if ( $(this).attr( 'data-wp-editor-id' ) ){
						$(this).attr( 'data-wp-editor-id', $(this).attr( 'data-wp-editor-id').replace('[1]', '[0]') );
					}

				});
			}else{
				$(this).find('*').each(function() {
					if ( this.name ){
						this.name = this.name.replace('['+ (name_number+1) +']', '[' + (name_number) +']');
					}

					if ( this.id ){
						this.id= this.id.replace('AAAAA'+ (name_number+1) +'BBBBB', 'AAAAA' + (name_number) +'BBBBB');
					}

					if ( this.className ){
						this.className = this.className.replace('AAAAA'+ (name_number+1) +'BBBBB', 'AAAAA' + (name_number) +'BBBBB');
					}
					if ( this.href ){
						this.href = this.href.replace('AAAAA'+ (name_number+1) +'BBBBB', 'AAAAA' + (name_number) +'BBBBB');
					}
					if ( this.hasAttribute( 'mp_conditional_field_id' ) ){
						this.setAttribute( 'mp_conditional_field_id', this.getAttribute( 'mp_conditional_field_id' ).replace('['+ (name_number+1) +']', '[' + (name_number) +']') );
					}
					if ( $(this).attr( 'data-wp-editor-id' ) ){
						$(this).attr( 'data-wp-editor-id', $(this).attr( 'data-wp-editor-id').replace('['+ (name_number+1) +']', '[' + (name_number) +']') );
					}

				});
			}
			name_number = name_number + 1;
		});

		mp_core_reset_all_wp_editors();

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
	$(document).on("click", '.repeater_container .hndle, .repeater_container .handlediv', function(){

		var theoriginal = $(this).parent();

		var closed = theoriginal.hasClass( "closed" );

		//This is closed so open it
		if ( closed ){

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

		//Loop through each repeat
		$('.repeater_container li').each(function(index) {

			//If we have a title field for this repeater set
			thetitle = $(this).find('> .repeatertitle strong').html();

			if ( thetitle ){
				$(this).find( '> .mp_drag > .mp-core-repeater-title').html(thetitle);
			}

			//Reset the description for this repeater
			thedescription = '';
			pre_description = '';

			//Loop through each field in this repeat
			$(this).find('.mp_field').each(function(index){

				//If this field is a wp_editor, make it first
				if ( $(this).find('.wp-editor-area').text() ){
					var wp_editor_text = $(this).find('.wp-editor-area').text();

					pre_description += $(wp_editor_text).text() + ' | ';
				}


				if ( $(this).find('> input').val() ){

					//Add the title of the first field in the repeater
					thedescription += $(this).find('> .mp_title strong').html() + ': ';

					//Add the value of each field
					thedescription += $(this).find('> input').val() + ', ';
				}
				if ( $(this).find('> select option:selected').text() ){

					//Add the title of the first field in the repeater
					thedescription += $(this).find('> .mp_title strong').html() + ': ';

					//Add the value of each field
					thedescription += $(this).find('>select option:selected').text() + ', ';
				}

			});

			thedescription = pre_description + thedescription;

			$(this).find( '> .mp_drag > .mp-core-repeater-values-description').html(thedescription);

		});

	}

	//Apply names of repeater metaboxes on ready
	$( window ).on( 'load', function(){
		name_repeaters();
	});
	$( document ).ajaxComplete( function(){
		name_repeaters();
	});

	//Apply names of repeater metaboxes when typing in the first field
	$( document ).on('keyup click blur focus change paste', '.repeater_container li > .mp_field input', function() {
		name_repeaters();
	});

	//Handle dragging and dropping of repeaters to re-order them. Uses the "sortable" jquery plugin
	function mp_core_sortable_repeaters(){
		$('.repeater_container').sortable({
			handle: '.mp_drag.hndle',
			axis: 'y',
			opacity: 0.5,

			start: function(e, ui){

				//Switch all editors to tinymce mode before re-ordering.
				$( '.switch-tmce' ).trigger( 'click' );

				$(document).find('.wp-editor-area').each(function() {

					tinyMCE.execCommand( 'mceRemoveEditor', true, this.id );

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
								//For some reason we need 4 B's to get 5. I'm not good with regex - but this works.
								this.id= this.id.replace(/\AAAAA[0-9]\BBBBB/g, 'AAAAA' + (name_number) +'BBBB');
							}

							if ( this.className ){
								//For some reason we need 4 B's to get 5. I'm not good with regex - but this works.
								this.className = this.className.replace(/\AAAAA[0-9]\BBBBB/g, 'AAAAA' + (name_number) +'BBBB');
							}
							if ( this.href ){
								//For some reason we need 4 B's to get 5. I'm not good with regex - but this works.
								this.href = this.href.replace(/\AAAAA[0-9]\BBBBB/g, 'AAAAA' + (name_number) +'BBBB');
							}
							if ( $(this).attr( 'data-wp-editor-id' ) ){
								//For some reason we need 4 B's to get 5. I'm not good with regex - but this works.
								$(this).attr( 'data-wp-editor-id', $(this).attr( 'data-wp-editor-id').replace(/\AAAAA[0-9]\BBBBB/g, 'AAAAA' + (name_number) +'BBBB') );
							}

					});
					name_number = name_number + 1;
				});

				mp_core_reset_all_wp_editors();

				//Submit the form
				//$('#post').submit();
			}
		});
	}
	mp_core_sortable_repeaters();
	$(document).ajaxComplete( function() {
		mp_core_sortable_repeaters();
	});

	/**
	 * For metaboxes set to "metabox_load_content_when_open", load their contents once the metabox is "Opened" by the user.
	 */
	 $( document ).on( 'click', '.postbox .handlediv, .postbox .hndle', function( event ){

		var content_placeholder = $(this).parent().find( '.mp_core_metabox_ajax_placeholder');
		var metabox_id = content_placeholder.attr( 'mp_core_metabox_id' ) ? content_placeholder.attr( 'mp_core_metabox_id' ) : false;
		var post_id = content_placeholder.attr( 'mp_core_post_id' ) ? content_placeholder.attr( 'mp_core_post_id' ) : false;

		//Put the loading animation into the placeholder
		content_placeholder.html( '<div class="mp-core-loading-spinner"></div>' );

		//Open the metabox
		$( '#' + metabox_id ).removeClass( 'closed' ).addClass( 'open' );

		//If this metabox doesn't have an ajax placeholder in which to load the content, get out of here.
		if ( !metabox_id ){
			return;
		}

		//Load in the metabox content via ajax
		var postData = {
			action: metabox_id,
			mp_core_metabox_ajax: true,
			mp_core_metabox_id_ajax: metabox_id,
			mp_core_metabox_post_id: post_id
		};

		//Run the Ajax
		$.ajax({
			type: "POST",
			data: postData,
			dataType:"json",
			url: 'admin-ajax.php',
			success: function (response) {

				//If the response is false (or "0"), something went wrong.
				if ( response == 0 ){
					$( content_placeholder ).replaceWith( 'Oops! Something went wrong while trying to load these options for ' + metabox_id + '.' );
				}
				else{

					//Place the metabox controls into the metabox in question.
					mp_core_load_ajax_metabox_contents( response, '#' + metabox_id );

				}

			}
		}).fail(function (data) {
			console.log(data);
		});

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
	$( window ).on( 'load', function(){

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

	//When the user drags a range slider, show its value beside it
	$(document).on("change mousemove", "input[type='range']", function() {
		$(this).next().val($(this).val());
	});

	//Show each range slider's value bseide it when the page loads
	$( window ).on( 'load', function(){
		$(document).find("input[type='range']").each(function(){
			$(this).next().val($(this).val());
		});
	});

	//When a user types into the input range output field, update the range slider to match the value
	$( document ).on( 'propertychange change keyup input paste', '.mp_core_input_range_output', function( event ){

		//If the value the user entered is greater than 100, back it back to 100
		if ( $(this).val() > 100 ){
			$(this).val( '100' );
		}
		else if( $(this).val() < 0 ){
			$(this).val( '0' );
		}

		$(this).prev().val($(this).val());
	});

	//Conditional Fields - Fields that are only shown if a dropdown/checkbox is set to a specific value
	function mp_core_set_conditional_fields(){
		$(document).find("[mp_conditional_field_id]").each(function(){

			//Get the name of this fields parent conditional field
			var parent_conditional_field_name = $(this).attr('mp_conditional_field_id');

			//Get the name of this value that parent needs to be set to in order for this field to be visible
			var desired_conditional_field_values = $(this).attr('mp_conditional_field_values').split(', ');

			//If the parent is a checkbox
			if ( $( '[name="' + parent_conditional_field_name + '"]' ).attr( 'type' ) == 'checkbox' ){

				if ( $( '[name="' + parent_conditional_field_name + '"]' ).is(':checked')){
					//Show this field
					$(this).css( 'visibility', 'visible');
					$(this).css( 'position', '');
				}
				else{
					//Hide this field - we don't use display block because the showhiders already use it
					$(this).css( 'visibility', 'hidden');
					$(this).css( 'position', 'absolute');
				}

			}
			//If the parent is not a checkbox
			else{
				//If the parent's value is set to what it should be for this field to be visible
				if ( $.inArray( $( '[name="' + parent_conditional_field_name + '"]' ).val(), desired_conditional_field_values ) != -1 ){

					//Show this field
					$(this).css( 'visibility', 'visible');
					$(this).css( 'position', '');

				}
				else{
					//Hide this field - we don't use display block because the showhiders already use it
					$(this).css( 'visibility', 'hidden');
					$(this).css( 'position', 'absolute');
				}
			}
		});
	}
	$( window ).on( 'load', function(){
		mp_core_set_conditional_fields();
	});
	$( document ).ajaxComplete(function() {
		mp_core_set_conditional_fields();
	});

	//When any mp_field select is changed
	$( document ).on( 'change', '.mp_field select, .mp_field :checkbox', function(){

		//Store this select field's class name and object
		parent_name = $(this).attr('name');
		parent_conditional_field = $(this);

		//Find any fields that have this field as a conditional parent
		$(document).find('[mp_conditional_field_id="' + parent_name + '"]').each(function(){

			//Get the name of this value that parent needs to be set to in order for this field to be visible
			var desired_conditional_field_values = $(this).attr('mp_conditional_field_values').split(', ');

			if ( parent_conditional_field.attr('type') == 'checkbox' ){

				if ( parent_conditional_field.is(':checked')){
					//Show this field
					$(this).css( 'visibility', 'visible');
					$(this).css( 'position', '');
				}
				else{
					//Hide this field - we don't use display block because the showhiders already use it
					$(this).css( 'visibility', 'hidden');
					$(this).css( 'position', 'absolute');
				}
			}
			else{
				//If the parent's value is set to what one of the values should be for this field to be visible
				if ( $.inArray( $( parent_conditional_field ).val(), desired_conditional_field_values ) != -1 ){

					//Show this field
					$(this).css( 'visibility', 'visible');
					$(this).css( 'position', '');

				}
				else{
					//Hide this field - we don't use display block because the showhiders already use it
					$(this).css( 'visibility', 'hidden');
					$(this).css( 'position', 'absolute');
				}
			}

		});

	});

	//Showhider function which shows and hides fields based on their parent showhider
	$( document ).on('click', ".mp_core_showhider_button.closed", function(event){

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
	//When showhiders are closed
	$( document ).on('click', '.mp_core_showhider_button.open', function(event){

			event.preventDefault;

			var this_button = $(this);

			//Get name of showhider group
			var showhidergroup = this_button.attr( 'showhidergroup' );

			//After showhider is open, update the classes
			setTimeout(function() {
				this_button.removeClass('open').addClass('closed');
			}, 300);

			//Hide fields in this showhider	 (2nd level showhider)
			$( '[showhider=' + showhidergroup + ']').each( function(){

				//Hide this field
				$(this).css('display', 'none');

				//Child Showhider Button
				var child_this_button = $(this).find( '[showhidergroup]' );

				//Get the showhider group names of any showhiders with this showhider as a parent
				var child_showhidergroup = child_this_button.attr( 'showhidergroup' );

				//After showhider is open, update the classes
				setTimeout(function() {
					child_this_button.removeClass('open').addClass('closed');
				}, 300);

				//Hide fields in this showhider	 (3rd level showhider)
				$( '[showhider=' + child_showhidergroup + ']').each( function(){

					//Hide this field
					$(this).css('display', 'none');

					//Child Showhider Button
					var child_this_button = $(this).find( '[showhidergroup]' );

					//Get the showhider group names of any showhiders with this showhider as a parent
					var child_showhidergroup = child_this_button.attr( 'showhidergroup' );

					//After showhider is open, update the classes
					setTimeout(function() {
						child_this_button.removeClass('open').addClass('closed');
					}, 300);

					//Hide fields in this child showhidergroup (4th level showhider)
					$( '[showhider=' + child_showhidergroup + ']').each( function(){

						//Hide this field
						$(this).css('display', 'none');

					});


				});

			});

	});

});

/**
 * This function loads all of the responses from the mp_core_metabox_ajax ajax callback. For a complete example, open the mp-stacks-admin.js file and search for "mp_core_metabox_ajax".
 */
function mp_core_load_ajax_metabox_contents( response, metabox_id_string ){

	jQuery(document).ready(function($){

		//Loop through all the enqueued css stylesheets that were passed back from ajax
		if ( response.css_stylesheets ){
			$.each( response.css_stylesheets, function( stylesheet_counter, stylesheet_href ) {

				//If this stylesheet has not already been output to the page
				if ( $('link[href="' + stylesheet_href + '"]').length === 0 ){

					//Output this stylesheet link into the document head so the browser loads it
					$( 'head' ).append( '<link rel="stylesheet" href="' + stylesheet_href + '" />' );

				}

			});
		}

		//Place the content-type controls into the designated metabox
		$( metabox_id_string + ' .inside' ).html( response.metabox_content );

		//Reset all the wp_editors on the page
		mp_core_reset_all_wp_editors();

		//Loop through all the js scripts that were passed back from ajax
		if ( response.js_scripts ){
			$.each( response.js_scripts, function( script_counter, script_output_src ) {

				//If this script has not already been output to the page
				if ( $('script[src="' + script_output_src + '"]').length === 0 ){

					//Output this script into the footer so the browser loads it
					$( 'body' ).append( '<script type="text/javascript" src="' + script_output_src + '"></script>' );

				}

			});
		}

	});
}

function mp_core_reset_all_wp_editors(){

	jQuery(document).ready(function($){

		var wp_editor_init_script = $( "script:contains('tinyMCEPreInit = {')" );

		if ( wp_editor_init_script ){

			wp_editor_init_script = wp_editor_init_script.html().replace(/\s\s+/g, ' ');
		}

		var new_tiny_mce_code = JSON.parse( JSON.stringify( tinyMCEPreInit ) );

		$(document).find('.wp-editor-area').each(function() {

			//console.log( new_tiny_mce_code );

			tinyMCE.execCommand( 'mceRemoveEditor', true, this.id );
			$( '.quicktags-toolbar' ).remove();

			var this_wp_editor_id = $(this).attr( 'id' );

			//TinyMCE
			new_tiny_mce_code['mceInit'][this_wp_editor_id] = JSON.parse(JSON.stringify( tinyMCEPreInit['mceInit']['mp_core_wpeditor_init'] ));
			new_tiny_mce_code['mceInit'][this_wp_editor_id]['selector']  = '#' + this_wp_editor_id;
			new_tiny_mce_code['mceInit'][this_wp_editor_id]['body_class']  = this_wp_editor_id + ' post-status-publish locale-en-us';

			//QuickTags
			new_tiny_mce_code['qtInit'][this_wp_editor_id] = JSON.parse(JSON.stringify( tinyMCEPreInit['qtInit']['mp_core_wpeditor_init'] ));
			new_tiny_mce_code['qtInit'][this_wp_editor_id]['id'] = this_wp_editor_id;

		});

		//Stringify the array so it can be placed back into the script tag
		var new_tiny_mce_code = 'tinyMCEPreInit = ' + JSON.stringify(new_tiny_mce_code) + '; This will help with giving us something to search and replace.';

		//Store the load_ext code in a var so we can add it to the string.
		var load_ext_script = '}, "load_ext": function(url, lang) {' +
			'var sl = tinymce.ScriptLoader;' +
			'sl.markDone(url + \'/langs/\' + lang + \'.js\');' +
			'sl.markDone(url + \'/langs/\' + lang + \'_dlg.js\');'+
		'} };';

		//Add the load_ext code to the end of the stringified array
		new_tiny_mce_code = new_tiny_mce_code.replace( '}}; This will help with giving us something to search and replace.', load_ext_script );

		//Replace the old wp_editor init script with the newly adjusted script.
		$( "script:contains('tinyMCEPreInit = {')" ).replaceWith( '<script id="mp_core_wp_editor_init" type="text/javascript">' + new_tiny_mce_code + '</script>' );

		//console.log( 'addingfooterscripts. now..');

		//Refresh the scripts which initialize the wp_editors (tinyMCE)
		$( "script:contains('tinymce')" ).each( function( script_count, script_tag ){
			var script_html = $(this).html();
			$(this).after( '<script class="mp-core-tinymce-scripts-updated">' + script_html + '</script>' );
			$(this).remove();
			$( '.quicktags-toolbar' ).remove();

		});


		//$( '.quicktags-toolbar' ).remove();

	});
}
