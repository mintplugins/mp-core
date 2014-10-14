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
 * @link     http://moveplugins.com/doc/mp_core_fix_quotes
 * @see      function_name()
 * @param    string $string See link for description.
 * @return   void
 */
function mp_core_fix_quotes( $string ){
	
	return str_replace( '“', '"', str_replace( '”', '"', str_replace("‘", "'", $string ) ) );
		
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
	  $text = substr($text, 0, $pos[$limit]);
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
		if ($field_key == 'meta_hook_anchor_2'){
			
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