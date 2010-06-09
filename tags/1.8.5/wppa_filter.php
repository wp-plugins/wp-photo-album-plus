<?php
/* wppa_filter.php
* Pachkage: wp-photo-album-plus
*
* get the albums via filter
*/

add_action('init', 'wppa_do_filter');

function wppa_do_filter() {
	add_filter('the_content', 'wppa_albums_filter', 99);
}

function wppa_albums_filter($post) {
	$post_old = $post;
	$post_new = '';
	$occur = '0';
	
	if (substr_count($post, '%%wppa%%') > 0) {
		$wppa_pos = strpos($post_old, '%%wppa%%');
		while (is_numeric($wppa_pos)) {
			$post_new .= wppa_disp(substr($post_old, 0, $wppa_pos));	// Copy BEFORE part to new post
			$post_old = substr($post_old, $wppa_pos);					// Shift BEFORE part out
			$post_old = substr($post_old, 8);							// Shift %%wppa%% out
			$album_pos = strpos($post_old, '%%album=');					// Is there an album given?
			$cover_pos = strpos($post_old, '%%cover=');					// Is there a cover given?
			$size_pos = strpos($post_old, '%%size=');					// Is there a size given?
			$wppa_pos = strpos($post_old, '%%wppa%%');					// Is there another occurrence?
			// set defaults
			$album_number = '';
			$is_cover = '';
			$size = '';
			// examine album number
			if (is_numeric($album_pos)) {
				if (!is_numeric($wppa_pos) || (is_numeric($wppa_pos) && ($album_pos < $wppa_pos))) {
					$post_old = substr($post_old, $album_pos + 8);		// shift up to and including %%album= out
					$album_number = wppa_atoi($post_old);				// get album #
					$iscover = '0';
					$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift album # and trailing %% out
				}	
			}
			elseif (is_numeric($cover_pos)) {
				if (!is_numeric($wppa_pos) || (is_numeric($wppa_pos) && ($cover_pos < $wppa_pos))) {
					$post_old = substr($post_old, $cover_pos + 8);		// shift up to and including %%cover= out
					$album_number = wppa_atoi($post_old);				// get album #
					$iscover = '1';
					$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift album # and trailing %% out
				}
			}
			$size_pos = strpos($post_old, '%%size=');					// Refresh 
			if (is_numeric($size_pos)) {
				if (!is_numeric($wppa_pos) || (is_numeric($wppa_pos) && ($size_pos < $wppa_pos))) {
					$post_old = substr($post_old, $size_pos + 7);		// shift up to and including %%size= out
					$size = wppa_atoi($post_old);						// get size #
					$post_old = substr($post_old, strpos($post_old, '%%') + 2); // shift size # and trailing %% out
				}
			}
			
			$post_new .= wppa_set_album($album_number);
			$post_new .= wppa_set_cover($iscover);
			$post_new .= wppa_set_occur($occur);
			if ($size) $post_new .= wppa_set_fullsize($size);
			$post_new .= wppa_albums();		
			$wppa_pos = strpos($post_old, '%%wppa%%');
			$occur++;	
		}
	}
	$post_new .= $post_old;
	return $post_new;
}

/* If you simplify the following small routines, by coding it inline in the filter, the sky will fall upon you */
function wppa_disp($var) {
	echo($var);
}

function wppa_set_album($alb) {
	global $startalbum;
	$startalbum = $alb;
}

function wppa_set_cover($iscov) {
	global $is_cover;
	$is_cover = $iscov;
}

function wppa_set_occur($occur) {
	global $wppa_occur;
	$wppa_occur = $occur;
}

function wppa_set_fullsize($siz) {
	global $wppa_fullsize;
	$wppa_fullsize = $siz;
}

function wppa_atoi($var) {
	$result = '0';
	$len = 0;
	$t = $result;
	while (is_numeric($t)) {
		$result = $t;
		$len++;
		$t = substr($var, 0, $len);		
	}
	return $result;
}

?>