<?php
/**
 * This file contains the MP_CORE_Plugin_Updater class which plugins can use to keep themselves up to date
 *
 * @link       http://mintplugins.com/doc/plugin-updater-class/
 * @since      1.0.0
 *
 * @package    MP Core
 * @subpackage Classes
 *
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */

/**
 * Plugin Updater Class which plugins can use to keep themselves up to date. 
 * This class will work in conjunction with the MP Repo plugin as a custom repo.
 *
 * @author     Philip Johnston
 * @link       http://mintplugins.com/doc/plugin-updater-class/
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
		 * @link     http://mintplugins.com/doc/plugin-updater-class/
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
			
			//Get args
			$this->_args = $args;		
				
			//If we are not in the admin section of WP, get out of here
			if ( !is_admin() ){
				return;	
			}
									
			//Set up hooks.
			$this->hook();
										
		}
		
		/**
		 * Parse the args used in this class to make sure the defaults are set
		 *
		 * @since 1.0
		 * @see wp_parse_args()
		 * @see sanitize_title()
		 * @param $args Array
		 * @return void
		 */
		public function parse_the_args($args){
			
			//Set defaults for args		
			$args_defaults = array(
				'software_name' => NULL,
				'software_api_url' => NULL,
				'software_filename' => NULL,
				'software_licensed' => NULL,
				'software_wp_repo_ignore' => false,
			);
			
			//Get and parse args
			$args = wp_parse_args( $args, $args_defaults );
			
			//Plugin Name Slug
			$args['software_name_slug'] = sanitize_title ( $args['software_name'] ); //EG move-plugins-core		
			
			//Get current screen
			$this->current_screen = get_current_screen();
			
			return $args;	
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
			
			//Show Option Page on Plugins page as well
			add_action( 'load-plugins.php', array( $this, 'plugins_page') ); 			
					
			add_filter( 'mp_core_custom_plugins', array( $this, 'add_custom_plugin' ) );
			add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3);
			
		}
		
		/**
		 * Check for Updates at the defined API endpoint and modify the update array.
		 *
		 * This function is run when the 'mp_core_custom_plugins' filter is run. It injects the custom plugin data retrieved from the API.
		 * It is reassembled from parts of the native Wordpress plugin update code.
		 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
		 *
		 * @see api_request()
		 * @see MP_CORE_Plugin_Updater::set_plugin_vars()
		 *
		 * @param array $_transient_data Update array build by Wordpress.
		 * @return array Modified update array with custom plugin data.
		 */
		function add_custom_plugin( $custom_api_plugins ) {
			
			//Set plugin vars like software license, name, slug	, version
			$this->set_plugin_vars();
			
			if( empty( $custom_api_plugins ) ) return $custom_api_plugins;
			
			//Add the slug to the info to send to the API
			$to_send = array( 'slug' => $this->slug );
						
			//Check the API for a new version and return the info object
			$api_response = $this->api_request( 'plugin_latest_version', $to_send );
					
			//If the response exists
			if( false !== $api_response && is_object( $api_response ) ) {
			
				//We could use version_compare but it doesn't account for beta versions:  if( version_compare( $this->version, $api_response->new_version, '<' ) ){
				if( $this->version != $api_response->new_version ){
					$api_response->plugin = $this->name;				
					$custom_api_plugins->response[$this->name] = $api_response;
				}
								
			}
			
			return $custom_api_plugins;
		
		}
									
		/**
		 * Updates information on the "View version x.x details" page with custom data.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see MP_CORE_Plugin_Updater::api_request()
		 * @see MP_CORE_Plugin_Updater::set_plugin_vars()
		 * @param mixed $_data
		 * @param string $_action
		 * @param object $_args
		 * @return object $_data
		 */
		function plugins_api_filter( $_data, $_action = '', $_args = null ){
			
			//Set plugin vars like software license, name, slug	, version	
			$this->set_plugin_vars();
			
			if ( ( $_action != 'plugin_information' ) || !isset( $_args->slug ) || ( $_args->slug != $this->slug ) ) return $_data;
	
			$to_send = array( 'slug' => $this->slug );
	
			$api_response = $this->api_request( 'plugin_information', $to_send );
			if ( false !== $api_response ) $_data = $api_response;
			
			return $_data;
			
		}
		
		/**
		 * Sets class variables used by filter functions 
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see get_plugin_data()
		 * @see MP_CORE_Plugin_Updater::parse_the_args()
		 * @return false||object
		 */
		function set_plugin_vars(){
			
			//Parse the args
			$args = $this->parse_the_args( $this->_args );
			
			//Get all plugins		
			$all_plugins = get_plugins();
				
			//Loop through all plugins
			foreach ( (array)$all_plugins as $plugin_file => $plugin_data) {
				
				//Split the plugin_file from mp-core/mp-core.php to just mp-core.php (because that's what the plugin passed us in $args['software_filename'])
				//This allows us to potentially have a different plugin directory name (like mp-core2/mp-core.php) and updates still work		
				$plugin_filename = explode('/', $plugin_file);
				
				//If the plugin isn't in a directory, use the base 
				$plugin_filename = isset($plugin_filename[1]) ? $plugin_filename[1] : $plugin_file;
				
				//Compare if the plugin we are looping through is the one we are looking for
				if ($plugin_filename == $args['software_filename']) {
											
					//If we should ignore the WP repo
					if ( $args['software_wp_repo_ignore'] ){
					
						//Disable check on WP.org repo for this plugin
						add_filter( 'http_request_args', array( &$this, 'disable_plugin_check_from_wp'), 10, 2 );
						
					}
					
					//If this software is licensed, do checks for updates using the license
					if ( $args['software_licensed'] ){
													
						//Get license		
						$license_key = trim( get_option( $args['software_name_slug'] . '_license_key' ) );	
						
						//Disable check on WP.org repo for this plugin
						add_filter( 'http_request_args', array( &$this, 'disable_plugin_check_from_wp'), 10, 2 );
										
					}
					//If this software does not require a license, check for update from MP repo
					else{
																				
						$license_key = NULL;		
					}
								
					//Set variables
					$this->name     = $plugin_file; //EG: mp-core/mp-core.php
					$this->slug     = basename( $plugin_file, '.php'); //EG: mp-core
					$this->software_license  = $license_key;
					$this->version  = $plugin_data['Version'];
					
				}
			}					
		}
		
		/**
		 * Check if we should call the API or just return what is stored in the transient for this plugin
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see MP_CORE_Plugin_Updater::parse_the_args()
		 * @see get_bloginfo()
		 * @see wp_remote_post()
		 * @see is_wp_error()
		 * @param string $_action The requested action.
		 * @param array $_data Parameters for the API action.
		 * @return false||object
		 */
		private function api_request( $_action, $_data ) {
			
			global $wp_version;
			
			//Get the transient where we store the api request for this plugin for 24 hours
			$mp_api_request_transient = get_site_transient( 'mp_api_request_' . $this->slug );
			
			//If we have no transient-saved value, run the API, set a fresh transient with the API value, and return that value too right now.
			if ( empty( $mp_api_request_transient ) ){ 
				
				return $this->actually_do_api_request( $_action, $_data );
				
			}
			
			//If we have this data in the 24 hour transient, saving checks from more often than 24 hours - can be cleared by using the "Check Again" button on the updates page)
			if( isset( $_GET['force-check'] ) ){
		
				return $this->actually_do_api_request( $_action, $_data );
				
			}
			
			//If we are on the update-core page and we haven't already fetched the API for this plugin ON THIS PAGE LOAD
			if(  ( isset( $this->current_screen->base ) && $this->current_screen->base == 'update-core' ) && empty( $this->api_request ) ){
				
				return $this->actually_do_api_request( $_action, $_data );
				
			}
			
			//Otherwise, return what is in the transient
			return $mp_api_request_transient;
				
		}
		
		/**
		 * Calls the API and, if successfull, returns the object delivered by the API.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see MP_CORE_Plugin_Updater::parse_the_args()
		 * @see get_bloginfo()
		 * @see wp_remote_post()
		 * @see is_wp_error()
		 * @param string $_action The requested action.
		 * @param array $_data Parameters for the API action.
		 * @return false||object
		 */
		function actually_do_api_request( $_action, $_data ){
			
			//Parse the args
			$args = $this->parse_the_args( $this->_args );
			
			//This filter can be used to change the API URL. Useful when calling for updates to the API site's plugins which need to be loaded from a separate URL (see mp_repo_mirror)
			$args['software_api_url'] = has_filter( 'mp_core_plugin_update_package_url' ) ? apply_filters( 'mp_core_plugin_update_package_url', $args['software_api_url'] ) : $args['software_api_url'];
			
			if( $_data['slug'] != $this->slug )
				return;
	
			$api_params = array(
					'api' => 'true',
					'slug' => $_data['slug'],
					'author' => '', //$this->version - not working for some reason
					'license_key' => $this->software_license,
					'old_license_key' => get_option( $_data['slug'] . '_license_key' ),
					'site_activating' => get_bloginfo( 'wpurl' )
				);
			$request = wp_remote_post( $args['software_api_url']  . '/repo/' . $args['software_name_slug'], array( 'method' => 'POST', 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );				
									
			if ( !is_wp_error( $request ) ){
				$request = json_decode( wp_remote_retrieve_body( $request ) );
				set_site_transient( $args['software_name_slug'],  $request );
				if( $request ){
					$request->sections = maybe_unserialize( $request->sections );
				}
				$this->api_request = $request;
				//Expires in 1 day (86400 seconds)
				set_site_transient( 'mp_api_request_' . $this->slug, $request, 86400 );
				return $request;
			}else{
				return false;
			}
				
		}
		
		/**
		 * This function is called on the plugins page only
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      MP_CORE_Plugin_Updater::parse_the_args()
		 * @see      add_action()
		 * @return   void
		 */
		function plugins_page() {
			
			//Parse the args
			$args = $this->parse_the_args( $this->_args );
			
			//If this software is licensed, show license field on plugins page
			if ( $args['software_licensed'] ){
								
				//Set the "Green Light" Notification option for this license		
				$this->set_license_green_light(); 
				
				//Enqueue scripts for plugins page			
				add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_license_script' ) );
				
				//Show license on plugin page
				add_action( 'admin_notices', array( &$this, 'display_license' ) ); 
				
			}
			
		}
		
		/**
		 * Function calls the MP_CORE_Verify_License class which listens for a license, 
		 * verifies it, and sets the green light variable to let the user know their license is active
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      MP_CORE_Plugin_Updater::parse_the_args()
		 * @see      MP_CORE_Verify_License
		 * @return   void
		 */
		function set_license_green_light(){
			
			//Parse the args
			$args = $this->parse_the_args( $this->_args );
			
			//This filter can be used to change the API URL. Useful when calling for updates to the API site's plugins which need to be loaded from a separate URL (see mp_repo_mirror)
			$args['software_api_url'] = has_filter( 'mp_core_plugin_update_package_url' ) ? apply_filters( 'mp_core_plugin_update_package_url', $args['software_api_url'] ) : $args['software_api_url'];
			
			$software_name_slug = sanitize_title( $args['software_name'] );
			
			//Listen for our activate button to be clicked
			if( isset( $_POST[ $software_name_slug . '_license_key' ] ) ) {
								
				//If it has, store it in the license_key variable 
				$license_key = $_POST[ $software_name_slug . '_license_key' ];
				
				//Check nonce
				if( ! check_admin_referer( $software_name_slug . '_nonce', $software_name_slug . '_nonce' ) ) 	
					return false; // get out if we didn't click the Activate button
					
				$args = array(
					'software_name'      => $args['software_name'],
					'software_api_url'   => $args['software_api_url'],
					'software_license_key'   => $license_key,
					'software_store_license' => true,
				);
							
				mp_core_verify_license( $args );
				
			}

		}
				
		/**
		 * Enqueue Jquery on Plugin page to place license in correct spot
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      MP_CORE_Plugin_Updater::parse_the_args()
		 * @see      wp_enqueue_script()
		 * @see      wp_localize_script()
		 * @return   void
		 */
		function enqueue_license_script () {
			
			//Parse the args
			$args = $this->parse_the_args( $this->_args );
			
			//Globalize the $global_plugin_update_num variable. It stores the number of times we've localized a plugin updater script
			global $global_plugin_update_num;
			
			//Add 1 to the global_plugin_update_num - This variable is used during registering javascrits
			$global_plugin_update_num = $global_plugin_update_num + 1;
			
			//mp_core_settings_css
			wp_enqueue_style( 'mp_core_settings_css', plugins_url('css/core/mp-core-settings.css', dirname(dirname(__FILE__) ) ) );
			
			//Enqueue style for this license message
			wp_enqueue_style( 'mp-core-licensing-css', plugins_url( 'css/core/mp-core-licensing.css', dirname(dirname(__FILE__) ) ) );	
				
			//Enqueue script for this plugin
			wp_enqueue_script( $args['software_name_slug'] . '-plugins-placement', plugins_url( 'js/plugins-page.js', dirname(__FILE__) ),  array( 'jquery' ) );	
			
			//Pass slug variable to the js
			wp_localize_script( $args['software_name_slug'] . '-plugins-placement', 'mp_core_update_plugin_vars' . $global_plugin_update_num , array(
					'name_slug' => $args['software_name_slug']
				)
			);		
								
		}
		
		/**
		 * Output the code which will display the license form on the plugins page
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      MP_CORE_Plugin_Updater::parse_the_args()
		 * @see      get_option()
		 * @see      wp_nonce_field()
		 * @see      submit_button()
		 * @return   void
		 */
		function display_license(){
			
			//Parse the args
			$args = $this->parse_the_args( $this->_args );
			
			//This filter can be used to change the API URL. Useful when calling for updates to the API site's plugins which need to be loaded from a separate URL (see mp_repo_mirror)
			$args['software_api_url'] = has_filter( 'mp_core_plugin_update_package_url' ) ? apply_filters( 'mp_core_plugin_update_package_url', $args['software_api_url'] ) : $args['software_api_url'];
			//API response
			$api_response = get_site_transient( $args['software_name_slug'] );
			
			//If a new license has just been submitted
			if ( isset( $_POST[$args['software_name_slug'] . '_license_key'] ) ){
				 
				//Get license from $_POST var
				$license_key = $_POST[$args['software_name_slug'] . '_license_key'];
				
			}
			//Otherwise get the license from the database
			else{
								
				//Get license from database
				$license_key = get_option( $args['software_name_slug'] . '_license_key' );
			
			}
			
			//Only verify the license if the transient is older than 7 days
			$check_licenses_transient_time = get_site_transient( 'mp_check_licenses_transient' );
			
			//If our transient is older than 30 days (2592000 seconds)
			if ( time() > ($check_licenses_transient_time + 2592000) ){
				
				//reset the transient
				set_site_transient( 'mp_check_licenses_transient', time() );
			
				//Set args to Verfiy the License
				$verify_license_args = array(
					'software_name'      => $args['software_name'],
					'software_api_url'   => $args['software_api_url'],
					'software_license_key'   => $license_key,
					'software_store_license' => true,
				);
								
				//Double check license. Use the Verfiy License function to verify and store whether this license is valid or not
				mp_core_verify_license( $verify_license_args );	
				
			}
			
			//Get license status (set in verify license class)
			$status = get_option( $args['software_name_slug'] . '_license_status_valid' );
			
			//Get license link:
			$get_license_link = !empty( $api_response->get_license ) ? '<a href="' . $api_response->get_license . '" target="_blank" >' . __( 'Get License', 'mp_core' ) . '</a>' : NULL;
			
			?>
			<div id="<?php echo $args['software_name_slug']; ?>-plugin-license-wrap" class="wrap mp-core-plugin-license-wrap">
				
				<p class="plugin-description"><?php echo __('Enter your license key to enable automatic updates.', 'mp_core'); ?></p>
				
				<form method="post">
									
					<input style="float:left; margin-right:10px;" id="<?php echo $args['software_name_slug']; ?>_license_key" name="<?php echo $args['software_name_slug']; ?>_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license_key ); ?>" />						
					<?php mp_core_true_false_light( array( 'value' => $status, 'description' => $status == true ? __('Auto-updates enabled.', 'mp_core') : __('This license is not valid! ', 'mp_core') . $get_license_link ) ); ?>
					
					<?php wp_nonce_field( $args['software_name_slug'] . '_nonce', $args['software_name_slug'] . '_nonce' ); ?>
							
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
		 * @see      plugin_basename()
		 * @see      unserialize()
		 * @see      serialize()
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

/**
 * This function is hooked to the pre_set_site_transient_update_plugins filter
 *
 * This filter (pre_set_site_transient_update_plugins) currently is called twice for each plugin by WP. <br />
 * See: http://core.trac.wordpress.org/ticket/25542<br />
 * For this reason, to prevent multiple unncecessary API calls, we set up a transient which lasts for 10 seconds and stores the API data.
 * We then pass that to the pre_set_site_transient_update_plugins filter.
 *
 * @access   public
 * @since    1.0.0
 * @see      get_site_transient()
 * @see      set_site_transient()
 * @see      apply_filters()
 * @return   void
 */
function pre_set_site_transient_update_plugins_filter( $_transient_data ) {
		
	if( empty( $_transient_data ) ) return $_transient_data;
		
	global $mp_core_update_plugins_flag;
	
	//If flag is true, this is our second go around with pre_set_site_transient_update_plugins
	if ( $mp_core_update_plugins_flag ){
			
		$custom_api_plugins = new stdClass();
		
		//My wp_remote_post to my custom api is in a function which hooks to this filter:
		$custom_api_plugins = apply_filters( 'mp_core_custom_plugins', $custom_api_plugins );
						
		//If there are plugins passed into this filter
		if ( is_object($custom_api_plugins) && (count(get_object_vars($custom_api_plugins)) > 0) ){
			//Loop through each custom plugin in the custom transient object
			foreach ( $custom_api_plugins->response as $plugin_name => $api_response ){
				
				//Add each custom plugin to the pre_set_site_transient_update_plugins value
				$_transient_data->response[$plugin_name] = $api_response;
								
			}
		}
	
		$mp_core_update_plugins_flag = false;
	
	//If the flag is empty for false
	}else{
	
		$mp_core_update_plugins_flag = true;
			
	}
	
	//Return the new array which includes all custom plugins and WP.org plugins
	return $_transient_data;

}
add_filter( 'pre_set_site_transient_update_plugins', 'pre_set_site_transient_update_plugins_filter' );

/**
 * The ajax plugin update intrduced in WP 4.2 returned true even if it shouldn't. This double checks our custom plugins to make the version number mat up when updating via ajax
 * NOTE: While this function does send all the right information to fail when a plugin doesn't update (eg: bad license), WordPress doesn't currently display the error properly.
 *
 * @access   public
 * @since    1.0.0
 * @return   void
 */
function mp_core_ajax_upgrader_process_complete( $Plugin_Upgrader, $args ){
			
	//If this is an ajax plugin update
	if ( defined('DOING_AJAX') && DOING_AJAX ){
		
		$update_plugins_transient = get_site_transient( 'update_plugins' );
		$plugins_in_question = $update_plugins_transient->response;
		
		foreach( $plugins_in_question as $name_of_plugin_name => $plugin_in_question ){
			break;
		}

		$latest_version_available = $plugin_in_question->new_version;		
		
		//Get all plugins		
		$all_plugins = get_plugins();
			
		//Loop through all plugins
		foreach ( (array)$all_plugins as $plugin_file => $plugin_data) {
			
			//If this is the plugin we are looking for
			if ( $plugin_file == $name_of_plugin_name ){
				
				//If the versions don't match up, it failed
				if ( $latest_version_available != $plugin_data['Version']){
					
					$status['errorCode'] = 'mintplugins_license_invalid';
					$status['error'] = __( 'The plugin license entered is invalid. Please double check.', 'mp_core' );
					wp_send_json_error( $status );
				}
				
			}
			else{
				//do nothing because it successfully updated
			}
		}
	}
}
add_action( 'upgrader_process_complete', 'mp_core_ajax_upgrader_process_complete', 10, 2 );