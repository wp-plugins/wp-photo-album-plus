<?php 
global $allow_sidebars;
global $wppa_fullsize;
if ($allow_sidebars == '') $allow_sidebars = '1';
/* Dutch  
$faster = 'Sneller';
$slower = 'Trager';
$view = 'Bekijk het album';
$vw = 'Bekijk ';
$ph = ' foto&#039;s';
$and = ' en ';
$slideshow = 'Diavoorstelling';
/* English */
$faster = 'Faster';
$slower = 'Slower';
$view = 'View the album';
$vw = 'View ';
$ph = ' photos';
$and = ' and ';
$slideshow = 'Slideshow';
/**/

global $before_album;
echo ($before_album);

if (wppa_page('albums')) :
    echo '<div id="prevnext">'; wppa_breadcrumb(); echo '</div>';
    $albums = wppa_get_albums(); 
    if ($albums) : 
    echo '<div id="albumlist">';
        $alt = 'even';
        foreach ($albums as $ta) :  global $album; $album = $ta;
            $albumname = $album['name'];
            $albumdesc = $album['description'];
            $albumurl = wppa_get_album_url();
            $photocount = wppa_get_photo_count();
            $albumcount = wppa_get_album_count();
 			echo '<div class="album ' . $alt . '">';
            echo '<a href="' . $albumurl . '" title="' . $view . ' ' . $albumname . '">
                    <img src="' . wppa_get_image_url() . '" alt="' . $view . ' ' . $albumname . '" class="image" /></a>';
            echo '<h2 class="name"><a href="' . $albumurl . '" title="' . $view . ' ' . $albumname . '">' . $albumname . '</a></h2>';
            echo '<p class="description">' . $albumdesc . '</p>';
                if ($photocount > 1) echo '<a href="' . wppa_get_slideshow_url() . '" title="' . $slideshow . '" >' . $slideshow . '</a>';
                echo '<br/>';
                if ($photocount > 1 || $albumcount) echo '<a href="' . $albumurl . '" title="' . $view . '" >' . $vw; 
                if ($albumcount) echo $albumcount . ' albums';
                if ($photocount > 1 && $albumcount) echo $and;
                if ($photocount > 1) echo $photocount . $ph;
                echo '</a>';
                echo '<div id="clear"></div>'; 
            echo '</div>';
            if ($alt == 'even') $alt = 'alt'; else $alt = 'even';
        endforeach; 
    echo '</div>';
    endif;
 
    $thumbs = wppa_get_thumbs();
    if (count($thumbs) > 1) :
    if ($allow_sidebars) echo '<div class="thumbs thumbsnarrow" id="thumbsnarrow">';
    else echo '<div class="thumbs thumbswide" id="thumbswide">';
        foreach ($thumbs as $tt) :  global $thumb; $thumb = $tt; 
			echo '<a href="' . wppa_get_photo_page_url() . '" class="img"><img src="' . wppa_get_thumb_url() . '" alt="*" title="" /></a>';
		endforeach;
	echo '</div>';
    endif;

elseif (wppa_page('single')) :
    echo '<div id="prevnext">'; wppa_breadcrumb(); echo '</div>';  
    wppa_prev_next('<div id="prev-arrow" class="prev"><a href="%link%">&laquo;</a></div>', '<div id="next-arrow" class="next"><a href="%link%">&raquo;</a></div>');
    echo '<img src="' . wppa_photo_url('', TRUE) . '" alt="' . wppa_photo_name('', TRUE) . '" class="big" ' . wppa_get_fullsize() . ' />';
    echo '<p id="imagedesc" class="imagedesc">' . wppa_photo_desc('', TRUE) . '</p>';
	echo '<p id="imagetitle" class="imagetitle">' . wppa_photo_name('', TRUE) . '</p>';

elseif (wppa_page('slide')) :
    echo '<div id="prevnext">'; wppa_breadcrumb();
    echo '<p style="text-align: center">
    <a href="#" id="speed0" onclick="wppa_speed(false)">' . $slower . '</a> |
    <a href="#" id="startstop" onclick="wppa_startstop()">Start</a> |
    <a href="#" id="speed1" onclick="wppa_speed(true)">' . $faster . '</a></p></div>'; 
	if (is_numeric($wppa_fullsize)) $minheight = $wppa_fullsize * 3 / 4;
	else $minheight = get_option('wppa_fullsize') * 3 / 4;
    echo '<p id="theslide" style="min-height: ' . $minheight . 'px; text-align: center;">';
    echo '<p id="imagedesc" class="imagedesc"></p><p id="imagetitle" class="imagetitle"></p>';
    echo '<script type="text/javascript" src="' . get_bloginfo('wpurl') .'/wp-content/plugins/' . PLUGIN_PATH . '/theme/wppa_slideshow.js"></script>'; 
    $index = 0;
    $alb = $_GET['album'];
    foreach (wppa_get_thumbs($alb) as $tt) : $id = $tt['id'];
        echo '<script type="text/javascript">wppa_store_slideinfo(' . wppa_get_slide_info($index, $id) . ');</script>';
        $index++;
    endforeach;
    echo '<script type="text/javascript">wppa_startstop();</script>';
    
endif; ?>