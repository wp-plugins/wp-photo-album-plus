<?php
/* wppa-htaccess.php
* Package: wp-photo-album-plus
*
* Various funcions
* Version 5.4.5
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

// Create .htaccess in the .../uploads/wppa folder to grant normal http access to photo files
function wppa_create_wppa_htaccess() {
	$file = fopen( WPPA_UPLOAD_PATH . '/.htaccess', 'wb' );
	if ( $file ) {
		fwrite( $file, '<IfModule mod_rewrite.c>' );
		fwrite( $file, "\n" . 'RewriteEngine Off' );
		fwrite( $file, "\n" . '</IfModule>' );
		fclose( $file );
	}
}

// Create .../wp-content/wppa-pl and .../wp-content/wppa-pl/.htaccess to support permalinks for photo source files
function wppa_create_pl_htaccess( $pl_dirname = '' ) {
global $wpdb;

	// Only supported on single sites at the moment
	if ( is_multisite() && ! WPPA_MULTISITE_GLOBAL ) {
		return false;
	}
	
	// Where are the photo source files?
	$source_root = str_replace( ABSPATH, '', wppa_opt( 'wppa_source_dir' ) );

	// Find permalink root name
	if ( ! $pl_dirname ) {
		$pl_dirname = wppa_opt( 'wppa_pl_dirname' );
	}
	
	// If no pl_dirname, use default
	if ( ! $pl_dirname ) {
		$pl_dirname = 'wppa-pl';
	}
	
	// Create pl root directory
	$pl_root = WPPA_CONTENT_PATH . '/' . $pl_dirname;
	if ( ! wppa_mktree( $pl_root ) ) {
		wppa_log( 'Error', 'Can not create '.$pl_root );
		return false;
	}
	
	// Create .htaccess file
	$file = fopen( $pl_root . '/.htaccess', 'wb' );
	if ( ! $file ) {
		wppa_log( 'Error', 'Can not create '.$pl_root . '/.htaccess' );
		return false;
	}
	
	fwrite( $file, '<IfModule mod_rewrite.c>' );
	fwrite( $file, "\n" . 'RewriteEngine On' );
	// RewriteBase /wp-content/wppa-pl
	fwrite( $file, "\n" . 'RewriteBase /' . str_replace( ABSPATH, '', $pl_root ) );
	$albs = $wpdb->get_results( "SELECT `id`, `name` FROM `".WPPA_ALBUMS."` ORDER BY `id`", ARRAY_A );
	if ( $albs ) foreach( $albs as $alb ) {
		$fm = sanitize_file_name( $alb['name'] );
		$to = $source_root . '/album-'.$alb['id'];
		fwrite( $file, "\n" . 'RewriteRule ^'.$fm.'(.*) /'.$to.'$1 [NC]' );
	}	
	fwrite( $file, "\n" . '</IfModule>' );
	fclose( $file );
	return true;
}

