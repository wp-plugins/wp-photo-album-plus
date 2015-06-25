// wppa-lightbox.js
//
// Conatins lightbox modules
// Dependancies: wppa.js and default wp jQuery library
// 
var wppaLightboxVersion = '6.1.15';

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
			wppaStopVideo( mocc );
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
			if ( ! elem ) return;
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

// Show the lightbox overlay.
// arg is either numeric ( index to current lightbox set ) or 'this' for a single image
// This function consists of 4 steps if not in fullscreen mode: 
// wppaOvlShow() wppaOvlShow2(), wppaOvlShow3() and wppaOvlShow4() with rendering in between the steps.
// If in fullscreen mode, wppaOvlShow() calls wppaOvlShowFull().
function wppaOvlShow( arg ) {
wppaConsoleLog( 'wppaOvlShow arg='+arg );

	if ( wppaOvlFirst ) {
	
		// Install keyboard handler
		jQuery( document ).on('keydown', wppaOvlKeyboardHandler	);
		
		// Stop all slideshows
		var occ = 0;
		while ( occ < wppaTopMoc ) {
			occ++;
			wppaStopShow( occ );
		}
		
		wppaOvlFirst = false;
	}

	// Prevent Weaver ii from hiding us
	jQuery( '#weaver-final' ).removeClass( 'wvr-hide-bang' );
	
	// Display spinner
	jQuery( '#wppa-overlay-sp' ).css( {left: ( jQuery( window ).width()/2 )-16, top: ( jQuery( window ).height()/2 )-16, visibility: 'visible'});
	
	var href;
	if ( parseInt( arg ) == arg ) {	// Arg is Numeric
		if ( arg != -1 ) {
			wppaOvlUrl 					= wppaOvlUrls[arg];
			wppaOvlTitle 				= wppaOvlTitles[arg];
			wppaOvlIdx 					= arg;
			wppaOvlVideoHtml 			= wppaOvlVideoHtmls[arg];
			wppaOvlAudioHtml 			= wppaOvlAudioHtmls[arg];
			wppaOvlVideoNaturalWidth 	= wppaOvlVideoNaturalWidths[arg];
			wppaOvlVideoNaturalHeight 	= wppaOvlVideoNaturalHeights[arg];
		} // else redo the same single
	}
	else {						// Arg is 'this' arg
		wppaOvlIdx = -1;	// Assume single
		wppaOvlUrl = arg.href;
		if ( jQuery( arg ).attr( 'data-lbtitle' ) ) {
			wppaOvlTitle = wppaRepairScriptTags( jQuery( arg ).attr( 'data-lbtitle' ) );
		}
		else {
			wppaOvlTitle = wppaRepairScriptTags( arg.title );
		}
		wppaOvlVideoHtml 			= jQuery( arg ).attr( 'data-videohtml' ) ? decodeURI( jQuery( arg ).attr( 'data-videohtml' ) ) : '';
		wppaOvlAudioHtml 			= jQuery( arg ).attr( 'data-audiohtml' ) ? decodeURI( jQuery( arg ).attr( 'data-audiohtml' ) ) : '';

		wppaOvlVideoNaturalWidth 	= jQuery( arg ).attr( 'data-videonatwidth' ) ? jQuery( arg ).attr( 'data-videonatwidth' ) : '';
		wppaOvlVideoNaturalHeight 	= jQuery( arg ).attr( 'data-videonatheight' ) ? jQuery( arg ).attr( 'data-videonatheight' ) : '';
		
		var rel;
		if ( arg.rel ) {
			rel = arg.rel;
		}
		else if ( jQuery( arg ).attr( 'data-rel' ) ) {
			rel = jQuery( arg ).attr( 'data-rel' );
		}
		else {
			rel = false;
		}
		
		var temp = rel.split( '[' );
		
		if ( temp[1] ) {	// We are in a set
			wppaOvlUrls 				= [];
			wppaOvlTitles 				= [];
			wppaOvlVideoHtmls 			= [];
			wppaOvlAudioHtmls 			= [];
			wppaOvlVideoNaturalWidths 	= [];	
			wppaOvlVideoNaturalHeights 	= [];			
			var setname 				= temp[1];
			var anchors 				= jQuery( 'a' );
			var anchor;
			var i, j 					= 0;
			wppaOvlIdx 					= -1;
			
			// Save the set
			for ( i=0;i<anchors.length;i++ ) {
				anchor = anchors[i];
//				if ( anchor.rel ) {
//					temp = anchor.rel.split( "[" );
//				}
//				else 
				if ( jQuery( anchor ).attr( 'data-rel' ) ) {
					temp = jQuery( anchor ).attr( 'data-rel').split( "[" );
				}
				else {
					temp = false;
				}
				if ( temp.length > 1 ) {
					if ( temp[0] == 'wppa' && temp[1] == setname ) {	// Same set
						wppaOvlUrls[j] = anchor.href;
						if ( jQuery( anchor ).attr( 'data-lbtitle' ) ) {
							wppaOvlTitles[j] = wppaRepairScriptTags( jQuery( anchor ).attr( 'data-lbtitle' ) );
						}
						else {
							wppaOvlTitles[j] = wppaRepairScriptTags( anchor.title );
						}
						wppaOvlVideoHtmls[j] 			= jQuery( anchor ).attr( 'data-videohtml' ) ? decodeURI( jQuery( anchor ).attr( 'data-videohtml' ) ) : '';
						wppaOvlAudioHtmls[j] 			= jQuery( anchor ).attr( 'data-audiohtml' ) ? decodeURI( jQuery( anchor ).attr( 'data-audiohtml' ) ) : '';

						wppaOvlVideoNaturalWidths[j] 	= jQuery( anchor ).attr( 'data-videonatwidth' ) ? jQuery( anchor ).attr( 'data-videonatwidth' ) : '';	
						wppaOvlVideoNaturalHeights[j] 	= jQuery( anchor ).attr( 'data-videonatheight' ) ? jQuery( anchor ).attr( 'data-videonatheight' ) : '';
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
			wppaOvlVideoNaturalWidths = false;
			wppaOvlVideoNaturalHeights = false;
			wppaOvlIdx = -1;
		}
	}

	wppaPhotoId = wppaUrlToId( wppaOvlUrl );

	_bumpViewCount( wppaPhotoId );
	
	var wppaIsVideo 	= wppaOvlVideoHtml != '';
	var wppaHasAudio 	= wppaOvlAudioHtml != '';

	// Fullsize?
	if ( wppaOvlMode != 'normal' ) {
		var html;
		
		// Init background
		jQuery( '#wppa-overlay-bg' ).fadeTo( 300, wppaOvlOpacity );	// show black background first
		
		// Fullsize Video
		if ( wppaIsVideo ) {
			html = 
			'<div id="wppa-ovl-full-bg" style="position:fixed; width:'+jQuery( window ).width()+'px; height:'+jQuery( window ).height()+'px; left:0px; top:0px; text-align:center;" >'+
				'<video id="wppa-overlay-img" controls preload="metadata"' +
					( wppaOvlVideoStart ? ' autoplay' : '' ) +
					' ontouchstart="wppaTouchStart( event, \'wppa-overlay-img\', -1 );"' +
					' ontouchend="wppaTouchEnd( event );"' +
					' ontouchmove="wppaTouchMove( event );"' +
					' ontouchcancel="wppaTouchCancel( event );"' +
					' onpause="wppaOvlVideoPlaying = false;"' +
					' onplay="wppaOvlVideoPlaying = true;"' +
					' style="border:none; width:'+jQuery( window ).width()+'px; box-shadow:none; position:absolute;" >'+
						wppaOvlVideoHtml+
				'</video>'+
				'<div style="height: 20px; width: 100%; position:absolute; top:0; left:0;" onmouseover="jQuery(\'#wppa-ovl-legenda-2\').css(\'visibility\',\'visible\');" onmouseout="jQuery(\'#wppa-ovl-legenda-2\').css(\'visibility\',\'hidden\');wppaShowLegenda=\'hidden\';" >';
				if ( wppaOvlShowLegenda ) {
					html +=
					'<div id="wppa-ovl-legenda-2" style="position:fixed; left:0; top:0; background-color:'+(wppaOvlTheme == 'black' ? '#272727' : '#a7a7a7')+'; color:'+(wppaOvlTheme == 'black' ? '#a7a7a7' : '#272727')+'; visibility:'+wppaShowLegenda+';" >'+
						'Mode='+wppaOvlMode+'. '+( wppaOvlIsSingle ? wppaOvlFullLegendaSingle : wppaOvlFullLegenda ) +
					'</div>';
				}
				html +=
				'</div>';
			'</div>';
		}
		// Fullsize Photo
		else {
			html = 
			'<div id="wppa-ovl-full-bg" style="position:fixed; width:'+jQuery( window ).width()+'px; height:'+jQuery( window ).height()+'px; left:0px; top:0px; text-align:center;" >'+
				'<img id="wppa-overlay-img"'+
					' ontouchstart="wppaTouchStart( event, \'wppa-overlay-img\', -1 );"'+
					' ontouchend="wppaTouchEnd( event );"'+
					' ontouchmove="wppaTouchMove( event );"'+
					' ontouchcancel="wppaTouchCancel( event );"'+
					' src="'+wppaOvlUrl+'"'+
					' style="border:none; width:'+jQuery( window ).width()+'px; visibility:hidden; box-shadow:none; position:absolute;"'+
				' />';
				if ( wppaHasAudio ) {
				html += '<audio' + 
							' id="wppa-overlay-audio"' +
							' class="wppa-overlay-audio"' +
							' data-from="wppa"' +
							' preload="metadata"' +
							( ( wppaOvlAudioStart ) ? ' autoplay' : '' ) +
							' onpause="wppaOvlAudioPlaying = false;"' +
							' onplay="wppaOvlAudioPlaying = true;"' +
							' style="' +
								'width:100%;' +
								'position:absolute;' +
								'left:0px;' +
								'bottom:0px;' +
								'padding:0;' +
								'"' +
							' controls' +
							' >' +
							wppaOvlAudioHtml +
						'</audio>';
				}
				html +=
				'<div style="height: 20px; width: 100%; position:absolute; top:0; left:0;" onmouseover="jQuery(\'#wppa-ovl-legenda-2\').css(\'visibility\',\'visible\');" onmouseout="jQuery(\'#wppa-ovl-legenda-2\').css(\'visibility\',\'hidden\');wppaShowLegenda=\'hidden\';" >';
				if ( wppaOvlShowLegenda ) {
					html +=
					'<div id="wppa-ovl-legenda-2" style="position:fixed; left:0; top:0; background-color:'+(wppaOvlTheme == 'black' ? '#272727' : '#a7a7a7')+'; color:'+(wppaOvlTheme == 'black' ? '#a7a7a7' : '#272727')+'; visibility:'+wppaShowLegenda+';" >'+
						'Mode='+wppaOvlMode+'. '+( wppaOvlIsSingle ? wppaOvlFullLegendaSingle : wppaOvlFullLegenda )+
					'</div>';
				}
				html +=
				'</div>';
			'</div>';
		}

		// Replacing the html stops a running video,
		// so we only replace html on a new id, or a photo without audio
		if ( ( ! wppaIsVideo && ! wppaHasAudio ) || wppaOvlFsPhotoId != wppaPhotoId || wppaPhotoId == 0 ) {
			wppaStopVideo( 0 );
			wppaStopAudio();
			jQuery( '#wppa-overlay-ic' ).html( html );
		}

		wppaOvlIsVideo = wppaIsVideo;
		setTimeout( 'wppaOvlShowFull()', 10 );
		if ( wppaIsVideo || wppaHasAudio ) {
			setTimeout( 'wppaOvlUpdateFsId()', 2000 );
		}
		else {
			wppaOvlFsPhotoId = 0;
		}
		return false;
	}
	
	// NOT fullsize
	else {
		wppaOvlFsPhotoId = 0; // Reset ovl fullscreen photo id
		wppaPhotoId = 0;
		var mw = 250;
		
		wppaStopVideo( 0 );

		jQuery( '#wppa-overlay-bg' ).fadeTo( 300, wppaOvlOpacity );
		var lft = ( jQuery( window ).width()/2-125 )+'px';
		var ptp = ( jQuery( window ).height()/2-125 )+'px';

		jQuery( '#wppa-overlay-ic' ).css( {left: lft, paddingTop: ptp});
		var txtcol = wppaOvlTheme == 'black' ? '#a7a7a7' : '#272727';	// Normal font
		var qtxtcol = wppaOvlTheme == 'black' ? '#a7a7a7' : '#575757';	// Bold font
		if ( wppaOvlFontColor ) txtcol = wppaOvlFontColor;
		var startstop = wppaOvlRunning ? wppaStop : wppaStart;
		var html = '';
		if ( wppaOvlShowStartStop) {
			html += '<div id="wppa-overlay-start-stop" style="position:absolute; left:0px; top:'+( wppaOvlPadTop-1 )+'px; visibility:hidden; box-shadow:none; font-family:helvetica; font-weight:bold; font-size:14px; color:'+qtxtcol+'; cursor:pointer; " onclick="wppaOvlStartStop()" ontouchstart="wppaOvlStartStop()" >'+startstop+'</div>';
		}
		html += 
			'<div id="wppa-overlay-qt-txt"  style="position:absolute; right:16px; top:'+( wppaOvlPadTop-1 )+'px; visibility:hidden; box-shadow:none; font-family:helvetica; font-weight:bold; font-size:14px; color:'+qtxtcol+'; cursor:pointer; " onclick="wppaOvlHide()" ontouchstart="wppaOvlHide()" >'+wppaOvlCloseTxt+'&nbsp;&nbsp;</div>'+
			'<img id="wppa-overlay-qt-img"  src="'+wppaImageDirectory+'smallcross-'+wppaOvlTheme+'.gif'+'" style="position:absolute; right:0; top:'+wppaOvlPadTop+'px; visibility:hidden; box-shadow:none; cursor:pointer" onclick="wppaOvlHide()" ontouchstart="wppaOvlHide()" >';
					
		// Not Fullsize Video
		if ( wppaIsVideo ) {
		
			html += '<video' +
					' id="wppa-overlay-img"' +
					' preload="metadata"' +
					( wppaOvlVideoStart ? ' autoplay' : '' ) +
					' onpause="wppaOvlVideoPlaying = false;"' +
					' onplay="wppaOvlVideoPlaying = true;"' +
					' ontouchstart="wppaTouchStart( event, \'wppa-overlay-img\', -1 );"' +
					' ontouchend="wppaTouchEnd( event );"' +
					' ontouchmove="wppaTouchMove( event );"' +
					' ontouchcancel="wppaTouchCancel( event );" ' +
					' style="' +
						'border-width:16px;' +
						'border-style:solid;' +
						'border-color:'+wppaOvlTheme+';' +
						'margin-bottom:-15px;' +
						'max-width:'+mw+'px;' +
						'visibility:hidden;' +
						'box-shadow:none;"' +
					' controls' +
					' >'+wppaOvlVideoHtml+'</video>';
					
			wppaOvlIsVideo = true;
		}
		
		// Not fullsize photo
		else {
		
			html += '<img' +
						' id="wppa-overlay-img"'+
						' ontouchstart="wppaTouchStart( event, \'wppa-overlay-img\', -1 );"' +
						' ontouchend="wppaTouchEnd( event );"' +
						' ontouchmove="wppaTouchMove( event );"' +
						' ontouchcancel="wppaTouchCancel( event );"' +
						' src="'+wppaOvlUrl+'"' +
						' style="' +
							'border-width:16px;' +
							'border-style:solid;' +
							'border-color:'+wppaOvlTheme+';' +
							'margin-bottom:-15px;' +
							'max-width:'+mw+'px;' +
							'visibility:hidden;' +
							'box-shadow:none;"' +
					' />';
			
			// Audio on not fullsize
			if ( wppaHasAudio ) { //wppaOvlAudioHtml != '' ) {
				html += '<audio' + 
							' id="wppa-overlay-audio"' +
							' class="wppa-overlay-audio"' +
							' data-from="wppa"' +
							' preload="metadata"' +
					//		( ( wppaOvlAudioStart && ! wppaOvlAudioPlaying ) ? ' autoplay' : '' ) +
							' onpause="wppaOvlAudioPlaying = false;"' +
							' onplay="wppaOvlAudioPlaying = true;"' +
							' style="' +
								'width:100%;' +
								'position:relative;' +
								'top:-'+( wppaAudioHeight + 16 )+'px;' +
								'padding:16px;' +
								'background-color:transparent;' +
								'box-sizing:border-box;' +
								'"' +
							' controls' +
							' >' +
							wppaOvlAudioHtml +
						'</audio>';
			}
			wppaOvlIsVideo = false;
		}
		html += '<div id="wppa-overlay-txt-container"' +
					' style="' +
						'position:relative;' +
						( wppaHasAudio ? 'top:-'+( wppaAudioHeight + 32 )+'px;' : '' ) +
						'padding:10px;' +
						'background-color:'+wppaOvlTheme+'; color:'+txtcol+'; text-align:center; font-family:'+wppaOvlFontFamily+'; font-size: '+wppaOvlFontSize+'px; font-weight:'+wppaOvlFontWeight+'; line-height:'+wppaOvlLineHeight+'px; visibility:hidden; box-shadow:none;" ><div>';

		jQuery( '#wppa-overlay-ic' ).html( html );
		setTimeout( 'wppaOvlShow2()', 10 );
		return false;
	}
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

// Show fullscreen lightbox image
function wppaOvlShowFull() {

	var img;
	var natWidth;
	var natHeight;
	
	// Find out if the picture is more portrait than the screen
	if ( wppaOvlIsVideo ) {
		img 		= document.getElementById( 'wppa-overlay-img' );
		natWidth 	= wppaOvlVideoNaturalWidth;
		natHeight 	= wppaOvlVideoNaturalHeight;
	}
	else {
		img 		= document.getElementById( 'wppa-overlay-img' );
		if ( ! img || ! img.complete ) {
			setTimeout( 'wppaOvlShowFull()', 10 );	// Wait for load complete
			return;
		}
		natWidth 	= img.naturalWidth;
	 	natHeight 	= img.naturalHeight;
	}

	var screenRatio = jQuery( window ).width() / jQuery( window ).height();
	var imageRatio 	= natWidth / natHeight; 
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
				margLeft 	= ( jQuery( window ).width() - jQuery( window ).height() * imageRatio ) / 2;
				margTop 	= 0;
				imgHeight 	= jQuery( window ).height();
				imgWidth 	= jQuery( window ).height() * imageRatio;
			}
			else {
				margLeft 	= 0;
				margTop 	= ( jQuery( window ).height() - jQuery( window ).width() / imageRatio ) / 2;
				imgHeight 	= jQuery( window ).width() / imageRatio;
				imgWidth 	= jQuery( window ).width();
			}
			break;
		case 'stretched':
			margLeft 	= 0;
			margTop 	= 0;
			imgHeight 	= jQuery( window ).height();
			imgWidth 	= jQuery( window ).width();
			break;
		case 'clipped':
			if ( screenRatio > imageRatio ) {	// Picture is more portrait
				margLeft 	= 0;
				margTop 	= ( jQuery( window ).height() - jQuery( window ).width() / imageRatio ) / 2;
				imgHeight 	= jQuery( window ).width() / imageRatio;
				imgWidth 	= jQuery( window ).width();
			}
			else {
				margLeft 	= ( jQuery( window ).width() - jQuery( window ).height() * imageRatio ) / 2;
				margTop 	= 0;
				imgHeight 	= jQuery( window ).height();
				imgWidth 	= jQuery( window ).height() * imageRatio;
			}
			break;
		case 'realsize':
			margLeft 	= ( jQuery( window ).width() - natWidth ) / 2;
			if ( margLeft < 0 ) {
				scrollLeft 	= - margLeft;
				margLeft 	= 0;
			}
			margTop 	= ( jQuery( window ).height() - natHeight ) / 2;
			if ( margTop < 0 ) {
				scrollTop 	= - margTop;
				margTop 	= 0;
			}
			imgHeight 	= natHeight;
			imgWidth 	= natWidth;
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

// This function is called after a timeout to update fullsize photo id. 
// Used to determine if a video/audio must restart
function wppaOvlUpdateFsId() {
	wppaOvlFsPhotoId = wppaPhotoId;
}

// Start audio on the lightbox view
function wppaOvlStartAudio() {

	// Due to a bug in jQuery ( jQuery.play() does not exist ), must do myself:
	var elm = document.getElementById( 'wppa-overlay-audio' );
	if ( elm ) {
		if ( typeof( elm.play ) == 'function' ) {
			elm.play();
			wppaConsoleLog('Audio play '+'wppa-overlay-audio', 'force');
		}
	}
}
// Step through the ring of fullscreen modes
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

// Start / stop lightbox slideshow
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

// Start lb slideshow
function wppaOvlRun() {
wppaConsoleLog( 'wppaOvlRun, running='+wppaOvlRunning );

	if ( ! wppaOvlRunning ) return;
	
	if ( wppaOvlVideoPlaying || wppaOvlAudioPlaying ) {
		setTimeout( 'wppaOvlRun()', 500 ); 
		return;
	}

	var next;
	if ( wppaOvlIdx >= ( wppaOvlUrls.length-1 ) ) next = 0;
	else next = wppaOvlIdx + 1;
	
	wppaOvlFsPhotoId = 0;
	wppaPhotoId = 0;
	
	wppaOvlShow( next );

	setTimeout( 'wppaOvlRun()', wppaOvlSlideSpeed );
}

// One back in the set
function wppaOvlShowPrev() {
wppaConsoleLog( 'wppaOvlShowPrev' );

	wppaOvlFsPhotoId = 0;
	wppaPhotoId = 0;
	
	if ( wppaOvlIsSingle ) return false;
	if ( wppaOvlIdx < 1 ) {
		wppaOvlIdx = wppaOvlUrls.length;	// Restart at last
	}
	wppaOvlShow( wppaOvlIdx-1 );
	return false;
}

// One further in the set
function wppaOvlShowNext() {
wppaConsoleLog( 'wppaOvlShowNext' );

	wppaOvlFsPhotoId = 0;
	wppaPhotoId = 0;
	
	if ( wppaOvlIsSingle ) return false;
	if ( wppaOvlIdx >= ( wppaOvlUrls.length-1 ) ) {
		wppaOvlIdx = -1;	// Restart at first
	}
	wppaOvlShow( wppaOvlIdx+1 );
	return false;
}

// Adjust display sizes
// Two parts with rendering in between
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

	var iw = jQuery( window ).width();
	var ih = jQuery( window ).height();	
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
	
	var elm = document.getElementById( 'wppa-overlay-img' );
	if ( ! elm ) return;	// quit inbetween
	var cw = elm.clientWidth;
	var txtwidth;
	if ( wppaOvlRunning ) txtwidth = cw + 12;
	else txtwidth = cw - 80;

	jQuery( '#wppa-overlay-img' ).css( {width: cw});	// Req'd for ver 4 browsers
	jQuery( '#wppa-overlay-ic' ).css( {width: cw+32});	// ditto
	jQuery( '#wppa-overlay-txt' ).css( {width: txtwidth+'px', visibility: 'visible'});

	return true;
}

// Quit lightbox mode
function wppaOvlHide() {
wppaConsoleLog( 'wppaOvlHide' );

	// Stop audio
	wppaStopAudio();
	
	// Clear image container
	jQuery( '#wppa-overlay-ic' ).html( '' );
	jQuery( '#wppa-overlay-ic' ).css( {paddingTop: 0});
	
	// Remove background
	jQuery( '#wppa-overlay-bg' ).fadeOut( 300 );
	
	// Remove kb handler
	jQuery( document ).off( 'keydown', wppaOvlKeyboardHandler );
	
	// Reset switches
	wppaOvlFirst = true;
	wppaOvlRunning = false;
	wppaOvlMode = 'normal';
	jQuery( '#wppa-overlay-sp' ).css({visibility:'hidden'});
}

// Perform onclick action
function wppaOvlOnclick( event ) {
	switch ( wppaOvlOnclickType ) {
		case 'none':
			break;
		case 'close':
			wppaOvlHide();
			break;
		case 'browse':
			var x = event.screenX - window.screenX;
			if ( x < jQuery( window ).width() / 2 ) wppaOvlShowPrev();
			else wppaOvlShowNext();
			break;
		default:
			alert( 'Unimplemented action: '+wppaOvlOnclickType );
			break;
	}
	return true;
}

// Initialize <a> tags with onclick and ontouchstart events to lightbox
function wppaInitOverlay() {
wppaConsoleLog( 'wppaInitOverlay' );

	var anchors = jQuery( 'a' );
	var anchor;
	var i;
	var temp = [];

	wppaOvlFsPhotoId = 0; // Reset ovl fullscreen photo id
	wppaPhotoId = 0;
		
	for ( i = 0; i < anchors.length; i++ ) {
		
		anchor = anchors[i];
		if ( jQuery( anchor ).attr( 'data-rel' ) ) {
			temp = jQuery( anchor ).attr( 'data-rel' ).split( "[" );
		}
		else if ( anchor.rel ) {
			temp = anchor.rel.split( "[" );
		}
		else {
			temp[0] = '';
		}

		if ( temp[0] == 'wppa' ) {
		
			// found one
			wppaWppaOverlayActivated = true;
			
			// Install onclick handler
			jQuery( anchor ).click( function( event ) {
				wppaOvlShow( this );
				event.preventDefault();
			}); 
			
			// Install ontouchstart handler
			jQuery( anchor ).on( "touchstart", function( event ) {
				wppaOvlShow( this );
				// event.preventDefault();
			}); 
		}
	}
}

// This module is intented to be used in any onclick definition that opens or closes a part of the photo description.
// this will automaticly adjust the picturesize so that the full description will be visible.
// Example: <a onclick="myproc()" >Show Details</a>
// Change to: <a onclick="myproc(); wppaOvlResize()" >Show Details</a>
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
	
	if ( wppaOvlAudioStart && ! wppaOvlAudioPlaying ) {
		setTimeout( 'wppaOvlStartAudio()', 1010 );
	}
}


wppaConsoleLog( 'wppa-lightbox.js version '+wppaLightboxVersion+' loaded.', 'force' );