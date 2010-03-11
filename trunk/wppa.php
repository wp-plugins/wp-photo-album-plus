<?php
/*
Plugin Name: WP Photo Album Plus
Description: Easily manage and display your photo albums and slideshows within your WordPress site.
Version: 1.8
Author: J.N. Breetvelt a.k.a OpaJaap
Author URI: http://www.opajaap.nl/
Plugin URI: http://wordpress.org/extend/plugins/wp-photo-album-plus/
*/

load_plugin_textdomain('wppa', 'wp-content/plugins/wp-photo-album-plus/langs/', 'wp-photo-album-plus/langs/');

/* GLOBAL SETTINGS */
global $wpdb;
define('ALBUM_TABLE', $wpdb->prefix . 'wppa_albums');
define('PHOTO_TABLE', $wpdb->prefix . 'wppa_photos');
define('PLUGIN_PATH', 'wp-photo-album-plus');

/* FORM SECURITY */
if ( !function_exists('wp_nonce_field') ) {
        function wppa_nonce_field($action = -1) { return; }
        $wppa_nonce = -1;
} else {
		function wppa_nonce_field($action = -1,$name = 'wppa-update-check') { return wp_nonce_field($action,$name); }
		define('WPPA_NONCE' , 'wppa-update-check');
}

/* SETUP */
// calls the setup function on activation
register_activation_hook( __FILE__, 'wppa_setup' );


// does the initial setup
function wppa_setup() {
	global $wpdb;
	
	if (get_option('wppa_revision', '100') < '170') {
		
	$create_albums = "CREATE TABLE " . ALBUM_TABLE . " (
                    id bigint(20) NOT NULL auto_increment, 
                    name text NOT NULL, 
                    description text NOT NULL, 
                    a_order smallint(5) unsigned NOT NULL, 
                    main_photo bigint(20) NOT NULL, 
                    a_parent bigint(20) NOT NULL,
                    p_order_by int unsigned NOT NULL,
                    PRIMARY KEY  (id) 
                    );";
                    
	$create_photos = "CREATE TABLE " . PHOTO_TABLE . " (
                    id bigint(20) NOT NULL auto_increment, 
                    album bigint(20) NOT NULL, 
                    ext tinytext NOT NULL, 
                    name text NOT NULL, 
                    description longtext NOT NULL, 
                    p_order smallint(5) unsigned NOT NULL,
                    PRIMARY KEY  (id) 
                    );";

    require_once(ABSPATH . 'wp-admin/upgrade-functions.php');

    dbDelta($create_albums);
    dbDelta($create_photos);
	
	if (!is_numeric(get_option('wppa_thumbsize', 'nil'))) update_option('wppa_thumbsize', '130');
	
	update_option('wppa_revision', '170');
	}
}

/* ADMIN MENU */
function wppa_add_admin() {
	$level = get_option('wppa-accesslevel');
	if (empty($level)) { $level = 'level_10'; }
	
	$iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . PLUGIN_PATH . '/images/camera16.png';
	
	add_menu_page('WP Photo Album', __('Photo Albums', 'wppa'), $level, __FILE__, 'wppa_admin', $iconurl);
	
    add_submenu_page(__FILE__, __('Upload Photos', 'wppa'), __('Upload Photos', 'wppa'), $level, 'upload_photos', 'wppa_page_upload');
    add_submenu_page(__FILE__, __('Settings', 'wppa'), __('Settings', 'wppa'), $level, 'options', 'wppa_page_options');
	add_submenu_page(__FILE__, __('Sidebar Widget', 'wppa'), __('Sidebar Widget', 'wppa'), $level, 'wppa_sidebar_options', 'wppa_sidebar_page_options');
    add_submenu_page(__FILE__, __('Help &amp; Info', 'wppa'), __('Help &amp; Info', 'wppa'), $level, 'wppa_help', 'wppa_page_help');
}

add_action('admin_menu', 'wppa_add_admin');
add_action('plugins_loaded', 'init_wppa_widget');

function init_wppa_widget() {
	register_sidebar_widget('Photo Album Widget', 'show_wppa_widget');
}

function show_wppa_widget($args) {
	global $wpdb;
	extract($args);
	
	$widget_title = get_option('wppa_widgettitle', __('Photo of the day', 'wppa'));
	
	$wid = get_option('wppa_widget_width', '150');
	// get the photo id
	switch (get_option('wppa_widget_method', '1')) 
	{
	case '1':	// Fixed photo
		$id = get_option('wppa_widget_photo', '');
		if ($id != '') {
			// get the photo
			$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE id=$id LIMIT 0,1", 'ARRAY_A');
			if (!empty($image)) {
				$imgurl = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/' . $image['id'] . '.' . $image['ext'];
				$widget_content = '<div class="wppa-widget"><img src="' . $imgurl . '" style="width: ' . $wid . 'px;" ></div>';
				}
			else $widget_content = __('Photo not found (1)');
		}
		else $widget_content = __('Unknown photo (1)');
		break;
	case '2':	// Random
		$album = get_option('wppa_widget_album', '');
		if ($album != '') {
			$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE album=$album  ORDER BY RAND() LIMIT 0,1", 'ARRAY_A');
			if (!empty($image)) {
				$imgurl = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/' . $image['id'] . '.' . $image['ext'];
				$widget_content = '<div class="wppa-widget"><img src="' . $imgurl . '" style="width: ' . $wid . 'px;" ></div>';
				}
			else $widget_content = __('Photo not found (2)');
		}
		else $widget_content = __('Unknown album (2)');
		break;
	case '3':	// Last upload
		$album = get_option('wppa_widget_album', '');
		if ($album != '') {
			$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE album=$album  ORDER BY id DESC LIMIT 0,1", 'ARRAY_A');
			if (!empty($image)) {
				$imgurl = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/' . $image['id'] . '.' . $image['ext'];
				$widget_content = '<div class="wppa-widget"><img src="' . $imgurl . '" style="width: ' . $wid . 'px;" ></div>';
				}
			else $widget_content = __('Photo not found (3)');
		}
		else $widget_content = __('Unknown album (3)');
		break;
	case '4':	// Change every
		$album = get_option('wppa_widget_album', '');
		if ($album != '') {
			$u = date("U"); // Seconds since 1-1-1970
			$u /= 3600;		//  hours since
			$u = floor($u);
			$u /= get_option('wppa_widget_period', '168');
			$u = floor($u);
			$p = wppa_get_photo_count($album);
			if (!is_numeric($p) || $p < 1) $p = '1'; // make sure we dont get overflow in the next line
			$idn = fmod($u, $p);
			$photos = $wpdb->get_results("SELECT * FROM " . PHOTO_TABLE . " WHERE album=$album  " . wppa_get_photo_order($album), 'ARRAY_A');
			$i = 0;
			foreach ($photos as $image) {
				if ($i == $idn) {	// found the idn'th out of p
					$id = $image['id'];
					$ext = $image['ext'];
				}
				$i++;
			}
			if (is_numeric($id)) {
				$imgurl = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/' . $id . '.' . $ext;
				$widget_content = '<div class="wppa-widget"><img src="' . $imgurl . '" style="width: ' . $wid . 'px;" ></div>';
				}
			else {
				$widget_content = __('Photo not found (4)');
			}
		}
		else $widget_content = __('Unknown album (4)');
		break;
	case '5':	// Slideshow
			$widget_content = __('Not implemented yet (5)');
		break;
	case '6':	// Scrollable
			$widget_content = __('Not implemented yet (6)');
		break;	
	}
	switch (get_option('wppa_widget_subtitle', 'none'))
	{
		case 'none': 
			break;
		case 'name': if ($image && $image['name'] != '') $widget_content .= '<div style="text-align: center;">' . $image['name'] . '</div>';
			break;
		case 'desc': if ($image && $image['description'] != '') $widget_content .= '<div style="text-align: center;">' . $image['description'] . '</div>'; 
			break;
	}
	// Display the widget
	echo $before_widget . $before_title . $widget_title . $after_title . $widget_content . $after_widget;
}

/* ADMIN PAGES */
function wppa_admin() {
	global $wpdb;
	
	// warn if the uploads directory is no writable
	if (!is_writable(ABSPATH . 'wp-content/uploads')) { ?>
		<div id="error" class="error">
			<p>
				<strong><?php _e('Warning:', 'wppa'); ?></strong> 
				<?php _e('The uploads directory does not exist or is not writable by the server. Please make sure that <tt>wp-content/uploads/</tt> is writeable by the server.', 'wppa'); ?>
			</p>
		</div>
<?php }

if (isset($_GET['tab'])) {		
	// album edit page
	if ($_GET['tab'] == 'edit'){
	
		// updates the details
		if (isset($_POST['wppa-ea-submit'])) {
			check_admin_referer( '$wppa_nonce', WPPA_NONCE );
			wppa_edit_album();
		}
		
		// deletes the image
		if (isset($_GET['photo_del'])) {
			
			$ext = $wpdb->get_var("SELECT ext FROM " . PHOTO_TABLE . " WHERE id={$_GET['photo_del']}");
			unlink(ABSPATH . 'wp-content/uploads/wppa/' . $_GET['photo_del'] . '.' . $ext);
			unlink(ABSPATH . 'wp-content/uploads/wppa/thumbs/' . $_GET['photo_del'] . '.' . $ext);
			
			$wpdb->query("DELETE FROM " . PHOTO_TABLE . " WHERE id={$_GET['photo_del']} LIMIT 1");
?>			
			<div id="message" class="updated fade"><p><strong><?php _e('Photo Deleted.', 'wppa'); ?></strong></p></div>
<?php
		}		
		
		$albuminfo = $wpdb->get_row("SELECT * FROM " . ALBUM_TABLE . " WHERE id={$_GET['edit_id']} ", 'ARRAY_A');
?>				
		<div class="wrap">
			<h2><?php _e('Edit Ablum Information', 'wppa'); ?></h2>
			<p><?php _e('Album number:', 'wppa'); echo(' ' . $_GET['edit_id'] . '.'); ?></p>
			<form action="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=<?php echo(PLUGIN_PATH) ?>/wppa.php&amp;tab=edit&amp;edit_id=<?php echo($_GET['edit_id']) ?>" method="post">
			<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE); ?>

				<table class="form-table albumtable">
					<tbody>
						<tr valign="top">
							<th scope="row">
								<label for="wppa-name"><?php _e('Name:', 'wppa'); ?></label>
							</th>
							<td>
								<input type="text" name="wppa-name" id="wppa-name" value="<?php echo($albuminfo['name']) ?>" />
								<span class="description">
									<br/><?php _e('Type the name of the album. Do not leave this empty.', 'wppa'); ?>
								</span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="wppa-description"><?php _e('Description:', 'wppa'); ?></label>
							</th>
							<td>
								<textarea rows="5" cols="40" name="wppa-desc" id="wppa-desc"><?php echo($albuminfo['description']) ?></textarea>
								<span class="description">
									<br/><?php _e('Enter / modify the description for this album.', 'wppa'); ?>
								</span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="wppa-order"><?php _e('Sort order #:', 'wppa'); ?></label>
							</th>
							<td>
								<input type="text" name="wppa-order" id="wppa-order" value="<?php echo($albuminfo['a_order']) ?>" style="width: 50px;"/>
								<span class="description">
									<br/><?php _e('If you want to sort the albums by order #, enter / modify the order number here.', 'wppa'); ?>
								</span>
							</td>
						</tr>
						<tr valign="top">
							<th>
								<label for="wppa-parent"><?php _e('Parent album:', 'wppa'); ?> </label>
							</th>
							<td>
								<?php if (wppa_get_album_count($albuminfo["id"])) { ?>
									<?php _e('You can not change the parent of an album that contains sub albums.&nbsp;', 'wppa'); ?> 
									<span class="description">
										<br/><?php _e('This is to prevent circular references. Change the sub albums parent id first.', 'wppa'); ?>
									</span>
								<?php } else { ?>
									<select name="wppa-parent"><?php echo(wppa_album_select("", $albuminfo["a_parent"], TRUE, TRUE)) ?></select>
									<span class="description">
										<br/><?php _e('If this is a sub album, select the album in which this album will appear.', 'wppa'); ?>
									</span>
								<?php } ?>					
							</td>
						</tr>
						<tr valign="top">
							<th>
								<?php $order = $albuminfo['p_order_by']; ?>
								<label for="wppa-list-photos-by"><?php _e('Photo order:', 'wppa'); ?></label>
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
								<label for="wppa-main"><?php _e('Cover Photo:', 'wppa'); ?></label>
							</th>
							<td>
								<?php echo(wppa_main_photo($albuminfo['main_photo'])) ?>
								<span class="description">
									<br/><?php _e('Select the photo you want to appear on the cover of this album.', 'wppa'); ?>
								</span>
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
			<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . PLUGIN_PATH . '/images/albumdel32.png'; ?>
			<div id="icon-albumdel" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
				<br/>
			</div>

			<h2><?php _e('Delete Album', 'wppa'); ?></h2>
			
			<p><?php _e('Album:', 'wppa'); ?> <b><?php wppa_album_name($_GET['id']); ?>.</b></p>
			<p><?php _e('Are you sure you want to delete this album?', 'wppa'); ?><br />
				<?php _e('Press Delete to continue, and Cancel to go back.', 'wppa'); ?>
			</p>
			<form name="wppa-del-form" action="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=<?php echo(PLUGIN_PATH) ?>/wppa.php" method="post">
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
			check_admin_referer( '$wppa_nonce', WPPA_NONCE );
			wppa_add_album();
		}
		
		// if album deleted
		if (isset($_POST['wppa-del-confirm'])) {
			check_admin_referer( '$wppa_nonce', WPPA_NONCE );

			if ($_POST['wppa-del-photos'] == 'move') {
				$move = $_POST['wppa-move-album'];
			} else {
				$move = '';
			}
			wppa_del_album($_POST['wppa-del-id'], $move);
		}
?>		
		<div class="wrap">
			<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . PLUGIN_PATH . '/images/album32.png'; ?>
			<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
				<br/>
			</div>

			<h2><?php _e('Manage Albums', 'wppa'); ?></h2>
			<?php wppa_admin_albums() ?>
			
			<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . PLUGIN_PATH . '/images/albumnew32.png'; ?>
			<div id="icon-albumnew" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
				<br/>
			</div>

			<h2><?php _e('Create New Album', 'wppa'); ?></h2>
			<form action="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=<?php echo(PLUGIN_PATH) ?>/wppa.php" method="post">
			<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE) ?>
				<table class="form-table albumtable">
					<tbody>
						<tr valign="top">
							<th scope="row">
								<label for="wppa-name"><?php _e('Name:', 'wppa'); ?></label>
							</th>
							<td>
								<input type="text" name="wppa-name" id="wppa-name" />
								<span class="description">
									<br/><?php _e('Type the name of the new album. Do not leave this empty.', 'wppa'); ?>
								</span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="wppa-description"><?php _e('Description:', 'wppa'); ?></label>
							</th>
							<td>
								<textarea rows="5" cols="40" name="wppa-desc" id="wppa-desc"></textarea>
								<span class="description">
									<br/>
								</span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="wppa-name"><?php _e('Order #:', 'wppa'); ?></label>
							</th>
							<td>
								<input type="text" name="wppa-order" id="wppa-order" style="width: 50px;"/>
								<span class="description">
									<br/><?php _e('If you want to sort the albums by order #, enter the order number here.', 'wppa'); ?>
								</span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="wppa-parent"><?php _e('Parent album:', 'wppa'); ?></label>
							</th>
							<td>
								<select name="wppa-parent"><?php echo(wppa_album_select('', '', TRUE, TRUE)) ?></select>
								<span class="description">
									<br/><?php _e('If this is a sub album, select the album in which this album will appear.', 'wppa'); ?>
								</span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="wppa-photo-order-by"><?php _e('Order photos by:', 'wppa'); ?></label>
							</th>
							<td>
								<select name="wppa-photo-order-by"><?php wppa_order_options('0', __('--- default ---', 'wppa')) ?></select>
								<span class="description">
									<br/><?php _e('If you want to sort the photos in this album different from the system setting, select the order method here.', 'wppa'); ?>
								</span>
							</td>
						</tr>
						<tr valighn="top">
							<th scope="row">
								<input type="submit" class="button-primary" name="wppa-na-submit" value="<?php _e('Create Album!', 'wppa'); ?>" />
							</th>
							<td>
								<span class="description">
									<?php _e('You can change all these settings later by clicking the "Edit" link in the table above.', 'wppa'); ?>
								</span>
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
        
		if (isset($_POST['wppa-upload'])) {
			check_admin_referer( '$wppa_nonce', WPPA_NONCE );
            
			wppa_upload_photos();
		}
?>
		<div class="wrap">
			<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . PLUGIN_PATH . '/images/camera32.png'; ?>
			<div id="icon-camera" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
				
			</div>
			<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . PLUGIN_PATH . '/images/arrow32.png'; ?>
			<div id="icon-arrow" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
			</div>
			<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . PLUGIN_PATH . '/images/album32.png'; ?>
			<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
				<br/>
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
					<input type="submit" name="wppa-upload" value="Upload Photos" />
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
				<p><?php _e('No albums exist. You must', 'wppa'); ?> <a href="admin.php?page=<?php echo(PLUGIN_PATH) ?>/wppa.php"><?php _e('create one', 'wppa'); ?></a> <?php _e('beofre you can upload your photos.', 'wppa'); ?></p>
<?php } ?>
		</div>
<?php
}

function wppa_page_options() {
	$options_error = false;
	
	if (isset($_POST['wppa-set-submit'])) {
		check_admin_referer( '$wppa_nonce', WPPA_NONCE );

		if (($_POST['wppa-thumbsize'] != get_option('wppa_thumbsize')) && is_numeric($_POST['wppa-thumbsize'])) {
			update_option('wppa_thumbsize', $_POST['wppa-thumbsize']);
			update_option('wppa_lastthumb', '-1');
		}
		elseif (!is_numeric($_POST['wppa-thumbsize'])) {
			wppa_error_message(__('Please supply a numeric value for Thumbnail size.'));
			$options_error = true;
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
			wppa_updat_message(__('READY regenerating thumbnail images.', 'wppa')); 				
			update_option('wppa_lastthumb', '-2');
		}
		
		if (is_numeric($_POST['wppa-min-thumbs'])) update_option('wppa_min_thumbs', $_POST['wppa-min-thumbs']);
		else {
			wppa_error_message(__('Please supply a numeric value for Photocount treshold.', 'wppa'));
			$options_error = true;
		}
		
		if (is_numeric($_POST['wppa-fullsize'])) update_option('wppa_fullsize', $_POST['wppa-fullsize']);
		else {
			wppa_error_message(__('Please supply a numeric value for Full size.', 'wppa'));
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
	
		update_option('wppa-accesslevel', $_POST['wppa-accesslevel']);
		
		if (!$options_error) wppa_update_message(__('Changes Saved', 'wppa')); 
	}
    elseif (get_option('wppa_lastthumb', '-2') != '-2') wppa_error_message(__('Regeneration of thumbnail images interrupted. Please press "Save Changes"', 'wppa')); 
?>		
	<div class="wrap">
		<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . PLUGIN_PATH . '/images/settings32.png'; ?>
		<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
			<br/>
		</div>
		<h2><?php _e('WP Photo Album Plus Settings', 'wppa'); ?></h2>
		<p><?php _e('Database revision:', 'wppa'); ?> <?php echo(get_option('wppa_revision', '100')) ?>.</p><br/>
		<form action="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=options" method="post">
	
			<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE); ?>

			<table class="form-table albumtable">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="wppa-thumbsize"><?php _e('Thumbnail Size:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-thumbsize" id="wppa-tumbsize" value="<?php echo(get_option('wppa_thumbsize', '130')) ?>" style="width: 50px;" />
							<span class="description">
								<br/><?php _e('Changing the thumbnail size will result in all thumbnails being regenerated. this may take a while.', 'wppa'); ?>
							</span>
						</td>
					<tr/>
					<tr valign="top">
						<th scope="row">
							<label for="wppa-min-thumbs"><?php _e('Photocount treshold:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-min-thumbs" id="wppa-min-thumbs" value="<?php echo(get_option('wppa_min_thumbs', '1')) ?>" style="width: 50px;" />
							<span class="description">
								<br/><?php _e('Photos do not show up in the album unless there are more than this number of photos in the album. This allows you to have cover photos on an album that contains only sub albums without seeing them in the list of sub albums. Usually set to 0 (always show) or 1 (for one cover photo).', 'wppa'); ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wppa-fullsize"><?php _e('Full Size:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-fullsize" id="wppa-fullsize" value="<?php echo(get_option('wppa_fullsize')) ?>" style="width: 50px;" />
							<span class="description">
								<br/><?php _e('The size of the full images is controled with html, the photo itself will not be resized.', 'wppa'); ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wppa-enlarge"><?php _e('Enlarge if needed:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-enlarge" id="wppa-enlarge" <?php if (get_option('wppa_enlarge', 'yes') == 'yes') echo ('checked="checked"') ?> />
							<span class="description">
								<br/><?php _e('Fullsize images will be enlarged to the Full Size if needed. Leaving unchecked is recommended. It is better to upload photos that fit well the sizes you use!', 'wppa'); ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wppa-list-albums-by"><?php _e('Album order:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $order = get_option('wppa_list_albums_by'); ?>
							<select name="wppa-list-albums-by"><?php wppa_order_options($order, __('--- none ---', 'wppa')); ?></select>
							<span class="description">
								<br/><?php _e('Specify the way the albums should be ordered.', 'wppa'); ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wppa-list-albums-desc"><?php _e('Descending:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-list-albums-desc" id="wppa-list-albums-desc" <?php if (get_option('wppa_list_albums_desc') == 'yes') echo('checked="checked"') ?> />
							<span class="description">
								<br/><?php _e('If checked: largest first', 'wppa'); ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wppa-list-photos-by"><?php _e('Photo order:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $order = get_option('wppa_list_photos_by'); ?>
							<select name="wppa-list-photos-by"><?php wppa_order_options($order, __('--- none ---', 'wppa')); ?></select>
							<span class="description">
								<br/><?php _e('Specify the way the photos should be ordered. This is the default setting. You can overrule the default sorting order on a per album basis.', 'wppa'); ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wppa-list-photos-desc"><?php _e('Descending:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="checkbox" name="wppa-list-photos-desc" id="wppa-list-photos-desc" <?php if (get_option('wppa_list_photos_desc') == 'yes') echo (' checked="checked"') ?> />
							<span class="description">
								<br/><?php _e('This is a system wide setting.', 'wppa'); ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wppa-accesslevel"><?php _e('Access Level:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $level = get_option('wppa-accesslevel'); ?>
							<?php $sel = 'selected="selected"'; ?>
							<select name="wppa-accesslevel">
								<option value="level_10" <?php if ($level == 'level_10') echo($sel); ?>><?php _e('Administrator', 'wppa'); ?></option> 
								<option value="level_7" <?php if ($level == 'level_7') echo($sel); ?>><?php _e('Editor', 'wppa'); ?></option>
								<option value="level_2" <?php if ($level == 'level_2') echo($sel); ?>><?php _e('Author', 'wppa'); ?></option>
								<option value="level_1" <?php if ($level == 'level_1') echo($sel); ?>><?php _e('Contributor', 'wppa'); ?></option>				
							</select>
							<span class="description">
								<br/><?php _e('The minmum user level that can access the photo album admin.', 'wppa'); ?>
							</span>
						</td>
					</tr>						
				</tbody>
			</table>

			<br/>
			<p>
				<input type="submit" class="button-primary" name="wppa-set-submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
			</p>
		</form>
	</div>
<?php 
}

function wppa_sidebar_page_options() {
	global $wpdb;
	$options_error = false;
	
	if (isset($_GET['walbum'])) update_option('wppa_widget_album', $_GET['walbum']);
		
	if (isset($_POST['wppa-set-submit'])) {
		check_admin_referer( '$wppa_nonce', WPPA_NONCE );
		
		update_option('wppa_widgettitle', $_POST['wppa-widgettitle']);
		
		if (is_numeric($_POST['wppa-widget-width'])) update_option('wppa_widget_width', $_POST['wppa-widget-width']);
		else {
			wppa_error_message(__('Please supply a numeric value for Widget Photo Width.', 'wppa'));
			$options_error = true;
		}
		if (isset($_POST['wppa-widget-album'])) update_option('wppa_widget_album', $_POST['wppa-widget-album']);
		if (isset($_POST['wppa-widget-photo'])) update_option('wppa_widget_photo', $_POST['wppa-widget-photo']);
		if (isset($_POST['wppa-widget-method'])) update_option('wppa_widget_method', $_POST['wppa-widget-method']);
		if (isset($_POST['wppa-widget-period'])) update_option('wppa_widget_period', $_POST['wppa-widget-period']);
		if (isset($_POST['wppa-widget-subtitle'])) update_option('wppa_widget_subtitle', $_POST['wppa-widget-subtitle']);
		
		if (!$options_error) wppa_update_message(__('Changes Saved. Don\'t forget to activate the widget!', 'wppa')); 
	}

?>
	<div class="wrap">
		<?php $iconurl = get_bloginfo('wpurl') . '/wp-content/plugins/' . PLUGIN_PATH . '/images/settings32.png'; ?>
		<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
			<br/>
		</div>
		<h2><?php _e('WP Photo Album Plus Sidebar Widget Settings', 'wppa'); ?></h2>
		
		<form action="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=wppa_sidebar_options" method="post">
			<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE); ?>

			<table class="form-table albumtable">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="wppa-widgettitle"><?php _e('Widget Title:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-widgettitle" id="wppa-widgettitle" value="<?php echo(get_option('wppa_widgettitle', __('Photo of the day', 'wppa'))); ?>" />
							<span class="description">
								<br/><?php _e('Enter the caption to be displayed for the widget.', 'wppa'); ?>
							</span>
						</td>
					</tr>				
					<tr valign="top">
						<th scope="row">
							<label for="wppa-widget-width"><?php _e('Widget Photo Width:', 'wppa'); ?></label>
						</th>
						<td>
							<input type="text" name="wppa-widget-width" id="wppa-widget-width" value="<?php echo(get_option('wppa_widget_width', '150')); ?>" style="width: 50px;" />
							<span class="description">
								<br/><?php _e('Enter the desired display width of the photo in the sidebar.', 'wppa'); ?>
							</span>
						</td>
					<tr/>
					<tr valign="top">
						<th scope="row">
							<label for="wppa-widget-album"><?php _e('Use album:', 'wppa'); ?></label>
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
							<span class="description">
								<br/><?php _e('Select the album that contains the widget photos.', 'wppa'); ?>
							</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wppa-widget-method"><?php _e('Display method:', 'wppa'); ?></label>
						</th>
						<td>
							<?php $sel = 'selected="selected"'; ?>
							<script type="text/javascript">
							/* <![CDATA[ */
							function wppaCheckWm() {
								var ph;
								var i;
								if (document.getElementById("wppa-wm").value=="4") {
									document.getElementById("wppa-wp").style.visibility="visible";
								}
								else {
									document.getElementById("wppa-wp").style.visibility="hidden";
								}
								if (document.getElementById("wppa-wm").value=="1") {
									ph=document.getElementsByName("wppa-widget-photo");
									i=0;
									while (i<ph.length) {
										ph[i].style.visibility="visible";
										i++;	
									}
								}
								else {
									ph=document.getElementsByName("wppa-widget-photo");
									i=0;
									while (i<ph.length) {
										ph[i].style.visibility="hidden";
										i++;
									}
								}
							}
							/* ]]> */
							</script>
							<?php $method = get_option('wppa_widget_method', '1'); ?>
							<select name="wppa-widget-method" id="wppa-wm" onchange="wppaCheckWm()"; >
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
							<span class="description">
								<br/><?php _e('Select how the widget should display.', 'wppa'); ?>
							</span>	
							
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="wppa-widget-subtitle"><?php _e('Subtitle:', 'wppa'); ?></label>
						</th>
						<td>
							<script type="text/javascript">
							/* <![CDATA[ */
							function wppaCheckWs() {
								var subtitle = document.getElementById('wppa-st').value;
								var stn, std;
								var i;
								stn = document.getElementsByName('wsubtitname');
								std = document.getElementsByName('wsubtitdesc');
								i = 0;
								switch (subtitle)
								{
								case 'none':
									while (i < stn.length) {
										stn[i].style.visibility = "hidden";
										std[i].style.visibility = "hidden";
										i++;
									}
									break;
								case 'name':
									while (i < stn.length) {
										stn[i].style.visibility = "visible";
										std[i].style.visibility = "hidden";
										i++;
									}
									break;
								case 'desc':
									while (i < stn.length) {
										stn[i].style.visibility = "hidden";
										std[i].style.visibility = "visible";
										i++;
									}
									break;
								}
							}
							/* ]]> */
							</script>
							<?php $subtit = get_option('wppa_widget_subtitle', 'none'); ?>
							<select name="wppa-widget-subtitle" id="wppa-st" onchange="wppaCheckWs()" >
								<option value="none" <?php if ($subtit == 'none') echo($sel); ?>><?php _e('--- none ---', 'wppa'); ?></option>
								<option value="name" <?php if ($subtit == 'name') echo($sel); ?>><?php _e('Photo Name', 'wppa'); ?></option>
								<option value="desc" <?php if ($subtit == 'desc') echo($sel); ?>><?php _e('Description', 'wppa'); ?></option>
							</select>
							<span class="description">
								<br/><?php _e('Select the content of the subtitle.', 'wppa'); ?>
							</span>	
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
				$wi = get_option('wppa_thumbsize', '130') + 24;
				foreach ($photos as $photo) {
?>
					<div class="photoselect" style="width: <?php echo(get_option('wppa_widget_width', '150')); ?>px; height: <?php echo($wi); ?>px;" >
						<img src="<?php echo(get_bloginfo('wpurl') . '/wp-content/uploads/wppa/thumbs/' . $photo['id'] . '.' . $photo['ext']); ?>" ></img>
						<input type="radio" name="wppa-widget-photo" id="wppa-widget-photo<?php echo($photo['id']); ?>" value="<?php echo($photo['id']) ?>" <?php if ($photo['id'] == $id) echo('checked="checked"'); ?>/>
						<div class="clear"></div>
						<div name="wsubtitname" style="position: absolute; top:<?php echo( $wi - 12 ); ?>px;"><?php echo($photo['name']); ?></div>
						<div name="wsubtitdesc" style="position: absolute; top:<?php echo( $wi - 12 ); ?>px;"><?php echo($photo['description']); ?></div>
					</div>
<?php		
				}
?>
					<div class="clear"></div>
<?php
			}
?>
			<script type="text/javascript">wppaCheckWm();</script>
			<script type="text/javascript">wppaCheckWs();</script>
			<br/>
			<p>
				<input type="submit" class="button-primary" name="wppa-set-submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
			</p>
		</form>
	</div>
<?php
}

function wppa_page_help() {

?>
	<div class="wrap">
<?php 
		$iconurl = "http://www.gravatar.com/avatar/b421f77aa39db35a5c1787240c77634f?s=32&amp;d=http%3A%2F%2Fwww.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D32&amp;r=G";
?>		
		<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
			<br/>
		</div>
		<h2><?php _e('Help and Information', 'wppa'); ?></h2>
		
		<h3><?php _e('Plugin Description', 'wppa'); ?></h3>
        <p><?php _e('This plugin is designed to easily manage and display your photo albums within your WordPress site.', 'wppa'); ?></p>
		<p>
			<?php _e('Features:', 'wppa'); ?>
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
		</p>
		
		<h3><?php _e('Plugin Admin Features', 'wppa'); ?></h3>
		<p><?php _e('You can find the plugin admin section under Menu Photo Albums on the admin screen.', 'wppa'); ?></p>
		<p>
			<?php _e('The following submenus exist.', 'wppa'); ?>
			<ul class="wppa-help-ul">
				<li><?php _e('Photo Albums: Create and manage Albums.', 'wppa'); ?></li>
				<li><?php _e('Upload photos: To upload photos to an album you created.', 'wppa'); ?></li>
				<li><?php _e('Settings: To control the various settings to customize your needs.', 'wppa'); ?></li>
				<li><?php _e('Sidebar Widget: To specify the behaviour for an optional sidebar widget.', 'wppa'); ?></li>
				<li><?php _e('Help & Info: The screen you are watching now.', 'wppa'); ?></li>
			</ul>
		</p>

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
			<?php _e('This method enables you to add more than one album to a specific page or post as long as they have the same parent.', 'wppa'); ?><br /><br/ >
			<?php _e('Additionally, if you set the parent of this album (nr 22 in this example) to "--- separate ---", it will not be listed in the "generic" photo gallery and the breadcrumb will display the best.', 'wppa'); ?><br /><br />
			<?php _e('You can add a third line if you want the photos to be displayed at a different size than normal. You can "overrule" the "Full size" setting by adding the line (for e.g. 300px):', 'wppa'); ?><br />
			<tt>%%size=300%%</tt><br /><br />
			<?php _e('To avoid excessive newlines in your page or post you may combine the above to something like:', 'wppa'); ?><br />
			<tt>%%wppa%%%%cover=19%%%%size=300%%</tt><br /><br /><br />
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
			<?php _e('In order to work properly, the wppa_albums() tag needs to be within the', 'wppa'); ?> <a href="http://codex.wordpress.org/The_Loop">WordPress loop</a>.<br/>
			<?php _e('For more information on creating custom page templates, click', 'wppa'); ?> <a href="http://codex.wordpress.org/Pages#Creating_your_own_Page_Templates">here</a>.<br/>
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
			<li><?php _e('There can be only <b>ONE</b> occurence of <tt>%%wppa%%</tt> in a page or post. If you want more than one, make them siblings and refer to the parent album. This is a permanent restriction.', 'wppa'); ?></li>
			<li><?php _e('Keep the sequence intact: 1. <tt>%%wppa%%</tt>, 2. <tt>%%album=</tt> or <tt>%%cover=</tt>, 3. <tt>%%size=</tt>. (2. being optional even when using 3.).', 'wppa'); ?></li>
			<li><?php _e('Use the default page template, or create one yourself. In this case, study the example (actually the version i use myself): <tt>...wp-content/plugins/wp-photo-album-plus/examples/page-photo-album.php</tt>', 'wppa'); ?></li>
			<li><?php _e('WPPA uses a system of tags similar to the WordPress theme system. To view a list of available tags, please read tags.txt', 'wppa'); ?></li>
		</ul>
	
		<h3><?php _e('Plugin Support And Feature Request', 'wppa'); ?></h3>
		<p>
			<?php _e('If you\'ve read over this readme carefully and are still having issues, if you\'ve discovered a bug,', 'wppa'); ?>
			<?php _e('or have a feature request, please contact me via my', 'wppa'); ?> <a href="mailto:opajaap@opajaap.nl?subject=WP%20Photo%20Album%20Plus">E-mail</a>.
		</p>
        <p>
			<?php _e('If you love this plugin, please buy me a', 'wppa'); ?> <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=OpaJaap@OpaJaap.nl&item_name=WP-Photo-Album-Plus&item_number=Support-Open-Source&currency_code=USD">Heineken.</a>
		</p>

		<h3><?php _e('About and credits', 'wppa'); ?></h3>
		<p>
			<?php _e('WP Photo Album Plus is extended with many new features and is maintained by J.N. Breetvelt, a.k.a.', 'wppa'); ?>
			<a href="http://www.opajaap.nl/"> (OpaJaap)</a><br />
			<?php _e('Thanx to R.J. Kaplan for WP Photo Album 1.5.1.', 'wppa'); ?><br/>
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
					<td><?php echo($album['name']) ?></td>
					<td><small><?php echo($album['description']) ?></small></td>
					<td><?php echo($album['id']) ?></td>
					<td><?php echo($album['a_order']) ?></td>
					<td><?php wppa_album_name($album['a_parent']) ?></td>
					<td><a href="admin.php?page=<?php echo(PLUGIN_PATH) ?>/wppa.php&amp;tab=edit&amp;edit_id=<?php echo($album['id']) ?>" class="wppaedit"><?php _e('Edit', 'wppa'); ?></a></td>
					<td><a href="admin.php?page=<?php echo(PLUGIN_PATH) ?>/wppa.php&amp;tab=del&amp;id=<?php echo($album['id']) ?>" class="wppadelete"><?php _e('Delete', 'wppa'); ?></a></td>
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
	$photos = $wpdb->get_results("SELECT * FROM " . PHOTO_TABLE . " WHERE album=$id " . wppa_get_photo_order($id), 'ARRAY_A');
	if (empty($photos)) {
?>
	<p><?php _e('No photos yet in this album.', 'wppa'); ?></p>
<?php
	} else {
		foreach ($photos as $photo) {
?>
			<div class="photoitem">
				
					<img src="<?php echo(get_bloginfo('wpurl')) ?>/wp-content/uploads/wppa/thumbs/<?php echo($photo['id'] . '.' . $photo['ext']) ?>" alt="<?php echo($photo['name']) ?>" />
					<table class="details phototable">
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo('photos[' . $photo['id'] . '][name]') ?>"><?php _e('Name:', 'wppa'); ?></label>
							</th>
							<td>
								<input type="text" name="<?php echo('photos[' . $photo['id'] . '][name]') ?>" value="<?php echo($photo['name']) ?>" />
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
								<a href="<?php echo(get_option('siteurl')) ?>/wp-admin/admin.php?page=<?php echo(PLUGIN_PATH) ?>/wppa.php&amp;tab=edit&amp;edit_id=<?php echo($_GET['edit_id']) ?>&amp;photo_del=<?php echo($photo['id']) ?>" class="deletelink" onclick="return confirm('Are you sure you want to delete this photo?')"><?php _e('Delete', 'wppa'); ?></a>
							</th>
							<td>
							</td>
						</tr>
					</table>
					<input type="hidden" name="<?php echo('photos[' . $photo['id'] . '][id]') ?>" value="<?php echo($photo['id']) ?>" />
					<div class="desc"><?php _e('Description:', 'wppa'); ?><br /><textarea cols="40" rows="4" name="photos[<?php echo($photo['id']) ?>][description]"><?php echo($photo['description']) ?></textarea></div>
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
function wppa_album_select($exc = '', $sel = '', $addnone = FALSE, $addseparate = FALSE) {
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
		if ($album['id'] != $exc) {
			$result .= '<option value="' . $album['id'] . '"' . $selected . '>' . $album['name'] . '</option>';
		}
	}
    
    if ($sel == -1) $selected = ' selected="selected" '; else $selected = '';
    if ($addseparate) $result .= '<option value="-1"' . $selected . '>' . __('--- separate ---', 'wppa') . '</option>';
	return $result;
}

// add an album 
function wppa_add_album() {
	global $wpdb;
	$name = $_POST['wppa-name']; $name = esc_attr($name);
	$desc = $_POST['wppa-desc']; $desc = esc_attr($desc);
	$order = $_POST['wppa-order']; if (!is_numeric($order)) $order = 0;
    $parent = $_POST['wppa-parent']; if (!is_numeric($parent)) $parent = 0;
    $porder = $_POST['wppa-photo-order-by']; if (!is_numeric($porder)) $porder = 0;
	
	if (!empty($name)) {
        $query = "INSERT INTO " . ALBUM_TABLE . " (id, name, description, a_order, a_parent, p_order_by) VALUES (0, '$name', '$desc', '$order', '$parent', '$porder')";
		$iret = $wpdb->query($query);
        if ($iret === FALSE) echo '<div id="message" class="error"><p><strong>Could not create album.</strong></p></div>';
		else {
            $id = wppa_album_id($name, TRUE);
            wppa_set_last_album($id);
            echo '<div id="message" class="updated fade"><p><strong>Album #' . $id .' Added.</strong></p></div>';
        }
	} 
    else { ?>
		<div id="message" class="error"><p><strong><?php _e('Album Name cannot be empty.', 'wppa'); ?></strong></p></div>
<?php
	}
}

// edit an album 
function wppa_edit_album() {
	global $wpdb;
    $first = TRUE;
	$name = $_POST['wppa-name'];
	$desc = $_POST['wppa-desc'];
	$main = $_POST['wppa-main'];
    $order = $_POST['wppa-order']; if (!is_numeric($order)) $order = 0;
	if (isset($_POST['wppa-parent'])) $parent = $_POST['wppa-parent']; 
	else $parent = 0;
    $orderphotos = $_POST['wppa-list-photos-by']; if (!is_numeric($orderphotos)) $orderphotos = 0;
	
    // update the photo information
    if (isset($_POST['photos']))
	foreach ($_POST['photos'] as $photo) {
        $photo['name'] = esc_attr($photo['name']);
        if (!is_numeric($photo['p_order'])) $photo['p_order'] = 0;
		$query = "UPDATE " . PHOTO_TABLE . " SET name='{$photo['name']}', album={$photo['album']}, description='{$photo['description']}', p_order={$photo['p_order']} WHERE id={$photo['id']} LIMIT 1";
		$iret = $wpdb->query($query);
        if ($iret === FALSE) {
            if ($first) { ?>
				<div id="message" class="error"><p><strong><?php _e('Could not update photo.', 'wppa'); ?></strong></p></div>
<?php
				$first = FALSE;
			}
        }
	}
	
	// update the album information
	if (!empty($name)) {
        $iret = $wpdb->query("UPDATE " . ALBUM_TABLE . " SET name='$name', description='$desc', main_photo='$main', a_order='$order', a_parent='$parent', p_order_by='$orderphotos' WHERE id={$_GET['edit_id']}");
        if ($iret === FALSE) { ?>
			<div id="message" class="error"><p><strong><?php _e('Album could not be updated.', 'wppa'); ?></strong></p></div>
<?php	}
		else {
?>
		<div id="message" class="updated fade"><p><strong><?php _e('Album information edited.', 'wppa'); ?> <a href="admin.php?page=<?php echo(PLUGIN_PATH); ?>/wppa.php"><?php _e('Back to album management.', 'wppa'); ?></a></strong></p></div>
<?php	}
    wppa_set_last_album($_GET['edit_id']);
	} 
    else { ?>
		<div id="message" class="error"><p><strong><?php _e('Album Name cannot be empty.', 'wppa'); ?></strong></p></div>';
<?php
	}
}

// delete an album 
function wppa_del_album($id, $move = '') {
	global $wpdb;
	$wpdb->query("DELETE FROM " . ALBUM_TABLE . " WHERE id=$id LIMIT 1");
	
	if (empty($move)) { // will delete all the album's photos
		$photos = $wpdb->get_results("SELECT * FROM " . PHOTO_TABLE . " WHERE album=$id", 'ARRAY_A');
		if (is_array($photos)) {
			foreach ($photos as $photo) {
				// remove the photos and thumbs
				unlink(ABSPATH . 'wp-content/uploads/wppa/' . $photo['id'] . '.' . $photo['ext']);
				unlink(ABSPATH . 'wp-content/uploads/wppa/thumbs/' . $photo['id'] . '.' . $photo['ext']);
			} 
		}
		// remove the database entries
		$wpdb->query("DELETE FROM " . PHOTO_TABLE . " WHERE album=$id");
	} else {
		$wpdb->query("UPDATE " . PHOTO_TABLE . " SET album=$move WHERE album=$id");
	}
?>
	<div id="message" class="updated fade"><p><strong><?php _e('Album Deleted.', 'wppa'); ?></strong></p></div>
<?php
}

// select main photo
function wppa_main_photo($cur = '') {
	global $wpdb;
    $a_id = $_GET['edit_id'];
	$photos = $wpdb->get_results("SELECT * FROM " . PHOTO_TABLE . " WHERE album=$a_id " . wppa_get_photo_order($a_id), 'ARRAY_A');
	
	$output = '';
	if (!empty($photos)) {
		$output .= '<select name="wppa-main">';
		$output .= '<option value="">' . __('--- random ---', 'wppa') . '</option>';

		foreach($photos as $photo) {
			if ($cur == $photo['id']) { $selected = 'selected="selected"'; } else { $selected = ''; }
			$output .= '<option value="' . $photo['id'] . '" ' . $selected . '>' . $photo['name'] . '</option>
			';
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
	if (!is_dir($wppa_dir)) mkdir($wppa_dir);	
	
	// check if thumbs dir exists 
	if (!is_dir($wppa_dir . '/thumbs')) mkdir($wppa_dir . '/thumbs');
	
	foreach ($_FILES as $file) {
    if ($file['tmp_name'] != '')
		if (getimagesize($file['tmp_name'])) {
			$ext = substr(strrchr($file['name'], "."), 1);
		
			$query = "INSERT INTO " . PHOTO_TABLE . " (id, album, ext, name, description) VALUES (0, {$_POST['wppa-album']}, '$ext', '{$file['name']}', '')";
			$wpdb->query($query);
			//echo $query;
			$image_id = $wpdb->get_var("SELECT LAST_INSERT_ID()");
			
			$newimage = $wppa_dir . '/' . $image_id . '.' . $ext;
			copy($file['tmp_name'], $newimage);

			if (is_file ($newimage)) {
				$uploaded_a_file = TRUE;
				if (is_numeric(get_option('wppa_thumbsize'))) {
					$thumbsize = get_option('wppa_thumbsize');
				} else {
					$thumbsize = 130;
				}
				
				wppa_create_thumbnail($newimage, $thumbsize, '' );
			} 
		}
	}
	
	if ($uploaded_a_file) { ?>
        <div id="message" class="updated fade"><p><strong><?php _e('Photos Uploaded in album nr', 'wppa'); echo(' ' . $_POST['wppa-album']); ?></strong></p></div>
<?php
        wppa_set_last_album($_POST['wppa-album']);
    }
}

/* Add Javascript to page head */
add_action('admin_head', 'wppa_admin_head');

function wppa_admin_head() { ?>
 	<script type="text/javascript" src="<?php echo(get_bloginfo('wpurl')); ?>/wp-content/plugins/<?php echo(PLUGIN_PATH); ?>/multifile_compressed.js"></script>
 	<link rel="stylesheet" href="<?php echo(get_bloginfo('wpurl')); ?>/wp-content/plugins/<?php echo(PLUGIN_PATH); ?>/admin_styles.css" type="text/css" media="screen" />
<?php
}

// update all thumbs 
function wppa_regenerate_thumbs() {
	global $wpdb;
	$thumbsize = get_option('wppa_thumbsize');
	$wppa_dir = ABSPATH . 'wp-content/uploads/wppa/';
    
    $start = get_option('wppa_lastthumb', '-1');

	$photos = $wpdb->get_results("SELECT * FROM " . PHOTO_TABLE . " WHERE id>" . $start . " ORDER BY id", 'ARRAY_A');
	
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
				$image_new_height = $image_height / $image_ratio;
				//width is > height
			} else {
				$image_width = $image_attr[0];
				$image_height = $image_attr[1];
				$image_new_height = $max_side;

				$image_ratio = $image_height / $image_new_height;
				$image_new_width = $image_width / $image_ratio;
				//height > width
			}

			$thumbnail = imagecreatetruecolor( $image_new_width, $image_new_height);
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


/* LISTING FUNCTIONS */
// get the albums via filter
add_action('init', 'wppa_do_filter');

function wppa_do_filter() {
add_filter('the_content', 'wppa_albums_filter', 99);
}

function wppa_albums_filter($post) {
    global $startalbum;
    global $wppa_fullsize;
	global $before_album;
	global $is_cover;
	
    $is_cover = '0';
	
	if (substr_count($post, '%%wppa%%') > 0) {
	
		$wppapos = strpos($post, '%%wppa%%');
		$before_album = substr($post, 0, $wppapos);
		$post = substr($post, $wppapos);
	
        $albpos = strpos($post, '%%album=');
        if ($albpos) {
            $albpos += 8;
            $len = 1;
            $alb = substr($post, $albpos, $len);
            while (is_numeric($alb) && $len < 5) {
                $startalbum = $alb;
                $len++;
                $alb = substr($post, $albpos, $len);
            }
			$rmv = '%%album=' . $alb . '%';
            $post = substr_replace($post, '', strpos($post, $rmv), strlen($rmv)); 
			/*str_replace('%%album=' . $alb . '%', '', $post);  // remove from content */
        }
		else {
			$covpos = strpos($post, '%%cover=');
			if ($covpos) {
				$covpos += 8;
				$len = 1;
				$alb = substr($post, $covpos, $len);
				while (is_numeric($alb) && $len < 5) {
					$startalbum = $alb;
					$len++;
					$alb = substr($post, $covpos, $len);
				}
				$rmv = '%%cover=' . $alb . '%';
				$post = substr_replace($post, '', strpos($post, $rmv), strlen($rmv));
				$is_cover = '1';
			}
		}
        
        $sizepos = strpos($post, '%%size=');
        if ($sizepos) {
            $sizepos += 7;
            $len = 1;
            $size = substr($post, $sizepos, $len);
            while (is_numeric($size) && $len < 5) {
                $wppa_fullsize = $size;
                $len++;
                $size = substr($post, $sizepos, $len);
            }
			$rmv = '%%size=' . $size . '%';
			$post = substr_replace($post, '', strpos($post, $rmv), strlen($rmv));
            /*$post = str_replace('%%size=' . $size . '%', '', $post);*/
        }
        
		$post = substr_replace($post, wppa_albums(), strpos($post, '%%wppa%%'), 8); 
		/* str_replace('%%wppa%%', wppa_albums(), $post); */

    }
	return $post;
}

// get the albums
function wppa_albums($xalb = '') {
	global $wpdb;
    global $startalbum;
    
    if (is_numeric($xalb)) $startalbum = $xalb;
    
	$templatefile = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa_theme.php';
	
	// check for user template before using default template
	if (is_file($templatefile)) {
		include($templatefile);
	} else {
		include(ABSPATH . 'wp-content/plugins/' . PLUGIN_PATH . '/theme/wppa_theme.php');
	}
}

// add  styling to header
add_action('wp_head', 'wppa_add_style');

function wppa_add_style() {
	$userstyle = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa_style.css';
	if (is_file($userstyle)) {
		echo '<link rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/themes/' . get_option('template')  . '/wppa_style.css" type="text/css" media="screen" />
		';
	} else {
		echo '<link rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/' . PLUGIN_PATH . '/theme/wppa_style.css" type="text/css" media="screen" />
		';
	}
}


/* TEMPLATE FUNCTIONS (TAGS) */

// shows the breadcrumb navigation
function wppa_breadcrumb($xsep = '&raquo;') {
	global $startalbum;
    $sep = '&nbsp;' . $xsep . '&nbsp;';
    $home = 'Home';
    echo '<a href="' . get_bloginfo('url') . '" class="backlink">' . $home . '</a>' . $sep;
    if (isset($_GET['album'])) $alb = $_GET['album']; 
	elseif (is_numeric($startalbum)) $alb = $startalbum;
	else $alb = 0;
	$separate = wppa_is_separate($alb);
	
	if ($alb == 0) {
        if (!$separate) the_title();
		return;
	} else {
		if (!$separate) {
			echo '<a href="' . get_permalink()  . '" class="backlink">'; the_title(); echo '</a>' . $sep;
		}
        wppa_crumb_ancestors($sep, $alb);
		if (!isset($_GET['photo'])) {
			echo wppa_album_name($alb, TRUE); //$_GET['album'], TRUE);
			return;
		} else {
			echo '<a href="' . get_permalink() . wppa_sep() . 'album=' . $alb . '" class="backlink">' . wppa_album_name($alb, TRUE) . '</a>' . $sep;
			echo wppa_photo_name($_GET['photo']);
		}
	}
}

function wppa_crumb_ancestors($sep, $alb) {
    $parent = wppa_get_parentalbumid($alb);
    if ($parent < 1) return;
    
    wppa_crumb_ancestors($sep, $parent);
   
    echo '<a href="' . get_permalink() . wppa_sep() . 'album=' . $parent . '" class="backlink">' . wppa_album_name($parent, TRUE) . '</a>' . $sep;
    return;
}

// Get the albums parent
function wppa_get_parentalbumid($alb) {
    global $wpdb;
    
    $query = "SELECT a_parent FROM " . ALBUM_TABLE . " WHERE id=$alb";
    $result = $wpdb->get_var($query);
    if (!is_numeric($result)) $result = 0;
    return $result;
}

// See if an album is in a separate tree
function wppa_is_separate($xalb) {
	if (!is_numeric($xalb)) return FALSE;	// should never happen
		
	$alb = wppa_get_parentalbumid($xalb);
	if ($alb == 0) return FALSE;
	if ($alb == -1) return TRUE;
	return (wppa_is_separate($alb));
}

// get album title by id
function wppa_get_album_name($id = '') {
	return wppa_album_name($id = '', TRUE);
}

function wppa_album_name($id = '', $return = FALSE) {
	global $wpdb;
    
    if ($id == '0') $name = __('--- none ---', 'wppa');
    elseif ($id == '-1') $name = __('--- separate ---', 'wppa');
    else {
        if ($id == '') $id = $_GET['album'];
        $id = $wpdb->escape($id);	
        if (is_numeric($id)) $name = $wpdb->get_var("SELECT name FROM " . ALBUM_TABLE . " WHERE id=$id");
    }
	
	if ($return) return $name; else echo $name;
}

// get album id by title
function wppa_get_album_id($name = '') {
	return wppa_album_id($name, TRUE);
}

function wppa_album_id($name = '', $return = FALSE) {
	global $wpdb;
    
	if ($name == '') return '';
    $name = $wpdb->escape($name);
    $id = $wpdb->get_var("SELECT id FROM " . ALBUM_TABLE . " WHERE name='" . $name . "'");
    
 	if ($return) return $id; else echo $id;
}

// get the seperator (& or ?, depending on permalink structure)
function wppa_sep() {
	if (get_option('permalink_structure') == '') $sep = '&amp;';
    else $sep = '?';
	return $sep;
}

// determine page
function wppa_page($page) {
    if (isset($_GET['slide'])) $cur_page = 'slide';	
    elseif (isset($_GET['photo'])) $cur_page = 'single';
	else $cur_page = 'albums';
	
	if ($cur_page == $page) return TRUE; else return FALSE;
}

// get url of current album image
function wppa_get_image_url() {
	return wppa_image_url(TRUE);
}
function wppa_image_url($return = FALSE) {
	global $wpdb, $album;
		
	// cehck if a main photo is set
	if (empty($album['main_photo'])) {
		$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE album={$album['id']} ORDER BY RAND() LIMIT 0,1", 'ARRAY_A');
	} else {
		$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE id={$album['main_photo']} LIMIT 0,1", 'ARRAY_A');
	}
	
	$imgurl = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/thumbs/' . $image['id'] . '.' . $image['ext'];
		
	if ($return) return $imgurl; else echo $imgurl;
}


function wppa_image_page_url($return = FALSE) {
	global $wpdb, $album;
		
	// cehck if a main photo is set
	if (empty($album['main_photo'])) {
		$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE album={$album['id']} ORDER BY RAND() LIMIT 0,1", 'ARRAY_A');
	} else {
		$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE id={$album['main_photo']} LIMIT 0,1", 'ARRAY_A');
	}
	
	//$imgurl = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/thumbs/' . $image['id'] . '.' . $image['ext'];

$imgurl = get_permalink()  . wppa_sep() . 'album=' . $album['id'] . '&amp;photo=' . $image['id'];	
	if ($return) return $imgurl; else echo $imgurl;

}

// loop album
function wppa_get_albums() {
	global $wpdb;
    global $startalbum;
	global $is_cover;
	
	if (isset($_GET['cover'])) $is_cover = $_GET['cover'];

    if (isset($_GET['album'])) $parent = $_GET['album'];
    elseif (is_numeric($startalbum)) $parent=$startalbum;
    else $parent = 0;
	
	if ($is_cover) $albums = $wpdb->get_results("SELECT * FROM " . ALBUM_TABLE . " WHERE id={$parent} ", 'ARRAY_A');
	else $albums = $wpdb->get_results("SELECT * FROM " . ALBUM_TABLE . " WHERE a_parent={$parent} " . wppa_get_album_order(),'ARRAY_A');
    return $albums;
}

// get link to album by id or in loop
function wppa_get_album_url($xid = '') {
	global $album;
	if ($xid != '') $id = $xid;
	else $id = $album['id'];
    $link = get_permalink() . wppa_sep() . 'album=' . $id;
    return $link;
}

// get link to album (in loop)
function wppa_album_url($return = FALSE) {
	global $album;
	$link = get_permalink() . wppa_sep() . 'album=' . $album['id'] . '&cover=0';
	
	if ($return) return $link; else echo $link;	
}

// get number of photos in album 
function wppa_get_photo_count($xid = '') {
    global $wpdb;
    global $album;
    
    if (is_numeric($xid)) $id = $xid; else $id = $album['id'];
    $count = $wpdb->query("SELECT * FROM " . PHOTO_TABLE . " WHERE album=$id");
	return $count;
}

// get number of albums in album 
function wppa_get_album_count($xid = '') {
    global $wpdb;
    global $album;
    
    if (is_numeric($xid)) $id = $xid; else $id = $album['id'];
    $count = $wpdb->query("SELECT * FROM " . ALBUM_TABLE . " WHERE a_parent=$id");
    return $count;
}

// get album name
function wppa_get_the_album_name() {
	global $album;
	
	return $album['name'];
}

function wppa_the_album_name($return = FALSE) {
	global $album;
	
	if ($return) return $album['name']; else echo $album['name'];	
}

// get album decription
function wppa_get_the_album_desc() {
	return $album['description'];
}
function wppa_the_album_desc($return = FALSE) {
	global $album;
	
	if ($return) return $album['description']; else echo $album['description'];	
}

// get link to slideshow (in loop)
function wppa_get_slideshow_url() {
	return wppa_slideshow_url(TRUE);
}
function wppa_slideshow_url($return = FALSE) {
	global $album;
	$link = get_permalink() . wppa_sep() . 'album=' . $album['id'] . '&amp;' . 'slide=true';
	
	if ($return) return $link; else echo $link;	
}

// loop thumbs
function wppa_get_thumbs() {
	global $wpdb;
    global $startalbum;
    
    if (isset($_GET['album'])) $album = $_GET['album'];
    elseif (is_numeric($startalbum)) $album = $startalbum; 
    else $album = 0;
	if (is_numeric($album)) $thumbs = $wpdb->get_results("SELECT * FROM " . PHOTO_TABLE . " WHERE album=$album " . wppa_get_photo_order($album), 'ARRAY_A'); 
	return $thumbs;
}

// get link to photo
function wppa_photo_page_url() {
	echo wppa_get_photo_page_url();
}
function wppa_get_photo_page_url() {
	global $thumb;
    if (isset($_GET['album'])) $url = get_permalink()  . wppa_sep() . 'album=' . $_GET['album'] . '&amp;photo=' . $thumb['id'];
	else $url = get_permalink()  . wppa_sep() . 'photo=' . $thumb['id'];
	return $url; 
}

// get url of thumb
function wppa_thumb_url() {
	echo wppa_get_thumb_url();
}
function wppa_get_thumb_url() {
	global $thumb;
    
	$url = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/thumbs/' . $thumb['id'] . '.' . $thumb['ext'];
	return $url; 
}

// get url of a full sized image
function wppa_get_photo_url($id = '') {
	return wppa_photo_url($id, TRUE);
}
function wppa_photo_url($id = '', $return = FALSE) {
	global $wpdb;
    if ($id == '') $id = $_GET['photo'];    
    $id = $wpdb->escape($id);
    
	if (is_numeric($id)) $ext = $wpdb->get_var("SELECT ext FROM " . PHOTO_TABLE . " WHERE id=$id");
	$url = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/' . $id . '.' . $ext;
	
	if ($return) return $url; else echo $url;
}

// get the name of a full sized image
function wppa_photo_name($id = '', $return = FALSE) {
	global $wpdb;
	if ($id == '') $id = $_GET['photo'];	
	$id = $wpdb->escape($id);
		
	if (is_numeric($id)) $name = $wpdb->get_var("SELECT name FROM " . PHOTO_TABLE . " WHERE id=$id");
	else $name = '';
	
	if ($return) return $name; else echo $name;
}

// get the description of a full sized image
function wppa_photo_desc($id = '', $return = FALSE) {
	global $wpdb;
	if ($id == '') $id = $_GET['photo'];
	$id = $wpdb->escape($id);
	
	if (is_numeric($id)) $desc = $wpdb->get_var("SELECT description FROM " . PHOTO_TABLE . " WHERE id=$id");
	else $desc = '';
	
	if ($return) return $desc; else echo $desc;
}

// prev/next links
function wppa_prev_next($prev = '&laquo;<a href="%link%">Previous Photo</a> ', $next = '<a href="%link%">Next Photo</a>&raquo;', $id='', $return = FALSE) {
	global $wpdb;
	
	$result = '';
	$position = '';
	$ids = '';

	if (empty($id)) { $id = $_GET['photo']; }
	$id = $wpdb->escape($id);
	
	if (is_numeric($id)) {
		$album = $wpdb->get_var("SELECT album FROM " . PHOTO_TABLE . " WHERE id=$id");
		$ids = $wpdb->get_results("SELECT id FROM " . PHOTO_TABLE . " WHERE album=$album " . wppa_get_photo_order($album), 'ARRAY_N');
	
		$tmp_pos = 0;
	
		foreach ($ids as $single) {
			if ($single[0] == $id) {
				$position = $tmp_pos;
			}
		$tmp_pos++;
		}
	}
	
	// if is not first photo 
	if ($position > 0) {
		$prev_pos = $position - 1;
		if (isset($_GET['album'])) $link = get_permalink()  . wppa_sep() . 'album=' . $_GET['album'] . '&amp;photo=' . $ids[$prev_pos][0];
		else $link = get_permalink()  . wppa_sep() . 'photo=' . $ids[$prev_pos][0];
		$result .= str_replace('%link%', $link, $prev);
	}
	
	// if is not last photo
	if ($position < (count($ids) - 1)) {
		$next_pos = $position + 1;
		if (isset($_GET['album'])) $link = get_permalink()  . wppa_sep() . 'album=' . $_GET['album'] . '&amp;photo=' . $ids[$next_pos][0];
		else $link = get_permalink()  . wppa_sep() . 'photo=' . $ids[$next_pos][0];
		$result .= str_replace('%link%', $link, $next);
	}
	
	if ($return) return $result; else echo $result;
}

// get height or width limit
function wppa_fullsize($id = '') {
	echo wppa_get_fullsize($id);
}
function wppa_get_fullsize($id = '') {
	global $wpdb;
    global $wppa_fullsize;
	global $wppa_no_enlarge;
	
	if (!is_numeric($wppa_fullsize)) $wppa_fullsize = get_option('wppa_fullsize');
	if (!is_numeric($wppa_fullsize)) $wppa_fullsize = '450';

	$wppa_enlarge = get_option('wppa_enlarge', 'true');
	
	if ($wppa_enlarge != 'true') {
		$result = 'style="max-width: ' . $wppa_fullsize . 'px; max-height: ' . $wppa_fullsize . 'px;"';
	}
	else {
		if (empty($id)) $id = $_GET['photo'];
			
		if (is_numeric($id)) {
			$ext = $wpdb->get_var("SELECT ext FROM " . PHOTO_TABLE . " WHERE id=$id");
		}
		$img_path = ABSPATH . 'wp-content/uploads/wppa/' . $id . '.' . $ext;
		if(is_file($img_path)) {
			$size = getimagesize($img_path);
		}
		
		if ($size[0] >= $size[1]) {
			$result = 'width="' . $wppa_fullsize . '"';
		} 
		else {
			$result = 'height="' . $wppa_fullsize . '"';
		}
	}
	return $result;
}

// get slide info
function wppa_get_slide_info($index, $id) {
    $result = "'" . $index . "','" . wppa_get_photo_url($id) . "','" . wppa_get_fullsize($id) . "','" . esc_attr(wppa_photo_name($id, TRUE)) . "','" . esc_attr(wppa_photo_desc($id, TRUE)) . "'";
    return $result;                                                        
}


/* LOW LEVEL UTILITY ROUTINES */

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

// get album order
function wppa_get_album_order() {
    $result = '';
    $order = get_option('wppa_list_albums_by');
    switch ($order)
    {
    case '1':
        $result = 'ORDER BY a_order';
        break;
    case '2':
        $result = 'ORDER BY name';
        break;  
    case '3':
        $result = 'ORDER BY RAND()';
        break;
    default:
        $result = 'ORDER BY id';
    }
    if (get_option('wppa_list_albums_desc') == 'yes') $result .= ' DESC';
    return $result;
}

// get photo order
function wppa_get_photo_order($id) {
    global $wpdb;
    
	if ($id == 0) $order=0;
	else $order = $wpdb->get_var("SELECT p_order_by FROM " . ALBUM_TABLE . " WHERE id=$id");
    if ($order == '0') $order = get_option('wppa_list_photos_by');
    switch ($order)
    {
    case '1':
        $result = 'ORDER BY p_order';
        break;
    case '2':
        $result = 'ORDER BY name';
        break;
    case '3':
        $result = 'ORDER BY RAND()';
        break;
    default:
        $result = 'ORDER BY id';
    }
    if (get_option('wppa_list_photos_desc') == 'yes') $result .= ' DESC';
    return $result;
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