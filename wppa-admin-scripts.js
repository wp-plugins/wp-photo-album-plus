/* admin-scripts.js */
/* Package: wp-photo-album-plus
/*
/* Version 5.4.18
/* Various js routines used in admin pages		
*/

var wppa_moveup_url = '#';
var wppa_import = 'Import';
var wppa_update = 'Update';
var wppaImageDirectory;
var wppaAjaxUrl;
var wppaUploadToThisAlbum = 'Upload to this album';

/* Check if jQuery library revision is high enough, othewise give a message and uncheck checkbox elm */
function checkjQueryRev(msg, elm, rev){
	var version = parseFloat(jQuery.fn.jquery);
	if (elm.checked) {
		if (version < rev) {
			alert (msg+'\nThe version of your jQuery library: '+version+' is too low for this feature. It requires version '+rev);
			elm.checked = '';
		}
	}
}
	
/* This functions does the init after loading settings page. do not put this code in the document.ready function!!! */
function wppaInitSettings() {
	wppaCheckBreadcrumb();
	wppaCheckFullHalign();
	wppaCheckUseThumbOpacity();
	wppaCheckUseCoverOpacity();
	wppaCheckThumbType();
	wppaCheckThumbLink();
	wppaCheckTopTenLink();
	wppaCheckFeaTenLink();
	wppaCheckLasTenLink();
	wppaCheckThumbnailWLink();
	wppaCheckCommentLink();
	wppaCheckMphotoLink();
	wppaCheckSphotoLink();
	wppaCheckSlidePhotoLink();
	wppaCheckSlideOnlyLink();
	wppaCheckAlbumWidgetLink();
	wppaCheckSlideLink();
	wppaCheckCoverImg();
	wppaCheckPotdLink();
	wppaCheckTagLink()
	wppaCheckRating();
	wppaCheckComments();
	wppaCheckCustom();
	wppaCheckResize();
	wppaCheckNumbar();
	wppaCheckWatermark();
	wppaCheckPopup();
	wppaCheckGravatar();
	wppaCheckUserUpload();
	wppaCheckAjax();
	wppaCheckFotomoto();
	wppaCheckLinkPageErr('sphoto');
	wppaCheckLinkPageErr('mphoto');
	wppaCheckLinkPageErr('topten_widget');
	wppaCheckLinkPageErr('slideonly_widget');
	wppaCheckLinkPageErr('widget');
	wppaCheckLinkPageErr('comment_widget');
	wppaCheckLinkPageErr('thumbnail_widget');
	wppaCheckLinkPageErr('lasten_widget');
	wppaCheckLinkPageErr('album_widget');
	wppaCheckLinkPageErr('tagcloud');
	wppaCheckLinkPageErr('multitag');
	wppaCheckLinkPageErr('super_view');
	wppaCheckSplitNamedesc();
	wppaCheckShares();
//	wppaCheckKeepSource();
	wppaCheckCoverType();
	wppaCheckNewpag();
	wppaCheckIndexSearch();
	wppaCheckCDN();
	wppaCheckAutoPage();
	wppaCheckGps();
	wppaCheckFontPreview();
	
	var tab = new Array('O','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII');
	var sub = new Array('A','B','C','D','E','F','G','H','I','J','K');
	
	for (table=1; table<13; table++) {
		var cookie = wppa_getCookie('table_'+table);
		if (cookie == 'on') {
			wppaShowTable(table);	// Refreshes cookie, so it 'never' forgets
		}
		else {
			wppaHideTable(table);	// Refreshes cookie, so it 'never' forgets
		}
		for (subtab=0; subtab<11; subtab++) {
			cookie = wppa_getCookie('table_'+tab[table-1]+'-'+sub[subtab]);
			if (cookie == 'on') {
				wppaToggleSubTable(tab[table-1],sub[subtab]);
			}
		}
		wppaToggleSubTable(tab[table-1],'Z');
	}
}

function wppaToggleTable(table) {
	if (jQuery('#wppa_table_'+table).css('display')=='none') {
		jQuery('#wppa_table_'+table).css('display', 'inline');
		wppa_tablecookieon(table);
	}
	else {
		jQuery('#wppa_table_'+table).css('display', 'none');
		wppa_tablecookieoff(table);
	}
	
}

var wppaSubTabOn = new Array();

function wppaToggleSubTable(table,subtable) {
	if (wppaSubTabOn[table+'-'+subtable]) {
		jQuery('.wppa-'+table+'-'+subtable).addClass('wppa-none');
		wppaSubTabOn[table+'-'+subtable] = false;
		wppa_tablecookieoff(table+'-'+subtable);
	}
	else {
		jQuery('.wppa-'+table+'-'+subtable).removeClass('wppa-none');
		wppaSubTabOn[table+'-'+subtable] = true;
		wppa_tablecookieon(table+'-'+subtable);
	}
}

function wppaHideTable(table) {
	jQuery('#wppa_table_'+table).css('display', 'none'); 
	jQuery('#wppa_tableHide-'+table).css('display', 'none'); 
	jQuery('#wppa_tableShow-'+table).css('display', 'inline');
	wppa_tablecookieoff(table);
}

function wppaShowTable(table) {
	jQuery('#wppa_table_'+table).css('display', 'block'); 
	jQuery('#wppa_tableHide-'+table).css('display', 'inline'); 
	jQuery('#wppa_tableShow-'+table).css('display', 'none');
	wppa_tablecookieon(table);
}

var _wppaRefreshAfter = false;
function wppaRefreshAfter() {
	_wppaRefreshAfter = true;
}

function wppaCheckFotomoto() {
	var on = document.getElementById("wppa_fotomoto_on").checked;
	if ( on ) {
		jQuery(".wppa_fotomoto").css('display', '');
	}
	else {
		jQuery(".wppa_fotomoto").css('display', 'none');
	}
}

function wppaCheckFontPreview() {
	var font = document.getElementById('wppa_textual_watermark_font').value;
	var type = document.getElementById('wppa_textual_watermark_type').value;
	var fsrc = wppaFontDirectory+'wmf'+font+'-'+type+'.png';
	var tsrc = wppaFontDirectory+'wmf'+type+'-'+font+'.png';
	jQuery('#wm-font-preview').attr('src', fsrc);
	jQuery('#wm-type-preview').attr('src', tsrc);
}

/* Adjust visibility of selection radiobutton if fixed photo is chosen or not */				
function wppaCheckWidgetMethod() {
	var ph;
	var i;
	if (document.getElementById('wppa-wm').value=='4') {
		document.getElementById('wppa-wp').style.visibility='visible';
	}
	else {
		document.getElementById('wppa-wp').style.visibility='hidden';
	}
	if (document.getElementById('wppa-wm').value=='1') {
		ph=document.getElementsByName('wppa-widget-photo');
		i=0;
		while (i<ph.length) {
			ph[i].style.visibility='visible';
			i++;	
		}
	}
	else {
		ph=document.getElementsByName('wppa-widget-photo');
		i=0;
		while (i<ph.length) {
			ph[i].style.visibility='hidden';
			i++;
		}
	}
}

/* Displays or hides names and.or description dependant of subtitle chosen */
function wppaCheckWidgetSubtitle() {
	var subtitle = document.getElementById('wppa-st').value;
	var stn, std;
	var i;
	stn = document.getElementsByTagName('h4');
	std = document.getElementsByTagName('h6');
	i = 0;
	switch (subtitle)
	{
	case 'none':
		while (i < stn.length) {
			stn[i].style.visibility = 'hidden';
			std[i].style.visibility = 'hidden';
			i++;
		}
		break;
	case 'name':
		while (i < stn.length) {
			stn[i].style.visibility = 'visible';
			std[i].style.visibility = 'hidden';
			i++;
		}
		break;
	case 'desc':
		while (i < stn.length) {
			stn[i].style.visibility = 'hidden';
			std[i].style.visibility = 'visible';
			i++;
		}
		break;
	}
}

/* Enables or disables the setting of full size horizontal alignment. Only when fullsize is unequal to column width */
/* also no hor align if vertical align is ---default-- */
/* Also show/hide initial colwidth for resp themem ( Table I-A1.1 ) */
function wppaCheckFullHalign() {
	var fs = document.getElementById('wppa_fullsize').value;
	var cs = document.getElementById('wppa_colwidth').value;
	var va = document.getElementById('wppa_fullvalign').value;
	if ((fs != cs) && (va != 'default')) {
		jQuery('.wppa_ha').css('display', '');
	}
	else {
		jQuery('.wppa_ha').css('display', 'none');
	}
	if ( cs == 'auto' ) {
		jQuery('.wppa_init_resp_width').css('display', '');
	}
	else {
		jQuery('.wppa_init_resp_width').css('display', 'none');
	}
}

/* Check for CDN type */
function wppaCheckCDN() {
	var cdn = document.getElementById('wppa_cdn_service').value;
	if ( cdn == 'cloudinary' ) jQuery('.cloudinary').css('display', '');
	else jQuery('.cloudinary').css('display', 'none');
}

/* Check GPX Implementation */
function wppaCheckGps() {
	var gpx = document.getElementById('wppa_gpx_implementation').value;
	if ( gpx == 'wppa-plus-embedded' ) {
		jQuery('.wppa_gpx_native').css('display', '');
		jQuery('.wppa_gpx_plugin').css('display', 'none');
	}
	else {
		jQuery('.wppa_gpx_native').css('display', 'none');
		jQuery('.wppa_gpx_plugin').css('display', '');
	}
}

/* Enables or disables popup thumbnail settings according to availability */
function wppaCheckThumbType() {
	var ttype = document.getElementById('wppa_thumbtype').value;
	if (ttype == 'default') {
		jQuery('.tt_normal').css('display', '');
		jQuery('.tt_ascovers').css('display', 'none');
		jQuery('.tt_always').css('display', '');
		wppaCheckUseThumbOpacity();
	}
	if (ttype == 'ascovers') {
		jQuery('.tt_normal').css('display', 'none');
		jQuery('.tt_ascovers').css('display', '');
		jQuery('.tt_always').css('display', '');
	}
	if (ttype == 'none') {
		jQuery('.tt_normal').css('display', 'none');
		jQuery('.tt_ascovers').css('display', 'none');
		jQuery('.tt_always').css('display', 'none');
	}
}

function wppaCheckAutoPage() {
	var auto = document.getElementById('wppa_auto_page').checked;
	if ( auto ) jQuery('.autopage').css('display', '');
	else jQuery('.autopage').css('display', 'none');
}

/* Enables or disables thumb opacity dependant on whether feature is selected */
function wppaCheckUseThumbOpacity() {
	var topac = document.getElementById('wppa_use_thumb_opacity').checked;
	if (topac) {
		jQuery('.thumb_opacity').css('color', '#333');
		jQuery('.thumb_opacity_html').css('visibility', 'visible');
	}
	else {
		jQuery('.thumb_opacity').css('color', '#999');
		jQuery('.thumb_opacity_html').css('visibility', 'hidden');
	}
}

/* Enables or disables coverphoto opacity dependant on whether feature is selected */
function wppaCheckUseCoverOpacity() {
	var copac = document.getElementById('wppa_use_cover_opacity').checked;
	if (copac) {
		jQuery('.cover_opacity').css('color', '#333');
		jQuery('.cover_opacity_html').css('visibility', 'visible');
	}
	else {
		jQuery('.cover_opacity').css('color', '#999');
		jQuery('.cover_opacity_html').css('visibility', 'hidden');
	}
}

/* Enables or disables secundairy breadcrumb settings */
function wppaCheckBreadcrumb() {
	var Bca = document.getElementById('wppa_show_bread_posts').checked;
	var Bcb = document.getElementById('wppa_show_bread_pages').checked;
	var Bc = Bca || Bcb;
	if (Bc) {
		jQuery('.wppa_bc').css('display', '');
		jQuery('.wppa_bc_html').css('display', '');
		var BcVal = document.getElementById('wppa_bc_separator').value;
		if (BcVal == 'txt') {
			jQuery('.wppa_bc_txt').css('display', '');
			jQuery('.wppa_bc_url').css('display', 'none');
			
			jQuery('.wppa_bc_txt_html').css('display', '');
			jQuery('.wppa_bc_url_html').css('display', 'none');
		}
		else {
			if (BcVal == 'url') {
				jQuery('.wppa_bc_txt').css('display', 'none');
				jQuery('.wppa_bc_url').css('display', '');
				
				jQuery('.wppa_bc_txt_html').css('display', 'none');
				jQuery('.wppa_bc_url_html').css('display', '');
			}
			else {
				jQuery('.wppa_bc_txt').css('display', 'none');
				jQuery('.wppa_bc_url').css('display', 'none');
			}
		}
	}
	else {	
		jQuery('.wppa_bc').css('display', 'none');
		jQuery('.wppa_bc_txt').css('display', 'none');
		jQuery('.wppa_bc_url').css('display', 'none');
	}
}

/* Enables or disables rating system settings */
function wppaCheckRating() {
	var Rt = document.getElementById('wppa_rating_on').checked;
	if (Rt) {
		jQuery('.wppa_rating').css('color', '#333');
		jQuery('.wppa_rating_html').css('visibility', 'visible');
		jQuery('.wppa_rating_').css('display', '');
	}
	else {
		jQuery('.wppa_rating').css('color', '#999');
		jQuery('.wppa_rating_html').css('visibility', 'hidden');
		jQuery('.wppa_rating_').css('display', 'none');
	}
}

function wppaCheckComments() {
	var Cm = document.getElementById('wppa_show_comments').checked;
	if (Cm) {
		jQuery('.wppa_comment').css('color', '#333');
		jQuery('.wppa_comment_html').css('visibility', 'visible');
		jQuery('.wppa_comment_').css('display', '');
	}
	else {
		jQuery('.wppa_comment').css('color', '#999');
		jQuery('.wppa_comment_html').css('visibility', 'hidden');
		jQuery('.wppa_comment_').css('display', 'none');
	}

}

function wppaCheckAjax() {
	var Aa = document.getElementById('wppa_allow_ajax').checked;
	if (Aa) {
		jQuery('.wppa_allow_ajax_').css('display', '');
	}
	else {
		jQuery('.wppa_allow_ajax_').css('display', 'none');
	}
}

function wppaCheckShares() {
	var Sh = document.getElementById('wppa_share_on').checked || document.getElementById('wppa_share_on_widget').checked || document.getElementById('wppa_share_on_lightbox').checked || document.getElementById('wppa_share_on_thumbs').checked || document.getElementById('wppa_share_on_mphoto').checked;
	if (Sh) jQuery('.wppa_share').css('display', '');
	else jQuery('.wppa_share').css('display', 'none');
}
/*
function wppaCheckKeepSource() {
	var Ks = document.getElementById('wppa_keep_source').checked;
	if ( Ks ) jQuery('.wppa_keep_source').css('display', '');
	else jQuery('.wppa_keep_source').css('display', 'none');
}
*/

function wppaCheckCoverType() {
	var Type = document.getElementById('wppa_cover_type').value;
	var Pos = document.getElementById('wppa_coverphoto_pos').value;
	
	if ( Type == 'imagefactory' || Type == 'imagefactory-mcr' ) {
		jQuery('.wppa_imgfact_').css('display', '');
/*		if ( Pos == 'left' || Pos == 'right' )
			alert('To avoid layout problems: please set Cover photo position ( Table IV-D3 ) to \'top\' or \'bottom\'!');
*/	}
	else jQuery('.wppa_imgfact_').css('display', 'none');
	
	if ( Type == 'longdesc' ) {
/*		if ( Pos == 'top' || Pos == 'bottom' ) 
			alert('To avoid layout problems: please set Cover photo position ( Table IV-D3 ) to \'left\' or \'right\'!');
*/	}
}

function wppaCheckNewpag() {
	var Np = document.getElementById('wppa_newpag_create').checked;
	if ( Np ) jQuery('.wppa_newpag').css('display', '');
	else jQuery('.wppa_newpag').css('display', 'none');
}

function wppaCheckCustom() {
	var Cm = document.getElementById('wppa_custom_on').checked;
	if (Cm) {
		jQuery('.wppa_custom').css('color', '#333');
		jQuery('.wppa_custom_html').css('visibility', 'visible');
		jQuery('.wppa_custom_').css('display', '');
	}
	else {
		jQuery('.wppa_custom').css('color', '#999');
		jQuery('.wppa_custom_html').css('visibility', 'hidden');
		jQuery('.wppa_custom_').css('display', 'none');
	}
}

function wppaCheckWidgetLink() { 
	if (document.getElementById('wppa_wlp').value == '-1') {
		jQuery('.wppa_wlu').css('display', ''); 
		jQuery('.wppa_wlt').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_wlu').css('display', 'none'); 
		jQuery('.wppa_wlt').css('visibility', 'visible');
	}
}

function wppaCheckThumbLink() { 
	var lvalue = document.getElementById('wppa_thumb_linktype').value;
	if (lvalue == 'none' || lvalue == 'file' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_tlp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_tlp').css('visibility', 'visible');
	}
	if (lvalue == 'none' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_tlb').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_tlb').css('visibility', 'visible');
	}	
}

function wppaCheckTopTenLink() { 
	var lvalue = document.getElementById('wppa_topten_widget_linktype').value;
	if (lvalue == 'none' || lvalue == 'file' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_ttlp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_ttlp').css('visibility', 'visible');
	}
	if (lvalue == 'none' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_ttlb').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_ttlb').css('visibility', 'visible');
	}
}

function wppaCheckFeaTenLink() {
	var lvalue = document.getElementById('wppa_featen_widget_linktype').value;
	if (lvalue == 'none' || lvalue == 'file' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_ftlp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_ftlp').css('visibility', 'visible');
	}
	if (lvalue == 'none' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_ftlb').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_ftlb').css('visibility', 'visible');
	}
}

function wppaCheckLasTenLink() { 
	var lvalue = document.getElementById('wppa_lasten_widget_linktype').value;
	if (lvalue == 'none' || lvalue == 'file' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_ltlp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_ltlp').css('visibility', 'visible');
	}
	if (lvalue == 'none' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_ltlb').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_ltlb').css('visibility', 'visible');
	}
}

function wppaCheckThumbnailWLink() {
	var lvalue = document.getElementById('wppa_thumbnail_widget_linktype').value;
	if (lvalue == 'none' || lvalue == 'file' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_tnlp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_tnlp').css('visibility', 'visible');
	}
	if (lvalue == 'none' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_tnlb').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_tnlb').css('visibility', 'visible');
	}
}

function wppaCheckCommentLink() {
	var lvalue = document.getElementById('wppa_comment_widget_linktype').value;
	if (lvalue == 'none' || lvalue == 'file' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_cmlp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_cmlp').css('visibility', 'visible');
	}
	if (lvalue == 'none' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_cmlb').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_cmlb').css('visibility', 'visible');
	}
}

function wppaCheckSlideOnlyLink() {
	var lvalue = document.getElementById('wppa_slideonly_widget_linktype').value;
	if (lvalue == 'none' || lvalue == 'file' || lvalue == 'widget' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_solp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_solp').css('visibility', 'visible');
	}
	if (lvalue == 'none' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_solb').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_solb').css('visibility', 'visible');
	}
}

function wppaCheckAlbumWidgetLink() {
	var lvalue = document.getElementById('wppa_album_widget_linktype').value;
	if (lvalue == 'lightbox') {
		jQuery('.wppa_awlp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_awlp').css('visibility', 'visible');
	}
	if (lvalue == 'lightbox') {
		jQuery('.wppa_awlb').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_awlb').css('visibility', 'visible');
	}
}

function wppaCheckSlideLink() {
	var lvalue = document.getElementById('wppa_slideshow_linktype').value;
		if (lvalue == 'none' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_sslb').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_sslb').css('visibility', 'visible');
	}
}

function wppaCheckCoverImg() {
	var lvalue = document.getElementById('wppa_coverimg_linktype').value;
		if (lvalue == 'none' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_covimgbl').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_covimgbl').css('visibility', 'visible');
	}
}

function wppaCheckPotdLink() {
	var lvalue = document.getElementById('wppa_widget_linktype').value;
	if (lvalue == 'none' || lvalue == 'lightbox' || lvalue == 'file' || lvalue == 'custom') {
		jQuery('.wppa_potdlp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_potdlp').css('visibility', 'visible');
	}
	if (lvalue == 'none' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_potdlb').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_potdlb').css('visibility', 'visible');
	}
}

function wppaCheckTagLink() {
	var lvalue = document.getElementById('wppa_tagcloud_linktype').value;
	/* */
}

function wppaCheckMTagLink() {
	var lvalue = document.getElementById('wppa_multitag_linktype').value;
	/* */
}

function wppaCheckMphotoLink() { 
	var lvalue = document.getElementById('wppa_mphoto_linktype').value;
	if (lvalue == 'none' || lvalue == 'file' || lvalue == 'lightbox' ) {
		jQuery('.wppa_mlp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_mlp').css('visibility', 'visible');
	}
	if (lvalue == 'none' || lvalue == 'lightbox' ) {
		jQuery('.wppa_mlb').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_mlb').css('visibility', 'visible');
	}
}

function wppaCheckSphotoLink() { 
	var lvalue = document.getElementById('wppa_sphoto_linktype').value;
	if (lvalue == 'none' || lvalue == 'file' || lvalue == 'lightbox' ) {
		jQuery('.wppa_slp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_slp').css('visibility', 'visible');
	}
	if (lvalue == 'none' || lvalue == 'lightbox' ) {
		jQuery('.wppa_slb').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_slb').css('visibility', 'visible');
	}
}

function wppaCheckSlidePhotoLink() {
	var lvalue = document.getElementById('wppa_slideshow_linktype').value;
	if (lvalue == 'none' || lvalue == 'file' || lvalue == 'lightbox' || lvalue == 'lightboxsingle' || lvalue == 'fullpopup' ) {
		jQuery('.wppa_sslp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_sslp').css('visibility', 'visible');
	}
	if (lvalue == 'none' || lvalue == 'lightbox' || lvalue == 'lightboxsingle' || lvalue == 'fullpopup' ) {
		jQuery('.wppa_sslb').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_sslb').css('visibility', 'visible');
	}
}

function wppaCheckResize() {
	var Rs = document.getElementById('wppa_resize_on_upload').checked;
	if (Rs) {
		jQuery('.re_up').css('display', '');
	}
	else {
		jQuery('.re_up').css('display', 'none');
	}
}

function wppaCheckNumbar() {
	var Nb = document.getElementById('wppa_show_slideshownumbar').checked;
	if (Nb) {
		jQuery('.wppa_numbar').css('display', '');
	}
	else {
		jQuery('.wppa_numbar').css('display', 'none');
	}
}

function wppaCheckWatermark() {
	var Wm = document.getElementById('wppa_watermark_on').checked;
	if (Wm) {
		jQuery('.wppa_watermark').css('display', '');
	}
	else {
		jQuery('.wppa_watermark').css('display', 'none');
	}
}

function wppaCheckPopup() {
	if (document.getElementById('wppa_use_thumb_popup').checked) {
		jQuery('.wppa_popup').css('display', '');
	}
	else {
		jQuery('.wppa_popup').css('display', 'none');
	}
}

function wppaCheckGravatar() {
	if ( ! document.getElementById('wppa_comment_gravatar') ) return;
	if (document.getElementById('wppa_comment_gravatar').value == 'url') {
		jQuery('.wppa_grav').css('display', '');
	}
	else {
		jQuery('.wppa_grav').css('display', 'none');
	}
}

function wppaCheckUserUpload() {
	if (document.getElementById('wppa_user_upload_on').checked) {
		jQuery('.wppa_copyr').css('display', '');
	}
	else {
		jQuery('.wppa_copyr').css('display', 'none');
	}
}

function wppaCheckSplitNamedesc() {
	if (document.getElementById('wppa_split_namedesc').checked) {
		jQuery('.swap_namedesc').css('display', 'none');
		jQuery('.hide_empty').css('display', '');
	}
	else {
		jQuery('.swap_namedesc').css('display', '');
		jQuery('.hide_empty').css('display', 'none');
	}
}

function wppaCheckIndexSearch() {
	if (document.getElementById('wppa_indexed_search').checked) {
		jQuery('.index_search').css('display', '');
	}
	else {
		jQuery('.index_search').css('display', 'none');
	}
}

function wppa_tablecookieon(i) {
	wppa_setCookie('table_'+i, 'on', '365');
}

function wppa_tablecookieoff(i) {
	wppa_setCookie('table_'+i, 'off', '365');
}

function wppa_setCookie(c_name,value,exdays) {
var exdate=new Date();
exdate.setDate(exdate.getDate() + exdays);
var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
document.cookie=c_name + "=" + c_value;
}

function wppa_getCookie(c_name) {
var i,x,y,ARRcookies=document.cookie.split(";");
for (i=0;i<ARRcookies.length;i++)
{
  x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
  y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
  x=x.replace(/^\s+|\s+$/g,"");
  if (x==c_name)
    {
    return unescape(y);
    }
  }
  return "";
}

function wppaCookieCheckbox(elm, id) {
	if ( elm.checked ) wppa_setCookie(id, 'on', '365');
	else wppa_setCookie(id, 'off', '365');
}

function wppa_move_up(who) {
	document.location = wppa_moveup_url+who+"&wppa-nonce="+document.getElementById('wppa-nonce').value;
}

function checkColor(slug) {
	var color = document.getElementById(slug).value;
	jQuery('#colorbox-'+slug).css('background-color', color);
}

function checkAll(name, clas) {
	var elm = document.getElementById(name);
	if (elm) {
		if ( elm.checked ) {
			jQuery(clas).prop('checked', 'checked');
		}
		else {
			jQuery(clas).prop('checked', '');
		}
	}
}

/*
function checkOrg(name, clas) {
	var elm = document.getElementById(name);
	if (elm) {
		var all = jQuery(clas); 
		var idx = 0;
		var checkboxid;
		var photoname;
		var lastdash;
		var isorig;
		var sizespec;
		var pointpos;
		var temparr;
		while ( idx < all.length ) {
			isorig = false;
			checkboxid = jQuery(all[idx]).attr('id');
			photoname = jQuery('#name-'+checkboxid).html();
			photoname = photoname.replace(/&nbsp;/g, '');
			photoname = photoname.replace(' ', '');
			lastdash = photoname.lastIndexOf('-');
			if ( lastdash == -1 ) {
				isorig = true;
			}
			else {
				sizespec = photoname.substr(lastdash+1);
				pointpos = sizespec.indexOf('.');
				if ( pointpos > 0 ) sizespec = sizespec.substr(0, pointpos);
				temparr = sizespec.split('x');
				if ( temparr.length != 2 || parseInt(temparr[0]) == 0 || parseInt(temparr[1]) == 0 ) {
					isorig = true;
				}
			}
			if ( isorig ) {
				if ( elm.checked ) {
					jQuery('#'+checkboxid).prop('checked', 'checked');
				}
				else {
					jQuery('#'+checkboxid).prop('checked', '');
				}
			}
			else {	// NOT orig
				if ( elm.checked ) {
					jQuery('#td-'+checkboxid).css('visibility', 'hidden');
				}
				else {
					jQuery('#td-'+checkboxid).css('visibility', 'visible');
				}
			}
			idx++;
		}
	}
}
*/

function impUpd(elm, id) {
	if ( elm.checked ) {
		jQuery(id).prop('value', wppa_update);
		jQuery('.hideifupdate').css('display', 'none');
	}
	else {
		jQuery(id).prop('value', wppa_import);
		jQuery('.hideifupdate').css('display', '');
	}
}

function wppaAjaxDeletePhoto(photo) {

	wppaFeAjaxLog('in');

	var xmlhttp = wppaGetXmlHttp();

	// Make the Ajax url
	var url = wppaAjaxUrl+'?action=wppa&wppa-action=delete-photo&photo-id='+photo;
	url += '&wppa-nonce='+document.getElementById('photo-nonce-'+photo).value;

	// Do the Ajax action
	xmlhttp.open('GET',url,true);
	xmlhttp.send();

	// Process the result
	xmlhttp.onreadystatechange=function() {
		switch (xmlhttp.readyState) {
		case 1:
			document.getElementById('photostatus-'+photo).innerHTML = 'server connection established';
			break;
		case 2:
			document.getElementById('photostatus-'+photo).innerHTML = 'request received';
			break;
		case 3:
			document.getElementById('photostatus-'+photo).innerHTML = 'processing request';
			break;
		case 4:
			if ( xmlhttp.status == 200 ) {
				var str = wppaTrim(xmlhttp.responseText);
				var ArrValues = str.split("||");
				if (ArrValues[0] != '') {
					alert('The server returned unexpected output:\n'+ArrValues[0]);
				}
				
				if ( ArrValues[1] == 0 ) document.getElementById('photostatus-'+photo).innerHTML = ArrValues[2];	// Error
				else {
					document.getElementById('photoitem-'+photo).innerHTML = ArrValues[2];	// OK
					wppaProcessFull(ArrValues[3], ArrValues[4]);
				}
				wppaFeAjaxLog('out');
			}
			else {	// status != 200
				document.getElementById('photoitem-'+photo).innerHTML = '<span style="color:red;" >Comm error '+xmlhttp.status+': '+xmlhttp.statusText+'</span>';
			}			
		}
	}
}

function wppaAjaxApplyWatermark(photo, file, pos) {

	wppaFeAjaxLog('in');

	var xmlhttp = wppaGetXmlHttp();

	// Show spinner
	jQuery('#wppa-water-spin-'+photo).css({visibility:'visible'});
	
	// Make the Ajax send data
	var data = 'action=wppa&wppa-action=watermark-photo&photo-id='+photo;
	data += '&wppa-nonce='+document.getElementById('photo-nonce-'+photo).value;
	if (file) data += '&wppa-watermark-file='+file;
	if (pos) data += '&wppa-watermark-pos='+pos;

	// Do the Ajax action
	xmlhttp.open('POST',wppaAjaxUrl,true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.send(data);

	// Process the result
	xmlhttp.onreadystatechange=function() {
		if ( xmlhttp.readyState == 4 ) {
			if ( xmlhttp.status == 200 ) {
				var str = wppaTrim(xmlhttp.responseText);
				var ArrValues = str.split("||");

				if (ArrValues[0] != '') {
					alert('The server returned unexpected output:\n'+ArrValues[0]);
				}
				switch (ArrValues[1]) {
					case '0':		// No error
						document.getElementById('photostatus-'+photo).innerHTML = ArrValues[2];
						break;
					default:
						document.getElementById('photostatus-'+photo).innerHTML = '<span style="color:red">'+ArrValues[2]+'</span>';
				}
				// Hide spinner
				jQuery('#wppa-water-spin-'+photo).css({visibility:'hidden'});
				
				wppaFeAjaxLog('out');
			}
			else {	// status != 200
				document.getElementById('photostatus-'+photo).innerHTML = '<span style="color:red;" >Comm error '+xmlhttp.status+': '+xmlhttp.statusText+'</span>';
			}			
		}
	}
}

var wppaAjaxPhotoCount = new Array();
var wppaPhotoUpdateMatrix = new Array();

function wppaAjaxUpdatePhoto(photo, actionslug, elem, refresh) {

	var count = wppaPhotoUpdateMatrix.length;
	var i = 0;
	var found = false;
	var index;
	while ( i < count ) {
		if ( wppaPhotoUpdateMatrix[i][0] == photo && wppaPhotoUpdateMatrix[i][1] == actionslug ) {
			found = true;
			index = i;
		}
		i++;
	}
	if ( ! found ) {
		var oldval = 'undefined';
		var newval = false;
		var busy = false;
		var refresh = false;
		wppaPhotoUpdateMatrix[count] = [photo, actionslug, oldval, newval, busy, refresh];
		index = count;
	}
	wppaPhotoUpdateMatrix[index][3] = elem.value;
	wppaPhotoUpdateMatrix[index][5] = refresh;

	wppaAjaxUpdatePhotoMonitor();	
}

function wppaAjaxUpdatePhotoMonitor() {

	var count = wppaPhotoUpdateMatrix.length;
	var i = 0;

	while ( i < count ) {
		if ( ( wppaPhotoUpdateMatrix[i][2] != wppaPhotoUpdateMatrix[i][3] ) && ! wppaPhotoUpdateMatrix[i][4] ) {
			wppaPhotoUpdateMatrix[i][4] = true;
			_wppaAjaxUpdatePhoto( wppaPhotoUpdateMatrix[i][0], wppaPhotoUpdateMatrix[i][1], wppaPhotoUpdateMatrix[i][3], wppaPhotoUpdateMatrix[i][5] );
		}
		i++;
	}
}

function _wppaAjaxUpdatePhoto(photo, actionslug, value, refresh) {

	wppaFeAjaxLog('in');
	if ( ! wppaAjaxPhotoCount[photo] ) wppaAjaxPhotoCount[photo] = 0;
	wppaAjaxPhotoCount[photo]++;

	var xmlhttp = wppaGetXmlHttp();

	// Show spinner
	if ( actionslug == 'description' ) jQuery('#wppa-photo-spin-'+photo).css({visibility:'visible'});

	// Make the Ajax send data
	var data = 'action=wppa&wppa-action=update-photo&photo-id='+photo+'&item='+actionslug;
	data += '&wppa-nonce='+document.getElementById('photo-nonce-'+photo).value;
	data += '&value='+wppaEncode(value);

	// Do the Ajax action
	xmlhttp.open('POST',wppaAjaxUrl,true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.send(data);
	jQuery('#photostatus-'+photo).html('Working, please wait... ('+wppaAjaxPhotoCount[photo]+')');

	// Process the result
	xmlhttp.onreadystatechange=function() {
		switch (xmlhttp.readyState) {
		case 1:
			document.getElementById('photostatus-'+photo).innerHTML = 'server connection established';
			break;
		case 2:
			document.getElementById('photostatus-'+photo).innerHTML = 'request received';
			break;
		case 3:
			document.getElementById('photostatus-'+photo).innerHTML = 'processing request';
			break;
		case 4:
			wppaAjaxPhotoCount[photo]--;
			if ( xmlhttp.status == 200 ) {
				var str = wppaTrim(xmlhttp.responseText);
				var ArrValues = str.split("||");

				if (ArrValues[0] != '') {
					alert('The server returned unexpected output:\n'+ArrValues[0]);
				}
				switch (ArrValues[1]) {
					case '0':		// No error
						if ( wppaAjaxPhotoCount[photo] == 0 ) jQuery('#photostatus-'+photo).html(ArrValues[2]);
						else jQuery('#photostatus-'+photo).html('Working, please wait... ('+wppaAjaxPhotoCount[photo]+')');
						break;
					case '99':	// Photo is gone
						document.getElementById('photoitem-'+photo).innerHTML = '<span style="color:red">'+ArrValues[2]+'</span>';
						break;
					default:	// Any error
						document.getElementById('photostatus-'+photo).innerHTML = '<span style="color:red">'+ArrValues[2]+' ('+ArrValues[1]+')</span>';
						break;
				}
				// Hide spinner
				if ( actionslug == 'description' ) jQuery('#wppa-photo-spin-'+photo).css({visibility:'hidden'});
//				if ( actionslug == 'rotleft' || actionslug == 'rotright' ) 

				// Update matrix
				var i = 0;
				var index;
				count = wppaPhotoUpdateMatrix.length;
				while ( i < count ) {
					if ( wppaPhotoUpdateMatrix[i][0] == photo && wppaPhotoUpdateMatrix[i][1] == actionslug ) {
						index = i;
					}
					i++;
				}
				wppaPhotoUpdateMatrix[index][2] = value;
				wppaPhotoUpdateMatrix[index][4] = false;	// no more busy
				wppaPhotoUpdateMatrix[index][5] = false;	// reset refresh
				
				wppaFeAjaxLog('out');
				
				wppaAjaxUpdatePhotoMonitor();	// check for more

				if ( refresh ) wppaRefresh('photo_'+photo);
			}
			else {	// status != 200
				document.getElementById('photostatus-'+photo).innerHTML = '<span style="color:red;" >Comm error '+xmlhttp.status+': '+xmlhttp.statusText+'</span>';
			}			
		}
	}
}

function wppaChangeScheduleAlbum(album, elem) {
	var onoff = jQuery(elem).prop('checked');
	if ( onoff ) {
		jQuery('.wppa-datetime-'+album).css('display', 'inline');
	}
	else {
		jQuery('.wppa-datetime-'+album).css('display', 'none');
		wppaAjaxUpdateAlbum(album, 'scheduledtm', document.getElementById('wppa-dummy') );
	}
}

var wppaAjaxAlbumCount = 0;
var wppaAlbumUpdateMatrix = new Array();

function wppaAjaxUpdateAlbum(album, actionslug, elem) {

	// Links
	if ( actionslug == 'set_deftags' || actionslug == 'add_deftags' ) {
		_wppaAjaxUpdateAlbum(album, actionslug, elem.value);
		return;
	}
	
	var count = wppaAlbumUpdateMatrix.length;
	var i = 0;
	var found = false;
	var index;
	while ( i < count ) {
		if ( wppaAlbumUpdateMatrix[i][0] == album && wppaAlbumUpdateMatrix[i][1] == actionslug ) {
			found = true;
			index = i;
		}
		i++;
	}
	if ( ! found ) {
		var oldval = 'undefined';
		var newval = false;
		var busy = false;
		wppaAlbumUpdateMatrix[count] = [album, actionslug, oldval, newval, busy];
		index = count;
	}
	wppaAlbumUpdateMatrix[index][3] = elem.value;
	
	wppaAjaxUpdateAlbumMonitor();
}

function wppaAjaxUpdateAlbumMonitor() {

	var count = wppaAlbumUpdateMatrix.length;
	var i = 0;

	while ( i < count ) {
		if ( ( wppaAlbumUpdateMatrix[i][2] != wppaAlbumUpdateMatrix[i][3] ) && ! wppaAlbumUpdateMatrix[i][4] ) {
			wppaAlbumUpdateMatrix[i][4] = true;
			_wppaAjaxUpdateAlbum( wppaAlbumUpdateMatrix[i][0], wppaAlbumUpdateMatrix[i][1], wppaAlbumUpdateMatrix[i][3] );
		}
		i++;
	}
}

var _wppaRefreshAfter = false;
function _wppaAjaxUpdateAlbum(album, actionslug, value) {

	wppaAjaxAlbumCount++;
	
	var xmlhttp = wppaGetXmlHttp();

	// Show spinner
	if ( actionslug == 'description' ) jQuery('#wppa-album-spin').css({visibility:'visible'});
	
	// Make the Ajax send data
	var data = 'action=wppa&wppa-action=update-album&album-id='+album+'&item='+actionslug;
	data += '&wppa-nonce='+document.getElementById('album-nonce-'+album).value;
	data += '&value='+wppaEncode(value);

	// Do the Ajax action
	xmlhttp.open('POST',wppaAjaxUrl,true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.send(data);
	document.getElementById('albumstatus-'+album).innerHTML = 'Working, please wait... ('+wppaAjaxAlbumCount+')';

	// Process the result
	xmlhttp.onreadystatechange=function() {
		switch (xmlhttp.readyState) {
		case 1:
			document.getElementById('albumstatus-'+album).innerHTML = 'server connection established';
			break;
		case 2:
			document.getElementById('albumstatus-'+album).innerHTML = 'request received';
			break;
		case 3:
			document.getElementById('albumstatus-'+album).innerHTML = 'processing request';
			break;
		case 4:
			wppaAjaxAlbumCount--;
			if ( xmlhttp.status == 200 ) {
				var str = wppaTrim(xmlhttp.responseText);
				var ArrValues = str.split("||");

				if (ArrValues[0] != '') {
					alert('The server returned unexpected output:\n'+ArrValues[0]);
				}
				switch (ArrValues[1]) {
					case '0':		// No error
						// Update status
						if ( wppaAjaxAlbumCount == 0 ) jQuery('#albumstatus-'+album).html(ArrValues[2]);
						else jQuery('#albumstatus-'+album).html('Working, please wait... ('+wppaAjaxAlbumCount+')');
						// Process full/notfull
						if ( typeof(ArrValues[3]) != 'undefined' ) wppaProcessFull(ArrValues[3], ArrValues[4]);
						break;
					case '97':		// Ratings cleared
						document.getElementById('albumstatus-'+album).innerHTML = ArrValues[2];
						jQuery('.wppa-rating').html(ArrValues[3]);
						break;
					default:		// Any error
						document.getElementById('albumstatus-'+album).innerHTML = '<span style="color:red">'+ArrValues[2]+' ('+ArrValues[1]+')</span>';
						break;
				}
				
				// Need refresh?
				if ( _wppaRefreshAfter ) {
					_wppaRefreshAfter = false;
					document.location.reload(true);
				}

				// Hide spinner
				if ( actionslug == 'description' ) jQuery('#wppa-album-spin').css({visibility:'hidden'});
				
				// Update Matrix
				var i = 0;
				var index;
				var count = wppaAlbumUpdateMatrix.length;
				while ( i < count ) {
					if ( wppaAlbumUpdateMatrix[i][0] == album && wppaAlbumUpdateMatrix[i][1] == actionslug ) {
						index = i;
					}
					i++;
				}
				wppaAlbumUpdateMatrix[index][2] = value;
				wppaAlbumUpdateMatrix[index][4] = false;
					
				wppaAjaxUpdateAlbumMonitor();	// Check for more to do
				
				// Refresh for alt main_photo selections when cover type changed
//				if ( actionslug == 'cover_type' ) document.location = document.location;
			}
			else {	// status != 200
				document.getElementById('albumstatus-'+album).innerHTML = '<span style="color:red;" >Comm error '+xmlhttp.status+': '+xmlhttp.statusText+'</span>';
			}			
		}
	}
}
	
function wppaProcessFull(arg, n) {

	if ( arg == 'full' ) {
		jQuery('#full').css('display', '');
		jQuery('#notfull').css('display', 'none');
	}
	if ( arg == 'notfull' ) {
		jQuery('#full').css('display', 'none');
		if ( n > 0 ) jQuery('#notfull').attr('value', wppaUploadToThisAlbum+' (max '+n+')');
		else jQuery('#notfull').attr('value', wppaUploadToThisAlbum);
		jQuery('#notfull').css('display', '');
	}
}
	
function wppaAjaxUpdateCommentStatus(photo, id, value) {
	
	var xmlhttp = wppaGetXmlHttp();
	
	// Make the Ajax url
	var url = wppaAjaxUrl+	'?action=wppa&wppa-action=update-comment-status'+
							'&wppa-photo-id='+photo+
							'&wppa-comment-id='+id+
							'&wppa-comment-status='+value+
							'&wppa-nonce='+document.getElementById('photo-nonce-'+photo).value;
	
	xmlhttp.onreadystatechange=function() {
		if ( xmlhttp.readyState == 4 ) {
			if ( xmlhttp.status == 200 ) {
				var str = wppaTrim(xmlhttp.responseText);
				var ArrValues = str.split("||");

				if (ArrValues[0] != '') {
					alert('The server returned unexpected output:\n'+ArrValues[0]);
				}
				switch (ArrValues[1]) {
					case '0':		// No error
						jQuery('#photostatus-'+photo).html(ArrValues[2]);
						break;
					default:	// Error
						jQuery('#photostatus-'+photo).html('<span style="color:red">'+ArrValues[2]+'</span>');
						break;
				}
				jQuery('#wppa-comment-spin-'+id).css('visibility', 'hidden');
			}
			else {	// status != 200
				jQuery('#photostatus-'+photo).html('<span style="color:red;" >Comm error '+xmlhttp.status+': '+xmlhttp.statusText+'</span>');
			}			
		}
	}

	// Do the Ajax action
	xmlhttp.open('GET',url,true);
	xmlhttp.send();	
}

function wppaAjaxUpdateOptionCheckBox(slug, elem) {

	var xmlhttp = wppaGetXmlHttp();

	// Make the Ajax url
	var url = wppaAjaxUrl+'?action=wppa&wppa-action=update-option&wppa-option='+slug;
	url += '&wppa-nonce='+document.getElementById('wppa-nonce').value;
	if (elem.checked) url += '&value=yes';
	else url += '&value=no';

	// Process the result
	xmlhttp.onreadystatechange=function() {
		switch (xmlhttp.readyState) {
		case 1:
		case 2:
		case 3:
			document.getElementById('img_'+slug).src = wppaImageDirectory+'clock.png';
			break;
		case 4:
			var str = wppaTrim(xmlhttp.responseText);
			var ArrValues = str.split("||");
			
			if (ArrValues[0] != '') {
				alert('The server returned unexpected output:\n'+ArrValues[0]);
			}
			if (xmlhttp.status!=404) {
				switch (ArrValues[1]) {
					case '0':	// No error
						document.getElementById('img_'+slug).src = wppaImageDirectory+'tick.png';
						document.getElementById('img_'+slug).title = ArrValues[2];
						if ( ArrValues[3] != '' ) alert(ArrValues[3]);
						if ( _wppaRefreshAfter ) {
							_wppaRefreshAfter = false;
							document.location.reload(true);
						}
						break;
					default:
						document.getElementById('img_'+slug).src = wppaImageDirectory+'cross.png';
						document.getElementById('img_'+slug).title = 'Error #'+ArrValues[1]+', message: '+ArrValues[2]+', status: '+xmlhttp.status;
					}
				
			}
			else {
				document.getElementById('img_'+slug).src = wppaImageDirectory+'cross.png';
				document.getElementById('img_'+slug).title = 'Communication error, status = '+xmlhttp.status;
			}
			wppaCheckInconsistencies();
		}
	}
	
	// Do the Ajax action
	xmlhttp.open('GET',url,true);
	xmlhttp.send();	
}

function wppaMaintenanceProc(slug, intern) {

	// If running: stop
	if ( ! intern && document.getElementById(slug+"_continue").value == 'yes' ) {
		document.getElementById(slug+"_continue").value = 'no';
		document.getElementById(slug+"_button").value = 'Start!';
		if ( jQuery("#"+slug+"_togo").html() > 0 ) {
			jQuery("#"+slug+"_status").html('Pausing...');
			jQuery("#"+slug+"_button").css('display', 'none');
		}
		return;
	}

	// Start
	document.getElementById(slug+"_continue").value = 'yes';
	document.getElementById(slug+"_button").value = 'Stop!';
	jQuery("#"+slug+"_status").html('Working');

	var  xmlhttp = wppaGetXmlHttp();
	
	// Response on-unit
	xmlhttp.onreadystatechange=function() {
		if ( xmlhttp.readyState == 4 ) {
			if ( xmlhttp.status = 200 ) {
				var resp 	= xmlhttp.responseText;			// sample: '<error>||<slug>||<status>||<togo>'
				var resparr = resp.split("||");
				var slug 	= resparr[1];
				var error 	= false;
				
				// Check for unrecoverable error
				if ( ! slug ) {
					alert('The server returned unexpected output:\n'+xmlhttp.responseText+'\nIf the current procedure has a Skip One button, press it before retrying. Reloading page...');
					wppaReload();
					return;	// give up;
				}
				
				// Check for recoverable error
				if ( resparr[0] != '' ) {
					alert('An error occurred:\n'+resparr[0]);
					error = true;
				}
				
				// Update status and togo
				jQuery("#"+slug+"_status").html(resparr[2]);
				jQuery("#"+slug+"_togo").html(resparr[3]);
				jQuery("#"+slug+"_button").css('display', '');

				// Stop on error or on ready
				if ( error || resparr[3] == '0' ) {
					if ( resparr[4] == 'reload' ) {
						alert('This page will now be reloaded to finish the operation. Please stay tuned...');
						wppaReload();
						return;
					}
					else {
						setTimeout('wppaMaintenanceProc(\''+slug+'\', false)', 20);	// fake extern to stop it
					}
					return;
				}

				// Continue if not stopped by user
				if ( document.getElementById(slug+"_continue").value == 'yes' ) {
					setTimeout('wppaMaintenanceProc(\''+slug+'\', true)', 20);
					return;
				}
			}
			else {	// Status not 200
				jQuery("#"+slug+"_status").html('Comm err '+xmlhttp.status);
				setTimeout('wppaMaintenanceProc(\''+slug+'\', false)', 20);	// fake extern to stop it
				return;
			}

		}
	}
	
	// Compose ajax url
	var data = 'action=wppa&wppa-action=maintenance&slug='+slug;
	data += '&wppa-nonce='+document.getElementById('wppa-nonce').value;

	// Do the Ajax action
	xmlhttp.open('POST',wppaAjaxUrl,true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.send(data);
}

function wppaAjaxPopupWindow( slug ) {
	
	var name;
	switch ( slug ) {
		case 'wppa_list_index':
			name = 'Search index table';
			break;
		case 'wppa_list_errorlog':
			name = 'WPPA+ Error log';
			break;
		case 'wppa_list_rating':
			name = 'Recent ratings';
			break;
		case 'wppa_list_session':
			name = 'Active sessions';
			break;
	}
	var desc = '';
	var width = 960;
	var height = 512;

	if ( screen.availWidth < width ) width = screen.availWidth;

	var wnd = window.open("", "_blank", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=yes, width="+width+", height="+height, true);

	wnd.document.write('<!DOCTYPE html>');
	wnd.document.write('<html>');
	wnd.document.write('<head>');	
		// The following is one statement that fixes a bug in opera
		wnd.document.write(	'<link rel="stylesheet" id="wppa_style-css"  href="'+wppaWppaUrl+'/wppa-admin-styles.css?ver='+wppaVersion+'" type="text/css" media="all" />'+
							'<style>body {font-family: sans-serif; font-size: 12px; line-height: 1.4em;}a {color: #21759B;}</style>'+
							'<script type="text/javascript" src="'+wppaIncludeUrl+'/js/jquery/jquery.js?ver='+wppaVersion+'"></script>'+
							'<script type="text/javascript" src="'+wppaWppaUrl+'/wppa-admin-scripts.js?ver='+wppaVersion+'"></script>'+
							'<title>'+name+'</title>'+
							'<script type="text/javascript">wppaAjaxUrl="'+wppaAjaxUrl+'";</script>');
	wnd.document.write('</head>');
	wnd.document.write('<body>'); // onunload="window.opener.location.reload()">');	// This does not work in Opera
	
	var xmlhttp = wppaGetXmlHttp();
	
	// Make the Ajax send data
	var url = wppaAjaxUrl;
	var data = 'action=wppa&wppa-action=maintenancepopup&slug='+slug;
	data += '&wppa-nonce='+document.getElementById('wppa-nonce').value;
			
	// Do the Ajax action
	xmlhttp.open('POST', url, false);	// Synchronously !!
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.send(data);

	// Process result
	if (xmlhttp.readyState==4 && xmlhttp.status==200) {
		var result = xmlhttp.responseText;
		wnd.document.write(result);
	}
	wnd.document.write('</body>');
	wnd.document.write('</html>');

}

function wppaAjaxUpdateOptionValue(slug, elem) {

	var xmlhttp = wppaGetXmlHttp();

	// on-unit to process the result
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState != 4) {
			document.getElementById('img_'+slug).src = wppaImageDirectory+'clock.png';
		}
		else {	// Ready
			var str = wppaTrim(xmlhttp.responseText);
//alert(str);
			var ArrValues = str.split("||");

			if (ArrValues[0] != '') {
				alert('The server returned unexpected output:\n'+ArrValues[0]);
			}
			if (xmlhttp.status!=404) {	// No Not found
				switch (ArrValues[1]) {
					case '0':	// No error
						document.getElementById('img_'+slug).src = wppaImageDirectory+'tick.png';
						if ( ArrValues[3] != '' ) alert(ArrValues[3]);
						if ( _wppaRefreshAfter ) {
							_wppaRefreshAfter = false;
							document.location.reload(true);
						}
						break;
					default:
						document.getElementById('img_'+slug).src = wppaImageDirectory+'cross.png';
						if ( ArrValues[3] != '' ) alert(ArrValues[3]);
				}
				document.getElementById('img_'+slug).title = ArrValues[2];
			}
			else {						// Not found
				document.getElementById('img_'+slug).src = wppaImageDirectory+'cross.png';
				document.getElementById('img_'+slug).title = 'Communication error';
			}
			wppaCheckInconsistencies();
		}
	}

	// Make the Ajax url
	eslug = wppaEncode(slug);
	var data = 'action=wppa&wppa-action=update-option&wppa-option='+eslug;
	data += '&wppa-nonce='+document.getElementById('wppa-nonce').value;
	if ( elem != 0 ) data += '&value='+wppaEncode(elem.value);
//if (!confirm('Do '+wppaAjaxUrl+'\n'+data)) return;	// Diagnostic
	// Do the Ajax action
	xmlhttp.open('POST',wppaAjaxUrl,true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.send(data);
}
	
function wppaEncode(xtext) {
	var text, result;
	
	if (typeof(xtext)=='undefined') return;
	
	text = xtext;
	result = text.replace(/#/g, '||HASH||');
	text = result;
	result = text.replace(/&/g, '||AMP||');
	text = result;
//	result = text.replace(/+/g, '||PLUS||');
	var temp = text.split('+');
	var idx = 0;
	result = '';
	while (idx < temp.length) {
		result += temp[idx];
		idx++;
		if (idx < temp.length) result += '||PLUS||';
	}

//	alert('encoded result='+result);
	return result;
}

// Check conflicting settings, Autosave version only
function wppaCheckInconsistencies() {

	// Uses thumb popup and thumb lightbox?
	if ( document.getElementById('wppa_use_thumb_popup').checked == true && document.getElementById('wppa_thumb_linktype').value == 'lightbox' ) {
		jQuery('.popup-lightbox-err').css('display', '');
	}
	else {
		jQuery('.popup-lightbox-err').css('display', 'none');
	}
}

// Get the http request object
function wppaGetXmlHttp() {
	if (window.XMLHttpRequest) {		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else {								// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	return xmlhttp;
}

function wppaPhotoStatusChange(id) {
	// Init
	jQuery('#psdesc-'+id).css({display: 'none'});
	elm = document.getElementById('status-'+id);
	
	if ( elm.value == 'pending' || elm.value == 'scheduled' ) {
		jQuery('#photoitem-'+id).css({backgroundColor: '#ffebe8', borderColor: '#cc0000'});
	}
	if (elm.value=='publish') {
		jQuery('#photoitem-'+id).css({backgroundColor:'#ffffe0', borderColor:'#e6db55'}); 
	}
	if (elm.value=='featured') {
		jQuery('#photoitem-'+id).css({backgroundColor: '#e0ffe0', borderColor: '#55ee55'});
		var temp = document.getElementById('pname-'+id).value;
		var name = temp.split('.')
		if (name.length > 1) {
			var i = 0;
			while ( i< name.length ) {
				if (name[i] == 'jpg' || name[i] == 'JPG' ) {
					jQuery('#psdesc-'+id).css({display: ''});
				}
				i++;
			}
		}
	}
	if (elm.value=='gold') {
		jQuery('#photoitem-'+id).css({backgroundColor:'#eeeecc', borderColor:'#ddddbb'}); 
	}
	if (elm.value=='silver') {
		jQuery('#photoitem-'+id).css({backgroundColor:'#ffffff', borderColor:'#eeeeee'}); 
	}
	if (elm.value=='bronze') {
		jQuery('#photoitem-'+id).css({backgroundColor:'#ddddbb', borderColor:'#ccccaa'}); 
	}
		
	if ( elm.value == 'scheduled' ) {
		jQuery( '.wppa-datetime-'+id ).css('display', ''); //prop( 'disabled', false );
	}
	else {
		jQuery( '.wppa-datetime-'+id ).css('display', 'none'); //prop( 'disabled', true );
	}
}

function wppaCheckLinkPageErr(slug) {
	var type = 'nil';
		if ( document.getElementById('wppa_'+slug+'_linktype') ) type = document.getElementById('wppa_'+slug+'_linktype').value;
	var page = document.getElementById('wppa_'+slug+'_linkpage').value;
	
	if ( page == '0' && ( type == 'nil' || type == 'photo' || type == 'single' || type == 'album' || type == 'content' || type == 'slide' || type == 'plainpage' )) {
		jQuery('#'+slug+'-err').css({display:''});
	}
	else {
		jQuery('#'+slug+'-err').css({display:'none'});
	}
}

function wppaAddCat(val, id) {
	wppaAddTag(val, id);
}

function wppaAddTag(val, id) {
	var elm = document.getElementById(id);
	if ( val ) {
		if ( elm.value ) {
			elm.value += ','+val;
		}
		else {
			elm.value = val;
		}
	}
}

function wppaRefresh(label) {
	var oldurl 	= new String(document.location);
	var temp 	= oldurl.split("#");
	var newurl 	= temp[0]+'#'+label;

	document.location = newurl;
}
function wppaReload() {
	document.location.reload( true );
}
function wppaTrim (str) {
    return str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}

var wppaFeCount = 0;
function wppaFeAjaxLog(key) {
 
	if ( key == 'in' ) {
		if ( wppaFeCount == 0 ) {
			jQuery('#wppa-fe-exit').css('display', 'none');
		}
		wppaFeCount++;
		jQuery('#wppa-fe-count').html(wppaFeCount);
	}
	if ( key == 'out' ) {
		if ( wppaFeCount == 1 ) {
			jQuery('#wppa-fe-count').html('');
			jQuery('#wppa-fe-exit').css('display', 'inline');
			wppaFeCount--;
		}
		if ( wppaFeCount > 1 ) {
			wppaFeCount--;
			jQuery('#wppa-fe-count').html(wppaFeCount);
		}
	}
}

function wppaArrayToEnum( arr, sep ) {
	
	// Step 1. Sort Ascending Numeric
	temp = arr.sort(function(a, b){return a-b});
	
	// Init	
	var result = '';
	var lastitem = -1;
	var previtemp = -2;
	var lastitemp = 0;
	var isrange = false;
	var i = 0;
	var item;
	while ( i < arr.length ) {
		item = arr[i].valueOf();
		if ( item != 0 ) {
			lastitemp = lastitem;
			lastitemp++;
			if ( item == lastitemp ) {
				isrange = true;
			}
			else {
				if ( isrange ) {	// Close range
					if ( lastitem == previtemp ) {	// Range is x . (x+1)
						result += sep + lastitem + sep + item;
					}
					else {
						result += sep + sep + lastitem + sep + item;
					}
					isrange = false;
				}
				else {				// Add single item
					result += sep + item;
				}
			}
			if ( ! isrange ) {
				previtemp = item;
				previtemp++;
			}
			lastitem = item;
		}
		i++;
	}
	if ( isrange ) {	// Don't forget the last if it ends in a range
		result += '..' + lastitem;
	}
	
	// ltrim .
	while ( result.substr(0,1) == '.') result = result.substr(1);

	return result;
}

function wppaGetSelEnumToId( cls, id ) {
	p = jQuery( '.'+cls );
	var pararr = [];
	i = 0;
	j = 0;
	while ( i < p.length ) {
		if ( p[i].selected ) {
			pararr[j] = p[i].value;
			j++;
		}
		i++;
	}
	jQuery( '#'+id ).attr( 'value', wppaArrayToEnum( pararr, '.' ) );
}
