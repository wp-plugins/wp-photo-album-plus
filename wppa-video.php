<?php
/* wppa-video.php
* Package: wp-photo-album-plus
*
* Contains all video routines
* Version 6.1.0
*
*/
 
if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

// Video files support. Define supported filetypes.
global $wppa_supported_video_extensions;
	$wppa_supported_video_extensions = array( 'mp4', 'ogv', 'webm' );

// See if a photo is a video
// Returns array with all available file extensions or false if not a video
function wppa_is_video( $id ) {
global $wppa_supported_video_extensions;

	if ( ! $id ) return false;					// No id
	
	$ext = wppa_get_photo_item( $id, 'ext' );	
	if ( $ext != 'xxx' ) return false;	// This is not a video
	
	$result = array();
	$path = wppa_get_photo_path( $id );
	$raw_path = wppa_strip_ext( $path );
	foreach ( $wppa_supported_video_extensions as $ext ) {
		if ( is_file( $raw_path.'.'.$ext ) ) {
			$result[$ext] = $ext;
		}
	}
	if ( empty( $result ) ) {
		return false;	// Its multimedia but not video
	}
	
	return $result;
}

// Return the html for video display
function wppa_get_video_html( $args ) {
global $wppa;

	extract( wp_parse_args( (array) $args, array (
					'id'			=> '0',
					'width'			=> '0',
					'height' 		=> '0',
					'controls' 		=> true,
					'margin_top' 	=> '0',
					'margin_bottom' => '0',
					'tagid' 		=> 'video-'.$wppa['mocc'],
					'cursor' 		=> '',
					'events' 		=> '',
					'title' 		=> '',
					'preload' 		=> 'metadata',
					'onclick' 		=> '',
					'lb' 			=> false,
					'class' 		=> '',
					'style' 		=> '',
					'use_thumb' 	=> false,
					'autoplay' 		=> false
					) ) );
					
	// No id? no go
	if ( ! $id ) return '';
	
	// Not a video? no go
	if ( ! wppa_is_video( $id ) ) return '';
	
	extract( wp_parse_args( (array) wppa_is_video( $id ), array (
					'mp4' 	=> false,
					'ogv' 	=> false,
					'webm' 	=> false
					) ) );

	// Prepare attributes
	$w 		= $width ? ' width:'.$width.'px;' : '';
	$h 		= $height ? ' height:'.$height.'px;' : '';
	$t 		= $margin_top ? ' margin-top:'.$margin_top.'px;' : '';
	$b 		= $margin_bottom ? ' margin-bottom:'.$margin_bottom.'px;' : '';
	$ctrl 	= $controls ? ' controls' : '';
	$tit 	= $title ? ' title="'.$title.'"' : '';
	$onc 	= $onclick ? ' onclick="'.$onclick.'"' : '';
	$cls 	= $class ? ' class="'.$class.'"' : '';
	$style 	= $style ? rtrim( trim( $style ), ';' ) . ';' : '';
	$play 	= $autoplay ? ' autoplay' : '';
	
	// See if there is a poster image
	$poster_photo_path = wppa_fix_poster_ext( wppa_get_photo_path( $id ), $id );
	$poster_thumb_path = wppa_fix_poster_ext( wppa_get_thumb_path( $id ), $id );
	$poster_photo = is_file ( $poster_photo_path ) ? ' poster="' . wppa_fix_poster_ext( wppa_get_photo_url( $id ), $id ) . '"' : '';
	$poster_thumb = is_file ( $poster_thumb_path ) ? ' poster="' . wppa_fix_poster_ext( wppa_get_thumb_url( $id ), $id ) . '"' : '';
	$poster = '';	// Init to none
	
	// Thumbnail?
	if ( $use_thumb ) {
		$poster = $poster_thumb;
	}
	// Fullsize image
	else {
		$poster = $poster_photo;
	}
	
	// If the poster exists and no controls, we need no preload at all.
	if ( $poster && ! $controls ) {
		$preload = 'none';
	}
	
	// Do we have html5 video tag supported filetypes on board?
	if ( $mp4 || $ogv || $webm ) {
	
		// Assume the browser supports html5
		$result = '<video id="'.$tagid.'" '.$ctrl.$play.' style="'.$style.$w.$h.$t.$b.$cursor.'" '.$events.' '.$tit.$onc.$poster.' preload="'.$preload.'"'.$cls.' >';

		$result .= wppa_get_video_body( $id, false, $width, $height );

		// Close the video tag
		$result .= '</video>';
	}

	// Done
	return $result;
}

// Get the content of the video tag for photo(video)id = $id
function wppa_get_video_body( $id, $for_lb = false, $w = '0', $h = '0' ) {

	$is_video = wppa_is_video( $id, true );
	
	// Not a video? no go
	if ( ! $is_video ) return '';
	
	// See what file types are present
	extract( wp_parse_args( $is_video, array( 	'mp4' => false,
												'ogv' => false,
												'webm' => false
											) 
							) 
			);
	
	// Collect other data
	$width 		= $w ? $w : wppa_get_videox( $id );
	$height 	= $h ? $h : wppa_get_videoy( $id );
	$source 	= wppa_get_photo_url( $id );
	$source 	= substr( $source, 0, strrpos( $source, '.' ) );
	$class 		= $for_lb ? ' class="wppa-overlay-img"' : '';
	
	$is_opera 	= strpos( $_SERVER["HTTP_USER_AGENT"], 'OPR' );
	$is_ie 		= strpos( $_SERVER["HTTP_USER_AGENT"], 'Trident' );
	$is_safari 	= strpos( $_SERVER["HTTP_USER_AGENT"], 'Safari' );
	
	wppa_dbg_msg('Mp4:'.$mp4.', Opera:'.$is_opera.', Ie:'.$is_ie.', Saf:'.$is_safari);	
	
	// Assume the browser supports html5
	$ext = '';
	if ( $is_opera ) {
		if ( $ogv ) {
			$ext = 'ogv';
		}
		elseif ( $webm ) {
			$ext = 'webm';
		}
		elseif ( $mp4 ) {
			$ext = 'mp4';
		}
	}
	elseif ( $is_safari || $is_ie ) {
		if ( $mp4 ) {
			$ext = 'mp4';
		}
	}
	else {
		if ( $mp4 ) {
			$ext = 'mp4';
		}
		elseif( $webm ) {
			$ext = 'webm';
		}
		elseif( $ogv ) {
			$ext = 'ogv';
		}
	}
	
	if ( $ext ) {
		$mime = str_replace( 'ogv', 'ogg', 'video/'.$ext );
		$result = '<source src="'.$source.'.'.$ext.'" type="'.$mime.'">';
	}
	$result .= __a('There is no filetype available for your browser, or your browser does not support html5 video', 'wppa');
	
	return $result;
}

// Copy the files only
function wppa_copy_video_files( $fromid, $toid ) {
global $wppa_supported_video_extensions;

	// Is it a video?
	if ( ! wppa_is_video( $fromid ) ) return false;

	// Get paths
	$from_path 		= wppa_get_photo_path( $fromid );
	$raw_from_path 	= wppa_strip_ext( $from_path );
	$to_path 		= wppa_get_photo_path( $toid );
	$raw_to_path 	= wppa_strip_ext( $to_path );
	
	// Copy the media files
	foreach ( $wppa_supported_video_extensions as $ext ) {
		$file = $raw_from_path . '.' . $ext;
		if ( is_file( $file ) ) {
			if ( ! copy( $file, $raw_to_path . '.' . $ext ) ) return false;
		}		
	}
	
/*
	// Copy the poster file
	$poster = wppa_fix_poster_ext( $from_path, $fromid );
	if ( is_file( $poster ) ) {
		if ( ! copy( $poster,  $raw_to_path . '.' . wppa_get_ext( $from_path ) ) ) return false;
	}
	
	// Copy the poster thumb
	$poster_thumb 		= wppa_fix_poster_ext( wppa_get_thumb_path( $fromid ) );
	$poster_thumb_to 	= wppa_strip_ext( wppa_get_thumb_path( $toid ) ) . '.' . wppa_get_ext( $poster_thumb );
	if ( is_file( $poster_thumb ) ) {
		if ( ! copy( $poster_thumb, $poster_thumb_to ) ) return false;
	}
	
*/
	// Done!
	return true;
}

function wppa_get_videox( $id ) {
global $thumb;

	if ( ! wppa_is_video( $id ) ) return '0';
	
	wppa_cache_thumb( $id );
	if ( $thumb['videox'] ) return $thumb['videox'];
	return wppa_opt( 'wppa_video_width' );
}

function wppa_get_videoy( $id ) {
global $thumb;

	if ( ! wppa_is_video( $id ) ) return '0';
	
	wppa_cache_thumb( $id );
	if ( $thumb['videoy'] ) return $thumb['videoy'];
	return wppa_opt( 'wppa_video_height' );
}

