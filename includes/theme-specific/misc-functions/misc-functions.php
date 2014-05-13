<?php 
/**
 * Custom functions specifically used in themes.
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
 * Menu fallback if no menu is set up. Link to "Appearance" > "Menus" so the user can manage their menus.
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_link_to_menu_editor/
 * @see      current_user_can()
 * @see      admin_url()
 * @param    array $args See wp-includes/nav-menu-template.php for available arguments
 * @return   string to use if no menu is found
 */
if ( ! function_exists( 'mp_core_link_to_menu_editor' ) ):
	function mp_core_link_to_menu_editor( $args )
	{
		if ( ! current_user_can( 'manage_options' ) )
		{
			return;
		}
	
		// see wp-includes/nav-menu-template.php for available arguments
		extract( $args );
		
		if ( is_user_logged_in() && current_user_can('edit_theme_options') ) {
			$link = $link_before
				. '<a href="' .admin_url( 'nav-menus.php' ) . '">' . $before . __( 'Add a menu', 'mp_core' ) . $after . '</a>'
				. $link_after;
		}
		else{
			$link = NULL;	
		}
	
		// We have a list
		if ( FALSE !== stripos( $items_wrap, '<ul' )
			or FALSE !== stripos( $items_wrap, '<ol' )
		)
		{
			$link = "<li>$link</li>";
		}
	
		$output = sprintf( $items_wrap, $menu_id, $menu_class, $link );
		if ( ! empty ( $container ) )
		{
			$output  = "<$container class='$container_class' id='$container_id'>$output</$container>";
		}
	
		if ( $echo )
		{
			echo $output;
		}
	
		return $output;
	}
endif;

/**
 * Returns true if a blog has more than 1 category
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_categorized_blog/
 * @see      set_transient()
 * @see      get_transient()
 * @see      get_categories()
 * @return   boolean Returns true if a blog has more than 1 category
 */
function mp_core_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) {
		// Create an array of all the categories that are attached to posts
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'all_the_cool_cats', $all_the_cool_cats );
	}

	if ( '1' != $all_the_cool_cats ) {
		// This blog has more than 1 category so mp_core_categorized_blog should return true
		return true;
	} else {
		// This blog has only 1 category so mp_core_categorized_blog should return false
		return false;
	}
}

/**
 * Flush out the transients used in mp_core_categorized_blog
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_category_transient_flusher/
 * @see      delete_transient()
 * @return   void
 */
function mp_core_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'all_the_cool_cats' );
}
add_action( 'edit_category', 'mp_core_category_transient_flusher' );
add_action( 'save_post', 'mp_core_category_transient_flusher' );
