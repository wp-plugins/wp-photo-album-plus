<?php
/* wppa-items.php
* Package: wp-photo-album-plus
*
* Contains functions to retrieve album and photo items
* Version 5.4.15
*
*/
 
if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );
	
// Bring album into cache
// Returns album info and puts it also in global $album
function wppa_cache_album( $id, $data = '' ) {
global $wpdb;
static $album;
static $album_cache_2;

	// Action?
	if ( $id == 'invalidate' ) {
		if ( isset( $album_cache_2[$data] ) ) unset( $album_cache_2[$data] );
		$album = false;
		return false;
	}
	if ( $id == 'add' ) {
		if ( ! $data ) {							// Nothing to add
			return false;
		}
		elseif ( isset( $data['id'] ) ) { 			// Add a single album to 2nd level cache
			$album_cache_2[$data['id']] = $data;	// Looks valid
		}
		else foreach( $data as $album ) {			// Add multiple
			if ( isset( $album['id'] ) ) {			// Looks valid
				$album_cache_2[$album['id']] = $album;
			}
		}
		return false;
	}
	if ( $id == 'count' ) {
		if ( is_array( $album_cache_2 ) ) {
			return count( $album_cache_2 );
		}
		else {
			return false;
		}
	}
	if ( wppa_is_enum( $id ) && ! wppa_is_int( $id ) ) {
		return false;	// enums not supporte yet
	}
	if ( ! wppa_is_int( $id ) || $id < '1' ) {
		$album = false;
		wppa_dbg_msg( 'Invalid arg wppa_cache_album('.$id.')', 'red' );
		return false;
	}

	// In first level cache?
	if ( isset( $album['id'] ) && $album['id'] == $id ) {
		wppa_dbg_q( 'G-A1' );
		return $album;
	}

	// In  second level cache?
	if ( ! empty( $album_cache_2 ) ) {
		if ( in_array( $id, array_keys( $album_cache_2 ) ) ) {
			$album = $album_cache_2[$id];
			wppa_dbg_q( 'G-A2' );
			return $album;
		}
	}

	// Not in cache, do query
	$album = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $id ), ARRAY_A );
	wppa_dbg_q( 'Q-A' );
	
	// Found one?
	if ( $album ) {
		// Store in second level cache
		$album_cache_2[$id] = $album;
		return $album;
	}
	else {
		wppa_dbg_msg( 'Album '.$id.' does not exist', 'red' );
		return false;
	}
}

// Bring photo into cache
// Returns photo info and puts it also in global $thumb
function wppa_cache_photo( $id, $data = '' ) {
	return wppa_cache_thumb( $id, $data );
}
function wppa_cache_thumb( $id, $data = '' ) {
global $wpdb;
global $thumb;
static $thumb_cache_2;

	// Action?
	if ( $id == 'invalidate' ) {
		if ( isset( $thumb_cache_2[$data] ) ) unset( $thumb_cache_2[$data] );
		$thumb = false;
		return false;
	}
	if ( $id == 'add' ) {
		if ( ! $data ) {							// Nothing to add
			return false;
		}
		elseif ( isset( $data['id'] ) ) { 			// Add a single thumb to 2nd level cache
			$thumb_cache_2[$data['id']] = $data;	// Looks valid
		}
		else foreach( $data as $thumb ) {			// Add multiple
			if ( isset( $thumb['id'] ) ) {			// Looks valid
				$thumb_cache_2[$thumb['id']] = $thumb;
			}
		}
		return false;
	}
	if ( $id == 'count' ) {
		if ( is_array( $thumb_cache_2 ) ) {
			return count( $thumb_cache_2 );
		}
		else {
			return false;
		}
	}
	if ( ! wppa_is_int( $id ) || $id < '1' ) {
		wppa_dbg_msg( 'Invalid arg wppa_cache_thumb('.$id.')', 'red' );
		$thumb = false;
		return false;
	}
	
	// In first level cache?
	if ( isset( $thumb['id'] ) && $thumb['id'] == $id ) {
		wppa_dbg_q( 'G-T1' );
		return $thumb;
	}

	// In  second level cache?
	if ( ! empty( $thumb_cache_2 ) ) {
		if ( in_array( $id, array_keys( $thumb_cache_2 ) ) ) {
			$thumb = $thumb_cache_2[$id];
			wppa_dbg_q( 'G-T2' );
			return $thumb;
		}
	}
	
	// Not in cache, do query
	$thumb = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $id ), ARRAY_A );
	wppa_dbg_q( 'Q-P' );
	
	// Found one?
	if ( $thumb ) {
		// Store in second level cache
		$thumb_cache_2[$id] = $thumb;
		return $thumb;
	}
	else {
		wppa_dbg_msg( 'Photo '.$id.' does not exist', 'red' );
		return false;
	}
}

// get the name of a full sized image
function wppa_get_photo_name( $id, $add_owner = false, $add_medal = false, $esc_js = false, $show_name = true ) {
global $wppa_opt;

	// Init
	$result = '';
	
	// Verify args
	if ( ! is_numeric( $id ) || $id < '1' ) {
		wppa_dbg_msg( 'Invalid arg wppa_get_photo_name( '.$id.' )', 'red' );
		return '';
	}
	
	// Get data
	$thumb = wppa_cache_thumb( $id );
	if ( $show_name ) {
		$result .= __( stripslashes( $thumb['name'] ) );
	}
	
	// Add owner?
	if ( $add_owner ) {
		$user = get_user_by( 'login', $thumb['owner'] );
		if ( $user ) {
			if ( $show_name ) {
				$result .= ' ('.$user->display_name.')';
			}
			else {
				$result .= ' '.$user->display_name;
			}
		}
	}

	// For js use?
	if ( $esc_js ) $result = esc_js( $result );
	
	// Medal?
	if ( $add_medal ) {
		$color = $wppa_opt['wppa_medal_color'];
		$wppa_url = is_ssl() ? str_replace( 'http://', 'https://', WPPA_URL ) : WPPA_URL;	// Probably redundant... but it is not clear in to the codex if plugins_url() returns https 
		if ( $thumb['status'] == 'gold' ) $result .= '<img src="'.$wppa_url.'/images/medal_gold_'.$color.'.png" title="'.esc_attr(__a('Gold medal award!')).'" alt="'.__a('Gold').'" style="border:none; margin:0; padding:0; box-shadow:none; height:32px;" />';
		if ( $thumb['status'] == 'silver' ) $result .= '<img src="'.$wppa_url.'/images/medal_silver_'.$color.'.png" title="'.esc_attr(__a('Silver medal award!')).'" alt="'.__a('Silver').'" style="border:none; margin:0; padding:0; box-shadow:none; height:32px;" />';
		if ( $thumb['status'] == 'bronze' ) $result .= '<img src="'.$wppa_url.'/images/medal_bronze_'.$color.'.png" title="'.esc_attr(__a('Bronze medal award!')).'" alt="'.__a('Bronze').'" style="border:none; margin:0; padding:0; box-shadow:none; height:32px;" />';
	}
	return $result;
}

// get the description of an image
function wppa_get_photo_desc( $id, $do_shortcodes = false, $do_geo = false ) {
global $wppa;
global $wppa_opt;

	// Verufy args
	if ( ! is_numeric( $id ) || $id < '1' ) {
		wppa_dbg_msg( 'Invalid arg wppa_get_photo_desc( '.$id.' )', 'red' );
		return '';
	}
	
	// Get data
	$thumb = wppa_cache_thumb( $id );
	$desc = $thumb['description'];			// Raw data
	$desc = stripslashes( $desc );			// Unescape
	$desc = __( $desc );					// qTranslate 

	// To prevent recursive rendering of scripts or shortcodes:
	$desc = str_replace( array( '%%wppa%%', '[wppa', '[/wppa]' ), array( '%-wppa-%', '{wppa', '{/wppa}' ), $desc );

	// Geo
	if ( $thumb['location'] && ! $wppa['in_widget'] && strpos( $wppa_opt['wppa_custom_content'], 'w#location' ) !== false && $do_geo == 'do_geo' ) {
		wppa_do_geo( $id, $thumb['location'] );
	}
	
	// Other keywords
	if ( strpos($desc, 'w#') !== false ) {	// Is there any 'w#' ?
		// Keywords
		$desc = str_replace( 'w#albumname', wppa_get_album_name( $thumb['album'] ), $desc );
		$desc = str_replace( 'w#albumid', $thumb['album'], $desc );
		$keywords = array('name', 'filename', 'owner', 'id', 'tags', 'views', 'album');
		foreach ( $keywords as $keyword ) {
			$replacement = __( trim( stripslashes( $thumb[$keyword] ) ) );
			if ( $replacement == '' ) $replacement = '&lsaquo;'.__a( 'none', 'wppa' ).'&rsaquo;';
			$desc = str_replace( 'w#'.$keyword, $replacement, $desc );
		}
		$desc = str_replace( 'w#url', wppa_get_lores_url( $id ), $desc );
		$desc = str_replace( 'w#hrurl', esc_attr( wppa_get_hires_url( $id ) ), $desc );
		$desc = str_replace( 'w#tnurl', wppa_get_tnres_url( $id ), $desc );
		$desc = str_replace( 'w#pl', wppa_get_source_pl( $id ), $desc );
		
		// Art monkey sizes
		if ( strpos( $desc, 'w#amx' ) !== false || strpos( $desc, 'w#amy' ) !== false || strpos( $desc, 'w#amfs' ) !== false ) {
			$amxy = wppa_get_artmonkey_size_a( $id );
			if ( is_array( $amxy ) ) {
				$desc = str_replace( 'w#amx', $amxy['x'], $desc );
				$desc = str_replace( 'w#amy', $amxy['y'], $desc );
				$desc = str_replace( 'w#amfs', $amxy['s'], $desc );
			}
			else {
				$desc = str_replace( 'w#amx', 'N.a.', $desc );
				$desc = str_replace( 'w#amy', 'N.a.', $desc );
				$desc = str_replace( 'w#amfs', 'N.a.', $desc );
			}
		}
		
		// Timestamps
		$timestamps = array( 'timestamp', 'modified' );
		foreach ( $timestamps as $timestamp ) {
			if ( $thumb[$timestamp] ) {
				$desc = str_replace( 'w#'.$timestamp, wppa_local_date( get_option( 'date_format', "F j, Y," ).' '.get_option( 'time_format', "g:i a" ), $thumb[$timestamp] ), $desc );
			}
			else {
				$desc = str_replace( 'w#'.$timestamp, '&lsaquo;'.__a( 'unknown' ).'&rsaquo;', $desc );
			}
		}
	}

	// Shortcodes
	if ( $do_shortcodes ) $desc = do_shortcode( $desc );	// Do shortcodes if wanted
	else $desc = strip_shortcodes( $desc );					// Remove shortcodes if not wanted

	$desc = wppa_html( $desc );				// Enable html
	$desc = balanceTags( $desc, true );		// Balance tags
	$desc = wppa_filter_iptc( $desc, $id );	// Render IPTC tags
	$desc = wppa_filter_exif( $desc, $id );	// Render EXIF tags
	$desc = make_clickable( $desc );		// Auto make a tags for links

	return $desc;
}

// get album name
function wppa_get_album_name( $id, $extended = false ) {

	if ( $id > '0' ) {
		$album = wppa_cache_album( $id );
	}
	else {
		$album = false;
	}
	
    $name = '';
	
	if ( $extended ) {
		if ( $id == '0' ) {
			$name = is_admin() ? __( '--- none ---', 'wppa' ) : __a( '--- none ---', 'wppa_theme' );
			return $name;
		}
		if ( $id == '-1' ) {
			$name = is_admin() ? __( '--- separate ---', 'wppa' ) : __a( '--- separate ---', 'wppa_theme' );
			return $name;
		}
		if ( $id == '-2' ) {
			$name = is_admin() ? __( '--- all ---', 'wppa' ) : __a( '--- all ---', 'wppa_theme' );
			return $name;
		}
		if ( $id == '-9' ) {
			$name = is_admin() ? __( '--- deleted ---', 'wppa' ) : __a( '--- deleted ---', 'wppa_theme' );
			return $name;
		}
		if ( $extended == 'raw' ) {
			$name = $album['name'];
			return $name;
		}
	}
	else {
		if ( $id == '-2' ) {
			$name = is_admin() ? __( 'All Albums', 'wppa' ) : __a( 'All Albums', 'wppa_theme' );
			return $name;
		}
	}
	
	if ( ! $id ) return '';
	elseif ( ! is_numeric( $id ) || $id < '1' ) {
		wppa_dbg_msg( 'Invalid arg wppa_get_album_name( '.$id.', '.$extended.' )', 'red' );
		return '';
	}
    else {
		if ( ! $album ) $name = is_admin() ? __( '--- deleted ---', 'wppa' ) : __a( '--- deleted ---', 'wppa_theme' );
		else $name = __( stripslashes( $album['name'] ) );
    }

	return $name;
}

// get album description
function wppa_get_album_desc( $id ) {
	
	if ( ! is_numeric( $id ) || $id < '1' ) wppa_dbg_msg( 'Invalid arg wppa_get_album_desc( '.$id.' )', 'red' );
	$album = wppa_cache_album( $id );
	$desc = $album['description'];			// Raw data
	if ( ! $desc ) return '';				// No content, need no filtering
	$desc = stripslashes( $desc );			// Unescape
	$desc = __( $desc );					// qTranslate 
	$desc = wppa_html( $desc );				// Enable html
	$desc = balanceTags( $desc, true );		// Balance tags

	if ( strpos($desc, 'w#') !== false ) {	// Is there any 'w#' ?
		// Keywords
		$keywords = array( 'name', 'owner', 'id', 'views' );
		foreach ( $keywords as $keyword ) {
			$replacement = __( trim( stripslashes( $album[$keyword] ) ) );
			if ( $replacement == '' ) $replacement = '&lsaquo;'.__a( 'none', 'wppa' ).'&rsaquo;';
			$desc = str_replace( 'w#'.$keyword, $replacement, $desc );
		}

		// Timestamps
		$timestamps = array( 'timestamp', 'modified' );	// Identical, there is only timestamp, but it acts as modified
		foreach ( $timestamps as $timestamp ) {
			if ( $album['timestamp'] ) {
				$desc = str_replace( 'w#'.$timestamp, wppa_local_date( get_option( 'date_format', "F j, Y," ).' '.get_option( 'time_format', "g:i a" ), $album['timestamp'] ), $desc );
			}
			else {
				$desc = str_replace( 'w#'.$timestamp, '&lsaquo;'.__a('unknown').'&rsaquo;', $desc );
			}
		}
	}
	
	// To prevent recursive rendering of scripts or shortcodes:
	$desc = str_replace( array( '%%wppa%%', '[wppa', '[/wppa]' ), array( '%-wppa-%', '{wppa', '{/wppa}' ), $desc );
	
	// Convert links and mailto:
	$desc = make_clickable( $desc );
	
	return $desc;
}

// Get any album field of any alnbum, raw data from the db
function wppa_get_album_item( $id, $item ) {
	
	$album = wppa_cache_album( $id );
	
	if ( $album ) {
		if ( isset( $album[$item] ) ) {
			return trim( $album[$item] );
		}
		else {
			wppa_dbg_msg( 'Album item ' . $item . ' does not exist. ( get_album_item )', 'red' );
		}
	}
	else {
		wppa_dbg_msg( 'Album ' . $id . ' does not exist. ( get_album_item )', 'red' );
	}
	return false;
}

// Get any photo field of any photo, raw data from the db
function wppa_get_photo_item( $id, $item ) {
	
	$photo = wppa_cache_photo( $id );
	
	if ( $photo ) {
		if ( isset( $photo[$item] ) ) {
			return trim( $photo[$item] );
		}
		else {
			wppa_dbg_msg( 'Photo item ' . $item . ' does not exist. ( get_photo_item )', 'red' );
		}
	}
	else {
		wppa_dbg_msg( 'Photo ' . $id . ' does not exist. ( get_photo_item )', 'red' );
	}
	return false;
}

// Get sizes routines
// $id: int photo id
// $force: bool force recalculation, both x and y
function wppa_get_thumbx( $id, $force = false ) {
	return wppa_get_thumbphotoxy( $id, 'thumbx', $force );
}
function wppa_get_thumby( $id, $force = false ) {
	return wppa_get_thumbphotoxy( $id, 'thumby', $force );
}
function wppa_get_photox( $id, $force = false ) {
	return wppa_get_thumbphotoxy( $id, 'photox', $force );
}
function wppa_get_photoy( $id, $force = false ) {
	return wppa_get_thumbphotoxy( $id, 'photoy', $force );
}
function wppa_get_thumbphotoxy( $id, $key, $force = false ) {

	$result = wppa_get_photo_item( $id, $key );
	if ( $result && ! $force ) {
		return $result; 			// Value found
	}
	
	if ( $key == 'thumbx' || $key == 'thumby' ) {
		$file = wppa_get_thumb_path( $id );
	}
	else {
		$file = wppa_get_photo_path( $id );
	}
	if ( ! is_file( $file ) && ! $force ) {
		return '0';	// File not found
	}
	
	if ( is_file( $file ) ) {
		$size = getimagesize( $file );
	}
	else {
		$size = array( '0', '0');
	}
	if ( is_array( $size ) ) {
		if ( $key == 'thumbx' || $key == 'thumby' ) {
			wppa_update_photo( array( 'id' => $id, 'thumbx' => $size[0], 'thumby' => $size[1] ) );
		}
		else {
			wppa_update_photo( array( 'id' => $id, 'photox' => $size[0], 'photoy' => $size[1] ) );
		}
		wppa_cache_photo( 'invalidate', $id );
	}
	
	if ( $key == 'thumbx' || $key == 'photox' ) {
		return $size[0];
	}
	else {
		return $size[1];
	}
}

function wppa_get_imagexy( $id, $key = 'photo' ) {
	if ( wppa_is_video( $id ) ) {
		$result = array( wppa_get_videox( $id ), wppa_get_videoy( $id ) );
	}
	elseif ( $key == 'thumb' ) {
		$result = array( wppa_get_thumbx( $id ), wppa_get_thumby( $id ) );
	}
	else {
		$result = array( wppa_get_photox( $id ), wppa_get_photoy( $id ) );
	}
	return $result;
}

function wppa_get_imagex( $id, $key = 'photo' ) {
	if ( wppa_is_video( $id ) ) {
		$result = wppa_get_videox( $id );
	}
	elseif ( $key == 'thumb' ) {
		$result = wppa_get_thumbx( $id );
	}
	else {
		$result = wppa_get_photox( $id );
	}
	return $result;
}

function wppa_get_imagey( $id, $key = 'photo' ) {
	if ( wppa_is_video( $id ) ) {
		$result = wppa_get_videoy( $id );
	}
	elseif ( $key == 'thumb' ) {
		$result = wppa_get_thumby( $id );
	}
	else {
		$result = wppa_get_photoy( $id );
	}
	return $result;
}