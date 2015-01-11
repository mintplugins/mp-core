<?php
/**
 * This file contains various functions dealing with animation
 *
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Functions
 *
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */

/**
 * Return the javascript needed to animate an element including the mouseover event and the animation for a child within that parent element
 *
 * @access   public
 * @since    1.0.0
 * @param    $mouse_over_string String The name of the class whose element we will animate
 * @param    $child_to_animate String The name of the class within the parent which we want to animate
 * @param    $animation_repeater Array The array, saved using mp_core_metabox class, retrieved using get_post_meta
 * @return   $js_output String The javascript code which, when run, would cause the element to be animated
 */
function mp_core_js_mouse_over_animate_child( $mouse_over_string, $child_to_animate, $animation_repeater ){
	
	//Set the first frame CSS
	$js_output = '<style type="text/css" id="' . str_replace(' ', '', str_replace('.', '', str_replace('#', '', $mouse_over_string)) . '_' . str_replace('.', '', str_replace('#', '', $child_to_animate))) . '">';
	
		$js_output .= $mouse_over_string . ' ' . $child_to_animate . '{';
			
			//Temporarily set it to be non-visible
			$js_output .= 'visibility: hidden;';
	
	$js_output .= '}
	</style>';
	
	$js_output .= '<script type="text/javascript">
		jQuery(document).ready(function($){ 
			
			$( document ).on( \'mp_core_animation_set_first_keyframe_trigger\', function(event){
				
				' . mp_core_js_animate_set_first_keyframe( "$(this).find('" . $mouse_over_string . ' ' . $child_to_animate . "')", $animation_repeater ) . '
			});
			
			$( document ).trigger(\'mp_core_animation_set_first_keyframe_trigger\');';
			
			//If we are on an iphone, ipad, android, or other touch enabled screens, run the animations on the first touch, then go to the link on the second
			if ( mp_core_is_iphone() || mp_core_is_ipad() || mp_core_is_android() ){
				
				$js_output .= '
				//On mobile, the first click runs the animation and the second goes to the link.
				$( document ).on( \'touchstart\', \'' . $mouse_over_string . '\', function(event){
			
					if ( typeof $(this).attr(\'mp_core_animation_run\') == \'undefined\' ){
						
						var this_element = $(this);
						
						event.preventDefault();
											
						' . mp_core_js_animate_element( "this_element.find('" . $child_to_animate . "')", $animation_repeater ) . '
						
						setInterval(function(){ 
							
							this_element.attr(\'mp_core_animation_run\', \'true\');
							
						}, 30);
						
					}
				});';
			}
			//If we are not on mobile, run the animations on mouseenter and mouseleave
			else{
			
				$js_output .= '
					$( document ).on( \'mouseenter\', \'' . $mouse_over_string . '\', function(event){
						' . mp_core_js_animate_element( "$(this).find('" . $child_to_animate . "')", $animation_repeater ) . 
					'}); 
					
					$( document ).on( \'mouseleave\', \'' . $mouse_over_string . '\', function(event){
						' . mp_core_js_reverse_animate_element( "$(this).find('" . $child_to_animate . "')", $animation_repeater ) . 
					'});';
			}
			
			$js_output .= '
						
			//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
			$(document).find("#' . str_replace(' ', '', str_replace('.', '', str_replace('#', '', $mouse_over_string)) . '_' . str_replace('.', '', str_replace('#', '', $child_to_animate))) . '").remove(); 
		});
	</script>';
	
	return $js_output;
}

/**
 * Return the javascript needed to set the first keyframe of an animation upon load
 *
 * @access   public
 * @since    1.0.0
 * @param    $selector_string String The name of the class whose element we will animate
 * @param    $animation_repeater Array The array, saved using mp_core_metabox class, retrieved using get_post_meta
 * @return   $js_output String The javascript code which, when run, would cause the element to be animated
 */
function mp_core_js_animate_set_first_keyframe( $selector_string, $animation_repeater ){
		
	ob_start(); 
	
	echo $selector_string . '.velocity(
					{';
    
    //Loop through each keyframe
	foreach( $animation_repeater as $repeat ){
		
		$value_counter = 2;
		$value_forlength = count($repeat);
		
		//Loop through each value in this keyframe
		foreach( $repeat as $id => $value ){
			
			//Don't export the animation length parameter because we use that differently altogether
			if ( $id == 'animation_length' ){
				continue;	
			}
			
			//Default for the unit 
			$unit = NULL;
			
			//If this value is 'opacity'
			if ( $id == 'opacity' ){
			
				//Opacity
				$value = $value / 100;
				
				//If this is set to be 0
				if ( empty( $value ) && is_numeric( $value ) ){	
					 $value =  '0';
				}
				//If it has a value
				else if ( !empty( $value ) ){	
					$value = $value;
				}
				//If it has no value
				else{
					$value = 1;
				}
			}
			
			//If this is a color animation
			if ( $id == 'backgroundColor' ){
				
				$rgb_array = mp_core_hex2rgb( $value );
				
				echo 'backgroundColorRed: "' . $rgb_array[0] . '",';
				echo 'backgroundColorGreen: "' . $rgb_array[1] . '",';
				echo 'backgroundColorBlue: "' . $rgb_array[2] . '",';
					
			}
			
			//If this is rotation
			if ( $id == 'rotateZ' ){
				$unit = 'deg';
			}
			
			//If this is X or Y
			if ( $id == 'translateX' || $id == 'translateY' ){
				$unit = 'px';
			}
			
			//If this is scale
			if ( $id == 'scale' ){
				$value = $value / 100;
			}
			
			//Output the value for this keyframe value
			echo $id . ': "' . $value . $unit . '",';
			
		}
        
		//Animation Length: This is formatted weird like this so it looks nicer on the front end
		echo '}, 
						{duration: 0 });'; 
		
		//We only need to apply the first keyframe, so break this.
		break;
	}
	
	return ob_get_clean(); 
		
}

/**
 * Return the javascript needed to animate an element
 *
 * @access   public
 * @since    1.0.0
 * @param    $class_name String The name of the class whose element we will animate
 * @param    $animation_repeater Array The array, saved using mp_core_metabox class, retrieved using get_post_meta
 * @return   $js_output String The javascript code which, when run, would cause the element to be animated
 */
function mp_core_js_animate_element( $selector_string, $animation_repeater ){
	
	$counter = 1;
	$forlength = count($animation_repeater);
		
	ob_start(); 
	
	echo $selector_string . '.velocity(
					{';
    
    //Loop through each keyframe
	foreach( $animation_repeater as $repeat ){
		
		$value_counter = 2;
		$value_forlength = count($repeat);
		
		//Loop through each value in this keyframe
		foreach( $repeat as $id => $value ){
			
			//Don't export the animation length parameter because we use that differently altogether
			if ( $id == 'animation_length' ){
				continue;	
			}
			
			//Default for the unit 
			$unit = NULL;
			
			//If this value is 'opacity'
			if ( $id == 'opacity' ){
			
				//Opacity
				$value = $value / 100;
				
				//If this is set to be 0
				if ( empty( $value ) && is_numeric( $value ) ){	
					 $value =  '0';
				}
				//If it has a value
				else if ( !empty( $value ) ){	
					$value = $value;
				}
				//If it has no value
				else{
					$value = 1;
				}
			}
			
			//If this is a color animation
			if ( $id == 'backgroundColor' ){
				
				$rgb_array = mp_core_hex2rgb( $value );
				
				echo 'backgroundColorRed: "' . $rgb_array[0] . '",';
				echo 'backgroundColorGreen: "' . $rgb_array[1] . '",';
				echo 'backgroundColorBlue: "' . $rgb_array[2] . '",';
					
			}
			
			//If this is rotation
			if ( $id == 'rotateZ' ){
				$unit = 'deg';
			}
			
			//If this is rotation
			if ( $id == 'translateX' || $id == 'translateY' ){
				$unit = 'px';
			}
			
			//If this is scale
			if ( $id == 'scale' ){
				$value = $value / 100;
			}
			
			//Output the value for this keyframe value
			echo $id . ': "' . $value . $unit . '"';
			
			//If this isn't the last item in the keyframe
			if ($value_counter < $value_forlength){
				echo ',';	
			}
			
			$value_counter = $value_counter + 1;
		}
        
		//Animation Length: This is formatted weird like this so it looks nicer on the front end
		echo '}, 
						{duration: ';
		
		if ( $counter == 1 ){ 
		
			echo '0'; 
			
		}else{ 
		
			//If this is set to be 0
			if ( empty( $repeat['animation_length'] ) && is_numeric( $repeat['animation_length'] ) ){	
				 echo '0';
			}
			//If it has a value
			else if ( !empty( $repeat['animation_length'] ) ){	
				echo $repeat['animation_length'];
			}
			//If it has no value
			else{
				echo '500';
			}
			
		}
	 
	echo '})';
	
	if ( $counter < $forlength){ 
		echo '.velocity(
					{';
	} 
	else{ 
		echo ';';
	} 
	
	$counter = $counter + 1;
	
	}
	
	return ob_get_clean(); 
		
}

/**
 * Return the javascript needed to animate an element
 *
 * @access   public
 * @since    1.0.0
 * @param    $class_name String The name of the class whose element we will animate
 * @param    $animation_repeater Array The array, saved using mp_core_metabox class, retrieved using get_post_meta
 * @return   $js_output String The javascript code which, when run, would cause the element to be animated
 */
function mp_core_js_reverse_animate_element( $selector_string, $animation_repeater ){
	
	$counter = 1;
	$forlength = count($animation_repeater);
	
	$animation_repeater = array_reverse( $animation_repeater );
		
	ob_start();
	
	//Apply the velocity animation to the element	
	echo $selector_string . '.velocity(
					{';
    
    //Loop through each keyframe
	foreach( $animation_repeater as $repeat ){
    	
		$value_counter = 2;
		$value_forlength = count($repeat);
		
		//Loop through each value in this keyframe
		foreach( $repeat as $id => $value ){
			
			//Don't export the animation length parameter because we use that differently altogether
			if ( $id == 'animation_length' ){
				continue;	
			}
			
			//Default for the unit 
			$unit = NULL;
			
			//If this value is 'opacity'
			if ( $id == 'opacity' ){
			
				//Opacity
				$value = $value / 100;
				
				//If this is set to be 0
				if ( empty( $value ) && is_numeric( $value ) ){	
					 $value =  '0';
				}
				//If it has a value
				else if ( !empty( $value ) ){	
					$value = $value;
				}
				//If it has no value
				else{
					$value = 1;
				}
			}
			
			//If this is rotation
			if ( $id == 'rotateZ' ){
				$unit = 'deg';
			}
			
			//If this is rotation
			if ( $id == 'translateX' || $id == 'translateY' ){
				$unit = 'px';
			}
			
			//If this is scale
			if ( $id == 'scale' ){
				$value = $value / 100;
			}
			
			//Output the value for this keyframe value
			echo $id . ': "' . $value . $unit . '"';
			
			//If this isn't the last item in the keyframe
			if ($value_counter < $value_forlength){
				echo ',';	
			}
			
			$value_counter = $value_counter + 1;
		
		}
        
		//This is formatted weird like this so it looks nicer on the front end
		echo '}, 
						{duration:';
			
			//If this is our first keyframe, we dont want any delay in the animation starting
			if ( $counter == 1 ){ 
				echo 0; 
			}
			else{ 
				
				//If this is set to be 0
				if ( empty( $animation_repeater[$forlength-$counter]['animation_length'] ) && is_numeric( $animation_repeater[$forlength-$counter]['animation_length'] ) ){	
					 echo '0';
				}
				//If it has a value
				else if ( !empty( $animation_repeater[$forlength-$counter]['animation_length'] ) ){	
					echo $animation_repeater[$forlength-$counter]['animation_length'];
				}
				//If it has no value
				else{
					echo '500';
				} 
			}
			
		echo '})';
		
		if ( $counter < $forlength){
			echo '.velocity(
						{';
		} else{ 
			echo ';';
		} 
		
		$counter = $counter + 1;
	
	}
	
	return ob_get_clean(); 
		
}
	