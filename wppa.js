// wppa.js
//
// conatins slideshow, theme, ajax and lightbox code
//
// Version 5.4.18

// Part 1: Slideshow
//
// Slide show variables and functions
// Vars. The vars that have a name that starts with an underscore is an internal var
// The vars without leading underscore are 'external' and get a value from html

// 'External' variables ( public )
var wppaVersion = '0';
var wppaDebug = false;
var wppaFullValign = [];
var wppaFullHalign = [];
var wppaFullFrameDelta = [];
var wppaAnimationSpeed;
var wppaImageDirectory;
var wppaAutoColumnWidth = [];
var wppaAutoColumnFrac = [];
var wppaThumbnailAreaDelta;
var wppaSlideShowTimeOut = 2500;
var wppaFadeInAfterFadeOut = false;
var wppaTextFrameDelta = 0;
var wppaBoxDelta = 0;
var wppaPreambule;
var wppaHideWhenEmpty = false;
var wppaThumbnailPitch = [];
var wppaFilmStripLength = [];
var wppaFilmStripMargin = [];
var wppaFilmStripAreaDelta = [];
var wppaFilmShowGlue = false;
var wppaIsMini = [];
var wppaPortraitOnly = [];
var wppaSlideShow;				// = 'Slideshow' or its translation
var wppaPhoto;					// = 'Photo' or its translation
var wppaOf;						// = 'of' or its translation
var wppaNextPhoto;				// = 'Next photo' or its translation
var wppaPreviousPhoto;			// = 'Previous photo' or its translation
var wppaSlower;
var wppaFaster;
var wppaNextP;
var wppaPrevP;
var wppaAvgRating;
var wppaMyRating;
var wppaAvgRat;
var wppaMyRat;
var wppaDislikeMsg;
var wppaShowDislikeCount = false;
var wppaNoDislikes = 'no dislikes';
var wppa1Dislike = '1 dislike';
var wppaDislikes = 'dislikes';
var wppaIncludingMine = 'including mine';
var wppaMiniTreshold = 300;
var wppaStart = 'Start';		// defaults
var wppaStop = 'Stop';			//
var wppaPleaseName;
var wppaPleaseEmail;
var wppaPleaseComment;
var wppaRatingOnce = true;
var wppaBGcolorNumbar = 'transparent';
var wppaBcolorNumbar = 'transparent';
var wppaBGcolorNumbarActive = 'transparent';
var wppaBcolorNumbarActive = 'transparent';
var wppaFontFamilyNumbar = '';
var wppaFontSizeNumbar = '';
var wppaFontColorNumbar = '';
var wppaFontWeightNumbar = '';
var wppaFontFamilyNumbarActive = '';
var wppaFontSizeNumbarActive = '';
var wppaFontColorNumbarActive = '';
var wppaFontWeightNumbarActive = '';
var wppaNumbarMax = '10';
var wppaAjaxUrl = '';
var wppaLang = '';
var wppaNextOnCallback = false;
var wppaRatingUseAjax = false;
var wppaStarOpacity = 0.2;
var wppaSlideWrap = true;
var wppaLightBox = [];
var wppaEmailRequired = true;
var wppaSlideBorderWidth = 0;
var wppaSlideInitRunning = [];
var wppaAnimationType = 'fadeover';
var wppaSlidePause = [];
var wppaSlideBlank = [];
var wppaRatingMax = 5;
var wppaRatingDisplayType = 'graphic';
var wppaRatingPrec = 2;
var wppaFilmPageSize = [];
var wppaAspectRatio = [];
var wppaFullSize = [];
var wppaStretch = false;
var wppaThumbSpaceAuto = false;
var wppaMinThumbSpace = 4;
var wppaMagnifierCursor = '';
var wppaArtMonkyLink = 'none';
var wppaAutoOpenComments = false;
var wppaUpdateAddressLine = false;
var wppaFilmThumbTitle = '';
var wppaUploadUrl = '';
var wppaVoteForMe = '';
var wppaVotedForMe = '';
var wppaSlideSwipe = true;
var wppaLightboxSingle = [];
var wppaMaxCoverWidth = 300;	// For responsive multicolumn covers
var wppaDownLoad = 'Download';
var wppaSiteUrl = '';
var wppaWppaUrl = '';
var wppaIncludeUrl = '';
var wppaSlideToFullpopup = false; 
var wppaComAltSize = 75;
var wppaBumpViewCount = true;
var wppaFotomoto = false;
var wppaArtMonkeyButton = true;
var wppaShortQargs = false;
var wppaOvlHires = false;

// 'Internal' variables ( private )
var _wppaId = [];
var _wppaAvg = [];
var _wppaDisc = [];
var _wppaMyr = [];
var _wppaVRU = [];
var _wppaLinkUrl = [];
var _wppaLinkTitle = [];
var _wppaLinkTarget = [];
var _wppaCommentHtml = [];
var _wppaIptcHtml = [];
var _wppaExifHtml = [];
var _wppaToTheSame = false;
var _wppaSlides = [];
var _wppaNames = [];
var _wppaFullNames = [];
var _wppaDsc = [];
var _wppaOgDsc = [];
var _wppaCurIdx = [];
var _wppaNxtIdx = [];
var _wppaTimeOut = [];
var _wppaSSRuns = [];
var _wppaFg = [];
var _wppaTP = [];
var _wppaIsBusy = [];
var _wppaFirst = [];
var _wppaVIP = false;
var _wppaTextDelay;
var _wppaUrl = [];
var _wppaLastVote = 0;
var _wppaSkipRated = [];
var _wppaLbTitle = [];
var _wppaStateCount = 0;
var _wppaDidGoto = [];
var wppaTopMoc = 0;
var wppaColWidth = [];
var _wppaShareUrl = [];
var _wppaShareHtml = [];
var _wppaFilmNoMove = [];
var wppaShareHideWhenRunning = false;
var _wppaHiresUrl = [];
var wppaFotomotoHideWhenRunning = false;
var wppaFotomotoMinWidth = 400;
var wppaPhotoView = [];
var wppaCommentRequiredAfterVote = true;
var _wppaIsVideo = [];
var _wppaVideoHtml = [];

var __wppaOverruleRun = false;

// Init at dom ready
jQuery( document ).ready(function() {
	var anyAutocol = false;

	// Check for occurrences that are responsive
	for ( mocc = 1; mocc <= wppaTopMoc; mocc++ ) {
		if ( wppaAutoColumnWidth[mocc] ) {
			wppaColWidth[mocc] = 0;
			_wppaDoAutocol( mocc );
			anyAutocol = true;
		}
	}	
	
	// Misc. init
	_wppaTextDelay = wppaAnimationSpeed;
	if ( wppaFadeInAfterFadeOut ) _wppaTextDelay *= 2;
	
	// Install resize handler
	if ( anyAutocol ) {
		jQuery( window ).resize(function() {
			for ( mocc = 1; mocc <= wppaTopMoc; mocc++ ) {
				if ( wppaAutoColumnWidth[mocc] ) {
					wppaColWidth[mocc] = 0;
					_wppaDoAutocol( mocc );
				}
			}
		}); 
	}
});

// First the external entrypoints that may be called directly from HTML

// This is an entrypoint to load the slide data
function wppaStoreSlideInfo( 
							mocc, 		// The occurrance of a wppa invocation 
										// ( php: $wppa['maseter_occur'] )
							id, 		// The index in the slide array
							url, 		// The url to the fs image file
							size, 
							width, 
							height, 
							fullname, 
							name, 
							desc, 
							photoid, 		// The photo id
							avgrat, 		// Average rating
							discount, 		// Dislike count
							myrat, 			// My rating
							rateurl, 		// The rating url
							linkurl, 
							linktitle, 
							linktarget, 
							iwtimeout, 
							commenthtml, 	// The html code for the comment box
							iptchtml, 
							exifhtml, 
							lbtitle, 		// Lightbox subtext
							shareurl, 
							smhtml, 
							ogdsc, 
							hiresurl, 		// The url to the hi res ( source ) image file
							videohtml 		// The html for the video, or ''
							) {

	var cursor;

	desc = wppaRepairScriptTags( desc );

	if ( ! _wppaSlides[mocc] || '0' == id ) {	// First or next page
		_wppaSlides[mocc] = [];
		_wppaNames[mocc] = [];
		_wppaFullNames[mocc] = [];
		_wppaDsc[mocc] = [];
		_wppaOgDsc[mocc] = [];
		_wppaCurIdx[mocc] = -1;
		_wppaNxtIdx[mocc] = 0;
		if ( parseInt( iwtimeout ) > 0 ) _wppaTimeOut[mocc] = parseInt( iwtimeout );
		else _wppaTimeOut[mocc] = wppaSlideShowTimeOut;
		_wppaSSRuns[mocc] = false;
		_wppaTP[mocc] = -2;	// -2 means NO, index for _wppaStartStop otherwise
		_wppaFg[mocc] = 0;
		_wppaIsBusy[mocc] = false;
		_wppaFirst[mocc] = true;
		_wppaId[mocc] = [];
		_wppaAvg[mocc] = [];
		_wppaDisc[mocc] = [];
		_wppaMyr[mocc] = [];
		_wppaVRU[mocc] = [];
		_wppaLinkUrl[mocc] = []; // linkurl;
		_wppaLinkTitle[mocc] = []; // linktitle;
		_wppaLinkTarget[mocc] = [];
		_wppaCommentHtml[mocc] = [];
		_wppaIptcHtml[mocc] = [];
		_wppaExifHtml[mocc] = [];
		_wppaUrl[mocc] = [];
		_wppaSkipRated[mocc] = false;
		_wppaLbTitle[mocc] = [];
		_wppaDidGoto[mocc] = false;
		wppaSlidePause[mocc] = false;
		_wppaShareUrl[mocc] = [];
		_wppaShareHtml[mocc] = [];
		_wppaFilmNoMove[mocc] = false;
		_wppaHiresUrl[mocc] = [];
		_wppaIsVideo[mocc] = [];
		_wppaVideoHtml[mocc] = [];
	}
	
	// Cursor
	cursor = 'default';
	if ( linkurl != '' ) {
		cursor = 'pointer';
	}
	else if ( 'wppa' == wppaLightBox[mocc] ) {
		cursor =  'url( '+wppaImageDirectory+wppaMagnifierCursor+' ),pointer';
	}

	// Is it a video?
	_wppaIsVideo[mocc][id] = ( '' != videohtml );
	
	// Fill _wppaSlides[mocc][id]
	if ( _wppaIsVideo[mocc][id] ) {
		_wppaSlides[mocc][id] = ' alt="' + wppaTrimAlt( name ) + '" class="theimg theimg-'+mocc+' big" ';
	}
	else {
		_wppaSlides[mocc][id] = ' src="' + url + '" alt="' + wppaTrimAlt( name ) + '" class="theimg theimg-'+mocc+' big" ';
	}
	
	// Add swipe
	if ( wppaSlideSwipe ) {
		_wppaSlides[mocc][id] += 'ontouchstart="wppaTouchStart( event, this.id, '+mocc+' );"  ontouchend="wppaTouchEnd( event );" ontouchmove="wppaTouchMove( event );" ontouchcancel="wppaTouchCancel( event );" ';
	}
	
	// Add 'old' width and height only for non-auto
	if ( ! wppaAutoColumnWidth[mocc] ) _wppaSlides[mocc][id] += 'width="' + width + '" height="' + height + '" ';
	if ( _wppaIsVideo[mocc][id] ) {
		var controls;
		controls = 'wppa' == wppaLightBox[mocc] ? '' : 'controls';
		_wppaSlides[mocc][id] += 'style="' + size + '; cursor:'+cursor+'; display:none;" '+controls+'>'+videohtml+'</video>';
	}
	else {
		_wppaSlides[mocc][id] += 'style="' + size + '; cursor:'+cursor+'; display:none;">';
	}
	
    _wppaFullNames[mocc][id] = fullname;
    _wppaNames[mocc][id] = name;
    _wppaDsc[mocc][id] = desc;
	_wppaOgDsc[mocc][id] = ogdsc;
	_wppaId[mocc][id] = photoid;		// reqd for rating and comment and monkey and registering views
	_wppaAvg[mocc][id] = avgrat;		// avg ratig value
	_wppaDisc[mocc][id] = discount;		// Dislike count
	_wppaMyr[mocc][id] = myrat;			// my rating
	_wppaVRU[mocc][id] = rateurl;		// url that performs the vote and returns to the page
	_wppaLinkUrl[mocc][id] = linkurl;
	_wppaLinkTitle[mocc][id] = linktitle;
	
	if ( linktarget != '' ) {
		_wppaLinkTarget[mocc][id] = linktarget;
	}
	else if ( wppaSlideBlank[mocc] ) {
		_wppaLinkTarget[mocc][id] = '_blank';
	}
	else {
		_wppaLinkTarget[mocc][id] = '_self';
	}
	
	_wppaCommentHtml[mocc][id] = commenthtml;
	_wppaIptcHtml[mocc][id] = iptchtml;
	_wppaExifHtml[mocc][id] = exifhtml;
	_wppaUrl[mocc][id] = /* wppaUploadUrl + */ url;		// Image url
	_wppaLbTitle[mocc][id] = wppaRepairScriptTags( lbtitle );
	_wppaShareUrl[mocc][id] = shareurl;
	_wppaShareHtml[mocc][id] = wppaRepairScriptTags( smhtml );
	_wppaHiresUrl[mocc][id] = hiresurl;
	_wppaVideoHtml[mocc][id] = videohtml;
}

// These functions check the validity and store the users request to be executed later if busy and if applicable.
function wppaSpeed( mocc, faster ) {
	if ( _wppaSSRuns[mocc] ) {
		_wppaSpeed( mocc, faster );
	}
}

function wppaStopShow( mocc ) {
	if ( _wppaSSRuns[mocc] ) {
		_wppaStop( mocc );
	}
}

// The application contains various togglers for start/stop
// The busy flag will be reset at the end of the NextSlide procedure
function wppaStartStop( mocc, index ) {
	if ( _wppaIsBusy[mocc] ) {
		// Remember there is a toggle pending
		_wppaTP[mocc] = index;		
	}
	else {
		if ( _wppaSSRuns[mocc] ) {		
			// Stop it
			_wppaStop( mocc );
		}
		else {
			// Start it
			_wppaStart( mocc, index );
		}
	}
}

function wppaBbb( mocc, where, act ) {
	if ( ! _wppaSSRuns[mocc] ) {
		// Big Browsing Buttons only work when stopped
		_wppaBbb( mocc, where, act );
	}
}

function wppaUbb( mocc, where, act ) {
	if ( ! _wppaSSRuns[mocc] ) {
		// Ugli Browsing Buttons only work when stopped
		_wppaUbb( mocc, where, act );
	}
}

function wppaRateIt( mocc, value ) {
	_wppaRateIt( mocc, value );
}

function wppaPrev( mocc ) {
	_wppaDidGoto[mocc] = true;
	if ( ! _wppaSSRuns[mocc] ) {
		_wppaPrev( mocc );
	}
}

function wppaPrevN( mocc, n ) {
	_wppaDidGoto[mocc] = true;
	if ( ! _wppaSSRuns[mocc] ) {
		_wppaPrevN( mocc, n );
	}
}

function wppaNext( mocc ) {
	_wppaDidGoto[mocc] = true;
	if ( ! _wppaSSRuns[mocc] ) {
		_wppaNext( mocc );
	}
}

function wppaNextN( mocc, n ) {
	_wppaDidGoto[mocc] = true;
	if ( ! _wppaSSRuns[mocc] ) {
		_wppaNextN( mocc, n );
	}
}

function wppaFollowMe( mocc, idx ) {
	if ( ! _wppaSSRuns[mocc] ) {
		_wppaFollowMe( mocc, idx );
	}
}

function wppaLeaveMe( mocc, idx ) {
	if ( ! _wppaSSRuns[mocc] ) {
		_wppaLeaveMe( mocc, idx );
	}
}

function wppaGoto( mocc, idx ) {
	// Goto the requested slide if the slideshow stopped
	_wppaDidGoto[mocc] = true;
	if ( ! _wppaSSRuns[mocc] ) {
		_wppaGoto( mocc, idx );
	}
}

function wppaGotoFilmNoMove( mocc, idx ) {
	_wppaDidGoto[mocc] = true;
	if ( ! _wppaSSRuns[mocc] ) {
		_wppaFilmNoMove[mocc] = true;
		_wppaGoto( mocc, idx );
	}	
}

function wppaGotoKeepState( mocc, idx ) {
	// Goto the requested slide and preserve running state
	_wppaDidGoto[mocc] = true;
	_wppaGotoKeepState( mocc, idx );
}

function _wppaGotoKeepState( mocc, idx ) {	
	if ( _wppaSSRuns[mocc] ) {
		_wppaGotoRunning( mocc,idx );
	}
	else {
		_wppaGoto( mocc,idx );
	}
}

function wppaGotoRunning( mocc, idx ) {
	// Goto the requested slide and start running
	_wppaDidGoto[mocc] = true;
	_wppaGotoRunning( mocc, idx );
}

function wppaValidateComment( mocc ) {
	return _wppaValidateComment( mocc );
}

function _wppaNextSlide( mocc, mode ) {

	var fg = _wppaFg[mocc];
	var bg = 1 - fg;

	// Paused??
	if ( 'auto' == mode ) {
		if ( wppaSlidePause[mocc] ) {
			jQuery( '#theimg'+fg+'-'+mocc ).attr( "title", wppaSlidePause[mocc] );
			setTimeout( '_wppaNextSlide( '+mocc+', "auto" )', 250 );	// Retry after 250 ms.
			return;
		}
	}
	// Kill an old timed request, while stopped
	if ( ! _wppaSSRuns[mocc] && 'auto' == mode ) return; 
	// Empty slideshow?
	if ( ! _wppaSlides[mocc] ) return;
	// Do not animate single image
	if ( _wppaSlides[mocc].length < 2 && ! _wppaFirst[mocc] ) return; 
	// Reset request?
	if ( ! _wppaSSRuns[mocc] && 'reset' == mode ) {
		_wppaSSRuns[mocc] = true;
		__wppaOverruleRun = false;
	}

	// No longer busy voting
	_wppaVIP = false;
	
	// Set the busy flag
	_wppaIsBusy[mocc] = true;

	// Hide metadata while changing image
	if ( _wppaSSRuns[mocc] ) _wppaShowMetaData( mocc, 'hide' );
	
	// Find index of next slide if in auto mode and not stop in progress
	if ( _wppaSSRuns[mocc] ) {
		_wppaNxtIdx[mocc] = _wppaCurIdx[mocc] + 1;
		if ( _wppaNxtIdx[mocc] == _wppaSlides[mocc].length ) _wppaNxtIdx[mocc] = 0;
	}

	// Update geo if any
	// GPX Plugin
	jQuery( '#geodiv-'+mocc+'-'+_wppaId[mocc][_wppaCurIdx[mocc]] ).css( { display: 'none' });
	jQuery( '#geodiv-'+mocc+'-'+_wppaId[mocc][_wppaNxtIdx[mocc]] ).css( { display: '' });
	// WPPA+ Native
	if ( typeof( _wppaLat ) != 'undefined' ) {
		if ( _wppaLat[mocc] ) {
			if ( _wppaLat[mocc][_wppaId[mocc][_wppaNxtIdx[mocc]]] ) {
				jQuery( '#map-canvas-'+mocc ).css( 'display', '' );
				wppaGeoInit( mocc, _wppaLat[mocc][_wppaId[mocc][_wppaNxtIdx[mocc]]], _wppaLon[mocc][_wppaId[mocc][_wppaNxtIdx[mocc]]] );
			}
			else jQuery( '#map-canvas-'+mocc ).css( 'display', 'none' );
		}
		else jQuery( '#map-canvas-'+mocc ).css( 'display', 'none' );
	}
	else jQuery( '#map-canvas-'+mocc ).css( 'display', 'none' );
	
	// Set numbar backgrounds and fonts
	jQuery( '[id^=wppa-numbar-' + mocc + '-]' ).css( {	backgroundColor: wppaBGcolorNumbar, 
													borderColor: wppaBcolorNumbar,
													fontFamily: wppaFontFamilyNumbar,
													fontSize: wppaFontSizeNumbar,
													color: wppaFontColorNumbar,
													fontWeight: wppaFontWeightNumbar
													});

	jQuery( "#wppa-numbar-" + mocc + "-" + _wppaNxtIdx[mocc] ).css( {	backgroundColor: wppaBGcolorNumbarActive, 
																	borderColor: wppaBcolorNumbarActive,
																	fontFamily: wppaFontFamilyNumbarActive,
																	fontSize: wppaFontSizeNumbarActive,
																	color: wppaFontColorNumbarActive,
																	fontWeight: wppaFontWeightNumbarActive
																	});

	// too many? all dots except current
	if ( _wppaSlides[mocc].length > wppaNumbarMax ) {
		jQuery( '[id^=wppa-numbar-' + mocc + '-]' ).html( ' . ' );
		jQuery( "#wppa-numbar-" + mocc + "-" + _wppaNxtIdx[mocc] ).html( ' ' + ( _wppaNxtIdx[mocc]+1 ) + ' ' );
	}
	
    // first:
    if ( _wppaFirst[mocc] ) {
	    if ( _wppaCurIdx[mocc] != -1 ) {
			wppaMakeTheSlideHtml( mocc, '0', _wppaCurIdx[mocc] );
		}
		wppaMakeTheSlideHtml( mocc, '1', _wppaNxtIdx[mocc] );
	    
		// Display name, description and comments
		jQuery( "#imagedesc-"+mocc ).html( _wppaDsc[mocc][_wppaCurIdx[mocc]] );
		jQuery( "#imagetitle-"+mocc ).html( wppaMakeNameHtml( mocc ) );
		jQuery( "#wppa-comments-"+mocc ).html( _wppaCommentHtml[mocc][_wppaCurIdx[mocc]] );
		jQuery( "#iptc-"+mocc ).html( _wppaIptcHtml[mocc][_wppaCurIdx[mocc]] );
		jQuery( "#exif-"+mocc ).html( _wppaExifHtml[mocc][_wppaCurIdx[mocc]] );
		
		// Display counter and arrow texts
		if ( wppaIsMini[mocc] || wppaGetContainerWidth( mocc ) < wppaMiniTreshold ) {
			jQuery( '#prev-arrow-'+mocc ).html( '&laquo;&nbsp;'+wppaPrevP );
			jQuery( '#next-arrow-'+mocc ).html( wppaNextP+'&nbsp;&raquo;' );
			jQuery( '#wppa-avg-rat-'+mocc ).html( wppaAvgRat );
			jQuery( '#wppa-my-rat-'+mocc ).html( wppaMyRat );
		}
		else {
			jQuery( '#prev-arrow-'+mocc ).html( '&laquo;&nbsp;'+wppaPreviousPhoto );
			jQuery( '#next-arrow-'+mocc ).html( wppaNextPhoto+'&nbsp;&raquo;' );
			jQuery( '#wppa-avg-rat-'+mocc ).html( wppaAvgRating );
			jQuery( '#wppa-my-rat-'+mocc ).html( wppaMyRating );
		}
	}
    // end first
    else {    	// load next img ( backg )
		wppaMakeTheSlideHtml( mocc, bg, _wppaNxtIdx[mocc] );
    }
	
	_wppaLoadSpinner( mocc );
	
	_wppaFirst[mocc] = false;
	
	// See if the filmstrip needs wrap around before shifting to the right location
	_wppaCheckRewind( mocc );

////	if ( wppaAutoColumnWidth[mocc] ) _wppaDoAutocol( mocc );
				wppaColWidth[mocc] = 0;	// force a recalc
				_wppaDoAutocol( mocc );

	// Give free for a while to enable rendering of what we have done so far
	setTimeout( '_wppaNextSlide_2( '+mocc+' )', 10 );	// to be continued
}

function _wppaNextSlide_2( mocc ) {

	var fg = _wppaFg[mocc];
	var bg = 1 - fg;
	
	// Wait for load complete
	var elm = document.getElementById( 'theimg'+bg+"-"+mocc );
	if ( elm ) { // Exists
		if ( 1 == elm.nodeType ) {										// Is html
			if ( 'IMG' == elm.nodeName ) {								// Is an image
				if ( ! elm.complete ) {									// Is not complete yet
					setTimeout( '_wppaNextSlide_2( '+mocc+' )', 100 );	// Try again after 100 ms
					wppaConsoleLog( 'Retry next2' );					// Log retry
					return;
				}
			}
		}
	}

	// Update lightbox
	wppaUpdateLightboxes();
	
	// Remove spinner
	_wppaUnloadSpinner( mocc );
	
	// Hide subtitles
	if ( _wppaSSRuns[mocc] != -1 ) {	// not stop in progress
		if ( ! _wppaToTheSame ) {
			_wppaShowMetaData( mocc, 'hide' );
		}
	}
	
	// change foreground
	_wppaFg[mocc] = 1 - _wppaFg[mocc];
	fg = _wppaFg[mocc];
	bg = 1 - fg;
	setTimeout( '_wppaNextSlide_3( '+mocc+' )', 10 );
}

function _wppaNextSlide_3( mocc ) {

	var nw 		= _wppaFg[mocc];
	var ol 		= 1 - nw;
	
	var olIdx 	= _wppaCurIdx[mocc];
	var nwIdx 	= _wppaNxtIdx[mocc];
	
	var olSli	= "#theslide"+ol+"-"+mocc;
	var nwSli 	= "#theslide"+nw+"-"+mocc;
	
	var olImg	= "#theimg"+ol+"-"+mocc;
	var nwImg	= "#theimg"+nw+"-"+mocc;
	
	var w 		= parseInt( jQuery( olSli ).css( 'width' ) );
	var dir 	= 'nil';

	if ( olIdx == nwIdx ) dir = 'none';
	if ( olIdx == nwIdx-1 ) dir = 'left';
	if ( olIdx == nwIdx+1 ) dir = 'right';
	if ( olIdx == _wppaSlides[mocc].length-1 && 0 == nwIdx && wppaSlideWrap ) dir = 'left';
	if ( 0 == olIdx && nwIdx == _wppaSlides[mocc].length-1 && wppaSlideWrap ) dir = 'right';
	
	// Not known yet?
	if ( 'nil' == dir ) {
		if ( olIdx < nwIdx ) dir = 'left';
		else dir = 'right';
	}

	// Repair standard css
	jQuery( olSli ).css( {marginLeft:0, width:w});
	jQuery( nwSli ).css( {marginLeft:0, width:w});

	wppaFormatSlide( mocc );
	
	switch ( wppaAnimationType ) {
	
		case 'fadeover': 
			jQuery( olImg ).fadeOut( wppaAnimationSpeed ); 
			jQuery( nwImg ).fadeIn( wppaAnimationSpeed, _wppaNextSlide_4( mocc ) ); 
			break;
		
		case 'fadeafter': 
			jQuery( olImg ).fadeOut( wppaAnimationSpeed ); 
			jQuery( nwImg ).delay( wppaAnimationSpeed ).fadeIn( wppaAnimationSpeed, _wppaNextSlide_4( mocc ) ); 
			break;
		
		case 'swipe':
			switch ( dir ) {
				case 'left':
					jQuery( olSli ).animate( {marginLeft:-w+"px"}, wppaAnimationSpeed, "swing" );
					jQuery( nwSli ).css( {marginLeft:w+"px"});
					jQuery( nwImg ).fadeIn( 10 );
					jQuery( nwSli ).animate( {marginLeft:0+"px"}, wppaAnimationSpeed, "swing", _wppaNextSlide_4( mocc ) );
					break;
				case 'right':
					jQuery( olSli ).animate( {marginLeft:w+"px"}, wppaAnimationSpeed, "swing" );
					jQuery( nwSli ).css( {marginLeft:-w+"px"});
					jQuery( nwImg ).fadeIn( 10 );
					jQuery( nwSli ).animate( {marginLeft:0+"px"}, wppaAnimationSpeed, "swing", _wppaNextSlide_4( mocc ) );
					break;
				case 'none':
					jQuery( nwImg ).fadeIn( 10 );
					setTimeout( '_wppaNextSlide_4( '+mocc+' )', 10 );
					break;
			}
			break;
		
		case 'stackon':
			switch ( dir ) {
				case 'left':
					jQuery( olSli ).css( {zIndex:80});
					jQuery( nwSli ).css( {marginLeft:w+"px", zIndex:81});
					jQuery( nwImg ).fadeIn( 10 );
					jQuery( olImg ).delay( wppaAnimationSpeed ).fadeOut( 10 );
					jQuery( nwSli ).animate( {marginLeft:0+"px"}, wppaAnimationSpeed, "swing", _wppaNextSlide_4( mocc ) );
					break;
				case 'right':
					jQuery( olSli ).css( {zIndex:80});
					jQuery( nwSli ).css( {marginLeft:-w+"px", zIndex:81});
					jQuery( nwImg ).fadeIn( 10 );
					jQuery( olImg ).delay( wppaAnimationSpeed ).fadeOut( 10 );
					jQuery( nwSli ).animate( {marginLeft:0+"px"}, wppaAnimationSpeed, "swing", _wppaNextSlide_4( mocc ) );
					break;
				case 'none':
					jQuery( nwImg ).fadeIn( 10 );
					setTimeout( '_wppaNextSlide_4( '+mocc+' )', 10 );
					break;
			}
			break;
			
		case 'stackoff':
			switch ( dir ) {
				case 'left':
					jQuery( olSli ).css( {marginLeft:0, zIndex:81});
					jQuery( olSli ).animate( {marginLeft:-w+"px"}, wppaAnimationSpeed, "swing", _wppaNextSlide_4( mocc ) );
					jQuery( nwSli ).css( {marginLeft:0, zIndex:80});
					jQuery( nwImg ).fadeIn( 10 );
					jQuery( olImg ).delay( wppaAnimationSpeed ).fadeOut( 10 );
					break;
				case 'right':
					jQuery( olSli ).css( {marginLeft:0, zIndex:81});
					jQuery( olSli ).animate( {marginLeft:w+"px"}, wppaAnimationSpeed, "swing", _wppaNextSlide_4( mocc ) );
					jQuery( nwSli ).css( {marginLeft:0, zIndex:80});
					jQuery( nwImg ).fadeIn( 10 );
					jQuery( olImg ).delay( wppaAnimationSpeed ).fadeOut( 10 );
					break;
				case 'none':
					jQuery( nwImg ).fadeIn( 10 );
					setTimeout( '_wppaNextSlide_4( '+mocc+' )', 10 );
					break;
			}
			break;
			
		case 'turnover':
			switch ( dir ) {
				case 'left':
/*	there is a z-order problem here, if you can fix it, i would be glad
					jQuery( olSli ).css( {zIndex:81});
					jQuery( olSli ).animate( {width:0}, wppaAnimationSpeed, "swing" );
					jQuery( olImg ).animate( {marginLeft:0, width:0, paddingLeft:0, paddingRight:0}, wppaAnimationSpeed, "swing", _wppaNextSlide_4( mocc ) );
					jQuery( nwSli ).css( {width:w, zIndex:80});
					jQuery( nwImg ).fadeIn( 10 );
					jQuery( olImg ).fadeOut( 10 );
					break;
*/
				case 'right':
					var nwImgWid = parseInt( jQuery( nwSli ).css( 'width' ) );
					var nwMarLft = parseInt( jQuery( nwImg ).css( 'marginLeft' ) );
//		alert( nwImg+' '+nwImgWid+'  '+nwMarLft );
					jQuery( olSli ).css( {zIndex:80});
					jQuery( nwSli ).css( {zIndex:81, width:0});
					jQuery( nwImg ).css( {maxWidth:0, marginLeft:0});
					jQuery( nwImg ).fadeIn( 10 );
					jQuery( nwSli ).animate( {width:w}, wppaAnimationSpeed, "swing" );
					jQuery( nwImg ).animate( {maxWidth:nwImgWid, marginLeft:nwMarLft}, wppaAnimationSpeed, "swing", _wppaNextSlide_4( mocc ) );
					jQuery( olImg ).delay( wppaAnimationSpeed ).fadeOut( 10 );
					break;

				case 'none':
					jQuery( nwImg ).fadeIn( 10 );
					setTimeout( '_wppaNextSlide_4( '+mocc+' )', 10 );
					break;
				}
			break;
			
		default:
			alert( 'Animation type '+wppaAnimationType+' is not supported in this version' );	
			
	}
}

function _wppaNextSlide_4( mocc ) {

	var nw = _wppaFg[mocc];
	var ol = 1-nw;

	var olSli	= "#theslide"+ol+"-"+mocc;
	var nwSli 	= "#theslide"+nw+"-"+mocc;

	// Make sure title and onclick of the new image ( slide ) are in sight
	jQuery( olSli ).css( {zIndex:80});
	jQuery( nwSli ).css( {zIndex:81});
	
    // Next is now current // put here for swipe
	_wppaCurIdx[mocc] = _wppaNxtIdx[mocc];

	wppaFormatSlide( mocc );
	
	// Display counter and arrow texts
	if ( wppaIsMini[mocc] || wppaGetContainerWidth( mocc ) < wppaMiniTreshold ) {
		jQuery( '#counter-'+mocc ).html( ( _wppaCurIdx[mocc]+1 )+' / '+_wppaSlides[mocc].length );
	}
	else {
		jQuery( '#counter-'+mocc ).html( wppaPhoto+' '+( _wppaCurIdx[mocc]+1 )+' '+wppaOf+' '+_wppaSlides[mocc].length );
	}

	// Update breadcrumb
	jQuery( '#bc-pname-'+mocc ).html( _wppaNames[mocc][_wppaCurIdx[mocc]] );

	// Adjust filmstrip
	_wppaAdjustFilmstrip( mocc );
	
	// Set rating mechanism
	_wppaSetRatingDisplay( mocc );
	
	// Update og: meta tags
	// This seems to be useless...
//	_wppaUpdateOgMeta( mocc );
	
	// Wait for almost next slide
	setTimeout( '_wppaNextSlide_5( '+mocc+' )', _wppaTextDelay ); 
}

function _wppaNextSlide_5( mocc ) {

	// If we are going to the same slide, there is no need to hide and restore the subtitles and commentframe
	if ( ! _wppaToTheSame ) {
	
		// Restore subtitles
		jQuery( '#imagedesc-'+mocc ).html( _wppaDsc[mocc][_wppaCurIdx[mocc]] );
		if ( wppaHideWhenEmpty ) {
			var desc = _wppaDsc[mocc][_wppaCurIdx[mocc]];
			if ( '' == desc || '&nbsp;' == desc ) {
				jQuery( '#descbox-'+mocc ).css( 'display', 'none' );
			}
			else {
				jQuery( '#descbox-'+mocc ).css( 'display', '' );
			}
		}
		jQuery( "#imagetitle-"+mocc ).html( wppaMakeNameHtml( mocc ) );
		
		// Restore comments html
		jQuery( "#wppa-comments-"+mocc ).html( _wppaCommentHtml[mocc][_wppaCurIdx[mocc]] );
		
		// Restor IPTC
		jQuery( "#iptc-"+mocc ).html( _wppaIptcHtml[mocc][_wppaCurIdx[mocc]] );
		jQuery( "#exif-"+mocc ).html( _wppaExifHtml[mocc][_wppaCurIdx[mocc]] );
		
		// Restore share html
		jQuery( "#wppa-share-"+mocc ).html( _wppaShareHtml[mocc][_wppaCurIdx[mocc]] );
	}
	_wppaToTheSame = false;					// This has now been worked out

	// End of non wrapped show?
	if ( _wppaSSRuns[mocc] && 
		! wppaSlideWrap && 
		( ( _wppaCurIdx[mocc] + 1 ) == _wppaSlides[mocc].length ) ) {  
			_wppaIsBusy[mocc] = false;
			_wppaStop( mocc );	// stop
			return;
	}

	// Re-display the metadata
	_wppaShowMetaData( mocc, 'show' ); 
	
	// Almost done, finalize
	if ( _wppaTP[mocc] != -2 ) {		// A Toggle pending?
		var index = _wppaTP[mocc];		// Remember the pending startstop request argument
		_wppaTP[mocc] = -2;				// Reset the pending toggle
		wppaStartStop( mocc, index );		// Do as if the toggle request happens now
	}
	else {								// No toggle pending
		wppaUpdateLightboxes(); 		// Refresh lightbox
		
		// Update url and title if ( ( this is non-mini ) AND 
		// ( this is the only running non-mini OR there are no running non-minis ) )
		if ( ! wppaIsMini[mocc] ) {		// This is NOT a widget
		
		// Prepare visual url ( for addressline )
			var visurl = wppaGetCurrentFullUrl( mocc, _wppaCurIdx[mocc] );
				if ( '' == visurl ) {
					visurl = _wppaShareUrl[mocc][_wppaCurIdx[mocc]];
				}
				
			// Update possible QR Widget
			if ( typeof( wppaQRUpdate ) != 'undefined' ) {
				wppaQRUpdate( _wppaShareUrl[mocc][_wppaCurIdx[mocc]] );
			}
			
			// Push state if not slphoto
			if ( ! _wppaSSRuns[mocc] ) {	// This is not running
				if ( _wppaSlides[mocc].length > 1 ) {
					wppaPushStateSlide( mocc, _wppaCurIdx[mocc], visurl );
				}
			}
		}
		
		// If running: Wait for next slide
		if ( _wppaSSRuns[mocc] ) {				
			setTimeout( '_wppaNextSlide( '+mocc+', "auto" )', _wppaTimeOut[mocc] ); 
		}	
	}
	
	_wppaDidGoto[mocc] = false;								// Is worked out now
	_wppaIsBusy[mocc] = false;								// No longer busy
	if ( ! wppaIsMini[mocc] ) { 							// Not in a widget
		_bumpViewCount( _wppaId[mocc][_wppaCurIdx[mocc]] );	// Register a view
	}
	
	_wppaDoAutocol(mocc);
}
 
function wppaMakeNameHtml( mocc ) {
var result;
	if ( wppaIsMini[mocc] || _wppaIsVideo[mocc][_wppaCurIdx[mocc]] != '' ) {
		result = _wppaFullNames[mocc][_wppaCurIdx[mocc]];
	}
	else switch ( wppaArtMonkyLink ) {
	case 'file':
	case 'zip':
		if ( wppaArtMonkeyButton ) {
			if ( _wppaFullNames[mocc][_wppaCurIdx[mocc]] ) {
				var label = _wppaFullNames[mocc][_wppaCurIdx[mocc]].split( '<img' );
				result = '<input type="button" title="Download" style="cursor:pointer; margin-bottom:0px; max-width:'+( wppaGetContainerWidth( mocc )-24 )+'px;" class="wppa-download-button" onclick="wppaAjaxMakeOrigName( '+mocc+', '+_wppaId[mocc][_wppaCurIdx[mocc]]+' );" value="'+wppaDownLoad+': '+label[0]+'" />';
				if ( label[1] ) result += '<img'+label[1];
			}
		}
		else {
			result = '<a title="Download" style="cursor:pointer;" onclick="wppaAjaxMakeOrigName( '+mocc+', '+_wppaId[mocc][_wppaCurIdx[mocc]]+' );" >'+wppaDownLoad+': '+_wppaFullNames[mocc][_wppaCurIdx[mocc]]+'</a>';
		}
		break;
	case 'none':
		result = _wppaFullNames[mocc][_wppaCurIdx[mocc]];
		break;
	}
	return result;
}

function wppaMakeTheSlideHtml( mocc, bgfg, idx ) {

	var imgVideo = ( _wppaIsVideo[mocc][idx] != '' ) ? 'video' : 'img';
	var theHtml;
	var url;
	
//	if ( _wppaVideoHtml[mocc][idx] != '' ) {
//		jQuery( "#theslide"+bgfg+"-"+mocc ).html( _wppaVideoHtml[mocc][idx] );
//		return;
//	}

	if ( _wppaLinkUrl[mocc][idx] != '' ) {	// Link explicitly given
		if ( wppaSlideToFullpopup ) {
			theHtml = 	'<a onclick="'+_wppaLinkUrl[mocc][idx]+'" target="'+_wppaLinkTarget[mocc][idx]+'" title="'+_wppaLinkTitle[mocc][idx]+'">'+
							'<'+imgVideo+' title="'+_wppaLinkTitle[mocc][idx]+'" id="theimg'+bgfg+'-'+mocc+'" '+_wppaSlides[mocc][idx]+
						'</a>';
		}
		else {
			theHtml = 	'<a href="'+_wppaLinkUrl[mocc][idx]+'" target="'+_wppaLinkTarget[mocc][idx]+'" title="'+_wppaLinkTitle[mocc][idx]+'">'+
							'<'+imgVideo+' title="'+_wppaLinkTitle[mocc][idx]+'" id="theimg'+bgfg+'-'+mocc+'" '+_wppaSlides[mocc][idx]+
						'</a>';
		}
		jQuery( "#theslide"+bgfg+"-"+mocc ).html( theHtml );
	}
	else {
		if ( wppaLightBox[mocc] == '' ) {			// No link and no lightbox
			jQuery( "#theslide"+bgfg+"-"+mocc ).html( '<'+imgVideo+' title="'+_wppaNames[mocc][idx]+'" id="theimg'+bgfg+'-'+mocc+'" '+_wppaSlides[mocc][idx] );
		}
		else {								// Lightbox
			var html = '';
			var i = 0;
			var set = wppaLightboxSingle[mocc] ? '' : '[slide-'+mocc+'-'+bgfg+']';
			// Before current slide	// This does NOT work on lightbox 3 ! 
			if ( wppaLightBox[mocc] == 'wppa' ) {
				while ( i<idx ) {
					// Make sure fullsize
					if ( wppaOvlHires && _wppaIsVideo[mocc][i] == '' ) {
						url = _wppaHiresUrl[mocc][i];
					}
					else {
						url = wppaMakeFullsizeUrl( _wppaUrl[mocc][i] );
					}

					html += '<a href="'+url+'" data-videohtml="'+encodeURI( _wppaVideoHtml[mocc][i] )+'" title="'+_wppaLbTitle[mocc][i]+'" rel="'+wppaLightBox[mocc]+set+'"></a>';
					i++;
				}
			}
			// Current slide
			if ( wppaOvlHires && _wppaIsVideo[mocc][idx] == '' ) {
				url = _wppaHiresUrl[mocc][idx];
			}
			else {
				url = wppaMakeFullsizeUrl( _wppaUrl[mocc][idx] );
			}
		//	url = wppaMakeFullsizeUrl( _wppaUrl[mocc][idx] );
//alert( _wppaVideoHtml[mocc][idx] );
			html += '<a href="'+url+'" target="'+_wppaLinkTarget[mocc][idx]+'" data-videohtml="'+encodeURI( _wppaVideoHtml[mocc][idx] )+'" title="'+_wppaLbTitle[mocc][idx]+'" rel="'+wppaLightBox[mocc]+set+'">'+
						'<'+imgVideo+' title="'+_wppaLinkTitle[mocc][idx]+'" id="theimg'+bgfg+'-'+mocc+'" '+_wppaSlides[mocc][idx]+
					'</a>';
			// After current slide // This does NOT work on lightbox 3 ! 
			if ( wppaLightBox[mocc] == 'wppa' ) {
				i = idx + 1;
				while ( i<_wppaUrl[mocc].length ) {
					if ( wppaOvlHires && _wppaIsVideo[mocc][i] == '' ) {
						url = _wppaHiresUrl[mocc][i];
					}
					else {
						url = wppaMakeFullsizeUrl( _wppaUrl[mocc][i] );
					}
					html += '<a href="'+url+'" data-videohtml="'+encodeURI( _wppaVideoHtml[mocc][i] )+'" title="'+_wppaLbTitle[mocc][i]+'" rel="'+wppaLightBox[mocc]+set+'"></a>';
					i++;
				}
			}
			jQuery( "#theslide"+bgfg+"-"+mocc ).html( html );
		}
	}
}

function wppaMakeFullsizeUrl( url ) {

	url = url.replace( '/thumbs/', '/' );	// Not a thumb
	// Remove sizespec for Cloudinary
	var temp = url.split( '//' );
	var temp2 = temp[1].split( '/' );
	url = temp[0]+'/';
	var j = 0;
	while ( j < temp2.length ) {
		var chunk = temp2[j];
		var w = chunk.split( '_' );
		if ( w[0] != 'w' ) url += '/'+chunk;
		j++;
	}
	return url;
}

function wppaFormatSlide( mocc ) {

	// vars we have
	var imgid    = 'theimg'+_wppaFg[mocc]+'-'+mocc;
	var slideid  = 'theslide'+_wppaFg[mocc]+'-'+mocc;
	var frameid  = 'slide_frame-'+mocc;
	var contw    = wppaColWidth[mocc];
	var elm      = document.getElementById( imgid );
	if ( ! elm ) return;	// No slide present
	if ( typeof( contw ) == 'undefined' || contw == 0 ) {
		contw = wppaGetContainerWidth( mocc ); 
		wppaColWidth[mocc] = contw;
	}
	var natwidth  = elm.naturalWidth;
		if ( typeof( natwidth )=='undefined' ) natwidth = parseInt( elm.style.maxWidth );
	var natheight = elm.naturalHeight;
		if ( typeof( natheight )=='undefined' ) natheight = parseInt( elm.style.maxHeight );
	var aspect    = wppaAspectRatio[mocc];
	var fullsize  = wppaFullSize[mocc];
	var delta     = wppaFullFrameDelta[mocc];

	// Switches we have
	var ponly   = wppaPortraitOnly[mocc];
	var valign  = wppaFullValign[mocc]; if ( typeof( valign )=='undefined' ) valign = 'none';
	var halign  = wppaFullHalign[mocc]; if ( typeof( halign )=='undefined' ) halign = 'none';
	var stretch = wppaStretch;
	
	// vars to be calculated:
	var imgw, imgh;		// image width and height
	var margl, margt;	// image margins
	var slidew, slideh;	// slide width and height
	var framew, frameh;	// frame
	
	// Calculate
	if ( ponly ) {
		imgw = contw - delta;
		imgh = parseInt( imgw * natheight / natwidth );
		margl = 0;
		margt = 0;
		slidew = contw;
		slideh = imgh + delta;
		framew = contw;
		frameh = slideh;
		// Size
		jQuery( '#'+frameid ).css( {width:framew, height:frameh});
		jQuery( '#'+slideid ).css( {width:slidew, height:slideh});
		jQuery( '#'+imgid ).css( {width:imgw, height:imgh});
	}
	else {
		// not 'ponly' so we have a fixed display area. First assume the container is the hor limit
		framew = contw;
		// If the fullsize ( Table I-B1 ) is smaller than the container width The frame is scaled down to fit the fullsize
		if ( fullsize < contw ) {
			framew = fullsize;				// The fullsize appears to be the hor limit
		}
		frameh = parseInt( framew * aspect );	// Always obey the occurences aspect ratio
		slidew = framew;
		slideh = frameh;
		if ( stretch || natwidth >= ( framew-delta ) || natheight >= ( frameh-delta ) ) {	// Image big enough
			if ( ( ( natheight+delta ) / ( natwidth+delta ) ) > aspect ) {	// vertical limit
				imgh = frameh - delta;
				imgw = parseInt( imgh * natwidth / natheight );
			}
			else {	// horizontal limit
				imgw = framew - delta;
				imgh = parseInt( imgw * natheight / natwidth );
			}
		}
		else {															// Image too small
			imgw = natwidth;
			imgh = natheight;
		}

		// Align vertical
		if ( valign != 'default' && valign != 'none' ) {
			switch ( valign ) {
				case 'top':				
					margt = 0;
					break;
				case 'center':
					margt = parseInt( ( frameh - ( imgh+delta ) ) / 2 );
					break;
				case 'bottom':
					margt = frameh - ( imgh+delta );
					break;
				case 'fit':
					margt = 0;
					frameh = imgh + delta;
					slideh = imgh + delta;
					break;
				default:
				//	alert( 'Unknown v align:'+valign+' occ='+mocc );
			}
			jQuery( '#'+imgid ).css( {marginTop:margt, marginBottom:0});
		}

		// Size ( after v align because 'fit' changes the frameh and slidh )
		jQuery( '#'+frameid ).css( {width:framew, height:frameh});
		jQuery( '#'+slideid ).css( {width:slidew, height:slideh});
		jQuery( '#'+imgid ).css( {width:imgw, height:imgh});

		// Align horizontal
		if ( valign != 'default' && valign != 'none' && halign != 'none' && halign != 'default' ) {
			switch ( halign ) {
				case 'left':
					margl = 0;
					break;
				case 'center':
					margl = parseInt( ( contw - framew ) / 2 );
					break;
				case 'right':
					margl = contw - framew;
					break;
				default:
				//	alert( 'Unknown h align:'+halign+' occ='+mocc );
			}
			if ( margl < 0 ) margl = 0;
			jQuery( '#'+imgid ).css( {marginLeft:'auto', marginRight:'auto'});
			jQuery( '#'+frameid ).css( {marginLeft:margl});
			// Do not let ver 4 browser shortcomings workarounds spoil the max dimensions
//			jQuery( '#'+imgid ).css( 'max-height', imgh+'px' );
//			jQuery( '#'+imgid ).css( 'max-width', imgw+'px' );
		}
	}
	
	// Size Big Browse Buttons
	var bbbwidth = parseInt( framew/3 );
	var leftmarg = bbbwidth*2;
	
	jQuery( '#bbb-'+mocc+'-l' ).css( {height:frameh, width:bbbwidth, left:0});
	jQuery( '#bbb-'+mocc+'-r' ).css( {height:frameh, width:bbbwidth, left:leftmarg});
	
//	jQuery( '#'+imgid ).css( {cursor:url(),pointer});
}

function _wppaAdjustFilmstrip( mocc ) {

	if ( ! document.getElementById( 'wppa-filmstrip-'+mocc ) ) return;	// No filmstrip this mocc
	
	// Remove class from active thumb
	jQuery( '.wppa-film-'+mocc ).removeClass( 'wppa-filmthumb-active' );
	
	if ( ! _wppaFilmNoMove[mocc] ) {
		var xoffset;
		xoffset = wppaFilmStripLength[mocc] / 2 - ( _wppaCurIdx[mocc] + 0.5 + wppaPreambule ) * wppaThumbnailPitch[mocc] - wppaFilmStripMargin[mocc];
		if ( wppaFilmShowGlue ) xoffset -= ( wppaFilmStripMargin[mocc] * 2 + 2 );	// Glue
		jQuery( '#wppa-filmstrip-'+mocc ).stop().animate( {marginLeft: xoffset+'px'});
	}
	else {
		_wppaFilmNoMove[mocc] = false; // reset
	}
	
	// make them visible...
	if ( _wppaCurIdx[mocc] != -1 ) {
		var from = _wppaCurIdx[mocc] - 10; if ( from < 0 ) from = 0;
		var to = _wppaCurIdx[mocc] + 10; if ( to > _wppaSlides[mocc].length ) to = _wppaSlides[mocc].length;
		var index = from;
		while ( index <= to ) {
			if ( typeof ( _wppaId[mocc][index] ) != 'undefined' ) {
				if ( typeof ( document.getElementById( 'film_wppatnf_'+_wppaId[mocc][index]+'_'+mocc ) ) != 'undefined' ) {
					var html = document.getElementById( 'film_wppatnf_'+_wppaId[mocc][index]+'_'+mocc ).innerHTML;
					if ( html.search( '<!--' ) != -1 ) {
						html = html.replace( '<!--', '' );
						html = html.replace( '-->', '' );
						document.getElementById( 'film_wppatnf_'+_wppaId[mocc][index]+'_'+mocc ).innerHTML = html;
						if ( wppaFilmThumbTitle != '' ) {
							document.getElementById( 'wppa-film-'+index+'-'+mocc ).title = wppaFilmThumbTitle;
						}
						else {
							document.getElementById( 'wppa-film-'+index+'-'+mocc ).title = _wppaNames[mocc][index];
						}
					}
				}
			}
			index++;
		}
	}
	
	// Apply class to active filmthumb
	jQuery( '#wppa-film-'+_wppaCurIdx[mocc]+'-'+mocc ).addClass( 'wppa-filmthumb-active' );
}

function wppaUpdateLightboxes() {

	if ( typeof( myLightbox )!="undefined" ) myLightbox.updateImageList();	// Lightbox-3
	wppaInitOverlay();													// Native wppa lightbox
}

function _wppaNext( mocc ) {

	// Check for end of non wrapped show
	if ( ! wppaSlideWrap && _wppaCurIdx[mocc] == ( _wppaSlides[mocc].length -1 ) ) return;
	// Find next index
	_wppaNxtIdx[mocc] = _wppaCurIdx[mocc] + 1;
	if ( _wppaNxtIdx[mocc] == _wppaSlides[mocc].length ) _wppaNxtIdx[mocc] = 0;
	// And go! 
	_wppaNextSlide( mocc, 0 );
}

function _wppaNextN( mocc, n ) {

	// Check for end of non wrapped show
	if ( ! wppaSlideWrap && _wppaCurIdx[mocc] >= ( _wppaSlides[mocc].length - n ) ) return;
	// Find next index
	_wppaNxtIdx[mocc] = _wppaCurIdx[mocc] + n;
	while ( _wppaNxtIdx[mocc] >= _wppaSlides[mocc].length ) _wppaNxtIdx[mocc] -= _wppaSlides[mocc].length;
	// And go! 
	_wppaNextSlide( mocc, 0 );
}

function _wppaNextOnCallback( mocc ) {

	// Check for end of non wrapped show
	if ( ! wppaSlideWrap && _wppaCurIdx[mocc] == ( _wppaSlides[mocc].length -1 ) ) return;
	// Check for skip rated after rating
	if ( _wppaSkipRated[mocc] ) {
		var now = _wppaCurIdx[mocc];
		var idx = now + 1;
		if ( idx == _wppaSlides[mocc].length ) idx = 0;	// wrap?
		var next = idx; // assume simple next
		if ( _wppaMyr[mocc][next] != 0 ) {		// Already rated, skip
			idx++;	// try next
			if ( idx == _wppaSlides[mocc].length ) idx = 0;	// wrap?
			while ( idx != next && _wppaMyr[mocc][idx] != 0 ) {	// still rated, skip
				idx ++;	// try next
				if ( idx == _wppaSlides[mocc].length ) idx = 0;	// wrap?
			}	// either idx == next or not rated
			next = idx;
		}
		_wppaNxtIdx[mocc] = next;
	}
	else {	// Normal situation
		_wppaNxtIdx[mocc] = _wppaCurIdx[mocc] + 1;
		if ( _wppaNxtIdx[mocc] == _wppaSlides[mocc].length ) _wppaNxtIdx[mocc] = 0;
	}
	_wppaNextSlide( mocc, 0 );
}

function _wppaPrev( mocc ) {
	
	// Check for begin of non wrapped show
	if ( ! wppaSlideWrap && _wppaCurIdx[mocc] == 0 ) return;
	// Find previous index
	_wppaNxtIdx[mocc] = _wppaCurIdx[mocc] - 1;
	if ( _wppaNxtIdx[mocc] < 0 ) _wppaNxtIdx[mocc] = _wppaSlides[mocc].length - 1;
	// And go! 
	_wppaNextSlide( mocc, 0 );
}

function _wppaPrevN( mocc, n ) {
	
	// Check for begin of non wrapped show
	if ( ! wppaSlideWrap && _wppaCurIdx[mocc] < n ) return;
	// Find previous index
	_wppaNxtIdx[mocc] = _wppaCurIdx[mocc] - n;
	while ( _wppaNxtIdx[mocc] < 0 ) _wppaNxtIdx[mocc] += _wppaSlides[mocc].length;
	// And go! 
	_wppaNextSlide( mocc, 0 );
}

function _wppaGoto( mocc, idx ) {
	
	_wppaToTheSame = ( _wppaNxtIdx[mocc] == idx );
	_wppaNxtIdx[mocc] = idx;
	_wppaNextSlide( mocc, 0 );
}

function _wppaGotoRunning( mocc, idx ) {
	//wait until not bussy
	if ( _wppaIsBusy[mocc] ) { 
		setTimeout( '_wppaGotoRunning( '+mocc+',' + idx + ' )', 10 );	// Try again after 10 ms
		return;
	}
    
	wppaConsoleLog( 'GotoRunning '+mocc );

	_wppaSSRuns[mocc] = false; // we don't want timed loop to occur during our work
    
	_wppaToTheSame = ( _wppaNxtIdx[mocc] == idx );
	_wppaNxtIdx[mocc] = idx;
__wppaOverruleRun = true;
	_wppaNextSlide( mocc, "manual" ); // enqueue new transition
    
	_wppaGotoContinue( mocc );
}

function _wppaGotoContinue( mocc ) {
	if ( _wppaIsBusy[mocc] ) {
		setTimeout( '_wppaGotoContinue( '+mocc+' )', 10 );	// Try again after 10 ms
		return;
	}
	setTimeout( '_wppaNextSlide( '+mocc+', "reset" )', _wppaTimeOut[mocc] + 10 ); //restart slideshow after new timeout
}

function _wppaStart( mocc, idx ) {
	
	if ( idx == -2 ) {	// Init at first without my rating
		var i = 0;
		idx = 0;
		_wppaSkipRated[mocc] = true;
		if ( _wppaMyr[mocc][i] != 0 ) {
			while ( i < _wppaSlides[mocc].length ) {
				if ( idx == 0 && _wppaMyr[mocc][i] == 0 ) idx = i;
				i++;
			}
		}
	}

	if ( idx > -1 ) {	// Init still at index idx
		jQuery( '#startstop-'+mocc ).html( wppaStart+' '+wppaSlideShow ); 
		jQuery( '#speed0-'+mocc ).css( 'display', 'none' );
		jQuery( '#speed1-'+mocc ).css( 'display', 'none' );
		_wppaNxtIdx[mocc] = idx;
		_wppaCurIdx[mocc] = idx;
		_wppaNextSlide( mocc, 0 );
		_wppaShowMetaData( mocc, 'show' );
	}
	else {	// idx == -1, start from where you are
		_wppaSSRuns[mocc] = true;
		_wppaNextSlide( mocc, 0 );
		jQuery( '#startstop-'+mocc ).html( wppaStop );
		jQuery( '#speed0-'+mocc ).css( 'display', 'inline' );
		jQuery( '#speed1-'+mocc ).css( 'display', 'inline' );
		_wppaShowMetaData( mocc, 'hide' );	
		jQuery( '#bc-pname-'+mocc ).html( wppaSlideShow );
	}
	
	// Both cases:
	_wppaSetRatingDisplay( mocc );
}

function _wppaStop( mocc ) {
	
    _wppaSSRuns[mocc] = false;
    jQuery( '#startstop-'+mocc ).html( wppaStart+' '+wppaSlideShow );  
	jQuery( '#speed0-'+mocc ).css( 'display', 'none' );
	jQuery( '#speed1-'+mocc ).css( 'display', 'none' );
	_wppaShowMetaData( mocc, 'show' );
	jQuery( '#bc-pname-'+mocc ).html( _wppaNames[mocc][_wppaCurIdx[mocc]] );
}

function _wppaSpeed( mocc, faster ) {
	
    if ( faster ) {
        if ( _wppaTimeOut[mocc] > 500 ) _wppaTimeOut[mocc] /= 1.5;
    }
    else {
        if ( _wppaTimeOut[mocc] < 60000 ) _wppaTimeOut[mocc] *= 1.5;
    }
}

function _wppaLoadSpinner( mocc ) {
	
	if ( ! document.getElementById( 'slide_frame-'+mocc ) ) return;	// filmonly
	
	var top;
	var lft;
	var elm;
	
	var flag = true;
	
	if ( document.getElementById( 'theimg0-'+mocc ) ) { 
		if ( document.getElementById( 'theimg0-'+mocc ).complete ) flag = false;
	}
	if ( document.getElementById( 'theimg1-'+mocc ) ) { 
		if ( document.getElementById( 'theimg1-'+mocc ).complete ) flag = false;
	}

	top = parseInt( document.getElementById( 'slide_frame-'+mocc ).clientHeight / 2 ) - 16;
	lft = parseInt( document.getElementById( 'slide_frame-'+mocc ).clientWidth / 2 ) - 16;

	jQuery( '#spinner-'+mocc ).css( 'top',top );
	jQuery( '#spinner-'+mocc ).css( 'left',lft );
	jQuery( '#spinner-'+mocc ).html( '<img id="spinnerimg-'+mocc+'" src="'+wppaImageDirectory+'loading.gif" style="box-shadow: none" />' );
}

function _wppaUnloadSpinner( mocc ) {

	jQuery( '#spinner-'+mocc ).html( '' );
}

function wppaGetContainerWidth( mocc ) {
	var elm = document.getElementById( 'wppa-container-'+mocc );
	var w = 0;
	
	if ( ! wppaAutoColumnWidth[mocc] ) return elm.clientWidth;
	
	while ( w == 0 ) {
		elm = elm.parentNode;
		w = jQuery( elm ).width();
	}

	return w * wppaAutoColumnFrac[mocc];
}

function _wppaDoAutocol( mocc ) {
	
	wppaConsoleLog( 'Doing autocol '+mocc );
	
	if ( ! wppaAutoColumnWidth[mocc] ) return;	// Not auto

	var w;
	var h;
	
	// Container
	w = wppaGetContainerWidth( mocc );//document.getElementById( 'wppa-container-'+mocc ).parentNode.clientWidth;
//	if ( wppaColWidth[mocc] == w && w != 0 ) {
//		setTimeout( '_wppaDoAutocol( '+mocc+' )', 100 );
//		return;
//	}
	
	wppaColWidth[mocc] = w;
	jQuery( ".wppa-container-"+mocc ).css( 'width',w );

	// Covers
	jQuery( ".wppa-asym-text-frame-"+mocc ).css( 'width',w - wppaTextFrameDelta );
	jQuery( ".wppa-cover-box-"+mocc ).css( 'width',w - wppaBoxDelta );
	
	// Multi Column Responsive covers
	var exists = jQuery( ".wppa-cover-box-mcr-"+mocc );
	var MCRWidth;
	if ( exists.length > 1 ) {	// Yes there are
//		wppaConsoleLog( 'aantal='+exists.length );
		var nCovers = parseInt( ( w + 8 )/( wppaMaxCoverWidth+8 ) ) + 1;
		var coverMax1 = nCovers - 1;
		MCRWidth = parseInt( ( ( w + 8 )/nCovers ) - 8 );
		var idx = 0;
		while ( idx < exists.length ) {
			var col = idx % nCovers;
			switch ( col ) {
				case 0:	/* left */
					jQuery( exists[idx] ).css( {'marginLeft': '0px', 'clear': 'both', 'float': 'left'});
					break;
				case coverMax1:	/* right */
					jQuery( exists[idx] ).css( {'marginLeft': '8px', 'clear': 'none', 'float': 'right'});
					break;
				default:
					jQuery( exists[idx] ).css( {'marginLeft': '8px', 'clear': 'none', 'float': 'left'});
			}
			idx++;
		}	
		jQuery( ".wppa-asym-text-frame-mcr-"+mocc ).css( 'width',MCRWidth - wppaTextFrameDelta );
		jQuery( ".wppa-cover-box-mcr-"+mocc ).css( 'width',MCRWidth - wppaBoxDelta );
	}
	else {	// One cover: full width, 0 covers don't care
		jQuery( ".wppa-asym-text-frame-mcr-"+mocc ).css( 'width',w - wppaTextFrameDelta );
		jQuery( ".wppa-cover-box-mcr-"+mocc ).css( {'width': ( w - wppaBoxDelta ), 'marginLeft': '0px', 'float':  'left'});
	}

	// Thumbnail area
	jQuery( ".wppa-thumb-area-"+mocc ).css( 'width',w - wppaThumbnailAreaDelta );
	
	// Thumbframes
	if ( wppaThumbSpaceAuto ) {
		var tfw = parseInt( jQuery( ".thumbnail-frame-"+mocc ).css( 'width' ) );
		if ( tfw ) {
			var minspc = wppaMinThumbSpace;
			var weff = w - wppaThumbnailAreaDelta - 7;
			var nthumbs = parseInt( weff / ( tfw + minspc ) );
			var availsp = weff - nthumbs * tfw;
			var newspc = parseInt( 0.5 + availsp / ( nthumbs+1 ) );			
			
			jQuery( ".thumbnail-frame-"+mocc ).css( {marginLeft:newspc});
		}
	}
	jQuery( ".wppa-com-alt-"+mocc ).css( 'width', w - wppaThumbnailAreaDelta - wppaComAltSize - 20 );

	// User upload
	jQuery( ".wppa-file-"+mocc ).css( 'width',w - 16 ); 
	
	// User upload responsive covers
	jQuery( ".wppa-file-mcr-"+mocc ).css( 'width', MCRWidth - wppaBoxDelta - 6 );
	
	// Slide
	wppaFormatSlide( mocc );
	
	// Comments
	jQuery( ".wppa-comment-textarea-"+mocc ).css( 'width',w * 0.7 );
	
	// Filmstrip
	wppaFilmStripLength[mocc] = w - wppaFilmStripAreaDelta[mocc];
	jQuery( "#filmwindow-"+mocc ).css( 'width',wppaFilmStripLength[mocc] );
	_wppaAdjustFilmstrip( mocc );	// reposition content
	
	// Texts in slideshow and browsebar
	if ( ! wppaIsMini[mocc] && typeof( _wppaSlides[mocc] ) != 'undefined' ) {	// Mini is properly initialized
		if ( wppaColWidth[mocc] < wppaMiniTreshold ) {
			jQuery( '#prev-arrow-'+mocc ).html( wppaPrevP );
			jQuery( '#next-arrow-'+mocc ).html( wppaNextP );
			jQuery( '#wppa-avg-rat-'+mocc ).html( wppaAvgRat );
			jQuery( '#wppa-my-rat-'+mocc ).html( wppaMyRat );

			jQuery( '#counter-'+mocc ).html( ( _wppaCurIdx[mocc]+1 )+' / '+_wppaSlides[mocc].length );
		}
		else {
			jQuery( '#prev-arrow-'+mocc ).html( wppaPreviousPhoto );
			jQuery( '#next-arrow-'+mocc ).html( wppaNextPhoto );
			jQuery( '#wppa-avg-rat-'+mocc ).html( wppaAvgRating );
			jQuery( '#wppa-my-rat-'+mocc ).html( wppaMyRating );

			jQuery( '#counter-'+mocc ).html( wppaPhoto+' '+( _wppaCurIdx[mocc]+1 )+' '+wppaOf+' '+_wppaSlides[mocc].length );
		}
	}
	
	// Single photo
	jQuery( ".wppa-sphoto-"+mocc ).css( 'width',w );
	jQuery( ".wppa-simg-"+mocc ).css( 'width',w - 2*wppaSlideBorderWidth );
	jQuery( ".wppa-simg-"+mocc ).css( 'height', '' );
	
	// Mphoto
	jQuery( ".wppa-mphoto-"+mocc ).css( 'width',w + 10 );
	jQuery( ".wppa-mimg-"+mocc ).css( 'width',w );
	jQuery( ".wppa-mimg-"+mocc ).css( 'height', '' );

	// Check again after 1000 ms	
//	setTimeout( '_wppaDoAutocol( '+mocc+' )', 1000 );
}

function _wppaCheckRewind( mocc ) {

	var n_images;
	var n_diff;
	var l_substrate;
	var x_marg;
	
	if ( ! document.getElementById( 'wppa-filmstrip-'+mocc ) ) return; // There is no filmstrip
	
	n_diff = Math.abs( _wppaCurIdx[mocc] - _wppaNxtIdx[mocc] );
	if ( n_diff <= wppaFilmPageSize[mocc] ) return;	// was 2
	
	var n_images = wppaFilmStripLength[mocc] / wppaThumbnailPitch[mocc];
	
	if ( n_diff >= ( ( n_images + 1 ) / 2 ) ) {
		l_substrate = wppaThumbnailPitch[mocc] * _wppaSlides[mocc].length;
		if ( wppaFilmShowGlue ) l_substrate += ( 2 + 2 * wppaFilmStripMargin[mocc] );
		
		x_marg = parseInt( jQuery( '#wppa-filmstrip-'+mocc ).css( 'margin-left' ) );

		if ( _wppaNxtIdx[mocc] > _wppaCurIdx[mocc] ) {
			x_marg -= l_substrate;
		}
		else {
			x_marg += l_substrate;
		}

		jQuery( '#wppa-filmstrip-'+mocc ).css( 'margin-left', x_marg+'px' );
	}
}

function _wppaSetRatingDisplay( mocc ) {

	var idx, avg, tmp, cnt, dsc, myr, dsctxt;
	if ( ! document.getElementById( 'wppa-rating-'+mocc ) ) return; 	// No rating bar
	
	avg = _wppaAvg[mocc][_wppaCurIdx[mocc]];
if ( typeof( avg ) == 'undefined' ) return;
	tmp = avg.split( '|' );
	avg = tmp[0];
	cnt = tmp[1];
	
	dsc = _wppaDisc[mocc][_wppaCurIdx[mocc]];
	myr = _wppaMyr[mocc][_wppaCurIdx[mocc]];

	wppaConsoleLog( 'avg='+avg+' cnt='+cnt+' dsc='+dsc+' myr='+myr );	
	
	// Graphic display ?
	if ( wppaRatingDisplayType == 'graphic' ) {
		// Set Avg rating
		_wppaSetRd( mocc, avg, '#wppa-avg-' );
		// Set My rating
		_wppaSetRd( mocc, myr, '#wppa-rate-' );
		
		// Display dislike
		if ( myr == 0 ) {	// If i did not vote yet, enable the thumb down
			jQuery( '#wppa-dislike-'+mocc ).css( 'display', 'inline' );
			jQuery( '#wppa-dislike-imgdiv-'+mocc ).css( 'display', 'inline' );
			// Hide the filler only when there is a thumbdown
			if ( document.getElementById( 'wppa-dislike-'+mocc ) ) jQuery( '#wppa-filler-'+mocc ).css( 'display', 'none' );
			jQuery( '#wppa-dislike-'+mocc ).stop().fadeTo( 100, wppaStarOpacity );
		}
		else {			// If i voted, disable thumb down
			jQuery( '#wppa-dislike-'+mocc ).css( 'display', 'none' );
			jQuery( '#wppa-dislike-imgdiv-'+mocc ).css( 'display', 'none' );
			jQuery( '#wppa-filler-'+mocc ).css( 'display', 'inline' );
			jQuery( '#wppa-filler-'+mocc ).stop().fadeTo( 100, wppaStarOpacity );
						// Show filler with dislike count
			if ( wppaShowDislikeCount ) {
				dsctxt = wppaGetDislikeText( dsc,myr,true );
				jQuery( '#wppa-filler-'+mocc ).attr( 'title', dsctxt );
			}
		}
	}
	// Numeric display
	else { 	
		// Set avg rating
		jQuery( '#wppa-numrate-avg-'+mocc ).html( avg+' ( '+cnt+' ) ' );
		
		// Set My rating
		if ( wppaRatingOnce && myr > 0 ) {	// I did a rating and one allowed
			jQuery( '#wppa-numrate-mine-'+mocc ).html( myr );
		}
		else if ( myr < 0 ) {					// I did a dislike
			jQuery( '#wppa-numrate-mine-'+mocc ).html( ' dislike' );
		}
		else {								// Multiple allowed or change allowed or not rated yet
			var htm = '';
			for ( i=1;i<=wppaRatingMax;i++ ) {
				if ( myr == i ) {
					htm += '<span style="cursor:pointer; font-weight:bold;" onclick="_wppaRateIt( '+mocc+', '+i+' )">&nbsp;'+i+'&nbsp;</span>';
				}
				else {
					if ( myr > ( i-1 ) && myr < i ) htm += '&nbsp;( '+myr+' )&nbsp;';
					htm += '<span style="cursor:pointer;" onclick="_wppaRateIt( '+mocc+', '+i+' )" onmouseover="this.style.fontWeight=\'bold\'" onmouseout="this.style.fontWeight=\'normal\'" >&nbsp;'+i+'&nbsp;</span>';
				}
			}
			jQuery( '#wppa-numrate-mine-'+mocc ).html( htm );
		}	
		
		// Display dislike
		if ( myr == 0 ) {	// If i did not vote yet, enable the thumb down
			jQuery( '#wppa-dislike-'+mocc ).css( 'display', 'inline' );
			jQuery( '#wppa-dislike-imgdiv-'+mocc ).css( 'display', 'inline' );
			jQuery( '#wppa-filler-'+mocc ).css( 'display', 'none' );
			jQuery( '#wppa-dislike-'+mocc ).stop().fadeTo( 100, wppaStarOpacity );
		}
		else {			// If i voted, disable thumb down
			jQuery( '#wppa-dislike-'+mocc ).css( 'display', 'none' );
			jQuery( '#wppa-dislike-imgdiv-'+mocc ).css( 'display', 'none' );
			jQuery( '#wppa-filler-'+mocc ).css( 'display', 'inline' );
		}
		if ( wppaShowDislikeCount ) {
			dsctxt = wppaGetDislikeText( dsc,myr,false );
			dsctxt += '&bull; ';
			jQuery( '#wppa-discount-'+mocc ).html( dsctxt );	// Show count
			jQuery( '#wppa-filler-'+mocc ).css( 'display', 'none' );
		}
		else {
			jQuery( '#wppa-discount-'+mocc ).html( '' );	
//			jQuery( '#wppa-filler-'+mocc ).css( 'display', 'inline' );
		}
	}
	// One Button Vote only?
	if ( myr == 0 ) {
		jQuery( '#wppa-vote-button-'+mocc ).val( wppaVoteForMe );
	}
	else {
		jQuery( '#wppa-vote-button-'+mocc ).val( wppaVotedForMe );
	}
	jQuery( '#wppa-vote-count-'+mocc ).html( cnt );
}
	
function wppaGetDislikeText( dsc,myr,incmine ) {

	if ( dsc == 0 && myr != 0 ) dsctxt = ' '+wppaNoDislikes+' ';
	else if ( dsc == 1 ) dsctxt = ' '+wppa1Dislike+' ';
	else dsctxt = ' '+dsc+' '+wppaDislikes+' ';
	if ( incmine && myr < 0 ) dsctxt+=wppaIncludingMine;
	return dsctxt;
}
		
function _wppaSetRd( mocc, avg, where ) {
		
	var idx1 = parseInt( avg );
	var idx2 = idx1 + 1;
	var frac = avg - idx1;
	var opac = wppaStarOpacity + frac * ( 1.0 - wppaStarOpacity );
	var ilow = 1;
	var ihigh = wppaRatingMax;

	for ( idx=ilow;idx<=ihigh;idx++ ) {
		if ( where == '#wppa-rate-' ) {
			jQuery( where+mocc+'-'+idx ).attr( 'src', wppaImageDirectory+'star.png' );
		}
		if ( idx <= idx1 ) {
			jQuery( where+mocc+'-'+idx ).stop().fadeTo( 100, 1.0 );
		}
		else if ( idx == idx2 ) {
			jQuery( where+mocc+'-'+idx ).stop().fadeTo( 100, opac ); 
		}
		else {
			jQuery( where+mocc+'-'+idx ).stop().fadeTo( 100, wppaStarOpacity );
		}
	}
//	jQuery( '#wppa-dislike-'+mocc ).stop().fadeTo( 100, wppaStarOpacity );
}

function _wppaFollowMe( mocc, idx ) {

	if ( _wppaSSRuns[mocc] ) return;				// Do not rate on a running show, what only works properly in Firefox								

	if ( _wppaMyr[mocc][_wppaCurIdx[mocc]] != 0 && wppaRatingOnce ) return;	// Already rated
	if ( _wppaMyr[mocc][_wppaCurIdx[mocc]] < 0 ) return; 	// Disliked aleady
	if ( _wppaVIP ) return;
	_wppaSetRd( mocc, idx, '#wppa-rate-' );
}

function _wppaLeaveMe( mocc, idx ) {

	if ( _wppaSSRuns[mocc] ) return;				// Do not rate on a running show, what only works properly in Firefox	

	if ( _wppaMyr[mocc][_wppaCurIdx[mocc]] != 0 && wppaRatingOnce ) return;	// Already rated
	if ( _wppaMyr[mocc][_wppaCurIdx[mocc]] < 0 ) return; 	// Disliked aleady
	if ( _wppaVIP ) return;
	_wppaSetRd( mocc, _wppaMyr[mocc][_wppaCurIdx[mocc]], '#wppa-rate-' );
}

function _bumpViewCount( photo ) {
	wppaConsoleLog( 'B '+photo );
	if ( ! wppaBumpViewCount ) return;
	if ( wppaPhotoView[photo] == true ) {
		wppaConsoleLog( 'B '+photo+' Already done' );
		return; // Already reported
	}
	
	// Create http object
	var xmlhttp = wppaGetXmlHttp();	
	
	// Make the Ajax url
	url = wppaAjaxUrl+'?action=wppa&wppa-action=bumpviewcount&wppa-photo='+photo;
	url += '&wppa-nonce='+jQuery( '#wppa-nonce' ).val( );

	// Setup process the result
	xmlhttp.onreadystatechange=function() {
		if ( xmlhttp.readyState==4 && xmlhttp.status==200 ) {
//			alert( xmlhttp.responseText );	// Diagnostic
		}
	}
	
	// Do the Ajax action
	xmlhttp.open( 'GET',url,true );
	xmlhttp.send();
	wppaPhotoView[photo] = true;	
	wppaConsoleLog( 'B '+photo+' set true' );
}

function wppaVoteThumb( mocc, photoid ) {
	// Create http object
	var xmlhttp = wppaGetXmlHttp();	

	// Make the Ajax url
	url = wppaAjaxUrl+'?action=wppa&wppa-action=rate&wppa-rating=1&wppa-rating-id='+photoid;
	url += '&wppa-occur='+mocc;
	url += '&wppa-nonce='+jQuery( '#wppa-nonce' ).val( );
	if ( wppaLang != '' ) url += '&lang='+wppaLang;
	
	// Setup process the result
	xmlhttp.onreadystatechange=function() {
		if ( xmlhttp.readyState==4 && xmlhttp.status==200 ) {
			jQuery( '#wppa-vote-button-'+mocc+'-'+photoid ).val( wppaVotedForMe );
		}
	}
	// Do the Ajax action
	xmlhttp.open( 'GET',url,true );
	xmlhttp.send();	
}

function _wppaRateIt( mocc, value ) {

if ( value == 0 ) return;
	var photoid = _wppaId[mocc][_wppaCurIdx[mocc]];
	var oldval  = _wppaMyr[mocc][_wppaCurIdx[mocc]];
	var url 	= _wppaVRU[mocc][_wppaCurIdx[mocc]]+'&wppa-rating='+value+'&wppa-rating-id='+photoid;
		url    += '&wppa-nonce='+jQuery( '#wppa-nonce' ).val( );
	
	if ( _wppaSSRuns[mocc] ) return;								// Do not rate a running show								
	if ( oldval != 0 && wppaRatingOnce ) return;							// Already rated, and once allowed only
	if ( oldval < 0 ) return; 	// Disliked aleady
		
	_wppaVIP = true;											// Keeps opacity as it is now
	_wppaLastVote = value;
	
	jQuery( '#wppa-rate-'+mocc+'-'+value ).attr( 'src', wppaImageDirectory+'tick.png' );	// Set icon
	jQuery( '#wppa-rate-'+mocc+'-'+value ).stop().fadeTo( 100, 1.0 );		// Fade in fully
	
	// Try to create the http request object
	var xmlhttp = wppaGetXmlHttp();	

	if ( ( wppaRatingUseAjax || value == -1 ) && xmlhttp ) {			// USE AJAX Dislike always uses ajax
		
		// Make the Ajax url
		url = wppaAjaxUrl+'?action=wppa&wppa-action=rate&wppa-rating='+value+'&wppa-rating-id='+photoid;
		url += '&wppa-occur='+mocc+'&wppa-index='+_wppaCurIdx[mocc];
		url += '&wppa-nonce='+jQuery( '#wppa-nonce' ).val( );
		if ( wppaLang != '' ) url += '&lang='+wppaLang;
		
		// Setup process the result
		xmlhttp.onreadystatechange=function() {
			if ( xmlhttp.readyState==4 && xmlhttp.status==200 ) {
// alert( xmlhttp.responseText );
				var ArrValues = xmlhttp.responseText.split( "||" );
				wppaConsoleLog( xmlhttp.responseText );				
				if ( ArrValues[0] == 0 ) {	// Error
					if ( ArrValues[1] == 900 ) {		// Recoverable error
						alert( ArrValues[2] );
						_wppaSetRatingDisplay( mocc );	// Restore display
					}
					else {
						alert( 'Error Code='+ArrValues[1]+'\n\n'+ArrValues[2] );
					}
				}
				else {	// No error
					if ( value == -1 ) {	// -1 is the dislike button
//						alert( wppaDislikeMsg );
					}
					// Store new values
					_wppaMyr[ArrValues[0]][ArrValues[2]] = ArrValues[3];
					_wppaAvg[ArrValues[0]][ArrValues[2]] = ArrValues[4];
					_wppaDisc[ArrValues[0]][ArrValues[2]] = ArrValues[5];
					// Update display
					_wppaSetRatingDisplay( mocc );
					// If commenting required and not done so far...
					if ( wppaCommentRequiredAfterVote ) {
						if ( ArrValues[6] == 0 ) {
							alert( ArrValues[7] );
						}
					}

					if ( wppaNextOnCallback ) _wppaNextOnCallback( mocc );
				}
			}
		}
		// Do the Ajax action
		xmlhttp.open( 'GET',url,true );
		xmlhttp.send();	
	}
}

function _wppaValidateComment( mocc ) {

	var photoid = _wppaId[mocc][_wppaCurIdx[mocc]];
	
	// Process name
	var name = jQuery( '#wppa-comname-'+mocc ).val( );
	if ( name.length<1 ) {
		alert( wppaPleaseName );
		return false;
	}
	
	if ( wppaEmailRequired ) {
		// Process email address
		var email = jQuery( '#wppa-comemail-'+mocc ).val( );
		var atpos=email.indexOf( "@" );
		var dotpos=email.lastIndexOf( "." );
		if ( atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length ) {
			alert( wppaPleaseEmail );
			return false;
		}
	}
	
	// Process comment
	var text = jQuery( '#wppa-comment-'+mocc ).val( );
	if ( text.length<1 ) {
		alert( wppaPleaseComment );
		return false;
	}
	
	return true;
}

function _wppaGo( url ) {
	
	document.location = url;	// Go! 
}

function _wppaBbb( mocc,where,act ) {
	
	if ( _wppaSSRuns[mocc] ) return;
	
	var elm = '#bbb-'+mocc+'-'+where;
	switch ( act ) {
		case 'show':
//			jQuery( elm ).stop().fadeTo( 100, 0.2 );
			if ( where == 'l' ) jQuery( elm ).attr( 'title', wppaPreviousPhoto );
			if ( where == 'r' ) jQuery( elm ).attr( 'title', wppaNextPhoto );
			jQuery( '.bbb-'+mocc ).css( 'cursor', 'pointer' );
			break;
		case 'hide':
//			jQuery( elm ).stop().fadeTo( 400, 0 );
			jQuery( '.bbb-'+mocc ).removeAttr( 'title' );
			jQuery( '.bbb-'+mocc ).css( 'cursor', 'default' );
			break;
		case 'click':
			if ( where == 'l' ) wppaPrev( mocc );
			if ( where == 'r' ) wppaNext( mocc );
			break;
		default:
			alert( 'Unimplemented instruction: '+act+' on: '+elm );
	}
}

function _wppaUbb( mocc,where,act ) {
	
	if ( _wppaSSRuns[mocc] ) return;
	
	var elm = '#ubb-'+mocc+'-'+where;

	switch ( act ) {
		case 'show':
//			jQuery( elm ).stop().fadeTo( 100, 0.2 );
			if ( where == 'l' ) jQuery( elm ).attr( 'title', wppaPreviousPhoto );
			if ( where == 'r' ) jQuery( elm ).attr( 'title', wppaNextPhoto );
			jQuery( '.ubb-'+mocc ).css( 'cursor', 'pointer' );
			jQuery( '.ubb-'+mocc ).fadeTo( 200, 0.8 );
			break;
		case 'hide':
//			jQuery( elm ).stop().fadeTo( 400, 0 );
			jQuery( '.ubb-'+mocc ).removeAttr( 'title' );
			jQuery( '.ubb-'+mocc ).css( 'cursor', 'default' );
			jQuery( '.ubb-'+mocc ).fadeTo( 200, 0.2 );
			break;
		case 'click':
			if ( where == 'l' ) wppaPrev( mocc );
			if ( where == 'r' ) wppaNext( mocc );
			break;
		default:
			alert( 'Unimplemented instruction: '+act+' on: '+elm );
	}
}

function wppaOpenComments( mocc ) {

	if ( _wppaSSRuns[mocc] ) _wppaStop( mocc );
	
	// Show existing comments
	jQuery( '#wppa-comtable-wrap-'+mocc ).css( 'display', 'block' );
	
	// Show the input form table
	jQuery( '#wppa-comform-wrap-'+mocc ).css( 'display', 'block' );
	
	// Hide the comment footer
	jQuery( '#wppa-comfooter-wrap-'+mocc ).css( 'display', 'none' );
	
	// Do autocol to fix a layout problem
	wppaColWidth[mocc] = 0;	
	setTimeout( '_wppaDoAutocol( '+mocc+' )', 100 );
}

function _wppaShowMetaData( mocc, key ) {
	
	// What to do when the slideshow is NOT running
	if ( ! _wppaSSRuns[mocc] && ! __wppaOverruleRun ) {	
		if ( key == 'show' ) {			// Show
			if ( wppaAutoOpenComments ) {
				// Show existing comments
				jQuery( '#wppa-comtable-wrap-'+mocc ).css( 'display', 'block' );
				// Show the input form table
				jQuery( '#wppa-comform-wrap-'+mocc ).css( 'display', 'block' );
				// Hide the comment footer
				jQuery( '#wppa-comfooter-wrap-'+mocc ).css( 'display', 'none' );
			}
			// Fade the browse arrows in
			if ( wppaSlideWrap || ( _wppaCurIdx[mocc] != 0 ) )
				jQuery( '.wppa-prev-'+mocc ).css( 'visibility', 'visible' ); // fadeIn( 300 );
			if ( wppaSlideWrap || ( _wppaCurIdx[mocc] != ( _wppaSlides[mocc].length - 1 ) ) )
				jQuery( '.wppa-next-'+mocc ).css( 'visibility', 'visible' ); // fadeIn( 300 );
			// SM box
			if ( wppaShareHideWhenRunning ) {
				jQuery( '#wppa-share-'+mocc ).css( 'display', '' );
			}
			
			// Fotomoto
			wppaFotomotoToolbar( mocc, _wppaHiresUrl[mocc][_wppaCurIdx[mocc]] );
//			wppaFotomotoToolbar( mocc, _wppaUrl[mocc][_wppaCurIdx[mocc]] );
//			if ( wppaFotomoto && document.getElementById( 'wppa-fotomoto-container-'+mocc ) ) {
//				var url = _wppaUrl[mocc][_wppaCurIdx[mocc]];
			//	FOTOMOTO.API.setBoxImage( url );
			//	FOTOMOTO.API.removeBoxToolbar();
//				FOTOMOTO.API.showToolbar( 'wppa-fotomoto-container-'+mocc, url );
//			}
		}
		else {							// Hide
			// Hide existing comments
			jQuery( '#wppa-comtable-wrap-'+mocc ).css( 'display', 'none' );
			// Hide the input form table
			jQuery( '#wppa-comform-wrap-'+mocc ).css( 'display', 'none' );
			// Show the comment footer
			jQuery( '#wppa-comfooter-wrap-'+mocc ).css( 'display', 'block' );
			// Fade the browse arrows out
//			jQuery( '.wppa-prev-'+mocc ).fadeOut( 300 );	
//			jQuery( '.wppa-next-'+mocc ).fadeOut( 300 );
			wppaFotomotoHide( mocc );
		}
	}
	// What to do when the slideshow is running
	else {	// Slideshow is running
		if ( key == 'show' ) {
			// Fotomoto
			if ( ! wppaFotomotoHideWhenRunning ) wppaFotomotoToolbar( mocc, _wppaHiresUrl[mocc][_wppaCurIdx[mocc]] );
		}
		else {
			// SM box
			if ( wppaShareHideWhenRunning ) {
				jQuery( '#wppa-share-'+mocc ).css( 'display', 'none' );
			}
			// Fotomoto
		//	if ( wppaFotomotoHideWhenRunning ) 
		//	wppaFotomotoHide( mocc );
		}
	}
	
	// What to do always, independant of slideshow is running
	if ( key == 'show' ) {
		// Show title and description
		jQuery( "#imagedesc-"+mocc ).css( 'visibility', 'visible' );
		jQuery( "#imagetitle-"+mocc ).css( 'visibility', 'visible' );
		// Display counter
		jQuery( "#counter-"+mocc ).css( 'visibility', 'visible' );
		// Display iptc
		jQuery( "#iptccontent-"+mocc ).css( 'visibility', 'visible' ); 
		jQuery( "#exifcontent-"+mocc ).css( 'visibility', 'visible' ); 
	}
	else {
		// Hide title and description
//		jQuery( "#imagedesc-"+mocc ).css( 'visibility', 'hidden' ); 
//		jQuery( "#imagetitle-"+mocc ).css( 'visibility', 'hidden' );
		// Hide counter	
		jQuery( "#counter-"+mocc ).css( 'visibility', 'hidden' );
		// Fade the browse arrows out
		jQuery( '.wppa-prev-'+mocc ).css( 'visibility', 'hidden' ); // fadeOut( 300 );	
		jQuery( '.wppa-next-'+mocc ).css( 'visibility', 'hidden' ); // fadeOut( 300 );
		// Hide iptc
		jQuery( "#iptccontent-"+mocc ).css( 'visibility', 'hidden' ); 
		jQuery( "#exifcontent-"+mocc ).css( 'visibility', 'hidden' ); 

	}
}

var wppaFotomotoLoaded = false;

function fotomoto_loaded() {
	wppaFotomotoLoaded = true;
}

var wppaFotomotoToolbarIds = [];

function wppaFotomotoToolbar( mocc, url ) {
	if ( wppaColWidth[mocc] >= wppaFotomotoMinWidth ) {	// Space enough to show the toolbar
		jQuery( '#wppa-fotomoto-container-'+mocc ).css( 'display','inline' );
		jQuery( '#wppa-fotomoto-checkout-'+mocc ).css( 'display','inline' );
	}
	else {
		jQuery( '#wppa-fotomoto-container-'+mocc ).css( 'display','none' );
		jQuery( '#wppa-fotomoto-checkout-'+mocc ).css( 'display','none' );
		return;	// Too small
	}
	if ( wppaFotomoto && document.getElementById( 'wppa-fotomoto-container-'+mocc ) ) { // Configured and container present
		if ( wppaFotomotoLoaded ) {
			FOTOMOTO.API.checkinImage( url );
//			if ( wppaFotomotoToolbarIds[mocc] ) {	// Not the first in this container
//				FOTOMOTO.API.updateToolbar( wppaFotomotoToolbarIds[mocc], url );		// This usually fails, especially when the url is not yest checked in
//				alert( wppaFotomotoToolbarIds[mocc]+' '+url )
//			}
//			else {									// The first in this container
				wppaFotomotoToolbarIds[mocc] = FOTOMOTO.API.showToolbar( 'wppa-fotomoto-container-'+mocc, url );
//			}
		}
		else { // Not loaded yet, retry after 200 ms
			setTimeout( 'wppaFotomotoToolbar( '+mocc+',"'+url+'" )', 200 );
			wppaConsoleLog( 'Waiting for Fotomoto' );
		}
	}
}

function wppaFotomotoHide( mocc ) {
	jQuery( '#wppa-fotomoto-container-'+mocc ).css( 'display','none' );
	jQuery( '#wppa-fotomoto-checkout-'+mocc ).css( 'display','none' );
}

function wppaGetCurrentFullUrl( mocc, idx ) {
		
var xurl = document.location.href;
var url;
	
	// Remove &wppa-photo=... if present.
	var temp1 = xurl.split( "?" );
	var temp2 = 'nil';
	var temp3;
	var i = 0;
	var first = true;
	var pfx;
	
	if ( ! wppaShortQargs ) pfx = 'wppa-';
	else pfx = '';

	if ( temp1[1] ) temp2 = temp1[1].split( "&" );

	var albumSeen = false;
	url = temp1[0];	// everything before '?'
	if ( temp2 != 'nil' ) {
		if ( temp2.length > 0 ) {
			while ( i<temp2.length ) {
				temp3 = temp2[i].split( "=" );
				if ( temp3[0] == pfx+'album' ) albumSeen = true;
				if ( temp3[0] != pfx+'photo' ) {
					if ( first ) url += "?";
					else url += "&";
					first = false;
					url += temp2[i];
				}
				i++;
			}
		}
	}
	if ( ! albumSeen ) return '';
	
	// Append new &wppa-photo=...
	if ( first ) url += "?";
	else url += "&";
	if ( wppaUsePhotoNamesInUrls ) { //&& ! wppaStringContainsForbiddenChars( _wppaNames[mocc][idx] ) ) {
		url += pfx+'photo='+encodeURIComponent(_wppaNames[mocc][idx]);
	}
	else {
		url += pfx+'photo='+_wppaId[mocc][idx];
	}

	return url;
}

function wppaStringContainsForbiddenChars( str ) {
var forbidden = [ '?', '&', '#', '/', '"', "'" ];
var i=0;

	while ( i < forbidden.length ) {
		if ( str.indexOf( forbidden[i] ) != -1 ) {
			return true;
		}
		i++;
	}
	return false;
}


// Swipe

var triggerElementID = null; 
var fingerCount = 0;
var startX = 0;
var startY = 0;
var curX = 0;
var curY = 0;
var deltaX = 0;
var deltaY = 0;
var horzDiff = 0;
var vertDiff = 0;
var minLength = 72; 
var swipeLength = 0;
var swipeAngle = null;
var swipeDirection = null;
var wppaMocc = 0;

function wppaTouchStart( event,passedName,mocc ) {
	wppaMocc = mocc;
	event.preventDefault();
	fingerCount = event.touches.length;

	if ( fingerCount == 1 ) {
		startX = event.touches[0].pageX;
		startY = event.touches[0].pageY;
		triggerElementID = passedName;
	} else {
		wppaTouchCancel( event );
	}
}

function wppaTouchMove( event ) {
	event.preventDefault();
	if ( event.touches.length == 1 ) {
		curX = event.touches[0].pageX;
		curY = event.touches[0].pageY;
	} else {
		wppaTouchCancel( event );
	}
}

function wppaTouchEnd( event ) {
	event.preventDefault();
	if ( fingerCount == 1 && curX != 0 ) {
		swipeLength = Math.round( Math.sqrt( Math.pow( curX - startX,2 ) + Math.pow( curY - startY,2 ) ) );
		if ( swipeLength >= minLength ) {
			wppaCalculateAngle();
			wppaDetermineSwipeDirection();
			wppaProcessingRoutine();
			wppaTouchCancel( event ); // reset the variables
		} else {
			wppaTouchCancel( event );
		}	
	} else {
		wppaTouchCancel( event );
	}
}

function wppaTouchCancel( event ) {
	fingerCount = 0;
	startX = 0;
	startY = 0;
	curX = 0;
	curY = 0;
	deltaX = 0;
	deltaY = 0;
	horzDiff = 0;
	vertDiff = 0;
	swipeLength = 0;
	swipeAngle = null;
	swipeDirection = null;
	triggerElementID = null;
	wppaMocc = 0;
}

function wppaCalculateAngle() {
	var X = startX-curX;
	var Y = curY-startY;
	var Z = Math.round( Math.sqrt( Math.pow( X,2 )+Math.pow( Y,2 ) ) ); //the distance - rounded - in pixels
	var r = Math.atan2( Y,X ); //angle in radians ( Cartesian system )
	swipeAngle = Math.round( r*180/Math.PI ); //angle in degrees
	if ( swipeAngle < 0 ) { swipeAngle =  360 - Math.abs( swipeAngle ); }
}

function wppaDetermineSwipeDirection() {
	if ( ( swipeAngle <= 45 ) && ( swipeAngle >= 0 ) ) {
		swipeDirection = 'left';
	} else if ( ( swipeAngle <= 360 ) && ( swipeAngle >= 315 ) ) {
		swipeDirection = 'left';
	} else if ( ( swipeAngle >= 135 ) && ( swipeAngle <= 225 ) ) {
		swipeDirection = 'right';
	} else if ( ( swipeAngle > 45 ) && ( swipeAngle < 135 ) ) {
		swipeDirection = 'down';
	} else {
		swipeDirection = 'up';
	}
}

function wppaProcessingRoutine() {
	var swipedElement = document.getElementById( triggerElementID );
	if ( wppaMocc == -1 ) { // swipe on ligtbox image
		if ( swipeDirection == 'left' ) {
			wppaOvlShowNext();
			wppaMocc = 0;
		}
		else if ( swipeDirection == 'right' ) {
			wppaOvlShowPrev();
			wppaMocc = 0;
		}		
	}
	else {	// swipe on slideshow
		if ( swipeDirection == 'left' ) {
			wppaNext( wppaMocc );
			wppaMocc = 0;
		} 
		else if ( swipeDirection == 'right' ) {
			wppaPrev( wppaMocc );
			wppaMocc = 0;
		} 
		else if ( swipeDirection == 'up' ) {
		} 
		else if ( swipeDirection == 'down' ) {
		}
	}
}

// Part 2: Theme variables and functions
//

var wppaBackgroundColorImage = '';
var _wppaTimer = [];
var wppa_saved_id = [];
var wppaPopupLinkType = '';
var wppaPopupOnclick = [];
var wppaThumbTargetBlank = false;

// Popup of thumbnail images 
function wppaPopUp( mocc, elm, id, name, desc, rating, ncom, videohtml, maxsizex, maxsizey ) {
	var topDivBig, topDivSmall, leftDivBig, leftDivSmall;
	var heightImgBig, heightImgSmall, widthImgBig, widthImgSmall, widthImgBigSpace;
	var puImg;
	var vOffset = 0;
	var imghtml;
	
	// stop if running 
	clearTimeout( _wppaTimer[mocc] );
	
	// Vertical offset?
//	if ( document.getElementById( 'wppa-albname-'+mocc ) ) vOffset += document.getElementById( 'wppa-albname-'+mocc ).clientHeight;
//	if ( document.getElementById( 'wppa-albdesc-'+mocc ) ) vOffset += document.getElementById( 'wppa-albdesc-'+mocc ).clientHeight;
	
	// Give this' occurrances popup its content
	if ( document.getElementById( 'x-'+id+'-'+mocc ) ) {
		var namediv = name ? '<div id="wppa-name-'+mocc+'" style="display:none; padding:1px;" class="wppa_pu_info">'+name+'</div>' : '';
		var descdiv = desc ? '<div id="wppa-desc-'+mocc+'" style="clear:both; display:none; padding:1px;" class="wppa_pu_info">'+desc+'</div>' : '';
		var ratediv = rating ? '<div id="wppa-rat-'+mocc+'" style="clear:both; display:none; padding:1px;" class="wppa_pu_info">'+rating+'</div>' : '';
		var ncomdiv = ncom ? '<div id="wppa-ncom-'+mocc+'" style="clear:both; display:none; padding:1px;" class="wppa_pu_info">'+ncom+'</div>' : '';
		var popuptext = namediv+descdiv+ratediv+ncomdiv;

		var target = '';
		if ( wppaThumbTargetBlank ) target = 'target="_blank"';
//wppaConsoleLog('vhtml='+videohtml);
		switch ( wppaPopupLinkType ) {
			case 'none':
				imghtml = videohtml != '' ? videohtml : '<img id="wppa-img-'+mocc+'" src="'+elm.src+'" title="" style="border-width: 0px;" />';
				jQuery( '#wppa-popup-'+mocc ).html( '<div class="wppa-popup" style="background-color:'+wppaBackgroundColorImage+'; text-align:center;">'+imghtml+popuptext+'</div>' );
				break;
			case 'fullpopup':
				imghtml = videohtml != '' ? videohtml : '<img id="wppa-img-'+mocc+'" src="'+elm.src+'" title="" style="border-width: 0px;" onclick="'+wppaPopupOnclick[id]+'" />';
				jQuery( '#wppa-popup-'+mocc ).html( '<div class="wppa-popup" style="background-color:'+wppaBackgroundColorImage+'; text-align:center;">'+imghtml+popuptext+'</div>' );
				break;
			default:
				if ( elm.onclick ) {
					imghtml = videohtml != '' ? videohtml : '<img id="wppa-img-'+mocc+'" src="'+elm.src+'" title="" style="border-width: 0px;" />';
					jQuery( '#wppa-popup-'+mocc ).html( '<div class="wppa-popup" style="background-color:'+wppaBackgroundColorImage+'; text-align:center;">'+imghtml+popuptext+'</div>' );
					document.getElementById( 'wppa-img-'+mocc ).onclick = elm.onclick;
				}
				else {
					imghtml = videohtml != '' ? videohtml : '<img id="wppa-img-'+mocc+'" src="'+elm.src+'" title="" style="border-width: 0px;" />';
					jQuery( '#wppa-popup-'+mocc ).html( '<div class="wppa-popup" style="background-color:'+wppaBackgroundColorImage+'; text-align:center;"><a id="wppa-a" href="'+document.getElementById( 'x-'+id+'-'+mocc ).href+'" '+target+' style="line-height:1px;" >'+imghtml+'</a>'+popuptext+'</div>' );
				}
		}
	}
	
	// Find handle to the popup image 
	puImg = document.getElementById( 'wppa-img-'+mocc );

	// Compute ending sizes
	widthImgBig = parseInt(maxsizex);
	heightImgBig = parseInt(maxsizey);
		
	wppaConsoleLog( 'widthImgBig='+widthImgBig+', heightImgBig='+heightImgBig );

	// Set width of text fields to width of a landscape image	
	if ( puImg ) jQuery( ".wppa_pu_info" ).css( 'width', ( ( widthImgBig > heightImgBig ? widthImgBig : heightImgBig ) - 8 )+'px' );	
	// Compute starting coords
	leftDivSmall = parseInt( elm.offsetLeft ) - 7 - 5 - 1; // thumbnail_area:padding, wppa-img:padding, wppa-border; jQuery().css( "padding" ) does not work for padding in css file, only when litaral in the tag
	topDivSmall = parseInt( elm.offsetTop ) - 7 - 5 - 1;		
		topDivSmall -= vOffset;
	// Compute starting sizes
	widthImgSmall = parseInt( elm.clientWidth );
	heightImgSmall = parseInt( elm.clientHeight );

	widthImgBigSpace = widthImgBig > heightImgBig ? widthImgBig : heightImgBig;
	// Compute ending coords
	leftDivBig = leftDivSmall - parseInt( ( widthImgBigSpace - widthImgSmall ) / 2 );
	topDivBig = topDivSmall - parseInt( ( heightImgBig - heightImgSmall ) / 2 );
	
	// Margin for portrait images
	var lrMarg = parseInt( ( widthImgBigSpace - widthImgBig ) / 2 );
	
	// Setup starting properties
	jQuery( '#wppa-popup-'+mocc ).css( {"marginLeft":leftDivSmall+"px","marginTop":topDivSmall+"px"});
	jQuery( '#wppa-img-'+mocc ).css( {"marginLeft":0,"marginRight":0,"width":widthImgSmall+"px","height":heightImgSmall+"px"});
	// Do the animation
	jQuery( '#wppa-popup-'+mocc ).stop().animate( {"marginLeft":leftDivBig+"px","marginTop":topDivBig+"px"}, 400 );
	jQuery( '#wppa-img-'+mocc ).stop().animate( {"marginLeft":lrMarg+"px","marginRight":lrMarg+"px","width":widthImgBig+"px","height":heightImgBig+"px"}, 400 );
//alert( widthImgBig+', '+heightImgBig );
	// adding ", 'linear', wppaPopReady( occ ) " fails, therefor our own timer to the "show info" module
	_wppaTimer[mocc] = setTimeout( 'wppaPopReady( '+mocc+' )', 400 );
}
function wppaPopReady( mocc ) {
	jQuery( "#wppa-name-"+mocc ).show();
	jQuery( "#wppa-desc-"+mocc ).show();
	jQuery( "#wppa-rat-"+mocc ).show();
	jQuery( "#wppa-ncom-"+mocc ).show();
}

// Dismiss popup
function wppaPopDown( mocc ) {	//	 return; //debug
	jQuery( '#wppa-popup-'+mocc ).html( "" );
	return;
}

// Popup of fullsize image
function wppaFullPopUp( mocc, id, url, xwidth, xheight ) {
	var height = xheight+50;
	var width  = xwidth+14;
	var name = '';
	var desc = '';
	
	var elm = document.getElementById( 'i-'+id+'-'+mocc );
	if ( elm ) {
		name = elm.alt;
		desc = elm.title;
	}	
	
	var wnd = window.open( '', 'Print', 'width='+width+', height='+height+', location=no, resizable=no, menubar=yes ' );
	wnd.document.write( '<html>' );
		wnd.document.write( '<head>' );	
			wnd.document.write( '<style type="text/css">body{margin:0; padding:6px; background-color:'+wppaBackgroundColorImage+'; text-align:center;}</style>' );
			wnd.document.write( '<title>'+name+'</title>' );
			wnd.document.write( 
			'<script type="text/javascript">function wppa_downl( id ) {'+
				'var xmlhttp = new XMLHttpRequest();'+
				'var url = "'+wppaAjaxUrl+'?action=wppa&wppa-action=makeorigname&photo-id='+id+'&from=popup";'+
				'xmlhttp.open( "GET",url,false );'+
				'xmlhttp.send();'+
				'if ( xmlhttp.readyState==4 && xmlhttp.status==200 ) {'+
					'var result = xmlhttp.responseText.split( "||" );'+
					'if ( result[1] == "0" ) {'+
						'window.open( result[2] );'+
						'return true;'+
					'}'+
					'else {'+
						'alert( "Error: "+result[1]+" "+result[2] );'+
						'return false;'+
					'}'+
				'}'+
				'else {'+
					'alert( "Comm error encountered" );'+
					'return false;'+
				'}'+
			'}</script>' );
			wnd.document.write( 
			'<script type="text/javascript">function wppa_print() {'+
				'document.getElementById( "wppa_printer" ).style.visibility="hidden"; '+
				'document.getElementById( "wppa_download" ).style.visibility="hidden"; '+
				'window.print();'+
			'}</script>' );
		wnd.document.write( '</head>' );
		wnd.document.write( '<body>' );
			wnd.document.write( '<div style="width:'+xwidth+'px;">' );
				wnd.document.write( '<img src="'+url+'" style="padding-bottom:6px;" /><br/>' );
				wnd.document.write( '<div style="text-align:center">'+desc+'</div>' );
				var left = xwidth-66;
				wnd.document.write( '<img src="'+wppaImageDirectory+'download.png" id="wppa_download" title="Download" style="position:absolute; top:6px; left:'+left+'px; background-color:'+wppaBackgroundColorImage+'; padding: 2px; cursor:pointer;" onclick="wppa_downl();" />' );
				left = xwidth-30;
				wnd.document.write( '<img src="'+wppaImageDirectory+'printer.png" id="wppa_printer" title="Print" style="position:absolute; top:6px; left:'+left+'px; background-color:'+wppaBackgroundColorImage+'; padding: 2px; cursor:pointer;" onclick="wppa_print();" />' );
			wnd.document.write( '</div>' );
		wnd.document.write( '</body>' );
	wnd.document.write( '</html>' );
}

// Frontend Edit Photo
function wppaEditPhoto( mocc, id ) {
	var name = 'Edit Photo '+id;
	var desc = '';
	var width = 960;
	var height = 512;

	if ( screen.availWidth < width ) width = screen.availWidth;
	
//	var hdoc = document;

	var wnd = window.open( "", "_blank", "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, copyhistory=yes, width="+width+", height="+height, true );

	wnd.document.write( '<! DOCTYPE html>' );
	wnd.document.write( '<html>' );
		wnd.document.write( '<head>' );	
			// The following is one statement that fixes a bug in opera
			wnd.document.write( 	'<link rel="stylesheet" id="wppa_style-css"  href="'+wppaWppaUrl+'/wppa-admin-styles.css?ver='+wppaVersion+'" type="text/css" media="all" />'+
								'<style>body {font-family: sans-serif; font-size: 12px; line-height: 1.4em;}a {color: #21759B;}</style>'+
								'<script type="text/javascript" src="'+wppaIncludeUrl+'/js/jquery/jquery.js?ver='+wppaVersion+'"></script>'+
								'<script type="text/javascript" src="'+wppaWppaUrl+'/wppa-admin-scripts.js?ver='+wppaVersion+'"></script>'+
								'<title>'+name+'</title>'+
								'<script type="text/javascript">wppaAjaxUrl="'+wppaAjaxUrl+'";</script>' );
		wnd.document.write( '</head>' );
		wnd.document.write( '<body>' ); // onunload="window.opener.location.reload()">' );	// This does not work in Opera
		
		var xmlhttp = wppaGetXmlHttp();
		// Make the Ajax send data
		var data = 'action=wppa&wppa-action=front-edit&photo-id='+id+'&moccur='+mocc;
				
		var url = wppaAjaxUrl+'?'+data;
		// Do the Ajax action
		xmlhttp.open( 'POST', url, false );	// Synchronously ! ! 
		xmlhttp.setRequestHeader( "Content-type","application/x-www-form-urlencoded" );
		xmlhttp.send( data );
	
		// Process result
		if ( xmlhttp.readyState==4 && xmlhttp.status==200 ) {
			var result = xmlhttp.responseText;
			wnd.document.write( result );
		}
		wnd.document.write( '<script>wppaPhotoStatusChange( '+id+' )</script>' ); 
		wnd.document.write( '</body>' );
	wnd.document.write( '</html>' );
//
}
/*
function wppaReloadWindow( hwnd ) {
alert( typeof( hwnd ) );
	hwnd.document.location.reload( true );
}
*/
// Part 3: Ajax
// Additionally: functions to change the url during ajax and browse operations

// AJAX RENDERING INCLUDING HISTORY MANAGEMENT
// IF AJAX NOT ALLOWED, ALSO NO HISTORY MENAGEMENT! ! 

var wppaHis = 0;
var wppaStartHtml = [];
var wppaCanAjaxRender = false;	// Assume failure
var wppaCanPushState = false;
var wppaAllowAjax = true;		// Assume we are allowed to use ajax
var wppaMaxOccur = 0;
var wppaFirstOccur = 0;
var wppaUsePhotoNamesInUrls = false;

// Initialize
jQuery( document ).ready(function( e ) {
	// Are we allowed and capable to ajax?
	if ( wppaAllowAjax && wppaGetXmlHttp() ) {
		wppaCanAjaxRender = true;
	}
	// Can we do history.pushState ?
	if ( typeof( history.pushState ) != 'undefined' ) {		
		// Save entire initial page content ( I do not know which container is going to be modified first )
		var i=1;
		while ( i<=wppaMaxOccur ) {
			wppaStartHtml[i] = jQuery( '#wppa-container-'+i ).html();
			i++;
		}
//if ( wppaUpdateAddressLine )
		wppaCanPushState = true;
	}
});

// Get the http request object
function wppaGetXmlHttp() {
	if ( window.XMLHttpRequest ) {		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else {								// code for IE6, IE5
		xmlhttp=new ActiveXObject( "Microsoft.XMLHTTP" );
	}
	return xmlhttp;
}

// Setup an event handler for popstate events
window.onpopstate = function( event ) { 
	var occ = 0;
	if ( wppaCanPushState ) {
		if ( event.state ) {
			occ = event.state.occur;
			switch ( event.state.type ) {
				case 'html':
					// Restore wppa container content
					jQuery( '#wppa-container-'+occ ).html( event.state.html );
					break;
				case 'slide':
					// Go to specified slide without the didgoto switch to avoid a stackpush here
					_wppaGoto( occ, event.state.slide );
					break;				
			}
		}
		else if ( wppaUpdateAddressLine ) {
		/**/
			occ = wppaFirstOccur;
			// Restore first modified occurrences content
			jQuery( '#wppa-container-'+occ ).html( wppaStartHtml[occ] );
			// Now we are back to the initial page
			wppaFirstOccur = 0;
			// If a photo number given goto that photo
			if ( occ == 0 ) {	// Find current occur if not yet known
				var url = document.location.href;
				var urls = url.split( "&wppa-occur=" );
				occ = parseInt( urls[1] );			
			}
			var url = document.location.href;
			var urls = url.split( "&wppa-photo=" );
			var photo = parseInt( urls[1] );
			if ( photo > 0 ) {
				var idx = 0;
				while ( idx < _wppaId[occ].length ) {
					if ( _wppaId[occ][idx] == photo ) break;
					idx++;
				}
				if ( idx < _wppaId[occ].length ) _wppaGoto( occ, idx );
			}
	/*	*/
		}
		// If it is a slideshow, stop it
		if ( document.getElementById( 'theslide0-'+occ ) ) {
			_wppaStop( occ );
		}
	}
	if ( typeof( wppaQRUpdate ) != 'undefined' ) wppaQRUpdate( document.location.href );
//	wppaQRData = document.location.href; //????
};  

// The AJAX rendering routine Sync
function wppaDoAjaxRender( mocc, ajaxurl, newurl ) {

	// Fix the url
	if ( wppaLang != '' ) ajaxurl += '&lang='+wppaLang;

	// Create the http request object
	var xmlhttp = wppaGetXmlHttp();
		
	// Ajax possible ?
	if ( wppaCanAjaxRender ) {	

		// If it is a slideshow: Stop slideshow before pushing it on the stack
		if ( _wppaSSRuns[mocc] ) _wppaStop( mocc );

		// Display the spinner
		jQuery( '#wppa-ajax-spin-'+mocc ).css( 'display', '' );

		// Do the Ajax action
		xmlhttp.open( 'GET',ajaxurl,false );
		xmlhttp.send();	
		
		// Update the wppa container
		jQuery( '#wppa-container-'+mocc ).html( xmlhttp.responseText );
		
		// Push the stack
		if ( wppaCanPushState && wppaUpdateAddressLine ) {
			wppaHis++;
			cont = xmlhttp.responseText;
			try {
				history.pushState( {page: wppaHis, occur: mocc, type: 'html', html: cont}, "---", newurl );
				wppaConsoleLog( 'Ajax rendering: History stack updated' );
			}
			catch( err ) {
				wppaConsoleLog( 'Ajax rendering: Failed to update history stack' );
			}
			if ( wppaFirstOccur == 0 ) wppaFirstOccur = mocc;
		}
				
		// If lightbox is on board, refresh the imagelist. It has just changed, you know! 
		wppaUpdateLightboxes();
				
		// Update qrcode
		if ( typeof( wppaQRUpdate ) != 'undefined' ) wppaQRUpdate( newurl );

		// Run Autocol? 
		wppaColWidth[mocc] = 0;	
		_wppaDoAutocol( mocc );
				
		// If it is a slideshow: Upate 'Faster' and 'Slower' to the desired language.
		// The ajax stuff may get the admin language while we need the frontend language
		jQuery( '#speed0-'+mocc ).html( wppaSlower );
		jQuery( '#speed1-'+mocc ).html( wppaFaster );
		
		// Remove spinner
		jQuery( '#wppa-ajax-spin-'+mocc ).css( 'display', 'none' );

	}
	else {	// Ajax NOT possible
		document.location.href = newurl;

		// Run Autocol? 
		wppaColWidth[mocc] = 0;	// force a recalc and triggers autocol if needed
		_wppaDoAutocol( mocc );
	}
}

function wppaAjaxApprovePhoto( photoid ) {
	var xmlhttp = wppaGetXmlHttp();

	xmlhttp.onreadystatechange = function() {
		if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 ) {
			if ( xmlhttp.responseText == 'OK' ) {
				jQuery( '.wppa-approve-'+photoid ).css( 'display', 'none' );
			}
			else {
				alert( xmlhttp.responseText );
			}
		}
	}
	// Do the Ajax action
	ajaxurl = wppaAjaxUrl+'?action=wppa&wppa-action=approve&photo-id='+photoid;
	xmlhttp.open( 'GET',ajaxurl,true );
	xmlhttp.send();	
	
}

function wppaAjaxRemovePhoto( mocc, photoid, isslide ) {
	var xmlhttp = wppaGetXmlHttp();

	xmlhttp.onreadystatechange = function() {
		if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 ) {
			var rtxt = xmlhttp.responseText.split( '||' );
			if ( rtxt[0] == 'OK' ) {
				if ( isslide ) {
//					alert( rtxt[1] );
//					document.location = document.location;
//					_wppaUrl[mocc][_wppaCurIdx[mocc]] = '';
//					_wppaSlides[mocc][_wppaCurIdx[mocc]] = ' src=""';
jQuery( '#wppa-film-'+_wppaCurIdx[mocc]+'-'+mocc ).attr( 'src', '' );
jQuery( '#wppa-pre-'+_wppaCurIdx[mocc]+'-'+mocc ).attr( 'src', '' );
jQuery( '#wppa-film-'+_wppaCurIdx[mocc]+'-'+mocc ).attr( 'alt', 'removed' );
jQuery( '#wppa-pre-'+_wppaCurIdx[mocc]+'-'+mocc ).attr( 'alt', 'removed' );
					wppaNext( mocc );
				}
				else {
					jQuery( '.wppa-approve-'+photoid ).css( 'display', 'none' );
					jQuery( '.thumbnail-frame-photo-'+photoid ).css( 'display', 'none' );
				}
			}
			else {
				alert( xmlhttp.responseText );
			}
		}
	}
	// Do the Ajax action
	ajaxurl = wppaAjaxUrl+'?action=wppa&wppa-action=remove&photo-id='+photoid;
	xmlhttp.open( 'GET',ajaxurl,true );
	xmlhttp.send();	
	
}

function wppaAjaxApproveComment( commentid ) {
	var xmlhttp = wppaGetXmlHttp();

	xmlhttp.onreadystatechange = function() {
		if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 ) {
			if ( xmlhttp.responseText == 'OK' ) {
				jQuery( '.wppa-approve-'+commentid ).css( 'display', 'none' );
			}
			else {
				alert( xmlhttp.responseText );
			}
		}
	}
	// Do the Ajax action
	ajaxurl = wppaAjaxUrl+'?action=wppa&wppa-action=approve&comment-id='+commentid;
	xmlhttp.open( 'GET',ajaxurl,true );
	xmlhttp.send();	
	
}

function wppaAjaxRemoveComment( commentid ) {
	var xmlhttp = wppaGetXmlHttp();

	xmlhttp.onreadystatechange = function() {
		if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 ) {
			var rtxt = xmlhttp.responseText.split( '||' );
			if ( rtxt[0] == 'OK' ) {
				jQuery( '.wppa-approve-'+commentid ).css( 'display', 'none' );
				jQuery( '.wppa-comment-'+commentid ).css( 'display', 'none' );
			}
			else {
				alert( xmlhttp.responseText );
			}
		}
	}
	// Do the Ajax action
	ajaxurl = wppaAjaxUrl+'?action=wppa&wppa-action=remove&comment-id='+commentid;
	xmlhttp.open( 'GET',ajaxurl,true );
	xmlhttp.send();	
	
}

function wppaPushStateSlide( mocc, slide, url ) {

	if ( ! wppaIsMini[mocc] ) {	// Not from a widget
		if ( wppaCanPushState && wppaUpdateAddressLine ) {
//			var url = wppaGetCurrentFullUrl( mocc, _wppaCurIdx[mocc] );
			if ( url != '' ) {
				try {
					history.pushState( {page: wppaHis, occur: mocc, type: 'slide', slide: slide}, "---", url );
					wppaConsoleLog( 'Slide history stack updated' );
				}
				catch( err ) {
					wppaConsoleLog( 'Slide history stack update failed' );
				}
			}
		}
	}
}

// WPPA EMBEDDED NATIVE LIGHTBOX FUNCTIONALITY
//
var wppaOvlUrls;
var wppaOvlUrl;
var wppaOvlTitles;
var wppaOvlTitle;
var wppaOvlIdx = -1;
var wppaOvlFirst = true;
var wppaOvlKbHandler = '';
var wppaOvlSizeHandler = '';
var wppaOvlPadTop = 5;
var wppaWindowInnerWidth;
var wppaWindowInnerHeight;
var wppaOvlIsSingle;
var wppaOvlRunning = false;
var wppaOvlVideoHtmls;
var wppaOvlVideoHtml;
var wppaOvlMode = 'normal';

// The next var values become overwritten in wppa-non-admin.php -> wppa_load_footer()
var wppaOvlCloseTxt = 'CLOSE';
var wppaOvlTxtHeight = 36;	// 12 * ( n lines of text including the n/m line )
var wppaOvlOpacity = 0.8;
var wppaOvlOnclickType = 'none';
var wppaOvlTheme = 'black';
var wppaOvlAnimSpeed = 300;
var wppaOvlSlideSpeed = 3000;
var wppaVer4WindowWidth = 800;
var wppaVer4WindowHeight = 600;
var wppaOvlFontFamily = 'Helvetica';
var wppaOvlFontSize = '10';
var wppaOvlFontColor = '';
var wppaOvlFontWeight = 'bold';
var wppaOvlLineHeight = '12';
var wppaOvlShowCounter = true;
var wppaOvlIsVideo = false;
var wppaShowLegenda = '';

// Initial initialization
jQuery( document ).ready(function( e ) {
	wppaInitOverlay();
});

// Window resize handler
jQuery( window ).resize(function() {
	jQuery("#wppa-overlay-bg").css({height:window.innerHeight});
	wppaOvlResize( 10 );
});

// Keyboard handler
function wppaOvlKeyboardHandler( e ) {

	var keycode;
	var escapeKey;
	
	if ( e == null ) { // ie
		keycode = event.keyCode;
		escapeKey = 27;
	} else { // mozilla
		keycode = e.keyCode;
		escapeKey = 27; //e.DOM_VK_ESCAPE;
	}
	
	var key = String.fromCharCode( keycode ).toLowerCase();
	
	switch ( keycode ) {
		case escapeKey:
			wppaOvlHide();
			break;
		case 37:
			wppaOvlShowPrev();
			break;
		case 39:
			wppaOvlShowNext();
			break;
	}
	
	switch ( key ) {
		case 'x':
		case 'o':
		case 'c':
		case 'q':
			wppaOvlHide();
			break;
		case 'p':	
			wppaOvlShowPrev();
			break;
		case 'n':
			wppaOvlShowNext();
			break;
		case 's':
			wppaOvlStartStop();
			break;
		case 'd':
			jQuery('#wppa-ovl-legenda-1').css('visibility','hidden');
			jQuery('#wppa-ovl-legenda-2').css('visibility','hidden');
			wppaShowLegenda = 'hidden';
			break;
		case 'f':
			var oldMode = wppaOvlMode;
			wppaOvlStepMode();
			var elem = document.getElementById('wppa-overlay-ic');
			if ( oldMode == 'normal' ) {
				if (elem.requestFullscreen) {
					elem.requestFullscreen();
				} else if (elem.mozRequestFullScreen) {
					elem.mozRequestFullScreen();
				} else if (elem.webkitRequestFullscreen) {
					elem.webkitRequestFullscreen();
				}
				setTimeout( 'wppaOvlShow( '+wppaOvlIdx+' )', 1000 );
			}
			if ( wppaOvlMode == 'normal' ) {
				if (document.cancelFullScreen) {
					document.cancelFullScreen();
				} else if (document.mozCancelFullScreen) {
					document.mozCancelFullScreen();
				} else if (document.webkitCancelFullScreen) {
					document.webkitCancelFullScreen();
				}
			}
			jQuery('#wppa-ovl-legenda-1').html('');
			break;
	}
}


function wppaFindWindowSize() {
wppaConsoleLog( 'wppaFindWindowSize' );
	wppaWindowInnerWidth = window.innerWidth;
	wppaWindowInnerHeight = window.innerHeight;
	if ( typeof( wppaWindowInnerWidth )=='undefined' ) wppaWindowInnerWidth = jQuery( window ).width(); // wppaVer4WindowWidth;
	if ( typeof( wppaWindowInnerHeight )=='undefined' ) wppaWindowInnerHeight = jQuery( window ).height(); //wppaVer4WindowHeight;
wppaConsoleLog( 'winw='+wppaWindowInnerWidth+', winh='+wppaWindowInnerHeight );
}

function wppaOvlShow( arg ) {
wppaConsoleLog( 'wppaOvlShow arg='+arg );

	if ( wppaOvlFirst ) {
		jQuery( document ).on('keydown', wppaOvlKeyboardHandler	);
		wppaOvlFirst = false;
	}

	wppaFindWindowSize();
	
	// Prevent Weaver ii from hiding us
	jQuery( '#weaver-final' ).removeClass( 'wvr-hide-bang' );
	
	// Display spinner
	jQuery( '#wppa-overlay-sp' ).css( {left: ( wppaWindowInnerWidth/2 )-16, top: ( wppaWindowInnerHeight/2 )-16, visibility: 'visible'});
	
	var href;
	if ( parseInt( arg ) == arg ) {	// Arg is Numeric
		if ( arg != -1 ) {
			wppaOvlUrl = wppaOvlUrls[arg];
			wppaOvlTitle = wppaOvlTitles[arg];
			wppaOvlIdx = arg;
			wppaOvlVideoHtml = wppaOvlVideoHtmls[arg];
		} // else redo the same single
	}
	else {						// Arg is 'this' arg
		wppaOvlIdx = -1;	// Assume single
		wppaOvlUrl = arg.href;
		wppaOvlTitle = wppaRepairScriptTags( arg.title );
		wppaOvlVideoHtml = decodeURI( jQuery( arg ).attr( 'data-videohtml' ) );
		var rel = arg.rel;
		var temp = rel.split( '[' );
		if ( temp[1] ) {	// We are in a set
			wppaOvlUrls = [];
			wppaOvlTitles = [];
			wppaOvlVideoHtmls = [];
			var setname = temp[1];
			var anchors = jQuery( 'a' );
			var anchor;
			var i, j = 0;
			wppaOvlIdx = -1;
			// Save the set
			for ( i=0;i<anchors.length;i++ ) {
				anchor = anchors[i];
				if ( anchor.rel ) {
					temp = anchor.rel.split( "[" );
					if ( temp[0] == 'wppa' && temp[1] == setname ) {	// Same set
						wppaOvlUrls[j] = anchor.href;
						wppaOvlTitles[j] = wppaRepairScriptTags( anchor.title );
						wppaOvlVideoHtmls[j] = decodeURI( jQuery( anchor ).attr( 'data-videohtml' ) );
						if ( anchor.href == wppaOvlUrl ) wppaOvlIdx = j;	// Current index
						j++;
					}
				}
			}
		}
		else { 	// Single
			wppaOvlUrls = false;
			wppaOvlTitles = false;
			wppaOvlVideoHtmls = false;
			wppaOvlIdx = -1;
		}
	}

	var photoId = wppaUrlToId( wppaOvlUrl );
	_bumpViewCount( photoId );

	if ( wppaOvlMode != 'normal' ) {
		jQuery( '#wppa-overlay-bg' ).fadeTo( 300, wppaOvlOpacity );	// show black background first
		var html = 
		'<div id="wppa-ovl-full-bg" style="position:fixed; width:'+wppaWindowInnerWidth+'px; height:'+wppaWindowInnerHeight+'px; left:0px; top:0px; text-align:center;" >'+
			'<img id="wppa-overlay-img"'+
				' ontouchstart="wppaTouchStart( event, \'wppa-overlay-img\', -1 );"'+
				' ontouchend="wppaTouchEnd( event );"'+
				' ontouchmove="wppaTouchMove( event );"'+
				' ontouchcancel="wppaTouchCancel( event );"'+
				' src="'+wppaOvlUrl+'"'+
				' style="border:none; width:'+wppaWindowInnerWidth+'px; visibility:hidden; box-shadow:none; position:absolute;"'+
			' />'+
			' <div style="height: 20px; width: 100%; position:absolute; bottom:0; left:0;" onmouseover="jQuery(\'#wppa-ovl-legenda-2\').css(\'visibility\',\'visible\');" onmouseout="jQuery(\'#wppa-ovl-legenda-2\').css(\'visibility\',\'hidden\');wppaShowLegenda=\'hidden\';" >'+
				' <div id="wppa-ovl-legenda-2" style="position:fixed; left:0; bottom:0; background-color:'+(wppaOvlTheme == 'black' ? '#272727' : '#a7a7a7')+'; color:'+(wppaOvlTheme == 'black' ? '#a7a7a7' : '#272727')+'; visibility:'+wppaShowLegenda+';" >'+
					'Mode='+wppaOvlMode+'. '+( wppaOvlIsSingle ? wppaOvlFullLegendaSingle : wppaOvlFullLegenda )+
				' </div>'+
			' </div>';
		' </div>';

		jQuery( '#wppa-overlay-ic' ).html( html );
		setTimeout( 'wppaOvlShowFull()', 10 );
		return false;
	}

//	else {
		var mw = 250;

		jQuery( '#wppa-overlay-bg' ).fadeTo( 300, wppaOvlOpacity );
		var lft = ( wppaWindowInnerWidth/2-125 )+'px';
		var ptp = ( wppaWindowInnerHeight/2-125 )+'px';

		jQuery( '#wppa-overlay-ic' ).css( {left: lft, paddingTop: ptp});
		var txtcol = wppaOvlTheme == 'black' ? '#a7a7a7' : '#272727';	// Normal font
		var qtxtcol = wppaOvlTheme == 'black' ? '#a7a7a7' : '#575757';	// Bold font
		if ( wppaOvlFontColor ) txtcol = wppaOvlFontColor;
		var startstop = wppaOvlRunning ? wppaStop : wppaStart;
		var html = 	'<div id="wppa-overlay-start-stop" style="position:absolute; left:0px; top:'+( wppaOvlPadTop-1 )+'px; visibility:hidden; box-shadow:none; font-family:helvetica; font-weight:bold; font-size:14px; color:'+qtxtcol+'; cursor:pointer; " onclick="wppaOvlStartStop()" ontouchstart="wppaOvlStartStop()" >'+startstop+'</div>'+
					'<div id="wppa-overlay-qt-txt"  style="position:absolute; right:16px; top:'+( wppaOvlPadTop-1 )+'px; visibility:hidden; box-shadow:none; font-family:helvetica; font-weight:bold; font-size:14px; color:'+qtxtcol+'; cursor:pointer; " onclick="wppaOvlHide()" ontouchstart="wppaOvlHide()" >'+wppaOvlCloseTxt+'&nbsp;&nbsp;</div>'+
					'<img id="wppa-overlay-qt-img"  src="'+wppaImageDirectory+'smallcross-'+wppaOvlTheme+'.gif'+'" style="position:absolute; right:0; top:'+wppaOvlPadTop+'px; visibility:hidden; box-shadow:none; cursor:pointer" onclick="wppaOvlHide()" ontouchstart="wppaOvlHide()" >';
		if ( typeof( wppaOvlVideoHtml ) != 'undefined' && wppaOvlVideoHtml != '' && wppaOvlVideoHtml != 'undefined' ) {
	//alert( wppaOvlVideoHtml );
			html += '<video id="wppa-overlay-img"'+
			' ontouchstart="wppaTouchStart( event, \'wppa-overlay-img\', -1 );"  ontouchend="wppaTouchEnd( event );" ontouchmove="wppaTouchMove( event );" ontouchcancel="wppaTouchCancel( event );" '+
			' style="border-width:16px; border-style:solid; border-color:'+wppaOvlTheme+'; margin-bottom:-15px; max-width:'+mw+'px; visibility:hidden; box-shadow:none;" controls >'+wppaOvlVideoHtml+'</video>';
			wppaOvlIsVideo = true;
		}
		else {
			html += '<img id="wppa-overlay-img"'+
			' ontouchstart="wppaTouchStart( event, \'wppa-overlay-img\', -1 );"  ontouchend="wppaTouchEnd( event );" ontouchmove="wppaTouchMove( event );" ontouchcancel="wppaTouchCancel( event );" '+
			' src="'+wppaOvlUrl+'" style="border-width:16px; border-style:solid; border-color:'+wppaOvlTheme+'; margin-bottom:-15px; max-width:'+mw+'px; visibility:hidden; box-shadow:none;" />';
			wppaOvlIsVideo = false;
		}
		html += '<div id="wppa-overlay-txt-container" style="padding:10px; background-color:'+wppaOvlTheme+'; color:'+txtcol+'; text-align:center; font-family:'+wppaOvlFontFamily+'; font-size: '+wppaOvlFontSize+'px; font-weight:'+wppaOvlFontWeight+'; line-height:'+wppaOvlLineHeight+'px; visibility:hidden; box-shadow:none;" ><div>';
		
		jQuery( '#wppa-overlay-ic' ).html( html );
		setTimeout( 'wppaOvlShow2()', 10 );
		return false;
//	}
}

function wppaOvlShowFull() {
wppaConsoleLog('ShowFull '+wppaOvlMode );

	var img = document.getElementById( 'wppa-overlay-img' );
	
	if ( ! wppaOvlIsVideo && ( ! img || ! img.complete ) ) {
		setTimeout( 'wppaOvlShowFull()', 10 );	// Wait for load complete
		return;
	}
	
	// Find out if the picture is more portrait than the screen
	var screenRatio = wppaWindowInnerWidth / wppaWindowInnerHeight;
	var imageRatio 	= img.naturalWidth / img.naturalHeight; 
	var margLeft 	= 0;
	var margTop 	= 0;
	var imgHeight 	= 0;
	var imgWidth 	= 0;
	var scrollTop 	= 0;
	var scrollLeft 	= 0;
	var Overflow 	= 'hidden';

	switch ( wppaOvlMode ) {
		case 'padded':
			if ( screenRatio > imageRatio ) {	// Picture is more portrait
				margLeft 	= ( wppaWindowInnerWidth - wppaWindowInnerHeight * imageRatio ) / 2;
				margTop 	= 0;
				imgHeight 	= wppaWindowInnerHeight;
				imgWidth 	= wppaWindowInnerHeight * imageRatio;
			}
			else {
				margLeft 	= 0;
				margTop 	= ( wppaWindowInnerHeight - wppaWindowInnerWidth / imageRatio ) / 2;
				imgHeight 	= wppaWindowInnerWidth / imageRatio;
				imgWidth 	= wppaWindowInnerWidth;
			}
			break;
		case 'stretched':
			margLeft 	= 0;
			margTop 	= 0;
			imgHeight 	= wppaWindowInnerHeight;
			imgWidth 	= wppaWindowInnerWidth;
			break;
		case 'clipped':
			if ( screenRatio > imageRatio ) {	// Picture is more portrait
				margLeft 	= 0;
				margTop 	= ( wppaWindowInnerHeight - wppaWindowInnerWidth / imageRatio ) / 2;
				imgHeight 	= wppaWindowInnerWidth / imageRatio;
				imgWidth 	= wppaWindowInnerWidth;
			}
			else {
				margLeft 	= ( wppaWindowInnerWidth - wppaWindowInnerHeight * imageRatio ) / 2;
				margTop 	= 0;
				imgHeight 	= wppaWindowInnerHeight;
				imgWidth 	= wppaWindowInnerHeight * imageRatio;
			}
			break;
		case 'realsize':
			margLeft 	= ( wppaWindowInnerWidth - img.naturalWidth ) / 2;
			if ( margLeft < 0 ) {
				scrollLeft 	= - margLeft;
				margLeft 	= 0;
			}
			margTop 	= ( wppaWindowInnerHeight - img.naturalHeight ) / 2;
			if ( margTop < 0 ) {
				scrollTop 	= - margTop;
				margTop 	= 0;
			}
			imgHeight 	= img.naturalHeight;
			imgWidth 	= img.naturalWidth;
			Overflow 	= 'auto';
			break;
	}
	margLeft 	= parseInt( margLeft );
	margTop 	= parseInt( margTop );
	imgHeight 	= parseInt( imgHeight );
	imgWidth 	= parseInt( imgWidth );

	jQuery(img).css({height:imgHeight,width:imgWidth,marginLeft:margLeft,marginTop:margTop,left:0,top:0});
	jQuery(img).css({visibility:'visible'});
	jQuery( '#wppa-ovl-full-bg' ).css({overflow:Overflow});
	jQuery( '#wppa-ovl-full-bg' ).scrollTop( scrollTop );
	jQuery( '#wppa-ovl-full-bg' ).scrollLeft( scrollLeft );
	jQuery( '#wppa-overlay-sp' ).css({visibility:'hidden'});

	return true;	// Done!
}

function wppaOvlShow2() {
wppaConsoleLog( 'wppaOvlShow2' );	
	var img = document.getElementById( 'wppa-overlay-img' );
	
	if ( ! wppaOvlIsVideo && ( ! img || ! img.complete ) ) {
		setTimeout( 'wppaOvlShow2()', 10 );	// Wait for load complete
		return;
	}
if ( wppaOvlAnimSpeed!=0 )
	img.style.visibility = 'visible';		// Display image
	
	setTimeout( 'wppaOvlShow3()', 10 );
	return false;
}
function wppaOvlShow3() {
wppaConsoleLog( 'wppaOvlShow3' );
	// Remove spinner
	jQuery( '#wppa-overlay-sp' ).css( {visibility: 'hidden'});
	// Size to final dimensions
	jQuery( '#wppa-overlay-txt-container' ).html( '<div id="wppa-overlay-txt"></div>' );	// reqd for sizeing
	var speed = wppaOvlAnimSpeed;
	wppaOvlSize( speed );
	// Go on
	setTimeout( 'wppaOvlShow4()', speed+50 );
	return false;
}
function wppaOvlShow4() {
wppaConsoleLog( 'wppaOvlShow4' );

	var cw = document.getElementById( 'wppa-overlay-img' ).clientWidth;
	if ( wppaOvlIdx != -1 ) {	// One out of a set
		var vl = 'visibility:hidden;';
		var vr = 'visibility:hidden;';
		var ht = 'height:'+wppaOvlTxtHeight+'px;';
		
		var txtwidth;
		if ( wppaOvlRunning ) txtwidth = cw + 12;
		else txtwidth = cw - 80;
		
		if ( wppaOvlIdx != 0 ) vl = 'visible';
		if ( wppaOvlIdx != ( wppaOvlUrls.length-1 ) ) vr = 'visible';
		if ( wppaOvlTxtHeight == 'auto' ) ht = '';
		
		var html = 	'';
		if ( ! wppaOvlRunning ) {
			html += 
					'<img src="'+wppaImageDirectory+'prev-'+wppaOvlTheme+'.gif" style="position:relative; top:-8px; float:left; '+vl+'; box-shadow:none;" onclick="wppaOvlShowPrev()" ontouchstart="wppaOvlShowPrev()" />'+
					'<img src="'+wppaImageDirectory+'next-'+wppaOvlTheme+'.gif" style="position:relative; top:-8px; float:right;'+vr+'; box-shadow:none;" onclick="wppaOvlShowNext()" ontouchstart="wppaOvlShowNext()" />';
		}
		html +=			
					'<div id="wppa-overlay-txt" style="text-align:center; min-height:36px; '+ht+' overflow:auto; box-shadow:none; width:'+txtwidth+'px;" >';
					if ( wppaOvlShowCounter ) html += ( wppaOvlIdx+1 )+'/'+wppaOvlUrls.length+'<br />';
					html += wppaOvlTitle+'</div>';
					
	//			if ( wppaFotomoto ) {
	//				html += '<span class="FotomotoToolbarPosition"></span>';
	//				html += '<script style="text/javascript" >FOTOMOTO.API.setBoxImage( "'+wppaOvlUrl+'" );</script>';
	//			}

					
					
		jQuery( '#wppa-overlay-txt-container' ).html( html );
		wppaOvlIsSingle = false;
	}
	else {
		jQuery( '#wppa-overlay-txt-container' ).html( '<div id="wppa-overlay-txt" style="text-align:center; margin-left:45px;" >'+wppaOvlTitle+'</div>' );
		wppaOvlIsSingle = true;
	}
	jQuery( '#wppa-overlay-txt-container' ).css( 'visibility', 'visible' );
	jQuery( '#wppa-overlay-qt-txt' ).css( {visibility: 'visible'});
	jQuery( '#wppa-overlay-qt-img' ).css( {visibility: 'visible'});
	if ( ! wppaOvlIsSingle ) jQuery( '#wppa-overlay-start-stop' ).css( {visibility: 'visible'});


	if ( wppaOvlTxtHeight == 'auto' ) wppaOvlResize( 10 );	// Resize to accomodate for var text height
	return false;
}

function wppaOvlStepMode() {
wppaConsoleLog('StepMode from '+wppaOvlMode);
	var modes = new Array( 'normal', 'padded', 'stretched', 'clipped', 'realsize', 'padded' );
	var i = 0;
	while ( i < modes.length ) {
		if ( wppaOvlMode == modes[i] ) {
			wppaOvlMode = modes[i+1];
			wppaOvlShow( wppaOvlIdx );
			return;
		}
		i++;
	}
}

function wppaOvlStartStop() {
	if ( wppaOvlRunning ) {
		jQuery( '#wppa-overlay-start-stop' ).html( wppaStart );
		wppaOvlRunning = false;
	}
	else {
		jQuery( '#wppa-overlay-start-stop' ).html( wppaStop );
		wppaOvlRunning = true;
		wppaOvlRun();
	}
}
function wppaOvlRun() {
wppaConsoleLog( 'wppaOvlRun, running='+wppaOvlRunning );
	var next;
	if ( ! wppaOvlRunning ) return;
	if ( wppaOvlIdx >= ( wppaOvlUrls.length-1 ) ) next = 0;
	else next = wppaOvlIdx + 1;
	wppaOvlShow( next );
wppaConsoleLog( 'Setting timeout wppaOvlRun(), '+wppaOvlSlideSpeed );
	setTimeout( 'wppaOvlRun()', wppaOvlSlideSpeed );
}
function wppaOvlShowPrev() {
wppaConsoleLog( 'wppaOvlShowPrev' );
	if ( wppaOvlIsSingle ) return false;
	if ( wppaOvlIdx < 1 ) {
		wppaOvlIdx = wppaOvlUrls.length;	// Restart at last
//		wppaOvlHide();	// There is no prev, quit
//		return false;
	}
	wppaOvlShow( wppaOvlIdx-1 );
	return false;
}
function wppaOvlShowNext() {
wppaConsoleLog( 'wppaOvlShowNext' );
	if ( wppaOvlIsSingle ) return false;
	if ( wppaOvlIdx >= ( wppaOvlUrls.length-1 ) ) {
		wppaOvlIdx = -1;	// Restart at first
//		wppaOvlHide();	// There is no next, quit
//		return false;
	}
	wppaOvlShow( wppaOvlIdx+1 );
	return false;
}

function wppaOvlSize( speed ) {
wppaConsoleLog( 'wppaOvlSize' );

	// Are we still visible?
	if ( jQuery('#wppa-overlay-bg').css('display') == 'none' ) {
		wppaConsoleLog('Lb quitted');
		return;
	}
	
	// Full screen?
	if ( wppaOvlMode != 'normal' ) {
		wppaOvlShowFull();
		return;
	}
	
	
	// Wait for text complete
	if ( ! document.getElementById( 'wppa-overlay-txt' ) ) { setTimeout( 'wppaOvlSize( '+speed+' )', 10 ); return;}

	wppaFindWindowSize();

	var iw = wppaWindowInnerWidth;
	var ih = wppaWindowInnerHeight;	
	var img = document.getElementById( 'wppa-overlay-img' );
	var cw, nw, nh;
	
	if ( wppaOvlIsVideo ) {
		cw = 640;
		nw = 640;
		nh = 480;
	}
	else {
		cw = img.clientWidth;
	
		nw = img.naturalWidth; 
		nh = img.naturalHeight; 
	}
	
	var fakt1;
	var fakt2;
	var fakt;
	
	// If the width is the limiting factor, adjust the height
	if ( typeof( nw ) == 'undefined' ) {	// ver 4 browser
		nw = img.clientWidth;
		nh = img.clientHeight;
		fakt1 = ( iw-100 )/nw;
		fakt2 = ih/nh;
		if ( fakt1<fakt2 ) fakt = fakt1;	// very landscape, width is the limit
		else fakt = fakt2;				// Height is the limit
		if ( true ) {					// Up or downsize
			nw = parseInt( nw * fakt );
			nh = parseInt( nh * fakt );
		}
	}
	else {
		fakt1 = ( iw-100 )/nw;
		fakt2 = ih/nh;
		if ( fakt1<fakt2 ) fakt = fakt1;	// very landscape, width is the limit
		else fakt = fakt2;				// Height is the limit
		if ( fakt < 1.0 ) {				// Only downsize if needed
			nw = parseInt( nw * fakt );
			nh = parseInt( nh * fakt );
		}
	}

	var mh;	// max image height
	var tch = document.getElementById( 'wppa-overlay-txt' ).clientHeight;

	if ( wppaOvlTxtHeight == 'auto' ) {
		if ( tch == 0 ) tch = 36;
		mh = ih - tch - 52;
	}
	else {
		mh = ih - wppaOvlTxtHeight - 52;
	}

	var mw = parseInt( mh * nw / nh );
	var pt = wppaOvlPadTop;
	var lft = parseInt( ( iw-mw )/2 );
	var wid = mw;

	// Image too small?	( never for ver 4 browsers, we do not know the natural dimensions
	if ( nh < mh ) {
		pt = wppaOvlPadTop + ( mh - nh )/2;
		lft = parseInt( ( iw-nw )/2 );
		wid = nw;
	}

	var cwid = wid+32;	// container width = image width + 2 * border
	
		lft -= 16; 		// border width

	// Go to final size
	if ( speed == 0 ) {
		jQuery( '#wppa-overlay-img' ).css( {width:wid, maxWidth: wid, visibility: 'visible'});
		jQuery( '#wppa-overlay-ic' ).css( {width: cwid, left: lft, paddingTop: pt});
		jQuery( '#wppa-overlay-qt-txt' ).css( {top: ( pt-1 )});
		jQuery( '#wppa-overlay-start-stop' ).css( {top: ( pt-1 )});
		jQuery( '#wppa-overlay-qt-img' ).css( {top: pt});
	}
	else {
		jQuery( '#wppa-overlay-img' ).stop().animate( {width:wid, maxWidth: wid}, speed );
		jQuery( '#wppa-overlay-ic' ).stop().animate( {width: cwid, left: lft, paddingTop: pt}, speed );
		jQuery( '#wppa-overlay-qt-txt' ).stop().animate( {top: ( pt-1 )}, speed );
		jQuery( '#wppa-overlay-start-stop' ).stop().animate( {top: ( pt-1 )}, speed );
		jQuery( '#wppa-overlay-qt-img' ).stop().animate( {top: pt}, speed );
	}
	
	// If resizing, also resize txt elements when sizing is complete
	if ( document.getElementById( 'wppa-overlay-txt' ) ) {
		// Hide during resize if sizing takes longer than 10 ms.
		if ( speed > 10 ) jQuery( '#wppa-overlay-txt' ).css( {visibility: 'hidden'});
	}		
	setTimeout( 'wppaOvlSize2()', 20 );
	return true;
}
function wppaOvlSize2() {
wppaConsoleLog( 'wppaOvlSize2' );
	
	var cw;
	if ( ! wppaOvlIsVideo ) {
		cw = document.getElementById( 'wppa-overlay-img' ).clientWidth;
	}
	else {
		cw = 640;
	}
	var txtwidth;
	if ( wppaOvlRunning ) txtwidth = cw + 12;
	else txtwidth = cw - 80;

	jQuery( '#wppa-overlay-img' ).css( {width: cw});	// Req'd for ver 4 browsers
	jQuery( '#wppa-overlay-ic' ).css( {width: cw+32});	// ditto
	jQuery( '#wppa-overlay-txt' ).css( {width: txtwidth+'px', visibility: 'visible'});

	return true;
}

function wppaOvlHide() {
wppaConsoleLog( 'wppaOvlHide' );
	// Clear image container
	jQuery( '#wppa-overlay-ic' ).html( '' );
	jQuery( '#wppa-overlay-ic' ).css( {paddingTop: 0});
	// Remove background
	jQuery( '#wppa-overlay-bg' ).fadeOut( 300 );
	// Remove kb handler
	jQuery( document ).off( 'keydown', wppaOvlKeyboardHandler );//keydown(function( e ) {} );//wppaOvlKbHandler;
	// Re-instal possible original resize handler
//	window.onresize = wppaOvlSizeHandler;
	// Reset switch
	wppaOvlFirst = true;
	wppaOvlRunning = false;
	wppaOvlMode = 'normal';
	jQuery( '#wppa-overlay-sp' ).css({visibility:'hidden'});
}

function wppaOvlOnclick( event ) {
	switch ( wppaOvlOnclickType ) {
		case 'none':
			break;
		case 'close':
			wppaOvlHide();
			break;
		case 'browse':
			var x = event.screenX - window.screenX;
			if ( x < wppaWindowInnerWidth/2 ) wppaOvlShowPrev();
			else wppaOvlShowNext();
			break;
		default:
			alert( 'Unimplemented action: '+wppaOvlOnclickType );
			break;
	}
	return true;
}


			
function wppaInitOverlay() {
wppaConsoleLog( 'wppaInitOverlay' );
	var anchors=jQuery( 'a' );
	var anchor;
	var i;
		
	for( i=0;i<anchors.length;i++ ) {
		anchor = anchors[i];
		if ( anchor.rel ) {
			temp = anchor.rel.split( "[" );
			if ( temp[0] == 'wppa' ) {
				wppaWppaOverlayActivated = true;	// found one
				jQuery( anchor ).click(function( event ) {
					wppaOvlShow( this );
					event.preventDefault();
				}); 
				jQuery( anchor ).on( "touchstart", function( event ) {
					wppaOvlShow( this );
					event.preventDefault();
				}); 
			}
		}
	}
//	wppaOvlRunning = false;

	// Almost done, Install eventhandlers
	if ( wppaOvlFirst ) {
		// Enable kb input
//		jQuery( document ).on('keydown', wppaOvlKeyboardHandler	);		
//		if ( document.onkeydown ) {
//			wppaOvlKbHandler = document.onkeydown; 
//		}
//		document.onkeydown = wppaKbAction; 

//		if ( window.onresize ) {
//			wppaOvlSizeHandler = window.onresize;
//		}
//		window.onresize = function () {return wppaOvlResize( 10 );}		
//		wppaOvlFirst = false;
	}
	
	
}
/*
var wppaKbAction = function( e ) {

	if ( e == null ) { // ie
		keycode = event.keyCode;
		escapeKey = 27;
	} else { // mozilla
		keycode = e.keyCode;
		escapeKey = 27; //e.DOM_VK_ESCAPE;
	}

	key = String.fromCharCode( keycode ).toLowerCase();

	if ( ( key == 'x' ) || ( key == 'o' ) || ( key == 'c' ) || ( key == 'q' ) || ( keycode == escapeKey ) ) {
		wppaOvlHide();
	} 
	else if( ( key == 'p' ) || ( keycode == 37 ) ) {	
		wppaOvlShowPrev();
	} else if( ( key == 'n' ) || ( keycode == 39 ) ) {	
		wppaOvlShowNext();
	}
}
*/
// This module is intented to be used in any onclick definition that opens or closes a part of the photo description.
// this will automaticly adjust the picturesize so that the full description will be visible.
// Example: <a href="javascript://" onclick="myproc()" >Show Details</a>
// Change to: <a href="javascript://" onclick="myproc(); wppaOvlResize()" >Show Details</a>
// Isn't it simple?
function wppaOvlResize() {
wppaConsoleLog( 'wppaOvlResize' );
	// See if generic lightbox is on
//	if ( wppaLightBox != 'wppa' ) return;	// No, not this time.
	// Wait for completeion of text and do a size operation
	setTimeout( 'wppaOvlSize( 10 )', 50 );		// After resizing, the number of lines may have changed
	setTimeout( 'wppaOvlSize( 10 )', 100 );
	setTimeout( 'wppaOvlSize( 10 )', 150 );
	setTimeout( 'wppaOvlSize( 10 )', 1000 );
}

function wppaAjaxMakeOrigName( mocc, id ) {
	
	// Create the http request object
	var xmlhttp = wppaGetXmlHttp();
	var url = wppaAjaxUrl+'?action=wppa&wppa-action=makeorigname&photo-id='+id+'&from=fsname';

	// Issue request Synchronously! ! 
	xmlhttp.open( "GET",url,false );
	xmlhttp.send();
	
	if ( xmlhttp.readyState==4 && xmlhttp.status==200 ) {
		var result = xmlhttp.responseText.split( '||' );
		if ( result[1] == '0' ) {	// Ok, no error
			// Publish result
			if ( wppaArtMonkyLink == 'file' ) window.open( result[2] );
			if ( wppaArtMonkyLink == 'zip' ) document.location = result[2];
			// Go
			return true;
		}
		else {
			// Show error
			alert( 'Error: '+result[1]+'\n\n'+result[2] );
			return false;
		}
	}
	else {
		alert( 'Comm error encountered' );
		return false;
	}
}

function wppaAjaxDownloadAlbum( mocc, id ) {
	
	// Show spinner
	jQuery( '#dwnspin-'+mocc+'-'+id ).css( 'display', '' );
	
	// Create the http request object
	var xmlhttp = wppaGetXmlHttp();
	var url = wppaAjaxUrl+'?action=wppa&wppa-action=downloadalbum&album-id='+id;

	// Issue request Synchronously! ! 
	xmlhttp.open( "GET",url,false );
	xmlhttp.send();
	
	// Hide spinner
	jQuery( '#dwnspin-'+mocc+'-'+id ).css( 'display', 'none' );

	// Alalyze the result
	var result 	= xmlhttp.responseText.split( '||' );
	var url 	= result[0];
	var erok 	= result[1];
	var text 	= result[2];
	
	if ( result.length == 3 && text != '' ) alert( 'Attention:\n\n'+text );

	if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 ) {	// Normal successfull return
		if ( erok == 'OK' ) {
			document.location = url;
			return true;
		}
	}
	else {	// See if a ( partial ) zipfile has been created
		var zipurl = wppaGetAlbumZipUrl( id );
		if ( zipurl != '' ) {
			alert( 'The server could not complete the request. The zipfile may be incomplete' );
			document.location = zipurl;
		}
		else {
			alert( 'Comm error encountered. readyState = '+xmlhttp.readyState+', status = '+xmlhttp.status );
		}
		return false;
	}
}

function wppaGetAlbumZipUrl( id ) {
	// Create the http request object
	var xmlhttp = wppaGetXmlHttp();
	var url = wppaAjaxUrl+'?action=wppa&wppa-action=getalbumzipurl&album-id='+id;

	// Issue request Synchronously! ! 
	xmlhttp.open( "GET",url,false );
	xmlhttp.send();
	
	if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 ) {	// Normal successfull return
		if ( xmlhttp.responseText != 'ER' && xmlhttp.responseText != '' ) {	// Got a valid url
			return xmlhttp.responseText;
		}
	}
	return '';
}

function wppaAjaxComment( mocc, id ) {

	if ( ! _wppaValidateComment( mocc ) ) return;
	
	// Show spinner
	jQuery( "#wppa-comment-spin-"+mocc ).css( 'display', 'inline' );
	
	// Create the http request object
	var xmlhttp = wppaGetXmlHttp();

	// Make the Ajax send data
	var data = 'action=wppa&wppa-action=do-comment&photo-id='+id
		+'&comname='+jQuery( "#wppa-comname-"+mocc ).val( )
		+'&comment='+wppaEncode( jQuery( "#wppa-comment-"+mocc ).val( ) )
		+'&wppa-captcha='+jQuery( "#wppa-captcha-"+mocc ).val( )
		+'&wppa-nonce='+jQuery( "#wppa-nonce-"+mocc ).val( )
		+'&moccur='+mocc;
		if ( typeof ( jQuery( "#wppa-comemail-"+mocc ).val( ) ) != 'undefined' ) data += '&comemail='+jQuery( "#wppa-comemail-"+mocc ).val( );
		if ( typeof ( jQuery( "#wppa-comment-edit-"+mocc ).val( ) ) != 'undefined' ) data += '&comment-edit='+jQuery( "#wppa-comment-edit-"+mocc ).val( );
				
	// Do the Ajax action
	xmlhttp.open( 'POST',wppaAjaxUrl,false );	// Synchronously ! ! 
	xmlhttp.setRequestHeader( "Content-type","application/x-www-form-urlencoded" );
	xmlhttp.send( data );
	
	// Process result
	if ( xmlhttp.readyState==4 && xmlhttp.status==200 ) {
		var result = xmlhttp.responseText;
result = result.replace( /\\/g, '' );
		jQuery( "#wppa-comments-"+mocc ).html( result );
		_wppaCommentHtml[mocc][_wppaCurIdx[mocc]] = result;
		wppaOpenComments( mocc );
	}
	else {
		alert( 'Comm error encountered' );
		return false;
	}
	
	// Hide spinner
	jQuery( "#wppa-comment-spin-"+mocc ).css( 'display', 'none' );

}

function wppaConsoleLog( arg, force ) {
//wppaDebug=true;//diagnostic
	if ( typeof( console ) != 'undefined' && ( wppaDebug || force == 'force' ) ) {
		console.log( arg );
	}
}

function wppaRepairScriptTags( text ) {
	var temp = text.split( '[script' );
	if ( temp.length == 1 ) return text;
	var newtext = temp[0];
	var idx = 0;
	while ( temp.length > idx ) {
		newtext += '<script';
		idx++;
		newtext += temp[idx];
	}
	temp = newtext.split( '[/script' );
	newtext = temp[0];
	idx = 0;
	while ( temp.length > idx ) {
		newtext += '</script';
		idx++;
		newtext += temp[idx];
	}
	return newtext;
}

function wppaTrimAlt( text ) {
var result;
	if ( text.length > 13 ) {
		result = text.substr( 0,10 ) + '...';
	}
	else result = text;
	return result;
}

function _wppaUpdateOgMeta( mocc ) {
	if ( wppaIsMini[mocc] ) return;	// Not in a widget
	
	var metas = jQuery( "meta" );
	var i=0;
	while ( i < metas.length ) {
		elm = metas[i];
		if ( jQuery( elm ).attr( "property" ) == "og:image" ) {
			jQuery( elm ).attr( "content", _wppaUrl[mocc][_wppaCurIdx[mocc]] );
		}
		if ( jQuery( elm ).attr( "property" ) == "og:description" ) {
			jQuery( elm ).attr( "content", _wppaOgDsc[mocc][_wppaCurIdx[mocc]] );
		}
		if ( jQuery( elm ).attr( "property" ) == "og:title" ) {
			jQuery( elm ).attr( "content", _wppaNames[mocc][_wppaCurIdx[mocc]] );
		}
		if ( jQuery( elm ).attr( "property" ) == "og:url" ) {
			jQuery( elm ).attr( "content", wppaGetCurrentFullUrl( mocc, _wppaCurIdx[mocc] ) );
		}
		i++;
	}
//	var fbc = jQuery( ".fbc-comments" );
//	jQuery( fbc ).attr( "data-href", _wppaShareUrl[mocc][_wppaCurIdx[mocc]] );	// doet het niet
}

var wppaFbInitBusy = false;
function wppaFbInit() {
	if ( ! wppaFbInitBusy ) {
		if ( typeof( FB ) != 'undefined' ) {
			wppaFbInitBusy = true;				// set busy
			setTimeout( '_wppaFbInit()', 10 ); 	// do it async over 10 ms
//			FB.init( {status : true, xfbml : true });
		}
		else {
			wppaConsoleLog( 'Fb wait' );
			setTimeout( 'wppaFbInit()', 200 );
		}
	}
	else {
		wppaConsoleLog( 'Fb Init busy' );
	}
}
function _wppaFbInit() {
	FB.init( {status : true, xfbml : true } );
	wppaFbInitBusy = false;
}

function wppaInsertAtCursor( elm, value ) {
    //IE support
    if ( document.selection ) {
        elm.focus();
        sel = document.selection.createRange();
        sel.text = value;
    }
    //MOZILLA and others
    else if ( elm.selectionStart || elm.selectionStart == '0' ) {
        var startPos = elm.selectionStart;
        var endPos = elm.selectionEnd;
        elm.value = elm.value.substring( 0, startPos )
            + value
            + elm.value.substring( endPos, elm.value.length );
        elm.selectionStart = startPos + value.length;
        elm.selectionEnd = startPos + value.length;
    } else {
        elm.value += value;
    }
}

function wppaGeoInit( mocc, lat, lon ) {
	var myLatLng = new google.maps.LatLng( lat, lon );
	var mapOptions = {
		disableDefaultUI: false,
		panControl: false,
		zoomControl: true,
		mapTypeControl: true,
		scaleControl: true,
		streetViewControl: true,
		overviewMapControl: true,	
		zoom: 10,
		center: myLatLng,
//			mapTypeId: google.maps.MapTypeId.TERRAIN,
//			mapTypeControlOptions: {
//				mapTypeIds: [ google.maps.MapTypeId.TERRAIN, google.maps.MapTypeId.SATELLITE, google.maps.MapTypeId.HYBRID ],
//				style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
//			},
	};
	var map = new google.maps.Map( document.getElementById( "map-canvas-"+mocc ), mapOptions );
	var marker = new google.maps.Marker( {
		position: myLatLng,
		map: map,
		title:""
	});
	
	google.maps.event.addListener( map, "center_changed", function() {
		// 1 second after the center of the map has changed, pan back to the
		// marker.
		window.setTimeout(function() {
		  map.panTo( marker.getPosition() );
		}, 1000 );
	});
}

function wppaEncode( xtext ) {
	var text, result;
	
	if ( typeof( xtext )=='undefined' ) return;
	
	text = xtext;
	result = text.replace( /#/g, '||HASH||' );
	text = result;
	result = text.replace( /&/g, '||AMP||' );
	text = result;
//	result = text.replace( /+/g, '||PLUS||' );
	var temp = text.split( '+' );
	var idx = 0;
	result = '';
	while ( idx < temp.length ) {
		result += temp[idx];
		idx++;
		if ( idx < temp.length ) result += '||PLUS||';
	}

//	alert( 'encoded result='+result );
	return result;
}

function wppaUrlToId( url ) {
	var temp = url.split( '/wppa/' );		// if '/wppa/' found, a wppa image
	if ( temp.length == 1 ) {	
		temp = url.split( '/upload/' );	// if '/upload/' found, a cloudinary image
	}
	if ( temp.length == 1 ) return 0;	// Still nothing, not a wppa image, return 0
	// Find image id
	temp = temp[1];
	temp = temp.split( '.' );
	temp = temp[0].replace( '/', '' );
	temp = temp.replace( '/', '' );
	temp = temp.replace( '/', '' );
	temp = temp.replace( '/', '' );
	temp = temp.replace( '/', '' );
	return temp;
}

