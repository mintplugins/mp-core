<?php
/**
 * This file contains the function keeps the MP Core plugin up to date.
 *
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Functions
 *
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */
 
/**
 * Check for updates for the MP Core Plugin by creating a new instance of the MP_CORE_Plugin_Updater class.
 *
 * @access   public
 * @since    1.0.0
 * @return   void
 */
 if (!function_exists('mp_core_update')){
	function mp_core_update() {
		$args = array(
			'software_name' => 'MP Core', //<- The exact name of this Plugin. Make sure it matches the title in your mp_repo, edd, and the WP.org repo
			'software_api_url' => 'http://mintplugins.com',//The URL where EDD and mp_repo are installed and checked
			'software_filename' => 'mp-core.php',
			'software_licensed' => false, //<-Boolean
			'software_wp_repo_ignore' => true
		);
		
		//Since this is a plugin, call the Plugin Updater class
		$mp_core_plugin_updater = new MP_CORE_Plugin_Updater($args);
	}
 }
add_action( 'init', 'mp_core_update' );
