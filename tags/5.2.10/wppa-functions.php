<?php
/* wppa-functions.php
* Pachkage: wp-photo-album-plus
*
* Various funcions
* Version 5.2.10
*
*/

if ( ! defined( 'ABSPATH' ) )
    die( "Can't load this file directly" );

// Get the albums by calling the theme module and do some parameter processing
// This is the main entrypoint for the wppa+ invocation, either 'by hand' or through the filter.
// As of version 3.0.0 this routine returns the entire html created by the invocation.
function wppa_albums($id = '', $type = '', $size = '', $align = '') {
global $wppa;
global $wppa_opt;
global $wppa_lang;
global $wppa_locale;
global $wpdb;
global $thumbs;

	// Diagnostics
	wppa_dbg_msg('Entering wppa_albums');
	wppa_dbg_msg('Lang='.$wppa_lang.', Locale='.$wppa_locale.', Ajax='.$wppa['ajax']);
	if ( $wppa['debug'] ) {
		echo '<small>[WPPA+ dbg msg: $_SESSION = ';
		print_r($_SESSION);
		echo ']<br /></small>';
	}
	
	// Process a user upload request, if any. Do it here: it may affect this occurences display
	wppa_user_upload();

	// First calculate the occurance
	if ( $wppa['ajax'] ) {
		if ( isset($_GET['wppa-moccur']) ) {
			$wppa['master_occur'] = $_GET['wppa-moccur'];
			if ( ! is_numeric($wppa['master_occur']) ) wp_die('Are you cheeting? (1)');
		}
		else {
			$wppa['master_occur'] = '1';
		}
		
		$wppa['fullsize'] = $_GET['wppa-size'];
		
		if ( isset($_GET['wppa-occur']) ) {
			$wppa['occur'] = $_GET['wppa-occur'];
			if ( ! is_numeric($wppa['occur']) ) wp_die('Are you cheeting? (2)');
		}
		
		if ( isset($_GET['wppa-woccur']) ) {
			$wppa['widget_occur'] = $_GET['wppa-woccur'];
			$wppa['in_widget'] = true;
			if ( ! is_numeric($wppa['widget_occur']) ) wp_die('Are you cheeting? (3)');
		}
	}
	else {
		$wppa['master_occur']++;
		if ( $wppa['in_widget'] ) $wppa['widget_occur']++;
		else $wppa['occur']++;
	}

	// Set $wppa['src'] = true and $wppa['searcgstring'] if this occurrance processes a search request.
	wppa_test_for_search();
	
	// There are 3 ways to get here:
	// in order of priority:
	// 1. The given query string applies to this invocation (occurrance)
	//    This invocation requires the ignorance of the filter results and the interpretation of the querystring.
	if ( ( ( wppa_get_get('occur') || wppa_get_get('woccur') ) &&								// There IS a query string. For bw compat, occur is required ...
//		 ( wppa_get_get('album', false) !== false || wppa_get_get('photo') ) &&				// ... but not sufficient. Must have at least also album or photo
		 ( ( $wppa['in_widget'] && $wppa['widget_occur'] == wppa_get_get('woccur') ) ||		// and it applies to ...
		 ( ! $wppa['in_widget'] && $wppa['occur'] == wppa_get_get('occur') ) )				// this occurrance
		 ) && ! $wppa['is_autopage'] ) {
		// Process query string
		$wppa['out'] .= wppa_dbg_msg('Querystring applied', 'brown', false, true);
		$wppa['start_album'] 	= wppa_get_get('album', '');
		$wppa['is_cover'] 		= wppa_get_get('cover', '0');
		$wppa['is_slide'] 		= wppa_get_get('slide', false) !== false || ( wppa_get_get('album', false) !== false && wppa_get_get('photo') );
		if ( $wppa['is_slide'] ) wppa_dbg_msg('Is Slide');
		else wppa_dbg_msg('Is NOT Slide');
		$wppa['is_slideonly'] 	= '0';
		$wppa['is_slideonlyf'] 	= '0';
		$wppa['single_photo'] 	= $wppa['is_slide'] ? '0' : wppa_get_get('photo', '');
		$wppa['is_mphoto'] 		= '0';
		$wppa['film_on'] 		= '0';
		$wppa['is_landing'] 	= '0';
		$wppa['start_photo'] 	= $wppa['is_slide'] ? wppa_get_get('photo', '') : '0';	// Start a slideshow here
		wppa_dbg_msg('Start_phto='.$wppa['start_photo']);
		$wppa['is_single'] 		= wppa_get_get('single', false);					// Is a one image slideshow	
		$wppa['topten_count'] 	= wppa_get_get('topten', '0');
		$wppa['is_topten'] 		= $wppa['topten_count'] != '0';
		$wppa['lasten_count'] 	= wppa_get_get('lasten', '0');
		$wppa['is_lasten'] 		= $wppa['lasten_count'] != '0';
		$wppa['comten_count'] 	= wppa_get_get('comten', '0');
		$wppa['is_comten']		= $wppa['comten_count'] != '0';
		$wppa['featen_count']	= wppa_get_get('featen', '0');
		$wppa['is_featen']		= $wppa['featen_count'] != '0';
		$wppa['photos_only'] 	= wppa_get_get('photos-only', false);
		$wppa['related_count'] 	= wppa_get_get('relcount', '0');
		$wppa['is_related'] 	= wppa_get_get('rel', false);
		if ( $wppa['is_related'] == 'tags' ) {
			$wppa['is_tag'] = wppa_get_related_data();
			if ( $wppa['related_count'] == '0') $wppa['related_count'] = $wppa_opt['wppa_related_count'];
		}
		else $wppa['is_tag']	= trim(trim(strip_tags(wppa_get_get('tag', false)), ','), ';');
		if ( $wppa['is_related'] == 'desc' ) {
			$wppa['src'] = true;
			if ( $wppa['related_count'] == '0') $wppa['related_count'] = $wppa_opt['wppa_related_count'];
			$wppa['searchstring'] = str_replace(';', ',', wppa_get_related_data());
			$wppa['photos_only'] = true;
		}
		if ( $wppa['is_tag'] ) wppa_dbg_msg('Is Tag: '.$wppa['is_tag']);
		else wppa_dbg_msg('Is NOT Tag');
		$wppa['page'] 			= wppa_get_get('page', '');
		if ( wppa_get_get('superview', false) ) {
			$_SESSION['wppa_session']['superview'] = $wppa['is_slide'] ? 'slide': 'thumbs';
			$_SESSION['wppa_session']['superalbum'] = $wppa['start_album'];
		}
		$wppa['is_upldr'] 		= wppa_get_get('upldr', false);
		if ( $wppa['is_upldr'] ) $wppa['photos_only'] = true;
	}
	// 2. wppa_albums is called directly. Assume any arg. If not, no worry, system defaults are used == generic
	elseif ( $id != '' || $type != '' || $size != '' || $align != '' ) {
		// Do NOT Set internal defaults here, they may be set before the call

		// Interprete function args
		if ($type == 'album') {
		}
		elseif ( $type == 'cover' ) {
			$wppa['is_cover'] = '1';
		}
		elseif ( $type == 'slide' ) {
			$wppa['is_slide'] = '1';
		}
		elseif ( $type == 'slideonly' ) {
			$wppa['is_slideonly'] = '1';
		}
		
		if ( $type == 'photo' || $type == 'mphoto' || $type == 'slphoto' ) {	// Any type of single photo? id given is photo id
			if ( $id ) $wppa['single_photo'] = $id;
		}
		else {																	// Not single photo: id given is album id
			if ( $id ) $wppa['start_album'] = $id;
		}
	}
	// 3. The filter supplied the data
	else {
		if ( $wppa['is_landing'] && ! $wppa['src'] ) {
			wppa_dbg_msg('Nothing to do...');
			wppa_reset_occurrance();
			return '';	// Do nothing on a landing page without a querystring while it is also not a search operation
		}
		elseif ( $wppa['is_autopage'] ) {
			$wppa['single_photo'] = $wpdb->get_var( $wpdb->prepare( "SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `page_id` = %d LIMIT 1", get_the_ID() ) );
			if ( ! $wppa['single_photo'] ) {
				wppa_dbg_msg('No photo found for page '.get_the_ID(), 'red', 'force' );
				wppa_reset_occurrance();
				return '';	// Give up
			}
			$type = $wppa_opt['wppa_auto_page_type'];
			switch ( $type ) {
				case 'photo':
					break;
				case 'mphoto':
					$wppa['is_mphoto'] = true;
					break;
				case 'slphoto':
					$wppa['is_slide'] = '1';
					$wppa['start_photo'] = $wppa['single_photo'];
					$wppa['is_single'] = '1';
					break;
				default:
					wppa_dbg_msg('Unimplemented type autopage display: '.$type, 'red', 'force');
			}
		}
	}
	
	// Convert any keywords and / or names to numbers
	// Search for album keyword
	if ($wppa['start_album'] && !is_numeric($wppa['start_album'])) {
		if (substr($wppa['start_album'], 0, 1) == '#') {		// Keyword
			$keyword = $wppa['start_album'];
			if ( strpos($keyword, ',') ) $keyword = substr($keyword, 0, strpos($keyword, ','));
			switch ( $keyword ) {		//	( substr($wppa['start_album'], 0, 5) ) {	
				case '#last':				// Last upload
					$id = wppa_get_youngest_album_id();
					if ( $wppa['is_cover'] ) {	// To make sure the ordering sequence is ok.
						$temp = explode(',',$wppa['start_album']);
						if ( isset($temp['1']) && is_numeric($temp['1']) ) $wppa['last_albums_parent'] = $temp['1'];
						else $wppa['last_albums_parent'] = '0';
						if ( isset($temp['2']) && is_numeric($temp['2']) ) $wppa['last_albums'] = $temp['2'];
						else $wppa['last_albums'] = false;
					}
					else {		// Ordering seq is not important, convert to album enum				
						$temp = explode(',',$wppa['start_album']);
						if ( isset($temp['1']) && is_numeric($temp['1']) ) $parent = $temp['1'];
						else $parent = '0';
						if ( isset($temp['2']) && is_numeric($temp['2']) ) $limit = $temp['2'];
						else $limit = false;
						if ( $limit ) {
							if ( $parent ) {
								$q = $wpdb->prepare("SELECT `id` FROM `".WPPA_ALBUMS."` WHERE `a_parent` = %s ORDER BY `timestamp` DESC LIMIT %d", $parent, $limit);
							}
							else {
								$q = $wpdb->prepare("SELECT `id` FROM `".WPPA_ALBUMS."` ORDER BY `timestamp` DESC LIMIT %d", $limit);
							}
							$albs = $wpdb->get_results($q, ARRAY_A);
							if ( is_array($albs) ) foreach ( array_keys($albs) as $key ) $albs[$key] = $albs[$key]['id'];
							$id = implode('.', $albs);
						}					
					}
					break;
				case '#topten':
					$temp = explode(',',$wppa['start_album']);
					$id = isset($temp[1]) ? $temp[1] : '0';
					$wppa['topten_count'] = isset($temp[2]) ? $temp[2] : $wppa_opt['wppa_topten_count'];
					$wppa['is_topten'] = true;
					if ( $wppa['is_cover'] ) {
						wppa_dbg_msg('A topten album has no cover. '.$wppa['start_album'], 'red', 'force');
						wppa_reset_occurrance();
						return;	// Give up this occurence
					}
					break;
				case '#lasten':
					$temp = explode(',',$wppa['start_album']);
					$id = isset($temp[1]) ? $temp[1] : '0';
					$wppa['lasten_count'] = isset($temp[2]) ? $temp[2] : $wppa_opt['wppa_lasten_count'];
					$wppa['is_lasten'] = true;
					if ( $wppa['is_cover'] ) {
						wppa_dbg_msg('A lasten album has no cover. '.$wppa['start_album'], 'red', 'force');
						wppa_reset_occurrance();
						return;	// Give up this occurence
					}
					break;
				case '#comten':
					$temp = explode(',',$wppa['start_album']);
					$id = isset($temp[1]) ? $temp[1] : '0';
					$wppa['comten_count'] = isset($temp[2]) ? $temp[2] : $wppa_opt['wppa_comten_count'];
					$wppa['is_comten'] = true;
					if ( $wppa['is_cover'] ) {
						wppa_dbg_msg('A comten album has no cover. '.$wppa['start_album'], 'red', 'force');
						wppa_reset_occurrance();
						return;	// Give up this occurence
					}
					break;
				case '#featen':
					$temp = explode(',',$wppa['start_album']);
					$id = isset($temp[1]) ? $temp[1] : '0';
					$wppa['featen_count'] = isset($temp[2]) ? $temp[2] : $wppa_opt['wppa_featen_count'];
					$wppa['is_featen'] = true;
					if ( $wppa['is_cover'] ) {
						wppa_dbg_msg('A featen album has no cover. '.$wppa['start_album'], 'red', 'force');
						wppa_reset_occurrance();
						return;	// Give up this occurence
					}
					break;
				case '#related':
					$temp = explode(',',$wppa['start_album']);
					$type = isset($temp[1]) ? $temp[1] : 'tags';	// tags is default type
					$wppa['related_count'] = isset($temp[2]) ? $temp[2] : $wppa_opt['wppa_related_count'];
					$wppa['is_related'] = $type;
					
					$data = wppa_get_related_data();

					if ( $type == 'tags' ) {
						$wppa['is_tag'] = $data;
					}
					if ( $type == 'desc' ) {
						$wppa['src'] = true;
						$wppa['searchstring'] = str_replace(';', ',', $data);
						$wppa['photos_only'] = true;
					}
					$wppa['photos_only'] = true;
					$id = '0';	//$wppa['start_album'] = '';
					break;
				case '#tags':
					$wppa['is_tag'] = wppa_sanitize_tags(substr($wppa['start_album'], 6), true);
					$id = '0';	//$wppa['start_album'] = '';
					$wppa['photos_only'] = true;
					break;
				case '#cat':
					$temp = explode(',',$wppa['start_album']);
					$cat = isset( $temp[1] ) ? $temp[1] : '';
					$cat = wppa_sanitize_tags( $cat );
					$wppa['is_cat'] = $cat;
					if ( ! $cat ) {
						wppa_dbg_msg('Missing cat #cat album spec: '.$wppa['start_album'], 'red', 'force');
						wppa_reset_occurrance();
						return;	// Forget this occurrance
					}
					$albs = $wpdb->get_results( "SELECT `id`, `cats` FROM `".WPPA_ALBUMS."`", ARRAY_A );
					$id = '';
					if ( $albs ) foreach ( $albs as $alb ) {
						$temp = explode( ',', $alb['cats'] );
						if ( in_array( $cat, $temp ) ) {
							$id .= $alb['id'].'.';
						}
					}
					$id = rtrim($id, '.');
					break;
				case '#owner':
					$temp = explode(',',$wppa['start_album']);
					$owner = isset($temp[1]) ? $temp[1] : '';
					if ( $owner == '#me' ) {
						if ( is_user_logged_in() ) $owner = wppa_get_user();
						else {	// User not logged in, ignore shortcode
							wppa_reset_occurrance();
							return;	// Forget this occurrance
						}
					}
					if ( ! $owner ) {
						wppa_dbg_msg('Missing owner in #owner album spec: '.$wppa['start_album'], 'red', 'force');
						wppa_reset_occurrance();
						return;	// Forget this occurrance
					}
					$parent = isset($temp[2]) ? $temp[2] : '0';
					if ( ! wppa_is_int($parent) ) $parent = '0';
					$albs = $wpdb->get_results($wpdb->prepare("SELECT `id` FROM `".WPPA_ALBUMS."` WHERE `owner` = %s AND `a_parent` = %s", $owner, $parent), ARRAY_A);
					$id = '';
					if ( $albs ) foreach ( $albs as $alb ) {
						$id .= $alb['id'].'.';
					}
					$id = rtrim($id, '.');
					$wppa['is_owner'] = $owner;
					break;
				case '#upldr':
					$temp = explode(',',$wppa['start_album']);
					$owner = isset($temp[1]) ? $temp[1] : '';
					if ( $owner == '#me' ) {
						if ( is_user_logged_in() ) $owner = wppa_get_user();
						else {	// User not logged in, ignore shortcode
							wppa_reset_occurrance();
							return;	// Forget this occurrance
						}
					}
					if ( ! $owner ) {
						wppa_dbg_msg('Missing owner in #upldr album spec: '.$wppa['start_album'], 'red', 'force');
						wppa_reset_occurrance();
						return;	// Forget this occurrance
					}
					$id = '0';
					$wppa['is_upldr'] = $owner;
					$wppa['photos_only'] = true;
					break;
				case '#all':
					$id = '-2';
					break;
				default:
					wppa_dbg_msg('Unrecognized album keyword found: '.$wppa['start_album'], 'red', 'force');
					wppa_reset_occurrance();
					return;	// Forget this occurrance
			}
			$wppa['start_album'] = $id;
		}
	}
	
	// See if the album id is a name and convert it if possible
	if ($wppa['start_album'] && !is_numeric($wppa['start_album'])) {
		if (substr($wppa['start_album'], 0, 1) == '$') {		// Name
			$id = wppa_get_album_id_by_name(substr($wppa['start_album'], 1), 'report_dups');
			if ( $id > '0' ) $wppa['start_album'] = $id;
			elseif ( $id < '0' ) {
				wppa_dbg_msg('Duplicate album names found: '.$wppa['start_album'], 'red', 'force');
				wppa_reset_occurrance();
				return;	// Forget this occurrance
			}
			else {
				wppa_dbg_msg('Album name not found: '.$wppa['start_album'], 'red', 'force');
				wppa_reset_occurrance();
				return;	// Forget this occurrance
			}
		}
	}
	
	// Check if album is valid
	if ( strpos($wppa['start_album'], '.') !== false ) {	// Album may be enum
		if ( ! wppa_series_to_array($wppa['start_album']) ) { 	// Syntax error
			wppa_reset_occurrance();
			return;
		}
	}
	// Album must be numeric
	elseif ( $wppa['start_album'] && ! is_numeric($wppa['start_album']) ) {
		wppa_stx_err('Unrecognized Album identification found: '.$wppa['start_album']);
		wppa_reset_occurrance();
		return;	// Forget this occurrance
	}
	// Album must exist
	elseif ( $wppa['start_album'] > '0' ) {	// -2 is #all
		if ( ! wppa_album_exists($wppa['start_album']) ) {
			wppa_stx_err('Album does not exist: '.$wppa['start_album']);
			wppa_reset_occurrance();
			return;	// Forget this occurrance
		}
	}
	
	// See if the photo id is a keyword and convert it if possible
	if ($wppa['single_photo'] && !is_numeric($wppa['single_photo'])) {
		if (substr($wppa['single_photo'], 0, 1) == '#') {		// Keyword
			switch ($wppa['single_photo']) {
				case '#potd':				// Photo of the day
					$t = wppa_get_potd();
					if (is_array($t)) $id = $t['id'];
					else $id = '0';
					break;
				case '#last':				// Last upload
					$id = wppa_get_youngest_photo_id();
					break;
				default:
					wppa_dbg_msg('Unrecognized photo keyword found: '.$wppa['single_photo'], 'red', 'force');
					wppa_reset_occurrance();
					return;	// Forget this occurrance
			}
			$wppa['single_photo'] = $id;
		}
	}

	// See if the photo id is a name and convert it if possible
	if ($wppa['single_photo'] && !is_numeric($wppa['single_photo'])) {
		if (substr($wppa['single_photo'], 0, 1) == '$') {		// Name
			$id = wppa_get_photo_id_by_name(substr($wppa['single_photo'], 1));
			if ( $id > '0' ) $wppa['single_photo'] = $id;
			else {
				wppa_dbg_msg('Photo name not found: '.$wppa['single_photo'], 'red', 'force');
				wppa_reset_occurrance();
				return;	// Forget this occurrance
			}
		}
	}

	// Size and align
	if ( is_numeric($size) ) {
		$wppa['fullsize'] = $size;
	}
	elseif ( $size == 'auto' ) {
		$wppa['auto_colwidth'] = true;
	}
	if ( $align == 'left' || $align == 'center' || $align == 'right' ) {
		$wppa['align'] = $align;
	}

	// Empty related shortcode?
	if ( $wppa['is_related'] ) {
		$thumbs = false;
		wppa_get_thumbs();
		if ( empty($thumbs) ) {
			wppa_errorbox(__a('No related photos found.', 'wppa_theme'));
			$result = $wppa['out'];
			wppa_reset_occurrance();	// Forget this occurrance
			return $result;	
		}
	}
	
	// Is it the multitag box?
	if ( $wppa['is_multitagbox'] ) {
		wppa_multitag_box($wppa['tagcols'], $wppa['taglist']);
	}
	// Is it the tagcloud box?
	elseif ( $wppa['is_tagcloudbox'] ) {
		wppa_tagcloud_box($wppa['taglist'], '8', '24');
	}
	// Is it an upload box?
	elseif ( $wppa['is_upload'] ) {
		wppa_upload_box();
	}
	// Is it newstyle single photo mediastyle?
	elseif ( $wppa['is_mphoto'] == '1' ) {
		if ( $wppa['is_autopage'] ) wppa_auto_page_links('top');
		wppa_mphoto();
		if ( $wppa['is_autopage'] ) wppa_auto_page_links('bottom');
	}
	// Is it newstyle single photo plain?
	elseif ( wppa_page('oneofone') ) {
		if ( $wppa['is_autopage'] ) wppa_auto_page_links('top');
		wppa_sphoto();
		if ( $wppa['is_autopage'] ) wppa_auto_page_links('bottom');
	}
	// The normal case
	else {
		if ( function_exists('wppa_theme') ) {
			if ( $wppa['is_autopage'] ) wppa_auto_page_links('top');
			wppa_theme();	// Call the theme module
			if ( $wppa['is_autopage'] ) wppa_auto_page_links('bottom');
		}
		else $wppa['out'] = '<span style="color:red">ERROR: Missing function wppa_theme(), check the installation of WPPA+. Remove customized wppa_theme.php</span>';
		global $wppa_version; 
		$expected_version = '5-1-19';
		if ( $wppa_version != $expected_version ) wppa_dbg_msg('WARNING: customized wppa-theme.php is out of rev. Expected version: '.$expected_version.' found: '.$wppa_version, 'red');	
	}
	
	// Done
	$out = str_replace('w#location', $wppa['geo'], $wppa['out']);
	
	// Reset
	$wppa['out'] = ''; 
	$wppa['geo'] = '';
	wppa_reset_occurrance();
	return $out;		
}

function wppa_get_related_data() {
global $wpdb;
	$pagid = wppa_get_the_id();
	$data = $wpdb->get_var("SELECT `post_content` FROM `".$wpdb->posts."` WHERE `ID` = ".$pagid);
	$data = str_replace(array(' ', ',', '.', "\t", "\r", "0", "x0B", "\n"), ';', $data);
	$data = strip_tags($data);
	$data = strip_shortcodes($data);
	$data = wppa_sanitize_tags($data, true);
	$data = trim($data, "; \t\n\r\0\x0B");
	return $data;
}


// Prepare for next occurance by resetting runtime vars
function wppa_reset_occurrance() {
global $wppa;
global $thumb;
global $album;
global $thumbs;

	$thumbs = false;
	$thumb = false;
	$album = false;
	
	$wppa['src'] 			= false;
	$wppa['searchstring'] 	= '';
	$wppa['topten_count'] 	= '0';
	$wppa['is_topten'] 		= false;
	$wppa['lasten_count'] 	= '0';
	$wppa['is_lasten'] 		= false;
	$wppa['comten_count'] 	= '0';
	$wppa['is_comten']		= false;
	$wppa['is_featen']		= false;
	$wppa['featen_count'] 	= '0';
	$wppa['is_tag']			= false;
	
	$wppa['is_single'] 		= false;
	$wppa['is_mphoto'] 		= '0';
	$wppa['single_photo'] 	= '';
	
	$wppa['start_album'] 	= '';
	$wppa['is_cover'] 		= '0';
	$wppa['is_slide'] 		= '0';
	$wppa['is_slideonly'] 	= '0';
	$wppa['is_slideonlyf'] 	= '0';
	
	$wppa['film_on'] 		= '0';
	$wppa['is_landing'] 	= '0';
	$wppa['start_photo'] 	= '0';
	$wppa['photos_only']	= false;
	$wppa['albums_only'] 	= false;
	$wppa['page'] 			= '';
	$wppa['is_upload'] 		= false;
	$wppa['last_albums']	= false;
	$wppa['last_albums_parent']	= '0';
	$wppa['is_multitagbox'] = false;
	$wppa['is_tagcloudbox'] = false;
	$wppa['taglist'] 		= '';
	$wppa['tagcols']		= '2';
	$wppa['is_related']		= false;
	$wppa['related_count']	= '0';
	$wppa['is_owner']		= '';
	$wppa['is_upldr'] 		= '';
	$wppa['is_cat'] 		= false;


}

// Determine in wich theme page we are, Album covers (), Thumbnails
function wppa_page($page) {
global $wppa;

	if ( $wppa['in_widget'] ) {
		$occur = wppa_get_get('woccur', '0');
	}
	else {
		$occur = wppa_get_get('occur', '0');
	}

	$ref_occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
	
	if ( $wppa['is_slide'] == '1' ) $cur_page = 'slide';				// Do slide or single when explixitly on
	elseif ( $wppa['is_slideonly'] == '1' ) $cur_page = 'slide';		// Slideonly is a subset of slide
	elseif ( is_numeric($wppa['single_photo']) ) $cur_page = 'oneofone';
	elseif ( $occur == $ref_occur ) {									// Interprete $_GET only if occur is current
		if ( wppa_get_get('slide') !== false ) {
			$cur_page = 'slide';
		}
		elseif (wppa_get_get('photo')) {
			if (wppa_get_get('album') !== false ) {
				$cur_page = 'single';
			}
			else {
				$cur_page = 'oneofone';
				$wppa['single_photo'] = wppa_get_get('photo');
			}
		}
		else $cur_page = 'albums';
	}
	else $cur_page = 'albums';	

	if ($cur_page == $page) return true; else return false;
}

// loop album
function wppa_get_albums($album = false, $type = '') {
global $wpdb;
global $wppa;
global $wppa_opt;

	wppa_dbg_msg('get_albums entered: '.$wppa['master_occur'].' Start_album='.$wppa['start_album'].', Cover='.$wppa['is_cover']);
	
	if ( $wppa['is_topten'] ) return false;
	if ( $wppa['is_lasten'] ) return false;
	if ( $wppa['is_comten'] ) return false;
	if ( $wppa['is_featen'] ) return false;
	if ( $wppa['is_tag'] ) return false;
	if ( $wppa['photos_only'] ) return false;
	
	if ( $wppa['src'] && $wppa_opt['wppa_photos_only'] ) return false;
	if ( $wppa['is_owner'] && ! $wppa['start_album'] ) return false; 	// No owner album(s)
	
	if ( $wppa['src'] ) {	// Searching

		if ( wppa_switch('wppa_indexed_search') ) { 
			$final_array = array();
			$chunks = explode(',', stripslashes(strtolower($wppa['searchstring'])));
			// all chunks
			foreach ( $chunks as $chunk ) if ( strlen(trim($chunk)) ) {
			//	$words = explode(' ', trim($chunk));
				$words = wppa_index_raw_to_words($chunk);
				$album_array = array();
				// all words in the searchstring
				foreach ( $words as $word ) {	
					$word = trim($word);
//					$vfy = $word;
//					$vlen = strlen($vfy);
					if ( strlen($word) > 1 ) {
						if ( strlen($word) > 10 ) $word = substr($word, 0, 10);
//						$floor = $word;
//						$ceil = substr($floor, 0, strlen($floor) - 1).chr(ord(substr($floor, strlen($floor) - 1))+1);
						if ( wppa_switch('wppa_wild_front') ) {
							$aidxs = $wpdb->get_results("SELECT `slug`, `albums` FROM `".WPPA_INDEX."` WHERE `slug` LIKE '%".$word."%'", ARRAY_A);
						}
						else {
							$aidxs = $wpdb->get_results("SELECT `slug`, `albums` FROM `".WPPA_INDEX."` WHERE `slug` LIKE '".$word."%'", ARRAY_A);
						}
						$albums = '';
						if ( $aidxs ) {
							foreach ( $aidxs as $ai ) {
//								if ( substr($ai['slug'], 0, $vlen) == $vfy ) 
								$albums .= $ai['albums'].',';
							}
						}
						$album_array[] = wppa_index_string_to_array(trim($albums, ','));
					}
				}
				// Must meet all words: intersect photo sets			
				foreach ( array_keys($album_array) as $idx ) {
					if ( $idx > '0' ) {
						$album_array['0'] = array_intersect($album_array['0'], $album_array[$idx]);
					}				
				}
				// Save partial result
				if ( isset($album_array['0']) ) $final_array = array_merge($final_array, $album_array['0']);
			}
			
			// Compose WHERE clause
			$selection = " `id` = '0' ";
			foreach ( array_keys($final_array) as $p ) {
				$selection .= "OR `id` = '".$final_array[$p]."' ";
			}

			// Check maximum
			if ( count($final_array) > $wppa_opt['wppa_max_search_albums'] && $wppa_opt['wppa_max_search_albums'] != '0' ) {
				$alert_text = esc_js(sprintf(__a('There are %s albums found. Only the first %s will be shown. Please refine your search criteria.'), count($final_array), $wppa_opt['wppa_max_search_albums']));
				echo '<script type="text/javascript">alert(\''.$alert_text.'\');</script>';
				$limit = ' LIMIT '.$wppa_opt['wppa_max_search_albums'];
			}
			else $limit = '';

			// Get them
			$albums = $wpdb->get_results( "SELECT * FROM `" . WPPA_ALBUMS . "` WHERE " . $selection . " " . wppa_get_album_order('0') . $limit, ARRAY_A );
			wppa_dbg_q('Q10');
			
			// Exclusive separate albums?
			if ( wppa_switch('wppa_excl_sep') ) {
				foreach ( array_keys($albums) as $idx ) {
					if ( wppa_is_separate($albums[$idx]['id']) ) unset ( $albums[$idx] );
				}
			}
		}
		else { // Classic search
			$albs = $wpdb->get_results( "SELECT * FROM `" . WPPA_ALBUMS . "` " . wppa_get_album_order() , ARRAY_A );
			wppa_dbg_q('Q10');
			$albums = '';
			$idx = '0';
			foreach ( $albs as $album ) if ( ! $wppa_opt['wppa_excl_sep'] || $album['a_parent'] != '-1' ) {
				$haystack = __( $album['name'] ).' '.__( $album['description'] );
				if ( wppa_switch( 'wppa_search_cats' ) ) {
					$haystack .= ' '.str_replace( ',', ' ', $album['cats'] );
				}
				if ( wppa_deep_stristr( strtolower( $haystack ), $wppa['searchstring'] ) ) {
					$albums[$idx] = $album;
					$idx++;
				}
			}
		}
		if ( is_array( $albums ) ) $wppa['any'] = true;
	}
	else {	// Its not search
		$id = $wppa['start_album'];
		if ( ! $id ) $id = '0';
	
		// Do the query
		if ( $wppa['last_albums'] ) {	// is_cover = true. For the order sequence, see remark in wppa_albums()
			if ( $wppa['last_albums_parent'] ) {
				$q = $wpdb->prepare("SELECT * FROM `".WPPA_ALBUMS."` WHERE `a_parent` = %s ORDER BY `timestamp` DESC LIMIT %d", $wppa['last_albums_parent'], $wppa['last_albums']);
			}
			else {
				$q = $wpdb->prepare("SELECT * FROM `".WPPA_ALBUMS."` ORDER BY `timestamp` DESC LIMIT %d", $wppa['last_albums']);
			}
			wppa_dbg_msg($q);
			wppa_dbg_q('Q11a');
			$albums = $wpdb->get_results($q, ARRAY_A );
		}
		elseif ( wppa_is_int( $id ) ) {
			if ( $wppa['is_cover'] ) $q = $wpdb->prepare('SELECT * FROM ' . WPPA_ALBUMS . ' WHERE `id` = %s', $id);
			else $q = $wpdb->prepare( 'SELECT * FROM ' . WPPA_ALBUMS . ' WHERE `a_parent` = %s '. wppa_get_album_order( $id ), $id );
			wppa_dbg_msg($q);
			wppa_dbg_q('Q11b');
			$albums = $wpdb->get_results($q, ARRAY_A );
		}
		elseif ( strpos($id, '.') !== false ) {	// Album enum
			$ids = wppa_series_to_array($id);
			if ( $wppa['is_cover'] ) {
				$q = "SELECT * FROM `".WPPA_ALBUMS."` WHERE `id` = ".implode(" OR `id` = ", $ids);
			}
			else {
				$q = "SELECT * FROM `".WPPA_ALBUMS."` WHERE `a_parent` = ".implode(" OR `a_parent` = ", $ids);
			}

			wppa_dbg_msg($q);
			wppa_dbg_q('Q11c');
			$albums = $wpdb->get_results($q, ARRAY_A );
		}
		else $albums = false;
	}
	
	// Check for empty albums
	if ( wppa_switch('wppa_skip_empty_albums') ) {
		$user = wppa_get_user();
		if ( is_array($albums) ) foreach (array_keys($albums) as $albumkey) {
			$albumid 	= $albums[$albumkey]['id'];
			$albumowner = $albums[$albumkey]['owner'];
			$photocount = wppa_get_photo_count($albumid);
			$albumcount = wppa_get_album_count($albumid);
			if ( ! $photocount && ! $albumcount && ! current_user_can('administrator') && $user != $albumowner ) unset($albums[$albumkey]);
		}
	}
	
	$wppa['album_count'] = count($albums);
	return $albums;
}

// loop thumbs
function wppa_get_thumbs() {
global $wpdb;
global $wppa;
global $wppa_opt;
global $thumbs;

	if ( $wppa['is_owner'] && ! $wppa['start_album'] ) return false;	// No owner album(s) -> no photos
	
	wppa_dbg_msg('get_thumbs entered: '.$wppa['master_occur'].' Start_album='.$wppa['start_album'].', Cover='.$wppa['is_cover']);
	if ( $wppa['is_cover'] ) {
		wppa_dbg_msg('its cover, leave get_thumbs');
		return;
	}
	
	if ( is_array($thumbs) ) {	// Done already?
		wppa_dbg_msg('cached thumbs used');
		return $thumbs;	
	}
	
	$t = -microtime(true);

	// See if album is an enumeration or range
	$fullalb = $wppa['start_album'];	// Assume not
	if ( strpos($fullalb, '.') !== false ) {
		$ids = wppa_series_to_array($fullalb);
		$fullalb = implode(' OR `album` = ', $ids);
	}

	// Single image slideshow?
	if ( $wppa['start_photo'] && $wppa['is_single'] ) {
		$thumbs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `id` = %s', $wppa['start_photo'] ) , ARRAY_A );
		wppa_dbg_q('Q18');
	}
	// Uploader?
	elseif ( $wppa['is_upldr'] ) {
		$max = '1000000';
		$thumbs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `owner` = %s AND ( `status` <> 'pending' OR `owner` = %s ) ORDER BY `timestamp` DESC LIMIT %d", $wppa['is_upldr'], wppa_get_user(), $max ), ARRAY_A );
	}
	// Topten?	
	elseif ( $wppa['is_topten'] ) {
		$max = $wppa['topten_count'];
		$alb = $fullalb;
		if ($alb) $thumbs = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `mean_rating` > 0 AND ( `album` = ".$alb." ) ORDER BY `mean_rating` DESC LIMIT ".$max, ARRAY_A );
		else $thumbs = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `mean_rating` > 0 ORDER BY `mean_rating` DESC LIMIT ".$max, ARRAY_A );
		wppa_dbg_q('Q19');
	}
	// Featen?
	elseif ( $wppa['is_featen'] ) {
		$max = $wppa['featen_count'];
		$alb = $fullalb;
		if ($alb) $thumbs = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `status` = 'featured' AND ( `album` = ".$alb." ) ORDER BY RAND(".$wppa['randseed'].") DESC LIMIT ".$max, ARRAY_A );
		else $thumbs = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `status` = 'featured' ORDER BY RAND(".$wppa['randseed'].") DESC LIMIT ".$max, ARRAY_A );
		wppa_dbg_q('Q20');
	}	
	// Lasten?
	elseif ( $wppa['is_lasten'] ) {
		$max = $wppa['lasten_count'];
		$alb = $fullalb;
		if ( current_user_can('wppa_moderate') ) {
			if ($alb) $q =  "SELECT * FROM `".WPPA_PHOTOS."` WHERE `album` = ".$alb." ORDER BY `timestamp` DESC LIMIT ".$max;
			else $q = "SELECT * FROM `".WPPA_PHOTOS."` ORDER BY `timestamp` DESC LIMIT ".$max;
		}
		else {
			if ($alb) $q = "SELECT * FROM `".WPPA_PHOTOS."` WHERE ( `album` = ".$alb." ) AND `status` <> 'pending' ORDER BY `timestamp` DESC LIMIT " . $max;
			else $q = "SELECT * FROM `".WPPA_PHOTOS."` WHERE `status` <> 'pending' ORDER BY `timestamp` DESC LIMIT ".$max;
		}
		$thumbs = $wpdb->get_results( $q, ARRAY_A );
		wppa_dbg_msg($q);
		wppa_dbg_q('Q21');
	}
	// Comten?
	elseif ( $wppa['is_comten'] ) {
		$comments = $wpdb->get_results( "SELECT * FROM `".WPPA_COMMENTS."` WHERE `status` = 'approved' ORDER BY `timestamp` DESC", ARRAY_A );
		wppa_dbg_q('Q23');
		$max = $wppa['comten_count'];
		$alb = $fullalb;
		$thumbs = array();
		$indexes = array();
		$count = '0';
		$com_alt = $wppa['is_comten'] && wppa_switch('wppa_comten_alt_display') && ! $wppa['in_widget'];
		if ( $comments ) foreach ( $comments as $comment ) {
			if ( $com_alt && $count < $max ) {	// Duplicates allowed
				$thumb = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $comment['photo'] ), ARRAY_A );
				$thumb['com_id'] = $comment['id'];
				$thumbs[] = $thumb;
				$count++;
			}
			else {
				if ( ! in_array($comment['photo'], $indexes ) && $count < $max ) { 	// Not a duplicate
					$thumb = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $comment['photo'] ), ARRAY_A );
					wppa_dbg_q('Q24');
					if ( !$alb || $alb == $thumb['album'] || ( is_array($alb) && in_array($thumb['album'], $alb ) ) ) {
						$thumbs[] = $thumb;
						$indexes[] = $comment['photo'];	// remember for check on duplicate
						$count++;
					}
				}
			}
		}
	}
	// Tagcloud or multitag? Tags do not look at album
	elseif ( $wppa['is_tag'] ) {
		if ( current_user_can('wppa_moderate') ) {
			$temp = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `tags` <> '' ".wppa_get_photo_order('0'), ARRAY_A ); 
		}
		else {
			$temp = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `status` <> 'pending' AND `tags` <> '' ".wppa_get_photo_order('0'), ARRAY_A ); 
		}
		$tags = wppa_get_taglist();
		$thumbs = false;
		$andor = 'and';
		if ( strpos($wppa['is_tag'], ';') ) $andor = 'or';

		foreach ( array_keys($temp) as $index ) {
			if ( $andor == 'and' ) {	// and
				$seltags = explode(',',$wppa['is_tag']);
				$in = true;
				if ( $seltags ) foreach ( $seltags as $seltag ) {
					if ( $seltag && ! @in_array($temp[$index]['id'], $tags[$seltag]['ids']) ) {
						$in = false;
					}
				}
			}
			else {	// or
				$seltags = explode(';',$wppa['is_tag']);
				$in = false;
				if ( $seltags ) foreach ( $seltags as $seltag ) {
					if ( $seltag && @in_array($temp[$index]['id'], $tags[$seltag]['ids']) ) {
						$in = true;
					}
				}
			}
			if ( $in ) {
				if ( $wppa['is_related'] != 'tags' || count($thumbs) < $wppa['related_count'] ) $thumbs[] = $temp[$index];
			}
		}
		wppa_dbg_msg('Found:'.count($thumbs).' thumbs');
	}
	// Search?
	elseif ( $wppa['src'] ) {	// Searching

		// Indexed search??
		if ( wppa_switch('wppa_indexed_search') ) { 
			$final_array = array();
			$chunks = explode(',', stripslashes(strtolower($wppa['searchstring'])));
			// all chunks
			foreach ( $chunks as $chunk ) if ( strlen(trim($chunk)) ) {
			//	$words = explode(' ', trim($chunk));
				$words = wppa_index_raw_to_words($chunk);
				$photo_array = array();
				// all words in the searchstring
				foreach ( $words as $word ) {	
					$word = trim($word);
//					$vfy = $word;
//					$vlen = strlen($vfy);
					if ( strlen($word) > 1 ) {
						if ( strlen($word) > 20 ) $word = substr($word, 0, 20);
//						$floor = $word;
//						$ceil = substr($floor, 0, strlen($floor) - 1).chr(ord(substr($floor, strlen($floor) - 1))+1);
						if ( wppa_switch('wppa_wild_front') ) {
							$pidxs = $wpdb->get_results("SELECT `slug`, `photos` FROM `".WPPA_INDEX."` WHERE `slug` LIKE '%".$word."%'", ARRAY_A);
						}
						else {
							$pidxs = $wpdb->get_results("SELECT `slug`, `photos` FROM `".WPPA_INDEX."` WHERE `slug` LIKE '".$word."%'", ARRAY_A);
						}
						$photos = '';
						if ( $pidxs ) {
							foreach ( $pidxs as $pi ) {
//								if ( substr($pi['slug'], 0, $vlen) == $vfy ) 
								$photos .= $pi['photos'].',';
							}
						}
						$photo_array[] = wppa_index_string_to_array(trim($photos, ','));
					}
				}
				// Must meet all words: intersect photo sets			
				foreach ( array_keys($photo_array) as $idx ) {
					if ( $idx > '0' ) {
						$photo_array['0'] = array_intersect($photo_array['0'], $photo_array[$idx]);
					}				
				}
				// Save partial result
				if ( isset($photo_array['0']) ) $final_array = array_merge($final_array, $photo_array['0']);
			}
			
			// Compose WHERE clause
			$selection = " `id` = '0' ";
			$count = '0';
			foreach ( array_keys($final_array) as $p ) {
				if ( $wppa_opt['wppa_max_search_photos'] && $count < $wppa_opt['wppa_max_search_photos'] ) {
					$selection .= "OR `id` = '".$final_array[$p]."' ";
					$count++;
				}
			}

			// Check maximum
			if ( count($final_array) > $wppa_opt['wppa_max_search_photos'] && $wppa_opt['wppa_max_search_photos'] != '0' ) {
				$alert_text = esc_js(sprintf(__a('There are %s photos found. Only the first %s will be shown. Please refine your search criteria.'), count($final_array), $wppa_opt['wppa_max_search_photos']));

				echo '<script type="text/javascript">alert(\''.$alert_text.'\');</script>';
				$limit = ' LIMIT '.$wppa_opt['wppa_max_search_photos'];
			}
			else $limit = '';
			// Get them, depending of 'pending' criteria
			if ( current_user_can('wppa_moderate') ) {
				$thumbs = $wpdb->get_results( "SELECT * FROM `" . WPPA_PHOTOS . "` WHERE " . $selection . wppa_get_photo_order('0') . $limit, ARRAY_A );
			}
			else {
				$thumbs = $wpdb->get_results( "SELECT * FROM `" . WPPA_PHOTOS . "` WHERE status <> 'pending' AND (" . $selection . ") " . wppa_get_photo_order('0') . $limit, ARRAY_A );
			}
			wppa_dbg_q('Q25');
			
			// Check on seperate albums?
			if ( wppa_switch('wppa_excl_sep') ) {
				foreach ( array_keys($thumbs) as $idx ) {
					$alb = $thumbs[$idx]['album'];
					if ( wppa_is_separate($alb) ) unset ( $thumbs[$idx] );
				}
				// Sequence broken, create new indexes for thumbs array
				$temp = $thumbs;
				$thumbs = array();
				foreach($temp as $item) {
					$thumbs[] = $item;
				}
			}
		}
		else { // Conventional search

			if ( current_user_can('wppa_moderate') ) {
				$q = "SELECT * FROM `" . WPPA_PHOTOS . "` " . wppa_get_photo_order('0');
			}
			else {
				$q = "SELECT * FROM `" . WPPA_PHOTOS . "` WHERE status <> 'pending' " . wppa_get_photo_order('0');
			}
			$tmbs = $wpdb->get_results( $q, ARRAY_A );
			wppa_dbg_msg($q);
			wppa_dbg_q('Q25');
			
			$thumbs = array();
			foreach ( $tmbs as $thumb ) {
				if ( ! $wppa_opt['wppa_excl_sep'] || ! wppa_is_separate($thumb['album']) ) {	// Not exclude sepreate or not seperate
					$haystack = __($thumb['name']).' '.wppa_filter_exif(wppa_filter_iptc(__(stripslashes($thumb['description'])),$thumb['id']),$thumb['id']);
					if ( wppa_switch('wppa_search_tags') ) {
						$haystack .= ' '.str_replace(',', ' ', $thumb['tags']);
					}
					if ( wppa_switch( 'wppa_search_comments' ) ) {
						$comms = $wpdb->get_results($wpdb->prepare(" SELECT * FROM `".WPPA_COMMENTS."` WHERE `photo` = %s", $thumb['id'] ), ARRAY_A );
						if ( $comms ) foreach ( $comms as $comm ) {
							$haystack .= $comm['comment'];
						}
					}
					if ( wppa_deep_stristr(strtolower($haystack), $wppa['searchstring']) ) {
						$thumbs[] = $thumb;
					}
				}
			}
		}

		$wppa['any'] = ! empty ( $thumbs );
	}
	else {
		
		// Init $thumbs
		$thumbs = array();
		// On which album(s)?
		if ( strpos($wppa['start_album'], '.') !== false ) $allalb = wppa_series_to_array($wppa['start_album']);
		else $allalb = array($wppa['start_album']);

		foreach ( $allalb as $id ) {	// Do each album
			if (is_numeric($id)) {
				$wppa['current_album'] = $id;
				if ( $id == -2 ) {	// album == -2 is now: all albums
					if ( current_user_can('wppa_moderate') ) {
						$q = "SELECT * FROM `".WPPA_PHOTOS."` ".wppa_get_photo_order('0');
					}
					else {
						$q = $wpdb->prepare( "SELECT * FROM ".WPPA_PHOTOS." WHERE ( status <> %s OR owner = %s) ".wppa_get_photo_order('0'), 'pending', wppa_get_user() );
					}
					$partthumbs = $wpdb->get_results( $q, ARRAY_A ); 
					wppa_dbg_msg($q);
					wppa_dbg_q('Q26');
				}
				else {
					if ( current_user_can('wppa_moderate') ) {
						$q = $wpdb->prepare( "SELECT * FROM ".WPPA_PHOTOS." WHERE album = %s ".wppa_get_photo_order($id), $id );
					}
					else {
						$q = $wpdb->prepare( "SELECT * FROM ".WPPA_PHOTOS." WHERE album = %s AND ( status <> %s OR owner = %s) ".wppa_get_photo_order($id), $id, 'pending', wppa_get_user() );
					}
					$partthumbs = $wpdb->get_results( $q, ARRAY_A ); 
					wppa_dbg_msg($q);
					wppa_dbg_q('Q27');
				}
				if ( is_array($partthumbs) ) $thumbs = array_merge($thumbs, $partthumbs);
			}
		}
	}
	
	$wppa['thumb_count'] = empty($thumbs) ? '0' : count($thumbs);
	$t += microtime(true);
	wppa_dbg_msg('Get thumbs took '.$t.' seconds, found: '.$wppa['thumb_count'].' items.');
	return $thumbs;
}

/*
// Applies querystring to this occur?
function wppa_is_this_occur() {
global $wppa;
	if ($wppa['in_widget']) {
		$occur = wppa_get_get('woccur', '0');
	}
	else {
		$occur = wppa_get_get('occur', '0');
	}
	$ref_occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];

	return ($occur == $ref_occur);
}
*/
// get slide info
function wppa_get_slide_info($index, $id, $callbackid = '') {
global $wpdb;
global $wppa;
global $wppa_opt;
global $thumb;

	// Make sure $thumb contains our image data
	wppa_cache_thumb($id);

	$user = wppa_get_user();
	$photo = wppa_get_get('photo', '0');
	$ratingphoto = wppa_get_get('rating-id', '0');
	
	if ( ! $callbackid ) $callbackid = $id;
	
	// Process a comment if given for this photo
	$comment_request = (wppa_get_post('commentbtn') && ($id == $photo));
	$comment_allowed = (!$wppa_opt['wppa_comment_login'] || is_user_logged_in());
	if ($wppa_opt['wppa_show_comments'] && $comment_request && $comment_allowed) {
		wppa_do_comment($id);
	}

	// Process a rating if given for this photo
	if ( $wppa_opt['wppa_rating_on'] ) {	// Ajax only
		
		// Find my (avg) rating
		$rats = $wpdb->get_results( $wpdb->prepare( 'SELECT `value` FROM `'.WPPA_RATING.'` WHERE `photo` = %s AND `user` = %s', $id, $user ), ARRAY_A ); 
		wppa_dbg_q('Q33v');
		if ( $rats ) {
			$n = 0;
			$accu = 0;
			foreach ( $rats as $rat ) {
				$accu += $rat['value'];
				$n++;
			}
			$myrat = $accu / $n;
			$i = $wppa_opt['wppa_rating_prec'];
			$j = $i + '1';
			$myrat = sprintf('%'.$j.'.'.$i.'f', $myrat);
		}
		else $myrat = '0';

		// Find the avg rating
		$avgrat = wppa_get_rating_by_id($id, 'nolabel');
		if ( ! $avgrat ) $avgrat = '0';
		$avgrat .= '|'.wppa_get_rating_count_by_id($id);
		
		// Find the dislike count
		$discount = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM `".WPPA_RATING."` WHERE `photo` = %s AND `value` = -1", $id) );
	}
	else {	// Rating off
		$myrat = '0';
		$avgrat = '0';
		$discount = '0';
	}
	
	// Find comments
	$comment = ( $wppa_opt['wppa_show_comments'] && ! $wppa['is_filmonly'] ) ? wppa_comment_html($id, $comment_allowed) : '';
	
	// Get the callback url.
	if ( $wppa_opt['wppa_rating_on'] ) {
		$url = wppa_get_slide_callback_url($callbackid);
		$url = str_replace('&amp;', '&', $url);	// js use
	}
	else {
		$url = '';
	}
	
	// Find link url, link title and link target
	if ($wppa['in_widget'] == 'ss') {
		$link = wppa_get_imglnk_a('sswidget', $id);
	}
	else {
		$link = wppa_get_imglnk_a('slideshow', $id);
	}
	$linkurl = $link['url'];
	$linktitle = $link['title'];
	$linktarget = $link['target'];

	// Find full image style and size
	if ( $wppa['is_filmonly'] ) {
		$style_a['style'] = '';
		$style_a['width'] = '';
		$style_a['height'] = '';
	}
	else {
		$style_a = wppa_get_fullimgstyle_a($id);
	}
	
	// Find image url
	$usethumb = wppa_use_thumb_file( $id, $style_a['width'], $style_a['height'] );
	$photourl = $usethumb ? wppa_get_thumb_url( $id, '', $style_a['width'], $style_a['height'] ) : wppa_get_photo_url( $id, '', $style_a['width'], $style_a['height'] );

	// Find iptc data
	$iptc = ( $wppa_opt['wppa_show_iptc'] && ! $wppa['is_slideonly'] && ! $wppa['is_filmonly'] ) ? wppa_iptc_html($id) : '';
	
	// Find EXIF data
	$exif = ( $wppa_opt['wppa_show_exif'] && ! $wppa['is_slideonly'] && ! $wppa['is_filmonly'] ) ? wppa_exif_html($id) : '';
	
	// Lightbox subtitle
	$doit = false;
	if ( $wppa_opt['wppa_slideshow_linktype'] == 'lightbox' || $wppa_opt['wppa_slideshow_linktype'] == 'lightboxsingle' ) $doit = true;	// For fullsize
	if ( $wppa_opt['wppa_filmstrip'] && $wppa_opt['wppa_film_linktype'] == 'lightbox') {	// For filmstrip?
		if ( ! $wppa['is_slideonly'] ) $doit = true;		// Film below fullsize
		if ( $wppa['film_on'] ) $doit = true;				// Film explicitly on (slideonlyf)		
	}
	if ( $doit ) {
		$lbtitle = wppa_get_lbtitle('slide', $id);
	}
	else $lbtitle = '';
	
	// Name
	$name = esc_js(wppa_get_photo_name($id));
	if ( ! $name ) $name = '&nbsp;';
	$fullname = esc_js(wppa_get_photo_name($id, $wppa_opt['wppa_show_full_owner']));
	if ( ! $fullname ) $fullname = '&nbsp;';
	
	// Shareurl
	if ( $wppa['is_filmonly'] ) {
		$shareurl = '';
	}
	else {
		$shareurl = wppa_get_image_page_url_by_id($id, false, $wppa['start_album']);
		$shareurl = wppa_convert_to_pretty($shareurl);
		$shareurl = str_replace('&amp;', '&', $shareurl);
	}
	
	// Make photo desc, filtered
	if ( ( ! $wppa['is_slideonly'] || $wppa['desc_on'] ) && ! $wppa['is_filmonly'] ) {
		$desc = wppa_get_photo_desc($id, $wppa_opt['wppa_allow_foreign_shortcodes'], 'do_geo');	// Foreign shortcodes is handled here
		
		// Run wpautop on description?
		if ( $wppa_opt['wppa_run_wppautop_on_desc'] ) {
			$desc = wpautop($desc);	
		}

		// And format
		$desc = wppa_html(esc_js(stripslashes($desc)));

		// Remove extra space created by other filters like wpautop
		if ( $wppa_opt['wppa_allow_foreign_shortcodes'] && $wppa_opt['wppa_clean_pbr'] ) {
			$desc = str_replace(array("<p>", "</p>", "<br>", "<br/>", "<br />"), " ", $desc);
		}

		if ( ! $desc ) $desc = '&nbsp;';
	}
	else {
		$desc = '';
	}
	
	// Edit photo link
	if ( ! $wppa['is_filmonly'] ) {
		if ( ! wppa_is_user_blacklisted() ) {
			if ( ( current_user_can('wppa_admin') ) || ( wppa_get_user() == wppa_get_photo_owner($id) && current_user_can('wppa_upload') && wppa_switch('wppa_upload_edit') ) ) {
				$desc = '<div style="float:right; margin-right:6px;" ><a href="javascript:void();" onclick="_wppaStop('.$wppa['master_occur'].');wppaEditPhoto('.$wppa['master_occur'].', '.$thumb['id'].'); return false;" >'.__a('Edit').'</a></div><br />'.$desc;
			}
		}
	}
	
	if ( $thumb['status'] == 'pending' ) {
		$desc .= wppa_html(esc_js(wppa_moderate_links('slide', $id)));
	}

	// Share HTML 
	$sharehtml = ( $wppa['is_filmonly'] || $wppa['is_slideonly'] ) ? '' : wppa_get_share_html();
	
	// Og Description
	$ogdsc = ( wppa_switch('wppa_facebook_comments') && ! $wppa['in_widget'] ) ? esc_js(wppa_get_og_desc($id)) : '';
	
	// Hires url
	$hiresurl = wppa_get_hires_url( $id );

	// Produce final result
    $result = "'".$wppa['master_occur']."','";
	$result .= $index."','";
	$result .= $photourl."','";
	$result .= $style_a['style']."','";
	$result .= $style_a['width']."','";
	$result .= $style_a['height']."','";
	$result .= $fullname."','";
	$result .= $name."','";
	if ( $wppa['debug'] ) $result .= '/* desc: */';
	$result .= $desc."','";
	$result .= $id."','";
	$result .= $avgrat."','";
	$result .= $discount."','";
	$result .= $myrat."','";
	$result .= $url."','";
	$result .= $linkurl."','".$linktitle."','".$linktarget."','";
	$result .= $wppa['in_widget_timeout']."','";
	$result .= $comment."','";
	$result .= $iptc."','";
	$result .= $exif."','";
	$result .= $lbtitle."','";
	if ( $wppa['debug'] ) $result .= '/* shareurl: */';
	$result .= $shareurl."','";	// Used for history.pushstate()
	if ( $wppa['debug'] ) $result .= '/* sharehtml: */';
	$result .= $sharehtml."','";	// The content of the SM (share) box
	if ( $wppa['debug'] ) $result .= '/* ogdesc: */';
	$result .= $ogdsc."','";
	$result .= $hiresurl."'";
	
	// This is an ingenious line of code that is going to prevent us from very much trouble. 
	// Created by OpaJaap on Jan 15 2012, 14:36 local time. Thanx.
	// Make sure there are no linebreaks in the result that would screw up Javascript.
	return str_replace(array("\r\n", "\n", "\r"), " ", $result);	
}

// Process a comment request
function wppa_do_comment($id) {
global $wpdb;
global $wppa;
global $wppa_opt;
global $wppa_done;

	if ($wppa_done) return; // Prevent multiple
	$wppa_done = true;
	
	$time = time();
	$photo = isset($_REQUEST['photo']) ? strval(intval($_REQUEST['photo'])) : '0';	//wppa_get_get('photo');
	if ( ! $photo ) $photo = isset($_REQUEST['photo-id']) ? strval(intval($_REQUEST['photo-id'])) : '0';	//wppa_get_get('photo');
	if ( ! $photo ) die('Photo id missing while processing a comment');
	$user = wppa_get_post('comname');
	if ( ! $user ) die('Illegal attempt to enter a comment 1');
	$email = wppa_get_post('comemail');
	if ( ! $email ) {
		if ( wppa_switch('wppa_comment_email_required') ) die('Illegal attempt to enter a comment 2');
		else $email = wppa_get_user();	// If email not present and not required, use his IP
	}
	
	// Retrieve and filter comment
	$comment = wppa_get_post('comment');
	$comment = trim($comment);
	$comment = wppa_decode($comment);
//	$comment = stripslashes($comment);
	$comment = strip_tags($comment); //wppa_strip_tags($comment, 'script&style');
//	$comment = htmlspecialchars($comment);
	$save_comment = str_replace("\n", '<br />', $comment);	// Resque newline chars
	
	$policy = $wppa_opt['wppa_comment_moderation'];
	switch ($policy) {
		case 'all':
			$status = 'pending';
			break;
		case 'logout':
			$status = is_user_logged_in() ? 'approved' : 'pending';
			break;
		case 'none':
			$status = 'approved';
			break;
	}
	if ( current_user_can('wppa_moderate') ) $status = 'approved';	// Need not moderate comments issued by moderator

	// Editing a comment?
	$cedit = wppa_get_post('comment-edit', '0');
	if ( ! wppa_is_int($cedit) ) wp_die('Security check falure 14');

	// Check captcha
	if ( wppa_switch('wppa_comment_captcha') ) {
		$captkey = $id;
		if ( $cedit ) $captkey = $wpdb->get_var($wpdb->prepare('SELECT `timestamp` FROM `'.WPPA_COMMENTS.'` WHERE `id` = %s', $cedit)); 
		wppa_dbg_q('Q43');
		if ( ! wppa_check_captcha($captkey) ) {
				$status = 'spam';
		}
	}

	// Process (edited) comment
	if ($comment) {
		if ($cedit) {
			$query = $wpdb->prepare('UPDATE `'.WPPA_COMMENTS.'` SET `comment` = %s, `user` = %s, `email` = %s, `status` = %s, `timestamp` = %s WHERE `id` = %s LIMIT 1', $save_comment, $user, $email, $status, time(), $cedit);
			wppa_dbg_q('Q44');
			$iret = $wpdb->query($query);
			if ($iret !== false) {
				$wppa['comment_id'] = $cedit;
			}
		}
		else {
			// See if a refresh happened
			$old_entry = $wpdb->prepare('SELECT * FROM `'.WPPA_COMMENTS.'` WHERE `photo` = %s AND `user` = %s AND `comment` = %s LIMIT 1', $photo, $user, $save_comment);
			$iret = $wpdb->query($old_entry);
			if ($iret) {
				if ($wppa['debug']) echo('<script type="text/javascript">alert("Duplicate comment ignored")</script>');
				return;
			}
//			$key = wppa_nextkey(WPPA_COMMENTS);
//			$query = $wpdb->prepare('INSERT INTO `'.WPPA_COMMENTS.'` (`id`, `timestamp`, `photo`, `user`, `email`, `comment`, `status`, `ip`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s )', $key, $time, $photo, $user, $email, $save_comment, $status, $_SERVER['REMOTE_ADDR']);
//			$iret = $wpdb->query($query);
			$key = wppa_create_comments_entry( array( 'photo' => $photo, 'user' => $user, 'email' => $email, 'comment' => $save_comment, 'status' => $status ) );
			if ( $key ) $wppa['comment_id'] = $key;
		}
		if ( $iret !== false ) {
			if ( $status != 'spam' ) {
				if ($cedit) {
					if ( wppa_switch('wppa_comment_notify_added') ) echo('<script id="cme" type="text/javascript">alert("'.__a('Comment edited').'");jQuery("#cme").html("");</script>');
				}
				else {
					// SUCCESSFUL COMMENT, ADD POINTS
					if( function_exists('cp_alterPoints') && is_user_logged_in() ) {
						cp_alterPoints(cp_currentUser(), $wppa_opt['wppa_cp_points_comment']);
					}
					// SEND EMAILS
					$subj = __a('Comment on photo:').' '.wppa_get_photo_name($id);
					$usr  = $user;
					if ( is_user_logged_in() ) {
						global $current_user;
						get_currentuserinfo();
						$usr = $current_user->display_name;
					}
					$cont['0'] = $usr.' '.__a('wrote on photo').' '.wppa_get_photo_name($id).':';
					$cont['1'] = '<blockquote><em> '.$comment.'</em></blockquote>';
					$cont2     = '<a href="'.get_admin_url().'admin.php?page=wppa_manage_comments&commentid='.$key.'" >'.__a('Moderate comment admin').'</a>';
					$cont3     = '<a href="'.get_admin_url().'admin.php?page=wppa_admin_menu&tab=cmod&photo='.$id.'" >'.__a('Moderate manage photo').'</a>';
					$cont3a	   = '<a href="'.get_admin_url().'admin.php?page=wppa_edit_photo&photo='.$id.'" >'.__a('Edit photo').'</a>';
					
					$sentto = array();
					if ( is_numeric($wppa_opt['wppa_comment_notify']) ) {	// single user
						// Mail specific user
						$moduser 	= get_userdata($wppa_opt['wppa_comment_notify']);
						$to      	= $moduser->user_email;
						if ( user_can( $moduser, 'wppa_comments' ) ) $cont['2'] = $cont2; else $cont['2'] = '';
						if ( user_can( $moduser, 'wppa_admin' ) ) 	 $cont['3'] = $cont3; else $cont['3'] = '';
						$cont['4'] 	= __a('You receive this email as you are assigned to moderate');
						// Send!
						wppa_send_mail($to, $subj, $cont, $photo, $email);
						$sentto[] = $moduser->login_name;
					}
					if ( $wppa_opt['wppa_comment_notify'] == 'admin' || $wppa_opt['wppa_comment_notify'] == 'both' || $wppa_opt['wppa_comment_notify'] == 'upadmin' ) {
						// Mail admin
						$moduser   = get_user_by('id', '1');
						if ( ! in_array( $moduser->login_name, $sentto ) ) {	// Already sent him?
							$to        = get_bloginfo('admin_email');
							$cont['2'] = $cont2;
							$cont['3'] = $cont3;
							$cont['4'] = __a('You receive this email as administrator of the site');
							// Send!
							wppa_send_mail($to, $subj, $cont, $photo, $email);
							$sentto[] = $moduser->login_name;
						}
					}
					if ( $wppa_opt['wppa_comment_notify'] == 'upload' || $wppa_opt['wppa_comment_notify'] == 'upadmin' || $wppa_opt['wppa_comment_notify'] == 'upowner' ) {
						// Mail uploader
						$uploader = $wpdb->get_var($wpdb->prepare("SELECT `owner` FROM `".WPPA_PHOTOS."` WHERE `id` = %d", $id));
						$moduser = get_user_by('login', $uploader);
						if ( $moduser ) {	// else it's an ip address (anonymus uploader)
							if ( ! in_array( $moduser->login_name, $sentto ) ) {	// Already sent him?
								$to = $moduser->user_email;
								$cont['2'] = user_can( $moduser, 'wppa_comments' ) ? $cont2 : '';
								if ( user_can( $moduser, 'wppa_admin' ) ) $cont['3'] = $cont3;
								elseif ( wppa_switch('wppa_upload_edit') ) $cont['3'] = $cont3a;
								else $cont['3'] = '';
								$cont['4'] = __a('You receive this email as uploader of the photo');
								// Send!
								wppa_send_mail($to, $subj, $cont, $photo, $email);
								$sentto[] = $moduser->login_name;
							}
						}
					}
					if ( $wppa_opt['wppa_comment_notify'] == 'owner' || $wppa_opt['wppa_comment_notify'] == 'both' || $wppa_opt['wppa_comment_notify'] == 'upowner' ) {
						// Mail album owner
						$alb     = $wpdb->get_var($wpdb->prepare("SELECT `album` FROM `".WPPA_PHOTOS."` WHERE `id` = %d", $id));
						$owner   = $wpdb->get_var($wpdb->prepare("SELECT `owner` FROM `".WPPA_ALBUMS."` WHERE `id` = %d", $alb));
						if ( $owner == '--- public ---' ) $owner = 'admin';
						$moduser = get_user_by('login', $owner);
						if ( ! in_array( $moduser->login_name, $sentto ) ) {	// Already sent him?
							$to = $moduser->user_email;
							if ( user_can( $moduser, 'wppa_comments' ) ) $cont['2'] = $cont2; else $cont['2'] = '';
							if ( user_can( $moduser, 'wppa_admin' ) ) 	 $cont['3'] = $cont3; else $cont['3'] = '';
							$cont['4'] = __a('You receive this email as owner of the album');
							// Send!
							wppa_send_mail($to, $subj, $cont, $photo, $email);
							$sentto[] = $moduser->login_name;
						}
					}
					// Notyfy user
					if ( wppa_switch('wppa_comment_notify_added') ) echo('<script id="cma" type="text/javascript">alert("'.__a('Comment added').'");jQuery("#cma").html("")</script>');
				}
			}
			else {
				echo('<script type="text/javascript">alert("'.__a('Sorry, you gave a wrong answer.\n\nPlease try again to solve the computation.').'")</script>');
			}

			$wppa['comment_photo'] = $id;
			$wppa['comment_text'] = $comment;
			
			// Clear (super)cache
			wppa_clear_cache();
		}
		else {
			echo('<script type="text/javascript">alert("'.__a('Could not process comment.\nProbably timed out.').'")</script>');
		}
	}
	else {	// Empty comment
	}
}

// Create a captcha
function wppa_make_captcha($id) {
	$capt = wppa_ll_captcha($id);
	return $capt['text'];
}

// Check the comment security answer
function wppa_check_captcha($id) {
	$answer = wppa_get_post('wppa-captcha');
	$capt = wppa_ll_captcha($id);
	return $capt['ans'] == $answer;
}

// Low level captcha routine
function wppa_ll_captcha($id) {
	$nonce = wp_create_nonce('wppa_photo_comment_'.$id);
	$result['val1'] = 1 + intval(substr($nonce, 0, 4), 16) % 12;
	$result['val2'] = 1 + intval(substr($nonce, -4), 16) % 12;
	if ( $result['val1'] == $result['val1'] ) $result['val2'] = 1 + intval(substr($nonce, -5, 4), 16) % 12;
	if ( $result['val1'] != 1 && $result['val2'] != 1 && $result['val1'] * $result['val2'] < 21 ) {
		$result['oper'] = 'x'; 
		$result['ans'] = $result['val1'] * $result['val2'];
	}
	elseif ( $result['val1'] > ( $result['val2'] + 1 ) ) {
		$result['oper'] = '-'; 
		$result['ans'] = $result['val1'] - $result['val2'];
	}
	else {
		$result['oper'] = '+';
		$result['ans'] = $result['val1'] + $result['val2'];
	}
	$result['text'] = sprintf('%d %s %d = ', $result['val1'], $result['oper'], $result['val2']);
	return $result;
}


function wppa_get_imgevents($type = '', $id = '', $no_popup = false, $idx = '' ) {
global $wppa;
global $wppa_opt;
global $wpdb;

	$result = '';
	$perc = '';
	if ( $type == 'thumb' || $type=='film' ) {
		if ($wppa_opt['wppa_use_thumb_opacity'] || $wppa_opt['wppa_use_thumb_popup']) {
			
			if ($wppa_opt['wppa_use_thumb_opacity']) {
				$perc = $wppa_opt['wppa_thumb_opacity'];
				$result = ' onmouseout="jQuery(this).fadeTo(400, ' . $perc/100 . ')" onmouseover="jQuery(this).fadeTo(400, 1.0);';
			} else {
				$result = ' onmouseover="';
			}

			if ( $type == 'film' && $wppa_opt['wppa_film_hover_goto'] ) {
				$result .= 'wppaGotoFilmNoMove('.$wppa['master_occur'].', '.$idx.');';
			}

			if ( ! $no_popup && $wppa_opt['wppa_use_thumb_popup'] ) {
				if ( $wppa_opt['wppa_thumb_linktype'] != 'lightbox' ) {
				
					$name = $wppa_opt['wppa_popup_text_name'] ? wppa_get_photo_name($id) : '';
					$name = esc_js($name);
				
					$desc = $wppa_opt['wppa_popup_text_desc'] ? wppa_get_photo_desc($id) : '';
					if ( $wppa_opt['wppa_popup_text_desc_strip'] ) $desc = wppa_strip_tags($desc);
					$desc = esc_js($desc);

					$rating = $wppa_opt['wppa_popup_text_rating'] ? wppa_get_rating_by_id($id) : '';
					if ( $rating && $wppa_opt['wppa_show_rating_count'] ) $rating .= ' ('.wppa_get_rating_count_by_id($id).')';
					$rating = esc_js($rating);
					
					if ( $wppa_opt['wppa_popup_text_ncomments'] ) $ncom = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_COMMENTS."` WHERE `photo` = %s AND `status` = 'approved'", $id));
					else $ncom = '0';
					if ( $ncom ) {
						if ( $ncom == '1') $ncom = __a('1 Comment'); 
						else $ncom = sprintf(__a('%s Comments'), $ncom); 
					}
					else $ncom = '';
					$ncom = esc_js($ncom);

					$result .= 'wppaPopUp(' . $wppa['master_occur'] . ', this, ' . $id . ', \'' . $name . '\', \'' . $desc . '\', \'' . $rating . '\', \'' . $ncom . '\');" ';
				}
				else {
					// Popup and lightbox on thumbs are incompatible. skip popup.
					$result .= '" ';
				}
			}
			else $result .= '" ';
		}
	}
	elseif ($type == 'cover') {
		if ($wppa_opt['wppa_use_cover_opacity']) {
			$perc = $wppa_opt['wppa_cover_opacity'];
			$result = ' onmouseover="jQuery(this).fadeTo(400, 1.0)" onmouseout="jQuery(this).fadeTo(400, ' . $perc/100 . ')" ';
		}
	}		
	return $result;
}

function wppa_onpage($type = '', $counter, $curpage) {
global $wppa;

	$pagesize = wppa_get_pagesize($type);
	if ($pagesize == '0') {			// Pagination off
		if ($curpage == '1') return true;	
		else return false;
	}
	$cnt = $counter - 1;
	$crp = $curpage - 1;
	if (floor($cnt / $pagesize) == $crp) return true;
	return false;
}

function wppa_get_pagesize($type = '') {
global $wppa_opt;

	if ($type == 'albums') return $wppa_opt['wppa_album_page_size'];
	if ($type == 'thumbs') return $wppa_opt['wppa_thumb_page_size'];
	return '0';
}

function wppa_deep_stristr($string, $tokens) {
global $wppa_stree;
	$string = stripslashes($string);
	$tokens = stripslashes($tokens);
	// Explode tokens into search tree
	if (!isset($wppa_stree)) {
		// sanitize search token string
		$tokens = trim($tokens);
		while (strstr($tokens, ', ')) $tokens = str_replace(', ', ',', $tokens);
		while (strstr($tokens, ' ,')) $tokens = str_replace(' ,', ',', $tokens);
		while (strstr($tokens, '  ')) $tokens = str_replace('  ', ' ', $tokens);
		while (strstr($tokens, ',,')) $tokens = str_replace(',,', ',', $tokens);
		// to level explode
		if (strstr($tokens, ',')) {
			$wppa_stree = explode(',', $tokens);
		}
		else {
			$wppa_stree[0] = $tokens;
		}
		// bottom level explode
		for ($idx = 0; $idx < count($wppa_stree); $idx++) {
			if (strstr($wppa_stree[$idx], ' ')) {
				$wppa_stree[$idx] = explode(' ', $wppa_stree[$idx]);
			}
		}
	}
	// Check the search criteria
	foreach ($wppa_stree as $branch) {
		if (is_array($branch)) {
			if (wppa_and_stristr($string, $branch)) return true;
		}
		else {
			if (stristr($string, $branch)) return true;
		}
	}
	return false;
}

function wppa_and_stristr($string, $branch) {
	foreach ($branch as $leaf) {
		if (!stristr($string, $leaf)) return false;
	}
	return true;
}

function wppa_get_slide_frame_style() {
global $wppa;
global $wppa_opt;
	
	$fs = $wppa_opt['wppa_fullsize'];
	$cs = $wppa_opt['wppa_colwidth'];
	if ($cs == 'auto') {
		$cs = $fs;
		$wppa['auto_colwidth'] = true;
	}
	$result = '';
	$gfs = (is_numeric($wppa['fullsize']) && $wppa['fullsize'] > '0') ? $wppa['fullsize'] : $fs;
	
	$gfh = floor($gfs * $wppa_opt['wppa_maxheight'] / $wppa_opt['wppa_fullsize']);
	
	if ($wppa['in_widget'] == 'ss' && $wppa['in_widget_frame_height'] > '0') $gfh = $wppa['in_widget_frame_height'];
	
// for bbb:
$wppa['slideframewidth'] = $gfs;
$wppa['slideframeheight'] = $gfh;	
	
	if ($wppa['portrait_only']) {
		$result = 'width: ' . $gfs . 'px;';	// No height
	}
	else {
		if (wppa_page('oneofone')) {
			$imgattr = getimagesize(wppa_get_image_path($wppa['single_photo']));
			$h = floor($gfs * $imgattr[1] / $imgattr[0]);
			$result .= 'height: ' . $h . 'px;';
		}
		elseif ($wppa['auto_colwidth']) {
			$result .= ' height: ' . $gfh . 'px;';
		}
		elseif ($wppa['ss_widget_valign'] != '' && $wppa['ss_widget_valign'] != 'fit') {
			$result .= ' height: ' . $gfh . 'px;'; 
		}
		elseif ($wppa_opt['wppa_fullvalign'] == 'default') {
			$result .= 'min-height: ' . $gfh . 'px;'; 
		}
		else {
			$result .= 'height: ' . $gfh . 'px;'; 
		}
		$result .= 'width: ' . $gfs . 'px;';
	}
	
	$hor = $wppa_opt['wppa_fullhalign'];
	if ($gfs == $fs) {
		if ($fs != $cs) {
			switch ($hor) {
			case 'left':
				$result .= 'margin-left: 0px;';
				break;
			case 'center':
				$result .= 'margin-left: ' . floor(($cs - $fs) / 2) . 'px;';
				break;
			case 'right':
				$result .= 'margin-left: ' . ($cs - $fs) . 'px;';
				break;
			}
		}
	}
	// Margin bottom
	if ( $wppa_opt['wppa_box_spacing'] ) {
		$result .= 'margin-bottom: ' . $wppa_opt['wppa_box_spacing'] . 'px;';
	}

	return $result;
}

function wppa_get_thumb_frame_style($glue = false, $film = '') {
global $wppa_opt;
global $wppa;
global $wppaerrmsgxxx;
global $album;

	// Comten alt display?
	$com_alt = $wppa['is_comten'] && wppa_switch('wppa_comten_alt_display') && ! $wppa['in_widget'] && ! $film;

	$alt = !$film && is_array($album) && $album['alt_thumbsize'] == 'yes' ? '_alt' : '';

	$tfw = $wppa_opt['wppa_tf_width'.$alt];
	$tfh = $wppa_opt['wppa_tf_height'.$alt];
	$mgl = $wppa_opt['wppa_tn_margin'];
	if ($film == 'film' && $wppa['in_widget']) {
		$tfw /= 2;
		$tfh /= 2;
		$mgl /= 2;
	}
	$mgl2 = floor($mgl / '2');
	if ($film == '' && $wppa_opt['wppa_thumb_auto']) {
		$area = wppa_get_box_width() + $tfw;	// Area for n+1 thumbs
		$n_1 = floor($area / ($tfw + $mgl));
		if ( $n_1 == '0' ) {
			if ( ! $wppaerrmsgxxx ) wppa_dbg_msg('Misconfig. thumbnail area too small. Areasize = '.wppa_get_box_width().' tfwidth = '.$tfw.' marg= '.$mgl);
			$n_1 = '1';
			$wppaerrmsgxxx = true;	// err msg given
		}
		$mgl = floor($area / $n_1) - $tfw;	
	}
	if (is_numeric($tfw) && is_numeric($tfh)) {
		$result = 'width: '.$tfw.'px; height: '.$tfh.'px; margin-left: '.$mgl.'px; margin-top: '.$mgl2.'px; margin-bottom: '.$mgl2.'px;';
		if ($glue && $wppa_opt['wppa_film_show_glue'] && $wppa_opt['wppa_slide_wrap']) {
			$result .= 'padding-right:'.$mgl.'px; border-right: 2px dotted gray;';
		}
	}
	else $result = '';
	
	// Alt comment?
	if ( $com_alt ) {
		$result = 'width: '.wppa_get_container_width().'px; margin-left: 4px; margin-top: 2px; margin-bottom: 2px;';
	}

	return $result;
}

function wppa_get_container_width($netto = false) {
global $wppa;
global $wppa_opt;

	if (is_numeric($wppa['fullsize']) && $wppa['fullsize'] > '0') {
		$result = $wppa['fullsize'];
	}
	else {
		$result = $wppa_opt['wppa_colwidth'];
		if ($result == 'auto') {
			$result = '640';
			$wppa['auto_colwidth'] = true;
		}
	}
	if ($netto) {
	$result -= 12; // 2*padding
	$result -= 2 * $wppa_opt['wppa_bwidth'];
	}
	return $result;
}

function wppa_get_thumbnail_area_width() {
	$result = wppa_get_container_width();
	$result -= wppa_get_thumbnail_area_delta();
	return $result;
}

function wppa_get_thumbnail_area_delta() {
global $wppa_opt;

//	$result = 7 + 2 * $wppa_opt['wppa_bwidth'];	// 7 = .thumbnail_area padding-left
		$result = 12 + 2 * $wppa_opt['wppa_bwidth'];	// experimental
	return $result;
}

function wppa_get_container_style() {
global $wppa;
global $wppa_opt;

	$result = '';
	
	// See if there is space for a margin
	$marg = false;
	if (is_numeric($wppa['fullsize'])) {
		$cw = $wppa_opt['wppa_colwidth'];
		if (is_numeric($cw)) {
			if ($cw > ($wppa['fullsize'] + 10)) {
				$marg = '10px;';
			}
		}
	}
	
	if (!$wppa['in_widget']) $result .= 'clear: both; ';
	$ctw = wppa_get_container_width();
	if ($wppa['auto_colwidth']) {
		if (is_feed()) {
			$result .= 'width:'.$ctw.'px;';
		}
	}
	else {
		$result .= 'width:'.$ctw.'px;';
	}
	
//	if ($wppa['align'] == '' || 
	if ($wppa['align'] == 'left') {
		$result .= 'float: left;';
		if ($marg) $result .= 'margin-right: '.$marg;
	}
	elseif ($wppa['align'] == 'center') $result .= 'display: block; margin-left: auto; margin-right: auto;'; 
	elseif ($wppa['align'] == 'right') {
		$result .= 'float: right;';
		if ($marg) $result .= 'margin-left: '.$marg;
	}
	
	$result .= ' padding:0;';	//4.7.5
	
	return $result;
}

function wppa_get_curpage() {
global $wppa;

	if (wppa_get_get('page')) {
		if ($wppa['in_widget']) {
			$oc = wppa_get_get('woccur', '1');
			$curpage = $wppa['widget_occur'] == $oc ? wppa_get_get('page') : '1';
		}
		else {
			$oc = wppa_get_get('occur', '1');
			$curpage = $wppa['occur'] == $oc ? wppa_get_get('page') : '1';
		}
	}
	else $curpage = '1';
	return $curpage;
}

function wppa_container($action) {
global $wppa;	
global $wppa_opt;			
global $wppa_version;			// The theme version (wppa_theme.php)
global $wppa_alt;
global $wppa_microtime;
global $wppa_microtime_cum;
global $wppa_err_displayed;
global $wppa_loadtime;
global $wppa_initruntimetime;
global $wppa_numqueries;

	if (is_feed()) return;		// Need no container in RSS feeds
	
	if ($action == 'open') {
		// Open the container
		$wppa['out'] .= wppa_nltab('init');
		if ( ! $wppa['ajax'] ) {
			$wppa['out'] .= '<!-- Start WPPA+ generated code -->';
			$wppa['out'] .= wppa_nltab('+').'<div id="wppa-container-'.$wppa['master_occur'].'" style="'.wppa_get_container_style().'" class="wppa-container wppa-container-'.$wppa['master_occur'].' wppa-rev-'.$wppa['revno'].' wppa-prevrev-'.$wppa_opt['wppa_prevrev'].' wppa-theme-'.$wppa_version.' wppa-api-'.$wppa['api_version'].'" >';
		}
//		$wppa['out'] .= wppa_nltab().'<a name="wppa-loc-'.$wppa['master_occur'].'"></a>';

		// Spinner for Ajax
		if ( $wppa_opt['wppa_allow_ajax'] ) {
			if ( ! $wppa['in_widget'] ) {
				$wppa['out'] .= wppa_nltab('+').'<div class="wppa-container-'.$wppa['master_occur'].'" style="text-align:center; width:'.wppa_get_container_width().'px;" ><img id="wppa-ajax-spin-'.$wppa['master_occur'].'" src="'.wppa_get_imgdir().'loader.gif" style="box-shadow:none; z-index:1010; position:absolute; margin-top: 200px; margin-left:-32px; display:none;"></div>';
			}
		}
		
		// Start timer if in debug mode
		if ($wppa['debug']) {
			$wppa_microtime = - microtime(true);
			$wppa_numqueries = - get_num_queries();
			wppa_dbg_q('init');
		}
		if ( $wppa['master_occur'] == '1' ) {
			wppa_dbg_msg('Plugin load time :'.substr($wppa_loadtime,0,5).'s.');
			wppa_dbg_msg('Init runtime time :'.substr($wppa_initruntimetime,0,5).'s.');
			wppa_dbg_msg('Num queries before wppa :'.get_num_queries());
		}
		
		/* Check if wppa.js and jQuery are present */
		if ( ! $wppa_err_displayed && ( WPPA_DEBUG || isset($_GET['wppa-debug']) || WP_DEBUG ) ) {
			$wppa['out'] .= '<script type="text/javascript">/* <![CDATA[ */';
				$wppa['out'] .= "if (typeof(_wppaSlides) == 'undefined') alert('There is a problem with your theme. The file wppa.js is not loaded when it is expected (Errloc = wppa_container).');";
				$wppa['out'] .= "if (typeof(jQuery) == 'undefined') alert('There is a problem with your theme. The jQuery library is not loaded when it is expected (Errloc = wppa_container).');";
			$wppa['out'] .= "/* ]]> */</script>";
			$wppa_err_displayed = true;
		} 
		/* Check if init is properly done */
		if ( ! $wppa_opt['wppa_fullsize'] ) {
			$wppa['out'] .= '<script type="text/javascript">/* <![CDATA[ */';
				$wppa['out'] .= "alert('The initialisation of wppa+ is not complete yet. You will probably see division by zero errors. Please run Photo Albums -> Settings admin page Table VIII-A1. (Errloc = wppa_container).');";
			$wppa['out'] .= "/* ]]> */</script>";
		}
		
		// Nonce field check for rating security 
		if ($wppa['master_occur'] == '1') { 				
			if (wppa_get_get('rating')) {
				$nonce = wppa_get_get('nonce');
				$ok = wp_verify_nonce($nonce, 'wppa-check');
				if ($ok) {
					wppa_dbg_msg('Rating nonce ok');
					if ( ! is_user_logged_in() ) sleep(2);
				}
				else die(__a('<b>ERROR: Illegal attempt to enter a rating.</b>'));
			}
		}
		
		// Nonce field check for comment security 
		if ($wppa['master_occur'] == '1') { 			
			if (wppa_get_post('comment')) {
				$nonce = wppa_get_post('nonce');
				$ok = wp_verify_nonce($nonce, 'wppa-check');
				if ($ok) {
					wppa_dbg_msg('Comment nonce ok');
					if ( ! is_user_logged_in() ) sleep(2);
				}
				else die(__a('<b>ERROR: Illegal attempt to enter a comment.</b>'));
			}		
		}
	
		$wppa['out'] .= wppa_nltab().wp_nonce_field('wppa-check' , 'wppa-nonce', false, false);

		if (wppa_page('oneofone')) $wppa['portrait_only'] = true;
		$wppa_alt = 'alt';

		// Javascript occurrence dependant stuff
		$wppa['out'] .= wppa_nltab().'<script type="text/javascript">';
			$wppa['out'] .= wppa_nltab('+').'/* <![CDATA[ */';
			// $wppa['auto_colwidth'] is set by the filter or by wppa_albums in case called directly
			// $wppa_opt['wppa_colwidth'] is the option setting
			// script or call has precedence over option setting
			// so: if set by script or call: auto, else if set by option: auto
			$auto = false;
			$contw = wppa_get_container_width();
//echo 'auto_colwith='.$wppa['auto_colwidth'].' wppa-colwith='.$wppa['wppa_colwidth'].', c-style='.wppa_get_container_style(). '<br/>';
			if ($wppa['auto_colwidth']) $auto = true;
			elseif ($wppa_opt['wppa_colwidth'] == 'auto') $auto = true;
			elseif ($contw > 0 && $contw < 1.0 ) $auto = true;
			
//echo 'occur:'.$wppa['master_occur'].', auto='.$auto;
			if ($auto) {
				$wppa['out'] .= wppa_nltab().'wppaAutoColumnWidth['.$wppa['master_occur'].'] = true;';
				if ($contw > 0 && $contw < 1.0) $wppa['out'] .= wppa_nltab().'wppaAutoColumnFrac['.$wppa['master_occur'].'] = '.$contw.';';
				else $wppa['out'] .= wppa_nltab().'wppaAutoColumnFrac['.$wppa['master_occur'].'] = 1.0;';
				$wppa['out'] .= wppa_nltab().'wppaColWidth['.$wppa['master_occur'].'] = 0;';
			}
			else {
				$wppa['out'] .= wppa_nltab().'wppaAutoColumnWidth['.$wppa['master_occur'].'] = false;';
				$wppa['out'] .= wppa_nltab().'wppaColWidth['.$wppa['master_occur'].'] = '.wppa_get_container_width().';';
			}
			$wppa['out'] .= wppa_nltab().'wppaTopMoc = '.$wppa['master_occur'].';';
			
			// Aspect ratio and fullsize
			if ( $wppa['in_widget'] == 'ss' && is_numeric($wppa['in_widget_frame_width']) && $wppa['in_widget_frame_width'] > '0' ) {
				$asp = $wppa['in_widget_frame_height'] / $wppa['in_widget_frame_width'];
				$fls = $wppa['in_widget_frame_width'];
			}
			else {
				$asp = $wppa_opt['wppa_maxheight'] / $wppa_opt['wppa_fullsize'];
				$fls = $wppa_opt['wppa_fullsize'];
			}
			$wppa['out'] .= wppa_nltab().'wppaAspectRatio['.$wppa['master_occur'].'] = '.$asp.';';
			$wppa['out'] .= wppa_nltab().'wppaFullSize['.$wppa['master_occur'].'] = '.$fls.';';
//echo 'occ='.$wppa['master_occur'].' asp='.$asp.' fls='.$fls.' clw='.wppa_get_container_width().' auto='.$auto.'<br />';
			// last minute change: fullvalign with border needs a height correction in slideframe
			if ( $wppa_opt['wppa_fullimage_border_width'] != '' && ! $wppa['in_widget'] ) {
				$delta = (1 + $wppa_opt['wppa_fullimage_border_width']) * 2;
			} else $delta = 0;
			$wppa['out'] .= wppa_nltab().'wppaFullFrameDelta['.$wppa['master_occur'].'] = '.$delta.';';

			// last minute change: script %%size != default colwidth
			$temp = wppa_get_container_width() - ( 2*6 + 2*36 + 2*$wppa_opt['wppa_bwidth']);
			if ($wppa['in_widget']) $temp = wppa_get_container_width() - ( 2*6 + 2*18 + 2*$wppa_opt['wppa_bwidth']);
			$wppa['out'] .= wppa_nltab().'wppaFilmStripLength['.$wppa['master_occur'].'] = '.$temp.';';

			// last minute change: filmstrip sizes and related stuff. In widget: half size.		
			$temp = $wppa_opt['wppa_tf_width'] + $wppa_opt['wppa_tn_margin'];
			if ($wppa['in_widget']) $temp /= 2;
			$wppa['out'] .= wppa_nltab().'wppaThumbnailPitch['.$wppa['master_occur'].'] = '.$temp.';';
			$temp = $wppa_opt['wppa_tn_margin'] / 2;
			if ($wppa['in_widget']) $temp /= 2;
			$wppa['out'] .= wppa_nltab().'wppaFilmStripMargin['.$wppa['master_occur'].'] = '.$temp.';';
			$temp = 2*6 + 2*42 + 2*$wppa_opt['wppa_bwidth'];
			if ($wppa['in_widget']) $temp = 2*6 + 2*21 + 2*$wppa_opt['wppa_bwidth'];
			$wppa['out'] .= wppa_nltab().'wppaFilmStripAreaDelta['.$wppa['master_occur'].'] = '.$temp.';';
			if ($wppa['in_widget']) $wppa['out'] .= wppa_nltab().'wppaIsMini['.$wppa['master_occur'].'] = true;';
			else $wppa['out'] .= wppa_nltab().'wppaIsMini['.$wppa['master_occur'].'] = false;';
			
			$target = false;
			if ( $wppa['in_widget'] == 'ss' && $wppa_opt['wppa_sswidget_blank'] ) $target = true;
			if ( !$wppa['in_widget'] && $wppa_opt['wppa_slideshow_blank'] ) $target = true;
			if ( $target ) $wppa['out'] .= wppa_nltab().'wppaSlideBlank['.$wppa['master_occur'].'] = true;';
			else $wppa['out'] .= wppa_nltab().'wppaSlideBlank['.$wppa['master_occur'].'] = false;';
			
		$wppa['out'] .= wppa_nltab('-').'/* ]]> */';
		$wppa['out'] .= wppa_nltab().'</script>';
		
	}
	elseif ($action == 'close')	{
		if (wppa_page('oneofone')) $wppa['portrait_only'] = false;
		if (!$wppa['in_widget']) $wppa['out'] .= ('<div style="clear:both;"></div>');
		
		// Add diagnostic <p> if debug is 1
		if ( $wppa['debug'] == '1' && $wppa['master_occur'] == '1' ) $wppa['out'] .= wppa_nltab().'<p id="wppa-debug-'.$wppa['master_occur'].'" style="font-size:9px; color:#070; line-size:12px;" ></p>';	

		if ( ! $wppa['ajax'] ) {
			$wppa['out'] .= wppa_nltab('-').'</div><!-- wppa-container-'.$wppa['master_occur'].' -->';
			$wppa['out'] .= wppa_nltab().'<!-- End WPPA+ generated code -->';
		}
						
		if ($wppa['debug']) {
			$laptim = $wppa_microtime + microtime(true);
			$wppa_numqueries += get_num_queries();
			if (!is_numeric($wppa_microtime_cum)) $wppa_mcrotime_cum = '0';
			$wppa_microtime_cum += $laptim;
			wppa_dbg_msg('Time elapsed occ '.$wppa['master_occur'].':'.substr($laptim, 0, 5).'s. Tot:'.substr($wppa_microtime_cum, 0, 5).'s.');
			wppa_dbg_msg('Nuber of queries occ '.$wppa['master_occur'].':'.$wppa_numqueries);
			wppa_dbg_q('print');
		}
	}
	else {
		$wppa['out'] .= "\n".'<span style="color:red;">Error, wppa_container() called with wrong argument: '.$action.'. Possible values: \'open\' or \'close\'</span>';
	}
}

function wppa_album_list($action) {
global $wppa;
global $cover_count;
global $cover_count_key;

	if ($action == 'open') {
		$cover_count = '0';
		$cover_count_key = 'l';
		$wppa['out'] .= wppa_nltab('+').'<div id="wppa-albumlist-'.$wppa['master_occur'].'" class="albumlist">';
	}
	elseif ($action == 'close') {
		$wppa['out'] .= wppa_nltab('-').'</div><!-- wppa-albumlist-'.$wppa['master_occur'].' -->';
	}
	else {
		$wppa['out'] .= wppa_nltab().'<span style="color:red;">Error, wppa_albumlist() called with wrong argument: '.$action.'. Possible values: \'open\' or \'close\'</span>';
	}
}

function wppa_thumb_list($action) {
global $wppa;
global $cover_count;
global $cover_count_key;
global $album;

	if ($action == 'open') {
		$cover_count = '0';
		$cover_count_key = 'l';
		$wppa['out'] .= wppa_nltab('+').'<div id="wppa-thumblist-'.$wppa['master_occur'].'" class="thumblist">';
		if ( is_array($album) ) wppa_bump_viewcount('album', $album['id']);
	}
	elseif ($action == 'close') {
		$wppa['out'] .= wppa_nltab('-').'</div><!-- wppa-thumblist-'.$wppa['master_occur'].' -->';
	}
	else {
		$wppa['out'] .= wppa_nltab().'<span style="color:red;">Error, wppa_thumblist() called with wrong argument: '.$action.'. Possible values: \'open\' or \'close\'</span>';
	}
}


function wppa_get_npages($type, $array) {
global $wppa;
global $wppa_opt;

	$aps = wppa_get_pagesize('albums');	
	$tps = wppa_get_pagesize('thumbs'); 
	$arraycount = is_array($array) ? count($array) : '0';
	$result = '0';
	if ($type == 'albums') {
		if ($aps != '0') {
			$result = ceil($arraycount / $aps); 
		} 
		elseif ($tps != '0') {
			if ( $arraycount ) $result = '1'; 
			else $result = '0';
		}
	}
	elseif ($type == 'thumbs') {
		if ($wppa['is_cover'] == '1') {		// Cover has no thumbs: 0 pages
			$result = '0';
		} 
		elseif ( $arraycount <= $wppa_opt['wppa_min_thumbs'] 
					&& ! $wppa['src'] 
					&& ! $wppa['is_tag'] 
					&& ! $wppa['is_related']
					&& ! $wppa['is_upldr']
				) {	// Less than treshold and not searching and not from tagcloud: 0
			$result = '0';
		}
		elseif ($tps != '0') {
			$result = ceil($arraycount / $tps);	// Pag on: compute
		}
		else {
			$result = '1';								// Pag off: all fits on 1
		}
	}
	return $result;
}
		
function wppa_thumb_ascover() {
global $thumb;
global $wppa;
global $wppa_opt;
global $wppa_alt;
global $cover_count_key;
global $thlinkmsggiven;

	// Get the album info
	wppa_cache_album($thumb['album']);

	$path 		= wppa_get_thumb_path($thumb['id']); 
	$imgattr_a 	= wppa_get_imgstyle_a($path, $wppa_opt['wppa_smallsize'], '', 'cover'); 
	$events 	= is_feed() ? '' : wppa_get_imgevents('cover'); 
	$src 		= wppa_get_thumb_url( $thumb['id'], '', $imgattr_a['width'], $imgattr_a['height'] ); 
	$link 		= wppa_get_imglnk_a('thumb', $thumb['id']);

	if ($link) {
		$href = $link['url'];
		$title = $link['title'];
		$target = $link['target'];
	}
	else {
		$href = '';
		$title = '';
		$target = '';
	}
	
	if ( ! $link['is_url'] ) {
		if ( ! $thlinkmsggiven ) wppa_dbg_msg('Title link may not be an event in thumbs as covers.');
		$href = '';
		$title = '';
		$thlinkmsggiven = true;
	}

	$photo_left = $wppa_opt['wppa_thumbphoto_left'];
	$class_asym = 'wppa-asym-text-frame-'.$wppa['master_occur'];
	
	$style = __wcs('wppa-box').__wcs('wppa-'.$wppa_alt);
	if (is_feed()) $style .= ' padding:7px;';
	
	$wid = wppa_get_cover_width('thumb');
	$style .= 'width: '.$wid.'px;';	
	if ( $cover_count_key == 'm' ) {
		$style .= 'margin-left: 8px;';
	}
	elseif ( $cover_count_key == 'r' ) {
		$style .= 'float:right;';
	}
	else {
		$style .= 'clear:both;';
	}
	wppa_step_covercount('thumb');

	$wppa['out'] .= wppa_nltab('+').'<div id="thumb-'.$thumb['id'].'-'.$wppa['master_occur'].'" class="thumb wppa-box wppa-cover-box wppa-cover-box-'.$wppa['master_occur'].' wppa-'.$wppa_alt.'" style="'.$style.'" >';

		if ($photo_left) {
			wppa_the_thumbascoverphoto($src, $photo_left, $link, $imgattr_a, $events);
		}
		
		$textframestyle = wppa_get_text_frame_style($photo_left, 'thumb');
		$wppa['out'] .= wppa_nltab('+').'<div id="thumbtext_frame_'.$thumb['id'].'_'.$wppa['master_occur'].'" class="wppa-text-frame-'.$wppa['master_occur'].' wppa-text-frame thumbtext-frame '.$class_asym.'" '.$textframestyle.'>';
			$wppa['out'] .= wppa_nltab('+').'<h2 class="wppa-title" style="clear:none;">';
				$wppa['out'] .= wppa_nltab().'<a href="'.$href.'" target="'.$target.'" title="'.$title.'" style="'.__wcs('wppa-title').'" >'.wppa_qtrans(stripslashes($thumb['name'])).'</a>';
			$wppa['out'] .= wppa_nltab('-').'</h2>';
			$desc =  wppa_get_photo_desc($thumb['id']);
			if ( $thumb['status'] == 'pending' ) $desc .= wppa_moderate_links('thumb', $thumb['id']);
			$wppa['out'] .= wppa_nltab().'<p class="wppa-box-text wppa-black" style="'.__wcs('wppa-box-text').__wcs('wppa-black').'" >'.$desc.'</p>';
		$wppa['out'] .= wppa_nltab('-').'</div>';
//		$wppa['out'] .= wppa_nltab().'<div style="clear:both;"></div>';
		
		if (!$photo_left) {
			wppa_the_thumbascoverphoto($src, $photo_left, $link, $imgattr_a, $events);
		}
		
	$wppa['out'] .= wppa_nltab('-').'</div><!-- thumb-'.$thumb['id'].'-'.$wppa['master_occur'].' -->';
	if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
}

function wppa_the_thumbascoverphoto($src, $photo_left, $link, $imgattr_a, $events) {
global $thumb;
global $wppa;

	$href      = $link['url'];
	$title     = $link['title'];
	$imgattr   = $imgattr_a['style'];
	$imgwidth  = $imgattr_a['width'];
	$imgheight = $imgattr_a['height'];
	$frmwidth  = $imgwidth + '10';	// + 2 * 1 border + 2 * 4 padding
		
	if ($src != '') {
	
	if ($wppa['in_widget']) $photoframestyle = 'style="text-align:center;"';
	else $photoframestyle = $photo_left ? 'style="float:left; margin-right:5px;width:'.$frmwidth.'px;"' : 'style="float:right; margin-left:5px;width:'.$frmwidth.'px;"';
		$wppa['out'] .= wppa_nltab('+').'<div id="thumbphoto_frame_'.$thumb['id'].'_'.$wppa['master_occur'].'" class="thumbphoto-frame" '.$photoframestyle.'>';
		if ( $link['is_url'] ) {
			$wppa['out'] .= wppa_nltab('+').'<a href="'.$href.'" title="'.$title.'">';
				$wppa['out'] .= wppa_nltab().'<img src="'.$src.'" alt="'.$title.'" class="image wppa-img" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.__wcs('wppa-img').$imgattr.'" '.$events.' />';
			$wppa['out'] .= wppa_nltab('-').'</a>';
		}
		else {
			$wppa['out'] .= wppa_nltab().'<img src="'.$src.'" alt="'.$title.'" class="image wppa-img" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.__wcs('wppa-img').$imgattr.'" '.$events.' onclick="'.$href.'" />';
		}
			
		$wppa['out'] .= wppa_nltab('-').'</div>';
	}
}

function wppa_thumb_default() {
global $thumb;
global $wppa;
global $wppa_opt;
global $album;
global $wpdb;

	// Get the album info
	wppa_cache_album($thumb['album']);
	
	// Comten alt display?
	$com_alt = $wppa['is_comten'] && wppa_switch('wppa_comten_alt_display') && ! $wppa['in_widget'];
	
	$src       = wppa_get_thumb_path($thumb['id']); 
	// $maxsize = $wppa['in_widget'] ? $wppa_opt['wppa_comment_size'] : $wppa_opt['wppa_thumbsize'];
	// there is also:                  $wppa_opt['wppa_topten_size'] 
	// So, what to do with a WPPA+ Text widget ???
	$alt 		= $album['alt_thumbsize'] == 'yes' ? '_alt' : '';
	$imgattr_a 	= wppa_get_imgstyle_a($src, $wppa_opt['wppa_thumbsize'.$alt], 'optional', 'thumb'); 

	$imgstyle  	= $imgattr_a['style'];
	$imgwidth  	= $imgattr_a['width'];
	$imgheight 	= $imgattr_a['height'];
	if ( $com_alt ) {
		$imgwidth = $wppa_opt['wppa_comten_alt_thumbsize'];
		$imgheight = round($imgwidth * $imgattr_a['height'] / $imgattr_a['width']);
		$imgstyle .= 'float:left; margin:0 10px 8px 0;width:'.$imgwidth.'px; height:'.$imgheight.'px;';
	}
	$cursor	   	= $imgattr_a['cursor'];

	$x = $com_alt ? 'margin-right:20px;' : '';	// Extra style for comalt display
	
	$w = $imgattr_a['width'];
	$h = $imgattr_a['height'];
	if ( wppa_switch('wppa_use_thumb_popup') ) {
		if ( $w > $h ) { 	// Landscape
			$w = $wppa_opt['wppa_popupsize'];
			$h = round( $w * $imgattr_a['height'] / $imgattr_a['width'] );
		}
		else { 				// Portrait
			$h = $wppa_opt['wppa_popupsize'];
			$w = round( $h * $imgattr_a['width'] / $imgattr_a['height'] );
		}
	}

	$url       	= wppa_get_thumb_url( $thumb['id'], '', $w, $h ); 
	$events    	= wppa_get_imgevents('thumb', $thumb['id']); 
	$imgalt		= wppa_get_imgalt($thumb['id']);	// returns something like ' alt="Any text" '
	$title = esc_attr(wppa_get_photo_name($thumb['id']));
	
	if (is_feed()) {
		$imgattr_a = wppa_get_imgstyle_a($src, '100', '4', 'thumb');
		$style = $imgattr_a['style'];
		$wppa['out'] .= wppa_nltab().'<a href="'.get_permalink().'"><img src="'.$url.'" '.$imgalt.' title="'.$title.'" style="'.$style.'" /></a>';
		return;
	}
	
	// Open the thumbframe
//	if ( $com_alt ) $wppa['out'] .= wppa_nltab('+').'<div id="tn_frame_wrap_'.$thumb['id'].'_'.$wppa['master_occur'].'" style="width:100%" >';
	$cls = 'thumbnail-frame thumbnail-frame-'.$wppa['master_occur'].' thumbnail-frame-photo-'.$thumb['id'];
	if ( $com_alt ) $cls = 'thumbnail-frame-comalt thumbnail-frame-comalt-'.$wppa['master_occur'].' thumbnail-frame-photo-'.$thumb['id'];
	$wppa['out'] .= wppa_nltab('+').'<div id="thumbnail_frame_'.$thumb['id'].'_'.$wppa['master_occur'].'" class="'.$cls.'" style="'.wppa_get_thumb_frame_style().'" >';

	if ($wppa['is_topten']) {
		$no_album = !$wppa['start_album'];
		if ($no_album) $tit = __a('View the top rated photos'); else $tit = esc_attr(wppa_qtrans(stripslashes($thumb['description'])));
		$link = wppa_get_imglnk_a('thumb', $thumb['id'], '', $tit, '', $no_album);
	}
	else $link = wppa_get_imglnk_a('thumb', $thumb['id']);
	
	if ($link) {
		if ( $link['is_url'] ) {	// is url
			if ( $wppa_opt['wppa_allow_ajax'] 
				&& $wppa_opt['wppa_thumb_linktype'] == 'photo' 							// linktype must be to slideshow image
				&& $wppa_opt['wppa_thumb_linkpage'] == '0'								// same page/post
				&& ! $wppa_opt['wppa_thumb_blank']										// not on a new tab
				&& ! ( $wppa_opt['wppa_thumb_overrule'] && $thumb['linkurl'] )			// no ( ps overrule set AND link present )
				&& ! $wppa['is_topten']													// no topten selection
				&& ! $wppa['is_lasten']													// no lasten selection
				&& ! $wppa['is_comten']													// no comten selection
				&& ! $wppa['is_featen']
				&& ! $wppa['is_tag']													// no tag selection
//				&& ! $wppa['is_upldr']
				&& ! $wppa['src']														// no search
				&& ( wppa_is_int($wppa['start_album']) || $wppa['start_album'] == '' )	// no set of albums
				) 
			{ 	// Ajax	possible

				// Get the album info
//				wppa_cache_album($thumb['album']);
			
				$onclick = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_slideshow_url_ajax($wppa['start_album'], '0').'&amp;wppa-photo='.$thumb['id']."', '".wppa_convert_to_pretty(wppa_get_slideshow_url($wppa['start_album'], '0')."&amp;wppa-photo=".$thumb['id'])."')";

				$wppa['out'] .= wppa_nltab('+').'<a style="position:static;" class="thumb-img" id="x-'.$thumb['id'].'-'.$wppa['master_occur'].'">';
					$wppa['out'] .= wppa_nltab().'<img onclick="'.$onclick.'" id="i-'.$thumb['id'].'-'.$wppa['master_occur'].'" src="'.$url.'" '.$imgalt.' title="'.$title.'" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.$imgstyle.' cursor:pointer;" '.$events.' />';
				$wppa['out'] .= wppa_nltab('-').'</a>';
				
			}
			else { 	// non ajax
//echo 'B';
				$wppa['out'] .= wppa_nltab('+').'<a style="position:static;" href="'.$link['url'].'" target="'.$link['target'].'" class="thumb-img" id="x-'.$thumb['id'].'-'.$wppa['master_occur'].'">';
					$wppa['out'] .= wppa_nltab().'<img id="i-'.$thumb['id'].'-'.$wppa['master_occur'].'" src="'.$url.'" '.$imgalt.' title="'.$title.'" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.$imgstyle.' cursor:pointer;" '.$events.' />';
				$wppa['out'] .= wppa_nltab('-').'</a>';
			}
		}
		elseif ( $link['is_lightbox'] ) {
			$title = wppa_get_lbtitle('thumb', $thumb['id']);
			$wppa['out'] .= wppa_nltab('+').'<a href="'.$link['url'].'" target="'.$link['target'].'" rel="'.$wppa_opt['wppa_lightbox_name'].'[occ'.$wppa['master_occur'].']" title="'.$title.'" class="thumb-img" id="x-'.$thumb['id'].'-'.$wppa['master_occur'].'">';
				$wppa['out'] .= wppa_nltab().'<img id="i-'.$thumb['id'].'-'.$wppa['master_occur'].'" src="'.$url.'" '.$imgalt.' title="'.wppa_zoom_in().'" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.$imgstyle.$cursor.'" '.$events.' />';
			$wppa['out'] .= wppa_nltab('-').'</a>';
		}
		else {	// is onclick
			$wppa['out'] .= wppa_nltab('+').'<div onclick="'.$link['url'].'" class="thumb-img" id="x-'.$thumb['id'].'-'.$wppa['master_occur'].'">';
				$wppa['out'] .= wppa_nltab().'<img id="i-'.$thumb['id'].'-'.$wppa['master_occur'].'" src="'.$url.'" '.$imgalt.' title="'.$title.'" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.$imgstyle.' cursor:pointer;" '.$events.' />';
			$wppa['out'] .= wppa_nltab('-').'</div>';
			$wppa['out'] .= wppa_nltab('+').'<script type="text/javascript">';
			$wppa['out'] .= wppa_nltab().'/* <![CDATA[ */';
			$wppa['out'] .= wppa_nltab().'wppaPopupOnclick['.$thumb['id'].'] = "'.$link['url'].'";';
			$wppa['out'] .= wppa_nltab('-').'/* ]]> */';
			$wppa['out'] .= wppa_nltab().'</script>';
		}
	}
	else {	// no link
		if ($wppa_opt['wppa_use_thumb_popup']) {
			$wppa['out'] .= wppa_nltab('+').'<div id="x-'.$thumb['id'].'-'.$wppa['master_occur'].'">';
				$wppa['out'] .= wppa_nltab().'<img src="'.$url.'" '.$imgalt.' title="'.$title.'" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.$imgstyle.'" '.$events.' />';
			$wppa['out'] .= wppa_nltab('-').'</div>';
		}
		else {
			$wppa['out'] .= wppa_nltab().'<img src="'.$url.'" '.$imgalt.' title="'.$title.'" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.$imgstyle.'" '.$events.' />';
		}
	}

	// Comten alt display?
	if ( $com_alt ) {
		$wppa['out'] .= '<div class="wppa-com-alt-'.$wppa['master_occur'].'" style="height:'.$imgheight.'px; overflow:auto; margin: 0 20px 8px 10px; border:1px solid '.$wppa_opt['wppa_bcolor_alt'].';" >';
			$limit = '1'; //$wppa_opt['wppa_comten_alt_limit'] ? $wppa_opt['wppa_comten_alt_limit'] : '1000';
			$comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_COMMENTS."` WHERE `id` = %s LIMIT 1", $thumb['com_id']), ARRAY_A);
			if ( $comments ) foreach ( $comments as $com ) {
				$wppa['out'] .= '<h6 style="font-size:10px;line-height:12px;font-weight:bold;padding:0 0 0 6px;margin:0;float:left;">'.$com['user'].' '.__a('wrote:').
								' '.wppa_get_time_since($com['timestamp']).'</h6><br />'.
								'<p style="font-size:10px;line-height:12px;padding:0 0 0 6px;text-align:left;margin:0;">'.html_entity_decode(convert_smilies($com['comment'])).'</p>';
			}
		$wppa['out'] .= '</div>';
	}

if ( ! $com_alt ) {	
	
// Single button voting system	
if ( $wppa_opt['wppa_rating_max'] == '1' && wppa_switch('wppa_vote_thumb') ) {
	$mylast  = $wpdb->get_row($wpdb->prepare( 'SELECT * FROM `'.WPPA_RATING.'` WHERE `photo` = %s AND `user` = %s ORDER BY `id` DESC LIMIT 1', $thumb['id'], wppa_get_user() ), ARRAY_A ); 
	$buttext = $mylast ? __($wppa_opt['wppa_voted_button_text']) : __($wppa_opt['wppa_vote_button_text']);
	$wppa['out'] .= '<input id="wppa-vote-button-'.$wppa['master_occur'].'-'.$thumb['id'].'" class="wppa-vote-button-thumb" style="margin:0;" type="button" onclick="wppaVoteThumb('.$wppa['master_occur'].', '.$thumb['id'].')" value="'.$buttext.'" />';
}

	if ( $wppa['src'] || ( ( $wppa['is_comten'] || $wppa['is_topten'] || $wppa['is_lasten'] || $wppa['is_featen'] ) && $wppa['start_album'] != $thumb['album'] ) ) {
		$wppa['out'] .= wppa_nltab().'<div class="wppa-thumb-text" style="'.$x.__wcs('wppa-thumb-text').'" >(<a href="'.wppa_get_album_url($thumb['album']).'">'.stripslashes(__(wppa_get_album_name($thumb['album']))).'</a>)</div>';
	}

	$new = wppa_is_photo_new($thumb['id']);		
	if ($wppa_opt['wppa_thumb_text_name'] || $new) {
		$wppa['out'] .= wppa_nltab().'<div class="wppa-thumb-text" style="'.$x.__wcs('wppa-thumb-text').'" >';
			if ($wppa_opt['wppa_thumb_text_name']) $wppa['out'] .= wppa_get_photo_name($thumb['id'], $wppa_opt['wppa_thumb_text_owner']); // wppa_qtrans(stripslashes($thumb['name']));
			if ($new) $wppa['out'] .= '&nbsp;<img src="'.WPPA_URL.'/images/new.png" title="New!" class="wppa-thumbnew" style="border:none; margin:0; padding:0; box-shadow:none; " />';
		$wppa['out'] .= '</div>';
	}
	
	if ( wppa_switch('wppa_share_on_thumbs') ) {
		$wppa['out'] .= wppa_nltab().'<div class="wppa-thumb-text" style="'.$x.__wcs('wppa-thumb-text').'" >';
			$wppa['out'] .= wppa_get_share_html('thumb');
		$wppa['out'] .= '</div>';
	}
	
	if ($wppa_opt['wppa_thumb_text_desc'] || $thumb['status'] == 'pending') {
		$desc = '';
		if ( $thumb['status'] == 'pending' ) {
			$desc .= wppa_moderate_links('thumb', $thumb['id']);
//			$desc .= '<span style="color:red" class="wppa-approve-'.$thumb['id'].'" >'.__a('Awaiting moderation').'</span>';
//			if ( current_user_can('wppa_moderate') ) {
//				$desc .= wppa_approve_photo_button($thumb['id']);
//				$desc .' ';
//				$desc .= wppa_moderate_photo_button($thumb['id']);
//			}
		}
		$desc .= wppa_get_photo_desc($thumb['id'], $wppa_opt['wppa_allow_foreign_shortcodes_thumbs']);
		$wppa['out'] .= wppa_nltab().'<div class="wppa-thumb-text" style="'.$x.__wcs('wppa-thumb-text').'" >'.$desc.'</div>';
	}
	
	if ($wppa_opt['wppa_thumb_text_rating']) {
		$rating = wppa_get_rating_by_id($thumb['id']);
		if ( $rating && $wppa_opt['wppa_show_rating_count'] ) $rating .= ' ('.wppa_get_rating_count_by_id($thumb['id']).')';
		$wppa['out'] .= wppa_nltab().'<div class="wppa-thumb-text" style="'.$x.__wcs('wppa-thumb-text').'" >'.$rating.'</div>';
	}
	
	if ( $wppa_opt['wppa_thumb_text_viewcount'] ) {
		$wppa['out'] .= wppa_nltab().'<div class="wppa-thumb-text" style="clear:both;'.$x.__wcs('wppa-thumb-text').'" >'.__('Views:', 'wppa').' '.$thumb['views'].'</div>';
	}
} // if ! $com_alt		
	// Close the thumbframe
	$wppa['out'] .= wppa_nltab('-').'</div><!-- #thumbnail_frame_'.$thumb['id'].'_'.$wppa['master_occur'].' -->';

//	if ( $com_alt ) $wppa['out'] .= wppa_nltab('-').'</div><!-- wrapper -->';
}	


function wppa_get_mincount() {
global $wppa;
global $wppa_opt;

	if ( $wppa['src'] ) return '0';
	if ( $wppa['is_topten'] ) return '0';
	if ( $wppa['is_lasten'] ) return '0';
	if ( $wppa['is_comten'] ) return '0';
	if ( $wppa['is_featen'] ) return '0';
	if ( $wppa['is_tag'] ) return '0';
	if ( $wppa['is_upldr'] ) return '0';

	return $wppa_opt['wppa_min_thumbs'];
}


function wppa_popup() {
global $wppa;

	$wppa['out'] .= wppa_nltab().'<div id="wppa-popup-'.$wppa['master_occur'].'" class="wppa-popup-frame wppa-thumb-text" style="'.__wcs('wppa-thumb-text').'" onmouseout="wppaPopDown('.$wppa['master_occur'].');" ></div>';
	$wppa['out'] .= wppa_nltab().'<div style="clear:both;"></div>';
}

function wppa_run_slidecontainer($type = '') {
global $wppa;
global $wppa_opt;
global $thumbs;
global $thumb;

//	if ( $wppa['is_filmonly'] ) return;
	
	$c = is_array($thumbs) ? count($thumbs) : '0';
	wppa_dbg_msg('Running slidecontainer type '.$type.' with '.$c.' elements in thumbs, is_single='.$wppa['is_single']);

	if ( $wppa['is_single'] && is_feed() ) {	// process feed for single image slideshow here, normal slideshow uses filmthumbs
		$style_a = wppa_get_fullimgstyle_a($wppa['start_photo']);
		$style   = $style_a['style'];
		$width   = $style_a['width'];
		$height  = $style_a['height'];
		$imgalt	 = wppa_get_imgalt($wppa['start_photo']);
		$wppa['out'] .= wppa_nltab().'<a href="'.get_permalink().'"><img src="'.wppa_get_photo_url( $wppa['start_photo'], '', $width, $height ).'" style="'.$style.'" width="'.$width.'" height="'.$height.'" '.$imgalt.'/></a>';
		return;
	}
	elseif ($type == 'slideshow') {
		// Find slideshow start method
		switch ($wppa_opt['wppa_start_slide']) {
			case 'run':
				$startindex = -1;
				break;
			case 'still':
				$startindex = 0;
				break;
			case 'norate':
				$startindex = -2;
				break;
			default:
				echo 'Unexpected error unknown wppa_start_slide in wppa_run_slidecontainer';
		}
		// A requested photo id overrules the method. $startid >0 is requested photo id, -1 means: no id requested
//?		if (wppa_get_get('photo')) $startid = wppa_get_get('photo');	// Still slideshow at photo id $startid
//?		else 
		if ( $wppa['start_photo'] ) $startid = $wppa['start_photo'];
		else $startid = -1;
		
		// Find album
//		if (wppa_get_get('album')) $alb = wppa_get_get('album');
//		else $alb = '';	// Album id is in $wppa['start_album']
		// Find thumbs
//		$thumbs = wppa_get_thumbs($alb);
		// Create next ids
		$ix = 0;
		if ( $thumbs ) while ( $ix < count($thumbs) ) {
			if ( $ix == (count($thumbs)-1) ) $thumbs[$ix]['next_id'] = $thumbs[0]['id'];
			else $thumbs[$ix]['next_id'] = $thumbs[$ix + 1]['id'];
			$ix ++;
		}
		// Produce scripts for slides
		$index = 0;
		if ( $thumbs ) {
			$t = -microtime(true);
			$wppa['out'] .= wppa_nltab('+').'<script type="text/javascript">';
			$wppa['out'] .= wppa_nltab().'/* <![CDATA[ */';
				
				foreach ( $thumbs as $thumb ) {
					if ( $wppa_opt['wppa_next_on_callback'] ) {
						$wppa['out'] .= wppa_nltab().'wppaStoreSlideInfo(' . wppa_get_slide_info($index, $thumb['id'], $thumb['next_id']) . ');';
					}
					else {
						$wppa['out'] .= wppa_nltab().'wppaStoreSlideInfo(' . wppa_get_slide_info($index, $thumb['id']) . ');';
					}
					if ($startid == $thumb['id']) $startindex = $index;	// Found the requested id, put the corresponding index in $startindex
					$index++;
				}
				
			$wppa['out'] .= wppa_nltab('-').'/* ]]> */';
			$wppa['out'] .= wppa_nltab().'</script>';
			$t += microtime(true);
			wppa_dbg_msg('SlideInfo took '.$t.' seconds.');
		}
		
		$wppa['out'] .= wppa_nltab('+').'<script type="text/javascript">';
			$wppa['out'] .= '/* <![CDATA[ */';
		
			// How to start if slideonly
			if ($wppa['is_slideonly']) {
				if ( $wppa_opt['wppa_start_slideonly'] ) $startindex = -1;	// There are no navigations, so start running, overrule everything
				else $startindex = 0;
			}
			
			// Vertical align
			if ( $wppa['is_slideonly'] ) { 
				$ali = $wppa['ss_widget_valign'] ? $wppa['ss_widget_valign'] : $ali = 'fit';
				$wppa['out'] .= wppa_nltab().'wppaFullValign['.$wppa['master_occur'].'] = "'.$ali.'";';
			}
			else {
				$wppa['out'] .= wppa_nltab().'wppaFullValign['.$wppa['master_occur'].'] = "'.$wppa_opt['wppa_fullvalign'].'";';
			}
			
			// Horizontal align
			$wppa['out'] .= wppa_nltab().'wppaFullHalign['.$wppa['master_occur'].'] = "'.$wppa_opt['wppa_fullhalign'].'";';
			
			// Portrait only ?
			if ($wppa['portrait_only']) {
				$wppa['out'] .= wppa_nltab().'wppaPortraitOnly['.$wppa['master_occur'].'] = true;';
			}
			
			// Start command with appropriate $startindex: -2 = at norate, -1 run from first, >=0 still at index
			// If we use lightbox on slideshow, wait for documen.ready, if we do not use lightbox, go immediately.
			if ( $wppa_opt['wppa_slideshow_linktype'] == 'lightbox' || $wppa_opt['wppa_slideshow_linktype'] == 'lightboxsingle' || $wppa_opt['wppa_film_linktype'] == 'lightbox' ) {
				$wppa['out'] .= wppa_nltab().'jQuery(document).ready(function() { wppaStartStop('.$wppa['master_occur'].', '.$startindex.'); });';
			}
			else {
				$wppa['out'] .= wppa_nltab().'wppaStartStop('.$wppa['master_occur'].', '.$startindex.');';
			}
		
		$wppa['out'] .= wppa_nltab('-').'/* ]]> */';
		$wppa['out'] .= wppa_nltab().'</script>';

	}
	else {
		$wppa['out'] .= wppa_nltab().'<span style="color:red;">Error, wppa_run_slidecontainer() called with wrong argument: '.$type.'. Possible values: \'single\' or \'slideshow\'</span>';
	}
}

function wppa_is_pagination() {
global $wppa;

	if ((wppa_get_pagesize('albums') == '0' && wppa_get_pagesize('thumbs') == '0') /* || $wppa['src'] */ ) return false;
	else return true;
}


function wppa_do_filmthumb($idx, $do_for_feed = false, $glue = false) {
global $wppa;
global $wppa_opt;
global $thumb;

	$src = wppa_get_thumb_path($thumb['id']); 
	$max_size = $wppa_opt['wppa_thumbsize'];
	if ($wppa['in_widget']) $max_size /= 2;
	$com_alt = $wppa['is_comten'] && wppa_switch('wppa_comten_alt_display') && ! $wppa['in_widget'];
	
	$imgattr_a = wppa_get_imgstyle_a($src, $max_size, 'optional', 'fthumb'); 
	$imgstyle  = $imgattr_a['style'];
	$imgwidth  = $imgattr_a['width'];
	$imgheight = $imgattr_a['height'];
	$cursor    = $imgattr_a['cursor'];
		
	$url = wppa_get_thumb_url($thumb['id'], '', $imgwidth, $imgheight); 
	$furl = str_replace('/thumbs', '', $url);
	$events = wppa_get_imgevents('film', $thumb['id'], 'nopopup', $idx); 
	$thumbname = esc_attr(wppa_qtrans($thumb['name']));
	
	$alt = wppa_qtrans($thumb['name']);
	$alt = preg_replace('/\.[^.]*$/', '', $alt);	// Remove file extension
	if ( strlen($alt) > 13 ) $alt = substr($alt, 0, 10).'...';
	$alt = esc_attr($alt);
	
//	$title = $thumbname;
	
	if ( $wppa_opt['wppa_film_linktype'] == 'lightbox' ) {
//		$title = esc_attr(wppa_zoom_in());
	}
	else {
		$events .= ' onclick="wppaGoto('.$wppa['master_occur'].', '.$idx.')"';
		$events .= ' ondblclick="wppaStartStop('.$wppa['master_occur'].', -1)"';
//		$title = esc_attr(__a('Double click to start/stop slideshow running'));
	}
	
	if (is_feed()) {
		if ($do_for_feed) {
			$style_a = wppa_get_imgstyle_a($src, '100', '4', 'thumb');
			$style = $style_a['style'];
			$wppa['out'] .= wppa_nltab().'<a href="'.get_permalink().'"><img src="'.$url.'" alt="'.$alt.'" title="'.$thumbname.'" style="'.$style.'" /></a>';
		}
	} else {
		// If !$do_for_feed: pre-or post-ambule. To avoid dup id change it in that case
		$tmp = $do_for_feed ? 'film' : 'pre';
		$style = $glue ? 'style="'.wppa_get_thumb_frame_style($glue, 'film').'"' : '';
		$wppa['out'] .= '
				<div id="'.$tmp.'_wppatnf_'.$thumb['id'].'_'.$wppa['master_occur'].'" class="thumbnail-frame" '.$style.' >';
		
		if ( $wppa_opt['wppa_film_linktype'] == 'lightbox' && $tmp == 'film' ) {
			$wppa['out'] .= '<a href="'.$furl.'" rel="'.$wppa_opt['wppa_lightbox_name'].'[occ'.$wppa['master_occur'].']" title="'.wppa_get_lbtitle('slide', $thumb['id']).'" >';
		}
		
			if ( $tmp == 'pre' && $wppa_opt['wppa_film_linktype'] == 'lightbox' ) $cursor = 'cursor:default;';
			if ( $tmp == 'film' && ! $com_alt && ! wppa_cdn() ) $wppa['out'] .= '<!--';
				$wppa['out'] .= '<img id="wppa-'.$tmp.'-'.$idx.'-'.$wppa['master_occur'].'" class="wppa-'.$tmp.'-'.$wppa['master_occur'].'" src="'.$url.'" alt="'.$alt.'" '. //title="'.$title.'" '.
					//width="'.$imgwidth.'" height="'.$imgheight.'" 
					'style="'.$imgstyle.$cursor.'" '.$events.' />';
			if ( $tmp == 'film' && ! $com_alt && ! wppa_cdn() ) $wppa['out'].='-->';
			
		if ( $wppa_opt['wppa_film_linktype'] == 'lightbox' && $tmp == 'film' ) {
			$wppa['out'] .= '</a>';
		}
		
		$wppa['out'] .= '</div>'; //<!-- #thumbnail_frame_'.$thumb['id'].'_'.$wppa['master_occur'].' -->';
	}
}

function wppa_get_preambule() {
global $wppa_opt;

	if ( ! $wppa_opt['wppa_slide_wrap'] ) return '0';
	$result = is_numeric($wppa_opt['wppa_colwidth']) ? $wppa_opt['wppa_colwidth'] : $wppa_opt['wppa_fullsize'];
	$result = ceil(ceil($result / $wppa_opt['wppa_thumbsize']) / 2 );
	return $result;
}

function wppa_dummy_bar($msg = '') {
global $wppa;

	$wppa['out'] .= wppa_nltab().'<div style="margin:4px 0; '.__wcs('wppa-box').__wcs('wppa-nav').'text-align:center;">'.$msg.'</div>';
}


function wppa_rating_count_by_id($id = '') {
global $wppa;

	$wppa['out'] .= wppa_get_rating_count_by_id($id);
}


function wppa_rating_by_id($id = '', $opt = '') {
global $wppa;

	$wppa['out'] .= wppa_get_rating_by_id($id, $opt);
}

function wppa_get_cover_width($type) {
global $wppa_opt;

	$conwidth = wppa_get_container_width();
	$cols = wppa_get_cover_cols($type);
	
	$result = floor(($conwidth - (8 * ($cols - 1))) / $cols);

	$result -= (2 * (7 + $wppa_opt['wppa_bwidth']));	// 2 * (padding + border)
	return $result;
}

function wppa_get_text_frame_style($photo_left, $type) {
global $wppa_opt;
global $wppa;

	if ($wppa['in_widget']) {
		$result = '';
	}
	else {
		if ( $type == 'thumb' ) {
			$width = wppa_get_cover_width($type);
			$width -= 13;	// margin
			$width -= 2; 	// border
			$width -= $wppa_opt['wppa_smallsize'];
			
			if ($photo_left) {
				$result = 'style="width:'.$width.'px; float:right;"';
			}
			else {
				$result = 'style="width:'.$width.'px; float:left;"';
			}
		}
		elseif ( $type == 'cover' ) {
			$width = wppa_get_cover_width($type);
			$photo_pos = $photo_left;
			switch ( $photo_pos ) {
				case 'left':
					$width -= 13;	// margin
					$width -= 2; 	// border
					$width -= $wppa_opt['wppa_smallsize'];
					$result = 'style="width:'.$width.'px; float:right;"';
					break;
				case 'right':
					$width -= 13;	// margin
					$width -= 2; 	// border
					$width -= $wppa_opt['wppa_smallsize'];
					$result = 'style="width:'.$width.'px; float:left;"';
					break;
				case 'top':
//					$width -= 13;
					$result = '';//'style="width:'.$width.'px;"';
					break;
				case 'bottom':
//					$width -= 13;
					$result = '';//'style="width:'.$width.'px;"';
					break;
				default:
					wppa_dbg_msg('Illegal $photo_pos in wppa_get_text_frame_style', 'red');
			}
		}
		else wppa_dbg_msg('Illegal $type in wppa_get_text_frame_style', 'red');
	}
	return $result;
}

function wppa_get_textframe_delta() {
global $wppa_opt;

	$delta = $wppa_opt['wppa_smallsize'];
	$delta += (2 * (7 + $wppa_opt['wppa_bwidth'] + 4) + 5 + 2);	// 2 * (padding + border + photopadding) + margin
	return $delta;
}

function wppa_step_covercount($type) {
global $cover_count;
global $cover_count_key;

	$key = 'm';
	$cols = wppa_get_cover_cols($type);
	$cover_count++;
	if ( $cover_count == $cols ) {
		$cover_count = '0'; // Row is full
		$key = 'l';
	}
	if ( $cover_count + '1' == $cols ) {
		$key = 'r';
	}
	$cover_count_key = $key;
}

function wppa_get_cover_cols($type) {
global $wppa;
global $wppa_opt;

	$conwidth = wppa_get_container_width();
	
	$cols = ceil( $conwidth / $wppa_opt['wppa_max_cover_width'] );
	
	// Exceptions
	if ($wppa['auto_colwidth']) $cols = '1';
	if (($type == 'cover') && ($wppa['album_count'] < '2')) $cols = '1';
	if (($type == 'thumb') && ($wppa['thumb_count'] < '2')) $cols = '1';
	return $cols;
}

function wppa_get_box_width() {
global $wppa_opt;

	$result = wppa_get_container_width();
	$result -= 12;	// 2 * padding
	$result -= 2 * $wppa_opt['wppa_bwidth'];
	return $result;
}

function wppa_get_box_delta() {
	return wppa_get_container_width() - wppa_get_box_width();
}



function wppa_force_balance_pee($xtext) {

	$text = $xtext;	// Make a local copy
	$done = false;
	$temp = strtolower($text);
	
	// see if this chunk ends in <p> in which case we remove that in stead of appending a </p>
	$len = strlen($temp);
	if ($len > 3) {
		if (substr($temp, $len - 3) == '<p>') {
			$text = substr($text, 0, $len - 3);
			$temp = strtolower($text);
		}
	}
	
	$opens = substr_count($temp, '<p');
	$close = substr_count($temp, '</p');
	// append a close
	if ($opens > $close) {	
		$text .= '</p>';	
	}
	// prepend an open
	if ($close > $opens) {	
		$text = '<p>'.$text;
	}
	return $text;
}

// This is a nice simple function
function wppa_output($txt) {
global $wppa;

	$wppa['out'] .= $txt;
	return;
}

function wppa_mphoto() {
global $wppa;
global $wppa_opt;

	$width 		= wppa_get_container_width();
	$height 	= floor($width / wppa_get_ratio($wppa['single_photo']));
	$usethumb	= wppa_use_thumb_file($wppa['single_photo'], $width, $height);
	$src 		= $usethumb ? wppa_get_thumb_url( $wppa['single_photo'], '', $width, $height ) : wppa_get_photo_url( $wppa['single_photo'], '', $width, $height );
	
	if ( ! $wppa['in_widget'] ) wppa_bump_viewcount('photo', $wppa['single_photo']);
/**/
	$autocol = $wppa['auto_colwidth'] || ($width > 0 && $width < 1.0);
	if ( $autocol ) {
		$wppa['out'] .= wppa_nltab().'<script type="text/javascript">';
			$wppa['out'] .= wppa_nltab('+').'/* <![CDATA[ */';
				$wppa['out'] .= wppa_nltab().'wppaAutoColumnWidth['.$wppa['master_occur'].'] = true;';
				$wppa['out'] .= wppa_nltab().'wppaColWidth['.$wppa['master_occur'].'] = 0;';
				if ( $width > 1.0 ) $wppa['out'] .= wppa_nltab().'wppaAutoColumnFrac['.$wppa['master_occur'].'] = 1;';
				else $wppa['out'] .= wppa_nltab().'wppaAutoColumnFrac['.$wppa['master_occur'].'] = '.$width.';';
				$wppa['out'] .= wppa_nltab().'wppaTopMoc = '.$wppa['master_occur'].';';
			$wppa['out'] .= wppa_nltab('-').'/* ]]> */';
		$wppa['out'] .= wppa_nltab().'</script>';
	}
/**/
	$captwidth = $width + '10';
	$wppa['out'] .= '<div id="wppa-container-'.$wppa['master_occur'].'" class="wppa-mphoto-'.$wppa['master_occur'].' wp-caption';
		if ($wppa['align'] != '') $wppa['out'] .= ' align'.$wppa['align'];
	$wppa['out'] .='" style="width: '.$captwidth.'px">';

		// The link
		$link = wppa_get_imglnk_a('mphoto', $wppa['single_photo']);
		if ($link) {
			if ( $link['is_lightbox'] ) {
				$lbtitle = wppa_get_lbtitle('mphoto', $wppa['single_photo']);
				$wppa['out'] .= wppa_nltab('+').'<a href="'.$link['url'].'" title="'.$lbtitle.'" rel="'.$wppa_opt['wppa_lightbox_name'].'" target="'.$link['target'].'" class="thumb-img" id="a-'.$wppa['single_photo'].'-'.$wppa['master_occur'].'">';
			}
			else {
				$wppa['out'] .= wppa_nltab('+').'<a href="'.$link['url'].'" title="'.$link['title'].'" target="'.$link['target'].'" class="thumb-img" id="a-'.$wppa['single_photo'].'-'.$wppa['master_occur'].'">';
			}
		}
		
		// The image
		$title = $link ? $link['title'] : esc_attr(stripslashes(wppa_get_photo_name($wppa['single_photo'])));
		if ( $link['is_lightbox'] ) {
			$style = ' cursor:url('.wppa_get_imgdir().$wppa_opt['wppa_magnifier'].'),pointer;';
			$title = wppa_zoom_in();
		}
		else {
			$style = '';
		}
		$wppa['out'] .= wppa_nltab().'<img src="'.$src.'" alt="" style="'.$style.'" class="size-medium wppa-mphoto wppa-mimg-'.$wppa['master_occur'].'" title="'.$title.'" width="'.$width.'" height="'.$height.'" />';
		if ($link) {
			$wppa['out'] .= wppa_nltab('-').'</a>';
		}
		
		// The subtitle
		$wppa['out'] .= '<p class="wp-caption-text">'.wppa_get_photo_desc($wppa['single_photo']).'</p>';
		
		// The share buttons
		if ( wppa_switch('wppa_share_on_mphoto') ) {
			$wppa['out'] .= wppa_get_share_html( 'mphoto', false );
		}

		// Add diagnostic <p> if debug is 1
		if ( $wppa['debug'] == '1' && $wppa['master_occur'] == '1' ) $wppa['out'] .= wppa_nltab().'<p id="wppa-debug-'.$wppa['master_occur'].'" style="font-size:9px; color:#070; line-size:12px;" ></p>';	

	$wppa['out'] .= '</div>';
}	

// Like mphoto but without the caption and with the fullsize background/border
function wppa_sphoto() {
global $wppa;
global $wppa_opt;

	$width 		= wppa_get_container_width();
	$height 	= floor($width / wppa_get_ratio($wppa['single_photo']));
	$usethumb	= wppa_use_thumb_file($wppa['single_photo'], $width, $height);
	$src 		= $usethumb ? wppa_get_thumb_url( $wppa['single_photo'], '', $width, $height ) : wppa_get_photo_url( $wppa['single_photo'], '', $width, $height );

	if ( ! $wppa['in_widget'] ) wppa_bump_viewcount('photo', $wppa['single_photo']);

	$autocol = $wppa['auto_colwidth'] || ($width > 0 && $width < 1.0);
	if ( $autocol ) {
		$wppa['out'] .= wppa_nltab().'<script type="text/javascript">';
			$wppa['out'] .= wppa_nltab('+').'/* <![CDATA[ */';
				$wppa['out'] .= wppa_nltab().'wppaAutoColumnWidth['.$wppa['master_occur'].'] = true;';
				$wppa['out'] .= wppa_nltab().'wppaColWidth['.$wppa['master_occur'].'] = 0;';
				if ( $width > 1.0 ) $wppa['out'] .= wppa_nltab().'wppaAutoColumnFrac['.$wppa['master_occur'].'] = 1;';
				else $wppa['out'] .= wppa_nltab().'wppaAutoColumnFrac['.$wppa['master_occur'].'] = '.$width.';';
				$wppa['out'] .= wppa_nltab().'wppaTopMoc = '.$wppa['master_occur'].';';
			$wppa['out'] .= wppa_nltab('-').'/* ]]> */';
		$wppa['out'] .= wppa_nltab().'</script>';
	}
	
	// The pseudo container
	$wppa['out'] .= '<div id="wppa-container-'.$wppa['master_occur'].'" class="';
		if ($wppa['align'] != '') $wppa['out'] .= ' align'.$wppa['align'];
		$wppa['out'] .= ' wppa-sphoto-'.$wppa['master_occur'];
	$wppa['out'] .='" style="width: '.$width.'px">';

		// The link
		$link = wppa_get_imglnk_a('sphoto', $wppa['single_photo']);
		if ($link) {
			if ( $link['is_lightbox'] ) {
				$lbtitle = wppa_get_lbtitle('sphoto', $wppa['single_photo']);
				$wppa['out'] .= wppa_nltab('+').'<a href="'.$link['url'].'" title="'.$lbtitle.'" rel="'.$wppa_opt['wppa_lightbox_name'].'" target="'.$link['target'].'" class="thumb-img" id="a-'.$wppa['single_photo'].'-'.$wppa['master_occur'].'" >';
			}
			else {
				$wppa['out'] .= wppa_nltab('+').'<a href="'.$link['url'].'" title="'.$link['title'].'" target="'.$link['target'].'" class="thumb-img" id="a-'.$wppa['single_photo'].'-'.$wppa['master_occur'].'" >';
			}
		}
		
		// The image
		$wppa['portrait_only'] = true;
		$fis 	= wppa_get_fullimgstyle_a($wppa['single_photo']);
		$width	= $fis['width'];
		$height	= $fis['height'];
		$style	= $fis['style'];
	//	$cursor = $fis['cursor'];
		
		$title = $link ? esc_attr($link['title']) : esc_attr(stripslashes(wppa_get_photo_name($wppa['single_photo'])));
		if ( $link['is_lightbox'] ) {
			$style .= ' cursor:url('.wppa_get_imgdir().$wppa_opt['wppa_magnifier'].'),pointer;';
			$title = wppa_zoom_in();
		}
		
		$wppa['out'] .= wppa_nltab().'<img src="'.$src.'" alt="" '.
										'class="size-medium wppa-sphoto wppa-simg-'.$wppa['master_occur'].'" '.
										'title="'.$title.'" ';
										if ( $autocol ) {
		$wppa['out'] .=						'style="'.$style.'" ';
										}
										else {
		$wppa['out'] .=						'style="'.$style.'" '.
											'width="'.$width.'" height="'.$height.'" ';
										}
		$wppa['out'] .=					'/>';

		// The link
		if ($link) {
			$wppa['out'] .= wppa_nltab('-').'</a>';
		}
	
		// Add diagnostic <p> if debug is 1
		if ( $wppa['debug'] == '1' && $wppa['master_occur'] == '1' ) $wppa['out'] .= wppa_nltab().'<p id="wppa-debug-'.$wppa['master_occur'].'" style="font-size:9px; color:#070; line-size:12px;" ></p>';	

	// The pseudo container
	$wppa['out'] .= '</div>';
}	

// returns aspect ratio (w/h), or 1 on error
function wppa_get_ratio($id = '') {
global $wpdb;

	if (!is_numeric($id)) return '1';	// Not 0 to prevent divide by zero
	
	$photo = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . WPPA_PHOTOS . " WHERE id=%s LIMIT 1", $id ), ARRAY_A );
	wppa_dbg_q('Q51');
	if (!$photo) return '1';
	
	$file = wppa_get_photo_path($id);
	if (is_file($file)) $image_attr = getimagesize($file);
	else return '1';
	
	if ($image_attr[1] != 0) return $image_attr[0]/$image_attr[1];	// width/height
	return '1';
}

function wppa_get_imglnk_a($wich, $photo, $lnk = '', $tit = '', $onc = '', $noalb = false, $album = '') {
global $wppa;
global $wppa_opt;
global $thumb;
global $wpdb;

	// make sure the photo data ia available
	wppa_cache_thumb($photo);
	if ( ! $thumb ) return false;
	
	// For cases it is appropriate...
	if ( ( $wich == 'sphoto'     && $wppa_opt['wppa_sphoto_overrule'] ) ||
		 ( $wich == 'mphoto'     && $wppa_opt['wppa_mphoto_overrule'] ) ||
		 ( $wich == 'thumb'      && $wppa_opt['wppa_thumb_overrule'] ) ||
		 ( $wich == 'topten'     && $wppa_opt['wppa_topten_overrule'] ) ||
		 ( $wich == 'featen'	 && $wppa_opt['wppa_featen_overrule'] ) ||
		 ( $wich == 'lasten'     && $wppa_opt['wppa_lasten_overrule'] ) ||
		 ( $wich == 'sswidget'   && $wppa_opt['wppa_sswidget_overrule'] ) ||
		 ( $wich == 'potdwidget' && $wppa_opt['wppa_potdwidget_overrule'] ) ||
		 ( $wich == 'coverimg'   && $wppa_opt['wppa_coverimg_overrule'] ) ||
		 ( $wich == 'comten'	 && $wppa_opt['wppa_comment_overrule'] ) ||
		 ( $wich == 'slideshow'  && $wppa_opt['wppa_slideshow_overrule'] ) ||
		 ( $wich == 'tnwidget' 	 && $wppa_opt['wppa_thumbnail_widget_overrule'] )) {
		// Look for a photo specific link
		
		$data = $thumb;
//		if ( isset($thumb['id']) && $thumb['id'] == $photo ) {
//			$data = $thumb;
//			wppa_dbg_q('G53');
//		}
//		else {
//			$data = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM '.WPPA_PHOTOS.' WHERE id=%s LIMIT 1', $photo ) , ARRAY_A );
//			wppa_dbg_q('Q53');
//		}
		if ($data) {
			// If it is there...
			if ($data['linkurl'] != '') {
				// Use it. It superceeds other settings
				$result['url'] = esc_attr($data['linkurl']);
				$result['title'] = esc_attr(wppa_qtrans(stripslashes($data['linktitle'])));
				$result['is_url'] = true;
				$result['is_lightbox'] = false;
				$result['onclick'] = '';
				$result['target'] = $data['linktarget'];
				return $result;
			}
		}
	}
	
	$result['target'] = '_self';
	$result['title'] = '';
	switch ($wich) {
		case 'sphoto':
			$type = $wppa_opt['wppa_sphoto_linktype'];
			$page = $wppa_opt['wppa_sphoto_linkpage'];
			if ($page == '0') $page = '-1';
			if ($wppa_opt['wppa_sphoto_blank']) $result['target'] = '_blank';
			break;
		case 'mphoto':
			$type = $wppa_opt['wppa_mphoto_linktype'];
			$page = $wppa_opt['wppa_mphoto_linkpage'];
			if ($page == '0') $page = '-1';
			if ($wppa_opt['wppa_mphoto_blank']) $result['target'] = '_blank';
			break;
		case 'thumb':
			$type = $wppa_opt['wppa_thumb_linktype'];
			$page = $wppa_opt['wppa_thumb_linkpage'];
			if ($wppa_opt['wppa_thumb_blank']) $result['target'] = '_blank';
			break;
		case 'topten':
			$type = $wppa_opt['wppa_topten_widget_linktype'];
			$page = $wppa_opt['wppa_topten_widget_linkpage'];
			if ($page == '0') $page = '-1';
			if ($wppa_opt['wppa_topten_blank']) $result['target'] = '_blank';
			break;
		case 'featen':
			$type = $wppa_opt['wppa_featen_widget_linktype'];
			$page = $wppa_opt['wppa_featen_widget_linkpage'];
			if ($page == '0') $page = '-1';
			if ($wppa_opt['wppa_featen_blank']) $result['target'] = '_blank';
			break;
		case 'lasten':
			$type = $wppa_opt['wppa_lasten_widget_linktype'];
			$page = $wppa_opt['wppa_lasten_widget_linkpage'];
			if ($page == '0') $page = '-1';
			if ($wppa_opt['wppa_lasten_blank']) $result['target'] = '_blank';
			break;
		case 'comten':
			$type = $wppa_opt['wppa_comment_widget_linktype'];
			$page = $wppa_opt['wppa_comment_widget_linkpage'];
			if ($page == '0') $page = '-1';
			if ($wppa_opt['wppa_comment_blank']) $result['target'] = '_blank';
			break;
		case 'sswidget':
			$type = $wppa_opt['wppa_slideonly_widget_linktype'];
			$page = $wppa_opt['wppa_slideonly_widget_linkpage'];
			if ($page == '0') $page = '-1';
			if ($wppa_opt['wppa_sswidget_blank']) $result['target'] = '_blank';
			break;
		case 'potdwidget':
			$type = $wppa_opt['wppa_widget_linktype'];
			$page = $wppa_opt['wppa_widget_linkpage'];
			if ($page == '0') $page = '-1';
			if ($wppa_opt['wppa_potd_blank']) $result['target'] = '_blank';
			break;
		case 'coverimg':
			$type = $wppa_opt['wppa_coverimg_linktype'];
			$page = $wppa_opt['wppa_coverimg_linkpage'];
			if ($page == '0') $page = '-1';
			if ($wppa_opt['wppa_coverimg_blank']) $result['target'] = '_blank';
			break;
		case 'tnwidget':
			$type = $wppa_opt['wppa_thumbnail_widget_linktype'];
			$page = $wppa_opt['wppa_thumbnail_widget_linkpage'];
			if ($page == '0') $page = '-1';
			if ($wppa_opt['wppa_thumbnail_widget_blank']) $result['target'] = '_blank';
			break;
		case 'slideshow':
			$type = $wppa_opt['wppa_slideshow_linktype'];	//'';
			$page = $wppa_opt['wppa_slideshow_linkpage'];
			$result['url'] = '';
			if ( $type == 'lightbox' || $type == 'lightboxsingle' || $type == 'file' ) { 
				$result['title'] = wppa_zoom_in();
				$result['target'] = '';
				return $result;
			}
			if ( $type == 'none' ) return;
			// Continue for 'single' 
			break;
		case 'albwidget':
			$type = $wppa_opt['wppa_album_widget_linktype'];
			$page = $wppa_opt['wppa_album_widget_linkpage'];
			if ($page == '0') $page = '-1';
			if ($wppa_opt['wppa_album_widget_blank']) $result['target'] = '_blank';
			break;
		default:
			return false;
			break;
	}
if ( ! $album ) {
	$album = $wppa['start_album'];
}
	if ( $album == '' ) {
		$album = wppa_get_album_id_by_photo_id($photo);
	}
	if ( is_numeric($album) ) {
		$album_name = wppa_get_album_name($album);
	}
	else $album_name = '';
	
if ( $wich == 'comten' ) $album='0';

if ( $wppa['is_tag'] ) $album='0';
if ( $wppa['is_upldr'] ) $album='0';
	
	if ( $photo ) {
		$photo_name = wppa_get_photo_name($photo);
	}
	else $photo_name = '';
	
/*	
	$photo_name = false;
	
	if (is_array($thumb)) {
		if ($thumb['id'] == $photo) {
			$photo_name = wppa_qtrans(stripslashes($thumb['name']));
		}
	}
	
	if (!$photo_name) $photo_name = wppa_get_photo_name($photo);
*/

	$photo_name_js = esc_js($photo_name);
	$photo_name = esc_attr($photo_name);
	
	if ( $photo ) {
		$photo_desc = esc_attr(wppa_get_photo_desc($photo));
	}
	else $photo_desc = '';

	$title = __($photo_name);	// Patch 4.3.3, translate patch 4.7.13
	
	$result['onclick'] = '';	// Init
	switch ($type) {
		case 'none':		// No link at all
			return false;
			break;
		case 'file':		// The plain file
			$siz = getimagesize( wppa_get_photo_path( $photo ) );
			$result['url'] = wppa_get_photo_url( $photo, '', $siz['0'], $siz['1'] );
			$result['title'] = $title; 
			$result['is_url'] = true;
			$result['is_lightbox'] = false;
			return $result;
			break;
		case 'lightbox':
		case 'lightboxsingle':
			$siz = getimagesize( wppa_get_photo_path( $photo ) );
			$result['url'] = wppa_get_photo_url( $photo, '', $siz['0'], $siz['1'] );
			$result['title'] = $title; 
			$result['is_url'] = false;
			$result['is_lightbox'] = true;
			return $result;
		case 'widget':		// Defined at widget activation
			$result['url'] = $wppa['in_widget_linkurl'];
			$result['title'] = esc_attr($wppa['in_widget_linktitle']);
			$result['is_url'] = true;
			$result['is_lightbox'] = false;
			return $result;
			break;
		case 'album':		// The albums thumbnails
		case 'content':		// For album widget
			switch ($page) {
				case '-1':
					return false;
					break;
				case '0':
					if ($noalb) {
						$result['url'] = wppa_get_permalink().'wppa-album=0&amp;wppa-cover=0';
						$result['title'] = ''; // $album_name;
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					else {
						$result['url'] = wppa_get_permalink().'wppa-album='.$album.'&amp;wppa-cover=0';
						$result['title'] = $album_name;
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					break;
				default:
					if ($noalb) {
						$result['url'] = wppa_get_permalink($page).'wppa-album=0&amp;wppa-cover=0';
						$result['title'] = ''; //$album_name;//'a++';
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					else {
						$result['url'] = wppa_get_permalink($page).'wppa-album='.$album.'&amp;wppa-cover=0';
						$result['title'] = $album_name;//'a++';
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					break;
			}
			break;
		case 'thumbalbum':
			$album = $thumb['album'];
			$album_name = wppa_get_album_name($album);
			switch ($page) {
				case '-1':
					return false;
					break;
				case '0':
					$result['url'] = wppa_get_permalink().'wppa-album='.$album.'&amp;wppa-cover=0';
					$result['title'] = $album_name;
					$result['is_url'] = true;
					$result['is_lightbox'] = false;
					break;
				default:
					$result['url'] = wppa_get_permalink($page).'wppa-album='.$album.'&amp;wppa-cover=0';
					$result['title'] = $album_name;//'a++';
					$result['is_url'] = true;
					$result['is_lightbox'] = false;
					break;
			}
			break;
		case 'photo':
		case 'slphoto':
			if ( $type == 'slphoto' ) {
				$si = '&amp;wppa-single=1';
			}
			else {
				$si = '';
			}
			switch ($page) {
				case '-1':
					return false;
					break;
				case '0':
					if ($noalb) {
						$result['url'] = wppa_get_permalink().'wppa-album=0&amp;wppa-photo='.$photo.$si;
						$result['title'] = $title; //$photo_name;
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					else {
						$result['url'] = wppa_get_permalink().'wppa-album='.$album.'&amp;wppa-photo='.$photo.$si;
						$result['title'] = $title; //$photo_name;//'p-0';
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					break;
				default:
					if ($noalb) {
						$result['url'] = wppa_get_permalink($page).'wppa-album=0&amp;wppa-photo='.$photo.$si;
						$result['title'] = $title; //$photo_name;
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					else {
						$result['url'] = wppa_get_permalink($page).'wppa-album='.$album.'&amp;wppa-photo='.$photo.$si;
						$result['title'] = $title; //$photo_name;//'p++';
						$result['is_url'] = true;
						$result['is_lightbox'] = false;
					}
					break;
			}
			break;
		case 'single':
			switch ($page) {
				case '-1':
					return false;
					break;
				case '0':
					$result['url'] = wppa_get_permalink().'wppa-photo='.$photo;
					$result['title'] = $title; //$photo_name;//'s-0';
					$result['is_url'] = true;
					$result['is_lightbox'] = false;
					break;
				default:
					$result['url'] = wppa_get_permalink($page).'wppa-photo='.$photo;
					$result['title'] = $title; //$photo_name;//'s++';
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
//			$url = wppa_get_photo_url($photo);
			$imgsize = getimagesize( wppa_get_photo_path($photo) );
			if ($imgsize) {
				$wid = $imgsize['0'];
				$hig = $imgsize['1'];
			}
			else {
				$wid = '0';
				$hig = '0';
			}
			$url = wppa_get_photo_url( $photo, '', $wid, $hig );

			$result['url'] = "wppaFullPopUp(".$wppa['master_occur'].", ".$photo.", \'".$url."\', ".$wid.", ".$hig." )";

			$result['title'] = $title; //$photo_name;
			$result['is_url'] = false;
			$result['is_lightbox'] = false;
			return $result;
			break;
		case 'custom':
			if ($wich == 'potdwidget') {
				$result['url'] = $wppa_opt['wppa_widget_linkurl'];
				$result['title'] = $wppa_opt['wppa_widget_linktitle'];
				$result['is_url'] = true;
				$result['is_lightbox'] = false;
				return $result;
			}
			break;
		case 'slide':	// for album widget
			$result['url'] = wppa_get_permalink($wppa_opt['wppa_album_widget_linkpage']).'wppa-album='.$album.'&amp;slide';
			$result['title'] = '';
			$result['is_url'] = true;
			$result['is_lightbox'] = false;
			break;
		case 'autopage':
			if ( ! wppa_switch('wppa_auto_page') ) {
				wppa_dbg_msg('Auto page has been switched off, but there are still links to it ('.$wich.')', 'red', 'force');
				$result['url'] = '';
			}
			else {
				$result['url'] = wppa_get_permalink( wppa_get_the_auto_page( $photo ) );
			}
			$result['title'] = '';
			$result['is_url'] = true;
			$result['is_lightbox'] = false;
			break;
		default:
			wppa_dbg_msg('Error, wrong type: '.$type.' in wppa_get_imglink_a', 'red');
			return false;
			break;
	}
	
	if ( $wppa['src'] && ! $wppa['is_related'] && ! $wppa['in_widget'] ) { 
		$result['url'] .= '&amp;wppa-searchstring='.urlencode($wppa['searchstring']);
	}

	if ($wich == 'topten') {
		$result['url'] .= '&amp;wppa-topten='.$wppa_opt['wppa_topten_count'];
	}
	elseif ($wppa['is_topten']) {
		$result['url'] .= '&amp;wppa-topten='.$wppa['topten_count'];
	}
	
	if ($wich == 'lasten') {
		$result['url'] .= '&amp;wppa-lasten='.$wppa_opt['wppa_lasten_count'];
	}
	elseif ($wppa['is_lasten']) {
		$result['url'] .= '&amp;wppa-lasten='.$wppa['lasten_count'];
	}

	if ($wich == 'comten') {
		$result['url'] .= '&amp;wppa-comten='.$wppa_opt['wppa_comten_count'];
	}
	elseif ($wppa['is_comten']) {
		$result['url'] .= '&amp;wppa-comten='.$wppa['comten_count'];
	}

	if ($wich == 'featen') {
		$result['url'] .= '&amp;wppa-featen='.$wppa_opt['wppa_featen_count'].'&amp;wppa-randseed='.$wppa['randseed'];
	}
	elseif ($wppa['is_featen']) {
		$result['url'] .= '&amp;wppa-featen='.$wppa['featen_count'].'&amp;wppa-randseed='.$wppa['randseed'];
	}
	
	if ( $wppa['is_related'] ) {
		$result['url'] .= '&amp;wppa-rel='.$wppa['is_related'].'&amp;wppa-relcount='.$wppa['related_count'];
	}
	elseif ( $wppa['is_tag'] ) {
		$result['url']  .= '&amp;wppa-tag='.$wppa['is_tag'];
	}
	
	if ( $wppa['is_upldr'] ) {
		$result['url'] .= '&amp;wppa-upldr='.$wppa['is_upldr'];
	}
	
	if ($page != '0') {	// on a different page
		$occur = '1';
		$w = '';
	}
	else {				// on the same page, post or widget
		$occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
		$w = $wppa['in_widget'] ? 'w' : '';
	}
	$result['url'] .= '&amp;wppa-'.$w.'occur='.$occur;
	$result['url'] = wppa_convert_to_pretty($result['url']);
	
	if ($result['title'] == '') $result['title'] = $tit;	// If still nothing, try arg
	
	return $result;
}

function wppa_nltab($key = '') {
global $wppa;
	switch($key) {
		case 'init':
			$wppa['tabcount'] = '0';
			break;
		case '-':
			if ($wppa['tabcount']) $wppa['tabcount']--;
			break;
	}
	$wppa['out'] .= "\n";
	$t = $wppa['tabcount'];
	while($t > '0') {
		$wppa['out'] .= "\t";
		$t--;
	}
	if ($key == '+') $wppa['tabcount']++;
}

function wppa_is_photo_new($id) {
global $thumb;
global $wpdb;
global $wppa_opt;

	if ( is_array($thumb) ) {
		$birthtime = $thumb['timestamp'];
	}
	else {
		$birthtime = $wpdb->get_var( $wpdb->prepare( "SELECT timestamp FROM " . WPPA_PHOTOS . " WHERE id = %s LIMIT 1", $id ) );
		wppa_dbg_q('Q54');
	}
	$timnow = time();
	
	$isnew = (( $timnow - $birthtime ) < $wppa_opt['wppa_max_photo_newtime'] );
	return $isnew;
}

function wppa_is_album_new($id) {
global $wpdb;
global $wppa_opt;

	$birthtime = $wpdb->get_var( $wpdb->prepare( "SELECT timestamp FROM " . WPPA_ALBUMS . " WHERE id = %s LIMIT 1", $id ) );
	wppa_dbg_q('Q55');
	$timnow = time();
	$isnew = (( $timnow - $birthtime ) < $wppa_opt['wppa_max_album_newtime'] );
	if ( $isnew ) return true;
	// A new (grand)child?
	if ( false ) return false;	// check setting
	$children = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_ALBUMS."` WHERE `a_parent` = %s", $id), ARRAY_A );
	if ( $children ) {
		foreach ( $children as $child ) {
			if ( wppa_is_album_new($child['id']) ) return true;	// Found one? Done!
		}
	}
	return false;
}

function wppa_get_get($index, $default = false) {
global $wppa_get_get_cache;

	if ( isset($wppa_get_get_cache[$index]) ) return $wppa_get_get_cache[$index];
	
	if (isset($_GET['wppa-'.$index])) {			// New syntax first
		$result = $_GET['wppa-'.$index];
	}
	elseif (isset($_GET[$index])) {				// Old syntax
		$result = $_GET[$index];
	}
	else return $default;						// Nothing, return default
	
	if ( $result == 'nil' ) $result = $default;	// Nil simulates not set
	
	$result = strip_tags($result);
	if ( strpos($result, '<?') !== false ) die('Security check failure #191');
	if ( strpos($result, '?>') !== false ) die('Security check failure #192');

	// Post processing needed?
	if ( $index == 'photo' && ( ! is_numeric($result) || ! wppa_photo_exists($result) ) ) {
		$result = wppa_get_photo_id_by_name($result, wppa_get_get('album'));
		if ( ! $result ) $result = $default;
	}
	if ( $index == 'album' ) {
		if ( ! is_numeric($result) ) {
			$temp = wppa_get_album_id_by_name($result);
			if ( is_numeric($temp) && $temp > '0' ) {
				$result = $temp;
			}
			elseif ( ! wppa_series_to_array($result) ) {
				$result = $default;
			}
		}
	}
	
	// Save in cache
	$wppa_get_get_cache[$index] = $result;
	return $result;
}

function wppa_get_post($index, $default = false) {
	if (isset($_POST['wppa-'.$index])) {		// New syntax first
		$result = $_POST['wppa-'.$index];
		if ( strpos($result, '<?') !== false ) die('Security check failure #291');
		if ( strpos($result, '?>') !== false ) die('Security check failure #292');
		return $result;
	}
	if (isset($_POST[$index])) {				// Old syntax
		$result = $_POST[$index];
		if ( strpos($result, '<?') !== false ) die('Security check failure #391');
		if ( strpos($result, '?>') !== false ) die('Security check failure #392');
		return $result;
	}
	return $default;
}

function wppa_get_photo_id_by_name($xname, $album = '0') {
global $wpdb;
global $allphotos;

	if ( is_numeric($album) ) {
		$alb = $album;
		$albums = array($album);
	}
	else {
		$albums = wppa_series_to_array($album);
		$alb = implode(" OR `album` = ", $albums);
	}
	// Do a first guess, assume no quotes and no language
	$results = $wpdb->get_results($wpdb->prepare( "SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `name` = %s AND ( `album` = %s )", $xname, $album), ARRAY_A );
	$guess = $results ? $results[0]['id'] : false;
	if ( $guess ) {
		wppa_dbg_msg('wppa_get_photo_id_by_name() first guess succesfull!');
		return $guess;
	}
	wppa_dbg_msg('wppa_get_photo_id_by_name() first guess NOT succesfull!');
	
	$name = wppa_normalize_quotes(stripslashes($xname));
	// Get all photos
	if ( ! $allphotos ) {
		$allphotos = $wpdb->get_results( "SELECT `id`, `name`, `ext`, `album` FROM `" . WPPA_PHOTOS . "`", ARRAY_A );
		wppa_dbg_q('Q56');
		// Translate names
		if ( is_array($allphotos) ) {
			$index = '0';
			$count = count($allphotos);
			// Translate names
			while ( $index < $count ) {
				$allphotos[$index]['name'] = wppa_normalize_quotes(stripslashes(wppa_qtrans($allphotos[$index]['name'])));
				$index++;
			}
		}
	}
	// Search
	if ( is_array($allphotos) ) {
		$index = '0';
		$count = count($allphotos);
		while ( $index < $count ) {
			if ($name == $allphotos[$index]['name']) {
				if ( $album ) {
					if ( in_array($allphotos[$index]['album'], $albums) ) return $allphotos[$index]['id'];	// Found!
				}
				else {
					return $allphotos[$index]['id'];	// Found!
				}
			}
			$index++;
		}
	}
	// Not found
	return false;	
}

function wppa_get_album_id_by_name($xname, $report_dups = false) {
global $wpdb;
global $allalbums;

	$name = wppa_normalize_quotes(stripslashes($xname));
	// Get all albums
	if ( ! $allalbums ) {
		$allalbums = $wpdb->get_results( "SELECT `id`, `name` FROM `" . WPPA_ALBUMS . "`", ARRAY_A );
		wppa_dbg_q('Q57');
		// Translate names
		if ( is_array($allalbums) ) {
			$index = '0';
			$count = count($allalbums);
			// Translate names
			while ( $index < $count ) {
				$allalbums[$index]['name'] = wppa_normalize_quotes(stripslashes(wppa_qtrans($allalbums[$index]['name'])));
				$index++;
			}
		}
	}
	// Search
	$result = false;
	if ( is_array($allalbums) ) {
		$index = '0';
		$count = count($allalbums);
		while ( $index < $count ) {
			if ($name == $allalbums[$index]['name']) {	// Found one
				if ( $report_dups ) {
					if ( $result ) {	//Dup
						return '-1';
					}
					$result = $allalbums[$index]['id'];	// Found (first) !
				}
				else {
					$result = $allalbums[$index]['id'];	// Found!
					return $result;
				}
			}
			$index++;
		}
	}
	// Not found
	return $result;	
}

// Perform the frontend upload
function wppa_user_upload() {
global $wpdb;
global $wppa;
global $wppa_opt;

	wppa_dbg_msg('Usr_upl entered');
	
	if ($wppa['user_uploaded']) return;	// Already done
	$wppa['user_uploaded'] = true;
	if ( !$wppa_opt['wppa_user_upload_on'] ) return;	// Feature not enabled
	if ( $wppa_opt['wppa_user_upload_login'] ) {
		if ( !is_user_logged_in() ) return;					// Must login
//		if ( !current_user_can('wppa_upload') ) return;		// No upload rights
	}
//print_r($_POST);
//return;
	if ( wppa_get_post('wppa-album-name') ) {	// Create album
		$nonce = wppa_get_post('nonce');
		$ok = wp_verify_nonce($nonce, 'wppa-album-check');
		if ( ! $ok ) die(__a('<b>ERROR: Illegal attempt to create an album.</b>'));
		// Check captcha
		$captkey = $wppa['randseed'];
		if ( ! wppa_check_captcha($captkey) ) {
			wppa_err_alert(__a('Wrong captcha, please try again'));
			return;
		}
		$album = wppa_create_album_entry( array( 	'name' => strip_tags( wppa_get_post('wppa-album-name') ), 
													'description' => strip_tags( wppa_get_post('wppa-album-desc') ),
													'a_parent' => strval( intval( wppa_get_post('wppa-album-parent') ) ),
													) );
		if ( $album ) wppa_err_alert( sprintf( __a('Album #%s created'), $album ) );
		else wppa_err_alert( __a('Could not create album') );
	}
	
	if ( wppa_get_post('wppa-upload-album') ) {	// Upload photo
		$nonce = wppa_get_post('nonce');
		$ok = wp_verify_nonce($nonce, 'wppa-check');
		if ( ! $ok ) die(__a('<b>ERROR: Illegal attempt to upload a file.</b>'));
		
		$alb = wppa_get_post('wppa-upload-album');

		if (is_array($_FILES)) {
			$bret = true;
			$filecount = '1';
			$done = '0';
			foreach ($_FILES as $file) {
				if ( $bret ) {
					if ( ! is_array($file['error']) ) {
						$file['name'] = strip_tags($file['name']);
						$bret = wppa_do_frontend_file_upload($file, $alb);	// this should no longer happen since the name is incl []
						if ( $bret ) $done++;
					}
					else {
						$filecount = count($file['error']);
						for ($i = '0'; $i < $filecount; $i++) {
							if ( $bret ) {
								$f['error'] = $file['error'][$i];
								$f['tmp_name'] = $file['tmp_name'][$i];
								$f['name'] = strip_tags($file['name'][$i]);
								$f['type'] = $file['type'][$i];
								$f['size'] = $file['size'][$i];
								$bret = wppa_do_frontend_file_upload($f, $alb);
								if ( $bret ) $done++;
							}
						}
					}
				}
			}
			if ( $done ) {
				//SUCCESSFUL UPLOAD, ADD POINTS
				if( function_exists('cp_alterPoints') && is_user_logged_in() ) {
					$cbpoints = $wppa_opt['wppa_cp_points_upload'] * $done;
					cp_alterPoints(cp_currentUser(), $cbpoints);
				}
				else $cbpoints = '0';
				$alert = $done == '1' ? __a('Photo successfully uploaded.') : sprintf(__a('%s photos successfully uploaded.'), $done);
				if ( $cbpoints ) $alert .= '\n'.sprintf(__a('%s points added.'), $cbpoints);
				wppa_err_alert($alert);
			}
			else wppa_err_alert(__a('Upload failed'));
		}		
	}	
}

// Subroutine to upload one file in the frontend
function wppa_do_frontend_file_upload($file, $alb) {
global $wpdb;
global $wppa_opt;
global $album;

	wppa_cache_album($alb);
				
	if ( ! wppa_allow_uploads($alb) || ! wppa_allow_user_uploads() ) {
		wppa_err_alert(__a('Max uploads reached'));
		return false;
	}
	if ( $file['error'] != '0' ) {
		wppa_err_alert(__a('Error during upload'));
		return false;
	}
	$imgsize = getimagesize($file['tmp_name']);
	if ( !is_array($imgsize) ) {
		wppa_err_alert(__a('Uploaded file is not an image'));
		return false;
	}
	if ( $imgsize[2] < 1 || $imgsize[2] > 3 ) {
		wppa_err_alert(sprintf(__a('Only gif, jpg and png image files are supported. Returned filetype = %d.'), $imagesize[2]));
		return false;
	}
	$mayupload = wppa_check_memory_limit('', $imgsize[0], $imgsize[1]);
	if ( $mayupload === false ) {
		$maxsize = wppa_check_memory_limit(false);
		if ( is_array($maxsize) ) {	
			wppa_err_alert(sprintf(__a('The image is too big. Max photo size: %d x %d (%2.1f MegaPixel)'), $maxsize['maxx'], $maxsize['maxy'], $maxsize['maxp']/(1024*1024) ));
			return false;
		}
	}
	switch($imgsize[2]) { 	// mime type
		case 1: $ext = 'gif'; break;
		case 2: $ext = 'jpg'; break;
		case 3: $ext = 'png'; break;
	}
//	$id = wppa_nextkey(WPPA_PHOTOS);
	if ( wppa_get_post( 'user-name' ) ) {
		$name = strip_tags( wppa_get_post( 'user-name' ) );
	}
	else {
		$name = $file['name'];
	}
	$name = htmlspecialchars($name);
//	if ( wppa_switch('wppa_strip_file_ext') ) {
//		$name = preg_replace('/\.[^.]*$/', '', $name);
//	}
//	$porder = '0';
	$desc = balanceTags( wppa_get_post( 'user-desc' ), true );
//	$mrat = '0';
//	$linkurl = '';
//	$linktitle = '';
	$linktarget = '_self';
//	$owner = wppa_get_user();
	$status = ( $wppa_opt['wppa_upload_moderate'] && !current_user_can('wppa_admin') ) ? 'pending' : 'publish';
	$filename = $file['name'];
	$id = wppa_create_photo_entry( array( 'album' => $alb, 'ext' => $ext, 'name' => $name, 'description' => $desc, 'status' => $status, 'filename' => $filename, ) );
//	(`id`, `album`, `ext`, `name`, `p_order`, `description`, `mean_rating`, `linkurl`, `linktitle`, `linktarget`, `timestamp`, `owner`, `status`, `tags`, `alt`, `filename`, `modified`, `location`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, \'0\', \'\')', 
//	$id, $alb, $ext, $name, $porder, $desc, $mrat, $linkurl, $linktitle, $linktarget, time(), $owner, $status, $album['default_tags'], '', $filename);
//
//	wppa_dbg_q('Q58');
	
	if ( ! $id ) {
		wppa_err_alert(__a('Could not insert photo into db.'));
		return false;
	}
	else {
		wppa_save_source($file['tmp_name'], $filename, $alb);
		wppa_update_album_timestamp($alb);
		wppa_set_last_album($alb);
		wppa_flush_treecounts($alb);
	}
	if ( wppa_make_the_photo_files( $file['tmp_name'], $id, $ext ) ) {
		// Repair photoname if not standard
		if ( ! wppa_get_post( 'user-name' ) ) {
			wppa_set_default_name( $id );
		}
		// Defaul tags
		wppa_set_default_tags( $id );
		// Index
		wppa_index_add('photo', $id);
		// Mail
		if ( $wppa_opt['wppa_upload_notify'] ) {
			$to = get_bloginfo('admin_email');
			$subj = sprintf(__a('New photo uploaded: %s'), $name);
			$cont['0'] = sprintf(__a('User %s uploaded photo %s into album %s'), wppa_get_user(), $id, wppa_get_album_name($alb));
			if ( $wppa_opt['wppa_upload_moderate'] && !current_user_can('wppa_admin') ) {
				$cont['1'] = __a('This upload requires moderation');
				$cont['2'] = '<a href="'.get_admin_url().'admin.php?page=wppa_admin_menu&tab=pmod&photo='.$id.'" >'.__a('Moderate manage photo').'</a>';
			}
			else {
				$cont['1'] = __a('Details:');
				$cont['1'] .= ' <a href="'.get_admin_url().'admin.php?page=wppa_admin_menu&tab=pmod&photo='.$id.'" >'.__a('Manage photo').'</a>';
			}
			wppa_send_mail($to, $subj, $cont, $id);
		}
		return true;
	}
	else {
		return false;
	}
}

function wppa_normalize_quotes($xtext) {

	$text = html_entity_decode($xtext);
	$result = '';
	while ( $text ) {
		$char = substr($text, 0, 1);
		$text = substr($text, 1);
		switch ($char) {
			case '`':	// grave
			case '’':	// acute
				$result .= "'";
				break;
			case '“':	// double grave
			case '”':	// double acute
				$result .= '"';
				break;
			case '&':
				if (substr($text, 0, 5) == '#039;') {	// quote
					$result .= "'";
					$text = substr($text, 5);
				}
				elseif (substr($text, 0, 5) == '#034;') {	// double quote
					$result .= "'";
					$text = substr($text, 5);
				}
				elseif ( substr($text, 0, 6) == '#8216;' || substr($text, 0, 6) == '#8217;' ) {	// grave || acute
					$result .= "'";
					$text = substr($text, 6);
				}
				elseif ( substr($text, 0, 6) == '#8220;' || substr($text, 0, 6) == '#8221;' ) {	// double grave || double acute
					$result .= '"';
					$text = substr($text, 6);
				}
				break;
			default:
				$result .= $char;
				break;
		}
	}
	return $result;
}

// Find the search results. For use in a page template to show the search results. See ./theme/search.php
function wppa_have_photos($xwidth = '0') {
global $wppa;

	if ( !is_search() ) return false;
	$width = $xwidth ? $xwidth : wppa_get_container_width();
	
	$wppa['searchresults'] = wppa_albums('', '', $width);

	return $wppa['any'];
}

// Display the searchresults. For use in a page template to show the search results. See ./theme/search.php
function wppa_the_photos() {
global $wppa;

	if ( $wppa['any'] ) echo $wppa['searchresults'];
}

// Decide if a thumbnail photo file can be used for a requested display
function wppa_use_thumb_file($id, $width = '0', $height = '0') {
global $wppa_opt;
global $wpdb;

	if ( ! $wppa_opt['wppa_use_thumbs_if_fit'] ) return false;
	if ( $width < 1.0 && $height < 1.0 ) return false;	// should give at least one dimension and not when fractional
	$ext = $wpdb->get_var($wpdb->prepare('SELECT ext FROM `'.WPPA_PHOTOS.'` WHERE `id` = %s', $id));
	if ( ! $ext ) return false;
	$file = wppa_get_thumb_path($id);
	if ( file_exists($file) ) {
		$size = getimagesize($file);
	}
	else return false;
	if ( ! is_array($size) ) return false;
	if ( $width > 0 && $size[0] < $width ) return false;
	if ( $height > 0 && $size[1] < $height ) return false;
	return true;
}
	
// Compute time to wait for time limited uploads
function wppa_time_to_wait_html($album, $user = false) {
global $wpdb;
	
	if ( ! $album && ! $user ) return '0';

	if ( $user ) {
		$limits = wppa_get_user_upload_limits();
	}
	else {
		$limits = $wpdb->get_var($wpdb->prepare("SELECT `upload_limit` FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $album));
	}
	wppa_dbg_q('Q62');
	$temp = explode('/', $limits);
	$limit_max  = isset($temp[0]) ? $temp[0] : '0';
	$limit_time = isset($temp[1]) ? $temp[1] : '0';

	$result = '';
	
	if ( ! $limit_max || ! $limit_time ) return $result;
	
	if ( $user ) {
		$owner = wppa_get_user('login');
		$last_upload_time = $wpdb->get_var($wpdb->prepare("SELECT `timestamp` FROM `".WPPA_PHOTOS."` WHERE `owner` = %s ORDER BY `timestamp` DESC LIMIT 1", $owner));
	}
	else {
		$last_upload_time = $wpdb->get_var($wpdb->prepare("SELECT `timestamp` FROM `".WPPA_PHOTOS."` WHERE `album` = %s ORDER BY `timestamp` DESC LIMIT 1", $album));
	}
	wppa_dbg_q('Q63');
	$timnow = time();
	
	// For simplicity: a year is 364 days = 52 weeks, we skip the months
	$seconds = array( 'min' => '60', 'hour' => '3600', 'day' => '86400', 'week' => '604800', 'month' => '2592000', 'year' => '31449600' );
	$deltatim = $last_upload_time + $limit_time - $timnow;
	
	$temp    = $deltatim;
//	$months  = floor($temp / $seconds['month']);
//	$temp    = $temp % $seconds['month'];
	$weeks   = floor($temp / $seconds['week']);
	$temp    = $temp % $seconds['week'];
	$days    = floor($temp / $seconds['day']);
	$temp    = $temp % $seconds['day'];
	$hours   = floor($temp / $seconds['hour']);
	$temp    = $temp % $seconds['hour'];
	$mins    = floor($temp / $seconds['min']);
	$secs    = $temp % $seconds['min'];
	
	$switch = false;
	$string = __a('You can upload after').' ';
//	if ( $months           ) { $string .= $months.' '.'months'.', '; $switch = true; }
	if ( $weeks || $switch ) { $string .= $weeks.' '.__a('weeks').', '; $switch = true; }
	if ( $days  || $switch ) { $string .= $days.' '.__a('days').', '; $switch = true; }
	if ( $hours || $switch ) { $string .= $hours.' '.__a('hours').', '; $switch = true; }
	if ( $mins  || $switch ) { $string .= $mins.' '.__a('minutes').' '.__a('and').' '; $switch = true; }
	if (           $switch ) { $string .= $secs.' '.__a('seconds'); }
	$string .= '.';
	$result = '<span style="font-size:9px;"> '.$string.'</span>';
	return $result;
}

// Get the title to be used for lightbox links == thext under the lightbox image
function wppa_get_lbtitle($type, $id) {
global $wppa_opt;
global $thumb;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_lbtitle('.$id.')', 'red');

	wppa_cache_thumb($id);

	$do_name 	= wppa_switch('wppa_ovl_'.$type.'_name');
	$do_desc 	= wppa_switch('wppa_ovl_'.$type.'_desc');
	$do_sm 		= wppa_switch('wppa_share_on_lightbox');

	$result = '';
	if ( $do_name ) $result .= wppa_get_photo_name($thumb['id']); 
	if ( $do_name && $do_desc ) $result .= '<br />';
	if ( $do_desc ) $result .= wppa_get_photo_desc($thumb['id']);
	if ( ( $do_name || $do_desc ) && $do_sm ) $result .= '<br />';
	if ( $do_sm ) $result .= wppa_get_share_html( 'lightbox' );
	
	$result = esc_attr($result);
	return $result;
}

function wppa_zoom_in() {
global $wppa_opt;
	if ( $wppa_opt['wppa_show_zoomin'] ) return __a('Zoom in');
	else return ' ';
}

