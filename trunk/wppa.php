<?php
/*
Plugin Name: WP Photo Album Plus
Description: Easily manage and display your photo albums and slideshows within your WordPress site.
Version: 4.1.0
Author: J.N. Breetvelt a.k.a OpaJaap
Author URI: http://www.opajaap.nl/
Plugin URI: http://wordpress.org/extend/plugins/wp-photo-album-plus/
*/

/* GLOBALS */
global $wppa_revno; $wppa_revno = '410';	/* This is the database revision number */
global $wpdb;

/* CONSTANTS
/*
/* Check for php version
/* PHP_VERSION_ID is available as of PHP 5.2.7, if our 
/* version is lower than that, then emulate it
*/
if ( ! defined( 'PHP_VERSION_ID' ) ) {
	$version = explode( '.', PHP_VERSION );
	define( 'PHP_VERSION_ID', ( $version[0] * 10000 + $version[1] * 100 + $version[2] ) );
}
define( 'WPPA_ALBUMS',   $wpdb->prefix . 'wppa_albums' );
define( 'WPPA_PHOTOS',   $wpdb->prefix . 'wppa_photos' );
define( 'WPPA_RATING',   $wpdb->prefix . 'wppa_rating' );
define( 'WPPA_COMMENTS', $wpdb->prefix . 'wppa_comments' );
																// Standard examples
define( 'WPPA_FILE', basename( __FILE__ ) );					// wppa.php
define( 'WPPA_PATH', dirname( __FILE__ ) );						// /.../wp-content/plugins/wp-photo-album-plus
define( 'WPPA_NAME', basename( dirname( __FILE__ ) ) );			// wp-photo-album-plus
define( 'WPPA_URL',  plugins_url() . '/' . WPPA_NAME );			// http://...../wp-photo-album-plus

define('WPPA_NONCE' , 'wppa-update-check');

/* LOAD SIDEBAR WIDGETS */
require_once 'wppa-potd-widget.php';
require_once 'wppa-search-widget.php';
require_once 'wppa-topten-widget.php';
require_once 'wppa-slideshow-widget.php';
require_once 'wppa-gp-widget.php';

/* COMMON FUNCTIONS */
require_once 'wppa-common-functions.php';

/* SET UP $wppa[], $wppa_opt[], URL and PATH constants and LANGUAGE */
add_action('init', 'wppa_initialize_runtime', '100');

/* DO THE ADMIN/NON ADMIN SPECIFIC STUFF */
if ( is_admin() ) require_once 'wppa-admin.php';
else require_once 'wppa-non-admin.php';
