<?php
/* wppa-styles.php
* Package: wp-photo-album-plus
*
* Various style computation routines
* Version 5.0.17
*
*/

if ( ! defined( 'ABSPATH' ) )
    die( "Can't load this file directly" );

// get full img style
function wppa_get_fullimgstyle($id = '') {
	$temp = wppa_get_fullimgstyle_a($id);
	if ( is_array($temp) ) return $temp['style'];
	else return '';
}

// get full img style - array output
function wppa_get_fullimgstyle_a($id) {
global $wppa;
global $wppa_opt;
global $thumb;

	if (!is_numeric($wppa['fullsize']) || $wppa['fullsize'] < '1') $wppa['fullsize'] = $wppa_opt['wppa_fullsize'];

	$wppa['enlarge'] = $wppa_opt['wppa_enlarge'];

	wppa_cache_thumb($id);

	$img_path = wppa_get_photo_path($id);
	$result = wppa_get_imgstyle_a($img_path, $wppa['fullsize'], 'optional', 'fullsize');
	return $result;
}

