<?php
/**
 * This file contains the MP_CORE_Metabox class
 *
 * @link http://mintplugins.com/doc/metabox-class/
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Classes
 *
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */
 
/**
 * This class adds a new metabox with fields of save-able data. 
 * 
 * The field can be singular or they can repeat in groups. 
 * It works by passing an associative array containing the information for the fields to the class
 *
 * @author     Philip Johnston
 * @link       http://mintplugins.com/doc/metabox-class/
 * @since      1.0.0
 * @return     void
 */

if (!class_exists('MP_CORE_Metabox')){
	class MP_CORE_Metabox{
				
		protected $_args;
		protected $_metabox_items_array = array();
		
		/**
		 * Constructor
		 *
		 * @access   public
		 * @since    1.0.0
		 * @link     http://mintplugins.com/doc/metabox-class/
		 * @author   Philip Johnston
		 * @see      MP_CORE_Metabox::mp_core_add_metabox()
		 * @see      MP_CORE_Metabox::mp_core_save_data()
		 * @see      MP_CORE_Metabox::mp_core_enqueue_scripts()
		 * @see      wp_parse_args()
		 * @see      sanitize_title()
		 * @param    array $args (required) See link for description.
		 * @param    array $items_array (required) See link for description.
		 * @return   void
		 */	
		public function __construct($args, $items_array){
											
			//Set defaults for args		
			$args_defaults = array(
				'metabox_id' => NULL, 
				'metabox_title' => NULL, 
				'metabox_posttype' => NULL, 
				'metabox_context' => NULL, 
				'metabox_priority' => NULL 
			);
			
			if( !session_id() ){
				session_start();
			}
			
			//Get and parse args
			$this->_args = wp_parse_args( $args, $args_defaults );
			
			//Get metabox items array
			$this->_metabox_items_array = $items_array;
			
			add_action( 'add_meta_boxes', array( $this, 'mp_core_add_metabox' ) );
			add_action( 'save_post', array( $this, 'mp_core_save_data' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'mp_core_enqueue_scripts' ) );
			
		}
		
		/**
		 * Enqueue Scripts needed for the MP_CORE_Metabox class
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      get_current_screen()
		 * @see      wp_enqueue_style()
		 * @see      wp_enqueue_script()
		 * @see      wp_enqueue_media()
		 * @see      do_action()
		 * @return   void
		 */
		public function mp_core_enqueue_scripts(){
			
			//Get current page
			$current_page = get_current_screen();
			
			//Only load if we are on a post based page
			if ( $current_page->base == 'post' ){
				//mp_core_metabox_css
				wp_enqueue_style( 'mp_core_metabox_css', plugins_url('css/core/mp-core-metabox.css', dirname(__FILE__)), MP_CORE_VERSION );
				//color picker scripts
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker-load', plugins_url( 'js/core/wp-color-picker.js', dirname(__FILE__)),  array( 'jquery', 'wp-color-picker'), MP_CORE_VERSION );
				//mp-ajax-popup script used for popup help tips and videos to help the user
				wp_enqueue_script( 'mp-ajax-popup-js', plugins_url( 'js/core/mp-ajax-popup.js', dirname(__FILE__)),  array( 'jquery' ), MP_CORE_VERSION );
				//media upload scripts
				wp_enqueue_media();
				//image uploader script
				wp_enqueue_script( 'image-upload', plugins_url( 'js/core/image-upload.js', dirname(__FILE__) ),  array( 'jquery' ), MP_CORE_VERSION );
				//Metabox scripts for duplicating fields etc
				wp_enqueue_script( 'mp-core-metabox-js', plugins_url( 'js/core/mp-core-metabox.js', dirname(__FILE__) ),  array( 'jquery', 'jquery-ui-sortable', 'mp-ajax-popup-js' ), MP_CORE_VERSION );	 	
				
				//If this script has already been localized, don't do it again. We only need it once. Global var used so other class calls don't duplicate output.
				global $mp_core_metabox_js_localized;
				
				if (!$mp_core_metabox_js_localized){
					wp_localize_script( 'mp-core-metabox-js', 'mp_core_metabox_js', array(
						'loading' => __( 'Loading...', 'mp_core' ),
						'hide' => __( 'Hide', 'mp_core' ),
						'cantremove' => __( 'Can\'t Remove', 'mp_core' )
					) );
					
					$mp_core_metabox_js_localized = true;
					
				}
				
				//Action hook alllowing for more scripts to be loaded only when this metabox is used
				do_action('mp_core_' . $this->_args['metabox_id'] . '_metabox_custom_scripts');
				
		
			}
		}
				
		/**
		 * This function adds a metabox to the Post Type passed-in to the class in the $args array
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      add_meta_box()
		 * @return   void
		 */	
		public function mp_core_add_metabox() {
			
			global $post;
			$this->_post_id = isset($post->ID) ? $post->ID : '';
						
			//Get current page
			$current_page = get_current_screen();
				
			//defaults
			$metabox_posttype = (isset($this->_args['metabox_posttype']) ? $this->_args['metabox_posttype'] : "post");
			$metabox_context = (isset($this->_args['metabox_context']) ? $this->_args['metabox_context'] : "advanced");
			$metabox_priority = (isset($this->_args['metabox_priority']) ? $this->_args['metabox_priority'] : "default");
			
			//Only create metabox if we are on the right post type
			if ( $current_page->base != 'post' || $post->post_type != $metabox_posttype){
				return;	
			}
			
			add_meta_box( 
				$this->_args['metabox_id'],
				$this->_args['metabox_title'],
				array( &$this, 'mp_core_metabox_callback' ),
				$metabox_posttype,
				$metabox_context,
				$metabox_priority
			);
		}
		
		/**
		 * This function prints the metabox content to the metabox
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      wp_nonce_field()
		 * @see      has_filter()
		 * @see      apply_filters()
		 * @see      plugin_basename()
		 * @see      get_post_meta()
		 * @see      MP_CORE_Metabox::basictext()
		 * @see      MP_CORE_Metabox::textbox()
		 * @see      MP_CORE_Metabox::textarea()
		 * @see      MP_CORE_Metabox::colorpicker()
		 * @see      MP_CORE_Metabox::mediaupload()
		 * @see      MP_CORE_Metabox::select()
		 * @see      MP_CORE_Metabox::password()
		 * @see      MP_CORE_Metabox::checkbox()
		 * @see      MP_CORE_Metabox::url()
		 * @see      MP_CORE_Metabox::date()
		 * @see      MP_CORE_Metabox::time()
		 * @see      MP_CORE_Metabox::number()		 
		 * @return   void
		 */	
		public function mp_core_metabox_callback() {
			
			global $post;
			
			$this->_post_id = isset($post->ID) ? $post->ID : '';
						
			$prev_repeater = false;
			
			//Loop through the pre-set, passed-in array of fields
			foreach ($this->_metabox_items_array as $field){
				
				//If this is a hook_anchor, it doesn not need to be processed so skip over it. It's just used for filters to hook new meta options to the right location.
				if ( $field['field_type'] == 'hook_anchor' ){
					continue;	
				}
				
				//Just realized I'm checking a nonce for EVERY field. Whoa there cowboy! Might be some overkill here....
				// Set up nonce for verification
				//wp_nonce_field( plugin_basename( __FILE__ ), $field['field_id'] . '_metabox_nonce' );	
				
				// Filter for title of this field
				$field['field_title'] = has_filter('mp_' . $field['field_id'] . '_title') ? apply_filters( 'mp_' . $field['field_id'] . '_title', $field['field_title'], $this->_post_id) : $field['field_title'];
				
				// Filter for description of this field
				$field['field_description'] = has_filter('mp_' . $field['field_id'] . '_description') ? apply_filters( 'mp_' . $field['field_id'] . '_description', $field['field_description'], $this->_post_id) : $field['field_description'];
				
				//This is the first field in a set of repeater
				if ( isset($field['field_repeater']) && $prev_repeater != $field['field_repeater']){
					
					//Just realized I'm checking a nonce for EVERY field. Whoa there cowboy! Might be some overkill here....
					// Set up nonce for verification
					//wp_nonce_field( plugin_basename( __FILE__ ), $field['field_repeater'] . '_metabox_nonce' );	
					
					//Make sure a post number has been set
					if ( isset($this->_post_id) ){
									
						//Get the array of variables stored in the database for this repeater
						$current_stored_repeater = get_post_meta( $this->_post_id, $key = $field['field_repeater'], true );
												
						//This is a brand new repeater
						$repeat_counter = 0;
						
						//Get the showhider
						$showhider = !empty($field['field_showhider']) ? 'showhider="' . $field['field_showhider'] . '"' : NULL;
						
						//Create ul container for this repeater
						echo '<ul class="repeater_container" ' . $showhider . '>';
						
						//If this repeater has had info saved to it previously
						if ($current_stored_repeater != NULL){
							
							//Loop the same amount of times the user clicked 'repeat' (including the first one that was there before they clicked 'repeat')
							foreach ($current_stored_repeater as $repeater_set) {
								
								//Should we add the "closed" class (if there's more than 1 repeater)
								$closed_class = count($current_stored_repeater) > 1 ? 'closed' : '';
								
								//Create start of div for this repeat 
								echo '<li class="' . $field['field_repeater'] . '_repeater ' . $closed_class . '"> 
									<div class="mp_repeater_handlediv handlediv" title="Click to toggle">
										<br>
									</div>
									<h3 class="mp_drag hndle">
										<div class="mp-core-repeater-title">' . __( 'Enter Info:', 'mp_core' ) . '</div>
										<div class="mp-core-repeater-values-description"></div>
										<div class="mp-core-repeater-values-ellipses">...</div>
										<div class="mp-core-repeater-description">' . __( ' Click to edit. Drag to re-order', 'mp_core' ) . '</div>
									</h3>';
								
								foreach ($this->_metabox_items_array as $thefield){
									if ( isset($thefield['field_repeater']) && $thefield['field_repeater'] == $field['field_repeater']){
										//formula to match all field in the rows they were saved to the rows they are displayed in  = $field_position_in_repeater*$number_of_repeats+$i
																				
										//If a value has been saved for this specific field
										if (isset($repeater_set[$thefield['field_id']])){
											
											//Use the saved value.
											$field_value = $repeater_set[$thefield['field_id']];
											
										} 
										//If a value has not been saved, check if there has been a passed-in value. If so use it, if not, set it to be empty
										else{
							
											//Otherwise use the passed-in, default value.
											$field_value = isset($thefield['field_value']) ? $thefield['field_value'] : '';
											
										}								
										
										//Set up the showhider attr based on whether it's parent showhidergroup repeats or if the entire repeater is in a parent showhider
										if ( isset( $thefield['field_showhider'] ) ){
											
											//If the showhider repeats with the repeater
											if ( isset( $thefield['field_showhider_repeats'] ) && $thefield['field_showhider_repeats'] ){
												$showhider = 'showhider="' . $thefield['field_repeater'] . 'AAAAA' . $repeat_counter . 'BBBBBAAAAA' . $thefield['field_showhider'] . 'BBBBB' . '"';
											}
											//If the showhider does not repeat with this repeater
											else{
												$showhider = 'showhider="' . $thefield['field_showhider'] . '"';
											}
											
										}
										else{
											$showhider = NULL;	
										}
									
										//Make array to pass to callback function
										$callback_args = array(
											'field_id' => $thefield['field_repeater'] . '[' . $repeat_counter . '][' . $thefield['field_id'] . ']', 
											'field_title' =>  $thefield['field_title'], 
											'field_description' => $thefield['field_description'], 
											'field_value' => $field_value, 
											'field_input_class' => isset($thefield['field_input_class']) ? 'mp_repeater ' . $thefield['field_input_class'] : 'mp_repeater', 
											'field_container_class' => isset($thefield['field_container_class']) ? $thefield['field_container_class'] : NULL, 
											'field_select_values' => isset($thefield['field_select_values']) ? $thefield['field_select_values'] : NULL,
											'field_default_attr' => NULL, 
											'field_required' => isset( $thefield['field_required'] ) ? $thefield['field_required'] : false,
											'field_showhider' => $showhider,
											'field_placeholder' => isset( $thefield['field_placeholder'] ) ? ' placeholder="' . $thefield['field_placeholder'] . '" ' : NULL,
											'field_conditional_id' => isset( $thefield['field_conditional_id'] ) ? $thefield['field_repeater'] . '[' . $repeat_counter . '][' . $thefield['field_conditional_id'] . ']' : NULL,
											'field_conditional_values' => isset( $thefield['field_conditional_values'] ) ? $thefield['field_conditional_values'] : NULL
										);
										
										//call function for field type (callback function name stored in $this->$field['field_type']
										$this->$thefield['field_type']( $callback_args );	
														
									}	
								}
								
								//This is the last one in a set of repeatable fields
								echo '<div class="mp_duplicate_buttons"><a class="mp_duplicate button">' . __('Add Another', 'mp_core') . '</a><a class="mp_duplicate_remove button">' . __('Remove', 'mp_core') . '</a></div>';
								echo '</li>';
								
								//bump the repeat_counter to the next number of the array
								$repeat_counter = $repeat_counter + 1;
						
							}
						}
						//This repeater has never been saved
						else{
							//Create start of div for this repeat 
							echo '<li class="' . $field['field_repeater'] . '_repeater"> <div class="handlediv" title="Click to toggle"><br></div><h3 class="mp_drag hndle">
										<div class="mp-core-repeater-title">' . __( 'Enter Info:', 'mp_core' ) . '</div>
										<div class="mp-core-repeater-values-description"></div>
										<div class="mp-core-repeater-values-ellipses">...</div>
										<div class="mp-core-repeater-description">' . __( ' Click to edit. Drag to re-order', 'mp_core' ) . '</div>
									</h3>';
							
							foreach ($this->_metabox_items_array as $thefield){
								if ( isset($thefield['field_repeater']) && $thefield['field_repeater'] == $field['field_repeater']){
									//formula to match all field in the rows they were saved to the rows they are displayed in  = $field_position_in_repeater*$number_of_repeats+$i
									
									
									//Set up the showhider attr based on whether it's parent showhidergroup repeats or if the entire repeater is in a parent showhider
									if ( isset( $thefield['field_showhider'] ) ){
										
										//If the showhider repeats with the repeater
										if ( isset( $thefield['field_showhider_repeats'] ) && $thefield['field_showhider_repeats'] ){
											$showhider = 'showhider="' . $thefield['field_repeater'] . 'AAAAA' . $repeat_counter . 'BBBBBAAAAA' . $thefield['field_showhider'] . 'BBBBB' . '"';
										}
										//If the showhider does not repeat with this repeater
										else{
											$showhider = 'showhider="' . $thefield['field_showhider'] . '"';
										}
										
									}
									else{
										$showhider = NULL;	
									}
																		
									//Make array to pass to callback function
									$callback_args = array(
										'field_id' => $thefield['field_repeater'] . '[' . $repeat_counter . '][' . $thefield['field_id'] . ']', 
										'field_title' => $thefield['field_title'], 
										'field_description' => $thefield['field_description'], 
										'field_value' => isset($thefield['field_value']) ? $thefield['field_value'] : '', 
										'field_input_class' => isset($thefield['field_input_class']) ? 'mp_repeater ' . $thefield['field_input_class'] : 'mp_repeater', 
										'field_container_class' => isset($thefield['field_container_class']) ? $thefield['field_container_class'] : NULL, 
										'field_select_values' => isset($thefield['field_select_values']) ? $thefield['field_select_values'] : NULL,
										'field_default_attr' => NULL, 
										'field_required' => isset( $thefield['field_required'] ) ? $thefield['field_required'] : false,
										'field_showhider' => $showhider,
										'field_placeholder' => isset( $thefield['field_placeholder'] ) ? ' placeholder="' . $thefield['field_placeholder'] . '" ' : NULL,
										'field_popup_help' => isset( $thefield['field_popup_help'] ) ? $thefield['field_popup_help'] : NULL,
										'field_conditional_id' => isset( $thefield['field_conditional_id'] ) ? $thefield['field_repeater'] . '[' . $repeat_counter . '][' . $thefield['field_conditional_id'] . ']' : NULL,
										'field_conditional_values' => isset( $thefield['field_conditional_values'] ) ? $thefield['field_conditional_values'] : NULL
									);
									
									//call function for field type (callback function name stored in $this->$field['field_type']
									$this->$thefield['field_type']( $callback_args );	
													
								}	
							}
							
							//This is the last one in a set of repeatable fields
							echo '<div class="mp_duplicate_buttons"><a class="mp_duplicate button">' . __('Add Another', 'mp_core') . '</a><a class="mp_duplicate_remove button">' . __('Remove', 'mp_core') . '</a>';
							echo '</li>';
						}
						
						//close repeater container
						echo '</ul>';
		
						//Make a note that we have handled this repeater already so we don't do it again. We do this by storing the name of the current repeater 
						$prev_repeater = $field['field_repeater'];
					}
				}
				// This is not the first field in a repeater
				else{
					//And it's also not a repeater at all. It is a single field.
					if ( !isset($field['field_repeater']) ){
	
						// Use mp_core_get_post_meta_or_never_been_saved to retrieve an existing value from the database or 'never_been_saved' if it was never previously saved.
						$value = mp_core_get_post_meta_or_never_been_saved( $this->_post_id, $key = $field['field_id'] );
						
						//If this field has never been saved before 
						if ( $value == 'never_been_saved_73698363746983746' ){
							
							//Set the value to be the pre-set value for this field passed-in.					
							$value = isset($field['field_value']) ? $field['field_value'] : '';
						}
						
						//If field required hasn't been set, set it to be false
						$field_required = isset( $field['field_required'] ) ? $field['field_required'] : false;
						//if $field_select_values hasn't been set, set it to be NULL
						$field_select_values = isset($field['field_select_values']) ? $field['field_select_values'] : NULL;
						//set the preset value to the passed in value
						$preset_value = isset($field['field_value']) ? $field['field_value'] : '';
						
						//Default Field Attribute String:
						$field_default_attr = isset($field['field_value']) ? ' mp_default_value="' . $field['field_value'] . '" ' : ' mp_default_value="" ';
						
						//set the showhider
						$showhider_value = isset($field['field_showhider']) ? 'showhider="' . $field['field_showhider'] . '"' : '';
						//set the field container class
						$field_container_class = isset($field['field_container_class']) ? $field['field_container_class'] : '' . ' ' . $field['field_id'];
						//set the field input class
						$field_input_class = isset($field['field_input_class']) ? $field['field_input_class'] . ' ' . $field['field_id'] : $field['field_id'];
						//set the placeholder
						$placeholder_value = isset($field['field_placeholder']) ? 'placeholder="' . $field['field_placeholder'] . '"' : '';
						//set the popup help
						$popup_help = isset($field['field_popup_help']) ? $field['field_popup_help'] : NULL;
						//Set the field that we want to check the condition of before showing this field 
						//These 2 options control a js function which hides fields dependant on the value of another field - conditional logic
						$field_conditional_id = isset( $field['field_conditional_id'] ) ? $field['field_conditional_id'] : NULL;
						$field_conditional_values = isset( $field['field_conditional_values'] ) ? $field['field_conditional_values'] : NULL;
						
						//Make array to pass to callback function
						$callback_args = array(
							'field_id' => $field['field_id'], 
							'field_title' => $field['field_title'], 
							'field_description' => $field['field_description'], 
							'field_value' => $value, 
							'field_input_class' => $field_input_class, 
							'field_container_class' => $field_container_class, 
							'field_select_values' => $field_select_values, 
							'field_default_attr' => $field_default_attr , 
							'field_required' => $field_required,
							'field_showhider' => $showhider_value,
							'field_placeholder' => $placeholder_value,
							'field_popup_help' => $popup_help,
							'field_conditional_id' => $field_conditional_id,
							'field_conditional_values' => $field_conditional_values
						);
						
						//call function for field type (function name stored in $this->$field['field_type']
						$this->$field['field_type']( $callback_args );
					}
				}
			}
		}
				
		/**
		 * When the post is saved, this function saves our metabox data
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      wp_verify_nonce()
		 * @see      plugin_basename()
		 * @see      current_user_can()
		 * @see      update_post_meta()
		 * @see      wp_kses()
		 * @see      wpautop()
		 * @see      sanitize_text_field()	 
		 * @return   void
		 */	
		public function mp_core_save_data() {
			
			if( !session_id() )
				session_start();
				
			//Check if post type has been set
			$this_post_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : NULL;				
			
			//If we are saving this post type - we dont' want to save every single metabox that has been created using this class - only this post type
			if ( $this->_args['metabox_posttype'] != $this_post_type ) {
				return;	
			}
									
		    global $post;
		    $this->_post_id = isset($post->ID) ? $post->ID : '';
		   
		    // verify if this is an auto save routine. 
		    // If it is our form has not been submitted, so we dont want to do anything
		  	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
				return;
			
			//these_repeater_fields variable holds repeated values to be saved in database
			$these_repeater_field_id_values = array();
			//Set default for $repeater to false
			$prev_repeater = false;
			
			//Loop through each item in the passed array
			foreach ($this->_metabox_items_array as $field){
					
				//If this is a hook_anchor, it doesn not need to be processed so skip over it. It's just used for filters to hook new meta options to the right location.
				if ( $field['field_type'] == 'hook_anchor' ){
					continue;	
				}
				
				//Just realized I'm checking a nonce for EVERY field. Whoa there cowboy! Might be some overkill here....
				// verify this came from our screen and with proper authorization,
				// because save_post can be triggered at other times
				//if ( isset($_POST[$field['field_id'] . '_metabox_nonce']) ){
				//	if ( !wp_verify_nonce( $_POST[$field['field_id'] . '_metabox_nonce'], plugin_basename( __FILE__ ) ) )
				//	  return;
				//}else{
				//	return;
				//}
				
				// Check permissions
				if ( $this->_args['metabox_posttype'] == $_POST['post_type'] ) {
					if ( !current_user_can( 'edit_page', $this->_post_id ) )
						return;
				}
				else{
					if ( !current_user_can( 'edit_post', $this->_post_id ) )
						return;
				}
				
				// OK, we're authenticated: we need to find and save the data
				
				//If the passed array has the field_repeater value set, than loop through all of the fields with that repeater
				if ( isset($field['field_repeater']) ){
					//If this repeater has not already been looped through and saved, loop through and save it.
					//Because if this is a repeater, the whole repeater gets looped through and saved and never touched again
					if ($prev_repeater != $field['field_repeater']){
						//But first check if the previous field was the last in a set of repeaters. If so, update that set of repeaters now
						if ($prev_repeater != false){
							
							// Update $these_repeater_field_id_values 
							update_post_meta($this->_post_id, $prev_repeater, $these_repeater_field_id_values);
							
							//Reset these_repeater_field_id_values
							$these_repeater_field_id_values = array();
						}
						
						//Set $prev_repeater to current field repeater 
						$prev_repeater = $field['field_repeater'];
						
						//Store all the post values for this repeater in $these_repeater_field_id_values. 
						$these_repeater_field_id_values = isset( $_POST[$field['field_repeater']] ) ? $_POST[$field['field_repeater']] : NULL;
						
						if ( !is_array( $these_repeater_field_id_values ) ){
							continue;	
						}
						
						//Sanitize user input for this repeater field and add it to the $data array
						$allowed_tags = wp_kses_allowed_html( 'post' );
						
						//Set default for repeat counter
						$repeater_counter = 0;
							
						//Loop through all of the repeats in the $_POST with this repeater
						foreach( $these_repeater_field_id_values as $repeater ){
							
							//Loop through all of the repeats in the $_POST with this repeater
							foreach( $repeater as $field_id => $field_value ){
																
								//Loop through each passed in fields so we can find the "type"
								foreach ( $this->_metabox_items_array as $child_loop_field ){
									
									if ( isset($child_loop_field['field_repeater']) ){
										
										//If the current passed-in field is a checkbox
										if( $child_loop_field['field_type'] == 'checkbox' ){
											
											//If it does NOT exist in the submitted POST for this repeat	
											if ( !array_key_exists( $child_loop_field['field_id'], $repeater ) ){
												//Set it to be empty so it at least saves SOMETHING (because empty checkboxes don't even get submitted through POST)
												//This way we know whether this checkbox has ever been saved, or if it was just saved to be empty/unchecked.
												$these_repeater_field_id_values[$repeater_counter][$child_loop_field['field_id']] = '';
											}
											
											
										}
											
										//If the current iteration of passed-in field's repeater matches the repeater we're on in the master loop
										if ( $child_loop_field['field_repeater'] == $field['field_repeater']){
											
											//If this child loop's id matched the current one in the POST array
											if ( $child_loop_field['field_id'] == $field_id ){
												
												//Sanitize each field according to its type
												if ( $child_loop_field['field_type'] == 'textarea' ){
													$these_repeater_field_id_values[$repeater_counter][$field_id] = wp_kses( mp_core_fix_quotes( esc_html( $field_value ) ), $allowed_tags ); 
												}
												elseif ( $child_loop_field['field_type'] == 'multiple_checkboxes' ){
													$these_repeater_field_id_values[$repeater_counter][$field_id] = json_encode( $field_value ); 
												}
												elseif( $child_loop_field['field_type'] == 'wp_editor' ){
													$these_repeater_field_id_values[$repeater_counter][$field_id] = wp_kses( mp_core_fix_quotes( mp_core_fix_nbsp( esc_html( wpautop( $field_value, true ) ) ) ), $allowed_tags ); 									
												}
												else{
													$these_repeater_field_id_values[$repeater_counter][$field_id] = mp_core_fix_quotes( sanitize_text_field( $field_value ) );	
												}
												
											}
											
										}
									}
								}
							}
							//Increment repeat counter
							$repeater_counter = $repeater_counter + 1;		
						}	
										
					}
				}
				//This is not a repeater field.
				else{
					//But if the previous field was a repeater, update that repeater now
					if ($prev_repeater != false){
												
						// Update $these_repeater_field_id_values 
						update_post_meta($this->_post_id, $prev_repeater, $these_repeater_field_id_values);
						
						//Set $prev_repeater back to false
						$prev_repeater = false;
						//Set $these_repeater_field_id_values back to be an empty array
						$these_repeater_field_id_values = array();
					}
					
					//Update single post:
					//get value from $_POST
					if ( $field['field_type'] == 'checkbox' || $field['field_type'] == 'multiple_checkboxes' ){
						$post_value = isset( $_POST[$field['field_id']] ) ? $_POST[$field['field_id']] : '';
					}
					else if ( isset($_POST[$field['field_id']] ) ){
						$post_value = $_POST[$field['field_id']];
					}
					else{
						continue;
					}
					
					//sanitize user input
					$allowed_tags = wp_kses_allowed_html( 'post' );
					
					if ( $field['field_type'] == 'textarea' ){
						$data = wp_kses( mp_core_fix_quotes( mp_core_fix_nbsp( esc_html($post_value ) ) ), $allowed_tags );
					}
					elseif ( $field['field_type'] == 'multiple_checkboxes' ){
						$data = json_encode( $post_value ); 
					}
					elseif( $field['field_type'] == 'wp_editor' ){
						$data = wp_kses( mp_core_fix_quotes( mp_core_fix_nbsp( esc_html( wpautop( $post_value, true ) ) ) ), $allowed_tags );
					}
					else{
						$data = mp_core_fix_quotes( sanitize_text_field( $post_value ) );
					}
					
					// Update $data 
					update_post_meta($this->_post_id, $field['field_id'], $data);
					
				}
				
			}//End of foreach through $this->_metabox_items_array
							
			//If the final field was a repeater, update that repeater now
			if ($prev_repeater != false){
				
				// Update $these_repeater_field_id_values 
				update_post_meta($this->_post_id, $prev_repeater, $these_repeater_field_id_values);
				
			}	
			
			
		}
		
		/**
		 * basictext field. Parameters in this function match all below
		 *
		 * @access   public
		 * @since    1.0.0
   		 * @param    string $field_id Required. This must be a unique name with no spaces
		 * @param    string $field_title Required. This is the title of the field that will display to the user
		 * @param    string $field_description Required. The user will see this description above this field
		 * @param    string $value Required. The value displayed in this field
		 * @param    string $classname Required. The name of the  css class for this field
		 * @return   void
		*/
		function basictext( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
						
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  .  $conditional_output . '><div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
			echo '</div></div>';
			echo '</div>'; 
		}
		
		/**
		 * repeatertitle field. Parameters in this function match all below
		 *
		 * @access   public
		 * @since    1.0.0
   		 * @param    string $field_id Required. This must be a unique name with no spaces
		 * @param    string $field_title Required. This is the title of the field that will display to the user
		 * @param    string $field_description Required. The user will see this description above this field
		 * @param    string $value Required. The value displayed in this field
		 * @param    string $classname Required. The name of the  css class for this field
		 * @return   void
		*/
		function repeatertitle( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . ' repeatertitle" ' . $field_showhider  . $conditional_output . '><div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
			
			echo '</div></div>';
			echo '</div>'; 
		}
		
		/**
		* textbox field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function textbox( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
				'field_placeholder' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
			echo '<input type="text" id="' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . '" ' . $field_placeholder . ' name="' . $field_id . '" ' . $field_default_attr . ' mp_saved_value="' . $field_value . '"  class="' . $field_input_class . '" value="' . htmlentities( $field_value ) . '" '. $field_required_output . '/>';
			echo '</div>'; 
		}
		
		/**
		* hidden field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function hidden( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			echo '<div style="display:none;" class="mp_field mp_field_hidden mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
			echo '<input type="hidden" id="' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . '" ' . $field_placeholder . ' name="' . $field_id . '" ' . $field_default_attr . ' mp_saved_value="' . $field_value . '"  class="' . $field_input_class . '" value="' . htmlentities( $field_value ) . '" '. $field_required_output . '/>';
			echo '</div>'; 
		}
		
		/**
		* password field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function password( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
			echo '<input type="password" id="' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . '" ' . $field_placeholder . ' name="' . $field_id . '" ' . $field_default_attr . ' mp_saved_value="' . $field_value . '"  class="' . $field_input_class . '" value="' . htmlentities( $field_value ) . '" '. $field_required_output . ' />';
			echo '</div>'; 
		}
		
		/**
		* checkbox field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function checkbox( $args ){
						
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			//If this has been saved before
			if ( $field_value == '' || mp_core_value_exists( $field_value ) ){
				$field_value = $field_value;
			}
			//If this has never been saved before
			else{
				//If there is no value saved but there is a default value, use that value
				$field_value = mp_core_value_exists( $field_default_attr ) ? $field_default_attr : $field_value;
			}
			
			//Note! We don't do a "field_default_attr" check for checkboxes like we do with other field<br />
			//because we can't set a true default (because false is empty so it would just always be true no matter what).
				
			$checked = empty($field_value) ? '' : 'checked';
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
			echo '<input type="checkbox" id="' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . '" name="' . $field_id . '" class="' . $field_input_class . '" value="' . $field_id . '" ' . $checked . ' />';
			echo '</div>'; 
		}
		
		/**
		* Multiple checkboxes field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function multiple_checkboxes( $args ){
						
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			//If this has been saved before
			if ( $field_value == '' || mp_core_value_exists( $field_value ) ){
				$field_values = json_decode( $field_value );
			}
			//If this has never been saved before
			else{
				//If there is no value saved but there is a default value, user that value
				$field_values = mp_core_value_exists( $field_default_attr ) ? $field_default_attr : $field_value;
			}
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
						
			//Loop through each checkbox option
			foreach( $field_select_values as $field_slug => $field_readable_name ){
				
				//Check if there is a saved value for this checkbox
				$checked = is_array( $field_values ) && in_array( $field_slug, $field_values ) ? 'checked' : '';
				
				//Show the checkbox
				echo '<input type="checkbox" id="' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . '" name="' . $field_id . '[]" class="' . $field_input_class . '" value="' . $field_slug . '" ' . $checked . ' /><span class="mp-multiple-checkboxes-desc">' . $field_readable_name . '</span>';
				
			}
			echo '</div>'; 
		}
		
		/**
		* url field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function url( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
			echo '<input type="url" id="' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . '" ' . $field_placeholder . ' name="' . $field_id . '" ' . $field_default_attr . ' mp_saved_value="' . $field_value . '"  class="' . $field_input_class . '" value="' . htmlentities( $field_value ) . '" '. $field_required_output . ' />';
			echo '</div>'; 
		}
		
		/**
		* date field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function date( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
			echo '<input type="date" id="' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . '" ' . $field_placeholder . ' name="' . $field_id . '" ' . $field_default_attr . ' mp_saved_value="' . $field_value . '"  class="' . $field_input_class . '" value="' . htmlentities( $field_value ) . '" size="30" '. $field_required_output . ' />';
			echo '</div>'; 
		}
		
		/**
		* time field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function time( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
			echo '<input type="time" id="' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . '" ' . $field_placeholder . ' name="' . $field_id . '" ' . $field_default_attr . ' mp_saved_value="' . $field_value . '"  class="' . $field_input_class . '" value="' . htmlentities( $field_value ) . '" size="50" '. $field_required_output . ' />';
			echo '</div>'; 
		}
		
		/**
		* number field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function number( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
			echo '<input type="number" id="' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . '" ' . $field_placeholder . ' name="' . $field_id . '" ' . $field_default_attr . ' mp_saved_value="' . $field_value . '"  class="' . $field_input_class . '" value="' . htmlentities( $field_value ) . '" size="20" '. $field_required_output . ' />';
			echo '</div>'; 
		}
		
		/**
		* textarea field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function textarea( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
			echo '</div></div>';
			echo '<textarea id="' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . '" name="' . $field_id . '" ' . $field_placeholder . ' ' . $field_default_attr . ' mp_saved_value="' . $field_value . '"  class="' . $field_input_class . '" rows="4" cols="50" '. $field_required_output . '>';
			echo $field_value;
			echo '</textarea>';
			echo '</div>'; 
		}
		
		/**
		* WordPress editor field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function wp_editor( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
						
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
			echo '</div></div>';
			echo wp_editor( html_entity_decode($field_value) , 'mp_core_wp_editor_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ), $settings = array('textarea_rows' => 6, 'textarea_name' => $field_id));			
			echo '</div>'; 
		}
		
		/**
		* select field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function select( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
			?>
			
            <select name="<?php echo $field_id; ?>" class="<?php echo $field_input_class; ?>" <?php echo $field_required_output; ?> <?php echo $field_default_attr; ?> mp_saved_value="<?php echo $field_value; ?>">
                <option value=""></option>
                <?php foreach ( $field_select_values as $select_value => $select_text) : ?>
                <option value="<?php echo esc_attr( $select_value ); ?>" <?php selected( $select_value, $field_value ); ?>>
                    <?php echo isset($select_text) ? esc_attr( $select_text ) : esc_attr( $select_value ); ?>
                </option>
                <?php endforeach; ?>
            </select>
			
			<?php        
			echo '</div>'; 
		}
		
		/**
		* datalist field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function datalist( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
			?>
			<input name="<?php echo $field_id; ?>" list="<?php echo $field_id; ?>" class="<?php echo $field_input_class; ?>" <?php echo $field_required_output; ?> value="<?php echo $field_value; ?>" <?php echo $field_default_attr; ?> mp_saved_value="<?php echo $field_value; ?>"/>
            <datalist id="<?php echo $field_id; ?>">
                <option value="">
                <?php foreach ( $field_select_values as $select_value => $select_text) : ?>
                	<option value="<?php echo isset($select_text) ? esc_attr( $select_text ) : esc_attr( $select_value ); ?>">
                <?php endforeach; ?>
            </datalist>
			
			<?php        
			echo '</div>'; 
		}
		
		/**
		* radio field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function radio( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
			
			//Loop through each Radio Button 
			foreach ( $field_select_values as $select_value => $select_text) : ?>
                <div class="mp-core-radio-element">
                    <div class="mp-core-radio-button">
                    	<input type="radio" class="<?php echo $field_input_class; ?>" name="<?php echo $field_id; ?>" <?php echo $field_default_attr; ?> mp_saved_value="<?php echo $field_value; ?>" value="<?php echo esc_attr( $select_value ); ?>" <?php checked( $select_value, $field_value ); ?> <?php echo $field_required_output; ?>>
                    </div>
                    <div class="mp-core-radio-description">
						<?php 
                        do_action('mp_core_metabox_before_' . $select_value . '_radio_description'); 
                        echo isset($select_text) ? esc_attr( $select_text ) : esc_attr( $select_value ); 
                    	?>
                    </div>
                </div> 	<?php 
			endforeach; 
					
			echo '</div>'; 
		}
		
		/**
		* range field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function input_range( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
			?>
            <input type="range" name="<?php echo $field_id; ?>" class="<?php echo $field_input_class; ?>" min="0" max="100" <?php echo $field_default_attr; ?> mp_saved_value="<?php echo $field_value; ?>" value ="<?php echo $field_value; ?>" <?php echo $field_required_output; ?> ><input type="number" class="<?php echo $field_input_class; ?>_output mp_core_input_range_output">
			<?php        
			echo '</div>'; 
		}
		
		/**
		* range field without a number preview beside it
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function input_range_no_number_preview( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</div></div>';
			?>
            <input type="range" name="<?php echo $field_id; ?>" class="<?php echo $field_input_class; ?>" min="0" max="100" mp_default_value="'<?php echo $field_default_attr; ?>" value ="<?php echo $field_value; ?>" <?php echo $field_required_output; ?> >
			<?php        
			echo '</div>'; 
		}
		
		/**
		* colorpicker field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function colorpicker( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
			echo '</div></div>';
			echo '<input type="text" class="of-color ' . $field_input_class . '" id="' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . '" ' . $field_default_attr . ' mp_saved_value="' . $field_value . '"  name="' . $field_id . '" value="' . htmlentities( $field_value ) . '" '. $field_required_output . ' />';
			echo '</div>'; 
		}
		
		/**
		* mediaupload field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function mediaupload( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
						
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
			echo '</div></div>';
			?>       
			<!-- Upload button and text field -->
            <div class="mp_media_upload">
                <input class="custom_media_url <?php echo $field_input_class; ?>" id="<?php echo str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ); ?>" <?php echo $field_placeholder; ?> type="text" name="<?php echo $field_id; ?>" <?php echo $field_default_attr; ?> mp_saved_value="<?php echo $field_value; ?>" value="<?php echo esc_attr( $field_value ); ?>" <?php echo $field_required_output; ?>>
                <a href="#" class="button custom_media_upload"><?php _e('Upload', 'mp_core'); ?></a>
			</div>
			<?php
			//Image thumbnail
			if ( isset($field_value) ){
				$ext = pathinfo($field_value, PATHINFO_EXTENSION);
				if ($ext == 'png' || $ext == 'jpg'){
					?><img class="custom_media_image" src="<?php echo $field_value; ?>" style="display:inline-block;" /><?php
				}else{
					?><img class="custom_media_image" src="<?php echo $field_value; ?>" style="display: none;" /><?php
				}
			}
		echo '</div>';   
	
		}
		
		/**
		* iconfontpicker field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function iconfontpicker( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Add mp_required to the classes if it is required
			$field_input_class = $field_required == true ? $field_input_class . ' mp_required' : $field_input_class;
			
			//Set the output for html5 required field
			$field_required_output = $field_required == true ? 'required="required"' : '';
			
			
			
			//Get the non-repeater field ID and use it as a class for the icon
			$icon_class = explode( '[', $field_id );
			$icon_class = explode( ']', $icon_class[2] );
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo !empty( $field_popup_help ) ? '<div class="mp-core-popup-help-icon" mp_ajax_popup="' . $field_popup_help . '"></div>' : NULL;
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
			echo '</div></div>';
			
			//Font thumbnail
			echo '<div class="mp_font_icon_thumbnail">';
				echo '<div class="' . $field_value . ' ' . $icon_class[0] . '">';
					echo '<div class="mp-iconfontpicker-title" >' . $field_value . '</div>';
				echo '</div>';
			echo '</div>';
			
			?>       
			<!-- Upload button and text field -->
            <div class="mp-icon-font-select">
                <input class="custom_media_url <?php echo $field_input_class; ?>" id="<?php echo str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ); ?>" <?php echo $field_placeholder; ?> type="hidden" name="<?php echo $field_id; ?>" <?php echo $field_default_attr; ?> mp_saved_value="<?php echo $field_value; ?>" value="<?php echo esc_attr( $field_value ); ?>" <?php echo $field_required_output; ?>>
                <a href="#TB_inline?width=750&inlineId=mp-thickbox-<?php echo str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ); ?>" class="thickbox button"><?php _e('Select', 'mp_core'); ?></a>
			</div>
            
			<?php
		echo '</div>';   
		
		?>
		
		<!--Create the hidden div which will display in the Thickbox -->	
        <div id="mp-thickbox-<?php echo str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ); ?>" style="display: none;">
            <div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
            	<div class="<?php echo str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ); ?>">
					<?php
                    foreach( $field_select_values as $select_value ){
                        
                        echo '<a href="#" class="mp_iconfontpicker_item">';
													
							echo '<div class="' . $select_value . ' ' . $icon_class[0] . '">';
								
								echo '<div class="mp-iconfontpicker-title" >' . $select_value . '</div>';
							
							echo '</div>';
						
						echo '</a>';
                             
                    } 
                    ?>
                </div>   
            </div>
        </div>
        
        <?php
	
		}
		
		/**
		 * help field. Parameters in this function match all below
		 *
		 * @access   public
		 * @since    1.0.0
   		 * @param    string $field_id Required. This must be a unique name with no spaces
		 * @param    string $field_title Required. This is the title of the field that will display to the user
		 * @param    string $field_description Required. The user will see this description above this field
		 * @param    string $value Required. The value displayed in this field
		 * @param    string $classname Required. The name of the  css class for this field
		 * @return   void
		*/
		function help( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<div style="clear: both;"></div>';
			foreach ($field_select_values as $help_array){
				echo '<div class="mp_core_help">';		
					echo '<a class="mp_core_help_' . $help_array['type'] . '" alt="' . $help_array['link_text'] . '" href="' . $help_array['link'] . '" target="' . $help_array['target'] . '" >' . $help_array['link_text'] . '</a>';
				echo '</div>';		
			}
			echo '<div style="clear: both;"></div>';
			echo '</div></div>';
			echo '</div>'; 
		}
		
		/**
		 * showhider field. Used to show or hide options. Parameters in this function match all below
		 *
		 * @access   public
		 * @since    1.0.0
   		 * @param    string $field_id Required. This must be a unique name with no spaces
		 * @param    string $field_title Required. This is the title of the field that will display to the user
		 * @param    string $field_description Required. The user will see this description above this field
		 * @param    string $value Required. The value displayed in this field
		 * @param    string $classname Required. The name of the  css class for this field
		 * @return   void
		*/
		function showhider( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Make each array item into its own variable
			extract( $args, EXTR_SKIP );
			
			//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			$conditional_output = !empty( $field_conditional_id ) ? ' mp_conditional_field_id="' . $field_conditional_id . '" mp_conditional_field_values="' . implode(', ', $field_conditional_values ) . '" ' : NULL;
			
			echo '<div class="mp_field mp_field_' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . ' ' . $field_container_class . '" ' . $field_showhider  . $conditional_output . '> <div class="mp_title"><div for="' . $field_id . '">';
			echo '<div style="clear: both;"></div>';
			
			echo '<a class="mp_core_showhider_button closed" alt="' . $field_title . '" showhidergroup="' . str_replace( array( '[', ']' ), array('AAAAA', 'BBBBB'), $field_id ) . '">' . $field_title . '</a>';
			
			echo '<div style="clear: both;"></div>';
			echo '</div></div>';
			echo '</div>'; 
		}
		
		/**
		* customfieldtype field
		*
		* @access   public
		* @since    1.0.0
		* @return   void
		*/
		function customfieldtype( $args ){
			
			//Set defaults for args		
			$args_defaults = array(
				'field_id' => NULL, 
				'field_title' => NULL,
				'field_description' => NULL,
				'field_value' => NULL,
				'field_input_class' => NULL,
				'field_container_class' => NULL,
				'field_select_values' => NULL,
				'field_default_attr' => NULL,
				'field_required' => NULL,
				'field_showhider' => NULL,
                'field_placeholder' => NULL,
				'field_popup_help' => NULL,
				'field_conditional_id' => NULL,
				'field_conditional_values' => NULL,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
						
			//Use this hook to pass in your inpur field and whatever else you want this custom field to look like.
			do_action('mp_core_' . $this->_args['metabox_id'] . '_customfieldtype', $args);
		}
		
	}
}