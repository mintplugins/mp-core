<?php
/**
 * Sends for a plugin update from the mp_repo plugin instaled on the API site
 * http://moveplugins.com/MP_CORE_MP_REPO_Plugin_Updater/
 */
if ( !class_exists( 'MP_CORE_MP_REPO_Plugin_Updater' ) ){
	class MP_CORE_MP_REPO_Plugin_Updater{
		
		public function __construct($args){
			
			//Parse args					
			$args = wp_parse_args( $args, array( 
				'software_version' 	=> '',
				'software_file_url' => '',
				'software_api_url' 	=> '',
				'software_license' 	=> NULL,
				'software_name' 	=> '',
			) );
			
			//Get args
			$this->_args = $args;
			
			//Set variables
			$this->api_url  = trailingslashit( $this->_args['software_api_url'] ); //EG: http://moveplugins.com
			$this->name     = plugin_basename( $this->_args['software_file_url'] ); //EG: mp-core/mp-core.php
			$this->slug     = basename( $this->_args['software_file_url'], '.php'); //EG: mp-core
			$this->version  = $this->_args['software_version']; //EG: 1.0
			$this->plugin_name_slug = sanitize_title ( $this->_args['software_name'] ); //EG move-plugins-core
		
			// Set up hooks.
			$this->hook();
											
			//Delete transients for testing purposes if WP_DEBUG is on
			//if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) )
                //$this->delete_transients();
											
		}
		
		/**
		 * Delete transients (runs when WP_DEBUG is on)
		 * For testing purposes the site transient will be reset on each page load
		 *
		 * @since 1.0
		 * @return void
		 */
		public function delete_transients () {
			delete_site_transient( 'update_plugins' );
		}
					
		/**
		 * Set up Wordpress filters to hook into WP's update process.
		 *
		 * @uses add_filter()
		 *
		 * @return void
		 */
		private function hook() {		
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins_filter' ) );
			add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3);
			
		}
		
		/**
		 * Check for Updates at the defined API endpoint and modify the update array.
		 *
		 * This function dives into the update api just when Wordpress creates its update array,
		 * then adds a custom API call and injects the custom plugin data retrieved from the API.
		 * It is reassembled from parts of the native Wordpress plugin update code.
		 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
		 *
		 * @uses api_request()
		 *
		 * @param array $_transient_data Update array build by Wordpress.
		 * @return array Modified update array with custom plugin data.
		 */
		function pre_set_site_transient_update_plugins_filter( $_transient_data ) {
			
			if( empty( $_transient_data ) ) return $_transient_data;
	
			$to_send = array( 'slug' => $this->slug );
	
			$api_response = $this->api_request( 'plugin_latest_version', $to_send );
			
			//Add the license to the package URL if the license passed in is not NULL <--this is now handled by the mp-repo plugin
			//$api_response->package = $this->_args['software_license'] != NULL ? add_query_arg('license_key', $this->_args['software_license'], $api_response->package ) : $api_response->package;
					
			if( false !== $api_response && is_object( $api_response ) ) {
				if( version_compare( $this->version, $api_response->new_version, '<' ) )
					$_transient_data->response[$this->name] = $api_response;
		}
			//print_r ($_transient_data);
			return $_transient_data;
		}
		
		/**
		 * Updates information on the "View version x.x details" page with custom data.
		 *
		 * @uses api_request()
		 *
		 * @param mixed $_data
		 * @param string $_action
		 * @param object $_args
		 * @return object $_data
		 */
		function plugins_api_filter( $_data, $_action = '', $_args = null ) {
			if ( ( $_action != 'plugin_information' ) || !isset( $_args->slug ) || ( $_args->slug != $this->slug ) ) return $_data;
	
			$to_send = array( 'slug' => $this->slug );
	
			$api_response = $this->api_request( 'plugin_information', $to_send );
			if ( false !== $api_response ) $_data = $api_response;
			
			return $_data;
		}
		
		/**
		 * Calls the API and, if successfull, returns the object delivered by the API.
		 *
		 * @uses get_bloginfo()
		 * @uses wp_remote_post()
		 * @uses is_wp_error()
		 *
		 * @param string $_action The requested action.
		 * @param array $_data Parameters for the API action.
		 * @return false||object
		 */
		private function api_request( $_action, $_data ) {
			
			global $wp_version;
	
			
			if( $_data['slug'] != $this->slug )
				return;
	
			$api_params = array(
					'api' => 'true',
					'slug' => $this->slug,
					'author' => '', //$this->_args['software_version'] - not working for some reason
					'license_key' => $this->_args['software_license']
				);
								
			$request = wp_remote_post( $this->_args['software_api_url']  . '/repo/' . $this->plugin_name_slug, array( 'method' => 'POST', 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );				
									
			if ( !is_wp_error( $request ) ):
				$request = json_decode( wp_remote_retrieve_body( $request ) );
				if( $request )
					$request->sections = maybe_unserialize( $request->sections );
				return $request;
			else:
				return false;
			endif;
		}
	}
}