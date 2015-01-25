<?php
/**
 * Plugin Directory Class for the mp_core Plugin by Mint Plugins
 * http://mintplugins.com/doc/plugin-directory-class/
 */
function mp_core_plugin_directory(){
	
	$args = array (
		'parent_slug' => 'plugins.php',
		'menu_title' => 'Mint Plugins',
		'page_title' => 'Mint Plugins',
		'slug' => 'mp_core_plugin_directory',
		'directory_list_urls' => array(
			'mint_plugins' => array(
				'title' => __( 'Mint Plugins', 'mp_core' ),
				'description' => __( 'Plugins created and maintained by MintPlugins.com', 'mp_core' ),
				'directory_list_url' => 'http://mintplugins.com/repo-group/mint-plugins/',
			),
		),
		'search_api_url' => 'http://mintplugins.com/',
		'limit_search_to_repo_group_slug' => 'mint-plugins',
	);
	
	new MP_CORE_Plugin_Directory( $args );
}
add_action( '_admin_menu', 'mp_core_plugin_directory' );

//Add Mint Plugins as a tab on the "Plugins" > "Add New" menu.
function mp_core_add_mintplugins_to_add_new_directory( $views ){
	
	$views['plugin-install-mintplugins'] = '<a href="' . admin_url('/plugins.php?page=mp_core_plugin_directory') . '">Mint Plugins</a>';
	
	return $views;
	
}
add_filter( 'views_plugin-install', 'mp_core_add_mintplugins_to_add_new_directory' );