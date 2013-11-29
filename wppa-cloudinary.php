<?php
/* Only loads when php version >= 5.3 
*
* Version 5.2.1
*
*/

add_action('init', 'wppa_load_cloudinary');
function wppa_load_cloudinary() {
	if ( get_option('wppa_cdn_service', 'nil') != 'cloudinary' ) return;
	
	require_once 'cloudinary/src/Cloudinary.php';
	require_once 'cloudinary/src/Uploader.php';
	require_once 'cloudinary/src/Api.php';
	
	\Cloudinary::config(array( 
		"cloud_name" 	=> get_option('wppa_cdn_cloud_name'), 
		"api_key" 		=> get_option('wppa_cdn_api_key'), 
		"api_secret" 	=> get_option('wppa_cdn_api_secret')
	));
	
	global $wppa_cloudinary_api;
	$wppa_cloudinary_api = new \Cloudinary\Api();
}