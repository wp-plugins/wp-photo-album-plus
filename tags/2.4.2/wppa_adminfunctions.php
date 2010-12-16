<?php
/* wppa_adminfunctions.php
* Pachkage: wp-photo-album-plus
*
* gp admin functions
* version 2.4.2
*/

// update all thumbs 
function wppa_regenerate_thumbs() {
	global $wpdb;
	
	$thumbsize = wppa_get_minisize();
	$wppa_dir = ABSPATH . 'wp-content/uploads/wppa/';

	if (!defined('WP_DEBUG')) define('WP_DEBUG', true);	
	
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

// Moved here from wppa_functions.php:

// The following routines belong perhaps in wppa_adminfunctions.php
// set last album 
function wppa_set_last_album($id = '') {
    global $albumid;
    
    if (is_numeric($id)) $albumid = $id; else $albumid = '';
    update_option('wppa_last_album_used', $albumid);
}

// get last album
function wppa_get_last_album() {
    global $albumid;
    
    if (is_numeric($albumid)) $result = $albumid;
    else $result = get_option('wppa_last_album_used');
    if (!is_numeric($result)) $result = '';
    else $albumid = $result;

	return $result; 
}

// display order options
function wppa_order_options($order, $nil) {
    if ($nil != '') { ?>
        <option value="0"<?php if ($order == "" || $order == "0") echo (' selected="selected"'); ?>><?php echo($nil); ?></option>
<?php }
?>
    <option value="1"<?php if ($order == "1") echo(' selected="selected"'); ?>><?php _e('Order #', 'wppa'); ?></option>
    <option value="2"<?php if ($order == "2") echo(' selected="selected"'); ?>><?php _e('Name', 'wppa'); ?></option>
    <option value="3"<?php if ($order == "3") echo(' selected="selected"'); ?>><?php _e('Random', 'wppa'); ?></option>  
<?php
}

// These also in admin?
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

// End in admin?

