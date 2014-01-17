<?php
/* wppa-settings-autosave.php
* Package: wp-photo-album-plus
*
* manage all options
* Version 5.2.10
*
*/

function _wppa_page_options() {
global $wpdb;
global $wppa;
global $wppa_opt;
global $blog_id; 
global $wppa_status;
global $options_error;
global $wppa_api_version;
global $wp_roles;
global $wppa_table;
global $wppa_subtable;
global $wppa_revno;
global $thumb;
global $no_default;
			

	// Initialize
	wppa_set_defaults();
	$options_error = false;
	
	// Things that wppa-admin-scripts.js needs to know
	echo('<script type="text/javascript">'."\n");
	echo('/* <![CDATA[ */'."\n");
		echo("\t".'wppaImageDirectory = "'.wppa_get_imgdir().'";'."\n");
		echo("\t".'wppaAjaxUrl = "'.admin_url('admin-ajax.php').'";'."\n");
	echo("/* ]]> */\n");
	echo("</script>\n");

	$key = '';
	// Someone hit a submit button or the like?
	if ( isset($_REQUEST['wppa_settings_submit']) ) {	// Yep!
		check_admin_referer(  'wppa-nonce', 'wppa-nonce' );
		$key = $_REQUEST['wppa-key'];
		$sub = $_REQUEST['wppa-sub'];
		
		// Switch on action key
		switch ( $key ) {
							
			// Must be here
			case 'wppa_moveup':
				if ( get_option('wppa_split_namedesc') == 'yes' ) {
					$sequence = get_option('wppa_slide_order_split');
					$indices = explode(',', $sequence);
					$temp = $indices[$sub];
					$indices[$sub] = $indices[$sub - '1'];
					$indices[$sub - '1'] = $temp;
					wppa_update_option('wppa_slide_order_split', implode(',', $indices));
				}
				else {
					$sequence = get_option('wppa_slide_order');
					$indices = explode(',', $sequence);
					$temp = $indices[$sub];
					$indices[$sub] = $indices[$sub - '1'];
					$indices[$sub - '1'] = $temp;
					wppa_update_option('wppa_slide_order', implode(',', $indices));
				}
				break;
			// Should better be here
			case 'wppa_setup':
				wppa_setup(true); // Message on success or fail is in the routine
				break;
			// Must be here
			case 'wppa_backup':
				wppa_backup_settings();	// Message on success or fail is in the routine
				break;
			// Must be here
			case 'wppa_load_skin':
				$fname = get_option('wppa_skinfile');

				if ($fname == 'restore') {
					if (wppa_restore_settings(WPPA_DEPOT_PATH.'/settings.bak', 'backup')) {
						wppa_ok_message(__('Saved settings restored', 'wppa'));
					}
					else {
						wppa_error_message(__('Unable to restore saved settings', 'wppa'));
						$options_error = true;
					}
				}
				elseif ($fname == 'default' || $fname == '') {
					if (wppa_set_defaults(true)) {						
						wppa_ok_message(__('Reset to default settings', 'wppa'));
					}
					else {
						wppa_error_message(__('Unable to set defaults', 'wppa'));
						$options_error = true;
					}
				}
				elseif (wppa_restore_settings($fname, 'skin')) {
					wppa_ok_message(sprintf(__('Skinfile %s loaded', 'wppa'), basename($fname)));
				}
				else {
					// Error printed by wppa_restore_settings()
				}
				break;
			// kan naar ajax
			case 'wppa_cleanup':
				wppa_cleanup_photos('0');
				break;
			// Must be here
			case 'wppa_watermark_upload':
				if ( isset($_FILES['file_1']) && $_FILES['file_1']['error'] != 4 ) { // Expected a fileupload for a watermark
					$file = $_FILES['file_1'];
					if ( $file['error'] ) {
						wppa_error_message(sprintf(__('Upload error %s', 'wppa'), $file['error']));
					} 
					else {
						$imgsize = getimagesize($file['tmp_name']);
						if ( !is_array($imgsize) || !isset($imgsize[2]) || $imgsize[2] != 3 ) {
							wppa_error_message(sprintf(__('Uploaded file %s is not a .png file', 'wppa'), $file['name']).' (Type='.$file['type'].').');
						}
						else {
							copy($file['tmp_name'], WPPA_UPLOAD_PATH . '/watermarks/' . basename($file['name']));
							wppa_err_alert(sprintf(__('Upload of %s done', 'wppa'), basename($file['name'])));
						}
					}
				}
				else {
					wppa_error_message(__('No file selected or error on upload', 'wppa'));
				}
				break;

			// Se if a remake is pending
			case 'wppa_remake':
				update_option('wppa_remake_start', time());
				break;
				
			// Recuperate
			case 'wppa_recup':
				$usr = get_option('wppa_recuperating_user', wppa_get_user('login'));
				if ( $usr != wppa_get_user('login') ) {
					wppa_error_message('This action is already started by '.$usr.'. Please wait until he has finished.');
				}
				else {
					update_option('wppa_recuperating_user', $usr);
					$wpdb->query( 'TRUNCATE TABLE '.WPPA_IPTC );
					delete_option('wppa_'.WPPA_IPTC.'_lastkey');
					$wpdb->query( 'TRUNCATE TABLE '.WPPA_EXIF );
					delete_option('wppa_'.WPPA_EXIF.'_lastkey');
					update_option('wppa_last_recup', '0');
				}
				break;
				
			// See if a fileconversion is pending
			case 'wppa_file_system':
				if ( get_option('wppa_file_system') == 'flat' ) update_option('wppa_file_system', 'to-tree');
				if ( get_option('wppa_file_system') == 'tree' ) update_option('wppa_file_system', 'to-flat');
				break;
				
			// Indexing actions
			case 'wppa_remake_index':
				$usr = get_option('wppa_indexing_user', wppa_get_user('login'));
				if ( $usr != wppa_get_user('login') ) {
					wppa_error_message('This action is already started by '.$usr.'. Please wait until he has finished.');
				}
				else {
					update_option('wppa_indexing_user', $usr);
					update_option('wppa_last_index_albums', '-1');
					update_option('wppa_last_index_photos', '-1');
				}
				break;

			case 'wppa_list_index':
				break;
				
			case 'wppa_append_to_photodesc':
				$start = get_option('wppa_last_append', '0');
				$photos = $wpdb->get_results("SELECT `id`, `description` FROM `".WPPA_PHOTOS."` WHERE `id` > ".$start." ORDER BY `id`", ARRAY_A);
				$value = trim($wppa_opt['wppa_append_text']);
				$count = '0';
				if ( $value && $photos ) {
					wppa_ok_message('Appending <i>'.$value.'</i> to all photo descriptions. Starting at id = '.($start+'1').'. Please wait...');
					foreach ( array_keys($photos) as $phidx ) {
						if ( ! wppa_is_time_up() ) {
							$newdesc = rtrim($photos[$phidx]['description']).' '.$value;
							$wpdb->query($wpdb->prepare("UPDATE `".WPPA_PHOTOS."` SET `description` = %s WHERE `id` = %s", $newdesc, $photos[$phidx]['id']));
							$count++;
							update_option('wppa_last_append', $photos[$phidx]['id']);
						}
						if ( wppa_is_time_up() ) break;					
					}
					if ( wppa_is_time_up($count) ) {
						wppa_error_message('Restart this operation to continue.');
					}
					else {
						wppa_ok_message('Done! Appending </strong><i>'.$value.'</i><strong> to all photodescriptions.');
						delete_option('wppa_last_append');
					}
				}
				else {
					wppa_error_message('Empty string to append or no photos to process.');
				}
				break;
				
			case 'wppa_remove_from_photodesc':
				$start = get_option('wppa_last_remove', '0');
				$photos = $wpdb->get_results("SELECT `id`, `description` FROM `".WPPA_PHOTOS."` WHERE `id` > ".$start." ORDER BY `id`", ARRAY_A);
				$value = trim($wppa_opt['wppa_remove_text']);
				$count = '0';
				if ( $value && $photos ) {
					wppa_ok_message('Removing <i>'.$value.'</i> from all photo descriptions. Starting at id = '.($start+'1').'. Please wait...');
					foreach ( array_keys($photos) as $phidx ) {
						if ( ! wppa_is_time_up() ) {
							$newdesc = rtrim(str_replace($value, '', $photos[$phidx]['description']));
							$wpdb->query($wpdb->prepare("UPDATE `".WPPA_PHOTOS."` SET `description` = %s WHERE `id` = %s", $newdesc, $photos[$phidx]['id']));
							$count++;
							update_option('wppa_last_remove', $photos[$phidx]['id']);
						}
						if ( wppa_is_time_up() ) break;					
					}
					if ( wppa_is_time_up($count) ) {
						wppa_error_message('Restart this operation to continue.');
					}
					else {
						wppa_ok_message('Done! Removing </strong><i>'.$value.'</i><strong> from all photodescriptions.');
						delete_option('wppa_last_remove');
					}
				}
				else {
					wppa_error_message('Empty string to remove or no photos to process.');
				}
				break;
				
			case 'wppa_cdn_service_update':
				update_option('wppa_cdn_service_update', 'yes');
				break;
				
			case 'wppa_delete_all_from_cloudinary':
				wppa_delete_all_from_cloudinary();
				echo 'Done! wppa_delete_all_from_cloudinary';
				break;
				
			default: wppa_error_message('Unimplemnted action key: '.$key);
		}
		
		// Make sure we are uptodate
		wppa_initialize_runtime(true);

	} // wppa-settings-submit
	
	// See if a remake is pending
	if ( get_option('wppa_remake_start', '0') != '0' ) {
	
		$msg = __('Remaking image files from the available source photo files. Please wait...<br />', 'wppa');
		$msg .= __('If the line of dots stops growing or your browser reports Ready but you did NOT get a \'READY remaking image files\' message, your server has given up. In that case: continue this action by clicking', 'wppa');
		$msg .= ' <a href="'.wppa_dbg_url(get_admin_url().'admin.php?page=wppa_options').'">'.__('here', 'wppa').'</a>';
		$max_time = ini_get('max_execution_time');	
		if ($max_time > '0') {
			$msg .= sprintf(__('<br /><br />Your server reports that the elapsed time for this operation is limited to %s seconds.', 'wppa'), $max_time);
			$msg .= __('<br />There may also be other restrictions set by the server, like cpu time limit.', 'wppa');
		}

		wppa_ok_message($msg);	// Creates element with id "wppa-ok-p"

		if ( wppa_remake_files() ) {
			?>
			<script type="text/javascript">document.getElementById("wppa-ok-p").innerHTML="<strong><?php _e('READY remaking image files.', 'wppa') ?></strong>"</script>
			<?php				
			update_option('wppa_remake_start', '0');
		}
		elseif ( wppa_switch('wppa_auto_continue') ) wppa_ok_message('Trying to continue...<script type="text/javascript">document.location=document.location</script>');
	}

	// See if a regeneration of thumbs is pending
	$start = get_option('wppa_lastthumb', '-2');
	if ( $start != '-2' ) {
		$start++; 
		$count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `' . WPPA_PHOTOS . '` WHERE `id` > %s ', $start));
		$msg = sprintf(__('Regenerating thumbnail images, starting at id=%s. To go: %s. Please wait...<br />', 'wppa'), $start, $count);
		wppa_ok_message($msg);	// Creates element with id "wppa-ok-p"

		if ( wppa_regenerate_thumbs() ) {
			wppa_ok_message(__('READY regenerating thumbnail images.', 'wppa'));			
			wppa_update_option('wppa_lastthumb', '-2');
			wppa_bump_thumb_rev();//wppa_update_option('wppa_thumb_version', get_option('wppa_thumb_version', '1') + '1');
		}
		elseif ( wppa_switch('wppa_auto_continue') ) wppa_ok_message(__('Trying to continue...<script type="text/javascript">document.location=document.location</script>', 'wppa'));
	}
	
	// See if a Recuperatie is pending
	if ( get_option('wppa_last_recup', '-2') != '-2' ) {
		$usr = get_option('wppa_recuperating_user', wppa_get_user('login'));
		if ( $usr == wppa_get_user('login') ) {	// My task
			if ( wppa_recuperate_iptc_exif() ) {
				delete_option('wppa_recuperating_user');
				wppa_ok_message('IPTC/EXIF Data Recuperated');
			}
			elseif ( wppa_switch('wppa_auto_continue') ) wppa_ok_message(__('Trying to continue...<script type="text/javascript">document.location=document.location</script>', 'wppa'));
		}
	}
	
	// See if a remake index is pending
	$a = get_option('wppa_last_index_albums', '-2');
	$p = get_option('wppa_last_index_photos', '-2');
	if ( $a != '-2' || $p != '-2' ) {
		$usr = get_option('wppa_indexing_user', wppa_get_user('login'));
		if ( $usr == wppa_get_user('login') ) {	// My task
			$start++;
			wppa_ok_message(__('Creating index. Please wait...<br />', 'wppa'));
			if ( wppa_remake_index() )  {
				wppa_ok_message('Index created');
			}
			elseif ( wppa_switch('wppa_auto_continue') ) wppa_ok_message(__('Trying to continue...<script type="text/javascript">document.location=document.location</script>', 'wppa'));
		}
	}
	elseif ( wppa_switch('wppa_indexed_search') ) {
		$indexes = $wpdb->get_var("SELECT COUNT(*) FROM `".WPPA_INDEX."` ");
		$albums  = $wpdb->get_var("SELECT COUNT(*) FROM `".WPPA_ALBUMS."` ");
		if ( $albums && ! $indexes ) {	// No index yet
			update_option('wppa_index_need_remake', 'yes');
		}
		if ( get_option('wppa_index_need_remake', 'no') == 'yes' ) {
			wppa_error_message(__('The search index database table must be rebuilt. Please run Table VIII-A8', 'wppa'));
		}
	}
	
	// See if a cloudinary upload is pending
	$need_cloud = get_option('wppa_cdn_service_update', 'no') == 'yes'; 
	global $blog_id;
	if ( $need_cloud ) { 
		switch ( wppa_cdn() ) {
			case 'cloudinary':
				if ( ! function_exists( 'wppa_upload_to_cloudinary' ) ) {
					wppa_error_message('Trying to upload to Cloudinary, but it is not configured');
					exit;
				}
				$j = '0';
				$last = get_option('wppa_last_cloud_upload', '0');
				$photos = $wpdb->get_results( "SELECT * FROM `".WPPA_PHOTOS."` WHERE `id` > ".$last." ORDER BY `id` LIMIT 1000", ARRAY_A );
				if ( empty($photos) ) {
					wppa_ok_message(__('Ready uploading to Cloudinary', 'wppa'));
					update_option('wppa_cdn_service_update', 'no');
					update_option('wppa_last_cloud_upload', '0');
					wppa_ready_on_cloudinary();
				}
				else {
					$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_PHOTOS."` WHERE `id` > %s", $last));
					wppa_update_message('Uploading to Cloudinary cloud name: '.get_option('wppa_cdn_cloud_name').'. '.$count.' images to go.');
					$present_at_cloudinary = wppa_get_present_at_cloudinary_a();

					if ( $photos ) foreach ( $photos as $photo ) {
					
						if ( ! isset( $present_at_cloudinary[$photo['id']] ) ) {
							echo '['.$photo['id'].']';
							$path = wppa_get_photo_path( $photo['id'] );
							if ( file_exists( $path ) ) {
								wppa_upload_to_cloudinary( $photo['id'] );
							}
							else {
								wppa_error_message( sprintf( __( 'Unexpected error: Photo %s does not exist!', 'wppa' ), $photo['id'] ) );
							}
							$j++;
							if ( $j % '10' == '0' ) echo '<br />';
						}
						else {
							echo '.';
						}
						
						update_option('wppa_last_cloud_upload', $photo['id']);
						$time_up = wppa_is_time_up($j);
						if ( ! $time_up ) continue;
						wppa_ok_message('Trying to continue...<script type="text/javascript">document.location=document.location</script>');
						break;
						
					}
					
					if ( $count < '1000' && ! $time_up ) {
						wppa_ok_message(__('Ready uploading to Cloudinary', 'wppa'));
						update_option('wppa_cdn_service_update', 'no');
						update_option('wppa_last_cloud_upload', '0');
						wppa_ready_on_cloudinary();
					}
				}
				break;
				
			default:
				wppa_error_message('Unimplemented CDN service configured: '.wppa_cdn());
		}
	}
	
	// Convert to new file structure
	$fs = get_option('wppa_file_system', 'flat');
	if ( $fs == 'to-tree' || $fs == 'to-flat' ) {
		$time_up = false;
		$last_converted = get_option('wppa_last_converted', '0');
		$upto = $wpdb->get_var( "SELECT `id` FROM `".WPPA_PHOTOS."` ORDER BY `id` DESC LIMIT 1" );
		$upto++;
		
		wppa_warning_message(__('Converting file structure, starting at id='.($last_converted+'1').', please wait...', 'wppa'));
		
		$j = '0';
		if ( $fs == 'to-tree' ) {
			$from = 'flat';
			$to = 'tree';
		}
		else {
			$from = 'tree';
			$to = 'flat';
		}
		
		$time_up = false;
		$id = $last_converted + '1';
		while ( $id < $upto && ! $time_up ) {
			wppa_cache_thumb( $id );
			if ( $thumb ) {
				if ( file_exists( wppa_get_photo_path( $id, $from ) ) ) {
					@ rename ( wppa_get_photo_path( $id, $from ), wppa_get_photo_path( $id, $to ) );
					$j++;
				}
				if ( file_exists( wppa_get_thumb_path( $id, $from ) ) ) {
					@ rename ( wppa_get_thumb_path( $id, $from ), wppa_get_thumb_path( $id, $to ) );
				}
				update_option('wppa_last_converted', $id);
				$time_up = wppa_is_time_up($j);
				if ( $time_up ) {
					wppa_ok_message('Trying to continue...<script type="text/javascript">document.location=document.location</script>');
				}
			}
			$id++;
		}
		if ( ! $time_up ) {
			wppa_update_option('wppa_last_converted', '0');
			wppa_update_option('wppa_file_system', $to);
			wppa_initialize_runtime();
			wppa_ok_message(__('Done! converting filestructure.', 'wppa'));
		}
	}
	// end file system conversion
	
	// Fix invalid ratings
	$iret = $wpdb->query("DELETE FROM `".WPPA_RATING."` WHERE `value` = 0");
	if ( $iret ) wppa_update_message( sprintf( __( '%s invalid ratings removed. Please run Table VIII-A5: Rerate to fix the averages.', 'wppa' ), $iret ) );
?>		
	<div class="wrap">
		<?php $iconurl = WPPA_URL.'/images/settings32.png'; ?>
		<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
			<br />
		</div>
		<h2><?php _e('WP Photo Album Plus Settings', 'wppa'); ?> <span style="color:blue;"><?php _e('Auto Save', 'wppa') ?></span></h2>
		<?php _e('Database revision:', 'wppa'); ?> <?php echo(get_option('wppa_revision', '100')) ?>. <?php _e('WP Charset:', 'wppa'); ?> <?php echo(get_bloginfo('charset')); ?>. <?php echo 'Current PHP version: ' . phpversion() ?>. <?php echo 'WPPA+ API Version: '.$wppa_api_version ?>.
		<br /><?php if ( is_multisite() ) { 
			if ( WPPA_MULTISITE_GLOBAL ) {
				_e('Multisite in singlesite mode.', 'wppa');
			}
			else {
				_e('Multisite enabled.', 'wppa');
				echo ' ';
				_e('Blogid =', 'wppa');
				echo ' '.$blog_id;
			}			
		}
		
		// Blacklist
		$blacklist_plugins = array('performance-optimization-order-styles-and-javascript/order-styles-js.php', 'wp-ultra-simple-paypal-shopping-cart/wp_ultra_simple_shopping_cart.php', 'cachify/cachify.php');
		$plugins = get_option('active_plugins');
		$matches = array_intersect($blacklist_plugins, $plugins);
		foreach ( $matches as $bad ) {
			wppa_error_message(__('Please de-activate plugin <i style="font-size:14px;">', 'wppa').substr($bad, 0, strpos($bad, '/')).__('. </i>This plugin will cause wppa+ to function not properly.', 'wppa'));
		}
		// Graylist
		$graylist_plugins = array('shortcodes-ultimate/shortcodes-ultimate.php');
		$matches = array_intersect($graylist_plugins, $plugins);
		foreach ( $matches as $bad ) {
			wppa_warning_message(__('Please note that plugin <i style="font-size:14px;">', 'wppa').substr($bad, 0, strpos($bad, '/')).__('</i> can cause wppa+ to function not properly if it is misconfigured.', 'wppa'));
		}

?>
		<!--<br /><a href="javascript:window.print();"><?php //_e('Print settings', 'wppa') ?></a><br />-->
		<a style="cursor:pointer;" id="wppa-legon" onclick="jQuery('#wppa-legenda').css('display', ''); jQuery('#wppa-legon').css('display', 'none'); return false;" ><?php _e('Show legenda', 'wppa') ?></a> 
		<div id="wppa-legenda" class="updated" style="line-height:20px; display:none" >
			<div style="float:left"><?php _e('Legenda:', 'wppa') ?></div><br />			
			<?php echo wppa_doit_button(__('Button', 'wppa')) ?><div style="float:left">&nbsp;:&nbsp;<?php _e('action that causes page reload.', 'wppa') ?></div>
			<br />
			<input type="button" onclick="if ( confirm('<?php _e('Are you sure?', 'wppa') ?>') ) return true; else return false;" class="button-secundary" style="float:left; border-radius:3px; font-size: 12px; height: 18px; margin: 0 4px; padding: 0px;" value="<?php _e('Button', 'wppa') ?>" />
			<div style="float:left">&nbsp;:&nbsp;<?php _e('action that does not cause page reload.', 'wppa') ?></div>
			<br />			
			<img src="<?php echo wppa_get_imgdir() ?>star.png" title="<?php _e('Setting unmodified', 'wppa') ?>" style="padding-left:4px; float:left; height:16px; width:16px;" /><div style="float:left">&nbsp;:&nbsp;<?php _e('Setting unmodified', 'wppa') ?></div>
			<br />
			<img src="<?php echo wppa_get_imgdir() ?>clock.png" title="<?php _e('Update in progress', 'wppa') ?>" style="padding-left:4px; float:left; height:16px; width:16px;" /><div style="float:left">&nbsp;:&nbsp;<?php _e('Update in progress', 'wppa') ?></div>
			<br />
			<img src="<?php echo wppa_get_imgdir() ?>tick.png" title="<?php _e('Setting updated', 'wppa') ?>" style="padding-left:4px; float:left; height:16px; width:16px;" /><div style="float:left">&nbsp;:&nbsp;<?php _e('Setting updated', 'wppa') ?></div>
			<br />
			<img src="<?php echo wppa_get_imgdir() ?>cross.png" title="<?php _e('Update failed', 'wppa') ?>" style="padding-left:4px; float:left; height:16px; width:16px;" /><div style="float:left">&nbsp;:&nbsp;<?php _e('Update failed', 'wppa') ?></div>
			<br />
			&nbsp;<a style="cursor:pointer;" onclick="jQuery('#wppa-legenda').css('display', 'none'); jQuery('#wppa-legon').css('display', ''); return false;" ><?php _e('Hide this', 'wppa') ?></a> 
		</div>

<?php
// Display index?
			if ( $key == 'wppa_list_index' ) {
				wppa_list_index();
			}
?>
		
		<form enctype="multipart/form-data" action="<?php echo(wppa_dbg_url(get_admin_url().'admin.php?page=wppa_options')) ?>" method="post">

			<?php wp_nonce_field('wppa-nonce', 'wppa-nonce'); ?>
			<input type="hidden" name="wppa-key" id="wppa-key" value="" />
			<input type="hidden" name="wppa-sub" id="wppa-sub" value="" />
			<?php if ( get_option('wppa_i_done') == 'done' ) { ?>
			<a class="-wppa-quick" onclick="jQuery('.wppa-quick').css('display','inline');jQuery('.-wppa-quick').css('display','none')" ><?php _e('Quick setup', 'wppa') ?></a>
			<?php } else { ?>
			<input type="button" class="-wppa-quick" onclick="jQuery('.wppa-quick').css('display','inline');jQuery('.-wppa-quick').css('display','none')" value="<?php _e('Do a quick initial setup', 'wppa') ?>" />
			<input type="button" style="display:none;" class="wppa-quick" onclick="jQuery('.-wppa-quick').css('display','inline');jQuery('.wppa-quick').css('display','none')" value="<?php _e('Close quick setup', 'wppa') ?>" />
			<?php } ?>
			
			<div class="wppa-quick" style="display:none;" >
			<?php // Table 0: Quick Setup ?>
			<?php wppa_settings_box_header(
				'0',
				__('Table O:', 'wppa').' '.__('Quick Setup:', 'wppa').' '.
				__('This table enables you to quickly do an inital setup.', 'wppa')
			); ?>
			
				<div id="wppa_table_0" style=" margin:0; padding:0; " class="inside" >
					<table class="widefat wppa-table wppa-setting-table">
						<thead style="font-weight: bold; " class="wppa_table_1">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Setting', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</thead>
						<tbody class="wppa_table_0">
							<?php 
							$no_default = true;
							
							$wppa_table = '0';
							wppa_setting_subheader( '', '1', __('To quickly setup WPPA+ please answer the following questions. You can alway change any setting later. <span style="color:#700">Click on me!</span>', 'wppa'));
							{
							$name = __('Is your theme <i>responsive</i>?', 'wppa');
							$desc = __('Responsive themes have a layout that varies with the size of the browser window.', 'wppa');
							$help = esc_js(__('WPPA+ needs to know this to automaticly adept the width of the display to the available width on the page.', 'wppa'));
							$slug = 'wppa_i_responsive';
							$opts = array('', 'yes', 'no');
							$vals = array('', 'yes', 'no');
							$html = wppa_select($slug, $opts, $vals);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Do you want to downsize photos during upload?', 'wppa');
							$desc = __('Downsizing photos make them load faster to the visitor, without loosing display quality', 'wppa');
							$help = esc_js(__('If you answer yes, the photos will be downsized to max 1024 x 768 pixels. You can change this later, if you like', 'wppa'));
							$slug = 'wppa_i_downsize';
							$opts = array('', 'yes', 'no');
							$vals = array('', 'yes', 'no');
							$html = wppa_select($slug, $opts, $vals);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
				
							$name = __('May visitors upload photos?', 'wppa');
							$desc = __('It is safe to do so, but i will have to do some settings to keep it safe!', 'wppa');
							$help = esc_js(__('If you answer yes, i will assume you want to enable logged in users to upload photos at the front-end of the website and allow them to edit their photos name and descriptions.', 'wppa'));
							$help .= '\n\n'.esc_js(__('The photos will be hold for moderation, the admin will get notified by email.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Each user will get his own album to upload to. These settings can be changed later.', 'wppa'));
							$slug = 'wppa_i_userupload';
							$opts = array('', 'yes', 'no');
							$vals = array('', 'yes', 'no');
							$html = wppa_select($slug, $opts, $vals);
							wppa_setting($slug, '3', $name, $desc, $html, $help);

							$name = __('Do you want the rating system active?', 'wppa');
							$desc = __('Enable the rating system and show the votes in the slideshow.', 'wppa');
							$help = esc_js(__('You can configure the details of the rating system later', 'wppa'));
							$slug = 'wppa_i_rating';
							$opts = array('', 'yes', 'no');
							$vals = array('', 'yes', 'no');
							$html = wppa_select($slug, $opts, $vals);
							wppa_setting($slug, '4', $name, $desc, $html, $help);

							$name = __('Do you want the comment system active?', 'wppa');
							$desc = __('Enable the comment system and show the comments in the slideshow.', 'wppa');
							$help = esc_js(__('You can configure the details of the comment system later', 'wppa'));
							$slug = 'wppa_i_comment';
							$opts = array('', 'yes', 'no');
							$vals = array('', 'yes', 'no');
							$html = wppa_select($slug, $opts, $vals);
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							
							$name = __('Do you want the social media share buttons displayed?', 'wppa');
							$desc = __('Display the social media buttons in the slideshow', 'wppa');;
							$help = esc_js(__('These buttons share the specific photo rather than the page where it is displayed on', 'wppa'));
							$slug = 'wppa_i_share';
							$opts = array('', 'yes', 'no');
							$vals = array('', 'yes', 'no');
							$html = wppa_select($slug, $opts, $vals);
							wppa_setting($slug, '6', $name, $desc, $html, $help);

							$name = __('Are you going to use IPTC data?', 'wppa');
							$desc = __('IPTC data is information you may have added in a photo manipulation program.', 'wppa');
							$help = esc_js(__('The information can be displayed in slideshows and in photo descriptions.', 'wppa'));
							$slug = 'wppa_i_iptc';
							$opts = array('', 'yes', 'no');
							$vals = array('', 'yes', 'no');
							$html = wppa_select($slug, $opts, $vals);
							wppa_setting($slug, '7', $name, $desc, $html, $help);

							$name = __('Are you going to use EXIF data?', 'wppa');
							$desc = __('EXIF data is information from the camera like model no, focal distance and aperture used.', 'wppa');
							$help = esc_js(__('The information can be displayed in slideshows and in photo descriptions.', 'wppa'));
							$slug = 'wppa_i_exif';
							$opts = array('', 'yes', 'no');
							$vals = array('', 'yes', 'no');
							$html = wppa_select($slug, $opts, $vals);
							wppa_setting($slug, '8', $name, $desc, $html, $help);
							
							$name = __('Done?', 'wppa');
							$desc = __('If you are ready answering these questions, select <b>yes</b>', 'wppa');
							$help = esc_js(__('You can change any setting later, and be more specific and add a lot of settings. For now it is enough, go create albums and upload photos!', 'wppa'));
							$slug = 'wppa_i_done';
							$opts = array('', 'yes');
							$vals = array('', 'yes');
							$closetext = esc_js(__('Thank you!. The most important settings are done now. You can refine your settings, the behaviour and appearance of WPPA+ in the Tables below.', 'wppa'));
							$postaction = 'alert(\''.$closetext.'\');setTimeout(\'document.location.reload(true)\', 1000)';
							$html = wppa_select($slug, $opts, $vals, '', '', false, $postaction);
							wppa_setting($slug, '9', $name, $desc, $html, $help);
							
							$no_default = false;
							}
							?>
						</tbody>
						<tfoot style="font-weight: bold;" class="wppa_table_1">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Setting', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</tfoot>
					</table>
				</div>	
			</div>
						
			<?php // Table 1: Sizes ?>
			<?php wppa_settings_box_header(
				'1', 
				__('Table I:', 'wppa').' '.__('Sizes:', 'wppa').' '.
				__('This table describes all the sizes and size options (except fontsizes) for the generation and display of the WPPA+ elements.', 'wppa')
			); ?>
						
				<div id="wppa_table_1" style=" margin:0; padding:0; " class="inside" >
					<table class="widefat wppa-table wppa-setting-table">
						<thead style="font-weight: bold; " class="wppa_table_1">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Setting', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</thead>
						<tbody class="wppa_table_1">
							<?php 
							$wppa_table = 'I';
							
							wppa_setting_subheader( 'A', '1', __( 'WPPA+ global system related size settings', 'wppa' ) );
							{
							$name = __('Column Width', 'wppa');
							$desc = __('The width of the main column in your theme\'s display area.', 'wppa');
							$help = esc_js(__('Enter the width of the main column in your theme\'s display area.', 'wppa'));
							$help .= '\n'.esc_js(__('You should set this value correctly to make sure the fullsize images are properly aligned horizontally.', 'wppa')); 
							$help .= '\n\n'.esc_js(__('You may enter \'auto\' for use in themes that have a floating content column.', 'wppa'));
							$help .= '\n'.esc_js(__('The use of \'auto\' is required for responsive themes.', 'wppa'));
							$slug = 'wppa_colwidth';
							$onchange = 'wppaCheckFullHalign()';
							$html = wppa_input($slug, '40px', '', __('pixels wide', 'wppa'), $onchange);
							wppa_setting($slug, '1', $name, $desc, $html, $help);

							$name = __('Resize on Upload', 'wppa');
							$desc = __('Indicate if the photos should be resized during upload.', 'wppa');
							$help = esc_js(__('If you check this item, the size of the photos will be reduced to the dimension specified in the next item during the upload/import process.', 'wppa'));
							$help .= '\n'.esc_js(__('The photos will never be stretched during upload if they are smaller.', 'wppa')); 
							$slug = 'wppa_resize_on_upload';
							$onchange = 'wppaCheckResize()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('Resize to', 'wppa');
							$desc = __('Resize photos to fit within a given area.', 'wppa');
							$help = esc_js(__('Specify the screensize for the unscaled photos.', 'wppa'));
							$help .= '\n'.esc_js(__('The use of a non-default value is particularly usefull when you make use of lightbox functionality.', 'wppa'));
							$slug = 'wppa_resize_to';
							$px = __('pixels', 'wppa');
							$options = array(__('Fit within rectangle as set in Table I-B1,2', 'wppa'), '640 x 480 '.$px, '800 x 600 '.$px, '1024 x 768 '.$px, '1200 x 900 '.$px, '1280 x 960 '.$px, '1366 x 768 '.$px, '1920 x 1080 '.$px);
							$values = array( '0', '640x480', '800x600', '1024x768', '1200x900', '1280x960', '1366x768', '1920x1080');
							$class = 're_up';
							$html = wppa_select($slug, $options, $values);
							wppa_setting('', '3', $name, $desc, $html, $help, $class);
							
							$name = __('Photocount threshold', 'wppa');
							$desc = __('Number of thumbnails in an album must exceed.', 'wppa');
							$help = esc_js(__('Photos do not show up in the album unless there are more than this number of photos in the album. This allows you to have cover photos on an album that contains only sub albums without seeing them in the list of sub albums. Usually set to 0 (always show) or 1 (for one cover photo).', 'wppa'));
							$slug = 'wppa_min_thumbs';
							$html = wppa_input($slug, '40px', '', __('photos', 'wppa'));
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							
							$name = __('Border thickness', 'wppa');
							$desc = __('Thickness of wppa+ box borders.', 'wppa');
							$help = esc_js(__('Enter the thickness for the border of the WPPA+ boxes. A number of 0 means: no border.', 'wppa'));
							$help .= '\n'.esc_js(__('WPPA+ boxes are: the navigation bars and the filmstrip.', 'wppa'));
							$slug = 'wppa_bwidth';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							
							$name = __('Border radius', 'wppa');
							$desc = __('Radius of wppa+ box borders.', 'wppa');
							$help = esc_js(__('Enter the corner radius for the border of the WPPA+ boxes. A number of 0 means: no rounded corners.', 'wppa'));
							$help .= '\n'.esc_js(__('WPPA+ boxes are: the navigation bars and the filmstrip.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Note that rounded corners are only supported by modern browsers.', 'wppa'));
							$slug = 'wppa_bradius';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							
							$name = __('Box spacing', 'wppa');
							$desc = __('Distance between wppa+ boxes.', 'wppa');
							$help = '';
							$slug = 'wppa_box_spacing';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '7', $name, $desc, $html, $help);
							
							$name = __('Related count', 'wppa');
							$desc = __('The default maximum number of related photos to find.', 'wppa');
							$help = esc_js(__('When using shortcodes like [wppa type="album" album="#related,desc,23"][/wppa], the maximum number is 23. Omitting the number gives the maximum of this setting.', 'wppa'));
							$slug = 'wppa_related_count';
							$html = wppa_input($slug, '40px', '', __('photos', 'wppa'));
							wppa_setting($slug, '8', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'B', '1', __( 'Slideshow related size settings', 'wppa' ) );
							{
							$name = __('Maximum Width', 'wppa');
							$desc = __('The maximum width photos will be displayed in slideshows.', 'wppa');
							$help = esc_js(__('Enter the largest size in pixels as how you want your photos to be displayed.', 'wppa'));
							$help .= '\n'.esc_js(__('This is usually the same as the Column Width (Table I-A1), but it may differ.', 'wppa'));
							$slug = 'wppa_fullsize';
							$onchange = 'wppaCheckFullHalign()';
							$html = wppa_input($slug, '40px', '', __('pixels wide', 'wppa'), $onchange);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Maximum Height', 'wppa');
							$desc = __('The maximum height photos will be displayed in slideshows.', 'wppa');
							$help = esc_js(__('Enter the largest size in pixels as how you want your photos to be displayed.', 'wppa'));
							$help .= '\n'.esc_js(__('This setting defines the height of the space reserved for photos in slideshows.', 'wppa'));
							$help .= '\n'.esc_js(__('If you change the width of a display by the %%size= command, this value changes proportionally to match the aspect ratio as defined by this and the previous setting.', 'wppa'));
							$slug = 'wppa_maxheight';
							$html = wppa_input($slug, '40px', '', __('pixels high', 'wppa'));
							wppa_setting($slug, '2', $name, $desc, $html, $help);

							$name = __('Stretch to fit', 'wppa');
							$desc = __('Stretch photos that are too small.', 'wppa');
							$help = esc_js(__('Images will be stretched to the Maximum Size at display time if they are smaller. Leaving unchecked is recommended. It is better to upload photos that fit well the sizes you use!', 'wppa'));
							$slug = 'wppa_enlarge';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '3', $name, $desc, $html, $help);

							$name = __('Slideshow borderwidth', 'wppa');
							$desc = __('The width of the border around slideshow images.', 'wppa');
							$help = esc_js(__('The border is made by the image background being larger than the image itsself (padding).', 'wppa'));
							$help .= '\n'.esc_js(__('Additionally there may be a one pixel outline of a different color. See Table III-A2.', 'wppa'));
							$help .= '\n\n'.esc_js(__('The number you enter here is exclusive the one pixel outline.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If you leave this entry empty, there will be no outline either.', 'wppa'));
							$slug = 'wppa_fullimage_border_width';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '4', $name, $desc, $html, $help);
						
							$name = __('Numbar Max', 'wppa');
							$desc = __('Maximum nubers to display.', 'wppa');
							$help = esc_js(__('In order to attemt to fit on one line, the numbers will be replaced by dots - except the current - when there are more than this number of photos in a slideshow.', 'wppa'));
							$slug = 'wppa_numbar_max';
							$html = wppa_input($slug, '40px', '', __('numbers', 'wppa'));
							$class = 'wppa_numbar';
							wppa_setting($slug, '5', $name, $desc, $html, $help, $class);
							
							$name = __('Share button size', 'wppa');
							$desc = __('The size of the social media icons in the Share box', 'wppa');
							$help = '';
							$slug = 'wppa_share_size';
							$opts = array('16 x 16', '32 x 32');
							$vals = array('16', '32');
							$html = wppa_select($slug, $opts, $vals);
							$class = 'wppa_share';
							wppa_setting($slug, '6', $name, $desc, $html.__('pixels', 'wppa'), $help, $class);
							
							$name = __('Mini Treshold', 'wppa');
							$desc = __('Show mini text at slideshow smaller then.', 'wppa');
							$help = esc_js(__('Display Next and Prev. as opposed to Next photo and Previous photo when the cotainer is smaller than this size.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Special use in responsive themes.', 'wppa'));
							$slug = 'wppa_mini_treshold';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '7', $name, $desc, $html, $help);
							
							$name = __('Slideshow pagesize', 'wppa');
							$desc = __('The maximum number of slides in a certain view. 0 means no pagination', 'wppa');
							$help = '';
							$slug = 'wppa_slideshow_pagesize';
							$html = wppa_input($slug, '40px', '', __('slides', 'wppa'));
							wppa_setting($slug, '8', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'C', '1', __( 'Thumbnail photos related size settings', 'wppa' ) );
							{
							$name = __('Thumbnail Size', 'wppa');
							$desc = __('The size of the thumbnail images.', 'wppa');
							$help = esc_js(__('This size applies to the width or height, whichever is the largest.', 'wppa'));
							$help .= '\n'.esc_js(__('Changing the thumbnail size may result in all thumbnails being regenerated. this may take a while.', 'wppa'));
							$slug = 'wppa_thumbsize';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							$class = 'tt_normal';
							wppa_setting($slug, '1', $name, $desc, $html, $help, $class);

							$name = __('Thumbnail Size Alt', 'wppa');
							$desc = __('The alternative size of the thumbnail images.', 'wppa');
							$help = esc_js(__('This size applies to the width or height, whichever is the largest.', 'wppa'));
							$help .= '\n'.esc_js(__('Changing the thumbnail size may result in all thumbnails being regenerated. this may take a while.', 'wppa'));
							$slug = 'wppa_thumbsize_alt';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							$class = 'tt_normal';
							wppa_setting($slug, '1a', $name, $desc, $html, $help, $class);

							$name = __('Thumbnail Aspect', 'wppa');
							$desc = __('Aspect ration of thumbnail image', 'wppa');
							$help = '';
							$slug = 'wppa_thumb_aspect';
							$options = array(
								__('--- same as fullsize ---', 'wppa'), 
								__('--- square clipped ---', 'wppa'),
								__('4:5 landscape clipped', 'wppa'),
								__('3:4 landscape clipped', 'wppa'), 
								__('2:3 landscape clipped', 'wppa'),
								__('9:16 landscape clipped', 'wppa'),
								__('1:2 landscape clipped', 'wppa'),
								__('--- square padded ---', 'wppa'),
								__('4:5 landscape padded', 'wppa'),
								__('3:4 landscape padded', 'wppa'), 
								__('2:3 landscape padded', 'wppa'),
								__('9:16 landscape padded', 'wppa'),
								__('1:2 landscape padded', 'wppa')
								);
							$values = array(
								'0:0:none', 
								'1:1:clip',
								'4:5:clip',
								'3:4:clip', 
								'2:3:clip',
								'9:16:clip',
								'1:2:clip',
								'1:1:padd',
								'4:5:padd',
								'3:4:padd', 
								'2:3:padd',
								'9:16:padd',
								'1:2:padd'
								);
							$html = wppa_select($slug, $options, $values);
							$class = 'tt_normal';
							wppa_setting($slug, '2', $name, $desc, $html, $help, $class);
							
							$name = __('Thumbframe width', 'wppa');
							$desc = __('The width of the thumbnail frame.', 'wppa');
							$help = esc_js(__('Set the width of the thumbnail frame.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Set width, height and spacing for the thumbnail frames.', 'wppa'));
							$help .= '\n'.esc_js(__('These sizes should be large enough for a thumbnail image and - optionally - the text under it.', 'wppa'));
							$slug = 'wppa_tf_width';
							$html = wppa_input($slug, '40px', '', __('pixels wide', 'wppa'));
							$class = 'tt_normal';
							wppa_setting($slug, '3', $name, $desc, $html, $help, $class);

							$name = __('Thumbframe width Alt', 'wppa');
							$desc = __('The width of the alternative thumbnail frame.', 'wppa');
							$help = esc_js(__('Set the width of the thumbnail frame.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Set width, height and spacing for the thumbnail frames.', 'wppa'));
							$help .= '\n'.esc_js(__('These sizes should be large enough for a thumbnail image and - optionally - the text under it.', 'wppa'));
							$slug = 'wppa_tf_width_alt';
							$html = wppa_input($slug, '40px', '', __('pixels wide', 'wppa'));
							$class = 'tt_normal';
							wppa_setting($slug, '3a', $name, $desc, $html, $help, $class);

							$name = __('Thumbframe height', 'wppa');
							$desc = __('The height of the thumbnail frame.', 'wppa');
							$help = esc_js(__('Set the height of the thumbnail frame.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Set width, height and spacing for the thumbnail frames.', 'wppa'));
							$help .= '\n'.esc_js(__('These sizes should be large enough for a thumbnail image and - optionally - the text under it.', 'wppa'));
							$slug = 'wppa_tf_height';
							$html = wppa_input($slug, '40px', '', __('pixels high', 'wppa'));
							$class = 'tt_normal';
							wppa_setting($slug, '4', $name, $desc, $html, $help, $class);

							$name = __('Thumbframe height Alt', 'wppa');
							$desc = __('The height of the alternative thumbnail frame.', 'wppa');
							$help = esc_js(__('Set the height of the thumbnail frame.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Set width, height and spacing for the thumbnail frames.', 'wppa'));
							$help .= '\n'.esc_js(__('These sizes should be large enough for a thumbnail image and - optionally - the text under it.', 'wppa'));
							$slug = 'wppa_tf_height_alt';
							$html = wppa_input($slug, '40px', '', __('pixels high', 'wppa'));
							$class = 'tt_normal';
							wppa_setting($slug, '4a', $name, $desc, $html, $help, $class);

							$name = __('Thumbnail spacing', 'wppa');
							$desc = __('The spacing between adjacent thumbnail frames.', 'wppa');
							$help = esc_js(__('Set the minimal spacing between the adjacent thumbnail frames', 'wppa'));
							$help .= '\n\n'.esc_js(__('Set width, height and spacing for the thumbnail frames.', 'wppa'));
							$help .= '\n'.esc_js(__('These sizes should be large enough for a thumbnail image and - optionally - the text under it.', 'wppa'));
							$slug = 'wppa_tn_margin';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							$class = 'tt_normal';
							wppa_setting($slug, '5', $name, $desc, $html, $help, $class);
							
							$name = __('Auto spacing', 'wppa');
							$desc = __('Space the thumbnail frames automatic.', 'wppa');
							$help = esc_js(__('If you check this box, the thumbnail images will be evenly distributed over the available width.', 'wppa'));
							$help .= '\n'.esc_js(__('In this case, the thumbnail spacing value (setting I-9) will be regarded as a minimum value.', 'wppa'));
							$slug = 'wppa_thumb_auto';
							$html = wppa_checkbox($slug);
							$class = 'tt_normal';
							wppa_setting($slug, '6', $name, $desc, $html, $help, $class);
							
							$name = __('Page size', 'wppa');
							$desc = __('Max number of thumbnails per page.', 'wppa');
							$help = esc_js(__('Enter the maximum number of thumbnail images per page. A value of 0 indicates no pagination.', 'wppa'));
							$slug = 'wppa_thumb_page_size';
							$html = wppa_input($slug, '40px', '', __('thumbnails', 'wppa'));
							$class = 'tt_always';
							wppa_setting($slug, '7', $name, $desc, $html, $help, $class);

							$name = __('Popup size', 'wppa');
							$desc = __('The size of the thumbnail popup images.', 'wppa');
							$help = esc_js(__('Enter the size of the popup images. This size should be larger than the thumbnail size.', 'wppa'));
							$help .= '\n'.esc_js(__('This size should also be at least the cover image size.', 'wppa'));
							$help .= '\n'.esc_js(__('Changing the popup size may result in all thumbnails being regenerated. this may take a while.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Although this setting has only visual effect if "Thumb popup" (Table IV-C8) is checked,', 'wppa'));
							$help .= ' '.esc_js(__('the value must be right as it is the physical size of the thumbnail and coverphoto images.', 'wppa'));
							$slug = 'wppa_popupsize';
							$class = 'tt_normal';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '8', $name, $desc, $html, $help, $class);
							
							$name = __('Use thumbs if fit', 'wppa');
							$desc = __('Use the thumbnail image files if they are large enough.', 'wppa');
							$help = esc_js(__('This setting speeds up page loading for small photos.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Do NOT use this when your thumbnails have a forced aspect ratio (when Table I-C2 is set to anything different from --- same as fullsize ---)', 'wppa'));
							$slug = 'wppa_use_thumbs_if_fit';
							$html = wppa_checkbox($slug); 
							wppa_setting($slug, '9', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'D', '1', __( 'Album cover related size settings', 'wppa' ) );
							{
							$name = __('Max Cover width', 'wppa');
							$desc = __('Maximum width for a album cover display.', 'wppa');
							$help = esc_js(__('Display covers in 2 or more columns if the display area is wider than the given width.', 'wppa'));
							$help .= '\n'.esc_js(__('This also applies for \'thumbnails as covers\', and will NOT apply to single items.', 'wppa'));
							$slug = 'wppa_max_cover_width';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '1', $name, $desc, $html, $help);

							$name = __('Min Text frame height', 'wppa');
							$desc = __('The minimal cover text frame height.', 'wppa');
							$help = esc_js(__('The minimal height of the description field in an album cover display.', 'wppa'));
							$help .= '\n\n'.esc_js(__('This setting enables you to give the album covers the same height provided that the cover images are equally sized.', 'wppa'));
							$slug = 'wppa_text_frame_height';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '2', $name, $desc, $html, $help);

							$name = __('Coverphoto size', 'wppa');
							$desc = __('The size of the coverphoto.', 'wppa');
							$help = esc_js(__('This size applies to the width or height, whichever is the largest.', 'wppa'));
							$help .= '\n'.esc_js(__('Changing the coverphoto size may result in all thumbnails being regenerated. this may take a while.', 'wppa'));
							$slug = 'wppa_smallsize';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('Coverphoto size multi', 'wppa');
							$desc = __('The size of coverphotos if more than one.', 'wppa');
							$help = esc_js(__('This size applies to the width or height, whichever is the largest.', 'wppa'));
							$help .= '\n'.esc_js(__('Changing the coverphoto size may result in all thumbnails being regenerated. this may take a while.', 'wppa'));
							$slug = 'wppa_smallsize_multi';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '3.1', $name, $desc, $html, $help);
							
							$name = __('Size is height', 'wppa');
							$desc = __('The size of the coverphoto is the height of it.', 'wppa');
							$help = esc_js(__('If set: the previous setting is the height, if unset: the largest of width and height.', 'wppa'));
							$help .= '\n'.esc_js(__('This setting applies for coverphoto position top or bottom only (Table IV-D3).', 'wppa'));
							$help .= '\n'.esc_js(__('This makes it easyer to make the covers of equal height.', 'wppa'));
							$slug = 'wppa_coversize_is_height';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '3.9', $name, $desc, $html, $help);
							
							$name = __('Page size', 'wppa');
							$desc = __('Max number of covers per page.', 'wppa');
							$help = esc_js(__('Enter the maximum number of album covers per page. A value of 0 indicates no pagination.', 'wppa'));
							$slug = 'wppa_album_page_size';
							$html = wppa_input($slug, '40px', '', __('covers', 'wppa'));
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'E', '1', __( 'Rating and comment related size settings', 'wppa' ) );
							{
							$name = __('Rating size', 'wppa');
							$desc = __('Select the number of voting stars.', 'wppa');
							$help = '';
							$slug = 'wppa_rating_max';
							$options = array('Standard: 5 stars', 'Extended: 10 stars', 'One button vote');
							$values = array('5', '10', '1');
							$html = wppa_select($slug, $options, $values);
							$class = 'wppa_rating_';
							wppa_setting($slug, '1', $name, $desc, $html, $help, $class);
							
							$name = __('Display precision', 'wppa');
							$desc = __('Select the desired rating display precision.', 'wppa');
							$help = '';
							$slug = 'wppa_rating_prec';
							$options = array('1 '.__('decimal places', 'wppa'), '2 '.__('decimal places', 'wppa'), '3 '.__('decimal places', 'wppa'), '4 '.__('decimal places', 'wppa'));
							$values = array('1', '2', '3', '4');
							$html = wppa_select($slug, $options, $values);
							$class = 'wppa_rating_';
							wppa_setting($slug, '2', $name, $desc, $html, $help, $class);
							
							$name = __('Avatar size', 'wppa');
							$desc = __('Size of Avatar images.', 'wppa');
							$help = esc_js(__('The size of the square avatar; must be > 0 and < 256', 'wppa'));
							$slug = 'wppa_gravatar_size';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('Rating space', 'wppa');
							$desc = __('Space between avg and my rating stars', 'wppa');
							$help = '';
							$slug = 'wppa_ratspacing';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'F', '1', __( 'Widget related size settings', 'wppa' ) );
							{
							$name = __('Widget width', 'wppa');
							$desc = __('The useable width within widgets.', 'wppa');
							$help = esc_js(__('Widget width for photo of the day, general purpose (default), slideshow (default) and upload widgets.', 'wppa'));
							$slug = 'wppa_widget_width';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '0', $name, $desc, $html, $help);
							
							$name = __('TopTen count', 'wppa');
							$desc = __('Number of photos in TopTen widget.', 'wppa');
							$help = esc_js(__('Enter the maximum number of rated photos in the TopTen widget.', 'wppa'));
							$slug = 'wppa_topten_count';
							$html = wppa_input($slug, '40px', '', __('photos', 'wppa'));
							wppa_setting($slug, '1', $name, $desc, $html, $help, 'wppa_rating');
							
							$name = __('TopTen size', 'wppa');
							$desc = __('Size of thumbnails in TopTen widget.', 'wppa');
							$help = esc_js(__('Enter the size for the mini photos in the TopTen widget.', 'wppa'));
							$help .= '\n'.esc_js(__('The size applies to the width or height, whatever is the largest.', 'wppa'));
							$help .= '\n'.esc_js(__('Recommended values: 86 for a two column and 56 for a three column display.', 'wppa'));
							$slug = 'wppa_topten_size';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '2', $name, $desc, $html, $help, 'wppa_rating');

							$name = __('Comment count', 'wppa');
							$desc = __('Number of entries in Comment widget.', 'wppa');
							$help = esc_js(__('Enter the maximum number of entries in the Comment widget.', 'wppa'));
							$slug = 'wppa_comten_count';
							$html = wppa_input($slug, '40px', '', __('entries', 'wppa'));
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('Comment size', 'wppa');
							$desc = __('Size of thumbnails in Comment widget.', 'wppa');
							$help = esc_js(__('Enter the size for the mini photos in the Comment widget.', 'wppa'));
							$help .= '\n'.esc_js(__('The size applies to the width or height, whatever is the largest.', 'wppa'));
							$help .= '\n'.esc_js(__('Recommended values: 86 for a two column and 56 for a three column display.', 'wppa'));
							$slug = 'wppa_comten_size';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '4', $name, $desc, $html, $help);

							$name = __('Thumbnail count', 'wppa');
							$desc = __('Number of photos in Thumbnail widget.', 'wppa');
							$help = esc_js(__('Enter the maximum number of rated photos in the Thumbnail widget.', 'wppa'));
							$slug = 'wppa_thumbnail_widget_count';
							$html = wppa_input($slug, '40px', '', __('photos', 'wppa'));
							wppa_setting($slug, '5', $name, $desc, $html, $help);

							$name = __('Thumbnail widget size', 'wppa');
							$desc = __('Size of thumbnails in Thumbnail widget.', 'wppa');
							$help = esc_js(__('Enter the size for the mini photos in the Thumbnail widget.', 'wppa'));
							$help .= '\n'.esc_js(__('The size applies to the width or height, whatever is the largest.', 'wppa'));
							$help .= '\n'.esc_js(__('Recommended values: 86 for a two column and 56 for a three column display.', 'wppa'));
							$slug = 'wppa_thumbnail_widget_size';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							
							$name = __('LasTen count', 'wppa');
							$desc = __('Number of photos in Last Ten widget.', 'wppa');
							$help = esc_js(__('Enter the maximum number of rated photos in the LasTen widget.', 'wppa'));
							$slug = 'wppa_lasten_count';
							$html = wppa_input($slug, '40px', '', __('photos', 'wppa'));
							wppa_setting($slug, '7', $name, $desc, $html, $help);
							
							$name = __('LasTen size', 'wppa');
							$desc = __('Size of thumbnails in Last Ten widget.', 'wppa');
							$help = esc_js(__('Enter the size for the mini photos in the LasTen widget.', 'wppa'));
							$help .= '\n'.esc_js(__('The size applies to the width or height, whatever is the largest.', 'wppa'));
							$help .= '\n'.esc_js(__('Recommended values: 86 for a two column and 56 for a three column display.', 'wppa'));
							$slug = 'wppa_lasten_size';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '8', $name, $desc, $html, $help);
							
							$name = __('Album widget count', 'wppa');
							$desc = __('Number of albums in Album widget.', 'wppa');
							$help = esc_js(__('Enter the maximum number of thumbnail photos of albums in the Album widget.', 'wppa'));
							$slug = 'wppa_album_widget_count';
							$html = wppa_input($slug, '40px', '', __('albums', 'wppa'));
							wppa_setting($slug, '9', $name, $desc, $html, $help);
							
							$name = __('Album widget size', 'wppa');
							$desc = __('Size of thumbnails in Album widget.', 'wppa');
							$help = esc_js(__('Enter the size for the mini photos in the Album widget.', 'wppa'));
							$help .= '\n'.esc_js(__('The size applies to the width or height, whatever is the largest.', 'wppa'));
							$help .= '\n'.esc_js(__('Recommended values: 86 for a two column and 56 for a three column display.', 'wppa'));
							$slug = 'wppa_album_widget_size';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '10', $name, $desc, $html, $help);
							
							$name = __('FeaTen count', 'wppa');
							$desc = __('Number of photos in Featured Ten widget.', 'wppa');
							$help = esc_js(__('Enter the maximum number of rated photos in the FeaTen widget.', 'wppa'));
							$slug = 'wppa_featen_count';
							$html = wppa_input($slug, '40px', '', __('photos', 'wppa'));
							wppa_setting($slug, '11', $name, $desc, $html, $help);
							
							$name = __('FeaTen size', 'wppa');
							$desc = __('Size of thumbnails in Featured Ten widget.', 'wppa');
							$help = esc_js(__('Enter the size for the mini photos in the FeaTen widget.', 'wppa'));
							$help .= '\n'.esc_js(__('The size applies to the width or height, whatever is the largest.', 'wppa'));
							$help .= '\n'.esc_js(__('Recommended values: 86 for a two column and 56 for a three column display.', 'wppa'));
							$slug = 'wppa_featen_size';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '12', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'G', '1', __( 'Lightbox related size settings. These settings have effect only when Table IX-J3 is set to wppa', 'wppa' ) );
							{
							$name = __('Number of text lines', 'wppa');
							$desc = __('Number of lines on the lightbox description area, exclusive the n/m line.', 'wppa');
							$help = esc_js(__('Enter a number in the range from 0 to 24 or auto', 'wppa'));
							$slug = 'wppa_ovl_txt_lines';
							$html = wppa_input($slug, '40px', '', __('lines', 'wppa'));
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Magnifier cursor size', 'wppa');
							$desc = __('Select the size of the magnifier cursor.', 'wppa');
							$help = '';
							$slug = 'wppa_magnifier';
							$options = array(__('small','wppa'), __('medium', 'wppa'), __('large', 'wppa'));
							$values  = array('magnifier-small.png', 'magnifier-medium.png', 'magnifier-large.png');
							$onchange = 'document.getElementById(\'wppa-cursor\').src=wppaImageDirectory+document.getElementById(\'wppa_magnifier\').value';
							$html = wppa_select($slug, $options, $values, $onchange);
							wppa_setting($slug, '2', $name, $desc, $html.'&nbsp;&nbsp;<img id="wppa-cursor" src="'.wppa_get_imgdir().$wppa_opt[$slug].'" />', $help);
							}
							?>
						</tbody>
						<tfoot style="font-weight: bold;" class="wppa_table_1">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Setting', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
		
			<?php // Table 2: Visibility ?>
			<?php wppa_settings_box_header(
				'2', 
				__('Table II:', 'wppa').' '.__('Visibility:', 'wppa').' '.
				__('This table describes the visibility of certain wppa+ elements.', 'wppa')
			); ?>
			
				<div id="wppa_table_2" style="display:none" >
					<table class="widefat wppa-table wppa-setting-table">
						<thead style="font-weight: bold; " class="wppa_table_2">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Setting', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</thead>
						<tbody class="wppa_table_2">
							<?php 
							$wppa_table = 'II';
							
							wppa_setting_subheader( 'A', '1', __( 'Breadcrumb related visibility settings', 'wppa' ) );
							{
							$name = __('Breadcrumb on posts', 'wppa');
							$desc = __('Show breadcrumb navigation bars.', 'wppa');
							$help = esc_js(__('Indicate whether a breadcrumb navigation should be displayed', 'wppa'));
							$slug = 'wppa_show_bread_posts';
							$onchange = 'wppaCheckBreadcrumb()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '1a', $name, $desc, $html, $help);

							$name = __('Breadcrumb on pages', 'wppa');
							$desc = __('Show breadcrumb navigation bars.', 'wppa');
							$help = esc_js(__('Indicate whether a breadcrumb navigation should be displayed', 'wppa'));
							$slug = 'wppa_show_bread_pages';
							$onchange = 'wppaCheckBreadcrumb()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '1b', $name, $desc, $html, $help);

							$name = __('Breadcrumb on search results', 'wppa');
							$desc = __('Show breadcrumb navigation bars on the search results page.', 'wppa');
							$help = esc_js(__('Indicate whether a breadcrumb navigation should be displayed above the search results.', 'wppa'));
							$slug = 'wppa_bc_on_search';
							$html = wppa_checkbox($slug);
							$class = 'wppa_bc';
							wppa_setting($slug, '2', $name, $desc, $html, $help, $class);
							
							$name = __('Breadcrumb on topten displays', 'wppa');
							$desc = __('Show breadcrumb navigation bars on topten displays.', 'wppa');
							$help = esc_js(__('Indicate whether a breadcrumb navigation should be displayed above the topten displays.', 'wppa'));
							$slug = 'wppa_bc_on_topten';
							$html = wppa_checkbox($slug);
							$class = 'wppa_bc';
							wppa_setting($slug, '3.0', $name, $desc, $html, $help, $class);
							
							$name = __('Breadcrumb on last ten displays', 'wppa');
							$desc = __('Show breadcrumb navigation bars on last ten displays.', 'wppa');
							$help = esc_js(__('Indicate whether a breadcrumb navigation should be displayed above the last ten displays.', 'wppa'));
							$slug = 'wppa_bc_on_lasten';
							$html = wppa_checkbox($slug);
							$class = 'wppa_bc';
							wppa_setting($slug, '3.1', $name, $desc, $html, $help, $class);

							$name = __('Breadcrumb on comment ten displays', 'wppa');
							$desc = __('Show breadcrumb navigation bars on comment ten displays.', 'wppa');
							$help = esc_js(__('Indicate whether a breadcrumb navigation should be displayed above the comment ten displays.', 'wppa'));
							$slug = 'wppa_bc_on_comten';
							$html = wppa_checkbox($slug);
							$class = 'wppa_bc';
							wppa_setting($slug, '3.2', $name, $desc, $html, $help, $class);

							$name = __('Breadcrumb on tag result displays', 'wppa');
							$desc = __('Show breadcrumb navigation bars on tag result displays.', 'wppa');
							$help = esc_js(__('Indicate whether a breadcrumb navigation should be displayed above the tag result displays.', 'wppa'));
							$slug = 'wppa_bc_on_tag';
							$html = wppa_checkbox($slug);
							$class = 'wppa_bc';
							wppa_setting($slug, '3.3', $name, $desc, $html, $help, $class);

							$name = __('Breadcrumb on featured ten displays', 'wppa');
							$desc = __('Show breadcrumb navigation bars on featured ten displays.', 'wppa');
							$help = esc_js(__('Indicate whether a breadcrumb navigation should be displayed above the featured ten displays.', 'wppa'));
							$slug = 'wppa_bc_on_featen';
							$html = wppa_checkbox($slug);
							$class = 'wppa_bc';
							wppa_setting($slug, '3.4', $name, $desc, $html, $help, $class);

							$name = __('Breadcrumb on related photos displays', 'wppa');
							$desc = __('Show breadcrumb navigation bars on related photos displays.', 'wppa');
							$help = esc_js(__('Indicate whether a breadcrumb navigation should be displayed above the related photos displays.', 'wppa'));
							$slug = 'wppa_bc_on_related';
							$html = wppa_checkbox($slug);
							$class = 'wppa_bc';
							wppa_setting($slug, '3.5', $name, $desc, $html, $help, $class);
							
							$name = __('Home', 'wppa');
							$desc = __('Show "Home" in breadcrumb.', 'wppa');
							$help = esc_js(__('Indicate whether the breadcrumb navigation should start with a "Home"-link', 'wppa'));
							$slug = 'wppa_show_home';
							$html = wppa_checkbox($slug);
							$class = 'wppa_bc';
							wppa_setting($slug, '4', $name, $desc, $html, $help, $class);

							$name = __('Page', 'wppa');
							$desc = __('Show the page(s) in breadcrumb.', 'wppa');
							$help = esc_js(__('Indicate whether the breadcrumb navigation should show the page(hierarchy)', 'wppa'));
							$slug = 'wppa_show_page';
							$html = wppa_checkbox($slug);
							$class = 'wppa_bc';
							wppa_setting($slug, '4.1', $name, $desc, $html, $help, $class);

							$name = __('Separator', 'wppa');
							$desc = __('Breadcrumb separator symbol.', 'wppa');
							$help = esc_js(__('Select the desired breadcrumb separator element.', 'wppa'));
							$help .= '\n'.esc_js(__('A text string may contain valid html.', 'wppa'));
							$help .= '\n'.esc_js(__('An image will be scaled automatically if you set the navigation font size.', 'wppa'));
							$slug = 'wppa_bc_separator';
							$options = array('&amp;raquo', '&amp;rsaquo', '&amp;gt', '&amp;bull', __('Text (html):', 'wppa'), __('Image (url):', 'wppa'));
							$values = array('raquo', 'rsaquo', 'gt', 'bull', 'txt', 'url');
							$onchange = 'wppaCheckBreadcrumb()';
							$html = wppa_select($slug, $options, $values, $onchange);
							$class = 'wppa_bc';
							wppa_setting($slug, '5', $name, $desc, $html, $help, $class);
							
							$name = __('Html', 'wppa');
							$desc = __('Breadcrumb separator text.', 'wppa');
							$help = esc_js(__('Enter the HTML code that produces the separator symbol you want.', 'wppa'));
							$help .= '\n'.esc_js(__('It may be as simple as \'-\' (without the quotes) or as complex as a tag like <div>..</div>.', 'wppa'));
							$slug = 'wppa_bc_txt';
							$html = wppa_input($slug, '90%', '300px');
							wppa_setting($slug, '6', $name, $desc, $html, $help, $slug);

							$name = __('Image Url', 'wppa');
							$desc = __('Full url to separator image.', 'wppa');
							$help = esc_js(__('Enter the full url to the image you want to use for the separator symbol.', 'wppa'));
							$slug = 'wppa_bc_url';
							$html = wppa_input($slug, '90%', '300px');
							wppa_setting($slug, '7', $name, $desc, $html, $help, $slug);
							
							$name = __('Pagelink position', 'wppa');
							$desc = __('The location for the pagelinks bar.', 'wppa');
							$help = '';
							$slug = 'wppa_pagelink_pos';
							$options = array(__('Top', 'wppa'), __('Bottom', 'wppa'), __('Both', 'wppa'));
							$values = array('top', 'bottom', 'both');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '8', $name, $desc, $html, $help);
							
							$name = __('Thumblink on slideshow', 'wppa');
							$desc = __('Show a thumb link on slideshow bc.', 'wppa');
							$help = esc_js(__('Show a link to thumbnail display on an breadcrumb above a slideshow', 'wppa'));
							$slug = 'wppa_bc_slide_thumblink';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '9', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'B', '1', __( 'Slideshow related visibility settings', 'wppa' ) );
							{
							$name = __('Start/stop', 'wppa');
							$desc = __('Show the Start/Stop slideshow bar.', 'wppa');
							$help = esc_js(__('If checked: display the start/stop slideshow navigation bar above the full-size images and slideshow', 'wppa'));
							$slug = 'wppa_show_startstop_navigation';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Browse bar', 'wppa');
							$desc = __('Show Browse photos bar.', 'wppa');
							$help = esc_js(__('If checked: display the preveous/next navigation bar under the full-size images and slideshow', 'wppa'));
							$slug = 'wppa_show_browse_navigation';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('Filmstrip', 'wppa');
							$desc = __('Show Filmstrip navigation bar.', 'wppa');
							$help = esc_js(__('If checked: display the filmstrip navigation bar under the full_size images and slideshow', 'wppa'));
							$slug = 'wppa_filmstrip';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('Film seam', 'wppa');
							$desc = __('Show seam between end and start of film.', 'wppa');
							$help = esc_js(__('If checked: display the wrap-around point in the filmstrip', 'wppa'));
							$slug = 'wppa_film_show_glue';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '4', $name, $desc, $html, $help);

							$name = __('Photo name', 'wppa');
							$desc = __('Display photo name.', 'wppa');
							$help = esc_js(__('If checked: display the name of the photo under the slideshow image.', 'wppa')); 
							$slug = 'wppa_show_full_name';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							
							$name = __('Add (Owner)', 'wppa');
							$desc = __('Add the uploaders display name in parenthesis to the name.', 'wppa');
							$help = '';
							$slug = 'wppa_show_full_owner';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '5.1', $name, $desc, $html, $help, $class);
							
							$name = __('Photo desc', 'wppa');
							$desc = __('Display Photo description.', 'wppa');
							$help = esc_js(__('If checked: display the description of the photo under the slideshow image.', 'wppa'));
							$slug = 'wppa_show_full_desc';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							
							$name = __('Hide when empty', 'wppa');
							$desc = __('Hide the descriptionbox when empty.', 'wppa');
							$help = '';
							$slug = 'wppa_hide_when_empty';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '6.1', $name, $desc, $html, $help, 'hide_empty');

							$name = __('Rating system', 'wppa');
							$desc = __('Enable the rating system.', 'wppa');
							$help = esc_js(__('If checked, the photo rating system will be enabled.', 'wppa'));
							$slug = 'wppa_rating_on';
							$onchange = 'wppaCheckRating()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '7', $name, $desc, $html, $help);
							
							$name = __('Comments system', 'wppa');
							$desc = __('Enable the comments system.', 'wppa');
							$help = esc_js(__('Display the comments box under the fullsize images and let users enter their comments on individual photos.', 'wppa'));
							$slug = 'wppa_show_comments';
							$onchange = 'wppaCheckComments()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '10', $name, $desc, $html, $help);
							
							$name = __('Comment Avatar default', 'wppa');
							$desc = __('Show Avatars with the comments if not --- none ---', 'wppa');
							$help = '';
							$slug = 'wppa_comment_gravatar';
							$onchange = 'wppaCheckGravatar()';
							$options = array(	__('--- none ---', 'wppa'), 
												__('mystery man', 'wppa'), 
												__('identicon', 'wppa'), 
												__('monsterid', 'wppa'), 
												__('wavatar', 'wppa'),
												__('retro', 'wppa'),
												__('--- url ---', 'wppa')
											);
							$values = array(	'none', 
												'mm', 
												'identicon', 
												'monsterid',
												'wavatar',
												'retro',
												'url'
											);
							$class = 'wppa_comment_';
							$html = wppa_select($slug, $options, $values, $onchange);
							wppa_setting($slug, '11', $name, $desc, $html, $help, $class);
							
							$name = __('Comment Avatar url', 'wppa');
							$desc = __('Comment Avatar default url.', 'wppa');
							$help = '';
							$slug = 'wppa_comment_gravatar_url';
							$class = 'wppa_grav';
							$html = wppa_input($slug, '90%', '300px');
							wppa_setting($slug, '12', $name, $desc, $html, $help, $class);
							
							$name = __('Big Browse Buttons', 'wppa');
							$desc = __('Enable invisible browsing buttons.', 'wppa');
							$help = esc_js(__('If checked, the fullsize image is covered by two invisible areas that act as browse buttons.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Make sure the Full height (Table I-B2) is properly configured to prevent these areas to overlap unwanted space.', 'wppa'));
							$help .= '\n\n'.esc_js(__('A side effect of this setting is that right clicking the image no longer enables the visitor to download the image.', 'wppa'));
							$slug = 'wppa_show_bbb';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '13', $name, $desc, $html, $help);

							$name = __('Show custom box', 'wppa');
							$desc = __('Display the custom box in the slideshow', 'wppa');
							$help = esc_js(__('You can fill the custom box with any html you like. It will not be checked, so it is your own responsability to close tags properly.', 'wppa'));
							$help .= '\n\n'.esc_js(__('The position of the box can be defined in Table IX-E.', 'wppa'));
							$slug = 'wppa_custom_on';
							$onchange = 'wppaCheckCustom()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '14', $name, $desc, $html, $help);
							
							$name = __('Custom content', 'wppa');
							$desc = __('The content (html) of the custom box.', 'wppa');
							$help = esc_js(__('You can fill the custom box with any html you like. It will not be checked, so it is your own responsability to close tags properly.', 'wppa'));
							$help .= '\n\n'.esc_js(__('The position of the box can be defined in Table IX-E.', 'wppa'));
							$slug = 'wppa_custom_content';
							$html = wppa_textarea($slug, $name);
							$class = 'wppa_custom_';
							wppa_setting(false, '15', $name, $desc, $html, $help, $class);

							$name = __('Slideshow/Number bar', 'wppa');
							$desc = __('Display the Slideshow / Number bar.', 'wppa');
							$help = esc_js(__('If checked: display the number boxes on slideshow', 'wppa'));
							$slug = 'wppa_show_slideshownumbar';
							$onchange = 'wppaCheckNumbar()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '16', $name, $desc, $html, $help);
							
							$name = __('IPTC system', 'wppa');
							$desc = __('Enable the iptc system.', 'wppa');
							$help = esc_js(__('Display the iptc box under the fullsize images.', 'wppa'));
							$slug = 'wppa_show_iptc';
							$onchange = ''; 
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '17', $name, $desc, $html, $help);

							$name = __('IPTC open', 'wppa');
							$desc = __('Display the iptc box initially opened.', 'wppa');
							$help = esc_js(__('Display the iptc box under the fullsize images initially open.', 'wppa'));
							$slug = 'wppa_show_iptc_open';
							$onchange = ''; 
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '17.1', $name, $desc, $html, $help);

							$name = __('EXIF system', 'wppa');
							$desc = __('Enable the exif system.', 'wppa');
							$help = esc_js(__('Display the exif box under the fullsize images.', 'wppa'));
							$slug = 'wppa_show_exif';
							$onchange = ''; 
							$html = wppa_checkbox($slug); 
							wppa_setting($slug, '18', $name, $desc, $html, $help);

							$name = __('EXIF open', 'wppa');
							$desc = __('Display the exif box initially opened.', 'wppa');
							$help = esc_js(__('Display the exif box under the fullsize images initially open.', 'wppa'));
							$slug = 'wppa_show_exif_open';
							$onchange = ''; 
							$html = wppa_checkbox($slug); 
							wppa_setting($slug, '18.1', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'C', '1', __( 'Social media share box related visibility settings', 'wppa' ) );
							{
							$name = __('Show Share Box', 'wppa');
							$desc = __('Display the share social media buttons box.', 'wppa');
							$help = '';
							$slug = 'wppa_share_on';
							$onchange = 'wppaCheckShares()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Hide when running', 'wppa');
							$desc = __('Hide the SM box when slideshow runs.', 'wppa');
							$help = '';
							$slug = 'wppa_share_hide_when_running';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('Show Share Box Widget', 'wppa');
							$desc = __('Display the share social media buttons box in widgets.', 'wppa');
							$help = __('This setting applies to normal slideshows in widgets, not to the slideshowwidget as that is a slideonly display.', 'wppa');
							$slug = 'wppa_share_on_widget';
							$onchange = 'wppaCheckShares()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '3', $name, $desc, $html, $help);

							$name = __('Show Share Buttons Thumbs', 'wppa');
							$desc = __('Display the share social media buttons under thumbnails.', 'wppa');
							$help = '';// __('This setting applies to normal slideshows in widgets, not to the slideshowwidget as that is a slideonly display.', 'wppa');
							$slug = 'wppa_share_on_thumbs';
							$onchange = 'wppaCheckShares()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '4', $name, $desc, $html, $help);

							$name = __('Show Share Buttons Lightbox', 'wppa');
							$desc = __('Display the share social media buttons on lightbox displays.', 'wppa');
							$help = '';// __('This setting applies to normal slideshows in widgets, not to the slideshowwidget as that is a slideonly display.', 'wppa');
							$slug = 'wppa_share_on_lightbox';
							$onchange = 'wppaCheckShares()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '5', $name, $desc, $html, $help);

							$name = __('Show Share Buttons Mphoto', 'wppa');
							$desc = __('Display the share social media buttons on mphoto displays.', 'wppa');
							$help = '';// __('This setting applies to normal slideshows in widgets, not to the slideshowwidget as that is a slideonly display.', 'wppa');
							$slug = 'wppa_share_on_mphoto';
							$onchange = 'wppaCheckShares()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '6', $name, $desc, $html, $help);

							$name = __('Show QR Code', 'wppa');
							$desc = __('Display the QR code in the share box.', 'wppa');
							$help = '';
							$slug = 'wppa_share_qr';
							$class = 'wppa_share';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '11', $name, $desc, $html, $help, $class);
							
							$name = __('Show Facebook button', 'wppa');
							$desc = __('Display the Facebook button in the share box.', 'wppa');
							$help = '';
							$slug = 'wppa_share_facebook';
							$class = 'wppa_share';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '12', $name, $desc, $html, $help, $class);

							$name = __('Show Twitter button', 'wppa');
							$desc = __('Display the Twitter button in the share box.', 'wppa');
							$help = '';
							$slug = 'wppa_share_twitter';
							$class = 'wppa_share';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '13', $name, $desc, $html, $help, $class);
							
							$name = __('Show Google+ button', 'wppa');
							$desc = __('Display the Google+ button in the share box.', 'wppa');
							$help = '';
							$slug = 'wppa_share_google';
							$class = 'wppa_share';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '14', $name, $desc, $html, $help, $class);
							
							$name = __('Show Pinterest button', 'wppa');
							$desc = __('Display the Pintrest button in the share box.', 'wppa');
							$help = '';
							$slug = 'wppa_share_pinterest';
							$class = 'wppa_share';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '15', $name, $desc, $html, $help, $class);

							$name = __('Show LinkedIn button', 'wppa');
							$desc = __('Display the LinkedIn button in the share box.', 'wppa');
							$help = '';
							$slug = 'wppa_share_linkedin';
							$class = 'wppa_share';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '16', $name, $desc, $html, $help, $class);
							
							$name = __('Show Facebook like button', 'wppa');
							$desc = __('Display the Facebook button in the share box.', 'wppa');
							$help = '';
							$slug = 'wppa_facebook_like';
							$class = 'wppa_share';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '17', $name, $desc, $html, $help, $class);
						
							$name = __('Show Facebook comment box', 'wppa');
							$desc = __('Display the Facebook comment dialog box in the share box.', 'wppa');
							$help = '';
							$slug = 'wppa_facebook_comments';
							$class = 'wppa_share';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '18', $name, $desc, $html, $help, $class);
							
							$name = __('Facebook User Id', 'wppa');
							$desc = __('Enter your facebook user id to be able to moderate comments and sends', 'wppa');
							$help = '';
							$slug = 'wppa_facebook_admin_id';
							$class = 'wppa_share';
							$html = wppa_input($slug, '200px');
							wppa_setting($slug, '19', $name, $desc, $html, $help, $class);
							
							$name = __('Facebook App Id', 'wppa');
							$desc = __('Enter your facebook app id to be able to moderate comments and sends', 'wppa');
							$help = '';
							$slug = 'wppa_facebook_app_id';
							$class = 'wppa_share';
							$html = wppa_input($slug, '200px');
							wppa_setting($slug, '20', $name, $desc, $html, $help, $class);
							
							$name = __('Facebook js SDK', 'wppa');
							$desc = __('Load Facebook js SDK', 'wppa');
							$help = esc_js(__('Uncheck this box only when there is a conflict with an other plugin that also loads the Facebook js SDK.', 'wppa'));
							$slug = 'wppa_load_facebook_sdk';
							$class = 'wppa_share';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '21', $name, $desc, $html, $help, $class);

							$name = __('Share single image', 'wppa');
							$desc = __('Share a link to a single image, not the slideshow.', 'wppa');
							$help = esc_js(__('The sharelink points to a page with a single image rather than to the page with the photo in the slideshow.', 'wppa'));
							$slug = 'wppa_share_single_image';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '99', $name, $desc, $html, $help, $class);
							}
							wppa_setting_subheader( 'D', '1', __( 'Thumbnail display related visibility settings', 'wppa' ) );
							{
							$name = __('Thumbnail name', 'wppa');
							$desc = __('Display Thubnail name.', 'wppa');
							$help = esc_js(__('Display photo name under thumbnail images.', 'wppa'));
							$slug = 'wppa_thumb_text_name';
							$html = wppa_checkbox($slug);
							$class = 'tt_normal';
							wppa_setting($slug, '1', $name, $desc, $html, $help, $class);
							
							$name = __('Add (Owner)', 'wppa');
							$desc = __('Add the uploaders display name in parenthesis to the name.', 'wppa');
							$help = '';
							$slug = 'wppa_thumb_text_owner';
							$html = wppa_checkbox($slug);
							$class = 'tt_normal';
							wppa_setting($slug, '1.1', $name, $desc, $html, $help, $class);
							
							$name = __('Thumbnail desc', 'wppa');
							$desc = __('Display Thumbnail description.', 'wppa');
							$help = esc_js(__('Display description of the photo under thumbnail images.', 'wppa'));
							$slug = 'wppa_thumb_text_desc';
							$html = wppa_checkbox($slug);
							$class = 'tt_normal';
							wppa_setting($slug, '2', $name, $desc, $html, $help, $class);
							
							$name = __('Thumbnail rating', 'wppa');
							$desc = __('Display Thumbnail Rating.', 'wppa');
							$help = esc_js(__('Display the rating of the photo under the thumbnail image.', 'wppa'));
							$slug = 'wppa_thumb_text_rating';
							$html = '<span class="wppa_rating">'.wppa_checkbox($slug).'</span>';
							$class = 'wppa_rating_ tt_normal';
							wppa_setting($slug, '3', $name, $desc, $html, $help, $class);
							
							$name = __('Thumbnail viewcount', 'wppa');
							$desc = __('Display the number of views.', 'wppa');
							$help = esc_js(__('Display the number of views under the thumbnail image.', 'wppa'));
							$slug = 'wppa_thumb_text_viewcount';
							$html = wppa_checkbox($slug);
							$class = 'tt_normal';
							wppa_setting($slug, '8', $name, $desc, $html, $help, $class);

							$name = __('Popup name', 'wppa');
							$desc = __('Display Thubnail name on popup.', 'wppa');
							$help = esc_js(__('Display photo name under thumbnail images on the popup.', 'wppa'));
							$slug = 'wppa_popup_text_name';
							$html = wppa_checkbox($slug);
							$class = 'tt_normal wppa_popup';
							wppa_setting($slug, '4', $name, $desc, $html, $help, $class);
							
							$name = __('Popup desc', 'wppa');
							$desc = __('Display Thumbnail description on popup.', 'wppa');
							$help = esc_js(__('Display description of the photo under thumbnail images on the popup.', 'wppa'));
							$slug = 'wppa_popup_text_desc';
							$html = wppa_checkbox($slug);
							$class = 'tt_normal wppa_popup';
							wppa_setting($slug, '5', $name, $desc, $html, $help, $class);
							
							$name = __('Popup desc no links', 'wppa');
							$desc = __('Strip html anchor tags from descriptions on popups', 'wppa');
							$help = esc_js(__('Use this option to prevent the display of links that cannot be activated.', 'wppa'));
							$slug = 'wppa_popup_text_desc_strip';
							$html = wppa_checkbox($slug);
							$class = 'tt_normal wppa_popup';
							wppa_setting($slug, '5.1', $name, $desc, $html, $help, $class);
							
							$name = __('Popup rating', 'wppa');
							$desc = __('Display Thumbnail Rating on popup.', 'wppa');
							$help = esc_js(__('Display the rating of the photo under the thumbnail image on the popup.', 'wppa'));
							$slug = 'wppa_popup_text_rating';
							$html = '<span class="wppa_rating">'.wppa_checkbox($slug).'</span>';
							$class = 'wppa_rating_ tt_normal wppa_popup';
							wppa_setting($slug, '6', $name, $desc, $html, $help, $class);
							
							$name = __('Popup comcount', 'wppa');
							$desc = __('Display Thumbnail Comment count on popup.', 'wppa');
							$help = esc_js(__('Display the number of comments of the photo under the thumbnail image on the popup.', 'wppa'));
							$slug = 'wppa_popup_text_ncomments';
							$html = wppa_checkbox($slug);
							$class = 'tt_normal wppa_popup';
							wppa_setting($slug, '6.1', $name, $desc, $html, $help, $class);
							
							$name = __('Show rating count', 'wppa');
							$desc = __('Display the number of votes along with average ratings.', 'wppa');
							$help = esc_js(__('If checked, the number of votes is displayed along with average rating displays on thumbnail and popup displays.', 'wppa'));
							$slug = 'wppa_show_rating_count';
							$html = wppa_checkbox($slug);
							$class = 'wppa_rating_ tt_normal';
							wppa_setting($slug, '7', $name, $desc, $html, $help, $class);

							$name = __('Show name on thumb area', 'wppa');
							$desc = __('Select if and where to display the album name on the thumbnail display.', 'wppa');
							$help = '';
							$slug = 'wppa_albname_on_thumbarea';
							$options = array(__('None', 'wppa'), __('At the top', 'wppa'), __('At the bottom', 'wppa'));
							$values = array('none', 'top', 'bottom');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '8.1', $name, $desc, $html, $help);

							$name = __('Show desc on thumb area', 'wppa');
							$desc = __('Select if and where to display the album description on the thumbnail display.', 'wppa');
							$help = '';
							$slug = 'wppa_albdesc_on_thumbarea';
							$options = array(__('None', 'wppa'), __('At the top', 'wppa'), __('At the bottom', 'wppa'));
							$values = array('none', 'top', 'bottom');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '8.2', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'E', '1', __( 'Album cover related visibility settings', 'wppa' ) );
							{
							$name = __('Covertext', 'wppa');
							$desc = __('Show the text on the album cover.', 'wppa');
							$help = esc_js(__('Display the album decription on the album cover', 'wppa'));
							$slug = 'wppa_show_cover_text';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Slideshow', 'wppa');
							$desc = __('Enable the slideshow.', 'wppa');
							$help = esc_js(__('If you do not want slideshows: uncheck this box. Browsing full size images will remain possible.', 'wppa'));
							$slug = 'wppa_enable_slideshow';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '2', $name, $desc, $html, $help);

							$name = __('Slideshow/Browse', 'wppa');
							$desc = __('Display the Slideshow / Browse photos link on album covers', 'wppa');
							$help = esc_js(__('This setting causes the Slideshow link to be displayed on the album cover.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If slideshows are disabled in item 2 in this table, you will see a browse link to fullsize images.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If you do not want the browse link either, uncheck this item.', 'wppa'));
							$slug = 'wppa_show_slideshowbrowselink';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('View ...', 'wppa');
							$desc = __('Display the View xx albums and yy photos link on album covers', 'wppa');
							$help = '';
							$slug = 'wppa_show_viewlink';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							
							$name = __('Treecount', 'wppa');
							$desc = __('Disaplay the total number of (sub)albums and photos in subalbums', 'wppa');
							$help = esc_js(__('Displays the total number of sub albums and photos in the entire album tree in parenthesis if the numbers differ from the direct content of the album.', 'wppa'));
							$slug = 'wppa_show_treecount';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							
							$name = __('Skip empty albums', 'wppa');
							$desc = __('Do not show empty albums, except for admin and owner.', 'wppa');
							$help = '';
							$slug = 'wppa_skip_empty_albums';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'F', '1', __( 'Widget related visibility settings', 'wppa' ) );
							{
							$name = __('Big Browse Buttons in widget', 'wppa');
							$desc = __('Enable invisible browsing buttons in widget slideshows.', 'wppa');
							$help = esc_js(__('If checked, the fullsize image is covered by two invisible areas that act as browse buttons.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Make sure the Full height (Table I-B2) is properly configured to prevent these areas to overlap unwanted space.', 'wppa'));
							$help .= '\n\n'.esc_js(__('A side effect of this setting is that right clicking the image no longer enables the visitor to download the image.', 'wppa'));
							$slug = 'wppa_show_bbb_widget';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '1', $name, $desc, $html, $help);

							$name = __('Album widget tooltip', 'wppa');
							$desc = __('Show the album description on hoovering thumbnail in album widget', 'wppa');
							$help = '';
							$slug = 'wppa_show_albwidget_tooltip';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'G', '1', __( 'Lightbox related settings. These settings have effect only when Table IX-A6 is set to wppa', 'wppa' ) );
							{
							$name = __('Overlay Close label text', 'wppa');
							$desc = __('The text label for the cross exit symbol.', 'wppa');
							$help = __('This text may be multilingual according to the qTranslate short tags specs.', 'wppa');
							$slug = 'wppa_ovl_close_txt';
							$html = wppa_input($slug, '200px');
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Overlay theme color', 'wppa');
							$desc = __('The color of the image border and text background.', 'wppa');
							$help = '';
							$slug = 'wppa_ovl_theme';
							$options = array(__('Black', 'wppa'), __('White', 'wppa'));
							$values = array('black', 'white');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('Overlay slide name', 'wppa');
							$desc = __('Show name if from slide.', 'wppa');
							$help = esc_js(__('Shows the photos name on a lightbox display when initiated from a slide.', 'wppa'));
							$help .= '\n\n'.esc_js(__('This setting also applies to film thumbnails if Table VI-11 is set to lightbox overlay.', 'wppa'));
							$slug = 'wppa_ovl_slide_name';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('Overlay slide desc', 'wppa');
							$desc = __('Show description if from slide.', 'wppa');
							$help = esc_js(__('Shows the photos description on a lightbox display when initiated from a slide.', 'wppa'));
							$help .= '\n\n'.esc_js(__('This setting also applies to film thumbnails if Table VI-11 is set to lightbox overlay.', 'wppa'));
							$slug = 'wppa_ovl_slide_desc';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							
							$name = __('Overlay thumb name', 'wppa');
							$desc = __('Show the photos name if from thumb.', 'wppa');
							$help = esc_js(__('Shows the name on a lightbox display when initiated from a standard thumbnail or a widget thumbnail.', 'wppa'));
							$help .= '\n\n'.esc_js(__('This setting applies to standard thumbnails, thumbnail-, comment-, topten- and lasten-widget.', 'wppa'));
							$slug = 'wppa_ovl_thumb_name';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							
							$name = __('Overlay thumb desc', 'wppa');
							$desc = __('Show description if from thumb.', 'wppa');
							$help = esc_js(__('Shows the photos description on a lightbox display when initiated from a standard thumbnail or a widget thumbnail.', 'wppa'));
							$help .= '\n\n'.esc_js(__('This setting applies to standard thumbnails, thumbnail-, comment-, topten- and lasten-widget.', 'wppa'));
							$slug = 'wppa_ovl_thumb_desc';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '6', $name, $desc, $html, $help);

							$name = __('Overlay potd name', 'wppa');
							$desc = __('Show the photos name if from photo of the day.', 'wppa');
							$help = esc_js(__('Shows the name on a lightbox display when initiated from the photo of the day.', 'wppa'));
							$slug = 'wppa_ovl_potd_name';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '7', $name, $desc, $html, $help);
							
							$name = __('Overlay potd desc', 'wppa');
							$desc = __('Show description if from from photo of the day.', 'wppa');
							$help = esc_js(__('Shows the photos description on a lightbox display when initiated from the photo of the day.', 'wppa'));
							$slug = 'wppa_ovl_potd_desc';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '8', $name, $desc, $html, $help);

							$name = __('Overlay sphoto name', 'wppa');
							$desc = __('Show the photos name if from a single photo.', 'wppa');
							$help = esc_js(__('Shows the name on a lightbox display when initiated from a single photo.', 'wppa'));
							$slug = 'wppa_ovl_sphoto_name';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '9', $name, $desc, $html, $help);
							
							$name = __('Overlay sphoto desc', 'wppa');
							$desc = __('Show description if from from a single photo.', 'wppa');
							$help = esc_js(__('Shows the photos description on a lightbox display when initiated from a single photo.', 'wppa'));
							$slug = 'wppa_ovl_sphoto_desc';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '10', $name, $desc, $html, $help);

							$name = __('Overlay mphoto name', 'wppa');
							$desc = __('Show the photos name if from a single media style photo.', 'wppa');
							$help = esc_js(__('Shows the name on a lightbox display when initiated from a single media style photo.', 'wppa'));
							$slug = 'wppa_ovl_mphoto_name';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '11', $name, $desc, $html, $help);
							
							$name = __('Overlay mphoto desc', 'wppa');
							$desc = __('Show description if from from a media style photo.', 'wppa');
							$help = esc_js(__('Shows the photos description on a lightbox display when initiated from a single media style photo.', 'wppa'));
							$slug = 'wppa_ovl_mphoto_desc';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '12', $name, $desc, $html, $help);
							
							$name = __('Overlay albumwidget name', 'wppa');
							$desc = __('Show the photos name if from the album widget.', 'wppa');
							$help = esc_js(__('Shows the name on a lightbox display when initiated from the album widget.', 'wppa'));
							$slug = 'wppa_ovl_alw_name';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '13', $name, $desc, $html, $help);
							
							$name = __('Overlay albumwidget desc', 'wppa');
							$desc = __('Show description if from from the album widget.', 'wppa');
							$help = esc_js(__('Shows the photos description on a lightbox display when initiated from the album widget.', 'wppa'));
							$slug = 'wppa_ovl_alw_desc';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '14', $name, $desc, $html, $help);
							
							$name = __('Overlay coverphoto name', 'wppa');
							$desc = __('Show the photos name if from the album cover.', 'wppa');
							$help = esc_js(__('Shows the name on a lightbox display when initiated from the album coverphoto.', 'wppa'));
							$slug = 'wppa_ovl_cover_name';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '15', $name, $desc, $html, $help);
							
							$name = __('Overlay coverphoto desc', 'wppa');
							$desc = __('Show description if from from the album cover.', 'wppa');
							$help = esc_js(__('Shows the photos description on a lightbox display when initiated from the album coverphoto.', 'wppa'));
							$slug = 'wppa_ovl_cover_desc';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '16', $name, $desc, $html, $help);

							$name = __('Overlay show counter', 'wppa');
							$desc = __('Show the x/y counter below the image.', 'wppa');
							$help = '';
							$slug = 'wppa_ovl_show_counter';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '90', $name, $desc, $html, $help);
							
							$name = __('Show Zoom in', 'wppa');
							$desc = __('Display tooltip "Zoom in" along with the magnifier cursor.', 'wppa');
							$help = '';
							$slug = 'wppa_show_zoomin';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '91', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'H', '1', __( 'Miscellaneous visibility settings', 'wppa' ) );
							{
							$name = __('Show Copyright', 'wppa');
							$desc = __('Show a copyright warning on frontend upload locations.', 'wppa');
							$help = '';
							$slug = 'wppa_copyright_on';
							$class = 'wppa_copyr';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '1', $name, $desc, $html, $help, $class);
							
							$name = __('Copyright notice', 'wppa');
							$desc = __('The message to be displayed.', 'wppa');
							$help = '';
							$slug = 'wppa_copyright_notice';
							$class = 'wppa_copyr';
							$html = wppa_textarea($slug, $name);
							wppa_setting($slug, '2', $name, $desc, $html, $help, $class);
							
							$name = __('Widget thumbs fontsize', 'wppa');
							$desc = __('Font size for thumbnail subtext in widgets.', 'wppa');
							$help = '';
							$slug = 'wppa_fontsize_widget_thumb';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('Arrow color', 'wppa');
							$desc = __('Left/right browsing arrow color.', 'wppa');
							$help = esc_js(__('Enter the color of the filmstrip navigation arrows.', 'wppa'));
							$slug = 'wppa_arrow_color';
							$html = wppa_input($slug, '70px', '', '');
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							}
							?>
						</tbody>
						<tfoot style="font-weight: bold;" class="wppa_table_2">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Setting', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</tfoot>
					</table>
				</div>

			<?php // Table 3: Backgrounds ?>
			<?php wppa_settings_box_header(
				'3', 
				__('Table III:', 'wppa').' '.__('Backgrounds:', 'wppa').' '.
				__('This table describes the backgrounds of wppa+ elements.', 'wppa')
			); ?>
			
				<div id="wppa_table_3" style="display:none" >
					<table class="widefat wppa-table wppa-setting-table">
						<thead style="font-weight: bold; " class="wppa_table_3">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Background color', 'wppa') ?></td>
								<td><?php _e('Sample', 'wppa') ?></td>
								<td><?php _e('Border color', 'wppa') ?></td>
								<td><?php _e('Sample', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</thead>
						<tbody class="wppa_table_3">
							<?php 
							$wppa_table = 'III';
							
							wppa_setting_subheader( 'A', '4', __('Slideshow elements backgrounds', 'wppa' ) );
							{
							$name = __('Nav', 'wppa');
							$desc = __('Navigation bars.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for navigation backgrounds and borders.', 'wppa'));
							$slug1 = 'wppa_bgcolor_nav';
							$slug2 = 'wppa_bcolor_nav';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$html = array($html1, $html2);
							wppa_setting($slug, '1', $name, $desc, $html, $help);

							$name = __('SlideImg', 'wppa');
							$desc = __('Fullsize Slideshow Photos.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for fullsize photo backgrounds and borders.', 'wppa'));
							$help .= '\n'.esc_js(__('The colors may be equal or "transparent"', 'wppa'));
							$help .= '\n'.esc_js(__('For more information about slideshow image borders see the help on Table I-B4', 'wppa'));
							$slug1 = 'wppa_bgcolor_fullimg';
							$slug2 = 'wppa_bcolor_fullimg';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$html = array($html1, $html2);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
						
							$name = __('Numbar', 'wppa');
							$desc = __('Number bar box background.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for numbar box backgrounds and borders.', 'wppa'));
							$slug1 = 'wppa_bgcolor_numbar';
							$slug2 = 'wppa_bcolor_numbar';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$class = 'wppa_numbar';
							$html = array($html1, $html2);
							wppa_setting($slug, '3', $name, $desc, $html, $help, $class);

							$name = __('Numbar active', 'wppa');
							$desc = __('Number bar active box background.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for numbar active box backgrounds and borders.', 'wppa'));
							$slug1 = 'wppa_bgcolor_numbar_active';
							$slug2 = 'wppa_bcolor_numbar_active';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$class = 'wppa_numbar';
							$html = array($html1, $html2);
							wppa_setting($slug, '4', $name, $desc, $html, $help, $class);

							$name = __('Name/desc', 'wppa');
							$desc = __('Name and Description bars.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for name and description box backgrounds and borders.', 'wppa'));
							$slug1 = 'wppa_bgcolor_namedesc';
							$slug2 = 'wppa_bcolor_namedesc';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$html = array($html1, $html2);
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							
							$name = __('Comments', 'wppa');
							$desc = __('Comment input and display areas.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for comment box backgrounds and borders.', 'wppa'));
							$slug1 = 'wppa_bgcolor_com';
							$slug2 = 'wppa_bcolor_com';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$class = 'wppa_comment_';
							$html = array($html1, $html2);
							wppa_setting($slug, '6', $name, $desc, $html, $help, $class);

							$name = __('Custom', 'wppa');
							$desc = __('Custom box background.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for custom box backgrounds and borders.', 'wppa'));
							$slug1 = 'wppa_bgcolor_cus';
							$slug2 = 'wppa_bcolor_cus';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$html = array($html1, $html2);
							wppa_setting($slug, '7', $name, $desc, $html, $help);

							$name = __('IPTC', 'wppa');
							$desc = __('IPTC display box background.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for iptc box backgrounds and borders.', 'wppa'));
							$slug1 = 'wppa_bgcolor_iptc';
							$slug2 = 'wppa_bcolor_iptc';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$html = array($html1, $html2);
							wppa_setting($slug, '8', $name, $desc, $html, $help);

							$name = __('EXIF', 'wppa');
							$desc = __('EXIF display box background.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for exif box backgrounds and borders.', 'wppa'));
							$slug1 = 'wppa_bgcolor_exif';
							$slug2 = 'wppa_bcolor_exif';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$html = array($html1, $html2);
							wppa_setting($slug, '9', $name, $desc, $html, $help);
				
							$name = __('Share', 'wppa');
							$desc = __('Share box display background.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for share box backgrounds and borders.', 'wppa'));
							$slug1 = 'wppa_bgcolor_share';
							$slug2 = 'wppa_bcolor_share';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$html = array($html1, $html2);
							wppa_setting($slug, '10', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'B', '4', __('Other backgrounds', 'wppa' ) );
							{
							$name = __('Even', 'wppa');
							$desc = __('Even background.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for even numbered backgrounds and borders of album covers and thumbnail displays \'As covers\'.', 'wppa'));
							$slug1 = 'wppa_bgcolor_even';
							$slug2 = 'wppa_bcolor_even';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$html = array($html1, $html2);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Odd', 'wppa');
							$desc = __('Odd background.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for odd numbered backgrounds and borders of album covers and thumbnail displays \'As covers\'.', 'wppa'));
							$slug1 = 'wppa_bgcolor_alt';
							$slug2 = 'wppa_bcolor_alt';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$html = array($html1, $html2);
							wppa_setting($slug, '2', $name, $desc, $html, $help);

							$name = __('Img', 'wppa');
							$desc = __('Cover Photos and popups.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for Cover photo and popup backgrounds and borders.', 'wppa'));
							$slug1 = 'wppa_bgcolor_img';
							$slug2 = 'wppa_bcolor_img';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$html = array($html1, $html2);
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('Upload', 'wppa');
							$desc = __('Upload box background.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for upload box backgrounds and borders.', 'wppa'));
							$slug1 = 'wppa_bgcolor_upload';
							$slug2 = 'wppa_bcolor_upload';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$html = array($html1, $html2);
							wppa_setting($slug, '4', $name, $desc, $html, $help);

							$name = __('Multitag', 'wppa');
							$desc = __('Multitag box background.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for multitag box backgrounds and borders.', 'wppa'));
							$slug1 = 'wppa_bgcolor_multitag';
							$slug2 = 'wppa_bcolor_multitag';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$html = array($html1, $html2);
							wppa_setting($slug, '5', $name, $desc, $html, $help);

							$name = __('Tagcloud', 'wppa');
							$desc = __('Tagcloud box background.', 'wppa');
							$help = esc_js(__('Enter valid CSS colors for tagcloud box backgrounds and borders.', 'wppa'));
							$slug1 = 'wppa_bgcolor_tagcloud';
							$slug2 = 'wppa_bcolor_tagcloud';
							$slug = array($slug1, $slug2);
							$html1 = wppa_input($slug1, '100px', '', '', "checkColor('".$slug1."')") . '</td><td>' . wppa_color_box($slug1);
							$html2 = wppa_input($slug2, '100px', '', '', "checkColor('".$slug2."')") . '</td><td>' . wppa_color_box($slug2);
							$html = array($html1, $html2);
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							}
							?>
						</tbody>
						<tfoot style="font-weight: bold;" class="wppa_table_3">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Background color', 'wppa') ?></td>
								<td><?php _e('Sample', 'wppa') ?></td>
								<td><?php _e('Border color', 'wppa') ?></td>
								<td><?php _e('Sample', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
					
			<?php // Table 4: Behaviour ?>
			<?php wppa_settings_box_header(
				'4', 
				__('Table IV:', 'wppa').' '.__('Behaviour:', 'wppa').' '.
				__('This table describes the dynamic behaviour of certain wppa+ elements.', 'wppa')
			); ?>

				<div id="wppa_table_4" style="display:none" >
					<table class="widefat wppa-table wppa-setting-table">
						<thead style="font-weight: bold; " class="wppa_table_4">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Setting', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</thead>
						<tbody class="wppa_table_4">
							<?php 
							$wppa_table = 'IV';
							
							wppa_setting_subheader( 'A', '1', __( 'System related settings', 'wppa' ) );
							{
							$name = __('Use Ajax', 'wppa');
							$desc = __('Use Ajax as much as is possible and implemented.', 'wppa');
							$help = '';
							$slug = 'wppa_allow_ajax';
							$onchange = 'wppaCheckAjax()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Ajax NON Admin', 'wppa');
							$desc = __('Frontend ajax use no admin files.', 'wppa');
							$help = esc_js(__('If you want to password protect wp-admin, check this box.', 'wppa'));
							$help .= '\n\n'.esc_js(__('In rare cases changing page content does not work when this box is checked. Verify the functionality!', 'wppa'));
							$slug = 'wppa_ajax_non_admin';
							$html = wppa_checkbox($slug);
							$class = '';
							wppa_setting($slug, '1.1', $name, $desc, $html, $help, $class);
							
							$name = __('Photo names in urls', 'wppa');
							$desc = __('Display photo names in urls, no numbers.', 'wppa');
							$help = esc_js(__('While browsing through a slideshow and Use Ajax is checked, and the browser supports history.pushState,', 'wppa'));
							$help .= ' '.esc_js(__('the photo names will be displayed in the generated urls in the browser address line.', 'wppa'));
							$help .= '\n\n'.esc_js(__('These urls are valid and can be saved for use later.', 'wppa'));
							$slug = 'wppa_use_photo_names_in_urls';
							$html = wppa_checkbox($slug);
							$class = 'wppa_allow_ajax_';
							wppa_setting($slug, '2', $name, $desc, $html, $help, $class);
							
							$name = __('Enable pretty links', 'wppa');
							$desc = __('Enable the generation and understanding of pretty links.', 'wppa');
							$help = esc_js(__('If checked, links to social media and the qr code will have "/token1/token2/" etc in stead of "&arg1=..&arg2=.." etc.', 'wppa'));
							$help .= '\n'.esc_js(__('These types of links will be interpreted and cause a redirection on entering.', 'wppa'));
							$help .= '\n'.esc_js(__('It is recommended to check this box. It shortens links dramatically and simplifies qr codes.', 'wppa'));
							$help .= '\n'.esc_js(__('However, you may encounter conflicts with themes and/or other plugins, so test it troughly!', 'wppa'));
							$help .= '\n\n'.esc_js(__('Table IV-A2 (Photo names in urls) must be UNchecked for this setting to work!', 'wppa'));
							$slug = 'wppa_use_pretty_links';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('Update addressline', 'wppa');
							$desc = __('Update the addressline after an ajax action or next slide.', 'wppa');
							$help = esc_js(__('If checked, refreshing the page will show the current content and the browsers back and forth arrows will browse the history on the page.', 'wppa'));
							$help .= '\n'.esc_js(__('If unchecked, refreshing the page will re-display the content of the original page.', 'wppa'));
							$help .= '\n\n'.esc_js(__('This will only work on browsers that support history.pushState() and therefor NOT in IE', 'wppa'));
							$warning = esc_js(__('Switching this off will affect the browsers behaviour.', 'wppa'));
							$slug = 'wppa_update_addressline';
							$html = wppa_checkbox_warn_off($slug, '', '', $warning);
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							
							$name = __('Render shortcode always', 'wppa');
							$desc = __('This will skip the check on proper initialisation.', 'wppa');
							$help = esc_js(__('This setting is required for certain themes like Gantry to prevent the display of wppa placeholders like [WPPA+ Photo display].', 'wppa'));
							$help .= '\n\n'.esc_js(__('If this check is needed, you can use shortcodes like [wppa ...] only, not scripts like %%wppa%%.', 'wppa'));
							$slug = 'wppa_render_shortcode_always';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							
							$name = __('Track viewcounts', 'wppa');
							$desc = __('Register number of views of albums and photos.', 'wppa');
							$help = '';
							$slug = 'wppa_track_viewcounts';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							
							$name = __('Auto page', 'wppa');
							$desc = __('Create a wp page for every fullsize image.', 'wppa');
							$help = '';
							$slug = 'wppa_auto_page';
							$onchange = 'wppaCheckAutoPage()';
							$warn = esc_js(__('Please reload this page after changing!', 'wppa'));
							$html = wppa_checkbox_warn($slug, $onchange, '', $warn);
							wppa_setting($slug, '7', $name, $desc, $html, $help);
							
							$name = __('Auto page display', 'wppa');
							$desc = __('The type of display on the autopage pages.', 'wppa');
							$help = '';
							$slug = 'wppa_auto_page_type';
							$opts = array(__('Single photo', 'wppa'), __('Media type photo', 'wppa'), __('In the style of a slideshow', 'wppa') );
							$vals = array('photo', 'mphoto', 'slphoto');
							$html = wppa_select($slug, $opts, $vals);
							$class = 'autopage';
							wppa_setting($slug, '7.1', $name, $desc, $html, $help, $class);
							
							$name = __('Auto page links', 'wppa');
							$desc = __('The location for the pagelinks.', 'wppa');
							$slug = 'wppa_auto_page_links';
							$opts = array(__('none', 'wppa'), __('At the top', 'wppa'), __('At the bottom', 'wppa'), __('At top and bottom', 'wppa'));
							$vals = array('none', 'top', 'bottom', 'both');
							$html = wppa_select($slug, $opts, $vals);
							$class = 'autopage';
							wppa_setting($slug, '7.2', $name, $desc, $html, $help, $class);
							}
							wppa_setting_subheader( 'B', '1', __( 'Slideshow related settings', 'wppa' ) );
							{
							$name = __('V align', 'wppa');
							$desc = __('Vertical alignment of slideshow images.', 'wppa');
							$help = esc_js(__('Specify the vertical alignment of slideshow images.', 'wppa'));
							$help .= '\n'.esc_js(__('If you select --- none ---, the photos will not be centered horizontally either.', 'wppa'));
							$slug = 'wppa_fullvalign';
							$options = array(__('--- none ---', 'wppa'), __('top', 'wppa'), __('center', 'wppa'), __('bottom', 'wppa'), __('fit', 'wppa'));
							$values = array('default', 'top', 'center', 'bottom', 'fit');
							$onchange = 'wppaCheckFullHalign()';
							$html = wppa_select($slug, $options, $values, $onchange);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('H align', 'wppa');
							$desc = __('Horizontal alignment of slideshow images.', 'wppa');
							$help = esc_js(__('Specify the horizontal alignment of slideshow images. If you specify --- none --- , no horizontal alignment will take place.', 'wppa'));
							$help .= '\n\n'.esc_js(__('This setting is only usefull when the Column Width differs from the Maximum Width.', 'wppa'));
							$help .= '\n'.esc_js(__('(Settings I-A1 and I-B1)', 'wppa'));
							$slug = 'wppa_fullhalign';
							$options = array(__('--- none ---', 'wppa'), __('left', 'wppa'), __('center', 'wppa'), __('right', 'wppa'));
							$values = array('default', 'left', 'center', 'right');
							$html = wppa_select($slug, $options, $values);
							$class = 'wppa_ha';
							wppa_setting($slug, '2', $name, $desc, $html, $help, $class);
							
							$name = __('Start', 'wppa');
							$desc = __('Start slideshow running.', 'wppa');
							$help = esc_js(__('If you select "running", the slideshow will start running immediately, if you select "still at first photo", the first photo will be displayed in browse mode.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If you select "still at first norated", the first photo that the visitor did not gave a rating will be displayed in browse mode.', 'wppa'));
							$slug = 'wppa_start_slide';
							$options = array(	__('running', 'wppa'), 
												__('still at first photo', 'wppa'), 
												__('still at first norated', 'wppa')
											);
							$values = array(	'run', 
												'still', 
												'norate'
											);
							$html = wppa_select($slug, $options, $values);
							$class = 'wppa_ss';
							wppa_setting($slug, '3', $name, $desc, $html, $help, $class);
							
							$name = __('Start slideonly', 'wppa');
							$desc = __('Start slideonly slideshow running.', 'wppa');
							$help = '';
							$slug = 'wppa_start_slideonly';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '3.1', $name, $desc, $html, $help);
													
							$name = __('Animation type', 'wppa');
							$desc = __('The way successive slides appear.', 'wppa');
							$help = esc_js(__('Select the way the old slide is to be replaced by the new one in the slideshow/browse fullsize display.', 'wppa'));
							$slug = 'wppa_animation_type';
							$options = array(	__('Fade out and in simultaneous', 'wppa'),
												__('Fade in after fade out', 'wppa'),
												__('Shift adjacent', 'wppa'),
												__('Stack on', 'wppa'),
												__('Stack off', 'wppa'),
												__('Turn over', 'wppa')
											);
							$values = array(	'fadeover',
												'fadeafter',
												'swipe',
												'stackon',
												'stackoff',
												'turnover'
										);
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							
							$name = __('Timeout', 'wppa');
							$desc = __('Slideshow timeout.', 'wppa');
							$help = esc_js(__('Select the time a single slide will be visible when the slideshow is started.', 'wppa'));
							$slug = 'wppa_slideshow_timeout';
							$options = array(__('very short (1 s.)', 'wppa'), __('short (1.5 s.)', 'wppa'), __('normal (2.5 s.)', 'wppa'), __('long (4 s.)', 'wppa'), __('very long (6 s.)', 'wppa'));
							$values = array('1000', '1500', '2500', '4000', '6000');
							$html = wppa_select($slug, $options, $values);
							$class = 'wppa_ss';
							wppa_setting($slug, '5', $name, $desc, $html, $help, $class);
							
							$name = __('Speed', 'wppa');
							$desc = __('Slideshow animation speed.', 'wppa');
							$help = esc_js(__('Specify the animation speed to be used in slideshows.', 'wppa'));
							$help .= '\n'.esc_js(__('This is the time it takes a photo to fade in or out.', 'wppa'));
							$slug = 'wppa_animation_speed';
							$options = array(__('--- off ---', 'wppa'), __('very fast (200 ms.)', 'wppa'), __('fast (400 ms.)', 'wppa'), __('normal (800 ms.)', 'wppa'),  __('slow (1.2 s.)', 'wppa'), __('very slow (2 s.)', 'wppa'), __('extremely slow (4 s.)', 'wppa'));
							$values = array('10', '200', '400', '800', '1200', '2000', '4000');
							$html = wppa_select($slug, $options, $values);
							$class = 'wppa_ss';
							wppa_setting($slug, '6', $name, $desc, $html, $help, $class);
			
							$name = __('Slide hover pause', 'wppa');
							$desc = __('Running Slideshow suspends during mouse hover.', 'wppa');
							$help = '';
							$slug = 'wppa_slide_pause';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '7', $name, $desc, $html, $help);
							
							$name = __('Slideshow wrap around', 'wppa');
							$desc = __('The slideshow wraps around the start and end', 'wppa');
							$help = '';
							$slug = 'wppa_slide_wrap';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '8', $name, $desc, $html, $help);
							
							$name = __('Full desc align', 'wppa');
							$desc = __('The alignment of the descriptions under fullsize images and slideshows.', 'wppa');
							$help = '';
							$slug = 'wppa_fulldesc_align';
							$options = array(__('Left', 'wppa'), __('Center', 'wppa'), __('Right', 'wppa'));
							$values = array('left', 'center', 'right');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '9', $name, $desc, $html, $help);
							
							$name = __('Remove redundant space', 'wppa');
							$desc = __('Removes unwanted &lt;p> and &lt;br> tags in fullsize descriptions.', 'wppa');
							$help = __('This setting has only effect when Table IX-A7 (foreign shortcodes) is checked.', 'wppa');
							$slug = 'wppa_clean_pbr';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '10', $name, $desc, $html, $help);
							
							$name = __('Run wpautop on description', 'wppa');
							$desc = __('Adds &lt;p> and &lt;br> tags in fullsize descriptions.', 'wppa');
							$help = '';
							$slug = 'wppa_run_wppautop_on_desc';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '11', $name, $desc, $html, $help);
							
							$name = __('Auto open comments', 'wppa');
							$desc = __('Automatic opens comments box when slideshow does not run.', 'wppa');
							$help = '';
							$slug = 'wppa_auto_open_comments';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '12', $name, $desc, $html, $help);
							
							$name = __('Film hover goto', 'wppa');
							$desc = __('Go to slide when hovering filmstrip thumbnail.', 'wppa');
							$help = __('Do not use this setting when slides have different aspect ratios!', 'wppa');
							$slug = 'wppa_film_hover_goto';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '13', $name, $desc, $html, $help);
							
							$name = __('Slide swipe', 'wppa');
							$desc = __('Enable touch events swipe left-right on slides on touch screens.', 'wppa');
							$help = '';
							$slug = 'wppa_slide_swipe';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '14', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'C', '1', __( 'Thumbnail related settings', 'wppa' ) );
							{
							$name = __('Photo order', 'wppa');
							$desc = __('Photo ordering sequence method.', 'wppa');
							$help = esc_js(__('Specify the way the photos should be ordered. This is the default setting. You can overrule the default sorting order on a per album basis.', 'wppa'));
							$slug = 'wppa_list_photos_by';
							$options = array(	__('--- none ---', 'wppa'), 
												__('Order #', 'wppa'), 
												__('Name', 'wppa'), 
												__('Random', 'wppa'), 
												__('Rating mean value', 'wppa'), 
												__('Number of votes', 'wppa'), 
												__('Timestamp', 'wppa'),
												__('EXIF Date', 'wppa'),
												__('Order # desc', 'wppa'), 
												__('Name desc', 'wppa'), 
												__('Rating mean value desc', 'wppa'), 
												__('Number of votes desc', 'wppa'), 
												__('Timestamp desc', 'wppa'),
												__('EXIF Date desc', 'wppa')
												);
							$values = array(	'0', 
												'1', 
												'2', 
												'3', 
												'4', 
												'6', 
												'5', 
												'7', 
												'-1', 
												'-2', 
												'-4', 
												'-6', 
												'-5', 
												'-7'
												);
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Thumbnail type', 'wppa');
							$desc = __('The way the thumbnail images are displayed.', 'wppa');
							$help = esc_js(__('You may select an altenative display method for thumbnails. Note that some of the thumbnail settings do not apply to all available display methods.', 'wppa'));
							$slug = 'wppa_thumbtype';
							$options = array(__('--- default ---', 'wppa'), __('like album covers', 'wppa'), __('--- none ---', 'wppa'));
							$values = array('default', 'ascovers', 'none');
							$onchange = 'wppaCheckThumbType()';
							$html = wppa_select($slug, $options, $values, $onchange);
							wppa_setting($slug, '3', $name, $desc, $html, $help);

							$name = __('Placement', 'wppa');
							$desc = __('Thumbnail image left or right.', 'wppa');
							$help = esc_js(__('Indicate the placement position of the thumbnailphoto you wish.', 'wppa'));
							$slug = 'wppa_thumbphoto_left';
							$options = array(__('Left', 'wppa'), __('Right', 'wppa'));
							$values = array('yes', 'no');
							$html = wppa_select($slug, $options, $values);
							$class = 'tt_ascovers';
							wppa_setting($slug, '4', $name, $desc, $html, $help, $class);

							$name = __('Vertical alignment', 'wppa');
							$desc = __('Vertical alignment of thumbnails.', 'wppa');
							$help = esc_js(__('Specify the vertical alignment of thumbnail images. Use this setting when albums contain both portrait and landscape photos.', 'wppa'));
							$help .= '\n'.esc_js(__('It is NOT recommended to use the value --- default ---; it will affect the horizontal alignment also and is meant to be used with custom css.', 'wppa'));
							$slug = 'wppa_valign';
							$options = array( __('--- default ---', 'wppa'), __('top', 'wppa'), __('center', 'wppa'), __('bottom', 'wppa'));
							$values = array('default', 'top', 'center', 'bottom');
							$html = wppa_select($slug, $options, $values);
							$class = 'tt_normal';
							wppa_setting($slug, '5', $name, $desc, $html, $help, $class);
							
							$name = __('Thumb mouseover', 'wppa');
							$desc = __('Apply thumbnail mouseover effect.', 'wppa');
							$help = esc_js(__('Check this box to use mouseover effect on thumbnail images.', 'wppa'));
							$slug = 'wppa_use_thumb_opacity';
							$onchange = 'wppaCheckUseThumbOpacity()';
							$html = wppa_checkbox($slug, $onchange);
							$class = 'tt_normal';
							wppa_setting($slug, '6', $name, $desc, $html, $help, $class);
							
							$name = __('Thumb opacity', 'wppa');
							$desc = __('Initial opacity value.', 'wppa');
							$help = esc_js(__('Enter percentage of opacity. 100% is opaque, 0% is transparant', 'wppa'));
							$slug = 'wppa_thumb_opacity';
							$html = '<span class="thumb_opacity_html">'.wppa_input($slug, '50px', '', __('%', 'wppa')).'</span>';
							$class = 'tt_normal';
							wppa_setting($slug, '7', $name, $desc, $html, $help, $class);

							$name = __('Thumb popup', 'wppa');
							$desc = __('Use popup effect on thumbnail images.', 'wppa');
							$help = esc_js(__('Thumbnails pop-up to a larger image when hovered.', 'wppa'));
							$slug = 'wppa_use_thumb_popup';
							$onchange = 'wppaCheckPopup()';
							$html = wppa_checkbox($slug, $onchange);
							$htmlerr = wppa_htmlerr('popup-lightbox');
							$class = 'tt_normal';
							wppa_setting($slug, '8', $name, $desc, $html.$htmlerr, $help, $class);
							}
							wppa_setting_subheader( 'D', '1', __( 'Album and covers related settings', 'wppa' ) );
							{
							$name = __('Album order', 'wppa');
							$desc = __('Album ordering sequence method.', 'wppa');
							$help = esc_js(__('Specify the way the albums should be ordered.', 'wppa'));
							$slug = 'wppa_list_albums_by';
							$options = array(	__('--- none ---', 'wppa'),
												__('Order #', 'wppa'), 
												__('Name', 'wppa'), 
												__('Random', 'wppa'), 
												__('Timestamp', 'wppa'),
												__('Order # desc', 'wppa'), 
												__('Name desc', 'wppa'),
												__('Timestamp desc', 'wppa'),
												);
							$values = array(	'0', 
												'1', 
												'2', 
												'3', 
												'5',
												'-1',
												'-2',
												'-5'
												);
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
/*							
							$name = __('Descending', 'wppa');
							$desc = __('Descending order.', 'wppa');
							$help = esc_js(__('If checked: largest first', 'wppa'));
							$slug = 'wppa_list_albums_desc';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
*/							
							$name = __('Placement', 'wppa');
							$desc = __('Cover image position.', 'wppa');
							$help = esc_js(__('Enter the position that you want to be used for the default album cover selected in Table IV-D6.', 'wppa'));
							$help .= '\n\n'.esc_js(__('For covertype Image Factory: left will be treated as top and right will be treted as bottom.', 'wppa'));
							$help .= '\n'.esc_js(__('For covertype Long Descriptions: top will be treated as left and bottom will be treted as right.', 'wppa'));
							$slug = 'wppa_coverphoto_pos';
							$options = array(__('Left', 'wppa'), __('Right', 'wppa'), __('Top', 'wppa'), __('Bottom', 'wppa'));
							$values = array('left', 'right', 'top', 'bottom');
							$onchange = 'wppaCheckCoverType()';
							$html = wppa_select($slug, $options, $values, $onchange);
							wppa_setting($slug, '3', $name, $desc, $html, $help);

							$name = __('Cover mouseover', 'wppa');
							$desc = __('Apply coverphoto mouseover effect.', 'wppa');
							$help = esc_js(__('Check this box to use mouseover effect on cover images.', 'wppa'));
							$slug = 'wppa_use_cover_opacity';
							$onchange = 'wppaCheckUseCoverOpacity()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '4', $name, $desc, $html, $help);

							$name = __('Cover opacity', 'wppa');
							$desc = __('Initial opacity value.', 'wppa');
							$help = esc_js(__('Enter percentage of opacity. 100% is opaque, 0% is transparant', 'wppa'));
							$slug = 'wppa_cover_opacity';
							$html = '<span class="cover_opacity_html">'.wppa_input($slug, '50px', '', __('%', 'wppa')).'</span>';
							$class = 'tt_normal';
							wppa_setting($slug, '5', $name, $desc, $html, $help, $class);
							
							$name = __('Cover type', 'wppa');
							$desc = __('Select the default cover type.', 'wppa');
							$help = esc_js(__('Types with the addition mcr are suitable for Multi Column in a Responsive theme', 'wppa'));;
							$slug = 'wppa_cover_type';
							$options = array(	__('Standard', 'wppa'), 
												__('Long Descriptions', 'wppa'), 
												__('Image Factory', 'wppa'), 
												__('Standard mcr', 'wppa'),
												__('Long Descriptions mcr', 'wppa'),
												__('Image Factory mcr', 'wppa')
											);
							$values = array(	'default', 
												'longdesc', 
												'imagefactory', 
												'default-mcr',
												'longdesc-mcr',
												'imagefactory-mcr'
											);
							$onchange = 'wppaCheckCoverType()';
							$html = wppa_select($slug, $options, $values, $onchange);
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							
							$name = __('Number of coverphotos', 'wppa');
							$desc = __('The umber of coverphotos. Must be > 1 and < 25.', 'wppa');
							$help = '';
							$slug = 'wppa_imgfact_count';
							$html = wppa_input($slug, '50px', '', __('photos', 'wppa'));
							$class = 'wppa_imgfact_';
							wppa_setting($slug, '6.1', $name, $desc, $html, $help, $class);
							}
							wppa_setting_subheader( 'E', '1', __( 'Rating related settings', 'wppa' ), 'wppa_rating_' );	
							{
							$name = __('Rating login', 'wppa');
							$desc = __('Users must login to rate photos.', 'wppa');
							$help = esc_js(__('If users want to vote for a photo (rating 1..5 stars) the must login first. The avarage rating will always be displayed as long as the rating system is enabled.', 'wppa'));
							$slug = 'wppa_rating_login';
							$html = wppa_checkbox($slug);
							$class = 'wppa_rating_';
							wppa_setting($slug, '1', $name, $desc, $html, $help, $class);
							
							$name = __('Rating change', 'wppa');
							$desc = __('Users may change their ratings.', 'wppa');
							$help = esc_js(__('Users may change their ratings.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If "One button vote" is selected in Table I-E1, this setting has no meaning', 'wppa'));
							$slug = 'wppa_rating_change';
							$html = wppa_checkbox($slug);
							$class = 'wppa_rating_';
							wppa_setting($slug, '2', $name, $desc, $html, $help, $class);
							
							$name = __('Rating multi', 'wppa');
							$desc = __('Users may give multiple votes.', 'wppa');
							$help = esc_js(__('Users may give multiple votes. (This has no effect when users may change their votes.)', 'wppa'));
							$slug = 'wppa_rating_multi';
							$html = wppa_checkbox($slug);
							$class = 'wppa_rating_';
							wppa_setting($slug, '3', $name, $desc, $html, $help, $class);
							
							$name = __('Dislike value', 'wppa');
							$desc = __('This value counts dislike rating.', 'wppa');
							$help = esc_js(__('This value will be used for a dislike rating on calculation of avarage ratings.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If "One button vote" is selected in Table I-E1, this setting has no meaning', 'wppa'));
							$slug = 'wppa_dislike_value';
							$html = wppa_input($slug, '50px', '', __('points', 'wppa'));
							$class = 'wppa_rating_';
							wppa_setting($slug, '4', $name, $desc, $html, $help, $class);
							
							$name = __('Next after vote', 'wppa');
							$desc = __('Goto next slide after voting', 'wppa');
							$help = esc_js(__('If checked, the visitor goes straight to the slide following the slide he voted. This will speed up mass voting.', 'wppa'));
							$slug = 'wppa_next_on_callback';
							$html = wppa_checkbox($slug);
							$class = 'wppa_rating_';
							wppa_setting($slug, '5', $name, $desc, $html, $help, $class);
							
							$name = __('Star off opacity', 'wppa');
							$desc = __('Rating star off state opacity value.', 'wppa');
							$help = esc_js(__('Enter percentage of opacity. 100% is opaque, 0% is transparant', 'wppa'));
							$help .= '\n\n'.esc_js(__('If "One button vote" is selected in Table I-E1, this setting has no meaning', 'wppa'));
							$slug = 'wppa_star_opacity';
							$html = wppa_input($slug, '50px', '', __('%', 'wppa'));
							$class = 'wppa_rating_';
							wppa_setting($slug, '6', $name, $desc, $html, $help, $class);
							
							$name = __('Notify inappropriate', 'wppa');
							$desc = __('Notify admin every x times.', 'wppa');
							$help = esc_js(__('If this number is positive, there will be a thumb down icon in the rating bar.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Cicking the icon indicates a user wants to report that an image is inappropiate.', 'wppa'));
							$help .= '\n'.esc_js(__('Admin will be notified by email after every x reports.', 'wppa'));
							$help .= '\n'.esc_js(__('A value of 0 disables this feature.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If "One button vote" is selected in Table I-E1, this setting has no meaning', 'wppa'));
							$slug = 'wppa_dislike_mail_every';
							$html = wppa_input($slug, '40px', '', __('reports', 'wppa'));
							$class = 'wppa_rating_';
							wppa_setting($slug, '7', $name, $desc, $html, $help, $class);
							
							$name = __('Pending after', 'wppa');
							$desc = __('Set status to pending after xx dislike votes.', 'wppa');
							$help = esc_js(__('A value of 0 disables this feature.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If "One button vote" is selected in Table I-E1, this setting has no meaning', 'wppa'));
							$slug = 'wppa_dislike_set_pending';
							$html = wppa_input($slug, '40px', '', __('reports', 'wppa'));
							$class = 'wppa_rating_';
							wppa_setting($slug, '8', $name, $desc, $html, $help, $class);
							
							$name = __('Delete after', 'wppa');
							$desc = __('Deete photo after xx dislike votes.', 'wppa');
							$help = esc_js(__('A value of 0 disables this feature.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If "One button vote" is selected in Table I-E1, this setting has no meaning', 'wppa'));
							$slug = 'wppa_dislike_delete';
							$html = wppa_input($slug, '40px', '', __('reports', 'wppa'));
							$class = 'wppa_rating_';
							wppa_setting($slug, '9', $name, $desc, $html, $help, $class);
							
							$name = __('Show dislike count', 'wppa');
							$desc = __('Show the number of dislikes in the rating bar.', 'wppa');
							$help = esc_js(__('Displayes the total number of dislike votes for the current photo.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If "One button vote" is selected in Table I-E1, this setting has no meaning', 'wppa'));
							$slug = 'wppa_dislike_show_count';
							$html = wppa_checkbox($slug, $onchange);
							$class = 'wppa_rating_';
							wppa_setting($slug, '10', $name, $desc, $html, $help, $class);
							
							$name = __('Rating display type', 'wppa');
							$desc = __('Specify the type of the rating display.', 'wppa');
							$help = esc_js(__('If "One button vote" is selected in Table I-E1, this setting has no meaning', 'wppa'));
							$slug = 'wppa_rating_display_type';
							$options = array(__('Graphic', 'wppa'), __('Numeric', 'wppa'));
							$values = array('graphic', 'numeric');
							$html = wppa_select($slug, $options, $values);
							$class = 'wppa_rating_';
							wppa_setting($slug, '11', $name, $desc, $html, $help, $class);

							$name = __('Show average rating', 'wppa');
							$desc = __('Display the avarage rating and/or vote count on the rating bar', 'wppa');
							$help = esc_js(__('If checked, the average rating as well as the current users rating is displayed in max 5 or 10 stars.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If unchecked, only the current users rating is displayed (if any).', 'wppa'));
							$help .= '\n\n'.esc_js(__('If "One button vote" is selected in Table I-E1, this box checked will display the vote count.', 'wppa'));
							$slug = 'wppa_show_avg_rating';
							$html = wppa_checkbox($slug);
							$class = 'wppa_rating_';
							wppa_setting($slug, '12', $name, $desc, $html, $help, $class);
							
							$name = __('Single vote button text', 'wppa');
							$desc = __('The text on the voting button.', 'wppa');
							$help = __('This text may contain qTranslate compatible language tags.', 'wppa');
							$slug = 'wppa_vote_button_text';
							$html = wppa_input($slug, '100');
							$class = 'wppa_rating_';
							wppa_setting($slug, '13', $name, $desc, $html, $help, $class);
							
							$name = __('Single vote button text voted', 'wppa');
							$desc = __('The text on the voting button when voted.', 'wppa');
							$help = __('This text may contain qTranslate compatible language tags.', 'wppa');
							$slug = 'wppa_voted_button_text';
							$html = wppa_input($slug, '100');
							$class = 'wppa_rating_';
							wppa_setting($slug, '14', $name, $desc, $html, $help, $class);
							
							$name = __('Single vote button thumbnail', 'wppa');
							$desc = __('Display single vote button below thumbnails.', 'wppa');
							$help = esc_js(__('This works only in single vote mode: Table I-E1 set to "one button vote"', 'wppa'));
							$slug = 'wppa_vote_thumb';
							$html = wppa_checkbox($slug);
							$class = 'wppa_rating_';
							wppa_setting($slug, '15', $name, $desc, $html, $help, $class);
							}
							wppa_setting_subheader( 'F', '1', __( 'Comments related settings', 'wppa' ), 'wppa_comment_' );
							{
							$name = __('Commenting login', 'wppa');
							$desc = __('Users must be logged in to comment on photos.', 'wppa');
							$help = esc_js(__('Check this box if you want users to be logged in to be able to enter comments on individual photos.', 'wppa'));
							$slug = 'wppa_comment_login';
							$html = wppa_checkbox($slug);
							$class = 'wppa_comment_';
							wppa_setting($slug, '1', $name, $desc, $html, $help, $class);
							
							$name = __('Last comment first', 'wppa');
							$desc = __('Display the newest comment on top.', 'wppa');
							$help = esc_js(__('If checked: Display the newest comment on top.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If unchecked, the comments are listed in the ordere they were entered.', 'wppa'));
							$slug = 'wppa_comments_desc';
							$html = wppa_checkbox($slug);
							$class = 'wppa_comment_';
							wppa_setting($slug, '2', $name, $desc, $html, $help, $class);
							
							$name = __('Comment moderation', 'wppa');
							$desc = __('Comments from what users need approval.', 'wppa');
							$help = esc_js(__('Select the desired users of which the comments need approval.', 'wppa'));
							$slug = 'wppa_comment_moderation';
							$options = array(__('All users', 'wppa'), __('Logged out users', 'wppa'), __('No users', 'wppa'));
							$values = array('all', 'logout', 'none');
							$html = wppa_select($slug, $options, $values);
							$class = 'wppa_comment_';
							wppa_setting($slug, '3', $name, $desc, $html, $help, $class);
							
							$name = __('Comment email required', 'wppa');
							$desc = __('Commenting users must enter their email addresses.', 'wppa');
							$help = '';
							$slug = 'wppa_comment_email_required';
							$html = wppa_checkbox($slug);
							$class = 'wppa_comment_';
							wppa_setting($slug, '4', $name, $desc, $html, $help, $class);
							
							$name = __('Comment notify', 'wppa');
							$desc = __('Select who must receive an e-mail notification of a new comment.', 'wppa');
							$help = '';
							$slug = 'wppa_comment_notify';
							$options = array(	__('--- None ---', 'wppa'), 
												__('--- Admin ---', 'wppa'), 
												__('--- Album owner ---'), 
												__('--- Admin & Owner ---', 'wppa'),
												__('--- Uploader ---', 'wppa'),
												__('--- Up & admin ---', 'wppa'),
												__('--- Up & Owner ---', 'wppa')
												);
							$values = array(	'none', 
												'admin', 
												'owner', 
												'both', 
												'upload', 
												'upadmin', 
												'upowner'
												);
							$users = wppa_get_users();
							foreach ($users as $usr) {
								$options[] = $usr['display_name'];
								$values[]  = $usr['ID'];
							}							
							$html = wppa_select($slug, $options, $values);
							$class = 'wppa_comment_';
							wppa_setting($slug, '5', $name, $desc, $html, $help, $class);
							
							$name = __('Comment ntfy added', 'wppa');
							$desc = __('Show "Comment added" after successfull adding a comment.', 'wppa');
							$help = '';
							$slug = 'wppa_comment_notify_added';
							$html = wppa_checkbox($slug);
							$class = 'wppa_comment_';
							wppa_setting($slug, '6', $name, $desc, $html, $help, $class);
							
							$name = __('ComTen alt display', 'wppa');
							$desc = __('Display comments at comten thumbnails.', 'wppa');
							$help = '';
							$slug = 'wppa_comten_alt_display';
							$html = wppa_checkbox($slug);
							$class = 'wppa_comment_';
							wppa_setting($slug, '7', $name, $desc, $html, $help, $class);
							
							$name = __('Comten Thumbnail width', 'wppa');
							$desc = __('The width of the thumbnail in the alt comment display.', 'wppa');
							$help = '';
							$slug = 'wppa_comten_alt_thumbsize';
							$html = wppa_input($slug, '50px', '', __('Pixels', 'wppa'));
							$class = 'wppa_comment_';
							wppa_setting($slug, '8', $name, $desc, $html, $help, $class);

							$name = __('Show smiley picker', 'wppa');
							$desc = __('Display a clickable row of smileys.', 'wppa');
							$help = '';
							$slug = 'wppa_comment_smiley_picker';
							$html = wppa_checkbox($slug);
							$class = 'wppa_comment_';
							wppa_setting($slug, '9', $name, $desc, $html, $help, $class);
							}
							wppa_setting_subheader( 'G', '1', __( 'Lightbox related settings. These settings have effect only when Table IX-J3 is set to wppa', 'wppa' ) );
							{
							$name = __('Overlay opacity', 'wppa');
							$desc = __('The opacity of the lightbox overlay background.', 'wppa');
							$help = '';
							$slug = 'wppa_ovl_opacity';
							$html = wppa_input($slug, '50px', '', __('%', 'wppa'));
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Click on background', 'wppa');
							$desc = __('Select the action to be taken on click on background.', 'wppa');
							$help = '';
							$slug = 'wppa_ovl_onclick';
							$options = array(__('Nothing', 'wppa'), __('Exit (close)', 'wppa'), __('Browse (left/right)', 'wppa'));
							$values = array('none', 'close', 'browse');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('Overlay animation speed', 'wppa');
							$desc = __('The fade-in time of the lightbox images', 'wppa');
							$help = '';
							$slug = 'wppa_ovl_anim';
							$options = array(__('--- off ---', 'wppa'), __('very fast (100 ms.)', 'wppa'), __('fast (200 ms.)', 'wppa'), __('normal (300 ms.)', 'wppa'),  __('slow (500 ms.)', 'wppa'), __('very slow (1 s.)', 'wppa'), __('extremely slow (2 s.)', 'wppa'));
							$values = array('0', '100', '200', '300', '500', '1000', '2000');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('Overlay at top in Chrome', 'wppa');
							$desc = __('Place the overlay (lightbox) image at the top of the page in Chrome browsers.', 'wppa');
							$help = esc_js(__('This is required for certain mobime devices.', 'wppa'));
							$slug = 'wppa_ovl_chrome_at_top';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							
							$name = __('WPPA+ Lightbox global', 'wppa');
							$desc = __('Use the wppa+ lightbox also for non-wppa images.', 'wppa');
							$help = '';
							$slug = 'wppa_lightbox_global';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							}
							?>
						</tbody>
						<tfoot style="font-weight: bold;" class="wppa_table_4">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Setting', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</tfoot>
					</table>
				</div>

			<?php // Table 5: Fonts ?>
			<?php wppa_settings_box_header(
				'5',
				__('Table V:', 'wppa').' '.__('Fonts:', 'wppa').' '.
				__('This table describes the Fonts used for the wppa+ elements.', 'wppa')
			); ?>
			
				<div id="wppa_table_5" style="display:none" >
					<table class="widefat wppa-table wppa-setting-table">
						<thead style="font-weight: bold; " class="wppa_table_5">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<th scope="col" style="min-width:250px;" ><?php _e('Font family', 'wppa') ?></td>
								<td><?php _e('Font size', 'wppa') ?></td>
								<td><?php _e('Font color', 'wppa') ?></td>
								<td><?php _e('Font weight', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</thead>
						<tbody class="wppa_table_5">
							<?php 
							$wppa_table = 'V';
							
							$wppa_subtable = 'Z';	// No subtables
							
							$options = array(__('normal', 'wppa'), __('bold', 'wppa'), __('bolder', 'wppa'), __('lighter', 'wppa'), '100', '200', '300', '400', '500', '600', '700', '800', '900');
							$values = array('normal', 'bold', 'bolder', 'lighter', '100', '200', '300', '400', '500', '600', '700', '800', '900');
							
							$name = __('Album titles', 'wppa');
							$desc = __('Font used for Album titles.', 'wppa');
							$help = esc_js(__('Enter font name, size, color and weight for album cover titles.', 'wppa'));
							$slug1 = 'wppa_fontfamily_title';
							$slug2 = 'wppa_fontsize_title';
							$slug3 = 'wppa_fontcolor_title';
							$slug4 = 'wppa_fontweight_title';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$html1 = wppa_input($slug1, '90%', '200px', '');
							$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
							$html3 = wppa_input($slug3, '70px', '', '');
							$html4 = wppa_select($slug4, $options, $values);
							$html = array($html1, $html2, $html3, $html4);
							wppa_setting($slug, '1a,b,c,d', $name, $desc, $html, $help);

							$name = __('Slideshow desc', 'wppa');
							$desc = __('Font for slideshow photo descriptions.', 'wppa');
							$help = esc_js(__('Enter font name, size, color and weight for slideshow photo descriptions.', 'wppa'));
							$slug1 = 'wppa_fontfamily_fulldesc';
							$slug2 = 'wppa_fontsize_fulldesc';
							$slug3 = 'wppa_fontcolor_fulldesc';
							$slug4 = 'wppa_fontweight_fulldesc';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$html1 = wppa_input($slug1, '90%', '200px', '');
							$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
							$html3 = wppa_input($slug3, '70px', '', '');
							$html4 = wppa_select($slug4, $options, $values);
							$html = array($html1, $html2, $html3, $html4);
							wppa_setting($slug, '2a,b,c,d', $name, $desc, $html, $help);
							
							$name = __('Slideshow name', 'wppa');
							$desc = __('Font for slideshow photo names.', 'wppa');
							$help = esc_js(__('Enter font name, size, color and weight for slideshow photo names.', 'wppa'));
							$slug1 = 'wppa_fontfamily_fulltitle';
							$slug2 = 'wppa_fontsize_fulltitle';
							$slug3 = 'wppa_fontcolor_fulltitle';
							$slug4 = 'wppa_fontweight_fulltitle';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$html1 = wppa_input($slug1, '90%', '200px', '');
							$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
							$html3 = wppa_input($slug3, '70px', '', '');
							$html4 = wppa_select($slug4, $options, $values);
							$html = array($html1, $html2, $html3, $html4);
							wppa_setting($slug, '3a,b,c,d', $name, $desc, $html, $help);
							
							$name = __('Navigations', 'wppa');
							$desc = __('Font for navigations.', 'wppa');
							$help = esc_js(__('Enter font name, size, color and weight for navigation items.', 'wppa'));
							$slug1 = 'wppa_fontfamily_nav';
							$slug2 = 'wppa_fontsize_nav';
							$slug3 = 'wppa_fontcolor_nav';
							$slug4 = 'wppa_fontweight_nav';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$html1 = wppa_input($slug1, '90%', '200px', '');
							$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
							$html3 = wppa_input($slug3, '70px', '', '');
							$html4 = wppa_select($slug4, $options, $values);
							$html = array($html1, $html2, $html3, $html4);
							wppa_setting($slug, '4a,b,c,d', $name, $desc, $html, $help);
							
							$name = __('Thumbnails', 'wppa');
							$desc = __('Font for text under thumbnails.', 'wppa');
							$help = esc_js(__('Enter font name, size, color and weight for text under thumbnail images.', 'wppa'));
							$slug1 = 'wppa_fontfamily_thumb';
							$slug2 = 'wppa_fontsize_thumb';
							$slug3 = 'wppa_fontcolor_thumb';
							$slug4 = 'wppa_fontweight_thumb';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$html1 = wppa_input($slug1, '90%', '200px', '');
							$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
							$html3 = wppa_input($slug3, '70px', '', '');
							$html4 = wppa_select($slug4, $options, $values);
							$html = array($html1, $html2, $html3, $html4);
							wppa_setting($slug, '5a,b,c,d', $name, $desc, $html, $help);
							
							$name = __('Other', 'wppa');
							$desc = __('General font in wppa boxes.', 'wppa');
							$help = esc_js(__('Enter font name, size, color and weight for all other items.', 'wppa')); 
							$slug1 = 'wppa_fontfamily_box';
							$slug2 = 'wppa_fontsize_box';
							$slug3 = 'wppa_fontcolor_box';
							$slug4 = 'wppa_fontweight_box';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$html1 = wppa_input($slug1, '90%', '200px', '');
							$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
							$html3 = wppa_input($slug3, '70px', '', '');
							$html4 = wppa_select($slug4, $options, $values);
							$html = array($html1, $html2, $html3, $html4);
							wppa_setting($slug, '6a,b,c,d', $name, $desc, $html, $help);

							$name = __('Numbar', 'wppa');
							$desc = __('Font in wppa number bars.', 'wppa');
							$help = esc_js(__('Enter font name, size, color and weight for numberbar navigation.', 'wppa')); 
							$slug1 = 'wppa_fontfamily_numbar';
							$slug2 = 'wppa_fontsize_numbar';
							$slug3 = 'wppa_fontcolor_numbar';
							$slug4 = 'wppa_fontweight_numbar';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$html1 = wppa_input($slug1, '90%', '200px', '');
							$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
							$html3 = wppa_input($slug3, '70px', '', '');
							$html4 = wppa_select($slug4, $options, $values);
							$html = array($html1, $html2, $html3, $html4);
							wppa_setting($slug, '7a,b,c,d', $name, $desc, $html, $help);

							$name = __('Numbar Active', 'wppa');
							$desc = __('Font in wppa number bars, active item.', 'wppa');
							$help = esc_js(__('Enter font name, size, color and weight for numberbar navigation.', 'wppa')); 
							$slug1 = 'wppa_fontfamily_numbar_active';
							$slug2 = 'wppa_fontsize_numbar_active';
							$slug3 = 'wppa_fontcolor_numbar_active';
							$slug4 = 'wppa_fontweight_numbar_active';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$html1 = wppa_input($slug1, '90%', '200px', '');
							$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
							$html3 = wppa_input($slug3, '70px', '', '');
							$html4 = wppa_select($slug4, $options, $values);
							$html = array($html1, $html2, $html3, $html4);
							wppa_setting($slug, '8a,b,c,d', $name, $desc, $html, $help);
							
							$name = __('Lightbox', 'wppa');
							$desc = __('Font in wppa lightbox overlays.', 'wppa');
							$help = esc_js(__('Enter font name, size, color and weight for wppa lightbox overlays.', 'wppa')); 
							$slug1 = 'wppa_fontfamily_lightbox';
							$slug2 = 'wppa_fontsize_lightbox';
							$slug3 = 'wppa_fontcolor_lightbox';
							$slug4 = 'wppa_fontweight_lightbox';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$html1 = wppa_input($slug1, '90%', '200px', '');
							$html2 = wppa_input($slug2, '40px', '', __('pixels', 'wppa'));
							$html3 = wppa_input($slug3, '70px', '', '');
							$html4 = wppa_select($slug4, $options, $values);
							$html = array($html1, $html2, $html3, $html4);
							wppa_setting($slug, '9a,b,c,d', $name, $desc, $html, $help);
							
							?>
						</tbody>
						<tfoot style="font-weight: bold;" class="wppa_table_5">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Font family', 'wppa') ?></td>
								<td><?php _e('Font size', 'wppa') ?></td>
								<td><?php _e('Font color', 'wppa') ?></td>
								<td><?php _e('Font weight', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			
			<?php // Table 6: Links ?>
			<?php wppa_settings_box_header(
				'6',
				__('Table VI:', 'wppa').' '.__('Links:', 'wppa').' '.
				__('This table defines the link types and pages.', 'wppa')
			); ?>
		
				<div id="wppa_table_6" style="display:none" >
					<table class="widefat wppa-table wppa-setting-table">
						<thead style="font-weight: bold; " class="wppa_table_6">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Link type', 'wppa') ?></td>
								<td><?php _e('Link page', 'wppa') ?></td>
								<td><?php _e('New tab', 'wppa') ?></td>
								<th scope="col" title="<?php _e('Photo specific link overrules', 'wppa') ?>" style="cursor: default"><?php _e('PSO', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</thead>
						<tbody class="wppa_table_6">
							<?php 
							$wppa_table = 'VI';
							
							$wppa_subtable = 'Z';
							
							// Linktypes
							$options_linktype = array(
								__('no link at all.', 'wppa'), 
								__('the plain photo (file).', 'wppa'), 
								__('the full size photo in a slideshow.', 'wppa'), 
								__('the fullsize photo on its own.', 'wppa'), 
								__('the single photo in the style of a slideshow.', 'wppa'),
								__('the fs photo with download and print buttons.', 'wppa'), 
								__('lightbox.', 'wppa')
							);
							$values_linktype = array(
								'none', 
								'file', 
								'photo', 
								'single', 
								'slphoto', 
								'fullpopup', 
								'lightbox'
							);
							$options_linktype_album = array(
								__('no link at all.', 'wppa'), 
								__('the plain photo (file).', 'wppa'), 
								__('the content of the album.', 'wppa'), 
								__('the full size photo in a slideshow.', 'wppa'), 
								__('the fullsize photo on its own.', 'wppa'), 
								__('lightbox.', 'wppa')
							);
							$values_linktype_album = array('none', 'file', 'album', 'photo', 'single', 'lightbox'); //, 'indiv');
							$options_linktype_ss_widget = array(
								__('no link at all.', 'wppa'), 
								__('the plain photo (file).', 'wppa'), 
								__('defined at widget activation.', 'wppa'), 
								__('the content of the album.', 'wppa'), 
								__('the full size photo in a slideshow.', 'wppa'), 
								__('the fullsize photo on its own.', 'wppa')
							);
							$values_linktype_ss_widget = array('none', 'file', 'widget', 'album', 'photo', 'single'); //, 'indiv');
							$options_linktype_potd_widget = array(__('no link at all.', 'wppa'), __('the plain photo (file).', 'wppa'), __('defined on widget admin page.', 'wppa'), __('the content of the album.', 'wppa'), __('the full size photo in a slideshow.', 'wppa'), __('the fullsize photo on its own.', 'wppa'), __('lightbox.', 'wppa')); //, __('the photo specific link.', 'wppa'));
							$values_linktype_potd_widget = array('none', 'file', 'custom', 'album', 'photo', 'single', 'lightbox'); //, 'indiv');
							$options_linktype_cover_image = array(__('no link at all.', 'wppa'), __('the plain photo (file).', 'wppa'), __('same as title.', 'wppa'), __('lightbox.', 'wppa'));
							$values_linktype_cover_image = array('none', 'file', 'same', 'lightbox');
							$options_linktype_lasten = array(
								__('no link at all.', 'wppa'), 
								__('the plain photo (file).', 'wppa'), 
								__('the content of the virtual lasten album.', 'wppa'),
								__('the content of the thumbnails album.', 'wppa'),
								__('the full size photo in a slideshow.', 'wppa'), 
								__('the fullsize photo on its own.', 'wppa'), 
								__('the single photo in the style of a slideshow.', 'wppa'),
								__('the fs photo with download and print buttons.', 'wppa'), 
								__('lightbox.', 'wppa')
							);
							$values_linktype_lasten = array(
								'none', 
								'file', 
								'album',
								'thumbalbum',
								'photo', 
								'single', 
								'slphoto', 
								'fullpopup', 
								'lightbox'
							);

							// Linkpages
							$options_page = false;
							$options_page_post = false;
							$values_page = false;
							$values_page_post = false;
							// First
							$options_page_post[] = __('--- The same post or page ---', 'wppa');
							$values_page_post[] = '0';
							$options_page[] = __('--- Please select a page ---', 'wppa');
							$values_page[] = '0';
							// Pages if any
							$query = "SELECT ID, post_title, post_content, post_parent FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish' ORDER BY post_title ASC";
							$pages = $wpdb->get_results ($query, ARRAY_A);
							if ($pages) {
								if ( $wppa_opt['wppa_hier_pagesel'] ) $pages = wppa_add_parents($pages);
								else {	// Just translate
									foreach ( array_keys($pages) as $index ) {
										$pages[$index]['post_title'] = __(stripslashes($pages[$index]['post_title']));
									}
								}
								$pages = wppa_array_sort($pages, 'post_title');
								foreach ($pages as $page) {
									if (strpos($page['post_content'], '%%wppa%%') !== false || strpos($page['post_content'], '[wppa') !== false) {
										$options_page[] = __($page['post_title']);
										$options_page_post[] = __($page['post_title']);
										$values_page[] = $page['ID'];
										$values_page_post[] = $page['ID'];
									}
									else {
										$options_page[] = '|'.__($page['post_title']).'|';
										$options_page_post[] = '|'.__($page['post_title']).'|';
										$values_page[] = $page['ID'];
										$values_page_post[] = $page['ID'];
									}
								}
							}
							else {
								$options_page[] = __('--- No page to link to (yet) ---', 'wppa');
								$values_page[] = '0';
							}

							wppa_setting_subheader('A', '4', __('Links from images in WPPA+ Widgets', 'wppa'));
							
							$name = __('PotdWidget', 'wppa');
							$desc = __('Photo Of The Day widget link.', 'wppa');
							$help = esc_js(__('Select the type of link the photo of the day points to.', 'wppa')); 
							$help .= '\n\n'.esc_js(__('If you select \'defined on widget admin page\' you can manually enter a link and title on the Photo of the day Widget Admin page.', 'wppa'));
							$slug1 = 'wppa_widget_linktype';
							$slug2 = 'wppa_widget_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_potd_blank';
							$slug4 = 'wppa_potdwidget_overrule';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$onchange = 'wppaCheckPotdLink(); wppaCheckLinkPageErr(\'widget\');';
							$html1 = wppa_select($slug1, $options_linktype_potd_widget, $values_linktype_potd_widget, $onchange);
							$class = 'wppa_potdlp';
							$onchange = 'wppaCheckLinkPageErr(\'widget\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = 'wppa_potdlb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = wppa_checkbox($slug4);
							$htmlerr = wppa_htmlerr('widget');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							wppa_setting($slug, '1a,b,c,d', $name, $desc, $html, $help);
							
							$name = __('SlideWidget', 'wppa');
							$desc = __('Slideshow widget photo link.', 'wppa');
							$help = esc_js(__('Select the type of link the slideshow photos point to.', 'wppa')); 
							$slug1 = 'wppa_slideonly_widget_linktype';
							$slug2 = 'wppa_slideonly_widget_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_sswidget_blank';
							$slug4 = 'wppa_sswidget_overrule';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$onchange = 'wppaCheckSlideOnlyLink(); wppaCheckLinkPageErr(\'slideonly_widget\');';
							$html1 = wppa_select($slug1, $options_linktype_ss_widget, $values_linktype_ss_widget, $onchange);
							$class = 'wppa_solp';
							$onchange = 'wppaCheckLinkPageErr(\'slideonly_widget\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = 'wppa_solb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = wppa_checkbox($slug4);
							$htmlerr = wppa_htmlerr('slideonly_widget');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							wppa_setting($slug, '2a,b,c,d', $name, $desc, $html, $help);
							
							$name = __('Album widget', 'wppa');
							$desc = __('Album widget thumbnail link', 'wppa');
							$help = esc_js(__('Select the type of link the album widget photos point to.', 'wppa'));
							$slug1 = 'wppa_album_widget_linktype'; 
							$slug2 = 'wppa_album_widget_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_album_widget_blank';
						//	$slug4 = 'wppa_album_widget_overrule';	// useless
							$slug = array($slug1, $slug2, $slug3);
							$onchange = 'wppaCheckAlbumWidgetLink(); wppaCheckLinkPageErr(\'album_widget\');';
							$options_linktype_album_widget = array(__('subalbums and thumbnails.', 'wppa'), __('slideshow.', 'wppa'), __('lightbox.', 'wppa'));
							$values_linktype_album_widget = array('content', 'slide', 'lightbox');
							$html1 = wppa_select($slug1, $options_linktype_album_widget, $values_linktype_album_widget, $onchange);
							$class = 'wppa_awlp';
							$onchange = 'wppaCheckLinkPageErr(\'album_widget\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = 'wppa_awlb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = ''; // wppa_checkbox($slug4);
							$htmlerr = wppa_htmlerr('album_widget');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							wppa_setting($slug, '3a,b,c', $name, $desc, $html, $help);

							$name = __('ThumbnailWidget', 'wppa');
							$desc = __('Thumbnail widget photo link.', 'wppa');
							$help = esc_js(__('Select the type of link the thumbnail photos point to.', 'wppa')); 
							$slug1 = 'wppa_thumbnail_widget_linktype'; 
							$slug2 = 'wppa_thumbnail_widget_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_thumbnail_widget_blank';
							$slug4 = 'wppa_thumbnail_widget_overrule';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$onchange = 'wppaCheckThumbnailWLink(); wppaCheckLinkPageErr(\'thumbnail_widget\');';
							$html1 = wppa_select($slug1, $options_linktype, $values_linktype, $onchange);
							$class = 'wppa_tnlp';
							$onchange = 'wppaCheckLinkPageErr(\'thumbnail_widget\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = 'wppa_tnlb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = wppa_checkbox($slug4);
							$htmlerr = wppa_htmlerr('thumbnail_widget');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							wppa_setting($slug, '4a,b,c,d', $name, $desc, $html, $help);

							$name = __('TopTenWidget', 'wppa');
							$desc = __('TopTen widget photo link.', 'wppa');
							$help = esc_js(__('Select the type of link the top ten photos point to.', 'wppa')); 
							$slug1 = 'wppa_topten_widget_linktype'; 
							$slug2 = 'wppa_topten_widget_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_topten_blank';
							$slug4 = 'wppa_topten_overrule';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$onchange = 'wppaCheckTopTenLink(); wppaCheckLinkPageErr(\'topten_widget\');';
							$html1 = wppa_select($slug1, $options_linktype, $values_linktype, $onchange);
							$class = 'wppa_ttlp';
							$onchange = 'wppaCheckLinkPageErr(\'topten_widget\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = 'wppa_ttlb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = wppa_checkbox($slug4);
							$htmlerr = wppa_htmlerr('topten_widget');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							$class = 'wppa_rating';
							wppa_setting($slug, '5a,b,c,d', $name, $desc, $html, $help, $class);
							
							$name = __('LasTenWidget', 'wppa');
							$desc = __('Last Ten widget photo link.', 'wppa');
							$help = esc_js(__('Select the type of link the last ten photos point to.', 'wppa')); 
							$slug1 = 'wppa_lasten_widget_linktype'; 
							$slug2 = 'wppa_lasten_widget_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_lasten_blank';
							$slug4 = 'wppa_lasten_overrule';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$onchange = 'wppaCheckLasTenLink(); wppaCheckLinkPageErr(\'lasten_widget\');';
							$html1 = wppa_select($slug1, $options_linktype_lasten, $values_linktype_lasten, $onchange);
							$class = 'wppa_ltlp';
							$onchange = 'wppaCheckLinkPageErr(\'lasten_widget\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = 'wppa_ltlb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = wppa_checkbox($slug4);
							$htmlerr = wppa_htmlerr('lasten_widget');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							wppa_setting($slug, '6a,b,c,d', $name, $desc, $html, $help);

							$name = __('CommentWidget', 'wppa');
							$desc = __('Comment widget photo link.', 'wppa');
							$help = esc_js(__('Select the type of link the comment widget photos point to.', 'wppa')); 
							$slug1 = 'wppa_comment_widget_linktype'; 
							$slug2 = 'wppa_comment_widget_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_comment_blank';
							$slug4 = 'wppa_comment_overrule';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$onchange = 'wppaCheckCommentLink(); wppaCheckLinkPageErr(\'comment_widget\');';
							$html1 = wppa_select($slug1, $options_linktype, $values_linktype, $onchange);
							$class = 'wppa_cmlp';
							$onchange = 'wppaCheckLinkPageErr(\'comment_widget\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = 'wppa_cmlb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = wppa_checkbox($slug4);
							$htmlerr = wppa_htmlerr('comment_widget');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							wppa_setting($slug, '7a,b,c,d', $name, $desc, $html, $help);
							
							$name = __('FeaTenWidget', 'wppa');
							$desc = __('FeaTen widget photo link.', 'wppa');
							$help = esc_js(__('Select the type of link the featured ten photos point to.', 'wppa')); 
							$slug1 = 'wppa_featen_widget_linktype'; 
							$slug2 = 'wppa_featen_widget_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_featen_blank';
							$slug4 = 'wppa_featen_overrule';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$onchange = 'wppaCheckFeaTenLink(); wppaCheckLinkPageErr(\'featen_widget\');';
							$html1 = wppa_select($slug1, $options_linktype, $values_linktype, $onchange);
							$class = 'wppa_ftlp';
							$onchange = 'wppaCheckLinkPageErr(\'featen_widget\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = 'wppa_ftlb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = wppa_checkbox($slug4);
							$htmlerr = wppa_htmlerr('featen_widget');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							wppa_setting($slug, '8a,b,c,d', $name, $desc, $html, $help);

							wppa_setting_subheader('B', '4', __('Links from other WPPA+ images', 'wppa'));
							
							$name = __('Cover Image', 'wppa');
							$desc = __('The link from the cover image of an album.', 'wppa');
							$help = esc_js(__('Select the type of link the coverphoto points to.', 'wppa'));
							$help .= '\n\n'.esc_js(__('The link from the album title can be configured on the Edit Album page.', 'wppa'));
							$help .= '\n'.esc_js(__('This link will be used for the photo also if you select: same as title.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If you specify New Tab on this line, all links from the cover will open a new tab,', 'wppa'));
							$help .= '\n'.esc_js(__('except when Ajax is activated on Table IV-A1.', 'wppa'));
							$slug1 = 'wppa_coverimg_linktype';
							$slug2 = 'wppa_coverimg_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_coverimg_blank';
							$slug4 = 'wppa_coverimg_overrule';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$onchange = 'wppaCheckCoverImg()';
							$html1 = wppa_select($slug1, $options_linktype_cover_image, $values_linktype_cover_image, $onchange);
							$class = '';
							$html2 = '';
							$class = 'wppa_covimgbl';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = wppa_checkbox($slug4);
							$html = array($html1, $html2, $html3, $html4);
							wppa_setting($slug, '1a,b,c,d', $name, $desc, $html, $help);

							$name = __('Thumbnail', 'wppa');
							$desc = __('Thumbnail link.', 'wppa');
							$help = esc_js(__('Select the type of link you want, or no link at all.', 'wppa'));
							$help .= '\n'.esc_js(__('If you select the fullsize photo on its own, it will be stretched to fit, regardless of that setting.', 'wppa')); /* oneofone is treated as portrait only */ 
							$help .= '\n'.esc_js(__('Note that a page must have at least %%wppa%% or [wppa][/wppa] in its content to show up the photo(s).', 'wppa'));
							$slug1 = 'wppa_thumb_linktype';
							$slug2 = 'wppa_thumb_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_thumb_blank';
							$slug4 = 'wppa_thumb_overrule';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$onchange = 'wppaCheckThumbLink()';
							$opts = $options_linktype;
							$vals = $values_linktype;
							if ( wppa_switch('wppa_auto_page') ) {
								$opts[] = __('Auto Page', 'wppa');
								$vals[] = 'autopage';
							}
							$html1 = wppa_select($slug1, $opts, $vals, $onchange);
							$class = 'wppa_tlp';
							$html2 = wppa_select($slug2, $options_page_post, $values_page_post, '', $class);
							$class = 'wppa_tlb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = wppa_checkbox($slug4);
							$htmlerr = wppa_htmlerr('popup-lightbox');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							$class = 'tt_always';
							wppa_setting($slug, '2a,b,c,d', $name, $desc, $html, $help, $class);

							$name = __('Sphoto', 'wppa');
							$desc = __('Single photo link.', 'wppa');
							$help = esc_js(__('Select the type of link you want, or no link at all.', 'wppa')); 
							$help .= '\n'.esc_js(__('If you select the fullsize photo on its own, it will be stretched to fit, regardless of that setting.', 'wppa')); /* oneofone is treated as portrait only */
							$help .= '\n'.esc_js(__('Note that a page must have at least %%wppa%% or [wppa][/wppa] in its content to show up the photo(s).', 'wppa')); 
							$slug1 = 'wppa_sphoto_linktype';
							$slug2 = 'wppa_sphoto_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_sphoto_blank';
							$slug4 = 'wppa_sphoto_overrule';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$onchange = 'wppaCheckSphotoLink(); wppaCheckLinkPageErr(\'sphoto\');';
							$html1 = wppa_select($slug1, $options_linktype_album, $values_linktype_album, $onchange);
							$class = 'wppa_slp';
							$onchange = 'wppaCheckLinkPageErr(\'sphoto\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = 'wppa_slb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = wppa_checkbox($slug4);
							$htmlerr = wppa_htmlerr('sphoto');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							wppa_setting($slug, '3a,b,c,d', $name, $desc, $html, $help);

							$name = __('Mphoto', 'wppa');
							$desc = __('Media-like photo link.', 'wppa');
							$help = esc_js(__('Select the type of link you want, or no link at all.', 'wppa')); 
							$help .= '\n'.esc_js(__('If you select the fullsize photo on its own, it will be stretched to fit, regardless of that setting.', 'wppa')); /* oneofone is treated as portrait only */
							$help .= '\n'.esc_js(__('Note that a page must have at least %%wppa%% or [wppa][/wppa] in its content to show up the photo(s).', 'wppa')); 
							$slug1 = 'wppa_mphoto_linktype';
							$slug2 = 'wppa_mphoto_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_mphoto_blank';
							$slug4 = 'wppa_mphoto_overrule';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$onchange = 'wppaCheckMphotoLink(); wppaCheckLinkPageErr(\'mphoto\');';
							$html1 = wppa_select($slug1, $options_linktype_album, $values_linktype_album, $onchange);
							$class = 'wppa_mlp';
							$onchange = 'wppaCheckLinkPageErr(\'mphoto\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = 'wppa_mlb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = wppa_checkbox($slug4);
							$htmlerr = wppa_htmlerr('mphoto');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							wppa_setting($slug, '4a,b,c,d', $name, $desc, $html, $help);
							
							$name = __('Slideshow', 'wppa');
							$desc = __('Slideshow fullsize link', 'wppa');
							$help = esc_js(__('You can overrule lightbox but not big browse buttons with the photo specifc link.', 'wppa'));
							$slug1 = 'wppa_slideshow_linktype';
							$slug2 = 'wppa_slideshow_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_slideshow_blank';
							$slug4 = 'wppa_slideshow_overrule';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$onchange = 'wppaCheckSlidePhotoLink();';
							$opts = array(__('no link at all.', 'wppa'), __('the plain photo (file).', 'wppa'), __('the fullsize photo on its own.', 'wppa'), __('lightbox.', 'wppa'), __('lightbox single photos.', 'wppa'), __('the fs photo with download and print buttons.', 'wppa'));
							$vals = array('none', 'file', 'single', 'lightbox', 'lightboxsingle', 'fullpopup'); 
//							$opts = array(__('no link at all.', 'wppa'), __('the plain photo (file).', 'wppa'), __('lightbox.', 'wppa'));
//							$vals = array('none', 'file', 'lightbox'); 
							$onchange = 'wppaCheckSlidePhotoLink()';
							$html1 = wppa_select($slug1, $opts, $vals, $onchange);
							$class = 'wppa_sslp';
							$html2 = wppa_select($slug2, $options_page_post, $values_page_post, $onchange, $class);
							$class = 'wppa_sslb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = wppa_checkbox($slug4);
							$html = array($html1, $html2, $html3, $html4);
							wppa_setting($slug, '5a,,c,d', $name, $desc, $html, $help);

							$name = __('Film linktype', 'wppa');
							$desc = __('Direct access goto image in:', 'wppa');
							$help = esc_js(__('Select the action to be taken when the user clicks on a filmstrip image.', 'wppa'));
							$slug = 'wppa_film_linktype';
							$options = array(__('slideshow window', 'wppa'), __('lightbox overlay', 'wppa'));
							$values = array('slideshow', 'lightbox');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '6', $name, $desc, $html.'</td><td></td><td></td><td>', $help);
							
							wppa_setting_subheader('C', '4', __('Other links', 'wppa'));

							$name = __('Art Monkey Link', 'wppa');
							$desc = __('Makes the photo name a download button.', 'wppa');
							$help = esc_js(__('Link Photo name in slideshow to file or zip with photoname as filename.', 'wppa'));
							$slug = 'wppa_art_monkey_link';
							$options = array(__('--- none ---', 'wppa'), __('image file', 'wppa'), __('zipped image', 'wppa'));
							$values = array('none', 'file', 'zip');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '1', $name, $desc, $html.'</td><td></td><td></td><td>', $help);
							
							$name = __('Art Monkey Source', 'wppa');
							$desc = __('Use Source file for art monkey link if available.', 'wppa');
							$help = '';
							$slug = 'wppa_artmonkey_use_source';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '1.1', $name, $desc, $html.'</td><td></td><td></td><td>', $help);
							
							$name = __('Art Monkey Display', 'wppa');
							$desc = __('Select button or link ( text ).', 'wppa');
							$help = '';
							$slug = 'wppa_art_monkey_display';
							$options = array(__('Button', 'wppa'), __('Textlink', 'wppa'));
							$values = array('button', 'text');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '1.2', $name, $desc, $html.'</td><td></td><td></td><td>', $help);						
							
							$name = __('Popup Download Link', 'wppa');
							$desc = __('Configure the download link on fullsize popups.', 'wppa');
							$help = esc_js(__('Link fullsize popup download button to either image or zip file.', 'wppa'));
							$slug = 'wppa_art_monkey_popup_link';
							$options = array(__('image file', 'wppa'), __('zipped image', 'wppa'));
							$values = array('file', 'zip');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '2', $name, $desc, $html.'</td><td></td><td></td><td>', $help);
							
							$name = __('Tagcloud Link', 'wppa');
							$desc = __('Configure the link from the tags in the tag cloud.', 'wppa');
							$help = esc_js(__('Link the tag words to ether the thumbnails or the slideshow.', 'wppa'));
							$slug1 = 'wppa_tagcloud_linktype';
							$slug2 = 'wppa_tagcloud_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_tagcloud_blank';
							$slug4 = '';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$opts = array(__('thumbnails', 'wppa'), __('slideshow', 'wppa'));
							$vals = array('album', 'slide'); 
							$onchange = 'wppaCheckTagLink(); wppaCheckLinkPageErr(\'tagcloud\');';
							$html1 = wppa_select($slug1, $opts, $vals, $onchange);						
							$class = 'wppa_tglp';
							$onchange = 'wppaCheckLinkPageErr(\'tagcloud\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = 'wppa_tglb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = '';
							$htmlerr = wppa_htmlerr('tagcloud');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							wppa_setting($slug, '3a,b,c', $name, $desc, $html, $help);

							$name = __('Multitag Link', 'wppa');
							$desc = __('Configure the link from the multitag selection.', 'wppa');
							$help = esc_js(__('Link to ether the thumbnails or the slideshow.', 'wppa'));
							$slug1 = 'wppa_multitag_linktype';
							$slug2 = 'wppa_multitag_linkpage';
							wppa_verify_page($slug2);
							$slug3 = 'wppa_multitag_blank';
							$slug4 = '';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$opts = array(__('thumbnails', 'wppa'), __('slideshow', 'wppa'));
							$vals = array('album', 'slide'); 
							$onchange = 'wppaCheckMTagLink(); wppaCheckLinkPageErr(\'multitag\');';
							$html1 = wppa_select($slug1, $opts, $vals, $onchange);						
							$class = 'wppa_tglp';
							$onchange = 'wppaCheckLinkPageErr(\'multitag\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = 'wppa_tglb';
							$html3 = wppa_checkbox($slug3, '', $class);
							$html4 = '';
							$htmlerr = wppa_htmlerr('multitag');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							wppa_setting($slug, '4a,b,c', $name, $desc, $html, $help);
							
							$name = __('Super View Landing', 'wppa');
							$desc = __('Select the landing page for the Super View widget.', 'wppa');
							$help = '';
							$slug1 = '';
							$slug2 = 'wppa_super_view_linkpage';
							wppa_verify_page($slug2);
							$slug3 = '';
							$slug4 = '';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$html1 = '';				
							$class = '';
							$onchange = 'wppaCheckLinkPageErr(\'super_view\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = '';
							$html3 = '';
							$html4 = '';
							$htmlerr = wppa_htmlerr('super_view');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							wppa_setting($slug, '5', $name, $desc, $html, $help);

							$name = __('Uploader Widget Landing', 'wppa');
							$desc = __('Select the landing page for the Uploader Photos Widget', 'wppa');
							$help = '';
							$slug1 = '';
							$slug2 = 'wppa_upldr_widget_linkpage';
							wppa_verify_page($slug2);
							$slug3 = '';
							$slug4 = '';
							$slug = array($slug1, $slug2, $slug3, $slug4);
							$html1 = '';				
							$class = '';
							$onchange = 'wppaCheckLinkPageErr(\'upldr_widget\');';
							$html2 = wppa_select($slug2, $options_page, $values_page, $onchange, $class, true);
							$class = '';
							$html3 = '';
							$html4 = '';
							$htmlerr = wppa_htmlerr('upldr_widget');
							$html = array($html1, $htmlerr.$html2, $html3, $html4);
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							
							?>
						</tbody>
						<tfoot style="font-weight: bold;" class="wppa_table_6">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Link type', 'wppa') ?></td>
								<td><?php _e('Link page', 'wppa') ?></td>
								<td><?php _e('New tab', 'wppa') ?></td>
								<th scope="col" title="<?php _e('Photo specific link overrules', 'wppa') ?>" style="cursor: default"><?php _e('PSO', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			
			<?php // Table 7: Security ?>
			<?php wppa_settings_box_header(
				'7',
				__('Table VII:', 'wppa').' '.__('Permittings and Restrictions:', 'wppa').' '.
				__('This table describes the access settings for admin and front-end activities.', 'wppa')
			); ?>

				<div id="wppa_table_7" style="display:none" >
					<table class="widefat wppa-table wppa-setting-table" style="padding-bottom:0; margin-bottom:0;" >
						<thead style="font-weight: bold; " class="wppa_table_7">
							<tr>
								<?php
									$wppacaps = array(	'wppa_admin', 
														'wppa_upload', 
														'wppa_import', 
														'wppa_moderate',
														'wppa_export', 
														'wppa_settings', 
														'wppa_potd', 
														'wppa_comments', 
														'wppa_help'
														);
									$wppanames = array( 'Album Admin', 
														'Upload Photos', 
														'Import Photos', 
														'Moderate P+C',
														'Export Photos', 
														'Settings', 
														'Photo of the day', 
														'Comment&nbsp;Admin', 
														'Help & Info'
														);
									echo '<td>'.__('Role', 'wppa').'</td>';
									for ($i = 0; $i < count($wppacaps); $i++) echo '<td style="width:11%;">'.$wppanames[$i].'</td>';
								?>
							</tr>
						</thead>
						<tbody class="wppa_table_7">
							<?php 
							$wppa_table = 'VII';
							
							wppa_setting_subheader('A', '6', __('Admin settings per user role. Enabling these settings will overrule the front-end settings for the specific user role', 'wppa'));

							$roles = $wp_roles->roles;
							foreach (array_keys($roles) as $key) {
								$role = $roles[$key];
								echo '<tr class="wppa-VII-A wppa-none" ><td>'.$role['name'].'</td>';
								$caps = $role['capabilities'];
								for ($i = 0; $i < count($wppacaps); $i++) {
									if (isset($caps[$wppacaps[$i]])) {
										$yn = $caps[$wppacaps[$i]] ? true : false;
									}
									else $yn = false;
									$enabled = ( $key != 'administrator' );
									echo '<td>'.wppa_checkbox_e('caps-'.$wppacaps[$i].'-'.$key, $yn, '', '', $enabled).'</td>';
								};
								echo '</tr>';
							}
							?>
						</tbody>
					</table>
					<table class="widefat wppa-table wppa-setting-table" style="margin-top:-2px;padding-top:0;" >
						<tbody class="wppa_table_7">
							<?php
							wppa_setting_subheader( 'B', '2', __('Frontend create Albums and upload Photos enabling and limiting settings', 'wppa' ) );
							
							$name = __('User create Albums', 'wppa');
							$desc = __('Enable frontend album creation.', 'wppa');
							$help = esc_js(__('If you check this item, frontend album creation will be enabled.', 'wppa'));
							$slug = 'wppa_user_create_on';
							$onchange = '';//wppaCheckUserUpload()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('User create Albums login', 'wppa');
							$desc = __('Frontend album creation requires the user is logged in.', 'wppa');
							$help = '';//esc_js(__('If you uncheck this box, make sure you check the item Owners only in the next sub-table.', 'wppa'));
//							$help .= '\n'.esc_js(__('Set the owner to ---public--- of the albums that are allowed to be uploaded to.', 'wppa'));
							$slug = 'wppa_user_create_login';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('User upload Photos', 'wppa');
							$desc = __('Enable frontend upload.', 'wppa');
							$help = esc_js(__('If you check this item, frontend upload will be enabled according to the rules set in the following items of this table.', 'wppa'));
							$slug = 'wppa_user_upload_on';
							$onchange = 'wppaCheckUserUpload()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('User upload Photos login', 'wppa');
							$desc = __('Frontend upload requires the user is logged in.', 'wppa');
							$help = esc_js(__('If you uncheck this box, make sure you check the item Owners only in the next sub-table.', 'wppa'));
							$help .= '\n'.esc_js(__('Set the owner to ---public--- of the albums that are allowed to be uploaded to.', 'wppa'));
							$slug = 'wppa_user_upload_login';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							
							// User upload limits
							$options = array( 	__('for ever', 'wppa'), 
												__('per hour', 'wppa'), 
												__('per day', 'wppa'), 
												__('per week', 'wppa'), 
												__('per month', 'wppa'), 	// 30 days
												__('per year', 'wppa'));	// 364 days
							$values = array( '0', '3600', '86400', '604800', '2592000', '31449600');
							
							$roles = $wp_roles->roles;
							$roles['loggedout'] = '';
							unset ($roles['administrator']);
							foreach (array_keys($roles) as $role) {
								if ( get_option('wppa_'.$role.'_upload_limit_count', 'nil') == 'nil') update_option('wppa_'.$role.'_upload_limit_count', '0');
								if ( get_option('wppa_'.$role.'_upload_limit_time', 'nil') == 'nil') update_option('wppa_'.$role.'_upload_limit_time', '0');
								$name = sprintf(__('Upload limit %s', 'wppa'), $role);
								if ( $role == 'loggedout' ) $desc = __('Limit upload capacity for logged out users.', 'wppa');
								else $desc = sprintf(__('Limit upload capacity for the user role %s.', 'wppa'), $role);
								if ( $role == 'loggedout' ) $help = esc_js(__('This setting has only effect when Table VII-B2 is unchecked.', 'wppa'));
								else $help = esc_js(__('This limitation only applies to frontend uploads when the same userrole does not have the Upload checkbox checked in Table VII-A.', 'wppa'));
								$help .= '\n'.esc_js(__('A value of 0 means: no limit.', 'wppa'));
								$slug = 'wppa_'.$role.'_upload_limit_count';
								$html = wppa_input($slug, '50px', '', __('photos', 'wppa'));
								$slug = 'wppa_'.$role.'_upload_limit_time';
								$html .= wppa_select($slug, $options, $values);
								wppa_setting(false, '5.'.$role, $name, $desc, $html, $help);
							}
							
							$name = __('Upload one only', 'wppa');
							$desc = __('Non admin users can upload only one photo at a time.', 'wppa');
							$help = '';
							$slug = 'wppa_upload_one_only';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '6', $name, $desc, $html, $help);

							$name = __('Upload moderation', 'wppa');
							$desc = __('Uploaded photos need moderation.', 'wppa');
							$help = esc_js(__('If checked, photos uploaded by users who do not have photo album admin access rights need moderation.', 'wppa'));
							$help .= esc_js(__('Users who have photo album admin access rights can change the photo status to publish or featured.', 'wppa'));
							$help .= '\n\n'.esc_js(__('You can set the album admin access rights in Table VII-A.', 'wppa'));
							$slug = 'wppa_upload_moderate';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '7', $name, $desc, $html, $help);

							$name = __('Upload notify', 'wppa');
							$desc = __('Notify admin at frontend upload.', 'wppa');
							$help = esc_js(__('If checked, admin will receive a notification by email.', 'wppa'));
							$slug = 'wppa_upload_notify';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '8', $name, $desc, $html, $help);

							$name = __('Upload backend notify', 'wppa');
							$desc = __('Notify admin at backend upload.', 'wppa');
							$help = esc_js(__('If checked, admin will receive a notification by email.', 'wppa'));
							$slug = 'wppa_upload_backend_notify';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '9', $name, $desc, $html, $help);
							
							wppa_setting_subheader( 'C', '1', __('Admin Functionality restrictions for non administrators', 'wppa' ) );
							
							$name = __('Alt thumb is restricted', 'wppa');
							$desc = __('Using <b>alt thumbsize</b> is a restricted action.', 'wppa');
							$help = esc_js(__('If checked: alt thumbsize can not be set in album admin by users not having admin rights.', 'wppa'));
							$slug = 'wppa_alt_is_restricted';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Link is restricted', 'wppa');
							$desc = __('Using <b>Link to</b> is a restricted action.', 'wppa');
							$help = esc_js(__('If checked: Link to: can not be set in album admin by users not having admin rights.', 'wppa'));
							$slug = 'wppa_link_is_restricted';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('CoverType is restricted', 'wppa');
							$desc = __('Changing <b>Cover Type</b> is a restricted action.', 'wppa');
							$help = esc_js(__('If checked: Cover Type: can not be set in album admin by users not having admin rights.', 'wppa'));
							$slug = 'wppa_covertype_is_restricted';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('Photo order# is restricted', 'wppa');
							$desc = __('Changing <b>Photo sort order #</b> is a restricted action.', 'wppa');
							$help = esc_js(__('If checked: Photo sort order #: can not be set in photo admin by users not having admin rights.', 'wppa'));
							$slug = 'wppa_porder_restricted';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							
							$name = __('Change source restricted', 'wppa');
							$desc = __('Changing the import source dir requires admin rights.', 'wppa');
							$help = esc_js(__('If checked, the imput source for importing photos and albums is restricted to user role administrator.', 'wppa'));
							$slug = 'wppa_chgsrc_is_restricted';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '5', $name, $desc, $html, $help);

							wppa_setting_subheader('D', '1', __('Miscellaneous limiting settings', 'wppa'));
							
							$name = __('Owners only', 'wppa');
							$desc = __('Limit album access to the album owners only.', 'wppa');
							$help = esc_js(__('If checked, users who can edit albums and/or upload/import photos can do that with their own albums and --- public --- albums only.', 'wppa')); 
							$help .= '\n'.esc_js(__('Users can give their albums to another user. Administrators can change ownership and access all albums always.', 'wppa'));
							$slug = 'wppa_owner_only';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Uploader Edit', 'wppa');
							$desc = __('Allow the uploader to edit the photo info', 'wppa');
							$help = esc_js(__('If checked, any logged in user that has upload rights and uploads an image has the capability to edit the photo information.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Note: This may be AFTER moderation!!', 'wppa'));
							$slug = 'wppa_upload_edit';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('Upload memory check frontend', 'wppa');
							$desc = __('Disable uploading photos that are too large.', 'wppa');
							$help = esc_js(__('To prevent out of memory crashes during upload and possible database inconsistencies, uploads can be prevented if the photos are too big.', 'wppa'));
							$slug = 'wppa_memcheck_frontend';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('Upload memory check admin', 'wppa');
							$desc = __('Disable uploading photos that are too large.', 'wppa');
							$help = esc_js(__('To prevent out of memory crashes during upload and possible database inconsistencies, uploads can be prevented if the photos are too big.', 'wppa'));
							$slug = 'wppa_memcheck_admin';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							
							$name = __('Comment captcha', 'wppa');
							$desc = __('Use a simple calculate captcha on comments form.', 'wppa');
							$help = '';
							$slug = 'wppa_comment_captcha';
							$html = wppa_checkbox($slug);
							$class = 'wppa_comment_';
							wppa_setting($slug, '5', $name, $desc, $html, $help, $class);
							
							$name = __('Spam lifetime', 'wppa');
							$desc = __('Delete spam comments when older than.', 'wppa');
							$help = '';
							$slug = 'wppa_spam_maxage';
							$options = array(__('--- off ---', 'wppa'), __('10 minutes', 'wppa'), __('half an hour', 'wppa'), __('one hour', 'wppa'), __('one day', 'wppa'), __('one week', 'wppa'));
							$values = array('none', '600', '1800', '3600', '86400', '604800');
							$html = wppa_select($slug, $options, $values);
							$class = 'wppa_comment_';
							wppa_setting($slug, '6', $name, $desc, $html, $help, $class);

							?>
						</tbody>
						<tfoot style="font-weight: bold;" class="wppa_table_7">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Setting', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			
			<?php // Table 8: Actions ?>
			<?php wppa_settings_box_header(
				'8',
				__('Table VIII:', 'wppa').' '.__('Actions:', 'wppa').' '.
				__('This table lists all actions that can be taken to the wppa+ system', 'wppa')
			); ?>
			
				<div id="wppa_table_8" style="display:none" >
					<table class="widefat wppa-table wppa-setting-table">
						<thead style="font-weight: bold; " class="wppa_table_8">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Specification', 'wppa') ?></td>
								<td><?php _e('Do it!', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</thead>
						<tbody class="wppa_table_8">
							<?php 
							$wppa_table = 'VIII';
							
							wppa_setting_subheader('A', '2', __('Harmless and reverseable actions', 'wppa'));
							
							$name = __('Setup', 'wppa');
							$desc = __('Re-initialize plugin.', 'wppa');
							$help = esc_js(__('Re-initilizes the plugin, (re)creates database tables and sets up default settings and directories if required.', 'wppa'));
							$help .= '\n\n'.esc_js(__('This action may be required to setup blogs in a multiblog (network) site as well as in rare cases to correct initilization errors.', 'wppa'));
							$slug = 'wppa_setup';
							$html1 = '';
							$html2 = wppa_doit_button('', $slug);
							$html = array($html1, $html2);
							wppa_setting(false, '1', $name, $desc, $html, $help);

							$name = __('Backup settings', 'wppa');
							$desc = __('Save all settings into a backup file.', 'wppa');
							$help = esc_js(__('Saves all the settings into a backup file', 'wppa'));
							$slug = 'wppa_backup';
							$html1 = '';
							$html2 = wppa_doit_button('', $slug);
							$html = array($html1, $html2);
							wppa_setting(false, '2', $name, $desc, $html, $help);
							
							$name = __('Load settings', 'wppa');
							$desc = __('Restore all settings from defaults, a backup or skin file.', 'wppa');
							$help = esc_js(__('Restores all the settings from the factory supplied defaults, the backup you created or from a skin file.', 'wppa'));
							$slug1 = 'wppa_skinfile';
							$slug2 = 'wppa_load_skin';
							$files = glob(WPPA_PATH.'/theme/*.skin');
							
							$options = false;
							$values = false;
							$options[] = __('--- set to defaults ---', 'wppa');
							$values[] = 'default';
							if (is_file(WPPA_DEPOT_PATH.'/settings.bak')) {
								$options[] = __('--- restore backup ---', 'wppa');
								$values[] = 'restore';
							}
							if ( count($files) ) {
								foreach ($files as $file) {
									$fname = basename($file);
									$ext = strrchr($fname, '.');
									if ( $ext == '.skin' )  {
										$options[] = $fname;
										$values[] = $file;
									}
								}
							}
							$html1 = wppa_select($slug1, $options, $values);
							$html2 = wppa_doit_button('', $slug2);
							$html = array($html1, $html2);
							wppa_setting(false, '3', $name, $desc, $html, $help);

							$name = __('Regenerate', 'wppa');
							$desc = __('Regenerate all thumbnails.', 'wppa');
							$help = esc_js(__('Regenerate all thumbnails.', 'wppa'));
							$slug = 'wppa_regen';
							$html1 = '';
							$html2 = wppa_ajax_button('', $slug);
							$html = array($html1, $html2);
							wppa_setting(false, '4', $name, $desc, $html, $help);

							$name = __('Rerate', 'wppa');
							$desc = __('Recalculate ratings.', 'wppa');
							$help = esc_js(__('This function will recalculate all mean photo ratings from the ratings table.', 'wppa'));
							$help .= '\n'.esc_js(__('You may need this function after the re-import of previously exported photos', 'wppa'));
							$slug = 'wppa_rerate';
							$html1 = '';
							$html2 = wppa_ajax_button('', $slug);
							$html = array($html1, $html2);
							wppa_setting(false, '5', $name, $desc, $html, $help);

							$name = __('Cleanup', 'wppa');
							$desc = __('Fix and secure WPPA+ system consistency', 'wppa');
							$help = esc_js(__('This function will cleanup incomplete db entries and recover lost photos.', 'wppa'));
							$slug = 'wppa_cleanup';
							$html1 = '';
							$html2 = wppa_doit_button('', $slug);
							$html = array($html1, $html2);
							wppa_setting(false, '6', $name, $desc, $html, $help);
							
							$name = __('Recuperate', 'wppa');
							$desc = 'Recuperate IPTC and EXIF data from photos in WPPA+.';
							$help = esc_js(__('This action will attempt to find and register IPTC and EXIF data from photos in the WPPA+ system.', 'wppa'));
							$help .= '\n\n'.esc_js(__('WARNING: Photos that have been downzised during upload/import will have NO IPTC and/or EXIF data.', 'wppa'));
							$help .= '\n'.esc_js(__('If you want that data, you will have to re-import the original files. Use the update switch. You may resize them again.', 'wppa'));
							$slug = 'wppa_recup';
							$html1 = '';
							$usr = get_option('wppa_recuperating_user', '');
							if ( $usr && $usr != wppa_get_user('login') ) {
								$html2 = '<b style="color:red" >'.sprintf(__('Is currently being executed by %s', 'wppa'), $usr);
							}
							else {
								$html2 = wppa_doit_button('', $slug);
							}
							$html = array($html1, $html2);
							wppa_setting(false, '7', $name, $desc, $html, $help);
							
							$name = __('Remake Index', 'wppa');
							$desc = __('Remakes the index database table.', 'wppa');
							$help = '';
							$slug = 'wppa_remake_index';
							$html1 = '';
							$usr = get_option('wppa_indexing_user', '');
							if ( $usr && $usr != wppa_get_user('login') ) {
								$html2 = '<b style="color:red" >'.sprintf(__('Is currently being executed by %s', 'wppa'), $usr);
							}
							else {
								$html2 = wppa_doit_button('', $slug);
							}
							$html = array($html1, $html2);
							$class = 'index_search';
							wppa_setting(false, '8', $name, $desc, $html, $help, $class);
							
							$name = __('List Index', 'wppa');
							$desc = __('Show the content if the index table.', 'wppa');
							$help = '';
							$slug = 'wppa_list_index';
							$html1 = '';
							$html2 = wppa_doit_button('', $slug);
							$class = 'index_search';
							$html = array($html1, $html2);
							wppa_setting(false, '9', $name, $desc, $html, $help, $class);
							
							if ( get_option('wppa_file_system') == 'flat' || get_option('wppa_file_system') == 'tree' ) {	// Not if currently converting
								if ( get_option('wppa_file_system') == 'flat' ) {
									$name = __('Convert to tree', 'wppa');
									$desc = __('Convert filesystem to tree structure.', 'wppa');
								}
								if ( get_option('wppa_file_system') == 'tree' ) {
									$name = __('Convert to flat', 'wppa');
									$desc = __('Convert filesystem to flat structure.', 'wppa');
								}
								$help = esc_js(__('If you want to go back to a wppa+ version prior to 5.0.16, you MUST convert to flat first.', 'wppa'));
								$slug = 'wppa_file_system';
								$html1 = '';
								$html2 = wppa_doit_button('', $slug);
								$html = array($html1, $html2);
								wppa_setting(false, '10', $name, $desc, $html, $help);
							}
							
							$name = __('Blacklist user', 'wppa');
							$desc = __('Set the status of all the users photos to \'pending\'.', 'wppa');
							$help = esc_js(__('Set the status of all the users photos to \'pending\'.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Also inhibits further uploads.', 'wppa'));
							$slug = 'wppa_blacklist_user';
					//		$users = wppa_get_users();	// Already known
							$blacklist = get_option( 'wppa_black_listed_users', array() );
							$options = array( __('-- select a user to blacklist ---', 'wppa') );
							$values = array( '0' );
							foreach ( $users as $usr ) {
								if ( ! wppa_user_is( 'administrator', $usr['ID'] ) ) {	// an administrator can not be blacklisted
									if ( ! in_array( $usr['user_login'], $blacklist ) ) {	// skip already on blacklist
										$options[] = $usr['display_name'].' ('.$usr['user_login'].')';
										$values[]  = $usr['user_login'];
									}
								}
							}
							$onchange = 'alert(\''.__('The page will be reloaded after the action has taken place.', 'wppa').'\');wppaRefreshAfter();';
							$html1 = wppa_select($slug, $options, $values, $onchange);
							$html2 = '';
							$html = array( $html1, $html2 );
							wppa_setting(false, '11', $name, $desc, $html, $help);

							$name = __('Unblacklist user', 'wppa');
							$desc = __('Set the status of all the users photos to \'publish\'.', 'wppa');
							$help = '';
							$slug = 'wppa_un_blacklist_user';
							$blacklist = get_option( 'wppa_black_listed_users', array() );
							$options = array( __('-- select a user to unblacklist ---', 'wppa') );
							$values = array( '0' );
					//		$users = wppa_get_users();	// Already known
							foreach ( $users as $usr ) {
								if ( in_array( $usr['user_login'], $blacklist ) ) {
									$options[] = $usr['display_name'].' ('.$usr['user_login'].')';
									$values[]  = $usr['user_login'];
								}
							}
							$onchange = 'alert(\''.__('The page will be reloaded after the action has taken place.', 'wppa').'\');wppaRefreshAfter();';
							$html1 = wppa_select($slug, $options, $values, $onchange);
							$html2 = '';
							$html = array( $html1, $html2 );
							wppa_setting(false, '12', $name, $desc, $html, $help);

							wppa_setting_subheader('B', '2', __('Clearing and other irreverseable actions', 'wppa'));
							
							$name = __('Clear ratings', 'wppa');
							$desc = __('Reset all ratings.', 'wppa');
							$help = esc_js(__('WARNING: If checked, this will clear all ratings in the system!', 'wppa'));
							$slug = 'wppa_rating_clear';
							$html1 = '';
							$html2 = wppa_ajax_button('', $slug);
							$html = array($html1, $html2);
							wppa_setting(false, '1', $name, $desc, $html, $help);
							
							$name = __('Reset IPTC', 'wppa');
							$desc = __('Clear all IPTC data.', 'wppa');
							$help = esc_js(__('WARNING: If checked, this will clear all IPTC data in the system!', 'wppa'));
							$slug = 'wppa_iptc_clear';
							$html1 = '';
							$html2 = wppa_ajax_button('', $slug);
							$html = array($html1, $html2);
							wppa_setting(false, '2', $name, $desc, $html, $help);

							$name = __('Reset EXIF', 'wppa');
							$desc = __('Clear all EXIF data.', 'wppa');
							$help = esc_js(__('WARNING: If checked, this will clear all EXIF data in the system!', 'wppa'));
							$slug = 'wppa_exif_clear';
							$html1 = '';
							$html2 = wppa_ajax_button('', $slug);
							$html = array($html1, $html2);
							wppa_setting(false, '3', $name, $desc, $html, $help);
							
							$name = __('Apply New Photodesc', 'wppa');
							$desc = __('Apply New photo description on all photos in the system.', 'wppa');
							$help = '';
							$slug = 'wppa_apply_new_photodesc_all';
							$html1 = '';
							$html2 = wppa_ajax_button('', $slug);
							$html = array($html1, $html2);
							wppa_setting(false, '4', $name, $desc, $html, $help);
							
							$name = __('Append to photodesc', 'wppa');
							$desc = __('Append this text to all photo descriptions.', 'wppa');
							$help = 'Appends a space character and the given text to the description of all photos.';
							$help .= '\n\n'.esc_js('First edit the text to append, click outside the edit window and wait for the green checkmark to appear. Then click the Doit! button.');
							$help .= '\n\n'.esc_js('If this operation does not finish with a green \"Done! Appending...\" message, but a \"Time out\" message or simply dies after a minute or so,');
							$help .= esc_js('wait another minute, and press the Doit! button again. The process will continue where it ended.');
							$help .= '\n\n'.esc_js('Go on this way until a \"Done!\" message appears. Do NOT change the append text in between!');
							$slug1 = 'wppa_append_text';
							$slug2 = 'wppa_append_to_photodesc';
							$html1 = wppa_input($slug1, '200px');
							$html2 = wppa_doit_button('', $slug2);
							$html = array($html1, $html2);
							wppa_setting(false, '5', $name, $desc, $html, $help);
							
							$name = __('Remove from photodesc', 'wppa');
							$desc = __('Remove this text from all photo descriptions.', 'wppa');
							$help = 'Removes all occurrencies of the given text from the description of all photos.';
							$help .= '\n\n'.esc_js('First edit the text to remove, click outside the edit window and wait for the green checkmark to appear. Then click the Doit! button.');
							$help .= '\n\n'.esc_js('If this operation does not finish with a green \"Done! Removing...\" message, but a \"Time out\" message or simply dies after a minute or so,');
							$help .= esc_js('wait another minute, and press the Doit! button again. The process will continue where it ended.');
							$help .= '\n\n'.esc_js('Go on this way until a \"Done!\" message appears. Do NOT change the remove text in between!');
							$slug1 = 'wppa_remove_text';
							$slug2 = 'wppa_remove_from_photodesc';
							$html1 = wppa_input($slug1, '200px');
							$html2 = wppa_doit_button('', $slug2);
							$html = array($html1, $html2);
							wppa_setting(false, '6', $name, $desc, $html, $help);

							$name = __('Remove empty albums', 'wppa');
							$desc = __('Removes albums that are not used.', 'wppa');
							$help = '';
							$slug2 = 'wppa_remove_empty_albums';
							$html1 = '';
							$html2 = wppa_ajax_button('', $slug2);
							$html = array($html1, $html2);
							wppa_setting(false, '7', $name, $desc, $html, $help);
							
							?>
						</tbody>
						<tfoot style="font-weight: bold;" class="wppa_table_8">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Specification', 'wppa') ?></td>
								<td><?php _e('Do it!', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			
			<?php // Table 9: Miscellaneous ?>
			<?php wppa_settings_box_header(
				'9',
				__('Table IX:', 'wppa').' '.__('Miscellaneous:', 'wppa').' '.
				__('This table lists all settings that do not fit into an other table', 'wppa')
			); ?>
			
				<div id="wppa_table_9" style="display:none" >
					<table class="widefat wppa-table wppa-setting-table">
						<thead style="font-weight: bold; " class="wppa_table_9">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Setting', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</thead>
						<tbody class="wppa_table_9">
							<?php
							$wppa_table = 'IX';
							
							wppa_setting_subheader( 'A', '1', __( 'Internal engine related settings', 'wppa' ) );
							{
							$name = __('WPPA+ Filter priority', 'wppa');
							$desc = __('Sets the priority of the wppa+ content filter.', 'wppa');
							$help = esc_js(__('If you encounter conflicts with the theme or other plugins, increasing this value sometimes helps. Use with great care!', 'wppa'));
							$slug = 'wppa_filter_priority';
							$html = wppa_input($slug, '50px');
							wppa_setting($slug, '1', $name, $desc, $html, $help);
			
							$name = __('JPG image quality', 'wppa');
							$desc = __('The jpg quality when photos are downsized', 'wppa');
							$help = esc_js(__('The higher the number the better the quality but the larger the file', 'wppa'));
							$help .= '\n'.esc_js(__('Possible values 20..100', 'wppa'));
							$slug = 'wppa_jpeg_quality';
							$html = wppa_input($slug, '50px');
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('Allow WPPA+ Debugging', 'wppa');
							$desc = __('Allow the use of &amp;debug=.. in urls to this site.', 'wppa');
							$help = esc_js(__('If checked: appending (?)(&)debug or (?)(&)debug=<int> to an url to this site will generate the display of special WPPA+ diagnostics, as well as php warnings', 'wppa'));
							$slug = 'wppa_allow_debug';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '3', $name, $desc, $html, $help);

							$name = __('Auto continue', 'wppa');
							$desc = __('Continue automatic after time out', 'wppa');
							$help = esc_js(__('If checked, an attempt will be made to restart an admin process when the time is out.', 'wppa'));
							$slug = 'wppa_auto_continue';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							
							$name = __('Max execution time', 'wppa');
							$desc = __('Set max execution time here.', 'wppa');
							$help = esc_js(__('If your php config does not properly set the max execution time, you can set it here. Seconds, 0 means do not change.', 'wppa'));
							$help .= '\n'.esc_js(__('A safe value is 45', 'wppa'));
							$slug = 'wppa_max_execution_time';
							$html = wppa_input($slug, '50px', '', 'seconds');
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							
							$name = __('Feed use thumb', 'wppa');
							$desc = __('Feeds use thumbnail pictures always.', 'wppa');
							$help = '';
							$slug = 'wppa_feed_use_thumb';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'B', '1', __( 'WPPA+ Admin related miscellaneous settings', 'wppa' ) );
							{
							$name = __('Allow HTML', 'wppa');
							$desc = __('Allow HTML in album and photo descriptions.', 'wppa');
							$help = esc_js(__('If checked: html is allowed. WARNING: No checks on syntax, it is your own responsability to close tags properly!', 'wppa'));
							$slug = 'wppa_html';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '1', $name, $desc, $html, $help);

							$name = __('Check tag balance', 'wppa');
							$desc = __('Check if the HTML tags are properly closed: "balanced".', 'wppa');
							$help = esc_js(__('If the HTML tags in an album or a photo description are not in balance, the description is not updated, an errormessage is displayed', 'wppa'));
							$slug = 'wppa_check_balance';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('Use WP editor', 'wppa');
							$desc = __('Use the wp editor for multiline text fields.', 'wppa');
							$help = '';
							$slug = 'wppa_use_wp_editor';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '3', $name, $desc, $html, $help);

							$name = __('Album sel hierarchic', 'wppa');
							$desc = __('Show albums with (grand)parents in selection lists.', 'wppa');
							$help = '';
							$slug = 'wppa_hier_albsel';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '4', $name, $desc, $html, $help);

							$name = __('Page sel hierarchic', 'wppa');
							$desc = __('Show pages with (grand)parents in selection lists.', 'wppa');
							$help = '';
							$slug = 'wppa_hier_pagesel';
							$warn = 'This setting will be effective after reload of the page';
							$html = wppa_checkbox_warn($slug, '', '', $warn);
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							
							$name = __('Photo admin page size', 'wppa');
							$desc = __('The number of photos per page on the Edit Album -> Manage photos and Edit Photos admin pages.', 'wppa');
							$help = '';
							$slug = 'wppa_photo_admin_pagesize';
							$options = array( __('--- off ---', 'wppa'), '10', '20', '50', '100', '200');
							$values = array('0', '10', '20', '50', '100', '200');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							
							$name = __('Comment admin page size', 'wppa');
							$desc = __('The number of comments per page on the Comments admin pages.', 'wppa');
							$help = '';
							$slug = 'wppa_comment_admin_pagesize';
							$options = array( __('--- off ---', 'wppa'), '10', '20', '50', '100', '200');
							$values = array('0', '10', '20', '50', '100', '200');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '7', $name, $desc, $html, $help);

							$name = __('Geo info edit', 'wppa');
							$desc = __('Lattitude and longitude may be edited in photo admin.', 'wppa');
							$help = '';
							$slug = 'wppa_geo_edit';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '8', $name, $desc, $html, $help);

							$name = __('Admin bar menu admin', 'wppa');
							$desc = __('Show menu on admin bar on admin pages.', 'wppa');
							$help = '';
							$slug = 'wppa_adminbarmenu_admin';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '9', $name, $desc, $html, $help);

							$name = __('Admin bar menu frontend', 'wppa');
							$desc = __('Show menu on admin bar on frontend pages.', 'wppa');
							$help = '';
							$slug = 'wppa_adminbarmenu_frontend';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '10', $name, $desc, $html, $help);

							$name = __('Add shortcode to posts', 'wppa');
							$desc = __('Add a shortcode to the end of all posts.', 'wppa');
							$help = '';
							$slug = 'wppa_add_shortcode_to_post';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '11', $name, $desc, $html, $help);
							
							$name = __('Shortcode to add', 'wppa');
							$desc = __('The shortcode to be added to the posts.', 'wppa');
							$help = '';
							$slug = 'wppa_shortcode_to_add';
							$html = wppa_input($slug, '300px');
							wppa_setting($slug, '12', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'C', '1', __( 'SEO related settings', 'wppa' ) );
							{
							$name = __('Meta on page', 'wppa');
							$desc = __('Meta tags for photos on the page.', 'wppa');
							$help = esc_js(__('If checked, the header of the page will contain metatags that refer to featured photos on the page in the page context.', 'wppa'));
							$slug = 'wppa_meta_page';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Meta all', 'wppa');
							$desc = __('Meta tags for all featured photos.', 'wppa');
							$help = esc_js(__('If checked, the header of the page will contain metatags that refer to all featured photo files.', 'wppa'));
							$help .= '\n'.esc_js(__('If you have many featured photos, you might wish to uncheck this item to reduce the size of the page header.', 'wppa'));
							$slug = 'wppa_meta_all';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('Add og meta tags', 'wppa');
							$desc = __('Add og meta tags to the page header.', 'wppa');
							$help = '';
							$slug = 'wppa_og_tags_on';
							$warn = esc_js(__('Turning this off may affect the functionality of social media items in the share box that rely on open graph tags information.', 'wppa'));
							$html = wppa_checkbox_warn_off($slug, '', '', $warn, false);
							wppa_setting($slug, '3', $name, $desc, $html, $help);

							$name = __('Image Alt attribute type', 'wppa');
							$desc = __('Select kind of HTML alt="" content for images.', 'wppa');
							$help = '';
							$slug = 'wppa_alt_type';
							$options = array( __('--- none ---', 'wppa'), __('photo name', 'wppa'), __('name without file-ext', 'wppa'), __('set in album admin', 'wppa') );
							$values = array( 'none', 'fullname', 'namenoext', 'custom');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'D', '1', __( 'New Album and New Photo related miscellaneous settings', 'wppa' ) );
							{
							$name = __('New Album', 'wppa');
							$desc = __('Maximum time an album is indicated as New!', 'wppa');
							$help = '';
							$slug = 'wppa_max_album_newtime';
							$options = array( __('--- off ---', 'wppa'), __('One hour', 'wppa'), __('One day', 'wppa'), __('One week', 'wppa'), __('One month', 'wppa') );
							$values = array( 0, 60*60, 60*60*24, 60*60*24*7, 60*60*24*30);
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '1', $name, $desc, $html, $help);

							$name = __('New Photo', 'wppa');
							$desc = __('Maximum time a photo is indicated as New!', 'wppa');
							$help = '';
							$slug = 'wppa_max_photo_newtime';
							$options = array( __('--- off ---', 'wppa'), __('One hour', 'wppa'), __('One day', 'wppa'), __('One week', 'wppa'), __('One month', 'wppa') );
							$values = array( 0, 60*60, 60*60*24, 60*60*24*7, 60*60*24*30);
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('Apply Newphoto desc', 'wppa');
							$desc = __('Give each new photo a standard description.', 'wppa');
							$help = esc_js(__('If checked, each new photo will get the description (template) as specified in the next item.', 'wppa'));
							$slug = 'wppa_apply_newphoto_desc';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '3', $name, $desc, $html, $help);

							$name = __('Apply Newphoto desc user', 'wppa');
							$desc = __('Give each new frontend uploaded photo a standard description.', 'wppa');
							$help = esc_js(__('If checked, each new photo will get the description (template) as specified in the next item.', 'wppa'));
							$slug = 'wppa_apply_newphoto_desc_user';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '4', $name, $desc, $html, $help);

							$name = __('New photo desc', 'wppa');
							$desc = __('The description (template) to add to a new photo.', 'wppa');
							$help = esc_js(__('Enter the default description.', 'wppa'));
							$help .= '\n\n'.esc_js(__('If you use html, please check item A-1 of this table.', 'wppa'));
							$slug = 'wppa_newphoto_description';
							$html = wppa_textarea($slug, $name);
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							
							$name = __('Upload limit', 'wppa');
							$desc = __('New albums are created with this upload limit.', 'wppa');
							$help = esc_js(__('Administrators can change the limit settings in the "Edit Album Information" admin page.', 'wppa'));
							$help .= '\n'.esc_js(__('A value of 0 means: no limit.', 'wppa'));
							$slug = 'wppa_upload_limit_count';
							$html = wppa_input($slug, '50px', '', __('photos', 'wppa'));
							$slug = 'wppa_upload_limit_time';
							$options = array( 	__('for ever', 'wppa'), 
												__('per hour', 'wppa'), 
												__('per day', 'wppa'), 
												__('per week', 'wppa'), 
												__('per month', 'wppa'), 	// 30 days
												__('per year', 'wppa'));	// 364 days
							$values = array( '0', '3600', '86400', '604800', '2592000', '31449600');
							$html .= wppa_select($slug, $options, $values);
							wppa_setting(false, '6', $name, $desc, $html, $help);

							$name = __('Default parent', 'wppa');
							$desc = __('The parent album of new albums.', 'wppa');
							$help = '';
							$slug = 'wppa_default_parent';
							$opts = array( __('--- none ---', 'wppa'), __('--- separate ---', 'wppa') );
							$vals = array( '0', '-1');
							$albs = $wpdb->get_results( "SELECT `id`, `name` FROM`" . WPPA_ALBUMS . "` ORDER BY `name`", ARRAY_A );
							if ( $albs ) {
								foreach ( $albs as $alb ) {
									$opts[] = __(stripslashes($alb['name']));
									$vals[] = $alb['id'];
								}
							}
							$html = wppa_select($slug, $opts, $vals);
							wppa_setting($slug, '7', $name, $desc, $html, $help);

							$name = __('Show album full', 'wppa');
							$desc = __('Show the Upload limit reached message if appropriate.', 'wppa');
							$help = '';
							$slug = 'wppa_show_album_full';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '8', $name, $desc, $html, $help);
							
							$name = __('Grant an album', 'wppa');
							$desc = __('Create an album for each user logging in.', 'wppa');
							$help = '';
							$slug = 'wppa_grant_an_album';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '9', $name, $desc, $html, $help);

							$name = __('Grant album name', 'wppa');
							$desc = __('The name to be used for the album.', 'wppa');
							$help = '';
							$slug = 'wppa_grant_name';
							$opts = array(__('Login name', 'wppa'), __('Display name', 'wppa'), __('Id', 'wppa'), __('Firstname Lastname', 'wppa'));
							$vals = array('login', 'display', 'id', 'firstlast');
							$html = wppa_select($slug, $opts, $vals);
							wppa_setting($slug, '10', $name, $desc, $html, $help);
							
							$name = __('Grant parent', 'wppa');
							$desc = __('The parent album of the auto created albums.', 'wppa');
							$help = '';
							$slug = 'wppa_grant_parent';
							$opts = array( __('--- none ---', 'wppa'), __('--- separate ---', 'wppa') );
							$vals = array( '0', '-1');
							$albs = $wpdb->get_results( "SELECT `id`, `name` FROM`" . WPPA_ALBUMS . "` ORDER BY `name`", ARRAY_A );
							if ( $albs ) {
								foreach ( $albs as $alb ) {
									$opts[] = __(stripslashes($alb['name']));
									$vals[] = $alb['id'];
								}
							}
							$html = wppa_select($slug, $opts, $vals);
							wppa_setting($slug, '11', $name, $desc, $html, $help);
							
							$name = __('Max user albums', 'wppa');
							$desc = __('The max number of albums a user can create.', 'wppa');
							$help = esc_js(__('The maximum number of albums a user can create when he is not admin and owner only is active', 'wppa'));
							$help .= '\n\n'.esc_js(__('A number of 0 means No limit', 'wppa'));
							$slug = 'wppa_max_albums';
							$html = wppa_input($slug, '50px', '', 'albums');
							wppa_setting($slug, '12', $name, $desc, $html, $help);
							
							$name = __('Default photo name', 'wppa');
							$desc = __('Select the way the name of a new uploaded photo should be determined.', 'wppa');
							$help = esc_js('If you select an IPTC Tag and it is not found, the filename will be used instead.', 'wppa');
							$slug = 'wppa_newphoto_name_method';
							$opts = array( 	__('Filename', 'wppa'), 
											__('Filename without extension', 'wppa'), 
											__('IPTC Tag 2#005 (Graphic name)', 'wppa'),
											__('IPTC Tag 2#120 (Caption)', 'wppa')											
										);
							$vals = array( 'filename', 'noext', '2#005', '2#120' );
							$html = wppa_select($slug, $opts, $vals);
							wppa_setting($slug, '13', $name, $desc, $html, $help);
							
							$name = __('Copy Timestamp', 'wppa');
							$desc = __('Copy timestamp when copying photo.', 'wppa');
							$help = esc_js('If checked, the copied photo is not "new"', 'wppa');
							$slug = 'wppa_copy_timestamp';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '14', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'E', '1', __( 'Search Albums and Photos related settings', 'wppa' ) );
							{
							$name = __('Search page', 'wppa');
							$desc = __('Display the search results on page.', 'wppa');
							$help = esc_js(__('Select the page to be used to display search results. The page MUST contain %%wppa%% or [wppa][/wppa].', 'wppa'));
							$help .= '\n'.esc_js(__('You may give it the title "Search results" or something alike.', 'wppa'));
							$help .= '\n'.esc_js(__('Or you ou may use the standard page on which you display the generic album.', 'wppa'));
							$slug = 'wppa_search_linkpage';
							wppa_verify_page($slug);
							$query = "SELECT ID, post_title, post_content FROM " . $wpdb->posts . " WHERE post_type = 'page' AND post_status = 'publish' ORDER BY post_title ASC";
							$pages = $wpdb->get_results($query, ARRAY_A);
							$options = false;
							$values = false;
							$options[] = __('--- Please select a page ---', 'wppa');
							$values[] = '0';
							if ($pages) {
								if ( $wppa_opt['wppa_hier_pagesel'] ) $pages = wppa_add_parents($pages);
								else {	// Just translate
									foreach ( array_keys($pages) as $index ) {
										$pages[$index]['post_title'] = __(stripslashes($pages[$index]['post_title']));
									}
								}
								$pages = wppa_array_sort($pages, 'post_title');
								foreach ($pages as $page) {
									if ( strpos($page['post_content'], '%%wppa%%') !== false || strpos($page['post_content'], '[wppa') !== false ) {
										$options[] = __($page['post_title']);
										$values[] = $page['ID'];
									}
									else {
										$options[] = '|'.__($page['post_title']).'|';
										$values[] = $page['ID'];
									}
								}
							}
							$html = wppa_select($slug, $options, $values, '', '', true);
							wppa_setting(false, '1', $name, $desc, $html, $help);
							
							$name = __('Exclude separate', 'wppa');
							$desc = __('Do not search \'separate\' albums.', 'wppa');
							$help = esc_js(__('When checked, albums (and photos in them) that have the parent set to --- separate --- will be excluded from being searched.', 'wppa'));
							$slug = 'wppa_excl_sep';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '2', $name, $desc, $html, $help);

							$name = __('Include tags', 'wppa');
							$desc = __('Do also search the photo tags.', 'wppa');
							$help = esc_js(__('When checked, the tags of the photo will also be searched.', 'wppa'));
							$slug = 'wppa_search_tags';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '3', $name, $desc, $html, $help);
							
							$name = __('Include categories', 'wppa');
							$desc = __('Do also search the album categories.', 'wppa');
							$help = esc_js(__('When checked, the categories of the album will also be searched.', 'wppa'));
							$slug = 'wppa_search_cats';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							
							$name = __('Include comments', 'wppa');
							$desc = __('Do also search the comments on photos.', 'wppa');
							$help = esc_js(__('When checked, the comments of the photos will also be searched.', 'wppa'));
							$slug = 'wppa_search_comments' ;
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							
							$name = __('Photos only', 'wppa');
							$desc = __('Search for photos only.', 'wppa');
							$help = esc_js(__('When checked, only photos will be searched for.', 'wppa'));
							$slug = 'wppa_photos_only';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							
							$name = __('Indexed search', 'wppa');
							$desc = __('Searching uses index db table.', 'wppa');
							$help = '';
							$slug = 'wppa_indexed_search';
							$onchange = 'wppaCheckIndexSearch()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '7', $name, $desc, $html, $help);
							
							$name = __('Max albums found', 'wppa');
							$desc = __('The maximum number of albums to be displayed.', 'wppa');
							$help = '';
							$slug = 'wppa_max_search_albums';
							$html = wppa_input($slug, '50px');
							wppa_setting($slug, '8', $name, $desc, $html, $help);

							$name = __('Max photos found', 'wppa');
							$desc = __('The maximum number of photos to be displayed.', 'wppa');
							$help = '';
							$slug = 'wppa_max_search_photos';
							$html = wppa_input($slug, '50px');
							wppa_setting($slug, '9', $name, $desc, $html, $help);
							
							$name = __('Tags OR only', 'wppa');
							$desc = __('No and / or buttons', 'wppa');
							$help = esc_js(__('Hide the and/or radiobuttons and do the or method in the multitag widget and shortcode.', 'wppa'));
							$slug = 'wppa_tags_or_only';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '10', $name, $desc, $html, $help);
							
							$name = __('Floating searchtoken', 'wppa');
							$desc = __('A match need not start at the first char.', 'wppa');
							$help = esc_js(__('A match is found while searching also when the entered token is somewhere in the middle of a word.', 'wppa'));
							$help .= '\n\n'.esc_js(__('This works in indexed search only!', 'wppa'));
							$slug = 'wppa_wild_front';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '11', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'F', '1', __( 'Watermark related settings', 'wppa' ) );
							{
							$name = __('Watermark', 'wppa');
							$desc = __('Enable the application of watermarks.', 'wppa');
							$help = esc_js(__('If checked, photos can be watermarked during upload / import.', 'wppa'));
							$slug = 'wppa_watermark_on';
							$onchange = 'wppaCheckWatermark()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('User Watermark', 'wppa');
							$desc = __('Uploading users may select watermark settings', 'wppa');
							$help = esc_js(__('If checked, anyone who can upload and/or import photos can overrule the default watermark settings.', 'wppa'));
							$slug = 'wppa_watermark_user';
							$class = 'wppa_watermark';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '2', $name, $desc, $html, $help, $class);
													
							$name = __('Watermark file', 'wppa');
							$desc = __('The default watermarkfile to be used.', 'wppa');
							$help = esc_js(__('Watermark files are of type png and reside in', 'wppa') . ' ' . WPPA_UPLOAD_URL . '/watermarks/');
							$help .= '\n\n'.esc_js(__('A suitable watermarkfile typically consists of a transparent background and a black text or drawing.', 'wppa'));
							$help .= '\n'.esc_js(__('The watermark image will be overlaying the photo with 80% transparency.', 'wppa'));
							$slug = 'wppa_watermark_file';
							$class = 'wppa_watermark';
							$html = '<select style="float:left; font-size:11px; height:20px; margin:0 4px 0 0; padding:0; " id="wppa_watermark_file" onchange="wppaAjaxUpdateOptionValue(\'wppa_watermark_file\', this)" >' . wppa_watermark_file_select('default') . '</select>';
							$html .= '<img id="img_wppa_watermark_file" src="'.wppa_get_imgdir().'star.png" title="'.__('Setting unmodified', 'wppa').'" style="padding-left:4px; float:left; height:16px; width:16px;" />';
							$html .= '<span style="float:left; margin-left:12px;" >'.__('position:', 'wppa').'</span><select style="float:left; font-size:11px; height:20px; margin:0 0 0 20px; padding:0; "  id="wppa_watermark_pos" onchange="wppaAjaxUpdateOptionValue(\'wppa_watermark_pos\', this)" >' . wppa_watermark_pos_select('default') . '</select>';
							$html .= '<img id="img_wppa_watermark_pos" src="'.wppa_get_imgdir().'star.png" title="'.__('Setting unmodified', 'wppa').'" style="padding-left:4px; float:left; height:16px; width:16px;" />';
							wppa_setting(false, '3', $name, $desc, $html, $help, $class);
		
							$name = __('Upload watermark', 'wppa');
							$desc = __('Upload a new watermark file', 'wppa');
							// $help = ''; SAME AS PREVIOUS
							$slug = 'wppa_watermark_upload';
							$html = '<input id="my_file_element" type="file" name="file_1" style="float:left; height:18px; font-size: 11px;" />';
							$html .= wppa_doit_button(__('Upload it!', 'wppa'), $slug);
							wppa_setting(false, '4', $name, $desc, $html, $help, $class);
													
							$name = __('Watermark opacity', 'wppa');
							$desc = __('You can set the intensity of watermarks here.', 'wppa');
							$help = esc_js(__('The higher the number, the intenser the watermark. Value must be > 0 and <= 100.', 'wppa'));
							$slug = 'wppa_watermark_opacity';
							$html = wppa_input($slug, '50px', '', '%');
							wppa_setting($slug, '5', $name, $desc, $html, $help, $class);
							}
							wppa_setting_subheader( 'G', '1', __( 'Slideshow elements sequence order settings', 'wppa' ) );
							{
							if ( get_option('wppa_split_namedesc') == 'yes' ) {
								$indexopt = get_option('wppa_slide_order_split');
								$indexes  = explode(',', $indexopt);
								$names    = array(
									__('StartStop', 'wppa'), 
									__('SlideFrame', 'wppa'), 
									__('Name', 'wppa'), 
									__('Desc', 'wppa'),
									__('Custom', 'wppa'), 
									__('Rating', 'wppa'), 
									__('FilmStrip', 'wppa'), 
									__('Browsebar', 'wppa'), 
									__('Comments', 'wppa'),
									__('IPTC data', 'wppa'),
									__('EXIF data', 'wppa'),
									__('Share box', 'wppa') 
									);
								$enabled  = '<span style="color:green; float:right;">( '.__('Enabled', 'wppa');
								$disabled = '<span style="color:orange; float:right;">( '.__('Disabled', 'wppa');
								$descs = array(
									__('Start/Stop & Slower/Faster navigation bar', 'wppa') . ( $wppa_opt['wppa_show_startstop_navigation'] == 'yes' ? $enabled : $disabled ) . ' II-B1 )</span>',
									__('The Slide Frame', 'wppa') . '<span style="float:right;">'.__('( Always )', 'wppa').'</span>',
									__('Photo Name Box', 'wppa') . ( $wppa_opt['wppa_show_full_name'] == 'yes' ? $enabled : $disabled ) .' II-B5 )</span>',
									__('Photo Description Box', 'wppa') . ( $wppa_opt['wppa_show_full_desc'] == 'yes' ? $enabled : $disabled ) .' II-B6 )</span>',
									__('Custom Box', 'wppa') . ( $wppa_opt['wppa_custom_on'] == 'yes' ? $enabled : $disabled ).' II-B14 )</span>',
									__('Rating Bar', 'wppa') . ( $wppa_opt['wppa_rating_on'] == 'yes' ? $enabled : $disabled ).' II-B7 )</span>',
									__('Film Strip with embedded Start/Stop and Goto functionality', 'wppa') . ( $wppa_opt['wppa_filmstrip'] == 'yes' ? $enabled : $disabled ).' II-B3 )</span>',
									__('Browse Bar with Photo X of Y counter', 'wppa') . ( $wppa_opt['wppa_show_browse_navigation'] == 'yes' ? $enabled : $disabled ).' II-B2 )</span>',
									__('Comments Box', 'wppa') . ( $wppa_opt['wppa_show_comments'] == 'yes' ? $enabled : $disabled ).' II-B10 )</span>',
									__('IPTC box', 'wppa') . ( $wppa_opt['wppa_show_iptc'] == 'yes' ? $enabled : $disabled ).' II-B17 )</span>',
									__('EXIF box', 'wppa') . ( $wppa_opt['wppa_show_exif'] == 'yes' ? $enabled : $disabled ).' II-B18 )</span>',
									__('Social media share box', 'wppa') . ( $wppa_opt['wppa_share_on'] == 'yes' ? $enabled : $disabled ).' II-C1 )</span>'
									);
								$i = '0';
								while ( $i < '12' ) {
									$name = $names[$indexes[$i]];
									$desc = $descs[$indexes[$i]];
									$html = $i == '0' ? '&nbsp;' : wppa_doit_button(__('Move Up', 'wppa'), 'wppa_moveup', $i);
									$help = '';
									$slug = 'wppa_slide_order';
									wppa_setting($slug, $indexes[$i]+1 , $name, $desc, $html, $help);
									$i++;
								}
							}
							else {
								$indexopt = get_option('wppa_slide_order');
								$indexes  = explode(',', $indexopt);
								$names    = array(
									__('StartStop', 'wppa'), 
									__('SlideFrame', 'wppa'), 
									__('NameDesc', 'wppa'), 
									__('Custom', 'wppa'), 
									__('Rating', 'wppa'), 
									__('FilmStrip', 'wppa'), 
									__('Browsebar', 'wppa'), 
									__('Comments', 'wppa'),
									__('IPTC data', 'wppa'),
									__('EXIF data', 'wppa'),
									__('Share box', 'wppa')
									);
								$enabled  = '<span style="color:green; float:right;">( '.__('Enabled', 'wppa');
								$disabled = '<span style="color:orange; float:right;">( '.__('Disabled', 'wppa');
								$descs = array(
									__('Start/Stop & Slower/Faster navigation bar', 'wppa') . ( $wppa_opt['wppa_show_startstop_navigation'] == 'yes' ? $enabled : $disabled ) . ' II-B1 )</span>',
									__('The Slide Frame', 'wppa') . '<span style="float:right;">'.__('( Always )', 'wppa').'</span>',
									__('Photo Name & Description Box', 'wppa') . ( ( $wppa_opt['wppa_show_full_name'] == 'yes' || $wppa_opt['wppa_show_full_desc'] == 'yes' ) ? $enabled : $disabled ) .' II-B5,6 )</span>',
									__('Custom Box', 'wppa') . ( $wppa_opt['wppa_custom_on'] == 'yes' ? $enabled : $disabled ).' II-B14 )</span>',
									__('Rating Bar', 'wppa') . ( $wppa_opt['wppa_rating_on'] == 'yes' ? $enabled : $disabled ).' II-B7 )</span>',
									__('Film Strip with embedded Start/Stop and Goto functionality', 'wppa') . ( $wppa_opt['wppa_filmstrip'] == 'yes' ? $enabled : $disabled ).' II-B3 )</span>',
									__('Browse Bar with Photo X of Y counter', 'wppa') . ( $wppa_opt['wppa_show_browse_navigation'] == 'yes' ? $enabled : $disabled ).' II-B2 )</span>',
									__('Comments Box', 'wppa') . ( $wppa_opt['wppa_show_comments'] == 'yes' ? $enabled : $disabled ).' II-B10 )</span>',
									__('IPTC box', 'wppa') . ( $wppa_opt['wppa_show_iptc'] == 'yes' ? $enabled : $disabled ).' II-B17 )</span>',
									__('EXIF box', 'wppa') . ( $wppa_opt['wppa_show_exif'] == 'yes' ? $enabled : $disabled ).' II-B18 )</span>',
									__('Social media share box', 'wppa') . ( $wppa_opt['wppa_share_on'] == 'yes' ? $enabled : $disabled ).' II-C1 )</span>'
									);
								$i = '0';
								while ( $i < '11' ) {
									$name = $names[$indexes[$i]];
									$desc = $descs[$indexes[$i]];
									$html = $i == '0' ? '&nbsp;' : wppa_doit_button(__('Move Up', 'wppa'), 'wppa_moveup', $i);
									$help = '';
									$slug = 'wppa_slide_order';
									wppa_setting($slug, $indexes[$i]+1 , $name, $desc, $html, $help);
									$i++;
								}
							}
							
							$name = __('Swap Namedesc', 'wppa');
							$desc = __('Swap the order sequence of name and description', 'wppa');
							$help = '';
							$slug = 'wppa_swap_namedesc';
							$html = wppa_checkbox($slug);
							$class = 'swap_namedesc';
							wppa_setting($slug, '12', $name, $desc, $html, $help, $class);
							
							$name = __('Split Name and Desc', 'wppa');
							$desc = __('Put Name and Description in separate boxes', 'wppa');
							$help = '';
							$slug = 'wppa_split_namedesc';
							$html = wppa_checkbox($slug,'alert(\''.__('Please reload this page after the green checkmark appears!', 'wppa').'\');wppaCheckSplitNamedesc();');
							wppa_setting($slug, '13', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'H', '1', __( 'Source file management and other upload/import settings and actions.', 'wppa' ) );
							{
							$name = __('Keep sourcefiles admin', 'wppa');
							$desc = __('Keep the original uploaded and imported photo files.', 'wppa');
							$help = esc_js(__('The files will be kept in a separate directory with subdirectories for each album', 'wppa'));
							$help .= '\n\n'.esc_js(__('These files can be used to update the photos used in displaying in wppa+ and optionally for downloading original, un-downsized images.', 'wppa'));
							$slug = 'wppa_keep_source_admin';
							$onchange = '';
							$warn = __('Switching this on will require a lot of disk space!', 'wppa');
							$html = wppa_checkbox_warn_on($slug, $onchange, '', $warn);
							wppa_setting($slug, '1', $name, $desc, $html, $help);

							$name = __('Keep sourcefiles frontend', 'wppa');
							$desc = __('Keep the original frontend uploaded photo files.', 'wppa');
							$help = esc_js(__('The files will be kept in a separate directory with subdirectories for each album', 'wppa'));
							$help .= '\n\n'.esc_js(__('These files can be used to update the photos used in displaying in wppa+ and optionally for downloading original, un-downsized images.', 'wppa'));
							$slug = 'wppa_keep_source_frontend';
							$warn = __('Switching this on will require a lot of disk space!', 'wppa');
							$html = wppa_checkbox_warn_on($slug, '', '', $warn);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('Source directory', 'wppa');
							$desc = __('The path to the directory where the original photofiles will be saved.', 'wppa');
							$help = esc_js(__('You may change the directory path, but it can not be an url.', 'wppa'));
							$help .= '\n\n'.esc_js(__('The parent of the directory that you enter here must exist and be writable.', 'wppa'));
							$help .= '\n'.esc_js(__('The directory itsself will be created if it does not exist yet.', 'wppa'));
							$slug = 'wppa_source_dir';
							$html = wppa_input($slug, '300px');
							wppa_setting($slug, '3', $name, $desc, $html, $help);

							$name = __('Keep sync', 'wppa');
							$desc = __('Keep source synchronously with wppa system.', 'wppa');
							$help = esc_js(__('If checked, photos that are deleted from wppa, will also be removed from the sourcefiles.', 'wppa'));
							$help .= '\n'.esc_js(__('Also, copying or moving photos to different albums, will also copy/move the sourcefiles.', 'wppa'));
							$slug = 'wppa_keep_sync';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							
							$name = __('Remake', 'wppa');
							$desc = __('Remake the photofiles.', 'wppa');
							$help = esc_js(__('This action will remake the fullsize images, thumbnail images, and will refresh the iptc and exif data for all photos where the source is found in the corresponding album sub-directory of the source directory.', 'wppa'));
							$slug = 'wppa_remake';
							$html = wppa_doit_button('', $slug);
							wppa_setting(false, '5', $name, $desc, $html, $help);
							
							$name = __('Remake add', 'wppa');
							$desc = __('Photos will be added from the source pool', 'wppa');
							$help = esc_js(__('If checked: If photo files are found in the source directory that do not exist in the corresponding album, they will be added to the album.', 'wppa'));
							$slug = 'wppa_remake_add';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							
							$name = __('Save IPTC data', 'wppa');
							$desc = __('Store the iptc data from the photo into the iptc db table', 'wppa');
							$help = esc_js(__('You will need this if you enabled the display of iptc data in Table II-B17 or if you use it in the photo descriptions.', 'wppa'));
							$slug = 'wppa_save_iptc';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '7', $name, $desc, $html, $help);
							
							$name = __('Save EXIF data', 'wppa');
							$desc = __('Store the exif data from the photo into the exif db table', 'wppa');
							$help = esc_js(__('You will need this if you enabled the display of exif data in Table II-B18 or if you use it in the photo descriptions.', 'wppa'));
							$slug = 'wppa_save_exif';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '8', $name, $desc, $html, $help);
							
							$name = __('Max EXIF tag array size', 'wppa');
							$desc = __('Truncate array tags to ...', 'wppa');
							$help = esc_js(__('A value of 0 disables this feature', 'wppa'));
							$slug = 'wppa_exif_max_array_size';
							$html = wppa_input($slug, '40px', '', __('elements', 'wppa'));
							wppa_setting($slug, '9', $name, $desc, $html, $help);
							
							$name = __('Import Create page', 'wppa');
							$desc = __('Create wp page when a directory to album is imported.', 'wppa');
							$help = esc_js(__('As soon as an album is created when a directory is imported, a wp page is made that displays the album content.', 'wppa'));
							$slug = 'wppa_newpag_create';
							$onchange = 'wppaCheckNewpag()';
							$html = wppa_checkbox($slug, $onchange);
							wppa_setting($slug, '10', $name, $desc, $html, $help);
							
							$name = __('Page content', 'wppa');
							$desc = __('The content of the page. Must contain <b>w#album</b>', 'wppa');
							$help = esc_js(__('The content of the page. Note: it must contain w#album. This will be replaced by the album number in the generated shortcode.', 'wppa'));
							$slug = 'wppa_newpag_content';
							$class = 'wppa_newpag';
							$html = wppa_input($slug, '500px');
							wppa_setting($slug, '11', $name, $desc, $html, $help, $class);
							
							$name = __('Page type', 'wppa');
							$desc = __('Select the type of page to create.', 'wppa');
							$help = '';
							$slug = 'wppa_newpag_type';
							$class = 'wppa_newpag';
							$options = array(__('Page', 'wppa'), __('Post', 'wppa'));
							$values = array('page', 'post');
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '12', $name, $desc, $html, $help, $class);
							
							$name = __('Page status', 'wppa');
							$desc = __('Select the initial status of the page.', 'wppa');
							$help = '';
							$slug = 'wppa_newpag_status';
							$class = 'wppa_newpag';
							$options = array(__('Published', 'wppa'), __('Draft', 'wppa'));
							$values = array('publish', 'draft');	// 'draft' | 'publish' | 'pending'| 'future' | 'private'
							$html = wppa_select($slug, $options, $values);
							wppa_setting($slug, '13', $name, $desc, $html, $help, $class);
							}
							wppa_setting_subheader( 'J', '1', __( 'Other plugins related settings', 'wppa' ) );
							{
							$name = __('Foreign shortcodes fullsize', 'wppa');
							$desc = __('Enable the use of non-wppa+ shortcodes in fullsize photo descriptions.', 'wppa');
							$help = esc_js(__('When checked, you can use shortcodes from other plugins in the description of photos.', 'wppa'));
							$help .= '\n\n'.esc_js(__('The shortcodes will be expanded in the descriptions of fullsize images.', 'wppa'));
							$help .= '\n'.esc_js(__('You will most likely need also to check Table IX-A1 (Allow HTML).', 'wppa'));
							$slug = 'wppa_allow_foreign_shortcodes';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('Foreign shortcodes thumbnails', 'wppa');
							$desc = __('Enable the use of non-wppa+ shortcodes in thumbnail photo descriptions.', 'wppa');
							$help = esc_js(__('When checked, you can use shortcodes from other plugins in the description of photos.', 'wppa'));
							$help .= '\n\n'.esc_js(__('The shortcodes will be expanded in the descriptions of thumbnail images.', 'wppa'));
							$help .= '\n'.esc_js(__('You will most likely need also to check Table IX-A1 (Allow HTML).', 'wppa'));
							$slug = 'wppa_allow_foreign_shortcodes_thumbs';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('Lightbox keyname', 'wppa');
							$desc = __('The identifier of lightbox.', 'wppa');
							$help = esc_js(__('If you use a lightbox plugin that uses rel="lbox-id" you can enter the lbox-id here.', 'wppa'));
							$slug = 'wppa_lightbox_name';
							$class = 'wppa_alt_lightbox';
							$html = wppa_input($slug, '100px');
							wppa_setting($slug, '3', $name, $desc, $html, $help, $class);
							
							$name = __('Cube Points Comment', 'wppa');
							$desc = __('Number of points for a comment', 'wppa');
							$help = esc_js(__('This setting requires the plugin Cube Points', 'wppa'));
							$slug = 'wppa_cp_points_comment';
							$html = wppa_input($slug, '50px', '', __('points per comment', 'wppa'));
							wppa_setting($slug, '4', $name, $desc, $html, $help);

							$name = __('Cube Points Rating', 'wppa');
							$desc = __('Number of points for a rating vote', 'wppa');
							$help = esc_js(__('This setting requires the plugin Cube Points', 'wppa'));
							$slug = 'wppa_cp_points_rating';
							$html = wppa_input($slug, '50px', '', __('points per vote', 'wppa'));
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							
							$name = __('Cube Points Upload', 'wppa');
							$desc = __('Number of points for a successfull frontend upload', 'wppa');
							$help = esc_js(__('This setting requires the plugin Cube Points', 'wppa'));
							$slug = 'wppa_cp_points_upload';
							$html = wppa_input($slug, '50px', '', __('points per upload', 'wppa'));
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							
							$name = __('Use SCABN', 'wppa');
							$desc = __('Use the wppa interface to Simple Cart & Buy Now plugin.', 'wppa');
							$help = esc_js(__('If checked, the shortcode to use for the "add to cart" button in photo descriptions is [cart ...]', 'wppa'));
							$help .= '\n'.esc_js(__('as opposed to [scabn ...] for the original scabn "add to cart" button.', 'wppa'));
							$help .= '\n'.esc_js(__('The shortcode for the check-out page is still [scabn]', 'wppa'));
							$help .= '\n\n'.esc_js(__('The arguments are the same, the defaults are: name = photoname, price = 0.01.', 'wppa'));
							$help .= '\n'.esc_js(__('Supplying the price should be sufficient; supply a name only when it differs from the photo name.', 'wppa'));
							$help .= '\n\n'.esc_js(__('This shortcode handler will also work with Ajax enabled.', 'wppa'));
							$help .= '\n\n'.esc_js(__('Using this interface makes sure that the item urls and callback action urls are correct.', 'wppa'));
							$slug = 'wppa_use_scabn';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '7', $name, $desc, $html, $help);
							}
							wppa_setting_subheader( 'K', '1', __('External services related setting and actions.', 'wppa'));
							{
							$name = __('QR Code widget size', 'wppa');
							$desc = __('The size of the QR code display.', 'wppa');
							$help = '';
							$slug = 'wppa_qr_size';
							$html = wppa_input($slug, '50px', '', __('pixels', 'wppa'));
							wppa_setting($slug, '1', $name, $desc, $html, $help);
							
							$name = __('QR color', 'wppa');
							$desc = __('The display color of the qr code (dark)', 'wppa');
							$help = esc_js(__('This color MUST be given in hexadecimal format!', 'wppa'));
							$slug = 'wppa_qr_color';
							$html = wppa_input($slug, '100px', '', '', "checkColor('".$slug."')") . wppa_color_box($slug);
							wppa_setting($slug, '2', $name, $desc, $html, $help);
							
							$name = __('QR background color', 'wppa');
							$desc = __('The background color of the qr code (light)', 'wppa');
							$help = '';
							$slug = 'wppa_qr_bgcolor';
							$html = wppa_input($slug, '100px', '', '', "checkColor('".$slug."')") . wppa_color_box($slug);
							wppa_setting($slug, '3', $name, $desc, $html, $help);

							$name = __('CDN Service', 'wppa');
							$desc = __('Select a CDN Service you want to use.', 'wppa');
							$help = '';
							$slug = 'wppa_cdn_service';
							$opts = array(__('--- none ---'), 'Cloudinary');
							$vals = array('', 'cloudinary');
							$onch = 'wppaCheckCDN()';
							$html = wppa_select($slug, $opts, $vals, $onch);
							wppa_setting($slug, '4', $name, $desc, $html, $help);
							
							if ( PHP_VERSION_ID >= 50300 ) {
							
								$name = __('Cloud name', 'wppa');
								$desc = '';
								$help = '';
								$slug = 'wppa_cdn_cloud_name';
								$html = wppa_input($slug, '500px');
								$class = 'cloudinary';
								wppa_setting($slug, '4.1', $name, $desc, $html, $help, $class);
								
								$name = __('API key', 'wppa');
								$desc = '';
								$help = '';
								$slug = 'wppa_cdn_api_key';
								$html = wppa_input($slug, '500px');
								$class = 'cloudinary';
								wppa_setting($slug, '4.2', $name, $desc, $html, $help, $class);
								
								$name = __('API secret', 'wppa');
								$desc = '';
								$help = '';
								$slug = 'wppa_cdn_api_secret';
								$html = wppa_input($slug, '500px');
								$class = 'cloudinary';
								wppa_setting($slug, '4.3', $name, $desc, $html, $help, $class);
							
								$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_PHOTOS."` WHERE `id` > %s", get_option('wppa_last_cloud_upload', '0')));
								$name = __('Update uploads', 'wppa');
								$desc = sprintf(__('Verify and upload photos to the cloud.', 'wppa'), $count);
								$help = esc_js(__('This function will add the missing photos to Cloudinary.', 'wppa'));
								$help .= '\n\n'.esc_js(__('You need to run this only when there are images that are not displayed.', 'wppa'));
								$help .= '\n\n'.esc_js(__('This procedure may take much time!', 'wppa'));
								$slug = 'wppa_cdn_service_update';
								$html = wppa_doit_button('', $slug);
								$class = 'cloudinary';
								wppa_setting(false, '4.4', $name, $desc, $html, $help, $class);
								
								$name = __('Delete all', 'wppa');
								$desc = __('<span style="color"red" >Deletes them all !!!</span>', 'wppa');
								$help = '';
								$slug = 'wppa_delete_all_from_cloudinary';
								$html = wppa_doit_button('', $slug);
								$class = 'cloudinary';
								wppa_setting(false, '4.5', $name, $desc, $html, $help, $class);
								
							}
							else {
							
								$name = __('Cloudinary', 'wppa');
								$desc = __('<span style="color:red;">Requires at least PHP version 5.3</span>', 'wppa');
								$help = '';
								$html = '';
								$class = 'cloudinary';
								wppa_setting($slug, '4', $name, $desc, $html, $help, $class);
								
							}
						
							$name = __('GPX Implementation', 'wppa');
							$desc = __('The way the maps are produced.', 'wppa');
							$help = esc_js(__('Select the way the maps are produced.', 'wppa'));
							$help .= '\n\n'.esc_js(__('When using Google maps GPX viewer plugin, you can not use Ajax (Table IV-A1)', 'wppa'));
							$help .= '\n'.esc_js(__('When using WPPA+ Embedded code, you can use Ajax, but there are less display options.', 'wppa'));
							$slug = 'wppa_gpx_implementation';
							$opts = array( __('--- none ---', 'wppa'), __('WPPA+ Embedded code', 'wppa'), __('Google maps GPX viewer plugin', 'wppa') );
							$vals = array( 'none', 'wppa-plus-embedded', 'google-maps-gpx-viewer' );
							$onch = 'wppaCheckGps()';
							$html = wppa_select($slug, $opts, $vals, $onch);
							wppa_setting($slug, '5', $name, $desc, $html, $help);
							
							$name = __('Map height', 'wppa');
							$desc = __('The height of the map display.', 'wppa');
							$help = '';
							$slug = 'wppa_map_height';
							$html = wppa_input($slug, '40px', '', __('pixels', 'wppa'));
							$class = 'wppa_gpx_native';
							wppa_setting($slug, '5.1', $name, $desc, $html, $help, $class);
							
							$name = __('Google maps API key', 'wppa');
							$desc = __('Enter your Google maps api key here if you have one.', 'wppa');
							$help = '';
							$slug = 'wppa_map_apikey';
							$html = wppa_input($slug, '200px', '');
							$class = 'wppa_gpx_native';
							wppa_setting($slug, '5.2', $name, $desc, $html, $help, $class);
							
							$name = __('GPX Shortcode', 'wppa');
							$desc = __('The shortcode to be used for the gpx feature.', 'wppa');
							$help = esc_js(__('Enter / modify the shortcode to be generated for the gpx plugin. It must contain w#lat and w#lon as placeholders for the lattitude and longitude.', 'wppa'));
							$help .= '\n\n'.esc_js(__('This item is required for using Google maps GPX viewer plugin only', 'wppa'));
							$slug = 'wppa_gpx_shortcode';
							$html = wppa_input($slug, '500px');
							$class = 'wppa_gpx_plugin';
							wppa_setting($slug, '5.3', $name, $desc, $html, $help, $class);
							
							$name = __('Fotomoto', 'wppa');
							$desc = __('Yes, we use Fotomoto on this site. Read the help text!', 'wppa');
							$help = esc_js(__('In order to function properly:', 'wppa'));
							$help .= '\n\n'.esc_js(__('1. Get yourself a Fotomoto account.', 'wppa'));
							$help .= '\n'.esc_js(__('2. Install the Fotomoto plugin, enter the "Fotomoto Site Key:" and check the "Use API Mode:" checkbox.', 'wppa'));
							$help .= '\n'.esc_js(__('3. Enable the Custom box in Table II-B14.', 'wppa'));
							$help .= '\n'.esc_js(__('4. Put the text w#fotomoto anywhere in the Custombox ( Table II-B15 ).', 'wppa'));
							$slug = 'wppa_fotomoto_on';
							$html = wppa_checkbox($slug);
							wppa_setting($slug, '6', $name, $desc, $html, $help);
							
							}
							?>		
			
						</tbody>
						<tfoot style="font-weight: bold;" class="wppa_table_9">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Name', 'wppa') ?></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Setting', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			
			<?php // Table 10: IPTC Configuration ?>
			<?php wppa_settings_box_header(
				'10',
				__('Table X:', 'wppa').' '.__('IPTC Configuration:', 'wppa').' '.
				__('This table defines the IPTC configuration', 'wppa')
			); ?>
			
				<div id="wppa_table_10" style="display:none" >
					<table class="widefat wppa-table wppa-setting-table">
						<thead style="font-weight: bold; " class="wppa_table_10">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Tag', 'wppa') ?></td>
								<td></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Status', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</thead>
						<tbody class="wppa_table_10">
							<?php
							$wppa_table = 'X';
							
							$wppa_subtable = 'Z';

							$labels = $wpdb->get_results( "SELECT * FROM `".WPPA_IPTC."` WHERE `photo` = '0' ORDER BY `tag`", ARRAY_A );
							if ( is_array( $labels ) ) {
								$i = '1';
								foreach ( $labels as $label ) {
									$name = $label['tag'];
									$desc = '';
									$help = '';
									$slug1 = 'wppa_iptc_label_'.$name;
									$slug2 = 'wppa_iptc_status_'.$name;
									$html1 = wppa_edit($slug1, $label['description']);
									$options = array(__('Display', 'wppa'), __('Hide', 'wppa'), __('Optional', 'wppa'));
									$values = array('display', 'hide', 'option');
									$html2 = wppa_select_e($slug2, $label['status'], $options, $values);
									$html = array($html1, $html2);
									wppa_setting(false, $i, $name, $desc, $html, $help);
									$i++;

								}
							}
							
							?>
						</tbody>
						<tfoot style="font-weight: bold;" class="wppa_table_10">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Tag', 'wppa') ?></td>
								<td></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Status', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			
			<?php // Table 11: EXIF Configuration ?>
			<?php wppa_settings_box_header(
				'11',
				__('Table XI:', 'wppa').' '.__('EXIF Configuration:', 'wppa').' '.
				__('This table defines the EXIF configuration', 'wppa')
			); ?>
			
				<div id="wppa_table_11" style="display:none" >
					<table class="widefat wppa-table wppa-setting-table">
						<thead style="font-weight: bold; " class="wppa_table_11">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Tag', 'wppa') ?></td>
								<td></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Status', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</thead>
						<tbody class="wppa_table_11">
							<?php
							$wppa_table = 'XI';
							
							$wppa_subtable = 'Z';

							if ( ! function_exists('exif_read_data') ) {
								wppa_setting_subheader('', '1', '</b><span style="color:red;">'.
									__('Function exif_read_data() does not exist. This means that <b>EXIF</b> is not enabled. If you want to use <b>EXIF</b> data, ask your hosting provider to add <b>\'--enable-exif\'</b> to the php <b>Configure Command</b>.', 'wppa').
									'<b></span>');
							}
													
							$labels = $wpdb->get_results( "SELECT * FROM `".WPPA_EXIF."` WHERE `photo` = '0' ORDER BY `tag`", ARRAY_A);
							if ( is_array( $labels ) ) {
								$i = '1';
								foreach ( $labels as $label ) {
									$name = $label['tag'];
									$desc = '';
									$help = '';
									$slug1 = 'wppa_exif_label_'.$name;
									$slug2 = 'wppa_exif_status_'.$name;
									$html1 = wppa_edit($slug1, $label['description']);
									$options = array(__('Display', 'wppa'), __('Hide', 'wppa'), __('Optional', 'wppa'));
									$values = array('display', 'hide', 'option');
									$html2 = wppa_select_e($slug2, $label['status'], $options, $values);
									$html = array($html1, $html2);
									wppa_setting(false, $i, $name, $desc, $html, $help);
									$i++;

								}
							}
							
							?>
						</tbody>
						<tfoot style="font-weight: bold;" class="wppa_table_11">
							<tr>
								<td><?php _e('#', 'wppa') ?></td>
								<td><?php _e('Tag', 'wppa') ?></td>
								<td></td>
								<td><?php _e('Description', 'wppa') ?></td>
								<td><?php _e('Status', 'wppa') ?></td>
								<td><?php _e('Help', 'wppa') ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			
			<?php // Table 12: Php configuration ?>
			<?php wppa_settings_box_header(
				'12',
				__('Table XII:', 'wppa').' '.__('WPPA+ and PHP Configuration:', 'wppa').' '.
				__('This table lists all WPPA+ constants and PHP server configuration parameters and is read only', 'wppa')
			); ?>
			
			<?php
			$wppa_table = 'XII';
			$wppa_subtable = 'Z';
			?>

				<div id="wppa_table_12" style="display:none" >
		<!--		<div class="wppa_table_12" style="margin-top:20px; text-align:left; ">	-->
						<table class="widefat wppa-table wppa-setting-table">
							<thead style="font-weight: bold; " class="wppa_table_12">
								<tr>
									<td><?php _e('Name', 'wppa') ?></td>
									<td><?php _e('Description', 'wppa') ?></td>
									<td><?php _e('Value', 'wppa') ?></td>
								</tr>
							<tbody class="wppa_table_12">
								<tr style="color:#333;">
									<td>WPPA_ALBUMS</td>
									<td><small><?php _e('Albums db table name.', 'wppa') ?></small></td>
									<td><?php echo($wpdb->prefix . 'wppa_albums') ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_PHOTOS</td>
									<td><small><?php _e('Photos db table name.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_PHOTOS) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_RATING</td>
									<td><small><?php _e('Rating db table name.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_RATING) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_COMMENTS</td>
									<td><small><?php _e('Comments db table name.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_COMMENTS) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_IPTC</td>
									<td><small><?php _e('IPTC db table name.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_IPTC) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_EXIF</td>
									<td><small><?php _e('EXIF db table name.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_EXIF) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_INDEX</td>
									<td><small><?php _e('Index db table name.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_INDEX) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_FILE</td>
									<td><small><?php _e('Plugins main file name.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_FILE) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_PATH</td>
									<td><small><?php _e('Path to plugins directory.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_PATH) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_NAME</td>
									<td><small><?php _e('Plugins directory name.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_NAME) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_URL</td>
									<td><small><?php _e('Plugins directory url.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_URL) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_UPLOAD</td>
									<td><small><?php _e('The relative upload directory.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_UPLOAD) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_UPLOAD_PATH</td>
									<td><small><?php _e('The upload directory path.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_UPLOAD_PATH) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_UPLOAD_URL</td>
									<td><small><?php _e('The upload directory url.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_UPLOAD_URL) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_DEPOT</td>
									<td><small><?php _e('The relative depot directory.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_DEPOT) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_DEPOT_PATH</td>
									<td><small><?php _e('The depot directory path.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_DEPOT_PATH) ?></td>
								</tr>
								<tr style="color:#333;">
									<td>WPPA_DEPOT_URL</td>
									<td><small><?php _e('The depot directory url.', 'wppa') ?></small></td>
									<td><?php echo(WPPA_DEPOT_URL) ?></td>
								</tr>
							</tbody>
						</table>
						<p>&nbsp;</p>
						<?php wppa_phpinfo() ?>
		<!--			</div>-->
				</div>

		</form>
		<script type="text/javascript">wppaInitSettings();wppaCheckInconsistencies();</script>
		<?php echo sprintf(__('<br />Memory used on this page: %6.2f Mb.', 'wppa'), memory_get_peak_usage(true)/(1024*1024)); ?>
		<?php echo sprintf(__('<br />There are %d settings and %d runtime parameters.', 'wppa'), count($wppa_opt), count($wppa)); ?>
	</div>
	
<?php
}

function wppa_settings_box_header($id, $title) {
	echo '
		<div id="wppa_settingbox_'.$id.'" class="postbox metabox-holder" style="padding-top:0; margin-bottom:-1px; margin-top:20px; " >
			<div class="handlediv" title="Click to toggle table" onclick="wppaToggleTable('.$id.');" >
				<br>
			</div>
			<h3 class="hndle" style="cursor:pointer;" title="Click to toggle table" onclick="wppaToggleTable('.$id.');" >
				<span>'.$title.'</span>
				<br>
			</h3>
		</div>
		';
}

function wppa_setting_subheader($lbl, $col, $txt, $cls = '') {
global $wppa_subtable;
global $wppa_table;

	$wppa_subtable = $lbl;
	$colspan = $col + 3;
	echo 	'<tr class="'.$cls.'" style="background-color:#f0f0f0;" >'.
				'<td style="color:#333;"><b>'.$lbl.'</b></td>'.
				'<td title="Click to toggle subtable" onclick="wppaToggleSubTable(\''.$wppa_table.'\',\''.$wppa_subtable.'\');" colspan="'.$colspan.'" style="color:#333; cursor:pointer;" ><em><b>'.$txt.'</b></em></td>'.
			'</tr>';
}


function wppa_setting($slug, $num, $name, $desc, $html, $help, $cls = '') {
global $wppa_status;
global $wppa_defaults;
global $wppa_table;
global $wppa_subtable;
global $no_default;

	if ( is_array($slug) ) $slugs = $slug;
	else {
		$slugs = false;
		if ( $slug ) $slugs[] = $slug;
	}
	if ( is_array($html) ) $htmls = $html;
	else {
		$htmls = false;
		if ( $html ) $htmls[] = $html;
	}
	if ( strpos($num, ',') !== false ) {
		$nums = explode(',', $num);
		$nums[0] = substr($nums[0], 1);
	}
	else {
		$nums = false;
		if ( $num ) $nums[] = $num;
	}

	$result = "\n";
	$result .= '<tr id="'.$wppa_table.$wppa_subtable.$num.'" class="wppa-'.$wppa_table.'-'.$wppa_subtable.' '.$cls.' wppa-none" style="color:#333;">';
	$result .= '<td>'.$num.'</td>';
	$result .= '<td>'.$name.'</td>';
	$result .= '<td><small>'.$desc.'</small></td>';
	if ( $htmls ) foreach ( $htmls as $html ) {
		$result .= '<td>'.$html.'</td>';
	}
	
	if ( $help ) {
		$hlp = $name.':\n\n'.$help;
		if ( ! $no_default ) {
			if ( $slugs ) {
				$hlp .= '\n\n'.__('The default for this setting is:', 'wppa');
				if ( count($slugs) == 1) {
					if ( $slugs[0] != '' ) $hlp .= ' '.esc_js(wppa_dflt($slugs[0]));
				}
				else foreach ( array_keys($slugs) as $slugidx ) {
					if ( $slugs[$slugidx] != '' && isset($nums[$slugidx]) ) $hlp .= ' '.$nums[$slugidx].'. '.esc_js(wppa_dflt($slugs[$slugidx]));
				}
			}
		}
		$result .= '<td><input type="button" style="font-size: 11px; height:20px; padding:0; cursor: pointer;" title="'.__('Click for help', 'wppa').'" onclick="alert('."'".$hlp."'".')" value="&nbsp;?&nbsp;"></td>';
	}
	else {
		$result .= '<td></td>';//$hlp = __('No help available', 'wppa');
	}
	
	$result .= '</tr>';
	
	echo $result;	

}

function wppa_input($slug, $width, $minwidth = '', $text = '', $onchange = '') {

	$html = '<input style="float:left; width: '.$width.'; height:20px;';
	if ($minwidth != '') $html .= ' min-width:'.$minwidth.';';
	$html .= ' font-size: 11px; margin: 0px; padding: 0px;" type="text" id="'.$slug.'"';
	if ($onchange != '') $html .= ' onchange="'.$onchange.';wppaAjaxUpdateOptionValue(\''.$slug.'\', this)"';
	else $html .= ' onchange="wppaAjaxUpdateOptionValue(\''.$slug.'\', this)"';
	$html .= ' value="'.esc_attr(get_option($slug)).'" />';	// changed stripslashes into esc_attr
	$html .= '<img id="img_'.$slug.'" src="'.wppa_get_imgdir().'star.png" title="'.__('Setting unmodified', 'wppa').'" style="padding:0 4px; float:left; height:16px; width:16px;" />';
	$html .= '<span style="float:left">'.$text.'</span>';
	
	return $html;
}

function wppa_edit($slug, $value, $width = '90%', $minwidth = '', $text = '', $onchange = '') {

	$html = '<input style="float:left; width: '.$width.'; height:20px;';
	if ($minwidth != '') $html .= ' min-width:'.$minwidth.';';
	$html .= ' font-size: 11px; margin: 0px; padding: 0px;" type="text" id="'.$slug.'"';
	if ($onchange != '') $html .= ' onchange="'.$onchange.';wppaAjaxUpdateOptionValue(\''.$slug.'\', this)"';
	else $html .= ' onchange="wppaAjaxUpdateOptionValue(\''.$slug.'\', this)"';
	$html .= ' value="'.esc_attr($value).'" />';
	$html .= '<img id="img_'.$slug.'" src="'.wppa_get_imgdir().'star.png" title="'.__('Setting unmodified', 'wppa').'" style="padding:0 4px; float:left; height:16px; width:16px;" />';
	$html .= $text;
	
	return $html;

}

function wppa_textarea($slug, $buttonlabel = '') {

	if ( get_option('wppa_use_wp_editor') == 'yes' ) {	// New style textarea, use wp_editor
		$editor_id = str_replace( '_', '', $slug);
		ob_start();
			$quicktags_settings = array( 'buttons' => 'strong,em,link,block,ins,ul,ol,li,code,close' );
			wp_editor( get_option($slug), $editor_id, $settings = array('wpautop' => false, 'media_buttons' => false, 'textarea_rows' => '6', 'textarea_name' => $slug, 'tinymce' => false, 'quicktags' => $quicktags_settings ) );
		$html = ob_get_clean();
		$blbl = __('Update', 'wppa');
		if ( $buttonlabel ) $blbl .= ' '.$buttonlabel;
		
		$html .= wppa_ajax_button($blbl, $slug, $editor_id, 'no_confirm');
	}
	else {
		$html = '<textarea id="'.$slug.'" style="float:left; width:300px;" onchange="wppaAjaxUpdateOptionValue(\''.$slug.'\', this)" >';
		$html .= esc_textarea(stripslashes(get_option($slug))); //htmlspecialchars(stripslashes(get_option($slug)));
		$html .= '</textarea>';
	
		$html .= '<img id="img_'.$slug.'" src="'.wppa_get_imgdir().'star.png" title="'.__('Setting unmodified', 'wppa').'" style="padding:0 4px; float:left; height:16px; width:16px;" />';
	}
	return $html;
}

function wppa_checkbox($slug, $onchange = '', $class = '') {

	$html = '<input style="float:left; height: 15px; margin: 0px; padding: 0px;" type="checkbox" id="'.$slug.'"'; 
	if (get_option($slug) == 'yes') $html .= ' checked="checked"';
	if ($onchange != '') $html .= ' onchange="'.$onchange.';wppaAjaxUpdateOptionCheckBox(\''.$slug.'\', this)"';
	else $html .= ' onchange="wppaAjaxUpdateOptionCheckBox(\''.$slug.'\', this)"';

	if ($class != '') $html .= ' class="'.$class.'"';
	$html .= ' /><img id="img_'.$slug.'" src="'.wppa_get_imgdir().'star.png" title="'.__('Setting unmodified', 'wppa').'" style="padding-left:4px; float:left; height:16px; width:16px;"';
	if ($class != '') $html .= ' class="'.$class.'"';
	$html .= ' />';
	
	return $html;
}

function wppa_checkbox_warn($slug, $onchange = '', $class = '', $warning) {

	$warning = esc_js(__('Warning!', 'wppa')).'\n\n'.$warning;
	$html = '<input style="float:left; height: 15px; margin: 0px; padding: 0px;" type="checkbox" id="'.$slug.'"'; 
	if (get_option($slug) == 'yes') $html .= ' checked="checked"';
	if ($onchange != '') $html .= ' onchange="alert(\''.$warning.'\'); '.$onchange.';wppaAjaxUpdateOptionCheckBox(\''.$slug.'\', this)"';
	else $html .= ' onchange="alert(\''.$warning.'\'); wppaAjaxUpdateOptionCheckBox(\''.$slug.'\', this)"';

	if ($class != '') $html .= ' class="'.$class.'"';
	$html .= ' /><img id="img_'.$slug.'" src="'.wppa_get_imgdir().'star.png" title="'.__('Setting unmodified', 'wppa').'" style="padding-left:4px; float:left; height:16px; width:16px;"';
	if ($class != '') $html .= ' class="'.$class.'"';
	$html .= ' />';
	
	return $html;
}

function wppa_checkbox_warn_off($slug, $onchange = '', $class = '', $warning, $is_help = true) {

	$warning = esc_js(__('Warning!', 'wppa')).'\n\n'.$warning;
	if ( $is_help) $warning .= '\n\n'.esc_js(__('Please read the help', 'wppa'));
	$html = '<input style="float:left; height: 15px; margin: 0px; padding: 0px;" type="checkbox" id="'.$slug.'"'; 
	if (get_option($slug) == 'yes') $html .= ' checked="checked"';
	if ($onchange != '') $html .= ' onchange="if (!this.checked) alert(\''.$warning.'\'); '.$onchange.';wppaAjaxUpdateOptionCheckBox(\''.$slug.'\', this)"';
	else $html .= ' onchange="if (!this.checked) alert(\''.$warning.'\'); wppaAjaxUpdateOptionCheckBox(\''.$slug.'\', this)"';

	if ($class != '') $html .= ' class="'.$class.'"';
	$html .= ' /><img id="img_'.$slug.'" src="'.wppa_get_imgdir().'star.png" title="'.__('Setting unmodified', 'wppa').'" style="padding-left:4px; float:left; height:16px; width:16px;"';
	if ($class != '') $html .= ' class="'.$class.'"';
	$html .= ' />';
	
	return $html;
}

function wppa_checkbox_warn_on($slug, $onchange = '', $class = '', $warning) {

	$warning = esc_js(__('Warning!', 'wppa')).'\n\n'.$warning.'\n\n'.esc_js(__('Please read the help', 'wppa'));
	$html = '<input style="float:left; height: 15px; margin: 0px; padding: 0px;" type="checkbox" id="'.$slug.'"'; 
	if (get_option($slug) == 'yes') $html .= ' checked="checked"';
	if ($onchange != '') $html .= ' onchange="if (this.checked) alert(\''.$warning.'\'); '.$onchange.';wppaAjaxUpdateOptionCheckBox(\''.$slug.'\', this)"';
	else $html .= ' onchange="if (this.checked) alert(\''.$warning.'\'); wppaAjaxUpdateOptionCheckBox(\''.$slug.'\', this)"';

	if ($class != '') $html .= ' class="'.$class.'"';
	$html .= ' /><img id="img_'.$slug.'" src="'.wppa_get_imgdir().'star.png" title="'.__('Setting unmodified', 'wppa').'" style="padding-left:4px; float:left; height:16px; width:16px;"';
	if ($class != '') $html .= ' class="'.$class.'"';
	$html .= ' />';
	
	return $html;
}

function wppa_checkbox_e($slug, $curval, $onchange = '', $class = '', $enabled = true) {

	$html = '<input style="float:left; height: 15px; margin: 0px; padding: 0px;" type="checkbox" id="'.$slug.'"'; 
	if ($curval) $html .= ' checked="checked"';
	if ( ! $enabled ) $html .= ' disabled="disabled"';
	if ($onchange != '') $html .= ' onchange="'.$onchange.';wppaAjaxUpdateOptionCheckBox(\''.$slug.'\', this)"';
	else $html .= ' onchange="wppaAjaxUpdateOptionCheckBox(\''.$slug.'\', this)"';

	if ($class != '') $html .= ' class="'.$class.'"';
	$html .= ' /><img id="img_'.$slug.'" src="'.wppa_get_imgdir().'star.png" title="'.__('Setting unmodified', 'wppa').'" style="padding-left:4px; float:left; height:16px; width:16px;"';
	if ($class != '') $html .= ' class="'.$class.'"';
	$html .= ' />';
	
	return $html;
}

function wppa_select($slug, $options, $values, $onchange = '', $class = '', $first_disable = false, $postaction = '') {

	if (!is_array($options)) {
		$html = __('There are no pages (yet) to link to.', 'wppa');
		return $html;
	}
	
	$html = '<select style="float:left; font-size: 11px; height: 20px; margin: 0px; padding: 0px; max-width:220px;" id="'.$slug.'"';
//	if ($onchange != '') 
	$html .= ' onchange="'.$onchange.';wppaAjaxUpdateOptionValue(\''.$slug.'\', this);'.$postaction.'"';
//	else $html .= ' onchange="wppaAjaxUpdateOptionValue(\''.$slug.'\', this)"';

	if ($class != '') $html .= ' class="'.$class.'"';
	$html .= '>';
	
	$val = get_option($slug);
	$idx = 0;
	$cnt = count($options);
	while ($idx < $cnt) {
		$html .= "\n";
		$html .= '<option value="'.$values[$idx].'" '; 
		$dis = false;
		if ($idx == 0 && $first_disable) $dis = true;
		$opt = trim($options[$idx], '|');
		if ($opt != $options[$idx]) $dis = true;
		if ($val == $values[$idx]) $html .= ' selected="selected"'; 
		if ($dis) $html .= ' disabled="disabled"';
		$html .= '>'.$opt.'</option>';
		$idx++;
	}
	$html .= '</select>';
	$html .= '<img id="img_'.$slug.'" class="'.$class.'" src="'.wppa_get_imgdir().'star.png" title="'.__('Setting unmodified', 'wppa').'" style="padding:0 4px; float:left; height:16px; width:16px;" />';

	return $html;
}

function wppa_select_e($slug, $curval, $options, $values, $onchange = '', $class = '') {

	if (!is_array($options)) {
		$html = __('There are no pages (yet) to link to.', 'wppa');
		return $html;
	}
	
	$html = '<select style="float:left; font-size: 11px; height: 20px; margin: 0px; padding: 0px;" id="'.$slug.'"';
	if ($onchange != '') $html .= ' onchange="'.$onchange.';wppaAjaxUpdateOptionValue(\''.$slug.'\', this)"';
	else $html .= ' onchange="wppaAjaxUpdateOptionValue(\''.$slug.'\', this)"';

	if ($class != '') $html .= ' class="'.$class.'"';
	$html .= '>';
	
	$val = $curval; // get_option($slug);
	$idx = 0;
	$cnt = count($options);
	while ($idx < $cnt) {
		$html .= "\n";
		$html .= '<option value="'.$values[$idx].'" '; 
		if ($val == $values[$idx]) $html .= ' selected="selected"'; 
		$html .= '>'.$options[$idx].'</option>';
		$idx++;
	}
	$html .= '</select>';
	$html .= '<img id="img_'.$slug.'" class="'.$class.'" src="'.wppa_get_imgdir().'star.png" title="'.__('Setting unmodified', 'wppa').'" style="padding-left:4px; float:left; height:16px; width:16px;" />';

	return $html;
}

function wppa_dflt($slug) {
global $wppa_defaults;
global $wppa;
global $no_default;

	if ( $slug == '' ) return '';
	if ( $no_default ) return '';
	
	$dflt = $wppa_defaults[$slug];

	$dft = $dflt;
	switch ($dflt) {
		case 'yes': 	$dft .= ': '.__('Checked', 'wppa'); break;
		case 'no': 		$dft .= ': '.__('Unchecked', 'wppa'); break;
		case 'none': 	$dft .= ': '.__('no link at all.', 'wppa'); break;
		case 'file': 	$dft .= ': '.__('the plain photo (file).', 'wppa'); break;
		case 'photo': 	$dft .= ': '.__('the full size photo in a slideshow.', 'wppa'); break;
		case 'single': 	$dft .= ': '.__('the fullsize photo on its own.', 'wppa'); break;
		case 'indiv': 	$dft .= ': '.__('the photo specific link.', 'wppa'); break;
		case 'album': 	$dft .= ': '.__('the content of the album.', 'wppa'); break;
		case 'widget': 	$dft .= ': '.__('defined at widget activation.', 'wppa'); break;
		case 'custom': 	$dft .= ': '.__('defined on widget admin page.', 'wppa'); break;
		case 'same': 	$dft .= ': '.__('same as title.', 'wppa'); break;
		default:
	}

	return $dft;
}

function wppa_color_box($slug) {
global $wppa_opt;

	return '<div id="colorbox-' . $slug . '" style="width:100px; height:16px; float:left; background-color:' . $wppa_opt[$slug] . '; border:1px solid #dfdfdf;" ></div>';

}

function wppa_doit_button( $label = '', $key = '', $sub = '' ) {
	if ( $label == '' ) $label = __('Do it!', 'wppa');

	$result = '<input type="submit" class="button-primary" style="float:left; font-size: 11px; height: 16px; margin: 0 4px; padding: 0px;"';
	$result .= ' name="wppa_settings_submit" value="'.$label.'"';
	$result .= ' onclick="';
	if ( $key ) $result .= 'document.getElementById(\'wppa-key\').value=\''.$key.'\';';
	if ( $sub ) $result .= 'document.getElementById(\'wppa-sub\').value=\''.$sub.'\';';
	$result .= 'if ( confirm(\''.__('Are you sure?', 'wppa').'\')) return true; else return false;" />';
	
	return $result;
}

function wppa_ajax_button( $label = '', $slug, $elmid = '0', $no_confirm = false ) {
	if ( $label == '' ) $label = __('Do it!', 'wppa');

	$result = '<input type="button" class="button-secundary" style="float:left; border-radius:3px; font-size: 11px; height: 18px; margin: 0 4px; padding: 0px;" value="'.$label.'"';
	$result .= ' onclick="';
	if ( ! $no_confirm ) $result .= 'if (confirm(\''.__('Are you sure?', 'wppa').'\')) ';
	if ( $elmid ) { 
		$result .= 'wppaAjaxUpdateOptionValue(\''.$slug.'\', document.getElementById(\''.$elmid.'\'))" />';
	}
	else {
		$result .= 'wppaAjaxUpdateOptionValue(\''.$slug.'\', 0)" />';
	}
	
	$result .= '<img id="img_'.$slug.'" src="'.wppa_get_imgdir().'star.png" title="'.__('Not done yet', 'wppa').'" style="padding:0 4px; float:left; height:16px; width:16px;" />';
	
	return $result;
}

function wppa_htmlerr($slug) {
	
	switch ($slug) {
		case 'popup-lightbox':
			$title = __('You can not have popup and lightbox on thumbnails at the same time. Uncheck either Table IV-C8 or choose a different linktype in Table VI-2.', 'wppa');
			break;
		default:
			$title = __('It is important that you select a page that contains at least %%wppa%% or [wppa][/wppa].', 'wppa');
			$title .= " ".__('If you ommit this, the link will not work at all or simply refresh the (home)page.', 'wppa');
			break;
	}
	$result = '<img  id="'.$slug.'-err" '.
					'src="'.wppa_get_imgdir().'error.png" '.
					'class="'.$slug.'-err" '.
					'style="height:16px; width:16px; float:left; display:none;" '.
					'title="'.$title.'" '.
					'onmouseover="jQuery(this).animate({width: 32, height:32}, 100)" '.
					'onmouseout="jQuery(this).animate({width: 16, height:16}, 200)" />';
	
	return $result;
}

function wppa_verify_page($slug) {
global $wpdb;
global $wppa_opt;

	if ( ! isset($wppa_opt[$slug]) ) {
		wppa_error_message('Unexpected error in wppa_verify_page()', 'red', 'force');
		return;
	}
	$iret = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `" . $wpdb->posts . "` WHERE `post_type` = 'page' AND `post_status` = 'publish' AND `ID` = %s", $wppa_opt[$slug]));
	if ( ! $iret ) {
		$wppa_opt[$slug] = '0';
		wppa_update_option($slug, '0');
	}
}