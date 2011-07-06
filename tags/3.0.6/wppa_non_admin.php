<?php 
/* wppa_non_admin.php
* Package: wp-photo-album-plus
*
* Contains all the non admin stuff
* Version 3.0.6
*
* dbg
*/

/* API FILTER and FUNCTIONS */
require_once('wppa_filter.php');
require_once('wppa_functions.php');
	
/* LOAD STYLESHEET */
add_action('wp_print_styles', 'wppa_add_style');

function wppa_add_style() {
	$userstyle = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa_style.css';
	if (is_file($userstyle)) {
		wp_register_style('wppa_style', '/wp-content/themes/' . get_option('template')  . '/wppa_style.css');
		wp_enqueue_style('wppa_style');
	} else {
		wp_register_style('wppa_style', WPPA_URL.'/theme/wppa_style.css');
		wp_enqueue_style('wppa_style');
	}
}

/* LOAD SLIDESHOW and THEME JS */
add_action('init', 'wppa_add_javascripts');
	
function wppa_add_javascripts() {
	wp_register_script('wppa_slideshow', WPPA_URL.'/theme/wppa_slideshow.js');
	wp_register_script('wppa_theme_js', WPPA_URL.'/theme/wppa_theme.js');
	wp_enqueue_script('jquery');
	wp_enqueue_script('wppa_slideshow');
	wp_enqueue_script('wppa_theme_js');
}
	
/* LOAD WPPA+ THEME */
add_action('init', 'wppa_load_theme');
	
function wppa_load_theme() {
	$templatefile = ABSPATH.'wp-content/themes/'.get_option('template').'/wppa_theme.php';
	if (is_file($templatefile)) {
		require_once($templatefile);
	} else {
		require_once('theme/wppa_theme.php');
	}
}
	
/* LOAD JS VARS AND ENABLE RENDERING */
add_action('wp_head', 'wppa_kickoff', '100');

function wppa_kickoff() {
global $wppa;
global $wppa_opt;

	echo("\n<!-- WPPA+ Runtime parameters -->\n");
	
	/* This goes into wppa_theme.js */ 
	echo('<script type="text/javascript">'."\n");
	
		echo("\t".'wppa_bgcolor_img = "'.$wppa_opt['wppa_bgcolor_img'].'";'."\n");
		if ($wppa_opt['wppa_thumb_linktype'] == 'none') echo("\twppa_popup_nolink = true;\n"); 
		else echo("\twppa_popup_nolink = false;\n");

		/* This goes into wppa_slideshow.js */
		if ($wppa_opt['wppa_fadein_after_fadeout']) echo("\t".'wppa_fadein_after_fadeout = true;'."\n");
		else echo("\t".'wppa_fadein_after_fadeout = false;'."\n");
		echo("\t".'wppa_animation_speed = '.$wppa_opt['wppa_animation_speed'].';'."\n");
		echo("\t".'wppa_imgdir = "'.wppa_get_imgdir().'";'."\n");
		if ($wppa['auto_colwidth']) echo("\t".'wppa_auto_colwidth = true;'."\n");
		else echo("\t".'wppa_auto_colwidth = false;'."\n");
		echo("\t".'wppa_thumbnail_area_delta = '.wppa_get_thumbnail_area_delta().';'."\n");
		echo("\t".'wppa_textframe_delta = '.wppa_get_textframe_delta().';'."\n");
		echo("\t".'wppa_box_delta = '.wppa_get_box_delta().';'."\n");
		echo("\t".'wppa_ss_timeout = '.$wppa_opt['wppa_slideshow_timeout'].';'."\n");		
		echo("\t".'wppa_preambule = '.wppa_get_preambule().';'."\n");
/*
		$temp = $wppa_opt['wppa_tf_width'] + $wppa_opt['wppa_tn_margin'];
		echo("\t".'wppa_thumbnail_pitch = '.$temp.';'."\n");
		$temp = $wppa_opt['wppa_tn_margin'] / 2;
		echo("\t".'wppa_filmstrip_margin = '.$temp.';'."\n");
		$temp = 2*6 + 2*23 + 2*$wppa_opt['wppa_bwidth'];
		echo("\t".'wppa_filmstrip_area_delta = '.$temp.';'."\n");
*/	
		if ($wppa_opt['wppa_film_show_glue'] == 'yes') echo("\t".'wppa_film_show_glue = true;'."\n");
		else echo("\t".'wppa_film_show_glue = false;'."\n");
		echo("\t".'wppa_slideshow = "'.__a('Slideshow', 'wppa_theme').'";'."\n");
		echo("\t".'wppa_start = "'.__a('Start', 'wppa_theme').'";'."\n");
		echo("\t".'wppa_stop = "'.__a('Stop', 'wppa_theme').'";'."\n");
		echo("\t".'wppa_photo = "'.__a('Photo', 'wppa_theme').'";'."\n");
		echo("\t".'wppa_of = "'.__a('of', 'wppa_theme').'";'."\n");
		echo("\t".'wppa_prevphoto = "'.__a('Prev.&nbsp;photo', 'wppa_theme').'";'."\n");
		echo("\t".'wppa_nextphoto = "'.__a('Next&nbsp;photo', 'wppa_theme').'";'."\n");
		echo("\t".'wppa_prevp = "'.__a('Prev.', 'wppa_theme').'";'."\n");
		echo("\t".'wppa_nextp = "'.__a('Next', 'wppa_theme').'";'."\n");
		echo("\t".'wppa_username = "'.wppa_get_user().'";'."\n");
		if ($wppa_opt['wppa_rating_change'] || $wppa_opt['wppa_rating_multi']) echo("\t".'wppa_rating_once = false;'."\n");
		else echo("\t".'wppa_rating_once = true;'."\n");

	echo("</script>\n");
	
	$wppa['rendering_enabled'] = true;
	echo("\n<!-- WPPA+ Rendering enabled -->\n");
	if ($wppa['debug']) {
		error_reporting($wppa['debug']);
		add_action('wp_footer', 'wppa_phpinfo');
	}
}
