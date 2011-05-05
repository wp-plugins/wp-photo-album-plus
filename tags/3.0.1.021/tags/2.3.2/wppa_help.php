<?php
/* wppa_help.php
* Pachkage: wp-photo-album-plus
*
* admin help page
* version 2.3.2
*/

function wppa_page_help() {

	// Check if a message is required
	wppa_check_update();

?>
	<div class="wrap">
<?php 
		$iconurl = "http://www.gravatar.com/avatar/b421f77aa39db35a5c1787240c77634f?s=32&amp;d=http%3A%2F%2Fwww.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D32&amp;r=G";
?>		
		<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
			<br />
		</div>
		<h2><?php _e('Help and Information', 'wppa'); ?></h2>
		
		<h3><?php _e('Plugin Description', 'wppa'); ?></h3>
        <p><?php _e('This plugin is designed to easily manage and display your photo albums within your WordPress site.', 'wppa'); ?></p>
			<?php _e('Features:', 'wppa'); ?><br /><br />
			<ul class="wppa-help-ul">
				<li><?php _e('You can create various albums that contain photos as well as sub albums at the same time.', 'wppa'); ?></li>
				<li><?php _e('There is no limitation to the number of albums and photos.', 'wppa'); ?></li>
				<li><?php _e('There is no limitation to the nesting depth of sub-albums.', 'wppa'); ?></li>
				<li><?php _e('You have full control over the display sizes of the photos.', 'wppa'); ?></li>
				<li><?php _e('You can specify the way the albums are ordered.', 'wppa'); ?></li>
				<li><?php _e('You can specify the way the photos are ordered within the albums, both on a system-wide as well as an per album basis.', 'wppa'); ?></li>
				<li><?php _e('The visitor of your site can run a slideshow from the photos in an album by a single mouseclick.', 'wppa'); ?></li>
				<li><?php _e('The visitor can see an overview of thumbnail images of the photos in album.', 'wppa'); ?></li>
				<li><?php _e('The visitor can browse through the photos in each album you decide to publish.', 'wppa'); ?></li>
				<li><?php _e('You can add a Sidebar Widget that displays a photo which can be changed every hour, day or week.', 'wppa'); ?></li>
			</ul>
		
		<h3><?php _e('Plugin Admin Features', 'wppa'); ?></h3>
		<p><?php _e('You can find the plugin admin section under Menu Photo Albums on the admin screen.', 'wppa'); ?></p>
			<?php _e('The following submenus exist.', 'wppa'); ?><br /><br />
			<ul class="wppa-help-ul">
				<li><?php _e('Photo Albums: Create and manage Albums.', 'wppa'); ?></li>
				<li><?php _e('Upload photos: To upload photos to an album you created.', 'wppa'); ?></li>
				<li><?php _e('Settings: To control the various settings to customize your needs.', 'wppa'); ?></li>
				<li><?php _e('Sidebar Widget: To specify the behaviour for an optional sidebar widget.', 'wppa'); ?></li>
				<li><?php _e('Help & Info: The screen you are watching now.', 'wppa'); ?></li>
			</ul>

		<h3><?php _e('Installation', 'wppa'); ?></h3>
        <ol class="wppa-help-ol">
			<li><?php _e('Unzip and upload the wppa plugin folder to', 'wppa'); ?> <tt>wp-content/plugins/</tt></li>
			<li><?php _e('Make sure that the folder', 'wppa'); ?> <tt>wp-content/uploads/</tt> <?php _e('exists and is writable by the server (CHMOD 755)', 'wppa'); ?></li>
			<li><?php _e('Activate the plugin in WP Admin -> Plugins.', 'wppa'); ?></li>
		</ol>

        <h3><?php _e('Upgrading from WP Photo Album', 'wppa'); ?></h3>
        <p><?php _e('When upgrading from WP Photo Album to WP Photo Album Plus be aware of:', 'wppa'); ?></p>
        <ol class="wppa-help-ol">
			<li><?php _e('First de-activate WP Photo Album before activating WP Photo Album Plus!!', 'wppa'); ?><br/>
				<?php _e('YOU CAN NOT RUN BOTH VERSIONS AT THE SAME TIME!!', 'wppa'); ?>
			</li>
			<li><?php _e('The existing database and albums and photos will be preserved.', 'wppa'); ?><br/>
				<?php _e('YOU DO NOT NEED TO RE-UPLOAD YOUR PHOTOS', 'wppa'); ?>
			</li>
			<li><?php _e('You will need to use (and probably modify) the newly supplied default theme file "wppa_theme.php".', 'wppa'); ?></li>
			<li><?php _e('You can use existing albums to make sub-albums, simply by specifying in which album they belong.', 'wppa'); ?></li>
        </ol>
            
		<h3><?php _e('How to start', 'wppa'); ?></h3>
        <ol class="wppa-help-ol">
			<li><?php _e('Install WP Photo ALbum Plus as described above under "Installation".', 'wppa'); ?></li>
            <li><?php _e('Create at least two albums in the "Photo Albums" tab. Just enter the name and a brief description and press "Create Album". Leave "Parent" at "--- none ---".', 'wppa'); ?></li>
			<li><?php _e('In the uploads tab, you can now upload you photots. Upload at least 2 photos to each album. Make sure the photos you are uploading are of reasonable size (say up to 1024x768 pixels). Do not upload the full 7MP images!', 'wppa'); ?></li>
			<li><?php _e('Create a new WP Page, name it something like "Photo Gallery" and put in the content:', 'wppa'); ?> <tt>%%wppa%%</tt></li>
			<li><?php _e('Publish the page, and view the page from your WP site.', 'wppa'); ?></li>
			<li><?php _e('Now, go playing with the settings in the "Settings" panel, discover all the configurable options and watch what is happening when you re-open the "Photo Gallery" page.', 'wppa'); ?></li>
			<li><?php _e('If you want a "Photo of the week" sidebar widget you can use an album for that purpose. See all the options in the "Sidebar Widget" submenu.', 'wppa'); ?></li>
        </ol>

		<h3><?php _e('Creating a Photo Album Page or a Post with photos - Advanced', 'wppa'); ?></h3>
		<p>
			<?php _e('Create a page like you normally would in WordPress, using the "Default Template". In my example, give it the page title of "Photo Gallery". In the Page Content section add the following code:', 'wppa'); ?><br />
			<tt>%%wppa%%</tt><br />
			<?php _e('This will result in a gallery of all Albums that have their parent set to "--- none ---".', 'wppa'); ?><br /><br />
			<?php _e('If you want to display a single album - say album number 19 - in a WP page or WP post (they act exactly the same), add a second line like this:', 'wppa'); ?><br />
			<tt>%%album=19%%</tt><br />
			<?php _e('This will result in the display of the', 'wppa'); ?><b> <?php _e('contents', 'wppa'); ?> </b><?php _e('of album nr 19.', 'wppa'); ?><br /><br />
			<?php _e('If you want to display the', 'wppa'); ?><b> <?php _e('"cover"', 'wppa'); ?> </b><?php _e('of the album, i.e. like one of the albums in the "Photo Gallery" as used above, add (instead of "%%album=...") a second line like this:', 'wppa'); ?><br />
			<tt>%%cover=19%%</tt><br /><br />
			<?php _e('Alternatively, you can create an extra album (say it has number 22) and set the "parent" property of album 19 to this new album. Then your second line should read:', 'wppa'); ?><br />
			<tt>%%album=22%%</tt><br />
			<?php _e('This method enables you to add more than one album to a specific page or post as long as they have the same parent.', 'wppa'); ?><br /><br />
			<?php _e('Additionally, if you set the parent of this album (nr 22 in this example) to "--- separate ---", it will not be listed in the "generic" photo gallery and the breadcrumb will display the best.', 'wppa'); ?><br /><br />
			<?php _e('You can add a third line if you want the photos to be displayed at a different size than normal. You can "overrule" the "Full size" setting by adding the line (for e.g. 300px):', 'wppa'); ?><br />
			<tt>%%size=300%%</tt><br /><br />
			<?php _e('Note: all information between the %% tags including newlines will be lost.', 'wppa'); ?><br />
			<?php _e('The sequence above may be used more than once in a single page or post.', 'wppa'); ?><br />
			<?php _e('The text before the first sequence, the text between 2 sequences, as well as the text after the last sequence will be preserved.', 'wppa'); ?><br />
			<br/ ><br />
			<?php _e('You can also create a custom page template by dropping the following code into a page template:', 'wppa'); ?><br />
			<tt>&lt;?php wppa_albums(); ?&gt;</tt><br /><br />
			<?php _e('If you want to display the <b>contents</b> of a single album in the template - say album number 19 - the code would be:', 'wppa'); ?><br />
			<tt>&lt;?php wppa_albums(19); ?&gt;</tt><br />
			<?php _e('If you want the <b>cover</b> to be displayed instead, add the following code:', 'wppa'); ?><br />
			<tt>&lt;?php global $is_cover; ?&gt;</tt><br />
			<tt>&lt;?php $is_cover = '1'; ?&gt;</tt><br /><br />
			<?php _e('If you want to specify a size, add the following code:', 'wppa'); ?><br />
			<tt>&lt;?php global $wppa_fullsize; ?&gt;<br/>
			&lt;?php $wppa_fullsize = 300; ?&gt;</tt><br/><br />
			<?php _e('You can combine the above as follows: (example)', 'wppa'); ?><br />
			<tt>&lt;?php wppa_albums(19, 'album', 300); ?&gt;</tt><br />
			<?php _e('or as:', 'wppa'); ?><br />
			<tt>&lt;?php wppa_albums(19, 'cover', 300); ?&gt;</tt><br /><br />
			<?php _e('In order to work properly, the wppa_albums() tag needs to be within the', 'wppa'); ?> <a href="http://codex.wordpress.org/The_Loop">WordPress loop</a>.<br/>
			<?php _e('For more information on creating custom page templates, click', 'wppa'); ?> <a href="http://codex.wordpress.org/Pages#Creating_your_own_Page_Templates"><?php _e('here', 'wppa'); ?></a>.<br/>
		</p>
		
		<h3><?php _e('Adjusting CSS and Template Styling', 'wppa'); ?></h3>
		<p>
			<?php _e('WP Photo Album Plus comes with a default layout and theme.', 'wppa'); ?>
			<?php _e('To change the style and layout of the photo album, copy <tt>.../wp-content/plugins/wp-photo-album-plus/theme/wppa_theme.php</tt> and <tt>.../wp-content/plugins/wp-photo-album-plus/theme/wppa_style.css</tt> to your active theme\'s folder, and edit them.', 'wppa'); ?>
		</p>
		
		<h3><?php _e('Facts to remember', 'wppa'); ?></h3>
		<ul class="wppa-help-ul">
			<li><?php _e('An album can have only <b>ONE</b> parent.', 'wppa'); ?></li>
			<li><?php _e('If the number of photos in an album is less than or equal to the treshold value, they will not display in the album. They will be used for the cover only.', 'wppa'); ?></li>
			<li><?php _e('An album that has it\'s parent set to "--- separate ---" will not be displayed in the "generic" gallery. This enables you to have albums for use solely for single posts or pages.', 'wppa'); ?>
			<li><?php _e('Specifying <tt>%%album=...</tt> causes the <b>content</b> of the album to be displayed.', 'wppa'); ?></li>
			<li><?php _e('Specifying <tt>%%cover=...</tt> causes the <b>cover</b> of the album to be displayed.', 'wppa'); ?></li>
			<li><?php _e('Keep the sequence intact: 1. <tt>%%wppa%%</tt>, 2. <tt>%%album=</tt> or <tt>%%cover=</tt>, 3. <tt>%%size=</tt>. (2. being optional even when using 3.).', 'wppa'); ?></li>
			<li><?php _e('Use the default page template, or create one yourself. In this case, study the example (actually the version i use myself): <tt>...wp-content/plugins/wp-photo-album-plus/examples/page-photo-album.php</tt>', 'wppa'); ?></li>
			<li><?php _e('WPPA uses a system of tags similar to the WordPress theme system. To view a list of available tags, please read tags.txt', 'wppa'); ?></li>
			<li><?php _e('You can remove the plugin and re-install the latest version always. This will not affect your photos or albums.', 'wppa'); ?></li>
			</ul>
	
		<h3><?php _e('Plugin Support And Feature Request', 'wppa'); ?></h3>
		<p>
			<?php _e('If you\'ve read over this readme carefully and are still having issues, if you\'ve discovered a bug,', 'wppa'); ?>
			<?php _e('or have a feature request, please contact me via my', 'wppa'); ?> <a href="mailto:opajaap@opajaap.nl?subject=WP%20Photo%20Album%20Plus">E-mail</a>.
			<br/>
			<?php _e('You may also check the', 'wppa'); ?> <a href="http://wordpress.org/tags/wp-photo-album-plus">forum</a> <?php _e('for this plugin and/or leave a question there.', 'wppa'); ?>
			<br/>
			<?php _e('For hot fixes check the', 'wppa'); ?> <a href="http://plugins.trac.wordpress.org/log/wp-photo-album-plus/">development log</a> <?php _e('for this plugin.', 'wppa'); ?>
		</p>
        <p>
			<?php _e('If you love this plugin, I would appreciate a donation, either in', 'wppa'); ?>
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=OpaJaap@OpaJaap.nl&item_name=WP-Photo-Album-Plus&item_number=Support-Open-Source&currency_code=USD&lc=US">USD</a>&nbsp;
				<?php _e('or in', 'wppa'); ?>&nbsp;
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=OpaJaap@OpaJaap.nl&item_name=WP-Photo-Album-Plus&item_number=Support-Open-Source&currency_code=EUR&lc=US">EURO.</a>
		</p>

		<h3><?php _e('About and credits', 'wppa'); ?></h3>
		<p>
			<?php _e('WP Photo Album Plus is extended with many new features and is maintained by J.N. Breetvelt, a.k.a.', 'wppa'); ?>
			<a href="http://www.opajaap.nl/"> (OpaJaap)</a><br />
			<?php _e('Thanx to R.J. Kaplan for WP Photo Album 1.5.1.', 'wppa'); ?><br/>
			<?php _e('Thanx to E.S. Rosenberg for programming tips on security issues.', 'wppa'); ?><br/>
		</p>
		
		<h3><?php _e('Licence', 'wppa'); ?></h3>
		<p>
			<?php _e('WP Photo Album is released under the', 'wppa'); ?> <a href="http://www.gnu.org/copyleft/gpl.html">GNU GPL</a> <?php _e('licence.', 'wppa'); ?>
		</p>
		
	</div>
<?php
}
?>