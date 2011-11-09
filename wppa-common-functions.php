<?php
/* wppa-common-functions.php
*
* Functions used in admin and in themes
* version 4.2.4
*
*/
global $wppa_api_version;
$wppa_api_version = '4-2-4-000';
// Initialize globals and option settings
function wppa_initialize_runtime($force = false) {
global $wppa;
global $wppa_opt;
global $wppa_revno;
global $wppa_api_version;
global $blog_id;

	if ($force) {
		$wppa = false; 					// destroy existing arrays
		$wppa_opt = false;
	}

	if (!is_array($wppa)) {
		$wppa = array (
			'debug' 					=> false,
			'revno' 					=> $wppa_revno,				// set in wppa.php
			'api_version' 				=> $wppa_api_version,		// set in wppa_functions.php
			'fullsize' 					=> '',
			'enlarge' 					=> false,
			'occur' 					=> '0',
			'master_occur' 				=> '0',
			'widget_occur' 				=> '0',
			'in_widget' 				=> false,
			'is_cover' 					=> '0',
			'is_slide' 					=> '0',
			'is_slideonly' 				=> '0',
			'film_on' 					=> '0',
			'browse_on' 				=> '0',
			'name_on' 					=> '0',
			'desc_on' 					=> '0',
			'numbar_on' 				=> '0',
			'single_photo' 				=> '',
			'is_mphoto' 				=> '0',
			'start_album' 				=> '',
			'align' 					=> '',
			'src' 						=> false,
			'portrait_only' 			=> false,
			'in_widget_linkurl' 		=> '',
			'in_widget_linktitle' 		=> '',
			'in_widget_timeout' 		=> '0',
			'ss_widget_valign' 			=> '',
			'album_count' 				=> '0',
			'thumb_count' 				=> '0',
			'out' 						=> '',
			'auto_colwidth' 			=> false,
			'permalink' 				=> '',
			'randseed' 					=> time() % '4711',
			'rendering_enabled' 		=> false,
			'tabcount' 					=> '0',
			'comment_id' 				=> '',
			'comment_photo' 			=> '0',
			'comment_user' 				=> '',
			'comment_email' 			=> '',
			'comment_text' 				=> '',
			'no_default' 				=> false,
			'in_widget_frame_height' 	=> '',
			'user_uploaded'				=> false,
			'current_album'				=> '0',
			'searchstring'				=> wppa_get_searchstring(),
			'searchresults'				=> '',
			'any'						=> false

		);

		if (isset($_POST['wppa-searchstring'])) $wppa['src'] = true;
		if (isset($_GET['s'])) $wppa['src'] = true;

	}
	
	if (!is_array($wppa_opt)) {
		$wppa_opt = array ( 
			'wppa_revision' => '',
			'wppa_fullsize' => '',
			'wppa_colwidth' => '',
			'wppa_maxheight' => '',
			'wppa_enlarge' => '',
			'wppa_resize_on_upload' => '',
			'wppa_resize_to' => '',
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
			'wppa_bgcolor_cus' => '',
			'wppa_bgcolor_numbar' => '',
			'wppa_bgcolor_numbar_active' => '',
			'wppa_bcolor_even' => '',
			'wppa_bcolor_alt' => '',
			'wppa_bcolor_nav' => '',
			'wppa_bcolor_img' => '',
			'wppa_bcolor_namedesc' => '',
			'wppa_bcolor_com' => '',
			'wppa_bcolor_cus' => '',
			'wppa_bcolor_numbar' => '',
			'wppa_bcolor_numbar_active' => '',
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
			'wppa_fontfamily_numbar' 	=> '',
			'wppa_fontsize_numbar' 		=> '',
			'wppa_fontcolor_numbar' 	=> '',
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
			'wppa_widget_linkurl' => '',
			'wppa_widget_linktitle' => '',
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
			'wppa_slideshow_overrule'	=> '',
			'wppa_search_linkpage' => '',
			'wppa_chmod' => '',
			'wppa_setup' => '',
			'wppa_rerate' => '',
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
			'wppa_use_lightbox' => '',
			'wppa_lightbox_bordersize' => '',
			'wppa_lightbox_animationspeed' => '',
			'wppa_lightbox_backgroundcolor' => '',
			'wppa_lightbox_bordercolor' => '',
			'wppa_lightbox_overlaycolor' => '',
			'wppa_lightbox_overlayopacity' => '',
			'wppa_swap_namedesc' => '',
			'wppa_fontfamily_lightbox' => '',
			'wppa_fontsize_lightbox' => '',
			'wppa_fontcolor_lightbox' => '',
			'wppa_filter_priority' => '',
			'wppa_custom_on' => '',
			'wppa_custom_content' => '',
			'wppa_apply_newphoto_desc' => '',
			'wppa_newphoto_description' => '',
			'wppa_comments_desc' => '',
			'wppa_user_upload_on' => '',
			'wppa_show_slideshownumbar'  => '',
			'wppa_autoclean' => '',
			'wppa_numbar_max' => '',
			'wppa_watermark_on'				=> '',
			'wppa_watermark_user'			=> '',
			'wppa_watermark_file'			=> '',
			'wppa_watermark_pos'			=> '',
			'wppa_watermark_upload'			=> '',
			'wppa_comment_widget_linkpage'	=> '',
			'wppa_comment_widget_linktype'	=> '',
			'wppa_comment_count'			=> '',
			'wppa_comment_size'				=> '',
			'wppa_comment_overrule'			=> ''



		);
		array_walk($wppa_opt, 'wppa_set_options');
	}
	wppa_load_language();
	
	if (isset($_GET['debug']) && $wppa_opt['wppa_allow_debug']) {
		$key = $_GET['debug'] ? $_GET['debug'] : E_ALL;
		$wppa['debug'] = $key;
	}
	
	if ( ! defined( 'WPPA_UPLOAD') ) {
		if ( is_multisite() ) {
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
	case '5':
		$result = 'ORDER BY timestamp';
		break;
    default:
        $result = 'ORDER BY id';
    }
    if (get_option('wppa_list_albums_desc') == 'yes') $result .= ' DESC';
    return $result;
}

// get photo order
function wppa_get_photo_order($id, $no_random = false) {
global $wpdb;
global $wppa;
    
	if ($id == 0) $order=0;
	else $order = $wpdb->get_var( $wpdb->prepare( "SELECT p_order_by FROM " . WPPA_ALBUMS . " WHERE id=%s", $id) );
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
		if ($no_random) $result = 'ORDER BY name';
        else $result = 'ORDER BY RAND('.$wppa['randseed'].')';
        break;
	case '4':
		$result = 'ORDER BY mean_rating';
		break;
	case '5':
		$result = 'ORDER BY timestamp';
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
	$query = $wpdb->prepare( 'SELECT * FROM '.WPPA_RATING.' WHERE photo = %s', $id);
	$ratings = $wpdb->get_results($query, 'ARRAY_A');
	if ($ratings) return count($ratings);
	else return '0';
}

function wppa_get_rating_by_id($id = '', $opt = '') {
global $wpdb;

	$result = '';
	if (is_numeric($id)) {
		$rating = $wpdb->get_var( $wpdb->prepare( "SELECT mean_rating FROM ".WPPA_PHOTOS." WHERE id=%s", $id ) );
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
    $id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM " . WPPA_ALBUMS . " WHERE name = %s", $name ) );
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
        if (is_numeric($id)) $name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM " . WPPA_ALBUMS . " WHERE id=%s", $id ) );
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

// Check if an image is more landscape that the width/height ratio set in Table I item 2 and 3
function wppa_is_wider($x, $y, $refx = '', $refy = '') {
	if ( $refx == '' ) {
		$ratioref = get_option('wppa_fullsize') / get_option('wppa_maxheight');
	}
	else {
		$ratioref = $refx/$refy;
	}
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

function wppa_dbg_msg($txt='', $color = 'blue', $force = false) {
global $wppa;
	if ( $wppa['debug'] || $force ) echo('<span style="color:'.$color.';"><small>[WPPA+ dbg msg: '.$txt.']<br /></small></span>');
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

function wppa_nextkey($table) {
// Creating a keyvalue of an auto increment primary key incidently returns the value of MAXINT
// and thereby making it impossible to add a next record.
// This routine will find a free keyvalue larger than any key used, ignoring the fact that the MAXINT key may be used.
global $wpdb;

	$name = 'wppa_'.$table.'_lastkey';
	$lastkey = get_option($name, 'nil');
	
	if ( $lastkey == 'nil' ) {	// Init option
		$lastkey = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM ".$table." WHERE id < '9223372036854775806' ORDER BY id DESC LIMIT 1" ) );
		if ( ! is_numeric($lastkey) ) $lastkey = '0';
		add_option( $name, $lastkey, '', 'no');
	}
	wppa_dbg_msg('Lastkey in '.$table.' = '.$lastkey);
	
	$result = $lastkey + '1';
	while ( ! wppa_is_id_free($table, $result) ) {
		$result++;
	}
	update_option($name, $result);
	return $result;
}

function wppa_is_id_free($type, $id) {
global $wpdb;
	if (!is_numeric($id)) return false;
	if ($id == '0') return false;
	
	$table = '';
	if ($type == 'album') $table = WPPA_ALBUMS;
	elseif ($type == 'photo') $table = WPPA_PHOTOS;
	else $table = $type;	// $type may be the tablename itsself
	
	if ($table == '') {
		echo('Unexpected error in wppa_is_id_free()');
		return false;
	}
	
	$exists = $wpdb->get_row($wpdb->prepare( 'SELECT * FROM '.$table.' WHERE id = %s', $id ), 'ARRAY_A');
	if ($exists) return false;
	return true;
}

// See if an album or any album is accessable for the current user
function wppa_have_access($alb) {
global $wpdb;
global $current_user;

	if ( !$alb ) return false;
	
	// See if there is any album accessable
	if ($alb == 'any') {
	
		// Administrator has always access OR If all albums are public
		if (current_user_can('administrator') || get_option('wppa_owner_only', 'no') == 'no') {
			$albs = $wpdb->get_results($wpdb->prepare( 'SELECT id FROM '.WPPA_ALBUMS ) );
			if ($albs) return true;
			else return false;	// No albums in system
		}
		
		// Any --- public --- albums?
		$albs = $wpdb->get_results($wpdb->prepare( 'SELECT id FROM '.WPPA_ALBUMS.' WHERE owner = "--- public ---"' ) );
		if ($albs) return true;
		
		// Any albums owned by this user?
		get_currentuserinfo();
		$user = $current_user->user_login;
		$albs = $wpdb->get_results($wpdb->prepare( 'SELECT id FROM '.WPPA_ALBUMS.' WHERE owner = "%s"', $user) );
		if ($albs) return true;
		else return false;	// No albums for user accessable
		
	}
	
	// See for given album data array or album number
	else {
	
		// Administrator has always access
		if (current_user_can('administrator')) return true;
		
		// If all albums are public
		if (get_option('wppa_owner_only', 'no') == 'no') return true;
		
		// Find the owner
		$owner = '';
		if (is_array($alb)) {
			$owner = $alb['owner'];
		}
		elseif (is_numeric($alb)) {
			$owner = $wpdb->get_var($wpdb->prepare( 'SELECT owner FROM '.WPPA_ALBUMS.' WHERE id = %s', $alb ) );
		}
		
		// -- public --- ?
		if ( $owner == '--- public ---' ) return true;
		
		// Find the user
		get_currentuserinfo();
		
		if ( $current_user->user_login == $owner ) return true;
	}
	return false;
}

function wppa_make_the_photo_files($file, $image_id, $ext) {
global $wppa_opt;
				
	wppa_dbg_msg('make_the_photo_files called with file='.$file.' image_id='.$image_id.' ext='.$ext);
	$img_size = getimagesize($file);
	if ($img_size) {
		$newimage = WPPA_UPLOAD_PATH . '/' . $image_id . '.' . $ext;
		wppa_dbg_msg('newimage='.$newimage);
		
		if (get_option('wppa_resize_on_upload', 'no') == 'yes') {
			require_once('wppa-class-resize.php');
			// Picture sizes
			$picx = $img_size[0];
			$picy = $img_size[1];
			// Reference suzes
			if ( $wppa_opt['wppa_resize_to'] == '0') {	// from fullsize
				$refx = $wppa_opt['wppa_fullsize'];
				$refy = $wppa_opt['wppa_maxheight'];
			}
			else {										// from selection
				$screen = explode('x', $wppa_opt['wppa_resize_to']);
				$refx = $screen[0];
				$refy = $screen[1];
			}
			// Too landscape?
			if ( $picx/$picy > $refx/$refy ) {					// focus on width
//			if (wppa_is_wider($picx, $picy, $refx, $refy)) {	// focus on width
				$dir = 'W';
				$siz = $refx; //get_option('wppa_fullsize', '640');
				$s = $img_size[0];
			}
			else {												// focus on height
				$dir = 'H';
				$siz = $refy; //get_option('wppa_maxheight', get_option('wppa_fullsize', '640'));
				$s = $img_size[1];
			}

			if ($s > $siz) {	
				$objResize = new wppa_ImageResize($file, $newimage, $dir, $siz);
				$objResize->destroyImage($objResize->resOriginalImage);
				$objResize->destroyImage($objResize->resResizedImage);
			}
			else {
				copy($file, $newimage);
			}
		}
		else {
			copy($file, $newimage);
		}
		
		// File successfully created ?
		if ( is_file ($newimage) ) {	
			// Create thumbnail...
			$thumbsize = wppa_get_minisize();
			wppa_create_thumbnail($newimage, $thumbsize, '' );
			// and add watermark (optionally) to fullsize image only
			wppa_add_watermark($newimage);
		} 
		else {
			if (is_admin()) wppa_error_message(__('ERROR: Resized or copied image could not be created.', 'wppa'));
			else wppa_err_alert(__('ERROR: Resized or copied image could not be created.', 'wppa_theme'));
			return false;
		}
		// Show progression
		if (is_admin()) echo('.');
		return true;
	}
	else {
		if (is_admin()) wppa_error_message(sprintf(__('ERROR: File %s is not a valid picture file.', 'wppa'), $file));
		else wppa_err_alert(sprintf(__('ERROR: File %s is not a valid picture file.', 'wppa_theme'), $file));
		return false;
	}
}

function wppa_get_minisize() {
	$result = '100';
	
	$tmp = get_option('wppa_thumbsize', 'nil');
	if (is_numeric($tmp) && $tmp > $result) $result = $tmp;
	$tmp = get_option('wppa_smallsize', 'nil');
	if (is_numeric($tmp) && $tmp > $result) $result = $tmp;
	$tmp = get_option('wppa_popupsize', 'nil');
	if (is_numeric($tmp) && $tmp > $result) $result = $tmp;
	
	$result = ceil($result / 25) * 25;
	return $result;
}

function wppa_create_thumbnail( $file, $max_side, $effect = '') {
	if (file_exists($file)) {
		$img_size = getimagesize( $file );
		$dir = $img_size[0] > $img_size[1] ? 'W' : 'H';
		$thumb = 'thumbs/' . basename( $file );
		$thumbpath = str_replace( basename( $file ), $thumb, $file );

		require_once('wppa-class-resize.php');		
		$objResize = new wppa_ImageResize($file, $thumbpath, $dir, $max_side, false);
		$objResize->destroyImage($objResize->resOriginalImage);
		$objResize->destroyImage($objResize->resResizedImage);
	}
	else {
		return false;
	}
	return true;
}

function wppa_get_searchstring() {
global $wppa;

	$src = '';
	
	if (isset($_POST['wppa-searchstring'])) {
		$src = $_POST['wppa-searchstring'];
	}
	elseif (isset($_GET['s'])) {	// wp search
		$src = $_GET['s'];
	}

	return $src;
}

function wppa_add_watermark($file) {
global $wppa_opt;

	// Init
	if ( get_option('wppa_watermark_on') != 'yes' ) return;	// Watermarks off
	$user = wppa_get_user();
	
	// Find the watermark file and location
	$waterfile = WPPA_UPLOAD_PATH . '/watermarks/' . $wppa_opt['wppa_watermark_file'];	// default
	$waterpos = $wppa_opt['wppa_watermark_pos'];										// default

	if ( get_option('wppa_watermark_user') == 'yes' ) {									// user overrule?
		if ( isset($_POST['wppa-watermark-file'] ) ) {
			$waterfile = WPPA_UPLOAD_PATH . '/watermarks/' . $_POST['wppa-watermark-file'];
			update_option('wppa_watermark_file_' . $user, $_POST['wppa-watermark-file']);
		}
		elseif ( get_option('wppa_watermark_file_' . $user, 'nil') != 'nil' ) {
			$waterfile = WPPA_UPLOAD_PATH . '/watermarks/' . get_option('wppa_watermark_file_' . $user);
		}
		if ( isset($_POST['wppa-watermark-pos'] ) ) {
			$waterpos = $_POST['wppa-watermark-pos'];
			update_option('wppa_watermark_pos_' . $user, $_POST['wppa-watermark-pos']);
		}
		elseif ( get_option('wppa_watermark_pos_' . $user, 'nil') != 'nil' ) {
			$waterpos = get_option('wppa_watermark_pos_' . $user);
		}
	}

	if ( basename($waterfile) == '--- none ---' ) return;	// No watermark this time
	// Open the watermark file
	$watersize = @getimagesize($waterfile);
	if ( !is_array($watersize) ) return;	// Not a valid picture file
	$waterimage = imagecreatefrompng($waterfile);
	if ( empty( $waterimage ) or ( !$waterimage ) ) {
		wppa_dbg_msg('Watermark file '.$waterfile.' not found or corrupt');
		return;			// No image
	}
	imagealphablending($waterimage, false);
	imagesavealpha($waterimage, true);

		
	// Open the photo file
	$photosize = getimagesize($file);
	if ( !is_array($photosize) ) {
		return;	// Not a valid photo
	}
	switch ($photosize[2]) {
		case 1: $tempimage = imagecreatefromgif($file);
			$photoimage = imagecreatetruecolor($photosize[0], $photosize[1]);
			imagecopy($photoimage, $tempimage, 0, 0, 0, 0, $photosize[0], $photosize[1]);
			break;
		case 2: $photoimage = imagecreatefromjpeg($file);
			break;
		case 3: $photoimage = imagecreatefrompng($file);
			break;
	}
	if ( empty( $photoimage ) or ( !$photoimage ) ) return; 			// No image

	$ps_x = $photosize[0];
	$ps_y = $photosize[1];
	$ws_x = $watersize[0];
	$ws_y = $watersize[1];
	$src_x = 0;
	$src_y = 0;
	if ( $ws_x > $ps_x ) {
		$src_x = ($ws_x - $ps_x) / 2;
		$ws_x = $ps_x;
	}		
	if ( $ws_y > $ps_y ) {
		$src_y = ($ws_y - $ps_y) / 2;
		$ws_y = $ps_y;
	}
	
	$loy = substr( $waterpos, 0, 3);
	switch($loy) {
		case 'top': $dest_y = 0;
			break;
		case 'cen': $dest_y = ( $ps_y - $ws_y ) / 2;
			break;
		case 'bot': $dest_y = $ps_y - $ws_y;
			break;
		default: $dest_y = 0; 	// should never get here
	}
	$lox = substr( $waterpos, 3);
	switch($lox) {
		case 'lft': $dest_x = 0;
			break;
		case 'cen': $dest_x = ( $ps_x - $ws_x ) / 2;
			break;
		case 'rht': $dest_x = $ps_x - $ws_x;
			break;
		default: $dest_x = 0; 	// should never get here
	}

	wppa_imagecopymerge_alpha( $photoimage , $waterimage , $dest_x, $dest_y, $src_x, $src_y, $ws_x, $ws_y, 20 );

	// Save the result
	switch ($photosize[2]) {
		case 1: imagegif($photoimage, $file);
			break;
		case 2: imagejpeg($photoimage, $file, 100);
			break;
		case 3: imagepng($photoimage, $file, 0);
			break;
	}

	// Cleanup
	imagedestroy($photoimage);
	imagedestroy($waterimage);

}


/**
 * PNG ALPHA CHANNEL SUPPORT for imagecopymerge();
 * This is a function like imagecopymerge but it handle alpha channel well!!!
 **/

// A fix to get a function like imagecopymerge WITH ALPHA SUPPORT
// Main script by aiden dot mail at freemail dot hu
// Transformed to imagecopymerge_alpha() by rodrigo dot polo at gmail dot com
function wppa_imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
    if(!isset($pct)){
        return false;
    }
    $pct /= 100;
    // Get image width and height
    $w = imagesx( $src_im );
    $h = imagesy( $src_im );
    // Turn alpha blending off
    imagealphablending( $src_im, false );
    // Find the most opaque pixel in the image (the one with the smallest alpha value)
    $minalpha = 127;
    for( $x = 0; $x < $w; $x++ )
    for( $y = 0; $y < $h; $y++ ){
        $alpha = ( imagecolorat( $src_im, $x, $y ) >> 24 ) & 0xFF;
        if( $alpha < $minalpha ){
            $minalpha = $alpha;
        }
    }
    //loop through image pixels and modify alpha for each
    for( $x = 0; $x < $w; $x++ ){
        for( $y = 0; $y < $h; $y++ ){
            //get current alpha value (represents the TANSPARENCY!)
            $colorxy = imagecolorat( $src_im, $x, $y );
            $alpha = ( $colorxy >> 24 ) & 0xFF;
            //calculate new alpha
            if( $minalpha !== 127 ){
                $alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha );
            } else {
                $alpha += 127 * $pct;
            }
            //get the color index with new alpha
            $alphacolorxy = imagecolorallocatealpha( $src_im, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
            //set pixel with the new color + opacity
            if( !imagesetpixel( $src_im, $x, $y, $alphacolorxy ) ){
                return false;
            }
        }
    }
    // The image copy
    imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
}

function wppa_watermark_file_select($default = false) {
global $wppa_opt;

	// Init
	$result = '';
	$user = wppa_get_user();
	
	// See what's in there
	$paths = WPPA_UPLOAD_PATH . '/watermarks/*.png';
	$files = glob($paths);
	
	// Find current selection
	$select = $wppa_opt['wppa_watermark_file'];	// default
	if ( !$default && get_option('wppa_watermark_user') == 'yes' && get_option('wppa_watermark_file_' . $user, 'nil') !== 'nil' ) {
		$select = get_option('wppa_watermark_file_' . $user);
	}
	
	// Produce the html
	$result .= '<option value="--- none ---">'.__('--- none ---', 'wppa').'</option>';
	if ( $files ) foreach ( $files as $file ) {
		$sel = $select == basename($file) ? 'selected="selected"' : '';
		$result .= '<option value="'.basename($file).'" '.$sel.'>'.basename($file).'</option>';
	}
	
	return $result;
}

function wppa_watermark_pos_select($default = false) {
global $wppa_opt;

	// Init
	$user = wppa_get_user();
	$result = '';
	$opt = array(	__('top - left', 'wppa'), __('top - center', 'wppa'), __('top - right', 'wppa'), 
					__('center - left', 'wppa'), __('center - center', 'wppa'), __('center - right', 'wppa'), 
					__('bottom - left', 'wppa'), __('bottom - center', 'wppa'), __('bottom - right', 'wppa'), );
	$val = array(	'toplft', 'topcen', 'toprht',
					'cenlft', 'cencen', 'cenrht',
					'botlft', 'botcen', 'botrht', );
	$idx = 0;

	// Find current selection
	$select = $wppa_opt['wppa_watermark_pos'];	// default
	if ( !$default && get_option('wppa_watermark_user') == 'yes' && get_option('wppa_watermark_pos_' . $user, 'nil') !== 'nil' ) {
		$select = get_option('wppa_watermark_pos_' . $user);
	}
	
	// Produce the html
	while ($idx < 9) {
		$sel = $select == $val[$idx] ? 'selected="selected"' : '';
		$result .= '<option value="'.$val[$idx].'" '.$sel.'>'.$opt[$idx].'</option>';
		$idx++;
	}
	
	return $result;
}

function wppa_table_exists($xtable) {
global $wpdb;

	$tables = $wpdb->get_results($wpdb->prepare("SHOW TABLES FROM ".DB_NAME), 'ARRAY_A');
	foreach ($tables as $table) {
		foreach ( $table as $item ) {
			if ($item == $xtable) return true;
		}
	}
	return false;
}