<?php
/* wppa-functions.php
* Pachkage: wp-photo-album-plus
*
* Various funcions and API modules
* Version 5.0.16
*
*/

if ( ! defined( 'ABSPATH' ) )
    die( "Can't load this file directly" );

/* show system statistics */
function wppa_statistics() {
global $wppa;

	$wppa['out'] .= wppa_get_statistics();
}
function wppa_get_statistics() {

	$count = wppa_get_total_album_count();
	$y_id = wppa_get_youngest_album_id();
	$y_name = __(wppa_get_album_name($y_id));
	$p_id = wppa_get_parentalbumid($y_id);
	$p_name = __(wppa_get_album_name($p_id));
	
	$result = '<div class="wppa-box wppa-nav" style="text-align: center; '.__wcs('wppa-box').__wcs('wppa-nav').'">';
	$result .= __a('There are').' '.$count.' '.__a('photo albums. The last album added is').' ';
	$result .= '<a href="'.wppa_get_permalink().'wppa-album='.$y_id.'&amp;wppa-cover=0&amp;wppa-occur=1">'.$y_name.'</a>';

	if ($p_id > '0') {
		$result .= __a(', a subalbum of').' '; 
		$result .= '<a href="'.wppa_get_permalink().'wppa-album='.$p_id.'&amp;wppa-cover=0&amp;wppa-occur=1">'.$p_name.'</a>';
	}
	
	$result .= '.</div>';
	
	return $result;
}

/* shows the breadcrumb navigation */
function wppa_breadcrumb($opt = '') {
global $wppa;
global $wppa_opt;
global $wpdb;

	/* See if they need us */
		
	if ( $wppa['is_single'] ) return;	/* A single image slideshow needs no navigation */

	if ($opt == 'optional') {
		$pid = wppa_get_the_page_id();
		$type = $wpdb->get_var( $wpdb->prepare( "SELECT `post_type` FROM `" . $wpdb->posts . "` WHERE `ID` = %s", $pid ) );
		if ( $type == 'post' && ! $wppa_opt['wppa_show_bread_posts'] ) return;	/* Nothing to do here */
		if ( $type == 'page' && ! $wppa_opt['wppa_show_bread_pages'] ) return;	/* Nothing to do here */
	}
	if (wppa_page('oneofone')) return; /* Never at a single image */
	if ($wppa['is_slideonly'] == '1') return;	/* Not when slideony */
	if ($wppa['in_widget']) return; /* Not in a widget */
	if (is_feed()) return;	/* Not in a feed */
	if ($wppa['is_topten'] && !$wppa_opt['wppa_bc_on_topten']) return;
	if ($wppa['is_lasten'] && !$wppa_opt['wppa_bc_on_lasten']) return;
	if ($wppa['is_comten'] && !$wppa_opt['wppa_bc_on_comten']) return;
	if ($wppa['is_featen'] && !$wppa_opt['wppa_bc_on_featen']) return;
	if ($wppa['is_tag'] && !$wppa_opt['wppa_bc_on_tag']) return;
	if (wppa_get_searchstring() && !$wppa_opt['wppa_bc_on_search']) return;

	/* Compute the seperator */
	$temp = $wppa_opt['wppa_bc_separator'];
	switch ($temp) {
		case 'url':
			$size = $wppa_opt['wppa_fontsize_nav'];
			if ( $size == '' ) $size = '12';
			$style = 'height:'.$size.'px;';
			$sep = ' <img src="'.$wppa_opt['wppa_bc_url'].'" class="no-shadow" style="'.$style.'" /> ';
			break;
		case 'txt':
			$sep = ' '.html_entity_decode(stripslashes($wppa_opt['wppa_bc_txt']), ENT_QUOTES).' ';
			break;
		default:
			$sep = ' &' . $temp . '; ';
	}

	$occur = wppa_get_get('occur', '1');
	$this_occur = ( ( $occur == $wppa['occur'] ) || $wppa['ajax'] ); /**/ // or ajax???

	$alb = '0';
	if ( $this_occur ) $alb = wppa_get_get('album');
	if ( ! $alb && is_numeric($wppa['start_album']) ) $alb = $wppa['start_album'];
	$separate = wppa_is_separate($alb);
	$slide = ( wppa_get_album_title_linktype($alb) == 'slide' ) ? '&amp;wppa-slide' : '';

	// See if we link to covers or to contents
	$to_cover = $wppa_opt['wppa_thumbtype'] == 'none' ? '1' : '0';
	
	$wppa['out'] .= wppa_nltab('+').'<div id="wppa-bc-'.$wppa['master_occur'].'" class="wppa-nav wppa-box wppa-nav-text" style="'.__wcs('wppa-nav').__wcs('wppa-box').__wcs('wppa-nav-text').'">';

		if ($wppa_opt['wppa_show_home']) {
			$wppa['out'] .= wppa_nltab().'<a href="'.wppa_dbg_url(get_bloginfo('url')).'" class="wppa-nav-text" style="'.__wcs('wppa-nav-text').'" >'.__a('Home').'</a>';
			$wppa['out'] .= wppa_nltab().'<span class="wppa-nav-text" style="'.__wcs('wppa-nav-text').'" >'.$sep.'</span>';	
		}
	
		if ( is_page() || $wppa['ajax'] ) if ( $wppa_opt['wppa_show_page'] ) wppa_page_breadcrumb($sep);	
	
		if ( $wppa['ajax'] ) {
			if ( isset($_GET['p']) ) $p = $_GET['p'];
			elseif ( isset($_GET['page_id']) ) $p = $_GET['page_id'];
			elseif ( isset($_GET['wppa-fromp']) ) $p = $_GET['wppa-fromp'];
			$query = "SELECT post_title FROM " . $wpdb->posts . " WHERE post_status = 'publish' AND id = %s LIMIT 0,1";
			$the_title = wppa_qtrans(stripslashes($wpdb->get_var($wpdb->prepare($query, $p))));
		}
		else {
			$the_title = the_title('', '', false);
		}
		
		if ( $alb == 0 || wppa_is_enum($alb) ) {
			if ( !$separate ) if ( $wppa_opt['wppa_show_page'] ) {
				$wppa['out'] .= wppa_nltab().'<span class="wppa-nav-text wppa-black b1" style="'.__wcs('wppa-nav-text').__wcs('wppa-black').'" >'.$the_title.'</span>';
			}
		} else {	/* $alb != 0 */
			if ( !$separate ) if ( $wppa_opt['wppa_show_page'] ) {
				$wppa['out'] .= wppa_nltab().'<a href="'.wppa_get_permalink('', true).'" class="wppa-nav-text b2" style="'.__wcs('wppa-nav-text').'" >'.$the_title.'</a>';
				$wppa['out'] .= wppa_nltab().'<span class="wppa-nav-text b3" style="'.__wcs('wppa-nav-text').'" >'.$sep.'</span>';
			}

		    wppa_crumb_ancestors($sep, $alb, $wppa['occur'], $to_cover);

			if ( wppa_page('oneofone') ) {
				$photo = $wppa['single_photo'];
			}
			elseif ( wppa_page('single') ) {
				$photo = wppa_get_get('photo', '');
			}
			else {
				$photo = '';
			}
		
			if ( is_numeric($photo) && $this_occur ) {
				$wppa['out'] .= wppa_nltab().'<a href="'.wppa_get_permalink().'wppa-album='.$alb.'&amp;wppa-cover='.$to_cover.$slide.'&amp;wppa-occur='.$wppa['occur'].'" class="wppa-nav-text b4" style="'.__wcs('wppa-nav-text').'" >'.__(wppa_get_album_name($alb)).'</a>';
				$wppa['out'] .= wppa_nltab().'<span class="b5" >'.$sep.'</span>';
				$wppa['out'] .= wppa_nltab().'<span id="bc-pname-'.$wppa['occur'].'" class="wppa-nav-text wppa-black b8" style="'.__wcs('wppa-nav-text').__wcs('wppa-black').'" >'.__(wppa_get_photo_name($photo)).'</span>';
			} elseif ( $this_occur && !wppa_page('albums') ) {
				$wppa['out'] .= wppa_nltab().'<a href="'.wppa_get_permalink().'wppa-album='.$alb.'&amp;wppa-cover='.$to_cover.$slide.'&amp;wppa-occur='.$wppa['occur'].'" class="wppa-nav-text b6" style="'.__wcs('wppa-nav-text').'" >'.__(wppa_get_album_name($alb)).'</a>';
				$wppa['out'] .= wppa_nltab().'<span class="b7" >'.$sep.'</span>';
				$wppa['out'] .= wppa_nltab().'<span id="bc-pname-'.$wppa['occur'].'" class="wppa-nav-text wppa-black b9" style="'.__wcs('wppa-nav-text').__wcs('wppa-black').'" >'.__a('Slideshow').'</span>';
			} else {	// NOT This occurance OR album
				$albnam = $alb == '-2' ? __a('All albums') : __(wppa_get_album_name($alb));
				$wppa['out'] .= wppa_nltab().'<span class="wppa-nav-text wppa-black b10" style="'.__wcs('wppa-nav-text').__wcs('wppa-black').'" >'.$albnam.'</span>';
			} 
		}

		if ($wppa['src'] && $wppa['master_occur'] == '1') {
			$wppa['out'] .= wppa_nltab().'<span class="b12" >'.$sep.'</span>';
			$wppa['out'] .= wppa_nltab().'<span class="wppa-nav-text b11" style="'.__wcs('wppa-nav-text').__wcs('wppa-black').'" ><b>'.__a('Searchstring:').'&nbsp;'.stripslashes($wppa['searchstring']).'</b></span>'; // $_POST['wppa-searchstring'].'</b></span>';
		}
		elseif ( $wppa['is_topten'] ) {
			$wppa['out'] .= wppa_nltab().'<span class="b12" >'.$sep.'</span>';
			$wppa['out'] .= wppa_nltab().'<span class="wppa-nav-text b11" style="'.__wcs('wppa-nav-text').__wcs('wppa-black').'" ><b>'.__a('Top rated photos').'</b></span>';
		}
		elseif ( $wppa['is_lasten'] ) {
			$wppa['out'] .= wppa_nltab().'<span class="b12" >'.$sep.'</span>';
			$wppa['out'] .= wppa_nltab().'<span class="wppa-nav-text b11" style="'.__wcs('wppa-nav-text').__wcs('wppa-black').'" ><b>'.__a('Recently uploaded photos').'</b></span>';
		}
		elseif ( $wppa['is_comten'] ) {
			$wppa['out'] .= wppa_nltab().'<span class="b12" >'.$sep.'</span>';
			$wppa['out'] .= wppa_nltab().'<span class="wppa-nav-text b11" style="'.__wcs('wppa-nav-text').__wcs('wppa-black').'" ><b>'.__a('Recently commented photos').'</b></span>';
		}
		elseif ( $wppa['is_featen'] ) {
			$wppa['out'] .= wppa_nltab().'<span class="b12" >'.$sep.'</span>';
			$wppa['out'] .= wppa_nltab().'<span class="wppa-nav-text b11" style="'.__wcs('wppa-nav-text').__wcs('wppa-black').'" ><b>'.__a('Featured photos').'</b></span>';
		}
		elseif ( $wppa['is_tag'] ) {
			$wppa['out'] .= wppa_nltab().'<span class="b12" >'.$sep.'</span>';
			$tg = $wppa['is_tag'];
			$tg = trim($tg, ',;');
			$tg = str_replace(',', '</b> and <b>', $tg);
			$tg = str_replace(';', '</b> or <b>', $tg);
			$wppa['out'] .= wppa_nltab().'<span class="wppa-nav-text b11" style="'.__wcs('wppa-nav-text').__wcs('wppa-black').'" ><b>'.__a('Tagged photos:').'&nbsp;'.$tg.'</b></span>';
		}

	if ( $wppa['is_slide'] ) {
		if ( $wppa_opt['wppa_bc_slide_thumblink'] ) {
			$s = $wppa['src'] ? '&wppa-searchstring='.urlencode($wppa['searchstring']) : '';
			$onclick = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_album_url_ajax($wppa['start_album'], '0')."&wppa-photos-only=1".$s."', '".wppa_convert_to_pretty(wppa_get_album_url($wppa['start_album'], '0').'&wppa-photos-only=1'.$s)."')";
			$fs = $wppa_opt['wppa_fontsize_nav'];	
			if ($fs != '') $fs += 3; else $fs = '15';	// iconsize = fontsize+3, Default to 15
			$imgs = 'height: '.$fs.'px; margin:0 0 -3px 0; padding:0; box-shadow:none;';
			$wppa['out'] .= '<a href="javascript:void()" title="'.__a('Thumbnail view', 'wppa').'" class="wppa-nav-text" style="'.__wcs('wppa-nav-text').'float:right; cursor:pointer;" onclick="'.$onclick.'" >'.
				'<img src="'.wppa_get_imgdir().'application_view_icons.png" alt="'.__a('Thumbs', 'wppa_theme').'" style="'.$imgs.'" />'.
			'</a>';
		}
	}
	
	$wppa['out'] .= wppa_nltab('-').'</div>';
}
function wppa_crumb_ancestors($sep, $alb, $occur, $to_cover) {
global $wppa;
global $wpdb;

    $parent = wppa_get_parentalbumid($alb);
	if ( $parent < '1' ) return;
    
    wppa_crumb_ancestors($sep, $parent, $wppa['occur'], $to_cover);

	$slide = ( wppa_get_album_title_linktype($parent) == 'slide' ) ? '&amp;wppa-slide' : '';
	
$pagid = $wpdb->get_var($wpdb->prepare("SELECT `cover_linkpage` FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $parent));

    $wppa['out'] .= wppa_nltab().'<a href="'.wppa_get_permalink($pagid).'wppa-album='.$parent.'&amp;wppa-cover='.$to_cover.$slide.'&amp;wppa-occur='.$occur.'" class="wppa-nav-text b20" style="'.__wcs('wppa-nav-text').'" >'.__(wppa_get_album_name($parent)).'</a>';
	$wppa['out'] .= wppa_nltab().'<span class="wppa-nav-text" style="'.__wcs('wppa-nav-text').'">'.$sep.'</span>';
    return;
}
function wppa_page_breadcrumb($sep) {

	$page = wppa_get_the_page_id();
	wppa_crumb_page_ancestors($sep, $page); 
}
function wppa_crumb_page_ancestors($sep, $page = '0') {
global $wpdb;
global $wppa;

	$query = "SELECT post_parent FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish' AND id = %s LIMIT 0,1";
	$parent = $wpdb->get_var( $wpdb->prepare( $query, $page ) );
	if (!is_numeric($parent) || $parent == '0') return;

	wppa_crumb_page_ancestors($sep, $parent);

	$query = "SELECT post_title FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish' AND id = %s LIMIT 0,1";
	$title = $wpdb->get_var( $wpdb->prepare( $query, $parent ) );
	if (!$title) {
		$title = '****';		// Page exists but is not publish
		$wppa['out'] .= wppa_nltab().'<a href="#" class="wppa-nav-text b30" style="'.__wcs('wppa-nav-text').'" ></a>';
		$wppa['out'] .= wppa_nltab().'<span class="wppa-nav-text b31" style="'.__wcs('wppa-nav-text').'" >'.$title.$sep.'</span>';
	} else {
		$wppa['out'] .= wppa_nltab().'<a href="'.get_page_link($parent).'" class="wppa-nav-text b32" style="'.__wcs('wppa-nav-text').'" >'.__($title).'</a>';
		$wppa['out'] .= wppa_nltab().'<span class="wppa-nav-text b32" style="'.__wcs('wppa-nav-text').'" >'.$sep.'</span>';
	}
}
function wppa_get_the_page_id() {
	$page = @ get_the_ID();
	if ( ! $page ) {
		if ( isset($_REQUEST['page_id']) ) $page = $_REQUEST['page_id'];
		elseif ( isset($_REQUEST['wppa-fromp']) ) $page = $_REQUEST['wppa-fromp'];
		else $page = '0';
	}
	return $page;
}

// Get the albums by calling the theme module and do some parameter processing
// This is the main entrypoint for the wppa+ invocation, either 'by hand' or through the filter.
// As of version 3.0.0 this routine returns the entire html created by the invocation.
function wppa_albums($id = '', $type = '', $size = '', $align = '') {
global $wppa;
global $wppa_opt;
global $wppa_lang;
global $wppa_locale;

	wppa_dbg_msg('Entering wppa_albums');
	wppa_dbg_msg('Lang='.$wppa_lang.', Locale='.$wppa_locale.', Ajax='.$wppa['ajax'], 'red');
	
	// Process a user upload request, if any. Do it here: it may affect this occurences display
	wppa_user_upload();

	// First calculate the occurance
	if ( $wppa['ajax'] ) {
		$wppa['master_occur'] = $_GET['wppa-moccur'];
		if ( ! is_numeric($wppa['master_occur']) ) wp_die('Are you cheeting?');
		$wppa['fullsize'] = $_GET['wppa-size'];
		if ( isset($_GET['wppa-occur']) ) {
			$wppa['occur'] = $_GET['wppa-occur'];
			if ( ! is_numeric($wppa['occur']) ) wp_die('Are you cheeting?');
		}
		if ( isset($_GET['wppa-woccur']) ) {
			$wppa['widget_occur'] = $_GET['wppa-woccur'];
			if ( ! is_numeric($wppa['widget-occur']) ) wp_die('Are you cheeting?');
			$wppa['in_widget'] = true;
		}
	}
	else {
		$wppa['master_occur']++;
		if ( $wppa['in_widget'] ) $wppa['widget_occur']++;
		else $wppa['occur']++;
	}

	// There are 3 ways to get here:
	// in order of priority:
	// 1. The given query string applies to this invocation (occurrance)
	//    This invocation requires the ignorance of the filter results and the interpretation of the querystring.
	if ( ( wppa_get_get('occur') || wppa_get_get('woccur') ) &&								// There IS a query string. For bw compat, occur is required ...
//		 ( wppa_get_get('album', false) !== false || wppa_get_get('photo') ) &&				// ... but not sufficient. Must have at least also album or photo
		 ( ( $wppa['in_widget'] && $wppa['widget_occur'] == wppa_get_get('woccur') ) ||		// and it applies to ...
		 ( ! $wppa['in_widget'] && $wppa['occur'] == wppa_get_get('occur') ) )				// this occurrance
		 ) {
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
		$wppa['is_single'] 		= wppa_get_get('single', false);					// Is a one image slideshow	
		$wppa['topten_count'] 	= wppa_get_get('topten', '0');
		$wppa['is_topten'] 		= $wppa['topten_count'] != '0';
		$wppa['lasten_count'] 	= wppa_get_get('lasten', '0');
		$wppa['is_lasten'] 		= $wppa['lasten_count'] != '0';
		$wppa['comten_count'] 	= wppa_get_get('comten', '0');
		$wppa['is_comten']		= $wppa['comten_count'] != '0';
		$wppa['featen_count']	= wppa_get_get('featen', '0');
		$wppa['is_featen']		= $wppa['featen_count'] != '0';
		$wppa['is_tag']			= trim(trim(strip_tags(wppa_get_get('tag', false)), ','), ';');
		if ( $wppa['is_tag'] ) wppa_dbg_msg('Is Tag');
		else wppa_dbg_msg('Is NOT Tag');
		$wppa['photos_only'] 	= wppa_get_get('photos-only', false);
		$wppa['page'] 			= wppa_get_get('page', '');
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
		// Do Nothing in $wppa, all is set by filter
		if (isset($_REQUEST['wppa-searchstring'])) {
			wppa_dbg_msg('Searchstring = '.$_REQUEST['wppa-searchstring']);
			if ( $wppa['src'] ) wppa_dbg_msg('src is true');
			else wppa_dbg_msg('src is false');
		}
		else {
			wppa_dbg_msg('Searchstring is not set');
		}
		if ( $wppa['is_landing'] && ! $wppa['src'] ) {
			wppa_dbg_msg('Nothing to do...');
			wppa_reset_occurrance();
			return '';	// Do nothing on a landing page without a querystring while it is also not a search operation
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
	if ($wppa['start_album'] && !is_numeric($wppa['start_album'])) {
		wppa_dbg_msg('Unrecognized Album identification found: '.$wppa['start_album'], 'red', 'force');
		wppa_reset_occurrance();
		return;	// Forget this occurrance
	}
	if ( $wppa['start_album'] > '0' ) {	// -2 is #all
		if ( ! wppa_album_exists($wppa['start_album']) ) {
			wppa_dbg_msg('Album does not exist: '.$wppa['start_album'], 'red', 'force');
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

	// Is it an upload box?
	if ( $wppa['is_upload'] ) {
		wppa_upload_box();
	}
	// Is it newstyle single photo mediastyle?
	elseif ( $wppa['is_mphoto'] == '1' ) {
		wppa_mphoto();
	}
	// Is it newstyle single photo plain?
	elseif ( wppa_page('oneofone') ) {
		wppa_sphoto();
	}
	// The normal case
	else {
		if ( function_exists('wppa_theme') ) wppa_theme();	// Call the theme module
		else $wppa['out'] = '<span style="color:red">ERROR: Missing function wppa_theme(), check the installation of WPPA+. Remove customized wppa_theme.php</span>';
		global $wppa_version; 
		$expected_version = '4-9-10';
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

function wppa_reset_occurrance() {
global $wppa;
global $thumb;
global $album;
global $thumbs;

	$thumbs = false;
	$thumb = false;
	$album = false;
	
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
	$wppa['page'] 			= '';
	$wppa['is_upload'] 		= false;

}

// determine page
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

function wppa_upload_box() {
global $wppa;

	if ( wppa_switch('wppa_user_upload_login') ) {
		if ( ! is_user_logged_in() ) return;					// Must login
		if ( ! current_user_can('wppa_upload') ) return;		// No upload rights
	}

	if ( $wppa['start_album'] ) {
		if ( ! wppa_have_access($wppa['start_album']) ) return;	// No access to this album
	}
	else {
		if ( ! wppa_have_access('0') ) return;				// No access to any album
	}
	
	wppa_container('open');
	$wppa['out'] .= wppa_nltab('+').'<div id="wppa-upload-'.$wppa['master_occur'].'" class="wppa-box wppa-upload" style="'.__wcs('wppa-box').__wcs('wppa-upload').'">';
		wppa_user_upload_html($wppa['start_album'], wppa_get_container_width('netto'), 'uploadbox');
	$wppa['out'] .= wppa_nltab('-').'<div style="clear:both;"></div></div>';
	wppa_container('close');
}

// get id of coverphoto. does all testing
function wppa_get_coverphoto_id($xalb = '') {
	$result = wppa_get_coverphoto_ids($xalb, '1');

	if ( empty($result) ) return false;
	return $result['0'];
}

function wppa_get_coverphoto_ids($xalb = '', $count) {
global $wpdb;
global $album;
global $wppa;
	
	if ($xalb == '') {						// default album
		if (isset($album['id'])) $alb = $album['id'];
	}
	else {									// supplied album
		$alb = $xalb;
	}
	if ( is_numeric($alb) ) {					// find main id
		if ( $count == '1') {
			$id = $wpdb->get_var( $wpdb->prepare( "SELECT main_photo FROM " . WPPA_ALBUMS . " WHERE id = %s", $alb ) );
	
			if ( is_numeric($id) && $id > '0' ) {		// check if id belongs to album
				$ph_alb = $wpdb->get_var( $wpdb->prepare( "SELECT album FROM " . WPPA_PHOTOS . " WHERE id = %s", $id ) );
				wppa_dbg_q('Q2');
				if ( $ph_alb != $alb ) {				// main photo does no longer belong to album. Treat as random
					$id = '0';
				}
				$ids = array();
				$ids[] = $id;
			}
			else {	// random
				$temp = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `album` = %s AND `status` <> %s ORDER BY RAND() LIMIT %d", $alb, 'pending', $count ), ARRAY_A );
				wppa_dbg_q('Q3');
				$ids = array();
				foreach ( $temp as $item ) {
					$ids[] = $item['id'];
				}
			}
			return $ids;	
	
		}
		else {	// count > 1
			$id = $wpdb->get_var( $wpdb->prepare( "SELECT main_photo FROM " . WPPA_ALBUMS . " WHERE id = %s", $alb ) );
			if ( $id != '-1' ) {	// Default: Random
				$temp = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `album` = %s AND `status` <> 'pending' ORDER BY RAND(".$wppa['randseed'].") LIMIT %d", $alb, $count), ARRAY_A );
			}
			else { 					// Featured only
				$temp = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `album` = %s AND `status` = 'featured' ORDER BY `id` LIMIT %d", $alb, $count), ARRAY_A );
			}
			$ids = array();
			foreach ( $temp as $item ) {
				$ids[] = $item['id'];
			}
			return $ids;
		}
	}
	else return false;						// no album, no coverphoto
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

// loop album
function wppa_get_albums($album = false, $type = '') {
global $wpdb;
global $wppa;
global $wppa_opt;

	if ( $wppa['is_topten'] ) return false;
	if ( $wppa['is_lasten'] ) return false;
	if ( $wppa['is_comten'] ) return false;
	if ( $wppa['is_featen'] ) return false;
	if ( $wppa['is_tag'] ) return false;
	if ( $wppa['photos_only'] ) return false;
	
	if ( $wppa['master_occur'] == '1' && ! $wppa['in_widget'] ) $src = wppa_get_searchstring();
	else $src = '';
	
	if ( $src && $wppa_opt['wppa_photos_only'] ) return false;
	
	if ( strlen($src) && $wppa['master_occur'] == '1' && ! $wppa['in_widget'] ) {	// Search is in occur 1 only and not in a widget

		if ( wppa_switch('wppa_indexed_search') ) { 
			$final_array = array();
			$chunks = explode(',', stripslashes(strtolower($src)));
			// all chunks
			foreach ( $chunks as $chunk ) if ( strlen(trim($chunk)) ) {
			//	$words = explode(' ', trim($chunk));
				$words = wppa_index_raw_to_words($chunk);
				$album_array = array();
				// all words in the searchstring
				foreach ( $words as $word ) {	
					$word = trim($word);
					$vfy = $word;
					$vlen = strlen($vfy);
					if ( strlen($word) > 1 ) {
						if ( strlen($word) > 10 ) $word = substr($word, 0, 10);
				//		$wlen = strlen($word);
						$floor = $word;
						$ceil = substr($floor, 0, strlen($floor) - 1).chr(ord(substr($floor, strlen($floor) - 1))+1);
						$aidxs = $wpdb->get_results("SELECT `slug`, `albums` FROM `".WPPA_INDEX."` WHERE `slug` >= '".$floor."' AND `slug` < '".$ceil."'", ARRAY_A);
						$albums = '';
						if ( $aidxs ) {
							foreach ( $aidxs as $ai ) {
								if ( substr($ai['slug'], 0, $vlen) == $vfy ) $albums .= $ai['albums'].',';
							}
						}
						$album_array[] = wppa_index_string_to_array(trim($albums, ','));
					}
					else echo '<script type="text/javascript">alert(\'Search tokens smaller than 2 characters are ignored!\');</script>';
				}
				// Must meet all words: intersect photo sets			
				foreach ( array_keys($album_array) as $idx ) {
					if ( $idx > '0' ) {
						$album_array['0'] = array_intersect($album_array['0'], $album_array[$idx]);
					}				
				}
				// Save partial result
				$final_array = array_merge($final_array, $album_array['0']);
			}
			
			// Compose WHERE clause
			$selection = " `id` = '0' ";
			foreach ( array_keys($final_array) as $p ) {
				$selection .= "OR `id` = '".$final_array[$p]."' ";
			}

			// Check maximum
			if ( count($final_array) > $wppa_opt['wppa_max_search_albums'] && $wppa_opt['wppa_max_search_albums'] != '0' ) {
				echo '<script type="text/javascript">alert(\'There are '.count($final_array).' albums found. Only the first '.$wppa_opt['wppa_max_search_albums'].' will be shown.\n Please refine your search criteria.\');</script>';
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
			$albs = $wpdb->get_results( 'SELECT * FROM ' . WPPA_ALBUMS . ' ' . wppa_get_album_order() , ARRAY_A );
			wppa_dbg_q('Q10');
			$albums = '';
			$idx = '0';
			foreach ($albs as $album) if (!$wppa_opt['wppa_excl_sep'] || $album['a_parent'] != '-1') {
				if (wppa_deep_stristr(wppa_qtrans($album['name']).' '.wppa_qtrans($album['description']), $src)) {
					$albums[$idx] = $album;
					$idx++;
				}
			}
		}
		if (is_array($albums)) $wppa['any'] = true;
	}
	else {
		if ( $wppa['src'] && $wppa['master_occur'] == '1' && ! $wppa['in_widget'] ) return false;	// empty search string

		if ($wppa['in_widget']) {
			$occur = wppa_get_get('woccur', '0');
		}
		else {
			$occur = wppa_get_get('occur', '0');
		}
		
		// Check if querystring given This has the highest priority in case of matching occurrance
		// Obey querystring only if the global occurence matches the occurence in the querystring, or no query occurrence given.
		$ref_occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];

		if (($occur == $ref_occur) && wppa_get_get('album')) {
			$id = wppa_get_get('album');
			$wppa['is_cover'] = wppa_get_get('cover');
		}
		// Check if parameters set
		elseif (is_numeric($album)) {
			$id = $album;
			if ($type == 'album') $wppa['is_cover'] = '0';
			if ($type == 'cover') $wppa['is_cover'] = '1';
		}
		// Check if globals set
		elseif (is_numeric($wppa['start_album'])) {
			$id = $wppa['start_album'];
		}
		// The default: all albums with parent = 0;
		else $id = '0';
		
		// Top-level album has no cover
		if ($id == '0') $wppa['is_cover'] = '0';
		
		// Do the query
		if (is_numeric($id)) {
			if ($wppa['is_cover']) $q = $wpdb->prepare('SELECT * FROM ' . WPPA_ALBUMS . ' WHERE `id`= %s', $id);
			else $q = $wpdb->prepare('SELECT * FROM ' . WPPA_ALBUMS . ' WHERE `a_parent`= %s '. wppa_get_album_order($id), $id);
			wppa_dbg_q('Q11');
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

// get number of photos in album 
function wppa_get_photo_count($xid = '') {
global $wpdb;
global $album;
    
    if (is_numeric($xid)) $id = $xid; else $id = $album['id'];
    $count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM " . WPPA_PHOTOS . " WHERE album = %s AND ( status <> %s OR owner = %s )", $id, 'pending', wppa_get_user() ) );
	wppa_dbg_q('Q12v');
	return $count;
}

// get number of albums in album 
function wppa_get_album_count($xid = '') {
global $wpdb;
global $album;
    
    if (is_numeric($xid)) $id = $xid; else $id = $album['id'];
    $count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM " . WPPA_ALBUMS . " WHERE a_parent=%s", $id ) );
	wppa_dbg_q('Q13v');
    return $count;
}

// get number of albums in system
function wppa_get_total_album_count() {
global $wpdb;

	$count = $wpdb->get_var("SELECT COUNT(*) FROM `".WPPA_ALBUMS."`");
	wppa_dbg_q('Q14');
	return $count;
}

// get youngest photo id
function wppa_get_youngest_photo_id() {
global $wpdb;

	$result = $wpdb->get_var( "SELECT `id` FROM `" . WPPA_PHOTOS . "` WHERE `status` <> 'pending' ORDER BY `id` DESC LIMIT 1" );
	wppa_dbg_q('Q15');
	return $result;
}

// get youngest album id
function wppa_get_youngest_album_id() {
global $wpdb;
	
	$result = $wpdb->get_var( "SELECT `id` FROM `" . WPPA_ALBUMS . "` ORDER BY `id` DESC LIMIT 1" );
	wppa_dbg_q('Q16');
	return $result;
}

// get youngest album name
function wppa_get_youngest_album_name() {
global $wpdb;
	
	$result = $wpdb->get_var( "SELECT `name` FROM `" . WPPA_ALBUMS . "` ORDER BY `id` DESC LIMIT 1" );
	wppa_dbg_q('Q17');
	return stripslashes($result);
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

// loop thumbs
function wppa_get_thumbs() {
global $wpdb;
global $wppa;
global $wppa_opt;
global $thumbs;

	wppa_dbg_msg('get_thumbs entered: '.$wppa['master_occur']);
	
	if ( is_array($thumbs) ) {	// Done already?
		wppa_dbg_msg('cached thumbs used');
		return $thumbs;	
	}
	
	$t = -microtime(true);
	
	if ( $wppa['master_occur'] == '1' && ! $wppa['in_widget'] ) $src = wppa_get_searchstring();
	else $src = '';
	
	// Single image slideshow?
	if ( $wppa['start_photo'] && $wppa['is_single'] ) {
		$thumbs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `id` = %s', $wppa['start_photo'] ) , ARRAY_A );
		wppa_dbg_q('Q18');
	}
	
	// Topten?	
	elseif ( $wppa['is_topten'] ) {
		$max = $wppa['topten_count'];
		$alb = $wppa['start_album'];
		if ($alb) $thumbs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `mean_rating` > 0 AND `album` = %s ORDER BY `mean_rating` DESC LIMIT %d', $alb, $max ) , ARRAY_A );
		else $thumbs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `mean_rating` > 0 ORDER BY `mean_rating` DESC LIMIT %d', $max ) , ARRAY_A );
		wppa_dbg_q('Q19');
	}
	// Featen?
	elseif ( $wppa['is_featen'] ) {
		$max = $wppa['featen_count'];
		$alb = $wppa['start_album'];
		if ($alb) $thumbs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `status` = %s AND `album` = %s ORDER BY RAND('.$wppa['randseed'].') DESC LIMIT %d', 'featured', $alb, $max ) , ARRAY_A );
		else $thumbs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `status` = %s ORDER BY RAND('.$wppa['randseed'].') DESC LIMIT %d', 'featured', $max ) , ARRAY_A );
		wppa_dbg_q('Q20');
	}	
	// Lasten?
	elseif ( $wppa['is_lasten'] ) {
		$max = $wppa['lasten_count'];
		$alb = $wppa['start_album'];
		if (wppa_is_enum($alb)) $alb = explode(',', $alb);
		if ( is_array($alb) ) $alb = implode("' OR `album` = '", $alb);
		if ( current_user_can('wppa_moderate') ) {
			if ($alb) $thumbs = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `album` = '".$alb."' ORDER BY `timestamp` DESC LIMIT " . $max, ARRAY_A );
			else $thumbs = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` ORDER BY `timestamp` DESC LIMIT " . $max, ARRAY_A );
		}
		else {
			if ($alb) $thumbs = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` WHERE ( `album` = '".$alb."' ) AND `status` <> 'pending' ORDER BY `timestamp` DESC LIMIT " . $max, ARRAY_A );
			else $thumbs = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `status` <> 'pending' ORDER BY `timestamp` DESC LIMIT " . $max, ARRAY_A );
		}
		wppa_dbg_q('Q21');
	}
	// Comten?
	elseif ( $wppa['is_comten'] ) {
		$comments = $wpdb->get_results( "SELECT * FROM `".WPPA_COMMENTS."` WHERE `status` = 'approved' ORDER BY `timestamp` DESC", ARRAY_A );
		wppa_dbg_q('Q23');
		$max = $wppa_opt['wppa_comten_count'];
		$alb = $wppa['start_album'];
		if ( wppa_is_enum($alb) ) $alb = explode(',', $alb);
		$thumbs = false;
		$indexes = false;
		$indexes[] = '-1';
		$count = '0';
		if ( $comments ) foreach ( $comments as $comment ) {
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
	// Tagcloud or multitag?
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
			if ( $in ) $thumbs[] = $temp[$index];
		}
	}
	// Search?
	elseif ( strlen($src) && $wppa['master_occur'] == '1' && ! $wppa['in_widget'] ) {	// Search is in occur 1 only and not in a widget

		// Indexed search??
		if ( wppa_switch('wppa_indexed_search') ) { 
			$final_array = array();
			$chunks = explode(',', stripslashes(strtolower($src)));
			// all chunks
			foreach ( $chunks as $chunk ) if ( strlen(trim($chunk)) ) {
			//	$words = explode(' ', trim($chunk));
				$words = wppa_index_raw_to_words($chunk);
				$photo_array = array();
				// all words in the searchstring
				foreach ( $words as $word ) {	
					$word = trim($word);
					$vfy = $word;
					$vlen = strlen($vfy);
					if ( strlen($word) > 1 ) {
						if ( strlen($word) > 10 ) $word = substr($word, 0, 10);
						$floor = $word;
						$ceil = substr($floor, 0, strlen($floor) - 1).chr(ord(substr($floor, strlen($floor) - 1))+1);
						$pidxs = $wpdb->get_results("SELECT `slug`, `photos` FROM `".WPPA_INDEX."` WHERE `slug` >= '".$floor."' AND `slug` < '".$ceil."'", ARRAY_A);
						$photos = '';
						if ( $pidxs ) {
							foreach ( $pidxs as $pi ) {
								if ( substr($pi['slug'], 0, $vlen) == $vfy ) $photos .= $pi['photos'].',';
							}
						}
						$photo_array[] = wppa_index_string_to_array(trim($photos, ','));
					}
					else echo '<script type="text/javascript">alert(\'Search tokens smaller than 2 characters are ignored!\');</script>';
				}
				// Must meet all words: intersect photo sets			
				foreach ( array_keys($photo_array) as $idx ) {
					if ( $idx > '0' ) {
						$photo_array['0'] = array_intersect($photo_array['0'], $photo_array[$idx]);
					}				
				}
				// Save partial result
				$final_array = array_merge($final_array, $photo_array['0']);
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
				echo '<script type="text/javascript">alert(\'There are '.count($final_array).' photos found. Only the first '.$wppa_opt['wppa_max_search_photos'].' will be shown.\n Please refine your search criteria.\');</script>';
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
			}
		}
		else { // Conventional search

			if ( current_user_can('wppa_moderate') ) {
				$tmbs = $wpdb->get_results( "SELECT * FROM `" . WPPA_PHOTOS . "` " . wppa_get_photo_order('0'), ARRAY_A );
			}
			else {
				$tmbs = $wpdb->get_results( "SELECT * FROM `" . WPPA_PHOTOS . "` WHERE status <> 'pending' " . wppa_get_photo_order('0'), ARRAY_A );
			}

			wppa_dbg_q('Q25');
			$thumbs = array();
			foreach ( $tmbs as $thumb ) {
				if ( ! $wppa_opt['wppa_excl_sep'] || ! wppa_is_separate($thumb['album']) ) {	// Not exclude sepreate or not seperate
					$haystack = __($thumb['name']).' '.wppa_filter_exif(wppa_filter_iptc(__(stripslashes($thumb['description'])),$thumb['id']),$thumb['id']);
					if ( wppa_switch('wppa_search_tags') ) {
						$haystack .= ' '.str_replace(',', ' ', $thumb['tags']);
					}
					if ( wppa_deep_stristr(strtolower($haystack), $src) ) {
						$thumbs[] = $thumb;
					}
				}
			}

		}


		$wppa['any'] = ! empty ( $thumbs );
//echo 'there are '.count($thumbs).' in the selection';
	}
	else {
		if ( $wppa['src'] && $wppa['master_occur'] == '1' && ! $wppa['in_widget'] ) return false; 	// empty search string
		
		if ($wppa['in_widget']) {
			$occur = wppa_get_get('woccur', '0');
		}
		else {
			$occur = wppa_get_get('occur', '0');
		}
		
		// Obey querystring only if the global occurence matches the occurence in the querystring, or no query occurrence given.
		$ref_occur = $wppa['in_widget'] ? $wppa['widget_occur'] : $wppa['occur'];
		if ($occur == $ref_occur && wppa_get_get('album')) {
			$id = wppa_get_get('album');
		}
		elseif (is_numeric($wppa['start_album'])) $id = $wppa['start_album']; 
		else $id = 0;
		// The above is probably obsolete since wppa_albums() now always(?) fills $wppa['start_album']
		
		if (is_numeric($id)) {
			$wppa['current_album'] = $id;
			if ( $id == -2 ) {	// album == -2 is now: all albums
				if ( current_user_can('wppa_moderate') ) {
					$thumbs = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` ".wppa_get_photo_order('0'), ARRAY_A ); 
				}
				else {
					$thumbs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".WPPA_PHOTOS." WHERE ( status <> %s OR owner = %s) ".wppa_get_photo_order('0'), 'pending', wppa_get_user() ), ARRAY_A ); 
				}
				wppa_dbg_q('Q26');
			}
			else {
				if ( current_user_can('wppa_moderate') ) {
					$thumbs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".WPPA_PHOTOS." WHERE album = %s ".wppa_get_photo_order($id), $id ), ARRAY_A ); 
				}
				else {
					$thumbs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".WPPA_PHOTOS." WHERE album = %s AND ( status <> %s OR owner = %s) ".wppa_get_photo_order($id), $id, 'pending', wppa_get_user() ), ARRAY_A ); 
				}
				wppa_dbg_q('Q27');
			}
		}
		else {
			$thumbs = false;
		}
	}
	$wppa['thumb_count'] = count($thumbs);
	$t += microtime(true);
	wppa_dbg_msg('Get thumbs took '.$t.' seconds.', 'red');
	return $thumbs;
}

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

// get full img style
function wppa_get_fullimgstyle($id = '') {
	$temp = wppa_get_fullimgstyle_a($id);
	if ( is_array($temp) ) return $temp['style'];
	else return '';
}

function wppa_get_fullimgstyle_a($id) {
global $wppa;
global $wppa_opt;
global $thumb;

	if (!is_numeric($wppa['fullsize']) || $wppa['fullsize'] < '1') $wppa['fullsize'] = $wppa_opt['wppa_fullsize'];

	$wppa['enlarge'] = $wppa_opt['wppa_enlarge'];

	wppa_cache_thumb($id);

	$img_path = wppa_get_photo_path($id);
	$result = wppa_get_imgstyle_a($img_path, $wppa['fullsize'], 'optional', 'fullsize');
	return $result;
}

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
//if ( $wppa['start_photo'] != $photo ) wppa_dbg_msg('Hier gaat het mis', 'red');
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
		$avgrat = wppa_get_rating_by_id($id, 'nolabel').'|'.wppa_get_rating_count_by_id($id);
		
		// Find the dilike count
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
	$usethumb = wppa_use_thumb_file($id, $style_a['width'], $style_a['height']);
	$photourl = $usethumb ? wppa_get_thumb_url($id) : wppa_get_photo_url($id);
	$photourl = str_replace(WPPA_UPLOAD_URL, '', $photourl);

	// Find iptc data
	$iptc = ( $wppa_opt['wppa_show_iptc'] && ! $wppa['is_slideonly'] && ! $wppa['is_filmonly'] ) ? wppa_iptc_html($id) : '';
	
	// Find EXIF data
	$exif = ( $wppa_opt['wppa_show_exif'] && ! $wppa['is_slideonly'] && ! $wppa['is_filmonly'] ) ? wppa_exif_html($id) : '';
	
	// Lightbox subtitle
	$doit = false;
	if ( $wppa_opt['wppa_slideshow_linktype'] == 'lightbox' ) $doit = true;					// For fullsize
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
		$desc = wppa_get_photo_desc($id, $wppa_opt['wppa_allow_foreign_shortcodes']);	// Foreign shortcodes is handled here
		
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
	
	if ( ! $wppa['is_filmonly'] ) {
		if ( current_user_can('wppa_admin') ) {
			$desc = '<div style="float:right; margin-right:6px;" ><a href="'.get_admin_url().'admin.php?page=wppa_admin_menu&amp;tab=pedit&amp;photo='.$thumb['id'].'" target="_blank" >'.__a('Edit').'</a></div><br />'.$desc;
		}
		elseif ( current_user_can('wppa_upload') && $wppa_opt['wppa_upload_edit'] && $thumb['owner'] == wppa_get_user() ) {
			$desc = '<div style="float:right; margin-right:6px;" ><a href="'.get_admin_url().'admin.php?page=wppa_edit_photo&amp;photo='.$thumb['id'].'" target="_blank" >'.__a('Edit').'</a></div><br />'.$desc;
		}
	}
	
	if ( $thumb['status'] == 'pending' ) {
		$desc .= wppa_html(esc_js(wppa_moderate_links('slide', $id)));
	}

	// Share HTML 
	$sharehtml = ( $wppa['is_filmonly'] || $wppa['is_slideonly'] ) ? '' : wppa_get_share_html();

	// Produce final result
    $result = "'".$wppa['master_occur']."','";
	$result .= $index."','";
	$result .= $photourl."','";
	$result .= $style_a['style']."','";
	$result .= $style_a['width']."','";
	$result .= $style_a['height']."','";
	$result .= $fullname."','";
	$result .= $name."','";
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
	$result .= $shareurl."','";	// Used for history.pushstate()
	$result .= $sharehtml."'";	// The content of the SM (share) box
	
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
	$photo = wppa_get_get('photo');	
	$user = wppa_get_post('comname');
	if ( !$user ) die('Illegal attempt to enter a comment');
	$email = wppa_get_post('comemail');
	if ( !$email ) {
		if ( $wppa_opt['wppa_comment_email_required'] ) die('Illegal attempt to enter a comment');
		else $email = wppa_get_user();	// If email not present and not required, use his IP
	}
	
	// Retrieve and filter comment
	$comment = wppa_get_post('comment');
	$comment = trim($comment);
	$comment = stripslashes($comment);
	$comment = wppa_strip_tags($comment, 'script&style');
	$comment = htmlspecialchars($comment);
	
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

	// Editing a comment?
	$cedit = wppa_get_post('comment-edit');
	
	// Check captcha
	if ( $wppa_opt['wppa_comment_captcha'] ) {
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
			$query = $wpdb->prepare('UPDATE `'.WPPA_COMMENTS.'` SET `comment` = %s, `user` = %s, `email` = %s, `status` = %s, `timestamp` = %s WHERE `id` = %s LIMIT 1', $comment, $user, $email, $status, time(), $cedit);
			wppa_dbg_q('Q44');
			$iret = $wpdb->query($query);
			if ($iret !== false) {
				$wppa['comment_id'] = $cedit;
			}
		}
		else {
			// See if a refresh happened
			$old_entry = $wpdb->prepare('SELECT * FROM `'.WPPA_COMMENTS.'` WHERE `photo` = %s AND `user` = %s AND `comment` = %s LIMIT 1', $photo, $user, $comment);
			wppa_dbg_q('Q45');
			$iret = $wpdb->query($old_entry);
			if ($iret) {
				if ($wppa['debug']) echo('<script type="text/javascript">alert("Duplicate comment ignored")</script>');
				return;
			}
			$key = wppa_nextkey(WPPA_COMMENTS);
			$query = $wpdb->prepare('INSERT INTO `'.WPPA_COMMENTS.'` (`id`, `timestamp`, `photo`, `user`, `email`, `comment`, `status`, `ip`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s )', $key, $time, $photo, $user, $email, $comment, $status, $_SERVER['REMOTE_ADDR']);
//			wppa_dbg_q('Q46');
			$iret = $wpdb->query($query);
			if ($iret !== false) $wppa['comment_id'] = $key;
		}
		if ( $iret !== false ) {
			if ( $status != 'spam' ) {
				if ($cedit) {
					if ( $wppa_opt['wppa_comment_notify_added'] ) echo('<script type="text/javascript">alert("'.__a('Comment edited').'")</script>');
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
						$moduser   = get_user_by('login', 'admin');
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
								elseif ( $wppa_opt['wppa_upload_edit'] ) $cont['3'] = $cont3a;
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
					if ( $wppa_opt['wppa_comment_notify_added'] ) echo('<script type="text/javascript">alert("'.__a('Comment added').'")</script>');
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
// Check tho comment security answer
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

// Build the html for the comment box
function wppa_comment_html($id, $comment_allowed) {
global $wpdb;
global $wppa;
global $wppa_opt;
global $current_user;
global $wppa_first_comment_html;

	$result = '';
	if ($wppa['in_widget']) return $result;		// NOT in a widget
	
	// Find out who we are either logged in or not
	$vis = is_user_logged_in() ? $vis = 'display:none; ' : '';
	if (!$wppa_first_comment_html) {
		$wppa_first_comment_html = true;
		// Find user
		if (wppa_get_post('comname')) $wppa['comment_user'] = wppa_get_post('comname');
		if (wppa_get_post('comemail')) $wppa['comment_email'] = wppa_get_post('comemail');
		elseif (is_user_logged_in()) {
			get_currentuserinfo();
			$wppa['comment_user'] = $current_user->display_name; //user_login;
			$wppa['comment_email'] = $current_user->user_email;
		}
	}

	// Loop the comments already there
	$n_comments = 0;
	if ($wppa_opt['wppa_comments_desc']) $ord = 'DESC'; else $ord = '';
	$comments = $wpdb->get_results($wpdb->prepare( 'SELECT * FROM '.WPPA_COMMENTS.' WHERE photo = %s ORDER BY id '.$ord, $id ), ARRAY_A );
	wppa_dbg_q('Q46v');
	$com_count = count($comments);
	$color = 'darkgrey';
	if ($wppa_opt['wppa_fontcolor_box']) $color = $wppa_opt['wppa_fontcolor_box'];
	if ($comments) {
		$result .= '<div id="wppa-comtable-wrap-'.$wppa['master_occur'].'" style="display:none;" >';
			$result .= '<table id="wppacommentstable-'.$wppa['master_occur'].'" class="wppa-comment-form" style="margin:0; "><tbody>';
			foreach($comments as $comment) {
				// Show a comment either when it is approved, or it is pending and mine or i am a moderator
				if ($comment['status'] == 'approved' || current_user_can('wppa_moderate') || current_user_can('wppa_comments') || (($comment['status'] == 'pending' || $comment['status'] == 'spam') && $comment['user'] == $wppa['comment_user'])) {
					$n_comments++;
					$result .= '<tr class="wppa-comment-'.$comment['id'].'" valign="top" style="border-bottom:0 none; border-top:0 none; border-left: 0 none; border-right: 0 none; " >';
						$result .= '<td valign="top" class="wppa-box-text wppa-td" style="vertical-align:top; width:30%; border-width: 0 0 0 0; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >';
							$result .= $comment['user'].' '.__a('wrote:');
							$result .= '<br /><span style="font-size:9px; ">'.wppa_get_time_since($comment['timestamp']).'</span>';
							if ( $wppa_opt['wppa_comment_gravatar'] != 'none') {
								// Find the default
								if ( $wppa_opt['wppa_comment_gravatar'] != 'url') {
									$default = $wppa_opt['wppa_comment_gravatar'];
								}
								else {
									$default = $wppa_opt['wppa_comment_gravatar_url'];
								}
								// Find the avatar
								$avt = '';
								$usr = get_user_by('login', $comment['user']);
								if ( $usr ) {	// Local Avatar ?
									$avt = str_replace("'", "\"", get_avatar($usr->ID, $wppa_opt['wppa_gravatar_size'], $default));
								}
								if ( $avt == '' ) {	// Global avatars off, try myself
									$avt = '<img class="wppa-box-text wppa-td" src="http://www.gravatar.com/avatar/'.md5(strtolower(trim($comment['email']))).'.jpg?d='.urlencode($default).'&s='.$wppa_opt['wppa_gravatar_size'].'" />';
								}
								// Compose the html
								$result .= '<div class="com_avatar">'.$avt.'</div>';
							}
						$result .= '</td>';
						$txtwidth = floor( wppa_get_container_width() * 0.7 ).'px';
						$result .= '<td class="wppa-box-text wppa-td" style="width:70%; word-wrap:break-word; border-width: 0 0 0 0;'.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.
										'<p class="wppa-comment-textarea-'.$wppa['master_occur'].'" style="margin:0; background-color:transparent; width:'.$txtwidth.'; max-height:90px; overflow:auto; word-wrap:break-word;'.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.
											html_entity_decode(esc_js(stripslashes(convert_smilies($comment['comment']))));
										
											if ( $comment['status'] != 'approved' && ( current_user_can('wppa_moderate') || current_user_can('wppa_comments') ) ) {
												$result .= wppa_html(esc_js(wppa_moderate_links('comment', $id, $comment['id'])));
											}
											elseif ($comment['status'] == 'pending' && $comment['user'] == $wppa['comment_user']) {
												$result .= '<br /><span style="color:red; font-size:9px;" >'.__a('Awaiting moderation').'</span>';
											}
											elseif ($comment['status'] == 'spam' && $comment['user'] == $wppa['comment_user']) {
												$result .= '<br /><span style="color:red; font-size:9px;" >'.__a('Marked as spam').'</span>';
											}
											
											
											$result .= '</p>';
						$result .= '</td>';
					$result .= '</tr>';
					$result .= '<tr class="wppa-comment-'.$comment['id'].'"><td colspan="2" style="padding:0"><hr style="background-color:'.$color.'; margin:0;" /></td></tr>';
				}
			}
			$result .= '</tbody></table>';
		$result .= '</div>';
	}
	
	// See if we are currently in the process of adding/editing this comment
	$is_current = ($id == $wppa['comment_photo'] && $wppa['comment_id']);
	if ($is_current) {
		$txt = $wppa['comment_text'];
		$btn = __a('Edit!');
	}
	else {
		$txt = '';
		$btn = __a('Send!');
	}
	
	// Prepare the callback url
	$returnurl = wppa_get_permalink();

	$album = wppa_get_get('album');
	if ( $album !== false ) $returnurl .= 'wppa-album='.$album.'&';
	$cover = wppa_get_get('cover');
	if ($cover) $returnurl .= 'wppa-cover='.$cover.'&';
	$slide = wppa_get_get('slide');
	if ($slide !== false) $returnurl .= 'wppa-slide&';
	$occur = wppa_get_get('occur');
	if ($occur) $returnurl .= 'wppa-occur='.$occur.'&';
	$lasten = wppa_get_get('lasten');
	if ( $lasten ) $returnurl .= 'wppa-lasten='.$lasten.'&';
	$topten = wppa_get_get('topten');
	if ( $topten ) $returnurl .= 'wppa-topten='.$topten.'&';
	$comten = wppa_get_get('comten');
	if ( $comten ) $returnurl .= 'wppa-comten='.$comten.'&';
	$tag = wppa_get_get('tag');
	if ( $tag ) $returnurl .= 'wppa-tag='.$tag.'&';
	
	$returnurl .= 'wppa-photo='.$id;
	
	// The comment form
	if ( $comment_allowed ) {
		$result .= '<div id="wppa-comform-wrap-'.$wppa['master_occur'].'" style="display:none;" >';
			$result .= '<form id="wppa-commentform-'.$wppa['master_occur'].'" class="wppa-comment-form" action="'.$returnurl.'" method="post" style="" onsubmit="return wppaValidateComment('.$wppa['master_occur'].')">';
				$result .= wp_nonce_field('wppa-check' , 'wppa-nonce', false, false);
				if ($album) $result .= '<input type="hidden" name="wppa-album" value="'.$album.'" />';
				if ($cover) $result .= '<input type="hidden" name="wppa-cover" value="'.$cover.'" />';
				if ($slide) $result .= '<input type="hidden" name="wppa-slide" value="'.$slide.'" />';
				if ($is_current) $result .= '<input type="hidden" name="wppa-comment-edit" value="'.$wppa['comment_id'].'" />';
				$result .= '<input type="hidden" name="wppa-occur" value="'.$wppa['occur'].'" />';

				$result .= '<table id="wppacommenttable-'.$wppa['master_occur'].'" style="margin:0;">';
					$result .= '<tbody>';
						$result .= '<tr valign="top" style="'.$vis.'">';
							$result .= '<td class="wppa-box-text wppa-td" style="width:30%; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.__a('Your name:').'</td>';
							$result .= '<td class="wppa-box-text wppa-td" style="width:70%; '.__wcs('wppa-box-text').__wcs('wppa-td').'" ><input type="text" name="wppa-comname" id="wppa-comname-'.$wppa['master_occur'].'" style="width:100%; " value="'.$wppa['comment_user'].'" /></td>';
						$result .= '</tr>';
						if ( $wppa_opt['wppa_comment_email_required'] ) {
							$result .= '<tr valign="top" style="'.$vis.'">';
								$result .= '<td class="wppa-box-text wppa-td" style="width:30%; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.__a('Your email:').'</td>';
								$result .= '<td class="wppa-box-text wppa-td" style="width:70%; '.__wcs('wppa-box-text').__wcs('wppa-td').'" ><input type="text" name="wppa-comemail" id="wppa-comemail-'.$wppa['master_occur'].'" style="width:100%; " value="'.$wppa['comment_email'].'" /></td>';
							$result .= '</tr>';
						}
						$result .= '<tr valign="top" style="vertical-align:top;">';	
							$result .= '<td valign="top" class="wppa-box-text wppa-td" style="vertical-align:top; width:30%; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.__a('Your comment:').'<br />'.$wppa['comment_user'].'<br />';
							if ( $wppa_opt['wppa_comment_captcha'] ) {
								$wid = '20%';
								if ( $wppa_opt['wppa_fontsize_box'] ) $wid = ($wppa_opt['wppa_fontsize_box'] * 1.5 ).'px';
								$captkey = $id;
								if ( $is_current ) $captkey = $wpdb->get_var($wpdb->prepare('SELECT `timestamp` FROM `'.WPPA_COMMENTS.'` WHERE `id` = %s', $wppa['comment_id'])); 
								$result .= wppa_make_captcha($captkey).'<input type="text" name="wppa-captcha" style="width:'.$wid.'; '.__wcs('wppa-box-text').__wcs('wppa-td').'" />&nbsp;';
							}
							$result .= '<input type="submit" name="commentbtn" value="'.$btn.'" style="margin:0;" /></td>';
							$result .= '<td valign="top" class="wppa-box-text wppa-td" style="vertical-align:top; width:70%; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >';
/*							if ( $wppa_opt['wppa_use_wp_editor'] ) {
								$quicktags_settings = array( 'buttons' => 'strong,em,link,block,ins,ul,ol,li,code,close' );
								ob_start();
								wp_editor(stripslashes($txt), 'wppacomment'.wppa_alfa_id($id), array('wpautop' => false, 'media_buttons' => false, 'textarea_rows' => '6', 'tinymce' => false, 'quicktags' => $quicktags_settings ));
								$editor = ob_get_clean();
								$result .= str_replace("'", '"', $editor);
							}
							else {
/**/
								$result .= '<textarea name="wppa-comment" id="wppa-comment-'.$wppa['master_occur'].'" style="height:60px; width:100%; ">'.esc_textarea(stripslashes($txt)).'</textarea>';
/*							}
/* */
							$result .= '</td>';
						$result .= '</tr>';
					$result .= '</tbody>';
				$result .= '</table>';
			$result .= '</form>';
		$result .= '</div>';
	}
	else {
		$result .= sprintf(__a('You must <a href="%s">login</a> to enter a comment'), site_url('wp-login.php', 'login'));
	}
	
	$result .= '<div id="wppa-comfooter-wrap-'.$wppa['master_occur'].'" style="display:block;" >';
		$result .= '<table id="wppacommentfooter-'.$wppa['master_occur'].'" class="wppa-comment-form" style="margin:0;">';
			$result .= '<tbody><tr style="text-align:center; "><td style="text-align:center; cursor:pointer;'.__wcs('wppa-box-text').'" ><a onclick="wppaOpenComments('.$wppa['master_occur'].', -1); return false;">'; // wppaStartStop('.$wppa['master_occur'].', -1); return false;">';
			if ( $n_comments ) {
				$result .= sprintf(__a('%d  comments'), $n_comments);
			}
			else {
				if ( $comment_allowed ) {
					$result .= __a('Leave a comment');
				}
			}
		$result .= '</a></td></tr></tbody></table>';
	$result .= '</div>';

	return $result;
}

function wppa_iptc_html($photo) {
global $wppa;
global $wpdb;
global $wppaiptcdefaults;
global $wppaiptclabels;

	// Get the default (one time only)
	if ( ! $wppa['iptc'] ) {
		$tmp = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_IPTC."` WHERE `photo` = %s ORDER BY `tag`", '0'), "ARRAY_A");
		wppa_dbg_q('Q47');
		if ( ! $tmp ) return '';	// Nothing defined
		$wppaiptcdefaults = false;	// Init
		$wppaiptclabels = false;	// Init
		foreach ($tmp as $t) {
			$wppaiptcdefaults[$t['tag']] = $t['status'];
			$wppaiptclabels[$t['tag']] = $t['description'];
		}
		$wppa['iptc'] = true;
	}
	else wppa_dbg_q('G47');

	$count = 0;

	// Get the photo data
	$iptcdata = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_IPTC."` WHERE `photo` = %s ORDER BY `tag`", $photo), "ARRAY_A");
	wppa_dbg_q('Q48v');
	if ( $iptcdata ) {
		// Open the container content
		$result = '<div id="iptccontent-'.$wppa['master_occur'].'" >';
		// Process data
		$onclick = esc_attr("wppaStopShow(".$wppa['master_occur']."); jQuery('.wppa-iptc-table-".$wppa['master_occur']."').css('display', ''); jQuery('.-wppa-iptc-table-".$wppa['master_occur']."').css('display', 'none')");
		$result .= '<a class="-wppa-iptc-table-'.$wppa['master_occur'].'" onclick="'.$onclick.'" style="cursor:pointer;" >'.__a('Show IPTC data').'</a>';

		$onclick = esc_attr("jQuery('.wppa-iptc-table-".$wppa['master_occur']."').css('display', 'none'); jQuery('.-wppa-iptc-table-".$wppa['master_occur']."').css('display', '')");
		$result .= '<a class="wppa-iptc-table-'.$wppa['master_occur'].'" onclick="'.$onclick.'" style="display:none; cursor:pointer;" >'.__a('Hide IPTC data').'</a>';

		$result .= '<table class="wppa-iptc-table-'.$wppa['master_occur'].' wppa-detail" style="display:none; border:0 none; margin:0;" ><tbody>';
		$oldtag = '';
		foreach ( $iptcdata as $iptcline ) {
			if ( $iptcline['status'] == 'hide' ) continue;														// Photo status is hide
			if ( $iptcline['status'] == 'default' && $wppaiptcdefaults[$iptcline['tag']] == 'hide' ) continue;	// P s is default and default is hide
			if ( $iptcline['status'] == 'default' && $wppaiptcdefaults[$iptcline['tag']] == 'option' && ! trim($iptcline['description'], "\x00..\x1F ") ) continue;	// P s is default and default is optional and field is empty
			
			$count++;
			$newtag = $iptcline['tag'];
			if ( $newtag != $oldtag && $oldtag != '') $result .= '</td></tr>';	// Close previous line
			if ( $newtag == $oldtag ) {
				$result .= '; ';							// next item with same tag
			}
			else {
				$result .= '<tr style="border-bottom:0 none; border-top:0 none; border-left: 0 none; border-right: 0 none; "><td class="wppa-iptc-label wppa-box-text wppa-td" style="'.__wcs('wppa-box-text').__wcs('wppa-td').'" >';						// Open new line
				$result .= esc_js(__($wppaiptclabels[$newtag]));
				$result .= '</td><td class="wppa-iptc-value wppa-box-text wppa-td" style="'.__wcs('wppa-box-text').__wcs('wppa-td').'" >';
			}
			$result .= esc_js(trim(__($iptcline['description'])));
			$oldtag = $newtag;
		}	
		if ( $oldtag != '' ) $result .= '</td></tr>';	// Close last line
		$result .= '</tbody></table></div>';
	}
	if ( ! $count ) {
		$result = '<div id="iptccontent-'.$wppa['master_occur'].'" >'.__a('No IPTC data').'</div>';
	}

	return ($result);
}

function wppa_exif_html($photo) {
global $wppa;
global $wpdb;
global $wppaexifdefaults;
global $wppaexiflabels;

	// Get the default (one time only)
	if ( ! $wppa['exif'] ) {
		$tmp = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_EXIF."` WHERE `photo` = %s ORDER BY `tag`", '0'), "ARRAY_A");
		wppa_dbg_q('Q49');
		if ( ! $tmp ) return '';	// Nothing defined
		$wppaexifdefaults = false;	// Init
		$wppaexiflabels = false;	// Init
		foreach ($tmp as $t) {
			$wppaexifdefaults[$t['tag']] = $t['status'];
			$wppaexiflabels[$t['tag']] = $t['description'];
		}
		$wppa['exif'] = true;
	}
	else wppa_dbg_q('G49');

	$count = 0;

	// Get the photo data
	$exifdata = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_EXIF."` WHERE `photo`=%s ORDER BY `tag`", $photo), "ARRAY_A");
	wppa_dbg_q('Q50v');
	if ( $exifdata ) {
		// Open the container content
		$result = '<div id="exifcontent-'.$wppa['master_occur'].'" >';
		// Process data
		$onclick = esc_attr("wppaStopShow(".$wppa['master_occur']."); jQuery('.wppa-exif-table-".$wppa['master_occur']."').css('display', ''); jQuery('.-wppa-exif-table-".$wppa['master_occur']."').css('display', 'none')");
		$result .= '<a class="-wppa-exif-table-'.$wppa['master_occur'].'" onclick="'.$onclick.'" style="cursor:pointer;" >'.__a('Show EXIF data').'</a>';

		$onclick = esc_attr("jQuery('.wppa-exif-table-".$wppa['master_occur']."').css('display', 'none'); jQuery('.-wppa-exif-table-".$wppa['master_occur']."').css('display', '')");
		$result .= '<a class="wppa-exif-table-'.$wppa['master_occur'].'" onclick="'.$onclick.'" style="display:none; cursor:pointer;" >'.__a('Hide EXIF data').'</a>';

		$result .= '<table class="wppa-exif-table-'.$wppa['master_occur'].' wppa-detail" style="display:none; border:0 none; margin:0;" ><tbody>';
		$oldtag = '';
		foreach ( $exifdata as $exifline ) {
			if ( $exifline['status'] == 'hide' ) continue;														// Photo status is hide
			if ( $exifline['status'] == 'default' && $wppaexifdefaults[$exifline['tag']] == 'hide' ) continue;	// P s is default and default is hide
			if ( $exifline['status'] == 'default' && $wppaexifdefaults[$exifline['tag']] == 'option' && ! trim($exifline['description'], "\x00..\x1F ") ) continue; // P s is default and default is optional and field is empty

			$count++;
			$newtag = $exifline['tag'];
			if ( $newtag != $oldtag && $oldtag != '') $result .= '</td></tr>';	// Close previous line
			if ( $newtag == $oldtag ) {
				$result .= '; ';							// next item with same tag
			}
			else {
				$result .= '<tr style="border-bottom:0 none; border-top:0 none; border-left: 0 none; border-right: 0 none; "><td class="wppa-exif-label wppa-box-text wppa-td" style="'.__wcs('wppa-box-text').__wcs('wppa-td').'" >';						// Open new line
				$result .= esc_js(__($wppaexiflabels[$newtag]));
				$result .= '</td><td class="wppa-exif-value wppa-box-text wppa-td" style="'.__wcs('wppa-box-text').__wcs('wppa-td').'" >';
			}
			$result .= esc_js(trim(__(wppa_format_exif($exifline['tag'], $exifline['description']))));
			$oldtag = $newtag;
		}	
		if ( $oldtag != '' ) $result .= '</td></tr>';	// Close last line
		$result .= '</tbody></table></div>';
	}
	if ( ! $count ) {
		$result = '<div id="exifcontent-'.$wppa['master_occur'].'" >'.__a('No EXIF data').'</div>';
	}
	
	return ($result);
}

function wppa_get_imgstyle($file, $max_size, $xvalign = '', $type = '') {
	$result = wppa_get_imgstyle_a($file, $max_size, $xvalign, $type);
	return $result['style'];
}

function wppa_get_imgstyle_a($file, $xmax_size, $xvalign = '', $type = '') {
global $wppa;
global $wppa_opt;

	$result = Array( 'style' => '', 'width' => '', 'height' => '', 'cursor' => '' );	// Init 
	
	if ($file == '') return $result;					// no image: no dimensions
	if ( !is_file($file) ) {
		wppa_dbg_msg('Please check file '.$file.' it is missing while expected.', 'red');
		return $result;				// no file: no dimensions (2.3.0)
	}
	
	$image_attr = getimagesize( $file );
	if ( ! $image_attr || ! isset($image_attr['0']) || ! $image_attr['0'] || ! isset($image_attr['1']) || ! $image_attr['1'] ) {
		// File is corrupt
		wppa_dbg_msg('Please check file '.$file.' it is corrupted. If it is a thumbnail image, regenerate them using Table VIII item 7 of the Photo Albums -> Settings admin page.', 'red');
		return $result;
	}
	
	// Adjust for 'border' 
	if ( $type == 'fullsize' && ! $wppa['in_widget'] ) {
		switch ( $wppa_opt['wppa_fullimage_border_width'] ) {
			case '':
				$max_size = $xmax_size;
				break;
			case '0':
				$max_size = $xmax_size - '2';
				break;
			default:
				$max_size = $xmax_size - '2' - 2 * $wppa_opt['wppa_fullimage_border_width'];
			}
	}
	else $max_size = $xmax_size;
	
	$ratioref = $wppa_opt['wppa_maxheight'] / $wppa_opt['wppa_fullsize'];
	$max_height = round($max_size * $ratioref);
	
	if ($type == 'fullsize') {
		if ($wppa['portrait_only']) {
			$width = $max_size;
			$height = round($width * $image_attr[1] / $image_attr[0]);
		}
		else {
			if (wppa_is_wider($image_attr[0], $image_attr[1])) {
				$width = $max_size;
				$height = round($width * $image_attr[1] / $image_attr[0]);
			}
			else {
				$height = round($ratioref * $max_size);
				$width = round($height * $image_attr[0] / $image_attr[1]);
			}
			if ($image_attr[0] < $width && $image_attr[1] < $height) {
				if (!$wppa['enlarge']) {
					$width = $image_attr[0];
					$height = $image_attr[1];
				}
			}
		}
	}
	else {
		if ( $type == 'cover' && $wppa_opt['wppa_coversize_is_height'] && ( $wppa['coverphoto_pos'] == 'top' || $wppa['coverphoto_pos'] == 'bottom' ) ) {
			$height = $max_size;
			$width = round($max_size * $image_attr[0] / $image_attr[1]);
		}
		else {
			if (wppa_is_landscape($image_attr)) {
				$width = $max_size;
				$height = round($max_size * $image_attr[1] / $image_attr[0]);
			}
			else {
				$height = $max_size;
				$width = round($max_size * $image_attr[0] / $image_attr[1]);
			}
		}
	}
	
	switch ($type) {
		case 'cover':
			if ($wppa_opt['wppa_bcolor_img'] != '') { 		// There is a border color given
				$result['style'] .= ' border: 1px solid '.$wppa_opt['wppa_bcolor_img'].';';
			}
			else {											// No border color: no border
				$result['style'] .= ' border-width: 0px;';
			}
			$result['style'] .= ' width:' . $width . 'px; height:' . $height . 'px;';
			if ($wppa_opt['wppa_use_cover_opacity'] && !is_feed()) {
				$opac = $wppa_opt['wppa_cover_opacity'];
				$result['style'] .= ' opacity:' . $opac/100 . '; filter:alpha(opacity=' . $opac . ');';
			}
			if ($wppa_opt['wppa_coverimg_linktype'] == 'lightbox' ) $result['cursor'] = ' cursor:url('.wppa_get_imgdir().$wppa_opt['wppa_magnifier'].'),pointer;';
			break;
		case 'thumb':		// Normal
		case 'ttthumb':		// Topten
		case 'comthumb':	// Comment widget
		case 'fthumb':		// Filmthumb
		case 'twthumb':		// Thumbnail widget
		case 'ltthumb':		// Lasten widget
		case 'albthumb':	// Album widget
			$result['style'] .= ' border-width: 0px;';
			$result['style'] .= ' width:' . $width . 'px; height:' . $height . 'px;';
			if ($xvalign == 'optional') $valign = $wppa_opt['wppa_valign'];
			else $valign = $xvalign;
			if ($valign != 'default') {	// Center horizontally
				$delta = floor(($max_size - $width) / 2);
				if (is_numeric($valign)) $delta += $valign;
				if ($delta < '0') $delta = '0';
				if ($delta > '0') $result['style'] .= ' margin-left:' . $delta . 'px; margin-right:' . $delta . 'px;';
			} 
						
			switch ($valign) {
				case 'top':
					$result['style'] .= ' margin-top: 0px;';
					break;
				case 'center':
					$delta = round(($max_size - $height) / 2);
					if ($delta < '0') $delta = '0';
					$result['style'] .= ' margin-top: ' . $delta . 'px;';
					break;
				case 'bottom':
					$delta = $max_size - $height;
					if ($delta < '0') $delta = '0';
					$result['style'] .= ' margin-top: ' . $delta . 'px;';
					break;
				default:
					if (is_numeric($valign)) {
						$delta = $valign;
						$result['style'] .= ' margin-top: '.$delta.'px; margin-bottom: '.$delta.'px;';
					}
			}
			if ($wppa_opt['wppa_use_thumb_opacity'] && !is_feed()) {
				$opac = $wppa_opt['wppa_thumb_opacity'];
				$result['style'] .= ' opacity:' . $opac/100 . '; filter:alpha(opacity=' . $opac . ');';
			}
			// Cursor
			$linktyp = '';
			switch ($type) {
				case 'thumb':		// Normal
					$linktyp = $wppa_opt['wppa_thumb_linktype'];
					break;
				case 'ttthumb':		// Topten	v
					$linktyp = $wppa_opt['wppa_topten_widget_linktype'];
					break;
				case 'comthumb':	// Comment widget	v
					$linktyp = $wppa_opt['wppa_comment_widget_linktype'];
					break;
				case 'fthumb':		// Filmthumb
					$linktyp = $wppa_opt['wppa_film_linktype'];
					break;
				case 'twthumb':		// Thumbnail widget	v
					$linktyp = $wppa_opt['wppa_thumbnail_widget_linktype'];
					break;
				case 'ltthumb':		// Lasten widget	v
					$linktyp = $wppa_opt['wppa_lasten_widget_linktype'];
					break;
				case 'albthumb':	// Album widget
					$linktyp = $wppa_opt['wppa_album_widget_linktype'];
			}
			if ($linktyp == 'none') $result['cursor'] = ' cursor:default;';
			elseif ($linktyp == 'lightbox' ) $result['cursor'] = ' cursor:url('.wppa_get_imgdir().$wppa_opt['wppa_magnifier'].'),pointer;';
			else $result['cursor'] = ' cursor:pointer;';
			
			break;
		case 'fullsize':
			if ( $wppa['auto_colwidth'] ) {
				$result['style'] .= ' max-width:' . $width . 'px;';		// These sizes fit within the rectangle define by Table I-B1,2
				$result['style'] .= ' max-height:' . $height . 'px;';	// and are supplied for ver 4 browsers as they have undifined natural sizes
			}
			else {
				$result['style'] .= ' max-width:' . $width . 'px;';		// These sizes fit within the rectangle define by Table I-B1,2
				$result['style'] .= ' max-height:' . $height . 'px;';	// and are supplied for ver 4 browsers as they have undifined natural sizes

				$result['style'] .= ' width:' . $width . 'px;';
				$result['style'] .= ' height:' . $height . 'px;';
				// There are still users that have #content .img {max-width: 640px; } and Table I item 1 larger than 640, so we increase max-width inline.
				// $result['style'] .= ' max-width:' . wppa_get_container_width() . 'px;';
			}
			
			if ($wppa['is_slideonly'] == '1') {
				if ($wppa['ss_widget_valign'] != '') $valign = $wppa['ss_widget_valign'];
				else $valign = 'fit';
			}
			elseif ($xvalign == 'optional') {
				$valign = $wppa_opt['wppa_fullvalign'];
			}
			else {
				$valign = $xvalign;
			}
			
			// Margin
			if ($valign != 'default') {
				$m_left 	= '0';
				$m_right 	= '0';
				$m_top 		= '0';
				$m_bottom 	= '0';
				// Center horizontally
				$delta = round(($max_size - $width) / 2);
				if ($delta < '0') $delta = '0';
				if ( $wppa['auto_colwidth'] ) {
//					$result['style'] .= ' margin-left:auto; margin-right:auto;';
					$m_left 	= 'auto';
					$m_right 	= 'auto';
				}
				else {
//					$result['style'] .= ' margin-left:' . $delta . 'px;';
					$m_left 	= $delta;
					$m_right 	= '0';
				}
				// Position vertically
				if ( $wppa['in_widget'] == 'ss' && $wppa['in_widget_frame_height'] > '0' ) $max_height = $wppa['in_widget_frame_height'];
				$delta = '0';
				if (!$wppa['auto_colwidth'] && !wppa_page('oneofone')) {
					switch ($valign) {
						case 'top':
						case 'fit':
							$delta = '0';
							break;
						case 'center':
							$delta = round(($max_height - $height) / 2);
							if ($delta < '0') $delta = '0';
							break;
						case 'bottom':
							$delta = $max_height - $height;
							if ($delta < '0') $delta = '0';
							break;
					}
				}
				$m_top = $delta;
				
//				$result['style'] .= ' margin-top:' . $delta . 'px;';

				$result['style'] .= wppa_combine_style('margin', $m_top, $m_left, $m_right, $m_bottom);
/*				
				$result['style'] .= ' margin:'.$m_top;
				if ( $m_left == $m_right ) {
					if ( $m_top == '0' ) {
						$result['style'] .= ' '.$m_left.';';				// 2 given: TB=0, LR
					}
					else {
						$result['style'] .= ' '.$m_left.' 0;';				// 3 given: T, LR, B=0
					}
				}
				else $result['style'] .= ' '.$m_left.' '.$m_right.' 0;';	// 4 given: T, L, R, B=0
*/
			}
			
			// Border and padding
			if ( ! $wppa['in_widget'] ) switch ( $wppa_opt['wppa_fullimage_border_width'] ) {
				case '':
					break;
				case '0':
					$result['style'] .= ' border: 1px solid ' . $wppa_opt['wppa_bcolor_fullimg'] . ';';
					break;
				default:
					$result['style'] .= ' border: 1px solid ' . $wppa_opt['wppa_bcolor_fullimg'] . ';';
					$result['style'] .= ' background-color:' . $wppa_opt['wppa_bgcolor_fullimg'] . ';';
					$result['style'] .= ' padding:' . $wppa_opt['wppa_fullimage_border_width'] . 'px;';
					// If we do round corners...
					if ( $wppa_opt['wppa_bradius'] > '0' ) {	// then also here
						$result['style'] .= ' border-radius:' . $wppa_opt['wppa_fullimage_border_width'] . 'px;';
					}
			}
			
			break;
		default:
			$wppa['out'] .=  ('Error wrong "$type" argument: '.$type.' in wppa_get_imgstyle_a');
	}
	$result['width'] = $width;
	$result['height'] = $height;
	return $result;
}

function wppa_is_landscape($img_attr) {
	return ($img_attr[0] > $img_attr[1]);
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
		if ( isset($_GET['wppa-occur']) ) $extra_url .= '&amp;wppa-occur='.$_GET['wppa-occur'];
		if ( isset($_GET['wppa-woccur']) ) $extra_url .= '&amp;wppa-woccur='.$_GET['wppa-woccur'];
	}
	// Topten?
//	if ( wppa_get_get('topten') ) $extra_url .= '&amp;wppa-topten='.wppa_get_get('topten');
//	else
	if ( $wppa['is_topten'] ) $extra_url .= '&amp;wppa-topten='.$wppa['topten_count'];
	// Lasten?
	if ( $wppa['is_lasten'] ) $extra_url .= '&amp;wppa-lasten='.$wppa['lasten_count'];
	// Comten?
	if ( $wppa['is_comten'] ) $extra_url .= '&amp;wppa-comten='.$wppa['comten_count'];
	// Featen?
	if ( $wppa['is_featen'] ) $extra_url .= '&amp;wppa-featen='.$wppa['featen_count'];
	// Tag?
	if ( $wppa['is_tag'] ) $extra_url .= '&amp;wppa-tag='.$wppa['is_tag'];
	// Search?
	if ( $wppa['src'] ) $extra_url .= '&amp;wppa-searchstring='.urlencode($wppa['searchstring']);
	
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

function wppa_get_pagesize($type = '') {
global $wppa_opt;

//	if (isset($_REQUEST['wppa-searchstring'])) return '0';
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
	$result -= 14; // 2*padding
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

	$result = 7 + 2 * $wppa_opt['wppa_bwidth'];	// 7 = .thumbnail_area padding-left
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
			wppa_dbg_msg('Plugin load time :'.substr($wppa_loadtime,0,5).'s.', 'green');
			wppa_dbg_msg('Init runtime time :'.substr($wppa_initruntimetime,0,5).'s.', 'green');
			wppa_dbg_msg('Num queries before wppa :'.get_num_queries(), 'green');
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
			wppa_dbg_msg('Time elapsed occ '.$wppa['master_occur'].':'.substr($laptim, 0, 5).'s. Tot:'.substr($wppa_microtime_cum, 0, 5).'s.', 'green');
			wppa_dbg_msg('Nuber of queries occ '.$wppa['master_occur'].':'.$wppa_numqueries, 'green');
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

	if ($action == 'open') {
		$cover_count = '0';
		$cover_count_key = 'l';
		$wppa['out'] .= wppa_nltab('+').'<div id="wppa-thumblist-'.$wppa['master_occur'].'" class="thumblist">';
	}
	elseif ($action == 'close') {
		$wppa['out'] .= wppa_nltab('-').'</div><!-- wppa-thumblist-'.$wppa['master_occur'].' -->';
	}
	else {
		$wppa['out'] .= wppa_nltab().'<span style="color:red;">Error, wppa_thumblist() called with wrong argument: '.$action.'. Possible values: \'open\' or \'close\'</span>';
	}
}

function wppa_thumb_area($action) {
global $wppa;
global $wppa_alt;
global $album;

	if ($action == 'open') {
		if (is_feed()) {
			$wppa['out'] .= wppa_nltab('+').'<div id="wppa-thumbarea-'.$wppa['master_occur'].'" style="clear: both: '.__wcs('wppa-box').__wcs('wppa-'.$wppa_alt).'">';
		}
		else {
			$wppa['out'] .= wppa_nltab('+').'<div id="wppa-thumbarea-'.$wppa['master_occur'].'" style="clear: both; '.__wcs('wppa-box').__wcs('wppa-'.$wppa_alt).'width: '.wppa_get_thumbnail_area_width().'px;" class="thumbnail-area thumbnail-area-'.$wppa['master_occur'].' wppa-box wppa-'.$wppa_alt.'" >';
		}		
		if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
	}
	elseif ($action == 'close') {
		wppa_user_upload_html($wppa['current_album'], wppa_get_container_width('netto'), 'thumb');
		$wppa['out'] .= wppa_nltab().'<div style="clear:both;"></div>';		

		$wppa['out'] .= wppa_nltab('-').'</div><!-- wppa-thumbarea-'.$wppa['master_occur'].' -->';
	}
	else {
		$wppa['out'] .= wppa_nltab().'<span style="color:red;">Error, wppa_thumb_area() called with wrong argument: '.$action.'. Possible values: \'open\' or \'close\'</span>';
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
		elseif ( $arraycount <= $wppa_opt['wppa_min_thumbs'] && ! $wppa['src'] && ! $wppa['is_tag'] ) {	// Less than treshold and not searching and not from tagcloud: 0
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

function wppa_album_cover() {
global $wppa_opt;
global $album;
global $wppa;

	$type = $album['cover_type'] ? $album['cover_type'] : $wppa_opt['wppa_cover_type'];
	$wppa['coverphoto_pos'] = $wppa_opt['wppa_coverphoto_pos'];

	switch ( $type ) {
		case 'default':
			wppa_album_cover_default();
			break;
		case 'imagefactory':
			if ( $wppa['coverphoto_pos'] == 'left' ) $wppa['coverphoto_pos'] = 'top';
			if ( $wppa['coverphoto_pos'] == 'right' ) $wppa['coverphoto_pos'] = 'bottom';
			wppa_album_cover_imagefactory();
			break;
		case 'longdesc':
			if ( $wppa['coverphoto_pos'] == 'top' ) $wppa['coverphoto_pos'] = 'left';
			if ( $wppa['coverphoto_pos'] == 'bottom' ) $wppa['coverphoto_pos'] = 'right';
			wppa_album_cover_longdesc();
			break;
		default:
			wppa_dbg_msg('Unimplemented covertype: '.$wppa_opt['wppa_cover_type']);
	}
}

function wppa_album_cover_default() {
global $album;
global $wppa;
global $wppa_opt;
global $wppa_alt;
global $cover_count_key;
global $wpdb;

	$albumid = $album['id'];
	
	$coverphoto = wppa_get_coverphoto_id($albumid);
	$image = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $coverphoto), ARRAY_A );
	$photocount = wppa_get_photo_count($albumid);
	$albumcount = wppa_get_album_count($albumid);
	$mincount = wppa_get_mincount();
	$title = '';
	$linkpage = '';
	
	$href_title = '';
	$href_slideshow = '';
	$href_content = '';
	$onclick_title = '';
	$onclick_slideshow = '';
	$onclick_content = '';

	// See if there is substantial content to the album
	$has_content = ($albumcount > '0') || ($photocount > $mincount);
	
	// What is the albums title linktype
	$linktype = $album['cover_linktype'];
	if ( !$linktype ) $linktype = 'content'; // Default 
	
	// What is the albums title linkpage
	$linkpage = $album['cover_linkpage'];
	if ( $linkpage == '-1' ) $linktype = 'none'; // for backward compatibility
	
	// Find the cover title link and onclick
	// Dispatch on linktype when page is not current
	if ( $linkpage > 0 ) {
		switch ( $linktype ) {
			case 'content':
				if ($has_content) {
					$href_title = wppa_get_album_url($album['id'], $linkpage);
				}
				else {
					$href_title = get_page_link($album['cover_linkpage']);
				}
				break;
			case 'slide':
				if ($has_content) {
					$href_title = wppa_get_slideshow_url('', $linkpage);
				}
				else {
					$href_title = get_page_link($album['cover_linkpage']);
				}
				break;
			case 'none':
				break;
			default:
		}
		$href_title = wppa_convert_to_pretty($href_title);
		$title = __a('Link to');
		$title .= ' ' . __(get_the_title($album['cover_linkpage']));
	}
	// Dispatch on linktype when page is current
	elseif ($has_content) {
		switch ( $linktype ) {
			case 'content':
				$href_title = wppa_convert_to_pretty(wppa_get_album_url($album['id'], $linkpage));
				if ( $wppa_opt['wppa_allow_ajax'] ) {
					$onclick_title = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_album_url_ajax($album['id'], $linkpage)."', '".$href_title."')";
					$href_title = "#";
				}
				break;
			case 'slide':
				$href_title = wppa_convert_to_pretty(wppa_get_slideshow_url('', $linkpage));
				if ( $wppa_opt['wppa_allow_ajax'] ) {
					$onclick_title = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_slideshow_url_ajax($album['id'], $linkpage)."', '".$href_title."')";
					$href_title = "#";
				}
				break;
			case 'none':
				break;
			default:
		}
		$title = __a('View the album').' '.esc_attr(wppa_qtrans(stripslashes($album['name'])));
	}
	else {	// No content on current page/post
		if ($photocount > '0') {	// coverphotos only
			if ( $coverphoto ) {
				$href_title = wppa_convert_to_pretty(wppa_get_image_page_url_by_id($coverphoto)); 
			}
			else {
				$href_title = '#';
			}
			if ( $wppa_opt['wppa_allow_ajax'] ) {
				if ( $coverphoto ) {
					$onclick_title = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_image_url_ajax_by_id($coverphoto)."', '".$href_title."')";
				}
				else {
					$onclick_title = '';
				}
				$href_title = "#";
			}
			if ($photocount == '1') $title = __a('View the cover photo'); 
			else $title = __a('View the cover photos');
		}
	}
	
	// Find the slideshow link and onclick
	$href_slideshow = wppa_convert_to_pretty(wppa_get_slideshow_url('', $linkpage));
	if ( $wppa_opt['wppa_allow_ajax'] && ! $linkpage ) {
		$onclick_slideshow = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_slideshow_url_ajax($album['id'], $linkpage)."', '".wppa_convert_to_pretty($href_slideshow)."')";
		$href_slideshow = "#";
	}

	// Find the content 'View' link 
	$href_content = wppa_convert_to_pretty(wppa_get_album_url($album['id'], $linkpage));
	if ( $wppa_opt['wppa_allow_ajax'] && ! $linkpage ) {
		$onclick_content = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_album_url_ajax($album['id'], $linkpage)."', '".wppa_convert_to_pretty($href_content)."')";
		$href_content = "#";
	}

	// Find the coverphoto link
	if ( $coverphoto ) {
		$photolink = wppa_get_imglnk_a('coverimg', $coverphoto, $href_title, $title, $onclick_title, '', $albumid);
	}
	else $photolink = false;
	
	// Find the coverphoto details
	if ( $coverphoto ) {
		$src = wppa_get_thumb_url($coverphoto);	
		$path = wppa_get_thumb_path($coverphoto);
		$imgattr_a = wppa_get_imgstyle_a($path, $wppa_opt['wppa_smallsize'], '', 'cover');
	}
	else {
		$src = '';
		$path = '';
		$imgattr_a = false;
	}
	if (is_feed()) {
		$events = '';
	}
	else {
		$events = wppa_get_imgevents('cover');
	}
	$photo_pos = $wppa['coverphoto_pos'];
	$class_asym = ( $photo_pos == 'left' || $photo_pos == 'right' ) ? 'wppa-asym-text-frame-'.$wppa['master_occur'] : '';
	
	$style =  __wcs('wppa-box').__wcs('wppa-'.$wppa_alt);
	if (is_feed()) $style .= ' padding:7px;';
	
	$wid = wppa_get_cover_width('cover');
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
	wppa_step_covercount('cover');
	
	$target = $wppa_opt['wppa_allow_ajax'] ? '_self' : $photolink['target'];
	
	// Open the album box
	$wppa['out'] .= wppa_nltab('+').'<div id="album-'.$album['id'].'-'.$wppa['master_occur'].'" class="album wppa-box wppa-cover-box wppa-cover-box-'.$wppa['master_occur'].' wppa-'.$wppa_alt.'" style="'.$style.'" >';

		if ( $photo_pos == 'left' || $photo_pos == 'top') {
			// First The Cover photo
			wppa_the_coverphoto($image, $src, $photo_pos, $photolink, $title, $imgattr_a, $events);
		}
		
		// The Cover text
		$textframestyle = wppa_get_text_frame_style($photo_pos, 'cover');
		$wppa['out'] .= wppa_nltab('+').'<div id="covertext_frame_'.$album['id'].'_'.$wppa['master_occur'].'" class="wppa-text-frame-'.$wppa['master_occur'].' wppa-text-frame covertext-frame '.$class_asym.'" '.$textframestyle.'>';

			// The Album title
			$wppa['out'] .= wppa_nltab('+').'<h2 class="wppa-title" style="clear:none; '.__wcs('wppa-title').'">';
				if ($href_title != '') { 
					if ($href_title == '#') {
						$wppa['out'] .= wppa_nltab().'<a onclick="'.$onclick_title.'" title="'.$title.'" class="wppa-title" style="cursor:pointer; '.__wcs('wppa-title').'">'.wppa_qtrans(stripslashes($album['name'])).'</a>';
					}
					else {
						$wppa['out'] .= wppa_nltab().'<a href="'.$href_title.'" target="'.$target.'" onclick="'.$onclick_title.'" title="'.$title.'" class="wppa-title" style="'.__wcs('wppa-title').'">'.wppa_qtrans(stripslashes($album['name'])).'</a>';
					}
				} else { 
					$wppa['out'] .= wppa_qtrans(stripslashes($album['name'])); 
				} 
				if ( wppa_is_album_new($album['id']) ) {
					$wppa['out'] .= wppa_nltab().'<img src="'.WPPA_URL.'/images/new.png" title="New!" class="wppa-albumnew" style="border:none; margin:0; padding:0; box-shadow:none; " />';
				}
			$wppa['out'] .= wppa_nltab('-').'</h2>';
			

			// The Album description
			if ( $wppa_opt['wppa_show_cover_text'] ) {
				$textheight = $wppa_opt['wppa_text_frame_height'] > '0' ? 'min-height:'.$wppa_opt['wppa_text_frame_height'].'px; ' : '';
				$wppa['out'] .= wppa_nltab().'<p class="wppa-box-text wppa-black" style="'.$textheight.__wcs('wppa-box-text').__wcs('wppa-black').'">'.wppa_get_album_desc($album['id']).'</p>';
			}
			
			// The 'Slideshow'/'Browse' link
			if ( $wppa_opt['wppa_show_slideshowbrowselink'] ) {
				$wppa['out'] .= wppa_nltab('+').'<div class="wppa-box-text wppa-black wppa-info wppa-slideshow-browse-link">';
					if ($photocount > $mincount) { 
						$label = $wppa_opt['wppa_enable_slideshow'] ?  __a('Slideshow') : __a('Browse photos');
						if ( $href_slideshow == '#' ) {
							$wppa['out'] .= wppa_nltab().'<a onclick="'.$onclick_slideshow.'" title="'.$label.'" style="'.__wcs('wppa-box-text', 'nocolor').'" >'.$label.'</a>';
						}
						else {
							$wppa['out'] .= wppa_nltab().'<a href="'.$href_slideshow.'" target="'.$target.'" onclick="'.$onclick_slideshow.'" title="'.$label.'" style="'.__wcs('wppa-box-text', 'nocolor').'" >'.$label.'</a>';
						}
					} else $wppa['out'] .= '&nbsp;'; 
				$wppa['out'] .= wppa_nltab('-').'</div>';
			}

			// The 'View' link
			if ( $wppa_opt['wppa_show_viewlink'] ) {
				$wppa['out'] .= wppa_nltab('+').'<div class="wppa-box-text wppa-black wppa-info">';
					if ($has_content) {
						if ($wppa_opt['wppa_thumbtype'] == 'none') $photocount = '0'; 	// Fake photocount to prevent link to empty page
						if ($photocount > $mincount || $albumcount) {					// Still has content
							if ( $wppa_opt['wppa_show_treecount'] ) {
								$treecount = wppa_treecount_a($album['id']);
							}
							else $treecount = false;
							if ( $href_content == '#' ) {
								$wppa['out'] .= wppa_nltab('+').'<a onclick="'.$onclick_content.'" title="'.__a('View the album').' '.esc_attr(stripslashes(wppa_qtrans($album['name']))).'" style="'.__wcs('wppa-box-text', 'nocolor').'" >';
							}
							else {
								$wppa['out'] .= wppa_nltab('+').'<a href="'.$href_content.'" target="'.$target.'" onclick="'.$onclick_content.'" title="'.__a('View the album').' '.esc_attr(stripslashes(wppa_qtrans($album['name']))).'" style="'.__wcs('wppa-box-text', 'nocolor').'" >';
							}
							$wppa['out'] .= __a('View');
							if ($albumcount) { 
								if ($albumcount == '1') {
									$wppa['out'] .= ' 1 '.__a('album'); 
								}
								else {
									$wppa['out'] .= ' '.$albumcount.' '.__a('albums');
								}
								if ( $treecount ) {
									if ( $treecount['albums'] != $albumcount ) {
										$wppa['out'] .= ' ('.$treecount['albums'].')';
									}
								}
							}
							if ($photocount > $mincount && $albumcount) {
								$wppa['out'] .= ' '.__a('and'); 
							}
							if ($photocount > $mincount || $treecount) { 
								if ( $photocount <= $mincount ) $photocount = '0';
								if ($photocount == '1') {
									$wppa['out'] .= ' 1 '.__a('photo');
								}
								elseif ($photocount) {
									$wppa['out'] .= ' '.$photocount.' '.__a('photos'); 
								}
								if ( $treecount ) {
									if ( $treecount['photos'] != $photocount ) {
										if ( ! $photocount ) $wppa['out'] .= ', '.__a('photos'); 
										$wppa['out'] .= ' ('.$treecount['photos'].')';
									}
								}
							} 
							$wppa['out'] .= wppa_nltab('-').'</a>'; 
						}
					} 
				$wppa['out'] .= wppa_nltab('-').'</div>';
			}
		$wppa['out'] .= wppa_nltab('-').'</div>';
		
		if ( $photo_pos == 'right' || $photo_pos == 'bottom' ) {
			// The Cover photo last
			wppa_the_coverphoto($image, $src, $photo_pos, $photolink, $title, $imgattr_a, $events);
		}
		
		$wppa['out'] .= wppa_nltab().'<div style="clear:both;"></div>';		
		
		wppa_user_upload_html($album['id'], wppa_get_cover_width('cover'), 'cover');

	$wppa['out'] .= wppa_nltab('-').'</div><!-- #album-'.$album['id'].'-'.$wppa['master_occur'].' -->';
	if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
}

function wppa_album_cover_imagefactory() {
global $album;
global $wppa;
global $wppa_opt;
global $wppa_alt;
global $cover_count_key;
global $wpdb;

	$albumid = $album['id'];

	$photo_pos = $wppa['coverphoto_pos'];
	if ( $photo_pos == 'left' ) $photo_pos = 'top';
	if ( $photo_pos == 'right' ) $photo_pos = 'bottom';
	$cpcount =  $album['main_photo'] > '0' ? '1' : $wppa_opt['wppa_imgfact_count'];
//	if ( $cpcount == '1' ) $coverphotos = wppa_get_coverphoto_ids($albumid, '1');
//	else 
	$coverphotos = wppa_get_coverphoto_ids($albumid, $cpcount);
	
	$images = array();
	$srcs = array();
	$paths = array();
	$imgattrs_a = array();
	foreach ( $coverphotos as $coverphoto ) {
		$images[] 		= $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $coverphoto), ARRAY_A);
		$srcs[] 		= wppa_get_thumb_url($coverphoto);
		$path 			= wppa_get_thumb_path($coverphoto);
		$paths[] 		= $path;
		$cpsize 		= count($coverphotos) == '1' ? $wppa_opt['wppa_smallsize'] : $wppa_opt['wppa_smallsize_multi'];
		$imgattrs_a[] 	= wppa_get_imgstyle_a($path, $cpsize, '', 'cover');
	}
	
	if (is_feed()) {
		$events = '';
	}
	else {
		$events = wppa_get_imgevents('cover');
	}

if ( ! empty($images) ) $image = $images['0'];
else $image = false;
if ( ! empty($coverphotos) ) $coverphoto = $coverphotos['0'];
else $coverphoto = false;
	$photocount = wppa_get_photo_count($albumid);
	$albumcount = wppa_get_album_count($albumid);
	$mincount = wppa_get_mincount();
	$title = '';
	$linkpage = '';
	
	$href_title = '';
	$href_slideshow = '';
	$href_content = '';
	$onclick_title = '';
	$onclick_slideshow = '';
	$onclick_content = '';

	// See if there is substantial content to the album
	$has_content = ($albumcount > '0') || ($photocount > $mincount);
	
	// What is the albums title linktype
	$linktype = $album['cover_linktype'];
	if ( !$linktype ) $linktype = 'content'; // Default 
	
	// What is the albums title linkpage
	$linkpage = $album['cover_linkpage'];
	if ( $linkpage == '-1' ) $linktype = 'none'; // for backward compatibility
	
	// Find the cover title link and onclick
	// Dispatch on linktype when page is not current
	if ( $linkpage > 0 ) {
		switch ( $linktype ) {
			case 'content':
				if ($has_content) {
					$href_title = wppa_get_album_url($album['id'], $linkpage);
				}
				else {
					$href_title = get_page_link($album['cover_linkpage']);
				}
				break;
			case 'slide':
				if ($has_content) {
					$href_title = wppa_get_slideshow_url('', $linkpage);
				}
				else {
					$href_title = get_page_link($album['cover_linkpage']);
				}
				break;
			case 'none':
				break;
			default:
		}
		$href_title = wppa_convert_to_pretty($href_title);
		$title = __a('Link to');
		$title .= ' ' . __(get_the_title($album['cover_linkpage']));
	}
	// Dispatch on linktype when page is current
	elseif ($has_content) {
		switch ( $linktype ) {
			case 'content':
				$href_title = wppa_convert_to_pretty(wppa_get_album_url($album['id'], $linkpage));
				if ( $wppa_opt['wppa_allow_ajax'] ) {
					$onclick_title = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_album_url_ajax($album['id'], $linkpage)."', '".$href_title."')";
					$href_title = "#";
				}
				break;
			case 'slide':
				$href_title = wppa_convert_to_pretty(wppa_get_slideshow_url('', $linkpage));
				if ( $wppa_opt['wppa_allow_ajax'] ) {
					$onclick_title = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_slideshow_url_ajax($album['id'], $linkpage)."', '".$href_title."')";
					$href_title = "#";
				}
				break;
			case 'none':
				break;
			default:
		}
		$title = __a('View the album').' '.esc_attr(wppa_qtrans(stripslashes($album['name'])));
	}
	else {	// No content on current page/post
		if ($photocount > '0') {	// coverphotos only
			if ( $coverphoto ) {
				$href_title = wppa_convert_to_pretty(wppa_get_image_page_url_by_id($coverphoto)); 
			}
			else {
				$href_title = '#';
			}
			if ( $wppa_opt['wppa_allow_ajax'] ) {
				if ( $coverphoto ) {
					$onclick_title = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_image_url_ajax_by_id($coverphoto)."', '".$href_title."')";
				}
				else {
					$onclick_title = '';
				}
				$href_title = "#";
			}
			if ($photocount == '1') $title = __a('View the cover photo'); 
			else $title = __a('View the cover photos');
		}
	}
	
	// Find the slideshow link and onclick
	$href_slideshow = wppa_convert_to_pretty(wppa_get_slideshow_url('', $linkpage));
	if ( $wppa_opt['wppa_allow_ajax'] && ! $linkpage ) {
		$onclick_slideshow = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_slideshow_url_ajax($album['id'], $linkpage)."', '".wppa_convert_to_pretty($href_slideshow)."')";
		$href_slideshow = "#";
	}

	// Find the content 'View' link 
	$href_content = wppa_convert_to_pretty(wppa_get_album_url($album['id'], $linkpage));
	if ( $wppa_opt['wppa_allow_ajax'] && ! $linkpage ) {
		$onclick_content = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_album_url_ajax($album['id'], $linkpage)."', '".wppa_convert_to_pretty($href_content)."')";
		$href_content = "#";
	}

	// Find the coverphoto link
	if ( $coverphoto ) {
		$photolink = wppa_get_imglnk_a('coverimg', $coverphoto, $href_title, $title, $onclick_title, '', $albumid);
	}
	else $photolink = false;
	
	$style =  __wcs('wppa-box').__wcs('wppa-'.$wppa_alt);
	if (is_feed()) $style .= ' padding:7px;';
	
	$wid = wppa_get_cover_width('cover');
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
	wppa_step_covercount('cover');
	
	$target = $wppa_opt['wppa_allow_ajax'] ? '_self' : $photolink['target'];
	
	// Open the album box
	$wppa['out'] .= wppa_nltab('+').'<div id="album-'.$album['id'].'-'.$wppa['master_occur'].'" class="album wppa-box wppa-cover-box wppa-cover-box-'.$wppa['master_occur'].' wppa-'.$wppa_alt.'" style="'.$style.'" >';

		if ( $photo_pos == 'left' || $photo_pos == 'top') {
			// First The Cover photo
			wppa_the_coverphotos($images, $srcs, $photo_pos, $photolink, $title, $imgattrs_a, $events);
		}
		
		// The Cover text
		$textframestyle = 'style="text-align:center;"';//'style="width: 100% !important;"';//wppa_get_text_frame_style($photo_pos, 'cover');

		$wppa['out'] .= wppa_nltab('+').'<div id="covertext_frame_'.$album['id'].'_'.$wppa['master_occur'].'" class="wppa-text-frame-'.$wppa['master_occur'].' wppa-text-frame covertext-frame" '.$textframestyle.'>';

			// The Album title
			$wppa['out'] .= wppa_nltab('+').'<h2 class="wppa-title" style="clear:none; '.__wcs('wppa-title').'">';
				if ($href_title != '') { 
					if ($href_title == '#') {
						$wppa['out'] .= wppa_nltab().'<a onclick="'.$onclick_title.'" title="'.$title.'" class="wppa-title" style="cursor:pointer; '.__wcs('wppa-title').'">'.wppa_qtrans(stripslashes($album['name'])).'</a>';
					}
					else {
						$wppa['out'] .= wppa_nltab().'<a href="'.$href_title.'" target="'.$target.'" onclick="'.$onclick_title.'" title="'.$title.'" class="wppa-title" style="'.__wcs('wppa-title').'">'.wppa_qtrans(stripslashes($album['name'])).'</a>';
					}
				} else { 
					$wppa['out'] .= wppa_qtrans(stripslashes($album['name'])); 
				} 
				if ( wppa_is_album_new($album['id']) ) {
					$wppa['out'] .= wppa_nltab().'<img src="'.WPPA_URL.'/images/new.png" title="New!" class="wppa-albumnew" style="border:none; margin:0; padding:0; box-shadow:none; " />';
				}
			$wppa['out'] .= wppa_nltab('-').'</h2>';
			

			// The Album description
			if ( $wppa_opt['wppa_show_cover_text'] ) {
				$textheight = $wppa_opt['wppa_text_frame_height'] > '0' ? 'min-height:'.$wppa_opt['wppa_text_frame_height'].'px; ' : '';
				$wppa['out'] .= wppa_nltab().'<p class="wppa-box-text wppa-black" style="'.$textheight.__wcs('wppa-box-text').__wcs('wppa-black').'">'.wppa_get_album_desc($album['id']).'</p>';
			}
			
			// The 'Slideshow'/'Browse' link
			if ( $wppa_opt['wppa_show_slideshowbrowselink'] ) {
				$wppa['out'] .= wppa_nltab('+').'<div class="wppa-box-text wppa-black wppa-info wppa-slideshow-browse-link">';
					if ($photocount > $mincount) { 
						$label = $wppa_opt['wppa_enable_slideshow'] ?  __a('Slideshow') : __a('Browse photos');
						if ( $href_slideshow == '#' ) {
							$wppa['out'] .= wppa_nltab().'<a onclick="'.$onclick_slideshow.'" title="'.$label.'" style="'.__wcs('wppa-box-text', 'nocolor').'" >'.$label.'</a>';
						}
						else {
							$wppa['out'] .= wppa_nltab().'<a href="'.$href_slideshow.'" target="'.$target.'" onclick="'.$onclick_slideshow.'" title="'.$label.'" style="'.__wcs('wppa-box-text', 'nocolor').'" >'.$label.'</a>';
						}
					} else $wppa['out'] .= '&nbsp;'; 
				$wppa['out'] .= wppa_nltab('-').'</div>';
			}

			// The 'View' link
			if ( $wppa_opt['wppa_show_viewlink'] ) {
				$wppa['out'] .= wppa_nltab('+').'<div class="wppa-box-text wppa-black wppa-info">';
					if ($has_content) {
						if ($wppa_opt['wppa_thumbtype'] == 'none') $photocount = '0'; 	// Fake photocount to prevent link to empty page
						if ($photocount > $mincount || $albumcount) {					// Still has content
							if ( $wppa_opt['wppa_show_treecount'] ) {
								$treecount = wppa_treecount_a($album['id']);
							}
							else $treecount = false;
							if ( $href_content == '#' ) {
								$wppa['out'] .= wppa_nltab('+').'<a onclick="'.$onclick_content.'" title="'.__a('View the album').' '.esc_attr(stripslashes(wppa_qtrans($album['name']))).'" style="'.__wcs('wppa-box-text', 'nocolor').'" >';
							}
							else {
								$wppa['out'] .= wppa_nltab('+').'<a href="'.$href_content.'" target="'.$target.'" onclick="'.$onclick_content.'" title="'.__a('View the album').' '.esc_attr(stripslashes(wppa_qtrans($album['name']))).'" style="'.__wcs('wppa-box-text', 'nocolor').'" >';
							}
							$wppa['out'] .= __a('View');
							if ($albumcount) { 
								if ($albumcount == '1') {
									$wppa['out'] .= ' 1 '.__a('album'); 
								}
								else {
									$wppa['out'] .= ' '.$albumcount.' '.__a('albums');
								}
								if ( $treecount ) {
									if ( $treecount['albums'] != $albumcount ) {
										$wppa['out'] .= ' ('.$treecount['albums'].')';
									}
								}
							}
							if ($photocount > $mincount && $albumcount) {
								$wppa['out'] .= ' '.__a('and'); 
							}
							if ($photocount > $mincount || $treecount) { 
								if ( $photocount <= $mincount ) $photocount = '0';
								if ($photocount == '1') {
									$wppa['out'] .= ' 1 '.__a('photo');
								}
								elseif ($photocount) {
									$wppa['out'] .= ' '.$photocount.' '.__a('photos'); 
								}
								if ( $treecount ) {
									if ( $treecount['photos'] != $photocount ) {
										if ( ! $photocount ) $wppa['out'] .= ', '.__a('photos'); 
										$wppa['out'] .= ' ('.$treecount['photos'].')';
									}
								}
							} 
							$wppa['out'] .= wppa_nltab('-').'</a>'; 
						}
					} 
				$wppa['out'] .= wppa_nltab('-').'</div>';
			}
		$wppa['out'] .= wppa_nltab('-').'</div>';
		
		if ( $photo_pos == 'right' || $photo_pos == 'bottom' ) {
			// The Cover photo last
			wppa_the_coverphotos($images, $srcs, $photo_pos, $photolink, $title, $imgattrs_a, $events);
		}
		
		$wppa['out'] .= wppa_nltab().'<div style="clear:both;"></div>';		
		
		wppa_user_upload_html($album['id'], wppa_get_cover_width('cover'), 'cover');

	$wppa['out'] .= wppa_nltab('-').'</div><!-- #album-'.$album['id'].'-'.$wppa['master_occur'].' -->';
	if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
}

function wppa_album_cover_longdesc() {
global $album;
global $wppa;
global $wppa_opt;
global $wppa_alt;
global $cover_count_key;
global $wpdb;

	$albumid = $album['id'];
	
	$coverphoto = wppa_get_coverphoto_id($albumid);
	$image = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $coverphoto), ARRAY_A );
	$photocount = wppa_get_photo_count($albumid);
	$albumcount = wppa_get_album_count($albumid);
	$mincount = wppa_get_mincount();
	$title = '';
	$linkpage = '';
	
	$href_title = '';
	$href_slideshow = '';
	$href_content = '';
	$onclick_title = '';
	$onclick_slideshow = '';
	$onclick_content = '';

	// See if there is substantial content to the album
	$has_content = ($albumcount > '0') || ($photocount > $mincount);
	
	// What is the albums title linktype
	$linktype = $album['cover_linktype'];
	if ( !$linktype ) $linktype = 'content'; // Default 
	
	// What is the albums title linkpage
	$linkpage = $album['cover_linkpage'];
	if ( $linkpage == '-1' ) $linktype = 'none'; // for backward compatibility
	
	// Find the cover title link and onclick
	// Dispatch on linktype when page is not current
	if ( $linkpage > 0 ) {
		switch ( $linktype ) {
			case 'content':
				if ($has_content) {
					$href_title = wppa_get_album_url($album['id'], $linkpage);
				}
				else {
					$href_title = get_page_link($album['cover_linkpage']);
				}
				break;
			case 'slide':
				if ($has_content) {
					$href_title = wppa_get_slideshow_url('', $linkpage);
				}
				else {
					$href_title = get_page_link($album['cover_linkpage']);
				}
				break;
			case 'none':
				break;
			default:
		}
		$href_title = wppa_convert_to_pretty($href_title);
		$title = __a('Link to');
		$title .= ' ' . __(get_the_title($album['cover_linkpage']));
	}
	// Dispatch on linktype when page is current
	elseif ($has_content) {
		switch ( $linktype ) {
			case 'content':
				$href_title = wppa_convert_to_pretty(wppa_get_album_url($album['id'], $linkpage));
				if ( $wppa_opt['wppa_allow_ajax'] ) {
					$onclick_title = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_album_url_ajax($album['id'], $linkpage)."', '".$href_title."')";
					$href_title = "#";
				}
				break;
			case 'slide':
				$href_title = wppa_convert_to_pretty(wppa_get_slideshow_url('', $linkpage));
				if ( $wppa_opt['wppa_allow_ajax'] ) {
					$onclick_title = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_slideshow_url_ajax($album['id'], $linkpage)."', '".$href_title."')";
					$href_title = "#";
				}
				break;
			case 'none':
				break;
			default:
		}
		$title = __a('View the album').' '.esc_attr(wppa_qtrans(stripslashes($album['name'])));
	}
	else {	// No content on current page/post
		if ($photocount > '0') {	// coverphotos only
			if ( $coverphoto ) {
				$href_title = wppa_convert_to_pretty(wppa_get_image_page_url_by_id($coverphoto)); 
			}
			else {
				$href_title = '#';
			}
			if ( $wppa_opt['wppa_allow_ajax'] ) {
				if ( $coverphoto ) {
					$onclick_title = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_image_url_ajax_by_id($coverphoto)."', '".$href_title."')";
				}
				else {
					$onclick_title = '';
				}
				$href_title = "#";
			}
			if ($photocount == '1') $title = __a('View the cover photo'); 
			else $title = __a('View the cover photos');
		}
	}
	
	// Find the slideshow link and onclick
	$href_slideshow = wppa_convert_to_pretty(wppa_get_slideshow_url('', $linkpage));
	if ( $wppa_opt['wppa_allow_ajax'] && ! $linkpage ) {
		$onclick_slideshow = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_slideshow_url_ajax($album['id'], $linkpage)."', '".wppa_convert_to_pretty($href_slideshow)."')";
		$href_slideshow = "#";
	}

	// Find the content 'View' link 
	$href_content = wppa_convert_to_pretty(wppa_get_album_url($album['id'], $linkpage));
	if ( $wppa_opt['wppa_allow_ajax'] && ! $linkpage ) {
		$onclick_content = "wppaDoAjaxRender(".$wppa['master_occur'].", '".wppa_get_album_url_ajax($album['id'], $linkpage)."', '".wppa_convert_to_pretty($href_content)."')";
		$href_content = "#";
	}

	// Find the coverphoto link
	if ( $coverphoto ) {
		$photolink = wppa_get_imglnk_a('coverimg', $coverphoto, $href_title, $title, $onclick_title, '', $albumid);
	}
	else $photolink = false;
	
	// Find the coverphoto details
	if ( $coverphoto ) {
		$src = wppa_get_thumb_url($coverphoto);	
		$path = wppa_get_thumb_path($coverphoto);
		$imgattr_a = wppa_get_imgstyle_a($path, $wppa_opt['wppa_smallsize'], '', 'cover');
	}
	else {
		$src = '';
		$path = '';
		$imgattr_a = false;
	}
	if (is_feed()) {
		$events = '';
	}
	else {
		$events = wppa_get_imgevents('cover');
	}
	$photo_pos = $wppa['coverphoto_pos'];
	
	$style =  __wcs('wppa-box').__wcs('wppa-'.$wppa_alt);
	if (is_feed()) $style .= ' padding:7px;';
	
	$wid = wppa_get_cover_width('cover');
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
	wppa_step_covercount('cover');
	
	$target = $wppa_opt['wppa_allow_ajax'] ? '_self' : $photolink['target'];
	
	// Open the album box
	$wppa['out'] .= wppa_nltab('+').'<div id="album-'.$album['id'].'-'.$wppa['master_occur'].'" class="album wppa-box wppa-cover-box wppa-cover-box-'.$wppa['master_occur'].' wppa-'.$wppa_alt.'" style="'.$style.'" >';

		if ( $photo_pos == 'left' || $photo_pos == 'top') {
			// First The Cover photo
			wppa_the_coverphoto($image, $src, $photo_pos, $photolink, $title, $imgattr_a, $events);
		}
		
		// The Cover text
		$textframestyle = wppa_get_text_frame_style($photo_pos, 'cover');
		$wppa['out'] .= wppa_nltab('+').'<div id="covertext_frame_'.$album['id'].'_'.$wppa['master_occur'].'" class="wppa-text-frame-'.$wppa['master_occur'].' wppa-text-frame covertext-frame wppa-asym-text-frame-'.$wppa['master_occur'].'" '.$textframestyle.'>';

			// The Album title
			$wppa['out'] .= wppa_nltab('+').'<h2 class="wppa-title" style="clear:none; '.__wcs('wppa-title').'">';
				if ($href_title != '') { 
					if ($href_title == '#') {
						$wppa['out'] .= wppa_nltab().'<a onclick="'.$onclick_title.'" title="'.$title.'" class="wppa-title" style="cursor:pointer; '.__wcs('wppa-title').'">'.wppa_qtrans(stripslashes($album['name'])).'</a>';
					}
					else {
						$wppa['out'] .= wppa_nltab().'<a href="'.$href_title.'" target="'.$target.'" onclick="'.$onclick_title.'" title="'.$title.'" class="wppa-title" style="'.__wcs('wppa-title').'">'.wppa_qtrans(stripslashes($album['name'])).'</a>';
					}
				} else { 
					$wppa['out'] .= wppa_qtrans(stripslashes($album['name'])); 
				} 
				if ( wppa_is_album_new($album['id']) ) {
					$wppa['out'] .= wppa_nltab().'<img src="'.WPPA_URL.'/images/new.png" title="New!" class="wppa-albumnew" style="border:none; margin:0; padding:0; box-shadow:none; " />';
				}
			$wppa['out'] .= wppa_nltab('-').'</h2>';
			

			
			// The 'Slideshow'/'Browse' link
			if ( $wppa_opt['wppa_show_slideshowbrowselink'] ) {
				$wppa['out'] .= wppa_nltab('+').'<div class="wppa-box-text wppa-black wppa-info wppa-slideshow-browse-link">';
					if ($photocount > $mincount) { 
						$label = $wppa_opt['wppa_enable_slideshow'] ?  __a('Slideshow') : __a('Browse photos');
						if ( $href_slideshow == '#' ) {
							$wppa['out'] .= wppa_nltab().'<a onclick="'.$onclick_slideshow.'" title="'.$label.'" style="'.__wcs('wppa-box-text', 'nocolor').'" >'.$label.'</a>';
						}
						else {
							$wppa['out'] .= wppa_nltab().'<a href="'.$href_slideshow.'" target="'.$target.'" onclick="'.$onclick_slideshow.'" title="'.$label.'" style="'.__wcs('wppa-box-text', 'nocolor').'" >'.$label.'</a>';
						}
					} else $wppa['out'] .= '&nbsp;'; 
				$wppa['out'] .= wppa_nltab('-').'</div>';
			}

			// The 'View' link
			if ( $wppa_opt['wppa_show_viewlink'] ) {
				$wppa['out'] .= wppa_nltab('+').'<div class="wppa-box-text wppa-black wppa-info">';
					if ($has_content) {
						if ($wppa_opt['wppa_thumbtype'] == 'none') $photocount = '0'; 	// Fake photocount to prevent link to empty page
						if ($photocount > $mincount || $albumcount) {					// Still has content
							if ( $wppa_opt['wppa_show_treecount'] ) {
								$treecount = wppa_treecount_a($album['id']);
							}
							else $treecount = false;
							if ( $href_content == '#' ) {
								$wppa['out'] .= wppa_nltab('+').'<a onclick="'.$onclick_content.'" title="'.__a('View the album').' '.esc_attr(stripslashes(wppa_qtrans($album['name']))).'" style="'.__wcs('wppa-box-text', 'nocolor').'" >';
							}
							else {
								$wppa['out'] .= wppa_nltab('+').'<a href="'.$href_content.'" target="'.$target.'" onclick="'.$onclick_content.'" title="'.__a('View the album').' '.esc_attr(stripslashes(wppa_qtrans($album['name']))).'" style="'.__wcs('wppa-box-text', 'nocolor').'" >';
							}
							$wppa['out'] .= __a('View');
							if ($albumcount) { 
								if ($albumcount == '1') {
									$wppa['out'] .= ' 1 '.__a('album'); 
								}
								else {
									$wppa['out'] .= ' '.$albumcount.' '.__a('albums');
								}
								if ( $treecount ) {
									if ( $treecount['albums'] != $albumcount ) {
										$wppa['out'] .= ' ('.$treecount['albums'].')';
									}
								}
							}
							if ($photocount > $mincount && $albumcount) {
								$wppa['out'] .= ' '.__a('and'); 
							}
							if ($photocount > $mincount || $treecount) { 
								if ( $photocount <= $mincount ) $photocount = '0';
								if ($photocount == '1') {
									$wppa['out'] .= ' 1 '.__a('photo');
								}
								elseif ($photocount) {
									$wppa['out'] .= ' '.$photocount.' '.__a('photos'); 
								}
								if ( $treecount ) {
									if ( $treecount['photos'] != $photocount ) {
										if ( ! $photocount ) $wppa['out'] .= ', '.__a('photos'); 
										$wppa['out'] .= ' ('.$treecount['photos'].')';
									}
								}
							} 
							$wppa['out'] .= wppa_nltab('-').'</a>'; 
						}
					} 
				$wppa['out'] .= wppa_nltab('-').'</div>';
			}
			

			
		$wppa['out'] .= wppa_nltab('-').'</div>';
		
		if ( $photo_pos == 'right' || $photo_pos == 'bottom' ) {
			// The Cover photo last
			wppa_the_coverphoto($image, $src, $photo_pos, $photolink, $title, $imgattr_a, $events);
		}

		// The Album description
		if ( $wppa_opt['wppa_show_cover_text'] ) {
			$textheight = $wppa_opt['wppa_text_frame_height'] > '0' ? 'min-height:'.$wppa_opt['wppa_text_frame_height'].'px; ' : '';
			$wppa['out'] .= wppa_nltab().'<div id="coverdesc_frame_'.$album['id'].'_'.$wppa['master_occur'].'" style="clear:both" ><p class="wppa-box-text wppa-black" style="'.$textheight.__wcs('wppa-box-text').__wcs('wppa-black').'">'.wppa_get_album_desc($album['id']).'</p></div>';
		}
		
		
		
		$wppa['out'] .= wppa_nltab().'<div style="clear:both;"></div>';		
		
		wppa_user_upload_html($album['id'], wppa_get_cover_width('cover'), 'cover');

	$wppa['out'] .= wppa_nltab('-').'</div><!-- #album-'.$album['id'].'-'.$wppa['master_occur'].' -->';
	if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
}

function wppa_the_coverphoto($image, $src, $photo_pos, $photolink, $title, $imgattr_a, $events) {
global $wppa;
global $album;
global $wppa_opt;
global $thumb;
global $wpdb;

	if ( $image ) { 
	
		$imgattr   = $imgattr_a['style'];
		$imgwidth  = $imgattr_a['width'];
		$imgheight = $imgattr_a['height'];
		$frmwidth  = $imgwidth + '10';	// + 2 * 1 border + 2 * 4 padding

		if ($wppa['in_widget']) $photoframestyle = 'style="text-align:center; "';
		else {
 			switch ( $photo_pos ) {
				case 'left':
					$photoframestyle = 'style="float:left; margin-right:5px;width:'.$frmwidth.'px;"';
					break;
				case 'right':
					$photoframestyle = 'style="float:right; margin-left:5px;width:'.$frmwidth.'px;"';
					break;
				case 'top':
					$photoframestyle = 'style="text-align:center;"';//width:'.wppa_get_cover_width('cover').'px;"';
					break;
				case 'bottom':
					$photoframestyle = 'style="text-align:center;"';//width:'.wppa_get_cover_width('cover').'px;"';
					break;
				default :
					wppa_dbg_msg('Illegal $photo_pos in wppa_the_coverphoto');
			}
		}
		$wppa['out'] .= wppa_nltab('+').'<div id="coverphoto_frame_'.$album['id'].'_'.$wppa['master_occur'].'" class="coverphoto-frame" '.$photoframestyle.'>';
		if ( $photolink ) {
			if ( $photolink['is_lightbox'] ) {
				$thumbs = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_PHOTOS."` WHERE `album` = %s ".wppa_get_photo_order($album['id']), $album['id']), ARRAY_A );
				if ( $thumbs ) foreach ( $thumbs as $thumb ) {
					$title = wppa_get_lbtitle('cover', $thumb['id']);
					$link = wppa_get_photo_url($thumb['id']);
					$wppa['out'] .= "\n\t".'<a href="'.$link.'" rel="'.$wppa_opt['wppa_lightbox_name'].'[alw-'.$wppa['master_occur'].'-'.$album['id'].']" title="'.$title.'" >';
					if ( $thumb['id'] == $image['id'] ) {		// the cover image
						$wppa['out'] .= "\n\t\t".'<img class="image wppa-img" id="i-'.$image['id'].'-'.$wppa['master_occur'].'" title="'.wppa_zoom_in().'" src="'.$src.'" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.__wcs('wppa-img').$imgattr.$imgattr_a['cursor'].'" '.$events.' alt="'.$title.'">';
					}
					$wppa['out'] .= "\n\t".'</a>';
				}
			}
			else {
				$href = $photolink['url'] == '#' ? '' : 'href="'.$photolink['url'].'" ';
				$wppa['out'] .= wppa_nltab('+').'<a '.$href.'target="'.$photolink['target'].'" title="'.$photolink['title'].'" onclick="'.$photolink['onclick'].'" >';
					$wppa['out'] .= wppa_nltab().'<img src="'.$src.'" alt="'.$title.'" class="image wppa-img" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.__wcs('wppa-img').$imgattr.'" '.$events.' />';
				$wppa['out'] .= wppa_nltab('-').'</a>'; 
			}
		} else { 
			$wppa['out'] .= wppa_nltab().'<img src="'.$src.'" alt="'.$title.'" class="image wppa-img" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.__wcs('wppa-img').$imgattr.'" '.$events.' />';
		} 
		$wppa['out'] .= wppa_nltab('-').'</div><!-- #coverphoto_frame_'.$album['id'].'_'.$wppa['master_occur'].' -->'; 
	} 
}

function wppa_the_coverphotos($images, $srcs, $photo_pos, $photolink, $title, $imgattrs_a, $events) {
global $wppa;
global $album;
global $wppa_opt;
global $thumb;
global $wpdb;

	if ( $images ) { 
	
//		if ($wppa['in_widget']) 
$photoframestyle = 'style="text-align:center; "';
//		else {
 //			switch ( $photo_pos ) {
//				case 'left':
//					$photoframestyle = 'style="float:left; margin-right:5px;width:'.$frmwidth.'px;"';
//					break;
//				case 'right':
//					$photoframestyle = 'style="float:right; margin-left:5px;width:'.$frmwidth.'px;"';
//					break;
//				case 'top':
//					$photoframestyle = 'style="text-align:center;width:'.wppa_get_cover_width('cover').'px;"';
//					break;
//				case 'bottom':
//					$photoframestyle = 'style="text-align:center;width:'.wppa_get_cover_width('cover').'px;"';
//					break;
//				default :
//					wppa_dbg_msg('Illegal $photo_pos in wppa_the_coverphotos: '.$photo_pos);
//			}
//		}

		$wppa['out'] .= wppa_nltab('+').'<div id="coverphoto_frame_'.$album['id'].'_'.$wppa['master_occur'].'" class="coverphoto-frame" '.$photoframestyle.'>';
		for ( $idx='0'; $idx<count($images); $idx++ ) {
			$image 		= $images[$idx];
			$src 		= $srcs[$idx];
			$imgattr   	= $imgattrs_a[$idx]['style'];
			$imgwidth  	= $imgattrs_a[$idx]['width'];
			$imgheight 	= $imgattrs_a[$idx]['height'];
			$frmwidth  	= $imgwidth + '10';	// + 2 * 1 border + 2 * 4 padding
			$imgattr_a	= $imgattrs_a[$idx];

			if ( $photolink ) {
				if ( $photolink['is_lightbox'] ) {
				
					$thumb = $image;
					
//					$thumbs = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_PHOTOS."` WHERE `album` = %s ".wppa_get_photo_order($album['id']), $album['id']), ARRAY_A );
//					if ( $thumbs ) foreach ( $thumbs as $thumb ) {
						$title = wppa_get_lbtitle('cover', $thumb['id']);
						$link = wppa_get_photo_url($thumb['id']);
						$wppa['out'] .= "\n\t".'<a href="'.$link.'" rel="'.$wppa_opt['wppa_lightbox_name'].'[alw-'.$wppa['master_occur'].'-'.$album['id'].']" title="'.$title.'" >';
						if ( $thumb['id'] == $image['id'] ) {		// the cover image
							$wppa['out'] .= "\n\t\t".'<img class="image wppa-img" id="i-'.$image['id'].'-'.$wppa['master_occur'].'" title="'.wppa_zoom_in().'" src="'.$src.'" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.__wcs('wppa-img').$imgattr.$imgattr_a['cursor'].'" '.$events.' alt="'.$title.'">';
						}
						$wppa['out'] .= "\n\t".'</a>';
//					}
				}
				else {
					$href = $photolink['url'] == '#' ? '' : 'href="'.$photolink['url'].'" ';
					$wppa['out'] .= wppa_nltab('+').'<a '.$href.'target="'.$photolink['target'].'" title="'.$photolink['title'].'" onclick="'.$photolink['onclick'].'" >';
						$wppa['out'] .= wppa_nltab().'<img src="'.$src.'" alt="'.$title.'" class="image wppa-img" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.__wcs('wppa-img').$imgattr.'" '.$events.' />';
					$wppa['out'] .= wppa_nltab('-').'</a>'; 
				}
			} else { 
				$wppa['out'] .= wppa_nltab().'<img src="'.$src.'" alt="'.$title.'" class="image wppa-img" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.__wcs('wppa-img').$imgattr.'" '.$events.' />';
			} 
		}
		$wppa['out'] .= wppa_nltab('-').'</div><!-- #coverphoto_frame_'.$album['id'].'_'.$wppa['master_occur'].' -->'; 
	} 
}
		
function wppa_thumb_ascover() {
global $thumb;
global $wppa;
global $wppa_opt;
global $wppa_alt;
global $cover_count_key;
global $thlinkmsggiven;

	$path = wppa_get_thumb_path($thumb['id']); 
	$imgattr_a = wppa_get_imgstyle_a($path, $wppa_opt['wppa_smallsize'], '', 'cover'); 
	$events = is_feed() ? '' : wppa_get_imgevents('cover'); 
	$src = wppa_get_thumb_url($thumb['id']); 
	$link = wppa_get_imglnk_a('thumb', $thumb['id']);

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

	$src       = wppa_get_thumb_path($thumb['id']); 
	// $maxsize = $wppa['in_widget'] ? $wppa_opt['wppa_comment_size'] : $wppa_opt['wppa_thumbsize'];
	// there is also:                  $wppa_opt['wppa_topten_size'] 
	// So, what to do with a WPPA+ Text widget ???
	$alt 		= is_array($album) && $album['alt_thumbsize'] == 'yes' ? '_alt' : '';
	$imgattr_a 	= wppa_get_imgstyle_a($src, $wppa_opt['wppa_thumbsize'.$alt], 'optional', 'thumb'); 

	$imgstyle  	= $imgattr_a['style'];
	$imgwidth  	= $imgattr_a['width'];
	$imgheight 	= $imgattr_a['height'];
	$cursor	   	= $imgattr_a['cursor'];

	$url       	= wppa_get_thumb_url($thumb['id']); 
	$events    	= wppa_get_imgevents('thumb', $thumb['id']); 
//	$thumbname 	= esc_attr(wppa_qtrans($thumb['name']));
//	$altforpopup = $wppa_opt['wppa_popup_text_name'] ? esc_attr(stripslashes($thumbname)) : '';	// Added esc_attr(stripslashes()) in 4.3.11
	
	$imgalt		= wppa_get_imgalt($thumb['id']);	// returns something like ' alt="Any text" '

//	if ( $wppa_opt['wppa_use_thumb_popup'] ) {
//		$title = $wppa_opt['wppa_popup_text_desc'] ? $thumb['description'] : '';
//		$title = wppa_filter_exif(wppa_filter_iptc($title,$thumb['id']),$thumb['id']);
//		if ( $wppa_opt['wppa_popup_text_desc_strip'] ) {
//			$title = wppa_strip_tags($title);
//		}
//		$title = esc_attr(__($title));
//		$title = esc_attr(stripslashes(__($title)));
//	}
//	else {
//		$title = esc_attr(wppa_get_photo_name($thumb['id']));	// esc_attr was esc_js prior to 4.0.7
//	}
	$title = esc_attr(wppa_get_photo_name($thumb['id']));
	
	if (is_feed()) {
		$imgattr_a = wppa_get_imgstyle_a($src, '100', '4', 'thumb');
		$style = $imgattr_a['style'];
		$wppa['out'] .= wppa_nltab().'<a href="'.get_permalink().'"><img src="'.$url.'" '.$imgalt.' title="'.$title.'" style="'.$style.'" /></a>';
		return;
	}
	
	
	$wppa['out'] .= wppa_nltab('+').'<div id="thumbnail_frame_'.$thumb['id'].'_'.$wppa['master_occur'].'" class="thumbnail-frame thumbnail-frame-'.$wppa['master_occur'].' thumbnail-frame-photo-'.$thumb['id'].'" style="'.wppa_get_thumb_frame_style().'" >';

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
				&& ! wppa_get_searchstring()											// no search findings
				&& ( is_numeric($wppa['start_album']) || $wppa['start_album'] == '' )	// no set of albums
	//			&& ! $wppa_opt['wppa_use_thumb_popup']									// links from popups now also supported
				) 
			{ 	// Ajax	possible

				// Get the album info
				wppa_cache_album($thumb['album']);
			
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
	
	if ( $wppa['src'] || ( ( $wppa['is_comten'] || $wppa['is_topten'] || $wppa['is_lasten'] || $wppa['is_featen'] ) && $wppa['start_album'] != $thumb['album'] ) ) {
		$wppa['out'] .= wppa_nltab().'<div class="wppa-thumb-text" style="'.__wcs('wppa-thumb-text').'" >(<a href="'.wppa_get_album_url($thumb['album']).'">'.stripslashes(__(wppa_get_album_name($thumb['album']))).'</a>)</div>';
	}
	
	$new = wppa_is_photo_new($thumb['id']);		
	if ($wppa_opt['wppa_thumb_text_name'] || $new) {
		$wppa['out'] .= wppa_nltab().'<div class="wppa-thumb-text" style="'.__wcs('wppa-thumb-text').'" >';
			if ($wppa_opt['wppa_thumb_text_name']) $wppa['out'] .= wppa_get_photo_name($thumb['id'], $wppa_opt['wppa_thumb_text_owner']); // wppa_qtrans(stripslashes($thumb['name']));
			if ($new) $wppa['out'] .= '&nbsp;<img src="'.WPPA_URL.'/images/new.png" title="New!" class="wppa-thumbnew" style="border:none; margin:0; padding:0; box-shadow:none; " />';
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
		$wppa['out'] .= wppa_nltab().'<div class="wppa-thumb-text" style="'.__wcs('wppa-thumb-text').'" >'.$desc.'</div>';
	}
	
	if ($wppa_opt['wppa_thumb_text_rating']) {
		$rating = wppa_get_rating_by_id($thumb['id']);
		if ( $rating && $wppa_opt['wppa_show_rating_count'] ) $rating .= ' ('.wppa_get_rating_count_by_id($thumb['id']).')';
		$wppa['out'] .= wppa_nltab().'<div class="wppa-thumb-text" style="'.__wcs('wppa-thumb-text').'" >'.$rating.'</div>';
	}
		
	$wppa['out'] .= wppa_nltab('-').'</div><!-- #thumbnail_frame_'.$thumb['id'].'_'.$wppa['master_occur'].' -->';
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
		$wppa['out'] .= wppa_nltab().'<a href="'.get_permalink().'"><img src="'.wppa_get_photo_url($wppa['start_photo']).'" style="'.$style.'" width="'.$width.'" height="'.$height.'" '.$imgalt.'/></a>';
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
			wppa_dbg_msg('SlideInfo took '.$t.' seconds.', 'red');
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
			if ( $wppa_opt['wppa_slideshow_linktype'] == 'lightbox' || $wppa_opt['wppa_film_linktype'] == 'lightbox' ) {
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
	
	$imgattr_a = wppa_get_imgstyle_a($src, $max_size, 'optional', 'fthumb'); 
	$imgstyle  = $imgattr_a['style'];
	$imgwidth  = $imgattr_a['width'];
	$imgheight = $imgattr_a['height'];
	$cursor    = $imgattr_a['cursor'];
		
	$url = wppa_get_thumb_url($thumb['id']); 
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
		
			if ( $tmp == 'film' ) $wppa['out'] .= '<!--';
				$wppa['out'] .= '<img id="wppa-'.$tmp.'-'.$idx.'-'.$wppa['master_occur'].'" class="wppa-'.$tmp.'-'.$wppa['master_occur'].'" src="'.$url.'" alt="'.$alt.'" '. //title="'.$title.'" '.
					//width="'.$imgwidth.'" height="'.$imgheight.'" 
					'style="'.$imgstyle.$cursor.'" '.$events.' />';
			if ( $tmp == 'film' ) $wppa['out'].='-->';
			
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

function __wcs($class = '', $nocolor = '') {
global $wppa_opt;
global $wppa;

	$opt = '';
	$result = '';
	switch ($class) {
		case 'wppa-box':
			$opt = $wppa_opt['wppa_bwidth'];
			if ($opt > '0') $result .= 'border-style: solid; border-width:'.$opt.'px; ';
			$opt = $wppa_opt['wppa_bradius'];
			if ($opt > '0') {
				$result .= 'border-radius:'.$opt.'px; ';
				$result .= '-moz-border-radius:'.$opt.'px; -khtml-border-radius:'.$opt.'px; -webkit-border-radius:'.$opt.'px; ';
			}
			$opt = $wppa_opt['wppa_box_spacing'];
			if ( $opt != '' ) {
				$result .= 'margin-bottom:'.$opt.'px; ';
			}
			break;
		case 'wppa-mini-box':
			$opt = $wppa_opt['wppa_bwidth'];
			if ($opt > '0') {
				$opt = floor(($opt + 2) / 3);
				$result .= 'border-style: solid; border-width:'.$opt.'px; ';
			}
			$opt = $wppa_opt['wppa_bradius'];
			if ($opt > '0') {
				$opt = floor(($opt + 2) / 3);
				$result .= 'border-radius:'.$opt.'px; ';
				$result .= '-moz-border-radius:'.$opt.'px; -khtml-border-radius:'.$opt.'px; -webkit-border-radius:'.$opt.'px; ';
			}
			break;
		case 'wppa-thumb-text':
			$opt = $wppa_opt['wppa_fontfamily_thumb'];
			if ($opt != '') $result .= 'font-family:'.$opt.'; ';
			$opt = $wppa_opt['wppa_fontsize_thumb'];
			if ($opt != '') {
				$ls = floor($opt * 1.29);
				$result .= 'font-size:'.$opt.'px; line-height:'.$ls.'px; ';
			}
			$opt = $wppa_opt['wppa_fontcolor_thumb'];
			if ($opt != '') $result .= 'color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_fontweight_thumb'];
			if ($opt != '') $result .= 'font-weight:'.$opt.'; ';
			break;
		case 'wppa-box-text':
			$opt = $wppa_opt['wppa_fontfamily_box'];
			if ($opt != '') $result .= 'font-family:'.$opt.'; ';
			$opt = $wppa_opt['wppa_fontsize_box'];
			if ($opt != '') $result .= 'font-size:'.$opt.'px; ';
			$opt = $wppa_opt['wppa_fontcolor_box'];
			if ($opt != '' && $nocolor != 'nocolor') $result .= 'color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_fontweight_box'];
			if ($opt != '') $result .= 'font-weight:'.$opt.'; ';
			break;
		case 'wppa-comments':
			$opt = $wppa_opt['wppa_bgcolor_com'];
			if ($opt) $result .= 'background-color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_bcolor_com'];
			if ($opt) $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-iptc':
			$opt = $wppa_opt['wppa_bgcolor_iptc'];
			if ($opt) $result .= 'background-color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_bcolor_iptc'];
			if ($opt) $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-exif':
			$opt = $wppa_opt['wppa_bgcolor_exif'];
			if ($opt) $result .= 'background-color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_bcolor_exif'];
			if ($opt) $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-share':
			$opt = $wppa_opt['wppa_bgcolor_share'];
			if ($opt) $result .= 'background-color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_bcolor_share'];
			if ($opt) $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-name-desc':
			$opt = $wppa_opt['wppa_bgcolor_namedesc'];
			if ($opt) $result .= 'background-color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_bcolor_namedesc'];
			if ($opt) $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-nav':
			$opt = $wppa_opt['wppa_bgcolor_nav'];
			if ($opt != '') $result .= 'background-color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_bcolor_nav'];
			if ($opt != '') $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-nav-text':
			$opt = $wppa_opt['wppa_fontfamily_nav'];
			if ($opt != '') $result .= 'font-family:'.$opt.'; ';
			$opt = $wppa_opt['wppa_fontsize_nav'];
			if ($opt != '') $result .= 'font-size:'.$opt.'px; ';
			$opt = $wppa_opt['wppa_fontcolor_nav'];
			if ($opt != '') $result .= 'color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_fontweight_nav'];
			if ($opt != '') $result .= 'font-weight:'.$opt.'; ';
			break;
		case 'wppa-even':
			$opt = $wppa_opt['wppa_bgcolor_even'];
			if ($opt != '') $result .= 'background-color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_bcolor_even'];
			if ($opt != '') $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-alt':
			$opt = $wppa_opt['wppa_bgcolor_alt'];
			if ($opt != '') $result .= 'background-color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_bcolor_alt'];
			if ($opt != '') $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-img':
			$opt = $wppa_opt['wppa_bgcolor_img'];
			if ($opt != '') $result .= 'background-color:'.$opt.'; ';
			break;
		case 'wppa-title':
			$opt = $wppa_opt['wppa_fontfamily_title'];
			if ($opt != '') $result .= 'font-family:'.$opt.'; ';
			$opt = $wppa_opt['wppa_fontsize_title'];
			if ($opt != '') $result .= 'font-size:'.$opt.'px; ';
			$opt = $wppa_opt['wppa_fontcolor_title'];
			if ($opt != '') $result .= 'color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_fontweight_title'];
			if ($opt != '') $result .= 'font-weight:'.$opt.'; ';
			break;
		case 'wppa-fulldesc':
			$opt = $wppa_opt['wppa_fontfamily_fulldesc'];
			if ($opt != '') $result .= 'font-family:'.$opt.'; ';
			$opt = $wppa_opt['wppa_fontsize_fulldesc'];
			if ($opt != '') $result .= 'font-size:'.$opt.'px; ';
			$opt = $wppa_opt['wppa_fontcolor_fulldesc'];
			if ($opt != '') $result .= 'color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_fontweight_fulldesc'];
			if ($opt != '') $result .= 'font-weight:'.$opt.'; ';
			break;
		case 'wppa-fulltitle':
			$opt = $wppa_opt['wppa_fontfamily_fulltitle'];
			if ($opt != '') $result .= 'font-family:'.$opt.'; ';
			$opt = $wppa_opt['wppa_fontsize_fulltitle'];
			if ($opt != '') $result .= 'font-size:'.$opt.'px; ';
			$opt = $wppa_opt['wppa_fontcolor_fulltitle'];
			if ($opt != '') $result .= 'color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_fontweight_fulltitle'];
			if ($opt != '') $result .= 'font-weight:'.$opt.'; ';
			break;
		case 'wppa-custom':
			$opt = $wppa_opt['wppa_bgcolor_cus'];
			if ($opt) $result .= 'background-color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_bcolor_cus'];
			if ($opt) $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-upload':
			$opt = $wppa_opt['wppa_bgcolor_upload'];
			if ($opt) $result .= 'background-color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_bcolor_upload'];
			if ($opt) $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-black':
//			$opt = $wppa_opt['wppa_black'];
//			if ($opt != '') $result .= 'color:'.$opt.'; ';
//			break;
			break;
		case 'wppa-arrow':
			$opt = $wppa_opt['wppa_arrow_color'];
			if ($opt != '') $result .= 'color:'.$opt.'; ';
			break;
		case 'wppa-td';
			$result .= 'padding: 3px 2px 3px 0; border: 0';
			break;
		default:
			wppa_dbg_msg('Unexpected error in __wcs, unknown class: '.$class, 'red');
	}
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
					wppa_dbg_msg('Illegal $photo_pos in wppa_get_text_frame_style');
			}
		}
		else wppa_dbg_msg('Illegal $type in wppa_get_text_frame_style');
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
	$result -= 14;	// 2 * padding
	$result -= 2 * $wppa_opt['wppa_bwidth'];
	return $result;
}

function wppa_get_box_delta() {
	return wppa_get_container_width() - wppa_get_box_width();
}

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
	$usethumb	= wppa_use_thumb_file($wppa['single_photo'], $width, $height) ? 'thumbs/' : '';
	$src 		= str_replace('/wppa/', '/wppa/'.$usethumb , wppa_get_photo_url($wppa['single_photo']));
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
		$wppa['out'] .= '<p class="wp-caption-text">'.wppa_get_photo_desc($wppa['single_photo']).'</p>';

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
	$usethumb	= wppa_use_thumb_file($wppa['single_photo'], $width, $height) ? 'thumbs/' : '';
	$src 		= str_replace('/wppa/', '/wppa/'.$usethumb , wppa_get_photo_url($wppa['single_photo']));

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
			$page = '';
			$result['url'] = '';
			if ($type == 'lightbox' || $type == 'file') $result['title'] = wppa_zoom_in();
			$result['target'] = '';
			return $result;
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
	
if ($wich=='comten') $album='0';

if ( $wppa['is_tag'] ) $album='0';
	
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
			$result['url'] = wppa_get_photo_url($photo);
			$result['title'] = $title; 
			$result['is_url'] = true;
			$result['is_lightbox'] = false;
			return $result;
			break;
		case 'lightbox':
			$result['url'] = wppa_get_photo_url($photo);
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
			$url = wppa_get_photo_url($photo);
			$imgsize = getimagesize(wppa_get_photo_path($photo));
			if ($imgsize) {
				$wid = $imgsize['0'];
				$hig = $imgsize['1'];
			}
			else {
				$wid = '0';
				$hig = '0';
			}

			$result['url'] = "wppaFullPopUp(".$wppa['master_occur'].", ".$photo.", '".$url."', ".$wid.", ".$hig.", '".admin_url('admin-ajax.php')."')";

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
		default:
			wppa_dbg_msg('Error, wrong type: '.$type.' in wppa_get_imglink_a');
			return false;
			break;
	}
	
	if ( $wppa['src'] ) { //(isset($_REQUEST['wppa-searchstring'])) {
		$result['url'] .= '&amp;wppa-searchstring='.urlencode($wppa['searchstring']);//strip_tags($_REQUEST['wppa-searchstring']));
	}

	if ($wich == 'topten') {
		$result['url'] .= '&amp;wppa-topten='.$wppa_opt['wppa_topten_count'];
	}
	elseif ($wppa['is_topten']) {
		$result['url'] .= '&amp;wppa-topten='.$wppa['topten_count'];
	}
	
	if ( $type != 'thumbalbum' ) {
		if ($wich == 'lasten') {
			$result['url'] .= '&amp;wppa-lasten='.$wppa_opt['wppa_lasten_count'];
		}
		elseif ($wppa['is_lasten']) {
			$result['url'] .= '&amp;wppa-lasten='.$wppa['lasten_count'];
		}
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
	
	if ( $wppa['is_tag'] ) {
		$result['url']  .= '&amp;wppa-tag='.$wppa['is_tag'];
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
	
	// Post processing needed?
	if ( $index == 'photo' && ! is_numeric($result) ) {
		$result = wppa_get_photo_id_by_name($result, wppa_get_get('album'));
		if ( ! $result ) $result = $default;
	}
	if ( $index == 'album' ) {
		if ( ! is_numeric($result) ) {
			$temp = wppa_get_album_id_by_name($result);
			if ( is_numeric($temp) && $temp > '0' ) {
				$result = $temp;
			}
			elseif ( ! wppa_is_enum($result) ) {
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
		return $_POST['wppa-'.$index];
	}
	if (isset($_POST[$index])) {				// Old syntax
		return $_POST[$index];
	}
	return $default;
}

function wppa_get_photo_id_by_name($xname, $album = '0') {
global $wpdb;
global $allphotos;

	// Do a first guess, assume no quotes and no language
	$guess = $wpdb->get_var($wpdb->prepare( "SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `name` = %s AND `album` = %s ", $xname, $album) );
	if ( $guess ) {
		wppa_dbg_msg('wppa_get_photo_id_by_name() first guess succesfull!', 'green');
		return $guess;
	}
	wppa_dbg_msg('wppa_get_photo_id_by_name() first guess NOT succesfull!', 'red');
	
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
					if ( $allphotos[$index]['album'] == $album ) return $allphotos[$index]['id'];	// Found!
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

function wppa_user_upload_html($alb, $width, $where = '') {
global $wppa;
global $wppa_opt;

	// May I?
	if ( ! $wppa_opt['wppa_user_upload_on'] ) return;			// Feature not enabled
	if ( wppa_switch('wppa_user_upload_login') ) {
		if ( ! is_user_logged_in() ) return;					// Must login
	}
	if ( ! wppa_have_access($alb) ) {
		return;						// No album access
	}

	// Find max files for the user
	$allow_me = wppa_allow_user_uploads();
	if ( ! $allow_me ) {
		if ( wppa_switch('wppa_show_album_full') ) {
			$wppa['out'] .= '<span style="color:red">';
			$wppa['out'] .= __a('Max uploads reached');
			$time = wppa_time_to_wait_html('0', true);	// For the user
			$wppa['out'] .= $time;
			wppa_dbg_msg('Max for user '.wppa_get_user().' reached '.$time);
			$wppa['out'] .= '</span>';
		}
		return;													// Max quota reached
	}
	
	// Find max files for the album
	$allow_alb = wppa_allow_uploads($alb);
	if ( ! $allow_alb ) {
		if ( wppa_switch('wppa_show_album_full') ) {
			$wppa['out'] .= '<span style="color:red">';
			$wppa['out'] .= __a('Max uploads reached');
			$time = wppa_time_to_wait_html($alb);		// For the album
			$wppa['out'] .= $time;
			wppa_dbg_msg('Max for album '.$alb.' reached '.$time);
			$wppa['out'] .= '</span>';
		}
		return;													// Max quota reached
	}
	
	// Find max files for the system
	$allow_sys = ini_get('max_file_uploads');
	
	// THE max
//echo $allow_me.','.$allow_alb.','.$allow_sys.'<br />';	

	if ( $allow_me == '-1' ) $allow_me = $allow_sys;
	if ( $allow_alb == '-1' ) $allow_alb = $allow_sys;
	$max = min($allow_me, $allow_alb, $allow_sys);
//echo $allow_me.','.$allow_alb.','.$allow_sys.'<br />'.$max.'<br />';	
	// In a widget?
	$widget_upload = ( $wppa['in_widget'] == 'upload' );

	// Create the return url
	$returnurl = wppa_get_permalink();
	if ( $where == 'cover' ) {
		$returnurl .= 'wppa-album='.wppa_get_parentalbumid($alb).'&amp;wppa-cover=0&amp;wppa-occur='.$wppa['occur'];
	}
	elseif ( $where == 'thumb' ) {
		$returnurl .= 'wppa-album='.$alb.'&amp;wppa-cover=0&amp;wppa-occur='.$wppa['occur'];
	}
	elseif ( $where == 'widget' || $where == 'uploadbox' ) {
		// As is: permalink
		//$returnurl .= 'wppa-dummy=13';
	}
	if ( $wppa['page'] ) $returnurl .= '&amp;wppa-page='.$wppa['page'];
	
	// Make the HTML
	$wppa['out'] .= wppa_nltab().'<a id="wppa-up-'.$alb.'-'.$wppa['master_occur'].'" onclick="jQuery(\'#wppa-up-'.$alb.'-'.$wppa['master_occur'].'\').css(\'display\',\'none\');jQuery(\'#wppa-file-'.$alb.'-'.$wppa['master_occur'].'\').css(\'display\',\'block\');wppaColWidth['.$wppa['master_occur'].']=0;" class="" style="float:left; cursor:pointer;">'.__a('Upload Photo').'</a>';
	$wppa['out'] .= wppa_nltab('+').'<div id="wppa-file-'.$alb.'-'.$wppa['master_occur'].'" class="wppa-file-'.$wppa['master_occur'].'" style="width:'.$width.'px;text-align:center;display:none" >';
		$wppa['out'] .= wppa_nltab('+').'<form action="'.$returnurl.'" method="post" enctype="multipart/form-data">';
			$wppa['out'] .= wppa_nltab().wp_nonce_field('wppa-check' , 'wppa-nonce', false, false);		
			if ( ! $alb ) {	// No album given: select one
				$wppa['out'] .= wppa_nltab().'<select id="wppa-upload-'.$wppa['master_occur'].'" name="wppa-upload-album" style="float:left; max-width: '.$width.'px; ">'.wppa_album_select_a(	array (	'addpleaseselect' 	=> true, 
																												'checkaccess'		=> true,
																												'checkupload' 		=> true,
																												'path' 				=> wppa_switch('wppa_hier_albsel') )).
											'</select><br />';
			}
			else {
				$wppa['out'] .= wppa_nltab().'<input type="hidden" name="wppa-upload-album" value="'.$alb.'" />';
			}
			if ( wppa_switch('wppa_upload_one_only') && ! current_user_can('administrator') ) {
				$wppa['out'] .= wppa_nltab().'<input type="file" class="wppa-user-file" style="max-width: '.$width.'; margin: 6px 0; float:left; '.__wcs('wppa-box-text').'" id="wppa-user-upload-'.$alb.'-'.$wppa['master_occur'].'" name="wppa-user-upload-'.$alb.'-'.$wppa['master_occur'].'[]" onchange="jQuery(\'#wppa-user-submit-'.$alb.'-'.$wppa['master_occur'].'\').css(\'display\', \'block\')" />';
			}
			else {
				$wppa['out'] .= wppa_nltab().'<input type="file" multiple="multiple" class="wppa-user-file" style="max-width: '.$width.'; margin: 6px 0; float:left; '.__wcs('wppa-box-text').'" id="wppa-user-upload-'.$alb.'-'.$wppa['master_occur'].'" name="wppa-user-upload-'.$alb.'-'.$wppa['master_occur'].'[]" onchange="jQuery(\'#wppa-user-submit-'.$alb.'-'.$wppa['master_occur'].'\').css(\'display\', \'block\')" />';
			}
			
			$onclick = $alb ? '' : ' onclick="if ( document.getElementById(\'wppa-upload-'.$wppa['master_occur'].'\').value == 0 ) {alert(\''.esc_js(__a('Please select an album and try again')).'\');return false;}"';
			$wppa['out'] .= wppa_nltab().'<input type="submit" id="wppa-user-submit-'.$alb.'-'.$wppa['master_occur'].'"'.$onclick.' style="display:none; margin: 6px 0; float:right; '.__wcs('wppa-box-text').'" class="wppa-user-submit" name="wppa-user-submit-'.$alb.'-'.$wppa['master_occur'].'" value="'.__a('Upload').'" /><br />';
			
			if ( ! wppa_switch('wppa_upload_one_only') && ! current_user_can('administrator') ) {
//				$max = ini_get('max_file_uploads');
//				if ( $max && $allow > '0' && $allow < $max ) $max = $allow;
				if ( $max ) $wppa['out'] .= wppa_nltab().'<br /><span style="font-size:10px;" >'.sprintf(__a('You may upload up to %s photos at once if your browser supports HTML-5 multiple file upload'), $max).'</span>';
				$maxsize = wppa_check_memory_limit(false);
				if ( is_array($maxsize) ) {
					$wppa['out'] .= wppa_nltab().'<br /><span style="font-size:10px;" >'.sprintf(__a('Max photo size: %d x %d (%2.1f MegaPixel)'), $maxsize['maxx'], $maxsize['maxy'], $maxsize['maxp']/(1024*1024) ).'</span>';
				}
			}
			if ( $wppa_opt['wppa_copyright_on'] ) {
				$wppa['out'] .= wppa_nltab().'<div id="wppa-copyright-'.$wppa['master_occur'].'" style="clear:both;" >'.__($wppa_opt['wppa_copyright_notice']).'</div>';
			}
			// Watermark
			if ( $wppa_opt['wppa_watermark_on'] == 'yes' && ( $wppa_opt['wppa_watermark_user'] == 'yes' || current_user_can('wppa_settings') ) ) { 
				$wppa['out'] .= wppa_nltab('+').'<table class="wppa-watermark wppa-box-text" style="margin:0; border:0; '.__wcs('wppa-box-text').'" ><tbody>';
					$wppa['out'] .= wppa_nltab('+').'<tr valign="top" style="border: 0 none; " >';
						$wppa['out'] .= wppa_nltab('+').'<td class="wppa-box-text wppa-td" style="'.__wcs('wppa-box-text').__wcs('wppa-td').'" >';
							$wppa['out'] .= wppa_nltab('+').__a('Apply watermark file:');
						$wppa['out'] .= wppa_nltab('-').'</td>';
						if ( $widget_upload ) $wppa['out'] .= '</tr><tr>';
						$wppa['out'] .= wppa_nltab(   ).'<td class="wppa-box-text wppa-td" style="width: '.$width.';'.__wcs('wppa-box-text').__wcs('wppa-td').'" >';
							$wppa['out'] .= wppa_nltab('+').'<select style="margin:0; padding:0; text-align:left; width:auto; " name="wppa-watermark-file" id="wppa-watermark-file">'.wppa_watermark_file_select().'</select>';
						$wppa['out'] .= wppa_nltab('-').'</td>';
					$wppa['out'] .= wppa_nltab('-').'</tr>';
					$wppa['out'] .= wppa_nltab(   ).'<tr valign="top" style="border: 0 none; " >';
						$wppa['out'] .= wppa_nltab('+').'<td class="wppa-box-text wppa-td" style="width: '.$width.';'.__wcs('wppa-box-text').__wcs('wppa-td').'" >';
							$wppa['out'] .= wppa_nltab('+').__a('Position:');
						$wppa['out'] .= wppa_nltab('-').'</td>';
						if ( $widget_upload ) $wppa['out'] .= '</tr><tr>';
						$wppa['out'] .= wppa_nltab(   ).'<td class="wppa-box-text wppa-td" style="width: '.$width.';'.__wcs('wppa-box-text').__wcs('wppa-td').'" >';
							$wppa['out'] .= wppa_nltab('+').'<select style="margin:0; padding:0; text-align:left; width:auto; " name="wppa-watermark-pos" id="wppa-watermark-pos">'.wppa_watermark_pos_select().'</select>';
						$wppa['out'] .= wppa_nltab('-').'</td>';
					$wppa['out'] .= wppa_nltab('-').'</tr>';
				$wppa['out'] .= wppa_nltab('-').'</table>';
			}	
			
// Name
$wppa['out'] .= '
		<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.
			__a('Enter photo name.').'&nbsp;<span style="font-size:10px;" >'.__a('If you leave this blank, the original filename will be used as photo name.').'</span>'.'
		</div>
		<input type="text" class="wppa-box-text wppa-file-'.$wppa['master_occur'].'" style="padding:0; width:'.($width-6).'px; '.__wcs('wppa-box-text').'" name="wppa-user-name" />';

		// Description
		$desc = $wppa_opt['wppa_apply_newphoto_desc_user'] ? stripslashes($wppa_opt['wppa_newphoto_description']) : '';
$wppa['out'] .= '
		<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.
			__a('Enter/modify photo description').'
		</div>
		<textarea class="wppa-user-textarea wppa-box-text wppa-file-'.$wppa['master_occur'].'" style="height:120px; width:'.($width-6).'px; '.__wcs('wppa-box-text').'" name="wppa-user-desc" >'.$desc.'</textarea>
	</form>
</div>';
}

function wppa_user_upload() {
global $wpdb;
global $wppa;
global $wppa_opt;

	wppa_dbg_msg('Usr_upl entered', 'red');
	
	if ($wppa['user_uploaded']) return;	// Already done
	$wppa['user_uploaded'] = true;
	if ( !$wppa_opt['wppa_user_upload_on'] ) return;	// Feature not enabled
	if ( $wppa_opt['wppa_user_upload_login'] ) {
		if ( !is_user_logged_in() ) return;					// Must login
//		if ( !current_user_can('wppa_upload') ) return;		// No upload rights
	}	
	if (wppa_get_post('wppa-upload-album')) {
		$nonce = wppa_get_post('nonce');
		$ok = wp_verify_nonce($nonce, 'wppa-check');
		if ( !$ok ) die(__a('<b>ERROR: Illegal attempt to upload a file.</b>'));
		
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
	else {
		wppa_dbg_msg('Unknown album', 'red');
	}
}

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
	$id = wppa_nextkey(WPPA_PHOTOS);
	if ( wppa_get_post('user-name') ) {
		$name = wppa_get_post('user-name');
	}
	else {
		$name = $file['name'];
	}
	$name = htmlspecialchars($name);
	if ( wppa_switch('wppa_strip_file_ext') ) {
		$name = preg_replace('/\.[^.]*$/', '', $name);
	}
	$porder = '0';
	$desc = balanceTags(wppa_get_post('user-desc'), true);
	$mrat = '0';
	$linkurl = '';
	$linktitle = '';
	$linktarget = '_self';
	$owner = wppa_get_user();
	$status = ( $wppa_opt['wppa_upload_moderate'] && !current_user_can('wppa_admin') ) ? 'pending' : 'publish';
	$filename = $file['name'];
	$query = $wpdb->prepare('INSERT INTO `' . WPPA_PHOTOS . '` (`id`, `album`, `ext`, `name`, `p_order`, `description`, `mean_rating`, `linkurl`, `linktitle`, `linktarget`, `timestamp`, `owner`, `status`, `tags`, `alt`, `filename`, `modified`, `location`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, \'0\', \'\')', $id, $alb, $ext, $name, $porder, $desc, $mrat, $linkurl, $linktitle, $linktarget, time(), $owner, $status, $album['default_tags'], '', $filename);
	wppa_flush_treecounts($alb);
	wppa_dbg_q('Q58');
	
	if ($wpdb->query($query) === false) {
		wppa_err_alert(__a('Could not insert photo into db.'));
		return false;
	}
	else {
		wppa_save_source($file['tmp_name'], $filename, $alb);
		wppa_update_album_timestamp($alb);
		wppa_set_last_album($alb);
	}
	if ( wppa_make_the_photo_files($file['tmp_name'], $id, $ext) ) {
		wppa_index_add('photo', $id);
		if ( $wppa_opt['wppa_upload_notify'] ) {
			$to = get_bloginfo('admin_email');
			$subj = sprintf(__a('New photo uploaded: %s'), $name);
			$cont['0'] = sprintf(__a('User %s uploaded photo %s into album %s'), $owner, $id, wppa_get_album_name($alb));
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
			case '�':	// acute
				$result .= "'";
				break;
			case '�':	// double grave
			case '�':	// double acute
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

function wppa_get_album_title_linktype($alb) {
global $wpdb;

	if ( is_numeric($alb) ) $result = $wpdb->get_var( $wpdb->prepare( "SELECT cover_linktype FROM ".WPPA_ALBUMS." WHERE id = %s LIMIT 1", $alb ) );
	else $result = '';
	wppa_dbg_q('Q59');
//echo $result;
	return $result;
}

// Find the search results
function wppa_have_photos($xwidth = '0') {
global $wppa;

	if ( !is_search() ) return false;
	$width = $xwidth ? $xwidth : wppa_get_container_width();
	
	$wppa['searchresults'] = wppa_albums('', '', $width);

	return $wppa['any'];
}
// Display the searchresults
function wppa_the_photos() {
global $wppa;

	if ( $wppa['any'] ) echo $wppa['searchresults'];
}




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

function wppa_get_lbtitle($type, $id) {
global $wppa_opt;
global $thumb;

	if ( ! is_numeric($id) || $id < '1' ) wppa_dbg_msg('Invalid arg wppa_get_lbtitle('.$id.')', 'red');

	wppa_cache_thumb($id);

	$do_name = $wppa_opt['wppa_ovl_'.$type.'_name'];
	$do_desc = $wppa_opt['wppa_ovl_'.$type.'_desc'];

	$result = '';
	if ( $do_name ) $result .= wppa_get_photo_name($thumb['id']); 
	if ( $do_name && $do_desc ) $result .= '<br />';
	if ( $do_desc ) $result .= wppa_get_photo_desc($thumb['id']);

	$result = esc_attr($result);
	return $result;
}

function wppa_zoom_in() {
global $wppa_opt;
	if ( $wppa_opt['wppa_show_zoomin'] ) return __a('Zoom in');
	else return ' ';
}

function wppa_album_desc($key) {
global $wppa;
global $wppa_opt;
global $wpdb;

	$result = '';
	if ( $wppa_opt['wppa_albdesc_on_thumbarea'] == $key && $wppa['current_album'] ) {
		$desc = wppa_get_album_desc($wppa['current_album']);
		if ( $key == 'top' ) {
			$result .= '<div id="wppa-albdesc-'.$wppa['master_occur'].'" class="wppa-box-text wppa-black" style="padding-right:6px;'.__wcs('wppa-box-text').__wcs('wppa-black').'" >'.$desc.'</div><div style="clear:both" ></div>';
		}
		if ( $key == 'bottom' ) {
			$result .= '<div class="wppa-box-text wppa-black" style="clear:both; padding-right:6px;'.__wcs('wppa-box-text').__wcs('wppa-black').'" >'.$desc.'</div>';
		}
	}
	$wppa['out'] .= $result;
}

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

function wppa_get_share_html() {
global $wppa;
global $wppa_opt;
global $thumb;

	$do_it = false;
	if ( ! $wppa['is_slideonly'] ) {
		if ( $wppa_opt['wppa_share_on'] && ! $wppa['in_widget'] ) $do_it = true;
		if ( $wppa_opt['wppa_share_on_widget'] && $wppa['in_widget'] ) $do_it = true;
	}
	if ( ! $do_it ) return '';

	// The share url
	$share_url = wppa_convert_to_pretty(str_replace('&amp;', '&', wppa_get_image_page_url_by_id($thumb['id'], $wppa_opt['wppa_share_single_image'])));
	
	// The share title
	$photo_name = __(stripslashes($thumb['name']));
	
	// The share description
	$photo_desc = strip_shortcodes(wppa_strip_tags(wppa_html(__(stripslashes($thumb['description']))), 'all'));
	
	// The default description
	$see_on_site = sprintf(__a('See this image on %s'), str_replace('&amp;', __a('and'), get_bloginfo('name')));
	
	// The share thumbnail
	$share_img = wppa_get_thumb_url($thumb['id']);

	// The icon size
	$s = $wppa['in_widget'] ? '16' : $wppa_opt['wppa_share_size'];
	
	// qr code
	if ( $wppa_opt['wppa_share_qr'] ) {	
		$src = 'http://api.qrserver.com/v1/create-qr-code/?data='.urlencode($share_url).'&size=80x80&color='.trim($wppa_opt['wppa_qr_color'], '#').'&bgcolor='.trim($wppa_opt['wppa_qr_bgcolor'], '#');
		$qr = '<div style="float:left; padding:2px;" ><img src="'.$src.'" title="'.esc_attr($share_url).'"/></div>';
	}
	else $qr = '';
	
	// facebook share button
	if ( $wppa_opt['wppa_share_facebook'] ) { 
		$summary = $see_on_site . ': ' . $photo_desc;
		$fb = 	'<div style="float:left; padding:2px;" >';
		$fb .= 		'<a title="'.sprintf(__a('Share %s on Facebook', 'wppa'), esc_attr($photo_name)).'" ';
		$fb .= 			'href="http://www.facebook.com/sharer.php?s=100&p[url]='.urlencode($share_url);
		$fb .= 				'&p[images][0]='.$share_img.'&p[title]='.urlencode($photo_name).'&p[summary]='.urlencode($summary).'" ';
		$fb .= 			'target="_blank" >';
		$fb .= 				'<img src="'.wppa_get_imgdir().'facebook.png" style="height:'.$s.'px;" alt="Share on Facebook" />';
		$fb .= 		'</a>';
		$fb .= 	'</div>';
	}
	else $fb = '';
	
	// twitter share button
	if ( $wppa_opt['wppa_share_twitter'] ) {	
		$tweet = urlencode($see_on_site) . ': ';
		$tweet_len = strlen($tweet);
		
		$tweet .= urlencode($share_url);
		$url_len = strpos($share_url, '/', 8) + 1;	// find first '/' after 'http(s)://' rest doesnt count for twitter chars
		$tweet_len += ($url_len > 1) ? $url_len : strlen($share_url);
		
		$rest_len = 140 - $tweet_len;
		
		if ( $wppa_opt['wppa_show_full_name'] ) {
			if ( $rest_len > strlen($photo_name) ) {
				$tweet .= ' ' . urlencode($photo_name);
				$rest_len -= strlen($photo_name);
				$rest_len -= '2';
			}
			else {
				$tweet .= ' '. urlencode(substr($photo_name, 0, $rest_len)) . '...';
				$rest_len -= strlen(substr($photo_name, 0, $rest_len));
				$rest_len -= '5';
			}
		}
		
		if ( $rest_len > strlen($photo_desc) ) {
			$tweet .= ': ' . urlencode($photo_desc);
		}
		elseif ( $rest_len > 8 ) {
			$tweet .= ': '. urlencode(substr($photo_desc, 0, $rest_len)) . '...';
		}
		
		$tw = 	'<div style="float:left; padding:2px;" >';
		$tw .= 		'<a title="'.sprintf(__a('Tweet %s on Twitter', 'wppa'), esc_attr($photo_name)).'" ';
		$tw .= 			'href="https://twitter.com/intent/tweet?text='.$tweet.'" ';
		$tw .= 			'target="_blank" >';
		$tw .=				'<img src="'.wppa_get_imgdir().'twitter.png" style="height:'.$s.'px;" alt="Share on Twitter" />';
		$tw .= 		'</a>';
		$tw .=	'</div>';
	}
	else $tw = '';
	
	// hyves
	if ( $wppa_opt['wppa_share_hyves'] ) {
		$hv = 	'<div style="float:left; padding:2px;" >';
		$hv .= 		'<a title="'.sprintf(__a('Tip %s on Hyves', 'wppa'), esc_attr($photo_name)).'" ';
		$hv .= 			'href="http://www.hyves-share.nl/button/tip/?tipcategoryid=12&rating=5&title='.urlencode($photo_name);
		$hv .= 				'&body='.str_replace('+', ' ', urlencode($see_on_site)).': '.urlencode($share_url).' '.str_replace('+', ' ', urlencode($photo_desc)).'" ';
		$hv .= 			'target="_blank" >';
		$hv .= 				'<img src="'.wppa_get_imgdir().'hyves.png" style="height:'.$s.'px;" alt="Share on Hyves" />';
		$hv .= 		'</a>';
		$hv .= 	'</div>';		
	}
	else $hv = '';
	
	// Google
	if ( $wppa_opt['wppa_share_google'] ) {
		$go = 	'<div style="float:left; padding:2px;" >';
		$go .= 		'<a title="'.sprintf(__a('Share %s on Google+', 'wppa'), esc_attr($photo_name)).'" ';
		$go .= 			'href="https://plus.google.com/share?url='.urlencode($share_url).'" ';
		$go .= 			'onclick="javascript:window.open(this.href, \"\", \"menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\");return false;" ';
		$go .= 			'target="_blank" >';
		$go .= 			'<img src="'.wppa_get_imgdir().'google.png" style="height:'.$s.'px;" alt="Share on Google+"/>';
		$go .= 		'</a>';
		$go .= 	'</div>';
	}
	else $go = '';
	
	// Pinterest
	$desc = urlencode($see_on_site).': '.urlencode($photo_desc).'" ';
	if ( strlen($desc) > 500) $desc = substr($desc, 0, 495).'...';
	if ( $wppa_opt['wppa_share_pinterest'] ) {
		$pi = 	'<div style="float:left; padding:2px;" >';
		$pi .= 		'<a title="'.sprintf(__a('Share %s on Pinterest', 'wppa'), esc_attr($photo_name)).'" ';
		$pi .= 			'href="http://pinterest.com/pin/create/button/?url='.urlencode($share_url);
		$pi .=			'&media='.urlencode(str_replace('/thumbs/', '/', $share_img));						// Fullsize image
		$pi .=			'&description='.$desc;
		$pi .=			'target="_blank" >';//'class="pin-it-button" count-layout="horizontal" >';
		$pi .=			'<img src="'.wppa_get_imgdir().'pinterest.png" style="height:'.$s.'px;" alt="Pin it!" />';	//border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" />';
		$pi .=		'</a>';
		$pi .=	'</div>';

	}
	else $pi = '';
	
	return $qr.$fb.$tw.$hv.$go.$pi.//.'<small>This box is under construction and may not yet properly work for all icons shown</small>'.
	'<div style="clear:both"></div>';

}

function wppa_get_tagcloud_html() {
global $wppa_opt;
global $wppa;

	$result 	= '';
	if ( $wppa_opt['wppa_tagcloud_linkpage'] ) {
		$hr = wppa_get_permalink($wppa_opt['wppa_tagcloud_linkpage']);
		if ( $wppa_opt['wppa_tagcloud_linktype'] == 'album' ) {
			$hr .= 'wppa-album=0&wppa-cover=0&wppa-occur=1';
		}
		if ( $wppa_opt['wppa_tagcloud_linktype'] == 'slide' ) {
			$hr .= 'wppa-album=0&wppa-cover=0&wppa-occur=1&slide';
		}
	}
	else {
		return __a('Please select a tagcloud landing page in Table VI-C3b');
	}
	$tags = wppa_get_taglist();
	if ( $tags ) {
		$top = '0';
		foreach ( $tags as $tag ) {	// Find largest percentage
			if ( $tag['fraction'] > $top ) $top = $tag['fraction'];
		}
		if ( $top ) $factor = '16' / $top;
		else $factor = '1.0';
		foreach ( $tags as $tag ) {
			$href 		= $hr . '&wppa-tag=' . $tag['tag'];
			$title 		= sprintf('%d photos - %s%%', $tag['count'], $tag['fraction'] * '100');
			$name 		= $tag['tag'];
			$size 		= floor('8' + $tag['fraction'] * $factor);
			$result    .= '<a href="'.$href.'" title="'.$title.'" style="font-size:'.$size.'px;" >'.$name.'</a> ';
		}
	}
	return $result;
}

function wppa_get_multitag_html($nperline = '2') {
global $wppa_opt;
global $wppa;

	$result 	= '';
	if ( $wppa_opt['wppa_multitag_linkpage'] ) {
		$hr = wppa_get_permalink($wppa_opt['wppa_multitag_linkpage']);
		if ( $wppa_opt['wppa_multitag_linktype'] == 'album' ) {
			$hr .= 'wppa-album=0&wppa-cover=0&wppa-occur=1';
		}
		if ( $wppa_opt['wppa_multitag_linktype'] == 'slide' ) {
			$hr .= 'wppa-album=0&wppa-cover=0&wppa-occur=1&slide';
		}
	}
	else {
		return __a('Please select a multitag landing page in Table VI-C4b');
	}
	$tags = wppa_get_taglist();
	$result .= '
	<script type="text/javascript">
	function wppaProcessMultiTagRequest() {
	var any = false;
	var url="'.$hr.'&wppa-tag=";
	var andor = "and";
		if ( document.getElementById("andoror").checked ) andor = "or";
	var sep;
	if ( andor == "and" ) sep = ","; else sep = ";";
	';
	if ( $tags ) foreach ( $tags as $tag ) {
		$result .= '
		if ( document.getElementById("wppa'.$tag['tag'].'").checked ) {
			url+="'.$tag['tag'].'"+sep;
			any = true;
		}';
	}	
	$result .= '
	if ( any ) document.location = url;
	else alert ("'.__a('Please check the tag(s) that the photos must have').'");
	}</script>
	';
	
	$qtag = wppa_get_get('tag');
	$andor = 'and'; // default
	if ( strpos($qtag, ',') ) {
		$querystringtags = explode(',',wppa_get_get('tag'));
	}
	elseif ( strpos($qtag, ';') ) {
		$querystringtags = explode(';',wppa_get_get('tag'));
		$andor = 'or';
	}
	else $querystringtags = wppa_get_get('tag');

	if ( $tags ) {
	
		$result .= '<table><tr>';
	
		$result .= '<td><input class="radio" name="andor" value="and" id="andorand" type="radio" ';
		if ( $andor == 'and' ) $result .= 'checked="checked" ';
		$result .= 'size="30" />'.__a('And', 'wppa_theme').'</td>';
		$result .= '<td><input class="radio" name="andor" value="or" id="andoror" type="radio" ';
		if ( $andor == 'or' ) $result .= 'checked="checked" ';
		$result .= 'size="30" />'.__a('Or', 'wppa_theme').'</td>';
		$result .= '</tr>';
		
		$count = '0';
		$checked = '';
	//	$result .= '<table>';
		
		
		$tropen = false;
		foreach ( $tags as $tag ) {
			if ( $count % $nperline == '0' ) {
				$result .= '<tr>';
				$tropen = true;
			}
			if ( is_array($querystringtags) ) {
				$checked = in_array($tag['tag'], $querystringtags) ? 'checked="checked" ' : '';
			}
			$result .= '<td style="padding-right:4px;" ><input type="checkbox" id="wppa'.$tag['tag'].'" '.$checked.'/>'.$tag['tag'].'</td>';
			$count++;
			if ( $count % $nperline == '0' ) {
				$result .= '</tr>';
				$tropen = false;
			}
		}
		if ( $tropen ) $result .= '</tr>';
		$result .= '</table>';
		$result .= '<input type="button" onclick="wppaProcessMultiTagRequest()" value="'.__a('Find!').'" />';
	}
	return $result;
}

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
/*
function wppa_approve_photo_button($id) {
	
	$result = '
	<br class="wppa-approve-'.$id.'" />
	<a class="wppa-approve-'.$id.'" style="font-weight:bold; color:green; cursor:pointer;" onclick="wppaAjaxApprovePhoto(\''.$id.'\')">'.__a('Approve').'</a>';
	return $result;
}
function wppa_moderate_photo_button($id) {
	
	$result = '
	<a class="wppa-approve-'.$id.'" style="font-weight:bold; color:blue; cursor:pointer;" onclick="document.location=\''.get_admin_url().'admin.php?page=wppa_moderate_photos&amp;photo='.$id.'\'" >'.__a('Moderate').'</a>
	<br class="wppa-approve-'.$id.'"/>';
	return $result;
}
*/