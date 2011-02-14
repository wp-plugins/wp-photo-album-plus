<?php
/* wppa_adminfunctions.php
* Pachkage: wp-photo-album-plus
*
* gp admin functions
* version 2.5.0
*/

// Set default option values
function wppa_set_defaults($force = false) {
	$defaults = array ( 'wppa_revision' => '100',
						'wppa_fullsize' => '640',
						'wppa_colwidth' => get_option('wppa_fullsize', '640'),
						'wppa-maxheight' => get_option('wppa_fullsize', '640'),
						'wppa_enlarge' => 'no',
						'wppa_resize_on_upload' => 'no',
						'wppa_fullvalign' => 'fit',
						'wppa_min_thumbs' => '1',
						'wppa_valign' => 'center',
						'wppa_thumbsize' => '100',
						'wppa_tf_width' => get_option('wppa_thumbsize', '100'),
						'wppa_tf_height' => (get_option('wppa_thumbsize', '100') + '10'),
						'wppa_tn_margin' => '4',
						'wppa_smallsize' => '150',
						'wppa_show_bread' => 'yes',
						'wppa_show_home' => 'yes',
						'wppa_bc_separator' => 'raquo',
						'wppa_use_thumb_opacity' => 'yes',
						'wppa_thumb_opacity' => '85',
						'wppa_use_thumb_popup' => 'yes',
						'wppa_use_cover_opacity' => 'yes',
						'wppa_cover_opacity' => '85',
						'wppa_animation_speed' => '600',
						'wppa_bgcolor_even' => '#eeeeee',
						'wppa_bgcolor_alt' => '#dddddd',
						'wppa_bgcolor_nav' => '#dddddd',
						'wppa_bgcolor_img' => '#eeeeee',
						'wppa_bcolor_even' => '#cccccc',
						'wppa_bcolor_alt' => '#bbbbbb',
						'wppa_bcolor_nav' => '#bbbbbb'
						);
	
	array_walk($defaults, 'wppa_set_default', $force);
}
function wppa_set_default($value, $key, $force) {
	if ($force) {
		update_option($key, $value);
	}
	else {
//echo "Checking ".$key." to be ".$value."<br/>";
		if (get_option($key, 'nil') == 'nil') update_option($key, $value);
	}
}

// update all thumbs 
function wppa_regenerate_thumbs() {
	global $wpdb;
	
	$thumbsize = wppa_get_minisize();
	$wppa_dir = ABSPATH . 'wp-content/uploads/wppa/';

	@define('WP_DEBUG', true);	
	
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

// Create thumbnail
function wppa_create_thumbnail( $file, $max_side, $effect = '') {
	if (file_exists($file)) {
		$img_size = getimagesize( $file );
		$dir = $img_size[0] > $img_size[1] ? 'W' : 'H';
		$thumb = 'thumbs/' . basename( $file );
		$thumbpath = str_replace( basename( $file ), $thumb, $file );

		require_once('wppa_class_resize.php');		
		$objResize = new wppa_ImageResize($file, $thumbpath, $dir, $max_side);
	}
	else {
		return false;
	}
}

function wppa_check_update() {
global $wppa_revno;
	if (get_option('wppa_revision', '100') < $wppa_revno) {
		wppa_error_message(__('The wppa database tables are not yet at the required revision level.', 'wppa').' '.__('Current=', 'wppa').get_option('wppa_revision', '100').' '.__('New=', 'wppa').$wppa_revno);
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
function wppa_order_options($order, $nil, $rat) {
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
	$result = ceil($result / 25) * 25;
	return $result;
}

// See if an album or any album is accessable for the current user
function wppa_have_access($alb) {
global $wpdb;
global $current_user;
	// See if there is any album accessable
	if ($alb == 'any') {
		// Administrator has always access OR If all albums are public
		if (current_user_can('administrator') || get_option('wppa_owner_only', 'no') == 'no') {
			$albs = $wpdb->get_results('SELECT id FROM '.ALBUM_TABLE);
			if ($albs) return true;
			else return false;	// No albums in system
		}
		else {
			get_currentuserinfo();
			$user = $current_user->user_login;
			$albs = $wpdb->get_results('SELECT id FROM '.ALBUM_TABLE.' WHERE owner = "'.$user.'"');
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
			$owner = $wpdb->get_var('SELECT owner FROM '.ALBUM_TABLE.' WHERE id = '.$alb);
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
//	foreach ($users as $usr) {
//		echo($usr['user_login'].'='.$usr['display_name'].'<br/>');
//	}
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