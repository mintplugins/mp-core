<?php
/**
 * Custom functions/filters used specifically for themes. These functions come with the underscores theme and thus have been included.
 *
 * Eventually, some of the functionality here could be replaced by core features or eliminated.
 * 
 * @link http://mintplugins.com/doc/move-plugins-core-api/
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
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link. If a theme doesn't use the 
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_page_menu_args/
 * @param    array $args Options passed to the wp_page_menu() function in WP
 * @return   array Options passed to the wp_page_menu() function in WP
 */
function mp_core_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'mp_core_page_menu_args' );

/**
 * Adds custom classes to the array of body classes.
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_body_classes/
 * @see      is_multi_author()
 * @param    array $classes The names of the classes to add to the body
 * @return   array The names of the classes to add to the body
 */
function mp_core_body_classes( $classes ) {
	
	// If this WordPress has more than 1 author
	if ( is_multi_author() ) {
		
		//Add the class "grou-blog" to the body
		$classes[] = 'group-blog';
	}

	return $classes;
}
add_filter( 'body_class', 'mp_core_body_classes' );

/**
 * Filter in a link to a content ID attribute for the next/previous image links on image attachment pages
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_enhanced_image_navigation/
 * @see      is_attachment()
 * @see      wp_attament_is_image()
 * @see      get_post()
 * @param    string $url The url to the attachment
 * @param    string $id The post ID of the attachment
 * @return   string The url to the attachment with #main added to it if it is the main image
 */
function mp_core_enhanced_image_navigation( $url, $id ) {
	
	//If the page we are on is not an attachment page and the ID passed-in is not an image
	if ( ! is_attachment() && ! wp_attachment_is_image( $id ) ){
	
		//Just return the passed-in URL exactly as is
		return $url;
		
	}
	
	//Get the WP Post Object for this passed-in ID
	$image = get_post( $id );
	
	//If this post has a parent post and that post parent is not this attachment
	if ( ! empty( $image->post_parent ) && $image->post_parent != $id ){
	
		//Add '#main' to the end of the URL
		$url .= '#main';
		
	}
    
	//Return the URL
	return $url;
}
add_filter( 'attachment_link', 'mp_core_enhanced_image_navigation', 10, 2 );

/**
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_wp_title/
 * @global   int $page The page of the post, as specified by the query var page 
 * @global   boolean $paged Whether this page has multiple pages to it
 * @param    string $title The title of the current page
 * @param    string $sep The separator
 * @return   string The new title of the page
 */
function mp_core_wp_title( $title, $sep ) {
	global $page, $paged;

	if ( is_feed() )
		return $title;

	// Add the blog name
	$title .= get_bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title .= " $sep $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		$title .= " $sep " . sprintf( __( 'Page %s', 'mp_core' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'mp_core_wp_title', 10, 2 );