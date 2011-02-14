<?php
/* wppa_slideshow_widget.php
* Package: wp-photo-album-plus
*
* display a slideshow in the sidebar
* Version 2.5.1.003
*/

/* load_plugin_textdomain('wppa', 'wp-content/plugins/lege-widget/langs/', 'lege-widget/langs/');
/**
 * SlideshowWidget Class
 */
class SlideshowWidget extends WP_Widget {
    /** constructor */
    function SlideshowWidget() {
        parent::WP_Widget(false, $name = 'Sidebar Slideshow');	
		$widget_ops = array('classname' => 'slideshow_widget', 'description' => __( 'WPPA+ Sidebar Slideshow', 'wppa') );	//
		$this->WP_Widget('slideshow_widget', __('Sidebar Slideshow', 'wppa'), $widget_ops);															
		
    }

	/** @see WP_Widget::widget */
    function widget($args, $instance) {		
		global $wpdb;
		global $widget_content;
		global $wppa_in_widget;
		global $wppa_portrait_only;
		global $wppa_in_widget_linkurl;
		global $wppa_in_widget_linktitle;
		global $wppa_in_widget_timeout;
		global $wppa_ss_widget_valign;
		global $wppa_fullsize;

        extract( $args );
        
 		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Sidebar Slideshow', 'wppa' ) : $instance['title']);

		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'album' => '', 'width' => get_option('wppa_widget_width', '190'), 'ponly' => 'no', 'linkurl' => '', 'linktitle' => '', 'subtext' => '', 'supertext' => '', 'valign' => 'fit', 'timeout' => '4' ) );

		$album = $instance['album'];
		$width = $instance['width'];
		$ponly = $instance['ponly'];
		$linkurl = $instance['linkurl'];
		$linktitle = $instance['linktitle'];
		$supertext = $instance['supertext'];
		$subtext = $instance['subtext'];
		$valign = $instance['valign'];
		$timeout = $instance['timeout'] * 1000;
		
		if (is_numeric($album)) {
			echo $before_widget . $before_title . $title . $after_title;
				if ($linkurl != '') {
					$wppa_in_widget_linkurl = $linkurl;
					$wppa_in_widget_linktitle = $linktitle;
				}
				if ($supertext != '') {
					echo '<div style="padding-top:2px; padding-bottom:4px; text-align:center">'.$supertext.'</div>';
				}
				echo '<div style="padding-top:2px; padding-bottom:4px;" >';
					$wppa_in_widget = true;
						$wppa_in_widget_timeout = $timeout;
						$wppa_portrait_only = ($ponly == 'yes');
							$wppa_ss_widget_valign = $valign;
								wppa_albums($album, 'slideonly', $width, 'center');
							$wppa_ss_widget_valign = '';
						$wppa_portrait_only = false;
						$wppa_in_widget_timeout = '0';
					$wppa_in_widget = false;
					$wppa_fullsize = '';	// Reset to prevent inheritage of wrong size in case widget is rendered before main column
				echo '</div>';
				if ($linkurl != '') {
					$wppa_in_widget_linkurl = '';
					$wppa_in_widget_linktitle = '';
				}
				if ($subtext != '') {
					echo '<div style="padding-top:2px; padding-bottom:0px; text-align:center">'.$subtext.'</div>';
				}
			echo $after_widget;
		}
		else {
			echo $before_widget . $before_title . $title . $after_title;
			echo __('No album defined yet.', 'wppa');
			echo $after_widget;
		}

		//echo $before_widget . $before_title . $title . $after_title . $widget_content . $after_widget;
    }
	
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['album'] = $new_instance['album'];
		$instance['width'] = $new_instance['width'];
		$instance['ponly'] = $new_instance['ponly'];
		$instance['linkurl'] = $new_instance['linkurl'];
		$instance['linktitle'] = $new_instance['linktitle'];
		$instance['supertext'] = $new_instance['supertext'];
		$instance['subtext'] = $new_instance['subtext'];
		if ($instance['ponly'] == 'yes') {
			$instance['valign'] = 'fit';
		}
		else {
			$instance['valign'] = $new_instance['valign'];
		}
		$instance['timeout'] = $new_instance['timeout'];
		
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'album' => '', 'width' => get_option('wppa_widget_width', '190'), 'ponly' => 'no', 'linkurl' => '', 'linktitle' => '', 'subtext' => '', 'supertext' => '', 'valign' => 'center', 'timeout' => '4' ) );
		$title = esc_attr( $instance['title'] );
		$album = $instance['album'];
		$width = $instance['width'];
		$ponly = $instance['ponly'];
		$linkurl = $instance['linkurl'];
		$linktitle = $instance['linktitle'];
		$supertext = $instance['supertext'];
		$subtext = $instance['subtext'];
		$valign = $instance['valign'];
		$timeout = $instance['timeout'];
		
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wppa'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('album'); ?>"><?php _e('Album:', 'wppa'); ?></label> <select id="<?php echo $this->get_field_id('album'); ?>" name="<?php echo $this->get_field_name('album'); ?>"><?php echo(wppa_album_select('', $album)) ?></select></p>
		<p><label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width:', 'wppa'); ?></label> <input class="widefat" style="width:15%;" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" />&nbsp;<?php _e('pixels.', 'wppa') ?></p>
		<p>
			<?php _e('Portrait only:', 'wppa'); ?>
			<select id="<?php echo $this->get_field_id('ponly'); ?>" name="<?php echo $this->get_field_name('ponly'); ?>">
				<option value="no" <?php if ($ponly == 'no') echo 'selected="selected"' ?>><?php _e('no.', 'wppa'); ?></option>
				<option value="yes" <?php if ($ponly == 'yes') echo 'selected="selected"' ?>><?php _e('yes.', 'wppa'); ?></option>
			</select>&nbsp;<?php _e('Set to \'yes\' if there are only portrait images in the album and you want the photos to fill the full width of the widget.<br/>Set to \'no\' otherwise.', 'wppa') ?>
			&nbsp;<?php _e('If set to \'yes\', Vertical alignment will be forced to \'fit\'.', 'wppa') ?>
		</p>
		<p>
			<?php _e('Vertical alignment:', 'wppa'); ?>
			<select id="<?php echo $this->get_field_id('valign'); ?>" name="<?php echo $this->get_field_name('valign'); ?>">
				<option value="top" <?php if ($valign == 'top') echo(' selected '); ?>><?php _e('top', 'wppa'); ?></option>
				<option value="center" <?php if ($valign == 'center') echo(' selected '); ?>><?php _e('center', 'wppa'); ?></option>
				<option value="bottom" <?php if ($valign == 'bottom') echo(' selected '); ?>><?php _e('bottom', 'wppa'); ?></option>
				<option value="fit" <?php if ($valign == 'fit') echo(' selected '); ?>><?php _e('fit', 'wppa'); ?></option>	
			</select><br/><?php _e('Set the desired vertical alignment method.', 'wppa'); ?>
		</p>
		<p><label for="<?php echo $this->get_field_id('linkurl'); ?>"><?php _e('Link to:', 'wppa'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('linkurl'); ?>" name="<?php echo $this->get_field_name('linkurl'); ?>" type="text" value="<?php echo $linkurl; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('linktitle'); ?>"><?php _e('Tooltip text:', 'wppa'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('linktitle'); ?>" name="<?php echo $this->get_field_name('linktitle'); ?>" type="text" value="<?php echo $linktitle; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('supertext'); ?>"><?php _e('Text above photos:', 'wppa'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('supertext'); ?>" name="<?php echo $this->get_field_name('supertext'); ?>" type="text" value="<?php echo $supertext; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('subtext'); ?>"><?php _e('Text below photos:', 'wppa'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('subtext'); ?>" name="<?php echo $this->get_field_name('subtext'); ?>" type="text" value="<?php echo $subtext; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('timeout'); ?>"><?php _e('Slideshow timeout:', 'wppa'); ?></label> <input class="widefat" style="width:15%;" id="<?php echo $this->get_field_id('timeout'); ?>" name="<?php echo $this->get_field_name('timeout'); ?>" type="text" value="<?php echo $timeout; ?>" />&nbsp;<?php _e('sec.', 'wppa'); ?></p>
<?php
    }

} // class SlideshowWidget

// register SlideshowWidget widget
add_action('widgets_init', create_function('', 'return register_widget("SlideshowWidget");'));
?>
