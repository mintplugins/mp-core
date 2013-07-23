<?php
/**
 * Plugin Directory Class for the mp_core Plugin by Move Plugins
 * http://moveplugins.com/doc/plugin-directory-class/
 */
if ( !class_exists( 'MP_CORE_Verify_License' ) ){
	class MP_CORE_Verify_License{
		
		public function __construct($args){
			
			//Get args
			$this->_args = $args;
			
			//If the args passed have 'plugin' as the prefix, change that to 'software'
			$this->_args['software_name'] = isset( $this->_args['plugin_name'] ) ? $this->_args['plugin_name'] : $this->_args['software_name'];
			$this->_args['software_api_url'] = isset( $this->_args['plugin_api_url'] ) ? $this->_args['plugin_api_url'] : $this->_args['software_api_url'];
			
			//Software/Theme/Plugin Name Slug
			$this->software_name_slug = sanitize_title ( $this->_args['software_name'] ); //EG move-plugins-core	
			
			//Verify license
			$this->store_and_verify_license();
										
		}
		
		/**
		 * Function which stores and verifies a license
		 */
		public function store_and_verify_license(){
			// listen for our activate button to be clicked
			if( isset( $_POST[ $this->software_name_slug . '_license_key' ] ) ) {
				
				//Check nonce
				if( ! check_admin_referer( $this->software_name_slug . '_nonce', $this->software_name_slug . '_nonce' ) ) 	
					return; // get out if we didn't click the Activate button
				
				// retrieve the license from the $_POST
				$license = trim( $_POST[ $this->software_name_slug . '_license_key' ] );
				
				//Sanitize and update license
				update_option( $this->software_name_slug . '_license_key', wp_kses(htmlentities($license, ENT_QUOTES), '' ) );	 
								
				//If the length of the key matches the length of normal EDD licenses, do an EDD update
				if ( strlen( $license ) == 32 ){
					
					//Set args for EDD Licence check function
					$args = array(
						'software_api_url' => $this->_args['software_api_url'],
						'software_name'    => $this->_args['software_name'],
						'software_license' => $license,
					);
						
					//Check and update EDD Licence. The mp_core_edd_license_check function in in the mp_core
					update_option( $this->software_name_slug . '_license_status_valid', mp_core_edd_license_check($args) );	
				}
				
				//If the length of the key matches the length of normal ENVATO licenses, do an ENVATO update
				elseif(strlen( $license ) == 36){
					
					//Check the response from the repo if this license is valid					
					$envato_response = wp_remote_post( $this->_args['software_api_url']  . '/repo/' . $this->software_name_slug . '/?envato-check&license=' . $license );
								
					//Check and Update Envato Licence
					update_option( $this->software_name_slug . '_license_status_valid', $envato_response );	
					
				}
				
				//This license length doesn't match any we are checking for and therefore, this license is not valid
				else{
					update_option( $this->software_name_slug . '_license_status_valid', false );
				}
					
			}
		}
	}
}

