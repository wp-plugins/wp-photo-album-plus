<?php
/* wppa_settings.php
* Package: wp-photo-album-plus
*
* manage all optins
* Version 2.4.0
*/

function wppa_page_options() {
	global $wpdb;
	
	$options_error = false;
	
	// Check if a message is required
	wppa_check_update();

	if (isset($_POST['wppa-set-submit'])) {
		wppa_check_admin_referer( '$wppa_nonce', WPPA_NONCE );

		$old_minisize = wppa_get_minisize();
		
		if (wppa_check_numeric($_POST['wppa-thumbsize'], '50', __('Thumbnail size.', 'wppa'))) {
			if (get_option('wppa_thumbsize') != $_POST['wppa-thumbsize']) {
				update_option('wppa_thumbsize', $_POST['wppa-thumbsize']);
			}
		} else $options_error = true;
		
		if (wppa_check_numeric($_POST['wppa-tf-width'], get_option('wppa_thumbsize'), __('Thumbnail frame width', 'wppa'))) {
			update_option('wppa_tf_width', $_POST['wppa-tf-width']);
		} else $options_error = true; 
		
		if (wppa_check_numeric($_POST['wppa-tf-height'], get_option('wppa_thumbsize'), __('Thumbnail frame height', 'wppa'))) {
			update_option('wppa_tf_height', $_POST['wppa-tf-height']);
		} else $options_error = true; 
		
		if (wppa_check_numeric($_POST['wppa-tn-margin'], '0', __('Thumbnail Spacing', 'wppa'))) {
			update_option('wppa_tn_margin', $_POST['wppa-tn-margin']);
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
			wppa_ok_message(__('Regenerating thumbnail images, starting at id=', 'wppa').$start.'. Please wait... '.__('If the line of dots stops growing or you browser reports Ready but you did NOT get a \'READY regenerating thumbnail images\' message, your server has given up. In that case: continue this action by clicking', 'wppa').' <a href="'.get_option('siteurl').'/wp-admin/admin.php?page=options">'.__('here', 'wppa').'</a>'.' '.__('and click "Save Changes" again.', 'wppa'));
		
			wppa_regenerate_thumbs(); 
			wppa_update_message(__('READY regenerating thumbnail images.', 'wppa')); 				
			update_option('wppa_lastthumb', '-2');
		}
		
		if (isset($_POST['wppa-thumbtype'])) update_option('wppa_thumbtype', $_POST['wppa-thumbtype']);
		
		if (isset($_POST['wppa-thumb-text'])) update_option('wppa_thumb_text', 'yes');
		else update_option('wppa_thumb_text', 'no');
		
		update_option('wppa_valign', $_POST['wppa-valign']);
		update_option('wppa_fullvalign', $_POST['wppa-fullvalign']);
		update_option('wppa_fullhalign', $_POST['wppa-fullhalign']);
		
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
		
		if ($_POST['wppa-colwidth'] == 'auto') update_option('wppa_colwidth', 'auto');
		else {
			if (wppa_check_numeric($_POST['wppa-colwidth'], '100', __('Column width.'))) {
				update_option('wppa_colwidth', $_POST['wppa-colwidth']);
			} else {
				$options_error = true;
			}
		}
		
		if (isset($_POST['wppa-show-full-name'])) update_option('wppa_show_full_name', 'yes');
		else update_option('wppa_show_full_name', 'no');

		if (isset($_POST['wppa-show-full-desc'])) update_option('wppa_show_full_desc', 'yes');
		else update_option('wppa_show_full_desc', 'no');

		if (isset($_POST['wppa-show-startstop-navigation'])) update_option('wppa_show_startstop_navigation', 'yes');
		else update_option('wppa_show_startstop_navigation', 'no');
		
		if (isset($_POST['wppa-show-browse-navigation'])) update_option('wppa_show_browse_navigation', 'yes');
		else update_option('wppa_show_browse_navigation', 'no');
		
		if (isset($_POST['wppa-resize-on-upload'])) update_option('wppa_resize_on_upload', 'yes');
		else update_option('wppa_resize_on_upload', 'no');
		
		if (isset($_POST['wppa-hide-slideshow'])) update_option('wppa_hide_slideshow', 'no');
		else update_option('wppa_hide_slideshow', 'yes');
		
		if (isset($_POST['wppa-start-slide'])) update_option('wppa_start_slide', 'yes');
		else update_option('wppa_start_slide', 'no');
		
		if (isset($_POST['wppa-fadein-after-fadeout'])) update_option('wppa_fadein_after_fadeout', 'yes');
		else update_option('wppa_fadein_after_fadeout', 'no');
		
		if (wppa_check_numeric($_POST['wppa-album-page-size'], '0', __('Album page size.'))) {
			update_option('wppa_album_page_size', $_POST['wppa-album-page-size']);
		} else {
			$options_error = true;
		}
		
		if (wppa_check_numeric($_POST['wppa-thumb-page-size'], '0', __('Thumb page size.'))) {
			update_option('wppa_thumb_page_size', $_POST['wppa-thumb-page-size']);
		} else {
			$options_error = true;
		}

		if (isset($_POST['wppa-slideshow-timeout'])) update_option('wppa_slideshow_timeout', $_POST['wppa-slideshow-timeout']);
		
		if (isset($_POST['wppa-animation-speed'])) update_option('wppa_animation_speed', $_POST['wppa-animation-speed']);
		
		if (isset($_POST['wppa-filmstrip'])) update_option('wppa_filmstrip', 'yes');
		else update_option('wppa_filmstrip', 'no');
		
		if (isset($_POST['wppa-use-thumb-opacity'])) update_option('wppa_use_thumb_opacity', 'yes');
		else update_option('wppa_use_thumb_opacity', 'no');
		if (wppa_check_numeric($_POST['wppa-thumb-opacity'], '0', __('Opacity.'), '100')) {
			update_option('wppa_thumb_opacity', $_POST['wppa-thumb-opacity']);
		} else {
			$options_error = true;
		}
		
		if (isset($_POST['wppa-coverphoto-left'])) update_option('wppa_coverphoto_left', $_POST['wppa-coverphoto-left']);
		
		if (isset($_POST['wppa-thumbphoto-left'])) update_option('wppa_thumbphoto_left', $_POST['wppa-thumbphoto-left']);
		
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
	
		if (isset($_POST['wppa-set-access-by'])) update_option('wppa_set_access_by', $_POST['wppa-set-access-by']);	

		$need_update = false;
		if (isset($_POST['wppa-accesslevel'])) {
			if (get_option('wppa_accesslevel', '') != $_POST['wppa-accesslevel']) {
				if (get_option('wppa_set_access_by', 'me') == 'me') update_option('wppa_accesslevel', $_POST['wppa-accesslevel']);
				$need_update = true;
			}
		}
		if (isset($_POST['wppa-accesslevel-upload'])) {
			if (get_option('wppa_accesslevel_upload', '') != $_POST['wppa-accesslevel-upload']) {
				if (get_option('wppa_set_access_by', 'me') == 'me') update_option('wppa_accesslevel_upload', $_POST['wppa-accesslevel-upload']);
				$need_update = true;
			}
		}
		if (isset($_POST['wppa-accesslevel-sidebar'])) {
			if (get_option('wppa_accesslevel_sidebar', '') != $_POST['wppa-accesslevel-sidebar']) {
				if (get_option('wppa_set_access_by', 'me') == 'me') update_option('wppa_accesslevel_sidebar', $_POST['wppa-accesslevel-sidebar']);
				$need_update = true;
			}
		}
		if ($need_update) {
			if (get_option('wppa_set_access_by', 'me') == 'me') {
				wppa_set_caps();
			}
			else {
				wppa_error_message(__('Changes in accesslevels will not be made. It is set to be done by an other program.', 'wppa'));
			}
		}
				
		if (isset($_POST['wppa-html'])) update_option('wppa_html', 'yes');
		else update_option('wppa_html', 'no');
		
		if (isset($_POST['wppa-bgcolor-even'])) update_option('wppa_bgcolor_even', $_POST['wppa-bgcolor-even']);
		if (isset($_POST['wppa-bgcolor-alt'])) update_option('wppa_bgcolor_alt', $_POST['wppa-bgcolor-alt']);
		if (isset($_POST['wppa-bgcolor-nav'])) update_option('wppa_bgcolor_nav', $_POST['wppa-bgcolor-nav']);
		if (isset($_POST['wppa-bcolor-even'])) update_option('wppa_bcolor_even', $_POST['wppa-bcolor-even']);
		if (isset($_POST['wppa-bcolor-alt'])) update_option('wppa_bcolor_alt', $_POST['wppa-bcolor-alt']);
		if (isset($_POST['wppa-bcolor-nav'])) update_option('wppa_bcolor_nav', $_POST['wppa-bcolor-nav']);
		if (isset($_POST['wppa-bgcolor-img'])) update_option('wppa_bgcolor_img', $_POST['wppa-bgcolor-img']);
		if (isset($_POST['wppa-bwidth'])) update_option('wppa_bwidth', $_POST['wppa-bwidth']);
		if (isset($_POST['wppa-bradius'])) update_option('wppa_bradius', $_POST['wppa-bradius']);
		if (isset($_POST['wppa-fontfamily-title'])) update_option('wppa_fontfamily_title', $_POST['wppa-fontfamily-title']);
		if (isset($_POST['wppa-fontsize-title'])) update_option('wppa_fontsize_title', $_POST['wppa-fontsize-title']);
		if (isset($_POST['wppa-fontfamily-fulldesc'])) update_option('wppa_fontfamily_fulldesc', $_POST['wppa-fontfamily-fulldesc']);
		if (isset($_POST['wppa-fontsize-fulldesc'])) update_option('wppa_fontsize_fulldesc', $_POST['wppa-fontsize-fulldesc']);
		if (isset($_POST['wppa-fontfamily-fulltitle'])) update_option('wppa_fontfamily_fulltitle', $_POST['wppa-fontfamily-fulltitle']);
		if (isset($_POST['wppa-fontsize-fulltitle'])) update_option('wppa_fontsize_fulltitle', $_POST['wppa-fontsize-fulltitle']);
		if (isset($_POST['wppa-fontfamily-nav'])) update_option('wppa_fontfamily_nav', $_POST['wppa-fontfamily-nav']);
		if (isset($_POST['wppa-fontsize-nav'])) update_option('wppa_fontsize_nav', $_POST['wppa-fontsize-nav']);
		if (isset($_POST['wppa-fontfamily-box'])) update_option('wppa_fontfamily_box', $_POST['wppa-fontfamily-box']);
		if (isset($_POST['wppa-fontsize-box'])) update_option('wppa_fontsize_box', $_POST['wppa-fontsize-box']);
		if (isset($_POST['wppa-black'])) update_option('wppa_black', $_POST['wppa-black']);
		
		if (isset($_POST['wppa-search-linkpage'])) update_option('wppa_search_linkpage', $_POST['wppa-search-linkpage']);

		if (isset($_POST['wppa-excl-sep'])) update_option('wppa_excl_sep', 'yes');
		else update_option('wppa_excl_sep', 'no');
		
		if (isset($_POST['wppa-charset']) && get_option('wppa_charset', '') != 'UTF-8') {
			if (!options_error) {
				global $wpdb;
				if ($wpdb->query("ALTER TABLE " . ALBUM_TABLE . " MODIFY name text CHARACTER SET utf8") === false) $options_error = true;
				if ($wpdb->query("ALTER TABLE " . PHOTO_TABLE . " MODIFY name text CHARACTER SET utf8") === false) $options_error = true;
				if ($wpdb->query("ALTER TABLE " . ALBUM_TABLE . " MODIFY description text CHARACTER SET utf8") === false) $options_error = true;
				if ($wpdb->query("ALTER TABLE " . PHOTO_TABLE . " MODIFY description longtext CHARACTER SET utf8") === false) $options_error = true;
				if ($options_error) wppa_error_message(__('Error converting to UTF-8', 'wppa'));
				else update_option('wppa_charset', 'UTF-8');
			}
		}
		if ($options_error) wppa_update_message(__('Other changes saved', 'wppa'));
		else wppa_update_message(__('Changes Saved', 'wppa')); 
	}
    elseif (get_option('wppa_lastthumb', '-2') != '-2') wppa_error_message(__('Regeneration of thumbnail images interrupted. Please press "Save Changes"', 'wppa')); 
?>		
	<div class="wrap">
		<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/settings32.png'; ?>
		<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
			<br />
		</div>
		<h2><?php _e('WP Photo Album Plus Settings', 'wppa'); ?></h2>
		<p><?php _e('Database revision:', 'wppa'); ?> <?php echo(get_option('wppa_revision', '100')) ?>. <?php _e('WP Charset:', 'wppa'); ?> <?php echo(get_bloginfo('charset')); ?>. <?php _e('WPPA Charset:', 'wppa'); ?> <?php echo(get_option('wppa_charset', __('default', 'wppa'))); ?>.</p><br/>
		<form action="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=options" method="post">
	
			<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE); ?>

			<p>
				<input type="submit" class="button-primary" name="wppa-set-submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
			</p>
			<br />

			<div class="table_wrapper">
			<table class="form-table albumtable">
				<tbody>
					<tr><th><h3><?php _e('General settings:', 'wppa'); ?></h3></th></tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Column Width:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-colwidth" id="wppa-colwidth" onchange="wppaCheckFullHalign()" value="<?php echo(get_option('wppa_colwidth', get_option('wppa_fullsize'))) ?>" style="width: 50px;" /><?php _e('pixels.', 'wppa'); ?>
							<span class="description"><br/><?php _e('Enter the width of the main column in your theme\'s display area.', 'wppa');
								echo(' '); _e('You should set this value correctly to make sure the fullsize images are properly aligned horizontally.', 'wppa'); 
								echo('<br/>'); _e('You may enter <b>auto</b> for use in themes that have a floating content column.', 'wppa'); ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Full Size:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-fullsize" id="wppa-fullsize" onchange="wppaCheckFullHalign()" value="<?php echo(get_option('wppa_fullsize')) ?>" style="width: 50px;" /><?php _e('pixels wide or high, whichever is the largest.', 'wppa'); ?>
							<span class="description"><br/><?php _e('Enter the largest size in pixels as how you want your photos to be displayed.', 'wppa');
								echo(' '); _e('This is usually the same as the Column Width, but it may differ.', 'wppa');  ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Resize on Upload:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-resize-on-upload" id="wppa-resize-on-upload" <?php if (get_option('wppa_resize_on_upload', 'no') == 'yes') echo('checked="checked"') ?> />
							<span class="description"><br/><?php _e('If you check this item, the size of the photos will be reduced to the Full Size during the upload/import process.', 'wppa');
								echo(' '); _e('The photos will never be enlarged if they are smaller than the Full Size.', 'wppa'); ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Stretch to fit:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-enlarge" id="wppa-enlarge" <?php if (get_option('wppa_enlarge', 'yes') == 'yes') echo ('checked="checked"') ?> />
							<span class="description"><br/><?php _e('Fullsize images will be stretched to the Full Size at display time if they are smaller. Leaving unchecked is recommended. It is better to upload photos that fit well the sizes you use!', 'wppa'); ?></span>
						</td>
					</tr>
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
					<tr><th><hr/></th><td><hr/></td></tr>
					<tr><th><h3><?php _e('Full-size & Slideshow:', 'wppa'); ?></h3></th></tr>				
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Vertical alignment:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $valign = get_option('wppa_fullvalign', 'default'); ?>
							<select name="wppa-fullvalign" id="wppa-fullvalign" onchange="wppaCheckFullHalign()">
								<option value="default" <?php if ($valign == 'default') echo(' selected '); ?>><?php _e('--- default ---', 'wppa'); ?></option>
								<option value="top" <?php if ($valign == 'top') echo(' selected '); ?>><?php _e('top', 'wppa'); ?></option>
								<option value="center" <?php if ($valign == 'center') echo(' selected '); ?>><?php _e('center', 'wppa'); ?></option>
								<option value="bottom" <?php if ($valign == 'bottom') echo(' selected '); ?>><?php _e('bottom', 'wppa'); ?></option>
								<option value="fit" <?php if ($valign == 'fit') echo(' selected '); ?>><?php _e('fit', 'wppa'); ?></option>	
							</select>
							<span class="description"><br/><?php _e('Specify the vertical alignment of fullsize images.', 'wppa'); 
								echo(' '); _e('If you select --- default ---, the photos will not be centered horizontally either.', 'wppa'); ?>
							</span>
						</td>
					</tr>
					<tr valign="top" class="wppa-ha">
						<th scope="row">
							<label><?php _e('Horizontal alignment:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $halign = get_option('wppa_fullhalign', 'center'); ?>
							<select name="wppa-fullhalign">
								<option value="default" <?php if ($halign == 'default') echo(' selected '); ?>><?php _e('--- default ---', 'wppa'); ?></option>
								<option value="left" <?php if ($halign == 'left') echo(' selected '); ?>><?php _e('left', 'wppa'); ?></option>
								<option value="center" <?php if ($halign == 'center') echo(' selected '); ?>><?php _e('center', 'wppa'); ?></option>
								<option value="right" <?php if ($halign == 'right') echo(' selected '); ?>><?php _e('right', 'wppa'); ?></option>
							</select>
							<span class="description"><br/><?php _e('Specify the horizontal alignment of fullsize images. If you specify --- default --- , no horizontal alignment will take place.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Name and description:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Name:', 'wppa'); ?><input type="checkbox" name="wppa-show-full-name" id="wppa-show-full-name" <?php if (get_option('wppa_show_full_name', 'yes') == 'yes') echo('checked="checked"'); ?> />
							<?php echo(', '); _e('Description:', 'wppa'); ?><input type="checkbox" name="wppa-show-full-desc" id="wppa-show-full-desc" <?php if (get_option('wppa_show_full_desc', 'yes') == 'yes') echo('checked="checked"'); ?> />
							<span class="description"><br/><?php _e('If checked: display name and description under the full-size images and slideshow.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Navigation bars:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Start/stop slideshow bar:', 'wppa'); ?><input type="checkbox" name="wppa-show-startstop-navigation" id="wppa-show-startstop-navigation" <?php if (get_option('wppa_show_startstop_navigation', 'yes') == 'yes') echo('checked="checked"'); ?> />
							<?php echo(', '); _e('Browse photos bar:', 'wppa'); ?><input type="checkbox" name="wppa-show-browse-navigation"id="wppa-show-browse-navigation" <?php if (get_option('wppa_show_browse_navigation', 'yes') == 'yes') echo('checked="checked"'); ?> />
							<?php echo(', '); _e('Filmstrip navigator:', 'wppa'); ?><input type="checkbox" name="wppa-filmstrip" id="wppa-filmstrip" <?php if (get_option('wppa_filmstrip', 'no') == 'yes') echo('checked="checked"'); ?> />
							<span class="description"><br/><?php _e('If checked: display navigation bars and/or a filmstrip over and under the full-size images and slideshow.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Enable slideshow:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-hide-slideshow" id="wppa-hide-slideshow" onchange="wppaCheckHs()" <?php if (get_option('wppa_hide_slideshow', 'no') == 'no') echo('checked="checked"'); ?> />
							<span class="description"><br/><?php _e('If you do not want slideshows: uncheck this box. Browsing full size images will remain possible.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top" class="wppa-ss">
						<th scope="row">
							<label><?php _e('Start slideshow running:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-start-slide" id="wppa-start-slide" <?php if (get_option('wppa_start_slide', 'no') == 'yes') echo('checked="checked"'); ?> />
							<span class="description"><br/><?php _e('If checked, the slideshow will start running immediately, if unchecked the first photo will be displayed in browse mode.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top" class="wppa-ss">
						<th scope="row">
							<label><?php _e('Fade-in after fade-out:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-fadein-after-fadeout" id="wppa-fadein-after-fadeout" <?php if (get_option('wppa_fadein_after_fadeout', 'no') == 'yes') echo('checked="checked"'); ?> />
							<span class="description"><br/><?php _e('If checked: slides are faded out and in after each other. If unchecked: fadin and fadeout overlap.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top" class="wppa-ss">
						<th scope="row">
							<label><?php _e('Slideshow timeout:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $timeout = get_option('wppa_slideshow_timeout', 2500); ?>
							<select name="wppa-slideshow-timeout">
								<option value="1000" <?php if ($timeout == '1000') echo('selected '); ?>><?php _e('very short (1 s.)', 'wppa'); ?></option>
								<option value="1500" <?php if ($timeout == '1500') echo('selected '); ?>><?php _e('short (1.5 s.)', 'wppa'); ?></option>
								<option value="2500" <?php if ($timeout == '2500') echo('selected '); ?>><?php _e('normal (2.5 s.)', 'wppa'); ?></option>
								<option value="4000" <?php if ($timeout == '4000') echo('selected '); ?>><?php _e('long (4 s.)', 'wppa'); ?></option>
								<option value="6000" <?php if ($timeout == '6000') echo('selected '); ?>><?php _e('very long (6 s.)', 'wppa'); ?></option>
							</select>
							<span class="description"><br/><?php _e('Select the time a single slide will be visible when the slideshow is started.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top" class="wppa-ss">
						<th scope="row">
							<label><?php _e('Slideshow animation speed:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $anim = get_option('wppa_animation_speed', 400); ?>
							<select name="wppa-animation-speed">
								<option value="0" <?php if ($anim == '0') echo('selected '); ?>><?php _e('--- off ---', 'wppa'); ?></option>
								<option value="200" <?php if ($anim == '200') echo('selected '); ?>><?php _e('very fast (200 ms.)', 'wppa'); ?></option>
								<option value="400" <?php if ($anim == '400') echo('selected '); ?>><?php _e('fast (400 ms.)', 'wppa'); ?></option>
								<option value="800" <?php if ($anim == '800') echo('selected '); ?>><?php _e('normal (800 ms.)', 'wppa'); ?></option>
								<option value="1200" <?php if ($anim == '1200') echo('selected '); ?>><?php _e('slow (1.2 s.)', 'wppa'); ?></option>
								<option value="2000" <?php if ($anim == '2000') echo('selected '); ?>><?php _e('very slow (2 s.)', 'wppa'); ?></option>
								<option value="4000" <?php if ($anim == '4000') echo('selected '); ?>><?php _e('extremely slow (4 s.)', 'wppa'); ?></option>
							</select>
							<span class="description"><br/><?php _e('Specify the animation speed to be used in slideshows.', 'wppa'); ?></span>
						</td>
					</tr>	
					<script type="text/javascript">wppaCheckHs();</script>
					<tr><th><hr/></th><td><hr/></td></tr>
					<tr><th><h3><?php _e('Thumbnails:', 'wppa'); ?></h3></th></tr>
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
							<label><?php _e('Display type:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $thumbtype = get_option('wppa_thumbtype', 'default'); ?>
							<select name="wppa-thumbtype" id="wppa-thumbtype" onchange="wppaCheckThumbType()" >
								<option value="default" <?php if ($thumbtype == 'default') echo(' selected '); ?>><?php _e('--- default ---', 'wppa'); ?></option>
								<option value="ascovers" <?php if ($thumbtype == 'ascovers') echo(' selected '); ?>><?php _e('like album covers', 'wppa'); ?></option>
							</select>
							<span class="description"><br/><?php _e('You may select an altenative display method for thumbnails. Note that some of the thumbnail settings do not apply to all available display methods.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top" class="tt-ascovers">
						<th scope="row">
							<label><?php _e('Placement:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $left = (get_option('wppa_thumbphoto_left', 'no') == 'yes'); ?>
							<input type="radio" name="wppa-thumbphoto-left" value="yes" <?php if ($left) echo('checked="checked"') ?>/><?php _e('Left', 'wppa') ?><br/>
							<input type="radio" name="wppa-thumbphoto-left" value="no" <?php if(!$left) echo('checked="checked"') ?>/><?php _e('Right', 'wppa') ?>
							<span class="description"><br/><?php _e('Indicate the placement position of the thumbnailphoto you wish.', 'wppa') ?></span>
						</td>
					</tr>
					<tr valign="top" class="tt-normal">
						<th scope="row">
							<label ><?php _e('Thumbnail Size:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-thumbsize" id="wppa-thumbsize" value="<?php echo(get_option('wppa_thumbsize', '130')) ?>" style="width: 50px;" />
							<?php _e('pixels.', 'wppa'); ?>
							<span class="description"><br/><?php _e('Changing the thumbnail size may result in all thumbnails being regenerated. this may take a while.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top" class="tt-normal">
						<th scope="row">
							<label ><?php _e('Thumbnail Framesize:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Width:', 'wppa'); ?><input type="text" name="wppa-tf-width" id="wppa-tf-width" value="<?php echo(get_option('wppa_tf_width')); ?>" style="width: 50px;" />
							<?php _e('pixels, Height:', 'wppa'); ?><input type="text" name="wppa-tf-height" id="wppa-tf-height" value="<?php echo(get_option('wppa_tf_height')); ?>" style="width: 50px;" />
							<?php _e('pixels, Spacing:', 'wppa'); ?><input type="text" name="wppa-tn-margin" id="wppa-tn-margin" value="<?php echo(get_option('wppa_tn_margin')); ?>" style="width: 25px;" />
							<?php _e('pixels.', 'wppa'); ?>
							<span class="description"><br/><?php 
										   _e('Set width, height and spacing for the thumbnail frames.', 'wppa');
								echo(' '); _e('These sizes should be large enough for a thumbnail image and - optionally - the text under it.', 'wppa'); ?>
							</span>
						</td>
					</tr>
					<tr valign="top" class="tt-normal">
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
					<tr valign="top" class="tt-normal">
						<th scope="row">
							<label><?php _e('Apply mouseover effect:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-use-thumb-opacity" id="wppa-use-thumb-opacity" onchange="wppaCheckUseThumbOpacity()"<?php if (get_option('wppa_use_thumb_opacity', 'no') == 'yes') echo('checked="checked"') ?> />
							<span class="thumb-opacity"><?php _e('Opacity value:', 'wppa'); ?></span><input class="thumb-opacity" type="text" name="wppa-thumb-opacity" id="wppa-thumb-opacity" value="<?php echo(get_option('wppa_thumb_opacity', '80')) ?>" style="width: 50px;" /><span class="thumb-opacity">%.</span>
							<span class="description"><br/><?php _e('Check this box to use mouseover effect on thumbnail images.', 'wppa') ?></span>
							<span class="description thumb-opacity"><br/><?php _e('Enter percentage of opacity. 100% is opaque, 0% is transparant', 'wppa') ?></span>
						</td>
					</tr>
					<tr valign="top" class="tt-normal">
						<th scope="row">
							<label><?php _e('Apply popup effect:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-use-thumb-popup" id="wppa-use-thumb-popup" <?php if (get_option('wppa_use_thumb_popup', 'no') == 'yes') echo('checked="checked"') ?> />
							<span class="description"><br/><?php _e('Use popup effect on thumbnail images.', 'wppa') ?></span>
						</td>
					</tr>
					<tr valign="top" class="tt-normal">
						<th scope="row">
							<label><?php _e('Thumbnail text:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-thumb-text" id="wppa-thumb-text" <?php if (get_option('wppa_thumb_text', 'no') == 'yes') echo('checked="checked"') ?> />
							<span class="description"><br/><?php _e('Display name and description under thumbnails.', 'wppa') ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Max thumbnails per page:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-thumb-page-size" id="wppa-thumb-page-size" value="<?php echo(get_option('wppa_thumb_page_size', '0')) ?>" style="width: 50px;" />
							<span class="description"><br/><?php _e('Enter the maximum number of thumbnail images per page. A value of 0 indicates no pagination.', 'wppa') ?></span>
						</td>
					</tr>
					<tr><th><hr/></th><td><hr/></td></tr>
					<tr><th><h3><?php _e('Album covers:', 'wppa'); ?></h3></th></tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Coverphoto Size:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-smallsize" id="wppa-smallsize" value="<?php echo(get_option('wppa_smallsize', '130')) ?>" style="width: 50px;" />
							<span class="description"><br/><?php _e('Changing the coverphoto size may result in all thumbnails being regenerated. this may take a while.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Placement:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $left = (get_option('wppa_coverphoto_left', 'no') == 'yes'); ?>
							<input type="radio" name="wppa-coverphoto-left" value="yes" <?php if ($left) echo('checked="checked"') ?>/><?php _e('Left', 'wppa') ?><br/>
							<input type="radio" name="wppa-coverphoto-left" value="no" <?php if(!$left) echo('checked="checked"') ?>/><?php _e('Right', 'wppa') ?>
							<span class="description"><br/><?php _e('Indicate the placement position of the coverphoto you wish.', 'wppa') ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Apply mouseover effect:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-use-cover-opacity" id="wppa-use-cover-opacity" onchange="wppaCheckUseCoverOpacity()" <?php if (get_option('wppa_use_cover_opacity', 'no') == 'yes') echo('checked="checked"') ?> />
							<span class="cover-opacity"><?php _e('Opacity value:', 'wppa'); ?></span>
							<input class="cover-opacity" type="text" name="wppa-cover-opacity" id="wppa-cover-opacity" value="<?php echo(get_option('wppa_cover_opacity', '80')) ?>" style="width: 50px;" /><span class="cover-opacity">%.</span>
							<span class="description"><br/><?php _e('Check this box to use mouseover effect on cover images.', 'wppa') ?></span>
							<span class="description cover-opacity"><br/><?php _e('Enter percentage of opacity. 100% is opaque, 0% is transparant', 'wppa') ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Max covers per page:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-album-page-size" id="wppa-album-page-size" value="<?php echo(get_option('wppa_album_page_size', '0')) ?>" style="width: 50px;" />
							<span class="description"><br/><?php _e('Enter the maximum number of album covers per page. A value of 0 indicates no pagination.', 'wppa') ?></span>
						</td>
					</tr>
					<tr><th><hr/></th><td><hr/></td></tr>
					<tr><th><h3><?php _e('Order settings:', 'wppa'); ?></h3></th></tr>
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
					<tr><th><h3><?php _e('Appearance:', 'wppa'); ?></h3></th></tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Explanation:', 'wppa'); ?></label>
						</th>
						<td>
							<span class="description">
								<?php _e('The settings in this chapter may be left blank. When blank, the theme\'s defaults are used or the settings in wppa_style.css.', 'wppa'); ?>
								<br/><?php _e('It is strongly recommended that you try these settings to achieve your desired appearance before editing the css file.', 'wppa'); ?>
								<br/><?php _e('Note: these settings - if not blank - have precednce over css settings or any hard coded attributes.', 'wppa'); ?>
								<br/><?php _e('The css classes that these settings act upon are indicated in the descriptions as follows (for example \'wppa-class\'):', 'wppa'); ?> <b>(.wppa-class)</b>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Even background:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Background Color:', 'wppa') ?> <input type="text" name="wppa-bgcolor-even" id="wppa-bgcolor-even" value="<?php echo(get_option('wppa_bgcolor_even', '#e6f2d9')) ?>" style="width: 100px;" />
							<?php _e('Border Color:', 'wppa') ?> <input type="text" name="wppa-bcolor-even" id="wppa-bcolor-even" value="<?php echo(get_option('wppa_bcolor_even', '#e6f2d9')) ?>" style="width: 100px;" />
							<span class="description"><br/><?php _e('Enter valid CSS colors for even numbered backgrounds and borders of album covers and thumbnail displays \'As covers\'.', 'wppa'); ?> <b>(.wppa-even)</b></span>						
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Odd background:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Background Color:', 'wppa') ?> <input type="text" name="wppa-bgcolor-alt" id="wppa-bgcolor-alt" value="<?php echo(get_option('wppa_bgcolor_alt', '#d5eabf')) ?>" style="width: 100px;" />
							<?php _e('Border Color:', 'wppa') ?> <input type="text" name="wppa-bcolor-alt" id="wppa-bcolor-alt" value="<?php echo(get_option('wppa_bcolor_alt', '#e6f2d9')) ?>" style="width: 100px;" />
							<span class="description"><br/><?php _e('Enter valid CSS colors for odd numbered backgrounds and borders of album covers and thumbnail displays \'As covers\'.', 'wppa'); ?> <b>(.wppa-alt)</b></span>						
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Navigation bars:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Background Color:', 'wppa') ?> <input type="text" name="wppa-bgcolor-nav" id="wppa-bgcolor-nav" value="<?php echo(get_option('wppa_bgcolor_nav', '#d5eabf')) ?>" style="width: 100px;" />
							<?php _e('Border Color:', 'wppa') ?> <input type="text" name="wppa-bcolor-nav" id="wppa-bcolor-nav" value="<?php echo(get_option('wppa_bcolor_nav', '#d5eabf')) ?>" style="width: 100px;" />
							<span class="description"><br/><?php _e('Enter valid CSS colors for navigation backgrounds and borders.', 'wppa'); ?> <b>(.wppa-nav)</b></span>						
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Borders:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Border thickness:', 'wppa') ?> <input type="text" name="wppa-bwidth" id="wppa-bwidth" value="<?php echo(get_option('wppa_bwidth', '1')) ?>" style="width: 50px;" />px.&nbsp;
							<?php _e('Border radius:', 'wppa') ?> <input type="text" name="wppa-bradius" id="wppa-bradius" value="<?php echo(get_option('wppa_bradius', '6')) ?>" style="width: 50px;" />px.
							<span class="description"><br/><?php _e('Enter thicknes and corner radius for the backgrounds above. A number of 0 means: no.', 'wppa'); ?> <b>(.wppa-box, .wppa-mini-box)</b>
							<br/><?php _e('Note that rounded corners are only supported by modern browsers.', 'wppa'); ?></span>						
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Popup and Cover Photos:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Background Color:', 'wppa') ?> <input type="text" name="wppa-bgcolor-img" id="wppa-bgcolor-img" value="<?php echo(get_option('wppa_bgcolor_img', '#eef7e6')) ?>" style="width: 100px;" />
							<span class="description"><br/><?php _e('Enter a valid CSS color for image backgrounds.', 'wppa'); ?> <b>(.wppa-img)</b></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Font for album titles:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Font family:', 'wppa') ?> <input type="text" name="wppa-fontfamily-title" id="wppa-fontfamily-title" value="<?php echo(get_option('wppa_fontfamily_title', '')) ?>" style="width: 200px;" />&nbsp;
							<?php _e('Size:', 'wppa') ?> <input type="text" name="wppa-fontsize-title" id="wppa-fontsize-title" value="<?php echo(get_option('wppa_fontsize_title', '')) ?>" style="width: 50px;" />px.
							<span class="description"><br/><?php _e('Enter font name and size for album cover titles.', 'wppa'); ?> <b>(.wppa-title)</b></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Font for fullsize photo descriptions:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Font family:', 'wppa') ?> <input type="text" name="wppa-fontfamily-fulldesc" id="wppa-fontfamily-fulldesc" value="<?php echo(get_option('wppa_fontfamily_fulldesc', '')) ?>" style="width: 200px;" />&nbsp;
							<?php _e('Size:', 'wppa') ?> <input type="text" name="wppa-fontsize-fulldesc" id="wppa-fontsize-fulldesc" value="<?php echo(get_option('wppa_fontsize_fulldesc', '')) ?>" style="width: 50px;" />px.
							<span class="description"><br/><?php _e('Enter font name and size for album cover titles.', 'wppa'); ?> <b>(.wppa-fulldesc)</b></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Font for fullsize photo names:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Font family:', 'wppa') ?> <input type="text" name="wppa-fontfamily-fulltitle" id="wppa-fontfamily-fulltitle" value="<?php echo(get_option('wppa_fontfamily_fulltitle', '')) ?>" style="width: 200px;" />&nbsp;
							<?php _e('Size:', 'wppa') ?> <input type="text" name="wppa-fontsize-fulltitle" id="wppa-fontsize-fulltitle" value="<?php echo(get_option('wppa_fontsize_fulltitle', '')) ?>" style="width: 50px;" />px.
							<span class="description"><br/><?php _e('Enter font name and size for album cover titles.', 'wppa'); ?> <b>(.wppa-fulltitle)</b></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Font for navigations:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Font family:', 'wppa') ?> <input type="text" name="wppa-fontfamily-nav" id="wppa-fontfamily-nav" value="<?php echo(get_option('wppa_fontfamily_nav', '')) ?>" style="width: 200px;" />&nbsp;
							<?php _e('Size:', 'wppa') ?> <input type="text" name="wppa-fontsize-nav" id="wppa-fontsize-nav" value="<?php echo(get_option('wppa_fontsize_nav', '')) ?>" style="width: 50px;" />px.
							<span class="description"><br/><?php _e('Enter font name and size for navigation items.', 'wppa'); ?> <b>(.wppa-nav-text)</b></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('General font in wppa boxes:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Font family:', 'wppa') ?> <input type="text" name="wppa-fontfamily-box" id="wppa-fontfamily-box" value="<?php echo(get_option('wppa_fontfamily_box', '')) ?>" style="width: 200px;" />&nbsp;
							<?php _e('Size:', 'wppa') ?> <input type="text" name="wppa-fontsize-box" id="wppa-fontsize-box" value="<?php echo(get_option('wppa_fontsize_box', '')) ?>" style="width: 50px;" />px.
							<span class="description"><br/><?php _e('Enter font name and size for all other items.', 'wppa'); ?> <b>(.wppa-box-text)</b></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label><?php _e('Default text color:', 'wppa'); ?></label>
						</th>
						<td>
							<?php _e('Color:', 'wppa') ?> <input type="text" name="wppa-black" id="wppa-black" value="<?php echo(get_option('wppa_black', 'black')) ?>" style="width: 100px;" />
							<span class="description"><br/><?php _e('Enter your sites default text color.', 'wppa'); ?> <b>(.wppa-black)</b></span>
						</td>
					</tr>
					<tr><th><hr/></th><td><hr/></td></tr>
					<tr><th><h3><?php _e('Miscelanious:', 'wppa'); ?></h3></th></tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Search page:', 'wppa'); ?></label>
						</th>
						<td>
<?php
							$query = "SELECT ID, post_title FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish' ORDER BY post_title ASC";
							$pages = $wpdb->get_results ($query, 'ARRAY_A');
							if (empty($pages)) {
								_e('There are no pages (yet) to link to.', 'wppa');
							} else {
								$linkpage = get_option('wppa_search_linkpage', '0');
								$sel = 'selected="selected"';
?>
							<select name="wppa-search-linkpage" id="wppa-wsp" >
<?php
								foreach ($pages as $page) { ?>
								<option value="<?php echo($page['ID']); ?>" <?php if ($linkpage == $page['ID']) echo($sel); ?>><?php echo($page['post_title']); ?></option>
<?php 							} 
?>
							</select>
							<span class="description"><br/><?php _e('Select the page to be used to display search results. The page MUST contain %%wppa%%.', 'wppa');
							echo(' '); _e('You may give it the title "Search results" or something alike.', 'wppa');
							echo(' '); _e('Or you ou may use the standard page on which you display the generic album.', 'wppa'); ?></span>
<?php						}
?>					
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Exclude separate:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-excl-sep" id="wppa-excl-sep" <?php if (get_option('wppa_excl_sep', 'no') == 'yes') echo (' checked="checked"') ?> />
							<span class="description"><br/><?php _e('When checked, albums (and photos in them) that have the parent set to --- separate --- will be excluded from being searched.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Allow HTML in album and photo descriptions:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-html" id="wppa-html" <?php if (get_option('wppa_html', 'yes') == 'yes') echo (' checked="checked"') ?> />
							<span class="description"><br/><?php _e('If checked: html is allowed. WARNING: No checks on syntax, it is your own responsability to close tags properly!', 'wppa'); ?></span>
						</td>
					</tr>

<?php if (get_bloginfo('charset') == 'UTF-8') { ?>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Set Character set to UTF-8:', 'wppa'); ?></label>
						</th>
						<td>
							<input <?php if (get_option('wppa_charset') == 'UTF-8') echo('disabled="disabled"'); ?> type="checkbox" name="wppa-charset" id="wppa-charset" <?php if (get_option('wppa_charset', '') == 'UTF-8') echo(' checked="checked"') ?> />
							<span class="description"><br/><?php _e('If checked: Converts the wppa database tables to UTF-8 This allows the use of certain characters - like Turkish - in photo and album names and descriptions.', 'wppa'); ?></span>
						</td>
					</tr>
<?php } ?>						

<?php if (current_user_can('administrator')) { ?>
					<tr><th><hr/></th><td><hr/></td></tr>
					<tr><th><h3><?php _e('Access settings:', 'wppa'); ?></h3></th></tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Explanation:', 'wppa'); ?></label>
						</th>
						<td>
							<span class="description">
								<?php _e('Here you can set the capabilities that are specific for <strong>WPPA+.</strong>', 'wppa'); ?>
								<br/><?php _e('If you set them here, the classical userlevel is imitated, but implemented by the modern Roles and Capabilities system.', 'wppa'); ?>
								<br/><?php _e('That means that any higher userlevel (role) will automaticly get the capabilities that you give to a certain (lower) level.', 'wppa'); ?>
								<br/><?php _e('If you want to give a capability to a specific user or role, you can set it using an other plugin, such as <strong>Capability Manager</strong>.', 'wppa'); ?>
								<br/><?php _e('Possible capabilities are: <strong>wppa_admin</strong> (for the Photo Albums page), <strong>wppa_sidebar_admin</strong> (for the Sidebar Widget page) and <strong>wppa_upload</strong> (for the Upload and Import pages).', 'wppa'); ?>
								<br/><?php _e('The Help page is available to users with the capability <strong>edit_posts</strong>, the <strong>Settings</strong> page (the page you are on right now) is limited to the role of administrator.', 'wppa'); ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Access settings:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="radio" name="wppa-set-access-by" id="wppa-set-access-by" value="me" <?php if (get_option('wppa_set_access_by', 'me') == 'me') echo (' checked="checked"') ?> />
							<?php echo(' '); _e('Accesslevels are set here.', 'wppa'); ?><br/>
							<input type="radio" name="wppa-set-access-by" id="wppa-set-access-by" value="other" <?php if (get_option('wppa_set_access_by', 'me') == 'other') echo (' checked="checked"') ?> />
							<?php echo(' '); _e('Accesslevels are set by an other program.', 'wppa'); ?>
							<span class="description"><br/><?php _e('Indicate whether the accesslevels must be set by this admin page or by an other program.', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Albums Access Level:', 'wppa'); ?></label>
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
							<span class="description"><br/><?php _e('The minmum user level that can access the photo album admin (i.e. Manage Albums and Upload Photos).', 'wppa'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Upload Access Level:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $level = get_option('wppa_accesslevel_upload'); ?>
							<?php $sel = 'selected="selected"'; ?>
							<select name="wppa-accesslevel-upload">
								<option value="administrator" <?php if ($level == 'administrator') echo($sel); ?>><?php _e('Administrator', 'wppa'); ?></option> 
								<option value="editor" <?php if ($level == 'editor') echo($sel); ?>><?php _e('Editor', 'wppa'); ?></option>
								<option value="author" <?php if ($level == 'author') echo($sel); ?>><?php _e('Author', 'wppa'); ?></option>
								<option value="contributor" <?php if ($level == 'contributor') echo($sel); ?>><?php _e('Contributor', 'wppa'); ?></option>				
							</select>
							<span class="description"><br/><?php _e('The minmum user level that can upload photos.', 'wppa'); ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label ><?php _e('Sidebar Access Level:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $level = get_option('wppa_accesslevel_sidebar'); ?>
							<?php $sel = 'selected="selected"'; ?>
							<select name="wppa-accesslevel-sidebar">
								<option value="administrator" <?php if ($level == 'administrator') echo($sel); ?>><?php _e('Administrator', 'wppa'); ?></option> 
								<option value="editor" <?php if ($level == 'editor') echo($sel); ?>><?php _e('Editor', 'wppa'); ?></option>
								<option value="author" <?php if ($level == 'author') echo($sel); ?>><?php _e('Author', 'wppa'); ?></option>
								<option value="contributor" <?php if ($level == 'contributor') echo($sel); ?>><?php _e('Contributor', 'wppa'); ?></option>				
							</select>
							<span class="description"><br/><?php _e('The minmum user level that can access the photo album sidebar widget admin.', 'wppa'); ?>
							<br/><br/><?php _e('NOTE: Accessing this page - WP Photo Album Plus Settings - is always priviledged to Administrators.', 'wppa'); ?></span>
						</td>
					</tr>
<?php } ?>					
				
				</tbody>
			</table>
			</div>
			<br />
			<p>
				<input type="submit" class="button-primary" name="wppa-set-submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
			</p>
		</form>
		<script type="text/javascript">wppaInitSettings();</script>
	</div>
<?php 
}
	
?>