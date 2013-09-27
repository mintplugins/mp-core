<?php
/**
 * This file contains the enqueueing of css scripts used in theme creation
 *
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Theme Specific Functions
 *
 * @copyright  Copyright (c) 2013, Move Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */

/**
 * Theme specific CSS
 *
 * @since    1.0.0
 * @see      wp_enqueue_style()
 * @return   void
 */
function mp_core_enqueue_theme_specific_css(){
	
	//Enqueue CSS standard for WordPress page/post content. IE right and left aligned images etc.
	wp_enqueue_style( 'mp_core_the_content_css', plugins_url('css/the-content.css', dirname(__FILE__)) );
}
add_action( 'wp_enqueue_scripts', 'mp_core_enqueue_theme_specific_css' );