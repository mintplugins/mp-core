<?php
/**
 * This file contains the MP_CORE_Theme_Updater class which tehemes can use to keep themselves up to date
 *
 * @link http://moveplugins.com/doc/theme-updater-class/
 * @since 1.0.0
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
 * @link       http://moveplugins.com/doc/theme-updater-class/
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
		 * @link     http://moveplugins.com/doc/plugin-updater-class/
		 * @author   Philip Johnston
		 * @see      MP_CORE_Plugin_Updater::set_license_green_light()
		 * @see      MP_CORE_Plugin_Updater::themes_page()
		 * @see      MP_CORE_Plugin_Updater::mp_core_update_theme()
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
				'software_licensed' => NULL
			);
			
			//Get and parse args
			$this->_args = wp_parse_args( $args, $args_defaults );
			
			//Theme Data
			$this->theme_slug = sanitize_title ( get_template() ); //EG knapstack (directory name)
			$this->theme_name_slug = sanitize_title ( $this->_args['software_name']  ); //EG knapstack-theme (Name of theme in style.css)
			$theme = wp_get_theme( $this->theme_slug );
			$this->version = ! empty( $version ) ? $version : $theme->get( 'Version' );
			
			//If this software is licensed, show license field on plugins page
			if ( $this->_args['software_licensed'] ){
				
				//Set the "Green Light" Notification option for this license		
				add_action( 'admin_init', array( &$this, 'set_license_green_light' ) );
				
				//Show Option Page on Themes page as well
				add_action( 'load-themes.php', array( $this, 'themes_page') );  
			
			}	
			
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
			
			//Theme Update Function	
			//add_action( 'admin_init', array( &$this, 'mp_core_update_theme' ) ); 	
						
						
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
			
			
			//We could use version_compare here but it doesn't account for beta mode: if ( version_compare( $this->version, $api_response->new_version, '<' ) ) {	
			if( $this->version < $api_response->new_version ){	
	
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
			
			//Add the license to the package URL if the license passed in is not NULL - this is now done in the mp_repo plugin
			//$update_data['package'] = $this->_args['software_license'] != NULL ? add_query_arg('license', $this->_args['software_license'], $update_data['package'] ) : $update_data['package'];
					
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
			
			//If this software is licensed, do checks for updates using the license
			if ( $this->_args['software_licensed'] ){
				
				//Get license		
				$license_key = trim( get_option( $this->theme_name_slug . '_license_key' ) );
															
			}
			
			//This isn't a licensed theme
			else{
					
				$license_key = NULL;
				
			}
			
			//This filter can be used to change the API URL. Useful when calling for updates to the API site's plugins which need to be loaded from a separate URL (see mp_repo_mirror)
			$this->_args['software_api_url'] = has_filter( 'mp_core_theme_update_package_url' ) ? apply_filters( 'mp_core_theme_update_package_url', $this->_args['software_api_url'] ) : $this->_args['software_api_url'];
										
			$theme = wp_get_theme( $this->theme_slug );
								
			$update_data = get_transient( $this->response_key ); //malachi-update-response
				
			if ( false == $update_data ) {
				
				$failed = false;
	
				$api_params = array(
					'api' => 'true',
					'slug' => $this->theme_name_slug,
					'theme' => true,
					'license_key' => $license_key
				);
								
				$response = wp_remote_post( $this->_args['software_api_url']  . '/repo/' . $this->theme_name_slug, array( 'method' => 'POST', 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
																			
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
			
			//We could use version_compare but it doesn't account for beta versions:  if( version_compare( $this->version, $update_data->new_version, >=' ) ){
			if( $this->version >= $update_data->new_version ){				
				return false;
			}
			
			return (array) $update_data;
			
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
		 * This function is called on the themes page only
		 */
		function themes_page() {
			
			//Enqueue scripts for theme page
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_themes_scripts' ) );
			
			//Display license on themes page
			add_action( 'admin_notices', array( &$this, 'display_license' ) ); 
		}
		
		/**
		 * Enqueue Jquery on Theme page to place license in correct spot
		 */
		function enqueue_themes_scripts() {
			//Enqueue style for this license message
			wp_enqueue_style( 'mp-core-licensing-css', plugins_url( 'css/core/mp-core-licensing.css', dirname(dirname(__FILE__) ) ) );			
			wp_enqueue_script( 'mp-core-themes-placement', plugins_url( 'js/themes-page.js', dirname(__FILE__) ),  array( 'jquery' ) );		
		}	
		
		/**
		 * Display the license on the themes page
		 */
		function display_license(){
			
			//Get license
			$license_key = get_option( $this->theme_name_slug . '_license_key' );
			
			//Set args to Verfiy the License
			$verify_license_args = array(
				'software_name'      => $this->_args['software_name'],
				'software_api_url'   => $this->_args['software_api_url'],
				'software_license_key'   => $license_key
			);
			
			//Double check license. Use the Verfiy License class to verify whether this license is valid or not
			new MP_CORE_Verify_License( $verify_license_args );	
			
			//Get license status (set in verify license class)
			$status = get_option( $this->theme_name_slug . '_license_status_valid' );
			
			?>
			<div id="mp-core-theme-license-wrap" class="wrap">
				
				<strong><?php echo __('Updates', 'mp_core'); ?></strong><br />
				<p class="theme-description"><?php echo __('Enter your license key to enable automatic updates'); ?></p>
				
				<form method="post">
									
					<input style="float:left; margin-right:10px;" id="<?php echo $this->theme_name_slug; ?>_license_key" name="<?php echo $this->theme_name_slug; ?>_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license_key ); ?>" />						
					<?php mp_core_true_false_light( array( 'value' => $status, 'description' => $status == true ? __('License is valid', 'mp_core') : __('This license is not valid!', 'mp_core') ) ); ?>
					
					<?php wp_nonce_field( $this->theme_name_slug . '_nonce', $this->theme_name_slug . '_nonce' ); ?>
								
					<?php submit_button(__('Submit License', 'mp_core') ); ?>
				
				</form>
			</div>
			<?php
		}
	}
}