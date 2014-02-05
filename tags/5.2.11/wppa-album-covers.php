<?php
/* wppa-album-covers.php
* Package: wp-photo-album-plus
*
* Functions for album covers
* Version 5.2.7
*
*/

if ( ! defined( 'ABSPATH' ) )
    die( "Can't load this file directly" );

// Main entry for an album cover
// decide wich cover type and call the types function
function wppa_album_cover() {
global $wppa_opt;
global $album;
global $wppa;

	$type = $album['cover_type'] ? $album['cover_type'] : $wppa_opt['wppa_cover_type'];
	$wppa['coverphoto_pos'] = $wppa_opt['wppa_coverphoto_pos'];

	$flag = true;
	
	switch ( $type ) {
		case 'default':
			$flag = false;
		case 'default-mcr':
			wppa_album_cover_default($flag);
			break;
		case 'imagefactory':
			$flag = false;
		case 'imagefactory-mcr':
			if ( $wppa['coverphoto_pos'] == 'left' ) $wppa['coverphoto_pos'] = 'top';
			if ( $wppa['coverphoto_pos'] == 'right' ) $wppa['coverphoto_pos'] = 'bottom';
			wppa_album_cover_imagefactory($flag);
			break;
		case 'longdesc':
			$flag = false;
		case 'longdesc-mcr':
			if ( $wppa['coverphoto_pos'] == 'top' ) $wppa['coverphoto_pos'] = 'left';
			if ( $wppa['coverphoto_pos'] == 'bottom' ) $wppa['coverphoto_pos'] = 'right';
			wppa_album_cover_longdesc($flag);
			break;
		default:
			wppa_dbg_msg('Unimplemented covertype: '.$wppa_opt['wppa_cover_type']);
	}
}

// The default cover type
function wppa_album_cover_default($multicolumnresponsive = false) {
global $album;
global $wppa;
global $wppa_opt;
global $wppa_alt;
global $cover_count_key;
global $wpdb;

	// Multi column responsive?
	if ( $multicolumnresponsive ) $mcr = 'mcr-'; else $mcr = '';
	
	// Find album details
	$albumid 	= $album['id'];
	$coverphoto = wppa_get_coverphoto_id($albumid);
	$image 		= $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $coverphoto), ARRAY_A );
	$photocount = wppa_get_photo_count($albumid);
	$albumcount = wppa_get_album_count($albumid);
	$mincount 	= wppa_get_mincount();
	
	// Init links
	$title 				= '';
	$linkpage 			= '';
	$href_title 		= '';
	$href_slideshow 	= '';
	$href_content 		= '';
	$onclick_title 		= '';
	$onclick_slideshow 	= '';
	$onclick_content 	= '';

	// See if there is substantial content to the album
	$has_content = ($albumcount > '0') || ($photocount > $mincount);
	
	// What is the albums title linktype
	$linktype = $album['cover_linktype'];
	if ( !$linktype ) $linktype = 'content'; // Default 
	
	// What is the albums title linkpage
	$linkpage = $album['cover_linkpage'];
	if ( $linkpage == '-1' ) $linktype = 'none'; // for backward compatibility
	
	// Find the cover title href, onclick and title
	$title_attr 	= wppa_get_album_title_attr_a($linktype, $linkpage, $has_content, $coverphoto, $photocount);
	$href_title 	= $title_attr['href'];
	$onclick_title 	= $title_attr['onclick'];
	$title 			= $title_attr['title'];
	
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
	else {
		$photolink = false;
	}
	
	// Find the coverphoto details
	if ( $coverphoto ) {
		$path 		= wppa_get_thumb_path( $coverphoto );
		$imgattr_a 	= wppa_get_imgstyle_a( $path, $wppa_opt['wppa_smallsize'], '', 'cover' );
		$src 		= wppa_get_thumb_url( $coverphoto, '', $imgattr_a['width'], $imgattr_a['height'] );	
	}
	else {
		$path 		= '';
		$imgattr_a 	= false;
		$src 		= '';
	}
	
	// Feed?
	if ( is_feed() ) {
		$events  	= '';
	}
	else {
		$events 	= wppa_get_imgevents('cover');
	}
	
	$photo_pos = $wppa['coverphoto_pos'];
	$class_asym = ( $photo_pos == 'left' || $photo_pos == 'right' ) ? 'wppa-asym-text-frame-'.$mcr.$wppa['master_occur'] : '';
	
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
	$wppa['out'] .= wppa_nltab('+').'<div id="album-'.$album['id'].'-'.$wppa['master_occur'].'" class="wppa-album-cover-standard album wppa-box wppa-cover-box wppa-cover-box-'.$mcr.$wppa['master_occur'].' wppa-'.$wppa_alt.'" style="'.$style.'" >';

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
			wppa_album_cover_view_link($has_content, $photocount, $albumcount, $mincount, $href_content, $target, $onclick_content);

		$wppa['out'] .= wppa_nltab('-').'</div>';
		
		if ( $photo_pos == 'right' || $photo_pos == 'bottom' ) {
			// The Cover photo last
			wppa_the_coverphoto($image, $src, $photo_pos, $photolink, $title, $imgattr_a, $events);
		}
		
		$wppa['out'] .= wppa_nltab().'<div style="clear:both;"></div>';		
		
		wppa_user_create_html($album['id'], wppa_get_cover_width('cover'), 'cover', $multicolumnresponsive);
		wppa_user_upload_html($album['id'], wppa_get_cover_width('cover'), 'cover', $multicolumnresponsive);

/**
		if ( $album['cats'] ) {
			if ( strpos( $album['cats'] ,',' ) ) {
				$wppa['out'] .= '<span style="float:right;">'.__a('Categories:').' <b>'.$album['cats'].'</b></span>';
			}
			else {
				$wppa['out'] .= '<span style="float:right;">'.__a('Category:').' <b>'.$album['cats'].'</b></span>';
			}
		}
/**/
	$wppa['out'] .= wppa_nltab('-').'</div><!-- #album-'.$album['id'].'-'.$wppa['master_occur'].' -->';
	if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
}

// Type Image Factory
function wppa_album_cover_imagefactory($multicolumnresponsive = false) {
global $album;
global $wppa;
global $wppa_opt;
global $wppa_alt;
global $cover_count_key;
global $wpdb;

	if ( $multicolumnresponsive ) $mcr = 'mcr-'; else $mcr = '';

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
		$path 			= wppa_get_thumb_path($coverphoto);
		$paths[] 		= $path;
		$cpsize 		= count($coverphotos) == '1' ? $wppa_opt['wppa_smallsize'] : $wppa_opt['wppa_smallsize_multi'];
		$imgattr_a		= wppa_get_imgstyle_a( $path, $cpsize, '', 'cover' );
		$imgattrs_a[] 	= $imgattr_a;
		$srcs[] 		= wppa_get_thumb_url( $coverphoto, '', $imgattr_a['width'], $imgattr_a['height'] );
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
	
	// Find the cover title href, onclick and title
	$title_attr 	= wppa_get_album_title_attr_a($linktype, $linkpage, $has_content, $coverphoto, $photocount);
	$href_title 	= $title_attr['href'];
	$onclick_title 	= $title_attr['onclick'];
	$title 			= $title_attr['title'];

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
	$wppa['out'] .= wppa_nltab('+').'<div id="album-'.$album['id'].'-'.$wppa['master_occur'].'" class="wppa-album-cover-imagefactory album wppa-box wppa-cover-box wppa-cover-box-'.$mcr.$wppa['master_occur'].' wppa-'.$wppa_alt.'" style="'.$style.'" >';

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
			wppa_album_cover_view_link($has_content, $photocount, $albumcount, $mincount, $href_content, $target, $onclick_content);

		$wppa['out'] .= wppa_nltab('-').'</div>';
		
		if ( $photo_pos == 'right' || $photo_pos == 'bottom' ) {
			// The Cover photo last
			wppa_the_coverphotos($images, $srcs, $photo_pos, $photolink, $title, $imgattrs_a, $events);
		}
		
		$wppa['out'] .= wppa_nltab().'<div style="clear:both;"></div>';		
		
		wppa_user_create_html($album['id'], wppa_get_cover_width('cover'), 'cover', $multicolumnresponsive);
		wppa_user_upload_html($album['id'], wppa_get_cover_width('cover'), 'cover', $multicolumnresponsive);

	$wppa['out'] .= wppa_nltab('-').'</div><!-- #album-'.$album['id'].'-'.$wppa['master_occur'].' -->';
	if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
}

// Type Long Description
function wppa_album_cover_longdesc($multicolumnresponsive = false) {
global $album;
global $wppa;
global $wppa_opt;
global $wppa_alt;
global $cover_count_key;
global $wpdb;

	if ( $multicolumnresponsive ) $mcr = 'mcr-'; else $mcr = '';

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
	
	// Find the cover title href, onclick and title
	$title_attr 	= wppa_get_album_title_attr_a($linktype, $linkpage, $has_content, $coverphoto, $photocount);
	$href_title 	= $title_attr['href'];
	$onclick_title 	= $title_attr['onclick'];
	$title 			= $title_attr['title'];
	
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
		$path 		= wppa_get_thumb_path( $coverphoto );
		$imgattr_a 	= wppa_get_imgstyle_a( $path, $wppa_opt['wppa_smallsize'], '', 'cover' );
		$src 		= wppa_get_thumb_url( $coverphoto, '', $imgattr_a['width'], $imgattr_a['height'] );	
	}
	else {
		$path 		= '';
		$imgattr_a 	= false;
		$src 		= '';
	}
	
	// Feed?
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
	$wppa['out'] .= wppa_nltab('+').'<div id="album-'.$album['id'].'-'.$wppa['master_occur'].'" class="wppa-album-cover-longdesc album wppa-box wppa-cover-box wppa-cover-box-'.$mcr.$wppa['master_occur'].' wppa-'.$wppa_alt.'" style="'.$style.'" >';

		if ( $photo_pos == 'left' || $photo_pos == 'top') {
			// First The Cover photo
			wppa_the_coverphoto($image, $src, $photo_pos, $photolink, $title, $imgattr_a, $events);
		}
		
		// The Cover text
		$textframestyle = wppa_get_text_frame_style($photo_pos, 'cover');
		$wppa['out'] .= wppa_nltab('+').'<div id="covertext_frame_'.$album['id'].'_'.$wppa['master_occur'].'" class="wppa-text-frame-'.$wppa['master_occur'].' wppa-text-frame covertext-frame wppa-asym-text-frame-'.$mcr.$wppa['master_occur'].'" '.$textframestyle.'>';

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
			wppa_album_cover_view_link($has_content, $photocount, $albumcount, $mincount, $href_content, $target, $onclick_content);
			
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
		
		wppa_user_create_html($album['id'], wppa_get_cover_width('cover'), 'cover', $multicolumnresponsive);
		wppa_user_upload_html($album['id'], wppa_get_cover_width('cover'), 'cover', $multicolumnresponsive);

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
					$siz = getimagesize( wppa_get_photo_path( $thumb['id'] ) );
					$link = wppa_get_photo_url( $thumb['id'], '', $siz['0'], $siz['1'] );
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
	
		$photoframestyle = 'style="text-align:center; "';

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
					$title = wppa_get_lbtitle('cover', $thumb['id']);
					$siz = getimagesize( wppa_get_photo_path( $thumb['id'] ) );
					$link = wppa_get_photo_url( $thumb['id'], '', $siz['0'], $siz['1'] );
					$wppa['out'] .= "\n\t".'<a href="'.$link.'" rel="'.$wppa_opt['wppa_lightbox_name'].'[alw-'.$wppa['master_occur'].'-'.$album['id'].']" title="'.$title.'" >';
					if ( $thumb['id'] == $image['id'] ) {		// the cover image
						$wppa['out'] .= "\n\t\t".'<img class="image wppa-img" id="i-'.$image['id'].'-'.$wppa['master_occur'].'" title="'.wppa_zoom_in().'" src="'.$src.'" width="'.$imgwidth.'" height="'.$imgheight.'" style="'.__wcs('wppa-img').$imgattr.$imgattr_a['cursor'].'" '.$events.' alt="'.$title.'">';
					}
					$wppa['out'] .= "\n\t".'</a>';
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
	
// get id of coverphoto. does all testing
function wppa_get_coverphoto_id($xalb = '') {
	$result = wppa_get_coverphoto_ids($xalb, '1');

	if ( empty($result) ) return false;
	return $result['0'];
}

// Get the cover photo id(s)
// The id in the album may be 0: random, -1: featured random; -2: last upload; > 0 one assigned specific.
// If one assigned but no longer exists: treat as random
function wppa_get_coverphoto_ids($xalb = '', $count) {
global $wpdb;
global $album;
global $wppa;
	
	// Find album
	if ( $xalb == '' ) {						// default album
		if ( isset($album['id']) ) $alb = $album['id'];
	}
	else {										// supplied album
		$alb = $xalb;
	}
	if ( ! $alb ) return false;					// no album, no coverphoto
	
	// Find cover photo id
	$id = $wpdb->get_var( $wpdb->prepare( "SELECT `main_photo` FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $alb ) );
	switch ( $id ) {
		case '-2':		// Last upload
			if ( current_user_can('wppa_moderate') ) {
				$temp = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `album` = %s ORDER BY `timestamp` DESC LIMIT %d", $alb, $count ), ARRAY_A );
			}
			else {
				$temp = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `album` = %s AND ( `status` <> 'pending' OR `owner` = %s ) ORDER BY `timestamp` DESC LIMIT %d", $alb, wppa_get_user(), $count ), ARRAY_A );
			}
			break;
		case '-1':		// Random featured
			$temp = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `album` = %s AND `status` = 'featured' ORDER BY RAND(".$wppa['randseed'].") LIMIT %d", $alb, $count), ARRAY_A );
			break;
		case '0':		// Random
			if ( current_user_can('wppa_moderate') ) {
				$temp = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `album` = %s ORDER BY RAND(".$wppa['randseed'].") LIMIT %d", $alb, $count), ARRAY_A );
			}
			else {
				$temp = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `album` = %s AND ( `status` <> 'pending' OR `owner` = %s ) ORDER BY RAND(".$wppa['randseed'].") LIMIT %d", $alb, wppa_get_user(), $count), ARRAY_A );
			}
			break;
		default:		// One assigned
			// Check if id still exists and is in album alb
			$ph_alb = $wpdb->get_var( $wpdb->prepare( "SELECT `album` FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $id ) );
			if ( $ph_alb && $ph_alb == $alb ) { 
				$temp['0']['id'] = $id;
			}
			else {	// Treat as random
				if ( current_user_can('wppa_moderate') ) {
					$temp = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `album` = %s ORDER BY RAND(".$wppa['randseed'].") LIMIT %d", $alb, $count), ARRAY_A );
				}
				else {
					$temp = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `album` = %s AND ( `status` <> 'pending' OR `owner` = %s ) ORDER BY RAND(".$wppa['randseed'].") LIMIT %d", $alb, wppa_get_user(), $count), ARRAY_A );
				}
			}
			break;
	}
	$ids = array();
	if ( is_array($temp) ) foreach ( $temp as $item ) {
		$ids[] = $item['id'];
	}
	return $ids;
}

// Find the cover Title's href, onclick and title
function wppa_get_album_title_attr_a($linktype, $linkpage, $has_content, $coverphoto, $photocount) {
global $wppa;
global $album;
global $wppa_opt;

	// Init
	$href_title 	= '';
	$onclick_title 	= '';
	$title_title 	= '';
	
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
			case 'page':
				$href_title = get_page_link($album['cover_linkpage']);
				break;
			case 'none':
				break;
			default:
		}
		$href_title = wppa_convert_to_pretty($href_title);
		$title_title = __a('Link to');
		$title_title .= ' ' . __(get_the_title($album['cover_linkpage']));
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
		$title_title = __a('View the album').' '.esc_attr(wppa_qtrans(stripslashes($album['name'])));
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
			if ($photocount == '1') $title_title = __a('View the cover photo'); 
			else $title_title = __a('View the cover photos');
		}
	}
	$title_attr['href'] 	= $href_title;
	$title_attr['onclick'] 	= $onclick_title;
	$title_attr['title'] 	= $title_title;
	
	return $title_attr;
}

// The 'View' link
function wppa_album_cover_view_link($has_content, $photocount, $albumcount, $mincount, $href_content, $target, $onclick_content) {
global $wppa;
global $wppa_opt;
global $album;

	if ( $wppa_opt['wppa_show_viewlink'] ) {
		$wppa['out'] .= wppa_nltab('+').'<div class="wppa-box-text wppa-black wppa-info wppa-viewlink">';
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
}