<?php
/* wppa_functions.php
* Pachkage: wp-photo-album-plus
*
* Various funcions and API modules
* Version 2.0.1
*/

/* TEMPLATE FUNCTIONS (TAGS) */

// shows the breadcrumb navigation
function wppa_breadcrumb($xsep = '&raquo;', $opt = '') {
	global $wppa_occur;
	global $startalbum;
	global $wppa_local_occur;
	
	if (is_numeric($wppa_occur) && $wppa_occur > '0') $wppa_local_occur = $wppa_occur; else $wppa_local_occur = '1'; /* default for call outside the loop */
	
	if ($opt == 'optional' && get_option('wppa_show_bread', 'yes') == 'no') return;

	$sep = '&nbsp;' . $xsep . '&nbsp;';
	if (get_option('wppa_show_home', 'yes') == 'yes') echo '<a href="' . get_bloginfo('url') . '" class="backlink">' . __('Home', 'wppa') . '</a>' . $sep;
	
	if (isset($_GET['occur'])) $occur = $_GET['occur'];
	else $occur = '1';
	if ($occur == $wppa_local_occur) $this_occur = true; 
	else $this_occur = false;
	
    if (isset($_GET['album']) && $this_occur) $alb = $_GET['album']; 
	elseif (is_numeric($startalbum)) $alb = $startalbum;
	else $alb = 0;
	$separate = wppa_is_separate($alb);
	
	if ($alb == 0) {
        if (!$separate) the_title();
		return;
	} else {
		if (!$separate) {
			echo '<a href="' . get_permalink() . wppa_sep() . 'occur=' . $wppa_local_occur . '" class="backlink">'; the_title(); echo '</a>' . $sep;
		}
        wppa_crumb_ancestors($sep, $alb);
		if (isset($_GET['photo']) && $this_occur) {
			echo '<a href="' . get_permalink() . wppa_sep() . 'album=' . $alb . '&cover=0&occur=' . $wppa_local_occur . '" class="backlink">' . wppa_album_name($alb, TRUE) . '</a>' . $sep;
			echo '<a id="bc-pname" >' . wppa_photo_name($_GET['photo'], true) . '</a>';
		} elseif (isset($_GET['slide']) && $this_occur) {
			echo '<a href="' . get_permalink() . wppa_sep() . 'album=' . $alb . '&cover=0&occur=' . $wppa_local_occur . '" class="backlink">' . wppa_album_name($alb, TRUE) . '</a>' . $sep;
			echo '<a id="bc-pname" >' . __('Slideshow', 'wppa') . '</a>';
		} else {
			echo wppa_album_name($alb, TRUE); 
			return;
		} 
	}
}

function wppa_crumb_ancestors($sep, $alb) {
global $wppa_local_occur;
    $parent = wppa_get_parentalbumid($alb);
    if ($parent < 1) return;
    
    wppa_crumb_ancestors($sep, $parent);
   
    echo '<a href="' . get_permalink() . wppa_sep() . 'album=' . $parent . '&cover=0&occur=' . $wppa_local_occur . '" class="backlink">' . wppa_album_name($parent, TRUE) . '</a>' . $sep;
    return;
}

// Get the albums parent
function wppa_get_parentalbumid($alb) {
    global $wpdb;
    
	$query = $wpdb->prepare('SELECT `a_parent` FROM `' . ALBUM_TABLE . '` WHERE `id` = %d', $alb);
	$result = $wpdb->get_var($query);
	
    if (!is_numeric($result)) {
		$result = 0;
	}
    return $result;
}

// See if an album is another albums ancestor
function wppa_is_ancestor($anc, $xchild) {
	$child = $xchild;
	if (is_numeric($anc) && is_numeric($child)) {
		$parent = wppa_get_parentalbumid($child);
		while ($parent > '0') {
			if ($anc == $parent) return true;
			$child = $parent;
			$parent = wppa_get_parentalbumid($child);
		}
	}
	return false;
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
	return wppa_album_name($id, TRUE);
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
	$name = stripslashes($name);
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
	global $wppa_occur;

	$occur = '0';
	if (isset($_GET['occur'])) if (is_numeric($_GET['occur'])) $occur = $_GET['occur'];

	if ($occur != $wppa_occur) {
		$cur_page = 'albums';
	}
	else {
		if (isset($_GET['slide'])) $cur_page = 'slide';	
		elseif (isset($_GET['photo'])) $cur_page = 'single';
		else $cur_page = 'albums';
	}
	
	if ($cur_page == $page) return TRUE; else return FALSE;
}

// get url of current album image
function wppa_get_image_url($key = '') {
	global $wpdb, $album, $saved_id;
	
	if ($key == 'use_id') {
		$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE id={$saved_id} LIMIT 0,1", 'ARRAY_A');
	}
	else {
		// cehck if a main photo is set
		if (empty($album['main_photo'])) {
			$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE album={$album['id']} ORDER BY RAND() LIMIT 0,1", 'ARRAY_A');
		} else {
			$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE id={$album['main_photo']} LIMIT 0,1", 'ARRAY_A');
		}
	}
	
	if (!empty($image)) {
		$imgurl = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/thumbs/' . $image['id'] . '.' . $image['ext'];
		if ($key == 'save_id') $saved_id = $image['id'];
	}
	else $imgurl = '';
		
	return $imgurl;
}
function wppa_get_image_path($key = '') {
	global $wpdb, $album, $saved_id;
		
	if ($key == 'use_id') {
		$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE id={$saved_id} LIMIT 0,1", 'ARRAY_A');
	}
	else {
		// cehck if a main photo is set
		if (empty($album['main_photo'])) {
			$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE album={$album['id']} ORDER BY RAND() LIMIT 0,1", 'ARRAY_A');
		} else {
			$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE id={$album['main_photo']} LIMIT 0,1", 'ARRAY_A');
		}
	}
	
	if (!empty($image)) {
		$imgurl = ABSPATH . 'wp-content/uploads/wppa/thumbs/' . $image['id'] . '.' . $image['ext'];
		if ($key == 'save_id') $saved_id = $image['id'];
	}
	else $imgurl = '';
		
	return $imgurl;
}

function wppa_get_image_page_url() {
	return wppa_image_page_url(TRUE);
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

	if (!empty($image)) $imgurl = get_permalink()  . wppa_sep() . 'album=' . $album['id'] . '&photo=' . $image['id'] . '&cover=0';	
	else $imgurl = '';
	
	if ($return) return $imgurl; else echo $imgurl;
}

// loop album
function wppa_get_albums($album = false, $type = '') {
	global $wpdb;
    global $startalbum;
	global $is_cover;
	global $wppa_occur;
		
	$occur = '0';
	if (isset($_GET['occur'])) if (is_numeric($_GET['occur'])) $occur = $_GET['occur'];
	
	// Check if querystring given This has the highest priority in case of matching occurrance
	// Obey querystring only if the global occurence matches the occurence in the querystring, or no query occurrence given.
	if (($occur == $wppa_occur) && (isset($_GET['album']))) {
		$id = $_GET['album'];
		if (isset($_GET['cover'])) $is_cover = $_GET['cover'];
	}
	// Check if parameters set
	elseif (is_numeric($album)) {
		$id = $album;
		if ($type == 'album') $is_cover = '0';
		if ($type == 'cover') $is_cover = '1';
	}
	// Check if globals set
	elseif (is_numeric($startalbum)) {
		$id = $startalbum;
	}
	// The default: all albums with parent = 0;
	else $id = '0';
	
	// Top-level album has no cover
	if ($id == '0') $is_cover = '0';
	
	// Do the query
	if (is_numeric($id)) {
	    if ($is_cover) $q = $wpdb->prepare('SELECT * FROM ' . ALBUM_TABLE . ' WHERE id= %d', $id);
        else $q = $wpdb->prepare('SELECT * FROM ' . ALBUM_TABLE . ' WHERE a_parent= %d '. wppa_get_album_order(), $id);
		$albums = $wpdb->get_results($q, 'ARRAY_A');
	}
	else $albums = false;
	return $albums;
}

// get link to album by id or in loop
function wppa_get_album_url($xid = '') {
	global $album;
	if ($xid != '') $id = $xid;
	else $id = $album['id'];
    $link = get_permalink() . wppa_sep() . 'album=' . $id . '&cover=0';
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

// get number of albums in system
function wppa_get_total_album_count() {
	global $wpdb;
	
	$count = $wpdb->query("SELECT * FROM " . ALBUM_TABLE);
	return $count;
}

// get youngest album id
function wppa_get_youngest_album_id() {
	global $wpdb;
	
	$result = $wpdb->get_var("SELECT id FROM " . ALBUM_TABLE . " ORDER BY id DESC LIMIT 1");
	return $result;
}

// get youngest album name
function wppa_get_youngest_album_name() {
	global $wpdb;
	
	$result = $wpdb->get_var("SELECT name FROM " . ALBUM_TABLE . " ORDER BY id DESC LIMIT 1");
	return stripslashes($result);
}

// get album name
function wppa_get_the_album_name() {
	global $album;
	
	return stripslashes($album['name']);
}

function wppa_the_album_name($return = FALSE) {
	global $album;
	
	if ($return) return stripslashes($album['name']); else echo stripslashes($album['name']);	
}

// get album decription
function wppa_get_the_album_desc() {
	global $album;
	return stripslashes($album['description']);
}
function wppa_the_album_desc($return = FALSE) {
	global $album;
	
	if ($return) return stripslashes($album['description']); else echo (stripslashes($album['description']));	
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
	global $wppa_occur;

	$occur = '0';
	if (isset($_GET['occur'])) if (is_numeric($_GET['occur'])) $occur = $_GET['occur'];
	
	// Obey querystring only if the global occurence matches the occurence in the querystring, or no query occurrence given.
	if (($occur == $wppa_occur) && (isset($_GET['album']))) {
		$id = $_GET['album'];
	}
    
//    if (isset($_GET['album'])) $album = $_GET['album'];
    elseif (is_numeric($startalbum)) $id = $startalbum; 
    else $id = 0;
	if (is_numeric($id)) {
		$thumbs = $wpdb->get_results("SELECT * FROM " . PHOTO_TABLE . " WHERE album=$id " . wppa_get_photo_order($id), 'ARRAY_A'); 
	}
	else {
		$thumbs = false;
	}
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

function wppa_get_thumb_path() {
	global $thumb;
	
	$path = ABSPATH . 'wp-content/uploads/wppa/thumbs/' . $thumb['id'] . '.' . $thumb['ext'];
	return $path; 
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
function wppa_get_photo_desc($id = '') {
	return wppa_photo_desc($id, TRUE);
}
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

// get full img style
function wppa_fullimgstyle($id = '') {
	echo wpa_get_fullimgstyle($id);
}
function wpa_get_fullimgstyle($id = '') {
	global $wpdb;
    global $wppa_fullsize;
	global $wppa_no_enlarge;
	
	if (!is_numeric($wppa_fullsize)) $wppa_fullsize = get_option('wppa_fullsize');
	if (!is_numeric($wppa_fullsize)) $wppa_fullsize = '800';

	$wppa_enlarge = get_option('wppa_enlarge', 'yes');
	
	if (empty($id)) $id = $_GET['photo'];
	if (is_numeric($id)) {
		$ext = $wpdb->get_var("SELECT ext FROM " . PHOTO_TABLE . " WHERE id=$id");
	}
	$img_path = ABSPATH . 'wp-content/uploads/wppa/' . $id . '.' . $ext;
	$result = wppa_get_imgstyle($img_path, $wppa_fullsize, 'optional', 'fullsize');
	return $result;
}

// get slide info
function wppa_get_slide_info($index, $id) {
    $result = "'" . $index . "','" . wppa_get_photo_url($id) . "','" . wpa_get_fullimgstyle($id) . "','" . esc_js(wppa_photo_name($id, TRUE)) . "','" . wppa_html(esc_js(wppa_photo_desc($id,true))) . "'";
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
global $wppa_no_nonce;
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
	$result = '300';
	
	$tmp = get_option('wppa_thumbsize', 'nil');
	if (is_numeric($tmp) && $tmp > $result) $result = $tmp;
	$tmp = get_option('wppa_smallsize', 'nil');
	if (is_numeric($tmp) && $tmp > $result) $result = $tmp;

	$result = ceil($tmp / 25) * 25;
	return $result;
}

function wppa_get_imgstyle($file, $max_size, $xvalign = '', $type = '') {	
	if($file == '') return '';					// no image: no dimensions
	$image_attr = getimagesize( $file );
	// figure out the longest side
	if ( $image_attr[0] > $image_attr[1] ) {	//width is > height
		$width = $max_size;
		$height = round($max_size * $image_attr[1] / $image_attr[0]);
	} else {									//height > width
		$height = $max_size;
		$width = round($max_size * $image_attr[0] / $image_attr[1]);
	}
	// figure out if a too small img must be shown as is
	if ($image_attr[0] < $max_size && $image_attr[1] < $max_size) $too_small = true;
	else $too_small = false;
	if ($too_small) $stretch = get_option('wppa_enlarge', 'no');
	else $stretch = 'yes';
	if ($stretch == 'no') {
		$width = $image_attr[0];
		$height = $image_attr[1];
	}
	// compose the size result
	if ($image_attr[0] > $image_attr[1]) $result = ' width: ' . $width . 'px; ';
	else $result = ' height: ' . $height . 'px; ';
	// see if valign required
	if ($xvalign == 'optional') {
		if ($type == 'fullsize') {
			$valign = get_option('wppa_fullvalign', '');
		}
		else {
			$valign = get_option('wppa_valign', '');
		}
	}
	else $valign = $xvalign;
	if ($valign == 'top') {
		$result .= ' margin-top: 0px; ';
	}
	elseif ($valign == 'center') {
		$delta = floor(($width - $height) / 2);
		if ($delta < '0') $delta = '0';
		$result .= ' margin-top: ' . $delta . 'px; ';
	}
	elseif ($valign == 'bottom') {
		$delta = $width - $height;
		if ($delta < '0') $delta = '0';
		$result .= ' margin-top: ' . $delta . 'px; ';
	}
	// see if horizontal center needed
	if ($type != 'cover') {
		if ($valign != 'default') {
			$delta = floor(($height - $width) / 2);
			if ($delta < '0') $delta = '0';
			$result .= ' margin-left:' . $delta . 'px; ';
		} 
	}
	// see if hover effect enabled
	if ($type == 'thumb') {
		if (get_option('wppa_use_thumb_opacity', 'no') == 'yes') {
			$opac = get_option('wppa_thumb_opacity', '80');
			$result .= ' opacity:' . $opac/100 . '; filter:alpha(opacity=' . $opac . ')';
		}
	} elseif ($type == 'cover') {
		if (get_option('wppa_use_cover_opacity', 'no') == 'yes') {
			$opac = get_option('wppa_cover_opacity', '80');
			$result .= ' opacity:' . $opac/100 . '; filter:alpha(opacity=' . $opac . ')';
		}
	}
	return $result;
}

function wppa_get_imgevents($type = '', $id = '') {
global $wppa_occur;
	$result = '';
	$perc = '';
	if ($type == 'thumb') {
		if ((get_option('wppa_use_thumb_opacity', 'no') == 'yes') || (get_option('wppa_use_thumb_popup', 'no') == 'yes')) {
			
			if (get_option('wppa_use_thumb_opacity', 'no') == 'yes') {
				$perc = get_option('wppa_thumb_opacity', '80');
				$result = ' onmouseout="jQuery(this).fadeTo(400, ' . $perc/100 . ')" onmouseover="jQuery(this).fadeTo(400, 1.0);';
			} else {
				$result = ' onmouseover="';
			}
			if (get_option('wppa_use_thumb_popup', 'no') == 'yes') {
				$result .= 'wppa_popup(this, ' . $id . ' , ' . $wppa_occur . ');';
			}
			$result .= '" ';
		}
	}
	elseif ($type == 'cover') {
		if (get_option('wppa_use_cover_opacity', 'no') == 'yes') {
			$perc = get_option('wppa_cover_opacity', '80');
			$result = ' onmouseover="jQuery(this).fadeTo(400, 1.0)" onmouseout="jQuery(this).fadeTo(400, ' . $perc/100 . ')" ';
		}
	}		
	return $result;
}

function wppa_html($str) {
	if (get_option('wppa_html', 'no') == 'yes') {
		$result = html_entity_decode($str);
	}
	else {
		$result = $str;
	}
	return $result;
}

function wppa_get_imgdir() {
	$result = get_bloginfo('wpurl') . '/wp-content/plugins/' . PLUGIN_PATH . '/images/';
	return $result;
}

?>