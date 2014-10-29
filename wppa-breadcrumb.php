<?php
/* wppa-breadcrumb.php
* Package: wp-photo-album-plus
*
* Functions for breadcrumbs
* Version 5.4.15
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

// shows the breadcrumb navigation 
function wppa_breadcrumb( $opt = '' ) {
global $wppa;
global $wppa_opt;
global $wpdb;
global $wppa_session;

	// See if they need us 
	// Check Table II-A1 a and b
	if ( $opt == 'optional' ) {													
		$pid = wppa_get_the_page_id();
		$type = $wpdb->get_var( $wpdb->prepare( 
			"SELECT `post_type` FROM `" . $wpdb->posts . "` WHERE `ID` = %s", $pid 
			 ) );
		wppa_dbg_q( 'Q-bc1') ;
		if ( $type == 'post' && ! wppa_switch( 'wppa_show_bread_posts' ) ) {
			return;	// Nothing to do here
		}
		if ( $type != 'post' && ! wppa_switch( 'wppa_show_bread_pages' ) ) {
			return;	// Nothing to do here
		}
	}
	
	// Check special cases
	if ( $wppa['is_single'] ) return;			// A single image slideshow needs no navigation 
	if ( wppa_page( 'oneofone' ) ) return; 		// Never at a single image page
	if ( $wppa['is_slideonly'] == '1' ) return;	// Not when slideonly
	if ( $wppa['in_widget'] ) return; 			// Not in a widget
	if ( is_feed() ) return;					// Not in a feed
	
	$thumbhref = '';
	
	// Any special selection has its own switch
	if ( $wppa['is_topten'] && ! wppa_switch( 'wppa_bc_on_topten' ) ) return;		
	if ( $wppa['is_lasten'] && ! wppa_switch( 'wppa_bc_on_lasten' ) ) return;
	if ( $wppa['is_comten'] && ! wppa_switch( 'wppa_bc_on_comten' ) ) return;
	if ( $wppa['is_featen'] && ! wppa_switch( 'wppa_bc_on_featen' ) ) return;
	if ( $wppa['is_related'] && ! wppa_switch( 'wppa_bc_on_related' ) ) return;
	if ( $wppa['is_tag'] && ! wppa_switch( 'wppa_bc_on_tag' ) ) return;
	if ( $wppa['src'] && ! wppa_switch( 'wppa_bc_on_search' ) ) return;

	// Get the album number
	$alb = wppa_is_int( $wppa['start_album'] ) ? 
		$wppa['start_album'] : 
		'0';	// A single album or all ( all = 0 here )
		
	$is_albenum = strlen( $wppa['start_album'] ) > '0' && ! wppa_is_int( $wppa['start_album'] );
	
	wppa_dbg_msg( 'alb=' . $alb . ', albenum=' . $is_albenum, 'green' );
	
	$virtual = ( 
		$wppa['is_topten'] || $wppa['is_lasten'] || $wppa['is_comten'] || 
		$wppa['is_featen'] || $wppa['is_tag'] || $wppa['last_albums'] || $wppa['is_upldr'] 
		 );
		
	if ( $wppa['last_albums'] ) {
		$alb = $wppa['last_albums_parent'];
	}
	
	wppa_dbg_msg( 
		'alb='.$alb.', albenum='.$is_albenum.', l_a='.$wppa['last_albums'].
		', l_a_p='.$wppa['last_albums_parent'], 'green' );
	
	// See if the album is a 'stand alone' album
	$separate = wppa_is_separate( $alb );
	
	// See if the album links to slides in stead of thumbnails
	$slide = ( wppa_get_album_title_linktype( $alb ) == 'slide' ) ? '&amp;wppa-slide' : '';

	// See if we link to covers or to contents
	$to_cover = $wppa_opt['wppa_thumbtype'] == 'none' ? '1' : '0';
	
	// Photo number?
	$photo = $wppa['start_photo'];
	
	wppa_dbg_msg( 
		'pid='.$pid.', type='.$type.', alb='.$alb.', sep='.$separate.
		', slide='.$slide.', t_c=0, ph='.$photo, 'green' );
	
	// Open the breadcrumb box
	$wppa['out'] .= wppa_nltab( '+' ).
		'<div id="wppa-bc-'.$wppa['mocc'].
		'" class="wppa-nav wppa-box wppa-nav-text" style="'.
		__wcs( 'wppa-nav' ).__wcs( 'wppa-box' ).__wcs( 'wppa-nav-text' ).'">';

		// Do we need Home?
		if ( wppa_switch( 'wppa_show_home' ) ) {
			$value 	= __a( 'Home' );
			$href 	= wppa_dbg_url( get_bloginfo( 'url' ) );
			$title 	= get_bloginfo( 'title' );
			wppa_bcitem( $value, $href, $title, 'b1' );
		}
		
		// Page ( grand )parents ?		
		if ( $type == 'page' && wppa_switch( 'wppa_show_page' ) ) {
			wppa_crumb_page_ancestors( $pid );
		}
	
		// Do the post/page
		if ( wppa_switch( 'wppa_show_page' ) ) {
			$value 	= __( stripslashes( $wpdb->get_var( $wpdb->prepare( 
				"SELECT `post_title` FROM `".$wpdb->posts.
				"` WHERE `post_status` = 'publish' AND `ID` = %s LIMIT 0,1", $pid
				) ) ) );
			wppa_dbg_q( 'Q-bc2' );
			$href	= ( $alb || $virtual || $wppa['src'] || $is_albenum ) ? 
				wppa_get_permalink( $pid, true ) : 
				'';
			$title	= $type == 'post' ? __a( 'Post:' ).' '.$value : __a( 'Page:' ).' '.$value;
			wppa_bcitem( $value, $href, $title, 'b3' );
		}

		// The album ( grant )parents if not separate
		if ( ! $separate ) {
			wppa_crumb_ancestors( $alb, $to_cover );
		}
		
		// The album and optionall placeholder for photo
		if ( $wppa['src'] && $wppa['mocc'] == '1' && ! $wppa['is_related'] ) {	// Search
			if ( isset( $wppa_session['search_root'] ) ) {
				$searchroot = $wppa_session['search_root'];
			}
			else {
				$searchroot = '-2';
			}
			$albtxt = $wppa['is_rootsearch'] ? 
				' <span style="cursor:pointer;" title="'.
					esc_attr( sprintf( __a( 'Searchresults from album %s and its subalbums' ), 
					wppa_get_album_name( $searchroot ) ) ).'">*</span> ' : 
				'';
			if ( $wppa['is_slide'] ) {
				$value  = __a( 'Searchstring:' ) . ' ' . ( isset ( $wppa_session['display_searchstring'] ) ? $wppa_session['display_searchstring'] : stripslashes( $wppa['searchstring'] ) ) . $albtxt;
				$thumbhref 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-searchstring='.stripslashes( $wppa['searchstring'] );
				$title  = __a( 'View the thumbnails' );
				wppa_bcitem( $value, $thumbhref, $title, 'b8' );
			}
			$value  = __a( 'Searchstring:' ) . ' ' . ( isset ( $wppa_session['display_searchstring'] ) ? $wppa_session['display_searchstring'] : stripslashes( $wppa['searchstring'] ) ) . $albtxt;
			$href 	= '';
			$title	= '';
			wppa_bcitem( $value, $href, $title, 'b9' );
		}
		elseif ( $wppa['is_upldr'] ) {
			$usr = get_user_by( 'login', $wppa['is_upldr'] );
			if ( $usr ) $user = $usr->display_name; else $user = $wppa['is_upldr'];
			if ( $wppa['is_slide'] ) {
				$value 	= sprintf( __a( 'Photos by %s' ), $user );
				if ( $wppa['start_album'] ) {
					$thumbhref = wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-upldr='.$wppa['is_upldr'].'&amp;wppa-album='.$wppa['start_album'];
				}
				else {
					$thumbhref = wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-upldr='.$wppa['is_upldr'];
				}
				$title	= __a( 'View the thumbnails' );
				wppa_bcitem( $value, $thumbhref, $title, 'b8' );
			}
			$value 	= sprintf( __a( 'Photos by %s' ), $user );
			$href 	= '';
			$title	= '';
			wppa_bcitem( $value, $href, $title, 'b9' );
		}
		elseif ( $wppa['is_topten'] ) {							// TopTen
			if ( $wppa['start_album'] ) {
				$value 	= $is_albenum ? __a( 'Various albums' ) : wppa_get_album_name( $alb );
				$href 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= $is_albenum ? __a( 'Albums:' ).' '.$wppa['start_album'] : __a( 'Album:' ).' '.$value;
				wppa_bcitem( $value, $href, $title, 'b7' );
			}
			if ( $wppa['is_slide'] ) {
				$value 	= __a( 'Top rated photos' );
				$thumbhref 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-topten='.$wppa['topten_count'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= __a( 'View the thumbnails' );
				wppa_bcitem( $value, $thumbhref, $title, 'b8' );
			}
			$value 	= __a( 'Top rated photos' );
			$href 	= '';
			$title	= '';
			wppa_bcitem( $value, $href, $title, 'b9' );
		}
		elseif ( $wppa['is_lasten'] ) {							// Lasten
			if ( $wppa['start_album'] ) {
				$value 	= $is_albenum ? __a( 'Various albums' ) : wppa_get_album_name( $alb );
				$href 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= $is_albenum ? __a( 'Albums:' ).' '.$wppa['start_album'] : __a( 'Album:' ).' '.$value;
				wppa_bcitem( $value, $href, $title, 'b7' );
			}
			if ( $wppa['is_slide'] ) {
				$value 	= __a( 'Recently uploaded photos' );
				$thumbhref 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-lasten='.$wppa['lasten_count'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= __a( 'View the thumbnails' );
				wppa_bcitem( $value, $thumbhref, $title, 'b8' );
			}
			$value 	= __a( 'Recently uploaded photos' );
			$href 	= '';
			$title	= '';
			wppa_bcitem( $value, $href, $title, 'b9' );
		}
		elseif ( $wppa['is_comten'] ) {							// Comten
			if ( $wppa['start_album'] ) {
				$value 	= $is_albenum ? __a( 'Various albums' ) : wppa_get_album_name( $alb );
				$href 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= $is_albenum ? __a( 'Albums:' ).' '.$wppa['start_album'] : __a( 'Album:' ).' '.$value;
				wppa_bcitem( $value, $href, $title, 'b7' );
			}
			if ( $wppa['is_slide'] ) {
				$value 	= __a( 'Recently commented photos' );
				$thumbhref 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-comten='.$wppa['comten_count'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= __a( 'View the thumbnails' );
				wppa_bcitem( $value, $thumbhref, $title, 'b8' );
			}
			$value 	= __a( 'Recently commented photos' );
			$href 	= '';
			$title	= '';
			wppa_bcitem( $value, $href, $title, 'b9' );
		}
		elseif ( $wppa['is_featen'] ) {							// Featen
			if ( $wppa['start_album'] ) {
				$value 	= $is_albenum ? __a( 'Various albums' ) : wppa_get_album_name( $alb );
				$href 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= $is_albenum ? __a( 'Albums:' ).' '.$wppa['start_album'] : __a( 'Album:' ).' '.$value;
				wppa_bcitem( $value, $href, $title, 'b7' );
			}
			if ( $wppa['is_slide'] ) {
				$value 	= __a( 'Featured photos' );
				$thumbhref 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-featen='.$wppa['featen_count'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= __a( 'View the thumbnails' );
				wppa_bcitem( $value, $thumbhref, $title, 'b8' );
			}
			$value 	= __a( 'Featured photos' );
			$href 	= '';
			$title	= '';
			wppa_bcitem( $value, $href, $title, 'b9' );
		}
		elseif ( $wppa['is_related'] ) {						// Related photos
			if ( $wppa['is_slide'] ) {
				$value 	= __a( 'Related photos' );
				$href 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-tag='.$wppa['is_tag'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= __a( 'View the thumbnails' );
				wppa_bcitem( $value, $href, $title, 'b8' );
			}
			$value 	= __a( 'Related photos' );
			$href 	= '';
			$title	= '';
			wppa_bcitem( $value, $href, $title, 'b9' );
		}
		elseif ( $wppa['is_tag'] ) {							// Tagged photos
			if ( $wppa['is_slide'] ) {
				$value 	= __a( 'Tagged photos:' ).'&nbsp;'.str_replace( ';', ' '.__a( 'or' ).' ', str_replace( ',', ' '.__a( 'and' ).' ', trim( $wppa['is_tag'], ',;' ) ) );
				$thumbhref 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-tag='.$wppa['is_tag'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= __a( 'View the thumbnails' );
				wppa_bcitem( $value, $thumbhref, $title, 'b8' );
			}
			$value 	= __a( 'Tagged photos:' ).'&nbsp;'.str_replace( ';', ' '.__a( 'or' ).' ', str_replace( ',', ' '.__a( 'and' ).' ', trim( $wppa['is_tag'], ',;' ) ) );
			$href 	= '';
			$title	= '';
			wppa_bcitem( $value, $href, $title, 'b9' );
		}
		elseif ( $wppa['is_cat'] ) {							// Categorized albums
			if ( $wppa['is_slide'] ) {
				$value 	= __a( 'Category:' ).'&nbsp;'.$wppa['is_cat'];//str_replace( ';', ' '.__a( 'or' ).' ', str_replace( ',', ' '.__a( 'and' ).' ', trim( $wppa['is_tag'], ',;' ) ) );
				$thumbhref 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-cat='.$wppa['is_cat'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= __a( 'View the thumbnails' );
				wppa_bcitem( $value, $thumbhref, $title, 'b8' );
			}
			$value 	= __a( 'Category:' ).'&nbsp;'.$wppa['is_cat'];//str_replace( ';', ' '.__a( 'or' ).' ', str_replace( ',', ' '.__a( 'and' ).' ', trim( $wppa['is_tag'], ',;' ) ) );
			$href 	= '';
			$title	= '';
			wppa_bcitem( $value, $href, $title, 'b9' );
		}

		elseif ( $wppa['last_albums'] ) {							// Recently modified albums( s )
			if ( $wppa['last_albums_parent'] ) {
				$value 	= wppa_get_album_name( $alb );
				$href 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= __a( 'Album:' ).' '.$value;
				wppa_bcitem( $value, $href, $title, 'b7' );
			}
			if ( $wppa['is_slide'] ) {
				$value 	= __a( 'Recently updated albums' );
				$thumbhref 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= __a( 'View the thumbnails' );
				wppa_bcitem( $value, $thumbhref, $title, 'b8' );
			}
			$value 	= __a( 'Recently updated albums' );
			$href 	= '';
			$title	= '';
			wppa_bcitem( $value, $href, $title, 'b9' );
		}
		else { 			// Maybe a simple normal standard album???
			if ( $wppa['is_owner'] ) {
				$usr = get_user_by( 'login', $wppa['is_owner'] );
				if ( $usr ) $dispname = $usr->display_name;
				else $dispname = $wppa['is_owner'];	// User deleted
				$various = sprintf( __a( 'Various albums by %s' ), $dispname );
			}
			else $various = __a( 'Various albums' );
			if ( $wppa['is_slide'] ) {
				$value 	= $is_albenum ? $various : wppa_get_album_name( $alb );
				$href 	= wppa_get_permalink().'wppa-cover=0&amp;wppa-occur='.$wppa['occur'].'&amp;wppa-album='.$wppa['start_album'];
				$title	= $is_albenum ? __a( 'Albums:' ).' '.$wppa['start_album'] : __a( 'Album:' ).' '.$value;
				wppa_bcitem( $value, $href, $title, 'b7' );
			}
			$value 	= $is_albenum ? $various : wppa_get_album_name( $alb );
			$href 	= '';
			$title	= '';
			$class 	= 'b10';
			wppa_bcitem( $value, $href, $title, $class );
		}
		
		// 'Go to thumbnail display' - icon
		if ( $wppa['is_slide'] ) {
			if ( wppa_switch( 'wppa_bc_slide_thumblink' ) ) {
				if ( $virtual ) {
					if ( $thumbhref ) {
						$thumbhref = wppa_trim_wppa_( $thumbhref );
						$fs = $wppa_opt['wppa_fontsize_nav'];	
						if ( $fs != '' ) $fs += 3; else $fs = '15';	// iconsize = fontsize+3, Default to 15
						$imgs = 'height: '.$fs.'px; margin:0 0 -3px 0; padding:0; box-shadow:none;';
						$wppa['out'] .= '<a href="'.$thumbhref.'" title="'.__a( 'Thumbnail view', 'wppa' ).
										'" class="wppa-nav-text" style="'.__wcs( 'wppa-nav-text' ).'float:right; cursor:pointer;" '.
										'onmouseover="jQuery(\'#wppa-tnv\').css(\'display\', \'none\'); jQuery(\'#wppa-tnvh\').css(\'display\', \'\')" '.
										'onmouseout="jQuery(\'#wppa-tnv\').css(\'display\', \'\'); jQuery(\'#wppa-tnvh\').css(\'display\', \'none\')" >'.
										'<img id="wppa-tnv" src="'.wppa_get_imgdir().'application_view_icons.png" alt="'.__a( 'Thumbs', 'wppa_theme' ).'" style="'.$imgs.'" />'.
										'<img id="wppa-tnvh" src="'.wppa_get_imgdir().'application_view_icons_hover.png" alt="'.__a( 'Thumbs', 'wppa_theme' ).'" style="display:none;'.$imgs.'" />'.
						'</a>';
					}
				}
				else {
					$s = $wppa['src'] ? '&wppa-searchstring='.urlencode( $wppa['searchstring'] ) : '';
					$onclick = "wppaDoAjaxRender( ".$wppa['mocc'].", '".wppa_get_album_url_ajax( $wppa['start_album'], '0' )."&amp;wppa-photos-only=1".$s."', '".wppa_convert_to_pretty( wppa_get_album_url( $wppa['start_album'], '0' ).'&wppa-photos-only=1'.$s )."' )";
					$fs = $wppa_opt['wppa_fontsize_nav'];	
					if ( $fs != '' ) $fs += 3; else $fs = '15';	// iconsize = fontsize+3, Default to 15
					$imgs = 'height: '.$fs.'px; margin:0 0 -3px 0; padding:0; box-shadow:none;';
					$wppa['out'] .= '<a title="'.__a( 'Thumbnail view', 'wppa' ).
									'" class="wppa-nav-text" style="'.__wcs( 'wppa-nav-text' ).'float:right; cursor:pointer;" '.
									'onclick="'.$onclick.'" '.
									'onmouseover="jQuery(\'#wppa-tnv\').css(\'display\', \'none\'); jQuery(\'#wppa-tnvh\').css(\'display\', \'\')" '.
									'onmouseout="jQuery(\'#wppa-tnv\').css(\'display\', \'\'); jQuery(\'#wppa-tnvh\').css(\'display\', \'none\')" >'.
										'<img id="wppa-tnv" src="'.wppa_get_imgdir().'application_view_icons.png" alt="'.__a( 'Thumbs', 'wppa_theme' ).'" style="'.$imgs.'" />'.
										'<img id="wppa-tnvh" src="'.wppa_get_imgdir().'application_view_icons_hover.png" alt="'.__a( 'Thumbs', 'wppa_theme' ).'" style="display:none;'.$imgs.'" />'.
									'</a>';
				}
			}
		}
	
	// Close the breadcrumb box
	$wppa['out'] .= wppa_nltab( '-' ).'</div>';
}

// Display a breadcrumb item with optionally a seperator if it is a link. 
// If it's a link, it's not the last item
function wppa_bcitem( $value = '', $href = '', $title = '', $class = '' ) {
global $wppa;
global $wppa_opt;
static $sep;
	
	// Has content?
	if ( ! $value ) return;	// No content
	if ( $href ) {
		$wppa['out'] .= 
			'<a href="'.$href.'" class="wppa-nav-text '.$class.'" style="'.
			__wcs( 'wppa-nav-text' ).'" title="'.esc_attr($title).'" >'.$value.'</a>';
	}
	else {					// No link, its the last item
		$wppa['out'] .= 
			'<span id="bc-pname-'.$wppa['mocc'].'" class="wppa-nav-text '.$class.'" style="'.
			__wcs( 'wppa-nav-text' ).'" title="'.esc_attr($title).'" >'.$value.'</span>';
		return;
	}
		
	// Add seperator
	if ( ! $sep ) {		// Compute the seperator 
		$temp = $wppa_opt['wppa_bc_separator'];
		switch ( $temp ) {
			case 'url':
				$size = $wppa_opt['wppa_fontsize_nav'];
				if ( $size == '' ) $size = '12';
				$style = 'height:'.$size.'px;';
				$sep = ' <img src="'.$wppa_opt['wppa_bc_url'].'" class="no-shadow" style="'.$style.'" /> ';
				break;
			case 'txt':
				$sep = ' '.html_entity_decode( stripslashes( $wppa_opt['wppa_bc_txt'] ), ENT_QUOTES ).' ';
				break;
			default:
				$sep = ' &' . $temp . '; ';
		}
	}
	$wppa['out'] .= 
		'<span class="wppa-nav-text '.$class.'" style="'.
		__wcs( 'wppa-nav-text' ).'" >'.$sep.'</span>';	
}

// Recursive process to display the ( grand )parent albums
function wppa_crumb_ancestors( $alb, $to_cover ) {
global $wppa;
global $wpdb;

	// Find parent
    $parent = wppa_get_parentalbumid( $alb );
	if ( $parent < '1' ) return;				// No parent -> toplevel -> done.
    
    wppa_crumb_ancestors( $parent, $to_cover );

	// Find the album specific link type ( content, slide, page or none ) TO BE EXPANDED! ! ! 
	$slide = ( wppa_get_album_title_linktype( $parent ) == 'slide' ) ? '&amp;wppa-slide' : '';
	
	$pagid = $wpdb->get_var( $wpdb->prepare( 
		"SELECT `cover_linkpage` FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $parent 
		) );
	wppa_dbg_q( 'Q-bc3' );

	$value 	= wppa_get_album_name( $parent );
	$href 	= 
		wppa_get_permalink( $pagid ).
		'wppa-album='.$parent.'&amp;wppa-cover='.$to_cover.$slide.
		'&amp;wppa-occur='.$wppa['occur'];
	$title 	= __( 'Album:' ).' '.wppa_get_album_name( $parent );
	$class 	= 'b20';
	wppa_bcitem( $value, $href, $title, $class );
    return;
}

// Recursive process to display the ( grand )parent pages
function wppa_crumb_page_ancestors( $page = '0' ) {
global $wpdb;
global $wppa;

	$query = "SELECT post_parent FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish' AND id = %s LIMIT 0,1";
	$parent = $wpdb->get_var( $wpdb->prepare( $query, $page ) );
	wppa_dbg_q( 'Q-bc4' );

	if ( ! is_numeric( $parent ) || $parent == '0' ) return;

	wppa_crumb_page_ancestors( $parent );

	$query = "SELECT post_title FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish' AND id = %s LIMIT 0,1";
	$title = $wpdb->get_var( $wpdb->prepare( $query, $parent ) );
	wppa_dbg_q( 'Q-bc5' );
	
	$title = __( stripslashes( $title ) );
	if ( ! $title ) {
		$title = '****';		// Page exists but is not publish
		wppa_bcitem( $title, '#', __a( 'Unpublished' ), $class = 'b2' );
	} else {
		wppa_bcitem( $title, get_page_link( $parent ), __( 'Page:' ).' '.$title, 'b2' );
	}
}

// Get the page id, returns the page id we are working for, even when Ajax
function wppa_get_the_page_id() {
	$page = @ get_the_ID();
	if ( ! $page ) {
		if ( isset( $_REQUEST['page_id'] ) ) $page = $_REQUEST['page_id'];
		elseif ( isset( $_REQUEST['wppa-fromp'] ) ) $page = $_REQUEST['wppa-fromp'];
		else $page = '0';
	}
	return $page;
}
