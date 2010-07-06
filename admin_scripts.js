/* admin_scripts.js */
/*	
/* Various js routines used in admin pages		
/*
/* Adjust visibility of selection radiobutton if fixed photo is chosen or not */				
function wppaCheckWidgetMethod() {
	var ph;
	var i;
	if (document.getElementById("wppa-wm").value=="4") {
		document.getElementById("wppa-wp").style.visibility="visible";
	}
	else {
		document.getElementById("wppa-wp").style.visibility="hidden";
	}
	if (document.getElementById("wppa-wm").value=="1") {
		ph=document.getElementsByName("wppa-widget-photo");
		i=0;
		while (i<ph.length) {
			ph[i].style.visibility="visible";
			i++;	
		}
	}
	else {
		ph=document.getElementsByName("wppa-widget-photo");
		i=0;
		while (i<ph.length) {
			ph[i].style.visibility="hidden";
			i++;
		}
	}
}

/* Displays or hides names and.or description dependant of subtitle chosen */
function wppaCheckWidgetSubtitle() {
	var subtitle = document.getElementById('wppa-st').value;
	var stn, std;
	var i;
	stn = document.getElementsByTagName('h4');
	std = document.getElementsByTagName('h6');
	i = 0;
	switch (subtitle)
	{
	case 'none':
		while (i < stn.length) {
			stn[i].style.visibility = "hidden";
			std[i].style.visibility = "hidden";
			i++;
		}
		break;
	case 'name':
		while (i < stn.length) {
			stn[i].style.visibility = "visible";
			std[i].style.visibility = "hidden";
			i++;
		}
		break;
	case 'desc':
		while (i < stn.length) {
			stn[i].style.visibility = "hidden";
			std[i].style.visibility = "visible";
			i++;
		}
		break;
	}
}

/**/