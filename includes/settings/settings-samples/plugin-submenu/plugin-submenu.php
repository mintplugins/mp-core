<?php 

//Set args for new administration menu
$args = array('parent_slug' => 'edit.php?post_type=download', 'title' => __('Plugin Submenu', 'mp_core'), 'slug' => 'mp_plugin_submenu', 'type' => 'submenu');
//dashboard, posts, media, links, theme, menu, submenu, plugins, management, options, users, comments, pages

//Initialize settings class
global $plugin_submenu;
$plugin_submenu = new mp_core_Settings($args);


//Include other option tabs
include_once( 'settings-tab-general.php' );
include_once( 'settings-tab-display.php' );