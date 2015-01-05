<?php
/**
* Title		: Aqua Resizer
* Description	: Resizes WordPress images on the fly
* Version	: 1.1.6
* Author	: Syamil MJ
* Author URI	: http://aquagraphite.com
* License	: WTFPL - http://sam.zoy.org/wtfpl/
* Documentation	: https://github.com/sy4mil/Aqua-Resizer/
*
* @param	string $url - (required) must be uploaded using wp media uploader
* @param	int $width - (required)
* @param	int $height - (optional)
* @param	bool $crop - (optional) default to soft crop
* @param	bool $single - (optional) returns an array if false
* @uses		wp_upload_dir()
* @uses		image_resize_dimensions() | image_resize()
* @uses		wp_get_image_editor()
*
* @return str|array
*/
if ( !function_exists( 'mp_aq_resize' ) ){
	function mp_aq_resize( $url, $width, $height = null, $crop = null, $single = true ) {
		
		//validate inputs
		if(!$url OR !$width ) return false;
		
		//If using ajax, go retina as a default
		if ( defined('DOING_AJAX') ){	
			
			//screen is 2x so double the size of images
			$aq_width = $width * 2;
			$aq_height = $height * 2;
			
		}else{
			
			//screen is 2x so double the size of images
			$aq_width = has_filter('aq_resize_width') ? apply_filters( 'aq_resize_width', $width) : $width;
			$aq_height = has_filter('aq_resize_height') ? apply_filters( 'aq_resize_height', $height) : $height;
			
		}
		
		//define upload path & dir
		$upload_info = wp_upload_dir();
		$upload_dir = $upload_info['basedir'];
		$upload_url = $upload_info['baseurl'];
		
		if ( is_ssl() ) {
			$upload_url = str_replace( 'http://', 'https://', $upload_url );
		}
		
		//check if $img_url is local
		if(strpos( $url, $upload_url ) === false) return $url;
		
		//define path of image
		$rel_path = str_replace( $upload_url, '', $url);
		$img_path = $upload_dir . $rel_path;
		
		//check if img path exists, and is an image indeed
		if( !file_exists($img_path) OR !getimagesize($img_path) ) return $url;
		
		//get image info
		$info = pathinfo($img_path);
		$ext = $info['extension'];
		list($orig_w,$orig_h) = getimagesize($img_path);
		
		//If the original width and height of the image are not larger than the retina size required, set them back to the passed-in values
		if ($aq_width > ($orig_w) || $aq_height > ($orig_h)){	
			$aq_width = $width;
			$aq_height = $height;
			
			//If the original width and height of the image are not larger than the passed-in values, find the lowest common denominator and create a cropped image at the same ratio
			if ($aq_width > ($orig_w) || $aq_height > ($orig_h)){	
			
				//If the width is greater than the height
				if ( $aq_width > $aq_height && $aq_height > 0 ){
					
					//Find the lowest common denominator of width=? when height=1
					$width_lcd = $aq_width / $aq_height;
										
					//Find the value for height
					$adjusted_aq_height = $orig_w / $width_lcd;
					
					//Set the width to the actual width of the image						
					$adjusted_aq_width = $orig_w;
					
					//If the height of the image is shorter than it needs to be with the width at actual size,
					if ( $adjusted_aq_height > $orig_h ){
						
						//Find out how wide we can make this image without being too short on the height
						
						//Find the lowest common denominator of width=? when height=1
						$height_lcd = $aq_height / $aq_width;
											
						//Find the value for width
						$adjusted_aq_width = $orig_h / $height_lcd;
						
						//Set the width to the actual width of the image						
						$adjusted_aq_height = $orig_h;
						
						
					}
						
					$aq_width = $adjusted_aq_width;	
					$aq_height = $adjusted_aq_height;	
					
								
				}
				//If the height is greater than the width
				else if ( $aq_height > $aq_width && $aq_width > 0){
					
					//Find the lowest common denominator of width=? when height=1
					$height_lcd = $aq_height / $aq_width;
										
					//Find the value for height
					$adjusted_aq_width = $orig_h / $height_lcd;
					
					//Set the width to the actual width of the image						
					$adjusted_aq_height = $orig_h;
					
					//If the width of the image is more narrow than it needs to be with the height at actual size,
					if ( $adjusted_aq_width > $orig_w ){
						
						//Find out how high we can make this image without being too narrow on the width
						
						//Find the lowest common denominator of width=? when height=1
						$width_lcd = $aq_width / $aq_height;
											
						//Find the value for width
						$adjusted_aq_height = $orig_w / $width_lcd;
						
						//Set the width to the actual width of the image						
						$adjusted_aq_width = $orig_w;
						
						
					}
						
					$aq_width = $adjusted_aq_width;	
					$aq_height = $adjusted_aq_height;	
					
								
				}
			
			}
		}
		
		//get image size after cropping
		$dims = image_resize_dimensions($orig_w, $orig_h, $aq_width, $aq_height, $crop);
		
		$dst_w = $dims[4];
		$dst_h = $dims[5];
		
		//use this to check if cropped image already exists, so we can return that instead
		$suffix = "{$dst_w}x{$dst_h}";
		$dst_rel_path = str_replace( '.'.$ext, '', $rel_path);
		$destfilename = "{$upload_dir}{$dst_rel_path}-{$suffix}.{$ext}";
		
		if(!$dst_h) {
			//can't resize, so return original url
			$img_url = $url;
			$dst_w = $orig_w;
			$dst_h = $orig_h;
		}
		//else check if cache exists
		elseif(file_exists($destfilename) && getimagesize($destfilename)) {
			$img_url = "{$upload_url}{$dst_rel_path}-{$suffix}.{$ext}";
		}
		//else, we resize the image and return the new resized image url
		else {
						
			// Note: This pre-3.5 fallback check will edited out in subsequent version
			if(function_exists('wp_get_image_editor')) {
			
				$editor = wp_get_image_editor($img_path);
								
				if ( is_wp_error( $editor ) || is_wp_error( $editor->resize( $aq_width, $aq_height, $crop ) ) )
					return false;
				
				$resized_file = $editor->save();
				
				if(!is_wp_error($resized_file)) {
					$resized_rel_path = str_replace( $upload_dir, '', $resized_file['path']);
					$img_url = $upload_url . $resized_rel_path;
				} else {
					return false;
				}
				
			} else {
				
				$resized_img_path = image_resize( $img_path, $aq_width, $aq_height, $crop ); // Fallback foo
				if(!is_wp_error($resized_img_path)) {
					$resized_rel_path = str_replace( $upload_dir, '', $resized_img_path);
					$img_url = $upload_url . $resized_rel_path;
				} else {
					return false;
				}
			
			}
			
		}
		
		//return the output
		if($single) {
			//str return
			$image = $img_url;
		} else {
			//array return
			$image = array (
				0 => $img_url,
				1 => $dst_w,
				2 => $dst_h
			);
		}
		
		return $image;
	}
}