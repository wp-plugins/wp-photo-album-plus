=== WP Photo Album Plus ===
Contributors: opajaap
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=OpaJaap@OpaJaap.nl&item_name=WP-Photo-Album-Plus&item_number=Support-Open-Source&currency_code=USD&lc=US
Tags: photo, album, photoalbum, gallery, slideshow, sidebar widget, photowidget, photoblog, widget, qtranslate, multisite, network
Version: 4.0.4
Stable tag: trunk
Author: J.N. Breetvelt
Author URI: http://www.opajaap.nl/
Requires at least: 3.0
Tested up to: 3.2.1

This plugin is designed to easily manage and display your photo albums and slideshows in a single as well as in a network WordPress site. 
Additionally there are four widgets: Photo of the day, a Search Photos widget, a Top Ten Rated photo widget and a Mini slideshow widget.
Visitors can leave comments on individual photos.

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
* You can add a Photo of the day Sidebar Widget that displays a photo which can be changed every hour, day or week.
* You can add a Search Sidebar Widget which enables the visitors to search albums and photos for certain words in names and descriptions.
* You can enable a rating system and a supporting Top Ten Photos Sidebar Widget that can hold a configurable number of high rated photos.
* You can enable a comment system that allows visitors to enter comments on individual photos.
* Apart from the full-size slideshows you can add a Sidebar Widget that displays a mini slideshow.
* There is a General Purpose widget that is a text widget wherein you can use wppa+ script commands.
* Almost all appearance settings can be done in the settings admin page. No php, html or css knowledge is required to customize the appearence of the photo display.
* International language support for static text: Currently included foreign languages files: Dutch, Japanese, French(outdated), Spanish, German.
* Inrernational language support for dynamic text: Album and photo names and descriptions fully support the qTranslate multilanguage rules and have separate edit fields for all qTranslate activated languages.

Plugin Admin Features:

You can find the plugin admin section under Menu Photo Albums on the admin screen.

* Photo Albums: Create and manage Albums.
* Upload photos: To upload photos to an album you created.
* Import photos: To bulk import photos to an album that are previously been ftp'd.
* Settings: To control the various settings to customize your needs.
* Sidebar Widget: To specify the behaviour for an optional sidebar widget.
* Help & Info: Much information about how to...

== Installation ==

= Upgrade notice =
This version is: Major rev# 3, Minor rev# 0, Fix rev# 2, Hotfix rev# 000.
If you are upgrading from a previous Major or Minor version, note that:
* If you modified wppa_theme.php and/or wppa_style.css, you will have to use the newly supplied versions. The previous versions are NOT compatible.
* If you set the userlevel to anything else than 'administrator' you may have to set it again. Note that changing the userlevel can be done by the administrator only!
* You may have to activate the sidebar widget again.

= Standard installation when not from the wp plugins page =
* Unzip and upload the wppa plugin folder to wp-content/plugins/
* Make sure that the folder wp-content/uploads/ exists and is writable by the server (CHMOD 755)
* Activate the plugin in WP Admin -> Plugins.
* If, after installation, you are unable to upload photos, check the existance and rights (CHMOD 755)
of the folders wp-content/uploads/wppa/ and wp-content/uploads/wppa/thumbs/. 
In rare cases you will need to create them manually.
* If you upgraded from WP Photo Album (without plus) and you had copied wppa_theme.php and/or wppa_style.css 
to your theme directory, you must remove them or replace them with the newly supplied versions.

== Frequently Asked Questions ==

= How does the search widget work? =

* A space between words means AND, a comma between words means OR.
Example: search for 'one two, three four, five' gives a result when either 'one' AND 'two' appears in the same (combination of) name and description. 
If it matches the name and description of an album, you get the album, and photo vice versa.
OR this might apply for ('three' AND 'four') OR 'five'. Albums and photos are returned on one page, regardless of pagination settings, if any. 
That's the way it is designed.

= How can i translate the plugin into my language? =

* See the documentation on the WPPA+ Docs & Demos site: http://wppa.opajaap.nl/?page_id=1349

= How do i install a hotfix? =

* See the documentation on the WPPA+ Docs & Demos site: http://wppa.opajaap.nl/?page_id=823

= What to do if i get errors during upload or import photos? =

* It is always the best to downsize your photos to the Full Size before uploading. It is the fastest and safest way to add photos tou your photo albums.
Photos that are way too large take unnessesary long time to download, so your visitors will expierience a slow website. 
Therefor the photos should not be larger (in terms of pixelsizes) than the largest size you are going to display them on the screen.
WP-photo-album-plus is capable to downsize the photos for you, but very often this fails because of configuration problems. 
Here is explained why:
Modern cameras produce photos of 7 megapixels or even more. To downsize the photos to either an automaticly downsized photo or
even a thumbnail image, the server has to create internally a fullsize fullcolor image of the photo you are uploading/importing.
This will require one byte of memory for each color (Red, Green, Blue) and for every pixel. 
So, apart form the memory required for the server's program and the resized image, you will need 21 MB (or even more) of memory just for the intermediate image.
As most hosting providers do not allow you more than 32 MB, you will get 'Out of memory' errormessages when you try to upload large pictures.
You can configure WP to use 64 MB (That would be enough in most cases) by specifying `define(‘WP_MEMORY_LIMIT’, ‘64M’);` in wp-config.php, 
but, as explained earlier, this does not help when your hosting provider does not allows the use of that much memory.
If you have control over the server yourself: configure it to allow the use of enough memory.
Oh, just Google on 'picture resizer' and you will find a bunch of free programs that will easily perform the resizing task for you.


== Changelog ==

= Wish List =

* Indication of NEW for photos and albums with configurable NEW period.
* Borders around fullsize images.
* Bulk actions on comments.

= 4.0.4 =

= Bug Fixes =

* When the coverwidth is set so that there will be more than 3 covers in a row, they will show up no longer in one column.

= Other changes = 

* Added height and width attributes to img tags. This may fix some layout problems with old browsers.

= Known issues =

* The Big Browse Buttons are transparent. IE 6 does not know about transparency. Therefor the slidshow will not display properly in IE6 with BBB's enabled.

= 4.0.3 =

= Bug Fix =

* Repaired using get_bloginfo('wpurl') as opposed to get_bloginfo('url') to fix problem where sites using a non-default site address stopped displaying photos.

= Other changes =

* Changed display of phpinfo (Table X)

= 4.0.2 =

= Bug Fixes =

* Photo of the day admin caused a fatal error, fixed

= New Features =

* You can select *Top* and *Bottom* additionally to Right and Left for coverphoto display position (Table IV item 13). 
A spinoff of this enhancement are the folowing:
* The 2 and 3 column treshold values (Table I items 17 and 18) have been replaced by Maximum cover width (item 17).
This basically does the same as the 2 column treshod value, but is more user understandable and makes the 3col treshold superfluous.
Note: If you had set the 2 column treshold exactly to the column width before, 
you may need to change this setting as the old value (that will be used) will result in one column instead of two.
* There is a new item 18: Minimal cover text frame height, that makes it easier to get the covers equal in height. 
Additionally will you need to keep the coverphotos all landscape (with the same aspect ratio) or portrait to keep the covers equal in height.

= 4.0.1 =

* The Big Browse Buttons are now optional (Table II item 19).
* The BBB's will have no border.


= 4.0.0 =

= New features =

* WPPA+ Now supports multisite installations.

= Bug fixes =

* A clicking monkey will no longer be able to get the slideshow into a hangup state.

= Other changes =

* The sequence order of the slideshow parts (bars and photoframe) is now settable in the Settings screen (Table IX item 6.x). 
There is no longer a known reason as of to modify wppa-theme.php.
* The Big Browse Buttons are now invisible, but have a title and a cursor and have the size of half the slideframe each.
* The Filenames now comply with the wp coding standards.
* The Photo Links can be set to overrule with the photo specific link - if any. 
This behaviour can be set for all photo link types independantly. 
The 'Use photo specific link' linktype is hereby obsolete and has been removed as a selection option.
* Table X has been extended with WPPA+ constants and all other PHP settings.
* Border radius in css3 format added (IE9)

= Wish List =

* Cover photo above or below the text (Vertical shape of cover).
* Indication of NEW for photos and albums with configurable NEW period.


= 3.1.8 =

= Bug fixes = 

* fixed an errormessage in debug mode

= New features = 

* You can set the thumbnail popup image size now explicitly. (Before it was the unscaled thumbnail image)


= 3.1.7 =

= Bug Fixes =

* After introduction of the link with print option, all other linktypes failed. Fixed.


= 3.1.6 =

= New features =

* New link type added for thumbnails and topten thumbnails: the fullsize photo with a print button. 
This will open the fullsize photo in a new browser window and enables you to print the photo with the description below it.

= Bug Fixes =

* Fixed an RSS bug in displaying thumbnails.

= Other changes =

* Reverted the change made in version 3.1.3 for the algorithm to decide if the indicator must be printed. 
It turned out to create a bigger problem than it solved. (This change was made in 3.1.5 but not yet documented as such.


= 3.1.4 =

= Bug Fixes =

* The static text in the photo comment form and alert boxes is now properly translatable.
* The behaviour after input of incomplete comment has been corrected.

= Other changes =

* Cosmetic and reliability enhancements in slideshow.
* Update text 3.1.3 Other changes to fix the first item issue.


= 3.1.3 =

= Other changes =

* The algoritm to decide if the indicator [WPPA+ Photo display] must be printed has been improved. 
Only the first in a list of excerpts (archive or search results when the_excerpt() is used as opposed to the_content()) may be wrong.
You can correct this by adding the following line of code just prior to *the_excerpt();* in the template files involved: *global $wppa; $wppa['is_excerpt'] = true;*
* Uses display name rather than login name in comments on photos.

= 3.1.2 =

= Bug Fixes =

* Fixed breaking js execution caused by a newline in an comment edit.

= 3.1.1 =

= Bug Fixes =

* You can have single quotes in comments now.

= Other changes =

* Removed changelog prior to version 3.0.0
* Minor cosmetic changes

= 3.1.0 =

= New Features =

* A per photo based comment system has been added.
* Big Browsing Buttons. When hovering near the left and right edges of the fullsize image when the slideshow is stopped, big left (previous) and right (next) browse buttons appear.

= Enhancements =

* Admin pages load only when used, this results in less server memory usage and speed-up of all admin pages.
* The name and description under the fullsize images is now combined in a wppa+ box. You can still set fonts individually, you can also switch them on/of individually.
If you like the 'old' display method, this is still possible; see the explanation in /theme/wppa_theme.php.

= Bug Fixes =

* You can manipulate and delete Albums and Photos now even when their id is greater then 2147483647.

= 3.0.7 =

= Enhancements =

* The way the plugin is re-activated after an update has been changed due to the fact that wp does no longer run the activation hook after update.
You should no longer get the messages that the 'database rev is not yet updated' and 'i fixed that for you'. 
Manual re-initialization still remains possible with the settings page table VIII item 3.
* The horizontal alignment of the photo of the day widget content can be set to none, left, center or right on the photo of the day admin page.
The text goes along; if you want the photo and the text align differently, set alignment to --- none --- and use css (classes wppa-widget-photo and wppa-widget-text).
* Added script keyword: #last. %%photo=#last%% or %%mphoto=#last%% gives the last added photo. %%album, %%cover, %%slide=#last%% etc gives the last added album.
* Better qTranslate support for the photo of the day admin page.

= Bug fixes =

* In an archive, you will get a marker at the place of an wppa+ invocation rather than the display of javascript.

= 3.0.6 =

= New features =

* You can now easily disable the display of all text except the album title from the albumcover. Table II item 17.
* You can append &debug (?debug if it is the first argument) in the adress bar of the browser to switch debug mode on.
An optional integer can be set to set the php error reporting switches. Default = 6143 (E_ALL). Example: &debug=-1 (switches everything on: wppa debug, php's E_ALL and E_STRICT).
This feature can be anabled/disabled by the setting in Table IX item 5.
If switched on, the WPPA+ system will produce diagnostic messages, together with the normal php errors and warnings.
It works for both admin as well as site views. Links within the WPPA+ system include the debug switch (and optional value).
The main wp admin menu items are beyond the scope of this feature. Press the menuitem, append &debug to the adressbar here.
* You can optionally switch the filmstrip and/or the browsebar on in the slideshow widget.
* Clicking the counter (Photo xx of yy, or xx / yy in the mini version) will start/stop the slideshow.
* You can specify an album for the topten widget. Now it is usefull to have more than one topten widget by using different albums.
* A start has been made with 'keywords' in places of numbers. You can issue the script command: %%photo=#potd%% to use the photo of the day in a page or post.

= Enhancements =

* In a widget, the album cover text will appear above or below the cover photo. This can be set by the coverphoto left/right switch. Table IV item 13.
This works also for "thumbnail as covers".
* The Photo of the day widget photo will be centered horizontally, no padding setting is required anymore.
* The filmstrip will be half the normal size in widgets.

= 3.0.5 =

= Bug fixes =

* IMPORTANT Fix: All problems that are related to pre-rendering are fixed. 
The problems with themes like Thesis and plugins like the face-book-meta-tags-plugin that 
perform a pre-rendering of a post or excerpt are solved now. 
The restrictions on using the rating system (that did not work anyway) are no longer applicable.
* Under some circumstances when using qTranslate, the proper language file was not loaded. Fixed.

= Hot fixes after initial release =

* 001: Fixed erroneous link in albumcover

= 3.0.4 =

= New features = 

* You can back-up and restore the settings and reset them to default values.
* Added table X in the settings panel, being a read only table displaying the php configuration.

= Enhancements =

* Improved error reporting and documentation of limitations in admin pages.

= Bug fixes =

* Fixed an no harmfull warning in photo of the day widget admin page.
* Removed a superfluous p-opening tag.

= Known problems =

* The Thesis theme has a problem with the <input > field that is required for the rating system. (nonce field).
The rating system should be disabled in that case (using Thesis).

= Hotfixes after initial release =

* 001: Added class wppa-slideshow-browse-link to enable hiding it with display: none. This was a special cutomer request and not an error.
* 002: Photo specific link will now also be copied during a copy photo action.
* 003: Removed an empty <p></p> right before a wppa invocation. 
* 004: Fix for facebook plugin (?)

= 3.0.3 =

= New features = 

* Increase configurability of links from album cover photo.
* A re-initializing action (Table VIII, item 3) has been added. This will be helpfull in multiblog (network) sites.

= Bug fixes =

* Includes all hot-fixes since 3.0.2.000.
* Minor cosmetic changes in the new settings page.

= Hot fixes after initial release =

= Known problems =

* The Thesis theme has a problem with the <input > field that is required for the rating system. (nonce field).
The rating system should be disabled in that case (using Thesis).


= 3.0.2 =

= New features = 

* The Settings page has been rewritten to make it more user friendly. 
All settinges are grouped into tables, and are identifiable by its table number and item number.
* Increased link configurability. You can link mphotos and thumbnails now also to the plain file. 
You can define photo specific links: All photos can have a unique link url and title. 
You can choose to use that link in all 5 different places where a photo link can be configured. 
Please check the link settings in the Settings screen, Table VI. You might want to change something there.
* Additionally to the family and size you can now also set the colors for the fonts used in wppa+.

= Bug fixes =

* Includes all hot-fixes since 3.0.1.000.
* The mouseover effect now also works on TopTen thumbnail images.
* Fix for Column width = auto. This works now the same like %%size=auto%%

= Hot fixes after initial release =

* 001: Made noncefield conditional to rating system enabled
* 002: Admin functions now also work in SSL admin
* 003: If an image has a link configured, the cursor will be a pointer (hand).


= 3.0.1 =

= New features =

* WPPA+ Now supports Multi language sites that use qTranslate. 
Both album and photo names and descriptions follow the qTranslate multilanguage rules.
In the Album Admin page all fields that are multilingual have separate edit fields for each activated language.
For more information on multilanguage sites, see the documentation of the qTranslate plugin.

= Enhancements =

* You can link media-like photos (those made with %%mphoto=..%%) to a different (selectable) page, either to a full-size photo on its own or in a slideshow/browseable.
* You will now get a warning message inclusive an uncheck of the box if your jQuery version does not support delay and therefor not the fadein after fadeout feature.
* Improved consistency in the layout of the different types of navigation bars.

= Pending enhancement requests =

* Multisite support
* More than one photo of the day
* Fullscreen slideshow

= Known bugs =

* None, if you find one, please let me know and i will fix 'm

= Hot fixes since the initial release =

* 001: HTML in photo of the day widget fixed
* 002: Fixed 'Start undefined'
* 003: You can now rotate images when they are already uploaded
* 004: Photo of the day option change every pageview added
* 005: Photo of the day split padding top and left
* 006: If Filmstrip is off you can overrule display filmstrip by using %%slidef=.. and %%slideonlyf=..
* 007: Clear:both added to thumbnail area
* 008: Fixed a problem where photos were not found if the number of found photos was less than or equal to the photocount treshold value
* 009: You can now upload zipfiles with photos if your php version is at least 5.2.7.
* 010: Fixed a Invalid argument supplied for foreach() warning in upload.
* 011: Fixed a wrong link from thumbnail to slideshow.
* 012: Changed the check for minimal size of thumbnail frame.
* 013: Fixed a problem where a bullet was displayed as &bull in some browsers.
* 014: Fixed a problem where the navigation arrows in the filmstrip were not hidden if the startstop bar was disabled.
* 015: New feature: If slideshow is enabled, double clicks on filmthumbs toggles Start/stop running slideshow. Tooltip documents it.
* 016: Slides and filmthumbs have the same sequence now when ordering is Random.
* 017: Some people do not read the settings page and get in panic when they see two or three colums of album covers after an upgrade, so i changed the defaults for the columns tresholds to 1024.
* 018: TopTen widget initializes runtime also now, just in case it is the first.
* 019: Fixed alignment problem in multi column, unequal cover heights.
* 020: Photo of the day widget now also initializes runtime.
* 021: Fix for pre-rendering themes like thesis.

= 3.0.0 =

= New features =

* You can link thumbnails to different (selectable) page, either to a full-size photo on its own or in a slideshow/browseable.
* You can link the photo of the day to a full-size photo on its own or in a slideshow/browseable or to the current photos album contents display (thumbnails).
* You can set the thumbnail display type to --- none ---. This removes the 'View .. photos' link on album covers, while keeping the 'View .. albums' link.
* When the Slideshow is disabled and there are more than the photocount treshold photos, the 'Slideshow'-link is changed to 'Browse photos' with the corresponding action.
* The front end (theme) is now seperately translatable. Only 43 words/small sentences need translation. A potfile is included (wppa_theme.pot).
* You can now easy copy a single photo to an other album in the Photo Albums -> Edit album admin page.
* There is a new script command: %%mphoto=..%%. This is an alternative for %%photo=..%% and displays the single photo with the same style as normal media photos with background and caption. No associated links yet.

= Bug fixes =

* The 'Slideshow' and 'Browse photos' link now also point to the page selected in the edit album form.

= Hot fixes after initial release =

* 001: [caption] is not allowed to have html (wp restriction), tags are now removed from photo description for use with [caption]
* 002: Fixed a breadcrumb nav that did not want to hide itself when Display breadcrumb was unchecked
* 003: You can now import media photos from the upload directory you specified in the wp media settings page also when it is not the default dir.
* 004: Fixed a problem where, when pagination is off, in a mixed display of covers and thumbs, the covers were not shown.
* 005: added class size-medium to mphotos ([caption])


= Notes =

* Due to internal changes, there is a speed-up of apprix 30% with respect to earlier versions.
* Due to internal changes, you will have to re-modify wppa_theme.php if you used a modified one. wppa_theme is now a function.
* Due to internal changes, it is most likely that this problem will be fixed: http://wordpress.org/support/topic/plugin-wp-photo-album-plus-page-drops-when-activated-on-page?replies=24#post-1965780
* If you had set *No Links* for thumbnails, you will have to set it again.


== About and Credits ==

* WP Photo Album Plus is extended with many new features and is maintained by J.N. Breetvelt, ( http://www.opajaap.nl/ ) a.k.a. OpaJaap
* Thanx to R.J. Kaplan for WP Photo Album 1.5.1, the basis of this plugin.

== Licence ==

WP Photo Album is released under the GNU GPL licence. ( http://www.gnu.org/copyleft/gpl.html )