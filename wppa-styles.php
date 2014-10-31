<?php
/* wppa-styles.php
/* Package: wp-photo-album-plus
/*
/* Various style computation routines
/* Version 5.4.17
/*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

// Create dynamic css file
// This function creates the dynamic css file with styles that depend on the settings. 
// Any updates to this routine must also be implemented in __wcs()
/* If you add something that uses an element from $wppa_opt[], */
/* or a function that uses an element from $wppa_opt[], */
/* add the optionslug to $dynamic_css_critical[] in wppa_update_option in wppa-utils.php!!!!! */
function wppa_create_wppa_dynamic_css() {
global $wppa_dynamic_css_data;

	$header = 
'/* -- WPPA+ css
/*
/* Dynamicly Created on ' . date( 'c' ) . '
/*
*/
';	

	$content = '
.wppa-box {
	' . ( wppa_opt( 'wppa_bwidth' ) > '0' ? 
		'border-style: solid; border-width:' . wppa_opt( 'wppa_bwidth' ) . 'px; ' 
		: '' ) . '
	' . ( wppa_opt( 'wppa_bradius' ) > '0' ? 
		'border-radius:' . wppa_opt( 'wppa_bradius' ) . 'px; -moz-border-radius:' . 
			wppa_opt( 'wppa_bradius' ) . 'px; -khtml-border-radius:' . 
			wppa_opt( 'wppa_bradius' ) . 'px; -webkit-border-radius:' . 
			wppa_opt( 'wppa_bradius' ) . 'px; ' : 
		'' ) . '
	' . ( wppa_opt( 'wppa_box_spacing' ) ? 
		'margin-bottom:' . wppa_opt( 'wppa_box_spacing' ) . 'px; ' : 
		'' ) . '
}';

	$content .= '
.wppa-mini-box {
	' . ( wppa_opt( 'wppa_bwidth' ) > '0' ? 
		'border-style: solid; border-width:' . floor( ( wppa_opt( 'wppa_bwidth' ) + 2 ) / 3 ) . 'px; ' : 
		'' ) . '
	' . ( wppa_opt( 'wppa_bradius' ) > '0' ? 
		'border-radius:' . floor( ( wppa_opt( 'wppa_bradius' ) + 2 ) / 3 ) . 'px; -moz-border-radius:' . 
			floor( ( wppa_opt( 'wppa_bradius' ) + 2 ) / 3 ) . 'px; -khtml-border-radius:' . 
			floor( ( wppa_opt( 'wppa_bradius' ) + 2 ) / 3 ) . 'px; -webkit-border-radius:' . 
			floor( ( wppa_opt( 'wppa_bradius' ) + 2 ) / 3 ) . 'px; ' : 
		'' ) . '
}';

	$content .= '
.wppa-cover-box {
	' . ( wppa_opt( 'wppa_cover_minheight' ) ?
		'min-height:' . wppa_opt( 'wppa_cover_minheight' ) . 'px; ' :
		'' ) . '
}';

	$content .= '
.wppa-cover-text-frame {
	' . ( wppa_opt( 'wppa_head_and_text_frame_height' ) ?
		'min-height:' . wppa_opt( 'wppa_head_and_text_frame_height' ) . 'px; ' :
		'' ) . '
}';

	$content .= '
.wppa-box-text {
	' . ( wppa_opt( 'wppa_fontcolor_box' ) ? 
		'color:' . wppa_opt( 'wppa_fontcolor_box' ) . '; ' : 
		'' ) . '
}
.wppa-box-text, .wppa-box-text-nocolor {
	' . ( wppa_opt( 'wppa_fontfamily_box' ) ? 
		'font-family:' . wppa_opt( 'wppa_fontfamily_box' ) . '; ' : 
		'' ) . '
	' . ( wppa_opt( 'wppa_fontsize_box' ) ? 
		'font-size:' . wppa_opt( 'wppa_fontsize_box' ) . 'px; ' : 
		'' ) . '
	' . ( wppa_opt( 'wppa_fontweight_box' ) ? 
		'font-weight:' . wppa_opt( 'wppa_fontweight_box' ) . '; ' : 
		'' ) . '
}';

	$content .= '
.wppa-thumb-text {
	' . ( wppa_opt( 'wppa_fontfamily_thumb' ) ? 
		'font-family:' . wppa_opt( 'wppa_fontfamily_thumb' ) . '; ' : 
		'' ) . '
	' . ( wppa_opt( 'wppa_fontsize_thumb' ) ? 
		'font-size:' . wppa_opt( 'wppa_fontsize_thumb' ) . 'px; line-height:' . 
			floor( wppa_opt( 'wppa_fontsize_thumb' ) * 1.29 ) . 'px; ' : 
		'' ) . '
	' . ( wppa_opt( 'wppa_fontcolor_thumb' ) ? 
		'color:' . wppa_opt( 'wppa_fontcolor_thumb' ) . '; ' : 
		'' ) . '
	' . ( wppa_opt( 'wppa_fontweight_thumb' ) ? 
		'font-weight:' . wppa_opt( 'wppa_fontweight_thumb' ) . '; ' : 
		'' ) . '
}';

	$content .= '
.wppa-comments {
	' . ( wppa_opt( 'wppa_bgcolor_com' ) ? 
		'background-color:' . wppa_opt( 'wppa_bgcolor_com' ) . '; ' : 
		'' ) . '
	' . ( wppa_opt( 'wppa_bcolor_com' ) ? 
		'border-color:' . wppa_opt( 'wppa_bcolor_com' ) . '; ' : 
		'' ) . '
}';

	$content .= '
.wppa-iptc {
	' . ( wppa_opt( 'wppa_bgcolor_iptc' ) ? 
		'background-color:' . wppa_opt( 'wppa_bgcolor_iptc' ) . '; ' : 
		'' ) . '
	' . ( wppa_opt( 'wppa_bcolor_iptc' ) ? 
		'border-color:' . wppa_opt( 'wppa_bcolor_iptc' ) . '; ' : 
		'' ) . '
}';

	$content .= '
.wppa-exif {
	' . ( wppa_opt( 'wppa_bgcolor_exif' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_exif' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_bcolor_exif' ) ? 'border-color:' . wppa_opt( 'wppa_bcolor_exif' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-share {
	' . ( wppa_opt( 'wppa_bgcolor_share' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_share' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_bcolor_share' ) ? 'border-color:' . wppa_opt( 'wppa_bcolor_share' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-name-desc {
	' . ( wppa_opt( 'wppa_bgcolor_namedesc' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_namedesc' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_bcolor_namedesc' ) ? 'border-color:' . wppa_opt( 'wppa_bcolor_namedesc' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-nav {
	' . ( wppa_opt( 'wppa_bgcolor_nav' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_nav' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_bcolor_nav' ) ? 'border-color:' . wppa_opt( 'wppa_bcolor_nav' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-nav-text {
	' . ( wppa_opt( 'wppa_fontfamily_nav' ) ? 'font-family:' . wppa_opt( 'wppa_fontfamily_nav' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_fontsize_nav' ) ? 'font-size:' . wppa_opt( 'wppa_fontsize_nav' ) . 'px; ' : '' ) . '
	' . ( wppa_opt( 'wppa_fontcolor_nav' ) ? 'color:' . wppa_opt( 'wppa_fontcolor_nav' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_fontweight_nav' ) ? 'font-weight:' . wppa_opt( 'wppa_fontweight_nav' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-even {
	' . ( wppa_opt( 'wppa_bgcolor_even' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_even' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_bcolor_even' ) ? 'border-color:' . wppa_opt( 'wppa_bcolor_even' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-alt {
	' . ( wppa_opt( 'wppa_bgcolor_alt' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_alt' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_bcolor_alt' ) ? 'border-color:' . wppa_opt( 'wppa_bcolor_alt' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-img {
	' . ( wppa_opt( 'wppa_bgcolor_img' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_img' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-title {
	' . ( wppa_opt( 'wppa_fontfamily_title' ) ? 'font-family:' . wppa_opt( 'wppa_fontfamily_title' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_fontsize_title' ) ? 'font-size:' . wppa_opt( 'wppa_fontsize_title' ) . 'px; ' : '' ) . '
	' . ( wppa_opt( 'wppa_fontcolor_title' ) ? 'color:' . wppa_opt( 'wppa_fontcolor_title' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_fontweight_title' ) ? 'font-weight:' . wppa_opt( 'wppa_fontweight_title' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-fulldesc {
	' . ( wppa_opt( 'wppa_fontfamily_fulldesc' ) ? 'font-family:' . wppa_opt( 'wppa_fontfamily_fulldesc' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_fontsize_fulldesc' ) ? 'font-size:' . wppa_opt( 'wppa_fontsize_fulldesc' ) . 'px; ' : '' ) . '
	' . ( wppa_opt( 'wppa_fontcolor_fulldesc' ) ? 'color:' . wppa_opt( 'wppa_fontcolor_fulldesc' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_fontweight_fulldesc' ) ? 'font-weight:' . wppa_opt( 'wppa_fontweight_fulldesc' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-fulltitle {
	' . ( wppa_opt( 'wppa_fontfamily_fulltitle' ) ? 'font-family:' . wppa_opt( 'wppa_fontfamily_fulltitle' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_fontsize_fulltitle' ) ? 'font-size:' . wppa_opt( 'wppa_fontsize_fulltitle' ) . 'px; ' : '' ) . '
	' . ( wppa_opt( 'wppa_fontcolor_fulltitle' ) ? 'color:' . wppa_opt( 'wppa_fontcolor_fulltitle' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_fontweight_fulltitle' ) ? 'font-weight:' . wppa_opt( 'wppa_fontweight_fulltitle' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-custom {
	' . ( wppa_opt( 'wppa_bgcolor_cus' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_cus' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_bcolor_cus' ) ? 'border-color:' . wppa_opt( 'wppa_bcolor_cus' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-upload {
	' . ( wppa_opt( 'wppa_bgcolor_upload' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_upload' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_bcolor_upload' ) ? 'border-color:' . wppa_opt( 'wppa_bcolor_upload' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-multitag {
	' . ( wppa_opt( 'wppa_bgcolor_multitag' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_multitag' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_bcolor_multitag' ) ? 'border-color:' . wppa_opt( 'wppa_bcolor_multitag' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-bestof {
	' . ( wppa_opt( 'wppa_bgcolor_bestof' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_bestof' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_bcolor_bestof' ) ? 'border-color:' . wppa_opt( 'wppa_bcolor_bestof' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-tagcloud {
	' . ( wppa_opt( 'wppa_bgcolor_tagcloud' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_tagcloud' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_bcolor_tagcloud' ) ? 'border-color:' . wppa_opt( 'wppa_bcolor_tagcloud' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-superview {
	' . ( wppa_opt( 'wppa_bgcolor_superview' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_superview' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_bcolor_superview' ) ? 'border-color:' . wppa_opt( 'wppa_bcolor_superview' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-search {
	' . ( wppa_opt( 'wppa_bgcolor_search' ) ? 'background-color:' . wppa_opt( 'wppa_bgcolor_search' ) . '; ' : '' ) . '
	' . ( wppa_opt( 'wppa_bcolor_search' ) ? 'border-color:' . wppa_opt( 'wppa_bcolor_search' ) . '; ' : '' ) . '
}';

	$content .= '
.wppa-arrow {
	' . ( wppa_opt( 'wppa_arrow_color' ) ? 'color:' . wppa_opt( 'wppa_arrow_color' ) . '; ' : '' ) . '
}';

	$content .= wppa_opt( 'wppa_custom_style' );

	// Open file
	$file = @ fopen ( WPPA_PATH . '/wppa-dynamic.css', 'wb' );
	if ( $file ) {
		// Minify
		$old_len = strlen( $content );
		$new_len = '0';
		while ( $old_len != $new_len ) {
			$content = str_replace( '  ', ' ', $content );
			$old_len = $new_len;
			$new_len = strlen( $content );
		}
		$content = str_replace( "\t", '', $content );
		$content = str_replace( "\n", '', $content );
		$content = str_replace( ' }', '}', $content );
		$content = str_replace( '} ', '}', $content );
		$content = str_replace( ' {', '{', $content );
		$content = str_replace( '{ ', '{', $content );
		$content = str_replace( ' ;', ';', $content );
		$content = str_replace( '; ', ';', $content );
		$content = str_replace( ' :', ':', $content );
		$content = str_replace( ': ', ':', $content );
		$content = str_replace( '}', "}\n", $content );
		$content = str_replace( '*/', "*/\n", $content );
		$content = str_replace( ",\n", ',', $content );
		// Write file
		fwrite ( $file, $header . $content );
		// Close file
		fclose ( $file );
		$wppa_dynamic_css_data = '';
	}
	else {
		$wppa_dynamic_css_data = 
'<style type="text/css">
/* Warning: file wppa-dynamic.css could not be created */
/* The content is therefor output here */

' . $content . '
</style>
';
	}
}

// get full img style
function wppa_get_fullimgstyle( $id ) {

	$temp = wppa_get_fullimgstyle_a( $id );
	
	if ( is_array( $temp ) ) {
		return $temp['style'];
	}
	else {
		return '';
	}
}

// get full img style - array output
function wppa_get_fullimgstyle_a( $id ) {
global $wppa;

	if ( ! is_numeric( $wppa['fullsize'] ) || $wppa['fullsize'] < '1' ) {
		$wppa['fullsize'] = wppa_opt( 'wppa_fullsize' );
	}

	$wppa['enlarge'] = wppa_switch( 'wppa_enlarge' );

	return wppa_get_imgstyle_a( $id, wppa_get_photo_path( $id ), $wppa['fullsize'], 'optional', 'fullsize' );
}

// Image style array output
function wppa_get_imgstyle_a( $id, $file, $xmax_size, $xvalign = '', $type = '' ) {
global $wppa;

	$result = Array( 
					'style' 		=> '', 
					'width' 		=> '', 
					'height' 		=> '', 
					'cursor' 		=> '', 
					'margin-top' 	=> '', 
					'margin-bottom' => '' 
					 );	// Init 
	
	wppa_cache_thumb( $id );
	
	if ( ! $id ) return $result;						// no image: no dimensions
	if ( $file == '' ) return $result;					// no image: no dimensions
	
	if ( ! wppa_is_video( $id ) && ! is_file( $file ) ) {
		wppa_dbg_msg( 'Please check file ' . $file . ' it is missing while expected. Id=' . $id, 'red' );
		return $result;									// no file: no dimensions ( 2.3.0 )
	}
	
	if ( strpos( $file, '/wppa/thumbs/' ) ) {
		$image_attr = wppa_get_imagexy( $id, 'thumb' );
	}
	else {
		$image_attr = wppa_get_imagexy( $id, 'photo' );
	}
//	if ( wppa_is_video( $id ) ) {
//		$image_attr = array( '0' => wppa_get_videox( $id ), '1' => wppa_get_videoy( $id ) );
//	}
//	else {
//		$image_attr = array( '0' => wppa_get_photox( $id ), '1' => wppa_get_photoy( $id ) );
//	}
	if ( 
			! $image_attr || 
			! isset( $image_attr['0'] ) || 
			! $image_attr['0'] || 
			! isset( $image_attr['1'] ) || 
			! $image_attr['1'] ) {
			
		// File is corrupt
		wppa_dbg_msg( 'Please check file ' . $file . ' it is corrupted. If it is a thumbnail image,' . 
			' regenerate them using Table VIII-A4 of the Photo Albums -> Settings admin page.', 
			'red' );
		return $result;
	}
	
	// Adjust for 'border' 
	if ( $type == 'fullsize' && ! $wppa['in_widget'] ) {
		switch ( wppa_opt( 'wppa_fullimage_border_width' ) ) {
			case '':
				$max_size = $xmax_size;
				break;
			case '0':
				$max_size = $xmax_size - '2';
				break;
			default:
				$max_size = $xmax_size - '2' - 2 * wppa_opt( 'wppa_fullimage_border_width' );
			}
	}
	else $max_size = $xmax_size;
	
	$ratioref = wppa_opt( 'wppa_maxheight' ) / wppa_opt( 'wppa_fullsize' );
	$max_height = round( $max_size * $ratioref );
	
	if ( $type == 'fullsize' ) {
		if ( $wppa['portrait_only'] ) {
			$width = $max_size;
			$height = round( $width * $image_attr[1] / $image_attr[0] );
		}
		else {
			if ( wppa_is_wider( $image_attr[0], $image_attr[1] ) ) {
				$width = $max_size;
				$height = round( $width * $image_attr[1] / $image_attr[0] );
			}
			else {
				$height = round( $ratioref * $max_size );
				$width = round( $height * $image_attr[0] / $image_attr[1] );
			}
			if ( $image_attr[0] < $width && $image_attr[1] < $height ) {
				if ( ! $wppa['enlarge'] ) {
					$width = $image_attr[0];
					$height = $image_attr[1];
				}
			}
		}
	}
	else {
		if ( $type == 'cover' && 
			wppa_switch( 'wppa_coversize_is_height' ) && 
			( $wppa['coverphoto_pos'] == 'top' || $wppa['coverphoto_pos'] == 'bottom' ) 
			 ) {
				$height = $max_size;
				$width = round( $max_size * $image_attr[0] / $image_attr[1] );
		}
		else {
			if ( wppa_is_landscape( $image_attr ) ) {
				$width = $max_size;
				$height = round( $max_size * $image_attr[1] / $image_attr[0] );
			}
			else {
				$height = $max_size;
				$width = round( $max_size * $image_attr[0] / $image_attr[1] );
			}
		}
	}
	
	switch ( $type ) {
		case 'cover':
			if ( wppa_opt( 'wppa_bcolor_img' ) != '' ) { 		// There is a border color given
				$result['style'] .= ' border: 1px solid ' . wppa_opt( 'wppa_bcolor_img' ) . ';';
			}
			else {											// No border color: no border
				$result['style'] .= ' border-width: 0px;';
			}
			$result['style'] .= ' width:' . $width . 'px; height:' . $height . 'px;';
			if ( wppa_switch( 'wppa_use_cover_opacity' ) && ! is_feed() ) {
				$opac = wppa_opt( 'wppa_cover_opacity' );
				$result['style'] .= ' opacity:' . $opac/100 . 
					'; filter:alpha( opacity=' . $opac . ' );';
			}
			if ( wppa_opt( 'wppa_coverimg_linktype' ) == 'lightbox' ) {
				$result['cursor'] = 
					' cursor:url( ' .wppa_get_imgdir() . wppa_opt( 'wppa_magnifier' ) . ' ),pointer;';
			}
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
			if ( $xvalign == 'optional' ) $valign = wppa_opt( 'wppa_valign' );
			else $valign = $xvalign;
			if ( $valign != 'default' ) {	// Center horizontally
				$delta = floor( ( $max_size - $width ) / 2 );
				if ( is_numeric( $valign ) ) $delta += $valign;
				if ( $delta < '0' ) {
					$delta = '0';
				}
				if ( $delta > '0' ) {
					$result['style'] .= ' margin-left:' . $delta . 
						'px; margin-right:' . $delta . 'px;';
				}
			} 
						
			switch ( $valign ) {
				case 'top':
					$delta = $max_size - $height;
					if ( $delta < '0' ) $delta = '0';
					$result['style'] .= ' margin-bottom: ' . $delta . 'px;';
					$result['margin-bottom'] = $delta;
					break;
				case 'center':
					$delta = round( ( $max_size - $height ) / 2 );
					if ( $delta < '0' ) $delta = '0';
					$result['style'] .= ' margin-top: ' . $delta . 
						'px; margin-bottom: ' . $delta . 'px;';
					$result['margin-top'] = $delta;
					$result['margin-bottom'] = $delta;
					break;
				case 'bottom':
					$delta = $max_size - $height;
					if ( $delta < '0' ) $delta = '0';
					$result['style'] .= ' margin-top: ' . $delta . 'px;';
					$result['margin-top'] = $delta;
					break;
				default:
					if ( is_numeric( $valign ) ) {
						$delta = $valign;
						$result['style'] .= ' margin-top: ' . $delta . 'px;';
						$result['style'] .= ' margin-bottom: ' . $delta . 'px;';
						$result['margin-top'] = $delta;
						$result['margin-bottom'] = $delta;
					}
			}
			if ( wppa_switch( 'wppa_use_thumb_opacity' ) && ! is_feed() ) {
				$opac = wppa_opt( 'wppa_thumb_opacity' );
				$result['style'] .= 
					' opacity:' . $opac/100 . '; filter:alpha( opacity=' . $opac . ' );';
			}
			
			// Cursor
			$linktyp = '';
			switch ( $type ) {
				case 'thumb':		// Normal
					$linktyp = wppa_opt( 'wppa_thumb_linktype' );
					break;
				case 'ttthumb':		// Topten	v
					$linktyp = wppa_opt( 'wppa_topten_widget_linktype' );
					break;
				case 'comthumb':	// Comment widget	v
					$linktyp = wppa_opt( 'wppa_comment_widget_linktype' );
					break;
				case 'fthumb':		// Filmthumb
					$linktyp = wppa_opt( 'wppa_film_linktype' );
					break;
				case 'twthumb':		// Thumbnail widget	v
					$linktyp = wppa_opt( 'wppa_thumbnail_widget_linktype' );
					break;
				case 'ltthumb':		// Lasten widget	v
					$linktyp = wppa_opt( 'wppa_lasten_widget_linktype' );
					break;
				case 'albthumb':	// Album widget
					$linktyp = wppa_opt( 'wppa_album_widget_linktype' );
			}
			if ( $linktyp == 'none' ) {
				$result['cursor'] = ' cursor:default;';
			}
			elseif ( $linktyp == 'lightbox' ) {
				$result['cursor'] = ' cursor:url(' . wppa_get_imgdir() . 
					wppa_opt( 'wppa_magnifier' ) . '),pointer;';
			}
			else {
				$result['cursor'] = ' cursor:pointer;';
			}
			
			break;
		case 'fullsize':
			if ( $wppa['auto_colwidth'] ) {
			
				// These sizes fit within the rectangle define by Table I-B1,2 
				// times 2 for responsive themes,
				// and are supplied for ver 4 browsers as they have undefined natural sizes. 
				$result['style'] .= ' max-width:' . ( $width * 2 ) . 'px;';		
				$result['style'] .= ' max-height:' . ( $height * 2 ) . 'px;';	
			}
			else {
			
				// These sizes fit within the rectangle define by Table I-B1,2
				// and are supplied for ver 4 browsers as they have undefined natural sizes. 
				$result['style'] .= ' max-width:' . $width . 'px;';		
				$result['style'] .= ' max-height:' . $height . 'px;';	

				$result['style'] .= ' width:' . $width . 'px;';
				$result['style'] .= ' height:' . $height . 'px;';
			}
			
			if ( $wppa['is_slideonly'] == '1' ) {
				if ( $wppa['ss_widget_valign'] != '' ) $valign = $wppa['ss_widget_valign'];
				else $valign = 'fit';
			}
			elseif ( $xvalign == 'optional' ) {
				$valign = wppa_opt( 'wppa_fullvalign' );
			}
			else {
				$valign = $xvalign;
			}
			
			// Margin
			if ( $valign != 'default' ) {
				$m_left 	= '0';
				$m_right 	= '0';
				$m_top 		= '0';
				$m_bottom 	= '0';
				
				// Center horizontally
				$delta = round( ( $max_size - $width ) / 2 );
				if ( $delta < '0' ) $delta = '0';
				if ( $wppa['auto_colwidth'] ) {
					$m_left 	= 'auto';
					$m_right 	= 'auto';
				}
				else {
					$m_left 	= $delta;
					$m_right 	= '0';
				}
				
				// Position vertically
				if ( $wppa['in_widget'] == 'ss' && $wppa['in_widget_frame_height'] > '0' ) {
					$max_height = $wppa['in_widget_frame_height'];
				}
				$delta = '0';
				if ( ! $wppa['auto_colwidth'] && ! wppa_page( 'oneofone' ) ) {
					switch ( $valign ) {
						case 'top':
						case 'fit':
							$delta = '0';
							break;
						case 'center':
							$delta = round( ( $max_height - $height ) / 2 );
							if ( $delta < '0' ) $delta = '0';
							break;
						case 'bottom':
							$delta = $max_height - $height;
							if ( $delta < '0' ) $delta = '0';
							break;
					}
				}
				$m_top = $delta;
				
				$result['style'] .= wppa_combine_style( 'margin', $m_top, $m_left, $m_right, $m_bottom );
			}
			
			// Border and padding
			if ( ! $wppa['in_widget'] ) switch ( wppa_opt( 'wppa_fullimage_border_width' ) ) {
				case '':
					break;
				case '0':
					$result['style'] .= ' border: 1px solid ' . wppa_opt( 'wppa_bcolor_fullimg' ) . ';';
					break;
				default:
					$result['style'] .= ' border: 1px solid ' . wppa_opt( 'wppa_bcolor_fullimg' ) . ';';
					$result['style'] .= ' background-color:' . wppa_opt( 'wppa_bgcolor_fullimg' ) . ';';
					$result['style'] .= ' padding:' . wppa_opt( 'wppa_fullimage_border_width' ) . 'px;';
					
					// If we do round corners...
					if ( wppa_opt( 'wppa_bradius' ) > '0' ) {	// then also here
						$result['style'] .= ' border-radius:' . 
							wppa_opt( 'wppa_fullimage_border_width' ) . 'px;';
					}
			}
			
			break;
		default:
			$wppa['out'] .=  'Error wrong "$type" argument: ' . $type . ' in wppa_get_imgstyle_a';
	}
	$result['width'] = $width;
	$result['height'] = $height;
	return $result;
}

// This function is used to either provide inline styles or hide them, 
// dependant of the wppa_inline_css switch. 
// Styles should be passed as is . They must be independatnt of settings, 
// and appear in the standard wppa-styles.css file.
function __wis( $style ) {

	if ( ! wppa_switch( 'wppa_inline_css' ) ) {
		return '';	// No inline styles
	}
	else {
		return $style;
	}
}

// This function returns styles dependant of settings or hides them, 
// dependant of the wppa_inline_css switch. 
// The class is passed and the corresponding inline styles are returned. 
// Any updates to this routine must also be implemented in wppa_create_wppa_dynamic_css().
function __wcs( $class ) {
global $wppa;

	if ( ! wppa_switch( 'wppa_inline_css' ) ) {
		return '';	// No inline styles
	}
	
	$opt = '';
	$result = '';
	
	switch ( $class ) {
		case 'wppa-box':
			$opt = wppa_opt( 'wppa_bwidth' );
			if ( $opt > '0' ) {
				$result .= 'border-style: solid; border-width:' . $opt . 'px; ';
			}
			$opt = wppa_opt( 'wppa_bradius' );
			if ( $opt > '0' ) {
				$result .= 'border-radius:' . $opt . 'px; ';
				$result .= '-moz-border-radius:' . $opt . 'px; ';
				$result .= '-khtml-border-radius:' . $opt . 'px; ';
				$result .= '-webkit-border-radius:' . $opt . 'px; ';
			}
			$opt = wppa_opt( 'wppa_box_spacing' );
			if ( $opt != '' ) {
				$result .= 'margin-bottom:' . $opt . 'px; ';
			}
			break;
		case 'wppa-mini-box':
			$opt = wppa_opt( 'wppa_bwidth' );
			if ( $opt > '0' ) {
				$opt = floor( ( $opt + 2 ) / 3 );
				$result .= 'border-style: solid; border-width:' . $opt . 'px; ';
			}
			$opt = wppa_opt( 'wppa_bradius' );
			if ( $opt > '0' ) {
				$opt = floor( ( $opt + 2 ) / 3 );
				$result .= 'border-radius:' . $opt . 'px; ';
				$result .= '-moz-border-radius:' . $opt . 'px; ';
				$result .= '-khtml-border-radius:' . $opt . 'px; ';
				$result .= '-webkit-border-radius:' . $opt . 'px; ';
			}
			break;
		case 'wppa-cover-box':
			$opt = wppa_opt( 'wppa_cover_minheight' );
			if ( $opt ) $result .= 'min-height:' . $opt . 'px; ';
			break;
		case 'wppa-cover-text-frame':
			$opt = wppa_opt( 'wppa_head_and_text_frame_height' );
			if ( $opt ) $result .= 'min-height:' . $opt . 'px; ';
			break;
		case 'wppa-thumb-text':
			$opt = wppa_opt( 'wppa_fontfamily_thumb' );
			if ( $opt ) $result .= 'font-family:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_fontsize_thumb' );
			if ( $opt ) {
				$ls = floor( $opt * 1.29 );
				$result .= 'font-size:' . $opt . 'px; line-height:' . $ls . 'px; ';
			}
			$opt = wppa_opt( 'wppa_fontcolor_thumb' );
			if ( $opt ) $result .= 'color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_fontweight_thumb' );
			if ( $opt ) $result .= 'font-weight:' . $opt . '; ';
			break;
		case 'wppa-box-text':
			$opt = wppa_opt( 'wppa_fontcolor_box' );
			if ( $opt ) $result .= 'color:' . $opt . '; ';
		case 'wppa-box-text-nocolor':
			$opt = wppa_opt( 'wppa_fontfamily_box' );
			if ( $opt ) $result .= 'font-family:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_fontsize_box' );
			if ( $opt ) $result .= 'font-size:' . $opt . 'px; ';
			$opt = wppa_opt( 'wppa_fontweight_box' );
			if ( $opt ) $result .= 'font-weight:' . $opt . '; ';
			break;
		case 'wppa-comments':
			$opt = wppa_opt( 'wppa_bgcolor_com' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_com' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-iptc':
			$opt = wppa_opt( 'wppa_bgcolor_iptc' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_iptc' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-exif':
			$opt = wppa_opt( 'wppa_bgcolor_exif' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_exif' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-share':
			$opt = wppa_opt( 'wppa_bgcolor_share' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_share' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-name-desc':
			$opt = wppa_opt( 'wppa_bgcolor_namedesc' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_namedesc' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-nav':
			$opt = wppa_opt( 'wppa_bgcolor_nav' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_nav' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-nav-text':
			$opt = wppa_opt( 'wppa_fontfamily_nav' );
			if ( $opt ) $result .= 'font-family:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_fontsize_nav' );
			if ( $opt ) $result .= 'font-size:' . $opt . 'px; ';
			$opt = wppa_opt( 'wppa_fontcolor_nav' );
			if ( $opt ) $result .= 'color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_fontweight_nav' );
			if ( $opt ) $result .= 'font-weight:' . $opt . '; ';
			break;
		case 'wppa-even':
			$opt = wppa_opt( 'wppa_bgcolor_even' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_even' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-alt':
			$opt = wppa_opt( 'wppa_bgcolor_alt' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_alt' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-img':
			$opt = wppa_opt( 'wppa_bgcolor_img' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			break;
		case 'wppa-title':
			$opt = wppa_opt( 'wppa_fontfamily_title' );
			if ( $opt ) $result .= 'font-family:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_fontsize_title' );
			if ( $opt ) $result .= 'font-size:' . $opt . 'px; ';
			$opt = wppa_opt( 'wppa_fontcolor_title' );
			if ( $opt ) $result .= 'color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_fontweight_title' );
			if ( $opt ) $result .= 'font-weight:' . $opt . '; ';
			break;
		case 'wppa-fulldesc':
			$opt = wppa_opt( 'wppa_fontfamily_fulldesc' );
			if ( $opt ) $result .= 'font-family:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_fontsize_fulldesc' );
			if ( $opt ) $result .= 'font-size:' . $opt . 'px; ';
			$opt = wppa_opt( 'wppa_fontcolor_fulldesc' );
			if ( $opt ) $result .= 'color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_fontweight_fulldesc' );
			if ( $opt ) $result .= 'font-weight:' . $opt . '; ';
			break;
		case 'wppa-fulltitle':
			$opt = wppa_opt( 'wppa_fontfamily_fulltitle' );
			if ( $opt ) $result .= 'font-family:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_fontsize_fulltitle' );
			if ( $opt ) $result .= 'font-size:' . $opt . 'px; ';
			$opt = wppa_opt( 'wppa_fontcolor_fulltitle' );
			if ( $opt ) $result .= 'color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_fontweight_fulltitle' );
			if ( $opt ) $result .= 'font-weight:' . $opt . '; ';
			break;
		case 'wppa-custom':
			$opt = wppa_opt( 'wppa_bgcolor_cus' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_cus' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-upload':
			$opt = wppa_opt( 'wppa_bgcolor_upload' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_upload' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-multitag':
			$opt = wppa_opt( 'wppa_bgcolor_multitag' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_multitag' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-bestof':
			$opt = wppa_opt( 'wppa_bgcolor_bestof' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_bestof' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-tagcloud':
			$opt = wppa_opt( 'wppa_bgcolor_tagcloud' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_tagcloud' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-superview':
			$opt = wppa_opt( 'wppa_bgcolor_superview' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_superview' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-search':
			$opt = wppa_opt( 'wppa_bgcolor_search' );
			if ( $opt ) $result .= 'background-color:' . $opt . '; ';
			$opt = wppa_opt( 'wppa_bcolor_search' );
			if ( $opt ) $result .= 'border-color:' . $opt . '; ';
			break;
		case 'wppa-black':
//			$opt = wppa_opt( 'wppa_black' );
//			if ( $opt ) $result .= 'color:' . $opt . '; ';
//			break;
			break;
		case 'wppa-arrow':
			$opt = wppa_opt( 'wppa_arrow_color' );
			if ( $opt ) $result .= 'color:' . $opt . '; ';
			break;
		case 'wppa-td';
			$result .= 'padding: 3px 2px 3px 0; border: 0';
			break;
		default:
			wppa_dbg_msg( 'Unexpected error in __wcs, unknown class: ' . $class, 'red' );
			wppa_log( 'Err', 'Unexpected error in __wcs, unknown class: ' . $class );
	}
	return $result;
}
