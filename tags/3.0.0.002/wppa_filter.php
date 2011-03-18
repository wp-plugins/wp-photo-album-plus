<?php
/* wppa_filter.php
* Package: wp-photo-album-plus
*
* get the albums via filter
* version 3.0.0
*
*/

add_action('init', 'wppa_do_filter');

function wppa_do_filter() {
	add_filter('the_content', 'wppa_albums_filter', 10);
}

function wppa_albums_filter($post) {
global $wppa;

	wppa_initialize_runtime();

	$post_old = $post;
	$post_new = '';
	
	if (substr_count($post_old, '%%wppa%%') > 0) {						// Yes, there is something to do here
		$wppa['occur'] = '0';											// Init this occurance
		$wppa_pos = strpos($post_old, '%%wppa%%');						// Where in the post is the invocation
		while ($wppa_pos !== false) {
		
			$text_chunk = substr($post_old, 0, $wppa_pos);
			$post_new .= wppa_force_balance_pee($text_chunk);				// Copy BEFORE part to new post
			
			$post_old = substr($post_old, $wppa_pos);					// Shift BEFORE part out of old post
			$post_old = substr($post_old, 8);							// Shift %%wppa%% out of old post

			$album_pos = strpos($post_old, '%%album=');					// Is there an album given?
			$cover_pos = strpos($post_old, '%%cover=');					// Is there a cover given?
			$slide_pos = strpos($post_old, '%%slide=');					// Is there a slidealbum given?
			$slideonly_pos = strpos($post_old, '%%slideonly=');			// Is there a slideonly album given?
			$photo_pos = strpos($post_old, '%%photo=');					// Is there a photo id given?
			$mphoto_pos = strpos($post_old, '%%mphoto=');
			$size_pos = strpos($post_old, '%%size=');					// Is there a size given?
			$align_pos = strpos($post_old, '%%align=');					// Is there an align given?
			
			$wppa_pos = strpos($post_old, '%%wppa%%');					// Is there another occurrence?
			// Invalidate positions if they belong to a later occurance
			if (is_numeric($wppa_pos)) {								// Yes there is another occurance
				if (is_numeric($album_pos) && $album_pos > $wppa_pos) $album_pos = 'nil';
				if (is_numeric($cover_pos) && $cover_pos > $wppa_pos) $cover_pos = 'nil';
				if (is_numeric($slide_pos) && $slide_pos > $wppa_pos) $slide_pos = 'nil';
				if (is_numeric($slideonly_pos) && $slideonly_pos > $wppa_pos) $slideonly_pos = 'nil';
				if (is_numeric($photo_pos) && $photo_pos > $wppa_pos) $photo_pos = 'nil';
			if (is_numeric($mphoto_pos) && $mphoto_pos > $wppa_pos) $mphoto_pos = 'nil';
				if (is_numeric($size_pos) && $size_pos > $wppa_pos) $size_pos = 'nil';
				if (is_numeric($align_pos) && $align_pos > $wppa_pos) $align_pos = 'nil';
			}
			// set defaults
			$wppa['start_album'] = '';
			$wppa['is_cover'] = '0';
			$wppa['is_slide'] = '0';
			$wppa['is_slideonly'] = '0';
			$wppa['single_photo'] = '';
			$wppa['is_mphoto'] = '0';
			$size = '';
			$align = '';
			// examine album number
			if (is_numeric($album_pos)) {				
				$post_old = substr($post_old, $album_pos + 8);				// shift up to and including %%album= out
				$wppa['start_album'] = wppa_atoi($post_old);				// get album #
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift album # and trailing %% out
			}
			elseif (is_numeric($cover_pos)) {
				$post_old = substr($post_old, $cover_pos + 8);				// shift up to and including %%cover= out
				$wppa['start_album'] = wppa_atoi($post_old);				// get album #
				$wppa['is_cover'] = '1';
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift album # and trailing %% out
			}
			elseif (is_numeric($slide_pos)) {
				$post_old = substr($post_old, $slide_pos + 8);				// shift up to and including %%slide= out
				$wppa['start_album'] = wppa_atoi($post_old);				// get album #
				$wppa['is_slide'] = '1';
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift album # and trailing %% out
			}
			elseif (is_numeric($slideonly_pos)) {
				$post_old = substr($post_old, $slideonly_pos + 12);			// shift up to and including %%slideonly= out
				$wppa['start_album'] = wppa_atoi($post_old);				// get album #
				$wppa['is_slideonly'] = '1';
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift album # and trailing %% out
			}
			elseif (is_numeric($photo_pos)) {
				$post_old = substr($post_old, $photo_pos + 8);				// shift up to and including %%photo= out
				$wppa['single_photo'] = wppa_atoi($post_old);				// get photo #
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift photo # and trailing %% out
			}
			elseif (is_numeric($mphoto_pos)) {
				$post_old = substr($post_old, $mphoto_pos + 9);				// shift up to and including %%mphoto= out
				$wppa['single_photo'] = wppa_atoi($post_old);				// get photo #
				$wppa['is_mphoto'] = '1';
				$post_old = substr($post_old, strpos($post_old, '%%') + 2);	// shift photo # and trailing %% out
			}
			// see if a size is given and get it
			if (is_numeric($size_pos)) {
				$size_pos = strpos($post_old, '%%size=');					// refresh position due to out-shifting above
				$post_old = substr($post_old, $size_pos + 7);				// shift up to and including %%size= out
				$size = wppa_atoi($post_old);								// get size #
				if (substr_compare($post_old, 'auto', 0, 4) == 0) $size = 'auto';

				$post_old = substr($post_old, strpos($post_old, '%%') + 2); // shift size # and trailing %% out
				if ($size == 'auto') {
					$wppa['auto_colwidth'] = true;
					$wppa['fullsize'] = '';
				}
				else {
					$wppa['auto_colwidth'] = false;
					$wppa['fullsize'] = $size;
				}
			}
			// see if alignment is given and get it
			if (is_numeric($align_pos)) {
				$align_pos = strpos($post_old, '%%align=');					// refresh position due to out-shifting above
				$post_old = substr($post_old, $align_pos + 8);				// shift up to and including %%align= out
				if (substr_compare($post_old, 'left', 0, 4) == 0) $align = 'left';
				elseif (substr_compare($post_old, 'center', 0, 6) == 0) $align = 'center';
				elseif (substr_compare($post_old, 'right', 0, 5) == 0) $align = 'right';
				$post_old = substr($post_old, strpos($post_old, '%%') + 2); // shift position and trailing %% out
				$wppa['align'] = $align;
			}
			
			$post_new .= wppa_albums();										// Insert the html
			
			$wppa_pos = strpos($post_old, '%%wppa%%');						// Refresh the next invocation position, if any
		}
	}
	$post_new .= wppa_force_balance_pee($post_old);							// Copy the rest of the post/page
	return $post_new;
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