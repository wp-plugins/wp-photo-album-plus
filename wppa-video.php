<?php
/* wppa-video.php
* Package: wp-photo-album-plus
*
* Contains all video routines
* Version 5.3.11
*
*/
 
if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

if ( is_file( dirname( __FILE__ ).'/wppa-video-working.php' ) ) {
	require_once 'wppa-video-working.php';
}
else {

	// This is a dummy library. Video support is under development and not released yet.

	function wppa_is_video_enabled() {
		return false;
	}

	function wppa_is_video( $id, $ret_ext = false ) {
		return false;
	}

	function wppa_get_video_html( $args ) {
		return '';
	}

	function wppa_get_video_body( $id ) {
		return '';
	}

	function wppa_delete_video( $id ) {
		return;
	}

	function wppa_copy_video_files( $fromid, $toid ) {
		return false;
	}

	function wppa_get_videox( $id ) {
		return '0';
	}

	function wppa_get_videoy( $id ) {
		return '0';
	}

}