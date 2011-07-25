// Theme variables and functions
// This is wppa_theme.js version 3.1.6
//

var wppa_bgcolor_img = '';
var wppa_timer = new Array();
var wppa_saved_id = new Array();
var wppa_popup_linktype = '';
var wppa_popup_onclick = new Array();

// Popup of thumbnail images 
function wppa_popup(mocc, elm, id, rating) {
	var topDivBig, topDivSmall, leftDivBig, leftDivSmall;
	var heightImgBig, heightImgSmall, widthImgBig, widthImgSmall, widthImgBigSpace;
	var puImg;
	
	// Ignore Duplicate call
	if (id == wppa_saved_id[mocc]) return; 
	wppa_saved_id[mocc] = id;
	
	// due to callback bug, see below, we need an extra timer 
	// stop if running 
	clearTimeout(wppa_timer[mocc]);
	
	// Give this' occurrances popup its content
	if (document.getElementById('x-'+id+'-'+mocc)) {

		switch (wppa_popup_linktype) {
			case 'none':
				jQuery('#wppa-popup-'+mocc).html('<div class="wppa-popup" style="background-color:'+wppa_bgcolor_img+'; text-align:center;"><img id="wppa-img-'+mocc+'" src="'+elm.src+'" title="" style="border-width: 0px;" /><div id="wppa-name-'+mocc+'" style="display:none; padding:2px;" class="wppa_pu_info">'+elm.alt+'</div><div id="wppa-desc-'+mocc+'" style="clear:both; display:none;" class="wppa_pu_info">'+elm.title+'</div><div id="wppa-rat-'+mocc+'" style="clear:both; display:none;" class="wppa_pu_info">'+rating+'</div></div>');
				break;
			case 'fullpopup':
				jQuery('#wppa-popup-'+mocc).html('<div class="wppa-popup" style="background-color:'+wppa_bgcolor_img+'; text-align:center;"><img id="wppa-img-'+mocc+'" src="'+elm.src+'" title="" style="border-width: 0px;" onclick="'+wppa_popup_onclick[id]+'" /><div id="wppa-name-'+mocc+'" style="display:none; padding:2px;" class="wppa_pu_info">'+elm.alt+'</div><div id="wppa-desc-'+mocc+'" style="clear:both; display:none;" class="wppa_pu_info">'+elm.title+'</div><div id="wppa-rat-'+mocc+'" style="clear:both; display:none;" class="wppa_pu_info">'+rating+'</div></div>');
				break;
			default:
				jQuery('#wppa-popup-'+mocc).html('<div class="wppa-popup" style="background-color:'+wppa_bgcolor_img+'; text-align:center;"><a id="wppa-a" href="'+document.getElementById('x-'+id+'-'+mocc).href+'"><img id="wppa-img-'+mocc+'" src="'+elm.src+'" title="" style="border-width: 0px;" /></a><div id="wppa-name-'+mocc+'" style="display:none; padding:2px;" class="wppa_pu_info">'+elm.alt+'</div><div id="wppa-desc-'+mocc+'" style="clear:both; display:none;" class="wppa_pu_info">'+elm.title+'</div><div id="wppa-rat-'+mocc+'" style="clear:both; display:none;" class="wppa_pu_info">'+rating+'</div></div>');
		}
	}
	
	// Find handle to the popup image 
	puImg = document.getElementById('wppa-img-'+mocc);
	
	// Set width of text fields to width of a landscape image	
	if (puImg)
		jQuery(".wppa_pu_info").css('width', ((puImg.clientWidth > puImg.clientHeight ? puImg.clientWidth : puImg.clientHeight) - 8)+'px');
	
	// Compute starting coords
	leftDivSmall = parseInt(elm.offsetLeft) - 7 - 5 - 1; // thumbnail_area:padding, wppa-img:padding, wppa-border; jQuery().css("padding") does not work for padding in css file, only when litaral in the tag
	topDivSmall = parseInt(elm.offsetTop) - 7 - 5 - 1;		
	// Compute starting sizes
	widthImgSmall = parseInt(elm.clientWidth);
	heightImgSmall = parseInt(elm.clientHeight);
	// Compute ending sizes
	widthImgBig = puImg.clientWidth; 
	heightImgBig = parseInt(puImg.clientHeight);
	widthImgBigSpace = puImg.clientWidth > puImg.clientHeight ? puImg.clientWidth : puImg.clientHeight;
	// Compute ending coords
	leftDivBig = leftDivSmall - parseInt((widthImgBigSpace - widthImgSmall) / 2);
	topDivBig = topDivSmall - parseInt((heightImgBig - heightImgSmall) / 2);
	
	// Setup starting properties
	jQuery('#wppa-popup-'+mocc).css({"marginLeft":leftDivSmall+"px","marginTop":topDivSmall+"px"});
	jQuery('#wppa-img-'+mocc).css({"width":widthImgSmall+"px","height":heightImgSmall+"px"});
	// Do the animation
	jQuery('#wppa-popup-'+mocc).stop().animate({"marginLeft":leftDivBig+"px","marginTop":topDivBig+"px"}, 400);
	jQuery('#wppa-img-'+mocc).stop().animate({"width":widthImgBig+"px","height":heightImgBig+"px"}, 400);
	// adding ", 'linear', wppa_popready(occ) " fails, therefor our own timer to the "show info" module
	wppa_timer[mocc] = setTimeout('wppa_popready('+mocc+')', 400);
}
function wppa_popready(mocc) {
	jQuery("#wppa-name-"+mocc).show();
	jQuery("#wppa-desc-"+mocc).show();
	jQuery("#wppa-rat-"+mocc).show();
}
function wppa_popdown(mocc) {	//	return; //debug
	jQuery('#wppa-popup-'+mocc).html("");
}

// Popup of fullsize image
function wppa_full_popup(mocc, id, url, xwidth, xheight) {
	var height = xheight+50;
	var width  = xwidth+14;
	var name = '';
	var desc = '';
	
	var elm = document.getElementById('i-'+id+'-'+mocc);
	if (elm) {
		name = elm.alt;
		desc = elm.title;
	}	
	
	var wnd = window.open('', '', 'width='+width+', height='+height+', location=no, resizable=no');
	wnd.document.write('<html>');
		wnd.document.write('<head>');	
			wnd.document.write('<style type="text/css">body{margin:0; padding:6px; background-color:'+wppa_bgcolor_img+'; text-align:center;}</style>');
			wnd.document.write('<title>'+name+'</title>');
			wnd.document.write('<script type="text/javascript">function wppa_print(){document.getElementById("wppa_printer").style.visibility="hidden";window.print();document.getElementById("wppa_printer").style.visibility="visible";}</script>');
		wnd.document.write('</head>');
		wnd.document.write('<body>');
			wnd.document.write('<div style="width:'+xwidth+'px;">');
				wnd.document.write('<img src="'+url+'" style="padding-bottom:6px;" /><br/>');
				wnd.document.write('<div style="text-align:center">'+desc+'</div>');
				var left = xwidth-30;
				wnd.document.write('<img src="'+wppa_imgdir+'printer.png" id="wppa_printer" title="Print" style="position:absolute; top:6px; left:'+left+'px; background-color:'+wppa_bgcolor_img+'; padding: 2px; cursor:pointer;" onclick="wppa_print();" />');
			wnd.document.write('</div>');
		wnd.document.write('</body>');
	wnd.document.write('</html>');
}

