/* admin-scripts.js */
/* Package: wp-photo-album-plus
/*
/* Version 4.1.0
/* Various js routines used in admin pages		
*/

var wppa_moveup_url = '#';
var wppa_import = 'Import';
var wppa_update = 'Update';

jQuery(document).ready(function() {
/* alert( 'You are running jQuery version: ' + jQuery.fn.jquery ); */

	jQuery(".fade").fadeTo(20000, 0.0)
	});

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
	wppaCheckHs();
	wppaCheckUseThumbOpacity();
	wppaCheckUseCoverOpacity();
	wppaCheckThumbType();
	wppaCheckThumbLink();
	wppaCheckTopTenLink();
	wppaCheckMphotoLink();
	wppaCheckSlideOnlyLink();
	wppaCheckPotdLink();
	wppaCheckRating();
	wppaCheckComments();
	wppaCheckLightbox();
	
	for (table=1; table<11; table++) {
		var cookie = wppa_getCookie('table_'+table);
		if (cookie == 'on') {
			wppaShowTable(table);	// Refreshes cookie, so it 'never' forgets
		}
		else {
			wppaHideTable(table);	// Refreshes cookie, so it 'never' forgets
		}
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
function wppaCheckFullHalign() {
	var fs = document.getElementById('wppa_fullsize').value;
	var cs = document.getElementById('wppa_colwidth').value;
	var va = document.getElementById('wppa_fullvalign').value;
	if ((fs != cs) && (va != 'default')) {
		jQuery('.wppa_ha').css('color', '#333');
		jQuery('.wppa_ha_html').css('visibility', 'visible');
	}
	else {
		jQuery('.wppa_ha').css('color', '#999');
		jQuery('.wppa_ha_html').css('visibility', 'hidden');
	}
}

/* Enables or disables popup thumbnail settings according to availability */
function wppaCheckThumbType() {
	var ttype = document.getElementById('wppa_thumbtype').value;
	if (ttype == 'default') {
		jQuery('.tt_normal').css('diaplay', 'inline');
		jQuery('.tt_ascovers').css('display', 'none');
		jQuery('.tt_always').css('diaplay', 'inline');
		wppaCheckUseThumbOpacity();
	}
	if (ttype == 'ascovers') {
		jQuery('.tt_normal').css('display', 'none');
		jQuery('.tt_ascovers').css('diaplay', 'inline');
		jQuery('.tt_always').css('diaplay', 'inline');
	}
	if (ttype == 'none') {
		jQuery('.tt_normal').css('display', 'none');
		jQuery('.tt_ascovers').css('display', 'none');
		jQuery('.tt_always').css('display', 'none');
	}
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

/* if the slideshow is disabled its useless to ask if it should initially run */
function wppaCheckHs() {
	var Hs = document.getElementById('wppa_enable_slideshow').checked;
	if (Hs) jQuery('.wppa_ss').css('diaplay', 'inline');
	else jQuery('.wppa_ss').css('display', 'none');
}

/* Enables or disables secundairy breadcrumb settings */
function wppaCheckBreadcrumb() {
	var Bc = document.getElementById('wppa_show_bread').checked;
	if (Bc) {
		jQuery('.wppa_bc').css('visibility', 'visible');
		var BcVal = document.getElementById('wppa_bc_separator').value;
		if (BcVal == 'txt') {
			jQuery('.wppa_bc_txt').css('color', '#333');
			jQuery('.wppa_bc_url').css('color', '#999');
			
			jQuery('.wppa_bc_txt_html').css('visibility', 'visible');
			jQuery('.wppa_bc_url_html').css('visibility', 'hidden');
		}
		else {
			if (BcVal == 'url') {
				jQuery('.wppa_bc_txt').css('color', '#999');
				jQuery('.wppa_bc_url').css('color', '#333');
				
				jQuery('.wppa_bc_txt_html').css('visibility', 'hidden');
				jQuery('.wppa_bc_url_html').css('visibility', 'visible');
			}
			else {
				jQuery('.wppa_bc_txt').css('color', '#999');
				jQuery('.wppa_bc_url').css('color', '#999');
				
				jQuery('.wppa_bc_txt_html').css('visibility', 'hidden');
				jQuery('.wppa_bc_url_html').css('visibility', 'hidden');
			}
		}
	}
	else {	
		jQuery('.wppa_bc').css('color', '#999');
		jQuery('.wppa_bc_txt').css('color', '#999');
		jQuery('.wppa_bc_url').css('color', '#999');
		
		jQuery('.wppa_bc_html').css('visibility', 'hidden');
		jQuery('.wppa_bc_txt_html').css('visibility', 'hidden');
		jQuery('.wppa_bc_url_html').css('visibility', 'hidden');
	}
}

/* Enables or disables rating system settings */
function wppaCheckRating() {
	var Rt = document.getElementById('wppa_rating_on').checked;
	if (Rt) {
		jQuery('.wppa_rating').css('color', '#333');
		jQuery('.wppa_rating_html').css('visibility', 'visible');
	}
	else {
		jQuery('.wppa_rating').css('color', '#999');
		jQuery('.wppa_rating_html').css('visibility', 'hidden');
	}
}

function wppaCheckComments() {
	var Cm = document.getElementById('wppa_show_comments').checked;
	if (Cm) {
		jQuery('.wppa_comment').css('color', '#333');
		jQuery('.wppa_comment_html').css('visibility', 'visible');
	}
	else {
		jQuery('.wppa_comment').css('color', '#999');
		jQuery('.wppa_comment_html').css('visibility', 'hidden');
	}

}

function wppaCheckWidgetLink() { 
	if (document.getElementById('wppa_wlp').value == '-1') {
		jQuery('.wppa_wlu').css('diaplay', 'inline'); 
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
}

function wppaCheckTopTenLink() { 
	var lvalue = document.getElementById('wppa_topten_widget_linktype').value;
	if (lvalue == 'none' || lvalue == 'file' || lvalue == 'lightbox' || lvalue == 'fullpopup') {
		jQuery('.wppa_ttlp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_ttlp').css('visibility', 'visible');
	}
}

function wppaCheckSlideOnlyLink() {
	var lvalue = document.getElementById('wppa_slideonly_widget_linktype').value;
	if (lvalue == 'none' || lvalue == 'file' || lvalue == 'widget') {
		jQuery('.wppa_solp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_solp').css('visibility', 'visible');
	}
}

function wppaCheckPotdLink() {
	var lvalue = document.getElementById('wppa_widget_linktype').value;
	if (lvalue == 'none' || lvalue == 'file' || lvalue == 'custom') {
		jQuery('.wppa_potdlp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_potdlp').css('visibility', 'visible');
	}
}

function wppaCheckMphotoLink() { 
	var lvalue = document.getElementById('wppa_mphoto_linktype').value;
	if (lvalue == 'none' || lvalue == 'file') {
		jQuery('.wppa_mlp').css('visibility', 'hidden');
	}
	else {
		jQuery('.wppa_mlp').css('visibility', 'visible');
	}
}

function wppaCheckLightbox() {
	var Lb = document.getElementById('wppa_use_lightbox').checked;
	if (Lb) {
		jQuery('.wppa_lightbox').css('diaplay', 'inline');
	}
	else {
		jQuery('.wppa_lightbox').css('display', 'none');
	}
}

function wppa_tablecookieon(i) {
	wppa_setCookie('table_'+i, 'on', '365');
}

function wppa_tablecookieoff(i) {
	wppa_setCookie('table_'+i, 'off', '365');
}

function wppa_setCookie(c_name,value,exdays)
{
var exdate=new Date();
exdate.setDate(exdate.getDate() + exdays);
var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
document.cookie=c_name + "=" + c_value;
}

function wppa_getCookie(c_name)
{
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

function wppa_move_up(who) {
	document.location = wppa_moveup_url+who+"&wppa-update-check="+document.getElementById('wppa-update-check').value;
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

function impUpd(elm, id) {
	if ( elm.checked ) {
		jQuery(id).prop('value', wppa_update);
	}
	else {
		jQuery(id).prop('value', wppa_import);
	}
}