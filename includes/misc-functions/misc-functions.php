<?php
/**
 * This file contains various functions
 *
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Functions
 *
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */
 
 
//Front end scripts
function mp_core_enqueue_scripts(){
 
 	//no front end scripts currently
			
}
add_action( 'wp_enqueue_scripts', 'mp_core_enqueue_scripts' );

/**
 * Check if a value exists in a variable. Similar to the "empty" PHP function - but returns true even if the value is 0.
 *
 * @since    1.0.0
 * @param    mixed $value_to_check See link for description.
 * @return   boolean
 */
function mp_core_value_exists( $value_to_check ) {	

	//If the value_to_check is empty 
	if ( empty( $value_to_check ) ){
		
		//If this value_to_check is set to be the number 0
		if ( is_numeric( $value_to_check ) ){
			
			return true;
			
		}
		//If it is truly just empty
		else{
			
			//return the default value
			return false;
		}
		
	}
	else{
		return true;	
	}
	
}

/**
 * Add and return styles for the TinyMCE styles
 *
 * @since    1.0.0
 * @link     http://codex.wordpress.org/Function_Reference/add_editor_style
 * @see      get_bloginfo()
 * @param    array $args See link for description.
 * @return   void
 */
function mp_core_addTinyMCELinkClasses( $wp ) {	
	
	//Themes and plugins will hook to this to add styles to the editor
	do_action('mp_core_editor_styles');
	
	//All inline styles including the customizer
	//add_editor_style( plugins_url('/css/core/tinymce-css.php', dirname( __FILE__ ) ) );
}
add_action( 'admin_init', 'mp_core_addTinyMCELinkClasses' );

/**
 * This function takes a string and changes all "weird" apostrphes and quotes and converts them to "normal" ones.
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_fix_quotes
 * @see      function_name()
 * @param    string $string See link for description.
 * @return   void
 */
function mp_core_fix_quotes( $string ){
	
	return str_replace( '“', '"', str_replace( '”', '"', str_replace("‘", "'", $string ) ) );
		
}

/**
 * This function takes a string and changes all "&nbsp;" to spaces.
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_fix_nbsp
 * @see      function_name()
 * @param    string $string See link for description.
 * @return   void
 */
function mp_core_fix_nbsp( $string ){
	
	return str_replace( '&nbsp;', ' ', $string );
		
}

/**
 * Get a post meta or, if it's never been saved, return false. This function is based on the "get_metadata" function in WP core.
 *
 * @param int $object_id ID of the object metadata is for
 * @param string $meta_key Metadata key. 
 * @return string If never been saved return: 'never_been_saved_73698363746983746' otherwise return the value saved for this meta. 
 */
function mp_core_get_post_meta_or_never_been_saved( $object_id, $meta_key ){
	if ( ! is_numeric( $object_id ) ) {
		return false;
	}

	$object_id = absint( $object_id );
	if ( ! $object_id ) {
		return false;
	}

	/**
	 * Filter whether to retrieve metadata of a specific type.
	 *
	 * The dynamic portion of the hook, $meta_type, refers to the meta
	 * object type (comment, post, or user). Returning a non-null value
	 * will effectively short-circuit the function.
	 *
	 * @since 3.1.0
	 *
	 * @param null|array|string $value     The value get_metadata() should
	 *                                     return - a single metadata value,
	 *                                     or an array of values.
	 * @param int               $object_id Object ID.
	 * @param string            $meta_key  Meta key.
	 * @param string|array      $single    Meta value, or an array of values.
	 */
	$check = apply_filters( "get_post_metadata", null, $object_id, $meta_key, true );
	if ( null !== $check ) {
		if ( $single && is_array( $check ) )
			return $check[0];
		else
			return $check;
	}

	$meta_cache = wp_cache_get($object_id, 'post' . '_meta');

	if ( !$meta_cache ) {
		$meta_cache = update_meta_cache( 'post', array( $object_id ) );
		$meta_cache = $meta_cache[$object_id];
	}

	if ( !$meta_key )
		return $meta_cache;

	if ( isset($meta_cache[$meta_key]) ) {
		return maybe_unserialize( $meta_cache[$meta_key][0] );
	}
	
	//Return string if this field has never been saved before (with the number added just to make it extremely unlikely that a field would save this exact value for another purpose. Don't hate the playa, hate the game).
	return 'never_been_saved_73698363746983746';
	
}

/**
 * Get a post meta value and return a default if empty
 *
 * @since    1.0.0
 * @link     https://mintplugins.com/doc/mp_core_get_post_meta
 * @param    int $post_id The id of the post this meta value is attached to.
 * @param    string $meta_key The key for the value we want to get.
 * @param    mixed $default The default value for this if nothing is saved.
 * @return   mixed $meta_value Either the meta value saved or the default passed-in.
 */
function mp_core_get_post_meta( $post_id, $meta_key, $default = NULL, $args = array() ){
	
	$default_args = array(
		'before' => NULL,
		'after' => NULL,
	);
	
	$args = wp_parse_args( $args, $default_args );
			
	//Get the post meta field we are looking for
	$meta_value = get_post_meta($post_id, $meta_key, true);
	
	//If the meta_value is empty 
	if ( empty( $meta_value ) ){
		
		//If this meta value is set to be the number 0
		if ( is_numeric( $meta_value ) ){
			
			//If there is a before value
			if ( !empty( $args['before'] ) ){
				$meta_value = $args['before'] . $meta_value;
			}
			
			//If there is an after value
			if ( !empty( $args['after'] ) ){
				$meta_value = $meta_value . $args['after'];
			}
			
			return $meta_value;
			
		}
		//If it is truly just empty
		else{
			
			//return the default value
			return $default;
		}
		
	}
	
	//If there is a before value
	if ( !empty( $args['before'] ) ){
		$meta_value = $args['before'] . $meta_value;
	}
	
	//If there is an after value
	if ( !empty( $args['after'] ) ){
		$meta_value = $meta_value . $args['after'];
	}
			
	return $meta_value;	
	
}

/**
 * Return a line of CSS if a value exists
 *
 * @access   public
 * @since    1.0.0
 * @param    string $css_name - The name of the css value we want
 * @param    string $css_value - The value of the css value we want to use. If this is blank, we return NULL
 * @param    string $css_unit_after - The CSS unit we want to use. For example 'px' or '%'. This an be blank if none is needed
 * @return   mixed String or NULL - If we have css to show, it's a string of css - a single line. If not, we return NULL
 */
function mp_core_css_line( $css_name, $css_value = NULL, $css_unit_after = NULL ) {
	
	//If the css_value is empty 
	if ( empty( $css_value ) ){
		
		//If this meta value is set to be the number 0
		if ( is_numeric( $css_value ) ){
			
			$css_line = $css_name . ': ' . $css_value . $css_unit_after . ';';	
			
			return $css_line;
			
		}
		//If it is truly just empty
		else{
			
			//return nothing so there is no output for this CSS line
			return NULL;
		}
		
	}
	//If there is a css_value
	else{
		
		$css_line = $css_name . ': ' . $css_value . $css_unit_after . ';';	
				
		return $css_line;
	}
	
}

/**
 * Convert a hex color code to an RGB array
 *
 * @since    1.0.0
 * @link     http://codex.wordpress.org/Function_Reference/mp_core_hex2rgb
 * @param    string $hex a colour hex
 * @return   array $rgb Format is [0]R [1]G [2]B in that order: Array ( [0] => 204 [1] => 204 [2] => 204 )
 */
function mp_core_hex2rgb( $hex ) {
	
	if (!empty($hex)){
	   $hex = str_replace("#", "", $hex);
	
	   if(strlen($hex) == 3) {
		  $r = hexdec(substr($hex,0,1).substr($hex,0,1));
		  $g = hexdec(substr($hex,1,1).substr($hex,1,1));
		  $b = hexdec(substr($hex,2,1).substr($hex,2,1));
	   } else {
		  $r = hexdec(substr($hex,0,2));
		  $g = hexdec(substr($hex,2,2));
		  $b = hexdec(substr($hex,4,2));
	   }
	   $rgb = array($r, $g, $b);
	   //return implode(",", $rgb); // returns the rgb values separated by commas
	   return $rgb; // returns an array with the rgb values
	}
	else{
		return NULL;
	}
}
				
/**
 * Ajax to display help content
 *
 * @since    1.0.0
 * @link     http://codex.wordpress.org/Function_Reference/mp_core_hex2rgb
 * @param    string $hex a colour hex
 * @return   array $rgb Format is [0]R [1]G [2]B in that order: Array ( [0] => 204 [1] => 204 [2] => 204 )
 */
function mp_core_show_help_content_ajax(){
	
	//Get Help href
	$help_url = $_POST['help_href'];
	
	//Get Help Type
	$help_type = $_POST['help_type'];
	
	if ( $help_type == 'oembed' ){
		echo mp_core_oembed_get($help_url);
	}
	else{
		echo '<iframe src="' . $help_url . '" width="100%" height="400px"/>';	
	}
	
	exit;
}
add_action( 'wp_ajax_mp_core_help_content_ajax', 'mp_core_show_help_content_ajax' );

/**
 * Replace all whitespace and dashes to underscores in a string
 *
 * @since    1.0.0
 * @link     http://mintplugins.com/doc/mp_core_sanitize_title_with_underscores
 * @see      sanitize_title
 * @param    string $string The String to sanitize
 * @return   string $string The sanitized string
 */
function mp_core_sanitize_title_with_underscores( $string ){
	
	//Replace all whitespace and dashes to underscores
	return str_replace("-", "_", sanitize_title( $string ) );
}

/**
 * Red Light if false, Green light if true
 *
 * @access   public
 * @since    1.0.0
 * @see      wp_parse_args()
 * @see      esc_attr()
 * @param    array $args
 * @return   void
 */
function mp_core_true_false_light($args = array() ) {
	$defaults = array(
		'name'        => '',
		'value'       => '',
		'description' => '',
	);
	
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$class = $value == true ? 'mp-core-green-light' : 'mp-core-red-light';
	
	?>
	<div class="mp-core-true-false-light">
		<div class="<?php echo $class; ?>"></div>
		<?php echo $description; ?>
	</div>
	<?php
}

/**
 * Zip a directory and all of its files and subdirectories
 */
function mp_core_zip_directory($source, $destination)
{
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }
	
	$dirname = explode('/', $destination);
	$dirname = explode('.', end($dirname));
	$dirname = $dirname[0];

    $source = str_replace('\\', '/', realpath($source));

    if (is_dir($source) === true)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file)
        {
            $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                continue;

            $file = realpath($file);

            if (is_dir($file) === true)
            {
                $zip->addEmptyDir(str_replace($source . '/', $dirname . '/', $file . '/'));
            }
            else if (is_file($file) === true)
            {
                $zip->addFromString(str_replace($source . '/', $dirname . '/', $file), file_get_contents($file));
            }
        }
    }
    else if (is_file($source) === true)
    {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    return $zip->close();
}

/**
 * Delete a directory and all of its files and subdirectories
 */
function mp_core_remove_directory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!mp_core_remove_directory($dir.DIRECTORY_SEPARATOR.$item)) return false;
    }
    return rmdir($dir);
}

/**
 * Count and return the number of words in a string
 */
function mp_core_word_count($html) {

  # strip all html tags
  $wc = strip_tags($html);

  # remove 'words' that don't consist of alphanumerical characters or punctuation
  $pattern = "#[^(\w|\d|\'|\"|\.|\!|\?|;|,|\\|\/|\-|:|\&|@)]+#";
  $wc = trim(preg_replace($pattern, " ", $wc));

  # remove one-letter 'words' that consist only of punctuation
  $wc = trim(preg_replace("#\s*[(\'|\"|\.|\!|\?|;|,|\\|\/|\-|:|\&|@)]\s*#", " ", $wc));

  # remove superfluous whitespace
  $wc = preg_replace("/\s\s+/", " ", $wc);

  # split string into an array of words
  $wc = explode(" ", $wc);

  # remove empty elements
  $wc = array_filter($wc);

  # return the number of words
  return count($wc);

}

/**
 * Limit number of words in a string
 *
 * @access   public
 * @since    1.0.0
 * @param    $text  string The string whose words we want to limit
 * @param    $limit int The number of words to limit the text to
 * @return   $text  string The limited string
 */
function mp_core_limit_text_to_words($text, $limit) {	
  if (mp_core_word_count($text) > $limit) {
	  $words = str_word_count($text, 2);
	  $pos = array_keys($words);
	  $text = isset( $pos[$limit] ) ? substr($text, 0, $pos[$limit]) : $text;
  }
  return $text;
}

/**
 * Check if the browser is an iPhone
 *
 * @access   public
 * @since    1.0.0
 * @param    $user_agent string The User agent of the browser
 * @return   $boolean boolean True if it is an iPhone, False if not.
 */
function mp_core_is_iphone($user_agent=NULL) {
    if(!isset($user_agent)) {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }
    return (strpos($user_agent, 'iPhone') !== FALSE);
}

/**
 * Check if the browser is an iPad
 *
 * @access   public
 * @since    1.0.0
 * @param    $user_agent string The User agent of the browser
 * @return   $boolean boolean True if it is an iPad, False if not.
 */
function mp_core_is_ipad($user_agent=NULL) {
    if(!isset($user_agent)) {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }
    return (strpos($user_agent, 'iPad') !== FALSE);
}

/**
 * Check if the browser is an android
 *
 * @access   public
 * @since    1.0.0
 * @param    $user_agent string The User agent of the browser
 * @return   $boolean boolean True if it is an android, False if not.
 */
function mp_core_is_android($user_agent=NULL) {
    if(!isset($user_agent)) {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }
    return (strpos($user_agent, 'android') !== FALSE);
}

/**
 * This function will split an array of meta field formatted for MP_CORE_Metabox at a specific key, add new fields, and return it as a singe array. 
 *
 * @access   public
 * @since    1.0.0
 * @param    $items_array array The array into which you wish to insert new fields
 * @param    $new_values array An array with field arrays for MP_Core_Metabox
 * @param    $split_key string The array key after which we will insert the new fieldss in $new_values
 * @return   $return_items_array array The arrya of fields with the new ones inserted.
 */
function mp_core_insert_meta_fields( $items_array, $new_fields, $split_key ){
	
	$counter = 0;
	
	//Loop through passed-in metabox fields
	foreach ( $items_array as $field_key => $field_array ){
		
		//If the current loop is for the brick_bg_image
		if ($field_key == $split_key){
			
			//Split the array after the array with the field containing 'brick_bg_image'
			$options_prior = array_slice($items_array, 0, $counter+1, true);
			$options_after = array_slice($items_array, $counter+1);
			
			break;
						
		}
		
		//Increment Counter
		$counter = $counter + 1;
	
	}
	
	if ( !empty($options_prior) ){
		
		//Add the first options to the return array
		$return_items_array = $options_prior;
		
		//Loop through each passed-in field
		foreach ( $new_fields as $new_field ){
			
			//Add new field to array
			array_push($return_items_array, $new_field);
			
		}
		
		//Re-add fields that came after our split point
		foreach ($options_after as $option){
			//Add all fields that came after
			array_push($return_items_array, $option);
		}
		
	}
	
	return $return_items_array;

}

/**
 * Return the source URL of an image in HTML. The second parameter is the image number we want.
 *
 * @access   public
 * @since    1.0.0
 * @param    $html_string string A string containing HTML and an img attribute
 * @param    $img_number Int The image number we want to get (1st, 2nd image etc)
 * @return   $img_src string The source URL of the first img tag in the html string
 */
function mp_core_get_img_src_from_html( $html_string, $img_number ){
	$doc = new DOMDocument();
	@$doc->loadHTML( $html_string );
	
	$tags = $doc->getElementsByTagName('img');
	
	$image_counter = 1;
	
	foreach ($tags as $tag) {
		if ( $image_counter == $img_number ){
			return $tag->getAttribute('src');
		}
		$image_counter = $image_counter + 1;	   
	}
}

/**
 * Sort an Array using a specific key.
 *
 * @access   public
 * @since    1.0.0
 * @param    $array array The array we want to sort.
 * @param    $key String The key we wish to sort by (eg create a 'date' key in sub-arrays and pass the word 'date' to sort by that number.
 * @return   $order SORT_DESC or SORT_ASC 
 */
function mp_core_array_sort_by_key( $array, $key, $order=SORT_ASC )
{
    $new_array = array();
    $sortable_array = array();
	$array = mp_core_object_to_array( $array );

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $key) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

/**
 * Convert an stdObject to a multidimentional array
 *
 * @access   public
 * @since    1.0.0
 * @param    $object object The object we want to convert into a multidimentional array.
 * @return   $object array The converted array.
 */
function mp_core_object_to_array( $object ) {
	if (is_object($object)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$object = get_object_vars($object);
	}

	if (is_array($object)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $object);
	}
	else {
		// Return array
		return $object;
	}
}

/**
 * Convert an stdObject to a multidimentional array
 *
 * @access   public
 * @since    1.0.0
 * @param    $array array The multidimentional array we want to convert into an object.
 * @return   $array object The converted object.
 */
function mp_core_array_to_object( $array ) {
	if (is_array($array)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return (object) array_map(__FUNCTION__, $array);
	}
	else {
		// Return object
		return $array;
	}
}
 
 

/**
 * Wrap a url in it's appropriate HTML5 tag for displaying
 *
 * @access   public
 * @since    1.0.0
 * @param    $array array The array we want to sort.
 * @param    $key String The key we wish to sort by (eg create a 'date' key in sub-arrays and pass the word 'date' to sort by that number.
 * @return   $order SORT_DESC or SORT_ASC 
 */
function mp_core_wrap_media_url_in_html_tag( $url, $args = array() ){
   	
	$defaults = array(
		'autoplay_videos' => false
	);
	
	$args = wp_parse_args( $args, $defaults );
	$autoplay = $args['autoplay_videos'] ? 'autoplay' : NULL;
	
	$info     = pathinfo($url);
	$basename = isset( $info['basename'] ) ? $info['basename'] : NULL;
	$ext      = isset( $info['extension'] ) ? $info['extension'] : NULL;
	
	if ( $ext == 'jpg' || $ext == 'png' ){
		$return_html = '<img src="' . $url . '" style="max-width:100%;" />';	
	}
	else if ( $ext == 'mp4' ){
		$return_html = '<video controls ' . $autoplay . ' name="media" style="max-width:100%;">
			<source src="' . $url . '" type="video/mp4">
		</video>';	
	}
	else{
		
		return mp_core_oembed_get( $url );
	}
	
	return $return_html;
}

/**
 * Get the time ago string for a date
 *
 * @access   public
 * @since    1.0.0
 * @param    $date string A date or timestamp
 * @return   $time_ago string The time ago that this date is
 */
function mp_core_time_ago( $date ){
	
    if( empty( $date ) )
    {
        return "No date provided";
    }

    $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");

    $lengths = array("60","60","24","7","4.35","12","10");

    $now = time();
	
	//If the user passed a timestamp - don't try and convert it - just use it ya dingus!
	if ( is_numeric( $date ) && (int)$date == $date ){
		$unix_date = $date;
	}
	//If the user passed a date string - convert it to a timestamp.
	else{
    	$unix_date = strtotime( $date );
	}

    // check validity of date

    if( empty( $unix_date ) )
    {
        return "Bad date";
    }

    // is it future date or past date

    if( $now > $unix_date )
    {
        $difference = $now - $unix_date;
        $tense = "ago";
    }
    else
    {
        $difference = $unix_date - $now;
        $tense = "from now";
    }

    for( $j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++ )
    {
        $difference /= $lengths[$j];
    }

    $difference = round( $difference );

    if( $difference != 1 )
    {
        $periods[$j].= "s";
    }

    return "$difference $periods[$j] {$tense}";

}

/**
 * Get the html meta tags for facebook open graph for a video
 *
 * @access   public
 * @since    1.0.0
 * @param    $date video_url A url to a video
 * @return   $meta_tagts string the meta tags which should be placed in the header to make Open Graph display a video correctly.
 */
function mp_core_open_graph_video_meta_tags( $video_url ){
	//Find the youtube video id by checking all the types of urls we could be given
	if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_url, $match)) {
		$youtube_video_code = $match[1];
		$og_video_url = 'https://youtube.com/v/' . $youtube_video_code;
	}
	//Vimeo format of open graph (og)
	else if( strpos( $video_url, 'vimeo.com' )  !== false ){
		$vimeo_video_code = explode( '://vimeo.com/', $video_url );
		$og_video_url = 'http://vimeo.com/moogaloop.swf?clip_id=' . $vimeo_video_code[1];	
	}
	else{
		$og_video_url = $video_url;	
	}
	
	$content_output = '<meta property="og:video" content="' . $og_video_url . '">
	<meta property="og:type" content="video.other">';
	
	return $content_output;
}