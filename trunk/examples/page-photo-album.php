<?php
/*
 *Template name: Page Photo Album
 */
/* This file is to be placed in your wordpress/wp-content/themes/<your theme name>/ directory
*/
/* The next line is only required if you have the plugin MyCaptcha installed */
if (class_exists('MyCaptcha')) { $MyCaptcha->initialize(); }

/* The next three lines make it possible to show the sidebar(s) if you don't need the space for the phots and you like them to come up */
global $allow_sidebars;

if (isset($_GET['album'])) $id = $_GET['album'];
if (!is_numeric($id)) $id = '0';
$count = wppa_get_photo_count($id);
// if (function_exists('wppa_get_photo_count')) echo('+');else echo('-');
if (isset($_GET['slide']) || isset($_GET['photo']) || $count > 30) $allow_sidebars = '0';
else $allow_sidebars = '1';

/* Here starts the 'normal' page.php code */
get_header(); 

    /* We use the $allow_sidebars var to define the width of the space we need (class widecolumn or narrowcolumn */
	if ($allow_sidebars) echo '<div id="content" class="narrowcolumn" role="main">';
    else echo '<div id="content" class="widecolumn" role="main">'; ?>
	
	<p style="text-align: center;">
		<?php
			$count = wppa_get_total_album_count();
			$y_id = wppa_get_youngest_album_id();
			$y_name = wppa_get_album_name($y_id);
			$p_id = wppa_get_parentalbumid($y_id);
			$p_name = wppa_get_album_name($p_id);
			echo('Er zijn ' . $count . ' fotoalbums, het laatst toegevoegde album is ');
			?><a href="<?php echo(get_permalink() . wppa_sep() . 'album=' . $y_id . '&cover=0&occur=1'); ?>"><?php echo($y_name); ?></a><?php if ($p_id == '0') echo('.');
			else { echo(', een subalbum van '); ?><a href="<?php echo(get_permalink() . wppa_sep() . 'album=' . $p_id . '&cover=0&occur=1'); ?>"><?php echo($p_name); ?></a>.<?php } 
		?>
	</p>	
	
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div class="post" id="post-<?php the_ID(); ?>">
    <?php /*        <h2></h2><?php /* Note: we need no title, we have breadcrumbs, but we leave the space */ ?>
            <?php if ($allow_sidebars) echo '<div class="entry">'; /* same as avove */
            else echo '<div class="entry wideentry">'; ?>
	<?php the_breadcrumb('no_p'); ?>
            <?php wppa_albums();  /* Here comes wppa_theme.php in, don't bother, all by itsself */	?>
			</div>
		</div>
        <?php if (!isset($_GET['album'])) comments_template(); /* Only comments on top-level photo album please */ ?>
		<?php endwhile; endif; ?>
  
        <div class="navigation">  
            <a href="<?php bloginfo('url') ?>">Beginpagina</a><!-- I.E. Home -->
        </div>
            
	</div>

    <?php if ($allow_sidebars == '1') {
        /* Adjust for your own sidebar(s) */
        get_sidebar('left');
        get_sidebar('right'); 
        } ?>

<?php get_footer(); ?>
<small class="debug">template = page-photo-album.php</small>
