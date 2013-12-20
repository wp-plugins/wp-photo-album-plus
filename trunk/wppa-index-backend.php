<?php
/* wppa-index-backend.php
* Package: wp-photo-album-plus
*
* indexing admin functions
* version 5.2.5
*
* 
*/

// Index search maintenance modules
function wppa_remake_index() {
global $wpdb;
global $album;
global $thumb;
global $acount;
global $pcount;

	// Init
	update_option('wppa_index_need_remake', 'no');	// I'm doing it now...
	ini_set('output_buffering', '128');
	$lastalbum = get_option('wppa_last_index_albums', '-2');
	if ( ! is_numeric($lastalbum) ) die ('Unexpected error in wppa_remake_index # 1');
	$acount = '0';
	$lastphoto = get_option('wppa_last_index_photos', '-2');
	if ( ! is_numeric($lastphoto) ) die ('Unexpected error in wppa_remake_index # 2');
	$pcount = '0';
	
	
	// All over ?
	if ( $lastalbum == '-1' && $lastphoto == '-1' ) {	// Start all over
		$wpdb->query("TRUNCATE TABLE `".WPPA_INDEX."` ");
		delete_option('wppa_'.WPPA_INDEX.'_lastkey');
		wppa_index_compute_skips();
	}
	
	// Do the albums
	if ( $lastalbum != '-2' ) {
		wppa_ok_message('Starting Albums at id = '.($lastalbum > '0' ? $lastalbum + '1' : '1'));
		// dirty work
		$albums = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_ALBUMS."` WHERE `id` > %d ORDER BY `id`", $lastalbum), ARRAY_A );
		foreach ( $albums as $album ) {
			wppa_index_add('album');
			update_option('wppa_last_index_albums', $album['id']);
			if ( wppa_is_time_up($acount) ) return false;
		}
//		update_option('wppa_last_album_indexed', get_option('wppa_last_index_albums'));	// To possibly expand index
		update_option('wppa_last_index_albums', '-2'); // Mark as Done
		wppa_ok_message('Done with Albums');
	}
	
	// Do the photos
	if ( $lastphoto != '-2' ) {
		// dirty work	
		wppa_ok_message('Starting Photos at id = '.($lastphoto > '0' ? $lastphoto + '1' : '1'));
		// note : excl seps moet nog!!!
		$thumbs = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` > %d ORDER BY `id`", $lastphoto), ARRAY_A );
		foreach ( $thumbs as $thumb ) {
			wppa_index_add('photo');
			update_option('wppa_last_index_photos', $thumb['id']);
			if ( wppa_is_time_up($pcount) ) return false;
		}
//		update_option('wppa_last_photo_indexed', get_option('wppa_last_index_photos'));	// To possibly expand index
		update_option('wppa_last_index_photos', '-2'); 	// Mark as Done
		wppa_ok_message('Done with Photos');
		
		delete_option('wppa_indexing_user');			// Nobody
	}
	return true;	// Done!!	
}

// Remove an item from the index Use this function if you do NOT know the current photo data matches the index info
function wppa_index_remove($type, $id) {
global $wpdb;

	if ( $type == 'album' ) {
		$indexes = $wpdb->get_results("SELECT * FROM `".WPPA_INDEX."` WHERE `albums` <> ''", ARRAY_A);
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
	elseif ( $type == 'photo') {
		$indexes = $wpdb->get_results("SELECT * FROM `".WPPA_INDEX."` WHERE `photos` <> ''", ARRAY_A);
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

// List the content of the index table
function wppa_list_index() {
global $wpdb;

	$indexes = $wpdb->get_results("SELECT * FROM `".WPPA_INDEX."` ORDER BY `slug`", ARRAY_A);
	echo '
		<h2>List of Indexes</h2>
			<div style="float:left; clear:both; width:100%; height:400px; overflow:auto; background-color:#f1f1f1; border:1px solid #ddd;" >
				<table>
					<thead>
						<tr>
							<th><span style="float:left;" >Word</span></th>
							<th style="max-width:400px;" ><span style="float:left;" >Albums</span></th>
							<th><span style="float:left;" >Photos</span></th>
						</tr>
					</thead>
					<tbody>';
					
	foreach ( $indexes as $index ) {
		echo '
						<tr>
							<td>'.$index['slug'].'</td>
							<td style="max-width:400px; word-wrap: break-word;" >'.$index['albums'].'</td>
							<td>'.$index['photos'].'</td>
						</tr>';
	}
	
	echo '
					</tbody>
				</table>
			</div><div style="clear:both;"></div>
			';
}