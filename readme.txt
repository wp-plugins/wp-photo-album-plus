=== WP Photo Album Plus ===
Contributors: opajaap
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=OpaJaap@OpaJaap.nl&item_name=WP-Photo-Album-Plus&item_number=Support-Open-Source&currency_code=USD&lc=US
Tags: photo, album, photoalbum, gallery, slideshow, video, sidebar widget, photowidget, photoblog, widget, qtranslate, cubepoints, myCRED, multisite, network, lightbox, comment, watermark, iptc, exif, responsive, mobile, cloudinary, fotomoto, CMTooltipGlossary
Version: 6.1.15
Stable tag: trunk
Author: J.N. Breetvelt
Author URI: http://www.opajaap.nl/
Requires at least: 3.9
Tested up to: 4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin is designed to easily manage and display your photos, photo albums, slideshows and videos in a single as well as in a network WP site.

== Description ==

This plugin is designed to easily manage and display your photo albums and slideshows within your WordPress site. 

* You can create various albums that contain photos as well as sub albums at the same time.
* You can mix photos and videos throughout the system.
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

= 6.1.15 =

= Bug Fixes =

* Fixed a bug in a security check on front-end album deletion.
* Fixed high resolution image urls for videos / audios on slideshows.

= New Features =

* WPPA+ Text widget has now an extra checkbox, to set if you want the widget to be seen by logged in users only.
* Table II-D18: Show empty thumbnail area. Check this to see the thumbnail area of empty albums for the upload link in it.
* There are now close links in the front-end upload and album edit / create dialogs.

= Other Changes =

* Improved compatibility with lightbox plugin prettyPhoto. Slideshows work on it ( but still no videos or audios ).
* There are no loger empty titles or titles with only a space on images.
* If the WPPA+ embedded lightbox is used, the subtitles are now transferred to lightbox by data-lbtitle="..", 
to prevent hughe tooltip boxes while hovering over the image that links to lightbox while Table II-G1 is unticked.
If you use a non-default lightbox, make sure the liughtbox titles are empty, or Table II-G91 is ticked.

= 6.1.14 =

= Bug Fixes =

* On servers where the function readdir() not properly workes, the import page never showed up. Fixed.
* Many minor fixes for w3c validation.

= Other Changes =

* For browsers that display empty tooltip boxes: there are no longer empty title attributes generated.
* wppa lightbox now uses data-rel="wppa" to meet w3c standards.
* Table II-G19: Overlay show legenda. Regardless of this setting, it will not show up on mobile devices, 
but the keyboard handler will be installed to facillitate tablet/laptop converable devices.
* There was a serious performance problem with the new smilies: emoji. 
Especially on firefox and using ajax On one of my testsites a slideshow with 15 slides and comments enabled and the smiley picker displayed 
used to take 4 seconds to load. Now it takes up to a minute; the browser even does not respond for over 50 seconds.
As a work around for this, i coded my own convert_smilies() function: wppa_convert_smilies(), located in wppa_utils.php, 
just for the creation of the html for the smileypicker and the smilies in the comments on photos. 
It still uses the emoji images, but by direct coding and not through a character code.

= 6.1.13 =

* Intermediate test version, not released.

= 6.1.12 =

= Bug Fixes =

* Rotating an image will always produce a rotated thumbnail created out of the display file, regardless of setting Table IX-F12.
If you have rotated images and you want to remake all thumbnails and you have source files saved, tick Table IX-F12 to make sure all thumbnails will have the right orientation.
* Thumbnail type *masonry style rows* is now usable on static themes. There still is a problem with Thumbnail type ( Table IV-C3 ) *masonry style rows.* 
On static themes: untick Table IV-C6: *Thumb mouseover* to fix the behaviour in Internet Explorer.
On responsive themes, in Internet Explorer and Google Chrome show odd layouts. Do not use *masonry style rows* on responive themes until this issue is fixed.
*masonry style columns* works as expected in all browsers, both in responsive and static themes.
* Layout fix on album cover if album full.

= New Features =

* Topten Widget can have owner and album displayed in the subtitle, album will be a link to the photos album.
* You can now also use keywords for exif and iptc labels in photo descriptions. Use *2#L080* for *Photographer:*, *E#L9003* For *Date time original* etc, where *2#080* and *E#9003* return the photo specific data..
* New settings for lightbox: Table II-G18 and 19 to hide Start/Stop and Fullscreen legenda.

= 6.1.11 =

= Bug Fixes =

* Fixed an error in statistics for logged out visitors.

= New Features =

* Bulk import custom data. see http://wppa.opajaap.nl/using-custom-data-fields/

= 6.1.10 =

= Bug Fixes =

* Supersearch now also works in I.E.
* Fixed breadcrumb for supersearch displays.

= New Features =

* New photo status: private. Private photos are only shown to logged in visitors. This will only work in normal pageviews. A full url to an image file will not be rejected.

= Other Changes =

* Performance improvements in supersearch.
* Added Table IX-13 and E-14 to reduce select box options.
* Exif and Iptc systems now clean up garbage automaticly.
* Removed 'hover to select' and 'click to add/remove' from supersearch selectboxes because i.e. does not support event handlers on option tags.

= 6.1.9 =

= Bug Fixes =

* Fixed a layout issue of Com alt displays on responsive themes.

= New Features =

* The default album cover linktype that will be set at album creation is now settable in Table IX-D18.
* New shortcode: type="supersearch". Related settings: Table VI-C9, Table IX-E13.

= Other Changes =

* Split js files in logical units, prevent loading of not used code.

= 6.1.8 =

= Bug Fixes =

* Custom data is now properly indexed ( 6.1.7.001 )
* Skip empty albums now correctly tests on user role administrator as opposed to capability wppa_admin.
* Thumbnail popup did not work properly on chrome browser on certain themes. Fixed.
* Fixed potential problems with setting options that have leading or trailing spaces.

= 6.1.7 =

= New Features =

* Custom datafields are now imputtable at the front-end upload dialog box. Table II-H10. Tags switch is now Table II-H11.

= Other Changes =

* Cosmetic changes in page link bar.
* Set input field width in seach box to 60%, added class wppa-search-input to input field. This fixes a lay-out issue on theme twentyfifteen.

= 6.1.6 =

= Bug Fixes =

* If no album selected in frontend upload widget/box an alertbox will be displayed.
* Fotomoto 'hide when running' now works.

= New Features =

* Up to 10 custom datafields for photos can be defined. See Table II-J10(.x).
See http://wppa.opajaap.nl/using-custom-data-fields/ for an explanation.

= Other Changes =

* wppa.js is now split into 4 files.
* All front-end ajax actions are now asynchronous using jQuery.ajax().

= 6.1.5 =

= Bug Fixes =

* Selected albums did not show up in album selection lists. Fixed.
* Alt thumbsizes stopped working. Fixed.

= 6.1.4 =

= Bug Fixes =

* Fixed a regression vs 6.1.2: The upload link on an album should only show up if the user hass album access to the album.

= Other Changes =

* Setting Table VII-D1 ( Owner only ) has been split into VII-D1.1 referring to album admin access and VII-D1.2 refering to album upload access.

= 6.1.3 =

= Bug Fixes =

* The message: 'Comment added' stopped to be displayed even if Table IV-F6 was ticked. Fixed.
* Smilies in photo descriptions are now displayed.
* Improved randomness of random selected photos in multiple albums.
* Fixed consistency in random sequence between first and successive pages in paginated displays.
* Improved algorithm to decide when to display the front-end upload and creat album link.
* Fixed an inconsistency in rights on the album admin table.
* Security fix.

= New Features =

* The ability to limit the number of albums for a user based on user role. Table VII-B5a.x

= 6.1.2 =

= Bug Fixes =

* The new wp (4.2) implementation of smileys broke the smileypicker. Fixed.
* The BestOf widget could not handle video's. Fixed.

= 6.1.1 =

= Bug Fixes =

* Poster image files could no be import-updated. Fixed.
* The photo of the day widget could not handle videos. Fixed.

= New Features =

* Audio support. Supported filetypes: .mp3, .wav and .ogg.
* Added filetype .jpeg for photos.

= Other Changes =

* Table II-D ( Visibility: Thumbnails ) has been renumbered.
* When a video plays, a running slideshow will be suspended until the video finishes. Same for videos on running lightbox slideshows.
* Fixed a lay-out issue on horizontal masonry thumbnails.
* The thumbnail subtext is now displayed as title ( hover text ) on masonry style thumbnails.

= 6.0.0 =

= New Features =

* The support of videos. You can mix photos and videos throughout the system including lightbox.
See the <a href="http://wppa.opajaap.nl/video-support/">documentation.</a>

= Other Changes =

* Added link types to various virtual album widgets.

= 5.5.7 =

= Other Changes =

* Layout change in default album covers. All links are now below the body text when the photo is at the left hand side.
* In case of deadlock situations in maintenance procs, you can now now ignore the concurrency error in Table VIII-A0.

= 5.5.6 =

= Bug Fixes =

* Tags system is updated when a photo status changes ( e.g. from pending to publish ).
* Fixed a bug in lightbox on cover images.
* The import dir to album stopped at the first duplicate found. Fixed.
 
= 5.5.5 =

= Bug Fixes =

* Tags selected and input at front-end upload now always update the tags system properly so the tagged photos will be found immediately by the tag widgets and #tags shortcodes.

= Other Changes =

* The only way to exit fullscreen lightbox is the escape key. It depends on the browser type if the escape key must be hit twice.
* Many invisible internal changes to improve reliability, error handling and reporting and protection against programming errors that reduces the probability of the creation of new bugs.

= 5.5.4 =

= Bug Fixes =

* Fixed a potantial recursive error log resulting in a blank page when pretty links activated.

= New Features =

* Table IX-D2.1: Limit LasTen New. Limits the LasTen found images to those that are 'New'. Both in widget and in shortcode using album="#lasten".

= Other Changes =

* Icreased the choices in Table IX-D1 and 2.

= 5.5.3 =

= Bug Fixes =

* Fixed a potantial spurious lay-out issue on masonry rows thumbnail style.

= New Features =

* Max 3 tag selection box on frontend upload. The frontend upload dialog is now fully configurable. See Table II-H.
* A new shortcode to temporary change a setting on the page. Use shortcode [wppa_set name="a_legal_setting_slug" value="the_new_value"][/wppa_set].
See Table IX-A7 and 8.

= 5.5.2 =

= Bug Fixes =

* Fixed undefined vars in settings page 5.5.1.001.
* Fixed a bug in wppa_get_hires_url() 5.5.1.002.
* Fixed a missing /a on filmthumbnail links with pso.
* IPTC and EXIF keywords in photo descriptions are now translated and added to the photo index. You may need to rebuild the photo index in Table VIII-A9.

= New Features =

* Emails sent by the commenting system now contain a Reply link to the original page/photo where the comment was issued.
* Two versions of Masonry style thumbnail layout added. See Table IV-C3.

= Other Changes =

* In the shortcode generator: If Table IX-B4: 'Album sel hierarchic' is ticked, the album sequence in the selection box is alphabetic to the names of the albums.
If the box is UNticked, the album sequence is by descemding timestamp ( i.e. latest created/modified/uploaded album at the top ).
* Photo Download link will now also work on mobile devices.
* If Load Facebook sdk is selected in Table II-C17.9, it will only be loaded IF at least one of the settings Table II-C1,3,4,5,6 is ticked AND at least one of the settings Table II-C17.1, 17.2, 17.4 is ticked.

= 5.5.1 =

= Bug Fixes =

* The subtitle of the first lightbox picture did not show up. Fixed.
* Fixed an error: undefined var in settings.

= New Features =

* PSO and New Tab checkboxes for Film thumb images links. Table VI-B6.

= Other Changes =

* Improved error reporting on maintenance routines.

= 5.5.0 =

= Bug Fixes =

* Lightbox on thumbnails ( VI-B2 ) now also works on thumbnail type 'like album covers' ( IV-C3 ).
* Default parent always ( IX-D7.2 ) is now a checkbox as it should be, and indicates that frontend created albums should have the album in IX-D7.1 as parent.
* Lightbox on slideshows. If the photo is smaller than the frame, the hoover text ( title ) no longer shows the lightbox subtext html. This only works for the embedded lightbox.
* Default tags seemed not to work at front-end upload. Fixed.
* The shortcodegenerator asked for parent albums for several virtual albums where this is not implemented. It now asks for an optional multiple selection of albums to be used.
* The ubb's now also work on running slideshows.
* Swipe now also works on running slideshows.

= New Features =

* Quick select settings. All settings have one or more tags. Select up to 2 tags to open all settings that have at least those tags. See the Photo ALbums -> Settings admin page.
* Delete album at frontend. Table VII-B1.2
* Added Table IX-D15.1: Copy owner. Copies the owner on copy photo.
* Foreign shortcodes in album desc, album name and photo name are now possible if Table IX-J0 is checked.
* Table IV-A20. Thumbs first. Display the thumbnails before the sub-albums.
* Table IV-A21. Login links. 'You must login to...' links to wp login page.

= Other Changes =

* The frontend upload dialog now has a tags preview line. The user can see exactly what tags will be added to the photo after upload.
* Classic search has been discontinued. Search operations use the index db table. After update to this version you may be asked to rebuild the index tables.
* Changed a lot of default setting values. This only affects new installs.
* Improved layout of FB Comments box in responsive themes ( I-A1 = auto ).

= 5.4.25 =

= Bug Fixes =

* Users with wppa admin rights and no moderate rights received security error #78 on changing photo status. Fixed ( 5.4.24.001 ).
* Fixed a typo in link title from slide to thumbnail.

= New Features =

* Table VII-C6: Extended status is restricted. If Checked, setting photo status other than Publish or Pending requires administrator rights. 
If the user has moderate rights he can only set status to Publish or Pending.
* New album title link types: 'the sub-albums' and 'the thumbnails'. Selectable on the edit album information admin screen.
Also works on the cover photo if Table VI-B1 is set to 'same as title'.
The View link is not affected and will still link to the 'content' i.e. sub-albums and thumbnails.
* Table VI-C8: Album navigator widget link: you can now select thumbnails or slideshow.

= Other Changes =

* Added Brazilian Portugese front-end language files.
* Table IV-B15. You can now switch the use of Ajax for slideshow pagelinks independantly.
* When linking back from slide to thumbnail view, you will land on the correct thumbnail page and the actual thumbnail has a blue border to indicate the link source. This does not apply to the 'back to thumbnails' icon in the breadcrumb.

= 5.4.24 =

= Bug Fixes =

* The existance of a photo was not always properly detected when avoid duplicates was selected in Table VII-D7. Fixed.

= New Features =

* The length of a photo file name can be limited in Table I-A10.1.
* The length of a photo name can be limited in Table I-A10.2.

= Other Changes =

* Top search for edit photos at the backend, it is no longer required to be administrator. 
Album admin access combined with moderate access is sufficient to search all photos ( Table VII-A ).
Album admin access combined with Uploader edit ( Table VII-D2.1 ) enables the search for own photos only.

= 5.4.23 =

= Bug Fixes =

* Fixed a display bug of comalt thumbnails on responsive themes with column width of less than or equal to 100%.

= New Features =

* Added linktype 'the thumbnails' to slideshow linktypes in Table VI-B5.

= Other Changes =

* When Fotomoto is enabled, the source files are used for display if available, not the standard files. 
This enables the use of print resolution files in the cloud in combination with the Auto Pickup feature of Fotomoto.

= 5.4.22 =

= Bug Fixes =

* Fixed a problem of non visible slideshows on some installations ( 5.4.21.002 ).
* Fixed a problem in rating when Ajax NON admin was not checked.
* Fixed a link syntax error in frontend upload.
* Fixed a slideshow layout problem on some themes with some browsers.
* Fixed a popup image positioning issue.
* Add gpx tags maintenance proc now also updates search index db table.
* The Back to album table link at the top of the Manage (searched) photos now also works; not the bottom one only.
* The search photos album entry in the album table now also shows up on the collapsable table when there are no ---separate--- albums.
* Fixed a problem with responsive single photos ( type="photo" and type="mphoto" ).
* Source saving now works as expected according to settings Table IX-H1 and 2.

= 5.4.21 =

= Bug Fixes =

* The links for the plugin CM Tooltip Glossary now also show the tooltips in dynamic content like photo descriptions in the slideshows.
* Fixed a lay-out issue in descriptions caused by CM Tooltip Glossary.
* The pagination of search results now also works when short query args is ticked in Table IV-A5. ( fixed 5.4.20.001 )
* Fixed a conflict with bbPress.
* Search index was not always properly updated when the photo tags were changed. Fixed.

= New Features =

* On the Album Admin page, a virtual album has been added with a search edit box. 
Enter (a) search token(s) and edit the photo(s) that match the search criteria. Search tokens must be seperated by commas: ','.
This works for administrators always, non-admins need the switch in Table VII-D2: Uploader edit, and can edit their own photos only.
This feature requires indexed search, if not activated already, Tick Table IX-E7 and run Table VIII-A8 and A9.
* Table II-H5: Owner on new line. Places the (owner) addition to the photo name on a new line.
* Setting in Table VII-D2.2: Uploader Moderate Comment. If checked the uploader can moderate the comments on his photos. Requires Tavle VII-D2.1: Uploader Edit.
* Table IX-D7.2: Default parent always. For non-administrators: creating an album at the frontend will always set the parent to the default parent.
* Implemented compatibility with plugin myCred. See Table IX-J4,5,6.

= Other Changes =

* Links in the breadcrumb are now also converted to pretty links if pretty links are enabled.

= 5.4.20 =

= Bug Fixes =

* Fixed a divide by zero error on the bulk edit screen when a thumbnail was missing.
* The comten widget and shortcode no longer show duplicate photos, the recently commented photos will show all their comments in descending timestamp order.
* Using cloudinary on ssl pages now correctly produces https image links.
* Double clicking on the filmstrip incidently did hang up the slideshow. Fixed.

= New Features =

* Support for plugin CM Tooltip Glossary. 
Enable to act on album and photo descriptions in Table IX-J8. 
If you have this plugin active, you have to tick the box in Table IV-A13: Defer javascript, 
even if you do not want to let this plugin act on album and photo descriptions!

= Other Changes =

* Improved algorithm to decide when the random photo order has to be remebered onto the naxt page and when not.

= 5.4.19 =

= Bug Fixes =

* Photo tags may contain spaces, Now also in the application/addition of default tags in album admin.
* On certain installations and frontend upload using ajax, a fatal error undefined function wppa_user_upload() occurred. Fixed.

= New Features =

* Maintenance proc Table VIII-B13: Edit tag.
* Frontend upload can add tags if enabled in Table VII-B3.3.

= Other Changes =

* More info about files on the edit photo information screen.
* Lifted an internal limitation so albums can now contain over 20.000 photos.
* Filmstrip double arrows go to first or last slide instead of filmstrip length back and forth.

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