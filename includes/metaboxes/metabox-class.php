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
			//color picker scripts
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker-load', plugins_url( '/mp_core/includes/js/wp-color-picker.js'),  array( 'jquery') );
			//media upload scripts
			wp_enqueue_media();
			//image uploader script
			wp_enqueue_script( 'image-upload', plugins_url( '/mp_core/includes/js/image-upload.js' ),  array( 'jquery' ) );
			//duplicator script
			wp_enqueue_script( 'field-duplicator', plugins_url( '/mp_core/includes/js/field-duplicator.js' ),  array( 'jquery' ) );	
			//drag and drop sortable script - http://farhadi.ir/projects/html5sortable/
			wp_enqueue_script( 'sortable', plugins_url( '/mp_core/includes/js/sortable.js' ),  array( 'jquery' ) );	
			wp_enqueue_script( 'mp_set_sortables', plugins_url( '/mp_core/includes/js/mp_set_sortables.js' ),  array( 'jquery', 'sortable' ) );	
			//do_action
			do_action('mp_core_' . $this->_args['metabox_id'] . '_metabox_custom_scripts');
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
											//Check if the type of value is a checkbox and it is empty
											if ($thefield['field_type'] == 'checkbox' && empty($repeater_set[$thefield['field_id']])){
												$field_value = '';
											}
											//If it's not a checkbox than use the saved value.
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
								echo '<a class="mp_duplicate button" style="margin-right:5px">' . __('Add New', 'mp_core') . '</a><a class="mp_duplicate_remove button" style="margin-right:5px">' . __('Remove', 'mp_core') . '</a><a href="#" class="mp_drag button">' . __('Drag Me', 'mp_core') . '</a>';
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
							echo '<a class="mp_duplicate button" style="margin-right:5px">' . __('Add New', 'mp_core') . '</a><a class="mp_duplicate_remove button" style="margin-right:5px">' . __('Remove', 'mp_core') . '</a><a href="#" class="mp_drag button">' . __('Drag Me', 'mp_core') . '</a>';
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
							// If this is not a checkbox, set any empty settings to be the values set in the passed-in array
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
						$this->$field['field_type']( $field['field_id'], $field['field_title'], $field['field_description'], $value, NULL, $field_select_values, $preset_value);
					}
				}
			}
		}
		
		/* When the post is saved, saves our custom data */	
		public function mp_core_save_data() {
			
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
			
				// verify this came from the our screen and with proper authorization,
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
					//If this repeater as not already been handled, go through and save it.
					if ($prev_repeater != $field['field_repeater']){
						//If the previous field was the last in a set of repeaters, update that set of repeater now
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
								'strong' => array()
							);
							if ($field['field_type'] == 'textarea'){
								$repeat_field[$field['field_id']] = wp_kses(htmlentities($repeat_field[$field['field_id']], ENT_QUOTES), $allowed_tags ); }
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
						'strong' => array()
					);
					$data = $field['field_type'] == 'textarea' ? wp_kses($post_value, $allowed_tags) : sanitize_text_field( $post_value );
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
		
		public function get_repeater_field($post_id, $repeater){
				//Set default for $fields
				$fields = array();
				
				//Get the array of variables stored in the database for this repeater
				$current_stored_repeater = get_post_meta( $post_id, $key = $repeater, $single = true );
				
				//This is a brand new repeater
				$repeat_counter = 0;
					
				//Loop the same amount of times the user clicked 'repeat' (including the first one that was there before they clicked 'repeat')
				foreach ($current_stored_repeater as $repeater_set) {
					
					foreach ($this->_metabox_items_array as $thefield){
						if ( isset($thefield['field_repeater']) && $thefield['field_repeater'] == $repeater){
							
							$fields[$repeat_counter][$thefield['field_id']] = array(
									'field_id'           => $thefield['field_id'],
									'field_title'        => $thefield['field_title'],
									'field_description'  => $thefield['field_description'],
									'field_value'        => isset($repeater_set[$thefield['field_id']]) ? $repeater_set[$thefield['field_id']] : '',
									'field_class'        => 'mp_repeater'
							);				
						}	
					}
					
					//bump the repeat_counter to the next number of the array
					$repeat_counter = $repeat_counter + 1;
				
				}
				
				return $fields;
		}//End function get_repeater_field
		
		
		/**
		* basictext field
		*/
		function basictext($field_id, $field_title, $field_description, $value, $classname){
			echo '<p><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' - ' . $field_description : '';
			echo '<input type="hidden" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value=" " size="25" />';
			echo '</label>';
			echo '</p>'; 
		}
		/**
		* textbox field
		*/
		function textbox($field_id, $field_title, $field_description, $value, $classname){
			echo '<p><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' - ' . $field_description : '';   
			echo '</label><br />';
			echo '<input type="text" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value="' . $value . '" size="25" />';
			echo '</p>'; 
		}
		/**
		* checkbox field
		*/
		function checkbox($field_id, $field_title, $field_description, $value, $classname, $field_select_values, $field_preset_value){
			$checked = empty($value) ? '' : 'checked';
			echo '<p><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' - ' . $field_description : '';   
			echo '</label><br />';
			echo '<input type="checkbox" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value="' . $field_preset_value . '" size="25" ' . $checked . '/>';
			echo '</p>'; 
		}
		/**
		* url field
		*/
		function url($field_id, $field_title, $field_description, $value, $classname){
			echo '<p><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' - ' . $field_description : '';   
			echo '</label><br />';
			echo '<input type="url" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value="' . $value . '" size="25" />';
			echo '</p>'; 
		}
		/**
		* date field
		*/
		function date($field_id, $field_title, $field_description, $value, $classname){
			echo '<p><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' - ' . $field_description : '';   
			echo '</label><br />';
			echo '<input type="date" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value="' . $value . '" size="25" />';
			echo '</p>'; 
		}
		/**
		* number field
		*/
		function number($field_id, $field_title, $field_description, $value, $classname){
			echo '<p><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' - ' . $field_description : '';   
			echo '</label><br />';
			echo '<input type="number" id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" value="' . $value . '" size="25" />';
			echo '</p>'; 
		}
		/**
		* textarea field
		*/
		function textarea($field_id, $field_title, $field_description, $value, $classname){
			echo '<p><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' - ' . $field_description : '';
			echo '</label><br />';
			echo '<textarea id="' . $field_id . '" name="' . $field_id . '" class="' . $classname . '" rows="4" cols="50">';
			echo $value;
			echo '</textarea>';
			echo '</p>'; 
		}
		/**
		* select field
		*/
		function select($field_id, $field_title, $field_description, $value, $classname, $select_values){
			echo '<p><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' - ' . $field_description : '';   
			echo '</label><br />';
			?>
			<label for="<?php echo $field_id; ?>">
				<select name="<?php echo $field_id; ?>" class="<?php echo $classname; ?>">
					<?php foreach ( $select_values as $select_value ) : ?>
					<option value="<?php echo esc_attr( $select_value ); ?>" <?php selected( $select_value, $value ); ?>>
						<?php echo esc_attr( $select_value ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php        
			echo '</p>'; 
		}
		/**
		* colorpicker field
		*/
		function colorpicker($field_id, $field_title, $field_description, $value, $classname){
			echo '<p><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' - ' . $field_description : '';
			echo '</label><br />';
			echo '<input type="text" class="of-color ' . $classname . '" id="' . $field_id . '" name="' . $field_id . '" value="' . $value . '" size="25" />';
			echo '</p>'; 
		}
		/**
		* mediaupload field
		*/
		function mediaupload($field_id, $field_title, $field_description, $value, $classname){
			echo '<p><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' - ' . $field_description : '';
			echo '</label><br />';
			?>       
			<!-- Upload button and text field -->
			<input class="custom_media_url <?php echo $classname; ?>" id="<?php echo $field_id; ?>" type="text" name="<?php echo $field_id; ?>" value="<?php echo esc_attr( $value ); ?>" style="margin-bottom:10px; clear:right;">
			<a href="#" class="button custom_media_upload"><?php _e('Upload', 'mp_core'); ?></a>
			
			<?php
			//Image thumbnail
			if ( isset($value) ){
				$ext = pathinfo($value, PATHINFO_EXTENSION);
				if ($ext == 'png' || $ext == 'jpg'){
					?><br /><img class="custom_media_image" src="<?php echo $value; ?>" style="max-width:100px; display:inline-block;" /><?php
				}else{
					?><br /><img class="custom_media_image" src="<?php echo $value; ?>" style="max-width:100px; display: none;" /><?php
				}
			}
		echo '</p>';   
	
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