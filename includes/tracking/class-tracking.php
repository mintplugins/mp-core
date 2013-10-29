<?php
/**
 * This file contains the MP_CORE_Tracking class
 *
 * @link http://moveplugins.com/doc/tracking-class/
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
 * This class adds a new tracking data set for Press Trends
 *
 * @author     Philip Johnston
 * @link       http://moveplugins.com/doc/tracking-class/
 * @since      1.0.0
 * @return     void
 */
if (!class_exists('MP_CORE_Tracking')){
	class MP_CORE_Tracking{
				
		protected $_args;
		protected $_metabox_items_array = array();
		
		/**
		 * Constructor
		 *
		 * @access   public
		 * @since    1.0.0
		 * @link     http://moveplugins.com/doc/tracking-class/
		 * @author   Philip Johnston
		 * @see      MP_CORE_Tracking::presstrends_plugin()
		 * @see      MP_CORE_Tracking::presstrends_theme()
		 * @see      wp_parse_args()
		 * @param    array $args (required) See link for description.
		 * @return   void
		 */	
		public function __construct($args){
											
			//Set defaults for args		
			$args_defaults = array(
				'api_key' => NULL, //Press Trends API key
				'type' => NULL, //Plugin or Theme
				'plugin_data' => NULL //get_plugin_data( __FILE__ )
			);
			
			//Get and parse args
			$this->_args = wp_parse_args( $args, $args_defaults );
			
			//Activate Press Trends	for theme or plugin
			$press_trends_function = 'presstrends_' . $this->_args['type'];
			$this->$press_trends_function();
			
		}
		
		/**
		 * Enqueue Scripts needed for the MP_CORE_Tracking class
		 *
		 * @access   public
		 * @since    1.0.0
		 * @see      get_transient()
		 * @see      wp_count_posts()
		 * @see      wp_count_comments()
		 * @see      wp_get_theme()
		 * @see      get_theme_data()
		 * @see      get_stylesheet_directory()
		 * @see      get_plugins()
		 * @see      wp_remote_get()
		 * @see      set_transient()
		 * @return   void
		 */
		public function presstrends_plugin() {
			
			// PressTrends Account API Key
			$api_key = $this->_args['api_key'];
			$auth    = '';
			
			// Start of Metrics
			global $wpdb;
			$data = get_transient( 'presstrends_cache_data' );
			if ( !$data || $data == '' ) {
				$api_base = 'http://api.presstrends.io/index.php/api/pluginsites/update?auth=';
				$url      = $api_base . $auth . '&api=' . $api_key . '';
				$count_posts    = wp_count_posts();
				$count_pages    = wp_count_posts( 'page' );
				$comments_count = wp_count_comments();
				if ( function_exists( 'wp_get_theme' ) ) {
					$theme_data = wp_get_theme();
					$theme_name = urlencode( $theme_data->Name );
				} else {
					$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
					$theme_name = $theme_data['Name'];
				}
				$plugin_name = '&';
				foreach ( get_plugins() as $plugin_info ) {
					$plugin_name .= $plugin_info['Name'] . '&';
				}
				// CHANGE __FILE__ PATH IF LOCATED OUTSIDE MAIN PLUGIN FILE
				$plugin_data         = $this->_args['plugin_data'];
				$posts_with_comments = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='post' AND comment_count > 0" );
				$data                = array(
					'url'             => base64_encode(site_url()),
					'posts'           => $count_posts->publish,
					'pages'           => $count_pages->publish,
					'comments'        => $comments_count->total_comments,
					'approved'        => $comments_count->approved,
					'spam'            => $comments_count->spam,
					'pingbacks'       => $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_type = 'pingback'" ),
					'post_conversion' => ( $count_posts->publish > 0 && $posts_with_comments > 0 ) ? number_format( ( $posts_with_comments / $count_posts->publish ) * 100, 0, '.', '' ) : 0,
					'theme_version'   => $plugin_data['Version'],
					'theme_name'      => $theme_name,
					'site_name'       => str_replace( ' ', '', get_bloginfo( 'name' ) ),
					'plugins'         => count( get_option( 'active_plugins' ) ),
					'plugin'          => urlencode( $plugin_name ),
					'wpversion'       => get_bloginfo( 'version' ),
				);
				foreach ( $data as $k => $v ) {
					$url .= '&' . $k . '=' . $v . '';
				}
				wp_remote_get( $url );
				set_transient( 'presstrends_cache_data', $data, 60 * 60 * 24 );
			}
		}
	}
}

/**
 * Create an instance of the tracking class for the MP Core plugin
 *
 * @since    1.0.0
 * @see      MP_CORE_Tracking
 * @return   void
 */
function mp_core_press_trends(){
	
	new MP_CORE_Tracking(array( 'api_key' => 'ykuk0gokrffmdjz79axjzqy5i0a8b22jh', 'type' => 'plugin', 'plugin_data' => get_plugin_data( __FILE__ )));
	
}
add_action( 'admin_init', 'mp_core_press_trends' );
