<?php
/* wppa-links.php
* Package: wp-photo-album-plus
*
* Frontend links
* Version 5.1.17
*
*/

if ( ! defined( 'ABSPATH' ) )
    die( "Can't load this file directly" );

// get permalink plus ? or & and possible debug switch
function wppa_get_permalink($key = '', $plain = false) {
global $wppa;
global $wppa_opt;
global $wppa_lang;

	if ( ! $key && is_search() ) $key = $wppa_opt['wppa_search_linkpage'];
	
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

//	$al = admin_url('admin-ajax.php').'?action=wppa&amp;wppa-action=render';
	$al = WPPA_URL.'/wppa-ajax-front.php?action=wppa&amp;wppa-action=render';
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
		if ( $wppa['is_upldr'] ) $link .= '&amp;wppa-upldr='.$wppa['is_upldr'];
		// can be extended for other special cases, see wppa_thumb_default() in wppa-functions.php
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
		if ( $wppa['is_upldr'] ) $link .= '&amp;wppa-upldr='.$wppa['is_upldr'];
		// can be extended for other special cases, see wppa_thumb_default() in wppa-functions.php
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
				case 'wo':
					$newuri .= 'wppa-woccur=';
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
					break;
				case 'rl':
					$newuri .= 'wppa-rel=';
					break;
				case 'rc':
					$newuri .= 'wppa-relcount=';
					break;
				case 'ul':
					$newuri .= 'wppa-upldr=';
					break;
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
	$support = array('album', 'photo', 'slide', 'cover', 'occur', 'woccur', 'page', 'searchstring', 'topten', 'lasten', 'comten', 'featen', 'randseed', 'lang', 'single', 'tag', 'photos-only', 'debug', 'rel', 'relcount', 'upldr');
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
					case 'woccur':
						$newuri .= 'wo';
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
					case 'rel':
						$newuri .= 'rl';
						break;
					case 'relcount':
						$newuri .= 'rc';
						break;
					case 'upldr':
						$newuri .= 'ul';
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
	if ( $wppa['is_related'] ) $url .= 'wppa-rel='.$wppa['is_related'].'&amp;wppa-relcount='.$wppa['related_count'].'&amp;';
	elseif ( $wppa['is_tag'] ) $url .= 'wppa-tag='.$wppa['is_tag'].'&amp;';
	$url .= 'wppa-photo=' . $callbackid;
//	if ( $wppa['is_owner'] ) $url .= 'wppa-owner='.$wppa['is_owner'].'&amp;';
	if ( $wppa['is_upldr'] ) $url .= 'wppa-upldr='.$wppa['is_upldr'].'&amp;';
		
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
	if ( $wppa['is_related'] ) $url .= 'wppa-rel='.$wppa['is_related'].'&amp;wppa-relcount='.$wppa['related_count'].'&amp;';
	elseif ( $wppa['is_tag'] ) $url .= 'wppa-tag='.$wppa['is_tag'].'&amp;';
//	if ( $wppa['is_owner'] ) $url .= 'wppa-owner='.$wppa['is_owner'].'&amp;';
	if ( $wppa['is_upldr'] ) $url .= 'wppa-upldr='.$wppa['is_upldr'].'&amp;';

	$url = substr($url, 0, strlen($url) - 5);	// remove last '&amp;'
		
	return $url;
}

function wppa_get_upldr_link($user) {
global $wppa_opt;

	$page = $wppa_opt['wppa_upldr_widget_linkpage'];
	$url = wppa_get_permalink($page);
	
	$url .= 'wppa-upldr='.$user.'&amp;';
	$url .= 'wppa-cover=0&amp;';
	$url .= 'wppa-occur=1&amp;';

	$url = substr($url, 0, strlen($url) - 5);	// remove last '&amp;'
	
	return $url;
}

function wppa_page_links($npages = '1', $curpage = '1', $slide = false) {
global $wppa;
global $wppa_opt;
	
	if ($npages < '2') return;	// Nothing to display
	if (is_feed()) {
		return;
	}

	// Compose the Previous and Next Page urls
	$link_url = wppa_get_permalink();
	$ajax_url = wppa_get_ajaxlink();

	// cover
	if (wppa_get_get('cover')) $ic = wppa_get_get('cover');
	else {
		if ($wppa['is_cover'] == '1') $ic = '1'; else $ic = '0';
	}
	$extra_url = 'wppa-cover='.$ic;
	// album
//	if ( $wppa['start_album'] ) $alb = $wppa['start_album'];
//	elseif (wppa_get_get('album')) $alb = wppa_get_get('album');
	$occur = $wppa['in_widget'] ? wppa_get_get('woccur', '0') : wppa_get_get('occur', '0');
	$ref_occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
	if (($occur == $ref_occur || $wppa['ajax'] ) && wppa_get_get('album')) {
			$alb = wppa_get_get('album');
	}
	elseif ( $wppa['start_album'] ) $alb = $wppa['start_album'];
	else $alb = '0';
//	if ( $alb ) 
		$extra_url .= '&amp;wppa-album='.$alb;
	
	// photo
	if (wppa_get_get('photo')) {
		$extra_url .= '&amp;wppa-photo='.wppa_get_get('photo');
	}
	// occur
	if ( ! $wppa['ajax'] ) {
		$occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
		$w = $wppa['in_widget'] ? 'w' : '';
		$extra_url .= '&amp;wppa-'.$w.'occur='.$occur;
	}
	else {
		if ( isset($_GET['wppa-occur']) ) {
			$occur = $_GET['wppa-occur'];
			$extra_url .= '&amp;wppa-occur='.strip_tags($occur);
		}
		elseif ( isset($_GET['wppa-woccur']) ) {
			$occur = $_GET['wppa-woccur'];
			$extra_url .= '&amp;wppa-woccur='.strip_tags($occur);
		}
		else {
			$extra_url .= '&amp;wppa-occur='.$wppa['occur'];	// Should never get here?
		}
	}
	// Topten?
	if ( $wppa['is_topten'] ) $extra_url .= '&amp;wppa-topten='.$wppa['topten_count'];
	// Lasten?
	if ( $wppa['is_lasten'] ) $extra_url .= '&amp;wppa-lasten='.$wppa['lasten_count'];
	// Comten?
	if ( $wppa['is_comten'] ) $extra_url .= '&amp;wppa-comten='.$wppa['comten_count'];
	// Featen?
	if ( $wppa['is_featen'] ) $extra_url .= '&amp;wppa-featen='.$wppa['featen_count'];
	// Tag?
	if ( $wppa['is_tag'] && ! $wppa['is_related'] ) $extra_url .= '&amp;wppa-tag='.$wppa['is_tag'];
	// Search?
	if ( $wppa['src'] && ! $wppa['is_related'] ) $extra_url .= '&amp;wppa-searchstring='.urlencode($wppa['searchstring']);
	// Related
	if ( $wppa['is_related'] ) $extra_url .= '&amp;wppa-rel='.$wppa['is_related'].'&amp;wppa-relcount='.$wppa['related_count'];
	// Uploader?
	if ( $wppa['is_upldr'] ) $extra_url .= '&amp;wppa-upldr='.$wppa['is_upldr'];
	// Photos only?
	if ( $wppa['photos_only'] ) $extra_url .= '&amp;wppa-photos-only=1';
	
	// Slideshow?
	if ( $slide ) $extra_url .= '&amp;wppa-slide';
	
	// Almost ready
	$link_url .= $extra_url;
	$ajax_url .= $extra_url;

	// Adjust display range
	$from = 1;
	$to = $npages;
	if ($npages > '7') {
		$from = $curpage - '3';
		$to = $curpage + 3;
		while ($from < '1') {
			$from++;
			$to++;
		}
		while ($to > $npages) {
			$from--;
			$to--;
		}
	}

	// Doit
	$wppa['out'] .= wppa_nltab('+').'<div id="prevnext-a-'.$wppa['master_occur'].'" class="wppa-nav-text wppa-box wppa-nav" style="clear:both; text-align:center; '.__wcs('wppa-box').__wcs('wppa-nav').'" >';
		$vis = $curpage == '1' ? 'visibility: hidden;' : '';
		$wppa['out'] .= wppa_nltab('+').'<div id="prev-page" style="float:left; text-align:left; '.$vis.'">';
			$wppa['out'] .= wppa_nltab().'<span class="wppa-arrow" style="'.__wcs('wppa-arrow').'cursor: default;">&laquo;&nbsp;</span>';
			if ( $wppa_opt['wppa_allow_ajax'] ) $wppa['out'] .= wppa_nltab().'<a style="cursor:pointer;" id="p-p" onclick="wppaDoAjaxRender('.$wppa['master_occur'].', \''.$ajax_url.'&amp;wppa-page='.($curpage - 1).'\', \''.wppa_convert_to_pretty($link_url.'&amp;wppa-page='.($curpage - 1)).'\')" >'.__a('Prev.&nbsp;page').'</a>';
			else $wppa['out'] .= wppa_nltab().'<a style="cursor:pointer;" id="p-p" href="'.$link_url.'&amp;wppa-page='.($curpage - 1).'" >'.__a('Prev.&nbsp;page', 'wppa_theme').'</a>';
		$wppa['out'] .= wppa_nltab('-').'</div><!-- #prev-page -->';
		$vis = $curpage == $npages ? 'visibility: hidden;' : '';
		$wppa['out'] .= wppa_nltab('+').'<div id="next-page" style="float:right; text-align:right; '.$vis.'">';
			if ( $wppa_opt['wppa_allow_ajax'] ) $wppa['out'] .= wppa_nltab().'<a style="cursor:pointer;" id="n-p" onclick="wppaDoAjaxRender('.$wppa['master_occur'].', \''.$ajax_url.'&amp;wppa-page='.($curpage + 1).'\', \''.wppa_convert_to_pretty($link_url.'&amp;wppa-page='.($curpage + 1)).'\')" >'.__a('Next&nbsp;page').'</a>';
			else $wppa['out'] .= wppa_nltab().'<a style="cursor:pointer;" id="n-p" href="'.$link_url.'&amp;wppa-page='.($curpage + 1).'" >'.__a('Next&nbsp;page').'</a>';
			$wppa['out'] .= wppa_nltab().'<span class="wppa-arrow" style="'.__wcs('wppa-arrow').'cursor: default;">&nbsp;&raquo;</span>';
		$wppa['out'] .= wppa_nltab('-').'</div><!-- #next-page -->';
		
		if ($from > '1') {
			$wppa['out'] .= ('.&nbsp;.&nbsp;.&nbsp;');
		}
		for ($i=$from; $i<=$to; $i++) {
			if ($curpage == $i) { 
				$wppa['out'] .= wppa_nltab('+').'<div class="wppa-mini-box wppa-alt wppa-black" style="display:inline; text-align:center; '.__wcs('wppa-mini-box').__wcs('wppa-alt').__wcs('wppa-black').' text-decoration: none; cursor: default; font-weight:normal; " >';
//					$wppa['out'] .= wppa_nltab().'<a style="font-weight:normal; text-decoration: none; cursor: default; '.__wcs('wppa-black').'">&nbsp;'.$i.'&nbsp;</a>';
					$wppa['out'] .= wppa_nltab().'&nbsp;'.$i.'&nbsp;';
				$wppa['out'] .= wppa_nltab('-').'</div>';
			}
			else { 
				$wppa['out'] .= wppa_nltab('+').'<div class="wppa-mini-box wppa-even" style="display:inline; text-align:center; '.__wcs('wppa-mini-box').__wcs('wppa-even').'" >';
					if ( $wppa_opt['wppa_allow_ajax'] ) $wppa['out'] .= wppa_nltab().'<a style="cursor:pointer;" onclick="wppaDoAjaxRender('.$wppa['master_occur'].', \''.$ajax_url.'&amp;wppa-page='.$i.'\', \''.wppa_convert_to_pretty($link_url.'&amp;wppa-page='.$i).'\')">&nbsp;'.$i.'&nbsp;</a>';
					else $wppa['out'] .= wppa_nltab().'<a style="cursor:pointer;" href="'.$link_url.'&amp;wppa-page='.$i.'">&nbsp;'.$i.'&nbsp;</a>';
				$wppa['out'] .= wppa_nltab('-').'</div>';	
			}
		}
		if ($to < $npages) {
			$wppa['out'] .= ('&nbsp;.&nbsp;.&nbsp;.');
		}
	$wppa['out'] .= wppa_nltab('-').'</div><!-- #prevnext-a-'.$wppa['master_occur'].' -->';
}
