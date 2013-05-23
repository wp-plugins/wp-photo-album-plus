<?php
/* wppa-utils.php
* Package: wp-photo-album-plus
*
* Contains low-level utility routines
* Version 5.0.4
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
	if ( ! $wppa_opt['wppa_html'] ) {
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

function wppa_dislike_add($photo) {
global $wppa_opt;

	$usr = wppa_get_user();
	$data = get_option('wppa_dislikes', false);
	
	if ( ! is_array($data) ) { 	// Empty
		$data[$photo][] = $usr;
		update_option('wppa_dislikes', $data);
		return;
	}
	else {
		if ( ! isset($data[$photo]) || ! in_array($usr, $data[$photo]) ) {
			$data[$photo][] = $usr;
			update_option('wppa_dislikes', $data);
			$count = count($data[$photo]);
			
			if ( $count % $wppa_opt['wppa_dislike_mail_every'] == '0' ) {	// Mail the admin
				$to        = get_bloginfo('admin_email');
				$subj 	   = __('Notification of inappropriate image', 'wppa');
				$cont['0'] = sprintf(__('Photo %s has been marked as inappropriate by %s different visitors.', 'wppa'), $photo, $count);
				$cont['1'] = '<a href="'.get_admin_url().'admin.php?page=wppa_admin_menu&tab=pmod&photo='.$photo.'" >'.__('Manage photo', 'wppa').'</a>';
				wppa_send_mail($to, $subj, $cont, $photo);
			}
		}
	}
}

function wppa_dislike_remove($photo) {

	$data = get_option('wppa_dislikes', false);
	if ( is_array($data) ) {
		if ( isset($data[$photo]) ) unset($data[$photo]);
		update_option('wppa_dislikes', $data);
	}
}

function wppa_dislike_get($photo) {
	
	$data = get_option('wppa_dislikes', false);
	if ( is_array($data) ) {
		if ( isset($data[$photo]) ) {
			return $data[$photo];
		}
	}
	return false;
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

function wppa_get_slide_callback_url($callbackid) {
global $wppa;

	$url = wppa_get_permalink();
	if ( $wppa['start_album'] ) $url .= 'wppa-album='.$wppa['start_album'].'&amp;';
	else $url .= 'wppa-album=0&amp;';
	$url .= 'wppa-cover=0&amp;';
	$url .= 'wppa-slide&amp;';
	if ( $wppa['is_single'] ) $url .= 'wppa-single=1&amp;';
	if ( $wppa['in_widget'] ) $url .= 'wppa-woccur='.$wppa['widget_occur'].'&amp;';
	else $url .= 'wppa-occur='.$wppa['occur'].'&amp;';
	if ( $wppa['is_topten'] ) $url .= 'wppa-topten='.$wppa['topten_count'].'&amp;';
	if ( $wppa['is_lasten'] ) $url .= 'wppa-lasten='.$wppa['lasten_count'].'&amp;';
	if ( $wppa['is_comten'] ) $url .= 'wppa-comten='.$wppa['comten_count'].'&amp;';
	if ( $wppa['is_tag'] ) $url .= 'wppa-tag='.$wppa['is_tag'].'&amp;';
	$url .= 'wppa-photo=' . $callbackid;
		
	return $url;
}

function wppa_get_thumb_callback_url() {
global $wppa;

	$url = wppa_get_permalink();
	if ( $wppa['start_album'] ) $url .= 'wppa-album='.$wppa['start_album'].'&amp;';
	else $url .= 'wppa-album=0&amp;';
	$url .= 'wppa-cover=0&amp;';
	if ( $wppa['is_single'] ) $url .= 'wppa-single=1&amp;';
	if ( $wppa['in_widget'] ) $url .= 'wppa-woccur='.$wppa['widget_occur'].'&amp;';
	else $url .= 'wppa-occur='.$wppa['occur'].'&amp;';
	if ( $wppa['is_topten'] ) $url .= 'wppa-topten='.$wppa['topten_count'].'&amp;';
	if ( $wppa['is_lasten'] ) $url .= 'wppa-lasten='.$wppa['lasten_count'].'&amp;';
	if ( $wppa['is_comten'] ) $url .= 'wppa-comten='.$wppa['comten_count'].'&amp;';
	if ( $wppa['is_tag'] ) $url .= 'wppa-tag='.$wppa['is_tag'].'&amp;';

	$url = substr($url, 0, strlen($url) - 5);	// remove last '&amp;'
		
	return $url;
}

function wppa_flush_treecounts($alb = '') {
global $wppa;

	if ( $alb ) {
		$wppa['treecounts'] = get_option('wppa_treecounts', array());
		if ( isset($wppa['treecounts'][$alb]) ) {
			unset($wppa['treecounts'][$alb]['albums']);
			unset($wppa['treecounts'][$alb]['photos']);
			unset($wppa['treecounts'][$alb]['selfalbums']);
			unset($wppa['treecounts'][$alb]['selfphotos']);
			unset($wppa['treecounts'][$alb]['pendphotos']);
			unset($wppa['treecounts'][$alb]);
			update_option('wppa_treecounts', $wppa['treecounts']);
		}
		$parent = wppa_get_parentalbumid($alb);
		if ( $parent > '0' ) wppa_flush_treecounts($parent);
	}
	else delete_option('wppa_treecounts');
}

function wppa_treecount_a($alb) {
global $wpdb;
global $wppa;
	
	// See if we have this in cache
	if ( ! isset($wppa['treecounts']) ) {
		$wppa['treecounts'] = get_option('wppa_treecounts', array());	// Initial fetch
	}
	if ( isset($wppa['treecounts'][$alb]) ) {							// Album found
		$result['albums'] = $wppa['treecounts'][$alb]['albums'];		// Use data
		$result['photos'] = $wppa['treecounts'][$alb]['photos'];
		$result['selfalbums'] = $wppa['treecounts'][$alb]['selfalbums'];
		$result['selfphotos'] = $wppa['treecounts'][$alb]['selfphotos'];
		$result['pendphotos'] = $wppa['treecounts'][$alb]['pendphotos'];
		return $result;													// And return
	}
	else {	// Not in cache
		$albums 	 = $wpdb->get_results($wpdb->prepare('SELECT `id` FROM `'.WPPA_ALBUMS.'` WHERE `a_parent` = %s', $alb), ARRAY_A);
		$album_count = empty($albums) ? '0' : count($albums);
		$photo_count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `'.WPPA_PHOTOS.'`  WHERE `album` = %s AND `status` <> "pending"', $alb));
		$pend_count  = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `'.WPPA_PHOTOS.'`  WHERE `album` = %s AND `status` = "pending"', $alb));
		
		$result = array('albums' => $album_count, 'photos' => $photo_count, 'selfalbums' => $album_count, 'selfphotos' => $photo_count, 'pendphotos' => $pend_count);
		if ( empty($albums) ) {}
		else foreach ( $albums as $album ) {
			$subcount = wppa_treecount_a($album['id']);
			$result['albums'] += $subcount['albums'];
			$result['photos'] += $subcount['photos'];
		}
		// Save to cache
		$wppa['treecounts'][$alb]['albums'] = $result['albums'];
		$wppa['treecounts'][$alb]['photos'] = $result['photos'];
		$wppa['treecounts'][$alb]['selfalbums'] = $result['selfalbums'];
		$wppa['treecounts'][$alb]['selfphotos'] = $result['selfphotos'];
		$wppa['treecounts'][$alb]['pendphotos'] = $result['pendphotos'];
		update_option('wppa_treecounts', $wppa['treecounts']);
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
				wppa_warning_message(sprintf(__('Time up after processing %s items.', 'wppa'), $count));
			}
			else {
				wppa_error_message(sprintf(__('Time up after processing %s items. Please restart this operation', 'wppa'), $count));
			}
		}
		else {
			wppa_err_alert(sprintf(__('Time up after processing %s items. Please restart this operation', 'wppa_theme'), $count));
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

// Expand compressed string
function wppa_index_string_to_array($string) {
	// Anything?
	if ( ! $string ) return array();
	// Any ranges?
	if ( ! strstr($string, '..') ) return explode(',', $string);	// No
	// Yes
	$temp = explode(',', $string);
	$result = array();
	foreach ( $temp as $t ) {
		if ( ! strstr($t, '..') ) $result[] = $t;
		else {
			$range = explode('..', $t);
			$from = $range['0'];
			$to = $range['1'];
			while ( $from <= $to ) {
				$result[] = $from;
				$from++;
			}
		}
	}
	return $result;
}

// Compress array ranges and convert to string
function wppa_index_array_to_string($array) {
	$result = '';
	$lastitem = '-1';
	$isrange = false;
	foreach ( $array as $item ) {
		if ( $item == $lastitem+'1' ) {
			$isrange = true;
		}
		else {
			if ( $isrange ) {	// Close range
				$result .= '..'.$lastitem.','.$item;
				$isrange = false;
			}
			else {				// Add single item
				$result .= ','.$item;
			}
				
			
		}
		$lastitem = $item;
	}
	if ( $isrange ) {	// Don't forget the last if it ends in a range
		$result .= '..'.$lastitem;
	}
	$result = trim($result, ',');
	return $result;
}

// Convert raw data string to indexable word array
function wppa_index_raw_to_words($xtext, $noskips = false) {

	$ignore = array( '"', "'", '\\', '>', '<', ',', ':', ';', '!', '?', '=', '_', '[', ']', '(', ')', '{', '}', '..', '...', '....', "\n", "\r", "\t", '.jpg', '.png', '.gif', '&#039', '&amp' );
	if ( $noskips ) $skips = array();
	else $skips = get_option('wppa_index_skips', array());
	
	$result = array();
	if ( $xtext ) {
		$text = strtolower($xtext);
		$text = html_entity_decode($text);
		$text = wppa_strip_tags($text, 'script&style');	// strip style and script tags inclusive content
		$text = str_replace('>', '> ', $text);			// Make sure <td>word1</td><td>word2</td> will not endup in 'word1word2', but in 'word1' 'word2'
		$text = strip_tags($text);						// Now strip the tags
		$text = str_replace($ignore, ' ', $text);		// Remove funny chars
		$text = trim($text);
		$text = trim($text, " ./-");
		while ( strpos($text, '  ') ) $text = str_replace('  ', ' ', $text);	// Compress spaces
		$words = explode(' ', $text);
		foreach ( $words as $word ) {
			$word = trim($word);
			$word = trim($word, " ./-");
			if ( strlen($word) > '1' && ! in_array($word, $skips) ) $result[] = $word;
			if ( strpos($word, '-') !== false ) {
				$fracts = explode('-', $word);
				foreach ( $fracts as $fract ) {
					$fract = trim($fract);
					$fract = trim($fract, " ./-");
					if ( strlen($fract) > '1' && ! in_array($fract, $skips) ) $result[] = $fract;
				}
			}
		}
	}
	return $result;
}