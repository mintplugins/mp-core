<?php			

function mp_theme_options_general_create(){
	global $theme_options;
	register_setting(
		'mp_theme_options_general',
		'mp_theme_options_general',
		'mp_theme_options_general_validate'
	);
	
	add_settings_section(
		'slider_settings',
		__( 'Slider Settings', 'mp_core' ),
		'__return_false',
		'mp_theme_options_general'
	);
	
	add_settings_field(
		'featured_category',
		__( 'Featured Slider Category', 'mp_core' ), 
		array( &$theme_options, 'mediaupload' ),
		'mp_theme_options_general',
		'slider_settings',
		array(
			'name'        => 'featured_category',
			'value'       => $theme_options->mp_core_get_option( 'mp_theme_options_general',  'featured_category' ),
			'description' => __( 'Posts in this category will be used on the homepage&#39;s slider.', 'mp_core' ),
			'registration'=> 'mp_theme_options_general'
		)
	);
	
	//additional general settings
	do_action('mp_plugin_options_additional_general_settings_hook');
}
add_action( 'admin_init', 'mp_theme_options_general_create' );

/**
 * Display tab at top of Theme Options page
 */
function mp_theme_options_general_tab_title($active_tab){ 
	if ($active_tab == 'mp_theme_options_general'){ $active_class = 'nav-tab-active'; }else{$active_class = "";}
	echo ('<a href="?page=mp_theme_options&tab=mp_theme_options_general" class="nav-tab ' . $active_class . '">General Options</a>');
}
add_action( 'mp_theme_options_new_tab_hook', 'mp_theme_options_general_tab_title' );

/**
 * Display the content for this tab
 */
function mp_theme_options_general_tab_content(){
	function mp_theme_options_general() {  
		settings_fields( 'mp_theme_options_general' );
		do_settings_sections( 'mp_theme_options_general' );
	}
}
add_action( 'mp_theme_options_do_settings_hook', 'mp_theme_options_general_tab_content' );

/**
 * Sanitize and validate form input. Accepts an array, return a sanitized array.
 *
 * @param array $input Unknown values.
 * @return array Sanitized theme options ready to be stored in the database.
 *
 * @since Lighthouse 1.0
 */
function mp_theme_options_general_validate( $input ) {
	global $theme_options;
	$output = array();
	
	foreach ($input as $key => $option){
		if ( isset ( $option ) )
		$output[ $key ] = esc_attr( $option );
	}	
	
	$output = wp_parse_args( $output,$theme_options->mp_core_get_option( 'mp_theme_options_general' ) );	
		
	return apply_filters( 'mp_theme_options_general_validate', $output, $input );
}