<?php
/* wppa-links.php
* Package: wp-photo-album-plus
*
* Frontend links
* Version 5.4.18
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

// get permalink plus ? or & and possible debug switch
function wppa_get_permalink( $key = '', $plain = false ) {
global $wppa;
global $wppa_lang;

	if ( ! $key && is_search() ) $key = wppa_opt( 'wppa_search_linkpage' );
	
	switch ( $key ) {
		case '0':
		case '':	// normal permalink
			if ( $wppa['in_widget'] ) {
//				$pl = esc_url( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ); // 
				$pl = get_permalink(); // home_url();
				if ( $plain ) return $pl;
				if ( strpos( $pl, '?' ) ) $pl .= '&amp;';
				else $pl .= '?';
				}
			else {
				if ( $wppa['ajax'] ) {
					if ( wppa_get_get( 'page_id' ) ) $id = wppa_get_get( 'page_id' );
					elseif ( wppa_get_get( 'p' ) ) $id = wppa_get_get( 'p' );
					elseif ( wppa_get_get( 'fromp' ) ) $id = wppa_get_get( 'fromp' );
					else $id = '';
					$pl = get_permalink( intval( $id ) );
					if ( $plain ) return $pl;
					if ( strpos( $pl, '?' ) ) $pl .= '&amp;';
					else $pl .= '?';
				}
				else {
					$pl = get_permalink();
					if ( $plain ) return $pl;
					if ( strpos( $pl, '?' ) ) $pl .= '&amp;';
					else $pl .= '?';
				}
			}
			break;
		case 'js':	// normal permalink for js use
			if ( $wppa['in_widget'] ) {
				$pl = home_url();
				if ( $plain ) return $pl;
				if ( strpos($pl, '?' ) ) $pl .= '&';
				else $pl .= '?';
			}
			else {
				if ( $wppa['ajax'] ) {
					if ( wppa_get_get( 'page_id' ) ) $id = wppa_get_get( 'page_id' );
					elseif ( wppa_get_get( 'p' ) ) $id = wppa_get_get( 'p' );
					elseif ( wppa_get_get( 'wppa-fromp' ) ) $id = wppa_get_get( 'wppa-fromp' );
					else $id = '';
					$pl = get_permalink( intval( $id ) );
					if ( $plain ) return $pl;
					if ( strpos( $pl, '?' ) ) $pl .= '&';
					else $pl .= '?';
				}
				else {
					$pl = get_permalink();
					if ( $plain ) return $pl;
					if ( strpos( $pl, '?' ) ) $pl .= '&';
					else $pl .= '?';
				}
			}
			break;
		default:	// pagelink
			$pl = get_permalink($key);
			if ( $plain ) return $pl;
			if (strpos($pl, '?')) $pl .= '&amp;';
			else $pl .= '?';
			break;
	}

	if ( wppa_get_get( 'lang' ) ) {	// If lang in querystring: keep it
		if ( strpos( $pl, 'lang=' ) === false ) { 	// Not yet
			if ( $key == 'js' ) $pl .= 'lang='.$wppa_lang.'&';
			else $pl .= 'lang='.$wppa_lang.'&amp;';
		}
	}
	
	if ( $wppa['is_rootsearch'] ) {
		if ( $key == 'js' ) $pl .= 'rootsearch=1&';
		else $pl .= 'rootsearch=1&amp;';
	}
	
	if ($wppa['debug']) {
		if ( $key == 'js' ) $pl .= 'debug='.$wppa['debug'].'&';
		else $pl .= 'debug='.$wppa['debug'].'&amp;';
	}
	
	return $pl;
}

// Like get_permalink but for ajax use
function wppa_get_ajaxlink($key = '') {
global $wppa_lang;
global $wppa;

	if ( wppa_switch( 'wppa_ajax_non_admin' ) ) {
		$al = WPPA_URL.'/wppa-ajax-front.php?action=wppa&amp;wppa-action=render';
	}
	else {
		$al = admin_url( 'admin-ajax.php' ).'?action=wppa&amp;wppa-action=render';
	}
	
	// See if this call is from an ajax operation or...
	if ( $wppa['ajax'] ) {
		if ( wppa_get_get( 'size' ) ) $al .= '&amp;wppa-size=' . wppa_get_get( 'size' );
		if ( wppa_get_get( 'moccur' ) ) $al .= '&amp;wppa-moccur=' . wppa_get_get( 'moccur' );
		if ( is_numeric( $key ) && $key > '0' ) {
			$al .= '&amp;page_id='.$key;
		}
		else {
			if ( wppa_get_get( 'page_id' ) ) $al .= '&amp;page_id=' . wppa_get_get( 'page_id' );
		}
		if ( wppa_get_get( 'p' ) ) $al .= '&amp;p=' . wppa_get_get( 'p' );
		if ( wppa_get_get( 'fromp' ) ) $al .= '&amp;wppa-fromp=' . wppa_get_get( 'wppa-fromp' );
	}
	else {	// directly from a page or post
		$al .= '&amp;wppa-size='.wppa_get_container_width();
		$al .= '&amp;wppa-moccur='.$wppa['mocc'];
		if ( is_numeric($key) && $key > '0' ) {
			$al .= '&amp;page_id='.$key;
		}
		else {
			if ( wppa_get_get( 'p' ) ) $al .= '&amp;p=' . wppa_get_get( 'p' );
			if ( wppa_get_get( 'page_id' ) ) $al .= '&amp;page_id=' . wppa_get_get( 'page_id' );
		}
		$al .= '&amp;wppa-fromp='.get_the_ID();
	}
	
	if ( wppa_get_get( 'lang' ) ) {	// If lang in querystring: keep it
		if ( strpos($al, 'lang=') === false ) { 	// Not yet
			if ( $key == 'js' ) $al .= '&lang='.$wppa_lang;
			else $al .= '&amp;lang='.$wppa_lang;
		}
	}

	if ( $wppa['is_rootsearch'] ) {
		if ( $key == 'js' ) $al .= '&rootsearch=1';
		else $al .= '&amp;rootsearch=1';
	}

	if ( $wppa['debug'] ) {
		$al .= '&amp;debug=' . $wppa['debug'];
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
	
	if ( ! $occur ) $occur = '1';
	
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
function wppa_get_album_url($id, $pag = '') {
global $wppa;
	
	$occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
	$w = $wppa['in_widget'] ? 'w' : '';
	
	if ($id) {
		$link = wppa_get_permalink($pag).'wppa-album='.$id.'&amp;wppa-cover=0&amp;wppa-'.$w.'occur='.$occur;
	}
	else $link = '';
    return $link;
}

// get link to album by id or in loop ajax version
function wppa_get_album_url_ajax($id, $pag = '') {
global $wppa;
	
	$occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
	$w = $wppa['in_widget'] ? 'w' : '';
	
	if ($id) {
		$link = wppa_get_ajaxlink($pag).'wppa-album='.$id.'&amp;wppa-cover=0&amp;wppa-'.$w.'occur='.$occur;
	}
	else $link = '';
    return $link;
}

// get link to slideshow (in loop)
function wppa_get_slideshow_url($id, $page = '', $pid = '') {
global $wppa;
	
	if ($id) {
		$occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
		$w = $wppa['in_widget'] ? 'w' : '';
		$link = wppa_get_permalink($page).'wppa-album='.$id.'&amp;wppa-slide'.'&amp;wppa-'.$w.'occur='.$occur;	// slide=true changed in slide
		if ( $pid ) $link .= '&amp;wppa-photo='.$pid;
		if ( $wppa['is_upldr'] ) $link .= '&amp;wppa-upldr='.$wppa['is_upldr'];
		// can be extended for other special cases, see wppa_thumb_default() in wppa-functions.php
	}
	else {
		$link = '';
	}
	
	return $link;	
}

// get link to slideshow (in loop) ajax version
function wppa_get_slideshow_url_ajax($id, $page = '') {
global $wppa;
	
	if ($id) {
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
//	if ( ! isset($_ENV["SCRIPT_URI"]) ) return $uri;
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
		$possible_conflicts = array( 'wppa-page=' );

		foreach ( $args as $arg ) {
			if ( $first ) $newuri .= '?'; else $newuri .= '&';
			$first = false;
			$code = substr($arg, 0, 2);
			switch ( $code ) {
				case 'ab':
					$deltauri = 'wppa-album=';
					break;
				case 'pt':
					$deltauri = 'wppa-photo=';
					break;
				case 'sd':
					$deltauri = 'wppa-slide';
					break;
				case 'cv':
					$deltauri = 'wppa-cover=';
					break;
				case 'oc':
					$deltauri = 'wppa-occur=';
					break;
				case 'wo':
					$deltauri = 'wppa-woccur=';
					break;
				case 'pg':
					$deltauri = 'wppa-page=';
					break;
				case 'ss':
					$deltauri = 'wppa-searchstring=';
					break;
				case 'tt':
					$deltauri = 'wppa-topten=';
					break;
				case 'lt':
					$deltauri = 'wppa-lasten=';
					break;
				case 'ct':
					$deltauri = 'wppa-comten=';
					break;
				case 'ft':
					$deltauri = 'wppa-featen=';
					break;
				case 'ln':
					$deltauri = 'lang=';
					break;
				case 'si':
					$deltauri = 'wppa-single=';
					break;
				case 'tg':
					$deltauri = 'wppa-tag=';
					break;
				case 'po':
					$deltauri = 'wppa-photos-only=';
					break;
//				case 'rs':
//					$deltauri = 'wppa-randseed=';
//					break;
				case 'db':
					$deltauri = 'debug=';
					break;
				case 'rl':
					$deltauri = 'wppa-rel=';
					break;
				case 'rc':
					$deltauri = 'wppa-relcount=';
					break;
				case 'ul':
					$deltauri = 'wppa-upldr=';
					break;
				case 'ow':
					$deltauri = 'wppa-owner=';
					break;
				case 'rt':
					$deltauri = 'wppa-rootsearch=';
					break;
			}
			
//			if ( wppa_switch( 'wppa_use_short_qargs' ) ) {
//				if ( ! in_array( $deltauri, $possible_conflicts ) ) {
//					$deltauri = str_replace( 'wppa-', '', $deltauri );
//				}
				
//			}
			$newuri .= $deltauri;
			$newuri .= substr($arg, 2);
		}
	}

	$newuri = wppa_trim_wppa_( $newuri );
	return $newuri;
}

// Pretty links Encode
function wppa_convert_to_pretty( $xuri ) {
global $wppa_opt;

	// Make local copy
	$uri = $xuri;
	
	// Only when permalink structure is not default
	if ( ! get_option('permalink_structure') ) return $uri;
	
	// Any querystring?
	if ( strpos( $uri, '?' ) === false ) {
		return $uri;
	}
	
	// Re-order
	if ( strpos( $uri, '&amp;' ) !== false ) {
		$amps = true;
		$uri = str_replace( '&amp;', '&', $uri );
	}
	else {
		$amps = false;
	}
	$parts = explode( '?', $uri );
	$args = explode( '&', $parts[1] );
	$order = array( 'occur', 'woccur', 
					'searchstring', 
					'topten', 'lasten', 'comten', 'featen', 
//					'randseed', 
					'lang', 
					'single', 
					'tag', 
					'photos-only', 
					'rel', 
					'relcount', 
					'upldr', 
					'owner', 
					'rootsearch',
					'slide', 'cover', 'page',
					'album', 'photo', 'debug' );
	$uri = $parts[0] . '?';
	$first = true;
	foreach ( $order as $item ) {
		foreach ( array_keys($args) as $argidx ) {
			if ( strpos( $args[$argidx], $item ) === 0 || strpos( $args[$argidx], 'wppa-' . $item ) === 0 ) {
				if ( ! $first ) {
					$uri .= '&';
				}
				$uri .=  $args[$argidx];
				unset ( $args[$argidx] );
				$first = false;
			}
		}
	}
	foreach ( $args as $arg ) {	// append unprocessed items
		$uri .= '&' . $arg;
	}
	if ( $amps ) {
		$uri = str_replace( '&', '&amp;', $uri );
	} 

	// First filter for short query args
	$uri = wppa_trim_wppa_( $uri );
/*	if ( wppa_switch( 'wppa_use_short_qargs' ) ) {
		$uri = str_replace( '?wppa-', '?', $uri );
		$uri = str_replace( '&amp;wppa-', '&amp;', $uri );
		$uri = str_replace( '&wppa-', '&', $uri );
	}
*/

	// Now filter for album names in urls
	if ( wppa_switch( 'wppa_use_album_names_in_urls' ) ) {
		$apos = strpos( $uri, 'album=' );
		if ( $apos !== false ) {
			$start = $apos + '6';
			$end = strpos( $uri, '&', $start );
		}
		$before = substr( $uri, 0, $start );
		if ( $end ) {
			$albnum = substr( $uri, $start, $end - $start );
			if ( wppa_is_int( $albnum ) && $albnum > '0' ) {	// Can convert single positive integer album ids only
				$after	= substr( $uri, $end );
				$albnam = stripslashes( wppa_get_album_item( $albnum, 'name' ) );
				$albnam = wppa_encode_uri_component( $albnam );
				$uri = $before . $albnam . $after;
			}
		}
		else {
			$albnum = substr( $uri, $start );
			if ( wppa_is_int( $albnum ) && $albnum > '0' ) {	// Can convert single positive integer album ids only
				$albnam = stripslashes( wppa_get_album_item( $albnum, 'name' ) );
				$albnam = wppa_encode_uri_component( $albnam );
				$uri = $before . $albnam;
			}
		}
	}
	
	// Now filter for photo names in urls
	if ( wppa_switch( 'wppa_use_photo_names_in_urls' ) ) {
		$start 	= 0;
		$end 	= 0;
		$ppos 	= strpos( $uri, 'photo=' );
		if ( $ppos !== false ) {
			$start = $ppos + '6';
			$end = strpos( $uri, '&', $start );
		}
		$before = substr( $uri, 0, $start );
		if ( $end ) {
			$id = substr( $uri, $start, $end - $start );
			if ( wppa_is_int( $id ) ) {	// Can convert single integer photo ids only
				$after = substr( $uri, $end );
				$pname = stripslashes( wppa_get_photo_item( $id, 'name' ) );
				$pname = wppa_encode_uri_component( $pname );
				$uri = $before . $pname . $after;
			}
		}
		else {
			$id = substr( $uri, $start );
			if ( wppa_is_int( $id ) ) {	// Can convert single integer photo ids only
				$pname = stripslashes( wppa_get_photo_item( $id, 'name' ) );
				$pname = wppa_encode_uri_component( $pname );
				$uri = $before . $pname;
			}
		}
	}
	
	// Now urlencode for funny chars
	$uri = str_replace( array( ' ', '[', ']' ), array( '%20', '%5B', '%5D' ), $uri );
	
	// Now the actual conversion to pretty links
	if ( ! wppa_switch('wppa_use_pretty_links') ) return $uri;
	if ( ! get_option('permalink_structure') ) return $uri;
	
	// Leaving the next line out gives 404 on pretty links under certain circumstances. 
	// Can not reproduce and also do not understand why, and do not remember why i have put it in.
	//
	// nov 5 2014: changed add_action to test on redirection form init to pplugins_loaded. 
	// also skipped if ( ! isset($_ENV["SCRIPT_URI"]) ) return; in redirect test. See wpp-non-admin.php. Seems to work now
//	if ( ! isset($_ENV["SCRIPT_URI"]) ) return $uri;

	// Do some preprocessing
	$uri = str_replace('&amp;', '&', $uri);
	$uri = str_replace('?wppa-', '?', $uri);
	$uri = str_replace('&wppa-', '&', $uri);

	// Test if querystring exists
	$qpos = stripos($uri, '?');
	if ( ! $qpos ) return $uri;

	// Make sure we end without '/'
	$newuri = trim(substr($uri, 0, $qpos), '/');
	$newuri .= '/wppaspec';
	
	// explode querystring
	$args = explode('&', substr($uri, $qpos+1));
	$support = array('album', 'photo', 'slide', 'cover', 'occur', 'woccur', 'page', 'searchstring', 'topten', 'lasten', 'comten', 'featen', 'lang', 'single', 'tag', 'photos-only', 'debug', 'rel', 'relcount', 'upldr', 'owner', 'rootsearch' );
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
//					case 'randseed':
//						$newuri .= 'rs';
//						break;
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
					case 'owner':
						$newuri .= 'ow';
						break;
					case 'rootsearch':
						$newuri .= 'rt';
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
function wppa_moderate_links( $type, $id, $comid = '' ) {
global $thumb;

	wppa_cache_thumb( $id );

	if ( current_user_can('wppa_moderate') || ( current_user_can('wppa_comments') && $type == 'comment' ) ) {
		switch ( $type ) {
			case 'thumb':
				$app = __a('App');
				$mod = __a('Mod');
				$del = __a('Del');

				$result = '
				<div style="clear:both;"></div>
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
				<div style="clear:both;"></div>
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
		if ( $type == 'comment' || $thumb['status'] != 'scheduled' ) {
			$result = '<div style="clear:both; color:red">'.__a('Awaiting moderation').'</div>';
		}
		else {
			$result = '<div style="clear:both; color:red">'.sprintf( __a( 'Scheduled for %s' ), wppa_format_scheduledtm( $thumb['scheduledtm'] ) ).'</div>';
		}
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
	
	return str_replace( ' ', '%20', wppa_trim_wppa_( $url ) );
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
	if ( wppa_get_get( 'cover' ) ) $ic = wppa_get_get( 'cover' );
	else {
		if ( $wppa['is_cover'] == '1' ) $ic = '1'; else $ic = '0';
	}
	$extra_url = 'wppa-cover='.$ic;
	// album
//	if ( $wppa['start_album'] ) $alb = $wppa['start_album'];
//	elseif (wppa_get_get('album')) $alb = wppa_get_get('album');
	$occur = $wppa['in_widget'] ? wppa_get_get('woccur') : wppa_get_get('occur');
	$ref_occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
	if (($occur == $ref_occur || $wppa['ajax'] ) && wppa_get_get('album')) {
			$alb = wppa_get_get('album');
	}
	elseif ( $wppa['start_album'] ) $alb = $wppa['start_album'];
	else $alb = '0';
//	if ( $alb ) 
		$extra_url .= '&amp;wppa-album='.$alb;
	
	// photo
	if ( wppa_get_get( 'photo' ) ) {
		$extra_url .= '&amp;wppa-photo=' . wppa_get_get( 'photo' );
	}
	// occur
	if ( ! $wppa['ajax'] ) {
		$occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
		$w = $wppa['in_widget'] ? 'w' : '';
		$extra_url .= '&amp;wppa-' . $w . 'occur=' . $occur;
	}
	else {
		if ( wppa_get_get( 'occur' ) ) {
			$occur = wppa_get_get( 'occur' );
			$extra_url .= '&amp;wppa-occur=' . strval( intval( $occur ) );
		}
		elseif ( wppa_get_get( 'woccur' ) ) {
			$occur = wppa_get_get( 'woccur' );
			$extra_url .= '&amp;wppa-woccur=' . strval( intval( $occur ) );
		}
		else {
			$extra_url .= '&amp;wppa-occur=' . $wppa['occur'];	// Should never get here?
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
	
	// Add the random seed
//	$extra_url .= '&amp;wppa-randseed='.$wppa['randseed'];
	
	// Almost ready
	$link_url .= $extra_url;
	$ajax_url .= $extra_url;
	
	// Compress
	$link_url = wppa_trim_wppa_( $link_url );
	$ajax_url = wppa_trim_wppa_( $ajax_url );

	// Adjust display range
	$from = 1;
	$to = $npages;
	if ( $npages > $wppa_opt['wppa_pagelinks_max'] ) {
		$delta = floor( $wppa_opt['wppa_pagelinks_max'] / 2 );
		$from = $curpage - $delta;
		$to = $curpage + $delta;
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
	$wppa['out'] .= wppa_nltab('+').'<div id="prevnext-a-'.$wppa['mocc'].'" class="wppa-nav-text wppa-box wppa-nav" style="clear:both; text-align:center; '.__wcs('wppa-box').__wcs('wppa-nav').'" >';
		$vis = $curpage == '1' ? 'visibility: hidden;' : '';
		$wppa['out'] .= wppa_nltab('+').'<div id="prev-page" style="float:left; text-align:left; '.$vis.'">';
			$wppa['out'] .= wppa_nltab().'<span class="wppa-arrow" style="'.__wcs('wppa-arrow').'cursor: default;">&laquo;&nbsp;</span>';
			if ( wppa_switch('wppa_allow_ajax') ) $wppa['out'] .= wppa_nltab().'<a style="cursor:pointer;" id="p-p" onclick="wppaDoAjaxRender('.$wppa['mocc'].', \''.$ajax_url.'&amp;wppa-page='.($curpage - 1).'\', \''.wppa_convert_to_pretty($link_url.'&amp;wppa-page='.($curpage - 1)).'\')" >'.__a('Prev.&nbsp;page').'</a>';
			else $wppa['out'] .= wppa_nltab().'<a style="cursor:pointer;" id="p-p" href="'.$link_url.'&amp;wppa-page='.($curpage - 1).'" >'.__a('Prev.&nbsp;page', 'wppa_theme').'</a>';
		$wppa['out'] .= wppa_nltab('-').'</div><!-- #prev-page -->';
		$vis = $curpage == $npages ? 'visibility: hidden;' : '';
		$wppa['out'] .= wppa_nltab('+').'<div id="next-page" style="float:right; text-align:right; '.$vis.'">';
			if ( wppa_switch('wppa_allow_ajax') ) $wppa['out'] .= wppa_nltab().'<a style="cursor:pointer;" id="n-p" onclick="wppaDoAjaxRender('.$wppa['mocc'].', \''.$ajax_url.'&amp;wppa-page='.($curpage + 1).'\', \''.wppa_convert_to_pretty($link_url.'&amp;wppa-page='.($curpage + 1)).'\')" >'.__a('Next&nbsp;page').'</a>';
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
					if ( wppa_switch('wppa_allow_ajax') ) $wppa['out'] .= wppa_nltab().'<a style="cursor:pointer;" onclick="wppaDoAjaxRender('.$wppa['mocc'].', \''.$ajax_url.'&amp;wppa-page='.$i.'\', \''.wppa_convert_to_pretty($link_url.'&amp;wppa-page='.$i).'\')">&nbsp;'.$i.'&nbsp;</a>';
					else $wppa['out'] .= wppa_nltab().'<a style="cursor:pointer;" href="'.$link_url.'&amp;wppa-page='.$i.'">&nbsp;'.$i.'&nbsp;</a>';
				$wppa['out'] .= wppa_nltab('-').'</div>';	
			}
		}
		if ($to < $npages) {
			$wppa['out'] .= ('&nbsp;.&nbsp;.&nbsp;.');
		}
	$wppa['out'] .= wppa_nltab('-').'</div><!-- #prevnext-a-'.$wppa['mocc'].' -->';
}

function wppa_album_download_link( $albumid ) {
global $wppa;
global $wppa_opt;

	if ( ! wppa_switch( 'wppa_allow_download_album' ) ) return;	// Not enabled
	
	$wppa['out'] .= '<div style="clear:both;" ></div>';
	$wppa['out'] .= '<a onclick="wppaAjaxDownloadAlbum('.$wppa['mocc'].', '.$albumid.' );" style="cursor:pointer;" title="'.__a('Download').'">'.__a('Download album').'</a>';
	$wppa['out'] .= '<img id="dwnspin-'.$wppa['mocc'].'-'.$albumid.'" src="'.wppa_get_imgdir().'wpspin.gif" style="margin-left:6px; display:none;" alt="spin" />';
}

function wppa_get_imglnk_a( $wich, $id, $lnk = '', $tit = '', $onc = '', $noalb = false, $album = '' ) {
global $wppa;
global $wppa_opt;
global $wpdb;

	// make sure the photo data ia available
	$thumb = wppa_cache_thumb( $id );
	if ( ! $thumb ) return false;
	
	// Is it a video?
	$is_video = wppa_is_video( $id, true );
	
	// Photo Specific Overrule?
	if ( ( $wich == 'sphoto'     && wppa_switch( 'wppa_sphoto_overrule' ) ) ||
		 ( $wich == 'mphoto'     && wppa_switch( 'wppa_mphoto_overrule' ) ) ||
		 ( $wich == 'thumb'      && wppa_switch( 'wppa_thumb_overrule' ) ) ||
		 ( $wich == 'topten'     && wppa_switch( 'wppa_topten_overrule' ) ) ||
		 ( $wich == 'featen'	 && wppa_switch( 'wppa_featen_overrule' ) ) ||
		 ( $wich == 'lasten'     && wppa_switch( 'wppa_lasten_overrule' ) ) ||
		 ( $wich == 'sswidget'   && wppa_switch( 'wppa_sswidget_overrule' ) ) ||
		 ( $wich == 'potdwidget' && wppa_switch( 'wppa_potdwidget_overrule' ) ) ||
		 ( $wich == 'coverimg'   && wppa_switch( 'wppa_coverimg_overrule' ) ) ||
		 ( $wich == 'comten'	 && wppa_switch( 'wppa_comment_overrule' ) ) ||
		 ( $wich == 'slideshow'  && wppa_switch( 'wppa_slideshow_overrule' ) ) ||
		 ( $wich == 'tnwidget' 	 && wppa_switch( 'wppa_thumbnail_widget_overrule' ) ) ) {
		 
		// Look for a photo specific link
		if ( $thumb ) {
			// If it is there...
			if ( $thumb['linkurl'] ) {
				// Use it. It superceeds other settings
				$result['url'] = esc_attr( $thumb['linkurl'] );
				$result['title'] = esc_attr( wppa_qtrans( stripslashes( $thumb['linktitle'] ) ) );
				$result['is_url'] = true;
				$result['is_lightbox'] = false;
				$result['onclick'] = '';
				$result['target'] = $thumb['linktarget'];
				return $result;
			}
		}
	}
	
	$result['target'] = '_self';
	$result['title'] = '';
	$result['onclick'] = '';
	switch ( $wich ) {
		case 'sphoto':
			$type = $wppa_opt['wppa_sphoto_linktype'];
			$page = $wppa_opt['wppa_sphoto_linkpage'];
			if ( $page == '0' ) $page = '-1';
			if ( wppa_switch( 'wppa_sphoto_blank' ) ) $result['target'] = '_blank';
			break;
		case 'mphoto':
			$type = $wppa_opt['wppa_mphoto_linktype'];
			$page = $wppa_opt['wppa_mphoto_linkpage'];
			if ( $page == '0' ) $page = '-1';
			if ( wppa_switch( 'wppa_mphoto_blank' ) ) $result['target'] = '_blank';
			break;
		case 'thumb':
			$type = $wppa_opt['wppa_thumb_linktype'];
			$page = $wppa_opt['wppa_thumb_linkpage'];
			if ( wppa_switch( 'wppa_thumb_blank' ) ) $result['target'] = '_blank';
			break;
		case 'topten':
			$type = $wppa_opt['wppa_topten_widget_linktype'];
			$page = $wppa_opt['wppa_topten_widget_linkpage'];
			if ( $page == '0' ) $page = '-1';
			if ( wppa_switch( 'wppa_topten_blank' ) ) $result['target'] = '_blank';
			break;
		case 'featen':
			$type = $wppa_opt['wppa_featen_widget_linktype'];
			$page = $wppa_opt['wppa_featen_widget_linkpage'];
			if ( $page == '0' ) $page = '-1';
			if ( wppa_switch( 'wppa_featen_blank' ) ) $result['target'] = '_blank';
			break;
		case 'lasten':
			$type = $wppa_opt['wppa_lasten_widget_linktype'];
			$page = $wppa_opt['wppa_lasten_widget_linkpage'];
			if ( $page == '0' ) $page = '-1';
			if ( wppa_switch( 'wppa_lasten_blank' ) ) $result['target'] = '_blank';
			break;
		case 'comten':
			$type = $wppa_opt['wppa_comment_widget_linktype'];
			$page = $wppa_opt['wppa_comment_widget_linkpage'];
			if ( $page == '0' ) $page = '-1';
			if ( wppa_switch( 'wppa_comment_blank' ) ) $result['target'] = '_blank';
			break;
		case 'sswidget':
			$type = $wppa_opt['wppa_slideonly_widget_linktype'];
			$page = $wppa_opt['wppa_slideonly_widget_linkpage'];
			if ( $page == '0' ) $page = '-1';
			if ( wppa_switch( 'wppa_sswidget_blank' ) ) $result['target'] = '_blank';
			$result['url'] = '';
			if ( $type == 'lightbox' || $type == 'lightboxsingle' || $type == 'file' ) { 
				$result['title'] = wppa_zoom_in( $id );
				$result['target'] = '';
				return $result;
			}
			break;
		case 'potdwidget':
			$type = $wppa_opt['wppa_widget_linktype'];
			$page = $wppa_opt['wppa_widget_linkpage'];
			if ( $page == '0' ) $page = '-1';
			if ( wppa_switch( 'wppa_potd_blank' ) ) $result['target'] = '_blank';
			break;
		case 'coverimg':
			$type = $wppa_opt['wppa_coverimg_linktype'];
			$page = $wppa_opt['wppa_coverimg_linkpage'];
			if ( $page == '0' ) $page = '-1';
			if ( wppa_switch( 'wppa_coverimg_blank' ) ) $result['target'] = '_blank';
			if ( $type == 'slideshowstartatimage' ) {
				$result['url'] = wppa_get_slideshow_url( $album, $page, $id);
				$result['is_url'] = true;
				$result['is_lightbox'] = false;
				return $result;
			}
			break;
		case 'tnwidget':
			$type = $wppa_opt['wppa_thumbnail_widget_linktype'];
			$page = $wppa_opt['wppa_thumbnail_widget_linkpage'];
			if ( $page == '0' ) $page = '-1';
			if ( wppa_switch( 'wppa_thumbnail_widget_blank' ) ) $result['target'] = '_blank';
			break;
		case 'slideshow':
			$type = $wppa_opt['wppa_slideshow_linktype'];	//'';
			$page = $wppa_opt['wppa_slideshow_linkpage'];
			$result['url'] = '';
			if ( $type == 'lightbox' || $type == 'lightboxsingle' || $type == 'file' ) { 
				$result['title'] = wppa_zoom_in( $id );
				$result['target'] = '';
				return $result;
			}
			if ( $type == 'none' ) return;
			// Continue for 'single' 
			break;
		case 'albwidget':
			$type = $wppa_opt['wppa_album_widget_linktype'];
			$page = $wppa_opt['wppa_album_widget_linkpage'];
			if ( $page == '0' ) $page = '-1';
			if ( wppa_switch( 'wppa_album_widget_blank' ) ) $result['target'] = '_blank';
			break;
		default:
			return false;
			break;
	}
	
	if ( ! $album ) {
		$album = $wppa['start_album'];
	}
	if ( $album == '' && ! $wppa['is_upldr'] ) {	/**/
		$album = wppa_get_album_id_by_photo_id( $id );
	}
	if ( is_numeric( $album ) ) {
		$album_name = wppa_get_album_name( $album );
	}
	else $album_name = '';
	
	if ( ! $album ) $album = '0';
	if ( $wich == 'comten' ) $album = '0';

	if ( $wppa['is_tag'] ) $album = '0';
//	if ( $wppa['is_upldr'] ) $album = '0';	// probeersel upldr parent
	
	if ( $id ) {
		$photo_name = wppa_get_photo_name( $id );
	}
	else $photo_name = '';
	
	$photo_name_js = esc_js( $photo_name );
	$photo_name = esc_attr( $photo_name );
	
	if ( $id ) {
		$photo_desc = esc_attr( wppa_get_photo_desc( $id ) );
	}
	else $photo_desc = '';

	$title = __( $photo_name );
	
	$result['onclick'] = '';	// Init
	switch ( $type ) {
		case 'none':		// No link at all
			return false;
			break;
		case 'file':		// The plain file
			if ( $is_video ) {
				$siz = array( wppa_get_videox( $id ), wppa_get_videoy( $id ) );
				$result['url'] = wppa_get_photo_url( $id, '', $siz['0'], $siz['1'] );
				$result['url'] = str_replace( 'xxx', $is_video['0'], $result['url'] );
			}
			else {
				$siz = array( wppa_get_photox( $id ), wppa_get_photoy( $id ) );
				$result['url'] = wppa_get_photo_url( $id, '', $siz['0'], $siz['1'] );
			}
			$result['title'] = $title; 
			$result['is_url'] = true;
			$result['is_lightbox'] = false;
			return $result;
			break;
		case 'lightbox':
		case 'lightboxsingle':
			if ( $is_video ) {
				$siz = array( wppa_get_videox( $id ), wppa_get_videoy( $id ) );
				$result['url'] = wppa_get_photo_url( $id, '', $siz['0'], $siz['1'] );
				$result['url'] = str_replace( 'xxx', $is_video['0'], $result['url'] );
			}
			else {
				if ( wppa_switch( 'wppa_lb_hres' ) ) {
					$result['url'] = wppa_get_hires_url( $id );
				}
				else {
					$siz = array( wppa_get_photox( $id ), wppa_get_photoy( $id ) );
					$result['url'] = wppa_get_photo_url( $id, '', $siz['0'], $siz['1'] );
				}
			}
			$result['title'] = $title; 
			$result['is_url'] = false;
			$result['is_lightbox'] = true;
			return $result;
		case 'widget':		// Defined at widget activation
			$result['url'] = $wppa['in_widget_linkurl'];
			$result['title'] = esc_attr( $wppa['in_widget_linktitle'] );
			$result['is_url'] = true;
			$result['is_lightbox'] = false;
			return $result;
			break;
		case 'album':		// The albums thumbnails
		case 'content':		// For album widget
			switch ( $page ) {
				case '-1':
					return false;
					break;
				case '0':
					if ( $noalb ) {
						$result['url'] = wppa_get_permalink() . 'wppa-album=0&amp;wppa-cover=0';
						$result['title'] = ''; // $album_name;
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					else {
						$result['url'] = wppa_get_permalink() . 'wppa-album=' . $album . '&amp;wppa-cover=0';
						$result['title'] = $album_name;
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					break;
				default:
					if ( $noalb ) {
						$result['url'] = wppa_get_permalink( $page ) . 'wppa-album=0&amp;wppa-cover=0';
						$result['title'] = ''; //$album_name;//'a++';
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					else {
						$result['url'] = wppa_get_permalink( $page ) . 'wppa-album=' . $album . '&amp;wppa-cover=0';
						$result['title'] = $album_name;//'a++';
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					break;
			}
			break;
		case 'thumbalbum':
			$album = $thumb['album'];
			$album_name = wppa_get_album_name( $album );
			switch ( $page ) {
				case '-1':
					return false;
					break;
				case '0':
					$result['url'] = wppa_get_permalink() . 'wppa-album=' . $album . '&amp;wppa-cover=0';
					$result['title'] = $album_name;
					$result['is_url'] = true;
					$result['is_lightbox'] = false;
					break;
				default:
					$result['url'] = wppa_get_permalink( $page ) . 'wppa-album=' . $album . '&amp;wppa-cover=0';
					$result['title'] = $album_name;//'a++';
					$result['is_url'] = true;
					$result['is_lightbox'] = false;
					break;
			}
			break;
		case 'photo':		// This means: The fullsize photo in a slideshow
		case 'slphoto':		// This means: The single photo in the style of a slideshow
			if ( $type == 'slphoto' ) {
				$si = '&amp;wppa-single=1';
			}
			else {
				$si = '';
			}
			switch ( $page ) {
				case '-1':
					return false;
					break;
				case '0':
					if ( $noalb ) {
						$result['url'] = wppa_get_permalink() . 'wppa-album=0&amp;wppa-photo=' . $id . $si;
						$result['title'] = $title;
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					else {
						$result['url'] = wppa_get_permalink() . 'wppa-album=' . $album . '&amp;wppa-photo=' . $id . $si;
						$result['title'] = $title;
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					break;
				default:
					if ( $noalb ) {
						$result['url'] = wppa_get_permalink( $page ) . 'wppa-album=0&amp;wppa-photo=' . $id . $si;
						$result['title'] = $title;
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					else {
						$result['url'] = wppa_get_permalink( $page ) . 'wppa-album=' . $album . '&amp;wppa-photo=' . $id . $si;
						$result['title'] = $title;
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					break;
			}
			break;
		case 'single':
			switch ( $page ) {
				case '-1':
					return false;
					break;
				case '0':
					$result['url'] = wppa_get_permalink() . 'wppa-photo=' . $id;
					$result['title'] = $title;
					$result['is_url'] = true;
					$result['is_lightbox'] = false;
					break;
				default:
					$result['url'] = wppa_get_permalink( $page ) . 'wppa-photo=' . $id;
					$result['title'] = $title;
					$result['is_url'] = true;
					$result['is_lightbox'] = false;
					break;
			}
			break;
		case 'same':
			$result['url'] = $lnk;
			$result['title'] = $tit;
			$result['is_url'] = true;
			$result['is_lightbox'] = false;
			$result['onclick'] = $onc;
			return $result;
			break;
		case 'fullpopup':
			if ( $is_video ) { 		// A video can not be printed or downloaded
				$result['url'] = esc_attr( 'alert( "' . esc_js( __a( 'A video can not be printed or downloaded' ) ) . '" )' );
			}
			else {
				$wid = wppa_get_photox( $id );
				$hig = wppa_get_photoy( $id );
				/*
				$imgsize = getimagesize( wppa_get_photo_path( $id ) );
				if ( $imgsize ) {
					$wid = $imgsize['0'];
					$hig = $imgsize['1'];
				}
				else {
					$wid = '0';
					$hig = '0';
				}
				*/
				$url = wppa_get_photo_url( $id, '', $wid, $hig );
				
				$result['url'] = esc_attr( 'wppaFullPopUp( ' . $wppa['mocc'] . ', ' . $id . ', "' . $url . '", ' . $wid . ', ' . $hig . ' )' );
			}
			$result['title'] = $title;
			$result['is_url'] = false;
			$result['is_lightbox'] = false;
			return $result;
			break;
		case 'custom':
			if ( $wich == 'potdwidget' ) {
				$result['url'] = $wppa_opt['wppa_widget_linkurl'];
				$result['title'] = $wppa_opt['wppa_widget_linktitle'];
				$result['is_url'] = true;
				$result['is_lightbox'] = false;
				return $result;
			}
			break;
		case 'slide':	// for album widget
			$result['url'] = wppa_get_permalink( $wppa_opt['wppa_album_widget_linkpage'] ) . 'wppa-album=' . $album . '&amp;slide';
			$result['title'] = '';
			$result['is_url'] = true;
			$result['is_lightbox'] = false;
			break;
		case 'autopage':
			if ( ! wppa_switch( 'wppa_auto_page' ) ) {
				wppa_dbg_msg( 'Auto page has been switched off, but there are still links to it (' . $wich . ')', 'red', 'force' );
				$result['url'] = '';
			}
			else {
				$result['url'] = wppa_get_permalink( wppa_get_the_auto_page( $id ) );
			}
			$result['title'] = '';
			$result['is_url'] = true;
			$result['is_lightbox'] = false;
			break;
		case 'plainpage':
			$result['url'] = get_permalink( $page );
			$result['title'] = $wpdb->get_var( $wpdb->prepare( "SELECT `post_title` FROM `" . $wpdb->prefix . "posts` WHERE `ID` = %s", $page ) );
			$result['is_url'] = true;
			$result['is_lightbox'] = false;
			return $result;
			break;
		default:
			wppa_dbg_msg( 'Error, wrong type: ' . $type . ' in wppa_get_imglink_a', 'red' );
			return false;
			break;
	}
	
	if ( $wppa['src'] && ! $wppa['is_related'] && ! $wppa['in_widget'] ) { 
		$result['url'] .= '&amp;wppa-searchstring=' . urlencode( $wppa['searchstring'] );
	}

	if ( $wich == 'topten' ) {
		$result['url'] .= '&amp;wppa-topten=' . $wppa_opt['wppa_topten_count'];
	}
	elseif ( $wppa['is_topten'] ) {
		$result['url'] .= '&amp;wppa-topten=' . $wppa['topten_count'];
	}
	
	if ( $wich == 'lasten' ) {
		$result['url'] .= '&amp;wppa-lasten=' . $wppa_opt['wppa_lasten_count'];
	}
	elseif ( $wppa['is_lasten'] ) {
		$result['url'] .= '&amp;wppa-lasten=' . $wppa['lasten_count'];
	}

	if ( $wich == 'comten' ) {
		$result['url'] .= '&amp;wppa-comten=' . $wppa_opt['wppa_comten_count'];
	}
	elseif ( $wppa['is_comten'] ) {
		$result['url'] .= '&amp;wppa-comten=' . $wppa['comten_count'];
	}

	if ( $wich == 'featen' ) {
		$result['url'] .= '&amp;wppa-featen=' . $wppa_opt['wppa_featen_count'];// . '&amp;wppa-randseed=' . $wppa['randseed'];
	}
	elseif ( $wppa['is_featen'] ) {
		$result['url'] .= '&amp;wppa-featen=' . $wppa['featen_count'];// . '&amp;wppa-randseed=' . $wppa['randseed'];
	}
	
	if ( $wppa['is_related'] ) {
		$result['url'] .= '&amp;wppa-rel=' . $wppa['is_related'] . '&amp;wppa-relcount=' . $wppa['related_count'];
	}
	elseif ( $wppa['is_tag'] ) {
		$result['url']  .= '&amp;wppa-tag=' . $wppa['is_tag'];
	}
	
	if ( $wppa['is_upldr'] ) {
		$result['url'] .= '&amp;wppa-upldr=' . $wppa['is_upldr'];
	}
	
	if ( $page != '0' ) {	// on a different page
		$occur = '1';
		$w = '';
	}
	else {				// on the same page, post or widget
		$occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
		$w = $wppa['in_widget'] ? 'w' : '';
	}
	$result['url'] .= '&amp;wppa-' . $w . 'occur=' . $occur;
	$result['url'] = wppa_convert_to_pretty( $result['url'] );
	
	if ( $result['title'] == '' ) $result['title'] = $tit;	// If still nothing, try arg
	
	return $result;
}

// Remove wppa- from query string arguments
function wppa_trim_wppa_( $link ) {
static $trimmable;

	$trimmable = array('album', 'photo', 'slide', 'cover', 'occur', 'woccur', 'searchstring', 'topten', 'lasten', 'comten', 'featen', 'single', 'photos-only', 'debug', 'relcount', 'upldr', 'owner', 'rootsearch' );

	$result = $link;
	
	if ( wppa_switch( 'wppa_use_short_qargs' ) ) {
		foreach ( $trimmable as $item ) {
			$result = str_replace( 'wppa-'.$item, $item, $result );
		}
	}
	
	return $result;
}
