<?php 
/* wppa_admin.php
* Package: wp-photo-album-plus
*
* Contains all the admin pages
* Version 3.0.6
*
* dbg
*/

/* SETUP */
register_activation_hook(WPPA_FILE, 'wppa_setup');

function wppa_setup() {
	global $wpdb;
	global $wppa_revno;
	
	$old_rev = get_option('wppa_revision', '100');
	if ($old_rev <= $wppa_revno) {
		
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
					
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta($create_albums);
    dbDelta($create_photos);
	dbDelta($create_rating);
	
	wppa_set_defaults();
	wppa_check_dirs();
	
	$iret = true;
	
	if ($old_rev < '302') {		// hide is obsolete, we use eneble now
		$opt = get_option('wppa_hide_slideshow', 'nil');
		if ($opt != 'nil') {
			if ($opt == 'yes') update_option('wppa_enable_slideshow', 'no');
			else update_option('wppa_enable_slideshow', 'yes');
			delete_option('wppa_hide_slideshow');
		}
		$opt = get_option('wppa_toptenwidgettitle', 'nil');	// obsolete
		if ($opt != 'nil') {
			delete_option('wppa_toptenwidgettitle');
		}
		$opt = get_option('wppa_black', 'nil'); // obsolete
		if ($opt != 'nil') {
			update_option('wppa_fontcolor_box', $opt);
			delete_option('wppa_black');
		}
	}
	
	if ($old_rev < '300') {		// theme and/or css changed since...
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

	if (current_user_can('administrator')) {	// Make sure admin has access rights
		$wp_roles->add_cap('administrator', 'wppa_admin');
		$wp_roles->add_cap('administrator', 'wppa_upload');
		$wp_roles->add_cap('administrator', 'wppa_sidebar_admin');
	}

	$iconurl = WPPA_URL.'/images/camera16.png';
	add_menu_page('WP Photo Album', __('Photo&thinsp;Albums', 'wppa'), 'wppa_admin', WPPA_FILE, 'wppa_admin', $iconurl);
	
    add_submenu_page(WPPA_FILE, __('Upload Photos', 'wppa'), __('Upload Photos', 'wppa'), 'wppa_upload', 'upload_photos', 'wppa_page_upload');
	add_submenu_page(WPPA_FILE, __('Import Photos', 'wppa'), __('Import Photos', 'wppa'), 'wppa_upload', 'import_photos', 'wppa_page_import');
	add_submenu_page(WPPA_FILE, __('Export Photos', 'wppa'), __('Export Photos', 'wppa'), 'administrator', 'export_photos', 'wppa_page_export');
    add_submenu_page(WPPA_FILE, __('Settings', 'wppa'), __('Settings', 'wppa'), 'administrator', 'options', 'wppa_page_options');
	add_submenu_page(WPPA_FILE, __('Photo of the day Widget', 'wppa'), __('Photo of the day', 'wppa'), 'wppa_sidebar_admin', 'wppa_sidebar_options', 'wppa_sidebar_page_options');
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
require_once('wppa_albumadmin.php');
require_once('wppa_upload.php');
require_once('wppa_settings.php');
require_once('wppa_widgetadmin.php');
require_once('wppa_help.php');
require_once('wppa_adminfunctions.php');
require_once('wppa_export.php');
