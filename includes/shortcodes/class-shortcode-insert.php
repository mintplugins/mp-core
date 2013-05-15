<?php
/**
 * mp_core Shortcode Class
 *
 * @package mp_core
 * @since mp_core 1.0
 */

class MP_CORE_Shortcode_Insert{
	
	protected $_args;
	protected $_settings_array = array();
	
	public function __construct($args){
		$this->_args = $args;
		
		add_action( 'admin_enqueue_scripts', array( $this, 'mp_core_enqueue_scripts' ) );
		add_filter( 'media_buttons_context', array( $this, 'mp_core_shortcode_button' ) );
		add_action( 'admin_footer', array( $this, 'mp_core_shortcode_admin_footer_for_thickbox' ) );
	}
	
	public function mp_core_enqueue_scripts(){
		
	}
	
	/**
	 * Media Button
	 *
	 * Returns the Insert Shortcode TinyMCE button.
	 *
	 * @access      private
	 * @since       1.0
	 * @return      string
	*/
	
	function mp_core_shortcode_button( $context ) {
		global $pagenow, $typenow, $wp_version;
		$output = '';
	
		/** Only run in post/page creation and edit screens */
		if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
			/* check current WP version */
			if ( version_compare( $wp_version, '3.5', '<' ) ) {
				$output = '<a href="#TB_inline?width=640&inlineId=choose-' . $this->_args['shortcode_id'] . '" class="thickbox" title="' . __('Insert ', 'mp_core') . $this->_args['shortcode_title'] . '">' . $img . '</a>';
			} else {
				$img = '<span class="wp-media-buttons-icon" id="mp-core-' . $this->_args['shortcode_id'] . '"></span>';
				$output = '<a href="#TB_inline?width=640&inlineId=choose-' . $this->_args['shortcode_id'] . '" class="thickbox button" title="' . __('Insert ', 'mp_core') . $this->_args['shortcode_title'] . '">' . __('Insert ', 'mp_core') . $this->_args['shortcode_title'] . '</a>';
			}
		}
		return $context . $output;
	}
	
	/**
	 * Admin Footer For Thickbox
	 *
	 * Prints the footer code needed for the Insert Shortcode
	 * TinyMCE button.
	 *
	 * @access      private
	 * @since       1.0
	 * @return      void
	*/
	
	function mp_core_shortcode_admin_footer_for_thickbox() {
		global $pagenow, $typenow;
		
		// Only run in post/page creation and edit screens
		if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
			$downloads = get_posts( array( 'post_type' => 'mp_slide', 'posts_per_page' => -1 ) );
			?>
			<script type="text/javascript">
	
			jQuery(document).ready(function(){
				//Loop through all options in this shortcode
				<?php foreach ($this->_args['shortcode_options'] as $option){
					
					//Only create functions for checkbox types
					if ($option['option_type'] == "checkbox"){
						
						//Set default value for the checkbox id javascript variable
						echo $option['option_id']; ?> = "false";
						
						//*
						//Create a function which, when the checkbox is clicked, checks if this checkbox is checked or not 
						//and stores the corresponding "true" or "false" in a variable named after the option_id
						//*
						jQuery( "#<?php echo $option['option_id']; ?>" ).change(function() {
						  //Check if the checked attribute exists
						  var checked = jQuery(this).attr('checked');
						  
						  //If the checked attribute does exist
						  if (typeof checked !== 'undefined' && checked !== false) {
							//Change the value of the option_id variable to true
							<?php echo $option['option_id']; ?> = "true";
						  }
						  //If the checked attribute doesn't exist
						  else{
							//Change the value of the option_id variable to false
							<?php echo $option['option_id']; ?> = "false";
						  }
						});
						
					<?php }
				} ?>
			});
				function insert_<?php echo $this->_args['shortcode_id']; ?>_Shortcode() {
	
					// Send the shortcode to the editor
					window.send_to_editor('[<?php echo $this->_args['shortcode_id']; 
						
						foreach ($this->_args['shortcode_options'] as $option){
							
							//If this is a checkbox
							if ($option['option_type'] == 'checkbox'){
								echo ' ' . $option['option_id'] . '="'; ?>' + <?php echo $option['option_id']; ?> + '<?php echo '"'; 
							}
							//If this is not a checkbox
							else{
								echo ' ' . $option['option_id'] . '="'; ?>' + jQuery('#<?php echo $this->_args['shortcode_id'] . '_' . $option['option_id']; ?>').val() + '<?php echo '"'; 
							}
						}
					
					?>]');
				}
			</script>
			
            <!--Create the hidden div which will display in the Thickbox -->	
			<div id="choose-<?php echo $this->_args['shortcode_id']; ?>" style="display: none;">
				<div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
				<?php
				if ( $this->_args['shortcode_options'] ) {
					echo '<p>' . $this->_args['shortcode_description'] . '</p>';
					
					//Loop through each option in this shortcode and display the corresponding function 
					foreach ($this->_args['shortcode_options'] as $option){ 
					
                       	//Call the function for this option type. EG textbox, select, checkbox etc
					    $this->$option['option_type'](
							$this->_args['shortcode_id'] . '_' . $option['option_id'], //<-- $field_id
							$option['option_title'], //<-- $field_title
							$option['option_description'], //<-- $field_description
							$option['option_value'] //<-- $value
						);
                    }
					?>
					<p class="submit">
						<input type="button" id="<?php echo $this->_args['shortcode_id']; ?>" class="button-primary" value="<?php echo __('Insert ', 'mp_core') . $this->_args['shortcode_title']; ?>" onclick="insert_<?php echo $this->_args['shortcode_id']; ?>_Shortcode();" />
						<a id="<?php echo $this->_args['shortcode_id']; ?>-cancel-download-insert" class="button-secondary" onclick="tb_remove();" title="<?php _e( 'Cancel', 'mp_core' ); ?>"><?php _e( 'Cancel', 'mp_core' ); ?></a>
					</p>
				</div>
			</div>
			<?php
			}
		}
	}
	
	/**
	* basictext field
	*/
	function basictext($field_id, $field_title, $field_description, $value){
		echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
		echo '<input type="hidden" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value=" " />';
		echo '</label></div>';
		echo '</div>'; 
	}
	/**
	* textbox field
	*/
	function textbox($field_id, $field_title, $field_description, $value){
		echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		echo '<input type="text" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value="' . $value . '" />';
		echo '</div>'; 
	}
	/**
	* password field
	*/
	function password($field_id, $field_title, $field_description, $value){
		echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		echo '<input type="password" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value="' . $value . '" />';
		echo '</div>'; 
	}
	/**
	* checkbox field
	*/
	function checkbox($field_id, $field_title, $field_description, $value){
		$checked = empty($value) ? '' : 'checked';
		echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		echo '<input type="checkbox" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value="' . $field_id . '" ' . $checked . '/>';
		echo '</div>'; 
	}
	/**
	* url field
	*/
	function url($field_id, $field_title, $field_description, $value){
		echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		echo '<input type="url" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value="' . $value . '" />';
		echo '</div>'; 
	}
	/**
	* date field
	*/
	function date($field_id, $field_title, $field_description, $value){
		echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		echo '<input type="date" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value="' . $value . '" size="50" />';
		echo '</div>'; 
	}
	/**
	* number field
	*/
	function number($field_id, $field_title, $field_description, $value){
		echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		echo '<input type="number" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value="' . $value . '" size="20" />';
		echo '</div>'; 
	}
	/**
	* textarea field
	*/
	function textarea($field_id, $field_title, $field_description, $value){
		echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
		echo '</label></div>';
		echo '<textarea id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" rows="4" cols="50">';
		echo $value;
		echo '</textarea>';
		echo '</div>'; 
	}
	/**
	* select field
	*/
	function select($field_id, $field_title, $field_description, $value){
		echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		?>
		<label for="<?php echo $field_id; ?>">
			<select id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" class="<?php echo $field_id; ?>">
            	<option value="null">
				<?php foreach ( $value as $select_value => $select_text) : ?>
				<option value="<?php echo esc_attr( $select_value ); ?>">
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
	function colorpicker($field_id, $field_title, $field_description, $value){
		echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
		echo '</label></div>';
		echo '<input type="text" class="of-color ' . $field_id . '" id="' . $field_id . '" name="' . $field_id . '" value="' . $value . '" />';
		echo '</div>'; 
	}
	/**
	* mediaupload field
	*/
	function mediaupload($field_id, $field_title, $field_description, $value){
		echo '<div class="mp_field"><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
		echo '</label></div>';
		?>       
		<!-- Upload button and text field -->
		<div class="mp_media_upload">
			<input class="custom_media_url <?php echo $field_id; ?>" id="<?php echo $field_id; ?>" type="text" name="<?php echo $field_id; ?>" value="<?php echo esc_attr( $value ); ?>">
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
} 
