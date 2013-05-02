<?php

/*
|--------------------------------------------------------------------------
| THEME SPECIFIC INCLUDES
|--------------------------------------------------------------------------
*/
function mp_core_theme_specific_scripts(){
	
	/**
	 * Custom template tags for this theme.
	 */
	require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/template-tags/template-tags.php' );
	
	/**
	 * Theme Customizer Logo Template Tag
	 */
	require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/template-tags/logo.php' );
	
	/**
	 * Comments Template Tag
	 */
	require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/template-tags/comments.php' );
	
	/**
	 * Include Misc Functions
	 */
	require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/misc-functions/misc-functions.php' );
	
	/**
	 * Custom functions that act independently of the theme templates
	 */
	require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/misc-functions/extras.php' );
	
	/**
	 * Enqueue Default CSS
	 */
	require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/css/enqueue-css.php' );
	
}

//To include these scripts in your theme, use the following line of code:
//add_action( 'after_setup_theme', 'mp_core_theme_specific_scripts' );