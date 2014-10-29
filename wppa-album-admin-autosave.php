<?php
/* wppa-album-admin-autosave.php
* Package: wp-photo-album-plus
*
* create, edit and delete albums
* version 5.4.15
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

function _wppa_admin() {
	global $wpdb;
	global $q_config;
	global $wppa_opt;
	global $wppa_revno;
	
	if ( get_option('wppa_revision') != $wppa_revno ) wppa_check_database(true);
	
	echo '
<script type="text/javascript">
	/* <![CDATA[ */
	wppaAjaxUrl = "'.admin_url('admin-ajax.php').'";
	wppaUploadToThisAlbum = "'.__('Upload to this album', 'wppa').'";
	wppaImageDirectory = "'.wppa_get_imgdir().'";
	/* ]]> */
</script>
';

	// Delete trashed comments
	$query = "DELETE FROM " . WPPA_COMMENTS . " WHERE status='trash'";
	$wpdb->query($query);

	$sel = 'selected="selected"';

	// warn if the uploads directory is no writable
	if (!is_writable(WPPA_UPLOAD_PATH)) { 
		wppa_error_message(__('Warning:', 'wppa') . sprintf(__('The uploads directory does not exist or is not writable by the server. Please make sure that %s is writeable by the server.', 'wppa'), WPPA_UPLOAD_PATH));
	}

	// Fix orphan albums and deleted target pages
	$albs = $wpdb->get_results("SELECT * FROM `".WPPA_ALBUMS."`", ARRAY_A);
	if ( $albs ) {
		foreach ($albs as $alb) {
			if ( $alb['a_parent'] > '0' && wppa_get_parentalbumid($alb['a_parent']) == '-9' ) {	// Parent died?
				$wpdb->query("UPDATE `".WPPA_ALBUMS."` SET `a_parent` = '-1' WHERE `id` = '".$alb['id']."'");
			}
			if ( $alb['cover_linkpage'] > '0' ) {
				$iret = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".$wpdb->posts."` WHERE `ID` = %s AND `post_type` = 'page' AND `post_status` = 'publish'", $alb['cover_linkpage']));
				if ( ! $iret ) {	// Page gone?
					$wpdb->query("UPDATE `".WPPA_ALBUMS."` SET `cover_linkpage` = '0' WHERE `id` = '".$alb['id']."'");
				}
			}
		}
	}
	
	if (isset($_REQUEST['tab'])) {		
		// album edit page
		if ($_REQUEST['tab'] == 'edit'){
			if ( isset($_REQUEST['edit_id']) ) {
				$ei = $_REQUEST['edit_id'];
				if ( $ei != 'new' && ! is_numeric($ei) ) {
					wp_die('Security check failure 1');
				}
			}
			if ($_REQUEST['edit_id'] == 'new') {
				if ( ! wppa_can_create_album() ) wp_die('No rights to create an album');
				$id = wppa_nextkey(WPPA_ALBUMS);
				if (isset($_REQUEST['parent_id'])) {
					$parent = $_REQUEST['parent_id'];
					if ( ! is_numeric($parent) ) {
						wp_die('Security check failure 2');
					}
					$name = wppa_get_album_name($parent).'-#'.$id;
					if ( ! current_user_can('administrator') ) {	// someone creating an album for someone else?
						$parentowner = $wpdb->get_var($wpdb->prepare("SELECT `owner` FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $parent));
						if ( $parentowner !== wppa_get_user() ) wp_die('You are not allowed to create an album for someone else');
					}
				}
				else {
					$parent = $wppa_opt['wppa_default_parent'];
					$name = __('New Album', 'wppa');
					if ( ! wppa_can_create_top_album() ) wp_die('No rights to create a top-level album');
				}				
//				$uplim = $wppa_opt['wppa_upload_limit_count'].'/'.$wppa_opt['wppa_upload_limit_time'];
//				$query = $wpdb->prepare("INSERT INTO `" . WPPA_ALBUMS . "` (`id`, `name`, `description`, `a_order`, `a_parent`, `p_order_by`, `main_photo`, `cover_linktype`, `cover_linkpage`, `owner`, `timestamp`, `upload_limit`, `alt_thumbsize`, `default_tags`, `cover_type`, `suba_order_by`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, '', '', '')", $id, $name, '', '0', $parent, '0', '0', 'content', '0', wppa_get_user(), time(), $uplim, '0');
//				$iret = $wpdb->query($query);
				$id = wppa_create_album_entry( array( 'id' => $id, 'name' => $name, 'a_parent' => $parent ) );
				if ( ! $id ) {
					wppa_error_message( __('Could not create album.', 'wppa') );
					wp_die('Sorry, cannot continue');
				}
				else {
					$edit_id = $id;
					wppa_set_last_album($edit_id);
					wppa_flush_treecounts($edit_id);
					wppa_index_add('album', $id);
					wppa_update_message(__('Album #', 'wppa') . ' ' . $edit_id . ' ' . __('Added.', 'wppa'));
					wppa_create_pl_htaccess();
				}
			}
			else {
				$edit_id = $_REQUEST['edit_id'];
			}
		
			$album_owner = $wpdb->get_var($wpdb->prepare("SELECT `owner` FROM ".WPPA_ALBUMS." WHERE `id` = %s", $edit_id));
			if ( ( $album_owner == '--- public ---' && ! current_user_can('wppa_admin') ) || ! wppa_have_access($edit_id) ) {
				wp_die('You do not have the rights to edit this album');
			}
			
			// Apply new desc
			if ( isset($_REQUEST['applynewdesc']) ) {
				if ( ! wp_verify_nonce($_REQUEST['wppa_nonce'], 'wppa_nonce') ) wp_die('You do not have the rights to do this');
				$iret = $wpdb->query($wpdb->prepare("UPDATE `".WPPA_PHOTOS."` SET `description` = %s WHERE `album` = %s", $wppa_opt['wppa_newphoto_description'], $edit_id));
				wppa_ok_message($iret.' descriptions updated.');
			}
			
			// Remake album
			if ( isset($_REQUEST['remakealbum']) ) {
				if ( ! wp_verify_nonce($_REQUEST['wppa_nonce'], 'wppa_nonce') ) wp_die('You do not have the rights to do this');
				if ( get_option('wppa_remake_start_album_'.$edit_id) ) {	// Continue after time up
					wppa_ok_message('Continuing remake, please wait');
				}
				else {
					update_option('wppa_remake_start_album_'.$edit_id, time());
					wppa_ok_message('Remaking photofiles, please wait');
				}
				$iret = wppa_remake_files($edit_id);
				if ( $iret ) {
					wppa_ok_message('Photo files remade');
					update_option('wppa_remake_start_album_'.$edit_id, '0');
				}
				else {
					wppa_error_message('Remake of photo files did NOT complete');
				}
			}

			// Get the album information
			$albuminfo = $wpdb->get_row($wpdb->prepare('SELECT * FROM `'.WPPA_ALBUMS.'` WHERE `id` = %s', $edit_id), ARRAY_A); ?>	
			
			<div class="wrap">
				<h2><?php echo __('Edit Album Information', 'wppa').' <span style="color:blue">'.__('Auto Save', 'wppa').'</span>' ?></h2>
				<p class="description">
					<?php echo __('All modifications are instantly updated on the server, except for those that require a button push.', 'wppa');
						  echo ' '.__('The <b style="color:#070" >Remark</b> fields keep you informed on the actions taken at the background.', 'wppa');
					?>
				</p>
				<p>
					<?php _e('Album number:', 'wppa'); echo(' ' . $edit_id . '.'); ?>
				</p>
					<input type="hidden" id="album-nonce-<?php echo $edit_id ?>" value="<?php echo wp_create_nonce('wppa_nonce_'.$edit_id);  ?>" />
					<table class="widefat wppa-table wppa-album-table">
						<tbody>
						
							<!-- Name -->
							<tr>
								<th>
									<label><?php _e('Name:', 'wppa'); ?></label>
								</th>
								<?php if ( wppa_switch('wppa_use_wp_editor') ) { ?>
									<td>
										<input id="wppaalbumname" type="text" style="width: 100%;" value="<?php echo esc_attr(stripslashes($albuminfo['name'])) ?>" />
									</td>
									<td>
										<input type="button" class="button-secundary" value="<?php _e('Update Album name', 'wppa') ?>" onclick="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'name', document.getElementById('wppaalbumname') )" />
									</td>
								<?php } 
								else { ?>
									<td>
										<input type="text" style="width: 100%;" onkeyup="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'name', this)" onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'name', this)" value="<?php echo esc_attr(stripslashes($albuminfo['name'])) ?>" />
									</td>
									<td>
										<span class="description"><?php _e('Type the name of the album. Do not leave this empty.', 'wppa'); ?></span>
									</td>
								<?php } ?>
							</tr>
							
							<!-- Description -->
							<tr>
								<th>
									<label><?php _e('Description:', 'wppa'); ?></label>
								</th>
								<?php if ( wppa_switch('wppa_use_wp_editor') ) { ?>
									<td colspan="2" >
									
										<?php 
										$quicktags_settings = array( 'buttons' => 'strong,em,link,block,ins,ul,ol,li,code,close' );
										wp_editor(stripslashes($albuminfo['description']), 'wppaalbumdesc', array('wpautop' => false, 'media_buttons' => false, 'textarea_rows' => '6', 'tinymce' => false, 'quicktags' => $quicktags_settings ));
										?>
									
										<input type="button" class="button-secundary" value="<?php _e('Update Album description', 'wppa') ?>" onclick="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'description', document.getElementById('wppaalbumdesc') )" />
										<img id="wppa-album-spin" src="<?php echo wppa_get_imgdir().'wpspin.gif' ?>" style="visibility:hidden" />
										<br />
									</td>
								<?php }
								else { ?>
									<td>
										<textarea style="width: 100%; height: 80px;" onkeyup="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'description', this)" onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'description', this)" ><?php echo(stripslashes($albuminfo['description'])) ?></textarea>
									</td>
									<td>
										<span class="description"><?php _e('Enter / modify the description for this album.', 'wppa') ?></span>
									</td>
								<?php } ?>
							</tr>
							
							<!-- Modified -->
							<tr>
								<th>
									<label><?php _e('Modified:', 'wppa') ?></label>
								</th>
								<td>
									<?php echo wppa_local_date(get_option( 'date_format', "F j, Y,").' '.get_option( 'time_format', "g:i a" ), $albuminfo['timestamp'] ) ?>
								</td>
							
							<!-- Views -->
							<tr>
								<th>
									<label><?php _e('Views:', 'wppa'); ?></label>
								</th>
								<td>
									<?php echo $albuminfo['views'] ?>
								</td>
							</tr>

							<!-- Owner -->
							<?php // if ( wppa_switch('wppa_owner_only') ) { 
							if ( current_user_can('administrator') ) {
							?>
								<tr>
									<th>
										<label><?php _e('Owned by:', 'wppa'); ?></label>
									</th>
									<?php if ( $albuminfo['owner'] == '--- public ---' && !current_user_can('administrator') ) { ?>
										<td>
											<?php _e('--- public ---', 'wppa') ?>
										</td>
									<?php } else { ?>
										<td>
											<?php 
												$usercount = wppa_get_user_count();
												if ( $usercount > $wppa_opt['wppa_max_users'] ) { ?>
												<input type="text" value="<?php echo $albuminfo['owner'] ?>" onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'owner', this)" />
											<?php } else { ?>
												<select onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'owner', this)" ><?php wppa_user_select($albuminfo['owner']); ?></select>
											<?php } ?>
										</td>
										<td>
											<?php if ( ! current_user_can( 'administrator' ) ) { ?>
												<span class="description" style="color:orange;" ><?php _e('WARNING If you change the owner, you will no longer be able to modify this album and upload or import photos to it!', 'wppa'); ?></span>
											<?php } ?>
											<?php if ( $usercount > '1000' ) echo '<span class="description" >'.__('Enter user login name or <b>--- public ---</b>', 'wppa'),'</span>' ?>
										</td>
									<?php } ?>
								</tr>
							<?php } ?>
							
							<!-- Order # -->
							<tr>
								<th>
									<label><?php _e('Album sort order #:', 'wppa'); ?></label>
								</th>
								<td>
									<input type="text" onkeyup="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'a_order', this)" onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'a_order', this)" value="<?php echo($albuminfo['a_order']) ?>" style="width: 50px;"/>
								</td>
								<td>
									<?php if ( $wppa_opt['wppa_list_albums_by'] != '1' && $albuminfo['a_order'] != '0' ) { ?>
										<span class="description" style="color:red">
										<?php _e('Album order # has only effect if you set the album sort order method to <b>Order #</b> in the Photo Albums -> Settings screen.<br />', 'wppa') ?>
										</span>
									<?php } ?>
									<span class="description"><?php _e('If you want to sort the albums by order #, enter / modify the order number here.', 'wppa'); ?></span>
								</td>
							</tr>
							
							<!-- Parent -->
							<tr>
								<th>
									<label><?php _e('Parent album:', 'wppa'); ?> </label>
								</th>
								<td style="max-width:210px;">
									<?php if ( wppa_extended_access() ) { ?>
										<select id="wppa-parsel" style="max-width:100%;" onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'a_parent', this)" ><?php echo wppa_album_select_a(array('checkaccess' => true, 'exclude' => $albuminfo['id'], 'selected' => $albuminfo['a_parent'], 'addnone' => true, 'addseparate' => true, 'disableancestors' => true, 'path' => wppa_switch('wppa_hier_albsel'))) ?></select>
									<?php } else { ?>
										<select id="wppa-parsel" style="max-width:100%;" onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'a_parent', this)" ><?php echo wppa_album_select_a(array('checkaccess' => true, 'exclude' => $albuminfo['id'], 'selected' => $albuminfo['a_parent'], 'addselected' => true, 'disableancestors' => true, 'path' => wppa_switch('wppa_hier_albsel'))) ?></select>
									<?php } ?>
								</td>
								<td>
									<span class="description">
										<?php _e('If this is a sub album, select the album in which this album will appear.', 'wppa'); ?>
									</span>					
								</td>
							</tr>
							
							<!-- P-order-by -->
							<tr>
								<th>
									<?php $order = $albuminfo['p_order_by']; ?>
									<label><?php _e('Photo order:', 'wppa'); ?></label>
								</th>
								<td>
									<?php 
									$options = array(	__('--- default ---', 'wppa'), 
														__('Order #', 'wppa'), 
														__('Name', 'wppa'), 
														__('Random', 'wppa'), 
														__('Rating mean value', 'wppa'), 
														__('Number of votes', 'wppa'), 
														__('Timestamp', 'wppa'),
														__('EXIF Date', 'wppa'),
														__('Order # desc', 'wppa'), 
														__('Name desc', 'wppa'), 
														__('Rating mean value desc', 'wppa'), 
														__('Number of votes desc', 'wppa'), 
														__('Timestamp desc', 'wppa'),
														__('EXIF Date desc', 'wppa')
														);
									$values = array(	'0', 
														'1', 
														'2', 
														'3', 
														'4', 
														'6', 
														'5', 
														'7', 
														'-1', 
														'-2', 
														'-4', 
														'-6', 
														'-5', 
														'-7'
														);
									?>
									<select onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'p_order_by', this)">
									<?php
										foreach ( array_keys( $options ) as $key ) {
											$sel = $values[$key] == $order ? ' selected="selected"' : '';
											echo '<option value="'.$values[$key].'"'.$sel.' >'.$options[$key].'</option>';
										}
									?>
									</select>
								</td>
								<td>
									<span class="description">
										<?php _e('Specify the way the photos should be ordered in this album.', 'wppa'); ?><br />
										<?php if ( current_user_can('wppa_settings') ) _e('The default setting can be changed in the <b>Photo Albums -> Settings</b> page <b>Table IV-C1</b>.', 'wppa'); ?>
									</span>
								</td>
							</tr>
							
							<!-- Child album order -->
							<tr>
								<th>
									<label><?php _e('Sub album sort order:', 'wppa'); ?></label>
								</th>
								<td>
									<select onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'suba_order_by', this)" >
										<option value="0" <?php if ( ! $albuminfo['suba_order_by'] ) echo 'selected="selected"' ?>><?php _e('See Table IV-D1', 'wppa') ?></option>
										<option value="3" <?php if ( $albuminfo['suba_order_by'] == '3' ) echo 'selected="selected"' ?>><?php _e('Random', 'wppa') ?></option>
										<option value="1" <?php if ( $albuminfo['suba_order_by'] == '1' ) echo 'selected="selected"' ?>><?php _e('Order #', 'wppa') ?></option>
										<option value="-1" <?php if ( $albuminfo['suba_order_by'] == '-1' ) echo 'selected="selected"' ?>><?php _e('Order # reverse', 'wppa') ?></option>
										<option value="2" <?php if ( $albuminfo['suba_order_by'] == '2' ) echo 'selected="selected"' ?>><?php _e('Name', 'wppa') ?></option>
										<option value="-2" <?php if ( $albuminfo['suba_order_by'] == '-2' ) echo 'selected="selected"' ?>><?php _e('Name reverse', 'wppa') ?></option>
										<option value="5" <?php if ( $albuminfo['suba_order_by'] == '5' ) echo 'selected="selected"' ?>><?php _e('Timestamp', 'wppa') ?></option>
										<option value="-5" <?php if ( $albuminfo['suba_order_by'] == '-5' ) echo 'selected="selected"' ?>><?php _e('Timestamp reverse', 'wppa') ?></option>
									</select>
								</td>
								<td>
									<span class="description">
										<?php _e('Specify the sequence order method to be used for the sub albums of this album.', 'wppa') ?>
									</span>
								</td>
							</tr>
							
							<!-- Alternative thumbnail size? -->
							<?php if ( ! wppa_switch('wppa_alt_is_restricted') || current_user_can('administrator') ) { ?>
							<tr>
								<th>
									<label><?php _e('Use alt thumbsize:', 'wppa'); ?></label>
								</th>
								<td>
									<select onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'alt_thumbsize', this)" >
										<option value="0" <?php if ( ! $albuminfo['alt_thumbsize'] ) echo 'selected="selected"' ?>><?php _e('no', 'wppa') ?></option>
										<option value="yes" <?php if ( $albuminfo['alt_thumbsize'] ) echo 'selected="selected"' ?>><?php _e('yes', 'wppa') ?></option>
									</select>
								</td>
								<td>
									<span class="description">
										<?php _e('If set to <b>yes</b> The settings in <b>Table I-C1a,3a</b> and <b>4a</b> apply rather than <b>I-C1,3</b> and <b>4</b>.', 'wppa') ?>
									</span>
								</td>
							</tr>
							<?php } ?>
							
							<!-- Cover type -->
							<?php if ( ! wppa_switch('wppa_covertype_is_restricted') || current_user_can('administrator') ) { ?>
							<tr>
								<th>
									<label><?php _e('Cover Type:', 'wppa'); ?></label>
								</th>
								<td>
									<?php $sel = 'selected="selected"' ?>
									<select onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'cover_type', this)" >
										<option value="" <?php if ( $albuminfo['cover_type'] == '' ) echo $sel ?> ><?php _e('--- default ---', 'wppa') ?></option>
										<option value="default" <?php if ( $albuminfo['cover_type'] == 'default' ) echo $sel ?> ><?php _e('Standard', 'wppa') ?></option>
										<option value="longdesc" <?php if ( $albuminfo['cover_type'] == 'longdesc' ) echo $sel ?> ><?php _e('Long Descriptions', 'wppa') ?></option>
										<option value="imagefactory" <?php if ( $albuminfo['cover_type'] == 'imagefactory' ) echo $sel ?> ><?php _e('Image Factory', 'wppa') ?></option>
										<option value="default-mcr" <?php if ($albuminfo['cover_type'] == 'default-mcr' ) echo $sel ?> ><?php _e('Standard mcr', 'wppa') ?></option>
										<option value="longdesc-mcr" <?php if ( $albuminfo['cover_type'] == 'longdesc-mcr' ) echo $sel ?> ><?php _e('Long Descriptions mcr', 'wppa') ?></option>
										<option value="imagefactory-mcr" <?php if ( $albuminfo['cover_type'] == 'imagefactory-mcr' ) echo $sel ?> ><?php _e('Image Factory mcr', 'wppa') ?></option>
									</select>
								</td>
								<td>
									<span class="description">
										<?php 
											_e('The default cover type is the systems standard set in the <b>Photo Albums -> Settings</b> page <b>Table IV-D6</b>.', 'wppa'); 
										?>
									</span>
								</td>
							</tr>
							<?php } ?>
							
							<!-- Cover photo -->
							<tr>
								<th>
									<label><?php _e('Cover Photo:', 'wppa'); ?></label>
								</th>
								<td>
									<?php echo(wppa_main_photo($albuminfo['main_photo'], $albuminfo['cover_type'])) ?>
								</td>
								<td>
									<span class="description">
										<?php 
											if ( $wppa_opt['wppa_cover_type'] == 'default' ) _e('Select the photo you want to appear on the cover of this album.', 'wppa'); 
											else _e('Select the way the cover photos of this album are selected, or select a single image.', 'wppa'); 
										?>
									</span>
								</td>
							</tr>
						
							<!-- Upload limit -->
							<tr>
								<th>
									<label><?php _e('Upload limit:', 'wppa') ?></label>
								</th>
								<td>
								<?php
									$lims = explode('/', $albuminfo['upload_limit']);
									if ( current_user_can('administrator') ) { ?>
										<input type="text" id="upload_limit_count" value="<?php echo($lims[0]) ?>" style="width: 50px" onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'upload_limit_count', this)" />
										<select onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'upload_limit_time', this)" >
											<option value="0" <?php if ($lims[1] == '0') echo 'selected="selected"' ?>><?php _e('for ever', 'wppa') ?></option>
											<option value="3600" <?php if ($lims[1] == '3600') echo 'selected="selected"' ?>><?php _e('per hour', 'wppa') ?></option>
											<option value="86400" <?php if ($lims[1] == '86400') echo 'selected="selected"' ?>><?php _e('per day', 'wppa') ?></option>
											<option value="604800" <?php if ($lims[1] == '604800') echo 'selected="selected"' ?>><?php _e('per week', 'wppa') ?></option>
											<option value="2592000" <?php if ($lims[1] == '2592000') echo 'selected="selected"' ?>><?php _e('per month', 'wppa') ?></option>
											<option value="31536000" <?php if ($lims[1] == '31536000') echo 'selected="selected"' ?>><?php _e('per year', 'wppa') ?></option>
										</select>
										</td>
										<td>
										<span class="description"><?php _e('Set the upload limit (0 means unlimited) and the upload limit period.', 'wppa'); ?></span>
										<?php
									}
									else {
										
										if ( $lims[0] == '0' ) _e('Unlimited', 'wppa');
										else {
											echo $lims[0].' ';
											switch ($lims[1]) {
												case '3600': _e('per hour', 'wppa'); break;
												case '86400': _e('per day', 'wppa'); break;
												case '604800': _e('per week', 'wppa'); break;
												case '2592000': _e('per month', 'wppa'); break;
												case '31536000': _e('per year', 'wppa'); break;
											}
										}
									}
								?>
								</td>
							</tr>
							
							<!-- Cats -->
							<tr>
								<th>
									<label><?php _e('Catogories:', 'wppa') ?></label>
									<span class="description" >
										<br />&nbsp;
									</span>
								</th>
								<td>
									<input id="cats" type="text" style="width:100%;" onkeyup="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'cats', this)" onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'cats', this)" value="<?php echo(stripslashes($albuminfo['cats'])) ?>" />
								</td>
								<td>
									<span class="description" >
										<?php _e('Separate categories with commas.', 'wppa') ?>&nbsp;
										<?php _e('Examples:', 'wppa');
											$catlist = wppa_get_catlist(); 
										?>
										<select onchange="wppaAddCat(this.value, 'cats'); wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'cats', document.getElementById('cats'))" >
											<?php 
											if ( is_array($catlist) ) {
												echo '<option value="" >'.__('- select -', 'wppa').'</option>';
												foreach ( $catlist as $cat ) {
													echo '<option value="'.$cat['cat'].'" >'.$cat['cat'].'</option>';
												}
											}
											else {
												echo '<option value="0" >'.__('No categories yet', 'wppa').'</option>';
											}
											?>
										</select>
										<?php _e('Select to add', 'wppa') ?>
									</span>
								</td>
							</tr>
							
							<!-- Default tags -->
							<tr>
								<th>
									<label><?php _e('Default photo tags:', 'wppa') ?></label>
								</th>
								<td>
									<input type="text" id="default_tags" value="<?php echo $albuminfo['default_tags'] ?>" style="width: 100%" onkeyup="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'default_tags', this)" onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'default_tags', this)" />
								</td>
								<td>
									<span class="description"><?php _e('Enter the tags that you want to be assigned to new photos in this album.', 'wppa') ?></span>
								</td>
							</tr>
							
							<!-- Apply default tags -->
							<?php $onc1 = 'if (confirm(\''.__('Are you sure you want to set the default tags to all photos in this album?', 'wppa').'\')) { alert(\'The page will be reloaded after the action has taken place.\');wppaRefreshAfter(); wppaAjaxUpdateAlbum('.$edit_id.', \'set_deftags\', 0 ); }'; ?>
							<?php $onc2 = 'if (confirm(\''.__('Are you sure you want to add the default tags to all photos in this album?', 'wppa').'\')) { alert(\'The page will be reloaded after the action has taken place.\');wppaRefreshAfter(); wppaAjaxUpdateAlbum('.$edit_id.', \'add_deftags\', 0 ); }'; ?>
							<tr>
								<th>
									<a onclick="<?php echo $onc1 ?>" ><?php _e('Apply default tags', 'wppa') ?></a>
								</th>
								<td>
								</td>
								<td>
									<span class="description"><?php _e('Tag all photos in this album with the default tags.', 'wppa') ?></span>
								</td>
							</tr>
							<tr>
								<th>
									<a onclick="<?php echo $onc2 ?>" ><?php _e('Add default tags', 'wppa') ?></a>
								</th>
								<td>
								</td>
								<td>
									<span class="description"><?php _e('Add the default tags to all photos in this album.', 'wppa') ?></span>
								</td>
							</tr>

							<!-- Link type -->
							<tr>
								<th>
									<label><?php _e('Link type:', 'wppa') ?></label>
								</th>
								<td>
									<?php $linktype = $albuminfo['cover_linktype']; ?>
									<?php /* if ( !$linktype ) $linktype = 'content'; /* Default */ ?>	
									<?php /* if ( $albuminfo['cover_linkpage'] == '-1' ) $linktype = 'none'; /* for backward compatibility */ ?>
									<select onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'cover_linktype', this)" >
										<option value="content" <?php if ( $linktype == 'content' ) echo ($sel) ?>><?php _e('the sub-albums and thumbnails', 'wppa') ?></option>
										<option value="slide" <?php if ( $linktype == 'slide' ) echo ($sel) ?>><?php _e('the album photos as slideshow', 'wppa') ?></option>
										<option value="page" <?php if ( $linktype == 'page' ) echo ($sel) ?>><?php _e('the link page with a clean url', 'wppa') ?></option>
										<option value="none" <?php if ( $linktype == 'none' ) echo($sel) ?>><?php _e('no link at all', 'wppa') ?></option>
									</select>
								</td>
								<td>
									<span class="description">
										<?php 	if ( wppa_switch( 'wppa_auto_page') ) _e('If you select "the link page with a clean url", select an Auto Page of one of the photos in this album.', 'wppa'); 
												else _e('If you select "the link page with a clean url", make sure you enter the correct shortcode on the target page.', 'wppa'); ?>
									</span>
								</td>
							</tr>
							
							<!-- Link page -->
							<?php if ( ! wppa_switch('wppa_link_is_restricted') || current_user_can('administrator') ) { ?>
							<tr>
								<th>
									<label><?php _e('Link to:', 'wppa'); ?></label>
								</th>
								<td style="max-width:210px;" >
									<?php $query = 'SELECT `ID`, `post_title` FROM `'.$wpdb->posts.'` WHERE `post_type` = \'page\' AND `post_status` = \'publish\' ORDER BY `post_title` ASC';
									$pages = $wpdb->get_results($query, ARRAY_A);
									if (empty($pages)) {
										_e('There are no pages (yet) to link to.', 'wppa');
									} else {
										$linkpage = $albuminfo['cover_linkpage'];
										if (!is_numeric($linkpage)) $linkpage = '0'; ?>
										<select onchange="wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'cover_linkpage', this)" style="max-width:100%;">
											<option value="0" <?php if ($linkpage == '0') echo($sel); ?>><?php _e('--- the same page or post ---', 'wppa'); ?></option>
											<?php foreach ($pages as $page) { ?>
												<option value="<?php echo($page['ID']); ?>" <?php if ($linkpage == $page['ID']) echo($sel); ?>><?php _e($page['post_title']); ?></option>
											<?php } ?>
										</select>
								</td>
								<td>
										<span class="description">
											<?php _e('If you want, you can link the title to a WP page in stead of the album\'s content. If so, select the page the title links to.', 'wppa'); ?>
										</span>
									<?php }	?>
								</td>
							</tr>
							<?php } ?>
							
							<!-- Schedule -->
							<tr>
								<th>
									<label><?php _e('Schedule:', 'wppa') ?></label>
									<input type="checkbox" <?php if ( $albuminfo['scheduledtm'] ) echo 'checked="checked"' ?> onchange="wppaChangeScheduleAlbum(<?php echo $edit_id ?>, this);" />
								</th>
								<td>
									<input type="hidden" value="" id="wppa-dummy" />
									<span class="wppa-datetime-<?php echo $edit_id ?>" <?php if ( ! $albuminfo['scheduledtm'] ) echo 'style="display:none;"' ?> >
										<?php echo wppa_get_date_time_select_html( 'album', $edit_id, true ) ?>
									</span>
								</td>
								<td>
									<span class="description">
										<?php _e('If enabled, new photos will have their status set to the dat/time specified here.', 'wppa'); ?>
									</span>
								</td>
							</tr>
							<tr class="wppa-datetime-<?php echo $edit_id ?>" >
								<th>
									<a onclick="if (confirm('<?php _e('Are you sure you want to schedule all photos in this album?', 'wppa') ?>')) { alert('The page will be reloaded after the action has taken place.'); wppaRefreshAfter(); wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'setallscheduled', 0 ) }" ><?php _e('Schedule all', 'wppa') ?></a> 
								</th>
							</tr>

							<!-- Reset Ratings -->
							<?php if ( wppa_switch('wppa_rating_on') ) { ?>
								<tr>
									<th>
										<a onclick="if (confirm('<?php _e('Are you sure you want to clear the ratings in this album?', 'wppa') ?>')) wppaAjaxUpdateAlbum(<?php echo $edit_id ?>, 'clear_ratings', 0 )" ><?php _e('Reset ratings', 'wppa') ?></a> 
									</th>
								</tr>
							<?php } ?>
							
							<!-- Goto Upload -->
							<?php if ( current_user_can('wppa_upload') ) { 
								$a = wppa_allow_uploads($albuminfo['id']);
								if ( $a ) {
									$full = 'none';
									$notfull = '';
								}
								else {
									$full = '';
									$notfull = 'none';
								} 
								$onc = 'document.location = \''.wppa_dbg_url(get_admin_url()).'/admin.php?page=wppa_upload_photos&wppa-set-album='.$albuminfo['id'].'\'';
								$oncfull = 'alert(\''.__('Change the upload limit or remove photos to enable new uploads.', 'wppa').'\')';
								?>
								<tr>
									<th>
										<a id="notfull" style="display:<?php echo $notfull ?>" onclick="<?php echo $onc ?>" ><?php _e('Upload to this album', 'wppa'); if ( $a > '0') echo ' '.sprintf(__('(max %d)', 'wppa'), $a) ?></a> 
										<a id="full" style="display:<?php echo $full ?>" onclick="<?php echo $oncfull ?>" ><?php _e('Album is full', 'wppa') ?></a> 
									</th>
								</tr>
							<?php } ?>
							
							<!-- Apply New photo desc -->
							<?php if ( wppa_switch('wppa_apply_newphoto_desc') ) { 
							$onc = 'if ( confirm(\'Are you sure you want to set the description of all photos to \n\n'.esc_js($wppa_opt['wppa_newphoto_description']).'\')) document.location=\''.wppa_ea_url($albuminfo['id'], 'edit').'&applynewdesc\'';
							?>
								<tr>
									<th>
										<a onclick="<?php echo $onc ?>" ><?php _e('Apply new photo desc', 'wppa') ?></a>
									</th>
								</tr>
							<?php } ?>
							
							<!-- Remake all -->
							<?php if ( current_user_can('administrator') ) { 
							$onc = 'if ( confirm(\'Are you sure you want to remake the files for all photos in this album?\')) document.location=\''.wppa_ea_url($albuminfo['id'], 'edit').'&remakealbum\'';
							?>
								<tr>
									<th>
										<a onclick="<?php echo $onc ?>" ><?php _e('Remake all', 'wppa') ?></a>
									</th>
								</tr>
							<?php } ?>
							
							<!-- Status -->
							<tr >
								<th style="color:blue;" >
									<label style="color:#070"><?php _e('Remark:', 'wppa') ?></label>
								</th>
								<td id="albumstatus-<?php echo $edit_id ?>" >
									<?php echo sprintf(__('Album %s is not modified yet', 'wppa'), $edit_id) ?>
								</td>
							</tr>
						</tbody>
					</table>
<a name="manage-photos" id="manage-photos" ></a>							
				<h2><?php _e('Manage Photos', 'wppa'); 
					if ( isset($_REQUEST['bulk']) ) echo ' - <small><i>'.__('Copy / move / delete / edit name / edit description / change status', 'wppa').'</i></small>';
					elseif ( isset($_REQUEST['seq']) ) echo ' - <small><i>'.__('Change sequence order by drag and drop', 'wppa').'</i></small>';
					elseif ( isset($_REQUEST['quick']) ) echo ' - <small><i>'.__('Edit photo information except copy and move', 'wppa').'</i></small>';
					else echo ' - <small><i>'.__('Edit photo information', 'wppa').'</i></small>';
				?></h2>
				<?php 
					if ( isset($_REQUEST['bulk']) ) wppa_album_photos_bulk($edit_id);
					elseif ( isset($_REQUEST['seq']) ) wppa_album_photos_sequence($edit_id);
					else wppa_album_photos($edit_id) 
				?>
				<br /><a href="#manage-photos"><?php _e('Top of page', 'wppa') ?></a>
			</div>
<?php 	} 

		// Comment moderate
		else if ($_REQUEST['tab'] == 'cmod') {
			$photo = $_REQUEST['photo'];
			$alb = wppa_get_album_id_by_photo_id($photo);
			if ( current_user_can('wppa_comments') && wppa_have_access($alb) ) { ?>
				<div class="wrap">
					<h2><?php _e('Moderate comment', 'wppa') ?></h2>
				<?php //	<input type="hidden" id="album-nonce-<?php echo $edit_id ?><?//" value="<?php echo wp_create_nonce('wppa_nonce_'.$edit_id);  ?><?//" /> ?>
					<?php wppa_album_photos('', $photo) ?>
				</div>				
<?php		}
			else {
				wp_die('You do not have the rights to do this');
			}
		}
		
		// Photo moderate
		elseif ( $_REQUEST['tab'] == 'pmod' || $_REQUEST['tab'] == 'pedit' ) {
			$photo = $_REQUEST['photo'];
			$alb = wppa_get_album_id_by_photo_id($photo);
			if ( current_user_can('wppa_admin') && wppa_have_access($alb) ) { ?>
				<div class="wrap">
					<h2><?php 	if ( $_REQUEST['tab'] == 'pmod' ) _e('Moderate photo', 'wppa');
								else _e('Edit photo', 'wppa'); ?>
					</h2>
					<?php wppa_album_photos('', $photo) ?>
				</div>				
<?php		}
			else {
				wp_die('You do not have the rights to do this');
			}
		}

		// album delete confirm page
		else if ($_REQUEST['tab'] == 'del') { 

			$album_owner = $wpdb->get_var($wpdb->prepare("SELECT `owner` FROM ".WPPA_ALBUMS." WHERE `id` = %s", $_REQUEST['edit_id']));
			if ( ( $album_owner == '--- public ---' && ! current_user_can('administrator') ) || ! wppa_have_access($_REQUEST['edit_id']) ) {
				wp_die('You do not have the rights to delete this album');
			}
?>			
			<div class="wrap">
				<?php $iconurl = WPPA_URL.'/images/albumdel32.png'; ?>
				<div id="icon-albumdel" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
					<br />
				</div>

				<h2><?php _e('Delete Album', 'wppa'); ?></h2>
				
				<p><?php _e('Album:', 'wppa'); ?> <b><?php echo wppa_get_album_name($_REQUEST['edit_id']); ?>.</b></p>
				<p><?php _e('Are you sure you want to delete this album?', 'wppa'); ?><br />
					<?php _e('Press Delete to continue, and Cancel to go back.', 'wppa'); ?>
				</p>
				<form name="wppa-del-form" action="<?php echo(wppa_dbg_url(get_admin_url().'admin.php?page=wppa_admin_menu')) ?>" method="post">
					<?php wp_nonce_field('$wppa_nonce', WPPA_NONCE) ?>
					<p>
						<?php _e('What would you like to do with photos currently in the album?', 'wppa'); ?><br />
						<input type="radio" name="wppa-del-photos" value="delete" checked="checked" /> <?php _e('Delete', 'wppa'); ?><br />
						<input type="radio" name="wppa-del-photos" value="move" /> <?php _e('Move to:', 'wppa'); ?> 
						<select name="wppa-move-album">
							<?php echo wppa_album_select_a(array('checkaccess' => true, 'path' => wppa_switch('wppa_hier_albsel'), 'selected' => '0', 'exclude' => $_REQUEST['edit_id'], 'addpleaseselect' => true)) ?>
						</select>
					</p>
				
					<input type="hidden" name="wppa-del-id" value="<?php echo($_REQUEST['edit_id']) ?>" />
					<input type="button" class="button-primary" value="<?php _e('Cancel', 'wppa'); ?>" onclick="parent.history.back()" />
					<input type="submit" class="button-primary" style="color: red" name="wppa-del-confirm" value="<?php _e('Delete', 'wppa'); ?>" />
				</form>
			</div>
<?php	
		}
	} 
	else {	//  'tab' not set. default, album manage page.
		
		// if add form has been submitted
//		if (isset($_POST['wppa-na-submit'])) {
//			check_admin_referer( '$wppa_nonce', WPPA_NONCE );

//			wppa_add_album();
//		}
		
		// if album deleted
		if (isset($_POST['wppa-del-confirm'])) {
			check_admin_referer( '$wppa_nonce', WPPA_NONCE );
			
			$album_owner = $wpdb->get_var($wpdb->prepare("SELECT `owner` FROM ".WPPA_ALBUMS." WHERE `id` = %s", $_POST['wppa-del-id']));
			if ( ( $album_owner == '--- public ---' && ! current_user_can('administrator') ) || ! wppa_have_access($_POST['wppa-del-id']) ) {
				wp_die('You do not have the rights to delete this album');
			}

			if ($_POST['wppa-del-photos'] == 'move') {
				$move = $_POST['wppa-move-album'];
				if ( wppa_have_access($move) ) {
					wppa_del_album($_POST['wppa-del-id'], $move);
				}
				else {
					wppa_error_message(__('Unable to move photos. Album not deleted.', 'wppa'));
				}
			} else {
				wppa_del_album($_POST['wppa-del-id'], '');
			}
		}
		
		if ( wppa_extended_access() ) {
			if ( isset($_REQUEST['switchto']) ) update_option('wppa_album_table_'.wppa_get_user(), $_REQUEST['switchto']);
			$style = get_option('wppa_album_table_'.wppa_get_user(), 'flat');
		}
		else $style = 'flat';
		
		// The Manage Album page 
?>	
		<div class="wrap">
			<?php $iconurl = WPPA_URL.'/images/album32.png'; ?>
			<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
				<br />
			</div>

			<h2><?php _e('Manage Albums', 'wppa'); ?></h2>
			<br />
			<?php 
			// The Create new album button
			if ( wppa_can_create_top_album() ) {
				$url = wppa_dbg_url(get_admin_url().'admin.php?page=wppa_admin_menu&amp;tab=edit&amp;edit_id=new');
				$vfy = __('Are you sure you want to create a new album?', 'wppa');
				echo '<form method="post" action="'.get_admin_url().'admin.php?page=wppa_admin_menu" style="float:left; margin-right:12px;" >';
//				echo '<input type="hidden" name="page" value="wppa_admin_menu" />';
				echo '<input type="hidden" name="tab" value="edit" />';
				echo '<input type="hidden" name="edit_id" value="new" />';
				echo '<input type="submit" class="button-primary" onclick="return confirm(\''.$vfy.'\');" value="'.__('Create New Empty Album', 'wppa').'" style="height:28px;" />';
				echo '</form>';
			}
			// The switch to button(s)
			if ( wppa_extended_access() ) {
				if ( $style == 'flat' ) { ?>
					<input type="button" class="button-secundary" onclick="document.location='<?php echo wppa_dbg_url(get_admin_url().'admin.php?page=wppa_admin_menu&amp;switchto=collapsable') ?>'" value="<?php _e('Switch to Collapsable table', 'wppa'); ?>" />		
				<?php } 
				if ( $style == 'collapsable' ) { ?>
					<input type="button" class="button-secundary" onclick="document.location='<?php echo wppa_dbg_url(get_admin_url().'admin.php?page=wppa_admin_menu&amp;switchto=flat') ?>'" value="<?php _e('Switch to Flat table', 'wppa'); ?>" />		
				<?php } 
			} ?>
			
			<br />
			<?php // The table of existing albums 
				if ( $style == 'flat' ) wppa_admin_albums_flat();
				else wppa_admin_albums_collapsable(); 
			?>
			<br />
		</div>
<?php	
	}
}

// The albums table flat
function wppa_admin_albums_flat() {
	global $wpdb;
	
	// Read the albums
	$albums = $wpdb->get_results( "SELECT * FROM `" . WPPA_ALBUMS . "` ORDER BY `id`", ARRAY_A );

	// Find the ordering method
	$reverse = false;
	if ( isset($_REQUEST['order_by']) ) $order = $_REQUEST['order_by']; else $order = '';
	if ( ! $order ) {
		$order = get_option('wppa_album_order_'.wppa_get_user(), 'id');
		$reverse = (get_option('wppa_album_order_'.wppa_get_user().'_reverse') == 'yes');
	}
	else {
		$old_order = get_option('wppa_album_order_'.wppa_get_user(), 'id');
		$reverse = (get_option('wppa_album_order_'.wppa_get_user().'_reverse') == 'yes');
		if ( $old_order == $order ) {
			$reverse = ! $reverse;
		}
		else $reverse = false;
		update_option('wppa_album_order_'.wppa_get_user(), $order);
		if ( $reverse ) update_option('wppa_album_order_'.wppa_get_user().'_reverse', 'yes');
		else update_option('wppa_album_order_'.wppa_get_user().'_reverse', 'no');
	}
	
	if ( ! empty($albums) ) {

		// Setup the sequence array
		$seq = false;
		$num = false;
		foreach( $albums as $album ) {
			switch ( $order ) {
				case 'name':
					$seq[] = strtolower(wppa_qtrans(stripslashes($album['name'])));
					break;
				case 'description':
					$seq[] = strtolower(wppa_qtrans(stripslashes($album['description'])));
					break;
				case 'owner':
					$seq[] = strtolower($album['owner']);
					break;
				case 'a_order':
					$seq[] = $album['a_order'];
					$num = true;
					break;
				case 'a_parent':
					$seq[] = strtolower(wppa_get_album_name($album['a_parent'], 'extended'));
					break;
				default:
					$seq[] = $album['id'];
					$num = true;
					break;
			}
		}
		
		// Sort the seq array
		if ( $num ) asort($seq, SORT_NUMERIC);
		else asort($seq, SORT_REGULAR);

		// Reverse ?
		if ( $reverse ) {
			$t = $seq;
			$c = count($t);
			$tmp = array_keys($t);
			$seq = false;
			for ( $i = $c-1; $i >=0; $i-- ) {
				$seq[$tmp[$i]] = '0';
			}
		}

		$downimg = '<img src="'.wppa_get_imgdir().'down.png" alt="down" style=" height:12px; position:relative; top:2px; " />';
		$upimg   = '<img src="'.wppa_get_imgdir().'up.png" alt="up" style=" height:12px; position:relative; top:2px; " />';
?>	
<!--	<div class="table_wrapper">	-->
		<table class="wppa-table widefat wppa-setting-table" style="margin-top:12px;" >
			<thead>
			<tr>
				<?php $url = get_admin_url().'admin.php?page=wppa_admin_menu&amp;order_by='; ?>
				<td  style="min-width: 50px;" >
					<a href="<?php echo wppa_dbg_url($url.'id') ?>">
						<?php _e('ID', 'wppa');
							if ($order == 'id') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>					
					</a>
				</td>
				<td  style="min-width: 120px;">
					<a href="<?php echo wppa_dbg_url($url.'name') ?>">
						<?php _e('Name', 'wppa'); 
							if ($order == 'name') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<td >
					<a href="<?php echo wppa_dbg_url($url.'description') ?>">
						<?php _e('Description', 'wppa'); 
							if ($order == 'description') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<?php if (current_user_can('administrator')) { ?>
				<td  style="min-width: 100px;">
					<a href="<?php echo wppa_dbg_url($url.'owner') ?>">
						<?php _e('Owner', 'wppa'); 
							if ($order == 'owner') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<?php } ?>
                <td  style="min-width: 100px;" >
					<a href="<?php echo wppa_dbg_url($url.'a_order') ?>">
						<?php _e('Order', 'wppa'); 
							if ($order == 'a_order') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
                <td  style="width: 120px;">
					<a href="<?php echo wppa_dbg_url($url.'a_parent') ?>">
						<?php _e('Parent', 'wppa'); 
							if ($order == 'a_parent') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<td  title="<?php _e('Albums/Photos/Moderation required/Scheduled', 'wppa') ?>" >
					<?php _e('A/P/PM/S', 'wppa'); ?>
				</td>
				<td ><?php _e('Edit', 'wppa'); ?></td>
				<td ><?php _e('Quick', 'wppa'); ?></td>
				<td ><?php _e('Bulk', 'wppa'); ?></td>
				<td ><?php _e('Seq', 'wppa'); ?></td>
				<td ><?php _e('Delete', 'wppa'); ?></td>	
				<?php if ( wppa_can_create_album() ) echo '<td >'.__('Create', 'wppa').'</td>'; ?>
			</tr>
			</thead>
			<tbody>
			<?php $alt = ' class="alternate" '; ?>
		
			<?php
//				foreach ($albums as $album) if(wppa_have_access($album)) { 
				$idx = '0';
				foreach (array_keys($seq) as $s) {
					$album = $albums[$s];
					if (wppa_have_access($album)) {
						$counts = wppa_treecount_a($album['id']);
						$pendcount = $counts['pendphotos'];
//						$pendcount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_PHOTOS."` WHERE album=%s AND status=%s", $album['id'], 'pending')); 
						?>
						<tr <?php echo($alt); if ($pendcount) echo 'style="background-color:#ffdddd"' ?>>
							<td><?php echo($album['id']) ?></td>
							<td><?php echo(esc_attr(wppa_qtrans(stripslashes($album['name'])))) ?></td>
							<td><small><?php echo(esc_attr(wppa_qtrans(stripslashes($album['description'])))) ?></small></td>
							<?php if (current_user_can('administrator')) { ?>
								<td><?php echo($album['owner']); ?></td>
							<?php } ?>
							<td><?php echo($album['a_order']) ?></td>
							<td><?php echo wppa_get_album_name($album['a_parent'], 'extended') ?></td>
							<?php $url = wppa_dbg_url(get_admin_url().'admin.php?page=wppa_admin_menu&amp;tab=edit&amp;edit_id='.$album['id']); ?>
							<?php $na = $counts['selfalbums']; ?>
							<?php $np = $counts['selfphotos']; ?>
							<?php $nm = $counts['pendphotos']; ?>
							<?php $ns = $counts['scheduledphotos']; ?>
							<td><?php echo $na.'/'.$np.'/'.$nm.'/'.$ns; ?></td>
							<?php if ( $album['owner'] != '--- public ---' || current_user_can('wppa_admin') ) { ?>
								<?php $url = wppa_ea_url($album['id']) ?>
								<td><a href="<?php echo($url) ?>" class="wppaedit"><?php _e('Edit', 'wppa'); ?></a></td>
								<td><a href="<?php echo($url.'&amp;quick') ?>" class="wppaedit"><?php _e('Quick', 'wppa'); ?></a></td>
								<td><a href="<?php echo($url.'&amp;bulk#manage-photos') ?>" class="wppaedit"><?php _e('Bulk', 'wppa'); ?></a></td>
								<td><a href="<?php echo($url.'&amp;seq') ?>" class="wppaedit"><?php _e('Seq', 'wppa'); ?></a></td>
								
								<?php $url = wppa_ea_url($album['id'], 'del') ?>
								<td><a href="<?php echo($url) ?>" class="wppadelete"><?php _e('Delete', 'wppa'); ?></a></td>
								<?php if ( wppa_can_create_album() ) {
									$url = wppa_dbg_url(get_admin_url().'admin.php?page=wppa_admin_menu&amp;tab=edit&amp;edit_id=new&amp;parent_id='.$album['id']);
									$onc = 'if (confirm(\''.__('Are you sure you want to create a subalbum?', 'wppa').'\')) document.location=\''.$url.'\';';
									echo '<td><a onclick="'.$onc.'" class="wppacreate">'.__('Create', 'wppa').'</a></td>'; 
								}
							}
							else { ?>
							<td></td><td></td><?php if ( wppa_can_create_album() ) echo '<td></td' ?>
							<?php } ?>
						</tr>		
						<?php if ($alt == '') { $alt = ' class="alternate" '; } else { $alt = '';}
					}
					$idx++;
				}
			
?>	
			</tbody>
			<tfoot>
			<tr>
				<?php $url = get_admin_url().'admin.php?page=wppa_admin_menu&amp;order_by='; ?>
				<td >
					<a href="<?php echo wppa_dbg_url($url.'id') ?>">
						<?php _e('ID', 'wppa');
							if ($order == 'id') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>					
					</a>
				</td>
				<td  style="width: 120px;">
					<a href="<?php echo wppa_dbg_url($url.'name') ?>">
						<?php _e('Name', 'wppa'); 
							if ($order == 'name') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<td >
					<a href="<?php echo wppa_dbg_url($url.'description') ?>">
						<?php _e('Description', 'wppa'); 
							if ($order == 'description') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<?php if (current_user_can('administrator')) { ?>
				<td  style="width: 100px;">
					<a href="<?php echo wppa_dbg_url($url.'owner') ?>">
						<?php _e('Owner', 'wppa'); 
							if ($order == 'owner') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<?php } ?>
                <td >
					<a href="<?php echo wppa_dbg_url($url.'a_order') ?>">
						<?php _e('Order', 'wppa'); 
							if ($order == 'a_order') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
                <td  style="width: 120px;">
					<a href="<?php echo wppa_dbg_url($url.'a_parent') ?>">
						<?php _e('Parent', 'wppa'); 
							if ($order == 'a_parent') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<td  title="<?php _e('Albums/Photos/Moderation required/Scheduled', 'wppa') ?>" >
					<?php _e('A/P/PM/S', 'wppa'); ?>
				</td>
				<td ><?php _e('Edit', 'wppa'); ?></td>
				<td ><?php _e('Quick', 'wppa'); ?></td>
				<td ><?php _e('Bulk', 'wppa'); ?></td>
				<td ><?php _e('Seq', 'wppa'); ?></td>
				<td ><?php _e('Delete', 'wppa'); ?></td>	
				<?php if ( wppa_can_create_album() ) echo '<td >'.__('Create', 'wppa').'</td>'; ?>
			</tr>
			</tfoot>
		
		</table>
<!--	</div> -->
<?php
	wppa_album_admin_footer();

?>
<?php	
	} else { 
?>
	<p><?php _e('No albums yet.', 'wppa'); ?></p>
<?php
	}
}

// The albums table collapsable
function wppa_admin_albums_collapsable() {
	global $wpdb;
	
	// Read the albums
	$albums = $wpdb->get_results( "SELECT * FROM `" . WPPA_ALBUMS . "` ORDER BY `id`", ARRAY_A);

	// Find the ordering method
	$reverse = false;
	if ( isset($_REQUEST['order_by']) ) $order = $_REQUEST['order_by']; else $order = '';
	if ( ! $order ) {
		$order = get_option('wppa_album_order_'.wppa_get_user(), 'id');
		$reverse = (get_option('wppa_album_order_'.wppa_get_user().'_reverse') == 'yes');
	}
	else {
		$old_order = get_option('wppa_album_order_'.wppa_get_user(), 'id');
		$reverse = (get_option('wppa_album_order_'.wppa_get_user().'_reverse') == 'yes');
		if ( $old_order == $order ) {
			$reverse = ! $reverse;
		}
		else $reverse = false;
		update_option('wppa_album_order_'.wppa_get_user(), $order);
		if ( $reverse ) update_option('wppa_album_order_'.wppa_get_user().'_reverse', 'yes');
		else update_option('wppa_album_order_'.wppa_get_user().'_reverse', 'no');
	}
	
	if ( ! empty($albums) ) {

		// Setup the sequence array
		$seq = false;
		$num = false;
		foreach( $albums as $album ) {
			switch ( $order ) {
				case 'name':
					$seq[] = strtolower(wppa_qtrans(stripslashes($album['name'])));
					break;
				case 'description':
					$seq[] = strtolower(wppa_qtrans(stripslashes($album['description'])));
					break;
				case 'owner':
					$seq[] = strtolower($album['owner']);
					break;
				case 'a_order':
					$seq[] = $album['a_order'];
					$num = true;
					break;
				case 'a_parent':
					$seq[] = strtolower(wppa_get_album_name($album['a_parent']), 'extended');
					break;
				default:
					$seq[] = $album['id'];
					$num = true;
					break;
			}
		}
		
		// Sort the seq array
		if ( $num ) asort($seq, SORT_NUMERIC);
		else asort($seq, SORT_REGULAR);

		// Reverse ?
		if ( $reverse ) {
			$t = $seq;
			$c = count($t);
			$tmp = array_keys($t);
			$seq = false;
			for ( $i = $c-1; $i >=0; $i-- ) {
				$seq[$tmp[$i]] = '0';
			}
		}

		$downimg = '<img src="'.wppa_get_imgdir().'down.png" alt="down" style=" height:12px; position:relative; top:2px; " />';
		$upimg   = '<img src="'.wppa_get_imgdir().'up.png" alt="up" style=" height:12px; position:relative; top:2px; " />';
?>	
<!--	<div class="table_wrapper">	-->
		<table class="widefat wppa-table wppa-setting-table" style="margin-top:12px;" >
			<thead>
			<tr>
				<td style="min-width:20px;" >
					<img src="<?php echo wppa_get_imgdir().'backarrow.gif' ?>" style="height:16px;" title="<?php _e('Collapse subalbums', 'wppa') ?>" />
					<img src="<?php echo wppa_get_imgdir().'arrow.gif' ?>" style="height:16px;" title="<?php _e('Expand subalbums', 'wppa') ?>" />
				</td>
				<?php $url = get_admin_url().'admin.php?page=wppa_admin_menu&amp;order_by='; ?>
				<td  colspan="6" style="min-width: 50px;" >
					<a href="<?php echo wppa_dbg_url($url.'id') ?>">
						<?php _e('ID', 'wppa');
							if ($order == 'id') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>					
					</a>
				</td>
				
				<td  style="min-width: 120px;">
					<a href="<?php echo wppa_dbg_url($url.'name') ?>">
						<?php _e('Name', 'wppa'); 
							if ($order == 'name') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<td >
					<a href="<?php echo wppa_dbg_url($url.'description') ?>">
						<?php _e('Description', 'wppa'); 
							if ($order == 'description') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<?php if (current_user_can('administrator')) { ?>
				<td  style="min-width: 100px;">
					<a href="<?php echo wppa_dbg_url($url.'owner') ?>">
						<?php _e('Owner', 'wppa'); 
							if ($order == 'owner') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<?php } ?>
                <td  style="min-width: 100px;" >
					<a href="<?php echo wppa_dbg_url($url.'a_order') ?>">
						<?php _e('Order', 'wppa'); 
							if ($order == 'a_order') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
                <td  style="width: 120px;">
					<a href="<?php echo wppa_dbg_url($url.'a_parent') ?>">
						<?php _e('Parent', 'wppa'); 
							if ($order == 'a_parent') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<td  title="<?php _e('Albums/Photos/Moderation required/Scheduled', 'wppa') ?>" >
					<?php _e('A/P/PM/S', 'wppa'); ?>
				</td>
				<td ><?php _e('Edit', 'wppa'); ?></td>
				<td ><?php _e('Quick', 'wppa'); ?></td>
				<td ><?php _e('Bulk', 'wppa'); ?></td>
				<td ><?php _e('Seq', 'wppa'); ?></td>
				<td ><?php _e('Delete', 'wppa'); ?></td>	
				<?php if ( wppa_can_create_album() ) echo '<td >'.__('Create', 'wppa').'</td>'; ?>
			</tr>
			</thead>
			<tbody>
		
			<?php wppa_do_albumlist('0', '0', $albums, $seq); ?>
			<?php if ( $wpdb->get_var( "SELECT COUNT(*) FROM `".WPPA_ALBUMS."` WHERE `a_parent` = '-1'" ) > 0 ) { ?>
				<tr>
					<td colspan="12" ><em><?php _e('The following albums are ---separate--- and do not show up in the generic album display', 'wppa'); ?></em></td>
				</tr>
				<?php wppa_do_albumlist('-1', '0', $albums, $seq); ?>
			<?php } ?>
			</tbody>
			<tfoot>
			<tr>
				<td>
					<img src="<?php echo wppa_get_imgdir().'backarrow.gif' ?>" style="height:16px;" />
					<img src="<?php echo wppa_get_imgdir().'arrow.gif' ?>" style="height:16px;" />
				</td>
				<?php $url = get_admin_url().'admin.php?page=wppa_admin_menu&amp;order_by='; ?>
				<td  colspan="6" >
					<a href="<?php echo wppa_dbg_url($url.'id') ?>">
						<?php _e('ID', 'wppa');
							if ($order == 'id') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>					
					</a>
				</td>
				
				<td  style="width: 120px;">
					<a href="<?php echo wppa_dbg_url($url.'name') ?>">
						<?php _e('Name', 'wppa'); 
							if ($order == 'name') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<td >
					<a href="<?php echo wppa_dbg_url($url.'description') ?>">
						<?php _e('Description', 'wppa'); 
							if ($order == 'description') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<?php if (current_user_can('administrator')) { ?>
				<td  style="width: 100px;">
					<a href="<?php echo wppa_dbg_url($url.'owner') ?>">
						<?php _e('Owner', 'wppa'); 
							if ($order == 'owner') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<?php } ?>
                <td >
					<a href="<?php echo wppa_dbg_url($url.'a_order') ?>">
						<?php _e('Order', 'wppa'); 
							if ($order == 'a_order') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
                <td  style="width: 120px;">
					<a href="<?php echo wppa_dbg_url($url.'a_parent') ?>">
						<?php _e('Parent', 'wppa'); 
							if ($order == 'a_parent') {
								if ( $reverse ) echo $upimg;
								else echo $downimg;
							}
						?>
					</a>
				</td>
				<td  title="<?php _e('Albums/Photos/Moderation required/Scheduled', 'wppa') ?>" >
					<?php _e('A/P/PM/S', 'wppa'); ?>
				</td>
				<td ><?php _e('Edit', 'wppa'); ?></td>
				<td ><?php _e('Quick', 'wppa'); ?></td>
				<td ><?php _e('Bulk', 'wppa'); ?></td>
				<td ><?php _e('Seq', 'wppa'); ?></td>
				<td ><?php _e('Delete', 'wppa'); ?></td>	
				<?php if ( wppa_can_create_album() ) echo '<td >'.__('Create', 'wppa').'</td>'; ?>
			</tr>
			</tfoot>
		
		</table>
		
		<script type="text/javascript" >
			function checkArrows() {
				elms = jQuery('.alb-arrow-off');
				for(i=0;i<elms.length;i++) {
					elm = elms[i];
					if ( elm.parentNode.parentNode.style.display == 'none' ) elm.style.display = 'none';
				}
				elms = jQuery('.alb-arrow-on');
				for(i=0;i<elms.length;i++) {
					elm = elms[i];
					if ( elm.parentNode.parentNode.style.display == 'none' ) elm.style.display = '';
				}
			}
		</script>
<!--	</div> -->
<?php
	wppa_album_admin_footer();
?>
<?php	
	} else { 
?>
	<p><?php _e('No albums yet.', 'wppa'); ?></p>
<?php
	}
}

function wppa_album_admin_footer() {
global $wpdb;

	$albcount 		= $wpdb->get_var( "SELECT COUNT(*) FROM `".WPPA_ALBUMS."`" );
	$photocount 	= $wpdb->get_var( "SELECT COUNT(*) FROM `".WPPA_PHOTOS."`" );
	$pendingcount 	= $wpdb->get_var( "SELECT COUNT(*) FROM `".WPPA_PHOTOS."` WHERE `status` = 'pending'" );
	$schedulecount 	= $wpdb->get_var( "SELECT COUNT(*) FROM `".WPPA_PHOTOS."` WHERE `status` = 'scheduled'" );
	
	echo sprintf(__('There are <strong>%d</strong> albums and <strong>%d</strong> photos in the system.', 'wppa'), $albcount, $photocount);
	if ( $pendingcount ) echo ' '.sprintf(__('<strong>%d</strong> photos are pending moderation.', 'wppa'), $pendingcount);
	if ( $schedulecount ) echo ' '.sprintf(__('<strong>%d</strong> photos are scheduled for later publishing.', 'wppa'), $pendingcount);
	
	$lastalbum = $wpdb->get_row( "SELECT `id`, `name` FROM `".WPPA_ALBUMS."` ORDER BY `id` DESC LIMIT 1", ARRAY_A );
	if ( $lastalbum ) echo '<br />'.sprintf(__('The most recently added album is <strong>%s</strong> (%d).', 'wppa'), __(stripslashes($lastalbum['name'])), $lastalbum['id']);
	$lastphoto = $wpdb->get_row( "SELECT `id`, `name`, `album` FROM `".WPPA_PHOTOS."` ORDER BY `timestamp` DESC LIMIT 1", ARRAY_A );
	$lastphotoalbum = $wpdb->get_row($wpdb->prepare( "SELECT `id`, `name` FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $lastphoto['album']), ARRAY_A );
	if ( $lastphoto ) {
		echo '<br />'.sprintf(__('The most recently added photo is <strong>%s</strong> (%d)', 'wppa'), __(stripslashes($lastphoto['name'])), $lastphoto['id']);
		echo ' '.sprintf(__('in album <strong>%s</strong> (%d).', 'wppa'), __(stripslashes($lastphotoalbum['name'])), $lastphotoalbum['id']);
	}
}

function wppa_do_albumlist($parent, $nestinglevel, $albums, $seq) {
global $wpdb;

	$alt = true;

		foreach (array_keys($seq) as $s) {			// Obey the global sequence
			$album = $albums[$s];
			if ( $album['a_parent'] == $parent ) {
				if (wppa_have_access($album)) {
					$counts = wppa_treecount_a($album['id']);
					$pendcount = $counts['pendphotos'];
					$schedulecount = $counts['scheduledphotos'];
					$haschildren = wppa_have_accessable_children($album); 
					{
						$class = '';
						if ( $parent != '0' && $parent != '-1' ) {
							$class .= 'wppa-alb-on-'.$parent.' ';
							$par = $parent;
							while ( $par != '0' && $par != '-1' ) {
								$class .= 'wppa-alb-off-'.$par.' ';
								$par = wppa_get_parentalbumid($par);
							}
						}
						if ( $alt ) $class .= ' alternate';
						$style = '';
						if ( $pendcount ) $style .= 'background-color:#ffdddd; ';
					//	if ( $haschildren ) $style .= 'font-weight:bold; '; 
						if ( $parent != '0' && $parent != '-1' ) $style .= 'display:none; ';
						$onclickon = 'jQuery(\'.wppa-alb-on-'.$album['id'].'\').css(\'display\',\'\'); jQuery(\'#alb-arrow-on-'.$album['id'].'\').css(\'display\',\'none\'); jQuery(\'#alb-arrow-off-'.$album['id'].'\').css(\'display\',\'\');';
						$onclickoff = 'jQuery(\'.wppa-alb-off-'.$album['id'].'\').css(\'display\',\'none\'); jQuery(\'#alb-arrow-on-'.$album['id'].'\').css(\'display\',\'\'); jQuery(\'#alb-arrow-off-'.$album['id'].'\').css(\'display\',\'none\'); checkArrows();';
						$indent = $nestinglevel;
						if ( $indent > '5' ) $indent = 5;
						?>

						<tr class="<?php echo $class ?>" style="<?php echo $style ?>" >
							<?php
							$i = 0;
							while ( $i < $indent ) {
								echo '<td style="padding:2px;" ></td>';
								$i++;
							}
							?>
							<td style="padding:2px; text-align:center;" ><?php if ( $haschildren ) { ?>
								<img id="alb-arrow-off-<?php echo $album['id'] ?>" class="alb-arrow-off" style="height:16px; display:none;" src="<?php echo wppa_get_imgdir().'backarrow.gif' ?>" onclick="<?php echo $onclickoff ?>" title="<?php _e('Collapse subalbums', 'wppa') ?>" />
								<img id="alb-arrow-on-<?php echo $album['id'] ?>" class="alb-arrow-on" style="height:16px;" src="<?php echo wppa_get_imgdir().'arrow.gif' ?>" onclick="<?php echo $onclickon ?>" title="<?php _e('Expand subalbums', 'wppa') ?>" />
							<?php } ?></td>
							<td style="padding:2px;" ><?php echo($album['id']); ?></td>
							<?php 
							$i = $indent;
							while ( $i < 5 ) {
								echo '<td style="padding:2px;" ></td>';
								$i++;
							}
							?>
							<td><?php echo(esc_attr(wppa_qtrans(stripslashes($album['name'])))) ?></td>
							<td><small><?php echo(esc_attr(wppa_qtrans(stripslashes($album['description'])))) ?></small></td>
							<?php if (current_user_can('administrator')) { ?>
								<td><?php echo($album['owner']); ?></td>
							<?php } ?>
							<td><?php echo($album['a_order']) ?></td>
							<td><?php echo wppa_get_album_name($album['a_parent'], 'extended') ?></td>
							<?php $url = wppa_dbg_url(get_admin_url().'admin.php?page=wppa_admin_menu&amp;tab=edit&amp;edit_id='.$album['id']); ?>
							<?php $na = $counts['selfalbums']; ?>
							<?php $np = $counts['selfphotos']; ?>
							<?php $nm = $counts['pendphotos']; ?>
							<?php $ns = $counts['scheduledphotos']; ?>
							<td><?php echo $na.'/'.$np.'/'.$nm.'/'.$ns; ?></td>
							<?php if ( $album['owner'] != '--- public ---' || current_user_can('administrator') ) { ?>
								<?php $url = wppa_ea_url($album['id']) ?>
								<td><a href="<?php echo($url) ?>" class="wppaedit"><?php _e('Edit', 'wppa'); ?></a></td>
								<td><a href="<?php echo($url.'&amp;quick') ?>" class="wppaedit"><?php _e('Quick', 'wppa'); ?></a></td>
								<td><a href="<?php echo($url.'&amp;bulk#manage-photos') ?>" class="wppaedit"><?php _e('Bulk', 'wppa'); ?></a></td>
								<td><a href="<?php echo($url.'&amp;seq') ?>" class="wppaedit"><?php _e('Seq', 'wppa'); ?></a></td>
								
								<?php $url = wppa_ea_url($album['id'], 'del') ?>
								<td><a href="<?php echo($url) ?>" class="wppadelete"><?php _e('Delete', 'wppa'); ?></a></td>
								<?php if ( wppa_can_create_album() ) {
									$url = wppa_dbg_url(get_admin_url().'admin.php?page=wppa_admin_menu&amp;tab=edit&amp;edit_id=new&amp;parent_id='.$album['id']);
									$onc = 'if (confirm(\''.__('Are you sure you want to create a subalbum?', 'wppa').'\')) document.location=\''.$url.'\';';
									echo '<td><a onclick="'.$onc.'" class="wppacreate">'.__('Create', 'wppa').'</a></td>'; 
								}
							}
							else { ?>
							<td></td><td></td><?php if ( wppa_can_create_album() ) echo '<td></td' ?>
							<?php } ?>
						</tr>		
						<?php if ($alt == '') { $alt = ' class="alternate" '; } else { $alt = '';}
						if ( $haschildren ) wppa_do_albumlist($album['id'], $nestinglevel+'1', $albums, $seq);
					}
				}
			}
		}
	
}		

function wppa_have_accessable_children($alb) {
global $wpdb;

	$albums = $wpdb->get_results( "SELECT * FROM `" . WPPA_ALBUMS . "` WHERE `a_parent` = " . $alb['id'], ARRAY_A );

	if ( ! $albums || ! count($albums) ) return false;
	foreach ( $albums as $album ) {
		if ( wppa_have_access($album) ) return true;
	}
	return false;
}

// delete an album 
function wppa_del_album($id, $move = '') {
global $wpdb;

	if ( $move && !wppa_have_access($move) ) {
		wppa_error_message(__('Unable to move photos to album %s. Album not deleted.', 'wppa'));
		return false;
	}

	// Photos in the album
	$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . WPPA_PHOTOS . '` WHERE `album` = %s', $id), ARRAY_A);
	
	if (empty($move)) { // will delete all the album's photos
		if (is_array($photos)) {
			$cnt = '0';
			foreach ($photos as $photo) {
				$t = -microtime(true);
				
				wppa_delete_photo($photo['id']);
				
				$cnt++;
				$t += microtime(true);
//				wppa_dbg_msg('Del photo took :'.$t, 'red', 'force');
				// Time up?
				if ( wppa_is_time_up() ) {
					wppa_flush_treecounts($id);
					wppa_error_message('Time is out after '.$cnt.' photo deletes. Please redo this operation');
					return;
				}
			} 
		}

	} 
	else {	// Move
		if (is_array($photos)) {
			foreach ($photos as $photo) {
				$wpdb->query($wpdb->prepare('UPDATE `'.WPPA_PHOTOS.'` SET `album` = %s WHERE `id` = %s', $move, $photo['id']));
				wppa_move_source($photo['filename'], $photo['album'], $move);
				if ( wppa_is_time_up() ) {
					wppa_error_message('Time is out. Please redo this operation');
					return;
				}
			}
		}
		wppa_flush_treecounts($move);
	}

	// First flush treecounts, otherwise we do not know the parent if any
	wppa_flush_treecounts($id);
	
	// Now delete the album
	$wpdb->query($wpdb->prepare('DELETE FROM `' . WPPA_ALBUMS . '` WHERE `id` = %s LIMIT 1', $id));
	wppa_delete_album_source($id);
	wppa_index_remove('album', $id);
	
	wppa_update_message(__('Album Deleted.', 'wppa'));
}

// select main photo
function wppa_main_photo($cur = '', $covertype) {
global $wpdb;
global $wppa_opt;
	
    $a_id = $_REQUEST['edit_id'];
	$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `album` = %s '.wppa_get_photo_order($a_id), $a_id), ARRAY_A);
	
	$output = '';
	if ( ! empty($photos) ) {
		$output .= '<select name="wppa-main" onchange="wppaAjaxUpdateAlbum('.$a_id.', \'main_photo\', this)" >';
		$output .= '<option value="">'.__('--- please select ---', 'wppa').'</option>';
		if ( $covertype == 'imagefactory' || ( $covertype == '' && $wppa_opt['wppa_cover_type'] == 'imagefactory' ) ) {
			if ( $cur == '0' ) $selected = 'selected="selected"'; else $selected = '';
			$output .= '<option value="0" '.$selected.'>'.sprintf(__('auto select max %s random', 'wppa'), $wppa_opt['wppa_imgfact_count']).'</option>';
			if ( $cur == '-1' ) $selected = 'selected="selected"'; else $selected = '';
			$output .= '<option value="-1" '.$selected.'>'.sprintf(__('auto select max %s featured', 'wppa'), $wppa_opt['wppa_imgfact_count']).'</option>';
			if ( $cur == '-2' ) $selected = 'selected="selected"'; else $selected = '';
			$output .= '<option value="-2" '.$selected.'>'.sprintf(__('max %s most recent added', 'wppa'), $wppa_opt['wppa_imgfact_count']).'</option>';
		}
		else {
			if ( $cur == '0' ) $selected = 'selected="selected"'; else $selected = '';
			$output .= '<option value="0" '.$selected.'>'.__('--- random ---', 'wppa').'</option>';
			if ( $cur == '-1' ) $selected = 'selected="selected"'; else $selected = '';
			$output .= '<option value="-1" '.$selected.'>'.__('--- random featured ---', 'wppa').'</option>';
			if ( $cur == '-2' ) $selected = 'selected="selected"'; else $selected = '';
			$output .= '<option value="-2" '.$selected.'>'.__('--- most recent added ---', 'wppa').'</option>';
		}

		foreach($photos as $photo) {
			if ($cur == $photo['id']) { 
				$selected = 'selected="selected"'; 
			} 
			else { 
				$selected = ''; 
			}
			$name = __(stripslashes($photo['name']));
			if ( strlen($name) > 45 ) $name = substr($name, 0, 45).'...';
			if ( ! $name ) $name = __('Nameless, filename = ', 'wppa').$photo['filename'];
			$output .= '<option value="'.$photo['id'].'" '.$selected.'>'.$name.'</option>';
		}
		
		$output .= '</select>';
	} else {
		$output = '<p>'.__('No photos yet', 'wppa').'</p>';
	}
	return $output;
}

function wppa_ea_url($edit_id, $tab = 'edit') {

	$nonce = wp_create_nonce('wppa_nonce');
//	$referrer = $_SERVER["REQUEST_URI"];
	return wppa_dbg_url(get_admin_url().'admin.php?page=wppa_admin_menu&amp;tab='.$tab.'&amp;edit_id='.$edit_id.'&amp;wppa_nonce='.$nonce);
}

