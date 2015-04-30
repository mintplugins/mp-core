<?php
/**
 * This file contains Template Tags
 *
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Functions
 *
 * @link       http://mintplugins.com/doc/move-plugins-core-api/
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */
 

/**
 * This function returns the featured image url of a post
 *  
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_the_featured_image/
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
	
	if ( empty( $post_id ) ){
		return false;	
	}
	
	//Default width if blank
	$width = isset( $width ) ? $width : 600;
	
	//Set crop if there is a height. If not, don't crop
	$crop = isset( $height ) ? true : false;
	
	//Set default for featured image
	$image_url = has_filter('mp_featured_image_default') ? get_filter('mp_featured_image_default', '') : NULL;
	
	//get the post thumbnail for this post
	$image_id = get_post_thumbnail_id($post_id);  
	
	if ($image_id != ""){ 
	
		$image_url = wp_get_attachment_image_src($image_id,'full');  
		$image_url = $image_url[0];
		
		if (is_ssl()) {
			//action to take for page using SSL
			$image_url = str_replace( 'http://', 'https://', $image_url );
		}
		
		return $before . mp_aq_resize( $image_url, $width, $height, $crop ) . $after;    
	}
           
}
  
/**
 * This is a simple function which gets just the avatar URL without the wrapper tag
 *  
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_get_avatar_url/
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
 * @link     http://mintplugins.com/doc/mp_core_get_avatar/
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
 * Get video using URL. If it is a supported oembed url, use that. If it is not, set it to just be the url passed-in. 
 * Return iframe containing video at 100% width and height over a 16X9 image * undeneath to size it.
 * Supports videos passed-in in iframes as well. Pretty much, any video you throw at this, it will return it ready for responsive sizing.
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_oembed_get/
 * @param    string $video_url The URL of the Video or an iframe html
 * @param    int $min_width Optional. The minimum width in pixels this video should ever be.
 * @param    int $max_width Optional. The maximum width in pixels this video should ever be.
 * @return   string $html_output An iframe html tag containing the video wrapped in a div set to 100% width over a 16x9 image.
 */ 
function mp_core_oembed_get($video_url, $args = NULL){
	
	$args_defaults = array(
		'min_width' => NULL,
		'max_width' => NULL,
		'iframe_css_id' => NULL,
		'iframe_css_class' => NULL,
	);
	
	//Get and parse args
	$args = wp_parse_args( $args, $args_defaults );
	
	//Set CSS ID	
	$args['iframe_css_id'] = !empty( $args['iframe_css_id'] ) ? 'id="' . $args['iframe_css_id'] . '"' : '';
	
	//Set CSS Class	
	$args['iframe_css_class'] = !empty( $args['iframe_css_class'] ) ? 'class="' . $args['iframe_css_class'] . '"' : '';
	
	//Check if iframe exists in the video url
	$iframe = strpos( html_entity_decode($video_url), '<iframe' );
		
	//Is this a URL?
	if ( $iframe === false ){
		
		//The list of supported oembed providers
		$providers = array(
			'#https?://(www\.)?youtube\.com/watch.*#i'           => array( 'http://www.youtube.com/oembed',                     true  ),
			'http://youtu.be/*'                                  => array( 'http://www.youtube.com/oembed',                     false ),
			'http://blip.tv/*'                                   => array( 'http://blip.tv/oembed/',                            false ),
			'#https?://(www\.)?vimeo\.com/.*#i'                  => array( 'http://vimeo.com/api/oembed.{format}',              true  ),
			'#https?://(www\.)?dailymotion\.com/.*#i'            => array( 'http://www.dailymotion.com/services/oembed',        true  ),
			'http://dai.ly/*'                                    => array( 'http://www.dailymotion.com/services/oembed',        false ),
			'#https?://(www\.)?flickr\.com/.*#i'                 => array( 'http://www.flickr.com/services/oembed/',            true  ),
			'http://flic.kr/*'                                   => array( 'http://www.flickr.com/services/oembed/',            false ),
			'#https?://(.+\.)?smugmug\.com/.*#i'                 => array( 'http://api.smugmug.com/services/oembed/',           true  ),
			'#https?://(www\.)?hulu\.com/watch/.*#i'             => array( 'http://www.hulu.com/api/oembed.{format}',           true  ),
			'#https?://(www\.)?viddler\.com/.*#i'                => array( 'http://lab.viddler.com/services/oembed/',           true  ),
			'http://qik.com/*'                                   => array( 'http://qik.com/api/oembed.{format}',                false ),
			'http://revision3.com/*'                             => array( 'http://revision3.com/api/oembed/',                  false ),
			'http://i*.photobucket.com/albums/*'                 => array( 'http://photobucket.com/oembed',                     false ),
			'http://gi*.photobucket.com/groups/*'                => array( 'http://photobucket.com/oembed',                     false ),
			'#https?://(www\.)?scribd\.com/.*#i'                 => array( 'http://www.scribd.com/services/oembed',             true  ),
			'http://wordpress.tv/*'                              => array( 'http://wordpress.tv/oembed/',                       false ),
			'#https?://(.+\.)?polldaddy\.com/.*#i'               => array( 'http://polldaddy.com/oembed/',                      true  ),
			'#https?://(www\.)?funnyordie\.com/videos/.*#i'      => array( 'http://www.funnyordie.com/oembed',                  true  ),
			'#https?://(www\.)?twitter\.com/.+?/status(es)?/.*#i'=> array( 'https://api.twitter.com/1/statuses/oembed.{format}', true ),
 			'#https?://(www\.)?soundcloud\.com/.*#i'             => array( 'http://soundcloud.com/oembed',                      true  ),
			'#https?://(www\.)?slideshare\.net/*#'               => array( 'http://www.slideshare.net/api/oembed/2',            true  ),
			'#http://instagr(\.am|am\.com)/p/.*#i'               => array( 'http://api.instagram.com/oembed',                   true  ),
			'#https?://(www\.)?rdio\.com/.*#i'                   => array( 'http://www.rdio.com/api/oembed/',                   true  ),
			'#https?://rd\.io/x/.*#i'                            => array( 'http://www.rdio.com/api/oembed/',                   true  ),
			'#https?://(open|play)\.spotify\.com/.*#i'           => array( 'https://embed.spotify.com/oembed/',                 true  ),
		);
		
		/**
		 * Filter the list of oEmbed providers.
		 *
		 * Discovery is disabled for users lacking the unfiltered_html capability.
		 * Only providers in this array will be used for those users.
		 *
		 * @see wp_oembed_add_provider()
		 *
		 * @since 2.9.0
		 *
		 * @param array $providers An array of popular oEmbed providers.
		 */
		$providers = apply_filters( 'oembed_providers', $providers );
		
		
		//Loop through providers
		foreach ( $providers as $matchmask => $data ) {
			list( $providerurl, $regex ) = $data;

			// Turn the asterisk-type provider URLs into regex
			if ( !$regex ) {
				$matchmask = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $matchmask ), '#' ) ) . '#i';
				$matchmask = preg_replace( '|^#http\\\://|', '#https?\://', $matchmask );
			}
			
			//If our oembed matches one of the providers listed
			if ( preg_match( $matchmask, $video_url ) ) {
				//Get the oembed from the wp_oembed function
				$video_oembed = !is_array( $video_url ) ? wp_oembed_get( $video_url ) : NULL;
				break;
			}
		}
		
		//If video_oembed is set and this is a supported oembed url
		if ( isset( $video_oembed ) && $video_oembed ){
						
			$video_code_explode = explode( '<iframe ', $video_oembed );
			$video_code = '<iframe ' . $args['iframe_css_id'] . ' ' . $args['iframe_css_class'] . ' seamless="seamless" scrolling=no" style="position:absolute; width:100%; height:100%; top:0; left:0px; border:none;';
			
			//The link passed isn't something we can embed - so return the original URL
			if ( !isset( $video_code_explode[1] ) ){
				//For some very strange reason, this returns blank unless it is sent with an additional string - so I added a space to the end...?
				return $video_url . ' ';
			}
			
			$video_code .= '" ' . $video_code_explode[1];	
			
			$iframe_code = $video_code;			
			
		}
		//If this is not a supported oembed url (like youtube.com/embed/...")
		else{
			
			//If this is an mp4
			if ( strpos( $video_url, 'mp4' ) !== false ){
				
				//If the video should not have controls
				if ( strpos( $video_url, 'controls=false' ) !== false ){ 
					$controls = NULL;
				}
				//If this video should have controls
				else{
					$controls = 'controls ';	
				}
				
				//If the video should loop
				if ( strpos( $video_url, 'loop=true' ) !== false ){ 
					$loop = 'loop="" ';
				}
				//If this video should not Loop
				else{
					$loop = NULL;	
				}
				
				//If the video should autoplay
				if ( strpos( $video_url, 'autoplay=true' ) !== false ){ 
					$autoplay = 'autoplay="" ';
				}
				//If this video should not autoplay
				else{
					$autoplay = NULL;
				}
				
				$iframe_code = '<video width="100%" height="100%" class="mp-core-html5-video-autoplay" style="position:absolute; top:0; left:0;" preload="auto" ' . $controls . $loop . $autoplay . '>';
					$iframe_code .= '<source src="' . $video_url . '" type="video/mp4" />';
				$iframe_code .= '</video>';
				
				$iframe_code .= "<script type=\"text/javascript\">
					jQuery(document).ready(function($){
						$('.mp-core-html5-video-autoplay').each( function(){
							$(this).get(0).play();
						});
					});
				</script>";
			}
			//If we aren't sure what type of file this is
			else{
				//Embed it in an iframe					
				$iframe_code = '<iframe ' . $args['iframe_css_id'] . ' ' . $args['iframe_css_class'] . ' seamless="seamless" scrolling=no" style="position:absolute; width:100%; height:100%; top:0; left:0px; border:none;" src="' . $video_url . '" /></iframe>';
			}
							
		}
			
	}
	//Is this an iframe?
	else{
		
		//Video code without width and height attributes
		$iframe_code = preg_replace('/(<[^>]+) width=".*?"/i', '$1', preg_replace('/(<[^>]+) height=".*?"/i', '$1', html_entity_decode($video_url)));
				
		//If there is a value in $iframe_code
		if ( !empty($iframe_code) ){
			
			//Add custom syling to the iframe
			$iframe_code = explode( '<iframe ', html_entity_decode($iframe_code) );
			
			//Cut off anything after the iframe
			$iframe_code = explode( '>', $iframe_code[1]);
						
			$iframe_code = '<iframe ' . $args['iframe_css_id'] . ' ' . $args['iframe_css_class']  . ' seamless="seamless" scrolling=no" style="position:absolute; width:100%; height:100%; top:0; left:0px; border:none;" ' . $iframe_code[0] . '/></iframe>';
										
		}
		
	}

	$html_output = '<div class="mp-core-oembed-full-width-div" style="display:inline-block; position:relative; width:100%; vertical-align:top;';
	$html_output .= !empty( $args['min_width'] ) ? ' min-width:' . $args['min_width'] . 'px; margin: 0px auto 0px auto;' : NULL;
	$html_output .= !empty( $args['max_width'] ) ? ' max-width:' . $args['max_width'] . 'px; margin: 0px auto 0px auto;' : NULL;
	$html_output .= '">';
		$html_output .= '<img class="mp-core-oembed-full-width-img" style="position:relative; display:block; padding:0px; margin:0px; width:100%; border:none;'; 
		$html_output .= '" width="100%" src="' . plugins_url( 'images/16x9.png', dirname(dirname(__FILE__))) . '"/>';
		$html_output .= $iframe_code;
	$html_output .= '</div>';
	
	
	return $html_output;
}