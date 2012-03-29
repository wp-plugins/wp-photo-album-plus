/* wppa-ajax.js
* Package: wp-photo-album-plus
*
* Contains the ajax and history code for the frontend
* except ajax votng what is in the wppa-slideshow.js
*
* Additionally: functions to change the url for addthis during ajax and browse operations
*
* Version 4.4.0
*
*/

// AJAX RENDERING INCLUDING HISTORY MANAGEMENT
// IF AJAX NOT ALLOWED, ALSO NO HISTORY MENAGEMENT!!

var wppaHis = 0;
var wppaStartHtml = new Array();
var wppaCanAjaxRender = false;	// Assume failure
var wppaCanPushState = false;
var wppaAllowAjax = true;		// Assume we are allowed to use ajax
var wppaMaxOccur = 0;
var wppaFirstOccur = 0;
var wppaUsePhotoNamesInUrls = false;

// Initialize
jQuery(document).ready(function(e) {
	// Are we allowed anyway?
	if ( ! wppaAllowAjax ) return;	// Not allowed today
	
	if ( wppaGetXmlHttp() ) {
		wppaCanAjaxRender = true;
	}
	if ( history.pushState ) {		
		// Save entire initial page content ( I do not know which container is going to be modified first )
		var i=1;
		while (i<=wppaMaxOccur) {
			wppaStartHtml[i] = jQuery('#wppa-container-'+i).html();
			i++;
		}
		wppaCanPushState = true;
	}
});

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

// Setup an event handler for popstate events
window.onpopstate = function(event) { 
	var occ = 0;
	if ( wppaCanPushState ) {
		if ( event.state ) {
			occ = event.state.occur;
			switch ( event.state.type ) {
				case 'html':
					// Restore wppa container content
					jQuery('#wppa-container-'+occ).html(event.state.html);
					break;
				case 'slide':
					// Go to specified slide without the didgoto switch to avoid a stackpush here
					_wppaGoto(occ, event.state.slide);
					break;				
			}
		}
		else {
			occ = wppaFirstOccur;
			// Restore first modified occurrences content
			jQuery('#wppa-container-'+occ).html(wppaStartHtml[occ]);
			// Now we are back to the initial page
			wppaFirstOccur = 0;
			// If a photo number given goto that photo
			if (occ == 0) {	// Find current occur if not yet known
				var url = document.location.href;
				var urls = url.split("&wppa-occur=");
				occ = parseInt(urls[1]);			
			}
			var url = document.location.href;
			var urls = url.split("&wppa-photo=");
			var photo = parseInt(urls[1]);
			if (photo > 0) {
				var idx = 0;
				while ( idx < _wppaPhotoIds[occ].length ) {
					if (_wppaPhotoIds[occ][idx] == photo) break;
					idx++;
				}
				if ( idx < _wppaPhotoIds[occ].length ) _wppaGoto(occ, idx);
			}
		}
		// If it is a slideshow, stop it
		if ( document.getElementById('theslide0-'+occ) ) {
			_wppaStop(occ);
		}
	}
};  

// The AJAX rendering routine
function wppaDoAjaxRender(mocc, ajaxurl, newurl) {

	// Create the http request object
	var xmlhttp = wppaGetXmlHttp();
		
	if ( wppaCanAjaxRender ) {	// Ajax possible
		// Setup process the result
		xmlhttp.onreadystatechange = function() {
			if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 ) {
				// Update the wppa container
				jQuery('#wppa-container-'+mocc).html(xmlhttp.responseText);
				if ( wppaCanPushState ) {
					// Push state on stack
					wppaHis++;
					cont = xmlhttp.responseText;
					history.pushState({page: wppaHis, occur: mocc, type: 'html', html: cont}, "---", newurl);
					if ( wppaFirstOccur == 0 ) wppaFirstOccur = mocc;
				}
			}
		}
		// If it is a slideshow: Stop slideshow before pushing it on the stack
		if ( _wppaCurrentIndex[mocc] ) _wppaStop(mocc);
		// Do the Ajax action
		xmlhttp.open('GET',ajaxurl,true);
		xmlhttp.send();	
	}
	else {	// Ajax NOT possible
		document.location.href = newurl;
	}
	/* addthis */
	wppaUpdateAddThisUrl(newurl, '');
}

function wppaPushStateSlide(mocc, slide) {

	if ( wppaCanPushState ) {
		var url = wppaGetCurrentFullUrl(mocc, _wppaCurrentIndex[mocc]);
		history.pushState({page: wppaHis, occur: mocc, type: 'slide', slide: slide}, "---", url);
	}
}

// WPPA modules for addthis.
//

var wppaAddThis = false;
var addthis = null;

jQuery(document).ready(function(){
	if (addthis) {
		addthis.init();	// In case loaded asynchronously
		wppaAddThis = true;
	}
});



function wppaUpdateAddThisUrl(url, title) {
	if ( ! wppaAddThis ) return;	// No addthis activated
	addthis.update('share', 'url', url);
	if (title != '') {
		addthis.update('share', 'title', title);
	}
}



