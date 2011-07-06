<?php 
/* wppa_admin.php
* Package: wp-photo-album-plus
*
* Contains all the admin pages
* Version 3.1.0
*
* dbg
*/

/* SETUP */
//register_activation_hook(WPPA_FILE, 'wppa_setup');
//act hook is useless since wp does no longer call this hook after upgrade of the plugin
//this routine is now called at action admin_init, so also after initial install

//Set force to true to re-run it even when on rev (happens in wppa_settings.php)

function wppa_setup($force = false) {
	global $wpdb;
	global $wppa_revno;
	
	$old_rev = get_option('wppa_revision', '100');
//echo('oldrev='.$old_rev.' new rev='.$wppa_revno);
	if ($old_rev < $wppa_revno || $force) {
		
	$create_albums = "CREATE TABLE " . WPPA_ALBUMS . " (
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
                    
	$create_photos = "CREATE TABLE " . WPPA_PHOTOS . " (
                    id bigint(20) NOT NULL auto_increment, 
                    album bigint(20) NOT NULL, 
                    ext tinytext NOT NULL, 
                    name text NOT NULL, 
                    description longtext NOT NULL, 
                    p_order smallint(5) unsigned NOT NULL,
					mean_rating tinytext NOT NULL,
					linkurl text NOT NULL,
					linktitle text NOT NULL,
                    PRIMARY KEY  (id) 
                    );";

	$create_rating = "CREATE TABLE " . WPPA_RATING . " (
					id bigint(20) NOT NULL auto_increment,
					photo bigint(20) NOT NULL,
					value smallint(5) NOT NULL,
					user text NOT NULL,
					PRIMARY KEY  (id)
					);";
					
	$create_comments = "CREATE TABLE " . WPPA_COMMENTS . " (
					id bigint(20) NOT NULL auto_increment,
					timestamp tinytext NOT NULL,
					photo bigint(20) NOT NULL,
					user text NOT NULL,
					email text NOT NULL,
					comment text NOT NULL,
					status tinytext NOT NULL,
					PRIMARY KEY  (id)	
					);";
					
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta($create_albums);
    dbDelta($create_photos);
	dbDelta($create_rating);
	dbDelta($create_comments);
	
	wppa_set_defaults();
	wppa_check_dirs();
	
	$iret = true;
	
	if ($old_rev < '310') {		// theme and/or css changed since...
		$key = '0';
		$userstyle = ABSPATH.'wp-content/themes/'.get_option('template').'/wppa_style.css';
		$usertheme = ABSPATH.'wp-content/themes/'.get_option('template').'/wppa_theme.php';
		if (is_file($userstyle)) $key += '1';
		if (is_file($usertheme)) $key += '2';
		update_option('wppa_update_key', $key);
		}
	
	if ($old_rev < '243') {		// ownerfield added in...
		global $current_user;
		get_currentuserinfo();
		$user = $current_user->user_login;
		$query = $wpdb->prepare('UPDATE `'.WPPA_ALBUMS.'` SET `owner` = %s WHERE `owner` = %s', $user, '');
		$iret = $wpdb->query($query);
		}
		
	if ($iret !== false) update_option('wppa_revision', $wppa_revno);
//	echo('WPPA+ initialized');
	}
}

/* FORM SECURITY */
function wppa_nonce_field($action = -1, $name = 'wppa-update-check') { 
	return wp_nonce_field($action, $name); 
}
function wppa_check_admin_referer($arg1, $arg2) {
	check_admin_referer($arg1, $arg2);
}

/* ADMIN MENU */
add_action('admin_menu', 'wppa_add_admin');

function wppa_add_admin() {
	global $wp_roles;
	global $wpdb;

	if (current_user_can('administrator')) {	// Make sure admin has access rights
		$wp_roles->add_cap('administrator', 'wppa_admin');
		$wp_roles->add_cap('administrator', 'wppa_upload');
		$wp_roles->add_cap('administrator', 'wppa_sidebar_admin');
	}
	
	if (get_option('wppa_show_comments') == 'yes') {
		$pending = count($wpdb->get_results("SELECT id FROM ".WPPA_COMMENTS." WHERE status='pending'", "ARRAY_A"));
		$cnt = $pending ? '<span class="update-plugins"><span class="plugin-count">'.$pending.'</span></span>' : '';
	}
	else $cnt = '';
	
	$iconurl = WPPA_URL.'/images/camera16.png';
	add_menu_page('WP Photo Album', __('Photo&thinsp;Albums', 'wppa'), 'wppa_admin', WPPA_FILE, 'wppa_admin', $iconurl);
	
    add_submenu_page(WPPA_FILE, __('Upload Photos', 'wppa'), __('Upload Photos', 'wppa'), 'wppa_upload', 'upload_photos', 'wppa_page_upload');
	add_submenu_page(WPPA_FILE, __('Import Photos', 'wppa'), __('Import Photos', 'wppa'), 'wppa_upload', 'import_photos', 'wppa_page_import');
	add_submenu_page(WPPA_FILE, __('Export Photos', 'wppa'), __('Export Photos', 'wppa'), 'administrator', 'export_photos', 'wppa_page_export');
    add_submenu_page(WPPA_FILE, __('Settings', 'wppa'), __('Settings', 'wppa'), 'administrator', 'options', 'wppa_page_options');
	add_submenu_page(WPPA_FILE, __('Photo of the day Widget', 'wppa'), __('Photo of the day', 'wppa'), 'wppa_sidebar_admin', 'wppa_sidebar_options', 'wppa_sidebar_page_options');
	add_submenu_page(WPPA_FILE, __('Manage comments', 'wppa'), __('Comments', 'wppa').$cnt, 'administrator', 'manage_comments', 'wppa_comments');
    add_submenu_page(WPPA_FILE, __('Help &amp; Info', 'wppa'), __('Help &amp; Info', 'wppa'), 'edit_posts', 'wppa_help', 'wppa_page_help');
}

/* ADMIN STYLES */
add_action('admin_init', 'wppa_admin_styles');

function wppa_admin_styles() {
	wp_register_style('wppa_admin_style', WPPA_URL.'/admin_styles.css');
	wp_enqueue_style('wppa_admin_style');
}

/* ADMIN SCRIPTS */
add_action('admin_init', 'wppa_admin_scripts');

function wppa_admin_scripts() {
	wp_register_script('wppa_upload_script', WPPA_URL.'/multifile_compressed.js');
	wp_enqueue_script('wppa_upload_script');
	wp_register_script('wppa_admin_script', WPPA_URL.'/admin_scripts.js');
	wp_enqueue_script('wppa_admin_script');
	wp_enqueue_script('jquery');
}

/* ADMIN PHP's */
// to save server memory and speed up: only load when needed
function wppa_admin() {
	require_once('wppa_albumadmin.php');
	_wppa_admin();
}
function wppa_page_upload() {
	require_once('wppa_upload.php');
	_wppa_page_upload();
}
function wppa_page_import() {
	require_once('wppa_upload.php');
	_wppa_page_import();
}
function wppa_page_export() {
	require_once('wppa_export.php');
	_wppa_page_export();
}
function wppa_page_options() {	
	require_once('wppa_settings.php');
	_wppa_page_options();
}
function wppa_sidebar_page_options() {
	require_once('wppa_widgetadmin.php');
	_wppa_sidebar_page_options();
}
function wppa_page_help() {	
	require_once('wppa_help.php');
	_wppa_page_help();
}
function wppa_comments() { 
	require_once('wppa_commentadmin.php');
	_wppa_comments();
}
// General purpose admin functions
require_once('wppa_adminfunctions.php');

// WP no longer runs the activation hook at update so we do it here
add_action('admin_init', 'wppa_setup');
