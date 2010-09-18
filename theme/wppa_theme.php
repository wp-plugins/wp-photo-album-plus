<?php 
/* wppa_theme.php
* Package: wp-photo-album-plus
*
* display the albums/photos/slideshow in a page or post
* Version 2.0.0
*/
global $wppa_fullsize;
global $is_cover;
global $wppa_occur;

$alt = 'even';
$mincount = get_option('wppa_min_thumbs', '1');
$coversize = get_option('wppa_smallsize');
$thumbsize = get_option('wppa_thumbsize');
$occ = '&occur=' . $wppa_occur;



wppa_breadcrumb('&raquo;', 'optional');		// Display breadcrumb navigation only if it is set in the settings page

echo('</p>');	// Close wpautop generated paragraph

if (wppa_page('albums')) { 
    $albums = wppa_get_albums(); 
	
    if ($albums) { 
?>
    <div id="albumlist_<?php echo($wppa_occur) ?>" class="albumlist">
<?php
        foreach ($albums as $ta) :  global $album; $album = $ta;
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
			} else {
				if ($photocount != '0' && $photocount <= $mincount) {
					$href = wppa_get_image_page_url() . $occ; 
					$title = __('View the cover photo', 'wppa'); 
						if ($photocount > 1) $title .= __('s', 'wppa');
				} else {
					$href = wppa_get_album_url() . $occ; 
					$title = __('View the album', 'wppa') . ' ' . $album['name'];
				}
			}
			$src = wppa_get_image_url('save_id');	
			$path = wppa_get_image_path('use_id');
			$imgattr = wppa_get_imgstyle($path, $coversize, '', 'cover');
			$events = wppa_get_imgevents('cover');
?>
 			<div class="album <?php echo($alt); ?>">
				<?php if ($src != '') { ?>
					<div id="coverphoto_frame_<?php echo($album['id'].'_'.$wppa_occur) ?>" class="coverphoto_frame">
						<a href="<?php echo($href); ?>" title="<?php echo($title); ?>">
							<img src="<?php echo($src); ?>" alt="<?php echo($title); ?>" class="image" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/>
						</a>
					</div>
				<?php } ?>
				<h2 class="name">
					<a href="<?php echo($href); ?>" title="<?php echo($title); ?>"><?php echo(stripslashes($album['name'])); ?></a>
				</h2>
				<p class="description"><?php echo(wppa_html(wppa_get_the_album_desc())); ?></p>
				<div class="info">
					<?php if ($photocount > $mincount) { ?>
						<a href="<?php wppa_slideshow_url(); echo($occ); ?>" title="<?php _e('Slideshow', 'wppa'); ?>" ><?php _e('Slideshow', 'wppa'); ?></a>
					<?php } else echo('&nbsp;'); ?>
				</div>
				<div class="info">
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
        <?php endforeach; ?>
    </div><!-- #albumlist<?php echo($wppa_occur) ?>-->
<?php
    }
 
	if ($is_cover == '0') $thumbs = wppa_get_thumbs(); 
	else $thumbs = false;
	if ($thumbs) {
		if (count($thumbs) > $mincount) { 
			if (get_option('wppa_thumbtype', 'default') == 'ascovers') {
?>
				<div id="thumblist_<?php echo($wppa_occur) ?>" class="thumblist">
					<?php foreach ($thumbs as $tt) :  global $thumb; $thumb = $tt; ?>
						<?php $src = wppa_get_thumb_path(); ?>
						<?php $imgattr = wppa_get_imgstyle($src, $coversize, '', 'cover'); ?>
						<?php $src = wppa_get_thumb_url(); ?>
						<?php $events = wppa_get_imgevents('cover'); ?>
						<?php $title = $thumb['name']; ?>
						<?php $href = wppa_get_photo_page_url() . $occ; ?>
						<div class="thumb <?php echo($alt); ?>">
							<?php if ($src != '') { ?>
								<div id="thumbphoto_frame_<?php echo($thumb['id'].'_'.$wppa_occur) ?>" class="thumbphoto_frame">
									<a href="<?php echo($href); ?>" title="<?php echo($title); ?>">
										<img src="<?php echo($src); ?>" alt="<?php echo($title); ?>" class="image" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/>
									</a>
								</div>
							<?php } ?>
								<h2 class="name">
									<a href="<?php echo($href); ?>" title="<?php echo($title); ?>"><?php echo(stripslashes($thumb['name'])); ?></a>
								</h2>
								<p class="description"><?php echo(wppa_html(stripslashes($thumb['description']))); ?></p>
								<div class="clear"></div>		
						</div>
						<?php if ($alt == 'even') $alt = 'alt'; else $alt = 'even'; ?>
					<?php endforeach; ?>
				</div><!-- #thumblist_<?php echo($wppa_occur) ?>-->
<?php
			}
			else {
?>
				<div id="thumbnail_area_<?php echo($wppa_occur) ?>" class="thumbs thumbnail_area thumbnail_area_<?php echo($alt); ?>">
					<?php foreach ($thumbs as $tt) :  global $thumb; $thumb = $tt; ?>
						<?php $src = wppa_get_thumb_path(); ?>
						<?php $imgattr = wppa_get_imgstyle($src, $thumbsize, 'optional', 'thumb'); ?>
						<?php $src = wppa_get_thumb_url(); ?>
						<?php $events = wppa_get_imgevents('thumb', $thumb['id']); ?>
						<div id="thumbnail_frame_<?php echo($thumb['id'].'_'.$wppa_occur) ?>" class="thumbnail_frame" >
							<a href="<?php wppa_photo_page_url(); echo($occ); ?>" class="img" id="a-<?php echo($thumb['id'].'-'.$wppa_occur) ?>"><img src="<?php echo($src); ?>" alt="<?php echo($thumb['name']); ?>" title="<?php echo($thumb['name']); ?>" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/></a>
						</div><!-- #thumbnail_frame_<?php echo($thumb['id'].'_'.$wppa_occur) ?> -->
					<?php endforeach; ?>
					<div id="wppa-popup-<?php echo($wppa_occur) ?>" onmouseout="wppa_popdown(this, <?php echo($wppa_occur) ?>);" class="wppa-popup" ></div>
					<div id="wppa-debug-<?php echo($wppa_occur) ?>" class="clear"></div>
				</div><!-- #thumbnail_area_<?php echo($wppa_occur) ?> -->
				<script type="text/javascript" >wppa_animation_speed = <?php echo get_option('wppa_animation_speed', 400) ?>;</script>
<?php
			}
		}
	}
}
else {
?>
    <div id="prevnext" >
		<p style="text-align: center">
			<a id="speed0" onclick="wppa_speed(false)"><?php _e('Slower', 'wppa'); ?></a> |
			<a id="startstop" onclick="wppa_startstop(-1)"><?php _e('Start', 'wppa'); ?></a> |
			<a id="speed1" onclick="wppa_speed(true)"><?php _e('Faster', 'wppa'); ?></a>
		</p>
	</div><!-- #prevnext -->
<?php 
	if (is_numeric($wppa_fullsize)) $fullsize = $wppa_fullsize;
	else $fullsize = get_option('wppa_fullsize');
?>
	<div id="prev-arrow" class="prev arrow"><a id="p-a" onclick="wppa_prev()">&laquo;</a></div>
	<div id="next-arrow" class="next arrow"><a id="n-a" onclick="wppa_next()">&raquo;</a></div>
	<div id="slide_frame" style="<?php if (get_option('wppa_fullvalign', 'default') == 'default') echo('min-height: ' . $fullsize * 3/4 . 'px;'); else echo('height: ' . $fullsize .'px;') ?> width: <?php echo($fullsize) ?>px;">
		<div id="theslide0" class="theslide"></div>
		<div id="theslide1" class="theslide"></div>
		<div id="spinner" ><img id="spinnerimg" src="" /></div>
	</div>
	
	<p id="imagedesc" class="imagedesc"></p>
	<p id="imagetitle" class="imagetitle"></p>
	<script type="text/javascript" >wppa_slideshow = "<?php _e('Slideshow', 'wppa'); ?>";</script>
	<script type="text/javascript" >wppa_animation_speed = <?php echo get_option('wppa_animation_speed', 400) ?>;</script>
	<script type="text/javascript" >wppa_imgdir = "<?php echo wppa_get_imgdir() ?>";</script>
<?php
    $index = 0;
	$startindex = -1;
	if (isset($_GET['photo'])) $startid = $_GET['photo'];
	else $startid = -1;
    if (isset($_GET['album'])) $alb = $_GET['album'];
	else $alb = '';	// Album id is in $startalbum
    foreach (wppa_get_thumbs($alb) as $tt) : $id = $tt['id'];
        echo '<script type="text/javascript">wppa_store_slideinfo(' . wppa_get_slide_info($index, $id) . ');</script>';
		if ($startid == $id) $startindex = $index;
        $index++;
    endforeach;
?>
    <script type="text/javascript">wppa_startstop(<?php echo($startindex) ?>);</script>
<?php    
}

echo('<p>');	// Re-open wpautop generated paragraph

?>