<?php 
/* wppa_upload.php
* Package: wp-photo-album-plus
*
* Contains all the upload/import pages and functions
* Version 2.3.2
*/

function wppa_page_upload() {
		// upload images
        // sanitize system
		wppa_cleanup_photos('0');
		// Check if a message is required
		wppa_check_update();

		if (isset($_POST['wppa-upload'])) {
			wppa_check_admin_referer( '$wppa_nonce', WPPA_NONCE );
            
			wppa_upload_photos();
		}
?>
		<div class="wrap">
			<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/camera32.png'; ?>
			<div id="icon-camera" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
				
			</div>
			<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/arrow32.png'; ?>
			<div id="icon-arrow" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
			</div>
			<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/album32.png'; ?>
			<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
				<br />
			</div>
		
			<h2><?php _e('Upload Photos', 'wppa'); ?></h2><br />
			<?php		
			// chek if albums exist before allowing upload
			if(wppa_has_albums()) { ?>
				<form enctype="multipart/form-data" action="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=upload_photos" method="post">
				<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE); ?>
					<input id="my_file_element" type="file" name="file_1" />
					<div id="files_list">
						<h3><?php _e('Selected Files:', 'wppa'); ?> <small><?php _e('You can upload up to 15 photos at once.', 'wppa'); ?></small></h3>
					</div>
					<p>
						<label for="wppa-album"><?php _e('Album:', 'wppa'); ?> </label>
						<select name="wppa-album" id="wppa-album"><?php echo(wppa_album_select()); ?></select>
					</p>
					<input type="submit" class="button-primary" name="wppa-upload" value="<?php _e('Upload Photos', 'wppa') ?>" />					
				</form>
				<br />
				<script type="text/javascript">
				<!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->
					var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), 15 );
				<!-- Pass in the file element -->
					multi_selector.addElement( document.getElementById( 'my_file_element' ) );
				</script>
			<?php } 
			else { ?>
				<p><?php _e('No albums exist. You must', 'wppa'); ?> <a href="admin.php?page=<?php echo(WPPA_PLUGIN_PATH) ?>/wppa.php"><?php _e('create one', 'wppa'); ?></a> <?php _e('beofre you can upload your photos.', 'wppa'); ?></p>
<?php } ?>
		</div>
<?php
}

function wppa_page_import() {
	// import images
	// sanitize system
    wppa_cleanup_photos('0');
	// Check if a message is required
	wppa_check_update();

	if (isset($_POST['wppa-import-submit'])) {
		wppa_check_admin_referer( '$wppa_nonce', WPPA_NONCE );
        if (isset($_POST['del-after'])) $del = true; else $del = false; 
		wppa_import_photos($del);
	}
?>
	<div class="wrap">
		<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/camera32.png'; ?>
		<div id="icon-camera" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat"></div>
		<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/arrow32.png'; ?>
		<div id="icon-arrow" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat"></div>
		<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/album32.png'; ?>
		<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat"><br /></div>
		
		<h2><?php _e('Import Photos', 'wppa'); ?></h2><br />
<?php		
		// chek if albums exist before allowing upload
		if(wppa_has_albums()) { 

		$depot = ABSPATH . 'wp-content/wppa-depot';
		$depoturl = get_bloginfo('url').'/wp-content/wppa-depot';
		
		if (!is_dir($depot)) {
			if (!mkdir($depot)) wppa_error_message(__('Unable to create depot directory', 'wppa').'<br>'.__('Create', 'wppa').' '.$depot.' '.__('with an ftp program and place the photos there.', 'wppa'));
			else wppa_ok_message(__('Place your photos to be imported in:', 'wppa').' '.$depoturl.'/ '.__('using an FTP program and try again.', 'wppa'));
		}
		$paths = ABSPATH . 'wp-content/wppa-depot/*.*';
		$files = glob($paths);
		$photocount = wppa_get_photocount($files);
	
		if ($photocount > '0') {
			$idx = '0';
?>
			<form action="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=import_photos" method="post">
			<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE); ?>
			<p><?php _e('There are', 'wppa'); echo(' '.$photocount.' '); _e('photos in the depot.', 'wppa'); if (get_option('wppa_resize_on_upload', 'no') == 'yes') { echo(' '); _e('Photos will be downsized during import.', 'wppa'); } ?></p>
				<label for="wppa-album"><?php _e('Import photos to album:', 'wppa'); ?> </label>
				<select name="wppa-album" id="wppa-album"><?php echo(wppa_album_select()); ?></select>
				<label for="del-after"><?php _e('Delete after successfull import:', 'wppa'); ?> </label>
				<input type="checkbox" name="del-after" checked="checked" />
			</p>
			<p>
				<input type="submit" class="button-primary" name="wppa-import-submit" value="<?php _e('Import', 'wppa'); ?>" />
			</p>
			<br />
				<table class="form-table albumtable">
					<thead>
					</thead>
					<tbody>
						<tr>
<?php
							$ct = 0;
							foreach ($files as $file) {
								$ext = strtolower(substr(strrchr($file, "."), 1));
								if ($ext == 'jpg' || $ext == 'png' || $ext == 'gif') {
?>
									<td>
										<input type="checkbox" name="file-<?php echo($idx); ?>" checked="checked" />&nbsp;&nbsp;<?php echo(basename($file)); ?>
									</td>
<?php 								if ($ct == 4) {
										echo('</tr><tr>'); 
										$ct = 0;
									}
									else {
										$ct++;
									}
								}
								$idx++;
							}
?>
						</tr>
					</tbody>
				</table>
			<p>
				<input type="submit" class="button-primary" name="wppa-import-submit" value="<?php _e('Import', 'wppa'); ?>" />
			</p>
			</form>
<?php
		}
		else {
			wppa_ok_message(__('There are no photos in depot:', 'wppa').' '.$depoturl);
		}
	}
	else { ?>
				<p><?php _e('No albums exist. You must', 'wppa'); ?> <a href="admin.php?page=<?php echo(WPPA_PLUGIN_PATH) ?>/wppa.php"><?php _e('create one', 'wppa'); ?></a> <?php _e('beofre you can upload your photos.', 'wppa'); ?></p>
<?php } ?>
	</div>
<?php
}


// Upload photos 
function wppa_upload_photos() {
	global $wpdb;
	global $warning_given;

	wppa_cleanup_photos('0');
	
	$warning_given = false;

	$wppa_dir = ABSPATH . 'wp-content/uploads/wppa';
	
	if (!defined('WP_DEBUG')) define('WP_DEBUG', true);	

	// check if wppa dir exists
	if (!is_dir($wppa_dir)) {
		mkdir($wppa_dir);	
	}
	
	// check if thumbs dir exists 
	if (!is_dir($wppa_dir . '/thumbs')) {
		mkdir($wppa_dir . '/thumbs');
	}
	
	$count = '0';
	foreach ($_FILES as $file) {
		if ($file['tmp_name'] != '') {
			if (wppa_insert_photo($file['tmp_name'], $_POST['wppa-album'], $file['name'])) {
				$uploaded_a_file = true;
				$count++;
			}
			else {
				wppa_error_message(__('Error inserting photo', 'wppa') . ' ' . basename($file['tmp_name']) . '.');
				return;
			}
		}
	}
	
	if ($uploaded_a_file) { 
		wppa_update_message($count.' '.__('Photos Uploaded in album nr', 'wppa') . ' ' . $_POST['wppa-album']);
		wppa_set_last_album($_POST['wppa-album']);
    }
}

function wppa_import_photos($del_after_import = false) {
	global $warning_given;
	
	wppa_cleanup_photos('0');
	
	$warning_given = false;
	$paths = ABSPATH . 'wp-content/wppa-depot/*.*';
	
	if (!defined('WP_DEBUG')) define('WP_DEBUG', true);	
	
	$files = glob($paths);
	$idx='0';
	if (isset($_POST['wppa-album'])) $album = $_POST['wppa-album']; else $album = '0';
	if ($album > '0') {
		$count = '0';
		wppa_ok_message(__('Processing files, please wait...', 'wppa').' '.__('If the line of dots stops growing or you browser reports Ready, your server has given up. In that case: try again', 'wppa').' <a href="'.get_option('siteurl').'/wp-admin/admin.php?page=import_photos">'.__('here.', 'wppa').'</a>');
		foreach ($files as $file) {
			if (isset($_POST['file-'.$idx])) {
				if (wppa_insert_photo($file, $album)) {
					$count++;
					if ($del_after_import) {
						unlink($file);
					}
				}
				else {
					wppa_error_message(__('Error inserting photo', 'wppa') . ' ' . basename($file) . '.');
				}
			}
			$idx++;
		}
		if ($count == '0') {
			wppa_error_message(__('No files to import.', 'wppa'));
		}
		else {
			wppa_ok_message($count . ' ' . __('files imported to album number', 'wppa') . ' ' . $album); 
			wppa_set_last_album($album);
		}
	}
	else {
		wppa_error_message(__('No known valid album id to import photos to.', 'wppa'));
	}
}

function wppa_insert_photo ($file = '', $album = '', $name = '') {
	global $wpdb;
	global $warning_given;
	
	if ($name == '') $name = basename($file);
	if ($file != '' && $album != '' ) {
		$img_size = getimagesize($file);
	print_r($img_size);
		if ($img_size) { 
			if (!$warning_given && ($img_size['0'] > 1280 || $img_size['1'] > 1280)) {
				if (get_option('wppa_resize_on_upload', 'no') == 'yes') {
					wppa_ok_message(__('Although the photos are resized during the upload/import process, you may encounter \'Out of memory\'errors.', 'wppa') . '<br/>' . __('In that case: make sure you set the memory limit to 64M and make sure your hosting provider allows you the use of 64 Mb.', 'wppa'));
				}
				else {
					wppa_warning_message(__('WARNING You are uploading very large photos, this may result in server problems and excessive download times for your website visitors.', 'wppa') . '<br/>' . __('Check the \'Resize on upload\' checkbox, and/or resize the photos before uploading. The recommended size is: not larger than 1024 x 768 pixels (up to approx. 250 kB).', 'wppa'));
				}
				$warning_given = true;
			}
		}
		else return false;
		
		$ext = substr(strrchr($name, "."), 1);
			
		$query = $wpdb->prepare('INSERT INTO `' . PHOTO_TABLE . '` (`id`, `album`, `ext`, `name`, `p_order`, `description`) VALUES (0, %d, %s, %s, 0, \'\')', $album, $ext, $name);
		$wpdb->query($query);

		$image_id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
				
		$newimage = ABSPATH . 'wp-content/uploads/wppa/' . $image_id . '.' . $ext;
			
		if (get_option('wppa_resize_on_upload', 'no') == 'yes') {
			require_once('wppa_class_resize.php');
			$dir = $img_size[0] > $img_size[1] ? 'W' : 'H';
			$siz = get_option('wppa_fullsize', '800');
			$s = $img_size[0] > $img_size[1] ? $img_size[0] : $img_size[1];
			if ($s > $siz) {	
				$objResize = new wppa_ImageResize($file, $newimage, $dir, $siz);
			}
			else {
				copy($file, $newimage);
			}
		}
		else {
			copy($file, $newimage);
		}

		if (is_file ($newimage)) {
			$thumbsize = wppa_get_minisize();
			wppa_create_thumbnail($newimage, $thumbsize, '' );
		} 
		else {
			return false;
		}
		echo('.');
		return true;
	}
	else return false;
}

function wppa_get_photocount($files) {
	$result = 0;
	foreach ($files as $file) {
		$ext = strtolower(substr(strrchr($file, "."), 1));
		if ($ext == 'jpg' || $ext == 'png' || $ext == 'gif') $result++;
	}
	return $result;
}

// Remove photo entries that have no fullsize image or thumbnail
function wppa_cleanup_photos($alb = '') {
	global $wpdb;
	if ($alb == '') $alb = wppa_get_last_album();
	if (!is_numeric($alb)) return;

	$no_photos = '';
	if ($alb == '0') wppa_ok_message(__('Checking database, please wait...', 'wppa'));
	$delcount = 0;
	if ($alb == '0') $entries = $wpdb->get_results('SELECT id, ext FROM '.PHOTO_TABLE, ARRAY_A);
	else $entries = $wpdb->get_results('SELECT id, ext, name FROM '.PHOTO_TABLE.' WHERE album = '.$alb, ARRAY_A);
	if ($entries) {
		foreach ($entries as $entry) {
			$thumbpath = ABSPATH.'wp-content/uploads/wppa/thumbs/'.$entry['id'].'.'.$entry['ext'];
			$imagepath = ABSPATH.'wp-content/uploads/wppa/'.$entry['id'].'.'.$entry['ext'];
			if (!is_file($thumbpath)) {	// No thumb: delete fullimage
				if (is_file($imagepath)) unlink($imagepath);
				$no_photos .= ' '.$entry['name'];
			}
			if (!is_file($imagepath)) { // No fullimage: delete db entry
				if ($wpdb->query($wpdb->prepare('DELETE FROM `'.PHOTO_TABLE.'` WHERE `id` = %d LIMIT 1', $entry['id']))) {
					$delcount++;
				}
			}
		}
	}
	// Now fix missing exts for upload bug in 2.3.0
	$fixcount = 0;
	$entries = $wpdb->get_results('SELECT id, ext, name FROM '.PHOTO_TABLE.' WHERE ext = ""', ARRAY_A);
	if ($entries) {
		wppa_ok_message(__('Trying to fix '.count($entries).' entries with missing file extension, Please wait.', 'wppa'));
		foreach ($entries as $entry) {
			$tp = ABSPATH.'wp-content/uploads/wppa/'.$entry['id'].'.';
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
				if ($img[2] == 4) $ext = 'png';
			}
			if ($ext == 'jpg' || $ext == 'JPG' || $ext == 'png' || $ext == 'PNG' || $ext == 'gif' || $ext == 'GIF') {
				
				if ($wpdb->query('UPDATE '.PHOTO_TABLE.' SET ext = "'.$ext.'" WHERE id = '.$entry['id'])) {
					$oldimg = ABSPATH.'wp-content/uploads/wppa/'.$entry['id'].'.';
					$newimg = ABSPATH.'wp-content/uploads/wppa/'.$entry['id'].'.'.$ext;
					if (is_file($oldimg)) {
						copy($oldimg, $newimg);
						unlink($oldimg);
					}
					$oldimg = ABSPATH.'wp-content/uploads/wppa/thumbs/'.$entry['id'].'.';
					$newimg = ABSPATH.'wp-content/uploads/wppa/thumbs/'.$entry['id'].'.'.$ext;
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
	// End ext fix
	if ($delcount > 0){
		wppa_ok_message(__('Database fixed.', 'wppa').' '.$delcount.' '.__('invalid entries remooved:', 'wppa').$no_photos);
	}
	if ($fixcount > 0) {
		wppa_ok_message(__('Database fixed.', 'wppa').' '.$fixcount.' '.__('missing file extensions recovered.', 'wppa'));
	}
}

?>