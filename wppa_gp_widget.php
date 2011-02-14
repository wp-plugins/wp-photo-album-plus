<?php
/* wppa_gp_widget.php
* Package: wp-photo-album-plus
*
* gp wppa+ widget
*
* A text widget that hooks the wppa+ filter
*
* Version 2.5.1
*/

class WppaGpWidget extends WP_Widget {

	function WppaGpWidget() {
	    parent::WP_Widget(false, $name = 'General purpose widget');	
		$widget_ops = array('classname' => 'wppa_gp_widget', 'description' => __('WPPA+ General purpose widget', 'wppa'));
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('wppa_gp_widget', __('WPPA+ Text', 'wppa'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		global $wppa_in_widget;
		global $wppa_fullsize;
		extract($args);
 		$title = apply_filters('widget_title', empty( $instance['title'] ) ? '' : $instance['title']);

		$wppa_in_widget = true;
				
		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } 
		
		$text = $instance['filter'] ? wpautop($instance['text']) : $instance['text'];
		?>
			<div class="wppa-gp-widget" style="margin-top:2px; margin-left:2px;" ><?php echo wppa_albums_filter($text); ?></div>
		<?php
		
		echo $after_widget;
		
		$wppa_in_widget = false;
		$wppa_fullsize = '';	// Reset to prevent inheritage of wrong size in case widget is rendered before main column

	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( current_user_can('unfiltered_html') )
			$instance['text'] =  $new_instance['text'];
		else
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
		$instance['filter'] = isset($new_instance['filter']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = strip_tags($instance['title']);
		$text = format_to_edit($instance['text']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p><?php _e('Enter the content just like a normal text widget. This widget will interprete %%wppa%% script commands.', 'wppa'); ?></p>
		<p><?php _e('Don\'t forget %%size=', 'wppa'); echo get_option('wppa_widget_width', '190'); ?>&#37;&#37;</p>

		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>

		<p><input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked(isset($instance['filter']) ? $instance['filter'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e('Automatically add paragraphs'); ?></label></p>
<?php
	}
}
// register WppaGpWidget widget
add_action('widgets_init', create_function('', 'return register_widget("WppaGpWidget");'));