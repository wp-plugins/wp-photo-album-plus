<?php
/* wppa-album-covers.php
* Package: wp-photo-album-plus
*
* Functions for album covers
* Version 5.4.18
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

// Main entry for an album cover
// decide wich cover type and call the types function
function wppa_album_cover( $id ) {
global $wppa;
global $wppa_alt;

	if ( ! $wppa_alt ) $wppa_alt = 'alt';
		
	// Find the cover type
	$cover_type = wppa_get_album_item( $id, 'cover_type' ) ? 
		wppa_get_album_item( $id, 'cover_type' ) : 		// This album has a cover type specified.
		wppa_opt( 'wppa_cover_type' );					// Use the default setting
		
	// Find the cover photo position
	$wppa['coverphoto_pos'] = wppa_opt( 'wppa_coverphoto_pos' );

	// Assume multicolumn responsive
	$is_mcr = true;
	
	// Dispatch on covertype
	switch ( $cover_type ) {
		case 'default':
			$is_mcr = false;
		case 'default-mcr':
			wppa_album_cover_default( $id, $is_mcr );
			break;
		case 'imagefactory':
			$is_mcr = false;
		case 'imagefactory-mcr':
			if ( $wppa['coverphoto_pos'] == 'left' ) $wppa['coverphoto_pos'] = 'top';
			if ( $wppa['coverphoto_pos'] == 'right' ) $wppa['coverphoto_pos'] = 'bottom';
			wppa_album_cover_imagefactory( $id, $is_mcr );
			break;
		case 'longdesc':
			$is_mcr = false;
		case 'longdesc-mcr':
			if ( $wppa['coverphoto_pos'] == 'top' ) $wppa['coverphoto_pos'] = 'left';
			if ( $wppa['coverphoto_pos'] == 'bottom' ) $wppa['coverphoto_pos'] = 'right';
			wppa_album_cover_longdesc( $id, $is_mcr );
			break;
		default:
			$err = 'Unimplemented covertype: ' . $cover_type;
			wppa_dbg_msg( $err );
			wppa_log( 'Err', $err );
	}
}

// The default cover type
function wppa_album_cover_default( $albumid, $multicolresp = false ) {
global $wppa;
global $wppa_opt;
global $wppa_alt;
global $cover_count_key;
global $wpdb;

	$album = wppa_cache_album( $albumid );
	
	// Multi column responsive?
	if ( $multicolresp ) $mcr = 'mcr-'; else $mcr = '';
	
	// Find album details
	$coverphoto = wppa_get_coverphoto_id( $albumid );
	$image 		= $wpdb->get_row( $wpdb->prepare( 
		"SELECT * FROM `" . WPPA_PHOTOS . "` WHERE `id` = %s", $coverphoto 
		), ARRAY_A );
	$photocount = wppa_get_photo_count( $albumid, true );
	$albumcount = wppa_get_album_count( $albumid, true );
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
	$has_content = ( $albumcount > '0' ) || ( $photocount > $mincount );
	
	// What is the albums title linktype
	$linktype = $album['cover_linktype'];
	if ( ! $linktype ) $linktype = 'content'; // Default 
	
	// What is the albums title linkpage
	$linkpage = $album['cover_linkpage'];
	if ( $linkpage == '-1' ) $linktype = 'none'; // for backward compatibility
	
	// Find the cover title href, onclick and title
	$title_attr 	= wppa_get_album_title_attr_a( 
		$albumid, $linktype, $linkpage, $has_content, $coverphoto, $photocount );
	$href_title 	= $title_attr['href'];
	$onclick_title 	= $title_attr['onclick'];
	$title 			= $title_attr['title'];
	
	// Find the slideshow link and onclick
	$href_slideshow = wppa_convert_to_pretty( wppa_get_slideshow_url( $albumid, $linkpage ) );
	if ( wppa_switch( 'wppa_allow_ajax' ) && ! $linkpage ) {
		$onclick_slideshow = "wppaDoAjaxRender( " . $wppa['mocc'] . ", '" . 
			wppa_get_slideshow_url_ajax( $albumid, $linkpage ) . "', '" . 
			wppa_convert_to_pretty( $href_slideshow ) . "' )";
		$href_slideshow = "#";
	}

	// Find the content 'View' link 
	$href_content = wppa_convert_to_pretty( wppa_get_album_url( $albumid, $linkpage ) );
	if ( wppa_switch( 'wppa_allow_ajax' ) && ! $linkpage ) {
		$onclick_content = "wppaDoAjaxRender( " . $wppa['mocc'] . ", '" . 
			wppa_get_album_url_ajax( $albumid, $linkpage ) . "', '" . 
			wppa_convert_to_pretty( $href_content ) . "' )";
		$href_content = "#";
	}

	// Find the coverphoto link
	if ( $coverphoto ) {
		$photolink = wppa_get_imglnk_a( 
			'coverimg', $coverphoto, $href_title, $title, $onclick_title, '', $albumid );
	}
	else {
		$photolink = false;
	}
	
	// Find the coverphoto details
	if ( $coverphoto ) {
		$path 		= wppa_get_thumb_path( $coverphoto );
		$imgattr_a 	= wppa_get_imgstyle_a( 
							$coverphoto, $path, $wppa_opt['wppa_smallsize'], '', 'cover' );
		$src 		= wppa_get_thumb_url( 
							$coverphoto, '', $imgattr_a['width'], $imgattr_a['height'] );	
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
		$events 	= wppa_get_imgevents( 'cover' );
	}
	
	$photo_pos = $wppa['coverphoto_pos'];
	$class_asym = ( $photo_pos == 'left' || $photo_pos == 'right' ) ? 
		'wppa-asym-text-frame-' . $mcr . $wppa['mocc'] : 
		'';
	
	$style =  __wcs( 'wppa-box' ) . __wcs( 'wppa-' . $wppa_alt );
	if ( is_feed() ) $style .= ' padding:7px;';
	
	$wid = wppa_get_cover_width( 'cover' );
	$style .= 'width: ' . $wid . 'px;';	
	if ( $cover_count_key == 'm' ) {
		$style .= 'margin-left: 8px;';
	}
	elseif ( $cover_count_key == 'r' ) {
		$style .= 'float:right;';
	}
	else {
		$style .= 'clear:both;';
	}
	wppa_step_covercount( 'cover' );
	
	$target = wppa_switch( 'wppa_allow_ajax' ) ? '_self' : $photolink['target'];
	
	// Open the album box
	$wppa['out'] .= wppa_nltab( '+' ) . 
		'<div id="album-' . $albumid . '-' . $wppa['mocc'] . 
		'" class="wppa-album-cover-standard album wppa-box wppa-cover-box wppa-cover-box-' . 
		$mcr . $wppa['mocc'] . ' wppa-' . $wppa_alt . '" style="' . $style . __wcs( 'wppa-cover-box' ) . '" >';

	// First The Cover photo?
	if ( $photo_pos == 'left' || $photo_pos == 'top' ) {
		wppa_the_coverphoto( 
			$albumid, $image, $src, $photo_pos, $photolink, $title, $imgattr_a, $events );
	}
		
	// Open the Cover text frame
	$textframestyle = wppa_get_text_frame_style( $photo_pos, 'cover' );
	$wppa['out'] .= wppa_nltab( '+' ) . 
		'<div id="covertext_frame_' . $albumid . '_' . $wppa['mocc'] . 
		'" class="wppa-text-frame-' . $wppa['mocc'] . ' wppa-text-frame wppa-cover-text-frame ' . 
		$class_asym . '" ' . $textframestyle . '>';

	// The Album title
	wppa_the_album_title( $albumid, $href_title, $onclick_title, $title, $target );

	// The Album description
	if ( wppa_switch( 'wppa_show_cover_text' ) ) {
		$textheight = $wppa_opt['wppa_text_frame_height'] > '0' ?
			'min-height:' . $wppa_opt['wppa_text_frame_height'] . 'px; ' :
			'';
		$wppa['out'] .= wppa_nltab() . 
			'<p class="wppa-box-text wppa-black wppa-box-text-desc" style="' . $textheight . 
			__wcs( 'wppa-box-text' ) . __wcs( 'wppa-black' ) . '">' . 
			wppa_get_album_desc( $albumid ) . '</p>';
	}
			
	// The 'Slideshow'/'Browse' link
	wppa_the_slideshow_browse_link( $photocount, $href_slideshow, $onclick_slideshow, $target );

	// The 'View' link
	wppa_album_cover_view_link( 
		$albumid, $has_content, $photocount, $albumcount, $mincount, 
		$href_content, $target, $onclick_content );

	// Close the Cover text frame
	$wppa['out'] .= wppa_nltab( '-' ) . '</div><!-- covertext_frame -->';
		
	// The Cover photo last?
	if ( $photo_pos == 'right' || $photo_pos == 'bottom' ) {
		wppa_the_coverphoto( 
			$albumid, $image, $src, $photo_pos, $photolink, $title, $imgattr_a, $events );
	}
		
	// The sublinks
	wppa_albumcover_sublinks( $albumid, wppa_get_cover_width( 'cover' ), $multicolresp );

	// Prepare for closing
	$wppa['out'] .= wppa_nltab() . '<div style="clear:both;"></div>';		
		
	// Close the album box
	$wppa['out'] .= wppa_nltab( '-' ) . 
		'</div><!-- #album-' . $albumid . '-' . $wppa['mocc'] . ' -->';
	if ( $wppa_alt == 'even' ) $wppa_alt = 'alt'; else $wppa_alt = 'even';
}

// Type Image Factory
function wppa_album_cover_imagefactory( $albumid, $multicolresp = false ) {
global $wppa;
global $wppa_opt;
global $wppa_alt;
global $cover_count_key;
global $wpdb;

	$album = wppa_cache_album( $albumid );

	if ( $multicolresp ) $mcr = 'mcr-'; else $mcr = '';

	$photo_pos 		= $wppa['coverphoto_pos'];
	if ( $photo_pos == 'left' ) $photo_pos 	= 'top';
	if ( $photo_pos == 'right' ) $photo_pos = 'bottom';
	$cpcount 		=  $album['main_photo'] > '0' ? '1' : $wppa_opt['wppa_imgfact_count'];
	$coverphotos 	= wppa_get_coverphoto_ids( $albumid, $cpcount );
	
	$images 	= array();
	$srcs 		= array();
	$paths 		= array();
	$imgattrs_a = array();
	$photolinks = array();

	if ( is_feed() ) {
		$events = '';
	}
	else {
		$events = wppa_get_imgevents( 'cover' );
	}

	if ( ! empty( $images ) ) $image = $images['0'];
	else $image = false;
	if ( ! empty( $coverphotos ) ) $coverphoto = $coverphotos['0'];
	else $coverphoto = false;
	
	$photocount = wppa_get_photo_count( $albumid, true );
	$albumcount = wppa_get_album_count( $albumid, true );
	$mincount 	= wppa_get_mincount();
	$title 		= '';
	$linkpage 	= '';
	
	$href_title 		= '';
	$href_slideshow 	= '';
	$href_content 		= '';
	$onclick_title 		= '';
	$onclick_slideshow 	= '';
	$onclick_content 	= '';

	// See if there is substantial content to the album
	$has_content = ( $albumcount > '0' ) || ( $photocount > $mincount );
	
	// What is the albums title linktype
	$linktype = $album['cover_linktype'];
	if ( ! $linktype ) $linktype = 'content'; // Default 
	
	// What is the albums title linkpage
	$linkpage = $album['cover_linkpage'];
	if ( $linkpage == '-1' ) $linktype = 'none'; // for backward compatibility
	
	// Find the cover title href, onclick and title
	$title_attr 	= wppa_get_album_title_attr_a( 
						$albumid, $linktype, $linkpage, $has_content, $coverphoto, $photocount );
	$href_title 	= $title_attr['href'];
	$onclick_title 	= $title_attr['onclick'];
	$title 			= $title_attr['title'];

	// Find the coverphotos details
	foreach ( $coverphotos as $coverphoto ) {
		$images[] 		= $wpdb->get_row( $wpdb->prepare( 
							"SELECT * FROM `" . WPPA_PHOTOS . "` WHERE `id` = %s", $coverphoto 
							), ARRAY_A );
		$path 			= wppa_get_thumb_path( 	$coverphoto	 );
		$paths[] 		= $path;
		$cpsize 		= count( $coverphotos ) == '1' ?
							$wppa_opt['wppa_smallsize'] :
							$wppa_opt['wppa_smallsize_multi'];
		$imgattr_a		= wppa_get_imgstyle_a( $coverphoto, $path, $cpsize, '', 'cover' );
		$imgattrs_a[] 	= $imgattr_a;
		$srcs[] 		= wppa_get_thumb_url( 
							$coverphoto, '', $imgattr_a['width'], $imgattr_a['height'] );
		$photolinks[] 	= wppa_get_imglnk_a( 
							'coverimg', $coverphoto, $href_title, $title, $onclick_title, '', $albumid );
	}	
	
	// Find the slideshow link and onclick
	$href_slideshow = wppa_convert_to_pretty( wppa_get_slideshow_url( $albumid, $linkpage ) );
	if ( wppa_switch( 'wppa_allow_ajax' ) && ! $linkpage ) {
		$onclick_slideshow = "wppaDoAjaxRender( " . $wppa['mocc'] . ", '" . 
			wppa_get_slideshow_url_ajax( $albumid, $linkpage ) . "', '" . 
			wppa_convert_to_pretty( $href_slideshow ) . "' )";
		$href_slideshow = "#";
	}

	// Find the content 'View' link 
	$href_content = wppa_convert_to_pretty( wppa_get_album_url( $albumid, $linkpage ) );
	if ( wppa_switch( 'wppa_allow_ajax' ) && ! $linkpage ) {
		$onclick_content = "wppaDoAjaxRender( " . $wppa['mocc'] . ", '" . 
			wppa_get_album_url_ajax( $albumid, $linkpage ) . "', '" . 
			wppa_convert_to_pretty( $href_content ) . "' )";
		$href_content = "#";
	}

	$style =  __wcs( 'wppa-box' ) . __wcs( 'wppa-' . $wppa_alt );
	if ( is_feed() ) $style .= ' padding:7px;';
	
	$wid = wppa_get_cover_width( 'cover' );
	$style .= 'width: ' . $wid . 'px;';	
	if ( $cover_count_key == 'm' ) {
		$style .= 'margin-left: 8px;';
	}
	elseif ( $cover_count_key == 'r' ) {
		$style .= 'float:right;';
	}
	else {
		$style .= 'clear:both;';
	}
	wppa_step_covercount( 'cover' );

	$pl = isset( $photolinks['0']['target'] ) ? $photolinks['0']['target'] : '_self';
	$target = wppa_switch( 'wppa_allow_ajax' ) ? '_self' : $pl;
	
	// Open the album box
	$wppa['out'] .= wppa_nltab( '+' ) . 
		'<div id="album-' . $albumid . '-' . $wppa['mocc'] . 
		'" class="wppa-album-cover-imagefactory album wppa-box wppa-cover-box wppa-cover-box-' . 
		$mcr . $wppa['mocc'] . ' wppa-' . $wppa_alt . '" style="' . $style . __wcs( 'wppa-cover-box' ) . '" >';

	// First The Cover photo?
	if ( $photo_pos == 'left' || $photo_pos == 'top' ) {
		wppa_the_coverphotos( 
			$albumid, $images, $srcs, $photo_pos, $photolinks, $title, $imgattrs_a, $events );
	}
		
	// Open the Cover text frame
	$textframestyle = 'style="text-align:center;'.__wcs( 'wppa-cover-text-frame' ).'"';
	$wppa['out'] .= wppa_nltab( '+' ) . 
		'<div id="covertext_frame_' . $albumid . '_' . $wppa['mocc'] . 
		'" class="wppa-text-frame-' . $wppa['mocc'] . 
		' wppa-text-frame wppa-cover-text-frame" ' . $textframestyle . '>';

	// The Album title
	wppa_the_album_title( $albumid, $href_title, $onclick_title, $title, $target );

	// The Album description
	if ( wppa_switch( 'wppa_show_cover_text' ) ) {
		$textheight = $wppa_opt['wppa_text_frame_height'] > '0' ?
		'min-height:' . $wppa_opt['wppa_text_frame_height'] . 'px; ' :
		'';
		$wppa['out'] .= wppa_nltab() . 
			'<p class="wppa-box-text wppa-black wppa-box-text-desc" style="' . $textheight . 
			__wcs( 'wppa-box-text' ) . __wcs( 'wppa-black' ) . '">' . 
			wppa_get_album_desc( $albumid ) . '</p>';
	}

	// The 'Slideshow'/'Browse' link
	wppa_the_slideshow_browse_link( $photocount, $href_slideshow, $onclick_slideshow, $target );

	// The 'View' link
	wppa_album_cover_view_link( $albumid, $has_content, $photocount, $albumcount, 
		$mincount, $href_content, $target, $onclick_content );

	// Close the Cover text frame
	$wppa['out'] .= wppa_nltab( '-' ) . '</div><!-- covertext-frame -->';

	// The Cover photo last?
	if ( $photo_pos == 'right' || $photo_pos == 'bottom' ) {
		wppa_the_coverphotos( 
			$albumid, $images, $srcs, $photo_pos, $photolinks, $title, $imgattrs_a, $events );
	}
		
	// The sublinks
	wppa_albumcover_sublinks( $albumid, wppa_get_cover_width( 'cover' ), $multicolresp );

	// Prepare for closing
	$wppa['out'] .= wppa_nltab() . '<div style="clear:both;"></div>';		

	// Close the album box
	$wppa['out'] .= wppa_nltab( '-' ) . 
		'</div><!-- #album-' . $albumid . '-' . $wppa['mocc'] . ' -->';
	if ( $wppa_alt == 'even' ) $wppa_alt = 'alt'; else $wppa_alt = 'even';
}

// Type Long Description
function wppa_album_cover_longdesc( $albumid, $multicolresp = false ) {
global $wppa;
global $wppa_opt;
global $wppa_alt;
global $cover_count_key;
global $wpdb;

	$album = wppa_cache_album( $albumid );

	if ( $multicolresp ) $mcr = 'mcr-'; else $mcr = '';
	
	$coverphoto = wppa_get_coverphoto_id( $albumid );
	$image 		= $wpdb->get_row( $wpdb->prepare( 
					"SELECT * FROM `" . WPPA_PHOTOS . "` WHERE `id` = %s", $coverphoto 
					), ARRAY_A );
	$photocount = wppa_get_photo_count( $albumid, true );
	$albumcount = wppa_get_album_count( $albumid, true );
	$mincount 	= wppa_get_mincount();
	$title 		= '';
	$linkpage 	= '';
	
	$href_title 		= '';
	$href_slideshow 	= '';
	$href_content 		= '';
	$onclick_title 		= '';
	$onclick_slideshow 	= '';
	$onclick_content 	= '';

	// See if there is substantial content to the album
	$has_content = ( $albumcount > '0' ) || ( $photocount > $mincount );
	
	// What is the albums title linktype
	$linktype = $album['cover_linktype'];
	if ( !$linktype ) $linktype = 'content'; // Default 
	
	// What is the albums title linkpage
	$linkpage = $album['cover_linkpage'];
	if ( $linkpage == '-1' ) $linktype = 'none'; // for backward compatibility
	
	// Find the cover title href, onclick and title
	$title_attr 	= wppa_get_album_title_attr_a( 
						$albumid, $linktype, $linkpage, $has_content, $coverphoto, $photocount );
	$href_title 	= $title_attr['href'];
	$onclick_title 	= $title_attr['onclick'];
	$title 			= $title_attr['title'];
	
	// Find the slideshow link and onclick
	$href_slideshow = wppa_convert_to_pretty( wppa_get_slideshow_url( $albumid, $linkpage ) );
	if ( wppa_switch( 'wppa_allow_ajax' ) && ! $linkpage ) {
		$onclick_slideshow = "wppaDoAjaxRender( " . $wppa['mocc'] . ", '" . 
			wppa_get_slideshow_url_ajax( $albumid, $linkpage ) . "', '" . 
			wppa_convert_to_pretty( $href_slideshow ) . "' )";
		$href_slideshow = "#";
	}

	// Find the content 'View' link 
	$href_content = wppa_convert_to_pretty( wppa_get_album_url( $albumid, $linkpage ) );
	if ( wppa_switch( 'wppa_allow_ajax' ) && ! $linkpage ) {
		$onclick_content = "wppaDoAjaxRender( " . $wppa['mocc'] . ", '" . 
			wppa_get_album_url_ajax( $albumid, $linkpage ) . "', '" . 
			wppa_convert_to_pretty( $href_content ) . "' )";
		$href_content = "#";
	}

	// Find the coverphoto link
	if ( $coverphoto ) {
		$photolink = wppa_get_imglnk_a( 
			'coverimg', $coverphoto, $href_title, $title, $onclick_title, '', $albumid );
	}
	else $photolink = false;
	
	// Find the coverphoto details
	if ( $coverphoto ) {
		$path 		= wppa_get_thumb_path( $coverphoto );
		$imgattr_a 	= wppa_get_imgstyle_a( 
							$coverphoto, $path, $wppa_opt['wppa_smallsize'], '', 'cover' );
		$src 		= wppa_get_thumb_url( 
							$coverphoto, '', $imgattr_a['width'], $imgattr_a['height'] );	
	}
	else {
		$path 		= '';
		$imgattr_a 	= false;
		$src 		= '';
	}
	
	// Feed?
	if ( is_feed() ) {
		$events = '';
	}
	else {
		$events = wppa_get_imgevents( 'cover' );
	}
	
	$photo_pos = $wppa['coverphoto_pos'];
	
	$style =  __wcs( 'wppa-box' ) . __wcs( 'wppa-' . $wppa_alt );
	if ( is_feed() ) $style .= ' padding:7px;';
	
	$wid = wppa_get_cover_width( 'cover' );
	$style .= 'width: ' . $wid . 'px;';	
	if ( $cover_count_key == 'm' ) {
		$style .= 'margin-left: 8px;';
	}
	elseif ( $cover_count_key == 'r' ) {
		$style .= 'float:right;';
	}
	else {
		$style .= 'clear:both;';
	}
	wppa_step_covercount( 'cover' );
	
	$target = wppa_switch( 'wppa_allow_ajax' ) ? '_self' : $photolink['target'];
	
	// Open the album box
	$wppa['out'] .= wppa_nltab( '+' ) . 
		'<div id="album-' . $albumid . '-' . $wppa['mocc'] . 
		'" class="wppa-album-cover-longdesc album wppa-box wppa-cover-box wppa-cover-box-' . 
		$mcr . $wppa['mocc'] . ' wppa-' . $wppa_alt . '" style="' . $style . __wcs( 'wppa-cover-box' ) . '" >';

	// First The Cover photo?
	if ( $photo_pos == 'left' || $photo_pos == 'top' ) {
		wppa_the_coverphoto( 
			$albumid, $image, $src, $photo_pos, $photolink, $title, $imgattr_a, $events );
	}
		
	// Open the Cover text frame
	$textframestyle = wppa_get_text_frame_style( $photo_pos, 'cover' );
	$wppa['out'] .= wppa_nltab( '+' ) . 
		'<div id="covertext_frame_' . $albumid . '_' . $wppa['mocc'] . 
		'" class="wppa-text-frame-' . $wppa['mocc'] . 
		' wppa-text-frame wppa-cover-text-frame wppa-asym-text-frame-' . 
		$mcr . $wppa['mocc'] . '" ' . $textframestyle . '>';

	// The Album title
	wppa_the_album_title( $albumid, $href_title, $onclick_title, $title, $target );

	// The 'Slideshow'/'Browse' link
	wppa_the_slideshow_browse_link( $photocount, $href_slideshow, $onclick_slideshow, $target );

	// The 'View' link
	wppa_album_cover_view_link( $albumid, $has_content, $photocount, $albumcount, 
		$mincount, $href_content, $target, $onclick_content );
	
	// Close the Cover text frame
	$wppa['out'] .= wppa_nltab( '-' ) . '</div><!-- covertext-frame -->';

	// The Cover photo last?
	if ( $photo_pos == 'right' || $photo_pos == 'bottom' ) {
		wppa_the_coverphoto( 
			$albumid, $image, $src, $photo_pos, $photolink, $title, $imgattr_a, $events );
	}

	// The Album description
	if ( wppa_switch( 'wppa_show_cover_text' ) ) {
		$textheight = $wppa_opt['wppa_text_frame_height'] > '0' ?
			'min-height:' . $wppa_opt['wppa_text_frame_height'] . 'px; ' :
			'';
		$wppa['out'] .= wppa_nltab() . 
			'<div id="coverdesc_frame_' . $albumid . '_' . $wppa['mocc'] . 
			'" style="clear:both" ><p class="wppa-box-text wppa-black wppa-box-text-desc" style="' . $textheight . 
			__wcs( 'wppa-box-text' ) . __wcs( 'wppa-black' ) . '">' . 
			wppa_get_album_desc( $albumid ) . '</p></div>';
	}
		
	// The sublinks
	wppa_albumcover_sublinks( $albumid, wppa_get_cover_width( 'cover' ), $multicolresp );

	// Prepare for closing
	$wppa['out'] .= wppa_nltab() . '<div style="clear:both;"></div>';		

	// Close the album box
	$wppa['out'] .= wppa_nltab( '-' ) . 
		'</div><!-- #album-' . $albumid . '-' . $wppa['mocc'] . ' -->';
	if ( $wppa_alt == 'even' ) $wppa_alt = 'alt'; else $wppa_alt = 'even';
}

// A single coverphoto
// Output goes directly to $wppa['out']
function wppa_the_coverphoto( 
	$albumid, $image, $src, $photo_pos, $photolink, $title, $imgattr_a, $events ) {
	
global $wppa;
global $wppa_opt;
global $wpdb;

	if ( ! $image ) {
		return;
	}
	
	$imgattr   = $imgattr_a['style'];
	$imgwidth  = $imgattr_a['width'];
	$imgheight = $imgattr_a['height'];
	$frmwidth  = $imgwidth + '10';	// + 2 * 1 border + 2 * 4 padding

	// Find the photo frame style
	if ( $wppa['in_widget'] ) {
		$photoframestyle = 'style="text-align:center; "';
	}
	else {
		switch ( $photo_pos ) {
			case 'left':
				$photoframestyle = 
					'style="float:left; margin-right:5px;width:' . $frmwidth . 'px;"';
				break;
			case 'right':
				$photoframestyle = 
					'style="float:right; margin-left:5px;width:' . $frmwidth . 'px;"';
				break;
			case 'top':
				$photoframestyle = 'style="text-align:center;"';
				break;
			case 'bottom':
				$photoframestyle = 'style="text-align:center;"';
				break;
			default:
				$photoframestyle = '';
				wppa_dbg_msg( 'Illegal $photo_pos in wppa_the_coverphoto' );
		}
	}
		
	// Open the coverphoto frame
	$wppa['out'] .= wppa_nltab( '+' ) . 
		'<div id="coverphoto_frame_' . $albumid . '_' . $wppa['mocc'] . 
		'" class="coverphoto-frame" ' . $photoframestyle . '>';
	
	// The link from the coverphoto
	if ( $photolink ) {
	
		// If lightbox, we need all the album photos to set up a lightbox set
		if ( $photolink['is_lightbox'] ) {
			$thumbs = $wpdb->get_results( $wpdb->prepare( 
				"SELECT * FROM `" . WPPA_PHOTOS . "` WHERE `album` = %s " . 
				wppa_get_photo_order( $albumid ), $albumid 
				), ARRAY_A );
			wppa_dbg_q( 'Q-CovPlB' );							// Report the query
			wppa_cache_thumb( 'add', $thumbs );				// Save rsult in 2nd level cache
			
			if ( $thumbs ) foreach ( $thumbs as $thumb ) {
				$id = $thumb['id'];
				$title = wppa_get_lbtitle( 'cover', $id );
				if ( wppa_is_video( $id ) ) {
					$siz['0'] = wppa_get_videox( $id );
					$siz['1'] = wppa_get_videoy( $id );
				}
				else {
					$siz['0'] = wppa_get_photox( $id );
					$siz['1'] = wppa_get_photoy( $id );
				}
				$link = wppa_get_photo_url( $id, '', $siz['0'], $siz['1'] );
				
				// Open the anchor tag for lightbox
				$wppa['out'] .= "\n\t" . 
					'<a href="' . $link . '" data-videohtml="' . esc_attr( wppa_get_video_body( $id ) ) . 
					'" rel="' . $wppa_opt['wppa_lightbox_name'] . 
					'[alw-' . $wppa['mocc'] . '-' . $albumid . ']" title="' . $title . '" >';
				
				// the cover image
				if ( $id == $image['id'] ) {		
					if ( wppa_is_video( $image['id'] ) ) {
						$wppa['out'] .= "\n\t\t" . 
							'<video preload="metadata" class="image wppa-img" id="i-' . $image['id'] . '-' . 
							$wppa['mocc'] . '" title="' . wppa_zoom_in( $image['id'] ) . 
							'" width="' . $imgwidth . '" height="' . $imgheight . '" style="' . 
							__wcs( 'wppa-img' ) . $imgattr . $imgattr_a['cursor'] . '" ' . 
							$events . ' ' . wppa_get_imgalt( $image['id'] ) . '">' . 
							wppa_get_video_body( $image['id'] ) . '</video>';
					}
					else {
						$wppa['out'] .= "\n\t\t" . 
							'<img class="image wppa-img" id="i-' . $image['id'] . '-' . 
							$wppa['mocc'] . '" title="' . wppa_zoom_in( $image['id'] ) . 
							'" src="' . $src . '" width="' . $imgwidth . '" height="' . $imgheight . '" style="' . 
							__wcs( 'wppa-img' ) . $imgattr . $imgattr_a['cursor'] . '" ' . 
							$events . ' ' . wppa_get_imgalt( $image['id'] ) . ' />';
					}
				}
				
				// Close the lightbox anchor tag
				$wppa['out'] .= "\n\t" . '</a>';
			}
		}
		
		// Link is NOT lightbox
		else { 
			$href = $photolink['url'] == '#' ? '' : 'href="' . wppa_convert_to_pretty( $photolink['url'] ) . '" ';
			$wppa['out'] .= wppa_nltab( '+' ) . 
				'<a ' . $href . 'target="' . $photolink['target'] . '" title="' . $photolink['title'] . 
				'" onclick="' . $photolink['onclick'] . '" >';
				
			// A video?
			if ( wppa_is_video( $image['id'] ) ) {
				$wppa['out'] .= wppa_nltab() . 
					'<video preload="metadata" ' . wppa_get_imgalt( $image['id'] ) . $title . '" class="image wppa-img" width="' . $imgwidth . '" height="' . 
					$imgheight . '" style="' . __wcs( 'wppa-img' ) . $imgattr . '" ' . $events . ' >' . 
					wppa_get_video_body( $image['id'] ) . '</video>';
			}
			
			// A photo
			else {
				$wppa['out'] .= wppa_nltab() . 
					'<img src="' . $src . '" ' . wppa_get_imgalt( $image['id'] ) . ' class="image wppa-img" width="' . 
					$imgwidth . '" height="' . $imgheight . '" style="' . __wcs( 'wppa-img' ) . 
					$imgattr . '" ' . $events . ' />';
			}
			$wppa['out'] .= wppa_nltab( '-' ) . '</a>'; 
		}
	} 
	
	// No link on coverphoto
	else { 
	
		// A video?
		if ( wppa_is_video( $image['id'] ) ) {
			$wppa['out'] .= wppa_nltab() . 
				'<video preload="metadata" ' . wppa_get_imgalt( $image['id'] ) . ' class="image wppa-img" width="' . $imgwidth . '" height="' . 
				$imgheight . '" style="' . __wcs( 'wppa-img' ) . $imgattr . '" ' . $events . ' >' . 
				wppa_get_video_body( $image['id'] ) . '</video>';
		}
		
		// A photo
		else {
			$wppa['out'] .= wppa_nltab() . 
				'<img src="' . $src . '" ' . wppa_get_imgalt( $image['id'] ) . ' class="image wppa-img" width="' . 
				$imgwidth . '" height="' . $imgheight . '" style="' . __wcs( 'wppa-img' ) . 
				$imgattr . '" ' . $events . ' />';
		}
	} 
	
	// Close the coverphoto frame
	$wppa['out'] .= wppa_nltab( '-' ) . 
		'</div><!-- #coverphoto_frame_' . $albumid . '_' . $wppa['mocc'] . ' -->'; 
}

// Multiple coverphotos
// Output goes directly to $wppa['out']
function wppa_the_coverphotos( 
	$albumid, $images, $srcs, $photo_pos, $photolinks, $title, $imgattrs_a, $events ) {
	
global $wppa;
global $wppa_opt;
global $thumb;
global $wpdb;

	if ( ! $images ) { 
		return;
	}
	
	// Find the photo frame style
	$photoframestyle = 'style="text-align:center; "';

	// Open the coverphoto frame
	$wppa['out'] .= wppa_nltab( '+' ) . 
		'<div id="coverphoto_frame_' . $albumid . '_' . $wppa['mocc'] . 
		'" class="coverphoto-frame" ' . $photoframestyle . '>';
	
	// Process the images
	$n = count( $images );
	for ( $idx='0'; $idx < $n; $idx++ ) {
		$image 		= $images[$idx];
		$src 		= $srcs[$idx];
		$imgattr   	= $imgattrs_a[$idx]['style'];
		$imgwidth  	= $imgattrs_a[$idx]['width'];
		$imgheight 	= $imgattrs_a[$idx]['height'];
		$frmwidth  	= $imgwidth + '10';	// + 2 * 1 border + 2 * 4 padding
		$imgattr_a	= $imgattrs_a[$idx];
		$photolink 	= $photolinks[$idx];

		if ( $photolink ) {
			if ( $photolink['is_lightbox'] ) {
				$thumb = $image;
				$title = wppa_get_lbtitle( 'cover', $thumb['id'] );
				if ( wppa_is_video( $thumb['id'] ) ) {
					$siz['0'] = wppa_get_videox( $thumb['id'] );
					$siz['1'] = wppa_get_videoy( $thumb['id'] );
				}
				else {
					$siz['0'] = wppa_get_photox( $thumb['id'] );
					$siz['1'] = wppa_get_photoy( $thumb['id'] );
				}
				$link = wppa_get_photo_url( $thumb['id'], '', $siz['0'], $siz['1'] );
				$wppa['out'] .= 
					'<a href="' . $link . '" data-videohtml="' . 
					esc_attr( wppa_get_video_body( $thumb['id'] ) ) . '" rel="' . 
					$wppa_opt['wppa_lightbox_name'] . '[alw-' . $wppa['mocc'] . '-' . 
					$albumid . ']" title="' . $title . '" >';
					
				// the cover image
				if ( $thumb['id'] == $image['id'] ) {		
					if ( wppa_is_video( $image['id'] ) ) {
						$wppa['out'] .= "\n\t\t" . 
							'<video preload="metadata" class="image wppa-img" id="i-' . $image['id'] . '-' . 
							$wppa['mocc'] . '" title="' . wppa_zoom_in( $image['id'] ) . '" width="' . 
							$imgwidth . '" height="' . $imgheight . '" style="' . __wcs( 'wppa-img' ) . 
							$imgattr . $imgattr_a['cursor'] . '" ' . $events . ' ' . wppa_get_imgalt( $image['id'] ) . '>' . 
							wppa_get_video_body( $image['id'] ) . '</video>';
					}
					else {
						$wppa['out'] .= "\n\t\t" . 
							'<img class="image wppa-img" id="i-' . $image['id'] . '-' . 
							$wppa['mocc'] . '" title="' . wppa_zoom_in( $image['id'] ) . '" src="' . 
							$src . '" width="' . $imgwidth . '" height="' . $imgheight . '" style="' . 
							__wcs( 'wppa-img' ) . $imgattr . $imgattr_a['cursor'] . '" ' . $events . 
							' ' . wppa_get_imgalt( $image['id'] ) . '>';
					}
				}
				$wppa['out'] .= '</a> ';
			}
			
			else {	// Link is NOT lightbox
				$href = $photolink['url'] == '#' ? '' : 'href="' . wppa_convert_to_pretty( $photolink['url'] ) . '" ';
				$wppa['out'] .= '<a ' . $href . 'target="' . $photolink['target'] . 
					'" title="' . $photolink['title'] . '" onclick="' . $photolink['onclick'] . '" >';
				if ( wppa_is_video( $image['id'] ) ) {
					$wppa['out'] .= '<video preload="metadata" ' . wppa_get_imgalt( $image['id'] ) .
						' class="image wppa-img" width="' . $imgwidth . '" height="' . $imgheight . 
						'" style="' . __wcs( 'wppa-img' ) . $imgattr . '" ' . $events . ' >' . 
						wppa_get_video_body( $image['id'] ) . '</video>';
				}
				else {
					$wppa['out'] .= '<img src="' . $src . '" ' . wppa_get_imgalt( $image['id'] ) . 
						' class="image wppa-img" width="' . $imgwidth . '" height="' . $imgheight . 
						'" style="' . __wcs( 'wppa-img' ) . $imgattr . '" ' . $events . ' />';
				}
				$wppa['out'] .= '</a> '; 
			}
		} 
		
		// No link
		else { 
			$wppa['out'] .= wppa_nltab() . 
				'<img src="' . $src . '" ' . wppa_get_imgalt( $image['id'] ) . ' class="image wppa-img" width="' . $imgwidth . 
				'" height="' . $imgheight . '" style="' . __wcs( 'wppa-img' ) . $imgattr . '" ' . $events . ' />';
		} 
	}
		
	// Close the coverphoto frame
	$wppa['out'] .= wppa_nltab( '-' ) . 
		'</div><!-- #coverphoto_frame_' . $albumid . '_' . $wppa['mocc'] . ' -->'; 
}
	
// get id of coverphoto. does all testing
function wppa_get_coverphoto_id( $xalb = '' ) {
	$result = wppa_get_coverphoto_ids( $xalb, '1' );

	if ( empty( $result ) ) return false;
	return $result['0'];
}

// Get the cover photo id(s)
// The id in the album may be 0: random, -1: featured random; -2: last upload; > 0: one assigned specific.
// If one assigned but no longer exists or moved to other album: treat as random
function wppa_get_coverphoto_ids( $alb, $count ) {
global $wpdb;
global $wppa;
	
	if ( ! $alb ) return false;					// no album, no coverphoto
	
	// Find cover photo id
	$id = wppa_get_album_item( $alb, 'main_photo' );
		
	// main_photo is a positive integer ( photo id )?
	if ( $id > '0' ) {									// 1 coverphoto explicitly given
		$photo = wppa_cache_photo( $id );
		if ( ! $photo ) {								// Photo gone, set id to 0
			$id = '0';
		}
		elseif ( $photo['album'] != $alb ) {			// Photo moved to other album, set id to 0
			$id = '0';
		}
		else {
			$temp['0'] = $photo;						// Found!
		}
	}
	
	// main_photo is 0? Random
	if ( '0' == $id ) {
		if ( current_user_can( 'wppa_moderate' ) ) {
			$temp = $wpdb->get_results( $wpdb->prepare( 
				"SELECT * FROM `" . WPPA_PHOTOS . "` WHERE `album` = %s ORDER BY RAND( " . $wppa['page-randseed'] . " ) LIMIT %d", 
				$alb, $count ), ARRAY_A );
		}
		else {
			$temp = $wpdb->get_results( $wpdb->prepare( 
				"SELECT * FROM `" . WPPA_PHOTOS . "` WHERE `album` = %s AND ( ( `status` <> 'pending' AND `status` <> 'scheduled' ) OR `owner` = %s ) ORDER BY RAND( " . $wppa['page-randseed'] . " ) LIMIT %d", 
				$alb, wppa_get_user(), $count ), ARRAY_A );
		}
	}

	// main_photo is -2? Last upload
	if ( '-2' == $id ) {
		if ( current_user_can( 'wppa_moderate' ) ) {
			$temp = $wpdb->get_results( $wpdb->prepare( 
				"SELECT * FROM `" . WPPA_PHOTOS . 
				"` WHERE `album` = %s ORDER BY `timestamp` DESC LIMIT %d", $alb, $count 
				), ARRAY_A );
		}
		else {
			$temp = $wpdb->get_results( $wpdb->prepare( 
				"SELECT * FROM `" . WPPA_PHOTOS . 
				"` WHERE `album` = %s AND ( ( `status` <> 'pending' AND `status` <> 'scheduled' ) OR `owner` = %s ) ORDER BY `timestamp` DESC LIMIT %d", 
				$alb, wppa_get_user(), $count ), ARRAY_A );
		}
	}
		
	// main_phtot is -1? Random featured
	if ( '-1' == $id ) {		
		$temp = $wpdb->get_results( $wpdb->prepare( 
			"SELECT * FROM `" . WPPA_PHOTOS . 
			"` WHERE `album` = %s AND `status` = 'featured' ORDER BY RAND( " . $wppa['page-randseed'] . " ) LIMIT %d", 
			$alb, $count ), ARRAY_A );
	}

	// Report query
	wppa_dbg_q( 'Q-gcovp' );
	
	// Add to 2nd level cache
	wppa_cache_photo( 'add', $temp );
	
	// Extract the ids only
	$ids = array();
	if ( is_array( $temp ) ) foreach ( $temp as $item ) {
		$ids[] = $item['id'];
	}
	return $ids;
}

// Find the cover Title's href, onclick and title
function wppa_get_album_title_attr_a( 
	$albumid, $linktype, $linkpage, $has_content, $coverphoto, $photocount ) {
	
global $wppa;
global $wppa_opt;

	$album = wppa_cache_album( $albumid );
	
	// Init
	$href_title 	= '';
	$onclick_title 	= '';
	$title_title 	= '';
	
	// Dispatch on linktype when page is not current
	if ( $linkpage > 0 ) {
		switch ( $linktype ) {
			case 'content':
				if ( $has_content ) {
					$href_title = wppa_get_album_url( $albumid, $linkpage );
				}
				else {
					$href_title = get_page_link( $album['cover_linkpage'] );
				}
				break;
			case 'slide':
				if ( $has_content ) {
					$href_title = wppa_get_slideshow_url( $albumid, $linkpage );
				}
				else {
					$href_title = get_page_link( $album['cover_linkpage'] );
				}
				break;
			case 'page':
				$href_title = get_page_link( $album['cover_linkpage'] );
				break;
			case 'none':
				break;
			default:
		}
		$href_title = wppa_convert_to_pretty( $href_title );
		$title_title = __a( 'Link to' );
		$title_title .= ' ' . __( get_the_title( $album['cover_linkpage'] ) );
	}
	
	// Dispatch on linktype when page is current
	elseif ( $has_content ) {
		switch ( $linktype ) {
			case 'content':
				$href_title = wppa_convert_to_pretty( wppa_get_album_url( $albumid, $linkpage ) );
				if ( wppa_switch( 'wppa_allow_ajax' ) ) {
					$onclick_title = "wppaDoAjaxRender( " . $wppa['mocc'] . ", '" . 
						wppa_get_album_url_ajax( $albumid, $linkpage ) . "', '" . $href_title . "' )";
					$href_title = "#";
				}
				break;
			case 'slide':
				$href_title = wppa_convert_to_pretty( wppa_get_slideshow_url( $albumid, $linkpage ) );
				if ( wppa_switch( 'wppa_allow_ajax' ) ) {
					$onclick_title = "wppaDoAjaxRender( " . $wppa['mocc'] . ", '" . 
						wppa_get_slideshow_url_ajax( $albumid, $linkpage ) . "', '" . $href_title . "' )";
					$href_title = "#";
				}
				break;
			case 'none':
				break;
			default:
		}
		$title_title = 
			__a( 'View the album' ) . ' ' . esc_attr( wppa_qtrans( stripslashes( $album['name'] ) ) );
	}
	else {	// No content on current page/post
		if ( $photocount > '0' ) {	// coverphotos only
			if ( $coverphoto ) {
				$href_title = wppa_convert_to_pretty( wppa_get_image_page_url_by_id( $coverphoto ) ); 
			}
			else {
				$href_title = '#';
			}
			if ( wppa_switch( 'wppa_allow_ajax' ) ) {
				if ( $coverphoto ) {
					$onclick_title = "wppaDoAjaxRender( " . $wppa['mocc'] . ", '" . 
						wppa_get_image_url_ajax_by_id( $coverphoto ) . "', '" . $href_title . "' )";
				}
				else {
					$onclick_title = '';
				}
				$href_title = "#";
			}
			if ( $photocount == '1' ) $title_title = __a( 'View the cover photo' ); 
			else $title_title = __a( 'View the cover photos' );
		}
	}
	$title_attr['href'] 	= $href_title;
	$title_attr['onclick'] 	= $onclick_title;
	$title_attr['title'] 	= $title_title;
	
	return $title_attr;
}

// The 'View' link
function wppa_album_cover_view_link( 
	$albumid, $has_content, $photocount, $albumcount, $mincount, $href_content, 
	$target, $onclick_content ) {
	
global $wppa;
global $wppa_opt;

	$album = wppa_cache_album( $albumid );
	
	if ( wppa_switch( 'wppa_show_viewlink' ) ) {
		$wppa['out'] .= wppa_nltab( '+' ) . 
			'<div class="wppa-box-text wppa-black wppa-info wppa-viewlink">';
		if ( $has_content ) {
		
			// Fake photocount to prevent link to empty page
			if ( $wppa_opt['wppa_thumbtype'] == 'none' ) $photocount = '0'; 

			// Still has content
			if ( $photocount > $mincount || $albumcount ) {	

				// Get treecount data
				if ( wppa_switch( 'wppa_show_treecount' ) ) {
					$treecount = wppa_treecount_a( $albumid );
				}
				else {
					$treecount = false;
				}
				
				if ( $href_content == '#' ) {
					$wppa['out'] .= wppa_nltab( '+' ) . 
						'<a onclick="' . $onclick_content . '" title="' . __a( 'View the album' ) . ' ' . 
						esc_attr( stripslashes( wppa_qtrans( $album['name'] ) ) ) . '" style="' . 
						__wcs( 'wppa-box-text-nocolor' ) . '" >';
				}
				else {
					$wppa['out'] .= wppa_nltab( '+' ) . 
						'<a href="' . $href_content . '" target="' . $target . '" onclick="' . 
						$onclick_content . '" title="' . __a( 'View the album' ) . ' ' . 
						esc_attr( stripslashes( wppa_qtrans( $album['name'] ) ) ) . 
						'" style="' . __wcs( 'wppa-box-text-nocolor' ) . '" >';
				}

/**/				
				$text = __a( 'View' );
				if ( $albumcount ) { 
					if ( $albumcount == '1' ) {
						$text .= ' 1 ' . __a( 'album' ); 
					}
					else {
						$text .= ' ' . $albumcount . ' ' . __a( 'albums' );
					}
					if ( $treecount ) {
						if ( $treecount['albums'] > $albumcount ) {
							$text .= ' (' . $treecount['albums'] . ')';
						}
					}
				}
				if ( $photocount > $mincount && $albumcount ) {
					$text .= ' ' . __a( 'and' ); 
				}
				if ( $photocount > $mincount || $treecount ) { 
					if ( $photocount <= $mincount ) $photocount = '0';
					if ( $photocount == '1' ) {
						$text .= ' 1 ' . __a( 'photo' );
					}
					elseif ( $photocount ) {
						$text .= ' ' . $photocount . ' ' . __a( 'photos' ); 
					}
					if ( $treecount ) {
						if ( $treecount['photos'] > $photocount ) {
							if ( ! $photocount ) $text .= ', ' . __a( 'photos' ); 
							$text .= ' (' . $treecount['photos'] . ')';
						}
					}
				} 
				$wppa['out'] .= str_replace( ' ', '&nbsp;', $text );
/**/
				$wppa['out'] .= wppa_nltab( '-' ) . '</a>'; 
			}
		} 
		else {
			$wppa['out'] .= '&nbsp;';
		}
		$wppa['out'] .= wppa_nltab( '-' ) . '</div>';
	}
}

function wppa_the_album_title( $alb, $href_title, $onclick_title, $title, $target ) {
global $wppa;

	$album = wppa_cache_album( $alb );

	$wppa['out'] .= wppa_nltab( '+' ) . 
		'<h2 class="wppa-title" style="clear:none; ' . __wcs( 'wppa-title' ) . '">';
		
	if ( $href_title ) { 
		if ( $href_title == '#' ) {
			$wppa['out'] .= wppa_nltab() . 
				'<a onclick="' . $onclick_title . '" title="' . $title . 
				'" class="wppa-title" style="cursor:pointer; ' . __wcs( 'wppa-title' ) . '">' . 
				wppa_get_album_name( $alb ) . '</a>';
		}
		else {
			$wppa['out'] .= wppa_nltab() . 
				'<a href="' . $href_title . '" target="' . $target . '" onclick="' . $onclick_title . 
				'" title="' . $title . '" class="wppa-title" style="' . __wcs( 'wppa-title' ) . '">' . 
				wppa_get_album_name( $alb ) . '</a>';
		}
	} 
	else { 
		$wppa['out'] .= wppa_get_album_name( $alb ); 
	} 
	if ( wppa_is_album_new( $alb ) ) {
		$wppa['out'] .= wppa_nltab() . 
			'<img src="' . WPPA_URL . '/images/new.png" title="' . __a( 'New!' ) . 
			'" class="wppa-albumnew" style="border:none; margin:0; padding:0; box-shadow:none; " alt="'.__a('New').'" />';
	}
	$wppa['out'] .= wppa_nltab( '-' ) . '</h2>';
}

function wppa_albumcover_sublinks( $id, $width, $rsp ) {
	wppa_user_create_html( $id, $width, 'cover', $rsp );
	wppa_user_upload_html( $id, $width, 'cover', $rsp );
	wppa_user_albumedit_html( $id, $width, 'cover', $rsp );
	wppa_album_download_link( $id );
	wppa_the_album_cats( $id );
}

function wppa_the_slideshow_browse_link( $photocount, $href_slideshow, $onclick_slideshow, $target ) {
global $wppa;

	if ( wppa_switch( 'wppa_show_slideshowbrowselink' ) ) {
		$wppa['out'] .= wppa_nltab( '+' ) . 
			'<div class="wppa-box-text wppa-black wppa-info wppa-slideshow-browse-link">';
		if ( $photocount > wppa_get_mincount() ) { 
			$label = wppa_switch( 'wppa_enable_slideshow' ) ?  
				__a( 'Slideshow' ) : 
				__a( 'Browse photos' );
			if ( $href_slideshow == '#' ) {
				$wppa['out'] .= wppa_nltab() . 
					'<a onclick="' . $onclick_slideshow . '" title="' . $label . '" style="' . 
					__wcs( 'wppa-box-text-nocolor' ) . '" >' . $label . '</a>';
			}
			else {
				$wppa['out'] .= wppa_nltab() . 
					'<a href="' . $href_slideshow . '" target="' . $target . '" onclick="' . 
					$onclick_slideshow . '" title="' . $label . '" style="' . 
					__wcs( 'wppa-box-text-nocolor' ) . '" >' . $label . '</a>';
			}
		} 
		else {
			$wppa['out'] .= '&nbsp;'; 
		}
		$wppa['out'] .= wppa_nltab( '-' ) . '</div>';
	}
}

function wppa_the_album_cats( $alb ) {
global $wppa;

	if ( ! wppa_switch( 'wppa_show_cats' ) ) {
		return;
	}
	
	$cats = wppa_get_album_item( $alb, 'cats' );
	$cats = str_replace( ',', ',&nbsp;', $cats );
	
	if ( $cats ) {
		$wppa['out'] .= '<div id="wppa-cats-' . $alb . '-' . $wppa['mocc'] . '" style="float:right" >';
		if ( strpos( $cats ,',' ) ) {
			$wppa['out'] .= __a('Categories:') . '&nbsp;<b>' . $cats . '</b>';
		}
		else {
			$wppa['out'] .= __a('Category:') . '&nbsp;<b>' . $cats . '</b>';
		}
		$wppa['out'] .= '</div>';
	}
}