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