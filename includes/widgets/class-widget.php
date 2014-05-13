<?php
/**
 * This file contains the MP_CORE_Widget class
 *
 * @link http://mintplugins.com/doc/widget-class/
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
 * This class extends WP_Widget include saving settings and displaying them to the user. 
 * This class is meant to be extended with a custom construct function. See link for more.
 *
 * @author     Philip Johnston
 * @link       http://mintplugins.com/doc/widget-class/
 * @since      1.0.0
 * @return     void
 */
class MP_CORE_Widget extends WP_Widget {
	
	/**
	 * Enqueue Scripts needed for the MP_CORE_Metabox class
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      get_current_screen()
	 * @see      wp_enqueue_style()
	 * @see      wp_enqueue_script()
	 * @see      wp_enqueue_media()
	 * @return   void
	 */
	public function mp_widget_enqueue_scripts(){
		
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
			
		}
	}
	
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		
		$instance = array();		
		
			foreach ($this->_form as $updatekey => $updateentry){	
				$idkey = $updateentry['field_id'];		
				$instance[$idkey] = strip_tags( $new_instance[$idkey] );
			}
		

		return $instance;
		
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
			
		foreach ($this->_form as $formkey => $formentry){	
			//Show the field type and pass the variables
			$this->$formentry['field_type']( 
				$formentry['field_id'], 
				$formentry['field_title'], 
				$formentry['field_description'], 
				!empty( $instance[$formentry['field_id']] ) ? esc_attr( $instance[$formentry['field_id']] ) : NULL,
				isset( $formentry['field_select_values'] ) ? $formentry['field_select_values'] : NULL
			);
		}
		
	}
	
	
	/**
	* textbox field
	*/
	function textbox($field_id, $field_title, $field_description, $value){
		?>
        <p>
            <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo $field_title ?> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" type="text" value="<?php echo $value; ?>" />
        </p>
        <?php		
	}
	
	/**
	* url field
	*/
	function url($field_id, $field_title, $field_description, $value){
		?>
        <p>
            <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo $field_title ?> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" type="url" value="<?php echo $value; ?>" />
        </p>
        <?php		
	}
	
	/**
	* date field
	*/
	function date($field_id, $field_title, $field_description, $value){
		?>
        <p>
            <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo $field_title ?> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" type="date" value="<?php echo $value; ?>" />
        </p>
        <?php		
	}
	
	/**
	* password field
	*/
	function password($field_id, $field_title, $field_description, $value){
		?>
        <p>
            <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo $field_title ?> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" type="password" value="<?php echo $value; ?>" />
        </p>
        <?php		
	}
	
	/**
	* number field
	*/
	function number($field_id, $field_title, $field_description, $value){
		?>
        <p>
            <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo $field_title ?> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" type="number" value="<?php echo $value; ?>" />
        </p>
        <?php		
	}
	
	/**
	* select field
	*/
	function select($field_id, $field_title, $field_description, $value, $field_select_values){
		?>
    
        <p>
        <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo $field_title ?> <?php echo !empty($field_description) ? ' - ' . $field_description : ''; ?> </label><br />
            <label for="<?php echo $field_id; ?>">
				<select name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" >
					<?php foreach ( $field_select_values as $select_value => $select_text) : ?>
					<option value="<?php echo esc_attr( $select_value ); ?>" <?php selected( $select_value, $value ); ?>>
						<?php echo isset($select_text) ? esc_attr( $select_text ) : esc_attr( $select_value ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</label>
        </p>
        <?php		
	}
	
	/**
	* checkbox field
	*/
	function checkbox($field_id, $field_title, $field_description, $value){
		$checked = empty($value) ? '' : 'checked';
		?>
        <p>
            <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo $field_title ?> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" type="checkbox" value="<?php echo $field_id; ?>" <?php echo $checked; ?>/>
        </p>
        <?php		
	}
	
	/**
	* input range field
	*/
	function input_range($field_id, $field_title, $field_description, $value){
		?>
        <p>
            <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo $field_title ?> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
            <input type="range" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" min="1" max="100" value ="<?php echo $value; ?>">
        </p>
        <?php		
	}
	
	/**
	* textarea field
	*/
	function textarea($field_id, $field_title, $field_description, $value){
		?>
        <p>
            <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo $field_title ?> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
            <textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" rows="4" cols="50" name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" ><?php echo $value; ?></textarea>
        </p>
        <?php	
	}
	/**
	* colorpicker field
	*/
	function colorpicker($field_id, $field_title, $field_description, $value){
		?>
        <p>
            <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo $field_title ?> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
            <input class="of-color" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" type="text" value="<?php echo $value; ?>" >
        </p>
        <?php		
	}
	/**
	* mediaupload field
	*/
	function mediaupload($field_id, $field_title, $field_description, $value){
        echo '<p>';?>
            <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo $field_title ?> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
        
            <!-- Upload button and text field -->
            <div class="mp_media_upload">
                <input class="custom_media_url" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" type="text" name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" value="<?php echo esc_attr( $value ); ?>">
                <a href="#" class="button custom_media_upload" style="margin-bottom:10px;"><?php _e('Upload', 'mp_core'); ?></a>
            </div>
            <?php
            //Image thumbnail
            if (isset($value)){
                $ext = pathinfo($value, PATHINFO_EXTENSION);
                if ($ext == 'png' || $ext == 'jpg'){
                    ?><img class="custom_media_image" src="<?php echo $value; ?>" style="display:inline-block;" /><?php
                }else{
                    ?><img class="custom_media_image" src="<?php echo $value; ?>" style="display: none;" /><?php
                }
            }
		echo '</p>';
	}
} // class MP_CORE_Widget