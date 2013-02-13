<?php			
/**
 * This is the code that will create a new tab of settings for your page.
 * To set it up, first do a find-and-replace for the term 'my_plugin' and replace it with your plugin's textdomain
 * Then do a find and replace for 'general' and replace it with your desired tab slug
 * Go here for full setup instructions: 
 * http://moveplugins.com/settings-class/
 */
 
/**
* Display tab at top of Theme Options page
*/
function my_plugin_settings_general_tab_title($active_tab){ 
	$tab_title = __('My Other Settings' , 'my_plugin');
	if ($active_tab == 'my_plugin_settings_general'){ $active_class = 'nav-tab-active'; }else{$active_class = "";}
	echo ('<a href="?page=my_plugin_settings&tab=my_plugin_settings_general" class="nav-tab ' . $active_class . '">' . $tab_title . '</a>');
}
add_action( 'my_plugin_settings_new_tab_hook', 'my_plugin_settings_general_tab_title' );

/**
 * Display the content for this tab
 */
function my_plugin_settings_general_tab_content(){
	function my_plugin_settings_general() {  
		settings_fields( 'my_plugin_settings_general' );
		do_settings_sections( 'my_plugin_settings_general' );
	}
}
add_action( 'my_plugin_settings_do_settings_hook', 'my_plugin_settings_general_tab_content' );

function my_plugin_settings_general_create(){
	
	//This variable must be the name of the variable that stores the class.
	global $my_plugin_settings_class;
	
	register_setting(
		'my_plugin_settings_general',
		'my_plugin_settings_general',
		'mp_core_settings_validate'
	);
	
	add_settings_section(
		'envato_check_settings',
		__( 'Envato Check Settings', 'my_plugin' ),
		'__return_false',
		'my_plugin_settings_general'
	);
	
	add_settings_field(
		'enable_disable',
		__( 'Enable/Disable Envato Check', 'my_plugin' ), 
		array( &$my_plugin_settings_class, 'select' ),
		'my_plugin_settings_general',
		'envato_check_settings',
		array(
			'name'        => 'enable_disable',
			'value'       => mp_core_get_option( 'my_plugin_settings_general',  'enable_disable' ),
			'description' => __( 'Do you want the Envato Checker to be enabled or disabled?', 'my_plugin' ),
			'registration'=> 'my_plugin_settings_general',
			'options'=> array('enabled', 'disabled')
		)
	);
	
	add_settings_field(
		'envato_username',
		__( 'Envato Username', 'my_plugin' ), 
		array( &$my_plugin_settings_class, 'textbox' ),
		'my_plugin_settings_general',
		'envato_check_settings',
		array(
			'name'        => 'envato_username',
			'value'       => mp_core_get_option( 'my_plugin_settings_general',  'envato_username' ),
			'description' => __( 'Enter your Envato Username', 'my_plugin' ),
			'registration'=> 'my_plugin_settings_general',
		)
	);
	
	add_settings_field(
		'envato_api_key',
		__( 'Envato API Key', 'my_plugin' ), 
		array( &$my_plugin_settings_class, 'textbox' ),
		'my_plugin_settings_general',
		'envato_check_settings',
		array(
			'name'        => 'envato_api_key',
			'value'       => mp_core_get_option( 'my_plugin_settings_general',  'envato_api_key' ),
			'description' => __( 'Enter your Envato API Key', 'my_plugin' ),
			'registration'=> 'my_plugin_settings_general',
		)
	);
	
	add_settings_field(
		'redirect_page',
		__( 'Redirect Page', 'my_plugin' ), 
		array( &$my_plugin_settings_class, 'select' ),
		'my_plugin_settings_general',
		'envato_check_settings',
		array(
			'name'        => 'redirect_page',
			'value'       => mp_core_get_option( 'my_plugin_settings_general',  'redirect_page' ),
			'description' => __( 'Select the page you want to redirect your users to after they create an account', 'my_plugin' ),
			'registration'=> 'my_plugin_settings_general',
			'options'=> $my_plugin_settings_class->get_all_pages() 
		)
	);
	
	add_settings_field(
		'envato_message',
		__( 'Envato Message', 'my_plugin' ), 
		array( &$my_plugin_settings_class, 'wp_editor' ),
		'my_plugin_settings_general',
		'envato_check_settings',
		array(
			'name'        => 'envato_message',
			'value'       => mp_core_get_option( 'my_plugin_settings_general',  'envato_message' ),
			'description' => __( 'This is the message that will appear over the Purchase Code verification form.', 'my_plugin' ),
			'registration'=> 'my_plugin_settings_general',
		)
	);
	
	//additional general settings
	do_action('my_plugin_settings_additional_general_settings_hook');
}
add_action( 'admin_init', 'my_plugin_settings_general_create' );