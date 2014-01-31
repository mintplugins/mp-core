<?php
/**
 * This file contains various functions
 *
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Functions
 *
 * @copyright  Copyright (c) 2013, Move Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */
 
 
//Front end scripts
function mp_core_enqueue_scripts(){
 
 	//no front end scripts currently
			
}
add_action( 'wp_enqueue_scripts', 'mp_core_enqueue_scripts' );

/**
 * Add and return styles for the TinyMCE styles
 *
 * @since    1.0.0
 * @link     http://codex.wordpress.org/Function_Reference/add_editor_style
 * @see      get_bloginfo()
 * @param    array $args See link for description.
 * @return   void
 */
function mp_core_addTinyMCELinkClasses( $wp ) {	
	
	//Themes and plugins will hook to this to add styles to the editor
	do_action('mp_core_editor_styles');
	
	//All inline styles including the customizer
	add_editor_style( plugins_url('/css/core/tinymce-css.php', dirname( __FILE__ ) ) );
}
add_action( 'admin_init', 'mp_core_addTinyMCELinkClasses' );

/**
 * Convert a hex color code to an RGB array
 *
 * @since    1.0.0
 * @link     http://codex.wordpress.org/Function_Reference/mp_core_hex2rgb
 * @param    string $hex a colour hex
 * @return   array $rgb Format is [0]R [1]G [2]B in that order: Array ( [0] => 204 [1] => 204 [2] => 204 )
 */
function mp_core_hex2rgb( $hex ) {
	
	if (!empty($hex)){
	   $hex = str_replace("#", "", $hex);
	
	   if(strlen($hex) == 3) {
		  $r = hexdec(substr($hex,0,1).substr($hex,0,1));
		  $g = hexdec(substr($hex,1,1).substr($hex,1,1));
		  $b = hexdec(substr($hex,2,1).substr($hex,2,1));
	   } else {
		  $r = hexdec(substr($hex,0,2));
		  $g = hexdec(substr($hex,2,2));
		  $b = hexdec(substr($hex,4,2));
	   }
	   $rgb = array($r, $g, $b);
	   //return implode(",", $rgb); // returns the rgb values separated by commas
	   return $rgb; // returns an array with the rgb values
	}
	else{
		return NULL;
	}
}
				
/**
 * Ajax to display help content
 *
 * @since    1.0.0
 * @link     http://codex.wordpress.org/Function_Reference/mp_core_hex2rgb
 * @param    string $hex a colour hex
 * @return   array $rgb Format is [0]R [1]G [2]B in that order: Array ( [0] => 204 [1] => 204 [2] => 204 )
 */
function mp_core_show_help_content_ajax(){
	
	//Get Help href
	$help_url = $_POST['help_href'];
	
	//Get Help Type
	$help_type = $_POST['help_type'];
	
	if ( $help_type == 'oembed' ){
		echo mp_core_oembed_get($help_url);
	}
	else{
		echo '<iframe src="' . $help_url . '" width="100%" height="400px"/>';	
	}
	
	exit;
}
add_action( 'wp_ajax_mp_core_help_content_ajax', 'mp_core_show_help_content_ajax' );