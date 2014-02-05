<?php
/* wppa-bestof-widget.php
* Package: wp-photo-album-plus
*
* display the best rated photos
* Version 5.2.11
*/

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
		$page 			= wppa_get_the_landing_page('wppa_bestof_widget_linkpage', __a('Best Of Photos'));
		$count 			= $instance['count'];
		$sortby 		= $instance['sortby'];
		$display 		= $instance['display'];
		$period 		= $instance['period'];
		$maxratings 	= $instance['maxratings'];
		$meanrat		= $instance['meanrat'];
		$ratcount 		= $instance['ratcount'];
		$linktype 		= $instance['linktype'];
		$size 			= $wppa_opt['wppa_widget_width'];
		$data 			= wppa_get_the_bestof( $count, $period, $sortby, $display );
		$lineheight 	= $wppa_opt['wppa_fontsize_widget_thumb'] * 1.5;

		$widget_content = "\n".'<!-- WPPA+ BestOf Widget start -->';

		if ( $display == 'photo' ) {
			if ( is_array( $data ) ) {
				foreach ( array_keys( $data ) as $id ) {
					wppa_cache_thumb( $id );
					if ( $thumb ) {
						$maxw 			= $size;
						$imgsize 		= getimagesize( wppa_get_thumb_path( $id ) );
						$maxh 			= $maxw * $imgsize['1'] / $imgsize['0'];
						$totalh 		= $maxh + $lineheight;
						if ( $maxratings == 'yes' ) $totalh += $lineheight;
						if ( $meanrat == 'yes' ) 	$totalh += $lineheight;
						if ( $ratcount == 'yes' ) 	$totalh += $lineheight;

						$widget_content .= "\n".'<div class="wppa-widget" style="clear:both; width:'.$maxw.'px; height:'.$totalh.'px; margin:4px; display:inline; text-align:center; float:left;">'; 
					

							// The link if any
							if ( $linktype != 'none' ) {
								switch ( $linktype ) {
									case 'owneralbums':
										$href = wppa_get_permalink($page).'wppa-cover=1&wppa-owner='.$thumb['owner'].'&wppa-occur=1';
										$title = __a('See the authors albums', 'wppa');
										break;
									case 'ownerphotos':
										$href = wppa_get_permalink($page).'wppa-cover=0&wppa-owner='.$thumb['owner'].'&photos-only&wppa-occur=1';
										$title = __a('See the authors photos', 'wppa');
										break;
									case 'upldrphotos':
										$href = wppa_get_permalink($page).'wppa-cover=0&wppa-upldr='.$thumb['owner'].'&wppa-occur=1';
										$title = __a('See all the authors photos', 'wppa');
										break;
								}
								$widget_content .= '<a href="'.$href.'" title="'.$title.'" >';
							}
							
							// The image
							$widget_content .= '<img style="height:'.$maxh.'px; width:'.$maxw.'px;" src="'.wppa_get_photo_url( $id, '', $maxw, $maxh ).'" />';
							
							// The /link
							if ( $linktype != 'none' ) {
								$widget_content .= '</a>';
							}
							
							// The medal
							$widget_content .= wppa_get_medal_html( $id, $maxh );

							// The subtitles
							$widget_content .= "\n\t".'<div style="font-size:'.$wppa_opt['wppa_fontsize_widget_thumb'].'px; line-height:'.$lineheight.'px; position:absolute; width:'.$size.'px;">';
								$widget_content .= sprintf( __a( 'Photo by: %s' ), $data[$id]['user'] ).'<br />';
								if ( $maxratings 	== 'yes' ) $widget_content .= sprintf( __a( 'Max ratings: %s.' ), $data[$id]['maxratingcount'] ).'<br />';
								if ( $ratcount 		== 'yes' ) $widget_content .= sprintf( __a( 'Votes: %s.' ), $data[$id]['ratingcount'] ).'<br />';
								if ( $meanrat  		== 'yes' ) $widget_content .= sprintf( __a( 'Mean value: %4.2f.' ), $data[$id]['meanrating'] ).'<br />';
							$widget_content .= '</div>';
							$widget_content .= '<div style="clear:both" ></div>';
							
						$widget_content .= "\n".'</div>';
					}
					else {	// No image
						$widget_content .= '<div>'.sprintf( __a('Photo %s not found.'), $id ).'</div>';
					}
				}
			}	
			else {
				$widget_content .= $data;	// No array, print message
			}
		}
		else {	// Display = owner
			if ( is_array( $data ) ) {
				$widget_content .= '<ul>';
				foreach ( array_keys( $data ) as $author ) {
					$widget_content .= '<li>';
					// The link if any
					if ( $linktype != 'none' ) {
						switch ( $linktype ) {
							case 'owneralbums':
								$href = wppa_get_permalink($page).'wppa-cover=1&wppa-owner='.$data[$author]['owner'].'&wppa-occur=1';
								$title = __a('See the authors albums', 'wppa');
								break;
							case 'ownerphotos':
								$href = wppa_get_permalink($page).'wppa-cover=0&wppa-owner='.$data[$author]['owner'].'&photos-only&wppa-occur=1';
								$title = __a('See the authors photos', 'wppa');
								break;
							case 'upldrphotos':
								$href = wppa_get_permalink($page).'wppa-cover=0&wppa-upldr='.$data[$author]['owner'].'&wppa-occur=1';
								$title = __a('See all the authors photos', 'wppa');
								break;
						}
						$widget_content .= '<a href="'.$href.'" title="'.$title.'" >';
					}
					
					// The name
					$widget_content .= $author;

					// The /link
					if ( $linktype != 'none' ) {
						$widget_content .= '</a>';
					}
					
					$widget_content .= '<br/>';
					
					// The subtitles
					$widget_content .= "\n\t".'<div style="font-size:'.$wppa_opt['wppa_fontsize_widget_thumb'].'px; line-height:'.$lineheight.'px; ">';
								if ( $maxratings 	== 'yes' ) $widget_content .= sprintf( __a( 'Max ratings: %s.' ), $data[$author]['maxratingcount'] ).'<br />';
								if ( $ratcount 		== 'yes' ) $widget_content .= sprintf( __a( 'Votes: %s.' ), $data[$author]['ratingcount'] ).'<br />';
								if ( $meanrat  		== 'yes' ) $widget_content .= sprintf( __a( 'Mean value: %4.2f.' ), $data[$author]['meanrating'] ).'<br />';
					
					$widget_content .= '</div>';
					$widget_content .= '</li>';
				}
				$widget_content .= '</ul>';
			}
			else {
				$widget_content .= $data;	// No array, print message
			}
		}
		
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
if ( get_option( 'wppa_rating_on', 'yes' ) == 'yes' ) add_action( 'widgets_init', create_function( '', 'return register_widget("BestOfWidget" );' ) );

function wppa_get_the_bestof( $count, $period, $sortby, $what ) {
global $wppa_opt;
global $wpdb;
global $thumb;

	// Phase 1, find the period we are talking about
	// find $start and $end
	switch ( $period ) {
		case 'lastweek':
			$start 	= wppa_get_timestamp( 'lastweekstart' );
			$end   	= wppa_get_timestamp( 'lastweekend' );
			break;
		case 'thisweek':
			$start 	= wppa_get_timestamp( 'thisweekstart' );
			$end   	= wppa_get_timestamp( 'thisweekend' );
			break;
		case 'lastmonth':
			$start 	= wppa_get_timestamp( 'lastmonthstart' );
			$end 	= wppa_get_timestamp( 'lastmonthend' );
			break;
		case 'thismonth':
			$start 	= wppa_get_timestamp( 'thismonthstart' );
			$end 	= wppa_get_timestamp( 'thismonthend' );
			break;
		case 'lastyear':
			$start 	= wppa_get_timestamp( 'lastyearstart' );
			$end 	= wppa_get_timestamp( 'lastyearend' );
			break;
		case 'thisyear':
			$start 	= wppa_get_timestamp( 'thisyearstart' );
			$end 	= wppa_get_timestamp( 'thisyearend' );
			break;
		default:
			return 'Unimplemented period: '.$period;
	}
	
	// Phase 2, get the ratings of the period
	// find $ratings, ordered by photo id
	$ratings 	= $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_RATING."` WHERE `timestamp` >= %s AND `timestamp` < %s ORDER BY `photo`", $start, $end ), ARRAY_A );

	// Phase 3, set up an array with data we need
	// There are two methods: photo oriented and owner oriented, depending on 
	
	// Each element reflects a photo ( key = photo id ) and is an array with items: maxratings, meanrating, ratings, totvalue.
	$ratmax	= $wppa_opt['wppa_rating_max'];
	$data 	= array();
	foreach ( $ratings as $rating ) {
		$key = $rating['photo'];
		if ( ! isset( $data[$key] ) ) {
			$data[$key] = array();
			$data[$key]['ratingcount'] 		= '1';
			$data[$key]['maxratingcount'] 	= $rating['value'] == $ratmax ? '1' : '0';
			$data[$key]['totvalue'] 		= $rating['value'];
		}
		else {
			$data[$key]['ratingcount'] 		+= '1';
			$data[$key]['maxratingcount'] 	+= $rating['value'] == $ratmax ? '1' : '0';
			$data[$key]['totvalue'] 		+= $rating['value'];
		}
	}
	foreach ( array_keys( $data ) as $key ) {
		wppa_cache_thumb( $key );
		$data[$key]['meanrating'] = $data[$key]['totvalue'] / $data[$key]['ratingcount'];
		$user = get_user_by( 'login', $thumb['owner'] );
		if ( $user ) {
			$data[$key]['user'] = $user->display_name;
		}
		else { // user deleted
			$data[$key]['user'] = $thumb['owner'];
		}
		$data[$key]['owner'] = $thumb['owner'];
	}
	
	// Now we split into search for photos and search for owners
	
	if ( $what == 'photo' ) {
	
		// Pase 4, sort to the required sequence
		$data = wppa_array_sort( $data, $sortby, SORT_DESC );
		
	}
	else { 	// $what == 'owner'
	
		// Phase 4, combine all photos of the same owner
		wppa_array_sort( $data, 'user' );
		$temp = $data;
		$data = array();
		foreach ( array_keys( $temp ) as $key ) {
			if ( ! isset( $data[$temp[$key]['user']] ) ) {
				$data[$temp[$key]['user']]['photos'] 			= '1';
				$data[$temp[$key]['user']]['ratingcount'] 		= $temp[$key]['ratingcount'];
				$data[$temp[$key]['user']]['maxratingcount'] 	= $temp[$key]['maxratingcount'];
				$data[$temp[$key]['user']]['totvalue'] 			= $temp[$key]['totvalue'];
				$data[$temp[$key]['user']]['owner'] 			= $temp[$key]['owner'];
			}
			else {
				$data[$temp[$key]['user']]['photos'] 			+= '1';
				$data[$temp[$key]['user']]['ratingcount'] 		+= $temp[$key]['ratingcount'];
				$data[$temp[$key]['user']]['maxratingcount'] 	+= $temp[$key]['maxratingcount'];
				$data[$temp[$key]['user']]['totvalue'] 			+= $temp[$key]['totvalue'];
			}
		}
		foreach ( array_keys( $data ) as $key ) {
			$data[$key]['meanrating'] = $data[$key]['totvalue'] / $data[$key]['ratingcount'];
		}
		$data = wppa_array_sort( $data, $sortby, SORT_DESC );
	}
	
	// Phase 5, truncate to the desired length
	$c = '0';
	foreach ( array_keys( $data ) as $key ) {
		$c += '1';
		if ( $c > $count ) unset ( $data[$key] );
	}

	// Phase 6, return the result
	if ( count( $data ) ) {
		return $data;
	}
	else {
		return 'There are no ratings between <br />'.wppa_local_date( 'F j, Y, H:i s', $start ).' and <br />'.wppa_local_date( 'F j, Y, H:i s', $end ).'.';
	}
}

function wppa_get_timestamp( $key = false ) {
	
	$timnow = time();
	$format = 'Y:z:n:j:W:w:G:i:s';
	//         0 1 2 3 4 5 6 7 8
	// Year(2014):dayofyear(0-365):month(1-12):dayofmonth(1-31):Weeknumber(1-53):dayofweek(0-6):hour(0-23):min(0-59):sec(0-59)
	$local_date_time = wppa_local_date( $format, $timnow );

	$data = explode( ':', $local_date_time );
	$data[4] = ltrim( '0', $data[4] );
	
	$today_start = $timnow - $data[8] - 60 * $data[7] - 3600 * $data[6];
	if ( $key == 'todaystart' ) return $today_start;
	
	$daysec = 24 * 3600;
	
	if ( ! $data[5] ) $data[5] = 7;	// Sunday
	$thisweek_start = $today_start - $daysec * ( $data[5] - 1 );	// Week starts on monday
	if ( $key == 'thisweekstart' ) return $thisweek_start;
	if ( $key == 'lastweekend' ) return $thisweek_start;
	
	$thisweek_end = $thisweek_start + 7 * $daysec;
	if ( $key == 'thisweekend' ) return $thisweek_end;
	
	$lastweek_start = $thisweek_start - 7 * $daysec;
	if ( $key == 'lastweekstart' ) return $lastweek_start;
	
	$thismonth_start = $today_start - ( $data[3] - 1 ) * $daysec;
	if ( $key == 'thismonthstart' ) return $thismonth_start;
	if ( $key == 'lastmonthend' ) return $thismonth_start;
	
	$monthdays = array ( '0', '31', '28', '31', '30', '31', '30', '31', '31', '30', '31', '30', '31' );
	$monthdays[2] += wppa_local_date('L', $timnow );	// Leap year correction

	$thismonth_end = $thismonth_start + $monthdays[$data[2]] * $daysec;
	if ( $key == 'thismonthend' ) return $thismonth_end;
	
	$lm = $data[2] > 1 ? $data[2] - 1 : 12;
	$lastmonth_start = $thismonth_start - $monthdays[$lm] * $daysec;
	if ( $key == 'lastmonthstart' ) return $lastmonth_start;
	
	$thisyear_start = $thismonth_start;
	$idx = $data[2];
	while ( $idx > 1 ) {
		$idx--;
		$thisyear_start -= $monthdays[$idx] * $daysec;
	}
	if ( $key == 'thisyearstart' ) return $thisyear_start;
	if ( $key == 'lastyearend' ) return $thisyear_start;
	
	$thisyear_end = $thisyear_start;
	foreach ( $monthdays as $month ) $thisyear_end += $month * $daysec;
	if ( $key == 'thisyearend' ) return $thisyear_end;
	
	$lastyear_start = $thisyear_start - 365 * $daysec;
	if ( wppa_local_date('L', $thisyear_start - $daysec) ) $lastyear_start -= $daysec;	// Last year was a leap year
	if ( $key == 'lastyearstart' ) return $lastyear_start;
	
	return $timnow;
}
