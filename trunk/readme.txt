=== WP Photo Album Plus ===
Contributors: opajaap
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=OpaJaap@OpaJaap.nl&item_name=WP-Photo-Album-Plus&item_number=Support-Open-Source&currency_code=USD&lc=US
Tags: photo, album, gallery, slideshow, sidebar widget, photo widget, phtoblog
Version: 1.9
Stable tag: trunk
Author: J.N. Breetvelt
Author URI: http://www.opajaap.nl/
Requires at least: 2.8
Tested up to: 3.0

This plugin is designed to easily manage and display your photo albums and slideshows within your WordPress site. 


== Description ==

This plugin is designed to easily manage and display your photo albums and slideshows within your WordPress site. 

* You can create various albums that contain photos as well as sub albums at the same time.
* There is no limitation to the number of albums and photos.
* There is no limitation to the nesting depth of sub-albums.
* You have full control over the display sizes of the photos.
* You can specify the way the albums are ordered.
* You can specify the way the photos are ordered within the albums, both on a system-wide as well as an per album basis.
* The visitor of your site can run a slideshow from the photos in an album by a single mouseclick.
* The visitor can see an overview of thumbnail images of the photos in album.
* The visitor can browse through the photos in each album you decide to publish.
* You can add a Sidebar Widget that displays a photo which can be changed every hour, day or week.


Plugin Admin Features:

You can find the plugin admin section under Menu Photo Albums on the admin screen.

* Photo Albums: Create and manage Albums.
* Upload photos: To upload photos to an album you created.
* Settings: To control the various settings to customize your needs.
* Sidebar Widget: To specify the behaviour for an optional sidebar widget.
* Help & Info: The screen you are watching now **And much more**

== Installation ==

* Unzip and upload the wppa plugin folder to wp-content/plugins/
* Make sure that the folder wp-content/uploads/ exists and is writable by the server (CHMOD 755)
* Activate the plugin in WP Admin -> Plugins.
* If, after installation, you are unable to upload photos, check the existance and rights (CHMOD 755)
of the folders wp-content/uploads/wppa/ and wp-content/uploads/wppa/thumbs/. 
In rare cases you will need to create them manually.
* If you upgraded from WP Photo Album (without plus) and you had copied wppa_theme.php and/or wppa_style.css 
to your theme directory, you must remove them or replace them with the newly supplied versions.
This might also be the case after an update to a newer version to take advantage of all new features.

== Frequently Asked Questions ==

= How can i translate the plugin into my language? =

* Find on internet the free program POEDIT, and learn how it works.
* Use the file wppa.pot that is located in wp-photo-album-plus/langs to create or update
wppa-[your languagecode].po and wppa-[your languagecode].mo.
* Place these file in the langs subdir.
* If everything is ok, mail me the files and i will distribute them so other users can use it too.
* For more information on POT files, domains, gettext and i18n have a look at the I18n for 
WordPress developers Codex page and more specifically at the section about themes and plugins.

= Do i have to upload my photos again? =

No, if you had WP Photo Album before, you can simply take advantage of the new features 
that WP Photo Album Plus provides to you.

= Do i have to remake the albums? =

No, you will still have the albums you had. Now you can place them inside an other existing album,
that even may contain photos itself, or use it with the embedded sidebar widget.

= What if i regret the upgrade, can i downgrade back to WP Photo Album version 1.5.1? =

First of all: I bet you won't. But if you really want to go back, 
simply de-activate WP Photo Album Plus and re-activate WP Photo ALbum. 
but remember: **You can not run both versions at the same time.**
You will see the sub albums you created appear as normal albums.
The sort order information will have no longer effect.

= How can i implement auto scroll back when browsing full scale images? =

This topic is obsolete as per version 1.8.4

Find the php file in your theme's directory where the `<body >` tag is defined.
In the default theme this is header.php.
Just before this line insert the following code:

`
<?php
	if (isset($_GET['scrollx'])) $X = $_GET['scrollx']; else $X = 0;
	if (isset($_GET['scrolly'])) $Y = $_GET['scrolly']; else $Y = 0;
?>
`

add the 'onload' event attribute of the body-tag to read:

`onload="window.scrollTo(<?php echo($X . ', ' . $Y) ?>)"`

if there is already an 'onload' attribute, modify it like:

`onload="window.scrollTo(<?php echo($X . ', ' . $Y) ?>); yourfunction()"`

Complete example:

Before modification:
`
<body <?php body_class(); ?> onload="startheader()" onunload="stopheader()">
`
After modification:
`
<?php
	if (isset($_GET['scrollx'])) $X = $_GET['scrollx']; else $X = 0;
	if (isset($_GET['scrolly'])) $Y = $_GET['scrolly']; else $Y = 0;
?>
<body <?php body_class(); ?> onload="window.scrollTo(<?php echo($X . ', ' . $Y) ?>); startheader()" onunload="stopheader()">
`


== Changelog ==

= 1.9 =
* IF YOU COPIED/MODIFIED WPPA_THEME.PHP AND WPPA_STYLE.CSS Please study the files that come with this version and review your modifications!
* You can now have multiple sequences of `%%wppa.. %%cover'.. %%size=..` tags.
* You need no longer put the tags on one line, all information inside a tag sequence will be removed. Between the tag sequenses as well as before the first and after the last there may be content that will be displayed.
* You can also call the album more then once in a page template.

Example#1 text in Post or Page:

`
	Text Before
	%%wppa%%
	%%cover=10%%
	%%size=150%%
	Text Between 1
	%%wppa%%
	%%album=19%%
	%%size=350%%
	Text Between 2
	%%wppa%%
	%%cover=1%%
	Text After	
`

Example#2 php code in page template:

`
<?php 
	if (function_exists('wppa_albums')) {
	echo('Text Before<br />');
	wppa_albums(10, 'cover', 150);
	echo('<br />Text Between 1<br />');
	wppa_albums(19, 'album', 350); 
	echo('<br />Text Between 2<br />');
	wppa_albums(1, 'cover');
	echo('<br />Text After<br />');
	} 
	else echo('Photo software currently unavailable, sorry'); 
?>
`

As nothing in life is permanent, this enhancement revokes the previously announced permanent restriction of having only one occurence of an album or cover in a page or post.
* You can now have different sizes for thumbnail images and cover photos. The regeneration of thumbnails now only occurs when the largest of the two enters a diffrent range of 25 pixels.
* Fix for sizing problem in IE6 by removing max-width and max-height from the style attributes.
* Fixed rounding error in calculating thumbnail size. This may result in new thumbnails being one pixel larger. Regeneration of thumbnails will overcome this.
* The 'Enlarge fullsize photos' switch in the admin settings page now functions as expected.
* Various small fixes.
* Adder wrappers for coverphoto and thumbnails.
* You can now align thumbnails at top, center or bottom.
* Split up the code in functional pieces.
* Added functions: wppa_get_total_album_count(), wppa_get_youngest_album_id() and wppa_get_youngest_album_name() that are simple but not yet documented. For more info see the sourcecode: wppa_functions.php

= 1.8.5 =
* This is an intermediate version that never was released.

= 1.8.4 =
* Browsing photos and the slideshow are merged. You can now easily switch between them. This also fixes the scrolling issue.
* Fix to prevent loading stylesheet more than once.
* Fixed an HTML error where photo description or name contained newline characters.
* Added optional parameters to wppa_get_albums() to make its use more flexible. See: tags.txt.
* Improved security of wppa_get_albums()

= 1.8.3 =
* You can now link a album title and coverphoto to a WP page as opposed to the album's content. The album's content will still be reacheable by the View- and Slideshow links.
* You can now decide not to include a homelink in the breadcrumb navigation.
* Fixed some incomplete / erroneous links.
* While browsing full size images, the double arrow brackets will now have a transparent background.
* Minor fixes and improved error handling.
* There is now a way to automatically scroll back to the desired position when browsing full scale images. For more info: See the section Frequently asked questions.
If you made a copy of wppa_theme.php or wppa_style.css into your theme directory and you want to make full use of the new improvements, please redo your modifications (if still needed) using a fresh copy of the original files.

= 1.8.2 =
* You can now configure a link to a page from the sidebar widget photo.
* The widget will now display the correct subtitle in all cases.
* Security issue: Silence is golden index.php added to all directories.

= 1.8.1 =
* Fixed a fatal error after regeneration of thumbnails

= 1.8 =
* An optional Sidebar Widget has been added.
* A complete re-write of the help and info section.
* Increased configurability for the use of single albums in posts and pages.
* The plugin is now translatable. Dutch language files are included.
* There is now a way to configure the number of album cover photos for albums that contain sub albums only.
* Re-designed admin options page. It is now called: Settings.
* Various cosmetic changes to admin pages.

= 1.7.2 =
* Fixed the problem that in IE the accesslevel could not be changed and the options could not be saved.

= 1.7.1 =
* There is now a simple self-explaining way to recover from an interrupted regeneration of thumbnail images.
* Fixed a typo in admin_styles.css.
* Parent album Names rather than Id's are listed in the Manage Albums table.
* Parent Id's can be --- separate ---. They do not appear in the generic album where all albums are listed with a parent set to --- none ---, and are especially ment for use as parent albums (covers) for single albums in posts or pages with correct breadcrumb display.
* When displaying a blog with more than one post using an album, only the first album was displayed. This has been fixed. You can still have only one album per page/post.
* When displaying an album in a page or post, you can now overrule the default full size.
* You can now set the order of photos in an album to the default setting from the Options page.
* You can now specify whether photos may be enlarged to meet the Full Size crteria. Turning off this feature (recommended) will speed up the start of slideshows with hundreds of photos.

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

== About and Credits ==

* WP Photo Album Plus is extended with many new features and is maintained by J.N. Breetvelt, ( http://www.opajaap.nl/ ) a.k.a. OpaJaap
* Thanx to R.J. Kaplan for WP Photo Album 1.5.1, the basis of this plugin.

== Licence ==

WP Photo Album is released under the GNU GPL licence. ( http://www.gnu.org/copyleft/gpl.html )