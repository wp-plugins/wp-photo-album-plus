<?php 
/* wppa-setup.php
* Package: wp-photo-album-plus
*
* Contains all the setup stuff
* Version 5.3.10
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

/* SETUP */
// It used to be: register_activation_hook(WPPA_FILE, 'wppa_setup');
// The activation hook is useless since wp does no longer call this hook after upgrade of the plugin
// this routine is now called at action admin_init, so also after initial install
// Additionally it can now output messages about success or failure
// Just for people that rely on the healing effect of de-activating and re-activating a plugin
// we still do a setup on activation by faking that we are not up yo rev, and so invoking
// the setup on the first admin_init event. This has the advantage that we can display messages
// instead of characters of unexpected output.
// register_activation_hook(WPPA_FILE, 'wppa_activate_plugin'); is in wppa.php
function wppa_activate_plugin() {
	$old_rev = get_option('wppa_revision', '100');
	$new_rev = $old_rev - '0.01';
	wppa_update_option('wppa_revision', $new_rev);
}
// Set force to true to re-run it even when on rev (happens in wppa-settings.php)
// Force will NOT redefine constants
function wppa_setup($force = false) {
global $silent;
	global $wpdb;
	global $wppa_revno;
	global $current_user;
	global $wppa;
	
	$old_rev = get_option('wppa_revision', '100');

	if ( $old_rev >= $wppa_revno && ! $force ) return; // Nothing to do here
	
	wppa_clear_cache(true);	// Clear cache
	
	$wppa['error'] = false;	// Init no error
		
	$create_albums = "CREATE TABLE " . WPPA_ALBUMS . " (
					id bigint(20) NOT NULL, 
					name text NOT NULL, 
					description text NOT NULL, 
					a_order smallint(5) NOT NULL, 
					main_photo bigint(20) NOT NULL, 
					a_parent bigint(20) NOT NULL,
					p_order_by smallint(5) NOT NULL,
					cover_linktype tinytext NOT NULL,
					cover_linkpage bigint(20) NOT NULL,
					owner text NOT NULL,
					timestamp tinytext NOT NULL,
					upload_limit tinytext NOT NULL,
					alt_thumbsize tinytext NOT NULL,
					default_tags tinytext NOT NULL,
					cover_type tinytext NOT NULL,
					suba_order_by tinytext NOT NULL,
					views bigint(20) NOT NULL default '0',
					cats tinytext NOT NULL,
					scheduledtm tinytext NOT NULL,
					PRIMARY KEY  (id)
					) DEFAULT CHARACTER SET utf8;";
					
	$create_photos = "CREATE TABLE " . WPPA_PHOTOS . " (
					id bigint(20) NOT NULL, 
					album bigint(20) NOT NULL, 
					ext tinytext NOT NULL, 
					name text NOT NULL, 
					description longtext NOT NULL, 
					p_order smallint(5) NOT NULL,
					mean_rating tinytext NOT NULL,
					linkurl text NOT NULL,
					linktitle text NOT NULL,
					linktarget tinytext NOT NULL,
					owner text NOT NULL,
					timestamp tinytext NOT NULL,
					status tinytext NOT NULL,
					rating_count bigint(20) NOT NULL default '0',
					tags tinytext NOT NULL,
					alt tinytext NOT NULL,
					filename tinytext NOT NULL,
					modified tinytext NOT NULL,
					location tinytext NOT NULL,
					views bigint(20) NOT NULL default '0',
					page_id bigint(20) NOT NULL default '0',
					exifdtm tinytext NOT NULL,
					videox smallint(5) NOT NULL default '0',
					videoy smallint(5) NOT NULL default '0',
					scheduledtm tinytext NOT NULL,
					PRIMARY KEY  (id),
					KEY albumkey (album)
					) DEFAULT CHARACTER SET utf8;";

	$create_rating = "CREATE TABLE " . WPPA_RATING . " (
					id bigint(20) NOT NULL,
					timestamp tinytext NOT NULL,
					photo bigint(20) NOT NULL,
					value smallint(5) NOT NULL,
					user text NOT NULL,
					status tinytext NOT NULL,
					PRIMARY KEY  (id),
					KEY photokey (photo)
					) DEFAULT CHARACTER SET utf8;";
					
	$create_comments = "CREATE TABLE " . WPPA_COMMENTS . " (
					id bigint(20) NOT NULL,
					timestamp tinytext NOT NULL,
					photo bigint(20) NOT NULL,
					user text NOT NULL,
					ip tinytext NOT NULL,
					email text NOT NULL,
					comment text NOT NULL,
					status tinytext NOT NULL,
					PRIMARY KEY  (id),
					KEY photokey (photo)
					) DEFAULT CHARACTER SET utf8;";
					
	$create_iptc = "CREATE TABLE " . WPPA_IPTC . " (
					id bigint(20) NOT NULL,
					photo bigint(20) NOT NULL,
					tag tinytext NOT NULL,
					description text NOT NULL,
					status tinytext NOT NULL,
					PRIMARY KEY  (id),
					KEY photokey (photo)
					) DEFAULT CHARACTER SET utf8;";

	$create_exif = "CREATE TABLE " . WPPA_EXIF . " (
					id bigint(20) NOT NULL,
					photo bigint(20) NOT NULL,
					tag tinytext NOT NULL,
					description text NOT NULL,
					status tinytext NOT NULL,
					PRIMARY KEY  (id),
					KEY photokey (photo)
					) DEFAULT CHARACTER SET utf8;";
					
	$create_index = "CREATE TABLE " . WPPA_INDEX . " (
					id bigint(20) NOT NULL,
					slug tinytext NOT NULL,
					albums text NOT NULL,
					photos text NOT NULL,
					PRIMARY KEY  (id),
					KEY slugkey (slug(20))
					) DEFAULT CHARACTER SET utf8;";
					
	$create_session = "CREATE TABLE " . WPPA_SESSION . " (
					id bigint(20) NOT NULL,
					session tinytext NOT NULL,
					timestamp tinytext NOT NULL,
					user tinytext NOT NULL,
					ip tinytext NOT NULL,
					status tinytext NOT NULL,
					data text NOT NULL,
					count bigint(20) NOT NULL default '0',
					PRIMARY KEY  (id),
					KEY sessionkey (session(20))
					) DEFAULT CHARACTER SET utf8;";
					
	require_once WPPA_ABSPATH . 'wp-admin/includes/upgrade.php';
	
	// Create or update db tables
	$tn = array( WPPA_ALBUMS, WPPA_PHOTOS, WPPA_RATING, WPPA_COMMENTS, WPPA_IPTC, WPPA_EXIF, WPPA_INDEX, WPPA_SESSION );
	$tc = array( $create_albums, $create_photos, $create_rating, $create_comments, $create_iptc, $create_exif, $create_index, $create_session );
	$idx = 0;
	while ($idx < 8) {
		$a0 = wppa_table_exists($tn[$idx]);
		dbDelta($tc[$idx]);
		$a1 = wppa_table_exists($tn[$idx]);
		if ( WPPA_DEBUG ) {
			if ( ! $a0 ) {
				if ( $a1 ) wppa_ok_message('Database table '.$tn[$idx].' created.');
				else wppa_error_message('Could not create database table '.$tn[$idx]);
			}
			else wppa_ok_message('Database table '.$tn[$idx].' updated.');
		}
		$idx++;
	}
	
	// Do the things dbdelta does not do.
	// Character set
	wppa_setup_query( "ALTER TABLE " . WPPA_ALBUMS . " MODIFY name text CHARACTER SET utf8");
	wppa_setup_query( "ALTER TABLE " . WPPA_PHOTOS . " MODIFY name text CHARACTER SET utf8");
	wppa_setup_query( "ALTER TABLE " . WPPA_ALBUMS . " MODIFY description text CHARACTER SET utf8");
	wppa_setup_query( "ALTER TABLE " . WPPA_PHOTOS . " MODIFY description longtext CHARACTER SET utf8");
	wppa_setup_query( "ALTER TABLE " . WPPA_PHOTOS . " MODIFY linktitle text CHARACTER SET utf8");
	wppa_setup_query( "ALTER TABLE " . WPPA_COMMENTS . " MODIFY comment text CHARACTER SET utf8");
	wppa_setup_query( "ALTER TABLE " . WPPA_IPTC . " MODIFY description text CHARACTER SET utf8");
	wppa_setup_query( "ALTER TABLE " . WPPA_EXIF . " MODIFY description text CHARACTER SET utf8");
	// Default values
	get_currentuserinfo();
	$user = $current_user->user_login;
	wppa_setup_query($wpdb->prepare( 'UPDATE `'.WPPA_ALBUMS.'` SET `owner` = %s WHERE `owner` = %s', $user, '' ));
	wppa_setup_query($wpdb->prepare( 'UPDATE `'.WPPA_ALBUMS.'` SET `cover_linktype` = %s WHERE `cover_linktype` = %s', 'content', '' ));
	wppa_setup_query($wpdb->prepare( 'UPDATE `'.WPPA_ALBUMS.'` SET `cover_linktype` = %s WHERE `cover_linkpage` = %s', 'none', '-1' ));
	wppa_setup_query($wpdb->prepare( 'UPDATE `'.WPPA_PHOTOS.'` SET `status` = %s WHERE `status` = %s', 'publish', '' ));
	wppa_setup_query($wpdb->prepare( 'UPDATE `'.WPPA_PHOTOS.'` SET `linktarget` = %s WHERE `linktarget` = %s', '_self', '' ));
	wppa_setup_query($wpdb->prepare( 'UPDATE `'.WPPA_ALBUMS.'` SET `upload_limit` = %s WHERE `upload_limit` = %s', '0/0', '' ));
	wppa_setup_query($wpdb->prepare( 'UPDATE `'.WPPA_RATING.'` SET `status` = %s WHERE `status` = %s', 'publish', '' ));

	// Convert any changed and remove obsolete setting options
	if ( $old_rev > '100' ) {	// On update only
		if ( $old_rev <= '402' ) {
			wppa_convert_setting('wppa_coverphoto_left', 'no', 'wppa_coverphoto_pos', 'right');
			wppa_convert_setting('wppa_coverphoto_left', 'yes', 'wppa_coverphoto_pos', 'left');
		}
		if ( $old_rev <= '440' ) {
			wppa_convert_setting('wppa_fadein_after_fadeout', 'yes', 'wppa_animation_type', 'fadeafter');
			wppa_convert_setting('wppa_fadein_after_fadeout', 'no', 'wppa_animation_type', 'fadeover');
		}
		if ( $old_rev <= '450' ) {
			wppa_remove_setting('wppa_fadein_after_fadeout');
			wppa_copy_setting('wppa_show_bbb', 'wppa_show_bbb_widget');
			wppa_convert_setting('wppa_comment_use_gravatar', 'yes', 'wppa_comment_gravatar', 'mm');
			wppa_convert_setting('wppa_comment_use_gravatar', 'no', 'wppa_comment_gravatar', 'none');
			wppa_remove_setting('wppa_comment_use_gravatar');
			wppa_revalue_setting('wppa_start_slide', 'yes', 'run');
			wppa_revalue_setting('wppa_start_slide', 'no', 'still');
			wppa_rename_setting('wppa_accesslevel', 'wppa_accesslevel_admin');
			wppa_remove_setting('wppa_charset');
			wppa_remove_setting('wppa_chmod');
			wppa_remove_setting('wppa_coverphoto_left');
			wppa_remove_setting('wppa_2col_treshold');
			wppa_remove_setting('wppa_album_admin_autosave');
			wppa_remove_setting('wppa_doublethevotes');
			wppa_remove_setting('wppa_halvethevotes');
			wppa_remove_setting('wppa_lightbox_overlaycolor');
			wppa_remove_setting('wppa_lightbox_overlayopacity');
			wppa_remove_setting('wppa_multisite');
			wppa_remove_setting('wppa_set_access_by');
			wppa_remove_setting('wppa_accesslevel_admin');
			wppa_remove_setting('wppa_accesslevel_upload');
			wppa_remove_setting('wppa_accesslevel_sidebar');
		}
		if ( $old_rev <= '452') {
			wppa_copy_setting('wppa_fontfamily_numbar', 'wppa_fontfamily_numbar_active');
			wppa_copy_setting('wppa_fontsize_numbar', 'wppa_fontsize_numbar_active');
			wppa_copy_setting('wppa_fontcolor_numbar', 'wppa_fontcolor_numbar_active');
			wppa_copy_setting('wppa_fontweight_numbar', 'wppa_fontweight_numbar_active');
		}
		if ( $old_rev <= '455') {	// rating_count added to WPPA_PHOTOS
			$phs = $wpdb->get_results( 'SELECT `id` FROM `'.WPPA_PHOTOS.'`', ARRAY_A );
			if ($phs) foreach ($phs as $ph) {
				$cnt = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `'.WPPA_RATING.'` WHERE `photo` = %s', $ph['id']));
				$wpdb->query($wpdb->prepare('UPDATE `'.WPPA_PHOTOS.'` SET `rating_count` = %s WHERE `id` = %s', $cnt, $ph['id']));
			}
		}
		if ( $old_rev < '470' ) {	// single photo re-impl. has its own links, clone from slideshow
			wppa_copy_setting('wppa_slideshow_linktype', 'wppa_sphoto_linktype');
			wppa_copy_setting('wppa_slideshow_blank', 'wppa_sphoto_blank');
			wppa_copy_setting('wppa_slideshow_overrule', 'wppa_sphoto_overrule');
		}
		if ( $old_rev <= '474' ) {	// Convert album and photo descriptions to contain html in stead of htmlspecialchars. Allowing html is assumed, if not permitted, wppa_html will convert to specialcars.
			// html
			$at = 0; $ah = 0; $pt = 0; $ph = 0;
			$albs = $wpdb->get_results('SELECT `id`, `description` FROM '.WPPA_ALBUMS, ARRAY_A);
			if ($albs) {
				foreach($albs as $alb) {
					$at++;
					if (html_entity_decode($alb['description']) != $alb['description']) {
						$wpdb->query($wpdb->prepare('UPDATE `'.WPPA_ALBUMS.'` SET `description` = %s WHERE `id` = %s', html_entity_decode($alb['description']), $alb['id']));
						$ah++;
					}
				}
			}
			$phots = $wpdb->get_results('SELECT `id`, `description` FROM '.WPPA_PHOTOS, ARRAY_A);
			if ($phots) {
				foreach($phots as $phot) {
					$pt++;
					if (html_entity_decode($phot['description']) != $phot['description']) {
						$wpdb->query($wpdb->prepare('UPDATE `'.WPPA_PHOTOS.'` SET `description` = %s WHERE `id` = %s', html_entity_decode($phot['description']), $phot['id']));
						$ph++;
					}
				}
			}	
			if ( WPPA_DEBUG ) if ($ah || $ph) wppa_ok_message($ah.' out of '.$at.' albums and '.$ph.' out of '.$pt.' photos html converted');
		}
		if ( $old_rev <= '482' ) {	// Share box added
			$so = get_option('wppa_slide_order', '0,1,2,3,4,5,6,7,8,9');
			if ( strlen($so) == '19' ) {
				wppa_update_option('wppa_slide_order', $so.',10');
			}
			$so = get_option('wppa_slide_order_split', '0,1,2,3,4,5,6,7,8,9,10');
			if ( strlen($so) == '22' ) {
				wppa_update_option('wppa_slide_order_split', $so.',11');
			}
			wppa_remove_setting('wppa_sharetype');
			wppa_copy_setting('wppa_bgcolor_namedesc', 'wppa_bgcolor_share');
			wppa_copy_setting('wppa_bcolor_namedesc', 'wppa_bcolor_share');

		}
		if ( $old_rev <= '4811' ) {
			wppa_rename_setting('wppa_comment_count', 'wppa_comten_count');
			wppa_rename_setting('wppa_comment_size', 'wppa_comten_size');
		}
		if ( $old_rev <= '4910' ) {
			wppa_copy_setting('wppa_show_bread', 'wppa_show_bread_posts');
			wppa_copy_setting('wppa_show_bread', 'wppa_show_bread_pages');
			wppa_remove_setting('wppa_show_bread');
		}
		if ( $old_rev <= '5000' ) {
			wppa_remove_setting('wppa_autoclean');
		}
		if ( $old_rev <= '5010' ) {
			wppa_copy_setting('wppa_apply_newphoto_desc', 'wppa_apply_newphoto_desc_user');
		}
		if ( $old_rev <= '5107' ) {
			delete_option('wppa_taglist'); 	// Forces recreation
		}
		if ( $old_rev <= '5205' ) {
			if ( get_option('wppa_list_albums_desc', 'nil') == 'yes' ) {
				$value = get_option('wppa_list_albums_by', '0') * '-1';
				wppa_update_option('wppa_list_albums_by', $value);
				wppa_remove_setting('wppa_list_albums_desc');
			}
			if ( get_option('wppa_list_photos_desc', 'nil') == 'yes' ) {
				$value = get_option('wppa_list_photos_by', '0') * '-1';
				wppa_update_option('wppa_list_photos_by', $value);
				wppa_remove_setting('wppa_list_photos_desc');
			}
		}

		if ( $old_rev <= '5207' ) {
			if ( get_option( 'wppa_strip_file_ext', 'nil' ) == 'yes' ) {
				wppa_update_option( 'wppa_newphoto_name_method', 'noext' );
				delete_option( 'wppa_strip_file_ext' );
			}
		}

		if ( $old_rev <= '5307' ) {
			$wpdb->query( "TRUNCATE TABLE `".WPPA_SESSION."`" );
		}
		
		if ( $old_rev <= '5308' ) {
			wppa_flush_treecounts();
		}
		
	}
	
	// Set Defaults
	wppa_set_defaults();
	
	// Check required directories
	if ( ! wppa_check_dirs() ) $wppa['error'] = true;
		
	// Copy factory supplied watermarks
	$frompath = WPPA_PATH . '/watermarks';
	$watermarks = glob($frompath . '/*.png');
	if ( is_array($watermarks) ) {
		foreach ($watermarks as $fromfile) {
			$tofile = WPPA_UPLOAD_PATH . '/watermarks/' . basename($fromfile);
			@ copy($fromfile, $tofile);
		}
	}

	// Copy factory supplied watermark fonts
	$frompath = WPPA_PATH . '/fonts';
	$fonts = glob($frompath . '/*');
	if ( is_array($fonts) ) {
		foreach ($fonts as $fromfile) {
			if ( is_file ( $fromfile ) ) {
				$tofile = WPPA_UPLOAD_PATH . '/fonts/' . basename($fromfile);
				@ copy($fromfile, $tofile);
			}
		}
	}

	// Check if this update comes with a new wppa-theme.php and/or a new wppa-style.css
	// If so, produce message
	$key = '0';
	if ( $old_rev < '5200' ) {		// theme changed since...
		$usertheme = get_theme_root().'/'.get_option('template').'/wppa-theme.php';
		if ( is_file( $usertheme ) ) $key += '2';
	}
	if ( $old_rev < '5211' ) {		// css changed since...
		$userstyle = get_theme_root().'/'.get_option('stylesheet').'/wppa-style.css';
		if ( is_file( $userstyle ) ) {
			$key += '1';
		}
		else {
			$userstyle = get_theme_root().'/'.get_option('template').'/wppa-style.css';
			if ( is_file( $userstyle ) ) {
				$key += '1';
			}
		}
	}
	if ( $key ) {
		$msg = '<center>' . __('IMPORTANT UPGRADE NOTICE', 'wppa') . '</center><br/>';
		if ($key == '1' || $key == '3') $msg .= '<br/>' . __('Please CHECK your customized WPPA-STYLE.CSS file against the newly supplied one. You may wish to add or modify some attributes. Be aware of the fact that most settings can now be set in the admin settings page.', 'wppa');
		if ($key == '2' || $key == '3') $msg .= '<br/>' . __('Please REPLACE your customized WPPA-THEME.PHP file by the newly supplied one, or just remove it from your theme directory. You may modify it later if you wish. Your current customized version is NOT compatible with this version of the plugin software.', 'wppa');
		wppa_ok_message($msg);
	}
	
	// Check if db is ok
	if ( ! wppa_check_database() ) $wppa['error'] = true;
	
	// Done!
	if ( ! $wppa['error'] ) {
		$old_rev = round($old_rev); // might be 0.01 off
		if ( $old_rev < $wppa_revno ) { 	// was a real upgrade,
			wppa_update_option('wppa_prevrev', $old_rev);	// Remember prev rev. For support purposes. They say they stay up to rev, but they come from stoneage...
		}
		wppa_update_option('wppa_revision', $wppa_revno);	
		if ( WPPA_DEBUG ) {
			if ( is_multisite() ) {
				wppa_ok_message(sprintf(__('WPPA+ successfully updated in multi site mode to db version %s.', 'wppa'), $wppa_revno));
			}
			else {
				wppa_ok_message(sprintf(__('WPPA+ successfully updated in single site mode to db version %s.', 'wppa'), $wppa_revno));
			}
		}
	}
	else {
		if ( WPPA_DEBUG ) wppa_error_message(__('An error occurred during update', 'wppa'));
	}
}
function wppa_setup_query($query) {
global $wpdb;
global $wppa;
	if ( $wpdb->query($query) === false ) $wppa['error'] = true;
}
function wppa_convert_setting($oldname, $oldvalue, $newname, $newvalue) {
	if ( get_option($oldname, 'nil') == 'nil' ) return;	// no longer exists
	if ( get_option($oldname, 'nil') == $oldvalue ) wppa_update_option($newname, $newvalue);
}
function wppa_remove_setting($oldname) {
	if ( get_option($oldname, 'nil') != 'nil' ) delete_option($oldname);
}
function wppa_rename_setting($oldname, $newname) {
	if ( get_option($oldname, 'nil') == 'nil' ) return;	// no longer exists
	wppa_update_option($newname, get_option($oldname));
	delete_option($oldname);
}
function wppa_copy_setting($oldname, $newname) {
	if ( get_option($oldname, 'nil') == 'nil' ) return;	// no longer exists
	wppa_update_option($newname, get_option($oldname));
}
function wppa_revalue_setting($oldname, $oldvalue, $newvalue) {
	if ( get_option($oldname, 'nil') == $oldvalue ) wppa_update_option($oldname, $newvalue);
}

// Set default option values in global $wppa_defaults
// With $force = true, all non default options will be deleted, so everything is set to the default except: revision, rating_max and filesystem
function wppa_set_defaults($force = false) {
global $wppa_defaults;

	$npd = '
<a onClick="jQuery(\'.wppa-dtl\').css(\'display\', \'block\'); jQuery(\'.wppa-more\').css(\'display\', \'none\'); wppaOvlResize();">
<div class="wppa-more">
Camera info
</div>
</a>
<a onClick="jQuery(\'.wppa-dtl\').css(\'display\', \'none\'); jQuery(\'.wppa-more\').css(\'display\', \'block\'); wppaOvlResize();">
<div class="wppa-dtl" style="display:none;" >
Hide Camera info
</div>
</a>
<div class="wppa-dtl" style="display:none;">
<br />
<style>
.wppa-label { padding: 0 3px !important; border: none !important; }
.wppa-value { padding: 0 3px 0 12px !important; border:none !important; }
</style>
<table style="margin:0; border:none;" >
<tr><td class="wppa-label" >Date Time</td><td class="wppa-value" >E#0132</td></tr>
<tr><td class="wppa-label" >Camera</td><td class="wppa-value" >E#0110</td></tr>
<tr><td class="wppa-label" >Focal length</td><td class="wppa-value" >E#920A</td></tr>
<tr><td class="wppa-label" >F-Stop</td><td class="wppa-value" >E#829D</td></tr>
<tr><td class="wppa-label" >ISO Speed Rating</td><td class="wppa-value" >E#8827</td></tr>
<tr><td class="wppa-label" >Exposure program</td><td class="wppa-value" >E#8822</td></tr>
<tr><td class="wppa-label" >Metering mode</td><td class="wppa-value" >E#9207</td></tr>
<tr><td class="wppa-label" >Flash</td><td class="wppa-value" >E#9209</td></tr>
</table>
</div>';

	$wppa_defaults = array ( 	'wppa_revision' 		=> '100',
								'wppa_prevrev'			=> '100',
	
						// Table 0: Initial setup
						'wppa_i_responsive'				=> '',
						'wppa_i_downsize'				=> '',
						'wppa_i_source' 				=> '',
						'wppa_i_userupload'				=> '',
						'wppa_i_rating'					=> '',
						'wppa_i_comment'				=> '',
						'wppa_i_share'					=> '',
						'wppa_i_iptc'					=> '',
						'wppa_i_exif'					=> '',
						'wppa_i_gpx'					=> '',
						'wppa_i_fotomoto'				=> '',
						'wppa_i_done'					=> '',
						
						// Table I: Sizes
						// A System
						'wppa_colwidth' 				=> 'auto',	// 1
						'wppa_resize_on_upload' 		=> 'no',	// 2
						'wppa_resize_to'				=> '0',		// 3
						'wppa_min_thumbs' 				=> '1',		// 4
						'wppa_bwidth' 					=> '1',		// 5
						'wppa_bradius' 					=> '6',		// 6
						'wppa_box_spacing'				=> '8',		// 7
						'wppa_pagelinks_max' 			=> '7',
						// B Fullsize
						'wppa_fullsize' 				=> '640',	// 1
						'wppa_maxheight' 				=> '480',	// 2
						'wppa_enlarge' 					=> 'no',	// 3
						'wppa_fullimage_border_width' 	=> '',		// 4
						'wppa_numbar_max'				=> '10',	// 5
						'wppa_share_size'				=> '32',
						'wppa_mini_treshold'			=> '500',
						'wppa_slideshow_pagesize'		=> '0',
						// C Thumbnails
						'wppa_thumbsize' 				=> '100',		// 1
						'wppa_thumbsize_alt'			=> '130',		// 1a
						'wppa_thumb_aspect'				=> '0:0:none',	// 2
						'wppa_tf_width' 				=> '100',		// 3
						'wppa_tf_width_alt'				=> '130',		// 3a
						'wppa_tf_height' 				=> '150',		// 4
						'wppa_tf_height_alt'			=> '180',		// 4a
						'wppa_tn_margin' 				=> '4',			// 5
						'wppa_thumb_auto' 				=> 'yes',		// 6
						'wppa_thumb_page_size' 			=> '0',			// 7
						'wppa_popupsize' 				=> '150',		// 8
						'wppa_use_thumbs_if_fit'		=> 'yes',		// 9
						// D Covers
						'wppa_max_cover_width'			=> '1024',	// 1
						'wppa_text_frame_height'		=> '54',	// 2
						'wppa_smallsize' 				=> '150',	// 3
						'wppa_smallsize_multi'			=> '100',	// 3.1
						'wppa_coversize_is_height'		=> 'no',	// 3.9
						'wppa_album_page_size' 			=> '0',		// 4
						// E Rating & comments
						'wppa_rating_max'				=> '5',		// 1
						'wppa_rating_prec'				=> '2',		// 2
						'wppa_gravatar_size'			=> '40',	// 3
						'wppa_ratspacing'				=> '30',
						// F Widgets
						'wppa_topten_count' 			=> '10',	// 1
						'wppa_topten_size' 				=> '86',	// 2
						'wppa_comten_count'				=> '10',	// 3
						'wppa_comten_size'				=> '86',	// 4
						'wppa_featen_count'				=> '10',
						'wppa_featen_size'				=> '86',
						'wppa_thumbnail_widget_count'	=> '10',	// 5
						'wppa_thumbnail_widget_size'	=> '86',	// 6
						'wppa_lasten_count'				=> '10',	// 7
						'wppa_lasten_size' 				=> '86',	// 8
						'wppa_album_widget_count'		=> '10',
						'wppa_album_widget_size'		=> '86',
						
						'wppa_related_count'			=> '10',
						
						// G Overlay
						'wppa_ovl_txt_lines'			=> 'auto',	// 1
						'wppa_magnifier'				=> 'magnifier-small.png',	// 2
						
						// H Video
						'wppa_video_width'				=> '640',
						'wppa_video_height' 			=> '480',

						// Table II: Visibility
						// A Breadcrumb
						'wppa_show_bread_posts' 			=> 'yes',	// 1a
						'wppa_show_bread_pages'				=> 'yes',	// 1b
						'wppa_bc_on_search'					=> 'yes',	// 2
						'wppa_bc_on_topten'					=> 'yes',	// 3
						'wppa_bc_on_lasten'					=> 'yes',	// 3
						'wppa_bc_on_comten'					=> 'yes',	// 3
						'wppa_bc_on_featen'					=> 'yes',
						'wppa_bc_on_tag'					=> 'yes',	// 3
						'wppa_bc_on_related'				=> 'yes',
						'wppa_show_home' 					=> 'yes',	// 4
						'wppa_show_page' 					=> 'yes',	// 4
						'wppa_bc_separator' 				=> 'raquo',	// 5
						'wppa_bc_txt' 						=> htmlspecialchars('<span style="color:red; font_size:24px;">&bull;</span>'),	// 6
						'wppa_bc_url' 						=> wppa_get_imgdir().'arrow.gif',	// 7
						'wppa_pagelink_pos'					=> 'bottom',	// 8
						'wppa_bc_slide_thumblink'			=> 'no',
						// B Slideshow
						'wppa_show_startstop_navigation' 	=> 'yes',		// 1
						'wppa_show_browse_navigation' 		=> 'yes',		// 2
						'wppa_filmstrip' 					=> 'yes',		// 3
						'wppa_film_show_glue' 				=> 'yes',		// 4
						'wppa_show_full_name' 				=> 'yes',		// 5
						'wppa_show_full_owner'				=> 'no', 		// 5.1
						'wppa_show_full_desc' 				=> 'yes',		// 6
						'wppa_hide_when_empty'				=> 'no',		// 6.1
						'wppa_rating_on' 					=> 'yes',		// 7
						'wppa_dislike_mail_every'			=> '5', 		// 7.1
						'wppa_dislike_set_pending'			=> '0',
						'wppa_dislike_delete'				=> '0',
						'wppa_dislike_show_count'			=> 'yes',		// 7.2
						'wppa_rating_display_type'			=> 'graphic',	// 8
						'wppa_show_avg_rating'				=> 'yes',		// 9
						'wppa_show_comments' 				=> 'yes',		// 10
						'wppa_comment_gravatar'				=> 'none',		// 11
						'wppa_comment_gravatar_url'			=> 'http://',	// 12
						'wppa_show_bbb'						=> 'no',		// 13
						'wppa_show_ubb' 					=> 'no',
						'wppa_custom_on' 					=> 'no',		// 14
						'wppa_custom_content' 				=> '<div style="color:red; font-size:24px; font-weight:bold; text-align:center;">Hello world!</div>',	// 15
						'wppa_show_slideshownumbar'  		=> 'no',		// 16
						'wppa_show_iptc'					=> 'no',		// 17
						'wppa_show_iptc_open'				=> 'no',
						'wppa_show_exif'					=> 'no',		// 18
						'wppa_show_exif_open'				=> 'no',
						'wppa_copyright_on'					=> 'yes',		// 19
						'wppa_copyright_notice'				=> __('<span style="color:red" >Warning: Do not upload copyrighted material!</span>', 'wppa'),	// 20
						'wppa_share_on'						=> 'no',
						'wppa_share_hide_when_running'		=> 'no',
						'wppa_share_on_widget'				=> 'no',
						'wppa_share_on_thumbs'				=> 'no',
						'wppa_share_on_lightbox' 			=> 'no',
						'wppa_share_on_mphoto' 				=> 'no',
						'wppa_share_qr'						=> 'no',
						'wppa_share_facebook'				=> 'yes',
						'wppa_share_twitter'				=> 'yes',
						'wppa_share_hyves'					=> 'yes',
						'wppa_share_google'					=> 'yes',
						'wppa_share_pinterest'				=> 'yes',
						'wppa_share_linkedin'				=> 'yes',
						
						'wppa_facebook_comments'			=> 'yes',
						'wppa_facebook_like'				=> 'yes',
						'wppa_facebook_admin_id'			=> '',
						'wppa_facebook_app_id'				=> '',
						'wppa_load_facebook_sdk'			=> 'yes',
						'wppa_share_single_image'			=> 'yes',
						
						// C Thumbnails
						'wppa_thumb_text_name' 				=> 'yes',	// 1
						'wppa_thumb_text_owner'				=> 'no',	// 1.1
						'wppa_thumb_text_desc' 				=> 'yes',	// 2
						'wppa_thumb_text_rating' 			=> 'yes',	// 3
						'wppa_thumb_text_viewcount'			=> 'no',
						'wppa_popup_text_name' 				=> 'yes',	// 4
						'wppa_popup_text_desc' 				=> 'yes',	// 5
						'wppa_popup_text_desc_strip'		=> 'no',	// 5.1
						'wppa_popup_text_rating' 			=> 'yes',	// 6
						'wppa_popup_text_ncomments'			=> 'yes', 	//
						'wppa_show_rating_count'			=> 'no',	// 7
						'wppa_albdesc_on_thumbarea'			=> 'none',
						'wppa_albname_on_thumbarea'			=> 'none',

						// D Covers
						'wppa_show_cover_text' 				=> 'yes',	// 1
						'wppa_enable_slideshow' 			=> 'yes',	// 2
						'wppa_show_slideshowbrowselink' 	=> 'yes',	// 3
						'wppa_show_viewlink'				=> 'yes',	// 4
						'wppa_show_treecount'				=> 'no',
						'wppa_skip_empty_albums'			=> 'yes',
						// E Widgets
						'wppa_show_bbb_widget'				=> 'no',	// 1
						'wppa_show_ubb_widget'				=> 'no',	// 1
						'wppa_show_albwidget_tooltip'		=> 'yes',
						// F Overlay
						'wppa_ovl_close_txt'				=> 'CLOSE',
						'wppa_ovl_theme'					=> 'black',
						'wppa_ovl_slide_name'				=> 'no',
						'wppa_ovl_slide_desc'				=> 'yes',
						'wppa_ovl_thumb_name'				=> 'yes',
						'wppa_ovl_thumb_desc'				=> 'no',
						'wppa_ovl_potd_name'				=> 'yes',
						'wppa_ovl_potd_desc'				=> 'no',
						'wppa_ovl_sphoto_name'				=> 'yes',
						'wppa_ovl_sphoto_desc'				=> 'no',
						'wppa_ovl_mphoto_name'				=> 'yes',
						'wppa_ovl_mphoto_desc'				=> 'no',
						'wppa_ovl_alw_name'					=> 'yes',
						'wppa_ovl_alw_desc'					=> 'no',
						'wppa_ovl_cover_name'				=> 'yes',
						'wppa_ovl_cover_desc'				=> 'no',
						'wppa_ovl_show_counter'				=> 'yes',
						'wppa_show_zoomin'					=> 'yes',

						// Table III: Backgrounds
						'wppa_bgcolor_even' 			=> '#eeeeee',
						'wppa_bcolor_even' 				=> '#cccccc',
						'wppa_bgcolor_alt' 				=> '#dddddd',
						'wppa_bcolor_alt' 				=> '#bbbbbb',
						'wppa_bgcolor_thumbnail' 		=> '#000000',
					//	'wppa_bcolor_thumbnail'			=> '#000000',
						'wppa_bgcolor_nav' 				=> '#dddddd',
						'wppa_bcolor_nav' 				=> '#bbbbbb',
						'wppa_bgcolor_namedesc' 		=> '#dddddd',
						'wppa_bcolor_namedesc' 			=> '#bbbbbb',
						'wppa_bgcolor_com' 				=> '#dddddd',
						'wppa_bcolor_com' 				=> '#bbbbbb',
						'wppa_bgcolor_img'				=> '#eeeeee',
						'wppa_bcolor_img'				=> '',
						'wppa_bgcolor_fullimg' 			=> '#cccccc',
						'wppa_bcolor_fullimg' 			=> '#777777',
						'wppa_bgcolor_cus'				=> '#dddddd',
						'wppa_bcolor_cus'				=> '#bbbbbb',
						'wppa_bgcolor_numbar'			=> '#cccccc',
						'wppa_bcolor_numbar'			=> '#cccccc',
						'wppa_bgcolor_numbar_active'	=> '#333333',
						'wppa_bcolor_numbar_active'	 	=> '#333333',
						'wppa_bgcolor_iptc'				=> '#dddddd',
						'wppa_bcolor_iptc' 				=> '#bbbbbb',
						'wppa_bgcolor_exif'				=> '#dddddd',
						'wppa_bcolor_exif' 				=> '#bbbbbb',
						'wppa_bgcolor_share'			=> '#dddddd',
						'wppa_bcolor_share' 			=> '#bbbbbb',
						'wppa_bgcolor_upload'			=> '#dddddd',
						'wppa_bcolor_upload'			=> '#bbbbbb',
						'wppa_bgcolor_multitag'			=> '#dddddd',
						'wppa_bcolor_multitag'			=> '#bbbbbb',
						'wppa_bgcolor_tagcloud'			=> '#dddddd',
						'wppa_bcolor_tagcloud'			=> '#bbbbbb',
						'wppa_bgcolor_superview'		=> '#dddddd',
						'wppa_bcolor_superview'			=> '#bbbbbb',
						'wppa_bgcolor_search'			=> '#dddddd',
						'wppa_bcolor_search'			=> '#bbbbbb',
						'wppa_bgcolor_bestof'			=> '#dddddd',
						'wppa_bcolor_bestof'			=> '#bbbbbb',

						// Table IV: Behaviour
						// A System
						'wppa_allow_ajax'				=> 'no',
						'wppa_ajax_non_admin'			=> 'no',
						'wppa_use_photo_names_in_urls'	=> 'no',
						'wppa_use_pretty_links'			=> 'no',
						'wppa_update_addressline'		=> 'yes',
						'wppa_render_shortcode_always'	=> 'no',
						'wppa_track_viewcounts'			=> 'yes',
						'wppa_auto_page'				=> 'no',
						'wppa_auto_page_type'			=> 'photo',
						'wppa_auto_page_links'			=> 'bottom',
						'wppa_defer_javascript' 		=> 'no',
						'wppa_inline_css' 				=> 'yes',
						'wppa_custom_style' 			=> '',

						// B Full size and Slideshow
						'wppa_fullvalign' 				=> 'fit',
						'wppa_fullhalign' 				=> 'center',
						'wppa_start_slide' 				=> 'run',
						'wppa_start_slideonly'			=> 'yes',
						'wppa_animation_type'			=> 'fadeover',
						'wppa_slideshow_timeout'		=> '2500',
						'wppa_animation_speed' 			=> '800',
						'wppa_slide_pause'				=> 'no',
						'wppa_slide_wrap'				=> 'yes',
						'wppa_fulldesc_align'			=> 'center',
						'wppa_clean_pbr'				=> 'yes',
						'wppa_run_wpautop_on_desc'		=> 'no',
						'wppa_auto_open_comments'		=> 'yes',
						'wppa_film_hover_goto'			=> 'no',
						'wppa_slide_swipe'				=> 'yes',
						// C Thumbnail
						'wppa_list_photos_by' 			=> '0',
						'wppa_list_photos_desc' 		=> 'no',
						'wppa_thumbtype' 				=> 'default',
						'wppa_thumbphoto_left' 			=> 'no',
						'wppa_valign' 					=> 'center',
						'wppa_use_thumb_opacity' 		=> 'yes',
						'wppa_thumb_opacity' 			=> '95',
						'wppa_use_thumb_popup' 			=> 'yes',
						'wppa_align_thumbtext' 			=> 'no',
						// D Albums and covers
						'wppa_list_albums_by' 			=> '0',
						'wppa_list_albums_desc' 		=> 'no',
						'wppa_coverphoto_pos'			=> 'right',
						'wppa_use_cover_opacity' 		=> 'yes',
						'wppa_cover_opacity' 			=> '85',
						'wppa_cover_type'				=> 'default',
						'wppa_imgfact_count'			=> '10',
						// E Rating
						'wppa_rating_login' 			=> 'yes',
						'wppa_rating_change' 			=> 'yes',
						'wppa_rating_multi' 			=> 'no',
						'wppa_allow_owner_votes' 		=> 'yes',
						'wppa_vote_needs_comment' 		=> 'no',
						'wppa_dislike_value'			=> '-5',
		//				'wppa_rating_use_ajax'			=> 'no',
						'wppa_next_on_callback'			=> 'no',
						'wppa_star_opacity'				=> '20',
						
						'wppa_vote_button_text'			=> 'Vote for me!',
						'wppa_voted_button_text'		=> 'Voted for me',
						'wppa_vote_thumb'				=> 'no',
						'wppa_medal_bronze_when'		=> '5',
						'wppa_medal_silver_when'		=> '10',
						'wppa_medal_gold_when'			=> '15',
						'wppa_medal_color' 				=> '2',
						'wppa_medal_position' 			=> 'botright',
						'wppa_topten_sortby' 			=> 'mean_rating',
						
						// F Comments
						'wppa_comment_login' 			=> 'no',
						'wppa_comments_desc'			=> 'no',
						'wppa_comment_moderation'		=> 'logout',
						'wppa_comment_email_required'	=> 'yes',
						'wppa_comment_notify'			=> 'none',
						'wppa_comment_notify_added'		=> 'yes',
						'wppa_comten_alt_display'		=> 'no',
						'wppa_comten_alt_thumbsize'		=> '75',
						'wppa_comment_smiley_picker'	=> 'no',

						// G Overlay
						'wppa_ovl_opacity'				=> '80',
						'wppa_ovl_onclick'				=> 'none',
						'wppa_ovl_anim'					=> '300',
						'wppa_ovl_slide'				=> '5000',
						'wppa_ovl_chrome_at_top'		=> 'yes',
						'wppa_lightbox_global'			=> 'no',
						'wppa_lightbox_global_set'		=> 'no',
						
						// Table V: Fonts
						'wppa_fontfamily_title' 	=> '',
						'wppa_fontsize_title' 		=> '',
						'wppa_fontcolor_title' 		=> '',
						'wppa_fontweight_title'		=> 'bold',
						'wppa_fontfamily_fulldesc' 	=> '',
						'wppa_fontsize_fulldesc' 	=> '',
						'wppa_fontcolor_fulldesc' 	=> '',
						'wppa_fontweight_fulldesc'	=> 'normal',
						'wppa_fontfamily_fulltitle' => '',
						'wppa_fontsize_fulltitle' 	=> '',
						'wppa_fontcolor_fulltitle' 	=> '',
						'wppa_fontweight_fulltitle'	=> 'normal',
						'wppa_fontfamily_nav' 		=> '',
						'wppa_fontsize_nav' 		=> '',
						'wppa_fontcolor_nav' 		=> '',
						'wppa_fontweight_nav'		=> 'normal',
						'wppa_fontfamily_thumb' 	=> '',
						'wppa_fontsize_thumb' 		=> '',
						'wppa_fontcolor_thumb' 		=> '',
						'wppa_fontweight_thumb'		=> 'normal',
						'wppa_fontfamily_box' 		=> '',
						'wppa_fontsize_box' 		=> '',
						'wppa_fontcolor_box' 		=> '',
						'wppa_fontweight_box'		=> 'normal',
						'wppa_fontfamily_numbar' 	=> '',
						'wppa_fontsize_numbar' 		=> '',
						'wppa_fontcolor_numbar' 	=> '#777777',
						'wppa_fontweight_numbar'	=> 'normal',
						'wppa_fontfamily_numbar_active' 	=> '',
						'wppa_fontsize_numbar_active' 		=> '',
						'wppa_fontcolor_numbar_active' 	=> '#777777',
						'wppa_fontweight_numbar_active'	=> 'bold',

						'wppa_fontfamily_lightbox'	=> '',
						'wppa_fontsize_lightbox'	=> '10',
						'wppa_fontcolor_lightbox'	=> '',
						'wppa_fontweight_lightbox'	=> 'bold',
						
						'wppa_fontsize_widget_thumb'	=> '9',

						
						// Table VI: Links
						'wppa_sphoto_linktype' 				=> 'photo',
						'wppa_sphoto_linkpage' 				=> '0',
						'wppa_sphoto_blank'					=> 'no',
						'wppa_sphoto_overrule'				=> 'no',

						'wppa_mphoto_linktype' 				=> 'photo',
						'wppa_mphoto_linkpage' 				=> '0',
						'wppa_mphoto_blank'					=> 'no',
						'wppa_mphoto_overrule'				=> 'no',
						
						'wppa_thumb_linktype' 				=> 'photo',
						'wppa_thumb_linkpage' 				=> '0',
						'wppa_thumb_blank'					=> 'no',
						'wppa_thumb_overrule'				=> 'no',
						
						'wppa_topten_widget_linktype' 		=> 'photo',
						'wppa_topten_widget_linkpage' 		=> '0',
						'wppa_topten_blank'					=> 'no',
						'wppa_topten_overrule'				=> 'no',

						'wppa_featen_widget_linktype' 		=> 'photo',
						'wppa_featen_widget_linkpage' 		=> '0',
						'wppa_featen_blank'					=> 'no',
						'wppa_featen_overrule'				=> 'no',
						
						'wppa_slideonly_widget_linktype' 	=> 'widget',
						'wppa_slideonly_widget_linkpage' 	=> '0',
						'wppa_sswidget_blank'				=> 'no',
						'wppa_sswidget_overrule'			=> 'no',

						'wppa_widget_linktype' 				=> 'album',
						'wppa_widget_linkpage' 				=> '0',
						'wppa_potd_blank'					=> 'no',
						'wppa_potdwidget_overrule'			=> 'no',

						'wppa_coverimg_linktype' 			=> 'same',
						'wppa_coverimg_linkpage' 			=> '0',
						'wppa_coverimg_blank'				=> 'no',
						'wppa_coverimg_overrule'			=> 'no',

						'wppa_comment_widget_linktype'		=> 'photo',
						'wppa_comment_widget_linkpage'		=> '0',
						'wppa_comment_blank'				=> 'no',
						'wppa_comment_overrule'				=> 'no',

						'wppa_slideshow_linktype'			=> 'none',
						'wppa_slideshow_linkpage'			=> '0',
						'wppa_slideshow_blank'				=> 'no',
						'wppa_slideshow_overrule'			=> 'no',

						'wppa_thumbnail_widget_linktype'	=> 'photo',
						'wppa_thumbnail_widget_linkpage'	=> '0',
						'wppa_thumbnail_widget_overrule'	=> 'no',
						'wppa_thumbnail_widget_blank'		=> 'no',

						'wppa_film_linktype'				=> 'slideshow',
						
						'wppa_lasten_widget_linktype' 		=> 'photo',
						'wppa_lasten_widget_linkpage' 		=> '0',
						'wppa_lasten_blank'					=> 'no',
						'wppa_lasten_overrule'				=> 'no',
						
						'wppa_art_monkey_link'				=> 'none',
						'wppa_art_monkey_popup_link'		=> 'file',
						'wppa_artmonkey_use_source'			=> 'no',
						'wppa_art_monkey_display'			=> 'button',
						
						'wppa_allow_download_album' 		=> 'no',
						'wppa_download_album_source' 		=> 'yes',

						'wppa_album_widget_linktype'		=> 'content',
						'wppa_album_widget_linkpage'		=> '0',
						'wppa_album_widget_blank'			=> 'no',

						'wppa_tagcloud_linktype'			=> 'album',
						'wppa_tagcloud_linkpage'			=> '0',
						'wppa_tagcloud_blank'				=> 'no',

						'wppa_multitag_linktype'			=> 'album',
						'wppa_multitag_linkpage'			=> '0',
						'wppa_multitag_blank'				=> 'no',
						
						'wppa_super_view_linkpage'			=> '0',
						
						'wppa_upldr_widget_linkpage' 		=> '0',
						
						'wppa_bestof_widget_linkpage'		=> '0',
						
						'wppa_album_navigator_widget_linkpage' 	=> '0', 
						
						
						// Table VII: Security
						// B
						'wppa_user_upload_login'	=> 'yes',
						'wppa_owner_only' 			=> 'no',
						'wppa_user_upload_on'		=> 'no',
						'wppa_user_album_edit_on' 	=> 'no',
						'wppa_upload_moderate'		=> 'no',
						'wppa_upload_edit'			=> 'no',
						'wppa_upload_notify' 		=> 'no',
						'wppa_upload_backend_notify'	=> 'no',
						'wppa_upload_one_only'		=> 'no',
						'wppa_memcheck_frontend'	=> 'yes',
						'wppa_memcheck_admin'		=> 'yes',
						'wppa_comment_captcha'		=> 'no',
						'wppa_spam_maxage'			=> 'none',
						'wppa_user_create_on'		=> 'no',
						'wppa_user_create_login'	=> 'yes',
						'wppa_upload_fronend_maxsize' 	=> '0',
						'wppa_void_dups' 			=> 'no',
						
						'wppa_editor_upload_limit_count'		=> '0',
						'wppa_editor_upload_limit_time'			=> '0',
						'wppa_author_upload_limit_count'		=> '0',
						'wppa_author_upload_limit_time'			=> '0',
						'wppa_contributor_upload_limit_count'	=> '0',
						'wppa_contributor_upload_limit_time'	=> '0',
						'wppa_subscriber_upload_limit_count'	=> '0',
						'wppa_subscriber_upload_limit_time'		=> '0',
						'wppa_loggedout_upload_limit_count'		=> '0',
						'wppa_loggedout_upload_limit_time' 		=> '0',
						
						// Table VIII: Actions
						// A Harmless
						'wppa_setup' 				=> '',
						'wppa_backup' 				=> '',
						'wppa_load_skin' 			=> '',
						'wppa_skinfile' 			=> 'default',
						'wppa_regen_thumbs' 				=> '',
						'wppa_regen_thumbs_skip_one' 	=> '',
						'wppa_rerate'				=> '',
						'wppa_cleanup'				=> '',
						'wppa_recup'				=> '',
						'wppa_file_system'			=> 'flat',
						'wppa_blacklist_user' 		=> '',
						'wppa_un_blacklist_user' 	=> '',
						'wppa_remake' 				=> '',
						'wppa_remake_skip_one'		=> '',
						'wppa_errorlog_purge' 		=> '',

						// B Irreversable
						'wppa_rating_clear' 		=> 'no',
						'wppa_viewcount_clear' 		=> 'no',
						'wppa_iptc_clear'			=> '',
						'wppa_exif_clear'			=> '',
						'wppa_apply_new_photodesc_all'	=> '',
						'wppa_remake_index'			=> '',
						'wppa_extend_index' 		=> '',
						'wppa_list_index'			=> '',
						'wppa_list_index_display_start' 	=> '',
						'wppa_append_text'			=> '',
						'wppa_append_to_photodesc' 	=> '',
						'wppa_remove_text'			=> '',
						'wppa_remove_from_photodesc'	=> '',
						'wppa_remove_empty_albums'	=> '',
						'wppa_watermark_all' 		=> '',
						'wppa_create_all_autopages' => '',
						'wppa_readd_file_extensions' => '',

						// Table IX: Miscellaneous
						// A System
						'wppa_html' 					=> 'yes',		// 1
						'wppa_check_balance'			=> 'no',		// 2
						'wppa_allow_debug' 				=> 'no',		// 3
						
						'wppa_filter_priority'			=> '1001',		// 5
						'wppa_shortcode_priority' 		=> '11',
						'wppa_lightbox_name'			=> 'wppa',		// 6
						'wppa_allow_foreign_shortcodes' => 'no',		// 7
						'wppa_allow_foreign_shortcodes_thumbs' 	=> 'no',
						'wppa_arrow_color' 				=> 'black',
						'wppa_meta_page'				=> 'yes',		// 9
						'wppa_meta_all'					=> 'yes',		// 10
						'wppa_use_wp_editor'			=> 'no',
						'wppa_hier_albsel' 				=> 'no',
						'wppa_hier_pagesel'				=> 'no',
						'wppa_alt_type'					=> 'fullname',
						'wppa_photo_admin_pagesize'		=> '20',
						'wppa_comment_admin_pagesize'	=> '10',
						'wppa_jpeg_quality'				=> '95',
						'wppa_geo_edit' 				=> 'no',
						'wppa_auto_continue'			=> 'yes',
						'wppa_max_execution_time'		=> '30',
						'wppa_adminbarmenu_admin'		=> 'yes',
						'wppa_adminbarmenu_frontend'	=> 'yes',
						'wppa_feed_use_thumb'			=> 'no',
						'wppa_og_tags_on'				=> 'yes',
						'wppa_add_shortcode_to_post'	=> 'no',
						'wppa_shortcode_to_add'			=> '[wppa type="album" album="#related,desc"][/wppa]',


						// B New
						'wppa_max_album_newtime'		=> '0',		// 1
						'wppa_max_photo_newtime'		=> '0',		// 2
						'wppa_apply_newphoto_desc'		=> 'no',	// 3
						'wppa_apply_newphoto_desc_user'	=> 'no',
						'wppa_newphoto_description'		=> $npd,	// 4
						'wppa_upload_limit_count'		=> '0',		// 5a
						'wppa_upload_limit_time'		=> '0',		// 5b
						'wppa_show_album_full'			=> 'yes',
						'wppa_grant_an_album'		=> 'no',
						'wppa_grant_name'			=> 'display',
						'wppa_grant_parent'			=> '0',
						'wppa_default_parent' 		=> '0',
						
						'wppa_max_albums'				=> '0',
						'wppa_alt_is_restricted'		=> 'no',
						'wppa_link_is_restricted'		=> 'no',
						'wppa_covertype_is_restricted'	=> 'no',
						'wppa_porder_restricted'		=> 'no',
						
//						'wppa_strip_file_ext'			=> 'no',
						'wppa_newphoto_name_method' 	=> 'filename',
						
						'wppa_copy_timestamp'			=> 'no',
						
						// E Search
						'wppa_search_linkpage' 			=> '0',		// 1
						'wppa_excl_sep' 				=> 'no',	// 2
						'wppa_search_tags'				=> 'no',
						'wppa_search_cats'				=> 'no',
						'wppa_search_comments' 			=> 'no',
						'wppa_photos_only'				=> 'no',	// 3
						'wppa_indexed_search'			=> 'no',
						'wppa_max_search_photos'		=> '250',
						'wppa_max_search_albums'		=> '25',
						'wppa_tags_or_only'				=> 'no',
						'wppa_wild_front'				=> 'no',
						'wppa_search_display_type' 		=> 'content',
						
						// F Watermark
						'wppa_watermark_on'				=> 'no',
						'wppa_watermark_user'			=> 'no',
						'wppa_watermark_file'			=> 'specimen.png',
						'wppa_watermark_pos'			=> 'cencen',
						'wppa_textual_watermark_type'	=> 'tvstyle',
						'wppa_textual_watermark_text' 	=> "Copyright (c) 2014 w#site \n w#filename (w#owner)",
						'wppa_textual_watermark_font' 	=> 'system',
						'wppa_textual_watermark_size'	=> '10',
						'wppa_watermark_upload'			=> '',
						'wppa_watermark_opacity'		=> '20',
						'wppa_watermark_opacity_text' 	=> '80',
						
						// G Slide order
						'wppa_slide_order'				=> '0,1,2,3,4,5,6,7,8,9,10',
						'wppa_slide_order_split'		=> '0,1,2,3,4,5,6,7,8,9,10,11',
						'wppa_swap_namedesc' 			=> 'no',
						'wppa_split_namedesc'			=> 'no',

						// J Other plugins
						'wppa_cp_points_comment'		=> '0',
						'wppa_cp_points_rating'			=> '0',
						'wppa_cp_points_upload'			=> '0',
						'wppa_use_scabn'				=> 'no',
						
						// K External services
						'wppa_gpx_implementation' 		=> 'none',
						'wppa_map_height' 				=> '300',
						'wppa_map_apikey' 				=> '',
						'wppa_gpx_shortcode'			=> '[map style="width: auto; height:300px; margin:0; " marker="yes" lat="w#lat" lon="w#lon"]',
						'wppa_fotomoto_on'				=> 'no',
						'wppa_fotomoto_fontsize'		=> '',
						'wppa_fotomoto_hide_when_running'	=> 'no',
						'wppa_fotomoto_min_width' 		=> '400',

						// Photo of the day widget
						'wppa_widgettitle'			=> __('Photo of the day', 'wppa'),
						'wppa_widget_linkurl'		=> __('Type your custom url here', 'wppa'),
						'wppa_widget_linktitle' 	=> __('Type the title here', 'wppa'),
						'wppa_widget_subtitle'		=> 'none',
						'wppa_widget_album'			=> '0',
						'wppa_widget_photo'			=> '',
						'wppa_potd_align' 			=> 'center',
						'wppa_widget_method'		=> '1',
						'wppa_widget_period'		=> '168',
						
						'wppa_widget_width'			=> '200',
						
						// Topten widget
						'wppa_toptenwidgettitle'	=> __('Top Ten Photos', 'wppa'),

						// Thumbnail widget
						'wppa_thumbnailwidgettitle'	=> __('Thumbnail Photos', 'wppa'),
						
						// Search widget
						'wppa_searchwidgettitle'	=> __('Search photos', 'wppa'),
						
						// Comment admin
						'wppa_comadmin_show' 		=> 'all',
						'wppa_comadmin_order' 		=> 'timestamp',
								
						// QR code settings
						'wppa_qr_size'				=> '200',
						'wppa_qr_color'				=> '#000000',
						'wppa_qr_bgcolor'			=> '#FFFFFF',
						
						// H Source file management and import/upload
						'wppa_keep_source_admin'	=> 'no',
						'wppa_keep_source_frontend' => 'no',
						'wppa_source_dir'			=> WPPA_ABSPATH.WPPA_UPLOAD.'/wppa-source',
						'wppa_keep_sync'			=> 'yes',
						'wppa_remake_add'			=> 'yes',
						'wppa_save_iptc'			=> 'yes',
						'wppa_save_exif'			=> 'yes',
						'wppa_exif_max_array_size'	=> '10',
						'wppa_chgsrc_is_restricted'		=> 'no',
						'wppa_newpag_create'			=> 'no',
						'wppa_newpag_content'			=> '[wppa type="cover" album="w#album" align="center"][/wppa]',
						'wppa_newpag_type'				=> 'page',
						'wppa_newpag_status'			=> 'publish',

						// J CDN Services
						'wppa_cdn_service'			=> '',
						'wppa_cdn_cloud_name'		=> '',
						'wppa_cdn_api_key'			=> '',
						'wppa_cdn_api_secret'		=> '',
						'wppa_cdn_service_update'	=> 'no',
						'wppa_delete_all_from_cloudinary' 	=> ''
						
						);

	array_walk( $wppa_defaults, 'wppa_set_default', $force );
	
	return true;
}
function wppa_set_default($value, $key, $force) {
	$void_these = array(
		'wppa_revision',
		'wppa_rating_max',
		'wppa_file_system'
		);
							
/*	if ( $force ) {
		// Delete all options except the voids
		if ( ! in_array( $key, $void_these ) ) delete_option( $key );
	}
	else {
		// Delete all options that have the default value, except the voids
		if ( ! in_array( $key, $void_these ) && get_option( $key, 'nil' ) == $value ) delete_option( $key );
	}
*/
	if ( $force ) {
		if ( ! in_array($key, $void_these) ) wppa_update_option($key, $value);
	}
	else {
		if ( get_option($key, 'nil') == 'nil' ) wppa_update_option($key, $value);
	}

}

// Check if the required directories exist, if not, try to create them and optionally report it
function wppa_check_dirs() {

	if ( ! is_multisite() ) {
		// check if uploads dir exists
		$upload_dir = wp_upload_dir();
		$dir = $upload_dir['basedir'];
		if ( ! is_dir($dir) ) {
			wppa_mktree($dir);
			if ( ! is_dir($dir) ) {
				wppa_error_message(__('The uploads directory does not exist, please do a regular WP upload first.', 'wppa').'<br/>'.$dir);
				return false;
			}
			else {
				if ( WPPA_DEBUG ) wppa_ok_message(__('Successfully created uploads directory.', 'wppa').'<br/>'.$dir);
			}
		}
		@ chmod($dir, 0755);		
	}

	// check if wppa dir exists
	$dir = WPPA_UPLOAD_PATH;
	if ( ! is_dir($dir) ) {
		wppa_mktree($dir);
		if ( ! is_dir($dir) ) {
			wppa_error_message(__('Could not create the wppa directory.', 'wppa').wppa_credirmsg($dir));
			return false;
		}
		else {
			if ( WPPA_DEBUG ) wppa_ok_message(__('Successfully created wppa directory.', 'wppa').'<br/>'.$dir);
		}
	}
	@ chmod($dir, 0755);
	
	// check if thumbs dir exists 
	$dir = WPPA_UPLOAD_PATH.'/thumbs';
	if ( ! is_dir($dir) ) {
		wppa_mktree($dir);
		if ( ! is_dir($dir) ) {
			wppa_error_message(__('Could not create the wppa thumbs directory.', 'wppa').wppa_credirmsg($dir));
			return false;
		}
		else {
			if ( WPPA_DEBUG ) wppa_ok_message(__('Successfully created wppa thumbs directory.', 'wppa').'<br/>'.$dir);
		}
	}
	@ chmod($dir, 0755);

	// check if watermarks dir exists 
	$dir = WPPA_UPLOAD_PATH.'/watermarks';
	if ( ! is_dir($dir) ) {
		wppa_mktree($dir);
		if ( ! is_dir($dir) ) {
			wppa_error_message(__('Could not create the wppa watermarks directory.', 'wppa').wppa_credirmsg($dir));
			return false;
		}
		else {
			if ( WPPA_DEBUG ) wppa_ok_message(__('Successfully created wppa watermarks directory.', 'wppa').'<br/>'.$dir);
		}
	}
	@ chmod($dir, 0755);

	// check if fonts dir exists 
	$dir = WPPA_UPLOAD_PATH.'/fonts';
	if ( ! is_dir($dir) ) {
		wppa_mktree($dir);
		if ( ! is_dir($dir) ) {
			wppa_error_message(__('Could not create the wppa fonts directory.', 'wppa').wppa_credirmsg($dir));
			return false;
		}
		else {
			if ( WPPA_DEBUG ) wppa_ok_message(__('Successfully created wppa fonts directory.', 'wppa').'<br/>'.$dir);
		}
	}
	@ chmod($dir, 0755);
	
	// check if depot dir exists
	if ( ! is_multisite() ) {
		// check if users depot dir exists
		$dir = WPPA_CONTENT_PATH.'/wppa-depot';
		if ( ! is_dir($dir) ) {
			wppa_mktree($dir);
			if ( ! is_dir($dir) ) {
				wppa_error_message(__('Unable to create depot directory.', 'wppa').wppa_credirmsg($dir));
				return false;
			}
			else {
				if ( WPPA_DEBUG ) wppa_ok_message(__('Successfully created wppa depot directory.', 'wppa').'<br/>'.$dir);
			}
		}
		@ chmod($dir, 0755);
	}
	
	// check the user depot directory
	$dir = WPPA_DEPOT_PATH;
	if ( ! is_dir($dir) ) {
		wppa_mktree($dir);
		if ( ! is_dir($dir) ) {
			wppa_error_message(__('Unable to create user depot directory', 'wppa').wppa_credirmsg($dir));
			return false;
		}
		else {
			if ( WPPA_DEBUG ) wppa_ok_message(__('Successfully created wppa user depot directory.', 'wppa').'<br/>'.$dir);
		}
	}
	@ chmod($dir, 0755);
	
	// check the temp dir
	$dir = WPPA_UPLOAD_PATH.'/temp/';
	if ( ! is_dir($dir) ) {
		wppa_mktree($dir);
		if ( ! is_dir($dir) ) {
			wppa_error_message(__('Unable to create temp directory', 'wppa').wppa_credirmsg($dir));
			return false;
		}
		else {
			if ( WPPA_DEBUG ) wppa_ok_message(__('Successfully created temp directory.', 'wppa').'<br/>'.$dir);
		}
	}
	@ chmod($dir, 0755);
	
	return true;
}
function wppa_credirmsg($dir) {
	$msg = ' '.sprintf(__('Ask your administrator to give you more rights, or create <b>%s</b> manually using an FTP program.', 'wppa'), $dir);
	return $msg;
}
