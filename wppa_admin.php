<?php 
/* wppa_admin.php
* Package: wp-photo-album-plus
*
* Contains all the admin pages
* Version 2.0.0
*/

/* Add admin style */
add_action('admin_init', 'wppa_admin_styles');

function wppa_admin_styles() {
	wp_register_style('wppa_admin_style', '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/admin_styles.css');
	wp_enqueue_style('wppa_admin_style');
}

/* Add java scripts */
add_action('admin_init', 'wppa_admin_scripts');

function wppa_admin_scripts() {
	wp_register_script('wppa_upload_script', '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/multifile_compressed.js');
	wp_enqueue_script('wppa_upload_script');
	wp_register_script('wppa_admin_script', '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/admin_scripts.js');
	wp_enqueue_script('wppa_admin_script');
	wp_enqueue_script('jquery');
}

function wppa_admin() {
	global $wpdb;
	
	// Check if a message is required
	wppa_check_update();
	
	// warn if the uploads directory is no writable
	if (!is_writable(ABSPATH . 'wp-content/uploads')) { 
		wppa_error_message(__('Warning:', 'wppa') . __('The uploads directory does not exist or is not writable by the server. Please make sure that <tt>wp-content/uploads/</tt> is writeable by the server.', 'wppa'));
	}

if (isset($_GET['tab'])) {		
	// album edit page
	if ($_GET['tab'] == 'edit'){
	
		// updates the details
		if (isset($_POST['wppa-ea-submit'])) {
			wppa_check_admin_referer( '$wppa_nonce', WPPA_NONCE );
			wppa_edit_album();
		}
		
		// deletes the image
		if (isset($_GET['photo_del'])) {
			$message = __('Photo Deleted.', 'wppa');
			
			$ext = $wpdb->get_var($wpdb->prepare('SELECT `ext` FROM `' . PHOTO_TABLE . '` WHERE `id` = %d', $_GET['photo_del'])); 
			
			$file = ABSPATH . 'wp-content/uploads/wppa/' . $_GET['photo_del'] . '.' . $ext;
			if (file_exists($file)) {
				unlink($file);
			}
			else {
				$message .= ' ' . __('Fullsize image did not exist.', 'wppa');
			}
			
			$file = ABSPATH . 'wp-content/uploads/wppa/thumbs/' . $_GET['photo_del'] . '.' . $ext;
			if (file_exists($file)) {
				unlink($file);
			}
			else {
				$message .= ' ' . __('Thumbnail image did not exist.', 'wppa');
			}
			
			$wpdb->query($wpdb->prepare('DELETE FROM `' . PHOTO_TABLE . '` WHERE `id` = %d LIMIT 1', $_GET['photo_del']));

			wppa_update_message($message);
		}		
		
		$albuminfo = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . ALBUM_TABLE . '` WHERE `id` = %d', $_GET['edit_id']), 'ARRAY_A');
?>				
		<div class="wrap">
			<h2><?php _e('Edit Ablum Information', 'wppa'); ?></h2>
			<p><?php _e('Album number:', 'wppa'); echo(' ' . $_GET['edit_id'] . '.'); ?></p>
			<form action="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=<?php echo(WPPA_PLUGIN_PATH) ?>/wppa.php&amp;tab=edit&amp;edit_id=<?php echo($_GET['edit_id']) ?>" method="post">
			<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE); ?>

				<table class="form-table albumtable">
					<tbody>
						<tr valign="top">
							<th scope="row">
								<label ><?php _e('Name:', 'wppa'); ?></label>
							</th>
							<td>
								<input type="text" name="wppa-name" id="wppa-name" style="width: 300px;" value="<?php echo(stripslashes($albuminfo['name'])) ?>" />
								<span class="description"><br/><?php _e('Type the name of the album. Do not leave this empty.', 'wppa'); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label ><?php _e('Description:', 'wppa'); ?></label>
							</th>
							<td>
								<textarea rows="5" cols="40" name="wppa-desc" id="wppa-desc"><?php echo(stripslashes($albuminfo['description'])) ?></textarea>
								<span class="description"><br/><?php _e('Enter / modify the description for this album.', 'wppa'); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label ><?php _e('Sort order #:', 'wppa'); ?></label>
							</th>
							<td>
								<input type="text" name="wppa-order" id="wppa-order" value="<?php echo($albuminfo['a_order']) ?>" style="width: 50px;"/>
								<span class="description"><br/><?php _e('If you want to sort the albums by order #, enter / modify the order number here.', 'wppa'); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label ><?php _e('Parent album:', 'wppa'); ?> </label>
							</th>
							<td>
								<select name="wppa-parent"><?php echo(wppa_album_select($albuminfo["id"], $albuminfo["a_parent"], TRUE, TRUE, TRUE)) ?></select>
								<span class="description">
									<br/><?php _e('If this is a sub album, select the album in which this album will appear.', 'wppa'); ?>
								</span>					
							</td>
						</tr>
						<tr valign="top">
							<th>
								<?php $order = $albuminfo['p_order_by']; ?>
								<label ><?php _e('Photo order:', 'wppa'); ?></label>
							</th>
							<td>
								<select name="wppa-list-photos-by"><?php wppa_order_options($order, __('--- default ---', 'wppa')) ?></select>
								<span class="description">
									<br/><?php _e('Specify the way the photos should be ordered in this album.', 'wppa'); ?>
									<br/><?php _e('The default setting can be changed in the Options page.', 'wppa'); ?>
								</span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label ><?php _e('Cover Photo:', 'wppa'); ?></label>
							</th>
							<td>
								<?php echo(wppa_main_photo($albuminfo['main_photo'])) ?>
								<span class="description"><br/><?php _e('Select the photo you want to appear on the cover of this album.', 'wppa'); ?></span>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label ><?php _e('Link to:', 'wppa'); ?></label>
							</th>
							<td>
<?php
	$query = 'SELECT `ID`, `post_title` FROM `' . $wpdb->posts . '` WHERE `post_type` = \'page\' AND `post_status` = \'publish\' ORDER BY `post_title` ASC';
	$pages = $wpdb->get_results ($query, 'ARRAY_A');
								if (empty($pages)) {
									_e('There are no pages (yet) to link to.', 'wppa');
								} else {
									$linkpage = $albuminfo['cover_linkpage'];
									if (!is_numeric($linkpage)) $linkpage = '0';
									$sel = 'selected="selected"';
?>
									<select name="cover-linkpage" id="cover-linkpage" >
										<option value="0" <?php if ($linkpage == '0') echo($sel); ?>><?php _e('--- the album\'s content ---', 'wppa'); ?></option>
<?php
										foreach ($pages as $page) { ?>
											<option value="<?php echo($page['ID']); ?>" <?php if ($linkpage == $page['ID']) echo($sel); ?>><?php echo($page['post_title']); ?></option>
										<?php } ?>
									</select>
									<span class="description">
										<br/><?php _e('If you want, you can link the title and the coverphoto to a WP page in stead of the album\'s content. If so, select the page the cover photo links to.', 'wppa'); ?>
									</span>
<?php
								}							
?>
							</td>
						</tr>

						
					</tbody>
				</table>

				<p>
					<input type="submit" class="button-primary" name="wppa-ea-submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
				</p>
				<br />
		
				<h2><?php _e('Manage Photos', 'wppa'); ?></h2>
				<p>
					<input type="submit" class="button-primary" name="wppa-ea-submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
				</p>
			
				<?php wppa_album_photos($_GET['edit_id']) ?>
		
				<p>
					<input type="submit" class="button-primary" name="wppa-ea-submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
				</p>
		
			</form>
		</div>
<?php	
	// album delete confirm page
	} else if ($_GET['tab'] == 'del'){ ?>
		
		<div class="wrap">
			<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/albumdel32.png'; ?>
			<div id="icon-albumdel" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
				<br />
			</div>

			<h2><?php _e('Delete Album', 'wppa'); ?></h2>
			
			<p><?php _e('Album:', 'wppa'); ?> <b><?php wppa_album_name($_GET['id']); ?>.</b></p>
			<p><?php _e('Are you sure you want to delete this album?', 'wppa'); ?><br />
				<?php _e('Press Delete to continue, and Cancel to go back.', 'wppa'); ?>
			</p>
			<form name="wppa-del-form" action="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=<?php echo(WPPA_PLUGIN_PATH) ?>/wppa.php" method="post">
				<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE) ?>
				<p>
					<?php _e('What would you like to do with photos currently in the album?', 'wppa'); ?><br />
					<input type="radio" name="wppa-del-photos" value="delete" checked="checked" /> <?php _e('Delete', 'wppa'); ?><br />
					<input type="radio" name="wppa-del-photos" value="move" /> <?php _e('Move to:', 'wppa'); ?> 
					<select name="wppa-move-album"><?php echo(wppa_album_select($_GET['id'])) ?></select>
				</p>
			
				<input type="hidden" name="wppa-del-id" value="<?php echo($_GET['id']) ?>" />
				<input type="button" class="button-primary" value="<?php _e('Cancel', 'wppa'); ?>" onclick="parent.history.back()" />
				<input type="submit" class="button-primary" style="color: red" name="wppa-del-confirm" value="<?php _e('Delete', 'wppa'); ?>" />
			</form>
		</div>

<?php	
	}
	// default, album manage page.
	} else {
		
		// if add form has been submitted
		if (isset($_POST['wppa-na-submit'])) {
			wppa_check_admin_referer( '$wppa_nonce', WPPA_NONCE );
			wppa_add_album();
		}
		
		// if album deleted
		if (isset($_POST['wppa-del-confirm'])) {
			wppa_check_admin_referer( '$wppa_nonce', WPPA_NONCE );

			if ($_POST['wppa-del-photos'] == 'move') {
				$move = $_POST['wppa-move-album'];
			} else {
				$move = '';
			}
			wppa_del_album($_POST['wppa-del-id'], $move);
		}
?>		
		<div class="wrap">
			<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/album32.png'; ?>
			<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
				<br />
			</div>

			<h2><?php _e('Manage Albums', 'wppa'); ?></h2>
			<?php wppa_admin_albums() ?>
			
			<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/albumnew32.png'; ?>
			<div id="icon-albumnew" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
				<br />
			</div>

			<h2><?php _e('Create New Album', 'wppa'); ?></h2>
			<form action="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=<?php echo(WPPA_PLUGIN_PATH) ?>/wppa.php" method="post">
			<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE) ?>
				<table class="form-table albumtable">
					<tbody>
						<tr valign="top">
							<th scope="row">
								<label ><?php _e('Name:', 'wppa'); ?></label>
							</th>
							<td>
								<input type="text" name="wppa-name" id="wppa-name" />
								<span class="description"><br/><?php _e('Type the name of the new album. Do not leave this empty.', 'wppa'); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label ><?php _e('Description:', 'wppa'); ?></label>
							</th>
							<td>
								<textarea rows="5" cols="40" name="wppa-desc" id="wppa-desc"></textarea>
								<span class="description"><br/></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label ><?php _e('Order #:', 'wppa'); ?></label>
							</th>
							<td>
								<input type="text" name="wppa-order" id="wppa-order" style="width: 50px;"/>
								<span class="description"><br/><?php _e('If you want to sort the albums by order #, enter the order number here.', 'wppa'); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label ><?php _e('Parent album:', 'wppa'); ?></label>
							</th>
							<td>
								<select name="wppa-parent"><?php echo(wppa_album_select('', '', TRUE, TRUE)) ?></select>
								<span class="description"><br/><?php _e('If this is a sub album, select the album in which this album will appear.', 'wppa'); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label ><?php _e('Order photos by:', 'wppa'); ?></label>
							</th>
							<td>
								<select name="wppa-photo-order-by"><?php wppa_order_options('0', __('--- default ---', 'wppa')) ?></select>
								<span class="description"><br/><?php _e('If you want to sort the photos in this album different from the system setting, select the order method here.', 'wppa'); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<input type="submit" class="button-primary" name="wppa-na-submit" value="<?php _e('Create Album!', 'wppa'); ?>" />
							</th>
							<td>
								<span class="description"><?php _e('You can change all these settings later by clicking the "Edit" link in the table above.', 'wppa'); ?></span>
							</td>
						</tr>
					</tbody>
				</table>
			</form>	
		</div>
<?php	
	}
}

function wppa_page_upload() {
		// upload images
        
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

function wppa_page_options() {
	$options_error = false;
	
	// Check if a message is required
	wppa_check_update();

	if (isset($_POST['wppa-set-submit'])) {
		wppa_check_admin_referer( '$wppa_nonce', WPPA_NONCE );

		$old_minisize = wppa_get_minisize();
		
		if (wppa_check_numeric($_POST['wppa-thumbsize'], '50', __('Thumbnail size.'))) {
			if (get_option('wppa_thumbsize') != $_POST['wppa-thumbsize']) {
				update_option('wppa_thumbsize', $_POST['wppa-thumbsize']);
			}
		} else $options_error = true;
		
		if (wppa_check_numeric($_POST['wppa-smallsize'], '50', __('Cover photo size.'))) {
			if (get_option('wppa_smallsize') != $_POST['wppa-smallsize']) {
				update_option('wppa_smallsize', $_POST['wppa-smallsize']);
				
			}
		} else $options_error = true;
		
		if (!$options_error) {
			$new_minisize = wppa_get_minisize();
			if ($old_minisize != $new_minisize) update_option('wppa_lastthumb', '-1');	// restart making thumbnails
		}

		$start = get_option('wppa_lastthumb', '-2');
		if ($start != '-2') {
			$start++; 
?>
			<div id="message" class="updated fade">
				<p>
					<strong><?php _e('Regenerating thumbnail images, starting at', 'wppa'); ?> id=<?php echo($start) ?>. <?php _e('please wait.', 'wppa'); ?></strong><br/>
					<?php _e('If the line of dots stops growing and you do not get a <strong>READY</strong> message, please continue this action by clicking', 'wppa'); ?>
					<a href="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=options"> <?php _e('HERE', 'wppa'); ?></a> <?php _e('and click "Save Changes" again.', 'wppa'); ?>
				</p>
			</div>
<?php 			
			wppa_regenerate_thumbs(); 
			wppa_update_message(__('READY regenerating thumbnail images.', 'wppa')); 				
			update_option('wppa_lastthumb', '-2');
		}
		
		if (isset($_POST['wppa-thumbtype'])) update_option('wppa_thumbtype', $_POST['wppa-thumbtype']);
		
		update_option('wppa_valign', $_POST['wppa-valign']);
		update_option('wppa_fullvalign', $_POST['wppa-fullvalign']);
		
		if (wppa_check_numeric($_POST['wppa-min-thumbs'], '0', __('Photocount treshold.'))) {
			update_option('wppa_min_thumbs', $_POST['wppa-min-thumbs']);
		} else {
			$options_error = true;
		}
		
		if (wppa_check_numeric($_POST['wppa-fullsize'], '100', __('Full size.'))) {
			update_option('wppa_fullsize', $_POST['wppa-fullsize']);
		} else {
			$options_error = true;
		}
		
		if (isset($_POST['wppa-animation-speed'])) update_option('wppa_animation_speed', $_POST['wppa-animation-speed']);
		
		if (isset($_POST['wppa-use-thumb-opacity'])) update_option('wppa_use_thumb_opacity', 'yes');
		else update_option('wppa_use_thumb_opacity', 'no');
		if (wppa_check_numeric($_POST['wppa-thumb-opacity'], '0', __('Opacity.'), '100')) {
			update_option('wppa_thumb_opacity', $_POST['wppa-thumb-opacity']);
		} else {
			$options_error = true;
		}
		
		if (isset($_POST['wppa-use-thumb-popup'])) update_option('wppa_use_thumb_popup', 'yes');
		else update_option('wppa_use_thumb_popup', 'no');
		
		if (isset($_POST['wppa-use-cover-opacity'])) update_option('wppa_use_cover_opacity', 'yes');
		else update_option('wppa_use_cover_opacity', 'no');
		if (wppa_check_numeric($_POST['wppa-cover-opacity'], '0', __('Opacity.'), '100')) {
			update_option('wppa_cover_opacity', $_POST['wppa-cover-opacity']);
		} else {
			$options_error = true;
		}

		if (isset($_POST['wppa-enlarge'])) update_option('wppa_enlarge', 'yes');
		else update_option('wppa_enlarge', 'no');
		
		if (isset($_POST['wppa-list-albums-by'])) update_option('wppa_list_albums_by', $_POST['wppa-list-albums-by']);
		if (isset($_POST['wppa-list-albums-desc'])) update_option('wppa_list_albums_desc', 'yes');
		else update_option('wppa_list_albums_desc', 'no');
		
		if (isset($_POST['wppa-list-photos-by'])) update_option('wppa_list_photos_by', $_POST['wppa-list-photos-by']);
		if (isset($_POST['wppa-list-photos-desc'])) update_option('wppa_list_photos_desc', 'yes');
		else update_option('wppa_list_photos_desc', 'no');
		
		if (isset($_POST['wppa-show-bread'])) update_option('wppa_show_bread', 'yes');
		else update_option('wppa_show_bread', 'no');
		
		if (isset($_POST['wppa-show-home'])) update_option('wppa_show_home', 'yes');
		else update_option('wppa_show_home', 'no');
	
		if (isset($_POST['wppa-accesslevel'])) {
			update_option('wppa_accesslevel', $_POST['wppa-accesslevel']);
			wppa_set_caps();
		}
		
		if (isset($_POST['wppa-html'])) update_option('wppa_html', 'yes');
		else update_option('wppa_html', 'no');
		
		if (!$options_error) wppa_update_message(__('Changes Saved', 'wppa')); 
	}
    elseif (get_option('wppa_lastthumb', '-2') != '-2') wppa_error_message(__('Regeneration of thumbnail images interrupted. Please press "Save Changes"', 'wppa')); 
?>		
	<div class="wrap">
		<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/settings32.png'; ?>
		<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
			<br />
		</div>
		<h2><?php _e('WP Photo Album Plus Settings', 'wppa'); ?></h2>
		<p><?php _e('Database revision:', 'wppa'); ?> <?php echo(get_option('wppa_revision', '100')) ?>.</p><br/>
		<form action="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=options" method="post">
	
			<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE); ?>

			<p>
				<input type="submit" class="button-primary" name="wppa-set-submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
			</p>
			<br />

			<table class="form-table albumtable">
				<tbody>
					<tr><th><small><?php _e('Fullsize images:', 'wppa'); ?></small></th></tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Full Size:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-fullsize" id="wppa-fullsize" value="<?php echo(get_option('wppa_fullsize')) ?>" style="width: 50px;" />pixels.
							<span class="description"><br/><?php _e('The size of the full images is controled with html, the photo itself will not be resized.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Stretch if needed:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-enlarge" id="wppa-enlarge" <?php if (get_option('wppa_enlarge', 'yes') == 'yes') echo ('checked="checked"') ?> />
							<span class="description"><br/><?php _e('Fullsize images will be stretched to the Full Size if needed. Leaving unchecked is recommended. It is better to upload photos that fit well the sizes you use!', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Vertical alignment:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $valign = get_option('wppa_fullvalign', 'default'); ?>
							<select name="wppa-fullvalign">
								<option value="default" <?php if ($valign == 'default') echo(' selected '); ?>><?php _e('--- default ---', 'wppa'); ?></option>
								<option value="top" <?php if ($valign == 'top') echo(' selected '); ?>><?php _e('top', 'wppa'); ?></option>
								<option value="center" <?php if ($valign == 'center') echo(' selected '); ?>><?php _e('center', 'wppa'); ?></option>
								<option value="bottom" <?php if ($valign == 'bottom') echo(' selected '); ?>><?php _e('bottom', 'wppa'); ?></option>
							</select>
							<span class="description"><br/><?php _e('Specify the vertical alignment of fullsize images. Use this setting only when albums contain both portrait and landscape photos.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Slideshow animation speed:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $anim = get_option('wppa_animation_speed', 400); ?>
							<select name="wppa-animation-speed">
								<option value="0" <?php if ($anim == '0') echo('selected '); ?>><?php _e('--- off ---', 'wppa'); ?></option>
								<option value="200" <?php if ($anim == '200') echo('selected '); ?>><?php _e('very fast', 'wppa'); ?></option>
								<option value="400" <?php if ($anim == '400') echo('selected '); ?>><?php _e('fast', 'wppa'); ?></option>
								<option value="800" <?php if ($anim == '800') echo('selected '); ?>><?php _e('normal', 'wppa'); ?></option>
								<option value="1200" <?php if ($anim == '1200') echo('selected '); ?>><?php _e('slow', 'wppa'); ?></option>
								<option value="2000" <?php if ($anim == '2000') echo('selected '); ?>><?php _e('very slow', 'wppa'); ?></option>
							</select>
							<span class="description"><br/><?php _e('Specify the animation speed to be used in slideshows.', 'wppa'); ?></span>
						</td>
					</tr>					
					<tr><th><hr/></th><td><hr/></td></tr>
					<tr><th><small><?php _e('Thumbnails:', 'wppa'); ?></small></th></tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Photocount treshold:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-min-thumbs" id="wppa-min-thumbs" value="<?php echo(get_option('wppa_min_thumbs', '1')) ?>" style="width: 50px;" />
							<span class="description"><br/><?php _e('Photos do not show up in the album unless there are more than this number of photos in the album. This allows you to have cover photos on an album that contains only sub albums without seeing them in the list of sub albums. Usually set to 0 (always show) or 1 (for one cover photo).', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Thumbnail Size:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-thumbsize" id="wppa-thumbsize" value="<?php echo(get_option('wppa_thumbsize', '130')) ?>" style="width: 50px;" />pixels.
							<span class="description"><br/><?php _e('Changing the thumbnail size may result in all thumbnails being regenerated. this may take a while.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Display type:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $thumbtype = get_option('wppa_thumbtype', 'default'); ?>
							<select name="wppa-thumbtype" id="wppa-thumbtype" onchange="wppaCheckTt()" >
								<option value="default" <?php if ($thumbtype == 'default') echo(' selected '); ?>><?php _e('--- default ---', 'wppa'); ?></option>
								<option value="ascovers" <?php if ($thumbtype == 'ascovers') echo(' selected '); ?>><?php _e('like album covers', 'wppa'); ?></option>
							</select>
							<span class="description"><br/><?php _e('You may select an altenative display method for thumbnails. Note that some of the thumbnail settings do not apply to all available display methods.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Vertical alignment:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $valign = get_option('wppa_valign', 'default'); ?>
							<select name="wppa-valign">
								<option value="default" <?php if ($valign == 'default') echo(' selected '); ?>><?php _e('--- default ---', 'wppa'); ?></option>
								<option value="top" <?php if ($valign == 'top') echo(' selected '); ?>><?php _e('top', 'wppa'); ?></option>
								<option value="center" <?php if ($valign == 'center') echo(' selected '); ?>><?php _e('center', 'wppa'); ?></option>
								<option value="bottom" <?php if ($valign == 'bottom') echo(' selected '); ?>><?php _e('bottom', 'wppa'); ?></option>
							</select>
							<span class="description"><br/><?php _e('Specify the vertical alignment of thumbnail images. Use this setting only when albums contain both portrait and landscape photos.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Apply mouseover effect:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-use-thumb-opacity" id="wppa-use-thumb-opacity" onchange="wppaCheckUto()"<?php if (get_option('wppa_use_thumb_opacity', 'no') == 'yes') echo('checked="checked"') ?> />
							<span class="description"><br/><?php _e('Use mouseover effect on thumbnail images.', 'wppa') ?></span>
						</td>
					</tr>
					<tr valign="top" id="wppa-to">
						<th scope="row">
							<label><?php _e('Opacity value:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-thumb-opacity" id="wppa-thumb-opacity" value="<?php echo(get_option('wppa_thumb_opacity', '80')) ?>" style="width: 50px;" />%.
							<span class="description"><br/><?php _e('Percentage of opacity. 100% is opaque, 0% is transparant', 'wppa') ?></span>
						</td>
					</tr>
					<script type="text/javascript">wppaCheckUto();</script>
					<tr valign="top" id="wppa-utp">
						<th scope="row">
							<label><?php _e('Apply popup effect:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-use-thumb-popup" id="wppa-use-thumb-popup" <?php if (get_option('wppa_use_thumb_popup', 'no') == 'yes') echo('checked="checked"') ?> />
							<span class="description"><br/><?php _e('Use popup effect on thumbnail images.', 'wppa') ?></span>
						</td>
					</tr>
					<script type="text/javascript">wppaCheckTt();</script>
					<tr><th><hr/></th><td><hr/></td></tr>
					<tr><th><small><?php _e('Album covers:', 'wppa'); ?></small></th></tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Coverphoto Size:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-smallsize" id="wppa-smallsize" value="<?php echo(get_option('wppa_smallsize', '130')) ?>" style="width: 50px;" />
							<span class="description"><br/><?php _e('Changing the coverphoto size may result in all thumbnails being regenerated. this may take a while.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Apply mouseover effect:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-use-cover-opacity" id="wppa-use-cover-opacity" onchange="wppaCheckUco()" <?php if (get_option('wppa_use_cover_opacity', 'no') == 'yes') echo('checked="checked"') ?> />
							<span class="description"><br/><?php _e('Use mouseover effect on cover images.', 'wppa') ?></span>
						</td>
					</tr>
					<tr valign="top" id="wppa-co">
						<th scope="row">
							<label><?php _e('Opacity value:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-cover-opacity" id="wppa-cover-opacity" value="<?php echo(get_option('wppa_cover_opacity', '80')) ?>" style="width: 50px;" />%.
							<span class="description"><br/><?php _e('Percentage of opacity. 100% is opaque, 0% is transparant', 'wppa') ?></span>
						</td>
					</tr>							
					<script type="text/javascript">wppaCheckUco();</script>
					<tr><th><hr/></th><td><hr/></td></tr>
					<tr><th><small><?php _e('Order settings:', 'wppa'); ?></small></th></tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Album order:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $order = get_option('wppa_list_albums_by'); ?>
							<select name="wppa-list-albums-by"><?php wppa_order_options($order, __('--- none ---', 'wppa')); ?></select>
							<span class="description"><br/><?php _e('Specify the way the albums should be ordered.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Descending:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-list-albums-desc" id="wppa-list-albums-desc" <?php if (get_option('wppa_list_albums_desc') == 'yes') echo('checked="checked"') ?> />
							<span class="description"><br/><?php _e('If checked: largest first', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Photo order:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $order = get_option('wppa_list_photos_by'); ?>
							<select name="wppa-list-photos-by"><?php wppa_order_options($order, __('--- none ---', 'wppa')); ?></select>
							<span class="description"><br/><?php _e('Specify the way the photos should be ordered. This is the default setting. You can overrule the default sorting order on a per album basis.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Descending:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-list-photos-desc" id="wppa-list-photos-desc" <?php if (get_option('wppa_list_photos_desc') == 'yes') echo (' checked="checked"') ?> />
							<span class="description"><br/><?php _e('This is a system wide setting.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr><th><hr/></th><td><hr/></td></tr>
					<tr><th><small><?php _e('Miscelanious:', 'wppa'); ?></small></th></tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Show breadcrumb:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-show-bread" id="wppa-show-bread" <?php if (get_option('wppa_show_bread', 'yes') == 'yes') echo(' checked="checked"') ?> />
							<span class="description"><br/><?php _e('Indicate whether a breadcrumb navigation should be displayed', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Show "Home" in breadcrumb:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-show-home" id="wppa-show-home" <?php if (get_option('wppa_show_home', 'yes') == 'yes') echo (' checked="checked"') ?> />
							<span class="description"><br/><?php _e('Indicate whether the breadcrumb navigation should start with a "Home"-link', 'wppa'); ?></span>
						</td>
					</tr>
<?php if (current_user_can('administrator')) { ?>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Access Level:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $level = get_option('wppa_accesslevel'); ?>
							<?php $sel = 'selected="selected"'; ?>
							<select name="wppa-accesslevel">
								<option value="administrator" <?php if ($level == 'administrator') echo($sel); ?>><?php _e('Administrator', 'wppa'); ?></option> 
								<option value="editor" <?php if ($level == 'editor') echo($sel); ?>><?php _e('Editor', 'wppa'); ?></option>
								<option value="author" <?php if ($level == 'author') echo($sel); ?>><?php _e('Author', 'wppa'); ?></option>
								<option value="contributor" <?php if ($level == 'contributor') echo($sel); ?>><?php _e('Contributor', 'wppa'); ?></option>				
							</select>
							<span class="description"><br/><?php _e('The minmum user level that can access the photo album admin.', 'wppa'); ?></span>
						</td>
					</tr>
<?php } ?>					
<?php if (function_exists('html_entity_decode')) { ?>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Allow HTML in album and photo descriptions:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-html" id="wppa-html" <?php if (get_option('wppa_html', 'yes') == 'yes') echo (' checked="checked"') ?> />
							<span class="description"><br/><?php _e('If checked: html is allowed. WARNING: No checks on syntax, it is your own responsability to close tags properly!', 'wppa'); ?></span>
						</td>
					</tr>	
<?php } ?>					
				</tbody>
			</table>

			<br />
			<p>
				<input type="submit" class="button-primary" name="wppa-set-submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
			</p>
		</form>
	</div>
<?php 
}

function wppa_sidebar_page_options() {
	global $wpdb;
	
	// Check if a message is required
	wppa_check_update();

	$options_error = false;
	
	if (isset($_GET['walbum'])) update_option('wppa_widget_album', $_GET['walbum']);
		
	if (isset($_POST['wppa-set-submit'])) {
		wppa_check_admin_referer( '$wppa_nonce', WPPA_NONCE );
		
		update_option('wppa_widgettitle', $_POST['wppa-widgettitle']);
		
		if (wppa_check_numeric($_POST['wppa-widget-width'], '100', __('Widget Photo Width.'))) {
			update_option('wppa_widget_width', $_POST['wppa-widget-width']);
		} else {
			$options_error = true;
		}
		if (isset($_POST['wppa-widget-album'])) update_option('wppa_widget_album', $_POST['wppa-widget-album']);
		if (isset($_POST['wppa-widget-photo'])) update_option('wppa_widget_photo', $_POST['wppa-widget-photo']);
		if (isset($_POST['wppa-widget-method'])) update_option('wppa_widget_method', $_POST['wppa-widget-method']);
		if (isset($_POST['wppa-widget-period'])) update_option('wppa_widget_period', $_POST['wppa-widget-period']);
		if (isset($_POST['wppa-widget-subtitle'])) update_option('wppa_widget_subtitle', $_POST['wppa-widget-subtitle']);
		if (isset($_POST['wppa-widget-linkpage'])) update_option('wppa_widget_linkpage', $_POST['wppa-widget-linkpage']);
		
		if (!$options_error) wppa_update_message(__('Changes Saved. Don\'t forget to activate the widget!', 'wppa')); 
	}

?>
	<div class="wrap">
		<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/settings32.png'; ?>
		<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
			<br />
		</div>
		<h2><?php _e('WP Photo Album Plus Sidebar Widget Settings', 'wppa'); ?></h2>
		
		<form action="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=wppa_sidebar_options" method="post">
			<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE); ?>

			<table class="form-table albumtable">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Widget Title:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-widgettitle" id="wppa-widgettitle" value="<?php echo(get_option('wppa_widgettitle', __('Photo of the day', 'wppa'))); ?>" />
							<span class="description"><br/><?php _e('Enter the caption to be displayed for the widget.', 'wppa'); ?></span>
						</td>
					</tr>				
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Widget Photo Width:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-widget-width" id="wppa-widget-width" value="<?php echo(get_option('wppa_widget_width', '150')); ?>" style="width: 50px;" />
							<span class="description"><br/><?php _e('Enter the desired display width of the photo in the sidebar.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Use album:', 'wppa'); ?></label>
						</th>
						<td>
							<script type="text/javascript">
							/* <![CDATA[ */
							function wppaCheckWa() {
								var album = document.getElementById('wppa-wa').value;
								var url = "<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=wppa_sidebar_options&walbum=" + album;
								document.location.href = url;
							}
							/* ]]> */
							</script>
							<select name="wppa-widget-album" id="wppa-wa" onchange="wppaCheckWa()" ><?php echo(wppa_album_select('', get_option('wppa_widget_album', ''))) ?></select>
							<span class="description"><br/><?php _e('Select the album that contains the widget photos.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Display method:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $sel = 'selected="selected"'; ?>
							<?php $method = get_option('wppa_widget_method', '1'); ?>
							<select name="wppa-widget-method" id="wppa-wm" onchange="wppaCheckWidgetMethod()" >
								<option value="1" <?php if ($method == '1') echo($sel); ?>><?php _e('Fixed photo', 'wppa'); ?></option> 
								<option value="2" <?php if ($method == '2') echo($sel); ?>><?php _e('Random', 'wppa'); ?></option>
								<option value="3" <?php if ($method == '3') echo($sel); ?>><?php _e('Last upload', 'wppa'); ?></option>
								<option value="4" <?php if ($method == '4') echo($sel); ?>><?php _e('Change every', 'wppa'); ?></option>
	<?php /*
								<option value="5" <?php if ($method == '5') echo($sel); ?>><?php _e('Slideshow', 'wppa'); ?></option>
								<option value="6" <?php if ($method == '6') echo($sel); ?>><?php _e('Scrollable', 'wppa'); ?></option>
	*/ ?>
							</select>
							<?php $period = get_option('wppa_widget_period', '168'); ?>
							<select name="wppa-widget-period" id="wppa-wp" >
								<option value="1" <?php if ($period == '1') echo($sel); ?>><?php _e('hour.', 'wppa'); ?></option>
								<option value="24" <?php if ($period == '24') echo($sel); ?>><?php _e('day.', 'wppa'); ?></option>
								<option value="168" <?php if ($period == '168') echo($sel); ?>><?php _e('week.', 'wppa'); ?></option>
							</select>
							<span class="description"><br/><?php _e('Select how the widget should display.', 'wppa'); ?></span>								
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label ><?php _e('Link to:', 'wppa'); ?></label>
						</th>
						<td>
<?php
							$query = "SELECT ID, post_title FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish' ORDER BY post_title ASC";
							$pages = $wpdb->get_results ($query, 'ARRAY_A');
							if (empty($pages)) {
								_e('There are no pages (yet) to link to.', 'wppa');
							} else {
								$linkpage = get_option('wppa_widget_linkpage', '0');
?>
								<select name="wppa-widget-linkpage" id="wppa-wlp" >
									<option value="0" <?php if ($linkpage == '0') echo($sel); ?>><?php _e('--- none ---', 'wppa'); ?></option>
<?php
									foreach ($pages as $page) { ?>
										<option value="<?php echo($page['ID']); ?>" <?php if ($linkpage == $page['ID']) echo($sel); ?>><?php echo($page['post_title']); ?></option>
									<?php } ?>
								</select>
								<span class="description"><br/><?php _e('Select the page the photo links to.', 'wppa'); ?></span>
<?php
							}							
?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label ><?php _e('Subtitle:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $subtit = get_option('wppa_widget_subtitle', 'none'); ?>
							<select name="wppa-widget-subtitle" id="wppa-st" onchange="wppaCheckWidgetSubtitle()" >
								<option value="none" <?php if ($subtit == 'none') echo($sel); ?>><?php _e('--- none ---', 'wppa'); ?></option>
								<option value="name" <?php if ($subtit == 'name') echo($sel); ?>><?php _e('Photo Name', 'wppa'); ?></option>
								<option value="desc" <?php if ($subtit == 'desc') echo($sel); ?>><?php _e('Description', 'wppa'); ?></option>
							</select>
							<span class="description"><br/><?php _e('Select the content of the subtitle.', 'wppa'); ?></span>	
						</td>
					</tr>
				</tbody>
			</table>
			<p>
				<input type="submit" class="button-primary" name="wppa-set-submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
			</p>
<?php
			$alb = get_option('wppa_widget_album', '0');
			$photos = $wpdb->get_results("SELECT * FROM " . PHOTO_TABLE . " WHERE album=" . $alb . " " . wppa_get_photo_order($alb), 'ARRAY_A');
			if (empty($photos)) {
?>
			<p><?php _e('No photos yet in this album.', 'wppa'); ?></p>
<?php
			} else {
				$id = get_option('wppa_widget_photo', '');
//				$wi = get_option('wppa_thumbsize', '130') + 24;
				$wi = wppa_get_minisize() + 24;
				foreach ($photos as $photo) {
?>
					<div class="photoselect" style="width: <?php echo(get_option('wppa_widget_width', '150')); ?>px; height: <?php echo($wi); ?>px;" >
						<img src="<?php echo(get_bloginfo('wpurl') . '/wp-content/uploads/wppa/thumbs/' . $photo['id'] . '.' . $photo['ext']); ?>" alt="<?php echo($photo['name']); ?>"></img>
						<input type="radio" name="wppa-widget-photo" id="wppa-widget-photo<?php echo($photo['id']); ?>" value="<?php echo($photo['id']) ?>" <?php if ($photo['id'] == $id) echo('checked="checked"'); ?>/>
						<div class="clear"></div>
						<h4 style="position: absolute; top:<?php echo( $wi - 12 ); ?>px;"><?php echo(stripslashes($photo['name'])) ?></h4>
						<h6 style="position: absolute; top:<?php echo( $wi - 12 ); ?>px;"><?php echo(stripslashes($photo['description'])); ?></h6>
					</div>
<?php		
				}
?>
					<div class="clear"></div>
<?php
			}
?>
			<script type="text/javascript">wppaCheckWidgetMethod();</script>
			<script type="text/javascript">wppaCheckWidgetSubtitle();</script>
			<br />
			<p>
				<input type="submit" class="button-primary" name="wppa-set-submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
			</p>
		</form>
	</div>
<?php
}

function wppa_page_help() {

	// Check if a message is required
	wppa_check_update();

?>
	<div class="wrap">
<?php 
		$iconurl = "http://www.gravatar.com/avatar/b421f77aa39db35a5c1787240c77634f?s=32&amp;d=http%3A%2F%2Fwww.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D32&amp;r=G";
?>		
		<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
			<br />
		</div>
		<h2><?php _e('Help and Information', 'wppa'); ?></h2>
		
		<h3><?php _e('Plugin Description', 'wppa'); ?></h3>
        <p><?php _e('This plugin is designed to easily manage and display your photo albums within your WordPress site.', 'wppa'); ?></p>
			<?php _e('Features:', 'wppa'); ?><br /><br />
			<ul class="wppa-help-ul">
				<li><?php _e('You can create various albums that contain photos as well as sub albums at the same time.', 'wppa'); ?></li>
				<li><?php _e('There is no limitation to the number of albums and photos.', 'wppa'); ?></li>
				<li><?php _e('There is no limitation to the nesting depth of sub-albums.', 'wppa'); ?></li>
				<li><?php _e('You have full control over the display sizes of the photos.', 'wppa'); ?></li>
				<li><?php _e('You can specify the way the albums are ordered.', 'wppa'); ?></li>
				<li><?php _e('You can specify the way the photos are ordered within the albums, both on a system-wide as well as an per album basis.', 'wppa'); ?></li>
				<li><?php _e('The visitor of your site can run a slideshow from the photos in an album by a single mouseclick.', 'wppa'); ?></li>
				<li><?php _e('The visitor can see an overview of thumbnail images of the photos in album.', 'wppa'); ?></li>
				<li><?php _e('The visitor can browse through the photos in each album you decide to publish.', 'wppa'); ?></li>
				<li><?php _e('You can add a Sidebar Widget that displays a photo which can be changed every hour, day or week.', 'wppa'); ?></li>
			</ul>
		
		<h3><?php _e('Plugin Admin Features', 'wppa'); ?></h3>
		<p><?php _e('You can find the plugin admin section under Menu Photo Albums on the admin screen.', 'wppa'); ?></p>
			<?php _e('The following submenus exist.', 'wppa'); ?><br /><br />
			<ul class="wppa-help-ul">
				<li><?php _e('Photo Albums: Create and manage Albums.', 'wppa'); ?></li>
				<li><?php _e('Upload photos: To upload photos to an album you created.', 'wppa'); ?></li>
				<li><?php _e('Settings: To control the various settings to customize your needs.', 'wppa'); ?></li>
				<li><?php _e('Sidebar Widget: To specify the behaviour for an optional sidebar widget.', 'wppa'); ?></li>
				<li><?php _e('Help & Info: The screen you are watching now.', 'wppa'); ?></li>
			</ul>

		<h3><?php _e('Installation', 'wppa'); ?></h3>
        <ol class="wppa-help-ol">
			<li><?php _e('Unzip and upload the wppa plugin folder to', 'wppa'); ?> <tt>wp-content/plugins/</tt></li>
			<li><?php _e('Make sure that the folder', 'wppa'); ?> <tt>wp-content/uploads/</tt> <?php _e('exists and is writable by the server (CHMOD 755)', 'wppa'); ?></li>
			<li><?php _e('Activate the plugin in WP Admin -> Plugins.', 'wppa'); ?></li>
		</ol>

        <h3><?php _e('Upgrading from WP Photo Album', 'wppa'); ?></h3>
        <p><?php _e('When upgrading from WP Photo Album to WP Photo Album Plus be aware of:', 'wppa'); ?></p>
        <ol class="wppa-help-ol">
			<li><?php _e('First de-activate WP Photo Album before activating WP Photo Album Plus!!', 'wppa'); ?><br/>
				<?php _e('YOU CAN NOT RUN BOTH VERSIONS AT THE SAME TIME!!', 'wppa'); ?>
			</li>
			<li><?php _e('The existing database and albums and photos will be preserved.', 'wppa'); ?><br/>
				<?php _e('YOU DO NOT NEED TO RE-UPLOAD YOUR PHOTOS', 'wppa'); ?>
			</li>
			<li><?php _e('You will need to use (and probably modify) the newly supplied default theme file "wppa_theme.php".', 'wppa'); ?></li>
			<li><?php _e('You can use existing albums to make sub-albums, simply by specifying in which album they belong.', 'wppa'); ?></li>
        </ol>
            
		<h3><?php _e('How to start', 'wppa'); ?></h3>
        <ol class="wppa-help-ol">
			<li><?php _e('Install WP Photo ALbum Plus as described above under "Installation".', 'wppa'); ?></li>
            <li><?php _e('Create at least two albums in the "Photo Albums" tab. Just enter the name and a brief description and press "Create Album". Leave "Parent" at "--- none ---".', 'wppa'); ?></li>
			<li><?php _e('In the uploads tab, you can now upload you photots. Upload at least 2 photos to each album. Make sure the photos you are uploading are of reasonable size (say up to 1024x768 pixels). Do not upload the full 7MP images!', 'wppa'); ?></li>
			<li><?php _e('Create a new WP Page, name it something like "Photo Gallery" and put in the content:', 'wppa'); ?> <tt>%%wppa%%</tt></li>
			<li><?php _e('Publish the page, and view the page from your WP site.', 'wppa'); ?></li>
			<li><?php _e('Now, go playing with the settings in the "Settings" panel, discover all the configurable options and watch what is happening when you re-open the "Photo Gallery" page.', 'wppa'); ?></li>
			<li><?php _e('If you want a "Photo of the week" sidebar widget you can use an album for that purpose. See all the options in the "Sidebar Widget" submenu.', 'wppa'); ?></li>
        </ol>

		<h3><?php _e('Creating a Photo Album Page or a Post with photos - Advanced', 'wppa'); ?></h3>
		<p>
			<?php _e('Create a page like you normally would in WordPress, using the "Default Template". In my example, give it the page title of "Photo Gallery". In the Page Content section add the following code:', 'wppa'); ?><br />
			<tt>%%wppa%%</tt><br />
			<?php _e('This will result in a gallery of all Albums that have their parent set to "--- none ---".', 'wppa'); ?><br /><br />
			<?php _e('If you want to display a single album - say album number 19 - in a WP page or WP post (they act exactly the same), add a second line like this:', 'wppa'); ?><br />
			<tt>%%album=19%%</tt><br />
			<?php _e('This will result in the display of the', 'wppa'); ?><b> <?php _e('contents', 'wppa'); ?> </b><?php _e('of album nr 19.', 'wppa'); ?><br /><br />
			<?php _e('If you want to display the', 'wppa'); ?><b> <?php _e('"cover"', 'wppa'); ?> </b><?php _e('of the album, i.e. like one of the albums in the "Photo Gallery" as used above, add (instead of "%%album=...") a second line like this:', 'wppa'); ?><br />
			<tt>%%cover=19%%</tt><br /><br />
			<?php _e('Alternatively, you can create an extra album (say it has number 22) and set the "parent" property of album 19 to this new album. Then your second line should read:', 'wppa'); ?><br />
			<tt>%%album=22%%</tt><br />
			<?php _e('This method enables you to add more than one album to a specific page or post as long as they have the same parent.', 'wppa'); ?><br /><br />
			<?php _e('Additionally, if you set the parent of this album (nr 22 in this example) to "--- separate ---", it will not be listed in the "generic" photo gallery and the breadcrumb will display the best.', 'wppa'); ?><br /><br />
			<?php _e('You can add a third line if you want the photos to be displayed at a different size than normal. You can "overrule" the "Full size" setting by adding the line (for e.g. 300px):', 'wppa'); ?><br />
			<tt>%%size=300%%</tt><br /><br />
			<?php _e('Note: all information between the %% tags including newlines will be lost.', 'wppa'); ?><br />
			<?php _e('The sequence above may be used more than once in a single page or post.', 'wppa'); ?><br />
			<?php _e('The text before the first sequence, the text between 2 sequences, as well as the text after the last sequence will be preserved.', 'wppa'); ?><br />
			<br/ ><br />
			<?php _e('You can also create a custom page template by dropping the following code into a page template:', 'wppa'); ?><br />
			<tt>&lt;?php wppa_albums(); ?&gt;</tt><br /><br />
			<?php _e('If you want to display the <b>contents</b> of a single album in the template - say album number 19 - the code would be:', 'wppa'); ?><br />
			<tt>&lt;?php wppa_albums(19); ?&gt;</tt><br />
			<?php _e('If you want the <b>cover</b> to be displayed instead, add the following code:', 'wppa'); ?><br />
			<tt>&lt;?php global $is_cover; ?&gt;</tt><br />
			<tt>&lt;?php $is_cover = '1'; ?&gt;</tt><br /><br />
			<?php _e('If you want to specify a size, add the following code:', 'wppa'); ?><br />
			<tt>&lt;?php global $wppa_fullsize; ?&gt;<br/>
			&lt;?php $wppa_fullsize = 300; ?&gt;</tt><br/><br />
			<?php _e('You can combine the above as follows: (example)', 'wppa'); ?><br />
			<tt>&lt;?php wppa_albums(19, 'album', 300); ?&gt;</tt><br />
			<?php _e('or as:', 'wppa'); ?><br />
			<tt>&lt;?php wppa_albums(19, 'cover', 300); ?&gt;</tt><br /><br />
			<?php _e('In order to work properly, the wppa_albums() tag needs to be within the', 'wppa'); ?> <a href="http://codex.wordpress.org/The_Loop">WordPress loop</a>.<br/>
			<?php _e('For more information on creating custom page templates, click', 'wppa'); ?> <a href="http://codex.wordpress.org/Pages#Creating_your_own_Page_Templates"><?php _e('here', 'wppa'); ?></a>.<br/>
		</p>
		
		<h3><?php _e('Adjusting CSS and Template Styling', 'wppa'); ?></h3>
		<p>
			<?php _e('WP Photo Album Plus comes with a default layout and theme.', 'wppa'); ?>
			<?php _e('To change the style and layout of the photo album, copy <tt>.../wp-content/plugins/wp-photo-album-plus/theme/wppa_theme.php</tt> and <tt>.../wp-content/plugins/wp-photo-album-plus/theme/wppa_style.css</tt> to your active theme\'s folder, and edit them.', 'wppa'); ?>
		</p>
		
		<h3><?php _e('Facts to remember', 'wppa'); ?></h3>
		<ul class="wppa-help-ul">
			<li><?php _e('An album can have only <b>ONE</b> parent.', 'wppa'); ?></li>
			<li><?php _e('If the number of photos in an album is less than or equal to the treshold value, they will not display in the album. They will be used for the cover only.', 'wppa'); ?></li>
			<li><?php _e('An album that has it\'s parent set to "--- separate ---" will not be displayed in the "generic" gallery. This enables you to have albums for use solely for single posts or pages.', 'wppa'); ?>
			<li><?php _e('Specifying <tt>%%album=...</tt> causes the <b>content</b> of the album to be displayed.', 'wppa'); ?></li>
			<li><?php _e('Specifying <tt>%%cover=...</tt> causes the <b>cover</b> of the album to be displayed.', 'wppa'); ?></li>
			<li><?php _e('Keep the sequence intact: 1. <tt>%%wppa%%</tt>, 2. <tt>%%album=</tt> or <tt>%%cover=</tt>, 3. <tt>%%size=</tt>. (2. being optional even when using 3.).', 'wppa'); ?></li>
			<li><?php _e('Use the default page template, or create one yourself. In this case, study the example (actually the version i use myself): <tt>...wp-content/plugins/wp-photo-album-plus/examples/page-photo-album.php</tt>', 'wppa'); ?></li>
			<li><?php _e('WPPA uses a system of tags similar to the WordPress theme system. To view a list of available tags, please read tags.txt', 'wppa'); ?></li>
			<li><?php _e('You can remove the plugin and re-install the latest version always. This will not affect your photos or albums.', 'wppa'); ?></li>
			</ul>
	
		<h3><?php _e('Plugin Support And Feature Request', 'wppa'); ?></h3>
		<p>
			<?php _e('If you\'ve read over this readme carefully and are still having issues, if you\'ve discovered a bug,', 'wppa'); ?>
			<?php _e('or have a feature request, please contact me via my', 'wppa'); ?> <a href="mailto:opajaap@opajaap.nl?subject=WP%20Photo%20Album%20Plus">E-mail</a>.
			<br/>
			<?php _e('You may also check the', 'wppa'); ?> <a href="http://wordpress.org/tags/wp-photo-album-plus">forum</a> <?php _e('for this plugin and/or leave a question there.', 'wppa'); ?>
			<br/>
			<?php _e('For hot fixes check the', 'wppa'); ?> <a href="http://plugins.trac.wordpress.org/log/wp-photo-album-plus/">development log</a> <?php _e('for this plugin.', 'wppa'); ?>
		</p>
        <p>
			<?php _e('If you love this plugin, I would appreciate a donation, either in', 'wppa'); ?>
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=OpaJaap@OpaJaap.nl&item_name=WP-Photo-Album-Plus&item_number=Support-Open-Source&currency_code=USD&lc=US">USD</a>&nbsp;
				<?php _e('or in', 'wppa'); ?>&nbsp;
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=OpaJaap@OpaJaap.nl&item_name=WP-Photo-Album-Plus&item_number=Support-Open-Source&currency_code=EUR&lc=US">EURO.</a>
		</p>

		<h3><?php _e('About and credits', 'wppa'); ?></h3>
		<p>
			<?php _e('WP Photo Album Plus is extended with many new features and is maintained by J.N. Breetvelt, a.k.a.', 'wppa'); ?>
			<a href="http://www.opajaap.nl/"> (OpaJaap)</a><br />
			<?php _e('Thanx to R.J. Kaplan for WP Photo Album 1.5.1.', 'wppa'); ?><br/>
			<?php _e('Thanx to E.S. Rosenberg for programming tips on security issues.', 'wppa'); ?><br/>
		</p>
		
		<h3><?php _e('Licence', 'wppa'); ?></h3>
		<p>
			<?php _e('WP Photo Album is released under the', 'wppa'); ?> <a href="http://www.gnu.org/copyleft/gpl.html">GNU GPL</a> <?php _e('licence.', 'wppa'); ?>
		</p>
		
	</div>
<?php
}

/* get the albums */
function wppa_admin_albums() {
	global $wpdb;
	$albums = $wpdb->get_results("SELECT * FROM " . ALBUM_TABLE . " " . wppa_get_album_order(), 'ARRAY_A');
	
	if (!empty($albums)) {
?>	
		<table class="widefat">
			<thead>
			<tr>
				<th scope="col"><?php _e('Name', 'wppa'); ?></th>
				<th scope="col"><?php _e('Description', 'wppa'); ?></th>
				<th scope="col"><?php _e('ID', 'wppa'); ?></th>
                <th scope="col"><?php _e('Order', 'wppa'); ?></th>
                <th scope="col"><?php _e('Parent', 'wppa'); ?></th>
				<th scope="col"><?php _e('Edit', 'wppa'); ?></th>
				<th scope="col"><?php _e('Delete', 'wppa'); ?></th>	
			</tr>
			</thead>
			
			<?php $alt = ' class="alternate" '; ?>
			
			<?php foreach ($albums as $album) { ?>
				<tr <?php echo($alt) ?>>
					<td><?php echo(stripslashes($album['name'])) ?></td>
					<td><small><?php echo(stripslashes($album['description'])) ?></small></td>
					<td><?php echo($album['id']) ?></td>
					<td><?php echo($album['a_order']) ?></td>
					<td><?php wppa_album_name($album['a_parent']) ?></td>
					<td><a href="admin.php?page=<?php echo(WPPA_PLUGIN_PATH) ?>/wppa.php&amp;tab=edit&amp;edit_id=<?php echo($album['id']) ?>" class="wppaedit"><?php _e('Edit', 'wppa'); ?></a></td>
					<td><a href="admin.php?page=<?php echo(WPPA_PLUGIN_PATH) ?>/wppa.php&amp;tab=del&amp;id=<?php echo($album['id']) ?>" class="wppadelete"><?php _e('Delete', 'wppa'); ?></a></td>
				</tr>		
<?php			if ($alt == '') { $alt = ' class="alternate" '; } else { $alt = '';}
			}
?>			
		</table>
<?php	
	} else { 
?>
	<p><?php _e('No albums yet.', 'wppa'); ?></p>
<?php
	}
}

// get photo edit list for albums
function wppa_album_photos($id) {
	global $wpdb;
	
	$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . PHOTO_TABLE . '` WHERE `album` = %d ' . wppa_get_photo_order($id), $id), 'ARRAY_A');

	if (empty($photos)) {
?>
	<p><?php _e('No photos yet in this album.', 'wppa'); ?></p>
<?php
	} else {
		foreach ($photos as $photo) {
?>
			<div class="photoitem">
					<?php $src = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/thumbs/' . $photo['id'] . '.' . $photo['ext']; ?> 
					<?php $path = ABSPATH . 'wp-content/uploads/wppa/thumbs/' . $photo['id'] . '.' . $photo['ext']; ?>
					<img src="<?php echo($src) ?>" alt="<?php echo($photo['name']) ?>" style="<?php echo(wppa_get_imgstyle($path, '135')); ?>" />
					<table class="details phototable">
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo('photos[' . $photo['id'] . '][name]') ?>"><?php _e('Name:', 'wppa'); ?></label>
							</th>
							<td>
								<input type="text" name="<?php echo('photos[' . $photo['id'] . '][name]') ?>" value="<?php echo(stripslashes($photo['name'])) ?>" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo('photos[' . $photo['id'] . '][album]') ?>"><?php _e('Album:', 'wppa'); ?></label>
							</th>
							<td>							
								<select name="<?php echo('photos[' . $photo['id'] . '][album]') ?>"><?php echo(wppa_album_select('', $id)) ?></select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo('photos[' . $photo['id'] . '][p_order]') ?>"><?php _e('Order:', 'wppa'); ?></label>
							</th>
							<td>
								<input type="text" name="<?php echo('photos[' . $photo['id'] . '][p_order]') ?>" value="<?php echo($photo['p_order']) ?>" style="width: 50px"/>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<a href="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=<?php echo(WPPA_PLUGIN_PATH) ?>/wppa.php&amp;tab=edit&amp;edit_id=<?php echo($_GET['edit_id']) ?>&amp;photo_del=<?php echo($photo['id']) ?>" class="deletelink" onclick="return confirm('Are you sure you want to delete this photo?')"><?php _e('Delete', 'wppa'); ?></a>
							</th>
							<td>
							</td>
						</tr>
					</table>
					<input type="hidden" name="<?php echo('photos[' . $photo['id'] . '][id]') ?>" value="<?php echo($photo['id']) ?>" />
					<div class="desc"><?php _e('Description:', 'wppa'); ?><br /><textarea cols="40" rows="4" name="photos[<?php echo($photo['id']) ?>][description]"><?php echo(stripslashes($photo['description'])) ?></textarea></div>
					<div class="clear"></div>
				
			</div>
<?php	}
	}
}

// check if albums exist
function wppa_has_albums() {
	global $wpdb;	
	$albums = $wpdb->get_results("SELECT * FROM " . ALBUM_TABLE, 'ARRAY_A');
	if (empty($albums)) {
		return FALSE;
	} else {
		return TRUE;
	}
}

// get select form element listing albums 
function wppa_album_select($exc = '', $sel = '', $addnone = FALSE, $addseparate = FALSE, $checkancestors = FALSE) {
	global $wpdb;
	$albums = $wpdb->get_results("SELECT * FROM " . ALBUM_TABLE, 'ARRAY_A');
	
    if ($sel == '') {
        $s = wppa_get_last_album();
        if ($s != $exc) $sel = $s;
    }
    
    $result = '';
    if ($addnone) $result .= '<option value="0">' . __('--- none ---', 'wppa') . '</option>';
    
	foreach ($albums as $album) {
		if ($sel == $album['id']) { 
            $selected = ' selected="selected" '; 
        } 
        else { $selected = ''; }
		if ($album['id'] != $exc && (!$checkancestors || !wppa_is_ancestor($exc, $album['id']))) {
			$result .= '<option value="' . $album['id'] . '"' . $selected . '>' . stripslashes($album['name']) . '</option>';
		}
		else {
			$result .= '<option disabled="disabled" value="-3">' . stripslashes($album['name']) . '</option>';
		}
	}
    
    if ($sel == -1) $selected = ' selected="selected" '; else $selected = '';
    if ($addseparate) $result .= '<option value="-1"' . $selected . '>' . __('--- separate ---', 'wppa') . '</option>';
	return $result;
}

// add an album 
function wppa_add_album() {
	global $wpdb;
	
	$name = $_POST['wppa-name']; 
	$name = esc_attr($name);
	
	$desc = $_POST['wppa-desc']; 
	$desc = esc_attr($desc);
	
	$order = (is_numeric($_POST['wppa-order']) ? $_POST['wppa-order'] : 0);
	$parent = (is_numeric($_POST['wppa-parent']) ? $_POST['wppa-parent'] : 0);
	$porder = (is_numeric($_POST['wppa-photo-order-by']) ? $_POST['wppa-photo-order-by'] : 0);

	if (!empty($name)) {
		$query = $wpdb->prepare('INSERT INTO `' . ALBUM_TABLE . '` (`id`, `name`, `description`, `a_order`, `a_parent`, `p_order_by`, `main_photo`, `cover_linkpage`) VALUES (0, %s, %s, %d, %d, %d, %d, %d)', $name, $desc, $order, $parent, $porder, 0, 0);
		$iret = $wpdb->query($query);
        if ($iret === FALSE) wppa_error_message(__('Could not create album.', 'wppa'));
		else {
            $id = wppa_album_id($name, TRUE);
            wppa_set_last_album($id);
			wppa_update_message(__('Album #', 'wppa') . ' ' . $id . ' ' . __('Added.', 'wppa'));
        }
	} 
    else wppa_error_message(__('Album Name cannot be empty.', 'wppa'));
}

// edit an album 
function wppa_edit_album() {
	global $wpdb;
	
    $first = TRUE;
	
	$name = $_POST['wppa-name'];
	$name = esc_attr($name);
	
	$desc = $_POST['wppa-desc'];
	$desc = esc_attr($desc);
	
	$main = $_POST['wppa-main'];
	
    $order = (is_numeric($_POST['wppa-order']) ? $_POST['wppa-order'] : 0);
	
	$parent = (isset($_POST['wppa-parent']) ? $_POST['wppa-parent'] : 0);
	if ($parent == -3) $parent = 0;	// selected an unselectable item (IE < 8 ?)
	
    $orderphotos = (is_numeric($_POST['wppa-list-photos-by']) ? $_POST['wppa-list-photos-by'] : 0);
	
	$link = $_POST['cover-linkpage'];
	
    // update the photo information
    if (isset($_POST['photos']))
	foreach ($_POST['photos'] as $photo) {
        $photo['name'] = esc_attr($photo['name']);
		
        if (!is_numeric($photo['p_order'])) $photo['p_order'] = 0;
		
//		$charset = get_bloginfo('charset');
		$photo_desc = $photo['description'];
//		$photo_desc = htmlentities($photo_desc, ENT_NOQUOTES, $charset, false);
		
		$query = $wpdb->prepare('UPDATE `' . PHOTO_TABLE . '` SET `name` = %s, `album` = %s, `description` = %s, `p_order` = %d WHERE `id` = %d LIMIT 1', $photo['name'], $photo['album'], $photo_desc, $photo['p_order'], $photo['id']);
		$iret = $wpdb->query($query);

        if ($iret === FALSE) {
            if ($first) { 
				wppa_error_message(__('Could not update photo.', 'wppa'));
				$first = FALSE;
			}
        }
	}
	
	// update the album information
	if (!empty($name)) {
		$query = $wpdb->prepare('UPDATE `' . ALBUM_TABLE . '` SET `name` = %s, `description` = %s, `main_photo` = %s, `a_order` = %d, `a_parent` = %d, `p_order_by` = %s, `cover_linkpage` = %s WHERE `id` = %d', $name, $desc, $main, $order, $parent, $orderphotos, $link, $_GET['edit_id']);
		$iret = $wpdb->query($query);
		
        if ($iret === FALSE) {
			wppa_error_message(__('Album could not be updated.', 'wppa'));
		}
		else {
			wppa_update_message(__('Album information edited.', 'wppa') . ' ' . '<a href="admin.php?page=' . WPPA_PLUGIN_PATH . '/wppa.php">' . __('Back to album management.', 'wppa') . '</a>');
		}
		
		wppa_set_last_album($_GET['edit_id']);
	} else { 
		wppa_error_message(__('Album Name cannot be empty.', 'wppa'));
	}
}

// delete an album 
function wppa_del_album($id, $move = '') {
	global $wpdb;

//	$wpdb->query("DELETE FROM " . ALBUM_TABLE . " WHERE id=$id LIMIT 1");
	
	$wpdb->query($wpdb->prepare('DELETE FROM `' . ALBUM_TABLE . '` WHERE `id` = %d LIMIT 1', $id));

	if (empty($move)) { // will delete all the album's photos
		$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . PHOTO_TABLE . '` WHERE `album` = %d', $id), 'ARRAY_A');

		if (is_array($photos)) {
			foreach ($photos as $photo) {
				// remove the photos and thumbs
				$file = ABSPATH . 'wp-content/uploads/wppa/' . $photo['id'] . '.' . $photo['ext'];
				if (file_exists($file)) {
					unlink($file);
				}
				/* else: silence */
				$file = ABSPATH . 'wp-content/uploads/wppa/thumbs/' . $photo['id'] . '.' . $photo['ext'];
				if (file_exists($file)) {
					unlink($file);
				}
				/* else: silence */
			} 
		}
		
		// remove the database entries
		$wpdb->query($wpdb->prepare('DELETE FROM `' . PHOTO_TABLE . '` WHERE `album` = %d', $id));
	} else {
		$wpdb->query($wpdb->prepare('UPDATE `' . PHOTO_TABLE . '` SET `album` = %d WHERE `album` = %d', $move, $id));
	}
	
	wppa_update_message(__('Album Deleted.', 'wppa'));
}

// select main photo
function wppa_main_photo($cur = '') {
	global $wpdb;
	
    $a_id = $_GET['edit_id'];
	$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . PHOTO_TABLE . '` WHERE `album` = %d ' . wppa_get_photo_order($a_id), $a_id), 'ARRAY_A');
	
	$output = '';
	if (!empty($photos)) {
		$output .= '<select name="wppa-main">';
		$output .= '<option value="">' . __('--- random ---', 'wppa') . '</option>';

		foreach($photos as $photo) {
			if ($cur == $photo['id']) { 
				$selected = 'selected="selected"'; 
			} 
			else { 
				$selected = ''; 
			}
			$output .= '<option value="' . $photo['id'] . '" ' . $selected . '>' . $photo['name'] . '</option>';
		}
		
		$output .= '</select>';
	} else {
		$output = '<p>' . __('No photos yet', 'wppa') . '</p>';
	}
	return $output;
}

// Upload photos 
function wppa_upload_photos() {
	global $wpdb;

	$wppa_dir = ABSPATH . 'wp-content/uploads/wppa';
	
	// check if wppa dir exists
	if (!is_dir($wppa_dir)) {
		mkdir($wppa_dir);	
	}
	
	// check if thumbs dir exists 
	if (!is_dir($wppa_dir . '/thumbs')) {
		mkdir($wppa_dir . '/thumbs');
	}
	
	$warning_given = false;
	
	foreach ($_FILES as $file) {
		if ($file['tmp_name'] != '') {
			$img_size = getimagesize($file['tmp_name']);
			if ($img_size) { 
				if (!$warning_given && ($img_size['0'] > 1280 || $img_size['1'] > 1280)) {
					wppa_error_message(__('WARNING You are uploading very large photos, this may result in server problems! The recommended size is: not larger than 1024 x 768 pixels (up to approx. 250 kB).', 'wppa'));
					$warning_given = true;
				}
				$ext = substr(strrchr($file['name'], "."), 1);
			
				$query = $wpdb->prepare('INSERT INTO `' . PHOTO_TABLE . '` (`id`, `album`, `ext`, `name`, `p_order`, `description`) VALUES (0, %d, %s, %s, 0, \'\')', $_POST['wppa-album'], $ext, $file['name']);
				$wpdb->query($query);

				$image_id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
				
				$newimage = $wppa_dir . '/' . $image_id . '.' . $ext;
				copy($file['tmp_name'], $newimage);

				if (is_file ($newimage)) {
					$uploaded_a_file = TRUE;
					$thumbsize = wppa_get_minisize();
					wppa_create_thumbnail($newimage, $thumbsize, '' );
				} 
			}
		}
	}
	
	if ($uploaded_a_file) { 
		wppa_update_message(__('Photos Uploaded in album nr', 'wppa') . ' ' . $_POST['wppa-album']);
		wppa_set_last_album($_POST['wppa-album']);
    }
}



// update all thumbs 
function wppa_regenerate_thumbs() {
	global $wpdb;
	
	$thumbsize = wppa_get_minisize();
	$wppa_dir = ABSPATH . 'wp-content/uploads/wppa/';
    
    $start = get_option('wppa_lastthumb', '-1');

	$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . PHOTO_TABLE . '` WHERE `id` > %d ORDER BY `id`', $start), 'ARRAY_A');
	
	if (!empty($photos)) {
		foreach ($photos as $photo) {
			$newimage = $wppa_dir . $photo['id'] . '.' . $photo['ext'];
			wppa_create_thumbnail($newimage, $thumbsize, '' );
            update_option('wppa_lastthumb', $photo['id']);
            echo '.';
		}
	}		
}

/* create thubmnail - slightly modified  and renamed wordpress core function */
function wppa_create_thumbnail( $file, $max_side, $effect = '' ) {

		// 1 = GIF, 2 = JPEG, 3 = PNG

	if ( file_exists( $file ) ) {
		$type = getimagesize( $file );
		// if the associated function doesn't exist - then it's not
		// handle. duh. i hope.

		if (!function_exists( 'imagegif' ) && $type[2] == 1 ) {
			$error = __( 'Filetype not supported. Thumbnail not created.', 'wppa' );
		}
		elseif (!function_exists( 'imagejpeg' ) && $type[2] == 2 ) {
			$error = __( 'Filetype not supported. Thumbnail not created.', 'wppa' );
		}
		elseif (!function_exists( 'imagepng' ) && $type[2] == 3 ) {
			$error = __( 'Filetype not supported. Thumbnail not created.', 'wppa' );
		} else {

			// create the initial copy from the original file
			if ( $type[2] == 1 ) {
				$image = imagecreatefromgif( $file );
			}
			elseif ( $type[2] == 2 ) {
				$image = imagecreatefromjpeg( $file );
			}
			elseif ( $type[2] == 3 ) {
				$image = imagecreatefrompng( $file );
			}

			if ( function_exists( 'imageantialias' ))
				imageantialias( $image, TRUE );

			$image_attr = getimagesize( $file );

			// figure out the longest side

			if ( $image_attr[0] > $image_attr[1] ) {
				$image_width = $image_attr[0];
				$image_height = $image_attr[1];
				$image_new_width = $max_side;

				$image_ratio = $image_width / $image_new_width;
				$image_new_height = round($image_height / $image_ratio);
				//width is > height
			} else {
				$image_width = $image_attr[0];
				$image_height = $image_attr[1];
				$image_new_height = $max_side;

				$image_ratio = $image_height / $image_new_height;
				$image_new_width = round($image_width / $image_ratio);
				//height > width
			}

			$thumbnail = imagecreatetruecolor( $image_new_width, $image_new_height);
//			$thumbnail = imagecreate( $image_new_width, $image_new_height);
			@ imagecopyresampled( $thumbnail, $image, 0, 0, 0, 0, $image_new_width, $image_new_height, $image_attr[0], $image_attr[1] );

			// If no filters change the filename, we'll do a default transformation.
			if ( basename( $file ) == $thumb = apply_filters( 'thumbnail_filename', basename( $file ) ) )
				$thumb = 'thumbs/' . basename( $file );
				//$thumb = preg_replace( '!(\.[^.]+)?$!', '.thumbnail' . '$1', basename( $file ), 1 );

			$thumbpath = str_replace( basename( $file ), $thumb, $file );

			// move the thumbnail to its final destination
			if ( $type[2] == 1 ) {
				if (!imagegif( $thumbnail, $thumbpath ) ) {
					$error = __( "Thumbnail path invalid", 'wppa' );
				}
			}
			elseif ( $type[2] == 2 ) {
				if (!imagejpeg( $thumbnail, $thumbpath ) ) {
					$error = __( "Thumbnail path invalid", 'wppa' );
				}
			}
			elseif ( $type[2] == 3 ) {
				if (!imagepng( $thumbnail, $thumbpath ) ) {
					$error = __( "Thumbnail path invalid", 'wppa' );
				}
			}

		}
	} else {
		$error = __( 'File not found', 'wppa' );
	}

	if (!empty ( $error ) ) {
		return $error;
	} else {
		return apply_filters( 'wp_create_thumbnail', $thumbpath );
	}
}

function wppa_check_update() {
	$key = get_option('wppa_update_key', '0');
	if ($key == '0') return;
	
	$msg = '<center>' . __('IMPORTANT UPGRADE NOTICE', 'wppa') . '</center><br/>';
	if ($key == '1' || $key == '3') $msg .= '<br/>' . __('Please CHECK your customized WPPA_STYLE.CSS file against the newly supplied one. You may wish to add or modify some attributes.', 'wppa');
	if ($key == '2' || $key == '3') $msg .= '<br/>' . __('Please REPLACE your customized WPPA_THEME.PHP file by the newly supplied one, or just remove it from your theme directory. You may modify it later if you wish. Your current customized version is NOT compatible with this version of the plugin software.', 'wppa');
?>
	<div id="message" class="updatedok"><p><strong><?php echo($msg);?></strong></p></div>
<?php
	update_option('wppa_update_key', '0');
}

function wppa_set_caps() {
	global $wp_roles;

	if (current_user_can('administrator')) {
		$wp_roles->add_cap('administrator', 'wppa_admin');
		$level = get_option('wppa_accesslevel', 'administrator');
		if ($level == 'contributor') {
			$wp_roles->remove_cap('subscriber', 'wppa_admin');		
			$wp_roles->add_cap('contibutor', 'wppa_admin');
			$wp_roles->add_cap('author', 'wppa_admin');
			$wp_roles->add_cap('editor', 'wppa_admin');	
		}
		if ($level == 'author') {
			$wp_roles->remove_cap('subscriber', 'wppa_admin');		
			$wp_roles->remove_cap('contibutor', 'wppa_admin');
			$wp_roles->add_cap('author', 'wppa_admin');
			$wp_roles->add_cap('editor', 'wppa_admin');		
		}
		if ($level == 'editor') {
			$wp_roles->remove_cap('subscriber', 'wppa_admin');		
			$wp_roles->remove_cap('contibutor', 'wppa_admin');
			$wp_roles->remove_cap('author', 'wppa_admin');
			$wp_roles->add_cap('editor', 'wppa_admin');		
		}
		if ($level == 'administrator') {
			$wp_roles->remove_cap('subscriber', 'wppa_admin');		
			$wp_roles->remove_cap('contibutor', 'wppa_admin');
			$wp_roles->remove_cap('author', 'wppa_admin');
			$wp_roles->remove_cap('editor', 'wppa_admin');		
		}
	}
}			
?>