=== WP Photo Album Plus ===
Contributors: opajaap
Tags: photo, album, gallery, slideshow
Version: 1.7
Stable tag: trunk
Author: J.N. Breetvelt
Author URI: http://www.opajaap.nl/
Requires at least: 2.1
Tested up to: 2.9.1

This plugin is designed to easily manage and display your photo albums and slideshows within your WordPress site. 


== Description ==

This plugin is designed to easily manage and display your photo albums and slideshows within your WordPress site. 
This plugin is compatible with WP Photo ALbum version 1.5.1 so you can upgrade your existing 
photo album without re-creation of albums and without re-uploading your photos.
New features of WP Photo Album Plus with respect to WP Photo Album include: 
Subalbums, various ways to sort albums and photos, slideshow.
Albums may contain photos and albums at the same time. Albums may be nested to any depth.
The only restriction is that the top-level album can only contain albums.
 
Plugin Admin Features:
You can find the plugin admin section under Manage then submenu Photos.

* Manage and create albums
* Move photos to and from albums
* Upload and delete photos
* Adjust thumbnail and full view picture sizes (set default max sizes for each).
* Specify sort order for albums and photos
* Move albums into albums, creating sub-albums


== Installation ==

1. Unzip and upload the wppa plugin folder to wp-content/plugins/
2. Make sure that the folder wp-content/uploads/ exists and is writable by the server (CHMOD 755)
3. Activate the plugin in WP Admin -> Plugins.
4. Create at least one album in the albums tab
5. In the uploads tab, you can now upload you photots

== Frequently Asked Questions ==

= Do i have to upload my photos again? =

No, if you had WP Photo Album before, you can simply take advantage of the new features 
that WP Photo Album Plus provides to you.

= Do i have to remake the albums? =

No, you will still have the albums you had. Now you can place them inside an other existing album,
that even may contain photos itself.

= What if i regret the upgrade, can i downgrade back to WP Photo Album version 1.5.1? =

Yes, simply de-activate WP Photo Album Plus and re-activate WP Photo ALbum. 
but remember: **You can not run both versions at the same time.**
You will see the sub albums you created appear as normal albums.
The sort order information will have no longer effect.

== Changelog ==

= 1.7 =
* Slideshow did not work when having non-standard permalink structure.
* Fixed a layout issue around page header and breadcrumb.
* Added functionality: You can have a single album in a post or page.

= 1.6.1 =
* Minor cosmetic corrections.
* Updated tags.txt to reflect new and changed tags as per version 1.6.
* Updated readme.txt
* Included sample file: page-photo-album.php.

= 1.6 =
* Converted WP Photo Album 1.5.1 to WP Photo Album Plus. Extended database.
* Added various sort order options for albums and photos.
* Added slideshow.

== Upgrade Notice ==

= 1.6.1 =
* This version is better documented.
* This version includes an example of a photo album page template file.

= 1.6 =
If you want to create **sub-albums**, if you want to control the **order photos appear** and/or if you want to have a **slideshow**
you should upgrade your WP Photo Album to **WP Photo Album Plus**, or if you are new to photo albums, simply install **WP Photo Album Plus**


== Upgrade instructions ==

= When upgrading from WP Photo Album to WP Photo Album Plus be aware of: =
* First de-activate WP Photo Album before activating WP Photo Album Plus!!
* The existing database and albums and photos will be preserved.
* **YOU DO NOT NEED TO RE-UPLOAD YOUR PHOTOS**
* The database tables will be upgraded automatically to hold the extra data for the new features.
* You will need to use the newly supplied default theme file 'wppa_theme.php' and/or modify
  your current theme file as the callable functions (tags) are changed with respect to WP Photo Album.
                                                                                

== Creating Photo Album Page ==
Create a page like you normally would in WordPress. In my example, we'll give it the page title of "Photo Gallery". In the Page Content 
section add the following code:

%%wppa%%

If you want to display the **contents** of a specific album add the following line (e.g. for album number 19, replace 19 by the album number you wish):

%%album=19%%
	
If you want to display the **cover** of album #19, create a new album, use his number and make album 19's parent that album number.
Upload **one** photo to the parent album. This photo will be used as the cover photo, and not displayed inside the album.

Also, make sure under 'Page Template' you are using 'Default Template' as some WordPress themes have an archives template.  
Press the publish button and you're done. You'll now have a photo gallery page. 

You can also create a custom page template by dropping the following code into a page:

`<?php wppa_albums(); ?>`

In order to work properly, this tag needs to be within the WordPress loop ( http://codex.wordpress.org/The_Loop ) 
For more information on creating custom page templates, visit http://codex.wordpress.org/Pages#Creating_your_own_Page_Templates


== Adjusting CSS and Template Styling ==

WP Photo Album comes with a default layout and theme. To change the style and layout of the photo album, copy 
them/edit wppa_theme.php and theme/wppa_style.css to your active theme's folder, and edit them. 
WPPA uses a system of tags similar to the WordPress theme system. To view a list of available tags, please read tags.txt


== Plugin Support And Feature Request ==
If you've read over this readme carefully and are still having issues, if you've discovered a bug, or have a feature request, please 
contact me via my contact page ('Contact formulier' on the left sidebar of my homepage) on http://www.opajaap.nl/


== About and Credits ==
WP Photo Album was originally created by R.J. Kaplan.
WP Photo Album Plus is further developed by J.N. Breetvelt ( http://www.opajaap.nl/ )

== Licence ==
WP Photo Album is released under the GNU GPL licence. ( http://www.gnu.org/copyleft/gpl.html )