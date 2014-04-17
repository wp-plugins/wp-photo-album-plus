<?php
/* wppa-exif-iptc-common.php
* Package: wp-photo-album-plus
*
* exif and iptc common functions
* version 5.3.0
*
* 
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );
	
// Translate iptc tags into  photo dependant data inside a text
function wppa_filter_iptc($desc, $photo) {
global $wpdb;

	if ( strpos($desc, '2#') === false ) return $desc;	// No tags in desc: Nothing to do
	
	$iptcdata = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_IPTC."` WHERE `photo`=%s ORDER BY `id`", $photo), ARRAY_A);
	wppa_dbg_q('Q60');
	
	// Init
	$temp = $desc;
	$prevtag = '';
	$combined = '';
	
	// Process all iptclines of this photo
	if ( $iptcdata ) {
		foreach ($iptcdata as $iptcline) {
			$tag = $iptcline['tag'];
			if ($prevtag == $tag) {			// add a next item for this tag
				$combined .= ', '.htmlspecialchars(strip_tags($iptcline['description']));
			}
			else { 							// first item of this tag
				if ( $combined ) { 			// Process if required
					$pos = strpos($temp, $prevtag);
					while ( $pos !== false ) {
						$temp = substr_replace($temp, $combined, $pos, strlen($tag));
						$pos = strpos($temp, $prevtag);
					}
				}
				$combined = htmlspecialchars(strip_tags($iptcline['description']));
				$prevtag = $tag;
			}
		}
		
		// Process last
		$pos = strpos($temp, $prevtag);
		while ( $pos !== false ) {
			$temp = substr_replace($temp, $combined, $pos, strlen($tag));
			$pos = strpos($temp, $prevtag);
		}
	}

	// Remove untranslated
	$pos = strpos($temp, '2#');
	while ( $pos !== false ) {
		$tmp = substr($temp, 0, $pos).__a('n.a.').substr($temp, $pos+5);
		$temp = $tmp;
		$pos = strpos($temp, '2#');
	}

	return $temp;
}

// Translate exif tags into  photo dependant data inside a text
function wppa_filter_exif($desc, $photo) {
global $wpdb;
global $exifdata, $exifdataphoto;

	if ( strpos($desc, 'E#') === false ) return $desc;	// No tags in desc: Nothing to do
	
	if ( $photo != $exifdataphoto ) {
		$exifdata = $wpdb->get_results($wpdb->prepare("SELECT * FROM `".WPPA_EXIF."` WHERE `photo`=%s ORDER BY `id`", $photo), ARRAY_A);
		$exifdataphoto = $photo;
		wppa_dbg_q('Q61v');
	}
	else {
		wppa_dbg_q('G61');
	}
	
	// Init
	$temp = $desc;
	$prevtag = '';
	$combined = '';
	
	// Process all exiflines of this photo
	if ( $exifdata ) { 
		foreach ($exifdata as $exifline) {
			$tag = $exifline['tag'];
			if ($prevtag == $tag) {			// add a next item for this tag
				$combined .= ', '.htmlspecialchars(strip_tags($exifline['description']));
			}
			else { 							// first item of this tag
				if ( $combined ) { 			// Process if required
					$pos = strpos($temp, $prevtag);
					while ( $pos !== false ) {
						$temp = substr_replace($temp, wppa_format_exif($prevtag, $combined), $pos, strlen($tag));
						$pos = strpos($temp, $prevtag);
					}
				}
				$combined = htmlspecialchars(strip_tags($exifline['description']));
				$prevtag = $tag;
			}
		}
	
		// Process last
		$pos = strpos($temp, $prevtag);
		while ( $pos !== false ) {
			$temp = substr_replace($temp, wppa_format_exif($prevtag, $combined), $pos, strlen($tag));
			$pos = strpos($temp, $prevtag);
		}
	}

	// Remove untranslated
	$pos = strpos($temp, 'E#');
	while ( $pos !== false ) {
		$tmp = substr($temp, 0, $pos).__a('n.a.').substr($temp, $pos+6);
		$temp = $tmp;
		$pos = strpos($temp, 'E#');
	}

	// Return result
	return $temp;
}

function wppa_format_exif($tag, $data) {

	$result = $data;
	switch ($tag) {
/*
E#0132		Date Time					Already formatted correctly
E#013B		Photographer				Already formatted correctly
E#8298		Copyright					Already formatted correctly
			Location					Formatted into one line according to the 3 tags below:  2#092, 2#090, 2#095, 2#101
										2#092		Sub location
										2#090		City
										2#095		State
										2#101		Country

E#0110		Camera						Already formatted correctly  Example: Canon EOS 50D
aux:Lens	Lens						Already formatted correctly - See line 66 in sample photo exifdata.jpg attached  Example aux:Lens="EF300mm f/4L IS USM +1.4x"
*/
//	E#920A		Focal length				Must be formatted:  420/1 = 420 mm
		case 'E#920A':
			$temp = explode('/', $data);
			if (isset($temp[1])) {
				if (is_numeric($temp[1])) {
					if ($temp[1] != 0) $result = round($temp[0]/$temp[1]).' mm.';
				}
			}
			break;

//	E#9206		Subject distance			Must be formatted:  765/100 = 7,65 m.
		case 'E#9206':
			$temp = explode('/', $data);
			if (isset($temp[1])) {
				if (is_numeric($temp[1])) {
					if ($temp[1] != 0) $result = round(100*$temp[0]/$temp[1])/"100".' m.';
				}
			}
			break;

//	E#829A		Shutter Speed				Must be formatted:  1/125 = 1/125 s.
		case 'E#829A':
			if ($result) $result .= ' s.';
			break;
			
//	E#829D		F-Stop						Must be formatted:  56/10 = f/5,6
		case 'E#829D':
			$temp = explode('/', $data);
			if (isset($temp[1])) {
				if (is_numeric($temp[1])) {
					if ($temp[1] != 0) $result = 'f/'.(round(10*$temp[0]/$temp[1])/10);
				}
			}				
			break;
/*
E#8827		ISO	Speed Rating			Already formatted correctly
E#9204		Exposure bias				Already formatted correctly

E#8822		Exposure program			Must be formatted according to table
										0 = Not Defined
										1 = Manual
										2 = Program AE
										3 = Aperture-priority AE
										4 = Shutter speed priority AE
										5 = Creative (Slow speed)
										6 = Action (High speed)
										7 = Portrait
										8 = Landscape
										9 = Bulb
*/
		case 'E#8822':
			switch ($data) {
				case '0': $result = __a('Not Defined'); break;
				case '1': $result = __a('Manual'); break;
				case '2': $result = __a('Program AE'); break;
				case '3': $result = __a('Aperture-priority AE'); break;
				case '4': $result = __a('Shutter speed priority AE'); break;
				case '5': $result = __a('Creative (Slow speed)'); break;
				case '6': $result = __a('Action (High speed)'); break;
				case '7': $result = __a('Portrait'); break;
				case '8': $result = __a('Landscape'); break;
				case '9': $result = __a('Bulb'); break;
			}
			break;
/* 
E#9204 		Exposure bias value 
*/
		case 'E#9204':
			if ( $data) $result = $data.' EV';
			else $result = '';
			break;
/*
E#9207		Metering mode				Must be formatted according to table
										1 = Average
										2 = Center-weighted average
										3 = Spot
										4 = Multi-spot
										5 = Multi-segment
										6 = Partial
										255 = Other
*/
		case 'E#9207':
			switch ($data) {
				case '1': $result = __a('Average'); break;
				case '2': $result = __a('Center-weighted average'); break;
				case '3': $result = __a('Spot'); break;
				case '4': $result = __a('Multi-spot'); break;
				case '5': $result = __a('Multi-segment'); break;
				case '6': $result = __a('Partial'); break;
				case '255': $result = __a('Other'); break;
			}
			break;
/*
E#9209		Flash						Must be formatted according to table
										0x0	= No Flash
										0x1	= Fired
										0x5	= Fired, Return not detected
										0x7	= Fired, Return detected
										0x8	= On, Did not fire
										0x9	= On, Fired
										0xd	= On, Return not detected
										0xf	= On, Return detected
										0x10	= Off, Did not fire
										0x14	= Off, Did not fire, Return not detected
										0x18	= Auto, Did not fire
										0x19	= Auto, Fired
										0x1d	= Auto, Fired, Return not detected
										0x1f	= Auto, Fired, Return detected
										0x20	= No flash function
										0x30	= Off, No flash function
										0x41	= Fired, Red-eye reduction
										0x45	= Fired, Red-eye reduction, Return not detected
										0x47	= Fired, Red-eye reduction, Return detected
										0x49	= On, Red-eye reduction
										0x4d	= On, Red-eye reduction, Return not detected
										0x4f	= On, Red-eye reduction, Return detected
										0x50	= Off, Red-eye reduction
										0x58	= Auto, Did not fire, Red-eye reduction
										0x59	= Auto, Fired, Red-eye reduction
										0x5d	= Auto, Fired, Red-eye reduction, Return not detected
										0x5f	= Auto, Fired, Red-eye reduction, Return detected		
*/
		case 'E#9209':
			switch ($data) {
				case '0x0':
				case '0': $result = __a('No Flash'); break;
				case '0x1':
				case '1': $result = __a('Fired'); break;
				case '0x5':
				case '5': $result = __a('Fired, Return not detected'); break;
				case '0x7':
				case '7': $result = __a('Fired, Return detected'); break;
				case '0x8':
				case '8': $result = __a('On, Did not fire'); break;
				case '0x9':
				case '9': $result = __a('On, Fired'); break;
				case '0xd':
				case '13': $result = __a('On, Return not detected'); break;
				case '0xf':
				case '15': $result = __a('On, Return detected'); break;
				case '0x10':
				case '16': $result = __a('Off, Did not fire'); break;
				case '0x14':
				case '20': $result = __a('Off, Did not fire, Return not detected'); break;
				case '0x18':
				case '24': $result = __a('Auto, Did not fire'); break;
				case '0x19':
				case '25': $result = __a('Auto, Fired'); break;
				case '0x1d':
				case '29': $result = __a('Auto, Fired, Return not detected'); break;
				case '0x1f':
				case '31': $result = __a('Auto, Fired, Return detected'); break;
				case '0x20':
				case '32': $result = __a('No flash function'); break;
				case '0x30':
				case '48': $result = __a('Off, No flash function'); break;
				case '0x41':
				case '65': $result = __a('Fired, Red-eye reduction'); break;
				case '0x45':
				case '69': $result = __a('Fired, Red-eye reduction, Return not detected'); break;
				case '0x47':
				case '71': $result = __a('Fired, Red-eye reduction, Return detected'); break;
				case '0x49':
				case '73': $result = __a('On, Red-eye reduction'); break;
				case '0x4d':
				case '77': $result = __a('Red-eye reduction, Return not detected'); break;
				case '0x4f':
				case '79': $result = __a('On, Red-eye reduction, Return detected'); break;
				case '0x50':
				case '80': $result = __a('Off, Red-eye reduction'); break;
				case '0x58':
				case '88': $result = __a('Auto, Did not fire, Red-eye reduction'); break;
				case '0x59':
				case '89': $result = __a('Auto, Fired, Red-eye reduction'); break;
				case '0x5d':
				case '93': $result = __a('Auto, Fired, Red-eye reduction, Return not detected'); break;
				case '0x5f':
				case '95': $result = __a('Auto, Fired, Red-eye reduction, Return detected'); break;
			}
			break;
			
		default:
			$result = $data;
	}
	
	return $result;
}

