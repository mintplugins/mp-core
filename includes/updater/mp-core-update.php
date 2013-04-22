<?php
/**
 * Check for updates for this Theme
 *
 */
 if (!function_exists('mp_core_update')){
	function mp_core_update() {
		$args = array(
			'software_name' => 'mp_core', //<- The name of this Software in EDD
			'software_slug' => 'mp_core', //<- The slug (directory name) for this software. Make sure it matches the slug on the WP repo, edd, and mp_repo
			'software_api_url' => 'http://moveplugins.com/',//The URL where EDD and mp_repo are installed and checked
			'software_filename' => 'mp-core.php',
			'software_licenced' => true, //<-Boolean
		);
		
		//Since this is a theme, call the Plugin Updater class
		$mp_core_plugin_updater = new MP_CORE_Plugin_Updater($args);
	}
 }
add_action( 'init', 'mp_core_update' );
