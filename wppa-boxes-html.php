<?php
/* wppa-boxes-html.php
* Package: wp-photo-album-plus
*
* Various wppa boxes
* Version 5.4.18
*
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

// The box containing the thumbnails
function wppa_thumb_area( $action ) {
global $wppa;
global $wppa_alt;

	if ( $action == 'open' ) {
		if ( is_feed() ) {
			$wppa['out'] .= wppa_nltab( '+' ).'<div id="wppa-thumb-area-'.$wppa['mocc'].'" class="wppa-thumb-area" style="'.__wcs( 'wppa-box' ).__wcs( 'wppa-'.$wppa_alt ).'" >';
		}
		else {
			$wppa['out'] .= wppa_nltab( '+' ).'<div id="wppa-thumb-area-'.$wppa['mocc'].'" class="wppa-thumb-area wppa-thumb-area-'.$wppa['mocc'].' wppa-box wppa-'.$wppa_alt.'" style="'.__wcs( 'wppa-box' ).__wcs( 'wppa-'.$wppa_alt ).'width: '.wppa_get_thumbnail_area_width().'px;" >';
			if ( $wppa['current_album'] ) wppa_bump_viewcount( 'album', $wppa['current_album'] );
		}		
		if ( $wppa_alt == 'even' ) $wppa_alt = 'alt'; else $wppa_alt = 'even';
	}
	elseif ( $action == 'close' ) {
		if ( ! $wppa['is_upldr'] ) {
			wppa_user_create_html( $wppa['current_album'], wppa_get_container_width( 'netto' ), 'thumb' );
			wppa_user_upload_html( $wppa['current_album'], wppa_get_container_width( 'netto' ), 'thumb' );
		}
		$wppa['out'] .= wppa_nltab().'<div class="wppa-clear" style="'.__wis( 'clear:both;' ).'" ></div>';		

		$wppa['out'] .= wppa_nltab( '-' ).'</div><!-- wppa-thumb-area-'.$wppa['mocc'].' -->';
	}
	else {
		$wppa['out'] .= wppa_nltab().'<span style="color:red;">Error, wppa_thumb_area() called with wrong argument: '.$action.'. Possible values: \'open\' or \'close\'</span>';
	}
}

// Search box
function wppa_search_box() {
global $wppa;

	if ( is_feed() ) return;
	
	wppa_container( 'open' );
	$wppa['out'] .= wppa_nltab( '+' ).'<div id="wppa-search-'.$wppa['mocc'].'" class="wppa-box wppa-search" style="'.__wcs( 'wppa-box' ).__wcs( 'wppa-search' ).'">';
		$wppa['out'] .= wppa_get_search_html( '', $wppa['may_sub'], $wppa['may_root'] );
	$wppa['out'] .= wppa_nltab( '-' ).'<div class="wppa-clear" style="'.__wis( 'clear:both;' ).'" ></div></div>';
	wppa_container( 'close' );
}

// Get search html
function wppa_get_search_html( $label = '', $sub = false, $root = false ) {
global $wppa;
global $wppa_session;
global $wppa_opt;

	$page 			= wppa_get_the_landing_page( 'wppa_search_linkpage', __a( 'Photo search results' ) );
	$pagelink 		= wppa_dbg_url( get_page_link( $page ) );
	$cansubsearch  	= $sub && isset ( $wppa_session['use_searchstring'] ) && $wppa_session['use_searchstring'];
	$subboxset 		= isset ( $wppa_session['subbox'] ) && $wppa_session['subbox'] ? 'checked="checked"' : '';
	$canrootsearch 	= $root; 
	$rootboxset 	= isset ( $wppa_session['rootbox'] ) && $wppa_session['rootbox'] ? 'checked="checked"' : '';
	$value 			= $cansubsearch ? '' : wppa_test_for_search();
	$root 			= isset( $wppa_session['search_root'] ) ? $wppa_session['search_root'] : '';
	$fontsize 		= $wppa['in_widget'] ? 'font-size: 9px;' : '';

	wppa_dbg_msg( 'Root='.$root.': '.wppa_get_album_name( $root ) );
	
	$result = '
	<form id="wppa_searchform_'.$wppa['mocc'].'" action="'.$pagelink.'" method="post" class="widget_search" >
		<div>
			'.$label;
			if ( $cansubsearch ) {
				$result .= '<small>'.$wppa_session['display_searchstring'].'<br /></small>';
			}
			$result .= '
			<input type="text" name="wppa-searchstring" id="wppa_s-'.$wppa['mocc'].'" value="'.$value.'" />
			<input id="wppa_searchsubmit-'.$wppa['mocc'].'" type="submit" name="wppa-search-submit" value="'.__a( 'Search' ).'" onclick="if ( document.getElementById( \'wppa_s-'.$wppa['mocc'].'\' ).value == \'\' ) return false;" />';
			
			$result .= '
			<input type="hidden" name="wppa-searchroot" value="'.$root.'" />';
			
			if ( $canrootsearch ) { 
				$result .= '<div style="'.$fontsize.'" ><input type="checkbox" name="wppa-rootsearch" '.$rootboxset.'/> '.__a( 'Search in current section' ).'</div>';
			}
			if ( $cansubsearch ) {
				$result .= '<div style="'.$fontsize.'" ><input type="checkbox" name="wppa-subsearch" '.$subboxset.'/> '.__a( 'Search in current results' ).'</div>';
			} 
			$result .= '
		</div>
	</form>';

	return $result;
}

// Superview box
function wppa_superview_box( $album_root = '0', $sort = true ) {
global $wppa;

	if ( is_feed() ) return;
	
	wppa_container( 'open' );
	$wppa['out'] .= wppa_nltab( '+' ).'<div id="wppa-superview-'.$wppa['mocc'].'" class="wppa-box wppa-superview" style="'.__wcs( 'wppa-box' ).__wcs( 'wppa-superview' ).'">';
		$wppa['out'] .= wppa_get_superview_html( $album_root, $sort );
	$wppa['out'] .= wppa_nltab( '-' ).'<div class="wppa-clear" style="'.__wis( 'clear:both;' ).'" ></div></div>';
	wppa_container( 'close' );
}

// Get superview html
function wppa_get_superview_html( $album_root = '0', $sort = true ) {
global $wppa;
global $wppa_session;
global $wppa_opt;

	$page = wppa_get_the_landing_page( 'wppa_super_view_linkpage', __a( 'Super View Photos' ) );
	$url = get_permalink( $page );

	if ( ! isset ( $wppa_session ) ) $wppa_session = array();
	if ( ! isset ( $wppa_session['superview'] ) ) {
		$wppa_session['superview'] = 'thumbs';
		$wppa_session['superalbum'] = '0';
	}

	$checked = 'checked="checked"';
			
	$result = '
	<div>
		<form action="'.$url.'" method = "get">
			<label>'.__( 'Album:', 'wppa' ).'</label><br />
			<select name="wppa-album">
				'.wppa_album_select_a( array( 	'selected' 			=> $wppa_session['superalbum'], 
												'addpleaseselect' 	=> true, 
												'root' 				=> $album_root, 
												'content' 			=> true,
												'sort'				=> $sort,
												'path' 				=> ( ! $wppa['in_widget'] )
												 ) ).'
			</select><br />
			<input type="radio" name="wppa-slide" value="nil" '.( $wppa_session['superview'] == 'thumbs' ? $checked : '' ).'>'.__( 'Thumbnails', 'wppa' ).'<br />
			<input type="radio" name="wppa-slide" value="1" '.( $wppa_session['superview'] == 'slide' ? $checked : '' ).'>'.__( 'Slideshow', 'wppa' ).'<br />
			<input type="hidden" name="wppa-occur" value="1" />
			<input type="hidden" name="wppa-superview" value="1" />
			<input type="submit" value="'.__( 'Submit', 'wppa' ).'" />
		</form>
	</div>
	';
	
	return $result;
}

// The tagcloud box
function wppa_tagcloud_box( $seltags = '', $minsize = '8', $maxsize = '24' ) {
global $wppa;

	if ( is_feed() ) return;

	wppa_container( 'open' );
	$wppa['out'] .= wppa_nltab( '+' ).'<div id="wppa-tagcloud-'.$wppa['mocc'].'" class="wppa-box wppa-tagcloud" style="'.__wcs( 'wppa-box' ).__wcs( 'wppa-tagcloud' ).'">';
		$wppa['out'] .= wppa_get_tagcloud_html( $seltags, $minsize, $maxsize );
	$wppa['out'] .= wppa_nltab( '-' ).'<div class="wppa-clear" style="'.__wis( 'clear:both;' ).'" ></div></div>';
	wppa_container( 'close' );
}

// Get html for tagcloud
function wppa_get_tagcloud_html( $seltags = '', $minsize = '8', $maxsize = '24' ) {
global $wppa_opt;
global $wppa;

	$page = wppa_get_the_landing_page( 'wppa_tagcloud_linkpage', __a( 'Tagged photos' ) );

	$result 	= '';
	if ( $wppa_opt['wppa_tagcloud_linkpage'] ) {
		$hr = wppa_get_permalink( $page );
		if ( $wppa_opt['wppa_tagcloud_linktype'] == 'album' ) {
			$hr .= 'wppa-album=0&amp;wppa-cover=0&amp;wppa-occur=1';
		}
		if ( $wppa_opt['wppa_tagcloud_linktype'] == 'slide' ) {
			$hr .= 'wppa-album=0&amp;wppa-cover=0&amp;wppa-occur=1&amp;slide';
		}
	}
	else {
		return __a( 'Please select a tagcloud landing page in Table VI-C3b' );
	}
	$tags = wppa_get_taglist();
	if ( $tags ) {
		$top = '0';
		foreach ( $tags as $tag ) {	// Find largest percentage
			if ( $tag['fraction'] > $top ) $top = $tag['fraction'];
		}
		if ( $top ) $factor = ( $maxsize - $minsize ) / $top;
		else $factor = '1.0';
		$selarr = $seltags ? explode( ',', $seltags ) : array();
		foreach ( $tags as $tag ) {
			if ( ! $seltags || in_array( $tag['tag'], $selarr ) ) {
				$href 		= $hr . '&amp;wppa-tag=' . str_replace( ' ', '%20', $tag['tag'] );
				$title 		= sprintf( '%d photos - %s%%', $tag['count'], $tag['fraction'] * '100' );
				$name 		= $tag['tag'];
				$size 		= floor( $minsize + $tag['fraction'] * $factor );
				$result    .= '<a href="'.$href.'" title="'.$title.'" style="font-size:'.$size.'px;" >'.$name.'</a> ';
			}
		}
	}
	
	return $result;
}

// The multitag box
function wppa_multitag_box( $nperline = '2', $seltags = '' ) {
global $wppa;

	if ( is_feed() ) return;
	
	wppa_container( 'open' );
	$wppa['out'] .= wppa_nltab( '+' ).'<div id="wppa-multitag-'.$wppa['mocc'].'" class="wppa-box wppa-multitag" style="'.__wcs( 'wppa-box' ).__wcs( 'wppa-multitag' ).'">';
		$wppa['out'] .= wppa_get_multitag_html( $nperline, $seltags );
	$wppa['out'] .= wppa_nltab( '-' ).'<div class="wppa-clear" style="'.__wis( 'clear:both;' ).'" ></div></div>';
	wppa_container( 'close' );
}

// The html for multitag widget
function wppa_get_multitag_html( $nperline = '2', $seltags = '' ) {
global $wppa_opt;
global $wppa;

	$or_only = wppa_switch( 'wppa_tags_or_only' );
	$page = wppa_get_the_landing_page( 'wppa_multitag_linkpage', __a( 'Multi Tagged photos' ) );
	
	$result 	= '';
	if ( $wppa_opt['wppa_multitag_linkpage'] ) {
		$hr = wppa_get_permalink( $page );
		if ( $wppa_opt['wppa_multitag_linktype'] == 'album' ) {
			$hr .= 'wppa-album=0&wppa-cover=0&wppa-occur=1';
		}
		if ( $wppa_opt['wppa_multitag_linktype'] == 'slide' ) {
			$hr .= 'wppa-album=0&wppa-cover=0&wppa-occur=1&slide';
		}
	}
	else {
		return __a( 'Please select a multitag landing page in Table VI-C4b' );
	}
	$tags = wppa_get_taglist();
	
	$result .= '
	<script type="text/javascript">
	function wppaProcessMultiTagRequest() {
	var any = false;
	var url="'.$hr.'&wppa-tag=";';
	
	if ( $or_only ) {
		$result .= '
		var andor = "or";';
	}
	else {
	$result .= '
		var andor = "and";
			if ( document.getElementById( "andoror-'.$wppa['mocc'].'" ).checked ) andor = "or";
		var sep;';
	}
	
	$result .= '
	if ( andor == "and" ) sep = ","; else sep = ";";
	';
	
	$selarr = $seltags ? explode( ',', $seltags ) : array();
	if ( $tags ) foreach ( $tags as $tag ) {
		if ( ! $seltags || in_array( $tag['tag'], $selarr ) ) {
			$result .= '
			if ( document.getElementById( "wppa-'.str_replace( ' ', '_', $tag['tag']).'" ).checked ) {
				url+="'.str_replace(' ', '%20', $tag['tag']).'"+sep;
				any = true;
			}';
		}
	}	
	
	$result .= '
	if ( any ) document.location = url;
	else alert ( "'.__a( 'Please check the tag(s) that the photos must have' ).'" );
	}</script>
	';
	
	$qtag = wppa_get_get( 'tag' );
	$andor = $or_only ? 'or' : 'and'; // default
	if ( strpos( $qtag, ',' ) ) {
		$querystringtags = explode( ',',wppa_get_get( 'tag' ) );
	}
	elseif ( strpos( $qtag, ';' ) ) {
		$querystringtags = explode( ';', wppa_get_get( 'tag' ) );
		$andor = 'or';
	}
	else $querystringtags = wppa_get_get( 'tag' );

	if ( $tags ) {
	
		
		if ( ! $or_only ) {
			$result .= '<table class="wppa-multitag-table">';
			$result .= '<tr><td><input class="radio" name="andor-'.$wppa['mocc'].'" value="and" id="andorand-'.$wppa['mocc'].'" type="radio" ';
			if ( $andor == 'and' ) $result .= 'checked="checked" ';
			$result .= ' />&nbsp;'.__a( 'And', 'wppa_theme' ).'</td>';
			$result .= '<td><input class="radio" name="andor-'.$wppa['mocc'].'" value="or" id="andoror-'.$wppa['mocc'].'" type="radio" ';
			if ( $andor == 'or' ) $result .= 'checked="checked" ';
			$result .= ' />&nbsp;'.__a( 'Or', 'wppa_theme' ).'</td>';
			$result .= '</tr>';
			$result .= '</table>';
		}

		$result .= '<table class="wppa-multitag-table">';

		$count = '0';
		$checked = '';		
		
		$tropen = false;
		foreach ( $tags as $tag ) {
			if ( ! $seltags || in_array( $tag['tag'], $selarr ) ) {
				if ( $count % $nperline == '0' ) {
					$result .= '<tr>';
					$tropen = true;
				}
				if ( is_array( $querystringtags ) ) {
					$checked = in_array( $tag['tag'], $querystringtags ) ? 'checked="checked" ' : '';
				}
				$result .= '<td style="'.__wis( 'padding-right:4px;' ).'" ><input type="checkbox" id="wppa-'.str_replace( ' ', '_', $tag['tag'] ).'" '.$checked.' />&nbsp;'.str_replace( ' ', '&nbsp;', $tag['tag'] ).'</td>';
				$count++;
				if ( $count % $nperline == '0' ) {
					$result .= '</tr>';
					$tropen = false;
				}
			}
		}
		
		if ( $tropen ) {
			while ( $count % $nperline != '0' ) {
				$result .= '<td></td>';
				$count++;
			}
			$result .= '</tr>';
		}
		$result .= '</table>';
		$result .= '<input type="button" onclick="wppaProcessMultiTagRequest()" value="'.__a( 'Find!' ).'" />';
	}
	
	return $result;
}

// Make html for sharebox
function wppa_get_share_html( $id, $key = '', $js = true ) {
global $wppa;
global $wppa_opt;
global $wppa_locale;

	$do_it = false;
	if ( ! $wppa['is_slideonly'] || $key == 'lightbox' ) {
		if ( wppa_switch( 'wppa_share_on' ) && ! $wppa['in_widget'] ) $do_it = true;
		if ( wppa_switch( 'wppa_share_on_widget' ) && $wppa['in_widget'] ) $do_it = true;
		if ( wppa_switch( 'wppa_share_on_lightbox' ) ) $do_it = true;
	}
	if ( ! $do_it ) return '';

	// The share url
	$share_url = str_replace( '&amp;', '&', wppa_get_image_page_url_by_id( $id, wppa_switch( 'wppa_share_single_image' ) ) );
	
	// The share title
	$photo_name = wppa_get_photo_name( $id );
	
	// The share description
	$photo_desc = strip_shortcodes( wppa_strip_tags( wppa_html( wppa_get_photo_desc( $id ) ), 'all' ) );

	// The default description
	$see_on_site = sprintf( __a( 'See this image on %s' ), str_replace( '&amp;', __a( 'and' ), get_bloginfo( 'name' ) ) );
	
	// The share image. Must be the fullsize image for facebook. If you take the thumbnail, facebook takes a different image at random.
	$share_img = wppa_get_photo_url( $id );

	// The icon size
	$s = ( ( $wppa['in_widget'] && $key != 'lightbox' ) || $key == 'thumb' ) ? '16' : $wppa_opt['wppa_share_size'];
	
	// qr code
	if ( wppa_switch( 'wppa_share_qr' ) && $key != 'thumb' ) {	
		$src = 'http://api.qrserver.com/v1/create-qr-code/?data='.urlencode( $share_url ).'&size=80x80&color='.trim( $wppa_opt['wppa_qr_color'], '#' ).'&bgcolor='.trim( $wppa_opt['wppa_qr_bgcolor'], '#' );
		$qr = '<div style="float:left; padding:2px;" ><img src="'.$src.'" title="'.esc_attr( $share_url ).'" alt="'.__a('QR code').'" /></div>';
	}
	else $qr = '';
	
	// twitter share button
	if ( wppa_switch( 'wppa_share_twitter' ) ) {	
		$tweet = urlencode( $see_on_site ) . ': ';
		$tweet_len = strlen( $tweet ) + '1';
		
		$tweet .= urlencode( $share_url );
		$url_len = strpos( $share_url, '/', 8 ) + 1;	// find first '/' after 'http( s )://' rest doesnt count for twitter chars
		$tweet_len += ( $url_len > 1 ) ? $url_len : strlen( $share_url );
		
		$rest_len = 140 - $tweet_len;
		
		if ( wppa_switch( 'wppa_show_full_name' ) ) {
			if ( $rest_len > strlen( $photo_name ) ) {
				$tweet .= ' ' . urlencode( $photo_name );
				$rest_len -= strlen( $photo_name );
				$rest_len -= '2';
			}
			else {
				$tweet .= ' '. urlencode( substr( $photo_name, 0, $rest_len ) ) . '...';
				$rest_len -= strlen( substr( $photo_name, 0, $rest_len ) );
				$rest_len -= '5';
			}
		}
		
		if ( $photo_desc ) {
			if ( $rest_len > strlen( $photo_desc ) ) {
				$tweet .= ': ' . urlencode( $photo_desc );
			}
			elseif ( $rest_len > 8 ) {
				$tweet .= ': '. urlencode( substr( $photo_desc, 0, $rest_len ) ) . '...';
			}
		}
		
		$tw = 	'<div style="float:left; padding:2px;" >';
		$tw .= 		'<a title="'.sprintf( __a( 'Tweet %s on Twitter' ), esc_attr( $photo_name ) ).'" ';
		$tw .= 			'href="https://twitter.com/intent/tweet?text='.$tweet.'" ';
		$tw .= 			'target="_blank" >';
		$tw .=				'<img src="'.wppa_get_imgdir().'twitter.png" style="height:'.$s.'px;" alt="'.esc_attr( __a( 'Share on Twitter' ) ).'" />';
		$tw .= 		'</a>';
		$tw .=	'</div>';
	}
	else $tw = '';

	// Google
	if ( wppa_switch( 'wppa_share_google' ) ) {
		$go = 	'<div style="float:left; padding:2px;" >';
		$go .= 		'<a title="'.sprintf( __a( 'Share %s on Google+' ), esc_attr( $photo_name ) ).'" ';
		$go .= 			'href="https://plus.google.com/share?url='.urlencode( $share_url ).'" ';
		$go .= 			'onclick="javascript:window.open( this.href, \"\", \"menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\" );return false;" ';
		$go .= 			'target="_blank" >';
		$go .= 			'<img src="'.wppa_get_imgdir().'google.png" style="height:'.$s.'px;" alt="'.esc_attr( __a( 'Share on Google+' ) ).'"/>';
		$go .= 		'</a>';
		$go .= 	'</div>';
	}
	else $go = '';
	
	// Pinterest
	$desc = urlencode( $see_on_site ).': '.urlencode( $photo_desc ).'" ';
	if ( strlen( $desc ) > 500 ) $desc = substr( $desc, 0, 495 ).'...';
	if ( wppa_switch( 'wppa_share_pinterest' ) ) {
		$pi = 	'<div style="float:left; padding:2px;" >';
		$pi .= 		'<a title="'.sprintf( __a( 'Share %s on Pinterest' ), esc_attr( $photo_name ) ).'" ';
		$pi .= 			'href="http://pinterest.com/pin/create/button/?url='.urlencode( $share_url );
		$pi .=			'&media='.urlencode( str_replace( '/thumbs/', '/', $share_img ) );						// Fullsize image
		$pi .=			'&description='.$desc;
		$pi .=			'target="_blank" >';//'class="pin-it-button" count-layout="horizontal" >';
		$pi .=			'<img src="'.wppa_get_imgdir().'pinterest.png" style="height:'.$s.'px;" alt="'.esc_attr( __a( 'Share on Pinterest' ) ).'" />';	//border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" />';
		$pi .=		'</a>';
		$pi .=	'</div>';

	}
	else $pi = '';
	
	// LinkedIn
	if ( wppa_switch( 'wppa_share_linkedin' ) && $key != 'thumb' && $key != 'lightbox' ) {
		if ( $js ) {
			$li = '[script src="//platform.linkedin.com/in.js" type="text/javascript">';
			$li .= 'lang: '.$wppa_locale;
			$li .= '[/script>';
			$li .= '[script type="IN/Share" data-url="'.urlencode( $share_url ).'" data-counter="top">[/script>';
		}
		else {
			$li = '<script src="//platform.linkedin.com/in.js" type="text/javascript">';
			$li .= 'lang: '.$wppa_locale;
			$li .= '</script>';
			$li .= '<script type="IN/Share" data-url="'.urlencode( $share_url ).'" data-counter="top"></script>';
		}
	}
	else $li = '';
	
	// Facebook
	$need_fb_init = false;
	$small = ( 'thumb' == $key );
	if ( 'lightbox' == $key ) {
		if ( wppa_switch( 'wppa_facebook_like' ) && wppa_switch( 'wppa_share_facebook' ) ) {
			$lbs = 'max-width:62px; max-height:96px; overflow:show;';
		}
		else {
			$lbs = 'max-width:62px; max-height:64px; overflow:show;';
		}
	}
	else {
		$lbs = '';
	}
	$fb = '';
	
	// Share
	if ( wppa_switch( 'wppa_share_facebook' ) && ! wppa_switch( 'wppa_facebook_like' ) ) { 
		if ( $small ) {
			$fb .= '<div class="fb-share-button" style="float:left;" data-href="'.$share_url.'" data-type="icon" ></div>';
		}
		else {
			$disp = wppa_opt( 'wppa_fb_display' );
			if ( 'standard' == $disp ) {
				$disp = 'button';
			}
			$fb .= '<div class="fb-share-button" style="float:left; '.$lbs.'" data-width="200" data-href="'.$share_url.'" data-type="' . $disp . '" ></div>';
		}
		$need_fb_init = true;
	}
	
	// Like
	if ( wppa_switch( 'wppa_facebook_like' ) && ! wppa_switch( 'wppa_share_facebook' ) ) {
		if ( $small ) {
			$fb .= '<div class="fb-like" style="float:left;" data-href="'.$share_url.'" data-layout="button" ></div>';
		}
		else {
			$fb .= '<div class="fb-like" style="float:left; '.$lbs.'" data-width="200" data-href="'.$share_url.'" data-layout="' . wppa_opt( 'wppa_fb_display' ) . '" ></div>';
		}
		$need_fb_init = true;
	}

	// Like and share
	if ( wppa_switch( 'wppa_facebook_like' ) && wppa_switch( 'wppa_share_facebook' ) ) {
		if ( $small ) {
			$fb .= '<div class="fb-like" style="float:left;" data-href="'.$share_url.'" data-layout="button" data-action="like" data-show-faces="false" data-share="true"></div>';
		}
		else {
			$fb .= '<div class="fb-like" style="float:left; '.$lbs.'" data-width="200" data-href="'.$share_url.'" data-layout="' . wppa_opt( 'wppa_fb_display' ) . '" data-action="like" data-show-faces="false" data-share="true"></div>';
		}
		$need_fb_init = true;
	}

	// Comments
	if ( wppa_switch( 'wppa_facebook_comments' ) && ! $wppa['in_widget'] && $key != 'thumb' && $key != 'lightbox' ) { // && $key != 'lightbox' ) {
		$width = $wppa['auto_colwidth'] ? '470' : wppa_get_container_width( true );
		if ( wppa_switch( 'wppa_facebook_comments' ) ) {
			$fb .= '<div style="color:blue;clear:both">'.__a( 'Comment on Facebook:' ).'</div>';
			$fb .= '<div class="fb-comments" data-href="'.$share_url.'" data-width='.$width.'></div>';
			$need_fb_init = true;
		}
	}
	
	// Need init?
	if ( $need_fb_init ) {
		if ( $js && $key != 'thumb' ) {
			$fb .= '[script>wppaFbInit();[/script>';
		}
		else {
			$fb .= '<script>wppaFbInit();</script>';
		}
	}

	return $qr.$tw.$go.$pi.$li.$fb.//.'<small>This box is under construction and may not yet properly work for all icons shown</small>'.
	'<div style="clear:both"></div>';

}

// The upload box
function wppa_upload_box() {
global $wppa;

	if ( ! wppa_switch( 'wppa_user_upload_on' ) ) {
		return;													// Frontend upload not enabled
	}
	
	if ( wppa_switch( 'wppa_user_upload_login' ) ) {
		if ( ! is_user_logged_in() ) return;					// Must login
	}

	if ( $wppa['start_album'] ) {
		if ( ! wppa_have_access( $wppa['start_album'] ) ) return;	// No access to this album
	}
	else {
		if ( ! wppa_have_access( '0' ) ) return;					// No access to any album
	}
	
	wppa_container( 'open' );
	$wppa['out'] .= wppa_nltab( '+' ).'<div id="wppa-upload-box-'.$wppa['mocc'].'" class="wppa-box wppa-upload" style="'.__wcs( 'wppa-box' ).__wcs( 'wppa-upload' ).'">';
		wppa_user_create_html( $wppa['start_album'], wppa_get_container_width( 'netto' ), 'uploadbox' );
		wppa_user_upload_html( $wppa['start_album'], wppa_get_container_width( 'netto' ), 'uploadbox' );
	$wppa['out'] .= wppa_nltab( '-' ).'<div style="clear:both;"></div></div>';
	wppa_container( 'close' );
}

// Frontend create album, for use in the upload box, the widget or in the album and thumbnail box
function wppa_user_create_html( $alb, $width, $where = '', $mcr = false ) {
global $wppa;
global $wppa_opt;

	if ( $alb < '0' ) $alb = '0';
	
	// May I?
	if ( ! wppa_switch( 'wppa_user_create_on' ) ) return;			// Feature not enabled
	if ( wppa_switch( 'wppa_user_create_login' ) ) {
		if ( ! is_user_logged_in() ) return;					// Must login
	}
	if ( ! wppa_have_access( $alb ) ) {
		return;						// No album access
	}
	if ( is_user_logged_in() ) {
		if ( ! $alb && ! wppa_can_create_top_album() ) return;	// Current logged in user can not create a toplevel album
		if ( $alb && ! wppa_can_create_album() ) return;		// Current logged in user can not create a sub-album
	}
	if ( ! wppa_user_is( 'administrator' ) && wppa_switch( 'wppa_owner_only' ) ) {
		if ( $alb ) {
			$album = wppa_cache_album( $alb );
			if ( $album['owner'] == '--- public ---' ) return;	// Need to be admin to create public subalbums
		}
	}

	if ( wppa_is_user_blacklisted() ) return;

	// In a widget or multi column responsive?
	$small = ( $wppa['in_widget'] == 'upload' || $mcr );

	// Create the return url
	$returnurl = wppa_get_permalink();
	if ( $where == 'cover' ) {
		$returnurl .= 'wppa-album='.$alb.'&amp;wppa-cover=0&amp;wppa-occur='.$wppa['occur'];
	}
	elseif ( $where == 'thumb' ) {
		$returnurl .= 'wppa-album='.$alb.'&amp;wppa-cover=0&amp;wppa-occur='.$wppa['occur'];
	}
	elseif ( $where == 'widget' || $where == 'uploadbox' ) {
	}
	if ( $wppa['page'] ) $returnurl .= '&amp;wppa-page='.$wppa['page'];
	$returnurl = trim( $returnurl, '?' );
	
	$returnurl = wppa_trim_wppa_( $returnurl );
	
	// Make the HTML
	$t = $mcr ? 'mcr-' : '';
	$wppa['out'] .= '
	<div style="clear:both"></div>
	<a id="wppa-cr-'.$alb.'-'.$wppa['mocc'].'" class="wppa-create-'.$where.'" onclick="'.
									'jQuery( \'#wppa-create-'.$t.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'block\' );'.	// Open the Create form
									'jQuery( \'#wppa-cr-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'.			// Hide the Create link
									'jQuery( \'#wppa-up-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'.			// Hide the Upload link
									'jQuery( \'#wppa-ea-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'.			// Hide the Edit link
									'jQuery( \'#wppa-cats-' . $alb . '-' . $wppa['mocc'] . '\' ).css( \'display\',\'none\' );'.	// Hide catogory
									'_wppaDoAutocol( ' . $wppa['mocc'] . ' )'.													// Trigger autocol
									'" style="float:left; cursor:pointer;">
		'.__a( 'Create Album' ).'
	</a>
	
	<div id="wppa-create-'.$t.$alb.'-'.$wppa['mocc'].'" class="wppa-file-'.$t.$wppa['mocc'].'" style="width:'.$width.'px;text-align:center;display:none;" >
		<form id="wppa-creform-'.$alb.'-'.$wppa['mocc'].'" action="'.$returnurl.'" method="post" >'.
		wp_nonce_field( 'wppa-album-check' , 'wppa-nonce', false, false ).'
		<input type="hidden" name="wppa-album-parent" value="'.$alb.'" />
		<input type="hidden" name="wppa-fe-create" value="yes" />';
		// Name
		$wppa['out'] .= '
			<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.
				__a( 'Enter album name.' ).'&nbsp;<span style="font-size:10px;" >'.__a( 'Don\'t leave this blank!' ).'</span>
			</div>
			<input type="text" class="wppa-box-text wppa-file-'.$t.$wppa['mocc'].'" style="padding:0; width:'.( $width-6 ).'px; '.__wcs( 'wppa-box-text' ).'" name="wppa-album-name" />';

		// Description
		$desc = '';
		$wppa['out'] .= '
			<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.
				__a( 'Enter album description' ).'
			</div>
			<textarea class="wppa-user-textarea wppa-box-text wppa-file-'.$t.$wppa['mocc'].'" style="height:120px; width:'.( $width-6 ).'px; '.__wcs( 'wppa-box-text' ).'" name="wppa-album-desc" >'.$desc.'</textarea>';

			if ( true ) { 	// Captcha
				$captkey = $wppa['randseed'];
				$wppa['out'] .= '<div style="float:left; margin: 6px 0;" ><div style="float:left;">'.wppa_make_captcha( $captkey ).'</div><input type="text" id="wppa-captcha-'.$wppa['mocc'].'" name="wppa-captcha" style="margin-left: 6px; width:50px; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" /></div>';
//				$wppa['out'] .= '<input type="hidden" name="wppa-randseed" value="'.$captkey.'" />';
			}

		$wppa['out'] .= '
			<input type="submit" class="wppa-user-submit" style="margin: 6px 0; float:right; '.__wcs( 'wppa-box-text' ).'" value="'.__a( 'Create album' ).'" />
		</form>
	</div>';
}

// Frontend upload html, for use in the upload box, the widget or in the album and thumbnail box
function wppa_user_upload_html( $alb, $width, $where = '', $mcr = false ) {
global $wppa;
global $wppa_opt;

	// May I?
	if ( ! wppa_switch( 'wppa_user_upload_on' ) ) return;			// Feature not enabled
	if ( wppa_switch( 'wppa_user_upload_login' ) ) {
		if ( ! is_user_logged_in() ) return;					// Must login
	}
	if ( ! wppa_have_access( $alb ) ) {
		return;						// No album access
	}

	// Find max files for the user
	$allow_me = wppa_allow_user_uploads();
	if ( ! $allow_me ) {
		if ( wppa_switch( 'wppa_show_album_full' ) ) {
			$wppa['out'] .= '<span style="color:red">';
			$wppa['out'] .= __a( 'Max uploads reached' );
			$time = wppa_time_to_wait_html( '0', true );	// For the user
			$wppa['out'] .= $time;
			wppa_dbg_msg( 'Max for user '.wppa_get_user().' reached '.$time );
			$wppa['out'] .= '</span>';
		}
		return;													// Max quota reached
	}
	
	// Find max files for the album
	$allow_alb = wppa_allow_uploads( $alb );
	if ( ! $allow_alb ) {
		if ( wppa_switch( 'wppa_show_album_full' ) ) {
			$wppa['out'] .= '<span style="color:red">';
			$wppa['out'] .= __a( 'Max uploads reached' );
			$time = wppa_time_to_wait_html( $alb );		// For the album
			$wppa['out'] .= $time;
			wppa_dbg_msg( 'Max for album '.$alb.' reached '.$time );
			$wppa['out'] .= '</span>';
		}
		return;													// Max quota reached
	}
	
	if ( wppa_is_user_blacklisted() ) return;

	// Find max files for the system
	$allow_sys = ini_get( 'max_file_uploads' );
	
	// THE max
	if ( $allow_me == '-1' ) $allow_me = $allow_sys;
	if ( $allow_alb == '-1' ) $allow_alb = $allow_sys;
	$max = min( $allow_me, $allow_alb, $allow_sys );

	// In a widget or multi column responsive?
	$small = ( $wppa['in_widget'] == 'upload' || $mcr );

	// Ajax upload?
	$ajax_upload = wppa_switch( 'wppa_ajax_upload' );
	
	// Create the return url
	if ( $ajax_upload ) {
		$returnurl = wppa_switch('wppa_ajax_non_admin') ? WPPA_URL.'/wppa-ajax-front.php' : admin_url('admin-ajax.php');
		$returnurl .= '?action=wppa&wppa-action=do-fe-upload';
	}
	else {
		$returnurl = wppa_get_permalink();
		if ( $where == 'cover' ) {
			$returnurl .= 'wppa-album='.$alb.'&amp;wppa-cover=0&amp;wppa-occur='.$wppa['occur'];
		}
		elseif ( $where == 'thumb' ) {
			$returnurl .= 'wppa-album='.$alb.'&amp;wppa-cover=0&amp;wppa-occur='.$wppa['occur'];
		}
		elseif ( $where == 'widget' || $where == 'uploadbox' ) {
		}
		if ( $wppa['page'] ) $returnurl .= '&amp;wppa-page='.$wppa['page'];
		$returnurl = trim( $returnurl, '?' );

		$returnurl = wppa_trim_wppa_( $returnurl );
	}

	// Make the HTML
	$t = $mcr ? 'mcr-' : '';
	$wppa['out'] .= '
	<div style="clear:both"></div>
	<a id="wppa-up-'.$alb.'-'.$wppa['mocc'].'" class="wppa-upload-'.$where.'" onclick="'.
									'jQuery( \'#wppa-file-'.$t.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'block\' );'.		// Open the Upload form
									'jQuery( \'#wppa-up-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'.			// Hide the Upload link
									'jQuery( \'#wppa-cr-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'.			// Hide the Create link
									'jQuery( \'#wppa-ea-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'.			// Hide the Edit link
									'jQuery( \'#wppa-cats-' . $alb . '-' . $wppa['mocc'] . '\' ).css( \'display\',\'none\' );'.	// Hide catogory
									'_wppaDoAutocol( ' . $wppa['mocc'] . ' )'.													// Trigger autocol
									'" style="float:left; cursor:pointer;">

		'.__a( 'Upload Photo' ).'
	</a>
	<div id="wppa-file-'.$t.$alb.'-'.$wppa['mocc'].'" class="wppa-file-'.$t.$wppa['mocc'].'" style="width:'.$width.'px;text-align:center;display:none" >
		<form id="wppa-uplform-'.$alb.'-'.$wppa['mocc'].'" action="'.$returnurl.'" method="post" enctype="multipart/form-data">
			'.wp_nonce_field( 'wppa-check' , 'wppa-nonce', false, false );		
			if ( ! $alb ) {	// No album given: select one
				$wppa['out'] .= '
			<select id="wppa-upload-'.$wppa['mocc'].'" name="wppa-upload-album" style="float:left; max-width: '.$width.'px; ">
				'.wppa_album_select_a( array ( 'addpleaseselect' => true, 'checkaccess' => true, 'checkupload' => true, 'path' => wppa_switch( 'wppa_hier_albsel' ) ) ).'
			</select>
			<br />';
			}
			else {
				$wppa['out'] .= '
			<input type="hidden" id="wppa-upload-'.$wppa['mocc'].'" name="wppa-upload-album" value="'.$alb.'" />';
			}

			if ( wppa_switch( 'wppa_upload_one_only' ) && ! current_user_can( 'administrator' ) ) {
				$wppa['out'] .= '
			<input type="file" capture="camera" class="wppa-user-file" style="max-width: '.$width.'; margin: 6px 0; float:left; '.__wcs( 'wppa-box-text' ).'" id="wppa-user-upload-'.$alb.'-'.$wppa['mocc'].'" name="wppa-user-upload-'.$alb.'-'.$wppa['mocc'].'[]" onchange="jQuery( \'#wppa-user-submit-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\', \'block\' )" />';
			}
			else {
				$wppa['out'] .= '
			<input type="file" capture="camera" multiple="multiple" class="wppa-user-file" style="max-width: '.$width.'; margin: 6px 0; float:left; '.__wcs( 'wppa-box-text' ).'" id="wppa-user-upload-'.$alb.'-'.$wppa['mocc'].'" name="wppa-user-upload-'.$alb.'-'.$wppa['mocc'].'[]" onchange="jQuery( \'#wppa-user-submit-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\', \'block\' )" />';
			}
			
			$onclick = $alb ? '' : ' onclick="if ( document.getElementById( \'wppa-upload-'.$wppa['mocc'].'\' ).value == 0 ) {alert( \''.esc_js( __a( 'Please select an album and try again' ) ).'\' );return false;}"';

			$wppa['out'] .= '
			<input type="submit" id="wppa-user-submit-'.$alb.'-'.$wppa['mocc'].'"'.$onclick.' style="display:none; margin: 6px 0; float:right; '.__wcs( 'wppa-box-text' ).'" class="wppa-user-submit" name="wppa-user-submit-'.$alb.'-'.$wppa['mocc'].'" value="'.__a( 'Upload photo' ).'" />
			<div style="clear:both"></div>';

			// if ajax: progression bar
			if ( $ajax_upload ) {
				$wppa['out'] .= '
				<div id="progress-'.$alb.'-'.$wppa['mocc'].'" class="wppa-progress" >
					<div id="bar-'.$alb.'-'.$wppa['mocc'].'" class="wppa-bar" ></div>
					<div id="percent-'.$alb.'-'.$wppa['mocc'].'" class="wppa-percent" >0%</div >
				</div>
			 
				<div id="message-'.$alb.'-'.$wppa['mocc'].'" class="wppa-message" ></div>';
			}
			
			if ( ! wppa_switch( 'wppa_upload_one_only' ) && ! current_user_can( 'administrator' ) ) {
				if ( $max ) $wppa['out'] .= '
			<span style="font-size:10px;" >
				'.sprintf( __a( 'You may upload up to %s photos at once if your browser supports HTML-5 multiple file upload' ), $max ).'
			</span>';
				$maxsize = wppa_check_memory_limit( false );
				if ( is_array( $maxsize ) ) $wppa['out'] .= '
			<br />
			<span style="font-size:10px;" >
				'.sprintf( __a( 'Max photo size: %d x %d (%2.1f MegaPixel)' ), $maxsize['maxx'], $maxsize['maxy'], $maxsize['maxp']/( 1024*1024 ) ).'
			</span>';
			}
			
			if ( wppa_switch( 'wppa_copyright_on' ) ) $wppa['out'] .= '
			<div id="wppa-copyright-'.$wppa['mocc'].'" style="clear:both;" >
				'.__( $wppa_opt['wppa_copyright_notice'] ).'
			</div>';
			
			// Watermark
			if ( wppa_switch( 'wppa_watermark_on' ) && ( wppa_switch( 'wppa_watermark_user' ) || current_user_can( 'wppa_settings' ) ) ) { 
				$wppa['out'] .= '
			<table class="wppa-watermark wppa-box-text" style="margin:0; border:0; '.__wcs( 'wppa-box-text' ).'" >
				<tbody>
					<tr valign="top" style="border: 0 none; " >
						<td class="wppa-box-text wppa-td" style="'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >
							'.__a( 'Apply watermark file:' ).'
						</td>
					</tr>
					<tr>
						<td class="wppa-box-text wppa-td" style="width: '.$width.';'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >
							<select style="margin:0; padding:0; text-align:left; width:auto; " name="wppa-watermark-file" id="wppa-watermark-file">
								'.wppa_watermark_file_select().'
							</select>
						</td>
					</tr>
					<tr valign="top" style="border: 0 none; " >
						<td class="wppa-box-text wppa-td" style="width: '.$width.';'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >
							'.__a( 'Position:' ).'
						</td>';
						if ( $small ) $wppa['out'] .= '</tr><tr>';
						$wppa['out'] .= '
						<td class="wppa-box-text wppa-td" style="width: '.$width.';'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >
							<select style="margin:0; padding:0; text-align:left; width:auto; " name="wppa-watermark-pos" id="wppa-watermark-pos">
								'.wppa_watermark_pos_select().'
							</select>
						</td>
					</tr>
				</tbody>
			</table>';
			}
			
		// Name
		switch ( $wppa_opt['wppa_newphoto_name_method'] ) {
			case 'none':
				$expl = '';
				break;
			case '2#005':
				$expl = __a( 'If you leave this blank, iptc tag 005 (Graphic name) will be used as photoname if available, else the original filename will be used as photo name.' );
				break;
			case '2#120':
				$expl = __a( 'If you leave this blank, iptc tag 120 (Caption) will be used as photoname if available, else the original filename will be used as photo name.' );
				break;
			default:
				$expl = __a( 'If you leave this blank, the original filename will be used as photo name.' );
		}
		$wppa['out'] .= '
			<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.
				__a( 'Enter photo name.' ).'&nbsp;<span style="font-size:10px;" >'.$expl.'</span>'.'
			</div>
			<input type="text" class="wppa-box-text wppa-file-'.$t.$wppa['mocc'].'" style="padding:0; width:'.( $width-6 ).'px; '.__wcs( 'wppa-box-text' ).'" name="wppa-user-name" />';

		// Description
		$desc = wppa_switch( 'wppa_apply_newphoto_desc_user' ) ? stripslashes( $wppa_opt['wppa_newphoto_description'] ) : '';
		$wppa['out'] .= '
			<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.
				__a( 'Enter/modify photo description' ).'
			</div>
			<textarea class="wppa-user-textarea wppa-box-text wppa-file-'.$t.$wppa['mocc'].'" style="height:120px; width:'.( $width-6 ).'px; '.__wcs( 'wppa-box-text' ).'" name="wppa-user-desc" >'.$desc.'</textarea>
		</form>
	</div>';
	
	// Ajax upload script
	if ( $ajax_upload ) {
		$wppa['out'] .= '
		<script>
			jQuery(document).ready(function() {
	 
				var options = {
					beforeSend: function() {
						jQuery("#progress-'.$alb.'-'.$wppa['mocc'].'").show();
						//clear everything
						jQuery("#bar-'.$alb.'-'.$wppa['mocc'].'").width(\'0%\');
						jQuery("#message-'.$alb.'-'.$wppa['mocc'].'").html("");
						jQuery("#percent-'.$alb.'-'.$wppa['mocc'].'").html("");
					},
					uploadProgress: function(event, position, total, percentComplete) {
						jQuery("#bar-'.$alb.'-'.$wppa['mocc'].'").width(percentComplete+\'%\');
						if ( percentComplete < 95 ) {
							jQuery("#percent-'.$alb.'-'.$wppa['mocc'].'").html(percentComplete+\'%\');
						}
						else {
							jQuery("#percent-'.$alb.'-'.$wppa['mocc'].'").html(\'Processing...\');
						}
					},
					success: function() {
						jQuery("#bar-'.$alb.'-'.$wppa['mocc'].'").width(\'100%\');
						jQuery("#percent-'.$alb.'-'.$wppa['mocc'].'").html(\'Done!\');
					},
					complete: function(response) {
						jQuery("#message-'.$alb.'-'.$wppa['mocc'].'").html( \'<span style="font-size: 10px;" >\'+response.responseText+\'</span>\' );'.
						( $where == 'thumb' ? 'document.location.reload(true)' : '' ).'
					},
					error: function() {
						jQuery("#message-'.$alb.'-'.$wppa['mocc'].'").html( \'<span style="color: red;" >'.__a( 'ERROR: unable to upload files.' ).'</span>\' );
					}
				};
	 
				jQuery("#wppa-uplform-'.$alb.'-'.$wppa['mocc'].'").ajaxForm(options);
			});
		</script>';
	}
}

// Frontend edit album info
function wppa_user_albumedit_html( $alb, $width, $where = '', $mcr = false ) {
global $wppa;
global $wppa_opt;

	$album = wppa_cache_album( $alb );

	if ( ! wppa_switch( 'wppa_user_album_edit_on' ) ) return; 	// Feature not enabled
	if ( ! $alb ) return;										// No album given
	if ( ! wppa_have_access( $alb ) ) return;					// No rights
	if ( $album['owner'] == '--- public ---' && ! current_user_can( 'wppa_admin' ) ) return;	// Public albums are not publicly editable
	
	$t = $mcr ? 'mcr-' : '';
	
	// Create the return url
	$returnurl = wppa_get_permalink();
	if ( $where == 'cover' ) {
		$returnurl .= 'wppa-album='.$alb.'&amp;wppa-cover=1&amp;wppa-occur='.$wppa['occur'];
	}
	elseif ( $where == 'thumb' ) {
		$returnurl .= 'wppa-album='.$alb.'&amp;wppa-cover=0&amp;wppa-occur='.$wppa['occur'];
	}
	elseif ( $where == 'widget' || $where == 'uploadbox' ) {
	}
	if ( $wppa['page'] ) $returnurl .= '&amp;wppa-page='.$wppa['page'];
	$returnurl = trim( $returnurl, '?' );

		
	$result = '
	<div style="clear:both;"></div>
	<a id="wppa-ea-'.$alb.'-'.$wppa['mocc'].'" style="cursor:pointer" onclick="'.
									'jQuery( \'#wppa-fe-div-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'block\' );'.		// Open the Edit form
									'jQuery( \'#wppa-ea-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'.			// Hide the Edit link
									'jQuery( \'#wppa-cr-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'.			// Hide the Create libk
									'jQuery( \'#wppa-up-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'.			// Hide the upload link
									'jQuery( \'#wppa-cats-' . $alb . '-' . $wppa['mocc'] . '\' ).css( \'display\',\'none\' );'.	// Hide catogory
									'_wppaDoAutocol( ' . $wppa['mocc'] . ' )'.													// Trigger autocol
									'" >'.
		__a( 'Edit albuminfo' ).'
	</a>
	<div id="wppa-fe-div-'.$alb.'-'.$wppa['mocc'].'" style="display:none;" >
		<form action="'.$returnurl.'" method="post">
			<input type="hidden" name="wppa-albumeditnonce" id="album-nonce-'.$wppa['mocc'].'-'.$alb.'" value="'.wp_create_nonce( 'wppa_nonce_'.$alb ).'" />
			<input type="hidden" name="wppa-albumeditid" id="wppaalbum-id-'.$wppa['mocc'].'-'.$alb.'" value="'.$alb.'" />
			<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.
				__a( 'Enter album name.' ).'&nbsp;<span style="font-size:10px;" >'.__a( 'Don\'t leave this blank!' ).'</span>
			</div>
			<input name="wppa-albumeditname" id="wppaalbum-name-'.$wppa['mocc'].'-'.$alb.'" class="wppa-box-text wppa-file-'.$t.$wppa['mocc'].'" value="'.esc_attr( stripslashes( $album['name'] ) ).'" style="padding:0; width:'.( $width-6 ).'px; '.__wcs( 'wppa-box-text' ).'" />
			<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.
				__a( 'Album description:' ).'
			</div>
			<textarea name="wppa-albumeditdesc" id="wppaalbum-desc-'.$wppa['mocc'].'-'.$alb.'" class="wppa-user-textarea wppa-box-text wppa-file-'.$t.$wppa['mocc'].'" style="height:120px; width:'.( $width-6 ).'px; '.__wcs( 'wppa-box-text' ).'" >'.esc_textarea( stripslashes( $album['description'] ) ).'</textarea>
			<input type="submit" name="wppa-albumeditsubmit" class="wppa-user-submit" style="margin: 6px 0; float:right; '.__wcs( 'wppa-box-text' ).'" value="'.__a( 'Update album' ).'" />
		</form>
	</div>';
	$wppa['out'] .= $result;
}

// Build the html for the comment box
function wppa_comment_html( $id, $comment_allowed ) {
global $wpdb;
global $wppa;
global $wppa_opt;
global $current_user;
global $wppa_first_comment_html;

	$result = '';
	if ( $wppa['in_widget'] ) return $result;		// NOT in a widget
	
	// Find out who we are either logged in or not
	$vis = is_user_logged_in() ? $vis = 'display:none; ' : '';
	if ( !$wppa_first_comment_html ) {
		$wppa_first_comment_html = true;
		// Find user
		if ( wppa_get_post( 'comname' ) ) $wppa['comment_user'] = wppa_get_post( 'comname' );
		if ( wppa_get_post( 'comemail' ) ) $wppa['comment_email'] = wppa_get_post( 'comemail' );
		elseif ( is_user_logged_in() ) {
			get_currentuserinfo();
			$wppa['comment_user'] = $current_user->display_name; //user_login;
			$wppa['comment_email'] = $current_user->user_email;
		}
	}

	// Loop the comments already there
	$n_comments = 0;
	if ( wppa_switch( 'wppa_comments_desc' ) ) $ord = 'DESC'; else $ord = '';
	$comments = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.WPPA_COMMENTS.' WHERE photo = %s ORDER BY id '.$ord, $id ), ARRAY_A );
	wppa_dbg_q( 'Q-Comm' );
	$com_count = count( $comments );
	$color = 'darkgrey';
	if ( $wppa_opt['wppa_fontcolor_box'] ) $color = $wppa_opt['wppa_fontcolor_box'];
	if ( $comments ) {
		$result .= '<div id="wppa-comtable-wrap-'.$wppa['mocc'].'" style="display:none;" >';
			$result .= '<table id="wppacommentstable-'.$wppa['mocc'].'" class="wppa-comment-form" style="margin:0; "><tbody>';
			foreach( $comments as $comment ) {
				// Show a comment either when it is approved, or it is pending and mine or i am a moderator
				if ( $comment['status'] == 'approved' || current_user_can( 'wppa_moderate' ) || current_user_can( 'wppa_comments' ) || ( ( $comment['status'] == 'pending' || $comment['status'] == 'spam' ) && $comment['user'] == $wppa['comment_user'] ) ) {
					$n_comments++;
					$result .= '<tr class="wppa-comment-'.$comment['id'].'" valign="top" style="border-bottom:0 none; border-top:0 none; border-left: 0 none; border-right: 0 none; " >';
						$result .= '<td valign="top" class="wppa-box-text wppa-td" style="vertical-align:top; width:30%; border-width: 0 0 0 0; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >';
							$result .= $comment['user'].' '.__a( 'wrote:' );
							$result .= '<br /><span style="font-size:9px; ">'.wppa_get_time_since( $comment['timestamp'] ).'</span>';
							if ( $wppa_opt['wppa_comment_gravatar'] != 'none' ) {
								// Find the default
								if ( $wppa_opt['wppa_comment_gravatar'] != 'url' ) {
									$default = $wppa_opt['wppa_comment_gravatar'];
								}
								else {
									$default = $wppa_opt['wppa_comment_gravatar_url'];
								}
								// Find the avatar
								$avt = '';
								$usr = get_user_by( 'login', $comment['user'] );
								if ( $usr ) {	// Local Avatar ?
									$avt = str_replace( "'", "\"", get_avatar( $usr->ID, $wppa_opt['wppa_gravatar_size'], $default ) );
								}
								if ( $avt == '' ) {	// Global avatars off, try myself
									$avt = '<img class="wppa-box-text wppa-td" src="http://www.gravatar.com/avatar/'.md5( strtolower( trim( $comment['email'] ) ) ).'.jpg?d='.urlencode( $default ).'&s='.$wppa_opt['wppa_gravatar_size'].'" alt="'.__a('Avatar').'" />';
								}
								// Compose the html
								$result .= '<div class="com_avatar">'.$avt.'</div>';
							}
						$result .= '</td>';
						$txtwidth = floor( wppa_get_container_width() * 0.7 ).'px';
						$result .= '<td class="wppa-box-text wppa-td" style="width:70%; word-wrap:break-word; border-width: 0 0 0 0;'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.
										'<p class="wppa-comment-textarea wppa-comment-textarea-'.$wppa['mocc'].'" style="margin:0; background-color:transparent; width:'.$txtwidth.'; max-height:90px; overflow:auto; word-wrap:break-word;'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.
											html_entity_decode( esc_js( stripslashes( convert_smilies( $comment['comment'] ) ) ) );
										
											if ( $comment['status'] != 'approved' && ( current_user_can( 'wppa_moderate' ) || current_user_can( 'wppa_comments' ) ) ) {
												if ( $wppa['no_esc'] ) $result .= wppa_moderate_links( 'comment', $id, $comment['id'] );
												else $result .= wppa_html( esc_js( wppa_moderate_links( 'comment', $id, $comment['id'] ) ) );
											}
											elseif ( $comment['status'] == 'pending' && $comment['user'] == $wppa['comment_user'] ) {
												$result .= '<br /><span style="color:red; font-size:9px;" >'.__a( 'Awaiting moderation' ).'</span>';
											}
											elseif ( $comment['status'] == 'spam' && $comment['user'] == $wppa['comment_user'] ) {
												$result .= '<br /><span style="color:red; font-size:9px;" >'.__a( 'Marked as spam' ).'</span>';
											}
											
											
											$result .= '</p>';
						$result .= '</td>';
					$result .= '</tr>';
					$result .= '<tr class="wppa-comment-'.$comment['id'].'"><td colspan="2" style="padding:0"><hr style="background-color:'.$color.'; margin:0;" /></td></tr>';
				}
			}
			$result .= '</tbody></table>';
		$result .= '</div>';
	}
	
	// See if we are currently in the process of adding/editing this comment
	$is_current = ( $id == $wppa['comment_photo'] && $wppa['comment_id'] );
	// $debugtext=' ( id='.$id.', comment_photo='.$wppa['comment_photo'].', comment_id='.$wppa['comment_id'].' )';
	if ( $is_current ) {
		$txt = $wppa['comment_text'];
		$btn = __a( 'Edit!' );
	}
	else {
		$txt = '';
		$btn = __a( 'Send!' );
	}
	
	// Prepare the callback url
	$returnurl = wppa_get_permalink();

	$album = wppa_get_get( 'album' );
	if ( $album !== false ) $returnurl .= 'wppa-album='.$album.'&';
	$cover = wppa_get_get( 'cover' );
	if ( $cover ) $returnurl .= 'wppa-cover='.$cover.'&';
	$slide = wppa_get_get( 'slide' );
	if ( $slide !== false ) $returnurl .= 'wppa-slide&';
	$occur = wppa_get_get( 'occur' );
	if ( $occur ) $returnurl .= 'wppa-occur='.$occur.'&';
	$lasten = wppa_get_get( 'lasten' );
	if ( $lasten ) $returnurl .= 'wppa-lasten='.$lasten.'&';
	$topten = wppa_get_get( 'topten' );
	if ( $topten ) $returnurl .= 'wppa-topten='.$topten.'&';
	$comten = wppa_get_get( 'comten' );
	if ( $comten ) $returnurl .= 'wppa-comten='.$comten.'&';
	$tag = wppa_get_get( 'tag' );
	if ( $tag ) $returnurl .= 'wppa-tag='.$tag.'&';
	
	$returnurl .= 'wppa-photo='.$id;
	
	// The comment form
	if ( $comment_allowed ) {
		$result .= '<div id="wppa-comform-wrap-'.$wppa['mocc'].'" style="display:none;" >';
			$result .= '<form id="wppa-commentform-'.$wppa['mocc'].'" class="wppa-comment-form" action="'.$returnurl.'" method="post" style="" onsubmit="return wppaValidateComment( '.$wppa['mocc'].' )">';
				$result .= wp_nonce_field( 'wppa-check' , 'wppa-nonce-'.$wppa['mocc'], false, false );
				if ( $album ) $result .= '<input type="hidden" name="wppa-album" value="'.$album.'" />';
				if ( $cover ) $result .= '<input type="hidden" name="wppa-cover" value="'.$cover.'" />';
				if ( $slide ) $result .= '<input type="hidden" name="wppa-slide" value="'.$slide.'" />';
				if ( $is_current ) $result .= '<input type="hidden" id="wppa-comment-edit-'.$wppa['mocc'].'" name="wppa-comment-edit" value="'.$wppa['comment_id'].'" />';
				$result .= '<input type="hidden" name="wppa-occur" value="'.$wppa['occur'].'" />';

				$result .= '<table id="wppacommenttable-'.$wppa['mocc'].'" style="margin:0;">';
					$result .= '<tbody>';
						$result .= '<tr valign="top" style="'.$vis.'">';
							$result .= '<td class="wppa-box-text wppa-td" style="width:30%; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.__a( 'Your name:' ).'</td>';
							$result .= '<td class="wppa-box-text wppa-td" style="width:70%; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" ><input type="text" name="wppa-comname" id="wppa-comname-'.$wppa['mocc'].'" style="width:100%; " value="'.$wppa['comment_user'].'" /></td>';
						$result .= '</tr>';
						if ( wppa_switch( 'wppa_comment_email_required' ) ) {
							$result .= '<tr valign="top" style="'.$vis.'">';
								$result .= '<td class="wppa-box-text wppa-td" style="width:30%; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.__a( 'Your email:' ).'</td>';
								$result .= '<td class="wppa-box-text wppa-td" style="width:70%; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" ><input type="text" name="wppa-comemail" id="wppa-comemail-'.$wppa['mocc'].'" style="width:100%; " value="'.$wppa['comment_email'].'" /></td>';
							$result .= '</tr>';
						}
						$result .= '<tr valign="top" style="vertical-align:top;">';	
							$result .= '<td valign="top" class="wppa-box-text wppa-td" style="vertical-align:top; width:30%; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.__a( 'Your comment:' ).'<br />'.$wppa['comment_user'].'<br />';
							if ( wppa_switch( 'wppa_comment_captcha' ) ) {
								$wid = '20%';
								if ( $wppa_opt['wppa_fontsize_box'] ) $wid = ( $wppa_opt['wppa_fontsize_box'] * 1.5 ).'px';
								$captkey = $id;
								if ( $is_current ) $captkey = $wpdb->get_var( $wpdb->prepare( 'SELECT `timestamp` FROM `'.WPPA_COMMENTS.'` WHERE `id` = %s', $wppa['comment_id'] ) );
								wppa_dbg_q( 'Q-Com-ts' );								
								$result .= wppa_make_captcha( $captkey ).'<input type="text" id="wppa-captcha-'.$wppa['mocc'].'" name="wppa-captcha" style="width:'.$wid.'; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" />&nbsp;';
							}
// orig							$result .= '<input type="submit" name="commentbtn" value="'.$btn.'" style="margin:0;" /></td>';
							$result .= '<input type="button" name="commentbtn" onclick="wppaAjaxComment( '.$wppa['mocc'].', '.$id.' )" value="'.$btn.'" style="margin:0 4px 0 0;" />';
							$result .= '<img id="wppa-comment-spin-'.$wppa['mocc'].'" src="'.wppa_get_imgdir().'wpspin.gif" style="display:none;" />';
							$result .= '</td>';
							$result .= '<td valign="top" class="wppa-box-text wppa-td" style="vertical-align:top; width:70%; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >';
/*							if ( wppa_switch( 'wppa_use_wp_editor' ) ) {
								$quicktags_settings = array( 'buttons' => 'strong,em,link,block,ins,ul,ol,li,code,close' );
								ob_start();
								wp_editor( stripslashes( $txt ), 'wppacomment'.wppa_alfa_id( $id ), array( 'wpautop' => false, 'media_buttons' => false, 'textarea_rows' => '6', 'tinymce' => false, 'quicktags' => $quicktags_settings ) );
								$editor = ob_get_clean();
								$result .= str_replace( "'", '"', $editor );
							}
							else {
/**/
								if ( wppa_switch( 'wppa_comment_smiley_picker' ) ) $result .= wppa_get_smiley_picker_html( 'wppa-comment-'.$wppa['mocc'] );
								$result .= '<textarea name="wppa-comment" id="wppa-comment-'.$wppa['mocc'].'" style="height:60px; width:100%; ">'.esc_textarea( stripslashes( $txt ) ).'</textarea>';
/*							}
/* */
							$result .= '</td>';
						$result .= '</tr>';
					$result .= '</tbody>';
				$result .= '</table>';
			$result .= '</form>';
			// $result.=$debugtext;
		$result .= '</div>';
	}
	else {
		$result .= sprintf( __a( 'You must <a href="%s">login</a> to enter a comment' ), site_url( 'wp-login.php', 'login' ) );
	}
	
	$result .= '<div id="wppa-comfooter-wrap-'.$wppa['mocc'].'" style="display:block;" >';
		$result .= '<table id="wppacommentfooter-'.$wppa['mocc'].'" class="wppa-comment-form" style="margin:0;">';
			$result .= '<tbody><tr style="text-align:center; "><td style="text-align:center; cursor:pointer;'.__wcs( 'wppa-box-text' ).'" ><a onclick="wppaOpenComments( '.$wppa['mocc'].', -1 ); return false;">'; // wppaStartStop( '.$wppa['mocc'].', -1 ); return false;">';
			if ( $n_comments ) {
				$result .= sprintf( __a( '%d comments' ), $n_comments );
			}
			else {
				if ( $comment_allowed ) {
					$result .= __a( 'Leave a comment' );
				}
			}
		$result .= '</a></td></tr></tbody></table>';
	$result .= '</div><div style="clear:both"></div>';

	return $result;
}

function wppa_get_smiley_picker_html( $elm_id ) {
static $wppa_smilies;
global $wpsmiliestrans;

	// Fill inverted smilies array if needed
	if ( ! is_array( $wppa_smilies ) ) {
		foreach( array_keys( $wpsmiliestrans ) as $idx ) {
			if ( ! isset ( $wppa_smilies[$wpsmiliestrans[$idx]] ) ) {
				$wppa_smilies[$wpsmiliestrans[$idx]] = $idx;
			}
		}
	}
	
	// Make the html
	$result = '';
	foreach ( array_keys( $wppa_smilies ) as $key ) {
		$onclick = esc_attr( 'wppaInsertAtCursor( document.getElementById( "'.$elm_id.'" ), " '.$wppa_smilies[$key].' " )' );
		$title = substr( substr( $key, 5 ), 0, -4 );
		$result .= '<img src="'.esc_attr( includes_url( 'images/smilies/' ).$key ).'" onclick="'.$onclick.'" title="'.$title.'" style="display:inline;" /> ';
	}
	
	return $result;
} 

// IPTC box
function wppa_iptc_html( $photo ) {
global $wppa;
global $wpdb;
global $wppaiptcdefaults;
global $wppaiptclabels;

	// Get the default ( one time only )
	if ( ! $wppa['iptc'] ) {
		$tmp = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_IPTC."` WHERE `photo` = %s ORDER BY `tag`", '0' ), "ARRAY_A" );
		wppa_dbg_q( 'Q-iptc0' );
		if ( ! $tmp ) return '';	// Nothing defined
		$wppaiptcdefaults = false;	// Init
		$wppaiptclabels = false;	// Init
		foreach ( $tmp as $t ) {
			$wppaiptcdefaults[$t['tag']] = $t['status'];
			$wppaiptclabels[$t['tag']] = $t['description'];
		}
		$wppa['iptc'] = true;
	}

	$count = 0;

	// Get the photo data
	$iptcdata = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_IPTC."` WHERE `photo` = %s ORDER BY `tag`", $photo ), "ARRAY_A" );
	wppa_dbg_q( 'Q-iptc' );
	if ( $iptcdata ) {
		// Open the container content
		$result = '<div id="iptccontent-'.$wppa['mocc'].'" >';
		// Open or closed?
		$d1 = wppa_switch( 'wppa_show_iptc_open' ) ? 'display:none;' : 'display:inline;';
		$d2 = wppa_switch( 'wppa_show_iptc_open' ) ? 'display:inline;' : 'display:none;';
		// Process data
		$onclick = esc_attr( "wppaStopShow( ".$wppa['mocc']." ); jQuery( '.wppa-iptc-table-".$wppa['mocc']."' ).css( 'display', '' ); jQuery( '.-wppa-iptc-table-".$wppa['mocc']."' ).css( 'display', 'none' )" );
		$result .= '<a class="-wppa-iptc-table-'.$wppa['mocc'].'" onclick="'.$onclick.'" style="cursor:pointer;'.$d1.'" >'.__a( 'Show IPTC data' ).'</a>';

		$onclick = esc_attr( "jQuery( '.wppa-iptc-table-".$wppa['mocc']."' ).css( 'display', 'none' ); jQuery( '.-wppa-iptc-table-".$wppa['mocc']."' ).css( 'display', '' )" );
		$result .= '<a class="wppa-iptc-table-'.$wppa['mocc'].'" onclick="'.$onclick.'" style="cursor:pointer;'.$d2.'" >'.__a( 'Hide IPTC data' ).'</a>';

		$result .= '<div style="clear:both;" ></div><table class="wppa-iptc-table-'.$wppa['mocc'].' wppa-detail" style="border:0 none; margin:0;'.$d2.'" ><tbody>';
		$oldtag = '';
		foreach ( $iptcdata as $iptcline ) {
			if ( $iptcline['status'] == 'hide' ) continue;														// Photo status is hide
			if ( $iptcline['status'] == 'default' && $wppaiptcdefaults[$iptcline['tag']] == 'hide' ) continue;	// P s is default and default is hide
			if ( $iptcline['status'] == 'default' && $wppaiptcdefaults[$iptcline['tag']] == 'option' && ! trim( $iptcline['description'], "\x00..\x1F " ) ) continue;	// P s is default and default is optional and field is empty
			
			$count++;
			$newtag = $iptcline['tag'];
			if ( $newtag != $oldtag && $oldtag != '' ) $result .= '</td></tr>';	// Close previous line
			if ( $newtag == $oldtag ) {
				$result .= '; ';							// next item with same tag
			}
			else {
				$result .= '<tr style="border-bottom:0 none; border-top:0 none; border-left: 0 none; border-right: 0 none; "><td class="wppa-iptc-label wppa-box-text wppa-td" style="'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >';						// Open new line
				$result .= esc_js( __( $wppaiptclabels[$newtag] ) );
				$result .= '</td><td class="wppa-iptc-value wppa-box-text wppa-td" style="'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >';
			}
			$result .= esc_js( trim( __( $iptcline['description'] ) ) );
			$oldtag = $newtag;
		}	
		if ( $oldtag != '' ) $result .= '</td></tr>';	// Close last line
		$result .= '</tbody></table></div>';
	}
	if ( ! $count ) {
		$result = '<div id="iptccontent-'.$wppa['mocc'].'" >'.__a( 'No IPTC data' ).'</div>';
	}

	return ( $result );
}

// EXIF box
function wppa_exif_html( $photo ) {
global $wppa;
global $wpdb;
global $wppaexifdefaults;
global $wppaexiflabels;

	// Get the default ( one time only )
	if ( ! $wppa['exif'] ) {
		$tmp = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_EXIF."` WHERE `photo` = %s ORDER BY `tag`", '0' ), "ARRAY_A" );
		wppa_dbg_q( 'Q-exif0' );
		if ( ! $tmp ) return '';	// Nothing defined
		$wppaexifdefaults = false;	// Init
		$wppaexiflabels = false;	// Init
		foreach ( $tmp as $t ) {
			$wppaexifdefaults[$t['tag']] = $t['status'];
			$wppaexiflabels[$t['tag']] = $t['description'];
		}
		$wppa['exif'] = true;
	}

	$count = 0;

	// Get the photo data
	$exifdata = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_EXIF."` WHERE `photo`=%s ORDER BY `tag`", $photo ), "ARRAY_A" );
	wppa_dbg_q( 'Q-exif' );
	if ( $exifdata ) {
		// Open the container content
		$result = '<div id="exifcontent-'.$wppa['mocc'].'" >';
		// Open or closed?
		$d1 = wppa_switch( 'wppa_show_exif_open' ) ? 'display:none;' : 'display:inline;';
		$d2 = wppa_switch( 'wppa_show_exif_open' ) ? 'display:inline;' : 'display:none;';
		// Process data
		$onclick = esc_attr( "wppaStopShow( ".$wppa['mocc']." ); jQuery( '.wppa-exif-table-".$wppa['mocc']."' ).css( 'display', '' ); jQuery( '.-wppa-exif-table-".$wppa['mocc']."' ).css( 'display', 'none' )" );
		$result .= '<a class="-wppa-exif-table-'.$wppa['mocc'].'" onclick="'.$onclick.'" style="cursor:pointer;'.$d1.'" >'.__a( 'Show EXIF data' ).'</a>';

		$onclick = esc_attr( "jQuery( '.wppa-exif-table-".$wppa['mocc']."' ).css( 'display', 'none' ); jQuery( '.-wppa-exif-table-".$wppa['mocc']."' ).css( 'display', '' )" );
		$result .= '<a class="wppa-exif-table-'.$wppa['mocc'].'" onclick="'.$onclick.'" style="cursor:pointer;'.$d2.'" >'.__a( 'Hide EXIF data' ).'</a>';

		$result .= '<div style="clear:both;" ></div><table class="wppa-exif-table-'.$wppa['mocc'].' wppa-detail" style="'.$d2.' border:0 none; margin:0;" ><tbody>';
		$oldtag = '';
		foreach ( $exifdata as $exifline ) {
			if ( $exifline['status'] == 'hide' ) continue;														// Photo status is hide
			if ( $exifline['status'] == 'default' && $wppaexifdefaults[$exifline['tag']] == 'hide' ) continue;	// P s is default and default is hide
			if ( $exifline['status'] == 'default' && $wppaexifdefaults[$exifline['tag']] == 'option' && ! trim( $exifline['description'], "\x00..\x1F " ) ) continue; // P s is default and default is optional and field is empty

			$count++;
			$newtag = $exifline['tag'];
			if ( $newtag != $oldtag && $oldtag != '' ) $result .= '</td></tr>';	// Close previous line
			if ( $newtag == $oldtag ) {
				$result .= '; ';							// next item with same tag
			}
			else {
				$result .= '<tr style="border-bottom:0 none; border-top:0 none; border-left: 0 none; border-right: 0 none; "><td class="wppa-exif-label wppa-box-text wppa-td" style="'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >';						// Open new line
				$result .= esc_js( __( $wppaexiflabels[$newtag] ) );
				$result .= '</td><td class="wppa-exif-value wppa-box-text wppa-td" style="'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >';
			}
			$result .= esc_js( trim( __( wppa_format_exif( $exifline['tag'], $exifline['description'] ) ) ) );
			$oldtag = $newtag;
		}	
		if ( $oldtag != '' ) $result .= '</td></tr>';	// Close last line
		$result .= '</tbody></table></div>';
	}
	if ( ! $count ) {
		$result = '<div id="exifcontent-'.$wppa['mocc'].'" >'.__a( 'No EXIF data' ).'</div>';
	}
	
	return ( $result );
}

// Display the album name ( on a thumbnail display ) either on top or at the bottom of the thumbnail area
function wppa_album_name( $key ) {
global $wppa;
global $wppa_opt;
global $wpdb;

	if ( $wppa['is_upldr'] ) return;
	if ( strlen( $wppa['start_album'] ) > '0' && ! wppa_is_int( $wppa['start_album'] ) ) return; // Album enumeration
	
	$result = '';
	if ( $wppa_opt['wppa_albname_on_thumbarea'] == $key && $wppa['current_album'] ) {
		$name = wppa_get_album_name( $wppa['current_album'] );
		if ( $key == 'top' ) {
			$result .= '<h3 id="wppa-albname-'.$wppa['mocc'].'" class="wppa-box-text wppa-black" style="padding-right:6px; margin:0; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-black' ).'" >'.$name.'</h3><div style="clear:both" ></div>';
		}
		if ( $key == 'bottom' ) {
			$result .= '<h3 id="wppa-albname-b-'.$wppa['mocc'].'" class="wppa-box-text wppa-black" style="clear:both; padding-right:6px; margin:0; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-black' ).'" >'.$name.'</h3>';
		}
	}
	$wppa['out'] .= $result;
}

// Display the album description ( on a thumbnail display ) either on top or at the bottom of the thumbnail area
function wppa_album_desc( $key ) {
global $wppa;
global $wppa_opt;
global $wpdb;

	if ( $wppa['is_upldr'] ) return;
	if ( strlen( $wppa['start_album'] ) > '0' && ! wppa_is_int( $wppa['start_album'] ) ) return; // Album enumeration
	
	$result = '';
	if ( $wppa_opt['wppa_albdesc_on_thumbarea'] == $key && $wppa['current_album'] ) {
		$desc = wppa_get_album_desc( $wppa['current_album'] );
		if ( $key == 'top' ) {
			$result .= '<div id="wppa-albdesc-'.$wppa['mocc'].'" class="wppa-box-text wppa-black" style="padding-right:6px;'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-black' ).'" >'.$desc.'</div><div style="clear:both" ></div>';
		}
		if ( $key == 'bottom' ) {
			$result .= '<div id="wppa-albdesc-b-'.$wppa['mocc'].'" class="wppa-box-text wppa-black" style="clear:both; padding-right:6px;'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-black' ).'" >'.$desc.'</div>';
		}
	}
	$wppa['out'] .= $result;
}

function wppa_auto_page_links( $where ) {
global $wppa_opt;
global $wppa;
global $wpdb;

	$m = $where == 'bottom' ? 'margin-top:8px;' : '';
	$mustwhere = $wppa_opt['wppa_auto_page_links'];
	if ( ( $mustwhere == 'top' || $mustwhere == 'both' ) && ( $where == 'top' ) || ( ( $mustwhere == 'bottom' || $mustwhere == 'both' ) && ( $where == 'bottom' ) ) ) {
		$wppa['out'] .= '
			<div id="prevnext1-'.$wppa['mocc'].'" class="wppa-box wppa-nav wppa-nav-text" style="text-align: center; '.__wcs( 'wppa-box' ).__wcs( 'wppa-nav' ).__wcs( 'wppa-nav-text' ).$m.'">';
		$photo = $wppa['single_photo'];
		$thumb = wppa_cache_thumb( $photo );
		$album = $thumb['album'];
		$photos = $wpdb->get_results( $wpdb->prepare( "SELECT `id`, `page_id` FROM `".WPPA_PHOTOS."` WHERE `album` = %s ".wppa_get_photo_order( $album ), $album ), ARRAY_A );
		wppa_dbg_q( 'Q-Ppag' );
		$prevpag = '0';
		$nextpag = '0';
		$curpag  = get_the_ID();
		$count = count( $photos );
		$count_ = $count - 1;
		$current = '0';
		if ( $photos ) {
			foreach ( array_keys( $photos ) as $idx ) {
				if ( $photos[$idx]['page_id'] == $curpag ) {
					if ( $idx != '0' ) $prevpag = wppa_get_the_auto_page( $photos[$idx-1]['id'] ); // ['page_id'];
					if ( $idx != $count_ ) $nextpag = wppa_get_the_auto_page( $photos[$idx+1]['id'] ); // ['page_id'];
					$current = $idx;
				}
			}
		}
		
		if ( $prevpag ) {
			$wppa['out'] .= '
			<a href="'.get_permalink( $prevpag ).'" style="float:left" >'.__( '< Previous', 'wppa' ).'</a>';
		}
		else {
			$wppa['out'] .= '
			<span style="visibility:hidden" >'.__( '< Previous', 'wppa' ).'</span>';
		}
		$wppa['out'] .= ++$current.'/'.$count;
		if ( $nextpag ) {
			$wppa['out'] .= '
			<a href="'.get_permalink( $nextpag ).'" style="float:right" >'.__( 'Next >', 'wppa' ).'</a>';
		}
		else {
			$wppa['out'] .= '
			<span style="visibility:hidden" >'.__( 'Next >', 'wppa' ).'</span>';
		}

		$wppa['out'] .= '
			</div><div style="clear:both"></div>';
	}
}

// The bestof box
function wppa_bestof_box ( $args ) {
global $wppa;

	wppa_container ( 'open' );
	$wppa['out'] .= wppa_nltab ( '+' ).'<div id="wppa-bestof-'.$wppa['mocc'].'" class="wppa-box wppa-bestof" style="'.__wcs( 'wppa-box' ).__wcs( 'wppa-bestof' ).'">';
		$wppa['out'] .= wppa_bestof_html( $args, false );
	$wppa['out'] .= wppa_nltab ( '-' ).'<div style="clear:both; height:4px;"></div></div>';
	wppa_container ( 'close' );
}

function wppa_bestof_html( $args, $widget = true ) {
global $wppa_opt;

	// Copletify args
	$args = wp_parse_args( ( array ) $args, array( 	'page' 			=> '0',
													'count' 		=> '1',
													'sortby' 		=> 'maxratingcount', 
													'display' 		=> 'photo',
													'period' 		=> 'thisweek',
													'maxratings'	=> 'yes',
													'meanrat' 		=> 'yes',
													'ratcount' 		=> 'yes',
													'linktype' 		=> 'none',
													'size' 			=> $wppa_opt['wppa_widget_width'],
													'fontsize' 		=> $wppa_opt['wppa_fontsize_widget_thumb'],
													'lineheight' 	=> $wppa_opt['wppa_fontsize_widget_thumb'] * 1.5,
													'height' 		=> '200'
											 ) );
											
	// Make args into seperate vars
	extract ( $args );
	
	// Validate args
	if ( ! in_array( $sortby, array ( 'maxratingcount', 'meanrating', 'ratingcount' ) ) ) wppa_dbg_msg ( 'Invalid arg sortby "'.$sortby.'" must be "maxratingcount", "meanrating" or "ratingcount"', 'red', 'force' );
	if ( ! in_array( $display, array ( 'photo', 'owner' ) ) ) wppa_dbg_msg ( 'Invalid arg display "'.$display.'" must be "photo" or "owner"', 'red', 'force' );
	if ( ! in_array( $period, array ( 'lastweek', 'thisweek', 'lastmonth', 'thismonth', 'lastyear', 'thisyear' ) ) ) wppa_dbg_msg ( 'Invalid arg period "'.$period.'" must be "lastweek", "thisweek", "lastmonth", "thismonth", "lastyear" or "thisyear"', 'red', 'force' );
	if ( ! $widget ) $size = $height;
	
	$result = '';
	
	$data = wppa_get_the_bestof( $count, $period, $sortby, $display );
			
	if ( $display == 'photo' ) {
		if ( is_array( $data ) ) {
			foreach ( array_keys( $data ) as $id ) {
				$thumb = wppa_cache_thumb( $id );
				if ( $thumb ) {
					$imgsize		= array( wppa_get_photox( $id ), wppa_get_photoy( $id ) );
					if ( $widget ) {
						$maxw 		= $size;
						$maxh 		= round ( $maxw * $imgsize['1'] / $imgsize['0'] );
					}
					else {
						$maxh 		= $size;
						$maxw 		= round ( $maxh * $imgsize['0'] / $imgsize['1'] );
					}
					$totalh 		= $maxh + $lineheight;
					if ( $maxratings == 'yes' ) $totalh += $lineheight;
					if ( $meanrat == 'yes' ) 	$totalh += $lineheight;
					if ( $ratcount == 'yes' ) 	$totalh += $lineheight;

					if ( $widget ) $clear = 'clear:both; '; else $clear = '';
					$result .= "\n".'<div class="wppa-widget" style="'.$clear.'width:'.$maxw.'px; height:'.$totalh.'px; margin:4px; display:inline; text-align:center; float:left;">'; 
				

						// The link if any
						if ( $linktype != 'none' ) {
							switch ( $linktype ) {
								case 'owneralbums':
									$href = wppa_get_permalink( $page ).'wppa-cover=1&amp;wppa-owner='.$thumb['owner'].'&amp;wppa-occur=1';
									$title = __a( 'See the authors albums', 'wppa' );
									break;
								case 'ownerphotos':
									$href = wppa_get_permalink( $page ).'wppa-cover=0&amp;wppa-owner='.$thumb['owner'].'&photos-only&amp;wppa-occur=1';
									$title = __a( 'See the authors photos', 'wppa' );
									break;
								case 'upldrphotos':
									$href = wppa_get_permalink( $page ).'wppa-cover=0&amp;wppa-upldr='.$thumb['owner'].'&amp;wppa-occur=1';
									$title = __a( 'See all the authors photos', 'wppa' );
									break;
							}
							$result .= '<a href="'.wppa_convert_to_pretty( $href ).'" title="'.$title.'" >';
						}
						
						// The image
						$result .= '<img style="height:'.$maxh.'px; width:'.$maxw.'px;" src="'.wppa_get_photo_url( $id, '', $maxw, $maxh ).'" '.wppa_get_imgalt( $id ).'/>';
						
						// The /link
						if ( $linktype != 'none' ) {
							$result .= '</a>';
						}
						
						// The medal
						$result .= wppa_get_medal_html( $id, $maxh );

						// The subtitles
						$result .= "\n\t".'<div style="font-size:'.$fontsize.'px; line-height:'.$lineheight.'px; position:absolute; width:'.$maxw.'px; ">';
							$result .= sprintf( __a( 'Photo by: %s' ), $data[$id]['user'] ).'<br />';
							if ( $maxratings 	== 'yes' ) $result .= sprintf( __a( 'Max ratings: %s.' ), $data[$id]['maxratingcount'] ).'<br />';
							if ( $ratcount 		== 'yes' ) $result .= sprintf( __a( 'Votes: %s.' ), $data[$id]['ratingcount'] ).'<br />';
							if ( $meanrat  		== 'yes' ) $result .= sprintf( __a( 'Mean value: %4.2f.' ), $data[$id]['meanrating'] ).'<br />';
						$result .= '</div>';
						$result .= '<div style="clear:both" ></div>';
						
					$result .= "\n".'</div>';
				}
				else {	// No image
					$result .= '<div>'.sprintf( __a( 'Photo %s not found.' ), $id ).'</div>';
				}
			}
		}	
		else {
			$result .= $data;	// No array, print message
		}
	}
	else {	// Display = owner
		if ( is_array( $data ) ) {
			$result .= '<ul>';
			foreach ( array_keys( $data ) as $author ) {
				$result .= '<li>';
				// The link if any
				if ( $linktype != 'none' ) {
					switch ( $linktype ) {
						case 'owneralbums':
							$href = wppa_get_permalink( $page ).'wppa-cover=1&amp;wppa-owner='.$data[$author]['owner'].'&amp;wppa-occur=1';
							$title = __a( 'See the authors albums', 'wppa' );
							break;
						case 'ownerphotos':
							$href = wppa_get_permalink( $page ).'wppa-cover=0&amp;wppa-owner='.$data[$author]['owner'].'&amp;photos-only&amp;wppa-occur=1';
							$title = __a( 'See the authors photos', 'wppa' );
							break;
						case 'upldrphotos':
							$href = wppa_get_permalink( $page ).'wppa-cover=0&amp;wppa-upldr='.$data[$author]['owner'].'&amp;wppa-occur=1';
							$title = __a( 'See all the authors photos', 'wppa' );
							break;
					}
					$result .= '<a href="'.$href.'" title="'.$title.'" >';
				}
				
				// The name
				$result .= $author;

				// The /link
				if ( $linktype != 'none' ) {
					$result .= '</a>';
				}
				
				$result .= '<br/>';
				
				// The subtitles
				$result .= "\n\t".'<div style="font-size:'.$wppa_opt['wppa_fontsize_widget_thumb'].'px; line-height:'.$lineheight.'px; ">';
							if ( $maxratings 	== 'yes' ) $result .= sprintf( __a( 'Max ratings: %s.' ), $data[$author]['maxratingcount'] ).'<br />';
							if ( $ratcount 		== 'yes' ) $result .= sprintf( __a( 'Votes: %s.' ), $data[$author]['ratingcount'] ).'<br />';
							if ( $meanrat  		== 'yes' ) $result .= sprintf( __a( 'Mean value: %4.2f.' ), $data[$author]['meanrating'] ).'<br />';
				
				$result .= '</div>';
				$result .= '</li>';
			}
			$result .= '</ul>';
		}
		else {
			$result .= $data;	// No array, print message
		}
	}
	
	return $result;
}