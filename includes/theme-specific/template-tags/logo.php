<?php
/**
 * This page contains the functions used in managing a logo for a website.
 * 
 * @link http://mintplugins.com/doc/move-plugins-core-api/
 *
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Theme Specific Functions
 *
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */
 
/**
 * Add a logo image to the customizer which can be used in themes.
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_logo_customizer/
 * @see      has_filter()
 * @see      apply_filters() 
 * @see      MP_CORE_Customizer
 * @return   void
 */
function mp_core_logo_customizer(){
		
	$args = array(
		array( 'section_id' => 'mp_cope_logo_image', 'section_title' => sprintf( __( '%s Options', 'mp_core' ), "Logo" ), 'section_priority' => 1,
			'settings' => array(
				'mp_core_logo' => array(
					'label'      => __( 'Logo', 'mp_core' ),
					'type'       => 'image',
					'default'    => '',
					'priority'   => 9,
					'element'    => '#mp-core-logo',
					'jquery_function_name' => 'attr',
					'arg' => 'src'
				),
				'mp_core_logo_width' => array(
					'label'      => __( 'Logo Width (Pixels)', 'mp_core' ),
					'type'       => 'textbox',
					'default'    => '',
					'priority'   => 9,
					'element'    => '#mp-core-logo',
					'jquery_function_name' => 'attr',
					'arg' => 'width'
				),
				'mp_core_logo_height' => array(
					'label'      => __( 'Logo Height (Pixels)', 'mp_core' ),
					'type'       => 'textbox',
					'default'    => '',
					'priority'   => 9,
					'element'    => '#mp-core-logo',
					'jquery_function_name' => 'attr',
					'arg' => 'height'
				)
			)
		)
	);
	
	$args = has_filter('mp_core_logo_args') ? apply_filters('mp_core_logo_args', $args) : $args;
	
	new MP_CORE_Customizer($args);
}
add_action ('init', 'mp_core_logo_customizer');

/**
 * Template tag which displays the logo image.
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_logo_image/
 * @see      get_theme_mod()
 * @see      esc_url()
 * @see      home_url()
 * @see      esc_attr()
 * @see      get_bloginfo()
 * @see      admin_url()
 * @see      is_ssl()
 * @param    int $default_width Optional. This size in pixels to use for the width if the user hasn't selected a width
 * @param    int $default_height Optional. This size in pixels to use for the height if the user hasn't selected a height
 * @return   void
 */
if ( ! function_exists( 'mp_core_logo_image' ) ) {
	function mp_core_logo_image( $default_width = NULL, $default_height = NULL ){
		
		//Variables
		$logo_image = get_theme_mod( 'mp_core_logo' );
		
		if (is_ssl()) {
			$logo_image = str_replace( 'http://', 'https://', $logo_image );
		}
		
		if ( ! empty( $logo_image ) ) { 
			
			//Get sizes from customizer			
			$image_width = get_theme_mod( 'mp_core_logo_width' );
			$image_height = get_theme_mod( 'mp_core_logo_height' );
			
			//If the customizer's logo width hasn't ben set
			if ( empty($image_width) ){
				
				//If there is a default width passed-in for the width of the logo, apply it
				if ( !empty( $default_width ) ) { 
					$image_width = $default_width;
				}
				//If there is no default width, use the actual size of the uploaded image
				else{
					//Image width
					$image_size = getimagesize($logo_image);
					$image_width = $image_size[0];
				}
					
			}
			
			//If the customizer's logo height hasn't ben set
			if ( empty($image_height) ){
				
				//If there is a default height passed-in for the width of the logo, apply it
				if ( !empty( $default_height ) ){ 
					$image_height = ' height="' . $default_height . '" ';
				}
				//If there is no default height, set it automaticaly based on the width
				else{
					//Image height
					$image_height = NULL;
				}
					
			}
			else{
				$image_height = ' height="' . $image_height . '" ';
			}
		
			echo '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" rel="home">';
				
			echo '<img id="mp-core-logo" src="' . $logo_image . '" width="' . $image_width . '"' . $image_height . ' alt="home" />';
				
			echo '</a>';
			
		} else { 
			
			global $wp_customize;
	
			if ( !isset( $wp_customize ) ) {
				
				if ( is_user_logged_in() && current_user_can('edit_theme_options') ) {
				
					//We're not on the customizer page so load the "Add new logo" button
					echo ('<a id="mp-core-upload-logo" href="' . admin_url( 'customize.php' ) . '">' . __( 'Upload your logo', 'mp_core' ) . '</a>'); 
				
				}
			}
			else{
				//We are on the customizer page so load the placeholder for the logo
				echo '<img id="mp-core-logo" src="" alt="home" />';
			}
			
		}
		
	}
}