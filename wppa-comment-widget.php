<?php
/* wppa-comment-widget.php
* Package: wp-photo-album-plus
*
* display the recent commets on photos
* Version 5.3.9
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

class wppaCommentWidget extends WP_Widget {
    /** constructor */
    function wppaCommentWidget() {
        parent::WP_Widget(false, $name = 'Comments on Photos');	
		$widget_ops = array('classname' => 'wppa_comment_widget', 'description' => __( 'WPPA+ Comments on Photos', 'wppa') );
		$this->WP_Widget('wppa_comment_widget', __('Comments on Photos', 'wppa'), $widget_ops);
    }

	/** @see WP_Widget::widget */
    function widget($args, $instance) {		
		global $wpdb;
		global $wppa_opt;
		global $wppa;

        extract( $args );

		$page = in_array( $wppa_opt['wppa_comment_widget_linktype'], $wppa['links_no_page'] ) ? '' : wppa_get_the_landing_page('wppa_comment_widget_linkpage', __a('Recently commented photos'));

		$max  = $wppa_opt['wppa_comten_count'];
		$widget_title = apply_filters('widget_title', $instance['title']);
		
		$comments = $wpdb->get_results($wpdb->prepare( "SELECT * FROM ".WPPA_COMMENTS." WHERE `status` = 'approved' ORDER BY `timestamp` DESC LIMIT %d", $max ), ARRAY_A );

		$widget_content = "\n".'<!-- WPPA+ Comment Widget start -->';
		$maxw = $wppa_opt['wppa_comten_size'];
		$maxh = $maxw + 18;

		if ($comments) foreach ($comments as $comment) {
		
			// Make the HTML for current comment
			$widget_content .= "\n".'<div class="wppa-widget" style="width:'.$maxw.'px; height:'.$maxh.'px; margin:4px; display:inline; text-align:center; float:left;">'; 
			$image = $wpdb->get_row($wpdb->prepare( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` = %s", $comment['photo'] ), ARRAY_A );
			if ($image) {
			
				global $thumb;
				$thumb = $image;
				
				$no_album 	= true;//!$album;
				$tit		= esc_attr(wppa_qtrans(stripslashes($comment['comment'])));
				$link       = wppa_get_imglnk_a('comten', $image['id'], '', $tit, $no_album);
				$file       = wppa_get_thumb_path($image['id']);
				$imgstyle_a = wppa_get_imgstyle_a( $image['id'], $file, $maxw, 'center', 'comthumb' );
				$imgstyle   = $imgstyle_a['style'];
				$width      = $imgstyle_a['width'];
				$height     = $imgstyle_a['height'];
				$cursor		= $imgstyle_a['cursor'];
				$imgurl 	= wppa_get_thumb_url($image['id'], '', $width, $height);
				
				$imgevents = wppa_get_imgevents('thumb', $image['id'], true);	

//				if ($link) $title = esc_attr(stripslashes($link['title']));
//				else 
				$title = esc_attr($comment['comment']);
				if ($link) {
					if ( $link['is_url'] ) {	// Is a href
						$widget_content .= "\n\t".'<a href="'.$link['url'].'" target="'.$link['target'].'" title="'.$title.'">';
							$widget_content .= "\n\t\t".'<img id="i-'.$image['id'].'-'.$wppa['master_occur'].'" title="'.$title.'" src="'.$imgurl.'" width="'.$width.'" height="'.$height.'" style="'.$imgstyle.' cursor:pointer;" '.$imgevents.' alt="'.esc_attr(wppa_qtrans($image['name'])).'">';
						$widget_content .= "\n\t".'</a>';
					}
					elseif ( $link['is_lightbox'] ) {
						$title = wppa_get_lbtitle('thumb', $image['id']);
						$widget_content .= "\n\t".'<a href="'.$link['url'].'" data-videohtml="'.esc_attr( wppa_get_video_body( $image['id'] ) ).'" rel="'.$wppa_opt['wppa_lightbox_name'].'[comment]" title="'.$title.'">';
							$widget_content .= "\n\t\t".'<img id="i-'.$image['id'].'-'.$wppa['master_occur'].'" title="'.wppa_zoom_in().'" src="'.$imgurl.'" width="'.$width.'" height="'.$height.'" style="'.$imgstyle.$cursor.'" '.$imgevents.' alt="'.esc_attr(wppa_qtrans($image['name'])).'">';
						$widget_content .= "\n\t".'</a>';
					}
					else { // Is an onclick unit
						$widget_content .= "\n\t".'<img id="i-'.$image['id'].'-'.$wppa['master_occur'].'" title="'.$title.'" src="'.$imgurl.'" width="'.$width.'" height="'.$height.'" style="'.$imgstyle.' cursor:pointer;" '.$imgevents.' onclick="'.$link['url'].'" alt="'.esc_attr(wppa_qtrans($image['name'])).'">';					
					}
				}
				else {
					$widget_content .= "\n\t".'<img id="i-'.$image['id'].'-'.$wppa['master_occur'].'" title="'.$title.'" src="'.$imgurl.'" width="'.$width.'" height="'.$height.'" style="float:right; '.$imgstyle.'" '.$imgevents.' alt="'.esc_attr(wppa_qtrans($image['name'])).'">';
				}
			}
			else {
				$widget_content .= __a('Photo not found.', 'wppa_theme');
			}
			$widget_content .= "\n\t".'<span style="font-size:'.$wppa_opt['wppa_fontsize_widget_thumb'].'px; cursor:pointer;" title="'.esc_attr($comment['comment']).'" >'.$comment['user'].'</span>';
			$widget_content .= "\n".'</div>';
			
		}	
		else $widget_content .= 'There are no commented photos (yet).';
		
		$widget_content .= '<div style="clear:both"></div>';
		$widget_content .= "\n".'<!-- WPPA+ comment Widget end -->';

		echo "\n" . $before_widget;
		if ( !empty( $widget_title ) ) { echo $before_title . $widget_title . $after_title; }
		echo $widget_content . $after_widget;
    }
	
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('Comments on Photos', 'wppa') ) );
 		$widget_title = $instance['title'];
?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wppa'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $widget_title; ?>" /></p>
			<p><?php _e('You can set the sizes in this widget in the <b>Photo Albums -> Settings</b> admin page.', 'wppa'); ?></p>
<?php
    }

} // class wppaCommentWidget

// register wppaCommentWidget widget only if comment system is enabled
add_action('widgets_init', create_function('', 'return register_widget("wppaCommentWidget");'));
