<?php
/* wppa-index-common.php
* Package: wp-photo-album-plus
*
* indexing common functions
* version 5.2.5
*
* 
*/

// Add an item to the index
function wppa_index_add($type, $id = '') {
global $album;	
global $thumb;	
global $wpdb;
global $acount;
global $pcount;
global $wppa;

	if ( ! wppa_switch('wppa_indexed_search') ) {
		update_option('wppa_index_need_remake', 'yes');
		return;
	}
	
	if ( $type == 'album' ) {
		// Use cached album?
		if ( is_numeric($id) ) {
			$album = '';	// Clear album cache
			wppa_cache_album($id);
		}		
		
		// Find the raw text
		$words = __( $album['name'] ).' '.wppa_get_album_desc($album['id']);
		if ( wppa_switch( 'wppa_search_cats' ) ) {
			$words .= ' '.$album['cats'];
		}
		
		$words = wppa_index_raw_to_words($words);
		foreach ( $words as $word ) {
			$indexline = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPPA_INDEX."` WHERE `slug` = %s", $word), ARRAY_A);
			if ( ! $indexline ) {	// create new entry
				$id = wppa_create_index_entry( array( 'slug' => $word, 'albums' => $album['id'] ) );
			}
			else { 	// Add to entry
				$oldalbums = wppa_index_string_to_array($indexline['albums']);
				if ( ! in_array($album['id'], $oldalbums) ) {
					$oldalbums[] = $album['id'];
					sort($oldalbums);
					$newalbums = wppa_index_array_to_string($oldalbums);
					$wpdb->query($wpdb->prepare( "UPDATE `".WPPA_INDEX."` SET `albums` = %s WHERE `id` = %s", $newalbums, $indexline['id']));
				}
			}
		}
		$acount ++;
	}
	
	elseif ( $type == 'photo' ) {
		
		// Use cached photo?
		if ( is_numeric($id) ) {
			$thumb = '';	// Clear cache
			wppa_cache_thumb($id);
		}
		
		// Find the rew text
		$words = __( $thumb['name'] ).' '.$thumb['filename'].' '.wppa_get_photo_desc( $thumb['id'] );
		if ( wppa_switch( 'wppa_search_tags' ) ) $words .= ' '.$thumb['tags'];																					// Tags
		if ( wppa_switch( 'wppa_search_comments' ) ) {
			$coms = $wpdb->get_results($wpdb->prepare( "SELECT `comment` FROM `" . WPPA_COMMENTS . "` WHERE `photo` = %s AND `status` = 'approved'", $thumb['id'] ), ARRAY_A );
			if ( $coms ) {
				foreach ( $coms as $com ) {
					$words .= ' '.stripslashes( $com['comment'] );
				}
			}
		}
		
		$words = wppa_index_raw_to_words($words);	// convert raw string to sanitized array
		foreach ( $words as $word ) {
			$indexline = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPPA_INDEX."` WHERE `slug` = %s", $word), ARRAY_A);
			if ( ! $indexline ) {	// create new entry
				$id = wppa_create_index_entry( array( 'slug' => $word, 'photos' => $thumb['id'] ) );
			}
			else { 	// Add to entry
				$oldphotos = wppa_index_string_to_array($indexline['photos']);
				if ( ! in_array($thumb['id'], $oldphotos) ) {
					$oldphotos[] = $thumb['id'];
					sort($oldphotos);
					$newphotos = wppa_index_array_to_string($oldphotos);
					$wpdb->query($wpdb->prepare( "UPDATE `".WPPA_INDEX."` SET `photos` = %s WHERE `id` = %s", $newphotos, $indexline['id']));
				}
			}
			
		}
		$pcount ++;
	}
	
	else wppa_dbg_msg('Error, unimplemented type in wppa_index_add().', 'red', 'force');
}

// Convert raw data string to indexable word array
function wppa_index_raw_to_words($xtext, $noskips = false) {

	$ignore = array( '"', "'", '\\', '>', '<', ',', ':', ';', '!', '?', '=', '_', '[', ']', '(', ')', '{', '}', '..', '...', '....', "\n", "\r", "\t", '.jpg', '.png', '.gif', '&#039', '&amp' );
	if ( $noskips ) $skips = array();
	else $skips = get_option('wppa_index_skips', array());
	
	$result = array();
	if ( $xtext ) {
		$text = strtolower($xtext);
		$text = html_entity_decode($text);
		$text = wppa_strip_tags($text, 'script&style');	// strip style and script tags inclusive content
		$text = str_replace('>', '> ', $text);			// Make sure <td>word1</td><td>word2</td> will not endup in 'word1word2', but in 'word1' 'word2'
		$text = strip_tags($text);						// Now strip the tags
		$text = str_replace($ignore, ' ', $text);		// Remove funny chars
		$text = trim($text);
		$text = trim($text, " ./-");
		while ( strpos($text, '  ') ) $text = str_replace('  ', ' ', $text);	// Compress spaces
		$words = explode(' ', $text);
		foreach ( $words as $word ) {
			$word = trim($word);
			$word = trim($word, " ./-");
			if ( strlen($word) > '1' && ! in_array($word, $skips) ) $result[] = $word;
			if ( strpos($word, '-') !== false ) {
				$fracts = explode('-', $word);
				foreach ( $fracts as $fract ) {
					$fract = trim($fract);
					$fract = trim($fract, " ./-");
					if ( strlen($fract) > '1' && ! in_array($fract, $skips) ) $result[] = $fract;
				}
			}
		}
	}
	return $result;
}

// Expand compressed string
function wppa_index_string_to_array($string) {
	// Anything?
	if ( ! $string ) return array();
	// Any ranges?
	if ( ! strstr($string, '..') ) return explode(',', $string);	// No
	// Yes
	$temp = explode(',', $string);
	$result = array();
	foreach ( $temp as $t ) {
		if ( ! strstr($t, '..') ) $result[] = $t;
		else {
			$range = explode('..', $t);
			$from = $range['0'];
			$to = $range['1'];
			while ( $from <= $to ) {
				$result[] = $from;
				$from++;
			}
		}
	}
	return $result;
}

// Compress array ranges and convert to string
function wppa_index_array_to_string($array) {
	$result = '';
	$lastitem = '-1';
	$isrange = false;
	foreach ( $array as $item ) {
		if ( $item == $lastitem+'1' ) {
			$isrange = true;
		}
		else {
			if ( $isrange ) {	// Close range
				$result .= '..'.$lastitem.','.$item;
				$isrange = false;
			}
			else {				// Add single item
				$result .= ','.$item;
			}
		}
		$lastitem = $item;
	}
	if ( $isrange ) {	// Don't forget the last if it ends in a range
		$result .= '..'.$lastitem;
	}
	$result = trim($result, ',');
	return $result;
}
