<?php 
 
/**
 * Menu fallback. Link to the menu editor if that is useful.
 *
 * @param  array $args
 * @return string
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
	
		$link = $link_before
			. '<a href="' .admin_url( 'nav-menus.php' ) . '">' . $before . __( 'Add a menu', 'mp_core' ) . $after . '</a>'
			. $link_after;
	
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
 * Make a home page with template from theme and assign to be front page
 *
 * @param  str $pagetemplate
 * @return boolean
 */
function mp_core_make_home_page( $oldname, $oldtheme=false ){
	
		//Check for page template:
		$home_page_template = mp_core_is_pagetemplate_active('templates/page-template-home.php');
			
		//Check if the home page template is in use:
		if ( !$home_page_template ){
					
			//Create page for Home
			$home_page = array(
				'post_title' => 'Home',
				'post_content' => '',
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => 'page'
			);
			
			$home_page = wp_insert_post( $home_page );
			
			//Assign the Home page template to it
			update_post_meta( $home_page, '_wp_page_template', 'templates/page-template-home.php' );
			
			//Set the Settings Reading Home Page to be the home page	
			update_option( 'page_on_front', $home_page);
		}
		//If it is in use, make sure it is the front page
		else{
			
			//Set the Settings Reading Home Page to be the home page	
			update_option( 'page_on_front', $home_page_template);
			
		}
		
	}
//To use place this in theme:
//add_action("after_switch_theme", "mp_core_make_home_page", 10 ,  2);

/**
 * Check if a page template is in use
 *
 * @param  str $pagetemplate
 * @return boolean
 */
function mp_core_is_pagetemplate_active($pagetemplate = '') {
	global $wpdb;	
	$result = $wpdb->get_row("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_page_template' and meta_value like '" . $pagetemplate . "'", ARRAY_A);
	
	if ($result['post_id']) {
		return $result['post_id'];
	} else {
		return FALSE;
	}
}