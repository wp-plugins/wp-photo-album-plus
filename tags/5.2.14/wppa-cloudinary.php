<?php
/* Only loads when php version >= 5.3 
*
* Version 5.2.3
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

function wppa_upload_to_cloudinary( $id ) {

	$prefix = ( is_multisite() && ! WPPA_MULTISITE_GLOBAL ) ? $blog_id.'-' : '';
	$pub_id = $prefix.$id;
	$file 	= wppa_get_photo_path( $id );
	$args 	= array(	"public_id" 	=> $pub_id,
						"version"		=> get_option('wppa_photo_version', '1'),
						"invalidate" 	=> true
					);
					
	\Cloudinary\Uploader::upload( $file, $args );
	
}

function wppa_get_present_at_cloudinary_a() {
global $wppa_cloudinary_api;

	if ( ! session_id() ) @ session_start();
	
	if ( isset( $_SESSION['cloudinary_ids'] ) ) return $_SESSION['cloudinary_ids']; 	// Been here
	$_SESSION['cloudinary_ids'] = array();
	
	$data = $wppa_cloudinary_api->resources( array( "type" => "upload", 
													"max_results" => 500));
	$done = false;
	while ( ! $done ) {
		$temp = get_object_vars ( $data );
		foreach ( $temp['resources'] as $res ) {
			$_SESSION['cloudinary_ids'][$res['public_id']] = true;
		}
		if ( isset( $temp['next_cursor'] ) ) {
			$data = $wppa_cloudinary_api->resources( array( "type" => "upload", 
															"next_cursor" => $temp['next_cursor'],
															"max_results" => 500));
		}
		else {
			$done = true;
		}
	}

	return $_SESSION['cloudinary_ids'];
}

function wppa_ready_on_cloudinary() {
	if ( ! session_id() ) @ session_start();
	if ( isset ( $_SESSION['cloudinary_ids'] ) ) unset( $_SESSION['cloudinary_ids'] );
}

function wppa_delete_from_cloudinary( $id ) {

	$prefix = ( is_multisite() && ! WPPA_MULTISITE_GLOBAL ) ? $blog_id.'-' : '';
	$pub_id =  $prefix.$id;
	$args 	= array(	"invalidate" 	=> true
					);
					
	\Cloudinary\Uploader::destroy( $pub_id, $args );				

}

function wppa_delete_all_from_cloudinary() {
global $wppa_cloudinary_api;

	$wppa_cloudinary_api->delete_all_resources();
}
