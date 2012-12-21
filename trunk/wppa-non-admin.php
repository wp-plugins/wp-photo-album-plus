<?php 
/* wppa-non-admin.php
* Package: wp-photo-album-plus
*
* Contains all the non admin stuff
* Version 4.8.8
*
*/

/* API FILTER and FUNCTIONS */
require_once 'wppa-filter.php';
require_once 'wppa-slideshow.php';
require_once 'wppa-functions.php';
	
/* LOAD STYLESHEET */
add_action('wp_print_styles', 'wppa_add_style');

function wppa_add_style() {
	$userstyle = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa-style.css';
	if ( is_file($userstyle) ) {
		wp_register_style('wppa_style', '/wp-content/themes/' . get_option('template')  . '/wppa-style.css');
		wp_enqueue_style('wppa_style');
	} else {
		wp_register_style('wppa_style', WPPA_URL.'/theme/wppa-style.css');
		wp_enqueue_style('wppa_style');
	}
}

/* SEO META TAGS AND SM SHARE DATA */
add_action('wp_head', 'wppa_add_metatags');

function wppa_add_metatags() {
global $wpdb;
global $wppa_opt;
global $thumb;

	// If a photo is given in the querystring, it may be a sm examinig the site for a share, do not supply metatags
	if ( wppa_get_get('photo') ) {
		// Share info for sm that uses og
		$id = wppa_get_get('photo');
		if ( is_numeric($id) ) {
			wppa_cache_thumb($id);
			if ( $thumb ) {
				$title  = __(stripslashes($thumb['name']));
				$imgurl = WPPA_UPLOAD_URL.'/thumbs/'.$id.'.'.$thumb['ext'];
				$desc   = sprintf(__a('See this image on %s', 'wppa_theme'), str_replace('&amp;', __a('and', 'wppa_theme'), get_bloginfo('name')));
				$pdesc  = wppa_strip_tags(wppa_html(__(stripslashes($thumb['description']))), 'all');
				$url    = wppa_convert_to_pretty(str_replace('&amp;', '&', wppa_get_image_page_url_by_id($thumb['id'], $wppa_opt['wppa_share_single_image'])));
				$site   = get_bloginfo('name');
				if ( $pdesc ) $desc .= ': '.$pdesc;
				echo "\n<!-- WPPA+ Share data -->".'
	<meta property="og:type" content="article" />
	<meta property="og:url" content="'.esc_attr($url).'" />
	<meta property="og:site_name" content="'.esc_attr($site).'" />
	<meta property="og:title" content="'.esc_attr($title).'" />
	<meta property="og:image" content="'.esc_attr($imgurl).'" />
	<meta property="og:description" content="'.esc_attr($desc).'" />';				
				echo "\n<!-- WPPA+ End Share data -->\n";
			}
		}
	}

	// To make sure we are on a page that contains at least %%wppa%% we check for $_GET['wppa-album']. 
	// This also narrows the selection of featured photos to those that exist in the current album.
	elseif ( wppa_get_get('album') ) {
		if ( $wppa_opt['wppa_meta_page'] ) {
			$album = wppa_get_get('album');
			$photos = $wpdb->get_results($wpdb->prepare( "SELECT `id`, `name` FROM `".WPPA_PHOTOS."` WHERE `album` = %s AND `status` = 'featured'", $album ), ARRAY_A);
			if ( $photos ) {
				echo("\n<!-- WPPA+ BEGIN Featured photos on this page -->");
				foreach ( $photos as $photo ) {
					$id = $photo['id'];
					$name = esc_attr(__($photo['name']));
					$content = wppa_get_permalink().'wppa-photo='.$photo['id'].'&amp;wppa-occur=1';
					$content = wppa_convert_to_pretty($content);
					echo("\n<meta name=\"".$name."\" content=\"".$content."\" >");
				}
				echo("\n<!-- WPPA+ END Featured photos on this page -->\n");
			}
		}
	}
	
	// No photo and no album, give the plain photo links of all featured photos
	elseif ( $wppa_opt['wppa_meta_all'] ) {
		$photos = $wpdb->get_results( "SELECT `id`, `name`, `ext` FROM `".WPPA_PHOTOS."` WHERE `status` = 'featured'", ARRAY_A);
		if ( $photos ) {
			echo("\n<!-- WPPA+ BEGIN Featured photos on this site -->");
			foreach ( $photos as $photo ) {
				$id = $photo['id'];
				$name = esc_attr(__($photo['name']));
				$ext = $photo['ext'];
				$content = WPPA_UPLOAD_URL.'/'.$id.'.'.$ext;
				echo("\n<meta name=\"".$name."\" content=\"".$content."\" >");
			}
			echo("\n<!-- WPPA+ END Featured photos on this site -->\n");
		}
	}
}

/* LOAD SLIDESHOW, THEME, AJAX and LIGHTBOX js, all in one file nowadays */
add_action('init', 'wppa_add_javascripts');
	
function wppa_add_javascripts() {
	if ( is_file(WPPA_PATH.'/wppa.min.js') ) {
		wp_enqueue_script('wppa', WPPA_URL.'/wppa.min.js', array('jquery'));
	}
	else {
		wp_enqueue_script('wppa', WPPA_URL.'/wppa.js', array('jquery'));
	}
}
	
/* LOAD WPPA+ THEME */
add_action('init', 'wppa_load_theme');
	
function wppa_load_theme() {
	$usertheme = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa-theme.php';
	if ( is_file($usertheme) ) {
		require_once $usertheme;
	} else {
		require_once 'theme/wppa-theme.php';
	}
}
	
/* LOAD FOOTER REQD DATA */
add_action('wp_footer', 'wppa_load_footer');

function wppa_load_footer() {
global $wppa_opt;
	if ($wppa_opt['wppa_lightbox_name'] == 'wppa') {
		echo("\n<!-- start WPPA+ Footer data -->\n");
		echo('
			<div id="wppa-overlay-bg" style="text-align:center; display:none; position:fixed; top:0; left:0; z-index:100090; width:100%; height:2048px; background-color:black;" onclick="wppaOvlOnclick(event)" ></div>
			<div id="wppa-overlay-ic" style="position:fixed; top:0; padding-top:10px; z-index:100095; opacity:1; box-shadow:none;" '.
			' ontouchstart="wppaTouchStart(event, \'wppa-overlay-ic\', -1);"  ontouchend="wppaTouchEnd(event);" ontouchmove="wppaTouchMove(event);" ontouchcancel="wppaTouchCancel(event);" '.
			'></div>
			<img id="wppa-overlay-sp" style="position:fixed; top:200px; left:200px; z-index:100100; opacity:1; visibility:hidden; box-shadow:none;" src="'.wppa_get_imgdir().'loading.gif" />
			');
		echo("\n".'<script type="text/javascript">jQuery("#wppa-overlay-bg").css({height:screen.height+"px"});');
		if ( $wppa_opt['wppa_ovl_txt_lines'] == 'auto' ) {
			echo ("\n\t\t\t".'wppaOvlTxtHeight = "auto";');
		}
		else {
			if ( ! $wppa_opt['wppa_fontsize_lightbox'] ) $wppa_opt['wppa_fontsize_lightbox'] = '10';
			$d = $wppa_opt['wppa_ovl_show_counter'] ? 1 : 0;
			echo ("\n\t\t\t".'wppaOvlTxtHeight = '.(($wppa_opt['wppa_ovl_txt_lines'] + $d) * ($wppa_opt['wppa_fontsize_lightbox'] + 2)).';');
		}
		echo('
			wppaOvlCloseTxt = "'.__($wppa_opt['wppa_ovl_close_txt']).'";
			wppaOvlOpacity = '.($wppa_opt['wppa_ovl_opacity']/100).';
			wppaOvlOnclickType = "'.$wppa_opt['wppa_ovl_onclick'].'";
			wppaOvlTheme = "'.$wppa_opt['wppa_ovl_theme'].'";
			wppaOvlAnimSpeed = '.$wppa_opt['wppa_ovl_anim'].';
			wppaVer4WindowWidth = 800;
			wppaVer4WindowHeight = 600;');
			if ( $wppa_opt['wppa_ovl_show_counter'] ) echo ('
			wppaOvlShowCounter = true;');
			else echo ('
			wppaOvlShowCounter = false;');
			if ( $wppa_opt['wppa_fontfamily_lightbox'] ) echo ('
			wppaOvlFontFamily = "'.$wppa_opt['wppa_fontfamily_lightbox'].'";');
			if ( $wppa_opt['wppa_fontsize_lightbox'] ) echo ('
			wppaOvlFontSize = "'.$wppa_opt['wppa_fontsize_lightbox'].'";');
			if ( $wppa_opt['wppa_fontcolor_lightbox'] ) echo ('
			wppaOvlFontColor = "'.$wppa_opt['wppa_fontcolor_lightbox'].'";');
			if ( $wppa_opt['wppa_fontweight_lightbox'] ) echo ('
			wppaOvlFontWeight = "'.$wppa_opt['wppa_fontweight_lightbox'].'";');
			if ( $wppa_opt['wppa_fontsize_lightbox'] ) echo ('
			wppaOvlLineHeight = "'.($wppa_opt['wppa_fontsize_lightbox'] + '2').'";');
			echo ('
			</script>');
		echo("\n<!-- end WPPA+ Footer data -->\n");
		wppa_dbg_q('print');
	}
}

/* CHECK REDIRECTION */
add_action('init', 'wppa_redirect');

function wppa_redirect() {
	if ( ! isset($_ENV["SCRIPT_URI"]) ) return;
	$uri = $_ENV["SCRIPT_URI"];
	$wppapos = stripos($uri, '/wppaspec/');
	if ( $wppapos && get_option('permalink_structure') && get_option('wppa_use_pretty_links') == 'yes' ) {
		$newuri = wppa_convert_from_pretty($uri);
		if ( $newuri == $uri ) return;
		wp_redirect($newuri);
		exit;
	}
}

/* LOAD JS VARS AND ENABLE RENDERING */
add_action('wp_head', 'wppa_kickoff', '100');

function wppa_kickoff() {
global $wppa;
global $wppa_opt;
global $wppa_locale;

	echo("\n<!-- WPPA+ Runtime parameters -->\n");
	
	echo('<script type="text/javascript">'."\n");
	echo('/* <![CDATA[ */'."\n");
	
		/* Check if wppa.js and jQuery are present */
		if ( WPPA_DEBUG || isset($_GET['wppa-debug']) || WP_DEBUG ) {
			echo("\t"."if (typeof(_wppaSlides) == 'undefined') alert('There is a problem with your theme. The file wppa.js is not loaded when it is expected (Errloc = wppa_kickoff).');");
			echo("\t"."if (typeof(jQuery) == 'undefined') alert('There is a problem with your theme. The jQuery library is not loaded when it is expected (Errloc = wppa_kickoff).');");
		}
		/* This goes into wppa.js */ 
		echo '
	wppaBackgroundColorImage = "'.$wppa_opt['wppa_bgcolor_img'].'";
	wppaPopupLinkType = "'.$wppa_opt['wppa_thumb_linktype'].'";
	wppaAnimationType = "'.$wppa_opt['wppa_animation_type'].'";
	wppaAnimationSpeed = '.$wppa_opt['wppa_animation_speed'].';
	wppaImageDirectory = "'.wppa_get_imgdir().'";
	wppaThumbnailAreaDelta = '.wppa_get_thumbnail_area_delta().';
	wppaTextFrameDelta = '.wppa_get_textframe_delta().';
	wppaBoxDelta = '.wppa_get_box_delta().';
	wppaSlideShowTimeOut = '.$wppa_opt['wppa_slideshow_timeout'].';
	wppaPreambule = '.wppa_get_preambule().';';
		if ($wppa_opt['wppa_film_show_glue'] == 'yes') echo '
	wppaFilmShowGlue = true;
	wppaSlideShow = "'.__a('Slideshow', 'wppa_theme').'";
	wppaStart = "'.__a('Start', 'wppa_theme').'";
	wppaStop = "'.__a('Stop', 'wppa_theme').'";
	wppaSlower = "'.__a('Slower', 'wppa_theme').'";
	wppaFaster = "'.__a('Faster', 'wppa_theme').'";
	wppaPhoto = "'.__a('Photo', 'wppa_theme').'";
	wppaOf = "'.__a('of', 'wppa_theme').'";
	wppaPreviousPhoto = "'.__a('Previous photo', 'wppa_theme').'";
	wppaNextPhoto = "'.__a('Next photo', 'wppa_theme').'";
	wppaPrevP = "'.__a('Prev.', 'wppa_theme').'";
	wppaNextP = "'.__a('Next', 'wppa_theme').'";
	wppaMiniTreshold = '.$wppa_opt['wppa_mini_treshold'].';';
		echo("\n\t".'wppaUserName = "'.wppa_get_user().'";'."\n");
		if ($wppa_opt['wppa_rating_change'] || $wppa_opt['wppa_rating_multi']) echo("\t".'wppaRatingOnce = false;'."\n");
		else echo("\t".'wppaRatingOnce = true;'."\n");
		echo("\t".'wppaPleaseName = "'.__a('Please enter your name', 'wppa_theme').'";'."\n");
		echo("\t".'wppaPleaseEmail = "'.__a('Please enter a valid email address', 'wppa_theme').'";'."\n");
		echo("\t".'wppaPleaseComment = "'.__a('Please enter a comment', 'wppa_theme').'";'."\n");
		if ( $wppa_opt['wppa_hide_when_empty'] ) echo("\t".'wppaHideWhenEmpty = true;'."\n");
		echo("\t".'wppaBGcolorNumbar = "'.$wppa_opt['wppa_bgcolor_numbar'].'";'."\n");
		echo("\t".'wppaBcolorNumbar = "'.$wppa_opt['wppa_bcolor_numbar'].'";'."\n");
		echo("\t".'wppaBGcolorNumbarActive = "'.$wppa_opt['wppa_bgcolor_numbar_active'].'";'."\n");
		echo("\t".'wppaBcolorNumbarActive = "'.$wppa_opt['wppa_bcolor_numbar_active'].'";'."\n");
		echo("\t".'wppaFontFamilyNumbar = "'.$wppa_opt['wppa_fontfamily_numbar'].'";'."\n");
		echo("\t".'wppaFontSizeNumbar = "'.$wppa_opt['wppa_fontsize_numbar'].'px";'."\n");
		echo("\t".'wppaFontColorNumbar = "'.$wppa_opt['wppa_fontcolor_numbar'].'";'."\n");
		echo("\t".'wppaFontWeightNumbar = "'.$wppa_opt['wppa_fontweight_numbar'].'";'."\n");
		echo("\t".'wppaFontFamilyNumbarActive = "'.$wppa_opt['wppa_fontfamily_numbar_active'].'";'."\n");
		echo("\t".'wppaFontSizeNumbarActive = "'.$wppa_opt['wppa_fontsize_numbar_active'].'px";'."\n");
		echo("\t".'wppaFontColorNumbarActive = "'.$wppa_opt['wppa_fontcolor_numbar_active'].'";'."\n");
		echo("\t".'wppaFontWeightNumbarActive = "'.$wppa_opt['wppa_fontweight_numbar_active'].'";'."\n");
		echo("\t".'wppaNumbarMax = "'.$wppa_opt['wppa_numbar_max'].'";'."\n");
		if ($wppa_locale) echo("\t".'wppaLocale = "'.$wppa_locale.'";'."\n");
		echo("\t".'wppaAjaxUrl = "'.admin_url('admin-ajax.php').'";'."\n");
		if ($wppa_opt['wppa_next_on_callback']) echo("\t".'wppaNextOnCallback = true;'."\n");
		else echo("\t".'wppaNextOnCallback = false;'."\n");
		if ($wppa_opt['wppa_rating_use_ajax']) echo("\t".'wppaRatingUseAjax = true;'."\n");
		else if ($wppa_opt['wppa_rating_use_ajax']) echo("\t".'wppaRatingUseAjax = false;'."\n");
		echo("\t".'wppaStarOpacity = '.($wppa_opt['wppa_star_opacity']/'100').';'."\n");
		// Preload checkmark and clock images
		echo("\t".'wppaTickImg.src = "'.wppa_get_imgdir().'tick.png";'."\n");
		echo("\t".'wppaClockImg.src = "'.wppa_get_imgdir().'clock.png";'."\n");
		if ($wppa_opt['wppa_slide_wrap'] == 'yes') echo("\t".'wppaSlideWrap = true;'."\n");
		else echo("\t".'wppaSlideWrap = false;'."\n");
		switch ($wppa_opt['wppa_slideshow_linktype']) {
			case 'none':
				echo("\t".'wppaLightBox = "";'."\n");		// results in omitting the anchor tag
				break;
			case 'file':
				echo("\t".'wppaLightBox = "file";'."\n");	// gives anchor tag with rel="file"
				break;
			case 'lightbox':
				echo("\t".'wppaLightBox = "'.$wppa_opt['wppa_lightbox_name'].'";'."\n");	// gives anchor tag with rel="lightbox" or the like
				break;
		}
		if ( $wppa_opt['wppa_comment_email_required'] ) echo("\t".'wppaEmailRequired = true;'."\n");
		else echo("\t".'wppaEmailRequired = false;'."\n");
		if ( is_numeric($wppa_opt['wppa_fullimage_border_width']) ) $temp = $wppa_opt['wppa_fullimage_border_width'] + '1'; else $temp = '0';
		echo("\t".'wppaSlideBorderWidth = '.$temp.';'."\n");
		if ( $wppa_opt['wppa_allow_ajax'] ) echo("\t".'wppaAllowAjax = true;'."\n"); 
		else echo("\t".'wppaAllowAjax = false;'."\n");
		if ( $wppa_opt['wppa_use_photo_names_in_urls'] ) echo("\t".'wppaUsePhotoNamesInUrls = true;'."\n"); 
		else echo("\t".'wppaUsePhotoNamesInUrls = false;'."\n"); 
		if ( $wppa_opt['wppa_thumb_blank'] ) echo("\t".'wppaThumbTargetBlank = true;'."\n");
		else echo("\t".'wppaThumbTargetBlank = false;'."\n");
		echo ("\t".'wppaRatingMax = '.$wppa_opt['wppa_rating_max'].';'."\n");
		echo ("\t".'wppaRatingDisplayType = "'.$wppa_opt['wppa_rating_display_type'].'";'."\n");
		echo ("\t".'wppaRatingPrec = '.$wppa_opt['wppa_rating_prec'].';'."\n");
		if ( $wppa_opt['wppa_enlarge'] ) echo ("\t".'wppaStretch = true;'."\n");
		else ("\t".'wppaStretch = false;'."\n");
		echo ("\t".'wppaMinThumbSpace = '.$wppa_opt['wppa_tn_margin'].';'."\n");
		if ( $wppa_opt['wppa_thumb_auto'] ) echo ("\t".'wppaThumbSpaceAuto = true;'."\n");
		else ("\t".'wppaThumbSpaceAuto = false;'."\n");
		echo ("\t".'wppaMagnifierCursor = "'.$wppa_opt['wppa_magnifier'].'";'."\n");
		echo ("\t".'wppaArtMonkyLink = "'.$wppa_opt['wppa_art_monkey_link'].'";'."\n");
		if ( $wppa_opt['wppa_auto_open_comments'] ) echo ("\t".'wppaAutoOpenComments = true;'."\n");
		else echo ("\t".'wppaAutoOpenComments = false;'."\n");
		

	echo("/* ]]> */\n");
	echo("</script>\n");

	// Pinterest js
	if ( ( $wppa_opt['wppa_share_on'] || $wppa_opt['wppa_share_on_widget'] ) && $wppa_opt['wppa_share_pinterest'] ) {
		echo("\n<!-- WPPA+ Pinterest share -->\n");
		echo('<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>');
		echo("\n<!-- end WPPA+ Pinterest share -->\n");
	}

	$wppa['rendering_enabled'] = true;
	echo("\n<!-- WPPA+ Rendering enabled -->\n");
	if ($wppa['debug']) {
		error_reporting($wppa['debug']);
		add_action('wp_footer', 'wppa_phpinfo');
	}
}

/* ADD ADMIN BAR */
require_once 'wppa-adminbar.php';
