<?php
/* wppa-settings.php
* Package: wp-photo-album-plus
*
* manage all options
* Version 4.0.12
*
*/

function _wppa_page_options() {
global $wpdb;
global $wppa;
global $wppa_opt;
global $blog_id; 
global $wppa_status;
global $options_error;

	// Initialize
	wppa_set_defaults();
	$options_error = false;
	
	if ( isset($_GET['move_up']) ) {
		wppa_check_admin_referer( '$wppa_nonce', WPPA_NONCE );
		$sequence = get_option('wppa_slide_order');
		$indices = explode(',', $sequence);
		$temp = $indices[$_GET['move_up']];
		$indices[$_GET['move_up']] = $indices[$_GET['move_up'] - '1'];
		$indices[$_GET['move_up'] - '1'] = $temp;
		update_option('wppa_slide_order', implode(',', $indices));
	}
	
	if ( isset($_POST['wppa_set_submit']) ) {
		wppa_check_admin_referer( '$wppa_nonce', WPPA_NONCE );
		
		// Remember the existing physical thumbnail image size
		$old_minisize = wppa_get_minisize();
		
		// See if an action is requested, if so, do it and skip the setting changes if any
		// Table 8: Actions	
		if (isset($_POST['wppa_action'])) { // Action to be done
			$slug = $_POST['wppa_action'];
			switch ($slug) {
				case 'wppa_rating_clear':		
					$iret1 = $wpdb->query('DELETE FROM '.WPPA_RATING.' WHERE id > -1');
					$iret2 = $wpdb->query('UPDATE '.WPPA_PHOTOS.' SET mean_rating="0" WHERE id > -1');
					if ($iret1 && $iret2) wppa_update_message(__('Ratings cleared', 'wppa'));
					else {
						wppa_error_message(__('Could not clear ratings', 'wppa'));
						$options_error = true;
					}
					break;
					
				case 'wppa_charset':
					global $wpdb;
					if ($wpdb->query("ALTER TABLE " . WPPA_ALBUMS . " MODIFY name text CHARACTER SET utf8") === false) $options_error = true;
					if ($wpdb->query("ALTER TABLE " . WPPA_PHOTOS . " MODIFY name text CHARACTER SET utf8") === false) $options_error = true;
					if ($wpdb->query("ALTER TABLE " . WPPA_ALBUMS . " MODIFY description text CHARACTER SET utf8") === false) $options_error = true;
					if ($wpdb->query("ALTER TABLE " . WPPA_PHOTOS . " MODIFY description longtext CHARACTER SET utf8") === false) $options_error = true;
					if ($wpdb->query("ALTER TABLE " . WPPA_PHOTOS . " MODIFY linktitle text CHARACTER SET utf8") === false) $options_error = true;
					if ($wpdb->query("ALTER TABLE " . WPPA_COMMENTS . " MODIFY comment text CHARACTER SET utf8") === false) $options_error = true;
					if ($options_error) wppa_error_message(__('Error converting to UTF_8', 'wppa'));
					else {
						update_option('wppa_charset', 'UTF_8');
						wppa_update_message(__('Converted to utf8', 'wppa'));
					}
					break;
					
				case 'wppa_setup':
					wppa_setup(true);
					break;
					
				case 'wppa_backup':
					if (!wppa_backup_settings()) $options_error = true;
					//else wppa_ok_message(__('Settings backuped', 'wppa'));
					break;
					
				case 'wppa_load_skin':
					$fname = $_POST['wppa_skinfile'];
					if ($fname == 'restore') {
						if (wppa_restore_settings(WPPA_DEPOT_PATH.'/settings.bak', 'backup')) {
							wppa_ok_message(__('Saved settings restored', 'wppa'));
						}
						else {
							wppa_error_message(__('Unable to restore saved settings', 'wppa'));
							$options_error = true;
						}
					}
					elseif ($fname == 'default') {
						if (wppa_set_defaults(true)) {						
							wppa_ok_message(__('Reset to default settings', 'wppa'));
						}
						else {
							wppa_error_message(__('Unable to set defaults', 'wppa'));
							$options_error = true;
						}
					}
					elseif (wppa_restore_settings($fname, 'skin')) {
						wppa_ok_message(sprintf(__('Skinfile %s loaded', 'wppa'), basename($fname)));
					}
					else {
						$options_error = true;
					}
					wppa_initialize_runtime(true);
					break;

				case 'wppa_regen':
					$old_minisize--; // fake thumbnail size change
					break;
					
				case 'wppa_rerate':
					if (!wppa_recalculate_ratings()) $options_error = true;
					break;
					
			}
			if ($options_error) {
				wppa_error_message(__('Requested action failed, possible setting updates ignored', 'wppa'));
			}
			else {
				if ($slug != 'wppa_regen') wppa_ok_message(__('Requested action performed, possible setting updates ignored', 'wppa'));
			}
		}
		
		else { // Update setting(s)

			// Table 1: Sizes
			$slug = 'wppa_colwidth';
			$value = $_POST[$slug];
			if ($value == 'auto') wppa_update_value($slug);
			else wppa_update_numeric($slug, '100', __('Column width.', 'wppa'));

			wppa_update_numeric('wppa_fullsize', '100', __('Full size.', 'wppa'));
			wppa_update_numeric('wppa_maxheight', '100', __('Max height.', 'wppa'));
			wppa_update_check('wppa_resize_on_upload');
			wppa_update_check('wppa_enlarge');
			wppa_update_numeric('wppa_thumbsize', '50', __('Thumbnail size.', 'wppa'));
			wppa_update_numeric('wppa_tf_width', '50', __('Thumbnail frame width', 'wppa'));
			wppa_update_numeric('wppa_tf_height', '50', __('Thumbnail frame height', 'wppa'));
			wppa_update_numeric('wppa_tn_margin', '0', __('Thumbnail Spacing', 'wppa'));
			wppa_update_check('wppa_thumb_auto');
			wppa_update_numeric('wppa_min_thumbs', '0', __('Photocount treshold.', 'wppa'));
			wppa_update_numeric('wppa_thumb_page_size', '0', __('Thumb page size.', 'wppa'));
			wppa_update_numeric('wppa_smallsize', '50', __('Cover photo size.', 'wppa'));
			wppa_update_numeric('wppa_album_page_size', '0', __('Album page size.', 'wppa'));
			wppa_update_numeric('wppa_topten_count', '2', __('Number of TopTen photos', 'wppa'), '40');
			wppa_update_numeric('wppa_topten_size', '32', __('Widget image thumbnail size', 'wppa'), wppa_get_minisize());
			wppa_update_numeric('wppa_max_cover_width', '150', __('Max Cover width', 'wppa'));
			wppa_update_numeric('wppa_text_frame_height', '0', __('Minimal Cover text frame height', 'wppa'));
			
			$slug = 'wppa_bwidth';
			$value = $_POST[$slug];
			if ($value == '') wppa_update_value($slug);
			else wppa_update_numeric($slug, '0', __('Border width', 'wppa'));

			$slug = 'wppa_bradius';
			$value = $_POST[$slug];
			if ($value == '') wppa_update_value($slug);
			else wppa_update_numeric($slug, '0', __('Border radius', 'wppa'));
		
			$floor = get_option('wppa_thumbsize');
			$temp = get_option('wppa_smallsize');
			if ($temp > $floor) $floor = $temp;
			wppa_update_numeric('wppa_popupsize', $floor, __('Popup size', 'wppa'), get_option('wppa_fullsize'));
			
			$slug = 'wppa_fullimage_border_width';
			$value = $_POST[$slug];
			if ($value == '') wppa_update_value($slug);
			else wppa_update_numeric($slug, '0', __('Fullsize border width', 'wppa'));
			
			wppa_update_numeric('wppa_lightbox_bordersize', '0', __('Lightbox Bordersize', 'wppa'));
			

			// Table 2: Visibility
			wppa_update_check('wppa_show_bread');
			wppa_update_check('wppa_show_home');
			wppa_update_value('wppa_bc_separator');
			wppa_update('wppa_bc_txt', htmlspecialchars(stripslashes($_POST['wppa_bc_txt']), ENT_QUOTES));
			wppa_update('wppa_bc_url', $_POST['wppa_bc_url']);
			wppa_update_check('wppa_show_startstop_navigation');
			wppa_update_check('wppa_show_browse_navigation');
			wppa_update_check('wppa_filmstrip');
			wppa_update_check('wppa_film_show_glue');
			wppa_update_check('wppa_show_full_name');
			wppa_update_check('wppa_show_full_desc');
			wppa_update_check('wppa_enable_slideshow');
			wppa_update_check('wppa_rating_on');
			wppa_update_check('wppa_thumb_text_name');
			wppa_update_check('wppa_thumb_text_desc');
			wppa_update_check('wppa_thumb_text_rating');
			wppa_update_check('wppa_show_cover_text');
			wppa_update_check('wppa_show_comments');
			wppa_update_check('wppa_show_bbb');
			wppa_update_check('wppa_show_slideshowbrowselink');
			wppa_update_check('wppa_custom_on');
			wppa_update_textarea('wppa_custom_content');
		
			// Table 3: Backgrounds
			wppa_update_value('wppa_bgcolor_even');
			wppa_update_value('wppa_bcolor_even');
			wppa_update_value('wppa_bgcolor_alt');
			wppa_update_value('wppa_bcolor_alt');
			wppa_update_value('wppa_bgcolor_nav');
			wppa_update_value('wppa_bcolor_nav');		
			wppa_update_value('wppa_bgcolor_namedesc');
			wppa_update_value('wppa_bcolor_namedesc');
			wppa_update_value('wppa_bgcolor_com');
			wppa_update_value('wppa_bcolor_com');
			wppa_update_value('wppa_bgcolor_img');
			wppa_update_value('wppa_bcolor_img');
			wppa_update_value('wppa_bgcolor_fullimg');
			wppa_update_value('wppa_bcolor_fullimg');
			wppa_update_value('wppa_lightbox_backgroundcolor');
			wppa_update_value('wppa_lightbox_bordercolor');
			wppa_update_value('wppa_lightbox_overlaycolor');
			wppa_update_numeric('wppa_lightbox_overlayopacity', '0', __('Lightbox opacity.', 'wppa'), '100');
			wppa_update_value('wppa_bgcolor_cus');
			wppa_update_value('wppa_bcolor_cus');
				
			// Table 4: Behaviour
			wppa_update_value('wppa_fullvalign');
			wppa_update_value('wppa_fullhalign');
			wppa_update_check('wppa_start_slide');
			wppa_update_check('wppa_fadein_after_fadeout');
			wppa_update_value('wppa_slideshow_timeout');
			wppa_update_value('wppa_animation_speed');
			wppa_update_value('wppa_thumbtype');
			wppa_update_value('wppa_thumbphoto_left');
			wppa_update_value('wppa_valign');
			wppa_update_check('wppa_use_thumb_opacity');
			wppa_update_numeric('wppa_thumb_opacity', '0', __('Opacity.', 'wppa'), '100');
			wppa_update_check('wppa_use_thumb_popup');
			wppa_update_value('wppa_coverphoto_pos');
			wppa_update_check('wppa_use_cover_opacity');
			wppa_update_numeric('wppa_cover_opacity', '0', __('Opacity.', 'wppa'), '100');
			wppa_update_check('wppa_rating_login');
			wppa_update_check('wppa_rating_change');
			wppa_update_check('wppa_rating_multi');
			wppa_update_value('wppa_list_albums_by');
			wppa_update_check('wppa_list_albums_desc');
			wppa_update_value('wppa_list_photos_by');
			wppa_update_check('wppa_list_photos_desc');
			wppa_update_check('wppa_comment_login');
			wppa_update_value('wppa_lightbox_animationspeed');
			wppa_update_check('wppa_comments_desc');
		
			// Table 5: Fonts
			wppa_update_value('wppa_fontfamily_title');
			wppa_update_value('wppa_fontsize_title');
			wppa_update_value('wppa_fontcolor_title');
			wppa_update_value('wppa_fontfamily_fulldesc');
			wppa_update_value('wppa_fontsize_fulldesc');
			wppa_update_value('wppa_fontcolor_fulldesc');
			wppa_update_value('wppa_fontfamily_fulltitle');
			wppa_update_value('wppa_fontsize_fulltitle');
			wppa_update_value('wppa_fontcolor_fulltitle');
			wppa_update_value('wppa_fontfamily_nav');
			wppa_update_value('wppa_fontsize_nav');
			wppa_update_value('wppa_fontcolor_nav');
			wppa_update_value('wppa_fontfamily_thumb');
			wppa_update_value('wppa_fontsize_thumb');
			wppa_update_value('wppa_fontcolor_thumb');
			wppa_update_value('wppa_fontfamily_box');
			wppa_update_value('wppa_fontsize_box');
			wppa_update_value('wppa_fontcolor_box');
			wppa_update_value('wppa_fontfamily_lightbox');
			wppa_update_value('wppa_fontsize_lightbox');
			wppa_update_value('wppa_fontcolor_lightbox');

			// Table 6: Links
			wppa_update_value('wppa_mphoto_linktype');
			wppa_update_value('wppa_mphoto_linkpage');
			wppa_update_check('wppa_mphoto_overrule');
			wppa_update_value('wppa_thumb_linktype');
			wppa_update_value('wppa_thumb_linkpage');
			wppa_update_check('wppa_thumb_overrule');
			wppa_update_value('wppa_topten_widget_linktype');
			wppa_update_value('wppa_topten_widget_linkpage');
			wppa_update_check('wppa_topten_overrule');
			wppa_update_value('wppa_slideonly_widget_linktype');
			wppa_update_value('wppa_slideonly_widget_linkpage');
			wppa_update_check('wppa_sswidget_overrule');
			wppa_update_value('wppa_widget_linktype');
			wppa_update_value('wppa_widget_linkpage');
			wppa_update_check('wppa_potdwidget_overrule');
			wppa_update_value('wppa_coverimg_linktype');
//			wppa_update_value('wppa_coverimg_linkpage');
			wppa_update_check('wppa_coverimg_overrule');			

			// Table 7: Security
			if (isset($_POST['wppa_chmod'])) {
				$chmod = $_POST['wppa_chmod'];
				wppa_chmod($chmod);
			}
		
			wppa_update_check('wppa_owner_only');
			wppa_update_value('wppa_set_access_by');

			$need_update = false;
			if (isset($_POST['wppa_accesslevel'])) {
				if (get_option('wppa_accesslevel', '') != $_POST['wppa_accesslevel']) {
					if (get_option('wppa_set_access_by', 'me') == 'me') update_option('wppa_accesslevel', $_POST['wppa_accesslevel']);
					$need_update = true;
				}
			}
			if (isset($_POST['wppa_accesslevel_upload'])) {
				if (get_option('wppa_accesslevel_upload', '') != $_POST['wppa_accesslevel_upload']) {
					if (get_option('wppa_set_access_by', 'me') == 'me') update_option('wppa_accesslevel_upload', $_POST['wppa_accesslevel_upload']);
					$need_update = true;
				}
			}
			if (isset($_POST['wppa_accesslevel_sidebar'])) {
				if (get_option('wppa_accesslevel_sidebar', '') != $_POST['wppa_accesslevel_sidebar']) {
					if (get_option('wppa_set_access_by', 'me') == 'me') update_option('wppa_accesslevel_sidebar', $_POST['wppa_accesslevel_sidebar']);
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
	
			// Table 9: Micellaneous
			if ( is_multisite() && get_option('wppa_multisite', 'no') != 'yes' ) {
				// If set to multi, $_POST['wppa_multisite'] is not included because the whole item is skipped
				// To prevent resetting to 'no' we do the update only if appropriate
				if ( isset($_POST['wppa_multisite']) ) wppa_update_check('wppa_multisite');
			}
			wppa_update_value('wppa_arrow_color');
			wppa_update_value('wppa_search_linkpage');
			wppa_update_check('wppa_excl_sep');
			wppa_update_check('wppa_html');
			wppa_update_check('wppa_allow_debug');
			wppa_update_check('wppa_swap_namedesc');
			wppa_update_value('wppa_max_album_newtime');
			wppa_update_value('wppa_max_photo_newtime');
			wppa_update_check('wppa_use_lightbox');
			wppa_update_numeric('wppa_filter_priority', '0', __('Filter priority', 'wppa'));
			wppa_update_check('wppa_apply_newphoto_desc');
			wppa_update_textarea('wppa_newphoto_description');
		
			// Done update options!
			if ($options_error) wppa_update_message(__('Other changes saved', 'wppa'));
			else {
				wppa_initialize_runtime(true); // force reload of $wppa_opt;
				wppa_update_message(__('Changes Saved', 'wppa'));
			}
			
			// Check for inconsistencies
			if (($wppa_opt['wppa_thumb_linktype'] == 'lightbox' || $wppa_opt['wppa_topten_widget_linktype'] == 'lightbox') && $wppa_opt['wppa_use_lightbox'] == 'no')
				wppa_warning_message(__('You use lightbox, but you disabled the lightbox that comes with WPPA+. Either check Table IX item 9 or make sure you have a lightbox enabled.', 'wppa'));
			if ($wppa_opt['wppa_use_thumb_popup'] == 'yes' && $wppa_opt['wppa_thumb_linktype'] == 'lightbox')
				wppa_error_message(__('You can not have popup and lightbox on thumbnails at the same time. Uncheck either Table IV item 12 or choose a different linktype in Table VI item 2.', 'wppa'));
		}
		
		// Compute the new physical thumbnail image size
		$new_minisize = wppa_get_minisize();
		// Conditionally trigger restart making thumbnails
		if ($old_minisize != $new_minisize) update_option('wppa_lastthumb', '-1');	
		// See if a regeneration of thumbs is pending
		$start = get_option('wppa_lastthumb', '-2');
		if ($start != '-2') {
			$start++; 
			
			$msg = sprintf(__('Regenerating thumbnail images, starting at id=%s. Please wait...<br />', 'wppa'), $start);
			$msg .= __('If the line of dots stops growing or your browser reports Ready but you did NOT get a \'READY regenerating thumbnail images\' message, your server has given up. In that case: continue this action by clicking', 'wppa');
			$msg .= ' <a href="'.wppa_dbg_url(get_admin_url().'admin.php?page=wppa_options').'">'.__('here', 'wppa').'</a>';
			$msg .= ' '.__('and click "Save Changes" again.', 'wppa');
			$max_time = ini_get('max_input_time');	
			if ($max_time > '0') {
				$msg .= sprintf(__('<br /><br />Your server reports that the elapsed time for this operation is limited to %s seconds.', 'wppa'), $max_time);
				$msg .= __('<br />There may also be other restrictions set by the server, like cpu time limit.', 'wppa');
			}
			
			wppa_ok_message($msg);
		
			wppa_regenerate_thumbs(); 
			wppa_ok_message(__('READY regenerating thumbnail images.', 'wppa')); 				
			update_option('wppa_lastthumb', '-2');
		}
	} // if wppa_set_submit
	elseif (get_option('wppa_lastthumb', '-2') != '-2') wppa_error_message(__('Regeneration of thumbnail images interrupted. Please press "Save Changes"', 'wppa')); 
	
	
global $wppa_api_version;
?>		
	<div class="wrap">
		<?php $iconurl = WPPA_URL.'/images/settings32.png'; ?>
		<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
			<br />
		</div>
		<h2><?php _e('WP Photo Album Plus Settings', 'wppa'); ?></h2>
		<?php _e('Database revision:', 'wppa'); ?> <?php echo(get_option('wppa_revision', '100')) ?>. <?php _e('WP Charset:', 'wppa'); ?> <?php echo(get_bloginfo('charset')); ?>. <?php echo 'Current PHP version: ' . phpversion() ?>. <?php echo 'WPPA+ API Version: '.$wppa_api_version ?>.
		<br /><?php if (is_multisite()) { 
			_e('Multisite enabled. '); 
			_e('Blogid = '.$blog_id);			
			if (get_option('wppa_multisite', 'no') == 'no') {
				echo(' ');
				_e('WPPA+ multisite is NOT enabled', 'wppa');
				echo('<br />');
				$msg  = __('This site is a part of a multisite WP installation. It may still contain photos in single site mode.<br /><br />', 'wppa');
				$msg .= __('If you want to keep those photos, use Photo Albums -> Export to save them.<br /><br />', 'wppa');
				$msg .= __('If you saved them already, or if they may be lost, check the <b>Enable WPPA+ multisite</b> checkbox in <b>Table IX item 0</b> and press Save Changes.<br /><br />', 'wppa');
				$msg .= __('This will DISCARD THE EXISTING PHOTOS and enable this site in multisite mode.', 'wppa');
				wppa_error_message($msg);
			}
		}
?>
		<!--<br /><a href="javascript:window.print();"><?php //_e('Print settings', 'wppa') ?></a><br />-->
		<form action="<?php echo(wppa_dbg_url(get_admin_url().'admin.php?page=wppa_options')) ?>" method="post">
	
			<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE); ?>
			
			<?php // Table 1: Sizes ?>
			<h3><?php _e('Table I:', 'wppa'); echo(' '); _e('Sizes:', 'wppa'); ?><?php wppa_toggle_table(1) ?>
				<span style="font-weight:normal; font-size:12px;"><?php _e('This table describes all the sizes and size options (except fontsizes) for the generation and display of the WPPA+ elements.', 'wppa'); ?></span>
			</h3>

			<div id="wppa_table_1" >
				<table class="widefat">
					<thead style="font-weight: bold; " class="wppa_table_1">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Setting', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</thead>
					<tbody class="wppa_table_1">
						<?php 
						$name = __('Column Width', 'wppa');
						$desc = __('The width of the main column in your theme\'s display area.', 'wppa');
						$help = esc_js(__('Enter the width of the main column in your theme\'s display area.', 'wppa'));
						$help .= '\n'.esc_js(__('You should set this value correctly to make sure the fullsize images are properly aligned horizontally.', 'wppa')); 
						$help .= '\n'.esc_js(__('You may enter auto for use in themes that have a floating content column.', 'wppa'));
						$help .= '\n'.esc_js(__('The use of \'auto\' is strongly discouraged. Do not use it unless it is strictly required.', 'wppa'));
						$slug = 'wppa_colwidth';
						$onchange = 'wppaCheckFullHalign()';
						$html = wppa_input($slug, '40px', '', __('pixels wide', 'wppa'), $onchange);
						wppa_setting($slug, '1', $name, $desc, $html, $help);

						$name = __('Fullsize Width', 'wppa');
						$desc = __('The maximum width fullsize photos will be displayed.', 'wppa');
						$help = esc_js(__('Enter the largest size in pixels as how you want your photos to be displayed.', 'wppa'));
						$help .= '\n'.esc_js(__('This is usually the same as the Column Width, but it may differ.', 'wppa'));
						$slug = 'wppa_fullsize';
						$onchange = 'wppaCheckFullHalign()';
						$html = wppa_input($slug, '40px', '', __('pixels wide', 'wppa'), $onchange);
						wppa_setting($slug, '2', $name, $desc, $html, $help);
						
						$name = __('Fullsize Height', 'wppa');
						$desc = __('The maximum height fullsize photos will be displayed.', 'wppa');
						$help = esc_js(__('Enter the largest size in pixels as how you want your photos to be displayed.', 'wppa'));
						$help .= '\n'.esc_js(__('This setting defines the height of the space reserved for full sized photos.', 'wppa'));
						$help .= '\n'.esc_js(__('If you change the width of a display by the %%size= command, this value changes proportionally to match the aspect ratio as defined by this and the previous setting.', 'wppa'));
						$slug = 'wppa_maxheight';
						$html = wppa_input($slug, '40px', '', __('pixels high', 'wppa'));
						wppa_setting($slug, '3', $name, $desc, $html, $help);

						$name = __('Resize on Upload', 'wppa');
						$desc = __('Indicate if the photos should be resized during upload.', 'wppa');
						$help = esc_js(__('If you check this item, the size of the photos will be reduced to the Full Size during the upload/import process.', 'wppa'));
						$help .= '\n'.esc_js(__('The photos will never be enlarged if they are smaller than the Full Size.', 'wppa')); 
						$slug = 'wppa_resize_on_upload';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '4', $name, $desc, $html, $help);
						
						$name = __('Stretch to fit', 'wppa');
						$desc = __('Stretch photos that are too small.', 'wppa');
						$help = esc_js(__('Fullsize images will be stretched to the Full Size at display time if they are smaller. Leaving unchecked is recommended. It is better to upload photos that fit well the sizes you use!', 'wppa'));
						$slug = 'wppa_enlarge';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '5', $name, $desc, $html, $help);

						$name = __('Thumbnail Size', 'wppa');
						$desc = __('The size of the thumbnail images.', 'wppa');
						$help = esc_js(__('This size applies to the width or height, whichever is the largest.', 'wppa'));
						$help .= '\n'.esc_js(__('Changing the thumbnail size may result in all thumbnails being regenerated. this may take a while.', 'wppa'));
						$slug = 'wppa_thumbsize';
						$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
						$class = 'tt_normal';
						wppa_setting($slug, '6', $name, $desc, $html, $help, $class);
						
						$name = __('Thumbframe width', 'wppa');
						$desc = __('The width of the thumbnail frame.', 'wppa');
						$help = esc_js(__('Set the width of the thumbnail frame.', 'wppa'));
						$help .= '\n\n'.esc_js(__('Set width, height and spacing for the thumbnail frames.', 'wppa'));
						$help .= '\n'.esc_js(__('These sizes should be large enough for a thumbnail image and - optionally - the text under it.', 'wppa'));
						$slug = 'wppa_tf_width';
						$html = wppa_input($slug, '40px', '', __('pixels wide', 'wppa'));
						$class = 'tt_normal';
						wppa_setting($slug, '7', $name, $desc, $html, $help, $class);

						$name = __('Thumbframe height', 'wppa');
						$desc = __('The height of the thumbnail frame.', 'wppa');
						$help = esc_js(__('Set the height of the thumbnail frame.', 'wppa'));
						$help .= '\n\n'.esc_js(__('Set width, height and spacing for the thumbnail frames.', 'wppa'));
						$help .= '\n'.esc_js(__('These sizes should be large enough for a thumbnail image and - optionally - the text under it.', 'wppa'));
						$slug = 'wppa_tf_height';
						$html = wppa_input($slug, '40px', '', __('pixels high', 'wppa'));
						$class = 'tt_normal';
						wppa_setting($slug, '8', $name, $desc, $html, $help, $class);

						$name = __('Thumbnail spacing', 'wppa');
						$desc = __('The spacing between adjacent thumbnail frames.', 'wppa');
						$help = esc_js(__('Set the minimal spacing between the adjacent thumbnail frames', 'wppa'));
						$help .= '\n\n'.esc_js(__('Set width, height and spacing for the thumbnail frames.', 'wppa'));
						$help .= '\n'.esc_js(__('These sizes should be large enough for a thumbnail image and - optionally - the text under it.', 'wppa'));
						$slug = 'wppa_tn_margin';
						$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
						$class = 'tt_normal';
						wppa_setting($slug, '9', $name, $desc, $html, $help, $class);
						
						$name = __('Auto spacing', 'wppa');
						$desc = __('Space the thumbnail frames automatic.', 'wppa');
						$help = esc_js(__('If you check this box, the thumbnail images will be evenly distributed over the available width.', 'wppa'));
						$help .= '\n'.esc_js(__('In this case, the thumbnail spacing value (setting I-9) will be regarded as a minimum value.', 'wppa'));
						$slug = 'wppa_thumb_auto';
						$html = wppa_checkbox($slug);
						$class = 'tt_normal';
						wppa_setting($slug, '10', $name, $desc, $html, $help, $class);
						
						$name = __('Photocount threshold', 'wppa');
						$desc = __('Number of thumbnails in an album must exceed.', 'wppa');
						$help = esc_js(__('Photos do not show up in the album unless there are more than this number of photos in the album. This allows you to have cover photos on an album that contains only sub albums without seeing them in the list of sub albums. Usually set to 0 (always show) or 1 (for one cover photo).', 'wppa'));
						$slug = 'wppa_min_thumbs';
						$html = wppa_input($slug, '50px', '', __('pieces', 'wppa'));
						wppa_setting($slug, '11', $name, $desc, $html, $help);
						
						$name = __('Page size', 'wppa');
						$desc = __('Max number of thumbnails per page.', 'wppa');
						$help = esc_js(__('Enter the maximum number of thumbnail images per page. A value of 0 indicates no pagination.', 'wppa'));
						$slug = 'wppa_thumb_page_size';
						$html = wppa_input($slug, '40px', '', __('thumbnails', 'wppa'));
						$class = 'tt_always';
						wppa_setting($slug, '12', $name, $desc, $html, $help, $class);
						
						$name = __('Coverphoto size', 'wppa');
						$desc = __('The size of the coverphoto.', 'wppa');
						$help = esc_js(__('This size applies to the width or height, whichever is the largest.', 'wppa'));
						$help .= '\n'.esc_js(__('Changing the coverphoto size may result in all thumbnails being regenerated. this may take a while.', 'wppa'));
						$slug = 'wppa_smallsize';
						$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
						wppa_setting($slug, '13', $name, $desc, $html, $help);
						
						$name = __('Page size', 'wppa');
						$desc = __('Max number of covers per page.', 'wppa');
						$help = esc_js(__('Enter the maximum number of album covers per page. A value of 0 indicates no pagination.', 'wppa'));
						$slug = 'wppa_album_page_size';
						$html = wppa_input($slug, '40px', '', __('covers', 'wppa'));
						wppa_setting($slug, '14', $name, $desc, $html, $help);
						
						$name = __('TopTen count', 'wppa');
						$desc = __('Number of photos in TopTen widget.', 'wppa');
						$help = esc_js(__('Enter the maximum number of rated photos in the TopTen widget.', 'wppa'));
						$slug = 'wppa_topten_count';
						$html = wppa_input($slug, '40px', '', __('photos', 'wppa'));
						wppa_setting($slug, '15', $name, $desc, $html, $help, 'wppa_rating');
						
						$name = __('TopTen size', 'wppa');
						$desc = __('Size of thumbnails in TopTen widget.', 'wppa');
						$help = esc_js(__('Enter the size for the mini photos in the TopTen widget.', 'wppa'));
						$help .= '\n'.esc_js(__('The size applies to the width or height, whatever is the largest.', 'wppa'));
						$help .= '\n'.esc_js(__('Recommended values: 86 for a two column and 56 for a three column display.', 'wppa'));
						$slug = 'wppa_topten_size';
						$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
						wppa_setting($slug, '16', $name, $desc, $html, $help, 'wppa_rating');
					
						$name = __('Max Cover width', 'wppa');
						$desc = __('Maximum width for a album cover display.', 'wppa');
						$help = esc_js(__('Display covers in 2 or more columns if the display area is wider than the given width.', 'wppa'));
						$help .= '\n'.esc_js(__('This also applies for \'thumbnails as covers\', and will NOT apply to single items.', 'wppa'));
						$slug = 'wppa_max_cover_width';
						$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
						wppa_setting($slug, '17', $name, $desc, $html, $help);

						$name = __('Min Text frame height', 'wppa');
						$desc = __('The minimal cover text frame height.', 'wppa');
						$help = esc_js(__('The minimal height of the description field in an album cover display.', 'wppa'));
						$help .= '\n\n'.esc_js(__('This setting enables you to give the album covers the same height provided that the cover images are equally sized.', 'wppa'));
						$slug = 'wppa_text_frame_height';
						$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
						wppa_setting($slug, '18', $name, $desc, $html, $help);
						
						$name = __('Border thickness', 'wppa');
						$desc = __('Thickness of wppa+ box borders.', 'wppa');
						$help = esc_js(__('Enter the thickness for the border of the WPPA+ boxes. A number of 0 means: no border.', 'wppa'));
						$help .= '\n'.esc_js(__('WPPA+ boxes are: the navigation bars and the filmstrip.', 'wppa'));
						$slug = 'wppa_bwidth';
						$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
						wppa_setting($slug, '19', $name, $desc, $html, $help);
						
						$name = __('Border radius', 'wppa');
						$desc = __('Radius of wppa+ box borders.', 'wppa');
						$help = esc_js(__('Enter the corner radius for the border of the WPPA+ boxes. A number of 0 means: no rounded corners.', 'wppa'));
						$help .= '\n'.esc_js(__('WPPA+ boxes are: the navigation bars and the filmstrip.', 'wppa'));
						$help .= '\n\n'.esc_js(__('Note that rounded corners are only supported by modern browsers.', 'wppa'));
						$slug = 'wppa_bradius';
						$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
						wppa_setting($slug, '20', $name, $desc, $html, $help);
						
						$name = __('Popup size', 'wppa');
						$desc = __('The size of the thumbnail popup images.', 'wppa');
						$help = esc_js(__('Enter the size of the popup images. This size should be larger than the thumbnail size.', 'wppa'));
						$help .= '\n'.esc_js(__('This size should also be at least the cover image size.', 'wppa'));
						$help .= '\n'.esc_js(__('Changing the popup size may result in all thumbnails being regenerated. this may take a while.', 'wppa'));
						$help .= '\n\n'.esc_js(__('Although this setting has only visual effect if "Thumb popup" (Table IV item 12) is checked,', 'wppa'));
						$help .= ' '.esc_js(__('the value must be right as it is the physical size of the thumbnail and coverphoto images.', 'wppa'));
						$slug = 'wppa_popupsize';
						$class = 'tt_normal';
						$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
						wppa_setting($slug, '21', $name, $desc, $html, $help, $class);
				
						$name = __('Fullsize borderwidth', 'wppa');
						$desc = __('The width of the border around fullsize images.', 'wppa');
						$help = esc_js(__('The border is made by the image background being larger than the image itsself (padding).', 'wppa'));
						$help .= '\n'.esc_js(__('Additionally there may be a one pixel outline of a different color. See Table III, item 7.', 'wppa'));
						$help .= '\n\n'.esc_js(__('The number you enter here is exclusive the one pixel outline.', 'wppa'));
						$help .= '\n\n'.esc_js(__('If you leave this entry empty, there will be no outline either.', 'wppa'));
						$slug = 'wppa_fullimage_border_width';
						$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
						wppa_setting($slug, '22', $name, $desc, $html, $help, $class);
						
						$name = __('Lightbox Bordersize', 'wppa');
						$desc = __('The width of the border in lightbox overlay images.', 'wppa');
						$help = esc_js(__('The border is made by the image background being larger than the image itsself (padding).', 'wppa'));
						$slug = 'wppa_lightbox_bordersize';
						$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
						$class = 'wppa_lightbox';
						wppa_setting($slug, '23', $name, $desc, $html, $help, $class);
						
						?>
					</tbody>
					<tfoot style="font-weight: bold;" class="wppa_table_1">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Setting', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</tfoot>
				</table>
			</div>

			<?php // Table 2: Visibility ?>
			<h3><?php _e('Table II:', 'wppa'); echo(' '); _e('Visibility:', 'wppa'); ?><?php wppa_toggle_table(2) ?>
				<span style="font-weight:normal; font-size:12px;"><?php _e('This table describes the visibility of certain wppa+ elements.', 'wppa'); ?></span>
			</h3>
			
			<div id="wppa_table_2" >
				<table class="widefat">
					<thead style="font-weight: bold; " class="wppa_table_2">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Setting', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</thead>
					<tbody class="wppa_table_2">
						<?php 
						$name = __('Breadcrumb', 'wppa');
						$desc = __('Show breadcrumb navigation bars.', 'wppa');
						$help = esc_js(__('Indicate whether a breadcrumb navigation should be displayed', 'wppa'));
						$slug = 'wppa_show_bread';
						$onchange = 'wppaCheckBreadcrumb()';
						$html = wppa_checkbox($slug, $onchange);
						wppa_setting($slug, '1', $name, $desc, $html, $help);

						$name = __('Home', 'wppa');
						$desc = __('Show "Home" in breadcrumb.', 'wppa');
						$help = esc_js(__('Indicate whether the breadcrumb navigation should start with a "Home"-link', 'wppa'));
						$slug = 'wppa_show_home';
						$html = wppa_checkbox($slug);
						$class = 'wppa_bc';
						wppa_setting($slug, '2', $name, $desc, $html, $help, $class);

						$name = __('Separator', 'wppa');
						$desc = __('Breadcrumb separator symbol.', 'wppa');
						$help = esc_js(__('Select the desired breadcrumb separator element.', 'wppa'));
						$help .= '\n'.esc_js(__('A text string may contain valid html.', 'wppa'));
						$help .= '\n'.esc_js(__('An image will be scaled automatically if you set the navigation font size.', 'wppa'));
						$slug = 'wppa_bc_separator';
						$options = array('&raquo', '&rsaquo', '&gt', '&bull', __('Text (html):', 'wppa'), __('Image (url):', 'wppa'));
						$values = array('raquo', 'rsaquo', 'gt', 'bull', 'txt', 'url');
						$onchange = 'wppaCheckBreadcrumb()';
						$html = wppa_select($slug, $options, $values, $onchange);
						$class = 'wppa_bc';
						wppa_setting($slug, '3', $name, $desc, $html, $help, $class);
						
						$name = __('Html', 'wppa');
						$desc = __('Breadcrumb separator text.', 'wppa');
						$help = esc_js(__('Enter the HTML code that produces the separator symbol you want.', 'wppa'));
						$help .= '\n'.esc_js(__('It may be as simple as \'-\' (without the quotes) or as complex as a tag like <div>..</div>.', 'wppa'));
						$slug = 'wppa_bc_txt';
						$html = wppa_input($slug, '100%', '300px');
						wppa_setting($slug, '4', $name, $desc, $html, $help, $slug);

						$name = __('Image Url', 'wppa');
						$desc = __('Full url to separator image.', 'wppa');
						$help = esc_js(__('Enter the full url to the image you want to use for the separator symbol.', 'wppa'));
						$slug = 'wppa_bc_url';
						$html = wppa_input($slug, '100%', '300px');
						wppa_setting($slug, '5', $name, $desc, $html, $help, $slug);
						
						$name = __('Start/stop', 'wppa');
						$desc = __('Show the Start/Stop slideshow bar.', 'wppa');
						$help = esc_js(__('If checked: display the start/stop slideshow navigation bar above the full-size images and slideshow', 'wppa'));
						$slug = 'wppa_show_startstop_navigation';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '6', $name, $desc, $html, $help);
						
						$name = __('Browse bar', 'wppa');
						$desc = __('Show Browse photos bar.', 'wppa');
						$help = esc_js(__('If checked: display the preveous/next navigation bar under the full-size images and slideshow', 'wppa'));
						$slug = 'wppa_show_browse_navigation';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '7', $name, $desc, $html, $help);
						
						$name = __('Filmstrip', 'wppa');
						$desc = __('Show Filmstrip navigation bar.', 'wppa');
						$help = esc_js(__('If checked: display the filmstrip navigation bar under the full_size images and slideshow', 'wppa'));
						$slug = 'wppa_filmstrip';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '8', $name, $desc, $html, $help);
						
						$name = __('Film seam', 'wppa');
						$desc = __('Show seam between end and start of film.', 'wppa');
						$help = esc_js(__('If checked: display the wrap-around point in the filmstrip', 'wppa'));
						$slug = 'wppa_film_show_glue';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '9', $name, $desc, $html, $help);

						$name = __('Fullsize name', 'wppa');
						$desc = __('Display Fullsize name.', 'wppa');
						$help = esc_js(__('If checked: display the name of the photo under the full-size images and slideshow.', 'wppa')); 
						$slug = 'wppa_show_full_name';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '10', $name, $desc, $html, $help);
						
						$name = __('Fullsize desc', 'wppa');
						$desc = __('Display Fullsize description.', 'wppa');
						$help = esc_js(__('If checked: display description under the full-size images and slideshow.', 'wppa'));
						$slug = 'wppa_show_full_desc';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '11', $name, $desc, $html, $help);

						$name = __('Slideshow', 'wppa');
						$desc = __('Enable the slideshow.', 'wppa');
						$help = esc_js(__('If you do not want slideshows: uncheck this box. Browsing full size images will remain possible.', 'wppa'));
						$slug = 'wppa_enable_slideshow';
						$onchange = 'wppaCheckHs()';
						$html = wppa_checkbox($slug, $onchange);
						wppa_setting($slug, '12', $name, $desc, $html, $help);

						$name = __('Rating system', 'wppa');
						$desc = __('Enable the rating system.', 'wppa');
						$help = esc_js(__('If checked, the photo rating system will be enabled.', 'wppa'));
						$slug = 'wppa_rating_on';
						$onchange = 'wppaCheckRating()';
						$html = wppa_checkbox($slug, $onchange);
						wppa_setting($slug, '13', $name, $desc, $html, $help);
						
						$name = __('Thumbnail name', 'wppa');
						$desc = __('Display Thubnail name.', 'wppa');
						$help = esc_js(__('Display photo name under thumbnail images.', 'wppa'));
						$slug = 'wppa_thumb_text_name';
						$html = wppa_checkbox($slug);
						$class = 'tt_normal';
						wppa_setting($slug, '14', $name, $desc, $html, $help, $class);
						
						$name = __('Thumbnail desc', 'wppa');
						$desc = __('Display Thumbnail description.', 'wppa');
						$help = esc_js(__('Display description of the photo under thumbnail images.', 'wppa'));
						$slug = 'wppa_thumb_text_desc';
						$html = wppa_checkbox($slug);
						$class = 'tt_normal';
						wppa_setting($slug, '15', $name, $desc, $html, $help, $class);
						
						$name = __('Thumbnail rating', 'wppa');
						$desc = __('Display Thumbnail Rating.', 'wppa');
						$help = esc_js(__('Display the rating of the photo under the thumbnail image.', 'wppa'));
						$slug = 'wppa_thumb_text_rating';
						$html = '<span class="wppa_rating">'.wppa_checkbox($slug).'</span>&nbsp;&nbsp;<small>'.__('(This setting requires that the rating system is enabled.)', 'wppa').'</small>';
						$class = 'tt_normal';
						wppa_setting($slug, '16', $name, $desc, $html, $help, $class);
						
						$name = __('Covertext', 'wppa');
						$desc = __('Show the text on the album cover.', 'wppa');
						$help = esc_js(__('Display the album decription and the links to the album content', 'wppa'));
						$help .= '\n'.esc_js(__('If switched off, you can only link to the album using the covertitle or the coverphoto.', 'wppa'));
						$help .= '\n'.esc_js(__('Make sure you configure the coverphoto link as desired.', 'wppa'));
						$slug = 'wppa_show_cover_text';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '17', $name, $desc, $html, $help);
						
						$name = __('Comments system', 'wppa');
						$desc = __('Enable the comments system.', 'wppa');
						$help = esc_js(__('Display the comments box under the fullsize images and let users enter their comments on individual photos.', 'wppa'));
						$slug = 'wppa_show_comments';
						$onchange = 'wppaCheckComments()';
						$html = wppa_checkbox($slug, $onchange);
						wppa_setting($slug, '18', $name, $desc, $html, $help);
						
						$name = __('Big Browse Buttons', 'wppa');
						$desc = __('Enable invisible browsing buttons.', 'wppa');
						$help = esc_js(__('If checked, the fullsize image is covered by two invisible areas that act as browse buttons.', 'wppa'));
						$help .= '\n\n'.esc_js(__('Make sure the Full height (Table I item 3) is properly configured to prevent these areas to overlap unwanted space.', 'wppa'));
						$help .= '\n\n'.esc_js(__('A side effect of this setting is that right clicking the image no longer enables the visitor to download the image.', 'wppa'));
						$slug = 'wppa_show_bbb';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '19', $name, $desc, $html, $help);
						
						$name = __('Slideshow/Browse', 'wppa');
						$desc = __('Display the Slideshow / Browse photos link on album covers', 'wppa');
						$help = esc_js(__('This setting causes the Slideshow link to be displayed on the album cover.', 'wppa'));
						$help .= '\n\n'.esc_js(__('If slideshows are disabled in item 12 in this table, you will see a browse link to fullsize images.', 'wppa'));
						$help .= '\n\n'.esc_js(__('If you do not want the browse link link either, uncheck this item.', 'wppa'));
						$help .= '\n\n'.esc_js(__('You might wish to uncheck this item when you have thumbnail links set to lightbox', 'wppa'));
						$slug = 'wppa_show_slideshowbrowselink';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '20', $name, $desc, $html, $help);
						
						$name = __('Show custom box', 'wppa');
						$desc = __('Display the custom box in the slideshow', 'wppa');
						$help = esc_js(__('You can fill the custom box with any html you like. It will not be checked, so it is your own responsability to close tags properly.', 'wppa'));
						$help .= '\n\n'.esc_js(__('The position of the box can be defined in Table IX item 6.', 'wppa'));
						$slug = 'wppa_custom_on';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '21', $name, $desc, $html, $help);
						
						$name = __('Custom content', 'wppa');
						$desc = __('The content (html) of the custom box.', 'wppa');
						$help = esc_js(__('You can fill the custom box with any html you like. It will not be checked, so it is your own responsability to close tags properly.', 'wppa'));
						$help .= '\n\n'.esc_js(__('The position of the box can be defined in Table IX item 6.', 'wppa'));
						$slug = 'wppa_custom_content';
						$html = wppa_textarea($slug);
						wppa_setting($slug, '22', $name, $desc, $html, $help);
						
						?>
					</tbody>
					<tfoot style="font-weight: bold;" class="wppa_table_2">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Setting', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</tfoot>
				</table>
			</div>

			<?php // Table 3: Backgrounds ?>
			<h3><?php _e('Table III:', 'wppa'); echo(' '); _e('Backgrounds:', 'wppa'); ?><?php wppa_toggle_table(3) ?>
				<span style="font-weight:normal; font-size:12px;"><?php _e('This table describes the backgrounds of wppa+ elements.', 'wppa'); ?></span>
			</h3>

			<div id="wppa_table_3" >
				<table class="widefat">
					<thead style="font-weight: bold; " class="wppa_table_3">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Background color', 'wppa') ?></th>
							<th scope="col"><?php _e('Sample', 'wppa') ?></th>
							<th scope="col"><?php _e('Border color', 'wppa') ?></th>
							<th scope="col"><?php _e('Sample', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</thead>
					<tbody class="wppa_table_3">
						<?php 
						$name = __('Even', 'wppa');
						$desc = __('Even background.', 'wppa');
						$help = esc_js(__('Enter valid CSS colors for even numbered backgrounds and borders of album covers and thumbnail displays \'As covers\'.', 'wppa'));
						$slug1 = 'wppa_bgcolor_even';
						$slug2 = 'wppa_bcolor_even';
						$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
						$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
						wppa_setting_2($slug1, $slug2, '1', $name, $desc, $html1, $html2, $help);
						
						$name = __('Odd', 'wppa');
						$desc = __('Odd background.', 'wppa');
						$help = esc_js(__('Enter valid CSS colors for odd numbered backgrounds and borders of album covers and thumbnail displays \'As covers\'.', 'wppa'));
						$slug1 = 'wppa_bgcolor_alt';
						$slug2 = 'wppa_bcolor_alt';
						$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
						$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
						wppa_setting_2($slug1, $slug2, '2', $name, $desc, $html1, $html2, $help);

						$name = __('Nav', 'wppa');
						$desc = __('Navigation bars.', 'wppa');
						$help = esc_js(__('Enter valid CSS colors for navigation backgrounds and borders.', 'wppa'));
						$slug1 = 'wppa_bgcolor_nav';
						$slug2 = 'wppa_bcolor_nav';
						$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
						$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
						wppa_setting_2($slug1, $slug2, '3', $name, $desc, $html1, $html2, $help);

						$name = __('Name/desc', 'wppa');
						$desc = __('Name and Description bars.', 'wppa');
						$help = esc_js(__('Enter valid CSS colors for name and description box backgrounds and borders.', 'wppa'));
						$slug1 = 'wppa_bgcolor_namedesc';
						$slug2 = 'wppa_bcolor_namedesc';
						$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
						$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
						wppa_setting_2($slug1, $slug2, '4', $name, $desc, $html1, $html2, $help);
						
						$name = __('Comments', 'wppa');
						$desc = __('Comment input and display areas.', 'wppa');
						$help = esc_js(__('Enter valid CSS colors for comment box backgrounds and borders.', 'wppa'));
						$slug1 = 'wppa_bgcolor_com';
						$slug2 = 'wppa_bcolor_com';
						$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
						$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
						wppa_setting_2($slug1, $slug2, '5', $name, $desc, $html1, $html2, $help);

						$name = __('Img', 'wppa');
						$desc = __('Cover Photos.', 'wppa');
						$help = esc_js(__('Enter valid CSS colors for Cover photo backgrounds and borders.', 'wppa'));
						$slug1 = 'wppa_bgcolor_img';
						$slug2 = 'wppa_bcolor_img';
						$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
						$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
						wppa_setting_2($slug1, $slug2, '6', $name, $desc, $html1, $html2, $help);
						
						$name = __('FullImg', 'wppa');
						$desc = __('Full size Photos and slideshows.', 'wppa');
						$help = esc_js(__('Enter valid CSS colors for fullsize photo backgrounds and borders.', 'wppa'));
						$help .= '\n'.esc_js(__('The colors may be equal or "transparent"', 'wppa'));
						$help .= '\n'.esc_js(__('For more information about fullsize image borders see the help on Table I, item 22', 'wppa'));
						$slug1 = 'wppa_bgcolor_fullimg';
						$slug2 = 'wppa_bcolor_fullimg';
						$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
						$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
						wppa_setting_2($slug1, $slug2, '7', $name, $desc, $html1, $html2, $help);

						$name = __('Lightbox', 'wppa');
						$desc = __('Lightbox image.', 'wppa');
						$help = esc_js(__('Enter valid CSS colors for lightbox background and border.', 'wppa'));
						$help .= '\n'.esc_js(__('The colors may be equal or "transparent"', 'wppa'));
						$help .= '\n'.esc_js(__('For more information about fullsize image borders see the help on Table I, item 22', 'wppa'));
						$slug1 = 'wppa_lightbox_backgroundcolor';
						$slug2 = 'wppa_lightbox_bordercolor';
						$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
						$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
						$class = 'wppa_lightbox';
						wppa_setting_2($slug1, $slug2, '8', $name, $desc, $html1, $html2, $help, $class);

						$name = __('Overlay', 'wppa');
						$desc = __('Lightbox overlay background.', 'wppa');
						$help = esc_js(__('Enter color and opacity for lightbox overlay background.', 'wppa'));
						$slug1 = 'wppa_lightbox_overlaycolor';
						$slug2 = 'wppa_lightbox_overlayopacity';
						$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
						$html2 = wppa_input($slug2, '100px', '', __('% opacity', 'wppa')) . '</td><td>';
						$class = 'wppa_lightbox';
						wppa_setting_2($slug1, $slug2, '9', $name, $desc, $html1, $html2, $help, $class);
						
						$name = __('Custom', 'wppa');
						$desc = __('Custom box background.', 'wppa');
						$help = esc_js(__('Enter valid CSS colors for custom box backgrounds and borders.', 'wppa'));
						$slug1 = 'wppa_bgcolor_cus';
						$slug2 = 'wppa_bcolor_cus';
						$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
						$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
						wppa_setting_2($slug1, $slug2, '10', $name, $desc, $html1, $html2, $help);
				
						?>
					</tbody>
					<tfoot style="font-weight: bold;" class="wppa_table_3">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Background color', 'wppa') ?></th>
							<th scope="col"><?php _e('Sample', 'wppa') ?></th>
							<th scope="col"><?php _e('Border color', 'wppa') ?></th>
							<th scope="col"><?php _e('Sample', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</tfoot>
				</table>
			</div>
					
			<?php // Table 4: Behaviour ?>
			<h3><?php _e('Table IV:', 'wppa'); echo(' '); _e('Behaviour:', 'wppa'); ?><?php wppa_toggle_table(4) ?>
				<span style="font-weight:normal; font-size:12px;"><?php _e('This table describes the dynamic behaviour of certain wppa+ elements.', 'wppa'); ?></span>
			</h3>

			<div id="wppa_table_4" >
				<table class="widefat">
					<thead style="font-weight: bold; " class="wppa_table_4">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Setting', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</thead>
					<tbody class="wppa_table_4">
						<?php 
						$name = __('V align', 'wppa');
						$desc = __('Vertical alignment of full-size images.', 'wppa');
						$help = esc_js(__('Specify the vertical alignment of fullsize images.', 'wppa'));
						$help .= '\n'.esc_js(__('If you select --- none ---, the photos will not be centered horizontally either.', 'wppa'));
						$slug = 'wppa_fullvalign';
						$options = array(__('--- none ---', 'wppa'), __('top', 'wppa'), __('center', 'wppa'), __('bottom', 'wppa'), __('fit', 'wppa'));
						$values = array('default', 'top', 'center', 'bottom', 'fit');
						$onchange = 'wppaCheckFullHalign()';
						$html = wppa_select($slug, $options, $values, $onchange);
						wppa_setting($slug, '1', $name, $desc, $html, $help);
						
						$name = __('H align', 'wppa');
						$desc = __('Horizontal alignment of full-size images.', 'wppa');
						$help = esc_js(__('Specify the horizontal alignment of fullsize images. If you specify --- none --- , no horizontal alignment will take place.', 'wppa'));
						$help .= '\n\n'.esc_js(__('This setting is only usefull when the Column Width differs from the Fullsize Width.', 'wppa'));
						$help .= '\n'.esc_js(__('(Settings I-1 and I-2)', 'wppa'));
						$slug = 'wppa_fullhalign';
						$options = array(__('--- none ---', 'wppa'), __('left', 'wppa'), __('center', 'wppa'), __('right', 'wppa'));
						$values = array('default', 'left', 'center', 'right');
						$html = wppa_select($slug, $options, $values);
						$class = 'wppa_ha';
						wppa_setting($slug, '2', $name, $desc, $html, $help, $class);
						
						$name = __('Start', 'wppa');
						$desc = __('Start slideshow running.', 'wppa');
						$help = esc_js(__('If checked, the slideshow will start running immediately, if unchecked the first photo will be displayed in browse mode.', 'wppa'));
						$slug = 'wppa_start_slide';
						$html = wppa_checkbox($slug);
						$class = 'wppa_ss';
						wppa_setting($slug, '3', $name, $desc, $html, $help, $class);
						
						$name = __('Fading', 'wppa');
						$desc = __('Fade-in after fade-out.', 'wppa');
						$help = esc_js(__('If checked: slides are faded out and in after each other. If unchecked: fadin and fadeout overlap.', 'wppa'));
						$help .= '\n'.esc_js(__('The version of the jQuery library must be 1.4 or greater for this feature!', 'wppa'));
						$slug = 'wppa_fadein_after_fadeout';
						$onchange = 'checkjQueryRev('.__('Fade-in after fade-out:', 'wppa').', this, 1.4)';
						$html = wppa_checkbox($slug, $onchange);
						$class = 'wppa_ss';
						wppa_setting($slug, '4', $name, $desc, $html, $help, $class);
						?>
						<script type="text/javascript">checkjQueryRev('<?php _e('Fade-in after fade-out:', 'wppa') ?>', document.getElementById('wppa_fadein_after_fadeout'), 1.4)</script>
						<?php

						$name = __('Timeout', 'wppa');
						$desc = __('Slideshow timeout.', 'wppa');
						$help = esc_js(__('Select the time a single slide will be visible when the slideshow is started.', 'wppa'));
						$slug = 'wppa_slideshow_timeout';
						$options = array(__('very short (1 s.)', 'wppa'), __('short (1.5 s.)', 'wppa'), __('normal (2.5 s.)', 'wppa'), __('long (4 s.)', 'wppa'), __('very long (6 s.)', 'wppa'));
						$values = array('1000', '1500', '2500', '4000', '6000');
						$html = wppa_select($slug, $options, $values);
						$class = 'wppa_ss';
						wppa_setting($slug, '5', $name, $desc, $html, $help, $class);
						
						$name = __('Speed', 'wppa');
						$desc = __('Slideshow animation speed.', 'wppa');
						$help = esc_js(__('Specify the animation speed to be used in slideshows.', 'wppa'));
						$help .= '\n'.esc_js(__('This is the time it takes a photo to fade in or out.', 'wppa'));
						$slug = 'wppa_animation_speed';
						$options = array(__('--- off ---', 'wppa'), __('very fast (200 ms.)', 'wppa'), __('fast (400 ms.)', 'wppa'), __('normal (800 ms.)', 'wppa'),  __('slow (1.2 s.)', 'wppa'), __('very slow (2 s.)', 'wppa'), __('extremely slow (4 s.)', 'wppa'));
						$values = array('0', '200', '400', '800', '1200', '2000', '4000');
						$html = wppa_select($slug, $options, $values);
						$class = 'wppa_ss';
						wppa_setting($slug, '6', $name, $desc, $html, $help, $class);
		
						$name = __('Thumbnail type', 'wppa');
						$desc = __('The way the thumbnail images are displayed.', 'wppa');
						$help = esc_js(__('You may select an altenative display method for thumbnails. Note that some of the thumbnail settings do not apply to all available display methods.', 'wppa'));
						$slug = 'wppa_thumbtype';
						$options = array(__('--- default ---', 'wppa'), __('like album covers', 'wppa'), __('--- none ---', 'wppa'));
						$values = array('default', 'ascovers', 'none');
						$onchange = 'wppaCheckThumbType()';
						$html = wppa_select($slug, $options, $values, $onchange);
						wppa_setting($slug, '7', $name, $desc, $html, $help);

						$name = __('Placement', 'wppa');
						$desc = __('Thumbnail image left or right.', 'wppa');
						$help = esc_js(__('Indicate the placement position of the thumbnailphoto you wish.', 'wppa'));
						$slug = 'wppa_thumbphoto_left';
						$options = array(__('Left', 'wppa'), __('Right', 'wppa'));
						$values = array('yes', 'no');
						$html = wppa_select($slug, $options, $values);
						$class = 'tt_ascovers';
						wppa_setting($slug, '8', $name, $desc, $html, $help, $class);

						$name = __('Vertical alignment', 'wppa');
						$desc = __('Vertical alignment of thumbnails.', 'wppa');
						$help = esc_js(__('Specify the vertical alignment of thumbnail images. Use this setting when albums contain both portrait and landscape photos.', 'wppa'));
						$help .= '\n'.esc_js(__('It is NOT recommended to use the value --- default ---; it will affect the horizontal alignment also and is meant to be used with custom css.', 'wppa'));
						$slug = 'wppa_valign';
						$options = array( __('--- default ---', 'wppa'), __('top', 'wppa'), __('center', 'wppa'), __('bottom', 'wppa'));
						$values = array('default', 'top', 'center', 'bottom');
						$html = wppa_select($slug, $options, $values);
						$class = 'tt_normal';
						wppa_setting($slug, '9', $name, $desc, $html, $help, $class);
						
						$name = __('Thumb mouseover', 'wppa');
						$desc = __('Apply thumbnail mouseover effect.', 'wppa');
						$help = esc_js(__('Check this box to use mouseover effect on thumbnail images.', 'wppa'));
						$slug = 'wppa_use_thumb_opacity';
						$onchange = 'wppaCheckUseThumbOpacity()';
						$html = wppa_checkbox($slug, $onchange);
						$class = 'tt_normal';
						wppa_setting($slug, '10', $name, $desc, $html, $help, $class);
						
						$name = __('Thumb opacity', 'wppa');
						$desc = __('Initial opacity value.', 'wppa');
						$help = esc_js(__('Enter percentage of opacity. 100% is opaque, 0% is transparant', 'wppa'));
						$slug = 'wppa_thumb_opacity';
						$html = '<span class="thumb_opacity_html">'.wppa_input($slug, '50px', '', __('%', 'wppa')).'</span>&nbsp;&nbsp;<small>'.__('(This setting requires thumbnail mouseover to be switched on)', 'wppa').'</small>';
						$class = 'tt_normal';
						wppa_setting($slug, '11', $name, $desc, $html, $help, $class);

						$name = __('Thumb popup', 'wppa');
						$desc = __('Use popup effect on thumbnail images.', 'wppa');
						$help = esc_js(__('Thumbnails pop-up to a larger image when hovered.', 'wppa'));
						$slug = 'wppa_use_thumb_popup';
						$html = wppa_checkbox($slug);
						$class = 'tt_normal';
						wppa_setting($slug, '12', $name, $desc, $html, $help, $class);
						
						$name = __('Placement', 'wppa');
						$desc = __('Cover image position.', 'wppa');
						$help = esc_js(__('Indicate the placement position of the coverphoto you wish.', 'wppa'));
						$slug = 'wppa_coverphoto_pos';
						$options = array(__('Left', 'wppa'), __('Right', 'wppa'), __('Top', 'wppa'), __('Bottom', 'wppa'));
						$values = array('left', 'right', 'top', 'bottom');
						$html = wppa_select($slug, $options, $values);
						wppa_setting($slug, '13', $name, $desc, $html, $help);

						$name = __('Cover mouseover', 'wppa');
						$desc = __('Apply coverphoto mouseover effect.', 'wppa');
						$help = esc_js(__('Check this box to use mouseover effect on cover images.', 'wppa'));
						$slug = 'wppa_use_cover_opacity';
						$onchange = 'wppaCheckUseCoverOpacity()';
						$html = wppa_checkbox($slug, $onchange);
						wppa_setting($slug, '14', $name, $desc, $html, $help);

						$name = __('Cover opacity', 'wppa');
						$desc = __('Initial opacity value.', 'wppa');
						$help = esc_js(__('Enter percentage of opacity. 100% is opaque, 0% is transparant', 'wppa'));
						$slug = 'wppa_cover_opacity';
						$html = '<span class="cover_opacity_html">'.wppa_input($slug, '50px', '', __('%', 'wppa')).'</span>&nbsp;&nbsp;<small>'.__('(This setting requires coverphoto mouseover to be switched on)', 'wppa').'</small>';
						$class = 'tt_normal';
						wppa_setting($slug, '15', $name, $desc, $html, $help, $class);

						$name = __('Rating login', 'wppa');
						$desc = __('Users must login to rate photos.', 'wppa');
						$help = esc_js(__('If users want to vote for a photo (rating 1..5 stars) the must login first. The avarage rating will always be displayed as long as the rating system is enabled.', 'wppa'));
						$slug = 'wppa_rating_login';
						$html = wppa_checkbox($slug);
						$class = 'wppa_rating';
						wppa_setting($slug, '16', $name, $desc, $html, $help, $class);
						
						$name = __('Rating change', 'wppa');
						$desc = __('Users may change their ratings.', 'wppa');
						$help = esc_js(__('Users may change their ratings.', 'wppa'));
						$slug = 'wppa_rating_change';
						$html = wppa_checkbox($slug);
						$class = 'wppa_rating';
						wppa_setting($slug, '17', $name, $desc, $html, $help, $class);
						
						$name = __('Rating multi', 'wppa');
						$desc = __('Users may give multiple votes.', 'wppa');
						$help = esc_js(__('Users may give multiple votes. (This has no effect when users may change their votes.)', 'wppa'));
						$slug = 'wppa_rating_multi';
						$html = wppa_checkbox($slug);
						$class = 'wppa_rating';
						wppa_setting($slug, '18', $name, $desc, $html, $help, $class);

						$name = __('Album order', 'wppa');
						$desc = __('Album ordering sequence method.', 'wppa');
						$help = esc_js(__('Specify the way the albums should be ordered.', 'wppa'));
						$slug = 'wppa_list_albums_by';
						$options = array(__('--- none ---', 'wppa'), __('Order #', 'wppa'), __('Name', 'wppa'), __('Random', 'wppa'));
						$values = array('0', '1', '2', '3');
						$html = wppa_select($slug, $options, $values);
						wppa_setting($slug, '19', $name, $desc, $html, $help);
						
						$name = __('Descending', 'wppa');
						$desc = __('Descending order.', 'wppa');
						$help = esc_js(__('If checked: largest first', 'wppa'));
						$slug = 'wppa_list_albums_desc';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '20', $name, $desc, $html, $help);
						
						$name = __('Photo order', 'wppa');
						$desc = __('Photo ordering sequence method.', 'wppa');
						$help = esc_js(__('Specify the way the photos should be ordered. This is the default setting. You can overrule the default sorting order on a per album basis.', 'wppa'));
						$slug = 'wppa_list_photos_by';
						$options = array(__('--- none ---', 'wppa'), __('Order #', 'wppa'), __('Name', 'wppa'), __('Random', 'wppa'), __('Rating', 'wppa'));
						$values = array('0', '1', '2', '3', '4');
						$html = wppa_select($slug, $options, $values);
						wppa_setting($slug, '21', $name, $desc, $html, $help);
						
						$name = __('Descending', 'wppa');
						$desc = __('Descending order.', 'wppa');
						$help = esc_js(__('If checked: largest first', 'wppa'));
						$help .= '\n'.esc_js(__('This is a system wide setting.', 'wppa'));
						$slug = 'wppa_list_photos_desc';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '22', $name, $desc, $html, $help);
						
						$name = __('Commenting login', 'wppa');
						$desc = __('Users must be logged in to comment on photos.', 'wppa');
						$help = esc_js(__('Check this box if you want users to be logged in to be able to enter comments on individual photos.', 'wppa'));
						$slug = 'wppa_comment_login';
						$html = wppa_checkbox($slug);
						$class = 'wppa_comment';
						wppa_setting($slug, '23', $name, $desc, $html, $help, $class);
						
						$name = __('Lightbox Speed', 'wppa');
						$desc = __('Lightbox animation speed.', 'wppa');
						$help = esc_js(__('The higher the number, the faster the animation', 'wppa'));
						$slug = 'wppa_lightbox_animationspeed';
						$options = array(__('--- off ---', 'wppa'), '1', '2', '3', '4', '5', '6', '7', '8', '9', '10');
						$values = array('1000', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10');
						$html = wppa_select($slug, $options, $values);
						$class = 'wppa_lightbox';
						wppa_setting($slug, '24', $name, $desc, $html, $help, $class);
						
						$name = __('Last comment first', 'wppa');
						$desc = __('Display the newest comment on top.', 'wppa');
						$help = '';
						$slug = 'wppa_comments_desc';
						$html = wppa_checkbox($slug);
						$class = 'wppa_comment';
						wppa_setting($slug, '25', $name, $desc, $html, $help, $class);
						
						?>
					</tbody>
					<tfoot style="font-weight: bold;" class="wppa_table_4">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Setting', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</tfoot>
				</table>
			</div>

			<?php // Table 5: Fonts ?>
			<h3><?php _e('Table V:', 'wppa'); echo(' '); _e('Fonts:', 'wppa'); ?><?php wppa_toggle_table(5) ?>
				<span style="font-weight:normal; font-size:12px;"><?php _e('This table describes the Fonts used for the wppa+ elements.', 'wppa'); ?>
						<?php _e('If you leave fields empty, your themes defaults will be used.', 'wppa'); ?></span>
			</h3>
			
			<div id="wppa_table_5" >
				<table class="widefat">
					<thead style="font-weight: bold; " class="wppa_table_5">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Font family', 'wppa') ?></th>
							<th scope="col"><?php _e('Font size', 'wppa') ?></th>
							<th scope="col"><?php _e('Font color', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</thead>
					<tbody class="wppa_table_5">
						<?php 
						$name = __('Album titles', 'wppa');
						$desc = __('Font used for Album titles.', 'wppa');
						$help = esc_js(__('Enter font name, size and color for album cover titles.', 'wppa'));
						$slug1 = 'wppa_fontfamily_title';
						$slug2 = 'wppa_fontsize_title';
						$slug3 = 'wppa_fontcolor_title';
						$html1 = wppa_input($slug1, '100%', '300px', '');
						$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
						$html3 = wppa_input($slug3, '70px', '', '');
						wppa_setting_3($slug1, $slug2, $slug3, '1,2,3', $name, $desc, $html1, $html2, $html3, $help);

						$name = __('Fullsize desc', 'wppa');
						$desc = __('Font for fullsize photo descriptions.', 'wppa');
						$help = esc_js(__('Enter font name, size and color for fullsize photo descriptions.', 'wppa'));
						$slug1 = 'wppa_fontfamily_fulldesc';
						$slug2 = 'wppa_fontsize_fulldesc';
						$slug3 = 'wppa_fontcolor_fulldesc';
						$html1 = wppa_input($slug1, '100%', '300px', '');
						$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
						$html3 = wppa_input($slug3, '70px', '', '');
						wppa_setting_3($slug1, $slug2, $slug3, '4,5,6', $name, $desc, $html1, $html2, $html3, $help);
						
						$name = __('Fullsize name', 'wppa');
						$desc = __('Font for fullsize photo names.', 'wppa');
						$help = esc_js(__('Enter font name, size and color for album cover titles.', 'wppa'));
						$slug1 = 'wppa_fontfamily_fulltitle';
						$slug2 = 'wppa_fontsize_fulltitle';
						$slug3 = 'wppa_fontcolor_fulltitle';
						$html1 = wppa_input($slug1, '100%', '300px', '');
						$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
						$html3 = wppa_input($slug3, '70px', '', '');
						wppa_setting_3($slug1, $slug2, $slug3, '7,8,9', $name, $desc, $html1, $html2, $html3, $help);
						
						$name = __('Navigations', 'wppa');
						$desc = __('Font for navigations.', 'wppa');
						$help = esc_js(__('Enter font name, size and color for navigation items.', 'wppa'));
						$slug1 = 'wppa_fontfamily_nav';
						$slug2 = 'wppa_fontsize_nav';
						$slug3 = 'wppa_fontcolor_nav';
						$html1 = wppa_input($slug1, '100%', '300px', '');
						$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
						$html3 = wppa_input($slug3, '70px', '', '');
						wppa_setting_3($slug1, $slug2, $slug3, '10,11,12', $name, $desc, $html1, $html2, $html3, $help);
						
						$name = __('Thumbnails', 'wppa');
						$desc = __('Font for text under thumbnails.', 'wppa');
						$help = esc_js(__('Enter font name, size and color for text under thumbnail images.', 'wppa'));
						$slug1 = 'wppa_fontfamily_thumb';
						$slug2 = 'wppa_fontsize_thumb';
						$slug3 = 'wppa_fontcolor_thumb';
						$html1 = wppa_input($slug1, '100%', '300px', '');
						$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
						$html3 = wppa_input($slug3, '70px', '', '');
						wppa_setting_3($slug1, $slug2, $slug3, '13,14,15', $name, $desc, $html1, $html2, $html3, $help);
						
						$name = __('Other', 'wppa');
						$desc = __('General font in wppa boxes.', 'wppa');
						$help = esc_js(__('Enter font name, size and color for all other items.', 'wppa')); 
						$slug1 = 'wppa_fontfamily_box';
						$slug2 = 'wppa_fontsize_box';
						$slug3 = 'wppa_fontcolor_box';
						$html1 = wppa_input($slug1, '100%', '300px', '');
						$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
						$html3 = wppa_input($slug3, '70px', '', '');
						wppa_setting_3($slug1, $slug2, $slug3, '16,17,18', $name, $desc, $html1, $html2, $html3, $help);	

						$name = __('Lightbox', 'wppa');
						$desc = __('Font in wppa lightbox boxes.', 'wppa');
						$help = esc_js(__('Enter font name, size and color for lightbox overlays.', 'wppa')); 
						$slug1 = 'wppa_fontfamily_lightbox';
						$slug2 = 'wppa_fontsize_lightbox';
						$slug3 = 'wppa_fontcolor_lightbox';
						$html1 = wppa_input($slug1, '100%', '300px', '');
						$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
						$html3 = wppa_input($slug3, '70px', '', '');
						wppa_setting_3($slug1, $slug2, $slug3, '19,20,21', $name, $desc, $html1, $html2, $html3, $help);	
						
						?>
					</tbody>
					<tfoot style="font-weight: bold;" class="wppa_table_5">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Font family', 'wppa') ?></th>
							<th scope="col"><?php _e('Font size', 'wppa') ?></th>
							<th scope="col"><?php _e('Font color', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</tfoot>
				</table>
			</div>

			<?php // Table 6: Links ?>
			<h3><?php _e('Table VI:', 'wppa'); echo(' '); _e('Links:', 'wppa'); ?><?php wppa_toggle_table(6) ?>
				<span style="font-weight:normal; font-size:12px;"><?php _e('This table defines the link types and pages.', 'wppa'); ?></span>
			</h3>
			
			<div id="wppa_table_6" >
				<table class="widefat">
					<thead style="font-weight: bold; " class="wppa_table_6">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Link type', 'wppa') ?></th>
							<th scope="col"><?php _e('Link page', 'wppa') ?></th>
							<th scope="col"><?php _e('PS Overrule', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</thead>
					<tbody class="wppa_table_6">
						<?php 
						// Linktypes
						$options_linktype = array(__('no link at all.', 'wppa'), __('the plain photo (file).', 'wppa'), __('the full size photo in a slideshow.', 'wppa'), __('the fullsize photo on its own.', 'wppa'), __('the fullsize photo with a print button.', 'wppa'), __('lightbox.', 'wppa'));
						$values_linktype = array('none', 'file', 'photo', 'single', 'fullpopup', 'lightbox'); //, 'indiv');
						$options_linktype_album = array(__('no link at all.', 'wppa'), __('the plain photo (file).', 'wppa'), __('the content of the album.', 'wppa'), __('the full size photo in a slideshow.', 'wppa'), __('the fullsize photo on its own.', 'wppa')); //, __('the photo specific link.', 'wppa'));
						$values_linktype_album = array('none', 'file', 'album', 'photo', 'single'); //, 'indiv');
						$options_linktype_ss_widget = array(__('no link at all.', 'wppa'), __('the plain photo (file).', 'wppa'), __('defined at widget activation.', 'wppa'), __('the content of the album.', 'wppa'), __('the full size photo in a slideshow.', 'wppa'), __('the fullsize photo on its own.', 'wppa')); //, __('the photo specific link.', 'wppa'));
						$values_linktype_ss_widget = array('none', 'file', 'widget', 'album', 'photo', 'single'); //, 'indiv');
						$options_linktype_potd_widget = array(__('no link at all.', 'wppa'), __('the plain photo (file).', 'wppa'), __('defined on widget admin page.', 'wppa'), __('the content of the album.', 'wppa'), __('the full size photo in a slideshow.', 'wppa'), __('the fullsize photo on its own.', 'wppa')); //, __('the photo specific link.', 'wppa'));
						$values_linktype_potd_widget = array('none', 'file', 'custom', 'album', 'photo', 'single'); //, 'indiv');
						$options_linktype_cover_image = array(__('no link at all.', 'wppa'), __('the plain photo (file).', 'wppa'), __('same as title.', 'wppa'));
						$values_linktype_cover_image = array('none', 'file', 'same');

						// Linkpages
						$options_page = false;
						$options_page_post = false;
						$values_page = false;
						$values_page_post = false;
						// First
						$options_page_post[] = __('--- The same post or page ---', 'wppa');
						$values_page_post[] = '0';
						// Pages if any
						$query = "SELECT ID, post_title, post_content FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish' ORDER BY post_title ASC";
						$pages = $wpdb->get_results ($query, 'ARRAY_A');
						if ($pages) {
							foreach ($pages as $page) {
								if (stripos($page['post_content'], '%%wppa%%') !== false) {
									$options_page[] = $page['post_title'];
									$options_page_post[] = $page['post_title'];
									$values_page[] = $page['ID'];
									$values_page_post[] = $page['ID'];
								}
							}
						}
						else {
							$options_page[] = __('--- No page to link to (yet) ---', 'wppa');
							$values_page[] = '0';
						}

						$name = __('Mphoto', 'wppa');
						$desc = __('Media-like photo link.', 'wppa');
						$help = esc_js(__('Select the type of link you want, or no link at all.', 'wppa')); 
						$help .= '\n'.esc_js(__('If you select the fullsize photo on its own, it will be stretched to fit, regardless of that setting.', 'wppa')); /* oneofone is treated as portrait only */
						$help .= '\n'.esc_js(__('Note that a page must have at least %%wppa%% in its content to show up the photo(s).', 'wppa')); 
						$slug1 = 'wppa_mphoto_linktype';
						$slug2 = 'wppa_mphoto_linkpage';
						$slug3 = 'wppa_mphoto_overrule';
						$onchange = 'wppaCheckMphotoLink()';
						$html1 = wppa_select($slug1, $options_linktype_album, $values_linktype_album, $onchange);
						$class = 'wppa_mlp';
						$html2 = wppa_select($slug2, $options_page, $values_page, '', $class);
						$html3 = wppa_checkbox($slug3);
						wppa_setting_3($slug1, $slug2, $slug3, '1a,b,c', $name, $desc, $html1, $html2, $html3, $help);

						$name = __('Thumbnail', 'wppa');
						$desc = __('Thumbnail link.', 'wppa');
						$help = esc_js(__('Select the type of link you want, or no link at all.', 'wppa'));
						$help .= '\n'.esc_js(__('If you select the fullsize photo on its own, it will be stretched to fit, regardless of that setting.', 'wppa')); /* oneofone is treated as portrait only */ 
						$help .= '\n'.esc_js(__('Note that a page must have at least %%wppa%% in its content to show up the photo(s).', 'wppa'));
						$slug1 = 'wppa_thumb_linktype';
						$slug2 = 'wppa_thumb_linkpage';
						$slug3 = 'wppa_thumb_overrule';
						$onchange = 'wppaCheckThumbLink()';
						$html1 = wppa_select($slug1, $options_linktype, $values_linktype, $onchange);
						$class = 'wppa_tlp';
						$html2 = wppa_select($slug2, $options_page_post, $values_page_post, '', $class);
						$html3 = wppa_checkbox($slug3);
						$class = 'tt_always';
						wppa_setting_3($slug1, $slug2, $slug3, '2a,b,c', $name, $desc, $html1, $html2, $html3, $help, $class);
						
						$name = __('TopTenWidget', 'wppa');
						$desc = __('TopTen widget photo link.', 'wppa');
						$help = esc_js(__('Select the type of link the top ten photos point to.', 'wppa')); 
						$slug1 = 'wppa_topten_widget_linktype'; 
						$slug2 = 'wppa_topten_widget_linkpage';
						$slug3 = 'wppa_topten_overrule';
						$onchange = 'wppaCheckTopTenLink()';
						$html1 = wppa_select($slug1, $options_linktype, $values_linktype, $onchange);
						$class = 'wppa_ttlp';
						$html2 = wppa_select($slug2, $options_page, $values_page, '', $class);
						$html3 = wppa_checkbox($slug3);
						$class = 'wppa_rating';
						wppa_setting_3($slug1, $slug2, $slug3, '3a,b,c', $name, $desc, $html1, $html2, $html3, $help, $class);
						
						$name = __('SlideWidget', 'wppa');
						$desc = __('Slideshow widget photo link.', 'wppa');
						$help = esc_js(__('Select the type of link the top ten photos point to.', 'wppa')); 
						$slug1 = 'wppa_slideonly_widget_linktype';
						$slug2 = 'wppa_slideonly_widget_linkpage';
						$slug3 = 'wppa_sswidget_overrule';
						$onchange = 'wppaCheckSlideOnlyLink()';
						$html1 = wppa_select($slug1, $options_linktype_ss_widget, $values_linktype_ss_widget, $onchange);
						$class = 'wppa_solp';
						$html2 = wppa_select($slug2, $options_page, $values_page, '', $class);
						$html3 = wppa_checkbox($slug3);
						wppa_setting_3($slug1, $slug2, $slug3, '4a,b,c', $name, $desc, $html1, $html2, $html3, $help);
						
						$name = __('PotdWidget', 'wppa');
						$desc = __('Photo Of The Day widget link.', 'wppa');
						$help = esc_js(__('Select the type of link the photo of the day points to.', 'wppa')); 
						$help .= '\n\n'.esc_js(__('If you select \'defined on widget admin page\' you can manually enter a link and title on the Photo of the day Widget Admin page.', 'wppa'));
						$slug1 = 'wppa_widget_linktype';
						$slug2 = 'wppa_widget_linkpage';
						$slug3 = 'wppa_potdwidget_overrule';
						$onchange = 'wppaCheckPotdLink()';
						$html1 = wppa_select($slug1, $options_linktype_potd_widget, $values_linktype_potd_widget, $onchange);
						$class = 'wppa_potdlp';
						$html2 = wppa_select($slug2, $options_page, $values_page, '', $class);
						$html3 = wppa_checkbox($slug3);
						wppa_setting_3($slug1, $slug2, $slug3, '5a,b,c', $name, $desc, $html1, $html2, $html3, $help);
						
						$name = __('Cover Image', 'wppa');
						$desc = __('The link from the cover image of an album.', 'wppa');
						$help = esc_js(__('Select the type of link the coverphoto points to.', 'wppa'));
						$help .= '\n\n'.esc_js(__('The link from the album title can be configured on the Edit Album page.', 'wppa'));
						$help .= '\n'.esc_js(__('This link will be used for the photo also if you select: same as title.', 'wppa'));
						$slug1 = 'wppa_coverimg_linktype';
						$slug2 = 'wppa_coverimg_linkpage';
						$slug3 = 'wppa_coverimg_overrule';
						$onchange = '';
						$html1 = wppa_select($slug1, $options_linktype_cover_image, $values_linktype_cover_image, $onchange);
						$class = '';
						$html2 = '';
						$html3 = wppa_checkbox($slug3);
						wppa_setting_3($slug1, $slug2, $slug3, '6a,b,c', $name, $desc, $html1, $html2, $html3, $help);
						
						?>
						
					</tbody>
					<tfoot style="font-weight: bold;" class="wppa_table_6">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Link type', 'wppa') ?></th>
							<th scope="col"><?php _e('Link page', 'wppa') ?></th>
							<th scope="col"><?php _e('PS Overrule', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</tfoot>
				</table>
			</div>
			
			<?php // Table 7: Security ?>
			<h3><?php _e('Table VII:', 'wppa'); echo(' '); _e('Access and Security:', 'wppa'); ?><?php wppa_toggle_table(7) ?>
				<span style="font-weight:normal; font-size:12px;"><?php _e('This table describes the access settings for wppa+ elements and pages.', 'wppa'); ?></span>
			</h3>

			<div id="wppa_table_7" >
				<table class="widefat">
					<thead style="font-weight: bold; " class="wppa_table_7">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Setting', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</thead>
					<tbody class="wppa_table_7">
						<?php 
						$name = 'CHMOD';
						$desc = __('Directory access (CHMOD).', 'wppa');
						$help = esc_js(__('In rare cases you might need to change this setting. If you do not know what this means, leave it unchanged.', 'wppa'));
						$slug = 'wppa_chmod';
						$options = array(__('Leave unchanged.', 'wppa'), '750', '755', '775', '777');
						$values = array('0', '750', '755', '775', '777');
						$html = wppa_select($slug, $options, $values);
						wppa_setting($slug, '1', $name, $desc, $html, $help);

						$name = __('Album access', 'wppa');
						$desc = __('Limit album access to album owners only.', 'wppa');
						$help = esc_js(__('If checked, users who can edit albums and upload/import photos can do that with their own albums only.', 'wppa')); 
						$help .= '\n'.esc_js(__('Users can give their albums to another user. Administrators can change ownership and access all albums always.', 'wppa'));
						$slug = 'wppa_owner_only';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '2', $name, $desc, $html, $help);
						
						$name = __('WPPA Admin', 'wppa');
						$desc = __('Admin pages access.', 'wppa'); 
						$help = esc_js(__('Indicate whether the accesslevels must be set by this admin page or by an other program.', 'wppa'));
						$help .= '\n\n'.esc_js(__('If you set them here, the classical userlevel is imitated, but implemented by the modern Roles and Capabilities system.', 'wppa')); 
						$help .= '\n'.esc_js(__('That means that any higher userlevel (role) will automaticly get the capabilities that you give to a certain (lower) level.', 'wppa')); 
						$help .= '\n'.esc_js(__('If you want to give a capability to a specific user or role, you can set it using an other plugin, such as Capability Manager.', 'wppa')); 
						$help .= '\n'.esc_js(__('Possible capabilities are: <strong>wppa_admin</strong> (for the Photo Albums page), wppa_sidebar_admin (for the Sidebar Widget page) and wppa_upload (for the Upload and Import pages).', 'wppa')); 
						$help .= '\n'.esc_js(__('The Help page is available to users with the capability <strong>edit_posts</strong>, the Settings page (the page you are on right now) is limited to the role of administrator.', 'wppa')); 
						$slug = 'wppa_set_access_by';
						$options = array(__('Accesslevels are set here.', 'wppa'), __('Accesslevels are set by an other program.', 'wppa'));
						$values = array('me', 'other');
						$html = wppa_select($slug, $options, $values);
						wppa_setting($slug, '3', $name, $desc, $html, $help);
						
						$name = __('Photo Albums', 'wppa');
						$desc = __('Albums Access Level.', 'wppa');
						$help = esc_js(__('The minmum user level that can access the photo album admin (i.e. Manage Albums and Upload Photos).', 'wppa'));
						$slug = 'wppa_accesslevel';
						$options = array(__('Administrator', 'wppa'), __('Editor', 'wppa'), __('Author', 'wppa'), __('Contributor', 'wppa'));
						$values = array('administrator', 'editor', 'author', 'contributor');
						$html = wppa_select($slug, $options, $values);
						wppa_setting($slug, '4', $name, $desc, $html, $help);
						
						$name = __('Upload', 'wppa');
						$desc = __('Upload/Import Access Level.', 'wppa');
						$help = esc_js(__('The minmum user level that can upload or import photos.', 'wppa')); 
						$slug = 'wppa_accesslevel_upload';
						$options = array(__('Administrator', 'wppa'), __('Editor', 'wppa'), __('Author', 'wppa'), __('Contributor', 'wppa'));
						$values = array('administrator', 'editor', 'author', 'contributor');
						$html = wppa_select($slug, $options, $values);
						wppa_setting($slug, '5', $name, $desc, $html, $help);
						
						$name = __('Widget', 'wppa');
						$desc = __('Photo of the day widget admin.', 'wppa');
						$help = esc_js(__('The minmum user level that can access the photo of the day sidebar widget admin.', 'wppa'));
						$slug = 'wppa_accesslevel_sidebar';
						$options = array(__('Administrator', 'wppa'), __('Editor', 'wppa'), __('Author', 'wppa'), __('Contributor', 'wppa'));
						$values = array('administrator', 'editor', 'author', 'contributor');
						$html = wppa_select($slug, $options, $values);
						wppa_setting($slug, '6', $name, $desc, $html, $help);
						
						?>
					</tbody>
					<tfoot style="font-weight: bold;" class="wppa_table_7">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Setting', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</tfoot>
				</table>
			</div>

			<?php // Table 8: Actions ?>
			<h3><?php _e('Table VIII:', 'wppa'); echo(' '); _e('Actions:', 'wppa'); ?><?php wppa_toggle_table(8) ?>
				<span style="font-weight:normal; font-size:12px;"><?php _e('This table lists all actions that can be taken to the wppa+ system', 'wppa'); ?></span>
			</h3>
			
			<div id="wppa_table_8" >
				<table class="widefat">
					<thead style="font-weight: bold; " class="wppa_table_8">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Specification', 'wppa') ?></th>
							<th scope="col"><?php _e('Do it!', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</thead>
					<tbody class="wppa_table_8">
						<?php 
						$wppa['no_default'] = true;
						
						$name = __('Reset', 'wppa');
						$desc = __('Reset all ratings.', 'wppa');
						$help = esc_js(__('WARNING: If checked, this will clear all ratings in the system!', 'wppa'));
						$slug = 'wppa_rating_clear';
						$html = wppa_radio('wppa_action', $slug);
						wppa_setting_2('', $slug, '1', $name, $desc, '', $html, $help); //, 'wppa_rating');
						
						$name = __('Set to utf-8', 'wppa');
						$desc = __('Set Character set to UTF_8.', 'wppa');
						$help = esc_js(__('If checked: Converts the wppa database tables to UTF_8 This allows the use of certain characters - like Turkish - in photo and album names and descriptions.', 'wppa'));
						$slug = 'wppa_charset';
						$html = wppa_radio('wppa_action', $slug);
						wppa_setting_2('', $slug, '2', $name, $desc, '', $html, $help, 'wppa_utf8');
	//					if (get_option('wppa_charset') == 'UTF_8') { ?>
	<!--						<script type="text/javascript">jQuery('.wppa_utf8').css('color', '#999');jQuery('.wppa_utf8_html').css('visibility', 'hidden');</script>	-->
						<?php // }

						$name = __('Setup', 'wppa');
						$desc = __('Re-initialize plugin.', 'wppa');
						$help = esc_js(__('Re-initilizes the plugin, (re)creates database tables and sets up default settings and directories if required.', 'wppa'));
						$help .= '\n\n'.esc_js(__('This action may be required to setup blogs in a multiblog (network) site as well as in rare cases to correct initilization errors.', 'wppa'));
						$slug = 'wppa_setup';
						$html = wppa_radio('wppa_action', $slug);
						wppa_setting_2('', $slug, '3', $name, $desc, '', $html, $help);
						
						$name = __('Backup settings', 'wppa');
						$desc = __('Save all settings into a backup file.', 'wppa');
						$help = esc_js(__('Saves all the settings into a backup file', 'wppa'));
						$slug = 'wppa_backup';
						$html = wppa_radio('wppa_action', $slug);
						wppa_setting_2('', $slug, '4', $name, $desc, '', $html, $help);
						
						$name = __('Load settings', 'wppa');
						$desc = __('Restore all settings from defaults, a backup or skin file.', 'wppa');
						$help = esc_js(__('Restores all the settings from the factory supplied defaults, the backup you created or from a skin file.', 'wppa'));
						$slug1 = 'wppa_skinfile';
						$slug2 = 'wppa_load_skin';
						$files = glob(WPPA_PATH.'/theme/*.skin');
						
						$options = false;
						$values = false;
						$options[] = __('--- set to defaults ---', 'wppa');
						$values[] = 'default';
						if (is_file(WPPA_DEPOT_PATH.'/settings.bak')) {
							$options[] = __('--- restore backup ---', 'wppa');
							$values[] = 'restore';
						}
						if ( count($files) ) {
							foreach ($files as $file) {
								$fname = basename($file);
								$ext = strrchr($fname, '.');
								if ( $ext == '.skin' )  {
									$options[] = $fname;
									$values[] = $file;
								}
							}
						}
						
						$html1 = wppa_select($slug1, $options, $values);
						$html2 = wppa_radio('wppa_action', $slug2);
						wppa_setting_2($slug1, $slug2, '5', $name, $desc, $html1, $html2, $help);

						$name = __('Regenerate', 'wppa');
						$desc = __('Regenerate all thumbnails.', 'wppa');
						$help = esc_js(__('Regenerate all thumbnails.', 'wppa'));
						$slug = 'wppa_regen';
						$html = wppa_radio('wppa_action', $slug);
						wppa_setting_2('', $slug, '7', $name, $desc, '', $html, $help);

						$name = __('Rerate', 'wppa');
						$desc = __('Recalculate ratings.', 'wppa');
						$help = esc_js(__('This function will recalculate all mean photo ratings from the ratings table.', 'wppa'));
						$help .= '\n'.esc_js(__('You may need this function after the re-import of previously exported photos', 'wppa'));
						$slug = 'wppa_rerate';
						$html = wppa_radio('wppa_action', $slug);
						wppa_setting_2('', $slug, '8', $name, $desc, '', $html, $help);

						$wppa['no_default'] = false;

						?>
					</tbody>
					<tfoot style="font-weight: bold;" class="wppa_table_8">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Specification', 'wppa') ?></th>
							<th scope="col"><?php _e('Do it!', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</tfoot>
				</table>
			</div>
				
			<?php // Table 9: Miscellaneous ?>
			<h3><?php _e('Table IX:', 'wppa'); echo(' '); _e('Miscellaneous:', 'wppa'); ?><?php wppa_toggle_table(9) ?>
				<span style="font-weight:normal; font-size:12px;"><?php _e('This table lists all settings that do not fit into an other table', 'wppa'); ?></span>
			</h3>
			
			<div id="wppa_table_9" >
				<table class="widefat">
					<thead style="font-weight: bold; " class="wppa_table_9">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Setting', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</thead>
					<tbody class="wppa_table_9">
						<?php
						if ( is_multisite() && get_option('wppa_multisite', 'no') != 'yes' ) {
							$name = __('Enable WPPA+ multisite', 'wppa');
							$desc = __('Check this box to setup WPPA+ for a multisite wp installation', 'wppa');
							$n_photos = $wpdb->get_var('SELECT COUNT(*) FROM '.WPPA_PHOTOS);
							$help = esc_js(sprintf(__('This site is a part of a multisite WP installation. It still contains %s photos in single site mode.', 'wppa'), $n_photos));
							$help .= '\n\n'.esc_js(__('If you want to keep those photos, use Photo Albums -> Export to save them.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If you saved them already, or if they may be lost, check the Enable WPPA+ multisite checkbox in Table IX item 0 and press Save Changes.', 'wppa'));
							$help .= '\n\n'.esc_js(__('This will DISCARD THE EXISTING PHOTOS and enable this site in multisite mode.', 'wppa'));
							$slug = 'wppa_multisite';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '0', $name, $desc, $html, $help);
						}

						$name = __('Arrow color', 'wppa');
						$desc = __('Left/right browsing arrow color.', 'wppa');
						$help = esc_js(__('Enter the color of the navigation arrows.', 'wppa'));
						$slug = 'wppa_arrow_color';
						$html = wppa_input($slug, '70px', '', '');
						wppa_setting($slug, '1', $name, $desc, $html, $help);
						
						$name = __('Search page', 'wppa');
						$desc = __('Display the search results on page.', 'wppa');
						$help = esc_js(__('Select the page to be used to display search results. The page MUST contain %%wppa%%.', 'wppa'));
						$help .= '\n'.esc_js(__('You may give it the title "Search results" or something alike.', 'wppa'));
						$help .= '\n'.esc_js(__('Or you ou may use the standard page on which you display the generic album.', 'wppa'));
						$slug = 'wppa_search_linkpage';
						$query = "SELECT ID, post_title FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish' ORDER BY post_title ASC";
						$pages = $wpdb->get_results ($query, 'ARRAY_A');
						$options = false;
						$values = false;
						if ($pages) {
							foreach ($pages as $page) {
								$options[] = $page['post_title'];
								$values[] = $page['ID'];
							}
						}
						$html = wppa_select($slug, $options, $values);
						wppa_setting($slug, '2', $name, $desc, $html, $help);
						
						$name = __('Exclude separate', 'wppa');
						$desc = __('Do not search \'separate\' albums.', 'wppa');
						$help = esc_js(__('When checked, albums (and photos in them) that have the parent set to --- separate --- will be excluded from being searched.', 'wppa'));
						$slug = 'wppa_excl_sep';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '3', $name, $desc, $html, $help);
						
						$name = __('Allow HTML', 'wppa');
						$desc = __('Allow HTML in album and photo descriptions.', 'wppa');
						$help = esc_js(__('If checked: html is allowed. WARNING: No checks on syntax, it is your own responsability to close tags properly!', 'wppa'));
						$slug = 'wppa_html';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '4', $name, $desc, $html, $help);
						
						$name = __('Allow WPPA+ Debugging', 'wppa');
						$desc = __('Allow the use of &debug=.. in urls to this site.', 'wppa');
						$help = esc_js(__('If checked: appending (?)(&)debug or (?)(&)debug=<int> to an url to this site will generate the display of special WPPA+ diagnostics, as well as php warnings', 'wppa'));
						$slug = 'wppa_allow_debug';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '5', $name, $desc, $html, $help);
						
						?>
							<tr style="color:#333">
								<td>6</td>
								<td colspan="4"><?php echo __('The following lines represent the sequence order of the slideshow components', 'wppa') ?></td>
							</tr>
						<?php
						
						$indexopt = get_option('wppa_slide_order');
						$indexes  = explode(',', $indexopt);
						$names    = array(
							__('StartStop', 'wppa'), 
							__('SlideFrame', 'wppa'), 
							__('NameDesc', 'wppa'), 
							__('Custom', 'wppa'), 
							__('Rating', 'wppa'), 
							__('FilmStrip', 'wppa'), 
							__('Browsebar', 'wppa'), 
							__('Comments', 'wppa'));
						$enabled  = '<span style="color:green; float:right;">'.__('(Enabled)', 'wppa').'</span>';
						$disabled = '<span style="color:orange; float:right;">'.__('(Disabled)', 'wppa').'</span>';
						$descs = array(
							__('Start/Stop & Slower/Faster navigation bar', 'wppa') . ( $wppa_opt['wppa_show_startstop_navigation'] == 'yes' ? $enabled : $disabled ),
							__('The Slide Frame', 'wppa') . '<span style="float:right;">'.__('(Always)', 'wppa').'</span>',
							__('Photo Name & Description Box', 'wppa') . ( ( $wppa_opt['wppa_show_full_name'] == 'yes' || $wppa_opt['wppa_show_full_desc'] == 'yes' ) ? $enabled : $disabled ),
							__('Custom Box', 'wppa') . ( $wppa_opt['wppa_custom_on'] == 'yes' ? $enabled : $disabled ),
							__('Rating Bar', 'wppa') . ( $wppa_opt['wppa_rating_on'] == 'yes' ? $enabled : $disabled ),
							__('Film Strip with embedded Start/Stop and Goto functionality', 'wppa') . ( $wppa_opt['wppa_filmstrip'] == 'yes' ? $enabled : $disabled ),
							__('Browse Bar with Photo X of Y counter', 'wppa') . ( $wppa_opt['wppa_show_browse_navigation'] == 'yes' ? $enabled : $disabled ),
							__('Comments Box', 'wppa') . ( $wppa_opt['wppa_show_comments'] == 'yes' ? $enabled : $disabled )
							);
						$i = '0';
						while ( $i < '8' ) {
							$name = $names[$indexes[$i]];
							$desc = $descs[$indexes[$i]];
							$html = $i == '0' ? '' : wppa_button(__('Move Up', 'wppa'), 'wppa_move_up('.$i.')' );
							$help = '';
							wppa_setting($slug, '6.'.$indexes[$i] , $name, $desc, $html, $help);
							$i++;
						}
						?>		

						<script type="text/javascript">wppa_moveup_url = "<?php echo wppa_dbg_url(get_admin_url().'admin.php?page=wppa_options&move_up=') ?>";</script>

						<?php
						$name = __('Swap Namedesc', 'wppa');
						$desc = __('Swap the order sequence of name and description', 'wppa');
						$help = '';
						$slug = 'wppa_swap_namedesc';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '6.9', $name, $desc, $html, $help);
						
						$name = __('New Album', 'wppa');
						$desc = __('Maximum time an album is indicated as New!', 'wppa');
						$help = '';
						$slug = 'wppa_max_album_newtime';
						$options = array( __('--- off ---', 'wppa'), __('One hour', 'wppa'), __('One day', 'wppa'), __('One week', 'wppa'), __('One month', 'wppa') );
						$values = array( 0, 60*60, 60*60*24, 60*60*24*7, 60*60*24*30);
						$html = wppa_select($slug, $options, $values);
						wppa_setting($slug, '7', $name, $desc, $html, $help);

						$name = __('New Photo', 'wppa');
						$desc = __('Maximum time a photo is indicated as New!', 'wppa');
						$help = '';
						$slug = 'wppa_max_photo_newtime';
						$options = array( __('--- off ---', 'wppa'), __('One hour', 'wppa'), __('One day', 'wppa'), __('One week', 'wppa'), __('One month', 'wppa') );
						$values = array( 0, 60*60, 60*60*24, 60*60*24*7, 60*60*24*30);
						$html = wppa_select($slug, $options, $values);
						wppa_setting($slug, '8', $name, $desc, $html, $help);
						
						$name = __('WPPA+ Lightbox', 'wppa');
						$desc = __('Use wppa+ embedded lightbox.', 'wppa');
						$help = esc_js(__('WPPA+ comes with embedded lightbox 2. If you want to use a different ligtbox (plugin)', 'wppa'));
						$help .= ' '.esc_js(__('or you do not use lightbox links, you may uncheck this item.', 'wppa'));
						$help .= '\n\n'.esc_js(__('If you uncheck this item you can also no longer set lightbox configuration settings.', 'wppa'));
						$help .= '\n\n'.esc_js(__('If you check this item, the wp supplied scripts prototype and scriptaculous are also being loaded.', 'wppa'));
						$slug = 'wppa_use_lightbox';
						$onchange = 'wppaCheckLightbox()';
						$html = wppa_checkbox($slug, $onchange);
						wppa_setting($slug, '9', $name, $desc, $html, $help);
						
						$name = __('WPPA+ Filter priority', 'wppa');
						$desc = __('Sets the priority of the wppa+ content filter.', 'wppa');
						$help = esc_js(__('If you encounter conflicts with the theme or other plugins, increasing this value sometimes helps. Use with great care!', 'wppa'));
						$slug = 'wppa_filter_priority';
						$html = wppa_input($slug, '50px');
						wppa_setting($slug, '10', $name, $desc, $html, $help);
		
						$name = __('Apply Newphoto desc', 'wppa');
						$desc = __('Give each new photo a standard description.', 'wppa');
						$help = esc_js(__('If checked, each new photo will get the description (template) as specified in the next item.', 'wppa'));
						$slug = 'wppa_apply_newphoto_desc';
						$html = wppa_checkbox($slug);
						wppa_setting($slug, '11', $name, $desc, $html, $help);

						$wppa['no_default'] = true;
						
						$name = __('New photo desc', 'wppa');
						$desc = __('The description (template) to add to a new photo.', 'wppa');
						$help = esc_js(__('Enter the default description.', 'wppa'));
						$help .= '\n\n'.esc_js(__('If yuo use html, please check item 4 of this table.', 'wppa'));
						$slug = 'wppa_newphoto_description';
						$html = wppa_textarea($slug);
						wppa_setting($slug, '12', $name, $desc, $html, $help);

						$wppa['no_default'] = false;
						
						?>
					</tbody>
					<tfoot style="font-weight: bold;" class="wppa_table_9">
						<tr>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('Name', 'wppa') ?></th>
							<th scope="col"><?php _e('Description', 'wppa') ?></th>
							<th scope="col"><?php _e('Setting', 'wppa') ?></th>
							<th scope="col"><?php _e('Help', 'wppa') ?></th>
						</tr>
					</tfoot>
				</table>
			</div>
			
			<?php // Table 10: Php configuration ?>
			<h3><?php _e('Table X:', 'wppa'); echo(' '); _e('WPPA+ and PHP Configuration:', 'wppa'); ?><?php wppa_toggle_table(10) ?>
				<span style="font-weight:normal; font-size:12px;"><?php _e('This table lists all WPPA+ constants and PHP server configuration parameters and is read only', 'wppa'); ?></span>
			</h3>

			<div id="wppa_table_10" >
				<div class="wppa_table_10" style="margin-top:20px; text-align:left; ">
					<table class="widefat">
						<thead style="font-weight: bold; " class="wppa_table_9">
							<tr>
								<th scope="col"><?php _e('Name', 'wppa') ?></th>
								<th scope="col"><?php _e('Description', 'wppa') ?></th>
								<th scope="col"><?php _e('Value', 'wppa') ?></th>
							</tr>
						<tbody class="wppa_table_10">
							<tr style="color:#333;">
								<td>WPPA_ALBUMS</td>
								<td><small><?php _e('Albums db table name.', 'wppa') ?></small></td>
								<td><?php echo($wpdb->prefix . 'wppa_albums') ?></td>
							</tr>
							<tr style="color:#333;">
								<td>WPPA_PHOTOS</td>
								<td><small><?php _e('Photos db table name.', 'wppa') ?></small></td>
								<td><?php echo(WPPA_PHOTOS) ?></td>
							</tr>
							<tr style="color:#333;">
								<td>WPPA_RATING</td>
								<td><small><?php _e('Rating db table name.', 'wppa') ?></small></td>
								<td><?php echo(WPPA_RATING) ?></td>
							</tr>
							<tr style="color:#333;">
								<td>WPPA_COMMENTS</td>
								<td><small><?php _e('Comments db table name.', 'wppa') ?></small></td>
								<td><?php echo(WPPA_COMMENTS) ?></td>
							</tr>
							<tr style="color:#333;">
								<td>WPPA_FILE</td>
								<td><small><?php _e('Plugins main file name.', 'wppa') ?></small></td>
								<td><?php echo(WPPA_FILE) ?></td>
							</tr>
							<tr style="color:#333;">
								<td>WPPA_PATH</td>
								<td><small><?php _e('Path to plugins directory.', 'wppa') ?></small></td>
								<td><?php echo(WPPA_PATH) ?></td>
							</tr>
							<tr style="color:#333;">
								<td>WPPA_NAME</td>
								<td><small><?php _e('Plugins directory name.', 'wppa') ?></small></td>
								<td><?php echo(WPPA_NAME) ?></td>
							</tr>
							<tr style="color:#333;">
								<td>WPPA_URL</td>
								<td><small><?php _e('Plugins directory url.', 'wppa') ?></small></td>
								<td><?php echo(WPPA_URL) ?></td>
							</tr>
							<tr style="color:#333;">
								<td>WPPA_UPLOAD</td>
								<td><small><?php _e('The relative upload directory.', 'wppa') ?></small></td>
								<td><?php echo(WPPA_UPLOAD) ?></td>
							</tr>
							<tr style="color:#333;">
								<td>WPPA_UPLOAD_PATH</td>
								<td><small><?php _e('The upload directory path.', 'wppa') ?></small></td>
								<td><?php echo(WPPA_UPLOAD_PATH) ?></td>
							</tr>
							<tr style="color:#333;">
								<td>WPPA_UPLOAD_URL</td>
								<td><small><?php _e('The upload directory url.', 'wppa') ?></small></td>
								<td><?php echo(WPPA_UPLOAD_URL) ?></td>
							</tr>
							<tr style="color:#333;">
								<td>WPPA_DEPOT</td>
								<td><small><?php _e('The relative depot directory.', 'wppa') ?></small></td>
								<td><?php echo(WPPA_DEPOT) ?></td>
							</tr>
							<tr style="color:#333;">
								<td>WPPA_DEPOT_PATH</td>
								<td><small><?php _e('The depot directory path.', 'wppa') ?></small></td>
								<td><?php echo(WPPA_DEPOT_PATH) ?></td>
							</tr>
							<tr style="color:#333;">
								<td>WPPA_DEPOT_URL</td>
								<td><small><?php _e('The depot directory url.', 'wppa') ?></small></td>
								<td><?php echo(WPPA_DEPOT_URL) ?></td>
							</tr>
						</tbody>
					</table>
					<?php if ( $wppa_opt['wppa_allow_debug'] == 'yes' ) phpinfo(-1); else phpinfo(4); ?>
				</div>
			</div>
		</form>
		<script type="text/javascript">wppaInitSettings();</script>
	</div>
	
<?php
}

function wppa_setting($slug, $num, $name, $desc, $html, $help, $cls = '') {
global $wppa_status;
global $wppa_defaults;

	$result = "\n";
	$result .= '<tr';
	if ($cls != '') $result .= ' class="'.$cls.'"';
	$result .= ' style="color:#333;"';
	$result .= '>';
	
	$result .= '<td>'.$num.'</td>';
	$result .= '<td>'.$name.'</td>';
	$result .= '<td><small>'.$desc.'</small></td>';
	$result .= '<td><span class="'.$cls.'_html">'.$html.'</span></td>';
		
	if ( $help ) $hlp = $name.':\n\n'.$help.wppa_dflt('', $slug);
	else $hlp = __('No help available', 'wppa');

	$color = 'black';
	$char = '?';
	$fw = $wppa_defaults[$slug] == get_option($slug) ? 'normal' : 'bold';
	$title = __('Click for help', 'wppa');
	if (isset($wppa_status[$slug])) { 
		switch ($wppa_status[$slug]) {
			case '1':				// modified
				$color = 'green';
				$char = '!';
				$title = __('You just modified this setting', 'wppa');
				break;
			case '2':				// error
				$color = 'red';
				$char = '!';
				$title = __('You just tried to modify this setting into an illegal value', 'wppa');
				break;
			default:
				$color = 'black';
				$char = '?';
				break;
		}
	}
	
//	$result .= '<td><a style="color: '.$color.';text-decoration: none; font-weight: '.$fw.'; cursor: pointer;" title="'.$title.'" onclick="alert('."'".$hlp."'".')">'.$char.'</a></td>';
	$result .= '<td><input type="button" style="font_size: 11px; margin: 0px; padding: 0px; color: '.$color.';text-decoration: none; font-weight: '.$fw.'; cursor: pointer;" title="'.$title.'" onclick="alert('."'".$hlp."'".')" value="'.$char.'"></td>';
	
	$result .= '</tr>';
	
	echo $result;
}
	
function wppa_setting_2($slug1, $slug2, $num, $name, $desc, $html1, $html2, $help, $cls = '') {
global $wppa_status;
global $wppa_defaults;

	$result = "\n";
	$result .= '<tr';
	if ($cls != '') $result .= ' class="'.$cls.'"';
	$result .= ' style="color:#333;"';
	$result .= '>';
	
	$result .= '<td>'.$num.'</td>';
	$result .= '<td>'.$name.'</td>';
	$result .= '<td><small>'.$desc.'</small></td>';
	$result .= '<td><span class="'.$cls.'_html">'.$html1.'</span></td>';
	$result .= '<td><span class="'.$cls.'_html">'.$html2.'</span></td>';
	
	$hlp = $name.':\n\n'.$help.wppa_dflt('1.', $slug1).wppa_dflt('2.', $slug2);

	$color = 'black';
	$char = '?';
	
	if ($slug1 != '') $fw = ($wppa_defaults[$slug1] == get_option($slug1)) /* && ($wppa_defaults[$slug2] == get_option($slug2)) */ ? 'normal' : 'bold';
	else $fw = 'normal';
	
	$title = __('Click for help', 'wppa');
	$status = '0'; $stat1 = '0'; $stat2 = '0';
	if (isset($wppa_status[$slug1])) $stat1 = $wppa_status[$slug1];
	if (isset($wppa_status[$slug2])) $stat2 = $wppa_status[$slug2];
	if ($stat1 > $status) $status = $stat1;
	if ($stat2 > $status) $status = $stat2;
		
	switch ($status) {
		case '1':				// modified
			$color = 'green';
			$char = '!';
			$title = __('You just modified this setting', 'wppa');
			break;
		case '2':				// error
			$color = 'red';
			$char = '!';
			$title = __('You just tried to modify this setting into an illegal value', 'wppa');
			break;
		default:
			$color = 'black';
			$char = '?';
			break;
	}
	
//	$result .= '<td><a style="color: '.$color.';text-decoration: none; font-weight: '.$fw.'; cursor: pointer;" title="'.$title.'" onclick="alert('."'".$hlp."'".')">'.$char.'</a></td>';
	$result .= '<td><input type="button" style="font_size: 11px; margin: 0px; padding: 0px; color: '.$color.';text-decoration: none; font-weight: '.$fw.'; cursor: pointer;" title="'.$title.'" onclick="alert('."'".$hlp."'".')" value="'.$char.'"></td>';
	
	$result .= '</tr>';
	
	echo $result;
}

function wppa_setting_3($slug1, $slug2, $slug3, $num, $name, $desc, $html1, $html2, $html3, $help, $cls = '') {
global $wppa_status;
global $wppa_defaults;

	$result = "\n";
	$result .= '<tr';
	if ($cls != '') $result .= ' class="'.$cls.'"';
	$result .= ' style="color:#333;"';
	$result .= '>';
	
	$result .= '<td>'.$num.'</td>';
	$result .= '<td>'.$name.'</td>';
	$result .= '<td><small>'.$desc.'</small></td>';
	$result .= '<td><span class="'.$cls.'_html">'.$html1.'</span></td>';
	$result .= '<td><span class="'.$cls.'_html">'.$html2.'</span></td>';
	$result .= '<td><span class="'.$cls.'_html">'.$html3.'</span></td>';
	
	$hlp = $name.':\n\n'.$help.wppa_dflt('1.', $slug1).wppa_dflt('2.', $slug2).wppa_dflt('3.', $slug3);

	$color = 'black';
	$char = '?';
	$fw = ($wppa_defaults[$slug1] == get_option($slug1)) && ($wppa_defaults[$slug2] == get_option($slug2)) && ($wppa_defaults[$slug3] == get_option($slug3)) ? 'normal' : 'bold';
	$title = __('Click for help', 'wppa');
	$status = '0'; $stat1 = '0'; $stat2 = '0'; $stat3 = '0';
	if (isset($wppa_status[$slug1])) $stat1 = $wppa_status[$slug1];
	if (isset($wppa_status[$slug2])) $stat2 = $wppa_status[$slug2];
	if (isset($wppa_status[$slug3])) $stat3 = $wppa_status[$slug3];
	if ($stat1 > $status) $status = $stat1;
	if ($stat2 > $status) $status = $stat2;
	if ($stat3 > $status) $status = $stat3;
		
	switch ($status) {
		case '1':				// modified
			$color = 'green';
			$char = '!';
			$title = __('You just modified this setting', 'wppa');
			break;
		case '2':				// error
			$color = 'red';
			$char = '!';
			$title = __('You just tried to modify this setting into an illegal value', 'wppa');
			break;
		default:
			$color = 'black';
			$char = '?';
			break;
	}
	
//	$result .= '<td><a style="color: '.$color.';text-decoration: none; font-weight: '.$fw.'; cursor: pointer;" title="'.$title.'" onclick="alert('."'".$hlp."'".')">'.$char.'</a></td>';
	$result .= '<td><input type="button" style="font_size: 11px; margin: 0px; padding: 0px; color: '.$color.';text-decoration: none; font-weight: '.$fw.'; cursor: pointer;" title="'.$title.'" onclick="alert('."'".$hlp."'".')" value="'.$char.'"></td>';
	
	$result .= '</tr>';
	
	echo $result;
}

function wppa_update_numeric($slug, $minval, $target, $maxval = '') {
global $options_error;
global $wppa_status;

	$value = $_POST[$slug];
	if (wppa_check_numeric($value, $minval, $target, $maxval)) {
		wppa_update($slug, $value);
	}
	else {
		$wppa_status[$slug] = '2';
		$options_error = true;
	}
}

function wppa_update_value($slug) {
	if (isset($_POST[$slug])) {
		$value = $_POST[$slug];
		wppa_update($slug, $value);
	}
}

function wppa_update_textarea($slug) {
	if (isset($_POST[$slug])) {
		$value = html_entity_decode($_POST[$slug]);
		wppa_update($slug, $value);
	}
}

function wppa_update_check($slug) {
	if (isset($_POST[$slug])) wppa_update($slug, 'yes');
	else wppa_update($slug, 'no');
}

function wppa_update($slug, $value) {
global $wppa_status;

	$oldval = get_option($slug, 'nil');
	if ($oldval == $value) { 		// Not modified
		$wppa_status[$slug] = '0';
	}
	else {							// Modified
		update_option($slug, trim($value));
		$wppa_status[$slug] = '1';
	}
}

function wppa_input($slug, $width, $minwidth = '', $text = '', $onchange = '') {

	$html = '<input style="width: '.$width.';';
	if ($minwidth != '') $html .= ' min-width:'.$minwidth.';';
	$html .= ' font_size: 11px; margin: 0px; padding: 0px;" type="text" name="'.$slug.'" id="'.$slug.'"';
	if ($onchange != '') $html .= ' onchange="'.$onchange.'"';
	$html .= ' value="'.stripslashes(get_option($slug)).'" />'.$text;
	
	return $html;
}

function wppa_textarea($slug) {
	$html = '<textarea name="'.$slug.'" id="'.$slug.'" style="width:500px;" >';
	$html .= htmlspecialchars(stripslashes(get_option($slug)));
	$html .= '</textarea>';
	
	return $html;
}

function wppa_checkbox($slug, $onchange = '') {

	$html = '<input style="height: 15px; margin: 0px; padding: 0px;" type="checkbox" name="'.$slug.'" id="'.$slug.'"'; 
	if (get_option($slug) == 'yes') $html .= ' checked="checked"';
	if ($onchange != '') $html .= ' onchange="'.$onchange.'"';
	$html .= ' />';
	
	return $html;
}

function wppa_radio($slug, $value, $onchange = '') {

	$html = '<input style="height: 15px; margin: 0px; padding: 0px;" type="radio" name="'.$slug.'" id="'.$slug.'" value="'.$value.'"'; 
//	if (get_option($slug) == 'yes') $html .= ' checked="checked"';
	if ($onchange != '') $html .= ' onchange="'.$onchange.'"';
	$html .= ' />';
	
	return $html;
}

function wppa_select($slug, $options, $values, $onchange = '', $class = '') {

	if (!is_array($options)) {
		$html = __('There are no pages (yet) to link to.', 'wppa');
		return $html;
	}
	
	$html = '<select style="font_size: 11px; height: 20px; margin: 0px; padding: 0px;" name="'.$slug.'" id="'.$slug.'"';
	if ($onchange != '') $html .= ' onchange="'.$onchange.'"';
	if ($class != '') $html .= ' class="'.$class.'"';
	$html .= '>';
	
	$val = get_option($slug);
	$idx = 0;
	$cnt = count($options);
	while ($idx < $cnt) {
		$html .= "\n";
		$html .= '<option value="'.$values[$idx].'" '; 
		if ($val == $values[$idx]) $html .= ' selected="selected"'; 
		$html .= '>'.$options[$idx].'</option>';
		$idx++;
	}
	$html .= '</select>';
	
	return $html;
}

function wppa_button($text, $onclick) {

	$html = '<input style="font_size: 11px; height: 20px; margin: 0px; padding: 0px;" type="button" value="'.$text.'"'; 
	if ($onclick != '') $html .= ' onclick="'.$onclick.'"';
	$html .= ' />';

	return $html;
}

function wppa_dflt($n = '', $slug) {
global $wppa_defaults;
global $wppa;

	if ($slug == '') return '';
	if ($wppa['no_default']) return '';
	
	$dflt = $wppa_defaults[$slug];
	if ($dflt == get_option($slug)) return '\n\n'.$n.' '.esc_js(__('This value is set to the default.', 'wppa'));

	switch ($dflt) {
		case 'none': $dft = __('no link at all.', 'wppa'); break;
		case 'file': $dft = __('the plain photo (file).', 'wppa'); break;
		case 'photo': $dft = __('the full size photo in a slideshow.', 'wppa'); break;
		case 'single': $dft = __('the fullsize photo on its own.', 'wppa'); break;
		case 'indiv': $dft = __('the photo specific link.', 'wppa'); break;
		case 'album': $dft = __('the content of the album.', 'wppa'); break;
		case 'widget': $dft = __('defined at widget activation.', 'wppa'); break;
		case 'custom': $dft = __('defined on widget admin page.', 'wppa'); break;
		case 'same': $dft = __('same as title.', 'wppa'); break;
		default: $dft = $dflt;
	}

	return '\n\n'.$n.' '.esc_js(__('The default for this setting is:', 'wppa').' \''.$dft.'\'.');
}

function wppa_color_box($slug) {
global $wppa_opt;

	return '<div id="colorbox-' . $slug . '" style="width:100px; height:16px; background-color:' . $wppa_opt[$slug] . '; border:1px solid #dfdfdf;" ></div>';

}

function wppa_toggle_table($i) {
?>
	<input type="button" value="<?php _e('Hide', 'wppa') ?>" onclick="wppaHideTable('<?php echo($i) ?>');" id="wppa_tableHide-<?php echo($i) ?>" />
	<input type="button" value="<?php _e('Show', 'wppa') ?>" onclick="wppaShowTable('<?php echo($i) ?>');" id="wppa_tableShow-<?php echo($i) ?>" />
	<input type="submit" class="button-primary" name="wppa_set_submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
<?php
}