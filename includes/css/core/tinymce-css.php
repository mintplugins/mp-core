<?php
/**
 * This page is meant to be loaded outside of "WordPress". It creates css needed for the TinyMCE from the customizer and font classes in MP CORE
 *
 * @link http://mintplugins.com/doc/
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage CSS
 *
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define('WP_USE_THEMES', false);

/** Loads the WordPress Environment and Template */
require(  dirname(dirname(dirname(dirname(dirname(dirname(dirname( __FILE__ ))))))) . '/wp-load.php' );

/**
 * Create CSS Output for Tiny MCE
 */
function mp_core_create_tiny_mce_page(){	

	//Create hook which outputs css to page
	do_action( 'mp_core_tinymce_css' );

}
add_action('shutdown', 'mp_core_create_tiny_mce_page' );
