<?php
//Front end scripts
function mp_core_enqueue_scripts(){
 
	//Animate CSS
	wp_enqueue_style( 'mp_core_animate_css', plugins_url( 'css/animate-custom.css', dirname(__FILE__) ) );
		
}
add_action( 'wp_enqueue_scripts', 'mp_core_enqueue_scripts' );


/**
 * The function below is a temporary fix for this bug: http://core.trac.wordpress.org/ticket/18614
 */
function wordpress_fix_bug( $wp_query ) {
    if ( $wp_query->is_post_type_archive && $wp_query->is_tax ) {
        global $post_type_obj;
        $wp_query->is_tax = false;
        $post_type_obj = get_queried_object();
        if (empty($post_type_obj->labels)) {
            $post_type_obj->labels = new stdClass();
            $post_type_obj->labels->name = 'dev/hack to fix WordPress Bug';
        }
    }
}add_action( 'parse_query', 'wordpress_fix_bug' );