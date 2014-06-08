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