<?php
/* wppa-ajax-front.php
*
* Supplies the functionality like wp-admin/admin-ajax.php for wppa frontend ajax requests without using wp-admin files
* version 5.1.17
*
*/
define( 'DOING_AJAX', true );

/** Load WordPress Bootstrap */
require_once ( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

/** Allow for cross-domain requests (from the frontend). */
send_origin_headers();

// Require an action parameter
if ( empty( $_REQUEST['action'] ) )
	die( '0' );

// Load the wppa admin functions
require_once 'wppa-admin.php';

@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
@header( 'X-Robots-Tag: noindex' );

send_nosniff_header();
nocache_headers();

wppa_ajax_callback();
	
