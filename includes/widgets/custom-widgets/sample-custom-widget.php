<?php
/**
 * Extends MP_CORE_Widget to create custom widget class.
 */
class My_Custom_Widget extends MP_CORE_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'foo_widget', // Base ID
			'My Cool Widget', // Name
			array( 'description' => __( 'A Foo Widget', 'text_domain' ), ) // Args
		);
		
		//enqueue scripts defined in MP_CORE_Widget
		add_action( 'admin_enqueue_scripts', array( $this, 'mintplugins_enqueue_scripts' ) );
	
		$this->_form = array (
			"foofield1" => array(
				'field_id' 			=> 'title',
				'field_title' 	=> __('Title', 'mp_core'),
				'field_description' 	=> __('Enter the title:', 'mp_core'),
				'field_type' 	=> 'textbox'
			), 
			"foofield2" => array(
				'field_id' 			=> 'color',
				'field_title' 	=> __('Color', 'mp_core'),
				'field_description' 	=> __('Select a color:', 'mp_core'),
				'field_type' 	=> 'colorpicker'
			),
			"field3" => array(
				'field_id' 			=> 'my_text_area',
				'field_title' 	=> __('Text', 'mp_core'),
				'field_description' 	=> __('Enter some text:', 'mp_core'),
				'field_type' 	=> 'textarea'
			),
			"field4" => array(
				'field_id' 			=> 'my_image',
				'field_title' 	=> __('Image Upload', 'mp_core'),
				'field_description' 	=> __('Upload the image:', 'mp_core'),
				'field_type' 	=> 'mediaupload'
			)
		);
	
	}
	
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		/**
		 * Widget Start and Title
		 */
		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
			
		/**
		 * Widget Body
		 */
		echo $instance['title'];
		echo $instance['color'];
		echo $instance['my_text_area'];
		echo $instance['my_image'];
		
		/**
		 * Widget End
		 */
		echo $after_widget;
	}
}

add_action( 'widgets_init', create_function( '', 'register_widget( "my_custom_Widget" );' ) );