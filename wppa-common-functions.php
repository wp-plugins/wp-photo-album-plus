<?php
/* wppa-common-functions.php
*
* Functions used in admin and in themes
* version 4.0.7
*
*/
global $wppa_api_version;
$wppa_api_version = '4-0-7-000';
// Initialize globals and option settings
function wppa_initialize_runtime($force = false) {
global $wppa;
global $wppa_opt;
global $wppa_revno;
global $wppa_api_version;
global $blog_id;

	if ($force) {
		$wppa = false; // destroy existing arrays
		$wppa_opt = false;
	}

	if (!is_array($wppa)) {
		$wppa = array (
			'debug' => false,
			'revno' => $wppa_revno,				// set in wppa.php
			'api_version' => $wppa_api_version,	// set in wppa_functions.php
			'fullsize' => '',
			'enlarge' => false,
			'occur' => '0',
			'master_occur' => '0',
			'widget_occur' => '0',
			'in_widget' => false,
			'is_cover' => '0',
			'is_slide' => '0',
			'is_slideonly' => '0',
			'film_on' => '0',
			'browse_on' => '0',
			'single_photo' => '',
			'is_mphoto' => '0',
			'start_album' => '',
			'align' => '',
			'src' => false,
			'portrait_only' => false,
			'in_widget_linkurl' => '',
			'in_widget_linktitle' => '',
			'in_widget_timeout' => '0',
			'ss_widget_valign' => '',
			'album_count' => '0',
			'thumb_count' => '0',
			'out' => '',
			'auto_colwidth' => false,
			'permalink' => '',
			'randseed' => time() % '4711',
			'rendering_enabled' => false,
			'tabcount' => '0',
			'comment_id' => '',
			'comment_photo' => '0',
			'comment_user' => '',
			'comment_email' => '',
			'comment_text' => '',
			'no_default' => false
		);
		if (isset($_POST['wppa-searchstring'])) $wppa['src'] = true;
		if (isset($_GET['wppa_src'])) $wppa['src'] = true;
	}
	
	if (!is_array($wppa_opt)) {
		$wppa_opt = array ( 
			'wppa_multisite' => '',
			'wppa_revision' => '',
			'wppa_fullsize' => '',
			'wppa_colwidth' => '',
			'wppa_maxheight' => '',
			'wppa_enlarge' => '',
			'wppa_resize_on_upload' => '',
			'wppa_fullvalign' => '',
			'wppa_fullhalign' => '',
			'wppa_min_thumbs' => '',
			'wppa_thumbtype' => '',
			'wppa_valign' => '',
			'wppa_thumbsize' => '',
			'wppa_tf_width' => '',
			'wppa_tf_height' => '',
			'wppa_tn_margin' => '',
			'wppa_smallsize' => '',
			'wppa_show_bread' => '',
			'wppa_show_home' => '',
			'wppa_bc_separator' => '',
			'wppa_use_thumb_opacity' => '',
			'wppa_thumb_opacity' => '',
			'wppa_use_thumb_popup' => '',
			'wppa_use_cover_opacity' => '',
			'wppa_cover_opacity' => '',
			'wppa_animation_speed' => '',
			'wppa_slideshow_timeout' => '',
			'wppa_bgcolor_even' => '',
			'wppa_bgcolor_alt' => '',
			'wppa_bgcolor_nav' => '',
			'wppa_bgcolor_img' => '',
			'wppa_bgcolor_namedesc' => '',
			'wppa_bgcolor_com' => '',
			'wppa_bcolor_even' => '',
			'wppa_bcolor_alt' => '',
			'wppa_bcolor_nav' => '',
			'wppa_bcolor_img' => '',
			'wppa_bcolor_namedesc' => '',
			'wppa_bcolor_com' => '',
			'wppa_bwidth' => '',
			'wppa_bradius' => '',
			'wppa_fontfamily_thumb' => '',
			'wppa_fontsize_thumb' => '',
			'wppa_fontcolor_thumb' => '',
			'wppa_fontfamily_box' => '',
			'wppa_fontsize_box' => '',
			'wppa_fontcolor_box' => '',
			'wppa_fontfamily_nav' => '',
			'wppa_fontsize_nav' => '',
			'wppa_fontcolor_nav' => '',
			'wppa_fontfamily_title' => '',
			'wppa_fontsize_title' => '',
			'wppa_fontcolor_title' => '',
			'wppa_fontfamily_fulldesc' => '',
			'wppa_fontsize_fulldesc' => '',
			'wppa_fontcolor_fulldesc' => '',
			'wppa_fontfamily_fulltitle' => '',
			'wppa_fontsize_fulltitle' => '',
			'wppa_fontcolor_fulltitle' => '',
			'wppa_arrow_color' => '',
			'wppa_widget_width' => '',
			'wppa_max_cover_width' => '',
			'wppa_text_frame_height' => '',
			'wppa_film_show_glue' => '',
			'wppa_album_page_size' => '',
			'wppa_thumb_page_size' => '',
			'wppa_thumb_auto' => '',
			'wppa_coverphoto_pos' => '',
			'wppa_thumbphoto_left' => '',
			'wppa_enable_slideshow' => '',
			'wppa_thumb_text_name' => '',
			'wppa_thumb_text_desc' => '',
			'wppa_thumb_text_rating' => '',
			'wppa_show_startstop_navigation' => '',
			'wppa_show_browse_navigation' => '',
			'wppa_show_full_desc' => '',
			'wppa_show_full_name' => '',
			'wppa_show_comments' => '',
			'wppa_show_cover_text' => '',
			'wppa_start_slide' => '',
			'wppa_hide_slideshow' => '',
			'wppa_filmstrip' => '',
			'wppa_bc_url' => '',
			'wppa_bc_txt' => '',
			'wppa_topten_count' => '',
			'wppa_topten_size' => '',
			'wppa_excl_sep' => '',
			'wppa_rating_on' => '',
			'wppa_rating_login' => '',
			'wppa_rating_change' => '',
			'wppa_rating_multi' => '',
			'wppa_comment_login' => '',
			'wppa_list_albums_by' => '',
			'wppa_list_albums_desc' => '',
			'wppa_list_photos_by' => '',
			'wppa_list_photos_desc' => '',
			'wppa_html' => '',
			'wppa_thumb_linkpage' => '',
			'wppa_thumb_linktype' => '',
			'wppa_mphoto_linkpage' => '',
			'wppa_mphoto_linktype' => '',
			'wppa_fadein_after_fadeout' => '',
			'wppa_widget_linkpage' => '',
			'wppa_widget_linktype' => '',
			'wppa_slideonly_widget_linkpage' => '',
			'wppa_slideonly_widget_linktype' => '',
			'wppa_topten_widget_linkpage' => '',
			'wppa_topten_widget_linktype' => '',
			'wppa_coverimg_linktype' => '',
			'wppa_coverimg_linkpage' => '',
			'wppa_mphoto_overrule'		=> '',
			'wppa_thumb_overrule'		=> '',
			'wppa_topten_overrule'		=> '',
			'wppa_sswidget_overrule'	=> '',
			'wppa_potdwidget_overrule'	=> '',
			'wppa_coverimg_overrule'	=> '',
			'wppa_search_linkpage' => '',
			'wppa_chmod' => '',
			'wppa_setup' => '',
			'wppa_allow_debug' => '',
			'wppa_potd_align' => '',
			'wppa_comadmin_show' => '',
			'wppa_comadmin_order' => '',
			'wppa_popupsize' => '',
			'wppa_slide_order' => '',
			'wppa_show_bbb' => '',
			'wppa_show_slideshowbrowselink' => '',
			'wppa_fullimage_border_width' => '',
			'wppa_bgcolor_fullimg' => '',
			'wppa_bcolor_fullimg' => '',
			'wppa_max_photo_newtime' => '',
			'wppa_max_album_newtime' => '',
			'wppa_load_skin' => '',
			'wppa_skinfile' => '',
			'wppa_use_lightbox' => ''
		);
		array_walk($wppa_opt, 'wppa_set_options');
	}
	wppa_load_language();
	
	if (isset($_GET['debug']) && $wppa_opt['wppa_allow_debug']) {
		$key = $_GET['debug'] ? $_GET['debug'] : E_ALL;
		$wppa['debug'] = $key;
	}
	
	
/*
/wp-content/blogs.dir/1/wppa-depot (For backups, etc)
/wp-content/blogs.dir/2/wppa (For photo uploads, thumbnails...) 

*/
	if ( ! defined( 'WPPA_UPLOAD') ) {
		if ( get_option('wppa_multisite', 'no') == 'yes' ) {	// DO NOT change this in $wppa_opt['wppa_multisite'] as it will not work
			define( 'WPPA_UPLOAD', 'wp-content/blogs.dir/'.$blog_id);
			define( 'WPPA_UPLOAD_PATH', ABSPATH.WPPA_UPLOAD.'/wppa');
			define( 'WPPA_UPLOAD_URL', get_bloginfo('wpurl').'/'.WPPA_UPLOAD.'/wppa');
			define( 'WPPA_DEPOT', 'wp-content/blogs.dir/'.$blog_id.'/wppa-depot' );			
			define( 'WPPA_DEPOT_PATH', ABSPATH.WPPA_DEPOT );					
			define( 'WPPA_DEPOT_URL', get_bloginfo('wpurl').'/'.WPPA_DEPOT );	
		}
		else {
			define( 'WPPA_UPLOAD', 'wp-content/uploads');
			define( 'WPPA_UPLOAD_PATH', ABSPATH.WPPA_UPLOAD.'/wppa' );
			define( 'WPPA_UPLOAD_URL', get_bloginfo('wpurl').'/'.WPPA_UPLOAD.'/wppa' );
			$user = is_user_logged_in() ? '/'.wppa_get_user() : '';
			define( 'WPPA_DEPOT', 'wp-content/wppa-depot'.$user );
			define( 'WPPA_DEPOT_PATH', ABSPATH.WPPA_DEPOT );
			define( 'WPPA_DEPOT_URL', get_bloginfo('wpurl').'/'.WPPA_DEPOT );
		}
	}
}

function wppa_set_options($value, $key) {
global $wppa_opt;

	if (is_admin()) {	// admin needs the raw data
		$wppa_opt[$key] = get_option($key);
	}
	else {
		$temp = get_option($key);
		switch ($temp) {
			case 'no':
				$wppa_opt[$key] = false;
				break;
			case 'yes':
				$wppa_opt[$key] = true;
				break;
			default:
				$wppa_opt[$key] = $temp;
			}	
	}
}

function wppa_load_language() {
global $wppa_locale;
global $q_config;
global $wppa;

	if ($wppa_locale) return; // Done already
	
	// See if qTranslate present and actve, if so, get locale there
	if (wppa_qtrans_enabled()) {	
		if (isset($q_config['language'])) $lang = $q_config['language'];
		if (isset($q_config['locale'][$lang])) $wppa_locale = $q_config['locale'][$lang];
	}
	else {		// Get locale from wp-config
		$wppa_locale = get_locale();
	}
	if ($wppa_locale) {
		$domain = is_admin() ? 'wppa' : 'wppa_theme';
		$mofile = WPPA_PATH.'/langs/'.$domain.'-'.$wppa_locale.'.mo';
		$bret = load_textdomain($domain, $mofile);
	}
	
	if ($wppa['debug']) {	// Diagnostic
		$wppa['out'] .= '<span style="color:blue"><small>Lang='.$lang.', Locale='.$wppa_locale.', Mofile='.$mofile;
		if (is_file($mofile)) $wppa['out'] .= ' exists.'; else $wppa['out'] .= ' does not exist.';
		if (!$bret) $bret = '0';
		$wppa['out'] .= ', loaded='.$bret.'.</small></span><br/>';	
	}
}

function wppa_phpinfo($key = -1) {
	if (is_int($key)) $k = $key; else $k = intval($key);
	if (!$k) $k = -1;
	echo("\n".'<div style="width:600px; margin: 24px auto;">'."\n");
	phpinfo($k);
	echo("\n".'</div>'."\n");
	echo("\n".'<style type="text/css">');
	echo("\n\ta:link {color: #990000; text-decoration: none; background-color: transparent;}");
	echo("\n</style>");
}

// get the url to the plugins image directory
function wppa_get_imgdir() {
	$result = WPPA_URL.'/images/';
	return $result;
}

// get album order
function wppa_get_album_order() {
global $wppa;

    $result = '';
    $order = get_option('wppa_list_albums_by');
    switch ($order)
    {
    case '1':
        $result = 'ORDER BY a_order';
        break;
    case '2':
        $result = 'ORDER BY name';
        break;  
    case '3':
        $result = 'ORDER BY RAND('.$wppa['randseed'].')';
        break;
    default:
        $result = 'ORDER BY id';
    }
    if (get_option('wppa_list_albums_desc') == 'yes') $result .= ' DESC';
    return $result;
}

// get photo order
function wppa_get_photo_order($id) {
global $wpdb;
global $wppa;
    
	if ($id == 0) $order=0;
	else $order = $wpdb->get_var("SELECT p_order_by FROM " . WPPA_ALBUMS . " WHERE id=$id");
    if ($order == '0') $order = get_option('wppa_list_photos_by');
    switch ($order)
    {
    case '1':
        $result = 'ORDER BY p_order';
        break;
    case '2':
        $result = 'ORDER BY name';
        break;
    case '3':
        $result = 'ORDER BY RAND('.$wppa['randseed'].')';
        break;
	case '4':
		$result = 'ORDER BY mean_rating';
		break;
    default:
        $result = 'ORDER BY id';
    }
    if (get_option('wppa_list_photos_desc') == 'yes') $result .= ' DESC';
    return $result;
}

function wppa_get_rating_count_by_id($id = '') {
global $wpdb;

	if (!is_numeric($id)) return '';
	$query = 'SELECT * FROM '.WPPA_RATING.' WHERE photo = '.$id;
	$ratings = $wpdb->get_results($query, 'ARRAY_A');
	if ($ratings) return count($ratings);
	else return '0';
}

function wppa_get_rating_by_id($id = '', $opt = '') {
global $wpdb;

	$result = '';
	if (is_numeric($id)) {
		$rating = $wpdb->get_var("SELECT mean_rating FROM ".WPPA_PHOTOS." WHERE id=$id");
		if ($rating) {
			if ($opt == 'nolabel') $result = round($rating * 1000) / 1000;
			else $result = sprintf(__a('Rating: %s', 'wppa_theme'), round($rating * 1000) / 1000);
		}
	}
	return $result;
}

// See if an album is another albums ancestor
function wppa_is_ancestor($anc, $xchild) {

	$child = $xchild;
	if (is_numeric($anc) && is_numeric($child)) {
		$parent = wppa_get_parentalbumid($child);
		while ($parent > '0') {
			if ($anc == $parent) return true;
			$child = $parent;
			$parent = wppa_get_parentalbumid($child);
		}
	}
	return false;
}

// Get the albums parent
function wppa_get_parentalbumid($alb) {
global $wpdb;
    
	$query = $wpdb->prepare('SELECT `a_parent` FROM `' . WPPA_ALBUMS . '` WHERE `id` = %s', $alb);
	$result = $wpdb->get_var($query);
	
    if (!is_numeric($result)) {
		$result = 0;
	}
    return $result;
}

// get user
function wppa_get_user() {
global $current_user;

	if (is_user_logged_in()) {
		get_currentuserinfo();
		$user = $current_user->user_login;
		return $user;
	}
	else {
		if (is_admin()) {
			wpa_die('It is not allowed to run admin pages while you are not logged in.');
		}
		else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}
}

function wppa_get_album_id($name = '') {
global $wpdb;

	if ($name == '') return '';
    $name = $wpdb->escape($name);
    $id = $wpdb->get_var("SELECT id FROM " . WPPA_ALBUMS . " WHERE name='" . $name . "'");
    if ($id) {
		return $id;
	}
	else {
		return '';
	}
}

function wppa_get_album_name($id = '', $raw = '') {
global $wpdb;
    
    if ($id == '0') $name = is_admin() ? __('--- none ---', 'wppa') : __a('--- none ---', 'wppa_theme');
    elseif ($id == '-1') $name = is_admin() ? __('--- separate ---', 'wppa') : __a('--- separate ---', 'wppa_theme');
    else {
        if ($id == '') if (isset($_GET['album'])) $id = $_GET['album'];
        $id = $wpdb->escape($id);	
        if (is_numeric($id)) $name = $wpdb->get_var("SELECT name FROM " . WPPA_ALBUMS . " WHERE id=$id");
		else $name = '';
    }
	if ($name) {
		if ($raw != 'raw') $name = stripslashes($name);
	}
	else {
		$name = '';
	}
	if (!is_admin()) $name = wppa_qtrans($name);
	return $name;
}

function wppa_is_wider($x, $y) {

	$ratioref = get_option('wppa_fullsize') / get_option('wppa_maxheight');
	$ratio = $x / $y;
	return ($ratio > $ratioref);
}

// qtrans hook to see if qtrans is installed
function wppa_qtrans_enabled() {
	return (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage'));
}

// qtrans hook for multi language support of content
function wppa_qtrans($output, $lang = '') {
	if ($lang == '') {
		if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
			$output = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($output);
		}
	} else {
		if (function_exists('qtrans_use')) {
			$output = qtrans_use($lang, $output, false);
		}
	}
	return $output;
}

function wppa_dbg_msg($txt='', $color = 'blue') {
global $wppa;
	if ( $wppa['debug'] ) echo('<span style="color:'.$color.';"><small>[WPPA+ dbg msg: '.$txt.']<br /></small></span>');
}

function wppa_dbg_url($link, $js = '') {
global $wppa;
	$result = $link;
	if ($wppa['debug']) {
		if (strpos($result, '?')) {
			if ($js == 'js') $result .= '&';
			else $result .= '&amp;';
		}
		else $result .= '?';
		$result .= 'debug='.$wppa['debug'];
	}
	return $result;
}

function wppa_get_time_since($oldtime) {

	if (is_admin()) {	// admin version
		$newtime = time();
		$diff = $newtime - $oldtime;
		if ($diff < 60) {
			if ($diff == 1) return __('1 second', 'wppa');
			else return $diff.' '.__('seconds', 'wppa');
		}
		$diff = floor($diff / 60);
		if ($diff < 60) {
			if ($diff == 1) return __('1 minute', 'wppa');
			else return $diff.' '.__('minutes', 'wppa');
		}
		$diff = floor($diff / 60);
		if ($diff < 24) {
			if ($diff == 1) return __('1 hour', 'wppa');
			else return $diff.' '.__('hours', 'wppa');
		}
		$diff = floor($diff / 24);
		if ($diff < 7) {
			if ($diff == 1) return __('1 day', 'wppa');
			else return $diff.' '.__('days', 'wppa');
		}
		elseif ($diff < 31) {
			$t = floor($diff / 7);
			if ($t == 1) return __('1 week', 'wppa');
			else return $t.' '.__('weeks', 'wppa');
		}
		$diff = floor($diff / 30.4375);
		if ($diff < 12) {
			if ($diff == 1) return __('1 month', 'wppa');
			else return $diff.' '.__('months', 'wppa');
		}
		$diff = floor($diff / 12);
		if ($diff == 1) return __('1 year', 'wppa');
		else return $diff.' '.__('years', 'wppa');
	}
	else {	// theme version
		$newtime = time();
		$diff = $newtime - $oldtime;
		if ($diff < 60) {
			if ($diff == 1) return __a('1 second', 'wppa_theme');
			else return $diff.' '.__a('seconds', 'wppa_theme');
		}
		$diff = floor($diff / 60);
		if ($diff < 60) {
			if ($diff == 1) return __a('1 minute', 'wppa_theme');
			else return $diff.' '.__a('minutes', 'wppa_theme');
		}
		$diff = floor($diff / 60);
		if ($diff < 24) {
			if ($diff == 1) return __a('1 hour', 'wppa_theme');
			else return $diff.' '.__a('hours', 'wppa_theme');
		}
		$diff = floor($diff / 24);
		if ($diff < 7) {
			if ($diff == 1) return __a('1 day', 'wppa_theme');
			else return $diff.' '.__a('days', 'wppa_theme');
		}
		elseif ($diff < 31) {
			$t = floor($diff / 7);
			if ($t == 1) return __a('1 week', 'wppa_theme');
			else return $t.' '.__a('weeks', 'wppa_theme');
		}
		$diff = floor($diff / 30.4375);
		if ($diff < 12) {
			if ($diff == 1) return __a('1 month', 'wppa_theme');
			else return $diff.' '.__a('months', 'wppa_theme');
		}
		$diff = floor($diff / 12);
		if ($diff == 1) return __a('1 year', 'wppa_theme');
		else return $diff.' '.__a('years', 'wppa_theme');
	}
}