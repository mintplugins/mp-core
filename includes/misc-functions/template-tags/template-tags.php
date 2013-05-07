<?php
/**
 *Template Tags
 */
 
/**
 *The_featured_image - displays the featured image of a post
 */
function mp_core_the_featured_image( $post_id, $width, $height, $before = NULL, $after = NULL ){
	
	//Set default for featured image
	$image_url = has_filter('mp_featured_image_default') ? get_filter('mp_featured_image_default', '') : NULL;
	
	//get the post thumbnail for this post
	$image_id = get_post_thumbnail_id($post_id);  
	if ($image_id != ""){ 
		$image_url = wp_get_attachment_image_src($image_id,'full');  
		$image_url = $image_url[0];
		
		return $before . mp_aq_resize( $image_url, $width, $height, true ) . $after;    
	}
           
}
 
/**
 * Get just the avatar URL without the wrapper tag
 * Usage: mp_get_avatar_url( get_avatar( $email, 32 ) );
 */ 
function mp_get_avatar_url( $get_avatar ){
    $matches = explode("src='", $get_avatar);
    $matches = explode("'", $matches[1]);
	return ($matches[0]);
}
 

