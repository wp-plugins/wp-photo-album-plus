// Slide show variables and functions
// This is wppa_slideshow.js version 2.0.0
//

var wppa_slides = new Array();
var wppa_names = new Array();
var wppa_descs = new Array();
var wppa_id = -1;
var wppa_next_id = 0;
var wppa_timeout = 2500;
var wppa_timer;
var wppa_timeron = false;
var wppa_slideshow;
var wppa_foreground = 0;
var wppa_animation_speed;
var wppa_imgdir;
var wppa_first_spinner = true;
var wppa_busy = false;
var wppa_first = true;
var wppa_last_occ = 0;
    
function wppa_store_slideinfo(id, url, size, name, desc) {
    wppa_slides[id] = ' src="' + url + '" alt="' + name + '" class="theimg big" ' + ' style="' + size + ';">'; // opacity:0; filter:alpha(opacity=0);" >';
    wppa_names[id] = name;
    wppa_descs[id] = desc;
}

function wppa_next_slide() {
	var fg = wppa_foreground;
	var bg = 1 - fg;
	// Kill timer
	clearTimeout(wppa_timer);
	// Find index of next slide if in auto mode
	if (wppa_timeron) {
		wppa_next_id = wppa_id + 1;
		if (wppa_next_id == wppa_slides.length) wppa_next_id = 0;
	}
    // first:
    if (wppa_first) {
	    if (wppa_id != -1) {
			jQuery("#theslide0").html('<img id="theimg0" ' + wppa_slides[wppa_id]);
			jQuery("#theimg0").hide();
			jQuery("#theslide0").css('zIndex','901');
		}
		jQuery("#theslide1").html('<img id="theimg1" ' + wppa_slides[wppa_next_id]);
		jQuery("#theimg1").hide();	      
		jQuery("#theslide1").css('zIndex','900');
	
		wppa_load_spinner();
	    
		jQuery("#imagedesc").html('&nbsp;' + wppa_descs[wppa_id] + '&nbsp;');
		jQuery("#imagetitle").html('&nbsp;' + wppa_names[wppa_id] + '&nbsp;');
    }
    // end first
    else {
    	// load next img (backg)
		jQuery("#theslide"+bg).html('<img id="theimg'+bg+'" '+wppa_slides[wppa_next_id]);
		jQuery("#theslide"+bg).css('zIndex', '900');
		jQuery("#theslide"+fg).css('zIndex', '901');
		jQuery("#theimg"+bg).hide();
    }
	wppa_first = false;
	
    // Next is now current
    wppa_id = wppa_next_id;
    
	wppa_timer = setTimeout('wppa_fade()', 10);
}

function wppa_fade() {
	var fg, bg;
			
	clearTimeout(wppa_timer);
	fg = wppa_foreground;
	bg = 1 - fg;
	
	// Wait for load complete
	if (!document.getElementById('theimg'+bg).complete) {
		wppa_timer = setTimeout('wppa_fade()', 100);	// Try again after 100 ms
		return;
	}

	// Remove spinner
	wppa_unload_spinner();
	
	// Hide subtitles
	jQuery("#imagedesc").html('&nbsp;&nbsp;');
	jQuery("#imagetitle").html('&nbsp;&nbsp;');
	
	// change foreground
	wppa_foreground = 1 - wppa_foreground;
	fg = wppa_foreground;
	bg = 1 - fg;
	jQuery("#theslide"+bg).css('zIndex', '900');
	jQuery("#theslide"+fg).css('zIndex', '901');
	
	wppa_timer = setTimeout('wppa_fade_fade()', 10);
}

function wppa_fade_fade() {
	var fg;
	var bg;
	fg = wppa_foreground;
	bg = 1 - fg;

	clearTimeout(wppa_timer);

	jQuery("#theimg" + bg).fadeTo(wppa_animation_speed, 0); //animate({opacity:0}, wppa_animation_speed);	// Req'd for change in portrait/landscape vv
	jQuery("#theimg" + fg).fadeTo(wppa_animation_speed, 1, wppa_after_fade()); //animate({opacity:1}, wppa_animation_speed, wppa_after_fade());
}

function wppa_after_fade() {
	clearTimeout(wppa_timer);
	// Restore subtitles
	document.getElementById('imagedesc').innerHTML = '&nbsp;' + wppa_descs[wppa_id] + '&nbsp;';
	document.getElementById('imagetitle').innerHTML = '&nbsp;' + wppa_names[wppa_id] + '&nbsp;';
	// Wait for next slide
	if (wppa_timeron) {
		wppa_timer = setTimeout('wppa_next_slide()', wppa_timeout); 
	}
	else {
		jQuery(".arrow").stop().fadeTo(400,1);
		wppa_busy = false;
	}
}
 
 
function wppa_next() {
	if (wppa_busy) return;
	wppa_busy = true;
	wppa_next_id = wppa_id + 1;
	if (wppa_next_id == wppa_slides.length) wppa_next_id = 0;
	jQuery(".arrow").stop().fadeTo(400,0.2);
	wppa_next_slide();
}

function wppa_prev() {
	if (wppa_busy) return;
	wppa_busy = true;
	wppa_next_id = wppa_id - 1;
	if (wppa_next_id < 0) wppa_next_id = wppa_slides.length - 1;
	jQuery(".arrow").stop().fadeTo(400,0.2);
	wppa_next_slide();
}

function wppa_startstop(idx) {
	if (idx != -1) {
		document.getElementById('startstop').innerHTML='Start'+' '+wppa_slideshow; 
		document.getElementById('speed0').style.visibility="hidden";
		document.getElementById('speed1').style.visibility="hidden";
		wppa_next_id = idx;
		wppa_id = idx;
		wppa_next_slide();
	}
    if (wppa_timeron) { // stop it
        clearTimeout(wppa_timer);
        clearTimeout(wppa_timer);
        wppa_timeron = false;
        document.getElementById('startstop').innerHTML='Start'+' '+wppa_slideshow;  
		document.getElementById('prev-arrow').style.visibility="visible";
		document.getElementById('next-arrow').style.visibility="visible";
		document.getElementById('speed0').style.visibility="hidden";
		document.getElementById('speed1').style.visibility="hidden";
		document.getElementById('bc-pname').innerHTML = wppa_names[wppa_id];
    }
    else if (idx == -1) {
        wppa_timeron = true;
        document.getElementById('startstop').innerHTML='Stop';
        wppa_next_slide();
		document.getElementById('prev-arrow').style.visibility="hidden";
		document.getElementById('next-arrow').style.visibility="hidden";
		document.getElementById('speed0').style.visibility="visible";
		document.getElementById('speed1').style.visibility="visible";
		document.getElementById('bc-pname').innerHTML = wppa_slideshow;
    }
}
    
function wppa_speed(faster) {
    if (faster) {
        if (wppa_timeout > 500) wppa_timeout /= 1.5;
    }
    else {
        if (wppa_timeout < 60000) wppa_timeout *= 1.5;
    }
}

var topDivBig, topDivSmall, leftDivBig, leftDivSmall;
var heightImgBig, heightImgSmall, widthImgBig, widthImgSmall;

function wppa_popup(elm, id, occ) {
	// Thumbnail
	var thA;
	var thImg;
	// Popup
	var puDiv;
	var puA;
	var puImg;
	// Stop if still running
	if (wppa_last_occ > 0) {
		clearTimeout(wppa_timer);	// due to callback bug, see below
		jQuery('#wppa-popup-'+wppa_last_occ).stop(true,true);
		jQuery('#wppa-img-'+wppa_last_occ).stop(true,true);
	}
	wppa_last_occ = occ;
	// Coords and sizes
	thImg = elm;
	thA = document.getElementById('a-'+id+'-'+occ);
	// Setup the popup window
	puDiv = document.getElementById('wppa-popup-'+occ);
	puDiv.innerHTML = '<a id="wppa-a" href=""><img id="wppa-img-'+occ+'" class="wppa-img" src="#" /></a>';
	puA = document.getElementById('wppa-a');
	puImg = document.getElementById('wppa-img-'+occ);
	puA.href = thA.href;
	puImg.src = thImg.src;
	// Compute starting coords
	leftDivSmall = parseInt(thImg.offsetLeft) - 7 - 4 - 1; // thumbnail_area:padding, wppa-img:padding, wppa-border; jQuery().css("padding") does not work for padding in css file, only when litaral in the tag
	topDivSmall = parseInt(thImg.offsetTop) - 7 - 4 - 1;		
	// Compute starting sizes
	widthImgSmall = parseInt(thImg.clientWidth);
	heightImgSmall = parseInt(thImg.clientHeight);
	// Comute ending sizes
	widthImgBig = parseInt(puImg.clientWidth) - 8;	// == - 2 * padding
	heightImgBig = parseInt(puImg.clientHeight) - 8;
	// Compute ending coords
	leftDivBig = leftDivSmall - parseInt((widthImgBig - widthImgSmall) / 2);
	topDivBig = topDivSmall - parseInt((heightImgBig - heightImgSmall) / 2);
	// Setup starting properties
	jQuery('#wppa-popup-'+occ).css({"marginLeft":leftDivSmall+"px","marginTop":topDivSmall+"px"});
	jQuery('#wppa-img-'+occ).css({"width":widthImgSmall+"px","height":heightImgSmall+"px"});
	// Do the animation
	jQuery('#wppa-popup-'+occ).stop().animate({"marginLeft":leftDivBig+"px","marginTop":topDivBig+"px"}, 400);
	jQuery('#wppa-img-'+occ).stop().animate({"width":widthImgBig+"px","height":heightImgBig+"px"}, 400);
/*
	var dbg = document.getElementById('wppa-debug');
	dbg.innerHTML = '<small>'+topDivBig+', '+topDivSmall+', '+leftDivBig+', '+leftDivSmall+'<br/>'+heightImgBig+', '+heightImgSmall+', '+widthImgBig+', '+widthImgSmall+';</small>';
*/
}
function wppa_popdown(elm, occ) {	//	return; //debug
	jQuery('#wppa-popup-'+occ).stop().animate({"marginLeft":leftDivSmall+"px","marginTop":topDivSmall+"px"}, 300); //, 'linear', wppa_popaway());
	jQuery('#wppa-img-'+occ).stop().animate({"width":widthImgSmall+"px","height":heightImgSmall+"px"}, 300);
	
//	wppa_timer = 
	setTimeout('wppa_popaway('+occ+')', 500);
}
function wppa_popaway(occ) {
	// This function is called unfortunately directly, not after completion of the animation i.e. 300 ms as documented in the jquery docs
	// The solution is to use setTimeout ourselves. See above
//	clearTimeout(wppa_timer);
	jQuery('#wppa-popup-'+occ).html("");
	if (wppa_last_occ == occ) wppa_last_occ = 0;
}

function wppa_load_spinner() {
	var top;
	var lft;
	var elm;
	
	if (wppa_first_spinner) {
		elm = document.getElementById('slide_frame');
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
			
		document.getElementById('spinner').style.top = top;
		document.getElementById('spinner').style.left = lft;
		jQuery('.arrow').css('top', top);
		wppa_first_spinner = false;
	}
	document.getElementById('spinnerimg').src = wppa_imgdir + 'wpspin.gif';
	
}

function wppa_unload_spinner() {
	document.getElementById('spinnerimg').src = '';
}
/*
	var dbg = document.getElementById('wppa-debug');
	dbg.innerHTML = '<small>padding='+puImg.style.padding+'.</small>';
*/