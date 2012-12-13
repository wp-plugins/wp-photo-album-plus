<?php
/* wppa-utils.php
* Package: wp-photo-album-plus
*
* Contains low-level utility routines
* Version 4.8.7
*
*/

// Bring album into cache
function wppa_cache_album($id) {
global $wpdb;
global $album;

	if ( ! is_numeric($id) || $id < '1' ) {
		wppa_dbg_msg('Invalid arg wppa_cache_album('.$id.')', 'red');
		return;
	}
	if ( ! isset($album['id']) || $album['id'] != $id ) {
		$album = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $id), 'ARRAY_A');
		wppa_dbg_q('Q90');
	}
	else {
		wppa_dbg_q('G90');
	}
}

// Bring photo into cache
function wppa_cache_thumb($id) {
global $wpdb;
global $thumb;

	if ( ! is_numeric($id) || $id < '1' ) {
		wppa_dbg_msg('Invalid arg wppa_cache_thumb('.$id.')', 'red');
		return;
	}
	if ( ! isset($thumb['id']) || $thumb['id'] != $id ) {
		$thumb = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $id), 'ARRAY_A');
		wppa_dbg_q('Q91');
	}
	else {
		wppa_dbg_q('G91');
	}
}

// get url of thumb
function wppa_get_thumb_url($id) {
global $thumb;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_thumb_url('.$id.')', 'red');
	wppa_cache_thumb($id);
	return WPPA_UPLOAD_URL.'/thumbs/' . $thumb['id'] . '.' . $thumb['ext'];
}

// get path of thumb
function wppa_get_thumb_path($id) {
global $thumb;
	
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_thumb_path('.$id.')', 'red');
	wppa_cache_thumb($id);
	return WPPA_UPLOAD_PATH.'/thumbs/'.$thumb['id'].'.'.$thumb['ext'];
}

// get url of a full sized image
function wppa_get_photo_url($id) {
global $thumb;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_photo_url('.$id.')', 'red');
	wppa_cache_thumb($id);
	return WPPA_UPLOAD_URL.'/'.$id.'.'.$thumb['ext'];
}

// get path of a full sized image
function wppa_get_photo_path($id) {
global $thumb;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_photo_path('.$id.')', 'red');
	wppa_cache_thumb($id);
	return WPPA_UPLOAD_PATH.'/'.$id.'.'.$thumb['ext'];
}

// get the name of a full sized image
function wppa_get_photo_name($id) {
global $thumb;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_photo_name('.$id.')', 'red');
	wppa_cache_thumb($id);
	return __(stripslashes($thumb['name']));
}

// get the description of an image
function wppa_get_photo_desc($id) {
global $thumb;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_photo_desc('.$id.')', 'red');
	wppa_cache_thumb($id);
	$desc = $thumb['description'];
	$desc = __(wppa_filter_exif(wppa_filter_iptc(wppa_html(stripslashes($desc)), $id), $id));
	// To prevent recursive rendering of scripts or shortcodes:
	$desc = str_replace(array('%%wppa%%', '[wppa', '[/wppa]'), array('%-wppa-%', '{wppa', '{/wppa}'), $desc);
	return $desc;
}

// See if an album is in a separate tree
function wppa_is_separate($id) {

	if ( ! $id ) return false;
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_is_separate('.$id.')', 'red');
	$alb = wppa_get_parentalbumid($id);
	if ($alb == 0) return false;
	if ($alb == -1) return true;
	return (wppa_is_separate($alb));
}

// Get the albums parent
function wppa_get_parentalbumid($id) {
global $album;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_parentalbumid('.$id.')', 'red');
	wppa_cache_album($id);
	return $album['a_parent'];
}

// get album name
function wppa_get_album_name($id, $extended = false) {
global $wpdb;
global $album;

    $name = '';
	
	if ( $extended ) {
		if ( $id == '0' ) {
			$name = is_admin() ? __('--- none ---', 'wppa') : __a('--- none ---', 'wppa_theme');
			return $name;
		}
		if ( $id == '-1' ) {
			$name = is_admin() ? __('--- separate ---', 'wppa') : __a('--- separate ---', 'wppa_theme');
			return $name;
		}
		if ( $extended == 'raw' ) {
			$name = stripslashes($wpdb->get_var($wpdb->prepare("SELECT `name` FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $id)));
		}
	}
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_album_name('.$id.', '.$extended.')', 'red');
    else {
		wppa_cache_album($id);
		$name = __(stripslashes($album['name']));
    }

	return $name;
}

// get album decription
function wppa_get_album_desc($id) {
global $album;
	
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_album_desc('.$id.')', 'red');
	wppa_cache_album($id);
	$desc = __(wppa_html(stripslashes($album['description'])));
	// To prevent recursive rendering of scripts or shortcodes:
	$desc = str_replace(array('%%wppa%%', '[wppa', '[/wppa]'), array('%-wppa-%', '{wppa', '{/wppa}'), $desc);
	return $desc;
}

// get a photos album id
function wppa_get_album_id_by_photo_id($id) {
global $thumb;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_album_id_by_photo_id('.$id.')', 'red');
	wppa_cache_thumb($id);
	return $thumb['album'];
}

function wppa_get_rating_count_by_id($id) {
global $thumb;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_rating_count_by_id('.$id.')', 'red');
	wppa_cache_thumb($id);
	return $thumb['rating_count'];
}

function wppa_get_rating_by_id($id, $opt = '') {
global $wpdb;
global $wppa_opt;
global $thumb;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_rating_by_id('.$id.', '.$opt.')', 'red');
	wppa_cache_thumb($id);
	$rating = $thumb['mean_rating'];
	if ( $rating ) {
		$i = $wppa_opt['wppa_rating_prec'];
		$j = $i + '1';
		$val = sprintf('%'.$j.'.'.$i.'f', $rating);
		if ($opt == 'nolabel') $result = $val;
		else $result = sprintf(__a('Rating: %s', 'wppa_theme'), $val);
	}
	else $result = '';
	return $result;
}
