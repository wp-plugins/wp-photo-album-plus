<?php
/* wppa-users.php
* Package: wp-photo-album-plus
*
* Contains user and capabilities related routines
* Version 5.4.1
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

// Get number of users
function wppa_get_user_count() {
global $wpdb;

	$usercount = $wpdb->get_var( "SELECT COUNT(*) FROM `".$wpdb->users."`" );
	wppa_dbg_q( 'Q-usr' );
	return $usercount;
}

// Get all users
function wppa_get_users() {
global $wpdb;

	if ( wppa_get_user_count() > wppa_opt( 'wppa_max_users' ) ) {
		$users = array();
	}
	else {
		$users = $wpdb->get_results( 
			"SELECT * FROM `".$wpdb->users."` ORDER BY `display_name`", ARRAY_A 
			);
		wppa_dbg_q( 'Q-usr' );
	}
	return $users;
}

// Get user
// If logged in, return userdata as specified in $type
// If logged out, return IP
function wppa_get_user( $type = 'login' ) {
global $current_user;

	if ( is_user_logged_in() ) {
		get_currentuserinfo();
		switch ( $type ) {
			case 'login':
				return $current_user->user_login;
				break;
			case 'display':
				return $current_user->display_name;
				break;
			case 'id':
				return $current_user->ID;
				break;
			case 'firstlast':
				return $current_user->user_firstname.' '.$current_user->user_lastname;
				break;
			default:
				wppa_dbg_msg( 'Un-implemented type: '.$type.' in wppa_get_user()', 'red', 'force' );
				return '';
		}
	}
	else {
		return $_SERVER['REMOTE_ADDR'];
	}
}

// Test if a given user has a given role.
// @1: str role
// @2: int user id, default current user
// returns bool
function wppa_user_is( $role, $user_id = null ) {
 
 	if ( ! is_user_logged_in() ) return false;

	if ( is_numeric( $user_id ) ) {
		$user = get_userdata( $user_id );
	}
    else {
        $user = wp_get_current_user();
	}
 
    if ( empty( $user ) ) {
		return false;
	}
 
    return in_array( $role, ( array ) $user->roles );
}

// Test if current user has extended access
// returns bool
function wppa_extended_access() {

	if ( wppa_user_is( 'administrator' ) ) {
		return true;
	}
	if ( ! wppa_switch( 'wppa_owner_only' ) ) {
		return true;
	}
	return false;
}

// Test if current user is allowed to craete albums
// returns bool
function wppa_can_create_album() {
global $wpdb;

	if ( wppa_is_user_blacklisted() ) {
		return false;
	}
	if ( wppa_extended_access() ) {
		return true;
	}
	if ( wppa_opt( 'wppa_max_albums' ) == '0' ) {
		return true;	// 0 = unlimited
	}
	$user = wppa_get_user();
	$albs = $wpdb->get_var( $wpdb->prepare( 
		"SELECT COUNT(*) FROM `".WPPA_ALBUMS."` WHERE `owner` = %s", $user 
		) );
	wppa_dbg_q( 'Q-cca' );
	if ( $albs < wppa_opt( 'wppa_max_albums' ) ) {
		return true;
	}
	
	return false;
}

// Test if current user is allowed to craete top level albums
// returns bool
function wppa_can_create_top_album() {

	if ( wppa_user_is( 'administrator' ) ) {
		return true;
	}
	if ( ! wppa_can_create_album() ) {
		return false;
	}
	if ( wppa_switch( 'wppa_grant_an_album' ) && 
		'0' != wppa_opt( 'wppa_grant_parent' ) ) {
			return false;
		}
	
	return true;
}

// Test if a user is on the blacklist
// @1: user id, default current user
// returns bool
function wppa_is_user_blacklisted( $user = -1 ) {
global $wpdb;
static $result = -1;

	$cur = ( -1 == $user );
	
	if ( $cur && -1 != $result ) {	// Already found out for current user
		return $result;
	}
	
	if ( $cur && ! is_user_logged_in() ) {	// An logged out user can not be on the blacklist
		$result = false;
		return false;
	}
	
	$blacklist = get_option( 'wppa_black_listed_users', array() );
	if ( empty( $blacklist ) ) {	// Anybody on the blacklist?
		$result = false;
		return false;
	}
	
	if ( $cur ) {
		$user = get_current_user_id();
	}
	
	if ( is_numeric( $user ) ) {
		$user = $wpdb->get_var( $wpdb->prepare( 
			"SELECT `user_login` FROM `".$wpdb->users."` WHERE `ID` = %d", $user 
		) );
		wppa_dbg_q( 'Q-iub' );
	}
	else {
		return false;
	}
	
	if ( $cur ) {
		$result = in_array( $user, $blacklist );	// Save current users result.
	}
	
	return in_array( $user, $blacklist );
}
