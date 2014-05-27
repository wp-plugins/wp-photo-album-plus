<?php
/* wppa-wpdb-update.php
* Package: wp-photo-album-plus
*
* Contains low-level wpdb routines that update records
* Version 5.3.9
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

// Album
function wppa_update_album( $args ) {
global $wpdb;
global $wppa_opt;
global $album;

	if ( ! is_array( $args ) ) return false;
	if ( ! $args['id'] ) return false;
	if ( ! wppa_cache_album( $args['id'] ) ) return false;
	$id = $args['id'];
	
	foreach ( array_keys( $args ) as $itemname ) {
		$itemvalue = $args[$itemname];
		$doit = false;
		
		// Sanitize input
		switch( $itemname ) {
			case 'id':
				break;
			case 'name':
				$itemvalue = wppa_strip_tags( $itemvalue, 'all' );
				$doit = true;
				break;
			case 'description':
				$itemvalue = balanceTags( $itemvalue, true );
				$itemvalue = wppa_strip_tags( $itemvalue, 'script&style' );
				$doit = true;
				break;
			case 'timestamp':
				if ( ! $itemvalue ) {
					$itemvalue = time();
				}
				$doit = true;
				break;
			case 'scheduledtm':
				$doit = true;
				break;
				
			default:
				wppa_log( 'Error', 'Not implemented in wppa_update_album(): '.$itemname );
				return false;
		}
		
		if ( $doit ) {
			if ( $wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_ALBUMS."` SET `".$itemname."` = %s WHERE `id` = %s LIMIT 1", $itemvalue, $id ) ) ) {
				$album[$itemname] = $itemvalue; // Update cache
			}
		}
	}
	return true;
					
/*
		`a_order`, 
		`main_photo`, 
		`a_parent`, 
		`p_order_by`, 
		`cover_linktype`, 
		`cover_linkpage`, 
		`owner`, 
		`upload_limit`, 
		`alt_thumbsize`, 
		`default_tags`, 
		`cover_type`, 
		`suba_order_by`,
		`views`,
		`cats`
*/
}

// Photo
function wppa_update_photo( $args ) {
global $wpdb;
global $wppa_opt;
global $thumb;

	if ( ! is_array( $args ) ) return false;
	if ( ! $args['id'] ) return false;
	if ( ! wppa_cache_thumb( $args['id'] ) ) return false;
	$id = $args['id'];
	
	foreach ( array_keys( $args ) as $itemname ) {
		$itemvalue = $args[$itemname];
		$doit = false;
		
		// Sanitize input
		switch( $itemname ) {
			case 'id':
				break;
			case 'name':
				$itemvalue = wppa_strip_tags( $itemvalue, 'all' );
				$doit = true;
				break;
			case 'description':
				$itemvalue = balanceTags( $itemvalue, true );
				$itemvalue = wppa_strip_tags( $itemvalue, 'script&style' );
				$doit = true;
				break;
			case 'timestamp':
				if ( ! $itemvalue ) {
					$itemvalue = time();
				}
				$doit = true;
				break;
			case 'scheduledtm':
				$doit = true;
				break;
			case 'status':
				$doit = true;
				break;
				
			default:
				wppa_log( 'Error', 'Not implemented in wppa_update_photo(): '.$itemname );
				return false;
		}
		
		if ( $doit ) {
			if ( $wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_PHOTOS."` SET `".$itemname."` = %s WHERE `id` = %s LIMIT 1", $itemvalue, $id ) ) ) {
				$thumb[$itemname] = $itemvalue; // Update cache
			}
		}
	}
	return true;
}