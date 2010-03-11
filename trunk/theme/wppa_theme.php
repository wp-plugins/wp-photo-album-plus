<?php 
global $allow_sidebars;
global $wppa_fullsize;
global $is_cover;
if ($allow_sidebars == '') $allow_sidebars = '1';

global $before_album;
echo ($before_album);

$mincount = get_option('wppa_min_thumbs', '1');

if (wppa_page('albums')) { ?>

    <div id="prevnext"><?php wppa_breadcrumb(); ?></div>
<?php
    $albums = wppa_get_albums(); 
    if ($albums) { ?>
    <div id="albumlist">
<?php
        $alt = 'even';
        foreach ($albums as $ta) :  global $album; $album = $ta;
            $photocount = wppa_get_photo_count();
            $albumcount = wppa_get_album_count();
?>
 			<div class="album <?php echo($alt); ?>">
				<a href="<?php if ($photocount <= $mincount) wppa_image_page_url(); else wppa_album_url(); ?>" title="<?php if ($photocount <= $mincount) { _e('View the cover photo', 'wppa'); if ($photocount > 1) _e('s', 'wppa'); } else { _e('View the album', 'wppa'); echo(' ' . $album['name']); } ?>">
                    <img src="<?php wppa_image_url(); ?>" alt="<?php _e('View the album', 'wppa'); echo(' ' . $album['name']); ?>" class="image" />
				</a>
				<h2 class="name">
					<a href="<?php wppa_album_url(); ?>" title="<?php _e('View the album', 'wppa'); echo(' ' . $album['name']); ?>"><?php echo($album['name']); ?></a>
				</h2>
				<p class="description"><?php wppa_the_album_desc(); ?></p>
				<?php if ($photocount > $mincount) { ?>
					<a href="<?php wppa_slideshow_url(); ?>" title="<?php _e('Slideshow', 'wppa'); ?>" ><?php _e('Slideshow', 'wppa'); ?></a>
				<?php } ?>
				<br/>
				<?php if ($photocount > 1 || $albumcount) { ?>
					<a href="<?php wppa_album_url(); ?>" title="<?php _e('View the album', 'wppa'); echo(' ' . $album['name']); ?>" ><?php _e('View', 'wppa'); ?>&nbsp;
						<?php if ($albumcount) { echo($albumcount . ' '); _e('albums', 'wppa'); } ?>
						<?php if ($photocount > $mincount && $albumcount) _e('and', 'wppa'); ?>
						<?php if ($photocount > $mincount) { echo($photocount . ' '); _e('photos', 'wppa'); } ?>
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
    <div id="prevnext"><?php wppa_breadcrumb(); ?></div>
	<?php wppa_prev_next('<div id="prev-arrow" class="prev"><a href="%link%">&laquo;</a></div>', '<div id="next-arrow" class="next"><a href="%link%">&raquo;</a></div>'); ?>
    <img src="<?php wppa_photo_url() ?>" alt="<?php wppa_photo_name() ?>" class="big" <?php wppa_fullsize(); ?> />
    <p id="imagedesc" class="imagedesc"><?php wppa_photo_desc(); ?></p>
	<p id="imagetitle" class="imagetitle"><?php wppa_photo_name(); ?></p>
<?php
}
elseif (wppa_page('slide')) {
?>
    <div id="prevnext">
		<?php wppa_breadcrumb(); ?>
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