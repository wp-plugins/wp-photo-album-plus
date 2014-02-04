<?php 
/* wppa-non-admin.php
* Package: wp-photo-album-plus
*
* Contains all the non admin stuff
* Version 5.2.10
*
*/

/* API FILTER and FUNCTIONS */
require_once 'wppa-filter.php';
require_once 'wppa-slideshow.php';
require_once 'wppa-functions.php';
require_once 'wppa-breadcrumb.php';
require_once 'wppa-album-covers.php';
require_once 'wppa-links.php';
require_once 'wppa-boxes-html.php';
require_once 'wppa-styles.php';
require_once 'wppa-cart.php';
	
/* LOAD STYLESHEET */
add_action('wp_print_styles', 'wppa_add_style');

function wppa_add_style() {
global $wppa_api_version;
	// In child theme?
	$userstyle = ABSPATH . 'wp-content/themes/' . get_option('stylesheet') . '/wppa-style.css';
	if ( is_file($userstyle) ) {
		wp_register_style('wppa_style', '/wp-content/themes/' . get_option('stylesheet')  . '/wppa-style.css', array(), $wppa_api_version);
		wp_enqueue_style('wppa_style');
		return;
	}
	// In theme?
	$userstyle = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa-style.css';
	if ( is_file($userstyle) ) {
		wp_register_style('wppa_style', '/wp-content/themes/' . get_option('template')  . '/wppa-style.css', array(), $wppa_api_version);
		wp_enqueue_style('wppa_style');
		return;
	}
	// Use standard
	wp_register_style('wppa_style', WPPA_URL.'/theme/wppa-style.css', array(), $wppa_api_version);
	wp_enqueue_style('wppa_style');
}

/* SEO META TAGS AND SM SHARE DATA */
add_action('wp_head', 'wppa_add_metatags');

function wppa_add_metatags() {
global $wpdb;
global $wppa_opt;
global $thumb;

	// Share info for sm that uses og
	$id = wppa_get_get('photo', '0');
	if ( ! is_numeric($id) ) $id = '0';
	if ( get_option('wppa_og_tags_on', 'yes') == 'yes' ) {
		wppa_cache_thumb($id);
		if ( $thumb ) {
			$title  = wppa_get_photo_name($thumb['id']);
			$imgurl = wppa_get_photo_url($id);
			$desc 	= wppa_get_og_desc($thumb['id']); 
			$url    = wppa_convert_to_pretty(str_replace('&amp;', '&', wppa_get_image_page_url_by_id($thumb['id'], $wppa_opt['wppa_share_single_image'])));
			$site   = get_bloginfo('name');

		echo "\n<!-- WPPA+ Share data -->".'
	<meta property="og:site_name" content="'.esc_attr($site).'" />
	<meta property="og:type" content="article" />
	<meta property="og:url" content="'.esc_attr($url).'" /><!-- dynamicly updated -->
	<meta property="og:title" content="'.esc_attr($title).'" /><!-- dynamicly updated -->
	<meta property="og:image" content="'.esc_attr($imgurl).'" /><!-- dynamicly updated -->
	<meta property="og:description" content="'.esc_attr($desc).'" /><!-- dynamicly updated -->';				
		echo "\n<!-- WPPA+ End Share data -->\n";
		}
	}

	// To make sure we are on a page that contains at least %%wppa%% we check for $_GET['wppa-album']. 
	// This also narrows the selection of featured photos to those that exist in the current album.
	if ( wppa_get_get('album') ) {
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
				$id 		= $photo['id'];
				$name 		= esc_attr(wppa_get_photo_name($id));
				$content 	= wppa_get_photo_url($id);
				echo("\n<meta name=\"".$name."\" content=\"".$content."\" >");
			}
			echo("\n<!-- WPPA+ END Featured photos on this site -->\n");
		}
	}
	
	// Facebook Admin and App
	if ( ( wppa_switch('wppa_share_on') ||  wppa_switch('wppa_share_on_widget') ) && ( wppa_switch('wppa_facebook_comments') || wppa_switch('wppa_facebook_like') ) ) {
		echo("\n<!-- WPPA+ BEGIN Facebook meta tags -->");
		if ( $wppa_opt['wppa_facebook_admin_id'] ) {
			echo ("\n\t<meta property=\"fb:admins\" content=\"".$wppa_opt['wppa_facebook_admin_id']."\" />");
		}
		if ( $wppa_opt['wppa_facebook_app_id'] ) {
			echo ("\n\t<meta property=\"fb:app_id\" content=\"".$wppa_opt['wppa_facebook_app_id']."\" />");
		}
		echo("\n<!-- WPPA+ END Facebook meta tags -->\n");
	}
}

/* LOAD SLIDESHOW, THEME, AJAX and LIGHTBOX js, all in one file nowadays */
add_action('init', 'wppa_add_javascripts');
	
function wppa_add_javascripts() {
global $wppa_api_version;

	if ( is_file(WPPA_PATH.'/wppa.min.js') ) {
		wp_enqueue_script('wppa', WPPA_URL.'/wppa.min.js', array('jquery'), $wppa_api_version);
	}
	else {
		wp_enqueue_script('wppa', WPPA_URL.'/wppa.js', array('jquery'), $wppa_api_version);
	}
	if ( get_option('wppa_gpx_implementation', 'nil') == 'wppa-plus-embedded' ) {
		if ( get_option('wppa_map_apikey', false) ) {
			wp_enqueue_script('wppa-geo', 'https://maps.googleapis.com/maps/api/js?key='.get_option('wppa_map_apikey').'&sensor=false', '', $wppa_api_version);
		}
		else {
			wp_enqueue_script('wppa-geo', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', '', $wppa_api_version);
		}
	}
//	if ( true ) {
//		wp_enqueue_script('wppa-fotomoto', 'http://widget.fotomoto.com/stores/script/be53373cd51248dad2c101b4dbbc8cec228491c2.js', '', $wppa_api_version);
//	}
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
global $wppa;
global $wpdb;

	if ($wppa_opt['wppa_lightbox_name'] == 'wppa') {
		if ( ! $wppa_opt['wppa_fontsize_lightbox'] ) $wppa_opt['wppa_fontsize_lightbox'] = '10';
		$d = $wppa_opt['wppa_ovl_show_counter'] ? 1 : 0;
		$ovlh = $wppa_opt['wppa_ovl_txt_lines'] == 'auto' ? 'auto' : (($wppa_opt['wppa_ovl_txt_lines'] + $d) * ($wppa_opt['wppa_fontsize_lightbox'] + 2));
		echo '
<!-- start WPPA+ Footer data -->
	<div id="wppa-overlay-bg" style="text-align:center; display:none; position:fixed; top:0; left:0; z-index:100090; width:100%; height:2048px; background-color:black;" onclick="wppaOvlOnclick(event)" ></div>
	<div id="wppa-overlay-ic" style="position:fixed; top:0; padding-top:10px; z-index:100095; opacity:1; box-shadow:none;"
		ontouchstart="wppaTouchStart(event, \'wppa-overlay-ic\', -1);"  ontouchend="wppaTouchEnd(event);" 
		ontouchmove="wppaTouchMove(event);" ontouchcancel="wppaTouchCancel(event);" >
	</div>
	<img id="wppa-overlay-sp" style="position:fixed; top:200px; left:200px; z-index:100100; opacity:1; visibility:hidden; box-shadow:none;" src="'.wppa_get_imgdir().'loading.gif" />
	<script type="text/javascript">jQuery("#wppa-overlay-bg").css({height:screen.height+"px"});
		wppaOvlTxtHeight = "'.$ovlh.'";
		wppaOvlCloseTxt = "'.__($wppa_opt['wppa_ovl_close_txt']).'";
		wppaOvlOpacity = '.($wppa_opt['wppa_ovl_opacity']/100).';
		wppaOvlOnclickType = "'.$wppa_opt['wppa_ovl_onclick'].'";
		wppaOvlTheme = "'.$wppa_opt['wppa_ovl_theme'].'";
		wppaOvlAnimSpeed = '.$wppa_opt['wppa_ovl_anim'].';
		wppaVer4WindowWidth = 800;
		wppaVer4WindowHeight = 600;
		wppaOvlShowCounter = '.( $wppa_opt['wppa_ovl_show_counter'] ? 'true' : 'false' ).';
		'.( $wppa_opt['wppa_fontfamily_lightbox'] ? 'wppaOvlFontFamily = "'.$wppa_opt['wppa_fontfamily_lightbox'].'"' : '').'
		'.( $wppa_opt['wppa_fontsize_lightbox'] ? 'wppaOvlFontSize = "'.$wppa_opt['wppa_fontsize_lightbox'].'"' : '').'
		'.( $wppa_opt['wppa_fontcolor_lightbox'] ? 'wppaOvlFontColor = "'.$wppa_opt['wppa_fontcolor_lightbox'].'"' : '').'
		'.( $wppa_opt['wppa_fontweight_lightbox'] ? 'wppaOvlFontWeight = "'.$wppa_opt['wppa_fontweight_lightbox'].'"' : '').'
		'.( $wppa_opt['wppa_fontsize_lightbox'] ? 'wppaOvlLineHeight = "'.($wppa_opt['wppa_fontsize_lightbox'] + '2').'"' : '').'
	</script>
	';
	wp_nonce_field('wppa-check' , 'wppa-nonce', false, true);	// Nonce field for Ajax bump view counter from lightbox
	echo '
<!-- end WPPA+ Footer data -->
';
	}
	
	wppa_dbg_q('print');
	if ( $wppa['debug'] ) {
		$plugins = get_option('active_plugins');
		wppa_dbg_msg('Active Plugins');
		foreach ( $plugins as $plugin ) {
			wppa_dbg_msg($plugin);
		}
		wppa_dbg_msg('End Active Plugins');
//		wppa_dbg_msg(htmlspecialchars($wpdb->get_var("SELECT `option_value` FROM ".$wpdb->prefix . 'options'." WHERE `option_name` = 'wppa_cached_options'")));
	}
	
	echo '
<!-- Do user upload -->';
	wppa_user_upload();	// Do the upload if required and not yet done
	echo '
<!-- Done user upload -->';
}

/* FACEBOOK COMMENTS */
function wppa_fbc_setup() {
	$wppa_app_id = '';
	$wppa_lang = get_locale();
	if ( ! $wppa_lang ) $wppa_lang = 'en_US';
	?>
<!-- Facebook Comments for WPPA+ -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/<?php echo $wppa_lang; ?>/all.js#xfbml=1&appId=<?php echo $wppa_app_id; ?>";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>
<?php 
}
if ( ( get_option('wppa_facebook_like') == 'yes' || get_option('wppa_facebook_comments') == 'yes' ) 
	&& get_option('wppa_share_on') == 'yes' 
	&& get_option('wppa_load_facebook_sdk') == 'yes' ) {
		add_action('wp_footer', 'wppa_fbc_setup', 100);
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
		// Although the searchstring is urlencoded it is damaged by wp_redirect when it contains chars like ë, so in that case we do a header() call
		if ( strpos($newuri, '&wppa-searchstring=') || strpos($newuri, '&s=') ) header('Location: '.$newuri, true, 302);
		else wp_redirect($newuri);
		exit;
	}
}

/* LOAD JS VARS AND ENABLE RENDERING */
add_action('wp_head', 'wppa_kickoff', '100');

function wppa_kickoff() {
global $wppa;
global $wppa_opt;
global $wppa_lang;
global $wppa_api_version;

	switch ($wppa_opt['wppa_slideshow_linktype']) {
		case 'file':
			$lbkey = 'file'; //echo("\t".'wppaLightBox = "file";'."\n");	// gives anchor tag with rel="file"
			break;
		case 'lightbox':
		case 'lightboxsingle':
			$lbkey = $wppa_opt['wppa_lightbox_name']; //echo("\t".'wppaLightBox = "'.$wppa_opt['wppa_lightbox_name'].'";'."\n");	// gives anchor tag with rel="lightbox" or the like
			break;
		default:
			$lbkey = ''; //echo("\t".'wppaLightBox = "";'."\n");		// results in omitting the anchor tag
			break;
	}
	if ( is_numeric($wppa_opt['wppa_fullimage_border_width']) ) $fbw = $wppa_opt['wppa_fullimage_border_width'] + '1'; else $fbw = '0';
		
	echo '
<!-- WPPA+ Runtime parameters -->
	<script type="text/javascript">
	/* <![CDATA[ */
';
	if ( WPPA_DEBUG || isset($_GET['wppa-debug']) || WP_DEBUG ) {
	echo '
	/* Check if wppa.js and jQuery are present */
	if (typeof(_wppaSlides) == \'undefined\') alert(\'There is a problem with your theme. The file wppa.js is not loaded when it is expected (Errloc = wppa_kickoff).\');
	if (typeof(jQuery) == \'undefined\') alert(\'There is a problem with your theme. The jQuery library is not loaded when it is expected (Errloc = wppa_kickoff).\');
';	}
	/* This goes into wppa.js */ 
	echo '
	wppaDebug = '.( $wppa['debug'] ? 'true' : 'false' ).';
	wppaVersion = "'.$wppa_api_version.'";
	wppaBackgroundColorImage = "'.$wppa_opt['wppa_bgcolor_img'].'";
	wppaPopupLinkType = "'.$wppa_opt['wppa_thumb_linktype'].'";
	wppaAnimationType = "'.$wppa_opt['wppa_animation_type'].'";
	wppaAnimationSpeed = '.$wppa_opt['wppa_animation_speed'].';
	wppaImageDirectory = "'.wppa_get_imgdir().'";
	wppaThumbnailAreaDelta = '.wppa_get_thumbnail_area_delta().';
	wppaTextFrameDelta = '.wppa_get_textframe_delta().';
	wppaBoxDelta = '.wppa_get_box_delta().';
	wppaSlideShowTimeOut = '.$wppa_opt['wppa_slideshow_timeout'].';
	wppaPreambule = '.wppa_get_preambule().';
	wppaFilmShowGlue = '.( $wppa_opt['wppa_film_show_glue'] == 'yes' ? 'true' : 'false' ).';
	wppaSlideShow = "'.__a('Slideshow').'";
	wppaStart = "'.__a('Start').'";
	wppaStop = "'.__a('Stop').'";
	wppaSlower = "'.__a('Slower').'";
	wppaFaster = "'.__a('Faster').'";
	wppaPhoto = "'.__a('Photo').'";
	wppaOf = "'.__a('of').'";
	wppaPreviousPhoto = "&laquo;&nbsp;'.__a('Previous photo').'";
	wppaNextPhoto = "'.__a('Next photo').'&nbsp;&raquo;";
	wppaPrevP = "'.__a('Prev.').'";
	wppaNextP = "'.__a('Next').'";
	wppaAvgRating = "'.__a('Average&nbsp;rating').'";
	wppaMyRating = "'.__a('My&nbsp;rating').'";
	wppaAvgRat = "'.__a('Avg.').'";
	wppaMyRat = "'.__a('Mine').'";
	wppaDislikeMsg = "'.__a('You marked this image as inappropriate.').'";
	wppaShowDislikeCount = '.( $wppa_opt['wppa_dislike_show_count'] ? 'true' : 'false' ).';
	wppaNoDislikes = "'.__a('No dislikes').'";
	wppa1Dislike = "'.__a('1 dislike').'";
	wppaDislikes = "'.__a('dislikes').'";
	wppaIncludingMine = "'.__a('including mine').'";
	wppaMiniTreshold = '.$wppa_opt['wppa_mini_treshold'].';
	wppaUserName = "'.wppa_get_user().'";
	wppaRatingOnce = '.( $wppa_opt['wppa_rating_change'] || $wppa_opt['wppa_rating_multi'] ? 'false' : 'true' ).';
	wppaPleaseName = "'.__a('Please enter your name').'";
	wppaPleaseEmail = "'.__a('Please enter a valid email address').'";
	wppaPleaseComment = "'.__a('Please enter a comment').'";
	wppaHideWhenEmpty = '.( $wppa_opt['wppa_hide_when_empty'] ? 'true' : 'false' ).';
	wppaBGcolorNumbar = "'.$wppa_opt['wppa_bgcolor_numbar'].'";
	wppaBcolorNumbar = "'.$wppa_opt['wppa_bcolor_numbar'].'";
	wppaBGcolorNumbarActive = "'.$wppa_opt['wppa_bgcolor_numbar_active'].'";
	wppaBcolorNumbarActive = "'.$wppa_opt['wppa_bcolor_numbar_active'].'";
	wppaFontFamilyNumbar = "'.$wppa_opt['wppa_fontfamily_numbar'].'";
	wppaFontSizeNumbar = "'.$wppa_opt['wppa_fontsize_numbar'].'px";
	wppaFontColorNumbar = "'.$wppa_opt['wppa_fontcolor_numbar'].'";
	wppaFontWeightNumbar = "'.$wppa_opt['wppa_fontweight_numbar'].'";
	wppaFontFamilyNumbarActive = "'.$wppa_opt['wppa_fontfamily_numbar_active'].'";
	wppaFontSizeNumbarActive = "'.$wppa_opt['wppa_fontsize_numbar_active'].'px";
	wppaFontColorNumbarActive = "'.$wppa_opt['wppa_fontcolor_numbar_active'].'";
	wppaFontWeightNumbarActive = "'.$wppa_opt['wppa_fontweight_numbar_active'].'";
	wppaNumbarMax = "'.$wppa_opt['wppa_numbar_max'].'";
	wppaLang = "'.$wppa_lang.'";
	wppaAjaxUrl = "'.( $wppa_opt['wppa_ajax_non_admin'] ? WPPA_URL.'/wppa-ajax-front.php' : admin_url('admin-ajax.php') ).'";
	wppaSiteUrl = "'.site_url().'";
	wppaNextOnCallback = '.( $wppa_opt['wppa_next_on_callback'] ? 'true' : 'false' ).';
	wppaRatingUseAjax = true;
	wppaStarOpacity = '.( $wppa_opt['wppa_star_opacity']/'100' ).';
	wppaTickImg.src = "'.wppa_get_imgdir().'tick.png";
	wppaClockImg.src = "'.wppa_get_imgdir().'clock.png";
	wppaSlideWrap = '.( $wppa_opt['wppa_slide_wrap'] ? 'true' : 'false' ).';
	wppaLightBox = "'.$lbkey.'";
	wppaEmailRequired = '.( $wppa_opt['wppa_comment_email_required'] ? 'true' : 'false' ).';
	wppaSlideBorderWidth = '.$fbw.';
	wppaAllowAjax = '.( $wppa_opt['wppa_allow_ajax'] ? 'true' : 'false' ).';
	wppaUsePhotoNamesInUrls = '.( $wppa_opt['wppa_use_photo_names_in_urls'] ? 'true' : 'false' ).';
	wppaThumbTargetBlank = '.( $wppa_opt['wppa_thumb_blank'] ? 'true' : 'false' ).';
	wppaRatingMax = '.$wppa_opt['wppa_rating_max'].';
	wppaRatingDisplayType = "'.$wppa_opt['wppa_rating_display_type'].'";
	wppaRatingPrec = '.$wppa_opt['wppa_rating_prec'].';
	wppaStretch = '.( $wppa_opt['wppa_enlarge'] ? 'true' : 'false' ).';
	wppaMinThumbSpace = '.$wppa_opt['wppa_tn_margin'].';
	wppaThumbSpaceAuto = '.( $wppa_opt['wppa_thumb_auto'] ? 'true' : 'false' ).';
	wppaMagnifierCursor = "'.$wppa_opt['wppa_magnifier'].'";
	wppaArtMonkyLink = "'.$wppa_opt['wppa_art_monkey_link'].'";
	wppaAutoOpenComments = '.( $wppa_opt['wppa_auto_open_comments'] ? 'true' : 'false' ).';
	wppaUpdateAddressLine = '.( $wppa_opt['wppa_update_addressline'] ? 'true' : 'false' ).';
	wppaUploadUrl = "'.WPPA_UPLOAD_URL.'";
	wppaFilmThumbTitle = "'.( $wppa_opt['wppa_film_linktype'] == 'lightbox' ? wppa_zoom_in() : __a('Double click to start/stop slideshow running') ).'";
	wppaVoteForMe = "'.__($wppa_opt['wppa_vote_button_text']).'";
	wppaVotedForMe = "'.__($wppa_opt['wppa_voted_button_text']).'";
	wppaSlideSwipe = '.( wppa_switch('wppa_slide_swipe') ? 'true' : 'false' ).';
	wppaMaxCoverWidth = '.$wppa_opt['wppa_max_cover_width'].';
	wppaLightboxSingle = '.( $wppa_opt['wppa_slideshow_linktype'] == 'lightboxsingle' ? 'true': 'false' ).';
	wppaDownLoad = "'.__a('Download').'";
	wppaSlideToFullpopup = '.( $wppa_opt['wppa_slideshow_linktype'] == 'fullpopup' ? 'true' : 'false' ).'; 
	wppaComAltSize = '.$wppa_opt['wppa_comten_alt_thumbsize'].';
	wppaBumpViewCount = '.( wppa_switch('wppa_track_viewcounts') ? 'true' : 'false' ).';
	wppaShareHideWhenRunning = '.( wppa_switch('wppa_share_hide_when_running') ? 'true' : 'false' ).';
	wppaFotomoto = '.( wppa_switch('wppa_fotomoto_on') ? 'true' : 'false' ).';
	wppaArtMonkeyButton = '.( $wppa_opt['wppa_art_monkey_display'] == 'button' ? 'true' : 'false' ).';
	/* ]]> */
</script>
';

	// Patch for chrome?
	if ( isset($_SERVER["HTTP_USER_AGENT"]) && isset($_SERVER["HTTP_USER_AGENT"]) ) {
		echo '
		<!-- Browser detected = '.$_SERVER["HTTP_USER_AGENT"].' -->';
		if ( strstr($_SERVER["HTTP_USER_AGENT"], 'Chrome') && wppa_switch('wppa_ovl_chrome_at_top') ) echo '
		<style type="text/css">
			#wppa-overlay-ic { padding-top: 5px !important; } 
			#wppa-overlay-qt-txt, #wppa-overlay-qt-img { top: 5px !important; }
		</style>';
	}

	// Pinterest js
	if ( ( $wppa_opt['wppa_share_on'] || $wppa_opt['wppa_share_on_widget'] ) && $wppa_opt['wppa_share_pinterest'] ) {
		echo '
		<!-- WPPA+ Pinterest share -->
			<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>
		<!-- end WPPA+ Pinterest share -->
		';
	}

	$wppa['rendering_enabled'] = true;
	echo '
	<!-- WPPA+ Rendering enabled -->
	';
	if ($wppa['debug']) {
		error_reporting($wppa['debug']);
		add_action('wp_footer', 'wppa_phpinfo');
	}
}

/* SKIP JETPACK FOTON ON WPPA+ IMAGES */
add_filter('jetpack_photon_skip_image', 'wppa_skip_photon', 10, 3);
function wppa_skip_photon($val, $src, $tag) {
	$result = $val;
	if ( strpos($src, WPPA_UPLOAD_URL) !== false ) $result = true;
	return $result;
}
