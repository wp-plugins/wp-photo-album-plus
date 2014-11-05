<?php 
/* wppa-upload.php
* Package: wp-photo-album-plus
*
* Contains all the upload/import pages and functions
* Version 5.4.18
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

// upload images admin page
function _wppa_page_upload() {
global $target;
global $wppa_opt;
global $wppa_revno;

    // sanitize system
	$user = wppa_get_user();
	wppa_sanitize_files();

	// Update watermark settings for the user ifnew values supplied
	if ( wppa_switch( 'wppa_watermark_on' ) && ( wppa_switch( 'wppa_watermark_user' ) || current_user_can( 'wppa_settings' ) ) ) {
		if ( isset( $_POST['wppa-watermark-file'] ) ) update_option( 'wppa_watermark_file_'.$user, $_POST['wppa-watermark-file'] );
		if ( isset( $_POST['wppa-watermark-pos'] ) ) update_option( 'wppa_watermark_pos_'.$user, $_POST['wppa-watermark-pos'] );
	}
	
	// If from album admin set the last album
	if ( isset( $_REQUEST['wppa-set-album'] ) ) wppa_set_last_album( $_REQUEST['wppa-set-album'] );
	
	// Do the upload if requested
	// From BOX A
	if ( isset( $_POST['wppa-upload-multiple'] ) ) {
		check_admin_referer( '$wppa_nonce', WPPA_NONCE );
		wppa_upload_multiple();
		if ( isset( $_POST['wppa-go-edit-multiple'] ) ) {
			if ( current_user_can( 'wppa_admin' ) ) {
				wppa_ok_message( __( 'Connecting to edit album...', 'wppa' ) ); ?>
				<script type="text/javascript">document.location = '<?php echo( wppa_dbg_url( get_admin_url().'admin.php?page=wppa_admin_menu&tab=edit&edit_id='.$_POST['wppa-album'], 'js' ) ) ?>';</script>
			<?php }
			else {
				wppa_ok_message( __( 'Connecting to edit photos...', 'wppa' ) ); ?>
				<script type="text/javascript">document.location = '<?php echo( wppa_dbg_url( get_admin_url().'admin.php?page=wppa_edit_photo', 'js' ) ) ?>';</script>
			<?php }
		}
	}
	// From BOX B
	if ( isset( $_POST['wppa-upload'] ) ) {
		check_admin_referer( '$wppa_nonce', WPPA_NONCE );
		wppa_upload_photos();
		if ( isset( $_POST['wppa-go-edit-single'] ) ) {
			if ( current_user_can( 'wppa_admin' ) ) {
				wppa_ok_message( __( 'Connecting to edit album...', 'wppa' ) ); ?>
				<script type="text/javascript">document.location = '<?php echo( wppa_dbg_url( get_admin_url().'admin.php?page=wppa_admin_menu&tab=edit&edit_id='.$_POST['wppa-album'], 'js' ) ) ?>';</script>
			<?php }
			else {
				wppa_ok_message( __( 'Connecting to edit photos...', 'wppa' ) ); ?>
				<script type="text/javascript">document.location = '<?php echo( wppa_dbg_url( get_admin_url().'admin.php?page=wppa_edit_photo', 'js' ) ) ?>';</script>
			<?php }
		}
	} 
	// From BOX C
	if ( isset( $_POST['wppa-upload-zip'] ) ) {
		check_admin_referer( '$wppa_nonce', WPPA_NONCE );
		$err = wppa_upload_zip();
		if ( isset( $_POST['wppa-go-import'] ) && $err == '0' ) { 
			wppa_ok_message( __( 'Connecting to your depot...', 'wppa' ) );
			update_option( 'wppa_import_source_'.$user, WPPA_DEPOT ); ?>
			<script type="text/javascript">document.location = '<?php echo( wppa_dbg_url( get_admin_url().'admin.php?page=wppa_import_photos&zip='.$target, 'js' ) ) ?>';</script>
		<?php }
	} 
	
	// sanitize system again
	wppa_sanitize_files();
	
	?>
	
	<div class="wrap">
		<?php $iconurl = WPPA_URL.'/images/camera32.png'; ?>
		<div id="icon-camera" class="icon32" style="background: transparent url( <?php echo( $iconurl ); ?> ) no-repeat">
		</div>
		<?php $iconurl = WPPA_URL.'/images/arrow32.png'; ?>
		<div id="icon-arrow" class="icon32" style="background: transparent url( <?php echo( $iconurl ); ?> ) no-repeat">
		</div>
		<?php $iconurl = WPPA_URL.'/images/album32.png'; ?>
		<div id="icon-album" class="icon32" style="background: transparent url( <?php echo( $iconurl ); ?> ) no-repeat">
		<br />
		</div>
		<h2><?php _e( 'Upload Photos', 'wppa' ); ?></h2>

		<?php	
		// Check for trivial requirements
		if ( ! function_exists( 'imagecreatefromjpeg' ) ) {
			wppa_error_message( __( 'There is a serious misconfiguration in your servers PHP config. Function imagecreatefromjpeg() does not exist. You will encounter problems when uploading photos and not be able to generate thumbnail images. Ask your hosting provider to add GD support with a minimal version 1.8.', 'wppa' ) );
		}

		$max_files = ini_get( 'max_file_uploads' );
		$max_files_txt = $max_files;
		if ( $max_files < '1' ) {
			$max_files_txt = __( 'unknown', 'wppa' );
			$max_files = '15';
		}
		$max_size = ini_get( 'upload_max_filesize' );
		$max_time = ini_get( 'max_input_time' );	
		if ( $max_time < '1' ) $max_time = __( 'unknown', 'wppa' );
		
		// chek if albums exist before allowing upload
		if ( wppa_has_albums() ) {
			if ( wppa_switch( 'wppa_upload_one_only' ) && ! current_user_can( 'administrator' ) ) {
				/* One only */ ?>
				<div style="border:1px solid #ccc; padding:10px; margin-bottom:10px; width: 600px;">
					<h3 style="margin-top:0px;"><?php _e( 'Upload a single photo', 'wppa' ); ?></h3>
					<form enctype="multipart/form-data" action="<?php echo( wppa_dbg_url( get_admin_url().'admin.php?page=wppa_upload_photos' ) ) ?>" method="post">
					<?php wp_nonce_field( '$wppa_nonce', WPPA_NONCE ); ?>
						<input id="my_files" type="file" name="my_files[]" />
						<p>
							<label for="wppa-album"><?php _e( 'Album:', 'wppa' ); ?> </label>
							<select name="wppa-album" id="wppa-album-s">
								<?php echo wppa_album_select_a( array( 'path' => wppa_switch( 'wppa_hier_albsel' ),'addpleaseselect' => true, 'checkaccess' => true, 'checkupload' => true ) ) ?>
							</select>
						</p>
						<?php if ( wppa_switch( 'wppa_watermark_on' ) && ( wppa_switch( 'wppa_watermark_user' ) || current_user_can( 'wppa_settings' ) ) ) { ?>		
							<p>		
								<?php _e( 'Apply watermark file:', 'wppa' ) ?>
								<select name="wppa-watermark-file" id="wppa-watermark-file">
									<?php echo( wppa_watermark_file_select() ) ?>
								</select>

								<?php _e( 'Position:', 'wppa' ) ?>
								<select name="wppa-watermark-pos" id="wppa-watermark-pos">
									<?php echo( wppa_watermark_pos_select() ) ?>
								</select>
							</p>
						<?php } ?>
						<input type="submit" class="button-primary" name="wppa-upload-multiple" value="<?php _e( 'Upload Photo', 'wppa' ) ?>" onclick="if ( document.getElementById( 'wppa-album-s' ).value == 0 ) { alert( '<?php _e( 'Please select an album', 'wppa' ) ?>' ); return false; }" />
						<input type="checkbox" id="wppa-go-edit-multiple" name="wppa-go-edit-multiple" style="display:none" checked="checked" />&nbsp;
					</form>
				</div>
<?php		}
			else { ?>
				<div style="border:1px solid #ccc; padding:10px; margin-bottom:10px; width: 600px; background-color:#fffbcc; border-color:#e6db55;">
				<?php echo( sprintf( __( '<b>Notice:</b> your server allows you to upload <b>%s</b> files of maximum total <b>%s</b> bytes and allows <b>%s</b> seconds to complete.', 'wppa' ), $max_files_txt, $max_size, $max_time ) ) ?>
				<?php _e( 'If your request exceeds these limitations, it will fail, probably without an errormessage.', 'wppa' ) ?>
				<?php _e( 'Additionally your hosting provider may have set other limitations on uploading files.', 'wppa' ) ?>
				<?php echo '<br />'.wppa_check_memory_limit() ?>
				</div>
				<?php /* Multple photos */ ?>
				<div style="border:1px solid #ccc; padding:10px; margin-bottom:10px; width: 600px;">
					<h3 style="margin-top:0px;"><?php _e( 'Box A:', 'wppa' ); echo ' ';_e( 'Multiple Photos in one selection', 'wppa' ); ?></h3>
					<?php echo sprintf( __( 'You can select up to %s photos in one selection and upload them.', 'wppa' ), $max_files_txt ); ?>
					<br /><small style="color:blue" ><?php _e( 'You need a modern browser that supports HTML-5 to select multiple files', 'wppa' ) ?></small>
					<form enctype="multipart/form-data" action="<?php echo( wppa_dbg_url( get_admin_url().'admin.php?page=wppa_upload_photos' ) ) ?>" method="post">
					<?php wp_nonce_field( '$wppa_nonce', WPPA_NONCE ); ?>
						<input id="my_files" type="file" multiple="multiple" name="my_files[]" onchange="showit()" />
						<div id="files_list2">
							<h3><?php _e( 'Selected Files:', 'wppa' ); ?></h3>
							
						</div>
						<script type="text/javascript">
							function showit() {
								var maxsize = parseInt( '<?php echo $max_size ?>' ) * 1024 * 1024;
								var maxcount = parseInt( '<?php echo $max_files_txt ?>' );
								var totsize = 0;
								var files = document.getElementById( 'my_files' ).files;
								var tekst = '<h3><?php _e( 'Selected Files:', 'wppa' ) ?></h3>';
								tekst += '<table><thead><tr>';
										tekst += '<td><?php _e( 'Name', 'wppa' ) ?></td><td><?php _e( 'Size', 'wppa' ) ?></td><td><?php _e( 'Type', 'wppa' ) ?></td>';
									tekst += '</tr></thead>';
									tekst += '<tbody>';
										tekst += '<tr><td><hr /></td><td><hr /></td><td><hr /></td></tr>';
										for ( var i=0;i<files.length;i++ ) {
											tekst += '<tr>';
												tekst += '<td>' + files[i].name + '</td>';
												tekst += '<td>' + files[i].size + '</td>';
												totsize += files[i].size;
												tekst += '<td>' + files[i].type + '</td>';
											tekst += '</tr>';
										}
										tekst += '<tr><td><hr /></td><td><hr /></td><td><hr /></td></tr>';
									var style1 = '';
									var style2 = '';
									var style3 = '';
									var warn1 = '';
									var warn2 = '';
									var warn3 = '';
									if ( maxcount > 0 && files.length > maxcount ) {
										style1 = 'color:red';
										warn1 = '<?php _e( 'Too many!', 'wppa' ) ?>';
									}
									if ( maxsize > 0 && totsize > maxsize ) {
										style2 = 'color:red';
										warn2 = '<?php _e( 'Too big!', 'wppa' ) ?>';
									}
									if ( warn1 || warn2 ) {
										style3 = 'color:green';
										warn3 = '<?php _e( 'Try again!', 'wppa' ) ?>';
									}
									tekst += '<tr><td style="'+style1+'" ><?php _e( 'Total', 'wppa' ) ?>: '+files.length+' '+warn1+'</td><td style="'+style2+'" >'+totsize+' '+warn2+'</td><td style="'+style3+'" >'+warn3+'</td></tr>';
									tekst += '</tbody>';
								tekst += '</table>';
								jQuery( '#files_list2' ).html( tekst ); 
							}
						</script>
						<p>
							<label for="wppa-album"><?php _e( 'Album:', 'wppa' ); ?> </label>
							<select name="wppa-album" id="wppa-album-s">
								<?php echo wppa_album_select_a( array( 'path' => wppa_switch( 'wppa_hier_albsel' ), 'addpleaseselect' => true, 'checkaccess' => true, 'checkupload' => true ) ) ?>
							</select>
						</p>
						<?php if ( wppa_switch( 'wppa_watermark_on' ) && ( wppa_switch( 'wppa_watermark_user' ) || current_user_can( 'wppa_settings' ) ) ) { ?>		
							<p>		
								<?php _e( 'Apply watermark file:', 'wppa' ) ?>
								<select name="wppa-watermark-file" id="wppa-watermark-file">
									<?php echo( wppa_watermark_file_select() ) ?>
								</select>

								<?php _e( 'Position:', 'wppa' ) ?>
								<select name="wppa-watermark-pos" id="wppa-watermark-pos">
									<?php echo( wppa_watermark_pos_select() ) ?>
								</select>
							</p>
						<?php } ?>
						<input  type="submit" class="button-primary" name="wppa-upload-multiple" value="<?php _e( 'Upload Multiple Photos', 'wppa' ) ?>" onclick="if ( document.getElementById( 'wppa-album-s' ).value == 0 ) { alert( '<?php _e( 'Please select an album', 'wppa' ) ?>' ); return false; }" />
						<input type="checkbox" id="wppa-go-edit-multiple" name="wppa-go-edit-multiple" onchange="wppaCookieCheckbox( this, 'wppa-go-edit-multiple' )" />&nbsp;
						<script type="text/javascript" >
							if ( wppa_getCookie( 'wppa-go-edit-multiple' ) == 'on' ) document.getElementById( 'wppa-go-edit-multiple' ).checked = 'checked';
						</script>
						<?php 
						if ( current_user_can( 'wppa_admin' ) ) { 
							_e( 'After upload: Go to the <b>Edit Album</b> page.', 'wppa' );
						} 
						else {
							_e( 'After upload: Go to the <b>Edit Photos</b> page.', 'wppa' );
						}
						?>
					</form>
				</div>
				<?php /* End multiple */ ?>

				<?php /* Single photos */ ?>
				<div style="border:1px solid #ccc; padding:10px; margin-bottom:10px; width: 600px;">
					<h3 style="margin-top:0px;"><?php  _e( 'Box B:', 'wppa' ); echo ' ';_e( 'Single Photos in multiple selections', 'wppa' ); ?></h3>
					<?php echo sprintf( __( 'You can select up to %s photos one by one and upload them at once.', 'wppa' ), $max_files_txt ); ?>
					<form enctype="multipart/form-data" action="<?php echo( wppa_dbg_url( get_admin_url().'admin.php?page=wppa_upload_photos' ) ) ?>" method="post">
					<?php wp_nonce_field( '$wppa_nonce', WPPA_NONCE ); ?>
						<input id="my_file_element" type="file" name="file_1" />
						<div id="files_list">
							<h3><?php _e( 'Selected Files:', 'wppa' ); ?></h3>
							
						</div>
						<p>
							<label for="wppa-album"><?php _e( 'Album:', 'wppa' ); ?> </label>
							<select name="wppa-album" id="wppa-album-m">
								<?php echo wppa_album_select_a( array( 'path' => wppa_switch( 'wppa_hier_albsel' ), 'addpleaseselect' => true, 'checkaccess' => true, 'checkupload' => true ) );//( '', '', false, false, false, false, false, true ) ); ?>
							</select>
						</p>
						<?php if ( wppa_switch( 'wppa_watermark_on' ) && ( wppa_switch( 'wppa_watermark_user' ) || current_user_can( 'wppa_settings' ) ) ) { ?>		
							<p>		
								<?php _e( 'Apply watermark file:', 'wppa' ) ?>
								<select name="wppa-watermark-file" id="wppa-watermark-file">
									<?php echo( wppa_watermark_file_select() ) ?>
								</select>

								<?php _e( 'Position:', 'wppa' ) ?>
								<select name="wppa-watermark-pos" id="wppa-watermark-pos">
									<?php echo( wppa_watermark_pos_select() ) ?>
								</select>
							</p>
						<?php } ?>
						<input type="submit" class="button-primary" name="wppa-upload" value="<?php _e( 'Upload Single Photos', 'wppa' ) ?>" onclick="if ( document.getElementById( 'wppa-album-m' ).value == 0 ) { alert( '<?php _e( 'Please select an album', 'wppa' ) ?>' ); return false; }" />
						<input type="checkbox" id="wppa-go-edit-single" name="wppa-go-edit-single" onchange="wppaCookieCheckbox( this, 'wppa-go-edit-single' )" />&nbsp;
						<script type="text/javascript" >
							if ( wppa_getCookie( 'wppa-go-edit-single' ) == 'on' ) document.getElementById( 'wppa-go-edit-single' ).checked = 'checked';
						</script>
						<?php 
						if ( current_user_can( 'wppa_admin' ) ) {
							_e( 'After upload: Go to the <b>Edit Album</b> page.', 'wppa' );
						}
						else {
							_e( 'After upload: Go to the <b>Edit Photos</b> page.', 'wppa' );
						} 
						?>
					</form>
					<script type="text/javascript">
					<!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->
						var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), <?php echo( $max_files ) ?> );
					<!-- Pass in the file element -->
						multi_selector.addElement( document.getElementById( 'my_file_element' ) );
					</script>
				</div>
				<?php /* End single photos */ ?>

				<?php /* Single zips */ ?>
				<?php if ( current_user_can( 'wppa_import' ) ) { ?>
					<?php if ( PHP_VERSION_ID >= 50207 ) { ?>
						<div style="border:1px solid #ccc; padding:10px; width: 600px;">
							<h3 style="margin-top:0px;"><?php  _e( 'Box C:', 'wppa' ); echo ' ';_e( 'Zipped Photos in one selection', 'wppa' ); ?></h3>
							<?php echo sprintf( __( 'You can upload one zipfile. It will be placed in your personal wppa-depot: <b>.../%s</b><br/>Once uploaded, use <b>Import Photos</b> to unzip the file and place the photos in any album.', 'wppa' ), WPPA_DEPOT ) ?>
							<form enctype="multipart/form-data" action="<?php echo( wppa_dbg_url( get_admin_url().'admin.php?page=wppa_upload_photos' ) ) ?>" method="post">
							<?php wp_nonce_field( '$wppa_nonce', WPPA_NONCE ); ?>
								<input id="my_zipfile_element" type="file" name="file_zip" /><br/><br/>
								<input type="submit" class="button-primary" name="wppa-upload-zip" value="<?php _e( 'Upload Zipped Photos', 'wppa' ) ?>" />
								<input type="checkbox" id="wppa-go-import" name="wppa-go-import" onchange="wppaCookieCheckbox( this, 'wppa-go-import' )" />&nbsp;
								<script type="text/javascript" >
									if ( wppa_getCookie( 'wppa-go-import' ) == 'on' ) document.getElementById( 'wppa-go-import' ).checked = 'checked';
								</script>
								<?php _e( 'After upload: Go to the <b>Import Photos</b> page.', 'wppa' ) ?>
							</form>
						</div>
					<?php }
					else { ?>
						<div style="border:1px solid #ccc; padding:10px; width: 600px;">
						<?php _e( '<small>Ask your administrator to upgrade php to version 5.2.7 or later. This will enable you to upload zipped photos.</small>', 'wppa' ) ?>
						</div>
					<?php }
				} 
			}
		}
	else { ?>
			<?php $url = wppa_dbg_url( get_admin_url().'admin.php?page=wppa_admin_menu' ); ?>
			<p><?php _e( 'No albums exist. You must', 'wppa' ); ?> <a href="<?php echo( $url ) ?>"><?php _e( 'create one', 'wppa' ); ?></a> <?php _e( 'beofre you can upload your photos.', 'wppa' ); ?></p>
<?php } ?>
	</div>
<?php
}

// import images admin page
function _wppa_page_import() {
global $wppa_opt;
global $wppa_revno;
global $wppa;
global $wppa_supported_video_extensions;

	if ( $wppa['ajax'] ) ob_start();	// Suppress output if ajax operation
	
	// Init
	$ngg_opts 	= get_option( 'ngg_options', false );
	$user 		= wppa_get_user();
	
	// Check database
	wppa_check_database( true );

	// Sanitize system
	$count = wppa_sanitize_files();
	if ( $count ) wppa_error_message( $count.' '.__( 'illegal files deleted.', 'wppa' ) );

	// Update watermark settings
	if ( wppa_switch( 'wppa_watermark_on' ) && ( wppa_switch( 'wppa_watermark_user' ) || current_user_can( 'wppa_settings' ) ) ) {
		if ( isset( $_POST['wppa-watermark-file'] ) ) update_option( 'wppa_watermark_file_'.$user, $_POST['wppa-watermark-file'] );
		if ( isset( $_POST['wppa-watermark-pos'] ) ) update_option( 'wppa_watermark_pos_'.$user, $_POST['wppa-watermark-pos'] );
	}

	// Extract zip
	if ( isset( $_GET['zip'] ) ) {
		wppa_extract( $_GET['zip'], true );
	}
	
	// Set local / remote
	if ( isset( $_POST['wppa-local-remote'] ) ) {
		check_admin_referer( '$wppa_nonce', WPPA_NONCE );
		update_option( 'wppa_import_source_type_'.$user, $_POST['wppa-local-remote'] );
	}
	
	// Set import source dir ( when local )
	if ( isset( $_POST['wppa-import-set-source-dir'] ) ) {
		check_admin_referer( '$wppa_nonce', WPPA_NONCE );
		if ( isset( $_POST['wppa-source'] ) ) {
			update_option( 'wppa_import_source_'.$user, $_POST['wppa-source'] );
		}
	}
	
	// Set import source url ( when remote )
	if ( isset( $_POST['wppa-import-set-source-url'] ) ) {
		check_admin_referer( '$wppa_nonce', WPPA_NONCE );
		if ( isset( $_POST['wppa-source-remote'] ) ) {
			update_option( 'wppa_import_source_url_'.$user, $_POST['wppa-source-remote'] );
			update_option( 'wppa_import_source_url_found_'.$user, false );
			update_option( 'wppa_import_remote_max_'.$user, strval( intval( $_POST['wppa-import-remote-max'] ) ) );
		}
	}
	
	// Hit the submit button
	if ( isset( $_POST['wppa-import-submit'] ) ) {
		if ( $wppa['ajax'] ) {
			if ( ! wp_verify_nonce( $_POST['wppa-update-check'], '$wppa_nonce' ) ) {
				echo $_POST['wppa-update-check'].' Security check failure';
				exit;
			}
		} 
		else {
			check_admin_referer( '$wppa_nonce', WPPA_NONCE );
		}
        if ( isset( $_POST['del-after-p'] ) ) $delp = true; else $delp = false;
		if ( isset( $_POST['del-after-a'] ) ) $dela = true; else $dela = false;	
		if ( isset( $_POST['del-after-z'] ) ) $delz = true; else $delz = false;
		if ( isset( $_POST['del-after-v'] ) ) $delv = true; else $delv = false;

		wppa_import_photos( $delp, $dela, $delz, $delv );
	} 
	
	// Continue dirimport after timeout
	elseif ( isset( $_GET['continue'] ) ) {
		if ( wp_verify_nonce( $_GET['nonce'], 'dirimport' ) ) wppa_import_photos();
	}
	
	// If we did this by ajax, setup reporting results for it
	if ( $wppa['ajax'] ) {
		ob_end_clean();
		if ( $wppa['ajax_import_files_done'] ) {
			echo '<span style="color:green" >'.$wppa['ajax_import_files'].' '.__( 'Done!', 'wppa' ).'</span>';
		}
		elseif ( $wppa['ajax_import_files_error'] ) {
			echo '<span style="color:red" >'.$wppa['ajax_import_files'].' '.$wppa['ajax_import_files_error'].'</span>';
		}
		else {
			echo '<span style="color:red" >'.$wppa['ajax_import_files'].' '.__( 'Failed!', 'wppa' ).'</span>';
		}
		exit;
	}

	// Sanitize again
	$count = wppa_sanitize_files();
	if ( $count ) wppa_error_message( $count.' '.__( 'illegal files deleted.', 'wppa' ) );
?>
	
	<div class="wrap">
		<?php $iconurl = WPPA_URL.'/images/camera32.png'; ?>
		<div id="icon-camera" class="icon32" style="background: transparent url( <?php echo( $iconurl ); ?> ) no-repeat"></div>
		<?php $iconurl = WPPA_URL.'/images/arrow32.png'; ?>
		<div id="icon-arrow" class="icon32" style="background: transparent url( <?php echo( $iconurl ); ?> ) no-repeat"></div>
		<?php $iconurl = WPPA_URL.'/images/album32.png'; ?>
		<div id="icon-album" class="icon32" style="background: transparent url( <?php echo( $iconurl ); ?> ) no-repeat"><br /></div>
		
		<h2><?php _e( 'Import Photos', 'wppa' ); ?></h2><br />
		<?php	
		
		// Get this users current source directory setting
		$can_remote = ini_get( 'allow_url_fopen' ) && function_exists( 'curl_init' );
		// $can_remote = false; // Debug
		if ( ! $can_remote ) {
			update_option( 'wppa_import_source_type_'.$user, 'local' );
		}
		$source_type = get_option( 'wppa_import_source_type_'.$user, 'local' );
		if ( $source_type == 'local' ) {
			$source      = get_option( 'wppa_import_source_'.$user, WPPA_DEPOT );
			if ( ! $source || ! is_dir( WPPA_ABSPATH . $source ) ) {
				$source = WPPA_DEPOT;
				update_option( 'wppa_import_source_'.$user, WPPA_DEPOT );
			}
			$source_path = WPPA_ABSPATH . $source;
			$source_url  = get_bloginfo( 'url' ) . '/' . $source;
			// See if the current source is the 'home' directory
			$is_depot 	= ( $source == WPPA_DEPOT );
			// See if the current souce is a wp upload location or a wppa+ sourcefile location ( if so: no delete checkbox )
			$is_sub_depot = ( substr( $source, 0, strlen( WPPA_DEPOT ) ) == WPPA_DEPOT ) && ( substr( WPPA_ABSPATH.$source, 0, strlen( $wppa_opt['wppa_source_dir'] ) ) != $wppa_opt['wppa_source_dir'] );
			// See what's in there
			$files 		= wppa_get_import_files();
			$zipcount 	= wppa_get_zipcount( $files );
			$albumcount = wppa_get_albumcount( $files );
			$photocount = wppa_get_photocount( $files );
			$videocount = wppa_get_video_count( $files );
			$dircount	= $is_depot ? wppa_get_dircount( $files ) : '0';
			// echo 'zips:'.$zipcount,' albs:'.$albumcount.' pho:'.$photocount.' dirs:'.$dircount;
			if ( $ngg_opts ) {
				$is_ngg = strpos( $source, $ngg_opts['gallerypath'] ) !== false;	// this is false for the ngg root !!
			}
			else $is_ngg = false;
		}
		if ( $source_type == 'remote' ) {
			$wppa['is_remote'] = true;
			$source     	= get_option( 'wppa_import_source_url_'.$user, 'http://' );
			$source_path 	= $source;
			$source_url 	= $source;
			$is_depot 		= false;
			$is_sub_depot 	= false;
			$files 			= wppa_get_import_files();
			$zipcount 		= '0';
			$albumcount 	= '0';
			$photocount 	= $files ? count( $files ) : '0';
			$videocount 	= '0';
			$dircount		= '0';
			$is_ngg 		= false;
			$remote_max 	= get_option( 'wppa_import_remote_max_'.$user, '10' );
		}

?>		
	<form action="<?php echo( wppa_dbg_url( get_admin_url().'admin.php?page=wppa_import_photos' ) ) ?>" method="post">
		<?php if ( current_user_can( 'administrator' ) || $wppa_opt['wppa_chgsrc_is_restricted'] == 'no' ) { ?>

		<div style="border:1px solid gray; padding:4px; margin: 3px 0;" >
			<?php 
				wp_nonce_field( '$wppa_nonce', WPPA_NONCE ); 
				_e( 'Select Local or Remote', 'wppa' ); 
				$disabled = $can_remote ? '' : 'disabled="disabled"';
			?>
			<select name="wppa-local-remote" >
				<option value="local" <?php if ( $source_type == 'local' ) echo 'selected="selected"' ?>><?php _e( 'Local', 'wppa' ) ?></option>
				<option value="remote" <?php echo $disabled; if ( $source_type == 'remote' ) echo 'selected="selected"' ?>><?php _e( 'Remote', 'wppa' ) ?></option>
			</select>
			<?php if ( $can_remote ) { ?>
				<input type="submit" class="button-secundary" name="wppa-import-set-source" value="<?php _e( 'Set Local/Remote', 'wppa' ); ?>" />
			<?php } else { 
				if ( ! ini_get( 'allow_url_fopen' ) )
					_e( 'The server does not allow you to import from remote locations. ( The php directive allow_url_fopen is not set to 1 )', 'wppa' );
				if ( ! function_exists( 'curl_init' ) )
					_e( 'The server does not allow you to import from remote locations. ( The curl functions are not set up )', 'wppa' );
			} ?>
		</div>
		<div style="border:1px solid gray; padding:4px; margin: 3px 0;" >
			<?php wp_nonce_field( '$wppa_nonce', WPPA_NONCE ); ?>
			<?php _e( 'Import photos from:', 'wppa' ); ?>
			<?php if ( $source_type == 'local' ) { ?>
			<select name="wppa-source">
				<option value="<?php echo( WPPA_DEPOT ) ?>" <?php if ( $is_depot ) echo( 'selected="selected"' ) ?>><?php _e( '--- My depot ---', 'wppa' ) ?></option>
				<?php 
					wppa_walktree( WPPA_DEPOT, $source, true, true ); 				// Allow the name 'wppa', exclude topdir
					if ( $ngg_opts ) {
						$nextgen_root = trim( $ngg_opts['gallerypath'], '/' );
						wppa_walktree( $nextgen_root, $source, true, true, false ); 	// Allow the name 'wppa', exclude topdir, do not allow 'thumbs' */ 
					} 
					wppa_walktree( WPPA_UPLOAD, $source, false, false ); 				// Do NOT allow the name 'wppa', include topdir
				?>
			</select>
			<input type="submit" class="button-secundary" name="wppa-import-set-source-dir" value="<?php _e( 'Set source directory', 'wppa' ); ?>" />
			<?php } else { ?>
			<input type="text" style="width:50%" name="wppa-source-remote" value="<?php echo $source ?>" />
			<?php _e( 'Max:', 'wppa' ) ?>
			<input type="text" style="width:50px;" name="wppa-import-remote-max" value="<?php echo $remote_max ?>" />
			<input type="submit" onclick="jQuery( '#rem-rem' ).css( 'display','inline' ); return true;" class="button-secundary" name="wppa-import-set-source-url" value="<?php _e( 'Find remote photos', 'wppa' ); ?>" />
			<span id="rem-rem" style="display:none;"><?php _e( 'Working, please wait...', 'wppa' ) ?></span>
			<?php _e( '<br />You can enter either a web page address like <i>http://mysite.com/mypage/</i> or a full url to an image file like <i>http://mysite.com/wp-content/uploads/wppa/4711.jpg</i>', 'wppa' ); ?>
			<?php } ?>
		</div>
		
		<?php } ?>
	</form>

<?php
		
		// check if albums exist or will be made before allowing upload
		if ( wppa_has_albums() || $albumcount > '0' || $zipcount >'0' || $dircount > '0' || $videocount > '0' ) { 
	
			if ( $photocount > '0' || $albumcount > '0' || $zipcount >'0' || $dircount > '0' || $videocount > '0' ) { ?>
			
				<form action="<?php echo( wppa_dbg_url( get_admin_url().'admin.php?page=wppa_import_photos' ) ) ?>" method="post">
				<?php wp_nonce_field( '$wppa_nonce', WPPA_NONCE ); 
				
				// Display the zips
				if ( PHP_VERSION_ID >= 50207 && $zipcount > '0' ) { ?>	
					<div style="border:1px solid gray; padding:4px; margin: 3px 0;" >
						<p><b>
							<?php _e( 'There are', 'wppa' ); echo( ' '.$zipcount.' ' ); _e( 'zipfiles in the depot.', 'wppa' ) ?><br/>
						</b></p>
						<table class="form-table wppa-table widefat" style="margin-bottom:0;" >
							<thead>
								<tr>
									<td>
										<input type="checkbox" id="all-zip" checked="checked" onchange="checkAll( 'all-zip', '.wppa-zip' )" /><b>&nbsp;&nbsp;<?php _e( 'Check/uncheck all', 'wppa' ) ?></b>
									</td>
									<?php if ( $is_sub_depot ) { ?>
										<td>
											<input type="checkbox" id="del-after-z" name="del-after-z" checked="checked" /><b>&nbsp;&nbsp;<?php _e( 'Delete after successful extraction.', 'wppa' ); ?></b>
										</td>
									<?php } ?>
								</tr>
							</thead>
						</table>
						<table class="form-table wppa-table widefat" style="margin-top:0;" >
							<tr>
								<?php
								$ct = 0;
								$idx = '0';
								foreach ( $files as $file ) {
						
									$ext = strtolower( substr( strrchr( $file, "." ), 1 ) );
									if ( $ext == 'zip' ) { ?>
										<td>
											<input type="checkbox" id="file-<?php echo( $idx ) ?>" name="file-<?php echo( $idx ) ?>" class="wppa-zip" checked="checked" />&nbsp;&nbsp;<?php echo( wppa_sanitize_file_name( basename( $file ) ) ); ?>
										</td>
										<?php if ( $ct == 3 ) {
											echo( '</tr><tr>' ); 
											$ct = 0;
										}
										else {
											$ct++;
										}
									}
									$idx++;
								} ?>
							</tr>
						</table>
					</div>
				<?php }
				
				// Dispay the albums ( .amf files )
				if ( $albumcount > '0' ) { ?>
					<div style="border:1px solid gray; padding:4px; margin: 3px 0;" >
						<p><b>
							<?php _e( 'There are', 'wppa' ); echo( ' '.$albumcount.' ' ); _e( 'albumdefinitions in the depot.', 'wppa' ) ?><br/>
						</b></p>
						<table class="form-table wppa-table widefat" style="margin-bottom:0;" >
							<thead>
								<tr>
									<td>
										<input type="checkbox" id="all-amf" checked="checked" onchange="checkAll( 'all-amf', '.wppa-amf' )" /><b>&nbsp;&nbsp;<?php _e( 'Check/uncheck all', 'wppa' ) ?></b>
									</td>
									<?php if ( $is_sub_depot ) { ?>
										<td>
											<input type="checkbox" id="del-after-a" name="del-after-a" checked="checked" /><b>&nbsp;&nbsp;<?php _e( 'Delete after successful import, or if the album already exits.', 'wppa' ); ?></b>
										</td>
									<?php } ?>
								</tr>
							</thead>
						</table>
						<table class="form-table wppa-table widefat"  style="margin-top:0;" >
							<tr>
								<?php
								$ct = 0;
								$idx = '0';
								foreach ( $files as $file ) {
									$ext = strtolower( substr( strrchr( $file, "." ), 1 ) );
									if ( $ext == 'amf' ) { ?>
										<td>
											<input type="checkbox" id="file-<?php echo( $idx ) ?>" name="file-<?php echo( $idx ) ?>" class="wppa-amf" checked="checked" />&nbsp;&nbsp;<?php echo( basename( $file ) ); ?>&nbsp;<?php echo( stripslashes( wppa_get_meta_name( $file, '( ' ) ) ) ?>
										</td>
										<?php if ( $ct == 3 ) {
											echo( '</tr><tr>' ); 
											$ct = 0;
										}
										else {
											$ct++;
										}
									}
									$idx++;
								} ?>
							</tr>
						</table>
					</div>
				<?php }
				
				// Display the single photos
				if ( $photocount > '0' ) { ?>
					<div style="border:1px solid gray; padding:4px; margin: 3px 0;" >
						<p><b>
							<?php _e( 'There are', 'wppa' ); echo( ' '.$photocount.' ' ); 
								if ( $source_type == 'local' ) {
									if ( $is_ngg ) {
										_e( 'photos in the ngg gallery.', 'wppa' );
									}
									else _e( 'photos in the depot.', 'wppa' ); 
								}
								else _e( 'possible photos found remote.', 'wppa' );						
								if ( wppa_switch( 'wppa_resize_on_upload' ) ) { echo( ' ' ); _e( 'Photos will be downsized during import.', 'wppa' ); } ?><br/>
						</b></p>
						<p class="hideifupdate" >
							<?php _e( 'Default album for import:', 'wppa' ) ?>
							<select name="wppa-album" id="wppa-album">
								<!--<option value=""><?php // _e( '- select an album -', 'wppa' ) ?></option>-->
								<?php echo wppa_album_select_a( array( 'path' => wppa_switch( 'wppa_hier_albsel' ), 'addpleaseselect' => true, 'checkaccess' => true, 'checkupload' => true ) ); // ( '', '', false, false, false, false, false, true ) ) ?>
							</select>
							<?php _e( 'Photos that have (<em>name</em>)[<em>album</em>] will be imported by that <em>name</em> in that <em>album</em>.', 'wppa' ) ?>
						</p>
						<?php if ( wppa_switch( 'wppa_watermark_on' ) && ( wppa_switch( 'wppa_watermark_user' ) || current_user_can( 'wppa_settings' ) ) ) { ?>
							<p>
								<?php _e( 'Apply watermark file:', 'wppa' ) ?>
								<select name="wppa-watermark-file" id="wppa-watermark-file">
									<?php echo( wppa_watermark_file_select() ) ?>
								</select>
								<?php _e( 'Position:', 'wppa' ) ?>
								<select name="wppa-watermark-pos" id="wppa-watermark-pos">
									<?php echo( wppa_watermark_pos_select() ) ?>
								</select>
							</p>
						<?php } ?>
						<table class="form-table wppa-table widefat" style="margin-bottom:0;" >
							<thead>
								<tr>
									<td>
										<input type="checkbox" id="all-pho" <?php if ( $is_sub_depot ) echo( 'checked="checked"' ) ?> onchange="checkAll( 'all-pho', '.wppa-pho' )" /><b>&nbsp;&nbsp;<?php _e( 'Check/uncheck all', 'wppa' ) ?></b>
									</td>
									<?php if ( $is_sub_depot ) { ?>
										<td>
											<input type="checkbox" id="del-after-p" name="del-after-p" checked="checked" /><b>&nbsp;&nbsp;<?php _e( 'Delete after successful import.', 'wppa' ); ?></b>
										</td>
									<?php } ?>
									<?php if ( $is_ngg ) { ?>
										<td>
											<input type="checkbox" id="cre-album" name="cre-album" checked="checked" value="<?php echo esc_attr( basename( $source ) ) ?>" /><b>&nbsp;&nbsp;<?php echo sprintf( __( 'Import into album <i>%s</i>.', 'wppa' ), basename( $source ) ); ?></b>
											<br /><small><?php _e( 'The album will be created if it does not exist', 'wppa' ) ?></small>
										</td>
										<td>
											<input type="checkbox" id="use-backup" name="use-backup" checked="checked" /><b>&nbsp;&nbsp;<?php _e( 'Use backup if available', 'wppa' ) ?></b>
										</td>
									<?php } ?>
									<td>
										<input type="checkbox" id="wppa-update" onchange="impUpd( this, '#submit' )" name="wppa-update"><b>&nbsp;&nbsp;<?php _e( 'Update existing photos', 'wppa' ) ?></b>
									</td>
									<td>
									<?php if ( wppa_switch( 'wppa_void_dups' ) ) { ?>
										<input type="hidden" id="wppa-nodups" name="wppa-nodups" value="true" />
									<?php } else { ?>
										<input type="checkbox" id="wppa-nodups" name="wppa-nodups" checked="checked" ><b>&nbsp;&nbsp;<?php _e( 'Do not create duplicates', 'wppa' ) ?></b>
									<?php } ?>
									</td>
									<?php
									if ( wppa_switch( 'wppa_import_preview' ) ) { ?>
										<td>
											<input type="checkbox" id="wppa-zoom" onclick="wppa_setCookie('zoompreview', this.checked, '365')" ><b>&nbsp;&nbsp;<?php _e( 'Zoom previews', 'wppa' ) ?></b>
											<script type="text/javascript">if ( wppa_getCookie('zoompreview') == true ) { jQuery('#wppa-zoom').attr('checked', 'checked') }</script>
										</td>
									<?php } ?>
								</tr>
							</thead>
						</table>				
						<table class="form-table wppa-table widefat" style="margin-top:0;" >
							<tr> 
								<?php
								$ct = 0;
								$idx = '0';
								if ( is_array( $files ) ) foreach ( $files as $file ) {
									$ext = strtolower( substr( strrchr( $file, "." ), 1 ) );
									$meta =	substr( $file, 0, strlen( $file )-3 ).'pmf';
									if ( $ext == 'jpg' || $ext == 'png' || $ext == 'gif' ) { ?>
										<td id="td-file-<?php echo( $idx ) ?>" >
											<input type="checkbox" id="file-<?php echo( $idx ) ?>" name="file-<?php echo( $idx ) ?>" title="<?php echo $file ?>" class= "wppa-pho" <?php if ( $is_sub_depot ) echo( 'checked="checked"' ) ?> /><span id="name-file-<?php echo( $idx ) ?>" >&nbsp;&nbsp;<?php echo( wppa_sanitize_file_name( basename( $file ) ) ); ?>&nbsp;<?php echo( stripslashes( wppa_get_meta_name( $meta, '( ' ) ) ) ?><?php echo( stripslashes( wppa_get_meta_album( $meta, '[' ) ) ) ?></span>
											<?php 
											if ( wppa_switch( 'wppa_import_preview' ) ) {
												if ( $wppa['is_remote'] ) { 
													if ( strpos( $file, '//res.cloudinary.com/' ) !== false ) {
														$img_url = dirname( $file ) . '/h_144/' . basename( $file );
													}
													else {
														$img_url = $file; 
													}
												}
												else { 
													$img_url = str_replace( ABSPATH, home_url().'/', $file );
												} 
											?>
											<img src="<?php echo $img_url ?>" alt="N.A." style="max-height:48px;" onmouseover="if (jQuery('#wppa-zoom').attr('checked')) jQuery(this).css('max-height', '144px')" onmouseout="if (jQuery('#wppa-zoom').attr('checked')) jQuery(this).css('max-height', '48px')" />
											<?php } ?>
										</td>
										<?php if ( $ct == 3 ) {
											echo( '</tr><tr>' ); 
											$ct = 0;
										}
										else {
											$ct++;
										}
									}
									$idx++;
								} ?>
							</tr>
						</table>
					</div>
				<?php } 
				// Display the videos
				if ( $videocount > '0' ) { ?>
					<div style="border:1px solid gray; padding:4px; margin: 3px 0;" >
						<p><b>
							<?php _e( 'There are', 'wppa' ); echo( ' '.$videocount.' ' ); _e( 'videos in the depot.', 'wppa' ) ?><br/>
						</b></p>
						<p class="hideifupdate" >
							<?php _e( 'Album to import to:', 'wppa' ) ?>
							<select name="wppa-video-album" id="wppa-video-album">
								<option value=""><?php _e( '- select an album -', 'wppa' ) ?></option>
								<?php echo wppa_album_select_a( array( 'path' => wppa_switch( 'wppa_hier_albsel' ), 'addpleaseselect' => true, 'checkaccess' => true, 'checkupload' => true ) ); // ( '', '', false, false, false, false, false, true ) ) ?>
							</select>
						</p>
						<table class="form-table wppa-table widefat" style="margin-bottom:0;" >
							<thead>
								<tr>
									<td>
										<input type="checkbox" id="all-video" checked="checked" onchange="checkAll( 'all-video', '.wppa-video' )" /><b>&nbsp;&nbsp;<?php _e( 'Check/uncheck all', 'wppa' ) ?></b>
									</td>
									<?php if ( $is_sub_depot ) { ?>
										<td>
											<input type="checkbox" id="del-after-v" name="del-after-v" checked="checked" /><b>&nbsp;&nbsp;<?php _e( 'Delete after successful import.', 'wppa' ); ?></b>
										</td>
									<?php } ?>
								</tr>
							</thead>
						</table>
						<table class="form-table wppa-table widefat" style="margin-top:0;" >
							<tr>
								<?php
								$ct = 0;
								$idx = '0';
								if ( is_array( $files ) ) foreach ( $files as $file ) {
									$ext = strtolower( substr( strrchr( $file, "." ), 1 ) );
									if ( in_array( $ext, $wppa_supported_video_extensions ) ) { ?>
										<td>
											<input type="checkbox" id="file-<?php echo( $idx ) ?>" name="file-<?php echo( $idx ) ?>" title="<?php echo $file ?>" class="wppa-video" checked="checked" /><span id="name-file-<?php echo( $idx ) ?>" >&nbsp;&nbsp;<?php echo( wppa_sanitize_file_name( basename( $file ) ) ); ?></span>
										</td>
										<?php if ( $ct == 3 ) {
											echo( '</tr><tr>' ); 
											$ct = 0;
										}
										else {
											$ct++;
										}
									}
									$idx++;
								} ?>
							</tr>
						</table>
					</div>
				<?php }
				
				// Display the directories to be imported as albums. Do this in the depot only!!
				if ( $is_depot && $dircount > '0' ) { ?>
					<div style="border:1px solid gray; padding:4px; margin: 3px 0;" >
						<p><b>
							<?php _e( 'There are', 'wppa' ); echo( ' '.$dircount.' ' ); _e( 'albumdirectories in the depot.', 'wppa' ) ?><br/>
						</b></p>
						<table class="form-table wppa-table widefat" style="margin-bottom:0;" >
							<thead>
								<tr>
									<td>
										<input type="checkbox" id="all-dir" checked="checked" onchange="checkAll( 'all-dir', '.wppa-dir' )" /><b>&nbsp;&nbsp;<?php _e( 'Check/uncheck all', 'wppa' ) ?></b>
									</td>
								</tr>
							</thead>
						</table>				
						<table class="form-table wppa-table widefat" style="margin-top:0;" >
							<?php 
								$ct = 0; 
								$idx = '0';
								foreach( $files as $dir ) { 
									if ( basename( $dir ) == '.' ) {}
									elseif ( basename( $dir ) == '..' ) {}
									elseif ( is_dir( $dir ) ) { ?>
										<tr>
											<td>
												<input type="checkbox" id="file-<?php echo( $idx ) ?>" name="file-<?php echo( $idx ) ?>" class= "wppa-dir" checked="checked" />&nbsp;&nbsp;<b><?php echo( wppa_sanitize_file_name( basename( $dir ) ) ) ?></b>
												<?php
													$subfiles = glob( $dir.'/*' );
													$subdircount = '0';
													if ( $subfiles ) foreach ( $subfiles as $subfile ) if ( is_dir( $subfile ) && basename( $subfile ) != '.' && basename( $subfile ) != '..' ) $subdircount++;
													$sfcount = empty( $subfiles ) ? '0' : wppa_get_photocount( $subfiles );
													echo ' Contains '.$sfcount.' files';
													if ( $subdircount ) echo ' and '.$subdircount.' sub directories.';
												?>
											</td>
										</tr>
									<?php 
									}
									$idx++;
								} ?>
						</table>
					</div>				
				<?php } ?>
				<?php
				// The submit button
				?>
				<p>
					<script type="text/javascript">
						function wppaVfyAlbum() {
							if ( jQuery( '#wppa-update' ).attr( 'checked' ) != 'checked' ) {
								if ( ! parseInt( jQuery( '#wppa-album' ).attr( 'value' ) ) && ! parseInt( jQuery( '#wppa-video-album' ).attr( 'value' ) ) ) {
									alert( 'Please select an album first' );
									return false;
								}
							}
							return true;
						}
						function wppaCheckInputVars() {
							var checks = jQuery( ':checked' );
							var nChecks = checks.length;
							var nMax = <?php echo ini_get( 'max_input_vars' ) ?>;
							if ( nMax == 0 ) nMax = 100;
							if ( nChecks > nMax ) { 
								alert ( 'There are '+nChecks+' boxes checked or selected, that is more than the maximum allowed number of '+nMax );
								return false;
							}
							var dirs = jQuery( '.wppa-dir' );
							var nDirsChecked = 0;
							if ( dirs.length > 0 ) {
								var i = 0;
								while ( i < dirs.length ) {
									if ( jQuery( dirs[i] ).attr( 'checked' ) == 'checked' ) {
										nDirsChecked++;
									}
									i++;
								}
							}
							var zips = jQuery( '.wppa-zip' );
							var nZipsChecked = 0;
							if ( zips.length > 0 ) {
								var i = 0;
								while ( i < zips.length ) {
									if ( jQuery( zips[i] ).attr( 'checked' ) == 'checked' ) {
										nZipsChecked++;
									}
									i++;
								}
							}
							// If no dirs to import checked, there must be an album selected
							if ( 0 == nDirsChecked && 0 == nZipsChecked && ! wppaVfyAlbum() ) return false;
							return true;
						}
					</script>
					<input type="submit" onclick="return wppaCheckInputVars()" class="button-primary" id="submit" name="wppa-import-submit" value="<?php _e( 'Import', 'wppa' ); ?>" />
					<script type="text/javascript" >
						var wppaImportRuns = false;
						function wppaDoAjaxImport() {
							wppaImportRuns = true;
							var data = '';
							data += 'wppa-update-check='+jQuery( '#wppa-update-check' ).attr( 'value' );
							data += '&wppa-album='+jQuery( '#wppa-album' ).attr( 'value' );
							data += '&wppa-video-album='+jQuery( '#wppa-video-album' ).attr( 'value' );
							data += '&wppa-watermark-file='+jQuery( '#wppa-watermark-file' ).attr( 'value' );
							data += '&wppa-watermark-pos='+jQuery( '#wppa-watermark-pos' ).attr( 'value' );
							if ( jQuery( '#cre-album' ).attr( 'checked' ) ) data += '&cre-album='+jQuery( '#cre-album' ).attr( 'value' );
							if ( jQuery( '#use-backup' ).attr( 'checked' ) ) data += '&use-backup=on'; //+jQuery( '#use-backup' ).attr( 'value' );
							if ( jQuery( '#wppa-update' ).attr( 'checked' ) ) data += '&wppa-update=on'; //+jQuery( '#wppa-update' ).attr( 'value' );
							if ( jQuery( '#wppa-nodups' ).attr( 'checked' ) ) data += '&wppa-nodups=on'; //+jQuery( '#wppa-nudups' ).attr( 'value' );
							if ( jQuery( '#del-after-p' ).attr( 'checked' ) ) data += '&del-after-p=on';
							if ( jQuery( '#del-after-v' ).attr( 'checked' ) ) data += '&del-after-v=on';
							data += '&wppa-import-submit=ajax';
							
							var files = jQuery( ':checked' );
							var found = false;
							var i=0;
							var elm;
							var fulldata;
							for ( i=0; i<files.length; i++ ) {
								found = false;	// assume done
								elm = files[i];
								// Is it a file checkbox?
								var temp = elm.id.split( '-' );
								if ( temp[0] != 'file' ) continue;	// no
								fulldata = data+'&import-ajax-file='+elm.title;
								found = true;
								break;
							}
							//	alert( data );
							if ( ! found ) {
								wppaStopAjaxImport();
								return;	// nothing left
							}
							// found one, do it
							var oldhtml=jQuery( '#name-'+elm.id ).html();
							var xmlhttp = wppaGetXmlHttp();
							xmlhttp.onreadystatechange = function() {
								if ( xmlhttp.readyState == 4 ) {
									if ( xmlhttp.status!=404 ) {
										if ( jQuery( '#del-after-p' ).attr( 'checked' ) ) {
											elm.checked = '';
											elm.disabled = 'disabled';
											elm.title = '';
											jQuery( '#name-'+elm.id ).html( '&nbsp;&nbsp;<b>'+xmlhttp.responseText+'</b>' );
										}
										else {
											elm.checked = '';
											
											jQuery( '#name-'+elm.id ).html( '&nbsp;&nbsp;<b>'+xmlhttp.responseText+'</b>' );
										}
										if ( wppaImportRuns ) {
											setTimeout( 'wppaDoAjaxImport()', 100 );
										}
									}
									else {
										jQuery( '#name-'+elm.id ).html( '&nbsp;&nbsp;<b>Not found</b>' );
									}
								}
							}
							var url = wppaAjaxUrl+'?action=wppa&wppa-action=import';
							xmlhttp.open( 'POST',url,true );
							xmlhttp.setRequestHeader( "Content-type","application/x-www-form-urlencoded" );
							xmlhttp.send( fulldata );
							jQuery( '#name-'+elm.id ).html( '&nbsp;&nbsp;<b style="color:blue" >Working...</b>' );
							jQuery( '#wppa-start-ajax' ).css( 'display', 'none' );
							jQuery( '#wppa-stop-ajax' ).css( 'display', 'inline' );
						}
						function wppaStopAjaxImport() {
							wppaImportRuns = false;
							jQuery( '#wppa-start-ajax' ).css( 'display', 'inline' );
							jQuery( '#wppa-stop-ajax' ).css( 'display', 'none' );
						}
					</script>
					<?php if ( ( $photocount || $videocount ) && ! $albumcount && ! $dircount && ! $zipcount ) { ?>
					<input id="wppa-start-ajax" type="button" onclick="if ( wppaVfyAlbum() ) { wppaDoAjaxImport() }" class="button-secundary" value="Start Ajax" />
					<input id="wppa-stop-ajax" style="display:none;" type="button" onclick="wppaStopAjaxImport()" class="button-secundary" value="Stop Ajax" />
					<?php } ?>
				</p>
				</form>

		<?php }
		else {
			if ( $source_type == 'local' ) {
				if ( PHP_VERSION_ID >= 50207 ) {
					if ( wppa_is_video_enabled() ) {
						wppa_ok_message( __( 'There are no archives, albums, photos or videos in directory:', 'wppa' ).' '.$source_url );
					}
					else {
						wppa_ok_message( __( 'There are no archives, albums or photos in directory:', 'wppa' ).' '.$source_url );
					}
				}
				else {
					if ( wppa_is_video_enabled() ) {
						wppa_ok_message( __( 'There are no albums, photos or videos in directory:', 'wppa' ).' '.$source_url );
					}
					else {
						wppa_ok_message( __( 'There are no albums or photos in directory:', 'wppa' ).' '.$source_url );
					}
				}
			}
			else {
				wppa_ok_message( __( 'There are no photos found or left to process at url:', 'wppa' ).' '.$source_url );
			}
		}
	}
	else { ?>
		<?php $url = wppa_dbg_url( get_admin_url().'admin.php?page=wppa_admin_menu' ); ?>
		<p><?php _e( 'No albums exist. You must', 'wppa' ); ?> <a href="<?php echo( $url ) ?>"><?php _e( 'create one', 'wppa' ); ?></a> <?php _e( 'beofre you can upload your photos.', 'wppa' ); ?></p><?php 
	} 
	if ( $wppa['continue'] ) {
		wppa_warning_message( __( 'Trying to continue...', 'wppa' ) );
		echo '<script type="text/javascript">document.location=\''.get_admin_url().'admin.php?page=wppa_import_photos&continue&nonce='.wp_create_nonce( 'dirimport' ).'\';</script>';
	} ?>
	</div>
<?php
}

// get array of files to import
function wppa_get_import_files() {

	// Init
	$user 			= wppa_get_user();
	$source_type 	= get_option( 'wppa_import_source_type_'.$user, 'local' );
	$files			= array();
	
	// Ajax? one file
	if ( isset ( $_POST['import-ajax-file'] ) ) {
		$files = array( $_POST['import-ajax-file'] );
	}
	
	// Dispatch on source type local/remote
	elseif ( $source_type == 'local' ) {
		$source 		= get_option( 'wppa_import_source_'.$user, WPPA_DEPOT );
		$source_path 	= WPPA_ABSPATH . $source;	// Filesystem
		$files 			= glob( $source_path . '/*' );
	}
	else { // remote
		$max_tries 		= get_option( 'wppa_import_remote_max_'.$user, '10' );
		$setting 		= get_option( 'wppa_import_source_url_'.$user, 'http://' );
		$pattern		= '/src=".*?"/';
										
		if ( is_array( @ getimagesize( $setting ) ) ) {	// image uri
			$files = array( $setting );
			$pid = wppa_strip_ext( basename( $setting ) );
			if ( is_numeric( $pid ) ) {
				$tries = 1;
				$before = substr( $setting, 0, strpos( $setting, $pid) );
				while ( $tries < $max_tries ) {
					$tries++;
					$pid++;
					$files[] = $before.$pid.'.jpg';
				}
			}
		}
		else {	// page url
			$files = get_option( 'wppa_import_source_url_found_'.$user, false );
			if ( $files === false ) {
			
				// Init
				$files = array();
				
				// Get page content
				$curl = curl_init();
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt( $curl, CURLOPT_URL, $setting );
				$contents = curl_exec( $curl );
				curl_close( $curl );
				
				// Process result
				if ( $contents ) {
				
					// Preprocess
					$contents = str_replace( '\'', '"', $contents );
					
					// Find matches
					preg_match_all( $pattern, $contents, $matches, PREG_PATTERN_ORDER );
					if ( is_array( $matches[0] ) ) {
					
						// Sort
						sort( $matches[0] );
						
						// Copy to $files, skipping dups
						$val = '';
						$count = 0;
						$sfxs = array( 'jpg', 'gif', 'png', 'JPG', 'GIF', 'PNG' );
						foreach ( array_keys( $matches[0] ) as $idx ) {
							if ( $matches[0][$idx] != $val ) {
								$val = $matches[0][$idx];
								// Post process found item
								$match 		= substr( $matches[0][$idx], 5 );
								$matchpos 	= strpos( $contents, $match );
								$match 		= trim( $match, '"' );
								if ( strpos( $match, '?' ) ) $match = substr( $match, 0, strpos( $match, '?' ) );
								$match 		= str_replace( '/uploads/wppa/thumbs/', '/uploads/wppa/', $match );
								$sfx = substr( $match, -3 );
								if ( in_array( $sfx, $sfxs ) ) {
									// Save it
									$count++;
									if ( $count <= $max_tries ) {
										$files[] = $match;
									}
								}
							}
						}
					}
				}
				update_option( 'wppa_import_source_url_found_'.$user, $files );
			}
		}
	}
	
	// Remove non originals
	foreach ( array_keys( $files ) as $key ) {
		if ( ! wppa_is_orig( $files[$key] ) ) {
			unset ( $files[$key] );
		}
	}
	
	// Sort to keep synchronicity when doing ajax import
	if ( is_array( $files ) ) sort( $files );
	
	// Done, return result
	return $files;
}

// Upload multiple photos
function wppa_upload_multiple() {
	global $wpdb;
	global $warning_given;

	$warning_given = false;
	$uploaded_a_file = false;
	
	$count = '0';
	foreach ( $_FILES as $file ) {
		if ( is_array( $file['error'] ) ) {
			for ( $i = '0'; $i < count( $file['error'] ); $i++ ) {
				if ( wppa_is_time_up() ) {
					wppa_error_message( sprintf( __( 'Time out. %s photos uploaded in album nr %s.', 'wppa' ), $count, $_POST['wppa-album'] ) );
					wppa_set_last_album( $_POST['wppa-album'] );
					return;
				}
				if ( ! $file['error'][$i] ) {
					$id = wppa_insert_photo( $file['tmp_name'][$i], $_POST['wppa-album'], $file['name'][$i] );
					if ( $id ) {
						$uploaded_a_file = true;
						$count++;
						wppa_backend_upload_mail( $id, $_POST['wppa-album'], $file['name'][$i] );
					}
					else {
						wppa_error_message( __( 'Error inserting photo', 'wppa' ) . ' ' . wppa_sanitize_file_name( basename( $file['name'][$i] ) ) . '.' );
						return;
					}
				}
			}
		}
	}
	
	if ( $uploaded_a_file ) { 
		wppa_update_message( $count.' '.__( 'Photos Uploaded in album nr', 'wppa' ) . ' ' . $_POST['wppa-album'] );
		wppa_set_last_album( $_POST['wppa-album'] );
    }
}

// Upload single photos 
function wppa_upload_photos() {
	global $wpdb;
	global $warning_given;

	$warning_given = false;
	$uploaded_a_file = false;
	
	$count = '0';
	foreach ( $_FILES as $file ) {
		if ( $file['tmp_name'] != '' ) {
//			$file['name'] = wppa_sanitize_file_name( $file['name'] );
			$id = wppa_insert_photo( $file['tmp_name'], $_POST['wppa-album'], $file['name'] );
			if ( $id ) {
				$uploaded_a_file = true;
				$count++;
				wppa_backend_upload_mail( $id, $_POST['wppa-album'], $file['name'] );
			}
			else {
				wppa_error_message( __( 'Error inserting photo', 'wppa' ) . ' ' . wppa_sanitize_file_name( basename( $file['name'] ) ) . '.' );
				return;
			}
		}
	}
	
	if ( $uploaded_a_file ) { 
		wppa_update_message( $count.' '.__( 'Photos Uploaded in album nr', 'wppa' ) . ' ' . $_POST['wppa-album'] );
		wppa_set_last_album( $_POST['wppa-album'] );
    }
}

// Send emails after backend upload
function wppa_backend_upload_mail( $id, $alb, $name ) {
global $wppa_opt;
		
	$owner = wppa_get_user();
	if ( $owner == 'admin' ) return;	// Admin does not send mails to himself
	
	if ( wppa_switch( 'wppa_upload_backend_notify' ) ) {
		$to = get_bloginfo( 'admin_email' );
		$subj = sprintf( __a( 'New photo uploaded: %s' ), wppa_sanitize_file_name( $name ) );
		$cont['0'] = sprintf( __a( 'User %s uploaded photo %s into album %s' ), $owner, $id, wppa_get_album_name( $alb ) );
		if ( wppa_switch( 'wppa_upload_moderate' ) && !current_user_can( 'wppa_admin' ) ) {
			$cont['1'] = __a( 'This upload requires moderation' );
			$cont['2'] = '<a href="'.get_admin_url().'admin.php?page=wppa_admin_menu&tab=pmod&photo='.$id.'" >'.__a( 'Moderate manage photo' ).'</a>';
		}
		else {
			$cont['1'] = __a( 'Details:' );
			$cont['1'] .= ' <a href="'.get_admin_url().'admin.php?page=wppa_admin_menu&tab=pmod&photo='.$id.'" >'.__a( 'Manage photo' ).'</a>';
		}
		wppa_send_mail( $to, $subj, $cont, $id );
	}
}

// Upload a zipfile
function wppa_upload_zip() {
global $target;

	$file 	= $_FILES['file_zip'];
	$name 	= wppa_sanitize_file_name( $file['name'] );
	$type 	= $file['type'];
	$error 	= $file['error'];
	$size 	= $file['size'];
	$temp 	= $file['tmp_name'];
	$target = WPPA_DEPOT_PATH.'/'.$name;
	
	copy( $temp, $target );
	
	if ( $error == '0' ) wppa_ok_message( __( 'Zipfile', 'wppa' ).' '.$name.' '.__( 'sucessfully uploaded.', 'wppa' ) );
	else wppa_error_message( __( 'Error', 'wppa' ).' '.$error.' '.__( 'during upload.', 'wppa' ) );
	
	return $error;
}

// Do the import photos
function wppa_import_photos( $delp = false, $dela = false, $delz = false, $delv = false ) {
global $wpdb;
global $warning_given;
global $wppa;
global $wppa_opt;
global $wppa_supported_video_extensions;

	$warning_given = false;
	
	// Get this users current source directory setting
	$user 			= wppa_get_user();
	$source_type 	= get_option( 'wppa_import_source_type_'.$user, 'local' );
	if ( $source_type == 'remote' ) $wppa['is_remote'] = true;
	$source 		= get_option( 'wppa_import_source_'.$user, WPPA_DEPOT );

	$depot 			= WPPA_ABSPATH . $source;	// Filesystem
	$depoturl 		= get_bloginfo( 'wpurl' ).'/'.$source;	// url

	// See what's in there
	$files = wppa_get_import_files();

	// First extract zips if our php version is ok
	$idx='0';
	$zcount = 0;
	if ( PHP_VERSION_ID >= 50207 ) {
		foreach( $files as $zipfile ) {
			if ( isset( $_POST['file-'.$idx] ) ) {
				$ext = strtolower( substr( strrchr( $zipfile, "." ), 1 ) );
				
				if ( $ext == 'zip' ) {
					$err = wppa_extract( $zipfile, $delz );
					if ( $err == '0' ) $zcount++;
				} // if ext = zip			
			} // if isset
			$idx++;
		} // foreach
	}
	
	// Now see if albums must be created
	$idx='0';
	$acount = 0;
	foreach( $files as $album ) {
		if ( isset( $_POST['file-'.$idx] ) ) {
			$ext = strtolower( substr( strrchr( $album, "." ), 1 ) );
			if ( $ext == 'amf' ) {
				$name = '';
				$desc = '';
				$aord = '0';
				$parent = '0';
				$porder = '0';
				$owner = '';
				$handle = fopen( $album, "r" );
				if ( $handle ) {
					$buffer = fgets( $handle, 4096 );
					while ( !feof( $handle ) ) {
						$tag = substr( $buffer, 0, 5 );
						$len = strlen( $buffer ) - 6;	// substract 5 for label and one for eol
						$data = substr( $buffer, 5, $len );
						switch( $tag ) {
							case 'name=':
								$name = $data;
								break;
							case 'desc=':
								$desc = wppa_txt_to_nl( $data );
								break;
							case 'aord=':
								if ( is_numeric( $data ) ) $aord = $data;
								break;
							case 'prnt=':
								if ( $data == __( '--- none ---', 'wppa' ) ) $parent = '0';
								elseif ( $data == __( '--- separate ---', 'wppa' ) ) $parent = '-1';
								else {
									$prnt = wppa_get_album_id( $data );
									if ( $prnt != '' ) {
										$parent = $prnt;
									}
									else {
										$parent = '0';
										wppa_warning_message( __( 'Unknown parent album:', 'wppa' ).' '.$data.' '.__( '--- none --- used.', 'wppa' ) );
									}
								}
								break;
							case 'pord=':
								if ( is_numeric( $data ) ) $porder = $data;
								break;
							case 'ownr=':
								$owner = $data;
								break;
						}
						$buffer = fgets( $handle, 4096 );
					} // while !foef
					fclose( $handle );
					if ( wppa_get_album_id( $name ) != '' ) {
						wppa_warning_message( 'Album already exists '.stripslashes( $name ) );
						if ( $dela ) unlink( $album );
					}
					else {
						$id = basename( $album );
						$id = substr( $id, 0, strpos( $id, '.' ) );
						$id = wppa_create_album_entry( array ( 	'id' 			=> $id, 
																'name' 			=> stripslashes( $name ), 
																'description' 	=> stripslashes( $desc ),
																'a_order' 		=> $aord, 
																'a_parent' 		=> $parent, 
																'p_order_by' 	=> $porder,
																'owner' 		=> $owner	
															 ) );

						if ( $id === false ) {
							wppa_error_message( __( 'Could not create album.', 'wppa' ) );
						}
						else {
							//$id = wppa_get_album_id( $name );
							wppa_set_last_album( $id );
							wppa_index_add( 'album', $id );
							wppa_ok_message( __( 'Album #', 'wppa' ) . ' ' . $id . ': '.stripslashes( $name ).' ' . __( 'Added.', 'wppa' ) );
							if ( $dela ) unlink( $album );
							$acount++;
							wppa_clear_cache();
							wppa_flush_treecounts( $id );
						} // album added
					} // album did not exist
				} // if handle ( file open )
			} // if its an album
		} // if isset
		$idx++;
	} // foreach file
	
	// Now the photos
	$idx 		= '0';
	$pcount 	= '0';
	$totpcount 	= '0';
	
	// find album id
	if ( isset( $_POST['cre-album'] ) ) {	// use album ngg gallery name for ngg conversion
		$album 	= wppa_get_album_id( strip_tags( $_POST['cre-album'] ) );
		if ( ! $album ) {				// the album does not exist yet, create it
			$name	= strip_tags( $_POST['cre-album'] );
			$desc 	= sprintf( __( 'This album has been converted from ngg gallery %s', 'wppa' ), $name );
			$uplim	= '0/0';	// Unlimited not to destroy the conversion process!!
			$album 	= wppa_create_album_entry( array ( 	'name' 			=> $name,
														'description' 	=> $desc,
														'upload_limit' 	=> $uplim
														 ) );
			if ( $album === false ) {
				wppa_error_message( __( 'Could not create album.', 'wppa' ).'<br/>Query = '.$query );
				wp_die( 'Sorry, cannot continue' );
			}
		}
	}
	elseif ( isset( $_POST['wppa-album'] ) ) {
		$album = $_POST['wppa-album']; 
	}
	else $album = '0';
	
	// Report starting process
	wppa_ok_message( __( 'Processing files, please wait...', 'wppa' ).' '.__( 'If the line of dots stops growing or your browser reports Ready, your server has given up. In that case: try again', 'wppa' ).' <a href="'.wppa_dbg_url( get_admin_url().'admin.php?page=wppa_import_photos' ).'">'.__( 'here.', 'wppa' ).'</a>' );
	
	// Do them all
	foreach ( array_keys( $files ) as $file_idx ) {
		$unsanitized_path_name = $files[$file_idx];
		$file = $files[$file_idx];
		if ( isset( $_POST['use-backup'] ) && is_file( $file.'_backup' ) ) {
			$file = $file.'_backup';
		}
		$file = wppa_sanitize_file_name( $file );
		if ( isset( $_POST['file-'.$idx] ) || $wppa['ajax'] ) {
			if ( $wppa['ajax'] ) $wppa['ajax_import_files'] = basename( $file );	/* */
			$ext = strtolower( substr( strrchr( $file, "." ), 1 ) );
			$ext = str_replace( '_backup', '', $ext );
			if ( $ext == 'jpg' || $ext == 'png' || $ext == 'gif' ) {
			
				// See if a metafile exists
				$meta = substr( $file, 0, strlen( $file ) - 3 ).'pmf';
				
				// find all data: name, desc, porder form metafile
				if ( is_file( $meta ) ) {
					$alb = wppa_get_album_id( wppa_get_meta_album( $meta ) );
					$name = wppa_get_meta_name( $meta );
					$desc = wppa_txt_to_nl( wppa_get_meta_desc( $meta ) );
					$porder = wppa_get_meta_porder( $meta );
					$linkurl = wppa_get_meta_linkurl( $meta );
					$linktitle = wppa_get_meta_linktitle( $meta );
				}
				else {
					$alb = $album;	// default album
					$name = '';		// default name
					$desc = '';		// default description
					$porder = '0';	// default p_order
					$linkurl = '';
					$linktitle = '';
				}
				
				// Update the photo ?
				if ( isset( $_POST['wppa-update'] ) ) { 
					$iret = wppa_update_photo_files( $unsanitized_path_name, $name );
					if ( $iret ) {
						if ( $wppa['ajax'] ) $wppa['ajax_import_files_done'] = true;
						$pcount++;
						$totpcount += $iret;
						if ( $delp ) {
							unlink( $file );
						}
					}
				} 
				
				// Insert the photo
				else { 
					if ( is_numeric( $alb ) && $alb != '0' ) {
						$id = basename( $file );
						if ( wppa_switch( 'wppa_void_dups' ) && wppa_file_is_in_album( $id, $alb ) ) {
							wppa_error_message( sprintf( __( 'Photo %s already exists in album %s.', 'wppa' ), $id, $alb ) );
							$wppa['ajax_import_files_error'] = __( 'Duplicate', 'wppa' );
						}
						else {
							$id = substr( $id, 0, strpos( $id, '.' ) );
							if ( !is_numeric( $id ) || !wppa_is_id_free( 'photo', $id ) ) $id = 0;
							if ( wppa_insert_photo( $unsanitized_path_name, $alb, stripslashes( $name ), stripslashes( $desc ), $porder, $id, stripslashes( $linkurl ), stripslashes( $linktitle ) ) ) {
								if ( $wppa['ajax'] ) {
									$wppa['ajax_import_files_done'] = true;
								}
								$pcount++;
								if ( $delp ) {
									unlink( $unsanitized_path_name );
									if ( is_file( $meta ) ) unlink( $meta );
								}
							}
							else {
								$wppa['ajax_import_files_error'] = __( 'Insert err', 'wppa');
								wppa_error_message( __( 'Error inserting photo', 'wppa' ) . ' ' . basename( $file ) . '.' );
							}
						}
					}
					else {
						wppa_error_message( sprintf( __( 'Error inserting photo %s, unknown or non existent album.', 'wppa' ), basename( $file ) ) );
					} 
				} // Insert
			}
		}
		$idx++;
		if ( $source_type == 'remote' ) unset( $files[$file_idx] );
		if ( wppa_is_time_up() ) {
			wppa_error_message( sprintf( __( 'Time out. %s photos imported. Please restart this operation.', 'wppa' ), $pcount ) );
			wppa_set_last_album( $album );
			if ( $source_type == 'remote' ) update_option( 'wppa_import_source_url_found_'.$user, $files );
			return;
		}
	} // foreach $files
	if ( $source_type == 'remote' ) update_option( 'wppa_import_source_url_found_'.$user, $files );
	
	// Now the dirs to album imports
	
	$idx 		= '0';
	$dircount 	= '0';
	global $photocount;
	$photocount = '0';
	$iret 		= true;

	foreach ( $files as $file ) {
		if ( basename( $file ) != '.' &&  basename( $file ) != '..' && ( isset( $_POST['file-'.$idx] ) || isset( $_GET['continue'] ) ) ) {
			if ( is_dir( $file ) ) {
				$iret = wppa_import_dir_to_album( $file, '0' );
				if ( wppa_is_time_up() && wppa_switch( 'wppa_auto_continue' ) ) {
					$wppa['continue'] = 'continue';
				}
				$dircount++;
			}
		}
		$idx++;
		if ( $iret == false ) break;	// Time out
	}	
	
	$videocount = '0';
	$alb = isset( $_POST['wppa-video-album'] ) ? $_POST['wppa-video-album'] : '0';
	if ( $wppa['ajax'] && ! $alb ) {
		$wppa['ajax_import_files_error'] = __( 'Unknown album', 'wppa' );
	}
	else foreach ( array_keys( $files ) as $idx ) {
		$file = $files[$idx];
		if ( isset( $_POST['file-'.$idx] ) || $wppa['ajax'] ) {
			if ( $wppa['ajax'] ) $wppa['ajax_import_files'] = wppa_sanitize_file_name( basename( $file ) );	/* */
			$ext = strtolower( substr( strrchr( $file, "." ), 1 ) );
			if ( in_array( $ext, $wppa_supported_video_extensions ) ) {
				if ( is_numeric( $alb ) && $alb != '0' ) {
					$filename = wppa_strip_ext( basename( $file ) ).'.xxx';
					$id = wppa_file_is_in_album( $filename, $alb );
					if ( ! $id ) {	// Add new entry
						$id = wppa_create_photo_entry( array( 'album' => $alb, 'filename' => $filename, 'ext' => 'xxx', 'name' => wppa_strip_ext( $filename ) ) );
						wppa_flush_treecounts( $alb );
					}					
					// Add video filetype
					$newpath = wppa_strip_ext( wppa_get_photo_path( $id ) ).'.'.$ext;
					copy( $file, $newpath );
					if ( $delv ) unlink( $file );
					if ( $wppa['ajax'] ) {
						$wppa['ajax_import_files_done'] = true;
					}
					$videocount++;
				}
				else {
					wppa_error_message( sprintf( __( 'Error inserting video %s, unknown or non existent album.', 'wppa' ), basename( $file ) ) );
				}				
			}
		}
	}
	
	wppa_ok_message( __( 'Done processing files.', 'wppa' ) );
	
	if ( $pcount == '0' && $acount == '0' && $zcount == '0' && $dircount == '0' && $photocount == '0' && $videocount == '0' ) {
		wppa_error_message( __( 'No files to import.', 'wppa' ) );
	}
	else {
		$msg = '';
		if ( $zcount ) $msg .= $zcount.' '.__( 'Zipfiles extracted.', 'wppa' ).' ';
		if ( $acount ) $msg .= $acount.' '.__( 'Albums created.', 'wppa' ).' ';
		if ( $dircount ) $msg .= $dircount.' '.__( 'Directory to album imports.', 'wppa' ).' ';
		if ( $photocount ) $msg .= ' '.sprintf( __( 'With total %s photos.','wppa' ), $photocount ).' ';
		if ( $pcount ) {
			if ( isset( $_POST['wppa-update'] ) ) {
				$msg .= $pcount.' '.__( 'Photos updated', 'wppa' );
				if ( $totpcount != $pcount ) {
					$msg .= ' '.sprintf( __( 'to %s locations', 'wppa' ), $totpcount );
				}
				$msg .= '.';
			}
			else $msg .= $pcount.' '.__( 'single photos imported.', 'wppa' ).' '; 
		}
		if ( $videocount ) {
			$msg .= $videocount.' '.__( 'Videos imported.', 'wppa' );
		}
		wppa_ok_message( $msg ); 
		wppa_set_last_album( $album );
	}
}

function wppa_get_zipcount( $files ) {
	$result = 0;
	if ( $files ) {
		foreach ( $files as $file ) {
			$ext = strtolower( substr( strrchr( $file, "." ), 1 ) );
			if ( $ext == 'zip' ) $result++;
		}
	}
	return $result;
}

function wppa_get_albumcount( $files ) {
	$result = 0;
	if ( $files ) {
		foreach ( $files as $file ) {
			$ext = strtolower( substr( strrchr( $file, "." ), 1 ) );
			if ( $ext == 'amf' ) $result++;
		}
	}
	return $result;
}

function wppa_get_photocount( $files ) {
	$result = 0;
	if ( $files ) {
		foreach ( $files as $file ) {
			$ext = strtolower( substr( strrchr( $file, "." ), 1 ) );
			if ( $ext == 'jpg' || $ext == 'png' || $ext == 'gif' ) $result++;
		}
	}
	return $result;
}

function wppa_get_video_count( $files ) {
global $wppa_supported_video_extensions;

	$result = 0;
	if ( $files ) {
		foreach ( $files as $file ) {
			$ext = strtolower( wppa_get_ext( $file ) );
			if ( in_array( $ext, $wppa_supported_video_extensions ) ) $result++;
		}
	}
	return $result;
}
			
	
// Find dir is new album candidates
function wppa_get_dircount( $files ) {
	$result = 0;
	if ( $files ) {
		foreach ( $files as $file ) {
			if ( basename( $file ) == '.' ) {}
			elseif ( basename( $file ) == '..' ) {}
			elseif ( is_dir( $file ) ) $result++;
		}
	}
	return $result;
}

function wppa_get_meta_name( $file, $opt = '' ) {
	return wppa_get_meta_data( $file, 'name', $opt );
}
function wppa_get_meta_album( $file, $opt = '' ) {
	return wppa_get_meta_data( $file, 'albm', $opt );
}
function wppa_get_meta_desc( $file, $opt = '' ) {
	return wppa_get_meta_data( $file, 'desc', $opt );
}
function wppa_get_meta_porder( $file, $opt = '' ) {
	return wppa_get_meta_data( $file, 'pord', $opt );
}
function wppa_get_meta_linkurl( $file, $opt = '' ) {
	return wppa_get_meta_data( $file, 'lnku', $opt );
}
function wppa_get_meta_linktitle( $file, $opt = '' ) {
	return wppa_get_meta_data( $file, 'lnkt', $opt );
}

function wppa_get_meta_data( $file, $item, $opt ) {
	$result = '';
	$opt2 = '';
	if ( $opt == '( ' ) $opt2 = ' )';
	if ( $opt == '{' ) $opt2 = '}';
	if ( $opt == '[' ) $opt2 = ']';
	if ( is_file( $file ) ) {
		$handle = fopen( $file, "r" );
		if ( $handle ) {
			while ( ( $buffer = fgets( $handle, 4096 ) ) !== false ) {
				if ( substr( $buffer, 0, 5 ) == $item.'=' ) {
					if ( $opt == '' ) $result = substr( $buffer, 5, strlen( $buffer )-6 );
					else $result = $opt.wppa_qtrans( substr( $buffer, 5, strlen( $buffer )-6 ) ).$opt2;		// Translate for display purposes only
				}
			}
			if ( !feof( $handle ) ) {
				_e( 'Error: unexpected fgets() fail in wppa_get_meta_data().', 'wppa' );
			}
			fclose( $handle );
		}
	}
	return $result;
}


function wppa_extract( $xpath, $delz ) {
// There are two reasons that we do not allow the directory structure from the zipfile to be restored.
// 1. we may have no create dir access rights.
// 2. we can not reach the pictures as we only glob the users depot and not lower.
// We extract all files to the users depot. 
// The illegal files will be deleted there by the wppa_sanitize_files routine, 
// so there is no chance a depot/subdir/destroy.php or the like will get a chance to be created.
// dus...

	$err = '0';
	if ( ! class_exists( 'ZipArchive' ) ) {
		$err = '3';
		wppa_error_message( __( 'Class ZipArchive does not exist! Check your php configuration', 'wppa' ) );
	}
	else {
	
		// Start security fix
		$path = wppa_sanitize_file_name( $xpath );
		if ( ! file_exists( $xpath ) ) {
			wppa_error_message( 'Zipfile '.$path.' does not exist.' );
//			unlink( $xpath );
			$err = '4';
			return $err;
		}
		// End security fix
		
		$ext = strtolower( substr( strrchr( $xpath, "." ), 1 ) );
		if ( $ext == 'zip' ) {
			$zip = new ZipArchive;
			if ( $zip->open( $xpath ) === true ) {
			
				$supported_file_ext = array( 'jpg', 'png', 'gif', 'JPG', 'PNG', 'GIF', 'amf', 'pmf' );
				$done = '0';
				$skip = '0';
				for( $i = 0; $i < $zip->numFiles; $i++ ){
					$stat = $zip->statIndex( $i );
					$file_ext = @ end( explode( '.', $stat['name'] ) );
					if ( in_array( $file_ext, $supported_file_ext ) ) {
						$zip->extractTo( WPPA_DEPOT_PATH, $stat['name'] );
						$done++;
					}
					else {
						wppa_error_message( sprintf( __( 'File %s is of an unsupported filetype and has been ignored during extraction.', 'wppa' ), wppa_sanitize_file_name( $stat['name'] ) ) );
						$skip++;
					}
				}
			
				$zip->close();
				wppa_ok_message( sprintf( __( 'Zipfile %s processed. %s files extracted, %s files skipped.', 'wppa' ), basename( $path ), $done, $skip ) );
				if ( $delz ) unlink( $xpath );
			} else {
				wppa_error_message( __( 'Failed to extract', 'wppa' ).' '.$path );
				$err = '1';
			}
		}
		else $err = '2';
	}
	return $err;
}

function wppa_import_dir_to_album( $file, $parent ) {
global $photocount;
global $wpdb;
global $wppa_opt;
	
	// see if album exists
	if ( is_dir( $file ) ) {
		$alb = wppa_get_album_id( basename( $file ) );
		if ( !$alb ) {	// Album must be created
			$name	= basename( $file );
			$uplim	= $wppa_opt['wppa_upload_limit_count'].'/'.$wppa_opt['wppa_upload_limit_time'];
			$alb = wppa_create_album_entry( array ( 'name' 		=> $name,
													'a_parent' 	=> $parent
													 ) );
			if ( $alb === false ) {
				wppa_error_message( __( 'Could not create album.', 'wppa' ).'<br/>Query = '.$query );
				wp_die( 'Sorry, cannot continue' );
			}
			else {
				wppa_set_last_album( $alb );
				wppa_flush_treecounts( $alb );
				wppa_index_add( 'album', $alb );
				wppa_ok_message( __( 'Album #', 'wppa' ) . ' ' . $alb . ' ( '.$name.' ) ' . __( 'Added.', 'wppa' ) );
				if ( wppa_switch( 'wppa_newpag_create' ) && $parent <= '0' ) {
				
					// Create post object
					$my_post = array( 
					  'post_title'    => $name,
					  'post_content'  => str_replace( 'w#album', $alb, $wppa_opt['wppa_newpag_content'] ),
					  'post_status'   => $wppa_opt['wppa_newpag_status'],
					  'post_type'	  => $wppa_opt['wppa_newpag_type']
					 );

					// Insert the post into the database
					$pagid = wp_insert_post( $my_post );
					if ( $pagid ) {
						wppa_ok_message( sprintf( __( 'Page <a href="%s" target="_blank" >%s</a> created.', 'wppa' ), home_url().'?page_id='.$pagid, $name ) );
						$wpdb->query( $wpdb->prepare( "UPDATE `".WPPA_ALBUMS."` SET `cover_linkpage` = %s WHERE `id` = %s", $pagid, $alb ) );
					}
					else {
						wppa_error_message( __( 'Could not create page.', 'wppa' ) );
					}
				}
			}
		}
		
		// Now import the files
		$photofiles = glob( $file.'/*' );
		if ( $photofiles ) foreach ( $photofiles as $photofile ) {
			if ( ! is_dir( $photofile ) ) {
				if ( wppa_albumphoto_exists( $alb, basename( $photofile ) ) ) {
					wppa_error_message( 'Photo '.basename( $photofile ).' already exists in album '.$alb.'. Removed.' );
					unlink( $photofile );
				}
				else {
					$bret = wppa_insert_photo( $photofile, $alb, basename( $photofile ) );
					if ( ! $bret ) return false;	// Time out
					unlink( $photofile );
					$photocount++;
				}
				if ( wppa_is_time_up( $photocount ) ) return false;
			}
		}
		
		// Now go deeper, process the subdirs
		$subdirs = glob( $file.'/*' );
		if ( $subdirs ) foreach ( $subdirs as $subdir ) {
			if ( is_dir( $subdir ) ) {
				if ( basename( $subdir ) != '.' && basename( $subdir ) != '..' ) {
					$bret = wppa_import_dir_to_album( $subdir, $alb );
					if ( ! $bret ) return false;	// Time out
				}
			}
		}
		@ rmdir( $file );	// Try to remove dir, ignore error
	}
	else {
		wppa_dbg_msg( 'Invalid file in wppa_import_dir_to_album(): '.$file );
		return false;
	}
	return true;
}