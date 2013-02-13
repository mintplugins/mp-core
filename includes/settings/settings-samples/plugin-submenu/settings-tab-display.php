<?php			

function mp_plugin_submenu_display_create(){
	global $plugin_submenu;
	register_setting(
		'mp_plugin_submenu_display',
		'mp_plugin_submenu_display',
		'mp_plugin_submenu_display_validate'
	);
	
	add_settings_section(
		'slider_settings',
		__( 'Slider Settings', 'mp_core' ),
		'__return_false',
		'mp_plugin_submenu_display'
	);
	
	add_settings_field(
		'featured_category',
		__( 'Featured Slider Category', 'mp_core' ), 
		array( &$plugin_submenu, 'colorpicker' ),
		'mp_plugin_submenu_display',
		'slider_settings',
		array(
			'name'        => 'featured_category',
			'value'       => $plugin_submenu->mp_core_get_option( 'mp_plugin_submenu_display',  'featured_category' ),
			'description' => __( 'Posts in this category will be used on the homepage&#39;s slider.', 'mp_core' ),
			'registration'=> 'mp_plugin_submenu_display'
		)
	);
	
	//additional display settings
	do_action('mp_plugin_submenu_additional_display_settings_hook');
}
add_action( 'admin_init', 'mp_plugin_submenu_display_create' );

/**
 * Display tab at top of Theme Options page
 */
function mp_plugin_submenu_display_tab_title($active_tab){ 
	if ($active_tab == 'mp_plugin_submenu_display'){ $active_class = 'nav-tab-active'; }else{$active_class = "";}
	echo ('<a href="?page=mp_plugin_submenu&tab=mp_plugin_submenu_display" class="nav-tab ' . $active_class . '">Display Options</a>');
}
add_action( 'mp_plugin_submenu_new_tab_hook', 'mp_plugin_submenu_display_tab_title' );

/**
 * Display the content for this tab
 */
function mp_plugin_submenu_display_tab_content(){
	function mp_plugin_submenu_display() {  
		settings_fields( 'mp_plugin_submenu_display' );
		do_settings_sections( 'mp_plugin_submenu_display' );
	}
}
add_action( 'mp_plugin_submenu_do_settings_hook', 'mp_plugin_submenu_display_tab_content' );

/**
 * Sanitize and validate form input. Accepts an array, return a sanitized array.
 *
 * @param array $input Unknown values.
 * @return array Sanitized theme options ready to be stored in the database.
 *
 * @since Lighthouse 1.0
 */
function mp_plugin_submenu_display_validate( $input ) {
	global $plugin_submenu;
	$output = array();
	
	foreach ($input as $key => $option){
		if ( isset ( $option ) )
		$output[ $key ] = esc_attr( $option );
	}	
	
	$output = wp_parse_args( $output,$plugin_submenu->mp_core_get_option( 'mp_plugin_submenu_display' ) );	
		
	return apply_filters( 'mp_plugin_submenu_display_validate', $output, $input );
}