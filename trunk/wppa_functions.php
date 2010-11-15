<?php
/* wppa_functions.php
* Pachkage: wp-photo-album-plus
*
* Various funcions and API modules
* Version 2.4.0
*/

global $wppa_api_version;
$wppa_api_version = '2-4-0-009';
/* build 002:fixed filmstrip dimensions for thumbnail sizes other than 100
/* build 003:fixed height in autocolwidth when valign = fit, thumb topmargin fix when in autocolwidth
/* build 004:fixed an old bug that came back. Wrong thumbnailsize (wppa_get_minisize()).
/* build 005:fix in filter.php
/* build 006:fix for occurrencies
/* build 007:fix remove spinner entirely (wppa_slideshow.js)
/* build 008:fixed broken rss due to the use of a cdata block
/* build 009:2nd attempt for fix for substr_count wrong parameter in wppa_filter.php
/*
/* TEMPLATE FUNCTIONS (TAGS) */

/* show system statistics */
function wppa_statistics() {
	echo(wppa_get_statistics());
}
function wppa_get_statistics() {
	$count = wppa_get_total_album_count();
	$y_id = wppa_get_youngest_album_id();
	$y_name = wppa_get_album_name($y_id);
	$p_id = wppa_get_parentalbumid($y_id);
	$p_name = wppa_get_album_name($p_id);
	
	$result = '<div class="wppa-box wppa-nav" style="text-align: center;">';
	$result .= __('There are', 'wppa').' '.$count.' '.__('photo albums. The last album added is', 'wppa').' ';
	$result .= '<a href="'.get_permalink().wppa_sep().'album='.$y_id.'&cover=0&occur=1">'.$y_name.'</a>';

	if ($p_id != '0') {
		$result .= __(', a subalbum of', 'wppa').' '; 
		$result .= '<a href="'.get_permalink().wppa_sep().'album='.$p_id.'&cover=0&occur=1">'.$p_name.'</a>';
	}
	
	$result .= '.</div>';
	
	return $result;
}

/* shows the breadcrumb navigation */
function wppa_breadcrumb($xsep = '&raquo;', $opt = '') {
	global $wppa_occur;
	global $wppa_master_occur;
	global $wppa_local_occur;
	global $startalbum;
	global $single_photo;

	if ($opt == 'optional' && get_option('wppa_show_bread', 'yes') == 'no') return;	/* Nothing to do here */
	if (wppa_page('oneofone')) return; /* Never at a single image */
	
	/* Do some preparations */
	$sep = '&nbsp;' . $xsep . '&nbsp;';
//	if ($wppa_master_occur > '0') $wppa_local_occur = $wppa_master_occur; else $wppa_local_occur = '1'; 
	if ($wppa_master_occur == '0') $wppa_local_occur = '1'; /* default for call outside the loop */
	else $wppa_local_occur = $wppa_occur;
	
	if (isset($_GET['occur'])) $occur = $_GET['occur'];
	else $occur = '1';
	if ($occur == $wppa_local_occur) $this_occur = true; 
	else $this_occur = false;
	
    if (isset($_GET['album']) && $this_occur) $alb = $_GET['album']; 
	elseif (is_numeric($startalbum)) $alb = $startalbum;
	else $alb = 0;
	$separate = wppa_is_separate($alb);
?>
	<div class="wppa-nav wppa-box wppa-nav-text">
<?php
		if (get_option('wppa_show_home', 'yes') == 'yes') {
?>
			<a href="<?php echo(get_bloginfo('url')); ?>" style="float: left" ><?php _e('Home', 'wppa'); ?></a><span style="float: left" ><?php echo($sep); ?></span>
<?php	
		}
		
		if (is_page()) wppa_page_breadcrumb($sep);	
	
		if ($alb == 0) {
			if (!$separate) echo('<span class="wppa-black b1" style="" >'); the_title(); echo('</span>');
		} else {	/* $alb != 0 */
			if (!$separate) {
?>
				<a href="<?php echo(get_permalink() . wppa_sep()); ?>occur=<?php echo($wppa_local_occur); ?>" class= "b2" style="float: left" ><?php the_title(); ?></a><span class="b3" style="float: left" ><?php echo($sep); ?></span>
<?php
			}
		    wppa_crumb_ancestors($sep, $alb);
			if (wppa_page('oneofone')) {
				$photo = $single_photo;
			}
			elseif (wppa_page('single')) {
				if (isset($_GET['photo'])) {
					$photo = $_GET['photo'];
				}
				else {
					$photo = '';
				}
			}
			else {
				$photo = '';
			}
		
			if (is_numeric($photo) && $this_occur) {
				echo '<a href="' . get_permalink() . wppa_sep() . 'album=' . $alb . '&cover=0&occur=' . $wppa_local_occur . '" class="wppa-nav-text b4" style="float: left" >' . wppa_get_album_name($alb) . '</a><span class="b5" style="float: left" >' . $sep .'</span>';
				echo '<span id="bc-pname-'.$wppa_local_occur.'" class="wppa-nav-text wppa-black b8" >' . wppa_photo_name($photo, true) . '</span>';
			} elseif ($this_occur && !wppa_page('albums')) { //($photo == '' && $this_occur && wppa_page('slide')) 
				echo '<a href="' . get_permalink() . wppa_sep() . 'album=' . $alb . '&cover=0&occur=' . $wppa_local_occur . '" class="wppa-nav-text b6" style="float: left" >' . wppa_get_album_name($alb) . '</a><span class="b7" style="float: left" >' . $sep .'</span>';
				echo '<span id="bc-pname-'.$wppa_local_occur.'" class="wppa-nav-text wppa-black b9" >' . __('Slideshow', 'wppa') . '</span>';
			} else {	// NOT This occurance OR album
				echo ('<span class="wppa-nav-text wppa-black b10" >' . wppa_get_album_name($alb) . '</span>'); 
			} 
		}
		if (isset($_POST['wppa-searchstring'])) {
			echo('<span class="b11" ><b>&nbsp;'.__('Searchstring:', 'wppa').'&nbsp;'.$_POST['wppa-searchstring'].'</b></span>');
		}
?>
	</div>
<?php
}
function wppa_crumb_ancestors($sep, $alb) {
	global $wppa_local_occur;
	
    $parent = wppa_get_parentalbumid($alb);
    if ($parent < 1) return;
    
    wppa_crumb_ancestors($sep, $parent);
   
    echo '<a href="' . get_permalink() . wppa_sep() . 'album=' . $parent . '&cover=0&occur=' . $wppa_local_occur . '" class="wppa-nav-text b20" style="float: left" >' . wppa_get_album_name($parent) . '</a>';
	echo '<span style="float: left">' . $sep . '</span>';
    return;
}
function wppa_page_breadcrumb($sep) {
	global $wpdb;
	
		if (isset($_REQUEST['page_id'])) $page = $_REQUEST['page_id'];
		else $page = '0';

		wppa_crumb_page_ancestors($sep, $page); 
}
function wppa_crumb_page_ancestors($sep, $page = '0') {
	global $wpdb;
	$query = "SELECT post_parent FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish' AND id = " . $page . " LIMIT 0,1";
	$parent = $wpdb->get_var($query);
	if (!is_numeric($parent) || $parent == '0') return;
	wppa_crumb_page_ancestors($sep, $parent);
	$query = "SELECT post_title FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish' AND id = " . $parent . " LIMIT 0,1";
	$title = $wpdb->get_var($query);
	if (!$title) {
		$title = '****';		// Page exists but is not publish
?>
	<a href="#" class="wppa-nav-text b30" style="float: left" ></a><span class="b31" style="float: left;" ><?php echo($title); ?><?php echo($sep); ?></span>
<?php
	} else {
?>
	<a href="<?php echo(get_page_link($parent)); ?>" class="wppa-nav-text b32" style="float: left;" ><?php echo($title); ?></a><span class="b32" style="float: left;" ><?php echo($sep); ?></span>
<?php
	}
}

// get the albums by inserting the theme template and do some parameter processing
function wppa_albums($xid = '', $typ='', $siz = '', $ali = '') {
	global $wppa_occur;
	global $wppa_master_occur;
    global $startalbum;
	global $is_cover;
	global $is_slide;
	global $wppa_fullsize;
	global $wppa_auto_colwidth;
	global $single_photo;
	global $wppa_align;
    
	$wppa_occur++;
	$wppa_master_occur++;
		
	if ($typ == 'album') {
		$is_cover = '0';
		$is_slide = '0';
	}
	elseif ($typ == 'cover') {
		$is_cover = '1';
		$is_slide = '0';
	}
	elseif ($typ == 'slide') {
		$is_cover = '0';
		$is_slide = '1';
	}
	
	if ($typ == 'photo') {
		$is_cover = '0';
		$is_slide = '0';
		if (is_numeric($xid)) {
			$single_photo = $xid;
		}
	}
	else {
		if (is_numeric($xid)) {
			$startalbum = $xid;
		}
	}
	
	if (is_numeric($siz)) {
		$wppa_fullsize = $siz;
	}
	elseif ($siz == 'auto') {
		$wppa_auto_colwidth = true;
	}
    
	if ($ali == 'left' || $ali == 'center' || $ali == 'right') {
		$wppa_align = $ali;
	}
	
	$templatefile = ABSPATH . 'wp-content/themes/' . get_option('template') . '/wppa_theme.php';
	
	// check for user template before using default template
	if (is_file($templatefile)) {
		include($templatefile);
	} else {
		include(ABSPATH . 'wp-content/plugins/' . WPPA_PLUGIN_PATH . '/theme/wppa_theme.php');
	}
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
	if (!is_numeric($xalb)) return false;
		
	$alb = wppa_get_parentalbumid($xalb);
	if ($alb == 0) return false;
	if ($alb == -1) return true;
	return (wppa_is_separate($alb));
}

// Get album title by id
function wppa_album_name($id = '') {
	echo(wppa_get_album_name($id));
}
function wppa_get_album_name($id = '') {
	global $wpdb;
    
    if ($id == '0') $name = __('--- none ---', 'wppa');
    elseif ($id == '-1') $name = __('--- separate ---', 'wppa');
    else {
        if ($id == '') if (isset($_GET['album'])) $id = $_GET['album'];
        $id = $wpdb->escape($id);	
        if (is_numeric($id)) $name = $wpdb->get_var("SELECT name FROM " . ALBUM_TABLE . " WHERE id=$id");
    }
	if ($name) {
		$name = stripslashes($name);
	}
	else {
		$name = '';
	}
	return $name;
}

// get album id by title
function wppa_album_id($name = '') {
	echo(wppa_get_album_id($name));
}
function wppa_get_album_id($name = '') {
	global $wpdb;
    
	if ($name == '') return '';
    $name = $wpdb->escape($name);
    $id = $wpdb->get_var("SELECT id FROM " . ALBUM_TABLE . " WHERE name='" . $name . "'");
    if ($id) {
		return $id;
	}
	else {
		return '';
	}
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
	global $is_slide;
	global $single_photo;

	$occur = '0';
	if (isset($_GET['occur'])) if (is_numeric($_GET['occur'])) $occur = $_GET['occur'];

	if ($is_slide == '1') $cur_page = 'slide';			// Do slide or single only when explixitly on
	elseif (is_numeric($single_photo)) $cur_page = 'oneofone';
	elseif ($occur == $wppa_occur) {					// Interprete $_GET only if occur is current
		if (isset($_GET['slide'])) $cur_page = 'slide';	
		elseif (isset($_GET['photo'])) $cur_page = 'single';
		else $cur_page = 'albums';
	}
	else $cur_page = 'albums';	

	if ($cur_page == $page) return TRUE; else return FALSE;
}

// get id of coverphoto
// this is intented to be a monkey-proof way to find the coverphoto id
// does all testing
function wppa_get_coverphoto_id($xalb = '') {
	global $wpdb, $album;
	
	if ($xalb == '') {				// default album
		if (isset($album['id'])) $alb = $album['id'];
	}
	else {							// supplied album
		$alb = $xalb;
	}
	if (is_numeric($alb)) {			// find main id
		$id = $wpdb->get_var("SELECT main_photo FROM " . ALBUM_TABLE . " WHERE id = " . $alb);
	}
	else return false;					// no album, no coverphoto
	if (is_numeric($id) && $id > '0') {		// check if id belongs to album
		$ph_alb = $wpdb->get_var("SELECT album FROM " . PHOTO_TABLE . " WHERE id = " . $id);
		if ($ph_alb != $alb) {		// main photo does no longer belong to album. Treat as random
			$id = '0';
		}
	}
	if (!is_numeric($id) || $id == '0') {	// random
		$id = $wpdb->get_var("SELECT id FROM " . PHOTO_TABLE . " WHERE album = " . $alb . " ORDER BY RAND() LIMIT 1");
	}
	return $id;	
}

// get thumb url
function wppa_get_thumb_url_by_id($id = false) {
	global $wpdb;
	if ($id == false) return '';	// no id: no url
	$ext = $wpdb->get_var("SELECT ext FROM " . PHOTO_TABLE . " WHERE id = " . $id);
	if ($ext) {
		$url = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/thumbs/' . $id . '.' . $ext;
	}
	else {
		$url = '';
	}
	return $url;
}

// get thumb path
function wppa_get_thumb_path_by_id($id = false) {
	global $wpdb;
	if ($id == false) return '';	// no id: no path
	$ext = $wpdb->get_var("SELECT ext FROM " . PHOTO_TABLE . " WHERE id = " . $id);
	if ($ext) {
		$path =  ABSPATH . 'wp-content/uploads/wppa/thumbs/' . $id . '.' . $ext;
	}
	else {
		$path = '';
	}
	return $path;
}

// get image url
function wppa_get_image_url_by_id($id = false) {
	global $wpdb;
	if ($id == false) return '';	// no id: no url
	$ext = $wpdb->get_var("SELECT ext FROM " . PHOTO_TABLE . " WHERE id = " . $id);
	if ($ext) {
		$url = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/' . $id . '.' . $ext;
	}
	else {
		$url = '';
	}
	return $url;
}

// get image path
function wppa_get_image_path_by_id($id = false) {
	global $wpdb;
	if ($id == false) return '';	// no id: no path
	$ext = $wpdb->get_var("SELECT ext FROM " . PHOTO_TABLE . " WHERE id = " . $id);
	if ($ext) {
		$path =  ABSPATH . 'wp-content/uploads/wppa/' . $id . '.' . $ext;
	}
	else {
		$path = '';
	}
	return $path;
}

// get page url of current album image
function wppa_get_image_page_url_by_id($id = false) {
	global $wpdb; //, $album;
	global $wppa_occur;
	
	if ($id == false) return '';
	$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE id={$id} LIMIT 1", 'ARRAY_A');
//	if ($image) $imgurl = get_permalink()  . wppa_sep() . 'album=' . $album['id'] . '&photo=' . $image['id'] . '&cover=0';	
	if ($image) $imgurl = get_permalink()  . wppa_sep() . 'album=' . $image['album'] . '&photo=' . $image['id'] . '&cover=0&occur=' . $wppa_occur;	
	else $imgurl = '';
	return $imgurl;
}

// loop album
function wppa_get_albums($album = false, $type = '') {
	global $wpdb;
    global $startalbum;
	global $is_cover;
	global $wppa_occur;
	global $wppa_src;

	$src = '';
	if (isset($_POST['wppa-searchstring'])) {
		$src = $_POST['wppa-searchstring'];
	}
	
	if (strlen($src)) {
		$skipsep = (get_option('wppa_excl_sep', 'no') == 'yes');
		$albs = $wpdb->get_results('SELECT * FROM ' . ALBUM_TABLE . ' ' . wppa_get_album_order(), 'ARRAY_A');
		$albums = '';
		$idx = '0';
		foreach ($albs as $album) if (!$skipsep || $album['a_parent'] != '-1') {
			if (wppa_deep_stristr($album['name'].' '.$album['description'], $src)) {
				$albums[$idx] = $album;
				$idx++;
			}
		}
	}
	else {
		if ($wppa_src) return false;	// empty search string
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
	}
	return $albums;
}

// get link to album by id or in loop
function wppa_album_url($xid = '') {
	echo(wppa_get_album_url($xid));
}
function wppa_get_album_url($xid = '') {
	global $album;
	global $wppa_occur;
	if ($xid != '') $id = $xid;
	elseif (isset($album['id'])) {
		$id = $album['id'];
	}
	if ($id != '') {
		$link = get_permalink() . wppa_sep() . 'album=' . $id . '&cover=0&occur='.$wppa_occur;
	}
	else $link = '';
    return $link;
}

// get number of photos in album 
function wppa_get_photo_count($xid = '') {
    global $wpdb;
    global $album;
    
    if (is_numeric($xid)) $id = $xid; else $id = $album['id'];
    $count = $wpdb->query("SELECT * FROM " . PHOTO_TABLE . " WHERE album=".$id);
	return $count;
}

// get number of albums in album 
function wppa_get_album_count($xid = '') {
    global $wpdb;
    global $album;
    
    if (is_numeric($xid)) $id = $xid; else $id = $album['id'];
    $count = $wpdb->query("SELECT * FROM " . ALBUM_TABLE . " WHERE a_parent=".$id);
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
function wppa_the_album_name() {
	echo(wppa_get_the_album_name());
}
function wppa_get_the_album_name() {
	global $album;
	
	return stripslashes($album['name']);
}

// get album decription
function wppa_the_album_desc() {
	echo(wppa_get_the_album_desc());
}
function wppa_get_the_album_desc() {
	global $album;
	
	return stripslashes($album['description']);
}

// get link to slideshow (in loop)
function wppa_slideshow_url() {
	echo(wppa_get_slideshow_url());
}
function wppa_get_slideshow_url() {
	global $album;
	global $wppa_occur;
	
	$link = get_permalink() . wppa_sep() . 'album=' . $album['id'] . '&amp;' . 'slide=true' . '&amp;occur=' . $wppa_occur;
	
	return $link;	
}

// loop thumbs
function wppa_get_thumbs() {
	global $wpdb;
    global $startalbum;
	global $wppa_occur;
	global $wppa_src;

	$src = '';
	if (isset($_POST['wppa-searchstring'])) {
		$src = $_POST['wppa-searchstring'];
	}
	elseif (isset($_GET['wppa_src'])) {
		$src = $_GET['wppa_src'];
	}
	
	if (strlen($src)) {
		$skipsep = (get_option('wppa_excl_sep', 'no') == 'yes');
		$tmbs = $wpdb->get_results('SELECT * FROM ' . PHOTO_TABLE . ' ' . wppa_get_photo_order('0'), 'ARRAY_A');
		$thumbs = '';
		$idx = '0';
		foreach ($tmbs as $thumb) {
			if (wppa_deep_stristr($thumb['name'].' '.$thumb['description'], $src)) {
				if (!$skipsep || (wppa_get_parentalbumid($thumb['album']) != '-1')) {
					$thumbs[$idx] = $thumb;
					$idx++;
				}
			}
		}
	}
	else {
		if ($wppa_src) return false; 	// empty search string
		$occur = '0';
		if (isset($_GET['occur'])) if (is_numeric($_GET['occur'])) $occur = $_GET['occur'];
		
		// Obey querystring only if the global occurence matches the occurence in the querystring, or no query occurrence given.
		if (($occur == $wppa_occur) && (isset($_GET['album']))) {
			$id = $_GET['album'];
		}
		elseif (is_numeric($startalbum)) $id = $startalbum; 
		else $id = 0;
		if (is_numeric($id)) {
			$thumbs = $wpdb->get_results("SELECT * FROM " . PHOTO_TABLE . " WHERE album=$id " . wppa_get_photo_order($id), 'ARRAY_A'); 
		}
		else {
			$thumbs = false;
		}
	}
	return $thumbs;
}

// get link to photo
function wppa_photo_page_url() {
	echo(wppa_get_photo_page_url());
}
function wppa_get_photo_page_url() {
	global $thumb;
	global $wppa_occur;
	
    if (isset($_GET['album'])) {
		$url = get_permalink()  . wppa_sep() . 'album=' . $_GET['album'] . '&amp;photo=' . $thumb['id'];
	}
	else {
		$url = get_permalink()  . wppa_sep() . 'photo=' . $thumb['id'];
		if (isset($_POST['wppa-searchstring'])) {
			$url .= '&wppa_src=' . $_POST['wppa-searchstring'];
		}
	}
	$url .= '&amp;occur=' . $wppa_occur;
	return $url; 
}

// get url of thumb
function wppa_thumb_url() {
	echo(wppa_get_thumb_url());
}
function wppa_get_thumb_url() {
	global $thumb;

	$url = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/thumbs/' . $thumb['id'] . '.' . $thumb['ext'];
	return $url; 
}

// get path of thumb
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
function wppa_get_photo_name($id = '') {
	return wppa_photo_name($id, TRUE);
}
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
/*
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
*/

// get full img style
function wppa_fullimgstyle($id = '') {
	echo wppa_get_fullimgstyle($id);
}
function wppa_get_fullimgstyle($id = '') {
	global $wpdb;
    global $wppa_fullsize;
	global $wppa_no_enlarge;
	
	if (!is_numeric($wppa_fullsize) || $wppa_fullsize == '0') $wppa_fullsize = get_option('wppa_fullsize', '640');

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
	global $wppa_master_occur;
    $result = "'" . $wppa_master_occur . "','" . $index . "','" . wppa_get_photo_url($id) . "','" . wppa_get_fullimgstyle($id) . "','" . esc_js(wppa_photo_name($id, TRUE)) . "','" . wppa_html(esc_js(wppa_photo_desc($id,true))) . "'";
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

function wppa_get_imgstyle($file, $max_size, $xvalign = '', $type = '') {	
global $wppa_auto_colwidth;
	if($file == '') return '';					// no image: no dimensions
	if (!is_file($file)) return '';				// no file: no dimensions (2.3.0)
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
	// compose the size result
	if ($valign == 'fit' && (!$wppa_auto_colwidth || $type != 'fullsize')) {
//	if ($valign == 'fit') {
		$result = ' height: ' . $height . 'px; width: ' . $width . 'px; ';
	}
	else {
		if ($image_attr[0] > $image_attr[1]) $result = ' width: ' . $width . 'px; ';
		else $result = ' height: ' . $height . 'px; ';
	}
	// compose the top margin
	if ($valign == 'top' || $valign == 'fit' || wppa_page('oneofone') || ($wppa_auto_colwidth && $type == 'fullsize')) {
		$result .= ' margin-top: 0px; ';
	}
	elseif ($valign == 'center') {
//		$delta = floor(($width - $height) / 2);
		$delta = floor(($max_size - $height) / 2);
		if ($delta < '0') $delta = '0';
		$result .= ' margin-top: ' . $delta . 'px; ';
	}
	elseif ($valign == 'bottom') {
//		$delta = $width - $height;
		$delta = $max_size - $height;
		if ($delta < '0') $delta = '0';
		$result .= ' margin-top: ' . $delta . 'px; ';
	}
	// see if horizontal center needed
	if ($type != 'cover') {
		if ($valign != 'default') {
			$delta = floor(($max_size - $width) / 2);
//			$delta = floor(($height - $width) / 2);
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

function wppa_get_imgevents($type = '', $id = '', $no_popup = false) {
global $wppa_occur;
global $wppa_master_occur;
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
			if (!$no_popup && get_option('wppa_use_thumb_popup', 'no') == 'yes') {
				$result .= 'wppa_popup(' . $wppa_master_occur . ', this, ' . $id . ');';
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
	$result = get_bloginfo('wpurl') . '/wp-content/plugins/' . WPPA_PLUGIN_PATH . '/images/';
	return $result;
}

function wppa_onpage($type = '', $counter, $curpage) {
	$pagesize = wppa_get_pagesize($type);
	if ($pagesize == '0') {			// Pagination off
		if ($curpage == '1') return true;	
		else return false;
	}
	$cnt = $counter - 1;
	$crp = $curpage - 1;
	if (floor($cnt / $pagesize) == $crp) return true;
	return false;
}

function wppa_page_links($npages = '1', $curpage = '1') {
	global $is_cover;
	global $wppa_occur;
	global $wppa_master_occur;
	
	if ($npages < '2') return;	// Nothing to display
	
	// Compose the Previous and Next Page urls
	if (isset($_GET['cover'])) $ic = $_GET['cover'];
	else {
		if ($is_cover == '1') $ic = '1'; else $ic = '0';
	}
	$pnu = get_permalink() . wppa_sep() . 'cover=' . $ic;
	if (isset($_GET['album'])) $pnu .= '&album=' . $_GET['album'];
	if (isset($_GET['photo'])) $pnu .= '&photo=' . $_GET['photo'];
	$pnu .= '&occur=' . $wppa_occur;
	$prevurl = $pnu . '&page=' . ($curpage - 1);	
	$nexturl = $pnu . '&page=' . ($curpage + 1);
	
	$from = 1;
	$to = $npages;
	if ($npages > '7') {
		$from = $curpage - '3';
		$to = $curpage + 3;
		while ($from < '1') {
			$from++;
			$to++;
		}
		while ($to > $npages) {
			$from--;
			$to--;
		}
	}

?>
	<div id="prevnext-a-<?php echo($wppa_master_occur) ?>" class="wppa-nav-text wppa-box wppa-nav" style="text-align:center" >
		<div id="prev-page" class="prev arrow" <?php if ($curpage == '1') echo('style="visibility: hidden;"'); ?>>
			<span style="cursor: default;">&laquo;&nbsp;</span>
			<a id="p-p" href="<?php echo($prevurl); ?>" ><?php _e('Previous page', 'wppa'); ?></a>
		</div>
		<div id="next-page" class="next arrow" <?php if ($curpage == $npages) echo('style="visibility: hidden;"'); ?>>
			<a id="n-p" href="<?php echo($nexturl); ?>" ><?php _e('Next page', 'wppa'); ?></a>
			<span style="cursor: default;">&nbsp;&raquo;</span>
		</div>
		
<?php
		if ($from > '1') echo('.&nbsp;.&nbsp;.&nbsp;');
		for ($i=$from; $i<=$to; $i++) {
			if ($curpage == $i) { ?>
				<div class="wppa-mini-box wppa-alt " style="display:inline; text-align:center; color:<?php echo(get_option('wppa_black', 'black')) ?>; text-decoration: none; cursor: default; font-weight:normal;" ><a style="font-weight:normal; text-decoration: none; cursor: default; color:<?php echo(get_option('wppa_black', 'black')) ?>;">&nbsp;<?php echo($i) ?>&nbsp;</a></div>
	<?php		}
			else { ?>
				<div class="wppa-mini-box wppa-even " style="display:inline; text-align:center;" ><a href="<?php echo($pnu . '&page=' . $i) ?>">&nbsp;<?php echo($i) ?>&nbsp;</a></div>
	<?php		}
		}
		if ($to < $npages) echo('&nbsp;.&nbsp;.&nbsp;.');
?>
	</div>
<?php
}
	
function wppa_set_runtimestyle() { 
global $wppa_master_occur;
global $wppa_auto_colwidth;

	if ($wppa_master_occur < '2') {	// Widget only: 0, else 1
		/* If you simplify this by making a cdata block, you will break rss feeds */
		/* rss attempts to create a nested cdata block that causes the loss of the script tag */
		/* The errormessage will say that the /script tag is not matched while it is. */
		/* This goes into wppa_theme.js */ ?>
		<script type="text/javascript">wppa_bgcolor_even = "<?php echo(get_option('wppa_bgcolor_even', '#eeeeee')); ?>";</script>
		<script type="text/javascript">wppa_bgcolor_alt = "<?php echo(get_option('wppa_bgcolor_alt', '#dddddd')); ?>";</script>
		<script type="text/javascript">wppa_bgcolor_nav = "<?php echo(get_option('wppa_bgcolor_nav', '#dddddd')); ?>";</script>
		<script type="text/javascript">wppa_bgcolor_img = "<?php echo(get_option('wppa_bgcolor_img', '#eeeeee')); ?>";</script>
		<script type="text/javascript">wppa_bcolor_even = "<?php echo(get_option('wppa_bcolor_even', '#cccccc')); ?>";</script>
		<script type="text/javascript">wppa_bcolor_alt = "<?php echo(get_option('wppa_bcolor_alt', '#bbbbbb')); ?>";</script>
		<script type="text/javascript">wppa_bcolor_nav = "<?php echo(get_option('wppa_bcolor_nav', '#bbbbbb')); ?>";</script>
		<script type="text/javascript">wppa_bwidth = "<?php echo(get_option('wppa_bwidth', '2')); ?>";</script>
		<script type="text/javascript">wppa_bradius = "<?php echo(get_option('wppa_bradius', '6')); ?>";</script>
		<script type="text/javascript">wppa_fontfamily_title = "<?php echo(get_option('wppa_fontfamily_title', '')); ?>";</script>
		<script type="text/javascript">wppa_fontsize_title = "<?php echo(get_option('wppa_fontsize_title', '')); ?>";</script>
		<script type="text/javascript">wppa_fontfamily_fulldesc = "<?php echo(get_option('wppa_fontfamily_fulldesc', '')); ?>";</script>
		<script type="text/javascript">wppa_fontsize_fulldesc = "<?php echo(get_option('wppa_fontsize_fulldesc', '')); ?>";</script>
		<script type="text/javascript">wppa_fontfamily_fulltitle = "<?php echo(get_option('wppa_fontfamily_fulltitle', '')); ?>";</script>
		<script type="text/javascript">wppa_fontsize_fulltitle = "<?php echo(get_option('wppa_fontsize_fulltitle', '')); ?>";</script>
		<script type="text/javascript">wppa_fontfamily_nav = "<?php echo(get_option('wppa_fontfamily_nav', '')); ?>";</script>
		<script type="text/javascript">wppa_fontsize_nav = "<?php echo(get_option('wppa_fontsize_nav', '')); ?>";</script>
		<script type="text/javascript">wppa_fontfamily_box = "<?php echo(get_option('wppa_fontfamily_box', '')); ?>";</script>
		<script type="text/javascript">wppa_fontsize_box = "<?php echo(get_option('wppa_fontsize_box', '')); ?>";</script>
		<script type="text/javascript">wppa_black = "<?php echo(get_option('wppa_black', 'black')); ?>";</script>
		<script type="text/javascript"><?php if (get_option('wppa_fadein_after_fadeout', 'no') == 'yes') echo('wppa_fadein_after_fadeout = true;'); ?></script>
		<script type="text/javascript">wppa_widget_padding = <?php echo(get_option('wppa_widget_padding', '5')); ?>;</script>
<?php 	/* This goes into wppa_slideshow.js */ ?>
		<script type="text/javascript">wppa_thumbnail_area_delta = <?php echo wppa_get_thumbnail_area_delta(); ?>;</script>
		<script type="text/javascript">wppa_auto_colwidth = <?php if ($wppa_auto_colwidth) echo('true'); else echo('false'); ?>;</script>
		<script type="text/javascript">wppa_slideshow = "<?php _e('Slideshow', 'wppa'); ?>";</script>
		<script type="text/javascript">wppa_photo = "<?php _e('Photo', 'wppa'); ?>";</script>
		<script type="text/javascript">wppa_of = "<?php _e('of', 'wppa'); ?>";</script>
		<script type="text/javascript">wppa_prevphoto = "<?php _e('Previous photo', 'wppa'); ?>";</script>
		<script type="text/javascript">wppa_nextphoto = "<?php _e('Next photo', 'wppa'); ?>";</script>
<?php	/* And some data */ ?>
		<script type="text/javascript">wppa_animation_speed = <?php echo get_option('wppa_animation_speed', 400) ?>;</script>
		<script type="text/javascript">wppa_imgdir = "<?php echo wppa_get_imgdir() ?>";</script>
		<script type="text/javascript">wppa_ss_timeout = <?php echo get_option('wppa_slideshow_timeout', 2500) ?>;</script>
		<script type="text/javascript">wppa_preambule = <?php echo wppa_get_preambule() ?>;</script>
		<script type="text/javascript">wppa_thumbnail_pitch = <?php echo (get_option('wppa_tf_width', '100') + get_option('wppa_tn_margin', '0')); ?>;</script>
		<script type="text/javascript">wppa_filmstrip_length = <?php echo (wppa_get_container_width() - 60); ?>;</script>
		<script type="text/javascript">wppa_filmstrip_margin = <?php echo (get_option('wppa_tn_margin', '0') / 2); ?>;</script>
<?php }
}

function wppa_get_pagesize($type = '') {
	if (isset($_POST['wppa-searchstring'])) return '0';
	if ($type == 'albums') return get_option('wppa_album_page_size', '0');
	if ($type == 'thumbs') return get_option('wppa_thumb_page_size', '0');
	return '0';
}

function wppa_deep_stristr($string, $tokens) {
global $wppa_stree;
	// Explode tokens into search tree
	if (!isset($wppa_stree)) {
		// sanitize search token string
		$tokens = trim($tokens);
		while (strstr($tokens, ', ')) $tokens = str_replace(', ', ',', $tokens);
		while (strstr($tokens, ' ,')) $tokens = str_replace(' ,', ',', $tokens);
		while (strstr($tokens, '  ')) $tokens = str_replace('  ', ' ', $tokens);
		while (strstr($tokens, ',,')) $tokens = str_replace(',,', ',', $tokens);
		// to level explode
		if (strstr($tokens, ',')) {
			$wppa_stree = explode(',', $tokens);
		}
		else {
			$wppa_stree[0] = $tokens;
		}
		// botoom level explode
		for ($idx = 0; $idx < count($wppa_stree); $idx++) {
			if (strstr($wppa_stree[$idx], ' ')) {
				$wppa_stree[$idx] = explode(' ', $wppa_stree[$idx]);
			}
		}
	}
	// Check the search criteria
	foreach ($wppa_stree as $branch) {
		if (is_array($branch)) {
			if (wppa_and_stristr($string, $branch)) return true;
		}
		else {
			if (stristr($string, $branch)) return true;
		}
	}
	return false;
}

function wppa_and_stristr($string, $branch) {
	foreach ($branch as $leaf) {
		if (!stristr($string, $leaf)) return false;
	}
	return true;
}

function wppa_get_slide_frame_style() {
	global $wppa_fullsize;
	global $single_photo;
	global $wppa_auto_colwidth;
	$fs = get_option('wppa_fullsize');
	$cs = get_option('wppa_colwidth', $fs);
	if ($cs == 'auto') {
		$cs = $fs;
		$wppa_auto_colwidth = true;
	}
	$result = '';
	$gfs = (is_numeric($wppa_fullsize) && $wppa_fullsize > '0') ? $wppa_fullsize : $fs;

	if (wppa_page('oneofone')) {
		$imgattr = getimagesize(wppa_get_image_path_by_id($single_photo));
		$h = floor($gfs * $imgattr[1] / $imgattr[0]);
		$result .= 'height: ' . $h . 'px;';
	}
	elseif ($wppa_auto_colwidth) {
		$result .= ' height: ' . $gfs * 3/4 . 'px;';
	}
	elseif (get_option('wppa_fullvalign', 'default') == 'default') {
		$result .= 'min-height: ' . $gfs * 3/4 . 'px;'; 
	}
	else {
		$result .= 'height: ' . $gfs . 'px;'; 
	}

	$result .= 'width: ' . $gfs . 'px;';
	
	$hor = get_option('wppa_fullhalign', 'center');
	if ($gfs == $fs) {
		if ($fs != $cs) {
			switch ($hor) {
			case 'left':
				$result .= 'margun-left: 0px;';
				break;
			case 'center':
//				if ($wppa_auto_colwidth) {
//					$result .= 'margin-left: auto; margin-right: auto;';
//				}
//				else {
					$result .= 'margin-left: ' . floor(($cs - $fs) / 2) . 'px;';
//				}
				break;
			case 'right':
				$result .= 'margin-left: ' . ($cs - $fs) . 'px;';
				break;
			}
		}
	}

	return $result;
}

function wppa_get_thumb_frame_style() {
	$tfw = get_option('wppa_tf_width');
	$tfh = get_option('wppa_tf_height');
	$mgl = get_option('wppa_tn_margin');
	$mgl2 = floor($mgl / '2');
	if (is_numeric($tfw) && is_numeric($tfh)) {
		return 'width: '.$tfw.'px; height: '.$tfh.'px; margin-left: '.$mgl.'px; margin-top: '.$mgl2.'px; margin-bottom: '.$mgl2.'px;';
	}
	else return '';
}

function wppa_get_container_width() {
global $wppa_fullsize;
global $wppa_auto_colwidth;
	if (is_numeric($wppa_fullsize) && $wppa_fullsize > '0') {
		$result = $wppa_fullsize;
	}
	else {
		$result = get_option('wppa_colwidth', '640');
		if ($result == 'auto') {
			$result = '640';
			$wppa_auto_colwidth = true;
		}
	}
	return $result;
}

function wppa_thumbnail_area_width() {
	echo (wppa_get_thumbnail_area_width());
}
function wppa_get_thumbnail_area_width() {
	$result = wppa_get_container_width();
	$result -= wppa_get_thumbnail_area_delta();
	return $result;
}

function wppa_get_thumbnail_area_delta() {
	$result = 7 + 2 * get_option('wppa_bwidth', '1');	// 7 = .thumbnail_area padding-left
	return $result;
}

function wppa_container_style() {
	echo(wppa_get_container_style());
}
function wppa_get_container_style() {
global $wppa_align;
global $wppa_fullsize;
global $wppa_auto_colwidth;
	// See if there is space for a margin
	$marg = false;
	if (is_numeric($wppa_fullsize)) {
		$cw = get_option('wppa_colwidth', '640');
		if (is_numeric($cw)) {
			if ($cw > ($wppa_fullsize + 10)) {
				$marg = '10px;';
			}
		}
	}
	
	$result = 'clear: both; ';
	$ctw = wppa_get_container_width();
	if ($wppa_auto_colwidth) {
	}
	else {
		$result .= 'width:'.$ctw.'px;';
	}
	
	if ($wppa_align == '' || $wppa_align == 'left') {
		$result .= 'float: left;';
		if ($marg) $result .= 'margin-right: '.$marg;
	}
	elseif ($wppa_align == 'center') $result .= 'display: block; margin-left: auto; margin-right: auto;'; 
	elseif ($wppa_align == 'right') {
		$result .= 'float: right;';
		if ($marg) $result .= 'margin-left: '.$marg;
	}
	
	return $result;
}

function wppa_get_curpage() {
global $wppa_master_occur;
	if (isset($_GET['occur'])) $oc = $_GET['occur']; else $oc = '1';
	if (isset($_GET['page']) && $wppa_master_occur == $oc) $curpage = $_GET['page']; else $curpage = '1';
	return $curpage;
}

function wppa_container($action) {
global $wppa_api_version;
global $wppa_master_occur;
global $wppa_version;
global $wppa_inp;
global $wppa_alt;
	if ($action == 'open') {
		$wppa_alt = 'alt';
		if ($wppa_inp) echo('</p>');						// Close wpautop generated paragraph if we're in
		echo('<div id="wppa-container-'.$wppa_master_occur.'" style="'.wppa_get_container_style().'" class="wppa-container wppa-'.$wppa_version.' wppa-api-'.$wppa_api_version.'">');
	}
	elseif ($action == 'close')	{
		echo('</div><!-- wppa-container-'.$wppa_master_occur.' -->');
	}
	else {
		echo('<span style="color:red;">Error, wppa_container() called with wrong argument: '.$action.'. Possible values: \'open\' or \'close\'</span>');
	}
}

function wppa_album_list($action) {
global $wppa_master_occur;
	if ($action == 'open') {
		echo('<div id="wppa-albumlist-'.$wppa_master_occur.'" class="albumlist">');
	}
	elseif ($action == 'close') {
		echo('</div><!-- wppa-albumlist-'.$wppa_master_occur.' -->');
	}
	else {
		echo('<span style="color:red;">Error, wppa_albumlist() called with wrong argument: '.$action.'. Possible values: \'open\' or \'close\'</span>');
	}
}

function wppa_thumb_list($action) {
global $wppa_master_occur;
	if ($action == 'open') {
		echo('<div id="wppa-thumblist-'.$wppa_master_occur.'" class="thumblist">');
	}
	elseif ($action == 'close') {
		echo('</div><!-- wppa-thumblist-'.$wppa_master_occur.' -->');
	}
	else {
		echo('<span style="color:red;">Error, wppa_thumblist() called with wrong argument: '.$action.'. Possible values: \'open\' or \'close\'</span>');
	}
}

function wppa_thumb_area($action) {
global $wppa_master_occur;
global $wppa_alt;
	if ($action == 'open') {
		echo('<div id="wppa-thumbarea-'.$wppa_master_occur.'" style="width: '.wppa_get_thumbnail_area_width().'px;" class="thumbnail-area wppa-box wppa-'.$wppa_alt.'" onclick="wppa_popdown('.$wppa_master_occur.')" >');
		if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
	}
	elseif ($action == 'close') {
		echo('</div><!-- wppa-thumbarea-'.$wppa_master_occur.' -->');
	}
	else {
		echo('<span style="color:red;">Error, wppa_thumbarea() called with wrong argument: '.$action.'. Possible values: \'open\' or \'close\'</span>');
	}
}

function wppa_get_npages($type, $array) {
global $is_cover;
	$aps = wppa_get_pagesize('albums');	
	$tps = wppa_get_pagesize('thumbs'); 
	$result = '0';
	if ($type == 'albums') {
		if ($aps != '0') {
			$result = ceil(count($array) / $aps); 
		} 
		elseif ($tps != '0') {
			$result = '1'; 
		}
	}
	elseif ($type == 'thumbs') {
		if ($is_cover == '1') {
			$result = '0';
		} 
		elseif ($tps != '0') {
			$result = ceil(count($array) / $tps);
		}
		else {
			$result = '1';
		}
	}
	return $result;
}

function wppa_album_cover() {
global $album;
global $wppa_master_occur;
global $wppa_alt;

	$coverphoto = wppa_get_coverphoto_id();
	$photocount = wppa_get_photo_count();
	$albumcount = wppa_get_album_count();
	$mincount = wppa_get_mincount();
	if (is_numeric($album['cover_linkpage']) && $album['cover_linkpage'] > 0) {
		$page_data = get_page($album['cover_linkpage']);
		if (!empty($page_data) && $page_data->post_status == 'publish') {
			$href = get_page_link($album['cover_linkpage']);
			$title = __('Link to', 'wppa');
			$title .= ' ' . $page_data->post_title;
		} else {
			$href = '#';
			$title = __('Page is not available.');
		}
	} elseif (is_numeric($album['cover_linkpage']) && $album['cover_linkpage'] == -1) {
		$href = '';
		$title = '';
	} else {
		if ($photocount != '0' && $photocount) {
			$href = wppa_get_image_page_url_by_id($coverphoto); 
			$title = __('View the cover photo', 'wppa'); 
			if ($photocount > 1) $title .= __('s', 'wppa');
		} else {
			$href = wppa_get_album_url(); 
			$title = __('View the album', 'wppa') . ' ' . $album['name'];
		}
	}
	$src = wppa_get_thumb_url_by_id($coverphoto);	
	$path = wppa_get_thumb_path_by_id($coverphoto);
	$imgattr = wppa_get_imgstyle($path, get_option('wppa_smallsize'), '', 'cover');
	$events = wppa_get_imgevents('cover');
	$photo_left = get_option('wppa_coverphoto_left', 'no') == 'yes';
	
	?>
	<div id="album-<?php echo($album['id'].'-'.$wppa_master_occur) ?>" class="album wppa-box wppa-<?php echo($wppa_alt); ?>"><?php 
		if ($src != '') { 
			if ($photo_left) {
				$photoframestyle = 'style="float:left;"';
			}
			else {
				$photoframestyle = '';
			}
			?>
			<div id="coverphoto_frame_<?php echo($album['id'].'_'.$wppa_master_occur) ?>" class="coverphoto-frame" <?php echo($photoframestyle) ?>><?php 
			if ($href != '') {
				?>
				<a href="<?php echo($href); ?>" title="<?php echo($title); ?>">
					<img src="<?php echo($src); ?>" alt="<?php echo($title); ?>" class="image wppa-img" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/>
				</a><?php 
			} else { 
				?>
				<img src="<?php echo($src); ?>" alt="<?php echo($title); ?>" class="image wppa-img" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/><?php 
			} 
			?>
			</div><!-- #coverphoto_frame_ <?php echo($album['id'].$wppa_master_occur) ?> --><?php 
		} 
		
		if ($photo_left) {
			$textframestyle = 'style="margin-left:'.(get_option('wppa_smallsize', '100')+17).'px;"';
		}
		else {
			$textframestyle = '';
		}
		?>
<div id="covertext_frame_<?php echo($album['id'].'_'.$wppa_master_occur) ?>" class="covertext-frame" <?php echo($textframestyle) ?>>
		<h2 class="wppa-title name" style="clear:none;"><?php 
			if ($href != '') { 
				?>
				<a href="<?php echo($href); ?>" title="<?php echo($title); ?>"><?php echo(stripslashes($album['name'])); ?></a><?php
			} else { 
				echo(stripslashes($album['name'])); 
			} 
		?>
		</h2>
		<p class="wppa-box-text wppa-black description"><?php echo(wppa_html(wppa_get_the_album_desc())); ?></p>
		<div class="wppa-box-text wppa-black info"><?php 
			if ($photocount > $mincount && get_option('wppa_hide_slideshow', 'no') == 'no') { 
				?>
				<a href="<?php wppa_slideshow_url(); ?>" title="<?php _e('Slideshow', 'wppa'); ?>" ><?php _e('Slideshow', 'wppa'); ?></a><?php 
			} else echo('&nbsp;'); 
		?>
		</div>
		<div class="wppa-box-text wppa-black info"><?php 
			if ($photocount > $mincount || $albumcount) { 
				?>
				<a href="<?php wppa_album_url(); ?>" title="<?php _e('View the album', 'wppa'); echo(' ' . $album['name']); ?>" ><?php 
				_e('View', 'wppa'); 
				if ($albumcount) { 
					echo(' ' . $albumcount . ' '); _e('albums', 'wppa'); 
				}
				if ($photocount > $mincount && $albumcount) {
					_e('and', 'wppa'); 
				}
				if ($photocount > $mincount) { 
					echo(' ' . $photocount . ' '); 
					_e('photos', 'wppa'); 
				} 
				?>
				</a><?php 
			} 
		?>
		</div>
</div>
		<div class="clear"></div>		
	</div><!-- #album-<?php echo($album['id'].'-'.$wppa_master_occur) ?> --><?php
	if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
}

function wppa_thumb_ascover() {
global $thumb;
global $wppa_master_occur;
global $wppa_alt;
	$src = wppa_get_thumb_path(); 
	$imgattr = wppa_get_imgstyle($src, get_option('wppa_smallsize'), '', 'cover'); 
	$src = wppa_get_thumb_url(); 
	$events = wppa_get_imgevents('cover'); 
	$title = esc_js(wppa_get_photo_name($thumb['id'])); 
	$href = wppa_get_photo_page_url();
	$photo_left = get_option('wppa_thumbphoto_left', 'no') == 'yes';
	
	?>
	<div id="thumb-<?php echo($thumb['id'].'-'.$wppa_master_occur) ?>" class="thumb wppa-box wppa-<?php echo($wppa_alt); ?>"><?php 
		if ($src != '') { 
			if ($photo_left) {
				$photoframestyle = 'style="float:left;"';
			}
			else {
				$photoframestyle = '';
			}
		?>
		<div id="thumbphoto_frame_<?php echo($thumb['id'].'_'.$wppa_master_occur) ?>" class="thumbphoto-frame" <?php echo($photoframestyle) ?>>
			<a href="<?php echo($href); ?>" title="<?php echo($title); ?>">
				<img src="<?php echo($src); ?>" alt="<?php echo($title); ?>" class="image wppa-img" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/>
			</a>
		</div><?php 
		} 
		if ($photo_left) {
			$textframestyle = 'style="margin-left:'.(get_option('wppa_smallsize', '100')+17).'px;"';
		}
		else {
			$textframestyle = '';
		}
		?>
<div id="thumbtext_frame_<?php echo($thumb['id'].'_'.$wppa_master_occur) ?>" class="thumbtext-frame" <?php echo($textframestyle) ?>>
		<h2 class="wppa-title name" style="clear:none;">
			<a href="<?php echo($href); ?>" title="<?php echo($title); ?>"><?php echo(stripslashes($thumb['name'])); ?></a>
		</h2>
		<p class="description"><?php echo(wppa_html(stripslashes($thumb['description']))); ?></p>
</div>
		<div class="clear"></div>		
	</div><!-- thumb-<?php echo($thumb['id'].'-'.$wppa_master_occur) ?> --><?php
	if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
}

function wppa_thumb_default() {
global $thumb;
global $wppa_master_occur;
global $wppa_src;
	$src = wppa_get_thumb_path(); 
	$imgattr = wppa_get_imgstyle($src, get_option('wppa_thumbsize'), 'optional', 'thumb'); 
	$src = wppa_get_thumb_url(); 
	$events = wppa_get_imgevents('thumb', $thumb['id']); 
	if (get_option('wppa_use_thumb_popup') == 'yes') $title = esc_attr(stripslashes($thumb['description']));
	else $title = esc_js(wppa_get_photo_name($thumb['id'])); 
	
	?>
	<div id="thumbnail_frame_<?php echo($thumb['id'].'_'.$wppa_master_occur) ?>" class="thumbnail-frame" style="<?php echo(wppa_get_thumb_frame_style()); ?>" >
		<a href="<?php wppa_photo_page_url(); ?>" class="thumb-img" id="a-<?php echo($thumb['id'].'-'.$wppa_master_occur) ?>"><img src="<?php echo($src); ?>" alt="<?php echo(esc_attr($thumb['name'])); ?>" title="<?php echo(esc_attr($title)); ?>" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/></a>
		<?php if ($wppa_src) { ?>
			<div class="thumb-text" ><?php echo('(<a href="'.wppa_get_album_url($thumb['album']).'">'.stripslashes(wppa_get_album_name($thumb['album'])).'</a>)'); ?></div>
	<?php } ?>
	<?php if (get_option('wppa_thumb_text', 'no') == 'yes') { ?>
		<div class="thumb-text" ><?php echo(stripslashes($thumb['name'])); ?></div>
		<div class="thumb-text" ><?php echo(stripslashes($thumb['description'])); ?></div>
	<?php } ?>
	</div><!-- #thumbnail_frame_<?php echo($thumb['id'].'_'.$wppa_master_occur) ?> --><?php
	
}	

function wppa_get_mincount() {
global $wppa_src;
	$result = $wppa_src ? '0' : get_option('wppa_min_thumbs', '1');	// Showing thumbs as searchresult has no minimum
	return $result;
}

function wppa_slide_frame() {
global $wppa_master_occur;
	?>
	<div id="slide_frame-<?php echo($wppa_master_occur) ?>" class="slide-frame" style="<?php echo(wppa_get_slide_frame_style()); ?>">
		<div id="theslide0-<?php echo($wppa_master_occur) ?>" class="theslide"></div>
		<div id="theslide1-<?php echo($wppa_master_occur) ?>" class="theslide"></div>
		<div id="spinner-<?php echo($wppa_master_occur) ?>" class="spinner"></div>
	</div>
	<?php
}

function wppa_startstop($opt = '') {
global $wppa_master_occur;
	if (($opt != 'optional') || (get_option('wppa_show_startstop_navigation', 'yes') == 'yes')) {
		?>
		<div id="prevnext1-<?php echo($wppa_master_occur) ?>" class="wppa-box wppa-nav wppa-nav-text" <?php if (get_option('wppa_hide_slideshow', 'no') == 'yes') echo('style="display:none;"'); ?>>
			<p style="text-align: center; margin:0">
				<a id="speed0-<?php echo($wppa_master_occur) ?>" class="speed0" onclick="wppa_speed(<?php echo($wppa_master_occur); ?>, false)"><?php _e('Slower', 'wppa'); ?></a> |
				<a id="startstop-<?php echo($wppa_master_occur) ?>" class="startstop" onclick="wppa_startstop(<?php echo($wppa_master_occur) ?>, -1)"><?php _e('Start', 'wppa'); ?></a> |
				<a id="speed1-<?php echo($wppa_master_occur) ?>" class="speed1" onclick="wppa_speed(<?php echo($wppa_master_occur); ?>, true)"><?php _e('Faster', 'wppa'); ?></a>
			</p>
		</div><!-- #prevnext1 --><?php 
	}
}

function wppa_browsebar($opt = '') {
global $wppa_master_occur;
	if (($opt != 'optional') || (get_option('wppa_show_browse_navigation', 'yes') == 'yes')) {
		?>
		<div id="prevnext2-<?php echo($wppa_master_occur) ?>" class="wppa-box wppa-nav wppa-nav-text">
			<p style="text-align: center; margin:0">
				<span id="p-a-<?php echo($wppa_master_occur) ?>" class="wppa-black prev">&laquo;&nbsp;</span><a id="prev-arrow-<?php echo($wppa_master_occur) ?>" class="wppa-nav-text prev arrow arrow-<?php echo($wppa_master_occur) ?>" onclick="wppa_prev(<?php echo($wppa_master_occur) ?>)"></a>
				<span id="n-a-<?php echo($wppa_master_occur) ?>" class="wppa-black next">&nbsp;&raquo;</span><a id="next-arrow-<?php echo($wppa_master_occur) ?>" class="wppa-nav-text next arrow arrow-<?php echo($wppa_master_occur) ?>" onclick="wppa_next(<?php echo($wppa_master_occur) ?>)"></a>
				<span id="counter-<?php echo($wppa_master_occur) ?>" class="wppa-black" style="text-align:center; "></span>		
			</p>
		</div><!-- #prevnext2 --><?php 
	}
}

function wppa_slide_description($opt = '') {
global $wppa_master_occur;
	if (($opt != 'optional') || (get_option('wppa_show_full_desc', 'yes') == 'yes')) {
		?><p id="imagedesc-<?php echo($wppa_master_occur) ?>" class="wppa-fulldesc imagedesc"></p><?php
	}
}

function wppa_slide_name($opt = '') {
global $wppa_master_occur;
	if (($opt != 'optional') || (get_option('wppa_show_full_name', 'yes') == 'yes')) {
		?><p id="imagetitle-<?php echo($wppa_master_occur) ?>" class="wppa-fulltitle imagetitle"></p><?php
	}
}	

function wppa_popup() {
global $wppa_master_occur;
	echo('<div id="wppa-popup-'.$wppa_master_occur.'" class="wppa-popup-frame" ></div>');
	echo('<div class="clear"></div>');
}

function wppa_run_slidecontainer($type = '') {
global $wppa_master_occur;
global $single_photo;
	if ($type == 'single') {
		echo('<script type="text/javascript">wppa_store_slideinfo('.wppa_get_slide_info(0, $single_photo).');</script>');
		echo('<script type="text/javascript">wppa_fullvalign_fit['.$wppa_master_occur.'] = true;</script>');
		echo('<script type="text/javascript">wppa_startstop('.$wppa_master_occur.', 0);</script>');
	}
	elseif ($type == 'slideshow') {
		$index = 0;
		$startindex = -1;

		if (isset($_GET['photo'])) $startid = $_GET['photo'];	// Still slideshow at photo id $startid
		else {
			if (get_option('wppa_start_slide', 'no') == 'yes' && get_option('wppa_hide_slideshow', 'no') == 'no') {
				$startid = -1;					// Start running
			}
			else $startid = -2;					// Start still at first photo
		}
		if (isset($_GET['album'])) $alb = $_GET['album'];
		else $alb = '';	// Album id is in $startalbum
		foreach (wppa_get_thumbs($alb) as $tt) : $id = $tt['id'];
			echo '<script type="text/javascript">wppa_store_slideinfo(' . wppa_get_slide_info($index, $id) . ');</script>';
			if ($startid == -2) $startid = $id;
			if ($startid == $id) $startindex = $index;
			$index++;
		endforeach;
	
		if (get_option('wppa_fullvalign', 'default') == 'fit') { 
			echo('<script type="text/javascript" >wppa_fullvalign_fit['.$wppa_master_occur.'] = true;</script>');
		}
		echo('<script type="text/javascript">wppa_startstop('.$wppa_master_occur.', '.$startindex.');</script>');
	}
	else {
		echo('<span style="color:red;">Error, wppa_run_slidecontainer() called with wrong argument: '.$type.'. Possible values: \'single\' or \'slideshow\'</span>');
	}
}

function wppa_is_pagination() {
global $wppa_src;
	if ((wppa_get_pagesize('albums') == '0'&& wppa_get_pagesize('thumbs') == '0') || $wppa_src) return false;
	else return true;
}

// Custom box			// Reserved for future use
function wppa_slide_custom($opt = '') {
}

// Show Filmstrip	
function wppa_slide_filmstrip($opt = '') {
global $wppa_master_occur;
global $thumb;
	if ($opt == 'optional' && get_option('wppa_filmstrip', 'no') == 'no') return;
/**/
	if (isset($_GET['album'])) $alb = $_GET['album'];
	else $alb = '';	// Album id is in $startalbum
	$thumbs = wppa_get_thumbs($alb);
	$preambule = wppa_get_preambule();
		
	$width = (get_option('wppa_tf_width') + get_option('wppa_tn_margin')) * (count($thumbs) + 2 * $preambule);
	$topmarg = get_option('wppa_thumbsize') / 2 - 12 + 7;

	echo('<div class="wppa-box wppa-nav" style="height:'.(get_option('wppa_thumbsize') + get_option('wppa_tn_margin')).'px;">');
		echo('<div class="prev arrow" style="margin-top: '.$topmarg.'px; width: 23px; font-size: 24px;"><a id="prev-film-arrow-'.$wppa_master_occur.'" onclick="wppa_prev('.$wppa_master_occur.')">&laquo;</a></div>');
		echo('<div class="next arrow" style="margin-top: '.$topmarg.'px; width: 23px; font-size: 24px;"><a id="next-film-arrow-'.$wppa_master_occur.'" onclick="wppa_next('.$wppa_master_occur.')">&raquo;</a></div>');
		echo('<div style="display: block; height: '.(get_option('wppa_thumbsize') + get_option('wppa_tn_margin')).'px; margin: 0 20px 0 20px; overflow:hidden;">');
			echo('<div id="wppa-filmstrip-'.$wppa_master_occur.'" style="height: '.get_option('wppa_thumbsize').'px; width:'.$width.'px; margin-left: -100px;">');
				$start = count($thumbs) - $preambule;
				$end = count($thumbs);
				$idx = $start;
				while ($idx < $end) {
					$thumb = $thumbs[$idx];
					wppa_do_filmthumb($idx);
					$idx++;
				}
				$idx = 0;
				foreach ($thumbs as $tt) : $thumb = $tt;
					wppa_do_filmthumb($idx);
					$idx++;
				endforeach;
				$start = '0';
				$end = $preambule;
				$idx = $start;
				while ($idx < $end) {
					$thumb = $thumbs[$idx];
					wppa_do_filmthumb($idx);
					$idx++;
				}
			echo('</div>');
		echo('</div>');
	echo('</div>');
/**/
}

function wppa_do_filmthumb($idx) {
global $wppa_master_occur;
global $thumb;
			$src = wppa_get_thumb_path(); 
			$imgattr = wppa_get_imgstyle($src, get_option('wppa_thumbsize'), 'optional', 'thumb'); 
			$src = wppa_get_thumb_url(); 
			$events = wppa_get_imgevents('thumb', $thumb['id'], 'nopopup'); 
			$events .= ' onclick="wppa_goto('.$wppa_master_occur.', '.$idx.')"';
?>
				<div id="thumbnail_frame_<?php echo($thumb['id'].'_'.$wppa_master_occur) ?>" class="thumbnail-frame" style="<?php echo(wppa_get_thumb_frame_style()); ?>" >
				<img src="<?php echo($src); ?>" alt="<?php echo(esc_attr($thumb['name'])); ?>" title="<?php echo(esc_attr($thumb['name'])); ?>" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/>
				</div><!-- #thumbnail_frame_<?php echo($thumb['id'].'_'.$wppa_master_occur) ?> -->
<?php

}

function wppa_get_preambule() {
	$result = is_numeric(get_option('wppa_colwidth', '640')) ? get_option('wppa_colwidth', '640') : get_option('wppa_fullsize', '640');
	$result = ceil(ceil($result / get_option('wppa_thumbsize')) / 2 );
	return $result;
}