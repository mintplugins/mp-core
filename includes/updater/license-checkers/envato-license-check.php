<?php
/**
 * Function which takes a license and checks Envato to see if it is active
 *
 * Returns true or false
 */
function mp_core_envato_license_check($args = array() ) {
	
	$args = wp_parse_args( $args, array(
		'software_envato_username' => '',
		'software_envato_api_key' => '',
		'software_envato_item_id' => '',
		'software_license' => '',
	) );
	
	extract( $args );
											
	//Initialize curl
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://marketplace.envato.com/api/edge/' . $software_envato_username . '/' . $software_envato_api_key . '/verify-purchase:' . $software_license . '.json');
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$ch_data = curl_exec($ch);
	curl_close($ch);
	
	// Verify Key		
	$response = json_decode($ch_data, true);
		
	// If Verify Purchase is set and the returned item id == the item ID passed in
	if( isset($response['verify-purchase']['buyer']) && $response['verify-purchase']['item_id'] == $software_envato_item_id ){
	
		//This license is valid
		return true;
		
	}
	else{
		
		//This license is not valid
		return false;
		
	}			
}
	