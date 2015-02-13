<?php
/**
 * This file contains the MP_CORE_Verify_License class
 *
 * @link http://mintplugins.com/doc/verify-license-class/
 * @since 1.0.0
 *
 * @package    MP Core
 * @subpackage Classes
 *
 * @copyright  Copyright (c) 2014, Mint Plugins
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @author     Philip Johnston
 */
 
/**
 * This class check with an outside API to verify whether it is active/valid. 
 * It takes the name of the plugin or theme, and the Software API URL it should check for verification.
 *
 * @author     Philip Johnston
 * @link       http://mintplugins.com/doc/verify-license-class/
 * @since      1.0.0
 * @return     void
 */

/**
 * Function which stores and verifies a license using the mp_repo plugin on the software_api_url
 *
 * @access   public
 * @since    1.0.0
 * @see      check_admin_referer()
 * @see      get_option()
 * @see      update_option()
 * @see      wp_remote_post()
 * @see      wp_remote_retrieve_body()
 * @param    array $args Info about where to check and what license and whether to store it.
 * @return   boolean True if the license is valid, False if not.
 */
function mp_core_verify_license( $args ){		
		
	//Set defaults for args		
	$args_defaults = array(
		'software_name'      => NULL,
		'software_api_url'   => NULL,
		'software_license_key'   => NULL,
		'software_store_license' => true,
	);
	
	//Get and parse args
	$args = wp_parse_args( $args, $args_defaults );
	
	//If the args passed have 'plugin' as the prefix, change that to 'software'
	$args['software_name'] = isset( $args['plugin_name'] ) ? $args['plugin_name'] : $args['software_name'];
	$args['software_api_url'] = isset( $args['plugin_api_url'] ) ? $args['plugin_api_url'] : $args['software_api_url'];
	
	//Software/Theme/Plugin Name Slug
	$software_name_slug = sanitize_title ( $args['software_name'] ); //EG move-plugins-core	
									
	//Retrieve the license from the $_POST
	$args['software_license_key'] = trim( $args['software_license_key'] );
	
	//Old License Key
	$old_license_key = get_option( $software_name_slug . '_license_key' );	 
	
	//If we should store this license in the database
	if ( $args['software_store_license'] == true ){
		//Sanitize and update license
		update_option( $software_name_slug . '_license_key', wp_kses(htmlentities($args['software_license_key'], ENT_QUOTES), '' ) );	 
	}
	
	if(substr($args['software_api_url'], -1) == '/') {
		$args['software_api_url'] = substr($args['software_api_url'], 0, -1);
	}
								
	//Check the response from the repo if this license is valid					
	$mp_repo_response = wp_remote_post( $args['software_api_url']  . '/repo/' . $software_name_slug . '/?license_check=true&license_key=' . $args['software_license_key'] . '&site_activating="' . get_bloginfo( 'wpurl' ) . '"&old_license_key=' . $old_license_key, array( 'method' => 'POST', 'timeout' => 15, 'sslverify' => false ) );
												
	//Retreive the body from the response - which should only have a 1 or a 0
	$mp_repo_response_boolean = ( json_decode( wp_remote_retrieve_body( $mp_repo_response ) ) );
	
	//If we should store the validity of this license in the database	
	if ( $args['software_store_license'] == true ){			
	
		//Check and Update Licence
		update_option( $software_name_slug . '_license_status_valid', $mp_repo_response_boolean );
	}
	
	//Return whether the license is valid or not
	return $mp_repo_response_boolean;
	
}

/**
 * Dual purpose function which returns a license's previously-saved validity 
 * OR, it listens for a newly posted license in the $_POST and returns its validity - if it exists
 *
 * @access   public
 * @since    1.0.0
 * @see      check_admin_referer()
 * @see      sanitize_title()
 * @see      mp_core_verify_license()
 * @see      get_option()
 * @param    array $args Info about where to check and what license and whether to store it.
 * @return   boolean True if the license is valid, False if not.
 */
function mp_core_listen_for_license_and_get_validity( $plugin_args ){
		
	$plugin_name_slug = sanitize_title($plugin_args['plugin_name']);
						
	//If there's a license waiting in the $_POST var for this plugin
	if( isset( $_POST[ $plugin_name_slug . '_license_key' ] ) ) {
				
		//Check nonce
		if( ! check_admin_referer( $plugin_name_slug . '_nonce', $plugin_name_slug . '_nonce' ) ) 	
			return false; // get out if we didn't click the Activate button
						
		$verify_license_args = array(
			'software_name'      => $plugin_args['plugin_name'],
			'software_api_url'   => $plugin_args['plugin_api_url'],
			'software_license_key'   => $_POST[ $plugin_name_slug . '_license_key' ],
			'software_store_license' => true, //Store this newly submitted license
		);
				
		//Check and return the validity of this license
		return mp_core_verify_license( $verify_license_args );
		
	}
	//Otherwise, return the validity of this license previously stored in the database
	else{
		return get_option( $plugin_name_slug . '_license_status_valid' );
	}
}