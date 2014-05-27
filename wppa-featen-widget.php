<?php
/* wppa-featen-widget.php
* Package: wp-photo-album-plus
*
* display the featured photos
* Version 5.3.9
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

class FeaTenWidget extends WP_Widget {
    /** constructor */
    function FeaTenWidget() {
        parent::WP_Widget(false, $name = 'Featured Photos');	
		$widget_ops = array('classname' => 'wppa_featen_widget', 'description' => __( 'WPPA+ Featured Photos', 'wppa') );
		$this->WP_Widget('wppa_featen_widget', __('Featured Photos', 'wppa'), $widget_ops);
    }

	/** @see WP_Widget::widget */
    function widget($args, $instance) {		
		global $wpdb;
		global $wppa_opt;
		global $wppa;

        extract( $args );
		
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'album' => '' ) );

 		$widget_title = apply_filters('widget_title', $instance['title'] );
		$page = in_array( $wppa_opt['wppa_featen_widget_linktype'], $wppa['links_no_page'] ) ? '' : wppa_get_the_landing_page('wppa_featen_widget_linkpage', __a('Featured photos'));

		$max  = $wppa_opt['wppa_featen_count'];
		
		$album = $instance['album'];

		$generic = ( $album == '-2' );
		if ( $generic ) {
			$album = '0';
			$max += '1000';
		}
		
		if ( $album ) {
			$thumbs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `status`= 'featured' AND `album` = %s ORDER BY RAND(".$wppa['randseed'].") DESC LIMIT " . $max, $album ), ARRAY_A );
		}
		else {
			$thumbs = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `status` = 'featured' ORDER BY RAND(".$wppa['randseed'].") DESC LIMIT " . $max, ARRAY_A );
		}
		$widget_content = "\n".'<!-- WPPA+ FeaTen Widget start -->';
		$maxw = $wppa_opt['wppa_featen_size'];
		$maxh = $maxw;
		$lineheight = $wppa_opt['wppa_fontsize_widget_thumb'] * 1.5;
		$maxh += $lineheight;
		if ( false ) 	$maxh += $lineheight;

		$count = '0';
		if ($thumbs) foreach ($thumbs as $image) {
			
			global $thumb;
			$thumb = $image;

			if ( $generic && wppa_is_separate( $thumb['album'] ) ) continue;

			// Make the HTML for current picture
			$widget_content .= "\n".'<div class="wppa-widget" style="width:'.$maxw.'px; height:'.$maxh.'px; margin:4px; display:inline; text-align:center; float:left;">'; 
			if ($image) {
				$no_album = !$album;
				if ($no_album) $tit = __a('View the featured photos', 'wppa_theme'); else $tit = esc_attr(wppa_qtrans(stripslashes($image['description'])));
				$link       = wppa_get_imglnk_a('featen', $image['id'], '', $tit, '', $no_album);
				$file       = wppa_get_thumb_path($image['id']);
				$imgstyle_a = wppa_get_imgstyle_a( $image['id'], $file, $maxw, 'center', 'ttthumb' );
				$imgstyle   = $imgstyle_a['style'];
				$width      = $imgstyle_a['width'];
				$height     = $imgstyle_a['height'];
				$cursor		= $imgstyle_a['cursor'];
				$imgurl 	= wppa_get_thumb_url( $image['id'], '', $width, $height );

				$imgevents = wppa_get_imgevents('thumb', $image['id'], true);

				if ($link) $title = esc_attr(stripslashes($link['title']));
				else $title = '';
				
				if ($link) {
					if ( $link['is_url'] ) {	// Is a href
						$widget_content .= "\n\t".'<a href="'.$link['url'].'" title="'.$title.'" target="'.$link['target'].'" >';
							$widget_content .= "\n\t\t".'<img id="i-'.$image['id'].'-'.$wppa['master_occur'].'" title="'.$title.'" src="'.$imgurl.'" width="'.$width.'" height="'.$height.'" style="'.$imgstyle.' cursor:pointer;" '.$imgevents.' alt="'.esc_attr(wppa_qtrans($image['name'])).'">';
						$widget_content .= "\n\t".'</a>';
					}
					elseif ( $link['is_lightbox'] ) {
						$title = wppa_get_lbtitle('thumb', $image['id']);
						$widget_content .= "\n\t".'<a href="'.$link['url'].'" data-videohtml="'.esc_attr( wppa_get_video_body( $image['id'] ) ).'" rel="'.$wppa_opt['wppa_lightbox_name'].'[featen-'.$album.']" title="'.$title.'" target="'.$link['target'].'" >';
							$widget_content .= "\n\t\t".'<img id="i-'.$image['id'].'-'.$wppa['master_occur'].'" title="'.wppa_zoom_in().'" src="'.$imgurl.'" width="'.$width.'" height="'.$height.'" style="'.$imgstyle.$cursor.'" '.$imgevents.' alt="'.esc_attr(wppa_qtrans($image['name'])).'">';
						$widget_content .= "\n\t".'</a>';
					}
					else { // Is an onclick unit
						$widget_content .= "\n\t".'<img id="i-'.$image['id'].'-'.$wppa['master_occur'].'" title="'.$title.'" src="'.$imgurl.'" width="'.$width.'" height="'.$height.'" style="'.$imgstyle.' cursor:pointer;" '.$imgevents.' onclick="'.$link['url'].'" alt="'.esc_attr(wppa_qtrans($image['name'])).'">';					
					}
				}
				else {
					$widget_content .= "\n\t".'<img id="i-'.$image['id'].'-'.$wppa['master_occur'].'" title="'.$title.'" src="'.$imgurl.'" width="'.$width.'" height="'.$height.'" style="'.$imgstyle.'" '.$imgevents.' alt="'.esc_attr(wppa_qtrans($image['name'])).'">';
				}
//			$widget_content .= "\n\t".'<span style="font-size:'.$wppa_opt['wppa_fontsize_widget_thumb'].'px;">'.wppa_get_rating_by_id($image['id']);
//				if ( wppa_switch('wppa_show_rating_count') ) $widget_content .= ' ('.wppa_get_rating_count_by_id($image['id']).')';
//			$widget_content .= '</span>'.
				$widget_content .= "\n".'</div>';
				
				
			}
			else {	// No image
				$widget_content .= __a('Photo not found.', 'wppa_theme');
			}
			$count++;
			if ( $count == $wppa_opt['wppa_featen_count'] ) break;
			
		}	
		else $widget_content .= 'There are no featured photos (yet).';
		
		$widget_content .= '<div style="clear:both"></div>';
		$widget_content .= "\n".'<!-- WPPA+ FeaTen Widget end -->';

		echo "\n" . $before_widget;
		if ( !empty( $widget_title ) ) { echo $before_title . $widget_title . $after_title; }
		echo $widget_content . $after_widget;
    }
	
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['album'] = $new_instance['album'];
				
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {	
		global $wppa_opt;
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('Featured Photos', 'wppa'), 'album' => '0') );
 		$widget_title = apply_filters('widget_title', $instance['title']);

		$album = $instance['album'];
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wppa'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $widget_title; ?>" />
		</p>
		<p><label for="<?php echo $this->get_field_id('album'); ?>"><?php _e('Album:', 'wppa'); ?></label> 
			<select class="widefat" id="<?php echo $this->get_field_id('album'); ?>" name="<?php echo $this->get_field_name('album'); ?>" >

				<?php echo wppa_album_select_a(array('selected' => $album, 'addall' => true, 'path' => wppa_switch('wppa_hier_albsel'))) //('', $album, true, '', '', true); ?>

			</select>
		</p>

		<p><?php _e('You can set the sizes in this widget in the <b>Photo Albums -> Settings</b> admin page.', 'wppa'); ?></p>
<?php
    }

} // class FeaTenWidget

// register FeaTenWidget widget
add_action('widgets_init', create_function('', 'return register_widget("FeaTenWidget");'));
