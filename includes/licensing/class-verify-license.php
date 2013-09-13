<?php
/**
 * Plugin Directory Class for the mp_core Plugin by Move Plugins
 * http://moveplugins.com/doc/plugin-directory-class/
 */
if ( !class_exists( 'MP_CORE_Verify_License' ) ){
	class MP_CORE_Verify_License{
		
		public function __construct($args){
																	
			//Set defaults for args		
			$args_defaults = array(
				'software_name'      => NULL,
				'software_api_url'   => NULL
			);
			
			//Get and parse args
			$this->_args = wp_parse_args( $args, $args_defaults );
			
			//If the args passed have 'plugin' as the prefix, change that to 'software'
			$this->_args['software_name'] = isset( $this->_args['plugin_name'] ) ? $this->_args['plugin_name'] : $this->_args['software_name'];
			$this->_args['software_api_url'] = isset( $this->_args['plugin_api_url'] ) ? $this->_args['plugin_api_url'] : $this->_args['software_api_url'];
			
			//Software/Theme/Plugin Name Slug
			$this->software_name_slug = sanitize_title ( $this->_args['software_name'] ); //EG move-plugins-core	
			
			//Verify license
			$this->store_and_verify_license();
										
		}
		
		/**
		 * Function which stores and verifies a license using the mp_repo plugin on the software_api_url
		 */
		public function store_and_verify_license(){
						
			// listen for our activate button to be clicked
			if( isset( $_POST[ $this->software_name_slug . '_license_key' ] ) ) {
				
				//Check nonce
				if( ! check_admin_referer( $this->software_name_slug . '_nonce', $this->software_name_slug . '_nonce' ) ) 	
					return; // get out if we didn't click the Activate button
				
				//Retrieve the license from the $_POST
				$license_key = trim( $_POST[ $this->software_name_slug . '_license_key' ] );
				
				//Old License Key
				$old_license_key = get_option( $this->software_name_slug . '_license_key' );	 
				
				//Sanitize and update license
				update_option( $this->software_name_slug . '_license_key', wp_kses(htmlentities($license_key, ENT_QUOTES), '' ) );	 
											
				//Check the response from the repo if this license is valid					
				$mp_repo_response = wp_remote_post( $this->_args['software_api_url']  . '/repo/' . $this->software_name_slug . '/?license_check=true&license_key=' . $license_key . '&old_license_key=' . $old_license_key );
															
				//Retreive the body from the response - which should only have a 1 or a 0
				$mp_repo_response_boolean = ( json_decode( wp_remote_retrieve_body( $mp_repo_response ) ) );
				
				//Check and Update Licence
				update_option( $this->software_name_slug . '_license_status_valid', $mp_repo_response_boolean );	
					
			}
		}
	}
}

