<?php
/* wppa-utils.php
* Package: wp-photo-album-plus
*
* Contains low-level utility routines
* Version 5.0.17
*
*/

function __a($txt, $dom = 'wppa_theme') {
	return __($txt, $dom);
}

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
function wppa_get_thumb_url($id, $system = 'flat') {
global $thumb;
$wppa_opt;

	if ( get_option('wppa_file_system') == 'flat' ) $system = 'flat';	// Have been converted, ignore argument
	if ( get_option('wppa_file_system') == 'tree' ) $system = 'tree';	// Have been converted, ignore argument
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_thumb_url('.$id.')', 'red');
	wppa_cache_thumb($id);
	if ( $system == 'tree' ) return WPPA_UPLOAD_URL.'/thumbs/'.wppa_expand_id($thumb['id']).'.'.$thumb['ext'].'?ver='.get_option('wppa_thumb_version', '1');
	else return WPPA_UPLOAD_URL.'/thumbs/'.$thumb['id'].'.'.$thumb['ext'].'?ver='.get_option('wppa_thumb_version', '1');
}
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
function wppa_get_photo_url($id, $system = 'flat') {
global $thumb;
$wppa_opt;

	if ( is_feed() && wppa_switch('wppa_feed_use_thumb') ) return wppa_get_thumb_url($id, $system);
	
	if ( get_option('wppa_file_system') == 'flat' ) $system = 'flat';	// Have been converted, ignore argument
	if ( get_option('wppa_file_system') == 'tree' ) $system = 'tree';	// Have been converted, ignore argument
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_photo_url('.$id.')', 'red');
	wppa_cache_thumb($id);
	if ( $system == 'tree' ) return WPPA_UPLOAD_URL.'/'.wppa_expand_id($thumb['id']).'.'.$thumb['ext'].'?ver='.get_option('wppa_photo_version', '1');
	else return WPPA_UPLOAD_URL.'/'.$thumb['id'].'.'.$thumb['ext'].'?ver='.get_option('wppa_photo_version', '1');
}
function wppa_bump_photo_rev() {
	wppa_update_option('wppa_photo_version', get_option('wppa_photo_version', '1') + '1');
}

// get path of a full sized image
function wppa_get_photo_path($id, $system = 'flat') {
global $thumb;
$wppa_opt;

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
function wppa_get_photo_desc($id, $do_shortcodes = false) {
global $thumb;
global $wppa;
global $wppa_opt;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_photo_desc('.$id.')', 'red');
	wppa_cache_thumb($id);
	$desc = $thumb['description'];			// Raw data
	$desc = stripslashes($desc);			// Unescape
	$desc = __($desc);						// qTranslate 

	// To prevent recursive rendering of scripts or shortcodes:
	$desc = str_replace(array('%%wppa%%', '[wppa', '[/wppa]'), array('%-wppa-%', '{wppa', '{/wppa}'), $desc);

	// Geo
	if ( $thumb['location'] && ! $wppa['in_widget'] && strpos($wppa_opt['wppa_custom_content'], 'w#location') !== false) {
		$temp = explode('/', $thumb['location']);
		$lat = $temp['2'];
		$lon = $temp['3'];
		$geo = str_replace('w#lon', $lon, str_replace('w#lat', $lat, $wppa_opt['wppa_gpx_shortcode']));
//		$geo = '[map style="width: auto; height:300px; margin:0; " marker="yes" lat="'.$lat.'" lon="'.$lon.'"]';
		$geo = do_shortcode($geo);
		$wppa['geo'] .= '<div id="geodiv-'.$wppa['master_occur'].'-'.$id.'" style="display:none;">'.$geo.'</div>';
	}
	
	if ( strpos($desc, 'w#') !== false ) {	// Is there any 'w#' ?
		// Keywords
		$keywords = array('name', 'filename', 'owner', 'id', 'tags');
		foreach ( $keywords as $keyword ) {
			$replacement = __(trim(stripslashes($thumb[$keyword])));
			if ( ! $replacement ) $replacement = '&lsaquo;'.__a('none', 'wppa').'&rsaquo;';
			$desc = str_replace('w#'.$keyword, $replacement, $desc);
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

	return $desc;
}

// See if an album is in a separate tree
function wppa_is_separate($id) {

	if ( $id == '' ) return false;
	if ( ! is_numeric($id) ) {
		wppa_dbg_msg('Invalid arg wppa_is_separate('.$id.')', 'red');
		return false;
	}
	if ( $id == '-1' ) return true;
	if ( $id < '1' ) return false;
	$alb = wppa_get_parentalbumid($id);
	
	return wppa_is_separate($alb);
}

// Get the albums parent
function wppa_get_parentalbumid($id) {
global $album;
global $prev_album_id;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_parentalbumid('.$id.')', 'red');
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
			$ext = strrchr($temp, '.');
			if ( $ext ) {
				$temp = strstr($temp, $ext, true);
			}
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

function wppa_save_source($file, $name, $alb) {
global $wppa_opt;

	if ( ( wppa_switch('wppa_keep_source_admin') && is_admin() ) || ( wppa_switch('wppa_keep_source_frontend') && ! is_admin() ) ) {
		$albdir = $wppa_opt['wppa_source_dir'].'/album-'.$alb;
		if ( ! is_dir($albdir) ) @ mkdir($albdir);	// This is a gimic, do not bother on failure
		$dest = $albdir.'/'.$name;
		if ( $file != $dest ) @ copy($file, $dest);	// Do not copy to self, and do not bother on failure
	}
}

function wppa_delete_source($name, $alb) {
global $wppa_opt;
	if ( wppa_switch('wppa_keep_sync') ) {
		$path = $wppa_opt['wppa_source_dir'].'/album-'.$alb.'/'.$name;
		@ unlink($path);										// Ignore error
		@ rmdir($wppa_opt['wppa_source_dir'].'/album-'.$alb);	// Ignore error
	}
}

function wppa_move_source($name, $from, $to) {
global $wppa_opt;
	if ( wppa_switch('wppa_keep_sync') ) {
		$frompath 	= $wppa_opt['wppa_source_dir'].'/album-'.$from.'/'.$name;
		if ( ! is_file($frompath) ) return;
		$todir 		= $wppa_opt['wppa_source_dir'].'/album-'.$to;
		$topath 	= $wppa_opt['wppa_source_dir'].'/album-'.$to.'/'.$name;
		if ( ! is_dir($todir) ) @ mkdir($todir);
		@ rename($frompath, $topath);		// will fail if target already exists
		@ unlink($frompath);				// therefor attempt delete
		@ rmdir($wppa_opt['wppa_source_dir'].'/album-'.$from);	// remove dir when empty Ignore error
	}
}

function wppa_copy_source($name, $from, $to) {
global $wppa_opt;
	if ( wppa_switch('wppa_keep_sync') ) {
		$frompath 	= $wppa_opt['wppa_source_dir'].'/album-'.$from.'/'.$name;
		if ( ! is_file($frompath) ) return;
		$todir 		= $wppa_opt['wppa_source_dir'].'/album-'.$to;
		$topath 	= $wppa_opt['wppa_source_dir'].'/album-'.$to.'/'.$name;
		if ( ! is_dir($todir) ) @ mkdir($todir);
		@ copy($frompath, $topath); // !
	}
}

function wppa_delete_album_source($album) {
global $wppa_opt;
	if ( wppa_switch('wppa_keep_sync') ) {
		@ rmdir($wppa_opt['wppa_source_dir'].'/album-'.$album);
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