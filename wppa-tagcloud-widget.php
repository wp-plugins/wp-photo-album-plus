<?php
/* wppa-tagcloud-widget.php
* Package: wp-photo-album-plus
*
* display the tagcloud widget
* Version 5.4.1
*
*/

class TagcloudPhotos extends WP_Widget {
    /** constructor */
    function TagcloudPhotos() {
        parent::WP_Widget(false, $name = 'Tagcloud Photos');	
		$widget_ops = array('classname' => 'wppa_tagcloud_photos', 'description' => __( 'WPPA+ Photo Tags', 'wppa') );	//
		$this->WP_Widget('wppa_tagcloud_photos', __('Photo Tag Cloud', 'wppa'), $widget_ops);															//
    }

	/** @see WP_Widget::widget */
    function widget($args, $instance) {		
		global $widget_content;
		global $wppa;
		
		$wppa['in_widget'] = 'tagcloud';
		$wppa['mocc']++;

        extract( $args );

		$instance = wp_parse_args( (array) $instance, array( 'title' => __('Photo Tags', 'wppa'), 'tags' => array() ) );
        if ( empty( $instance['tags'] ) ) $instance['tags'] = array();
		
 		$widget_title = apply_filters('widget_title', $instance['title']);

		// Display the widget
		echo $before_widget;
			
		if ( !empty( $widget_title ) ) { echo $before_title . $widget_title . $after_title; }
		
		echo '<div class="wppa-tagcloud-widget" >'.wppa_get_tagcloud_html(implode(',', $instance['tags'])).'</div>';

		echo '<div style="clear:both"></div>';
		echo $after_widget;
    }
	
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['tags'] = $new_instance['tags'];	
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('Photo Tags', 'wppa'), 'tags' => '' ) );
		$title = $instance['title'];
		$stags = $instance['tags'];
		if ( ! $stags ) $stags = array();
		
		echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:', 'wppa') . '</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></p>';
		echo '<p><label for="' . $this->get_field_id('tags') . '">' . __('Select multiple tags or --- all ---:', 'wppa') . '</label><br />';
			echo '<select class="widefat" id="' . $this->get_field_id('tags') . '" name="' . $this->get_field_name('tags') . '[]" multiple="multiple" >'.
					'<option value="" >'.__('--- all ---', 'wppa').'</option>';
						$tags = wppa_get_taglist();
						if ( $tags ) foreach ( array_keys($tags) as $tag ) {
							if ( in_array($tag, $stags) ) $sel = ' selected="selected"'; else $sel = '';
							echo '<option value="'.$tag.'"'.$sel.' >'.$tag.'</option>';
						}
			echo '</select>';
		echo '</p>';
		if ( isset($instance['tags']['0']) && $instance['tags']['0'] ) $s = implode(',', $instance['tags']); else $s = __('--- all ---', 'wppa');
		echo '<p>Currently selected tags: <br /><b>'.$s.'</b></p>';

    }

} // class TagcloudPhotos

// register Photo Tags widget
add_action('widgets_init', create_function('', 'return register_widget("TagcloudPhotos");'));

