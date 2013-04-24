<?php
/**
 * Malachi Theme
 *
 */
function mp_core_theme_updater() {
	$args = array(
		'software_type' => 'theme', 
		'software_name' => 'Malachi Theme', 
		'software_message' => 'You require the Malachi Theme', 
		'software_slug' => 'mt_malachi', 
		'software_filename' => 'malachi',
		'software_required' => true,
		'software_download_link' => 'http://our-themes.s3.amazonaws.com/armonico/Armonico-Theme.zips'
	);
	$mp_core_theme_updater = new MP_CORE_Plugin_Checker($args);
}
add_action( 'after_setup_theme', 'mp_core_theme_updater' );
