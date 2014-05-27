<?php
/* wppa-bestof-widget.php
* Package: wp-photo-album-plus
*
* display the best rated photos
* Version 5.3.6
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

class BestOfWidget extends WP_Widget {
    /** constructor */
    function BestOfWidget() {
        parent::WP_Widget(false, $name = 'Best Of Photos');	
		$widget_ops = array('classname' => 'wppa_bestof_widget', 'description' => __( 'WPPA+ Best Of Rated Photos', 'wppa') );
		$this->WP_Widget('wppa_bestof_widget', __('Best Of Photos', 'wppa'), $widget_ops);
    }

	/** @see WP_Widget::widget */
    function widget( $args, $instance ) {		
		global $wpdb;
		global $wppa_opt;
		global $wppa;
		global $widget_content;
		global $thumb;

        $wppa['in_widget'] = 'bestof';
		$wppa['master_occur']++;
		
		extract( $args );

		$instance 		= wp_parse_args( (array) $instance, array( 
														'title' 	=> '',
														'count' 	=> '1',
														'sortby' 	=> 'maxratingcount', 
														'display' 	=> 'photo',
														'period' 	=> 'thisweek',
														'maxratings'=> 'yes',
														'meanrat' 	=> 'yes',
														'ratcount' 	=> 'yes',
														'linktype' 	=> 'none',
														) );	
												
 		$widget_title 	= apply_filters('widget_title', $instance['title'] );
		$page 			= in_array( $instance['linktype'], $wppa['links_no_page'] ) ? '' : wppa_get_the_landing_page('wppa_bestof_widget_linkpage', __a('Best Of Photos'));
		$count 			= $instance['count'];
		$sortby 		= $instance['sortby'];
		$display 		= $instance['display'];
		$period 		= $instance['period'];
		$maxratings 	= $instance['maxratings'];
		$meanrat		= $instance['meanrat'];
		$ratcount 		= $instance['ratcount'];
		$linktype 		= $instance['linktype'];
		$size 			= $wppa_opt['wppa_widget_width'];
//		$data 			= wppa_get_the_bestof( $count, $period, $sortby, $display );
		$lineheight 	= $wppa_opt['wppa_fontsize_widget_thumb'] * 1.5;

		$widget_content = "\n".'<!-- WPPA+ BestOf Widget start -->';
		
		$widget_content .= wppa_bestof_html( array ( 	'page' 			=> $page,
														'count' 		=> $count,
														'sortby' 		=> $sortby,
														'display' 		=> $display,
														'period' 		=> $period,
														'maxratings' 	=> $maxratings,
														'meanrat' 		=> $meanrat,
														'ratcount' 		=> $ratcount,
														'linktype' 		=> $linktype,
														'size' 			=> $size,
														'lineheight' 	=> $lineheight

														) );

		$widget_content .= '<div style="clear:both"></div>';
		$widget_content .= "\n".'<!-- WPPA+ BestOf Widget end -->';

		echo "\n" . $before_widget;
		if ( !empty( $widget_title ) ) { echo $before_title . $widget_title . $after_title; }
		echo $widget_content . $after_widget;
    }
	
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['title'] 		= strip_tags($new_instance['title']);
		$instance['count'] 		= $new_instance['count'];
		$instance['sortby'] 	= $new_instance['sortby'];
		$instance['display'] 	= $new_instance['display'];
		$instance['period'] 	= $new_instance['period'];
		$instance['maxratings'] = $new_instance['maxratings'];
		$instance['meanrat']	= $new_instance['meanrat'];
		$instance['ratcount'] 	= $new_instance['ratcount'];
		$instance['linktype'] 	= $new_instance['linktype'];
		
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {	
		global $wppa_opt;
		//Defaults
		$instance 		= wp_parse_args( (array) $instance, array( 
														'title' 	=> __('Best Of Photos', 'wppa'), 
														'count' 	=> '1',
														'sortby' 	=> 'maxratingcount', 
														'display' 	=> 'photo',
														'period' 	=> 'thisweek',
														'maxratings'=> 'yes',
														'meanrat' 	=> 'yes',
														'ratcount' 	=> 'yes',
														'linktype' 	=> 'none'
														) );

 		$widget_title 	= $instance['title'];
		$count 			= $instance['count'];
		$sortby 		= $instance['sortby'];
		$display 		= $instance['display'];
		$period 		= $instance['period'];
		$maxratings 	= $instance['maxratings'];
		$meanrat		= $instance['meanrat'];
		$ratcount 		= $instance['ratcount'];
		$linktype		= $instance['linktype'];

		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wppa'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $widget_title; ?>" />
		</p>
		<?php
		
		$count_html 	= '
		<select id="'.$this->get_field_id('count').'" name="'.$this->get_field_name('count').'" >
			<option value="1" '.( $count == '1' ? 'selected="selected"' : '' ).' >1</option>
			<option value="2" '.( $count == '2' ? 'selected="selected"' : '' ).' >2</option>
			<option value="3" '.( $count == '3' ? 'selected="selected"' : '' ).' >3</option>
			<option value="4" '.( $count == '4' ? 'selected="selected"' : '' ).' >4</option>
			<option value="5" '.( $count == '5' ? 'selected="selected"' : '' ).' >5</option>
			<option value="6" '.( $count == '6' ? 'selected="selected"' : '' ).' >6</option>
			<option value="7" '.( $count == '7' ? 'selected="selected"' : '' ).' >7</option>
			<option value="8" '.( $count == '8' ? 'selected="selected"' : '' ).' >8</option>
			<option value="9" '.( $count == '9' ? 'selected="selected"' : '' ).' >9</option>
			<option value="10" '.( $count == '10' ? 'selected="selected"' : '' ).' >10</option>
		</select>
		';
		$what_html 		= '
		<select id="'.$this->get_field_id('display').'" name="'.$this->get_field_name('display').'" >
			<option value="photo" '.( $display == 'photo' ? 'selected="selected"' : '' ).' >'.__('Photo(s)', 'wppa').'</option>
			<option value="owner" '.( $display == 'owner' ? 'selected="selected"' : '' ).' >'.__('Owner(s)', 'wppa').'</option>
		</select>
		';
		$period_html 	= '
		<select id="'.$this->get_field_id('period').'" name="'.$this->get_field_name('period').'" >
			<option value="lastweek" '.( $period == 'lastweek' ? 'selected="selected"' : '' ).' >'.__('Last week', 'wppa').'</option>
			<option value="thisweek" '.( $period == 'thisweek' ? 'selected="selected"' : '' ).' >'.__('This week', 'wppa').'</option>
			<option value="lastmonth" '.( $period == 'lastmonth' ? 'selected="selected"' : '' ).' >'.__('Last month', 'wppa').'</option>
			<option value="thismonth" '.( $period == 'thismonth' ? 'selected="selected"' : '' ).' >'.__('This month', 'wppa').'</option>
			<option value="lastyear" '.( $period == 'lastyear' ? 'selected="selected"' : '' ).' >'.__('Last year', 'wppa').'</option>
			<option value="thisyear" '.( $period == 'thisyear' ? 'selected="selected"' : '' ).' >'.__('This year', 'wppa').'</option>
		</select>
		';
		$sort_html 		= '
		<select id="'.$this->get_field_id('sortby').'" name="'.$this->get_field_name('sortby').'" >
			<option value="maxratingcount" '.( $sortby == 'maxratingcount' ? 'selected="selected"' : '' ).' >'.__('Number of max ratings', 'wppa').'</option>
			<option value="meanrating" '.( $sortby == 'meanrating' ? 'selected="selected"' : '' ).' >'.__('Mean value', 'wppa').'</option>
			<option value="ratingcount" '.( $sortby == 'ratingcount' ? 'selected="selected"' : '' ).' >'.__('Number of votes', 'wppa').'</option>
		</select>
		';
		
		_e( sprintf( 'Display the %1s best %2s based on ratings given during %3s and sorted by %4s', $count_html, $what_html, $period_html, $sort_html ) );
		
?>		
	
		<p><label ><?php _e('Subtitle:', 'wppa'); ?></label>
			<br /><?php _e('No of max ratings:', 'wppa'); ?>
			<select id="<?php echo $this->get_field_id('maxratings'); ?>" name="<?php echo $this->get_field_name('maxratings'); ?>" >
				<option value="yes" <?php if ( $meanrat == 'yes' ) echo 'selected="selected"' ?>><?php _e('yes', 'wppa') ?></option>
				<option value="no" <?php if ( $meanrat == 'no' ) echo 'selected="selected"' ?>><?php _e('no', 'wppa') ?></option>
			</select>
			<br /><?php _e('Mean rating:', 'wppa'); ?>
			<select id="<?php echo $this->get_field_id('meanrat'); ?>" name="<?php echo $this->get_field_name('meanrat'); ?>" >
				<option value="yes" <?php if ( $meanrat == 'yes' ) echo 'selected="selected"' ?>><?php _e('yes', 'wppa') ?></option>
				<option value="no" <?php if ( $meanrat == 'no' ) echo 'selected="selected"' ?>><?php _e('no', 'wppa') ?></option>
			</select>
			<br /><?php _e('Rating count:', 'wppa'); ?>
			<select id="<?php echo $this->get_field_id('ratcount'); ?>" name="<?php echo $this->get_field_name('ratcount'); ?>" >
				<option value="yes" <?php if ( $ratcount == 'yes' ) echo 'selected="selected"' ?>><?php _e('yes', 'wppa') ?></option>
				<option value="no" <?php if ( $ratcount == 'no' ) echo 'selected="selected"' ?>><?php _e('no', 'wppa') ?></option>
			</select>
		</p>
		
		<p><label><?php _e('Link to:', 'wppa') ?></label>
			<select id="<?php echo $this->get_field_id('linktype'); ?>" name="<?php echo $this->get_field_name('linktype'); ?>" >
				<option value="none" <?php if ( $linktype == 'none' ) echo 'selected="selected"' ?>><?php _e('--- none ---', 'wppa') ?></option>
				<option value="owneralbums" <?php if ( $linktype == 'owneralbums' ) echo 'selected="selected"' ?>><?php _e('The authors album(s)', 'wppa') ?></option>
				<option value="ownerphotos" <?php if ( $linktype == 'ownerphotos' ) echo 'selected="selected"' ?>><?php _e('The photos in the authors album(s)', 'wppa') ?></option>
				<option value="upldrphotos" <?php if ( $linktype == 'upldrphotos' ) echo 'selected="selected"' ?>><?php _e('All the authors photos', 'wppa') ?></option>
			</select>
		</p>

<?php
    }

} // class BestOfWidget

// register BestOfWidget widget
add_action( 'widgets_init', create_function( '', 'return register_widget("BestOfWidget" );' ) );

