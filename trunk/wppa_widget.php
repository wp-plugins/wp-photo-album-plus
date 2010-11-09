<?php
/* wppa_widget.php
* Package: wp-photo-album-plus
*
* display the widget
* Version 2.4.0
*/

add_action('plugins_loaded', 'init_wppa_widget');

function init_wppa_widget() {
	wp_register_sidebar_widget('wppa-widget', __('Photo Album Widget', 'wppa'), 'show_wppa_widget');
}

function show_wppa_widget($args) {
	global $wpdb;
	extract($args);
	
	// get the title
	$widget_title = get_option('wppa_widgettitle', __('Photo of the day', 'wppa'));
			
	// get the photo  ($image)
	switch (get_option('wppa_widget_method', '1')) {
		case '1':	// Fixed photo
			$id = get_option('wppa_widget_photo', '');
			if ($id != '') {
				$image = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . PHOTO_TABLE . '` WHERE `id` = %d LIMIT 0,1', $id), 'ARRAY_A');
			}
			break;
		case '2':	// Random
			$album = get_option('wppa_widget_album', '');
			if ($album != '') {
				$image = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . PHOTO_TABLE . '` WHERE `album` = %d ORDER BY RAND() LIMIT 0,1', $album), 'ARRAY_A');
			}
			break;
		case '3':	// Last upload
			$album = get_option('wppa_widget_album', '');
			if ($album != '') {
				$image = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . PHOTO_TABLE . '` WHERE `album` = %d ORDER BY `id` DESC LIMIT 0,1', $album), 'ARRAY_A');
			}
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
				$photos = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . PHOTO_TABLE . '` WHERE `album` = %d ' . wppa_get_photo_order($album), $album), 'ARRAY_A');
				$i = 0;
				foreach ($photos as $photo) {
					if ($i == $idn) {	// found the idn'th out of p
						$image = $photo;
					}
					$i++;
				}
			} else {
				$image = '';
			}
			break;
		case '5':	// Slideshow
				$widget_content = __('Not implemented yet (5)');
				$image = '';
			break;
		case '6':	// Scrollable
				$widget_content = __('Not implemented yet (6)');
				$image = '';
			break;	
	}
	
	// Make the HTML for current picture
	$widget_content = '<div class="wppa-widget">';
	if ($image) {
		// make image url
		$imgurl = get_bloginfo('wpurl') . '/wp-content/uploads/wppa/' . $image['id'] . '.' . $image['ext'];
		
		// Find link page if any, if we find a title, there is a valid page to link to
		$pid = get_option('wppa_widget_linkpage', '0');
		$page_title = $wpdb->get_var("SELECT post_title FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish' AND ID=" . $pid);
		if ($page_title) { 			// Yep, Linkpage found
			$title = __('Link to', 'wppa') . ' ' . $page_title;
			$widget_content .= '<a href="' . get_page_link($pid) . wppa_sep() . 'album=' . $album . '&cover=0&occur=1">';
		} 
		else {
			$title = $widget_title;
		}
		
		$widget_content .= '<img src="' . $imgurl . '" style="width: ' . get_option('wppa_widget_width', '150') . 'px;" title="' . $title . '" alt="' . $title . '">';

		if ($page_title) $widget_content .= '</a>';
	} 
	else {	// No image
		$widget_content .= __('Photo not found.');
	}
	$widget_content .= '</div>';
	// Add subtitle, if any		
	switch (get_option('wppa_widget_subtitle', 'none'))
	{
		case 'none': 
			break;
		case 'name': 
			if ($image && $image['name'] != '') {
				$widget_content .= '<div class="wppa-widget-text">' . stripslashes($image['name']) . '</div>';
			}
			break;
		case 'desc': 
			if ($image && $image['description'] != '') {
				$widget_content .= '<div class="wppa-widget-text">' . stripslashes($image['description']) . '</div>'; 
			}
			break;
	}
	// Display the widget
	echo $before_widget . $before_title . $widget_title . $after_title . $widget_content . $after_widget;
	// Set padding
	wppa_set_runtimestyle();
}