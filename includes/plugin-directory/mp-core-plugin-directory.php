<?php
/**
 * Plugin Directory Class for the mp_core Plugin by Move Plugins
 * http://moveplugins.com/doc/plugin-directory-class/
 */
function mp_core_plugin_directory(){
	
	$args = array (
		'parent_slug' => 'plugins.php/',
		'page_title' => 'Move Plugins',
		'slug' => 'mp_core_plugin_directory',
		'directory_list_url' => 'http://moveplugins.com/repo-group/move-plugins/'
	);
	
	new MP_CORE_Plugin_Directory( $args );
}
add_action( '_admin_menu', 'mp_core_plugin_directory' );