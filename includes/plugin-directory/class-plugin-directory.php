<?php
/**
 * Plugin Directory Class for the mp_core Plugin by Move Plugins
 * http://moveplugins.com/doc/plugin-directory-class/
 */
if ( !class_exists( 'MP_CORE_Plugin_Directory' ) ){
	class MP_CORE_Plugin_Directory{
		
		public function __construct($args){
			
			//Get args
			$this->_args = $args;
			
			//Get list of plugins that should be shown
			$plugins = wp_remote_post( $this->_args['directory_list_url'], array( 'method' => 'POST', 'timeout' => 15, 'sslverify' => false, 'body' => array( 'directory' => 'true' ) ) );							 
			$this->plugins = json_decode($plugins['body'], true);
									
			foreach ( $this->plugins as $plugin ){
			
				// Create update/install plugin page
				new MP_CORE_Plugin_Installer( $plugin );
			
			}
									
			//Create Plugin Directory Page
			add_action( 'admin_menu', array( $this, 'add_submenu_page') );
			
			//Have license input field for each plugin 
			
			//Do license verification
			
			//Download and Install Plugin
										
		}
		
		/**
		 * Show Plugins on Page
		 *
		 */
		public function add_submenu_page(){
			add_submenu_page( $this->_args['parent_slug'], $this->_args['page_title'], $this->_args['page_title'], 'activate_plugins', $this->_args['slug'], array( &$this, 'plugin_directory_page' ) );	
		}
	
		/**
		 * Show Plugins on Page
		 *
		 */
		public function plugin_directory_page() {
			
			echo '<div class="wrap">' . screen_icon( 'plugins' )  .	'<h2>' . __( 'Install Plugins', 'mp_core' ) . '</h2>';
					
			//Get list of plugins that should be shown
			$plugins = wp_remote_post( $this->_args['directory_list_url'], array( 'method' => 'POST', 'timeout' => 15, 'sslverify' => false, 'body' => array( 'directory' => 'true' ) ) );							 
			$plugins = json_decode($plugins['body'], true);
			
			echo '<div id="availablethemes">';
						
			foreach ( $this->plugins as $plugin ){
				
				//print_r($plugin );
				
				//Check if plugin is installed
				$check_plugin = check_if_plugin_is_on_this_server( $plugin );
				
				if ( $check_plugin['plugin_active'] ) {
					
					$install_button = 'Plugin is active';
					
				}
				elseif ( $check_plugin['plugin_exists'] ) {
					
					$install_button = '<a href="' . wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $check_plugin['plugin_directory'] . $plugin['plugin_filename'] . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'activate-plugin_' . $check_plugin['plugin_directory'] . $plugin['plugin_filename']) . '" title="' . __('Activate ', 'mp_core') . $plugin['plugin_name'] . '" class="button" >' . __('Activate ', 'mp_core') . '"' . $plugin['plugin_name'] . '"</a>';
					
				}
				else{
									
					//Plugin Name Slug
					$this->plugin_name_slug = sanitize_title ( $plugin['plugin_name'] ); //EG move-plugins-core		
		
					/** If plugins_api isn't available, load the file that holds the function */
					if ( ! function_exists( 'plugins_api' ) )
						require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
 
 
					//Check if this plugin exists in the WordPress Repo
					$args = array( 'slug' => $this->plugin_name_slug );
					$api = plugins_api( 'plugin_information', $args );
										
					//If it doesn't, display link which downloads it from your custom URL
					if (isset($api->errors)){ 
						// "Oops! this plugin doesn't exist in the repo. So lets display a custom download button."; 
						$install_button = sprintf( '<a class="button" href="%s" style="display:inline-block; margin-right:.7em;"> ' . __('Install "', 'mp_core') . $plugin['plugin_name'] . '"</a>', admin_url( sprintf( 'plugins.php?page=mp_core_install_plugin_page_' .  $plugin['plugin_slug'] . '&action=install-plugin&plugin=' . $plugin['plugin_slug']  . '&_wpnonce=%s', wp_create_nonce( 'install-plugin_' . $plugin['plugin_download_link']  ) ) ) );	
						
					}else{
						//Otherwise display the WordPress.org Repo Install button
						$install_button = sprintf( '<a class="button" href="%s" style="display:inline-block; margin-right:.7em;"> ' . __('Install "', 'mp_core') . $plugin['plugin_name'] . '"</a>', admin_url( sprintf( 'update.php?action=install-plugin&plugin=' . $this->plugin_name_slug . '&_wpnonce=%s', wp_create_nonce( 'install-plugin_' . $this->plugin_name_slug ) ) ) );	
					
					}
						
				}
				
				echo '<div class="available-theme">
						<a href="" class="screenshot">
							<img src="" alt="">
						</a>
						<a href=" class="screenshot">
							<img src="" alt="">
						</a>

						<h3>' . $plugin['plugin_name'] . '</h3>
						<div class="theme-author">' . $plugin['plugin_description'] . '</a></div>
						<div class="action-links">
							<ul>
								<li>' . $install_button . '</li>
							</ul>		
						</div>

						<div class="themedetaildiv hide-if-js">
							<p><strong>Version: </strong>1.0</p>
							<p>An app theme.</p>
						</div>
				</div>';
				
				
			}
			
			echo '</div>';
			
		}
		
	}
	
	function check_if_plugin_is_on_this_server( $args ){
		
		/**
		 * Take steps to see if the 
		 * Plugin is installed
		 */	
			 
		//Get array of active plugins
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ));
		
		//Set default for $plugin_active
		$plugin_active = false;
		
		//Loop through each active plugin's string EG: (subdirectory/filename.php)
		foreach ($active_plugins as $active_plugin){
			//Check if the filename of the plugin in question exists in any of the plugin strings
			if (strpos($active_plugin, $args['plugin_filename'])){	
				
				//Plugin is active
				return array( 'plugin_active' => true, 'plugin_exists' => true, 'plugin_directory' => NULL );
				
				//Stop looping
				break;
			}
		}
		
		
		//If this plugin is not active
		if (!$plugin_active){
								
			/**
			 * Take steps to see if the 
			 * Plugin already exists or not
			 */	
			 
			//Check if the plugin file exists in the plugin root
			$plugin_root_files = array_filter(glob('../wp-content/plugins/' . '*'), 'is_file');
			
			//Preset value for plugin_exists to false
			$plugin_exists = false;
			
			//Preset value for $plugin_directory
			$plugin_directory = NULL;
			
			//Check if the plugin file is directly in the plugin root
			if (in_array( '../wp-content/plugins/' . $args['plugin_filename'], $plugin_root_files ) ){
				
				//Set plugin_exists to true
				$plugin_exists = true;
				
			}
			//Check if plugin exists in a subfolder inside the plugin root
			else{	
								 
				//Find all directories in the plugins directory
				$plugin_dirs = array_filter(glob('../wp-content/plugins/' . '*'), 'is_dir');
																					
				//Loop through each plugin directory
				foreach ($plugin_dirs as $plugin_dir){
					
					//Scan all files in this plugin and store them in an array
					$plugins_files = scandir($plugin_dir);
					
					//If the plugin filename in question is in this plugin's array, than this plugin exists but it is not active
					if (in_array( $args['plugin_filename'], $plugins_files ) ){
						
						//Set plugin_exists to true
						$plugin_exists = true;
						
						//Set the plugin directory for later use
						$plugin_directory = explode('../wp-content/plugins/', $plugin_dir);
						$plugin_directory = !empty($plugin_directory[1]) ? $plugin_directory[1] . '/' : NULL;
						
						//Stop checking through plugins
						break;	
					}							
				}
			}
	
			//This plugin exists but is just not active
			if ($plugin_exists){
								
				return array( 'plugin_active' => false, 'plugin_exists' => true, 'plugin_directory' => $plugin_directory );
			
			//This plugin doesn't even exist on this server	 	
			}else{
				
				return array( 'plugin_active' => false, 'plugin_exists' => false, 'plugin_directory' => $plugin_directory );
			
			}
		}
					
	}
}

