<?php
/* wppa_widgetfunctions.php
/* Package: wp-photo-album-plus
/*
/* Version 5.4.18
/*
*/

// This function returns an array of photos that meet the current photo of the day selection criteria
function wppa_get_widgetphotos( $alb, $option = '' ) {
global $wpdb;

	$photos = false;
	
	// Is it a single album?
	if ( is_numeric( $alb ) ) {
		$query = $wpdb->prepare( "SELECT * FROM `" . WPPA_PHOTOS . "` WHERE `album` = %s " . $option, $alb );
		$photos = $wpdb->get_results( $query, ARRAY_A );
		wppa_dbg_q( 'Q-WidP1' );
		wppa_cache_photo( 'add', $photos );
	}
	
	// Is it an enumeration of album ids?
	elseif ( strchr( $alb, ',' ) ) {
		$albs = explode( ',', $alb );
		$query = "SELECT * FROM `" . WPPA_PHOTOS . "` WHERE `album` = '0' ";
		foreach ( $albs as $a ) if ( is_numeric( $a ) ) {
				$query .= "OR `album` = '" . $a . "' ";
		}
		$query .= $option;
		$photos = $wpdb->get_results( $query, ARRAY_A );
		wppa_dbg_q( 'Q-WidP2' );
		wppa_cache_photo( 'add', $photos );
	}
	
	// Is it ALL?
	elseif ( $alb == 'all' ) {
		$query = "SELECT * FROM `" . WPPA_PHOTOS . "` " . $option;
		$photos = $wpdb->get_results( $query, ARRAY_A );
		wppa_dbg_q( 'Q-WidP3' );
		wppa_cache_photo( 'add', $photos );
	}
	
	// Is it SEP?
	elseif ( $alb == 'sep' ) {
		$albs = $wpdb->get_results( "SELECT `id`, `a_parent` FROM `" . WPPA_ALBUMS . "`", ARRAY_A );
		$query = "SELECT * FROM `" . WPPA_PHOTOS . "` WHERE `album` = '0' ";
		$first = true;
		foreach ( $albs as $a ) {
			if ( $a['a_parent'] == '-1' ) {
				$query .= "OR `album` = '" . $a['id'] . "' ";
			}
		}
		$query .= $option;
		$photos = $wpdb->get_results( $query, ARRAY_A );
		wppa_dbg_q( 'Q-WidP4' );
		wppa_cache_photo( 'add', $photos );
	}
	
	// Is it ALL-SEP?
	elseif ( $alb == 'all-sep' ) {
		$albs = $wpdb->get_results( "SELECT `id`, `a_parent` FROM `" . WPPA_ALBUMS . "`", ARRAY_A );
		$query = "SELECT * FROM `" . WPPA_PHOTOS . "` WHERE `album` = '0' ";
		foreach ( $albs as $a ) {
			if ( $a['a_parent'] != '-1' ) {
				$query .= "OR `album` = '" . $a['id'] . "' ";
			}
		}
		$option;
		$photos = $wpdb->get_results( $query, ARRAY_A );
		wppa_dbg_q( 'Q-WidP5' );
		wppa_cache_photo( 'add', $photos );
	}
	
	// Is it Topten?
	elseif ( $alb == 'topten' ) {
	
		// Find the 'top' policy
		switch ( wppa_opt( 'wppa_topten_sortby' ) ) {
			case 'mean_rating':
				$sortby = '`mean_rating` DESC, `rating_count` DESC, `views` DESC';
				break;
			case 'rating_count':
				$sortby = '`rating_count` DESC, `mean_rating` DESC, `views` DESC';
				break;
			case 'views':
				$sortby = '`views` DESC, `mean_rating` DESC, `rating_count` DESC';
				break;
			default:
				wppa_error_message( 'Unimplemented sorting method' );
				$sortby = '';
				break;
		}

		$query = "SELECT * FROM `" . WPPA_PHOTOS . "` ORDER BY " . $sortby . " LIMIT " . wppa_opt( 'wppa_topten_count' );
		$photos = $wpdb->get_results( $query, ARRAY_A );
		wppa_dbg_q( 'Q-WidP6' );
		wppa_cache_photo( 'add', $photos );
	}
	
	// Is is Featured?
	elseif ( $alb == 'featured' ) {
		$query = "SELECT * FROM `" . WPPA_PHOTOS . "` WHERE `status` = 'featured' " . $option;
		$photos = $wpdb->get_results( $query, ARRAY_A );
		wppa_dbg_q( 'Q-WidP7' );
		wppa_cache_photo( 'add', $photos );
	}

	return $photos;
}

// get select form element listing albums 
// Special version for widget
function wppa_walbum_select( $sel = '' ) {
	global $wpdb;
	$albums = $wpdb->get_results( "SELECT * FROM `" . WPPA_ALBUMS . "` ORDER BY `name`", ARRAY_A );
	wppa_dbg_q( 'Q-Asel' );
	wppa_cache_album( 'add', $albums );
	
	if ( is_numeric( $sel ) ) $type = 1;		// Single number
	elseif ( strchr( $sel, ',' ) ) {
		$type = 2;							// Array
		$albs =  explode( ',', $sel );
	}
	elseif ( $sel == 'all' ) $type = 3;		// All
	elseif ( $sel == 'sep' ) $type = 4;		// Separate only
	elseif ( $sel == 'all-sep' ) $type = 5;	// All minus separate
	elseif ( $sel == 'topten' ) $type = 6;	// Topten
	elseif ( $sel == 'featured' ) $type = 7;	// Featured
	else $type = 0;							// Nothing yet
    
    $result = '<option value="" >' . __( '- select (another) album or a set -', 'wppa' ) . '</option>';
    
	foreach ( $albums as $album ) {
		switch ( $type ) {
			case 1:
				$dis = ( $album['id'] == $sel );
				break;
			case 2:
				$dis = in_array( $album['id'], $albs );
				break;
			case 3:
				$dis = true;
				break;
			case 4:
				$dis = ( $album['a_parent'] == '-1' );
				break;
			case 5:
				$dis = ( $album['a_parent'] != '-1' );
				break;
			case 6:
				$dis = false;
				break;
			case 7:
				$dis = false;
				break;
			default:
				$dis = false;
		}
		if ( $dis ) $dis = 'disabled="disabled"';
		else $dis = '';
		$result .= '<option ' . $dis . ' value="' . $album['id'] . '">( ' . $album['id'] . ' )';
			if ( $album['id'] < '1000' ) $result .= '&nbsp;';
			if ( $album['id'] < '100' ) $result .= '&nbsp;';
			if ( $album['id'] < '10' ) $result .= '&nbsp;';
			$result .= wppa_qtrans( stripslashes( $album['name'] ) ) . '</option>';
	}
	$sel = $type == 3 ? 'selected="selected"' : '';
	$result .= '<option value="all" ' . $sel . ' >' . __( '- all albums -', 'wppa' ) . '</option>';
	$sel = $type == 4 ? 'selected="selected"' : '';
	$result .= '<option value="sep" ' . $sel . ' >' . __( '- all -separate- albums -', 'wppa' ) . '</option>';
	$sel = $type == 5 ? 'selected="selected"' : '';
	$result .= '<option value="all-sep" ' . $sel . ' >' . __( '- all albums except -separate-', 'wppa' ) . '</option>';
	$sel = $type == 6 ? 'selected="selected"' : '';
	$result .= '<option value="topten" ' . $sel . ' >' . __( '- top rated photos -', 'wppa' ) . '</option>';
	$sel = $type == 7 ? 'selected="selected"' : '';
	$result .= '<option value="featured" ' . $sel . ' >' . __( '- featured photos -', 'wppa' ) . '</option>';
	$result .= '<option value="clr" >' . __( '- start over -', 'wppa' ) . '</option>';
	return $result;
}

function wppa_walbum_sanitize( $walbum ) {

	$result = strtolower( $walbum );
	$result = strip_tags( $result );
	
	if ( strstr( $result, 'all-sep' ) ) $result = 'all-sep';
	elseif ( strstr( $result, 'all' ) ) $result = 'all';
	elseif ( strstr( $result, 'sep' ) ) $result = 'sep';
	elseif ( strstr( $result, 'topten' ) ) $result = 'topten';
	elseif ( strstr( $result, 'clr' ) ) $result = '';
	else {
	
		// Change multiple commas to one
		while ( substr_count( $result, ',,' ) ) $result = str_replace( ',,', ',', $result );
		
		// remove leading and trailing commas
		$result = trim( $result, ',' );
		
		// Check for illegal chars
		$temp = str_replace( ',', '', $result );
		if ( $temp && ! wppa_is_int( $temp ) ) {
			// $result contains other chars than numbers and comma's
			$result = 'clr';
		}
	}
	return $result;
}

// get the photo of the day
function wppa_get_potd() {
global $wpdb;
global $thumb;

	$image = '';
	switch ( wppa_opt( 'wppa_widget_method' ) ) {
		case '1':	// Fixed photo
			$id = wppa_opt( 'wppa_widget_photo' );
			if ( $id != '' ) {
				$image = $wpdb->get_row( $wpdb->prepare( 
					"SELECT * FROM `" . WPPA_PHOTOS . "` WHERE `id` = %s LIMIT 0,1", $id 
					), ARRAY_A );
				wppa_dbg_q( 'Q-Potd' );
				wppa_cache_photo( 'add', $image );
			}
			break;
		case '2':	// Random
			$album = wppa_opt( 'wppa_widget_album' );
			if ( $album == 'topten' ) {
				$images = wppa_get_widgetphotos( $album );
				if ( count( $images ) > 1 ) {	// Select a random first from the current selection
					$idx = rand( 0, count( $images ) - 1 );
					$image = $images[$idx];
				}
			}
			elseif ( $album != '' ) {
				$images = wppa_get_widgetphotos( $album, "ORDER BY RAND() LIMIT 0,1" );
				$image = $images[0];
			}
			break;
		case '3':	// Last upload
			$album = wppa_opt( 'wppa_widget_album' );
			if ( $album == 'topten' ) {
				$images = wppa_get_widgetphotos( $album );
				if ( $images ) {
					// fid last uploaded image in the $images pool
					$temp = 0;
					foreach( $images as $img ) {
						if ( $img['timestamp'] > $temp ) {
							$temp = $img['timestamp'];
							$image = $img;
						}
					}
				}
			}
			elseif ( $album != '' ) {
				$images = wppa_get_widgetphotos( $album, "ORDER BY timestamp DESC LIMIT 0,1" );
				$image = $images[0];
			}
			break;
		case '4':	// Change every
			$album = wppa_opt( 'wppa_widget_album' );
			if ( $album != '' ) {
				$per = wppa_opt( 'wppa_widget_period' );
				$photos = wppa_get_widgetphotos( $album );
				if ( $per == '0' ) {
					if ( $photos ) {
						$image = $photos[rand( 0, count( $photos )-1 )];
					}
					else $image = '';
				}
				elseif ( $per == 'day-of-week' ) {
					$image = '';
					if ( $photos ) {
						$d = wppa_local_date( 'w' );
						if ( ! $d ) $d = '7';
						foreach ( $photos as $img ) {
							if ( $img['p_order'] == $d ) $image = $img;
						}
					}
				}
				elseif ( $per == 'day-of-month' ) {
					$image = '';
					if ( $photos ) {
						$d = wppa_local_date( 'd' );
						if ( substr( $d, '0', '1' ) == '0' ) $d = substr( $d,'1' );
						foreach ( $photos as $img ) {
							if ( $img['p_order'] == $d ) $image = $img;
						}
					}
				}
				else {
					$u = date( "U" ); // Seconds since 1-1-1970
					$u /= 3600;		//  hours since
					$u = floor( $u );
					$u /= $per;
					$u = floor( $u );
					if ( $photos ) {
						$p = count( $photos ); 
						$idn = fmod( $u, $p );
						$image = $photos[$idn];
					}
					else {
						$image = '';
					}
				}
			} else {
				$image = '';
			}
			break;
							
		default:
			$image = '';
	}

	$thumb = $image;
	
	return $image;
}

// Do the widget thumb
function wppa_do_the_widget_thumb( $type, $image, $album, $display, $link, $title, $imgurl, $imgstyle_a, $imgevents ) {
global $wppa;
global $widget_content;

	// Get the id
	$id = $image ? $image['id'] : '0';

	// Is it a video?
	$is_video = $id ? wppa_is_video( $id ) : false;
	
	// Get the video body
	$videobody = $id ? wppa_get_video_body( $id ) : '';
	
	// Open container if an image must be displayed
	if ( $display == 'thumbs' ) {
		$size = max( $imgstyle_a['width'], $imgstyle_a['height'] );
		$widget_content .= '<div style="width:' . $size . 'px; height:' . $size . 'px; overflow:hidden;" >';
	}
	
	// Get the name
	$name = $id ? wppa_get_photo_name( $id ) : '';
	
	if ( $link ) {
		if ( $link['is_url'] ) {	// Is a href
			$widget_content .= "\n\t" . '<a href="' . $link['url'] . '" title="' . $title . '" target="' . $link['target'] . '" >';
				$widget_content .= "\n\t\t";
				if ( $display == 'thumbs' ) {
					if ( $is_video ) {
						$widget_content .= wppa_get_video_html( array(
							'id'			=> $id,
							'width'			=> $imgstyle_a['width'],
							'height' 		=> $imgstyle_a['height'],
							'controls' 		=> false,
							'margin_top' 	=> $imgstyle_a['margin-top'],
							'margin_bottom' => $imgstyle_a['margin-bottom'],
							'tagid' 		=> 'i-' . $id . '-' . $wppa['mocc'],
							'cursor' 		=> 'cursor:pointer;',
							'events' 		=> $imgevents,
							'title' 		=> $title,
						) );
					}
					else {
						$widget_content .= '<img id="i-' . $id . '-' . $wppa['mocc'] . '" title="' . $title . '" src="' . $imgurl . '" width="' . $imgstyle_a['width'] . '" height="' . $imgstyle_a['height'] . '" style="' . $imgstyle_a['style'] . ' cursor:pointer;" ' . $imgevents . ' ' . wppa_get_imgalt( $id ) . ' />';
					}
				}
				else {
					$widget_content .= $name;
				}
			$widget_content .= "\n\t" . '</a>';
		}
		elseif ( $link['is_lightbox'] ) {
			$title = wppa_get_lbtitle( 'thumb', $id );
			$widget_content .= "\n\t" . '<a href="' . $link['url'] . '" data-videohtml="' . esc_js( $videobody ) . '" rel="' . wppa_opt( 'wppa_lightbox_name' ) . '[' . $type . '-' . $album . '-' . $wppa['mocc'] . ']" title="' . $title . '" target="' . $link['target'] . '" >';
				$widget_content .= "\n\t\t";
				if ( $display == 'thumbs' ) {
					if ( $is_video ) {
						$widget_content .= wppa_get_video_html( array(
							'id'			=> $id,
							'width'			=> $imgstyle_a['width'],
							'height' 		=> $imgstyle_a['height'],
							'controls' 		=> false,
							'margin_top' 	=> $imgstyle_a['margin-top'],
							'margin_bottom' => $imgstyle_a['margin-bottom'],
							'tagid' 		=> 'i-' . $id . '-' . $wppa['mocc'],
							'cursor' 		=> $imgstyle_a['cursor'],
							'events' 		=> $imgevents,
							'title' 		=> wppa_zoom_in( $id ),
						) );
					}
					else {
						$widget_content .= '<img id="i-' . $id . '-' . $wppa['mocc'] . '" title="' . wppa_zoom_in( $id ) . '" src="' . $imgurl . '" width="' . $imgstyle_a['width'] . '" height="' . $imgstyle_a['height'] . '" style="' . $imgstyle_a['style'] . $imgstyle_a['cursor'] . '" ' . $imgevents . ' ' . wppa_get_imgalt( $id ) . ' />';
					}
				}
				else {
					$widget_content .= $name;
				}
			$widget_content .= "\n\t" . '</a>';
		}
		else { // Is an onclick unit
			$widget_content .= "\n\t";
			if ( $display == 'thumbs' ) {
				if ( $is_video ) {
					$widget_content .= wppa_get_video_html( array(
							'id'			=> $id,
							'width'			=> $imgstyle_a['width'],
							'height' 		=> $imgstyle_a['height'],
							'controls' 		=> false,
							'margin_top' 	=> $imgstyle_a['margin-top'],
							'margin_bottom' => $imgstyle_a['margin-bottom'],
							'tagid' 		=> 'i-' . $id . '-' . $wppa['mocc'],
							'cursor' 		=> 'cursor:pointer;',
							'events' 		=> $imgevents,
							'title' 		=> $title,
							'onclick' 		=> $link['url']
						) );
				}
				else {
					$widget_content .= '<img id="i-' . $id . '-' . $wppa['mocc'] . '" title="' . $title . '" src="' . $imgurl . '" width="' . $imgstyle_a['width'] . '" height="' . $imgstyle_a['height'] . '" style="' . $imgstyle_a['style'] . ' cursor:pointer;" ' . $imgevents . ' onclick="' . $link['url'] . '" ' . wppa_get_imgalt( $id ) . ' />';
				}
			}
			else {
				$widget_content .= '<a href="" style="cursor:pointer;" onclick="' . $link['url'] . '">' . $name . '</a>';

			}	
		}
	}
	else {	// No link
		$widget_content .= "\n\t";
		if ( $display == 'thumbs' ) {
			if ( $is_video ) { 
				$widget_content .= wppa_get_video_html( array(
						'id'			=> $id,
						'width'			=> $imgstyle_a['width'],
						'height' 		=> $imgstyle_a['height'],
						'controls' 		=> false,
						'margin_top' 	=> $imgstyle_a['margin-top'],
						'margin_bottom' => $imgstyle_a['margin-bottom'],
						'tagid' 		=> 'i-' . $id . '-' . $wppa['mocc'],
						'cursor' 		=> 'cursor:pointer;',
						'events' 		=> $imgevents,
						'title' 		=> $title
					) );
			}
			else {
				$widget_content .= '<img id="i-' . $id . '-' . $wppa['mocc'] . '" title="' . $title . '" src="' . $imgurl . '" width="' . $imgstyle_a['width'] . '" height="' . $imgstyle_a['height'] . '" style="' . $imgstyle_a['style'] . '" ' . $imgevents . ' ' . wppa_get_imgalt( $id ) . ' />';
			}
		}
		else {
			$widget_content .= $name;
		}
	}
	if ( $display == 'thumbs' ) {
		$widget_content .= $id ? wppa_get_medal_html( $id, max( $imgstyle_a['height'], $imgstyle_a['width'] ) ) : '';
	}
	
	// Close container
	if ( $display == 'thumbs' ) {
		$widget_content .= '</div>';
	}

}