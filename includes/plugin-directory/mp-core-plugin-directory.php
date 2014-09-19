<?php
/**
 * Plugin Directory Class for the mp_core Plugin by Mint Plugins
 * http://mintplugins.com/doc/plugin-directory-class/
 */
function mp_core_plugin_directory(){
	
	$args = array (
		'parent_slug' => 'plugins.php',
		'page_title' => 'Mint Plugins',
		'slug' => 'mp_core_plugin_directory',
		'directory_list_url' => 'https://mintplugins.com/repo-group/mint-plugins/'
	);
	
	new MP_CORE_Plugin_Directory( $args );
}
add_action( '_admin_menu', 'mp_core_plugin_directory' );