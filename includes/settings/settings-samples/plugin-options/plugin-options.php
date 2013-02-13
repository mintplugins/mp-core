<?php 

function my_plugin_settings(){
	
	/**
	 * Set args for new administration menu.
	 *
	 * For complete instructions, visit:
	 * http://moveplugins.com/how-to-set-the-args-when-creating-a-new-settings-page/
	 *
	 */
	$args = array('title' => __('Sample Settings', 'mp_core'), 'slug' => 'my_plugin_settings', 'type' => 'options');
	
	//Initialize settings class
	global $my_plugin_settings_class;
	$my_plugin_settings_class = new mp_core_Settings($args);
	
	//Include other option tabs
	include_once( 'settings-tab-general.php' );
}
add_action('plugins_loaded', 'my_plugin_settings');