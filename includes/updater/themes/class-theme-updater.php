<?php
/**
 * Plugin Checker Class for the wp_core Plugin by Move Plugins
 * http://moveplugins.com/plugin-checker-class/
 */
if ( !class_exists( 'MP_CORE_Theme_Updater' ) ){
	class MP_CORE_Theme_Updater{
		
		public function __construct($args){
				
			//Get args
			$this->_args = $args;
			
			//Set the "Green Light" Notification option for this license		
			$this->set_license_green_light();
			
			//Theme Update Function		
			$this->mp_core_update_theme();
		}
					
		/***********************************************
		* This is our updater
		***********************************************/
		function mp_core_update_theme(){
			
			//Get license		
			$license = isset( $_POST[ $this->_args['software_license_setting'] ] ) ? 
			//Get from $_POST if the license exists there
			trim( $_POST[ $this->_args['software_license_setting'] ][ 'license_key' ] ) : 
			//Otherwise, get from Database saved setting
			trim( mp_core_get_option( $this->_args['software_license_setting'], 'license_key' ) );
						
			//EDD Length: If the length of the key matches the length of normal EDD licenses, do an EDD update
			if ( strlen( $license ) == 32 ){
				
				//Do EDD Update
				if ( !class_exists( 'EDD_SL_Theme_Updater' ) ) {
					// Load our custom theme updater
					include( dirname( __FILE__ ) . '/EDD_SL_Theme_Updater.php' );
				}
								
				//Get theme info
				$theme = wp_get_theme($this->_args['software_slug']); // $theme->Name
												
				//Get current theme version
				$theme_current_version = $theme->Version;
				
				//Call the EDD_Theme Updater Class
				$edd_updater = new EDD_SL_Theme_Updater( array( 
						'remote_api_url' 	=> $this->_args['software_api_url'], 	// Our store URL that is running EDD
						'version' 			=> $theme_current_version, 				// The current theme version we are running
						'license' 			=> $license, 		// The license key (used get_option above to retrieve from DB)
						'item_name' 		=> $this->_args['software_name'],	// The name of this theme
						'author'			=> $this->_args['software_author']	// The author's name
					)
				);
				
			}
			//Envato Length: If the length of the key matches the length of normal envato licenses, do an envato update
			elseif ( strlen( $license ) == 36){
				
				//Do EDD Update
				if ( !class_exists( 'MP_CORE_Envato_Theme_Updater' ) ) {
					// Load our custom theme updater
					include( dirname( __FILE__ ) . '/class-envato-theme-updater.php' );
				}
												
				//If there is a license entered, call the EDD_Theme Updater Class
				if ( !empty( $license ) ) {
					$edd_updater = new MP_CORE_Envato_Theme_Updater( array( 
							'software_api_url' 	=> $this->_args['software_api_url'], 	// Our store URL that is running EDD
							'software_license' 	=> $license, // The license key (used get_option above to retrieve from DB)
							'software_slug' 	=> $this->_args['software_slug'],	// The slug of this theme
							'software_page_url' => $this->_args['software_page_url'] // The url of the page the user will see when they click "View Details"
						)
					);
				}
			}
		}
		
		/**
		 * Function which sets the green light variable to let the user know their license is active
		 */
		function set_license_green_light(){
			// listen for our activate button to be clicked
			if( isset( $_POST[ $this->_args['software_license_setting'] ] ) ) {
				
				// retrieve the license from the $_POST
				$licence = trim( $_POST[ $this->_args['software_license_setting'] ][ 'license_key' ] );	 
								
				//If the length of the key matches the length of normal EDD licenses, do an EDD update
				if ( strlen( $this->_args['post_license'] ) == 32 ){
					
					//Set args for EDD Licence check function
					$args = array(
						'software_api_url' => $this->_args['software_api_url'],
						'software_slug'    => $this->_args['software_slug'],
						'software_name'    => $this->_args['software_name'],
						'software_license' => $licence,
					);
						
					//Check and update EDD Licence
					update_option( $this->_args['software_slug'] . '_license_status_valid', mp_core_edd_license_check($args) );	
				}
				
				//If the length of the key matches the length of normal ENVATO licenses, do an ENVATO update
				elseif(strlen( $this->_args['post_license'] ) == 36){
					
					//Set args for ENVATO Licence check function
					$args = array(
						'software_envato_username' => $this->_args['software_envato_username'],
						'software_envato_api_key' => $this->_args['software_envato_api_key'],
						'software_license' => $licence,
					);
								
					//Check and Update Envato Licence
					update_option( $this->_args['software_slug'] . '_license_status_valid', mp_repo_envato_license_check($args) );	
				}
				
				//This license length doesn't match any we are checking for and therefore, this license is not valid
				else{
					update_option( $this->_args['software_slug'] . '_license_status_valid', false );
				}
					
			}
		}
	}
}