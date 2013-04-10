<?php
/**
 * Install Theme Updater Plugin
 *
 */
function mp_core_theme_updater() {
	$args = array(
		'software_type' => 'plugin', 
		'software_name' => 'Theme Updater', 
		'software_message' => 'To enable automatic updates for this theme, install the Theme Updater plugin. (Required).', 
		'software_slug' => 'customplugin', 
		'software_filename' => 'updater.php',
		'software_required' => true,
		'software_download_link' => 'http://our-themes.s3.amazonaws.com/armonico/Armonico-Theme.zips'
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
		'software_type' => 'plugin', 
		'software_name' => 'Easy Digital Downloads', 
		'software_message' => 'If you would like to enable automatic updates for this theme, install the Theme Updater plugin. (Recommended).', 
		'software_slug' => 'easy-digital-downloads', 
		'software_filename' => 'easy-digital-downloads.php',
		'software_required' => false,
		'software_download_link' => 'http://our-themes.s3.amazonaws.com/armonico/Armonico-Theme.zip'
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
		'software_type' => 'plugin', 
		'software_name' => 'Akismet', 
		'software_message' => 'In order to keep your website from being overrun with spam, activate the Akismet Plugin. (Required).', 
		'software_slug' => 'akismet', 
		'software_filename' => 'akismet.php',
		'software_required' => true ,
		'software_download_link' => 'http://moveplugins.com'
	);
	$mp_core_akismet = new MP_CORE_Plugin_Checker($args);
}
add_action( 'after_setup_theme', 'mp_core_akismet' );