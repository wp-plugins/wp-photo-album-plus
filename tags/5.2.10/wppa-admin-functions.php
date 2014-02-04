<?php
/* wppa-admin-functions.php
* Package: wp-photo-album-plus
*
* gp admin functions
* version 5.2.8
*
* 
*/


function wppa_backup_settings() {
global $wppa_opt;
global $wppa_bu_err;
global $wppa;
	// Open file
	$fname = WPPA_DEPOT_PATH.'/settings.bak';
	if ($wppa['debug']) wppa_dbg_msg('Backing up to: '.$fname);
	
	$file = fopen($fname, 'wb');
	// Backup
	if ($file) {
		array_walk($wppa_opt, 'wppa_save_an_option', $file);
		// Close file
		fclose($file);
		if (!$wppa_bu_err) {
			wppa_ok_message(__('Settings successfully backed up', 'wppa'));
			return true;
		}
	}
	wppa_error_message(__('Unable to backup settings', 'wppa'));
	return false;
}
function wppa_save_an_option($value, $key, $file) {
global $wppa_bu_err;
	$value = str_replace("\n", "\\n", $value);
	if (fwrite($file, $key.":".$value."\n") === false) {
		if ($wppa_bu_err !== true) {
			wppa_error_message(__('Error writing to settings backup file', 'wppa'));
			$wppa_bu_err = true;
		}	
	}
}

function wppa_restore_settings($fname, $type = '') {
global $wppa;

	if ($wppa['debug']) wppa_dbg_msg('Restoring from: '.$fname);
	if ( $type == 'skin' ) {
		$void_these = array(
							'wppa_revision', 
							'wppa_resize_on_upload', 
							'wppa_allow_debug', 
							'wppa_thumb_linkpage',
							'wppa_mphoto_linkpage',
							'wppa_widget_linkpage',
							'wppa_slideonly_widget_linkpage',
							'wppa_topten_widget_linkpage',
							'wppa_coverimg_linkpage',
							'wppa_search_linkpage',
							'permalink_structure',
							'wppa_rating_max',
							'wppa_file_system'
							);
	}
	else {
		$void_these = array(
							'wppa_revision',
							'wppa_rating_max',
							'wppa_file_system'
							);
	}
	
	// Open file
	$file = fopen($fname, 'r');
	// Restore
	if ($file) {
		$buffer = fgets($file, 4096);
		while (!feof($file)) {
			$buflen = strlen($buffer);
			if ($buflen > '0' && substr($buffer, 0, 1) != '/') {	// lines that start with '/' are comment
				$cpos = strpos($buffer, ':');
				$delta_l = $buflen - $cpos - 2;
				if ($cpos && $delta_l >= 0) {
					$slug = substr($buffer, 0, $cpos);
					$value = substr($buffer, $cpos+1, $delta_l);
					$value = str_replace('\n', "\n", $value);	// Replace substr '\n' by nl char value
					$value = stripslashes($value);
					//wppa_dbg_msg('Doing|'.$slug.'|'.$value);
					if ( ! in_array($slug, $void_these)) wppa_update_option($slug, $value);
					else wppa_dbg_msg($slug.' skipped');
				}
			}
//else echo 'Comment: '.$buffer.'<br/>';
			
			$buffer = fgets($file, 4096);
		}
		fclose($file);
		wppa_initialize_runtime(true);
		return true;
	}
	else {
		wppa_error_message(__('Settings file not found', 'wppa'));
		return false;
	}
}

// update all thumbs 
function wppa_regenerate_thumbs() {
	global $wpdb;
	
	$thumbsize = wppa_get_minisize();

    $start = get_option('wppa_lastthumb', '-1');

	$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . WPPA_PHOTOS . '` WHERE `id` > %s ORDER BY `id` LIMIT 1000', $start), ARRAY_A);
	
	if ( empty($photos) ) return true;		// Done, did them all
	
	$count = count($photos);
	$done = '0';
	foreach ($photos as $photo) {
		$newimage = wppa_get_photo_path($photo['id']);
		if ( is_file($newimage) ) {
			wppa_create_thumbnail($newimage, $thumbsize, '' );
			wppa_update_option('wppa_lastthumb', $photo['id']);
			wppa_clear_cache();
			echo '.';
			$done++;
		}
		else {
			wppa_error_message('Unexpected error: file '.$newimage.' was expected but is missing', 'force', 'red');
		}
		if ( wppa_is_time_up($done) ) {
			return false;	// NOT Done, have to go on
		}
	}
}

// Remake
function wppa_remake_files( $alb = '', $pid = '' ) {
global $wpdb;
global $wppa_opt;

	// Init
	$count = '0';
	
	// Find the album(s) if any
	if ( ! $alb && ! $pid ) { 
		$start_time = get_option('wppa_remake_start', '0');
		$albums = $wpdb->get_results('SELECT `id` FROM `'.WPPA_ALBUMS.'`', ARRAY_A);
	}
	elseif ( $alb ) {
		$start_time = get_option('wppa_remake_start_album_'.$alb, '0');
		$albums = array( array( 'id' => $alb ) );
	}
	else $albums = false;
	
	// Do it with albums
	if ( $albums ) foreach ( $albums as $album ) {
		$source_dir = wppa_get_source_album_dir( $album['id'] ); 
		if ( is_dir($source_dir) ) {
			$files = glob($source_dir.'/*');
			if ( $files ) foreach ( $files as $file ) {
				if ( ! is_dir($file) ) {
					$filename = basename($file);
					$photos = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_PHOTOS."` WHERE `filename` = %s OR ( `filename` = '' AND `name` = %s )", $filename, $filename), ARRAY_A);
					if ( $photos ) foreach ( $photos as $photo ) {	// Photo exists
						$modified_time = $photo['modified'];
						if ( $modified_time < $start_time ) {
							wppa_update_single_photo($file, $photo['id'], $filename);
//							$wpdb->query($wpdb->prepare('UPDATE `'.WPPA_PHOTOS.'` SET `modified` = %s WHERE `id` = %s', time(), $photo['id']));
							$count++;
						}			
						if ( wppa_is_time_up($count) ) {
							return false;
						}
					}
					else {	// No photo yet
						if ( $wppa_opt['wppa_remake_add'] ) {
							wppa_insert_photo($file, $album['id'], $filename);
						//	$wpdb->query($wpdb->prepare('UPDATE `'.WPPA_PHOTOS.'` SET `modified` = %s WHERE `id` = %s', time(), $photo['id']));
							$count++;
						}
					}
					if ( wppa_is_time_up($count) ) {
						return false;
					}
				}
			}
		}
	}
	// Do it with a single photo
	elseif ( $pid ) {
		$photo = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $pid), ARRAY_A);
		if ( $photo ) {
			$file = wppa_get_source_path( $photo['id'] ); 
			wppa_update_single_photo($file, $pid, $photo['filename']);
		}
		else return false;
	}
	return true;
}

// display usefull message
function wppa_update_message($msg, $fixed = false, $id = '') {
?>
    <div id="wppa-ms-<?php echo $id ?>" class="updated fade" <?php if ($fixed) echo 'style="position: fixed; width: 80%; text-align: center; text-weight:bold;"' ?>><p><strong><?php echo($msg); ?></strong></p></div>
<?php
}

// display error message
function wppa_error_message($msg, $fixed = false, $id = '') {
?>
	<div id="wppa-er-<?php echo $id ?>" class="error <?php if ($fixed == 'fixed') echo fade ?>" <?php if ($fixed == 'hidden') echo 'style="display:none;"'; if ($fixed == 'fixed') echo 'style="position: fixed;"' ?>><p><strong><?php echo($msg); ?></strong></p></div>
<?php
	wppa_log('Error', $msg);
}
// display warning message
function wppa_warning_message($msg, $fixed = false, $id = '') {
?>
	<div id="wppa-wr-<?php echo $id ?>" class="updated <?php if ($fixed == 'fixed') echo fade ?>" <?php if ($fixed == 'hidden') echo 'style="display:none;"'; if ($fixed == 'fixed') echo 'style="position: fixed;"' ?>><p><strong><?php echo($msg); ?></strong></p></div>
<?php
}
// display ok message
function wppa_ok_message($msg, $fixed = false, $id = '') {
?>
	<div id="wppa-ok-<?php echo $id ?>" class="updated <?php if ($fixed == 'fixed') echo fade ?>" style="background-color: #e0ffe0; border-color: #55ee55;" ><p id="wppa-ok-p" ><strong><?php echo($msg); ?></strong></p></div>
<?php
}

function wppa_check_numeric($value, $minval, $target, $maxval = '') {
	if ($maxval == '') {
		if (is_numeric($value) && $value >= $minval) return true;
		wppa_error_message(__('Please supply a numeric value greater than or equal to', 'wppa') . ' ' . $minval . ' ' . __('for', 'wppa') . ' ' . $target);
	}
	else {
		if (is_numeric($value) && $value >= $minval && $value <= $maxval) return true;
		wppa_error_message(__('Please supply a numeric value greater than or equal to', 'wppa') . ' ' . $minval . ' ' . __('and less than or equal to', 'wppa') . ' ' . $maxval . ' ' . __('for', 'wppa') . ' ' . $target);
	}
	return false;
}

// check if albums 'exists'
function wppa_has_albums() {
	return wppa_have_access('0');
}

function wppa_user_select($select = '') {
	$result = '';
	$iam = $select == '' ? wppa_get_user() : $select;
	$users = wppa_get_users();
	$sel = $select == '--- public ---' ? 'selected="selected"' : '';
	$result .= '<option value="--- public ---" '.$sel.'>'.__('--- public ---', 'wppa').'</option>';
	foreach ($users as $usr) {
		if ($usr['user_login'] == $iam) $sel = 'selected="selected"';
		else $sel = '';
		$result .= '<option value="'.$usr['user_login'].'" '.$sel.'>'.$usr['display_name'].'</option>';
	}	
	echo ($result);
}

function wppa_copy_photo($photoid, $albumto) {
global $wpdb;

	$err = '1';
	// Check args
	if (!is_numeric($photoid) || !is_numeric($albumto)) return $err;
	
	$err = '2';
	// Find photo details
	$photo = $wpdb->get_row($wpdb->prepare( 'SELECT * FROM '.WPPA_PHOTOS.' WHERE id = %s', $photoid ), 'ARRAY_A');
	if (!$photo) return $err;
	$albumfrom 	= $photo['album'];
	$album 		= $albumto;
	$ext 		= $photo['ext'];
	$name 		= $photo['name'];
	$porder		= '0';
	$desc 		= $photo['description'];
	$linkurl 	= $photo['linkurl'];
	$linktitle 	= $photo['linktitle'];
	$linktarget = $photo['linktarget'];
	$status 	= $photo['status'];
	$filename 	= $photo['filename'];
	$location	= $photo['location'];
	$oldimage 	= wppa_get_photo_path($photo['id']);
	$oldthumb 	= wppa_get_thumb_path($photo['id']);
	
	$err = '3';
	// Make new db table entry
//	$id = wppa_nextkey(WPPA_PHOTOS);
	$owner = wppa_get_user();
	$time = wppa_switch('wppa_copy_timestamp') ? $photo['timestamp'] : time();
//	$query = $wpdb->prepare('INSERT INTO `' . WPPA_PHOTOS . '` (`id`, `album`, `ext`, `name`, `p_order`, `description`, `mean_rating`, `linkurl`, `linktitle`, `linktarget`, `timestamp`, `owner`, `status`, `tags`, `alt`, `filename`, `modified`, `location`) VALUES (%s, %s, %s, %s, %s, %s, \'\', %s, %s, %s, %s, %s, %s, %s, %s, %s, \'0\', %s)', $id, $album, $ext, $name, $porder, $desc, $linkurl, $linktitle, $linktarget, $time, $owner, $status, '', '', $filename, $location);
//	if ($wpdb->query($query) === false) return $err;
	$id = wppa_create_photo_entry( array( 'album' => $album, 'ext' => $ext, 'name' => $name, 'p_order' => $porder, 'description' => $desc, 'linkurl' => $linkurl, 'linktitle' => $linktitle, 'linktarget' => $linktarget, 'timestamp' => $time, 'owner' => $owner, 'status' => $status, 'filename' => $filename, 'location' => $location ) );
	if ( ! $id ) return $err;
	wppa_flush_treecounts($album);
	wppa_index_add('photo', $id);

	$err = '4';
	// Find copied photo details
	$image_id = $id;			
	$newimage = wppa_get_photo_path($image_id);
	$newthumb = wppa_get_thumb_path($image_id);
	if (!$image_id) return $err;
	
	$err = '5';
	// Do the filsystem copy
	if (!copy($oldimage, $newimage)) return $err;
	$err = '6';
	if (!copy($oldthumb, $newthumb)) return $err;
	// Copy source
	wppa_copy_source($filename, $albumfrom, $albumto);
	// Copy Exif and iptc
	wppa_copy_exif($photoid, $id);
	wppa_copy_iptc($photoid, $id);
	// Bubble album timestamp
	if ( ! wppa_switch('wppa_copy_timestamp') ) wppa_update_album_timestamp($albumto);
	return false;	// No error
}
function wppa_copy_exif($fromphoto, $tophoto) {
global $wpdb;

	$exiflines = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_EXIF."` WHERE `photo` = %s", $fromphoto), ARRAY_A);
	if ( $exiflines ) foreach ( $exiflines as $line ) {
//		$id = wppa_nextkey(WPPA_EXIF);
//		$wpdb->query($wpdb->prepare("INSERT INTO `".WPPA_EXIF."` (`id`, `photo`, `tag`, `description`, `status`) VALUES (%s, %s, %s, %s, %s)", $id, $tophoto, $line['tag'], $line['description'], $line['status']));
		$id = wppa_create_exif_entry( array( 'photo' => $tophoto, 'tag' => $line['tag'], 'description' => $line['description'], 'status' => $line['status'] ) );
	}
}
function wppa_copy_iptc($fromphoto, $tophoto) {
global $wpdb;

	$iptclines = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_IPTC."` WHERE `photo` = %s", $fromphoto), ARRAY_A);
	if ( $iptclines ) foreach ( $iptclines as $line ) {
//		$id = wppa_nextkey(WPPA_IPTC);
//		$wpdb->query($wpdb->prepare("INSERT INTO `".WPPA_IPTC."` (`id`, `photo`, `tag`, `description`, `status`) VALUES (%s, %s, %s, %s, %s)", $id, $tophoto, $line['tag'], $line['description'], $line['status']));
		$id = wppa_create_iptc_entry( array( 'photo' => $tophoto, 'tag' => $line['tag'], 'description' => $line['description'], 'status' => $line['status'] ) );
	}
}


function wppa_rotate($id, $ang) {
global $wpdb;

	// Check args
	$err = '1';
	if (!is_numeric($id) || !is_numeric($ang)) return $err;
	
	// Get the ext
	$err = '2';
	$ext = $wpdb->get_var($wpdb->prepare( 'SELECT ext FROM '.WPPA_PHOTOS.' WHERE id = %s', $id ) );
	if (!$ext) return $err;
	
	// Get the image
	$err = '3';
	$file = wppa_get_photo_path($id);
	if (!is_file($file)) return $err;
	
	// Get the imgdetails
	$err = '4';
	$img = getimagesize($file);
	if (!$img) return $err;
	
	// Get the image
	switch ($img[2]) {
		case 1:	// gif
			$err = '5';
			$source = imagecreatefromgif($file);
			break;
		case 2: // jpg
			$err = '6';
			$source = imagecreatefromjpeg($file);
			break;
		case 3: // png
			$err = '7';
			$source = imagecreatefrompng($file);
			break;
		default: // unsupported mimetype
			$err = '10';
			$source = false;	
	}
	if (!$source) return $err;

	// Rotate the image
	$err = '11';
	$rotate = imagerotate($source, $ang, 0);
	if (!$rotate) return $err;
	
	// Save the image
	switch ($img[2]) {
		case 1:
			$err = '15';
			$bret = imagegif($rotate, $file, 95);
			break;
		case 2:
			$err = '16';
			$bret = imagejpeg($rotate, $file);
			break;
		case 3:
			$err = '17';
			$bret = imagepng($rotate, $file);
			break;
		default:
			$err = '20';
			$bret = false;
	}
	if (!$bret) return $err;
	
	// Destroy the source
	imagedestroy($source);
	// Destroy the result
	imagedestroy($rotate);

	// Recreate the thumbnail
	$err = '30';
	$thumbsize = wppa_get_minisize();
	$bret = wppa_create_thumbnail($file, $thumbsize, '' );
	if (!$bret) return $err;
	
	// Return success
	return false;
}


// Remove photo entries that have no fullsize image or thumbnail
// Additionally check the php config
// Photos that have a deleted album: album created
function wppa_cleanup_photos($alb = '') {
	global $wpdb;
	global $wppa_opt;
	global $wppa_error_displayed;
//echo('WPPADBG'.$alb);

// return; // temp patch for ivan

if ( is_multisite() ) return; // temp disabled for 4.0 bug, must be tested in a real multisite first before enabling
	
	// Check the users php config. sometimes a user 'reconfigures' his server to not having GD support...
	if ( ! function_exists('getimagesize') || ! function_exists('imagecreatefromjpeg') ) {
		if ( ! $wppa_error_displayed ) {
			wppa_error_message(__('Please check your php configuration. Currently it does not support the required functionality to manipulate photos', 'wppa'));
			$wppa_error_displayed = true;
		}
	}

	if ($alb == '') $alb = wppa_get_last_album();
	if (!is_numeric($alb)) return;

	$no_photos = '';
//	if ($alb == '0') wppa_ok_message(__('Checking database, please wait...', 'wppa'));
	$delcount = 0;
	if ($alb == '0') $entries = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS, ARRAY_A);
	else $entries = $wpdb->get_results($wpdb->prepare( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `album` = %s", $alb ), ARRAY_A);
	if ($entries) {
		foreach ( $entries as $entry ) {
			$thumbpath = wppa_get_thumb_path($entry['id']);
			$imagepath = wppa_get_photo_path($entry['id']);
			if ( ! is_file($thumbpath) ) {	// No thumb 
				wppa_dbg_msg('Error: expected thumbnail image file does not exist: '.$thumbpath, 'red', true);
			}
			if ( ! is_file($imagepath) ) { // No fullimage
				wppa_dbg_msg('Error: expected fullsize image file does not exist: '.$thumbpath, 'red', true);
				wppa_dbg_msg('Please delete photo '.$entry['name'].' with id='.$entry['id'], 'red', true);
			}
			if ( ! wppa_album_exists($entry['album']) ) {
				wppa_dbg_msg('Photo '.$entry['id'].' has album='.$entry['album'].', but the album does not exist.');
				$wpdb->query("UPDATE `".WPPA_PHOTOS."` SET `album` = 0 WHERE `id` = ".$entry['id']);
			}
		}
	}
	// Now fix missing exts for upload bug in 2.3.0
	$fixcount = 0;
/*
	$entries = $wpdb->get_results( "SELECT `id`, `ext`, `name` FROM `".WPPA_PHOTOS."` WHERE `ext` = ''" , ARRAY_A );
	if ($entries) {
		wppa_ok_message(__('Trying to fix '.count($entries).' entries with missing file extension, Please wait.', 'wppa'));
		foreach ($entries as $entry) {
			$tp = WPPA_UPLOAD_PATH.'/'.$entry['id'].'.';
			// Try the name
			$ext = substr(strrchr($entry['name'], "."), 1);
			if (!($ext == 'jpg' || $ext == 'JPG' || $ext == 'png' || $ext == 'PNG' || $ext == 'gif' || $ext == 'GIF')) {
				$ext = '';
			}
			if ($ext == '' && is_file($tp)) {
			// Try the type from the file
				$img = getimagesize($tp);
				if ($img[2] == 1) $ext = 'gif';
				if ($img[2] == 2) $ext = 'jpg';
				if ($img[2] == 3) $ext = 'png';
			}
			if ($ext == 'jpg' || $ext == 'JPG' || $ext == 'png' || $ext == 'PNG' || $ext == 'gif' || $ext == 'GIF') {
				
				if ($wpdb->query($wpdb->prepare( "UPDATE `".WPPA_PHOTOS."` SET `ext` = %s WHERE `id` = %s", $ext, $entry['id'] ) ) ) {
					$oldimg = WPPA_UP LOAD_PATH.'/'.$entry['id'].'.';
					$newimg = WPPA_UP LOAD_PATH.'/'.$entry['id'].'.'.$ext;
					if (is_file($oldimg)) {
						copy($oldimg, $newimg);
						unlink($oldimg);
					}
					$oldimg = WPPA_UP LOAD_PATH.'/thumbs/'.$entry['id'].'.';
					$newimg = WPPA_UP LOAD_PATH.'/thumbs/'.$entry['id'].'.'.$ext;
					if (is_file($oldimg)) {
						copy($oldimg, $newimg);
						unlink($oldimg);
					}
					$fixcount++;
					wppa_ok_message(__('Fixed extension for ', 'wppa').$entry['name']);
				}
				else {
					wppa_error_message(__('Unable to fix extension for ', 'wppa').$entry['name']);
				}
			}
			else {
				wppa_error_message(__('Unknown extension for photo ', 'wppa').$entry['name'].'. '.__('Please change the name to something with the proper extension and try again!', 'wppa'));
			}
		}	
	}
/**/	
	// Now fix orphan photos
	$orphcount = 0;
	$entries = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `album` = '0'", ARRAY_A);
	if ($entries) {
		$album = wppa_get_album_id(__('Orphan Photos', 'wppa'));
		if ($album == '') {
//			$key = wppa_nextkey(WPPA_ALBUMS);
//			$query = $wpdb->prepare("INSERT INTO `" . WPPA_ALBUMS . "` (`id`, `name`, `description`, `a_order`, `a_parent`, `p_order_by`, `main_photo`, `cover_linktype`, `cover_linkpage`, `owner`, `timestamp`, `default_tags`, `cover_type`, `suba_order_by`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, '', '', '')", $key, __('Orphan Photos', 'wppa'), '', '0', '-1', '0', '0', 'content', '0', 'admin', time());
//			$iret = $wpdb->query($query);
			$id = wppa_create_album_entry( array( 'name' => __('Orphan Photos', 'wppa'), 'a_parent' => '-1', 'owner' => 'admin' ) );
			if ( ! $id ) {
				wppa_error_message('Could not create album: Orphan Photos', 'wppa');
			}
			else {
				wppa_flush_treecounts($id);
				wppa_index_add('album', $id);
				wppa_ok_message('Album: Orphan Photos created.', 'wppa');
			}
			$album = wppa_get_album_id(__('Orphan Photos', 'wppa')); // retry
		}
		if ($album) {
			$orphcount = $wpdb->query($wpdb->prepare( 'UPDATE '.WPPA_PHOTOS.' SET album = %s WHERE album < 1', $album ) );
		}
		else {
			wppa_error_message(__('Could not recover orphanized photos.', 'wppa'));
		}
	}

	// End fix
	if ($orphcount > 0){
		wppa_ok_message(__('Database fixed.', 'wppa').' '.$orphcount.' '.__('orphanized photos recovered.', 'wppa'));
	}
	if ($delcount > 0){
		wppa_ok_message(__('Database fixed.', 'wppa').' '.$delcount.' '.__('invalid entries removed:', 'wppa').$no_photos);
	}
	if ($fixcount > 0) {
		wppa_ok_message(__('Database fixed.', 'wppa').' '.$fixcount.' '.__('missing file extensions recovered.', 'wppa'));
	}
		if ($alb == '0' && $delcount == 0 && $fixcount == 0) {
//		wppa_ok_message(__('Done. No errors found. Have a nice upload!', 'wppa'));
	}
//	wppa_flush_treecounts();
}


function wppa_walktree($relroot, $source, $allowwppa = false, $subdirsonly = false, $allowthumbs = true) {

	if ( ! $subdirsonly ) {
		if ($relroot == $source) $sel=' selected="selected"'; else $sel = ' ';
		$display  = str_replace(WPPA_DEPOT, __('--- My depot --- ', 'wppa'), $relroot);
		$ngg_opts = get_option('ngg_options', false);
		if ( $ngg_opts ) $display = str_replace($ngg_opts['gallerypath'], __('--- Ngg Galleries --- ', 'wppa'), $display);
		echo('<option value="'.$relroot.'"'.$sel.'>'.$display.'</option>');
	}
	
	if ( $handle = opendir(ABSPATH.$relroot) ) {
		while (false !== ($file = readdir($handle))) {
			if ( $file != "." && $file != ".." && ( $file != "wppa" || $allowwppa ) && ( $file != "thumbs" || $allowthumbs ) ) {
				$newroot = $relroot.'/'.$file;
				if (is_dir(ABSPATH.$newroot)) {	
					wppa_walktree($newroot, $source, $allowwppa, false, $allowthumbs);
				}
			}
		}
		closedir($handle);
	}
}

function wppa_sanitize_files() {

	// Get this users depot directory
	$depot = WPPA_DEPOT_PATH;
	__wppa_sanitize_files($depot);
}

function __wppa_sanitize_files($root) {
	// See what's in there
	$allowed_types = array('zip', 'jpg', 'png', 'gif', 'amf', 'pmf', 'bak', 'log');

	$paths = $root.'/*';
	$files = glob($paths);

	$count = '0';
	if ($files) foreach ($files as $file) {
		if (is_file($file)) {
			$ext = strtolower(substr(strrchr($file, "."), 1));
			if (!in_array($ext, $allowed_types)) {
				unlink($file);
				wppa_error_message(sprintf(__('File %s is of an unsupported filetype and has been removed.', 'wppa'), basename($file)));
				$count++;
			}
		}
		elseif (is_dir($file)) {
			$entry = basename($file);
			if ( $entry != '.' && $entry != '..' ) {
				__wppa_sanitize_files($file);
			}
		}
	}
	return $count;
}

// get select form element listing albums 
function wppa_album_select(	$exc = '', 
							$sel = '', 
							$addnone = FALSE, 
							$addseparate = FALSE, 
							$checkancestors = FALSE, 
							$none_is_all = false, 
							$none_is_blank = false,
							$check_upload_allowed = false,
							$add_multiple = false,
							$add_numbers = false
							) {

global $wpdb;

	$albums = $wpdb->get_results( "SELECT * FROM `".WPPA_ALBUMS."` ORDER BY `name`", ARRAY_A);
	
    if ($sel == '') {
        $s = wppa_get_last_album();
        if ($s != $exc) $sel = $s;
    }
    
    $result = '';
	
	if ($add_multiple) {
		$result .= '<option value="-99">' . __('--- multiple see below ---', 'wppa') . '</option>';
	}

    if ($addnone) {
		if ($none_is_blank) $result .= '<option value="0"></option>';
		elseif ($none_is_all) $result .= '<option value="0">' . __('--- all ---', 'wppa') . '</option>';
		else $result .= '<option value="0">' . __('--- none ---', 'wppa') . '</option>';
	}
    
	foreach ($albums as $album) if (wppa_have_access($album['id'])) {
		$disabled = '';
		$selected = '';

		if ( $check_upload_allowed && ! wppa_allow_uploads($album['id']) ) {
			$disabled = ' disabled="disabled" ';
		}
		elseif ($sel == $album['id']) { 
            $selected = ' selected="selected" '; 
        } 
		
		if ($album['id'] != $exc && (!$checkancestors || !wppa_is_ancestor($exc, $album['id']))) {
			$result .= '<option value="' . $album['id'] . '"' . $selected . $disabled . '>';
			$result .= wppa_qtrans(stripslashes($album['name']));
			if ( $disabled ) $result .= ' '.__('(full)', 'wppa');
			if ( $add_numbers ) $result .= ' ('.$album['id'].')';
			$result .= '</option>';
		}
		else {	// excluded or is ancestor
			$result .= '<option disabled="disabled" value="-3">'.wppa_qtrans(stripslashes($album['name'])).'</option>';
		}
	}
    
    if ($sel == -1) $selected = ' selected="selected" '; else $selected = '';
    if ($addseparate) $result .= '<option value="-1"' . $selected . '>' . __('--- separate ---', 'wppa') . '</option>';
	return $result;
}

function wppa_recalculate_ratings() {
global $wpdb;
global $wppa_opt;

	$photos = $wpdb->get_results( "SELECT `id` FROM `" . WPPA_PHOTOS . "`", ARRAY_A);
	if ($photos) {
		foreach ($photos as $photo) {
			$ratings = $wpdb->get_results($wpdb->prepare( 'SELECT value FROM '.WPPA_RATING.' WHERE photo = %s', $photo['id']), 'ARRAY_A');
			$the_value = '0';
			$the_count = '0';
			if ( $ratings ) foreach ($ratings as $rating) {
				if ( $rating['value'] == '-1' ) $the_value += $wppa_opt['wppa_dislike_value'];
				else $the_value += $rating['value'];
				$the_count++;
			}
			if ($the_count) $the_value /= $the_count;
			if ($the_value == '10') $the_value = '9.9999999';	// mean_rating is a text field. for sort order reasons we make 10 into 9.99999
			$iret = $wpdb->query($wpdb->prepare( 'UPDATE '.WPPA_PHOTOS.' SET mean_rating = %s WHERE id = %s', $the_value, $photo['id'] ) );
			if ($iret === false) {
				if ( $wppa['ajax'] ) {
					$wppa['error'] = true;
					$wppa['out'] = __('Unable to update mean rating', 'wppa');
				}
				else {
					wppa_error_message(__('Unable to update mean rating', 'wppa'));
				}
				return false;
			}
			$ratcount = count($ratings);
			$iret = $wpdb->query($wpdb->prepare( 'UPDATE '.WPPA_PHOTOS.' SET rating_count = %s WHERE id = %s', $ratcount, $photo['id'] ) );
		}
		return true;
	}
	else {
		if ( $wppa['ajax'] ) {
			$wppa['error'] = true;
			$wppa['out'] = __('No photos or error reading', 'wppa').WPPA_PHOTOS;
		}
		else {
			wppa_error_message(__('No photos or error reading', 'wppa').WPPA_PHOTOS);
		}
		return false;
	}
}

function wppa_check_database($verbose = false) {
global $wpdb;

	$any_error = false;
	// Check db tables
	// This is to test if dbdelta did his job in adding tables and columns
	$tn = array( WPPA_ALBUMS, WPPA_PHOTOS, WPPA_RATING, WPPA_COMMENTS, WPPA_IPTC, WPPA_EXIF, WPPA_INDEX );
	$flds = array( 	WPPA_ALBUMS => array(	'id' => 'bigint(20) NOT NULL', 
											'name' => 'text NOT NULL', 
											'description' => 'text NOT NULL', 
											'a_order' => 'smallint(5) unsigned NOT NULL', 
											'main_photo' => 'bigint(20) NOT NULL', 
											'a_parent' => 'bigint(20) NOT NULL',
											'p_order_by' => 'int unsigned NOT NULL',
											'cover_linktype' => 'tinytext NOT NULL',
											'cover_linkpage' => 'bigint(20) NOT NULL',
											'owner' => 'text NOT NULL',
											'timestamp' => 'tinytext NOT NULL',
											'upload_limit' => 'tinytext NOT NULL',	
											'alt_thumbsize' => 'tinytext NOT NULL',
											'default_tags' => 'tinytext NOT NULL',
											'cover_type' => 'tinytext NOT NULL',
											'suba_order_by' => 'tinytext NOT NULL'											
										), 
					WPPA_PHOTOS => array(	'id' => 'bigint(20) NOT NULL', 
											'album' => 'bigint(20) NOT NULL', 
											'ext' => 'tinytext NOT NULL', 
											'name' => 'text NOT NULL', 
											'description' => 'longtext NOT NULL', 
											'p_order' => 'smallint(5) unsigned NOT NULL',
											'mean_rating' => 'tinytext NOT NULL',
											'linkurl' => 'text NOT NULL',
											'linktitle' => 'text NOT NULL',
											'linktarget' => 'tinytext NOT NULL',
											'owner' => 'text NOT NULL',
											'timestamp' => 'tinytext NOT NULL',
											'status' => 'tinytext NOT NULL',
											'rating_count' => "bigint(20) default '0'",
											'tags' => 'tinytext NOT NULL',
											'alt' => 'tinytext NOT NULL',
											'filename' => 'tinytext NOT NULL',
											'modified' => 'tinytext NOT NULL',
											'location' => 'tinytext NOT NULL'
										), 
					WPPA_RATING => array(	'id' => 'bigint(20) NOT NULL',
											'photo' => 'bigint(20) NOT NULL',
											'value' => 'smallint(5) NOT NULL',
											'user' => 'text NOT NULL'
										), 
					WPPA_COMMENTS => array(
											'id' => 'bigint(20) NOT NULL',
											'timestamp' => 'tinytext NOT NULL',
											'photo' => 'bigint(20) NOT NULL',
											'user' => 'text NOT NULL',
											'ip' => 'tinytext NOT NULL',
											'email' => 'text NOT NULL',
											'comment' => 'text NOT NULL',
											'status' => 'tinytext NOT NULL'
										), 
					WPPA_IPTC => array(
											'id' => 'bigint(20) NOT NULL',
											'photo' => 'bigint(20) NOT NULL',
											'tag' => 'tinytext NOT NULL',
											'description' => 'text NOT NULL',
											'status' => 'tinytext NOT NULL'
										), 
					WPPA_EXIF => array(
											'id' => 'bigint(20) NOT NULL',
											'photo' => 'bigint(20) NOT NULL',
											'tag' => 'tinytext NOT NULL',
											'description' => 'text NOT NULL',
											'status' => 'tinytext NOT NULL'
										),
					WPPA_INDEX => array(
											'id' => 'bigint(20) NOT NULL',
											'slug' => 'tinytext NOT NULL',
											'albums' => 'text NOT NULL',
											'photos' => 'text NOT NULL'
										)
				);
	$errtxt = '';
	$idx = 0;
	while ($idx < 7) {
		// Test existence of table
		$ext = wppa_table_exists($tn[$idx]);
		if ( ! $ext ) {
			if ($verbose) wppa_error_message(__('Unexpected error:', 'wppa').' '.__('Missing database table:', 'wppa').' '.$tn[$idx], 'red', 'force');
			$any_error = true;
		}
		// Test columns
		else {
			$tablefields = $wpdb->get_results("DESCRIBE {$tn[$idx]};", "ARRAY_A");
			// unset flags for found fields
			foreach ( $tablefields as $field ) {					
				if ( isset( $flds[$tn[$idx]][$field['Field']] )) unset( $flds[$tn[$idx]][$field['Field']] );
			}
			// Fields left?
			if ( is_array($flds[$tn[$idx]]) ) foreach ( array_keys($flds[$tn[$idx]]) as $field ) {
				$errtxt .= '<tr><td>'.$tn[$idx].'</td><td>'.$field.'</td><td>'.$flds[$tn[$idx]][$field].'</td></tr>';
			}
		}
		$idx++;
	}
	if ( $errtxt ) {
		$fulltxt = 'The latest update failed to update the database tables required for wppa+ to function properly<br /><br />';
		$fulltxt .= 'Make sure you have the rights to issue SQL commands like <i>"ALTER TABLE tablename ADD COLUMN columname datatype"</i> and run the action on <i>Table VIII-A1</i> on the Photo Albums -> Settings admin page.<br /><br />';
		$fulltxt .= 'The following table lists the missing columns:';
		$fulltxt .= '<br /><table id="wppa-err-table"><thead style="font-weight:bold;"><tr><td>Table name</td><td>Column name</td><td>Data type</td></thead>';
		$fulltxt .= $errtxt;
		$fulltxt .= '</table><b>';
		if ($verbose) wppa_error_message( $fulltxt, 'red', 'force' );
		$any_error = true;
	}
	// Check directories
	$dn = array( ABSPATH.WPPA_UPLOAD, WPPA_UPLOAD_PATH, WPPA_UPLOAD_PATH.'/thumbs', WPPA_DEPOT_PATH);
	$idx = 0;
	while ($idx < 4) {
		if ( ! file_exists($dn[$idx]) ) {	// First try to repair
			@ mkdir($dn[$idx]);
			@ chmod($dn[$idx], 0755);
		}
		else {
			@ chmod($dn[$idx], 0755);		// there are always people who destruct things
		}
		
		if ( ! file_exists($dn[$idx]) ) {	// Test again
			if ($verbose) wppa_error_message(__('Unexpected error:', 'wppa').' '.__('Missing directory:', 'wppa').' '.$dn[$idx], 'red', 'force');
			$any_error = true;
		}
		elseif ( ! is_writable($dn[$idx]) ) {
			if ($verbose) wppa_error_message(__('Unexpected error:', 'wppa').' '.__('Directory is not writable:', 'wppa').' '.$dn[$idx], 'red', 'force');
			$any_error = true;
		}
		elseif ( ! is_readable($dn[$idx]) ) {
			if ($verbose) wppa_error_message(__('Unexpected error:', 'wppa').' '.__('Directory is not readable:', 'wppa').' '.$dn[$idx], 'red', 'force');
			$any_error = true;
		}
		$idx++;
	}
	if ( $any_error ) {
		if ($verbose) wppa_error_message(__('Please de-activate and re-activate the plugin. If this problem persists, ask your administrator.', 'wppa'), 'red', 'force');
	}
	
	return ! $any_error;	// True = no error
}

function wppa_has_children($alb) {
global $wpdb;

	return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_ALBUMS."` WHERE `a_parent` = %s", $alb) );
}

function wppa_admin_page_links($curpage, $pagesize, $count, $link, $extra = '') {

	if ( $pagesize < '1' ) return;	// Pagination is off
	
	$prevpage 	= $curpage - '1';
	$nextpage 	= $curpage + '1'; 
	$prevurl 	= $link.'&wppa-page='.$prevpage.$extra;
	$pagurl 	= $link.'&wppa-page=';
	$nexturl 	= $link.'&wppa-page='.$nextpage.$extra;
	$npages 	= ceil($count / $pagesize);

	if ($npages > '1') {
		if ($curpage != '1') {
			?><a href="<?php echo($prevurl) ?>"><?php _e('Prev page', 'wppa') ?></a><?php
		}
		$i = '1';
		while ($i <= $npages) {
			if ($i == $curpage) {
				echo(' '.$i.' ');
			}
			else {
				?>&nbsp;<a href="<?php echo($pagurl.$i.$extra) ?>"><?php echo($i) ?></a>&nbsp;<?php
			}
			$i++;
		}
		if ($curpage != $npages) {
			?><a href="<?php echo($nexturl) ?>"><?php _e('Next page', 'wppa') ?></a><?php
		}
	}
}

function wppa_update_single_photo($file, $id, $name) {
global $wpdb;

	$photo = $wpdb->get_row($wpdb->prepare( "SELECT `id`, `name`, `ext`, `album`, `filename` FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $id), ARRAY_A );
	wppa_make_the_photo_files($file, $id, $photo['ext']);
	wppa_save_source($file, $name, $photo['album']);
	$wpdb->query($wpdb->prepare('UPDATE `'.WPPA_PHOTOS.'` SET `filename` = %s WHERE `id` = %s', $name, $photo['id']));
	wppa_update_modified($photo['id']);
	wppa_dbg_msg('Update single photo: '.$name.' in album '.$photo['album'], 'green');
}

function wppa_update_photo($file, $xname) {
global $wpdb;
global $allphotos;

	if ($xname == '') $name = basename($file);
	else $name = __($xname);
	
//echo 'Trying: '.$name.' ';
	$photos = $wpdb->get_results($wpdb->prepare( "SELECT `id`, `name`, `ext`, `album`, `filename` FROM `".WPPA_PHOTOS."` WHERE `filename` = %s OR ( `filename` = '' AND `name` = %s )", $name, $name), ARRAY_A );
	if ( $photos ) {
		foreach ( $photos as $photo ) {
//echo 'found:'.count($photos);
			wppa_make_the_photo_files($file, $photo['id'], $photo['ext']);
			$album = $wpdb->get_var($wpdb->prepare('SELECT `album` FROM '.WPPA_PHOTOS.' WHERE `id` = %s', $photo['id']));
//echo ' album='.$album.'<br/>';
			wppa_save_source($file, basename($file), $album);
			$wpdb->query($wpdb->prepare('UPDATE `'.WPPA_PHOTOS.'` SET `filename` = %s WHERE `id` = %s', $name, $photo['id']));
			wppa_dbg_msg('Update photo: '.$name.' in album '.$album, 'green');
		}
		return count($photos);
	}
	return false;
}

function wppa_insert_photo( $file = '', $alb = '', $name = '', $desc = '', $porder = '0', $id = '0', $linkurl = '', $linktitle = '' ) {
global $wpdb;
global $warning_given_small;
global $wppa_opt;
global $album;
global $wppa;
	
	wppa_cache_album($alb);
	
	if ( ! wppa_allow_uploads($alb) ) {
		if ( is_admin() && ! $wppa['ajax'] ) {
			wppa_error_message(sprintf(__('Album %s is full', 'wppa'), wppa_get_album_name($alb)));
		}
		else {
			wppa_err_alert(sprintf(__('Album %s is full', 'wppa'), wppa_get_album_name($alb)));
		}
		return false;
	}

	if ($file != '' && $alb != '' ) {
		// Get the name if not given
		if ($name == '') $name = basename($file);
		// Sanitize name
		$name = htmlspecialchars(strip_tags($name));
		
		// If not dups allowed and its already here, quit
		if ( isset($_POST['wppa-nodups']) ) {
			$exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_PHOTOS."` WHERE `album` = %s AND ( `filename` = %s OR ( `filename` = '' AND `name` = %s ) )", $alb, $name, $name));
			if ( $exists ) {
				if ( isset($_POST['del-after-p']) ) {
					unlink($file);
					$msg = __('Photo %s already exists in album number %s. Removed from depot.', 'wppa');
				}
				else {
					$msg = __('Photo %s already exists in album number %s.', 'wppa');
				}
				wppa_warning_message(sprintf($msg, $name, $alb));
				
				return false;
			}
		}
		
		// Get and verify the size
		$img_size = getimagesize($file);
		
		if ($img_size) { 
			if ( wppa_check_memory_limit('', $img_size['0'], $img_size['1'] ) === false ) { 
				wppa_error_message(sprintf(__('ERROR: Attempt to upload a photo that is too large to process (%s).', 'wppa'), $name).wppa_check_memory_limit());
				return false;
			}
			if (!$warning_given_small && ($img_size['0'] < wppa_get_minisize() && $img_size['1'] < wppa_get_minisize())) {
				wppa_warning_message(__('WARNING: You are uploading photos that are too small. Photos must be larger than the thumbnail size and larger than the coverphotosize.', 'wppa'));
				$warning_given_small = true;
			}
		}
		else {
			wppa_error_message(__('ERROR: Unable to retrieve image size of', 'wppa').' '.$name.' '.__('Are you sure it is a photo?', 'wppa'));
			return false;
		}
		// Get ext based on mimetype, regardless of ext
		switch($img_size[2]) { 	// mime type
			case 1: $ext = 'gif'; break;
			case 2: $ext = 'jpg'; break;
			case 3: $ext = 'png'; break;
			default:
				wppa_error_message(__('Unsupported mime type encountered:', 'wppa').' '.$img_size[2].'.');
				return false;
		}
		// Get an id if not yet there
		if ($id == '0') {
			$id = wppa_nextkey(WPPA_PHOTOS);
		}
		// Get opt deflt desc if empty
		if ( $desc == '' && $wppa_opt['wppa_apply_newphoto_desc'] == 'yes' ) {
			$desc = stripslashes($wppa_opt['wppa_newphoto_description']);
		}
		// Reset rating
		$mrat = '0';
		// Find (new) owner
		$owner = wppa_get_user();
		// Validate album
		if ( !is_numeric($alb) || $alb < '1' ) {
			wppa_error_message(__('Album not known while trying to add a photo', 'wppa'));
			return false;
		}
		if ( !wppa_have_access($alb) ) {
			wppa_error_message(sprintf(__('Album %s does not exist or is not accessable while trying to add a photo', 'wppa'), $alb));
			return false;
		}
		// Add photo to db
		$status = ( $wppa_opt['wppa_upload_moderate'] == 'yes' && !current_user_can('wppa_admin') ) ? 'pending' : 'publish';
//		$linktarget = '_self';
		$filename = $name;

//		if ( wppa_switch('wppa_strip_file_ext') ) {
//			$name = preg_replace('/\.[^.]*$/', '', $name);
//		}
//		$query = $wpdb->prepare('INSERT INTO `' . WPPA_PHOTOS . '` (`id`, `album`, `ext`, `name`, `p_order`, `description`, `mean_rating`, `linkurl`, `linktitle`, `linktarget`, `timestamp`, `owner`, `status`, `tags`, `alt`, `filename`, `modified`, `location`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, \'0\', \'\' )', 
//$id, $alb, $ext, $name, $porder, $desc, $mrat, $linkurl, $linktitle, $linktarget, time(), $owner, $status, $album['default_tags'], '', $filename);
//		if ($wpdb->query($query) === false) {

		$id = wppa_create_photo_entry( array( 'id' => $id, 'album' => $alb, 'ext' => $ext, 'name' => $name, 'p_order' => $porder, 'description' => $desc, 'linkurl' => $linkurl, 'linktitle' => $linktitle,  'owner' => $owner, 'status' => $status, 'filename' => $filename) );
		if ( ! $id ) {
			wppa_error_message(__('Could not insert photo. query=', 'wppa').$query);
		}
		else {	// Save the source
			wppa_save_source($file, $filename, $alb);
			wppa_flush_treecounts($alb);
			wppa_update_album_timestamp($alb);
			wppa_flush_upldr_cache('photoid', $id);
		}
		// Make the photo files		
		if ( wppa_make_the_photo_files( $file, $id, $ext ) ) {
			// Repair photoname if not supplied and not standard
			wppa_set_default_name( $id );
			// Tags
			wppa_set_default_tags( $id );
			// Index
			wppa_index_add( 'photo', $id );
			// Done!
			return $id;
		}
	}
	else {
		wppa_error_message(__('ERROR: Unknown file or album.', 'wppa'));
		return false;
	}
}
