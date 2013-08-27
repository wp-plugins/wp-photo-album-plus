<?php
/* wppa-statistics.php
* Package: wp-photo-album-plus
*
* Functions for counts etc
* Common use front and admin
* Version 5.1.1
*
*/

if ( ! defined( 'ABSPATH' ) )
    die( "Can't load this file directly" );

// show system statistics
function wppa_statistics() {
global $wppa;

	$wppa['out'] .= wppa_get_statistics();
}
function wppa_get_statistics() {

	$count = wppa_get_total_album_count();
	$y_id = wppa_get_youngest_album_id();
	$y_name = __(wppa_get_album_name($y_id));
	$p_id = wppa_get_parentalbumid($y_id);
	$p_name = __(wppa_get_album_name($p_id));
	
	$result = '<div class="wppa-box wppa-nav" style="text-align: center; '.__wcs('wppa-box').__wcs('wppa-nav').'">';
	$result .= __a('There are').' '.$count.' '.__a('photo albums. The last album added is').' ';
	$result .= '<a href="'.wppa_get_permalink().'wppa-album='.$y_id.'&amp;wppa-cover=0&amp;wppa-occur=1">'.$y_name.'</a>';

	if ($p_id > '0') {
		$result .= __a(', a subalbum of').' '; 
		$result .= '<a href="'.wppa_get_permalink().'wppa-album='.$p_id.'&amp;wppa-cover=0&amp;wppa-occur=1">'.$p_name.'</a>';
	}
	
	$result .= '.</div>';
	
	return $result;
}

// get number of photos in album 
function wppa_get_photo_count($xid = '') {
global $wpdb;
global $album;
    
    if (is_numeric($xid)) $id = $xid; else $id = $album['id'];
    $count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM " . WPPA_PHOTOS . " WHERE album = %s AND ( status <> %s OR owner = %s )", $id, 'pending', wppa_get_user() ) );
	wppa_dbg_q('Q12v');
	return $count;
}

// get number of albums in album 
function wppa_get_album_count($xid = '') {
global $wpdb;
global $album;
    
    if (is_numeric($xid)) $id = $xid; else $id = $album['id'];
    $count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM " . WPPA_ALBUMS . " WHERE a_parent=%s", $id ) );
	wppa_dbg_q('Q13v');
    return $count;
}

// get number of albums in system
function wppa_get_total_album_count() {
global $wpdb;

	$count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPPA_ALBUMS."`");
	wppa_dbg_q('Q14');
	return $count;
}

// get youngest photo id
function wppa_get_youngest_photo_id() {
global $wpdb;

	$result = $wpdb->get_var( "SELECT `id` FROM `" . WPPA_PHOTOS . "` WHERE `status` <> 'pending' ORDER BY `id` DESC LIMIT 1" );
	wppa_dbg_q('Q15');
	return $result;
}

// get youngest album id
function wppa_get_youngest_album_id() {
global $wpdb;
	
	$result = $wpdb->get_var( "SELECT `id` FROM `" . WPPA_ALBUMS . "` ORDER BY `id` DESC LIMIT 1" );
	wppa_dbg_q('Q16');
	return $result;
}

// get youngest album name
function wppa_get_youngest_album_name() {
global $wpdb;
	
	$result = $wpdb->get_var( "SELECT `name` FROM `" . WPPA_ALBUMS . "` ORDER BY `id` DESC LIMIT 1" );
	wppa_dbg_q('Q17');
	return stripslashes($result);
}

// Bump Viewcount
function wppa_bump_viewcount($type, $id) {
global $wpdb;

	if ( $type != 'album' && $type != 'photo' ) die ( 'Illegal $type in wppa_bump_viewcount: '.$type);
	if ( ! is_numeric($id) ) die ( 'Illegal $id in wppa_bump_viewcount: '.$id);

	if ( ! isset($_SESSION['wppa']) ) 					$_SESSION['wppa'] = array();
	if ( ! isset($_SESSION['wppa'][$type]) ) 			$_SESSION['wppa'][$type] = array();
	if ( ! isset($_SESSION['wppa'][$type][$id] ) ) {	// This one not done yest
		$_SESSION['wppa'][$type][$id] = true;			// Mark as viewed
		if ( $type == 'album' ) $table = WPPA_ALBUMS; else $table = WPPA_PHOTOS;
		
		$count = $wpdb->get_var("SELECT `views` FROM `".$table."` WHERE `id` = ".$id);
		$count++;
		
		$wpdb->query("UPDATE `".$table."` SET `views` = ".$count." WHERE `id` = ".$id);
		wppa_dbg_msg('Bumped viewcount for '.$type.' '.$id.' to '.$count, 'red');
	}
//global $wppa;		
//if ( $wppa['debug'] )		print_r($_SESSION);	// Debug

}