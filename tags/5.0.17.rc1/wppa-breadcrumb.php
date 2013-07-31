<?php
/* wppa-breadcrumb.php
* Package: wp-photo-album-plus
*
* Functions for breadcrumbs
* Version 5.0.16
*
*/

if ( ! defined( 'ABSPATH' ) )
    die( "Can't load this file directly" );

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
