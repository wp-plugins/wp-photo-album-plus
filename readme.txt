=== WP Photo Album Plus ===
Contributors: opajaap
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=OpaJaap@OpaJaap.nl&item_name=WP-Photo-Album-Plus&item_number=Support-Open-Source&currency_code=USD&lc=US
Tags: photo, album, photoalbum, gallery, slideshow, sidebar widget, photowidget, photoblog, widget, qtranslate, cubepoints, multisite, network, lightbox, comment, watermark, iptc, exif, responsive, mobile, cloudinary, fotomoto
Version: 5.4.18
Stable tag: trunk
Author: J.N. Breetvelt
Author URI: http://www.opajaap.nl/
Requires at least: 3.9
Tested up to: 4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin is designed to easily manage and display your photo albums and slideshows in a single as well as in a network WordPress site.

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
* Individual thumbnails and slides can be linked to off site urls.
* You can add a Photo of the day Sidebar Widget that displays a photo which can be changed every hour, day or week.
* You can add a Search Sidebar Widget which enables the visitors to search albums and photos for certain words in names and descriptions.
* You can enable a rating system and a supporting Top Ten Photos Sidebar Widget that can hold a configurable number of high rated photos.
* You can enable a comment system that allows visitors to enter comments on individual photos.
* You can add a recent comments on photos Widget.
* Apart from the full-size slideshows you can add a Sidebar Widget that displays a mini slideshow.
* There is a widget to display a number of most recently uploaded photos. It can be configured systemwide and/or on an album basis.
* There is a General Purpose widget that is a text widget wherein you can use wppa+ script commands.
* There is an album widget that displays thumbnail images that link to album contents.
* There is a QR code widget that will be updated when the content of the page changes.
* There is a tag cloud widget and a multi tag widget to quickly get a selection of photos with (a) certain tag(s).
* There is an upload widget that allows for frontend uploads even when no wppa+ display is on the page.
* Almost all appearance settings can be done in the settings admin page. No php, html or css knowledge is required to customize the appearence of the photo display.
* International language support for static text: Currently included foreign languages files: Dutch, Japanese, French(outdated), Spanish, German.
* International language support for dynamic text: Album and photo names and descriptions fully support the qTranslate multilanguage rules.
* Contains embedded lightbox support but also supports lightbox 3.
* You can add watermarks to the photos.
* The plugin supports IPTC and EXIF data.
* Supports WP supercache. The cache will be cleared whenever required for wppa+.
* Supports Cube Points. You can assign points to comments and votes.
* There is an easy way to import existing NextGen galleries into WPPA+ albums.

Plugin Admin Features:

You can find the plugin admin section under Menu Photo Albums on the admin screen.

* Photo Albums: Create and manage Albums.
* Upload photos: To upload photos to an album you created.
* Import photos: To bulk import photos to an album that are previously been ftp'd.
* Settings: To control the various settings to customize your needs.
* Sidebar Widget: To specify the behaviour for an optional sidebar photo of the day widget.
* Help & Info: Much information about how to...

Translations:

There are translations in many languages. The frontend and admin sides are separately translatable. 
<ul>
<li>Dutch translation by OpaJaap himself (<a href="http://www.opajaap.nl">Opa Jaap's Weblog</a>) (both)</li>
<li>Slovak translation by Branco Radenovich (<a href="http://webhostinggeeks.com/user-reviews/">WebHostingGeeks.com</a>) (frontend)</li>
<li>Polish translation by Maciej Matysiak (both)</li>
<li>Ukranian translation by Michael Yunat (<a href="http://getvoip.com/blog">http://getvoip.com</a>) (both)</li>
<li>Italian translation by Giacomo Mazzullo (<a href="http://gidibao.net">http://gidibao.net</a> & <a href="http://charmingpress.com">http://charmingpress.com</a>) (both)</li>
</ul>

== Installation ==

= Requirements =

* The plugin requires at least wp version 3.1.
* The theme should have a call to wp_head() in its header.php file and wp_footer() in its footer.php file. 
* The theme should load enqueued scripts in the header if the scripts are enqueued without the $in_footer switch (like wppa.js and jQuery). 
* The theme should not prevent this plugin from loading the jQuery library in its default wp manner, i.e. the library jQuery in safe mode (uses jQuery() and not $()). 
* The theme should not use remove_action() or remove_all_actions() when it affects actions added by wppa+.
Most themes comply with these requirements. 
However, check these requirements in case of problems with new installations with themes you never had used before with wppa+ or when you modifies your theme.
* The server should have at least 64MB of memory.

= Upgrade notice =
This version is: Major rev# 4, Minor rev# 9, Fix rev# 6, Hotfix rev# 000.
If you are upgrading from a previous Major or Minor version, note that:
* If you modified wppa_theme.php and/or wppa_style.css, you will have to use the newly supplied versions. The previous versions are NOT compatible.
* If you set the userlevel to anything else than 'administrator' you may have to set it again. Note that changing the userlevel can be done by the administrator only!
* You may have to activate the sidebar widget again.

= Standard installation when not from the wp plugins page =
* Unzip and upload the wppa plugin folder to wp-content/plugins/
* Make sure that the folder wp-content/uploads/ exists and is writable by the server (CHMOD 755, some systems need CHMOD 777)
* Activate the plugin in WP Admin -> Plugins.
* If, after installation, you are unable to upload photos, check the existance and rights (CHMOD 755, some systems need CHMOD 777) of: 
for the single site mode installation: the folders .../wp-content/uploads/wppa/ and .../wp-content/uploads/wppa/thumbs/, 
and for the multisite mode installation (example for blog id 92): the folders path: .../wp-content/blogs.dir/92/wppa/ and .../wp-content/blogs.dir/92/wppa/thumbs/.
In rare cases you will need to create them manually. You can see the actual pathnames and urls in the lowest table of the Photo Albums -> Settings page.
* If you upgraded from WP Photo Album (without plus) and you had copied wppa_theme.php and/or wppa_style.css 
to your theme directory, you must remove them or replace them with the newly supplied versions. The fullsize will be reset to 640 px. 
See Table I-A1 and Table I-B1,2 of the Photo Albums -> Settings admin page.

== Frequently Asked Questions ==

= What do i have to do when converting to multisite? =

* If your WP installation is a new installation and you want to have only one - global - WPPA system, add to wp-config.php:
**define( 'WPPA_MULTISITE_GLOBAL', true );**
* If your WP installation is a new installation and you want to have a separate WPPA system for each sub-site, add to wp-config.php:
**define( 'WPPA_MULTISITE_INDIVIDUAL', true );**
* If your WP installation is older than 3.5 an you want to have only one - global - WPPA system, ad to wp-config.php:
**define( 'WPPA_MULTISITE_GLOBAL', true );**
* If your WP installation is older than 3.5 an you want to have a separate WPPA system for each sub-site, add to wp-config.php:
**define( 'WPPA_MULTISITE_BLOGSDIR', true );**
* If you want to convert your multisite WP installation that is prior to 3.5 to a version later than 3.5 and you want to convert an existing WPPA multisite installation
to the new multisite standards, do the following:
1. Update WP to version 3.5 or later.
1. Upate WPPA+ to version 5.4.7 or later.
1. Perform the network migration utility from the network admin which moves all the files from wp-content/blogs.dir/xx to wp-content/uploads/sites/xx
1. **Add** to wp-config.php: **define( 'WPPA_MULTISITE_INDIVIDUAL', true );**
1. If it is there, **Remove** from wp-config.php: **define( 'WPPA_MULTISITE_BLOGSDIR', true );**
	
= Which other plugins do you recommand to use with WPPA+, and which not? =

* Recommanded plugins: qTranslate, WP Super Cache, Cube Points, Simple Cart & Buy Now, Google-Maps-GPX-Viewer.
* Plugins that break up WPPA+: My Live Signature.
* Google Analytics for WordPress will break the slideshow in most cases when *Track outbound clicks & downloads:* has been checked in its configuration.

= Which themes have problems with wppa+ ? =

* Photocrati has a problem with the wppa+ embedded lightbox when using page templates with sidebar.

= Are there special requirements for responsive (mobile) themes? =

* Yes! Go to the Photo Albums -> Settings admin page. Enter **auto** in Table I-A1. Lowercase letters, no quotes.
* Do not use size="[any number]", use size="0.80" for 80% with etc.
* If you use the Slideshow widget, set the width also to **auto**, and the vertical alignment to **fit**.
* You may also need to change the thumbnail sizes for widgets in *Table I-F 2,4,6 and 8*. Set to 75 if you want 3 columns in the theme *Responsive*.

= After update, many things seem to go wrong =

* After an update, always clear your browser cache (CTRL+F5) and clear your temp internetfiles, this will ensure the new versions of js files will be loaded.
* And - most important - if you use a server side caching program (like WP Total Cavhe) clear its cache. 
* Make sure any minifying plugin (like W3 Total Cache) is also reset to make sure the new version files are used.
* Visit the Photo Albums -> Settings page -> Table VII-A1 and press Do it!
* When upload fails after an upgrade, one or more columns may be added to one of the db tables. In rare cases this may have been failed. 
Unfortunately this is hard to determine. 
If this happens, make sure (ask your hosting provider) that you have all the rights to modify db tables and run action Table VII-A1 again.

= How does the search widget work? =

* A space between words means AND, a comma between words means OR.
Example: search for 'one two, three four, five' gives a result when either 'one' AND 'two' appears in the same (combination of) name and description. 
If it matches the name and description of an album, you get the album, and photo vice versa.
OR this might apply for ('three' AND 'four') OR 'five'.
If you use indexed search, the tokens must be at least 3 characters in length.

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
As most hosting providers do not allow you more than 64 MB, you will get 'Out of memory' errormessages when you try to upload large pictures.
You can configure WP to use 128 MB (That would be enough in most cases) by specifying *define('WP_MEMORY_LIMIT', '128M');* in wp-config.php, 
but, as explained earlier, this does not help when your hosting provider does not allows the use of that much memory.
If you have control over the server yourself: configure it to allow the use of enough memory.
Oh, just Google on 'picture resizer' and you will find a bunch of free programs that will easily perform the resizing task for you.


== Changelog ==

See for additional information: http://wppa.opajaap.nl/?page_id=1459

= 5.4.18 =

= Bug Fixes =

* Security fix.
* Fixed a problem with random cover images.
* Fixed a link problem.

= New Features =

* Frontend upload uses ajax with progression bar. Default activated, you can switch it off in Table IV-A1.2.
* You can switch off the thumbnial preview images on the Import screen in Table IX-B14.

= Other Changes =

* Table IV-A2 is now Table IV-A1.2.
* Moved Table VIII-B14 to Table VIII-A12, because it it is harmless to run.
* Browsing through a lightbox set by keystrokes ( n=next, p=previous ) now loops around to the beginning or end. Both in 'normal' and in 'fullscreen' mode.
* You need no longer tick the box 'Originals only' on the import screen, non originals are neglected always now.
* You can turn off preview images on the import screen at Table IX-B14.
* Changed jQuery(..).attr( 'value' ..) to jQuery(..).val(..) in wppa.(min.)js for compatibility with older versions of jQuery.

= 5.4.17 =

= Bug Fixes =

* Thumbnails with non-standard aspect ratio were displayed with fullsize aspect ratio. Fixed.
* Fixed a problem that caused the pretty links not always being generated.
* Fixed legendatext when lightbox is single images.
* Regenerating thumbnails did not recompute sizes. Fixed.

= Other Changes =

* Added an errormessage that will be displayed if a multisite installation is mis-configured.

= 5.4.16 =

= Bug Fixes =

* Slideshows did not run on chrome an safari. Fixed.

= 5.4.15 =

= Bug Fixes =

* Fixed a layout problem in fullscreen lightbox legenda bar.
* Fixed a slashed quotes problem in comments.
* Fixed positionng of ubb's on responsive displays.

= New Features =

* Support for plugin EWWW Image Optimizer. Related settings: Table IX-D17 and Table VIII-B13.
* Support for Lazy Load and HTML optimizing plugins. The filmstrip will show up properly when Table IV-A19 is ticked.

= Other Changes =

* Go back to thumbnails icon in breadcrumb bar is now grayscale, blue on hover.
* Jpg image quality setting in Table IX-A2 now also applies to thumbnail image files.
* Random seed no longer in querystring. This enables the use of quickcache for pages with querystrings.
* Photosizes are now registered in the db to reduce file i/o. It is self learning, but you can run Table VIII-B14 to recalculate all.
* Improved russian translation.
* Import screen: you can switch off the zoom on hover of the thumbnails, and the non-originals will be hidden if you tick the Originals only box.
* Only querystring arguments used in wppa+ are now checked on php injection.
* Bulk edit goes to photosection directly on opening.

= 5.4.14 =

= Bug Fixes =

* Fixed a number of w3c validation errors.
* Fixed a linkproblem when permalink structure is standard.

= New Features =

* Added fullscreen modes to lightbox. When in lightbox, press f. Related settings: Table II-G2.1, Table IV-G6.

= Other Changes =

* Changed priority of action to add metatags to get the open graph metatags higher up on the page.

= 5.4.12 =

= Bug Fixes =

* Fixed open graph metatags so that the right image is supplied also if it is in a virtual album.
* Empty albums with only cover photo(s) will show a coverphoto in the albums widget.

= Other Changes =

* Console logs are now only printed in debug mode ( &debug ).
* Improved the content of metatags for featured photos.
* Performance improvement in indexing.

= 5.4.11 =

= Bug Fixes =

* Photo permalinks did not work for photos in albums with name like 'Album name extended' when the album 'Album name' also existed. Fixed.
* Photo permalinks for photos uploaded in a version before filnames were saved ( long ago ) are no longer erroneously generated.
* Fixed a layout problem with filmstrip in Chrome when zoomed out.
* Remote import stopped working. Fixed.
* Fixed a php notice in debug mode about undefined index.
* Fixed an erroneous link in breadcrumb while in tags selection.

= New Features =

* You can select ---none--- in Table I-G2: Magnifier cursor size. This sets the cursor to pointer and the tooltip to the photo name.
* Miniature pictures on the import screen.
* Added maintenance proc: Add GPX tag. Makes sure all photos with gpx data have at least tag: Gpx.

= Other Changes =

* Click on filmstrip image now also goes to that slide when the show is running.

= 5.4.10 =

= Bug Fixes =

* Tags and exifdatetime were not copied on copying photo. Fixed.
* Photonames in urls were not properly converted to their ids when the album spec was an enumeration. Fixed.
* Changing upload limits for nonstandard roles worked, but the new values were not shown in Table VII-B5. Fixed.
* All images now have an alt attribute if you configured it in Table IX-C4.
* If a slideshow shortcode followed a single image shortcode, all images of the slideshow were scaled to fill the entire column. Fixed.

= New Features =

* The **WPPA+ Uploader Photos** widget now also supports the restriction to look inside (multiple) parentalbum trees only.
* Width and horizontal alignment can now be set on the photo of the day widget admin page.
* Table IV-A18: You can now optionally create the .htaccess file in the wppa/ folder to grant access to your photofiles.
Switch it off if you want **All in one wp security plugin** to protect image hotlinking and you experience no other problems.

= Other Changes =

* Enhanced and more stable behaviour of Table IV-A5: Use short query args.
* Added function wppa_get_youngest_photo_ids( $n = '3' ); that returns an array of n most recently added photo ids.

= 5.4.9 =

= Bug Fixes =

* Fixed some consistency issues in photo file names that were a spinoff from the fixes in 5.4.8.

= Other Changes =

* The shortcode generator now also makes it possible to specify the parent albums for w#owner and w#upldr.

= 5.4.8 =

= Bug Fixes =

* Security fix

= 5.4.7 =

= Bug Fixes =

* The subtitle 'By:' on the photo of the day widget is now translatable.
* The selecion option '- select an album -' on the upload widget is now translatable.
* At setup time an erroneously .htaccess file will no longer be created in wp-content.
* Setting II-B5.1 was invisible when breadcrumbs were switched off. Fixed.

= New Features =

* Table II-G17 Overlay add owner to photoname. Same functionality as II-B5.1 and II-D1.1 for lightbox, works global for all lightbox name settings.
* Table II-D4.1 Popup owner. Same functionality as II-B5.1 and II-D1.1 for popups.
* Cache will now also be cleared for Quick Cache at settings change and uploads. ( Other supported caching plugins: wp-super-cache and w3Total cache. )
It is still not a guarantee but a reasonable attempt to keep the page content in accordance with the data on the site.
* In shortcodes, album="#upldr,username" may now also contain a parent album id: album="#upldr,username,parent".
* A parent album may now also be an enumeration of album ids, both in #upldr as in #owner album specifications. Example: album="#upldr,#me,12.34..37"
* New Thumbnail type for responsive themes in Table IV-C3: like album covers mcr.
* Table IX-F7: Predefined watermark text, now also supports w#timestamp.

= Other Changes =

* Album name may not be empty. This has always been a rule. You can no longer clear the album name, or change it into a (series of) dot(s).
* If your provider moved your installation to a different filesystem location, 
the sourcepath ( Table IX-H3 ) will now be corrected automaticly on entering the settings page.
* Table II-B5 and II-B5.1 ( Show photo name and add owner on slideshow ) now work independantly.
If only the owner is displayed, it will be without parenthesis.
* Table II-D1 and II-D1.1 ( Show photo name and add owner on thumbnails ) now work independantly.
If only the owner is displayed, it will be without parenthesis.
* On the Import screen you can now set a maximum number of photos to be found on remote locations. 
If the remote location is an imagefile and the filename exclusive extension is numeric, 
an assumption is made that - at a maximum of the max setting - the successive numbers may also be imagefiles.

= 5.4.6 =

= Bug Fixes =

* Links from thumnails to slideshow did not work for virtual albums when album names in urls was activated because virtual albums have no name. Fixed.
* The 'back to thumbnails' icon on slideshow breadcrumb now also works for virtual albums and no longer generate 404.
* Fixed a lay-out issue in Table V.

= New Features =

* Name a file so that it will be the default image loaded for the album Cover Photo. See Table IX-D14: Default coverphoto.
* Comment notify email to users already commented on the same photo. See Table IV-F5.1: Comment notify previous.
* Added a maintenance proc to add leading zeros to numeric photo names ( Table VIII-B11 ).

= Other Changes =

* Tags and cats can now contain spaces. ( 5.4.5.001 )
* Minor cosmetic changes in album admin.
* The session db table now relies on auto_increment.

= 5.4.5 =

= Bug Fixes =

* When clicking the link Remake files in front edit photo, the Exit and Refresh button did not come back. Fixed.

= New Features =

* If source files are kept ( Table IX-H1,2 ) and the system is single site or multisite_global, there is an automatic permalink structure to source image files.
Use the keyword w#pl in photo descriptions; if the source is not available, this keyword displays nothing.
The permalink has the following structure: `http://www.mysite.com/wp-content/wppa-pl/My-album/My-photo.jpg`.
My-album stands for the wppa+ album name, My-photo.jpg for the name of the photo.
You can change the name wppa-pl into any convenient filesystem safe name like 'albums' in Table IX-H14: Permalink root.
Make sure you choose a unique name inside .../wp-content/ for the permalink root.
There is no hierarchical album structure, and it is your responsability to have no duplicate album names, and no duplicate photo names inside the same album.
* The initial column width for responsive themes is now configurable in Table I-A1.1.
* The lightbox will be repeatedly initialized during page construction to facilitate clicks before document.ready.

= Other Changes =

* Fixed various security issues.

= 5.4.4 =

= Bug Fixes =

* Album description is no longer shown on thumbnail displays if the display contains thumbnails of various albums.
* On some installations accessing wppa+ photo files by their url ended up in a 404 error. Fixed. See Other Changes: .htaccess file.
* Fixed a problem in the interpretation of querystring arguments where album or photo names contained single or double quotes.
* Fixed a rights problem in front-end photo edit when upload rights were from Table VII-B only.
* Fixed a problem in shortcodes with type="covers". Links from displays of such shortcodes never showed photos, only albums.
* Albums selected with an album enumeration are now sorted accoring to the setting in Table IV-D1

= New Features =

* [wppa type="cover" album="#all"][/wppa] will now also work.

= Other Changes =

* A .htaccess file is now placed in the .../uploads/wppa/ folder to grant normal http access to wppa photo files.
This is to prevent the effect working down to wppa/ from other plugins .htaccess files rewriting http access in the .../uploads/ folder to an attachment page and/or generating a 404 error.

= 5.4.3 =

= Bug Fixes =

* Fixed a rare problem in the conversion to pretty links, i.e. where the url contained 'wppa-' before the '?'.
* Thumbnail popups squeezed to small square images in rare cases. Fixed. (5.4.2.001)
* Fixed improper escaped titles in breadcrumb bar. (5.4.2.002)

= New Features =

* Table I-F13,14: Min and max fontsize for tagclouds.
* Link from cover image to slideshow starting at cover image, see Table VI-B1: a slideshow starting at the photo.
* If Table IV-A3 ( Photo names in urls ) is checked, wppa+ will generate urls with the photo names in all places now.
* Added Table IV-A4 ( Album names in urls ). Works like IV-A2.
* Added Table IV-A5 ( Use short query args ). Omits the wppa- prefix in query string arguments. 
Use only when there are no conflicting plugins, like music plugins, that interprete &album= etc.
Note on using names in urls: Avoid duplicate album names and duplicate photo names within the same album!

= Other Changes =

* Removed the 'Create new style shortcode' checkbox from the TinyMce shortcode generator. It is replaced by the setting in Table IX-B13: We use scripts, with default being off.
It is highly recommended to use shortcodes only ( no scripts ) as the development of scripted shortcode features is frozen and only maintained for backward compatibility. 
New features will only be available in new style shortcodes.
* Shortcode generator for new style shortcodes has been rewritten and is now capable of generating all possible shortcodes.
* Table IV-A has been renumbered.

= 5.4.2 =

= Bug Fixes =

* Import using ajax stopped working. Fixed

= 5.4.1 =

= Bug Fixes =

* Treecounts were not updated when a sub-album was deleted. Fixed.
* Fixed a link problem from thumbnails to slideshow when ajax on and selection was #upldr.
* Upload box did not show up when frontend upload enabled but user had no upload rights and album was public. Fixed.
* Treecounts are now updated after a bulk change status action.
* The behaviour of the lightbox on album widget has been restored to the situation before version 5.3.10, i.e. the photos in the album will be shown in a set.
* On import, when there are only zipfiles to import, you will no longer be asked to specify a target album.

= New Features =

* New shortcode type: type="thumbs" will display the thumbnails only of an album that also contains sub-albums.
* New shortcode type: type="covers" will display the covers of the sub-albums of the given album. Do not confuse with type"cover"!!
* You can now set the minimum height of album covers ( Table I-D2 ) and of the text including the header ( Table I-D3 ) 
to make it easier to size the covers verically equally.
* Table VII-D10: Photo owner change. If checked, administrators can change the owner of the photo on the Album Admin -> Manage Photos screen.
* Download link on lightbox displays ( Table VI-C1.4 ).
* Added lightbox as a selection for the linktype of slideshow widget ( Table VI-A2 ).

= Other Changes =

* Erroneous treecounts will now be fixed automaticly, leaving a note in the wppa+ logfile ( See Table VIII-C1 ).
* Table I-D has been expanded and renumbered.
* Facebook share button is now new style; display type of share and like buttons is selectable. See Table II-C17.x

= 5.4.0 =

= Bug Fixes =

* All languages in names and descriptions are now indexed when using qTranslate and indexed search is used.
* The selection of the photo of the day when set to topten will follow the 'top' policy as specified in Table IV-E17.
* Fixed a bug in using custom css when Table IV-A9 ( Inline styles ) was UNchecked.
* Directory to album import continued to ask for a target album where it should not. Fixed.

= Other Changes =

* Many changes to reduce server load ( less db queries ) and client load ( improved responsive algorithm ) and to improve stability.
* Moved admin language files to separate plugin: <a href="http://wordpress.org/plugins/wppa-admin-language-pack/" >Wppa Admin Language Pack</a>
* The use of a modified wppa-theme.php file is strongly discouraged. 
The old version of this file is NOT compatible with this release. 
If you want to use a modified version, you will have to tick Table IV-A12
* The use of a modified wppa-style.css file is strongly discouraged. 
You should enter your custom css in Table IV-A10
If you want to use a modified version, you will have to tick Table IV-A11

= 5.3.11 =

= Bug Fixes =

* Fixed a problem in Album admin and in Settings for systems with very many registered users.

= New Features =

* New keywords in photo description: w#album, alias of w#albumid, will be replaced by album id; and w#albumname.
* Table IX-D15: FE Albums public. A switch to force the front-end created albums to be owned by --- public ---.
* You can hide the display of the commenter's email address in the notify email to the uploader by unticking the box in Table IV-F10.

= Other Changes =

* Dramatically reduced the number of queries for the generation of meta tags

= 5.3.10 =

= Bug Fixes =

* Uploading watermark file caused a fatal error due to non existant function. Fixed.
* Lightbox global stopped working. Fixed.
* On large systems ( > 250.000 photos ) deleting a photo sometimes causes a 500 error. Fixed.

= New Features =

* You can enable an album download link on the album cover in Table VI-C2. If VI-C2.1 is ticked, the original source files will be used if they have been saved during upload/import.

= Other Changes =

* Cosmetic changes to the schedule date/time display in album admin

= 5.3.9 =

= Bug Fixes =

* Maintenance procedure Convert filesystem ( VIII-A10 ) stopped working. Fixed.
* On ssl pages, the wppa+ internal symbol images had no secure urls. Fixed.
* The medals and the New indicator on thumbnail images were hidden when the thumbnail frame was some size bigger than the thumbnail size. Fixed.
* on a multisite where WPPA_MULTISITE_GLOBAL is defined as true, the tag widgets did work for the primary blog only. Fixed.
* Every user could edit albuminfo of ---public--- albums at the frontend. This is fixed: album admin rights are now required to do so.
* Treecounts on albumcovers were not always correct. Fixed.
* When album admin was enabled in Table VII-A, and owners only was set, one had access to all albums at backend album admin. Fixed.

= New Features =

* Schedueling of the publication of photos. See the Album admin and Photo admin pages.
* Added mintenance procedures: Re-add file-extensions to photonames and: Create all autopages.

= Other Changes =

* You can specify the Top criterium for topten displays created by shortcodes in Table IV-E17.
* Many code changes in preparation of video support, to be released in version 5.4.0.
* On the import screen, added a check if an album is selected when the update box is unticked.

= 5.3.8 =

= Bug Fixes =

* Running slideshow on the lightbox layer stopped when another slideshow was running on the page. Fixed.

= Other Changes =

* The alert boxes ( e.g. "Photo successfully uploaded" ) will be displayed when the page is almost loaded, if Table IV-A8 ( Defer javascript ) is ticked.
* Besides the Edit link there is now also a Delete link under the thumbnail image, if the user is not blacklisted and is the photo owner or administrator, and Uploader edit is enabled in Table VII-D2.

= 5.3.7 =

= Bug Fixes =

* When Cloudinary activated, photo views in lightbox were not reported. Fixed.
* Fixed page title in list sessions mainenance popup window.
* Lightbox on slide works also on a touchscreen.
* Improved session data handling. This will no longer excessively slow down the system.

= Other Changes =

* Having the capability Album Admin (wppa_admin) is sufficient to have edit access to an album, the role administrator is no longer required. This affects frontend edit album info.
* If frontend photo edit is enabled in Table VII-D2, there will not only be an edit link in the description area of the slideshow display, but also under the thumbnail display.

= 5.3.6 =

= Bug Fixes =

* Changing small image sizes stopped reporting that thumbnails should be regenerated when required. Fixed.

= Other Changes =

* Landing pages will now really only be created when the actually are going to be used and no longer when the link type does not ask for a linkpage.
* The re-creation of wpp-init.[lang].js files and wppa-dynamic.css is now only done when a setting is changed that affects the content of the files.

= 5.3.5 =

= Bug Fixes =

* Approving a pending photo at the frontend will now properly adjust the counts on the Album Admin page.

= New Features =

* You can edit album name and description at the frontend if it is enabled in Table VII-B1.1.
* You can disable the possibility to rate photos by the uploader himself by unchecking the box in Table IV-E3.1.
* You can force users to give a comment to validate their rating in Table IV-E3.2.

= Other Changes =

* If a line of text for the textual watermark is too long to fit on the photo, an attempt will be made to break it into two lines. 
There should be a space character in the right-hand half of the line. This also works on multiline watermark texts and texts that contain keywords.
* All listing actions are now in Table VIII-C.

= 5.3.4 =

= Other Changes =

* WPPA+ is now fullly compatible with WP 3.9. Use 5.3.3 for WP 3.8.x

= 5.3.3 = 

= Bug Fixes =

* Text under thumbnail popup displayed the photo name even when Table II-D4 was unchecked. Fixed.

= Other Changes =

* On entering the Settings admin page, an attempt will be made to fix damaged settings caused by improper functioning of delete_option().
The results can be seen in the logfile ( Table VIII-A12 ). You will still have to verify the content of edit fields, because most of them may have an empty content.

= 5.3.2 =

= Bug Fixes =

* The problem in pathnames on windows servers has been reviewed, tested and is really fixed now.
* You should no longer get a db erroressage referring an invalid name for an index.

= New Features =

* Added keyword w#id to the possible keywords in textual watermarks.
* You should now be able to upload photos from smartphone cameras directly.

= Other Changes =

* The upload directory structure for multisite now also supports new style multisite.
* The wp function wp_upload_dir(); is no longer used to retrieve the active path to the uploads dir. On many installations this function returns bogus data.
If you changed the names of wp-content and/or the up-loads dir, add the lines to wp-config.php as described above.
* Table VIII-A6 no longer cleans the database for missing photos, but is now named Lost and found, and will recover db entries for 'lost' photos.

= 5.3.1 =

= Bug Fixes =

* Windows backward filesystem slashes related problems in paths should be fixed now.
* The og description transferred to wppa.js is now properly escaped. Fixes broken slideshows.
* You should see no more [... is no yes/no setting] errors anymore.
* Fixed a layout issue on comments in slideshows.

= 5.3.0 =

= Known problems =

* The shortcode generator button is absent due to changes in wp 3.9

= Bug Fixes =

* You can now repeatedly do Remake Photofiles on an individual photo without the need to reload the page.
* Empty albums that contain empty albums only will now be seen as empty and will therefor be hidden when Table II-E6 is checked.
* Fixed lay-out issues for sharetext.

= New Features =

* Text based watermarks, with dynamic text and the ability to upload new truetype fonts. See Table IX-F.
* Watermark all photos. Table VIII-B9.
* Search results display type can now be set in Table IX-E12.

= Other Changes =

* The plugin is now believed to support the change of the wp-content directory as described here: http://codex.wordpress.org/Editing_wp-config.php#Moving_wp-content_folder
* Due to the unreliability of the PHP superglobal $_SESSION ( the values may be unintentedly modified by Firefox, see https://bugzilla.mozilla.org/show_bug.cgi?id=991019 ),
there is now a different implementation for the survival of session data between pageloads.
* Cloudinary derived images are now at jpeg quality set in Table IX-A2.
* Many internal modifications to speed up, simplify and improve maintainability of the plugin.

== About and Credits ==

* WP Photo Album Plus is extended with many new features and is maintained by J.N. Breetvelt, ( http://www.opajaap.nl/ ) a.k.a. OpaJaap
* Thanx to R.J. Kaplan for WP Photo Album 1.5.1, the basis of this plugin.

== Licence ==

WP Photo Album is released under the GNU GPL licence. ( http://www.gnu.org/copyleft/gpl.html )