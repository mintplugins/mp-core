<?php
/**
 * Widget Class for the Foundation Theme by Move Plugins
 * http://moveplugins.com/widget-class/
 */

/**
 * Extends WP_Widget include saving settings and displaying them to the user. 
 * This class is meant to be extended with a custom construct function. See http://moveplugins.com/widget-class/ for more
 */
class MP_CORE_Widget extends WP_Widget {
		
	public function moveplugins_enqueue_scripts(){
		//color picker scripts
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker-load', plugins_url( '/mp_core/includes/js/wp-color-picker.js'),  array( 'jquery' ) );
		//media upload scripts
		wp_enqueue_media();
		//image uploader script
		wp_enqueue_script( 'image-upload', plugins_url( '/mp_core/includes/js/image-upload.js' ),  array( 'jquery' ) );	
	}
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
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
				$this->$formentry['field_type']( $formentry['field_id'], $formentry['field_title'], $formentry['field_description'], isset( $instance[$formentry['field_id']] ) ? esc_attr( $instance[$formentry['field_id']] ) : '');
		}
		
	}
	
	
	/**
	* textbox field
	*/
	function textbox($field_id, $field_title, $field_description, $value){
		?>
        <p>
            <label for="<?php echo esc_attr( $field_id ); ?>"><strong><?php echo $field_title ?></strong> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" type="text" value="<?php echo $value; ?>" />
        </p>
        <?php		
	}
	
	/**
	* textarea field
	*/
	function textarea($field_id, $field_title, $field_description, $value){
		?>
        <p>
            <label for="<?php echo esc_attr( $field_id ); ?>"><strong><?php echo $field_title ?></strong> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
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
            <label for="<?php echo esc_attr( $field_id ); ?>"><strong><?php echo $field_title ?></strong> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
            <input class="of-color" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" type="text" value="<?php echo $value; ?>" >
        </p>
        <?php		
	}
	/**
	* mediaupload field
	*/
	function mediaupload($field_id, $field_title, $field_description, $value){
        echo '<p>';?>
            <label for="<?php echo esc_attr( $field_id ); ?>"><strong><?php echo $field_title ?></strong> <?php echo $field_description != "" ? ' - ' . $field_description : ''; ?> </label>
        
            <!-- Upload button and text field -->
            <input class="custom_media_url" id="<?php echo esc_attr( $this->get_field_id( $field_id ) ); ?>" type="text" name="<?php echo esc_attr( $this->get_field_name( $field_id ) ); ?>" value="<?php echo esc_attr( $value ); ?>">
            <a href="#" class="button custom_media_upload" style="margin-bottom:10px;"><?php _e('Upload', 'mp_core'); ?></a>
            
            <?php
            //Image thumbnail
            if (isset($value)){
                $ext = pathinfo($value, PATHINFO_EXTENSION);
                if ($ext == 'png' || $ext == 'jpg'){
                    ?><br /><img class="custom_media_image" src="<?php echo $value; ?>" style="max-width:100px; display:inline-block;" /><?php
                }else{
                    ?><br /><img class="custom_media_image" src="<?php echo $value; ?>" style="max-width:100px; display: none;" /><?php
                }
            }
		echo '</p>';
	}
} // class MP_CORE_Widget

include_once( 'custom-widgets/sample-custom-widget.php' );