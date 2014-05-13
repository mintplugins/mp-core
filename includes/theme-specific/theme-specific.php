<?php
/**
 * This file contains the function which includes theme specific functions included in the MP Core Plugin.
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
 * Theme Specific Scripts
 *
 * @access   public
 * @since    1.0.0
 * @return   void
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
	 * Comments Template Tag
	 */
	require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/template-tags/pagination.php' );
	
	/**
	 * Include Misc Functions
	 */
	require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/misc-functions/misc-functions.php' );
	
	/**
	 * Custom functions that act independently of the theme templates
	 */
	require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/misc-functions/extras.php' );
	
}

//To include these scripts in your theme, use the following line of code:
//add_action( 'after_setup_theme', 'mp_core_theme_specific_scripts' );