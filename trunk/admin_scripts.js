/* admin_scripts.js */
/* Package: wp-photo-album-plus
/*
/* Version 2.5.0
/* Various js routines used in admin pages		
*/

jQuery(document).ready(function() {
	jQuery(".fade").fadeTo(10000, 0.1)
	});

/* This functions does the init after loading settings page. do not put this code in the document.ready function!!! */
function wppaInitSettings() {
	wppaCheckFullHalign();
	wppaCheckUseThumbOpacity();
	wppaCheckUseCoverOpacity();
	wppaCheckThumbType();
}
	
/* Adjust visibility of selection radiobutton if fixed photo is chosen or not */				
function wppaCheckWidgetMethod() {
	var ph;
	var i;
	if (document.getElementById("wppa-wm").value=="4") {
		document.getElementById("wppa-wp").style.visibility="visible";
	}
	else {
		document.getElementById("wppa-wp").style.visibility="hidden";
	}
	if (document.getElementById("wppa-wm").value=="1") {
		ph=document.getElementsByName("wppa-widget-photo");
		i=0;
		while (i<ph.length) {
			ph[i].style.visibility="visible";
			i++;	
		}
	}
	else {
		ph=document.getElementsByName("wppa-widget-photo");
		i=0;
		while (i<ph.length) {
			ph[i].style.visibility="hidden";
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
			stn[i].style.visibility = "hidden";
			std[i].style.visibility = "hidden";
			i++;
		}
		break;
	case 'name':
		while (i < stn.length) {
			stn[i].style.visibility = "visible";
			std[i].style.visibility = "hidden";
			i++;
		}
		break;
	case 'desc':
		while (i < stn.length) {
			stn[i].style.visibility = "hidden";
			std[i].style.visibility = "visible";
			i++;
		}
		break;
	}
}

/* Enables or disables the setting of full size horizontal alignment. Only when fullsize is unequal to column width */
/* also no hor align if vertical align is ---default-- */
function wppaCheckFullHalign() {
	var fs = document.getElementById('wppa-fullsize').value;
	var cs = document.getElementById('wppa-colwidth').value;
	var va = document.getElementById('wppa-fullvalign').value;
	if ((fs != cs) && (va != 'default')) {
		jQuery('.wppa-ha').css('visibility', 'visible');
	}
	else {
		jQuery('.wppa-ha').css('visibility', 'collapse');
	}
}

/* Enables or disables popup thumbnail settings according to availability */
function wppaCheckThumbType() {
	var ttype = document.getElementById('wppa-thumbtype').value;
	if (ttype == 'default') {
		jQuery('.tt-normal').css('visibility', 'visible');
		jQuery('.tt-ascovers').css('visibility', 'collapse');
		wppaCheckUseThumbOpacity();
	}
	if (ttype == 'ascovers') {
		jQuery('.tt-normal').css('visibility', 'collapse');
		jQuery('.tt-ascovers').css('visibility', 'visible');
	}
}

/* Enables or disables thumb opacity dependant on whether feature is selected */
function wppaCheckUseThumbOpacity() {
	var topac = document.getElementById('wppa-use-thumb-opacity').checked;
	if (topac) {
		jQuery('.thumb-opacity').css('visibility', 'visible');
	}
	else {
		jQuery('.thumb-opacity').css('visibility', 'collapse');
	}
}

/* Enables or disables coverphoto opacity dependant on whether feature is selected */
function wppaCheckUseCoverOpacity() {
	var copac = document.getElementById('wppa-use-cover-opacity').checked;
	if (copac) {
		jQuery('.cover-opacity').css('visibility', 'visible');
	}
	else {
		jQuery('.cover-opacity').css('visibility', 'collapse');
	}
}

function wppaCheckHs() {
	var Hs = document.getElementById('wppa-hide-slideshow').checked;
	if (Hs) jQuery(".wppa-ss").css('visibility', 'visible');
	else jQuery(".wppa-ss").css('visibility', 'collapse');
}

/* Enables or disables secundairy breadcrumb settings */
function wppaCheckBreadcrumb() {
	var Bc = document.getElementById('wppa-show-bread').checked;
	if (Bc) {
		jQuery('.wppa-bc').css('visibility', 'visible');
	}
	else {
		jQuery('.wppa-bc').css('visibility', 'collapse');
	}
}

/* Enables or disables rating system settings */
function wppaCheckRating() {
	var Rt = document.getElementById('wppa-rating-on').checked;
	if (Rt) {
		jQuery('.wppa-rating').css('visibility', 'visible');
	}
	else {
		jQuery('.wppa-rating').css('visibility', 'collapse');
	}
}

function wppaCheckWidgetLink() { 
	if (document.getElementById('wppa-wlp').value == '-1') {
		jQuery('.wppa-wlu').css('visibility', 'visible'); 
	}
	else {
		jQuery('.wppa-wlu').css('visibility', 'collapse'); 
	}
}
