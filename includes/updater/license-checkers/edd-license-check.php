<?php
/*
 * Function which takes a license
 * Attempts to activate it
 * and checks EDD to see if it is active
 *
 * Returns true or false
 */
function mp_core_edd_license_check($args = array() ) {
	
	$args = wp_parse_args( $args, array(
		'software_api_url' => 'http://moveplugins.com',
		'software_name'    => '',
		'software_license' => '',
	) );
	
	$plugin_name_slug = sanitize_title ( $args['software_name'] ); //EG move-plugins-core
	
	extract( $args );
	
	/***********************************************
	* Activate the license
	***********************************************/	
		
	// data to send in our API request
	$api_params = array( 
		'edd_action'=> 'activate_license', 
		'license' 	=> $software_license, 
		'item_name' => $plugin_name_slug // the name of our product in EDD
	);
	
	// Call the custom API.
	$response = wp_remote_get( add_query_arg( $api_params, $software_api_url ) );
		
	// make sure the response came back okay
	if ( is_wp_error( $response ) )
		return false;

	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	// $license_data->license will be either "active" or "inactive"
	update_option( $plugin_name_slug . '_license_status', $license_data->license );
	
	/***********************************************
	* Check if the license is valid
	***********************************************/				
	$api_params = array( 
		'edd_action' => 'check_license', 
		'license' => $software_license, 
		'item_name' => urlencode( $software_name ) 
	);
	
	$response = wp_remote_get( add_query_arg( $api_params, $software_api_url ), array( 'timeout' => 15, 'sslverify' => false ) );

	if ( is_wp_error( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );
	
	// If license is valid
	if( $license_data->license == 'valid' ) {
		
		// this license is valid
		return true;
		
	} else {
		
		// this license is not valid
		return false;

	}
}	