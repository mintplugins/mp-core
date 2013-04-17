<?php
/**
 * Plugin Checker Class for the wp_core Plugin by Move Plugins
 * http://moveplugins.com/plugin-checker-class/
 */
if ( !class_exists( 'MP_CORE_Plugin_Updater' ) ){
	class MP_CORE_Plugin_Updater{
		
		public function __construct($args){
			
			//Get args
			$this->_args = $args;
				
			
			//Theme Update Function		
			add_action('admin_init', array( $this, 'mp_core_update_plugin' ) );	
			
			//Activate Licence
			add_action('admin_init', array( $this, 'mp_core_edd_activate_license' ) );		
			
		}
			
		/***********************************************
		* This is our updater
		***********************************************/
		function mp_core_update_plugin(){
			
			if ( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
				// Load our custom theme updater
				include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
			}
			
			//Get licence		
			$license = trim( mp_core_get_option( $this->_args['software_licence_option_tab'], 'edd_licence_key' ) );
			
			//Get theme info
			$theme = wp_get_theme($this->_args['software_slug']); // $theme->Name
							
			//Get current theme version
			$theme_current_version = $theme->Version;
			
			//If there is a licence entered, call the EDD_Theme Updater Class
			if ( !empty( $license ) ) {
				$edd_updater = new EDD_SL_Theme_Updater( array( 
						'remote_api_url' 	=> $this->_args['software_api_url'], 	// Our store URL that is running EDD
						'version' 			=> $theme_current_version, 				// The current theme version we are running
						'license' 			=> $license, 		// The license key (used get_option above to retrieve from DB)
						'item_name' 		=> $this->_args['software_name'],	// The name of this theme
						'author'			=> $this->_args['software_author']	// The author's name
					)
				);
			}
		
			
		}
		
		function mp_core_edd_activate_license() {
 
			// listen for our activate button to be clicked
			if( isset( $_POST[ $this->_args['software_licence_option_tab'] ] ) ) {
				 				 
				// retrieve the license from the $_POST
				$license = trim( $_POST[ $this->_args['software_licence_option_tab'] ][ 'edd_licence_key' ] );	 
		 
				// data to send in our API request
				$api_params = array( 
					'edd_action'=> 'activate_license', 
					'license' 	=> $license, 
					'item_name' => urlencode( $this->_args['software_name'] ) // the name of our product in EDD
				);
		 
				// Call the custom API.
				$response = wp_remote_get( add_query_arg( $api_params, $this->_args['software_api_url'] ) );
		 		
				// make sure the response came back okay
				if ( is_wp_error( $response ) )
					return false;
		 
				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		 
				// $license_data->license will be either "active" or "inactive"

				update_option( $this->_args['software_slug'] . '_license_status', $license_data->license );
				
				/***********************************************
				* Check if the license is valid
				***********************************************/
					
				$api_params = array( 
					'edd_action' => 'check_license', 
					'license' => $license, 
					'item_name' => urlencode( $this->_args['software_name'] ) 
				);
				
				$response = wp_remote_get( add_query_arg( $api_params, $this->_args['software_api_url'] ), array( 'timeout' => 15, 'sslverify' => false ) );
			
				if ( is_wp_error( $response ) )
					return false;
			
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			
				if( $license_data->license == 'valid' ) {
					// this license is still valid
					update_option( $this->_args['software_slug'] . '_license_status_valid', true );
				} else {
					// this license is no longer valid
					update_option( $this->_args['software_slug'] . '_license_status_valid', false );
				}
			}
		}		
	}
}