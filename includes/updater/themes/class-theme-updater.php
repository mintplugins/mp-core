<?php
/**
 * This file contains the MP_CORE_Theme_Updater class which tehemes can use to keep themselves up to date
 *
 * @link http://mintplugins.com/doc/theme-updater-class/
 * @since 1.0.0
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
 * @link       http://mintplugins.com/doc/theme-updater-class/
 * @since      1.0.0
 * @return     void
 */	
if ( !class_exists( 'MP_CORE_Theme_Updater' ) ){
	class MP_CORE_Theme_Updater{
		
		/**
		 * Constructor
		 *
		 * @access   public
		 * @since    1.0.0
		 * @link     http://mintplugins.com/doc/plugin-updater-class/
		 * @author   Philip Johnston
		 * @see      MP_CORE_Plugin_Updater::set_license_green_light()
		 * @see      MP_CORE_Plugin_Updater::themes_page()
		 * @see      wp_parse_args()
		 * @see      sanitize_title()
		 * @param    array $args (required) See link for description.
		 * @return   void
		 */	
		public function __construct($args){					
			
			//Get and parse args
			$this->_args = $args;
						
			//Show License on Themes page and Update Nag
			add_action( 'load-themes.php', array( $this, 'themes_page') );  
									
			//Hook to transient update themes to check for new updates
			add_filter( 'site_transient_update_themes', array( &$this, 'theme_update_transient' ) );
			add_action( 'after_switch_theme', array( &$this, 'check_for_update' ) );
			
			//Hooks which delete the theme transient
			add_filter( 'delete_site_transient_update_themes', array( &$this, 'delete_theme_update_transient' ) );
			add_action( 'load-update-core.php', array( &$this, 'delete_theme_update_transient' ) );
			add_action( 'load-themes.php', array( &$this, 'delete_theme_update_transient' ) );		
						
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
		public function parse_the_args( $args ){
			
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
						
			//Theme Data
			$args['theme_slug'] = sanitize_title ( get_template() ); //EG knapstack (directory name)
			$args['theme_name_slug'] = sanitize_title ( $args['software_name']  ); //EG knapstack-theme (Name of theme in style.css)
			
			//Theme Version
			$args['theme'] = wp_get_theme( $args['theme_slug'] );
			$args['theme_version'] = $args['theme']->get( 'Version' );
			
			//Get current screen
			$this->current_screen = get_current_screen();
			
			return $args;	
		}
		
		/**
		 * Update notice on Themes screen
		 *
		 */
		function update_nag() {
			
			//Set the defaults and values for $args
			$args = $this->parse_the_args( $this->_args );
				
			$api_response = get_site_transient( 'mp_api_request_' . $args['theme_name_slug'] );
						
			if( false === $api_response || $api_response == "No Theme Update Available" )
				return;
	
			$update_url = wp_nonce_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( $args['theme_slug'] ), 'upgrade-theme_' . $args['theme_slug'] );
			$update_onclick = ' onclick="if ( confirm(\'' . esc_js( __( "Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update." ) ) . '\') ) {return true;}return false;"';
			
			
			//We could use version_compare here but it doesn't account for beta mode: if ( version_compare( $args['theme_version'], $api_response->new_version, '<' ) ) {	
			if( $args['theme_version'] < $api_response['new_version'] ){	
	
				echo '<div id="update-nag">';
					printf( '<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.',
						$args['theme']->get( 'Name' ),
						$api_response['new_version'],
						'#TB_inline?width=640&amp;inlineId=' . $args['theme_slug'] . '_changelog',
						$args['theme']->get( 'Name' ),
						$update_url,
						$update_onclick
					);
				echo '</div>';
				echo '<div id="' . $args['theme_slug'] . '_' . 'changelog" style="display:none;">';
					echo wpautop( $api_response['sections']['changelog'] );
				echo '</div>';
			}
		}
				
		/**
		 * Delete the transient for this theme
		 *
		 */
		function delete_theme_update_transient() {
			
			//Set the defaults and values for $args
			$args = $this->parse_the_args( $this->_args );
			
			delete_site_transient( 'mp_api_request_' . $args['theme_name_slug'] );
		}
		
		/**
		 * Update the transient for this theme
		 *
		 */
		function theme_update_transient($value) {
			
			//Set the defaults and values for $args
			$args = $this->parse_the_args( $this->_args );
			
			$update_data = $this->check_for_update();
			
			//Add the license to the package URL if the license passed in is not NULL - this is now done in the mp_repo plugin
			//$update_data['package'] = $args['software_license'] != NULL ? mp_core_add_query_arg('license', $args['software_license'], $update_data['package'] ) : $update_data['package'];
					
			if ( $update_data ) {
				$value->response[ $args['theme_slug'] ] = $update_data;
			}
			
			return $value;
		}
		
		/**
		 * Check if we should call the API or just return what is stored in the transient for this theme
		 *
		 */
		function check_for_update() {
						
			//Set the defaults and values for $args
			$args = $this->parse_the_args( $this->_args );
			
			//Get the transient where we store the api request for this plugin for 24 hours
			$mp_api_request_transient = get_site_transient( 'mp_api_request_' . $args['theme_name_slug'] );
			
			//If we have no transient, check the API for real
			if ( empty($mp_api_request_transient) ){
				
				//Actually Call the API
				return $this->actually_check_for_update( $args );

			}
			
			//If Force check is set
			if ( isset( $_GET['force-check'] ) ){
				
				//Actually Call the API
				return $this->actually_check_for_update( $args );
				
			}
			
			//If we are on the update-core page 
			if( isset( $this->current_screen->base ) && $this->current_screen->base == 'update-core' ) {
				
				//If we haven't already fetched the API for this theme ON THIS PAGE LOAD
				if ( !isset( $this->api_request ) ){
					
					//Actually Call the API
					return $this->actually_check_for_update( $args );
					
				}
				//If we HAVE already fetched the API ON THIS PAGE LOAD
				else{
					
					//If there is no Theme Update available
					if ( $this->api_request == 'No Theme Update Available' ){
						return false;	
					}
					//If there is a Theme Update available
					else{
						return $this->api_request;
					}
						
				}
				
			}
			
			//If we made it this far, return the transient value 
			
			//If there is no Theme Update available in the Transient Data
			if ( $mp_api_request_transient == 'No Theme Update Available' ){
				return false;	
			}
			//If there IS a Theme Update available in the Transient Data
			else{
				return $mp_api_request_transient;
			}
				
		}
		
		/**
		 * Check for Update for this theme
		 *
		 */
		function actually_check_for_update( $args ){
				
			//If this software is licensed, do checks for updates using the license
			if ( $args['software_licensed'] ){
				
				//Get license		
				$license_key = trim( get_option( $args['theme_name_slug'] . '_license_key' ) );
															
			}
			
			//This isn't a licensed theme
			else{
					
				$license_key = NULL;
				
			}
						
			//This filter can be used to change the API URL. Useful when calling for updates to the API site's plugins which need to be loaded from a separate URL (see mp_repo_mirror)
			$args['software_api_url'] = has_filter( 'mp_core_theme_update_package_url' ) ? apply_filters( 'mp_core_theme_update_package_url', $args['software_api_url'] ) : $args['software_api_url'];														
				
			$failed = false;

			$api_params = array(
				'api' => 'true',
				'slug' => $args['theme_name_slug'],
				'theme' => true,
				'license_key' => $license_key,
				'old_license_key' => get_option( $args['theme_name_slug'] . '_license_key' ),
				'site_activating' => get_bloginfo( 'wpurl' )
			);
							
			$response = wp_remote_post( $args['software_api_url']  . '/repo/' . $args['theme_name_slug'], array( 'method' => 'POST', 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
																								
			// make sure the response was successful
			if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
				$failed = true;
			}
			
			$update_data = json_decode( wp_remote_retrieve_body( $response ) );
			
			//If there is no update data, return false
			if( empty( $update_data ) ){
				return false;	
			}
										
			//temporarily added this so that the url in the transient isn't blank and won't trigger an error - Philj
			if ( isset( $update_data->homepage ) ){
				$update_data->url =  $update_data->homepage;
			}
			else{
				$update_data->url = '';
			}
							
			if ( ! is_object( $update_data ) ) {
				$failed = true;
			}

			// if the response failed, try again in 30 minutes
			if ( $failed ) {
				$data = new stdClass;
				$data->new_version = $args['theme_version'];
				set_site_transient( 'mp_api_request_' . $args['theme_name_slug'], 'No Theme Update Available', strtotime( '+30 minutes' ) ); //Check again in 30 mins
				return false;
			}

			// if the status is 'ok', return the update arguments
			if ( ! $failed ) {
				
				$update_data->sections = maybe_unserialize( $update_data->sections );
				
				//If there's not a new version of the theme
				if( $args['theme_version'] >= $update_data->new_version ){	
				
					//Re Above: We could use version_compare but it doesn't account for beta versions:  if( version_compare( $args['theme_version'], $update_data->new_version, >=' ) ){
					
					$this->api_request = 'No Theme Update Available';
					set_site_transient( 'mp_api_request_' . $args['theme_name_slug'], 'No Theme Update Available', 86400 ); //Check again in 24 hours
					return false;
				}
				//If there is a new version of the theme
				else{
					$this->api_request = (array) $update_data;
					set_site_transient( 'mp_api_request_' . $args['theme_name_slug'], (array) $update_data, 86400 ); //Check again in 24 hours
					return (array) $update_data;
				}
			
			}
					
		}
		
		/**
		 * This function is called on the themes page only
		 */
		function themes_page() {
			
			//Set the defaults and values for $args
			$args = $this->parse_the_args( $this->_args );
			
			add_thickbox();
			add_action( 'admin_notices', array( &$this, 'update_nag' ) );
			
			//If this software is licensed, show license field on plugins page
			if ( $args['software_licensed'] ){
				
				//Set the "Green Light" Notification option for this license					
				$this->set_license_green_light();
				
				//Enqueue scripts for theme page
				add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_themes_scripts' ) );
				
				//Display license on themes page
				add_action( 'admin_notices', array( &$this, 'display_license' ) ); 
			}
			
		}
		
		/**
		 * Function which sets the green light variable to let the user know their license is active
		 */
		function set_license_green_light(){
			
			//Set the defaults and values for $args
			$args = $this->parse_the_args( $this->_args );
			
			//This filter can be used to change the API URL. Useful when calling for updates to the API site's plugins which need to be loaded from a separate URL (see mp_repo_mirror)
			$args['software_api_url'] = has_filter( 'mp_core_theme_update_package_url' ) ? apply_filters( 'mp_core_theme_update_package_url', $args['software_api_url'] ) : $args['software_api_url'];
			
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
		 * Enqueue Jquery on Theme page to place license in correct spot
		 */
		function enqueue_themes_scripts() {
			
			//mp_core_settings_css
			wp_enqueue_style( 'mp_core_settings_css', plugins_url('css/core/mp-core-settings.css', dirname(dirname(__FILE__) ) ) );
			
			//Enqueue style for this license message
			wp_enqueue_style( 'mp-core-licensing-css', plugins_url( 'css/core/mp-core-licensing.css', dirname(dirname(__FILE__) ) ) );			
		}	
		
		/**
		 * Display the license on the themes page
		 */
		function display_license(){
			
			//Set the defaults and values for $args
			$args = $this->parse_the_args( $this->_args );
			
			//This filter can be used to change the API URL. Useful when calling for updates to the API site's plugins which need to be loaded from a separate URL (see mp_repo_mirror)
			$args['software_api_url'] = has_filter( 'mp_core_theme_update_package_url' ) ? apply_filters( 'mp_core_theme_update_package_url', $args['software_api_url'] ) : $args['software_api_url'];
			
			//Get license
			$license_key = get_option( $args['theme_name_slug'] . '_license_key' );
			
			//Api Response
			$api_response = get_site_transient( 'mp_api_request_' . $args['theme_name_slug'] );
			
			//Only verify the license if the transient is older than 7 days
			$check_licenses_transient_time = get_site_transient( 'mp_check_licenses_transient' );
			
			//If our transient is older than 30 days (2592000 seconds)
			if ( time() > ($check_licenses_transient_time + 2592000) ){
				
				//We reset the transient on the plugins page
			
				//Set args to Verfiy the License
				$verify_license_args = array(
					'software_name'      => $args['software_name'],
					'software_api_url'   => $args['software_api_url'],
					'software_license_key'   => $license_key,
					'software_store_license' => true,
				);
				
				//Double check license. Use the Verfiy License class to verify whether this license is valid or not
				mp_core_verify_license( $verify_license_args );	
				
			}
			
			//Get license status (set in verify license class)
			$status = get_option( $args['theme_name_slug'] . '_license_status_valid' );
			
			//Get license link:
			$get_license_link = !empty( $api_response->get_license ) ? '<a href="' . $api_response->get_license . '" target="_blank" >' . __( 'Get License', 'mp_core' ) . '</a>' : NULL;
			
			?>
			<div id="mp-core-theme-license-wrap" class="wrap">
				
				<div class="title"><?php echo $args['software_name'] . ' ' . __('Updates', 'mp_core'); ?></div>
				<p class="theme-description"><?php echo __('Enter your license key to enable auto-updates.'); ?></p>
				
				<form method="post">
									
					<input style="float:left; margin-right:10px;" id="<?php echo $args['theme_name_slug']; ?>_license_key" name="<?php echo $args['theme_name_slug']; ?>_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license_key ); ?>" />						
					<?php mp_core_true_false_light( array( 'value' => $status, 'description' => $status == true ? __('Auto-updates enabled.', 'mp_core') : __('This license is not valid! ', 'mp_core') . $get_license_link ) ); ?>
					
					<?php wp_nonce_field( $args['theme_name_slug'] . '_nonce', $args['theme_name_slug'] . '_nonce' ); ?>
								
					<?php submit_button(__('Submit License', 'mp_core') ); ?>
				
				</form>
			</div>
			<?php
		}
	}
}