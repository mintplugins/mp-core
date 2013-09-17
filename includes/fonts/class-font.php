<?php
/**
 * This file contains the MP_CORE_Font class
 *
 * @link http://moveplugins.com/doc/font-class/
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Classes
 *
 * @copyright  Copyright (c) 2013, Move Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */


/**
 * Font Class for the MP Core Plugin by Move Plugins.
 * 
 * This class uses 2 strings to set up a font-face and assign a custom CSS Font Family name to any Google Font.
 *
 * @author     Philip Johnston
 * @link       http://moveplugins.com/doc/font-class/
 * @since      1.0.0
 * @return     void
 */
class MP_CORE_Font{
	
	/**
	 * Constructor
	 *
	 * @access   public
	 * @since    1.0.0
	 * @link     http://moveplugins.com/doc/font-class/
	 * @see      MP_CORE_Font::mp_core_enqueue_scripts()
	 * @param    string $font_family (required) – The Font Family name from Google Fonts. EG: ‘Merriweather Sans’
	 * @param    string $css_font_family (optional) – The Font Family name you will use in your style.css file. EG: ’My Font Family’. If blank, the font family will be the Google Font Family name.
	 * @return   void
	 */	
	public function __construct($font_family, $css_font_family = NULL){
		
		//Break the font into it's parts
		$font_explode = explode( ':', $font_family );
		
		//Set font family var
		$this->_font_family = $font_explode[0];	
		
		//Set font extras. EG 400italic,400,700,800
		$this->_font_family_extras = isset( $font_explode[1] ) ? $font_explode[1] : NULL;
								
		//Set CSS font family var. If blank, set it to the above var
		$this->_css_font_family = !isset( $css_font_family) ? $font_family : $css_font_family;
		
		//Font Name - Merriweather Sans -> merriweather_sans
		$this->_font_slug = sanitize_title ( $this->_font_family );
		
		//Font Family - Merriweather Sans -> Merriweather+Sans
		$this->_font_family_slug = str_replace( " ", "+", $this->_font_family );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'mp_core_enqueue_scripts' ) );
	
	}
	
	/**
 	* Enqueue Scripts and Fonts
	*
	* @access   public
	* @since    1.0.0
	* @see      wp_remote_get()
	* @return   void
 	*/
	function mp_core_enqueue_scripts() {
		
		$google_font_face = wp_remote_get( 'https://fonts.googleapis.com/css?family=' . $this->_font_family_slug . ':' . $this->_font_family_extras );
		
		if ( !strpos( $google_font_face['body'], 'Error' )){
			$google_font_face = str_replace("font-family: '" . $this->_font_family . "';", "font-family: '" . $this->_css_font_family . "';", $google_font_face['body'] );
			echo '<style> ' . $google_font_face . '</style>';
		}
		
	}
	
}

//Sample:
//new MP_CORE_Font( 'Shojumaru', 'My Font Family 1' );
