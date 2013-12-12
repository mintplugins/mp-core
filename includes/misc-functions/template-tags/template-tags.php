<?php
/**
 * This file contains Template Tags
 *
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Functions
 *
 * @link       http://moveplugins.com/doc/move-plugins-core-api/
 * @copyright  Copyright (c) 2013, Move Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */
 

/**
 * This function returns the featured image url of a post
 *  
 * @since    1.0.0
 * @link     http://moveplugins.com/doc/mp_core_the_featured_image/
 * @see      has_filter()
 * @see      get_filter()
 * @see      get_post_thumbnail_id()
 * @see      wp_get_attachment_image_src()
 * @see      mp_aq_resize()
 * @param    string $post_id The ID of the post who's featured image we want
 * @param    int $width The width of the image we want in pixels. Defaults to 600
 * @param    int $height The height of the image we want in pixels. Defaults to 600
 * @param    string $before Optional. A string to output directly before the image URL
 * @param    string $after Optional. A string to output directly after the image URL
 * @return   string The featured image's URL with $before and $after before and after respectively.
 */
function mp_core_the_featured_image( $post_id = NULL, $width = NULL, $height = NULL, $before = NULL, $after = NULL ){
	
	//Default setting for post id if blank
	global $post;
	$post_id = isset( $post_id ) ? $post_id : $post->ID;
	
	//Default width if blank
	$width = isset( $width ) ? $width : 600;
	
	//Default width if blank
	$height = isset( $height ) ? $height : 600;
	
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
 * This is a simple function which gets just the avatar URL without the wrapper tag
 *  
 * @since    1.0.0
 * @link     http://moveplugins.com/doc/mp_core_get_avatar_url/
 * @param    string $after The result of the get_avatar( $id_or_email, 32 ); is passed to this function as a string.
 * @return   string The avatar URL only without the wrapper tag.
 */
function mp_core_get_avatar_url( $get_avatar ){
    $matches = explode("src='", $get_avatar);
    $matches = explode("'", $matches[1]);
	return ($matches[0]);
}

/**
 * Get avatar tag at retina size (2x). This function is exactly the same as the WP default "get_avatar()" but returns image at 2x.
 *
 * @since    1.0.0
 * @link     http://moveplugins.com/doc/mp_core_get_avatar/
 * @param    string $comment The id or email of the user who's avatar we want
 * @param    int $size The size in pixels that this image should be at 1x.
 * @return   string The avatar URL only without the wrapper tag.
 */
function mp_core_get_avatar( $id_or_email, $size ){
	
	//Double the size for retina screens
	$size_doubled = $size * 2;
	
	//Get the avatar img tag
	$avatar_tag = get_avatar( $id_or_email, $size_doubled );
		
	//Explode the img tag
	$exploded_avatar = explode( "src='", $avatar_tag );	
	$avatar_url = explode( "'", $exploded_avatar[1] ); 
	$avatar_url = trim( $avatar_url[0]);
	
	return '<img alt="" src="' . $avatar_url . '" class="avatar avatar-' . $size . ' photo" width="' . $size . '" height="' . $size . '" >';
	
}

/**
 * Get oembed and set it to be 100% width and height using a 16x9 image undeneath to size it
 *
 * @since    1.0.0
 * @link     http://moveplugins.com/doc/mp_core_oembed_get/
 * @param    string $video_url The URL of the Video
 * @param    int $min_width Optional. The minimum width in pixels this video should ever be.
 * @param    int $max_width Optional. The maximum width in pixels this video should ever be.
 * @return   string $html_output An iframe html tag containing the video wrapped in a div set to 100% width over a 16x9 image.
 */ 
function mp_core_oembed_get($video_url, $min_width = NULL, $max_width = NULL){
	
	$video_code_explode = !is_array( $video_url ) ? wp_oembed_get( $video_url ) : NULL;
	
	if ( !empty($video_code_explode) ){
		
		$video_code_explode = explode( '<iframe ', $video_code_explode );
		$video_code = '<iframe style="position:absolute; width:100%; height:100%; top:0; left:0px;';
		$video_code .= '" ' . $video_code_explode[1];
				
		apply_filters( 'mp_core_oembed_video_code', $video_code );
		
		$html_output = '<div class="mp-core-oembed-full-width-div" style="display:inline-block; position:relative; width:100%;';
		$html_output .= !empty( $min_width ) ? ' min-width:' . $min_width . 'px; margin: 0px auto 0px auto;' : NULL;
		$html_output .= !empty( $max_width ) ? ' max-width:' . $max_width . 'px; margin: 0px auto 0px auto;' : NULL;
		$html_output .= '">';
		$html_output .= '<img class="mp-core-oembed-full-width-img" style="position:relative; display:block; '; 
		$html_output .= '" width="100%" src="' . plugins_url( 'images/16x9.gif', dirname(dirname(__FILE__))) . '"/>' . $video_code;
		
		$html_output .= '</div>';
		return $html_output;
	
	}
}