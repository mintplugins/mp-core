<?php
/**
 * Class to create new metaboxes
 *
 */
if (!class_exists('MP_CORE_Metabox')){
	class MP_CORE_Metabox{
				
		protected $_args;
		protected $_metabox_items_array = array();
		
		public function __construct($args, $items_array){
								
			$this->_args = $args;
			$this->_metabox_items_array = $items_array;
			
			add_action( 'add_meta_boxes', array( $this, 'mp_core_add_metabox' ) );
			add_action( 'save_post', array( $this, 'mp_core_save_data' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'mp_core_enqueue_scripts' ) );
			
		}
		
		public function mp_core_enqueue_scripts(){
			
			//Get current page
			$current_page = get_current_screen();
			
			//Only load if we are on a post based page
			if ( $current_page->base == 'post' ){
				//mp_core_metabox_css
				wp_enqueue_style( 'mp_core_metabox_css', plugins_url('css/core/mp-core-metabox.css', dirname(__FILE__)) );
				//color picker scripts
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker-load', plugins_url( 'js/core/wp-color-picker.js', dirname(__FILE__)),  array( 'jquery', 'wp-color-picker') );
				//media upload scripts
				wp_enqueue_media();
				//image uploader script
				wp_enqueue_script( 'image-upload', plugins_url( 'js/core/image-upload.js', dirname(__FILE__) ),  array( 'jquery' ) );
				//duplicator script
				wp_enqueue_script( 'field-duplicator', plugins_url( 'js/core/field-duplicator.js', dirname(__FILE__) ),  array( 'jquery' ) );	
				//drag and drop sortable script - http://farhadi.ir/projects/html5sortable/
				wp_enqueue_script( 'sortable', plugins_url( 'js/core/sortable.js', dirname(__FILE__) ),  array( 'jquery' ) );	
				wp_enqueue_script( 'mp_set_sortables', plugins_url( 'js/core/mp-set-sortables.js', dirname(__FILE__) ),  array( 'jquery', 'sortable' ) );	
				//do_action
				do_action('mp_core_' . $this->_args['metabox_id'] . '_metabox_custom_scripts');
			}
		}
		
		/* Adds a box to the main column on the Post and Page edit screens */	
		public function mp_core_add_metabox() {
			
			global $post;
			$this->_post_id = isset($post->ID) ? $post->ID : '';
			
			//defaults
			$metabox_posttype = (isset($this->_args['metabox_posttype']) ? $this->_args['metabox_posttype'] : "post");
			$metabox_context = (isset($this->_args['metabox_context']) ? $this->_args['metabox_context'] : "advanced");
			$metabox_priority = (isset($this->_args['metabox_priority']) ? $this->_args['metabox_priority'] : "default");
			
			add_meta_box( 
				$this->_args['metabox_id'],
				$this->_args['metabox_title'],
				array( &$this, 'mp_core_metabox_callback' ),
				$metabox_posttype,
				$metabox_context,
				$metabox_priority
			);
		}
		
		/* Prints the box content */	
		public function mp_core_metabox_callback() {
			
			global $post;
			$this->_post_id = isset($post->ID) ? $post->ID : '';
			
			$prev_repeater = false;
			
			//Loop through the pre-set, passed-in array of fields
			foreach ($this->_metabox_items_array as $field){
				
				// Use nonce for verification
				wp_nonce_field( plugin_basename( __FILE__ ), $field['field_id'] . '_metabox_nonce' );	
				
				// Filter for title of this field
				$field['field_title'] = has_filter('mp_' . $field['field_id'] . '_title') ? apply_filters( 'mp_' . $field['field_id'] . '_title', $field['field_title'], $this->_post_id) : $field['field_title'];
				
				// Filter for description of this field
				$field['field_description'] = has_filter('mp_' . $field['field_id'] . '_description') ? apply_filters( 'mp_' . $field['field_id'] . '_description', $field['field_description'], $this->_post_id) : $field['field_description'];
				
				//This is the first field in a set of repeater
				if ( isset($field['field_repeater']) && $prev_repeater != $field['field_repeater']){
					
					// Use nonce for verification
					wp_nonce_field( plugin_basename( __FILE__ ), $field['field_repeater'] . '_metabox_nonce' );	
					
					//Make sure a post number has been set
					if ( isset($this->_post_id) ){
									
						//Get the array of variables stored in the database for this repeater
						$current_stored_repeater = get_post_meta( $this->_post_id, $key = $field['field_repeater'], $single = true );
						
						//This is a brand new repeater
						$repeat_counter = 0;
						
						//Create ul container for this repeater
						echo '<ul class="repeater_container">';
						
						//If this repeater has had info saved to it previously
						if ($current_stored_repeater != NULL){
							
							//Loop the same amount of times the user clicked 'repeat' (including the first one that was there before they clicked 'repeat')
							foreach ($current_stored_repeater as $repeater_set) {
						
								//Create start of div for this repeat 
								echo '<li class="' . $field['field_repeater'] . '_repeater">';
								
								foreach ($this->_metabox_items_array as $thefield){
									if ( isset($thefield['field_repeater']) && $thefield['field_repeater'] == $field['field_repeater']){
										//formula to match all field in the rows they were saved to the rows they are displayed in  = $field_position_in_repeater*$number_of_repeats+$i
										
										//set variables for new callback field
										$field_id           = $thefield['field_repeater'] . '[' . $repeat_counter . '][' . $thefield['field_id'] . ']';
										$field_title        = $thefield['field_title'];
										$field_description  = $thefield['field_description'];
										
										//If a value has been saved
										if (isset($repeater_set[$thefield['field_id']])){
											//If this is an empty checkbox, set the field value to be empty
											if ($thefield['field_type'] == 'checkbox' && empty($repeater_set[$thefield['field_id']])){
												$field_value = '';
											}
											//Otherwise use the saved value.
											else{
												$field_value = $repeater_set[$thefield['field_id']];
											}
										} 
										//If a value has not been saved, check if there has been a passed-in value. If so use it, if not, set it to be empty
										else{
											 $field_value = isset($thefield['field_value']) ? $thefield['field_value'] : '';
										}
										
										$field_class        = 'mp_repeater';
										$field_select_values = isset($thefield['field_select_values']) ? $thefield['field_select_values'] : NULL;
										$field_preset_value = isset($thefield['field_value']) ? $thefield['field_value'] : '';
										
										//call function for field type (callback function name stored in $this->$field['field_type']
										$this->$thefield['field_type']( $field_id, $field_title, $field_description, $field_value, $field_class, $field_select_values, $field_preset_value);	
														
									}	
								}
								
								//This is the last one in a set of repeatable fields
								echo '<div class="mp_duplicate_buttons"><a class="mp_duplicate button">' . __('Add New', 'mp_core') . '</a><a class="mp_duplicate_remove button">' . __('Remove', 'mp_core') . '</a><a href="#" class="mp_drag button">' . __('Drag Me', 'mp_core') . '</a></div>';
								echo '</li>';
								
								//bump the repeat_counter to the next number of the array
								$repeat_counter = $repeat_counter + 1;
						
							}
						}
						//This repeater has never been saved
						else{
							//Create start of div for this repeat 
							echo '<li class="' . $field['field_repeater'] . '_repeater">';
							
							foreach ($this->_metabox_items_array as $thefield){
								if ( isset($thefield['field_repeater']) && $thefield['field_repeater'] == $field['field_repeater']){
									//formula to match all field in the rows they were saved to the rows they are displayed in  = $field_position_in_repeater*$number_of_repeats+$i
									
									//set variables for new callback field
									$field_id           = $thefield['field_repeater'] . '[' . $repeat_counter . '][' . $thefield['field_id'] . ']';
									$field_title        = $thefield['field_title'];
									$field_description  = $thefield['field_description'];
									$field_value        = isset($thefield['field_value']) ? $thefield['field_value'] : '';
									$field_class        = 'mp_repeater';
									$field_select_values = isset($thefield['field_select_values']) ? $thefield['field_select_values'] : NULL;
									$field_preset_value =  isset($thefield['field_value']) ? $thefield['field_value'] : '';
									
									//call function for field type (callback function name stored in $this->$field['field_type']
									$this->$thefield['field_type']( $field_id, $field_title, $field_description, $field_value, $field_class, $field_select_values, $field_preset_value);	
													
								}	
							}
							
							//This is the last one in a set of repeatable fields
							echo '<div class="mp_duplicate_buttons"><a class="mp_duplicate button">' . __('Add New', 'mp_core') . '</a><a class="mp_duplicate_remove button">' . __('Remove', 'mp_core') . '</a><a href="#" class="mp_drag button">' . __('Drag Me', 'mp_core') . '</a></div>';
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
						//If this post has been saved previously
						if ( isset($_GET['post'])){
							// Use get_post_meta to retrieve an existing value from the database and use the value for the form
							$value = get_post_meta( $this->_post_id, $key = $field['field_id'], $single = true );
							// If this is not a checkbox, set any empty settings to be the values set in the passed-in array, otherwise, leave them empty.
							if ($field['field_type'] != "checkbox"){
								$value = !empty($value) ? $value : $field['field_value'];
							}
						//If this post has never been saved before, set value to the passed-in value - unless there hasn't been a value passed in. In that case make it empty
						}else{
							$value = isset($field['field_value']) ? $field['field_value'] : '';
						}
						//if $field_select_values hasn't been set, set it to be NULL
						$field_select_values = isset($field['field_select_values']) ? $field['field_select_values'] : NULL;
						//set the preset value to the passed in value
						$preset_value = isset($field['field_value']) ? $field['field_value'] : '';
						
						//call function for field type (function name stored in $this->$field['field_type']
						$this->$field['field_type']( $field['field_id'], $field['field_title'], $field['field_description'], $value, $field['field_id'], $field_select_values, $preset_value);
					}
				}
			}
		}
		
		/* When the post is saved, saves our custom data */	
		public function mp_core_save_data() {
			
			//If we are saving this post type - we dont' want to save every single metabox that has been created using this class - only this post type
			if ( $this->_args['metabox_posttype'] == $_POST['post_type'] ) {
				
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
				
					// verify this came from our screen and with proper authorization,
					// because save_post can be triggered at other times
					if ( isset($_POST[$field['field_id'] . '_metabox_nonce']) ){
						if ( !wp_verify_nonce( $_POST[$field['field_id'] . '_metabox_nonce'], plugin_basename( __FILE__ ) ) )
						  return;
					}else{
						return;
					}
					
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
								// Update $data 
								update_post_meta($this->_post_id, $prev_repeater, $these_repeater_field_id_values);
								//Reset these_repeater_field_id_values
								$these_repeater_field_id_values = array();
							}
							
							//Set $prev_repeater to current field repeater 
							$prev_repeater = $field['field_repeater'];
							
							//Store all the post values for this repeater in $these_repeater_field_id_values
							$these_repeater_field_id_values = $_POST[$field['field_repeater']];
				
							//Loop through all of the fields in the $_POST with this repeater
							foreach($these_repeater_field_id_values as $repeat_field){
								
								//Sanitize user input for this repeater field and add it to the $data array
								$allowed_tags = array(
									'a' => array(
										'href' => array(),
										'title' => array()
									),
									'br' => array(),
									'em' => array(),
									'strong' => array(),
									'p' => array(),
								);
								if ( $field['field_type'] == 'textarea' ){
									$repeat_field[$field['field_id']] = wp_kses(htmlentities( $repeat_field[$field['field_id']], ENT_QUOTES), $allowed_tags ); 
								}
								elseif( $field['field_type'] == 'wp_editor' ){
									$repeat_field[$field['field_id']] = wp_kses(htmlentities(wpautop( $repeat_field[$field['field_id']], true ), ENT_QUOTES), $allowed_tags ); 
								}
								else{
									$repeat_field[$field['field_id']] = sanitize_text_field( $repeat_field[$field['field_id']] );	
								}
							}
						}
					}
					//This is not a repeater field.
					else{
						//But if the previous field was a repeater, update that repeater now
						if ($prev_repeater != false){
							// Update $data 
							update_post_meta($this->_post_id, $prev_repeater, $these_repeater_field_id_values);
							//Set $prev_repeater back to false
							$prev_repeater = false;
							//Set $these_repeater_field_id_values back to be an empty array
							$these_repeater_field_id_values = array();
						}
						
						//Update single post:
						//get value from $_POST
						$post_value = isset($_POST[$field['field_id']]) ? $_POST[$field['field_id']] : '';
						//sanitize user input
						$allowed_tags = array(
							'a' => array(
								'href' => array(),
								'title' => array()
							),
							'br' => array(),
							'em' => array(),
							'strong' => array(),
							'p' => array()
						);
						if ( $field['field_type'] == 'textarea' ){
							$data = wp_kses( htmlentities( $post_value, ENT_QUOTES ), $allowed_tags );
						}
						elseif( $field['field_type'] == 'wp_editor' ){
							$data = wp_kses( htmlentities( wpautop( $post_value, true ), ENT_QUOTES ), $allowed_tags );
						}
						else{
							$data = sanitize_text_field( $post_value );
						}
						
						// Update $data 
						update_post_meta($this->_post_id, $field['field_id'], $data);
					}
					
				}//End of foreach through $this->_metabox_items_array
				
				//If the final field was a repeater, update that repeater now
				if ($prev_repeater != false){
					// Update $data 
					update_post_meta($this->_post_id, $prev_repeater, $these_repeater_field_id_values);
				}
			}
		}
		
		/**
		* basictext field
		*/
		function basictext($field_id, $field_title, $field_description, $value, $classname){
			echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
			echo '<input type="hidden" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value=" " />';
			echo '</label></div>';
			echo '</div>'; 
		}
		/**
		* textbox field
		*/
		function textbox($field_id, $field_title, $field_description, $value, $classname){
			echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</label></div>';
			echo '<input type="text" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value="' . $value . '" />';
			echo '</div>'; 
		}
		/**
		* password field
		*/
		function password($field_id, $field_title, $field_description, $value, $classname){
			echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</label></div>';
			echo '<input type="password" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value="' . $value . '" />';
			echo '</div>'; 
		}
		/**
		* checkbox field
		*/
		function checkbox($field_id, $field_title, $field_description, $value, $classname, $field_select_values, $field_preset_value){
			$checked = empty($value) ? '' : 'checked';
			echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</label></div>';
			echo '<input type="checkbox" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value="' . $field_id . '" ' . $checked . '/>';
			echo '</div>'; 
		}
		/**
		* url field
		*/
		function url($field_id, $field_title, $field_description, $value, $classname){
			echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</label></div>';
			echo '<input type="url" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value="' . $value . '" />';
			echo '</div>'; 
		}
		/**
		* date field
		*/
		function date($field_id, $field_title, $field_description, $value, $classname){
			echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</label></div>';
			echo '<input type="date" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value="' . $value . '" size="50" />';
			echo '</div>'; 
		}
		/**
		* time field
		*/
		function time($field_id, $field_title, $field_description, $value, $classname){
			echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</label></div>';
			echo '<input type="time" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value="' . $value . '" size="50" />';
			echo '</div>'; 
		}
		/**
		* number field
		*/
		function number($field_id, $field_title, $field_description, $value, $classname){
			echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</label></div>';
			echo '<input type="number" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value="' . $value . '" size="20" />';
			echo '</div>'; 
		}
		/**
		* textarea field
		*/
		function textarea($field_id, $field_title, $field_description, $value, $classname){
			echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
			echo '</label></div>';
			echo '<textarea id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" rows="4" cols="50">';
			echo $value;
			echo '</textarea>';
			echo '</div>'; 
		}
		/**
		* WordPress editor field
		*/
		function wp_editor($field_id, $field_title, $field_description, $value, $classname){
			echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
			echo '</label></div>';
			echo wp_editor( html_entity_decode($value) , $field_id, $settings = array('textarea_rows' => 15));			
			echo '</div>'; 
		}
		/**
		* select field
		*/
		function select($field_id, $field_title, $field_description, $value, $classname, $select_values){
			echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
			echo '</label></div>';
			?>
			<label for="<?php echo $field_id; ?>">
				<select name="<?php echo $field_id; ?>" class="<?php echo $classname; ?>">
					<?php foreach ( $select_values as $select_value => $select_text) : ?>
					<option value="<?php echo esc_attr( $select_value ); ?>" <?php selected( $select_value, $value ); ?>>
						<?php echo isset($select_text) ? esc_attr( $select_text ) : esc_attr( $select_value ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php        
			echo '</div>'; 
		}
		/**
		* colorpicker field
		*/
		function colorpicker($field_id, $field_title, $field_description, $value, $classname){
			echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
			echo '</label></div>';
			echo '<input type="text" class="of-color ' . $classname . '" id="' . $field_id . '" name="' . $field_id . '" value="' . $value . '" />';
			echo '</div>'; 
		}
		/**
		* mediaupload field
		*/
		function mediaupload($field_id, $field_title, $field_description, $value, $classname){
			echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
			echo '</label></div>';
			?>       
			<!-- Upload button and text field -->
            <div class="mp_media_upload">
                <input class="custom_media_url <?php echo $classname; ?>" id="<?php echo $field_id; ?>" type="text" name="<?php echo $field_id; ?>" value="<?php echo esc_attr( $value ); ?>">
                <a href="#" class="button custom_media_upload"><?php _e('Upload', 'mp_core'); ?></a>
			</div>
			<?php
			//Image thumbnail
			if ( isset($value) ){
				$ext = pathinfo($value, PATHINFO_EXTENSION);
				if ($ext == 'png' || $ext == 'jpg'){
					?><img class="custom_media_image" src="<?php echo $value; ?>" style="display:inline-block;" /><?php
				}else{
					?><img class="custom_media_image" src="<?php echo $value; ?>" style="display: none;" /><?php
				}
			}
		echo '</div>';   
	
		}
		/**
		* customfieldtype field
		*/
		function customfieldtype($field_id, $field_title, $field_description, $value, $classname){
			
			//Use this hook to pass in your inpur field and whatever else you want this custom field to look like.
			do_action('mp_core_' . $this->_args['metabox_id'] . '_customfieldtype', $field_id, $field_title, $field_description, $value, $classname);
		}
		
	}
}