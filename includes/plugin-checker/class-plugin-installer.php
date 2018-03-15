<?php
/**
 * This file contains the MP_CORE_Plugin_Installer class
 *
 * @link http://mintplugins.com/doc/plugin-installer-class/
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Classes
 *
 * @copyright  Copyright (c) 2015, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */

/**
 * Plugin Installer Class for the mp_core Plugin by Mint Plugins
 *
 * @author     Philip Johnston
 * @link       http://mintplugins.com/doc/plugin-installer-class/
 * @since      1.0.0
 * @return     void
 */
if ( !class_exists( 'MP_CORE_Plugin_Installer' ) ){
	class MP_CORE_Plugin_Installer{

		/**
		 * Constructor
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      MP_CORE_Plugin_Installer::mp_core_install_plugin_page()
		 * @see      MP_CORE_Plugin_Installer::mp_core_install_plugin()
		 * @see      wp_parse_args()
		 * @param    array $args {
		 *      This array holds information the plugin
		 *		@type string 'plugin_name' Name of plugin.
		 *		@type string 'plugin_message' Message which shows up in notification for plugin.
		 *		@type string 'plugin_filename' Name of plugin's main file
		 * 		@type bool   'plugin_required' Whether or not this plugin is required
		 *		@type string 'plugin_download_link' Link to URL where this plugin's zip file can be downloaded
		 *		@type bool   'plugin_group_install' Whether to create the singular install page for this plugin or not
		 *		@type bool   'plugin_license' The license this plugin requires to be downloaded
		 *		@type bool   'plugin_success_link' Where to re-direct the user upon a sucessful install. No redirect if NULL
		 * }
		 * @return   void
		 */
		public function __construct($args){

			//Set defaults for args
			$defaults = array(
				'plugin_name' => NULL,
				'plugin_message' => NULL,
				'plugin_filename' => NULL,
				'plugin_required' => NULL,
				'plugin_download_link' => NULL,
				'plugin_group_install' => NULL,
				'plugin_license' => NULL,
				'plugin_success_link' => NULL,
				'plugin_is_theme' => false
			);

			//Get and parse args
			$this->_args = wp_parse_args( $args, $defaults );

			//Plugin Name Slug
			$this->plugin_name_slug = sanitize_title ( $this->_args['plugin_name'] ); //EG move-plugins-core

			// Create update/install plugin page
			add_action('admin_menu', array( $this, 'mp_core_install_plugin_page') );

			//If this plugin is part of a group install
			if ( $this->_args['plugin_group_install'] ){

				//Install plugin
				$this->mp_core_install_plugin();

			}

		}

		/**
		 * Create mp core install plugin page
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      get_plugin_page_hookname()
		 * @see      add_action()
	 	 * @return   void
		 */
		public function mp_core_install_plugin_page()
		{

			// This WordPress variable is essential: it stores which admin pages are registered to WordPress
			global $_registered_pages;

			// Get the name of the hook for this plugin
			// We use "options-general.php" as the parent as we want our page to appear under "options-general.php?page=mp_core_install_plugin_page" .  $this->plugin_name_slug
			$hookname = get_plugin_page_hookname('mp_core_install_plugin_page_' .  $this->plugin_name_slug, 'options-general.php');

			// Add the callback via the action on $hookname, so the callback function is called when the page "options-general.php?page=mp_core_install_plugin_page" .  $this->plugin_name_slug is loaded
			if (!empty($hookname)) {
				add_action($hookname, array( $this, 'mp_core_install_check_callback') );
			}

			// Add this page to the registered pages
			$_registered_pages[$hookname] = true;
		}

		/**
		 * Callback function for the update plugin page above.
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      MP_CORE_Plugin_Installer::mp_core_install_plugin()
	 	 * @return   void
		 */
		public function mp_core_install_check_callback() {

			echo '<div class="wrap">';

			echo '<h2>' . __('Install ', 'mp_core') . $this->_args['plugin_name'] . '</h2>';

			//Install plugin
			$this->mp_core_install_plugin();

			echo '</div>';

		}

		/**
		 * Follow all 301 redirects on a URL until we get the actual URL
		 *
		 * @access   public
		 * @since    1.0.0
		 * @return   string - the actual/final URL after all redirects
		 */
		public function get_final_url( $url, $timeout = 5 ) {
			$url = str_replace( "&amp;", "&", urldecode(trim($url)) );

			$cookie = tempnam ("/tmp", "CURLCOOKIE");
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt( $ch, CURLOPT_ENCODING, "" );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
			curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
			curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
			$content = curl_exec( $ch );
			$response = curl_getinfo( $ch );
			curl_close ( $ch );

			if ($response['http_code'] == 301 || $response['http_code'] == 302) {
				ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
				$headers = get_headers($response['url']);

				$location = "";
				foreach( $headers as $value ){
					if ( substr( strtolower($value), 0, 9 ) == "location:" )
					return $this->get_final_url( trim( substr( $value, 9, strlen($value) ) ) );
				}
			}

			if ( preg_match("/window\.location\.replace\('(.*)'\)/i", $content, $value) || preg_match("/window\.location\=\"(.*)\"/i", $content, $value) ) {
				return $this->get_final_url ( $value[1] );
			} else {
				return $response['url'];
			}
		}


		/**
		 * Callback function for the update plugin page above. This page uses the filesystem api to install a plugin
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      get_option()
		 * @see      wp_remote_post()
		 * @see      is_wp_error()
		 * @see      wp_remote_retrieve_response_code()
		 * @see      wp_remote_retrieve_body()
		 * @see      current_user_can()
		 * @see      wp_verify_nonce()
		 * @see      wp_nonce_url()
		 * @see      WP_Filesystem
		 * @see      WP_Filesystem::wp_plugins_dir()
		 * @see      request_filesystem_credentials()
		 * @see      trailingslashit()
		 * @see      unzip_file()
	 	 * @see      wp_cache_set()
		 * @see      activate_plugin()
		 * @return   void
		 */
		public function mp_core_install_plugin() {

			//If this product is licensed
			if ( !empty( $this->_args['plugin_licensed'] ) && $this->_args['plugin_licensed'] ){

				//get validity of license saved
				$license_valid = get_option( $this->plugin_name_slug . '_license_status_valid' );

				//if license saved is incorrrect
				if ( !$license_valid ) {

					//output incorrect license message
					echo "The license entered is not valid";

					//output form to try license

					//stop the rest of this page from showing
					return true;

				}

				$api_params = array(
					'api' => 'true',
					'slug' => $this->plugin_name_slug,
					'author' => NULL, //$this->_args['software_version'] - not working for some reason
					'license_key' => $this->_args['plugin_license'],
					'old_license_key' => get_option( $this->plugin_name_slug . '_license_key' ),
					'site_activating' => get_bloginfo( 'wpurl' )
				);

				$request = wp_remote_post( $this->_args['plugin_api_url']  . '/repo/' . $this->plugin_name_slug, array( 'method' => 'POST', 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

				// make sure the response was successful
				if ( is_wp_error( $request ) || 200 != wp_remote_retrieve_response_code( $request ) ) {
					$failed = true;
				}

				//JSON Decode response and store the plugin download link in $this->_args['plugin_download_link']
				$request = json_decode( wp_remote_retrieve_body( $request ) );

				//Set the plugin download link to be the package URL from the response
				$this->_args['plugin_download_link'] = $request->package;


			}

			//Make sure this user has the cpability to install plugins:
			if (!current_user_can('install_plugins')){ die('<p>' . __('You don\'t have permission to do this. Contact the system administrator for assistance.', 'mp_core') . '</p>'); }

			//Make sure the action is set to install-plugin
			if ($_GET['action'] != 'install-plugin'){ die('<p>' . __('Oops! Something went wrong', 'mp_core') . '</p>'); }

			//Get the nonce previously set
			$nonce=$_REQUEST['_wpnonce'];

			//Check that nonce to ensure the user wants to do this
			if (! wp_verify_nonce($nonce, 'install-plugin' ) ) die('<p>' . __('Security Check', 'mp_core') . '</p>');

			//Set the method for the wp filesystem
			$method = ''; // Normally you leave this an empty string and it figures it out by itself, but you can override the filesystem method here

			//Get credentials for wp filesystem
			$url = wp_nonce_url('options-general.php?page=mp_core_install_plugin_page_' .  $this->plugin_name_slug . '&action=install-plugin&plugin=' . $this->plugin_name_slug, 'install-plugin_' . $this->plugin_name_slug );
			if (false === ($creds = request_filesystem_credentials($url, $method, false, false) ) ) {

				// if we get here, then we don't have credentials yet,
				// but have just produced a form for the user to fill in,
				// so stop processing for now

				return true; // stop the normal page form from displaying
			}

			//Now we have some credentials, try to get the wp_filesystem running
			if ( ! WP_Filesystem($creds) ) {
				// our credentials were no good, ask the user for them again
				request_filesystem_credentials($url, $method, true, false);
				return true;
			}

			//By this point, the $wp_filesystem global should be working, so let's use it get our plugin
			global $wp_filesystem;

			//If we are installing a theme
			if ( $this->_args['plugin_is_theme'] ){

				//Get the plugins directory and name the temp plugin file
				$upload_dir = $wp_filesystem->wp_themes_dir();
			}
			//If we are installing a plugin
			else{
				//Get the plugins directory and name the temp plugin file
				$upload_dir = $wp_filesystem->wp_plugins_dir();
			}
			$filename = trailingslashit($upload_dir).'temp.zip';

			// If this is a local plugin/theme that just needs to be moved from one directory to another
			if ( 'local' == $this->_args['plugin_api_url'] ){

				$copy_from = $this->_args['plugin_download_link'];
				$copy_to = $wp_filesystem->wp_themes_dir();

				copy_dir( $copy_from, $copy_to );

				if ( isset( $this->_args['plugin_is_child_theme'] ) && $this->_args['plugin_is_child_theme'] ) {
					// In this case, we are moving a child theme from inside a theme-bundle plugin to the themes directory,
					// lets make it use the screenshot from the parent plugin
					$copy_from = dirname( dirname( $this->_args['plugin_download_link'] ) ) . '/screenshot.jpg';
					$copy_to = $wp_filesystem->wp_themes_dir() . $this->_args['plugin_dashed_slug'] . '/screenshot.jpg';

					if ( ! $wp_filesystem->copy( $copy_from, $copy_to, true, FS_CHMOD_FILE) ) {
						// If copy failed, chmod file to 0644 and try again.
						$wp_filesystem->chmod( $copy_to, FS_CHMOD_FILE );
						if ( ! $wp_filesystem->copy( $copy_from, $copy_to, true, FS_CHMOD_FILE) ) {
							// Unable to copy the screenshot, so skip it
						}

					}

				}

			} else {

				//if 'allow_url_fopen' is available, do it the right way using the WP Filesystem api
				if( ini_get('allow_url_fopen') ) {

					//Download the plugin file defined in the passed in array
					$saved_file = $wp_filesystem->get_contents( esc_url_raw( add_query_arg( array( 'site_activating' => get_bloginfo( 'wpurl' ) ), $this->_args['plugin_download_link'] ) ) );

					//If the file the came back was blank, try getting it another way.
					if ( empty( $saved_file ) ){
						$saved_file = wp_remote_retrieve_body( wp_remote_get( esc_url_raw( add_query_arg( array( 'site_activating' => get_bloginfo( 'wpurl' ) ), $this->_args['plugin_download_link'] ) ) ) );
					}

					//If the file still came back empty, try without using SSL
					if ( empty( $saved_file ) ){
						$plugin_download_link = str_replace( 'https', 'http', $this->_args['plugin_download_link'] );
						$saved_file = wp_remote_retrieve_body( wp_remote_get( esc_url_raw( add_query_arg( array( 'site_activating' => get_bloginfo( 'wpurl' ) ), $plugin_download_link ) ) ) );

						echo __( 'Oops! Your Web Host is poorly configured and doesn\'t allow secure connections over SSH!! Let them know they need to allow connections over SSH in order for WordPress to properly function. This can sometimes be caused by an out-dated version of OpenSSL.', 'mp_core' );

						die();
					}

					//If it's still empty
					if ( empty( $saved_file ) ){
						echo __( 'Oops! There was an error downloading the file', 'mp_core' );
						echo '<br />' . __( 'The URL to the file is: ', 'mp_core' ) . $saved_file;
						echo esc_url_raw( add_query_arg( array( 'site_activating' => get_bloginfo( 'wpurl' ) ), $this->_args['plugin_download_link'] ) );
						die();
					}

					//Save the contents into a temp.zip file (string stored in $filename)
					if ( !$wp_filesystem->put_contents( $filename, $saved_file, FS_CHMOD_FILE) ){

						//If the file was unable to be created, output an error and die
						echo __( 'Oops! The plugin file was unable to be created. Check with your webhost to see if the "wp-content" directory is "Writable".', 'mp_core' );
						die();

					}

				}
				//For people with poor/bad server configurations which don't have access to allow_url_fopen, try using curl
				else{

					$url_to_download = $this->get_final_url( $this->_args['plugin_download_link'] );

					// Initializing curl
					$ch = curl_init();

					//Return Transfer
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

					//File to fetch
					curl_setopt($ch, CURLOPT_URL, $url_to_download );

					//Open/Create new file
					$file = fopen($upload_dir . "temp.zip", 'w');

					//Put contents of plugin_download_link in this new file
					curl_setopt($ch, CURLOPT_FILE, $file ); #output

					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects

					//Set User Agent
					curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5'); //set user agent

					// Getting results
					$result =  curl_exec($ch); // Getting jSON result string

					curl_close($ch);

					fclose($file);

					//If we are unable to find the file, let the user know. This will also fail if a license is incorrect - but it should be caught further up the page
					if ( ! $result ) {

						die();

					}

				}
			}

			//Default unzipping to true
			$needs_unzipping = ! isset( $this->_args['plugin_needs_unzipping'] ) ? true : $this->_args['plugin_needs_unzipping'];

			if ( $needs_unzipping ) {
				//Unzip the temp zip file
				$unzip_result = unzip_file($filename, trailingslashit($upload_dir) . '/' );

				//If there was a problem unzipping the file
				if ( is_wp_error( $unzip_result ) ) {

					$zip = new ZipArchive;
					if ($zip->open($filename) === TRUE) {
						$zip->extractTo(trailingslashit($upload_dir) );
						$zip->close();
					} else {

						echo '<p>' . __( 'Error Unzipping ', 'mp_core' ) .  $this->_args['plugin_name']  . '</p>';

						//If the file was unable to be unzipped, it's likely this webhost has a strange temp directory - where wordpress stores files that are being unzipped.
						echo '<p>' . __( 'Your Web Host appears to have an improperly configured WordPress "temp" directory.', 'mp_core' ) . '</p>';

						echo '<p>' . __( 'The WordPress "temp" directory appears to be set to: ', 'mp_core' ) . '<strong>"' . get_temp_dir() . '"</strong> ' . __( 'and is preventing files from being properly unzipped by WordPress', 'mp_core' ) . '</p>';

						echo '<p>' . __( 'The actual error from PHP is: ', 'mp_core' ) . '</p><p>';
						print_r( $unzip_result ) . '</p>';

						die();
					}

				}

				//Delete the temp zipped file
				$wp_filesystem->rmdir($filename);
			}

			//If we are installing a theme
			if ( $this->_args['plugin_is_theme'] ){

				//Set themes cache to NULL so wp_get_themes will get the new theme we just installed
				wp_clean_themes_cache( true );

				$installed_themes = wp_get_themes();

				//Loop through each installed theme
				foreach( $installed_themes as $theme_slug => $theme ){

					echo $theme['headers:WP_Theme:private']['Name'];
					echo $theme['plugin_name'];

					//If this theme is the theme we're hoping to install
					if ( $theme['headers:WP_Theme:private']['Name'] == $theme['plugin_name'] ){

						//Switch to the theme we just installed
						switch_theme( $theme_slug );

						//Stop looping
						break;

					}

				}

				//Display a successfully installed message
				echo '<p>' . __( 'Successfully Installed ', 'mp_core' ) .  $this->_args['plugin_name']  . '</p>';

			}
			//If we are installing a plugin
			else{

				//Set plugin cache to NULL so activate_plugin->validate_plugin->get_plugins will check again for new plugins
				wp_cache_set( 'plugins', NULL, 'plugins' );

				//Activate plugin
				$result = activate_plugin( trailingslashit( $upload_dir ) . $this->plugin_name_slug . '/' . $this->_args['plugin_filename'] );

				//If there was a problem installing the plugin
				if ( is_wp_error( $result ) ) {
					//Display an error message
					echo '<p>' . __( 'Error Installing ', 'mp_core' ) .  $this->_args['plugin_name']  . '</p>';
					echo '<p>' . $result->get_error_message() . '</p>';
				}
				//If we activated the plugin and it's all good
				else{
					//Display a successfully installed message
					echo '<p>' . __( 'Successfully Installed ', 'mp_core' ) .  $this->_args['plugin_name']  . '</p>';
				}
			}

			if ( !empty( $this->_args['plugin_success_link'] ) ){
				//Javascript for redirection
				echo '<script type="text/javascript">';
					echo "window.location = '" . $this->_args['plugin_success_link'] . "';";
				echo '</script>';

				echo '</div>';
			}

		}

	}
}
