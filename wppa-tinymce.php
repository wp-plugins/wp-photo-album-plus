<?php
/* wppa-tinymce.php
* Pachkage: wp-photo-album-plus
*
*
* Version 4.6.10
*
*/

if ( ! defined( 'ABSPATH' ) )
    die( "Can't load this file directly" );
 
class wppaGallery
{
    function __construct() {
    add_action( 'admin_init', array( $this, 'action_admin_init' ) );
	}
	 
	function action_admin_init() {
		// only hook up these filters if we're in the admin panel, and the current user has permission
		// to edit posts and pages
		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
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
		$plugins['mygallery'] = plugin_dir_url( __FILE__ ) . 'wppa-tinymce.js';
		return $plugins;
	}
 
}
 
$wppagallery = new wppaGallery();

add_action('admin_head', 'wppa_inject_js');

function wppa_inject_js() {
	// Things that wppa-tinymce.js needs to know
	echo('<script type="text/javascript">'."\n");
	echo('/* <![CDATA[ */'."\n");
		echo("\t".'wppaImageDirectory = "'.wppa_get_imgdir().'";'."\n");
		echo("\t".'wppaAjaxUrl = "'.admin_url('admin-ajax.php').'";'."\n");
		echo("\t".'wppaThumbDirectory = "'.WPPA_UPLOAD_URL.'/thumbs/";'."\n");
	echo("/* ]]> */\n");
	echo("</script>\n");
}

function wppa_make_tinymce_dialog() {
global $wpdb;

	$result = 
	'<div id="mygallery-form">'.
		'<div style="height:156px; background-color:#eee; overflow:auto; margin-top:10px;" >'.
			'<div id="mygallery-album-preview" style="text-align:center;font-size:48px; line-height:21px; color:#fff;" class="mygallery-album" ><br /><br /><br />Preview</div>'.
			'<div id="mygallery-photo-preview" style="text-align:center;font-size:48px; line-height:21px; color:#fff; display:none;" class="mygallery-photo" ><br /><br /><br />Preview</div>'.
		'</div>'.
		'<table id="mygallery-table" class="form-table">'.
		
			'<tr>'.
				'<th><label for="mygallery-type">'.__('Type of Gallery display:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="mygallery-type" name="type" onchange="if (this.value == \'photo\' || this.value == \'mphoto\') {jQuery(\'.mygallery-photo\').show();jQuery(\'.mygallery-album\').hide();} else {jQuery(\'.mygallery-photo\').hide();jQuery(\'.mygallery-album\').show();}">'.
						'<option value="cover">'.__('The cover of an album', 'wppa').'</option>'.
						'<option value="album">'.__('The sub-albums and/or thumbnails in an album', 'wppa').'</option>'.
						'<option value="slide">'.__('A slideshow of the photos in an album', 'wppa').'</album>'.
						'<option value="slideonly">'.__('A slideshow without supporting boxes', 'wppa').'</album>'.
						'<option value="slideonlyf">'.__('A slideshow with a filmstrip only', 'wppa').'</album>'.
						'<option value="photo">'.__('A single photo', 'wppa').'</album>'.
						'<option value="mphoto">'.__('A single photo with caption', 'wppa').'</album>'.
					'</select>'.
					'<br />'.
					'<small>'.__('Specify the type of gallery', 'wppa').'</small>'.
				'</td>'.
			'</tr>'.
			
			'<tr class="mygallery-album" >'.
				'<th><label for="mygallery-album" class="mygallery-album" >'.__('The Album to be used:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="mygallery-album" name="album" class="mygallery-album" onchange="wppaTinyMceAlbumPreview(this.value)">';
						$albums = $wpdb->get_results($wpdb->prepare("SELECT `id`, `name` FROM `".WPPA_ALBUMS."` ORDER BY `timestamp` DESC"), 'ARRAY_A');
						if ($albums) {
							$result .= 
							'<option value="0" >'.__('Please select an album', 'wppa').'</option>';
							foreach ( $albums as $album ) {
								$value = $album['id'];
								$photos = $wpdb->get_results($wpdb->prepare("SELECT `id`, `name`, `album`, `ext` FROM `".WPPA_PHOTOS."` WHERE `album` = %s ".wppa_get_photo_order($album['id'])." DESC LIMIT 100", $album['id']), 'ARRAY_A');
								if ( $photos ) foreach ( $photos as $photo ) {
									$value .= '|'.$photo['id'].'.'.$photo['ext'];
								}
								else $value .= '|';
								$result .= '<option value="'.$value.'" >'.stripslashes(__($album['name'])).'</option>';
							}
							$result .=
							'<option value = "#last" >'.__('- The latest created album -', 'wppa').'</option>'.
							'<option value = "#topten" >'.__('--- The top rated photos ---', 'wppa').'</option>'.
							'<option value = "#lasten" >'.__('--- The most recently uploaded photos ---', 'wppa').'</option>'.
							'<option value = "#all" >'.__('--- All photos in the system ---', 'wppa').'</option>';
						}
						else {
							$result .= '<option value="0" >'.__('There are no albums yet', 'wppa').'</option>';
						}
					$result .=
					'</select>'.
					'<br />'.
					'<small class="mygallery-album" >'.
						__('Specify the album to be used or --- A special selection of photos ---', 'wppa').'<br />&nbsp;'.
					'</small>'.
				'</td>'.
			'</tr>'.
			
			'<tr class="mygallery-photo" style="display:none;" >'.
				'<th><label for="mygallery-photo" style="display:none;" class="mygallery-photo" >'.__('The Photo to be used:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="mygallery-photo" name="photo" style="display:none;" class="mygallery-photo" onchange="wppaTinyMcePhotoPreview(this.value)" >';
						$photos = $wpdb->get_results($wpdb->prepare("SELECT `id`, `name`, `album`, `ext` FROM `".WPPA_PHOTOS."` ORDER BY `timestamp` DESC LIMIT 100"), 'ARRAY_A');
						if ($photos) {
							$result .= '<option value="0" >'.__('Please select a photo', 'wppa').'</option>';
							foreach ( $photos as $photo ) {
								$result .= '<option value="'.$photo['id'].'.'.$photo['ext'].'" >'.stripslashes(__($photo['name'])).' ('.__(wppa_get_album_name($photo['album'])).')'.'</option>';
							}
						}
						else {
							$result .= '<option value="0" >'.__('There are no photos yet', 'wppa').'</option>';
						}
					$result .=
					'</select>'.
					'<br />'.
					'<small style="display:none;" class="mygallery-photo" >'.
						__('Specify the photo to be used', 'wppa').'<br />'.
						__('You can select from a maximum of 100 most recently added photos', 'wppa').'<br />'.
					'</small>'.
				'</td>'.
			'</tr>'.

			'<tr>'.
				'<th><label for="mygallery-size">'.__('The size of the display:', 'wppa').'</label></th>'.
				'<td>'.
					'<input type="text" id="mygallery-size" value="" />'.
					'<br />'.
					'<small>'.
						__('Specify the horizontal size in pixels or <span style="color:blue" >auto</span>', 'wppa').'<br />'.
						__('Leave this blank for default size', 'wppa').'</small>'.
				'</td>'.
			'</tr>'.
			
			'<tr>'.
				'<th><label for="mygallery-align">'.__('Horizontal alignment:', 'wppa').'</label></th>'.
				'<td>'.
					'<select id="mygallery-align" name="align" >'.
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
		'<p class="submit">'.
			'<input type="button" id="mygallery-submit" class="button-primary" value="Insert Gallery" name="submit" />'.
		'</p>'.
	'</div>';
	return $result;
}
?>