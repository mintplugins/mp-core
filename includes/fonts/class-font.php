<?php
/**
 * Google Fonts
 */
class MP_CORE_Font{
		
	public function __construct($font_family, $css_font_family = NULL){
		
		//Set font family var
		$this->_font_family = $font_family;
		
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
 	*/
	function mp_core_enqueue_scripts() {
		
		$google_font_face = wp_remote_get( 'http://fonts.googleapis.com/css?family=' . $this->_font_family_slug );
		
		if ( !strpos( $google_font_face['body'], 'Error' )){
			$google_font_face = preg_replace( '/' . $this->_font_family . '/', $this->_css_font_family, $google_font_face['body'], 1 );
			echo '<style> ' . $google_font_face . '</style>';
		}
		
	}
	
}

//Sample:
//new MP_CORE_Font( 'Shojumaru', 'My Font Family 1' );
