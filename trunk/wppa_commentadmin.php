<?php
/* wppa_commentadmin.php
* Package: wp-photo-album-plus
*
* manage all comments
* Version 3.1.0
*
*/

function _wppa_comments() {
global $wpdb;
global $wppa;

	$continue = true;
	
	if (isset($_GET['tab'])) {
		if ($_GET['tab'] == 'edit') {
			$id = $_GET['edit_id'];
			$comment = $wpdb->get_row("SELECT * FROM ".WPPA_COMMENTS." WHERE id=".$id." LIMIT 1", "ARRAY_A");
			if ($comment) {
			?>
			<div class="wrap">
				<?php $iconurl = WPPA_URL.'/images/comment.png'; ?>
				<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
					<br />
				</div>
				<h2><?php _e('WP Photo Album Plus Edit Comment', 'wppa'); ?></h2>
				
				<form action="<?php echo(wppa_dbg_url(get_admin_url().'/admin.php?page=manage_comments')); if (isset($_GET['compage'])) echo('&compage='.$_GET['compage']) ?>" method="post">
		
					<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE); ?>
					<input type="hidden" name="edit_comment" value="<?php echo($comment['id']) ?>" />
					<table class="form-table albumtable">
						<tbody>
							<tr style="vertical-align:top" >
								<th>
									<?php $photo = $wpdb->get_row("SELECT * FROM ".WPPA_PHOTOS." WHERE id=".$comment['photo'], "ARRAY_A") ?>
									<?php $url = get_bloginfo('wpurl').'/wp-content/uploads/wppa/thumbs/'.$comment['photo'].'.'.$photo['ext'] ?>
									<img src="<?php echo($url) ?>" />
								</th>
								<td>
									<?php echo(wppa_qtrans($photo['name']).'<br/><br/>'.wppa_qtrans(stripslashes($photo['description']))) ?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label ><?php _e('Photo:', 'wppa'); ?></label></th>
								<td><?php echo($comment['photo']) ?></td>								
							</tr>
							<tr>
								<th scope="row"><label ><?php _e('User:', 'wppa') ?></label></th>
								<td><input style="width:300px;" type="text" name="user" value="<?php echo($comment['user']) ?>" /></td>
							</tr>
							<tr>
								<th scope="row"><label ><?php _e('Email:', 'wppa') ?></label></th>
								<td><input style="width:300px;" type="text" name="email" value="<?php echo($comment['email']) ?>" /></td>
							</tr>
							<tr>
								<th scope="row"><label><?php _e('Comment:', 'wppa') ?></label></th>
								<td><textarea style="width:300px; height:150px;" name="comment"><?php echo(stripslashes($comment['comment'])) ?></textarea></td>
							</tr>
						</tbody>
					</table>
					<p>
						<input type="submit" class="button-primary" name="wppa_submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
					</p>
				</form>			
			<?php
			}
			$continue = false;
		}
		if ($_GET['tab'] == 'delete') {
			$id = $_GET['delete_id'];
			$iret = $wpdb->query("DELETE FROM ".WPPA_COMMENTS." WHERE id=".$id." LIMIT 1");
			if ($iret !== false) wppa_update_message('Comment deleted', 'wppa');
			else wppa_error_message('Error deleting comment', 'wppa');
			$continue = true;
		}
	}
	
	if ($continue) {

		// Update anything
		if (isset($_POST['wppa_submit'])) {
			wppa_check_admin_referer( '$wppa_nonce', WPPA_NONCE );

			$iret = true;
			
			if (isset($_POST['wppa_comadmin_show'])) update_option('wppa_comadmin_show', $_POST['wppa_comadmin_show']);
			if (isset($_POST['wppa_comadmin_order'])) update_option('wppa_comadmin_order', $_POST['wppa_comadmin_order']);
			
			if (isset($_POST['status'])) if (is_array($_POST['status'])) {
				array_walk($_POST['status'], 'wppa_edit_commentstatus');
			}

			if (isset($_POST['edit_comment'])) {
				$iret = wppa_edit_comment($_POST['edit_comment']);
			}
			
			if ($iret) wppa_update_message(__('Changes Saved', 'wppa'));
		}
?>
		<div class="wrap">
			<?php $iconurl = WPPA_URL.'/images/comment.png'; ?>
			<div id="icon-album" class="icon32" style="background: transparent url(<?php echo($iconurl); ?>) no-repeat">
				<br />
			</div>
			<h2><?php _e('WP Photo Album Plus Comment admin', 'wppa'); ?></h2>
		
			<table>
				<tbody>
					<tr>
						<td><h3 style="margin:0; color:#777777;"><?php _e('Total:', 'wppa') ?></h3></td>
						<td><h3 style="margin:0;"><?php $comments = $wpdb->get_results("SELECT id FROM ".WPPA_COMMENTS." ", "ARRAY_A"); echo(count($comments)) ?></h3></td>
					</tr>
					<tr>
						<td><h3 style="margin:0; color:green;"><?php _e('Approved:', 'wppa') ?></h3></td>
						<td><h3 style="margin:0;"><?php $comments = $wpdb->get_results("SELECT id FROM ".WPPA_COMMENTS." WHERE status='approved'", "ARRAY_A"); echo(count($comments)) ?></h3></td>
					</tr>
					<tr>
						<td><h3 style="margin:0; color:#e66f00;"><?php _e('Pending:', 'wppa') ?></h3></td>
						<td><h3 style="margin:0;"><?php $comments = $wpdb->get_results("SELECT id FROM ".WPPA_COMMENTS." WHERE status='pending'", "ARRAY_A"); echo(count($comments)) ?></h3></td>
					</tr>
					<tr>
						<td><h3 style="margin:0; color:red;"><?php _e('Spam:', 'wppa') ?></h3></td>
						<td><h3 style="margin:0;"><?php $comments = $wpdb->get_results("SELECT id FROM ".WPPA_COMMENTS." WHERE status='spam'", "ARRAY_A"); echo(count($comments)) ?></h3></td>
					</tr>
				</tbody>
			</table>
			<form action="<?php echo(wppa_dbg_url(get_admin_url().'/admin.php?page=manage_comments')) ?>" method="post">
		
				<?php wppa_nonce_field('$wppa_nonce', WPPA_NONCE); ?>
				
				<?php $comment_show = get_option('wppa_comadmin_show') ?>
				<p>
				<?php _e('Display status:', 'wppa') ?>
				<select name="wppa_comadmin_show">
					<option value="all" <?php if ($comment_show == 'all') echo('selected="selected"') ?>><?php _e('all', 'wppa') ?></option>
					<option value="pending" <?php if ($comment_show == 'pending') echo('selected="selected"') ?>><?php _e('pending', 'wppa') ?></option>
					<option value="approved" <?php if ($comment_show == 'approved') echo('selected="selected"') ?>><?php _e('approved', 'wppa') ?></option>
					<option value="spam" <?php if ($comment_show == 'spam') echo('selected="selected"') ?>><?php _e('spam', 'wppa') ?></option>
				</select>
				<?php $comment_order = get_option('wppa_comadmin_order', 'wppa') ?>
				<?php _e('Display order:', 'wppa') ?>
				<select name="wppa_comadmin_order">
					<option value="timestamp" <?php if ($comment_order == 'timestamp') echo('selected="selected"') ?>><?php _e('timestamp', 'wppa') ?></option>
					<option value="photo" <?php if ($comment_order == 'photo') echo('selected="selected"') ?>><?php _e('photo', 'wppa') ?></option>
				</select>
				</p>
				<?php 
				$pagsize = '8'; 
				if ($comment_show != 'all') {
					$where = " WHERE status='".$comment_show."'";
				}
				else $where = '';
				if ($comment_order == 'timestamp') {
					$order = " ORDER BY ".$comment_order." DESC";
				}
				else $order = " ORDER BY ".$comment_order;
				if (isset($_GET['compage'])) {
					$offset = ($_GET['compage'] - 1) * $pagsize;
					$limit = " LIMIT ".$offset.",".$pagsize;
				}
				else $limit = ' LIMIT 0,'.$pagsize;

				$c = $wpdb->get_results("SELECT id FROM ".WPPA_COMMENTS.$where, "ARRAY_A");
				$nitems = count($c);
				$npages = $nitems ? floor(($nitems - '1') / $pagsize) + '1' : '1';
				wppa_comment_submit_pages($nitems, $npages);
				?>
				<table class="widefat">
					<thead style="font-weight: bold" class="">
						<tr>
							<th scope="col"><?php _e('Photo', 'wppa') ?></th>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('User', 'wppa') ?></th>
							<th scope="col"><?php _e('Email', 'wppa') ?></th>
							<th scope="col"><?php _e('Time since', 'wppa') ?></th>
							<th scope="col"><?php _e('Comment', 'wppa') ?></th>
							<th scope="col"><?php _e('Status', 'wppa') ?></th>
							<th scope="col"><?php _e('Edit', 'wppa') ?></th>
							<th scope="col"><?php _e('Delete', 'wppa') ?></th>
						</tr>
					</thead>
					<tbody class="wppa_table_1">
						<?php 
						
						$comments = $wpdb->get_results('SELECT * FROM '.WPPA_COMMENTS.$where.$order.$limit, 'ARRAY_A');
						if ($comments) {
							foreach ($comments as $com) { ?>
								<tr>
									<td style="text-align:center"><img src="<?php echo(get_bloginfo('wpurl').'/wp-content/uploads/wppa/thumbs/'.$com['photo'].'.'.$wpdb->get_var("SELECT ext FROM ".WPPA_PHOTOS." WHERE id = ".$com['photo'])) ?>" style="max-height:64px;max-width:64px;" ></td>							
									<td><?php echo $com['photo'] ?></td>
									<td><?php echo $com['user'] ?></td>
									<td><?php echo $com['email'] ?></td>
									<td><?php echo wppa_get_time_since($com['timestamp']) ?></td>
									<td><?php echo $com['comment'] ?></td>
									<td>
										<select name="status['<?php echo $com['id'] ?>']"><?php echo $com['status'] ?>
											<option value="pending" 	<?php if($com['status'] == 'pending') 	echo 'selected="selected"' ?>><?php _e('Pending', 'wppa') ?></option>
											<option value="approved" 	<?php if($com['status'] == 'approved') 		echo 'selected="selected"' ?>><?php _e('Approved', 'wppa') ?></option>
											<option value="spam" 		<?php if($com['status'] == 'spam') 		echo 'selected="selected"' ?>><?php _e('Spam', 'wppa') ?></option>
										</select>
									</td>
									<?php $url = wppa_dbg_url(get_admin_url().'/admin.php?page=manage_comments&tab=edit&edit_id='.$com['id']);
										if (isset($_GET['compage'])) $url .= '&compage='.$_GET['compage']; ?>
									<?php $delurl = wppa_dbg_url(get_admin_url().'/admin.php?page=manage_comments&tab=delete&delete_id='.$com['id']) ?>
									<td style="color:green; cursor:pointer;" onclick="document.location='<?php echo($url) ?>'"><b><?php _e('Edit', 'wppa') ?></b></td>
									<td style="color:red; cursor:pointer;" onclick="if (confirm('<?php _e('Are you sure you want to delete this comment?', 'wppa') ?>')) document.location = '<?php echo($delurl) ?>';"><b><?php _e('Delete', 'wppa') ?></b></td>
								</tr>						
							<?php }					
						}					
						?>
					</tbody>
					<tfoot style="font-weight: bold" class="">
						<tr>
							<th scope="col"><?php _e('Photo', 'wppa') ?></th>
							<th scope="col"><?php _e('#', 'wppa') ?></th>
							<th scope="col"><?php _e('User', 'wppa') ?></th>
							<th scope="col"><?php _e('Email', 'wppa') ?></th>
							<th scope="col"><?php _e('Time since', 'wppa') ?></th>
							<th scope="col"><?php _e('Comment', 'wppa') ?></th>
							<th scope="col"><?php _e('Status', 'wppa') ?></th>
							<th scope="col"><?php _e('Edit', 'wppa') ?></th>
							<th scope="col"><?php _e('Delete', 'wppa') ?></th>
						</tr>
					</tfoot>
				</table>
				<?php wppa_comment_submit_pages($nitems, $npages) ?>
			</form>
		</div>
<?php
	}
}

function wppa_edit_commentstatus($value, $index) {
global $wpdb;

	switch ($value) {
		case 'delete':
//			$wpdb->query("DELETE FROM ".WPPA_COMMENTS." WHERE id=".$index." LIMIT 1");
			break;
		default:
			$wpdb->query("UPDATE ".WPPA_COMMENTS." SET status='".$value."' WHERE id=".$index." LIMIT 1");
			break;
	}
}

function wppa_edit_comment($id) {
global $wpdb;

	$record = $wpdb->get_row("SELECT * FROM ".WPPA_COMMENTS." WHERE id=".$id." LIMIT 0,1", "ARRAY_A");
	if ($record) {
		if (isset($_POST['comment'])) $record['comment'] = $_POST['comment'];
		if (isset($_POST['email'])) $record['email'] = $_POST['email'];
		if (isset($_POST['user'])) $record['user'] = $_POST['user'];
		
		$iret = $wpdb->query("UPDATE ".WPPA_COMMENTS." SET comment='".$record['comment']."', email='".$record['email']."', user='".$record['user']."' WHERE id=".$id." LIMIT 1");
		if ($iret === false) {
			wppa_error_message(__('Unable to update comment. Err =', 'wppa').' 2.');
			return false;
		}
		return true;		
	}
	else {
		wppa_error_message(__('Unable to update comment. Err =', 'wppa').' 1.');
		return false;
	}
}

function wppa_comment_submit_pages($nitems, $npages) {
?>
	<p>
		<input type="submit" class="button-primary" name="wppa_submit" value="<?php _e('Save Changes', 'wppa'); ?>" />
		&nbsp;
		<?php 
		$curpage = isset($_GET['compage']) ? $_GET['compage'] : '1'; 
		$prevpage = $curpage - '1';
		$nextpage = $curpage + '1'; 
		$prevurl = wppa_dbg_url(get_admin_url().'/admin.php?page=manage_comments&compage='.$nextpage);
		$pagurl = wppa_dbg_url(get_admin_url().'/admin.php?page=manage_comments').'&compage=';
		$nexturl = wppa_dbg_url(get_admin_url().'/admin.php?page=manage_comments&compage='.$nextpage);
		if ($npages > '1') {
			if ($curpage != '1') {
				?><a href="<?php echo($prevurl) ?>"><?php _e('Prev page', 'wppa') ?></a><?php
			}
			$i = '1';
			while ($i <= $npages) {
				if ($i == $curpage) {
					echo(' '.$i.' ');
				}
				else {
					?>&nbsp;<a href="<?php echo($pagurl.$i) ?>"><?php echo($i) ?></a>&nbsp;<?php
				}
				$i++;
			}
			if ($curpage != $npages) {
				?><a href="<?php echo($nexturl) ?>"><?php _e('Next page', 'wppa') ?></a><?php
			}
		}?>
	</p>
<?php
}
