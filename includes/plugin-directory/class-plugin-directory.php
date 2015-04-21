<?php
/**
 * This file contains the MP_CORE_Plugin_Directory class 
 *
 * @link http://mintplugins.com/doc/plugin-directory-class/
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
 * Plugin Directory Class for the MP Core Plugin by Mint Plugins.
 * 
 * This class facilitates the creation of directory pages in WordPress containing plugins to install.
 *
 * @author     Philip Johnston
 * @link       http://mintplugins.com/doc/plugin-directory-class/
 * @since      1.0.0
 * @return     void
 */
if ( !class_exists( 'MP_CORE_Plugin_Directory' ) ){
	class MP_CORE_Plugin_Directory{
		
		/**
		 * Constructor
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      MP_CORE_Plugin_Directory::enqueue_scripts()
		 * @see      MP_CORE_Plugin_Directory::add_submenu_page()
		 * @see      wp_parse_args()
		 * @see      add_action()
		 * @param    array $args {
		 *      This array contains info for creating the directory page
		 *		@type string 'parent_slug' The slug name for the parent menu (or the file name of a standard WordPress admin page)
		 *		@type string 'page_title' The title of the directory page.
		 *		@type string 'slug' The slug for this directory. Make this an original, unspaced string.
		 *		@type string 'directory_list_urls' Link to URL where the API is set to to handle the directory. See MP Repo Plugin.
		 * }
		 * @return   void
		 */
		public function __construct($args){
						
			//Set defaults for args		
			$defaults = array(
				'parent_slug' => NULL,
				'menu_title' => NULL,
				'page_title' => NULL,
				'slug' => NULL,
				'limit_search_to_repo_group_slug' => NULL,
				'directory_list_urls' => NULL,
			);
			
			//Get and parse args
			$this->_args = wp_parse_args( $args, $defaults );
						
			//Make sure we are on the directory page
			$this->_page = isset($_GET['page']) ? $_GET['page'] : NULL;
			$this->_mp_page_source = isset($_GET['mp-source']) ? $_GET['mp-source'] : NULL;
			$this->_mp_directory_tab = isset($_GET['mp_core_directory_tab']) ? $_GET['mp_core_directory_tab'] : NULL;
			$this->_mp_directory_paged = isset($_GET['mp_core_directory_paged']) ? $_GET['mp_core_directory_paged'] : 1;
			$this->_plugin_success_link = mp_core_add_query_arg( array('page' => $this->_args['slug'], 'mp_core_directory_tab' => $this->_mp_directory_tab ), admin_url('admin.php') );
			
			//If we are on the install page for this plugin
			if ( strpos( $this->_page, 'mp_core_install_plugin_page_' ) !== false && $this->_mp_page_source == 'mp_core_directory' ){
				
				//Set up the plugin installation
				$this->single_plugin_installation_via_directory( isset( $_GET['plugin_api_url']) ? base64_decode( $_GET['plugin_api_url'] ) : NULL, isset($_GET['plugin']) ? $_GET['plugin'] : NULL );
				
			}
			
			//If we are on the directory page 
			else if ( $this->_page  == $this->_args['slug'] || $this->_mp_page_source == $this->_args['slug'] ) {
				
				if ( is_array( $this->_args['directory_list_urls'] ) ){
					
					//If no tab has been entered in the URL, show the first directory list url in the array
					if ( !$this->_mp_directory_tab ){
						
						//Get the first Directory list URL in the array
						$first_directory_list_url = reset($this->_args['directory_list_urls']);
						
						$this->_mp_directory_tab = key($this->_args['directory_list_urls']);
						
						//Get list of Plugins to show, Listen for Licenses, And if we are on an Installation page, Create install page for that plugin		
						$this->setup_functions( $first_directory_list_url['directory_list_url'] );
						
					}
					//If we are on the search tab
					elseif ( $this->_mp_directory_tab == 'search' ){
						
						//Get list of Plugins to show, Listen for Licenses, And if we are on an Installation page, Create install page for that plugin		
						$this->setup_functions( mp_core_add_query_arg( array( 
							'limit_to_repo_group' => $this->_args['limit_search_to_repo_group_slug'], 
							's' => isset( $_GET['search']  ) ? $_GET['search'] : NULL
						), $this->_args['search_api_url'] ) );
						
					}
					//If a tab has been entered in the URL
					else{
							
						//Loop through each directory list url
						foreach( $this->_args['directory_list_urls'] as $directory_list_slug => $directory_list_array ){
						
							//If we are at the tab URL for one of our list URLs
							if ( $this->_mp_directory_tab == $directory_list_slug ){
								
								//Get list of Plugins to show, Listen for Licenses, And if we are on an Installation page, Create install page for that plugin		
								$this->setup_functions( $directory_list_array['directory_list_url'] );
						
							}
							
						}
					}
					
				}
				
				//Enqueue Scripts for directory page
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts') );
				
				//Admin Body Class
				add_filter( 'admin_body_class', array( $this, 'admin_body_class') );
			}
									
			//Create Plugin Directory Page
			add_action( 'admin_menu', array( $this, 'add_submenu_page') );
																
		}
		
		/**
		 * Enqueue Scripts
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      wp_enqueue_style()
		 * @see      wp_enqueue_script()
	 	 * @return   void
		 */
		public function enqueue_scripts(){
			
			//mp_core_settings_css
			wp_enqueue_style( 'mp_core_settings_css', plugins_url('css/core/mp-core-settings.css', dirname(__FILE__)) );
			
			//mp_core_directory_css
			wp_enqueue_style( 'mp_core_directory_css', plugins_url('css/core/mp-core-directory.css', dirname(__FILE__)) );
			
			//masonry script
			wp_enqueue_script( 'masonry' );
			
			//directory page js
			wp_enqueue_script( 'mp_core_directory_js', plugins_url( 'js/core/directory-page.js', dirname(__FILE__)),  array( 'jquery', 'masonry' ) );
			
			add_thickbox();
			
		}
		
		public function admin_body_class( $classes ){
			
			return $classes . ' plugin-install-php mp-core-directory';
			
		}
		
		/**
		 * Create Plugins Submenu Page in WordPress menu
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      add_submenu_page()
	 	 * @return   void
		 */
		public function add_submenu_page(){
			add_submenu_page( $this->_args['parent_slug'], $this->_args['page_title'], $this->_args['menu_title'], 'activate_plugins', $this->_args['slug'], array( &$this, 'plugin_directory_page' ) );	
		}
		
		/**
		 * Setup functions for each plugin in this directory list url
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      wp_remote_post()
		 * @see      sanitize_title()
		 * @see      MP_CORE_Verify_License
	 	 * @see      get_option()
		 * @see      MP_CORE_Plugin_Installer
		 * @see      admin_url()
	 	 * @see      wp_create_nonce()
		 * @see      add_action()
		 * @return   void
		 */
		public function setup_functions( $directory_list_url ){
				
			//This filter can be used to change the API URL. Useful when calling for updates to the API site's plugins which need to be loaded from a separate URL (see mp_repo_mirror)
			$directory_list_url = has_filter( 'mp_core_plugin_update_package_url' ) ? apply_filters( 'mp_core_plugin_update_package_url', $directory_list_url ) : $directory_list_url;
						
			//Get list of plugins that should be shown
			$response = wp_remote_post( $directory_list_url, array( 'method' => 'POST', 'timeout' => 15, 'sslverify' => false, 'body' => array( 'directory' => 'true', 'mp_directory_page' => $this->_mp_directory_paged ) ) );							 			
			//Json decode plugins array
			$this->response = json_decode($response['body'], true);
			$this->plugins = $this->response['items'];
			
			foreach ( $this->plugins as $plugin ){
				
				//Plugin Name Slug
				$plugin_name_slug = sanitize_title( $plugin['plugin_name'] ); //EG move-plugins-core
				
				//If this plugin requires a license
				if ( $plugin['plugin_licensed'] ){	
					
					//Listen for our activate button to be clicked
					if( isset( $_POST[ $plugin_name_slug . '_license_key' ] ) ) {
										
						//If it has, store it in the license_key variable 
						$license_key = $_POST[ $plugin_name_slug . '_license_key' ];
						
						//Check nonce
						if( ! check_admin_referer( $plugin_name_slug . '_nonce', $plugin_name_slug . '_nonce' ) ) 	
							return false; // get out if we didn't click the Activate button
						
						$args = array(
							'software_name'      => $plugin['plugin_name'],
							'software_api_url'   => $plugin['plugin_api_url'],
							'software_license_key'   => $license_key,
							'software_store_license' => true,
						);
						
						//Store, Verify, and Set the "Green Light" Notification option for this license
						$license_valid = mp_core_verify_license( $args );	
						
					}
					
				}
			
				//Get license
				$license = get_option( $plugin_name_slug . '_license_key' );
				$license_valid = get_option( $plugin_name_slug . '_license_status_valid' );	
				
				//If the install button for this plugin has been clicked and license entered is valid
				if( isset( $_POST[ $plugin_name_slug . '_license_key' ] ) && $license_valid ) {
					
					//Redirect to plugin install page
					header( 'Location: ' . admin_url( sprintf( 'options-general.php?page=mp_core_install_plugin_page_' .  $plugin_name_slug . '&mp-source=mp_core_directory&action=install-plugin&plugin=' . $plugin_name_slug  . '&plugin_api_url=' . base64_encode( $plugin['plugin_api_url'] ) . '&mp_core_directory_page=' . $this->_args['slug'] . '&mp_core_directory_tab=' . $this->_mp_directory_tab . '&_wpnonce=%s', wp_create_nonce( 'install-plugin' ) ) ) );
					
				}
				//If the install button for this plugin has been clicked and license entered is NOT valid!
				elseif ( isset( $_POST[ $plugin_name_slug . '_license_key' ] ) && !$license_valid ){
					
					//Show "License entered not valid" message
					add_action( 'admin_notices', array( $this, 'license_not_valid') );
				}
			}	
		}
		
		/**
		 * Single plugin installation via Directory
		 *
		 * @access   public
		 * @since    1.0.0
		 * @return   void
		 */
		public function single_plugin_installation_via_directory( $plugin_api_url, $plugin_slug ){
			
			if ( empty( $plugin_api_url ) || empty( $plugin_slug ) ){
				return false;	
			}
			
			//Get the plugin we are installing
			$response = wp_remote_post( $plugin_api_url  . '/repo/' . $plugin_slug, array( 'method' => 'POST', 'timeout' => 15, 'sslverify' => false, 'body' => array( 'directory' => 'true', 'mp_directory_page' => $this->_mp_directory_paged ) ) );							 			
			//Json decode plugins array
			$response = json_decode($response['body'], true);
			$plugin = $response['items'][0];
			
			//Plugin Name Slug
			$plugin_name_slug = sanitize_title( $plugin['plugin_name'] ); //EG move-plugins-core	
			
			//Get license
			$license = get_option( $plugin_name_slug . '_license_key' );
			$license_valid = get_option( $plugin_name_slug . '_license_status_valid' );
	
			//If we are on the install page for this plugin
			if ( $this->_page == 'mp_core_install_plugin_page_' .  $plugin_name_slug ){
									
				//Plugin License
				$plugin['plugin_license'] = $license;
				
				//Redirect when complete back to Directory page
				$plugin['plugin_success_link'] = mp_core_add_query_arg( array( 
					'page' => isset( $_GET['mp_core_directory_page'] ) ? $_GET['mp_core_directory_page'] : 'mp_core_plugin_directory', 
					'mp_core_directory_tab' => isset( $_GET['mp_core_directory_tab'] ) ? $_GET['mp_core_directory_tab'] : NULL 
					
				), self_admin_url( 'admin.php' ) );
													
				// Create update/install plugin page
				new MP_CORE_Plugin_Installer( $plugin );
									
			}
			
		}
		
		/**
		 * Show License Not Valid Message
		 *
		 * @access   public
		 * @since    1.0.0
		 * @return   void
		 */
		public function license_not_valid(){
			
			echo '<div class="updated fade"><p>';
				echo 'Oops! That license is not valid!';
			echo '</p></div>';
		}
	
		/**
		 * Show Plugins on Page
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      screen_icon()
		 * @see      sanitize_title()
		 * @see      MP_CORE_Plugin_Directory::check_if_plugin_is_on_this_server()
		 * @see      MP_CORE_Plugin_Directory::display_license()
		 * @see      wp_nonce_url()
		 * @see      plugins_api()
		 * @see      wp_create_nonce()
		 * @return   void
		 */
		public function plugin_directory_page() {
			
			echo '<div class="wrap">';
			
			echo screen_icon( 'plugins' )  .	'<h2>' . apply_filters( 'mp_core_directory_' . $this->_args['slug'] . '_title', $this->_args['page_title'] ) . '</h2>';
			
			do_action( 'mp_core_directory_header_' . $this->_args['slug'] ); ?>
           
            <div class="wp-filter">
                <ul class="filter-links">
                
                <?php 
                $description = NULL;
                
				//If multiple categories have been sent
				if ( is_array( $this->_args['directory_list_urls'] ) ){
					
					//If we are doing a search
					if ( $this->_mp_directory_tab == 'search' ){?>
						
                        <li class="plugin-install-search"><a href="<?php echo mp_core_add_query_arg( array( 'page' => $this->_args['slug'], 'mp_core_directory_tab' => 'search' ), admin_url( 'admin.php' ) ); ?>" class=" current"><?php echo __( 'Search Results', 'mp_core' ); ?></a> </li>

					<?php }
					
					//Loop through each Directory List URL passed-in
					foreach ( $this->_args['directory_list_urls'] as $directory_list_slug => $directory_list_array ){ ?>
						
						<li class="<?php echo $directory_list_slug; ?>"><a href="<?php echo mp_core_add_query_arg( array( 'page' => $this->_args['slug'], 'mp_core_directory_tab' => $directory_list_slug ), admin_url( 'admin.php' ) ); ?>" <?php echo $this->_mp_directory_tab ==  $directory_list_slug ? 'class="current"' : NULL; ?>><?php echo $directory_list_array['title']; ?></a> </li>
					
					<?php 
						$description = $this->_mp_directory_tab ==  $directory_list_slug ? $directory_list_array['description'] : $description;
					} 
				}?>
            	
                </ul>
            
                <form class="search-form search-plugins" method="get" action="">
                    <input type="hidden" name="mp_core_directory_tab" value="search">
                    <input type="hidden" name="page" value="<?php echo $this->_args['slug']; ?>">
                    <label><span class="screen-reader-text"><?php echo __( 'Search ', 'mp_core' ) . $this->_args['page_title']; ?></span>
                        <input type="search" name="search" value="<?php echo isset( $_GET['search'] ) ? $_GET['search'] : ''; ?>" class="" placeholder="<?php echo __( 'Search ', 'mp_core' ) . $this->_args['page_title']; ?>">
                    </label>
                    <input type="submit" name="" id="search-submit" class="button screen-reader-text" value="<?php echo __( 'Search ', 'mp_core' ) . $this->_args['page_title']; ?>">	
                </form>
            </div>
            
            <br class="clear">
            
            <div class="tablenav top">
				<div class="alignleft actions">
				</div>
				<div class="tablenav-pages">
                	<span class="displaying-num"><?php echo $this->response['total_items']; ?> <?php echo __( 'items', 'mp_core' ); ?></span>
					<span class="pagination-links">
                    	
                        <a class="first-page <?php echo $this->_mp_directory_paged == 1 ? 'disabled' : NULL; ?>" title="<?php echo __( 'Go to the first page', 'mp_core' ); ?>" href="<?php echo mp_core_add_query_arg( array( 
							'page' => $this->_args['slug'], 
							'mp_core_directory_tab' => $this->_mp_directory_tab, 
							'mp_core_directory_paged' => 1 
						), admin_url( 'admin.php' ) ); ?>">«</a>
						
                        <a class="prev-page <?php echo $this->_mp_directory_paged == 1 ? 'disabled' : NULL; ?>" title="<?php echo __( 'Go to the previous page', 'mp_core' ); ?>" href="<?php 					echo mp_core_add_query_arg( array( 
							'page' => $this->_args['slug'], 
							'mp_core_directory_tab' => $this->_mp_directory_tab, 
							'mp_core_directory_paged' => $this->_mp_directory_paged == 1 ? 1 : $this->_mp_directory_paged - 1
						), admin_url( 'admin.php' ) ); ?>">‹</a>
                        
						<span class="paging-input">
                        	<form class="mp-core-directory-paged-form" action="<?php echo admin_url( 'admin.php' ); ?>" method="get">
                                <label for="current-page-selector" class="screen-reader-text"><?php echo __( 'Select Page', 'mp_core' ); ?></label>
                                <input class="page" type="hidden" name="page" value="<?php echo $this->_args['slug']; ?>">
                                <input class="mp_core_directory_tab" type="hidden" name="mp_core_directory_tab" value="<?php echo $this->_mp_directory_tab; ?>">
                                <input class="current-page" id="current-page-selector" title="Current page" type="text" name="mp_core_directory_paged" value="<?php echo !empty( $_GET['mp_core_directory_paged'] ) ? $_GET['mp_core_directory_paged'] : 1; ?>" size="4">
                            </form> <?php echo __( 'of', 'mp_core' ); ?> <span class="total-pages"><?php echo $this->response['total_pages']; ?></span>
                        </span>
                        
						<a class="next-page <?php echo $this->_mp_directory_paged == $this->response['total_pages'] ? 'disabled' : NULL; ?>" title="<?php echo __( 'Go to the next page', 'mp_core'); ?>" href="<?php echo mp_core_add_query_arg( array( 
							'page' => $this->_args['slug'], 
							'mp_core_directory_tab' => $this->_mp_directory_tab, 
							'mp_core_directory_paged' => $this->_mp_directory_paged == $this->response['total_pages'] ? $this->_mp_directory_paged : $this->_mp_directory_paged + 1
						), admin_url( 'admin.php' ) ); ?>">›</a>
                        
						<a class="last-page <?php echo $this->_mp_directory_paged == $this->response['total_pages'] ? 'disabled' : NULL; ?>" title="<?php echo __( 'Go to the last page', 'mp_core' ); ?>" href="<?php echo mp_core_add_query_arg( array( 
							'page' => $this->_args['slug'], 
							'mp_core_directory_tab' => $this->_mp_directory_tab, 
							'mp_core_directory_paged' => $this->response['total_pages']
						), admin_url( 'admin.php' ) ); ?>">»</a>
                        
                    </span>
               </div>				
               
			</div>
            
            <p class="mp-core-directory-tab-description"><?php echo !empty( $description ) ? $description : NULL ?></p>
            
			<?php
			echo '<div class="mp-directory-browser">';
			
				echo '<div id="mp-directory-items">';
				
				if ( !is_array( $this->plugins ) || empty( $this->plugins ) ){
				
					echo '<div class="no-plugin-results">' . __( 'No items match your request.', 'mp_core' ) . '</div>';
					
					echo '</div>';
					
					return;
				}
			
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
						$installed_output = '<div class="mp-core-true-false-light  mp-core-directory-true-false-light">';
							$installed_output .= '<div class="mp-core-green-light"></div>';
						$installed_output .= '</div>';	
					
						//Set $install_output to say the plugin is active
						$installed_output .= 'Plugin is active';
						
						//If this plugin requires a license, show that license
						$install_output = $plugin['plugin_licensed'] == true ? $this->display_license( $plugin_name_slug, $check_plugin, $plugin['plugin_buy_url'],  $plugin['plugin_price'] ) : sprintf( '<a class="button mp-directory-install-btn" href="%s"> ' . __('Re-Install "', 'mp_core') . $plugin['plugin_name'] . '"</a>', admin_url( sprintf( 'options-general.php?page=mp_core_install_plugin_page_' .  $plugin['plugin_slug'] . '&action=install-plugin&mp-source=mp_core_directory&plugin=' . $plugin['plugin_slug']  . '&plugin_api_url=' . base64_encode( $plugin['plugin_api_url'] )  . '&mp_core_directory_page=' . $this->_args['slug'] . '&mp_core_directory_tab=' . $this->_mp_directory_tab . '&_wpnonce=%s', wp_create_nonce( 'install-plugin'  ) ) ) );
						
					}
					//If the plugin is installed but is not active
					elseif ( $check_plugin['plugin_exists'] ) {
						
						//Show the red light
						$installed_output = '<div class="mp-core-true-false-light  mp-core-directory-true-false-light">';
							$installed_output .= '<div class="mp-core-grey-light"></div>';
						$installed_output .= '</div>';	
						
						//Set $install_output to say the plugin is installed but not active
						$installed_output .= __( 'Plugin is installed but not active', 'mp_core' );
						
						//Set $install_output to "Activate" plugin
						$install_output = '<a href="' . wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $check_plugin['plugin_directory'] . $plugin['plugin_filename'] . '&amp;plugin_status=all&amp;paged=1&amp;s=', 'activate-plugin_' . $check_plugin['plugin_directory'] . $plugin['plugin_filename'] ) . '" title="' . __('Activate ', 'mp_core') . $plugin['plugin_name'] . '" class="button mp-directory-install-btn" >' . __('Activate ', 'mp_core') . '"' . $plugin['plugin_name'] . '"</a>';
						
						//If this plugin requires a license, show that license
						$install_output .= $plugin['plugin_licensed'] == true ? $this->display_license( $plugin_name_slug, $check_plugin, $plugin['plugin_buy_url'],  $plugin['plugin_price'] ) : NULL;
						
					}
					//If the plugin isn't instaled or active
					else{
						
						
						//Show the red light
						$installed_output = '<div class="mp-core-true-false-light  mp-core-directory-true-false-light">';
							$installed_output .= '<div class="mp-core-grey-light"></div>';
						$installed_output .= '</div>';	
						
						//Set $install_output to say the plugin is installed but not active
						$installed_output .= 'Plugin is not installed.';
													
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
														
								//Create License Output
								$install_output = $this->display_license( $plugin_name_slug, $check_plugin, $plugin['plugin_buy_url'],  $plugin['plugin_price'] );
								
							}
							else{
								
								// "Oops! this plugin doesn't exist in the repo. So lets display a custom download button."; 
								$install_output = sprintf( '<a class="button mp-directory-install-btn" href="%s"> ' . __('Install "', 'mp_core') . $plugin['plugin_name'] . '"</a>', admin_url( sprintf( 'options-general.php?page=mp_core_install_plugin_page_' .  $plugin['plugin_slug'] . '&action=install-plugin&mp-source=mp_core_directory&plugin=' . $plugin['plugin_slug']  . '&plugin_api_url=' . base64_encode( $plugin['plugin_api_url'] ) . '&mp_core_directory_page=' . $this->_args['slug'] . '&mp_core_directory_tab=' . $this->_mp_directory_tab . '&_wpnonce=%s', wp_create_nonce( 'install-plugin'  ) ) ) );
							}
												
						}else{
							//Otherwise display the WordPress.org Repo Install button
							$install_output = sprintf( '<a class="button mp-directory-install-btn" href="%s"> ' . __('Install "', 'mp_core') . $plugin['plugin_name'] . '"</a>', admin_url( sprintf( 'update.php?action=install-plugin&mp-source=mp_core_directory&plugin=' . $plugin_name_slug . '&mp_core_directory_tab=' . $this->_mp_directory_tab . '&_wpnonce=%s', wp_create_nonce( 'install-plugin' ) ) ) );	
						
						}
						
					}
									
					//Show this plugin on the page
					?>
                    <div class="plugin-card">
                        <div class="plugin-card-top">
                            <a href="<?php echo $plugin['plugin_buy_url']; ?>" class="plugin-icon" target="_blank"><img src="<?php echo str_replace( 'http://', 'https://', $plugin['plugin_image'] ); ?>"></a>
                            <div class="name column-name">
                                <h4><a href="<?php echo mp_core_add_query_arg( array( 'TB_iframe' => true, 'width' => '772', 'height' => '373' ), $plugin['plugin_buy_url'] ); ?>" class="thickbox" target="_blank"><?php echo $plugin['plugin_name']; ?></a></h4>
                            </div>
                            <div class="action-links mp-core-directory-price">
               					<h4><?php echo $plugin['plugin_price']; ?></h4>
                            </div>
                            <div class="desc column-description">
                                <p><?php echo $plugin['plugin_description']; ?></p>
                                <p class="authors"> 
                                	<cite>By <a href="<?php echo $plugin['plugin_author_url']; ?>" target="_blank"><?php echo $plugin['plugin_author']; ?></a></cite>
                                </p>
                            </div>
                        </div>
                        <div class="plugin-card-bottom">
                            <div class="vers column-rating mp-column-installation">
                                <?php echo $installed_output; ?>
								<?php echo $install_output; ?> 
                            </div>
                            <div class="column-updated mp-column-plugin-status">
                              
                            </div>
                        </div>
                    </div>
                    <?php
				}
				
				echo '</div>';
				
				do_action( 'mp_core_directory_footer_' . $this->_args['slug'] );
			
			echo '</div>';
			
			?><div class="tablenav top">
				<div class="alignleft actions">
				</div>
				<div class="tablenav-pages">
                	<span class="displaying-num"><?php echo $this->response['total_items']; ?> <?php echo __( 'items', 'mp_core' ); ?></span>
					<span class="pagination-links">
                    	
                        <a class="first-page <?php echo $this->_mp_directory_paged == 1 ? 'disabled' : NULL; ?>" title="<?php echo __( 'Go to the first page', 'mp_core' ); ?>" href="<?php echo mp_core_add_query_arg( array( 
							'page' => $this->_args['slug'], 
							'mp_core_directory_tab' => $this->_mp_directory_tab, 
							'mp_core_directory_paged' => 1 
						), admin_url( 'admin.php' ) ); ?>">«</a>
						
                        <a class="prev-page <?php echo $this->_mp_directory_paged == 1 ? 'disabled' : NULL; ?>" title="<?php echo __( 'Go to the previous page', 'mp_core' ); ?>" href="<?php 					echo mp_core_add_query_arg( array( 
							'page' => $this->_args['slug'], 
							'mp_core_directory_tab' => $this->_mp_directory_tab, 
							'mp_core_directory_paged' => $this->_mp_directory_paged == 1 ? 1 : $this->_mp_directory_paged - 1
						), admin_url( 'admin.php' ) ); ?>">‹</a>
                        
						<span class="paging-input">
                        	<form class="mp-core-directory-paged-form" action="<?php echo admin_url( 'admin.php' ); ?>" method="get">
                                <label for="current-page-selector" class="screen-reader-text"><?php echo __( 'Select Page', 'mp_core' ); ?></label>
                                <input class="page" type="hidden" name="page" value="<?php echo $this->_args['slug']; ?>">
                                <input class="mp_core_directory_tab" type="hidden" name="mp_core_directory_tab" value="<?php echo $this->_mp_directory_tab; ?>">
                                <input class="current-page" id="current-page-selector" title="Current page" type="text" name="mp_core_directory_paged" value="<?php echo !empty( $_GET['mp_core_directory_paged'] ) ? $_GET['mp_core_directory_paged'] : 1; ?>" size="4">
                            </form> <?php echo __( 'of', 'mp_core' ); ?> <span class="total-pages"><?php echo $this->response['total_pages']; ?></span>
                        </span>
                        
						<a class="next-page <?php echo $this->_mp_directory_paged == $this->response['total_pages'] ? 'disabled' : NULL; ?>" title="<?php echo __( 'Go to the next page', 'mp_core'); ?>" href="<?php echo mp_core_add_query_arg( array( 
							'page' => $this->_args['slug'], 
							'mp_core_directory_tab' => $this->_mp_directory_tab, 
							'mp_core_directory_paged' => $this->_mp_directory_paged == $this->response['total_pages'] ? $this->_mp_directory_paged : $this->_mp_directory_paged + 1
						), admin_url( 'admin.php' ) ); ?>">›</a>
                        
						<a class="last-page <?php echo $this->_mp_directory_paged == $this->response['total_pages'] ? 'disabled' : NULL; ?>" title="<?php echo __( 'Go to the last page', 'mp_core' ); ?>" href="<?php echo mp_core_add_query_arg( array( 
							'page' => $this->_args['slug'], 
							'mp_core_directory_tab' => $this->_mp_directory_tab, 
							'mp_core_directory_paged' => $this->response['total_pages']
						), admin_url( 'admin.php' ) ); ?>">»</a>
                        
                    </span>
               </div>				
               
			</div><?php
			
		}
			
		/**
		 * Display the license on the plugins page
		 *
		 * @param $plugin_name_slug string
		 * @param $check_plugin array( 
		 *			@type bool 'plugin_active' Whether this plugin is active or not
		 *          @type bool 'plugin_exists' Whether this plugin is exists or not
		 *          @type string 'plugin_directory' The location where plugins are installed on this server
		 * )
		 * @param $buy_url string The URL to where this plugin license can be purchased
		 * @param $price string The price of this plugin
		 *
		 * @return $output - HTML output for the license button
		 */
		public function display_license( $plugin_name_slug, $check_plugin, $buy_url, $price ){
			
			//Get and set license and status
			$license 	= get_option( $plugin_name_slug . '_license_key' );
			$status 	= get_option( $plugin_name_slug . '_license_status_valid' );
			
			$output = '<div id="' . $plugin_name_slug . '-plugin-license-wrap" class="mp-core-plugin-directory-license-wrap">';
				
			//If this license is valid
			if ( $status == true ){
				
				//Show the green light
				$output .= '<div class="mp-core-true-false-light  mp-core-directory-true-false-light">';
					$output .= '<div class="mp-core-green-light"></div>';
				$output .= '</div>';
				
				//Show a message that the license is valid
				$output .= __('License Valid', 'mp_core');		
				
				//If the plugin is already active and installed
				if ( $check_plugin['plugin_active'] == true && $check_plugin['plugin_exists'] == true) {
					//UPDATE LICENSE FORM - initally hidden
					$output .= '<form method="post" class="mp-core-directory-update-license-form">';
				}
				//If the plugin doesn't even exist on this server
				else{
					//UPDATE LICENSE BUTTON
					$output .= '<form method="post" class="mp-core-directory-new-license-form">';
				}
				
					//The input field for the license
					$output .= '<input id="' . $plugin_name_slug . '_license_key" name="' . $plugin_name_slug . '_license_key" type="text" class="mp-core-directory-license-input regular-text" value="' . esc_attr( $license ) . '" />';
												
					//Nonce								
					$output .= wp_nonce_field( $plugin_name_slug . '_nonce', $plugin_name_slug . '_nonce', true, false );
					
					//If the plugin is already active and installed
					if ( $check_plugin['plugin_active'] == true && $check_plugin['plugin_exists'] == true) {
						//Show the submit and install button
						$output .= get_submit_button(__('Submit License to Re-Install', 'mp_core') );
					}
					//If the plugin doesn't even exist on this server
					else{
						//Show the submit and install button
						$output .= get_submit_button(__('Submit License and Install', 'mp_core') );
					}
				
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
					$output .= __('License not valid', 'mp_core');
					
					//Show a link to update the license
					$output .= ' | ';
				
				}
				
				//Show the buy button
				$output .= '<a href="' . $buy_url . '" target="_blank">' . __( 'Get License Now - ', 'mp_core' ) . $price . '</a>'; 
				
				//License Form
				$output .= '<form method="post" class="mp-core-directory-update-license-form">';
					
					//License Input Field
					$output .= '<input id="' . $plugin_name_slug . '_license_key" name="' . $plugin_name_slug . '_license_key" type="text" class="mp-core-directory-license-input regular-text" placeholder="' . __( 'License Key', 'mp_core' ) . '" value="' . esc_attr( $license ) . '" />		';				
					
					//Nonce							
					$output .= wp_nonce_field( $plugin_name_slug . '_nonce', $plugin_name_slug . '_nonce', true, false );
					
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
		 *
		 * @param $args array For information see link.
		 * @return array array( 'plugin_active' => true, 'plugin_exists' => true, 'plugin_directory' => NULL )
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

