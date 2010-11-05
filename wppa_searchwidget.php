<?php
/* wppa_searchwidget.php
* Package: wp-photo-album-plus
*
* display the search widget
* Version 2.3.2
*/

add_action('plugins_loaded', 'init_wppa_searchwidget');

function init_wppa_searchwidget() {
	wp_register_sidebar_widget('wppa-searchwidget', __('Photo Search', 'wppa'), 'show_wppa_searchwidget');
}

function show_wppa_searchwidget($args) {
	global $wpdb;
	extract($args);
	
	// get the title
	$widget_title = get_option('wppa_searchwidgettitle', __('Search photos', 'wppa'));
		
	// Display the widget
	echo $before_widget . $before_title . $widget_title . $after_title;
	$page = get_option('wppa_search_linkpage', '0');
	if ($page == '0') {
		_e('Warning. No page defined for search results!', 'wppa');
	}
	else {
	$pagelink = get_page_link($page);
?>
		<form id="searchform" action="<?php echo($pagelink) ?>" method="post">
			<div>
				<input type="text" name="wppa-searchstring" id="s" value="<?php echo($wppa_searchstring) ?>" />
				<input id = "searchsubmit" type="submit" value="<?php _e('Search', 'wppa'); ?>" />
			</div>
		</form>
<?php
	}
	echo $after_widget;
}
?>