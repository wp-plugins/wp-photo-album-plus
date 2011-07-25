<?php
/* wppa_adminfunctions.php
* Pachkage: wp-photo-album-plus
*
* gp admin functions
* version 3.1.8
*
*/

// Set default option values
function wppa_set_defaults($force = false) {
global $wppa_defaults;

	$wppa_defaults = array ( 'wppa_revision' 		=> '100',
						'wppa_fullsize' 			=> '640',
						'wppa_colwidth' 			=> '640',
						'wppa_maxheight' 			=> '640',
						'wppa_enlarge' 				=> 'no',
						'wppa_resize_on_upload' 	=> 'no',
						'wppa_fullvalign' 			=> 'fit',
						'wppa_fullhalign' 			=> 'center',
						'wppa_min_thumbs' 			=> '1',
						'wppa_thumbtype' 			=> 'default',
						'wppa_valign' 				=> 'center',
						'wppa_thumbsize' 			=> '100',
						'wppa_tf_width' 			=> '100',
						'wppa_tf_height' 			=> '110',
						'wppa_tn_margin' 			=> '4',
						'wppa_smallsize' 			=> '150',
						'wppa_show_bread' 			=> 'yes',
						'wppa_show_home' 			=> 'yes',
						'wppa_bc_separator' 		=> 'raquo',
						'wppa_use_thumb_opacity' 	=> 'yes',
						'wppa_thumb_opacity' 		=> '85',
						'wppa_use_thumb_popup' 		=> 'yes',
						'wppa_use_cover_opacity' 	=> 'yes',
						'wppa_cover_opacity' 		=> '85',
						'wppa_animation_speed' 		=> '600',
						'wppa_slideshow_timeout'	=> '2500',
						'wppa_bgcolor_even' 		=> '#eeeeee',
						'wppa_bgcolor_alt' 			=> '#dddddd',
						'wppa_bgcolor_nav' 			=> '#dddddd',
						'wppa_bgcolor_img'			=> '#eeeeee',
						'wppa_bgcolor_namedesc' 	=> '#dddddd',
						'wppa_bgcolor_com' 			=> '#dddddd',
						'wppa_bcolor_even' 			=> '#cccccc',
						'wppa_bcolor_alt' 			=> '#bbbbbb',
						'wppa_bcolor_nav' 			=> '#bbbbbb',
						'wppa_bcolor_namedesc' 		=> '#bbbbbb',
						'wppa_bcolor_com' 			=> '#bbbbbb',
						'wppa_bwidth' 				=> '1',
						'wppa_bradius' 				=> '6',
						'wppa_fontfamily_thumb' 	=> '',
						'wppa_fontsize_thumb' 		=> '',
						'wppa_fontcolor_thumb' 		=> '',
						'wppa_fontfamily_box' 		=> '',
						'wppa_fontsize_box' 		=> '',
						'wppa_fontcolor_box' 		=> '',
						'wppa_fontfamily_nav' 		=> '',
						'wppa_fontsize_nav' 		=> '',
						'wppa_fontcolor_nav' 		=> '',
						'wppa_fontfamily_title' 	=> '',
						'wppa_fontsize_title' 		=> '',
						'wppa_fontcolor_title' 		=> '',
						'wppa_fontfamily_fulldesc' 	=> '',
						'wppa_fontsize_fulldesc' 	=> '',
						'wppa_fontcolor_fulldesc' 	=> '',
						'wppa_fontfamily_fulltitle' => '',
						'wppa_fontsize_fulltitle' 	=> '',
						'wppa_fontcolor_fulltitle' 	=> '',
						'wppa_arrow_color' 			=> 'black',
						'wppa_2col_treshold' 		=> '1024',
						'wppa_3col_treshold' 		=> '1024',
						'wppa_film_show_glue' 		=> 'yes',
						'wppa_album_page_size' 		=> '0',
						'wppa_thumb_page_size' 		=> '0',
						'wppa_thumb_auto' 			=> 'yes',
						'wppa_coverphoto_left' 		=> 'no',
						'wppa_thumbphoto_left' 		=> 'no',
						'wppa_enable_slideshow' 	=> 'yes',
						'wppa_thumb_text_name' 		=> 'yes',
						'wppa_thumb_text_desc' 		=> 'yes',
						'wppa_thumb_text_rating' 			=> 'yes',
						'wppa_show_startstop_navigation' 	=> 'yes',
						'wppa_show_browse_navigation' 		=> 'yes',
						'wppa_show_full_desc' 				=> 'yes',
						'wppa_show_full_name' 				=> 'yes',
						'wppa_show_comments' 				=> 'no',
						'wppa_show_cover_text' 				=> 'yes',
						'wppa_start_slide' 					=> 'yes',
						'wppa_hide_slideshow' 				=> 'no',
						'wppa_filmstrip' 					=> 'yes',
						'wppa_bc_url' 						=> wppa_get_imgdir().'arrow.gif',
						'wppa_bc_txt' 						=> htmlspecialchars('<span style="color:red; font_size:24px;">&bull;</span>'),
						'wppa_topten_count' 				=> '10',
						'wppa_topten_size' => '86',
						'wppa_excl_sep' => 'no',
						'wppa_rating_on' => 'yes',
						'wppa_rating_login' => 'yes',
						'wppa_rating_change' => 'yes',
						'wppa_rating_multi' => 'no',
						'wppa_comment_login' => 'no',
						'wppa_list_albums_by' => '0',
						'wppa_list_albums_desc' => 'no',
						'wppa_list_photos_by' => '0',
						'wppa_list_photos_desc' => 'no',
						'wppa_html' => 'no',
						'wppa_thumb_linkpage' => '0',
						'wppa_thumb_linktype' => 'photo',
						'wppa_mphoto_linkpage' => '0',
						'wppa_mphoto_linktype' => 'photo',
						'wppa_fadein_after_fadeout' => 'no',
						'wppa_widget_linkpage' => '0',
						'wppa_widget_linktype' => 'album',
						'wppa_topten_widget_linkpage' => '0',
						'wppa_topten_widget_linktype' => 'photo',
						'wppa_slideonly_widget_linkpage' => '0',
						'wppa_slideonly_widget_linktype' => 'widget',
						'wppa_coverimg_linkpage' => '0',
						'wppa_coverimg_linktype' => 'same',
						'wppa_search_linkpage' => '0',
						'wppa_rating_clear' => 'no',
						'wppa_chmod' => '0',
						'wppa_owner_only' => 'no',
						'wppa_set_access_by' => 'me',
						'wppa_accesslevel' => 'administrator',
						'wppa_accesslevel_upload' => 'administrator',
						'wppa_accesslevel_sidebar' => 'administrator',
						'wppa_charset' => '',
						'wppa_setup' => '',
						'wppa_backup' => '',
						'wppa_restore' => '',
						'wppa_defaults' => '',
						'wppa_regen' => '',
						'wppa_allow_debug' => 'no',
						'wppa_potd_align' => 'center',
						'wppa_comadmin_show' => 'all',
						'wppa_popupsize' => get_option('wppa_smallsize', '150'),
						'wppa_comadmin_order' => 'timestamp'

						);
	
	array_walk($wppa_defaults, 'wppa_set_default', $force);
}
function wppa_set_default($value, $key, $force) {
	if ($force) {
		update_option($key, $value);
	}
	else {
		if (get_option($key, 'nil') == 'nil') update_option($key, $value);
	}
}

function wppa_backup_settings() {
global $wppa_opt;
global $wppa_bu_err;
	// Open file
	$fname = ABSPATH.'wp-content/wppa-depot/'.wppa_get_user().'/settings.bak';
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
	if (fwrite($file, $key.":".$value."\n") === false) {
		if ($wppa_bu_err !== true) {
			wppa_error_message(__('Error writing to settings backup file', 'wppa'));
			$wppa_bu_err = true;
		}	
	}
}

function wppa_restore_settings() {
	// Open file
	$fname = ABSPATH.'wp-content/wppa-depot/'.wppa_get_user().'/settings.bak';
	$file = fopen($fname, 'r');
	if ($file) {
		$buffer = fgets($file, 4096);
		while (!feof($file)) {
			$buflen = strlen($buffer);
			$cpos = strpos($buffer, ':');
			$delta_l = $buflen - $cpos - 2;
			if ($cpos && $delta_l >= 0) {
				$slug = substr($buffer, 0, $cpos);
				$value = stripslashes(substr($buffer, $cpos+1, $delta_l));
				// echo('Doing|'.$slug.'|'.$value.'|<br/>');
				update_option($slug, $value);
			}
			$buffer = fgets($file, 4096);
		}
		fclose($file);
		wppa_initialize_runtime(true);
		return true;
	}
	else {
		wppa_error_message(__('Settings backup file not found', 'wppa'));
		return false;
	}
}

// update all thumbs 
function wppa_regenerate_thumbs() {
	global $wpdb;
	
	$thumbsize = wppa_get_minisize();
	$wppa_dir = ABSPATH . 'wp-content/uploads/wppa/';

    $start = get_option('wppa_lastthumb', '-1');

	$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . WPPA_PHOTOS . '` WHERE `id` > %s ORDER BY `id`', $start), 'ARRAY_A');
	
	if (!empty($photos)) {
		$count = count($photos);
		foreach ($photos as $photo) {
			$newimage = $wppa_dir . $photo['id'] . '.' . $photo['ext'];
			wppa_create_thumbnail($newimage, $thumbsize, '' );
            update_option('wppa_lastthumb', $photo['id']);
            echo '.';
		}
	}		
}

// Create thumbnail
function wppa_create_thumbnail( $file, $max_side, $effect = '') {
	if (file_exists($file)) {
		$img_size = getimagesize( $file );
		$dir = $img_size[0] > $img_size[1] ? 'W' : 'H';
		$thumb = 'thumbs/' . basename( $file );
		$thumbpath = str_replace( basename( $file ), $thumb, $file );

		require_once('wppa_class_resize.php');		
		$objResize = new wppa_ImageResize($file, $thumbpath, $dir, $max_side);
		$objResize->destroyImage($objResize->resOriginalImage);
		$objResize->destroyImage($objResize->resResizedImage);
	}
	else {
		return false;
	}
	return true;
}

function wppa_check_update() {
global $wppa_revno;
	if (get_option('wppa_revision', '100') < $wppa_revno) {
		$msg = '';
		$msg .= __('You probably performed an automatic update. Due to a WP bug not all actions that are required for an update have been executed yet.<br/><br/>', 'wppa');
		$msg .= __('The wppa database tables are not yet at the required revision level.', 'wppa').' '.__('Current=', 'wppa').get_option('wppa_revision', '100').' '.__('New=', 'wppa').$wppa_revno;
		wppa_error_message($msg);
		wppa_setup();
		if (get_option('wppa_revision', '100') < $wppa_revno) {
			wppa_error_message(__('PLEASE DE-ACTIVATE the plugin WP-PHOTO-ALBUM-PLUS and ACTIVATE AGAIN before you continue!', 'wppa'));
			wp_die(__('Failed to fix this.', 'wppa').' '.__('Current=', 'wppa').get_option('wppa_revision', '100').' '.__('New=', 'wppa').$wppa_revno);
			return;
		}
		else {
			wppa_ok_message(__('I fixed this for you......', 'wppa'));
		}
	}
	$key = get_option('wppa_update_key', '0');
	if ($key == '0') return;
	
	$msg = '<center>' . __('IMPORTANT UPGRADE NOTICE', 'wppa') . '</center><br/>';
	if ($key == '1' || $key == '3') $msg .= '<br/>' . __('Please CHECK your customized WPPA_STYLE.CSS file against the newly supplied one. You may wish to add or modify some attributes. Be aware of the fact that most settings can now be set in the admin settings page.', 'wppa');
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
		$wp_roles->add_cap('administrator', 'wppa_sidebar_admin');
		$wp_roles->add_cap('administrator', 'wppa_upload');
		/* album admin and upload */
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
		/* upload photos */
		$level = get_option('wppa_accesslevel_upload', 'administrator');
		if ($level == 'contributor') {
			$wp_roles->remove_cap('subscriber', 'wppa_upload');		
			$wp_roles->add_cap('contibutor', 'wppa_upload');
			$wp_roles->add_cap('author', 'wppa_upload');
			$wp_roles->add_cap('editor', 'wppa_upload');	
		}
		if ($level == 'author') {
			$wp_roles->remove_cap('subscriber', 'wppa_upload');		
			$wp_roles->remove_cap('contibutor', 'wppa_upload');
			$wp_roles->add_cap('author', 'wppa_upload');
			$wp_roles->add_cap('editor', 'wppa_upload');		
		}
		if ($level == 'editor') {
			$wp_roles->remove_cap('subscriber', 'wppa_upload');		
			$wp_roles->remove_cap('contibutor', 'wppa_upload');
			$wp_roles->remove_cap('author', 'wppa_upload');
			$wp_roles->add_cap('editor', 'wppa_upload');		
		}
		if ($level == 'administrator') {
			$wp_roles->remove_cap('subscriber', 'wppa_upload');		
			$wp_roles->remove_cap('contibutor', 'wppa_upload');
			$wp_roles->remove_cap('author', 'wppa_upload');
			$wp_roles->remove_cap('editor', 'wppa_upload');		
		}
		/* sidebar widget admin */
		$level = get_option('wppa_accesslevel_sidebar', 'administrator');
		if ($level == 'contributor') {
			$wp_roles->remove_cap('subscriber', 'wppa_sidebar_admin');		
			$wp_roles->add_cap('contibutor', 'wppa_sidebar_admin');
			$wp_roles->add_cap('author', 'wppa_sidebar_admin');
			$wp_roles->add_cap('editor', 'wppa_sidebar_admin');	
		}
		if ($level == 'author') {
			$wp_roles->remove_cap('subscriber', 'wppa_sidebar_admin');		
			$wp_roles->remove_cap('contibutor', 'wppa_sidebar_admin');
			$wp_roles->add_cap('author', 'wppa_sidebar_admin');
			$wp_roles->add_cap('editor', 'wppa_sidebar_admin');		
		}
		if ($level == 'editor') {
			$wp_roles->remove_cap('subscriber', 'wppa_sidebar_admin');		
			$wp_roles->remove_cap('contibutor', 'wppa_sidebar_admin');
			$wp_roles->remove_cap('author', 'wppa_sidebar_admin');
			$wp_roles->add_cap('editor', 'wppa_sidebar_admin');		
		}
		if ($level == 'administrator') {
			$wp_roles->remove_cap('subscriber', 'wppa_sidebar_admin');		
			$wp_roles->remove_cap('contibutor', 'wppa_sidebar_admin');
			$wp_roles->remove_cap('author', 'wppa_sidebar_admin');
			$wp_roles->remove_cap('editor', 'wppa_sidebar_admin');		
		}
	}
}

// set last album 
function wppa_set_last_album($id = '') {
    global $albumid;
    global $current_user;
	
	get_currentuserinfo();

    if (is_numeric($id)) $albumid = $id; else $albumid = '';
	$opt = 'wppa_last_album_used-'.$current_user->user_login;
    update_option($opt, $albumid);
}

// get last album
function wppa_get_last_album() {
    global $albumid;
    global $current_user;
	
	get_currentuserinfo();
    
    if (is_numeric($albumid)) $result = $albumid;
    else {
		$opt = 'wppa_last_album_used-'.$current_user->user_login;
		$result = get_option($opt, get_option('wppa_last_album_used', ''));
	}
    if (!is_numeric($result)) $result = '';
    else $albumid = $result;

	return $result; 
}

// display order options
function wppa_order_options($order, $nil, $rat = '') {
    if ($nil != '') { 
?>
    <option value="0"<?php if ($order == "" || $order == "0") echo (' selected="selected"'); ?>><?php echo($nil); ?></option>
<?php 
	}
?>
    <option value="1"<?php if ($order == "1") echo(' selected="selected"'); ?>><?php _e('Order #', 'wppa'); ?></option>
    <option value="2"<?php if ($order == "2") echo(' selected="selected"'); ?>><?php _e('Name', 'wppa'); ?></option>
    <option value="3"<?php if ($order == "3") echo(' selected="selected"'); ?>><?php _e('Random', 'wppa'); ?></option>  
<?php
	if ($rat != '') {
?>
	<option value="4"<?php if ($order == "4") echo(' selected="selected"'); if (get_option('wppa_rating_on', 'yes') == 'no') echo ('disabled="disabled"') ?>><?php echo($rat); ?></option>
<?php
	}
}

// display usefull message
function wppa_update_message($msg) {
?>
    <div id="message" class="updated fade"><p><strong><?php echo($msg); ?></strong></p></div>
<?php
}

// display error message
function wppa_error_message($msg) {
?>
	<div id="error" class="error"><p><strong><?php echo($msg); ?></strong></p></div>
<?php
}
// display warning message
function wppa_warning_message($msg) {
?>
	<div id="warning" class="updated"><p><strong><?php echo($msg); ?></strong></p></div>
<?php
}
// display ok message
function wppa_ok_message($msg) {
?>
	<div id="warning" class="updated" style="background-color: #e0ffe0; border-color: #55ee55;" ><p><strong><?php echo($msg); ?></strong></p></div>
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

function wppa_get_minisize() {
	$result = '100';
	
	$tmp = get_option('wppa_thumbsize', 'nil');
	if (is_numeric($tmp) && $tmp > $result) $result = $tmp;
	$tmp = get_option('wppa_smallsize', 'nil');
	if (is_numeric($tmp) && $tmp > $result) $result = $tmp;
	$tmp = get_option('wppa_popupsize', 'nil');
	if (is_numeric($tmp) && $tmp > $result) $result = $tmp;
	
	$result = ceil($result / 25) * 25;
	return $result;
}

// check if albums 'exists'
function wppa_has_albums() {
	return wppa_have_access('any');
}
// See if an album or any album is accessable for the current user
function wppa_have_access($alb) {
global $wpdb;
global $current_user;
	// See if there is any album accessable
	if ($alb == 'any') {
		// Administrator has always access OR If all albums are public
		if (current_user_can('administrator') || get_option('wppa_owner_only', 'no') == 'no') {
			$albs = $wpdb->get_results('SELECT id FROM '.WPPA_ALBUMS);
			if ($albs) return true;
			else return false;	// No albums in system
		}
		else {
			get_currentuserinfo();
			$user = $current_user->user_login;
			$albs = $wpdb->get_results('SELECT id FROM '.WPPA_ALBUMS.' WHERE owner = "'.$user.'"');
			if ($albs) return true;
			else return false;	// No albums for user accessable
		}
	}
	
	// See for given album data array or album number
	else {
		// Administrator has always access
		if (current_user_can('administrator')) return true;
		// If all albums are public
		if (get_option('wppa_owner_only', 'no') == 'no') return true;
		// Find the owner
		$owner = '';
		if (is_array($alb)) {
			$owner = $alb['owner'];
		}
		elseif (is_numeric($alb)) {
			$owner = $wpdb->get_var('SELECT owner FROM '.WPPA_ALBUMS.' WHERE id = '.$alb);
		}
		// Find the user
		get_currentuserinfo();
		
		if ($current_user->user_login == $owner) return true;
		else return false;
	}
}

function wppa_get_users() {
global $wpdb;
	$users = $wpdb->get_results('SELECT * FROM '.$wpdb->users, 'ARRAY_A');
	return $users;
}

function wppa_user_select($select = '') {
	$result = '';
	$iam = $select == '' ? wppa_get_user() : $select;
	$users = wppa_get_users();
	foreach ($users as $usr) {
		if ($usr['user_login'] == $iam) $sel = 'selected="selected"';
		else $sel = '';
		$result .= '<option value="'.$usr['user_login'].'" '.$sel.'>'.$usr['display_name'].'</option>';
	}	
	echo ($result);
}

function wppa_chmod($chmod) {
	_wppa_chmod_(ABSPATH.'wp-content/uploads/wppa', $chmod);
	_wppa_chmod_(ABSPATH.'wp-content/uploads/wppa/thumbs', $chmod);
	_wppa_chmod_(ABSPATH.'wp-content/wppa-depot', $chmod);
	$users = wppa_get_users();
	if ($users) foreach($users as $user) {
		_wppa_chmod_(ABSPATH.'wp-content/wppa-depot/'.$user['display_name'], $chmod);
	}
}

function _wppa_chmod_($file, $chmod) {
	if ($chmod == '0') return;	// Unchange
	switch ($chmod) {
		case '750':
			if (is_dir($file)) chmod($file, 0750);
			if (is_file($file)) chmod($file, 0640);
			break;
		case '755':
			if (is_dir($file)) chmod($file, 0755);
			if (is_file($file)) chmod($file, 0644);
			break;
		case '775':
			if (is_dir($file)) chmod($file, 0775);
			if (is_file($file)) chmod($file, 0664);
			break;
		case '777':
			if (is_dir($file)) chmod($file, 0777);
			if (is_file($file)) chmod($file, 0666);
			break;
		default:
		wppa_error_message(__('Unsupported value in _wppa_chmod_ :', 'wppa').' '.$chmod);
	}
}

function wppa_copy_photo($photoid, $albumto) {
global $wpdb;

	$err = '1';
	// Check args
	if (!is_numeric($photoid) || !is_numeric($albumto)) return $err;
	
	$err = '2';
	// Find photo details
	$photo = $wpdb->get_row('SELECT * FROM '.WPPA_PHOTOS.' WHERE id = '.$photoid, 'ARRAY_A');
	if (!$photo) return $err;
	$id = '0';
	$album = $albumto;
	$ext = $photo['ext'];
	$name = $photo['name'];
	$porder = '0';
	$desc = $photo['description'];
	$linkurl = $photo['linkurl'];
	$linktitle = $photo['linktitle'];
	$oldimage = ABSPATH.'wp-content/uploads/wppa/'.$photo['id'].'.'.$ext;
	$oldthumb = ABSPATH.'wp-content/uploads/wppa/thumbs/'.$photo['id'].'.'.$ext;
	
	$err = '3';
	// Make new db table entry
	$query = $wpdb->prepare('INSERT INTO `' . WPPA_PHOTOS . '` (`id`, `album`, `ext`, `name`, `p_order`, `description`, `mean_rating`, `linkurl`, `linktitle`) VALUES (%s, %s, %s, %s, %s, %s, \'\', %s, %s)', $id, $album, $ext, $name, $porder, $desc, $linkurl, $linktitle);
	if ($wpdb->query($query) === false) return $err;

	$err = '4';
	// Find copied photo details
	$image_id = $wpdb->get_var("SELECT LAST_INSERT_ID()");					
	$newimage = ABSPATH.'wp-content/uploads/wppa/'.$image_id.'.'.$ext;
	$newthumb = ABSPATH.'wp-content/uploads/wppa/thumbs/'.$image_id.'.'.$ext;
	if (!$image_id) return $err;
	
	$err = '5';
	// Do the filsystem copy
	if (!copy($oldimage, $newimage)) return $err;
	$err = '6';
	if (!copy($oldthumb, $newthumb)) return $err;
	
	return false;	// No error
}

function wppa_rotate($id, $ang) {
global $wpdb;

	// Check args
	$err = '1';
	if (!is_numeric($id) || !is_numeric($ang)) return $err;
	
	// Get the ext
	$err = '2';
	$ext = $wpdb->get_var('SELECT ext FROM '.WPPA_PHOTOS.' WHERE id = '.$id);
	if (!$ext) return $err;
	
	// Get the image
	$err = '3';
	$file = ABSPATH.'wp-content/uploads/wppa/'.$id.'.'.$ext;
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


// Check if the required directories exist, if not, try to create them and report it
function wppa_check_dirs() {

	// check if uploads dir exists
	$dir = ABSPATH . 'wp-content/uploads';
	if (!is_dir($dir)) {
		mkdir($dir);
		if (!is_dir($dir)) {
			wppa_error_message(__('The uploads directory does not exist, please do a regular WP upload first.', 'wppa'));
			return false;
		}
		else {
			wppa_ok_message(__('Successfully created uploads directory.', 'wppa'));
		}
	}	

	// check if wppa dir exists
	$dir = ABSPATH . 'wp-content/uploads/wppa';
	if (!is_dir($dir)) {
		mkdir($dir);
		if (!is_dir($dir)) {
			wppa_error_message(__('Could not create the wppa directory.', 'wppa').wppa_credirmsg($dir));
			return false;
		}
		else {
			wppa_ok_message(__('Successfully created wppa directory.', 'wppa'));
		}
	}
	
	// check if thumbs dir exists 
	$dir = ABSPATH . 'wp-content/uploads/wppa/thumbs';
	if (!is_dir($dir)) {
		mkdir($dir);
		if (!is_dir($dir)) {
			wppa_error_message(__('Could not create the wppa thumbs directory.', 'wppa').wppa_credirmsg($dir));
			return false;
		}
		else {
			wppa_ok_message(__('Successfully created wppa thumbs directory.', 'wppa'));
		}
	}
	
	// check if depot dir exists
	$dir = ABSPATH . 'wp-content/wppa-depot';
	if (!is_dir($dir)) {
		mkdir($dir);
		if (!is_dir($dir)) {
			wppa_error_message(__('Unable to create depot directory', 'wppa').wppa_credirmsg($dir));
			return false;
		}
		else {
			wppa_ok_message(__('Successfully created wppa master depot directory.', 'wppa'));
		}
	}
	
	// check if users depot dir exists
	$dir = ABSPATH . 'wp-content/wppa-depot/'.wppa_get_user();
	if (!is_dir($dir)) {
		mkdir($dir);
		if (!is_dir($dir)) {
			wppa_error_message(__('Unable to create user depot directory.', 'wppa').wppa_credirmsg($dir));
			return false;
		}
		else {
			wppa_ok_message(__('Successfully created wppa user depot directory.', 'wppa'));
		}
	}
	
	return true;
}
function wppa_credirmsg($dir) {
	$msg = ' '.sprintf(__('Ask your administrator to give you more rights, try CHMOD from table VII item 1 of the Photo Albums -> Settings admin page or create <b>%s</b> manually using an FTP program.', 'wppa'), $dir);
	return $msg;
}

// Remove photo entries that have no fullsize image or thumbnail
function wppa_cleanup_photos($alb = '') {
	global $wpdb;
	if ($alb == '') $alb = wppa_get_last_album();
	if (!is_numeric($alb)) return;

	$no_photos = '';
//	if ($alb == '0') wppa_ok_message(__('Checking database, please wait...', 'wppa'));
	$delcount = 0;
	if ($alb == '0') $entries = $wpdb->get_results('SELECT id, ext, name FROM '.WPPA_PHOTOS, ARRAY_A);
	else $entries = $wpdb->get_results('SELECT id, ext, name FROM '.WPPA_PHOTOS.' WHERE album = '.$alb, ARRAY_A);
	if ($entries) {
		foreach ($entries as $entry) {
			$thumbpath = ABSPATH.'wp-content/uploads/wppa/thumbs/'.$entry['id'].'.'.$entry['ext'];
			$imagepath = ABSPATH.'wp-content/uploads/wppa/'.$entry['id'].'.'.$entry['ext'];
			if (!is_file($thumbpath)) {	// No thumb: delete fullimage
				if (is_file($imagepath)) unlink($imagepath);
				$no_photos .= ' '.$entry['name'];
			}
			if (!is_file($imagepath)) { // No fullimage: delete db entry
				if ($wpdb->query($wpdb->prepare('DELETE FROM `'.WPPA_PHOTOS.'` WHERE `id` = %s LIMIT 1', $entry['id']))) {
					$delcount++;
				}
			}
		}
	}
	// Now fix missing exts for upload bug in 2.3.0
	$fixcount = 0;
	$entries = $wpdb->get_results('SELECT id, ext, name FROM '.WPPA_PHOTOS.' WHERE ext = ""', ARRAY_A);
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
				if ($img[2] == 3) $ext = 'png';
			}
			if ($ext == 'jpg' || $ext == 'JPG' || $ext == 'png' || $ext == 'PNG' || $ext == 'gif' || $ext == 'GIF') {
				
				if ($wpdb->query('UPDATE '.WPPA_PHOTOS.' SET ext = "'.$ext.'" WHERE id = '.$entry['id'])) {
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
	
	// Now fix orphan photos
	$orphcount = 0;
	$entries = $wpdb->get_results('SELECT id FROM '.WPPA_PHOTOS.' WHERE album = 0', ARRAY_A);
	if ($entries) {
		$album = wppa_get_album_id(__('Orphan Photos', 'wppa'));
		if ($album == '') {
			$query = $wpdb->prepare('INSERT INTO `' . WPPA_ALBUMS . '` (`id`, `name`, `description`, `a_order`, `a_parent`, `p_order_by`, `main_photo`, `cover_linkpage`, `owner`) VALUES (0, %s, %s, %s, %s, %s, %s, %s, %s)', __('Orphan Photos', 'wppa'), $desc, '0', '0', '0', '0', '0', 'admin');
			$iret = $wpdb->query($query);
			if ($iret === false) {
				wppa_error_message('Could not create album: Orphan Photos', 'wppa');
			}
			else {
				wppa_ok_message('Album: Orphan Photos created.', 'wppa');
			}
			$album = wppa_get_album_id(__('Orphan Photos', 'wppa')); // retry
		}
		if ($album) {
			$orphcount = $wpdb->query('UPDATE '.WPPA_PHOTOS.' SET album = '.$album.' WHERE album < 1');
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
}


function wppa_walktree($relroot, $source) {

	if ($relroot == $source) $sel=' selected="selected"'; else $sel = ' ';
	echo('<option value="'.$relroot.'"'.$sel.'>'.$relroot.'</option>');
	
	if ($handle = opendir(ABSPATH.$relroot)) {
		while (false !== ($file = readdir($handle))) {
			if (($file) != "." && ($file) != ".." && ($file) != "wppa") {
				$newroot = $relroot.'/'.$file;
				if (is_dir(ABSPATH.$newroot)) {	
					wppa_walktree($newroot, $source);
				}
			}
		}
		closedir($handle);
	}
}

function wppa_is_id_free($type, $id) {
global $wpdb;
	if (!is_numeric($id)) return false;
	if ($id == '0') return false;
	
	$table = '';
	if ($type == 'album') $table = WPPA_ALBUMS;
	if ($type == 'photo') $table = WPPA_PHOTOS;
	if ($table == '') {
		echo('Unexpected error in wppa_is_id_free()');
		return false;
	}
	$res = $wpdb->get_row('SELECT * FROM '.$table.' WHERE id = '.$id, 'ARRAY_A');
	if ($res) return false;
	return true;
}

function wppa_sanitize_files($user) {

	// Get this users depot directory
	$depot = ABSPATH.'wp-content/wppa-depot/'.$user;
	// See what's in there
	$paths = $depot.'/*.*';
	$files = glob($paths);
	$allowed_types = array('zip', 'jpg', 'png', 'gif', 'amf', 'pmf', 'bak');

	$count = '0';
	if ($files) foreach ($files as $file) {
		if (is_file($file)) {
			$ext = strtolower(substr(strrchr($file, "."), 1));
			if (!in_array($ext, $allowed_types)) {
				unlink($file);
				wppa_error_message(sprintf(__('File %s is of an unsupported filetype and has been removed.', 'wppa'), basename($file)));
//echo($file); echo('<br>');
				$count++;
			}
		}
	}
	return $count;
}

// get select form element listing albums 
function wppa_album_select($exc = '', $sel = '', $addnone = FALSE, $addseparate = FALSE, $checkancestors = FALSE, $none_is_all = false) {
	global $wpdb;
	$albums = $wpdb->get_results("SELECT * FROM ".WPPA_ALBUMS." ORDER BY name", 'ARRAY_A');
	
    if ($sel == '') {
        $s = wppa_get_last_album();
        if ($s != $exc) $sel = $s;
    }
    
    $result = '';
    if ($addnone) {
		if ($none_is_all) $result .= '<option value="0">' . __('--- all ---', 'wppa') . '</option>';
		else $result .= '<option value="0">' . __('--- none ---', 'wppa') . '</option>';
	}
    
	foreach ($albums as $album) if (wppa_have_access($album)) {
		if ($sel == $album['id']) { 
            $selected = ' selected="selected" '; 
        } 
        else { $selected = ''; }
		if ($album['id'] != $exc && (!$checkancestors || !wppa_is_ancestor($exc, $album['id']))) {
			$result .= '<option value="' . $album['id'] . '"' . $selected . '>'.wppa_qtrans(stripslashes($album['name'])).'</option>';
		}
		else {
			$result .= '<option disabled="disabled" value="-3">'.wppa_qtrans(stripslashes($album['name'])).'</option>';
		}
	}
    
    if ($sel == -1) $selected = ' selected="selected" '; else $selected = '';
    if ($addseparate) $result .= '<option value="-1"' . $selected . '>' . __('--- separate ---', 'wppa') . '</option>';
	return $result;
}

