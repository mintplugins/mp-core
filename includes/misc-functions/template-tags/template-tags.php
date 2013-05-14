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
function mp_core_get_avatar_url( $get_avatar ){
    $matches = explode("src='", $get_avatar);
    $matches = explode("'", $matches[1]);
	return ($matches[0]);
}

/**
 * Get oembed and set it to be 100% width and height matching
 * Usage: mp_get_avatar_url( get_avatar( $email, 32 ) );
 */ 
function mp_core_oembed_get($video_url, $min_width = NULL, $max_width = NULL){
	
	$video_code_explode = wp_oembed_get($video_url);
	
	if ( !empty($video_code_explode) ){
		
		$video_code_explode = explode( '<iframe ', $video_code_explode );
		$video_code = '<iframe style="position:absolute; width:100%; height:100%; top:0; left:0px;';
		$video_code .= '" ' . $video_code_explode[1];
		
		$html_output = '<div class="mp-core-oembed-full-width-div" style="display:block; position:relative; width:100%;';
		$html_output .= !empty( $min_width ) ? ' min-width:' . $min_width . 'px; margin: 0px auto 0px auto;' : NULL;
		$html_output .= !empty( $max_width ) ? ' max-width:' . $max_width . 'px; margin: 0px auto 0px auto;' : NULL;
		$html_output .= '">';
		$html_output .= '<img class="mp-core-oembed-full-width-img" style="position:relative; display:block; '; 
		$html_output .= '" width="100%" src="' . plugins_url( 'images/16x9.gif', dirname(dirname(__FILE__))) . '"/>' . $video_code;
		
		$html_output .= '</div>';
		return $html_output;
	
	}
}
 

