<?php
/**
 * This file contains the MP_CORE_Font class
 *
 * @link http://mintplugins.com/doc/font-class/
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Classes
 *
 * @copyright  Copyright (c) 2015, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */

/**
 * Font Class for the MP Core Plugin by Mint Plugins.
 * 
 * This class uses 2 strings to set up a font-face and assign a custom CSS Font Family name to any Google Font.
 *
 * @author     Philip Johnston
 * @link       http://mintplugins.com/doc/font-class/
 * @since      1.0.0
 * @return     void
 */
class MP_CORE_Font{
	
	/**
	 * Constructor
	 *
	 * @access   public
	 * @since    1.0.0
	 * @link     http://mintplugins.com/doc/font-class/
	 * @see      MP_CORE_Font::mp_core_get_google_font_styles()
	 * @param    string $font_family (required) – The Font Family name from Google Fonts. EG: ‘Merriweather Sans’
	 * @param    string $css_font_family (optional) – The Font Family name you will use in your style.css file. EG: ’My Font Family’. If blank, the font family will be the Google Font Family name.
	 * @return   void
	 */	
	public function __construct($font_family, $css_font_family = NULL, $args = array() ){
		
		//Additional arg/settings for this Font Class
		$defaults_args = array(
			'echo_google_font_css' => true,
			'wrap_in_style_tags' => true,
		);
		
		$args = wp_parse_args( $args, $defaults_args );
		
		//If a subset has been passed (Lobster&subset=latin-ext,greek)
		if ( strpos( $font_family, '&subset' ) !== false ){
			parse_str($font_family, $output);
			$this->_subset = '&subset=' . $output['subset'];
		}
		else{
			$this->_subset = NULL;	
		}
		
		//Break the font into it's parts
		$font_explode = explode( ':', $font_family );
		
		//Set font family var
		$this->_args = $args;	
		
		//Set font family var
		$this->_font_family = explode( '&', $font_explode[0] );	//Removes any additional things from the font family name
		$this->_font_family = $this->_font_family[0];
		
		//Set font extras. EG 400italic,400,700,800
		$this->_font_family_extras = isset( $font_explode[1] ) ? $font_explode[1] : NULL;
								
		//Set CSS font family var. If blank, set it to the above var
		$this->_css_font_family = !isset( $css_font_family) ? $font_family : $css_font_family;
		
		//Font Name - Merriweather Sans -> merriweather_sans
		$this->_font_slug = sanitize_title ( $this->_font_family );
		
		//Font Family - Merriweather Sans -> Merriweather+Sans
		$this->_font_family_slug = str_replace( " ", "+", $this->_font_family );
		
		//If this class is created after wp_enqueue_scripts has already run
		if ( did_action('wp_enqueue_scripts') === 1 && $this->_args['echo_google_font_css'] ){
			
			//Then run it in the footer
			add_action( 'wp_footer', array( $this, 'mp_core_output_google_font_styles' ) );
		}
		//If this class is created before wp_enqueue_scripts has been run
		elseif( $this->_args['echo_google_font_css'] ){
			
			//Run it in wp_enqueue_scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'mp_core_output_google_font_styles' ) );
		}
		else{
			
			$this->mp_core_get_google_font_styles();
				
		}
		
		add_action( 'mp_core_tinymce_css', array( $this, 'mp_core_output_google_font_styles' ) );
			
	}
	
	/**
 	* Get the Google Font CSS Output and return it
	*
	* @access   public
	* @since    1.0.0
	* @see      wp_remote_get()
	* @return   string - The Google Font CSS Output.
 	*/
	public function mp_core_get_google_font_styles() {
		
		global $mp_core_font_families;
					
		if ( !isset( $mp_core_font_families[$this->_css_font_family] ) ){
			
			//If there are font extras (thin, normal, bold etc)
			if ( !empty( $this->_font_family_extras ) ){
				$fetch_string = $this->_font_family_slug . ':' . $this->_font_family_extras;
			}
			else{
				$fetch_string = $this->_font_family_slug;
			}	
						
			//Fetch the font from Google's server
			$google_font_face = wp_remote_get( 'https://fonts.googleapis.com/css?family=' . $fetch_string . $this->_subset );
			
			//If the result was a WP error or some kind, return that error.
			if ( is_wp_error( $google_font_face ) ){
				
				//Add this font family to the array of font families so we dont re-create it again
				$mp_core_font_families[$this->_css_font_family] = $google_font_face->get_error_message();
				
				return NULL;
				
			}
			
			//If the entered font is not found on Google (ie it was spelled wrong or a style doesn't exist for it)
			if ( strpos( $google_font_face['body'], 'The requested font families are not available' ) !== false ){
				
				//Try getting the font without the extras (which might not exist)
				$google_font_face = wp_remote_get( 'https://fonts.googleapis.com/css?family=' . $this->_font_family_slug . $this->_subset );
				
				//If the font is still not found on Google Fonts
				if ( strpos( $google_font_face['body'], 'The requested font families are not available' ) !== false || strpos( $google_font_face['body'], 'error' ) !== false ){
					
					//Add this font family to the array of font families so we dont re-create it again
					$mp_core_font_families[$this->_css_font_family] = 'The requested font families are not available';
			
					return NULL;	
				
				}
			}
			
			if ( !is_wp_error( $google_font_face ) ){
				$google_font_face = str_replace("font-family: '" . $this->_font_family . "';", "font-family: '" . $this->_css_font_family . "';", $google_font_face['body'] );
				
				if ( !$this->_args['wrap_in_style_tags'] || current_filter() == 'mp_core_tinymce_css'){
					
					//Add this font family to the array of font families so we dont re-create it again
					$mp_core_font_families[$this->_css_font_family] = $google_font_face;
					return $google_font_face;
				}
				else{
					
					//Add this font family to the array of font families so we dont re-create it again
					$mp_core_font_families[$this->_css_font_family] = '<style> ' . $google_font_face . '</style>';
					
					return '<style> ' . $google_font_face . '</style>';
				}
			}
		}
		
	}
	
	/**
 	* Output the Google Font CSS Output 
	*
	* @access   public
	* @since    1.0.0
	* @see      wp_remote_get()
	* @return   void
 	*/
	public function mp_core_output_google_font_styles() {
		
		if ( $this->_args['echo_google_font_css'] ){
			echo $this->mp_core_get_google_font_styles();
		}
		
	}
	
}

//Sample:
//new MP_CORE_Font( 'Shojumaru', 'My Font Family 1' );
