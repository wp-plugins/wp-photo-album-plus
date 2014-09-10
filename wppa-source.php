<?php
/* wppa-source.php
* Package: wp-photo-album-plus
*
* Contains photo source file management routines
* Version 5.4.9
*
*/
 
if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );
	
function wppa_save_source( $file, $name, $alb ) {
global $wppa_opt;

	if ( ( wppa_switch('wppa_keep_source_admin') && is_admin() ) || ( wppa_switch('wppa_keep_source_frontend') && ! is_admin() ) ) {
		if ( ! is_dir( $wppa_opt['wppa_source_dir'] ) ) @ wppa_mktree( $wppa_opt['wppa_source_dir'] );
		$sourcedir = wppa_get_source_dir();
		if ( ! is_dir( $sourcedir ) ) @ wppa_mktree( $sourcedir );
		$albdir = wppa_get_source_album_dir( $alb ); 
		if ( ! is_dir( $albdir ) ) @ wppa_mktree( $albdir );	
		$dest = $albdir . '/' . wppa_sanitize_file_name( $name );
		if ( $file != $dest ) @ copy( $file, $dest );	// Do not copy to self, and do not bother on failure
	}
}

function wppa_delete_source( $name, $alb ) {
global $wppa_opt;
	if ( wppa_switch('wppa_keep_sync') ) {
		$path = wppa_get_source_album_dir( $alb ).'/'.$name;
		@ unlink( $path );										// Ignore error
		@ rmdir( wppa_get_source_album_dir( $alb ) );	// Ignore error
	}
}

function wppa_move_source( $name, $from, $to ) {
global $wppa_opt;
	if ( wppa_switch('wppa_keep_sync') ) {
		$frompath 	= wppa_get_source_album_dir( $from ).'/'.$name;
		if ( ! is_file( $frompath ) ) return;
		$todir 		= wppa_get_source_album_dir( $to );
		$topath 	= wppa_get_source_album_dir( $to ).'/'.$name;
		if ( ! is_dir( $todir ) ) @ wppa_mktree( $todir );
		@ rename( $frompath, $topath );		// will fail if target already exists
		@ unlink( $frompath );				// therefor attempt delete
		@ rmdir( wppa_get_source_album_dir( $from ) );	// remove dir when empty Ignore error
	}
}

function wppa_copy_source( $name, $from, $to ) {
global $wppa_opt;
	if ( wppa_switch('wppa_keep_sync') ) {
		$frompath 	= wppa_get_source_album_dir( $from ).'/'.$name;
		if ( ! is_file( $frompath ) ) return;
		$todir 		= wppa_get_source_album_dir( $to );
		$topath 	= wppa_get_source_album_dir( $to ).'/'.$name;
		if ( ! is_dir( $todir ) ) @ wppa_mktree( $todir );
		@ copy($frompath, $topath); // !
	}
}

function wppa_delete_album_source( $album ) {
global $wppa_opt;
	if ( wppa_switch('wppa_keep_sync') ) {
		@ rmdir( wppa_get_source_album_dir( $album ) );
	}
}
