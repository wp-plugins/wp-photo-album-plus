<?php
/* wppa-utils.php
* Package: wp-photo-album-plus
*
* Contains low-level utility routines
* Version 5.2.10
*
*/
 
function __a($txt, $dom = 'wppa_theme') {
	return __($txt, $dom);
}

// Bring album into cache
function wppa_cache_album($id) {
global $wpdb;
global $album;

	if ( ! wppa_is_int($id) || $id < '1' ) {
		wppa_dbg_msg('Invalid arg wppa_cache_album('.$id.')', 'red');
		return false;
	}
	if ( ! isset($album['id']) || $album['id'] != $id ) {
		$album = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $id), 'ARRAY_A');
		wppa_dbg_q('Q90');
		if ( ! $album ) {
			wppa_dbg_msg('Album '.$id.' does not exist', 'red');
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

	if ( ! $id ) {
		$thumb = false;
		return;
	}
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
function wppa_get_thumb_url($id, $system = 'flat', $x = '0', $y = '0') {
global $thumb;
global $wppa_opt;
global $blog_id;

	// If in the cloud...
	if ( wppa_cdn() ) {	
		if ( $x && $y ) {		// Only when size is given !! To prevent download of the fullsize image
			switch ( wppa_cdn() ) {
				case 'cloudinary':
					$transform	= explode( ':', $wppa_opt['wppa_thumb_aspect'] );
					$t 			= 'limit';
					if ( $transform['2'] == 'clip' ) $t = 'fill';
					if ( $transform['2'] == 'padd' ) $t = 'pad,b_black';
					$sizespec 	= ( $x && $y ) ? 'w_'.$x.',h_'.$y.',c_'.$t.'/' : '';
					$prefix 	= ( is_multisite() && ! WPPA_MULTISITE_GLOBAL ) ? $blog_id.'-' : '';
					$url = 'http://res.cloudinary.com/'.get_option('wppa_cdn_cloud_name').'/image/upload/'.$sizespec.$prefix.$thumb['id'].'.'.$thumb['ext'];
					return $url;
					break;
					
			}
		}
	}

	if ( get_option('wppa_file_system') == 'flat' ) $system = 'flat';	// Have been converted, ignore argument
	if ( get_option('wppa_file_system') == 'tree' ) $system = 'tree';	// Have been converted, ignore argument
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_thumb_url('.$id.')', 'red');
	wppa_cache_thumb($id);
	if ( $system == 'tree' ) return WPPA_UPLOAD_URL.'/thumbs/'.wppa_expand_id($thumb['id']).'.'.$thumb['ext'].'?ver='.get_option('wppa_thumb_version', '1');
	else return WPPA_UPLOAD_URL.'/thumbs/'.$thumb['id'].'.'.$thumb['ext'].'?ver='.get_option('wppa_thumb_version', '1');
}

// Bump thumbnail version number
function wppa_bump_thumb_rev() {
	wppa_update_option('wppa_thumb_version', get_option('wppa_thumb_version', '1') + '1');
}

// get path of thumb
function wppa_get_thumb_path($id, $system = 'flat' ) {
global $thumb;
$wppa_opt;

	if ( get_option('wppa_file_system') == 'flat' ) $system = 'flat';	// Have been converted, ignore argument
	if ( get_option('wppa_file_system') == 'tree' ) $system = 'tree';	// Have been converted, ignore argument
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_thumb_path('.$id.')', 'red');
	wppa_cache_thumb($id);
	if ( $system == 'tree' ) return WPPA_UPLOAD_PATH.'/thumbs/'.wppa_expand_id($thumb['id'], true).'.'.$thumb['ext'];
	else return WPPA_UPLOAD_PATH.'/thumbs/'.$thumb['id'].'.'.$thumb['ext'];
}

// get url of a full sized image
function wppa_get_photo_url($id, $system = 'flat', $x = '0', $y = '0') {
global $thumb;
global $wppa_opt;
global $blog_id;

	if ( is_feed() && wppa_switch('wppa_feed_use_thumb') ) return wppa_get_thumb_url($id, $system);
	
	// If in the cloud...
	if ( wppa_cdn() ) { 
		switch ( wppa_cdn() ) {
			case 'cloudinary':
				$prefix 	= ( is_multisite() && ! WPPA_MULTISITE_GLOBAL ) ? $blog_id.'-' : '';
				$t 			= wppa_switch('wppa_enlarge') ? 'fit' : 'limit';
				$sizespec 	= ( $x && $y ) ? 'w_'.$x.',h_'.$y.',c_'.$t.'/' : '';
				$url = 'http://res.cloudinary.com/'.get_option('wppa_cdn_cloud_name').'/image/upload/'.$sizespec.$prefix.$thumb['id'].'.'.$thumb['ext'];
				return $url;
				break;
				
		}
	}
	
	if ( get_option('wppa_file_system') == 'flat' ) $system = 'flat';	// Have been converted, ignore argument
	if ( get_option('wppa_file_system') == 'tree' ) $system = 'tree';	// Have been converted, ignore argument
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_photo_url('.$id.')', 'red');
	wppa_cache_thumb($id);
	if ( $system == 'tree' ) return WPPA_UPLOAD_URL.'/'.wppa_expand_id($thumb['id']).'.'.$thumb['ext'].'?ver='.get_option('wppa_photo_version', '1');
	else return WPPA_UPLOAD_URL.'/'.$thumb['id'].'.'.$thumb['ext'].'?ver='.get_option('wppa_photo_version', '1');
}

// Bump Fullsize photo version number
function wppa_bump_photo_rev() {
	wppa_update_option('wppa_photo_version', get_option('wppa_photo_version', '1') + '1');
}

// get path of a full sized image
function wppa_get_photo_path($id, $system = 'flat') {
global $thumb;

	if ( get_option('wppa_file_system') == 'flat' ) $system = 'flat';	// Have been converted, ignore argument
	if ( get_option('wppa_file_system') == 'tree' ) $system = 'tree';	// Have been converted, ignore argument
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_photo_path('.$id.')', 'red');
	wppa_cache_thumb($id);
	if ( $system == 'tree' ) return WPPA_UPLOAD_PATH.'/'.wppa_expand_id($thumb['id'], true).'.'.$thumb['ext'];
	else return WPPA_UPLOAD_PATH.'/'.$thumb['id'].'.'.$thumb['ext'];
}

// Expand id to subdir chain for new file structure
function wppa_expand_id($xid, $makepath = false) {
	$result = '';
	$id = $xid;
	$len = strlen($id);
	while ( $len > '2' ) {
		$result .= substr($id, '0', '2').'/';
		$id = substr($id, '2');
		$len = strlen($id);
		if ( $makepath ) {
			$path = WPPA_UPLOAD_PATH.'/'.$result;
			if ( ! is_dir($path) ) mkdir($path);
			$path = WPPA_UPLOAD_PATH.'/thumbs/'.$result;
			if ( ! is_dir($path) ) mkdir($path);
		}
	}
	$result .= $id;
	return $result;
}

// get the name of a full sized image
function wppa_get_photo_name($id, $add_owner = false) {
global $thumb;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_photo_name('.$id.')', 'red');
	wppa_cache_thumb($id);
	$result = __(stripslashes($thumb['name']));
	if ( $add_owner ) {
		$user = get_user_by('login', $thumb['owner']);
		if ( $user ) {
			$result .= ' ('.$user->display_name.')';
		}
	}
	return $result;
}

// get the description of an image
function wppa_get_photo_desc($id, $do_shortcodes = false, $do_geo = false) {
global $thumb;
global $wppa;
global $wppa_opt;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_photo_desc('.$id.')', 'red');
	wppa_cache_thumb($id);
	$desc = $thumb['description'];			// Raw data
//	if ( ! $desc ) return '';				// No content, need no filtering except geo!!
	$desc = stripslashes($desc);			// Unescape
	$desc = __($desc);						// qTranslate 

	// To prevent recursive rendering of scripts or shortcodes:
	$desc = str_replace(array('%%wppa%%', '[wppa', '[/wppa]'), array('%-wppa-%', '{wppa', '{/wppa}'), $desc);

	// Geo
	if ( $thumb['location'] && ! $wppa['in_widget'] && strpos($wppa_opt['wppa_custom_content'], 'w#location') !== false && $do_geo == 'do_geo') {
		wppa_do_geo();
	}
	
	// Other keywords
	if ( strpos($desc, 'w#') !== false ) {	// Is there any 'w#' ?
		// Keywords
		$keywords = array('name', 'filename', 'owner', 'id', 'tags', 'views');
		foreach ( $keywords as $keyword ) {
			$replacement = __(trim(stripslashes($thumb[$keyword])));
			if ( $replacement == '' ) $replacement = '&lsaquo;'.__a('none', 'wppa').'&rsaquo;';
			$desc = str_replace('w#'.$keyword, $replacement, $desc);
		}
		
		// Art monkey sizes
		if ( strpos($desc, 'w#amx') !== false || strpos($desc, 'w#amy') !== false || strpos($desc, 'w#amfs') !== false ) {
			$amxy = wppa_get_artmonkey_size_a($id);
			if ( is_array($amxy ) ) {
				$desc = str_replace('w#amx', $amxy['x'], $desc);
				$desc = str_replace('w#amy', $amxy['y'], $desc);
				$desc = str_replace('w#amfs', $amxy['s'], $desc);
			}
			else {
				$desc = str_replace('w#amx', 'N.a.', $desc);
				$desc = str_replace('w#amy', 'N.a.', $desc);
				$desc = str_replace('w#amfs', 'N.a.', $desc);
			}
		}
		
		// Timestamps
		$timestamps = array('timestamp', 'modified');
		foreach ( $timestamps as $timestamp ) {
			if ( $thumb[$timestamp] ) {
				$desc = str_replace('w#'.$timestamp, wppa_local_date(get_option('date_format', "F j, Y,").' '.get_option('time_format', "g:i a"), $thumb[$timestamp]), $desc);
			}
			else {
				$desc = str_replace('w#'.$timestamp, '&lsaquo;'.__a('unknown').'&rsaquo;', $desc);
			}
		}
	}

	// Shortcodes
	if ( $do_shortcodes ) $desc = do_shortcode($desc);	// Do shortcodes if wanted
	else $desc = strip_shortcodes($desc);				// Remove shortcodes if not wanted

	$desc = wppa_html($desc);				// Enable html
	$desc = balanceTags($desc, true);		// Balance tags
	$desc = wppa_filter_iptc($desc, $id);	// Render IPTC tags
	$desc = wppa_filter_exif($desc, $id);	// Render EXIF tags
	$desc = make_clickable($desc);			// Auto make a tags for links

	return $desc;
}

function wppa_do_geo() {
global $thumb;
global $wppa;
global $wppa_opt;

	$id 	= $thumb['id'];
	$temp 	= explode('/', $thumb['location']);
	$lat 	= $temp['2'];
	$lon 	= $temp['3'];
	
	$type 	= $wppa_opt['wppa_gpx_implementation'];
	
	// Switch on implementation type
	switch ( $type ) {
		case 'google-maps-gpx-viewer':
			$geo = str_replace('w#lon', $lon, str_replace('w#lat', $lat, $wppa_opt['wppa_gpx_shortcode']));
			$geo = do_shortcode($geo);
			$wppa['geo'] .= '<div id="geodiv-'.$wppa['master_occur'].'-'.$id.'" style="display:none;">'.$geo.'</div>';
			break;
		case 'wppa-plus-embedded':
			if ( $wppa['geo'] == '' ) { 	// First
				$wppa['geo'] = '
<div id="map-canvas-'.$wppa['master_occur'].'" style="height:'.$wppa_opt['wppa_map_height'].'px; width:100%; padding:0; margin:0; font-size: 10px;" ></div>
<script type="text/javascript" >
	if ( typeof ( _wppaLat ) == "undefined" ) { var _wppaLat = new Array();	var _wppaLon = new Array(); }
	_wppaLat['.$wppa['master_occur'].'] = new Array(); _wppaLon['.$wppa['master_occur'].'] = new Array();
</script>';
			}	// End first
			$wppa['geo'] .= '
<script type="text/javascript">_wppaLat['.$wppa['master_occur'].']['.$id.'] = '.$lat.'; _wppaLon['.$wppa['master_occur'].']['.$id.'] = '.$lon.';</script>';
			break;	// End native
	}
}

// See if an album is in a separate tree
function wppa_is_separate($id) {

	if ( $id == '' ) return false;
	if ( ! wppa_is_int($id) ) return false;
	if ( $id == '-1' ) return true;
	if ( $id < '1' ) return false;
	$alb = wppa_get_parentalbumid($id);
	
	return wppa_is_separate($alb);
}

// Get the albums parent
function wppa_get_parentalbumid($id) {
global $album;
global $prev_album_id;

	if ( ! wppa_is_int($id) || $id < '1' ) return '0';

	if ( ! wppa_cache_album($id) ) {
		wppa_dbg_msg('Album '.$id.' no longer exists, but is still set as a parent of '.$prev_album_id.'. Please correct this.', 'red');
		return '-9';	// Album does not exist
	}
	$prev_album_id = $id;
	return $album['a_parent'];
}

function wppa_html($str) {
global $wppa_opt;
// It is assumed that the raw data contains html.
// If html not allowed, filter specialchars
// To prevent duplicate filtering, first entity_decode
	$result = html_entity_decode($str);
	if ( ! $wppa_opt['wppa_html'] && ! current_user_can('wppa_moderate') ) {
		$result = htmlspecialchars($str);
	}
	return $result;
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
		if ( $id == '-2' ) {
			$name = is_admin() ? __('--- all ---', 'wppa') : __a('--- all ---', 'wppa_theme');
			return $name;
		}
		if ( $id == '-9' ) {
			$name = is_admin() ? __('--- deleted ---', 'wppa') : __a('--- deleted ---', 'wppa_theme');
			return $name;
		}
		if ( $extended == 'raw' ) {
			$name = stripslashes($wpdb->get_var($wpdb->prepare("SELECT `name` FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $id)));
			return $name;
		}
	}
	else {
		if ( $id == '-2' ) {
			$name = is_admin() ? __('All Albums', 'wppa') : __a('All Albums', 'wppa_theme');
			return $name;
		}
	}
	
	if ( ! $id ) return '';
	elseif ( ! is_numeric($id) || $id < '1' ) {
		wppa_dbg_msg('Invalid arg wppa_get_album_name('.$id.', '.$extended.')', 'red');
		return '';
	}
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
	if ( ! $desc ) return '';				// No content, need no filtering
	$desc = stripslashes($desc);			// Unescape
	$desc = __($desc);						// qTranslate 
	$desc = wppa_html($desc);				// Enable html
	$desc = balanceTags($desc, true);		// Balance tags

	if ( strpos($desc, 'w#') !== false ) {	// Is there any 'w#' ?
		// Keywords
		$keywords = array('name', 'owner', 'id', 'views');
		foreach ( $keywords as $keyword ) {
			$replacement = __(trim(stripslashes($album[$keyword])));
			if ( $replacement == '' ) $replacement = '&lsaquo;'.__a('none', 'wppa').'&rsaquo;';
			$desc = str_replace('w#'.$keyword, $replacement, $desc);
		}

		// Timestamps
		$timestamps = array('timestamp', 'modified');	// Identical, there is only timestamp, but it acts as modified
		foreach ( $timestamps as $timestamp ) {
			if ( $album['timestamp'] ) {
				$desc = str_replace('w#'.$timestamp, wppa_local_date(get_option('date_format', "F j, Y,").' '.get_option('time_format', "g:i a"), $album['timestamp']), $desc);
			}
			else {
				$desc = str_replace('w#'.$timestamp, '&lsaquo;'.__a('unknown').'&rsaquo;', $desc);
			}
		}
	}
	
	// To prevent recursive rendering of scripts or shortcodes:
	$desc = str_replace(array('%%wppa%%', '[wppa', '[/wppa]'), array('%-wppa-%', '{wppa', '{/wppa}'), $desc);
	
	// Convert links and mailto:
	$desc = make_clickable($desc);
	
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

function wppa_add_parents($pages) {
global $wpdb;

	if ( is_array($pages) ) foreach ( array_keys($pages) as $index ) {
		$tempid = $pages[$index]['ID'];
		$pages[$index]['post_title'] = __(stripslashes($pages[$index]['post_title']));
		while ( $tempid > '0') {
			$tempid = $wpdb->get_var($wpdb->prepare("SELECT `post_parent` FROM `" . $wpdb->posts . "` WHERE `ID` = %s", $tempid));
			if ( $tempid > '0' ) {
				$pages[$index]['post_title'] = __(stripslashes($wpdb->get_var($wpdb->prepare("SELECT `post_title` FROM `" . $wpdb->posts . "` WHERE `ID` = %s", $tempid)))).' > '.$pages[$index]['post_title'];
			}
			else $tempid = '0';			
		}
	}
	return $pages;
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
	else {
		foreach ( array_keys($result) as $tag ) {
			$result[$tag]['ids'] = wppa_index_string_to_array($result[$tag]['ids']);
		}
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
	$tosave = array();
	if ( is_array($result) ) {
		foreach ( array_keys($result) as $key ) {
			$result[$key]['fraction'] = sprintf('%4.2f', $result[$key]['count'] / $total);
		}
		$result = wppa_array_sort($result, 'tag');
		$tosave = $result;
		foreach ( array_keys($tosave) as $key ) {
			$tosave[$key]['ids'] = wppa_index_array_to_string($tosave[$key]['ids']);
		}
	}
	update_option('wppa_taglist', $tosave);
	return $result;
}

function wppa_get_catlist() {
	$result = get_option('wppa_catlist', 'nil');
	if ( $result == 'nil' ) {
		$result = wppa_create_catlist();
	}
	else {
		foreach ( array_keys($result) as $cat ) {
			$result[$cat]['ids'] = wppa_index_string_to_array($result[$cat]['ids']);
		}
	}
	return $result;
}

function wppa_clear_catlist() {
	if ( get_option('wppa_catlist', 'nil') != 'nil' ) {
		delete_option('wppa_catlist');
	}
}

function wppa_create_catlist() {
global $wpdb;
	$result = false;
	$total = '0';
	$albums = $wpdb->get_results("SELECT `id`, `cats` FROM `".WPPA_ALBUMS."` WHERE `cats` <> ''", ARRAY_A);
	if ( $albums ) foreach ( $albums as $album ) {
		$cats = explode(',', $album['cats']);
		if ( $cats ) foreach ( $cats as $cat ) {
			if ( $cat ) {
				if ( ! isset($result[$cat]) ) {	// A new cat
					$result[$cat]['cat'] = $cat;
					$result[$cat]['count'] = '1';
					$result[$cat]['ids'][] = $album['id'];
				}
				else {							// An existing cat
					$result[$cat]['count']++;
					$result[$cat]['ids'][] = $album['id'];
				}
			}
			$total++;
		}
	}
	$tosave = array();
	if ( is_array($result) ) {
		foreach ( array_keys($result) as $key ) {
			$result[$key]['fraction'] = sprintf('%4.2f', $result[$key]['count'] / $total);
		}
		$result = wppa_array_sort($result, 'cat');
		$tosave = $result;
		foreach ( array_keys($tosave) as $key ) {
			$tosave[$key]['ids'] = wppa_index_array_to_string($tosave[$key]['ids']);
		}
	}
	update_option('wppa_catlist', $tosave);
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

function wppa_photo_exists($id) {
global $wpdb;
	return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $id));
}

function wppa_albumphoto_exists($alb, $photo) {
global $wpdb;
	return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_PHOTOS."` WHERE `album` = %s AND `filename` = %s", $alb, $photo));
}

function wppa_dislike_check($photo) {
global $wppa_opt;
global $wpdb;
	
	$count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM `".WPPA_RATING."` WHERE `photo` = %s AND `value` = -1", $photo ));
	
	if ( $wppa_opt['wppa_dislike_mail_every'] > '0') {		// Feature enabled?
		if ( $count % $wppa_opt['wppa_dislike_mail_every'] == '0' ) {	// Mail the admin
			$to        = get_bloginfo('admin_email');
			$subj 	   = __('Notification of inappropriate image', 'wppa');
			$cont['0'] = sprintf(__('Photo %s has been marked as inappropriate by %s different visitors.', 'wppa'), $photo, $count);
			$cont['1'] = '<a href="'.get_admin_url().'admin.php?page=wppa_admin_menu&tab=pmod&photo='.$photo.'" >'.__('Manage photo', 'wppa').'</a>';
			wppa_send_mail($to, $subj, $cont, $photo);
		}
	}
	
	if ( $wppa_opt['wppa_dislike_set_pending'] > '0') {		// Feature enabled?
		if ( $count == $wppa_opt['wppa_dislike_set_pending'] ) {
			$wpdb->query($wpdb->prepare( "UPDATE `".WPPA_PHOTOS."` SET `status` = 'pending' WHERE `id` = %s", $photo ));
			$to        = get_bloginfo('admin_email');
			$subj 	   = __('Notification of inappropriate image', 'wppa');
			$cont['0'] = sprintf(__('Photo %s has been marked as inappropriate by %s different visitors.', 'wppa'), $photo, $count);
			$cont['0'] .= "\n".__('The status has been changed to \'pending\'.', 'wppa');
			$cont['1'] = '<a href="'.get_admin_url().'admin.php?page=wppa_admin_menu&tab=pmod&photo='.$photo.'" >'.__('Manage photo', 'wppa').'</a>';
			wppa_send_mail($to, $subj, $cont, $photo);
		}
	}
	
	if ( $wppa_opt['wppa_dislike_delete'] > '0') {			// Feature enabled?
		if ( $count == $wppa_opt['wppa_dislike_delete'] ) {
			$to        = get_bloginfo('admin_email');
			$subj 	   = __('Notification of inappropriate image', 'wppa');
			$cont['0'] = sprintf(__('Photo %s has been marked as inappropriate by %s different visitors.', 'wppa'), $photo, $count);
			$cont['0'] .= "\n".__('It has been deleted.', 'wppa');
			$cont['1'] = '';//<a href="'.get_admin_url().'admin.php?page=wppa_admin_menu&tab=pmod&photo='.$photo.'" >'.__('Manage photo', 'wppa').'</a>';
			wppa_send_mail($to, $subj, $cont, $photo);
			wppa_delete_photo($photo);
		}
	}
}



function wppa_dislike_get($photo) {
global $wpdb;

	$count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM `".WPPA_RATING."` WHERE `photo` = %s AND `value` = -1", $photo ));
	return $count;
}

function wppa_send_mail($to, $subj, $cont, $photo, $email = '') {

	wppa_dbg_msg('Sending mail to '.$to.' !');

	$from			= 'From: noreply@'.substr(home_url(), strpos(home_url(), '.') + '1');
	$extraheaders 	= "\n" . 'MIME-Version: 1.0' . "\n" . 'Content-Transfer-Encoding: 8bit' . "\n" . 'Content-Type: text/html; charset="UTF-8"';
	$message 		= '
<html>
	<head>
		<title>'.$subj.'</title>
		<style>blockquote { color:#000077; background-color: #dddddd; border:1px solid black; padding: 6px; border-radius 4px;} </style>
	</head>
	<body>
		<h3>'.$subj.'</h3>
		<p><img src="'.wppa_get_thumb_url($photo).'" /></p>';
		if ( is_array($cont) ) {
			foreach ( $cont as $c ) if ( $c ) {
				$message .= '
		<p>'.$c.'</p>';
			}
		}
		else {
			$message .= '
		<p>'.$cont.'</p>';
		}
		if ( is_user_logged_in() ) {
			global $current_user;
			get_currentuserinfo();
			$e = $current_user->user_email;
			$eml = sprintf(__a('The visitors email address is: <a href="mailto:%s">%s</a>'), $e, $e);
			$message .= '
		<p>'.$eml.'</p>';
		}
		elseif ( $email ) {
			$e = $email;
			$eml = sprintf(__a('The visitor says his email address is: <a href="mailto:%s">%s</a>'), $e, $e);
			$message .= '
		<p>'.$eml.'</p>';
		}
		$message .= '
		<p><small>'.sprintf(__a('This message is automaticly generated at %s. It is useless to respond to it.'), '<a href="'.home_url().'" >'.home_url().'</a>').'</small></p>';
		$message .= '
	</body>
</html>';
				
	$iret = mail( $to , '['.str_replace('&#039;', '', get_bloginfo('name')).'] '.$subj , $message , $from . $extraheaders, '' );
	if ( ! $iret ) echo 'Mail sending Failed';
}

function wppa_get_imgalt($id) {
global $thumb;
global $wppa_opt;

	wppa_cache_thumb($id);
	switch ( $wppa_opt['wppa_alt_type'] ) {
		case 'fullname':
			$result = ' alt="'.esc_attr(wppa_get_photo_name($id)).'" ';
			break;
		case 'namenoext':
			$temp = wppa_get_photo_name($id);
			$temp = preg_replace('/\.[^.]*$/', '', $temp);	// Remove file extension
//			$ext = strrchr($temp, '.');
//			if ( $ext ) {
//				$temp = strstr($temp, $ext, true);
//			}
			$result = ' alt="'.esc_attr($temp).'" ';
			break;
		case 'custom':
			$result = ' alt="'.esc_attr($thumb['alt']).'" ';
			break;
		default:
			$result = '';
			break;
	}
	return $result;
}

function wppa_flush_treecounts($alb = '') {
global $wppa;
/*
	$albums = '0';
	$photos = '1';
	$selfalbums = '3';
	$selfphotos = '4';
	$pendphotos = '5';
*/	
	if ( $alb ) {
		$wppa['counts'] = get_option('wppa_counts', array());
		$wppa['treecounts'] = get_option('wppa_counts_tree', array());
		if ( isset($wppa['counts'][$alb]) ) {
			unset($wppa['counts'][$alb]);
			update_option('wppa_counts', $wppa['counts']);
		}
		if ( isset($wppa['treecounts'][$alb]) ) {
			unset($wppa['treecounts'][$alb]);
			update_option('wppa_counts_tree', $wppa['treecounts']);
		}
		$parent = wppa_get_parentalbumid($alb);
		if ( $parent > '0' ) wppa_flush_treecounts($parent);
	}
	else {
		delete_option('wppa_counts');
		delete_option('wppa_counts_tree');
	}
}

function wppa_treecount_a($alb) {
global $wpdb;
global $wppa;
	
	$albums = '0';
	$photos = '1';
	$selfalbums = '3';
	$selfphotos = '4';
	$pendphotos = '5';

	// See if we have this in cache
	if ( ! isset($wppa['counts']) ) {
		$wppa['counts'] = get_option('wppa_counts', array());			// Initial fetch
	}
	if ( ! isset($wppa['treecounts']) ) {
		$wppa['treecounts'] = get_option('wppa_counts_tree', array());	// Initial fetch
	}
	if ( isset($wppa['counts'][$alb]) && isset($wppa['treecounts']) ) {	// Album found
		$result['albums'] = $wppa['treecounts'][$alb][$albums];			// Use data
		$result['photos'] = $wppa['treecounts'][$alb][$photos];
		$result['selfalbums'] = $wppa['counts'][$alb][$selfalbums];
		$result['selfphotos'] = $wppa['counts'][$alb][$selfphotos];
		$result['pendphotos'] = $wppa['counts'][$alb][$pendphotos];
		return $result;													// And return
	}
	else {	// Not in cache
		$albs	 	 = $wpdb->get_results($wpdb->prepare('SELECT `id` FROM `'.WPPA_ALBUMS.'` WHERE `a_parent` = %s', $alb), ARRAY_A);
		$album_count = empty($albs) ? '0' : count($albs);
		$photo_count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `'.WPPA_PHOTOS.'`  WHERE `album` = %s AND `status` <> "pending"', $alb));
		$pend_count  = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `'.WPPA_PHOTOS.'`  WHERE `album` = %s AND `status` = "pending"', $alb));
		// Result this level
		$result = array('albums' => $album_count, 'photos' => $photo_count, 'selfalbums' => $album_count, 'selfphotos' => $photo_count, 'pendphotos' => $pend_count);
		// Subalbums to process?
		if ( empty($albs) ) {}
		else foreach ( $albs as $albm ) {
			$subcount = wppa_treecount_a($albm['id']);
			$result['albums'] += $subcount['albums'];
			$result['photos'] += $subcount['photos'];
		}
		// Save to cache
		$wppa['treecounts'][$alb][$albums] = $result['albums'];
		$wppa['treecounts'][$alb][$photos] = $result['photos'];
		$wppa['counts'][$alb][$selfalbums] = $result['selfalbums'];
		$wppa['counts'][$alb][$selfphotos] = $result['selfphotos'];
		$wppa['counts'][$alb][$pendphotos] = $result['pendphotos'];
		update_option('wppa_counts', $wppa['counts']);
		update_option('wppa_counts_tree', $wppa['treecounts']);
		return $result;
	}
}

function wppa_is_time_up($count = '') {
global $wppa_starttime;
global $wppa_opt;

	$timnow = microtime(true);
	$laptim = $timnow - $wppa_starttime;

	$maxwppatim = get_option('wppa_max_execution_time');
	$maxinitim = ini_get('max_execution_time');
	
	if ( $maxwppatim && $maxinitim ) $maxtim = min($maxwppatim, $maxinitim);
	elseif ( $maxwppatim ) $maxtim = $maxwppatim;
	elseif ( $maxinitim ) $maxtim = $maxinitim;
	else return false;
	
	wppa_dbg_msg('Maxtim = '.$maxtim.', elapsed = '.$laptim, 'red');
	if ( ! $maxtim ) return false;	// No limit or no value
	if ( ( $maxtim - $laptim ) > '5' ) return false;
	if ( $count ) {
		if ( is_admin() ) {
			if ( wppa_switch('wppa_auto_continue') ) {
				wppa_warning_message(sprintf(__('Time out after processing %s items.', 'wppa'), $count));
			}
			else {
				wppa_error_message(sprintf(__('Time out after processing %s items. Please restart this operation', 'wppa'), $count));
			}
		}
		else {
			wppa_err_alert(sprintf(__('Time out after processing %s items. Please restart this operation', 'wppa_theme'), $count));
		}
	}
	return true;
}

function wppa_save_source( $file, $name, $alb ) {
global $wppa_opt;

	if ( ( wppa_switch('wppa_keep_source_admin') && is_admin() ) || ( wppa_switch('wppa_keep_source_frontend') && ! is_admin() ) ) {
		if ( ! is_dir( $wppa_opt['wppa_source_dir'] ) ) @ mkdir( $wppa_opt['wppa_source_dir'] );
		$sourcedir = wppa_get_source_dir();
		if ( ! is_dir( $sourcedir ) ) @ mkdir( $sourcedir );
		$albdir = wppa_get_source_album_dir( $alb ); 
		if ( ! is_dir( $albdir ) ) @ mkdir( $albdir );	
		$dest = $albdir.'/'.$name;
		if ( $file != $dest ) @ copy(  $file, $dest );	// Do not copy to self, and do not bother on failure
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
wppa_log('Debug', 'in move,');

	if ( wppa_switch('wppa_keep_sync') ) {
		$frompath 	= wppa_get_source_album_dir( $from ).'/'.$name;
		if ( ! is_file( $frompath ) ) return;
		$todir 		= wppa_get_source_album_dir( $to );
		$topath 	= wppa_get_source_album_dir( $to ).'/'.$name;
		if ( ! is_dir( $todir ) ) @ mkdir( $todir );
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
		if ( ! is_dir( $todir ) ) @ mkdir( $todir );
		@ copy($frompath, $topath); // !
	}
}

function wppa_delete_album_source( $album ) {
global $wppa_opt;
	if ( wppa_switch('wppa_keep_sync') ) {
		@ rmdir( wppa_get_source_album_dir( $album ) );
	}
}

// Update photo modified timestamp
function wppa_update_modified($photo) {
global $wpdb;
	$wpdb->query($wpdb->prepare("UPDATE `".WPPA_PHOTOS."` SET `modified` = %s WHERE `id` = %s", time(), $photo));
}

function wppa_nl_to_txt($text) {
	return str_replace("\n", "\\n", $text);
}
function wppa_txt_to_nl($text) {
	return str_replace('\n', "\n", $text);
}

// Check query arg on tags
function wppa_vfy_arg($arg, $txt = false) {
	if ( isset($_REQUEST[$arg]) ) {
		if ( $txt ) {	// Text is allowed, but without tags
			$reason = ( defined('WP_DEBUG') && WP_DEBUG ) ? ': '.$arg.' contains tags.' : '';
			if ( $_REQUEST[$arg] != strip_tags($_REQUEST[$arg]) ) wp_die('Security check failue'.$reason);
		}
		else {
			$reason = ( defined('WP_DEBUG') && WP_DEBUG ) ? ': '.$arg.' is not numeric.' : '';
			if ( ! is_numeric($_REQUEST[$arg]) ) wp_die('Security check failue'.$reason);
		}
	}
}

// Strip tags with content
function wppa_strip_tags($text, $key = '') {

	if ($key == 'all') {
		$text = preg_replace(	array	(	'@<a[^>]*?>.*?</a>@siu',				// unescaped <a> tag
											'@&lt;a[^>]*?&gt;.*?&lt;/a&gt;@siu',	// escaped <a> tag
											'@<table[^>]*?>.*?</table>@siu',
											'@<style[^>]*?>.*?</style>@siu',
											'@<div[^>]*?>.*?</div>@siu'
										),
								array	( ' ', ' ', ' ', ' ', ' '
										),
								$text );
		$text = str_replace(array('<br/>', '<br />'), ' ', $text);
		$text = strip_tags($text);
	}
	elseif ( $key == 'script' ) {
		$text = preg_replace('@<script[^>]*?>.*?</script>@siu', ' ', $text );
	}
	elseif ( $key == 'div' ) {
		$text = preg_replace('@<div[^>]*?>.*?</div>@siu', ' ', $text );
	}
	elseif ( $key == 'script&style' ) {
		$text = preg_replace(	array	(	'@<script[^>]*?>.*?</script>@siu',
											'@<style[^>]*?>.*?</style>@siu'
										),
								array	( ' ', ' '
										),
								$text );
	}
	else {
		$text = preg_replace(	array	(	'@<a[^>]*?>.*?</a>@siu',				// unescaped <a> tag
											'@&lt;a[^>]*?&gt;.*?&lt;/a&gt;@siu'		// escaped <a> tag
										),
								array	( ' ', ' '
										),
								$text );
	}
	return trim($text);
}

// Update album timestamp
function wppa_update_album_timestamp($album) {
global $wpdb;

	$wpdb->query($wpdb->prepare('UPDATE `'.WPPA_ALBUMS.'` SET `timestamp` = %s WHERE `id` = %s', time(), $album));
}

// set last album 
function wppa_set_last_album($id = '') {
    global $albumid;
	
	$opt = 'wppa_last_album_used-'.wppa_get_user('login');
			
	if ( is_numeric($id) && wppa_have_access($id) ) $albumid = $id; else $albumid = '';

    wppa_update_option($opt, $albumid);
}

// get last album
function wppa_get_last_album() {
    global $albumid;
    
    if ( is_numeric($albumid) ) $result = $albumid;
    else {
		$opt = 'wppa_last_album_used-'.wppa_get_user('login');
		$result = get_option($opt, get_option('wppa_last_album_used', ''));
	}
    if ( !is_numeric($result) ) $result = '';
    else $albumid = $result;

	return $result; 
}

// Combine margin or padding style
function wppa_combine_style($type, $top = '0', $left = '0', $right = '0', $bottom = '0') {
// echo $top.' '.$left.' '.$right.' '.$bottom.'<br />';
	$result = $type.':';			// Either 'margin:' or 'padding:'
	if ( $left == $right ) {
		if ( $top == $bottom ) {
			if ( $top == $left ) {	// All the same: one size fits all
				$result .= $top;
				if ( is_numeric($top) && $top > '0' ) $result .= 'px';
			}
			else {					// Top=Bot and Lft=Rht: two sizes
				$result .= $top;
				if ( is_numeric($top) && $top > '0' ) $result .= 'px '; else $result .= ' ';
				$result .= $left;
				if ( is_numeric($left) && $left > '0' ) $result .= 'px';
			}
		}
		else {						// Top, Lft=Rht, Bot: 3 sizes
			$result .= $top;
			if ( is_numeric($top) && $top > '0' ) $result .= 'px '; else $result .= ' ';
			$result .= $left;
			if ( is_numeric($left) && $left > '0' ) $result .= 'px '; else $result .= ' ';
			$result .= $bottom;
			if ( is_numeric($bottom) && $bottom > '0' ) $result .= 'px';
		}
	}
	else {							// Top, Rht, Bot, Lft: 4 sizes
		$result .= $top;
		if ( is_numeric($top) && $top > '0' ) $result .= 'px '; else $result .= ' ';
		$result .= $right;
		if ( is_numeric($right) && $right > '0' ) $result .= 'px '; else $result .= ' ';
		$result .= $bottom;
		if ( is_numeric($bottom) && $bottom > '0' ) $result .= 'px '; else $result .= ' ';
		$result .= $left;
		if ( is_numeric($left) && $left > '0' ) $result .= 'px';
	}
	$result .= ';';
	return $result;
}

// A temp routine to fix an old bug
function wppa_fix_source_extensions() {
global $wpdb;
global $wppa_opt;

	$start_time = time();
	$end = $start_time + '15';
	$count = '0';
	$start = get_option('wppa_sourcefile_fix_start', '0');
	if ( $start == '-1' ) return; // Done!
	
	$photos = $wpdb->get_results("SELECT `id`, `album`, `name`, `filename` FROM `".WPPA_PHOTOS."` WHERE `filename` <> ''  AND `filename` <> `name` AND `id` > ".$start." ORDER BY `id`", ARRAY_A);
	if ( $photos ) {
		foreach ( $photos as $data ) {
			$faulty_sourcefile_name = $wppa_opt['wppa_source_dir'].'/album-'.$data['album'].'/'.preg_replace('/\.[^.]*$/', '', $data['filename']);
			if ( is_file($faulty_sourcefile_name) ) {
				$proper_sourcefile_name = $wppa_opt['wppa_source_dir'].'/album-'.$data['album'].'/'.$data['filename'];
				if ( is_file($proper_sourcefile_name) ) {
					unlink($faulty_sourcefile_name);
				}
				else {
					rename($faulty_sourcefile_name, $proper_sourcefile_name);
				}
				$count++;
			}
			if ( time() > $end ) {
				wppa_ok_message('Fixed '.$count.' faulty sourcefile names. Last was '.$data['id'].'. Not finished yet. I will continue fixing next time you enter this page. Sorry for the inconvenience.');
				update_option('wppa_sourcefile_fix_start', $data['id']);
				return;
			}
		}
	}
	echo $count.' source file extensions repaired';
	update_option('wppa_sourcefile_fix_start', '-1');
}

// Delete a photo and all its attrs by id
function wppa_delete_photo($photo) {
global $wpdb;

	$photoinfo = $wpdb->get_row($wpdb->prepare('SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `id` = %s', $photo), ARRAY_A);
	// Get file extension
	$ext = $photoinfo['ext']; 
	// Get album
	$album = $photoinfo['album'];
	// Get filename
	$filename = $photoinfo['filename'];
	// Delete fullsize image
	$file = wppa_get_photo_path($photo);
	if ( file_exists($file) && ! is_dir($file) ) unlink($file);
	// Delete thumbnail image
	$file = wppa_get_thumb_path($photo);
	if ( file_exists($file) && ! is_dir($file) ) unlink($file);
	// Delete sourcefile
	wppa_delete_source($filename, $album);
	// Delete index
	wppa_index_remove('photo', $photo);
	// Delete db entries
	$wpdb->query($wpdb->prepare('DELETE FROM `'.WPPA_PHOTOS.'` WHERE `id` = %s LIMIT 1', $photo));
	$wpdb->query($wpdb->prepare('DELETE FROM `'.WPPA_RATING.'` WHERE `photo` = %s', $photo));
	$wpdb->query($wpdb->prepare('DELETE FROM `'.WPPA_COMMENTS.'` WHERE `photo` = %s', $photo));
	$wpdb->query($wpdb->prepare('DELETE FROM `'.WPPA_IPTC.'` WHERE `photo` = %s', $photo));
	$wpdb->query($wpdb->prepare('DELETE FROM `'.WPPA_EXIF.'` WHERE `photo` = %s', $photo));
	wppa_flush_treecounts($album);
	wppa_flush_upldr_cache('photoid', $photo);
	// Delete from cloud
	switch ( wppa_cdn() ) {
		case 'cloudinary':
			wppa_delete_from_cloudinary( $photo );
			break;
	}
}

function wppa_microtime($txt = '') {
static $old;

	$new = microtime(true);
	if ( $old ) {
		$delta = $new - $old;
		$old = $new;
		$msg = sprintf('%s took %7.3f s.', $txt, $delta);
		wppa_dbg_msg($msg, 'green', true);
	}
	else $old = $new;
}

function wppa_sanitize_cats($value) {
	return wppa_sanitize_tags($value);
}
function wppa_sanitize_tags($value, $keepsemi = false) {
	// Sanitize
	$value = strip_tags($value);					// Security
	$value = str_replace('"', '', $value);			// Remove funny chars
	$value = str_replace('\'', '', $value);			// ...
	$value = str_replace('\\', '', $value);			// ...
	$value = stripslashes($value);					// ...
	$value = str_replace(' ', '', $value);			// Remove spaces
	// Find separator
	$sep = ',';										// Default seperator
	if ( $keepsemi ) {								// ';' allowed
		if ( strpos($value, ';') !== false ) {		// and found at least one ';'
			$value = str_replace(',', ';', $value);	// convert all separators to ';'
			$sep = ';';
		}											// ... a mix is not permitted
	}
	else {	  
		$value = str_replace(';', ',', $value);		// Convert all seps to default separator ','
	}
	
	$temp = explode($sep, $value);
	if ( is_array($temp) ) {
		asort($temp);								// Sort
		$value = '';
		$first = true;
		$previdx = '';
		foreach ( array_keys($temp) as $idx ) {
			if ( strlen( $temp[$idx] ) > '1' ) {
				$temp[$idx] = strtoupper(substr($temp[$idx], 0, 1)).strtolower(substr($temp[$idx], 1));
				if ( $temp[$idx] ) {
					if ( $first ) {
						$first = false;
						$value .= $temp[$idx];
						$previdx = $idx;
					}
					elseif ( $temp[$idx] !=  $temp[$previdx] ) {	// Skip duplicates
						$value .= $sep.$temp[$idx];
						$previdx = $idx;
					}
				}		
			}
		}
	}
	return $value;
}

function wppa_series_to_array($xtxt) {
	$txt = str_replace(' ', '', $xtxt);					// Remove spaces
	if ( strpos($txt, '.') === false ) return false;	// Not an enum/series, the only legal way to return false
	if ( strpos($txt, '...') !== false ) {
		wppa_stx_err('Max 2 successive dots allowed. '.$txt);
		return false;
	}
	if ( substr($txt, 0, 1) == '.' ) {
		wppa_stx_err('Missing starting number. '.$txt);
		return false;
	}
	if ( substr($txt, -1) == '.' ) {
		wppa_stx_err('Missing ending number. '.$txt);
		return false;
	}
	$t = str_replace(array('.','0','1','2','3','4','5','6','7','8','9'), '',$txt);
	if ( $t ) {
		wppa_stx_err('Illegal character(s): "'.$t.'" found. '.$txt);
		return false;
	}
	$temp = explode('.', $txt);
	$tempcopy = $temp;
	
	foreach ( array_keys($temp) as $i ) {
		if ( ! $temp[$i] ) { 							// found a '..'
			if ( $temp[$i-'1'] >= $temp[$i+'1'] ) {
				wppa_stx_err('Start > end. '.$txt);
				return false;
			}
			for ( $j=$temp[$i-'1']+'1'; $j<$temp[$i+'1']; $j++ ) {
				$tempcopy[] = $j;
			}
		}
		else {
			if ( ! is_numeric($temp[$i] ) ) {
				wppa_stx_err('A enum or range token must be a number. '.$txt);
				return false;
			}
		}
	}
	$result = $tempcopy;
	foreach ( array_keys($result) as $i ) {
		if ( ! $result[$i] ) unset($result[$i]);
	}
	return $result;
}
function wppa_stx_err($msg) {
	echo 'Syntax error in album specification. '.$msg;
}

function wppa_get_og_desc($id) {

	$result = sprintf(__a('See this image on %s'), str_replace('&amp;', __a('and'), get_bloginfo('name')));
	$r2 	= strip_shortcodes(wppa_strip_tags(wppa_get_photo_desc($id)), 'all');
	if ( $r2 ) $result .= ': '.$r2;
	return $result;
}

// There is no php routine to test if a string var is an integer, like '3': yes, and '3.7' and '3..7': no.
// is_numeric('3.7') returns true
// intval('3..7') == '3..7' returns true
// is_int('3') returns false
// so we make it ourselves
function wppa_is_int($var) {
	return ( strval(intval($var)) == strval($var) );
}

function wppa_log($type, $msg) {
	@ mkdir(ABSPATH.'wp-content/wppa-depot/admin'); // Just in case...
	if ( ! $file = fopen(ABSPATH.'wp-content/wppa-depot/admin/error.log', 'ab') ) return;	// Unable to open log file
	fwrite($file, $type.': on:'.wppa_local_date(get_option('date_format', "F j, Y,").' '.get_option('time_format', "g:i a"), time()).': '.$msg."\n");
	fclose($file);
}

function wppa_is_landscape($img_attr) {
	return ($img_attr[0] > $img_attr[1]);
}

function wppa_get_the_id() {
global $wppa;
	$id = '0';
	if ( $wppa['ajax'] ) {
		if ( isset($_GET['page_id']) ) $id = $_GET['page_id'];
		elseif ( isset($_GET['p']) ) $id = $_GET['p'];
		elseif ( isset($_GET['wppa-fromp']) ) $id = $_GET['wppa-fromp'];
	}
	else {
		$id = get_the_ID();
	}
	return $id;
}

function wppa_get_artmonkey_size_a($photo) {
global $wppa_opt;
global $wpdb;

	$data = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $photo), ARRAY_A);
	if ( $data ) {
		if ( wppa_switch('wppa_artmonkey_use_source') ) {
			if ( is_file($wppa_opt['wppa_source_dir'].'/album-'.$data['album'].'/'.$data['filename']) ) {
				$source = $wppa_opt['wppa_source_dir'].'/album-'.$data['album'].'/'.$data['filename'];
			}
			else {
				$source = wppa_get_photo_path($photo);
			}
		}
		else {
			$source = wppa_get_photo_path($photo);
		}
		$imgattr = @ getimagesize($source);
		if ( is_array($imgattr) ) {
			$fs = filesize($source);
			if ( $fs > 1024*1024 ) $fs = sprintf('%4.2f Mb', $fs/(1024*1024));
			else $fs = sprintf('%4.2f Kb', $fs/1024);
			$result = array('x' => $imgattr['0'], 'y' => $imgattr['1'], 's' => $fs);
			return $result;
		}
	}
	return false;
}

function wppa_get_the_landing_page($slug, $title) {
global $wppa_opt;

	$page = $wppa_opt[$slug];
	if ( ! $page || ! wppa_page_exists($page) ) {
	$page = wppa_create_page($title);
		wppa_update_option($slug, $page);
		$wppa_opt[$slug] = $page;
	}
	return $page;
}

function wppa_get_the_auto_page($photo) {
global $thumb;
global $wpdb;

	if ( ! $photo ) return '0';
	wppa_cache_thumb($photo);
	if ( wppa_page_exists( $thumb['page_id'] ) ) return $thumb['page_id'];
	$page = wppa_create_page( $thumb['name'], '[wppa type="autopage"][/wppa]' );
	$thumb['page_id'] = $page;
	$wpdb->query( "UPDATE `".WPPA_PHOTOS."` SET `page_id` = ".$page." WHERE `id` = ".$photo );
	return $page;
}

function wppa_create_page( $title, $shortcode = '[wppa type="landing"][/wppa]' ) {
			
	$my_page = array(
				'post_title'    => $title,
				'post_content'  => $shortcode,
				'post_status'   => 'publish',
				'post_type'	  	=> 'page'
			);

	$page = wp_insert_post( $my_page );
	return $page;
}

function wppa_page_exists($id) {
global $wpdb;

	if ( ! $id ) return false;
	$iret = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `" . $wpdb->posts . "` WHERE `post_type` = 'page' AND `post_status` = 'publish' AND `ID` = %s", $id));
	return ( $iret > '0' );
}

function wppa_get_photo_owner($id) {
global $thumb;

	wppa_cache_thumb($id);
	return $thumb['owner'];
}

function wppa_cdn() {
global $wppa_opt;

	// What did we specify in the settings page?
	$cdn = $wppa_opt['wppa_cdn_service'];
	
	// Check for fully configured and active
	switch ( $cdn ) {
		case 'cloudinary':
			if ( $wppa_opt['wppa_cdn_cloud_name'] && $wppa_opt['wppa_cdn_api_key'] && $wppa_opt['wppa_cdn_api_secret'] ) {
			}
			else {
				$cdn = '';	// Incomplete configuration
			}
			break;
			
		default:
			$cdn = '';

	}
	
	return $cdn;
}

function wppa_get_source_path( $id ) {
global $wppa_opt;
global $blog_id;
global $thumb;

	wppa_cache_thumb( $id );
	
	$multi = is_multisite();
//	$multi = true;	// debug
	if ( $multi && ! WPPA_MULTISITE_GLOBAL ) {
		$blog = '/blog-'.$blog_id;
	}
	else {
		$blog = '';
	}
	$source_path = $wppa_opt['wppa_source_dir'].$blog.'/album-'.$thumb['album'].'/'.$thumb['filename'];
	
	return $source_path;
}

// Get url of photo with highest available resolution.
// Not for display ( need not to download fast ) but for external services like Fotomoto
function wppa_get_hires_url( $id ) {
	$source_path = wppa_get_source_path( $id );
	if ( file_exists( $source_path ) ) {
		$temp = explode( 'wp-content', $source_path );		
		$hires_url = get_bloginfo('wpurl').'/wp-content'.$temp['1'];
	}
	else {
		$hires_url = wppa_get_photo_url( $id );
	}
	$temp = explode( '?', $hires_url );
	$hires_url = $temp['0'];
	return $hires_url;
}

function wppa_get_source_dir() {
global $wppa_opt;
global $blog_id;

	$multi = is_multisite();
//	$multi = true;	// debug
	if ( $multi && ! WPPA_MULTISITE_GLOBAL ) {
		$blog = '/blog-'.$blog_id;
	}
	else {
		$blog = '';
	}
	$source_dir = $wppa_opt['wppa_source_dir'].$blog;
	
	return $source_dir;
}

function wppa_get_source_album_dir( $alb ) {
global $wppa_opt;
global $blog_id;

	$multi = is_multisite();
//	$multi = true;	// debug
	if ( $multi && ! WPPA_MULTISITE_GLOBAL ) {
		$blog = '/blog-'.$blog_id;
	}
	else {
		$blog = '';
	}
	$source_album_dir = $wppa_opt['wppa_source_dir'].$blog.'/album-'.$alb;
	
	return $source_album_dir;
}


function wppa_set_default_name( $id ) {
global $wpdb;
global $wppa_opt;
global $thumb;

	if ( ! wppa_is_int( $id ) ) return;
	wppa_cache_thumb( $id );
	
	$name 		= $thumb['filename']; 	// The default default
	$filename 	= $thumb['filename'];
	
	switch ( $wppa_opt['wppa_newphoto_name_method'] ) {
		case 'filename':
			break;
		case 'noext':
			$name = preg_replace('/\.[^.]*$/', '', $name);
			break;
		case '2#005':
			$tag = '2#005';
			$name = $wpdb->get_var( $wpdb->prepare( "SELECT `description` FROM `".WPPA_IPTC."` WHERE `photo` = %s AND `tag` = %s", $id, $tag ) );
			break;
		case '2#120':
			$tag = '2#120';
			$name = $wpdb->get_var( $wpdb->prepare( "SELECT `description` FROM `".WPPA_IPTC."` WHERE `photo` = %s AND `tag` = %s", $id, $tag ) );
			break;
	}
	if ( $name && ( $name != $filename ) ) {	// Update name
		$wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_PHOTOS."` SET `name` = %s WHERE `id` = %s", $name, $id ) );
		$thumb['name'] = $name;	// Update cache
	}
	if ( ! wppa_switch('wppa_save_iptc') ) { 	// He doesn't want to keep the iptc data, so...
		$wpdb->query($wpdb->prepare( "DELETE FROM `".WPPA_IPTC."` WHERE `photo` = %s", $id ) );
	}
}

function wppa_set_default_tags( $id ) {
global $wpdb;
global $thumb;
global $album;

	wppa_cache_thumb( $id );
	$alb = $thumb['album'];
	wppa_cache_album( $alb );
	
	$tags = wppa_sanitize_tags( str_replace( array( ' ', '\'', '"'), ',', wppa_filter_iptc( wppa_filter_exif( $album['default_tags'], $id ), $id ) ) );
	
	$wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_PHOTOS."` SET `tags` = %s WHERE `id` = %s", $tags, $id ) );
}
