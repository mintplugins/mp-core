<?php			
/**
 * This is the code that will create a new page of settings for your page.
 * To set up this page:
 * Step 1. Include this page in your plugin/theme
 * Step 2. Do a find-and-replace for the term 'my_plugin_settings' and replace it with the slug you desire for this page
 * Step 3. Go to line 17 and set the title, slug, and type for this page.
 * Step 4. Include options tabs.
 * Go here for full setup instructions: 
 * http://mintplugins.com/settings-class/
 */

function my_plugin_settings(){
	
	/**
	 * Set args for new administration menu.
	 *
	 * For complete instructions, visit:
	 * http://mintplugins.com/how-to-set-the-args-when-creating-a-new-settings-page/
	 *
	 */
	$args = array('title' => __('Sample Settings', 'mp_core'), 'slug' => 'my_plugin_settings', 'type' => 'options');
	
	//Initialize settings class
	global $my_plugin_settings;
	$my_plugin_settings = new MP_CORE_Settings($args);
	
	//Include other option tabs
	include_once( 'settings-tab-general.php' );
	
	//Include other option tabs
	include_once( 'settings-tab-display.php' );
}
add_action('plugins_loaded', 'my_plugin_settings');