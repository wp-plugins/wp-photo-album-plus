<?php 
/* wppa-non-admin.php
* Package: wp-photo-album-plus
*
* Contains all the non admin stuff
* Version 4.0.7
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

/* LOAD SLIDESHOW and THEME JS */
add_action('init', 'wppa_add_javascripts');
	
function wppa_add_javascripts() {
	wp_register_script('wppa_slideshow', WPPA_URL.'/wppa-slideshow.js');
	wp_register_script('wppa_theme_js', WPPA_URL.'/wppa-theme.js');
	wp_enqueue_script('jquery');
	wp_enqueue_script('wppa_slideshow');
	wp_enqueue_script('wppa_theme_js');
	if ( get_option('wppa_use_lightbox', 'yes') == 'yes' ) {
		wp_enqueue_script('prototype');
		wp_enqueue_script('scriptaculous-effects');
		wp_enqueue_script('scriptaculous-builder');
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
	
/* LOAD LIGHTBOX */
add_action('wp_head', 'wppa_lightbox', '99');

function wppa_lightbox() {
	if ( get_option('wppa_use_lightbox', 'yes') == 'yes' ) {
		echo "\n<!-- Start WPPA+ inserted lightbox -->\n";
	//	echo "\n".'<script type="text/javascript" src="'.WPPA_URL.'/lightbox/js/prototype.js"></script>';
	//	echo "\n".'<script type="text/javascript" src="'.WPPA_URL.'/lightbox/js/scriptaculous.js?load=effects,builder"></script>';
		echo "\n".'<script type="text/javascript"><!--//--><![CDATA[//><!--';
			echo "\n".'LightboxOptions = Object.extend({';
			echo "\n"."fileLoadingImage:        'wp-content/plugins/wp-photo-album-plus/lightbox/images/loading.gif',   ";  
			echo "\n"."fileBottomNavCloseImage: 'wp-content/plugins/wp-photo-album-plus/lightbox/images/closelabel.gif',";

			echo "\n".'overlayOpacity: 0.8,   // controls transparency of shadow overlay';

			echo "\n".'animate: true,         // toggles resizing animations';
			echo "\n".'resizeSpeed: 7,        // controls the speed of the image resizing animations (1=slowest and 10=fastest)';

			echo "\n".'borderSize: 10,         //if you adjust the padding in the CSS, you will need to update this variable';

			echo "\n".'// When grouping images this is used to write: Image # of #.';
			echo "\n".'// Change it for non-english localization';
			echo "\n".'labelImage: "'.__a('Image', 'wppa_theme').'",';
			echo "\n".'labelOf: "'.__a('of', 'wppa_theme').'"';
			echo "\n".'}, window.LightboxOptions || {});';
		echo "\n".'//--><!]]></script>';
		echo "\n".'<script type="text/javascript" src="'.WPPA_URL.'/lightbox/js/lightbox.js"></script>';
		echo "\n".'<link rel="stylesheet" href="'.WPPA_URL.'/lightbox/css/lightbox.css" type="text/css" media="screen" />';
		echo "\n<!-- End WPPA+ inserted lightbox -->\n";		
	}
}
/* LOAD JS VARS AND ENABLE RENDERING */
add_action('wp_head', 'wppa_kickoff', '100');

function wppa_kickoff() {
global $wppa;
global $wppa_opt;

	echo("\n<!-- WPPA+ Runtime parameters -->\n");
	
	echo('<script type="text/javascript"><!--//--><![CDATA[//><!--'."\n");
	
		/* This goes into wppa_theme.js */ 
		echo("\t".'wppaBackgroundColorImage = "'.$wppa_opt['wppa_bgcolor_img'].'";'."\n");
		echo("\t".'wppaPopupLinkType = "'.$wppa_opt['wppa_thumb_linktype'].'";'."\n"); 
		//echo("\t".'wppa_popup_size = "'.$wppa_opt['wppa_popupsize'].'";'."\n");

		/* This goes into wppa_slideshow.js */
		if ($wppa_opt['wppa_fadein_after_fadeout']) echo("\t".'wppaFadeInAfterFadeOut = true;'."\n");
		else echo("\t".'wppaFadeInAfterFadeOut = false;'."\n");
		echo("\t".'wppaAnimationSpeed = '.$wppa_opt['wppa_animation_speed'].';'."\n");
		echo("\t".'wppaImageDirectory = "'.wppa_get_imgdir().'";'."\n");
		if ($wppa['auto_colwidth']) echo("\t".'wppaAutoCoumnWidth = true;'."\n");
		else echo("\t".'wppaAutoCoumnWidth = false;'."\n");
		echo("\t".'wppaThumbnailAreaDelta = '.wppa_get_thumbnail_area_delta().';'."\n");
		echo("\t".'wppaTextFrameDelta = '.wppa_get_textframe_delta().';'."\n");
		echo("\t".'wppaBoxDelta = '.wppa_get_box_delta().';'."\n");
		echo("\t".'wppaSlideShowTimeOut = '.$wppa_opt['wppa_slideshow_timeout'].';'."\n");		
		echo("\t".'wppaPreambule = '.wppa_get_preambule().';'."\n");
		if ($wppa_opt['wppa_film_show_glue'] == 'yes') echo("\t".'wppaFilmShowGlue = true;'."\n");
		else echo("\t".'wppaFilmShowGlue = false;'."\n");
		echo("\t".'wppaSlideShow = "'.__a('Slideshow', 'wppa_theme').'";'."\n");
		echo("\t".'wppaStart = "'.__a('Start', 'wppa_theme').'";'."\n");
		echo("\t".'wppaStop = "'.__a('Stop', 'wppa_theme').'";'."\n");
		echo("\t".'wppaPhoto = "'.__a('Photo', 'wppa_theme').'";'."\n");
		echo("\t".'wppaOf = "'.__a('of', 'wppa_theme').'";'."\n");
		echo("\t".'wppaPreviousPhoto = "'.__a('Previous photo', 'wppa_theme').'";'."\n");
		echo("\t".'wppaNextPhoto = "'.__a('Next photo', 'wppa_theme').'";'."\n");
		echo("\t".'wppaPrevP = "'.__a('Prev.', 'wppa_theme').'";'."\n");
		echo("\t".'wppaNextP = "'.__a('Next', 'wppa_theme').'";'."\n");
		echo("\t".'wppaUserName = "'.wppa_get_user().'";'."\n");
		if ($wppa_opt['wppa_rating_change'] || $wppa_opt['wppa_rating_multi']) echo("\t".'wppaRatingOnce = false;'."\n");
		else echo("\t".'wppaRatingOnce = true;'."\n");
		echo("\t".'wppaPleaseName = "'.__a('Please enter your name', 'wppa_theme').'";'."\n");
		echo("\t".'wppaPleaseEmail = "'.__a('Please enter a valid email address', 'wppa_theme').'";'."\n");
		echo("\t".'wppaPleaseComment = "'.__a('Please enter a comment', 'wppa_theme').'";'."\n");

	echo("//--><!]]></script>\n");
	
	$wppa['rendering_enabled'] = true;
	echo("\n<!-- WPPA+ Rendering enabled -->\n");
	if ($wppa['debug']) {
		error_reporting($wppa['debug']);
		add_action('wp_footer', 'wppa_phpinfo');
	}
}
