// Slide show variables and functions
// This is wppa_slideshow.js version 3.1.4
//

var wppa_slides = new Array();
var wppa_names = new Array();
var wppa_descs = new Array();
var wppa_id = new Array();
var wppa_next_id = new Array();
var wppa_timeout = new Array();
var wppa_ss_running = new Array();
var wppa_foreground = new Array();
var wppa_toggle_pending = new Array();
var wppa_busy = new Array();
var wppa_first = new Array();
var wppa_saved_id = new Array();
var wppa_fullvalign_fit = new Array();
var wppa_animation_speed;
var wppa_imgdir;
var wppa_auto_colwidth = false;
var wppa_oldcolw = 0;
var wppa_thumbnail_area_delta;
var wppa_ss_timeout = 2500;
var wppa_fadein_after_fadeout = false;
var wppa_text_delay;
var wppa_textframe_delta = 0;
var wppa_box_delta = 0;
var wppa_preambule;
var wppa_thumbnail_pitch = new Array();
var wppa_filmstrip_length = new Array();
var wppa_filmstrip_margin = new Array();
var wppa_filmstrip_area_delta = new Array();
var wppa_film_show_glue;
var wppa_is_mini = new Array();
var wppa_slideshow;			// = 'Slideshow' or its translation
var wppa_photo;				// = 'Photo' or its translation
var wppa_of;				// = 'of' or its translation
var wppa_nextphoto;			// = 'Next photo' or its translation
var wppa_prevphoto;			// = 'Previous photo' or its translation
var wppa_nextp;
var wppa_prevp;
var wppa_start = 'Start';	// defaults
var wppa_stop = 'Stop';		//
var wppa_photo_ids = new Array();
var wppa_photo_avg = new Array();
var wppa_photo_myr = new Array();
var wppa_photo_rur = new Array();
var wppa_rating_once = true;
var wppa_username;
var wppa_portrait_only = new Array();
var wppa_in_widget_linkurl = new Array();
var wppa_in_widget_linktitle = new Array();
var wppa_comment_html = new Array();
var wppa_to_the_same = false;
var wppa_please_name;
var wppa_please_email;
var wppa_please_comment;

jQuery(document).ready(function(){
	if (wppa_auto_colwidth) wppa_do_autocol(0);
	wppa_text_delay = wppa_animation_speed;
	if (wppa_fadein_after_fadeout) wppa_text_delay *= 2;
});

function wppa_store_slideinfo(mocc, id, url, size, name, desc, photoid, avgrat, myrat, rateurl, iwlinkurl, iwlinktitle, iwtimeout, commenthtml) {
	if (!wppa_slides[mocc]) {
		wppa_slides[mocc] = new Array();
		wppa_names[mocc] = new Array();
		wppa_descs[mocc] = new Array();
		wppa_id[mocc] = -1;
		wppa_next_id[mocc] = 0;
		if (parseInt(iwtimeout) > 0) wppa_timeout[mocc] = parseInt(iwtimeout);
		else wppa_timeout[mocc] = wppa_ss_timeout;
		wppa_ss_running[mocc] = 0;
		wppa_toggle_pending[mocc] = false;
		wppa_foreground[mocc] = 0;
		wppa_busy[mocc] = false;
		wppa_first[mocc] = true;
		wppa_fullvalign_fit[mocc] = false;
		wppa_photo_ids[mocc] = new Array();
		wppa_photo_avg[mocc] = new Array();
		wppa_photo_myr[mocc] = new Array();
		wppa_photo_rur[mocc] = new Array();
		wppa_portrait_only[mocc] = false;
		wppa_in_widget_linkurl[mocc] = new Array(); // iwlinkurl;
		wppa_in_widget_linktitle[mocc] = new Array(); // iwlinktitle;
		wppa_comment_html[mocc] = new Array();
	}
    wppa_slides[mocc][id] = ' src="' + url + '" alt="' + name + '" class="theimg big" ' + ' style="' + size + '; display:block;">';
    wppa_names[mocc][id] = name;
    wppa_descs[mocc][id] = desc;
	wppa_photo_ids[mocc][id] = photoid;		// reqd for rating and comment
	wppa_photo_avg[mocc][id] = avgrat;		// avg ratig value
	wppa_photo_myr[mocc][id] = myrat;		// my rating
	wppa_photo_rur[mocc][id] = rateurl;		// url that performs the vote and returns to the page
	wppa_in_widget_linkurl[mocc][id] = iwlinkurl;
	wppa_in_widget_linktitle[mocc][id] = iwlinktitle;
	wppa_comment_html[mocc][id] = commenthtml;
}

function wppa_next_slide(mocc) {
	var fg = wppa_foreground[mocc];
	var bg = 1 - fg;

	// stop in progress??
	if (wppa_ss_running[mocc] == -1) { 
		wppa_ss_running[mocc] = 0;
		wppa_set_rating_display(mocc); // Update after all
		return;
	}
	// Find index of next slide if in auto mode and not stop in progress
	if (wppa_ss_running[mocc] == 1) {
		wppa_next_id[mocc] = wppa_id[mocc] + 1;
		if (wppa_next_id[mocc] == wppa_slides[mocc].length) wppa_next_id[mocc] = 0;
	}
    // first:
    if (wppa_first[mocc]) {
	    if (wppa_id[mocc] != -1) {
			if (wppa_in_widget_linkurl[mocc][wppa_id[mocc]] != '') jQuery("#theslide0-"+mocc).html('<a href="'+wppa_in_widget_linkurl[mocc][wppa_id[mocc]]+'" title="'+wppa_in_widget_linktitle[mocc][wppa_id[mocc]]+'"><img id="theimg0-'+mocc+'" '+wppa_slides[mocc][wppa_id[mocc]]+'</a>');
			else jQuery("#theslide0-"+mocc).html('<img id="theimg0-'+mocc+'" '+wppa_slides[mocc][wppa_id[mocc]]);
			jQuery("#theimg0-"+mocc).hide();
			jQuery("#theslide0-"+mocc).css('zIndex','901');
		}
		if (wppa_in_widget_linkurl[mocc][wppa_next_id[mocc]] != '') {
			jQuery("#theslide1-"+mocc).html('<a href="'+wppa_in_widget_linkurl[mocc][wppa_next_id[mocc]]+'" title="'+wppa_in_widget_linktitle[mocc][wppa_next_id[mocc]]+'"><img id="theimg1-'+mocc+'" '+wppa_slides[mocc][wppa_next_id[mocc]]+'</a>');
		}
		else {
			jQuery("#theslide1-"+mocc).html('<img id="theimg1-'+mocc+'" '+wppa_slides[mocc][wppa_next_id[mocc]]);
		}
		jQuery("#theimg1-"+mocc).hide();	      
		jQuery("#theslide1-"+mocc).css('zIndex','900');
	
		wppa_load_spinner(mocc);
	    
		jQuery("#imagedesc-"+mocc).html('&nbsp;'+wppa_descs[mocc][wppa_id[mocc]]+'&nbsp;');
		jQuery("#imagetitle-"+mocc).html('&nbsp;'+wppa_names[mocc][wppa_id[mocc]]+'&nbsp;');
		jQuery("#comments-"+mocc).html(wppa_comment_html[mocc][wppa_id[mocc]]);
		
	// Display counter and arrow texts
	if (document.getElementById('counter-'+mocc)) {
		if (wppa_is_mini[mocc]) {
//			document.getElementById('counter-'+mocc).innerHTML = (wppa_id[mocc]+1)+' / '+wppa_slides[mocc].length;
			document.getElementById('prev-arrow-'+mocc).innerHTML = wppa_prevp;
			document.getElementById('next-arrow-'+mocc).innerHTML = wppa_nextp;
		}
		else {
//			document.getElementById('counter-'+mocc).innerHTML = wppa_photo+' '+(wppa_id[mocc]+1)+' '+wppa_of+' '+wppa_slides[mocc].length;
			document.getElementById('prev-arrow-'+mocc).innerHTML = wppa_prevphoto;
			document.getElementById('next-arrow-'+mocc).innerHTML = wppa_nextphoto;
		}
	}
//		
    }
    // end first
    else {
    	// load next img (backg)
		if (wppa_in_widget_linkurl[mocc][wppa_next_id[mocc]] != '') jQuery("#theslide"+bg+"-"+mocc).html('<a href="'+wppa_in_widget_linkurl[mocc][wppa_next_id[mocc]]+'" title="'+wppa_in_widget_linktitle[mocc][wppa_next_id[mocc]]+'"><img id="theimg'+bg+'-'+mocc+'" '+wppa_slides[mocc][wppa_next_id[mocc]]+'</a>');
		else jQuery("#theslide"+bg+"-"+mocc).html('<img id="theimg'+bg+'-'+mocc+'" '+wppa_slides[mocc][wppa_next_id[mocc]]);
		jQuery("#theslide"+bg+"-"+mocc).css('zIndex', '900');
		jQuery("#theslide"+fg+"-"+mocc).css('zIndex', '901');
		jQuery("#theimg"+bg+"-"+mocc).hide();
    }
	wppa_first[mocc] = false;
	
	// See if the filmstrip needs wrap around before shifting to the right location
	wppa_check_rewind(mocc);

    // Next is now current
    wppa_id[mocc] = wppa_next_id[mocc];
	if (wppa_auto_colwidth) wppa_do_autocol(mocc);

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
	if (wppa_auto_colwidth) wppa_do_autocol(mocc);
	// Hide subtitles
	if (wppa_ss_running[mocc] != -1) {	// not stop in progress
		if (!wppa_to_the_same) {
			wppa_show_metadata(mocc, 'hide');
		}
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

	jQuery("#theimg"+bg+"-"+mocc).fadeOut(wppa_animation_speed); 					// Req'd for change in portrait/landscape vv

	// Fadein new image
	if (wppa_fadein_after_fadeout) {
		jQuery("#theimg"+fg+"-"+mocc).delay(wppa_animation_speed).fadeIn(wppa_animation_speed, wppa_after_fade(mocc)); 
	}
	else {
		jQuery("#theimg"+fg+"-"+mocc).fadeIn(wppa_animation_speed, wppa_after_fade(mocc)); 
	}
}

function wppa_after_fade(mocc) {
	// set height to fit if reqd
	if (wppa_portrait_only[mocc]) {
		h = jQuery('#theimg'+wppa_foreground[mocc]+'-'+mocc).css('height');
		jQuery('#slide_frame-'+mocc).css('height', parseInt(h)+'px');
	}
	else if (wppa_fullvalign_fit[mocc]) {
		h = jQuery('#theimg'+wppa_foreground[mocc]+'-'+mocc).css('height');
		if (h != 'auto') {
			jQuery('#slide_frame-'+mocc).css('height', parseInt(h)+'px');
		}
		jQuery('#slide_frame-'+mocc).css('minHeight', '0px');
	}
/**/
	// Display counter and arrow texts
	if (document.getElementById('counter-'+mocc)) {
		if (wppa_is_mini[mocc]) {
			document.getElementById('counter-'+mocc).innerHTML = (wppa_id[mocc]+1)+' / '+wppa_slides[mocc].length;
//			document.getElementById('prev-arrow-'+mocc).innerHTML = wppa_prevp;
//			document.getElementById('next-arrow-'+mocc).innerHTML = wppa_nextp;
		}
		else {
			document.getElementById('counter-'+mocc).innerHTML = wppa_photo+' '+(wppa_id[mocc]+1)+' '+wppa_of+' '+wppa_slides[mocc].length;
//			document.getElementById('prev-arrow-'+mocc).innerHTML = wppa_prevphoto;
//			document.getElementById('next-arrow-'+mocc).innerHTML = wppa_nextphoto;
		}
	}
/**/
	// Update breadcrumb
	if (document.getElementById('bc-pname-'+mocc)) document.getElementById('bc-pname-'+mocc).innerHTML = wppa_names[mocc][wppa_id[mocc]];

	// Adjust filmstrip
	var xoffset;
	xoffset = wppa_filmstrip_length[mocc] / 2 - (wppa_id[mocc] + 0.5 + wppa_preambule) * wppa_thumbnail_pitch[mocc] - wppa_filmstrip_margin[mocc];
	if (wppa_film_show_glue) xoffset -= (wppa_filmstrip_margin[mocc] * 2 + 2);	// Glue
	jQuery('#wppa-filmstrip-'+mocc).animate({marginLeft: xoffset+'px'});
	
	// Set rating mechanism
	wppa_set_rating_display(mocc);
	
	// Wait for next slide
//	if (wppa_ss_running[mocc] == 1) {
		setTimeout('wppa_almost_next_slide('+mocc+')', wppa_text_delay); // wppa_timeout[mocc]); // wppa_text_delay);
//	}

}

function wppa_almost_next_slide(mocc) {

	// If we are going to the same slide, there is no need to hide and restore the subtitles and commentframe
	if (!wppa_to_the_same) {	
		// Restore subtitles
		jQuery('#imagedesc-'+mocc).html('&nbsp;' + wppa_descs[mocc][wppa_id[mocc]] + '&nbsp;');
		jQuery('#imagetitle-'+mocc).html('&nbsp;' + wppa_names[mocc][wppa_id[mocc]] + '&nbsp;');
		// Restore comments html
		jQuery("#comments-"+mocc).html(wppa_comment_html[mocc][wppa_id[mocc]]);
	}
	wppa_to_the_same = false;	// This has now been worked out

	wppa_show_metadata(mocc, 'show'); 

	if (wppa_ss_running[mocc] == 1) {
		setTimeout('wppa_next_slide('+mocc+')', wppa_timeout[mocc]); 
		return;
	}	
	else {
		jQuery(".arrow-"+mocc).stop().fadeTo(400,1);
		wppa_busy[mocc] = false;
		if (wppa_toggle_pending[mocc]) {
			wppa_toggle_pending[mocc] = false;
			wppa_startstop(mocc, -1);
		}
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
	// goto only works when not running and not busy
	if (wppa_ss_running[mocc] != 0) return;	
	if (wppa_busy[mocc]) return;
	
	wppa_busy[mocc] = true;
	wppa_to_the_same = (wppa_next_id[mocc] == idx);
	wppa_next_id[mocc] = idx;
	jQuery(".arrow-"+mocc).stop().fadeTo(400,0.2);
	wppa_next_slide(mocc);
}

function wppa_startstop(mocc, idx) {
	if (idx != -1) {	// Init still at index idx
		if (document.getElementById('startstop-'+mocc)) document.getElementById('startstop-'+mocc).innerHTML=wppa_start+' '+wppa_slideshow; 
		if (document.getElementById('speed0-'+mocc)) document.getElementById('speed0-'+mocc).style.visibility="hidden";
		if (document.getElementById('speed1-'+mocc)) document.getElementById('speed1-'+mocc).style.visibility="hidden";
		wppa_next_id[mocc] = idx;
		wppa_id[mocc] = idx;
		wppa_next_slide(mocc);
		wppa_show_metadata(mocc, 'show');
	}
    if (wppa_ss_running[mocc] == 1) { // stop it
		wppa_ss_running[mocc] = -1;	// stop in progress
        document.getElementById('startstop-'+mocc).innerHTML=wppa_start+' '+wppa_slideshow;  
		jQuery('#prev-arrow-'+mocc).css('visibility', 'visible');
		jQuery('#next-arrow-'+mocc).css('visibility', 'visible');
		jQuery('#prev-film-arrow-'+mocc).css('visibility', 'visible');
		jQuery('#next-film-arrow-'+mocc).css('visibility', 'visible');
		jQuery('#p-a-'+mocc).css('visibility', 'visible');
		jQuery('#n-a-'+mocc).css('visibility', 'visible');
		jQuery('#speed0-'+mocc).css('visibility', 'hidden');
		jQuery('#speed1-'+mocc).css('visibility', 'hidden');
		wppa_show_metadata(mocc, 'show');
	
		jQuery('#bc-pname-'+mocc).html(wppa_names[mocc][wppa_id[mocc]]);
    }
    else if (idx == -1) {
		if (wppa_ss_running[mocc]) return; 	// already running or stop in progresss
		if (wppa_busy[mocc]) {				// Currently performing a goto
			wppa_toggle_pending[mocc] = true;
			return;		// one thing at a time
		}
        wppa_ss_running[mocc] = 1;
        wppa_next_slide(mocc);
		if (document.getElementById('startstop-'+mocc)) {
			document.getElementById('startstop-'+mocc).innerHTML=wppa_stop;
		}
		jQuery('#prev-arrow-'+mocc).css('visibility', 'hidden');
		jQuery('#next-arrow-'+mocc).css('visibility', 'hidden');
		jQuery('#prev-film-arrow-'+mocc).css('visibility', 'hidden');
		jQuery('#next-film-arrow-'+mocc).css('visibility', 'hidden');
		jQuery('#p-a-'+mocc).css('visibility', 'hidden');
		jQuery('#n-a-'+mocc).css('visibility', 'hidden');
		jQuery('#speed0-'+mocc).css('visibility', 'visible');
		jQuery('#speed1-'+mocc).css('visibility', 'visible');
		wppa_show_metadata(mocc, 'hide');	
		jQuery('#bc-pname-'+mocc).html(wppa_slideshow);
    }
	wppa_set_rating_display(mocc);
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
	document.getElementById('spinner-'+mocc).innerHTML = '<img id="spinnerimg-'+mocc+'" src="'+wppa_imgdir+'wpspin.gif" />';
}

function wppa_unload_spinner(mocc) {
	if (document.getElementById('spinnerimg-'+mocc)) {
		document.getElementById('spinnerimg-'+mocc).src = '';
		document.getElementById('spinner-'+mocc).innerHTML = '';
	}
}

function wppa_do_autocol(mocc) {
	var w;
	var h;
	if (!wppa_auto_colwidth) return;
	
	w = document.getElementById('wppa-container-1').parentNode.clientWidth;
	
	jQuery(".wppa-container").css('width',w);
	jQuery(".theimg").css('width',w);
	jQuery(".thumbnail-area").css('width',w - wppa_thumbnail_area_delta);
	wppa_filmstrip_length[mocc] = w - wppa_filmstrip_area_delta[mocc];
	jQuery(".filmwindow").css('width',wppa_filmstrip_length[mocc]);

	jQuery(".wppa-text-frame").css('width',w - wppa_textframe_delta);
	jQuery(".wppa-cover-box").css('width',w - wppa_box_delta);
	
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

function wppa_check_rewind(mocc) {
	var n_images;
	var n_diff;
	var l_substrate;
	var x_marg;
	
	if (!document.getElementById('wppa-filmstrip-'+mocc)) return; // There is no filmstrip
	
	n_diff = Math.abs(wppa_id[mocc] - wppa_next_id[mocc]);
	if (n_diff < 2) return;
	
	var n_images = wppa_filmstrip_length[mocc] / wppa_thumbnail_pitch[mocc];
	
	if (n_diff >= ((n_images + 1) / 2)) {
		l_substrate = wppa_thumbnail_pitch[mocc] * wppa_slides[mocc].length;
		if (wppa_film_show_glue) l_substrate += (2 + 2 * wppa_filmstrip_margin[mocc]);
		
		x_marg = parseInt(jQuery('#wppa-filmstrip-'+mocc).css('margin-left'));

		if (wppa_next_id[mocc] > wppa_id[mocc]) {
			x_marg -= l_substrate;
		}
		else {
			x_marg += l_substrate;
		}

		jQuery('#wppa-filmstrip-'+mocc).css('margin-left', x_marg+'px');
	}
}

function wppa_set_rating_display(mocc) {
var idx, avg, myr;
	if (!document.getElementById('wppa-rating-'+mocc)) return; 	// No rating bar
	if (wppa_ss_running[mocc] == -1) return; 					// Stop in progress, do nothing now
	
	avg = wppa_photo_avg[mocc][wppa_id[mocc]];
	wppa_set_rd(mocc, avg, '#wppa-avg-');
	
	if (wppa_username != '') {									// user logged in
		myr = wppa_photo_myr[mocc][wppa_id[mocc]];
		wppa_set_rd(mocc, myr, '#wppa-rate-');
	}
}
		
function wppa_set_rd(mocc, avg, where) {
		
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

var wppa_vote_in_progress = false;

function wppa_follow_me(mocc, idx) {
	if (wppa_ss_running[mocc] == 1) return;				// Do not rate on a running show, what only works properly in Firefox								

	if (wppa_photo_myr[mocc][wppa_id[mocc]] != 0 && wppa_rating_once) return;	// Already rated
	if (wppa_vote_in_progress) return;
	wppa_set_rd(mocc, idx, '#wppa-rate-');
}
function wppa_leave_me(mocc, idx) {
	if (wppa_ss_running[mocc] == 1) return;				// Do not rate on a running show, what only works properly in Firefox	

	if (wppa_photo_myr[mocc][wppa_id[mocc]] != 0 && wppa_rating_once) return;	// Already rated
	if (wppa_vote_in_progress) return;
	wppa_set_rd(mocc, wppa_photo_myr[mocc][wppa_id[mocc]], '#wppa-rate-');
}

function wppa_rate_it(mocc, value) {
	var photoid = wppa_photo_ids[mocc][wppa_id[mocc]];
	var oldval = wppa_photo_myr[mocc][wppa_id[mocc]];
	var url = wppa_photo_rur[mocc][wppa_id[mocc]]+'&wpparating='+value;
	
	if (document.getElementById('wppa_nonce')) url += '&wppa_nonce='+document.getElementById('wppa_nonce').value;

	if (oldval != 0 && wppa_rating_once) return;							// Already rated, and once allowed only
	if (wppa_ss_running[mocc] == 1) return;										
																			
	wppa_vote_in_progress = true;											// Keeps opacity as it is now
	
	document.getElementById('wppa-rate-'+mocc+'-'+value).src = wppa_imgdir+'tick.png';				// Set icon
	jQuery('#wppa-rate-'+mocc+'-'+value).stop().fadeTo(100, 1.0);
	
	setTimeout('wppa_go("'+url+'")', 200);	// 200 ms to display tick
}

function wppa_validate_comment(mocc) {
	var photoid = wppa_photo_ids[mocc][wppa_id[mocc]];
	
	// Process name
	var name = document.getElementById('wppacomname-'+mocc).value;
	if (name.length<1) {
		alert(wppa_please_name);
		return false;
	}
	
	// Process email address
	var email = document.getElementById('wppacomemail-'+mocc).value;
	var atpos=email.indexOf("@");
	var dotpos=email.lastIndexOf(".");
	if (atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length) {
		alert(wppa_please_email);
		return false;
	}
	
	// Process comment
	var text = document.getElementById('wppacomment-'+mocc).value;
	if (text.length<1) {
		alert(wppa_please_comment);
		return false;
	}
	
	return true;
}

function wppa_go(url) {
	document.location = url;	// Go!
}

function wppa_bbb(mocc,where,act) {
	if (wppa_ss_running[mocc]) return;
	
	var elm = '#bbb-'+mocc+'-'+where;
	switch (act) {
		case 'show':
			jQuery(elm).stop().fadeTo(100, 0.6);
			break;
		case 'hide':
			jQuery(elm).stop().fadeTo(400, 0);
			break;
		case 'click':
			if (where == 'l') wppa_prev(mocc);
			if (where == 'r') wppa_next(mocc);
			break;
		default:
			alert('Unimplemented instruction: '+act+' on: '+elm);
	}
}

function wppa_show_metadata(mocc, key) {
	// What to do when the slideshow is NOT running
	if (wppa_ss_running[mocc] != 1) {	// Maybe stopped (0) or stop in progress (-1)
		if (key == 'show') {
			// Show existing comments
			jQuery('#wppacommentstable-'+mocc).css('visibility', 'visible');
			// Show the input form table
			jQuery('#wppacommenttable-'+mocc).css('visibility', 'visible');
			// Show the comment footer
			jQuery('#wppacommentfooter-'+mocc).css('visibility', 'collapse');
			// Fade the browse arrows in
			jQuery('#prev-film-arrow-'+mocc).fadeIn(100);
			jQuery('#next-film-arrow-'+mocc).fadeIn(100);
		}
		else {
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