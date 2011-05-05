<?php
/*
Plugin Name: WP Photo Album Plus
Description: Easily manage and display your photo albums and slideshows within your WordPress site.
Version: 2.5.1
Author: J.N. Breetvelt a.k.a OpaJaap
Author URI: http://www.opajaap.nl/
Plugin URI: http://wordpress.org/extend/plugins/wp-photo-album-plus/
*/

load_plugin_textdomain('wppa', false, 'wp-photo-album-plus/langs/');

/* GLOBAL SETTINGS */
global $wpdb;
global $wp_roles;
global $wppa_occur;
global $wppa_master_occur;
global $wppa_widget_occur;
global $is_cover;
global $wppa_src;
global $wppa_revno;
global $wppa_in_widget;
global $wppa_portrait_only;
global $wppa_in_widget_linkurl;
global $wppa_in_widget_linktitle;
global $wppa_in_widget_timeout;
global $wppa_ss_widget_valign;

// Check for php version
// PHP_VERSION_ID is available as of PHP 5.2.7, if our 
// version is lower than that, then emulate it
if (!defined('PHP_VERSION_ID')) {
	$version = explode('.', PHP_VERSION);

	define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

define('ALBUM_TABLE', $wpdb->prefix . 'wppa_albums');
define('PHOTO_TABLE', $wpdb->prefix . 'wppa_photos');
define('WPPA_RATING', $wpdb->prefix . 'wppa_rating');
define('WPPA_PLUGIN_PATH', 'wp-photo-album-plus');
define('WPPA_NONCE' , 'wppa-update-check');

$wppa_revno = '250';
$wppa_occur = 0;
$wppa_master_occur = 0;
$wppa_widget_occur = 0;
$is_cover = '0';
$wppa_src = false;
if (isset($_POST['wppa-searchstring'])) $wppa_src = true;
if (isset($_GET['wppa_src'])) $wppa_src = true;
$wppa_in_widget = false;
$wppa_portrait_only = false;
$wppa_in_widget_linkurl = '';
$wppa_in_widget_linktitle = '';
$wppa_in_widget_timeout = '0';
$wppa_ss_widget_valign = '';

/* FORM SECURITY */
function wppa_nonce_field($action = -1, $name = 'wppa-update-check') { 
	return wp_nonce_field($action, $name); 
}
function wppa_check_admin_referer($arg1, $arg2) {
	check_admin_referer($arg1, $arg2);
}

/* SETUP */
// calls the setup function on activation
register_activation_hook( __FILE__, 'wppa_setup' );

// does the initial setup
function wppa_setup() {
	global $wpdb;
	global $wppa_revno;
	
	$old_rev = get_option('wppa_revision', '100');
	if ($old_rev <= $wppa_revno) {
		
	$create_albums = "CREATE TABLE " . ALBUM_TABLE . " (
                    id bigint(20) NOT NULL auto_increment, 
                    name text NOT NULL, 
                    description text NOT NULL, 
                    a_order smallint(5) unsigned NOT NULL, 
                    main_photo bigint(20) NOT NULL, 
                    a_parent bigint(20) NOT NULL,
                    p_order_by int unsigned NOT NULL,
					cover_linkpage bigint(20) NOT NULL,
					owner text NOT NULL,
                    PRIMARY KEY  (id) 
                    );";
                    
	$create_photos = "CREATE TABLE " . PHOTO_TABLE . " (
                    id bigint(20) NOT NULL auto_increment, 
                    album bigint(20) NOT NULL, 
                    ext tinytext NOT NULL, 
                    name text NOT NULL, 
                    description longtext NOT NULL, 
                    p_order smallint(5) unsigned NOT NULL,
					mean_rating tinytext NOT NULL,
                    PRIMARY KEY  (id) 
                    );";

	$create_rating = "CREATE TABLE " . WPPA_RATING . " (
					id bigint(20) NOT NULL auto_increment,
					photo bigint(20) NOT NULL,
					value smallint(5) NOT NULL,
					user text NOT NULL,
					PRIMARY KEY  (id)
					);";
					
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta($create_albums);
    dbDelta($create_photos);
	dbDelta($create_rating);
	
	wppa_set_defaults();

	$iret = true;
	
	if ($old_rev < '250') {		// theme and/or css changed since...
		$key = '0';
		$userstyle = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa_style.css';
		$usertheme = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa_theme.php';
		if (is_file($userstyle)) $key += '1';
		if (is_file($usertheme)) $key += '2';
		update_option('wppa_update_key', $key);
		}
	
	if ($old_rev < '243') {		// ownerfield added in...
		global $current_user;
		get_currentuserinfo();
		$user = $current_user->user_login;
		$query = $wpdb->prepare('UPDATE `' . ALBUM_TABLE . '` SET `owner` = %s WHERE `owner` = %s', $user, '');
		$iret = $wpdb->query($query);
		}
		
	if ($iret !== false) update_option('wppa_revision', $wppa_revno);
	
	}
}
	
/* LOAD SIDEBAR WIDGETS */
require_once('wppa_widget.php');
require_once('wppa_searchwidget.php');
require_once('wppa_toptenwidget.php');
require_once('wppa_slideshow_widget.php');
require_once('wppa_gp_widget.php');

/* ADMIN MENU */
add_action('admin_menu', 'wppa_add_admin');

function wppa_add_admin() {
	global $wp_roles;

	if (current_user_can('administrator')) {	// Make sure admin has access rights
		$wp_roles->add_cap('administrator', 'wppa_admin');
		$wp_roles->add_cap('administrator', 'wppa_upload');
		$wp_roles->add_cap('administrator', 'wppa_sidebar_admin');
	}

	$iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/camera16.png';
	add_menu_page('WP Photo Album', __('Photo Albums', 'wppa'), 'wppa_admin', __FILE__, 'wppa_admin', $iconurl);
	
    add_submenu_page(__FILE__, __('Upload Photos', 'wppa'), __('Upload Photos', 'wppa'), 'wppa_upload', 'upload_photos', 'wppa_page_upload');
	add_submenu_page(__FILE__, __('Import Photos', 'wppa'), __('Import Photos', 'wppa'), 'wppa_upload', 'import_photos', 'wppa_page_import');
	add_submenu_page(__FILE__, __('Export Photos', 'wppa'), __('Export Photos', 'wppa'), 'administrator', 'export_photos', 'wppa_page_export');
    add_submenu_page(__FILE__, __('Settings', 'wppa'), __('Settings', 'wppa'), 'administrator', 'options', 'wppa_page_options');
	add_submenu_page(__FILE__, __('Sidebar Widget', 'wppa'), __('Sidebar Widget', 'wppa'), 'wppa_sidebar_admin', 'wppa_sidebar_options', 'wppa_sidebar_page_options');
    add_submenu_page(__FILE__, __('Help &amp; Info', 'wppa'), __('Help &amp; Info', 'wppa'), 'edit_posts', 'wppa_help', 'wppa_page_help');
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

/* LOAD API and LOW LEVEL FUNCTIONS */
require_once('wppa_functions.php');
