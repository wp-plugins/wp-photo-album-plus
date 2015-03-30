<?php
/* wppa-video.php
* Package: wp-photo-album-plus
*
* Contains all video routines
* Version 6.0.0
*
*/
 
if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

// Video files support. 
add_action( 'init', 'wppa_init_video' );

function wppa_init_video() {
global $wppa_supported_video_extensions;

	if ( wppa_is_video_enabled() ) {
		$wppa_supported_video_extensions = array( 'mp4', 'ogv', 'ogg', 'webm', 'MP4' );
	}
	else {
		$wppa_supported_video_extensions = array();
	}
}

function wppa_is_video_enabled() {
	return wppa_switch( 'wppa_enable_video' );
}

// See if a photo is a video
// Returns array with all available file extensions if flag is set, or false if not a video
function wppa_is_video( $id, $ret_ext = false ) {
global $wppa_supported_video_extensions;

	if ( ! $id ) return false;					// No id
	
	$ext = wppa_get_photo_item( $id, 'ext' );	
	if ( $ext != 'xxx' ) return false;	// This is not a video
	if ( ! $ret_ext ) return true;		// Need not more to know
	
	$result = array();
	$path = wppa_get_photo_path( $id );
	$raw_path = substr( $path, 0, strlen( $path ) - 4 );
	foreach ( $wppa_supported_video_extensions as $ext ) {
		if ( is_file( $raw_path.'.'.$ext ) ) {
			$result[$ext] = $ext;
		}
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
	
	extract( wp_parse_args( (array) wppa_is_video( $id, true ), array (
					'mp4' 	=> false,
					'ogv' 	=> false,
					'ogg' 	=> false,
					'webm' 	=> false
					) ) );

	// Find sizes
//	if ( ! $width ) $width = wppa_get_videox( $id );
//	if ( ! $height ) $height = wppa_get_videoy( $id );
	
	// Find basic source url
	$source = wppa_get_photo_url( $id );
	$source = substr( $source, 0, strrpos( $source, '.' ) );

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
	
	// This is a preview only. See if there is a poster image
	if ( ! $controls ) {

		// This is a preview only. See if it can be a thumbnail
		if ( $use_thumb && file_exists( str_replace( 'xxx', 'jpg', wppa_get_thumb_path( $id ) ) ) ) {
			$poster = ' poster="' . str_replace( 'xxx', 'jpg', wppa_get_thumb_url( $id ) ) . '"';
			// This is a thumbnail non videorunning image, so do not preload a all
			$preload = 'none';
		}
		elseif ( file_exists( str_replace( 'xxx', 'jpg', wppa_get_photo_path( $id ) ) ) ) {	// Poster file found
			$poster = ' poster="' . str_replace( 'xxx', 'jpg', wppa_get_photo_url( $id ) ) . '"';
			// This is an other non videorunning image, so do not preload a all
			$preload = 'none';
		}
		else {
			$poster = '';
		}
	}
	else {
		$poster = '';
	}
	
	// Do we have html5 video tag supported filetypes on board?
	if ( $mp4 || $ogv || $ogg || $webm ) {
	
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
												'ogg' => false, 
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
	$result = '';
	if ( $mp4 && ! $is_opera ) $result .= '<source src="'.$source.'.mp4" type="video/mp4">';
	elseif ( $ogv && ! $is_ie && $is_opera ) $result .= '<source src="'.$source.'.ogv" type="video/ogg">';
	elseif ( $ogg && ! $is_ie && $is_opera ) $result .= '<source src="'.$source.'.ogg" type="video/ogg">';
	elseif ( $webm && ! $is_ie && ! $is_safari ) $result .= '<source src="'.$source.'.webm" type="video/webm">';
	
	return $result;
}


// Delete a video. First all filetypes, then the db entries
function wppa_delete_video( $id ) {
global $wppa_supported_video_extensions;
global $wpdb;

	if ( ! wppa_is_video( $id ) ) return;
	
	$path = wppa_get_photo_path( $id );
	$raw_path = substr( $path, 0, strlen( $path ) - 4 );
	foreach ( $wppa_supported_video_extensions as $ext ) {
		$file = $raw_path.'.'.$ext;
		if ( is_file( $file ) ) unlink( $file );
	}
	$poster = $raw_path.'.jpg';
	if ( is_file( $poster ) ) {
		unlink( $poster );
	}
	$poster_thumb = str_replace( 'xxx', 'jpg', wppa_get_thumb_path( $id ) );
	if ( is_file( $poster_thumb ) ) {
		unlink( $poster_thumb );
	}
	$wpdb->query( $wpdb->prepare( "DELETE FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $id ) );
}

// Copy the files only
function wppa_copy_video_files( $fromid, $toid ) {
global $wppa_supported_video_extensions;

	if ( ! wppa_is_video( $fromid ) ) return false;

	$from_path 		= wppa_get_photo_path( $fromid );
	$raw_from_path 	= substr( $from_path, 0, strlen( $from_path ) - 4 );
	$to_path 		= wppa_get_photo_path( $toid );
	$raw_to_path 	= substr( $to_path, 0, strlen( $to_path ) - 4 );
	foreach ( $wppa_supported_video_extensions as $ext ) {
		$file = $raw_from_path.'.'.$ext;
		if ( is_file( $file ) ) {
			if ( ! copy( $file, $raw_to_path.'.'.$ext ) ) return false;
		}		
	}
	$poster = $raw_from_path.'.jpg';
	if ( is_file( $poster ) ) {
		if ( ! copy( $poster,  $raw_to_path.'.jpg' ) ) return false;
	}
	$poster_thumb = str_replace( 'xxx', 'jpg', wppa_get_thumb_path( $fromid ) );
	$poster_thumb_to = str_replace( 'xxx', 'jpg', wppa_get_thumb_path( $toid ) );
	if ( is_file( $poster_thumb ) ) {
		if ( ! copy( $poster_thumb, $poster_thumb_to ) ) return false;
	}
	return true;
}

function wppa_get_videox( $id ) {
global $thumb;
global $wppa_opt;

	if ( ! wppa_is_video( $id ) ) return '0';
	
	wppa_cache_thumb( $id );
	if ( $thumb['videox'] ) return $thumb['videox'];
	return $wppa_opt['wppa_video_width'];
}

function wppa_get_videoy( $id ) {
global $thumb;
global $wppa_opt;

	if ( ! wppa_is_video( $id ) ) return '0';
	
	wppa_cache_thumb( $id );
	if ( $thumb['videoy'] ) return $thumb['videoy'];
	return $wppa_opt['wppa_video_height'];
}

function wppa_fix_poster_ext( $file ) {

	return str_replace( '.xxx', '.jpg', $file );
	/*
	$temp = explode( '?', $file );
	$file = $temp['0'];
	
	$ext = wppa_get_ext( $file );
	if ( $ext == 'xxx' ) {
		$result = wppa_strip_ext( $file ) . '.jpg';
	}
	else {
		$result = $file;
	}
	if ( isset( $temp['1'] ) ) {
		$result .= '?' . $temp['1'];
	}
	return $result;
	*/
}