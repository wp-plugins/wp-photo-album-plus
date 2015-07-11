<?php
/* Only loads when php version >= 5.3 
*
* Version 6.2.0
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

add_action('init', 'wppa_load_cloudinary');
function wppa_load_cloudinary() {
	
	$cdn = get_option('wppa_cdn_service', 'nil');
	
	if ( $cdn != 'cloudinary' && $cdn != 'cloudinarymaintenance' ) return;
	
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
	
	$args 	= array(	"public_id" 	=> $prefix.$id,
						"version"		=> get_option('wppa_photo_version', '1'),
						"invalidate" 	=> true
					);
					
	// Try source first
	$file 	= wppa_get_source_path( $id );
	
	// No source, use photofile
	if ( ! is_file( $file ) ) {
		$file 	= wppa_get_photo_path( $id );
	}
	
	// Doit
	if ( is_file ( $file ) ) {
		\Cloudinary\Uploader::upload( $file, $args );
	}
	
}

function wppa_get_present_at_cloudinary_a() {
global $wppa_cloudinary_api;
global $wppa_session;
	
	if ( isset( $wppa_session['cloudinary_ids'] ) ) return $wppa_session['cloudinary_ids']; 	// Been here
	$wppa_session['cloudinary_ids'] = array();
	
	$t0 = microtime( true );
	
	$data = $wppa_cloudinary_api->resources( array( "type" => "upload", 
													"max_results" => 500));
	$done = false;
	while ( ! $done ) {
		$temp = get_object_vars ( $data );
		foreach ( $temp['resources'] as $res ) {
			$wppa_session['cloudinary_ids'][$res['public_id']] = true;
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

	$t1 = microtime( true );
	
	echo sprintf( 'Get present at cloudinary took %6.2f seconds.<br />', $t1-$t0 );
	
	return $wppa_session['cloudinary_ids'];
}

function wppa_ready_on_cloudinary() {
	if ( isset ( $wppa_session['cloudinary_ids'] ) ) unset( $wppa_session['cloudinary_ids'] );
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

	$data = $wppa_cloudinary_api->delete_all_resources();
	$temp = get_object_vars( $data );
	
	if ( isset( $temp['next_cursor'] ) ) return false;
	return true;
}

function wppa_delete_derived_from_cloudinary() {
global $wppa_cloudinary_api;

	$data = $wppa_cloudinary_api->delete_all_resources( array( "keep_original" => TRUE	) );
	$temp = get_object_vars( $data );
	
	if ( isset( $temp['next_cursor'] ) ) return false;
	return true;
}

function wppa_get_cloudinary_url( $id, $test_only = false ) {
global $blog_id;

	$thumb 		= wppa_cache_thumb( $id );
	$ext 		= $thumb['ext'] == 'xxx' ? 'jpg' : $thumb['ext'];
	$prefix 	= ( is_multisite() && ! WPPA_MULTISITE_GLOBAL ) ? $blog_id.'-' : '';
	$size 		= $test_only ? 'h_144/' : '';
	$s 			= is_ssl() ? 's' : '';
	
	$url = 'http'.$s.'://res.cloudinary.com/'.get_option('wppa_cdn_cloud_name').'/image/upload/'.$size.$prefix.$id.'.'.$ext;

	return $url;
}

function wppa_get_cloudinary_usage() {
global $wppa_cloudinary_api;

	if ( $wppa_cloudinary_api ) {
		return get_object_vars( $wppa_cloudinary_api->usage() );
	}
	else {
		return false;
	}
}