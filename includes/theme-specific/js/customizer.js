/**
 * This file adds some LIVE to the Theme Customizer live preview. To leverage
 * this, set your custom settings to 'postMessage' and then add your handling
 * here. Your javascript should grab settings from customizer controls, and 
 * then make any necessary changes to the page using jQuery.
 */
( function( $ ) {
	
	/**
	 * Loop through the wp_registered variables 
	 * and apply the javascript 
	 */
	$.each(mp_core_customizer_vars, function( section, section_values ) {
		$.each(section_values['settings'], function( setting_id, setting_values ) {
			
			//Set the element variable
			element = setting_values['element'];//'#site-title a';
	
			//Update the element in real time...
			wp.customize( setting_id, function( value ) {
				value.bind( function( newval ) {
					
					//If there is an arg
					if (typeof(setting_values['arg']) != "undefined" && setting_values['arg'] !== null){
						
						//Background Image
						if (setting_values['arg'] == 'background-image'){
							if (newval == null || newval == '' || newval == false){
								//No background image
								eval('$( \'' + setting_values['element'] + '\' ).' + setting_values['jquery_function_name'] + '( \'' + setting_values['arg'] + '\', \'\' );');
							}else{
								eval('$( \'' + setting_values['element'] + '\' ).' + setting_values['jquery_function_name'] + '( \'' + setting_values['arg'] + '\', \'url(' + newval + ')\' );');
							}
						}
						//Background Enabled
						else if (setting_values['arg'] == 'background-disabled'){

							if (newval == null || newval == '' || newval == false){
								
								//Inherit background image
								eval('$( \'' + setting_values['element'] + '\' ).' + setting_values['jquery_function_name'] + '( \'' + 'background-image' + '\', \'\' );');
								
							}
							else{
								
								
								
								//No background image
								eval('$( \'' + setting_values['element'] + '\' ).' + setting_values['jquery_function_name'] + '( \'' + 'background-image' + '\', \'none\' );');
		
							}
						}
						//Display
						else if (setting_values['arg'] == 'display'){
							if (newval == false){
								newval = 'none';	
							}else{
								newval = 'block';	
							}
							eval('$( \'' + setting_values['element'] + '\' ).' + setting_values['jquery_function_name'] + '( \'' + setting_values['arg'] + '\', \'' + newval + '\' );');
						}
						//Other
						else{
							if (newval == null || newval == '' || newval == false){
								newval = 'inherit';	
							}
							eval('$( \'' + setting_values['element'] + '\' ).' + setting_values['jquery_function_name'] + '( \'' + setting_values['arg'] + '\', \'' + newval + '\' );');
						}
					//If there is no arg
					}else{
						eval('$( \'' + setting_values['element'] + '\' ).' + setting_values['jquery_function_name'] + '( \'' + newval + '\' );');
					}
				} );
			} );
			
		});
	});
		
} )( jQuery );