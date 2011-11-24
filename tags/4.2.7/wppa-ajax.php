<?php
/* wppa-ajax.php
*
* Functions used in ajax requests
* version 4.2.7
*
*/
add_action('wp_ajax_wppa', 'wppa_ajax_callback');
add_action('wp_ajax_nopriv_wppa', 'wppa_ajax_callback');

function wppa_ajax_callback() {
global $wpdb;
global $wppa_opt;

	// ALTHOUGH WE ARE HERE AS FRONT END VISITOR, is_admin() is true. 
	// So, $wppa_opt switches are 'yes' or 'no' and not true or false.
	
	$wppa_action = $_REQUEST['wppa-action'];
	
	switch ($wppa_action) {
		case 'rate':
			// Get commandline args
			$photo  = $_REQUEST['wppa-rating-id'];
			$rating = $_REQUEST['wppa-rating'];
			$occur  = $_REQUEST['wppa-occur'];
			$index  = $_REQUEST['wppa-index'];
			$nonce  = $_REQUEST['wppa-nonce'];
			
			// Make errortext
			$errtxt = __('An error occurred while processing you rating request.', 'wppa');
			$errtxt .= "\n".__('You may refresh the page and try again.', 'wppa');
			$wartxt = __('Althoug an error occurred while processing your rating, your vote has been registered.', 'wppa');
			$wartxt .= "\n".__('However, this may not be reflected in the current pageview', 'wppa');
			
			// Check on validity
			if ( ! wp_verify_nonce($nonce, 'wppa-check') ) {
				echo '0;100;'.$errtxt;
				exit;																// Nonce check failed
			}
			if ( ! in_array($rating, array('1', '2', '3', '4', '5')) ) {
				echo '0;101;'.$errtxt;
				exit;																// Value out of range
			}
			
			// Get other data
			$user     = wppa_get_user();
			$mylast   = $wpdb->get_var($wpdb->prepare( 'SELECT * FROM `'.WPPA_RATING.'` WHERE `photo` = %s AND `user` = %s ORDER BY `id` DESC LIMIT 1', $photo, $user ) ); 
			$myavgrat = '0';														// Init
			
			// Case 0: Illegal second vote
			if ( $mylast && $wppa_opt['wppa_rating_change'] == 'no' && $wppa_opt['wppa_rating_multi'] == 'no' ) {
				echo '0;109;'.__('Illegal attempt to enter a second vote.', 'wppa');
				exit;
			}
			// Case 1: This is my first vote for this photo
			if ( ! $mylast ) {
				$key = wppa_nextkey(WPPA_RATING);
				$iret = $wpdb->query($wpdb->prepare('INSERT INTO `'.WPPA_RATING. '` (`id`, `photo`, `value`, `user`) VALUES (%s, %s, %s, %s)', $key, $photo, $rating, $user));
				if ( $iret === false ) {
					echo '0;102;'.$errtxt;
					exit;															// Fail on storing vote
				}
				$myavgrat = $rating;
			}
			// Case 2: I will change my previously given vote
			elseif ( $wppa_opt['wppa_rating_change'] == 'yes' ) {					// Votechanging is allowed
				$query = $wpdb->prepare( 'UPDATE `'.WPPA_RATING.'` SET `value` = %s WHERE `photo` = %s AND `user` = %s LIMIT 1', $rating, $photo, $user );
				$iret = $wpdb->query($query);
				if ( $iret === false ) {
					echo '0;103;'.$errtxt;
					exit;															// Fail on update
				}
				$myavgrat = $rating;
			}
			// Case 3: Add another vote from me
			elseif ( $wppa_opt['wppa_rating_multi'] == 'yes' ) {					// Rating multi is allowed
				$key = wppa_nextkey(WPPA_RATING);
				$query = $wpdb->prepare( 'INSERT INTO `'.WPPA_RATING. '` (`id`, `photo`, `value`, `user`) VALUES (%s, %s, %s, %s)', $key, $photo, $rating, $user );
				$iret = $wpdb->query($query);
				if ( $iret === false ) {
					echo '0;104;'.$errtxt;
					exit;															// Fail on storing vote
				}
				// Compute my avg rating
				$query = $wpdb->prepare( 'SELECT * FROM `'.WPPA_RATING.'`  WHERE `photo` = %s AND `user` = %s', $photo, $user );
				$myrats = $wpdb->get_results($query, 'ARRAY_A');
				if ( ! $myrats) {
					echo '0;105;'.$wartxt;
					exit;															// Fail on retrieve
				}
				$sum = 0;
				$cnt = 0;
				foreach ($myrats as $rt) {
					$sum += $rt['value'];
					$cnt ++;
				}
				if ($cnt > 0) $myavgrat = $sum/$cnt; else $myavgrat = '0';
			}
			else { 																	// Should never get here....
				echo '0;110;'.__('Unexpected error', 'wppa');
				exit;
			}

			// Find Olld avgrat
			$oldavgrat = $wpdb->get_var($wpdb->prepare('SELECT `mean_rating` FROM '.WPPA_PHOTOS.' WHERE `id` = %s', $photo));
			if ($oldavgrat === false) {
				echo '0;108;'.$wartxt;
				exit;																// Fail on read old avgrat
			}
			// Compute new allavgrat
			$ratings = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.WPPA_RATING.' WHERE `photo` = %s', $photo), 'ARRAY_A');
			if ($ratings) {
				$sum = 0;
				$cnt = 0;
				foreach ($ratings as $rt) {
					$sum += $rt['value'];
					$cnt ++;
				}
				if ($cnt > 0) $allavgrat = $sum/$cnt; else $allavgrat = '0';
			}
			else $allavgrat = '0';

			// Store it in the photo info if it has been changed
			if ( $oldavgrat != $allavgrat ) {
				$query = $wpdb->prepare('UPDATE `'.WPPA_PHOTOS. '` SET `mean_rating` = %s WHERE `id` = %s', $allavgrat, $photo);
				$iret = $wpdb->query($query);
				if ( $iret === false ) {
					echo '0;106;'.$wartxt;
					exit;																// Fail on save
				}
			}

			echo $occur.';'.$photo.';'.$index.';'.$myavgrat.';'.$allavgrat;
			break;
			
		case 'delete-photo':
			$photo = $_REQUEST['photo-id'];
			$nonce = $_REQUEST['wppa-nonce'];
			
			// Check validity
			if ( ! wp_verify_nonce($nonce, 'wppa_nonce_'.$photo) ) {
				echo '0;'.__('You do not have the rights to delete a photo', 'wppa').$nonce;
				exit;																// Nonce check failed
			}
			// Get file extension
			$ext = $wpdb->get_var($wpdb->prepare('SELECT `ext` FROM `'.WPPA_PHOTOS.'` WHERE `id` = %s', $photo)); 
			// Delete fullsize image
			$file = ABSPATH.'wp-content/uploads/wppa/'.$photo.'.'.$ext;
			if (file_exists($file)) unlink($file);
			// Delete thumbnail image
			$file = ABSPATH.'wp-content/uploads/wppa/thumbs/'.$photo.'.'.$ext;
			if (file_exists($file)) unlink($file);
			// Delete db entries
			$wpdb->query($wpdb->prepare('DELETE FROM `'.WPPA_PHOTOS.'` WHERE `id` = %s LIMIT 1', $photo));
			$wpdb->query($wpdb->prepare('DELETE FROM `'.WPPA_RATING.'` WHERE `photo` = %s', $photo));
			$wpdb->query($wpdb->prepare('DELETE FROM `'.WPPA_COMMENTS.'` WHERE `photo` = %s', $photo));
			echo '1;<span style="color:red" >'.sprintf(__('Photo %s has been deleted', 'wppa'), $photo).'</span>';
			break;

		case 'update-album':
			$album = $_REQUEST['album-id'];
			$nonce = $_REQUEST['wppa-nonce'];
			$item  = $_REQUEST['item'];
			$value = $_REQUEST['value'];
			
			// Check validity
			if ( ! wp_verify_nonce($nonce, 'wppa_nonce_'.$album) ) {
				echo '0;'.__('You do not have the rights to update album information', 'wppa').$nonce;
				exit;																// Nonce check failed
			}

			switch ($item) {
				case 'clear_ratings':
					$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `album` = %s', $album), 'ARRAY_A');
					if ($photos) foreach ($photos as $photo) {
						$iret1 = $wpdb->query($wpdb->prepare('DELETE FROM `'.WPPA_RATING.'` WHERE `photo` = %s', $photo['id']));
						$iret2 = $wpdb->query($wpdb->prepare('UPDATE `'.WPPA_PHOTOS.'` SET `mean_rating` = %s WHERE `id` = %s', '', $photo['id']));
					}
					if ($photos !== false && $iret1 !== false && $iret2 !== false) {
						echo '97;'.__('<b>Ratings cleared</b>', 'wppa').';'.__('No ratings for this photo.', 'wppa');
					}
					else {
						echo '1;'.__('An error occurred while clearing ratings', 'wppa');
					}
					exit;
					break;
				case 'name':
					$itemname = __('Name', 'wppa');
					break;
				case 'description':
					$itemname = __('Description', 'wppa');
					break;
				case 'a_order':
					$itemname = __('Album order #', 'wppa');
					break;
				case 'main_photo':
					$itemname = __('Cover photo', 'wppa');
					break;
				case 'a_parent':
					$itemname = __('Parent album', 'wppa');
					break;
				case 'p_order_by':
					$itemname = __('Photo order', 'wppa');
					break;
				case 'cover_linktype':
					$itemname = __('Link type', 'wppa');
					break;
				case 'cover_linkpage':
					$itemname = __('Link to', 'wppa');
					break;
				case 'owner':
					$itemname = __('Owner', 'wppa');
					break;
				default:
					$itemname = $item;
			}
			
			$iret = $wpdb->query($wpdb->prepare('UPDATE '.WPPA_ALBUMS.' SET `'.$item.'` = %s WHERE `id` = %s', $value, $album));
			if ($iret !== false ) {
				echo '0;'.sprintf(__('<b>%s</b> of album %s updated', 'wppa'), $itemname, $album);
			}
			else {
				echo '2;'.sprintf(__('An error occurred while trying to update <b>%s</b> of album %s', 'wppa'), $itemname, $album);
				echo '<br>'.__('Press CTRL+F5 and try again.', 'wppa');
			}
			exit;
			break;
			
		case 'update-photo':
			$photo = $_REQUEST['photo-id'];
			$nonce = $_REQUEST['wppa-nonce'];
			$item  = $_REQUEST['item'];
			$value = $_REQUEST['value'];
			
			// Check validity
			if ( ! wp_verify_nonce($nonce, 'wppa_nonce_'.$photo) ) {
				echo '0;'.__('You do not have the rights to update photo information', 'wppa').$nonce;
				exit;																// Nonce check failed
			}
			
			switch ($item) {
				case 'rotright':
				case 'rotleft':
					$angle = $item == 'rotleft' ? '90' : '270';
					$error = wppa_rotate($photo, $angle);
					$leftorright = $item == 'rotleft' ? __('left', 'wppa') : __('right', 'wppa');
					if ( ! $error ) {
						echo '0;'.sprintf(__('Photo %s rotated %s', 'wppa'), $photo, $leftorright);
					}
					else {
						echo $error.';'.sprintf(__('An error occurred while trying to rotate photo %s', 'wppa'), $photo);
					}
					exit;
					break;
					
				case 'moveto':
					$iret = $wpdb->query($wpdb->prepare('UPDATE '.WPPA_PHOTOS.' SET `album` = %s WHERE `id` = %s', $value, $photo));
					if ($iret !== false ) {
						echo '99;'.sprintf(__('Photo %s has been moved to album %s (%s)', 'wppa'), $photo, wppa_qtrans(wppa_get_album_name($value)), $value);
					}
					else {
						echo '3;'.sprintf(__('An error occurred while trying to move photo %s', 'wppa'), $photo);
					}
					exit;
					break;
					
				case 'copyto':
					$error = wppa_copy_photo($photo, $value);
					if ( ! $error ) {
						echo '0;'.sprintf(__('Photo %s copied to album %s (%s)', 'wppa'), $photo, wppa_qtrans(wppa_get_album_name($value)), $value);
					}
					else {
						echo '4;'.sprintf(__('An error occurred while trying to copy photo %s', 'wppa'), $photo);
						echo '<br>'.__('Press CTRL+F5 and try again.', 'wppa');
					}
					break;
					
				case 'name':
				case 'description':
				case 'p_order':
				case 'owner':
				case 'linkurl':
				case 'linktitle':
					switch ($item) {
						case 'name':
							$itemname = __('Name', 'wppa');
							break;
						case 'description':
							$itemname = __('Description', 'wppa');
							break;
						case 'p_order':
							$itemname = __('Photo order #', 'wppa');
							break;
						case 'owner':
							$itemname = __('Owner', 'wppa');
							break;
						case 'linkurl':
							$itemname = __('Link url', 'wppa');
							break;
						case 'linktitle':
							$itemname = __('Link title', 'wppa');
							break;
						default:
							$itemname = $item;
					}
					$iret = $wpdb->query($wpdb->prepare('UPDATE '.WPPA_PHOTOS.' SET `'.$item.'` = %s WHERE `id` = %s', $value, $photo));
					if ($iret !== false ) {
						echo '0;'.sprintf(__('<b>%s</b> of photo %s updated', 'wppa'), $itemname, $photo);
					}
					else {
						echo '2;'.sprintf(__('An error occurred while trying to update <b>%s</b> of photo %s', 'wppa'), $itemname, $photo);
						echo '<br>'.__('Press CTRL+F5 and try again.', 'wppa');
					}
					exit;
					break;

					
				default:
					echo '98;This update action is not implemented yet('.$item.')';
					exit;
			}
		
			break;
		default:
		die('-1');
	}
	exit;
}
