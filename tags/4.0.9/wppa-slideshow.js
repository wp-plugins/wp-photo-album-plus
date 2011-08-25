// Slide show variables and functions
// This is wppa-slideshow.js version 4.0.8
//
// Vars. The vars that have a name that starts with an underscore is an internal var
// The vars without leading underscore are 'external' and get a value from html

// 'External' variables
var wppaFullValignFit = new Array();
var wppaAnimationSpeed;
var wppaImageDirectory;
var wppaAutoColumnWidth = false;
var wppaThumbnailAreaDelta;
var wppaSlideShowTimeOut = 2500;
var wppaFadeInAfterFadeOut = false;
var wppaTextFrameDelta = 0;
var wppaBoxDelta = 0;
var wppaPreambule;
var wppaThumbnailPitch = new Array();
var wppaFilmStripLength = new Array();
var wppaFilmStripMargin = new Array();
var wppaFilmStripAreaDelta = new Array();
var wppaFilmShowGlue;
var wppaIsMini = new Array();
var wppa_portrait_only = new Array();
var wppaSlideShow;				// = 'Slideshow' or its translation
var wppaPhoto;					// = 'Photo' or its translation
var wppaOf;						// = 'of' or its translation
var wppaNextPhoto;				// = 'Next photo' or its translation
var wppaPreviousPhoto;			// = 'Previous photo' or its translation
var wppaNextP;
var wppaPrevP;
var wppaStart = 'Start';		// defaults
var wppaStop = 'Stop';			//
var wppaPleaseName;
var wppaPleaseEmail;
var wppaPleaseComment;
var wppaRatingOnce = true;
var wppaUserName;

// 'Internal' variables
var _wppaPhotoIds = new Array();
var _wppaPhotoAverages = new Array();
var _wppaPhotoMyRating = new Array();
var _wppaVoteReturnUrl = new Array();
var _wppaInWidgetLinkUrl = new Array();
var _wppaInWidgetLinkTitle = new Array();
var _wppaCommentHtml = new Array();
var _wppaToTheSame = false;
var _wppaSlides = new Array();
var _wppaNames = new Array();
var _wppaDescriptions = new Array();
var _wppaCurrentIndex = new Array();
var _wppaNextIndex = new Array();
var _wppaTimeOut = new Array();
var _wppaSlideShowRuns = new Array();
var _wppaForeground = new Array();
var _wppaTogglePending = new Array();
var _wppaIsBusy = new Array();
var _wppaFirst = new Array();
var _wppaVoteInProgress = false;
var _wppaTextDelay;


jQuery(document).ready(function(){
	_wppaLog('ready', 0);
	if (wppaAutoColumnWidth) _wppaDoAutocol(0);
	_wppaTextDelay = wppaAnimationSpeed;
	if (wppaFadeInAfterFadeOut) _wppaTextDelay *= 2;
});

// First the external entrypoints that may be called directly from HTML
// These functions check the validity and store the users request to be executed later if busy and if applicable.

// This is an entrypoint to load the slide data
function wppaStoreSlideInfo(mocc, id, url, size, width, height, name, desc, photoid, avgrat, myrat, rateurl, iwlinkurl, iwlinktitle, iwtimeout, commenthtml) {
	if ( ! _wppaSlides[mocc] ) {
		_wppaSlides[mocc] = new Array();
		_wppaNames[mocc] = new Array();
		_wppaDescriptions[mocc] = new Array();
		_wppaCurrentIndex[mocc] = -1;
		_wppaNextIndex[mocc] = 0;
		if (parseInt(iwtimeout) > 0) _wppaTimeOut[mocc] = parseInt(iwtimeout);
		else _wppaTimeOut[mocc] = wppaSlideShowTimeOut;
		_wppaSlideShowRuns[mocc] = false;
		_wppaTogglePending[mocc] = -2;	// -2 means NO, index for _wppaStartStop otherwise
		_wppaForeground[mocc] = 0;
		_wppaIsBusy[mocc] = false;
		_wppaFirst[mocc] = true;
		wppaFullValignFit[mocc] = false;
		_wppaPhotoIds[mocc] = new Array();
		_wppaPhotoAverages[mocc] = new Array();
		_wppaPhotoMyRating[mocc] = new Array();
		_wppaVoteReturnUrl[mocc] = new Array();
		wppa_portrait_only[mocc] = false;
		_wppaInWidgetLinkUrl[mocc] = new Array(); // iwlinkurl;
		_wppaInWidgetLinkTitle[mocc] = new Array(); // iwlinktitle;
		_wppaCommentHtml[mocc] = new Array();
	}
    _wppaSlides[mocc][id] = ' src="' + url + '" alt="' + name + '" class="theimg big" ' + 'width="' + width + '" height="' + height + '" style="' + size + '; display:block;">';
    _wppaNames[mocc][id] = name;
    _wppaDescriptions[mocc][id] = desc;
	_wppaPhotoIds[mocc][id] = photoid;		// reqd for rating and comment
	_wppaPhotoAverages[mocc][id] = avgrat;		// avg ratig value
	_wppaPhotoMyRating[mocc][id] = myrat;		// my rating
	_wppaVoteReturnUrl[mocc][id] = rateurl;		// url that performs the vote and returns to the page
	_wppaInWidgetLinkUrl[mocc][id] = iwlinkurl;
	_wppaInWidgetLinkTitle[mocc][id] = iwlinktitle;
	_wppaCommentHtml[mocc][id] = commenthtml;
}

function wppaSpeed(mocc, faster) {
	// Can change speed of slideshow only when running
	if ( _wppaSlideShowRuns[mocc] ) {
		_wppaSpeed(mocc, faster);
	}
}

function wppaStartStop(mocc, index) {
	// The application contains various togglers for start/stop
	// The busy flag will be reset at the end of the NextSlide procedure
	if ( _wppaIsBusy[mocc] ) {					// Busy...
		_wppaTogglePending[mocc] = index;		// Remember there is a toggle pending
	}
	else { 										// Not busy...
		if ( _wppaSlideShowRuns[mocc] ) {		// Stop it
			_wppaStop(mocc);
		}
		else {	// Start it
			_wppaStart(mocc, index);
		}
	}
}

function wppaBbb(mocc, where, act) {
	// Big Browsing Buttons only work when stopped
	if ( ! _wppaSlideShowRuns[mocc] ) {
		_wppaBbb(mocc, where, act);
	}
}

function wppaRateIt(mocc, value) {
	_wppaRateIt(mocc, value);
}

function wppaPrev(mocc) {
	if ( ! _wppaSlideShowRuns[mocc] ) {
		_wppaPrev(mocc);
	}
}

function wppaNext(mocc) {
	if ( ! _wppaSlideShowRuns[mocc] ) {
		_wppaNext(mocc);
	}
}

function wppaFollowMe(mocc, idx) {
	if ( ! _wppaSlideShowRuns[mocc] ) {
		_wppaFollowMe(mocc, idx);
	}
}

function wppaLeaveMe(mocc, idx) {
	if ( ! _wppaSlideShowRuns[mocc] ) {
		_wppaLeaveMe(mocc, idx);
	}
}

function wppaGoto(mocc, idx) {
	if ( ! _wppaSlideShowRuns[mocc] ) {
		_wppaGoto(mocc, idx);
	}
}

function _wppaNextSlide(mocc, mode) {
	_wppaLog('NextSlide', mocc);

	if ( ! _wppaSlideShowRuns[mocc] && mode == 'auto' ) return; // Kill an old timed request, while stopped
	// Set the busy flag
	_wppaIsBusy[mocc] = true;

	var fg = _wppaForeground[mocc];
	var bg = 1 - fg;

	// Find index of next slide if in auto mode and not stop in progress
	if (_wppaSlideShowRuns[mocc]) {
		_wppaNextIndex[mocc] = _wppaCurrentIndex[mocc] + 1;
		if (_wppaNextIndex[mocc] == _wppaSlides[mocc].length) _wppaNextIndex[mocc] = 0;
	}
    // first:
    if (_wppaFirst[mocc]) {
	    if (_wppaCurrentIndex[mocc] != -1) {
			if (_wppaInWidgetLinkUrl[mocc][_wppaCurrentIndex[mocc]] != '') jQuery("#theslide0-"+mocc).html('<a href="'+_wppaInWidgetLinkUrl[mocc][_wppaCurrentIndex[mocc]]+'" title="'+_wppaInWidgetLinkTitle[mocc][_wppaCurrentIndex[mocc]]+'"><img id="theimg0-'+mocc+'" '+_wppaSlides[mocc][_wppaCurrentIndex[mocc]]+'</a>');
			else jQuery("#theslide0-"+mocc).html('<img id="theimg0-'+mocc+'" '+_wppaSlides[mocc][_wppaCurrentIndex[mocc]]);
			jQuery("#theimg0-"+mocc).hide();
//			jQuery("#theslide0-"+mocc).css('zIndex','901');
		}
		if (_wppaInWidgetLinkUrl[mocc][_wppaNextIndex[mocc]] != '') {
			jQuery("#theslide1-"+mocc).html('<a href="'+_wppaInWidgetLinkUrl[mocc][_wppaNextIndex[mocc]]+'" title="'+_wppaInWidgetLinkTitle[mocc][_wppaNextIndex[mocc]]+'"><img id="theimg1-'+mocc+'" '+_wppaSlides[mocc][_wppaNextIndex[mocc]]+'</a>');
		}
		else {
			jQuery("#theslide1-"+mocc).html('<img id="theimg1-'+mocc+'" '+_wppaSlides[mocc][_wppaNextIndex[mocc]]);
		}
		jQuery("#theimg1-"+mocc).hide();	      
//		jQuery("#theslide1-"+mocc).css('zIndex','900');
	
		_wppaLoadSpinner(mocc);
	    
		jQuery("#imagedesc-"+mocc).html('&nbsp;'+_wppaDescriptions[mocc][_wppaCurrentIndex[mocc]]+'&nbsp;');
		jQuery("#imagetitle-"+mocc).html('&nbsp;'+_wppaNames[mocc][_wppaCurrentIndex[mocc]]+'&nbsp;');
		jQuery("#comments-"+mocc).html(_wppaCommentHtml[mocc][_wppaCurrentIndex[mocc]]);
		
		// Display counter and arrow texts
		if (document.getElementById('counter-'+mocc)) {
			if (wppaIsMini[mocc]) {
				document.getElementById('prev-arrow-'+mocc).innerHTML = wppaPrevP;
				document.getElementById('next-arrow-'+mocc).innerHTML = wppaNextP;
			}
			else {
				document.getElementById('prev-arrow-'+mocc).innerHTML = wppaPreviousPhoto;
				document.getElementById('next-arrow-'+mocc).innerHTML = wppaNextPhoto;
			}
		}
    }
    // end first
    else {
    	// load next img (backg)
		if (_wppaInWidgetLinkUrl[mocc][_wppaNextIndex[mocc]] != '') jQuery("#theslide"+bg+"-"+mocc).html('<a href="'+_wppaInWidgetLinkUrl[mocc][_wppaNextIndex[mocc]]+'" title="'+_wppaInWidgetLinkTitle[mocc][_wppaNextIndex[mocc]]+'"><img id="theimg'+bg+'-'+mocc+'" '+_wppaSlides[mocc][_wppaNextIndex[mocc]]+'</a>');
		else jQuery("#theslide"+bg+"-"+mocc).html('<img id="theimg'+bg+'-'+mocc+'" '+_wppaSlides[mocc][_wppaNextIndex[mocc]]);
//		jQuery("#theslide"+bg+"-"+mocc).css('zIndex', '900');
//		jQuery("#theslide"+fg+"-"+mocc).css('zIndex', '901');
		jQuery("#theimg"+bg+"-"+mocc).hide();
    }
	_wppaFirst[mocc] = false;
	
	// See if the filmstrip needs wrap around before shifting to the right location
	_wppaCheckRewind(mocc);

    // Next is now current
    _wppaCurrentIndex[mocc] = _wppaNextIndex[mocc];
	if (wppaAutoColumnWidth) _wppaDoAutocol(mocc);

	setTimeout('_wppaNextSlide_2('+mocc+')', 10);
}

function _wppaNextSlide_2(mocc) {
	_wppaLog('NextSlide_2', mocc);

	var fg, bg;	

	fg = _wppaForeground[mocc];
	bg = 1 - fg;
	// Wait for load complete
	if (!document.getElementById('theimg'+bg+"-"+mocc).complete) {
		setTimeout('_wppaNextSlide_2('+mocc+')', 100);	// Try again after 100 ms
		return;
	}
	// Remove spinner
	_wppaUnloadSpinner(mocc);
	// Do autocol if required
	if (wppaAutoColumnWidth) _wppaDoAutocol(mocc);
	// Hide subtitles
	if (_wppaSlideShowRuns[mocc] != -1) {	// not stop in progress
		if (!_wppaToTheSame) {
			_wppaShowMetaData(mocc, 'hide');
		}
	}
	// change foreground
	_wppaForeground[mocc] = 1 - _wppaForeground[mocc];
	fg = _wppaForeground[mocc];
	bg = 1 - fg;
//	jQuery("#theslide"+bg+"-"+mocc).css('zIndex', '900');
//	jQuery("#theslide"+fg+"-"+mocc).css('zIndex', '901');
	setTimeout('_wppaNextSlide_3('+mocc+')', 10);
}

function _wppaNextSlide_3(mocc) {
	_wppaLog('NextSlide_3', mocc);

	var fg;
	var bg;
	fg = _wppaForeground[mocc];
	bg = 1 - fg;

	jQuery("#theimg"+bg+"-"+mocc).fadeOut(wppaAnimationSpeed); 					// Req'd for change in portrait/landscape vv

	// Fadein new image
	if (wppaFadeInAfterFadeOut) {
		jQuery("#theimg"+fg+"-"+mocc).delay(wppaAnimationSpeed).fadeIn(wppaAnimationSpeed, _wppaNextSlide_4(mocc)); 
	}
	else {
		jQuery("#theimg"+fg+"-"+mocc).fadeIn(wppaAnimationSpeed, _wppaNextSlide_4(mocc)); 
	}
}

function _wppaNextSlide_4(mocc) {
	_wppaLog('NextSlide_4', mocc);

	// set height to fit if reqd
	if (wppa_portrait_only[mocc]) {
		h = jQuery('#theimg'+_wppaForeground[mocc]+'-'+mocc).css('height');
		jQuery('#slide_frame-'+mocc).css('height', parseInt(h)+'px');
	}
	else if (wppaFullValignFit[mocc]) {
		h = jQuery('#theimg'+_wppaForeground[mocc]+'-'+mocc).css('height');
		if (h != 'auto') {
			jQuery('#slide_frame-'+mocc).css('height', parseInt(h)+'px');
		}
		jQuery('#slide_frame-'+mocc).css('minHeight', '0px');
	}

	// Display counter and arrow texts
	if (document.getElementById('counter-'+mocc)) {
		if (wppaIsMini[mocc]) {
			document.getElementById('counter-'+mocc).innerHTML = (_wppaCurrentIndex[mocc]+1)+' / '+_wppaSlides[mocc].length;
		}
		else {
			document.getElementById('counter-'+mocc).innerHTML = wppaPhoto+' '+(_wppaCurrentIndex[mocc]+1)+' '+wppaOf+' '+_wppaSlides[mocc].length;
		}
	}

	// Update breadcrumb
	if (document.getElementById('bc-pname-'+mocc)) document.getElementById('bc-pname-'+mocc).innerHTML = _wppaNames[mocc][_wppaCurrentIndex[mocc]];

	// Adjust filmstrip
	var xoffset;
	xoffset = wppaFilmStripLength[mocc] / 2 - (_wppaCurrentIndex[mocc] + 0.5 + wppaPreambule) * wppaThumbnailPitch[mocc] - wppaFilmStripMargin[mocc];
	if (wppaFilmShowGlue) xoffset -= (wppaFilmStripMargin[mocc] * 2 + 2);	// Glue
	jQuery('#wppa-filmstrip-'+mocc).animate({marginLeft: xoffset+'px'});
	
	// Set rating mechanism
	_wppaSetRatingDisplay(mocc);
	
	// Wait for almost next slide
	setTimeout('_wppaNextSlide_5('+mocc+')', _wppaTextDelay); 
}

function _wppaNextSlide_5(mocc) {
	_wppaLog('NextSlide_5', mocc);

	// If we are going to the same slide, there is no need to hide and restore the subtitles and commentframe
	if (!_wppaToTheSame) {	
		// Restore subtitles
		jQuery('#imagedesc-'+mocc).html('&nbsp;' + _wppaDescriptions[mocc][_wppaCurrentIndex[mocc]] + '&nbsp;');
		jQuery('#imagetitle-'+mocc).html('&nbsp;' + _wppaNames[mocc][_wppaCurrentIndex[mocc]] + '&nbsp;');
		// Restore comments html
		jQuery("#comments-"+mocc).html(_wppaCommentHtml[mocc][_wppaCurrentIndex[mocc]]);
	}
	_wppaToTheSame = false;					// This has now been worked out

	_wppaShowMetaData(mocc, 'show'); 

	if ( _wppaTogglePending[mocc] != -2 ) {			// A Toggle pending?
		var index = _wppaTogglePending[mocc];		// Remember the pending startstop request argument
		_wppaTogglePending[mocc] = -2;				// Reset the pending toggle
		wppaStartStop(mocc, index);					// Do as if the toggle request happens now
	}
	else {										// No toggle pending
		if (_wppaSlideShowRuns[mocc]) {				// Wait for next slide
			setTimeout('_wppaNextSlide('+mocc+', "auto")', _wppaTimeOut[mocc]); 
		}	
		else {									// Done!
//			jQuery(".arrow-"+mocc).stop().fadeTo(400,1);
		}
	}

	_wppaIsBusy[mocc] = false;					// No longer busy
}
 
function _wppaNext(mocc) {
	_wppaLog('Next', mocc);

	_wppaNextIndex[mocc] = _wppaCurrentIndex[mocc] + 1;
	if (_wppaNextIndex[mocc] == _wppaSlides[mocc].length) _wppaNextIndex[mocc] = 0;
	jQuery(".arrow-"+mocc).stop().fadeTo(400,0.2);
	_wppaNextSlide(mocc, 0);
}

function _wppaPrev(mocc) {
	_wppaLog('Prev', mocc);
	
	_wppaNextIndex[mocc] = _wppaCurrentIndex[mocc] - 1;
	if (_wppaNextIndex[mocc] < 0) _wppaNextIndex[mocc] = _wppaSlides[mocc].length - 1;
	jQuery(".arrow-"+mocc).stop().fadeTo(400,0.2);
	_wppaNextSlide(mocc, 0);
}

function _wppaGoto(mocc, idx) {
	_wppaLog('Goto', mocc);
	
	_wppaToTheSame = (_wppaNextIndex[mocc] == idx);
	_wppaNextIndex[mocc] = idx;
	jQuery(".arrow-"+mocc).stop().fadeTo(400,0.2);
	_wppaNextSlide(mocc, 0);
}

function _wppaStart(mocc, idx) {
	_wppaLog('Start', mocc);
	
	if ( idx != -1 ) {	// Init still at index idx
		if (document.getElementById('startstop-'+mocc)) document.getElementById('startstop-'+mocc).innerHTML=wppaStart+' '+wppaSlideShow; 
		if (document.getElementById('speed0-'+mocc)) document.getElementById('speed0-'+mocc).style.visibility="hidden";
		if (document.getElementById('speed1-'+mocc)) document.getElementById('speed1-'+mocc).style.visibility="hidden";
		_wppaNextIndex[mocc] = idx;
		_wppaCurrentIndex[mocc] = idx;
		_wppaNextSlide(mocc, 0);
		_wppaShowMetaData(mocc, 'show');
	}
	else {				// Init running
       _wppaSlideShowRuns[mocc] = true;
        _wppaNextSlide(mocc, 0);
		if (document.getElementById('startstop-'+mocc)) document.getElementById('startstop-'+mocc).innerHTML=wppaStop;
		jQuery('#prev-arrow-'+mocc).css('visibility', 'hidden');
		jQuery('#next-arrow-'+mocc).css('visibility', 'hidden');
		jQuery('#prev-film-arrow-'+mocc).css('visibility', 'hidden');
		jQuery('#next-film-arrow-'+mocc).css('visibility', 'hidden');
		jQuery('#p-a-'+mocc).css('visibility', 'hidden');
		jQuery('#n-a-'+mocc).css('visibility', 'hidden');
		jQuery('#speed0-'+mocc).css('visibility', 'visible');
		jQuery('#speed1-'+mocc).css('visibility', 'visible');
		_wppaShowMetaData(mocc, 'hide');	
		jQuery('#bc-pname-'+mocc).html(wppaSlideShow);
    }
	
	// Both cases:
	_wppaSetRatingDisplay(mocc);
}

function _wppaStop(mocc) {
	_wppaLog('Stop', mocc);
	
    _wppaSlideShowRuns[mocc] = false;
    document.getElementById('startstop-'+mocc).innerHTML=wppaStart+' '+wppaSlideShow;  
	jQuery('#prev-arrow-'+mocc).css('visibility', 'visible');
	jQuery('#next-arrow-'+mocc).css('visibility', 'visible');
	jQuery('#prev-film-arrow-'+mocc).css('visibility', 'visible');
	jQuery('#next-film-arrow-'+mocc).css('visibility', 'visible');
	jQuery('#p-a-'+mocc).css('visibility', 'visible');
	jQuery('#n-a-'+mocc).css('visibility', 'visible');
	jQuery('#speed0-'+mocc).css('visibility', 'hidden');
	jQuery('#speed1-'+mocc).css('visibility', 'hidden');
	_wppaShowMetaData(mocc, 'show');
	jQuery('#bc-pname-'+mocc).html(_wppaNames[mocc][_wppaCurrentIndex[mocc]]);
}

function _wppaSpeed(mocc, faster) {
	_wppaLog('Speed', 0);
	
    if (faster) {
        if (_wppaTimeOut[mocc] > 500) _wppaTimeOut[mocc] /= 1.5;
    }
    else {
        if (_wppaTimeOut[mocc] < 60000) _wppaTimeOut[mocc] *= 1.5;
    }
}

function _wppaLoadSpinner(mocc) {
	_wppaLog('LoadSpinner', mocc);
	
	var top;
	var lft;
	var elm;
	
	elm = document.getElementById('slide_frame-'+mocc);
	top = parseInt(elm.style.height);
	if (top > 0) {
		top = parseInt(parseInt(top/2) - 4)+'px';
	}
	else {
		top = parseInt(elm.style.minHeight);
		if (top > 0) {
			top = parseInt(parseInt(top/2) - 4)+'px';
		}
		else top = '150px';
	}
	lft = parseInt((parseInt(elm.style.width) / 2) - 4)+'px';

	document.getElementById('spinner-'+mocc).style.top = top;
	document.getElementById('spinner-'+mocc).style.left = lft;
	document.getElementById('spinner-'+mocc).innerHTML = '<img id="spinnerimg-'+mocc+'" src="'+wppaImageDirectory+'wpspin.gif" />';
}

function _wppaUnloadSpinner(mocc) {
	_wppaLog('UnloadSpinner', mocc);

	if (document.getElementById('spinnerimg-'+mocc)) {
		document.getElementById('spinnerimg-'+mocc).src = '';
		document.getElementById('spinner-'+mocc).innerHTML = '';
	}
}

function _wppaDoAutocol(mocc) {
	_wppaLog('DoAutocol', mocc);
	var w;
	var h;
	if (!wppaAutoColumnWidth) return;
	
	w = document.getElementById('wppa-container-1').parentNode.clientWidth;
	
	jQuery(".wppa-container").css('width',w);
	jQuery(".theimg").css('width',w);
	jQuery(".thumbnail-area").css('width',w - wppaThumbnailAreaDelta);
	wppaFilmStripLength[mocc] = w - wppaFilmStripAreaDelta[mocc];
	jQuery(".filmwindow").css('width',wppaFilmStripLength[mocc]);

	jQuery(".wppa-text-frame").css('width',w - wppaTextFrameDelta);
	jQuery(".wppa-cover-box").css('width',w - wppaBoxDelta);
	
	// See if there are slideframe images
	h = 0;
	if (mocc > 0) {
		if (document.getElementById('theimg0-'+mocc)) {
			h = document.getElementById('theimg0-'+mocc).clientHeight;
		}
		if (h == 0) {
			if (document.getElementById('theimg1-'+mocc)) {
				h = document.getElementById('theimg1-'+mocc).clientHeight;
			}
		}
		// Set slideframe height to the height found (if any)
		if (h > 0) {
			jQuery("#slide_frame-"+mocc).css('height',h);
		}
		else {		// Try again later
			setTimeout('_wppaDoAutocol('+mocc+')', wppaAnimationSpeed);
		}
	}
}

function _wppaCheckRewind(mocc) {
	_wppaLog('CheckRewind', mocc);

	var n_images;
	var n_diff;
	var l_substrate;
	var x_marg;
	
	if (!document.getElementById('wppa-filmstrip-'+mocc)) return; // There is no filmstrip
	
	n_diff = Math.abs(_wppaCurrentIndex[mocc] - _wppaNextIndex[mocc]);
	if (n_diff < 2) return;
	
	var n_images = wppaFilmStripLength[mocc] / wppaThumbnailPitch[mocc];
	
	if (n_diff >= ((n_images + 1) / 2)) {
		l_substrate = wppaThumbnailPitch[mocc] * _wppaSlides[mocc].length;
		if (wppaFilmShowGlue) l_substrate += (2 + 2 * wppaFilmStripMargin[mocc]);
		
		x_marg = parseInt(jQuery('#wppa-filmstrip-'+mocc).css('margin-left'));

		if (_wppaNextIndex[mocc] > _wppaCurrentIndex[mocc]) {
			x_marg -= l_substrate;
		}
		else {
			x_marg += l_substrate;
		}

		jQuery('#wppa-filmstrip-'+mocc).css('margin-left', x_marg+'px');
	}
}

function _wppaSetRatingDisplay(mocc) {
	_wppaLog('setRatingDisplay', mocc);

var idx, avg, myr;
	if (!document.getElementById('wppa-rating-'+mocc)) return; 	// No rating bar
//	if (_wppaSlideShowRuns[mocc] == -1) return; 					// Stop in progress, do nothing now
	
	avg = _wppaPhotoAverages[mocc][_wppaCurrentIndex[mocc]];
	_wppaSetRd(mocc, avg, '#wppa-avg-');
	
	if (wppaUserName != '') {									// user logged in
		myr = _wppaPhotoMyRating[mocc][_wppaCurrentIndex[mocc]];
		_wppaSetRd(mocc, myr, '#wppa-rate-');
	}
}
		
function _wppaSetRd(mocc, avg, where) {
	_wppaLog('SetRd', mocc);
		
	var idx1 = parseInt(avg);
	var idx2 = idx1 + 1;
	var frac = avg - idx1;
	var opac = 0.2 + frac * 0.8;
	var ilow = 1;
	var ihigh = 5;
	
	for (idx=ilow;idx<=ihigh;idx++) {
		if (idx <= idx1) {
			jQuery(where+mocc+'-'+idx).stop().fadeTo(100, 1.0);
		}
		else if (idx == idx2) {
			jQuery(where+mocc+'-'+idx).stop().fadeTo(100, opac); 
		}
		else {
			jQuery(where+mocc+'-'+idx).stop().fadeTo(100, 0.2);
		}
	}
}

function _wppaFollowMe(mocc, idx) {
	_wppaLog('FollowMe', mocc);

	if (_wppaSlideShowRuns[mocc]) return;				// Do not rate on a running show, what only works properly in Firefox								

	if (_wppaPhotoMyRating[mocc][_wppaCurrentIndex[mocc]] != 0 && wppaRatingOnce) return;	// Already rated
	if (_wppaVoteInProgress) return;
	_wppaSetRd(mocc, idx, '#wppa-rate-');
}

function _wppaLeaveMe(mocc, idx) {
	_wppaLog('LeaveMe', mocc);

	if (_wppaSlideShowRuns[mocc]) return;				// Do not rate on a running show, what only works properly in Firefox	

	if (_wppaPhotoMyRating[mocc][_wppaCurrentIndex[mocc]] != 0 && wppaRatingOnce) return;	// Already rated
	if (_wppaVoteInProgress) return;
	_wppaSetRd(mocc, _wppaPhotoMyRating[mocc][_wppaCurrentIndex[mocc]], '#wppa-rate-');
}

function _wppaRateIt(mocc, value) {
	_wppaLog('RateIt', mocc);

	var photoid = _wppaPhotoIds[mocc][_wppaCurrentIndex[mocc]];
	var oldval = _wppaPhotoMyRating[mocc][_wppaCurrentIndex[mocc]];
	var url = _wppaVoteReturnUrl[mocc][_wppaCurrentIndex[mocc]]+'&wpparating='+value;
	
	if (document.getElementById('wppa_nonce')) url += '&wppa_nonce='+document.getElementById('wppa_nonce').value;

	if (oldval != 0 && wppaRatingOnce) return;							// Already rated, and once allowed only
	if (_wppaSlideShowRuns[mocc]) return;										
																			
	_wppaVoteInProgress = true;											// Keeps opacity as it is now
	
	document.getElementById('wppa-rate-'+mocc+'-'+value).src = wppaImageDirectory+'tick.png';				// Set icon
	jQuery('#wppa-rate-'+mocc+'-'+value).stop().fadeTo(100, 1.0);
	
	setTimeout('_wppaGo("'+url+'")', 200);	// 200 ms to display tick
}

function _wppaValidateComment(mocc) {
	_wppaLog('ValidateComment', mocc);

	var photoid = _wppaPhotoIds[mocc][_wppaCurrentIndex[mocc]];
	
	// Process name
	var name = document.getElementById('wppacomname-'+mocc).value;
	if (name.length<1) {
		alert(wppaPleaseName);
		return false;
	}
	
	// Process email address
	var email = document.getElementById('wppacomemail-'+mocc).value;
	var atpos=email.indexOf("@");
	var dotpos=email.lastIndexOf(".");
	if (atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length) {
		alert(wppaPleaseEmail);
		return false;
	}
	
	// Process comment
	var text = document.getElementById('wppacomment-'+mocc).value;
	if (text.length<1) {
		alert(wppaPleaseComment);
		return false;
	}
	
	return true;
}

function _wppaGo(url) {
	_wppaLog('Go', 0);
	
	document.location = url;	// Go!
}

function _wppaBbb(mocc,where,act) {
	_wppaLog('Bbb', mocc);
	
	if (_wppaSlideShowRuns[mocc]) return;
	
	var elm = '#bbb-'+mocc+'-'+where;
	switch (act) {
		case 'show':
			jQuery(elm).stop().fadeTo(100, 0.2);
			if (where == 'l') jQuery(elm).attr('title', wppaPreviousPhoto);
			if (where == 'r') jQuery(elm).attr('title', wppaNextPhoto);
			jQuery('.bbb-'+mocc).css('cursor', 'pointer');
			break;
		case 'hide':
			jQuery(elm).stop().fadeTo(400, 0);
			jQuery('.bbb-'+mocc).removeAttr('title');
			jQuery('.bbb-'+mocc).css('cursor', 'default');
			break;
		case 'click':
			if (where == 'l') _wppaPrev(mocc);
			if (where == 'r') _wppaNext(mocc);
			break;
		default:
			alert('Unimplemented instruction: '+act+' on: '+elm);
	}
}

function _wppaShowMetaData(mocc, key) {
	_wppaLog('ShowMetaData', mocc);
	
	// What to do when the slideshow is NOT running
	if ( ! _wppaSlideShowRuns[mocc] ) {	
		if (key == 'show') {			// Show
			// Show existing comments
			jQuery('#wppacommentstable-'+mocc).css('visibility', 'visible');
			// Show the input form table
			jQuery('#wppacommenttable-'+mocc).css('visibility', 'visible');
			// Show the comment footer
			jQuery('#wppacommentfooter-'+mocc).css('visibility', 'collapse');
			// Fade the browse arrows in
			jQuery('#prev-film-arrow-'+mocc).fadeIn(100);
			jQuery('#next-film-arrow-'+mocc).fadeIn(100);

			jQuery(".arrow-"+mocc).stop().fadeTo(400,1);
		}
		else {							// Hide
			// Hide existing comments
			jQuery('#wppacommentstable-'+mocc).css('visibility', 'collapse');
			// Hide the input form table
			jQuery('#wppacommenttable-'+mocc).css('visibility', 'collapse');
			// Hide the comment footer
			jQuery('#wppacommentfooter-'+mocc).css('visibility', 'visible');
			// Fade the browse arrows out
			jQuery('#prev-film-arrow-'+mocc).fadeOut(400);
			jQuery('#next-film-arrow-'+mocc).fadeOut(400);
		}
	}
	// What to do when the slideshow is running
	else {	// Slideshow is running
	}
	
	// What to do always, independant of slideshow is running
	if (key == 'show') {
		// Show title and description
		jQuery("#imagedesc-"+mocc).css('visibility', 'visible');
		jQuery("#imagetitle-"+mocc).css('visibility', 'visible');
		// Show comments section
		jQuery("#comments-"+mocc).css('visibility', 'visible');
		// Display counter
		jQuery("#counter-"+mocc).css('visibility', 'visible');
	}
	else {
		// Hide title and description
		jQuery("#imagedesc-"+mocc).css('visibility', 'hidden'); 
		jQuery("#imagetitle-"+mocc).css('visibility', 'hidden');
		// Hide comments section
		jQuery("#comments-"+mocc).html('&nbsp;&nbsp;');	
		// Hide counter	
		jQuery("#counter-"+mocc).css('visibility', 'hidden');
	}
}

function _wppaLog(text, mocc) {
	if ( ! document.getElementById('wppa-debug-'+mocc) ) return;
	var elm = document.getElementById('wppa-debug-'+mocc);
	var old_html = elm.innerHTML;
	var html = '<br>[wppa js] '+mocc+' run=';
	if ( _wppaSlideShowRuns[mocc] ) html += 'yes'; else html += 'no ';
	html += ' busy=';
	if ( _wppaIsBusy[mocc] ) html += 'yes'; else html += 'no ';
	html += ' tp=';
	if ( _wppaTogglePending[mocc] ) html += 'yes'; else html += 'no ';
	html += ' '+text;
	
	html += old_html;
	if ( html.length > 1000 ) html = html.substr(0, 1000);
	elm.innerHTML = html;	// prepend logmessage
}
