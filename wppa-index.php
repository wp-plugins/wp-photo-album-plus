<?php
/* wppa-index.php
* Package: wp-photo-album-plus
*
* Contains all indexing functions
* version 5.3.10
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
	sort($array, SORT_NUMERIC);
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

// Remove an item from the index Use this function if you do NOT know the current photo data matches the index info
function wppa_index_remove( $type, $id ) {
global $wpdb;

	if ( $type == 'album' ) {
		$iam_big = ( $wpdb->get_var( "SELECT COUNT(*) FROM `".WPPA_ALBUMS."`" ) > '10000' );
		if ( $iam_big ) {
			// This is not strictly correct, the may be 24..28 when searching for 26, this will be missed. However this will not lead to problems during search.
			$indexes = $wpdb->get_results( "SELECT * FROM `".WPPA_INDEX."` WHERE `albums` LIKE '".$id."'", ARRAY_A );
		}
		else {
			// There are too many results on large systems, resulting in a 500 error, but it is strictly correct
			$indexes = $wpdb->get_results( "SELECT * FROM `".WPPA_INDEX."` WHERE `albums` <> ''", ARRAY_A );
		}
		if ( $indexes ) foreach ( $indexes as $indexline ) {
			$array = wppa_index_string_to_array($indexline['albums']);
			foreach ( array_keys($array) as $k ) {
				if ( $array[$k] == $id ) {
					unset ( $array[$k] );
					$string = wppa_index_array_to_string($array);
					$wpdb->query("UPDATE `".WPPA_INDEX."` SET `albums` = '".$string."' WHERE `id` = ".$indexline['id']);
				}
			}
		}
	}
	elseif ( $type == 'photo' ) {
		$iam_big = ( $wpdb->get_var( "SELECT COUNT(*) FROM `".WPPA_PHOTOS."`" ) > '10000' );
		if ( $iam_big ) {
			// This is not strictly correct, the may be 24..28 when searching for 26, this will be missed. However this will not lead to problems during search.
			$indexes = $wpdb->get_results( "SELECT * FROM `".WPPA_INDEX."` WHERE `photos` LIKE '%".$id."%'", ARRAY_A );
		}
		else {
			$indexes = $wpdb->get_results( "SELECT * FROM `".WPPA_INDEX."` WHERE `photos` <> ''", ARRAY_A );
			// There are too many results on large systems, resulting in a 500 error, but it is strictly correct
		}
		if ( $indexes ) foreach ( $indexes as $indexline ) {
			$array = wppa_index_string_to_array($indexline['photos']);
			foreach ( array_keys($array) as $k ) {
				if ( $array[$k] == $id ) {
					unset ( $array[$k] );
					$string = wppa_index_array_to_string($array);
					$wpdb->query("UPDATE `".WPPA_INDEX."` SET `photos` = '".$string."' WHERE `id` = ".$indexline['id']);
				}
			}
		}
	}
	else wppa_dbg_msg('Error, unimplemented type in wppa_index_remove().', 'red', 'force');
	$wpdb->query("DELETE FROM `".WPPA_INDEX."` WHERE `albums` = '' AND `photos` = ''");
}

// Use this function if you know the current photo data matches the index info
function wppa_index_quick_remove($type, $id) {
global $thumb;
global $wpdb;
global $album;

	if ( $type == 'album' ) {
		if ( is_numeric($id) ) wppa_cache_album($id);
		
		$words = __( $album['name'] ).' '.wppa_get_album_desc( $album['id'] ).' '.$album['cats'];
		$words = wppa_index_raw_to_words($words);
		
		foreach ( $words as $word ) {
			$indexline = $wpdb->get_row("SELECT * FROM `".WPPA_INDEX."` WHERE `slug` = '".$word."'", ARRAY_A);
			$array = wppa_index_string_to_array($indexline['albums']);
			foreach ( array_keys($array) as $k ) {
				if ( $array[$k] == $id ) {
					unset ( $array[$k] );
					$string = wppa_index_array_to_string($array);
					if ( $string || $indexline['photos'] ) {
						$wpdb->query("UPDATE `".WPPA_INDEX."` SET `albums` = '".$string."' WHERE `id` = ".$indexline['id']);
					}
					else {
						$wpdb->query("DELETE FROM `".WPPA_INDEX."` WHERE `id` = ".$indexline['id']);
					}
				}
			}
		}
		
	}
	elseif ( $type == 'photo') {
		if ( is_numeric($id) ) wppa_cache_thumb($id);

		// Find the raw text
		$words = __( $thumb['name'] ).' '.$thumb['filename'].' '.wppa_get_photo_desc( $thumb['id'] ).' '.$thumb['tags'];
		$coms = $wpdb->get_results($wpdb->prepare( "SELECT `comment` FROM `" . WPPA_COMMENTS . "` WHERE `photo` = %s AND `status` = 'approved'", $thumb['id'] ), ARRAY_A );
		if ( $coms ) foreach ( $coms as $com ) {
			$words .= ' '.stripslashes( $com['comment'] );
		}
		$words = wppa_index_raw_to_words($words, 'noskips');
		
		foreach ( $words as $word ) {
			$indexline = $wpdb->get_row("SELECT * FROM `".WPPA_INDEX."` WHERE `slug` = '".$word."'", ARRAY_A);
			$array = wppa_index_string_to_array($indexline['photos']);
			foreach ( array_keys($array) as $k ) {
				if ( $array[$k] == $id ) {
					unset ( $array[$k] );
					$string = wppa_index_array_to_string($array);
					if ( $string || $indexline['albums'] ) {
						$wpdb->query("UPDATE `".WPPA_INDEX."` SET `photos` = '".$string."' WHERE `id` = ".$indexline['id']);
					}
					else {
						$wpdb->query("DELETE FROM `".WPPA_INDEX."` WHERE `id` = ".$indexline['id']);
					}
				}
			}
		}
	}
}

// Re-index an edited item
function wppa_index_update($type, $id) {
	wppa_index_remove($type, $id);
	wppa_index_add($type, $id);
}

// The words in the new photo description should be left out
function wppa_index_compute_skips() {
global $wppa_opt;

	$user_skips = '';
	$words = wppa_index_raw_to_words($wppa_opt['wppa_newphoto_description'].' '.$user_skips, 'noskips');
	sort($words);

	$result = array();
	$last = '';
	foreach ( $words as $word ) {	// Remove dups
		if ( $word != $last ) {
			$result[] = $word;
			$last = $word;
		}
	}
	update_option('wppa_index_skips', $result);
}
