<?php
/**
 * Plugin Checker Class for the wp_core Plugin by Move Plugins
 * http://moveplugins.com/plugin-checker-class/
 */
if ( !class_exists( 'MP_CORE_Updater' ) ){
	class MP_CORE_Updater{
		
		public function __construct($args){
			
			//Get args
			$this->_args = $args;
			
			//If the user has just clicked "Dismiss", than add that to the options table
			add_action( 'admin_init', array( $this, 'mp_core_check_update') );
						
								
		}
		
		/**
		 * Show notice that plugin should be installed
		 *
		 */
		public function mp_core_check_update() {
			
				//This a plugin.
				if ( $this->_args['software_type'] == 'plugin' ){
				
				}
				//This not a plugin. Rather, it is a theme
				elseif ( $this->_args['software_type'] == 'theme' ){
					
					//Get theme info
					$theme = wp_get_theme($this->_args['software_slug']); // $theme->Name
										
					//Get current theme version
					$theme_current_version = $theme->Version;
					
					//Get latest theme version
					$theme_latest_version = wp_remote_get($this->_args['software_api_url']);
					$theme_latest_version = $theme_latest_version['body'];
					
					$api_params = array(
						'api_download'  => 'true',
						'version' 	=> 'get_version',
						'license' 		=> 123
					);
					$request = wp_remote_post( $this->_args['software_api_url'], array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
					
					print_r($request);
					
					if ( $theme_current_version != $theme_latest_version ){
						
						//Get and set the site transient for this theme 
						$theme_transient = get_site_transient('update_themes');  
						$theme_transient->response[$this->_args['software_slug']] = array(  
							'new_version' => $theme_latest_version,  
							'url' => $this->_args['software_page_url'],  
							'package' => $this->_args['software_download_url'] 
						);  
						//set_site_transient('update_themes', $theme_transient); 
						
					}
				
				}
				
		}
	}
}

