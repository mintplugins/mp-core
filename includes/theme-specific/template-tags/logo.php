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
function mp_core_header_customizer(){
	
	$theme = wp_get_theme();
	
	$args = array(
		array( 'section_id' => 'mp_cope_header_image', 'section_title' => sprintf( __( '%s Options', 'mp_core' ), "Logo" ), 'section_priority' => 1,
			'settings' => array(
				'mp_core_logo' => array(
					'label'      => __( 'Logo', 'mp_core' ),
					'type'       => 'image',
					'default'    => '',
					'priority'   => 9,
					'element'    => '.mp-core-logo',
					'jquery_function_name' => 'attr',
					'arg' => 'src'
				),
				'mp_core_logo_width' => array(
					'label'      => __( 'Logo Width (Pixels)', 'mp_core' ),
					'type'       => 'text',
					'default'    => '',
					'priority'   => 9,
					'element'    => '.mp-core-logo',
					'jquery_function_name' => 'attr',
					'arg' => 'width'
				),
				'mp_core_logo_height' => array(
					'label'      => __( 'Logo Height (Pixels)', 'mp_core' ),
					'type'       => 'text',
					'default'    => '',
					'priority'   => 9,
					'element'    => '.mp-core-logo',
					'jquery_function_name' => 'attr',
					'arg' => 'height'
				)
			)
		)
	);
	
	$args = has_filter('mp_core_logo_args') ? apply_filters('mp_core_logo_args', $args) : $args;
	
	new MP_CORE_Customizer($args);
}

add_action ('after_setup_theme', 'mp_core_header_customizer');


 
/**
  * Display logo image
  *
  * @since mp_core 1.0
  */
if ( ! function_exists( 'mp_core_logo_image' ) ) {
	function mp_core_logo_image(){
		
		//Variables
		$logo_image = get_theme_mod( 'mp_core_logo' );
		
		if ( ! empty( $logo_image ) ) { 
			
			//Get sizes from customizer			
			$image_width = get_theme_mod( 'mp_core_logo_width' );
			$image_height = get_theme_mod( 'mp_core_logo_height' );
			
			//If the customizer's logo width hasn't ben set
			if ( empty($image_width) ){
				
				//If there are theme filters for the width of the logo, apply it
				if ( has_filter('mp_core_logo_width') ) { 
					$image_width = apply_filters('mp_core_logo_width', 0);
				}
				//If there are no filters, use the actual size of the uploaded image
				else{
					//Image width
					$image_size = getimagesize($logo_image);
					$image_width = $image_size[0];
				}
					
			}
			
			//If the customizer's logo height hasn't ben set
			if ( empty($image_height) ){
				
				//If there are theme filters for the height of the logo, apply it
				if ( has_filter('mp_core_logo_height') ) { 
					$image_height = apply_filters('mp_core_logo_height', 0);
				}
				//If there are no filters, use the actual size of the uploaded image
				else{
					//Image height
					$image_height = $image_size[1];
				}
					
			}
		
			echo '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" rel="home">';
				
			echo '<img class="mp-core-logo" src="' . mp_aq_resize( $logo_image, $image_width, $image_height, true) . '" width="' . $image_width . '" height="' . $image_height . '" alt="home" />';
				
			echo '</a>';
			
		} else { 
		
			echo ('<a href="' . admin_url( 'customize.php' ) . '">' . __( 'Upload your logo', 'mp_core' ) . '</a>'); 
			
		}
		
	}
}