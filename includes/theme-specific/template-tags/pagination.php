<?php
/**
 * This page contains template tag function used to display pagination.
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
 * This template tag outputs pagination if needed. This function sets better defaults than just using paginate_links()
 *
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_paginate_links/
 * @see      has_filter()
 * @see      apply_filters()
 * @see      get_option()  
 * @see      get_query_var()
 * @see      mp_core_remove_query_arg()
 * @see      wp_parse_args()
 * @see      paginate_links()
 * @global   object $wp_query WP Query object.
 * @param    array $args See link for details.
 * @return   void
 */
if ( ! function_exists( 'mp_core_paginate_links' ) ) :
	function mp_core_paginate_links( $args = array() ) {
		
		global $wp_query;
				
		if ( get_query_var( 'paged' ) ) {
			$current_page = get_query_var( 'paged' );
		} else if ( get_query_var( 'page' ) ) {
			$current_page = get_query_var( 'page' );
		} else {
			$current_page = 1;
		}
		
		$permalink_structure = get_option('permalink_structure');
		$format = empty( $permalink_structure ) ? '?page=%#%' : 'page/%#%/';
		
		//Split the current url at the question mark
		$url_args = explode('?', $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );
		//If there is a question mark, store everything after it in url_args with a question mark
		$url_args = isset( $url_args[1] ) ? '?' . $url_args[1] : NULL;
		
		//This array stores all of the args (eg: ?s=MyTestSearch) that could be in the url. You can add your own using the filter 'mp_core_pagination_arg_remover'
		$array_of_url_args_to_remove = apply_filters ( 'mp_core_pagination_arg_remover', array('s') );
		
		$defaults = array(
			'total'     => $wp_query->max_num_pages,
			'base'      => mp_core_remove_query_arg( $array_of_url_args_to_remove, html_entity_decode(get_pagenum_link(1))) . '%_%' . $url_args,
			'format'    => $format,
			'current'   => $current_page,
			'prev_next' => true,
			'prev_text'    => apply_filters( 'mp_core_pagination_prev', '<' ),
			'next_text'    => apply_filters( 'mp_core_pagination_prev', '>' ),
			'type'      => 'list',
			'show_all'  => false
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		echo ('<nav id="posts-navigation" class="row pagination mp-core-pagination">');
	
		echo paginate_links( apply_filters( 'mp_core_pagination', $args ) );
		
		echo ('</nav>');
	}
endif;