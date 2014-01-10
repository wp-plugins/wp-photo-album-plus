<?php
/* wppa-super-view-widget.php
* Package: wp-photo-album-plus
*
* ask the album / display you want
* Version 5.2.4
*/


class SuperView extends WP_Widget {
    /** constructor */
    function SuperView() {
        parent::WP_Widget(false, $name = 'Super View Photos');	
		$widget_ops = array('classname' => 'wppa_super_view', 'description' => __( 'WPPA+ Selectable display', 'xxx') );	//
		$this->WP_Widget('wppa_super_view', __('Super View Photos', 'wppa'), $widget_ops);															//
    }

	/** @see WP_Widget::widget */
    function widget($args, $instance) {		
		global $wpdb;
		global $widget_content;
		global $wppa_opt;

        extract( $args );
		$instance = wp_parse_args( (array) $instance, array( 
														'title' => '',
														'root'	=> '0',
														'sort'	=> true,
														) );
        
 		$widget_title 	= apply_filters('widget_title', $instance['title'] );
		$album_root 	= $instance['root'];
		$sort 			= $instance['sort'];

		$page 			= wppa_get_the_landing_page('wppa_super_view_linkpage', __a('Super View Photos'));

//		$page = $wppa_opt['wppa_super_view_linkpage'];
//		$iret = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `" . $wpdb->posts . "` WHERE `post_type` = 'page' AND `post_status` = 'publish' AND `ID` = %s", $page));
//		if ( ! $iret ) $page = '0';	// Page vanished
		if ( $page == '0' ) {
			$widget_content = __a('Please select a super view widget landing page in Table VI-C');
		}
		else {
		
			$url = get_permalink($page);

			if ( ! isset ( $_SESSION['wppa_session'] ) ) $_SESSION['wppa_session'] = array();
			if ( ! isset ( $_SESSION['wppa_session']['superview'] ) ) {
				$_SESSION['wppa_session']['superview'] = 'thumbs';
				$_SESSION['wppa_session']['superalbum'] = '0';
			}

			$checked = 'checked="checked"';
			
			$widget_content = '
			<div>
			<form action="'.$url.'" method = "get">
				<label>'.__('Album:', 'wppa').'</label><br />
				<select name="wppa-album">
					'.wppa_album_select_a( array( 	'selected' 			=> $_SESSION['wppa_session']['superalbum'], 
													'addpleaseselect' 	=> true, 
													'root' 				=> $album_root, 
													'content' 			=> true,
													'sort'				=> $sort
													) ).'
				</select><br />
				<input type="radio" name="wppa-slide" value="nil" '.( $_SESSION['wppa_session']['superview'] == 'thumbs' ? $checked : '' ).'>'.__('Thumbnails', 'wppa').'<br />
				<input type="radio" name="wppa-slide" value="1" '.( $_SESSION['wppa_session']['superview'] == 'slide' ? $checked : '' ).'>'.__('Slideshow', 'wppa').'<br />';
			$widget_content .= '
				<input type="hidden" name="wppa-occur" value="1" />
				<input type="hidden" name="wppa-superview" value="1" />
				<input type="submit" value="'.__('Show!', 'wppa').'" />
			</form>
			</div>
			';
		}
	
		echo $before_widget . $before_title . $widget_title . $after_title . $widget_content . $after_widget;
    }
	
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['title'] 	= strip_tags($new_instance['title']);
		$instance['root'] 	= $new_instance['root'];
		$instance['sort']	= $new_instance['sort'];
		
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 	'title' => __( 'Super View Photos', 'wppa' ), 
																'root' 	=> '0',
																'sort'	=> true
															) );
		$title 	= esc_attr( $instance['title'] );
		$root 	= $instance['root'];
		$sort 	= $instance['sort'];
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wppa'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('root'); ?>"><?php _e('Enable (sub)albums of:', 'wppa'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('root'); ?>" name="<?php echo $this->get_field_name('root'); ?>" >
				<?php echo wppa_album_select_a( array( 'selected' => $root, 'addall' => true, 'addseparate' => true, 'addgeneric' => true, 'path' => true ) ) ?>
			</select>
		</p>
			<label for="<?php echo $this->get_field_id('sort'); ?>"><?php _e('Sort alphabeticly:', 'wppa'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('sort'); ?>" name="<?php echo $this->get_field_name('sort'); ?>" >
				<option value="0" ><?php _e('no, use album sort method', 'wppa') ?></option>
				<option value="1" <?php if ( $sort ) echo 'selected="selected"' ?> ><?php _e('yes', 'wppa') ?></option>
			</select>
		<p>
		</p>
<?php
    }

} // class SuperView

// register SuperView widget
add_action('widgets_init', create_function('', 'return register_widget("SuperView");'));
?>
