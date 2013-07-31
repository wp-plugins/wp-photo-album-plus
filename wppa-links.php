<?php
/* wppa-links.php
* Package: wp-photo-album-plus
*
* Frontend links
* Version 5.0.17
*
*/

if ( ! defined( 'ABSPATH' ) )
    die( "Can't load this file directly" );

// get permalink plus ? or & and possible debug switch
function wppa_get_permalink($key = '', $plain = false) {
global $wppa;
global $wppa_opt;
global $wppa_lang;
//$z=-get_num_queries();	
	if ( !$key && is_search() ) $key = $wppa_opt['wppa_search_linkpage'];
	
	switch ($key) {
		case '0':
		case '':	// normal permalink
			if ($wppa['in_widget']) {
				$pl = home_url();
				if ( $plain ) return $pl;
				if (strpos($pl, '?')) $pl .= '&amp;';
				else $pl .= '?';
				}
			else {
				if ( $wppa['ajax'] ) {
					if ( isset($_GET['page_id']) ) $id = $_GET['page_id'];
					elseif ( isset($_GET['p']) ) $id = $_GET['p'];
					elseif ( isset($_GET['wppa-fromp']) ) $id = $_GET['wppa-fromp'];
					else $id = '';
					$pl = get_permalink(intval($id));
					if ( $plain ) return $pl;
					if (strpos($pl, '?')) $pl .= '&amp;';
					else $pl .= '?';
				}
				else {
					$pl = get_permalink();
					if ( $plain ) return $pl;
					if (strpos($pl, '?')) $pl .= '&amp;';
					else $pl .= '?';
//					$pl .= 'wppa-fromp='.get_the_ID().'&amp;';
				}
			}
			break;
		case 'js':	// normal permalink for js use
			if ($wppa['in_widget']) {
				$pl = home_url();
				if ( $plain ) return $pl;
				if (strpos($pl, '?')) $pl .= '&';
				else $pl .= '?';
			}
			else {
				if ( $wppa['ajax'] ) {
					if ( isset($_GET['page_id']) ) $id = $_GET['page_id'];
					elseif ( isset($_GET['p']) ) $id = $_GET['p'];
					elseif ( isset($_GET['wppa-fromp']) ) $id = $_GET['wppa-fromp'];
					else $id = '';
					$pl = get_permalink(intval($id));
					if ( $plain ) return $pl;
					if (strpos($pl, '?')) $pl .= '&';
					else $pl .= '?';
				}
				else {
					$pl = get_permalink();
					if ( $plain ) return $pl;
					if (strpos($pl, '?')) $pl .= '&';
					else $pl .= '?';
//					$pl .= 'wppa-fromp='.get_the_ID().'&';
				}
			}
			break;
		default:	// pagelink
			$pl = get_page_link($key);
			if ( $plain ) return $pl;
			if (strpos($pl, '?')) $pl .= '&amp;';
			else $pl .= '?';
			break;
	}

	if ( isset($_GET['lang']) ) {	// If lang in querystring: keep it
		if ( strpos($pl, 'lang=') === false ) { 	// Not yet
			if ( $key == 'js' ) $pl .= 'lang='.$wppa_lang.'&';
			else $pl .= 'lang='.$wppa_lang.'&amp;';
		}
	}
	
	if ($wppa['debug']) {
		if ( $key == 'js' ) $pl .= 'debug='.$wppa['debug'].'&';
		else $pl .= 'debug='.$wppa['debug'].'&amp;';
	}
	
	return $pl;
}

// Like get_permalink but for ajax use
function wppa_get_ajaxlink($key = '') {
global $wppa;
global $wppa_lang;

	$al = admin_url('admin-ajax.php').'?action=wppa&amp;wppa-action=render';
	// See if this call is from an ajax operation or...
	if ( $wppa['ajax'] ) {
		if ( isset($_GET['wppa-size']) ) $al .= '&amp;wppa-size='.$_GET['wppa-size'];
		if ( isset($_GET['wppa-moccur']) ) $al .= '&amp;wppa-moccur='.$_GET['wppa-moccur'];
		if ( is_numeric($key) && $key > '0' ) {
			$al .= '&amp;page_id='.$key;
		}
		else {
			if ( isset($_GET['page_id']) ) $al .= '&amp;page_id='.$_GET['page_id'];
		}
		if ( isset($_GET['p']) ) $al .= '&amp;p='.$_GET['p'];
		if ( isset($_GET['wppa-fromp']) ) $al .= '&amp;wppa-fromp='.$_GET['wppa-fromp'];
	}
	else {	// directly from a page or post
		$al .= '&amp;wppa-size='.wppa_get_container_width();
		$al .= '&amp;wppa-moccur='.$wppa['master_occur'];
		if ( is_numeric($key) && $key > '0' ) {
			$al .= '&amp;page_id='.$key;
		}
		else {
			if ( isset($_GET['p']) ) $al .= '&amp;p='.$_GET['p'];
			if ( isset($_GET['page_id']) ) $al .= '&amp;page_id='.$_GET['page_id'];
		}
		$al .= '&amp;wppa-fromp='.get_the_ID();
	}
	
	if ( isset($_GET['lang']) ) {	// If lang in querystring: keep it
		if ( strpos($al, 'lang=') === false ) { 	// Not yet
			if ( $key == 'js' ) $al .= 'lang='.$wppa_lang.'&';
			else $al .= 'lang='.$wppa_lang.'&amp;';
		}
	}

	if ($wppa['debug']) {
		$al .= '&amp;debug='.$wppa['debug'];
	}

	return $al.'&amp;';
}

// get page url of current album image
function wppa_get_image_page_url_by_id($id, $single = false, $alb = false) {
global $wppa;
global $thumb;
	
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_image_page_url_by_id('.$id.')', 'red');

	wppa_cache_thumb($id);
	
	$occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
	$w = $wppa['in_widget'] ? 'w' : '';

	if ( ! $alb ) $alb = $thumb['album'];
	
	$result = wppa_get_permalink().'wppa-album='.$alb.'&amp;wppa-photo='.$thumb['id'].'&amp;wppa-cover=0&amp;wppa-'.$w.'occur='.$occur;	
	if ( $single ) $result .= '&amp;wppa-single=1';
	
	return $result;
}

// get page url of current album image, ajax version
function wppa_get_image_url_ajax_by_id($id) {
global $wppa;
global $thumb;
	
	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_image_url_ajax_by_id('.$id.')', 'red');

	wppa_cache_thumb($id);
	
	$occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
	$w = $wppa['in_widget'] ? 'w' : '';
	
	return wppa_get_ajaxlink().'wppa-album='.$thumb['album'].'&amp;wppa-photo='.$thumb['id'].'&amp;wppa-cover=0&amp;wppa-'.$w.'occur='.$occur;	
}

// get link to album by id or in loop
function wppa_get_album_url($xid = '', $pag = '') {
global $album;
global $wppa;
	
	$occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
	$w = $wppa['in_widget'] ? 'w' : '';
	
	if ($xid != '') $id = $xid;
	elseif (isset($album['id'])) {
		$id = $album['id'];
	}
	else $id = '';
	if ($id != '') {
		$link = wppa_get_permalink($pag).'wppa-album='.$id.'&amp;wppa-cover=0&amp;wppa-'.$w.'occur='.$occur;
	}
	else $link = '';
    return $link;
}

// get link to album by id or in loop ajax version
function wppa_get_album_url_ajax($xid = '', $pag = '') {
global $album;
global $wppa;
	
	$occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
	$w = $wppa['in_widget'] ? 'w' : '';
	
	if ($xid != '') $id = $xid;
	elseif (isset($album['id'])) {
		$id = $album['id'];
	}
	else $id = '';
	if ($id != '') {
		$link = wppa_get_ajaxlink($pag).'wppa-album='.$id.'&amp;wppa-cover=0&amp;wppa-'.$w.'occur='.$occur;
	}
	else $link = '';
    return $link;
}

// get link to slideshow (in loop)
function wppa_get_slideshow_url($xid = '', $page = '') {
global $album;
global $wppa;
	
	if ($xid != '') $id = $xid;
	elseif (isset($album['id'])) {
		$id = $album['id'];
	}
	
	if ($id != '') {
		$occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
		$w = $wppa['in_widget'] ? 'w' : '';
		$link = wppa_get_permalink($page).'wppa-album='.$id.'&amp;wppa-slide'.'&amp;wppa-'.$w.'occur='.$occur;	// slide=true changed in slide
	}
	else {
		$link = '';
	}
	
	return $link;	
}

// get link to slideshow (in loop) ajax version
function wppa_get_slideshow_url_ajax($xid = '', $page = '') {
global $album;
global $wppa;
	
	if ($xid != '') $id = $xid;
	elseif (isset($album['id'])) {
		$id = $album['id'];
	}
	
	if ($id != '') {
		$occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
		$w = $wppa['in_widget'] ? 'w' : '';
		$link = wppa_get_ajaxlink($page).'wppa-album='.$id.'&amp;wppa-slide'.'&amp;wppa-'.$w.'occur='.$occur;	// slide=true changed in slide
	}
	else {
		$link = '';
	}
	
	return $link;	
}

// Pretty links decode
function wppa_convert_from_pretty($uri) {
global $wppa_opt;

	// Test if we should be here anyway
	if ( ! isset($_ENV["SCRIPT_URI"]) ) return $uri;
	$wppapos = stripos($uri, '/wppaspec/');
	if ( ! $wppapos ) return $uri;	// Is not a pretty link
	if ( ! get_option('permalink_structure') ) return $uri;
	
	// copy start up to including slash before wppaspec
	$newuri = substr($uri, 0, $wppapos+1);				
	
	// explode part after wppaspec/
	$args = explode('/', substr($uri, $wppapos+10));	
	
	// process 'arguments'
	if ( count($args > 0) ) {
		$first = true;
		foreach ( $args as $arg ) {
			if ( $first ) $newuri .= '?'; else $newuri .= '&';
			$first = false;
			$code = substr($arg, 0, 2);
			switch ( $code ) {
				case 'ab':
					$newuri .= 'wppa-album=';
					break;
				case 'pt':
					$newuri .= 'wppa-photo=';
					break;
				case 'sd':
					$newuri .= 'wppa-slide';
					break;
				case 'cv':
					$newuri .= 'wppa-cover=';
					break;
				case 'oc':
					$newuri .= 'wppa-occur=';
					break;
				case 'pg':
					$newuri .= 'wppa-page=';
					break;
				case 'ss':
					$newuri .= 'wppa-searchstring=';
					break;
				case 'tt':
					$newuri .= 'wppa-topten=';
					break;
				case 'lt':
					$newuri .= 'wppa-lasten=';
					break;
				case 'ct':
					$newuri .= 'wppa-comten=';
					break;
				case 'ft':
					$newuri .= 'wppa-featen=';
					break;
				case 'ln':
					$newuri .= 'lang=';
					break;
				case 'si':
					$newuri .= 'wppa-single=';
					break;
				case 'tg':
					$newuri .= 'wppa-tag=';
					break;
				case 'po':
					$newuri .= 'wppa-photos-only=';
					break;
				case 'rs':
					$newuri .= 'wppa-randseed=';
					break;
				case 'db':
					$newuri .= 'debug=';
					
			}
//			if ( $code == 'ss' ) $newuri .= str_replace('|', ' ', substr($arg, 2));
//			else 
				$newuri .= substr($arg, 2);
		}
	}

	return $newuri;
}

// Pretty links Encode
function wppa_convert_to_pretty($xuri) {
global $wppa_opt;

	if ( ! $wppa_opt['wppa_use_pretty_links'] ) return $xuri;
	if ( ! get_option('permalink_structure') ) return $xuri;
	if ( ! isset($_ENV["SCRIPT_URI"]) ) return $xuri;

	// Do some preprocessing
	$uri = str_replace('&amp;', '&', $xuri);
	$uri = str_replace('wppa-', '', $uri);

	// Test if querystring exists
	$qpos = stripos($uri, '?');
	if ( ! $qpos ) return $uri;

	// Make sure we end without '/'
	$newuri = trim(substr($uri, 0, $qpos), '/');
	$newuri .= '/wppaspec';
	
	// explode querystring
	$args = explode('&', substr($uri, $qpos+1));
	$support = array('album', 'photo', 'slide', 'cover', 'occur', 'page', 'searchstring', 'topten', 'lasten', 'comten', 'featen', 'randseed', 'lang', 'single', 'tag', 'photos-only', 'debug');
	if ( count($args) > 0 ) {
		foreach ( $args as $arg ) {
			$t = explode('=', $arg);
			$code = $t['0'];
			if ( isset($t['1']) ) $val = $t['1']; else $val = false;
			if ( in_array( $code, $support ) ) {
				$newuri .= '/';
				switch ( $code ) {
					case 'album':
						$newuri .= 'ab';
						break;
					case 'photo':
						$newuri .= 'pt';
						break;
					case 'slide':
						$newuri .= 'sd';
						break;
					case 'cover':
						$newuri .= 'cv';
						break;
					case 'occur':
						$newuri .= 'oc';
						break;
					case 'page':
						$newuri .= 'pg';
						break;
					case 'searchstring':
						$newuri .= 'ss';
						break;
					case 'topten':
						$newuri .= 'tt';
						break;
					case 'lasten':
						$newuri .= 'lt';
						break;
					case 'comten':
						$newuri .= 'ct';
						break;
					case 'featen':
						$newuri .= 'ft';
						break;
					case 'lang':
						$newuri .= 'ln';
						break;
					case 'single':
						$newuri .= 'si';
						break;
					case 'tag':
						$newuri .= 'tg';
						break;
					case 'photos-only':
						$newuri .= 'po';
						break;
					case 'randseed':
						$newuri .= 'rs';
						break;
					case 'debug':
						$newuri .= 'db';
						break;
				}
				if ( $val !== false ) {
					if ( $code == 'searchstring' ) $newuri .= str_replace(' ', '_', $val);
					else $newuri .= $val;
				}
			}
		}
	}
	
	return $newuri;
}

// Moderate links
function wppa_moderate_links($type, $id, $comid = '') {

	if ( current_user_can('wppa_moderate') || ( current_user_can('wppa_comments') && $type == 'comment' ) ) {
		switch ( $type ) {
			case 'thumb':
				$app = __a('App');
				$mod = __a('Mod');
				$del = __a('Del');
				$result = '
				<br class="wppa-approve-'.$id.'" />
				<a class="wppa-approve-'.$id.'" style="font-weight:bold; color:green; cursor:pointer;" onclick="if ( confirm(\''.__a('Are you sure you want to publish this photo?').'\') ) wppaAjaxApprovePhoto(\''.$id.'\')">
					'.$app.
				'</a>
				<a class="wppa-approve-'.$id.'" style="font-weight:bold; color:blue; cursor:pointer;" onclick="document.location=\''.get_admin_url().'admin.php?page=wppa_moderate_photos&amp;photo='.$id.'\'" >
					'.$mod.
				'</a>
				<a class="wppa-approve-'.$id.'" style="font-weight:bold; color:red; cursor:pointer;" onclick="if ( confirm(\''.__a('Are you sure you want to remove this photo?').'\') ) wppaAjaxRemovePhoto(\''.$id.'\', true)">
					'.$del.
				'</a><br class="wppa-approve-'.$id.'" />';
				break;
			case 'slide':
				$app = __a('Approve');
				$mod = __a('Moderate');
				$del = __a('Delete');
				$result = '
				<br class="wppa-approve-'.$id.'" />
				<a class="wppa-approve-'.$id.'" style="font-weight:bold; color:green; cursor:pointer;" onclick="if ( confirm(\''.__a('Are you sure you want to publish this photo?').'\') ) wppaAjaxApprovePhoto(\''.$id.'\')">
					'.$app.
				'</a>
				<a class="wppa-approve-'.$id.'" style="font-weight:bold; color:blue; cursor:pointer;" onclick="document.location=\''.get_admin_url().'admin.php?page=wppa_moderate_photos&amp;photo='.$id.'\'" >
					'.$mod.
				'</a>
				<a class="wppa-approve-'.$id.'" style="font-weight:bold; color:red; cursor:pointer;" onclick="if ( confirm(\''.__a('Are you sure you want to remove this photo?').'\') ) wppaAjaxRemovePhoto(\''.$id.'\', true)">
					'.$del.
				'</a><br class="wppa-approve-'.$id.'" />';
				break;
			case 'comment':
				$app = __a('Approve');
				$mod1 = __a('PhotoAdmin');
				$mod2 = __a('CommentAdmin');
				$del = __a('Delete');
				$result = '
				<br class="wppa-approve-'.$comid.'" />
				<a class="wppa-approve-'.$comid.'" style="font-weight:bold; color:green; cursor:pointer;" onclick="if ( confirm(\''.__a('Are you sure you want to publish this comment?').'\') ) wppaAjaxApproveComment(\''.$comid.'\')">
					'.$app.
				'</a>';
				if ( current_user_can('wppa_moderate') ) $result .= '
				<a class="wppa-approve-'.$comid.'" style="font-weight:bold; color:blue; cursor:pointer;" onclick="document.location=\''.get_admin_url().'admin.php?page=wppa_moderate_photos&amp;photo='.$id.'\'" >
					'.$mod1.
				'</a>';
				if ( current_user_can('wppa_comments') ) $result .= '
				<a class="wppa-approve-'.$comid.'" style="font-weight:bold; color:blue; cursor:pointer;" onclick="document.location=\''.get_admin_url().'admin.php?page=wppa_manage_comments&amp;commentid='.$comid.'\'" > 
					'.$mod2.
				'</a>';
				$result .= '
				<a class="wppa-approve-'.$comid.'" style="font-weight:bold; color:red; cursor:pointer;" onclick="if ( confirm(\''.__a('Are you sure you want to remove this comment?').'\') ) wppaAjaxRemoveComment(\''.$comid.'\', true)">
					'.$del.
				'</a><br class="wppa-approve-'.$comid.'" />';
				break;
			default:
			echo 'error type='.$type;
				break;
		}
	}
	else {
		$result = '<br /><span style="color:red">'.__a('Awaiting moderation').'</span>';
	}
	return $result;
}

// Get the type of link for an album title. Used in wppa-breadcrumb.php
function wppa_get_album_title_linktype($alb) {
global $wpdb;

	if ( is_numeric($alb) ) $result = $wpdb->get_var( $wpdb->prepare( "SELECT cover_linktype FROM ".WPPA_ALBUMS." WHERE id = %s LIMIT 1", $alb ) );
	else $result = '';
	wppa_dbg_q('Q59');

	return $result;
}
