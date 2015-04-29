<?php
/* wppa-featen-widget.php
* Package: wp-photo-album-plus
*
* display the featured photos
* Version 6.1.3
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

 		require_once(dirname(__FILE__) . '/wppa-links.php');
		require_once(dirname(__FILE__) . '/wppa-styles.php');
		require_once(dirname(__FILE__) . '/wppa-functions.php');
		require_once(dirname(__FILE__) . '/wppa-thumbnails.php');
		require_once(dirname(__FILE__) . '/wppa-boxes-html.php');
		require_once(dirname(__FILE__) . '/wppa-slideshow.php');
		wppa_initialize_runtime();

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
			$thumbs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `status`= 'featured' AND `album` = %s ORDER BY RAND(".wppa_get_randseed().") DESC LIMIT " . $max, $album ), ARRAY_A );
		}
		else {
			$thumbs = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `status` = 'featured' ORDER BY RAND(".wppa_get_randseed().") DESC LIMIT " . $max, ARRAY_A );
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
				
				$album = '0';
				$display = 'thumbs';

				$widget_content .= wppa_get_the_widget_thumb('featen', $image, $album, $display, $link, $title, $imgurl, $imgstyle_a, $imgevents);
	
			}
			else {	// No image
				$widget_content .= __a('Photo not found.', 'wppa_theme');
			}
			
			$widget_content .= "\n".'</div>';
			
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
