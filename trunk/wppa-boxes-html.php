<?php
/* wppa-boxes-html.php
* Package: wp-photo-album-plus
*
* Various wppa boxes
* Version 5.2.10
*
*/

if ( ! defined( 'ABSPATH' ) )
    die( "Can't load this file directly" );

// The box conaining the thumbnails
function wppa_thumb_area($action) {
global $wppa;
global $wppa_alt;
global $album;

	if ($action == 'open') {
		if (is_feed()) {
			$wppa['out'] .= wppa_nltab('+').'<div id="wppa-thumbarea-'.$wppa['master_occur'].'" style="clear: both: '.__wcs('wppa-box').__wcs('wppa-'.$wppa_alt).'">';
		}
		else {
			$wppa['out'] .= wppa_nltab('+').'<div id="wppa-thumbarea-'.$wppa['master_occur'].'" style="clear: both; '.__wcs('wppa-box').__wcs('wppa-'.$wppa_alt).'width: '.wppa_get_thumbnail_area_width().'px;" class="thumbnail-area thumbnail-area-'.$wppa['master_occur'].' wppa-box wppa-'.$wppa_alt.'" >';
			if ( is_array($album) ) wppa_bump_viewcount('album', $album['id']);
		}		
		if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
	}
	elseif ($action == 'close') {
		if ( ! $wppa['is_upldr'] ) {
			wppa_user_create_html($wppa['current_album'], wppa_get_container_width('netto'), 'thumb');
			wppa_user_upload_html($wppa['current_album'], wppa_get_container_width('netto'), 'thumb');
		}
		$wppa['out'] .= wppa_nltab().'<div style="clear:both;"></div>';		

		$wppa['out'] .= wppa_nltab('-').'</div><!-- wppa-thumbarea-'.$wppa['master_occur'].' -->';
	}
	else {
		$wppa['out'] .= wppa_nltab().'<span style="color:red;">Error, wppa_thumb_area() called with wrong argument: '.$action.'. Possible values: \'open\' or \'close\'</span>';
	}
}

// The tagcloud box
function wppa_tagcloud_box($seltags = '', $minsize = '8', $maxsize = '24') {
global $wppa;

	if ( is_feed() ) return;

	wppa_container('open');
	$wppa['out'] .= wppa_nltab('+').'<div id="wppa-tagcloud-'.$wppa['master_occur'].'" class="wppa-box wppa-tagcloud" style="'.__wcs('wppa-box').__wcs('wppa-tagcloud').'">';
		$wppa['out'] .= wppa_get_tagcloud_html($seltags, $minsize, $maxsize);
	$wppa['out'] .= wppa_nltab('-').'<div style="clear:both;"></div></div>';
	wppa_container('close');
}

// Get html for tagcloud
function wppa_get_tagcloud_html($seltags = '', $minsize = '8', $maxsize = '24') {
global $wppa_opt;
global $wppa;

	$page = wppa_get_the_landing_page('wppa_tagcloud_linkpage', __a('Tagged photos'));

	$result 	= '';
	if ( $wppa_opt['wppa_tagcloud_linkpage'] ) {
		$hr = wppa_get_permalink($page);
		if ( $wppa_opt['wppa_tagcloud_linktype'] == 'album' ) {
			$hr .= 'wppa-album=0&wppa-cover=0&wppa-occur=1';
		}
		if ( $wppa_opt['wppa_tagcloud_linktype'] == 'slide' ) {
			$hr .= 'wppa-album=0&wppa-cover=0&wppa-occur=1&slide';
		}
	}
	else {
		return __a('Please select a tagcloud landing page in Table VI-C3b');
	}
	$tags = wppa_get_taglist();
	if ( $tags ) {
		$top = '0';
		foreach ( $tags as $tag ) {	// Find largest percentage
			if ( $tag['fraction'] > $top ) $top = $tag['fraction'];
		}
		if ( $top ) $factor = ($maxsize - $minsize) / $top;
		else $factor = '1.0';
		$selarr = $seltags ? explode( ',', $seltags ) : array();
		foreach ( $tags as $tag ) {
			if ( ! $seltags || in_array($tag['tag'], $selarr) ) {
				$href 		= $hr . '&wppa-tag=' . $tag['tag'];
				$title 		= sprintf('%d photos - %s%%', $tag['count'], $tag['fraction'] * '100');
				$name 		= $tag['tag'];
				$size 		= floor($minsize + $tag['fraction'] * $factor);
				$result    .= '<a href="'.$href.'" title="'.$title.'" style="font-size:'.$size.'px;" >'.$name.'</a> ';
			}
		}
	}
	return $result;
}

// The multitag box
function wppa_multitag_box($nperline = '2', $seltags = '') {
global $wppa;

	if ( is_feed() ) return;
	
	wppa_container('open');
	$wppa['out'] .= wppa_nltab('+').'<div id="wppa-multitag-'.$wppa['master_occur'].'" class="wppa-box wppa-multitag" style="'.__wcs('wppa-box').__wcs('wppa-multitag').'">';
		$wppa['out'] .= wppa_get_multitag_html($nperline, $seltags);
	$wppa['out'] .= wppa_nltab('-').'<div style="clear:both;"></div></div>';
	wppa_container('close');
}

// The html for multitag widget
function wppa_get_multitag_html($nperline = '2', $seltags = '') {
global $wppa_opt;
global $wppa;

	$or_only = wppa_switch('wppa_tags_or_only');
	$page = wppa_get_the_landing_page('wppa_multitag_linkpage', __a('Multi Tagged photos'));
	
	$result 	= '';
	if ( $wppa_opt['wppa_multitag_linkpage'] ) {
		$hr = wppa_get_permalink($page);
		if ( $wppa_opt['wppa_multitag_linktype'] == 'album' ) {
			$hr .= 'wppa-album=0&wppa-cover=0&wppa-occur=1';
		}
		if ( $wppa_opt['wppa_multitag_linktype'] == 'slide' ) {
			$hr .= 'wppa-album=0&wppa-cover=0&wppa-occur=1&slide';
		}
	}
	else {
		return __a('Please select a multitag landing page in Table VI-C4b');
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
			if ( document.getElementById("andoror-'.$wppa['master_occur'].'").checked ) andor = "or";
		var sep;';
	}
	$result .= '
	if ( andor == "and" ) sep = ","; else sep = ";";
	';
	$selarr = $seltags ? explode( ',', $seltags ) : array();
	if ( $tags ) foreach ( $tags as $tag ) {
		if ( ! $seltags || in_array($tag['tag'], $selarr) ) {
			$result .= '
			if ( document.getElementById("wppa'.$tag['tag'].'").checked ) {
				url+="'.$tag['tag'].'"+sep;
				any = true;
			}';
		}
	}	
	$result .= '
	if ( any ) document.location = url;
	else alert ("'.__a('Please check the tag(s) that the photos must have').'");
	}</script>
	';
	
	$qtag = wppa_get_get('tag');
	$andor = $or_only ? 'or' : 'and'; // default
	if ( strpos($qtag, ',') ) {
		$querystringtags = explode(',',wppa_get_get('tag'));
	}
	elseif ( strpos($qtag, ';') ) {
		$querystringtags = explode(';',wppa_get_get('tag'));
		$andor = 'or';
	}
	else $querystringtags = wppa_get_get('tag');

	if ( $tags ) {
	
		$result .= '<table>';
		
		if ( ! $or_only ) {
			$result .= '<tr><td><input class="radio" name="andor-'.$wppa['master_occur'].'" value="and" id="andorand-'.$wppa['master_occur'].'" type="radio" ';
			if ( $andor == 'and' ) $result .= 'checked="checked" ';
			$result .= 'size="30" />&nbsp;'.__a('And', 'wppa_theme').'</td>';
			$result .= '<td><input class="radio" name="andor-'.$wppa['master_occur'].'" value="or" id="andoror-'.$wppa['master_occur'].'" type="radio" ';
			if ( $andor == 'or' ) $result .= 'checked="checked" ';
			$result .= 'size="30" />&nbsp;'.__a('Or', 'wppa_theme').'</td>';
			$result .= '</tr>';
		}
		$count = '0';
		$checked = '';		
		
		$tropen = false;
//		$selarr = $seltags ? explode( ',', $seltags ) : array();
		foreach ( $tags as $tag ) {
			if ( ! $seltags || in_array($tag['tag'], $selarr) ) {
				if ( $count % $nperline == '0' ) {
					$result .= '<tr>';
					$tropen = true;
				}
				if ( is_array($querystringtags) ) {
					$checked = in_array($tag['tag'], $querystringtags) ? 'checked="checked" ' : '';
				}
				$result .= '<td style="padding-right:4px;" ><input type="checkbox" id="wppa'.$tag['tag'].'" '.$checked.'/>&nbsp;'.$tag['tag'].'</td>';
				$count++;
				if ( $count % $nperline == '0' ) {
					$result .= '</tr>';
					$tropen = false;
				}
			}
		}
		if ( $tropen ) $result .= '</tr>';
		$result .= '</table>';
		$result .= '<input type="button" onclick="wppaProcessMultiTagRequest()" value="'.__a('Find!').'" />';
	}
	return $result;
}

// Make html for sharebox
function wppa_get_share_html( $key = '', $js = true ) {
global $wppa;
global $wppa_opt;
global $thumb;
global $wppa_locale;

	$do_it = false;
	if ( ! $wppa['is_slideonly'] ) {
		if ( wppa_switch('wppa_share_on') && ! $wppa['in_widget'] ) $do_it = true;
		if ( wppa_switch('wppa_share_on_widget') && $wppa['in_widget'] ) $do_it = true;
		if ( wppa_switch('wppa_share_on_lightbox') ) $do_it = true;
	}
	if ( ! $do_it ) return '';

	// The share url
	$share_url = wppa_convert_to_pretty(str_replace('&amp;', '&', wppa_get_image_page_url_by_id($thumb['id'], $wppa_opt['wppa_share_single_image'])));
	
	// The share title
	$photo_name = __(stripslashes($thumb['name']));
	
	// The share description
//	$photo_desc = strip_shortcodes(wppa_strip_tags(wppa_html(__(stripslashes($thumb['description']))), 'all'));
	$photo_desc = strip_shortcodes(wppa_strip_tags(wppa_html(wppa_get_photo_desc($thumb['id']))), 'all');
	
	// The default description
	$see_on_site = sprintf(__a('See this image on %s'), str_replace('&amp;', __a('and'), get_bloginfo('name')));
	
	// The share thumbnail
	$share_img = wppa_get_thumb_url($thumb['id']);

	// The icon size
	$s = ( ( $wppa['in_widget'] && $key != 'lightbox' ) || $key == 'thumb' ) ? '16' : $wppa_opt['wppa_share_size'];
	
	// qr code
	if ( wppa_switch('wppa_share_qr') && $key != 'thumb' ) {	
		$src = 'http://api.qrserver.com/v1/create-qr-code/?data='.urlencode($share_url).'&size=80x80&color='.trim($wppa_opt['wppa_qr_color'], '#').'&bgcolor='.trim($wppa_opt['wppa_qr_bgcolor'], '#');
		$qr = '<div style="float:left; padding:2px;" ><img src="'.$src.'" title="'.esc_attr($share_url).'"/></div>';
	}
	else $qr = '';
	
	// facebook share button
	if ( wppa_switch('wppa_share_facebook') ) { 
		$summary = $see_on_site . ': ' . $photo_desc;
		$fb = 	'<div style="float:left; padding:2px;" >';
		$fb .= 		'<a title="'.sprintf(__a('Share %s on Facebook'), esc_attr($photo_name)).'" ';
		$fb .= 			'href="http://www.facebook.com/sharer.php?s=100&p[url]='.urlencode($share_url);
		$fb .= 				'&p[images][0]='.$share_img.'&p[title]='.urlencode($photo_name).'&p[summary]='.urlencode($summary).'" ';
		$fb .= 			'target="_blank" >';
		$fb .= 				'<img src="'.wppa_get_imgdir().'facebook.png" style="height:'.$s.'px;" alt="'.esc_attr(__a('Share on Facebook')).'" />';
		$fb .= 		'</a>';
		$fb .= 	'</div>';
	}
	else $fb = '';
	
	// twitter share button
	if ( wppa_switch('wppa_share_twitter') ) {	
		$tweet = urlencode($see_on_site) . ': ';
		$tweet_len = strlen($tweet) + '1';
		
		$tweet .= urlencode($share_url);
		$url_len = strpos($share_url, '/', 8) + 1;	// find first '/' after 'http(s)://' rest doesnt count for twitter chars
		$tweet_len += ($url_len > 1) ? $url_len : strlen($share_url);
		
		$rest_len = 140 - $tweet_len;
		
		if ( $wppa_opt['wppa_show_full_name'] ) {
			if ( $rest_len > strlen($photo_name) ) {
				$tweet .= ' ' . urlencode($photo_name);
				$rest_len -= strlen($photo_name);
				$rest_len -= '2';
			}
			else {
				$tweet .= ' '. urlencode(substr($photo_name, 0, $rest_len)) . '...';
				$rest_len -= strlen(substr($photo_name, 0, $rest_len));
				$rest_len -= '5';
			}
		}
		
		if ( $rest_len > strlen($photo_desc) ) {
			$tweet .= ': ' . urlencode($photo_desc);
		}
		elseif ( $rest_len > 8 ) {
			$tweet .= ': '. urlencode(substr($photo_desc, 0, $rest_len)) . '...';
		}
		
		$tw = 	'<div style="float:left; padding:2px;" >';
		$tw .= 		'<a title="'.sprintf(__a('Tweet %s on Twitter'), esc_attr($photo_name)).'" ';
		$tw .= 			'href="https://twitter.com/intent/tweet?text='.$tweet.'" ';
		$tw .= 			'target="_blank" >';
		$tw .=				'<img src="'.wppa_get_imgdir().'twitter.png" style="height:'.$s.'px;" alt="'.esc_attr(__a('Share on Twitter')).'" />';
		$tw .= 		'</a>';
		$tw .=	'</div>';
	}
	else $tw = '';
/*	
	// hyves
	if ( $wppa_opt['wppa_share_hyves'] ) {
		$hv = 	'<div style="float:left; padding:2px;" >';
		$hv .= 		'<a title="'.sprintf(__a('Tip %s on Hyves'), esc_attr($photo_name)).'" ';
		$hv .= 			'href="http://www.hyves-share.nl/button/tip/?tipcategoryid=12&rating=5&title='.urlencode($photo_name);
		$hv .= 				'&body='.str_replace('+', ' ', urlencode($see_on_site)).': '.urlencode($share_url).' '.str_replace('+', ' ', urlencode($photo_desc)).'" ';
		$hv .= 			'target="_blank" >';
		$hv .= 				'<img src="'.wppa_get_imgdir().'hyves.png" style="height:'.$s.'px;" alt="'.esc_attr(__a('Share on Hyves')).'" />';
		$hv .= 		'</a>';
		$hv .= 	'</div>';		
	}
	else $hv = '';
*/
	$hv = '';
	
	// Google
	if ( wppa_switch('wppa_share_google') ) {
		$go = 	'<div style="float:left; padding:2px;" >';
		$go .= 		'<a title="'.sprintf(__a('Share %s on Google+'), esc_attr($photo_name)).'" ';
		$go .= 			'href="https://plus.google.com/share?url='.urlencode($share_url).'" ';
		$go .= 			'onclick="javascript:window.open(this.href, \"\", \"menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\");return false;" ';
		$go .= 			'target="_blank" >';
		$go .= 			'<img src="'.wppa_get_imgdir().'google.png" style="height:'.$s.'px;" alt="'.esc_attr(__a('Share on Google+')).'"/>';
		$go .= 		'</a>';
		$go .= 	'</div>';
	}
	else $go = '';
	
	// Pinterest
	$desc = urlencode($see_on_site).': '.urlencode($photo_desc).'" ';
	if ( strlen($desc) > 500) $desc = substr($desc, 0, 495).'...';
	if ( wppa_switch('wppa_share_pinterest') ) {
		$pi = 	'<div style="float:left; padding:2px;" >';
		$pi .= 		'<a title="'.sprintf(__a('Share %s on Pinterest'), esc_attr($photo_name)).'" ';
		$pi .= 			'href="http://pinterest.com/pin/create/button/?url='.urlencode($share_url);
		$pi .=			'&media='.urlencode(str_replace('/thumbs/', '/', $share_img));						// Fullsize image
		$pi .=			'&description='.$desc;
		$pi .=			'target="_blank" >';//'class="pin-it-button" count-layout="horizontal" >';
		$pi .=			'<img src="'.wppa_get_imgdir().'pinterest.png" style="height:'.$s.'px;" alt="'.esc_attr(__a('Share on Pinterest')).'" />';	//border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" />';
		$pi .=		'</a>';
		$pi .=	'</div>';

	}
	else $pi = '';
	
	// LinkedIn
	if ( wppa_switch('wppa_share_linkedin') && $key != 'thumb' && $key != 'lightbox' ) {
		if ( $js ) {
			$li = '[script src="//platform.linkedin.com/in.js" type="text/javascript">';
			$li .= 'lang: '.$wppa_locale;
			$li .= '[/script>';
			$li .= '[script type="IN/Share" data-url="'.urlencode($share_url).'" data-counter="top">[/script>';
		}
		else {
			$li = '<script src="//platform.linkedin.com/in.js" type="text/javascript">';
			$li .= 'lang: '.$wppa_locale;
			$li .= '</script>';
			$li .= '<script type="IN/Share" data-url="'.urlencode($share_url).'" data-counter="top"></script>';
		}
	}
	else $li = '';
	
	// Facebook comments
	if ( ( wppa_switch('wppa_facebook_comments') || wppa_switch('wppa_facebook_like') ) && ! $wppa['in_widget'] && $key != 'thumb' && $key != 'lightbox') {
		$width = $wppa['auto_colwidth'] ? '470' : wppa_get_container_width(true);
		if ( wppa_switch('wppa_facebook_like') ) {
			$fbc = '<div class="fb-like" data-href="'.$share_url.'" data-width="'.$width.'" data-show-faces="false" data-send="true"></div>';
		}
		if ( wppa_switch('wppa_facebook_comments') ) {
			$fbc .= '<div style="color:blue;">'.__a('Comment on Facebook:').'</div>';
			$fbc .= '<div class="fb-comments" data-href="'.$share_url.'" data-width='.$width.'></div>';
		}
		if ( $js ) {
			$fbc .= '[script>wppaFbInit();[/script>';
		}
		else {
			$fbc .= '<script>wppaFbInit();</script>';
		}
	}
	else $fbc = '';

	return $qr.$fb.$tw.$hv.$go.$pi.$li.$fbc.//.'<small>This box is under construction and may not yet properly work for all icons shown</small>'.
	'<div style="clear:both"></div>';

}

// The upload box
function wppa_upload_box() {
global $wppa;

	if ( wppa_switch('wppa_user_upload_login') ) {
		if ( ! is_user_logged_in() ) return;					// Must login
		if ( ! current_user_can('wppa_upload') ) return;		// No upload rights
	}

	if ( $wppa['start_album'] ) {
		if ( ! wppa_have_access( $wppa['start_album'] ) ) return;	// No access to this album
	}
	else {
		if ( ! wppa_have_access('0') ) return;					// No access to any album
	}
	
	wppa_container('open');
	$wppa['out'] .= wppa_nltab('+').'<div id="wppa-upload-'.$wppa['master_occur'].'" class="wppa-box wppa-upload" style="'.__wcs('wppa-box').__wcs('wppa-upload').'">';
		wppa_user_create_html($wppa['start_album'], wppa_get_container_width('netto'), 'uploadbox');
		wppa_user_upload_html($wppa['start_album'], wppa_get_container_width('netto'), 'uploadbox');
	$wppa['out'] .= wppa_nltab('-').'<div style="clear:both;"></div></div>';
	wppa_container('close');
}

// Frontend create album, for use in the upload box, the widget or in the album and thumbnail box
function wppa_user_create_html($alb, $width, $where = '', $mcr = false) {
global $wppa;
global $wppa_opt;

	// May I?
	if ( ! $wppa_opt['wppa_user_create_on'] ) return;			// Feature not enabled
	if ( wppa_switch('wppa_user_create_login') ) {
		if ( ! is_user_logged_in() ) return;					// Must login
	}
	if ( ! wppa_have_access( $alb ) ) {
		return;						// No album access
	}
	if ( is_user_logged_in() ) {
		if ( ! $alb && ! wppa_can_create_top_album() ) return;	// Current logged in user can not create a toplevel album
		if ( $alb && ! wppa_can_create_album() ) return;		// Current logged in user can not create a sub-album
	}
	
	if ( wppa_is_user_blacklisted() ) return;

	// In a widget or multi column responsive?
	$small = ( $wppa['in_widget'] == 'upload' || $mcr );

	// Create the return url
	$returnurl = wppa_get_permalink();
	if ( $where == 'cover' ) {
		$returnurl .= 'wppa-album='.wppa_get_parentalbumid($alb).'&amp;wppa-cover=0&amp;wppa-occur='.$wppa['occur'];
	}
	elseif ( $where == 'thumb' ) {
		$returnurl .= 'wppa-album='.$alb.'&amp;wppa-cover=0&amp;wppa-occur='.$wppa['occur'];
	}
	elseif ( $where == 'widget' || $where == 'uploadbox' ) {
		// As is: permalink
	}
	if ( $wppa['page'] ) $returnurl .= '&amp;wppa-page='.$wppa['page'];
	
	// Make the HTML
	$t = $mcr ? 'mcr-' : '';
	$wppa['out'] .= '
	<div style="clear:both"></div>
	<a id="wppa-cr-'.$alb.'-'.$wppa['master_occur'].'" class="wppa-upcr-'.$alb.'-'.$wppa['master_occur'].'" onclick="jQuery(\'.wppa-upcr-'.$alb.'-'.$wppa['master_occur'].'\').css(\'display\',\'none\');jQuery(\'#wppa-create-'.$t.$alb.'-'.$wppa['master_occur'].'\').css(\'display\',\'block\');wppaColWidth['.$wppa['master_occur'].']=0;" class="" style="float:left; cursor:pointer;">
		'.__a('Create Album').'
	</a>
	
	<div id="wppa-create-'.$t.$alb.'-'.$wppa['master_occur'].'" class="wppa-file-'.$t.$wppa['master_occur'].'" style="width:'.$width.'px;text-align:center;display:none" >
		<form id="wppa-creform-'.$alb.'-'.$wppa['master_occur'].'" action="'.$returnurl.'" method="post" >'.
		wp_nonce_field('wppa-album-check' , 'wppa-nonce', false, false).'
		<input type="hidden" name="wppa-album-parent" value="'.$alb.'" />';
		// Name
		$wppa['out'] .= '
			<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.
				__a('Enter album name.').'&nbsp;<span style="font-size:10px;" >'.__a('Don\'t leave this blank!').'</span>
			</div>
			<input type="text" class="wppa-box-text wppa-file-'.$t.$wppa['master_occur'].'" style="padding:0; width:'.($width-6).'px; '.__wcs('wppa-box-text').'" name="wppa-album-name" />';

		// Description
//		$desc = $wppa_opt['wppa_apply_newphoto_desc_user'] ? stripslashes($wppa_opt['wppa_newphoto_description']) : '';
		$desc = '';
		$wppa['out'] .= '
			<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.
				__a('Enter album description').'
			</div>
			<textarea class="wppa-user-textarea wppa-box-text wppa-file-'.$t.$wppa['master_occur'].'" style="height:120px; width:'.($width-6).'px; '.__wcs('wppa-box-text').'" name="wppa-album-desc" >'.$desc.'</textarea>';

			if ( true ) { 	// Captcha
				$captkey = $wppa['randseed'];
				$wppa['out'] .= '<div style="float:left; margin: 6px 0;" ><div style="float:left;">'.wppa_make_captcha( $captkey ).'</div><input type="text" id="wppa-captcha-'.$wppa['master_occur'].'" name="wppa-captcha" style="margin-left: 6px; width:50px; '.__wcs('wppa-box-text').__wcs('wppa-td').'" /></div>';
				$wppa['out'] .= '<input type="hidden" name="wppa-randseed" value="'.$captkey.'" />';
			}

		$wppa['out'] .= '
			<input type="submit" class="wppa-user-submit" style="margin: 6px 0; float:right; '.__wcs('wppa-box-text').'" value="'.__a('Create album').'" />
		</form>
	</div>';
}

// Frontend upload html, for use in the upload box, the widget or in the album and thumbnail box
function wppa_user_upload_html($alb, $width, $where = '', $mcr = false) {
global $wppa;
global $wppa_opt;

	// May I?
	if ( ! $wppa_opt['wppa_user_upload_on'] ) return;			// Feature not enabled
	if ( wppa_switch('wppa_user_upload_login') ) {
		if ( ! is_user_logged_in() ) return;					// Must login
	}
	if ( ! wppa_have_access( $alb ) ) {
		return;						// No album access
	}

	// Find max files for the user
	$allow_me = wppa_allow_user_uploads();
	if ( ! $allow_me ) {
		if ( wppa_switch('wppa_show_album_full') ) {
			$wppa['out'] .= '<span style="color:red">';
			$wppa['out'] .= __a('Max uploads reached');
			$time = wppa_time_to_wait_html('0', true);	// For the user
			$wppa['out'] .= $time;
			wppa_dbg_msg('Max for user '.wppa_get_user().' reached '.$time);
			$wppa['out'] .= '</span>';
		}
		return;													// Max quota reached
	}
	
	// Find max files for the album
	$allow_alb = wppa_allow_uploads($alb);
	if ( ! $allow_alb ) {
		if ( wppa_switch('wppa_show_album_full') ) {
			$wppa['out'] .= '<span style="color:red">';
			$wppa['out'] .= __a('Max uploads reached');
			$time = wppa_time_to_wait_html($alb);		// For the album
			$wppa['out'] .= $time;
			wppa_dbg_msg('Max for album '.$alb.' reached '.$time);
			$wppa['out'] .= '</span>';
		}
		return;													// Max quota reached
	}
	
	if ( wppa_is_user_blacklisted() ) return;

	// Find max files for the system
	$allow_sys = ini_get('max_file_uploads');
	
	// THE max
	if ( $allow_me == '-1' ) $allow_me = $allow_sys;
	if ( $allow_alb == '-1' ) $allow_alb = $allow_sys;
	$max = min($allow_me, $allow_alb, $allow_sys);

	// In a widget or multi column responsive?
	$small = ( $wppa['in_widget'] == 'upload' || $mcr );

	// Create the return url
	$returnurl = wppa_get_permalink();
	if ( $where == 'cover' ) {
		$returnurl .= 'wppa-album='.wppa_get_parentalbumid($alb).'&amp;wppa-cover=0&amp;wppa-occur='.$wppa['occur'];
	}
	elseif ( $where == 'thumb' ) {
		$returnurl .= 'wppa-album='.$alb.'&amp;wppa-cover=0&amp;wppa-occur='.$wppa['occur'];
	}
	elseif ( $where == 'widget' || $where == 'uploadbox' ) {
		// As is: permalink
	}
	if ( $wppa['page'] ) $returnurl .= '&amp;wppa-page='.$wppa['page'];
	
	// Make the HTML
	$t = $mcr ? 'mcr-' : '';
	$wppa['out'] .= '
	<div style="clear:both"></div>
	<a id="wppa-up-'.$alb.'-'.$wppa['master_occur'].'" class="wppa-upcr-'.$alb.'-'.$wppa['master_occur'].'" onclick="jQuery(\'.wppa-upcr-'.$alb.'-'.$wppa['master_occur'].'\').css(\'display\',\'none\');jQuery(\'#wppa-file-'.$t.$alb.'-'.$wppa['master_occur'].'\').css(\'display\',\'block\');wppaColWidth['.$wppa['master_occur'].']=0;" class="" style="float:left; cursor:pointer;">
		'.__a('Upload Photo').'
	</a>
	<div id="wppa-file-'.$t.$alb.'-'.$wppa['master_occur'].'" class="wppa-file-'.$t.$wppa['master_occur'].'" style="width:'.$width.'px;text-align:center;display:none" >
		<form id="wppa-uplform-'.$alb.'-'.$wppa['master_occur'].'" action="'.$returnurl.'" method="post" enctype="multipart/form-data">
			'.wp_nonce_field('wppa-check' , 'wppa-nonce', false, false);		
			if ( ! $alb ) {	// No album given: select one
				$wppa['out'] .= '
			<select id="wppa-upload-'.$wppa['master_occur'].'" name="wppa-upload-album" style="float:left; max-width: '.$width.'px; ">
				'.wppa_album_select_a(	array (	'addpleaseselect' => true, 'checkaccess' => true, 'checkupload' => true, 'path' => wppa_switch('wppa_hier_albsel') )).'
			</select>
			<br />';
			}
			else {
				$wppa['out'] .= '
			<input type="hidden" name="wppa-upload-album" value="'.$alb.'" />';
			}
// Exp	
$wppa['out'] .= '		
<script>jQuery("#wppa-uplform-'.$alb.'-'.$wppa['master_occur'].'").attr("action", document.location.href);</script>';
			
// End exp
			if ( wppa_switch('wppa_upload_one_only') && ! current_user_can('administrator') ) {
				$wppa['out'] .= '
			<input type="file" class="wppa-user-file" style="max-width: '.$width.'; margin: 6px 0; float:left; '.__wcs('wppa-box-text').'" id="wppa-user-upload-'.$alb.'-'.$wppa['master_occur'].'" name="wppa-user-upload-'.$alb.'-'.$wppa['master_occur'].'[]" onchange="jQuery(\'#wppa-user-submit-'.$alb.'-'.$wppa['master_occur'].'\').css(\'display\', \'block\')" />';
			}
			else {
				$wppa['out'] .= '
			<input type="file" multiple="multiple" class="wppa-user-file" style="max-width: '.$width.'; margin: 6px 0; float:left; '.__wcs('wppa-box-text').'" id="wppa-user-upload-'.$alb.'-'.$wppa['master_occur'].'" name="wppa-user-upload-'.$alb.'-'.$wppa['master_occur'].'[]" onchange="jQuery(\'#wppa-user-submit-'.$alb.'-'.$wppa['master_occur'].'\').css(\'display\', \'block\')" />';
			}
			
			$onclick = $alb ? '' : ' onclick="if ( document.getElementById(\'wppa-upload-'.$wppa['master_occur'].'\').value == 0 ) {alert(\''.esc_js(__a('Please select an album and try again')).'\');return false;}"';

			$wppa['out'] .= '
			<input type="submit" id="wppa-user-submit-'.$alb.'-'.$wppa['master_occur'].'"'.$onclick.' style="display:none; margin: 6px 0; float:right; '.__wcs('wppa-box-text').'" class="wppa-user-submit" name="wppa-user-submit-'.$alb.'-'.$wppa['master_occur'].'" value="'.__a('Upload photo').'" />
			<div style="clear:both"></div>';
			
			if ( ! wppa_switch('wppa_upload_one_only') && ! current_user_can('administrator') ) {
				if ( $max ) $wppa['out'] .= '
			<span style="font-size:10px;" >
				'.sprintf(__a('You may upload up to %s photos at once if your browser supports HTML-5 multiple file upload'), $max).'
			</span>';
				$maxsize = wppa_check_memory_limit(false);
				if ( is_array($maxsize) ) $wppa['out'] .= '
			<br />
			<span style="font-size:10px;" >
				'.sprintf(__a('Max photo size: %d x %d (%2.1f MegaPixel)'), $maxsize['maxx'], $maxsize['maxy'], $maxsize['maxp']/(1024*1024) ).'
			</span>';
			}
			
			if ( $wppa_opt['wppa_copyright_on'] ) $wppa['out'] .= '
			<div id="wppa-copyright-'.$wppa['master_occur'].'" style="clear:both;" >
				'.__($wppa_opt['wppa_copyright_notice']).'
			</div>';
			
			// Watermark
			if ( $wppa_opt['wppa_watermark_on'] == 'yes' && ( $wppa_opt['wppa_watermark_user'] == 'yes' || current_user_can('wppa_settings') ) ) { 
				$wppa['out'] .= '
			<table class="wppa-watermark wppa-box-text" style="margin:0; border:0; '.__wcs('wppa-box-text').'" >
				<tbody>
					<tr valign="top" style="border: 0 none; " >
						<td class="wppa-box-text wppa-td" style="'.__wcs('wppa-box-text').__wcs('wppa-td').'" >
							'.__a('Apply watermark file:').'
						</td>
					</tr>
					<tr>
						<td class="wppa-box-text wppa-td" style="width: '.$width.';'.__wcs('wppa-box-text').__wcs('wppa-td').'" >
							<select style="margin:0; padding:0; text-align:left; width:auto; " name="wppa-watermark-file" id="wppa-watermark-file">
								'.wppa_watermark_file_select().'
							</select>
						</td>
					</tr>
					<tr valign="top" style="border: 0 none; " >
						<td class="wppa-box-text wppa-td" style="width: '.$width.';'.__wcs('wppa-box-text').__wcs('wppa-td').'" >
							'.__a('Position:').'
						</td>';
						if ( $small ) $wppa['out'] .= '</tr><tr>';
						$wppa['out'] .= '
						<td class="wppa-box-text wppa-td" style="width: '.$width.';'.__wcs('wppa-box-text').__wcs('wppa-td').'" >
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
			case '2#005':
				$expl = __a('If you leave this blank, iptc tag 005 (Graphic name) will be used as photoname if available, else the original filename will be used as photo name.');
				break;
			case '2#120':
				$expl = __a('If you leave this blank, iptc tag 120 (Caption) will be used as photoname if available, else the original filename will be used as photo name.');
				break;
			default:
				$expl = __a('If you leave this blank, the original filename will be used as photo name.');
		}
		$wppa['out'] .= '
			<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.
				__a('Enter photo name.').'&nbsp;<span style="font-size:10px;" >'.$expl.'</span>'.'
			</div>
			<input type="text" class="wppa-box-text wppa-file-'.$t.$wppa['master_occur'].'" style="padding:0; width:'.($width-6).'px; '.__wcs('wppa-box-text').'" name="wppa-user-name" />';

		// Description
		$desc = $wppa_opt['wppa_apply_newphoto_desc_user'] ? stripslashes($wppa_opt['wppa_newphoto_description']) : '';
		$wppa['out'] .= '
			<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.
				__a('Enter/modify photo description').'
			</div>
			<textarea class="wppa-user-textarea wppa-box-text wppa-file-'.$t.$wppa['master_occur'].'" style="height:120px; width:'.($width-6).'px; '.__wcs('wppa-box-text').'" name="wppa-user-desc" >'.$desc.'</textarea>
		</form>
	</div>';
}

// Build the html for the comment box
function wppa_comment_html($id, $comment_allowed) {
global $wpdb;
global $wppa;
global $wppa_opt;
global $current_user;
global $wppa_first_comment_html;

	$result = '';
	if ($wppa['in_widget']) return $result;		// NOT in a widget
	
	// Find out who we are either logged in or not
	$vis = is_user_logged_in() ? $vis = 'display:none; ' : '';
	if (!$wppa_first_comment_html) {
		$wppa_first_comment_html = true;
		// Find user
		if (wppa_get_post('comname')) $wppa['comment_user'] = wppa_get_post('comname');
		if (wppa_get_post('comemail')) $wppa['comment_email'] = wppa_get_post('comemail');
		elseif (is_user_logged_in()) {
			get_currentuserinfo();
			$wppa['comment_user'] = $current_user->display_name; //user_login;
			$wppa['comment_email'] = $current_user->user_email;
		}
	}

	// Loop the comments already there
	$n_comments = 0;
	if ( wppa_switch('wppa_comments_desc') ) $ord = 'DESC'; else $ord = '';
	$comments = $wpdb->get_results($wpdb->prepare( 'SELECT * FROM '.WPPA_COMMENTS.' WHERE photo = %s ORDER BY id '.$ord, $id ), ARRAY_A );
	wppa_dbg_q('Q46v');
	$com_count = count($comments);
	$color = 'darkgrey';
	if ($wppa_opt['wppa_fontcolor_box']) $color = $wppa_opt['wppa_fontcolor_box'];
	if ($comments) {
		$result .= '<div id="wppa-comtable-wrap-'.$wppa['master_occur'].'" style="display:none;" >';
			$result .= '<table id="wppacommentstable-'.$wppa['master_occur'].'" class="wppa-comment-form" style="margin:0; "><tbody>';
			foreach($comments as $comment) {
				// Show a comment either when it is approved, or it is pending and mine or i am a moderator
				if ($comment['status'] == 'approved' || current_user_can('wppa_moderate') || current_user_can('wppa_comments') || (($comment['status'] == 'pending' || $comment['status'] == 'spam') && $comment['user'] == $wppa['comment_user'])) {
					$n_comments++;
					$result .= '<tr class="wppa-comment-'.$comment['id'].'" valign="top" style="border-bottom:0 none; border-top:0 none; border-left: 0 none; border-right: 0 none; " >';
						$result .= '<td valign="top" class="wppa-box-text wppa-td" style="vertical-align:top; width:30%; border-width: 0 0 0 0; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >';
							$result .= $comment['user'].' '.__a('wrote:');
							$result .= '<br /><span style="font-size:9px; ">'.wppa_get_time_since($comment['timestamp']).'</span>';
							if ( $wppa_opt['wppa_comment_gravatar'] != 'none') {
								// Find the default
								if ( $wppa_opt['wppa_comment_gravatar'] != 'url') {
									$default = $wppa_opt['wppa_comment_gravatar'];
								}
								else {
									$default = $wppa_opt['wppa_comment_gravatar_url'];
								}
								// Find the avatar
								$avt = '';
								$usr = get_user_by('login', $comment['user']);
								if ( $usr ) {	// Local Avatar ?
									$avt = str_replace("'", "\"", get_avatar($usr->ID, $wppa_opt['wppa_gravatar_size'], $default));
								}
								if ( $avt == '' ) {	// Global avatars off, try myself
									$avt = '<img class="wppa-box-text wppa-td" src="http://www.gravatar.com/avatar/'.md5(strtolower(trim($comment['email']))).'.jpg?d='.urlencode($default).'&s='.$wppa_opt['wppa_gravatar_size'].'" />';
								}
								// Compose the html
								$result .= '<div class="com_avatar">'.$avt.'</div>';
							}
						$result .= '</td>';
						$txtwidth = floor( wppa_get_container_width() * 0.7 ).'px';
						$result .= '<td class="wppa-box-text wppa-td" style="width:70%; word-wrap:break-word; border-width: 0 0 0 0;'.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.
										'<p class="wppa-comment-textarea-'.$wppa['master_occur'].'" style="margin:0; background-color:transparent; width:'.$txtwidth.'; max-height:90px; overflow:auto; word-wrap:break-word;'.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.
											html_entity_decode(esc_js(stripslashes(convert_smilies($comment['comment']))));
										
											if ( $comment['status'] != 'approved' && ( current_user_can('wppa_moderate') || current_user_can('wppa_comments') ) ) {
												if ( $wppa['no_esc'] ) $result .= wppa_moderate_links('comment', $id, $comment['id']);
												else $result .= wppa_html(esc_js(wppa_moderate_links('comment', $id, $comment['id'])));
											}
											elseif ($comment['status'] == 'pending' && $comment['user'] == $wppa['comment_user']) {
												$result .= '<br /><span style="color:red; font-size:9px;" >'.__a('Awaiting moderation').'</span>';
											}
											elseif ($comment['status'] == 'spam' && $comment['user'] == $wppa['comment_user']) {
												$result .= '<br /><span style="color:red; font-size:9px;" >'.__a('Marked as spam').'</span>';
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
	$is_current = ($id == $wppa['comment_photo'] && $wppa['comment_id']);
	// $debugtext=' (id='.$id.', comment_photo='.$wppa['comment_photo'].', comment_id='.$wppa['comment_id'].')';
	if ($is_current) {
		$txt = $wppa['comment_text'];
		$btn = __a('Edit!');
	}
	else {
		$txt = '';
		$btn = __a('Send!');
	}
	
	// Prepare the callback url
	$returnurl = wppa_get_permalink();

	$album = wppa_get_get('album');
	if ( $album !== false ) $returnurl .= 'wppa-album='.$album.'&';
	$cover = wppa_get_get('cover');
	if ($cover) $returnurl .= 'wppa-cover='.$cover.'&';
	$slide = wppa_get_get('slide');
	if ($slide !== false) $returnurl .= 'wppa-slide&';
	$occur = wppa_get_get('occur');
	if ($occur) $returnurl .= 'wppa-occur='.$occur.'&';
	$lasten = wppa_get_get('lasten');
	if ( $lasten ) $returnurl .= 'wppa-lasten='.$lasten.'&';
	$topten = wppa_get_get('topten');
	if ( $topten ) $returnurl .= 'wppa-topten='.$topten.'&';
	$comten = wppa_get_get('comten');
	if ( $comten ) $returnurl .= 'wppa-comten='.$comten.'&';
	$tag = wppa_get_get('tag');
	if ( $tag ) $returnurl .= 'wppa-tag='.$tag.'&';
	
	$returnurl .= 'wppa-photo='.$id;
	
	// The comment form
	if ( $comment_allowed ) {
		$result .= '<div id="wppa-comform-wrap-'.$wppa['master_occur'].'" style="display:none;" >';
			$result .= '<form id="wppa-commentform-'.$wppa['master_occur'].'" class="wppa-comment-form" action="'.$returnurl.'" method="post" style="" onsubmit="return wppaValidateComment('.$wppa['master_occur'].')">';
				$result .= wp_nonce_field('wppa-check' , 'wppa-nonce-'.$wppa['master_occur'], false, false);
				if ($album) $result .= '<input type="hidden" name="wppa-album" value="'.$album.'" />';
				if ($cover) $result .= '<input type="hidden" name="wppa-cover" value="'.$cover.'" />';
				if ($slide) $result .= '<input type="hidden" name="wppa-slide" value="'.$slide.'" />';
				if ($is_current) $result .= '<input type="hidden" id="wppa-comment-edit-'.$wppa['master_occur'].'" name="wppa-comment-edit" value="'.$wppa['comment_id'].'" />';
				$result .= '<input type="hidden" name="wppa-occur" value="'.$wppa['occur'].'" />';

				$result .= '<table id="wppacommenttable-'.$wppa['master_occur'].'" style="margin:0;">';
					$result .= '<tbody>';
						$result .= '<tr valign="top" style="'.$vis.'">';
							$result .= '<td class="wppa-box-text wppa-td" style="width:30%; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.__a('Your name:').'</td>';
							$result .= '<td class="wppa-box-text wppa-td" style="width:70%; '.__wcs('wppa-box-text').__wcs('wppa-td').'" ><input type="text" name="wppa-comname" id="wppa-comname-'.$wppa['master_occur'].'" style="width:100%; " value="'.$wppa['comment_user'].'" /></td>';
						$result .= '</tr>';
						if ( wppa_switch('wppa_comment_email_required') ) {
							$result .= '<tr valign="top" style="'.$vis.'">';
								$result .= '<td class="wppa-box-text wppa-td" style="width:30%; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.__a('Your email:').'</td>';
								$result .= '<td class="wppa-box-text wppa-td" style="width:70%; '.__wcs('wppa-box-text').__wcs('wppa-td').'" ><input type="text" name="wppa-comemail" id="wppa-comemail-'.$wppa['master_occur'].'" style="width:100%; " value="'.$wppa['comment_email'].'" /></td>';
							$result .= '</tr>';
						}
						$result .= '<tr valign="top" style="vertical-align:top;">';	
							$result .= '<td valign="top" class="wppa-box-text wppa-td" style="vertical-align:top; width:30%; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >'.__a('Your comment:').'<br />'.$wppa['comment_user'].'<br />';
							if ( wppa_switch('wppa_comment_captcha') ) {
								$wid = '20%';
								if ( $wppa_opt['wppa_fontsize_box'] ) $wid = ($wppa_opt['wppa_fontsize_box'] * 1.5 ).'px';
								$captkey = $id;
								if ( $is_current ) $captkey = $wpdb->get_var($wpdb->prepare('SELECT `timestamp` FROM `'.WPPA_COMMENTS.'` WHERE `id` = %s', $wppa['comment_id'])); 
								$result .= wppa_make_captcha($captkey).'<input type="text" id="wppa-captcha-'.$wppa['master_occur'].'" name="wppa-captcha" style="width:'.$wid.'; '.__wcs('wppa-box-text').__wcs('wppa-td').'" />&nbsp;';
							}
// orig							$result .= '<input type="submit" name="commentbtn" value="'.$btn.'" style="margin:0;" /></td>';
							$result .= '<input type="button" name="commentbtn" onclick="wppaAjaxComment('.$wppa['master_occur'].', '.$id.' )" value="'.$btn.'" style="margin:0 4px 0 0;" />';
							$result .= '<img id="wppa-comment-spin-'.$wppa['master_occur'].'" src="'.wppa_get_imgdir().'wpspin.gif" style="display:none;" />';
							$result .= '</td>';
							$result .= '<td valign="top" class="wppa-box-text wppa-td" style="vertical-align:top; width:70%; '.__wcs('wppa-box-text').__wcs('wppa-td').'" >';
/*							if ( $wppa_opt['wppa_use_wp_editor'] ) {
								$quicktags_settings = array( 'buttons' => 'strong,em,link,block,ins,ul,ol,li,code,close' );
								ob_start();
								wp_editor(stripslashes($txt), 'wppacomment'.wppa_alfa_id($id), array('wpautop' => false, 'media_buttons' => false, 'textarea_rows' => '6', 'tinymce' => false, 'quicktags' => $quicktags_settings ));
								$editor = ob_get_clean();
								$result .= str_replace("'", '"', $editor);
							}
							else {
/**/
								if ( wppa_switch('wppa_comment_smiley_picker') ) $result .= wppa_get_smiley_picker_html('wppa-comment-'.$wppa['master_occur']);
								$result .= '<textarea name="wppa-comment" id="wppa-comment-'.$wppa['master_occur'].'" style="height:60px; width:100%; ">'.esc_textarea(stripslashes($txt)).'</textarea>';
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
		$result .= sprintf(__a('You must <a href="%s">login</a> to enter a comment'), site_url('wp-login.php', 'login'));
	}
	
	$result .= '<div id="wppa-comfooter-wrap-'.$wppa['master_occur'].'" style="display:block;" >';
		$result .= '<table id="wppacommentfooter-'.$wppa['master_occur'].'" class="wppa-comment-form" style="margin:0;">';
			$result .= '<tbody><tr style="text-align:center; "><td style="text-align:center; cursor:pointer;'.__wcs('wppa-box-text').'" ><a onclick="wppaOpenComments('.$wppa['master_occur'].', -1); return false;">'; // wppaStartStop('.$wppa['master_occur'].', -1); return false;">';
			if ( $n_comments ) {
				$result .= sprintf(__a('%d  comments'), $n_comments);
			}
			else {
				if ( $comment_allowed ) {
					$result .= __a('Leave a comment');
				}
			}
		$result .= '</a></td></tr></tbody></table>';
	$result .= '</div>';

	return $result;
}

function wppa_get_smiley_picker_html($elm_id) {
static $wppa_smilies;
global $wpsmiliestrans;

	// Fill inverted smilies array if needed
	if ( ! is_array($wppa_smilies) ) {
		foreach( array_keys($wpsmiliestrans) as $idx) {
			if ( ! isset ($wppa_smilies[$wpsmiliestrans[$idx]]) ) {
				$wppa_smilies[$wpsmiliestrans[$idx]] = $idx;
			}
		}
	}
	
	// Make the html
	$result = '';
	foreach ( array_keys($wppa_smilies) as $key ) {
		$onclick = esc_attr('wppaInsertAtCursor(document.getElementById("'.$elm_id.'"), " '.$wppa_smilies[$key].' ")');
		$title = substr(substr($key, 5), 0, -4);
		$result .= '<img src="'.esc_attr(includes_url( 'images/smilies/' ).$key).'" onclick="'.$onclick.'" title="'.$title.'" /> ';
	}
	
	return $result;
} 

// IPTC box
function wppa_iptc_html($photo) {
global $wppa;
global $wpdb;
global $wppaiptcdefaults;
global $wppaiptclabels;

	// Get the default (one time only)
	if ( ! $wppa['iptc'] ) {
		$tmp = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_IPTC."` WHERE `photo` = %s ORDER BY `tag`", '0'), "ARRAY_A");
		wppa_dbg_q('Q47');
		if ( ! $tmp ) return '';	// Nothing defined
		$wppaiptcdefaults = false;	// Init
		$wppaiptclabels = false;	// Init
		foreach ($tmp as $t) {
			$wppaiptcdefaults[$t['tag']] = $t['status'];
			$wppaiptclabels[$t['tag']] = $t['description'];
		}
		$wppa['iptc'] = true;
	}
	else wppa_dbg_q('G47');

	$count = 0;

	// Get the photo data
	$iptcdata = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_IPTC."` WHERE `photo` = %s ORDER BY `tag`", $photo), "ARRAY_A");
	wppa_dbg_q('Q48v');
	if ( $iptcdata ) {
		// Open the container content
		$result = '<div id="iptccontent-'.$wppa['master_occur'].'" >';
		// Open or closed?
		$d1 = wppa_switch('wppa_show_exif_open') ? 'display:none;' : 'display:inline;';
		$d2 = wppa_switch('wppa_show_exif_open') ? 'display:inline;' : 'display:none;';
		// Process data
		$onclick = esc_attr("wppaStopShow(".$wppa['master_occur']."); jQuery('.wppa-iptc-table-".$wppa['master_occur']."').css('display', ''); jQuery('.-wppa-iptc-table-".$wppa['master_occur']."').css('display', 'none')");
		$result .= '<a class="-wppa-iptc-table-'.$wppa['master_occur'].'" onclick="'.$onclick.'" style="cursor:pointer;'.$d1.'" >'.__a('Show IPTC data').'</a>';

		$onclick = esc_attr("jQuery('.wppa-iptc-table-".$wppa['master_occur']."').css('display', 'none'); jQuery('.-wppa-iptc-table-".$wppa['master_occur']."').css('display', '')");
		$result .= '<a class="wppa-iptc-table-'.$wppa['master_occur'].'" onclick="'.$onclick.'" style="cursor:pointer;'.$d2.'" >'.__a('Hide IPTC data').'</a>';

		$result .= '<div style="clear:both;" ></div><table class="wppa-iptc-table-'.$wppa['master_occur'].' wppa-detail" style="border:0 none; margin:0;'.$d2.'" ><tbody>';
		$oldtag = '';
		foreach ( $iptcdata as $iptcline ) {
			if ( $iptcline['status'] == 'hide' ) continue;														// Photo status is hide
			if ( $iptcline['status'] == 'default' && $wppaiptcdefaults[$iptcline['tag']] == 'hide' ) continue;	// P s is default and default is hide
			if ( $iptcline['status'] == 'default' && $wppaiptcdefaults[$iptcline['tag']] == 'option' && ! trim($iptcline['description'], "\x00..\x1F ") ) continue;	// P s is default and default is optional and field is empty
			
			$count++;
			$newtag = $iptcline['tag'];
			if ( $newtag != $oldtag && $oldtag != '') $result .= '</td></tr>';	// Close previous line
			if ( $newtag == $oldtag ) {
				$result .= '; ';							// next item with same tag
			}
			else {
				$result .= '<tr style="border-bottom:0 none; border-top:0 none; border-left: 0 none; border-right: 0 none; "><td class="wppa-iptc-label wppa-box-text wppa-td" style="'.__wcs('wppa-box-text').__wcs('wppa-td').'" >';						// Open new line
				$result .= esc_js(__($wppaiptclabels[$newtag]));
				$result .= '</td><td class="wppa-iptc-value wppa-box-text wppa-td" style="'.__wcs('wppa-box-text').__wcs('wppa-td').'" >';
			}
			$result .= esc_js(trim(__($iptcline['description'])));
			$oldtag = $newtag;
		}	
		if ( $oldtag != '' ) $result .= '</td></tr>';	// Close last line
		$result .= '</tbody></table></div>';
	}
	if ( ! $count ) {
		$result = '<div id="iptccontent-'.$wppa['master_occur'].'" >'.__a('No IPTC data').'</div>';
	}

	return ($result);
}

// EXIF box
function wppa_exif_html($photo) {
global $wppa;
global $wpdb;
global $wppaexifdefaults;
global $wppaexiflabels;

	// Get the default (one time only)
	if ( ! $wppa['exif'] ) {
		$tmp = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_EXIF."` WHERE `photo` = %s ORDER BY `tag`", '0'), "ARRAY_A");
		wppa_dbg_q('Q49');
		if ( ! $tmp ) return '';	// Nothing defined
		$wppaexifdefaults = false;	// Init
		$wppaexiflabels = false;	// Init
		foreach ($tmp as $t) {
			$wppaexifdefaults[$t['tag']] = $t['status'];
			$wppaexiflabels[$t['tag']] = $t['description'];
		}
		$wppa['exif'] = true;
	}
	else wppa_dbg_q('G49');

	$count = 0;

	// Get the photo data
	$exifdata = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_EXIF."` WHERE `photo`=%s ORDER BY `tag`", $photo), "ARRAY_A");
	wppa_dbg_q('Q50v');
	if ( $exifdata ) {
		// Open the container content
		$result = '<div id="exifcontent-'.$wppa['master_occur'].'" >';
		// Open or closed?
		$d1 = wppa_switch('wppa_show_exif_open') ? 'display:none;' : 'display:inline;';
		$d2 = wppa_switch('wppa_show_exif_open') ? 'display:inline;' : 'display:none;';
		// Process data
		$onclick = esc_attr("wppaStopShow(".$wppa['master_occur']."); jQuery('.wppa-exif-table-".$wppa['master_occur']."').css('display', ''); jQuery('.-wppa-exif-table-".$wppa['master_occur']."').css('display', 'none')");
		$result .= '<a class="-wppa-exif-table-'.$wppa['master_occur'].'" onclick="'.$onclick.'" style="cursor:pointer;'.$d1.'" >'.__a('Show EXIF data').'</a>';

		$onclick = esc_attr("jQuery('.wppa-exif-table-".$wppa['master_occur']."').css('display', 'none'); jQuery('.-wppa-exif-table-".$wppa['master_occur']."').css('display', '')");
		$result .= '<a class="wppa-exif-table-'.$wppa['master_occur'].'" onclick="'.$onclick.'" style="cursor:pointer;'.$d2.'" >'.__a('Hide EXIF data').'</a>';

		$result .= '<div style="clear:both;" ></div><table class="wppa-exif-table-'.$wppa['master_occur'].' wppa-detail" style="'.$d2.' border:0 none; margin:0;" ><tbody>';
		$oldtag = '';
		foreach ( $exifdata as $exifline ) {
			if ( $exifline['status'] == 'hide' ) continue;														// Photo status is hide
			if ( $exifline['status'] == 'default' && $wppaexifdefaults[$exifline['tag']] == 'hide' ) continue;	// P s is default and default is hide
			if ( $exifline['status'] == 'default' && $wppaexifdefaults[$exifline['tag']] == 'option' && ! trim($exifline['description'], "\x00..\x1F ") ) continue; // P s is default and default is optional and field is empty

			$count++;
			$newtag = $exifline['tag'];
			if ( $newtag != $oldtag && $oldtag != '') $result .= '</td></tr>';	// Close previous line
			if ( $newtag == $oldtag ) {
				$result .= '; ';							// next item with same tag
			}
			else {
				$result .= '<tr style="border-bottom:0 none; border-top:0 none; border-left: 0 none; border-right: 0 none; "><td class="wppa-exif-label wppa-box-text wppa-td" style="'.__wcs('wppa-box-text').__wcs('wppa-td').'" >';						// Open new line
				$result .= esc_js(__($wppaexiflabels[$newtag]));
				$result .= '</td><td class="wppa-exif-value wppa-box-text wppa-td" style="'.__wcs('wppa-box-text').__wcs('wppa-td').'" >';
			}
			$result .= esc_js(trim(__(wppa_format_exif($exifline['tag'], $exifline['description']))));
			$oldtag = $newtag;
		}	
		if ( $oldtag != '' ) $result .= '</td></tr>';	// Close last line
		$result .= '</tbody></table></div>';
	}
	if ( ! $count ) {
		$result = '<div id="exifcontent-'.$wppa['master_occur'].'" >'.__a('No EXIF data').'</div>';
	}
	
	return ($result);
}

// Display the album name ( on a thumbnail display ) either on top or at the bottom of the thumbnail area
function wppa_album_name($key) {
global $wppa;
global $wppa_opt;
global $wpdb;

	if ( $wppa['is_upldr'] ) return;
	
	$result = '';
	if ( $wppa_opt['wppa_albname_on_thumbarea'] == $key && $wppa['current_album'] ) {
		$name = wppa_get_album_name($wppa['current_album']);
		if ( $key == 'top' ) {
			$result .= '<h3 id="wppa-albname-'.$wppa['master_occur'].'" class="wppa-box-text wppa-black" style="padding-right:6px; margin:0; '.__wcs('wppa-box-text').__wcs('wppa-black').'" >'.$name.'</h3><div style="clear:both" ></div>';
		}
		if ( $key == 'bottom' ) {
			$result .= '<h3 id="wppa-albname-b-'.$wppa['master_occur'].'" class="wppa-box-text wppa-black" style="clear:both; padding-right:6px; margin:0; '.__wcs('wppa-box-text').__wcs('wppa-black').'" >'.$name.'</h3>';
		}
	}
	$wppa['out'] .= $result;
}

// Display the album description ( on a thumbnail display ) either on top or at the bottom of the thumbnail area
function wppa_album_desc($key) {
global $wppa;
global $wppa_opt;
global $wpdb;

	if ( $wppa['is_upldr'] ) return;
	
	$result = '';
	if ( $wppa_opt['wppa_albdesc_on_thumbarea'] == $key && $wppa['current_album'] ) {
		$desc = wppa_get_album_desc($wppa['current_album']);
		if ( $key == 'top' ) {
			$result .= '<div id="wppa-albdesc-'.$wppa['master_occur'].'" class="wppa-box-text wppa-black" style="padding-right:6px;'.__wcs('wppa-box-text').__wcs('wppa-black').'" >'.$desc.'</div><div style="clear:both" ></div>';
		}
		if ( $key == 'bottom' ) {
			$result .= '<div id="wppa-albdesc-b-'.$wppa['master_occur'].'" class="wppa-box-text wppa-black" style="clear:both; padding-right:6px;'.__wcs('wppa-box-text').__wcs('wppa-black').'" >'.$desc.'</div>';
		}
	}
	$wppa['out'] .= $result;
}

function wppa_auto_page_links( $where ) {
global $wppa_opt;
global $wppa;
global $wpdb;
global $thumb;

	$m = $where == 'bottom' ? 'margin-top:8px;' : '';
	$mustwhere = $wppa_opt['wppa_auto_page_links'];
	if ( ( $mustwhere == 'top' || $mustwhere == 'both' ) && ( $where == 'top' ) || ( ( $mustwhere == 'bottom' || $mustwhere == 'both' ) && ( $where == 'bottom' ) ) ) {
		$wppa['out'] .= '
			<div id="prevnext1-'.$wppa['master_occur'].'" class="wppa-box wppa-nav wppa-nav-text" style="text-align: center; '.__wcs('wppa-box').__wcs('wppa-nav').__wcs('wppa-nav-text').$m.'">';
		$photo = $wppa['single_photo'];
		wppa_cache_thumb( $photo );
		$album = $thumb['album'];
		$photos = $wpdb->get_results( $wpdb->prepare( "SELECT `id`, `page_id` FROM `".WPPA_PHOTOS."` WHERE `album` = %s ".wppa_get_photo_order( $album ), $album ), ARRAY_A );
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
			<a href="'.get_permalink($prevpag).'" style="float:left" >'.__('< Previous', 'wppa').'</a>';
		}
		else {
			$wppa['out'] .= '
			<span style="visibility:hidden" >'.__('< Previous', 'wppa').'</span>';
		}
		$wppa['out'] .= ++$current.'/'.$count;
		if ( $nextpag ) {
			$wppa['out'] .= '
			<a href="'.get_permalink($nextpag).'" style="float:right" >'.__('Next >', 'wppa').'</a>';
		}
		else {
			$wppa['out'] .= '
			<span style="visibility:hidden" >'.__('Next >', 'wppa').'</span>';
		}

		$wppa['out'] .= '
			</div><div style="clear:both"></div>';
	}
}