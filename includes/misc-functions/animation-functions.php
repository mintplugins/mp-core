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
 * Return the javascript needed to animate an element when it is in view using the waypoints.js event
 *
 * @access   public
 * @since    1.0.0
 * @param    $element_id String The ID of the element that triggers when it comes into view
 * @param    $child_to_animate String The name of the class within the parent which we want to animate
 * @param    $animation_repeater Array The array, saved using mp_core_metabox class, retrieved using get_post_meta
 * @return   $js_output String The javascript code which, when run, would cause the element to be animated
 */
function mp_core_js_waypoint_animate( $element_id, $animation_repeater, $reverse_play_upon_out = false ){
	
	if ( empty($animation_repeater ) ){
		return;	
	}
	
	//Set the first frame CSS
	$js_output = '<style scoped type="text/css" id="mp-core-temp-css-' . sanitize_title( $element_id ) . '">';
	
		$js_output .= $element_id . '{';
			
			//Temporarily set it to be non-visible
			$js_output .= 'visibility: hidden;';
	
	$js_output .= '}
	</style>';
	
	//If there is no javascript enabled in this user's browser, make the item visible again
	$js_output .= '
	<noscript>
		<style scoped type="text/css">
			' . $element_id . ' {visibility:visible;}
		</style>
	</noscript>';
	
	$js_output .= '<script type="text/javascript">
		jQuery(document).ready(function($){ 
						
			$( window ).load(function(){
								
				//Set the first frame of the animation and pause there
				' . mp_core_js_animate_set_first_keyframe( "$(document).find('" . $element_id . "')", $animation_repeater ) .'
				
				//Variable which tells us the current state of the animation: \'start\' or \'end\';
				var ' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position = "start";
				
				//Window Height
				var	windowHeight = $(window).height();
		
				//Get the brick element
				var mp_brick = $( "' . $element_id . '");
				var mp_brick_height = mp_brick.height();
				
				//Brick position variables
				var mp_brick_offset = mp_brick.offset();
				var mp_brick_offset_from_top = mp_brick_offset.top;
				var mp_brick_y = mp_brick_offset_from_top-$(window).scrollTop();	
				var mp_brick_y_plus_25_percent = mp_brick_y + (mp_brick_height / 4 );
				
				//If the top 25% of this brick is in view
				if ( mp_brick_y > 0 && mp_brick_y_plus_25_percent < windowHeight){
										
					//Animate the brick in
					' . mp_core_js_animate_element( "$('" . $element_id . "')", $animation_repeater ) . '
					' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position = "end";
					
					//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
					$(document).find("#mp-core-temp-css-' . sanitize_title( $element_id ) . '").remove(); 
				}
				
				
				//When the user scrolls down and a new brick comes into view	
				var waypoint_' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_in_down = new Waypoint({
				  element: document.getElementById(\'' . sanitize_title( $element_id ) . '\'),
				  handler: function( direction ) {
					  
					  if ( direction == \'up\' ){
						
						return false;
												
					  }
					  if ( direction == \'down\' ){
						
						//If we\'re scrolling down, animate it into view
						if ( ' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position == "start" ){
							' . mp_core_js_animate_element( "$('" . $element_id . "')", $animation_repeater ) . '
							' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position = "end";
						}
					  }
					
					//console.log( "Going " + direction );
					
					//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
					$(document).find("#mp-core-temp-css-' . sanitize_title( $element_id ) . '").remove(); 
				  },
				  offset: function() {
					//return this.element.clientHeight/2
					return windowHeight - ( this.element.clientHeight/4 )
				  }
				 
				});';
			
			if ( $reverse_play_upon_out ){
			
				$js_output .= '
				
					var waypoint_' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_down_out = new Waypoint({
					  element: document.getElementById(\'' . sanitize_title( $element_id ) . '\'),
					  handler: function( direction ) {
						  
						  if ( direction == \'down\' ){
							
							//If we\'re scrolling down, animate it out of view
							if( ' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position == "end" ){
								' . mp_core_js_reverse_animate_element( "$('" . $element_id . "')", $animation_repeater ) . '
								' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position = "start";
							}
						  }
						
						//console.log( "Going " + direction );
						
						//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
						$(document).find("#mp-core-temp-css-' . sanitize_title( $element_id ) . '").remove(); 
					  },
					   //When this brick\'s bottom edge is 20% from touching the top of the window (and travelling "upwards")
					  offset: function() {
						return -this.element.clientHeight + ( this.element.clientHeight /5 );
					  }
					 
					});
					
					var waypoint_' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_up_in = new Waypoint({
					  element: document.getElementById(\'' . sanitize_title( $element_id ) . '\'),
					  handler: function( direction ) {
						  
						  if ( direction == \'up\' ){
							
							//If we\'re scrolling up, animate it back into view
							if ( ' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position == "start" ){
								' . mp_core_js_animate_element( "$('" . $element_id . "')", $animation_repeater ) . '
								' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position = "end";
							}
							
						  }
						
						//console.log( "Going " + direction );
						
						//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
						$(document).find("#mp-core-temp-css-' . sanitize_title( $element_id ) . '").remove(); 
					  },
					   //When this brick\'s bottom edge is 20% from touching the top of the window (and travelling "downwards")
					  offset: function() {
						return -this.element.clientHeight + ( this.element.clientHeight /5 );
					  }
					 
					});
					
					//If we\'re scrolling up and a brick dissapears off the bottom
					var waypoint_' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_up_out = new Waypoint({
					  element: document.getElementById(\'' . sanitize_title( $element_id ) . '\'),
					  handler: function( direction ) {
						  
						  if ( direction == \'up\' ){
							
							//If we\'re scroll up, animate it out of view
							if( ' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position == "end" ){
								' . mp_core_js_reverse_animate_element( "$('" . $element_id . "')", $animation_repeater ) . '
								' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position = "start";
							}
						  }
						
						//console.log( "Going " + direction );
						
						//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
						$(document).find("#mp-core-temp-css-' . sanitize_title( $element_id ) . '").remove(); 
					  },
					  //When this brick\'s top edge is 20% from touching the bottom of the window (and travelling "downwards" )
					  offset: function() {
						return windowHeight - ( this.element.clientHeight/4 )
					  }
					 
					});';
			
			}
			
		$js_output .= '});		
		});
	</script>';
	
	return $js_output;
}

/**
 * Return the javascript needed to animate a CHILD element when it is in view using the waypoints.js event and the animation for a child within that parent element
 *
 * @access   public
 * @since    1.0.0
 * @param    $element_id String The ID of the element that triggers when it comes into view
 * @param    $child_to_animate String The name of the class within the parent which we want to animate
 * @param    $animation_repeater Array The array, saved using mp_core_metabox class, retrieved using get_post_meta
 * @return   $js_output String The javascript code which, when run, would cause the element to be animated
 */
function mp_core_js_waypoint_animate_child( $element_id, $child_to_animate, $animation_repeater, $reverse_play_upon_out = false ){
	
	if ( empty($animation_repeater ) ){
		return;	
	}
	
	//Set the first frame CSS
	$js_output = '<style scoped type="text/css" id="mp-core-temp-css-' . sanitize_title( $child_to_animate ) . '">';
	
		$js_output .= $element_id . ' ' . $child_to_animate . '{';
			
			//Temporarily set it to be non-visible
			$js_output .= 'visibility: hidden;';
	
	$js_output .= '}
	</style>';
	
	//If there is no javascript enabled in this user's browser, make the item visible again
	$js_output .= '
	<noscript>
		<style scoped type="text/css">
			' . $element_id . ' ' . $child_to_animate . ' {visibility:visible;}
		</style>
	</noscript>';
	
	$js_output .= '<script type="text/javascript">
		jQuery(document).ready(function($){ 
						
			$( window ).load(function(){
								
				//Set the first frame of the animation and pause there
				' . mp_core_js_animate_set_first_keyframe( "$(document).find('" . $element_id . ' ' . $child_to_animate . "')", $animation_repeater ) .'
				
				//Variable which tells us the current state of the animation: \'start\' or \'end\';
				var ' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position = "start";
				
				//Window Height
				var	windowHeight = $(window).height();
		
				//Get the brick element
				var mp_brick = $( "' . $element_id . '");
				var mp_brick_height = mp_brick.height();
				
				//Brick position variables
				var mp_brick_offset = mp_brick.offset();
				var mp_brick_offset_from_top = mp_brick_offset.top;
				var mp_brick_y = mp_brick_offset_from_top-$(window).scrollTop();	
				var mp_brick_y_plus_25_percent = mp_brick_y + (mp_brick_height / 4 );
				
				//If the top 25% of this brick is in view
				if ( mp_brick_y > 0 && mp_brick_y_plus_25_percent < windowHeight){
										
					//Animate the brick in
					' . mp_core_js_animate_element( "$('" . $element_id . " " . $child_to_animate . "')", $animation_repeater ) . '
					' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position = "end";
					
					//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
					$(document).find("#mp-core-temp-css-' . sanitize_title( $child_to_animate ) . '").remove(); 
				}
				
				
				//When the user scrolls down and a new brick comes into view	
				var waypoint_' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_in_down = new Waypoint({
				  element: document.getElementById(\'' . sanitize_title( $element_id ) . '\'),
				  handler: function( direction ) {
					  
					  if ( direction == \'up\' ){
						
						return false;
												
					  }
					  if ( direction == \'down\' ){
						
						//If we\'re scrolling down, animate it into view
						if ( ' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position == "start" ){
							' . mp_core_js_animate_element( "$('" . $element_id . " " . $child_to_animate . "')", $animation_repeater ) . '
							' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position = "end";
						}
					  }
					
					//console.log( "Going " + direction );
					
					//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
					$(document).find("#mp-core-temp-css-' . sanitize_title( $child_to_animate ) . '").remove(); 
				  },
				  offset: function() {
					//return this.element.clientHeight/2
					return windowHeight - ( this.element.clientHeight/4 )
				  }
				 
				});';
			
			if ( $reverse_play_upon_out ){
			
				$js_output .= '
				
					var waypoint_' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_down_out = new Waypoint({
					  element: document.getElementById(\'' . sanitize_title( $element_id ) . '\'),
					  handler: function( direction ) {
						  
						  if ( direction == \'down\' ){
							
							//If we\'re scrolling down, animate it out of view
							if( ' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position == "end" ){
								' . mp_core_js_reverse_animate_element( "$('" . $element_id . " " . $child_to_animate . "')", $animation_repeater ) . '
								' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position = "start";
							}
						  }
						
						//console.log( "Going " + direction );
						
						//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
						$(document).find("#mp-core-temp-css-' . sanitize_title( $child_to_animate ) . '").remove(); 
					  },
					   //When this brick\'s bottom edge is 20% from touching the top of the window (and travelling "upwards")
					  offset: function() {
						return -this.element.clientHeight + ( this.element.clientHeight /5 );
					  }
					 
					});
					
					var waypoint_' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_up_in = new Waypoint({
					  element: document.getElementById(\'' . sanitize_title( $element_id ) . '\'),
					  handler: function( direction ) {
						  
						  if ( direction == \'up\' ){
							
							//If we\'re scrolling up, animate it back into view
							if ( ' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position == "start" ){
								' . mp_core_js_animate_element( "$('" . $element_id . " " . $child_to_animate . "')", $animation_repeater ) . '
								' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position = "end";
							}
							
						  }
						
						//console.log( "Going " + direction );
						
						//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
						$(document).find("#mp-core-temp-css-' . sanitize_title( $child_to_animate ) . '").remove(); 
					  },
					   //When this brick\'s bottom edge is 20% from touching the top of the window (and travelling "downwards")
					  offset: function() {
						return -this.element.clientHeight + ( this.element.clientHeight /5 );
					  }
					 
					});
					
					//If we\'re scrolling up and a brick dissapears off the bottom
					var waypoint_' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_up_out = new Waypoint({
					  element: document.getElementById(\'' . sanitize_title( $element_id ) . '\'),
					  handler: function( direction ) {
						  
						  if ( direction == \'up\' ){
							
							//If we\'re scroll up, animate it out of view
							if( ' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position == "end" ){
								' . mp_core_js_reverse_animate_element( "$('" . $element_id . " " . $child_to_animate . "')", $animation_repeater ) . '
								' . str_replace( '-', '_', sanitize_title( $element_id ) ) . '_animation_position = "start";
							}
						  }
						
						//console.log( "Going " + direction );
						
						//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
						$(document).find("#mp-core-temp-css-' . sanitize_title( $child_to_animate ) . '").remove(); 
					  },
					  //When this brick\'s top edge is 20% from touching the bottom of the window (and travelling "downwards" )
					  offset: function() {
						return windowHeight - ( this.element.clientHeight/4 )
					  }
					 
					});';
			
			}
			
		$js_output .= '});		
		});
	</script>';
	
	return $js_output;
}


/**
 * Return the javascript needed to animate an element including the pageload event and the animation for a child within that parent element
 *
 * @access   public
 * @since    1.0.0
 * @param    $element_selector_string String The name of the class whose element we will animate
 * @param    $child_to_animate String The name of the class within the parent which we want to animate
 * @param    $animation_repeater Array The array, saved using mp_core_metabox class, retrieved using get_post_meta
 * @return   $js_output String The javascript code which, when run, would cause the element to be animated
 */
function mp_core_js_page_load_animate_child( $element_id, $child_to_animate, $animation_repeater ){
	
	if ( empty($animation_repeater ) ){
		return;	
	}
	
	//Set the first frame CSS (we wrap this in a script tag so that if the user has no javascript, it doesn't auto hide animated objects);
	$js_output = '<style scoped type="text/css" id="mp-core-temp-css-' . sanitize_title( $child_to_animate ) . '">';
	
		$js_output .= $element_selector_string . ' ' . $child_to_animate . '{';
			
			//Temporarily set it to be non-visible
			$js_output .= 'visibility: hidden;';
	
	$js_output .= '}
	</style>';
	
	//If there is no javascript enabled in this user's browser, make the item visible again
	$js_output .= '
	<noscript>
		<style scoped type="text/css">
			' . $element_selector_string . ' ' . $child_to_animate . ' {visibility:visible;}
		</style>
	</noscript>';
	
	$js_output .= '<script type="text/javascript">
		jQuery(document).ready(function($){ 
			
			$(document).on( "load", function($){ 
				' . mp_core_js_animate_set_first_keyframe( "$(this).find('" . $element_selector_string . ' ' . $child_to_animate . "')", $animation_repeater ) . '			
				$( document ).trigger(\'mp_core_animation_set_first_keyframe_trigger\');
			});';
				
			
			$js_output .= mp_core_js_animate_element( "$(this).find('" . $child_to_animate . "')", $animation_repeater );
				
			$js_output .= '
						
			//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
			$(document).find("#mp-core-temp-css-' . sanitize_title( $child_to_animate ) . '").remove(); 
		});
	</script>';
	
	return $js_output;
}

/**
 * Return the javascript needed to animate an element including the mouseover event and the animation for a child within that parent element
 *
 * @access   public
 * @since    1.0.0
 * @param    $mouse_over_string String The name of the class whose element we will animate
 * @param    $child_to_animate String The name of the class within the parent which we want to animate
 * @param    $animation_repeater Array The array, saved using mp_core_metabox class, retrieved using get_post_meta
 * @param    $bring_to_forefront Bool If true, this item's z-index will bump super high upon mouse over and back to nothing on mouse out
 * @return   $js_output String The javascript code which, when run, would cause the element to be animated
 */
function mp_core_js_mouse_over_animate_child( $mouse_over_string, $child_to_animate, $animation_repeater, $bring_to_forefront = true ){
	
	if ( empty($animation_repeater ) ){
		return;	
	}
	
	//Set the first frame CSS (we wrap this in a script tag so that if the user has no javascript, it doesn't auto hide animated objects);
	$js_output = '<style scoped type="text/css" id="mp-core-temp-css-' . sanitize_title( $child_to_animate ) . '">';
	
		$js_output .= $mouse_over_string . ' ' . $child_to_animate . '{';
			
			//Temporarily set it to be non-visible
			$js_output .= 'visibility: hidden;';
	
	$js_output .= '}
	</style>';
	
	//If there is no javascript enabled in this user's browser, make the item visible again
	$js_output .= '
	<noscript>
		<style scoped type="text/css">
			' . $mouse_over_string . ' ' . $child_to_animate . ' {visibility:visible;}
		</style>
	</noscript>';
	
	$js_output .= '<script type="text/javascript">
		jQuery(document).ready(function($){ 
			
			$( document ).on( \'mp_core_animation_set_first_keyframe_trigger\', function(event){
				' . mp_core_js_animate_set_first_keyframe( "$(this).find('" . $mouse_over_string . ' ' . $child_to_animate . "')", $animation_repeater ) . '
			});
			
			$( document ).trigger(\'mp_core_animation_set_first_keyframe_trigger\');';
			
			//If we are on an iphone, ipad, android, or other touch enabled screens, run the animations on the first touch, then go to the link on the second
			if ( mp_core_is_iphone() || mp_core_is_ipad() || mp_core_is_android() ){
				
				/* For now, hover triggered animations don't happen on touch devices
				$js_output .= '
				//On mobile, the first click runs the animation and the second goes to the link.
				$( document ).on( \'touchend\', \'' . $mouse_over_string . '\', function(event){
			
					if ( typeof $(this).attr(\'mp_core_animation_run\') == \'undefined\' ){
						
						var this_element = $(this);
						
						event.preventDefault();
											
						' . mp_core_js_animate_element( "this_element.find('" . $child_to_animate . "')", $animation_repeater ) . '
						
						setInterval(function(){ 
							
							this_element.attr(\'mp_core_animation_run\', \'true\');
							
						}, 30);
						
					}
				});';
				*/
			}
			//If we are not on mobile, run the animations on mouseenter and mouseleave
			else{
			
				$js_output .= '
					$( document ).on( \'mouseenter\', \'' . $mouse_over_string . '\', function(event){
						' . ( $bring_to_forefront ? '$(this).css("z-index", "9999999999");' : NULL ) . '
						' . mp_core_js_animate_element( "$(this).find('" . $child_to_animate . "')", $animation_repeater ) . 
					'}); 
					
					$( document ).on( \'mouseleave\', \'' . $mouse_over_string . '\', function(event){
						' . ( $bring_to_forefront ? '$(this).css("z-index", "");' : NULL ) . '
						' . mp_core_js_reverse_animate_element( "$(this).find('" . $child_to_animate . "')", $animation_repeater ) . 
					'});';
			}
			
			$js_output .= '
						
			//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
			$(document).find("#mp-core-temp-css-' . sanitize_title( $child_to_animate ) . '").remove(); 
		});
	</script>';
	
	return $js_output;
}

/**
 * Return the javascript needed to animate an element including the mouseover event
 *
 * @access   public
 * @since    1.0.0
 * @param    $mouse_over_string String The name of the class whose element we will animate
 * @param    $animation_repeater Array The array, saved using mp_core_metabox class, retrieved using get_post_meta
 * @param    $bring_to_forefront Bool If true, this item's z-index will bump super high upon mouse over and back to nothing on mouse out
 * @return   $js_output String The javascript code which, when run, would cause the element to be animated
 */
function mp_core_js_mouse_over_animate( $mouse_over_string, $animation_repeater, $bring_to_forefront = true ){
	
	if ( empty($animation_repeater ) ){
		return;	
	}
	
	//Set the first frame CSS (we wrap this in a script tag so that if the user has no javascript, it doesn't auto hide animated objects);
	$js_output = '<style scoped type="text/css" id="mp-core-temp-css-' . sanitize_title( $mouse_over_string ) . '">';
	
		$js_output .= $mouse_over_string . '{';
			
			//Temporarily set it to be non-visible
			$js_output .= 'visibility: hidden;';
	
	$js_output .= '}
	</style>';
	
	//If there is no javascript enabled in this user's browser, make the item visible again
	$js_output .= '
	<noscript>
		<style scoped type="text/css">
			' . $mouse_over_string . ' ' . '{visibility:visible;}
		</style>
	</noscript>';
	
	$js_output .= '<script type="text/javascript">
		jQuery(document).ready(function($){ 
			
			$( document ).on( \'mp_core_animation_set_first_keyframe_trigger\', function(event){
				' . mp_core_js_animate_set_first_keyframe( "$(this).find('" . $mouse_over_string . "')", $animation_repeater ) . '
			});
			
			$( document ).trigger(\'mp_core_animation_set_first_keyframe_trigger\');';
			
			//If we are on an iphone, ipad, android, or other touch enabled screens, run the animations on the first touch, then go to the link on the second
			if ( mp_core_is_iphone() || mp_core_is_ipad() || mp_core_is_android() ){
				
				$js_output .= '
				//On mobile, the first click runs the animation and the second goes to the link.
				$( document ).on( \'touchend\', \'' . $mouse_over_string . '\', function(event){
			
					if ( typeof $(this).attr(\'mp_core_animation_run\') == \'undefined\' ){
						
						var this_element = $(this);
						
						event.preventDefault();
											
						' . mp_core_js_animate_element( "this_element", $animation_repeater ) . '
						
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
						' . ( $bring_to_forefront ? '$(this).css("z-index", "9999999999");' : NULL ) . '
						' . mp_core_js_animate_element( "$(this)", $animation_repeater ) . 
					'}); 
					
					$( document ).on( \'mouseleave\', \'' . $mouse_over_string . '\', function(event){
						' . ( $bring_to_forefront ? '$(this).css("z-index", "");' : NULL ) . '
						' . mp_core_js_reverse_animate_element( "$(this)", $animation_repeater ) . 
					'});';
			}
			
			$js_output .= '
						
			//Remove the visibility:hidden for this element once the javascript has loaded the first keyframe
			$(document).find("#mp-core-temp-css-' . sanitize_title( $mouse_over_string ) . '").remove(); 
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
	
	echo $selector_string . '.each(function(){ $(this).velocity(
					{';
    
    //Loop through each keyframe
	foreach( $animation_repeater as $repeat ){
		
		mp_core_animation_echo_values( $repeat );
        
		//Animation Length: This is formatted weird like this so it looks nicer on the front end
		echo '}, 
						{duration: 0, begin: function() { 
			
		}, complete: function() { 
			
			//console.log( \'set default\');
			
		} });});'; 
		
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
		
	echo $selector_string . '.velocity("stop").velocity(
					{';
    
    //Loop through each keyframe
	foreach( $animation_repeater as $repeat ){
				
		mp_core_animation_echo_values( $repeat );
        
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
	
	if ( $counter < $forlength){ 
		echo '}).velocity(
					{';
	} 
	else{ 
		echo ', begin: function() { 
				
			$( document ).trigger( "mp_core_animation_start" ); 
			
		}, complete: function() { 
				
			$( document ).trigger( "mp_core_animation_end" ); 
			
		}
	});';
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
	echo $selector_string . ' .velocity("stop").velocity(
					{';
    
    //Loop through each keyframe
	foreach( $animation_repeater as $repeat ){
    	
		mp_core_animation_echo_values( $repeat );
        
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
		
		if ( $counter < $forlength){
			echo '}).velocity(
						{';
		} else{ 
			echo ', begin: function() { 
					
				$( document ).trigger( "mp_core_animation_start" ); 
				
			}, complete: function() { 
					
				$( document ).trigger( "mp_core_animation_end" ); 
				
			}
		});';
		} 
		 
		$counter = $counter + 1;
	
	}	
	return ob_get_clean(); 
		
}
	
/**
 * This function echoes all the values needed for the velocity animation based on the passed-in repeat
 *
 * @access   public
 * @since    1.0.0
 * @param    $repeat array A single animation keyframe containing all of the css changes 
 * @return   void 
 */	
function mp_core_animation_echo_values( $repeat ){
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
						
			//If it has a value
			if ( mp_core_value_exists( $value ) ){	
				
				//Reduce it to a 0 or 1 value
				$value = $value / 100;
			
			}
			//If it has no value
			else{
				$value = 1;
			}
		}
					
		//If this is a color animation
		if ( $id == 'backgroundColor' ){
			
			if ( !empty( $value ) ){
				
				//This is used in the next iteration
				$output_background_alpha = true;
				
				$rgb_array = mp_core_hex2rgb( $value );
				
				echo 'backgroundColorRed: "' . $rgb_array[0] . '",';
				echo 'backgroundColorGreen: "' . $rgb_array[1] . '",';
				echo 'backgroundColorBlue: "' . $rgb_array[2] . '",';
				
			}
			else{
				echo 'backgroundColor: function() { 
					if ( $(this).attr("mp-default-bg-color") ){
						return $(this).attr("mp-default-bg-color");		
					}
					else{
						return false;	
					}
				},';	
				$value_counter = $value_counter + 1;
				continue;
			}
				
		}
		
		//When creating the meta array, make sure the background alpha is directly after the background color or it won't work right
		if ( $id == 'backgroundColorAlpha' ){
			if ( isset( $output_background_alpha ) ){
						
				//If it has a value
				if ( mp_core_value_exists( $value ) ){	
					
					//Reduce it to a 0 or 1 value
					$value = $value / 100;
	
				}
				//If it has no value
				else{
					$value = 1;
				}
			}
			else{
				$value_counter = $value_counter + 1;
				continue;	
			}
		}
		
		//If this is rotation
		if ( $id == 'rotateZ' ){
			$value = empty( $value ) ? 0 : $value;
			$unit = 'deg';
		}
		
		//If this is X or Y
		if ( $id == 'translateX' || $id == 'translateY' ){
			$value = empty( $value ) ? 0 : $value;
			$unit = 'px';
		}
		
		//If this is scale
		if ( $id == 'scale' ){
			$value = empty($value) ? 1 : $value / 100;
		}
		
		//Output the value for this keyframe value
		if ( mp_core_value_exists( $value ) ){
			echo  $id . ': "' . $value . $unit . '"';
			
			//If this isn't the last item in the keyframe
			if ($value_counter < $value_forlength){
				echo ',';	
			}
		
		}
		
		$value_counter = $value_counter + 1;
	}
}