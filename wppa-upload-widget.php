<?php
/* wppa-upload-widget.php
* Package: wp-photo-album-plus
*
* upload wppa+ widget
*
* A wppa widget to upload photos
*
* Version 5.4.0
*/

class WppaUploadWidget extends WP_Widget {

	function WppaUploadWidget() {
	    parent::WP_Widget(false, $name = 'Upload widget');	
		$widget_ops = array('classname' => 'wppa_upload_widget', 'description' => __('WPPA+ Upload photos widget', 'wppa'));
//		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('wppa_upload_widget', __('WPPA+ Upload', 'wppa'), $widget_ops); // , $control_ops);
	}

	function widget( $args, $instance ) {
		global $wppa; 
		global $wppa_opt;

		extract($args);
		$instance = wp_parse_args( (array) $instance, 
									array( 	'title' 	=> '', 
											'album' 	=> '0'
										));
 		$title = apply_filters('widget_title', $instance['title']);
		$album = $instance['album'];

		wppa_user_upload();	// Do the upload if required
		
		$wppa['in_widget'] = 'upload';
				
		$wppa['mocc']++;
		$wppa['out'] = '';
		if ( wppa_switch('wppa_user_upload_login') && ! is_user_logged_in() ) return;	// Not logged in while login req'd for upload, no create also
		wppa_user_create_html( $album, $wppa_opt['wppa_widget_width'], 'widget' );
		wppa_user_upload_html( $album, $wppa_opt['wppa_widget_width'], 'widget' );
		if ( ! $wppa['out'] ) return;	// No possibility to upload or create, skip the widget
		
		$text = '<div class="wppa-upload-widget" style="margin-top:2px; margin-left:2px;" >'.$wppa['out'].'</div>';
		$wppa['out'] = '';

		echo $before_widget;
		if ( ! empty( $title ) ) { echo $before_title . $title . $after_title; } 
		echo $text;
		echo '<div style="clear:both"></div>';
		echo $after_widget;
		
		$wppa['in_widget'] = false;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['album'] = $new_instance['album'];
		return $instance;
	}

	function form( $instance ) {
		global $wppa_opt;
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('Upload Photos', 'wppa'), 'album' => '0' ) );
		$title = $instance['title'];
		$album = $instance['album'];
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wppa'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('album'); ?>"><?php _e('Album:', 'wppa'); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id('album'); ?>" name="<?php echo $this->get_field_name('album'); ?>" >
			<?php echo wppa_album_select_a(array('path' => wppa_switch('wppa_hier_albsel'), 'selected' => $album, 'addselbox' => true)) ?>
		</select>
<?php
	}
}
// register WppaUploadWidget
add_action('widgets_init', create_function('', 'return register_widget("WppaUploadWidget");'));