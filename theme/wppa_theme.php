<?php 
/* wppa_theme.php
* Package: wp-photo-album-plus
*
* display the albums/photos/slideshow in a page or post
* Version 2.3.0
*/
global $wppa_fullsize;
global $is_cover;
global $wppa_occur;
global $wppa_master_occur;
global $single_photo;
global $wppa_src;

$alt = 'even';
$mincount = $wppa_src ? '0' : get_option('wppa_min_thumbs', '1');
$coversize = get_option('wppa_smallsize');
$thumbsize = get_option('wppa_thumbsize');
$albumpagesize = wppa_get_pagesize('albums');
$thumbpagesize = wppa_get_pagesize('thumbs');
if (isset($_GET['occur'])) $oc = $_GET['occur']; else $oc = '1';
if (isset($_GET['page']) && $wppa_occur == $oc) $curpage = $_GET['page']; else $curpage = '1';
$counter = '0';
$occ = '&occur=' . $wppa_occur;

$didsome = false;

$nofalbumpages = '0';
$nofthumbpages = '0';

echo('</p>');	// Close wpautop generated paragraph

if (!wppa_page('oneofone')) {	// NOT for very single photos
	wppa_breadcrumb('&raquo;', 'optional');		// Display breadcrumb navigation only if it is set in the settings page
}

if ($wppa_master_occur == '1') wppa_set_runtimestyle();		// Import colors, borders and fonts from settings page
if (wppa_page('albums')) { 

    $albums = wppa_get_albums(); 

    if ($albums) { 
		if ($albumpagesize != '0') {
			$nofalbumpages = ceil(count($albums) / $albumpagesize); 
		} 
		else {
			if ($thumbpagesize != '0') {
				$nofalbumpages = '1';
			} 
			else {
				$nofalbumpages = '0';
			}
		}
?>
    <div id="albumlist_<?php echo($wppa_occur) ?>" class="albumlist">
<?php
        foreach ($albums as $ta) :  global $album; $album = $ta;
			$counter++;
			if (wppa_onpage($counter, $curpage, $albumpagesize)) {
				$didsome = true;
				$coverphoto = wppa_get_coverphoto_id();
				$photocount = wppa_get_photo_count();
				$albumcount = wppa_get_album_count();
				if (is_numeric($album['cover_linkpage']) && $album['cover_linkpage'] > 0) {
					$page_data = get_page($album['cover_linkpage']);
					if (!empty($page_data) && $page_data->post_status == 'publish') {
						$href = get_page_link($album['cover_linkpage']); // . $occ;
						$title = __('Link to', 'wppa');
						$title .= ' ' . $page_data->post_title;
					} else {
						$href = '#';
						$title = __('Page is not available.');
					}
				} elseif (is_numeric($album['cover_linkpage']) && $album['cover_linkpage'] == -1) {
					$href = '';
					$title = '';
				} else {
					if ($photocount != '0' && $photocount) {
						$href = wppa_get_image_page_url_by_id($coverphoto) . $occ; 
						$title = __('View the cover photo', 'wppa'); 
							if ($photocount > 1) $title .= __('s', 'wppa');
					} else {
						$href = wppa_get_album_url() . $occ; 
						$title = __('View the album', 'wppa') . ' ' . $album['name'];
					}
				}
				$src = wppa_get_thumb_url_by_id($coverphoto);	
				$path = wppa_get_thumb_path_by_id($coverphoto);
				$imgattr = wppa_get_imgstyle($path, $coversize, '', 'cover');
				$events = wppa_get_imgevents('cover');
	?>
				<div class="album wppa-box wppa-<?php echo($alt); ?>">
					<?php if ($src != '') { ?>
						<div id="coverphoto_frame_<?php echo($album['id'].'_'.$wppa_occur) ?>" class="coverphoto_frame">
						<?php if ($href != '') {?>
							<a href="<?php echo($href); ?>" title="<?php echo($title); ?>">
								<img src="<?php echo($src); ?>" alt="<?php echo($title); ?>" class="image wppa-img" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/>
							</a>
						<?php } else { ?>
							<img src="<?php echo($src); ?>" alt="<?php echo($title); ?>" class="image wppa-img" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/>
						<?php } ?>
						</div>
					<?php } ?>
					<h2 class="wppa-title name" style="clear:none;">
						<?php if ($href != '') { ?>
						<a href="<?php echo($href); ?>" title="<?php echo($title); ?>"><?php echo(stripslashes($album['name'])); ?></a>
						<?php } else { 
							echo(stripslashes($album['name'])); 
						} ?>
					</h2>
					<p class="wppa-box-text wppa-black description"><?php echo(wppa_html(wppa_get_the_album_desc())); ?></p>
					<div class="wppa-box-text info">
						<?php if ($photocount > $mincount && get_option('wppa_hide_slideshow', 'no') == 'no') { ?>
							<a href="<?php wppa_slideshow_url(); echo($occ); ?>" title="<?php _e('Slideshow', 'wppa'); ?>" ><?php _e('Slideshow', 'wppa'); ?></a>
						<?php } else echo('&nbsp;'); ?>
					</div>
					<div class="wppa-box-text wppa-black info">
						<?php if ($photocount > $mincount || $albumcount) { ?>
							<a href="<?php wppa_album_url(); echo($occ); ?>" title="<?php _e('View the album', 'wppa'); echo(' ' . $album['name']); ?>" ><?php _e('View', 'wppa'); ?>
								<?php if ($albumcount) { echo(' ' . $albumcount . ' '); _e('albums', 'wppa'); } ?>
								<?php if ($photocount > $mincount && $albumcount) _e('and', 'wppa'); ?>
								<?php if ($photocount > $mincount) { echo(' ' . $photocount . ' '); _e('photos', 'wppa'); } ?>
							</a>
						<?php } ?>
					</div>
					<div class="clear"></div>		
				</div>
				<?php if ($alt == 'even') $alt = 'alt'; else $alt = 'even'; ?>
			<?php } // End if on page
        endforeach; ?>
    </div><!-- #albumlist<?php echo($wppa_occur) ?>-->
<?php
    }	// If albums
 
	if ($is_cover == '0') $thumbs = wppa_get_thumbs();	
	else $thumbs = false;
	if ($thumbs) {
		if (count($thumbs) > $mincount) {
			if ($thumbpagesize != '0') {
				$nofthumbpages = ceil(count($thumbs) / $thumbpagesize);
			}
			else {
				$nofthumbpages = '1';
			}
		}
		if (($thumbpagesize == '0' && $albumpagesize == '0') || !$didsome) { // Either no pagination on thumbs OR thru with the albums
			if (count($thumbs) > $mincount) {
				$didsome = true;
				$counter = '0';
				if (get_option('wppa_thumbtype', 'default') == 'ascovers') {
	?>
					<div id="thumblist_<?php echo($wppa_occur) ?>" class="thumblist">
<?php 					foreach ($thumbs as $tt) :  global $thumb; $thumb = $tt; 
							$counter++;
							if (wppa_onpage($counter, $curpage - $nofalbumpages, $thumbpagesize)) {
								$src = wppa_get_thumb_path(); 
								$imgattr = wppa_get_imgstyle($src, $coversize, '', 'cover'); 
								$src = wppa_get_thumb_url(); 
								$events = wppa_get_imgevents('cover'); 
								$title = esc_js(wppa_get_photo_name($thumb['id'])); 
								$href = wppa_get_photo_page_url() . $occ; 
?>
								<div class="thumb wppa-box wppa-<?php echo($alt); ?>">
									<?php if ($src != '') { ?>
										<div id="thumbphoto_frame_<?php echo($thumb['id'].'_'.$wppa_occur) ?>" class="thumbphoto_frame">
											<a href="<?php echo($href); ?>" title="<?php echo($title); ?>">
												<img src="<?php echo($src); ?>" alt="<?php echo($title); ?>" class="image wppa-img" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/>
											</a>
										</div>
									<?php } ?>
										<h2 class="wppa-title name" style="clear:none;">
											<a href="<?php echo($href); ?>" title="<?php echo($title); ?>"><?php echo(stripslashes($thumb['name'])); ?></a>
										</h2>
										<p class="description"><?php echo(wppa_html(stripslashes($thumb['description']))); ?></p>
										<div class="clear"></div>		
								</div>
								<?php if ($alt == 'even') $alt = 'alt'; else $alt = 'even'; ?>
							<?php } // End if on page
						endforeach; ?>
					</div><!-- #thumblist_<?php echo($wppa_occur) ?>-->
<?php
				}
				else {
?>
					<div id="thumbnail_area_<?php echo($wppa_occur) ?>" class="thumbs thumbnail_area wppa-box wppa-<?php echo($alt); ?>" onclick="wppa_popdown(<?php echo($wppa_occur); ?>)" >
<?php 					foreach ($thumbs as $tt) :  global $thumb; $thumb = $tt; 
							$counter++;
							if (wppa_onpage($counter, $curpage - $nofalbumpages, $thumbpagesize)) {
								$src = wppa_get_thumb_path(); 
								$imgattr = wppa_get_imgstyle($src, $thumbsize, 'optional', 'thumb'); 
								$src = wppa_get_thumb_url(); 
								$events = wppa_get_imgevents('thumb', $thumb['id']); 
								if (get_option('wppa_use_thumb_popup') == 'yes') $title = esc_attr(stripslashes($thumb['description']));
								else $title = esc_js(wppa_get_photo_name($thumb['id'])); ?>
								<div id="thumbnail_frame_<?php echo($thumb['id'].'_'.$wppa_occur) ?>" class="thumbnail_frame" style="<?php echo(wppa_get_thumb_frame_style()); ?>" >
									<a href="<?php wppa_photo_page_url(); echo($occ); ?>" class="thumb-img" id="a-<?php echo($thumb['id'].'-'.$wppa_master_occur) ?>"><img src="<?php echo($src); ?>" alt="<?php echo(esc_attr($thumb['name'])); ?>" title="<?php echo(esc_attr($title)); ?>" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/></a>
									<?php if ($wppa_src) { ?>
										<div class="thumb-text" ><?php echo('(<a href="'.wppa_get_album_url($thumb['album']).$occ.'">'.stripslashes(wppa_get_album_name($thumb['album'])).'</a>)'); ?></div>
									<?php } ?>
									<?php if (get_option('wppa_thumb_text', 'no') == 'yes') { ?>
										<div class="thumb-text" ><?php echo(stripslashes($thumb['name'])); ?></div>
										<div class="thumb-text" ><?php echo(stripslashes($thumb['description'])); ?></div>
									<?php } ?>
								</div><!-- #thumbnail_frame_<?php echo($thumb['id'].'_'.$wppa_occur) ?> -->
							<?php }	// End if on page
						endforeach; ?>
						<div id="wppa-popup-<?php echo($wppa_master_occur) ?>" class="wppa-popup-frame" ></div>
						<div class="clear"></div>
					</div><!-- #thumbnail_area_<?php echo($wppa_occur) ?> -->
					<script type="text/javascript" >wppa_animation_speed = <?php echo get_option('wppa_animation_speed', 400) ?>;</script>
	<?php
				}
			}
		}	// No pag or thru with the albums
	}	// If thumbs
	
	if ($thumbpagesize == '0' && $albumpagesize == '0') $totpag = '0';
	else $totpag = $nofalbumpages + $nofthumbpages;

	wppa_page_links($totpag, $curpage);
	
	if (!$didsome && $wppa_src) {
?>
	<div class="center"><?php _e('No albums or photos found matching your search criteria.', 'wppa'); ?></div>
<?php
	}
}
else {	// Slideshow page or single
	if (!wppa_page('oneofone')) { ?>
    <div id="prevnext1-<?php echo($wppa_master_occur) ?>" class="wppa-box wppa-nav" <?php if (get_option('wppa_hide_slideshow', 'no') == 'yes') echo('style="display:none;"'); ?>>
		<p style="text-align: center; margin:0">
			<a id="speed0-<?php echo($wppa_master_occur) ?>" class="speed0" onclick="wppa_speed(<?php echo($wppa_master_occur); ?>, false)"><?php _e('Slower', 'wppa'); ?></a> |
			<a id="startstop-<?php echo($wppa_master_occur) ?>" class="startstop" onclick="wppa_startstop(<?php echo($wppa_master_occur) ?>, -1)"><?php _e('Start', 'wppa'); ?></a> |
			<a id="speed1-<?php echo($wppa_master_occur) ?>" class="speed1" onclick="wppa_speed(<?php echo($wppa_master_occur); ?>, true)"><?php _e('Faster', 'wppa'); ?></a>
		</p>
	</div><!-- #prevnext -->
<?php } ?>
	<div id="slide_frame-<?php echo($wppa_master_occur) ?>" class="slide_frame" style="<?php echo(wppa_get_slide_frame_style()); ?>">
		<div id="theslide0-<?php echo($wppa_master_occur) ?>" class="theslide"></div>
		<div id="theslide1-<?php echo($wppa_master_occur) ?>" class="theslide"></div>
		<div id="spinner-<?php echo($wppa_master_occur) ?>" class="spinner"><img id="spinnerimg-<?php echo($wppa_master_occur) ?>" src="" /></div>
	</div>
<?php if (!wppa_page('oneofone')) { ?>
	<p id="imagedesc-<?php echo($wppa_master_occur) ?>" class="wppa-fulldesc imagedesc"></p>
	<p id="imagetitle-<?php echo($wppa_master_occur) ?>" class="wppa-fulltitle imagetitle"></p>
<?php } ?>	

<?php if (!wppa_page('oneofone')) { ?>
	<div id="prevnext2-<?php echo($wppa_master_occur) ?>" class="wppa-box wppa-nav">
		<p style="text-align: center; margin:0;">
			<span id="p-a-<?php echo($wppa_master_occur) ?>" class="wppa-black prev">&laquo;&nbsp;</span><a id="prev-arrow-<?php echo($wppa_master_occur) ?>" class="prev arrow arrow-<?php echo($wppa_master_occur) ?>" onclick="wppa_prev(<?php echo($wppa_master_occur) ?>)"></a>
			<span id="counter-<?php echo($wppa_master_occur) ?>" class="wppa-black" style="text-align:center; "></span>
			<span id="n-a-<?php echo($wppa_master_occur) ?>" class="wppa-black next">&nbsp;&raquo;</span><a id="next-arrow-<?php echo($wppa_master_occur) ?>" class="next arrow arrow-<?php echo($wppa_master_occur) ?>" onclick="wppa_next(<?php echo($wppa_master_occur) ?>)"></a>
		</p>
	</div>
<?php } ?> 
	<script type="text/javascript" >
		/* <![CDATA[ */
		/* Transfer translated texts to slideshow js variables */
		wppa_slideshow = "<?php _e('Slideshow', 'wppa'); ?>";
		wppa_photo = "<?php _e('Photo', 'wppa'); ?>";
		wppa_of = "<?php _e('of', 'wppa'); ?>";
		wppa_prevphoto = "<?php _e('Previous photo', 'wppa'); ?>";
		wppa_nextphoto = "<?php _e('Next photo', 'wppa'); ?>";
		/* And some data */
		wppa_animation_speed = <?php echo get_option('wppa_animation_speed', 400) ?>;
		wppa_imgdir = "<?php echo wppa_get_imgdir() ?>";
		/* ]]> */
	</script>
<?php
    $index = 0;
	$startindex = -1;
	if (wppa_page('oneofone')) {	// Only one single photo
		$startindex = 0;
		echo '<script type="text/javascript">wppa_store_slideinfo(' . $wppa_master_occur . ',' . wppa_get_slide_info($index, $single_photo) . ');</script>';
	}
	else {		// Slideshow or single
		if (isset($_GET['photo'])) $startid = $_GET['photo'];
		else {
			if (get_option('wppa_start_slide', 'no') == 'yes' && get_option('wppa_hide_slideshow', 'no') == 'no') $startid = -1;
			else $startid = -2;
		}
		if (isset($_GET['album'])) $alb = $_GET['album'];
		else $alb = '';	// Album id is in $startalbum
		foreach (wppa_get_thumbs($alb) as $tt) : $id = $tt['id'];
			echo '<script type="text/javascript">wppa_store_slideinfo(' . $wppa_master_occur . ',' . wppa_get_slide_info($index, $id) . ');</script>';
			if ($startid == -2) $startid = $id;
			if ($startid == $id) $startindex = $index;
			$index++;
		endforeach;
	}
	if (get_option('wppa_fullvalign', 'default') == 'fit' || wppa_page('oneofone')) { ?>
		<script type="text/javascript" >wppa_fullvalign_fit[<?php echo($wppa_master_occur) ?>] = true;</script>
<?php } ?>
    <script type="text/javascript">
		/* <![CDATA[ */
		/* Start slideshow running (-1) or in browsemode at requested photo (>=0) */
		wppa_startstop(<?php echo($wppa_master_occur . ', '. $startindex) ?>);
		/* ]] */
	</script>
<?php    
}

echo('<p>');	// Re-open wpautop generated paragraph

?>