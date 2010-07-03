<?php 
global $allow_sidebars;
global $wppa_fullsize;
global $is_cover;
if ($allow_sidebars == '') $allow_sidebars = '1';

global $wppa_occur;

$mincount = get_option('wppa_min_thumbs', '1');
$coversize = get_option('wppa_smallsize');
$thumbsize = get_option('wppa_thumbsize');
$occ = '&occur=' . $wppa_occur;

wppa_breadcrumb('&raquo;', 'optional');		// Display breadcrumb navigation only if it is set in the settings page

if (wppa_page('albums')) { 
    $albums = wppa_get_albums(); 
	
    if ($albums) { 
?>
    <div id="albumlist">
<?php
        $alt = 'even';
        foreach ($albums as $ta) :  global $album; $album = $ta;
            $photocount = wppa_get_photo_count();
            $albumcount = wppa_get_album_count();
			if (is_numeric($album['cover_linkpage']) && $album['cover_linkpage'] > 0) {
				$page_data = get_page($album['cover_linkpage']);
				if (!empty($page_data) && $page_data->post_status == 'publish') {
					$href = get_page_link($album['cover_linkpage']) . $occ;
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
			$src = wppa_get_image_url();	
			$path = wppa_get_image_path();
			$imgattr = wppa_get_imgstyle($path, $coversize, '', 'cover');
			$events = wppa_get_imgevents('cover');
?>
 			<div class="album <?php echo($alt); ?>">
				<?php if ($src != '') { ?>
					<div id="coverphoto_frame">
						<a href="<?php echo($href); ?>" title="<?php echo($title); ?>">
							<img src="<?php echo($src); ?>" alt="<?php echo($title); ?>" class="image" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/>
						</a>
					</div>
				<?php } ?>
				<h2 class="name">
					<a href="<?php echo($href); ?>" title="<?php echo($title); ?>"><?php echo($album['name']); ?></a>
				</h2>
				<p class="description"><?php wppa_the_album_desc(); ?></p>
				<div class="info">
					<?php if ($photocount > $mincount) { ?>
						<a href="<?php wppa_slideshow_url(); echo($occ); ?>" title="<?php _e('Slideshow', 'wppa'); ?>" ><?php _e('Slideshow', 'wppa'); ?></a>
					<?php } ?>
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
    </div>
<?php
    }
 
	if ($is_cover == '0') $thumbs = wppa_get_thumbs(); 
	else $thumbs = false;
	if ($thumbs) {
		if (count($thumbs) > $mincount) { 
			if ($allow_sidebars) $w = 'narrow'; else $w = 'wide'; ?>
			<div class="thumbs thumbs<?php echo($w); ?>" id="thumbs<?php echo($w)?>">
			<?php foreach ($thumbs as $tt) :  global $thumb; $thumb = $tt; ?>
				<?php $src = wppa_get_thumb_path(); ?>
				<?php $imgattr = wppa_get_imgstyle($src, $thumbsize, 'optional', 'thumb'); ?>
				<?php $src = wppa_get_thumb_url(); ?>
				<?php $events = wppa_get_imgevents('thumb'); ?>
				<div id="thumbnail_frame">
					<a href="<?php wppa_photo_page_url(); echo($occ); ?>" class="img">
						<img src="<?php echo($src); ?>" alt="<?php echo($thumb['name']); ?>" title="<?php echo($thumb['name']); ?>" style="<?php echo($imgattr); ?>" <?php echo($events) ?>/>
					</a>
				</div>
			<?php endforeach; ?>
			<div class="clear"></div>
			</div>
<?php 	}
	}
}
else {
?>
    <div id="prevnext">
		<p style="text-align: center">
			<a id="speed0" onclick="wppa_speed(false)"><?php _e('Slower', 'wppa'); ?></a> |
			<a id="startstop" onclick="wppa_startstop(-1)"><?php _e('Start', 'wppa'); ?></a> |
			<a id="speed1" onclick="wppa_speed(true)"><?php _e('Faster', 'wppa'); ?></a>
		</p>
	</div>
<?php 
	if (is_numeric($wppa_fullsize)) $minheight = $wppa_fullsize * 3 / 4;
	else $minheight = get_option('wppa_fullsize') * 3 / 4;
	$minheight = ceil($minheight);
?>
	<div id="prev-arrow" class="prev"><a id="p-a" onclick="wppa_prev()">&laquo;</a></div><div id="next-arrow" class="next"><a id="n-a" onclick="wppa_next()">&raquo;</a></div>
    <p id="theslide" style="min-height: <?php echo($minheight) ?>px; text-align: center;"></p>
	<p id="imagedesc" class="imagedesc"></p>
	<p id="imagetitle" class="imagetitle"></p>
	<script type="text/javascript" src="<?php echo(get_bloginfo('wpurl')); ?>/wp-content/plugins/<?php echo(PLUGIN_PATH); ?>/theme/wppa_slideshow.js"></script>
	<script type="text/javascript" >wppa_slideshow = "<?php _e('Slideshow', 'wppa'); ?>";</script>
<?php
    $index = 0;
	$startindex = -1;
	if (isset($_GET['photo'])) $startid = $_GET['photo'];
	else $startid = -1;
    $alb = $_GET['album'];
    foreach (wppa_get_thumbs($alb) as $tt) : $id = $tt['id'];
        echo '<script type="text/javascript">wppa_store_slideinfo(' . wppa_get_slide_info($index, $id) . ');</script>';
		if ($startid == $id) $startindex = $index;
        $index++;
    endforeach;
?>
    <script type="text/javascript">wppa_startstop(<?php echo($startindex) ?>);</script>
<?php    
} ?>