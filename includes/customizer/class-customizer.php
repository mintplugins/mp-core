<?php
/**
 * This file contains the MP_CORE_Customizer class
 *
 * @link http://mintplugins.com/doc/customizer-class/
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
 * Customizer Class for the MP Core Plugin by Mint Plugins.
 * 
 * This class uses associative arrays to create new customizer fields. They can auto transport without needing to write extra javascript files.
 *
 * @author     Philip Johnston
 * @link       http://mintplugins.com/doc/customizer-class/
 * @since      1.0.0
 * @return     void
 */
class MP_CORE_Customizer{
	
	protected $_args;
	protected $_settings_array = array();
	
	/**
	 * Constructor
	 *
	 * @access   public
	 * @since    1.0.0
	 * @link      http://mintplugins.com/doc/customizer-class/
	 * @see      MP_CORE_Customizer::mp_core_customize_menu()
	 * @see      MP_CORE_Customizer::mp_core_customize_preview_js()
	 * @see      MP_CORE_Customizer::mp_core_customize_register_settings_and_controls()
	 * @see      MP_CORE_Customizer::mp_core_customize_register_transport()
	 * @see      MP_CORE_Customizer::mp_core_header_css()
	 * @param    array $args See link for description and layout
	 * @return   void
	 */
	public function __construct($args){
		
		//Get args
		$this->_args = $args;
		
		//Enqueue the preview js script
		add_action( 'customize_preview_init', array( $this, 'mp_core_customize_preview_js' ) );
		//Register settings and controls
		add_action( 'customize_register', array( $this, 'mp_core_customize_register_settings_and_controls' ) );
		//Set all transport settings to postMessage
		add_action( 'customize_register', array( $this, 'mp_core_customize_register_transport' ) );
		//Output custom css to header
		add_action( 'wp_head', array( $this, 'mp_core_header_css' ) );
		//Output custom css to TinyMCE css file
		add_action( 'mp_core_tinymce_css', array( $this, 'mp_core_header_css' ) );
	}
			 
	 /**
	 * Add postMessage support for all passed-in fields
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      apply_filters()
	 * @param    WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @return   void
	 */
	function mp_core_customize_register_transport( $wp_customize ) {
		
		$no_transport_types = array( 'background-image', 'background-disabled', 'responsive', 'background-color-opacity', 'font-size(px)' );
		
		//Fiter hook for args to ignore and make the page refresh
		$no_transport_types = apply_filters( 'mp_core_customizer_transport_ignore_types', $no_transport_types );
		
		foreach ( $this->_args as $section ){
			foreach ( $section['settings'] as $id => $setting ){
								
					if ( !in_array( $setting['arg'], $no_transport_types ) && !empty( $setting['arg'] ) ){
						
						$wp_customize->get_setting( $id )->transport = 'postMessage';
						
					}
				
			}
		}
	}
	
	/**
	 * Enqueue JS handler to make Theme Customizer preview reload changes asynchronously.
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      wp_enqueue_script()
	 * @see      wp_localize_script()
	 * @return   void
	 */
	function mp_core_customize_preview_js() {
		
		wp_enqueue_script( 'mp_core_customizer_js' . $this->_args[0]['section_id'], plugins_url( 'js/core/customizer.js', dirname(__FILE__)), array( 'jquery' , 'customize-preview' ), NULL, true );
				
		$mp_core_customizer_js_vars = $this->_args;
		
		wp_localize_script('mp_core_customizer_js' . $this->_args[0]['section_id'], 'mp_core_customizer_vars', $mp_core_customizer_js_vars );		
	}
	
	/**
	 * This function will output any CSS customizations that need to be in the document <head>
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      wp_enqueue_script()
	 * @see      get_theme_mod()
	 * @see      wp_localize_script()
	 * @return   void
	 */
	function mp_core_header_css() {
				
		if ( current_filter() != 'mp_core_tinymce_css'){
			echo '<style>';
		}
		
		
		foreach ( $this->_args as $section ){
			foreach ( $section['settings'] as $id => $setting ){
				
				$theme_mod_value = get_theme_mod( $id );
				$theme_mod_value = empty( $theme_mod_value ) ? $setting['default'] : $theme_mod_value;
				
				if  ( !empty( $setting['arg'] ) && $setting['arg'] != "responsive" && $setting['arg'] != "src" ){
					
					//If the element variable passed-in is an array, loop through each element
					if ( is_array( $setting['element'] ) ){
						
						//Loop through each element and arg for that element							
						for( $x=0; $x < sizeof( $setting['element'] ); $x++ ){
							
							//Outout CSS for this element/arg
							$this->mp_core_element_css( $setting['element'][$x], $setting['arg'][$x], $theme_mod_value );
	
						}
					}
					//If the element variable passed-in is not an array, it will be a single page element (css class, id, etc)
					else{
						
						 //Outout CSS for this element/arg
						 $this->mp_core_element_css( $setting['element'], $setting['arg'], $theme_mod_value );
						
					}
				}
			}
		}
		
		if ( current_filter() != 'mp_core_tinymce_css'){
			echo '</style>';
		}
	}
	
	/**
	 * Outputs CSS Output for elements passed-in to this function
	 *
	 * @access   public
	 * @since    1.0.0
	 * @param    string $element_id The CSS selector. 
	 * @param    string $css_arg The CSS arg name. 
	 * @param    string $theme_mod_value The CSS value. 
	 * @return   void
	 */
	function mp_core_element_css( $element_id, $css_arg, $theme_mod_value ) {
		
		echo $element_id . '{';
								
		//Background Image
		if ( $css_arg == "background-image" ){
			if (!empty ($theme_mod_value) || $theme_mod_value != false){
				echo $css_arg . ': url(\'' . $theme_mod_value . '\');';
			}
			
		}
		
		//Background Opacity
		elseif ( $css_arg == "background-color-opacity" ){
					
				//Store the opacity value in a global variable so we can use it next time through this on the rgb for the background
				global $mp_core_customizer_background_opacity;
				$mp_core_customizer_background_opacity = floatval($theme_mod_value);
			
						
		}
		
		//Background Color
		elseif ( $css_arg == "background-color" ){
			
			//If we set this up correctly, our opacity is right before our color and has been stored in the global variable
			global $mp_core_customizer_background_opacity;
			
			$mp_core_customizer_background_opacity = !is_numeric($mp_core_customizer_background_opacity) ? 1 : $mp_core_customizer_background_opacity;
			
			if (!empty ($theme_mod_value) || $theme_mod_value != false){
								
				$rgb = mp_core_hex2rgb( $theme_mod_value );
				echo $css_arg . ': rgba(' . $rgb[0] . ', ' . $rgb[1] . ', ' . $rgb[2] . ', ' . $mp_core_customizer_background_opacity . ');';
				
			}
			
			//Now that we've used it, set it to NULL
			$mp_core_customizer_background_opacity = NULL;
			
		}
		
		//Background Disabled
		elseif ( $css_arg == "background-disabled" ){
			if ( !empty( $theme_mod_value ) ){ //<--checked
				echo 'background-image: none;';
			}
			
		}
		
		//Display
		elseif( $css_arg == "display" ){
			
			$display_val = $theme_mod_value == false ? 'none' : 'block';
			
			echo $css_arg . ':' . $display_val . ';';
			
		}
		
		//Font-Size
		elseif( $css_arg == "font-size(px)" ){
			
			if ( !empty( $theme_mod_value ) ){
			
				echo 'font-size' . ':' . $theme_mod_value . 'px;';
				
			}
			
		}
		
		//Border-width
		elseif( $css_arg == "border-width" ){
			
			if ( !empty( $theme_mod_value ) ){
			
				echo 'border-width' . ':' . $theme_mod_value . 'px;';
				
			}
			
		}
		
		//Border-radius
		elseif( $css_arg == "border-radius" ){
			
			if ( !empty( $theme_mod_value ) ){
			
				echo 'border-radius' . ':' . $theme_mod_value . 'px;';
				
			}
			
		}
		
		//Other
		else{
			
			//Make sure it's not empty
			if ( !empty( $theme_mod_value ) || $theme_mod_value != false ){
				echo $css_arg . ':' . $theme_mod_value . ';';
			}
	
		}
	
		echo '}';
	
	}
	
	/**
	 * Get Theme Mod
	 *
	 * Instead of options, customizations are stored/accessed via Theme Mods
	 * (which are still technically settings). This wrapper provides a way to
	 * check for an existing mod, or load a default in its place.
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      MP_CORE_Customizer::mp_core_get_theme_mods()
	 * @see      get_theme_mod()
	 * @param    string $key The key of the theme mod to check. 
	 * @return   mixed The theme mod setting
	 */
	function mp_core_theme_mod( $key ) {
		$defaults = $this->mp_core_get_theme_mods();
		$mod      = get_theme_mod( $key, $defaults[ $key ] );
	
		return apply_filters( 'mp_core_theme_mod_' . $key, $mod );
	}
	
	/**
	 * Default theme customizations. Set the defaults to the defaults passed-in the the 'default' key for each.
	 *
	 * @access   public
	 * @since    1.0.0
	 * @return   array $options An array of default theme options
	 */
	function mp_core_get_theme_mods() {
		
		$defaults = array();
		
		foreach ( $this->_args as $section ){
			foreach ( $section['settings'] as $id => $setting ){
				$defaults[$id] = $setting['default'];
				
			}
		}

		return $defaults;
	}
	
	/**
	 * Customizations
	 *
	 * Register sections, settings, and controls and add them to the $wp_customize Theme Customizer Object.
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      WP_Customize_Manager::add_section()
	 * @see      MP_CORE_Customizer::textbox()
	 * @see      MP_CORE_Customizer::checkbox()
	 * @see      MP_CORE_Customizer::textarea()
	 * @see      MP_CORE_Customizer::radio()
	 * @see      MP_CORE_Customizer::image()
	 * @see      MP_CORE_Customizer::upload()
	 * @see      MP_CORE_Customizer::color()
	 * @see      MP_CORE_Customizer::select()
	 * @param    WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @return   void
	 */
	function mp_core_customize_register_settings_and_controls( $wp_customize ) {
		
		//Create sections
		foreach ( $this->_args as $section ){
		
			$wp_customize->add_section( $section['section_id'], array(
				'title'      => $section['section_title'],
				'priority'   => $section['section_priority'],
			) );
			
			//Create settings and controls
			foreach ( $section['settings'] as $setting_id => $setting ){
				$wp_customize->add_setting( $setting_id, array(
					'default'    => $setting['default']
				) );
				
				//Set default for priority if not filled out
				 $setting['priority'] = !empty( $setting['priority'] ) ? $setting['priority'] : 10;
				
				//Call the function to add the control for this type
				$this->$setting['type']( $wp_customize, $section['section_id'], $setting_id, $setting );
				
			}
	
		}
		
		return $wp_customize;
	}
	
	/**
	 * Type Text Field. Used to add a control for the text type
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      WP_Customize_Manager::add_control()
	 * @param    WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @param    $section_id The ID of this Section. Settings are separated into Sections.
	 * @param    $setting_id The ID of this Setting.
	 * @param    $section An array containing the 'label', 'priority', and 'choices' for this setting.
	 * @return   void
	 */
	 function textbox( $wp_customize, $section_id, $setting_id, $setting ){
		 
		 $wp_customize->add_control( $setting_id, array(
			'label'      => $setting['label'],
			'section'    => $section_id,
			'settings'   => $setting_id,
			'type'       => 'text',
			'priority'   => $setting['priority']
		) );
	 }
	 
	 /**
	 * Type checkbox Field. Used to add a control for the text type
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      WP_Customize_Manager::add_control()
	 * @param    WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @param    $section_id The ID of this Section. Settings are separated into Sections.
	 * @param    $setting_id The ID of this Setting.
	 * @param    $section An array containing the 'label', 'priority', and 'choices' for this setting.
	 * @return   void
	 */
	 function checkbox( $wp_customize, $section_id, $setting_id, $setting ){
		 
		 $wp_customize->add_control( $setting_id, array(
			'label'      => $setting['label'],
			'section'    => $section_id,
			'settings'   => $setting_id,
			'type'       => 'checkbox',
			'priority'   => $setting['priority']
		) );
	 }
	 
	 /**
	 * Type textarea Field. Used to add a control for the textarea type
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      WP_Customize_Manager::add_control()
	 8 @see      MP_CORE_Customizer::mp_core_Customize_Textarea_Control()
	 * @param    WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @param    $section_id The ID of this Section. Settings are separated into Sections.
	 * @param    $setting_id The ID of this Setting.
	 * @param    $section An array containing the 'label', 'priority', and 'choices' for this setting.
	 * @return   void
	 */
	 function textarea( $wp_customize, $section_id, $setting_id, $setting ){
		 $wp_customize->add_control( new mp_core_Customize_Textarea_Control( $wp_customize, $setting_id, array(
			'label'      => $setting['label'],
			'section'    => $section_id,
			'settings'   => $setting_id,
			'type'       => 'textarea',
			'priority'   => $setting['priority']
		) ) );
	
	 }
	 
	 /**
	 * Type radio Field. Used to add a control for the radio type
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      WP_Customize_Manager::add_control()
	 * @param    WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @param    $section_id The ID of this Section. Settings are separated into Sections.
	 * @param    $setting_id The ID of this Setting.
	 * @param    $section An array containing the 'label', 'priority', and 'choices' for this setting.
	 * @return   void
	 */
	 function radio( $wp_customize, $section_id, $setting_id, $setting ){
		
		 $wp_customize->add_control( $setting_id, array(
			'label'      => $setting['label'],
			'section'    => $section_id,
			'type'       => 'radio',
			'choices'    => $setting['choices'],
			'priority'       => 70
		) );
	
	 }
	 
	 /**
	 * Type image Field. Used to add a control for the image type
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      WP_Customize_Manager::add_control()
	 * @see      WP_Customize_Image_Control
	 * @param    WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @param    $section_id The ID of this Section. Settings are separated into Sections.
	 * @param    $setting_id The ID of this Setting.
	 * @param    $section An array containing the 'label', 'priority', and 'choices' for this setting.
	 * @return   void
	 */
	 function image( $wp_customize, $section_id, $setting_id, $setting ){
		
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $setting_id, array(
			'label'          => $setting['label'],
			'section'        => $section_id,
			'settings'       => $setting_id,
			'priority'       => $setting['priority']
		) ) );
	
	 }	
	 
	 /**
	 * Type upload Field. Used to add a control for the upload type
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      WP_Customize_Manager::add_control()
	 * @see      WP_Customize_Upload_Control
	 * @param    WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @param    $section_id The ID of this Section. Settings are separated into Sections.
	 * @param    $setting_id The ID of this Setting.
	 * @param    $section An array containing the 'label', 'priority', and 'choices' for this setting.
	 * @return   void
	 */
	 function upload( $wp_customize, $section_id, $setting_id, $setting ){
		
		$wp_customize->add_control( new WP_Customize_Upload_Control( $wp_customize, $setting_id, array(
			'label'          => $setting['label'],
			'section'        => $section_id,
			'settings'       => $setting_id,
			'priority'       => $setting['priority']
		) ) );
	
	 }	
	 
	 /**
	 * Type color Field. Used to add a control for the image type
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      WP_Customize_Color_Control
	 * @see      WP_Customize_Manager::add_control()
	 * @param    WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @param    $section_id The ID of this Section. Settings are separated into Sections.
	 * @param    $setting_id The ID of this Setting.
	 * @param    $section An array containing the 'label', 'priority', and 'choices' for this setting.
	 * @return   void
	 */
	 function color( $wp_customize, $section_id, $setting_id, $setting ){
		
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $setting_id, array(
			'label'          => $setting['label'],
			'section'        => $section_id,
			'settings'       => $setting_id,
			'priority'       => $setting['priority']
		) ) );
	
	 }	
	 
	 /**
	 * Type Select Field. Used to add a control for the image type
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      WP_Customize_Manager::add_control()
	 * @param    WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @param    $section_id The ID of this Section. Settings are separated into Sections.
	 * @param    $setting_id The ID of this Setting.
	 * @param    $section An array containing the 'label', 'priority', and 'choices' for this setting.
	 * @return   void
	 */
	 function select( $wp_customize, $section_id, $setting_id, $setting ){
		
		$wp_customize->add_control( $setting_id, array(
			'label' => $setting['label'],
			'section' => $section_id,
			'type' => 'select',
			'choices' => $setting['choices']
		) );
	
	 }	
	 
	  /**
	 * Type Select Field. Used to add a control for the image type
	 *
	 * @access   public
	 * @since    1.0.0
	 * @see      WP_Customize_Manager::add_control()
	 * @param    WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @param    $section_id The ID of this Section. Settings are separated into Sections.
	 * @param    $setting_id The ID of this Setting.
	 * @param    $section An array containing the 'label', 'priority', and 'choices' for this setting.
	 * @return   void
	 */
	 function range( $wp_customize, $section_id, $setting_id, $setting ){
		
		$wp_customize->add_control( $setting_id, array(
			'label' => $setting['label'],
			'section' => $section_id,
			'type' => 'range',
			'choices' => $setting['choices']
		) );
	
	 }	

}


/**
 * Textarea Control
 *
 * Attach the custom textarea control to the `customize_register` action
 * so the WP_Customize_Control class is initiated.
 *
 * @since    1.0.0
 * @see      WP_Customize_Control
 * @see      esc_textarea()
 * @see      esc_html()
 * @param    WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return   void
 */
function mp_core_customize_textarea_control($wp_customize) {
		 
	/**
	 * Textarea Control Class
	 * 
	 * This class extends the WP_Customize_Control class to make a textarea
	 *
	 * @author     Philip Johnston
	 * @link       http://mintplugins.com/doc/customizer-class/
	 * @since      1.0.0
	 * @return     void
	 */
	class mp_core_Customize_Textarea_Control extends WP_Customize_Control {
		
		public $type = 'textarea';

		public function render_content() {
	?>
		<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<textarea rows="8" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
		</label>
	<?php
		}
	} 
}
add_action( 'customize_register', 'mp_core_customize_textarea_control', 1, 1 );