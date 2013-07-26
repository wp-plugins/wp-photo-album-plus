<?php
/* wppa-thumbnail-widget.php
* Package: wp-photo-album-plus
*
* display thumbnail photos
* Version 5.0.15
*/

class ThumbnailWidget extends WP_Widget {
    /** constructor */
    function ThumbnailWidget() {
        parent::WP_Widget(false, $name = 'Thumbnail Photos');	
		$widget_ops = array('classname' => 'wppa_thumbnail_widget', 'description' => __( 'WPPA+ Thumbnails', 'wppa') );
		$this->WP_Widget('wppa_thumbnail_widget', __('Thumbnail Photos', 'wppa'), $widget_ops);
    }

	/** @see WP_Widget::widget */
    function widget($args, $instance) {		

		global $wpdb;
		global $wppa_opt;
		global $wppa;

		$wppa['in_widget'] = 'tn';
		
        extract( $args );
		
		$instance 		= wp_parse_args( (array) $instance, array( 
														'title' => '',
														'album' => 'no',
														'name' => 'no',
														'display' => 'thumbs'
														) );
		$widget_title 	= apply_filters('widget_title', $instance['title']);
		$page 			= $wppa_opt['wppa_thumbnail_widget_linkpage'];
		$max  			= $wppa_opt['wppa_thumbnail_widget_count'];
		$album 			= $instance['album'];
		$name 			= $instance['name'];
		$display 		= $instance['display'];
		
		if ($album) {
			$thumbs = $wpdb->get_results($wpdb->prepare( 'SELECT * FROM `'.WPPA_PHOTOS.'` WHERE `status` <> %s AND `album` = %s '.wppa_get_photo_order($album).' LIMIT '.$max, 'pending', $album ), 'ARRAY_A' );
		}
		else {
			$thumbs = $wpdb->get_results($wpdb->prepare( 'SELECT * FROM '.WPPA_PHOTOS.' WHERE `status` <> %s '.wppa_get_photo_order('0').' LIMIT '.$max, 'pending' ), 'ARRAY_A' );
		}

		global $widget_content;
		$widget_content = "\n".'<!-- WPPA+ thumbnail Widget start -->';
		$maxw = $wppa_opt['wppa_thumbnail_widget_size'];
		$maxh = $maxw;

		if ( $name == 'yes' ) $maxh += 18;
		
		if ( $thumbs ) foreach ( $thumbs as $image ) {
			global $thumb;
			$thumb = $image;
			// Make the HTML for current picture
			if ( $display == 'thumbs' ) {
				$widget_content .= "\n".'<div class="wppa-widget" style="width:'.$maxw.'px; height:'.$maxh.'px; margin:4px; display:inline; text-align:center; float:left;">'; 
			}
			else {
				$widget_content .= "\n".'<div class="wppa-widget" >';
			}
			if ($image) {
				$link       = wppa_get_imglnk_a('tnwidget', $image['id']);
				$file       = wppa_get_thumb_path($image['id']);
				$imgstyle_a = wppa_get_imgstyle_a($file, $maxw, 'center', 'twthumb');
				$imgurl 	= wppa_get_thumb_url($image['id']);
				$imgevents 	= wppa_get_imgevents('thumb', $image['id'], true);
				$title 		= $link ? esc_attr(stripslashes($link['title'])) : '';
				
				wppa_do_the_widget_thumb('thumbnail', $image, $album, $display, $link, $title, $imgurl, $imgstyle_a, $imgevents);

				if ( $name == 'yes' && $display == 'thumbs' ) {
					$widget_content .= "\n\t".'<span style="font-size:9px;">'.__(stripslashes($image['name'])).'</span>';
				}
			}
			else {	// No image
				$widget_content .= __a('Photo not found.', 'wppa_theme');
			}
			$widget_content .= "\n".'</div>';
		}	
		else $widget_content .= 'There are no photos (yet).';

		$widget_content .= '<div style="clear:both"></div>';
		$widget_content .= "\n".'<!-- WPPA+ thumbnail Widget end -->';

		echo "\n" . $before_widget;
		if ( !empty( $widget_title ) ) { echo $before_title . $widget_title . $after_title; }
		echo $widget_content . $after_widget;
		
		$wppa['in_widget'] = false;
    }
	
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['title'] 		= strip_tags($new_instance['title']);
		$instance['album'] 		= $new_instance['album'];
		$instance['name'] 		= $new_instance['name'];
		$instance['display'] 	= $new_instance['display'];
		
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {	
		global $wppa_opt;
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 
															'title'		=> __('Thumbnail Photos', 'wppa'),
															'album' 	=> '0',
															'name' 		=> 'no',
															'display' 	=> 'thumbs'
															) );
 		$album 			= $instance['album'];
		$name 			= $instance['name'];
		$widget_title 	= $instance['title'];
		$display 		= $instance['display'];
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wppa'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $widget_title; ?>" /></p>


		<p><label for="<?php echo $this->get_field_id('album'); ?>"><?php _e('Album:', 'wppa'); ?></label> 
			<select class="widefat" id="<?php echo $this->get_field_id('album'); ?>" name="<?php echo $this->get_field_name('album'); ?>" >

				<?php echo wppa_album_select_a(array('selected' => $album, 'addall' => true, 'path' => wppa_switch('wppa_hier_albsel'))) //('', $album, true, '', '', true); ?>

			</select>
		</p>

		<p>
			<?php _e('Display:', 'wppa'); ?>
			<select id="<?php echo $this->get_field_id('display'); ?>" name="<?php echo $this->get_field_name('display'); ?>">
				<option value="thumbs" <?php if ($display == 'thumbs') echo 'selected="selected"' ?>><?php _e('thumbnail images', 'wppa'); ?></option>
				<option value="names" <?php if ($display == 'names') echo 'selected="selected"' ?>><?php _e('photo names', 'wppa'); ?></option>
			</select>
			
		</p>
		
		<p>
			<?php _e('Show photo names <small>under thumbnails only</small>:', 'wppa'); ?>
			<select id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>">
				<option value="no" <?php if ($name == 'no') echo 'selected="selected"' ?>><?php _e('no.', 'wppa'); ?></option>
				<option value="yes" <?php if ($name == 'yes') echo 'selected="selected"' ?>><?php _e('yes.', 'wppa'); ?></option>
			</select>
		</p>

		<p><?php _e('You can set the sizes in this widget in the <b>Photo Albums -> Settings</b> admin page.', 'wppa'); ?></p>
<?php
    }

} // class thumbnailWidget

// register thumbnailWidget widget
add_action('widgets_init', create_function('', 'return register_widget("ThumbnailWidget");'));
