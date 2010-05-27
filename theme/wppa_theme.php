<?php 
global $allow_sidebars;
global $wppa_fullsize;
global $is_cover;
if ($allow_sidebars == '') $allow_sidebars = '1';

global $before_album;
echo ($before_album);

$mincount = get_option('wppa_min_thumbs', '1');

wppa_breadcrumb();
echo('<br />&nbsp;');
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
					$href = get_page_link($album['cover_linkpage']);
					$title = __('Link to', 'wppa');
					$title .= ' ' . $page_data->post_title;
				} else {
					$href = '#';
					$title = __('Page is not available.');
				}
			} else {
				if ($photocount != '0' && $photocount <= $mincount) {
					$href = wppa_get_image_page_url(); 
					$title = __('View the cover photo', 'wppa'); 
						if ($photocount > 1) $title .= __('s', 'wppa');
				} else {
					$href = wppa_get_album_url(); 
					$title = __('View the album', 'wppa') . ' ' . $album['name'];
				}
			}
			$src = wppa_get_image_url();	
?>
 			<div class="album <?php echo($alt); ?>">
				<?php if ($src != '') { ?>
					<a href="<?php echo($href); ?>" title="<?php echo($title); ?>">
						<img src="<?php echo($src); ?>" alt="<?php echo($title); ?>" class="image" />
					</a>
				<?php } ?>
				<h2 class="name">
					<a href="<?php echo($href); ?>" title="<?php echo($title); ?>"><?php echo($album['name']); ?></a>
				</h2>
				<p class="description"><?php wppa_the_album_desc(); ?></p>
				<?php if ($photocount > $mincount) { ?>
					<a href="<?php wppa_slideshow_url(); ?>" title="<?php _e('Slideshow', 'wppa'); ?>" ><?php _e('Slideshow', 'wppa'); ?></a>
				<?php } ?>
				<br/>
				<?php if ($photocount > $mincount || $albumcount) { ?>
					<a href="<?php wppa_album_url(); ?>" title="<?php _e('View the album', 'wppa'); echo(' ' . $album['name']); ?>" ><?php _e('View', 'wppa'); ?>
						<?php if ($albumcount) { echo(' ' . $albumcount . ' '); _e('albums', 'wppa'); } ?>
						<?php if ($photocount > $mincount && $albumcount) _e('and', 'wppa'); ?>
						<?php if ($photocount > $mincount) { echo(' ' . $photocount . ' '); _e('photos', 'wppa'); } ?>
					</a>
				<?php } ?>
                <div id="clear"></div>
            </div>
			<?php if ($alt == 'even') $alt = 'alt'; else $alt = 'even'; ?>
        <?php endforeach; ?>
    </div>
<?php
    }
 
	$thumbs = wppa_get_thumbs(); 
    if (count($thumbs) > $mincount && $is_cover == '0') { 
		if ($allow_sidebars) $w = 'narrow'; else $w = 'wide'; ?>
		<div class="thumbs thumbs<?php echo($w); ?>" id="thumbs<?php echo($w)?>">
		<?php foreach ($thumbs as $tt) :  global $thumb; $thumb = $tt; ?>
			<a href="<?php wppa_photo_page_url(); ?>" class="img">
				<img src="<?php wppa_thumb_url(); ?>" alt="<?php echo($thumb['name']); ?>" title="<?php echo($thumb['name']); ?>" />
			</a>
		<?php endforeach; ?>
	</div>
    <?php }
}
elseif (wppa_page('single')) { ?>
	<script type="text/javascript">
	/* <![CDATA[ */
		var prev_href = '';
		var next_href = '';
		
		function wppa_add_scroll(xid) {
			var the_href;
			var X = 0;
			var Y = 0;
			var id;
			
			if (xid == 0) id = 'p-a'; else id = 'n-a';
			
			if (id == 'p-a') {
				if (prev_href == '') prev_href = document.getElementById('p-a').href;
				the_href = prev_href;
			}
			else if (id == 'n-a') {
				if (next_href == '') next_href = document.getElementById('n-a').href;
				the_href = next_href;
			}
			else return; // Should never get here
				
			if (window.pageXOffset) if (window.pageXOffset != 0) X = window.pageXOffset;
			if (X == 0) if (document.body.scrollLeft != 0) X = document.body.scrollLeft; /* IE qrqs */
			if (X == 0) if (document.documentElement.scrollLeft != 0) X = document.documentElement.scrollLeft; /* IE std */
			
			if (window.pageYOffset) if (window.pageYOffset != 0) Y = window.pageYOffset;
			if (Y == 0) if (document.body.scrollTop != 0) Y = document.body.scrollTop; /* IE */
			if (Y == 0) if (document.documentElement.scrollTop != 0) Y = document.documentElement.scrollTop; 
												  
			document.getElementById(id).href = the_href + '&scrollx=' + X + '&scrolly=' + Y;
		}
	/* ]]> */
	</script>
<?php
	wppa_prev_next('<div id="prev-arrow" class="prev"><a id="p-a" href="%link%" onmouseover="wppa_add_scroll(0)">&laquo;</a></div>', '<div id="next-arrow" class="next"><a id="n-a" href="%link%" onmouseover="wppa_add_scroll(1)">&raquo;</a></div>'); 
?>
    <img src="<?php wppa_photo_url() ?>" alt="<?php wppa_photo_name() ?>" class="big" <?php wppa_fullsize(); ?> />
    <p id="imagedesc" class="imagedesc"><?php wppa_photo_desc(); ?></p>
	<p id="imagetitle" class="imagetitle"><?php wppa_photo_name(); ?></p>
<?php
}
elseif (wppa_page('slide')) {
?>
    <div id="prevnext">
		<p style="text-align: center">
			<a href="#" id="speed0" onclick="wppa_speed(false)"><?php _e('Slower', 'wppa'); ?></a> |
			<a href="#" id="startstop" onclick="wppa_startstop()"><?php _e('Start', 'wppa'); ?></a> |
			<a href="#" id="speed1" onclick="wppa_speed(true)"><?php _e('Faster', 'wppa'); ?></a>
		</p>
	</div>
<?php 
	if (is_numeric($wppa_fullsize)) $minheight = $wppa_fullsize * 3 / 4;
	else $minheight = get_option('wppa_fullsize') * 3 / 4;
	$minheight = ceil($minheight);
?>
    <p id="theslide" style="min-height: <?php echo($minheight); ?>px; text-align: center;">
	<p id="imagedesc" class="imagedesc"></p>
	<p id="imagetitle" class="imagetitle"></p>
	<script type="text/javascript" src="<?php echo(get_bloginfo('wpurl')); ?>/wp-content/plugins/<?php echo(PLUGIN_PATH); ?>/theme/wppa_slideshow.js"></script>
<?php
    $index = 0;
    $alb = $_GET['album'];
    foreach (wppa_get_thumbs($alb) as $tt) : $id = $tt['id'];
        echo '<script type="text/javascript">wppa_store_slideinfo(' . wppa_get_slide_info($index, $id) . ');</script>';
        $index++;
    endforeach;
?>
    <script type="text/javascript"><?php echo('wppa_startstop()'); ?></script>
<?php    
} ?>