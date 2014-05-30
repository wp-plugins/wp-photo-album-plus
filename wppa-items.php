<?php
/* wppa-items.php
* Package: wp-photo-album-plus
*
* Contains functions to retrieve album and photo items that need processing
* Version 5.3.9
*
* F.ok
*/
 
if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );
	
// Bring album into cache
// Returns album info and puts it also in global $album
function wppa_cache_album( $id ) {
global $wpdb;
global $album;

	if ( ! wppa_is_int( $id ) || $id < '1' ) {
		wppa_dbg_msg( 'Invalid arg wppa_cache_album( '.$id.' )', 'red' );
		return false;
	}
	if ( ! isset( $album['id'] ) || $album['id'] != $id ) {
		$album = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $id ), ARRAY_A );
		wppa_dbg_q( 'Q90' );
		if ( ! $album ) {
			wppa_dbg_msg( 'Album '.$id.' does not exist', 'red' );
			return false;
		}
	}
	else {
		wppa_dbg_q( 'G90' );
	}
	return $album;
}

// Bring photo into cache
// Returns photo info and puts it also in global $thumb
function wppa_cache_thumb( $id ) {
global $wpdb;
global $thumb;

	if ( ! $id ) {
		$thumb = false;
		return false;
	}
	if ( ! is_numeric( $id ) || $id < '1' ) {
		wppa_dbg_msg( 'Invalid arg wppa_cache_thumb( '.$id.' )', 'red' );
		return false;
	}
	if ( ! isset( $thumb['id'] ) || $thumb['id'] != $id ) {
		$thumb = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $id ), ARRAY_A );
		wppa_dbg_q( 'Q91' );
	}
	else {
		wppa_dbg_q( 'G91' );
	}
	return $thumb;
}

// get the name of a full sized image
function wppa_get_photo_name( $id, $add_owner = false, $add_medal = false, $esc_js = false ) {
global $wppa_opt;

	// Verify args
	if ( ! is_numeric( $id ) || $id < '1' ) {
		wppa_dbg_msg( 'Invalid arg wppa_get_photo_name( '.$id.' )', 'red' );
		return '';
	}
	
	// Get data
	$thumb = wppa_cache_thumb( $id );
	$result = __( stripslashes( $thumb['name'] ) );
	
	// Add owner?
	if ( $add_owner ) {
		$user = get_user_by( 'login', $thumb['owner'] );
		if ( $user ) {
			$result .= ' ('.$user->display_name.')';
		}
	}

	// For js use?
	if ( $esc_js ) $result = esc_js( $result );
	
	// Medal?
	if ( $add_medal ) {
		$color = $wppa_opt['wppa_medal_color'];
		$wppa_url = is_ssl() ? str_replace( 'http://', 'https://', WPPA_URL ) : WPPA_URL;	// Probably redundant... but it is not clear in to the codex if plugins_url() returns https 
		if ( $thumb['status'] == 'gold' ) $result .= '<img src="'.$wppa_url.'/images/medal_gold_'.$color.'.png" title="'.esc_attr(__a('Gold medal award!')).'" style="border:none; margin:0; padding:0; box-shadow:none; height:32px;" />';
		if ( $thumb['status'] == 'silver' ) $result .= '<img src="'.$wppa_url.'/images/medal_silver_'.$color.'.png" title="'.esc_attr(__a('Silver medal award!')).'" style="border:none; margin:0; padding:0; box-shadow:none; height:32px;" />';
		if ( $thumb['status'] == 'bronze' ) $result .= '<img src="'.$wppa_url.'/images/medal_bronze_'.$color.'.png" title="'.esc_attr(__a('Bronze medal award!')).'" style="border:none; margin:0; padding:0; box-shadow:none; height:32px;" />';
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
		wppa_do_geo();
	}
	
	// Other keywords
	if ( strpos($desc, 'w#') !== false ) {	// Is there any 'w#' ?
		// Keywords
		$keywords = array('name', 'filename', 'owner', 'id', 'tags', 'views');
		foreach ( $keywords as $keyword ) {
			$replacement = __( trim( stripslashes( $thumb[$keyword] ) ) );
			if ( $replacement == '' ) $replacement = '&lsaquo;'.__a( 'none', 'wppa' ).'&rsaquo;';
			$desc = str_replace( 'w#'.$keyword, $replacement, $desc );
		}
		$desc = str_replace( 'w#url', wppa_get_lores_url( $id ), $desc );
		$desc = str_replace( 'w#hrurl', esc_attr( wppa_get_hires_url( $id ) ), $desc );
		$desc = str_replace( 'w#tnurl', wppa_get_tnres_url( $id ), $desc );
		
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
			$name = $album['name']; 	// stripslashes( $wpdb->get_var( $wpdb->prepare( "SELECT `name` FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $id ) ) );
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
