<?php
/**
 * Customize
 *
 * Theme options are lame! Manage any customizations through the Theme
 * Customizer. Expose the customizer in the Appearance panel for easy access.
 *
 * @package mp_core
 * @since mp_core 1.0
 */

class MP_CORE_Customizer{
	
	protected $_args;
	protected $_settings_array = array();
	
	public function __construct($args){
		$this->_args = $args;
		
		//Add Customize link to the admin_menu
		add_action ( 'admin_menu', array( $this, 'mp_core_customize_menu' ) );
		//Enqueue the preview js script
		add_action( 'customize_preview_init', array( $this, 'mp_core_customize_preview_js' ) );
		//Register settings and controls
		add_action( 'customize_register', array( $this, 'mp_core_customize_register_settings_and_controls' ) );
		//Set all transport settings to postMessage
		add_action( 'customize_register', array( $this, 'mp_core_customize_register_transport' ) );
		//Output custom css to header
		add_action( 'wp_head', array( $this, 'mp_core_header_css' ) );
	}

	/**
	 * Expose a "Customize" link in the main admin menu.
	 *
	 * By default, the only way to access a theme customizer is via
	 * the themes.php page, which is totally lame.
	 *
	 * @since mp_core 1.0
	 *
	 * @return void
	 */
	function mp_core_customize_menu() {
		
		global $mp_core_customize_page_created;
		
		//If this menu item has not already been added
		if ($mp_core_customize_page_created == false){
			add_theme_page( __( 'Customize', 'mp_core' ), __( 'Customize', 'mp_core' ), 'edit_theme_options', 'customize.php' );
			
			//Let the global variable know we've added this menu page so that we don't add it twice
			$mp_core_customize_page_created = true;
		}
		
	}
	
	/**
	 * Add postMessage support for all default fields, as well
	 * as the site title and desceription for the Theme Customizer.
	 *
	 * @since mp_core 1.0
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @return void
	 */
	function mp_core_customize_register_transport( $wp_customize ) {
		
		$no_transport_types = array( 'background-image', 'background-disabled', 'responsive' );
		
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
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 *
	 * @since mp_core 1.0
	 */
	function mp_core_customize_preview_js() {
		wp_enqueue_script( 'mp_core_customizer_js' . $this->_args[0]['section_id'], plugins_url( 'js/customizer.js', dirname(__FILE__)), array( 'jquery' , 'customize-preview' ), NULL, true );
				
		$mp_core_customizer_js_vars = $this->_args;
		
		wp_localize_script('mp_core_customizer_js' . $this->_args[0]['section_id'], 'mp_core_customizer_vars', $mp_core_customizer_js_vars );		
	}
	
	/**
	 * Any CSS customizations we make need to be outputted in the document <head>
	 * This does that.
	 *
	 * @since mp_core 1.0
	 *
	 * @return void
	 */
	function mp_core_header_css() {
		
		echo '<style>';
		
		
		foreach ( $this->_args as $section ){
			foreach ( $section['settings'] as $id => $setting ){
				
				$theme_mod_id = get_theme_mod( $id );
				
				if  ( !empty( $setting['arg'] ) && $setting['arg'] != "responsive" ){
					echo $setting['element'] . '{';
						
						//Background Image
						if ( $setting['arg'] == "background-image" ){
							if (!empty ($theme_mod_id) || $theme_mod_id != false){
								echo $setting['arg'] . ': url(\'' . $theme_mod_id . '\');';
							}
							
						}
						
						//Background Disabled
						if ( $setting['arg'] == "background-disabled" ){
							if ( !empty( $theme_mod_id ) ){ //<--checked
								echo 'background-image: none;';
							}
							
						}
						
						//Display
						elseif( $setting['arg'] == "display" ){
							
							$display_val = $theme_mod_id == false ? 'none' : 'block';
							
							echo $setting['arg'] . ':' . $display_val . ';';
							
						}
						
						//Other
						else{
							
							//Make sure it's not empty
							if ( !empty( $theme_mod_id ) || $theme_mod_id != false ){
								echo $setting['arg'] . ':' . $theme_mod_id . ';';
							}
						}
					
					echo '}';
				}
			}
		}
		
		echo '</style>';
	}
	
	/**
	 * Get Theme Mod
	 *
	 * Instead of options, customizations are stored/accessed via Theme Mods
	 * (which are still technically settings). This wrapper provides a way to
	 * check for an existing mod, or load a default in its place.
	 *
	 * @since mp_core 1.0
	 *
	 * @param string $key The key of the theme mod to check. Prefixed with 'mp_core_'
	 * @return mixed The theme modification setting
	 */
	function mp_core_theme_mod( $key ) {
		$defaults = $this->mp_core_get_theme_mods();
		$mod      = get_theme_mod( $key, $defaults[ $key ] );
	
		return apply_filters( 'mp_core_theme_mod_' . $key, $mod );
	}
	
	/**
	 * Default theme customizations.
	 *
	 * @since mp_core 1.0
	 *
	 * @return $options an array of default theme options
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
	 * Register settings and controls 
	 *
	 * @since mp_core 1.0
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 * @return void
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
					'default'    => $this->mp_core_theme_mod( $setting_id )
				) );
				
				//Set default for priority if not filled out
				 $setting['priority'] = !empty( $setting['priority'] ) ? $setting['priority'] : 10;
				
				if ( isset ($setting['choices'] ) ){
					//Call the function to add the control for this type
					$this->$setting['type']( $wp_customize, $section['section_id'], $setting_id, $setting, $setting['choices'] );
				}
				else{
					//Call the function to add the control for this type
					$this->$setting['type']( $wp_customize, $section['section_id'], $setting_id, $setting );
				}
				
			}
	
		}
	
		do_action( 'mp_core_customize_hero', $wp_customize );
	
		return $wp_customize;
	}
	
	/**
	 * Type Text Field. Used to add a control for the text type
	 *
	 * @since mp_core 1.0
	 *
	 * @param $id
	 * @param $section - array
	 *
	 * @return void
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
	 * @since mp_core 1.0
	 *
	 * @param $id
	 * @param $section - array
	 *
	 * @return void
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
	 * @since mp_core 1.0
	 *
	 * @param $id
	 * @param $section - array
	 *
	 * @return void
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
	 * @since mp_core 1.0
	 *
	 * @param $id
	 * @param $section - array
	 *
	 * @return void
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
	 * @since mp_core 1.0
	 *
	 * @param $id
	 * @param $section - array
	 *
	 * @return void
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
	 * @since mp_core 1.0
	 *
	 * @param $id
	 * @param $section - array
	 *
	 * @return void
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
	 * @since mp_core 1.0
	 *
	 * @param $id
	 * @param $section - array
	 *
	 * @return void
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
	 * @since mp_core 1.0
	 *
	 * @param $id
	 * @param $section - array
	 *
	 * @return void
	 */
	 function select( $wp_customize, $section_id, $setting_id, $setting, $choices ){
		
		$wp_customize->add_control( $setting_id, array(
			'label' => $setting['label'],
			'section' => $section_id,
			'type' => 'select',
			'choices' => $choices
		) );
	
	 }	

}


/**
 * Textarea Control
 *
 * Attach the custom textarea control to the `customize_register` action
 * so the WP_Customize_Control class is initiated.
 *
 * @since mp_core 1.0
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function mp_core_customize_textarea_control($wp_customize) {
	/**
	 * Textarea Control
	 *
	 * @since CLoudify 1.0
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