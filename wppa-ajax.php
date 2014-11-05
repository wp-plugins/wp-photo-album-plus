<?php
/* wppa-ajax.php
*
* Functions used in ajax requests
* version 5.4.18
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

add_action( 'wp_ajax_wppa', 'wppa_ajax_callback' );
add_action( 'wp_ajax_nopriv_wppa', 'wppa_ajax_callback' );

function wppa_ajax_callback() {
global $wpdb;
global $wppa_opt;
global $wppa;
global $wppa_session;

	$wppa['ajax']  = true;
	$wppa['error'] = '0';
	$wppa['out']   = '';
	if ( ! isset( $wppa_session['page'] ) ) $wppa_session['page'] = '0';
	else $wppa_session['page']--;
	if ( ! isset( $wppa_session['ajax'] ) ) $wppa_session['ajax'] = '1';
	else $wppa_session['ajax']++;

	// ALTHOUGH IF WE ARE HERE AS FRONT END VISITOR, is_admin() is true. 
	// So, $wppa_opt switches are 'yes' or 'no' and not true or false.
	// So, always use the function wppa_switch( $slug ) to test on a bool setting
	
	// Globally check query args to prevent php injection
	$wppa_args = array( 'album', 'photo', 'slide', 'cover', 'occur', 'woccur', 'searchstring', 'topten', 
						'lasten', 'comten', 'featen', 'single', 'photos-only', 'debug', 
						'relcount', 'upldr', 'owner', 'rootsearch' );
	foreach ( $_REQUEST as $arg ) {
		if ( in_array( str_replace( 'wppa-', '', $arg ), $wppa_args ) ) {
			if ( strpos( $arg, '<?' ) !== false ) die( 'Security check failure #91' );
			if ( strpos( $arg, '?>' ) !== false ) die( 'Security check failure #92' );
		}
	}

	wppa_vfy_arg( 'wppa-action', true );
	wppa_vfy_arg( 'photo-id' );
	wppa_vfy_arg( 'comment-id' );
	wppa_vfy_arg( 'moccur' );
	
	$wppa_action = $_REQUEST['wppa-action'];
	
	switch ( $wppa_action ) {
		case 'front-edit':
			if ( ! isset( $_REQUEST['photo-id'] ) ) die( 'Missing required argument' );
			$photo = $_REQUEST['photo-id'];
			$ok = false;
			if ( current_user_can( 'wppa_admin' ) ) $ok = true;
			if ( wppa_get_user() == wppa_get_photo_owner( $photo ) && ( current_user_can( 'wppa_upload' ) || ( is_user_logged_in() && wppa_switch( 'wppa_upload_edit' ) ) ) ) $ok = true;
			if ( ! $ok ) die( 'You do not have sufficient rights to do this' );
			require_once 'wppa-photo-admin-autosave.php';
			$wppa['front_edit'] = true;
			echo '	<div style="padding-bottom:4px;height:24px;" >
						<span style="color:#777;" >
							<i>'.
								__a( 'All modifications are instantly updated on the server. The <b style="color:#070" >Remark</b> field keeps you informed on the actions taken at the background.' ).
							'</i>
						</span>
						<input id="wppa-fe-exit" type="button" style="float:right;color:red;font-weight:bold;" onclick="window.opener.location.reload( true );window.close();" value="'.__a( 'Exit & Refresh' ).'" />
						<div id="wppa-fe-count" style="float:right;" ></div>
					</div><div style="clear:both;"></div>';
			wppa_album_photos( '', $photo );
			exit;
			break;
			
		case 'do-comment':
			// Correct the fact that this is a non-admin operation, if it is only
			if ( is_admin() ) {
				require_once 'wppa-non-admin.php';
			}
			
			$wppa['mocc'] 	= $_REQUEST['moccur'];
			$wppa['comment_photo'] 	= isset( $_REQUEST['photo-id'] ) ? $_REQUEST['photo-id'] : '0';
			$wppa['comment_id'] 	= isset( $_REQUEST['comment-edit'] ) ? $_REQUEST['comment-edit'] : '0';
			
			$comment_allowed = ( ! wppa_switch( 'wppa_comment_login' ) || is_user_logged_in() );
			if ( wppa_switch( 'wppa_show_comments' ) && $comment_allowed ) {
				if ( wppa_switch( 'wppa_search_comments' ) ) wppa_index_quick_remove( 'photo', $_REQUEST['photo-id'] );
				wppa_do_comment( $_REQUEST['photo-id'] );		// Process the comment
				if ( wppa_switch( 'wppa_search_comments' ) ) wppa_index_add( 'photo', $_REQUEST['photo-id'] );
			}
			$wppa['no_esc'] = true;
			echo wppa_comment_html( $_REQUEST['photo-id'], $comment_allowed );	// Retrieve the new commentbox content
			exit;
			break;
			
		case 'import':
			require_once 'wppa-upload.php';
			_wppa_page_import();
			exit;
			break;
			
		case 'approve':
			$iret = '0';
			
			if ( ! current_user_can( 'wppa_moderate' ) && ! current_user_can( 'wppa_comments' ) ) {
				echo __( 'You do not have the rights to moderate photos this way', 'wppa' );
				exit;
			}
			
			if ( isset( $_REQUEST['photo-id'] ) && current_user_can( 'wppa_moderate' ) ) {
				$iret = $wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_PHOTOS."` SET `status` = 'publish' WHERE `id` = %s", $_REQUEST['photo-id'] ) );
				wppa_flush_upldr_cache( 'photoid', $_REQUEST['photo-id'] );
				$alb = $wpdb->get_var( $wpdb->prepare( "SELECT `album` FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $_REQUEST['photo-id'] ) );
				wppa_flush_treecounts( $alb );
			}
			if ( isset( $_REQUEST['comment-id'] ) ) {
				$iret = $wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_COMMENTS."` SET `status` = 'approved' WHERE `id` = %s", $_REQUEST['comment-id'] ) );
			}
			if ( $iret ) {
				echo 'OK';
			}
			else {
				if ( isset( $_REQUEST['photo-id'] ) ) {
					if ( current_user_can( 'wppa_moderate' ) ) {
						echo sprintf( __( 'Failed to update stutus of photo %s', 'wppa' ), $_REQUEST['photo-id'] )."\n".__( 'Please refresh the page', 'wppa' );
					}
					else {
						echo __( 'Security check failure', 'wppa' );
					}
				}
				if ( isset( $_REQUEST['comment-id'] ) ) {
					echo sprintf( __( 'Failed to update stutus of comment %s', 'wppa' ), $_REQUEST['comment-id'] )."\n".__( 'Please refresh the page', 'wppa' );
				}
			}
			exit;
			
		case 'remove':
			if ( isset( $_REQUEST['photo-id'] ) ) {	// Remove photo
				if ( ( wppa_user_is( 'administrator' ) ) || ( wppa_get_user() == wppa_get_photo_owner( $_REQUEST['photo-id'] ) && wppa_switch( 'wppa_upload_edit' ) ) ) { // Frontend delete?
					wppa_delete_photo( $_REQUEST['photo-id'] );
					echo 'OK||'.__( 'Photo removed', 'wppa' );
					exit;
				}
			}
			if ( ! current_user_can( 'wppa_moderate' ) && ! current_user_can( 'wppa_comments' ) ) {
				echo __( 'You do not have the rights to moderate photos this way', 'wppa' );
				exit;
			}
			if ( isset( $_REQUEST['photo-id'] ) ) {	// Remove photo
				if ( ! current_user_can( 'wppa_moderate' ) ) {
					echo __( 'Security check failure', 'wppa' );
					exit;
				}
				wppa_delete_photo( $_REQUEST['photo-id'] );
				echo 'OK||'.__( 'Photo removed', 'wppa' );
				exit;
			}
			if ( isset( $_REQUEST['comment-id'] ) ) {	// Remove comment
				$iret = $wpdb->query( $wpdb->prepare( "DELETE FROM `".WPPA_COMMENTS."` WHERE `id`= %s", $_REQUEST['comment-id'] ) );
				if ( $iret ) echo 'OK||'.__( 'Comment removed', 'wppa' );
				else echo __( 'Could not remove comment', 'wppa' );
				exit;
			}
			echo __( 'Unexpected error', 'wppa' );
			exit;

		case 'downloadalbum':
			// Feature enabled?
			if ( ! wppa_switch( 'wppa_allow_download_album' ) ) {
				echo '||ER||'.__( 'This feature is not enabled on this website', 'wppa' );
				exit;
			}
			
			// Validate args
			$alb = $_REQUEST['album-id'];
			$photos = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `album` = %s AND ( ( `status` <> 'pending' AND `status` <> 'scheduled' ) OR owner = %s ) ".wppa_get_photo_order( $alb ), $alb, wppa_get_user() ), ARRAY_A );
			if ( ! $photos ) {
				echo '||ER||'.__( 'The album is empty', 'wppa' );
				exit;
			}
			
			// Remove obsolete files
			wppa_delete_obsolete_tempfiles();
			
			// Open zipfile
			if ( ! class_exists( 'ZipArchive' ) ) {
				echo '||ER||'.__( 'Unable to create zip archive', 'wppa' );
				exit;
			}
			$zipfilename = wppa_get_album_name( $alb );
			$zipfilename = wppa_sanitize_file_name( $zipfilename.'.zip' ); 				// Remove illegal chars
			$zipfilepath = WPPA_UPLOAD_PATH.'/temp/'.$zipfilename;
			$wppa_zip = new ZipArchive;
			$iret = $wppa_zip->open( $zipfilepath, 1 );
			if ( $iret !== true ) {
				echo '||ER||'.sprintf( __( 'Unable to create zip archive. code = %s', 'wppa' ), $iret );
				exit;
			}
			
			// Add photos to zip
			foreach ( $photos as $p ) {
				$id = $p['id'];
				if ( ! wppa_is_video( $id ) ) {
					$source = ( wppa_switch( 'wppa_download_album_source' ) && is_file( wppa_get_source_path( $id ) ) ) ? wppa_get_source_path( $id ) : wppa_get_photo_path( $id );
					if ( is_file( $source ) ) {
						$dest 	= $p['filename'] ? wppa_sanitize_file_name( $p['filename'] ) : wppa_sanitize_file_name( wppa_strip_ext( $p['name'] ).'.'.$p['ext'] );
						$iret = $wppa_zip->addFile( $source, $dest );
						// To prevent too may files open, and to have at least a file when there are too many photos, close and re-open
						$wppa_zip->close();
						$wppa_zip->open( $zipfilepath );
					}
				}
			}
			
			// Close zip and return
			$zipcount = $wppa_zip->numFiles;
			$wppa_zip->close();	
			
			// A zip is created
			$desturl = WPPA_UPLOAD_URL.'/temp/'.$zipfilename;
			echo $desturl.'||OK||';	
			if ( $zipcount != count( $photos ) ) echo sprintf( __( 'Only %s out of %s photos could be added to the zipfile', 'wppa' ), $zipcount, count( $photos ) );
			exit;
			break;
			
		case 'getalbumzipurl':
			$alb = $_REQUEST['album-id'];
			$zipfilename = wppa_get_album_name( $alb );
			$zipfilename = wppa_sanitize_file_name( $zipfilename.'.zip' ); 				// Remove illegal chars
			$zipfilepath = WPPA_UPLOAD_PATH.'/temp/'.$zipfilename;
			$zipfileurl  = WPPA_UPLOAD_URL.'/temp/'.$zipfilename;
			if ( is_file( $zipfilepath ) ) {
				echo $zipfileurl;
			}
			else {
				echo 'ER';
			}
			exit;
			break;			
			
		case 'makeorigname':
			$photo = $_REQUEST['photo-id'];
			$from = $_REQUEST['from'];
			if ( $from == 'fsname' ) {
				$type = $wppa_opt['wppa_art_monkey_link'];
			}
			elseif ( $from == 'popup' ) {
				$type = $wppa_opt['wppa_art_monkey_popup_link'];
			}
			else {
				echo '||7||'.__( 'Unknown source of request', 'wppa' );
				exit;
			}
			
			$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $photo ), ARRAY_A );
			
			if ( $data ) {	// The photo is supposed to exist
			
				// Make the name
				if ( $data['filename'] ) {
					$name = $data['filename'];
				}
				else {
					$name = __( $data['name'] );
				}
				$name = wppa_sanitize_file_name( $name ); 				// Remove illegal chars
				$name = preg_replace( '/\.[^.]*$/', '', $name );	// Remove file extension
				if ( strlen( $name ) == '0' ) {
					echo '||1||'.__( 'Empty filename', 'wppa' );
					exit;
				}
				
				// Make the file
				if ( wppa_switch( 'wppa_artmonkey_use_source' ) ) {
					if ( is_file ( wppa_get_source_path( $photo ) ) ) {
						$source = wppa_get_source_path( $photo );
					}
					else {
						$source = wppa_get_photo_path( $photo );
					}
				}
				else {
					$source = wppa_get_photo_path( $photo );
				}
				$dest 		= WPPA_UPLOAD_PATH.'/temp/'.$name.'.'.$data['ext'];
				$zipfile 	= WPPA_UPLOAD_PATH.'/temp/'.$name.'.zip';
				$tempdir 	= WPPA_UPLOAD_PATH.'/temp';
				if ( ! is_dir( $tempdir ) ) @ mkdir( $tempdir );
				if ( ! is_dir( $tempdir ) ) {
					echo '||2||'.__( 'Unable to create tempdir', 'wppa' );
					exit;
				}

				// Remove obsolete files
				wppa_delete_obsolete_tempfiles();
				
				// Make the files
				if ( $type == 'file' ) {
					copy( $source, $dest );
					$ext = $data['ext'];
				}
				elseif ( $type == 'zip' ) {
					if ( ! class_exists( 'ZipArchive' ) ) {
						echo '||8||'.__( 'Unable to create zip archive', 'wppa' );
						exit;
					}
					$ext = 'zip';
					$wppa_zip = new ZipArchive;
					$wppa_zip->open( $zipfile, 1 );
					$wppa_zip->addFile( $source, basename( $dest ) );
					$wppa_zip->close();						
				}
				else {
					echo '||6||'.__( 'Unknown type', 'wppa' );
					exit;
				}
				
				$desturl = WPPA_UPLOAD_URL.'/temp/'.$name.'.'.$ext;
				echo '||0||'.$desturl;	// No error: return url
				exit;
			}
			else {
				echo '||9||'.__( 'The photo does no longer exist', 'wppa' );
				exit;
			}
			exit;
			break;
			
		case 'tinymcedialog':
			$result = wppa_make_tinymce_dialog();
			echo $result;
			exit;
			break;
			
		case 'bumpviewcount':
			$nonce  = $_REQUEST['wppa-nonce'];
			if ( wp_verify_nonce( $nonce, 'wppa-check' ) ) {
				wppa_bump_viewcount( 'photo', $_REQUEST['wppa-photo'] );
			}
			else {
				_e( 'Security check failure', 'wppa' );
			}
			exit;
			break;
			
		case 'rate':
			// Get commandline args
			$photo  = $_REQUEST['wppa-rating-id'];
			$rating = $_REQUEST['wppa-rating'];
			$occur  = $_REQUEST['wppa-occur'];
			$index  = $_REQUEST['wppa-index'];
			$nonce  = $_REQUEST['wppa-nonce'];
			
			// Make errortext
			$errtxt = __( 'An error occurred while processing you rating request.', 'wppa' );
			$errtxt .= "\n".__( 'Maybe you opened the page too long ago to recognize you.', 'wppa' );
			$errtxt .= "\n".__( 'You may refresh the page and try again.', 'wppa' );
			$wartxt = __( 'Althoug an error occurred while processing your rating, your vote has been registered.', 'wppa' );
			$wartxt .= "\n".__( 'However, this may not be reflected in the current pageview', 'wppa' );
			
			// Check on validity
			if ( ! wp_verify_nonce( $nonce, 'wppa-check' ) ) {
				echo '0||100||'.$errtxt;
				exit;																// Nonce check failed
			}
			if ( $wppa_opt['wppa_rating_max'] == '1' && $rating != '1' ) {
				echo '0||106||'.$errtxt.':'.$rating;
				exit;																// Value out of range
			}
			elseif ( $wppa_opt['wppa_rating_max'] == '5' && ! in_array( $rating, array( '-1', '1', '2', '3', '4', '5' ) ) ) {
				echo '0||106||'.$errtxt.':'.$rating;
				exit;																// Value out of range
			}
			elseif ( $wppa_opt['wppa_rating_max'] == '10' && ! in_array( $rating, array( '-1', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10' ) ) ) {
				echo '0||106||'.$errtxt.':'.$rating;
				exit;																// Value out of range
			}
			
			// Get other data
			if ( ! file_exists( wppa_get_thumb_path( $photo ) ) ) {
				echo '0||999||'.__( 'Photo has been removed.', 'wppa' );
				exit;
			}
			$user     = wppa_get_user();
			$mylast   = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM `'.WPPA_RATING.'` WHERE `photo` = %s AND `user` = %s ORDER BY `id` DESC LIMIT 1', $photo, $user ), ARRAY_A ); 
			$myavgrat = '0';			// Init
			
			// Rate own photo?
			if ( wppa_get_photo_item( $photo, 'owner' ) == $user && ! wppa_switch( 'wppa_allow_owner_votes' ) ) {
				echo '0||900||'.__( 'Sorry, you can not rate your own photos', 'wppa' );
				exit;
			}
			
			// Already a pending one?
			$pending = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `".WPPA_RATING."` WHERE `photo` = %s AND `user` = %s AND `status` = %s", $photo, $user, 'pending' ) );
			
			// Has user motivated his vote?
			$hascommented = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `".WPPA_COMMENTS."` WHERE `photo` = %s AND `user` = %s", $photo, wppa_get_user( 'display' ) ) );

			if ( $pending ) {
				if ( ! $hascommented ) {
					echo '0||900||'.__( 'Please enter a comment.', 'wppa' );
					exit;
				}
				else {
					$wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_RATING."` SET `status` = 'publish' WHERE `photo` = %s AND `user` = %s", $photo, $user ) );
				}
			}

			if ( wppa_switch( 'wppa_vote_needs_comment' ) ) {
				$ratingstatus = $hascommented ? 'publish' : 'pending';
			}
			else {
				$ratingstatus = 'publish';
			}
		
			// When done, we have to echo $occur.'||'.$photo.'||'.$index.'||'.$myavgrat.'||'.$allavgrat.'||'.$discount.||.$hascommented.||.$message;
			// So we have to do: process rating and find new $myavgrat, $allavgrat and $discount ( $occur, $photo and $index are known )
			
			// Case 0: Illegal second vote. Frontend takes care of this, but a hacker could enter an ajaxlink manually
			if ( $mylast && ( 	// I did vote already
								( ! ( wppa_switch( 'wppa_rating_change' ) || wppa_switch( 'wppa_rating_multi' ) ) ) || 	// No rating change or rating multi
								( $mylast['value'] < '0' ) ||														// I did a dislike, can not modify
								( $mylast['value'] > '0' && $rating == '-1' )										// I did a rating, can not change into dislike
							 )
						 ) {
				echo '0||109||'.__( 'Security check failure.', 'wppa' );
				exit;
			}
			// Case 1: value = -1 this is a legal dislike vote
			if ( $rating == '-1' ) {
				// Add my dislike
				$iret = wppa_create_rating_entry( array( 'photo' => $photo, 'value' => $rating, 'user' => $user, 'status' => $ratingstatus ) );
				if ( ! $iret ) {
					echo '0||101||'.$errtxt;
					exit;															// Fail on storing vote
				}
				// Add points
				if( function_exists( 'cp_alterPoints' ) && is_user_logged_in() ) cp_alterPoints( cp_currentUser(), $wppa_opt['wppa_cp_points_rating'] );
				wppa_dislike_check( $photo );	// Check for email to be sent every .. dislikes
				if ( ! is_file( wppa_get_thumb_path( $photo ) ) ) {	// Photo is removed
					 echo $occur.'||'.$photo.'||'.$index.'||-1||-1|0||'.$wppa_opt['wppa_dislike_delete'];
					 exit;
				}
			}
			// Case 2: This is my first vote for this photo
			elseif ( ! $mylast ) {
				// Add my vote
				$iret = wppa_create_rating_entry( array( 'photo' => $photo, 'value' => $rating, 'user' => $user, 'status' => $ratingstatus ) );
				if ( ! $iret ) {
					echo '0||102||'.$errtxt;
					exit;															// Fail on storing vote
				}
				// Add points
				if( function_exists( 'cp_alterPoints' ) && is_user_logged_in() ) cp_alterPoints( cp_currentUser(), $wppa_opt['wppa_cp_points_rating'] );
			}
			// Case 3: I will change my previously given vote
			elseif ( wppa_switch( 'wppa_rating_change' ) ) {					// Votechanging is allowed
				$iret = $wpdb->query( $wpdb->prepare( 'UPDATE `'.WPPA_RATING.'` SET `value` = %s WHERE `photo` = %s AND `user` = %s LIMIT 1', $rating, $photo, $user ) );
				if ( $iret === false ) {
					echo '0||103||'.$errtxt;
					exit;															// Fail on update
				}
			}
			// Case 4: Add another vote from me
			elseif ( wppa_switch( 'wppa_rating_multi' ) ) {					// Rating multi is allowed
				$iret = wppa_create_rating_entry( array( 'photo' => $photo, 'value' => $rating, 'user' => $user, 'status' => $ratingstatus ) );
				if ( ! $iret ) {
					echo '0||104||'.$errtxt;
					exit;															// Fail on storing vote
				}
			}
			else { 																	// Should never get here....
				echo '0||110||'.__( 'Unexpected error', 'wppa' );
				exit;
			}

			// Compute my avg rating
			$myrats = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `'.WPPA_RATING.'`  WHERE `photo` = %s AND `user` = %s AND `status` = %s ', $photo, $user, 'publish' ), ARRAY_A );
			if ( $myrats ) {
				$sum = 0;
				$cnt = 0;
				foreach ( $myrats as $rat ) {
					if ( $rat['value'] == '-1' ) {
						$sum += $wppa_opt['wppa_dislike_value'];
					}
					else {
						$sum += $rat['value'];
					}
					$cnt ++;
				}
				$myavgrat = $sum/$cnt; 
				$i = $wppa_opt['wppa_rating_prec'];
				$j = $i + '1';
				$myavgrat = sprintf( '%'.$j.'.'.$i.'f', $myavgrat );
			}
			else {
				$myavgrat = '0';
			}
			
			// Compute new allavgrat
			$ratings = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.WPPA_RATING.' WHERE `photo` = %s AND `status` = %s', $photo, 'publish' ), ARRAY_A );
			if ( $ratings ) {
				$sum = 0;
				$cnt = 0;
				foreach ( $ratings as $rat ) {
					if ( $rat['value'] == '-1' ) {
						$sum += $wppa_opt['wppa_dislike_value'];
					}
					else {
						$sum += $rat['value'];
					}
					$cnt++;
				}
				$allavgrat = $sum/$cnt;
				if ( $allavgrat == '10' ) $allavgrat = '9.99999999';	// For sort order reasons text field
			}
			else $allavgrat = '0';

			// Store it in the photo info 
			$iret = $wpdb->query( $wpdb->prepare( 'UPDATE `'.WPPA_PHOTOS. '` SET `mean_rating` = %s WHERE `id` = %s', $allavgrat, $photo ) );
			if ( $iret === false ) {
				echo '0||106||'.$wartxt;
				exit;																// Fail on save
			}
			
			// Compute rating_count and store in the photo info
			$ratcount = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `".WPPA_RATING."` WHERE `photo` = %s", $photo ) );
			if ( $ratcount !== false ) {
				$iret = $wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_PHOTOS."` SET `rating_count` = %s WHERE `id` = %s", $ratcount, $photo ) );
				if ( $iret === false ) {
					echo '0||107||'.$wartxt;
					exit;																// Fail on save
				}
			}

			// Format $allavgrat for output
			$allavgratcombi = $allavgrat.'|'.$ratcount;

			// Compute dsilike count
			$discount = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `".WPPA_RATING."` WHERE `photo` = %s AND `value` = -1 AND `status` = %s", $photo, 'publish' ) );
			if ( $discount === false ) {
				echo '0||108||'.$wartxt;
				exit;																// Fail on save
			}

			// Test for possible medal
			wppa_test_for_medal( $photo );
			
			// Success!
			wppa_clear_cache();
			
			if ( wppa_switch( 'wppa_vote_needs_comment' ) && ! $hascommented ) {
				$message = __( "Please explain your vote in a comment.\nYour vote will be discarded if you don't.\n\nAfter completing your comment,\nyou can refresh the page to see\nyour vote became effective.", 'wppa' );
			}
			else {
				$message = '';
			}

			echo $occur.'||'.$photo.'||'.$index.'||'.$myavgrat.'||'.$allavgratcombi.'||'.$discount.'||'.$hascommented.'||'.$message;
			break;
		
		case 'render':	
			$tim_1 	= microtime( true );
			$nq_1 	= get_num_queries();
			
			// Correct the fact that this is a non-admin operation, if it is
			if ( is_admin() ) {
				require_once 'wppa-non-admin.php';
			}
			wppa_load_theme();
			// Register geo shortcode if google-maps-gpx-vieuwer is on board. GPX does it in wp_head(), what is not done in an ajax call
//			if ( function_exists( 'gmapv3' ) ) add_shortcode( 'map', 'gmapv3' );
			// Get the post we are working for
			if ( isset ( $_REQUEST['wppa-fromp'] ) ) {
				$p = $_REQUEST['wppa-fromp'];
				if ( wppa_is_int( $p ) ) {
					$GLOBALS['post'] = get_post( $p );
				}
			}
			// Render
			echo wppa_albums();
			
			$tim_2 	= microtime( true );
			$nq_2 	= get_num_queries();
			$mem 	= memory_get_peak_usage( true ) / 1024 / 1024;

			$msg 	= sprintf( 'WPPA Ajax render: db queries: WP:%d, WPPA+: %d in %4.2f seconds, using %4.2f MB memory max', $nq_1, $nq_2 - $nq_1, $tim_2 - $tim_1, $mem );
			echo '<script type="text/javascript">wppaConsoleLog( \''.$msg.'\', \'force\' )</script>';
			break;
			
		case 'delete-photo':
			$photo = $_REQUEST['photo-id'];
			$nonce = $_REQUEST['wppa-nonce'];
			
			// Check validity
			if ( ! wp_verify_nonce( $nonce, 'wppa_nonce_'.$photo ) ) {
				echo '||0||'.__( 'You do not have the rights to delete a photo', 'wppa' );
				exit;																// Nonce check failed
			}
			if ( ! is_numeric( $photo ) ) {
				echo '||0||'.__( 'Security check failure', 'wppa' );
				exit;																// Nonce check failed
			}
			$album = $wpdb->get_var( $wpdb->prepare( 'SELECT `album` FROM `'.WPPA_PHOTOS.'` WHERE `id` = %s', $photo ) );
			wppa_delete_photo( $photo );
			wppa_clear_cache();
			echo '||1||<span style="color:red" >'.sprintf( __( 'Photo %s has been deleted', 'wppa' ), $photo ).'</span>';
			echo '||';
			$a = wppa_allow_uploads( $album );
			if ( ! $a ) echo 'full';
			else echo 'notfull||'.$a;
			break;

		case 'update-album':
			$album = $_REQUEST['album-id'];
			$nonce = $_REQUEST['wppa-nonce'];
			$item  = $_REQUEST['item'];
			$value = $_REQUEST['value'];
			$value  = wppa_decode( $value );
			
			// Check validity
			if ( ! wp_verify_nonce( $nonce, 'wppa_nonce_'.$album ) ) {
				echo '||0||'.__( 'You do not have the rights to update album information', 'wppa' ).$nonce;
				exit;																// Nonce check failed
			}

			switch ( $item ) {
				case 'clear_ratings':
					$photos = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `album` = %s', $album ), ARRAY_A );
					if ( $photos ) foreach ( $photos as $photo ) {
						$iret1 = $wpdb->query( $wpdb->prepare( 'DELETE FROM `'.WPPA_RATING.'` WHERE `photo` = %s', $photo['id'] ) );
						$iret2 = $wpdb->query( $wpdb->prepare( 'UPDATE `'.WPPA_PHOTOS.'` SET `mean_rating` = %s WHERE `id` = %s', '', $photo['id'] ) );
					}
					if ( $photos && $iret1 !== false && $iret2 !== false ) {
						echo '||97||'.__( '<b>Ratings cleared</b>', 'wppa' ).'||'.__( 'No ratings for this photo.', 'wppa' );
					}
					elseif ( $photos ) {
						echo '||1||'.__( 'An error occurred while clearing ratings', 'wppa' );
					}
					else {
						echo '||97||'.__( '<b>No photos in this album</b>', 'wppa' ).'||'.__( 'No ratings for this photo.', 'wppa' );
					}
					exit;
					break;
				case 'set_deftags':	// to be changed for large albums
					$photos = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `album` = %s', $album ), ARRAY_A );
					$deftag = $wpdb->get_var( $wpdb->prepare( 'SELECT `default_tags` FROM `'.WPPA_ALBUMS.'` WHERE `id` = %s', $album ) );
					if ( is_array( $photos ) ) foreach ( $photos as $photo ) {
						$tags = wppa_sanitize_tags( str_replace( array( ' ', '\'', '"' ), ',', wppa_filter_iptc( wppa_filter_exif( $deftag, $photo['id'] ), $photo['id'] ) ) );
						$iret = $wpdb->query( $wpdb->prepare( 'UPDATE `'.WPPA_PHOTOS.'` SET `tags` = %s WHERE `id` = %s', $tags, $photo['id'] ) );
						wppa_index_update( 'photo', $photo['id'] );
					}
					if ( $photos && $iret !== false ) {
						echo '||97||'.__( '<b>Tags set to defaults</b> (reload)', 'wppa' );
					}
					elseif ( $photos ) {
						echo '||1||'.__( 'An error occurred while setting tags', 'wppa' );
					}
					else {
						echo '||97||'.__( '<b>No photos in this album</b>', 'wppa' );
					}
					wppa_clear_taglist();
					exit;
					break;
				case 'add_deftags':
					$photos = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `album` = %s', $album ), ARRAY_A );
					$deftag = $wpdb->get_var( $wpdb->prepare( 'SELECT `default_tags` FROM `'.WPPA_ALBUMS.'` WHERE `id` = %s', $album ) );
					if ( is_array( $photos ) ) foreach ( $photos as $photo ) {
						$tags = wppa_sanitize_tags( str_replace( array( ' ', '\'', '"' ), ',', wppa_filter_iptc( wppa_filter_exif( $photo['tags'].','.$deftag, $photo['id'] ), $photo['id'] ) ) );
						$iret = $wpdb->query( $wpdb->prepare( 'UPDATE `'.WPPA_PHOTOS.'` SET `tags` = %s WHERE `id` = %s', $tags, $photo['id'] ) );
						wppa_index_update( 'photo', $photo['id'] );
					}
					if ( $photos && $iret !== false ) {
						echo '||97||'.__( '<b>Tags added width defaults</b> (reload)', 'wppa' );
					}
					elseif ( $photos ) {
						echo '||1||'.__( 'An error occurred while adding tags', 'wppa' );
					}
					else {
						echo '||97||'.__( '<b>No photos in this album</b>', 'wppa' );
					}
					wppa_clear_taglist();
					exit;
					break;
				case 'name':
					$value = trim( strip_tags( $value ) );
					if ( ! wppa_sanitize_file_name( $value ) ) {	// Empty album name is not allowed
						$value = 'Album-#'.$album;
						echo '||5||' . sprintf( __( 'Album name may not be empty.<br />Reset to <b>%s</b>', 'wppa' ), $value );
					}
					$itemname = __( 'Name', 'wppa' );
					break;
				case 'description':
					$itemname = __( 'Description', 'wppa' );
					if ( wppa_switch( 'wppa_check_balance' ) ) {
						$value = str_replace( array( '<br/>','<br>' ), '<br />', $value );
						if ( balanceTags( $value, true ) != $value ) {
							echo '||3||'.__( 'Unbalanced tags in album description!', 'wppa' );
							exit;
						}
					}
					$value = trim( $value );
					break;
				case 'a_order':
					$itemname = __( 'Album order #', 'wppa' );
					break;
				case 'main_photo':
					$itemname = __( 'Cover photo', 'wppa' );
					break;
				case 'a_parent':
					$itemname = __( 'Parent album', 'wppa' );
					wppa_flush_treecounts( $album );	// Myself and my parents
					wppa_flush_treecounts( $value );	// My new parent
					break;
				case 'p_order_by':
					$itemname = __( 'Photo order', 'wppa' );
					break;
				case 'alt_thumbsize':
					$itemname = __( 'Use Alt thumbsize', 'wppa' );
					break;
				case 'cover_type':
					$itemname = __( 'Cover Type', 'wppa' );
					break;
				case 'cover_linktype':
					$itemname = __( 'Link type', 'wppa' );
					break;
				case 'cover_linkpage':
					$itemname = __( 'Link to', 'wppa' );
					break;
				case 'owner':
					$itemname = __( 'Owner', 'wppa' );
					if ( $value != '--- public ---' && ! get_user_by( 'login', $value ) ) {
						echo '||4||'.sprintf( __( 'User %s does not exist', 'wppa' ), $value );
						exit;
					}
					break;
				case 'upload_limit_count':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Upload limit count', 'wppa' ) );
					if ( $wppa['error'] ) exit;
					$oldval = $wpdb->get_var( $wpdb->prepare( 'SELECT `upload_limit` FROM '.WPPA_ALBUMS.' WHERE `id` = %s', $album ) );
					$temp = explode( '/', $oldval );
					$value = $value.'/'.$temp[1];
					$item = 'upload_limit';
					$itemname = __( 'Upload limit count', 'wppa' );
					break;
				case 'upload_limit_time':
					$oldval = $wpdb->get_var( $wpdb->prepare( 'SELECT `upload_limit` FROM '.WPPA_ALBUMS.' WHERE `id` = %s', $album ) );
					$temp = explode( '/', $oldval );
					$value = $temp[0].'/'.$value;
					$item = 'upload_limit';
					$itemname = __( 'Upload limit time', 'wppa' );
					break;
				case 'default_tags':
					$value = wppa_sanitize_tags( $value );
					$itemname = __( 'Default tags', 'wppa' );
					break;
				case 'cats':
					$value = wppa_sanitize_cats( $value );
					wppa_clear_catlist();
					$itemname = __( 'Categories', 'wppa' );
					break;
				case 'suba_order_by':
					$itemname = __( 'Sub albums sort order', 'wppa' );
					break;
	
				case 'year':
				case 'month':
				case 'day':
				case 'hour':
				case 'min':
					$itemname = __( 'Schedule date/time', 'wppa' );
					$scheduledtm = $wpdb->get_var( $wpdb->prepare( "SELECT `scheduledtm` FROM`".WPPA_ALBUMS."` WHERE `id` = %s", $album ) );
					if ( ! $scheduledtm ) {
						$scheduledtm = wppa_get_default_scheduledtm();
					}
					$temp = explode( ',', $scheduledtm );
					if ( $item == 'year' ) 	$temp[0] = $value;
					if ( $item == 'month' ) $temp[1] = $value; 
					if ( $item == 'day' ) 	$temp[2] = $value;
					if ( $item == 'hour' ) 	$temp[3] = $value;
					if ( $item == 'min' ) 	$temp[4] = $value;
					$scheduledtm = implode( ',', $temp );
					wppa_update_album( array( 'id' => $album, 'scheduledtm' => $scheduledtm ) );
					echo '||0||'.sprintf( __( '<b>%s</b> of album %s updated', 'wppa' ), $itemname, $album );
					exit;
					break;
					
				case 'setallscheduled':
					$scheduledtm = $wpdb->get_var( $wpdb->prepare( "SELECT `scheduledtm` FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $album ) );
					if ( $scheduledtm ) {
						$iret = $wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_PHOTOS."` SET `status` = 'scheduled', `scheduledtm` = %s WHERE `album` = %s", $scheduledtm, $album ) );
						echo '||0||'.__( 'All photos set to scheduled per date', 'wppa' ).' ( '.$iret.' ) '.wppa_format_scheduledtm( $scheduledtm );
					}
					exit;
					break;
					
				default:
					$itemname = $item;
			}
			
			$query = $wpdb->prepare( 'UPDATE '.WPPA_ALBUMS.' SET `'.$item.'` = %s WHERE `id` = %s', $value, $album );
			$iret = $wpdb->query( $query );
			if ( $iret !== false ) {
				if ( $item == 'name' || $item == 'description' || $item == 'cats' ) {
					wppa_index_update( 'album', $album );
				}
				if ( $item == 'name' ) {
					wppa_create_pl_htaccess();
				}
				echo '||0||'.sprintf( __( '<b>%s</b> of album %s updated', 'wppa' ), $itemname, $album );
				if ( $item == 'upload_limit' ) {
					echo '||';
					$a = wppa_allow_uploads( $album );
					if ( ! $a ) echo 'full';
					else echo 'notfull||'.$a;
				}
			}
			else {
				echo '||2||'.sprintf( __( 'An error occurred while trying to update <b>%s</b> of album %s', 'wppa' ), $itemname, $album );
				echo '<br>'.__( 'Press CTRL+F5 and try again.', 'wppa' );
			}
			wppa_clear_cache();
			exit;
			break;
		
		case 'update-comment-status':
			$photo = $_REQUEST['wppa-photo-id'];
			$nonce = $_REQUEST['wppa-nonce'];
			$comid = $_REQUEST['wppa-comment-id'];
			$comstat = $_REQUEST['wppa-comment-status'];
			
			// Check validity
			if ( ! wp_verify_nonce( $nonce, 'wppa_nonce_'.$photo ) ) {
				echo '||0||'.__( 'You do not have the rights to update comment status', 'wppa' ).$nonce;
				exit;																// Nonce check failed
			}

			if ( wppa_switch( 'wppa_search_comments' ) ) wppa_index_quick_remove( 'photo', $photo );
			$iret = $wpdb->query( $wpdb->prepare( 'UPDATE `'.WPPA_COMMENTS.'` SET `status` = %s WHERE `id` = %s', $comstat, $comid ) );
			if ( wppa_switch( 'wppa_search_comments' ) ) wppa_index_add( 'photo', $photo );
			
			if ( $iret !== false ) {
				echo '||0||'.sprintf( __( 'Status of comment #%s updated', 'wppa' ), $comid );
			}
			else {
				echo '||1||'.sprintf( __( 'Error updating status comment #%s', 'wppa' ), $comid );
			}
			exit;
			break;
			
		case 'watermark-photo':
			$photo = $_REQUEST['photo-id'];
			$nonce = $_REQUEST['wppa-nonce'];
		
			// Check validity
			if ( ! wp_verify_nonce( $nonce, 'wppa_nonce_'.$photo ) ) {
				echo '||1||'.__( 'You do not have the rights to change photos', 'wppa' );
				exit;																// Nonce check failed
			}
			
			$ext = $wpdb->get_var( $wpdb->prepare( "SELECT `ext` FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $photo ) );
			
			wppa_cache_thumb( $photo );
			if ( wppa_add_watermark( $photo ) ) {
				if ( wppa_switch( 'wppa_watermark_thumbs' ) ) {
					wppa_create_thumbnail( wppa_get_photo_path( $photo ), wppa_get_minisize(), '' );	// create new thumb
				}
				echo '||0||'.__( 'Watermark applied', 'wppa' );
				exit;
			}
			else {
				echo '||1||'.__( 'An error occured while trying to apply a watermark', 'wppa' );
				exit;
			}

		case 'update-photo':
			$photo = $_REQUEST['photo-id'];
			$nonce = $_REQUEST['wppa-nonce'];
			$item  = $_REQUEST['item'];
			$value = $_REQUEST['value'];
			$value  = wppa_decode( $value );

			// Check validity
			if ( ! wp_verify_nonce( $nonce, 'wppa_nonce_'.$photo ) ) {
				echo '||0||'.__( 'You do not have the rights to update photo information', 'wppa' );
				exit;																// Nonce check failed
			}
			
			if ( substr( $item, 0, 20 ) == 'wppa_watermark_file_' || substr( $item, 0, 19 ) == 'wppa_watermark_pos_' ) {
				wppa_update_option( $item, $value );
				echo '||0||'.sprintf( __( '%s updated to %s.', 'wppa' ), $item, $value );
				exit;
			}
			
			switch ( $item ) {
				case 'lat':
					if ( ! is_numeric( $value ) || $value < '-90.0' || $value > '90.0' ) {
						echo '||1||'.__( 'Enter a value > -90 and < 90', 'wppa' );
						exit;
					}					
					$photodata = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM '.WPPA_PHOTOS.' WHERE `id` = %s', $photo ), ARRAY_A );
					$geo = $photodata['location'] ? $photodata['location'] : '///';
					$geo = explode( '/', $geo );
					$geo = wppa_format_geo( $value, $geo['3'] );
					$iret = $wpdb->query( $wpdb->prepare( 'UPDATE `'.WPPA_PHOTOS.'` SET `location` = %s WHERE `id` = %s', $geo, $photo ) );
					if ( $iret ) echo '||0||'.__( 'Lattitude updated', 'wppa' );
					else {
						echo '||1||'.__( 'Could not update lattitude', 'wppa' );
					}
					exit;
					break;
				case 'lon':
					if ( ! is_numeric( $value ) || $value < '-180.0' || $value > '180.0' ) {
						echo '||1||'.__( 'Enter a value > -180 and < 180', 'wppa' );
						exit;
					}					
					$photodata = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM '.WPPA_PHOTOS.' WHERE `id` = %s', $photo ), ARRAY_A );
					$geo = $photodata['location'] ? $photodata['location'] : '///';
					$geo = explode( '/', $geo );
					$geo = wppa_format_geo( $geo['2'], $value );
					$iret = $wpdb->query( $wpdb->prepare( 'UPDATE `'.WPPA_PHOTOS.'` SET `location` = %s WHERE `id` = %s', $geo, $photo ) );
					if ( $iret ) echo '||0||'.__( 'Longitude updated', 'wppa' );
					else {
						echo '||1||'.__( 'Could not update longitude', 'wppa' );
					}
					exit;
					break;
				case 'remake':
					if ( wppa_remake_files( '', $photo ) ) {
						wppa_bump_photo_rev();
						wppa_bump_thumb_rev();
						echo '||0||'.__( 'Photo files remade', 'wppa' );
					}
					else {
						echo '||2||'.__( 'Could not remake files', 'wppa' );
					}
					exit;
					break;
				case 'rotright':
				case 'rot180':
				case 'rotleft':
					switch ( $item ) {
						case 'rotleft':
							$angle = '90';
							$dir = __( 'left', 'wppa' );
							break;
						case 'rot180':
							$angle = '180';
							$dir = __( '180&deg;', 'wppa' );
							break;
						case 'rotright':
							$angle = '270';
							$dir = __( 'right', 'wppa' );
							break;
					}
					$wppa['error'] = wppa_rotate( $photo, $angle );
					if ( ! $wppa['error'] ) {
						wppa_update_modified( $photo );
						wppa_bump_photo_rev();
						wppa_bump_thumb_rev();
						echo '||0||'.sprintf( __( 'Photo %s rotated %s', 'wppa' ), $photo, $dir );
					}
					else {
						echo '||'.$wppa['error'].'||'.sprintf( __( 'An error occurred while trying to rotate photo %s', 'wppa' ), $photo );
					}
					exit;
					break;
					
				case 'moveto':
					$photodata = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM '.WPPA_PHOTOS.' WHERE `id` = %s', $photo ), ARRAY_A );
					if ( wppa_switch( 'wppa_void_dups' ) ) {	// Check for already exists
						$exists = wppa_file_is_in_album( $photodata['filename'], $value );
						if ( $exists ) {	// Already exists
							echo '||3||' . sprintf ( __( 'A photo with filename %s already exists in album %s.', 'wppa' ), $photodata['filename'], $value );
							exit;
							break;
						}
					}
					wppa_flush_treecounts( $photodata['album'] );	// Current album
					wppa_flush_treecounts( $value );				// New album
					$iret = $wpdb->query( $wpdb->prepare( 'UPDATE '.WPPA_PHOTOS.' SET `album` = %s WHERE `id` = %s', $value, $photo ) );
					if ( $iret !== false ) {
						wppa_move_source( $photodata['filename'], $photodata['album'], $value );
						echo '||99||'.sprintf( __( 'Photo %s has been moved to album %s (%s)', 'wppa' ), $photo, wppa_get_album_name( $value ), $value );
					}
					else {
						echo '||3||'.sprintf( __( 'An error occurred while trying to move photo %s', 'wppa' ), $photo );
					}
					exit;
					break;
					
				case 'copyto':
					$photodata = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM '.WPPA_PHOTOS.' WHERE `id` = %s', $photo ), ARRAY_A );
					if ( wppa_switch( 'wppa_void_dups' ) ) {	// Check for already exists
						$exists = wppa_file_is_in_album( $photodata['filename'], $value );
						if ( $exists ) {	// Already exists
							echo '||4||' . sprintf ( __( 'A photo with filename %s already exists in album %s.', 'wppa' ), $photodata['filename'], $value );
							exit;
							break;
						}
					}
					$wppa['error'] = wppa_copy_photo( $photo, $value );
					wppa_flush_treecounts( $value );				// New album
					if ( ! $wppa['error'] ) {
						echo '||0||'.sprintf( __( 'Photo %s copied to album %s (%s)', 'wppa' ), $photo, wppa_get_album_name( $value ), $value );
					}
					else {
						echo '||4||'.sprintf( __( 'An error occurred while trying to copy photo %s', 'wppa' ), $photo );
						echo '<br>'.__( 'Press CTRL+F5 and try again.', 'wppa' );
					}
					exit;
					break;
					
				case 'status':
				if ( ! current_user_can( 'wppa_moderate' ) ) die( 'Security check failure #78' );
					wppa_flush_treecounts( wppa_get_photo_item( $photo, 'album' ) ); // $wpdb->get_var( $wpdb->prepare( "SELECT `album` FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $photo ) ) );
				case 'owner':
				case 'name':
				case 'description':
				case 'p_order':
				case 'linkurl':
				case 'linktitle':
				case 'linktarget':
				case 'tags':
				case 'alt':
				case 'videox':
				case 'videoy':
					switch ( $item ) {
						case 'name':
							$value = strip_tags( $value );
							$itemname = __( 'Name', 'wppa' );
							break;
						case 'description':
							$itemname = __( 'Description', 'wppa' );
							if ( wppa_switch( 'wppa_check_balance' ) ) {
								$value = str_replace( array( '<br/>','<br>' ), '<br />', $value );
								if ( balanceTags( $value, true ) != $value ) {
									echo '||3||'.__( 'Unbalanced tags in photo description!', 'wppa' );
									exit;
								}
							}
							break;
						case 'p_order':
							$itemname = __( 'Photo order #', 'wppa' );
							break;
						case 'owner':
							$usr = get_user_by( 'login', $value );
							if ( ! $usr ) {
								echo '||4||' . sprintf( __( 'User %s does not exists', 'wppa' ), $value );
								exit;
							}
							$value = $usr->user_login;	// Correct possible case mismatch
							wppa_flush_upldr_cache( 'photoid', $photo ); 		// Current owner
							wppa_flush_upldr_cache( 'username', $value );		// New owner
							$itemname = __( 'Owner', 'wppa' );
							break;
						case 'linkurl':
							$itemname = __( 'Link url', 'wppa' );
							break;
						case 'linktitle':
							$itemname = __( 'Link title', 'wppa' );
							break;
						case 'linktarget':
							$itemname = __( 'Link target', 'wppa' );
							break;
						case 'tags':
							$value = wppa_sanitize_tags( wppa_filter_iptc( wppa_filter_exif( $value, $photo ), $photo ) );
							wppa_clear_taglist();
							$itemname = __( 'Photo Tags', 'wppa' );
							break;
						case 'status':
							wppa_flush_upldr_cache( 'photoid', $photo );
							$itemname = __( 'Status', 'wppa' );
							break;
						case 'alt':
							$itemname = __( 'HTML Alt', 'wppa' );
							$value = strip_tags( stripslashes( $value ) );
							break;
						case 'videox':
							$itemname = __( 'Video width', 'wppa' );
							if ( ! wppa_is_int( $value ) || $value < '0' ) {
								echo '||3||'.__( 'Please enter an integer value >= 0', 'wppa' );
								exit;
							}
							break;
						case 'videoy':
							$itemname = __( 'Video height', 'wppa' );
							if ( ! wppa_is_int( $value ) || $value < '0' ) {
								echo '||3||'.__( 'Please enter an integer value >= 0', 'wppa' );
								exit;
							}
							break;
						default:
							$itemname = $item;
					}
					if ( $item == 'name' || $item == 'description' || $item == 'tags' ) wppa_index_quick_remove( 'photo', $photo );
					$iret = $wpdb->query( $wpdb->prepare( 'UPDATE '.WPPA_PHOTOS.' SET `'.$item.'` = %s WHERE `id` = %s', $value, $photo ) );
					if ( $item == 'name' || $item == 'description' || $item == 'tags' ) wppa_index_add( 'photo', $photo );
					if ( $item == 'status' && $value != 'scheduled' ) wppa_update_photo( array( 'id' => $photo, 'scheduledtm' => '' ) );
					if ( $item == 'status' ) wppa_flush_treecounts( wppa_get_photo_item( $photo, 'album' ) );
					if ( $iret !== false ) {
						wppa_update_modified( $photo );
						if ( wppa_is_video( $photo ) ) {
							echo '||0||'.sprintf( __( '<b>%s</b> of video %s updated', 'wppa' ), $itemname, $photo );
						}
						else {
							echo '||0||'.sprintf( __( '<b>%s</b> of photo %s updated', 'wppa' ), $itemname, $photo );
						}
					}
					else {
						echo '||2||'.sprintf( __( 'An error occurred while trying to update <b>%s</b> of photo %s', 'wppa' ), $itemname, $photo );
						echo '<br>'.__( 'Press CTRL+F5 and try again.', 'wppa' );
						exit;
					}
					break;

				case 'year':
				case 'month':
				case 'day':
				case 'hour':
				case 'min':
					$itemname = __( 'Schedule date/time', 'wppa' );
					$scheduledtm = $wpdb->get_var( $wpdb->prepare( "SELECT `scheduledtm` FROM`".WPPA_PHOTOS."` WHERE `id` = %s", $photo ) );
					if ( ! $scheduledtm ) {
						$scheduledtm = wppa_get_default_scheduledtm();
					}
					$temp = explode( ',', $scheduledtm );
					if ( $item == 'year' ) 	$temp[0] = $value;
					if ( $item == 'month' ) $temp[1] = $value; 
					if ( $item == 'day' ) 	$temp[2] = $value;
					if ( $item == 'hour' ) 	$temp[3] = $value;
					if ( $item == 'min' ) 	$temp[4] = $value;
					$scheduledtm = implode( ',', $temp );
					wppa_update_photo( array( 'id' => $photo, 'scheduledtm' => $scheduledtm, 'status' => 'scheduled' ) );
					wppa_flush_treecounts( $wpdb->get_var( $wpdb->prepare( "SELECT `album` FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $photo ) ) );
					wppa_flush_upldr_cache( 'photoid', $photo );
					if ( wppa_is_video( $photo ) ) {
						echo '||0||'.sprintf( __( '<b>%s</b> of video %s updated', 'wppa' ), $itemname, $photo );
					}
					else {
						echo '||0||'.sprintf( __( '<b>%s</b> of photo %s updated', 'wppa' ), $itemname, $photo );
					}
					break;
					
				default:
					echo '||98||This update action is not implemented yet( '.$item.' )';
					exit;
			}
			wppa_clear_cache();
			break;
			
		// The wppa-settings page calls ajax with $wppa_action == 'update-option';
		case 'update-option':
			// Verify that we are legally here
			$nonce  = $_REQUEST['wppa-nonce'];
			if ( ! wp_verify_nonce( $nonce, 'wppa-nonce' ) ) {
				echo '||1||'.__( 'You do not have the rights to update settings', 'wppa' );
				exit;																// Nonce check failed
			}
			
			// Initialize
			$old_minisize = wppa_get_minisize();		// Remember for later, maybe we do something that requires regen
			$option = $_REQUEST['wppa-option'];			// The option to be processed
			$value  = isset( $_REQUEST['value'] ) ? wppa_decode( $_REQUEST['value'] ) : '';	// The new value, may also contain & # and +
			$value  = stripslashes( $value );
			$alert  = '';			// Init the return string data
			$wppa['error']  = '0';	//
			$title  = '';			//
			
			// If it is a font family, change all double quotes into single quotes as this destroys much more than you would like
			if ( strpos( $option, 'wppa_fontfamily_' ) !== false ) $value = str_replace( '"', "'", $value );
			
			$option = wppa_decode( $option );
			// Dispatch on option
			if ( substr( $option, 0, 16 ) == 'wppa_iptc_label_' ) {
				$tag = substr( $option, 16 );
				$q = $wpdb->prepare( "UPDATE `".WPPA_IPTC."` SET `description`=%s WHERE `tag`=%s AND `photo`='0'", $value, $tag );
				$bret = $wpdb->query( $q );
				// Produce the response text
				if ( $bret ) {
					$output = '||0||'.$tag.' updated to '.$value.'||';
				}
				else {
					$output = '||1||Failed to update '.$tag.'||';
				}
				echo $output;
				exit;
			}
			elseif ( substr( $option, 0, 17 ) == 'wppa_iptc_status_' ) {
				$tag = substr( $option, 17 );
				$q = $wpdb->prepare( "UPDATE `".WPPA_IPTC."` SET `status`=%s WHERE `tag`=%s AND `photo`='0'", $value, $tag );
				$bret = $wpdb->query( $q );
				// Produce the response text
				if ( $bret ) {
					$output = '||0||'.$tag.' updated to '.$value.'||';
				}
				else {
					$output = '||1||Failed to update '.$tag.'||';
				}
				echo $output;			
				exit;
			}
			elseif ( substr( $option, 0, 16 ) == 'wppa_exif_label_' ) {
				$tag = substr( $option, 16 );
				$q = $wpdb->prepare( "UPDATE `".WPPA_EXIF."` SET `description`=%s WHERE `tag`=%s AND `photo`='0'", $value, $tag );
				$bret = $wpdb->query( $q );
				// Produce the response text
				if ( $bret ) {
					$output = '||0||'.$tag.' updated to '.$value.'||';
				}
				else {
					$output = '||1||Failed to update '.$tag.'||';
				}
				echo $output;
				exit;
			}
			elseif ( substr( $option, 0, 17 ) == 'wppa_exif_status_' ) {
				$tag = substr( $option, 17 );
				$q = $wpdb->prepare( "UPDATE `".WPPA_EXIF."` SET `status`=%s WHERE `tag`=%s AND `photo`='0'", $value, $tag );
				$bret = $wpdb->query( $q );
				// Produce the response text
				if ( $bret ) {
					$output = '||0||'.$tag.' updated to '.$value.'||';
				}
				else {
					$output = '||1||Failed to update '.$tag.'||';
				}
				echo $output;			
				exit;
			}
			elseif ( substr( $option, 0, 5 ) == 'caps-' ) {	// Is capability setting
				global $wp_roles;
				//$R = new WP_Roles;
				$setting = explode( '-', $option );
				if ( $value == 'yes' ) {
					$wp_roles->add_cap( $setting[2], $setting[1] );
					echo '||0||'.__( 'Capability granted', 'wppa' ).'||';
					exit;
				}
				elseif ( $value == 'no' ) {
					$wp_roles->remove_cap( $setting[2], $setting[1] );
					echo '||0||'.__( 'Capability withdrawn', 'wppa' ).'||';
					exit;
				}
				else {
					echo '||1||Invalid value: '.$value.'||';
					exit;
				}
			}
			else switch ( $option ) {
					
				case 'wppa_colwidth': //	 ??	  fixed   low	high	title
					wppa_ajax_check_range( $value, 'auto', '100', false, __( 'Column width.', 'wppa' ) );
					break;
				case 'wppa_fullsize':
					wppa_ajax_check_range( $value, false, '100', false, __( 'Full size.', 'wppa' ) );
					break;
				case 'wppa_maxheight':
					wppa_ajax_check_range( $value, false, '100', false, __( 'Max height.', 'wppa' ) );
					break;
				case 'wppa_thumbsize':
					wppa_ajax_check_range( $value, false, '50', false, __( 'Thumbnail size.', 'wppa' ) );
					break;
				case 'wppa_tf_width':
					wppa_ajax_check_range( $value, false, '50', false, __( 'Thumbnail frame width', 'wppa' ) );
					break;
				case 'wppa_tf_height':
					wppa_ajax_check_range( $value, false, '50',false,  __( 'Thumbnail frame height', 'wppa' ) );
					break;
				case 'wppa_tn_margin':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Thumbnail Spacing', 'wppa' ) );
					break;
				case 'wppa_min_thumbs':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Photocount treshold.', 'wppa' ) );
					break;
				case 'wppa_thumb_page_size':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Thumb page size.', 'wppa' ) );
					break;
				case 'wppa_smallsize':
					wppa_ajax_check_range( $value, false, '50', false, __( 'Cover photo size.', 'wppa' ) );
					break;
				case 'wppa_album_page_size':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Album page size.', 'wppa' ) );
					break;
				case 'wppa_topten_count':
					wppa_ajax_check_range( $value, false, '2', false, __( 'Number of TopTen photos', 'wppa' ), '40' );
					break;
				case 'wppa_topten_size':
					wppa_ajax_check_range( $value, false, '32', false, __( 'Widget image thumbnail size', 'wppa' ), wppa_get_minisize() );
					break;
				case 'wppa_max_cover_width':
					wppa_ajax_check_range( $value, false, '150', false, __( 'Max Cover width', 'wppa' ) );
					break;
				case 'wppa_text_frame_height':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Minimal description height', 'wppa' ) );
					break;
				case 'wppa_cover_minheight':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Minimal cover height', 'wppa' ) );
					break;
				case 'wppa_head_and_text_frame_height':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Minimal text frame height', 'wppa' ) );
					break;
				case 'wppa_bwidth':
					wppa_ajax_check_range( $value, '', '0', false, __( 'Border width', 'wppa' ) );
					break;
				case 'wppa_bradius':
					wppa_ajax_check_range( $value, '', '0', false, __( 'Border radius', 'wppa' ) );
					break;
				case 'wppa_box_spacing':
					wppa_ajax_check_range( $value, '', '-20', '100', __( 'Box spacing', 'wppa' ) );
					break;
				case 'wppa_popupsize':				
					$floor = $wppa_opt['wppa_thumbsize'];
					$temp  = $wppa_opt['wppa_smallsize'];
					if ( $temp > $floor ) $floor = $temp;
					wppa_ajax_check_range( $value, false, $floor, $wppa_opt['wppa_fullsize'], __( 'Popup size', 'wppa' ) );
					break;
				case 'wppa_fullimage_border_width':
					wppa_ajax_check_range( $value, '', '0', false, __( 'Fullsize border width', 'wppa' ) );
					break;
				case 'wppa_lightbox_bordersize':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Lightbox Bordersize', 'wppa' ) );
					break;
				case 'wppa_comment_count':
					wppa_ajax_check_range( $value, false, '2', '40', __( 'Number of Comment widget entries', 'wppa' ) );
					break;
				case 'wppa_comment_size':
					wppa_ajax_check_range( $value, false, '32', wppa_get_minisize(), __( 'Comment Widget image thumbnail size', 'wppa' ), wppa_get_minisize() );
					break;
				case 'wppa_thumb_opacity':
					wppa_ajax_check_range( $value, false, '0', '100', __( 'Opacity.', 'wppa' ) );
					break;
				case 'wppa_cover_opacity':
					wppa_ajax_check_range( $value, false, '0', '100', __( 'Opacity.', 'wppa' ) );
					break;
				case 'wppa_star_opacity':
					wppa_ajax_check_range( $value, false, '0', '50', __( 'Opacity.', 'wppa' ) );
					break;
				case 'wppa_filter_priority':
					wppa_ajax_check_range( $value, false, '10', false, __( 'Filter priority', 'wppa' ) );
					break;
				case 'wppa_gravatar_size':
					wppa_ajax_check_range( $value, false, '10', '256', __( 'Avatar size', 'wppa' ) );
					break;
				case 'wppa_watermark_opacity':
					wppa_ajax_check_range( $value, false, '0', '100', __( 'Watermark opacity', 'wppa' ) );
					break;
				case 'wppa_watermark_opacity_text':
					wppa_ajax_check_range( $value, false, '0', '100', __( 'Watermark opacity', 'wppa' ) );
					break;
				case 'wppa_ovl_txt_lines':
					wppa_ajax_check_range( $value, 'auto', '0', '24', __( 'Number of text lines', 'wppa' ) );
					break;
				case 'wppa_ovl_opacity':
					wppa_ajax_check_range( $value, false, '0', '100', __( 'Overlay opacity', 'wppa' ) );
					break;
				case 'wppa_upload_limit_count':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Upload limit', 'wppa' ) );
					break;
				case 'wppa_dislike_mail_every':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Notify inappropriate', 'wppa' ) );
					break;
				case 'wppa_dislike_set_pending':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Dislike pending', 'wppa' ) );
					break;
				case 'wppa_dislike_delete':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Dislike delete', 'wppa' ) );
					break;
				case 'wppa_max_execution_time':
					wppa_ajax_check_range( $value, false, '0', '900', __( 'Max execution time', 'wppa' ) );
					break;
				case 'wppa_cp_points_comment':
				case 'wppa_cp_points_rating':
				case 'wppa_cp_points_upload':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Cube Points points', 'wppa' ) );
					break;
				case 'wppa_jpeg_quality':
					wppa_ajax_check_range( $value, false, '20', '100', __( 'JPG Image quality', 'wppa' ) );
					if ( $wppa_opt['wppa_cdn_service'] == 'cloudinary' && ! $wppa['out'] ) {
						wppa_delete_derived_from_cloudinary();
					}
					break;
				case 'wppa_imgfact_count':
					wppa_ajax_check_range( $value, false, '2', '24', __( 'Number of coverphotos', 'wppa' ) );
					break;
				case 'wppa_dislike_value':
					wppa_ajax_check_range( $value, false, '-10', '0', __( 'Dislike value', 'wppa' ) );
					break;
				case 'wppa_slideshow_pagesize':
					wppa_ajax_check_range( $value, false, '0', false, __( 'Slideshow pagesize', 'wppa' ) );
					break;
				case 'wppa_pagelinks_max':
					wppa_ajax_check_range( $value, false, '3', false, __( 'Max Pagelinks', 'wppa' ) );
					break;
				case 'wppa_rating_clear':
					$iret1 = $wpdb->query( 'TRUNCATE TABLE '.WPPA_RATING );
					$iret2 = $wpdb->query( 'UPDATE '.WPPA_PHOTOS.' SET mean_rating="0", rating_count="0" WHERE id > -1' );
					if ( $iret1 !== false && $iret2 !== false ) {
						delete_option( 'wppa_'.WPPA_RATING.'_lastkey' );
						$title = __( 'Ratings cleared', 'wppa' );
					}
					else {
						$title = __( 'Could not clear ratings', 'wppa' );
						$alert = $title;
						$wppa['error'] = '1';
					}
					break;
				case 'wppa_viewcount_clear':
					$iret = $wpdb->query( "UPDATE `".WPPA_PHOTOS."` SET `views` = '0'" ) &&
							$wpdb->query( "UPDATE `".WPPA_ALBUMS."` SET `views` = '0'" );
					if ( $iret !== false ) {
						$title = __( 'Viewcounts cleared', 'wppa' );
					}
					else {
						$title = __( 'Could not clear viewcounts', 'wppa' );
						$alert = $title;
						$wppa['error'] = '1';
					}
					break;

				case 'wppa_iptc_clear':
					$iret = $wpdb->query( 'TRUNCATE TABLE '.WPPA_IPTC );
					if ( $iret !== false ) {
						delete_option( 'wppa_'.WPPA_IPTC.'_lastkey' );
						$title = __( 'IPTC data cleared', 'wppa' );
						$alert = __( 'Refresh this page to clear table X', 'wppa' );
						update_option( 'wppa_index_need_remake', 'yes' );
					}
					else {
						$title = __( 'Could not clear IPTC data', 'wppa' );
						$alert = $title;
						$wppa['error'] = '1';
					}
					break;

				case 'wppa_exif_clear':
					$iret = $wpdb->query( 'TRUNCATE TABLE '.WPPA_EXIF );
					if ( $iret !== false ) {
						delete_option( 'wppa_'.WPPA_EXIF.'_lastkey' );
						$title = __( 'EXIF data cleared', 'wppa' );
						$alert = __( 'Refresh this page to clear table XI', 'wppa' );
						update_option( 'wppa_index_need_remake', 'yes' );
					}
					else {
						$title = __( 'Could not clear EXIF data', 'wppa' );
						$alert = $title;
						$wppa['error'] = '1';
					}
					break;
					
				case 'wppa_recup':
					$result = wppa_recuperate_iptc_exif();
					echo '||0||'.__( 'Recuperation performed', 'wppa' ).'||'.$result;
					exit;
					break;

				case 'wppa_bgcolor_thumbnail':
					$value = trim( strtolower( $value ) );
					if ( strlen( $value ) != '7' || substr( $value, 0, 1 ) != '#' ) {
						$wppa['error'] = '1';
					}
					else for ( $i=1; $i<7; $i++ ) {
						if ( ! in_array( substr( $value, $i, 1 ), array( '0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f' ) ) ) {
							$wppa['error'] = '1';
						}
					}
					if ( ! $wppa['error'] ) $old_minisize--;	// Trigger regen message
					else $alert = __( 'Illegal format. Please enter a 6 digit hexadecimal color value. Example: #77bbff', 'wppa' );
					break;

				case 'wppa_thumb_aspect':
					$old_minisize--;	// Trigger regen message
					break;

				case 'wppa_rating_max':
					if ( $value == '5' && $wppa_opt['wppa_rating_max'] == '10' ) {
						$rats = $wpdb->get_results( 'SELECT `id`, `value` FROM `'.WPPA_RATING.'`', ARRAY_A );
						if ( $rats ) {
							foreach ( $rats as $rat ) {
								$wpdb->query( $wpdb->prepare( 'UPDATE `'.WPPA_RATING.'` SET `value` = %s WHERE `id` = %s', $rat['value']/2, $rat['id'] ) );
							}
						}
					}
					if ( $value == '10' && $wppa_opt['wppa_rating_max'] == '5' ) {
						$rats = $wpdb->get_results( 'SELECT `id`, `value` FROM `'.WPPA_RATING.'`', ARRAY_A );
						if ( $rats ) {
							foreach ( $rats as $rat ) {
								$wpdb->query( $wpdb->prepare( 'UPDATE `'.WPPA_RATING.'` SET `value` = %s WHERE `id` = %s', $rat['value']*2, $rat['id'] ) );
							}
						}
					}
					
					update_option ( 'wppa_rerate_status', 'Required' );
					$alert .= __( 'You just changed a setting that requires the recalculation of ratings.', 'wppa' );
					$alert .= ' '.__( 'Please run the appropriate action in Table VIII.', 'wppa' );

					wppa_update_option( $option, $value );
					$wppa['error'] = '0';
					break;
					
				case 'wppa_newphoto_description':
					if ( wppa_switch( 'wppa_check_balance' ) && balanceTags( $value, true ) != $value ) {
						$alert = __( 'Unbalanced tags in photo description!', 'wppa' );
						$wppa['error'] = '1';
					}
					else {
						wppa_update_option( $option, $value );
						$wppa['error'] = '0';
						$alert = '';
						wppa_index_compute_skips();
					}
					break;
				
				case 'wppa_keep_source':
					$dir = $wppa_opt['wppa_source_dir'];
					if ( ! is_dir( $dir ) ) @ mkdir( $dir );
					if ( ! is_dir( $dir ) || ! is_writable( $dir ) ) {
						$wppa['error'] = '1';
						$alert = sprintf( __( 'Unable to create or write to %s', 'wppa' ), $dir );
					}
					break;
					
				case 'wppa_source_dir':
					$olddir = $wppa_opt['wppa_source_dir'];
					$value = rtrim( $value, '/' );
					if ( strpos( $value.'/', WPPA_UPLOAD_PATH.'/' ) !== false ) {
						$wppa['error'] = '1';
						$alert = sprintf( __( 'Source can not be inside the wppa folder.', 'wppa' ) );
					}
					else {
						$dir = $value;
						if ( ! is_dir( $dir ) ) @ mkdir( $dir );
						if ( ! is_dir( $dir ) || ! is_writable( $dir ) ) {
							$wppa['error'] = '1';
							$alert = sprintf( __( 'Unable to create or write to %s', 'wppa' ), $dir );
						}
						else {
							@ rmdir( $olddir ); 	// try to remove when empty
						}
					}
					break;
					
				case 'wppa_newpag_content':
					if ( strpos( $value, 'w#album' ) === false ) {
						$alert = __( 'The content must contain w#album', 'wppa' );
						$wppa['error'] = '1';
					}
					break;
					
				case 'wppa_gpx_shortcode':
					if ( strpos( $value, 'w#lat' ) === false || strpos( $value, 'w#lon' ) === false ) {
						$alert = __( 'The content must contain w#lat and w#lon', 'wppa' );
						$wppa['error'] = '1';
					}
					break;
					
				case 'wppa_i_responsive':
					if ( $value == 'yes' ) { wppa_update_option( 'wppa_colwidth', 'auto' ); }
					if ( $value == 'no' ) { wppa_update_option( 'wppa_colwidth', '640' ); }
					break;
					
				case 'wppa_i_downsize':
					if ( $value == 'yes' ) { 
						wppa_update_option( 'wppa_resize_on_upload', 'yes' );
						if ( $wppa_opt['wppa_resize_to'] == '0' ) wppa_update_option( 'wppa_resize_to', '1024x768' );
					}
					if ( $value == 'no' ) {
						wppa_update_option( 'wppa_resize_on_upload', 'no' );
					}
					break;
					
				case 'wppa_i_source':
					if ( $value == 'yes' ) {
						wppa_update_option( 'wppa_keep_source_admin', 'yes' );
						wppa_update_option( 'wppa_keep_source_frontend', 'yes' );
					}
					if ( $value == 'no' ) {
						wppa_update_option( 'wppa_keep_source_admin', 'no' );
						wppa_update_option( 'wppa_keep_source_frontend', 'no' );
					}
					break;
					
				case 'wppa_i_userupload':
					if ( $value == 'yes' ) { 
						wppa_update_option( 'wppa_user_upload_on', 'yes' );
						wppa_update_option( 'wppa_user_upload_login', 'yes' );
						wppa_update_option( 'wppa_owner_only', 'yes' );
						wppa_update_option( 'wppa_upload_moderate', 'yes' );
						wppa_update_option( 'wppa_upload_edit', 'yes' );
						wppa_update_option( 'wppa_upload_notify', 'yes' );
						wppa_update_option( 'wppa_grant_an_album', 'yes' );
						$grantparent = $wppa_opt['wppa_grant_parent'];
						if ( ! wppa_album_exists( $grantparent ) ) {
							$id = wppa_create_album_entry( array( 'name' => __( 'Members', 'wppa' ), 'description' => __( 'Parent of the member albums', 'wppa' ), 'a_parent' => '-1', 'upload_limit' => '0/0' ) );
							if ( $id ) {
								wppa_index_add( 'album', $id );
								wppa_update_option( 'wppa_grant_parent', $id );
							}
							$my_post = array( 
								'post_title'    => __( 'Members', 'wppa' ),
								'post_content'  => '[wppa type="content" album="'.$id.'"][/wppa]',
								'post_status'   => 'publish',
								'post_type'	  	=> 'page'
								 );
							$pagid = wp_insert_post( $my_post );
						}
						wppa_update_option( 'wppa_alt_is_restricted', 'yes' );
						wppa_update_option( 'wppa_link_is_restricted', 'yes' );
						wppa_update_option( 'wppa_covertype_is_restricted', 'yes' );
						wppa_update_option( 'wppa_porder_restricted', 'yes' );
					}
					if ( $value == 'no' ) {
						wppa_update_option( 'wppa_user_upload_on', 'no' );
					}
					break;
					
				case 'wppa_i_rating':
					if ( $value == 'yes' ) { 
						wppa_update_option( 'wppa_rating_on', 'yes' );
					}
					if ( $value == 'no' ) {
						wppa_update_option( 'wppa_rating_on', 'no' );
					}
					break;
					
				case 'wppa_i_comment':
					if ( $value == 'yes' ) { 
						wppa_update_option( 'wppa_show_comments', 'yes' );
						wppa_update_option( 'wppa_comment_moderation', 'all' );
						wppa_update_option( 'wppa_comment_notify', 'admin' );
					}
					if ( $value == 'no' ) {
						wppa_update_option( 'wppa_show_comments', 'no' );
					}
					break;
					
				case 'wppa_i_share':
					if ( $value == 'yes' ) { 
						wppa_update_option( 'wppa_share_on', 'yes' );
					}
					if ( $value == 'no' ) {
						wppa_update_option( 'wppa_share_on', 'no' );
					}
					break;
					
				case 'wppa_i_iptc':
					if ( $value == 'yes' ) { 
						wppa_update_option( 'wppa_show_iptc', 'yes' );
						wppa_update_option( 'wppa_save_iptc', 'yes' );
					}
					if ( $value == 'no' ) {
						wppa_update_option( 'wppa_show_iptc', 'no' );
						wppa_update_option( 'wppa_save_iptc', 'no' );
					}
					break;
					
				case 'wppa_i_exif':
					if ( $value == 'yes' ) { 
						wppa_update_option( 'wppa_show_exif', 'yes' );
						wppa_update_option( 'wppa_save_exif', 'yes' );
					}
					if ( $value == 'no' ) {
						wppa_update_option( 'wppa_show_exif', 'no' );
						wppa_update_option( 'wppa_save_exif', 'no' );
					}
					break;
					
				case 'wppa_i_gpx':
					if ( $value == 'yes' ) {
						$custom_content = $wppa_opt['wppa_custom_content'];
						if ( strpos( $custom_content, 'w#location' ) === false ) {
							$custom_content = $custom_content.' w#location';
							wppa_update_option( 'wppa_custom_content', $custom_content );
						}
						if ( ! wppa_switch( 'wppa_custom_on' ) ) {
							wppa_update_option( 'wppa_custom_on', 'yes' );
						}
						if ( $wppa_opt['wppa_gpx_implementation'] == 'none' ) {
							wppa_update_option( 'wppa_gpx_implementation', 'wppa-plus-embedded' );
						}
					}
					break;
					
				case 'wppa_i_fotomoto':
					if ( $value == 'yes' ) {
						$custom_content = $wppa_opt['wppa_custom_content'];
						if ( strpos( $custom_content, 'w#fotomoto' ) === false ) {
							$custom_content = 'w#fotomoto '.$custom_content;
							wppa_update_option( 'wppa_custom_content', $custom_content );
						}
						if ( ! wppa_switch( 'wppa_custom_on' ) ) {
							wppa_update_option( 'wppa_custom_on', 'yes' );
						}
						wppa_update_option( 'wppa_fotomoto_on', 'yes' );
					}
					break;
					
				case 'wppa_i_done':
					$value = 'done';
					break;
					
				case 'wppa_search_tags':
				case 'wppa_search_cats':
				case 'wppa_search_comments':
					update_option( 'wppa_index_need_remake', 'yes' );
					break;
					
				case 'wppa_blacklist_user':
					// Does user exist?
					$value = trim ( $value );
					$user = get_user_by ( 'login', $value );	// seems to be case insensitive
					if ( $user && $user->user_login === $value ) {
						$wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_PHOTOS."` SET `status` = 'pending' WHERE `owner` = %s", $value ) );
						$black_listed_users = get_option( 'wppa_black_listed_users', array() );
						if ( ! in_array( $value, $black_listed_users ) ) {
							$black_listed_users[] = $value;
							update_option( 'wppa_black_listed_users', $black_listed_users );
						}
						$alert = esc_js( sprintf( __( 'User %s has been blacklisted.', 'wppa' ), $value ) ); 
					}
					else {
						$alert = esc_js( sprintf( __( 'User %s does not exist.', 'wppa' ), $value ) ); 
					}
					$value = '';
					break;
					
				case 'wppa_un_blacklist_user':
					$wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_PHOTOS."` SET `status` = 'publish' WHERE `owner` = %s", $value ) );
					$black_listed_users = get_option( 'wppa_black_listed_users', array() );
					if ( in_array( $value, $black_listed_users ) ) {
						foreach ( array_keys( $black_listed_users ) as $usr ) {
							if ( $black_listed_users[$usr] == $value ) unset ( $black_listed_users[$usr] );
						}
						update_option( 'wppa_black_listed_users', $black_listed_users );
					}
					$value = '0';
					break;
				
				case 'wppa_fotomoto_on':
					if ( $value == 'yes' ) {
						$custom_content = $wppa_opt['wppa_custom_content'];
						if ( strpos( $custom_content, 'w#fotomoto' ) === false ) {
							$custom_content = 'w#fotomoto '.$custom_content;
							wppa_update_option( 'wppa_custom_content', $custom_content );
							$alert = __( 'The content of the Custom box has been changed to display the Fotomoto toolbar.', 'wppa' ).' ';
						}
						if ( ! wppa_switch( 'wppa_custom_on' ) ) {
							wppa_update_option( 'wppa_custom_on', 'yes' );
							$alert .= __( 'The display of the custom box has been enabled', 'wppa' );
						}
					}
					break;
					
				case 'wppa_gpx_implementation':
					if ( $value != 'none' ) {
						$custom_content = $wppa_opt['wppa_custom_content'];
						if ( strpos( $custom_content, 'w#location' ) === false ) {
							$custom_content = $custom_content.' w#location';
							wppa_update_option( 'wppa_custom_content', $custom_content );
							$alert = __( 'The content of the Custom box has been changed to display maps.', 'wppa' ).' ';
						}
						if ( ! wppa_switch( 'wppa_custom_on' ) ) {
							wppa_update_option( 'wppa_custom_on', 'yes' );
							$alert .= __( 'The display of the custom box has been enabled', 'wppa' );
						}
					}
					break;
					
				case 'wppa_regen_thumbs_skip_one':
					$last = get_option( 'wppa_regen_thumbs_last', '0' );
					$skip = $last + '1';
					update_option( 'wppa_regen_thumbs_last',  $skip );
					break;
					
				case 'wppa_remake_skip_one':
					$last = get_option( 'wppa_remake_last', '0' );
					$skip = $last + '1';
					update_option( 'wppa_remake_last',  $skip );
					break;
					
				case 'wppa_errorlog_purge':
					@ unlink( WPPA_CONTENT_PATH.'/wppa-depot/admin/error.log' );
					break;
					
				case 'wppa_pl_dirname':
					$value = wppa_sanitize_file_name( $value );
					$value = trim( $value, ' /' );
					if ( ! $value ) {
						$wppa['error'] = '714';
						$wppa['out'] = __('This value can not be empty', 'wppa');
					}
					else {
						wppa_create_pl_htaccess( $value );
					}
					break;
					
				default:
			
					$wppa['error'] = '0';
					$alert = '';
			}
			
			if ( $wppa['error'] ) {
				if ( ! $title ) $title = sprintf( __( 'Failed to set %s to %s', 'wppa' ), $option, $value );
				if ( ! $alert ) $alert .= $wppa['out'];
			}
			else {
				wppa_update_option( $option, $value );
				if ( ! $title ) $title = sprintf( __( 'Setting %s updated to %s', 'wppa' ), $option, $value );
			}
			
			// Something to do after changing the setting?
			$temp = $wppa;
			wppa_initialize_runtime( true );	// force reload new values
			
			// .htaccess
			wppa_create_wppa_htaccess();
			
			// Thumbsize
			$wppa = $temp;
			$new_minisize = wppa_get_minisize();
			if ( $old_minisize != $new_minisize ) {
				update_option ( 'wppa_regen_thumbs_status', 'Required' );
				$alert .= __( 'You just changed a setting that requires the regeneration of thumbnails.', 'wppa' );
				$alert .= ' '.__( 'Please run the appropriate action in Table VIII.', 'wppa' );
			}
			
			// Produce the response text
			$output = '||'.$wppa['error'].'||'.esc_attr( $title ).'||'.esc_js( $alert );
			
			echo $output;
			wppa_clear_cache();
			exit;
			break;	// End update-option
		
		case 'maintenance':
			$slug 	= $_POST['slug'];
			$nonce  = $_REQUEST['wppa-nonce'];
			if ( ! wp_verify_nonce( $nonce, 'wppa-nonce' ) ) {
				echo 'Security check failure||'.$slug.'||Error||0';
				exit;
			}
			echo wppa_do_maintenance_proc( $slug );
			exit;
			break;
			
		case 'maintenancepopup':
			$slug 	= $_POST['slug'];
			$nonce  = $_REQUEST['wppa-nonce'];
			if ( ! wp_verify_nonce( $nonce, 'wppa-nonce' ) ) {
				echo 'Security check failure||'.$slug.'||Error||0';
				exit;
			}
			echo wppa_do_maintenance_popup( $slug );
			exit;
			break;

		case 'do-fe-upload':
			wppa_user_upload();
			echo $wppa['out'];
			exit;
			break;
		
		default:	// Unimplemented $wppa-action
		die( '-1' );
	}
	exit;
}

function wppa_decode( $string ) {
	$arr = explode( '||HASH||', $string );
	$result = implode( '#', $arr );
	$arr = explode( '||AMP||', $result );
	$result = implode( '&', $arr );
	$arr = explode( '||PLUS||', $result );
	$result = implode( '+', $arr );
	
	return $result;
}

function wppa_ajax_check_range( $value, $fixed, $low, $high, $title ) {
global $wppa;
	if ( $fixed !== false && $fixed == $value ) return;						// User enetred special value correctly
	if ( !is_numeric( $value ) ) $wppa['error'] = true;						// Must be numeric if not specaial value
	if ( $low !== false && $value < $low ) $wppa ['error'] = true;			// Must be >= given min value
	if ( $high !== false && $value > $high ) $wppa ['error'] = true;		// Must be <= given max value
	
	if ( !$wppa ['error'] ) return;		// Still no error, ok
	
	// Compose error message
	if ( $low !== false && $high === false ) {	// Only Minimum given
		$wppa['out'] .= __( 'Please supply a numeric value greater than or equal to', 'wppa' ) . ' ' . $low . ' ' . __( 'for', 'wppa' ) . ' ' . $title;
		if ( $fixed !== false ) {
			if ( $fixed ) $wppa['out'] .= '. ' . __( 'You may also enter:', 'wppa' ) . ' ' . $fixed;
			else $wppa['out'] .= '. ' . __( 'You may also leave/set this blank', 'wppa' );
		}
	}
	else {	// Also Maximum given
		$wppa['out'] .= __( 'Please supply a numeric value greater than or equal to', 'wppa' ) . ' ' . $low . ' ' . __( 'and less than or equal to', 'wppa' ) . ' ' . $high . ' ' . __( 'for', 'wppa' ) . ' ' . $title;
		if ( $fixed !== false ) {
			if ( $fixed ) $wppa['out'] .= '. ' . __( 'You may also enter:', 'wppa' ) . ' ' . $fixed;
			else $wppa['out'] .= '. ' . __( 'You may also leave/set this blank', 'wppa' );
		}
	}
}

