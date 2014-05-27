<?php
/* wppa-potd-widget.php
* Package: wp-photo-album-plus
*
* display the widget
* Version 5.3.9
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

class PhotoOfTheDay extends WP_Widget {
    /** constructor */
    function PhotoOfTheDay() {
        parent::WP_Widget(false, $name = 'Photo Of The Day');	
		$widget_ops = array('classname' => 'wppa_widget', 'description' => __( 'WPPA+ Photo Of The Day', 'wppa') );	//
		$this->WP_Widget('wppa_widget', __('Photo Of The Day', 'wppa'), $widget_ops);															//
    }

	/** @see WP_Widget::widget */
    function widget($args, $instance) {		
		global $wpdb;
		global $wppa_opt;
		global $wppa;
		
		$wppa['in_widget'] = 'potd';

        extract( $args );

		$widget_title = apply_filters('widget_title', $instance['title']);

		// get the photo  ($image)
		$image = wppa_get_potd();
		
		// Make the HTML for current picture
		$widget_content = "\n".'<!-- WPPA+ Photo of the day Widget start -->';

		$ali = $wppa_opt['wppa_potd_align'];
		if ($ali != 'none') {
			$align = 'text-align:'.$ali.';';
		}
		else $align = '';
		$widget_content .= "\n".'<div class="wppa-widget-photo" style="'.$align.' padding-top:2px; ">';
		if ($image) {
			// make image url
			$w = $wppa_opt['wppa_widget_width'];
			$t = getimagesize( wppa_get_photo_path( $image['id'] ) );
			$ratio = $t['1'] / $t['0'];
			$h = round( $w * $ratio );
			$usethumb	= wppa_use_thumb_file($image['id'], $wppa_opt['wppa_widget_width'], '0');
			$imgurl 	= $usethumb ? wppa_get_thumb_url( $image['id'], '', $w, $h ) : wppa_get_photo_url( $image['id'], '', $w, $h );
		
			$name = wppa_qtrans($image['name']);
			$page = in_array( $wppa_opt['wppa_widget_linktype'], $wppa['links_no_page'] ) ? '' : wppa_get_the_landing_page( 'wppa_widget_linkpage', __a('Photo of the day') );
			$link = wppa_get_imglnk_a('potdwidget', $image['id']);
			$lightbox = $link['is_lightbox'] ? 'data-videohtml="'.esc_attr( wppa_get_video_body( $image['id'] ) ).'" rel="'.$wppa_opt['wppa_lightbox_name'].'"' : '';
			if ( $link ) {
				if ( $link['is_lightbox'] ) {
					$cursor = ' cursor:url('.wppa_get_imgdir().$wppa_opt['wppa_magnifier'].'),pointer;';
					$title  = wppa_zoom_in();
					$ltitle = wppa_get_lbtitle('potd', $image['id']);
				}
				else {
					$cursor = ' cursor:pointer;';
					$title  = $link['title'];
					$ltitle = $title;
				}
			}
			else {
				$cursor = ' cursor:default;';
				$title = esc_attr(stripslashes(__($image['name'])));
			}
			
			if ($link) $widget_content .= "\n\t".'<a href = "'.$link['url'].'" target="'.$link['target'].'" '.$lightbox.' title="'.$ltitle.'">';
			
				$widget_content .= "\n\t\t".'<img src="'.$imgurl.'" style="width: '.$wppa_opt['wppa_widget_width'].'px;'.$cursor.'" alt="'.$name.'" title="'.$title.'"/>';

			if ($link) $widget_content .= "\n\t".'</a>';
			
			$widget_content .= wppa_get_medal_html( $image['id'], $h );
		} 
		else {	// No image
			$widget_content .= __a('Photo not found.', 'wppa_theme');
		}
		$widget_content .= "\n".'</div>';
		
		// Add subtitle, if any		
		switch ($wppa_opt['wppa_widget_subtitle'])
		{
			case 'none': 
				break;
			case 'name': 
				if ($image && $image['name'] != '') {
					$widget_content .= "\n".'<div class="wppa-widget-text wppa-potd-text" style="'.$align.'">' . wppa_qtrans(wppa_html(stripslashes($image['name']))) . '</div>';
				}
				break;
			case 'desc': 
				if ($image && $image['description'] != '') {
					$widget_content .= "\n".'<div class="wppa-widget-text wppa-potd-text" style="'.$align.'">' . wppa_qtrans(wppa_html(stripslashes($image['description']))) . '</div>'; 
				}
				break;
			case 'owner':
				$owner = $image['owner'];
				$user = get_user_by('login', $owner);
				$owner = $user->display_name;
				$widget_content .= "\n".'<div class="wppa-widget-text wppa-potd-text" style="'.$align.'">By: ' . $owner . '</div>';
		}

		$widget_content .= "\n".'<!-- WPPA+ Photo of the day Widget end -->';

		echo "\n" . $before_widget;
		if ( !empty( $widget_title ) ) { echo $before_title . $widget_title . $after_title; }
		echo $widget_content . $after_widget;
		
		$wppa['in_widget'] = false;
    }
	
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {	
		global $wppa_opt;
		//Defaults
		$instance = wp_parse_args( (array) $instance, array(  'title' => $wppa_opt['wppa_widgettitle']) );
		$widget_title = $instance['title']; 
		?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wppa'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $widget_title; ?>" /></p>
			<p><?php _e('You can set the content and the sizes in this widget in the <b>Photo Albums -> Sidebar Widget</b> admin page.', 'wppa'); ?></p>
		<?php
    }

} // class PhotoOfTheDay

require_once ('wppa-widget-functions.php');

// register PhotoOfTheDay widget
add_action('widgets_init', create_function('', 'return register_widget("PhotoOfTheDay");'));
