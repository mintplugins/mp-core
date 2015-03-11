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
						
			//If the element variable is an array, we need to loop through each element and process the css for each
			if ($.isArray(setting_values['element'])){
				
				//Set Counter
				var counter = 0;
					
				//Loop through each element in the array
				$.each(setting_values['element'], function( key, element_id ) {
						
						//Set up event listener to change this element on the fly
						mp_core_element_css( element_id, setting_values['arg'][counter], setting_values['jquery_function_name'], setting_id );
						
						//Increment counter
						counter++;
						
				});
			}
			//If the element variable is not an array, we need to process the css for just this element
			else{
					
					//Set up event listener to change this element on the fly
					mp_core_element_css( setting_values['element'], setting_values['arg'], setting_values['jquery_function_name'], setting_id );
				
			}
			
		});
	});
	
	/**
	 * Event Listener which Processes CSS Output for elements passed-in to this function
	 *
	 * @since    1.0.0
	 * @param    string $element_id The CSS selector. 
	 * @param    string $css_arg The CSS arg name. 
	 * @param    string $theme_mod_id The CSS value. 
	 * @return   void 
	 */
	function mp_core_element_css( element_id, element_css_arg, jquery_function_name, setting_id ){
		
		//Update the element in real time...
		wp.customize( setting_id, function( value ) {
			value.bind( function( newval ) {
								
				//If there is an arg
				if (typeof(element_css_arg) != "undefined" && element_css_arg !== null){
					
					//Background Image
					if (element_css_arg == 'background-image'){
						if (newval == null || newval == '' || newval == false){
							//No background image
							eval('$( \'' + element_id + '\' ).' + jquery_function_name + '( \'' + element_css_arg + '\', \'\' );');
						}else{
							eval('$( \'' + element_id + '\' ).' + jquery_function_name + '( \'' + element_css_arg + '\', \'url(' + newval + ')\' );');
						}
					}
					//Background Enabled
					else if (element_css_arg == 'background-disabled'){
	
						if (newval == null || newval == '' || newval == false){
							
							//Inherit background image
							eval('$( \'' + element_id + '\' ).' + jquery_function_name + '( \'' + 'background-image' + '\', \'\' );');
							
						}
						else{
	
							//No background image
							eval('$( \'' + element_id + '\' ).' + jquery_function_name + '( \'' + 'background-image' + '\', \'none\' );');
	
						}
					}
					//Display
					else if (element_css_arg == 'display'){
						if (newval == false){
							newval = 'none';	
						}else{
							newval = 'block';	
						}
						eval('$( \'' + element_id + '\' ).' + jquery_function_name + '( \'' + element_css_arg + '\', \'' + newval + '\' );');
					}
					
					//Font-Size
					else if( element_css_arg == "font-size(px)" ){
						
						if (newval == null || newval == '' || newval == false){
						
							newval = '0';
							
						}
						
						eval('$( \'' + element_id + '\' ).' + jquery_function_name + '( \'' + 'font-size' + '\', \'' + newval + 'px\' );');
						
					}
					
					//Border-width
					else if( element_css_arg == "border-width" ){
						
						if (newval == null || newval == '' || newval == false){
							
							newval = '0';
							
						}
						
						eval('$( \'' + element_id + '\' ).' + jquery_function_name + '( \'' + element_css_arg + '\', \'' + newval + 'px\' );');
						
					}
					
					//Border-radius
					else if( element_css_arg == "border-radius" ){
						
						if (newval == null || newval == '' || newval == false){
							
							newval = '0';
							
						}
						eval('$( \'' + element_id + '\' ).' + jquery_function_name + '( \'' + element_css_arg + '\', \'' + newval + 'px\' );');
						
					}
					//Other
					else{
						if (newval == null || newval == '' || newval == false){
							newval = '';	
						}
						eval('$( \'' + element_id + '\' ).' + jquery_function_name + '( \'' + element_css_arg + '\', \'' + newval + '\' );');
					}
				//If there is no arg
				}else{
					eval('$( \'' + element_id + '\' ).' + jquery_function_name + '( \'' + newval + '\' );');
				}
			});
		});
	}
		
} )( jQuery );