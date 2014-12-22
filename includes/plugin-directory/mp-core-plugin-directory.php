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
				'directory_list_url' => 'https://mintplugins.com/repo-group/mint-plugins/',
			),
		),
		'search_api_url' => 'https://mintplugins.com/',
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
		
//Add our own Mint Plugins to the main WordPress plugin directory
//This makes the plugin show - but installation is currently not working but may be added in the future.
function mp_core_add_custom_to_wordpress_plugins_dir( $plugins ){
	
	$custom_plugin = new stdClass();
	$custom_plugin->name = 'MP Stacks';
	$custom_plugin->slug = 'mp-stacks';
	$custom_plugin->version = '1.0.0.0';
	$custom_plugin->author = '<a href="https://mintplugins.com">Mint Plugins</a>';
	$custom_plugin->author_profile = '//mintplugins.com';
	$custom_plugin->contributors = array();
	$custom_plugin->requires = '3.6';
	$custom_plugin->tested = '4.1';
	$custom_plugin->rating = '100';
	$custom_plugin->num_ratings = '1';
	$custom_plugin->ratings = array();
	$custom_plugin->downloaded = '500';
	$custom_plugin->last_updated = date( 'Y-m-d h:ia e');
	$custom_plugin->homepage = 'https://mintplugins.com';
	$custom_plugin->short_description = 'MP Stacks is a simple and free page building plugin for WordPress.';
	$custom_plugin->icons = array(
		'2x' => 'https://mintplugins.com/wp-content/uploads/2014/12/Stacks-Icon-For-Directory.jpg'
	);
	
	if ( !isset( $_GET['tab'] ) || (isset( $_GET['tab'] ) && $_GET['tab'] != 'plugin-information' ) ){
		array_push( $plugins->plugins, $custom_plugin );
	}
	
	return $plugins;
}
//add_filter( 'plugins_api_result', 'mp_core_add_custom_to_wordpress_plugins_dir' );