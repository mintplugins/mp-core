<?php
/**
 * Variables to create new metabox
 *
 */
$add_meta_box = array(
	'metabox_id' => 'my_metabox', 
	'metabox_title' => __('My Metabox', 'mp_core'), 
	'metabox_posttype' => 'page', 
	'metabox_context' => 'advanced', 
	'metabox_priority' => 'high' 
);

/**
 * Variables to create fields inside the above metabox
 *
 */
$items_array = array(
	array(
		'field_id'			=> 'cd_instructions',
		'field_title' 	=> __( 'Instructions', 'mp_core' ),
		'field_description' 	=> __( 'Here\'s some basic instructions:', 'mp_core' ),
		'field_type' 	=> 'basictext',
		'field_repeater' => 'cd_repeater'
	),
	array(
		'field_id' 			=> 'cd_cover',
		'field_title' 	=>  __('CD Cover','mp_core' ),
		'field_description' 	=> __( 'Select the CD Cover:','mp_core' ),
		'field_type' 	=> 'mediaupload',
		'field_repeater' => 'cd_repeater'
	), 
	array(
		'field_id' 			=> 'cd_title',
		'field_title' 	=>  __('CD Title','mp_core' ),
		'field_description' 	=> __( 'Enter the cd title:','mp_core' ),
		'field_type' 	=> 'textbox',
		'field_repeater' => 'cd_repeater'
	), 
	array(
		'field_id' 			=> 'cd_description',
		'field_title' 	=>  __('CD Description','mp_core' ),
		'field_description' 	=> __( 'Enter the cd description:','mp_core' ),
		'field_type' 	=> 'textarea',
		'field_repeater' => 'cd_repeater'
	), 
	array(
		'field_id' 			=> 'cd_colors',
		'field_title' 	=>  __('CD Color','mp_core' ),
		'field_description' 	=> __( 'Select the color','mp_core' ),
		'field_type' 	=> 'colorpicker',
		'field_repeater' => 'cd_repeater'
	)
);


//Custom filter to allow for add on plugins to hook in their own data for add_meta_box array
$add_meta_box = has_filter('mp_custom_plugin_meta_box_array') ? apply_filters( 'mp_custom_plugin_meta_box_array', $add_meta_box) : $add_meta_box;
//Custom filter to allow for add on plugins to hook in their own extra fields 
$items_array = has_filter('mp_custom_plugin_items_array') ? apply_filters( 'mp_custom_plugin_items_array', $items_array) : $items_array;

function mp_paintings_additional_items_array($items_array) {
    $items_array[0] .= 
        array(
        'field_id'  => 'cd_genre',
        'field_title'  =>  __('CD Genre','mp_core' ),
        'field_description'  => __( 'Select the genre','mp_core' ),
        'field_type'  => 'select',
        'field_repeater' => 'cd_repeater',
        'field_select_values' => array('Rock', 'Blues', 'Pop', 'Country', 'Metal', 'Folk', 'Other')  
    );
    return $items_array;
}
add_filter('mp_custom_plugin_items_array','mp_paintings_additional_items_array');

//Create Metabox class
global $my_metabox;
$my_metabox = new mp_core_New_Metabox($add_meta_box, $items_array);


//Get all the fields in a specific repeater to an array
$color_fields = $my_metabox->get_repeater_field('1402', 'cd_repeater');
//Get a specific field from that array
//echo $color_fields[1]['cd_repeatcolor3s']['field_value'];

//Add to the class name for any repeater listed above. Just make the 'my_custom_repeater' match your repater name in the items_array
add_filter('mp_core_' . 'coloursaregreat' . '_custom_class','my_repeater_class_name');
function my_repeater_class_name() {
	$classes = ' my-test-class1 my-test-class2';
    return $classes;
}

function mycustomscript(){
	//custom script
	wp_enqueue_script( 'customjs', plugins_url( '/mp_core/js/customjs.js' ),  array( 'jquery' ) );	
	//custom style
	wp_enqueue_style( 'customjs', plugins_url( '/mp_core/js/customjs.js' ),  array( 'jquery' ) );	
}
add_action('mp_core_' . $add_meta_box['metabox_id'] . '_metabox_custom_scripts', 'mycustomscript');