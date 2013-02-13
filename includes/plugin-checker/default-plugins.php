<?php
/**
 * Install Theme Updater Plugin
 *
 */
function mp_core_theme_updater() {
	$args = array(
		'plugin_name' => 'Theme Updater', 
		'plugin_message' => 'To enable automatic updates for this theme, install the Theme Updater plugin. (Required).', 
		'plugin_slug' => 'theme-updater', 
		'plugin_subdirectory' => 'theme-updater/', 
		'plugin_filename' => 'updater.php',
		'plugin_required' => true,
		'plugin_download_link' => 'http://moveplugins.com'
	);
	$mp_core_theme_updater = new MP_CORE_Plugin_Checker($args);
}
add_action( 'after_setup_theme', 'mp_core_theme_updater' );

/**
 * Install Easy Digital Downloads Plugin
 *
 */
function mp_core_edd() {
	$args = array(
		'plugin_name' => 'Easy Digital Downloads', 
		'plugin_message' => 'If you would like to enable automatic updates for this theme, install the Theme Updater plugin. (Recommended).', 
		'plugin_slug' => NULL, 
		'plugin_subdirectory' => 'easy-digital-downloads/', 
		'plugin_filename' => 'easy-digital-downloads.php',
		'plugin_required' => false,
		'plugin_download_link' => 'http://moveplugins.com'
	);
	$mp_core_edd = new MP_CORE_Plugin_Checker($args);
}
add_action( 'after_setup_theme', 'mp_core_edd' );

/**
 * Install Akismet Plugin
 *
 */
function mp_core_akismet() {
	$args = array(
		'plugin_name' => 'Akismet', 
		'plugin_message' => 'In order to keep your website from being overrun with spam, activate the Akismet Plugin. (Required).', 
		'plugin_slug' => 'akismet', 
		'plugin_subdirectory' => 'akismet/', 
		'plugin_filename' => 'akismet.php',
		'plugin_required' => true ,
		'plugin_download_link' => 'http://moveplugins.com'
	);
	$mp_core_akismet = new MP_CORE_Plugin_Checker($args);
}
add_action( 'after_setup_theme', 'mp_core_akismet' );