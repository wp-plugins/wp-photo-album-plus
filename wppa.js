// wppa.js
//
// conatins common vars and functions
// 
var wppaJsVersion = '6.1.6';

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
var wppaMasonryCols = [];
var wppaVideoPlaying = [];
var wppaAudioPlaying = [];
var wppaSlideVideoStart = false;
var wppaSlideAudioStart = false;
var wppaAudioHeight = 28;
var wppaHis = 0;
var wppaStartHtml = [];
var wppaCanAjaxRender = false;				// Assume failure
var wppaCanPushState = false;
var wppaAllowAjax = true;					// Assume we are allowed to use ajax
var wppaMaxOccur = 0;
var wppaFirstOccur = 0;
var wppaUsePhotoNamesInUrls = false;
var wppaShareHideWhenRunning = false;
var wppaCommentRequiredAfterVote = true;
var wppaTopMoc = 0;							// Set by wppa_functions.php -> function wppa_container( 'open' );
var wppaColWidth = [];						// [mocc] Set by wppa_functions.php -> function wppa_container( 'open' );
var wppaFotomotoHideWhenRunning = false;	// Set by wppa-non-admin.php -> wppa_create_wppa_init_js();
var wppaFotomotoMinWidth = 400;				// Set by wppa-non-admin.php -> wppa_create_wppa_init_js();
var wppaPhotoView = [];						// [id] Set to true by a bump viewcount to prevent duplicate bumps.

// 'Internal' variables ( private )
var _wppaId = [];
var _wppaAvg = [];
var _wppaDisc = [];
var _wppaMyr = [];
var _wppaVRU = [];							// Vote Return Url
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
var _wppaVoteInProgress = false;
var _wppaTextDelay;
var _wppaUrl = [];
var _wppaSkipRated = [];
var _wppaLbTitle = [];
var _wppaStateCount = 0;
var _wppaDidGoto = [];
var _wppaShareUrl = [];
var _wppaShareHtml = [];
var _wppaFilmNoMove = [];
var _wppaHiresUrl = [];
var _wppaIsVideo = [];
var _wppaVideoHtml = [];
var _wppaAudioHtml = [];
var _wppaVideoNatWidth = [];
var	_wppaVideoNatHeight = [];

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

// Convert a thumbnail url to a fs url
function wppaMakeFullsizeUrl( url ) {
var temp;
var temp2;

	url = url.replace( '/thumbs/', '/' );	// Not a thumb
	// Remove sizespec for Cloudinary
	temp = url.split( '//' );
	if ( temp[1] ) {
		temp2 = temp[1].split( '/' );
		url = temp[0]+'//';
	}
	else {
		temp2 = temp[0].split( '/' );
		url = '';
	}
	var j = 0;
	while ( j < temp2.length ) {
		var chunk = temp2[j];
		var w = chunk.split( '_' );
		if ( w[0] != 'w' ) {
			if ( j != 0 ) url += '/';
			url += chunk;
		}
		j++;
	}
	return url;
}

// Format a slide
function wppaFormatSlide( mocc ) {

	// vars we have
	var imgid    = 'theimg'+_wppaFg[mocc]+'-'+mocc;
	var slideid  = 'theslide'+_wppaFg[mocc]+'-'+mocc;
	var frameid  = 'slide_frame-'+mocc;
	var contw    = wppaColWidth[mocc];
	var elm      = document.getElementById( imgid );
	var audios 	 = jQuery( '.wppa-audio-'+mocc );
	
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
		}
		
		// Size audio
		if ( audios.length > 0 ) {
			var i = 0;
			jQuery( audios[i] ).css( { width:imgw, left:( contw - imgw ) / 2 } );
			i++;
		}
	}
	
	// Size Big Browse Buttons
	var bbbwidth = parseInt( framew/3 );
	var leftmarg = bbbwidth*2;
	
	jQuery( '#bbb-'+mocc+'-l' ).css( {height:frameh, width:bbbwidth, left:0});
	jQuery( '#bbb-'+mocc+'-r' ).css( {height:frameh, width:bbbwidth, left:leftmarg});
}


function wppaUpdateLightboxes() {

	if ( typeof( myLightbox )!="undefined" ) myLightbox.updateImageList();	// Lightbox-3
	wppaInitOverlay();														// Native wppa lightbox
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
	
	// Thumbframes default
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
	
	// Comalt thumbmails
	jQuery( ".wppa-com-alt-"+mocc ).css( 'width', w - wppaThumbnailAreaDelta - wppaComAltSize - 20 );
	
	// Masonry thumbnails horizontal
	var row = 1;
	var rowHeightPix;
	var rowHeightPerc = jQuery( '#wppa-mas-h-'+row+'-'+mocc ).attr( 'data-height-perc' );
	while ( rowHeightPerc ) {
		rowHeightPix = rowHeightPerc * ( w - wppaThumbnailAreaDelta ) / 100;
		jQuery( '#wppa-mas-h-'+row+'-'+mocc ).css( 'height', rowHeightPix );
		row++;
		rowHeightPerc = jQuery( '#wppa-mas-h-'+row+'-'+mocc ).attr( 'data-height-perc' );
	}

	// User upload
	jQuery( ".wppa-file-"+mocc ).css( 'width',w - 16 ); 
	
	// User upload responsive covers
	jQuery( ".wppa-file-mcr-"+mocc ).css( 'width', MCRWidth - wppaBoxDelta - 6 );
	
	// Slide
	wppaFormatSlide( mocc );
	
	// Audio on slide
	jQuery( "#audio-slide-"+mocc ).css( 'width', w - wppaBoxDelta - 6 );
	
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
	
		if ( swipeDirection == 'right' ) {	// wppaPrev( mocc );
			idx = _wppaCurIdx[mocc] - 1;
			if ( idx < 0 ) {
				if ( ! wppaSlideWrap ) {
					return;
				}
				idx = _wppaSlides[mocc].length - 1;
			}
			wppaGotoKeepState( mocc , idx );
		}
		if ( swipeDirection == 'left' ) {	// wppaNext( mocc );
			idx = _wppaCurIdx[mocc] + 1;
			if ( idx == _wppaSlides[mocc].length ) {
				if ( ! wppaSlideWrap ) {
					return;
				}
				idx = 0;
			}
			wppaGotoKeepState( mocc , idx );
		}
/*
		if ( swipeDirection == 'left' ) {
			wppaNext( wppaMocc );
			wppaMocc = 0;
		} 
		else if ( swipeDirection == 'right' ) {
			wppaPrev( wppaMocc );
			wppaMocc = 0;
		}
*/		
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
	topDivSmall = parseInt( elm.offsetTop ) - 7 - 1;
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
			'<script type="text/javascript" src="/wp-includes/js/jquery/jquery.js" ></script>' +
			'<script type="text/javascript">function wppa_downl() {'+
				'jQuery.ajax( { 	url: 		\'' + wppaAjaxUrl + '\',' + 
									'data: 		\'action=wppa' +
												'&wppa-action=makeorigname' +
												'&photo-id=' + id +
												'&from=popup' + '\',' +
									'async: 	true,' +
									'type: 		\'GET\',' +
									'timeout: 	10000,' +
									'beforeSend:	function( xhr ) {' +
						
													'},' +
									'success: 		function( result, status, xhr ) {' +
														'result = result.split( "||" );'+
														'if ( result[1] == "0" ) {'+
															'window.open( result[2] );'+
															'return true;'+
														'}'+
														'else {'+
															'alert( "Error: "+result[1]+" "+result[2] );'+
															'return false;'+
														'}'+

													'},' +
									'error: 		function( xhr, status, error ) {' +
														'wppaConsoleLog( \'wppaFullPopUp failed. Error = \' + error + \', status = \' + status, \'force\' );' +
													'},' +
								'} );' +				
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
var wppaOvlIsSingle;
var wppaOvlRunning = false;
var wppaOvlVideoHtmls;
var wppaOvlVideoHtml;
var wppaOvlAudioHtmls;
var wppaOvlAudioHtml;
var wppaOvlVideoNaturalWidths;
var wppaOvlVideoNaturalWidth;	
var wppaOvlVideoNaturalHeights;
var wppaOvlVideoNaturalHeight;
var wppaOvlMode = 'normal';
var wppaOvlVideoPlaying = false;
var wppaOvlAudioPlaying = false;

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
var wppaOvlFsPhotoId = 0;
var wppaPhotoId = 0;
var wppaOvlVideoStart = false;
var wppaOvlAudioStart = false;



function wppaConsoleLog( arg, force ) {
//wppaDebug=true;//diagnostic
	if ( typeof( console ) != 'undefined' && ( wppaDebug || force == 'force' ) ) {
		console.log( arg );
	}
}

function wppaRepairScriptTags( text ) {
var temp;
var newtext;

	// Just to be sure we do not run into undefined error
	if ( typeof( text ) == 'undefined' ) return '';
	
	temp = text.split( '[script' );
	if ( temp.length == 1 ) return text;
	
	newtext = temp[0];
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

function wppaRepairBrTags( text ) {
var newtext;

	// Just to be sure we do not run into undefined error
	if ( typeof(text) == 'undefined' ) return '';
	
	newtext = text.replace( '[br /]', '<br />' );
	return newtext;
}

function wppaTrimAlt( text ) {
var newtext;

	// Just to be sure we do not run into undefined error
	if ( typeof(text) == 'undefined' ) return '';
	
	if ( text.length > 13 ) {
		newtext = text.substr( 0,10 ) + '...';
	}
	else newtext = text;
	return newtext;
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

	return result;
}

function wppaUrlToId( url ) {
	var temp = url.split( '/wppa/' );		// if '/wppa/' found, a wppa image
	if ( temp.length == 1 ) {	
		temp = url.split( '/upload/' );	// if '/upload/' found, a cloudinary image
	}
	if ( temp.length == 1 ) {
		return 0;	// Still nothing, not a wppa image or ahires image, return 0
	}
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


wppaConsoleLog( 'wppa.js version '+wppaJsVersion+' loaded.', 'force' );
