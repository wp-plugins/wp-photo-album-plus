<?php
/* wppa-ajax.php
*
* Functions used in ajax requests
* version 4.2.6
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
			
			// Check on validity
			if ( ! wp_verify_nonce($nonce, 'wppa-check') ) {
				echo '0;100;Security failure';
				exit;																// Nonce check failed
			}
			if ( ! in_array($rating, array('1', '2', '3', '4', '5')) ) {
				echo '0;101;Invalid input';
				exit;																// Value out of range
			}
			
			// Get other data
			$user     = wppa_get_user();
			$mylast   = $wpdb->get_var($wpdb->prepare( 'SELECT * FROM `'.WPPA_RATING.'` WHERE `photo` = %s AND `user` = %s ORDER BY `id` DESC LIMIT 1', $photo, $user ) ); 
			$myavgrat = '0';														// Init
			
			// Case 1: This is my first vote for this photo
			if ( ! $mylast ) {
				$key = wppa_nextkey(WPPA_RATING);
				$iret = $wpdb->query($wpdb->prepare('INSERT INTO `'.WPPA_RATING. '` (`id`, `photo`, `value`, `user`) VALUES (%s, %s, %s, %s)', $key, $photo, $rating, $user));
				if (!$iret) {
					echo '0;102;Can not insert rating';
					exit;															// Fail on storing vote
				}
				$myavgrat = $rating;
			}
			// Case 2: I will change my previously given vote
			elseif ( $wppa_opt['wppa_rating_change'] == 'yes' ) {					// Votechanging is allowed
				$query = $wpdb->prepare( 'UPDATE `'.WPPA_RATING.'` SET `value` = %s WHERE `photo` = %s AND `user` = %s LIMIT 1', $rating, $photo, $user );
				$iret = $wpdb->query($query);
				if (!$iret) {
					echo '0;103;Can not update rating';
					exit;															// Fail on update
				}
				$myavgrat = $rating;
			}
			// Case 3: Add another vote from me
			elseif ( $wppa_opt['wppa_rating_multi'] == 'yes' ) {					// Rating multi is allowed
				$key = wppa_nextkey(WPPA_RATING);
				$query = $wpdb->prepare( 'INSERT INTO `'.WPPA_RATING. '` (`id`, `photo`, `value`, `user`) VALUES (%s, %s, %s, %s)', $key, $photo, $rating, $user );
				$iret = $wpdb->query($query);
				if (!$iret) {
					echo '0;104;Can not add rating';
					exit;															// Fail on storing vote
				}
				// Compute my avg rating
				$query = $wpdb->prepare( 'SELECT * FROM `'.WPPA_RATING.'`  WHERE `photo` = %s AND `user` = %s', $photo, $user );
				$myrats = $wpdb->get_results($query, 'ARRAY_A');
				if ( ! $myrats) {
					echo '0;105;Can not read ratings';
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

			// Compute new allavgrat
			$ratings = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.WPPA_RATING.' WHERE photo = %s', $photo), 'ARRAY_A');
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
			
			// Store it in the photo info
			$query = $wpdb->prepare('UPDATE `'.WPPA_PHOTOS. '` SET `mean_rating` = %s WHERE `id` = %s LIMIT 1', $allavgrat, $photo);
			$iret = $wpdb->query($query);
			if (!$iret) {
				echo '0;106;Can not save mean rating';
				exit;																// Fail on save
			}

			echo $occur.';'.$photo.';'.$index.';'.$myavgrat.';'.$allavgrat;
			break;
		default:
		die('-1');
	}
	exit;
}
