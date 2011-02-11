<?php
/* wppa_functions.php
* Pachkage: wp-photo-album-plus
*
* Various funcions and API modules
* Version 2.5.1
*

*/

global $wppa_api_version;
$wppa_api_version = '2-5-1-000';

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
	
	$result = '<div class="wppa-box wppa-nav" style="text-align: center; '.__wcs('wppa-box').__wcs('wppa-nav').'">';
	$result .= __('There are', 'wppa').' '.$count.' '.__('photo albums. The last album added is', 'wppa').' ';
	$result .= '<a href="'.get_permalink().wppa_sep().'album='.$y_id.'&amp;cover=0&amp;occur=1">'.$y_name.'</a>';

	if ($p_id > '0') {
		$result .= __(', a subalbum of', 'wppa').' '; 
		$result .= '<a href="'.get_permalink().wppa_sep().'album='.$p_id.'&amp;cover=0&amp;occur=1">'.$p_name.'</a>';
	}
	
	$result .= '.</div>';
	
	return $result;
}

/* shows the breadcrumb navigation */
function wppa_breadcrumb($xsep = '', $opt = '') {
	global $wppa_occur;
	global $wppa_master_occur;
	global $wppa_local_occur;
	global $startalbum;
	global $single_photo;
	global $is_slideonly;
	global $wppa_in_widget;

	if ($opt == 'optional' && get_option('wppa_show_bread', 'yes') == 'no') return;	/* Nothing to do here */
	if (wppa_page('oneofone')) return; /* Never at a single image */
	if ($is_slideonly == '1') return;	/* Not when slideony */
	if ($wppa_in_widget) return; /* Not in a widget */
	if (is_feed()) {
//		wppa_dummy_bar(__('- - - Breadcrumb navigation bar - - -', 'wppa'));	
		return;
	}
	
	/* Do some preparations */
	if ($xsep != '' && $xsep != '&raquo;') {					// Caller supplied separator &raquo; for compatibility with thema rev < 2.4.4
		$sep = '&nbsp'.$xsep.'&nbsp;';
	}
	else {		
		$temp = get_option('wppa_bc_separator', 'raquo');
		switch ($temp) {
			case 'url':
				$size = get_option('wppa_fontsize_nav', '');
				if ($size != '') $style = 'height:'.$size.'px;';
				else $style = '';
				$sep = '&nbsp;<img src="'.get_option('wppa_bc_url', wppa_get_imgdir().'arrow.png').'" class="no-shadow" style="'.$style.'" />&nbsp;';
				break;
			case 'txt':
				$sep = '&nbsp;'.html_entity_decode(stripslashes(get_option('wppa_bc_txt', '-&gt;')), ENT_QUOTES).'&nbsp;';
				break;
			default:
				$sep = '&nbsp;&' . $temp . ';&nbsp;';
		}
	}

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
	<div id="wppa-bc-<?php echo $wppa_master_occur ?>" class="wppa-nav wppa-box wppa-nav-text" style="<?php _wcs('wppa-nav'); _wcs('wppa-box'); _wcs('wppa-nav-text'); ?>">
<?php
		if (get_option('wppa_show_home', 'yes') == 'yes') {
?>
			<a href="<?php echo(get_bloginfo('url')); ?>" class="wppa-nav-text" style="<?php _wcs('wppa-nav-text'); ?>" ><?php _e('Home', 'wppa'); ?></a>
			<span class="wppa-nav-text" style="<?php _wcs('wppa-nav-text'); ?>" ><?php echo($sep); ?></span>
<?php	
		}
		
		if (is_page()) wppa_page_breadcrumb($sep);	
	
		if ($alb == 0) {
			if (!$separate) {
?>
				<span class="wppa-nav-text wppa-black b1" style="<?php _wcs('wppa-nav-text'); _wcs('wppa-black'); ?>" ><?php the_title(); ?></span>
<?php
			}
		} else {	/* $alb != 0 */
			if (!$separate) {
?>
				<a href="<?php echo(get_permalink() . wppa_sep()); ?>occur=<?php echo($wppa_local_occur); ?>" class="wppa-nav-text b2" style="<?php _wcs('wppa-nav-text'); ?>" ><?php the_title(); ?></a>
				<span class="wppa-nav-text b3" style="<?php _wcs('wppa-nav-text'); ?>" ><?php echo($sep); ?></span>
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
?>
				<a href="<?php echo(get_permalink() . wppa_sep() . 'album=' . $alb . '&amp;cover=0&amp;occur=' . $wppa_local_occur); ?>" class="wppa-nav-text b4" style="<?php _wcs('wppa-nav-text'); ?>" ><?php wppa_album_name($alb); ?></a>
				<span class="b5" ><?php echo($sep); ?></span>
				<span id="bc-pname-<?php echo($wppa_local_occur); ?>" class="wppa-nav-text wppa-black b8" style="<?php _wcs('wppa-nav-text'); _wcs('wppa-black'); ?>" ><?php echo(wppa_get_photo_name($photo)); ?></span>
<?php
			} elseif ($this_occur && !wppa_page('albums')) {
?>
				<a href="<?php echo(get_permalink() . wppa_sep() . 'album=' . $alb . '&amp;cover=0&amp;occur=' . $wppa_local_occur); ?>" class="wppa-nav-text b6" style="<?php _wcs('wppa-nav-text'); ?>" ><?php wppa_album_name($alb); ?></a>
				<span class="b7" ><?php echo($sep); ?></span>
				<span id="bc-pname-<?php echo($wppa_local_occur); ?>" class="wppa-nav-text wppa-black b9" style="<?php _wcs('wppa-nav-text'); _wcs('wppa-black'); ?>" ><?php _e("Slideshow", "wppa"); ?></span>
<?php
			} else {	// NOT This occurance OR album
?>
				<span class="wppa-nav-text wppa-black b10" style="<?php _wcs('wppa-nav-text'); _wcs('wppa-black'); ?>" ><?php wppa_album_name($alb); ?></span>
<?php
			} 
		}
		if (isset($_POST['wppa-searchstring'])) {
?>
			<span class="wppa-nav-text b11" style="<?php _wcs('wppa-nav-text'); _wcs('wppa-black'); ?>" ><b>&nbsp;<?php _e('Searchstring:', 'wppa'); ?>&nbsp;<?php echo($_POST['wppa-searchstring']); ?></b></span>
<?php
		}
		elseif (isset($_GET['topten'])) {
?>
			<span class="wppa-nav-text b11" style="<?php _wcs('wppa-nav-text'); _wcs('wppa-black'); ?>" ><b>&nbsp;<?php _e('Top rated photos', 'wppa'); ?></b></span>
<?php
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
?>
    <a href="<?php echo(get_permalink() . wppa_sep()); ?>album=<?php echo($parent); ?>&amp;cover=0&amp;occur=<?php echo($wppa_local_occur); ?>" class="wppa-nav-text b20" style="<?php _wcs('wppa-nav-text'); ?>" ><?php wppa_album_name($parent); ?></a>
	<span class="wppa-nav-text" style="<?php _wcs('wppa-nav-text'); ?>"><?php echo($sep); ?></span>
<?php
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
	<a href="#" class="wppa-nav-text b30" style="<?php _wcs('wppa-nav-text'); ?>" ></a>
	<span class="wppa-nav-text b31" style="<?php _wcs('wppa-nav-text'); ?>" ><?php echo($title . $sep); ?></span>
<?php
	} else {
?>
	<a href="<?php echo(get_page_link($parent)); ?>" class="wppa-nav-text b32" style="<?php _wcs('wppa-nav-text'); ?>" ><?php echo($title); ?></a>
	<span class="wppa-nav-text b32" style="<?php _wcs('wppa-nav-text'); ?>" ><?php echo($sep); ?></span>
<?php
	}
}

// get the albums by inserting the theme template and do some parameter processing
function wppa_albums($xid = '', $typ='', $siz = '', $ali = '') {
	global $wppa_occur;
	global $wppa_master_occur;
	global $wppa_widget_occur;
	global $wppa_in_widget;
    global $startalbum;
	global $is_cover;
	global $is_slide;
	global $is_slideonly;
	global $wppa_fullsize;
	global $wppa_auto_colwidth;
	global $single_photo;
	global $wppa_align;
    
	$wppa_occur++;
	$wppa_master_occur++;
	if ($wppa_in_widget) $wppa_widget_occur++;
	
	if ($typ == 'album') {
		$is_cover = '0';
		$is_slide = '0';
		$is_slideonly = '0';
	}
	elseif ($typ == 'cover') {
		$is_cover = '1';
		$is_slide = '0';
		$is_slideonly = '0';
	}
	elseif ($typ == 'slide') {
		$is_cover = '0';
		$is_slide = '1';
		$is_slideonly = '0';
	}
	elseif ($typ == 'slideonly') {
		$is_cover = '0';
		$is_slide = '0';
		$is_slideonly = '1';
	}
	
	if ($typ == 'photo') {
		$is_cover = '0';
		$is_slide = '0';
		$is_slideonly = '0';
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
function wppa_get_album_name($id = '', $raw = '') {
	global $wpdb;
    
    if ($id == '0') $name = __('--- none ---', 'wppa');
    elseif ($id == '-1') $name = __('--- separate ---', 'wppa');
    else {
        if ($id == '') if (isset($_GET['album'])) $id = $_GET['album'];
        $id = $wpdb->escape($id);	
        if (is_numeric($id)) $name = $wpdb->get_var("SELECT name FROM " . ALBUM_TABLE . " WHERE id=$id");
    }
	if ($name) {
		if ($raw != 'raw') $name = stripslashes($name);
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
/*    
	if ($name == '') return '';	// No name, no match

	$nam = stripslashes($name);
	$albs = $wpdb->get_results("SELECT id, name FROM ".ALBUM_TABLE, 'ARRAY_A');
	if ($albs) foreach($albs as $alb) {
		if ($nam == stripslashes($alb['name'])) return $alb['id'];
	}
	return '';
}
*/
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
function wppa_sep($opt = '') {
	if (get_option('permalink_structure') == '') {
		if ($opt == 'js') $sep = '&';
		else $sep = '&amp;';
	}
    else $sep = '?';
	return $sep;
}

// determine page
function wppa_page($page) {
	global $wppa_occur;
	global $wppa_master_occur;
	global $wppa_widget_occur;
	global $wppa_in_widget;
	global $is_slide;
	global $is_slideonly;
	global $single_photo;

	$occur = '0';
	if ($wppa_in_widget) {
		if (isset($_GET['woccur'])) if (is_numeric($_GET['woccur'])) $occur = $_GET['woccur'];
	}
	else {
		if (isset($_GET['occur'])) if (is_numeric($_GET['occur'])) $occur = $_GET['occur'];
	}

	$ref_occur = $wppa_in_widget ? $wppa_widget_occur : $wppa_occur;
	
	if ($is_slide == '1') $cur_page = 'slide';			// Do slide or single when explixitly on
	elseif ($is_slideonly == '1') $cur_page = 'slide';		// Slideonly is a subset of slide
//	elseif (isset($_GET['topten'])) $cur_page = 'slide';	// Do slide for topten selection
	elseif (is_numeric($single_photo)) $cur_page = 'oneofone';
	elseif ($occur == $ref_occur) {					// Interprete $_GET only if occur is current
		if (isset($_GET['slide'])) $cur_page = 'slide';	
		elseif (isset($_GET['photo'])) $cur_page = 'single';
		else $cur_page = 'albums';
	}
	else $cur_page = 'albums';	
//echo('Page='.$cur_page);
	if ($cur_page == $page) return TRUE; else return FALSE;
}

// get id of coverphoto. does all testing
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
	global $wppa_master_occur;
	global $wppa_widget_occur;
	global $wppa_in_widget;
	
	if ($id == false) return '';
	$occur = $wppa_in_widget ? $wppa_widget_occur : $wppa_occur;
	$w = $wppa_in_widget ? 'w' : '';
	$image = $wpdb->get_row("SELECT * FROM " . PHOTO_TABLE . " WHERE id={$id} LIMIT 1", 'ARRAY_A');
	if ($image) $imgurl = get_permalink()  . wppa_sep() . 'album=' . $image['album'] . '&amp;photo=' . $image['id'] . '&amp;cover=0&amp;'.$w.'occur=' . $occur;	
	else $imgurl = '';
	return $imgurl;
}

// loop album
function wppa_get_albums($album = false, $type = '') {
	global $wpdb;
    global $startalbum;
	global $is_cover;
	global $wppa_occur;
	global $wppa_master_occur;
	global $wppa_widget_occur;
	global $wppa_in_widget;
	global $wppa_src;
	global $wppa_album_count;

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
		if ($wppa_in_widget) {
			if (isset($_GET['woccur'])) if (is_numeric($_GET['woccur'])) $occur = $_GET['woccur'];
		}
		else {
			if (isset($_GET['occur'])) if (is_numeric($_GET['occur'])) $occur = $_GET['occur'];
		}
		
		// Check if querystring given This has the highest priority in case of matching occurrance
		// Obey querystring only if the global occurence matches the occurence in the querystring, or no query occurrence given.
		$ref_occur = $wppa_in_widget ? $wppa_widget_occur : $wppa_occur;
		if (($occur == $ref_occur) && (isset($_GET['album']))) {
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
	$wppa_album_count = count($albums);
	return $albums;
}

// get link to album by id or in loop
function wppa_album_url($xid = '') {
	echo(wppa_get_album_url($xid));
}
function wppa_get_album_url($xid = '') {
	global $album;
	global $wppa_occur;
	global $wppa_master_occur;
	global $wppa_widget_occur;
	global $wppa_in_widget;
	
	$occur = $wppa_in_widget ? $wppa_widget_occur : $wppa_occur;
	$w = $wppa_in_widget ? 'w' : '';
	
	if ($xid != '') $id = $xid;
	elseif (isset($album['id'])) {
		$id = $album['id'];
	}
	if ($id != '') {
		$link = get_permalink() . wppa_sep() . 'album=' . $id . '&amp;cover=0&amp;'.$w.'occur='.$occur;
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
	global $wppa_master_occur;
	global $wppa_widget_occur;
	global $wppa_in_widget;
	
	$occur = $wppa_in_widget ? $wppa_widget_occur : $wppa_occur;
	$w = $wppa_in_widget ? 'w' : '';
	$link = get_permalink() . wppa_sep() . 'album=' . $album['id'] . '&amp;slide=true' . '&amp;'.$w.'occur=' . $occur;
	
	return $link;	
}

// loop thumbs
function wppa_get_thumbs() {
	global $wpdb;
    global $startalbum;
	global $wppa_occur;
	global $wppa_master_occur;
	global $wppa_widget_occur;
	global $wppa_in_widget;
	global $wppa_src;
	global $wppa_thumb_count;

	$src = '';
	if (isset($_POST['wppa-searchstring'])) {
		$src = $_POST['wppa-searchstring'];
	}
	elseif (isset($_GET['wppa_src'])) {
		$src = $_GET['wppa_src'];
	}
	
	if (isset($_GET['topten'])) {
		$max = get_option('wppa_topten_count', '10');
		$thumbs = $wpdb->get_results('SELECT * FROM '.PHOTO_TABLE.' WHERE mean_rating > 0 ORDER BY mean_rating DESC LIMIT '.$max, 'ARRAY_A');
	}
	elseif (strlen($src)) {
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
		if ($wppa_in_widget) {
			if (isset($_GET['woccur'])) if (is_numeric($_GET['woccur'])) $occur = $_GET['woccur'];
		}
		else {
			if (isset($_GET['occur'])) if (is_numeric($_GET['occur'])) $occur = $_GET['occur'];
		}
		
		// Obey querystring only if the global occurence matches the occurence in the querystring, or no query occurrence given.
		$ref_occur = $wppa_in_widget ? $wppa_widget_occur : $wppa_occur;
		if (($occur == $ref_occur) && (isset($_GET['album']))) {
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
	$wppa_thumb_count = count($thumbs);
	return $thumbs;
}

// get link to photo
function wppa_photo_page_url() {
	echo(wppa_get_photo_page_url());
}
function wppa_get_photo_page_url() {
	global $thumb;
	global $wppa_occur;
	global $wppa_master_occur;
	global $wppa_widget_occur;
	global $wppa_in_widget;
	
    if (isset($_GET['album'])) {
		$url = get_permalink()  . wppa_sep() . 'album=' . $_GET['album'] . '&amp;photo=' . $thumb['id'];
	}
	else {
		$url = get_permalink()  . wppa_sep() . 'photo=' . $thumb['id'];
		if (isset($_POST['wppa-searchstring'])) {
			$url .= '&amp;wppa_src=' . $_POST['wppa-searchstring'];
		}
	}
	$occur = $wppa_in_widget ? $wppa_widget_occur : $wppa_occur;
	$w = $wppa_in_widget ? 'w' : '';
	$url .= '&amp;'.$w.'occur=' . $occur;
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

// get full img style
function wppa_fullimgstyle($id = '') {
	echo wppa_get_fullimgstyle($id);
}
function wppa_get_fullimgstyle($id = '') {
	global $wpdb;
    global $wppa_fullsize;
	
	if (!is_numeric($wppa_fullsize) || $wppa_fullsize == '0') $wppa_fullsize = get_option('wppa_fullsize', '640');

	$wppa_enlarge = get_option('wppa_enlarge', 'no');
	
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
global $wpdb;
	global $wppa_master_occur;
	global $wppa_widget_occur;
	global $wppa_occur;
	global $wppa_in_widget;
	global $wppa_in_widget_linkurl;
	global $wppa_in_widget_linktitle;
	global $wppa_in_widget_timeout;

	$user = wppa_get_user();
	
	if (isset($_GET['photo'])) {
		$photo = $_GET['photo'];
	}
	else $photo = '0';

	$rating_request = (isset($_GET['rating']) && ($id == $photo));
	$rating_on = (get_option('wppa_rating_on', 'yes') == 'yes');
	$rating_allowed = (get_option('wppa_rating_login', 'yes') == 'no' || is_user_logged_in());
	
	if ($rating_request && $rating_on && $rating_allowed) { // Rating request
		$rating = $_GET['rating'];
		
		if ($rating != '1' && $rating != '2' && $rating != '3' && $rating != '4' && $rating != '5') die(__('<b>ERROR: Attempt to enter an invalid rating.</b>', 'wppa'));

		$my_oldrat = $wpdb->get_var($wpdb->prepare('SELECT * FROM `'.WPPA_RATING.'` WHERE `photo` = %d AND `user` = %s LIMIT 1', $id, $user)); 

		if ($my_oldrat) {
			if (get_option('wppa_rating_change', 'no') == 'yes') {	// Modify my vote
				$query = $wpdb->prepare('UPDATE `'.WPPA_RATING.'` SET `value` = %d WHERE `photo` = %d AND `user` = %s LIMIT 1', $rating, $id, $user);
				$iret = $wpdb->query($query);
				if (!$iret) {
//					if (defined('WP_DEBUG')) echo('Unable to update rating. Query = '.$query);
					$myrat = $my_oldrat['value'];
				}
				else {
					$myrat = $rating;
				}
			}
			else if (get_option('wppa_rating_multi', 'no') == 'yes') {	// Add another vote from me
				$query = $wpdb->prepare('INSERT INTO `'.WPPA_RATING. '` (`id`, `photo`, `value`, `user`) VALUES (0, %d, %s, %s)', $id, $rating, $user);
				$iret = $wpdb->query($query);
				if (!$iret) {
//					if (defined('WP_DEBUG')) echo('Unable to add a rating. Query = '.$query);
					$myrat = $my_oldrat['value'];
				}
				else {
					$query = $wpdb->prepare('SELECT * FROM `'.WPPA_RATING.'`  WHERE `photo` = %d AND `user` = %s', $id, $user);
					$myrats = $wpdb->get_results($query, 'ARRAY_A');
					if (!$myrats) {
						if (defined('WP_DEBUG')) echo('Unable to retrieve ratings. Query = '.$query);
						$myrat = $my_oldrat['value'];
					}
					else {
						$sum = 0;
						$cnt = 0;
						foreach ($myrats as $rt) {
							$sum += $rt['value'];
							$cnt ++;
						}
						if ($cnt > 0) $myrat = $sum/$cnt; else $myrat = $my_oldrat['value'];
					}
				}
			}
		}
		else {	// This is the first and only rating for this photo/user combi
			$iret = $wpdb->query($wpdb->prepare('INSERT INTO `'.WPPA_RATING. '` (`id`, `photo`, `value`, `user`) VALUES (0, %d, %s, %s)', $id, $rating, $user));
//			if (!$iret) {
//				if (defined('WP_DEBUG')) echo('Unable to save rating.');
//			}
			$myrat = $rating;
		}

		// Compute new avgrat
		$ratings = $wpdb->get_results('SELECT * FROM '.WPPA_RATING.' WHERE photo = '.$id, 'ARRAY_A');
		if ($ratings) {
			$sum = 0;
			$cnt = 0;
			foreach ($ratings as $rt) {
				$sum += $rt['value'];
				$cnt ++;
			}
			if ($cnt > 0) $avgrat = $sum/$cnt; else $avgrat = '0';
		}
		else $avgrat = '0';
		// Store it
		// if (defined('WP_DEBUG')) echo('Trying to store '.$avgrat.' to photo #'.$id);
		$query = $wpdb->prepare('UPDATE `'.PHOTO_TABLE. '` SET `mean_rating` = %s WHERE `id` = %d LIMIT 1', $avgrat, $id);
		$iret = $wpdb->query($query);
//		if (!$iret) if (defined('WP_DEBUG')) echo('Error, could not update avg rating for photo '.$id.'. Query = '.$query);
	}
	else {	// No rating request
		$myrat = $wpdb->get_var($wpdb->prepare('SELECT `value` FROM `'.WPPA_RATING.'` WHERE `photo` = %d AND `user` = %s LIMIT 1', $id, $user)); 
		if (!$myrat) $myrat = '0';
	}
	// Now we know the (updated) $myrat
	// Find the $avgrat
	$avgrat = $wpdb->get_var('SELECT mean_rating FROM '.PHOTO_TABLE.' WHERE id = '.$id.' LIMIT 1'); 
	if (!$avgrat) $avgrat = '0';
	
	// Compose the rating request url
	$url = get_permalink() . wppa_sep('js');
	if (isset($_GET['album'])) $url .= 'album='.$_GET['album'].'&';
	if (isset($_GET['cover'])) $url .= 'cover='.$_GET['cover'].'&';
	if (isset($_GET['slide'])) $url .= 'slide='.$_GET['slide'].'&';
//	if (isset($_GET['occur'])) $url .= 'occur='.$_GET['occur'].'&';
	if ($wppa_in_widget) {
		$url .= 'woccur='.$wppa_widget_occur.'&';
	}
	else {
	   $url .= 'occur='.$wppa_occur.'&';
	}
	if (isset($_GET['topten'])) $url .= 'topten='.$_GET['topten'].'&';
	$url .= 'photo=' . $id . '&rating=';
	
	// Produce final result
    $result = "'" . $wppa_master_occur . "','" . $index . "','" . wppa_get_photo_url($id) . "','" . wppa_get_fullimgstyle($id) . "','" . esc_js(wppa_get_photo_name($id)) . "','" . wppa_html(esc_js(stripslashes(wppa_photo_desc($id,true)))) . "','" .$id. "','" .$avgrat. "','" .$myrat. "','" .$url. "','" .$wppa_in_widget_linkurl. "','" .$wppa_in_widget_linktitle. "','" .$wppa_in_widget_timeout. "'";
    return $result;                                                        
}

// get user
function wppa_get_user() {
global $current_user;
	if (is_user_logged_in()) {
		get_currentuserinfo();
		$user = $current_user->user_login;
		return $user;
	}
	else {
		if (is_admin()) {
			wpa_die('It is not allowed to run admin pages while you are not logged in.');
		}
		else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}
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
	case '4':
		$result = 'ORDER BY mean_rating';
		break;
    default:
        $result = 'ORDER BY id';
    }
    if (get_option('wppa_list_photos_desc') == 'yes') $result .= ' DESC';
    return $result;
}

function wppa_get_imgstyle($file, $max_size, $xvalign = '', $type = '') {
global $wppa_auto_colwidth;
global $wppa_portrait_only;
global $is_slideonly;

	if($file == '') return '';					// no image: no dimensions
	if (!is_file($file)) return '';				// no file: no dimensions (2.3.0)
	$result = '';
	$image_attr = getimagesize( $file );
	
	if ($type == 'fullsize') {
		if ($wppa_portrait_only) {
			$width = $max_size;
			$height = round($width * $image_attr[1] / $image_attr[0]);
		}
		else {
			$ratioref = get_option('wppa_maxheight', get_option('wppa_fullsize', '640')) / get_option('wppa_fullsize', '640');
			$max_height = round($max_size * $ratioref);
			if (wppa_is_wider($image_attr[0], $image_attr[1])) {
				$width = $max_size;
				$height = round($width * $image_attr[1] / $image_attr[0]);
			}
			else {
				$height = round($ratioref * $max_size);
				$width = round($height * $image_attr[0] / $image_attr[1]);
			}
			if ($image_attr[0] < $width && $image_attr[1] < $height) {
				if (get_option('wppa_enlarge', 'no') == 'no') {
					$width = $image_attr[0];
					$height = $image_attr[1];
				}
			}
		}
	}
	else {
		if (wppa_is_landscape($image_attr)) {
			$width = $max_size;
			$height = round($max_size * $image_attr[1] / $image_attr[0]);
		}
		else {
			$height = $max_size;
			$width = round($max_size * $image_attr[0] / $image_attr[1]);
		}
	}
	
	switch ($type) {
		case 'cover':
			$result .= ' width:' . $width . 'px; height:' . $height . 'px;';
			if ((get_option('wppa_use_cover_opacity', 'no') == 'yes') && !is_feed()) {
				$opac = get_option('wppa_cover_opacity', '80');
				$result .= ' opacity:' . $opac/100 . '; filter:alpha(opacity=' . $opac . ');';
			}
			break;
		case 'thumb':
			$result .= ' width:' . $width . 'px; height:' . $height . 'px;';
			if ($xvalign == 'optional') $valign = get_option('wppa_valign', '');
			else $valign = $xvalign;
			if ($valign != 'default') {	// Center horizontally
				$delta = floor(($max_size - $width) / 2);
				if (is_numeric($valign)) $delta += $valign;
				if ($delta < '0') $delta = '0';
				if ($delta > '0') $result .= ' margin-left:' . $delta . 'px; margin-right:' . $delta . 'px;';
			} 
			switch ($valign) {
				case 'top':
					$result .= ' margin-top: 0px;';
					break;
				case 'center':
					$delta = round(($max_size - $height) / 2);
					if ($delta < '0') $delta = '0';
					$result .= ' margin-top: ' . $delta . 'px;';
					break;
				case 'bottom':
					$delta = $max_size - $height;
					if ($delta < '0') $delta = '0';
					$result .= ' margin-top: ' . $delta . 'px;';
					break;
				default:
					if (is_numeric($valign)) {
						$delta = $valign;
						$result .= ' margin-top: '.$delta.'px; margin-bottom: '.$delta.'px;';
					}
			}
			if ((get_option('wppa_use_thumb_opacity', 'no') == 'yes') && !is_feed()) {
				$opac = get_option('wppa_thumb_opacity', '80');
				$result .= ' opacity:' . $opac/100 . '; filter:alpha(opacity=' . $opac . ');';
			}
			break;
		case 'fullsize':
			$result .= ' width:' . $width . 'px;';
			
			if (!$wppa_auto_colwidth) {
				$result .= 'height:' . $height . 'px;';
			}
			
			if ($is_slideonly == '1') {
				$valign = 'fit';
			}
			elseif ($xvalign == 'optional') {
				$valign = get_option('wppa_fullvalign', '');
			}
			else {
				$valign = $xvalign;
			}
			
			if ($valign != 'default') {
				// Center horizontally
				$delta = round(($max_size - $width) / 2);
				if ($delta < '0') $delta = '0';
				$result .= ' margin-left:' . $delta . 'px;';
				// Position vertically
				$delta = '0';
				if (!$wppa_auto_colwidth && !wppa_page('oneofone')) {
					switch ($valign) {
						case 'top':
						case 'fit':
							$delta = '0';
							break;
						case 'center':
							$delta = round(($max_height - $height) / 2);
							if ($delta < '0') $delta = '0';
							break;
						case 'bottom':
							$delta = $max_height - $height;
							if ($delta < '0') $delta = '0';
							break;
					}
				}
				$result .= ' margin-top:' . $delta . 'px;';
			}
			break;
		default:
			echo ('Error wrong "$type" argument: '.$type.' in wppa_get_imgstyle');
	}
	return $result;
}

function wppa_is_landscape($img_attr) {
	return ($img_attr[0] > $img_attr[1]);
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
				$rating = wppa_get_rating_by_id($id);
				$result .= 'wppa_popup(' . $wppa_master_occur . ', this, ' . $id . ', \''.$rating.'\');';
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
	global $wppa_widget_occur;
	global $wppa_in_widget;
	
	if ($npages < '2') return;	// Nothing to display
	if (is_feed()) {
//		wppa_dummy_bar(__('- - - Pagelinks - - -', 'wppa'));
		return;
	}
	
	// Compose the Previous and Next Page urls
	if (isset($_GET['cover'])) $ic = $_GET['cover'];
	else {
		if ($is_cover == '1') $ic = '1'; else $ic = '0';
	}
	$pnu = get_permalink() . wppa_sep() . 'cover=' . $ic;
	if (isset($_GET['album'])) $pnu .= '&amp;album=' . $_GET['album'];
	if (isset($_GET['photo'])) $pnu .= '&amp;photo=' . $_GET['photo'];
	$occur = $wppa_in_widget ? $wppa_master_occur : $wppa_occur;
	$w = $wppa_in_widget ? 'w' : '';
	$pnu .= '&amp;'.$w.'occur=' . $occur;
	$prevurl = $pnu . '&amp;page=' . ($curpage - 1);	
	$nexturl = $pnu . '&amp;page=' . ($curpage + 1);
	
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
	<div id="prevnext-a-<?php echo($wppa_master_occur); ?>" class="wppa-nav-text wppa-box wppa-nav" style="clear:both; text-align:center; <?php _wcs('wppa-box'); _wcs('wppa-nav'); ?>" >
		<div id="prev-page" style="float:left; text-align:left; <?php if ($curpage == '1') echo('visibility: hidden;'); ?>">
			<span style="cursor: default;">&laquo;&nbsp;</span>
			<a id="p-p" href="<?php echo($prevurl); ?>" ><?php _e('Prev.&nbsp;page', 'wppa'); ?></a>
		</div><!-- #prev-page -->
		<div id="next-page" style="float:right; text-align:right; <?php if ($curpage == $npages) echo('visibility: hidden;'); ?>">
			<a id="n-p" href="<?php echo($nexturl); ?>" ><?php _e('Next&nbsp;page', 'wppa'); ?></a>
			<span style="cursor: default;">&nbsp;&raquo;</span>
		</div><!-- #next-page -->
		
<?php
		if ($from > '1') {
			echo('.&nbsp;.&nbsp;.&nbsp;');
		}
		for ($i=$from; $i<=$to; $i++) {
			if ($curpage == $i) { 
?>
				<div class="wppa-mini-box wppa-alt wppa-black" style="display:inline; text-align:center; <?php _wcs('wppa-mini-box'); _wcs('wppa-alt'); _wcs('wppa-black'); ?> text-decoration: none; cursor: default; font-weight:normal; " >
					<a style="font-weight:normal; text-decoration: none; cursor: default; <?php _wcs('wppa-black') ?>;">&nbsp;<?php echo($i) ?>&nbsp;</a>
				</div>
<?php
			}
			else { 
?>
				<div class="wppa-mini-box wppa-even" style="display:inline; text-align:center; <?php _wcs('wppa-mini-box'); _wcs('wppa-even'); ?>" >
					<a href="<?php echo($pnu . '&amp;page=' . $i) ?>">&nbsp;<?php echo($i) ?>&nbsp;</a>
				</div>
<?php		
			}
		}
		if ($to < $npages) {
			echo('&nbsp;.&nbsp;.&nbsp;.');
		}
?>
	</div><!-- #prevnext-a-<?php echo($wppa_master_occur); ?> -->
<?php
}
	
function wppa_set_runtimestyle() { 
global $wppa_master_occur;
global $wppa_auto_colwidth;

	if ($wppa_master_occur == '1') {	// First time only
		/* Nonce field for rating security */
		if (isset($_GET['rating'])) {
			if (isset($_GET['wppa_nonce'])) $nonce = $_GET['wppa_nonce']; else $nonce = '';
			$ok = wp_verify_nonce($nonce, 'wppa-check');
			if ($ok) sleep(2);
			else die(__('<b>ERROR: Illegal attempt to enter a rating.</b>', 'wppa'));
		}
		wp_nonce_field('wppa-check' , 'wppa_nonce', false);
		/* If you simplify this by making a cdata block, you will break rss feeds */
		/* rss attempts to create a nested cdata block that causes the loss of the script tag */
		/* The errormessage will say that the /script tag is not matched while it is. */
		
		/* This goes into wppa_theme.js */ ?>
		<script type="text/javascript">wppa_bgcolor_img = "<?php echo(get_option('wppa_bgcolor_img', '#eeeeee')); ?>";</script>
		<script type="text/javascript">wppa_popup_nolink = <?php if (get_option('wppa_no_thumb_links', 'no') == 'yes') echo('true'); else echo('false'); ?>;</script>

<?php	/* This goes into wppa_slideshow.js */ ?>
		<script type="text/javascript"><?php if (get_option('wppa_fadein_after_fadeout', 'no') == 'yes') echo('wppa_fadein_after_fadeout = true;'); ?></script>
		<script type="text/javascript">wppa_animation_speed = <?php echo get_option('wppa_animation_speed', 400) ?>;</script>
		<script type="text/javascript">wppa_imgdir = "<?php echo wppa_get_imgdir() ?>";</script>
		<script type="text/javascript">wppa_auto_colwidth = <?php if ($wppa_auto_colwidth) echo('true'); else echo('false'); ?>;</script>
		<script type="text/javascript">wppa_thumbnail_area_delta = <?php echo wppa_get_thumbnail_area_delta(); ?>;</script>
		<script type="text/javascript">wppa_textframe_delta = <?php echo wppa_get_textframe_delta(); ?>;</script>
		<script type="text/javascript">wppa_box_delta = <?php echo wppa_get_box_delta(); ?>;</script>
		<script type="text/javascript">wppa_ss_timeout = <?php echo get_option('wppa_slideshow_timeout', 2500) ?>;</script>
		<script type="text/javascript">wppa_fadein_after_fadeout = <?php if (get_option('wppa_fadein_after_fadeout', 'no') == 'yes') echo('true'); else echo('false'); ?>;</script>
		<script type="text/javascript">wppa_preambule = <?php echo wppa_get_preambule() ?>;</script>
		<script type="text/javascript">wppa_thumbnail_pitch = <?php echo (get_option('wppa_tf_width', '100') + get_option('wppa_tn_margin', '0')); ?>;</script>
		<script type="text/javascript">wppa_filmstrip_length = <?php echo (wppa_get_container_width() - ( 2*6 + 2*23 + 2*get_option('wppa_bwidth', '1'))); ?>;</script>
		<script type="text/javascript">wppa_filmstrip_margin = <?php echo (get_option('wppa_tn_margin', '0') / 2); ?>;</script>
		<script type="text/javascript">wppa_filmstrip_area_delta = <?php echo ( 2*6 + 2*23 + 2*get_option('wppa_bwidth', '1')) ?>;</script>
		<script type="text/javascript">wppa_film_show_glue = <?php if (get_option('wppa_film_show_glue', 'yes') == 'yes') echo('true'); else echo('false'); ?>;</script>
		<script type="text/javascript">wppa_slideshow = "<?php _e('Slideshow', 'wppa'); ?>";</script>
		<script type="text/javascript">wppa_photo = "<?php _e('Photo', 'wppa'); ?>";</script>
		<script type="text/javascript">wppa_of = "<?php _e('of', 'wppa'); ?>";</script>
		<script type="text/javascript">wppa_prevphoto = "<?php _e('Prev.&nbsp;photo', 'wppa'); ?>";</script>
		<script type="text/javascript">wppa_nextphoto = "<?php _e('Next&nbsp;photo', 'wppa'); ?>";</script>
		<script type="text/javascript">wppa_username = "<?php echo(wppa_get_user()) ?>";</script>
		<script type="text/javascript">wppa_rating_once = <?php if (get_option('wppa_rating_change', 'no') == 'yes' || get_option('wppa_rating_multi', 'no') == 'yes') echo('false'); else echo('true'); ?>;</script>
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
	global $wppa_in_ss_widget;
	global $wppa_portrait_only;
	
	$fs = get_option('wppa_fullsize', '640');
	$cs = get_option('wppa_colwidth', $fs);
	if ($cs == 'auto') {
		$cs = $fs;
		$wppa_auto_colwidth = true;
	}
	$result = '';
	$gfs = (is_numeric($wppa_fullsize) && $wppa_fullsize > '0') ? $wppa_fullsize : $fs;
	
	$gfh = floor($gfs * get_option('wppa_maxheight', get_option('wppa_fullsize', '640')) / get_option('wppa_fullsize', '640'));

//	if ($wppa_in_ss_widget && $wppa_portrait_only) {
	if ($wppa_portrait_only) {
		$result = 'width: ' . $gfs . 'px;';	// No height
	}
	else {
		if (wppa_page('oneofone')) {
			$imgattr = getimagesize(wppa_get_image_path_by_id($single_photo));
			$h = floor($gfs * $imgattr[1] / $imgattr[0]);
			$result .= 'height: ' . $h . 'px;';
		}
		elseif ($wppa_auto_colwidth) {
			$result .= ' height: ' . $gfh . 'px;';
		}
		elseif (get_option('wppa_fullvalign', 'default') == 'default') {
			$result .= 'min-height: ' . $gfh . 'px;'; 
		}
		else {
			$result .= 'height: ' . $gfh . 'px;'; 
		}
		$result .= 'width: ' . $gfs . 'px;';
	}
	
	$hor = get_option('wppa_fullhalign', 'center');
	if ($gfs == $fs) {
		if ($fs != $cs) {
			switch ($hor) {
			case 'left':
				$result .= 'margin-left: 0px;';
				break;
			case 'center':
				$result .= 'margin-left: ' . floor(($cs - $fs) / 2) . 'px;';
				break;
			case 'right':
				$result .= 'margin-left: ' . ($cs - $fs) . 'px;';
				break;
			}
		}
	}

	return $result;
}

function wppa_get_thumb_frame_style($glue = false, $film = '') {
	$tfw = get_option('wppa_tf_width');
	$tfh = get_option('wppa_tf_height');
	$mgl = get_option('wppa_tn_margin');
	$mgl2 = floor($mgl / '2');
	if ($film == '' && get_option('wppa_thumb_auto', 'no') == 'yes') {
		$area = wppa_get_box_width() + $tfw;	// Area for n+1 thumbs
		$n_1 = floor($area / ($tfw + $mgl));
		$mgl = floor($area / $n_1) - $tfw;	
	}
	if (is_numeric($tfw) && is_numeric($tfh)) {
		$result = 'width: '.$tfw.'px; height: '.$tfh.'px; margin-left: '.$mgl.'px; margin-top: '.$mgl2.'px; margin-bottom: '.$mgl2.'px;';
		if ($glue && (get_option('wppa_film_show_glue', 'yes') == 'yes')) {
			$result .= 'padding-right:'.$mgl.'px; border-right: 2px dotted gray;';
		}
	}
	else $result = '';
	return $result;
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
global $wppa_in_widget;

	$result = '';
	
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
	
	if (!$wppa_in_widget) $result .= 'clear: both; ';
	$ctw = wppa_get_container_width();
	if ($wppa_auto_colwidth) {
		if (is_feed()) {
			$result .= 'width:'.$ctw.'px;';
		}
	}
	else {
		$result .= 'width:'.$ctw.'px;';
	}
	
//	if ($wppa_align == '' || 
	if ($wppa_align == 'left') {
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
global $wppa_in_widget;

	if ($wppa_in_widget) {
		if (isset($_GET['woccur'])) $oc = $_GET['woccur']; else $oc = '1';
	}
	else {
		if (isset($_GET['occur'])) $oc = $_GET['occur']; else $oc = '1';
	}
	if (isset($_GET['page']) && $wppa_master_occur == $oc) $curpage = $_GET['page']; else $curpage = '1';
	return $curpage;
}

function wppa_container($action) {
global $wppa_revno;				// The official version (wppa.php and readme.txt)
global $wppa_version;			// The theme version (wppa_theme.php)
global $wppa_api_version;		// The API version (this files version)
global $wppa_master_occur;
global $wppa_in_widget;
global $wppa_alt;
global $wppa_portrait_only;

	if (is_feed()) return;		// Need no container in RSS feeds
	if ($action == 'open') {
		if (wppa_page('oneofone')) $wppa_portrait_only = true;
		$wppa_alt = 'alt';
//		if ($wppa_inp) echo('</p>');				// Close wpautop generated paragraph if we're in
		echo('<div id="wppa-container-'.$wppa_master_occur.'" style="'.wppa_get_container_style().'" class="wppa-container wppa-rev-'.$wppa_revno.' wppa-theme-'.$wppa_version.' wppa-api-'.$wppa_api_version.'" >');
	}
	elseif ($action == 'close')	{
		if (wppa_page('oneofone')) $wppa_portrait_only = false;
		if (!$wppa_in_widget) echo('<div style="clear:both;"></div>');
		echo('</div><!-- wppa-container-'.$wppa_master_occur.' -->');
		if (!$wppa_in_widget) 
						echo('<p>');					// Re-open paragraph
	}
	else {
		echo('<span style="color:red;">Error, wppa_container() called with wrong argument: '.$action.'. Possible values: \'open\' or \'close\'</span>');
	}
}

function wppa_album_list($action) {
global $wppa_master_occur;
global $cover_count;

	if ($action == 'open') {
		$cover_count = '0';
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
global $cover_count;

	if ($action == 'open') {
		$cover_count = '0';
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
		if (is_feed()) {
			echo('<div id="wppa-thumbarea-'.$wppa_master_occur.'" style="'.__wcs('wppa-box').__wcs('wppa-'.$wppa_alt).'">');
		}
		else {
			echo('<div id="wppa-thumbarea-'.$wppa_master_occur.'" style="'.__wcs('wppa-box').__wcs('wppa-'.$wppa_alt).'width: '.wppa_get_thumbnail_area_width().'px;" class="thumbnail-area wppa-box wppa-'.$wppa_alt.'" onclick="wppa_popdown('.$wppa_master_occur.')" >');
		}		
		if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
	}
	elseif ($action == 'close') {
		echo('</div><!-- wppa-thumbarea-'.$wppa_master_occur.' -->');
	}
	else {
		echo('<span style="color:red;">Error, wppa_thumb_area() called with wrong argument: '.$action.'. Possible values: \'open\' or \'close\'</span>');
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
		if ($is_cover == '1') {		// Cover has no thumbs: 0 pages
			$result = '0';
		} 
		elseif (count($array) <= get_option('wppa_min_thumbs', '1')) {	// Less than treshold: 0
			$result = '0';
		}
		elseif ($tps != '0') {
			$result = ceil(count($array) / $tps);	// Pag on: compute
		}
		else {
			$result = '1';								// Pag off: all fits on 1
		}
	}
	return $result;
}

function wppa_album_cover() {
global $album;
global $wppa_master_occur;
global $wppa_alt;
global $cover_count;

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
		$href_p = $href;
		$href_t = $href;
		$title_p = $title;
		$title_t = $title;
	} elseif (is_numeric($album['cover_linkpage']) && $album['cover_linkpage'] == -1) {
		$href = '';
		$title = '';
		$href_p = $href;
		$href_t = $href;
		$title_p = $title;
		$title_t = $title;
	} else {
		if ($photocount != '0' && $photocount) {
			$href_p = wppa_get_image_page_url_by_id($coverphoto); 
			$title_p = __('View the cover photo', 'wppa'); 
			if ($photocount > 1) $title_p .= __('s', 'wppa');
		} else {
			$href_p = wppa_get_album_url(); 
			$title_p = __('View the album', 'wppa') . ' ' . $album['name'];
		}
		$href_t = wppa_get_album_url();
		$title_t = __('View the album', 'wppa') . ' ' . $album['name'];
	}
	$src = wppa_get_thumb_url_by_id($coverphoto);	
	$path = wppa_get_thumb_path_by_id($coverphoto);
	$imgattr = wppa_get_imgstyle($path, get_option('wppa_smallsize'), '', 'cover');
	if (is_feed()) {
//		$imgattr .= 'margin:4px;';
		$events = '';
	}
	else {
		$events = wppa_get_imgevents('cover');
	}
	$photo_left = get_option('wppa_coverphoto_left', 'no') == 'yes';
	
	$style =  __wcs('wppa-box').__wcs('wppa-'.$wppa_alt);
	if (is_feed()) $style .= ' padding:7px;';
	
	$wid = wppa_get_cover_width('cover');
	$style .= 'width: '.$wid.'px;';	
	if ($cover_count != '0') $style .= 'margin-left: 8px;';
	wppa_step_covercount('cover');
?>
		<div id="album-<?php echo($album['id'].'-'.$wppa_master_occur) ?>" class="album wppa-box wppa-cover-box wppa-<?php echo($wppa_alt); ?>" style="<?php echo($style) ?>" >
<?php 
		if ($src != '') { 
			$photoframestyle = $photo_left ? 'style="float:left; margin-right:5px;"' : 'style="float:right; margin-left:5px;"';
?>
			<div id="coverphoto_frame_<?php echo($album['id'].'_'.$wppa_master_occur) ?>" class="coverphoto-frame" <?php echo($photoframestyle) ?>>
<?php 
			if ($href_p != '') {
				?>
				<a href="<?php echo($href_p); ?>" title="<?php echo($title_p); ?>">
					<img src="<?php echo($src); ?>" alt="<?php echo($title_p); ?>" class="image wppa-img" style="<?php _wcs('wppa-img'); echo($imgattr); ?>" <?php echo($events) ?>/>
				</a><?php 
			} else { 
				?>
				<img src="<?php echo($src); ?>" alt="<?php echo($title_p); ?>" class="image wppa-img" style="<?php _wcs('wppa-img'); echo($imgattr); ?>" <?php echo($events) ?>/><?php 
			} 
			?>
			</div><!-- #coverphoto_frame_ <?php echo($album['id'].$wppa_master_occur) ?> --><?php 
		} 
		$textframestyle = wppa_get_text_frame_style($photo_left, 'cover');
		?>
<div id="covertext_frame_<?php echo($album['id'].'_'.$wppa_master_occur) ?>" class="wppa-text-frame covertext-frame" <?php echo($textframestyle) ?>>
		<h2 class="wppa-title" style="clear:none; <?php _wcs('wppa-title'); ?>"><?php 
			if ($href_t != '') { 
				?>
				<a href="<?php echo($href_t); ?>" title="<?php echo($title_t); ?>" class="wppa-title" style="<?php _wcs('wppa-title'); ?>"><?php echo(stripslashes($album['name'])); ?></a><?php
			} else { 
				echo(stripslashes($album['name'])); 
			} 
		?>
		</h2>
		<p class="wppa-box-text wppa-black" style="<?php _wcs('wppa-box-text'); _wcs('wppa-black'); ?>"><?php echo(wppa_html(wppa_get_the_album_desc())); ?></p>
		<div class="wppa-box-text wppa-black wppa-info"><?php 
			if ($photocount > $mincount && get_option('wppa_hide_slideshow', 'no') == 'no') { 
				?>
				<a href="<?php wppa_slideshow_url(); ?>" title="<?php _e('Slideshow', 'wppa'); ?>" style="<?php _wcs('wppa-box-text'); ?>" ><?php _e('Slideshow', 'wppa'); ?></a><?php 
			} else echo('&nbsp;'); 
		?>
		</div>
		<div class="wppa-box-text wppa-black wppa-info"><?php 
			if ($photocount > $mincount || $albumcount) { 
				?>
				<a href="<?php wppa_album_url(); ?>" title="<?php _e('View the album', 'wppa'); echo(' ' . $album['name']); ?>" style="<?php _wcs('wppa-box-text'); ?>" ><?php 
				_e('View', 'wppa'); 
				if ($albumcount) { 
					if ($albumcount == '1') {
						echo(' 1 '); _e('album', 'wppa'); 
					}
					else {
						echo(' ' . $albumcount . ' ');
						_e('albums', 'wppa');
					}
				}
				if ($photocount > $mincount && $albumcount) {
					echo(' ');
					_e('and', 'wppa'); 
				}
				if ($photocount > $mincount) { 
					if ($photocount == '1') {
						echo(' 1 '); _e('photo', 'wppa');
					}
					else {
						echo(' ' . $photocount . ' '); 
						_e('photos', 'wppa'); 
					}
				} 
				?>
				</a><?php 
			} 
		?>
		</div>
</div>
		<div style="clear:both;"></div>		
	</div><!-- #album-<?php echo($album['id'].'-'.$wppa_master_occur) ?> --><?php
	if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
}

function wppa_thumb_ascover() {
global $thumb;
global $wppa_master_occur;
global $wppa_alt;
global $cover_count;
	$path = wppa_get_thumb_path(); 
	$imgattr = wppa_get_imgstyle($path, get_option('wppa_smallsize'), '', 'cover'); 
	if (is_feed()) {
//		$imgattr .= 'margin:4px;';
		$events = '';
	}
	else {
		$events = wppa_get_imgevents('cover'); 
	}
	$src = wppa_get_thumb_url(); 
	$title = esc_js(wppa_get_photo_name($thumb['id'])); 
	$href = wppa_get_photo_page_url();
	$photo_left = get_option('wppa_thumbphoto_left', 'no') == 'yes';
	
	$style = __wcs('wppa-box').__wcs('wppa-'.$wppa_alt);
	if (is_feed()) $style .= ' padding:7px;';
	
	$wid = wppa_get_cover_width('thumb');
	$style .= 'width: '.$wid.'px;';	
	if ($cover_count != '0') $style .= 'margin-left: 8px;';
	wppa_step_covercount('thumb');
?>
	<div id="thumb-<?php echo($thumb['id'].'-'.$wppa_master_occur) ?>" class="thumb wppa-box wppa-cover-box wppa-<?php echo($wppa_alt); ?>" style="<?php echo($style) ?>" >
<?php 
		if ($src != '') { 
			$photoframestyle = $photo_left ? 'style="float:left; margin-right:5px;"' : 'style="float:right; margin-left:5px;"';
?>
			<div id="thumbphoto_frame_<?php echo($thumb['id'].'_'.$wppa_master_occur) ?>" class="thumbphoto-frame" <?php echo($photoframestyle) ?>>
			<a href="<?php echo($href); ?>" title="<?php echo($title); ?>">
				<img src="<?php echo($src); ?>" alt="<?php echo($title); ?>" class="image wppa-img" style="<?php _wcs('wppa-img'); echo($imgattr); ?>" <?php echo($events) ?>/>
			</a>
		</div><?php 
		}
		$textframestyle = wppa_get_text_frame_style($photo_left, 'thumb');
		?>
<div id="thumbtext_frame_<?php echo($thumb['id'].'_'.$wppa_master_occur) ?>" class="wppa-text-frame thumbtext-frame" <?php echo($textframestyle) ?>>
		<h2 class="wppa-title" style="clear:none;">
			<a href="<?php echo($href); ?>" title="<?php echo($title); ?>" style="<?php _wcs('wppa-title'); ?>" ><?php echo(stripslashes($thumb['name'])); ?></a>
		</h2>
		<p class="wppa-box-text wppa-black" style="<?php _wcs('wppa-box-text'); _wcs('wppa-black'); ?>" ><?php echo(wppa_html(stripslashes($thumb['description']))); ?></p>
</div>
		<div style="clear:both;"></div>		
	</div><!-- thumb-<?php echo($thumb['id'].'-'.$wppa_master_occur) ?> --><?php
	if ($wppa_alt == 'even') $wppa_alt = 'alt'; else $wppa_alt = 'even';
}

function wppa_thumb_default() {
global $thumb;
global $wppa_master_occur;
global $wppa_src;
	$src = wppa_get_thumb_path(); 
	$imgattr = wppa_get_imgstyle($src, get_option('wppa_thumbsize'), 'optional', 'thumb'); 
	$url = wppa_get_thumb_url(); 
	$events = wppa_get_imgevents('thumb', $thumb['id']); 

	if (get_option('wppa_use_thumb_popup') == 'yes') $title = esc_attr(stripslashes($thumb['description']));
	else $title = esc_js(wppa_get_photo_name($thumb['id']));
	
	if (is_feed()) {
?>
		<a href="<?php echo(get_permalink()) ?>"><img src="<?php echo($url) ?>" alt="<?php echo(esc_attr($thumb['name'])) ?>" title="<?php echo(esc_attr($thumb['name'])); ?>" style="<?php echo(wppa_get_imgstyle($src, '100', '4', 'thumb')) ?>" /></a>
<?php
		return;
	}
	
?>
	<div id="thumbnail_frame_<?php echo($thumb['id'].'_'.$wppa_master_occur) ?>" class="thumbnail-frame" style="<?php echo(wppa_get_thumb_frame_style()); ?>" >
<?php
	if (get_option('wppa_no_thumb_links', 'no') == 'no') {
?>
		<a href="<?php wppa_photo_page_url(); ?>" class="thumb-img" id="a-<?php echo($thumb['id'].'-'.$wppa_master_occur) ?>"><img src="<?php echo($url); ?>" alt="<?php echo(esc_attr($thumb['name'])); ?>" title="<?php echo(esc_attr($title)); ?>" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/></a>
<?php 
	}
	else {
?>
		<a id="a-<?php echo($thumb['id'].'-'.$wppa_master_occur) ?>"><img src="<?php echo($url); ?>" alt="<?php echo(esc_attr($thumb['name'])); ?>" title="<?php echo(esc_attr($title)); ?>" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/></a>
<?php
	}
	if ($wppa_src || isset($_GET['topten'])) { 
?>
	<div class="wppa-thumb-text" style="<?php _wcs('wppa-thumb-text') ?>" ><?php echo('(<a href="'.wppa_get_album_url($thumb['album']).'">'.stripslashes(wppa_get_album_name($thumb['album'])).'</a>)'); ?></div>
<?php } ?>
	<?php if (get_option('wppa_thumb_text_name', get_option('wppa_thumb_text', 'no')) == 'yes') { ?>
	<div class="wppa-thumb-text" style="<?php _wcs('wppa-thumb-text') ?>" ><?php echo(stripslashes($thumb['name'])); ?></div>
	<?php } ?>
	<?php if (get_option('wppa_thumb_text_desc', get_option('wppa_thumb_text', 'no')) == 'yes') { ?>
	<div class="wppa-thumb-text" style="<?php _wcs('wppa-thumb-text') ?>" ><?php echo(wppa_html(stripslashes($thumb['description']))); ?></div>
	<?php } ?>
	<?php if (get_option('wppa_thumb_text_rating', get_option('wppa_thumb_text', 'no')) == 'yes') { ?>
	<div class="wppa-thumb-text" style="<?php _wcs('wppa-thumb-text') ?>" ><?php echo(wppa_get_rating_by_id($thumb['id'])) ?></div>
	<?php } ?>
	</div><!-- #thumbnail_frame_<?php echo($thumb['id'].'_'.$wppa_master_occur) ?> -->
<?php	
}	

function wppa_get_mincount() {
global $wppa_src;
	$result = $wppa_src ? '0' : get_option('wppa_min_thumbs', '1');	// Showing thumbs as searchresult has no minimum
	return $result;
}

function wppa_slide_frame() {
global $wppa_master_occur;
	if (is_feed()) {
		if (wppa_page('oneofone')) {
//			wppa_dummy_bar(__('- - - Single photo - - -', 'wppa'));
		}
		else {
//			wppa_dummy_bar(__('- - - Slideshow - - -', 'wppa'));
		}
		return;
	}
?>
	<div id="slide_frame-<?php echo($wppa_master_occur); ?>" class="slide-frame" style="<?php echo(wppa_get_slide_frame_style()); ?>">
		<div id="theslide0-<?php echo($wppa_master_occur); ?>" class="theslide"></div>
		<div id="theslide1-<?php echo($wppa_master_occur); ?>" class="theslide"></div>
		<div id="spinner-<?php echo($wppa_master_occur); ?>" class="spinner"></div>
	</div>
<?php
}

function wppa_startstop($opt = '') {
global $wppa_master_occur;
global $is_slideonly;
	if (is_feed()) {
//		wppa_dummy_bar(__('- - - Start/stop slideshow navigation bar - - -', 'wppa'));
		return;
	}
	if (($opt == 'optional') && (get_option('wppa_show_startstop_navigation', 'yes') == 'no')) return;
	if ($is_slideonly == '1') return;	/* Not when slideonly */
?>
	<div id="prevnext1-<?php echo($wppa_master_occur) ?>" class="wppa-box wppa-nav wppa-nav-text" style="<?php _wcs('wppa-box'); _wcs('wppa-nav'); _wcs('wppa-nav-text'); if (get_option('wppa_hide_slideshow', 'no') == 'yes') echo('display:none; '); ?>">
		<p id="startstoptext-<?php echo($wppa_master_occur) ?>" style="text-align: center; margin:0">
			<a id="speed0-<?php echo($wppa_master_occur) ?>" class="wppa-nav-text speed0" style="<?php _wcs('wppa-nav-text'); ?>" onclick="wppa_speed(<?php echo($wppa_master_occur); ?>, false)"><?php _e('Slower', 'wppa'); ?></a> |
			<a id="startstop-<?php echo($wppa_master_occur) ?>" class="wppa-nav-text startstop" style="<?php _wcs('wppa-nav-text'); ?>" onclick="wppa_startstop(<?php echo($wppa_master_occur) ?>, -1)"><?php _e('Start', 'wppa'); ?></a> |
			<a id="speed1-<?php echo($wppa_master_occur) ?>" class="wppa-nav-text speed1" style="<?php _wcs('wppa-nav-text'); ?>" onclick="wppa_speed(<?php echo($wppa_master_occur); ?>, true)"><?php _e('Faster', 'wppa'); ?></a>
		</p><!-- #startstoptext -->
	</div><!-- #prevnext1 -->
<?php 
}

function wppa_browsebar($opt = '') {
global $wppa_master_occur;
global $is_slideonly;
	if (is_feed()) {
//		wppa_dummy_bar(__('- - - Browse navigation bar - - -', 'wppa'));
		return;
	}
	if (($opt == 'optional') && (get_option('wppa_show_browse_navigation', 'yes') == 'no')) return;
	if ($is_slideonly == '1') return;	/* Not when slideonly */
?>
	<div id="prevnext2-<?php echo($wppa_master_occur) ?>" class="wppa-box wppa-nav wppa-nav-text" style="<?php _wcs('wppa-box'); _wcs('wppa-nav'); _wcs('wppa-nav-text'); ?>">
		<p id="browsetext-<?php echo($wppa_master_occur) ?>" style="text-align: center; margin:0">
			<span id="p-a-<?php echo($wppa_master_occur) ?>" class="wppa-nav-text wppa-black" style="float:left; text-align:left; <?php _wcs('wppa-nav-text'); _wcs('wppa-black'); ?>">&laquo;&nbsp;</span>
			<a id="prev-arrow-<?php echo($wppa_master_occur) ?>" class="wppa-nav-text arrow-<?php echo($wppa_master_occur) ?>" style="float:left; text-align:left; cursor:pointer; <?php _wcs('wppa-nav-text'); ?>" onclick="wppa_prev(<?php echo($wppa_master_occur) ?>)"></a>
			<span id="n-a-<?php echo($wppa_master_occur) ?>" class="wppa-nav-text wppa-black" style="float:right; text-align:right; <?php _wcs('wppa-nav-text'); _wcs('wppa-black'); ?>">&nbsp;&raquo;</span>
			<a id="next-arrow-<?php echo($wppa_master_occur) ?>" class="wppa-nav-text arrow-<?php echo($wppa_master_occur) ?>" style="float:right; text-align:right; cursor:pointer; <?php _wcs('wppa-nav-text'); ?>" onclick="wppa_next(<?php echo($wppa_master_occur) ?>)"></a>
			<span id="counter-<?php echo($wppa_master_occur) ?>" class="wppa-nav-text wppa-black" style="text-align:center; <?php _wcs('wppa-nav-text'); ?>"></span>
		</p><!-- #browsetext -->
	</div><!-- #prevnext2 -->
<?php 
}

function wppa_slide_description($opt = '') {
global $wppa_master_occur;
global $is_slideonly;
	if (($opt == 'optional') && (get_option('wppa_show_full_desc', 'yes') == 'no')) return;
	if ($is_slideonly == '1') return;	/* Not when slideonly */
	echo('<p id="imagedesc-'.$wppa_master_occur.'" class="wppa-fulldesc imagedesc" style="'.__wcs('wppa-fulldesc').'"></p>');
}

function wppa_slide_name($opt = '') {
global $wppa_master_occur;
global $is_slideonly;
	if (($opt == 'optional') && (get_option('wppa_show_full_name', 'yes') == 'no')) return;
	if ($is_slideonly == '1') return;	/* Not when slideonly */
	echo('<p id="imagetitle-'.$wppa_master_occur.'" class="wppa-fulltitle imagetitle" style="'.__wcs('wppa-fulltitle').'"></p>');
}	

function wppa_popup() {
global $wppa_master_occur;
	echo('<div id="wppa-popup-'.$wppa_master_occur.'" class="wppa-popup-frame wppa-thumb-text" style="'.__wcs('wppa-thumb-text').'" ></div>');
	echo('<div style="clear:both;"></div>');
}

function wppa_run_slidecontainer($type = '') {
global $wppa_master_occur;
global $single_photo;
global $is_slideonly;
global $wppa_portrait_only;

	if ($type == 'single') {
		if (is_feed()) {
			echo('<a href="'.get_permalink().'"><img src="'.wppa_get_image_url_by_id($single_photo).'" style="'.wppa_get_fullimgstyle($single_photo).'"/></a>');
			return;
		} else {
			echo('<script type="text/javascript">wppa_store_slideinfo('.wppa_get_slide_info(0, $single_photo).');</script>');
			echo('<script type="text/javascript">wppa_fullvalign_fit['.$wppa_master_occur.'] = true;</script>');
			echo('<script type="text/javascript">wppa_startstop('.$wppa_master_occur.', 0);</script>');
		}
	}
	elseif ($type == 'slideshow') {
		$index = 0;
		$startindex = -1;
		$first = -1;

		if (isset($_GET['photo'])) $startid = $_GET['photo'];	// Still slideshow at photo id $startid
		else {
			if (get_option('wppa_start_slide', 'no') == 'yes' && get_option('wppa_hide_slideshow', 'no') == 'no') {
				$startid = -1;					// Start running
			}
			else $startid = -2;					// Start still at first photo
		}
		if (isset($_GET['album'])) $alb = $_GET['album'];
		else $alb = '';	// Album id is in $startalbum
		$thumbs = wppa_get_thumbs($alb);
		foreach ($thumbs as $tt) : $id = $tt['id'];
			echo '<script type="text/javascript">wppa_store_slideinfo(' . wppa_get_slide_info($index, $id) . ');</script>';
			if ($index == 0) $first = $id;
			if ($startid == -2) $startid = $id;
			if ($startid == $id) $startindex = $index;
			$index++;
		endforeach;
		if ($startindex != -1) $first = $startid;
		
		if ($is_slideonly) $startindex = -1;	// Start running, overrules everything
	
		if (get_option('wppa_fullvalign', 'default') == 'fit' || $is_slideonly == '1') { 
			echo('<script type="text/javascript" >wppa_fullvalign_fit['.$wppa_master_occur.'] = true;</script>');
		}
		if ($wppa_portrait_only) {
			echo('<script type="text/javascript" >wppa_portrait_only['.$wppa_master_occur.'] = true;</script>');
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
global $is_slideonly;
global $thumb;
	if ($opt == 'optional' && get_option('wppa_filmstrip', 'yes') == 'no' && !is_feed()) return;
	
//	if (is_feed()) {
//		wppa_dummy_bar(__('- - - Filmstrip - - -', 'wppa'));
//		return;
//	}
	if ($is_slideonly == '1' && !is_feed()) return;	/* Not when slideonly */

	if (isset($_GET['album'])) $alb = $_GET['album'];
	else $alb = '';	// Album id is in $startalbum
	$thumbs = wppa_get_thumbs($alb);
	if (!$thumbs || count($thumbs) < 1) return;
	
	$preambule = wppa_get_preambule();
		
	$width = (get_option('wppa_tf_width') + get_option('wppa_tn_margin')) * (count($thumbs) + 2 * $preambule);
	$width += get_option('wppa_tn_margin') + 2;
	$topmarg = get_option('wppa_thumbsize') / 2 - 12 + 7;

	$w = wppa_get_container_width() - ( 2*6 + 2*23 + 2*get_option('wppa_bwidth', '1')); /* 2*padding + 2*arrow + 2*border */
	$IE6 = 'width: '.$w.'px;';
	
	if (is_feed()) {
		echo('<div style="'.__wcs('wppa-box').__wcs('wppa-nav').'">');
	} 
	else {
?>
	<div class="wppa-box wppa-nav" style="<?php _wcs('wppa-box'); _wcs('wppa-nav'); ?>height:<?php echo(get_option("wppa_thumbsize") + get_option("wppa_tn_margin")); ?>px;">
		<div style="float:left; text-align:left; cursor:pointer; margin-top:<?php echo($topmarg); ?>px; width: 23px; font-size: 24px;"><a id="prev-film-arrow-<?php echo($wppa_master_occur); ?>" onclick="wppa_prev(<?php echo($wppa_master_occur); ?>);">&laquo;</a></div>
		<div style="float:right; text-align:right; cursor:pointer; margin-top:<?php echo($topmarg); ?>px; width: 23px; font-size: 24px;"><a id="next-film-arrow-<?php echo($wppa_master_occur); ?>" onclick="wppa_next(<?php echo($wppa_master_occur); ?>);">&raquo;</a></div>
		<div class="filmwindow" style="<?php echo($IE6) ?> display: block; height:<?php echo(get_option('wppa_thumbsize') + get_option('wppa_tn_margin')); ?>px; margin: 0 20px 0 20px; overflow:hidden;">
			<div id="wppa-filmstrip-<?php echo($wppa_master_occur); ?>" style="height:<?php echo(get_option("wppa_thumbsize")); ?>px; width:<?php echo($width); ?>px; margin-left: -100px;">
<?php
		}
				$cnt = count($thumbs);
				$start = $cnt - $preambule;
				$end = $cnt;
				$idx = $start;
				while ($idx < $end) {
					$glue = $cnt == ($idx + 1) ? true : false;
					$ix = $idx;
					while ($ix < 0) $ix += $cnt;
					$thumb = $thumbs[$ix];
					wppa_do_filmthumb($ix, false, $glue);
					$idx++;
				}
				$idx = 0;
				foreach ($thumbs as $tt) : $thumb = $tt;
					$glue = $cnt == ($idx + 1) ? true : false;
					wppa_do_filmthumb($idx, true, $glue);
					$idx++;
				endforeach;
				$start = '0';
				$end = $preambule;
				$idx = $start;
				while ($idx < $end) {
					$ix = $idx;
					while ($ix >= $cnt) $ix -= $cnt;
					$thumb = $thumbs[$ix];
					wppa_do_filmthumb($ix, false);
					$idx++;
				}
	if (is_feed()) {
		echo('</div>');
	}
	else {
?>
			</div>
		</div>
	</div>
<?php
	}
}

function wppa_do_filmthumb($idx, $do_for_feed = false, $glue = false) {
global $wppa_master_occur;
global $thumb;
	$src = wppa_get_thumb_path(); 
	$imgattr = wppa_get_imgstyle($src, get_option('wppa_thumbsize'), 'optional', 'thumb'); 
	$url = wppa_get_thumb_url(); 
	$events = wppa_get_imgevents('thumb', $thumb['id'], 'nopopup'); 
	$events .= ' onclick="wppa_goto('.$wppa_master_occur.', '.$idx.')"';
	
	if (is_feed()) {
		if ($do_for_feed) {
?>
			<a href="<?php echo(get_permalink()) ?>"><img src="<?php echo($url); ?>" alt="<?php echo(esc_attr($thumb['name'])); ?>" title="<?php echo(esc_attr($thumb['name'])); ?>" style="<?php echo(wppa_get_imgstyle($src, '100', '4', 'thumb')) ?>"/></a>
<?php
		}
	} else {
	// If !$do_for_feed: pre-or post-ambule. To avoid dup id change it in that case
?>
	<div id="<?php if ($do_for_feed) echo('film'); else echo('pre'); ?>_thumbnail_frame_<?php echo($thumb['id'].'_'.$wppa_master_occur) ?>" class="thumbnail-frame" style="<?php echo(wppa_get_thumb_frame_style($glue, 'film')); ?>" >
		<img src="<?php echo($url); ?>" alt="<?php echo(esc_attr($thumb['name'])); ?>" title="<?php echo(esc_attr($thumb['name'])); ?>" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/>
	</div><!-- #thumbnail_frame_<?php echo($thumb['id'].'_'.$wppa_master_occur) ?> -->
<?php
	}
}

function wppa_get_preambule() {
	$result = is_numeric(get_option('wppa_colwidth', '640')) ? get_option('wppa_colwidth', '640') : get_option('wppa_fullsize', '640');
	$result = ceil(ceil($result / get_option('wppa_thumbsize')) / 2 );
	return $result;
}

function _wcs($class = '') {
	echo(__wcs($class));
}
function __wcs($class = '') {
	$opt = '';
	$result = '';
	switch ($class) {
		case 'wppa-box':
			$opt = get_option('wppa_bwidth', '1');
			if ($opt > '0') $result .= 'border-style: solid; border-width:'.$opt.'px; ';
			$opt = get_option('wppa_bradius', '6');
			if ($opt > '0') {
			/*	$result .= 'border-radius:'.$opt.'px; ';	*/ /* Reserved for css3 */
				$result .= '-moz-border-radius:'.$opt.'px; -khtml-border-radius:'.$opt.'px; -webkit-border-radius:'.$opt.'px; ';
			}
			break;
		case 'wppa-mini-box':
			$opt = get_option('wppa_bwidth', '1');
			if ($opt > '0') {
				$opt = floor(($opt + 2) / 3);
				$result .= 'border-style: solid; border-width:'.$opt.'px; ';
			}
			$opt = get_option('wppa_bradius', '6');
			if ($opt > '0') {
				$opt = floor(($opt + 2) / 3);
			/*	$result .= 'border-radius:'.$opt.'px; ';	*/ /* Reserved for css3 */
				$result .= '-moz-border-radius:'.$opt.'px; -khtml-border-radius:'.$opt.'px; -webkit-border-radius:'.$opt.'px; ';
			}
			break;
		case 'wppa-thumb-text':
			$opt = get_option('wppa_fontfamily_thumb', '');
			if ($opt != '') $result .= 'font-family:'.$opt.'; ';
			$opt = get_option('wppa_fontsize_thumb', '');
			if ($opt != '') {
				$ls = floor($opt * 1.29);
				$result .= 'font-size:'.$opt.'px; line-height:'.$ls.'px; ';
			}
			break;
		case 'wppa-box-text':
			$opt = get_option('wppa_fontfamily_box', '');
			if ($opt != '') $result .= 'font-family:'.$opt.'; ';
			$opt = get_option('wppa_fontsize_box', '');
			if ($opt != '') $result .= 'font-size:'.$opt.'px; ';
			break;
		case 'wppa-nav':
			$opt = get_option('wppa_bgcolor_nav', '#d5eabf');
			if ($opt != '') $result .= 'background-color:'.$opt.'; ';
			$opt = get_option('wppa_bcolor_nav', '#d5eabf');
			if ($opt != '') $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-nav-text':
			$opt = get_option('wppa_fontfamily_nav', '');
			if ($opt != '') $result .= 'font-family:'.$opt.'; ';
			$opt = get_option('wppa_fontsize_nav', '');
			if ($opt != '') $result .= 'font-size:'.$opt.'px; ';
			break;
		case 'wppa-even':
			$opt = get_option('wppa_bgcolor_even', '#e6f2d9');
			if ($opt != '') $result .= 'background-color:'.$opt.'; ';
			$opt = get_option('wppa_bcolor_even', '#e6f2d9');
			if ($opt != '') $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-alt':
			$opt = get_option('wppa_bgcolor_alt', '#d5eabf');
			if ($opt != '') $result .= 'background-color:'.$opt.'; ';
			$opt = get_option('wppa_bcolor_alt', '#d5eabf');
			if ($opt != '') $result .= 'border-color:'.$opt.'; ';
			break;
		case 'wppa-img':
			$opt = get_option('wppa_bgcolor_img', '#eef7e6');
			if ($opt != '') $result .= 'background-color:'.$opt.'; ';
			break;
		case 'wppa-title':
			$opt = get_option('wppa_fontfamily_title', '');
			if ($opt != '') $result .= 'font-family:'.$opt.'; ';
			$opt = get_option('wppa_fontsize_title', '');
			if ($opt != '') $result .= 'font-size:'.$opt.'px; ';
			break;
		case 'wppa-fulldesc':
			$opt = get_option('wppa_fontfamily_fulldesc', '');
			if ($opt != '') $result .= 'font-family:'.$opt.'; ';
			$opt = get_option('wppa_fontsize_fulldesc', '');
			if ($opt != '') $result .= 'font-size:'.$opt.'px; ';
			break;
		case 'wppa-fulltitle':
			$opt = get_option('wppa_fontfamily_fulltitle', '');
			if ($opt != '') $result .= 'font-family:'.$opt.'; ';
			$opt = get_option('wppa_fontsize_fulltitle', '');
			if ($opt != '') $result .= 'font-size:'.$opt.'px; ';
			break;
		case 'wppa-black':
			$opt = get_option('wppa_black', 'black');
			if ($opt != '') $result .= 'color:'.$opt.'; ';
			break;
		case 'wppa-widget':
			$opt = get_option('wppa_widget_padding', '5');
			if ($opt != '') $result .= 'padding-top:'.$opt.'px; padding-left:'.$opt.'px; ';
			break;
	}
	return $result;
}

function wppa_dummy_bar($msg = '') {
?>
	<div style="margin:4px 0; <?php _wcs('wppa-box'); _wcs('wppa-nav'); ?>text-align:center;"><?php echo($msg); ?></div>
<?php
}

function wppa_is_wider($x, $y) {
	$ratioref = get_option('wppa_fullsize', '640') / get_option('wppa_maxheight', get_option('wppa_fullsize', '640'));
	$ratio = $x / $y;
	return ($ratio > $ratioref);
}

function wppa_slide_rating($opt = '') {
global $wppa_master_occur;
global $is_slideonly;
	if ($opt == 'optional' && get_option('wppa_rating_on', 'yes') == 'no') return;
	if ($is_slideonly == '1') return;	/* Not when slideonly */
	if (is_feed()) {
		wppa_dummy_bar(__('- - - Rating enabled - - -', 'wppa'));
		return;
	}
	$fs = get_option('wppa_fontsize_nav', '12');	
	$dh = $fs + '6';
	$size = 'font-size:'.$fs.'px;';
?>	
	<div id="wppa-rating-<?php echo($wppa_master_occur) ?>" class="wppa-box wppa-nav wppa-nav-text" style="<?php _wcs('wppa-box'); _wcs('wppa-nav'); _wcs('wppa-nav-text'); echo($size) ?> text-align:center;">
<?php
	$r['1'] = __('very low', 'wppa');
	$r['2'] = __('low', 'wppa');
	$r['3'] = __('average', 'wppa');
	$r['4'] = __('high', 'wppa');
	$r['5'] = __('very high', 'wppa');

	if ($fs != '') $fs += 3; else $fs = '15';	// iconsize = fontsize+3, Default to 15
	$size = 'style="height:'.$fs.'px; margin-bottom:-3px;"';

	_e('Average&nbsp;rating', 'wppa');
	echo('&nbsp;');
	
	$icon = 'star.png';
	$i = '1';
	while ($i < '6') {
		echo('<img id="wppa-avg-'.$wppa_master_occur.'-'.$i.'" class="wppa-avg-'.$wppa_master_occur.' no-shadow" '.$size.' src="'.wppa_get_imgdir().$icon.'" alt="'.$i.'" title="'.__('Avarage&nbsp;rating:', 'wppa').' '.$r[$i].'" />');
		$i++;
	}
	
	echo('&nbsp;&nbsp;');

	if (get_option('wppa_rating_login', 'yes') == 'no' || is_user_logged_in()) {
		$i = '1';
		while ($i < '6') {
			echo('<img id="wppa-rate-'.$wppa_master_occur.'-'.$i.'" class="wppa-rate-'.$wppa_master_occur.' no-shadow" '.$size.' src="'.wppa_get_imgdir().$icon.'" alt="'.$i.'" title="'.__('My&nbsp;rating:', 'wppa').' '.$r[$i].'" onmouseover="wppa_follow_me('.$wppa_master_occur.', '.$i.')" onmouseout="wppa_leave_me('.$wppa_master_occur.', '.$i.')" onclick="wppa_rate_it('.$wppa_master_occur.', '.$i.')" />');
			$i++;
		}
		echo('&nbsp;');
		_e('My&nbsp;rating', 'wppa');
	}
	else {
		_e('You must login to vote', 'wppa');
	}
?>
	</div>
<?php
}

function wppa_rating_count_by_id($id = '') {
	echo(wppa_get_rating_count_by_id($id));
}
function wppa_get_rating_count_by_id($id = '') {
global $wpdb;
	if (!is_numeric($id)) return '';
	$query = 'SELECT * FROM '.WPPA_RATING.' WHERE photo = '.$id;
	$ratings = $wpdb->get_results($query, 'ARRAY_A');
	if ($ratings) return count($ratings);
	else return '0';
}

function wppa_rating_by_id($id = '', $opt = '') {
	echo(wppa_get_rating_by_id($id, $opt));
}
function wppa_get_rating_by_id($id = '', $opt = '') {
global $wpdb;
	$result = '';
	if (is_numeric($id)) {
		$rating = $wpdb->get_var("SELECT mean_rating FROM ".PHOTO_TABLE." WHERE id=$id");
		if ($rating) {
			if ($opt == 'nolabel') $result = round($rating * 1000) / 1000;
			else $result = sprintf(__('Rating: %s', 'wppa'), round($rating * 1000) / 1000);
		}
	}
	return $result;
}

function wppa_get_cover_width($type) {
	$conwidth = wppa_get_container_width();
	$cols = wppa_get_cover_cols($type);
	
	switch ($cols) {
		case '1':
			$result = $conwidth;
			break;
		case '2':
			$result = floor(($conwidth - 8) / 2);
			break;
		case '3':
			$result = floor(($conwidth - 16) / 3);
			break;
		
	}
	$result -= (2 * (7 + get_option('wppa_bwidth', '1')));	// 2 * (padding + border)
	return $result;
}

function wppa_get_text_frame_style($photo_left, $type) {
	$width = wppa_get_cover_width($type); // - wppa_get_textframe_delta($type);
	$width -= get_option('wppa_smallsize', '100');
	$width -= 13;	// margin
	
	if ($photo_left) {
		$result = 'style="width:'.$width.'px; float:right;"';
	}
	else {
		$result = 'style="width:'.$width.'px; float:left;"';// position:absolute;"';
	}
	return $result;
}

function wppa_get_textframe_delta() {
	$delta = get_option('wppa_smallsize', '100');
	$delta += (2 * (7 + get_option('wppa_bwidth', '1') + 4) + 5);	// 2 * (padding + border + photopadding) + margin
	return $delta;
}

function wppa_step_covercount($type) {
global $cover_count;

	$cols = wppa_get_cover_cols($type);
	switch ($cols) {
		case 1:
		break;
		case 2:
			$cover_count++;
			if ($cover_count == '2') $cover_count = '0';
		break;
		case 3:
			$cover_count++;
			if ($cover_count == '3') $cover_count = '0';
			break;
	}
}

function wppa_get_cover_cols($type) {
global $wppa_auto_colwidth;
global $wppa_album_count;
global $wppa_thumb_count;
	$conwidth = wppa_get_container_width();
	$cols = '1';
	if ($conwidth >= get_option('wppa_2col_treshold', '640')) $cols = '2';
	if ($conwidth >= get_option('wppa_3col_treshold', '800')) $cols = '3';
	
	if ($wppa_auto_colwidth) $cols = '1';
	if (($type == 'cover') && ($wppa_album_count < '2')) $cols = '1';
	if (($type == 'thumb') && ($wppa_thumb_count < '2')) $cols = '1';
	return $cols;
}

function wppa_get_box_width() {
	$result = wppa_get_container_width();
	$result -= 14;	// 2 * padding
	$result -= 2 * get_option('wppa_bwidth', '1');
	return $result;
}

function wppa_get_box_delta() {
	return wppa_get_container_width() - wppa_get_box_width();
}