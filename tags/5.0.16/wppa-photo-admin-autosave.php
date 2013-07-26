<?php
/* wppa-photo-admin-autosave.php
* Package: wp-photo-album-plus
*
* edit and delete photos
* version 5.0.15
*
*/

// Edit photo for owners of the photo(s) only
function _wppa_edit_photo() {
global $thumb;

	// Check input
	wppa_vfy_arg('photo');
	
	// Edit Photo
	if ( isset($_GET['photo']) ) {
		$photo = $_GET['photo'];
		wppa_cache_thumb($photo);
		if ( $thumb['owner'] == wppa_get_user() ) { ?>
			<div class="wrap">
				<h2><?php _e('Edit photo', 'wppa') ?></h2>
				<?php wppa_album_photos('', $photo) ?>
			</div>				
<?php	}
		else {
			wp_die('You do not have the rights to do this');
		}
	}
	else {	// Edit all photos owned by current user
		?>
			<div class="wrap">
				<h2><?php _e('Edit photos', 'wppa') ?></h2>
				<?php wppa_album_photos('', '', wppa_get_user()) ?>
			</div>				
		<?php
	}
}

// Moderate photos
function _wppa_moderate_photos() {

	// Check input
	wppa_vfy_arg('photo');

	if ( isset($_GET['photo']) ) {
		$photo = $_GET['photo'];
	}
	else $photo = '';
	?>
		<div class="wrap">
			<h2><?php _e('Moderate photos', 'wppa') ?></h2>
			<?php wppa_album_photos('', $photo, '', true) ?>
		</div>				
	<?php
}

// The photo edit list. Also used in wppa-album-admin-autosave.php
function wppa_album_photos($album = '', $photo = '', $owner = '', $moderate = false) {
	global $wpdb;
	global $q_config;
	global $wppa_opt;
	
	// Check input
	wppa_vfy_arg('wppa-page');

	$pagesize 	= $wppa_opt['wppa_photo_admin_pagesize'];
	$page 		= isset ( $_GET['wppa-page'] ) ? $_GET['wppa-page'] : '1';
	$skip 		= ( $page - '1') * $pagesize;
	$limit 		= ( $pagesize < '1' ) ? '' : ' LIMIT '.$skip.','.$pagesize;
	
	if ( $album ) {
		$counts = wppa_treecount_a($album);
		$count = $counts['selfphotos'] + $counts['pendphotos']; //$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `'.WPPA_PHOTOS.'` WHERE `album` = %s', $album));
		$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `album` = %s '.wppa_get_photo_order($album, 'norandom').$limit, $album), ARRAY_A);
		$link = wppa_dbg_url(get_admin_url().'admin.php?page=wppa_admin_menu&tab=edit&edit_id='.$album);
	}
	elseif ( $photo && ! $moderate) {
		$count = '1';
		$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `id` = %s', $photo), ARRAY_A);
		$link = '';
	}
	elseif ( $owner ) {
		$count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `'.WPPA_PHOTOS.'` WHERE `owner` = %s', $owner));
		$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `owner` = %s ORDER BY `timestamp` DESC'.$limit, $owner), ARRAY_A);
		$link = wppa_dbg_url(get_admin_url().'admin.php?page=wppa_edit_photo');
	}
	elseif ( $moderate ) {
		if ( ! current_user_can('wppa_moderate') ) wp_die(__('You do not have the rights to do this', 'wppa'));
		if ( $photo ) {
			$count = '1';
			$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `id` = %s', $photo), ARRAY_A);
			$link = '';
		}
		else {
			// Photos with pending comments?
			$cmt = $wpdb->get_results("SELECT `photo` FROM `".WPPA_COMMENTS."` WHERE `status` = 'pending'", ARRAY_A);

			if ( $cmt ) {
				$orphotois = '';
				foreach ( $cmt as $c ) {
					$orphotois .= "OR `id` = ".$c['photo']." ";
				}
			}
			else $orphotois = '';
			$count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `'.WPPA_PHOTOS.'` WHERE `status` = %s '.$orphotois, 'pending'));
			$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `status` = %s '.$orphotois.' ORDER BY `timestamp` DESC'.$limit, 'pending'), ARRAY_A);
			$link = wppa_dbg_url(get_admin_url().'admin.php?page=wppa_page_moderate');
		}
		if ( empty($photos) ) {
			if ( $photo ) echo '<p>'.__('This photo is no longer awaiting moderation.', 'wppa').'</p>';
			else echo '<p>'.__('There are no photos awaiting moderation at this time.', 'wppa').'</p>';
			return;
		}
	}
	else wppa_dbg_msg('Missing required argument in wppa_album_photos()', 'red', 'force');
	
	if ( $link && isset($_REQUEST['quick']) ) $link .= '&quick';
	
	if (empty($photos)) { 
		echo '<p>'.__('No photos yet in this album.', 'wppa').'</p>';
	} 
	else { 
		$wms = array( 'toplft' => __('top - left', 'wppa'), 'topcen' => __('top - center', 'wppa'), 'toprht' => __('top - right', 'wppa'), 
					  'cenlft' => __('center - left', 'wppa'), 'cencen' => __('center - center', 'wppa'), 'cenrht' => __('center - right', 'wppa'), 
					  'botlft' => __('bottom - left', 'wppa'), 'botcen' => __('bottom - center', 'wppa'), 'botrht' => __('bottom - right', 'wppa'), );
		$temp = wppa_get_water_file_and_pos();
		$wmfile = $temp['file'];
		$wmpos = $wms[$temp['pos']];
		
		wppa_admin_page_links($page, $pagesize, $count, $link);
		
		foreach ($photos as $photo) { ?>
			<a id="photo_<?php echo $photo['id'] ?>" name="photo_<?php echo $photo['id'] ?>"></a>
			<div class="photoitem" id="photoitem-<?php echo $photo['id'] ?>" style="width:100%;" >
			
				<!-- Left half starts here -->
				<div style="width:49.5%; float:left; border-right:1px solid #ccc; margin-right:0;">
					<input type="hidden" id="photo-nonce-<?php echo $photo['id'] ?>" value="<?php echo wp_create_nonce('wppa_nonce_'.$photo['id']);  ?>" />
					<table class="form-table phototable"  >
						<tbody>	
							<tr style="vertical-align:top;" >
								<th scope="row">
									<label ><?php echo 'ID = '.$photo['id'].' '.__('Preview:', 'wppa'); ?></label>
									<br />&nbsp;<br />
									<a style="cursor:pointer; font-weight:bold;" onclick="if (confirm('<?php _e('Are you sure you want to rotate this photo?', 'wppa') ?>')) wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'rotleft', 0); " ><?php _e('Rotate left', 'wppa'); ?></a>
									<br />
									<a style="cursor:pointer; font-weight:bold;" onclick="if (confirm('<?php _e('Are you sure you want to rotate this photo?', 'wppa') ?>')) wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'rotright', 0); " ><?php _e('Rotate right', 'wppa'); ?></a>
									<br />
									
									<span style="font-size: 9px; line-height: 10px; color:#666;">
										<?php $refresh = '<a onclick="wppaReload()" >'.__('Refresh', 'wppa').'</a>'; ?>
										<?php echo sprintf(__('If it says \'Photo rotated\', the photo is rotated. %s the page.', 'wppa'), $refresh); ?>
									</span>
								</th>
								<td style="text-align:center;">
									<?php $src = wppa_get_thumb_url($photo['id']); ?>
									<?php $big = wppa_get_photo_url($photo['id']); ?>
									<a href="<?php echo $big ?>" target="_blank" title="<?php _e('Preview fullsize photo', 'wppa') ?>" >
										<img src="<?php echo($src) ?>" alt="<?php echo($photo['name']) ?>" style="max-width: 160px;" />
									</a>
								</td>	
							</tr>
							
							<!-- Upload -->
							<tr style="vertical-align:bottom;" >
								<th scope="row" style="padding-top:0; padding-bottom:0;">
									<label><?php _e('Upload:', 'wppa'); ?></label>
								</th>
								<td style="padding-top:0; padding-bottom:0;">
									<?php $timestamp = $photo['timestamp']; ?>
									<?php if ($timestamp) echo wppa_local_date(get_option('date_format', "F j, Y,").' '.get_option('time_format', "g:i a"), $timestamp).' '.__('local time', 'wppa').' '; if ($photo['owner']) echo( __('By:', 'wppa').$photo['owner']) ?>
								</td>
							</tr>
							
							<!-- Modified -->
							<tr style="vertical-align:bottom;" >
								<th scope="row" style="padding-top:0; padding-bottom:0;">
									<label><?php _e('Modified:', 'wppa'); ?></label>
								</th>
								<td style="padding-top:0; padding-bottom:0;">
									<?php $modified = $photo['modified']; ?>
									<?php if ($modified) echo wppa_local_date(get_option('date_format', "F j, Y,").' '.get_option('time_format', "g:i a"), $modified).' '.__('local time', 'wppa') ?>
								</td>
							</tr>
							
							<!-- Rating -->
							<tr style="vertical-align:bottom;" >
								<th scope="row" style="padding-top:0; padding-bottom:0;">
									<label><?php _e('Rating:', 'wppa') ?></label>
								</th>
								<td class="wppa-rating" style="padding-top:0; padding-bottom:0;">
									<?php 
									$entries = wppa_get_rating_count_by_id($photo['id']);
									if ( $entries ) {
										echo __('Entries:', 'wppa') . ' ' . $entries . '. ' . __('Mean value:', 'wppa') . ' ' . wppa_get_rating_by_id($photo['id'], 'nolabel') . '.'; 
									}
									else {
										_e('No ratings for this photo.', 'wppa');
									}
									$dislikes = wppa_dislike_get($photo['id']);
									if ( $dislikes ) {
										echo ' <span style="color:red" >'.sprintf(__('Disliked by %d visitors', 'wppa'), $dislikes).'</span>';
									}
									?>
									
								</td>
							</tr>
							
							<!-- P_order -->
							<tr style="vertical-align:bottom;" >
								<th scope="row" style="padding-top:0; padding-bottom:0;">
									<label><?php _e('Photo sort order #:', 'wppa'); ?></label>
								</th>
								<td style="padding-top:0; padding-bottom:0;">
									<input type="text" id="porder-<?php echo $photo['id'] ?>" value="<?php echo($photo['p_order']) ?>" style="width: 50px" onchange="wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'p_order', this)" />
								</td>
							</tr>
							
							<?php if ( ! isset($_REQUEST['quick']) ) { ?>
								<?php if ( ! isset($album_select[$photo['album']]) ) $album_select[$photo['album']] = wppa_album_select_a(array('checkaccess' => true, 'path' => wppa_switch('wppa_hier_albsel'), 'exclude' => $photo['album'], 'selected' => '0', 'addpleaseselect' => true)) ?>
								<!-- Move -->
								<tr style="vertical-align:bottom;" >
									<th scope="row" style="padding-top:0; padding-bottom:0;">
										<a style="" onclick="if(document.getElementById('moveto-<?php echo($photo['id']) ?>').value != 0) { if (confirm('<?php _e('Are you sure you want to move this photo?', 'wppa') ?>')) wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'moveto', document.getElementById('moveto-<?php echo($photo['id']) ?>') ) } else { alert('<?php _e('Please select an album to move the photo to first.', 'wppa') ?>'); return false;}" ><?php _e('Move photo to', 'wppa') ?></a> 
									</th>
									<td style="padding-top:0; padding-bottom:0;">							
										<select id="moveto-<?php echo $photo['id'] ?>" style="width:100%;" ><?php echo $album_select[$photo['album']] ?></select>
									</td>
								</tr>
								<!-- Copy -->
								<tr style="vertical-align:bottom;" >
									<th scope="row" style="padding-top:0; padding-bottom:0;">
										<a style="cursor:pointer; font-weight:bold;" onclick="if (document.getElementById('copyto-<?php echo($photo['id']) ?>').value != 0) { if (confirm('<?php _e('Are you sure you want to copy this photo?', 'wppa') ?>')) wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'copyto', document.getElementById('copyto-<?php echo($photo['id']) ?>') ) } else { alert('<?php _e('Please select an album to copy the photo to first.', 'wppa') ?>'); return false;}" ><?php _e('Copy photo to', 'wppa') ?></a>
									</th>
									<td style="padding-top:0; padding-bottom:0;">
										<select id="copyto-<?php echo($photo['id']) ?>" style="width:100%;" ><?php echo $album_select[$photo['album']] ?></select>
									</td>
								</tr>
							<?php } ?>
							<!-- Delete -->
							<tr style="vertical-align:bottom;" >
								<th scope="row" style="padding-top:0; padding-bottom:0; line-height:20px;">
									<a style="cursor:pointer; font-weight:bold; color:red;" onclick="if (confirm('<?php _e('Are you sure you want to delete this photo?', 'wppa') ?>')) wppaAjaxDeletePhoto(<?php echo $photo['id'] ?>)" ><?php _e('Delete photo', 'wppa'); ?></a>
								</th>
							</tr>
							<!-- Insert code -->
							<?php if ( current_user_can('edit_posts') || current_user_can('edit_pages') ) { ?>
							<tr style="vertical-align:bottom;" >
								<th scope="row" style="padding: 4px 10px; line-height:20px;">
									<a style="cursor:pointer; font-weight:bold;" onclick="prompt('<?php _e('Insert code for single image in Page or Post:\nYou may change the size if you like.', 'wppa') ?>', '%%wppa%% %%photo=<?php echo($photo['id']); ?>%% %%size=<?php echo $wppa_opt['wppa_fullsize'] ?>%%')" ><?php _e('Insertion Code', 'wppa'); ?></a>
								</th>
							</tr>
							<?php } ?>
							<?php if ( $wppa_opt['wppa_link_is_restricted'] == 'no' || current_user_can('administrator') ) { ?>
							<!-- Link url -->
							<tr style="vertical-align:top;" >
								<th scope="row" style="padding-top:0; padding-bottom:0;">
									<label><?php _e('Link url:', 'wppa') ?></label>
								</th>
								<td style="padding-top:0; padding-bottom:0;">
									<input type="text" style="width:70%;" onchange="wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'linkurl', this)" value="<?php echo(stripslashes($photo['linkurl'])) ?>" />
									<select style="float:right;" onchange="wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'linktarget', this)" >
										<option value="_self" <?php if ( $photo['linktarget'] == '_self' ) echo 'selected="selected"' ?>><?php _e('Same tab', 'wppa') ?></option>
										<option value="_blank" <?php if ( $photo['linktarget'] == '_blank' ) echo 'selected="selected"' ?>><?php _e('New tab', 'wppa') ?></option>
									</select>
								</td>
							</tr>
							<!-- Link title -->
							<tr style="vertical-align:top;" >
								<th scope="row" style="padding-top:0; padding-bottom:0;">
									<label><?php _e('Link title:', 'wppa') ?></label>
								</th>
								<td style="padding-top:0; padding-bottom:0;">
									<input type="text" style="width:100%;" onchange="wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'linktitle', this)" value="<?php echo(stripslashes($photo['linktitle'])) ?>" />
								</td>
							</tr>
							<?php if ( current_user_can('wppa_settings') ) { ?>
							<tr style="padding-left:10px; font-size:9px; line-height:10px; color:#666;" >
								<td colspan="2" style="padding-top:0" >
									<?php _e('If you want this link to be used, check \'PS Overrule\' checkbox in table VI.', 'wppa') ?>
								</td>
							</tr>
							<?php } ?>
							<?php } ?>
							<!-- Alt custom field -->
							<?php
							if ( $wppa_opt['wppa_alt_type'] == 'custom' ) { ?>
							<tr style="vertical-align:top;" >
								<th scope="row" style="padding-top:0; padding-bottom:0;">
									<label><?php _e('HTML Alt attribute:', 'wppa') ?></label>
								</th>
								<td style="padding-top:0; padding-bottom:0;">
									<input type="text" style="width:100%;" onchange="wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'alt', this)" value="<?php echo(stripslashes($photo['alt'])) ?>" />
								</td>
							</tr>
							<?php } ?>

						</tbody>
					</table>	
				</div>
				
				<!-- Right half starts here -->
				<div style="width:50%; float:left; border-left:1px solid #ccc; margin-left:-1px;">
					<table class="form-table phototable" >
						<tbody>
						
							<!-- Filename -->
							<?php if ( $photo['filename'] ) { ?>
							<tr style="vertical-align:top;" >
								<th scope="row" >
									<label><?php _e('Filename:', 'wppa'); ?></label>
								</th>
								<td>
									<?php echo $photo['filename'] ?>
									<?php if ( current_user_can('administrator') && is_file($wppa_opt['wppa_source_dir'].'/album-'.$photo['album'].'/'.$photo['filename']) ) {
										echo ' '.__('Source file available.', 'wppa'); ?>
										<a style="cursor:pointer; font-weight:bold;" onclick="wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'remake', this)">Remake files</a>
									<?php } ?>
								</td>
							</tr>
							<?php } ?>
							
							<!-- Location -->
							<?php if ( $photo['location'] || wppa_switch('wppa_geo_edit') ) { ?>
							<tr style="vertical-align:top;" >
								<th scope="row" >
									<label><?php _e('Location:', 'wppa'); ?></label>
								</th>
								<td>
									<?php
									$loc = $photo['location'] ? $photo['location'] : '///';
									$geo = explode('/', $loc);
									echo $geo['0'].' '.$geo['1'].' ';
									if ( wppa_switch('wppa_geo_edit') ) { ?>
									<?php _e('Lat:', 'wppa') ?><input type="text" style="width:100px;" id="lat-<?php echo $photo['id'] ?>" onchange="wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'lat', this);" value="<?php echo $geo['2'] ?>" />
									<?php _e('Lon:', 'wppa') ?><input type="text" style="width:100px;" id="lon-<?php echo $photo['id'] ?>" onchange="wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'lon', this);" value="<?php echo $geo['3'] ?>" />
									<span class="description"><br /><?php _e('Refresh the page after changing to see the degrees being updated', 'wppa') ?></span>
									<?php } ?>
								</td>
							</tr>
							<?php } ?>
							
							<!-- Name -->			
							<tr style="vertical-align:top;" >
								<th scope="row" >
									<label><?php _e('Photoname:', 'wppa'); ?></label>
								</th>
								<td>
									<input type="text" style="width:100%;" id="pname-<?php echo $photo['id'] ?>" onchange="wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'name', this);" value="<?php echo esc_attr(stripslashes($photo['name'])) ?>" />
								<!--	<span class="description"><br/><?php _e('Type/alter the name of the photo. <small>It is NOT a filename and needs no file extension like .jpg.</small>', 'wppa'); ?></span> -->
								</td>
							</tr>
							
							<!-- Description -->
							<tr style="vertical-align:top;" >
								<th scope="row" >
									<label><?php _e('Description:', 'wppa'); ?></label>
								</th>
								<?php if ( get_option('wppa_use_wp_editor') == 'yes' ) { ?>
								<td>
								
									<?php 
									$alfaid = wppa_alfa_id($photo['id']);
									$quicktags_settings = array( 'buttons' => 'strong,em,link,block,ins,ul,ol,li,code,close' );
									wp_editor(stripslashes($photo['description']), 'wppaphotodesc'.$alfaid, array('wpautop' => false, 'media_buttons' => false, 'textarea_rows' => '6', 'tinymce' => false, 'quicktags' => $quicktags_settings ));
									?>
									
									<input type="button" class="button-secundary" value="<?php _e('Update Photo description', 'wppa') ?>" onclick="wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'description', document.getElementById('wppaphotodesc'+'<?php echo $alfaid ?>') )" />
									<img id="wppa-photo-spin-<?php echo $photo['id'] ?>" src="<?php echo wppa_get_imgdir().'wpspin.gif' ?>" style="visibility:hidden" />
								</td>
								<?php }
								else { ?>
								<td>
									<textarea style="width: 100%; height:160px;" onchange="wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'description', this)" ><?php echo(stripslashes($photo['description'])) ?></textarea>
								</td>
								<?php } ?>
							</tr>
							<!-- Tags -->
							<tr style="vertical-align:center;" >
								<th scope="row" >
									<label ><?php _e('Tags:', 'wppa') ?></label>
									<span class="description" >
										<br />&nbsp;
									</span>
								</th>
								<td style="padding-top:0; padding-bottom:0;">
									<input id="tags-<?php echo $photo['id'] ?>" type="text" style="width:100%;" onchange="wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'tags', this)" value="<?php echo(stripslashes($photo['tags'])) ?>" />
									<span class="description" >
										<?php _e('Separate tags with commas.', 'wppa') ?>&nbsp;
										<?php _e('Examples:', 'wppa') ?>
										<select onchange="wppaAddTag(this.value, 'tags-<?php echo $photo['id'] ?>'); wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'tags', document.getElementById('tags-<?php echo $photo['id'] ?>'))" >
											<?php $taglist = wppa_get_taglist();
											if ( is_array($taglist) ) {
												echo '<option value="" >'.__('- select -', 'wppa').'</option>';
												foreach ( $taglist as $tag ) {
													echo '<option value="'.$tag['tag'].'" >'.$tag['tag'].'</option>';
												}
											}
											else {
												echo '<option value="0" >'.__('No tags yet', 'wppa').'</option>';
											}
											?>
										</select>
										<?php _e('Select to add', 'wppa') ?>
									</span>
								</td>
							</tr>

							<!-- Status -->
							<tr style="vertical-align:bottom;" >
								<th scope="row" >
									<label ><?php _e('Status:', 'wppa') ?></label>
								</th>
								<td>
								<?php if ( current_user_can('wppa_admin') || current_user_can('wppa_moderate') ) { ?>
									<select id="status-<?php echo $photo['id'] ?>" onchange="wppaAjaxUpdatePhoto(<?php echo $photo['id'] ?>, 'status', this); wppaPhotoStatusChange(<?php echo $photo['id'] ?>); ">
										<option value="pending" <?php if ($photo['status']=='pending') echo 'selected="selected"'?> ><?php _e('Pending', 'wppa') ?></option>
										<option value="publish" <?php if ($photo['status']=='publish') echo 'selected="selected"'?> ><?php _e('Publish', 'wppa') ?></option>
										<option value="featured" <?php if ($photo['status']=='featured') echo 'selected="selected"'?> ><?php _e('Featured', 'wppa') ?></option>
									</select>
								<?php }
									else { 
										if ( $photo['status'] == 'pending' ) _e('Pending', 'wppa');
										elseif ( $photo['status'] == 'publish' ) _e('Publish', 'wppa');
										elseif ( $photo['status'] == 'featured' ) e('Featured', 'wppa');
									} ?>
									<span id="psdesc-<?php echo $photo['id'] ?>" class="description" style="display:none;" ><?php _e('Note: Featured photos should have a descriptive name; a name a search engine will look for!', 'wppa'); ?></span>

								</td>
							</tr>
							<!-- Watermark -->
							<tr style="vertical-align:bottom;" >
								<th scope="row" >
									<label ><?php _e('Watermark:', 'wppa') ?></label>
								</th>
								<td>
									<?php 
									if ( get_option('wppa_watermark_on') == 'yes' ) { 
										if ( get_option('wppa_watermark_user') == 'yes' ) {	
											echo __('File:','wppa').' ' ?>
											<select id="wmfsel_<?php echo $photo['id']?>">
											<?php echo wppa_watermark_file_select() ?>
											</select>
											<?php
											echo __('Pos:', 'wppa').' ' ?>
											<select id="wmpsel_<?php echo $photo['id']?>">
											<?php echo wppa_watermark_pos_select() ?>
											</select> 
											<input type="button" class="button-secundary" value="<?php _e('Apply watermark', 'wppa') ?>" onclick="if (confirm('<?php _e('Are you sure?\n\nOnce applied it can not be removed!\nAnd I do not know if there is already a watermark on this photo', 'wppa') ?>')) wppaAjaxApplyWatermark(<?php echo $photo['id'] ?>, document.getElementById('wmfsel_<?php echo $photo['id']?>').value, document.getElementById('wmpsel_<?php echo $photo['id']?>').value)" />
											<?php
										}
										else {
											echo __('File:','wppa').' '.__($wmfile, 'wppa'); 
											if ( $wmfile != '--- none ---' ) echo ' '.__('Pos:', 'wppa').' '.$wmpos; 
										} ?>
										<img id="wppa-water-spin-<?php echo $photo['id'] ?>" src="<?php echo wppa_get_imgdir().'wpspin.gif' ?>" style="visibility:hidden" /><?php
									}
									else { 
										_e('Not configured', 'wppa');
									} 
									?>
								</td>
							</tr>
							<!-- Remark -->
							<tr style="vertical-align:bottom;" >
								<th scope="row">
									<label ><?php _e('Remark:', 'wppa') ?></label>
								</th>
								<td id="photostatus-<?php echo $photo['id'] ?>" style="width:99%; padding-left:10px;">
									<?php echo sprintf(__('Photo %s is not modified yet', 'wppa'), $photo['id']) ?>
								</td>
							</tr>

						</tbody>
					</table>
					<script type="text/javascript">wppaPhotoStatusChange(<?php echo $photo['id'] ?>)</script>
				</div>
			
				<div class="clear"></div>
				
				<!-- Comments -->
				<?php 
				$comments = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_COMMENTS."` WHERE `photo` = %s ORDER BY `timestamp` DESC", $photo['id']), ARRAY_A);
				if ( $comments ) {
				?>
				<hr />
				<div>
					<table>
						<thead>
							<tr style="font-weight:bold;" >
								<td style="padding:0 4px;" >#</td>
								<td style="padding:0 4px;" >User</td>
								<td style="padding:0 4px;" >Time since</td>
								<td style="padding:0 4px;" >Status</td>
								<td style="padding:0 4px;" >Comment</td>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $comments as $comment ) {
							echo '
							<tr>
								<td style="padding:0 4px;" >'.$comment['id'].'</td>
								<td style="padding:0 4px;" >'.$comment['user'].'</td>
								<td style="padding:0 4px;" >'.wppa_get_time_since($comment['timestamp']).'</td>';
								if ( current_user_can('wppa_comments') || current_user_can('wppa_moderate') ) {
									$p = ($comment['status'] == 'pending') ? 'selected="selected" ' : '';
									$a = ($comment['status'] == 'approved') ? 'selected="selected" ' : '';
									$s = ($comment['status'] == 'spam') ? 'selected="selected" ' : '';
									$t = ($comment['status'] == 'trash') ? 'selected="selected" ' : '';
									echo '
										<td style="padding:0 4px;" >
											<select onchange="wppaAjaxUpdateCommentStatus('.$photo['id'].', '.$comment['id'].', this.value)" >
												<option value="pending" '.$p.'>'.__('Pending', 'wppa').'</option>
												<option value="approved" '.$a.'>'.__('Approved', 'wppa').'</option>
												<option value="spam" '.$s.'>'.__('Spam', 'wppa').'</option>
												<option value="trash" '.$t.'>'.__('Trash', 'wppa').'</option>
											</select >
										</td>
									';
								}
								else {
									echo '<td style="padding:0 4px;" >';
										if ( $comment['status'] == 'pending' ) _e('Pending', 'wppa');
										elseif ( $comment['status'] == 'approved' ) _e('Approved', 'wppa');
										elseif ( $comment['status'] == 'spam' ) _e('Spam', 'wppa');
										elseif ( $comment['status'] == 'trash' ) _e('Trash', 'wppa');
									echo '</td>';
								}
								echo '<td style="padding:0 4px;" >'.$comment['comment'].'</td>
							</tr>
							';
							} ?>
						</tbody>
					</table>
				</div>
			<?php } ?>
			</div>
<?php
		} /* foreach photo */
		wppa_admin_page_links($page, $pagesize, $count, $link);
	} /* photos not empty */
} /* function */

