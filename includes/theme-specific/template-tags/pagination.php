<?php

/**
 * Paginate Links
 *
 * @since mp_core 1.0
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
			'base'      => remove_query_arg( $array_of_url_args_to_remove, get_pagenum_link(1)) . '%_%' . $url_args,
			'format'    => $format,
			'current'   => $current_page,
			'prev_next' => true,
			'prev_text'    => apply_filters( 'mp_core_pagination_prev', '<' ),
			'next_text'    => apply_filters( 'mp_core_pagination_prev', '>' ),
			'type'      => 'list',
			'show_all'  => true
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		echo ('<nav id="posts-navigation" class="row pagination mp-core-navigation">');
	
		echo paginate_links( apply_filters( 'mp_core_pagination', $args ) );
		
		echo ('</nav>');
	}
endif;