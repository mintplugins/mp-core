<?php 

//Set args for new administration menu
$args = array('title' => __('Theme Options', 'mp_core'), 'slug' => 'mp_theme_options', 'type' => 'theme');
//dashboard, posts, media, links, theme, menu, submenu, plugins, management, options, users, comments, pages

//Initialize settings class
global $theme_options;
$theme_options = new MP_CORE_Settings($args);


//Include other option tabs
include_once( 'settings-tab-general.php' );
include_once( 'settings-tab-display.php' );