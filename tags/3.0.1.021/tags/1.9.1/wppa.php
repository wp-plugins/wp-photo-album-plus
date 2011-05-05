<?php
/*
Plugin Name: WP Photo Album Plus
Description: Easily manage and display your photo albums and slideshows within your WordPress site.
Version: 1.9.1
Author: J.N. Breetvelt a.k.a OpaJaap
Author URI: http://www.opajaap.nl/
Plugin URI: http://wordpress.org/extend/plugins/wp-photo-album-plus/
*/

load_plugin_textdomain('wppa', 'wp-content/plugins/wp-photo-album-plus/langs/', 'wp-photo-album-plus/langs/');

/* GLOBAL SETTINGS */
global $wpdb;

define('ALBUM_TABLE', $wpdb->prefix . 'wppa_albums');
define('PHOTO_TABLE', $wpdb->prefix . 'wppa_photos');
define('PLUGIN_PATH', 'wp-photo-album-plus');

global $wppa_occur;
$wppa_occur = 0;

/* FORM SECURITY */
if ( !function_exists('wp_nonce_field') ) {
        function wppa_nonce_field($action = -1) { return; }
        $wppa_nonce = -1;
} else {
		function wppa_nonce_field($action = -1,$name = 'wppa-update-check') { return wp_nonce_field($action,$name); }
		define('WPPA_NONCE' , 'wppa-update-check');
}

/* SETUP */
// calls the setup function on activation
register_activation_hook( __FILE__, 'wppa_setup' );

// does the initial setup
function wppa_setup() {
	global $wpdb;
	
	if (get_option('wppa_revision', '100') <= '191') {
		
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
                    );";
                    
	$create_photos = "CREATE TABLE " . PHOTO_TABLE . " (
                    id bigint(20) NOT NULL auto_increment, 
                    album bigint(20) NOT NULL, 
                    ext tinytext NOT NULL, 
                    name text NOT NULL, 
                    description longtext NOT NULL, 
                    p_order smallint(5) unsigned NOT NULL,
                    PRIMARY KEY  (id) 
                    );";

    require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

    dbDelta($create_albums);
    dbDelta($create_photos);
	
	if (!is_numeric(get_option('wppa_fullsize', 'nil'))) update_option('wppa_fullsize', '800');
	if (!is_numeric(get_option('wppa_thumbsize', 'nil'))) update_option('wppa_thumbsize', '150');
	if (!is_numeric(get_option('wppa_smallsize', 'nil'))) update_option('wppa_smallsize', '95');
	if (get_option('wppa_show_bread', 'nil') == 'nil') update_option('wppa_show_bread', 'yes');
	if (get_option('wppa_use_thumb_opacity', 'nil') =='nil') update_option('wppa_use_thumb_opacity', 'no');
	if (!is_numeric(get_option('wppa_thumb_opacity', 'nil'))) update_option('wppa_thumb_opacity', '60');
	if (get_option('wppa_use_cover_opacity', 'nil') =='nil') update_option('wppa_use_cover_opacity', 'no');
	if (!is_numeric(get_option('wppa_cover_opacity', 'nil'))) update_option('wppa_cover_opacity', '60');
	
	update_option('wppa_revision', '191');
	}
}

/* LOAD SIDEBAR WIDGET */
require_once('wppa_widget.php');

/* ADMIN MENU */
add_action('admin_menu', 'wppa_add_admin');

function wppa_add_admin() {
	$level = get_option('wppa-accesslevel');
	if (empty($level)) { $level = 'level_10'; }
	
	$iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . PLUGIN_PATH . '/images/camera16.png';
	
	add_menu_page('WP Photo Album', __('Photo Albums', 'wppa'), $level, __FILE__, 'wppa_admin', $iconurl);
	
    add_submenu_page(__FILE__, __('Upload Photos', 'wppa'), __('Upload Photos', 'wppa'), $level, 'upload_photos', 'wppa_page_upload');
    add_submenu_page(__FILE__, __('Settings', 'wppa'), __('Settings', 'wppa'), $level, 'options', 'wppa_page_options');
	add_submenu_page(__FILE__, __('Sidebar Widget', 'wppa'), __('Sidebar Widget', 'wppa'), $level, 'wppa_sidebar_options', 'wppa_sidebar_page_options');
    add_submenu_page(__FILE__, __('Help &amp; Info', 'wppa'), __('Help &amp; Info', 'wppa'), $level, 'wppa_help', 'wppa_page_help');
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
		wp_register_style('wppa_style', '/wp-content/plugins/' . PLUGIN_PATH . '/theme/wppa_style.css');
		wp_enqueue_style('wppa_style');
	}
}


/* LISTING FUNCTIONS */
// get the albums
function wppa_albums($xalb = '', $type='', $siz = '') {
	global $wpdb;
    global $startalbum;
	global $wppa_occur;
	global $is_cover;
	global $wppa_fullsize;
    
    if (is_numeric($xalb)) $startalbum = $xalb;
	if (!is_numeric($wppa_occur)) $wppa_occur = '0'; else $wppa_occur++;
	if ($type == 'album') $is_cover = '0';
	elseif ($type == 'cover') $is_cover = '1';
	if (is_numeric($siz)) $wppa_fullsize = $siz;
    
	$templatefile = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa_theme.php';
	
	// check for user template before using default template
	if (is_file($templatefile)) {
		include($templatefile);
	} else {
		include(ABSPATH . 'wp-content/plugins/' . PLUGIN_PATH . '/theme/wppa_theme.php');
	}
}


require_once('wppa_functions.php');

?>
