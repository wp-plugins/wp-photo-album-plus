<?php
/* wppa-watermark.php
*
* Functions used for the application of watermarks
* version 5.3.0
*
*/

function wppa_create_textual_watermark_file( $arg, $pos = '', $xfont_name = '', $xtype = '' ) {
global $thumb;
global $wppa_opt;

	// We may have been called from wppa_get_water_file_and_pos() just to find the settings
	// In this case there may be no reliable data in $thumb, we may return, the file is not needed.
	if ( ! isset( $thumb['id'] ) && ! $xfont_name ) return false;

	// First cleanup a bit
	wppa_delete_obsolete_tempfiles();
	
	// Check the font
	if ( $arg == '---preview---' ) {
		$preview = true;
		$font_name 		= $xfont_name;
		$font_type 		= $font_name == 'system' ? 'system' : 'truetype';
		$font_size 		= $font_type == 'system' ? 5 : 12;
		$padding   		= 2;
		$linespacing 	= 2;
	}
	else {
		$preview = false;
		$font_name 		= $wppa_opt['wppa_textual_watermark_font'];
		$font_type 		= $font_name == 'system' ? 'system' : 'truetype';
		$font_size 		= $font_type == 'system' ? 5 : $wppa_opt['wppa_textual_watermark_size'];
		$padding   		= 5;
		$linespacing 	= floor( $font_size / 4 );
	}
	$font_file 		= $font_type == 'system' ? '' : WPPA_UPLOAD_PATH.'/fonts/'.$font_name.'.ttf';

	// Preprocess the text
	$text = '';
	switch ( $arg ) {
		case '---preview---':
			if ( $xtype ) {
				$text = strtoupper(substr($xtype,0,1)).strtolower(substr($xtype,1));
			}
			else {
				$text = strtoupper(substr($font_name,0,1)).strtolower(substr($font_name,1));
			}
			break;
		case '---filename---':
			$text = trim( $thumb['filename'] );
			break;
		case '---name---':
			$text = trim( wppa_get_photo_name( $thumb['id'] ) );
			break;
		case '---description---':
			$text = trim( strip_tags( wppa_get_photo_desc( $thumb['id'] ) ) );
			break;
		case '---predef---':
			$text = $wppa_opt['wppa_textual_watermark_text'];
			if ( $font_type != 'system' ) {
				$text = str_replace( '(c)', '&copy;', $text );
				$text = str_replace( '(R)', '&reg;', $text );
			}
			$text = html_entity_decode( $text );
			$text = str_replace( 'w#site', get_bloginfo('url'), $text );
			$text = str_replace( 'w#owner', $thumb['owner'], $text );
			$text = str_replace( 'w#name', wppa_get_photo_name( $thumb['id'] ), $text );
			$text = str_replace( 'w#filename', $thumb['filename'], $text );
			$text = trim( $text );
			break;
			
		default:
			wppa_log( 'Error', 'Unimplemented arg '.$arg.' in wppa_create_textual_watermark_file()' );
			return false;
	}

	// Any text anyway?
	if ( ! strlen( $text ) ) {
		wppa_log( 'Error', 'No text for textual watermark. photo='.$thumb['id'] );
		return false;		// No text -> no watermark
	}
	
	// Split text on linebreaks
	$text 	= str_replace( "\n", '\n', $text );
	$lines 	= explode( '\n', $text );
	
	// Find pixel linelengths
	foreach ( array_keys( $lines ) as $key ) {
		$lines[$key] = trim( $lines[$key] );
		if ( $font_type == 'system' ) {
			$lengths[$key] = strlen( $lines[$key] ) * imagefontwidth( 5 );
		}
		else {
			$temp = imagettfbbox ( $font_size , 0.0 , $font_file , $lines[$key] );
			$lengths[$key] = $temp[2] - $temp[0];
		}
	}
	$maxlen = wppa_array_max( $lengths );

	// Find image width
	if ( $preview ) {
		$image_width 	= 200;
		$image_height 	= 50;
	}
	else {
		$temp = getimagesize( wppa_get_photo_path( $thumb['id'] ) );
		if ( ! is_array( $temp ) ) {
			wppa_log( 'Error', 'Trying to apply a waterark on a non image file. Id = '.$thumb['id'] );
			return false;	// not an image
		}
		$image_width 	= $temp[0];
		$image_height 	= $temp[1];
	}

	// Find canvas size
	$nlines 	= count( $lines );
	if ( $font_type == 'system' ) {
		$line_height 	= imagefontheight( 5 );
	}
	else {
		$temp = imagettfbbox ( $font_size , 0.0 , $font_file , $lines[0] );
		$line_height = $temp[3] - $temp[7];
	}
	$canvas_width 	= wppa_array_max( $lengths ) + 4 * $padding;
	$canvas_height 	= ( $line_height + $linespacing ) * count( $lines ) + $linespacing + 2 * $padding;

	// Does it fit?
	if ( $canvas_width > $image_width ) {
		wppa_log( 'Error', 'Trying to apply a waterark that is too wide for the image. Id = '.$thumb['id'] );
		return false;	// not an image
	}
	if ( $canvas_height > $image_height ) {
		wppa_log( 'Error', 'Trying to apply a waterark that is too high for the image. Id = '.$thumb['id'] );
		return false;	// not an image
	}

	// Create canvas
	$canvas 	= imagecreatetruecolor( $canvas_width, $canvas_height );
	$bgcolor 	= imagecolorallocatealpha( $canvas, 0, 0, 0, 127 );	// Transparent
	$white 		= imagecolorallocate( $canvas, 255, 255, 255 );
	$black 		= imagecolorallocate( $canvas, 0, 0, 0 );

	if ( $preview ) {
		if ( $xtype ) {
			$filename = WPPA_UPLOAD_PATH.'/temp/wmf'.$xtype.'.png';
		}
		else {
			$filename = WPPA_UPLOAD_PATH.'/temp/wmf'.$font_name.'.png';
		}
	}
	else {
		$filename 	= WPPA_UPLOAD_PATH.'/temp/wmf'.$thumb['id'].'.png';
	}
	imagefill( $canvas, 0, 0, $bgcolor );

	// Define the text colors
	$type 		= $xtype ? $xtype : $wppa_opt['wppa_textual_watermark_type'];
	switch ( $type ) {
		case 'tvstyle':
		case 'whiteonblack':
			$fg = $white;
			$bg = $black;
			break;
		case 'utopia':
		case 'blackonwhite':
			$fg = $black;
			$bg = $white;
			break;
		case 'white':
			$fg = $white;
			$bg = $bgcolor;
			break;
		case 'black':
			$fg = $black;
			$bg = $bgcolor;
			break;
	}

	// Plot the text
	foreach ( array_keys( $lines ) as $lineno ) {
		if ( strpos( $pos, 'lft' ) !== false ) $indent = 0;
		elseif ( strpos( $pos, 'rht' ) !== false ) $indent = $maxlen - $lengths[$lineno];
		else $indent = floor( ( $maxlen - $lengths[$lineno] ) / 2 );
		switch ( $type ) {
			case 'tvstyle':
			case 'utopia':
				for ( $i=-1; $i<=1; $i++ ) {
					for ( $j=-1; $j<=1; $j++ ) {
						if ( $font_type == 'system' ) {
							imagestring( $canvas, $font_size, 2 * $padding + $i + $indent, $padding + $lineno * ( $line_height + $linespacing ) + $j,  $lines[$lineno], $bg );
						}
						else {
							imagettftext ( $canvas, $font_size, 0, 2 * $padding + $i + $indent, $padding + ( $lineno + 1 ) * $line_height + $lineno * $linespacing + $j, $bg, $font_file, $lines[$lineno] );
						}
					}
				}
				if ( $font_type == 'system' ) {
					imagestring( $canvas, $font_size, 2 * $padding + $indent, $padding + $lineno * ( $line_height + $linespacing ),  $lines[$lineno], $fg );
				}
				else {
					imagettftext ( $canvas, $font_size, 0, 2 * $padding + $indent, $padding + ( $lineno + 1 ) * $line_height + $lineno * $linespacing, $fg, $font_file, $lines[$lineno] );
				}
				break;
			case 'blackonwhite':
			case 'whiteonblack':
			case 'white':
			case 'black':
				imagefilledrectangle( $canvas, $padding + $indent, $lineno * ( $line_height + $linespacing ) + $padding, 3 * $padding + $indent + $lengths[$lineno], ( $lineno + 1 ) * ( $line_height + $linespacing ) + $padding + $linespacing, $bg );
				if ( $font_type == 'system' ) {
					imagestring( $canvas, $font_size, 2 * $padding + $indent, $padding + $lineno * ( $line_height + $linespacing ),  $lines[$lineno], $fg );
				}
				else {
					imagettftext ( $canvas, $font_size, 0, 2 * $padding + $indent, $padding + ( $lineno + 1 ) * $line_height + $lineno * $linespacing, $fg, $font_file, $lines[$lineno] );
				}
				break;
		}
	}
	imagesavealpha( $canvas, true);
	imagepng( $canvas, $filename );
	imagedestroy( $canvas );
	if ( $preview ) {
		$url = str_replace( WPPA_UPLOAD_PATH, WPPA_UPLOAD_URL, $filename );
		return $url;
	}
	else {
		return $filename;
	}
}

function wppa_array_max( $array ) {
	if ( ! is_array( $array ) ) return $array;
	$result = 0;
	foreach ( $array as $item ) if ( $item > $result ) $result = $item;
	return $result;
}

function wppa_get_water_file_and_pos() {
global $wppa_opt;

	$result['file'] = $wppa_opt['wppa_watermark_file'];	// default
	$result['pos'] = $wppa_opt['wppa_watermark_pos'];	// default

	$user = wppa_get_user();
	
	if ( get_option('wppa_watermark_user') == 'yes' || current_user_can('wppa_settings') ) {									// user overrule?
		if ( isset($_POST['wppa-watermark-file'] ) ) {
			$result['file'] = $_POST['wppa-watermark-file'];
			update_option('wppa_watermark_file_' . $user, $_POST['wppa-watermark-file']);
		}
		elseif ( get_option('wppa_watermark_file_' . $user, 'nil') != 'nil' ) {
			$result['file'] = get_option('wppa_watermark_file_' . $user);
		}
		if ( isset($_POST['wppa-watermark-pos'] ) ) {
			$result['pos'] = $_POST['wppa-watermark-pos'];
			update_option('wppa_watermark_pos_' . $user, $_POST['wppa-watermark-pos']);
		}
		elseif ( get_option('wppa_watermark_pos_' . $user, 'nil') != 'nil' ) {
			$result['pos'] = get_option('wppa_watermark_pos_' . $user);
		}
	}
	$result['select'] = $result['file'];

	if ( substr( $result['file'], 0, 3 ) == '---' && $result['file'] != '--- none ---' ) {			// Special identifier, not a file
		$result['file'] = wppa_create_textual_watermark_file( $result['file'], $result['pos'] );
	}
	else {
		$result['file'] = WPPA_UPLOAD_PATH . '/watermarks/' . $result['file'];
	}
	return $result;
}

	
function wppa_add_watermark($file) {
global $wppa_opt;

	// Init
	if ( get_option('wppa_watermark_on') != 'yes' ) return false;	// Watermarks off
	
	// Find the watermark file and location
	$temp = wppa_get_water_file_and_pos();
	$waterfile = $temp['file'];
	if ( ! $waterfile ) return false;					// an error has occurred
	
	$waterpos = $temp['pos'];										// default
	
	if ( basename($waterfile) == '--- none ---' ) {
		return false;	// No watermark this time
	}
	// Open the watermark file
	$watersize = @getimagesize($waterfile);
	if ( !is_array($watersize) ) return false;	// Not a valid picture file
	$waterimage = imagecreatefrompng($waterfile);
	if ( empty( $waterimage ) or ( !$waterimage ) ) {
		wppa_dbg_msg('Watermark file '.$waterfile.' not found or corrupt');
		return false;			// No image
	}
	imagealphablending($waterimage, false);
	imagesavealpha($waterimage, true);

		
	// Open the photo file
	$photosize = getimagesize($file);
	if ( !is_array($photosize) ) {
		return false;	// Not a valid photo
	}
	switch ($photosize[2]) {
		case 1: $tempimage = imagecreatefromgif($file);
			$photoimage = imagecreatetruecolor($photosize[0], $photosize[1]);
			imagecopy($photoimage, $tempimage, 0, 0, 0, 0, $photosize[0], $photosize[1]);
			break;
		case 2: $photoimage = imagecreatefromjpeg($file);
			break;
		case 3: $photoimage = imagecreatefrompng($file);
			break;
	}
	if ( empty( $photoimage ) or ( !$photoimage ) ) return false; 			// No image

	$ps_x = $photosize[0];
	$ps_y = $photosize[1];
	$ws_x = $watersize[0];
	$ws_y = $watersize[1];
	$src_x = 0;
	$src_y = 0;
	if ( $ws_x > $ps_x ) {
		$src_x = ($ws_x - $ps_x) / 2;
		$ws_x = $ps_x;
	}		
	if ( $ws_y > $ps_y ) {
		$src_y = ($ws_y - $ps_y) / 2;
		$ws_y = $ps_y;
	}
	
	$loy = substr( $waterpos, 0, 3);
	switch($loy) {
		case 'top': $dest_y = 0;
			break;
		case 'cen': $dest_y = ( $ps_y - $ws_y ) / 2;
			break;
		case 'bot': $dest_y = $ps_y - $ws_y;
			break;
		default: $dest_y = 0; 	// should never get here
	}
	$lox = substr( $waterpos, 3);
	switch($lox) {
		case 'lft': $dest_x = 0;
			break;
		case 'cen': $dest_x = ( $ps_x - $ws_x ) / 2;
			break;
		case 'rht': $dest_x = $ps_x - $ws_x;
			break;
		default: $dest_x = 0; 	// should never get here
	}

	$opacity = strpos( $waterfile, '/temp/' ) === false ? intval( $wppa_opt['wppa_watermark_opacity'] ) : intval( $wppa_opt['wppa_watermark_opacity_text'] );
	wppa_imagecopymerge_alpha( $photoimage , $waterimage , $dest_x, $dest_y, $src_x, $src_y, $ws_x, $ws_y, $opacity );

	// Save the result
	switch ($photosize[2]) {
		case 1: imagegif($photoimage, $file);
			break;
		case 2: imagejpeg($photoimage, $file, $wppa_opt['wppa_jpeg_quality']);
			break;
		case 3: imagepng($photoimage, $file, 7);
			break;
	}

	// Cleanup
	imagedestroy($photoimage);
	imagedestroy($waterimage);

	return true;
}


/**
 * PNG ALPHA CHANNEL SUPPORT for imagecopymerge();
 * This is a function like imagecopymerge but it handle alpha channel well!!!
 **/

// A fix to get a function like imagecopymerge WITH ALPHA SUPPORT
// Main script by aiden dot mail at freemail dot hu
// Transformed to imagecopymerge_alpha() by rodrigo dot polo at gmail dot com
function wppa_imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
    if(!isset($pct)){
        return false;
    }
    $pct /= 100;
    // Get image width and height
    $w = imagesx( $src_im );
    $h = imagesy( $src_im );
    // Turn alpha blending off
    imagealphablending( $src_im, false );
    // Find the most opaque pixel in the image (the one with the smallest alpha value)
    $minalpha = 127;
    for( $x = 0; $x < $w; $x++ )
    for( $y = 0; $y < $h; $y++ ){
        $alpha = ( imagecolorat( $src_im, $x, $y ) >> 24 ) & 0xFF;
        if( $alpha < $minalpha ){
            $minalpha = $alpha;
        }
    }
    //loop through image pixels and modify alpha for each
    for( $x = 0; $x < $w; $x++ ){
        for( $y = 0; $y < $h; $y++ ){
            //get current alpha value (represents the TANSPARENCY!)
            $colorxy = imagecolorat( $src_im, $x, $y );
            $alpha = ( $colorxy >> 24 ) & 0xFF;
            //calculate new alpha
            if( $minalpha !== 127 ){
                $alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha );
            } else {
                $alpha += 127 * $pct;
            }
            //get the color index with new alpha
            $alphacolorxy = imagecolorallocatealpha( $src_im, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
            //set pixel with the new color + opacity
            if( !imagesetpixel( $src_im, $x, $y, $alphacolorxy ) ){
                return false;
            }
        }
    }
    // The image copy
    imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
}

function wppa_watermark_file_select($default = false) {
global $wppa_opt;

	// Init
	$result = '';
	$user = wppa_get_user();
	
	// See what's in there
	$paths = WPPA_UPLOAD_PATH . '/watermarks/*.png';
	$files = glob($paths);
	
	// Find current selection
	$select = $wppa_opt['wppa_watermark_file'];	// default
	if ( !$default && ( get_option('wppa_watermark_user') == 'yes' || current_user_can('wppa_settings') ) && get_option('wppa_watermark_file_' . $user, 'nil') !== 'nil' ) {
		$select = get_option('wppa_watermark_file_' . $user);
	}
	
	// Produce the html
	$result .= '<option value="--- none ---">'.__('--- none ---', 'wppa').'</option>';
	if ( $files ) foreach ( $files as $file ) {
		$sel = $select == basename($file) ? 'selected="selected"' : '';
		$result .= '<option value="'.basename($file).'" '.$sel.'>'.basename($file).'</option>';
	}
	
	// Text based watermarks
	$sel = $select == '---name---' ? 'selected="selected"' : '';
	$result .= '<option value="---name---" '.$sel.'>'.__('--- text: name ---', 'wppa').'</option>';
	$sel = $select == '---filename---' ? 'selected="selected"' : '';
	$result .= '<option value="---filename---" '.$sel.'>'.__('--- text: filename ---', 'wppa').'</option>';
	$sel = $select == '---description---' ? 'selected="selected"' : '';
	$result .= '<option value="---description---" '.$sel.'>'.__('--- text: description ---', 'wppa').'</option>';
	$sel = $select == '---predef---' ? 'selected="selected"' : '';
	$result .= '<option value="---predef---" '.$sel.'>'.__('--- text: pre-defined ---', 'wppa').'</option>';
	
	return $result;
}

function wppa_watermark_pos_select($default = false) {
global $wppa_opt;

	// Init
	$user = wppa_get_user();
	$result = '';
	$opt = array(	__('top - left', 'wppa'), __('top - center', 'wppa'), __('top - right', 'wppa'), 
					__('center - left', 'wppa'), __('center - center', 'wppa'), __('center - right', 'wppa'), 
					__('bottom - left', 'wppa'), __('bottom - center', 'wppa'), __('bottom - right', 'wppa'), );
	$val = array(	'toplft', 'topcen', 'toprht',
					'cenlft', 'cencen', 'cenrht',
					'botlft', 'botcen', 'botrht', );
	$idx = 0;

	// Find current selection
	$select = $wppa_opt['wppa_watermark_pos'];	// default
	if ( !$default && ( get_option('wppa_watermark_user') == 'yes' || current_user_can('wppa_settings') ) && get_option('wppa_watermark_pos_' . $user, 'nil') !== 'nil' ) {
		$select = get_option('wppa_watermark_pos_' . $user);
	}
	
	// Produce the html
	while ($idx < 9) {
		$sel = $select == $val[$idx] ? 'selected="selected"' : '';
		$result .= '<option value="'.$val[$idx].'" '.$sel.'>'.$opt[$idx].'</option>';
		$idx++;
	}
	
	return $result;
}
