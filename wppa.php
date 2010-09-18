<?php
/*
Plugin Name: WP Photo Album Plus
Description: Easily manage and display your photo albums and slideshows within your WordPress site.
Version: 2.1.0
Author: J.N. Breetvelt a.k.a OpaJaap
Author URI: http://www.opajaap.nl/
Plugin URI: http://wordpress.org/extend/plugins/wp-photo-album-plus/
*/

load_plugin_textdomain('wppa', 'wp-content/plugins/wp-photo-album-plus/langs/', 'wp-photo-album-plus/langs/');

/* GLOBAL SETTINGS */
global $wpdb;
global $wp_roles;
global $wppa_occur;
global $wppa_master_occur;

define('ALBUM_TABLE', $wpdb->prefix . 'wppa_albums');
define('PHOTO_TABLE', $wpdb->prefix . 'wppa_photos');
define('WPPA_PLUGIN_PATH', 'wp-photo-album-plus');

$wppa_occur = 0;
$wppa_master_occur = 0;

/* FORM SECURITY */
global $wppa_no_nonce;
$wppa_no_nonce = false;
$path = ABSPATH . 'wp-content/themes/' . get_option('template')  . '/wppa_no_nonce.txt';
if (file_exists($path) || !function_exists('wp_nonce_field') ) {
        function wppa_nonce_field($action = -1) { return; }
        $wppa_nonce = -1;
		$wppa_no_nonce = true;
} else {
		function wppa_nonce_field($action = -1,$name = 'wppa-update-check') { return wp_nonce_field($action,$name); }
		define('WPPA_NONCE' , 'wppa-update-check');
}
function wppa_check_admin_referer($arg1, $arg2) {
global $wppa_no_nonce;
	if ($wppa_no_nonce) {
		if (is_admin()) return;
		die('You must be on an admin page to do this');
	}
	else {
		check_admin_referer($arg1, $arg2);
	}
}

/* SETUP */
// calls the setup function on activation
register_activation_hook( __FILE__, 'wppa_setup' );

// does the initial setup
function wppa_setup() {
	global $wpdb;
	
	$old_rev = get_option('wppa_revision', '100');
	if ($old_rev <= '201') {
		
	$create_albums = "CREATE TABLE " . ALBUM_TABLE . " (
                    id bigint(20) NOT NULL auto_increment, 
                    name text NOT NULL, 
                    description text NOT NULL, 
                    a_order smallint(5) unsigned NOT NULL, 
                    main_photo bigint(20) NOT NULL, 
                    a_parent bigint(20) NOT NULL,
                    p_order_by int unsigned NOT NULL,
					cover_linkpage bigint(20) NOT NULL,
                    PRIMARY KEY  (id) 
                    );"; // ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='WP Photo Album Plus';";
                    
	$create_photos = "CREATE TABLE " . PHOTO_TABLE . " (
                    id bigint(20) NOT NULL auto_increment, 
                    album bigint(20) NOT NULL, 
                    ext tinytext NOT NULL, 
                    name text NOT NULL, 
                    description longtext NOT NULL, 
                    p_order smallint(5) unsigned NOT NULL,
                    PRIMARY KEY  (id) 
                    );"; // ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='WP Photo Album Plus';";

    require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

    dbDelta($create_albums);
    dbDelta($create_photos);
		
	delete_option('wppa-accesslevel');	/* pre rev 2 version */
	delete_option('wppa_accesslevel');	/* reset at activation */
	
	if (!is_numeric(get_option('wppa_fullsize', 'nil'))) update_option('wppa_fullsize', '640');
	if (get_option('wppa_enlarge', 'nil') == 'nil') update_option('wppa_enlarge', 'yes');
	if (get_option('wppa_fullvalign', 'nil') == 'nil') update_option('wppa_fullvalign', 'default');
	if (!is_numeric(get_option('wppa_min_thumbs', 'nil'))) update_option('wppa_min_thumbs', '1');
	if (get_option('wppa_valign', 'nil') == 'nil') update_option('wppa_valign', 'default');
	if (!is_numeric(get_option('wppa_thumbsize', 'nil'))) update_option('wppa_thumbsize', '150');
	if (!is_numeric(get_option('wppa_smallsize', 'nil'))) update_option('wppa_smallsize', '100');
	if (get_option('wppa_show_bread', 'nil') == 'nil') update_option('wppa_show_bread', 'yes');
	if (get_option('wppa_use_thumb_opacity', 'nil') == 'nil') update_option('wppa_use_thumb_opacity', 'yes');
	if (!is_numeric(get_option('wppa_thumb_opacity', 'nil'))) update_option('wppa_thumb_opacity', '80');
	if (get_option('wppa_use_thumb_popup', 'nil') == 'nil') update_option('wppa_use_thumb_popup', 'yes');
	if (get_option('wppa_use_cover_opacity', 'nil') == 'nil') update_option('wppa_use_cover_opacity', 'no');
	if (!is_numeric(get_option('wppa_cover_opacity', 'nil'))) update_option('wppa_cover_opacity', '85');
	if (!is_numeric(get_option('wppa_animation_speed', 'nil'))) update_option('wppa_animation_speed', '600');
	
	if ($old_rev < '200') {
		$key = '0';
		$userstyle = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa_style.css';
		$usertheme = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa_theme.php';
		if (is_file($userstyle)) $key += '1';
		if (is_file($usertheme)) $key += '2';
		update_option('wppa_update_key', $key);
		}
	
	update_option('wppa_revision', '201');
	}
}
	
/* LOAD SIDEBAR WIDGET */
require_once('wppa_widget.php');

/* ADMIN MENU */
add_action('admin_menu', 'wppa_add_admin');

function wppa_add_admin() {
	global $wp_roles;

	if (current_user_can('administrator')) {	// Make sure admin has access rights
		$wp_roles->add_cap('administrator', 'wppa_admin');
	}

	$iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/camera16.png';
	add_menu_page('WP Photo Album', __('Photo Albums', 'wppa'), 'wppa_admin', __FILE__, 'wppa_admin', $iconurl);
	
    add_submenu_page(__FILE__, __('Upload Photos', 'wppa'), __('Upload Photos', 'wppa'), 'wppa_admin', 'upload_photos', 'wppa_page_upload');
    add_submenu_page(__FILE__, __('Settings', 'wppa'), __('Settings', 'wppa'), 'wppa_admin', 'options', 'wppa_page_options');
	add_submenu_page(__FILE__, __('Sidebar Widget', 'wppa'), __('Sidebar Widget', 'wppa'), 'wppa_admin', 'wppa_sidebar_options', 'wppa_sidebar_page_options');
    add_submenu_page(__FILE__, __('Help &amp; Info', 'wppa'), __('Help &amp; Info', 'wppa'), 'wppa_admin', 'wppa_help', 'wppa_page_help');
}

/* ADMIN PAGES */
if (is_admin()) require_once('wppa_admin.php');

/* API FILTER */
if (!is_admin()) require_once('wppa_filter.php');

/* LOAD STYLESHEET */
if (!is_admin()) add_action('wp_print_styles', 'wppa_add_style');

function wppa_add_style() {
	$userstyle = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa_style.css';
	if (is_file($userstyle)) {
		wp_register_style('wppa_style', '/wp-content/themes/' . get_option('template')  . '/wppa_style.css');
		wp_enqueue_style('wppa_style');
	} else {
		wp_register_style('wppa_style', '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/theme/wppa_style.css');
		wp_enqueue_style('wppa_style');
	}
}

/* LOAD SLIDESHOW and DYNAMIC STYLES */
if (!is_admin()) add_action('init', 'wppa_add_javascripts');

function wppa_add_javascripts() {
	wp_register_script('wppa_slideshow', '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/theme/wppa_slideshow.js');
	wp_register_script('wppa_theme_js', '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/theme/wppa_theme.js');
	wp_enqueue_script('jquery');
	wp_enqueue_script('wppa_slideshow');
	wp_enqueue_script('wppa_theme_js');
}

/* LISTING FUNCTIONS */
// get the albums
function wppa_albums($xalb = '', $type='', $siz = '') {
	global $wpdb;
    global $startalbum;
	global $wppa_occur;
	global $wppa_master_occur;
	global $is_cover;
	global $wppa_fullsize;
    
    if (is_numeric($xalb)) $startalbum = $xalb;
	$wppa_occur++;
	$wppa_master_occur++;
	if ($type == 'album') $is_cover = '0';
	elseif ($type == 'cover') $is_cover = '1';
	if (is_numeric($siz)) $wppa_fullsize = $siz;
    
	$templatefile = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa_theme.php';
	
	// check for user template before using default template
	if (is_file($templatefile)) {
		include($templatefile);
	} else {
		include(ABSPATH . 'wp-content/plugins/' . WPPA_PLUGIN_PATH . '/theme/wppa_theme.php');
	}
}


require_once('wppa_functions.php');

?>
