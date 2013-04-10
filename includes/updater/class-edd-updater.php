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
				
			//Theme Update Function		
			add_action('admin_init', array( $this, 'edd_update_theme' ) );	
			
			//Activate Licence
			add_action('admin_init', array( $this, 'edd_activate_license' ) );		
			
		}
			
		/***********************************************
		* This is our updater
		***********************************************/
		function edd_update_theme(){
			
			if ( !class_exists( 'EDD_SL_Theme_Updater' ) ) {
				// Load our custom theme updater
				include( dirname( __FILE__ ) . '/EDD_SL_Theme_Updater.php' );
			}
					
			$license = trim( mp_core_get_option( $this->_args['software_licence_option_tab'], 'edd_licence_key' ) );
			
			//Get theme info
			$theme = wp_get_theme($this->_args['software_slug']); // $theme->Name
							
			//Get current theme version
			$theme_current_version = $theme->Version;
			
			$edd_updater = new EDD_SL_Theme_Updater( array( 
					'remote_api_url' 	=> $this->_args['software_api_url'], 	// Our store URL that is running EDD
					'version' 			=> $theme_current_version, 				// The current theme version we are running
					'license' 			=> $license, 		// The license key (used get_option above to retrieve from DB)
					'item_name' 		=> $this->_args['software_name'],	// The name of this theme
					'author'			=> $this->_args['software_author']	// The author's name
				)
			);
		
			
		}
		
		function edd_activate_license() {
 
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
		 
			}
		}		
	}
}