<?php
/* wppa-session.php
* Package: wp-photo-album-plus
*
* Contains all session routines
* Version 5.1.14
*
* Firefox modifies data in the superglobal $_SESSION.
* See https://bugzilla.mozilla.org/show_bug.cgi?id=991019
* The use of $_SESSION data is therefor no longer reliable
* This file contains routines to obtain the same functionality, but more secure.
* In the application use the global $wppa_session in stead of $_SESSION['wppa_session']
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );
	
// Generate a unique session id
function wppa_get_session_id() {
global $wppa_api_version; 
	$id = md5( $_SERVER['REMOTE_ADDR'] . wppa_get_user() . $_SERVER["HTTP_USER_AGENT"] . $wppa_api_version );
	return $id;
}

// Start a session or retrieve the sessions data. To be called at init.
function wppa_session_start() {
global $wpdb;
global $wppa_session;

	if ( is_array( $wppa_session ) ) return true; 	// To prevent dup opens
	
	// If the session table does not yet exist on activation
	if ( ! wppa_table_exists( WPPA_SESSION ) ) {
		$wppa_session['id'] = '0';
		return false;
	}

	// Cleanup first
	$lifetime = 3600;			// Sessions expire after one hour
	$savetime = 3600;	// Save session data for 1 hour // 30 days
	$expire = time() - $lifetime;
	$wpdb->query( $wpdb->prepare( "UPDATE `" . WPPA_SESSION . "` SET `status` = 'expired' WHERE `timestamp` < %s", $expire ) );
	$purge = time() - $savetime;
	$wpdb->query( $wpdb->prepare( "DELETE FROM `" . WPPA_SESSION ."` WHERE `timestamp` < %s", $purge ) );
	
	// Is session already started?
	$session = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `".WPPA_SESSION."` WHERE `session` = %s AND `status` = 'valid' LIMIT 1", wppa_get_session_id() ), ARRAY_A );
	
	$data = isset( $session['data'] ) ? $session['data'] : false;
	
	if ( $data === false ) {
		$iret = false;
		$tries = '0';
		while ( ! $iret && $tries < '10' ) {
			$iret = wppa_create_session_entry( array() );
			if ( ! $iret ) {
				sleep(1);
				$tries++;
			}
		}
		if ( $tries > '3' && $iret ) {
			wppa_log( 'Debug', 'It took '.$tries.' retries to start session '.$iret );
		}
		if ( ! $iret ) {
			wppa_log( 'Error', 'Unable to create session.' );
			return false;
		}
		$wppa_session['page'] = '1';
		$wppa_session['ajax'] = '0';
		$wppa_session['id'] = $wpdb->get_var( $wpdb->prepare( "SELECT `id` FROM `".WPPA_SESSION."` WHERE `session` = %s LIMIT 1", wppa_get_session_id() ) ); 
	}
	else { 	// Update counter
		$wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_SESSION."` SET `count` = %s WHERE `id` = %s", $session['count'] + '1', $session['id'] ) );
		$wppa_session = unserialize( $data );
		$wppa_session['page'] = isset ( $wppa_session['page'] ) ? $wppa_session['page'] + '1' : '1';
		$wppa_session['id'] = $session['id'];
	}
	
	return true;
}

// Saves the session data. To be called at shutdown
function wppa_session_end() {
global $wpdb;
global $wppa_session;

	if ( ! wppa_get_session_id() ) return false;
	
	$iret = $wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_SESSION."` SET `data` = %s WHERE `session` = %s", serialize( $wppa_session ), wppa_get_session_id() ) );
	
	if ( $iret === false ) {
		wppa_log( 'Error', 'Unable to save session.' );
		return false;
	}

	return true;
}