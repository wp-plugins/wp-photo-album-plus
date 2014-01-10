<?php
/* wppa-styles.php
* Package: wp-photo-album-plus
*
* Various style computation routines
* Version 5.1.16
*
*/

if ( ! defined( 'ABSPATH' ) )
    die( "Can't load this file directly" );

// get full img style
function wppa_get_fullimgstyle($id = '') {
	$temp = wppa_get_fullimgstyle_a($id);
	if ( is_array($temp) ) return $temp['style'];
	else return '';
}

// get full img style - array output
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

// Image style
function wppa_get_imgstyle($file, $max_size, $xvalign = '', $type = '') {
	$result = wppa_get_imgstyle_a($file, $max_size, $xvalign, $type);
	return $result['style'];
}
// Image style array output
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
				$result['style'] .= ' max-width:' . ($width*2) . 'px;';		// These sizes fit within the rectangle define by Table I-B1,2 times 2 for responsive themes
				$result['style'] .= ' max-height:' . ($height*2) . 'px;';	// and are supplied for ver 4 browsers as they have undifined natural sizes
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
		case 'wppa-multitag':
			$opt = $wppa_opt['wppa_bgcolor_multitag'];
			if ($opt) $result .= 'background-color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_bcolor_multitag'];
			if ($opt) $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-tagcloud':
			$opt = $wppa_opt['wppa_bgcolor_tagcloud'];
			if ($opt) $result .= 'background-color:'.$opt.'; ';
			$opt = $wppa_opt['wppa_bcolor_tagcloud'];
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
