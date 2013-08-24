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
			
			//Plugin Name Slug
			$this->plugin_name_slug = sanitize_title ( $this->_args['software_name'] ); //EG move-plugins-core		
			
			//If this software is licensed, show license field on plugins page
			if ( $this->_args['software_licensed'] ){
				
				//Set the "Green Light" Notification option for this license		
				add_action( 'admin_init', array( &$this, 'set_license_green_light' ) ); 
			
				//Show Option Page on Plugins page as well
				add_action( 'load-plugins.php', array( $this, 'plugins_page') ); 
				
				//Enqueue style for license
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts') ); 
				
				//Create Option page for updates
				//add_action( 'admin_menu', array( &$this, 'updates_menu' ) );
				
			}
			
			//Plugin Update Function	
			add_action( 'admin_init', array( &$this, 'mp_core_update_plugin' ) ); 	
									
		}
		
		function enqueue_scripts(){
			
			//Enqueue style for this license message
			wp_enqueue_style( 'mp-core-licensing-css', plugins_url( 'css/core/mp-core-licensing.css', dirname(dirname(__FILE__) ) ) );	
			
		}
					
		/***********************************************
		* This is our updater
		***********************************************/
		function mp_core_update_plugin(){
			
			//Get Plugins directory
			$all_plugins_dir = explode( 'wp-content/plugins/', __FILE__ );
			
			//Get list of all active plugins
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ));
			
			//Loop through each active plugin's string EG: (subdirectory/filename.php)
			foreach ($active_plugins as $active_plugin){
				//Check if the filename of the plugin in question exists in any of the plugin strings
				if (strpos($active_plugin, $this->_args['software_filename'])){	
					
					//Store the plugin's directory and name. IE: mp_core/mp_core.php
					$plugin_dir_and_name = $active_plugin;
					
					//Stop looping
					break;
				}
			}
			
			//Complete plugin url
			$plugin_url = $all_plugins_dir[0] . 'wp-content/plugins/' . $plugin_dir_and_name; 
			$this->_plugin_url = $plugin_url;
			
			//Get plugin data
			$plugin_data = get_plugin_data( $plugin_url, $markup = true, $translate = true );
			
			//If this software is licensed, do checks for updates using the license
			if ( $this->_args['software_licensed'] ){
								
				//Disable check on WP.org repo for this plugin
				add_filter( 'http_request_args', array( &$this, 'disable_plugin_check_from_wp'), 10, 2 );

				//Get license		
				$license_key = trim( get_option( $this->plugin_name_slug . '_license_key' ) );	
				
			}
			//If this software does not require a license, check for update from MP repo
			else{
																		
				$license_key = NULL;		
			}
			
			//Do Update
			if ( !class_exists( 'MP_CORE_MP_REPO_Plugin_Updater' ) ) {
				// Load our custom theme updater
				include( dirname( __FILE__ ) . '/mp-repo/class-mp-repo-plugin-updater.php' );
			}
										
			//Call the MP_CORE_MP_REPO_Plugin_Updater Updater Class
			$updater = new MP_CORE_MP_REPO_Plugin_Updater( array( 
					'software_version'  => $plugin_data['Version'],
					'software_file_url'  => $plugin_url,
					'software_api_url' 	=> $this->_args['software_api_url'], 	// Our store URL that is running EDD
					'software_license' 	=> $license_key, // The license key (used get_option above to retrieve from DB)
					'software_name' 	=> $this->_args['software_name'],	// The slug of this theme
				)
			);
			
		}
		
		/**
		 * Function which sets the green light variable to let the user know their license is active
		 */
		function set_license_green_light(){
			
			$args = array(
				'software_name'      => $this->_args['software_name'],
				'software_api_url'   => $this->_args['software_api_url']
			);
						
			new MP_CORE_Verify_License( $args );		

		}
		
		/**
		 * This function is called on the plguins page only
		 */
		function plugins_page() {
			
			$plugin_status = isset( $_GET['plugin_status'] ) ? $_GET['plugin_status'] : NULL;
			$action = isset( $_GET['action'] ) ? $_GET['action'] : NULL;
				
			//Enqueue scripts for plugins page			
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_license_script' ) );
			
			//Show license on plugin page
			add_action( 'admin_notices', array( &$this, 'display_license' ) ); 
			
			
		}
		
		/**
		 * Enqueue Jquery on Plugin page to place license in correct spot
		 */
		function enqueue_license_script () {
			
			//Globalize the $global_plugin_update_num variable. It stores the number of times we've localized a plugin updater script
			global $global_plugin_update_num;
			
			//Add 1 to the global_plugin_update_num - This variable is used during registering javascrits
			$global_plugin_update_num = $global_plugin_update_num + 1;
				
			//Enqueue script for this plugin
			wp_enqueue_script( $this->plugin_name_slug. '-plugins-placement', plugins_url( 'js/plugins-page.js', dirname(__FILE__) ),  array( 'jquery' ) );	
			
			//Pass slug variable to the js
			wp_localize_script( $this->plugin_name_slug. '-plugins-placement', 'mp_core_update_plugin_vars' . $global_plugin_update_num , array(
					'name_slug' => $this->plugin_name_slug
				)
			);		
								
		}
		
		/**
		 * Display the license on the plugins page
		 */
		function display_license(){
			
			//Get and set license and status
			$license_key 	= get_option( $this->plugin_name_slug . '_license_key' );
			$status 	= get_option( $this->plugin_name_slug . '_license_status_valid' );
			?>
			<div id="<?php echo $this->plugin_name_slug; ?>-plugin-license-wrap" class="wrap mp-core-plugin-license-wrap">
				
				<p class="plugin-description"><?php echo __('Enter your license key to enable automatic updates', 'mp_core'); ?></p>
				
				<form method="post">
									
					<input style="float:left; margin-right:10px;" id="<?php echo $this->plugin_name_slug; ?>_license_key" name="<?php echo $this->plugin_name_slug; ?>_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license_key ); ?>" />						
					<?php mp_core_true_false_light( array( 'value' => $status, 'description' => $status == true ? __('License is valid', 'mp_core') : __('This license is not valid!', 'mp_core') ) ); ?>
					
					<?php wp_nonce_field( $this->plugin_name_slug . '_nonce', $this->plugin_name_slug . '_nonce' ); ?>
							
					<br />
						
					<?php submit_button(__('Submit License', 'mp_core') ); ?>
				
				</form>
			</div>
	   
			<?php
		}
		
		/**
		 * Disable update check from the WP.org plugin repo
		 */
		function disable_plugin_check_from_wp( $r, $url ) {
			if ( 0 === strpos( $url, 'http://api.wordpress.org/plugins/update-check/' ) ) {
				$plugin = plugin_basename( $this->_plugin_url );
				$plugins = unserialize( $r['body']['plugins'] );
				unset( $plugins->plugins[$plugin] );
				unset( $plugins->active[array_search( $plugin, $plugins->active )] );
				$r['body']['plugins'] = serialize( $plugins );
			}
			return $r;
		}
	}
}

//Enqueue Jquery on Plugin page to place license in correct spot
function mp_core_update_plugin_global_var() {
	
	//Enqueue Jquery on Plugin page to place license in correct spot
	function global_plugin_update_num_function() {
		
		//Enqueue script for this plugin
		wp_enqueue_script( 'global_plugin_update_num', plugins_url( 'js/global_plugin_update_num.js', dirname(__FILE__) ),  array( 'jquery' ) );	
						
	};
	add_action( 'admin_enqueue_scripts', 'global_plugin_update_num_function' );
					
};
add_action( 'load-plugins.php', 'mp_core_update_plugin_global_var' );