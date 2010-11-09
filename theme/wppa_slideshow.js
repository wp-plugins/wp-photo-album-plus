// Slide show variables and functions
// This is wppa_slideshow.js version 2.4.0, build 002
//

var wppa_slides = new Array();
var wppa_names = new Array();
var wppa_descs = new Array();
var wppa_id = new Array();
var wppa_next_id = new Array();
var wppa_timeout = new Array();
//var wppa_timer = new Array();
var wppa_ss_running = new Array();
var wppa_foreground = new Array();
var wppa_first_spinner = new Array();
var wppa_busy = new Array();
var wppa_first = new Array();
var wppa_saved_id = new Array();
var wppa_slideshow;			// = 'Slideshow' or its translation
var wppa_photo;				// = 'Photo' or its translation
var wppa_of;				// = 'of' or its translation
var wppa_nextphoto;			// = 'Next photo' or its translation
var wppa_prevphoto;			// = 'Previous photo' or its translation
var wppa_animation_speed;
var wppa_imgdir;
var wppa_fullvalign_fit = new Array();
var wppa_ss_timeout = 2500;
var wppa_fadein_after_fadeout = false;
var wppa_auto_colwidth = false;
var wppa_thumbnail_area_delta;
var wppa_thumbnail_pitch;
var wppa_filmstrip_length;
var wppa_filmstrip_margin = 0;
var wppa_preambule;

jQuery(document).ready(function(){
	if (wppa_auto_colwidth) wppa_do_autocol(0);
});

function wppa_store_slideinfo(mocc, id, url, size, name, desc) {
	if (!wppa_slides[mocc]) {
		wppa_slides[mocc] = new Array();
		wppa_names[mocc] = new Array();
		wppa_descs[mocc] = new Array();
		wppa_id[mocc] = -1;
		wppa_next_id[mocc] = 0;
		wppa_timeout[mocc] = wppa_ss_timeout;
		wppa_ss_running[mocc] = 0;
		wppa_foreground[mocc] = 0;
		wppa_first_spinner[mocc] = true;
		wppa_busy[mocc] = false;
		wppa_first[mocc] = true;
		wppa_fullvalign_fit[mocc] = false;
	}
    wppa_slides[mocc][id] = ' src="' + url + '" alt="' + name + '" class="theimg big" ' + ' style="' + size + '; display:block;">';
    wppa_names[mocc][id] = name;
    wppa_descs[mocc][id] = desc;
}

function wppa_next_slide(mocc) {
	var fg = wppa_foreground[mocc];
	var bg = 1 - fg;
	// Find index of next slide if in auto mode and not stop in progress
	if (wppa_ss_running[mocc] == 1) {
		wppa_next_id[mocc] = wppa_id[mocc] + 1;
		if (wppa_next_id[mocc] == wppa_slides[mocc].length) wppa_next_id[mocc] = 0;
	}
    // first:
    if (wppa_first[mocc]) {
	    if (wppa_id[mocc] != -1) {
			jQuery("#theslide0-"+mocc).html('<img id="theimg0-'+mocc+'" '+wppa_slides[mocc][wppa_id[mocc]]);
			jQuery("#theimg0-"+mocc).hide();
			jQuery("#theslide0-"+mocc).css('zIndex','901');
		}
		jQuery("#theslide1-"+mocc).html('<img id="theimg1-'+mocc+'" '+wppa_slides[mocc][wppa_next_id[mocc]]);
		jQuery("#theimg1-"+mocc).hide();	      
		jQuery("#theslide1-"+mocc).css('zIndex','900');
	
		wppa_load_spinner(mocc);
	    
		jQuery("#imagedesc-"+mocc).html('&nbsp;'+wppa_descs[mocc][wppa_id[mocc]]+'&nbsp;');
		jQuery("#imagetitle-"+mocc).html('&nbsp;'+wppa_names[mocc][wppa_id[mocc]]+'&nbsp;');
    }
    // end first
    else {
    	// load next img (backg)
		jQuery("#theslide"+bg+"-"+mocc).html('<img id="theimg'+bg+'-'+mocc+'" '+wppa_slides[mocc][wppa_next_id[mocc]]);
		jQuery("#theslide"+bg+"-"+mocc).css('zIndex', '900');
		jQuery("#theslide"+fg+"-"+mocc).css('zIndex', '901');
		jQuery("#theimg"+bg+"-"+mocc).hide();
    }
	wppa_first[mocc] = false;
    // Next is now current
    wppa_id[mocc] = wppa_next_id[mocc];
if (wppa_auto_colwidth) wppa_do_autocol(mocc);
//	wppa_timer[mocc] = 
	setTimeout('wppa_fade('+mocc+')', 10);
}

function wppa_fade(mocc) {
	var fg, bg;	

	fg = wppa_foreground[mocc];
	bg = 1 - fg;
	// Wait for load complete
	if (!document.getElementById('theimg'+bg+"-"+mocc).complete) {
		setTimeout('wppa_fade('+mocc+')', 100);	// Try again after 100 ms
		return;
	}
	// Remove spinner
	wppa_unload_spinner(mocc);
	// Do autocol if required
if (wppa_auto_colwidth) wppa_do_autocol();
	// Hide subtitles
if (wppa_ss_running[mocc] != -1) {	// not stop in progress
	jQuery("#imagedesc-"+mocc).html('&nbsp;&nbsp;');
	jQuery("#imagetitle-"+mocc).html('&nbsp;&nbsp;');	
}
	// change foreground
	wppa_foreground[mocc] = 1 - wppa_foreground[mocc];
	fg = wppa_foreground[mocc];
	bg = 1 - fg;
	jQuery("#theslide"+bg+"-"+mocc).css('zIndex', '900');
	jQuery("#theslide"+fg+"-"+mocc).css('zIndex', '901');
	setTimeout('wppa_fade_fade('+mocc+')', 10);
}

function wppa_fade_fade(mocc) {
	var fg;
	var bg;
	fg = wppa_foreground[mocc];
	bg = 1 - fg;

	// Do the actual fade. Fadeout only if not stop in progress
	if (wppa_ss_running[mocc] == -1) {	// stop in progress
//		wppa_ss_running[mocc] = 0;
	}
	else {
		jQuery("#theimg"+bg+"-"+mocc).fadeOut(wppa_animation_speed); 					// Req'd for change in portrait/landscape vv
	}
	// Fadein new image
	if (wppa_fadein_after_fadeout) {
		jQuery("#theimg"+fg+"-"+mocc).delay(wppa_animation_speed).fadeIn(wppa_animation_speed, wppa_after_fade(mocc)); 
	}
	else {
		jQuery("#theimg"+fg+"-"+mocc).fadeIn(wppa_animation_speed, wppa_after_fade(mocc)); 
	}
}

function wppa_after_fade(mocc) {
if (wppa_ss_running[mocc] == -1) { // stop in progress
	wppa_ss_running[mocc] = 0;
}
else {
	// set height to fit if reqd
	if (wppa_fullvalign_fit[mocc]) {
		h = document.getElementById('theimg'+wppa_foreground[mocc]+'-'+mocc).style.height;
		if (h) document.getElementById('slide_frame-'+mocc).style.height = parseInt(h)+'px';
		document.getElementById('slide_frame-'+mocc).style.minHeight = '0px';
	}
	// Display counter and arrow texts
	if (document.getElementById('counter-'+mocc)) {
		document.getElementById('counter-'+mocc).innerHTML = wppa_photo+' '+(wppa_id[mocc]+1)+' '+wppa_of+' '+wppa_slides[mocc].length;
		document.getElementById('prev-arrow-'+mocc).innerHTML = wppa_prevphoto;
		document.getElementById('next-arrow-'+mocc).innerHTML = wppa_nextphoto;
	}
	// Restore subtitles
	if (document.getElementById('imagedesc-'+mocc)) document.getElementById('imagedesc-'+mocc).innerHTML = '&nbsp;' + wppa_descs[mocc][wppa_id[mocc]] + '&nbsp;';
	if (document.getElementById('imagetitle-'+mocc)) document.getElementById('imagetitle-'+mocc).innerHTML = '&nbsp;' + wppa_names[mocc][wppa_id[mocc]] + '&nbsp;';
	
	// Update breadcrumb
	if (document.getElementById('bc-pname-'+mocc)) document.getElementById('bc-pname-'+mocc).innerHTML = wppa_names[mocc][wppa_id[mocc]];
}


// Adjust filmstrip
var xoffset;

//preambule = 4;
//var wppa_thumbnail_pitch = 104;
//var wppa_filmstrip_length = 390;
xoffset = wppa_filmstrip_length / 2 - (wppa_id[mocc] + 0.5 + wppa_preambule) * wppa_thumbnail_pitch - wppa_filmstrip_margin;
//if (xoffset > 0) xoffset = 0;
//xmin = wppa_filmstrip_length - wppa_slides[mocc].length * wppa_thumbnail_pitch;
//if (xoffset < (xmin)) xoffset = xmin;
//jQuery('#wppa-filmstrip-'+mocc).css('margin-left', xoffset);
jQuery('#wppa-filmstrip-'+mocc).animate({marginLeft: xoffset+'px'});
	
	// Wait for next slide
	if (wppa_ss_running[mocc] == 1) {
		setTimeout('wppa_next_slide('+mocc+')', wppa_timeout[mocc]); 
	}
	else {
		jQuery(".arrow-"+mocc).stop().fadeTo(400,1);
		wppa_busy[mocc] = false;
	}
}
 
function wppa_next(mocc) {
	if (wppa_busy[mocc]) return;
	wppa_busy[mocc] = true;
	wppa_next_id[mocc] = wppa_id[mocc] + 1;
	if (wppa_next_id[mocc] == wppa_slides[mocc].length) wppa_next_id[mocc] = 0;
	jQuery(".arrow-"+mocc).stop().fadeTo(400,0.2);
	wppa_next_slide(mocc);
}

function wppa_prev(mocc) {
	if (wppa_busy[mocc]) return;
	wppa_busy[mocc] = true;
	wppa_next_id[mocc] = wppa_id[mocc] - 1;
	if (wppa_next_id[mocc] < 0) wppa_next_id[mocc] = wppa_slides[mocc].length - 1;
	jQuery(".arrow-"+mocc).stop().fadeTo(400,0.2);
	wppa_next_slide(mocc);
}

function wppa_goto(mocc, idx) {
	if (wppa_ss_running[mocc] != 0) return;
	if (wppa_busy[mocc]) return;
	wppa_busy[mocc] = true;
	wppa_next_id[mocc] = idx;
	jQuery(".arrow-"+mocc).stop().fadeTo(400,0.2);
	wppa_next_slide(mocc);
}

function wppa_startstop(mocc, idx) {
//alert('startstop called with '+mocc+' '+idx);
	if (idx != -1) {	// Init still
if (document.getElementById('startstop-'+mocc)) document.getElementById('startstop-'+mocc).innerHTML='Start'+' '+wppa_slideshow; 
if (document.getElementById('speed0-'+mocc)) document.getElementById('speed0-'+mocc).style.visibility="hidden";
if (document.getElementById('speed1-'+mocc)) document.getElementById('speed1-'+mocc).style.visibility="hidden";
		wppa_next_id[mocc] = idx;
		wppa_id[mocc] = idx;
		wppa_next_slide(mocc);
	}
    if (wppa_ss_running[mocc] == 1) { // stop it
		wppa_ss_running[mocc] = -1;	// stop in progress
 //       clearTimeout(wppa_timer[mocc]);
        document.getElementById('startstop-'+mocc).innerHTML='Start'+' '+wppa_slideshow;  
		jQuery('#prev-arrow-'+mocc).css('visibility', 'visible');
		jQuery('#next-arrow-'+mocc).css('visibility', 'visible');
		jQuery('#prev-film-arrow-'+mocc).css('visibility', 'visible');
		jQuery('#next-film-arrow-'+mocc).css('visibility', 'visible');
		jQuery('#p-a-'+mocc).css('visibility', 'visible');
		jQuery('#n-a-'+mocc).css('visibility', 'visible');
		jQuery('#speed0-'+mocc).css('visibility', 'hidden');
		jQuery('#speed1-'+mocc).css('visibility', 'hidden');
		jQuery('#bc-pname-'+mocc).html(wppa_names[mocc][wppa_id[mocc]]);
    }
    else if (idx == -1) {
//wppa_next_id[mocc] = wppa_id[mocc];
        wppa_ss_running[mocc] = 1;
        wppa_next_slide(mocc);
		if (document.getElementById('startstop-'+mocc)) {
			document.getElementById('startstop-'+mocc).innerHTML='Stop';
			jQuery('#prev-arrow-'+mocc).css('visibility', 'hidden');
			jQuery('#next-arrow-'+mocc).css('visibility', 'hidden');
			jQuery('#prev-film-arrow-'+mocc).css('visibility', 'hidden');
			jQuery('#next-film-arrow-'+mocc).css('visibility', 'hidden');
			jQuery('#p-a-'+mocc).css('visibility', 'hidden');
			jQuery('#n-a-'+mocc).css('visibility', 'hidden');
			jQuery('#speed0-'+mocc).css('visibility', 'visible');
			jQuery('#speed1-'+mocc).css('visibility', 'visible');
		}
		jQuery('#bc-pname-'+mocc).html(wppa_slideshow);
    }
}
    
function wppa_speed(mocc, faster) {
    if (faster) {
        if (wppa_timeout[mocc] > 500) wppa_timeout[mocc] /= 1.5;
    }
    else {
        if (wppa_timeout[mocc] < 60000) wppa_timeout[mocc] *= 1.5;
    }
}

function wppa_load_spinner(mocc) {
	var top;
	var lft;
	var elm;
	
	if (wppa_first_spinner[mocc]) {
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
		wppa_first_spinner[mocc] = false;
	}
	document.getElementById('spinnerimg-'+mocc).src = wppa_imgdir + 'wpspin.gif';
	
}

function wppa_unload_spinner(mocc) {
	document.getElementById('spinnerimg-'+mocc).src = '';
}

function wppa_do_autocol(mocc) {
	var w;
	var h;
	if (!wppa_auto_colwidth) return;
	
	w = document.getElementById('wppa-container-1').parentNode.clientWidth;
	jQuery(".wppa-container").css('width',w);
	jQuery(".theimg").css('width',w);
	jQuery(".thumbnail-area").css('width',w-wppa_thumbnail_area_delta);

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
			setTimeout('wppa_do_autocol('+mocc+')', wppa_animation_speed);
		}
	}
}
/*
	var dbg = document.getElementById('wppa-debug');
	dbg.innerHTML = '<small>padding='+puImg.style.padding+'.</small>';
*/