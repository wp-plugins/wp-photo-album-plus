extra.txt

wp-photo-album-plus version 2.0.0

Theme modification


In order to synchronize the occurrence counter when multiple posts are displayed at the same time 
with more than one having calls to wppa (i.e. %%wppa%% in the text),
a small modification can be done to 'the loop'.

Two things are required:

1. the declaration somewhere at the top of the php file that contains 'the loop': 

<?php global $wppa_occur; ?>

2. The counter reset, just after the line <?php while ( have_posts() ) : the_post(); ?>:

<?php $wppa_occur = 0; ?>

This makes sure that, when you click on a link in a photo album cover like 'slideshow' in a non-first post,
the requested action is performed directly. If you ommit this modification, you will get the requested post
with closed albums at first and you will have to click the same link again.

If you keep in mind what the modifications are, you can implement it in any theme.

The modified file 'index.php' for the default theme (prior to wp 3.0) is supplied in the 'default' sub-directory.

The modified file 'loop.php' for the theme 'twentyten' is summlied in the 'twentyten' sub-directory.

Just replace the existing file in your theme directory if you are using one of these themes.
