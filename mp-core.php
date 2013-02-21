<?php
/*
Plugin Name: Move Plugins - Core
Plugin URI: http://moveplugins.com
Description: A core group of classes and functions.
Version: 1.0
Author: Phil Johnston
Author URI: http://moveplugins.com
Text Domain: mp_core
Domain Path: languages
License: GPL2
*/

/*  Copyright 2012  Phil Johnston  (email : phil@moveplugins.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Move Plugins Core.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Move Plugins Core, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
*/
// Plugin version
if( !defined( 'MP_CORE_VERSION' ) )
	define( 'MP_CORE_VERSION', '1.0.0.0' );

// Plugin Folder URL
if( !defined( 'MP_CORE_PLUGIN_URL' ) )
	define( 'MP_CORE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Plugin Folder Path
if( !defined( 'MP_CORE_PLUGIN_DIR' ) )
	define( 'MP_CORE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Plugin Root File
if( !defined( 'MP_CORE_PLUGIN_FILE' ) )
	define( 'MP_CORE_PLUGIN_FILE', __FILE__ );

/*
|--------------------------------------------------------------------------
| GLOBALS
|--------------------------------------------------------------------------
*/

//None at the moment

/*
|--------------------------------------------------------------------------
| INTERNATIONALIZATION
|--------------------------------------------------------------------------
*/

function mp_core_textdomain() {

	// Set filter for plugin's languages directory
	$mp_core_lang_dir = dirname( plugin_basename( MP_CORE_PLUGIN_FILE ) ) . '/languages/';
	$mp_core_lang_dir = apply_filters( 'mp_core_languages_directory', $mp_core_lang_dir );


	// Traditional WordPress plugin locale filter
	$locale        = apply_filters( 'plugin_locale',  get_locale(), 'mp-core' );
	$mofile        = sprintf( '%1$s-%2$s.mo', 'mp-core', $locale );

	// Setup paths to current locale file
	$mofile_local  = $mp_core_lang_dir . $mofile;
	$mofile_global = WP_LANG_DIR . '/mp-core/' . $mofile;

	if ( file_exists( $mofile_global ) ) {
		// Look in global /wp-content/languages/mp_core folder
		load_textdomain( 'mp_core', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) {
		// Look in local /wp-content/plugins/message_bar/languages/ folder
		load_textdomain( 'mp_core', $mofile_local );
	} else {
		// Load the default language files
		load_plugin_textdomain( 'mp_core', false, $mp_core_lang_dir );
	}

}
add_action( 'init', 'mp_core_textdomain', 1 );

/*
|--------------------------------------------------------------------------
| INCLUDES
|--------------------------------------------------------------------------
*/

/**
 * Include Plugin Checker
 */
require( MP_CORE_PLUGIN_DIR . 'includes/plugin-checker/plugin-checker.php' );

/**
 * Include Settings Class
 */
require( MP_CORE_PLUGIN_DIR . 'includes/settings/settings-class.php' );
require( MP_CORE_PLUGIN_DIR . 'includes/settings/settings-samples/plugin-options/plugin-options.php' );
require( MP_CORE_PLUGIN_DIR . 'includes/settings/settings-samples/plugin-submenu/plugin-submenu.php' );

/**
 * Include Metabox Class
 */
require( MP_CORE_PLUGIN_DIR . 'includes/metaboxes/metabox-class.php' );

/**
 * Include Widget Class
 */
require( MP_CORE_PLUGIN_DIR . 'includes/widgets/widget-class.php' );

/**
 * Include AQ Resizer
 */
require( MP_CORE_PLUGIN_DIR . 'includes/aq_resizer/aq-resizer.php' );
require( MP_CORE_PLUGIN_DIR . 'includes/aq_resizer/aq-resizer-ratio-check.php' );

/*
|--------------------------------------------------------------------------
| THEME SPECIFIC INCLUDES
|--------------------------------------------------------------------------
*/

/**
 * Custom template tags for this theme.
 */
require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/template-tags/template-tags.php' );

/**
 * Custom functions that act independently of the theme templates
 */
require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/misc-functions/extras.php' );
	
/**
 * Implement the Custom Header feature
 */
require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/custom-wp/custom-header.php' );

/**
 * Implement the Custom Background feature
 */
//require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/custom-wp/custom-background.php' );

/**
 * Theme Customizer Custom Options
 */
//require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/theme-customizer/customize.php' );

/**
 * Include Misc Functions
 */
require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/misc-functions/misc-functions.php' );

/**
 * Include WPFC template functions
 */
require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/misc-functions/wpfc/misc-functions.php' );

if( is_admin() ) {
	//none at the moment
} else {
	//none at the moment
}