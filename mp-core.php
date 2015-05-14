<?php
/*
Plugin Name: MP Core
Plugin URI: http://mintplugins.com
Description: A core group of classes and functions shared by all MintPlugins creating efficiency, robustness, and speed.
Version: 1.0.2.0
Author: Mint Plugins
Author URI: https://mintplugins.com
Text Domain: mp_core
Domain Path: languages
License: GPL2
*/

/*  Copyright 2015  Phil Johnston  (email : phil@mintplugins.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Mint Plugins Core.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Mint Plugins Core, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
*/
// Plugin version
if( !defined( 'MP_CORE_VERSION' ) )
	define( 'MP_CORE_VERSION', '1.0.2.0' );

// Plugin Folder URL
if( !defined( 'MP_CORE_PLUGIN_URL' ) )
	define( 'MP_CORE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Plugin Folder Path
if( !defined( 'MP_CORE_PLUGIN_DIR' ) )
	define( 'MP_CORE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Plugin Root File
if( !defined( 'MP_CORE_PLUGIN_FILE' ) )
	define( 'MP_CORE_PLUGIN_FILE', __FILE__ );
	
// Javascripts URL - Used as a prefix to load javascript scripts (non core related) included with the core
if( !defined( 'MP_CORE_JS_SCRIPTS_URL' ) )
	define( 'MP_CORE_JS_SCRIPTS_URL', plugin_dir_url( __FILE__ ) . 'includes/js/utility/' );

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
 * Activation Hook Function
 */
require( MP_CORE_PLUGIN_DIR . 'includes/misc-functions/install.php' );

/**
 * Include Plugin Installer Class
 */
require( MP_CORE_PLUGIN_DIR . 'includes/plugin-checker/class-plugin-installer.php' );

/**
 * Include Plugin Checker Class
 */
require( MP_CORE_PLUGIN_DIR . 'includes/plugin-checker/class-plugin-checker.php' );

/**
 * Include Plugin Directory Class
 */
require( MP_CORE_PLUGIN_DIR . 'includes/plugin-directory/class-plugin-directory.php' );

/**
 * Include Mint Plugins Directory Page
 */
require( MP_CORE_PLUGIN_DIR . 'includes/plugin-directory/mp-core-plugin-directory.php' );

/**
 * Include Theme Updater
 */
require( MP_CORE_PLUGIN_DIR . 'includes/updater/themes/class-theme-updater.php' );

/**
 * Include Plugin Updater
 */
require( MP_CORE_PLUGIN_DIR . 'includes/updater/plugins/class-plugin-updater.php' );

/**
 * Keep MP Core updated
 */
require( MP_CORE_PLUGIN_DIR . 'includes/updater/mp-core-update.php' );

/**
 * Include Settings Class
 */
require( MP_CORE_PLUGIN_DIR . 'includes/settings/class-settings-php-5.2.php' );

/**
 * Include Metabox Class
 */
require( MP_CORE_PLUGIN_DIR . 'includes/metaboxes/class-metabox.php' );

/**
 * Include Widget Class
 */
require( MP_CORE_PLUGIN_DIR . 'includes/widgets/class-widget.php' );

/**
 * Include Shortcode Class
 */
require( MP_CORE_PLUGIN_DIR . 'includes/shortcodes/class-shortcode-insert.php' );

/**
 * Include Customizer Class
 */
require( MP_CORE_PLUGIN_DIR . 'includes/customizer/class-customizer.php' );

/**
 * Include Font Class
 */
require( MP_CORE_PLUGIN_DIR . 'includes/fonts/class-font.php' );

/**
 * Include AQ Resizer Function
 */
require( MP_CORE_PLUGIN_DIR . 'includes/aq-resizer/aq-resizer.php' );
require( MP_CORE_PLUGIN_DIR . 'includes/aq-resizer/aq-resizer-ratio-check.php' );

/**
 * Template Tags
 */
require( MP_CORE_PLUGIN_DIR . 'includes/misc-functions/template-tags/template-tags.php' );

/**
 * Get Data Functions
 */
require( MP_CORE_PLUGIN_DIR . 'includes/misc-functions/get-data.php' );

/**
 * Misc Functions
 */
require( MP_CORE_PLUGIN_DIR . 'includes/misc-functions/misc-functions.php' );

/**
 * Animation Functions
 */
require( MP_CORE_PLUGIN_DIR . 'includes/misc-functions/animation-functions.php' );

/**
 * Include License Checking functions
 */
require( MP_CORE_PLUGIN_DIR . 'includes/licensing/licensing-functions.php' );
require( MP_CORE_PLUGIN_DIR . 'includes/licensing/class-show-license-form.php' );

/*
|--------------------------------------------------------------------------
| THEME SPECIFIC INCLUDES
|--------------------------------------------------------------------------
*/

/**
 * Theme Specific scripts
 */
require( MP_CORE_PLUGIN_DIR . 'includes/theme-specific/theme-specific.php' );