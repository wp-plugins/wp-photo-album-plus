<?php
/* wppa-tinymce-shortcodes.php
* Pachkage: wp-photo-album-plus
*
*
* Version 5.4.9
*
*/

if ( ! defined( 'WPPA_ABSPATH' ) )
    die( "Can't load this file directly" );
 
class wppaGallery
{
    function __construct() {
		add_action( 'init', array( $this, 'action_admin_init' ) ); // 'admin_init'
	}
	 
	function action_admin_init() {
		// only hook up these filters if we're in the admin panel, and the current user has permission
		// to edit posts or pages
		if ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) {
			add_filter( 'mce_buttons', array( $this, 'filter_mce_button' ) );
			add_filter( 'mce_external_plugins', array( $this, 'filter_mce_plugin' ) );	
		}
	}
	 
	function filter_mce_button( $buttons ) {
		// add a separation before our button.
		array_push( $buttons, '|', 'mygallery_button' );
		return $buttons;
	}
	 
	function filter_mce_plugin( $plugins ) {
		// this plugin file will work the magic of our button
		if ( wppa_switch( 'wppa_use_scripts_in_tinymce' ) ) {
			$file = 'wppa-tinymce-scripts.js';
		}
		else {
			$file = 'wppa-tinymce-shortcodes.js';
		}
		$plugins['wppagallery'] = plugin_dir_url( __FILE__ ) . $file;
		return $plugins;
	}
 
}
 
$wppagallery = new wppaGallery();

add_action('admin_head', 'wppa_inject_js');

function wppa_inject_js() {
global $wppa_api_version;

	// Things that wppa-tinymce.js AND OTHER MODULES!!! need to know
	echo('<script type="text/javascript">'."\n");
	echo('/* <![CDATA[ */'."\n");
		echo("\t".'wppaImageDirectory = "'.wppa_get_imgdir().'";'."\n");
		echo("\t".'wppaAjaxUrl = "'.admin_url('admin-ajax.php').'";'."\n");
		echo("\t".'wppaPhotoDirectory = "'.WPPA_UPLOAD_URL.'/";'."\n");
		echo("\t".'wppaThumbDirectory = "'.WPPA_UPLOAD_URL.'/thumbs/";'."\n");
		echo("\t".'wppaTempDirectory = "'.WPPA_UPLOAD_URL.'/temp/";'."\n");
		echo("\t".'wppaFontDirectory = "'.WPPA_UPLOAD_URL.'/fonts/";'."\n");
		echo("\t".'wppaNoPreview = "'.__('No Preview available', 'wppa').'";'."\n");
		echo("\t".'wppaVersion = "'.$wppa_api_version.'";'."\n");
		echo("\t".'wppaSiteUrl = "'.site_url().'";'."\n");
		echo("\t".'wppaWppaUrl = "'.WPPA_URL.'";'."\n");
		echo("\t".'wppaIncludeUrl = "'.trim(includes_url(), '/').'";'."\n");
	echo("/* ]]> */\n");
	echo("</script>\n");
}

function wppa_make_tinymce_dialog() {
global $wpdb;
global $wppa_opt;

	// Prepare albuminfo
	$albums = $wpdb->get_results( "SELECT `id`, `name` FROM `".WPPA_ALBUMS."` ORDER BY `timestamp` DESC", ARRAY_A );
	if ( wppa_switch( 'wppa_hier_albsel' ) ) {
		$albums = wppa_add_paths( $albums );
	}
	else {
		foreach ( array_keys( $albums ) as $index ) $albums[$index]['name'] = __( stripslashes( $albums[$index]['name'] ) );
	}
	$albums = wppa_array_sort( $albums, 'name' );
	
	// Prepare photoinfo
	$photos = $wpdb->get_results( "SELECT `id`, `name`, `album`, `ext` FROM `".WPPA_PHOTOS."` ORDER BY `timestamp` DESC LIMIT 100", ARRAY_A );
	
	// Get Tags/cats
	$tags 	= wppa_get_taglist();
	$cats 	= wppa_get_catlist();

	// Make the html
	$result = 
	'<div id="wppagallery-form">'.
		'<style type="text/css">'.
			'#wppagallery-table tr, #wppagallery-table th, #wppagallery-table td {'.
				'padding: 2px; 0;'.
			'}'.
		'</style>'.
		'<table id="wppagallery-table" class="form-table">'.
		
			// Top type selection
			'<tr >'.
				'<th><label for="wppagallery-top-type">'.__('Type of WPPA display:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-top-type" name="type" onchange="wppaGalleryEvaluate()">'.
						'<option value="" selected="selected" disabled="disabled" style="color:#700" >-- '.__('Please select a display type', 'wppa').' --</option>'.
						'<option value="galerytype" style="color:#070" >'.__('A gallery with covers and/or thumbnails', 'wppa').'</option>'.
						'<option value="slidestype" style="color:#070" >'.__('A slideshow', 'wppa').'</option>'.
						'<option value="singletype" style="color:#070" >'.__('A single image', 'wppa').'</option>'.
						'<option value="searchtype" style="color:#070" >'.__('A search/selection box', 'wppa').'</option>'.
						'<option value="misceltype" style="color:#070" >'.__('An other box type', 'wppa').'</option>'.
					'</select>'.
				'</td>'.
			'</tr>'.
						
			// Top type I: gallery sub type
			'<tr id="wppagallery-galery-type-tr" style="display:none;" >'.
				'<th><label for="wppagallery-galery-type">'.__('Type of gallery display:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-galery-type" name="type" onchange="wppaGalleryEvaluate()">'.
						'<option value="" selected="selected" disabled="disabled" style="color:#700" >-- '.__('Please select a gallery type', 'wppa').' --</option>'.
						'<option value="cover" style="color:#070" >'.__('The cover(s) of specific album(s)', 'wppa').'</option>'.
						'<option value="content" style="color:#070" >'.__('The content of specific album(s)', 'wppa').'</option>'.
						'<option value="covers" style="color:#070" >'.__('The covers of the subalbums of specific album(s)', 'wppa').'</option>'.
						'<option value="thumbs" style="color:#070" >'.__('The thumbnails of specific album(s)', 'wppa').'</option>'.
					'</select>'.
				'</td>'.
			'</tr>'.
			
			// Top type II: slide sub type
			'<tr id="wppagallery-slides-type-tr" style="display:none;" >'.
				'<th><label for="wppagallery-slides-type">'.__('Type of slideshow:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-slides-type" name="type" onchange="wppaGalleryEvaluate()">'.
						'<option value="" selected="selected" disabled="disabled" style="color:#700" >-- '.__('Please select a slideshow type', 'wppa').' --</option>'.					
						'<option value="slide" style="color:#070" >'.__('A fully featured slideshow', 'wppa').'</option>'.
						'<option value="slideonly" style="color:#070" >'.__('A slideshow without supporting boxes', 'wppa').'</option>'.
						'<option value="slideonlyf" style="color:#070" >'.__('A slideshow with a filmstrip only', 'wppa').'</option>'.
						'<option value="filmonly" style="color:#070" >'.__('A filmstrip only', 'wppa').'</option>'.
					'</select>'.
				'</td>'.
			'</tr>'.

			// Top type III: single sub type
			'<tr id="wppagallery-single-type-tr" style="display:none;" >'.
				'<th><label for="wppagallery-single-type">'.__('Type of single image:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-single-type" name="type" onchange="wppaGalleryEvaluate()">'.
						'<option value="" selected="selected" disabled="disabled" style="color:#700" >-- '.__('Please select a single image type', 'wppa').' --</option>'.					
						'<option value="photo" style="color:#070" >'.__('A plain single photo', 'wppa').'</option>'.
						'<option value="mphoto" style="color:#070" >'.__('A single photo with caption', 'wppa').'</option>'.
						'<option value="slphoto" style="color:#070" >'.__('A single photo in the style of a slideshow', 'wppa').'</option>'.
					'</select>'.
				'</td>'.
			'</tr>'.

			// Top type IV: search sub type
			'<tr id="wppagallery-search-type-tr" style="display:none;" >'.
				'<th><label for="wppagallery-search-type">'.__('Type of search:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-search-type" name="type" onchange="wppaGalleryEvaluate()">'.
						'<option value="" selected="selected" disabled="disabled" style="color:#700" >-- '.__('Please select a search type', 'wppa').' --</option>'.
						'<option value="search" style="color:#070" >'.__('A search box', 'wppa').'</option>'.
						'<option value="tagcloud" style="color:#070" >'.__('A tagcloud box', 'wppa').'</option>'.
						'<option value="multitag" style="color:#070" >'.__('A multitag box', 'wppa').'</option>'.
						'<option value="superview" style="color:#070" >'.__('A superview box', 'wppa').'</option>'.
					'</select>'.
				'</td>'.
			'</tr>'.
			
			// Top type V: other sub type
			'<tr id="wppagallery-miscel-type-tr" style="display:none;" >'.
				'<th><label for="wppagallery-miscel-type">'.__('Type miscellaneous:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-miscel-type" name="type" onchange="wppaGalleryEvaluate()">'.
						'<option value="" selected="selected" disabled="disabled" style="color:#700" >-- '.__('Please select a miscellaneous display', 'wppa').' --</option>'.					
						'<option value="generic">'.__('A generic albums display', 'wppa').'</option>'.
						'<option value="upload">'.__('An upload box', 'wppa').'</option>'.
						'<option value="landing">'.__('A landing page shortcode', 'wppa').'</option>'.
					'</select>'.
				'</td>'.
			'</tr>'.

			// Real or Virtual albums
			'<tr id="wppagallery-album-type-tr" style="display:none;" >'.
				'<th><label for="wppagallery-album-type">'.__('Kind of selection:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-album-type" name="type" onchange="wppaGalleryEvaluate()">'.
						'<option value="" selected="selected" disabled="disabled" style="color:#700" >-- '.__('Please select a type of selection to be used', 'wppa').' --</option>'.
						'<option value="real">'.__('One or more wppa+ albums', 'wppa').'</option>'.
						'<option value="virtual">'.__('A special selection', 'wppa').'</option>'.
					'</select>'.
				'</td>'.
			'</tr>'.
			
			// Real albums
			'<tr id="wppagallery-album-real-tr" style="display:none;" >'.
				'<th><label for="wppagallery-album-real">'.__('The Album(s) to be used:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-album-real" style="max-width:400px;" name="album" multiple="multiple" onchange="wppaGalleryEvaluate()">';
						if ( $albums ) {

							// Please select
							$result .= '<option id="wppagallery-album-0" value="0" disabled="disabled" selected="selected" style="color:#700" >-- '.__('Please select one or more albums', 'wppa').' --</option>';

							// All standard albums
							foreach ( $albums as $album ) {
								$id = $album['id'];
								$result .= '<option class="wppagallery-album" value="' . $id . '" >'.stripslashes( __( $album['name'] ) ) . ' (' . $id . ')</option>';
							}
						}
						else {
							$result .= '<option value="0" >' . __('There are no albums yet', 'wppa') . '</option>';
						}
					$result .= '</select>'.
				'</td>'.
			'</tr>'.
			
			// Virtual albums
			'<tr id="wppagallery-album-virt-tr" style="display:none;" >'.
				'<th><label for="wppagallery-album-virt">'.__('The selection to be used:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-album-virt" name="album" class="wppagallery-album" onchange="wppaGalleryEvaluate()">'.
						'<option value="" disabled="disabled" selected="selected" style="color:#700" >-- '.__('Please select a virtual album', 'wppa').' --</option>'.
						'<option value="#last" >'.__('The most recently modified album', 'wppa').'</option>'.
						'<option value="#topten" >'.__('The top rated photos', 'wppa').'</option>'.
						'<option value="#lasten" >'.__('The most recently uploaded photos', 'wppa').'</option>'.
						'<option value="#featen" >'.__('A random selection of featured photos', 'wppa').'</option>'.
						'<option value="#comten" >'.__('The most recently commented photos', 'wppa').'</option>'.
						'<option value="#tags" >'.__('Photos that have certain tags', 'wppa').'</option>'.
						'<option value="#cat" >'.__('Albums tagged with a certain category', 'wppa').'</option>'.
						'<option value="#owner" >'.__('Photos in albums owned by a certain user', 'wppa').'</option>'.
						'<option value="#upldr" >'.__('Photos uploaded by a certain user', 'wppa').'</option>'.
						'<option value="#all" >'.__('All photos in the system', 'wppa').'</option>'.
					'</select>'.
				'</td>'.
			'</tr>'.
			
			// Virtual albums that have covers
			'<tr id="wppagallery-album-virt-cover-tr" style="display:none;" >'.
				'<th><label for="wppagallery-album-virt-cover">'.__('The selection to be used:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-album-virt-cover" name="album" class="wppagallery-album" onchange="wppaGalleryEvaluate()">'.
						'<option value="" disabled="disabled" selected="selected" style="color:#700" >-- '.__('Please select a virtual album', 'wppa').' --</option>'.
						'<option value="#last" >'.__('The most recently modified album', 'wppa').'</option>'.
						'<option value="#owner" >'.__('Albums owned by a certain user', 'wppa').'</option>'.
						'<option value="#cat" >'.__('Albums tagged with a certain category', 'wppa').'</option>'.
						'<option value="#all" >'.__('All albums in the system', 'wppa').'</option>'.
					'</select>'.
				'</td>'.
			'</tr>'.
			
			// Owner selction
			'<tr id="wppagallery-owner-tr" style="display:none" >'.
				'<th><label for="wppagallery-owner">'.__('The album owner:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-owner" name="owner" class="wppagallery-owner" onchange="wppaGalleryEvaluate()">'.
						'<option value="" disabled="disabled" selected="selected" style="color:#700" >-- '.__('Please select a user', 'wppa').' --</option>'.
						'<option value="#me" >-- '.__('The logged in visitor', 'wppa').' --</option>';
						$users = wppa_get_users();
						if ( $users ) foreach ( $users as $user ) {
							$result .= '<option value="'.$user['user_login'].'" >'.$user['display_name'].'</option>';
						}
						else {	// Too many
							$result .= '<option value="xxx" >-- '.__('Too many users, edit manually', 'wppa').' --</option>';
						}
					$result .=
					'</select>'.
				'</td>'.
			'</tr>'.

			// Owner Parent album
			'<tr id="wppagallery-owner-parent-tr" style="display:none;" >'.
				'<th><label for="wppagallery-owner-parent">'.__('Parent album:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-owner-parent" style="color:#070;max-width:400px;" name="parentalbum" multiple="multiple" onchange="wppaGalleryEvaluate()">';
						if ( $albums ) {
						
							// Please select
							$result .= '<option value="" selected="selected" >-- '.__('No parent specification', 'wppa').' --</option>';
							
							// Generic
							$result .= '<option value="0" >-- '.__('The generic parent', 'wppa').' --</option>';

							// All standard albums
							foreach ( $albums as $album ) {
								$id = $album['id'];
								$result .= '<option class="wppagallery-album" value="'.$id.'" >'.stripslashes(__($album['name'])).' ('.$id.')</option>';
							}
						}
						else {
							$result .= '<option value="0" >'.__('There are no albums yet', 'wppa').'</option>';
						}
					$result .= '</select>'.
				'</td>'.				
			'</tr>'.
			
			// Album parent
			'<tr id="wppagallery-album-parent-tr" style="display:none;" >'.
				'<th><label for="wppagallery-album-parent">'.__('Parent album:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-album-parent-parent" style="color:#070;max-width:400px;" name="parentalbum" onchange="wppaGalleryEvaluate()">';
						if ($albums) {

							// Please select
							$result .= '<option id="wppagallery-album-0" value="0" selected="selected" style="color:#700" >-- '.__('The generic parent', 'wppa').' --</option>';

							// All standard albums
							foreach ( $albums as $album ) {
								$id = $album['id'];
								$result .= '<option class="wppagallery-album" value="'.$id.'" >'.stripslashes(__($album['name'])).' ('.$id.')</option>';
							}
						}
						else {
							$result .= '<option value="0" >'.__('There are no albums yet', 'wppa').'</option>';
						}
					$result .= '</select>'.
				'</td>'.				
			'</tr>'.

			// Album count
			'<tr id="wppagallery-album-count-tr" style="display:none;" >'.
				'<th><label for="wppagallery-album-count">'.__('Max Albums:', 'wppa').'</label></th>'.
				'<td>'.
					'<input id="wppagallery-album-count" type="text" style="color:#070;" value="1" onchange="wppaGalleryEvaluate()" />'.
				'</td>'.
			'</tr>'.

			// Photo count
			'<tr id="wppagallery-photo-count-tr" style="display:none;" >'.
				'<th><label for="wppagallery-photo-count">'.__('Max Photos:', 'wppa').'</label></th>'.
				'<td>'.
					'<input id="wppagallery-photo-count" type="text" style="color:#070;" value="1" onchange="wppaGalleryEvaluate()" />'.
				'</td>'.
			'</tr>'.

			// Albums with certain cat
			'<tr id="wppagallery-albumcat-tr" style="display:none;" >'.
				'<th><label for="wppagallery-albumcat">'.__('The cat the albums should have:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-albumcat" style="color:#700;" onchange="wppaGalleryEvaluate()">'.
						'<option value="" disabled="disabled" selected="selected" style="color:#700" >'.__('--- please select category ---', 'wppa').'</option>';
						if ( $cats ) foreach ( array_keys( $cats ) as $cat ) {
							$result .= '<option value="'.$cat.'" >'.$cat.'</option>';
						}
						$result .= 					
					'</select>'.
				'</td>'.
			'</tr>'.

			// Photo selection
			'<tr id="wppagallery-photo-tr" style="display:none;" >'.
				'<th><label for="wppagallery-photo" class="wppagallery-photo" >'.__('The Photo to be used:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-photo" name="photo" class="wppagallery-photo" onchange="wppaGalleryEvaluate()" >';
						if ( $photos ) {
							
							// Please select
							$result .= '<option value="" disabled="disabled" selected="selected" style="color:#700" >-- '.__('Please select a photo', 'wppa').' --</option>';
							
							// Most recent 100 photos
							foreach ( $photos as $photo ) {
								$name = stripslashes(__($photo['name']));
								if ( strlen($name) > '50') $name = substr($name, '0', '50').'...';
								if ( get_option( 'wppa_file_system' ) == 'flat' ) {
									$result .= '<option value="'.$photo['id'].'.'.$photo['ext'].'" >'.$name.' ('.wppa_get_album_name($photo['album']).')'.'</option>';
								}
								else {
									$result .= '<option value="'.wppa_expand_id($photo['id']).'.'.$photo['ext'].'" >'.$name.' ('.wppa_get_album_name($photo['album']).')'.'</option>';
								}
							}
							$result .=  '<option value="#last" >-- '.__('The most recently uploaded photo', 'wppa').' --</option>'.
										'<option value="#potd" >-- '.__('The photo of the day', 'wppa').' --</option>';
						}
						else {
							$result .= '<option value="0" >'.__('There are no photos yet', 'wppa').'</option>';
						}
						$result .=
					'</select>'.
					'<br />'.
					'<small style="display:none;" class="wppagallery-photo" >'.
						__('Specify the photo to be used', 'wppa').'<br />'.
						__('You can select from a maximum of 100 most recently added photos', 'wppa').'<br />'.
					'</small>'.
				'</td>'.
			'</tr>'.
			
			// Photo preview
			'<tr id="wppagallery-photo-preview-tr" style="display:none;" >'.
				'<th><label for="wppagallery-photo-preview" >'.__('Preview image:', 'wppa').'</label></th>'.
				'<td id="wppagallery-photo-preview" style="text-align:center;" >'.
				'</td >'.
			'</tr>'.

			// Photos with certain tags
			'<tr id="wppagallery-phototags-tr" style="display:none;" >'.
				'<th><label for="wppagallery-phototags">'.__('The tags the photos should have:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-phototags" style="color:#700;" multiple="multiple" onchange="wppaGalleryEvaluate()">'.
						'<option value="" disabled="disabled" selected="selected" style="color:#700" >'.__('--- please select tag(s) ---', 'wppa').'</option>';
						if ( $tags ) foreach ( array_keys($tags) as $tag ) {
							$result .= '<option class="wppagallery-phototags" value="'.$tag.'" >'.$tag.'</option>';
						}
						$result .= 					
					'</select>'.
				'</td>'.
			'</tr>'.
			
			// Search additional settings
			'<tr id="wppagallery-search-tr" style="display:none;" >'.
				'<th><label>'.__('Additional features:', 'wppa').'</label></th>'.
				'<td>'.
					'<input id="wppagallery-sub" type="checkbox" name="sub" onchange="wppaGalleryEvaluate()"/>'.__('Enable Subsearch', 'wppa').'&nbsp;'.
					'<input id="wppagallery-root" type="checkbox" name="root" onchange="wppaGalleryEvaluate()"/>'.__('Enable Rootsearch', 'wppa').
				'</td>'.
			'</tr>'.
			
			// Tagcloud/list additional settings
			'<tr id="wppagallery-taglist-tr" style="display:none;" >'.
				'<th><label>'.__('Additional features:', 'wppa').'</label></th>'.
				'<td>'.
					'<input id="wppagallery-alltags" type="checkbox" checked="checked" name="alltags" onchange="wppaGalleryEvaluate()"/>'.__('Enable all tags', 'wppa').'&nbsp;'.
					'<select id="wppagallery-seltags" style="color:#070; display:none;" name="seltags" multiple="multiple" onchange="wppaGalleryEvaluate()">';
						if ( $tags ) {
							'<option value="" disabled="disabled" selected="selected" style="color:#700" >-- '.__('Please select the tags to show', 'wppa').' --</option>';
							foreach( array_keys($tags) as $tag ) {
								$result .= '<option class="wppagallery-taglist-tags" value="'.$tag.'"style="color:#700" >'.$tag.'</option>';
							}
						}
						else {
							'<option value="" disabled="disabled" selected="selected" style="color:#700" >-- '.__('There are no tags', 'wppa').' --</option>';
						}
						$result .= '</select>'.
				'</td>'.
			'</tr>'.
			
			// Superview additional settings: optional parent
			'<tr id="wppagallery-album-super-tr" style="display:none;" >'.
				'<th><label for="wppagallery-album-super">'.__('Parent album:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-album-super-parent" style="color:#070;max-width:400px;" name="parentalbum" onchange="wppaGalleryEvaluate()">';
						if ( $albums ) {

							// Please select
							$result .= '<option value="" selected="selected" style="color:#700" >-- '.__('The generic parent', 'wppa').' --</option>';

							// All standard albums
							foreach ( $albums as $album ) {
								$id = $album['id'];
								$result .= '<option class="wppagallery-album" value="'.$id.'" >'.stripslashes(__($album['name'])).' ('.$id.')</option>';
							}
						}
						else {
							$result .= '<option value="0" >'.__('There are no albums yet', 'wppa').'</option>';
						}
					$result .= '</select>'.
				'</td>'.				
			'</tr>'.

			// Size
			'<tr>'.
				'<th><label for="wppagallery-size">'.__('The size of the display:', 'wppa').'</label></th>'.
				'<td>'.
					'<input type="text" id="wppagallery-size" value="" style="color:#070;" onchange="wppaGalleryEvaluate();"/>'.
					'<br />'.
					'<small>'.
						__('Specify the horizontal size in pixels or <span style="color:blue" >auto</span>.', 'wppa').' '.
						__('A value less than <span style="color:blue" >100</span> will automaticly be interpreted as a <span style="color:blue" >percentage</span> of the available space.', 'wppa').'<br />'.
						__('Leave this blank for default size', 'wppa').'</small>'.
				'</td>'.
			'</tr>'.
			
			// Align
			'<tr>'.
				'<th><label for="wppagallery-align">'.__('Horizontal alignment:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="wppagallery-align" name="align" style="color:#070;" onchange="wppaGalleryEvaluate();">'.
						'<option value="none" >'.__('--- none ---', 'wppa').'</option>'.
						'<option value="left" >'.__('left', 'wppa').'</option>'.
						'<option value="center" >'.__('center', 'wppa').'</option>'.
						'<option value="right" >'.__('right', 'wppa').'</option>'.
					'</select>'.
					'<br />'.
					'<small>'.__('Specify the alignment to be used or --- none ---', 'wppa').'</small>'.
				'</td>'.
			'</tr>'.

		'</table>'.
		'<div id="wppagallery-shortcode-preview-container" >'.
			'<input type="text" id="wppagallery-shortcode-preview" style="background-color:#ddd; width:100%; height:26px;" value="[wppa]Any comment[/wppa]" />'.
		'</div>'.
		'<div><small>'.__('This is a preview of the shortcode that is being generated. You may edit the comment', 'wppa').'</small></div>'.
		'<p class="submit">'.
			'<input type="button" id="wppagallery-submit" class="button-primary" value="'.__('Insert Gallery', 'wppa').'" name="submit" />&nbsp;'.
			'<input type="button" id="wppagallery-submit-notok" class="button-secundary" value="'.__('insert Gallery', 'wppa').'" onclick="alert(\''.esc_js(__('Please complete the shortcode specs', 'wppa')).'\')" />&nbsp;'.
		'</p>'.
	'</div>'.
	'<script type="text/javascript" >wppaGalleryEvaluate();</script>';
	return $result;
}
?>