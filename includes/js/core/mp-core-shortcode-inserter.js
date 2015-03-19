jQuery(document).ready(function($){
	
	//When Icon Font Picker item is clicked, put it's value in the field and close the pickerdiv
	$( 'body' ).on( 'click', '.mp-core-icon-picker-item-shortcode', function(event){
		
		event.preventDefault();
			
		//Put the icon code selected into the field ID input field
		$(this).parent().parent().find( '.mp-icon-font-field-container .mp-icon-font-field' ).val($(this).find(' > div > div').html());
		
		//Show the icon in the thumbnail area preview
		//$(this).parent().parent().find( '.mp_font_icon_thumbnail' ).attr( 'class', '');
		$(this).parent().parent().find( '.mp_font_icon_thumbnail' ).attr( 'class', $(this).find(' > div > div').html() + " mp-core-icon-thumb mp_font_icon_thumbnail" );
		$( '.mp_font_icon_thumbnail' ).css( 'display', 'inline-block' );
		
		//Hide the Icon Picker div
		$( '.mp-core-icon-picker-area' ).css('display', 'none');
	
	});
	
	//When Icon Font Picker button is clicked, show the icons availabel for selection
	$( 'body' ).on( 'click', '.mp-core-shortcode-icon-select', function(event){
		
		event.preventDefault();
					
		//Put the icon code selected into the field ID input field
		$( '.mp-core-icon-picker-area' ).css('display', 'inline-block');
			
	});
	
});