<?php 
/**
 * Setup the WordPress core background feature.
 *
 * Use add_theme_support to register support for WordPress 3.4+
 * as well as provide backward compatibility for previous versions.
 * Use feature detection of wp_get_theme() which was introduced
 * in WordPress 3.4.
 *
 * @todo Rework this function to remove WordPress 3.4 support when WordPress 3.6 is released.
 *
 * @uses mp_core_header_style()
 * @uses mp_core_admin_header_style()
 * @uses mp_core_admin_header_image()
 *
 * @package mp_core
 */

global $wp_version;
if ( version_compare( $wp_version, '3.4', '>=' ) ) {
	$args = array(
	'default-color' => '000000',
	'default-image' => get_template_directory_uri() . '/css/images/texturetastic_gray.png',
	);
	add_theme_support( 'custom-background', $args );
}else{
	add_custom_background( $args );
}

