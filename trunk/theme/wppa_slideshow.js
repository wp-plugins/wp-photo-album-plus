// Slide show variables and functions
//

var wppa_slides = new Array();
var wppa_names = new Array();
var wppa_descs = new Array();
var wppa_id = 0;
var wppa_timeout = 5000;
var wppa_timer;
var wppa_timeron = false;
var wppa_slideshow;
    
function wppa_store_slideinfo(id, url, size, name, desc) {
    wppa_slides[id] = '<img src="' + url + '" alt="' + name + '" class="big" ' + size + '/>';
    wppa_names[id] = name;
    wppa_descs[id] = desc;
}
    
function wppa_nextslide(first) {
    if (!first) clearTimeout(wppa_timer);
    document.getElementById('theslide').innerHTML = wppa_slides[wppa_id];
    document.getElementById('imagedesc').innerHTML = wppa_descs[wppa_id];
    document.getElementById('imagetitle').innerHTML = wppa_names[wppa_id];
    wppa_id++;
    if (wppa_id == wppa_slides.length) wppa_id = 0;
    if (wppa_timeron) wppa_timer = setTimeout('wppa_nextslide(false)', wppa_timeout);  
}
    
function wppa_next() {
	if (wppa_timeron) {
		wppa_startstop(-1);
		wppa_id--;	/* Counter already incremented */
	}
	wppa_id++;
	if (wppa_id == wppa_slides.length) wppa_id = 0;
	wppa_disp();
}

function wppa_prev() {
	if (wppa_timeron) {
		wppa_startstop(-1);
		wppa_id--;	/* Counter already incremented */
	}
	wppa_id--;
	if (wppa_id < 0) wppa_id = wppa_slides.length - 1;
	wppa_disp();
}

function wppa_disp() {
    document.getElementById('theslide').innerHTML = wppa_slides[wppa_id];
    document.getElementById('imagedesc').innerHTML = wppa_descs[wppa_id];
    document.getElementById('imagetitle').innerHTML = wppa_names[wppa_id];
	document.getElementById('bc-pname').innerHTML = wppa_names[wppa_id];
}

function wppa_startstop(idx) {
	if (idx != -1) {
		document.getElementById('startstop').innerHTML='Start'+' '+wppa_slideshow; 
		document.getElementById('speed0').style.visibility="hidden";
		document.getElementById('speed1').style.visibility="hidden";
		wppa_id = idx;
		wppa_disp();
	}
    if (wppa_timeron) { // stop it
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