<?php
/**
 * This file contains the MP_CORE_Shortcode_Insert class 
 *
 * @link http://mintplugins.com/doc/shortcode-insert-class/
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
 * This class is used to easily create “shortcode builders” which assemble the pieces of a shortcode for the user and insert it into the content area.
 *
 * @author     Philip Johnston
 * @link       http://mintplugins.com/doc/shortcode-insert-class/
 * @since      1.0.0
 * @return     void
 */

class MP_CORE_Shortcode_Insert{
	
	protected $_args;
	protected $_settings_array = array();
	
	/**
	 * Constructor
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      MP_CORE_Shortcode_Insert::mp_core_enqueue_scripts()
	 * @see      MP_CORE_Shortcode_Insert::mp_core_shortcode_button()
	 * @see      MP_CORE_Shortcode_Insert::mp_core_shortcode_admin_footer_for_thickbox()
	 * @see      MP_CORE_Shortcode_Insert::mp_core_enqueue_scripts()
	 * @see      wp_parse_args()
	 * @see      add_action()
	 * @see      add_filter()
	 * @param    array $args {
	 *      This array contains info for creating the shortcode builder
	 *		@type string 'shortcode_id' The unique id for this shortcode.
	 *		@type string 'shortcode_title' What to display for the title on the shortcode-insert media button in WP.
	 *		@type string 'shortcode_description' The description of this shortcode.
	 *		@type array 'shortcode_options' See link for details.
	 * }
	 * @return   void
	 */
	public function __construct($args){
		
		//Set defaults for args		
		$args_defaults = array(
			'shortcode_id' => NULL,
			'shortcode_title' => NULL,
			'shortcode_description' => NULL,
			'shortcode_icon_spot' => NULL,
			'shortcode_icon_dashicon_code' => NULL,
			'shortcode_options' => array()
		);
		
		//Get and parse args
		$this->_args = wp_parse_args( $args, $args_defaults );
		
		add_filter( 'media_buttons_context', array( $this, 'mp_core_shortcode_button' ) );
		add_action( 'admin_footer', array( $this, 'mp_core_shortcode_admin_footer_for_thickbox' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'mp_core_enqueue_scripts' ) );
	}
	
	/**
	 * Enqueue Scripts
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      get_current_screen()
	 * @see      wp_enqueue_style()
	 * @see      wp_enqueue_script()
	 * @see      wp_enqueue_media()
	 * @return   void
	 */
	public function mp_core_enqueue_scripts(){
		
		//Get current page
		$current_page = get_current_screen();
		
		//Only load if we are not on the nav menu page - where some of our scripts seem to be conflicting
		if ( $current_page->base != 'nav-menus' ){
			
			//color picker scripts
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker-load', plugins_url( 'js/core/wp-color-picker.js', dirname(__FILE__)),  array( 'jquery', 'wp-color-picker' ) );
			
			//media upload scripts
			wp_enqueue_media();
			
			//image uploader script
			wp_enqueue_script( 'image-upload', plugins_url( 'js/core/image-upload.js', dirname(__FILE__) ),  array( 'jquery' ) );	
			
			//custom js scripts
			wp_enqueue_script( 'mp_core_shortcode_inserter_js', plugins_url( 'js/core/mp-core-shortcode-inserter.js', dirname(__FILE__) ),  array( 'jquery' ) );	
			
			
			
		}
	}
	
	/**
	 * Media Button
	 *
	 * Returns the "Insert Shortcode" TinyMCE button.
	 *
	 * @access     public
	 * @since      1.0.0
	 * @global     $pagenow
	 * @global     $typenow
	 * @global     $wp_version
	 * @param      string $context The string of buttons that already exist
	 * @return     string The HTML output for the media buttons
	*/
	
	function mp_core_shortcode_button( $context ) {
		
		global $pagenow, $typenow, $wp_version;
		
		$output = '';
	
		/** Only run in post/page creation and edit screens */
		if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
			
			//Check current WP version - If we are on an older version than 3.5
			if ( version_compare( $wp_version, '3.5', '<' ) ) {
				
				//Output old style button
				$output = '<a href="#TB_inline?width=640&inlineId=choose-' . $this->_args['shortcode_id'] . '" class="thickbox ' . $this->_args['shortcode_id'] . '-thickbox" title="' . __('Add ', 'mp_core') . $this->_args['shortcode_title'] . '">' . $img . '</a>';
				
			//If we are on a newer than 3.5 WordPress	
			} else {
				
				//If we should show a dashicon on the Shortcode button
				if ( !empty( $this->_args['shortcode_icon_dashicon_class'] ) ){
					//Output new style button
					$output = '<a href="#TB_inline?width=640&inlineId=choose-' . $this->_args['shortcode_id'] . '" class="thickbox button ' . $this->_args['shortcode_id'] . '-thickbox" title="' . __('Add ', 'mp_core') . $this->_args['shortcode_title'] . '" style="padding-left:5px;">';
						$output .= '<span class="wp-media-buttons-icon dashicons ' . $this->_args['shortcode_icon_dashicon_class'] . '" style="font-size:17px;" id="' . $this->_args['shortcode_id'] . '-media-button"></span>';
					$output .= __('Add ', 'mp_core') . $this->_args['shortcode_title'] . '</a>';
				}
				//If we should just output a space for an icon (image icon)
				else if( !empty( $this->_args['shortcode_icon_spot'] ) ){
					$output = '<a href="#TB_inline?width=640&inlineId=choose-' . $this->_args['shortcode_id'] . '" class="thickbox button ' . $this->_args['shortcode_id'] . '-thickbox" title="' . __('Add ', 'mp_core') . $this->_args['shortcode_title'] . '">';
						$output .= '<span class="wp-media-buttons-icon" id="' . $this->_args['shortcode_id'] . '-media-button"></span>';
					$output.= __('Add ', 'mp_core') . $this->_args['shortcode_title'] . '</a>';
				}
				else{
				
					//Output old style button
					$output = '<a href="#TB_inline?width=640&inlineId=choose-' . $this->_args['shortcode_id'] . '" class="thickbox button ' . $this->_args['shortcode_id'] . '-thickbox" title="' . __('Add ', 'mp_core') . $this->_args['shortcode_title'] . '">';
					
					//Finish the output
					$output.= __('Add ', 'mp_core') . $this->_args['shortcode_title'] . '</a>';
				}
			}
		}
		
		//Add new button to list of buttons to output
		return $context . $output;
	}
	
	/**
	 * Admin Footer For Thickbox
	 *
	 * Prints the footer code needed for the Insert Shortcode
	 * which will exist in the Thickbox popup. Also contains the javascript used to process the form and insert into body area.
	 *
	 * @access     public
	 * @since      1.0.0
	 * @return     void
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
							jQuery( "#<?php echo $this->_args['shortcode_id'] . '_' . $option['option_id']; ?>" ).change(function() {
								
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
					
					// Send the shortcode to the editor ?>
 -					window.send_to_editor('[<?php echo $this->_args['shortcode_id']; 
						
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
					
					<?php 
					//Use this hook to execute functions that need to be called when the shortcode is inserted into the active tinymce editor
					do_action('mp_core_shortcode_' . $this->_args['shortcode_id'] . '_insert_event'); 
					?>
					
					//Use this jQuery Trigger to execute javascript that needs to be called when the shortcode is inserted into the active tinymce editor
					jQuery( window ).trigger( "mp_core_shortcode_<?php echo $this->_args['shortcode_id']; ?>_insert_event", event );
					
					tb_remove();
				}
				
			</script>
			
            <!--Create the hidden div which will display in the Thickbox -->	
			<div id="choose-<?php echo $this->_args['shortcode_id']; ?>" style="display: none;">
				<div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
				<?php
				
				do_action( 'mp_core_before_' . $this->_args['shortcode_id'] . '_shortcode_output' ); 
                
				if ( $this->_args['shortcode_options'] ) {
					echo '<p>' . $this->_args['shortcode_description'] . '</p>';
					
					//Loop through each option in this shortcode and display the corresponding function 
					foreach ($this->_args['shortcode_options'] as $option){ 
					
                       	//Call the function for this option type. EG textbox, select, checkbox etc
					    $this->$option['option_type'](
							$this->_args['shortcode_id'] . '_' . $option['option_id'], //<-- $field_id
							$option['option_title'], //<-- $field_title
							$option['option_description'], //<-- $field_description
							$option['option_value'], //<-- $value
							isset( $option['option_conditional_id'] ) ? $this->_args['shortcode_id'] . '_' . $option['option_conditional_id'] : NULL, //<-- The id of the field which we want to check the value of - to see if this field should be shown at all
							isset( $option['option_conditional_values'] ) ? $option['option_conditional_values'] : NULL //<-- array of values which, if selected from the conditional id, will show this field
						);
                    }
					?>
					<p class="submit">
						<input type="button" id="<?php echo $this->_args['shortcode_id']; ?>" class="button-primary" value="<?php echo __('Insert ', 'mp_core') . $this->_args['shortcode_title']; ?>" onclick="insert_<?php echo $this->_args['shortcode_id']; ?>_Shortcode();" />
						<a id="<?php echo $this->_args['shortcode_id']; ?>_cancel_download_insert" class="button-secondary" onclick="tb_remove();" title="<?php _e( 'Cancel', 'mp_core' ); ?>"><?php _e( 'Cancel', 'mp_core' ); ?></a>
					</p>
                    
                    <?php do_action( 'mp_core_after_' . $this->_args['shortcode_id'] . '_shortcode_output' ); ?>
				
				</div>
			</div>
			<?php
			}
		}
	}
			
	/**
	* basictext field
	*
	* @access     public
	* @since      1.0.0
	* @param      string $field_id The string to use for the HTML ID of this field
	* @param      string $field_title The string to use for the title above this field
	* @param      string $field_description The string to use for the description above this field
	* @param      string $value The current value to use for this field.
	* @return     void
	*/
	function basictext($field_id, $field_title, $field_description, $value, $conditional_id, $conditional_values){

		//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
		$conditional_output = !empty( $conditional_id ) ? ' mp_conditional_field_id="' . $conditional_id . '" mp_conditional_field_values="' . implode(', ', $conditional_values ) . '" ' : NULL;

		echo '<div class="mp_field ' . $field_id . '_field" ' . $conditional_output  . '><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
		echo '<input type="hidden" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value=" " />';
		echo '</label></div>';
		echo '</div>'; 
	}
	/**
	* textbox field
	*
	* @access     public
	* @since      1.0.0
	* @param      string $field_id The string to use for the HTML ID of this field
	* @param      string $field_title The string to use for the title above this field
	* @param      string $field_description The string to use for the description above this field
	* @param      string $value The current value to use for this field.
	*/
	function textbox($field_id, $field_title, $field_description, $value, $conditional_id, $conditional_values){

		//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
		$conditional_output = !empty( $conditional_id ) ? ' mp_conditional_field_id="' . $conditional_id . '" mp_conditional_field_values="' . implode(', ', $conditional_values ) . '" ' : NULL;
			
		echo '<div class="mp_field ' . $field_id . '_field" ' . $conditional_output . '><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		echo '<input type="text" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value="' . $value . '" />';
		echo '</div>'; 
	}
	/**
	* password field
	*
	* @access     public
	* @since      1.0.0
	* @param      string $field_id The string to use for the HTML ID of this field
	* @param      string $field_title The string to use for the title above this field
	* @param      string $field_description The string to use for the description above this field
	* @param      string $value The current value to use for this field.
	*/
	function password($field_id, $field_title, $field_description, $value, $conditional_id, $conditional_values){

		//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
		$conditional_output = !empty( $conditional_id ) ? ' mp_conditional_field_id="' . $conditional_id . '" mp_conditional_field_values="' . implode(', ', $conditional_values ) . '" ' : NULL;

		echo '<div class="mp_field ' . $field_id . '_field" ' . $conditional_output  . '><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		echo '<input type="password" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value="' . $value . '" />';
		echo '</div>'; 
	}
	/**
	* checkbox field
	*
	* @access     public
	* @since      1.0.0
	* @param      string $field_id The string to use for the HTML ID of this field
	* @param      string $field_title The string to use for the title above this field
	* @param      string $field_description The string to use for the description above this field
	* @param      string $value The current value to use for this field.
	*/
	function checkbox($field_id, $field_title, $field_description, $value, $conditional_id, $conditional_values){

		//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
		$conditional_output = !empty( $conditional_id ) ? ' mp_conditional_field_id="' . $conditional_id . '" mp_conditional_field_values="' . implode(', ', $conditional_values ) . '" ' : NULL;

		$checked = empty($value) ? '' : 'checked';
		echo '<div class="mp_field ' . $field_id . '_field" ' . $conditional_output  . '><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		echo '<input type="checkbox" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value="' . $field_id . '" ' . $checked . '/>';
		echo '</div>'; 
	}
	/**
	* input range field
	*
	* @access     public
	* @since      1.0.0
	* @param      string $field_id The string to use for the HTML ID of this field
	* @param      string $field_title The string to use for the title above this field
	* @param      string $field_description The string to use for the description above this field
	* @param      string $value The current value to use for this field.
	*/
	function inout_range($field_id, $field_title, $field_description, $value, $conditional_id, $conditional_values){

		//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
		$conditional_output = !empty( $conditional_id ) ? ' mp_conditional_field_id="' . $conditional_id . '" mp_conditional_field_values="' . implode(', ', $conditional_values ) . '" ' : NULL;

		echo '<div class="mp_field ' . $field_id . '_field" ' . $conditional_output  . '><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		echo '<input type="range" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value="' . $field_id . '" min="1" max="100" />';
		echo '</div>'; 
	}
	/**
	* url field
	*
	* @access     public
	* @since      1.0.0
	* @param      string $field_id The string to use for the HTML ID of this field
	* @param      string $field_title The string to use for the title above this field
	* @param      string $field_description The string to use for the description above this field
	* @param      string $value The current value to use for this field.
	*/
	function url($field_id, $field_title, $field_description, $value, $conditional_id, $conditional_values){

		//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
		$conditional_output = !empty( $conditional_id ) ? ' mp_conditional_field_id="' . $conditional_id . '" mp_conditional_field_values="' . implode(', ', $conditional_values ) . '" ' : NULL;

		echo '<div class="mp_field ' . $field_id . '_field" ' . $conditional_output  . '><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		echo '<input type="url" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value="' . $value . '" />';
		echo '</div>'; 
	}
	/**
	* date field
	*
	* @access     public
	* @since      1.0.0
	* @param      string $field_id The string to use for the HTML ID of this field
	* @param      string $field_title The string to use for the title above this field
	* @param      string $field_description The string to use for the description above this field
	* @param      string $value The current value to use for this field.
	*/
	function date($field_id, $field_title, $field_description, $value, $conditional_id, $conditional_values){

		//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
		$conditional_output = !empty( $conditional_id ) ? ' mp_conditional_field_id="' . $conditional_id . '" mp_conditional_field_values="' . implode(', ', $conditional_values ) . '" ' : NULL;

		echo '<div class="mp_field ' . $field_id . '_field" ' . $conditional_output  . '><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		echo '<input type="date" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value="' . $value . '" size="50" />';
		echo '</div>'; 
	}
	/**
	* number field
	*
	* @access     public
	* @since      1.0.0
	* @param      string $field_id The string to use for the HTML ID of this field
	* @param      string $field_title The string to use for the title above this field
	* @param      string $field_description The string to use for the description above this field
	* @param      string $value The current value to use for this field.
	*/
	function number($field_id, $field_title, $field_description, $value, $conditional_id, $conditional_values){

		//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
		$conditional_output = !empty( $conditional_id ) ? ' mp_conditional_field_id="' . $conditional_id . '" mp_conditional_field_values="' . implode(', ', $conditional_values ) . '" ' : NULL;

		echo '<div class="mp_field ' . $field_id . '_field" ' . $conditional_output  . '><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		echo '<input type="number" id="' . $field_id . '" name="' . $field_id . '" class="' . $field_id . '" value="' . $value . '" size="20" />';
		echo '</div>'; 
	}
	/**
	* textarea field
	*
	* @access     public
	* @since      1.0.0
	* @param      string $field_id The string to use for the HTML ID of this field
	* @param      string $field_title The string to use for the title above this field
	* @param      string $field_description The string to use for the description above this field
	* @param      string $value The current value to use for this field.
	*/
	function textarea($field_id, $field_title, $field_description, $value, $conditional_id, $conditional_values){

		//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
		$conditional_output = !empty( $conditional_id ) ? ' mp_conditional_field_id="' . $conditional_id . '" mp_conditional_field_values="' . implode(', ', $conditional_values ) . '" ' : NULL;

		echo '<div class="mp_field ' . $field_id . '_field" ' . $conditional_output  . '><div class="mp_title"><label for="' . $field_id . '">';
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
	*
	* @access     public
	* @since      1.0.0
	* @param      string $field_id The string to use for the HTML ID of this field
	* @param      string $field_title The string to use for the title above this field
	* @param      string $field_description The string to use for the description above this field
	* @param      string $value The current value to use for this field.
	*/
	function select($field_id, $field_title, $field_description, $value, $conditional_id, $conditional_values){

		//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
		$conditional_output = !empty( $conditional_id ) ? ' mp_conditional_field_id="' . $conditional_id . '" mp_conditional_field_values="' . implode(', ', $conditional_values ) . '" ' : NULL;

		echo '<div class="mp_field ' . $field_id . '_field" ' . $conditional_output  . '><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';   
		echo '</label></div>';
		?>
		<label for="<?php echo $field_id; ?>">
			<select id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" class="<?php echo $field_id; ?>">
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
	*
	* @access     public
	* @since      1.0.0
	* @param      string $field_id The string to use for the HTML ID of this field
	* @param      string $field_title The string to use for the title above this field
	* @param      string $field_description The string to use for the description above this field
	* @param      string $value The current value to use for this field.
	*/
	function colorpicker($field_id, $field_title, $field_description, $value, $conditional_id, $conditional_values){

		//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
		$conditional_output = !empty( $conditional_id ) ? ' mp_conditional_field_id="' . $conditional_id . '" mp_conditional_field_values="' . implode(', ', $conditional_values ) . '" ' : NULL;

		echo '<div class="mp_field ' . $field_id . '_field" ' . $conditional_output  . '><div class="mp_title"><label for="' . $field_id . '">';
		echo '<strong>' .  $field_title . '</strong>';
		echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
		echo '</label></div>';
		echo '<input type="text" class="of-color ' . $field_id . '" id="' . $field_id . '" name="' . $field_id . '" value="' . $value . '" />';
		echo '</div>'; 
	}
	/**
	* mediaupload field
	*
	* @access     public
	* @since      1.0.0
	* @param      string $field_id The string to use for the HTML ID of this field
	* @param      string $field_title The string to use for the title above this field
	* @param      string $field_description The string to use for the description above this field
	* @param      string $value The current value to use for this field.
	*/
	function mediaupload($field_id, $field_title, $field_description, $value, $conditional_id, $conditional_values){

		//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
		$conditional_output = !empty( $conditional_id ) ? ' mp_conditional_field_id="' . $conditional_id . '" mp_conditional_field_values="' . implode(', ', $conditional_values ) . '" ' : NULL;

		echo '<div class="mp_field ' . $field_id . '_field" ' . $conditional_output  . '><div class="mp_title"><label for="' . $field_id . '">';
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
	
	/**
	* iconfontpicker field
	*
	* @access   public
	* @since    1.0.0
	* @return   void
	*/
	function iconfontpicker($field_id, $field_title, $field_description, $value, $conditional_id, $conditional_values){

		//Set the conditional output which tells this field it is only visible if the parent's conditional value is $field_conditional_values
		$conditional_output = !empty( $conditional_id ) ? ' mp_conditional_field_id="' . $conditional_id . '" mp_conditional_field_values="' . implode(', ', $conditional_values ) . '" ' : NULL;

		
		echo '<div class="mp_field ' . $field_id . '_field" ' . $conditional_output  . '><div class="mp_title"><label for="' . $field_id . '">';
			echo '<strong>' .  $field_title . '</strong>';
			echo $field_description != "" ? ' ' . '<em>' . $field_description . '</em>' : '';
			echo '</label></div>';
			
			//Font thumbnail
			echo '<div class="mp_font_icon_thumbnail">';
				echo '<div class="'. $field_id . '">';
					echo '<div class="mp-iconfontpicker-title" ></div>';
				echo '</div>';
			echo '</div>';
			
			?>       
			<!-- Icon select and text field -->
			<div class="mp-icon-font-field-container">
				<input class="mp-icon-font-field <?php echo $field_id; ?>" id="<?php echo $field_id; ?>" type="hidden" name="<?php echo $field_id; ?>" value="">
				<a class="mp-core-shortcode-icon-select button"><?php _e('Select Icon', 'mp_core'); ?></a>
			</div>
					
			<div class="mp-core-icon-picker-area" style="display: none;">
						
				<?php
				foreach( $value as $icon ){
					
					echo '<a href="#" class="mp-core-icon-picker-item-shortcode">';
												
						echo '<div class="' . $icon . ' mp-core-icon">';
							
							echo '<div class="mp-iconfontpicker-title" >' . $icon . '</div>';
						
						echo '</div>';
					
					echo '</a>';
						 
				} 
				?>
				
			</div>
	
	<?php
	
	echo '</div>';   

	}
} 
