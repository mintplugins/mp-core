<?php
/**
 * Sends for a theme update from the mp_repo plugin instaled on the API site
 * http://moveplugins.com/MP_CORE_MP_REPO_Theme_Updater/
 */
if ( !class_exists( 'MP_CORE_MP_REPO_Theme_Updater' ) ){
	class MP_CORE_MP_REPO_Theme_Updater{
		
		public function __construct($args){
			
			//Parse args					
			$args = wp_parse_args( $args, array( 
				'software_api_url' 	=> '', 	// Our store URL that is running EDD
				'software_license' 	=> NULL, // The license key (used get_option above to retrieve from DB)
				'software_name_slug' 	=> '',	// The slug of this theme
			) );
			
			//Get args
			$this->_args = $args;
			
			//Theme Data
			$this->theme_slug = sanitize_key( get_template() );
			$theme = wp_get_theme( sanitize_key( $this->theme_slug ) );
			$this->version = ! empty( $version ) ? $version : $theme->get( 'Version' );
			
			//Response Key
			$this->response_key = $this->theme_slug . '-update-response';			
						
			//Hook to transient update themes to check for new updates
			add_filter( 'site_transient_update_themes', array( &$this, 'theme_update_transient' ) );
			
			//Hooks which delete the theme transient
			add_filter( 'delete_site_transient_update_themes', array( &$this, 'delete_theme_update_transient' ) );
			add_action( 'load-update-core.php', array( &$this, 'delete_theme_update_transient' ) );
			add_action( 'load-themes.php', array( &$this, 'delete_theme_update_transient' ) );	
			
			//Update Nag on Themes Screen
			add_action( 'load-themes.php', array( &$this, 'load_themes_screen' ) );			
								
		}
		
		/**
		 * Load thickbox on themes screen and call update nag
		 *
		 */
		function load_themes_screen() {
			add_thickbox();
			add_action( 'admin_notices', array( &$this, 'update_nag' ) );
		}
		
		/**
		 * Update notice on Themes screen
		 *
		 */
		function update_nag() {
			$theme = wp_get_theme( $this->theme_slug );
	
			$api_response = get_transient( $this->response_key );
	
			if( false === $api_response )
				return;
	
			$update_url = wp_nonce_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( $this->theme_slug ), 'upgrade-theme_' . $this->theme_slug );
			$update_onclick = ' onclick="if ( confirm(\'' . esc_js( __( "Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update." ) ) . '\') ) {return true;}return false;"';
	
			if ( version_compare( $this->version, $api_response->new_version, '<' ) ) {
	
				echo '<div id="update-nag">';
					printf( '<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.',
						$theme->get( 'Name' ),
						$api_response->new_version,
						'#TB_inline?width=640&amp;inlineId=' . $this->theme_slug . '_changelog',
						$theme->get( 'Name' ),
						$update_url,
						$update_onclick
					);
				echo '</div>';
				echo '<div id="' . $this->theme_slug . '_' . 'changelog" style="display:none;">';
					echo wpautop( $api_response->sections['changelog'] );
				echo '</div>';
			}
		}
				
		/**
		 * Delete the transient for this theme
		 *
		 */
		function delete_theme_update_transient() {
			delete_transient( $this->response_key );
		}
		
		/**
		 * Update the transient for this theme
		 *
		 */
		function theme_update_transient($value) {
			
			$update_data = $this->check_for_update();
			
			//Add the license to the package URL if the license passed in is not NULL
			$update_data['package'] = $this->_args['software_license'] != NULL ? add_query_arg('license', $this->_args['software_license'], $update_data['package'] ) : $update_data['package'];
					
			if ( $update_data ) {
				$value->response[ $this->theme_slug ] = $update_data;
			}
			
			return $value;
		}
		
		/**
		 * Check for Update for this theme
		 *
		 */
		function check_for_update() {
			
			$theme = wp_get_theme( $this->theme_slug );
								
			$update_data = get_transient( $this->response_key ); //malachi-update-response
				
			if ( false == $update_data ) {
				
				$failed = false;
	
				$api_params = array(
					'api' => 'true',
					'slug' => $this->theme_slug,
					'theme' => true,
					'license' => $this->_args['software_license']
				);
								
				$response = wp_remote_post( $this->_args['software_api_url']  . '/repo/' . $this->_args['software_name_slug'], array( 'method' => 'POST', 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
															
				// make sure the response was successful
				if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
					$failed = true;
				}
				
				$update_data = json_decode( wp_remote_retrieve_body( $response ) );
							
				//temporarily added this so that the url in the transient isn't blank and won't trigger an error - Philj
				$update_data->url =  $update_data->homepage;
				
				if ( ! is_object( $update_data ) ) {
					$failed = true;
				}
	
				// if the response failed, try again in 30 minutes
				if ( $failed ) {
					$data = new stdClass;
					$data->new_version = $this->version;
					set_transient( $this->response_key, $data, strtotime( '+30 minutes' ) );
					return false;
				}
	
				// if the status is 'ok', return the update arguments
				if ( ! $failed ) {
					$update_data->sections = maybe_unserialize( $update_data->sections );
					set_transient( $this->response_key, $update_data, strtotime( '+12 hours' ) );
				}
	
			}
	
			if ( version_compare( $this->version, $update_data->new_version, '>=' ) ) {
				return false;
			}
	
			return (array) $update_data;
		}
	}
}

