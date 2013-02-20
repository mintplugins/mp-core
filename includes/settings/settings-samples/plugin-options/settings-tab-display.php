<?php			
/**
 * This is the code that will create a new tab of settings for your page.
 * To create a new tab and set up this page:
 * Step 1. Duplicate this page and include it in the "class initialization function".
 * Step 1. Do a find-and-replace for the term 'my_plugin_settings' and replace it with the slug you set when initializing this class
 * Step 2. Do a find and replace for 'display' and replace it with your desired tab slug
 * Step 3. Go to line 17 and set the title for this tab.
 * Step 4. Begin creating your custom options on line 30
 * Go here for full setup instructions: 
 * http://moveplugins.com/settings-class/
 */

/**
* Create new tab
*/
$my_plugin_settings->mp_core_new_tab(__('Display Settings' , 'my_plugin'), 'display');

/**
* Create the options for this tab
*/
function my_plugin_settings_display_create(){
	
	register_setting(
		'my_plugin_settings_display',
		'my_plugin_settings_display',
		'mp_core_settings_validate'
	);
	
	add_settings_section(
		'envato_check_settings',
		__( 'Envato Check Settings', 'my_plugin' ),
		'__return_false',
		'my_plugin_settings_display'
	);
	
	add_settings_field(
		'enable_disable',
		__( 'Enable/Disable Envato Check', 'my_plugin' ), 
		'mp_core_select',
		'my_plugin_settings_display',
		'envato_check_settings',
		array(
			'name'        => 'enable_disable',
			'value'       => mp_core_get_option( 'my_plugin_settings_display',  'enable_disable' ),
			'description' => __( 'Do you want the Envato Checker to be enabled or disabled?', 'my_plugin' ),
			'registration'=> 'my_plugin_settings_display',
			'options'=> array('enabled', 'disabled')
		)
	);
	
	add_settings_field(
		'envato_username',
		__( 'Envato Username', 'my_plugin' ), 
		'mp_core_textbox',
		'my_plugin_settings_display',
		'envato_check_settings',
		array(
			'name'        => 'envato_username',
			'value'       => mp_core_get_option( 'my_plugin_settings_display',  'envato_username' ),
			'description' => __( 'Enter your Envato Username', 'my_plugin' ),
			'registration'=> 'my_plugin_settings_display',
		)
	);
	
	add_settings_field(
		'envato_api_key',
		__( 'Envato API Key', 'my_plugin' ), 
		'mp_core_textbox',
		'my_plugin_settings_display',
		'envato_check_settings',
		array(
			'name'        => 'envato_api_key',
			'value'       => mp_core_get_option( 'my_plugin_settings_display',  'envato_api_key' ),
			'description' => __( 'Enter your Envato API Key', 'my_plugin' ),
			'registration'=> 'my_plugin_settings_display',
		)
	);
	
	add_settings_field(
		'redirect_page',
		__( 'Redirect Page', 'my_plugin' ), 
		'mp_core_select',
		'my_plugin_settings_display',
		'envato_check_settings',
		array(
			'name'        => 'redirect_page',
			'value'       => mp_core_get_option( 'my_plugin_settings_display',  'redirect_page' ),
			'description' => __( 'Select the page you want to redirect your users to after they create an account', 'my_plugin' ),
			'registration'=> 'my_plugin_settings_display',
			'options'=> mp_core_get_all_pages() 
		)
	);
	
	add_settings_field(
		'envato_message',
		__( 'Envato Message', 'my_plugin' ), 
		'mp_core_wp_editor',
		'my_plugin_settings_display',
		'envato_check_settings',
		array(
			'name'        => 'envato_message',
			'value'       => mp_core_get_option( 'my_plugin_settings_display',  'envato_message' ),
			'description' => __( 'This is the message that will appear over the Purchase Code verification form.', 'my_plugin' ),
			'registration'=> 'my_plugin_settings_display',
		)
	);
	
	//additional display settings
	do_action('my_plugin_settings_additional_display_settings_hook');
}
add_action( 'admin_init', 'my_plugin_settings_display_create' );