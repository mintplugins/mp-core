<?php
/**
 * This file contains the MP_CORE_Licensed_Parent_Plugin_Installation_Routine class
 *
 * @link http://mintplugins.com/doc/MP_CORE_Licensed_Parent_Plugin_Installation_Routine/
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Classes
 *
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */
 
//Set up our Global Options for MP Stacks
mp_core_global_options_init();

/**
 * Set up the global $mp_core_options
 *
 * @since 1.0
 * @global $wpdb
 * @global $mp_core_options
 * @return void
 */
function mp_core_global_options_init(){
	
	global $mp_core_options;
	
	$mp_core_options = get_option('mp_core_options');
		
}

/**
 * This class handles the setup for  Stack Pack for MP Stacks. Set it up in the plugin activation hook for the Stack Pack. 
 * 
 * The field can be singular or they can repeat in groups. 
 * It works by passing an associative array containing the information for the fields to the class
 *
 * @author     Philip Johnston
 * @link       http://mintplugins.com/doc/metabox-class/
 * @since      1.0.0
 * @return     void
 */
class MP_CORE_Licensed_Parent_Plugin_Installation_Routine{
				
	protected $_parent_plugin_title;
	protected $_metabox_items_array = array();
	
	/**
	 * Constructor
	 *
	 * @access   public
	 * @since    1.0.0
	 * @link     http://mintplugins.com/doc/MP_CORE_Licensed_Parent_Plugin_Installation_Routine/
	 * @author   Philip Johnston
	 * @see      sanitize_title()
	 * @param    string $full_parent_plugin_title (required) See link for description.
	 * @return   void
	 */	
	public function __construct( $full_parent_plugin_title, $plugin_api_url ){
					
		//Set class wide parent plugin title
		$this->_parent_plugin_title = $full_parent_plugin_title;	
		
		//Set class wide parent plugin slug using hyphens as separators
		$this->_full_parent_plugin_hyphen_slug = sanitize_title( $full_parent_plugin_title );	
		
		//Set class wide parent plugin slug using underscores as separators
		$this->_full_parent_plugin_underscore_slug = str_replace("-", "_", $this->_full_parent_plugin_hyphen_slug );	
		
		$this->_parent_plugin_api_url = $plugin_api_url;
												
		//Set up hooked functions
		add_action( 'admin_init', array( $this, 'license_capture_upon_activation' ) );
		add_action( 'admin_footer', array( $this, 'footer_redirects_after_dependant_installs' ) );
		add_action( 'shutdown', array( $this, 'redirect_upon_activation' ) );
								
	}

	/**
	 * Redirects to installation of dependencies, saves Theme MetaData.
	 *
	 * @since 1.0
	 * @global $wpdb
	 * @global $mp_core_options
	 * @return void
	 */
	function redirect_upon_activation(){
		
		global $mp_core_options;
				
		//If we have just activated
		if ( $mp_core_options['parent_plugin_activation_status'] == 'just_activated' ){
						
			// Bail if activating from network, or bulk
			if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
				
				//Flush the rewrite rules
				flush_rewrite_rules();
				
				//Tell the mp_core_options that we no longer just activated so no redirects happen.
				$mp_core_options['parent_plugin_activation_status'] = 'cancelled';	
				//Save our mp_core_options - since we've just activated and changed some of them
				update_option( 'mp_core_options', $mp_core_options );
			
				return;
			}
			
			//If the core is NOT active (and we aren't installing the core right now), redirect the core installation
			if ( !function_exists('mp_core_textdomain') ){
				
				//Tell the mp_core_options that we are activating the core
				$mp_core_options['parent_plugin_activation_status'] = 'installing_core';	
				//Save our mp_core_options - since we've just activated and changed some of them
				update_option( 'mp_core_options', $mp_core_options );
			
				//Redirect to install the core
				wp_redirect( admin_url( sprintf( 'options-general.php?page=mp_core_install_plugins_page&action=install-plugin&_wpnonce=%s', wp_create_nonce( 'install-plugin' ) ) ) );	
				exit();
				
			}
			
			//If we made it this far, the core is active
			
			//Set up the name of the function in the parent plugin where we check if all dependant plugins are installed
			$dependency_function_name = $this->_full_parent_plugin_underscore_slug . '_dependencies';
			
			//If all required plugins are active, redirect to the welcome page
			if ( $dependency_function_name() ){
				
				$mp_core_options['parent_plugin_activation_status'] = 'complete';	
			
				//Save our mp_core_options - since we've just activated and changed some of them
				update_option( 'mp_core_options', $mp_core_options );
			
				// Redirect the user to our welcome page - or other page if an add-on filters this redirect
				wp_redirect( admin_url() . '?page=' . $this->_full_parent_plugin_hyphen_slug  . '-welcome' );
				exit();
			}
			//If all required plugins are NOT active, redirect to the mp-core intaller and install any other needed plugins too.
			else{
				
				$mp_core_options['parent_plugin_activation_status'] = 'installing_dependencies';	
			
				//Save our mp_core_options - since we've just activated and changed some of them
				update_option( 'mp_core_options', $mp_core_options );
				
				wp_redirect( admin_url( sprintf( 'options-general.php?page=mp_core_install_plugins_page&action=install-plugin&_wpnonce=%s', wp_create_nonce( 'install-plugin' ) ) ) );	
				exit();
			}
			
		}
		
	}
	
	
	
	//If no Stack Pack license exists, Gets Stack Pack License,
	function license_capture_upon_activation(){
		
		global $wpdb, $mp_core_options, $wp_version;
		
		//If the user just clicked cancel on the license actication
		if ( isset( $_GET['mp-core-parent-plugin-license-cancelled'] ) ){	
			
			//Tell the mp_core_options that we no longer just activated so no redirects happen.
			$mp_core_options['parent_plugin_activation_status'] = 'cancelled';	
			//Save our mp_core_options - since we've just activated and changed some of them
			update_option( 'mp_core_options', $mp_core_options );
			
			return false;
		}
					
		//Only keep going if we are supposed to be getting a license and the core is active
		if ( $mp_core_options['parent_plugin_activation_status'] == 'getting_license' && function_exists( 'mp_core_textdomain' ) ){
		
			//Check the validity of the license for this plugin (boolean)
			$plugin_vars = array(
				'plugin_name' => $this->_parent_plugin_title,
				'plugin_api_url' => $this->_parent_plugin_api_url,
			);	
		
			$license_key_valid = mp_core_listen_for_license_and_get_validity( $plugin_vars );
			$license_key = get_option( $this->_full_parent_plugin_hyphen_slug . '_license_key' );	 
			
			//If there isn't a valid license key, Make it so the license input form is all the user sees
			if ( !$license_key_valid ){
						
				?>
				<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US"><head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
					<title><?php echo !$license_key_valid && !empty( $license_key ) ? __( 'Invalid License', 'mp_core' ) : __( 'Install Stack Pack', 'mp_core' ); ?></title>
					<style type="text/css">
						html {
							background: #f1f1f1;
						}
						body {
							background: #fff;
							color: #444;
							font-family: "Open Sans", sans-serif;
							margin: 2em auto;
							padding: 1em 2em;
							max-width: 700px;
							-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
							box-shadow: 0 1px 3px rgba(0,0,0,0.13);
						}
						h1 {
							border-bottom: 1px solid #dadada;
							clear: both;
							color: #666;
							font: 24px "Open Sans", sans-serif;
							margin: 30px 0 0 0;
							padding: 0;
							padding-bottom: 7px;
						}
						#error-page {
							margin-top: 50px;
						}
						#error-page p {
							font-size: 14px;
							line-height: 1.5;
							margin: 25px 0 20px;
						}
						#error-page code {
							font-family: Consolas, Monaco, monospace;
						}
						ul li {
							margin-bottom: 10px;
							font-size: 14px ;
						}
						a {
							color: #21759B;
							text-decoration: none;
						}
						a:hover {
							color: #D54E21;
						}
						.button {
							background: #f7f7f7;
							border: 1px solid #cccccc;
							color: #555;
							display: inline-block;
							text-decoration: none;
							font-size: 13px;
							line-height: 26px;
							height: 28px;
							margin: 0;
							padding: 0 10px 1px;
							cursor: pointer;
							-webkit-border-radius: 3px;
							-webkit-appearance: none;
							border-radius: 3px;
							white-space: nowrap;
							-webkit-box-sizing: border-box;
							-moz-box-sizing:    border-box;
							box-sizing:         border-box;
				
							-webkit-box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0,0,0,.08);
							box-shadow: inset 0 1px 0 #fff, 0 1px 0 rgba(0,0,0,.08);
							vertical-align: top;
						}
				
						.button.button-large {
							height: 29px;
							line-height: 28px;
							padding: 0 12px;
						}
				
						.button:hover,
						.button:focus {
							background: #fafafa;
							border-color: #999;
							color: #222;
						}
				
						.button:focus  {
							-webkit-box-shadow: 1px 1px 1px rgba(0,0,0,.2);
							box-shadow: 1px 1px 1px rgba(0,0,0,.2);
						}
				
						.button:active {
							background: #eee;
							border-color: #999;
							color: #333;
							-webkit-box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
							box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
						}
						input{
							
							border: 1px solid #ddd;
							-webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
							box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
							background-color: #fff;
							color: #333;
							outline: 0;
							-webkit-transition: .05s border-color ease-in-out;
							transition: .05s border-color ease-in-out;
							padding: 5px 10px;
							font-size: 1em;
							outline: 0;
							width:100%;
								
						}
				
							</style>
				<style type="text/css"></style></head>
				<body id="error-page">
					<p><h2>
					<?php 
						//If the license is invalid
						if ( !$license_key_valid && !empty( $license_key ) ){
								echo __( 'Invalid License for ', 'mp_core' ) . '<br />' . $this->_parent_plugin_title . '...';
						}else{
								echo __( 'Enter your license key to complete installation of ', 'mp_core' ) . '<br />' . $this->_parent_plugin_title . '...'; 
						}
						?>
					</h2></p>
					
					<form id="<?php echo $this->_full_parent_plugin_underscore_slug; ?>_license" action="<?php echo admin_url(); ?>" method="post">
						
						<input name="<?php echo $this->_full_parent_plugin_hyphen_slug; ?>_license_key" style="margin-bottom:10px;" placeholder="<?php echo !$license_key_valid && !empty( $license_key )  ? __( 'Oops! The License Key you entered isn\'t valid', 'mp_core' ) : __( 'Enter your License Key for', 'mp_core' ) . ' ' . $this->_parent_plugin_title; ?> " value="" />
					   
						<input name="submit" type="submit" id="submit" class="button" style="width:initial; float:left; display:inline-block; margin-right:5px;" value="<?php echo __( 'Complete Installation', 'mp_core' ); ?>">
					   
					   <?php echo wp_nonce_field( $this->_full_parent_plugin_hyphen_slug  . '_nonce', $this->_full_parent_plugin_hyphen_slug  . '_nonce' ); ?>
					   
					   <a href="<?php echo add_query_arg( array( 'mp-core-parent-plugin-license-cancelled' => true ), admin_url() ); ?>" class="button"><?php echo __( 'Cancel', 'mp_core' ); ?></a>
					</form>
								
					<p><?php echo __( 'Lost your License Key? Log into your account at', 'mp_core' ); ?> <a href="<?php echo $this->_parent_plugin_api_url; ?>" target="_blank"><?php echo $this->_parent_plugin_api_url; ?></a></p>
					
				</body></html>
				
				<?php 
				die();
			}
			
			//If a valid license was just activated from the parent plugin license-only page
			else{
					
				//Set up the name of the function in the parent plugin where we check if all dependant plugins are installed
				$dependency_function_name = $this->_full_parent_plugin_underscore_slug . '_dependencies';
														
				//If all required plugins are active, redirect to the welcome page
				if ( $dependency_function_name() ){
					
					$mp_core_options['parent_plugin_activation_status'] = 'complete';	
				
					//Save our mp_core_options - since we've just activated and changed some of them
					update_option( 'mp_core_options', $mp_core_options );
				
					// Redirect the user to our welcome page - or other page if an add-on filters this redirect
					wp_redirect( admin_url() . '?page=' . $this->_full_parent_plugin_hyphen_slug  . '-welcome' );
					exit();
				}
				//If all required plugins are NOT active, redirect to the mp-core intaller and install any other needed plugins too.
				else{
					
					$mp_core_options['parent_plugin_activation_status'] = 'installing_dependencies';	
				
					//Save our mp_core_options - since we've just activated and changed some of them
					update_option( 'mp_core_options', $mp_core_options );
					
					wp_redirect( admin_url( sprintf( 'options-general.php?page=mp_core_install_plugins_page&action=install-plugin&_wpnonce=%s', wp_create_nonce( 'install-plugin' ) ) ) );	
					exit();
				}
				
			}
		}
	}
	
	/**
	 * This function fires in the footer to set redirects after installations of dependencies
	 *
	 * @since 1.0
	 * @global $mp_core_options
	 * @return void
	 */
	function footer_redirects_after_dependant_installs(){
		global $mp_core_options;
		
		//If we are installing dependant plugins, once they are all installed tell parent_plugin_activation_status that we are complete
		if( $mp_core_options['parent_plugin_activation_status'] == 'installing_dependencies' ){
				
			//Flush the rewrite rules
			flush_rewrite_rules();
			
			//Tell the mp_core_options that we no longer just activated
			$mp_core_options['parent_plugin_activation_status'] = 'complete';	
				
			//Save our mp_core_options - since we've just activated and changed some of them
			update_option( 'mp_core_options', $mp_core_options );
			
			// Redirect the user to Stack Pack's Welcome Page
			echo '<script type="text/javascript">';
				echo "window.location = '" . admin_url() . '?parent_plugin_welcome' . "';";
			echo '</script>';
			
		}
		
		
		//If we are currently installing the core, redirect to the license only page when complete
		if( $mp_core_options['parent_plugin_activation_status'] == 'installing_core' ){
			
			//If we were redirected to install mp-core and other required plugins
			if ( isset( $_GET['page'] ) && $_GET['page'] == 'mp_core_install_plugins_page' ){
				
				$mp_core_options['parent_plugin_activation_status'] = 'getting_license';
				//Save our mp_core_options - since we've just activated and changed some of them
				update_option( 'mp_core_options', $mp_core_options );
						
				// Redirect the user to the single license page after MP Core has been installed
				echo '<script type="text/javascript">';
					echo "window.location = '" . admin_url() . "';";
				echo '</script>';
				
				echo '</div>';
					
					
			}	
		}
	}
}