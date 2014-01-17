<?php
/* wppa-common-functions.php
*
* Functions used in admin and in themes
* version 5.2.10
*
*/

// Initialize globals and option settings
function wppa_initialize_runtime($force = false) {
global $wppa;
global $wppa_opt;
global $wppa_revno;
global $wppa_api_version;
global $blog_id;
global $wpdb;
global $wppa_initruntimetime;

//$force = true;	// debug

	$wppa_initruntimetime = - microtime(true);

	if ($force) {
		$wppa = false; 					// destroy existing arrays
		$wppa_opt = false;
	}

	if (!is_array($wppa)) {
		$wppa = array (
			'debug' 					=> false,
			'revno' 					=> $wppa_revno,				// set in wppa.php
			'api_version' 				=> $wppa_api_version,		// set in wppa.php
			'fullsize' 					=> '',
			'enlarge' 					=> false,
			'occur' 					=> '0',
			'master_occur' 				=> '0',
			'widget_occur' 				=> '0',
			'in_widget' 				=> false,
			'is_cover' 					=> '0',
			'is_slide' 					=> '0',
			'is_slideonly' 				=> '0',
			'is_slideonlyf'				=> '0',
			'is_filmonly'				=> '0',
			'film_on' 					=> '0',
			'browse_on' 				=> '0',
			'name_on' 					=> '0',
			'desc_on' 					=> '0',
			'numbar_on' 				=> '0',
			'single_photo' 				=> '',
			'is_mphoto' 				=> '0',
			'start_album' 				=> '',
			'align' 					=> '',
			'src' 						=> false,
			'portrait_only' 			=> false,
			'in_widget_linkurl' 		=> '',
			'in_widget_linktitle' 		=> '',
			'in_widget_timeout' 		=> '0',
			'ss_widget_valign' 			=> '',
			'album_count' 				=> '0',
			'thumb_count' 				=> '0',
			'out' 						=> '',
			'auto_colwidth' 			=> false,
			'permalink' 				=> '',
			'randseed' 					=> isset ( $_REQUEST['wppa-randseed'] ) ? $_REQUEST['wppa-randseed'] : time() % '4711',
			'rendering_enabled' 		=> false,
			'tabcount' 					=> '0',
			'comment_id' 				=> '',
			'comment_photo' 			=> '0',
			'comment_user' 				=> '',
			'comment_email' 			=> '',
			'comment_text' 				=> '',
			'no_default' 				=> false,
			'in_widget_frame_height' 	=> '',
			'in_widget_frame_width'		=> '',
			'user_uploaded'				=> false,
			'current_album'				=> '0',
			'searchstring'				=> wppa_test_for_search(),
			'searchresults'				=> '',
			'any'						=> false,
			'ajax'						=> false,
			'error'						=> false,
			'iptc'						=> false,
			'exif'						=> false,
			'is_topten'					=> false,
			'topten_count'				=> '0',
			'is_lasten'					=> false,
			'lasten_count'				=> '0',
			'is_featen'					=> false,
			'featen_count'				=> '0',
			'start_photo'				=> '0',
			'is_single'					=> false,
			'is_landing'				=> '0',
			'is_comten'					=> false,
			'comten_count'				=> '0',
			'is_tag'					=> false,
			'photos_only'				=> false,
			'albums_only'				=> false,
			'page'						=> '',
			'geo'						=> '',
			'continue'					=> '',
			'is_upload'					=> false,
			'ajax_import_files'			=> false,
			'ajax_import_files_done'	=> false,
			'last_albums'				=> false,
			'last_albums_parent'		=> '0',
			'is_multitagbox' 			=> false,
			'is_tagcloudbox' 			=> false,
			'taglist' 					=> '',
			'tagcols'					=> '2',
			'is_related'				=> false,
			'related_count'				=> '0',
			'is_owner'					=> '',
			'is_upldr'					=> '',
			'no_esc'					=> false,
			'front_edit'				=> false,
			'is_autopage'				=> false,
			'is_cat'					=> false



		);
	}
	
	if ( is_admin() ) {
		$wppa_opt = get_option('wppa_cached_options_admin', false);
	}
	else {
		$wppa_opt = get_option('wppa_cached_options', false);
	}
				
	if ( ! is_array($wppa_opt) ) {
		$wppa_opt = array ( 'wppa_revision' 			=> '',
							'wppa_prevrev'				=> '',
	
						// Table 0: Initial setup
						'wppa_i_responsive'				=> '',
						'wppa_i_downsize'				=> '',
						'wppa_i_userupload'				=> '',
						'wppa_i_rating'					=> '',
						'wppa_i_comment'				=> '',
						'wppa_i_share'					=> '',
						'wppa_i_iptc'					=> '',
						'wppa_i_exif'					=> '',
						'wppa_i_done'					=> '',
						
						// Table I: Sizes
						// A System
						'wppa_colwidth' 				=> '',	// 1
						'wppa_resize_on_upload' 		=> '',	// 2
						'wppa_resize_to'				=> '',	// 3
						'wppa_min_thumbs' 				=> '',	// 4
						'wppa_bwidth' 					=> '',	// 5
						'wppa_bradius' 					=> '',	// 6
						'wppa_box_spacing'				=> '',	// 7
						// B Fullsize
						'wppa_fullsize' 				=> '',	// 1
						'wppa_maxheight' 				=> '',	// 2
						'wppa_enlarge' 					=> '',	// 3
						'wppa_fullimage_border_width' 	=> '',	// 4
						'wppa_numbar_max'				=> '',	// 5
						'wppa_share_size'				=> '',
						'wppa_mini_treshold'			=> '',
						'wppa_slideshow_pagesize'		=> '',
						// C Thumbnails
						'wppa_thumbsize' 				=> '',	// 1
						'wppa_thumbsize_alt' 			=> '',	// 1a
						'wppa_thumb_aspect'				=> '',	// 2
						'wppa_tf_width' 				=> '',	// 3
						'wppa_tf_width_alt' 			=> '',	// 3a
						'wppa_tf_height' 				=> '',	// 4
						'wppa_tf_height_alt' 			=> '',	// 4a
						'wppa_tn_margin' 				=> '',	// 5
						'wppa_thumb_auto' 				=> '',	// 6
						'wppa_thumb_page_size' 			=> '',	// 7
						'wppa_popupsize' 				=> '',	// 8
						'wppa_use_thumbs_if_fit'		=> '',	// 9
						// D Covers
						'wppa_max_cover_width'			=> '',	// 1
						'wppa_text_frame_height'		=> '',	// 2
						'wppa_smallsize' 				=> '',	// 3
						'wppa_smallsize_multi'			=> '', 	// 3.1
						'wppa_coversize_is_height'		=> '',	// 3.9
						'wppa_album_page_size' 			=> '',	// 4
						// E Rating & comments
						'wppa_rating_max'				=> '',	// 1
						'wppa_rating_prec'				=> '',	// 2
						'wppa_gravatar_size'			=> '',	// 3
						'wppa_ratspacing'				=> '',
						// F Widgets
						'wppa_topten_count' 			=> '',	// 1
						'wppa_topten_size' 				=> '',	// 2
						'wppa_comten_count'			=> '',	// 3
						'wppa_comten_size'				=> '',	// 4
						'wppa_featen_count'				=> '',			// Moet nog in settings
						'wppa_featen_size'				=> '',
						'wppa_thumbnail_widget_count'	=> '',	// 5
						'wppa_thumbnail_widget_size'	=> '',	// 6
						'wppa_lasten_count' 			=> '',	// 1
						'wppa_lasten_size' 				=> '',	// 2
						'wppa_album_widget_count'		=> '',
						'wppa_album_widget_size'		=> '',
						
						'wppa_related_count'			=> '',

						// G Overlay
						'wppa_ovl_txt_lines'			=> '',	// 1
						'wppa_magnifier'				=> '',	// 2
						
						// Table II: Visibility
						// A Breadcrumb
						'wppa_show_bread_posts' 			=> '',	// 1a
						'wppa_show_bread_pages'				=> '', 	// 1b
						'wppa_bc_on_search'					=> '',	// 2
						'wppa_bc_on_topten'					=> '',	// 3.0
						'wppa_bc_on_lasten'					=> '',	// 3.1
						'wppa_bc_on_comten'					=> '',	// 3.2
						'wppa_bc_on_featen'					=> '', 	// 3.3
						'wppa_bc_on_tag'					=> '',	// 3.4
						'wppa_bc_on_related'				=> '', 	// 3.5
						'wppa_show_home' 					=> '',	// 4
						'wppa_show_page' 					=> '',	// 4
						'wppa_bc_separator' 				=> '',	// 5
						'wppa_bc_txt' 						=> '',	// 6
						'wppa_bc_url' 						=> '',	// 7
						'wppa_pagelink_pos'					=> '', 	// 8
						'wppa_bc_slide_thumblink'			=> '',	// 9
						// B Slideshow
						'wppa_show_startstop_navigation' 	=> '',	// 1
						'wppa_show_browse_navigation' 		=> '',	// 2
						'wppa_filmstrip' 					=> '',	// 3
						'wppa_film_show_glue' 				=> '',	// 4
						'wppa_show_full_name' 				=> '',	// 5
						'wppa_show_full_owner'				=> '',	// 5.1
						'wppa_show_full_desc' 				=> '',	// 6
						'wppa_hide_when_empty'				=> '',	// 6.1
						'wppa_rating_on' 					=> '',	// 7
						'wppa_dislike_mail_every'			=> '',	// 7.1
						'wppa_dislike_set_pending'			=> '',
						'wppa_dislike_delete'				=> '',
						'wppa_dislike_show_count'			=> '',
						'wppa_rating_display_type'			=> '',	// 8
						'wppa_show_avg_rating'				=> '',	// 9
						'wppa_show_comments' 				=> '',	// 10
						'wppa_comment_gravatar'				=> '',	// 11
						'wppa_comment_gravatar_url'			=> '',	// 12
						'wppa_show_bbb'						=> '',	// 13
						'wppa_custom_on' 					=> '',	// 14
						'wppa_custom_content' 				=> '',	// 15
						'wppa_show_slideshownumbar'  		=> '',	// 16
						'wppa_show_iptc'					=> '',	// 17
						'wppa_show_iptc_open'				=> '',
						'wppa_show_exif'					=> '',	// 18
						'wppa_show_exif_open'				=> '',
						'wppa_copyright_on'					=> '',	// 19
						'wppa_copyright_notice'				=> '',	// 20
						'wppa_share_on'						=> '',
						'wppa_share_hide_when_running' 		=> '',
						'wppa_share_on_widget'				=> '',
						'wppa_share_on_thumbs'				=> '',
						'wppa_share_on_lightbox' 			=> '',
						'wppa_share_on_mphoto' 				=> '',
						'wppa_share_qr'						=> '',
						'wppa_share_facebook'				=> '',
						'wppa_share_twitter'				=> '',
						'wppa_share_hyves'					=> '',
						'wppa_share_google'					=> '',
						'wppa_share_pinterest'				=> '',
						'wppa_share_linkedin'				=> '',

						'wppa_facebook_comments'			=> '',
						'wppa_facebook_like'				=> '',
						'wppa_facebook_admin_id'			=> '',
						'wppa_facebook_app_id'				=> '',
						'wppa_load_facebook_sdk' 			=> '',
						'wppa_share_single_image'			=> '',

						// C Thumbnails
						'wppa_thumb_text_name' 				=> '',	// 1
						'wppa_thumb_text_owner'				=> '',	// 1.1
						'wppa_thumb_text_desc' 				=> '',	// 2
						'wppa_thumb_text_rating' 			=> '',	// 3
						'wppa_popup_text_name' 				=> '',	// 4
						'wppa_popup_text_desc' 				=> '',	// 5
						'wppa_popup_text_desc_strip'		=> '',	// 5.1
						'wppa_popup_text_rating' 			=> '',	// 6
						'wppa_popup_text_ncomments'			=> '',
						'wppa_show_rating_count'			=> '',	// 7
						'wppa_thumb_text_viewcount'			=> '',
						'wppa_albdesc_on_thumbarea'			=> '',
						'wppa_albname_on_thumbarea'			=> '',

						// D Covers
						'wppa_show_cover_text' 				=> '',	// 1
						'wppa_enable_slideshow' 			=> '',	// 2
						'wppa_show_slideshowbrowselink' 	=> '',	// 3
						'wppa_show_viewlink'				=> '',	// 4
						'wppa_show_treecount'				=> '',
						'wppa_skip_empty_albums'			=> '',
						// E Widgets
						'wppa_show_bbb_widget'				=> '',	// 1
						'wppa_show_albwidget_tooltip'		=> '',
						// F Overlay
						'wppa_ovl_close_txt'				=> '',
						'wppa_ovl_theme'					=> '',
						'wppa_ovl_slide_name'				=> '',
						'wppa_ovl_slide_desc'				=> '',
						'wppa_ovl_thumb_name'				=> '',
						'wppa_ovl_thumb_desc'				=> '',
						'wppa_ovl_potd_name'				=> '',
						'wppa_ovl_potd_desc'				=> '',
						'wppa_ovl_sphoto_name'				=> '',
						'wppa_ovl_sphoto_desc'				=> '',
						'wppa_ovl_mphoto_name'				=> '',
						'wppa_ovl_mphoto_desc'				=> '',
						'wppa_ovl_alw_name'					=> '',
						'wppa_ovl_alw_desc'					=> '',
						'wppa_ovl_cover_name'				=> '',
						'wppa_ovl_cover_desc'				=> '',
						'wppa_show_zoomin'					=> '',
						'wppa_ovl_show_counter'				=> '',

						// Table III: Backgrounds
						'wppa_bgcolor_even' 			=> '',
						'wppa_bcolor_even' 				=> '',
						'wppa_bgcolor_alt' 				=> '',
						'wppa_bcolor_alt' 				=> '',
						'wppa_bgcolor_nav' 				=> '',
						'wppa_bcolor_nav' 				=> '',
						'wppa_bgcolor_namedesc' 		=> '',
						'wppa_bcolor_namedesc' 			=> '',
						'wppa_bgcolor_com' 				=> '',
						'wppa_bcolor_com' 				=> '',
						'wppa_bgcolor_img'				=> '',
						'wppa_bcolor_img'				=> '',
						'wppa_bgcolor_fullimg' 			=> '',
						'wppa_bcolor_fullimg' 			=> '',
						'wppa_bgcolor_cus'				=> '',
						'wppa_bcolor_cus'				=> '',
						'wppa_bgcolor_numbar'			=> '',
						'wppa_bcolor_numbar'			=> '',
						'wppa_bgcolor_numbar_active'	=> '',
						'wppa_bcolor_numbar_active'	 	=> '',
						'wppa_bgcolor_iptc'				=> '',
						'wppa_bcolor_iptc' 				=> '',
						'wppa_bgcolor_exif'				=> '',
						'wppa_bcolor_exif' 				=> '',
						'wppa_bgcolor_share'			=> '',
						'wppa_bcolor_share' 			=> '',
						'wppa_bgcolor_upload'			=> '',
						'wppa_bcolor_upload'			=> '',
						'wppa_bgcolor_multitag'			=> '',
						'wppa_bcolor_multitag'			=> '',
						'wppa_bgcolor_tagcloud'			=> '',
						'wppa_bcolor_tagcloud'			=> '',

						// Table IV: Behaviour
						// A System
						'wppa_allow_ajax'				=> '',	// 1
						'wppa_ajax_non_admin'			=> '',
						'wppa_use_photo_names_in_urls'	=> '',	// 2
						'wppa_use_pretty_links'			=> '',
						'wppa_update_addressline'		=> '',
						'wppa_render_shortcode_always' 	=> '',
						'wppa_track_viewcounts'			=> '',
						'wppa_auto_page'				=> '',
						'wppa_auto_page_type'			=> '',
						'wppa_auto_page_links'			=> '',


						// B Full size and Slideshow
						'wppa_fullvalign' 				=> '',	// 1
						'wppa_fullhalign' 				=> '',	// 2
						'wppa_start_slide' 				=> '',	// 3
						'wppa_start_slideonly'			=> '',	// 3.1
						'wppa_animation_type'			=> '',	// 4
						'wppa_slideshow_timeout'		=> '',	// 5
						'wppa_animation_speed' 			=> '',	// 6
						'wppa_slide_pause'				=> '',	// 7
						'wppa_slide_wrap'				=> '',	// 8
						'wppa_fulldesc_align'			=> '',	// 9
						'wppa_clean_pbr'				=> '',	// 10
						'wppa_run_wppautop_on_desc'		=> '',	// 11
						'wppa_auto_open_comments'		=> '',
						'wppa_film_hover_goto'			=> '',
						'wppa_slide_swipe'				=> '',
						// C Thumbnail
						'wppa_list_photos_by' 			=> '',	// 1
						'wppa_list_photos_desc' 		=> '',	// 2
						'wppa_thumbtype' 				=> '',	// 3
						'wppa_thumbphoto_left' 			=> '',	// 4
						'wppa_valign' 					=> '',	// 5
						'wppa_use_thumb_opacity' 		=> '',	// 6
						'wppa_thumb_opacity' 			=> '',	// 7
						'wppa_use_thumb_popup' 			=> '',	// 8
						// D Albums and covers
						'wppa_list_albums_by' 			=> '',	// 1
						'wppa_list_albums_desc' 		=> '',	// 2
						'wppa_coverphoto_pos'			=> '',	// 3
						'wppa_use_cover_opacity' 		=> '',	// 4
						'wppa_cover_opacity' 			=> '',	// 5
						'wppa_cover_type'				=> '',
						'wppa_imgfact_count'			=> '',
						// E Rating
						'wppa_rating_login' 			=> '',	// 1
						'wppa_rating_change' 			=> '',	// 2
						'wppa_rating_multi' 			=> '',	// 3
						'wppa_dislike_value'			=> '',
//						'wppa_rating_use_ajax'			=> '',	// 4
						'wppa_next_on_callback'			=> '',	// 5
						'wppa_star_opacity'				=> '',	// 6
						'wppa_vote_button_text'			=> '',
						'wppa_voted_button_text'		=> '',
						'wppa_vote_thumb'				=> '',
						// F Comments
						'wppa_comment_login' 			=> '',	// 1
						'wppa_comments_desc'			=> '',	// 2
						'wppa_comment_moderation'		=> '',	// 3
						'wppa_comment_email_required'	=> '',	// 4
						'wppa_comment_notify'			=> '',
						'wppa_comment_notify_added'		=> '',
						'wppa_comten_alt_display'		=> '',
						'wppa_comten_alt_thumbsize'		=> '',
						'wppa_comment_smiley_picker' 	=> '',
						
						// G Overlay
						'wppa_ovl_opacity'				=> '',
						'wppa_ovl_onclick'				=> '',
						'wppa_ovl_anim'					=> '',
						'wppa_ovl_chrome_at_top' 		=> '',
						'wppa_lightbox_global'			=> '',


						// Table V: Fonts
						'wppa_fontfamily_title' 	=> '',
						'wppa_fontsize_title' 		=> '',
						'wppa_fontcolor_title' 		=> '',
						'wppa_fontweight_title'		=> '',
						'wppa_fontfamily_fulldesc' 	=> '',
						'wppa_fontsize_fulldesc' 	=> '',
						'wppa_fontcolor_fulldesc' 	=> '',
						'wppa_fontweight_fulldesc'	=> '',
						'wppa_fontfamily_fulltitle' => '',
						'wppa_fontsize_fulltitle' 	=> '',
						'wppa_fontcolor_fulltitle' 	=> '',
						'wppa_fontweight_fulltitle'	=> '',
						'wppa_fontfamily_nav' 		=> '',
						'wppa_fontsize_nav' 		=> '',
						'wppa_fontcolor_nav' 		=> '',
						'wppa_fontweight_nav'		=> '',
						'wppa_fontfamily_thumb' 	=> '',
						'wppa_fontsize_thumb' 		=> '',
						'wppa_fontcolor_thumb' 		=> '',
						'wppa_fontweight_thumb'		=> '',
						'wppa_fontfamily_box' 		=> '',
						'wppa_fontsize_box' 		=> '',
						'wppa_fontcolor_box' 		=> '',
						'wppa_fontweight_box'		=> '',
						'wppa_fontfamily_numbar' 	=> '',
						'wppa_fontsize_numbar' 		=> '',
						'wppa_fontcolor_numbar' 	=> '',
						'wppa_fontweight_numbar'	=> '',
						'wppa_fontfamily_numbar_active' 	=> '',
						'wppa_fontsize_numbar_active' 		=> '',
						'wppa_fontcolor_numbar_active' 	=> '',
						'wppa_fontweight_numbar_active'	=> '',
						
						'wppa_fontfamily_lightbox'	=> '',
						'wppa_fontsize_lightbox'	=> '',
						'wppa_fontcolor_lightbox'	=> '',
						'wppa_fontweight_lightbox'	=> '',
						
						'wppa_fontsize_widget_thumb'	=> '',

						
						// Table VI: Links
						'wppa_sphoto_linktype' 				=> '',
						'wppa_sphoto_linkpage' 				=> '',
						'wppa_sphoto_blank'					=> '',
						'wppa_sphoto_overrule'				=> '',

						'wppa_mphoto_linktype' 				=> '',
						'wppa_mphoto_linkpage' 				=> '',
						'wppa_mphoto_blank'					=> '',
						'wppa_mphoto_overrule'				=> '',
						
						'wppa_thumb_linktype' 				=> '',
						'wppa_thumb_linkpage' 				=> '',
						'wppa_thumb_blank'					=> '',
						'wppa_thumb_overrule'				=> '',
						
						'wppa_topten_widget_linktype' 		=> '',
						'wppa_topten_widget_linkpage' 		=> '',
						'wppa_topten_blank'					=> '',
						'wppa_topten_overrule'				=> '',
						
						'wppa_featen_widget_linktype' 		=> '',
						'wppa_featen_widget_linkpage' 		=> '',
						'wppa_featen_blank'					=> '',
						'wppa_featen_overrule'				=> '',

						'wppa_slideonly_widget_linktype' 	=> '',
						'wppa_slideonly_widget_linkpage' 	=> '',
						'wppa_sswidget_blank'				=> '',
						'wppa_sswidget_overrule'			=> '',

						'wppa_widget_linktype' 				=> '',
						'wppa_widget_linkpage' 				=> '',
						'wppa_potd_blank'					=> '',
						'wppa_potdwidget_overrule'			=> '',

						'wppa_coverimg_linktype' 			=> '',
						'wppa_coverimg_linkpage' 			=> '',
						'wppa_coverimg_blank'				=> '',
						'wppa_coverimg_overrule'			=> '',

						'wppa_comment_widget_linktype'		=> '',
						'wppa_comment_widget_linkpage'		=> '',
						'wppa_comment_blank'				=> '',
						'wppa_comment_overrule'				=> '',

						'wppa_slideshow_linktype'			=> '',
						'wppa_slideshow_linkpage'			=> '',
						'wppa_slideshow_blank'				=> '',
						'wppa_slideshow_overrule'			=> '',

						'wppa_thumbnail_widget_linktype'	=> '',
						'wppa_thumbnail_widget_linkpage'	=> '',
						'wppa_thumbnail_widget_overrule'	=> '',
						'wppa_thumbnail_widget_blank'		=> '',

						'wppa_film_linktype'				=> '',
						
						'wppa_lasten_widget_linktype' 		=> '',
						'wppa_lasten_widget_linkpage' 		=> '',
						'wppa_lasten_blank'					=> '',
						'wppa_lasten_overrule'				=> '',

						'wppa_art_monkey_link'				=> '',
						'wppa_art_monkey_popup_link'		=> '',
						'wppa_artmonkey_use_source'			=> '',
						'wppa_art_monkey_display' 			=> '',
						
						'wppa_album_widget_linktype'		=> '',
						'wppa_album_widget_linkpage'		=> '',
						'wppa_album_widget_blank'			=> '',

						'wppa_tagcloud_linktype'			=> '',
						'wppa_tagcloud_linkpage'			=> '',
						'wppa_tagcloud_blank'				=> '',

						'wppa_multitag_linktype'			=> '',
						'wppa_multitag_linkpage'			=> '',
						'wppa_multitag_blank'				=> '',

						'wppa_super_view_linkpage'			=> '',
						'wppa_upldr_widget_linkpage' 		=> '',
						
						// Table VII: Security
						// B
						'wppa_user_upload_login'	=> '',
						'wppa_owner_only' 			=> '',
						'wppa_user_upload_on'		=> '',
						'wppa_upload_moderate'		=> '',
						'wppa_upload_edit'			=> '',
						'wppa_upload_notify' 		=> '',
						'wppa_upload_backend_notify'	=> '',
						'wppa_upload_one_only' 		=> '',
						'wppa_memcheck_frontend'	=> '',
						'wppa_memcheck_admin'		=> '',
						'wppa_comment_captcha'		=> '',
						'wppa_spam_maxage'			=> '',
						'wppa_user_create_on'		=> '',
						'wppa_user_create_login'	=> '',
						
						'wppa_editor_upload_limit_count'		=> '',
						'wppa_editor_upload_limit_time'			=> '',
						'wppa_author_upload_limit_count'		=> '',
						'wppa_author_upload_limit_time'			=> '',
						'wppa_contributor_upload_limit_count'	=> '',
						'wppa_contributor_upload_limit_time'	=> '',
						'wppa_subscriber_upload_limit_count'	=> '',
						'wppa_subscriber_upload_limit_time'		=> '',
						'wppa_loggedout_upload_limit_count'		=> '',
						'wppa_loggedout_upload_limit_time' 		=> '',


						
						// Table VIII: Actions
						// A Harmless
						'wppa_setup' 				=> '',
						'wppa_backup' 				=> '',
						'wppa_load_skin' 			=> '',
						'wppa_skinfile' 			=> '',
						'wppa_regen' 				=> '',
						'wppa_rerate'				=> '',
						'wppa_cleanup'				=> '',
						'wppa_recup'				=> '',
						'wppa_file_system'			=> '',
						'wppa_blacklist_user' 		=> '',
						'wppa_un_blacklist_user' 	=> '',
						// B Irreversable
						'wppa_rating_clear' 		=> '',
						'wppa_iptc_clear'			=> '',
						'wppa_exif_clear'			=> '',
						'wppa_apply_new_photodesc_all' 	=> '',
						'wppa_remake_index' 		=> '',
						'wppa_extend_index'			=> '',
						'wppa_list_index'			=> '',
						'wppa_append_text'			=> '',
						'wppa_append_to_photodesc' 	=> '',
						'wppa_remove_text'			=> '',
						'wppa_remove_from_photodesc'	=> '',
						'wppa_remove_empty_albums'	=> '',

						// Table IX: Miscellaneous
						'wppa_check_balance'			=> '',
						'wppa_arrow_color' 				=> '',
						'wppa_search_linkpage' 			=> '',
						'wppa_excl_sep' 				=> '',
						'wppa_search_tags'				=> '',
						'wppa_search_cats'				=> '',
						'wppa_search_comments' 			=> '',
						'wppa_photos_only'				=> '',	// 3
						'wppa_indexed_search'			=> '',
						'wppa_max_search_photos'		=> '',
						'wppa_max_search_albums'		=> '',
						'wppa_tags_or_only'				=> '',
						'wppa_wild_front'				=> '',
						
						'wppa_add_shortcode_to_post'	=> '',
						'wppa_shortcode_to_add'			=> '',
						
						'wppa_meta_page'				=> '',	// 9
						'wppa_meta_all'					=> '',	// 10
						'wppa_cp_points_comment'		=> '',
						'wppa_cp_points_rating'			=> '',
						'wppa_cp_points_upload'			=> '',
						'wppa_use_scabn'				=> '',
						'wppa_gpx_implementation' 		=> '',
						'wppa_map_height' 				=> '',
						'wppa_map_apikey' 				=> '',
						'wppa_gpx_shortcode'			=> '',

						'wppa_use_wp_editor'			=> '',	//A 11
						'wppa_hier_albsel' 				=> '',
						'wppa_hier_pagesel'				=> '',
						'wppa_alt_type'					=> '',
						'wppa_photo_admin_pagesize'		=> '',
						'wppa_comment_admin_pagesize'	=> '',
						'wppa_jpeg_quality'				=> '',
						'wppa_geo_edit' 				=> '',
						'wppa_auto_continue'			=> '',
						'wppa_max_execution_time'		=> '',
						'wppa_adminbarmenu_admin'		=> '',
						'wppa_adminbarmenu_frontend'	=> '',
						'wppa_feed_use_thumb'			=> '',
						'wppa_og_tags_on'				=> '',
						
						'wppa_html' 					=> '',
						'wppa_allow_debug' 				=> '',
						'wppa_slide_order'				=> '',
						'wppa_slide_order_split'		=> '',
						'wppa_swap_namedesc' 			=> '',
						'wppa_split_namedesc'			=> '',
						'wppa_max_album_newtime'		=> '',
						'wppa_max_photo_newtime'		=> '',
						'wppa_lightbox_name'			=> '',
						'wppa_filter_priority'			=> '',
						'wppa_apply_newphoto_desc'		=> '',
						'wppa_apply_newphoto_desc_user'	=> '',
						'wppa_newphoto_description'		=> '',
						'wppa_upload_limit_count'		=> '',		// 5a
						'wppa_upload_limit_time'		=> '',		// 5b
						'wppa_show_album_full'			=> '',
						'wppa_grant_an_album'			=> '',
						'wppa_grant_name'				=> '',
						'wppa_grant_parent'				=> '',
						'wppa_default_parent' 			=> '',
						'wppa_max_albums'				=> '',
						'wppa_alt_is_restricted'		=> '',
						'wppa_link_is_restricted'		=> '',
						'wppa_covertype_is_restricted'	=> '',
						'wppa_porder_restricted'		=> '',
						
						
//						'wppa_strip_file_ext'			=> '',
						'wppa_newphoto_name_method' 	=> '',

						'wppa_copy_timestamp'			=> '',
						
						'wppa_watermark_on'				=> '',
						'wppa_watermark_user'			=> '',
						'wppa_watermark_file'			=> '',
						'wppa_watermark_pos'			=> '',
						'wppa_watermark_upload'			=> '',
						'wppa_watermark_opacity'		=> '',
						'wppa_allow_foreign_shortcodes' => '',
						'wppa_allow_foreign_shortcodes_thumbs' 	=> '',

						// Photo of the day widget admin
						'wppa_widgettitle'			=> '',
						'wppa_widget_linkurl'		=> '',
						'wppa_widget_linktitle' 	=> '',
						'wppa_widget_subtitle'		=> '',
						'wppa_widget_album'			=> '',
						'wppa_widget_photo'			=> '',
						'wppa_potd_align' 			=> '',
						'wppa_widget_method'		=> '',
						'wppa_widget_period'		=> '',
						'wppa_widget_width'			=> '',
						
						// Topten widget
						'wppa_toptenwidgettitle'	=> '',

						// Thumbnail widget
						'wppa_thumbnailwidgettitle'	=> '',
						
						// Search widget
						'wppa_searchwidgettitle'	=> '',
						
						// Comment admin
						'wppa_comadmin_show' 		=> '',
						'wppa_comadmin_order' 		=> '',

						// QR code settings
						'wppa_qr_size'				=> '',
						'wppa_qr_color'				=> '',
						'wppa_qr_bgcolor'			=> '',

						// H Source file management and import/upload
						'wppa_keep_source_admin'	=> '',
						'wppa_keep_source_frontend' => '',
						'wppa_source_dir'			=> '',
						'wppa_keep_sync'			=> '',
						'wppa_remake_add'			=> '',
						'wppa_save_iptc'			=> '',
						'wppa_save_exif'			=> '',
						'wppa_exif_max_array_size'	=> '',
						'wppa_chgsrc_is_restricted'		=> '',
						'wppa_newpag_create'			=> '',
						'wppa_newpag_content'			=> '',
						'wppa_newpag_type'				=> '',
						'wppa_newpag_status'			=> '',
						
						// J CDN Services
						'wppa_cdn_service'			=> '',
						'wppa_cdn_cloud_name'		=> '',
						'wppa_cdn_api_key'			=> '',
						'wppa_cdn_api_secret'		=> '',
						'wppa_cdn_service_update'	=> '',
						'wppa_delete_all_from_cloudinary' 	=> '',
						
						'wppa_fotomoto_on' 			=> ''


		);
		
		array_walk($wppa_opt, 'wppa_set_options');
		
		if ( is_admin() ) {
			update_option('wppa_cached_options_admin', $wppa_opt);
		}
		else {
			update_option('wppa_cached_options', $wppa_opt);
		}
	}
	
	if (isset($_GET['debug']) && $wppa_opt['wppa_allow_debug']) {
		$key = $_GET['debug'] ? $_GET['debug'] : E_ALL;
		$wppa['debug'] = $key;
	}
	
	wppa_load_language();
	
	if ( ! defined( 'WPPA_UPLOAD') ) {
		if ( is_multisite() && ! WPPA_MULTISITE_GLOBAL ) {
			define( 'WPPA_UPLOAD', 'wp-content/blogs.dir/'.$blog_id);
			define( 'WPPA_UPLOAD_PATH', ABSPATH.WPPA_UPLOAD.'/wppa');
			define( 'WPPA_UPLOAD_URL', get_bloginfo('wpurl').'/'.WPPA_UPLOAD.'/wppa');
			define( 'WPPA_DEPOT', 'wp-content/blogs.dir/'.$blog_id.'/wppa-depot' );			
			define( 'WPPA_DEPOT_PATH', ABSPATH.WPPA_DEPOT );					
			define( 'WPPA_DEPOT_URL', get_bloginfo('wpurl').'/'.WPPA_DEPOT );	
		}
		else {
			define( 'WPPA_UPLOAD', 'wp-content/uploads');
			define( 'WPPA_UPLOAD_PATH', ABSPATH.WPPA_UPLOAD.'/wppa' );
			define( 'WPPA_UPLOAD_URL', get_bloginfo('wpurl').'/'.WPPA_UPLOAD.'/wppa' );
			$user = is_user_logged_in() ? '/'.wppa_get_user() : '';
			define( 'WPPA_DEPOT', 'wp-content/wppa-depot'.$user );
			define( 'WPPA_DEPOT_PATH', ABSPATH.WPPA_DEPOT );
			define( 'WPPA_DEPOT_URL', get_bloginfo('wpurl').'/'.WPPA_DEPOT );
		}
	}
	
	// Delete obsolete spam
	$spammaxage = $wppa_opt['wppa_spam_maxage'];
	if ( $spammaxage != 'none' ) {
		$time = time();
		$obsolete = $time - $spammaxage;
		$iret = $wpdb->query($wpdb->prepare( "DELETE FROM `".WPPA_COMMENTS."` WHERE `status` = 'spam' AND `timestamp` < %s", $obsolete));
		if ( $iret ) wppa_update_option('wppa_spam_auto_delcount', get_option('wppa_spam_auto_delcount', '0') + $iret);
	}
	
	// Create an album if required
	if ( wppa_switch('wppa_grant_an_album') 
		&& wppa_switch('wppa_owner_only')
		&& is_user_logged_in() 
		&& ( current_user_can('wppa_upload') || wppa_switch('wppa_user_upload_on') ) ) {
			$owner = wppa_get_user('login');
			$user = wppa_get_user(get_option('wppa_grant_name', 'display'));
			$albs = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM `".WPPA_ALBUMS."` WHERE `owner` = %s", $owner ));
			if ( ! $albs ) {	// make an album for this user
//				$id = wppa_nextkey(WPPA_ALBUMS);
				$name = $user;
				if ( is_admin() ) {
					$desc = __('Default photo album for', 'wppa').' '.$user;
				}
				else {
					$desc = __a('Default photo album for').' '.$user;
				}
				$parent = $wppa_opt['wppa_grant_parent'];
				$id = wppa_create_album_entry( array ( 'name' => $name, 'description' => $desc, 'a_parent' => $parent) );
				wppa_flush_treecounts($parent);
				wppa_index_add('album', $id);
			}
	}
		
	$wppa_initruntimetime += microtime(true);
}

function wppa_set_options($value, $key) {
global $wppa_opt;

	if (is_admin()) {	// admin needs the raw data
		$wppa_opt[$key] = get_option($key);
	}
	else {
		$temp = get_option($key);
		switch ($temp) {
			case 'no':
				$wppa_opt[$key] = false;
				break;
			case 'yes':
				$wppa_opt[$key] = true;
				break;
			default:
				$wppa_opt[$key] = $temp;
			}	
	}
}

function wppa_load_language() {
//global $wppa_locale;
global $wppa_lang;
global $q_config;
global $wppa;
global $wppa_locale;

	if ( $wppa_locale ) return; // Done already
	
	// See if qTranslate present and actve
	if ( wppa_qtrans_enabled() ) {	
		// Lang in arg?
		if ( isset($_REQUEST['lang']) ) {
			$wppa_lang = $_REQUEST['lang'];
		}
		// no. use q_configs lang
		else {
			$wppa_lang = isset($q_config['language']) ? $q_config['language'] : '';
		}
		// Find locale from lang
		if ( $wppa_lang ) {
			$wppa_locale = isset($q_config['locale'][$wppa_lang]) ? $q_config['locale'][$wppa_lang] : '';
		}
//		wppa_dbg_msg('Lang='.$wppa_lang.', Locale='.$wppa_locale.', Ajax='.$wppa['ajax']);
	}
	// If still not known, get locale from wp-config
	if ( ! $wppa_locale ) {		
		$wppa_locale = get_locale();
//		$lang = substr($wppa_locale, 0, 2);
	}
	
	// Load the language file(s)
	if ($wppa_locale) {
		$domain = is_admin() ? 'wppa' : 'wppa_theme';
		$mofile = WPPA_PATH.'/langs/'.$domain.'-'.$wppa_locale.'.mo';
		$bret = load_textdomain($domain, $mofile);
//		wppa_dbg_msg('Domain: '.$domain.', mofile: '.$mofile.', loaded='.$bret);
		
		// Load theme language file also, for Ajax
		if ( is_admin() ) {
			$domain2 = 'wppa_theme';
			$mofile2 = WPPA_PATH.'/langs/'.$domain2.'-'.$wppa_locale.'.mo';
			$bret2 = load_textdomain($domain2, $mofile2);
//			wppa_dbg_msg('Domain: '.$domain2.', mofile: '.$mofile2.', loaded='.$bret2);
		}
	}
}

function wppa_phpinfo($key = -1) {
global $wppa_opt;

	echo '<div id="phpinfo" style="width:600px; margin:auto;" >';

		ob_start();
		if ( $wppa_opt['wppa_allow_debug'] == 'yes' ) phpinfo(-1); else phpinfo(4);
		$php = ob_get_clean();
		$php = preg_replace(	array	(	'@<!DOCTYPE.*?>@siu',
											'@<html.*?>@siu',
											'@</html.*?>@siu',
											'@<head[^>]*?>.*?</head>@siu',
											'@<body.*?>@siu',
											'@</body.*?>@siu',
											'@cellpadding=".*?"@siu',
											'@border=".*?"@siu',
											'@width=".*?"@siu',
											'@name=".*?"@siu',
											'@<font.*?>@siu',
											'@</font.*?>@siu'
										),
										'',
										$php );
										
		$php = str_replace('Features','Features</td><td>', $php);

		echo $php;	
		
	echo '</div>';
}

// get the url to the plugins image directory
function wppa_get_imgdir() {
	$result = WPPA_URL.'/images/';
	return $result;
}

// get album order
function wppa_get_album_order( $parent = '0' ) {
global $wppa;
global $wppa_opt;
global $album;

	// Init
    $result = '';
	
	// Album given ?
	if ( $parent ) { 	
		wppa_cache_album($parent);
		$order = $album['suba_order_by'];
	}
	else {
		$order = '0';
	}
	if ( ! $order ) $order = $wppa_opt['wppa_list_albums_by'];

	switch ( $order ) {
		case '':
		case '0':
			$result = '';
			break;
		case '1':
			$result = 'ORDER BY a_order';
			break;
		case '-1':
			$result = 'ORDER BY a_order DESC';
			break;
		case '2':
			$result = 'ORDER BY name';
			break;  
		case '-2':
			$result = 'ORDER BY name DESC';
			break;  
		case '3':
			$result = 'ORDER BY RAND('.$wppa['randseed'].')';
			break;
		case '5':
			$result = 'ORDER BY timestamp';
			break;
		case '-5':
			$result = 'ORDER BY timestamp DESC';
			break;
		default:
			wppa_dbg_msg('Unimplemented album order: '.$order, 'red');
	}
	
	return $result;
}

// get photo order
function wppa_get_photo_order( $id = '0', $no_random = false ) {
global $wpdb;
global $wppa;
global $wppa_opt;
    
	if ( $id == '0' ) $order = '0';
	else {
		$order = $wpdb->get_var( $wpdb->prepare( "SELECT `p_order_by` FROM `" . WPPA_ALBUMS . "` WHERE `id` = %s", $id) );
		wppa_dbg_q('Q201');
	}
    if ( ! $order ) $order = $wppa_opt['wppa_list_photos_by'];
	
    switch ($order)
    {
	case '':
	case '0':
		$result = '';
		break;
    case '1':
        $result = 'ORDER BY p_order';
        break;
	case '-1':
		$result = 'ORDER BY p_order DESC';
		break;
    case '2':
        $result = 'ORDER BY name';
        break;
    case '-2':
        $result = 'ORDER BY name DESC';
        break;
    case '3':
		if ($no_random) $result = 'ORDER BY name';
        else $result = 'ORDER BY RAND('.$wppa['randseed'].')';
        break;
    case '-3':
		if ($no_random) $result = 'ORDER BY name DESC';
        else $result = 'ORDER BY RAND('.$wppa['randseed'].') DESC';
        break;
	case '4':
		$result = 'ORDER BY mean_rating';
		break;
	case '-4':
		$result = 'ORDER BY mean_rating DESC';
		break;
	case '5':
		$result = 'ORDER BY timestamp';
		break;
	case '-5':
		$result = 'ORDER BY timestamp DESC';
		break;
	case '6':
		$result = 'ORDER BY rating_count';
		break;
	case '-6':
		$result = 'ORDER BY rating_count DESC';
		break;
	case '7':
		$result = 'ORDER BY exifdtm';
		break;
	case '-7':
		$result = 'ORDER BY exifdtm DESC';
		break;
		
    default:
        wppa_dbg_msg('Unimplemented photo order: '.$order, 'red');
		$result = '';
    }

    return $result;
}


// See if an album is another albums ancestor
function wppa_is_ancestor($anc, $xchild) {

	$child = $xchild;
	if (is_numeric($anc) && is_numeric($child)) {
		$parent = wppa_get_parentalbumid($child);
		while ($parent > '0') {
			if ($anc == $parent) return true;
			$child = $parent;
			$parent = wppa_get_parentalbumid($child);
		}
	}
	return false;
}



function wppa_get_album_id($name = '') {
global $wpdb;

	if ($name == '') return '';
    $name = stripslashes($name);
    $id = $wpdb->get_var( $wpdb->prepare( "SELECT `id` FROM `" . WPPA_ALBUMS . "` WHERE `name` = %s", $name ) );
	wppa_dbg_q('Q205');
    if ($id) {
		return $id;
	}
	else {
		return '';
	}
}

// Check if an image is more landscape than the width/height ratio set in Table I item 2 and 3
function wppa_is_wider($x, $y, $refx = '', $refy = '') {
global $wppa_opt;
	if ( $refx == '' ) {
		$ratioref = $wppa_opt['wppa_fullsize'] / $wppa_opt['wppa_maxheight'];
	}
	else {
		$ratioref = $refx/$refy;
	}
	$ratio = $x / $y;
	return ($ratio > $ratioref);
}

// qtrans hook to see if qtrans is installed
function wppa_qtrans_enabled() {
	return (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage'));
}

// qtrans hook for multi language support of content
function wppa_qtrans($output, $lang = '') {
	if ($lang == '') {
		$output = __($output);
//		if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
//			$output = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($output);
//		}
	} else {
		if (function_exists('qtrans_use')) {
			$output = qtrans_use($lang, $output, false);
		}
	}
	return $output;
}

function wppa_dbg_msg($txt='', $color = 'blue', $force = false, $return = false) {
global $wppa;
	if ( $wppa['debug'] || $force || ( is_admin() && WPPA_DEBUG ) ) {
		$result = '<span style="color:'.$color.';"><small>[WPPA+ dbg msg: '.$txt.']<br /></small></span>';
		if ( $return ) {
			return $result;
		}
		else {
			echo $result;
		}
	}
}

function wppa_dbg_url($link, $js = '') {
global $wppa;
	$result = $link;
	if ($wppa['debug']) {
		if (strpos($result, '?')) {
			if ($js == 'js') $result .= '&';
			else $result .= '&amp;';
		}
		else $result .= '?';
		$result .= 'debug='.$wppa['debug'];
	}
	return $result;
}

function wppa_get_time_since($oldtime) {

	if (is_admin()) {	// admin version
		$newtime = time();
		$diff = $newtime - $oldtime;
		if ($diff < 60) {
			if ($diff == 1) return __('1 second', 'wppa');
			else return $diff.' '.__('seconds', 'wppa');
		}
		$diff = floor($diff / 60);
		if ($diff < 60) {
			if ($diff == 1) return __('1 minute', 'wppa');
			else return $diff.' '.__('minutes', 'wppa');
		}
		$diff = floor($diff / 60);
		if ($diff < 24) {
			if ($diff == 1) return __('1 hour', 'wppa');
			else return $diff.' '.__('hours', 'wppa');
		}
		$diff = floor($diff / 24);
		if ($diff < 7) {
			if ($diff == 1) return __('1 day', 'wppa');
			else return $diff.' '.__('days', 'wppa');
		}
		elseif ($diff < 31) {
			$t = floor($diff / 7);
			if ($t == 1) return __('1 week', 'wppa');
			else return $t.' '.__('weeks', 'wppa');
		}
		$diff = floor($diff / 30.4375);
		if ($diff < 12) {
			if ($diff == 1) return __('1 month', 'wppa');
			else return $diff.' '.__('months', 'wppa');
		}
		$diff = floor($diff / 12);
		if ($diff == 1) return __('1 year', 'wppa');
		else return $diff.' '.__('years', 'wppa');
	}
	else {	// theme version
		$newtime = time();
		$diff = $newtime - $oldtime;
		if ($diff < 60) {
			if ($diff == 1) return __a('1 second', 'wppa_theme');
			else return $diff.' '.__a('seconds', 'wppa_theme');
		}
		$diff = floor($diff / 60);
		if ($diff < 60) {
			if ($diff == 1) return __a('1 minute', 'wppa_theme');
			else return $diff.' '.__a('minutes', 'wppa_theme');
		}
		$diff = floor($diff / 60);
		if ($diff < 24) {
			if ($diff == 1) return __a('1 hour', 'wppa_theme');
			else return $diff.' '.__a('hours', 'wppa_theme');
		}
		$diff = floor($diff / 24);
		if ($diff < 7) {
			if ($diff == 1) return __a('1 day', 'wppa_theme');
			else return $diff.' '.__a('days', 'wppa_theme');
		}
		elseif ($diff < 31) {
			$t = floor($diff / 7);
			if ($t == 1) return __a('1 week', 'wppa_theme');
			else return $t.' '.__a('weeks', 'wppa_theme');
		}
		$diff = floor($diff / 30.4375);
		if ($diff < 12) {
			if ($diff == 1) return __a('1 month', 'wppa_theme');
			else return $diff.' '.__a('months', 'wppa_theme');
		}
		$diff = floor($diff / 12);
		if ($diff == 1) return __a('1 year', 'wppa_theme');
		else return $diff.' '.__a('years', 'wppa_theme');
	}
}

// See if an album or any album is accessable for the current user
function wppa_have_access( $alb ) {
global $wpdb;
global $current_user;
global $wppa_opt;

//	if ( !$alb ) $alb = 'any'; //return false;
	
	// See if there is any album accessable
	if ( ! $alb ) { // == 'any') {
	
		// Administrator has always access OR If all albums are public
		if (current_user_can('administrator') || get_option('wppa_owner_only', 'no') == 'no') {
			$albs = $wpdb->get_results( "SELECT `id` FROM `".WPPA_ALBUMS."`" );
			wppa_dbg_q('Q209');
			if ($albs) return true;
			else return false;	// No albums in system
		}
		
		// Any --- public --- albums?
		$albs = $wpdb->get_results( "SELECT `id` FROM `".WPPA_ALBUMS."` WHERE `owner` = '--- public ---'" );
		wppa_dbg_q('Q210');
		if ($albs) return true;
		
		// Any logged out created albums? ( owner = ip )
		$albs = $wpdb->get_results( "SELECT `owner` FROM `".WPPA_ALBUMS."`", ARRAY_A );
		if ( $albs ) foreach ( $albs as $a ) {
			if ( wppa_is_int( str_replace( '.', '', $a['owner'] ) ) ) return true;
		}
		
		// Any albums owned by this user?
		if ( is_user_logged_in() ) {
			get_currentuserinfo();
			$user = $current_user->user_login;
			$albs = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM `".WPPA_ALBUMS."` WHERE `owner` = %s", $user ) );
			wppa_dbg_q('Q211');
			if ( $albs ) return true;
			else return false;	// No albums for user accessable
		}
	}
	
	// See for given album data array or album number
	else {
	
		// Administrator has always access
		if ( current_user_can('administrator') ) return true;
		
		// If all albums are public
		if ( get_option( 'wppa_owner_only', 'no' ) == 'no' ) return true;
		
		// Find the owner
		$owner = '';
		if (is_array($alb)) {
			$owner = $alb['owner'];
		}
		elseif (is_numeric($alb)) {
			$owner = $wpdb->get_var( $wpdb->prepare( "SELECT `owner` FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $alb ) );
			wppa_dbg_q('Q212');
		}
		
		// -- public --- ?
		if ( $owner == '--- public ---' ) return true;
		if ( wppa_is_int( str_replace( '.', '', $owner ) ) ) return true;
		
		// Find the user
		if ( is_user_logged_in() ) {
			get_currentuserinfo();
			if ( $current_user->user_login == $owner ) return true;
		}
	}
	return false;
}

function wppa_make_the_photo_files($file, $image_id, $ext) {
global $wppa_opt;
global $wppa;
global $wpdb;

	wppa_dbg_msg('make_the_photo_files called with file='.$file.' image_id='.$image_id.' ext='.$ext);

	$img_size = getimagesize($file, $info);
	if ($img_size) {
		$newimage = wppa_get_photo_path($image_id);
		wppa_dbg_msg('newimage='.$newimage);
		
		if (get_option('wppa_resize_on_upload', 'no') == 'yes') {
			require_once('wppa-class-resize.php');
			// Picture sizes
			$picx = $img_size[0];
			$picy = $img_size[1];
			// Reference suzes
			if ( $wppa_opt['wppa_resize_to'] == '0') {	// from fullsize
				$refx = $wppa_opt['wppa_fullsize'];
				$refy = $wppa_opt['wppa_maxheight'];
			}
			else {										// from selection
				$screen = explode('x', $wppa_opt['wppa_resize_to']);
				$refx = $screen[0];
				$refy = $screen[1];
			}
			// Too landscape?
			if ( $picx/$picy > $refx/$refy ) {					// focus on width
				$dir = 'W';
				$siz = $refx;
				$s = $img_size[0];
			}
			else {												// focus on height
				$dir = 'H';
				$siz = $refy;
				$s = $img_size[1];
			}

			if ($s > $siz) {	
				$objResize = new wppa_ImageResize($file, $newimage, $dir, $siz);
				$objResize->destroyImage($objResize->resOriginalImage);
				$objResize->destroyImage($objResize->resResizedImage);
			}
			else {
				copy($file, $newimage);
			}
		}
		else {
			copy($file, $newimage);
		}
		
		// File successfully created ?
		if ( is_file ($newimage) ) {	
			// Create thumbnail...
			$thumbsize = wppa_get_minisize();
			wppa_create_thumbnail($newimage, $thumbsize, '' );
			// and add watermark (optionally) to fullsize image only
			wppa_add_watermark($newimage);
		} 
		else {
			if (is_admin()) wppa_error_message(__('ERROR: Resized or copied image could not be created.', 'wppa'));
			else wppa_err_alert(__('ERROR: Resized or copied image could not be created.', 'wppa_theme'));
			return false;
		}
		
		// Process the iptc data
		wppa_import_iptc($image_id, $info);
		
		// Process the exif data
		wppa_import_exif($image_id, $file);
		
		// GPS
		wppa_get_coordinates($file, $image_id);

		// Set ( update ) exif date-time if available
		$exdt = wppa_get_exif_datetime($file);
		if ( $exdt ) {
			$wpdb->query($wpdb->prepare( "UPDATE `".WPPA_PHOTOS."` SET `exifdtm` = %s WHERE `id` = %s", $exdt, $image_id ) );
		}
		
		// Show progression
		if ( is_admin() && ! $wppa['ajax'] ) echo('.');
		
		// Update CDN
		switch ( wppa_cdn() ) {
			case 'cloudinary':
				wppa_upload_to_cloudinary( $image_id );
				break;
		}
		
		// Clear (super)cache
		wppa_clear_cache();
		return true;
	}
	else {
		if (is_admin()) wppa_error_message(sprintf(__('ERROR: File %s is not a valid picture file.', 'wppa'), $file));
		else wppa_err_alert(sprintf(__('ERROR: File %s is not a valid picture file.', 'wppa_theme'), $file));
		return false;
	}
}

// Get the max size, rounded up to a multiple of 25 px, of all the possible small images 
// in order to create the thumbnail file big enough but not too big.
function wppa_get_minisize() {
global $wppa_opt;

	$result = '100';

	$things = array(	'wppa_thumbsize', 
						'wppa_thumbsize_alt', 
						'wppa_topten_size', 
						'wppa_comten_size', 
						'wppa_thumbnail_widget_size', 
						'wppa_lasten_size', 
						'wppa_album_widget_size', 
						'wppa_featen_size', 
						'wppa_popupsize',
						'wppa_smallsize'
						);
	foreach ( $things as $thing) {
		$tmp = get_option($thing, 'nil');
		if ( is_numeric($tmp) && $tmp > $result ) $result = $tmp;
	}

	$temp = get_option('wppa_smallsize', 'nil');
	if ( $wppa_opt['wppa_coversize_is_height'] ) {
		$tmp = round($tmp * 4 / 3);		// assume aspectratio 4:3
	}
	if ( is_numeric($tmp) && $tmp > $result ) $result = $tmp;
	
	$result = ceil($result / 25) * 25;
	return $result;
}

// Create thubnail from a given fullsize image path and max size
function wppa_create_thumbnail( $file, $max_side, $effect = '') {
global $wppa_opt;
	
	// See if we are called with the right args
	if ( ! file_exists($file) ) return false;		// No file, fail
	$img_attr = getimagesize( $file );
	if ( ! $img_attr ) return false;				// Not an image, fail
	
	// Retrieve additional required info
	$asp_attr = explode(':', $wppa_opt['wppa_thumb_aspect']);
//	$thumbpath = str_replace( basename( $file ), 'thumbs/' . basename( $file ), $file );
	$thumbpath = str_replace(WPPA_UPLOAD_PATH, WPPA_UPLOAD_PATH.'/thumbs', $file);
	// Source size
	$src_size_w = $img_attr[0];
	$src_size_h = $img_attr[1];
	// Mime type and thumb type
	$mime = $img_attr[2]; 
	$type = $asp_attr[2];
	// Source native aspect
	$src_asp = $src_size_h / $src_size_w;
	// Required aspect
	if ($type == 'none') {
		$dst_asp = $src_asp;
	}
	else {
		$dst_asp = $asp_attr[0] / $asp_attr[1];
	}
	
	// Create the source image
	switch ($mime) {	// mime type
		case 1: // gif
			$temp = @ imagecreatefromgif($file);
			if ( $temp ) {
				$src = imagecreatetruecolor($src_size_w, $src_size_h);
				imagecopy($src, $temp, 0, 0, 0, 0, $src_size_w, $src_size_h);
				imagedestroy($temp);
			}
			else $src = false;
			break;
		case 2:	// jpeg
			$src = @ imagecreatefromjpeg($file);
			break;
		case 3:	// png
			$src = @ imagecreatefrompng($file);
			break;
	}
	if ( ! $src ) {
		wppa_log('Error', 'Image file '.$file.' is corrupt while creating thmbnail');
		return true;
	}
	
	// Compute the destination image size
	if ( $dst_asp < 1.0 ) {	// Landscape
		$dst_size_w = $max_side;
		$dst_size_h = round($max_side * $dst_asp);
	}
	else {					// Portrait
		$dst_size_w = round($max_side / $dst_asp);
		$dst_size_h = $max_side;
	}
	
	// Create the (empty) destination image
	//echo 'dst_asp='.$dst_asp.' src_asp='.$src_asp;
	//echo ' size_w='.$dst_size_w.' size_h='.$dst_size_h;
	$dst = imagecreatetruecolor($dst_size_w, $dst_size_h);
	if ( $mime == 3 ) {	// Png, save transparancy
		imagealphablending($dst, false);
		imagesavealpha($dst, true);
	}

	// Switch on what we have to do
	switch ($type) {
		case 'none':	// Use aspect from fullsize image
			$src_x = 0;
			$src_y = 0;
			$src_w = $src_size_w;
			$src_h = $src_size_h;
			$dst_x = 0;
			$dst_y = 0;
			$dst_w = $dst_size_w;
			$dst_h = $dst_size_h;
			break;
		case 'clip':	// Clip image to given aspect ratio
			if ( $src_asp < $dst_asp ) {	// Source image more landscape than destination
				$dst_x = 0;
				$dst_y = 0;
				$dst_w = $dst_size_w;
				$dst_h = $dst_size_h;
				$src_x = round(($src_size_w - $src_size_h / $dst_asp) / 2);
				$src_y = 0;
				$src_w = round($src_size_h / $dst_asp);
				$src_h = $src_size_h;
			}
			else {
				$dst_x = 0;
				$dst_y = 0;
				$dst_w = $dst_size_w;
				$dst_h = $dst_size_h;
				$src_x = 0;
				$src_y = round(($src_size_h - $src_size_w * $dst_asp) / 2);
				$src_w = $src_size_w;
				$src_h = round($src_size_w * $dst_asp);
			}
			break;
		case 'padd':	// Padd image to given aspect ratio
			if ( $src_asp < $dst_asp ) {	// Source image more landscape than destination
				$dst_x = 0;
				$dst_y = round(($dst_size_h - $dst_size_w * $src_asp) / 2);
				$dst_w = $dst_size_w;
				$dst_h = round($dst_size_w * $src_asp);
				$src_x = 0;
				$src_y = 0;
				$src_w = $src_size_w;
				$src_h = $src_size_h;
			}
			else {
				$dst_x = round(($dst_size_w - $dst_size_h / $src_asp) / 2);
				$dst_y = 0;
				$dst_w = round($dst_size_h / $src_asp);
				$dst_h = $dst_size_h;
				$src_x = 0;
				$src_y = 0;
				$src_w = $src_size_w;
				$src_h = $src_size_h;
			}
			break;
		default:		// Not implemented
			return false;
	}
	
	// Do the copy
	//echo ' dst_x='.$dst_x.' dst_y='.$dst_y.' src_x='.$src_x.' src_y='.$src_y.' dst_w='.$dst_w.' dst_h='.$dst_h.' src_w='.$src_w.' src_h='.$src_h.'<br />';
	imagecopyresampled($dst, $src, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	
	// Save the thumb
	switch ($mime) {	// mime type
		case 1:
			imagegif($dst, $thumbpath);
			break;
		case 2:
			imagejpeg($dst, $thumbpath, 100);
			break;
		case 3:
			imagepng($dst, $thumbpath, 6);
			break;
	}
	
	// Cleanup
	imagedestroy($src);
	imagedestroy($dst);
	return true;
}

function wppa_test_for_search() {
global $wppa;

	if ( isset($_REQUEST['wppa-searchstring']) ) {	// wppa+ search
		$str = $_REQUEST['wppa-searchstring'];
	}
	elseif ( isset($_REQUEST['s']) ) {				// wp search
		$str = $_REQUEST['s'];
	}
	else $str = '';
	
	$str = strip_tags($str);						// Sanitize
	
	if ( is_array($wppa) ) {
		$wppa['searchstring'] = $str;
		if ( $wppa['searchstring'] && $wppa['occur'] == '1' && ! $wppa['in_widget'] ) $wppa['src'] = true;
		else $wppa['src'] = false;
		return $str;
	}
	else {
		return $str;
	}
}

function wppa_get_water_file_and_pos() {
global $wppa_opt;

	$result['file'] = $wppa_opt['wppa_watermark_file'];	// default
	$result['pos'] = $wppa_opt['wppa_watermark_pos'];	// default

	$user = wppa_get_user();
	
	if ( get_option('wppa_watermark_user') == 'yes' || current_user_can('wppa_settings') ) {									// user overrule?
		if ( isset($_POST['wppa-watermark-file'] ) ) {
			$result['file'] = $_POST['wppa-watermark-file'];
			update_option('wppa_watermark_file_' . $user, $_POST['wppa-watermark-file']);
		}
		elseif ( get_option('wppa_watermark_file_' . $user, 'nil') != 'nil' ) {
			$result['file'] = get_option('wppa_watermark_file_' . $user);
		}
		if ( isset($_POST['wppa-watermark-pos'] ) ) {
			$result['pos'] = $_POST['wppa-watermark-pos'];
			update_option('wppa_watermark_pos_' . $user, $_POST['wppa-watermark-pos']);
		}
		elseif ( get_option('wppa_watermark_pos_' . $user, 'nil') != 'nil' ) {
			$result['pos'] = get_option('wppa_watermark_pos_' . $user);
		}
	}
	return $result;
}

	
function wppa_add_watermark($file) {
global $wppa_opt;

	// Init
	if ( get_option('wppa_watermark_on') != 'yes' ) return false;	// Watermarks off
	
	// Find the watermark file and location
	$temp = wppa_get_water_file_and_pos();
	$waterfile = WPPA_UPLOAD_PATH . '/watermarks/' . $temp['file'];
	$waterpos = $temp['pos'];										// default
	
	if ( basename($waterfile) == '--- none ---' ) return false;	// No watermark this time
	// Open the watermark file
	$watersize = @getimagesize($waterfile);
	if ( !is_array($watersize) ) return false;	// Not a valid picture file
	$waterimage = imagecreatefrompng($waterfile);
	if ( empty( $waterimage ) or ( !$waterimage ) ) {
		wppa_dbg_msg('Watermark file '.$waterfile.' not found or corrupt');
		return false;			// No image
	}
	imagealphablending($waterimage, false);
	imagesavealpha($waterimage, true);

		
	// Open the photo file
	$photosize = getimagesize($file);
	if ( !is_array($photosize) ) {
		return false;	// Not a valid photo
	}
	switch ($photosize[2]) {
		case 1: $tempimage = imagecreatefromgif($file);
			$photoimage = imagecreatetruecolor($photosize[0], $photosize[1]);
			imagecopy($photoimage, $tempimage, 0, 0, 0, 0, $photosize[0], $photosize[1]);
			break;
		case 2: $photoimage = imagecreatefromjpeg($file);
			break;
		case 3: $photoimage = imagecreatefrompng($file);
			break;
	}
	if ( empty( $photoimage ) or ( !$photoimage ) ) return false; 			// No image

	$ps_x = $photosize[0];
	$ps_y = $photosize[1];
	$ws_x = $watersize[0];
	$ws_y = $watersize[1];
	$src_x = 0;
	$src_y = 0;
	if ( $ws_x > $ps_x ) {
		$src_x = ($ws_x - $ps_x) / 2;
		$ws_x = $ps_x;
	}		
	if ( $ws_y > $ps_y ) {
		$src_y = ($ws_y - $ps_y) / 2;
		$ws_y = $ps_y;
	}
	
	$loy = substr( $waterpos, 0, 3);
	switch($loy) {
		case 'top': $dest_y = 0;
			break;
		case 'cen': $dest_y = ( $ps_y - $ws_y ) / 2;
			break;
		case 'bot': $dest_y = $ps_y - $ws_y;
			break;
		default: $dest_y = 0; 	// should never get here
	}
	$lox = substr( $waterpos, 3);
	switch($lox) {
		case 'lft': $dest_x = 0;
			break;
		case 'cen': $dest_x = ( $ps_x - $ws_x ) / 2;
			break;
		case 'rht': $dest_x = $ps_x - $ws_x;
			break;
		default: $dest_x = 0; 	// should never get here
	}

	wppa_imagecopymerge_alpha( $photoimage , $waterimage , $dest_x, $dest_y, $src_x, $src_y, $ws_x, $ws_y, intval($wppa_opt['wppa_watermark_opacity']) );

	// Save the result
	switch ($photosize[2]) {
		case 1: imagegif($photoimage, $file);
			break;
		case 2: imagejpeg($photoimage, $file, $wppa_opt['wppa_jpeg_quality']);
			break;
		case 3: imagepng($photoimage, $file, 7);
			break;
	}

	// Cleanup
	imagedestroy($photoimage);
	imagedestroy($waterimage);

	return true;
}


/**
 * PNG ALPHA CHANNEL SUPPORT for imagecopymerge();
 * This is a function like imagecopymerge but it handle alpha channel well!!!
 **/

// A fix to get a function like imagecopymerge WITH ALPHA SUPPORT
// Main script by aiden dot mail at freemail dot hu
// Transformed to imagecopymerge_alpha() by rodrigo dot polo at gmail dot com
function wppa_imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
    if(!isset($pct)){
        return false;
    }
    $pct /= 100;
    // Get image width and height
    $w = imagesx( $src_im );
    $h = imagesy( $src_im );
    // Turn alpha blending off
    imagealphablending( $src_im, false );
    // Find the most opaque pixel in the image (the one with the smallest alpha value)
    $minalpha = 127;
    for( $x = 0; $x < $w; $x++ )
    for( $y = 0; $y < $h; $y++ ){
        $alpha = ( imagecolorat( $src_im, $x, $y ) >> 24 ) & 0xFF;
        if( $alpha < $minalpha ){
            $minalpha = $alpha;
        }
    }
    //loop through image pixels and modify alpha for each
    for( $x = 0; $x < $w; $x++ ){
        for( $y = 0; $y < $h; $y++ ){
            //get current alpha value (represents the TANSPARENCY!)
            $colorxy = imagecolorat( $src_im, $x, $y );
            $alpha = ( $colorxy >> 24 ) & 0xFF;
            //calculate new alpha
            if( $minalpha !== 127 ){
                $alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha );
            } else {
                $alpha += 127 * $pct;
            }
            //get the color index with new alpha
            $alphacolorxy = imagecolorallocatealpha( $src_im, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
            //set pixel with the new color + opacity
            if( !imagesetpixel( $src_im, $x, $y, $alphacolorxy ) ){
                return false;
            }
        }
    }
    // The image copy
    imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
}

function wppa_watermark_file_select($default = false) {
global $wppa_opt;

	// Init
	$result = '';
	$user = wppa_get_user();
	
	// See what's in there
	$paths = WPPA_UPLOAD_PATH . '/watermarks/*.png';
	$files = glob($paths);
	
	// Find current selection
	$select = $wppa_opt['wppa_watermark_file'];	// default
	if ( !$default && ( get_option('wppa_watermark_user') == 'yes' || current_user_can('wppa_settings') ) && get_option('wppa_watermark_file_' . $user, 'nil') !== 'nil' ) {
		$select = get_option('wppa_watermark_file_' . $user);
	}
	
	// Produce the html
	$result .= '<option value="--- none ---">'.__('--- none ---', 'wppa').'</option>';
	if ( $files ) foreach ( $files as $file ) {
		$sel = $select == basename($file) ? 'selected="selected"' : '';
		$result .= '<option value="'.basename($file).'" '.$sel.'>'.basename($file).'</option>';
	}
	
	return $result;
}

function wppa_watermark_pos_select($default = false) {
global $wppa_opt;

	// Init
	$user = wppa_get_user();
	$result = '';
	$opt = array(	__('top - left', 'wppa'), __('top - center', 'wppa'), __('top - right', 'wppa'), 
					__('center - left', 'wppa'), __('center - center', 'wppa'), __('center - right', 'wppa'), 
					__('bottom - left', 'wppa'), __('bottom - center', 'wppa'), __('bottom - right', 'wppa'), );
	$val = array(	'toplft', 'topcen', 'toprht',
					'cenlft', 'cencen', 'cenrht',
					'botlft', 'botcen', 'botrht', );
	$idx = 0;

	// Find current selection
	$select = $wppa_opt['wppa_watermark_pos'];	// default
	if ( !$default && ( get_option('wppa_watermark_user') == 'yes' || current_user_can('wppa_settings') ) && get_option('wppa_watermark_pos_' . $user, 'nil') !== 'nil' ) {
		$select = get_option('wppa_watermark_pos_' . $user);
	}
	
	// Produce the html
	while ($idx < 9) {
		$sel = $select == $val[$idx] ? 'selected="selected"' : '';
		$result .= '<option value="'.$val[$idx].'" '.$sel.'>'.$opt[$idx].'</option>';
		$idx++;
	}
	
	return $result;
}

function wppa_table_exists($xtable) {
global $wpdb;

	$tables = $wpdb->get_results( "SHOW TABLES FROM `".DB_NAME."`", ARRAY_A);
	wppa_dbg_q('Q213');
	// Some sqls do not show tables, benefit of the doubt: assume table exists
	if ( empty($tables) ) return true;
	
	// Normal check
	foreach ($tables as $table) {
		if ( is_array($table) )	foreach ( $table as $item ) {
			if ( strcasecmp($item, $xtable) == 0 ) return true;
		}
	}
	return false;
}

// Process the iptc data
function wppa_import_iptc($id, $info, $nodelete = false) {
global $wpdb;
static $labels;
global $wppa_opt;

	$doit = false;
	// Do we need this?
	if ( wppa_switch('wppa_save_iptc') ) $doit = true;
	if ( substr( $wppa_opt['wppa_newphoto_name_method'], 0, 2) == '2#' ) $doit = true;
	if ( ! $doit ) return;
	
	wppa_dbg_msg('wppa_import_iptc called for id='.$id);
	wppa_dbg_msg('array is'.( is_array($info) ? ' ' : ' NOT ' ).'available');
	wppa_dbg_msg('APP13 is '.( isset($info['APP13']) ? 'set' : 'NOT set'));
	
	// Is iptc data present?
	if ( !isset($info['APP13']) ) return false;	// No iptc data avail
//var_dump($info);
	// Parse
	$iptc = iptcparse($info['APP13']);
	if ( ! is_array($iptc) ) return false;		// No data avail 
	
	// There is iptc data for this image.
	// First delete any existing ipts data for this image
	if ( ! $nodelete ) {
		$wpdb->query($wpdb->prepare("DELETE FROM `".WPPA_IPTC."` WHERE `photo` = %s", $id));
		wppa_dbg_q('Q214');
	}
	
	// Find defined labels
	if ( ! is_array($labels) ) {
		$result = $wpdb->get_results( "SELECT `tag` FROM `".WPPA_IPTC."` WHERE `photo` = '0' ORDER BY `tag`", ARRAY_N);
		wppa_dbg_q('Q215');
		if ( ! is_array($result) ) $result = array();
		$labels = array();
		foreach ($result as $res) {
			$labels[] = $res['0'];
		}
	}
	
	foreach (array_keys($iptc) as $s) {
		if ( is_array($iptc[$s]) ) {
			$c = count ($iptc[$s]);
			for ($i=0; $i <$c; $i++) {
				// Process item
				wppa_dbg_msg('IPTC '.$s.' = '.$iptc[$s][$i]);
				// Check labels first
				if ( ! in_array( $s, $labels ) ) {
					$labels[] = $s;	// Add to labels
					// Add to db
//					$key 	= wppa_nextkey(WPPA_IPTC);
					$photo 	= '0';
					$tag 	= $s;
					$desc 	= $s.':';
						if ( $s == '2#005' ) $desc = 'Graphic name:';
						if ( $s == '2#010' ) $desc = 'Urgency:';
						if ( $s == '2#015' ) $desc = 'Category:'; 
						if ( $s == '2#020' ) $desc = 'Supp categories:';
						if ( $s == '2#040' ) $desc = 'Spec instr:'; 
						if ( $s == '2#055' ) $desc = 'Creation date:';
						if ( $s == '2#080' ) $desc = 'Photographer:';
						if ( $s == '2#085' ) $desc = 'Credit byline title:';
						if ( $s == '2#090' ) $desc = 'City:';
						if ( $s == '2#095' ) $desc = 'State:';	
						if ( $s == '2#101' ) $desc = 'Country:';
						if ( $s == '2#103' ) $desc = 'Otr:';
						if ( $s == '2#105' ) $desc = 'Headline:';
						if ( $s == '2#110' ) $desc = 'Source:';
						if ( $s == '2#115' ) $desc = 'Photo source:'; 	
						if ( $s == '2#120' ) $desc = 'Caption:';
					$status = 'display';
						if ( $s == '1#090' ) $status = 'hide';
						if ( $s == '2#000' ) $status = 'hide';
//					$query 	= $wpdb->prepare("INSERT INTO `".WPPA_IPTC."` (`id`, `photo`, `tag`, `description`, `status`) VALUES (%s, %s, %s, %s, %s)", $key, $photo, $tag, $desc, $status); 
//					wppa_dbg_q('Q216');
//					$iret 	= $wpdb->query($query);
					$iret = wppa_create_iptc_entry( array( 'photo' => $photo, 'tag' => $tag, 'description' => $desc, 'status' => $status ) );
					if ( ! $iret ) wppa_dbg_msg('Error: '.$query);
				}
				// Now add poto specific data item
//				$key 	= wppa_nextkey(WPPA_IPTC);
				$photo 	= $id;
				$tag 	= $s;
				$desc 	= $iptc[$s][$i];
				$status = 'default';
//				$query  = $wpdb->prepare("INSERT INTO `".WPPA_IPTC."` (`id`, `photo`, `tag`, `description`, `status`) VALUES (%s, %s, %s, %s, %s)", $key, $photo, $tag, $desc, $status); 
//				wppa_dbg_q('Q217');
//				$iret 	= $wpdb->query($query);
				$iret = wppa_create_iptc_entry( array( 'photo' => $photo, 'tag' => $tag, 'description' => $desc, 'status' => $status ) );
				if ( ! $iret ) wppa_dbg_msg('Error: '.$query);
			}
		}
	}
}

function wppa_get_exif_datetime($file) {
	// Check filetype
	if ( ! function_exists('exif_imagetype') ) return false;	// Exif functions absent
	$image_type = exif_imagetype($file);
	if ( $image_type != IMAGETYPE_JPEG ) return false;			// Not supported image type
	// Get exif data
	if ( ! function_exists('exif_read_data') ) return false;	// Not supported by the server
	$exif = @ exif_read_data($file, 'EXIF');
	if ( ! is_array($exif) ) return false;						// No data present
	// Data present
	if ( isset( $exif['DateTimeOriginal'] ) ) return $exif['DateTimeOriginal'];
	return false;
}

function wppa_import_exif($id, $file, $nodelete = false) {
global $wpdb;
static $labels;
static $names;
global $wppa_opt;
global $wppa;

	// Do we need this?
	if ( ! wppa_switch('wppa_save_exif') ) return;
wppa_dbg_msg('Sav EXIF is on');
	// Check filetype
	if ( ! function_exists('exif_imagetype') ) return false;
wppa_dbg_msg('Fn exif_imagetype() exists');
	$image_type = exif_imagetype($file);
	if ( $image_type != IMAGETYPE_JPEG ) return false;	// Not supported image type
wppa_dbg_msg('Img type is jpeg');
	// Get exif data
	if ( ! function_exists('exif_read_data') ) return false;	// Not supported by the server
wppa_dbg_msg('Fn exif_read_data exists');
	$exif = @ exif_read_data($file, 'EXIF');
	if ( ! is_array($exif) ) return false;			// No data present
wppa_dbg_msg('Exif data present');
//var_dump($exif);
	// There is exif data for this image.
	// First delete any existing exif data for this image
	if ( ! $nodelete ) {
		$wpdb->query($wpdb->prepare("DELETE FROM `".WPPA_EXIF."` WHERE `photo` = %s", $id));
		wppa_dbg_q('Q218');
	}
	
	// Find defined labels
	if ( ! is_array($labels) ) {
		$result = $wpdb->get_results( "SELECT * FROM `".WPPA_EXIF."` WHERE `photo` = '0' ORDER BY `tag`", ARRAY_A );
		wppa_dbg_q('Q219');
		if ( ! is_array($result) ) $result = array();
		$labels = array();
		$names  = array();
		foreach ($result as $res) {
			$labels[] = $res['tag'];
			$names[]  = $res['description'];
		}
	}
	
	foreach (array_keys($exif) as $s) {
		// Process item
		wppa_dbg_msg('EXIF '.$s.' = '.$exif[$s]);
		
		// Check labels first
		$tag = '';
		if ( in_array( $s, $names ) ) {
			$i = 0;
			while ( $i < count($labels) ) {
				if ( $names[$i] == $s ) $tag = $labels[$i];
			}
		}
		if ( $tag == '' ) $tag = wppa_exif_tag($s);
		if ( $tag == '' ) continue;
		if ( ! in_array( $tag, $labels ) ) {
			$labels[] = $tag;	// Add to labels
			$names[]  = $s.':';
			// Add to db
//			$key 	= wppa_nextkey(WPPA_EXIF);
			$photo 	= '0';
			$desc 	= $s.':';
			$status = 'display';
//			$query 	= $wpdb->prepare("INSERT INTO `".WPPA_EXIF."` (`id`, `photo`, `tag`, `description`, `status`) VALUES (%s, %s, %s, %s, %s)", $key, $photo, $tag, $desc, $status); 
//			wppa_dbg_q('Q220');
//			$iret 	= $wpdb->query($query);
//wppa_dbg_msg('Insert new label');
			$iret = wppa_create_exif_entry( array( 'photo' => $photo, 'tag' => $tag, 'description' => $desc, 'status' => $status ) );
			if ( ! $iret ) wppa_dbg_msg('Error: '.$query, false, 'red');
		}
		// Now add poto specific data item
		// If its an array...
		if ( is_array( $exif[$s] ) ) { // continue;
			$c = count ($exif[$s]);
			$max = $wppa_opt['wppa_exif_max_array_size'];
			if ( $max != '0' && $c > $max ) {
				wppa_dbg_msg('Exif tag '.$tag. ': array truncated form '.$c.' to '.$max.' elements for photo nr '.$id.'.', 'red');
				$c = $max;
			}
			for ($i=0; $i <$c; $i++) {
//				$key 	= wppa_nextkey(WPPA_EXIF);
				$photo 	= $id;
				$desc 	= $exif[$s][$i];
				$status = 'default';
//				$query  = $wpdb->prepare("INSERT INTO `".WPPA_EXIF."` (`id`, `photo`, `tag`, `description`, `status`) VALUES (%s, %s, %s, %s, %s)", $key, $photo, $tag, $desc, $status); 
//				wppa_dbg_q('Q221');
//				$iret 	= $wpdb->query($query);
//wppa_dbg_msg('Insert 1. Query = '.$query);
				$iret = wppa_create_exif_entry( array( 'photo' => $photo, 'tag' => $tag, 'description' => $desc, 'status' => $status ) );
				if ( ! $iret ) wppa_dbg_msg('Error: '.$query, false, 'red');
			
			}
		}
		// Its not an array
		else {
//			$key 	= wppa_nextkey(WPPA_EXIF);
			$photo 	= $id;
			$desc 	= $exif[$s];
			$status = 'default';
//			$query  = $wpdb->prepare("INSERT INTO `".WPPA_EXIF."` (`id`, `photo`, `tag`, `description`, `status`) VALUES (%s, %s, %s, %s, %s)", $key, $photo, $tag, $desc, $status); 
//			wppa_dbg_q('Q222');
//			$iret 	= $wpdb->query($query);
//wppa_dbg_msg('Insert 2. Query = '.$query);
			$iret = wppa_create_exif_entry( array( 'photo' => $photo, 'tag' => $tag, 'description' => $desc, 'status' => $status ) );
			if ( ! $iret ) wppa_dbg_msg('Error: '.$query, false, 'red');
		}
	}
}

// Inverse of exif_tagname();
function wppa_exif_tag($tagname) {
global $wppa_inv_exiftags;

	// Setup inverted matrix
	if ( ! is_array($wppa_inv_exiftags) ) {
		$key = 0;
		while ( $key < 65536 ) {
			$tag = exif_tagname($key);
			if ( $tag != '' ) {
				$wppa_inv_exiftags[$tag] = $key;
			}
			$key++;
			if ( ! $key ) break;	// 16 bit server wrap around (do they still exist??? )
		}
	}
	// Search
	if ( isset($wppa_inv_exiftags[$tagname]) ) return sprintf('E#%04X',$wppa_inv_exiftags[$tagname]);
	elseif ( strlen($tagname) == 19 ) {
		if ( substr($tagname, 0, 12) == 'UndefinedTag') return 'E#'.substr($tagname, -4);
	}
	else return '';
}

// This function attemps to recover iptc and exif data from existing files in the wppa dir.
function wppa_recuperate_iptc_exif() {
global $wpdb;

	if ( wppa_switch('wppa_save_iptc') || wppa_switch('wppa_save_exif') ) {
		$iptc_count = '0';
		$exif_count = '0';
		$tot_count = '0';
		$start = get_option('wppa_last_recup', '0');
		$photos = $wpdb->get_results("SELECT `id` FROM `".WPPA_PHOTOS."` WHERE `id` > ".$start." ORDER BY `id`", ARRAY_A);
		if ( $photos ) {
			wppa_update_message(sprintf('Recuperation started at photo id = %s.', $start+'1', 'wppa'));
			foreach ( $photos as $photo ) {
				$file = wppa_get_photo_path($photo['id']);
				if ( is_file ($file) ) {					// Not a dir
					$attr = getimagesize($file, $info);
					if ( is_array($attr) ) {				// Is a picturefile
						if ( $attr[2] == IMAGETYPE_JPEG ) {	// Is a jpg
							$id = $photo['id'];
							// Now we have $id, $file and $info
							if ( wppa_switch('wppa_save_iptc') ) {	// Save iptc
								if ( isset($info["APP13"]) ) {	// There is IPTC data
									$is_iptc = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_IPTC."` WHERE `photo` = %s", $id));
									wppa_dbg_q('Q223');
									if ( ! $is_iptc ) { 	// No IPTC yet and there is: Recuperate
										wppa_import_iptc($id, $info, 'nodelete');
										$iptc_count++;
									}						
								}
							}
							if ( wppa_switch('wppa_save_exif') ) {	// Save exif
								$image_type = exif_imagetype($file);
								if ( $image_type == IMAGETYPE_JPEG ) {	// EXIF supported by server
									$is_exif = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_EXIF."` WHERE `photo`=%s", $id));
									wppa_dbg_q('Q224');
									if ( ! $is_exif ) { 				// No EXIF yet
										$exif = @ exif_read_data($file, 'EXIF');//@
										if ( is_array($exif) )	{ 		// There is exif data present
											wppa_import_exif($id, $file, 'nodelete');
											$exif_count++;
										}
									}						
								}	
							}
							$tot_count++;							
						}					
					}
					update_option('wppa_last_recup', $id);
				}
				if ( wppa_is_time_up() ) {
					wppa_error_message(__(sprintf('Time out after processing %s items. %s photos with IPTC data and %s photos with EXIF data.', $tot_count, $iptc_count, $exif_count), 'wppa'));
//					update_option('wppa_last_recup', $id);
					return false;
				}
			}
		}
		wppa_ok_message(__(sprintf('Done! %s items processed. %s photos with IPTC data and %s photos with EXIF data.', $tot_count, $iptc_count, $exif_count), 'wppa'));
		update_option('wppa_last_recup', '-2');
		return true;
	}
	else {
		wppa_error_message(__('This is useless. Both saving IPTC and saving EXIF are switched off. See Table IX-H5 and 6', 'wppa'));
		update_option('wppa_last_recup', '-2');
		return true;
	}
}

function wppa_clear_cache($force = false) {
global $cache_path;

	// If wp-super-cache is on board, clear cache
	if ( function_exists('prune_super_cache') ) {
		prune_super_cache( $cache_path . 'supercache/', true );
		prune_super_cache( $cache_path, true );
	}
	// W3 Total cache
	if ( function_exists('w3tc_pgcache_flush') ) {
		w3tc_pgcache_flush();
	}
	
	if ( $force ) { 	// At a setup or update operation
		if ( is_dir(ABSPATH.'wp-content/cache/') ) {
			// W3 has no flush all api call, we do it by hand
			wppa_tree_empty(ABSPATH.'wp-content/cache');
		}
	}
}

// Removes the content of $dir, ignore errors
function wppa_tree_empty($dir) {
	$files = glob($dir.'/*');
	if ( is_array($files) ) foreach ( $files as $file ) {
		$name = basename($file);
		if ( $name == '.' || $name == '..' ) {}
		elseif ( is_dir($file) ) {
			wppa_tree_empty($file);
			@ unlink($file);
		}
		else @ unlink($file);
	}
}

function wppa_err_alert($msg) {
global $wppa;

	$fullmsg = '<script id="wppaer" type="text/javascript" >alert(\''.$msg.'\');jQuery("#wppaer").html("");</script>';
//	if ( is_admin() ) 
	echo $fullmsg;
//	else $wppa['out'] .= $fullmsg;	
}

// Return the allowed number to upload in an album. -1 = unlimited
function wppa_allow_uploads($album = '0') {
global $wpdb;

	if ( ! $album ) return '-1';//'0';
//	if ( $album == 'any' ) return '-1';
	
	$limits = $wpdb->get_var($wpdb->prepare("SELECT `upload_limit` FROM `".WPPA_ALBUMS."` WHERE `id` = %s", $album));
	wppa_dbg_q('Q225');
	$temp = explode('/', $limits);
	$limit_max  = isset($temp[0]) ? $temp[0] : '0';
	$limit_time = isset($temp[1]) ? $temp[1] : '0';

	if ( ! $limit_max ) return '-1';		// Unlimited max
	
	if ( ! $limit_time ) {					// For ever
		$curcount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_PHOTOS."` WHERE `album` = %s", $album));
		wppa_dbg_q('Q226');
	}
	else {									// Time criterium in place
		$timnow = time();
		$timthen = $timnow - $limit_time;
		$curcount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_PHOTOS."` WHERE `album` = %s AND `timestamp` > %s", $album, $timthen));
		wppa_dbg_q('Q227');
	}
	
	if ( $curcount >= $limit_max ) $result = '0';	// No more allowed
	else $result = $limit_max - $curcount;

	return $result;
}

// Return the allowed number of uploads for a certain user. -1 = unlimited
function wppa_allow_user_uploads() {
global $wpdb;
global $wppa_opt;

	// Get the limits
	$limits = wppa_get_user_upload_limits();
	
	$temp = explode('/', $limits);
	$limit_max  = isset($temp[0]) ? $temp[0] : '0';
	$limit_time = isset($temp[1]) ? $temp[1] : '0';
	
	if ( ! $limit_max ) return '-1';		// Unlimited max
	
	$user = wppa_get_user('login');
	if ( ! $limit_time ) {					// For ever
		$curcount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_PHOTOS."` WHERE `owner` = %s", $user));
		wppa_dbg_q('Q326');
	}
	else {									// Time criterium in place
		$timnow = time();
		$timthen = $timnow - $limit_time;
		$curcount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `".WPPA_PHOTOS."` WHERE `owner` = %s AND `timestamp` > %s", $user, $timthen));
		wppa_dbg_q('Q327');
	}
	
	if ( $curcount >= $limit_max ) $result = '0';	// No more allowed
	else $result = $limit_max - $curcount;

	return $result;	
}
function wppa_get_user_upload_limits() {
global $wp_roles;

	$limits = '';
	if ( is_user_logged_in() ) {
		if ( current_user_can('wppa_upload') ) $limits = '0/0';		// Unlimited if you have wppa_upload capabilities
		else {
			$roles = $wp_roles->roles;
			$roles['loggedout'] = '';
			unset ($roles['administrator']);
			foreach ( array_keys($roles) as $role ) if ( ! $limits ) {
				if ( current_user_can($role) ) $limits = get_option('wppa_'.$role.'_upload_limit_count', '0').'/'.get_option('wppa_'.$role.'_upload_limit_time', '0');
			}
		}
	}
	else {
		$limits = get_option('wppa_loggedout_upload_limit_count', '0').'/'.get_option('wppa_loggedout_upload_limit_time', '0');
	}
	return $limits;
}

// See if a string is a comma seperated list of numbers, a single num returns false
/* obsolete?
function wppa_is_enum($str) {

	if ( is_numeric($str) ) return false;
	if ( strstr( $str, ',' ) === false ) return false;
	
	$temp = explode(',', $str);
	
	foreach ( $temp as $t ) {
		if ( ! is_numeric($t) ) return false;
	}
	
	return true;
}
*/
function wppa_alfa_id($id = '0') {
	return str_replace( array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0'), array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'), $id );
}

// Thanx to the maker of nextgen, but greatly improved
// Usage: wppa_check_memory_limit() return string telling the max upload size
// @1: if false, return array ( 'maxx', 'maxy', 'maxp' )
// @2: width to test an image,
// @3: height to test an image.
// If both present: return true if fit in memory, false if not.
//
//
function wppa_check_memory_limit($verbose = true, $x = '0', $y = '0') {
global $wppa_opt;
// ini_set('memory_limit', '18M');	// testing
	if ( ! function_exists('memory_get_usage') ) return '';
	if ( is_admin() && $wppa_opt['wppa_memcheck_admin'] == 'no' ) return '';
	if ( ! is_admin() && ! $wppa_opt['wppa_memcheck_frontend'] ) return '';

	// get memory limit
	$memory_limit = 0;
	$memory_limini = wppa_convert_bytes(ini_get('memory_limit'));
	$memory_limcfg = wppa_convert_bytes(get_cfg_var('memory_limit'));
	
	// find the smallest not being zero
	if ( $memory_limini && $memory_limcfg ) $memory_limit = min($memory_limini, $memory_limcfg);
	elseif ( $memory_limini ) $memory_limit = $memory_limini;
	else $memory_limit = $memory_limcfg;

	// No data
	if ( ! $memory_limit ) return '';
	
	// Calculate the free memory 	
	$free_memory = $memory_limit - memory_get_usage(true);

	// Calculate number of pixels largest target resized image 
	if ( get_option('wppa_resize_on_upload') == 'yes' ) {
		$t = get_option('wppa_resize_to');
		if ( $t == '0') {
			$to['0'] = get_option('wppa_fullsize');
			$to['1'] = get_option('wppa_maxheight');
		}
		else {
			$to = explode('x', $t);
		}
		$resizedpixels = $to['0'] * $to['1'];
	}
	else {
		$resizedpixels = wppa_get_minisize() * wppa_get_minisize() * 3 / 4;
	}
	
	// Number of bytes per pixel (found by trial and error)
	//	$factor = '5.60';	//  5.60 for 17M: 386 x 289 (0.1 MP) thumb only
	//	$factor = '5.10';	//  5.10 for 104M: 4900 x 3675 (17.2 MP) thumb only
	$memlimmb = $memory_limit / ( 1024 * 1024 );
	$factor = '6.00' - '0.58' * ( $memlimmb / 104 );

	// Calculate max size
	$maxpixels = ( $free_memory / $factor ) - $resizedpixels;
	
	// If obviously faulty: quit silently
	if ( $maxpixels < 0 ) return '';
	
	// What are we asked for?
	if ( $x && $y ) { 	// Request for check an image
		if ( $x * $y <= $maxpixels ) $result = true;
		else $result = false;
	}
	else {	// Request for tel me what is the limit
		$maxx = sqrt($maxpixels / 12) * 4;
		$maxy = sqrt($maxpixels / 12) * 3;
		if ( $verbose ) {		// Make it a string
			$result = '<br />'.sprintf(  __( 'Based on your server memory limit you should not upload images larger then <strong>%d x %d (%2.1f MP)</strong>', 'wppa' ), $maxx, $maxy, $maxpixels / (1024 * 1024) );
		}
		else {					// Or an array
			$result['maxx'] = $maxx;
			$result['maxy'] = $maxy;
			$result['maxp'] = $maxpixels;
		}
	}
	return $result;
}

/**
 * Convert a shorthand byte value from a PHP configuration directive to an integer value. Negative values return 0.
 * @param    string   $value
 * @return   int
 */
function wppa_convert_bytes( $value ) {
    if ( is_numeric( $value ) ) {
        return max( '0', $value );
    } else {
        $value_length = strlen( $value );
        $qty = substr( $value, 0, $value_length - 1 );
        $unit = strtolower( substr( $value, $value_length - 1 ) );
        switch ( $unit ) {
            case 'k':
                $qty *= 1024;
                break;
            case 'm':
                $qty *= 1048576;
                break;
            case 'g':
                $qty *= 1073741824;
                break;
        }
        return max( '0', $qty );
    }
}

function wppa_dbg_q($id) {
global $wppa;

	if ( ! $wppa['debug'] ) return;	// Nothing to do here
	
	switch ( $id ) {
		case 'init':
			break;
		case 'print':
			if ( ! is_array($wppa['queries']) ) return;	// Did nothing
			$qtot = 0;
			$gtot = 0;
			$keys = array_keys($wppa['queries']);
			sort($keys);
			$line = 'Cumulative query statistics: Q=query, G=cache<br />';
			foreach ($keys as $k) {
				if ( substr($k,0,1) == 'Q' ) {
					$line .= $k.'=>'.$wppa['queries'][$k].', ';
					$qtot += $wppa['queries'][$k];
				}
			}
			$line .= '<br />';
			foreach ($keys as $k) {
				if ( substr($k,0,1) == 'G' ) {
					$line .= $k.'=>'.$wppa['queries'][$k].', ';
					$gtot += $wppa['queries'][$k];
				}
			}
			$line .= '<br />';
			$line .= sprintf('Total queries attempted: %d, Cash hits: %d, equals %4.2f', $qtot+$gtot, $gtot, $gtot*100/($qtot+$gtot)).'%';
			wppa_dbg_msg($line);
//			ob_start();
//			print_r($wppa['queries']);
//			wppa_dbg_msg(ob_get_clean());
			break;
		default:
			if ($wppa['debug']) if (!isset($wppa['queries'][$id])) $wppa['queries'][$id]=1;else $wppa['queries'][$id]++;
			break;
	}
}

// Exactly like php's date(), but corrected for wp's timezone
function wppa_local_date($format, $timestamp = false) {

	$current_offset = get_option('gmt_offset');
	$tzstring = get_option('timezone_string');

	// Remove old Etc mappings.  Fallback to gmt_offset.
	if ( false !== strpos($tzstring,'Etc/GMT') )
		$tzstring = '';

	if ( empty($tzstring) ) { // Create a UTC+- zone if no timezone string exists
		$check_zone_info = false;
		if ( 0 == $current_offset )
			$tzstring = 'UTC';
		elseif ($current_offset < 0)
			$tzstring = 'UTC' . $current_offset;
		else
			$tzstring = 'UTC+' . $current_offset;
	}

	date_default_timezone_set($tzstring);
	$result = date_i18n($format, $timestamp);
	
	return $result;
}

// Get gps data from photofile
function wppa_get_coordinates($picture_path, $photo_id) {
global $wpdb;

	// Exif on board?
	if ( ! function_exists('exif_read_data') ) return false;
	
	// Check filetype
	if ( ! function_exists('exif_imagetype') ) return false;
	$image_type = exif_imagetype($picture_path);
	if ( $image_type != IMAGETYPE_JPEG ) return false;	// Not supported image type
	
	// get exif data
	if ( $exif = @ exif_read_data($picture_path, 0 , false) ) {
	
		// any coordinates available?
		if ( !isset ($exif['GPSLatitude'][0]) ) return false;	// No GPS data
		if ( !isset ($exif['GPSLongitude'][0]) ) return false;	// No GPS data
		
		// north, east, south, west?
		if ( $exif['GPSLatitudeRef'] == "S" ) {
			$gps['latitude_string'] = -1; 
			$gps['latitude_dicrection'] = "S";
		} 
		else {
			$gps['latitude_string'] = 1; 
			$gps['latitude_dicrection'] = "N";
		}
		if ( $exif['GPSLongitudeRef'] == "W" ) {
			$gps['longitude_string'] = -1; 
			$gps['longitude_dicrection'] = "W";
		} 
		else {
			$gps['longitude_string'] = 1; 
			$gps['longitude_dicrection'] = "E";
		}
		// location
		$gps['latitude_hour'] = $exif["GPSLatitude"][0];
		$gps['latitude_minute'] = $exif["GPSLatitude"][1];
		$gps['latitude_second'] = $exif["GPSLatitude"][2];
		$gps['longitude_hour'] = $exif["GPSLongitude"][0];
		$gps['longitude_minute'] = $exif["GPSLongitude"][1];
		$gps['longitude_second'] = $exif["GPSLongitude"][2]; 
				
		// calculating 
		foreach($gps as $key => $value) {
			$pos = strpos($value, '/');
			if ( $pos !== false ) {
				$temp = explode('/',$value); 
				if ( $temp[1] ) $gps[$key] = $temp[0] / $temp[1];
				else $gps[$key] = 0;
			}
		}
		
		$geo['latitude_format'] = $gps['latitude_dicrection']." ".$gps['latitude_hour']."&deg;".$gps['latitude_minute']."&#x27;".round ($gps['latitude_second'], 4).'&#x22;';
		$geo['longitude_format'] = $gps['longitude_dicrection']." ".$gps['longitude_hour']."&deg;".$gps['longitude_minute']."&#x27;".round ($gps['longitude_second'], 4).'&#x22;';
		 
		$geo['latitude'] = $gps['latitude_string'] * ($gps['latitude_hour'] + ($gps['latitude_minute'] / 60) + ($gps['latitude_second'] / 3600));
		$geo['longitude'] = $gps['longitude_string'] * ($gps['longitude_hour'] + ($gps['longitude_minute'] / 60) + ($gps['longitude_second'] / 3600));	
		
	}
	else {	// No exif data
		return false;
	}

	// Process result
//	print_r($geo);	// debug
	$result = implode('/', $geo);
	$wpdb->query($wpdb->prepare("UPDATE `".WPPA_PHOTOS."` SET `location` = %s WHERE `id` = %s", $result, $photo_id));
	return $geo;
}


function wppa_format_geo($lat, $lon) {

	if ( ! $lat && ! $lon ) return '';	// Both zero: clear

	if ( ! $lat ) $lat = '0.0';
	if ( ! $lon ) $lon = '0.0';

	$geo['latitude_format'] = $lat >= '0.0' ? 'N ' : 'S ';
	$d = floor($lat);
	$m = floor(( $lat - $d ) * 60 );
	$s = round(((( $lat - $d ) * 60 - $m ) * 60 ), 4 );
	$geo['latitude_format'] .= $d.'&deg;'.$m.'&#x27;'.$s.'&#x22;';
	
	$geo['longitude_format'] = $lon >= '0.0' ? 'E ' : 'W ';
	$d = floor($lon);
	$m = floor(( $lon - $d ) * 60 );
	$s = round(((( $lon - $d ) * 60 - $m ) * 60 ), 4 );
	$geo['longitude_format'] .= $d.'&deg;'.$m.'&#x27;'.$s.'&#x22;';
	
	$geo['latitude'] = $lat;
	$geo['longitude'] = $lon;
	
	$result = implode('/', $geo);
	return $result;
}


function wppa_album_select_a($args) {
global $wpdb;

	$args = wp_parse_args( $args, array(	'exclude' 			=> '', 
											'selected' 			=> '', 
											'disabled' 			=> '',
											'addpleaseselect' 	=> false,
											'addnone' 			=> false, 
											'addall' 			=> false,
											'addgeneric'		=> false,
											'addblank' 			=> false,
											'addselected'		=> false,
											'addseparate' 		=> false, 
											'addselbox'			=> false,
											'disableancestors' 	=> false,
											'checkaccess' 		=> false,
											'checkupload' 		=> false,
											'addmultiple' 		=> false,
											'addnumbers' 		=> false,
											'path' 				=> false,
											'root' 				=> false,
											'content'			=> false,
											'sort'				=> true
											) );
											
	// Provide default selection if no selected given
	if ( $args['selected'] === '' ) {
        $args['selected'] = wppa_get_last_album();
    }
	// See if selection is valid
	if ( ( $args['selected'] == $args['exclude'] ) || 
		 ( $args['checkupload'] && ! wppa_allow_uploads($args['selected']) ) ||
		 ( $args['disableancestors'] && wppa_is_ancestor($args['exclude'], $args['selected']) )
	   ) {
		$args['selected'] = '0';
	}
												
	$albums = $wpdb->get_results( "SELECT `id`, `name` FROM `".WPPA_ALBUMS."` ".wppa_get_album_order($args['root']), ARRAY_A);	
	
	if ( $albums ) {
		// Filter for root
		if ( $args['root'] ) {
			$root = $args['root'];
			switch ( $root ) {	// case '0': all, will be skipped as it returns false in 'if ( $args['root'] )'
				case '-2':	// Generic only
				foreach ( array_keys( $albums ) as $albidx ) {
					if ( wppa_is_separate($albums[$albidx]['id']) ) unset ( $albums[$albidx] );
				}
				break;
				case '-1':	// Separate only
				foreach ( array_keys( $albums ) as $albidx ) {
					if ( ! wppa_is_separate($albums[$albidx]['id']) ) unset ( $albums[$albidx] );
				}
				break;
				default:
				foreach ( array_keys( $albums ) as $albidx ) {
					if ( ! wppa_is_ancestor($root, $albums[$albidx]['id']) ) unset ( $albums[$albidx] );
				}
				break;
			}
		}
		// Filter for must have content
		if ( $args['content'] ) {
			foreach ( array_keys( $albums ) as $albidx ) {
				if ( wppa_get_photo_count($albums[$albidx]['id']) <= wppa_get_mincount() ) unset ( $albums[$albidx] );
			}
		}
		// Add paths
		if ( $args['path'] ) {
			$albums = wppa_add_paths($albums);
		}
		// Or just translate
		else foreach ( array_keys($albums) as $index ) {
			$albums[$index]['name'] = __(stripslashes($albums[$index]['name']));
		}
		// Sort
		if ( $args['sort'] ) $albums = wppa_array_sort($albums, 'name');
	}
	
	// Output
	$result = '';
	
	$selected = $args['selected'] == '0' ? ' selected="selected"' : '';
	if ( $args['addpleaseselect'] ) $result .= '<option value="0" disabled="disabled" '.$selected.' >' . __('- select an album -', 'wppa') . '</option>';
	
	$selected = $args['selected'] == '0' ? ' selected="selected"' : '';
	if ( $args['addnone'] ) $result .= '<option value="0"'.$selected.' >' . __('--- none ---', 'wppa') . '</option>';
	
	$selected = $args['selected'] == '0' ? ' selected="selected"' : '';
	if ( $args['addall'] ) $result .= '<option value="0"'.$selected.' >' . __('--- all ---', 'wppa') . '</option>';

	$selected = $args['selected'] == '-2' ? ' selected="selected"' : '';
	if ( $args['addall'] ) $result .= '<option value="-2"'.$selected.' >' . __('--- generic ---', 'wppa') . '</option>';
	
	$selected = $args['selected'] == '0' ? ' selected="selected"' : '';
	if ( $args['addblank'] ) $result .= '<option value="0"'.$selected.' ></option>';
	
	$selected = $args['selected'] == '-99' ? ' selected="selected"' : '';
	if ( $args['addmultiple'] ) $result .= '<option value="-99"'.$selected.' >' . __('--- multiple see below ---', 'wppa') . '</option>';
	
	$selected = $args['selected'] == '0' ? ' selected="selected"' : '';
	if ( $args['addselbox'] ) $result .= '<option value="0"'.$selected.' >' . __('--- a selection box ---', 'wppa') . '</option>';
	
	if ( $albums ) foreach ( $albums as $album ) {
		if ( ( $args['disabled'] == $album['id'] ) || 
			 ( $args['exclude'] == $album['id'] ) ||
			 ( $args['checkupload'] && ! wppa_allow_uploads($album['id']) ) ||
			 ( $args['disableancestors'] && wppa_is_ancestor($args['exclude'], $album['id']) )
			) $disabled = ' disabled="disabled"'; else $disabled = '';
		if ( $args['selected'] == $album['id'] && ! $disabled ) $selected = ' selected="selected"'; else $selected = '';
		if ( ( ! $args['checkaccess'] || wppa_have_access($album['id']) ) ||
			 ( $selected && $args['addselected'] )	
			) {
			if ( $args['addnumbers'] ) $number = ' ('.$album['id'].')'; else $number = '';
			$result .= '<option value="' . $album['id'] . '" ' . $selected . $disabled . '>' . $album['name'] . $number . '</option>';
		}
	}
	
	$selected = $args['selected'] == '-1' ? ' selected="selected"' : '';
	if ( $args['addseparate'] ) $result .= '<option value="-1"' . $selected . '>' . __('--- separate ---', 'wppa') . '</option>';
	
	return $result;
}
