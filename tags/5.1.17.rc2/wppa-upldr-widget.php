<?php
/* wppa-upldr-widget.php
* Package: wp-photo-album-plus
*
* display a list of users linking to their photos
* Version 5.1.15
*/

class UpldrWidget extends WP_Widget {
    /** constructor */
    function UpldrWidget() {
        parent::WP_Widget(false, $name = 'User Photos');	
		$widget_ops = array('classname' => 'wppa_upldr_widget', 'description' => __( 'WPPA+ Uploader Photos', 'wppa') );
		$this->WP_Widget('wppa_upldr_widget', __('Uploader Photos', 'wppa'), $widget_ops);
    }

	/** @see WP_Widget::widget */
    function widget($args, $instance) {		
		global $wpdb;
		global $wppa_opt;
		global $wppa;

        $wppa['in_widget'] = 'upldr';
		$wppa['master_occur']++;
		extract( $args );
		
		$instance 		= wp_parse_args( (array) $instance, array( 
														'title' => '',
														'sortby' => 'name',
														'ignore' => 'admin'
														) );
 		$widget_title 	= apply_filters('widget_title', $instance['title'] );
		$page 			= wppa_get_the_landing_page('wppa_upldr_widget_linkpage', __a('User uploaded photos'));
		$ignorelist		= explode(',', $instance['ignore']);
		$upldrcache 	= wppa_get_upldr_cache();
		$needupdate 	= false;
		$users 			= $wpdb->get_results( "SELECT * FROM `".$wpdb->prefix.'users'."`", ARRAY_A );
		$workarr 		= array();
		
		// Make the data we need
		foreach ( $users as $user ) {
			if ( ! in_array($user['user_login'], $ignorelist) ) {
				$me = wppa_get_user();
				if ( $user['user_login'] != $me && isset ( $upldrcache[$user['ID']] ) ) $photo_count = $upldrcache[$user['ID']];
				else {
					$photo_count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM `".WPPA_PHOTOS."` WHERE `owner` = %s AND ( `status` <> 'pending' OR `owner` = %s )", $user['user_login'], $me ));
					if ( $user['user_login'] != $me ) {
						$upldrcache[$user['ID']] = $photo_count;
						$needupdate = true;
					}
				}
				if ( $photo_count ) {
					$last_dtm = $wpdb->get_var($wpdb->prepare( "SELECT `timestamp` FROM `".WPPA_PHOTOS."` WHERE `owner` = %s AND ( `status` <> 'pending' OR `owner` = %s ) ORDER BY `timestamp` DESC LIMIT 1", $user['user_login'], $me ));
					$workarr[] = array('login' => $user['user_login'], 'name' => $user['display_name'], 'count' => $photo_count, 'date' => $last_dtm);
				}
			}
		}
		if ( $needupdate ) update_option('wppa_upldr_cache', $upldrcache);
		
		// Bring me to top
		$myline = false;
		if ( is_user_logged_in() ) {
			$me = wppa_get_user();
			foreach ( array_keys($workarr) as $key ) {
				$user = $workarr[$key];
				if ( $user['login'] == $me ) {
					$myline = $workarr[$key];
					unset ( $workarr[$key] );
				}
			}
		}
		
		// Sort workarray
		$ord = $instance['sortby'] == 'name' ? SORT_ASC : SORT_DESC;
		$workarr = wppa_array_sort($workarr, $instance['sortby'], $ord);
		
		// Create widget content
		$widget_content = "\n".'<!-- WPPA+ Upldr Widget start -->';
		$widget_content .= '<div class="wppa-upldr" style="max-height:180px; overflow:auto"><table><tbody>';
		if ( $myline ) {
			$user = $myline;
			$widget_content .= '<tr class="wppa-user" >
									<td style="padding: 0 3px;" ><a href="'.wppa_get_upldr_link($user['login']).'" title="'.__a('Photos uploaded by').' '.$user['name'].'" ><b>'.$user['name'].'</b></a></td>
									<td style="padding: 0 3px;" ><b>'.$user['count'].'</b></td>
									<td style="padding: 0 3px;" ><b>'.wppa_get_time_since($user['date']).'</b></td>
								</tr>';
		}
		foreach ( $workarr as $user ) {
			$widget_content .= '<tr class="wppa-user" >
									<td style="padding: 0 3px;" ><a href="'.wppa_get_upldr_link($user['login']).'" title="'.__a('Photos uploaded by').' '.$user['name'].'" >'.$user['name'].'</a></td>
									<td style="padding: 0 3px;" >'.$user['count'].'</td>
									<td style="padding: 0 3px;" >'.wppa_get_time_since($user['date']).'</td>
								</tr>';
		}
		$widget_content .= '</tbody></table></div>';
		$widget_content .= '<div style="clear:both"></div>';
		$widget_content .= "\n".'<!-- WPPA+ Upldr Widget end -->';
		
		// Output
		echo "\n" . $before_widget;
		if ( !empty( $widget_title ) ) { echo $before_title . $widget_title . $after_title; }
		echo $widget_content . $after_widget;
    }
	
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['title'] 		= strip_tags($new_instance['title']);
		$instance['sortby'] 	= $new_instance['sortby'];
		$instance['ignore'] 	= $new_instance['ignore'];
		
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {	
		global $wppa_opt;
		//Defaults
		$instance 		= wp_parse_args( (array) $instance, array( 
														'title' => __('User Photos', 'wppa'),
														'sortby' => 'name',
														'ignore' => 'admin'
														) );
 		$widget_title 	= apply_filters('widget_title', $instance['title']);

?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wppa'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $widget_title; ?>" />
		</p>

		<p><label for="<?php echo $this->get_field_id('sortby'); ?>"><?php _e('Sort by:', 'wppa'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('sortby'); ?>" name="<?php echo $this->get_field_name('sortby'); ?>" >
				<option value="name" <?php if ($instance['sortby'] == 'name') echo 'selected="selected"' ?>><?php _e('Display name', 'wppa') ?></option>
				<option value="count" <?php if ($instance['sortby'] == 'count') echo 'selected="selected"' ?>><?php _e('Number of photos', 'wppa') ?></option>
				<option value="date" <?php if ($instance['sortby'] == 'date') echo 'selected="selected"' ?>><?php _e('Most recent photo', 'wppa') ?></option>
			</select>
		</p>
		
		<p><label for="<?php echo $this->get_field_id('ignore'); ?>"><?php _e('Ignore:', 'wppa'); ?></label>
			<input class="widefat" id=<?php echo $this->get_field_id('ignore'); ?>" name="<?php echo $this->get_field_name('ignore'); ?>" value="<?php echo $instance['ignore'] ?>" />
			<small><?php _e('Enter loginnames seperated by commas', 'wppa') ?></small>
		</p>

<?php
    }

} // class UpldrWidget

// register UpldrWidget widget
add_action('widgets_init', create_function('', 'return register_widget("UpldrWidget");'));
