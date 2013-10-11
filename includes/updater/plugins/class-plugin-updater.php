<?php
/**
 * This file contains the MP_CORE_Plugin_Updater class which plugins can use to keep themselves up to date
 *
 * @link       http://moveplugins.com/doc/plugin-updater-class/
 * @since      1.0.0
 *
 * @package    MP Core
 * @subpackage Classes
 *
 * @copyright  Copyright (c) 2013, Move Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */

/**
 * Plugin Updater Class which plugins can use to keep themselves up to date. 
 * This class will work in conjunction with the MP Repo plugin as a custom repo.
 *
 * @author     Philip Johnston
 * @link       http://moveplugins.com/doc/plugin-updater-class/
 * @since      1.0.0
 * @return     void
 */	
if ( !class_exists( 'MP_CORE_Plugin_Updater' ) ){
	class MP_CORE_Plugin_Updater{
		
		/**
		 * Constructor
		 *
		 * @access   public
		 * @since    1.0.0
		 * @link     http://moveplugins.com/doc/plugin-updater-class/
		 * @author   Philip Johnston
		 * @see      MP_CORE_Plugin_Updater::set_license_green_light()
		 * @see      MP_CORE_Plugin_Updater::plugins_page()
		 * @see      MP_CORE_Plugin_Updater::enqueue_scripts()
		 * @see      MP_CORE_Plugin_Updater::mp_core_update_plugin()
		 * @see      wp_parse_args()
		 * @see      sanitize_title()
		 * @param    array $args (required) See link for description.
		 * @return   void
		 */	
		public function __construct($args){
													
			//Set defaults for args		
			$args_defaults = array(
				'software_name' => NULL,
				'software_api_url' => NULL,
				'software_filename' => NULL,
				'software_licensed' => NULL,
				'software_wp_repo_ignore' => false,
			);
			
			//Get and parse args
			$this->_args = wp_parse_args( $args, $args_defaults );
			
			//Plugin Name Slug
			$this->plugin_name_slug = sanitize_title ( $this->_args['software_name'] ); //EG move-plugins-core		
			
			//This filter can be used to change the API URL. Useful when calling for updates to the API site's plugins which need to be loaded from a separate URL (see mp_repo_mirror)
			$this->_args['software_api_url'] = has_filter( 'mp_core_plugin_update_package_url' ) ? apply_filters( 'mp_core_plugin_update_package_url', $this->_args['software_api_url'] ) : $this->_args['software_api_url'];
			
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
					
			// Set up hooks.
			$this->hook();
										
		}
		
		/**
		 * Enqueue Scripts needed for the MP_CORE_Plugin_Updater class
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      wp_enqueue_style()
		 * @see      plugins_url()
		 * @return   void
		 */
		function enqueue_scripts(){
			
			//Enqueue style for this license message
			wp_enqueue_style( 'mp-core-licensing-css', plugins_url( 'css/core/mp-core-licensing.css', dirname(dirname(__FILE__) ) ) );	
			
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
		 * @see add_filter()
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
		 * This filter (pre_set_site_transient_update_plugins) currently is called twice for each plugin by WP. <br />
		 * See: http://core.trac.wordpress.org/ticket/25542
		 *
		 * @see api_request()
		 *
		 * @param array $_transient_data Update array build by Wordpress.
		 * @return array Modified update array with custom plugin data.
		 */
		function pre_set_site_transient_update_plugins_filter( $_transient_data ) {
						
			//We need to find the directory name, or 'slug', of this plugin. So get Plugins directory
			$all_plugins_dir = explode( 'wp-content/plugins/', __FILE__ );
			
			//Get list of all active plugins
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ));
			
			//Loop through each active plugin's string EG: (subdirectory/filename.php)
			foreach ($active_plugins as $active_plugin){
				//Check if the filename of the plugin passed-in exists in any of the plugin strings
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
			
			//If we should ignore the WP repo
			if ( $this->_args['software_wp_repo_ignore'] ){
			
				//Disable check on WP.org repo for this plugin
				add_filter( 'http_request_args', array( &$this, 'disable_plugin_check_from_wp'), 10, 2 );
				
			}
			
			//If this software is licensed, do checks for updates using the license
			if ( $this->_args['software_licensed'] ){
											
				//Get license		
				$license_key = trim( get_option( $this->plugin_name_slug . '_license_key' ) );	
				
				//Disable check on WP.org repo for this plugin
				add_filter( 'http_request_args', array( &$this, 'disable_plugin_check_from_wp'), 10, 2 );
								
			}
			//If this software does not require a license, check for update from MP repo
			else{
																		
				$license_key = NULL;		
			}
					
			//Set variables
			$this->name     = plugin_basename( $plugin_url ); //EG: mp-core/mp-core.php
			$this->slug     = basename( $plugin_url, '.php'); //EG: mp-core
			$this->software_license  = $license_key;
			$this->version  = $plugin_data['Version'];
			
			if( empty( $_transient_data ) ) return $_transient_data;
			
			//Add the slug to the info to send to the API
			$to_send = array( 'slug' => $this->slug );
			
			//Check the API for a new version and return the info object
			$api_response = $this->api_request( 'plugin_latest_version', $to_send );
					
			//If the response exists
			if( false !== $api_response && is_object( $api_response ) ) {
			
				//We could use version_compare but it doesn't account for beta versions:  if( version_compare( $this->version, $api_response->new_version, '<' ) ){
				if( $this->version != $api_response->new_version ){				
					$_transient_data->response[$this->name] = $api_response;
				}
								
			}
			
			return $_transient_data;
		
		}
									
		/**
		 * Updates information on the "View version x.x details" page with custom data.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see MP_CORE_Plugin_Updater::api_request()
		 * @param mixed $_data
		 * @param string $_action
		 * @param object $_args
		 * @return object $_data
		 */
		function plugins_api_filter( $_data, $_action = '', $_args = null ){
			
			if ( ( $_action != 'plugin_information' ) || !isset( $_args->slug ) || ( $_args->slug != $this->slug ) ) return $_data;
	
			$to_send = array( 'slug' => $this->slug );
	
			$api_response = $this->api_request( 'plugin_information', $to_send );
			if ( false !== $api_response ) $_data = $api_response;
			
			return $_data;
			
		}
		
		/**
		 * Calls the API and, if successfull, returns the object delivered by the API.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see get_bloginfo()
		 * @see wp_remote_post()
		 * @see is_wp_error()
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
					'slug' => $_data['slug'],
					'author' => '', //$this->version - not working for some reason
					'license_key' => $this->software_license
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
		
		/**
		 * Function calls the MP_CORE_Verify_License class which listens for a license, 
		 * verifies it, and sets the green light variable to let the user know their license is active
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      MP_CORE_Verify_License
		 * @return   void
		 */
		function set_license_green_light(){
			
			$args = array(
				'software_name'      => $this->_args['software_name'],
				'software_api_url'   => $this->_args['software_api_url']
			);
						
			new MP_CORE_Verify_License( $args );		

		}
		
		/**
		 * This function is called on the plugins page only
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      add_action()
		 * @return   void
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
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      wp_enqueue_script()
		 * @see      wp_localize_script()
		 * @return   void
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
		 * Output the code which will display the license form on the plugins page
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      get_option()
		 * @see      wp_nonce_field()
		 * @see      submit_button()
		 * @return   void
		 */
		function display_license(){
			
			//Get license
			$license_key = get_option( $this->plugin_name_slug . '_license_key' );
			
			//Set args to Verfiy the License
			$verify_license_args = array(
				'software_name'      => $this->_args['software_name'],
				'software_api_url'   => $this->_args['software_api_url'],
				'software_license'   => $license_key
			);
			
			//Double check license. Use the Verfiy License class to verify whether this license is valid or not
			new MP_CORE_Verify_License( $verify_license_args );	
			
			//Get license status (set in verify license class)
			$status = get_option( $this->plugin_name_slug . '_license_status_valid' );
			
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
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      get_option()
		 * @see      wp_nonce_field()
		 * @see      submit_button()
		 * @param    submit_button()
		 * @param    submit_button()
		 * @return   void
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



/**
 * This function calls a function which enqueues a js file 
 * which is used to count how many plugin licenses have been displayed
 * so each can have it's own unique ID
 *
 * @access   public
 * @since    1.0.0
 * @see      wp_enqueue_script()
 * @see      add_action()
 * @return   void
 */
function mp_core_update_plugin_global_var() {
	
	//Enqueue Jquery on Plugin page to place license in correct spot
	function global_plugin_update_num_function() {
		
		//Enqueue script for this plugin
		wp_enqueue_script( 'global_plugin_update_num', plugins_url( 'js/global_plugin_update_num.js', dirname(__FILE__) ),  array( 'jquery' ) );	
						
	};
	add_action( 'admin_enqueue_scripts', 'global_plugin_update_num_function' );
					
};
add_action( 'load-plugins.php', 'mp_core_update_plugin_global_var' );