<?php
/* wppa-comment-widget.php
* Package: wp-photo-album-plus
*
* display the recent commets on photos
* Version 6.1.14
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
		global $wppa;

		require_once(dirname(__FILE__) . '/wppa-links.php');
		require_once(dirname(__FILE__) . '/wppa-styles.php');
		require_once(dirname(__FILE__) . '/wppa-functions.php');
		require_once(dirname(__FILE__) . '/wppa-thumbnails.php');
		require_once(dirname(__FILE__) . '/wppa-boxes-html.php');
		require_once(dirname(__FILE__) . '/wppa-slideshow.php');
		wppa_initialize_runtime();

		$wppa['in_widget'] = 'com';
		$wppa['mocc']++;

        extract( $args );

		$page 			= in_array( wppa_opt( 'wppa_comment_widget_linktype' ), $wppa['links_no_page'] ) ? '' : wppa_get_the_landing_page('wppa_comment_widget_linkpage', __a('Recently commented photos'));
		$max  			= wppa_opt( 'wppa_comten_count' );
		$widget_title 	= apply_filters('widget_title', $instance['title']);
		$photo_ids 		= wppa_get_comten_ids( $max );
		$widget_content = "\n".'<!-- WPPA+ Comment Widget start -->';
		$maxw 			= wppa_opt( 'wppa_comten_size' );
		$maxh 			= $maxw + 18;

		if ( $photo_ids ) foreach( $photo_ids as $id ) {
		
			// Make the HTML for current comment
			$widget_content .= "\n".'<div class="wppa-widget" style="width:'.$maxw.'px; height:'.$maxh.'px; margin:4px; display:inline; text-align:center; float:left;">'; 
			
			$image = wppa_cache_thumb( $id );
			
			if ( $image ) {
			
				$link       = wppa_get_imglnk_a( 'comten', $id, '', '', true );
				$file       = wppa_get_thumb_path( $id );
				$imgstyle_a = wppa_get_imgstyle_a( $id, $file, $maxw, 'center', 'comthumb' );
				$imgstyle   = $imgstyle_a['style'];
				$width      = $imgstyle_a['width'];
				$height     = $imgstyle_a['height'];
				$cursor		= $imgstyle_a['cursor'];
				$imgurl 	= wppa_get_thumb_url($id, '', $width, $height);
				
				$imgevents = wppa_get_imgevents('thumb', $id, true);	

				$title = '';
				$comments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_COMMENTS."` WHERE `photo` = %s ORDER BY `timestamp` DESC", $id ), ARRAY_A );
				if ( $comments ) foreach ( $comments as $comment ) {
					$title .= $comment['user'].' '.__a( 'wrote' ).' '.wppa_get_time_since( $comment['timestamp'] ).":\n";
					$title .= $comment['comment']."\n\n";
				}
				$title = esc_attr( strip_tags( trim ( $title ) ) );
				
				$album = '0';
				$display = 'thumbs';

				$widget_content .= wppa_get_the_widget_thumb('comten', $image, $album, $display, $link, $title, $imgurl, $imgstyle_a, $imgevents);

			}

			else {
				$widget_content .= __a('Photo not found.', 'wppa_theme');
			}
			$widget_content .= "\n\t".'<span style="font-size:'.wppa_opt( 'wppa_fontsize_widget_thumb' ).'px; cursor:pointer;" title="'.esc_attr($comment['comment']).'" >'.$comment['user'].'</span>';
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

// register wppaCommentWidget widget
add_action('widgets_init', create_function('', 'return register_widget("wppaCommentWidget");'));
