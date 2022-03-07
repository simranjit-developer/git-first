<?php
/**
 * Plugin Name: API Token Authentication
 **/

 /* METHOD WILL MATCH THE BEARER TOKEN WITH AN API */
function auth_check_api($token_id, $location_id)
{
	$return = false;
	if($token_id && $location_id){

		/* API TO MATCH TOKEN ID AND LOCATION ID */	
		$auth_check_api_url = 'https://pranoy-dot-highlevel-staging.appspot.com/wordpress/authenticate?location_id='.$location_id;
		$res = '';
		$response = wp_remote_get( $auth_check_api_url, array('headers' => array(
			'token-id' =>$token_id
		)));
		
		$res = $response['body'];
		if(!empty($response)){
			$res = $response['body'];
			if($res =='OK'){
				$return = true;
			}
		}
	}
	return $return;
}

/* IF TOKEN IS CORRECT THEN SEND REPONSE TO API SERVER */
function gohighlevel_auth_handler( $user ) 
{
	//GET TOKEN-ID AND LOCATION-ID FROM CLIENT API 
	$headers = apache_request_headers();
	
	global $wpdb;
	$admin_user_id ="";
	
	//THIS METHOD WILL RETURN TRUE AND FALSE
	if(isset($headers['token-id'])&& isset($headers['location-id']))
	{
		
		$token_api = auth_check_api($headers['token-id'] ,$headers['location-id'] );
		
		if($token_api){
			$wp_user_search = $wpdb->get_row("SELECT u.ID, u.user_login FROM wp_users u, wp_usermeta m WHERE u.ID = m.user_id AND m.meta_key LIKE 'wp_capabilities' AND m.meta_value LIKE '%administrator%'");
			if(!empty($wp_user_search)){
			  $admin_user_id = $wp_user_search->ID;
			}

			return $admin_user_id;
		}else{
			
			return false;
		}
	}//token if end here
	
	return $user;	
	
} 
 
add_filter( 'determine_current_user', 'gohighlevel_auth_handler', 20 );
