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
	
	//Default styles for tinymce
	add_editor_style( plugins_url( '/theme-specific/css/the-content.css', dirname( __FILE__ ) ) );
	
	//All inline styles including the customizer
	add_editor_style( plugins_url('/css/core/tinymce-css.php', dirname( __FILE__ ) ) );
}
add_action( 'admin_init', 'mp_core_addTinyMCELinkClasses' );