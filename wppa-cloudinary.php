<?php
/* Only loads when php version >= 5.3 
*
* Version 5.2.2
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

function wppa_exists_on_cloudinary( $id ) {
global $thumb;

	wppa_cache_thumb($id);
	
	$prefix = ( is_multisite() && ! WPPA_MULTISITE_GLOBAL ) ? $blog_id.'-' : '';
	$url 	= 'http://res.cloudinary.com/'.get_option('wppa_cdn_cloud_name').'/image/upload/'.$prefix.$id.'.'.$thumb['ext'];
	
	if ( @ getimagesize( $url ) ) {
		return true;
	}
	
	return false;
	
	//			global $wppa_cloudinary_api;
	// try {
	//			$dtl = $wppa_cloudinary_api->resource($prefix.$thumb['id']);
	//			if ( ! empty($dtl) ) return true;
	//		}
	// catch {
	//			return false;
	//		}

}

function wppa_upload_to_cloudinary( $id ) {

	$prefix = ( is_multisite() && ! WPPA_MULTISITE_GLOBAL ) ? $blog_id.'-' : '';
	$file 	= wppa_get_photo_path( $id );
	$args 	= array(	"public_id" 	=> $prefix.$id,
						"version"		=> get_option('wppa_photo_version', '1')
					);
					
	\Cloudinary\Uploader::upload( $file, $args );
	
}