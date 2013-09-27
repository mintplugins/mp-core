<?php
/**
 * This file contains the MP_CORE_Theme_Updater class which tehemes can use to keep themselves up to date
 *
 * @link http://moveplugins.com/doc/theme-updater-class/
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
 * Plugin Updater Class which plugins can use to keep themselves up to date. 
 * This class will work in conjunction with the MP Repo plugin as a custom repo.
 *
 * @author     Philip Johnston
 * @link       http://moveplugins.com/doc/theme-updater-class/
 * @since      1.0.0
 * @return     void
 */	
if ( !class_exists( 'MP_CORE_Theme_Updater' ) ){
	class MP_CORE_Theme_Updater{
		
		/**
		 * Constructor
		 *
		 * @access   public
		 * @since    1.0.0
		 * @link     http://moveplugins.com/doc/plugin-updater-class/
		 * @author   Philip Johnston
		 * @see      MP_CORE_Plugin_Updater::set_license_green_light()
		 * @see      MP_CORE_Plugin_Updater::themes_page()
		 * @see      MP_CORE_Plugin_Updater::mp_core_update_theme()
		 * @see      wp_parse_args()
		 * @see      sanitize_title()
		 * @param    array $args (required) See link for description.
		 * @return   void
		 */	
		public function __construct($args){
							
			//Set defaults for args		
			$args_defaults = array(
				'software_name' => NULL,
				'software_api_url' => NULL,
				'software_licensed' => NULL
			);
			
			//Get and parse args
			$this->_args = wp_parse_args( $args, $args_defaults );
					
			//Theme Name Slug
			$this->theme_name_slug = sanitize_title ( $this->_args['software_name'] ); //EG move-plugins-core
			
			//If this software is licensed, show license field on plugins page
			if ( $this->_args['software_licensed'] ){
				
				//Set the "Green Light" Notification option for this license		
				add_action( 'admin_init', array( &$this, 'set_license_green_light' ) );
				
				//Show Option Page on Themes page as well
				add_action( 'load-themes.php', array( $this, 'themes_page') );  
			
			}
			
			//Theme Update Function	
			add_action( 'admin_init', array( &$this, 'mp_core_update_theme' ) ); 	
						
						
		}
					
		/***********************************************
		* This is our updater
		***********************************************/
		function mp_core_update_theme(){
			
			
			//If this software is licensed, do checks for updates using the license
			if ( $this->_args['software_licensed'] ){
				
				//Get theme info
				$theme = wp_get_theme($this->theme_name_slug); // $theme->Name
												
				//Get current theme version
				$theme_current_version = $theme->Version;
								
				//Get license		
				$license_key = trim( get_option( $this->theme_name_slug . '_license_key' ) );
															
			}
			//This isn't a licensed theme
			else{
					
				$license_key = NULL;
				
			}
			
			//Do Update Check
			if ( !class_exists( 'MP_CORE_MP_REPO_Theme_Updater' ) ) {
				// Load our custom theme updater
				include( dirname( __FILE__ ) . '/mp-repo/class-mp-repo-theme-updater.php' );
			}
										
			//Call the MP_CORE_MP_REPO_Plugin_Updater Updater Class
			$updater = new MP_CORE_MP_REPO_Theme_Updater( array( 
					'software_api_url' 	=> $this->_args['software_api_url'], 	// Our store URL that is running EDD
					'software_license' 	=> $license_key,
					'software_name_slug' 	=> $this->theme_name_slug,	// The slug of this theme
				)
			);

		}
		
		/**
		 * Function which sets the green light variable to let the user know their license is active
		 */
		function set_license_green_light(){
			
			$args = array(
				'software_name'      => $this->_args['software_name'],
				'software_api_url'   => $this->_args['software_api_url']
			);
				
			new MP_CORE_Verify_License( $args );		
			
		}
		
		/**
		 * This function is called on the themes page only
		 */
		function themes_page() {
			
			//Enqueue scripts for theme page
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_themes_scripts' ) );
			
			//Display license on themes page
			add_action( 'admin_notices', array( &$this, 'display_license' ) ); 
		}
		
		/**
		 * Enqueue Jquery on Theme page to place license in correct spot
		 */
		function enqueue_themes_scripts() {
			//Enqueue style for this license message
			wp_enqueue_style( 'mp-core-licensing-css', plugins_url( 'css/core/mp-core-licensing.css', dirname(dirname(__FILE__) ) ) );			
			wp_enqueue_script( 'mp-core-themes-placement', plugins_url( 'js/themes-page.js', dirname(__FILE__) ),  array( 'jquery' ) );		
		}	
		
		/**
		 * Display the license on the themes page
		 */
		function display_license(){
			$license_key 	= get_option( $this->theme_name_slug . '_license_key' );
			$status 	= get_option( $this->theme_name_slug . '_license_status_valid' );
			?>
			<div id="mp-core-theme-license-wrap" class="wrap">
				
				<strong><?php echo __('Updates', 'mp_core'); ?></strong><br />
				<p class="theme-description"><?php echo __('Enter your license key to enable automatic updates'); ?></p>
				
				<form method="post">
									
					<input style="float:left; margin-right:10px;" id="<?php echo $this->theme_name_slug; ?>_license_key" name="<?php echo $this->theme_name_slug; ?>_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license_key ); ?>" />						
					<?php mp_core_true_false_light( array( 'value' => $status, 'description' => $status == true ? __('License is valid', 'mp_core') : __('This license is not valid!', 'mp_core') ) ); ?>
					
					<?php wp_nonce_field( $this->theme_name_slug . '_nonce', $this->theme_name_slug . '_nonce' ); ?>
								
					<?php submit_button(__('Submit License', 'mp_core') ); ?>
				
				</form>
			</div>
			<?php
		}
	}
}