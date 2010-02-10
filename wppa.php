<?php
/*
Plugin Name: WP Photo Album Plus
Description: Easily manage and display your photo albums and slideshows within your WordPress site.
Version: 1.7
Author: J.N. Breetvelt / Rubin J. Kaplan (up to version 1.5.1)
Author URI: http://www.opajaap.nl/
Plugin URI: http://wordpress.org/extend/plugins/wp-photo-album-plus/
*/


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
}

/* ADMIN MENU */
function wppa_add_admin() {
	$level = get_option('wppa-accesslevel');
	if (empty($level)) { $level = 'level_10'; }
	
	add_menu_page('WP Photo Album', 'Photos', $level, __FILE__, 'wppa_admin');
	
    add_submenu_page(__FILE__, 'Upload Photos', 'Upload Photos', $level, 'upload_photos', 'wppa_page_upload');
    add_submenu_page(__FILE__, 'Options', 'Options', $level, 'options', 'wppa_page_options');
    add_submenu_page(__FILE__, 'Help &amp; Info', 'Help &amp; Info', $level, 'wppa_help', 'wppa_page_help');
}

add_action('admin_menu', 'wppa_add_admin');

/* ADMIN PAGES */
function wppa_admin() {
	global $wpdb;
	
	// warn if the uploads directory is no writable
	if (!is_writable(ABSPATH . 'wp-content/uploads')) { echo '<div id="error" class="error"><p><strong>Warning:</strong> The uploads directory does not exist or is not writable by the server. Please make sure that <tt>wp-content/uploads/</tt> is writeable by the server</p></div>'; }
		
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
			
			echo '<div id="message" class="updated fade"><p><strong>Photo Deleted.</strong></p></div>';
		}		
		
		$albuminfo = $wpdb->get_row("SELECT * FROM " . ALBUM_TABLE . " WHERE id={$_GET['edit_id']} ", 'ARRAY_A');
				
		echo '<div class="wrap">';
			
		echo '<h2>Edit Ablum Information</h2>';
        
		echo '<form action="' . get_option('siteurl') . '/wp-admin/admin.php?page=' . PLUGIN_PATH . '/wppa.php&amp;tab=edit&amp;edit_id=' . $_GET['edit_id'] . '" method="post">';
		
		wppa_nonce_field('$wppa_nonce', WPPA_NONCE);
		
		echo '<p>
				<label for="wppa-name">Name: </label><br />
				<input type="text" name="wppa-name" id="wppa-name" value="' . $albuminfo['name'] .'" />
			</p>
		
			<p>
				<label for="wppa-description">Description: </label><br />
				<textarea rows="5" cols="40" name="wppa-desc" id="wppa-desc">' . $albuminfo['description'] . '</textarea>
			</p>
			<p>
				<label for="wppa-order">Order: </label><br />
				<input type="text" name="wppa-order" id="wppa-order" value="' . $albuminfo['a_order'] .'" />
			</p>
            <p>
                <label for="wppa-parent">Parent album: </label><br />';
                if (wppa_get_album_count($albuminfo["id"])) echo 'You can not change the parent of an album that contains sub albums. <small>This is to prevent circular references. Change the sub albums parent id first.</small>';
                else echo '<select name="wppa-parent">' . wppa_album_select("", $albuminfo["a_parent"], TRUE) . '</select>';
                echo '
            </p>
            
            <p>';
                $order = $albuminfo['p_order_by'];
                echo '<label for="wppa-list-photos-by">Photo order: </label>
                <select name="wppa-list-photos-by">';
                    wppa_order_options($order, $nil);
                echo '</select>
                <small>Specify the way the photos should be ordered in this album.<br/>
                The default setting can be changed in the Options page.</small>
            </p>

			<p>
				<label for="wppa-main">Main Photo: </label><br />
				' . wppa_main_photo($albuminfo['main_photo']) . '
			</p>
			
			<p>
				<input type="submit" name="wppa-ea-submit" value="Save Changes" />
			</p>
			
		';
			
		echo '<br />';
		
		echo '<h2>Manage Photos</h2>';
		
		
		echo '<p>
				<input type="submit" name="wppa-ea-submit" value="Save Changes" />
			</p>';
			
		echo wppa_album_photos($_GET['edit_id']);
		
		echo '<p>
				<input type="submit" name="wppa-ea-submit" value="Save Changes" />
			</p>';
		
		echo '</form>';
		
		echo '</div>';
	
	// styles page
		

		
	// help page
	
		
	
	// album delete confirm page
	} else if ($_GET['tab'] == 'del'){
		
		echo '<div class="wrap">
			<h2>Delete Album</h2>
			<p>Are you sure you want to delete the album "' . wppa_album_name($_GET['id'], TRUE) . '" ?<br />
			Press Delete to continue, and Cancel to go back.</p>';
			
		echo '<form name="wppa-del-form" action="' . get_option('siteurl') . '/wp-admin/admin.php?page=' . PLUGIN_PATH . '/wppa.php" method="post">';
			
		wppa_nonce_field('$wppa_nonce', WPPA_NONCE);

		echo '<p>
				What would you like to do with photos currently in the album?<br />
				<input type="radio" name="wppa-del-photos" value="delete" checked="checked" /> Delete <br />
				<input type="radio" name="wppa-del-photos" value="move" /> Move to: 
				<select name="wppa-move-album">' . wppa_album_select($_GET['id']) . '</select>
			</p>
			
			<input type="hidden" name="wppa-del-id" value="' . $_GET['id'] . '" />
			<input type="button" value="Cancel" onclick="window.location = \'' . get_option('siteurl') . '/wp-admin/admin.php?page=' . PLUGIN_PATH . '/wppa.php\'" />
			<input type="submit" name="wppa-del-confirm" value="Delete" />
	
		</form>';

			
		echo '</div>';

	
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
		

		echo '<div class="wrap">';
	
		echo "<h2>Manage Albums</h2><br />";
		
		wppa_admin_albums();
		
		echo '<br /><h2>Create New Album</h2>';
		
		echo '<form action="' . get_option('siteurl') . '/wp-admin/admin.php?page=' . PLUGIN_PATH . '/wppa.php" method="post">';

		wppa_nonce_field('$wppa_nonce', WPPA_NONCE);

		echo '<p>
				<label for="wppa-name">Name: </label>
                <input type="text" name="wppa-name" id="wppa-name" />
                <small>Type the name of the new album. Do not leave this empty.</small>
			</p>
		
			<p>
				<label for="wppa-description">Description: </label><br />
				<textarea rows="5" cols="40" name="wppa-desc" id="wppa-desc"></textarea>
			</p>
			
			<p>
				<label for="wppa-name">Order #: </label>
                <input type="text" name="wppa-order" id="wppa-order" />
                <small>If you want to sort the albums by order #, enter the order number here.</small>
			</p>
            
            <p>
                <label for="wppa-parent">Parent album: </label>
                <select name="wppa-parent">' . wppa_album_select('', '', TRUE) . '</select>
                <small>If this is a sub album, select the album in which this album will appear.</small>
            </p>
            
            <p>
                <label for="wppa-photo-order-by">Order photos by: </label>
                <select name="wppa-photo-order-by">';
                wppa_order_options('0', '--- default ---');
                echo '</select>
                <small>If you want to sort the photos in this album different from the system setting, select the order method here.</small>
            </p>
			
			<p>
				<input type="submit" name="wppa-na-submit" value="Create Album!" />
                <small>You can change all these settings later by clicking the \'Edit\' link in the above table.</small>
			</p>
			
		</form>';
	
		echo '</div>';
	
	}
	
}

function wppa_page_upload() {
		// upload images
        
		if (isset($_POST['wppa-upload'])) {
			check_admin_referer( '$wppa_nonce', WPPA_NONCE );
            
			wppa_upload_photos();
		}

		echo '<div class="wrap">';
	
		echo "<h2>Upload Photos</h2><br />";
		
		// chek if albums exist before allowing upload
		
		if(wppa_has_albums()) {

			echo '
			<form enctype="multipart/form-data" action="' . get_option('siteurl') . '/wp-admin/admin.php?page=upload_photos" method="post">
			';
			
			wppa_nonce_field('$wppa_nonce', WPPA_NONCE);
			
			echo '
			<input id="my_file_element" type="file" name="file_1" />
		
			<div id="files_list">
				<h3>Selected Files: <small>You can upload up to 15 photos at once</small></h3>
			</div>';
		
			
			echo '<p>
			<label for="wppa-album">Album: </label>
			<select name="wppa-album" id="wppa-album">';
	
			echo wppa_album_select();
			
			echo '</select></p>';

		
			echo '<input type="submit" name="wppa-upload" value="Upload Photos" />
			</form>
		<br />
		<script type="text/javascript">
			<!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->
			var multi_selector = new MultiSelector( document.getElementById( \'files_list\' ), 15 );
			<!-- Pass in the file element -->
			multi_selector.addElement( document.getElementById( \'my_file_element\' ) );
		</script>';
		
		} else {
			echo '<p>No albums exist. You must <a href="admin.php?page=' . PLUGIN_PATH . '/wppa.php">create one</a> beofre you can upload your photos.</p>';
		}
	
		echo '</div>';
}

function wppa_page_options() {
		if (isset($_POST['wppa-set-submit'])) {
			check_admin_referer( '$wppa_nonce', WPPA_NONCE );

			if (($_POST['wppa-thumbsize'] != get_option('wppa_thumbsize')) && is_numeric($_POST['wppa-thumbsize'])) {
				update_option('wppa_thumbsize', $_POST['wppa-thumbsize']);
                echo '<div id="message" class="updated fade"><p><strong>Regenerating thumbnails, please wait.</strong></p></div>';
               
				wppa_regenerate_thumbs();
			}
			
			update_option('wppa_fullsize', $_POST['wppa-fullsize']);
            
            if (isset($_POST['wppa-list-albums-by'])) update_option('wppa_list_albums_by', $_POST['wppa-list-albums-by']);
            if (isset($_POST['wppa-list-albums-desc'])) update_option('wppa_list_albums_desc', 'yes');
            else update_option('wppa_list_albums_desc', 'no');
            
            if (isset($_POST['wppa-list-photos-by'])) update_option('wppa_list_photos_by', $_POST['wppa-list-photos-by']);
            if (isset($_POST['wppa-list-photos-desc'])) update_option('wppa_list_photos_desc', 'yes');
            else update_option('wppa_list_photos_desc', 'no');
		
			update_option('wppa-accesslevel', $_POST['wppa-accesslevel']);
			
			echo '<div id="message" class="updated fade"><p><strong>Changes Saved</strong></p></div>';
		}
		
		echo '<div class="wrap">';
			
		echo '<h2>WP Photo Album Options</h2>';
		
		echo '<form action="' . get_option('siteurl') . '/wp-admin/admin.php?page=options" method="post">';
		
		wppa_nonce_field('$wppa_nonce', WPPA_NONCE);

		echo '<p>
				<label for="wppa-thumbsize">Thumbnail Size: <small>Changing the thumbnail size will result in all thumbnails being regenerated. this may take a while.</small></label><br />
				<input type="text" name="wppa-thumbsize" id="wppa-tumbsize" value="' . get_option('wppa_thumbsize') .'" />
			</p>';
		
		echo '<p>
				<label for="wppa-fullsize">Full size: <small>The size of the full images is controled with html, the photo itself will not be resized.</small></label><br />
				<input type="text" name="wppa-fullsize" id="wppa-fullsize" value="' . get_option('wppa_fullsize') .'" />
			</p>';
 
        echo '<p>';
                $order = get_option('wppa_list_albums_by');
                echo '<label for="wppa-list-albums-by">Album order: </label>
                <select name="wppa-list-albums-by">';
                    wppa_order_options($order, '--- none ---');
                echo '            
                </select>
                <small>Specify the way the albums should be ordered.</small>
            </p>';

        echo '<p>
                <input type="checkbox" name="wppa-list-albums-desc" id="wppa-list-albums-desc"'; if (get_option('wppa_list_albums_desc') == 'yes') echo ' checked="checked"'; echo ' />
                <label for="wppa-list-albums-desc">Descending. i.e. largest first.</label>
            </p>';  

        echo '<p>';
                $order = get_option('wppa_list_photos_by');
                echo '<label for="wppa-list-photos-by">Photo order: </label>
                <select name="wppa-list-photos-by">';
                    wppa_order_options($order, '--- none ---');
                echo '              
                </select>
                <small>Specify the way the photos should be ordered. This is the default setting. You can overrule the default sorting order on a per album basis.</small>
            </p>';
			
        echo '<p>
                <input type="checkbox" name="wppa-list-photos-desc" id="wppa-list-photos-desc"'; if (get_option('wppa_list_photos_desc') == 'yes') echo ' checked="checked"'; echo ' />
                <label for="wppa-list-photos-desc">Descending. i.e. largest first.<small>This is a system wide setting.</small></label>        
            </p>';
			
		$level = get_option('wppa-accesslevel');

		switch ($level) {
			case "level_10":
				$l10 = ' selected="selected"';
				break;
			case "level_7":
				$l7 = ' selected="selected"';
				break;
			case "level_2":
				$l2 = ' selected="selected"';
				break;
			case "level_1":
				$l1 = ' selected="selected"';
				break;
		}
		
		echo '<p>
			<label for="wppa-accesslevel">Access Level: <small>The user levels that can access the photo album admin</small></label><br />
			<select name="wppa-accesslevel">
				<option value="level_10"' . $l10 . '>Administrator</option> 
				<option value="level_7"' . $l7 . '>Editor</option>
				<option value="level_2"' . $l2 . '>Author</option>
				<option value="level_1"' . $l1 . '>Contributor</option>				
			</select>
			';
			
		echo '<p>
				<input type="submit" name="wppa-set-submit" value="Save Changes" />
			</p>';
		echo '</form>';
		
		echo '</div>';
}

function wppa_page_help() {

		echo '<div class="wrap">
		
		<h2>Help and Information</h2>
		
		<h3>Plugin Description</h3>
        <p>This plugin is designed to easily manage and display your photo albums within your WordPress site.<br/>
            New features of WP Photo Album Plus with respect to WP Photo Album include:<br/>
            Subalbums, specification of album and photo order, slideshow.<br/>
            Albums may contain photos and albums at the same time. Albums may be nested to any depth.<br/>
            The only restriction is that the top-level album can only contain albums. This is exactly as it was before.<br/>
            Besides a system wide setting for the ordering of albums and photos, the photo sorting method
            can be set specifically for each album.<br/>
            Currently available sorting methods are: sort by Order #, 
            sort by Name, and sort Randomly.		
        </p>

		<h3>About and credits</h3>
		<p>WP Photo Album was originally created by R.J. Kaplan.<br/>
        As various requests for bugfixing and enhancements did not lead to response from the original author, the project is adopted by J.N. Breetvelt
        <a href="http://www.opajaap.nl/">(OpaJaap)</a></p>
        <p>WP Photo Album Plus is based upon WP Photo Album 1.5.1</p>
		
		<h3>Plugin Admin Features</h3>
		<p>You can find the plugin admin section under Manage then submenu Photos.</p>
		<p>Manage and create albums<br />
		Move photos to and from albums<br />
		Upload and delete photos<br />
		Adjust thumbnail and full view picture sizes (set default max sizes for each).<br />
        
        <h3>Additions and modifications by OpaJaap</h3>
        <ol>
        <li>Uplooading less than the maximum number of photos no longer produces an errormessage</li>
        <li>The number of photos that can be uploaded at once is increased from 10 to 15.</li>
        <li>The last album used is remembered. This is especially usefull when uploading large amounts of photos</li>
        <li>Sub albums exist. You can nest albums to any desired depth.</li>
        <li>There are various ways to sort the albums</li>
        <li>You can choose to sort the albums in reverse order</li>
        <li>The album order seleced is used both in the admin pages as well as in the theme</li>
        <li>There are various ways to sort the photos in an album. The sorting order can be set differently for each album.</li>
        <li>You can choose to sort the photos in reverse order</li>
        <li>The photo order seleced is used both in the admin pages as well as in the theme</li>
        <li>The capability of running a slide show has been added</li>
        </ol>

        <h3>Upgrading from WP Photo Album</h3>
        <p>When upgrading from WP Photo Album to WP Photo Album Plus be aware of:</p>
        <ol>
        <li>First de-activate WP Photo Album before activating WP Photo Album Plus!!<br/>YOU CAN NOT RUN BOTH VERSIONS AT THE SAME TIME!!</li>
        <li>The existing database and albums and photos will be preserved.<br/>
            YOU DO NOT NEED TO RE-UPLOAD YOUR PHOTOS</li>
        <li>The database tables will be upgraded automatically to hold the extra data for the new features.</li>
        <li>You will need to use the newly supplied default theme file \'wppa_theme.php\' and/or modify
            your current theme file as the callable functions (tags) are changed with respect to WP Photo Album.</li>
        <li>You can use existing albums to make sub-albums, simply by specifying in which album they belong.</li>
        </ol>
            
		<h3>Installation and Usage</h3>
        <ol>
        <li>Unzip and upload the wppa plugin folder to <tt>wp-content/plugins/</tt></li>
        <li>Make sure that the folder wp-content/uploads/ exists and is writable by the server (CHMOD 755)</li>
        <li>Activate the plugin in WP Admin -> Plugins.</li>
        <li>Create at least one album in the albums tab</li>
        <li>In the uploads tab, you can now upload you photots</li>
        </ol>

		<h3>Creating Photo Album Page</h3>
		<p>Create a page like you normally would in WordPress. In my example, we\'ll give it the page title of "Photo Gallery". In the Page Content section add the following code: <br />
		<tt>%%wppa%%</tt><br />
        Additionally, if you want to display the <b>contents</b> of a specific album add the following line (e.g. for album number 19, replace 19 by the album number you wish) <b>after</b> <tt>%%wppa%%</tt>:<br/>
        <tt>%%album=19%%</tt><br />
        If you want to display the <b>cover</b> of album #19, create a new album, use his number and make album 19\'s parent that album number.<br />
        Upload <b>one</b> photo to the parent album. This photo will be used as the cover photo, and not displayed inside the album.<br/>

		Also, make sure under \'Page Template\' you are using \'Default Template\' as some WordPress themes have an archives template.<br />
		Press the publish button and you\'re done. You\'ll now have a photo gallery page. </p>

		<p>You can also create a custom page template by dropping the following code into a page:<br />
		<tt>&lt;?php wppa_albums(); ?&gt;</tt><br />
        Alternatively, you can specify a single album in the template by passing the album number as argument (e.g. for album # 19:<br />
        <tt>&lt;?php wppa_albums(19); ?&gt;</tt><br />
		In order to work properly, this tag needs to be within the <a href="http://codex.wordpress.org/The_Loop">WordPress loop</a>. 

For more information on creating custom page templates, click <a href="http://codex.wordpress.org/Pages#Creating_your_own_Page_Templates">here</a>.</p>

		<h3>Adjusting CSS and Template Styling</h3>
		<p>WP Photo Album comes with a default layout and theme. To change the style and layout of the photo album, copy them/edit wppa_theme.php and theme/wppa_style.css to your active theme\'s folder, and edit them. 

WPPA uses a system of tags similar to the WordPress theme system. To view a list of available tags, please read tags.txt


		<h3>Plugin Support And Feature Request</h3>
		<p>If you\'ve read over this readme carefully and are still having issues, if you\'ve discovered a bug, 
        or have a feature request, please contact me via my <a href="mailto:opajaap@opajaap.nl?subject=WP%20Photo%20Album%20Plus">E-mail</a>.</p>
		
		<h3>Licence</h3>
		<p>WP Photo Album is released under the <a href="http://www.gnu.org/copyleft/gpl.html">GNU GPL</a> licence.</p>
		
		</div>';
}

/* get the albums */
function wppa_admin_albums() {
	global $wpdb;
	$albums = $wpdb->get_results("SELECT * FROM " . ALBUM_TABLE . " " . wppa_get_album_order(), 'ARRAY_A');
	
	if (!empty($albums)) {
	
		echo '<table class="widefat">
			<thead>
			<tr>
				<th scope="col">ID</th>
                <th scope="col">Parent</th>
				<th scope="col">Name</th>
				<th scope="col">Description</th>
				<th scope="col">Edit</th>
				<th scope="col">Delete</th>	
                <th scope="col">Order</th>
			</tr>
			</thead>';
			
			$alt = ' class="alternate" ';
			
			foreach ($albums as $album) {
				echo '<tr' . $alt . '><td>' . $album['id'] . '</td><td>' . $album['a_parent'] . '</td><td>' . $album['name'] . '</td><td><small>' . $album['description'] . '</small></td><td><a href="admin.php&#63;page=' . PLUGIN_PATH . '/wppa.php&amp;tab=edit&amp;edit_id=' . $album['id'] . '" class="edit">Edit</a></td><td><a href="admin.php?page=' . PLUGIN_PATH . '/wppa.php&amp;tab=del&amp;id=' . $album['id'] . '" class="delete">Delete</a></td><td>' . $album['a_order'] . '</td></tr>';			
				if ($alt == '') { $alt = ' class="alternate" '; } else { $alt = '';}
			}
			
		echo '</table>';
	
	} else { 
		echo "No albums yet."; 
	}
}

// get photo edit list for albums
function wppa_album_photos($id) {
	global $wpdb;
	$photos = $wpdb->get_results("SELECT * FROM " . PHOTO_TABLE . " WHERE album=$id " . wppa_get_photo_order($id), 'ARRAY_A');
	if (empty($photos)) {
		return "<p>No photos yet in this album.</p>";
	} else {
		foreach ($photos as $photo) {
			echo '<div class="photoitem">';
			echo '<img src="' . get_bloginfo('wpurl') . '/wp-content/uploads/wppa/thumbs/' . $photo['id'] . '.' . $photo['ext'] . '" alt="' . $photo['name'] . '" />';
			echo '<div class="details">';
				echo '<p>Name: <input type="text" name="photos[' . $photo['id'] . '][name]" value="' . $photo['name'] . '" /></p>';
				echo '<p>Album: <select name="photos[' . $photo['id'] . '][album]">' . wppa_album_select('', $id) . '</select></p>';
				echo '<input type="hidden" name="photos[' . $photo['id'] . '][id]" value="' . $photo['id'] . '" />';
                echo '<p>Order: <input type="text" name="photos[' . $photo['id'] . '][p_order]" value="' . $photo['p_order'] . '" /></p>';
				echo '<p><a href="' . get_option('siteurl') . '/wp-admin/admin.php?page=' . PLUGIN_PATH . '/wppa.php&amp;tab=edit&amp;edit_id=' . $_GET['edit_id'] . '&amp;photo_del=' . $photo['id'] . '" class="deletelink" onclick="return confirm(\'Are you sure you want to delete this photo?\')">Delete</a></p>';
			echo '</div>';
			echo '<div class="desc">Description:<br /><textarea cols="40" rows="4" name="photos[' . $photo['id'] . '][description]">' . $photo['description'] . '</textarea></div>';
			echo '<div class="clear"></div>';
			echo '</div>';
		}
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
function wppa_album_select($exc = '', $sel = '', $addnone = FALSE) {
	global $wpdb;
	$albums = $wpdb->get_results("SELECT * FROM " . ALBUM_TABLE, 'ARRAY_A');
	
    if ($sel == '') {
        $s = wppa_get_last_album(TRUE);
        if ($s != $exc) $sel = $s;
    }
    
    $result = '';
    if ($addnone) $result .= '<option value="0">--- none ---</option>';
    
	foreach ($albums as $album) {
		if ($sel == $album['id']) { 
            $selected = ' selected="selected" '; 
        } 
        else { $selected = ''; }
		if ($album['id'] != $exc) {
			$result .= '<option value="' . $album['id'] . '"' . $selected . '>' . $album['name'] . '</option>';
		}
	}
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
    else echo '<div id="message" class="error"><p><strong>Album Name cannot be empty.</strong></p></div>';
}

// edit an album 
function wppa_edit_album() {
	global $wpdb;
    $first = TRUE;
	$name = $_POST['wppa-name'];
	$desc = $_POST['wppa-desc'];
	$main = $_POST['wppa-main'];
    $order = $_POST['wppa-order']; if (!is_numeric($order)) $order = 0;
    $parent = $_POST['wppa-parent']; if (!is_numeric($parent)) $parent = 0;
    $orderphotos = $_POST['wppa-list-photos-by']; if (!is_numeric($orderphotos)) $orderphotos = 0;
	
    // update the photo information
    if (isset($_POST['photos']))
	foreach ($_POST['photos'] as $photo) {
        $photo['name'] = esc_attr($photo['name']);
        if (!is_numeric($photo['p_order'])) $photo['p_order'] = 0;
		$query = "UPDATE " . PHOTO_TABLE . " SET name='{$photo['name']}', album={$photo['album']}, description='{$photo['description']}', p_order={$photo['p_order']} WHERE id={$photo['id']} LIMIT 1";
		$iret = $wpdb->query($query);
        if ($iret === FALSE) {
            if ($first) echo '<div id="message" class="error"><p><strong>Could not update photo.</strong></p></div>';
            $first = FALSE;
        }
	}
	
	// update the album information
	if (!empty($name)) {
        $iret = $wpdb->query("UPDATE " . ALBUM_TABLE . " SET name='$name', description='$desc', main_photo='$main', a_order='$order', a_parent='$parent', p_order_by='$orderphotos' WHERE id={$_GET['edit_id']}");
        if ($iret === FALSE) echo '<div id="message" class="error"><p><strong>Album could not be updated.</strong></p></div>';
        wppa_set_last_album($_GET['edit_id']);
		echo '<div id="message" class="updated fade"><p><strong>Album information edited. <a href="admin.php?page=' . PLUGIN_PATH . '/wppa.php">Back to album management</a></strong></p></div>';
	} 
    else echo '<div id="message" class="error"><p><strong>Album Name cannot be empty.</strong></p></div>';
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
	
	echo '<div id="message" class="updated fade"><p><strong>Album Deleted.</strong></p></div>';
}

// select main photo
function wppa_main_photo($cur = '') {
	global $wpdb;
    $a_id = $_GET['edit_id'];
	$photos = $wpdb->get_results("SELECT * FROM " . PHOTO_TABLE . " WHERE album=$a_id " . wppa_get_photo_order($a_id), 'ARRAY_A');
	
	if (!empty($photos)) {
		$output .= '<select name="wppa-main">';
		$output .= '<option value="">--- none ---</option>';

		foreach($photos as $photo) {
			if ($cur == $photo['id']) { $selected = 'selected="selected"'; } else { $selected = ''; }
			$output .= '<option value="' . $photo['id'] . '" ' . $selected . '>' . $photo['name'] . '</option>
			';
		}
		
		$output .= '</select>';
	} else {
		$output = '<p>No photos yet</p>';
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
	
	if ($uploaded_a_file) { 
        echo '<div id="message" class="updated fade"><p><strong>Photos Uploaded in album nr ' . $_POST['wppa-album'] . '.</strong></p></div>'; 
        wppa_set_last_album($_POST['wppa-album']);
    }
}

/* Add Javascript to page head */
add_action('admin_head', 'wppa_admin_head');

function wppa_admin_head() {
 	echo '<script type="text/javascript" src="' . get_bloginfo('wpurl') .'/wp-content/plugins/' . PLUGIN_PATH . '/multifile_compressed.js"></script>
 	<link rel="stylesheet" href="' .  get_bloginfo('wpurl') .'/wp-content/plugins/' . PLUGIN_PATH . '/admin_styles.css" type="text/css" media="screen" />
	';
}

// update all thumbs 
function wppa_regenerate_thumbs() {
	global $wpdb;
	$thumbsize = get_option('wppa_thumbsize');
	$wppa_dir = ABSPATH . 'wp-content/uploads/wppa/';

	$photos = $wpdb->get_results("SELECT * FROM " . PHOTO_TABLE , 'ARRAY_A');
	
	if (!empty($photos)) {
		foreach ($photos as $photo) {
			$newimage = $wppa_dir . $photo['id'] . '.' . $photo['ext'];
			wppa_create_thumbnail($newimage, $thumbsize, '' );
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
			$error = __( 'Filetype not supported. Thumbnail not created.' );
		}
		elseif (!function_exists( 'imagejpeg' ) && $type[2] == 2 ) {
			$error = __( 'Filetype not supported. Thumbnail not created.' );
		}
		elseif (!function_exists( 'imagepng' ) && $type[2] == 3 ) {
			$error = __( 'Filetype not supported. Thumbnail not created.' );
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
					$error = __( "Thumbnail path invalid" );
				}
			}
			elseif ( $type[2] == 2 ) {
				if (!imagejpeg( $thumbnail, $thumbpath ) ) {
					$error = __( "Thumbnail path invalid" );
				}
			}
			elseif ( $type[2] == 3 ) {
				if (!imagepng( $thumbnail, $thumbpath ) ) {
					$error = __( "Thumbnail path invalid" );
				}
			}

		}
	} else {
		$error = __( 'File not found' );
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
add_filter('the_content', 'wppa_albums_filter', 1);
}

function wppa_albums_filter($post) {
    global $startalbum;
	if (substr_count($post, '%%wppa%%') > 0) {
	
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
            $post = str_replace('%%album=' . $alb . '%', '', $post);  // remove from content
        }
        
		$post = str_replace('%%wppa%%', wppa_albums(), $post);

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
		include_once($templatefile);
	} else {
		include_once(ABSPATH . 'wp-content/plugins/' . PLUGIN_PATH . '/theme/wppa_theme.php');
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
    $sep = '&nbsp;' . $xsep . '&nbsp;';
    $home = 'Home';
    echo '<a href="' . get_bloginfo('url') . '" class="backlink">' . $home . '</a>' . $sep;
    if (isset($_GET['album'])) $alb = $_GET['album']; else $alb = 0;
	if ($alb == 0) {
        the_title();
	/*	echo 'Foto album';  */
		return;
	} else {
		echo '<a href="' . get_permalink()  . '" class="backlink">'; the_title(); echo '</a>' . $sep;
        wppa_crumb_ancestors($sep, $alb);
		if (!isset($_GET['photo'])) {
			echo wppa_album_name($_GET['album'], TRUE);
			return;
		} else {
			echo '<a href="' . get_permalink() . wppa_sep() . 'album=' . $_GET['album'] . '" class="backlink">' . wppa_album_name($_GET['album'], TRUE) . '</a>' . $sep;
			echo wppa_photo_name($_GET['photo']);
		}
	}
}

function wppa_crumb_ancestors($sep, $alb) {
    $parent = wppa_get_parentalbumid($alb);
    if ($parent == 0) return;
    wppa_crumb_ancestors($sep, $parent);
    echo '<a href="' . get_permalink() . wppa_sep() . 'album=' . $parent . '" class="backlink">' . wppa_album_name($parent, TRUE) . '</a>' . $sep;
    return;
}

// Get the albums parent
function wppa_get_parentalbumid($alb) {
    global $wpdb;
    
    $query = "SELECT a_parent FROM " . ALBUM_TABLE . " WHERE id=$alb";
    $result = $wpdb->get_var($query);
    return $result;
}

// get album title by id
function wppa_album_name($id = '', $return = FALSE) {
	global $wpdb;
    
	if ($id == '') $id = $_GET['album'];
	$id = $wpdb->escape($id);	
	if (is_numeric($id)) $name = $wpdb->get_var("SELECT name FROM " . ALBUM_TABLE . " WHERE id=$id");
	
	if ($return) return $name; else echo $name;
}

// get album id by title
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

// loop album
function wppa_get_albums() {
	global $wpdb;
    global $startalbum;

    if (isset($_GET['album'])) $parent = $_GET['album'];
    elseif (is_numeric($startalbum)) $parent=$startalbum;
    else $parent = 0;
    $albums = $wpdb->get_results("SELECT * FROM " . ALBUM_TABLE . " WHERE a_parent={$parent} " . wppa_get_album_order(),'ARRAY_A');
    return $albums;
}

// get link to album by id
function wppa_get_album_url($id) {
    $link = get_permalink() . wppa_sep() . 'album=' . $id;
    return $link;
}

// get link to album (in loop)
function wppa_album_url($return = FALSE) {
	global $album;
	$link = get_permalink() . wppa_sep() . 'album=' . $album['id'];
	
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
function wppa_the_album_name($return = FALSE) {
	global $album;
	
	if ($return) return $album['name']; else echo $album['name'];	
}

// get album decription
function wppa_the_album_desc($return = FALSE) {
	global $album;
	
	if ($return) return $album['description']; else echo $album['description'];	
}

// get link to slideshow (in loop)
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
function wppa_get_photo_page_url() {
	global $thumb;
    
	$url = get_permalink()  . wppa_sep() . 'album=' . $_GET['album'] . '&amp;photo=' . $thumb['id'];
	return $url; 
}

// get url of thumb
function wppa_get_thumb_url() {
	global $thumb;
    
	$url = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/thumbs/' . $thumb['id'] . '.' . $thumb['ext'];
	return $url; 
}

// get url of a full sized image
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
	
	if ($return) return $name; else echo $name;
}

// get the description of a full sized image
function wppa_photo_desc($id = '', $return = FALSE) {
	global $wpdb;
	if ($id == '') $id = $_GET['photo'];
	$id = $wpdb->escape($id);
	
	if (is_numeric($id)) $desc = $wpdb->get_var("SELECT description FROM " . PHOTO_TABLE . " WHERE id=$id");
	
	if ($return) return $desc; else echo $desc;
}

// prev/next links
function wppa_prev_next($prev = '&laquo;<a href="%link%">Previous Photo</a> ', $next = '<a href="%link%">Next Photo</a>&raquo;', $id='', $return = FALSE) {
	global $wpdb;

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
		$link = get_permalink()  . wppa_sep() . 'album=' . $_GET['album'] . '&amp;photo=' . $ids[$prev_pos][0];
		$result .= str_replace('%link%', $link, $prev);
	}
	
	// if is not last photo
	if ($position < (count($ids) - 1)) {
		$next_pos = $position + 1;
		$link = get_permalink()  . wppa_sep() . 'album=' . $_GET['album'] . '&amp;photo=' . $ids[$next_pos][0];
		$result .= str_replace('%link%', $link, $next);
	}
	
	if ($return) return $result; else echo $result;
}

// get height or width limit
function wppa_get_fullsize($id = '') {
	global $wpdb;
	if (empty($id)) { $id = $_GET['photo']; }
	$id = $wpdb->escape($id);
	
	if (is_numeric($id)) {
		$ext = $wpdb->get_var("SELECT ext FROM " . PHOTO_TABLE . " WHERE id=$id");
	}
	$img_path = ABSPATH . 'wp-content/uploads/wppa/' . $id . '.' . $ext;
	if(is_file($img_path)) {
		$size = getimagesize($img_path);
	}
	
	$fullsize = get_option('wppa_fullsize');
	
	if (is_numeric($fullsize)) {
		if ($size[0] >= $size[1]) {
			$result = 'width="' . $fullsize . '"';
		} else {
			$result = 'height="' . $fullsize . '"';
		}
	}
	return $result;
}

// get slide info
function wppa_get_slide_info($index, $id) {
    $result = "'" . $index . "','" . wppa_photo_url($id, TRUE) . "','" . wppa_get_fullsize($id) . "','" . esc_attr(wppa_photo_name($id, TRUE)) . "','" . esc_attr(wppa_photo_desc($id, TRUE)) . "'";
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
function wppa_get_last_album( $return = FALSE) {
    global $albumid;
    
    if (is_numeric($albumid)) $result = $albumid;
    else $result = get_option('wppa_last_album_used');
    if (!is_numeric($result)) $result = '';
    else $albumid = $result;

	if ($return) return $result; else echo $result;
}

// display order options
function wppa_order_options($order, $nil) {
    if ($nil != '') {
        echo '<option value="0"'; if ($order == "" || $order == "0") echo ' selected="selected"'; echo '>' . $nil . '</option>';
    }
    echo '    
    <option value="1"'; if ($order == "1") echo ' selected="selected"'; echo '>Order #</option>
    <option value="2"'; if ($order == "2") echo ' selected="selected"'; echo '>Name</option>
    <option value="3"'; if ($order == "3") echo ' selected="selected"'; echo '>Random</option>  
    ';
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
    
    $order = $wpdb->get_var("SELECT p_order_by FROM " . ALBUM_TABLE . " WHERE id=$id");
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
function wppa_debug($msg) {
    echo '<div id="message" class="updated fade"><p><strong>' . $msg . '</strong></p></div>';
}