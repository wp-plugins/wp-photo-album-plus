// Slide show variables and functions
// This is wppa_slideshow version 2.0.0
//

var wppa_complete = false;
var wppa_slides = new Array();
var wppa_names = new Array();
var wppa_descs = new Array();
var wppa_id = 0;
var wppa_next_id = 0;
var wppa_timeout = 2500;
var wppa_timer;
var wppa_timeron = false;
var wppa_slideshow;
var wppa_opacity0 = 0;
var wppa_foreground = 0;
var wppa_stepsize = 20;
var wppa_animation_speed;
var wppa_imgdir;
var wppa_first_spinner = true;

var wppa_elm = new Array();
var wppa_img = new Array();
    
function wppa_store_slideinfo(id, url, size, name, desc) {
    wppa_slides[id] = ' src="' + url + '" alt="' + name + '" class="theimg big" ' + ' style="' + size + ' opacity:0; filter:alpha(opacity=0);" >';
    wppa_names[id] = name;
    wppa_descs[id] = desc;
}
   
function wppa_set_opacity(elm, val) {
	if (!elm) return;
	// Test browsertype
	if (document.all) {		// IE
		elm.filters.alpha.opacity = parseInt(val + 0.5);
	}
	else {					// Normal browser
		elm.style.opacity = val / 100;
	} 
}

function wppa_fade(dir) {
	/* dir == 0 : bring 1 to foreground */
	/* dir == 1 : bring 0 to foreground */
	var stop = false;
	var oldlyr;
	var newlyr;
			
	clearTimeout(wppa_timer);
	
	// Wait for load complete
	newlyr = 1 - dir;
	if (!document.getElementById('theimg'+newlyr).complete) {
		wppa_timer = setTimeout('wppa_fade(' + dir + ')', 100);	// Try again after 100 ms
		return;
	}

	// Remove spinner
	wppa_unload_spinner();
	
	// Hide subtitles
	document.getElementById('imagedesc').innerHTML = '&nbsp;&nbsp;';
	document.getElementById('imagetitle').innerHTML = '&nbsp;&nbsp;';
	
	// change foreground
	wppa_elm[wppa_foreground].style.zIndex = '900';
	wppa_foreground = 1 - wppa_foreground;
	wppa_elm[wppa_foreground].style.zIndex = '901';
	
	wppa_timer = setTimeout('wppa_fade_fade('+dir+')', 10);
}

function wppa_fade_fade(dir) {
	var fg;
	var bg;
	fg = wppa_foreground;
	bg = 1 - fg;

	clearTimeout(wppa_timer);
	
	jQuery("#theimg" + bg).animate({opacity:0}, wppa_animation_speed);	// Req'd for change in portrait/landscape vv
	jQuery("#theimg" + fg).animate({opacity:1}, wppa_animation_speed, wppa_after_fade());
}

function wppa_after_fade() {
	clearTimeout(wppa_timer);
	// Set display
	wppa_img[0].style.display = "block";
	wppa_img[1].style.display = "block";
	// Restore subtitles
	document.getElementById('imagedesc').innerHTML = '&nbsp;' + wppa_descs[wppa_id] + '&nbsp;';
	document.getElementById('imagetitle').innerHTML = '&nbsp;' + wppa_names[wppa_id] + '&nbsp;';
	// Wait for next slide
	wppa_complete = false;
	wppa_timer = setTimeout('wppa_nextslide(false)', wppa_timeout); 
}
 
function wppa_nextslide(first) {
	var fg = wppa_foreground;
	var bg = 1 - fg;
	
	// Kill timer
	clearTimeout(wppa_timer);
 	
	// Find index of next slide
	wppa_next_id = wppa_id + 1;
	if (wppa_next_id == wppa_slides.length) wppa_next_id = 0;
	
    // first:
    if (first) {
		
		wppa_elm[0] = document.getElementById('theslide0');
		wppa_elm[1] = document.getElementById('theslide1');
	    wppa_elm[0].innerHTML = '<img id="theimg0" ' + wppa_slides[wppa_id];
	    wppa_elm[1].innerHTML = '<img id="theimg1" ' + wppa_slides[wppa_next_id];
	    
	   	wppa_img[0] = document.getElementById('theimg0');
		wppa_img[1] = document.getElementById('theimg1');
	    wppa_elm[0].style.zIndex = '901';
	    wppa_elm[1].style.zIndex = '900';
		
		wppa_load_spinner();
			    
	    wppa_foreground = 0;
	    
	    document.getElementById('imagedesc').innerHTML = '&nbsp;' + wppa_descs[wppa_id] + '&nbsp;';
    	document.getElementById('imagetitle').innerHTML = '&nbsp;' + wppa_names[wppa_id] + '&nbsp;';

    }
    // end first
    else {
    	// load next img (backg)
    	if (wppa_foreground == 0) {
    		wppa_elm[1].innerHTML = '<img id="theimg1" ' + wppa_slides[wppa_next_id];
			wppa_img[1] = document.getElementById('theimg1');
			wppa_elm[1].style.zIndex = '900';
			wppa_elm[0].style.zIndex = '901';
    	}
    	else {
    		wppa_elm[0].innerHTML = '<img id="theimg0" ' + wppa_slides[wppa_next_id];
			wppa_img[0] = document.getElementById('theimg0');
			wppa_elm[0].style.zIndex = '900';
			wppa_elm[1].style.zIndex = '901';
    	}
    }
	
    // Who is next?
    wppa_id = wppa_next_id;
    
	wppa_timer = setTimeout('wppa_fade(' + wppa_foreground + ')', wppa_timeout);
}
    
function wppa_next() {
	if (wppa_timeron) {
		wppa_startstop(-1);
		wppa_id--;	/* Counter already incremented */
	}
	wppa_id++;
	if (wppa_id == wppa_slides.length) wppa_id = 0;
	wppa_disp(true);
}

function wppa_prev() {
	if (wppa_timeron) {
		wppa_startstop(-1);
		wppa_id--;	/* Counter already incremented */
	}
	wppa_id--;
	if (wppa_id < 0) wppa_id = wppa_slides.length - 1;
	wppa_disp(true);
}

function wppa_disp(init) {
	var fg = wppa_foreground;
	var bg = 1 - fg;
	
	if (init) {	
		jQuery(".arrow").fadeTo(400,0.2);
//		jQuery("#next-arrow").fadeTo(400,0.2);
//		document.getElementById('prev-arrow').style.visibility="hidden";
//		document.getElementById('next-arrow').style.visibility="hidden";

		wppa_elm[fg] = document.getElementById('theslide'+fg);
		wppa_elm[bg] = document.getElementById('theslide'+bg);
		wppa_elm[fg].innerHTML = '<img id="theimg'+fg+'" '+wppa_slides[wppa_id];

		wppa_img[fg] = document.getElementById('theimg'+fg);
		wppa_img[bg] = document.getElementById('theimg'+bg);
		wppa_elm[fg].style.zIndex = '901';
		wppa_elm[bg].style.zIndex = '900';

		wppa_set_opacity(wppa_img[fg], 0);

		wppa_load_spinner();
		
		wppa_complete = false;
	}
	// Wait for load complete
	if (!wppa_complete) {
		if (!wppa_img[fg].complete) {
			clearTimeout(wppa_timer);
			wppa_timer = setTimeout('wppa_disp(false)', 50);	// Try again after 50 ms
			return;
		}
		wppa_complete = true;
	}
		
	wppa_unload_spinner();
	
    wppa_set_opacity(wppa_img[fg], 100);
	wppa_set_opacity(wppa_img[bg], 0);	// Required for change in landscape/portrait vv
	
	wppa_foreground = 1 - wppa_foreground;

    document.getElementById('imagedesc').innerHTML = wppa_descs[wppa_id];
    document.getElementById('imagetitle').innerHTML = wppa_names[wppa_id];
	document.getElementById('bc-pname').innerHTML = wppa_names[wppa_id];
	
//	document.getElementById('prev-arrow').style.visibility="visible";
//	document.getElementById('next-arrow').style.visibility="visible";
		jQuery(".arrow").fadeTo(400,1);
//		jQuery("#next-arrow").fadeTo(400,1);

}

function wppa_startstop(idx) {
	if (idx != -1) {
		document.getElementById('startstop').innerHTML='Start'+' '+wppa_slideshow; 
		document.getElementById('speed0').style.visibility="hidden";
		document.getElementById('speed1').style.visibility="hidden";
		wppa_id = idx;
		wppa_disp(true);
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
		wppa_id--;	/* Counter already incremented */
		if (wppa_id < 0) wppa_id = wppa_slides.length - 1;
		document.getElementById('bc-pname').innerHTML = wppa_names[wppa_id];
    }
    else if (idx == -1) {
        wppa_timeron = true;
        document.getElementById('startstop').innerHTML='Stop';
        wppa_nextslide(true);
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

function wppa_popup(elm, id) {
	// Thumbnai
	var thA;
	var thImg;
	// Popup
	var puDiv;
	var puA;
	var puImg;
	// Coords and sizes
	var top, left;
	var height, width;
	
	thImg = elm;
	thA = document.getElementById('a'+id);
	
	puDiv = document.getElementById('wppa-popup');
	puDiv.innerHTML = '<a id="wppa-a" href=""><img id="wppa-img" src="#" /></a>';
	puA = document.getElementById('wppa-a');
	puImg = document.getElementById('wppa-img');
	
	puA.href = thA.href;
	puImg.src = thImg.src;
	
	left = thImg.offsetLeft - 5;	// = padding + border
	top = thImg.offsetTop - 5;		// ditto
	
	width = puImg.clientWidth - 8;
	height = puImg.clientHeight - 8;

	puImg.style.marginLeft = left+'px';
	puImg.style.marginTop = top+'px';
	
	puImg.style.width = thImg.clientWidth+'px';
	puImg.style.height = thImg.clientHeight+'px';
	
	left -= parseInt(((width - thImg.clientWidth) / 2)); 
	top -= parseInt(((height - thImg.clientHeight) / 2)); 
	
	jQuery('#wppa-img').stop().animate({marginLeft: left, marginTop: top, width: width, height: height}, 400);
}

function wppa_load_spinner() {
	var top;
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
			
		document.getElementById('spinner').style.top = top;
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