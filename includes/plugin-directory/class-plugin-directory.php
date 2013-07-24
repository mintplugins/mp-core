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
						
			//Make sure we are on the directory page
			$this->_page = isset($_GET['page']) ? $_GET['page'] : NULL;
			$this->_mp_page_source = isset($_GET['mp-source']) ? $_GET['mp-source'] : NULL;
			
			//If we are on the directory page or the mp_core_install_plugin page
			if ( $this->_page  == $this->_args['slug'] || $this->_mp_page_source == 'mp-core-directory' ) {
				
				//Create install page for each plugin		
				$this->create_install_pages();
			}
			
			//Enqueue Scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts') );
									
			//Create Plugin Directory Page
			add_action( 'admin_menu', array( $this, 'add_submenu_page') );
																
		}
		
		/**
		 * Enqueue Scripts
		 *
		 */
		public function enqueue_scripts(){
			
			//mp_core_settings_css
			wp_enqueue_style( 'mp_core_settings_css', plugins_url('css/core/mp-core-settings.css', dirname(__FILE__)) );
			
			//directory page js
			wp_enqueue_script( 'mp_core_directory_js', plugins_url( 'js/core/directory-page.js', dirname(__FILE__)),  array( 'jquery' ) );
			
		}
		
		/**
		 * Create Plugins Submenu Page in WordPress menu
		 *
		 */
		public function add_submenu_page(){
			add_submenu_page( $this->_args['parent_slug'], $this->_args['page_title'], $this->_args['page_title'], 'activate_plugins', $this->_args['slug'], array( &$this, 'plugin_directory_page' ) );	
		}
		
		/**
		 * Create install pages
		 *
		 */
		public function create_install_pages(){
			
			//Get list of plugins that should be shown
			$plugins = wp_remote_post( $this->_args['directory_list_url'], array( 'method' => 'POST', 'timeout' => 15, 'sslverify' => false, 'body' => array( 'directory' => 'true' ) ) );							 			
			//Json decode plugins array
			$this->plugins = json_decode($plugins['body'], true);
			
			//loop through each plugin
			foreach ( $this->plugins as $plugin ){
				
				//Plugin Name Slug
				$plugin_name_slug = sanitize_title ( $plugin['plugin_name'] ); //EG move-plugins-core	
							
				//Store, Verify, and Set the "Green Light" Notification option for this license
				new MP_CORE_Verify_License( $plugin );		
				
				//Get license
				$license = get_option( $plugin_name_slug . '_license_key' );
				$license_valid = get_option( $plugin_name_slug . '_license_status_valid' );
				
				//If we are on the install page for this plugin
				if ( $this->_page == 'mp_core_install_plugin_page_' .  $plugin['plugin_slug'] ){
					
					// Create update/install plugin page
					new MP_CORE_Plugin_Installer( $plugin, $license );
					
				}
				
				//If the install button for this plugin has been clicked and license entered is valid
				if( isset( $_POST[ $plugin_name_slug . '_license_key' ] ) && $license_valid ) {
					
					//Redirect to plugin install page
					header( 'Location: ' . admin_url( sprintf( 'plugins.php?page=mp_core_install_plugin_page_' .  $plugin['plugin_slug'] . '&mp-source=mp-core-directory&action=install-plugin&plugin=' . $plugin['plugin_slug']  . '&_wpnonce=%s', wp_create_nonce( 'install-plugin_' . $plugin_name_slug  ) ) ) );
					
				}
				//If the install button for this plugin has been clicked and license entered is NOT valid!
				elseif ( isset( $_POST[ $plugin_name_slug . '_license_key' ] ) && !$license_valid ){
					
					//Show "License entered not valid" message
					add_action( 'admin_notices', array( $this, 'license_not_valid') );
				}
			}
		}
		
		/**
		 * Show License Not Valid Message
		 *
		 */
		public function license_not_valid(){
			
			echo '<div class="updated fade"><p>';
				echo 'Oops! That license is not valid!';
			echo '</p></div>';
		}
	
		/**
		 * Show Plugins on Page
		 *
		 */
		public function plugin_directory_page() {
			
			echo '<div class="wrap">' . screen_icon( 'plugins' )  .	'<h2>' . __( 'Install Plugins', 'mp_core' ) . '</h2>';
			
			echo '<div id="availablethemes">';
			
			//Loop through all returned plugins from the wp_remote_post in the construct function	
			foreach ( $this->plugins as $plugin ){
								
				//Plugin Name Slug
				$plugin_name_slug = sanitize_title ( $plugin['plugin_name'] ); //EG move-plugins-core		
				
				//This next section figures out what do make the $install_output variable
				
				//Check if plugin is installed
				$check_plugin = $this->check_if_plugin_is_on_this_server( $plugin );
				
				//If the plugin is active
				if ( $check_plugin['plugin_active'] ) {
										
					//Show the green light
					$install_output = '<div class="mp-core-true-false-light  mp-core-directory-true-false-light">';
						$install_output .= '<div class="mp-core-green-light"></div>';
					$install_output .= '</div>';	
				
					//Set $install_output to say the plugin is active
					$install_output .= 'Plugin is active';
					
					//If this plugin requires a license, show that license
					$install_output .= $plugin['plugin_licensed'] == true ? $this->display_license( $plugin_name_slug, $check_plugin, $plugin['plugin_buy_url'],  $plugin['plugin_price'] ) : NULL;
					
				}
				//If the plugin is installed but is not active
				elseif ( $check_plugin['plugin_exists'] ) {
					
					//Show the red light
					$install_output = '<div class="mp-core-true-false-light  mp-core-directory-true-false-light">';
						$install_output .= '<div class="mp-core-red-light"></div>';
					$install_output .= '</div>';	
					
					//Set $install_output to say the plugin is installed but not active
					$install_output .= 'Plugin is installed but not active';
					
					//Set $install_output to "Activate" plugin
					$install_output .= '<a href="' . wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $check_plugin['plugin_directory'] . $plugin['plugin_filename'] . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'activate-plugin_' . $check_plugin['plugin_directory'] . $plugin['plugin_filename'] ) . '" title="' . __('Activate ', 'mp_core') . $plugin['plugin_name'] . '" class="button" >' . __('Activate ', 'mp_core') . '"' . $plugin['plugin_name'] . '"</a>';
					
					//If this plugin requires a license, show that license
					$install_output .= $plugin['plugin_licensed'] == true ? $this->display_license( $plugin_name_slug, $check_plugin, $plugin['plugin_buy_url'],  $plugin['plugin_price'] ) : NULL;
					
				}
				//If the plugin isn't instaled or active
				else{
					
					
					//Show the red light
					$install_output = '<div class="mp-core-true-false-light  mp-core-directory-true-false-light">';
						$install_output .= '<div class="mp-core-red-light"></div>';
					$install_output .= '</div>';	
					
					//Set $install_output to say the plugin is installed but not active
					$install_output .= 'Plugin is not installed.';
												
					/** If plugins_api isn't available, load the file that holds the function */
					if ( ! function_exists( 'plugins_api' ) )
						require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
 
 
					//Check if this plugin exists in the WordPress Repo
					$args = array( 'slug' => $plugin_name_slug );
					$api = plugins_api( 'plugin_information', $args );
										
					//If it doesn't, display link which downloads it from your custom URL
					if (isset($api->errors)){ 
						
						//If this plugin requires a license, show that license
						if ( $plugin['plugin_licensed'] == true ){
													
							//show license
							$install_output .= $this->display_license( $plugin_name_slug, $check_plugin, $plugin['plugin_buy_url'],  $plugin['plugin_price'] );
							
						}
						else{
							
							// "Oops! this plugin doesn't exist in the repo. So lets display a custom download button."; 
							$install_output = sprintf( '<a class="button" href="%s" style="display:inline-block; margin-right:.7em;"> ' . __('Install "', 'mp_core') . $plugin['plugin_name'] . '"</a>', admin_url( sprintf( 'plugins.php?page=mp_core_install_plugin_page_' .  $plugin['plugin_slug'] . '&action=install-plugin&mp-source=mp-core-directory&plugin=' . $plugin['plugin_slug']  . '&_wpnonce=%s', wp_create_nonce( 'install-plugin_' . $plugin_name_slug  ) ) ) );
						}
											
					}else{
						//Otherwise display the WordPress.org Repo Install button
						$install_output = sprintf( '<a class="button" href="%s" style="display:inline-block; margin-right:.7em;"> ' . __('Install "', 'mp_core') . $plugin['plugin_name'] . '"</a>', admin_url( sprintf( 'update.php?action=install-plugin&mp-source=mp-core-directory&plugin=' . $plugin_name_slug . '&_wpnonce=%s', wp_create_nonce( 'install-plugin_' . $plugin_name_slug ) ) ) );	
					
					}
					
				}
								
				//Show this plugin on the page
				echo '<div class="available-theme">
						<a href="" class="screenshot">
							<img src="' . $plugin['plugin_image'] . '" alt="' . $plugin['plugin_name'] . '">
						</a>
						
						<div class="mp-core-directory-price">' . $plugin['plugin_price'] . '</div>
						
						<h3>' . $plugin['plugin_name'] . '</h3>
						
						<div class="theme-author">
						
							' . $plugin['plugin_description'] . 
						
						'</div>
						
						<div class="action-links">
							
								' . $install_output . '
									
						</div>

				</div>';
				
			}
			
			echo '</div>';
			
		}
			
		/**
		 * Display the license on the plugins page
		 *
		 * @param $plugin_name_slug string
		 * @param $check_plugin array( 'plugin_active' => false, 'plugin_exists' => false, 'plugin_directory' => $plugin_directory )
		 *
		 * return $output - HTML output for the license button
		 */
		public function display_license( $plugin_name_slug, $check_plugin, $buy_url, $price ){
			
			//Get and set license and status
			$license 	= get_option( $plugin_name_slug . '_license_key' );
			$status 	= get_option( $plugin_name_slug . '_license_status_valid' );
			
			$output = '<div id="' . $plugin_name_slug . '-plugin-license-wrap" class="wrap mp-core-plugin-directory-license-wrap">';
				
			//If this license is valid
			if ( $status == true ){
				
				//Show a message that the license is valid
				$output .= __('License Valid', 'mp_core');
				
				//Show the green light
				$output .= '<div class="mp-core-true-false-light  mp-core-directory-true-false-light">';
					$output .= '<div class="mp-core-green-light"></div>';
				$output .= '</div>';		
				
				//Show a link to update the license
				$output .= ' | ' . '<a class="mp-core-update-license">Update License</a>';
				
				//If the plugin is already active and installed
				if ( $check_plugin['plugin_active'] == true && $check_plugin['plugin_exists'] == true) {
					//UPDATE LICENSE FORM - initally hidden
					$output .= '<form method="post" class="mp-core-directory-update-license-form">';
				}
				//If the plugin doesn't even exist on this server
				else{
					//UPDATE LICENSE BUTTON - initially shown
					$output .= '<form method="post">';
				}
				
					//The input field for the license
					$output .= '<input style="float:left; margin-right:10px;" id="' . $plugin_name_slug . '_license_key" name="' . $plugin_name_slug . '_license_key" type="text" class="mp-core-cirectory-license-input regular-text" value="' . esc_attr( $license ) . '" />';
												
					//Nonce								
					$output .= wp_nonce_field( $plugin_name_slug . '_nonce', $plugin_name_slug . '_nonce', true, false );
					
					//Separation		
					$output .= '<br />';
					
					//Show the submit and install button
					$output .= get_submit_button(__('Submit License and Install', 'mp_core') );
				
				//End of license form
				$output .= '</form>';
				
				
			}
			//If this license is not valid
			else{
										
				//If a license has been entered incorectly
				if ( !empty( $license ) ) {	
									
					//Show Red Light
					$output .= '<div class="mp-core-true-false-light mp-core-directory-true-false-light">';
							$output .= '<div class="mp-core-red-light"></div>';
					$output .= '</div>';
					
					//Show message that license is not valid
					$output .= __('Current License is not valid.', 'mp_core');
				
				}
				
				//Show the buy button
				$output .= '<div class="mp-core-directory-buy"><a class="button" href="' . $buy_url . '" target="_blank">' . __( 'Get License Now - ', 'mp_core' ) . $price . '</a></div>'; 
				
				//Ask the user if they already have a license.
				$output .= '<div class="mp-core-directory-license-msg">' . __('Already have a license? Enter here to install:', 'mp_core') . '</div>';
				
				//License Form
				$output .= '<form method="post">';
					
					//License Input Field
					$output .= '<input style="float:left; margin-right:10px;" id="' . $plugin_name_slug . '_license_key" name="' . $plugin_name_slug . '_license_key" type="text" class="mp-core-cirectory-license-input regular-text" value="' . esc_attr( $license ) . '" />		';				
					
					//Nonce							
					$output .= wp_nonce_field( $plugin_name_slug . '_nonce', $plugin_name_slug . '_nonce', true, false );
					
					//Separation	
					$output .= '<br />';
					
					//Show the submit and install button
					$output .= get_submit_button(__('Submit License and Install', 'mp_core') );
				
				//End license form
				$output .= '</form>';
				
			}								
			
							
			$output .= '</div>';
	   		
			return $output;
		}
	
		/**
		 * This function checks if a plugin is installed or not
		 * Returns array: array( 'plugin_active' => true, 'plugin_exists' => true, 'plugin_directory' => NULL );
		 */	
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
}

