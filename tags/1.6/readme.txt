=== WP Photo Album Plus ===
Version: 1.6
Stable tag: trunk
Author: J.N. Breetvelt
Author URI: http://www.opajaap.nl/
Contributors: Rubin J. Kaplan 
Tags: photo, album, gallery, slideshow
Requires at least: 2.1
Tested up to: 2.9.1

This plugin is designed to easily manage and display your photo albums within your WordPress site. 

== Description ==
This plugin is designed to easily manage and display your photo albums within your WordPress site. 
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

== Upgrade Notice ==
When upgrading from WP Photo Album to WP Photo Album Plus be aware of:
1. First de-activate WP Photo Album before activating WP Photo Album Plus!!
2. The existing database and albums and photos will be preserved.
   YOU DO NOT NEED TO RE-UPLOAD YOUR PHOTOS
3. The database tables will be upgraded automatically to hold the extra data for the new features.
4. You will need to use the newly supplied default theme file 'wppa_theme_php' and/or modify
   your current theme file as the callable functions (tags) are changed with respect to WP Photo Album.

== Installation ==
1. Unzip and upload the wppa plugin folder to wp-content/plugins/
2. Make sure that the folder wp-content/uploads/ exists and is writable by the server (CHMOD 755)
3. Activate the plugin in WP Admin -> Plugins.
4. Create at least one album in the albums tab
5. In the uploads tab, you can now upload you photots


== Creating Photo Album Page ==
Create a page like you normally would in WordPress. In my example, we'll give it the page title of "Photo Gallery". In the Page Content 
section add the following code:

%%wppa%%

Also, make sure under 'Page Template' you are using 'Default Template' as some WordPress themes have an archives template.  
Press the publish button and you're done. You'll now have a photo gallery page. 

You can also create a custom page template by dropping the following code into a page:

<?php wppa_albums(); ?>

In order to work properly, this tag needs to be within the WordPress loop ( http://codex.wordpress.org/The_Loop ) 
For more information on creating custom page templates, visit http://codex.wordpress.org/Pages#Creating_your_own_Page_Templates



== Adjusting CSS and Template Styling ==

WP Photo Album comes with a default layout and theme. To change the style and layout of the photo album, copy 
them/edit wppa_theme.php and theme/wppa_style.css to your active theme's folder, and edit them. 
WPPA uses a system of tags similar to the WordPress theme system. To view a list of available tags, please read tags.txt



== Plugin Support And Feature Request ==
If you've read over this readme carefully and are still having issues, if you've discovered a bug, or have a feature request, please 
contact me via my contact page.


== About and Credits ==
WP Photo Album was originally created by R.J. Kaplan.
WP Photo Album Plus is further developed by J.N. Breetvelt ( http://www.opajaap.nl/ )

== Licence ==
WP Photo Album is released under the GNU GPL licence. ( http://www.gnu.org/copyleft/gpl.html )