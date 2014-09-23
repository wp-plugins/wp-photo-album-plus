<?php
/* wppa-filter.php
* Package: wp-photo-album-plus
*
* get the albums via filter
* version 5.4.10
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

add_action('init', 'wppa_do_filter');

function wppa_do_filter() {
global $wppa_opt;
	add_filter( 'the_content', 'wppa_albums_filter', $wppa_opt['wppa_filter_priority'] );
	add_filter( 'the_content', 'wppa_add_shortcode_to_post' );
}

function wppa_add_shortcode_to_post($post) {
global $wppa_opt;
global $wppa;

	$new_post = $post;
	if ( ! $wppa['ajax'] && wppa_switch('wppa_add_shortcode_to_post') ) {
		$id = get_the_ID();
		$p = get_post($id, ARRAY_A);
		if ( $p['post_type'] == 'post' ) $new_post .= $wppa_opt['wppa_shortcode_to_add'];
	}
	return $new_post;
}

function wppa_albums_filter($post) {
global $wppa;

	$post_old = $post;
	$post_new = '';

	if ( strpos($post_old, '%%wppa%%') !== false ) {					// Yes, there is something to do here
		if ($wppa['debug']) wppa_dbg_msg('%%wppa%% found');				// Issue diagnostic message
		$wppa['occur'] = '0';											// Init this occurance
		$wppa['fullsize'] = '';											// Reset at each post
		$wppa_pos = strpos($post_old, '%%wppa%%');						// Where in the post is the invocation
		if ($wppa['debug']) wppa_dbg_msg('Text: '.htmlspecialchars(substr($post_old, $wppa_pos, 32)));
		while ($wppa_pos !== false) {
		
			$text_chunk = substr($post_old, 0, $wppa_pos);
			$post_new .= wppa_force_balance_pee($text_chunk);			// Copy BEFORE part to new post
			
			$post_old = substr($post_old, $wppa_pos);					// Shift BEFORE part out of old post
			$post_old = substr($post_old, 8);							// Shift %%wppa%% out of old post

			$album_pos = strpos($post_old, '%%album=');					// Is there an album given?
			$cover_pos = strpos($post_old, '%%cover=');					// Is there a cover given?
			$slide_pos = strpos($post_old, '%%slide=');					// Is there a slidealbum given?
			$slidef_pos = strpos($post_old, '%%slidef=');				// Slide with explixit filmstrip
			$slideonly_pos = strpos($post_old, '%%slideonly=');			// Is there a slideonly album given?
			$slideonlyf_pos = strpos($post_old, '%%slideonlyf=');		// Slideonly with explixit filmstrip
			$photo_pos = strpos($post_old, '%%photo=');					// Is there a photo id given?
			$mphoto_pos = strpos($post_old, '%%mphoto=');				// Single photo with caption like normal media photo
			$slphoto_pos = strpos($post_old, '%%slphoto=');				// Single photo like slideshow
			$size_pos = strpos($post_old, '%%size=');					// Is there a size given?
			$align_pos = strpos($post_old, '%%align=');					// Is there an align given?
			
			$wppa_pos = strpos($post_old, '%%wppa%%');					// Is there another occurrence?
			// Invalidate positions if they belong to a later occurance
			if (is_numeric($wppa_pos)) {								// Yes there is another occurance
				if (is_numeric($album_pos) && $album_pos > $wppa_pos) $album_pos = 'nil';
				if (is_numeric($cover_pos) && $cover_pos > $wppa_pos) $cover_pos = 'nil';
				if (is_numeric($slide_pos) && $slide_pos > $wppa_pos) $slide_pos = 'nil';
				if (is_numeric($slidef_pos) && $slidef_pos > $wppa_pos) $slidef_pos = 'nil';
				if (is_numeric($slideonly_pos) && $slideonly_pos > $wppa_pos) $slideonly_pos = 'nil';
				if (is_numeric($slideonlyf_pos) && $slideonlyf_pos > $wppa_pos) $slideonlyf_pos = 'nil';
				if (is_numeric($photo_pos) && $photo_pos > $wppa_pos) $photo_pos = 'nil';
				if (is_numeric($mphoto_pos) && $mphoto_pos > $wppa_pos) $mphoto_pos = 'nil';
				if (is_numeric($slphoto_pos) && $slphoto_pos > $wppa_pos) $slphoto_pos = 'nil';
				if (is_numeric($size_pos) && $size_pos > $wppa_pos) $size_pos = 'nil';
				if (is_numeric($align_pos) && $align_pos > $wppa_pos) $align_pos = 'nil';
			}
			// set defaults
			$wppa['start_album'] = '';
			$wppa['is_cover'] = '0';
			$wppa['is_slide'] = '0';
			$wppa['is_slideonly'] = '0';
			$wppa['is_filmonly'] = '0';
			$wppa['single_photo'] = '';
			$wppa['is_mphoto'] = '0';
			$wppa['film_on'] = '0';
			$wppa['is_single'] = '0';
			$size = '';
			$align = '';
			// examine album number
			if (is_numeric($album_pos)) {				
				$post_old = substr($post_old, $album_pos + 8);				// shift up to and including %%album= out
				$wppa['start_album'] = // substr($post_old, 0, strpos($post_old, '%%'));
										 wppa_atoid_a($post_old);				// get album #
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift album # and trailing %% out
			}
			elseif (is_numeric($cover_pos)) {
				$post_old = substr($post_old, $cover_pos + 8);				// shift up to and including %%cover= out
				$wppa['start_album'] = wppa_atoid_a($post_old);				// get album #
				$wppa['is_cover'] = '1';
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift album # and trailing %% out
			}
			elseif (is_numeric($slide_pos)) {
				$post_old = substr($post_old, $slide_pos + 8);				// shift up to and including %%slide= out
				$wppa['start_album'] = wppa_atoid_a($post_old);				// get album #
				$wppa['is_slide'] = '1';
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift album # and trailing %% out
			}
			elseif (is_numeric($slidef_pos)) {
				$post_old = substr($post_old, $slidef_pos + 9);				// shift up to and including %%slidef= out
				$wppa['start_album'] = wppa_atoid_a($post_old);				// get album #
				$wppa['is_slide'] = '1';
				$wppa['film_on'] = '1';
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift album # and trailing %% out
			}
			elseif (is_numeric($slideonly_pos)) {
				$post_old = substr($post_old, $slideonly_pos + 12);			// shift up to and including %%slideonly= out
				$wppa['start_album'] = wppa_atoid_a($post_old);				// get album #
				$wppa['is_slideonly'] = '1';
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift album # and trailing %% out
			}
			elseif (is_numeric($slideonlyf_pos)) {
				$post_old = substr($post_old, $slideonlyf_pos + 13);		// shift up to and including %%slideonlyf= out
				$wppa['start_album'] = wppa_atoid_a($post_old);				// get album #
				$wppa['is_slideonly'] = '1';
				$wppa['film_on'] = '1';
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift album # and trailing %% out
			}
			elseif (is_numeric($photo_pos)) {
				$post_old = substr($post_old, $photo_pos + 8);				// shift up to and including %%photo= out
				$wppa['single_photo'] = wppa_atoid($post_old);				// get photo #
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift photo # and trailing %% out
			}
			elseif (is_numeric($mphoto_pos)) {
				$post_old = substr($post_old, $mphoto_pos + 9);				// shift up to and including %%mphoto= out
				$wppa['single_photo'] = wppa_atoid($post_old);				// get photo #
				$wppa['is_mphoto'] = '1';
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift photo # and trailing %% out
			}
			elseif (is_numeric($slphoto_pos)) {
				$post_old = substr($post_old, $slphoto_pos + 10);			// shift up to and including %%slphoto= out
				$wppa['start_photo'] = wppa_atoid($post_old);				// get photo #
				$wppa['is_slide'] = '1';
				$wppa['is_single'] = '1';
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift photo # and trailing %% out
			}
			// see if a size is given and get it
			if (is_numeric($size_pos)) {
				$size_pos = strpos($post_old, '%%size=');					// refresh position due to out-shifting above
				$post_old = substr($post_old, $size_pos + 7);				// shift up to and including %%size= out
				$size = wppa_atoid($post_old);								// get size #
				if (substr_compare($post_old, 'auto', 0, 4) == 0) $size = 'auto';

				$post_old = substr($post_old, strpos($post_old, '%%') + 2); // shift size # and trailing %% out
				if ($size == 'auto') {
					$wppa['auto_colwidth'] = true;
					$wppa['fullsize'] = '';
				}
				else {
					$wppa['auto_colwidth'] = false;
					$wppa['fullsize'] = $size;
				}
			}
			// see if alignment is given and get it
			if (is_numeric($align_pos)) {
				$align_pos = strpos($post_old, '%%align=');					// refresh position due to out-shifting above
				$post_old = substr($post_old, $align_pos + 8);				// shift up to and including %%align= out
				if (substr_compare($post_old, 'left', 0, 4) == 0) $align = 'left';
				elseif (substr_compare($post_old, 'center', 0, 6) == 0) $align = 'center';
				elseif (substr_compare($post_old, 'right', 0, 5) == 0) $align = 'right';
				$post_old = substr($post_old, strpos($post_old, '%%') + 2); // shift position and trailing %% out
				$wppa['align'] = $align;
			}
			
			$do_it = false;
			
			if ($wppa['rendering_enabled']) {		// NOT in a head section (in a meta tag or so)
				$do_it = true;
			}
			if ($wppa['in_widget']) {				// A widget always works
				$do_it = true;						
			}
			if (is_feed()) {						// A feed has no head section
				$do_it = true;
			}
			
			if ($wppa['debug']) {
				
				if ($do_it) $msg = 'Doit is on';
				else $msg = 'Doit is off';
				
				wppa_dbg_msg($msg);
			}
			
			if ($do_it) { 
				$post_new .= wppa_albums();			// Insert the HTML
			}
			else {									// Or an indicator
				$post_new .= '<span style="color:blue; font-weight:bold; ">[WPPA+ Photo display (fsc)]</span>';	
			}
			
			$wppa_pos = strpos($post_old, '%%wppa%%');						// Refresh the next invocation position, if any
		}
	}
	$post_new .= wppa_force_balance_pee($post_old);							// Copy the rest of the post/page
		
	return $post_new;
}

function wppa_atoid($var) {
	$result = '0';
	if (substr($var, 0, 1) == '#') {	// a keyword found
		$to = strpos($var, '%%');
		if ($to) {
			$result = substr($var, 0, $to);
		}
	}
	elseif (substr($var, 0, 1) == '$') {	// a name found
		$to = strpos($var, '%%');
		if ($to) {
			$result = substr($var, 0, $to);
		}
	}
	else {
		$len = 0;
		$t = $result;
		while (is_numeric($t)) {
			$result = $t;
			$len++;
			$t = substr($var, 0, $len);		
		}
		if ( $result == '0' ) $result = substr($var, 0, strpos($var, '%%'));	// Expected a number
		if ( $result < '0' ) $result = '0';	// Neg values not allwed, they are codes now
	}
	return $result;
}
function wppa_atoid_a($var) {
	if ( ( substr($var, 0, 1) == '#') || 							// A keyword
		 ( substr($var, 0, 1) == '$') )	return wppa_atoid($var); 	// A name
	// Its not a keyword, not a name. 
	$result = substr($var, 0, strpos($var, '%%'));
	if ( strpos($result, '.') === false ) return wppa_atoid($var);	// Not an enum/range
	return $result;													// Possible enum/range. Validity check will be in wppa_albums()
}

// New shortcodes
function wppa_shortcodes( $xatts, $content = '' ) {
global $wppa;
global $wppa_postid;
global $wppa_opt;

	$atts = $xatts;
	
	extract( shortcode_atts( array(
		'type'  	=> 'generic',
		'album' 	=> '',
		'photo' 	=> '',
		'size'		=> '',
		'align'		=> '',
		'taglist'	=> '',
		'cols'		=> ''
	), $atts ) );

	// Find occur
	if ( get_the_ID() != $wppa_postid ) {		// New post
		$wppa['occur'] = '0';					// Init this occurance
		$wppa['fullsize'] = '';					// Reset at each post
		$wppa_postid = get_the_ID();			// Remember the post id
	}

	// Set internal defaults
	$wppa['start_album'] 		= '';
	$wppa['is_cover'] 			= '0';
	$wppa['is_slide'] 			= '0';
	$wppa['is_slideonly'] 		= '0';
	$wppa['is_filmonly'] 		= '0';
	$wppa['single_photo'] 		= '';
	$wppa['is_mphoto'] 			= '0';
	$wppa['film_on'] 			= '0';
	$wppa['is_landing'] 		= '0';
	$wppa['start_photo'] 		= '0';			// Start a slideshow here
	$wppa['is_single'] 			= false;		// Is a one image slideshow
	$wppa['is_upload'] 			= false;
	$wppa['is_multitagbox'] 	= false;
	$wppa['is_tagcloudbox'] 	= false;
	$wppa['taglist'] 			= '';
	$wppa['tagcols']			= '2';
	$wppa['is_autopage']		= false;
	$wppa['portrait_only'] 		= false;
	$wppa['shortcode_content'] 	= $content;

	// Find type
	switch ( $type ) {
		case 'landing':
			$wppa['is_landing'] = '1';
		case 'generic':
			break;
		case 'cover':
			$wppa['start_album'] = $album;
			$wppa['is_cover'] = '1';
			break;
		case 'album':
		case 'content':
			$wppa['start_album'] = $album;
			break;
		case 'thumbs':
			$wppa['start_album'] = $album;
			$wppa['photos_only'] = true;
			break;
		case 'covers':
			$wppa['start_album'] = $album;
			$wppa['albums_only'] = true;
			break;
		case 'slide':
			$wppa['start_album'] = $album;
			$wppa['is_slide'] = '1';
			$wppa['start_photo'] = $photo;
//			$wppa['is_single'] = $single;
			break;
		case 'slideonly':
			$wppa['start_album'] = $album;
			$wppa['is_slideonly'] = '1';
			$wppa['start_photo'] = $photo;
			break;
		case 'slideonlyf':
			$wppa['start_album'] = $album;
			$wppa['is_slideonly'] = '1';
			$wppa['film_on'] = '1';
			$wppa['start_photo'] = $photo;
			break;
			
		case 'filmonly':
			$wppa['start_album'] = $album;
			$wppa['is_slideonly'] = '1';
			$wppa['is_filmonly'] = '1';
			$wppa['film_on'] = '1';
			$wppa['start_photo'] = $photo;
			break;
		
		case 'photo':
			$wppa['single_photo'] = $photo;
			break;
		case 'mphoto':
			$wppa['single_photo'] = $photo;
			$wppa['is_mphoto'] = '1';
			break;
		case 'slphoto':
			$wppa['is_slide'] = '1';
			$wppa['start_photo'] = $photo;
			$wppa['is_single'] = '1';
			break;
		case 'autopage':
			$wppa['is_autopage'] = '1';
			break;			
		case 'upload':
			$wppa['start_album'] = $album;
			$wppa['is_upload'] = true;
			break;
		case 'multitag':
			$wppa['taglist'] = wppa_sanitize_tags($taglist);
			$wppa['is_multitagbox'] = true;
			if ( $cols ) $wppa['tagcols'] = $cols;
			break;
		case 'tagcloud':
			$wppa['taglist'] = wppa_sanitize_tags($taglist);
			$wppa['is_tagcloudbox'] = true;
			break;
		case 'bestof':
			$wppa['bestof'] = true;
			$wppa['bestof_args'] = $xatts;
			break;
		case 'superview':
			$wppa['is_superviewbox'] = true;
			$wppa['start_album'] = $album;
			break;
		case 'search':
			$wppa['is_searchbox'] = true;
			$wppa['may_sub'] = isset( $xatts['sub'] ) && $xatts['sub'];
			$wppa['may_root'] = isset( $xatts['root'] ) && $xatts['root'];
			break;
			
		default:
			wppa_dbg_msg ( 'Invalid type: '.$type.' in wppa shortcode.', 'red', 'force' );
			return '';
	}
	
	// Count (internally to wppa_albums)
	
	// Find size
	if ($size == 'auto') {
		$wppa['auto_colwidth'] = true;
		$wppa['fullsize'] = '';
	}
	else {
		$wppa['auto_colwidth'] = false;
		$wppa['fullsize'] = $size;
	}
	
	// Find align
	$wppa['align'] = $align;

	// Ready to render ???
	$do_it = false;
	if ($wppa['rendering_enabled']) $do_it = true;	// NOT in a head section (in a meta tag or so)
	if ($wppa['in_widget']) $do_it = true;			// A widget always works
	if (is_feed()) $do_it = true;					// A feed has no head section
	
	if ($wppa['debug']) {
		if ($do_it) $msg = 'Doit is on'; else $msg = 'Doit is off';
		wppa_dbg_msg($msg);
	}
	
	if ( $wppa_opt['wppa_render_shortcode_always'] ) $do_it = true;
	
	if ($do_it) $result =  wppa_albums();				// Return the HTML
	else $result = '<span style="color:blue; font-weight:bold; ">[WPPA+ Photo display (fsh)]</span>';	// Or an indicator

	// Reset
	$wppa['start_photo'] = '0';	// Start a slideshow here
	$wppa['is_single'] = false;	// Is a one image slideshow

	return $result;
}

add_shortcode( 'wppa', 'wppa_shortcodes' );

// Add filter for the use of our lightbox implementation for non wppa+ images
add_filter( 'the_content', 'wppa_lightbox_global' );

function wppa_lightbox_global( $content ) {
global $wppa_opt;

	if ( wppa_switch( 'wppa_lightbox_global' ) ) {
		if ( $wppa_opt['wppa_lightbox_name'] == 'wppa' ) {	// Our lightbox
			if ( wppa_switch( 'wppa_lightbox_global_set' )  ) { // A set
				$pattern ="/<a(.*?)href=('|\")(.*?).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>/i";
				$replacement = '<a$1href=$2$3.$4$5 rel="wppa[single]" style="'.' cursor:url('.wppa_get_imgdir().$wppa_opt['wppa_magnifier'].'),pointer;'.'"$6>';
				$content = preg_replace($pattern, $replacement, $content);
			}
			else {	// Not a set
				$pattern ="/<a(.*?)href=('|\")(.*?).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>/i";
				$replacement = '<a$1href=$2$3.$4$5 rel="wppa" style="'.' cursor:url('.wppa_get_imgdir().$wppa_opt['wppa_magnifier'].'),pointer;'.'"$6>';
				$content = preg_replace($pattern, $replacement, $content);
			}
		}
	}
	return $content;
}
