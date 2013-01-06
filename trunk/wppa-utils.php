<?php
/* wppa-utils.php
* Package: wp-photo-album-plus
*
* Contains low-level utility routines
* Version 4.9.0
*
*/

// Bring album into cache
function wppa_cache_album($id) {
global $wpdb;
global $album;

	if ( ! is_numeric($id) || $id < '1' ) {
		wppa_dbg_msg('Invalid arg wppa_cache_album('.$id.')', 'red');
		return false;
	}
	if ( ! isset($album['id']) || $album['id'] != $id ) {
		$album = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $id), 'ARRAY_A');
		wppa_dbg_q('Q90');
		if ( ! $album ) {
			wppa_dbg_msg('Album does not exist', 'red');
			return false;
		}
	}
	else {
		wppa_dbg_q('G90');
	}
	return true;
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
	$desc = $thumb['description'];			// Raw data
	$desc = stripslashes($desc);			// Unescape
	$desc = __($desc);						// qTranslate 
	$desc = wppa_html($desc);				// Enable html
	$desc = balanceTags($desc, true);		// Balance tags
	$desc = wppa_filter_iptc($desc, $id);	// Render IPTC tags
	$desc = wppa_filter_exif($desc, $id);	// Render EXIF tags
	
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
	if ( ! wppa_cache_album($id) ) {
		wppa_dbg_msg('Album '.$id.' no longer exists, but is still set as a parent. Please correct this.', 'red');
		return '-9';	// Album does not exist
	}
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
		if ( $id == '-9' ) {
			$name = is_admin() ? __('--- deleted ---', 'wppa') : __a('--- deleted ---', 'wppa_theme');
			return $name;
		}
		if ( $extended == 'raw' ) {
			$name = stripslashes($wpdb->get_var($wpdb->prepare("SELECT `name` FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $id)));
		}
	}
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_album_name('.$id.', '.$extended.')', 'red');
    else {
		if ( ! wppa_cache_album($id) ) $name = is_admin() ? __('--- deleted ---', 'wppa') : __a('--- deleted ---', 'wppa_theme');
		else $name = __(stripslashes($album['name']));
    }

	return $name;
}

// get album decription
function wppa_get_album_desc($id) {
global $album;
	
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_album_desc('.$id.')', 'red');
	wppa_cache_album($id);
	$desc = $album['description'];			// Raw data
	$desc = stripslashes($desc);			// Unescape
	$desc = __($desc);						// qTranslate 
	$desc = wppa_html($desc);				// Enable html
	$desc = balanceTags($desc, true);		// Balance tags

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

function wppa_switch($key) {
global $wppa_opt;
	return $wppa_opt[$key] === true || $wppa_opt[$key] == 'yes';
}

function wppa_add_paths($albums) {
	if ( is_array($albums) ) foreach ( array_keys($albums) as $index ) {
		$tempid = $albums[$index]['id'];
		$albums[$index]['name'] = __(stripslashes($albums[$index]['name']));	// Translate name
		while ( $tempid > '0' ) {
			$tempid = wppa_get_parentalbumid($tempid);
			if ( $tempid > '0' ) {
				$albums[$index]['name'] = wppa_get_album_name($tempid).' > '.$albums[$index]['name'];
			}
			elseif ( $tempid == '-1' ) $albums[$index]['name'] = '-s- '.$albums[$index]['name'];
		}
	}
	return $albums;
}
	
function wppa_array_sort($array, $on, $order=SORT_ASC) {

    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

function wppa_get_taglist() {
	$result = get_option('wppa_taglist', 'nil');
	if ( $result == 'nil' ) {
		$result = wppa_create_taglist();
	}
	return $result;
}

function wppa_clear_taglist() {
	if ( get_option('wppa_taglist', 'nil') != 'nil' ) {
		delete_option('wppa_taglist');
	}
}

function wppa_create_taglist() {
global $wpdb;
	$result = false;
	$total = '0';
	$photos = $wpdb->get_results("SELECT `id`, `tags` FROM `".WPPA_PHOTOS."` WHERE `status` <> 'pending' AND `tags` <> ''", ARRAY_A);
	if ( $photos ) foreach ( $photos as $photo ) {
		$tags = explode(',', $photo['tags']);
		if ( $tags ) foreach ( $tags as $tag ) {
			if ( $tag ) {
				if ( ! isset($result[$tag]) ) {	// A new tag
					$result[$tag]['tag'] = $tag;
					$result[$tag]['count'] = '1';
					$result[$tag]['ids'][] = $photo['id'];
				}
				else {							// An existing tag
					$result[$tag]['count']++;
					$result[$tag]['ids'][] = $photo['id'];
				}
			}
			$total++;
		}
	}
	if ( is_array($result) ) {
		foreach ( array_keys($result) as $key ) {
			$result[$key]['fraction'] = round($result[$key]['count'] * 100 / $total) / 100;
		}
		$result = wppa_array_sort($result, 'tag');
	}
	update_option('wppa_taglist', $result);
	return $result;
}

function wppa_update_option($option, $value) {
	update_option($option, $value);
	delete_option('wppa_cached_options');
	delete_option('wppa_cached_options_admin');
}

function wppa_album_exists($id) {
global $wpdb;
	return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $id));
}