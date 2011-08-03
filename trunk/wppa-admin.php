<?php 
/* wppa-admin.php
* Package: wp-photo-album-plus
*
* Contains the admin menu and startups the admin pages
* Version 4.0.0
*
*/


/* ADMIN MENU */
add_action( 'admin_menu', 'wppa_add_admin' );

function wppa_add_admin() {
	global $wp_roles;
	global $wpdb;

	// Make sure admin has access rights
	if ( current_user_can( 'administrator' ) ) {	
		$wp_roles->add_cap( 'administrator', 'wppa_admin' );
		$wp_roles->add_cap( 'administrator', 'wppa_upload' );
		$wp_roles->add_cap( 'administrator', 'wppa_sidebar_admin' );
	}
	
	// See if there are comments pending moderation
	$pending_html = '';
	if ( get_option( 'wppa_show_comments' ) == 'yes' ) {
		$pending_count = $wpdb->get_var( "SELECT COUNT(*) FROM ".WPPA_COMMENTS." WHERE status='pending'" );
		if ( $pending_count ) $pending_html = '<span class="update-plugins"><span class="plugin-count">'.$pending_count.'</span></span>';
	}
	
	$icon_url = WPPA_URL . '/images/camera16.png';
	add_menu_page( 'WP Photo Album', __('Photo&thinsp;Albums', 'wppa'), 'wppa_admin', WPPA_FILE, 'wppa_admin', $icon_url );
	
    add_submenu_page( WPPA_FILE, __('Upload Photos', 'wppa'), __('Upload Photos', 'wppa'), 'wppa_upload', 'upload_photos', 'wppa_page_upload' );
	add_submenu_page( WPPA_FILE, __('Import Photos', 'wppa'), __('Import Photos', 'wppa'), 'wppa_upload', 'import_photos', 'wppa_page_import' );
	add_submenu_page( WPPA_FILE, __('Export Photos', 'wppa'), __('Export Photos', 'wppa'), 'administrator', 'export_photos', 'wppa_page_export' );
    add_submenu_page( WPPA_FILE, __('Settings', 'wppa'), __('Settings', 'wppa'), 'administrator', 'options', 'wppa_page_options' );
	add_submenu_page( WPPA_FILE, __('Photo of the day Widget', 'wppa'), __('Photo of the day', 'wppa'), 'wppa_sidebar_admin', 'wppa_sidebar_options', 'wppa_sidebar_page_options' );
	add_submenu_page( WPPA_FILE, __('Manage comments', 'wppa'), __('Comments', 'wppa').$pending_html, 'administrator', 'manage_comments', 'wppa_comments' );
    add_submenu_page( WPPA_FILE, __('Help &amp; Info', 'wppa'), __('Help &amp; Info', 'wppa'), 'edit_posts', 'wppa_help', 'wppa_page_help' );
}

/* ADMIN STYLES */
add_action( 'admin_init', 'wppa_admin_styles' );

function wppa_admin_styles() {
	wp_register_style( 'wppa_admin_style', WPPA_URL.'/wppa-admin-styles.css' );
	wp_enqueue_style( 'wppa_admin_style' );
}

/* ADMIN SCRIPTS */
add_action( 'admin_init', 'wppa_admin_scripts' );

function wppa_admin_scripts() {
	wp_register_script( 'wppa_upload_script', WPPA_URL.'/wppa-multifile-compressed.js' );
	wp_enqueue_script( 'wppa_upload_script' );
	wp_register_script( 'wppa_admin_script', WPPA_URL.'/wppa-admin-scripts.js' );
	wp_enqueue_script( 'wppa_admin_script' );
	wp_enqueue_script( 'jquery' );
}

/* ADMIN PAGE PHP's */

// Album admin page
function wppa_admin() {
	require_once 'wppa-album-admin.php';
	_wppa_admin();
}
// Upload admin page
function wppa_page_upload() {
	require_once 'wppa-upload.php';
	_wppa_page_upload();
}
// Import admin page
function wppa_page_import() {
	require_once 'wppa-upload.php';
	_wppa_page_import();
}
// Export admin page
function wppa_page_export() {
	require_once 'wppa-export.php';
	_wppa_page_export();
}
// Settings admin page
function wppa_page_options() {	
	require_once 'wppa-settings.php';
	_wppa_page_options();
}
// Photo of the day admin page
function wppa_sidebar_page_options() {
	require_once 'wppa-widget-admin.php';
	_wppa_sidebar_page_options();
}
// Comments admin page
function wppa_comments() { 
	require_once 'wppa-comment-admin.php';
	_wppa_comments();
}
// Help admin page
function wppa_page_help() {	
	require_once 'wppa-help.php';
	_wppa_page_help();
}

/* GENERAL ADMIN */

// Check setup
require_once 'wppa-setup.php';
add_action('admin_init', 'wppa_setup');

// General purpose admin functions
require_once 'wppa-admin-functions.php';
