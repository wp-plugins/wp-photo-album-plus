<?php
/* wppa_albumadmin.php
* Pachkage: wp-photo-album-plus
*
* create, edit and delete albums
* version 2.4.3
*/

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
		
			if (!wppa_have_access($_GET['edit_id'])) wp_die('You do not have the rights to edit this album.');
		
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
				<h2><?php _e('Edit Album Information', 'wppa'); ?></h2>
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
<?php if (get_option('wppa_owner_only', 'no') == 'yes') { ?>
							<tr valign="top">
								<th scope="row">
									<label ><?php _e('Owned by:', 'wppa'); ?></label>
								</th>
								<td>
									<select name="wppa-owner"><?php wppa_user_select($albuminfo['owner']); ?></select>
<?php if (!current_user_can('administrator')) { ?>
									<span class="description" style="color:orange;" ><br/><?php _e('WARNING If you change the owner, you will no longer be able to modify this album and upload or import photos to it!', 'wppa'); ?></span>
<?php } ?>
								</td>
							</tr>
<?php } ?>
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
											<option value="-1" <?php if ($linkpage == '-1') echo($sel); ?>><?php _e('--- no link at all ---', 'wppa'); ?></option>
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
	} 
	else {	//  'tab' not set. default, album manage page.
		
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

/* get the albums */
function wppa_admin_albums() {
	global $wpdb;
	$albums = $wpdb->get_results("SELECT * FROM " . ALBUM_TABLE . " " . wppa_get_album_order(), 'ARRAY_A');
	
	if (!empty($albums)) {
?>	
	<div class="table_wrapper">	
		<table class="widefat">
			<thead>
			<tr>
				<th scope="col"><?php _e('Name', 'wppa'); ?></th>
				<th scope="col"><?php _e('Description', 'wppa'); ?></th>
				<th scope="col"><?php _e('ID', 'wppa'); ?></th>
<?php if (current_user_can('administrator')) { ?>
				<th scope="col"><?php _e('Owner', 'wppa'); ?></th>
<?php } ?>
                <th scope="col"><?php _e('Order', 'wppa'); ?></th>
                <th scope="col" style="width: 120px;"><?php _e('Parent', 'wppa'); ?></th>
				<th scope="col"><?php _e('Edit', 'wppa'); ?></th>
				<th scope="col"><?php _e('Delete', 'wppa'); ?></th>	
			</tr>
			</thead>
			
			<?php $alt = ' class="alternate" '; ?>
		
			<?php foreach ($albums as $album) if(wppa_have_access($album)) { ?>
				<tr <?php echo($alt) ?>>
					<td><?php echo(stripslashes($album['name'])) ?></td>
					<td><small><?php echo(stripslashes($album['description'])) ?></small></td>
					<td><?php echo($album['id']) ?></td>
<?php if (current_user_can('administrator')) { ?>
					<td><?php echo($album['owner']); ?></td>
<?php } ?>
					<td><?php echo($album['a_order']) ?></td>
					<td><?php wppa_album_name($album['a_parent']) ?></td>
					<td><a href="admin.php?page=<?php echo(WPPA_PLUGIN_PATH) ?>/wppa.php&amp;tab=edit&amp;edit_id=<?php echo($album['id']) ?>" class="wppaedit"><?php _e('Edit', 'wppa'); ?></a></td>
					<td><a href="admin.php?page=<?php echo(WPPA_PLUGIN_PATH) ?>/wppa.php&amp;tab=del&amp;id=<?php echo($album['id']) ?>" class="wppadelete"><?php _e('Delete', 'wppa'); ?></a></td>
				</tr>		
<?php			if ($alt == '') { $alt = ' class="alternate" '; } else { $alt = '';}
			}

?>			
		</table>
	</div>
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
						<tr valign="top">
							<th scope="row">
								<input type="button" class="button-secondary" onclick="prompt('<?php _e('Insert code for single image in Page or Post:\nYou may change the size if you like.', 'wppa') ?>', '%%wppa%% %%photo=<?php echo($photo['id']); ?>%% %%size=<?php echo(get_option('wppa_fullsize')); ?>%%')" value="<?php _e('Get insertion Code', 'wppa'); ?>" />
							</th>
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
	return wppa_have_access('any');
//	global $wpdb;	
//	$albums = $wpdb->get_results("SELECT * FROM " . ALBUM_TABLE, 'ARRAY_A');
//	if (empty($albums)) {
//		return FALSE;
//	} else {
//		return TRUE;
//	}
}

// get select form element listing albums 
function wppa_album_select($exc = '', $sel = '', $addnone = FALSE, $addseparate = FALSE, $checkancestors = FALSE) {
	global $wpdb;
	$albums = $wpdb->get_results("SELECT * FROM " . ALBUM_TABLE . " ORDER BY name", 'ARRAY_A');
	
    if ($sel == '') {
        $s = wppa_get_last_album();
        if ($s != $exc) $sel = $s;
    }
    
    $result = '';
    if ($addnone) $result .= '<option value="0">' . __('--- none ---', 'wppa') . '</option>';
    
	foreach ($albums as $album) if (wppa_have_access($album)) {
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
	
	$owner = wppa_get_user();

	if (!empty($name)) {
		$query = $wpdb->prepare('INSERT INTO `' . ALBUM_TABLE . '` (`id`, `name`, `description`, `a_order`, `a_parent`, `p_order_by`, `main_photo`, `cover_linkpage`, `owner`) VALUES (0, %s, %s, %d, %d, %d, %d, %d, %s)', $name, $desc, $order, $parent, $porder, 0, 0, $owner);
		$iret = $wpdb->query($query);
        if ($iret === FALSE) wppa_error_message(__('Could not create album.', 'wppa'));
		else {
            $id = wppa_get_album_id($name);
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

	if (isset($_POST['wppa-main'])) $main = $_POST['wppa-main'];
	else $main = '0';
	
    $order = (is_numeric($_POST['wppa-order']) ? $_POST['wppa-order'] : 0);
	
	$parent = (isset($_POST['wppa-parent']) ? $_POST['wppa-parent'] : 0);
	if ($parent == -3) $parent = 0;	// selected an unselectable item (IE < 8 ?)
	
    $orderphotos = (is_numeric($_POST['wppa-list-photos-by']) ? $_POST['wppa-list-photos-by'] : 0);
	
	$link = $_POST['cover-linkpage'];
	
	$owner = (isset($_POST['wppa-owner']) ? $_POST['wppa-owner'] : '');
	
    // update the photo information
    if (isset($_POST['photos']))
	foreach ($_POST['photos'] as $photo) {
        $photo['name'] = esc_attr($photo['name']);
		
        if (!is_numeric($photo['p_order'])) $photo['p_order'] = 0;
		
		$photo_desc = $photo['description'];
		
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
		if ($owner == '') $query = $wpdb->prepare('UPDATE `' . ALBUM_TABLE . '` SET `name` = %s, `description` = %s, `main_photo` = %s, `a_order` = %d, `a_parent` = %d, `p_order_by` = %s, `cover_linkpage` = %s WHERE `id` = %d', $name, $desc, $main, $order, $parent, $orderphotos, $link, $_GET['edit_id']);
		else $query = $wpdb->prepare('UPDATE `' . ALBUM_TABLE . '` SET `name` = %s, `description` = %s, `main_photo` = %s, `a_order` = %d, `a_parent` = %d, `p_order_by` = %s, `cover_linkpage` = %s, `owner` = %s WHERE `id` = %d', $name, $desc, $main, $order, $parent, $orderphotos, $link, $owner, $_GET['edit_id']);
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
		$output .= '<option value="0">' . __('--- random ---', 'wppa') . '</option>';

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
