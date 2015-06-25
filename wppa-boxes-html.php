<?php
/* wppa-boxes-html.php
* Package: wp-photo-album-plus
*
* Various wppa boxes
* Version 6.1.15
*
*
*/

if ( ! defined( 'ABSPATH' ) ) die( "Can't load this file directly" );

// Open / cose the box containing the thumbnails
function wppa_thumb_area( $action ) {

	// Init
	$result = '';
	$mocc 	= wppa( 'mocc' );
	$alt 	= wppa( 'alt' );
	
	// Open thumbnail area box
	if ( $action == 'open' ) {
		if ( is_feed() ) {
			$result .= 	'<div'.
							' id="wppa-thumb-area-' . $mocc . '"' .
							' class="wppa-thumb-area"' .
							' style="' . __wcs( 'wppa-box' ) . __wcs( 'wppa-' . $alt ) . '"' .
							' >';
		}
		else {
			$result .= 	"\n";
			$result .= 	'<div' .
							' id="wppa-thumb-area-' . $mocc . '"' .
							' class="' .
								'wppa-thumb-area ' .
								'wppa-thumb-area-' . $mocc . ' ' .
								'wppa-box wppa-' . $alt .
								'"' .
							' style="' .
								__wcs( 'wppa-box' ) . 
								__wcs( 'wppa-' . $alt ) .
								'width:' . wppa_get_thumbnail_area_width() . 'px;' .
								'"' .
							' >';
								
			if ( wppa( 'current_album' ) ) {
				wppa_bump_viewcount( 'album', wppa( 'current_album') );
			}
		}

		// Toggle alt/even		
		wppa_toggle_alt();
	}
	
	// Cloase thumbnail area box
	elseif ( $action == 'close' ) {
	
		// Display create subalbum and upload photo links conditionally
		if ( 	! wppa( 'is_upldr' ) &&
				! wppa( 'searchstring' ) &&
				! wppa( 'supersearch' ) ) {
			
			$alb = wppa( 'current_album' );
			wppa_user_create_html( $alb, wppa_get_container_width( 'netto' ), 'thumb' );
			wppa_user_upload_html( $alb, wppa_get_container_width( 'netto' ), 'thumb' );
		}
		
		// Clear both
		$result .= '<div class="wppa-clear" style="' . __wis( 'clear:both;' ) . '" ></div>';
		
		// Close the thumbnail box
		$result .= '</div><!-- wppa-thumb-area-' . $mocc . ' -->';
	}
	
	// Unimplemented action
	else {
		$result .= '<span style="color:red;">' .
						'Error, wppa_thumb_area() called with wrong argument: ' . 
						$action . 
						'. Possible values: \'open\' or \'close\'' .
					'</span>';
	}
	
	// Output result
	wppa_out( $result );
}

// Search box
function wppa_search_box() {

	// Init
	$result = '';
	
	// No search box on feeds
	if ( is_feed() ) return;
	
	// Open container
	wppa_container( 'open' );
	
	// Open wrapper
	$result .= "\n";
	$result .= '<div' .
					' id="wppa-search-'.wppa( 'mocc' ) . '"' .
					' class="wppa-box wppa-search"' .
					' style="' . __wcs( 'wppa-box' ) . __wcs( 'wppa-search' ) . '"' .
					' >';
					
	// The search html
	$result .= wppa_get_search_html( '', wppa( 'may_sub' ), wppa( 'may_root' ) );
	
	// Clear both
	$result .= '<div class="wppa-clear" style="'.__wis( 'clear:both;' ).'" ></div>';
	
	// Close wrapper
	$result .= '</div>';
	
	// Output
	wppa_out( $result );
	
	// Close container
	wppa_container( 'close' );
}

// Get search html
function wppa_get_search_html( $label = '', $sub = false, $root = false ) {
global $wppa_session;

	$page 			= wppa_get_the_landing_page( 	'wppa_search_linkpage', 
													__a( 'Photo search results' ) );
	$pagelink 		= wppa_dbg_url( get_page_link( $page ) );
	$cansubsearch  	= 	$sub && 
						isset ( $wppa_session['use_searchstring'] ) && 
						$wppa_session['use_searchstring'];
	$subboxset 		= 	isset ( $wppa_session['subbox'] ) && $wppa_session['subbox'] ? 
						'checked="checked"' : '';
	$canrootsearch 	= $root; 
	$rootboxset 	= 	isset ( $wppa_session['rootbox'] ) && $wppa_session['rootbox'] ? 
						'checked="checked"' : '';
	$value 			= $cansubsearch ? '' : wppa_test_for_search();
	$root 			= isset( $wppa_session['search_root'] ) ? $wppa_session['search_root'] : '';
	$fontsize 		= wppa( 'in_widget' ) ? 'font-size: 9px;' : '';
	$mocc 			= wppa( 'mocc' );

	wppa_dbg_msg( 'Root='.$root.': '.wppa_get_album_name( $root ) );
	
	$result = '
	<form' .
		' id="wppa_searchform_' . $mocc . '"' .
		' action="' . $pagelink.'"' .
		' method="post"' .
		' class="widget_search"' .
		' >
		<div>' . 
			$label .
			( $cansubsearch ? '<small>'.$wppa_session['display_searchstring'].'<br /></small>' : '' ) .
			'<input' .
				' type="text"' .
				' class="wppa-search-input"' .
				' style="width:60%;"' .
				' name="wppa-searchstring"' .
				' id="wppa_s-'.$mocc.'"' .
				' value="'.$value.'"' .
				' />' .
			'<input' .
				' id="wppa_searchsubmit-'.$mocc.'"' .
				' type="submit"' .
				' name="wppa-search-submit"' .
				' value="'.__a( 'Search' ).'"' .
				' onclick="if ( document.getElementById( \'wppa_s-'.$mocc.'\' ).value == \'\' ) return false;"' .
				' />' .
			'<input' .
				' type="hidden"' .
				' name="wppa-searchroot"' .
				' value="' . $root . '"' .
				' />' .
			( $canrootsearch ? 
				'<div style="' . $fontsize . '" >
					<input type="checkbox" name="wppa-rootsearch" ' . $rootboxset . ' /> ' .
						__a( 'Search in current section' ) . 
				'</div>' : '' ) .
			( $cansubsearch ? 
				'<div style="' . $fontsize . '" >
					<input type="checkbox" name="wppa-subsearch" ' . $subboxset . '/> '.
						__a( 'Search in current results' ).
				'</div>' : '' ) .
		'</div>
	</form>';

	return $result;
}

// The supersearch box
function wppa_supersearch_box() {

	if ( is_feed() ) return;
	
	wppa_container( 'open' );
	
	wppa_out( 	'<div' .
					' id="wppa-search-' . wppa( 'mocc' ) . '"' .
					' class="wppa-box wppa-search"' .
					' style="' . __wcs( 'wppa-box' ) . __wcs( 'wppa-search' ) . '"' .
					' >' .
					wppa_get_supersearch_html() .
					'<div class="wppa-clear" style="'.__wis( 'clear:both;' ).'" >' .
					'</div>' .
				'</div>'
			);
	
	wppa_container( 'close' );
}

// Get supersearch html
function wppa_get_supersearch_html() {
global $wpdb;
global $wppa_session;

	// Init
	$page 		= wppa_get_the_landing_page( 'wppa_supersearch_linkpage', __a( 'Photo search results' ) );
	$pagelink 	= wppa_dbg_url( get_page_link( $page ) );
	$fontsize 	= wppa( 'in_widget' ) ? 'font-size: 9px;' : '';
	$query 		= 	"SELECT `id`, `name`, `owner` FROM `" . WPPA_ALBUMS . "` ORDER BY `name`";
	$albums 	= $wpdb->get_results( $query, ARRAY_A );
	$query 		= 	"SELECT `name` FROM `" . WPPA_PHOTOS .
						"` WHERE `status` <> 'pending' AND `status` <> 'scheduled' ORDER BY `name`";
	$photonames	= $wpdb->get_results( $query, ARRAY_A );
	$query 		= 	"SELECT `owner` FROM `" .WPPA_PHOTOS .
						"` WHERE `status` <> 'pending' AND `status` <> 'scheduled' ORDER BY `owner`";
	$ownerlist 	= $wpdb->get_results( $query, ARRAY_A );
	$catlist 	= wppa_get_catlist();
	$taglist 	= wppa_get_taglist();
	$ss_data 	= 	isset( $wppa_session['supersearch'] ) ? 
						explode( ',', $wppa_session['supersearch'] ) : 
						array( '', '', '', '' );
	if ( count( $ss_data ) < '4' ) {
		$ss_data = array( '', '', '', '' );
	}
	$ss_cats 	= ( $ss_data['0'] == 'a' && $ss_data['1'] == 'c' ) ? explode( '.', $ss_data['3'] ) : array();
	$ss_tags 	= ( $ss_data['0'] == 'p' && $ss_data['1'] == 'g' ) ? explode( '.', $ss_data['3'] ) : array();
	$ss_data['3'] = str_replace( '...', '***', $ss_data['3'] );
	$ss_atxt 	= ( $ss_data['0'] == 'a' && $ss_data['1'] == 't' ) ? explode( '.', $ss_data['3'] ) : array();
	foreach( array_keys( $ss_atxt ) as $key ) {
		$ss_atxt[$key] = str_replace( '***', '...', $ss_atxt[$key] );
	}
	$ss_ptxt 	= ( $ss_data['0'] == 'p' && $ss_data['1'] == 't' ) ? explode( '.', $ss_data['3'] ) : array();
	foreach( array_keys( $ss_ptxt ) as $key ) {
		$ss_ptxt[$key] = str_replace( '***', '...', $ss_ptxt[$key] );
	}
	$ss_data['3'] = str_replace( '***', '...', $ss_data['3'] );
	
	$query 		= "SELECT `slug` FROM `".WPPA_INDEX."` WHERE `albums` <> '' ORDER BY `slug`";
	$albumtxt 	= $wpdb->get_results( $query, ARRAY_A );
	$query 		= "SELECT `slug` FROM `".WPPA_INDEX."` WHERE `photos` <> '' ORDER BY `slug`";
	$phototxt 	= $wpdb->get_results( $query, ARRAY_A );
	$iptclist 	= wppa_switch( 'wppa_save_iptc' ) ? 
					$wpdb->get_results( "SELECT `tag`, `description` FROM `" . WPPA_IPTC . 
							"` WHERE `photo` = '0' AND `status` <> 'hide' ", ARRAY_A ) : array();
	$exiflist 	= wppa_switch( 'wppa_save_exif' ) ? 
					$wpdb->get_results( "SELECT `tag`, `description` FROM `" . WPPA_EXIF . 
							"` WHERE `photo` = '0' AND `status` <> 'hide' ", ARRAY_A ) : array();

	// Check for empty albums
	if ( wppa_switch( 'skip_empty_albums' ) ) {
		$user = wppa_get_user();
		if ( is_array( $albums ) ) foreach ( array_keys( $albums ) as $albumkey ) {
			$albumid 	= $albums[$albumkey]['id'];
			$albumowner = $albums[$albumkey]['owner'];
			$treecount 	= wppa_treecount_a( $albums[$albumkey]['id'] );
			$photocount = $treecount['photos'];
			if ( ! $photocount && ! wppa_user_is( 'administrator' ) && $user != $albumowner ) {
				unset( $albums[$albumkey] );
			}
		}
	}
	if ( empty( $albums ) ) $albums = array();

	// Compress photonames if partial length search
	if ( wppa_opt( 'ss_name_max' ) ) {
		$maxl = wppa_opt( 'ss_name_max' );
		$last = '';
		foreach ( array_keys( $photonames ) as $key ) {
			if ( strlen( $photonames[$key]['name'] ) > $maxl ) {
				$photonames[$key]['name'] = substr( $photonames[$key]['name'], 0, $maxl ) . '...';
			}
			if ( $photonames[$key]['name'] == $last ) {
				unset( $photonames[$key] );
			}
			else {
				$last = $photonames[$key]['name'];
			}
		}
	}

	// Compress phototxt if partial length search
	if ( wppa_opt( 'ss_text_max' ) ) {
		$maxl = wppa_opt( 'ss_text_max' );
		$last = '';
		foreach ( array_keys( $phototxt ) as $key ) {
			if ( strlen( $phototxt[$key]['slug'] ) > $maxl ) {
				$phototxt[$key]['slug'] = substr( $phototxt[$key]['slug'], 0, $maxl ) . '...';
			}
			if ( $phototxt[$key]['slug'] == $last ) {
				unset( $phototxt[$key] );
			}
			else {
				$last = $phototxt[$key]['slug'];
			}
		}
	}
	
	// Remove dup photo owners
	$last = '';
	foreach( array_keys( $ownerlist ) as $key ) {
		if ( $ownerlist[$key]['owner'] == $last ) {
			unset( $ownerlist[$key] );
		}
		else {
			$last = $ownerlist[$key]['owner'];
		}
	}

	// Make the html
	$id = 'wppa_searchform_' . wppa('mocc');
	$result =
	'<form' .
		' id="' . $id . '"' .
		' action="'.$pagelink.'"' . 
		' method="post"' .
		' class="widget_search"' . 
		' >' .
		'<input' .
			' type="hidden"' .
			' id="wppa-ss-pageurl-'.wppa('mocc').'"' .
			' name="wppa-ss-pageurl"' .
			' value="'.$pagelink.'"' .
			' />';
		
		// album or photo
		$id = 'wppa-ss-pa-'.wppa('mocc');
		$result .=
		'<select' .
			' id="' . $id . '"' .
			' name="wppa-ss-pa"' .
			' style="margin:2px;padding:0;vertical-align:top;"' .
			' onchange="wppaSuperSearchSelect( '.wppa('mocc').' );"' .
			' size="2"' .
			' >' .
				'<option' .
					' value="a" ' . 
					( $ss_data['0'] == 'a' ? 'selected="selected" ' : '' ) . 
					' >' .
						__a('Albums') . 
				'</option>' .
				'<option' .
					' value="p" ' . 
					( $ss_data['0'] == 'p' ? 'selected="selected" ' : '' ) . 
					' >' .
						__a('Photos') .
				'</option>' .
		'</select>';
		
			// album
			$id = 'wppa-ss-albumopt-'.wppa('mocc');
			$result .= '
			<select'.
				' id="' . $id . '"' .
				' name="wppa-ss-albumopt"' .
				' style="display:none;margin:2px;padding:0;vertical-align:top;"' .
				' onchange="wppaSuperSearchSelect( '.wppa('mocc').' );"' .
				' size="' . ( ! empty( $catlist ) ? '3' : '2' ) . '"' .
				' >';
					if ( ! empty( $catlist ) ) {
						$result .=
						'<option' .
							' value="c"' .
							( $ss_data['0'] == 'a' && $ss_data['1'] == 'c' ? 'selected="selected" ' : '' ) . 
							' >' .
								__a('Category') .
						'</option>';
					}
					$result .=
					'<option' .
						' value="n"' .
						( $ss_data['0'] == 'a' && $ss_data['1'] == 'n' ? 'selected="selected" ' : '' ) . 
						' >' .
							__a('Name') .
					'</option>' .
					'<option' .
						' value="t"' .
						( $ss_data['0'] == 'a' && $ss_data['1'] == 't' ? 'selected="selected" ' : '' ) . 
						' >' .
							__a('Text') .
					'</option>' .
			'</select>';
			
				// album category
				if ( ! empty( $catlist ) ) {
					$id = 'wppa-ss-albumcat-'.wppa('mocc');
					$result .=
					'<select'.
						' id="' . $id . '"' .
						' name="wppa-ss-albumcat"' .
						' style="display:none;margin:2px;padding:0;vertical-align:top;"' .
						' onchange="wppaSuperSearchSelect( '.wppa('mocc').' );"' .
						' size="' . ( min( count( $catlist ), '6' ) ) . '"' .
						' multiple' .
						' title="' . 
						esc_attr( __a( 'CTRL+Click to add/remove option.' ) ) . "\n" . 
						esc_attr( __a( 'Items must meet all selected options.' ) ) . 
							'"' .
						' >';
						foreach ( array_keys( $catlist ) as $cat ) {
							$sel = in_array ( $cat, $ss_cats );
							$result .= 	
							'<option' .
								' value="' . $cat . '"' .
								' class="' . $id . '"' .
								( $sel ? ' selected="selected"' : '' ) .
								' >' .
									$cat .
							'</option>';
						}
					$result .=
					'</select>';
				}
				
				// album name
				$id = 'wppa-ss-albumname-'.wppa('mocc');
				$result .=
				'<select'.
					' id="' . $id . '"' .
					' name="wppa-ss-albumname"' .
					' style="display:none;margin:2px;padding:0;vertical-align:top;"' .
					' onchange="wppaSuperSearchSelect( '.wppa('mocc').' );"' .
					' size="' . ( min( count( $albums ), '6' ) ) . '"' .
					' >';
					foreach ( $albums as $album ) {
						$name = stripslashes( $album['name'] );
						$sel = ( $ss_data['3'] == $name && $ss_data['0'] == 'a' && $ss_data['1'] == 'n' );
						$result .= 
						'<option' .
							' value="' . esc_attr( $name ) . '"' .
							( $sel ? ' selected="selected"' : '' ) .
							' >' . 
								__( $name ) . 
						'</option>';
					}
				$result .=
				'</select>';
				
				// album text
				$id = 'wppa-ss-albumtext-'.wppa('mocc');
				$result .= '
				<select'.
					' id="' . $id . '"' .
					' name="wppa-ss-albumtext"' .
					' style="display:none;margin:2px;padding:0;vertical-align:top;"' .
					' onchange="wppaSuperSearchSelect( '.wppa('mocc').' );"' .
					' size="' . ( min( count( $albumtxt ), '6' ) ) . '"' .
					' multiple="multiple"' .
					' title="' . 
						esc_attr( __a( 'CTRL+Click to add/remove option.' ) ) . "\n" . 
						esc_attr( __a( 'Items must meet all selected options.' ) ) . 
						'"' .
					' >';
					foreach ( $albumtxt as $txt ) {
						$text = $txt['slug'];
						$sel = in_array ( $text, $ss_atxt );
						$result .= 
						'<option' .
							' value="' . $text . '"' .
							' class="' . $id . '"' .
							( $sel ? ' selected="selected"' : '' ) .
							' >' . 
								$text . 
						'</option>';
					}
				$result .= '
				</select>';
				$result .= '<!-- anm='.count($albumtxt).' -->';
				
			// photo
			$n = '1' + 
				( count( $ownerlist ) > '1' ) + 
				( ! empty( $taglist ) ) + 
				'1' + 
				( wppa_switch( 'wppa_save_iptc' ) ) + 
				( wppa_switch( 'wppa_save_exif' ) );
			$result .= '
			<select'.
				' id="wppa-ss-photoopt-'.wppa('mocc').'"' .
				' name="wppa-ss-photoopt"' .
				' style="display:none;margin:2px;padding:0;vertical-align:top;"' .
				' onchange="wppaSuperSearchSelect( '.wppa('mocc').' );"' .
				' size="' . $n . '"' .
				' >' .
					'<option' .
						' value="n"' .
						( $ss_data['0'] == 'p' && $ss_data['1'] == 'n' ? 'selected="selected" ' : '' ) . 
						' >' .
							__a('Name') .
					'</option>';
					if ( count( $ownerlist ) > '1' ) {
						$result .=
						'<option' .
							' value="o"' .
							( $ss_data['0'] == 'p' && $ss_data['1'] == 'o' ? 'selected="selected" ' : '' ) . 
							' >' .
								__a('Owner') .
						'</option>';
					}
					if ( ! empty( $taglist ) ) {
						$result .=
						'<option' .
							' value="g"' .
							( $ss_data['0'] == 'p' && $ss_data['1'] == 'g' ? 'selected="selected" ' : '' ) . 
							' >' .
								__a('Tag') .
						'</option>';
					}
					$result .=
					'<option' .
						' value="t"' .
						( $ss_data['0'] == 'p' && $ss_data['1'] == 't' ? 'selected="selected" ' : '' ) . 
						' >' .
							__a('Text') .
					'</option>';
					if ( wppa_switch( 'wppa_save_iptc' ) ) {
						$result .=
						'<option' .
							' value="i"' .
							( $ss_data['0'] == 'p' && $ss_data['1'] == 'i' ? 'selected="selected" ' : '' ) . 
							' >' .
								__a('Iptc') .
						'</option>';
					}
					if ( wppa_switch( 'wppa_save_exif' ) ) {
						$result .=
						'<option' .
							' value="e"' .
							( $ss_data['0'] == 'p' && $ss_data['1'] == 'e' ? 'selected="selected" ' : '' ) . 
							' >' .
								__a('Exif') .
						'</option>';
					}
			$result .=
			'</select>';
			
				// photo name
				$id = 'wppa-ss-photoname-'.wppa('mocc');
				$result .= '
				<select'.
					' id="' . $id . '"' .
					' name="wppa-ss-photoname"' .
					' style="display:none;margin:2px;padding:0;vertical-align:top;"' .
					' onchange="wppaSuperSearchSelect( '.wppa('mocc').' );"' .
					' size="' . min( count( $photonames ), '6' ) . '"' .
					' >';
					foreach ( $photonames as $photo ) {
						$name = stripslashes( $photo['name'] );
						$sel = ( $ss_data['3'] == $name && $ss_data['0'] == 'p' && $ss_data['1'] == 'n' );
						$result .= 
						'<option' .
							' value="' . esc_attr( $name ) . '"' .
							( $sel ? ' selected="selected"' : '' ) .
							' >' . 
								__( $name ) . 
						'</option>';
					}
				$result .= '
				</select>';
				$result .= '<!-- pnm='.count($photonames).' -->';
				
				// photo owner
				$id = 'wppa-ss-photoowner-'.wppa('mocc');
				$result .= '
				<select'.
					' id="' . $id . '"' .
					' name="wppa-ss-photoowner"' .
					' style="display:none;margin:2px;padding:0;vertical-align:top;"' .
					' onchange="wppaSuperSearchSelect( '.wppa('mocc').' );"' .
					' size="' . ( min( count( $ownerlist ), '6' ) ) . '"' .
					' >';
					foreach ( $ownerlist as $photo ) {
						$owner = $photo['owner'];
						$sel = ( $ss_data['3'] == $owner && $ss_data['0'] == 'p' && $ss_data['1'] == 'o' );
						$result .= 
						'<option' .
							' value="' . $owner . '"' .
							( $sel ? ' selected="selected"' : '' ) .
							' >' .
								$owner .
						'</option>';
					}
				$result .= '
				</select>';
				
				// photo tag
				$id = 'wppa-ss-phototag-'.wppa('mocc');
				$result .= '
				<select'.
					' id="' . $id . '"' .
					' name="wppa-ss-phototag"' .
					' style="display:none;margin:2px;padding:0;vertical-align:top;"' .
					' onchange="wppaSuperSearchSelect( '.wppa('mocc').' );"' .
					' size="' . ( min( count( $taglist ), '6' ) ) . '"' .
					' multiple' .
					' title="' . 
						esc_attr( __a( 'CTRL+Click to add/remove option.' ) ) . "\n" . 
						esc_attr( __a( 'Items must meet all selected options.' ) ) . 
						'"' .
					' >';
					foreach ( array_keys( $taglist ) as $tag ) {
						$sel = in_array ( $tag, $ss_tags );
						$result .= 
						'<option' .
							' value="'.$tag.'"' .
							' class="' . $id . '"' .
							( $sel ? ' selected="selected"' : '' ) .
							' >' .
								$tag .
						'</option>';
					}
				$result .=
				'</select>';
				
				// photo text
				$id = 'wppa-ss-phototext-'.wppa('mocc');
				$result .= '
				<select' .
					' id="' . $id . '"' .
					' name="wppa-ss-phototext"' .
					' style="display:none;margin:2px;padding:0;vertical-align:top;"' .
					' onchange="wppaSuperSearchSelect( '.wppa('mocc').' );"' .
					' size="' . ( min( count( $phototxt ), '6' ) ) . '"' .
					' multiple="multiple"' .
					' title="' . 
						esc_attr( __a( 'CTRL+Click to add/remove option.' ) ) . "\n" . 
						esc_attr( __a( 'Items must meet all selected options.' ) ) . 
						'"' .
					' >';
					foreach ( $phototxt as $txt ) {
						$text 	= $txt['slug'];
						$sel 	= in_array ( $text, $ss_ptxt );
						$result .= 
						'<option' .
							' value="' . $text . '"' .
							' class="' . $id . '"' .
							( $sel ? ' selected="selected"' : '' ) .
							' >' .
								$text .
						'</option>';
					}
				$result .= 
				'</select>';
				$result .= '<!-- ptxt='.count($phototxt).' -->';
				
				// photo iptc
				$result .= '
				<select' .
					' id="wppa-ss-photoiptc-'.wppa('mocc').'"' .
					' name="wppa-ss-photoiptc"' .
					' style="display:none;margin:2px;padding:0;vertical-align:top;"' .
					' onchange="wppaSuperSearchSelect( '.wppa('mocc').' );"' .
					' size="' . min( count( $iptclist ), '6' ) . '"' .
					' >';
					$reftag = str_replace( 'H', '#', $ss_data['2'] );
					foreach ( $iptclist as $item ) {
						$tag = $item['tag'];
						$sel = ( $reftag == $tag && $ss_data['0'] = 'p' && $ss_data['1'] == 'i' );
						$result .= 
						'<option' .
							' value="' . $tag . '"' .
							( $sel ? ' selected="selected"' : '' ) .
							' >' . 
								__( $item['description'] ) . 
						'</option>';
					}
				$result .= 
				'</select>';
				
				// Iptc items
				$result .= '
				<select' .
					' id="wppa-ss-iptcopts-'.wppa('mocc').'"' .
					' name="wppa-ss-iptcopts"' .
					' style="display:none;margin:2px;padding:0;vertical-align:top;"' .
					' size="6"' .
					' onchange="wppaSuperSearchSelect('.wppa('mocc').')"' .
					' >
				</select>';

				// photo exif
				$result .= '
				<select' .
					' id="wppa-ss-photoexif-'.wppa('mocc').'"' .
					' name="wppa-ss-photoexif"' .
					' style="display:none;margin:2px;padding:0;vertical-align:top;"' .
					' onchange="wppaSuperSearchSelect( '.wppa('mocc').' );"' .
					' size="' . min( count( $exiflist ), '6' ) . '"' .
					' >';
					$reftag = str_replace( 'H', '#', $ss_data['2'] );
					foreach ( $exiflist as $item ) {
						$tag = $item['tag'];
						$sel = ( $reftag == $tag && $ss_data['0'] = 'p' && $ss_data['1'] == 'e' );
						$result .= 
						'<option' .
							' value="' . $tag . '"' .
							( $sel ? ' selected="selected"' : '' ) .
							' >' . 
								__( $item['description'] ) . 
						'</option>';
					}
				$result .= 
				'</select>';
				
				// Exif items
				$result .= '
				<select' .
					' id="wppa-ss-exifopts-'.wppa('mocc').'"' .
					' name="wppa-ss-exifopts"' .
					' style="display:none;margin:2px;padding:0;vertical-align:top;"' .
					' size="6"' .
					' onchange="wppaSuperSearchSelect('.wppa('mocc').')"' .
					' >
				</select>';

				
		// The spinner
		$result .= '
		<img' .
			' id="wppa-ss-spinner-'.wppa('mocc').'"' .
			' src="' . wppa_get_imgdir() . '/wpspin.gif' . '"' .
			' style="margin:0 4px;display:none;"' .
			' />';
		
		// The button
		$result .= '
		<input' .
			' type="button"' . 
			' id="wppa-ss-button-' . wppa('mocc') . '"' .
			' value="' . __a('Submit') . '"' .
			' style="vertical-align:top;margin:2px;"' .
			' onclick="wppaSuperSearchSelect(' . wppa('mocc') .' , true)"' .
			' ontouchstart="wppaSuperSearchSelect(' . wppa('mocc') .' , true)"' .
			' />';
			
	$result .= '
	</form>
	<script type="text/javascript" >
		wppaSuperSearchSelect(' . wppa('mocc') . ');
	</script>';

	return $result;
}

// Superview box
function wppa_superview_box( $album_root = '0', $sort = true ) {

	if ( is_feed() ) return;
	
	wppa_container( 'open' );
	
	wppa_out( 	'<div' .
					' id="wppa-superview-' . wppa( 'mocc' ) . '"' .
					' class="wppa-box wppa-superview"' .
					' style="' . __wcs( 'wppa-box' ) . __wcs( 'wppa-superview' ) . '"' .
					' >' .
					wppa_get_superview_html( $album_root, $sort ) .
					'<div class="wppa-clear" style="'.__wis( 'clear:both;' ).'" >' .
					'</div>' .
				'</div>' 
			);
				
	wppa_container( 'close' );
}

// Get superview html
function wppa_get_superview_html( $album_root = '0', $sort = true ) {
global $wppa_session;

	$page = wppa_get_the_landing_page( 'wppa_super_view_linkpage', __a( 'Super View Photos' ) );
	$url = get_permalink( $page );

	if ( ! isset ( $wppa_session ) ) $wppa_session = array();
	if ( ! isset ( $wppa_session['superview'] ) ) {
		$wppa_session['superview'] = 'thumbs';
		$wppa_session['superalbum'] = '0';
	}

	$checked = 'checked="checked"';
			
	$result = '
	<div>
		<form action="' . $url . '" method = "get">
			<label>' . __( 'Album:', 'wppa' ) . '</label><br />
			<select name="wppa-album">' .
				wppa_album_select_a( array( 	'selected' 			=> $wppa_session['superalbum'], 
												'addpleaseselect' 	=> true, 
												'root' 				=> $album_root, 
												'content' 			=> true,
												'sort'				=> $sort,
												'path' 				=> ( ! wppa( 'in_widget' ) )
												 ) ) . 
			'</select><br />
			<input' .
				' type="radio"' .
				' name="wppa-slide"' .
				' value="nil" ' .
				( $wppa_session['superview'] == 'thumbs' ? $checked : '' ) .
				' >' .
				__( 'Thumbnails', 'wppa' ) .
				'<br />
			<input' .
				' type="radio"' .
				' name="wppa-slide"' .
				' value="1" ' . 
				( $wppa_session['superview'] == 'slide' ? $checked : '' ) .
				' >' .
				__( 'Slideshow', 'wppa' ) .
				'<br />
			<input type="hidden" name="wppa-occur" value="1" />
			<input type="hidden" name="wppa-superview" value="1" />
			<input type="submit" value="'.__( 'Submit', 'wppa' ).'" />
		</form>
	</div>
	';
	
	return $result;
}

// The tagcloud box
function wppa_tagcloud_box( $seltags = '', $minsize = '8', $maxsize = '24' ) {

	if ( is_feed() ) return;

	wppa_container( 'open' );
	
	wppa_out(	'<div' .
					' id="wppa-tagcloud-' . wppa( 'mocc' ) . '"' .
					' class="wppa-box wppa-tagcloud"' .
					' style="'.__wcs( 'wppa-box' ).__wcs( 'wppa-tagcloud' ).'"' .
					' >' .
					wppa_get_tagcloud_html( $seltags, $minsize, $maxsize ) .
					'<div class="wppa-clear" style="'.__wis( 'clear:both;' ).'" >' .
					'</div>' .
				'</div>'
			);
			
	wppa_container( 'close' );
}

// Get html for tagcloud
function wppa_get_tagcloud_html( $seltags = '', $minsize = '8', $maxsize = '24' ) {
global $wppa;

	$page = wppa_get_the_landing_page( 'wppa_tagcloud_linkpage', __a( 'Tagged photos' ) );

	$result 	= '';
	if ( wppa_opt( 'wppa_tagcloud_linkpage' ) ) {
		$hr = wppa_get_permalink( $page );
		if ( wppa_opt( 'wppa_tagcloud_linktype' ) == 'album' ) {
			$hr .= 'wppa-album=0&amp;wppa-cover=0&amp;wppa-occur=1';
		}
		if ( wppa_opt( 'wppa_tagcloud_linktype' ) == 'slide' ) {
			$hr .= 'wppa-album=0&amp;wppa-cover=0&amp;wppa-occur=1&amp;slide';
		}
	}
	else {
		return __a( 'Please select a tagcloud landing page in Table VI-C3b' );
	}
	$tags = wppa_get_taglist();
	if ( $tags ) {
		$top = '0';
		foreach ( $tags as $tag ) {	// Find largest percentage
			if ( $tag['fraction'] > $top ) $top = $tag['fraction'];
		}
		if ( $top ) $factor = ( $maxsize - $minsize ) / $top;
		else $factor = '1.0';
		$selarr = $seltags ? explode( ',', $seltags ) : array();
		foreach ( $tags as $tag ) {
			if ( ! $seltags || in_array( $tag['tag'], $selarr ) ) {
				$href 		= $hr . '&amp;wppa-tag=' . str_replace( ' ', '%20', $tag['tag'] );
				$title 		= sprintf( '%d photos - %s%%', $tag['count'], $tag['fraction'] * '100' );
				$name 		= $tag['tag'];
				$size 		= floor( $minsize + $tag['fraction'] * $factor );
				$result    .= 	'<a' .
									' href="' . $href . '"' .
									' title="' . $title . '"' .
									' style="font-size:' . $size . 'px;"' .
									' >' .
									$name .
								'</a> ';
			}
		}
	}
	
	return $result;
}

// The multitag box
function wppa_multitag_box( $nperline = '2', $seltags = '' ) {

	if ( is_feed() ) return;
	
	wppa_container( 'open' );
	
	wppa_out( 	'<div' .
					' id="wppa-multitag-' . wppa( 'mocc' ) . '"' .
					' class="wppa-box wppa-multitag"' .
					' style="' . __wcs( 'wppa-box' ) . __wcs( 'wppa-multitag' ) . '"' .
					' >' .
					wppa_get_multitag_html( $nperline, $seltags ) .
					'<div class="wppa-clear" style="'.__wis( 'clear:both;' ).'" >' .
					'</div>' .
				'</div>' 
			);
			
	wppa_container( 'close' );
}

// The html for multitag widget
function wppa_get_multitag_html( $nperline = '2', $seltags = '' ) {
global $wppa;

	$or_only = wppa_switch( 'tags_or_only' );
	$page = wppa_get_the_landing_page( 'wppa_multitag_linkpage', __a( 'Multi Tagged photos' ) );
	
	$result 	= '';
	if ( wppa_opt( 'wppa_multitag_linkpage' ) ) {
		$hr = wppa_get_permalink( $page );
		if ( wppa_opt( 'wppa_multitag_linktype' ) == 'album' ) {
			$hr .= 'wppa-album=0&wppa-cover=0&wppa-occur=1';
		}
		if ( wppa_opt( 'wppa_multitag_linktype' ) == 'slide' ) {
			$hr .= 'wppa-album=0&wppa-cover=0&wppa-occur=1&slide';
		}
	}
	else {
		return __a( 'Please select a multitag landing page in Table VI-C4b' );
	}
	$tags = wppa_get_taglist();
	
	$result .= '
	<script type="text/javascript">
	function wppaProcessMultiTagRequest() {
	var any = false;
	var url="'.$hr.'&wppa-tag=";';
	
	if ( $or_only ) {
		$result .= '
		var andor = "or";';
	}
	else {
	$result .= '
		var andor = "and";
			if ( document.getElementById( "andoror-'.$wppa['mocc'].'" ).checked ) andor = "or";
		var sep;';
	}
	
	$result .= '
	if ( andor == "and" ) sep = ","; else sep = ";";
	';
	
	$selarr = $seltags ? explode( ',', $seltags ) : array();
	if ( $tags ) foreach ( $tags as $tag ) {
		if ( ! $seltags || in_array( $tag['tag'], $selarr ) ) {
			$result .= '
			if ( document.getElementById( "wppa-'.str_replace( ' ', '_', $tag['tag']).'" ).checked ) {
				url+="'.str_replace(' ', '%20', $tag['tag']).'"+sep;
				any = true;
			}';
		}
	}	
	
	$result .= '
	if ( any ) document.location = url;
	else alert ( "'.__a( 'Please check the tag(s) that the photos must have' ).'" );
	}</script>
	';
	
	$qtag = wppa_get_get( 'tag' );
	$andor = $or_only ? 'or' : 'and'; // default
	if ( strpos( $qtag, ',' ) ) {
		$querystringtags = explode( ',',wppa_get_get( 'tag' ) );
	}
	elseif ( strpos( $qtag, ';' ) ) {
		$querystringtags = explode( ';', wppa_get_get( 'tag' ) );
		$andor = 'or';
	}
	else $querystringtags = wppa_get_get( 'tag' );

	if ( $tags ) {
	
		if ( ! $or_only ) {
			$result .= 	'<table class="wppa-multitag-table">' .
							'<tr>' .
								'<td>' .
									'<input' .
										' class="radio"' .
										' name="andor-' . wppa( 'mocc' ) . '"' .
										' value="and"' .
										' id="andorand-' . wppa( 'mocc' ) . '"' .
										' type="radio"' .
										( $andor == 'and' ? ' checked="checked"' : '' ) .
									' />' .
									'&nbsp;' . __a( 'And', 'wppa_theme' ) .
								'</td>' .
								'<td>' .
									'<input' .
										' class="radio"' .
										' name="andor-' . wppa( 'mocc' ) . '"' .
										' value="or"' .
										' id="andoror-' . wppa( 'mocc' ) . '"' .
										' type="radio"' .
										( $andor == 'or' ? ' checked="checked"' : '' ) .
									' />' .
									'&nbsp;' . __a( 'Or', 'wppa_theme' ) .
								'</td>' .
							'</tr>' .
						'</table>';
		}

		$count 		= '0';
		$checked 	= '';		
		$tropen 	= false;
		
		$result 	.= '<table class="wppa-multitag-table">';
		
		foreach ( $tags as $tag ) {
			if ( ! $seltags || in_array( $tag['tag'], $selarr ) ) {
				if ( $count % $nperline == '0' ) {
					$result .= '<tr>';
					$tropen = true;
				}
				if ( is_array( $querystringtags ) ) {
					$checked = in_array( $tag['tag'], $querystringtags ) ? 'checked="checked"' : '';
				}
				$result .= 	'<td' .
								' style="'.__wis( 'padding-right:4px;' ).'"' .
								' >' .
								'<input' .
									' type="checkbox"' .
									' id="wppa-'.str_replace( ' ', '_', $tag['tag'] ).'"' .
									' ' . $checked . 
								' />' .
								'&nbsp;' . str_replace( ' ', '&nbsp;', $tag['tag'] ) .
							'</td>';
				$count++;
				if ( $count % $nperline == '0' ) {
					$result .= '</tr>';
					$tropen = false;
				}
			}
		}
		
		if ( $tropen ) {
			while ( $count % $nperline != '0' ) {
				$result .= '<td></td>';
				$count++;
			}
			$result .= '</tr>';
		}
		$result .= '</table>';
		$result .= 	'<input' .
						' type="button"' .
						' onclick="wppaProcessMultiTagRequest()"' .
						' value="' . __a( 'Find!' ) . '"' .
					' />';
	}
	
	return $result;
}

// Make html for sharebox
function wppa_get_share_html( $id, $key = '', $js = true ) {
global $wppa;
global $wppa_locale;

	$do_it = false;
	if ( ! $wppa['is_slideonly'] || $key == 'lightbox' ) {
		if ( wppa_switch( 'share_on' ) && ! $wppa['in_widget'] ) $do_it = true;
		if ( wppa_switch( 'share_on_widget' ) && $wppa['in_widget'] ) $do_it = true;
		if ( wppa_switch( 'share_on_lightbox' ) ) $do_it = true;
	}
	if ( ! $do_it ) return '';

	// The share url
	$share_url = wppa_get_image_page_url_by_id( $id, wppa_switch( 'share_single_image' ) );
	$share_url = str_replace( '&amp;', '&', $share_url );
	
	// The share title
	$photo_name = wppa_get_photo_name( $id );
	
	// The share description
	$photo_desc = wppa_html( wppa_get_photo_desc( $id ) );
	$photo_desc = strip_shortcodes( wppa_strip_tags( $photo_desc, 'all' ) );

	// The default description
	$site = str_replace( '&amp;', __a( 'and' ), get_bloginfo( 'name' ) );
	$see_on_site = sprintf( __a( 'See this image on %s' ), $site );
	
	// The share image. Must be the fullsize image for facebook. 
	// If you take the thumbnail, facebook takes a different image at random.
	$share_img = wppa_get_photo_url( $id );

	// The icon size
	if ( ( $wppa['in_widget'] && $key != 'lightbox' ) || $key == 'thumb' ) {
		$s = '16';
	}
	else {
		$s = wppa_opt( 'wppa_share_size' );
	}
	
	// qr code
	if ( wppa_switch( 'share_qr' ) && $key != 'thumb' ) {	
		$src 	= 	'http://api.qrserver.com/v1/create-qr-code/' .
						'?data=' . urlencode( $share_url ) .
						'&size=80x80' .
						'&color=' . trim( wppa_opt( 'wppa_qr_color' ), '#' ) .
						'&bgcolor=' . trim( wppa_opt( 'wppa_qr_bgcolor' ), '#' );
		$qr 	= 	'<div style="float:left; padding:2px;" >' .
						'<img' .
							' src="' . $src . '"' .
							' title="' . esc_attr( $share_url ) . '"' .
							' alt="' . __a('QR code') . '"' .
						' />' .
					'</div>';
	}	
	else {
		$qr = '';
	}
	
	// twitter share button
	if ( wppa_switch( 'share_twitter' ) ) {	
		$tweet = urlencode( $see_on_site ) . ': ';
		$tweet_len = strlen( $tweet ) + '1';
		
		$tweet .= urlencode( $share_url );
		
		// find first '/' after 'http( s )://' rest doesnt count for twitter chars
		$url_len = strpos( $share_url, '/', 8 ) + 1;	
		$tweet_len += ( $url_len > 1 ) ? $url_len : strlen( $share_url );
		
		$rest_len = 140 - $tweet_len;
		
		if ( wppa_switch( 'show_full_name' ) ) {
			if ( $rest_len > strlen( $photo_name ) ) {
				$tweet .= ' ' . urlencode( $photo_name );
				$rest_len -= strlen( $photo_name );
				$rest_len -= '2';
			}
			else {
				$tweet .= ' '. urlencode( substr( $photo_name, 0, $rest_len ) ) . '...';
				$rest_len -= strlen( substr( $photo_name, 0, $rest_len ) );
				$rest_len -= '5';
			}
		}
		
		if ( $photo_desc ) {
			if ( $rest_len > strlen( $photo_desc ) ) {
				$tweet .= ': ' . urlencode( $photo_desc );
			}
			elseif ( $rest_len > 8 ) {
				$tweet .= ': '. urlencode( substr( $photo_desc, 0, $rest_len ) ) . '...';
			}
		}
		
		$tw = 	'<div style="float:left; padding:2px;" >' .
					'<a' .
						' title="' . sprintf( __a( 'Tweet %s on Twitter' ), esc_attr( $photo_name ) ) . '"' .
						' href="https://twitter.com/intent/tweet?text='.$tweet.'"' .
						' target="_blank"' .
						' >' .
						'<img' .
							' src="' . wppa_get_imgdir() . 'twitter.png"' .
							' style="height:' . $s . 'px;"' .
							' alt="' . esc_attr( __a( 'Share on Twitter' ) ) . '"' .
						' />' .
					'</a>' .
				'</div>';
	}
	else {
		$tw = '';
	}

	// Google
	if ( wppa_switch( 'share_google' ) ) {
		$go = 	'<div style="float:left; padding:2px;" >' .
					'<a' .
						' title="' . sprintf( __a( 'Share %s on Google+' ), esc_attr( $photo_name ) ) . '"' .
						' href="https://plus.google.com/share?url=' . urlencode( $share_url ) . '"' .
						' onclick="javascript:window.open( this.href, \"\", \"menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\" );return false;"' .
						' target="_blank"' .
						' >' .
						'<img' .
							' src="' . wppa_get_imgdir() . 'google.png"' .
							' style="height:' . $s . 'px;"' .
							' alt="' . esc_attr( __a( 'Share on Google+' ) ) . '"' .
						' />' .
					'</a>' .
				'</div>';
	}
	else {
		$go = '';
	}
	
	// Pinterest
	$desc = urlencode( $see_on_site ).': '.urlencode( $photo_desc );
	if ( strlen( $desc ) > 500 ) $desc = substr( $desc, 0, 495 ).'...';
	if ( wppa_switch( 'share_pinterest' ) ) {
		$pi = 	'<div style="float:left; padding:2px;" >' .
					'<a' .
						' title="' . sprintf( __a( 'Share %s on Pinterest' ), esc_attr( $photo_name ) ) . '"' .
						' href="http://pinterest.com/pin/create/button/?url=' . urlencode( $share_url ) .
							'&media=' . urlencode( str_replace( '/thumbs/', '/', $share_img ) ) . 
							'&description=' . $desc .
							'"' .
						' target="_blank"' .
						' >' .
						'<img' .
							' src="' . wppa_get_imgdir() . 'pinterest.png" style="height:' . $s . 'px;"' .
							' alt="' . esc_attr( __a( 'Share on Pinterest' ) ) . '"' .
						' />' .
					'</a>' .
				'</div>';

	}
	else {
		$pi = '';
	}
	
	// LinkedIn
	if ( wppa_switch( 'share_linkedin' ) && $key != 'thumb' && $key != 'lightbox' ) {
		$li = 	'<script' .
					' type="text/javascript"' .
					' src="//platform.linkedin.com/in.js"' .
					' >' .
					'lang: ' . $wppa_locale .
				'</script>' .
				'<script' .
					' type="IN/Share"' .
					' data-url="' . urlencode( $share_url ) . '"' .
					' data-counter="top"' .
					' >' .
				'</script>';
		if ( $js ) {
			$li = str_replace( '<', '[', $li );
		}
	}
	else {
		$li = '';
	}
	
	// Facebook
	$fb = '';
	$need_fb_init = false;
	$small = ( 'thumb' == $key );
	if ( 'lightbox' == $key ) {
		if ( wppa_switch( 'facebook_like' ) && wppa_switch( 'share_facebook' ) ) {
			$lbs = 'max-width:62px; max-height:96px; overflow:show;';
		}
		else {
			$lbs = 'max-width:62px; max-height:64px; overflow:show;';
		}
	}
	else {
		$lbs = '';
	}
	
	// Share
	if ( wppa_switch( 'share_facebook' ) && ! wppa_switch( 'facebook_like' ) ) { 
		if ( $small ) {
			$fb .= 	'<div' .
						' class="fb-share-button"' .
						' style="float:left;"' .
						' data-href="' . $share_url . '"' .
						' data-type="icon"' .
						' >' .
					'</div>';
		}
		else {
			$disp = wppa_opt( 'wppa_fb_display' );
			if ( 'standard' == $disp ) {
				$disp = 'button';
			}
			$fb .= 	'<div' .
						' class="fb-share-button"' .
						' style="float:left; '. $lbs . '"' .
						' data-width="200"' .
						' data-href="' . $share_url . '"' .
						' data-type="' . $disp . '"' .
						' >' .
					'</div>';
		}
		$need_fb_init = true;
	}
	
	// Like
	if ( wppa_switch( 'facebook_like' ) && ! wppa_switch( 'share_facebook' ) ) {
		if ( $small ) {
			$fb .= 	'<div' .
						' class="fb-like"' .
						' style="float:left;"' .
						' data-href="' . $share_url . '"' .
						' data-layout="button"' .
						' >' .
					'</div>';
		}
		else {
			$fb .= 	'<div' .
						' class="fb-like"' .
						' style="float:left; '.$lbs.'"' .
						' data-width="200"' .
						' data-href="' . $share_url . '"' .
						' data-layout="' . wppa_opt( 'wppa_fb_display' ) . '"' .
						' >' .
					'</div>';
		}
		$need_fb_init = true;
	}

	// Like and share
	if ( wppa_switch( 'facebook_like' ) && wppa_switch( 'share_facebook' ) ) {
		if ( $small ) {
			$fb .= 	'<div' .
						' class="fb-like"' .
						' style="float:left;"' .
						' data-href="' . $share_url . '"' .
						' data-layout="button"' .
						' data-action="like"' .
						' data-show-faces="false"' .
						' data-share="true"' .
						' >' .
					'</div>';
		}
		else {
			$fb .= 	'<div' .
						' class="fb-like"' .
						' style="float:left; '.$lbs.'"' .
						' data-width="200"' .
						' data-href="' . $share_url . '"' .
						' data-layout="' . wppa_opt( 'wppa_fb_display' ) . '"' .
						' data-action="like"' .
						' data-show-faces="false"' .
						' data-share="true"' .
						' >' .
					'</div>';
		}
		$need_fb_init = true;
	}

	// Comments
	if ( wppa_switch( 'facebook_comments' ) && ! $wppa['in_widget'] && $key != 'thumb' && $key != 'lightbox' ) {
		$width = $wppa['auto_colwidth'] ? '100%' : wppa_get_container_width( true );
		if ( wppa_switch( 'facebook_comments' ) ) {
			$fb .= 	'<div style="color:blue;clear:both" >' .
						__a( 'Comment on Facebook:' ) . 
					'</div>';
			$fb .= 	'<div class="fb-comments" data-href="'.$share_url.'" data-width='.$width.'></div>';
			$need_fb_init = true;
		}
	}
	
	// Need init?
	if ( $need_fb_init ) {
		if ( $js && $key != 'thumb' ) {
			$fb .= '[script>wppaFbInit();[/script>';
		}
		else {
			$fb .= '<script>wppaFbInit();</script>';
		}
	}

	return $qr.$tw.$go.$pi.$li.$fb.'<div style="clear:both"></div>';

}

// The upload box
function wppa_upload_box() {

	// Init
	$alb = wppa( 'start_album' );
	
	// Feature enabled?
	if ( ! wppa_switch( 'user_upload_on' ) ) {
		return;	
	}
	
	// Must login ?
	if ( wppa_switch( 'user_upload_login' ) ) {
		if ( ! is_user_logged_in() ) return;
	}

	// Have i access?
	if ( $alb ) {
	
		// Access to this album ?
		if ( ! wppa_have_access( $alb ) ) return;
	}
	else {
	
		// Access to any album ?
		if ( ! wppa_have_access( '0' ) ) return;
	}
	
	// Open container
	wppa_container( 'open' );
	
	// Open div
	wppa_out( 	'<div' .
					' id="wppa-upload-box-' . wppa( 'mocc' ) . '"' .
					' class="wppa-box wppa-upload"' .
					' style="'.__wcs( 'wppa-box' ).__wcs( 'wppa-upload' ).'"' .
					' >' 
			);

		// Do the dirty work
		wppa_user_create_html( $alb, wppa_get_container_width( 'netto' ), 'uploadbox' );
		wppa_user_upload_html( $alb, wppa_get_container_width( 'netto' ), 'uploadbox' );
	
	// Clear
	wppa_out( '<div style="clear:both;"></div></div>' );
	
	// Close container
	wppa_container( 'close' );
}

// Frontend delete album, for use in the album box
function wppa_user_destroy_html( $alb, $width, $where, $rsp ) {

	// Feature enabled ?
	if ( ! wppa_switch( 'user_destroy_on' ) ) {
		return;
	}
	
	// Must login ?
	if ( wppa_switch( 'user_create_login' ) ) {
		if ( ! is_user_logged_in() ) return;
	}
	
	// Album access ?
	if ( ! wppa_have_access( $alb ) ) {
		return;
	}
	
	// Been naughty ?
	if ( wppa_is_user_blacklisted() ) {
		return;
	}

	// Make the html
	wppa_out( 	'<div style="clear:both">' .
					'<a' .
						' style="float:left; cursor:pointer;"' .
						' onclick="' .
							'jQuery(this).html(\'' . __a('Working...') . '\');' .
							'wppaAjaxDestroyAlbum(' . $alb . ',\'' . wp_create_nonce( 'wppa_nonce_' . $alb ) . '\');' .
							'jQuery(this).html(\'' . __a('Delete album') . '\');' .
							'"' .
						' >' . 
						__a('Delete album') . 
					'</a>' .
				'</div>' 
			);

}

// Frontend create album, for use in the upload box, the widget or in the album and thumbnail box
function wppa_user_create_html( $alb, $width, $where = '', $mcr = false ) {

	// Init
	$mocc 	= wppa( 'mocc' );
	$occur 	= wppa( 'occur' );
	if ( $alb < '0' ) {
		$alb = '0';
	}

	// Feature enabled ?
	if ( ! wppa_switch( 'user_create_on' ) ) return;			

/*	
	if ( wppa_switch( 'user_create_login' ) ) {
		if ( ! is_user_logged_in() ) return;					// Must login
	}
*/

	if ( $alb && ! wppa_have_access( $alb ) ) {
		return;						// No album access
	}

//	if ( is_user_logged_in() ) {
		if ( ! $alb && ! wppa_can_create_top_album() ) return;	// Current logged in user can not create a toplevel album
		if ( $alb && ! wppa_can_create_album() ) return;		// Current logged in user can not create a sub-album
//	}

	if ( ! wppa_user_is( 'administrator' ) && wppa_switch( 'owner_only' ) ) {
		if ( $alb ) {
			$album = wppa_cache_album( $alb );
			if ( $album['owner'] == '--- public ---' ) return;	// Need to be admin to create public subalbums
		}
	}

//	if ( wppa_is_user_blacklisted() ) return;

	// In a widget or multi column responsive?
	$small = ( wppa( 'in_widget' ) == 'upload' || $mcr );

	// Create the return url
	$returnurl = wppa_get_permalink();
	if ( $where == 'cover' ) {
		$returnurl .= 'wppa-album=' . $alb . '&amp;wppa-cover=0&amp;wppa-occur=' . $occur;
	}
	elseif ( $where == 'thumb' ) {
		$returnurl .= 'wppa-album=' . $alb . '&amp;wppa-cover=0&amp;wppa-occur=' . $occur;
	}
	elseif ( $where == 'widget' || $where == 'uploadbox' ) {
	}
	if ( wppa( 'page' ) ) $returnurl .= '&amp;wppa-page=' . wppa( 'page' );
	$returnurl = trim( $returnurl, '?' );
	
	$returnurl = wppa_trim_wppa_( $returnurl );
	
	$t = $mcr ? 'mcr-' : '';
	
	// The links
	wppa_out(
		'<div style="clear:both"></div>' .
		'<a' .
			' id="wppa-cr-' . $alb . '-' . $mocc . '"' .
			' class="wppa-create-' . $where . '"' .
			' onclick="' .
				'jQuery( \'#wppa-create-'.$t.$alb.'-'.$mocc.'\' ).css( \'display\',\'block\' );'.	// Open the Create form
				'jQuery( \'#wppa-cr-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'none\' );'.			// Hide the Create link
				'jQuery( \'#wppa-up-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'none\' );'.			// Hide the Upload link
				'jQuery( \'#wppa-ea-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'none\' );'.			// Hide the Edit link
				'jQuery( \'#wppa-cats-' . $alb . '-' . $mocc . '\' ).css( \'display\',\'none\' );'.	// Hide catogory
				'jQuery( \'#_wppa-cr-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'block\' );'. 		// Show backlink
				'_wppaDoAutocol( ' . $mocc . ' )'.													// Trigger autocol
				'"' .
			' style="float:left; cursor:pointer;"' .
			'> ' . 
			__a( 'Create Album' ) .
		'</a>' .
		'<a' .
			' id="_wppa-cr-' . $alb . '-' . $mocc . '"' .
			' class="wppa-create-' . $where . '"' .
			' onclick="' .
				'jQuery( \'#wppa-create-'.$t.$alb.'-'.$mocc.'\' ).css( \'display\',\'none\' );'.	// Hide the Create form
				'jQuery( \'#wppa-cr-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'block\' );'.			// Show the Create link
				'jQuery( \'#wppa-up-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'block\' );'.			// Show the Upload link
				'jQuery( \'#wppa-ea-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'block\' );'.			// Show the Edit link
				'jQuery( \'#wppa-cats-' . $alb . '-' . $mocc . '\' ).css( \'display\',\'block\' );'.	// Show catogory
				'jQuery( \'#_wppa-cr-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'none\' );'. 			// Hide backlink
				'_wppaDoAutocol( ' . $mocc . ' )'.													// Trigger autocol
				'"' .
			' style="float:right; cursor:pointer;display:none;"' .
			' >' . 
			__a( 'Close' ) .
		'</a>' 
	);
	
	// The create form
	wppa_out( 
		'<div' .
			' id="wppa-create-'.$t.$alb.'-'.$mocc.'"' .
			' class="wppa-file-'.$t.$mocc.'"' .
			' style="width:'.$width.'px;text-align:center;display:none;"' .
			' >' .
			'<form' .
				' id="wppa-creform-'.$alb.'-'.$mocc.'"' .
				' action="'.$returnurl.'"' .
				' method="post"' .
				' >' .
				wppa_nonce_field( 'wppa-album-check' , 'wppa-nonce', false, false, $alb ) .
				'<input type="hidden" name="wppa-album-parent" value="'.$alb.'" />' .
				'<input type="hidden" name="wppa-fe-create" value="yes" />' .
				// Name
				'<div'.
					' class="wppa-box-text wppa-td"' .
					' style="' .
						'clear:both;' .
						'float:left;' .
						'text-align:left;' .
						__wcs( 'wppa-box-text' ) .
						__wcs( 'wppa-td' ) .
						'"' .
					' >' .
					__a( 'Enter album name.' ) .
					'&nbsp;<span style="font-size:10px;" >' .
					__a( 'Don\'t leave this blank!' ) . '</span>' .
				'</div>' .
				'<input' .
					' type="text"' .
					' class="wppa-box-text wppa-file-'.$t.$mocc.'"' .
					' style="padding:0; width:'.( $width-6 ).'px; '.__wcs( 'wppa-box-text' ).'"' .
					' name="wppa-album-name"' .
				' />' .
				// Description
				'<div' .
					' class="wppa-box-text wppa-td"' .
					' style="' .
						'clear:both;' .
						'float:left;' .
						'text-align:left;' .
						__wcs( 'wppa-box-text' ) .
						__wcs( 'wppa-td' ) .
						'"' .
					' >' .
					__a( 'Enter album description' ) .
				'</div>' .
				'<textarea' .
					' class="wppa-user-textarea wppa-box-text wppa-file-'.$t.$mocc.'"' .
					' style="padding:0;height:120px; width:'.( $width-6 ).'px; '.__wcs( 'wppa-box-text' ).'"' .
					' name="wppa-album-desc" >' .
				'</textarea>' .
				'<div style="float:left; margin: 6px 0;" >' .
					'<div style="float:left;">' .
						wppa_make_captcha( wppa_get_randseed( 'session' ) ) .
					'</div>' .
					'<input' .
						' type="text"' .
						' id="wppa-captcha-'.$mocc.'"' .
						' name="wppa-captcha"' .
						' style="margin-left: 6px; width:50px; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'"' .
					' />' .
				'</div>' .
				'<input' .
					' type="submit"' .
					' class="wppa-user-submit"' .
					' style="margin: 6px 0; float:right; '.__wcs( 'wppa-box-text' ).'"' .
					' value="'.__a( 'Create album' ).'"' .
				' />' .
			'</form>' .
		'</div>'
	);
}

// Frontend upload html, for use in the upload box, the widget or in the album and thumbnail box
function wppa_user_upload_html( $alb, $width, $where = '', $mcr = false ) {
static $seqno;

	// Init
	$mocc 	= wppa( 'mocc');
	$occur 	= wppa( 'occur' );
	
	// Using seqno to distinguish from different places within one occurrence because 
	// the album no is not known when there is a selection box.
	if ( $seqno ) $seqno++;
	else $seqno = '1';

	// Feature enabled?
	if ( ! wppa_switch( 'user_upload_on' ) ) return;
	
	// Login required?
	if ( wppa_switch( 'user_upload_login' ) ) {
		if ( ! is_user_logged_in() ) return;
	}
	
	// I should have access to this album ( $alb > 0 ).
	if ( $alb > '0' ) {
		$album_owner = wppa_get_album_item( $alb, 'owner' );
		if ( $album_owner != wppa_get_user() && $album_owner != '--- public ---' && ! wppa_have_access( $alb ) ) {
			return;
		}
	}

	// Find max files for the user
	$allow_me = wppa_allow_user_uploads();
	if ( ! $allow_me ) {
		if ( wppa_switch( 'show_album_full' ) ) {
			wppa_out( 	'<div style="clear:both"></div>' .
						'<span style="color:red">' .
							__a( 'Max uploads reached' ) .
							wppa_time_to_wait_html( '0', true ) .
						'</span>'
					);
		}
		return;	
	}
	
	// Find max files for the album
	$allow_alb = wppa_allow_uploads( $alb );
	if ( ! $allow_alb ) {
		if ( wppa_switch( 'show_album_full' ) ) {
			wppa_out(	'<div style="clear:both"></div>' .
						'<span style="color:red">' .
							__a( 'Max uploads reached' ) .
							wppa_time_to_wait_html( $alb ) .
						'</span>'
					);
		}
		return;	
	}
	
	if ( wppa_is_user_blacklisted() ) return;

	// Find max files for the system
	$allow_sys = ini_get( 'max_file_uploads' );
	
	// THE max
	if ( $allow_me == '-1' ) $allow_me = $allow_sys;
	if ( $allow_alb == '-1' ) $allow_alb = $allow_sys;
	$max = min( $allow_me, $allow_alb, $allow_sys );

	// In a widget or multi column responsive?
	$small = ( wppa( 'in_widget' ) == 'upload' || $mcr );

	// Ajax upload?
	$ajax_upload = wppa_switch( 'ajax_upload' ) && wppa_browser_can_html5();
	
	// Create the return url
	if ( $ajax_upload ) {
		$returnurl = wppa_switch( 'ajax_non_admin' ) ? WPPA_URL.'/wppa-ajax-front.php' : admin_url('admin-ajax.php');
		$returnurl .= '?action=wppa&amp;wppa-action=do-fe-upload';
	}
	else {
		$returnurl = wppa_get_permalink();
		if ( $where == 'cover' ) {
			$returnurl .= 'wppa-album=' . $alb . '&amp;wppa-cover=0&amp;wppa-occur=' . $occur;
		}
		elseif ( $where == 'thumb' ) {
			$returnurl .= 'wppa-album=' . $alb . '&amp;wppa-cover=0&amp;wppa-occur=' . $occur;
		}
		elseif ( $where == 'widget' || $where == 'uploadbox' ) {
		}
		if ( wppa( 'page' ) ) $returnurl .= '&amp;wppa-page=' . wppa( 'page' );
		$returnurl = trim( $returnurl, '?' );

		$returnurl = wppa_trim_wppa_( $returnurl );
	}

	// Make the HTML
	$t = $mcr ? 'mcr-' : '';
	wppa_out( 
		'<div style="clear:both"></div>' .
		'<a' .
			' id="wppa-up-'.$alb.'-'.$mocc.'"' .
			' class="wppa-upload-'.$where.'"' .
			' onclick="' .
				'jQuery( \'#wppa-file-'.$t.$alb.'-'.$mocc.'\' ).css( \'display\',\'block\' );'.		// Open the Upload form
				'jQuery( \'#wppa-up-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'none\' );'.			// Hide the Upload link
				'jQuery( \'#wppa-cr-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'none\' );'.			// Hide the Create link
				'jQuery( \'#wppa-ea-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'none\' );'.			// Hide the Edit link
				'jQuery( \'#wppa-cats-' . $alb . '-' . $mocc . '\' ).css( \'display\',\'none\' );'.	// Hide catogory
				'jQuery( \'#_wppa-up-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'block\' );'. 		// Show backlink
				'_wppaDoAutocol( ' . $mocc . ' )' .													// Trigger autocol
				'"' .
			' style="float:left; cursor:pointer;' .
			'" >' .
			__a( 'Upload Photo' ) .
		'</a>' .
		'<a' .
			' id="_wppa-up-'.$alb.'-'.$mocc.'"' .
			' class="wppa-upload-'.$where.'"' .
			' onclick="' .
				'jQuery( \'#wppa-file-'.$t.$alb.'-'.$mocc.'\' ).css( \'display\',\'none\' );'.		// Hide the Upload form
				'jQuery( \'#wppa-cr-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'block\' );'.			// Show the Create link
				'jQuery( \'#wppa-up-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'block\' );'.			// Show the Upload link
				'jQuery( \'#wppa-ea-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'block\' );'.			// Show the Edit link
				'jQuery( \'#wppa-cats-' . $alb . '-' . $mocc . '\' ).css( \'display\',\'block\' );'.	// Show catogory
				'jQuery( \'#_wppa-up-'.$alb.'-'.$mocc.'\' ).css( \'display\',\'none\' );'. 			// Hide backlink
				'_wppaDoAutocol( ' . $mocc . ' )' .													// Trigger autocol
				'"' .
			' style="float:right; cursor:pointer;display:none;' .
			'" >' .
			__a( 'Close' ) .
		'</a>' .
		'<div' .
			' id="wppa-file-'.$t.$alb.'-'.$mocc.'"' .
			' class="wppa-file-'.$t.$mocc.'"' .
			' style="width:'.$width.'px;text-align:center;display:none; clear:both;"' .
			' >' .
			'<form' .
				' id="wppa-uplform-'.$alb.'-'.$mocc.'"' .
				' action="'.$returnurl.'"' .
				' method="post"' .
				' enctype="multipart/form-data"' .
				' >' .
				wppa_nonce_field( 'wppa-check' , 'wppa-nonce', false, false, $alb )
	);
	
	// If no album given: select one		
	if ( ! $alb ) {	
		wppa_out(
			'<select' .
				' id="wppa-upload-album-'.$mocc.'-'.$seqno.'"' .
				' name="wppa-upload-album"' .
				' style="float:left; max-width: '.$width.'px;"' .
				' onchange="jQuery( \'#wppa-sel-'.$alb.'-'.$mocc.'\' ).trigger( \'onchange\' )"' .
				' >' .
				wppa_album_select_a( array ( 	'addpleaseselect' 	=> true, 
												'checkowner' 		=> true, 
												'checkupload' 		=> true, 
												'path' 				=> wppa_switch( 'hier_albsel' ) 
									) ) .
			'</select>' .
			'<br />'
		);
	}
	
	// Album given
	else {
		wppa_out( 	
			'<input' .
				' type="hidden"' .
				' id="wppa-upload-album-'.$mocc.'-'.$seqno.'"' .
				' name="wppa-upload-album"' .
				' value="'.$alb.'"' .
			' />'
		);
	}

	// One only ?
	if ( wppa_switch( 'upload_one_only' ) && ! current_user_can( 'administrator' ) ) {
		wppa_out(	
			'<input' .
				' type="file"' .
				' accept="image/*"' .
				' capture' .
				' class="wppa-user-file"' .
				' style="' .
					'width:auto;' .
					'max-width:' . $width . ';' .
					'margin:6px 0;' .
					'float:left;' .
					__wcs( 'wppa-box-text' ) .
				'"' .
				' id="wppa-user-upload-' . $alb . '-' . $mocc . '"' .
				' name="wppa-user-upload-' . $alb . '-' . $mocc . '[]"' .
				' onchange="jQuery( \'#wppa-user-submit-' . $alb . '-' . $mocc.'\' ).css( \'display\', \'block\' )"' .
			' />'
		);
	}
	
	// Multiple
	else {
		wppa_out(
			'<input' .
				' type="file"' .
				' accept="image/*"' .
				' capture' .
				' multiple="multiple"' .
				' class="wppa-user-file"' .
				' style="' .
					'width:auto;' .
					'max-width:' . $width . ';' .
					'margin:6px 0;' .
					'float:left;' .
					__wcs( 'wppa-box-text' ) .
					'"' .
				' id="wppa-user-upload-' . $alb . '-' . $mocc . '"' .
				' name="wppa-user-upload-' . $alb . '-' . $mocc . '[]"' .
				' onchange="jQuery( \'#wppa-user-submit-' . $alb . '-' . $mocc.'\' ).css( \'display\', \'block\' )"' .
			' />'
		);
	}
	
	// Onclick submit verify album is known
	if ( $alb ) {
		$onclick = 	' onclick="if ( document.getElementById( \'wppa-upload-album-'.$mocc.'-'.$seqno.'\' ).value == 0 )' .
					' {alert( \''.esc_js( __a( 'Please select an album and try again' ) ).'\' );return false;}"';
	}
	else {
		$onclick = '';
	}

	// The submit button
	wppa_out(
		'<input' .
			' type="submit"' .
			' id="wppa-user-submit-' . $alb . '-' . $mocc . '"' . 
			$onclick .
			' style="display:none; margin: 6px 0; float:right; '.__wcs( 'wppa-box-text' ).'"' .
			' class="wppa-user-submit"' .
			' name="wppa-user-submit-'.$alb.'-'.$mocc.'" value="'.__a( 'Upload photo' ).'"' .
		' />' .
		'<div style="clear:both"></div>'
	);

	// if ajax: progression bar
	if ( $ajax_upload ) {
		wppa_out(
			'<div' .
				' id="progress-'.$alb.'-'.$mocc.'"' .
				' class="wppa-progress"' .
				' style="border-color:'.wppa_opt( 'wppa_bcolor_upload' ).'"' .
				' >' .
				'<div id="bar-'.$alb.'-'.$mocc.'" class="wppa-bar" ></div>' .
				'<div id="percent-'.$alb.'-'.$mocc.'" class="wppa-percent" >0%</div >' .
			'</div>' .
			'<div id="message-'.$alb.'-'.$mocc.'" class="wppa-message" ></div>'
		);
	}
	
	// Explanation
	if ( ! wppa_switch( 'upload_one_only' ) && ! current_user_can( 'administrator' ) ) {
		if ( $max ) {
			wppa_out( 
				'<span style="font-size:10px;" >' .
					sprintf( __a( 'You may upload up to %s photos at once if your browser supports HTML-5 multiple file upload' ), $max ) .
				'</span>'
			);
			$maxsize = wppa_check_memory_limit( false );
			if ( is_array( $maxsize ) ) {
				wppa_out(
					'<br />' .
					'<span style="font-size:10px;" >' .
						sprintf( __a( 'Max photo size: %d x %d (%2.1f MegaPixel)' ), $maxsize['maxx'], $maxsize['maxy'], $maxsize['maxp']/( 1024*1024 ) ) .
					'</span>'
				);
			}
		}
	}
			
	// Copyright notice
	if ( wppa_switch( 'copyright_on' ) ) {
		wppa_out(
			'<div style="clear:both;" >' .
				__( wppa_opt( 'copyright_notice' ) ) .
			'</div>'
		);
	}
			
	// Watermark
	if ( wppa_switch( 'watermark_on' ) && ( wppa_switch( 'watermark_user' ) ) ) { 
		wppa_out(
			'<table' .
				' class="wppa-watermark wppa-box-text"' .
				' style="margin:0; border:0; '.__wcs( 'wppa-box-text' ).'"' .
				' >' .
				'<tbody>' .
					'<tr valign="top" style="border: 0 none; " >' .
						'<td' .
							' class="wppa-box-text wppa-td"' .
							' style="'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'"' .
							' >' .
							__a( 'Apply watermark file:' ) .
						'</td>' .
					'</tr>' .
					'<tr>' .
						'<td' .
							' class="wppa-box-text wppa-td"' .
							' style="width: '.$width.';'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'"' .
							' >' .
							'<select' .
								' style="margin:0; padding:0; text-align:left; width:auto; "' .
								' name="wppa-watermark-file"' .
								' id="wppa-watermark-file"' .
								' >' .
								wppa_watermark_file_select() .
							'</select>' .
						'</td>' .
					'</tr>' .
					'<tr valign="top" style="border: 0 none; " >' .
						'<td' .
							' class="wppa-box-text wppa-td"' .
							' style="width: '.$width.';'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'"' .
							' >' . 
							__a( 'Position:' ) . 
						'</td>' .
						( $small ? '</tr><tr>' : '' ) .
						'<td' .
							' class="wppa-box-text wppa-td"' .
							' style="width: '.$width.';'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'"' .
							' >' .
							'<select' .
								' style="margin:0; padding:0; text-align:left; width:auto; "' .
								' name="wppa-watermark-pos"' .
								' id="wppa-watermark-pos"' .
								' >' .
								wppa_watermark_pos_select() .
							'</select>' .
						'</td>' .
					'</tr>' .
				'</tbody>' .
			'</table>'
		);
	}
			
	// Name
	if ( wppa_switch( 'wppa_name_user' ) ) {
		switch ( wppa_opt( 'wppa_newphoto_name_method' ) ) {
			case 'none':
				$expl = '';
				break;
			case '2#005':
				$expl = __a( 'If you leave this blank, iptc tag 005 (Graphic name) will be used as photoname if available, else the original filename will be used as photo name.' );
				break;
			case '2#120':
				$expl = __a( 'If you leave this blank, iptc tag 120 (Caption) will be used as photoname if available, else the original filename will be used as photo name.' );
				break;
			default:
				$expl = __a( 'If you leave this blank, the original filename will be used as photo name.' );
		}
		wppa_out( 
			'<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >' .
				__a( 'Enter photo name.' ).'&nbsp;<span style="font-size:10px;" >'.$expl.'</span>' .
			'</div>' .
			'<input' .
				' type="text"' .
				' class="wppa-box-text wppa-file-'.$t.$mocc.'"' .
				' style="padding:0; width:'.( $width-6 ).'px; '.__wcs( 'wppa-box-text' ).'"' .
				' name="wppa-user-name"' .
			' />'
		);
	}
		
	// Description user fillable ?
	if ( wppa_switch( 'wppa_desc_user' ) ) {
		$desc = wppa_switch( 'apply_newphoto_desc_user' ) ? stripslashes( wppa_opt( 'wppa_newphoto_description' ) ) : '';
		wppa_out(
			'<div' .
				' class="wppa-box-text wppa-td"' .
				' style="clear:both; float:left; text-align:left; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'"' .
				' >' .
				__a( 'Enter/modify photo description' ) .
			'</div>' .
			'<textarea' .
				' class="wppa-user-textarea wppa-box-text wppa-file-'.$t.$mocc.'"' .
				' style="height:120px; width:'.( $width-6 ).'px; '.__wcs( 'wppa-box-text' ).'"' .
				' name="wppa-user-desc"' . 
				' >' .
				$desc .
			'</textarea>'
		);
	}
	
	// Predefined desc ?
	elseif ( wppa_switch( 'apply_newphoto_desc_user' ) ) {
		wppa_out( 
			'<input' .
				' type="hidden"' .
				' value="' . esc_attr( wppa_opt( 'wppa_newphoto_description' ) ) . '"' .
				' name="wppa-user-desc"' .
			' />' 
		);
	}
		
	// Custom fields
	if ( wppa_switch( 'fe_custom_fields' ) ) {
		for ( $i = '0'; $i < '10' ; $i++ ) {
			if ( wppa_opt( 'custom_caption_'.$i ) ) {
				wppa_out( 
					'<div' .
						' class="wppa-box-text wppa-td"' .
						' style="clear:both; float:left; text-align:left; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'"' .
						' >'.
							__( wppa_opt( 'custom_caption_'.$i ) ) . ': ' .
							( wppa_switch( 'custom_visible_'.$i ) ? '' : '&nbsp;<small><i>(&nbsp;'.__a( 'hidden', 'wppa' ).'&nbsp;)</i></small>' ) .
					'</div>' .
					'<input' .
						' type="text"' .
						' class="wppa-box-text wppa-file-'.$t.$mocc.'"' .
						' style="padding:0; width:'.( $width-6 ).'px; '.__wcs( 'wppa-box-text' ).'"' .
						' name="wppa-user-custom-'.$i.'"' .
					' />'
				);
			}
		}
	}
		
	// Tags
	if ( wppa_switch( 'fe_upload_tags' ) ) {
	
		// Prepare onclick action
		$onc = 'wppaPrevTags(\'wppa-sel-'.$alb.'-'.$mocc.'\', \'wppa-inp-'.$alb.'-'.$mocc.'\', \'wppa-upload-album-'.$mocc.'-'.$seqno.'\', \'wppa-prev-'.$alb.'-'.$mocc.'\')';
		
		// Open the tag enter area
		wppa_out( "\n".'<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >' );
		
			// Selection boxes 1..3
			for ( $i = '1'; $i < '4'; $i++ ) {
				if ( wppa_switch( 'up_tagselbox_on_'.$i ) ) {
					wppa_out( "\n".'<div style="float:left; margin-right:4px;" >' );
					wppa_out( '<small>'.__( wppa_opt( 'up_tagselbox_title_'.$i ) ).'</small><br />' );
					wppa_out( "\n".'<select id="wppa-sel-'.$alb.'-'.$mocc.'-'.$i.'" style="float:left; margin-right: 4px;" name="wppa-user-tags-'.$i.'[]" '.( wppa_switch( 'wppa_up_tagselbox_multi_'.$i ) ? 'multiple' : '' ).' onchange="'.$onc.'" >' );
					if ( wppa_opt( 'up_tagselbox_content_'.$i ) ) {	// List of tags supplied
						$tags = explode( ',', wppa_opt( 'up_tagselbox_content_'.$i ) );
						wppa_out( '<option value="" >&nbsp;</option>' );
						if ( is_array( $tags ) ) foreach ( $tags as $tag ) {
							wppa_out( '<option class="wppa-sel-'.$alb.'-'.$mocc.'" value="'.$tag.'">'.$tag.'</option>' );
						}							
					}
					else {											// All existing tags
						$tags = wppa_get_taglist();
						wppa_out( '<option value="" >&nbsp;</option>' );
						if ( is_array( $tags ) ) foreach ( $tags as $tag ) {
							wppa_out( '<option class="wppa-sel-'.$alb.'-'.$mocc.'" value="'.$tag['tag'].'">'.$tag['tag'].'</option>' );
						}							
					}
					wppa_out( "\n".'</select>' );
					wppa_out( '</div>' );
				}
			}
			
			// New tags
			if ( wppa_switch( 'wppa_up_tag_input_on' ) ) {
				wppa_out( '<div style="float:left; margin-right:4px;" >' );
				wppa_out( '<small>'.__( wppa_opt( 'up_tag_input_title' ) ).'</small><br />' );
				wppa_out( '<input id="wppa-inp-'.$alb.'-'.$mocc.'" type="text" class="wppa-box-text " style="padding:0; width:150px; '.__wcs( 'wppa-box-text' ).'" name="wppa-new-tags" onchange="'.$onc.'" />' );
				wppa_out( '</div>' );
			}
					
			// Preview area
			if ( wppa_switch( 'wppa_up_tag_preview' ) ) {
				wppa_out( '<div style="margin:0; clear:both;" >'.__a('Preview tags:').' <small id="wppa-prev-'.$alb.'-'.$mocc.'"></small></div>' );
				wppa_out( '<script type="text/javascript" >jQuery( document ).ready(function() {'.$onc.'})</script>' );
			}

		// Close tag enter area
		wppa_out( '</div>' );
	}
		
	// Done
	wppa_out( '</form></div>' );
	
	// Ajax upload script
	if ( $ajax_upload ) {
		wppa_out( 
			'<script>' .
				'jQuery(document).ready(function() {
	 
					var options = {
						beforeSend: function() {
							jQuery("#progress-'.$alb.'-'.$mocc.'").show();
							//clear everything
							jQuery("#bar-'.$alb.'-'.$mocc.'").width(\'0%\');
							jQuery("#message-'.$alb.'-'.$mocc.'").html("");
							jQuery("#percent-'.$alb.'-'.$mocc.'").html("");
						},
						uploadProgress: function(event, position, total, percentComplete) {
							jQuery("#bar-'.$alb.'-'.$mocc.'").width(percentComplete+\'%\');
							if ( percentComplete < 95 ) {
								jQuery("#percent-'.$alb.'-'.$mocc.'").html(percentComplete+\'%\');
							}
							else {
								jQuery("#percent-'.$alb.'-'.$mocc.'").html(\'Processing...\');
							}
						},
						success: function() {
							jQuery("#bar-'.$alb.'-'.$mocc.'").width(\'100%\');
							jQuery("#percent-'.$alb.'-'.$mocc.'").html(\'Done!\');
						},
						complete: function(response) {
							jQuery("#message-'.$alb.'-'.$mocc.'").html( \'<span style="font-size: 10px;" >\'+response.responseText+\'</span>\' );'.
							( $where == 'thumb' ? 'document.location.reload(true)' : '' ).'
						},
						error: function() {
							jQuery("#message-'.$alb.'-'.$mocc.'").html( \'<span style="color: red;" >'.__a( 'ERROR: unable to upload files.' ).'</span>\' );
						}
					};
	 
					jQuery("#wppa-uplform-'.$alb.'-'.$mocc.'").ajaxForm(options);
				});
			</script>'
		);
	}
}

// Frontend edit album info
function wppa_user_albumedit_html( $alb, $width, $where = '', $mcr = false ) {
global $wppa;

	$album = wppa_cache_album( $alb );

	if ( ! wppa_switch( 'user_album_edit_on' ) ) return; 	// Feature not enabled
	if ( ! $alb ) return;										// No album given
	if ( ! wppa_have_access( $alb ) ) return;					// No rights
	if ( $album['owner'] == '--- public ---' && ! current_user_can( 'wppa_admin' ) ) return;	// Public albums are not publicly editable
	
	$t = $mcr ? 'mcr-' : '';
	
	// Create the return url
	$returnurl = wppa_get_permalink();
	if ( $where == 'cover' ) {
		$returnurl .= 'wppa-album='.$alb.'&amp;wppa-cover=1&amp;wppa-occur='.$wppa['occur'];
	}
	elseif ( $where == 'thumb' ) {
		$returnurl .= 'wppa-album='.$alb.'&amp;wppa-cover=0&amp;wppa-occur='.$wppa['occur'];
	}
	elseif ( $where == 'widget' || $where == 'uploadbox' ) {
	}
	if ( $wppa['page'] ) $returnurl .= '&amp;wppa-page='.$wppa['page'];
	$returnurl = trim( $returnurl, '?' );

		
	$result = '
	<div style="clear:both;"></div>
	<a id="wppa-ea-'.$alb.'-'.$wppa['mocc'].'" class="wppa-aedit-'.$where.'" onclick="'.
									'jQuery( \'#wppa-fe-div-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'block\' );'.		// Open the Edit form
									'jQuery( \'#wppa-ea-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'.			// Hide the Edit link
									'jQuery( \'#wppa-cr-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'.			// Hide the Create libk
									'jQuery( \'#wppa-up-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'.			// Hide the upload link
									'jQuery( \'#wppa-cats-' . $alb . '-' . $wppa['mocc'] . '\' ).css( \'display\',\'none\' );'.	// Hide catogory
									'jQuery( \'#_wppa-ea-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'block\' );'. 		// Show backlink
									'_wppaDoAutocol( ' . $wppa['mocc'] . ' )' .													// Trigger autocol
									'" style="float:left; cursor:pointer;">									
		'.__a( 'Edit albuminfo' ).'
	</a>
	<a id="_wppa-ea-'.$alb.'-'.$wppa['mocc'].'" class="wppa-aedit-'.$where.'" onclick="'.
									'jQuery( \'#wppa-fe-div-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'.		// Hide the Edit form
									'jQuery( \'#wppa-cr-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'block\' );'.			// Show the Create link
									'jQuery( \'#wppa-up-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'block\' );'.			// Show the Upload link
									'jQuery( \'#wppa-ea-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'block\' );'.			// Show the Edit link
									'jQuery( \'#wppa-cats-' . $alb . '-' . $wppa['mocc'] . '\' ).css( \'display\',\'block\' );'.	// Show catogory
									'jQuery( \'#_wppa-ea-'.$alb.'-'.$wppa['mocc'].'\' ).css( \'display\',\'none\' );'. 			// Hide backlink
									'_wppaDoAutocol( ' . $wppa['mocc'] . ' )'.													// Trigger autocol
									'" style="float:right; cursor:pointer;display:none;">
		'.__a( 'Close' ).'
	</a>
	<div id="wppa-fe-div-'.$alb.'-'.$wppa['mocc'].'" style="display:none;" >
		<form action="'.$returnurl.'" method="post">
			<input type="hidden" name="wppa-albumeditnonce" id="album-nonce-'.$wppa['mocc'].'-'.$alb.'" value="'.wp_create_nonce( 'wppa_nonce_'.$alb ).'" />
			<input type="hidden" name="wppa-albumeditid" id="wppaalbum-id-'.$wppa['mocc'].'-'.$alb.'" value="'.$alb.'" />
			<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.
				__a( 'Enter album name.' ).'&nbsp;<span style="font-size:10px;" >'.__a( 'Don\'t leave this blank!' ).'</span>
			</div>
			<input name="wppa-albumeditname" id="wppaalbum-name-'.$wppa['mocc'].'-'.$alb.'" class="wppa-box-text wppa-file-'.$t.$wppa['mocc'].'" value="'.esc_attr( stripslashes( $album['name'] ) ).'" style="padding:0; width:'.( $width-6 ).'px; '.__wcs( 'wppa-box-text' ).'" />
			<div class="wppa-box-text wppa-td" style="clear:both; float:left; text-align:left; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.
				__a( 'Album description:' ).'
			</div>
			<textarea name="wppa-albumeditdesc" id="wppaalbum-desc-'.$wppa['mocc'].'-'.$alb.'" class="wppa-user-textarea wppa-box-text wppa-file-'.$t.$wppa['mocc'].'" style="padding:0;height:120px; width:'.( $width-6 ).'px; '.__wcs( 'wppa-box-text' ).'" >'.esc_textarea( stripslashes( $album['description'] ) ).'</textarea>
			<input type="submit" name="wppa-albumeditsubmit" class="wppa-user-submit" style="margin: 6px 0; float:right; '.__wcs( 'wppa-box-text' ).'" value="'.__a( 'Update album' ).'" />
		</form>
	</div>';
	$wppa['out'] .= $result;
}

// Build the html for the comment box
function wppa_comment_html( $id, $comment_allowed ) {
global $wpdb;
global $wppa;
global $current_user;
global $wppa_first_comment_html;

	$result = '';
	if ( $wppa['in_widget'] ) return $result;		// NOT in a widget
	
	// Find out who we are either logged in or not
	$vis = is_user_logged_in() ? $vis = 'display:none; ' : '';
	if ( !$wppa_first_comment_html ) {
		$wppa_first_comment_html = true;
		// Find user
		if ( wppa_get_post( 'comname' ) ) $wppa['comment_user'] = wppa_get_post( 'comname' );
		if ( wppa_get_post( 'comemail' ) ) $wppa['comment_email'] = wppa_get_post( 'comemail' );
		elseif ( is_user_logged_in() ) {
			get_currentuserinfo();
			$wppa['comment_user'] = $current_user->display_name; //user_login;
			$wppa['comment_email'] = $current_user->user_email;
		}
	}

	// Loop the comments already there
	$n_comments = 0;
	if ( wppa_switch( 'comments_desc' ) ) $ord = 'DESC'; else $ord = '';
	$comments = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.WPPA_COMMENTS.' WHERE photo = %s ORDER BY id '.$ord, $id ), ARRAY_A );
	wppa_dbg_q( 'Q-Comm' );
	$com_count = count( $comments );
	$color = 'darkgrey';
	if ( wppa_opt( 'wppa_fontcolor_box' ) ) $color = wppa_opt( 'wppa_fontcolor_box' );
	if ( $comments ) {
		$result .= '<div id="wppa-comtable-wrap-'.$wppa['mocc'].'" style="display:none;" >';
			$result .= '<table id="wppacommentstable-'.$wppa['mocc'].'" class="wppa-comment-form" style="margin:0; "><tbody>';
			foreach( $comments as $comment ) {
				// Show a comment either when it is approved, or it is pending and mine or i am a moderator
				if ( $comment['status'] == 'approved' || current_user_can( 'wppa_moderate' ) || current_user_can( 'wppa_comments' ) || ( ( $comment['status'] == 'pending' || $comment['status'] == 'spam' ) && $comment['user'] == $wppa['comment_user'] ) ) {
					$n_comments++;
					$result .= '<tr class="wppa-comment-'.$comment['id'].'" valign="top" style="border-bottom:0 none; border-top:0 none; border-left: 0 none; border-right: 0 none; " >';
						$result .= '<td valign="top" class="wppa-box-text wppa-td" style="vertical-align:top; width:30%; border-width: 0 0 0 0; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >';
							$result .= $comment['user'].' '.__a( 'wrote:' );
							$result .= '<br /><span style="font-size:9px; ">'.wppa_get_time_since( $comment['timestamp'] ).'</span>';
							if ( wppa_opt( 'wppa_comment_gravatar' ) != 'none' ) {
								// Find the default
								if ( wppa_opt( 'wppa_comment_gravatar' ) != 'url' ) {
									$default = wppa_opt( 'wppa_comment_gravatar' );
								}
								else {
									$default = wppa_opt( 'wppa_comment_gravatar_url' );
								}
								// Find the avatar
								$avt = '';
								$usr = get_user_by( 'login', $comment['user'] );
								if ( $usr ) {	// Local Avatar ?
									$avt = str_replace( "'", "\"", get_avatar( $usr->ID, wppa_opt( 'wppa_gravatar_size' ), $default ) );
								}
								if ( $avt == '' ) {	// Global avatars off, try myself
									$avt = '<img class="wppa-box-text wppa-td" src="http://www.gravatar.com/avatar/'.md5( strtolower( trim( $comment['email'] ) ) ).'.jpg?d='.urlencode( $default ).'&s='.wppa_opt( 'wppa_gravatar_size' ).'" alt="'.__a('Avatar').'" />';
								}
								// Compose the html
								$result .= '<div class="com_avatar">'.$avt.'</div>';
							}
						$result .= '</td>';
						$txtwidth = floor( wppa_get_container_width() * 0.7 ).'px';
						$result .= '<td class="wppa-box-text wppa-td" style="width:70%; word-wrap:break-word; border-width: 0 0 0 0;'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.
										'<p class="wppa-comment-textarea wppa-comment-textarea-'.$wppa['mocc'].'" style="margin:0; background-color:transparent; width:'.$txtwidth.'; max-height:90px; overflow:auto; word-wrap:break-word;'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.
											html_entity_decode( esc_js( stripslashes( wppa_convert_smilies( $comment['comment'] ) ) ) );
										
											if ( $comment['status'] != 'approved' && ( current_user_can( 'wppa_moderate' ) || current_user_can( 'wppa_comments' ) ) ) {
												if ( $wppa['no_esc'] ) $result .= wppa_moderate_links( 'comment', $id, $comment['id'] );
												else $result .= wppa_html( esc_js( wppa_moderate_links( 'comment', $id, $comment['id'] ) ) );
											}
											elseif ( $comment['status'] == 'pending' && $comment['user'] == $wppa['comment_user'] ) {
												$result .= '<br /><span style="color:red; font-size:9px;" >'.__a( 'Awaiting moderation' ).'</span>';
											}
											elseif ( $comment['status'] == 'spam' && $comment['user'] == $wppa['comment_user'] ) {
												$result .= '<br /><span style="color:red; font-size:9px;" >'.__a( 'Marked as spam' ).'</span>';
											}
											
											
											$result .= '</p>';
						$result .= '</td>';
					$result .= '</tr>';
					$result .= '<tr class="wppa-comment-'.$comment['id'].'"><td colspan="2" style="padding:0"><hr style="background-color:'.$color.'; margin:0;" /></td></tr>';
				}
			}
			$result .= '</tbody></table>';
		$result .= '</div>';
	}
	
	// See if we are currently in the process of adding/editing this comment
	$is_current = ( $id == $wppa['comment_photo'] && $wppa['comment_id'] );
	// $debugtext=' ( id='.$id.', comment_photo='.$wppa['comment_photo'].', comment_id='.$wppa['comment_id'].' )';
	if ( $is_current ) {
		$txt = $wppa['comment_text'];
		$btn = __a( 'Edit!' );
	}
	else {
		$txt = '';
		$btn = __a( 'Send!' );
	}
	
	// Prepare the callback url
	$returnurl = wppa_get_permalink();

	$album = wppa_get_get( 'album' );
	if ( $album !== false ) $returnurl .= 'wppa-album='.$album.'&';
	$cover = wppa_get_get( 'cover' );
	if ( $cover ) $returnurl .= 'wppa-cover='.$cover.'&';
	$slide = wppa_get_get( 'slide' );
	if ( $slide !== false ) $returnurl .= 'wppa-slide&';
	$occur = wppa_get_get( 'occur' );
	if ( $occur ) $returnurl .= 'wppa-occur='.$occur.'&';
	$lasten = wppa_get_get( 'lasten' );
	if ( $lasten ) $returnurl .= 'wppa-lasten='.$lasten.'&';
	$topten = wppa_get_get( 'topten' );
	if ( $topten ) $returnurl .= 'wppa-topten='.$topten.'&';
	$comten = wppa_get_get( 'comten' );
	if ( $comten ) $returnurl .= 'wppa-comten='.$comten.'&';
	$tag = wppa_get_get( 'tag' );
	if ( $tag ) $returnurl .= 'wppa-tag='.$tag.'&';
	
	$returnurl .= 'wppa-photo='.$id;
	
	// The comment form
	if ( $comment_allowed ) {
		$result .= '<div id="wppa-comform-wrap-'.$wppa['mocc'].'" style="display:none;" >';
			$result .= '<form id="wppa-commentform-'.$wppa['mocc'].'" class="wppa-comment-form" action="'.$returnurl.'" method="post" style="" onsubmit="return wppaValidateComment( '.$wppa['mocc'].' )">';
				$result .= wp_nonce_field( 'wppa-nonce-'.wppa('mocc') , 'wppa-nonce-'.wppa('mocc'), false, false );//, $alb );
				if ( $album ) $result .= '<input type="hidden" name="wppa-album" value="'.$album.'" />';
				if ( $cover ) $result .= '<input type="hidden" name="wppa-cover" value="'.$cover.'" />';
				if ( $slide ) $result .= '<input type="hidden" name="wppa-slide" value="'.$slide.'" />';
				$result .= '<input type="hidden" name="wppa-returnurl" id="wppa-returnurl-'.wppa( 'mocc' ).'" value="'.$returnurl.'" />';
				if ( $is_current ) $result .= '<input type="hidden" id="wppa-comment-edit-'.$wppa['mocc'].'" name="wppa-comment-edit" value="'.$wppa['comment_id'].'" />';
				$result .= '<input type="hidden" name="wppa-occur" value="'.$wppa['occur'].'" />';

				$result .= '<table id="wppacommenttable-'.$wppa['mocc'].'" style="margin:0;">';
					$result .= '<tbody>';
						$result .= '<tr valign="top" style="'.$vis.'">';
							$result .= '<td class="wppa-box-text wppa-td" style="width:30%; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.__a( 'Your name:' ).'</td>';
							$result .= '<td class="wppa-box-text wppa-td" style="width:70%; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" ><input type="text" name="wppa-comname" id="wppa-comname-'.$wppa['mocc'].'" style="width:100%; " value="'.$wppa['comment_user'].'" /></td>';
						$result .= '</tr>';
						if ( wppa_switch( 'comment_email_required' ) ) {
							$result .= '<tr valign="top" style="'.$vis.'">';
								$result .= '<td class="wppa-box-text wppa-td" style="width:30%; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.__a( 'Your email:' ).'</td>';
								$result .= '<td class="wppa-box-text wppa-td" style="width:70%; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" ><input type="text" name="wppa-comemail" id="wppa-comemail-'.$wppa['mocc'].'" style="width:100%; " value="'.$wppa['comment_email'].'" /></td>';
							$result .= '</tr>';
						}
						$result .= '<tr valign="top" style="vertical-align:top;">';	
							$result .= '<td valign="top" class="wppa-box-text wppa-td" style="vertical-align:top; width:30%; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >'.__a( 'Your comment:' ).'<br />'.$wppa['comment_user'].'<br />';
							if ( wppa_switch( 'comment_captcha' ) ) {
								$wid = '20%';
								if ( wppa_opt( 'wppa_fontsize_box' ) ) $wid = ( wppa_opt( 'wppa_fontsize_box' ) * 1.5 ).'px';
								$captkey = $id;
								if ( $is_current ) $captkey = $wpdb->get_var( $wpdb->prepare( 'SELECT `timestamp` FROM `'.WPPA_COMMENTS.'` WHERE `id` = %s', $wppa['comment_id'] ) );
								wppa_dbg_q( 'Q-Com-ts' );								
								$result .= wppa_make_captcha( $captkey ).'<input type="text" id="wppa-captcha-'.$wppa['mocc'].'" name="wppa-captcha" style="width:'.$wid.'; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" />&nbsp;';
							}
// orig							$result .= '<input type="submit" name="commentbtn" value="'.$btn.'" style="margin:0;" /></td>';
							$result .= '<input type="button" name="commentbtn" onclick="wppaAjaxComment( '.$wppa['mocc'].', '.$id.' )" value="'.$btn.'" style="margin:0 4px 0 0;" />';
							$result .= '<img id="wppa-comment-spin-'.$wppa['mocc'].'" src="'.wppa_get_imgdir().'wpspin.gif" style="display:none;" />';
							$result .= '</td>';
							$result .= '<td valign="top" class="wppa-box-text wppa-td" style="vertical-align:top; width:70%; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >';
/*							if ( wppa_switch( 'use_wp_editor' ) ) {
								$quicktags_settings = array( 'buttons' => 'strong,em,link,block,ins,ul,ol,li,code,close' );
								ob_start();
								wp_editor( stripslashes( $txt ), 'wppacomment'.wppa_alfa_id( $id ), array( 'wpautop' => false, 'media_buttons' => false, 'textarea_rows' => '6', 'tinymce' => false, 'quicktags' => $quicktags_settings ) );
								$editor = ob_get_clean();
								$result .= str_replace( "'", '"', $editor );
							}
							else {
/**/
								if ( wppa_switch( 'comment_smiley_picker' ) ) $result .= wppa_get_smiley_picker_html( 'wppa-comment-'.$wppa['mocc'] );
								$result .= '<textarea name="wppa-comment" id="wppa-comment-'.$wppa['mocc'].'" style="height:60px; width:100%; ">'.esc_textarea( stripslashes( $txt ) ).'</textarea>';
/*							}
/* */
							$result .= '</td>';
						$result .= '</tr>';
					$result .= '</tbody>';
				$result .= '</table>';
			$result .= '</form>';
			// $result.=$debugtext;
		$result .= '</div>';
	}
	else {
		if ( wppa_switch( 'login_links' ) ) {
			$result .= sprintf( __a( 'You must <a href="%s">login</a> to enter a comment' ), site_url( 'wp-login.php', 'login' ) );
		}
		else {
			$result .= __a( 'You must login to enter a comment' );
		}
	}
	
	$result .= '<div id="wppa-comfooter-wrap-'.$wppa['mocc'].'" style="display:block;" >';
		$result .= '<table id="wppacommentfooter-'.$wppa['mocc'].'" class="wppa-comment-form" style="margin:0;">';
			$result .= '<tbody><tr style="text-align:center; "><td style="text-align:center; cursor:pointer;'.__wcs( 'wppa-box-text' ).'" ><a onclick="wppaOpenComments( '.$wppa['mocc'].', -1 ); return false;">'; // wppaStartStop( '.$wppa['mocc'].', -1 ); return false;">';
			if ( $n_comments ) {
				$result .= sprintf( __a( '%d comments' ), $n_comments );
			}
			else {
				if ( $comment_allowed ) {
					$result .= __a( 'Leave a comment' );
				}
			}
		$result .= '</a></td></tr></tbody></table>';
	$result .= '</div><div style="clear:both"></div>';

	return $result;
}

// The smiley picker for the comment box
function wppa_get_smiley_picker_html( $elm_id ) {
static $wppa_smilies;
global $wpsmiliestrans;

	// Fill inverted smilies array if needed
	if ( ! is_array( $wppa_smilies ) ) {
		foreach( array_keys( $wpsmiliestrans ) as $idx ) {
			if ( ! isset ( $wppa_smilies[$wpsmiliestrans[$idx]] ) ) {
				$wppa_smilies[$wpsmiliestrans[$idx]] = $idx;
			}
		}
	}

	// Make the html
	$result = '';
	if ( is_array( $wppa_smilies ) ) {
		foreach ( array_keys( $wppa_smilies ) as $key ) {
			$onclick 	= esc_attr( 'wppaInsertAtCursor( document.getElementById( "' . $elm_id . '" ), " ' . $wppa_smilies[$key] . ' " )' );
			$title 		= trim( $wppa_smilies[$key], ':' );
			$result 	.= 	'<a onclick="'.$onclick.'" title="'.$title.'" >';
			$result 	.= 		wppa_convert_smilies( $wppa_smilies[$key] );
			$result 	.= 	'</a>';
		}
	}
	else {
		$result .= __a('Smilies are not available');
	}
	
	return $result;
} 

// IPTC box
function wppa_iptc_html( $photo ) {
global $wppa;
global $wpdb;
global $wppaiptcdefaults;
global $wppaiptclabels;

	// Get the default ( one time only )
	if ( ! $wppa['iptc'] ) {
		$tmp = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_IPTC."` WHERE `photo` = %s ORDER BY `tag`", '0' ), "ARRAY_A" );
		wppa_dbg_q( 'Q-iptc0' );
		if ( ! $tmp ) return '';	// Nothing defined
		$wppaiptcdefaults = false;	// Init
		$wppaiptclabels = false;	// Init
		foreach ( $tmp as $t ) {
			$wppaiptcdefaults[$t['tag']] = $t['status'];
			$wppaiptclabels[$t['tag']] = $t['description'];
		}
		$wppa['iptc'] = true;
	}

	$count = 0;

	// Get the photo data
	$iptcdata = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_IPTC."` WHERE `photo` = %s ORDER BY `tag`", $photo ), "ARRAY_A" );
	wppa_dbg_q( 'Q-iptc' );
	if ( $iptcdata ) {
		// Open the container content
		$result = '<div id="iptccontent-'.$wppa['mocc'].'" >';
		// Open or closed?
		$d1 = wppa_switch( 'show_iptc_open' ) ? 'display:none;' : 'display:inline;';
		$d2 = wppa_switch( 'show_iptc_open' ) ? 'display:inline;' : 'display:none;';
		// Process data
		$onclick = esc_attr( "wppaStopShow( ".$wppa['mocc']." ); jQuery( '.wppa-iptc-table-".$wppa['mocc']."' ).css( 'display', '' ); jQuery( '.-wppa-iptc-table-".$wppa['mocc']."' ).css( 'display', 'none' )" );
		$result .= '<a class="-wppa-iptc-table-'.$wppa['mocc'].'" onclick="'.$onclick.'" style="cursor:pointer;'.$d1.'" >'.__a( 'Show IPTC data' ).'</a>';

		$onclick = esc_attr( "jQuery( '.wppa-iptc-table-".$wppa['mocc']."' ).css( 'display', 'none' ); jQuery( '.-wppa-iptc-table-".$wppa['mocc']."' ).css( 'display', '' )" );
		$result .= '<a class="wppa-iptc-table-'.$wppa['mocc'].'" onclick="'.$onclick.'" style="cursor:pointer;'.$d2.'" >'.__a( 'Hide IPTC data' ).'</a>';

		$result .= '<div style="clear:both;" ></div><table class="wppa-iptc-table-'.$wppa['mocc'].' wppa-detail" style="border:0 none; margin:0;'.$d2.'" ><tbody>';
		$oldtag = '';
		foreach ( $iptcdata as $iptcline ) {
			if ( ! isset( $wppaiptcdefaults[$iptcline['tag']] ) ) continue;
			if ( $iptcline['status'] == 'hide' ) continue;														// Photo status is hide
			if ( $iptcline['status'] == 'default' && $wppaiptcdefaults[$iptcline['tag']] == 'hide' ) continue;	// P s is default and default is hide
			if ( $iptcline['status'] == 'default' && $wppaiptcdefaults[$iptcline['tag']] == 'option' && ! trim( $iptcline['description'], "\x00..\x1F " ) ) continue;	// P s is default and default is optional and field is empty
			
			$count++;
			$newtag = $iptcline['tag'];
			if ( $newtag != $oldtag && $oldtag != '' ) $result .= '</td></tr>';	// Close previous line
			if ( $newtag == $oldtag ) {
				$result .= '; ';							// next item with same tag
			}
			else {
				$result .= '<tr style="border-bottom:0 none; border-top:0 none; border-left: 0 none; border-right: 0 none; "><td class="wppa-iptc-label wppa-box-text wppa-td" style="'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >';						// Open new line
				$result .= esc_js( __( $wppaiptclabels[$newtag] ) );
				$result .= '</td><td class="wppa-iptc-value wppa-box-text wppa-td" style="'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >';
			}
			$result .= esc_js( trim( __( $iptcline['description'] ) ) );
			$oldtag = $newtag;
		}	
		if ( $oldtag != '' ) $result .= '</td></tr>';	// Close last line
		$result .= '</tbody></table></div>';
	}
	if ( ! $count ) {
		$result = '<div id="iptccontent-'.$wppa['mocc'].'" >'.__a( 'No IPTC data' ).'</div>';
	}

	return ( $result );
}

// EXIF box
function wppa_exif_html( $photo ) {
global $wppa;
global $wpdb;
global $wppaexifdefaults;
global $wppaexiflabels;

	// Get the default ( one time only )
	if ( ! $wppa['exif'] ) {
		$tmp = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_EXIF."` WHERE `photo` = %s ORDER BY `tag`", '0' ), "ARRAY_A" );
		wppa_dbg_q( 'Q-exif0' );
		if ( ! $tmp ) return '';	// Nothing defined
		$wppaexifdefaults = false;	// Init
		$wppaexiflabels = false;	// Init
		foreach ( $tmp as $t ) {
			$wppaexifdefaults[$t['tag']] = $t['status'];
			$wppaexiflabels[$t['tag']] = $t['description'];
		}
		$wppa['exif'] = true;
	}

	$count = 0;

	// Get the photo data
	$exifdata = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `".WPPA_EXIF."` WHERE `photo`=%s ORDER BY `tag`", $photo ), "ARRAY_A" );
	wppa_dbg_q( 'Q-exif' );
	if ( $exifdata ) {
		// Open the container content
		$result = '<div id="exifcontent-'.$wppa['mocc'].'" >';
		// Open or closed?
		$d1 = wppa_switch( 'show_exif_open' ) ? 'display:none;' : 'display:inline;';
		$d2 = wppa_switch( 'show_exif_open' ) ? 'display:inline;' : 'display:none;';
		// Process data
		$onclick = esc_attr( "wppaStopShow( ".$wppa['mocc']." ); jQuery( '.wppa-exif-table-".$wppa['mocc']."' ).css( 'display', '' ); jQuery( '.-wppa-exif-table-".$wppa['mocc']."' ).css( 'display', 'none' )" );
		$result .= '<a class="-wppa-exif-table-'.$wppa['mocc'].'" onclick="'.$onclick.'" style="cursor:pointer;'.$d1.'" >'.__a( 'Show EXIF data' ).'</a>';

		$onclick = esc_attr( "jQuery( '.wppa-exif-table-".$wppa['mocc']."' ).css( 'display', 'none' ); jQuery( '.-wppa-exif-table-".$wppa['mocc']."' ).css( 'display', '' )" );
		$result .= '<a class="wppa-exif-table-'.$wppa['mocc'].'" onclick="'.$onclick.'" style="cursor:pointer;'.$d2.'" >'.__a( 'Hide EXIF data' ).'</a>';

		$result .= '<div style="clear:both;" ></div><table class="wppa-exif-table-'.$wppa['mocc'].' wppa-detail" style="'.$d2.' border:0 none; margin:0;" ><tbody>';
		$oldtag = '';
		foreach ( $exifdata as $exifline ) {
			if ( ! isset( $wppaexifdefaults[$exifline['tag']] ) ) continue;
			$exifline['description'] = trim( $exifline['description'], "\x00..\x1F " );
			if ( $exifline['status'] == 'hide' ) continue;														// Photo status is hide
			if ( $exifline['status'] == 'default' && $wppaexifdefaults[$exifline['tag']] == 'hide' ) continue;	// P s is default and default is hide
			if ( $exifline['status'] == 'default' && $wppaexifdefaults[$exifline['tag']] == 'option' && ! $exifline['description'] ) continue; // P s is default and default is optional and field is empty

			$count++;
			$newtag = $exifline['tag'];
			if ( $newtag != $oldtag && $oldtag != '' ) $result .= '</td></tr>';	// Close previous line
			if ( $newtag == $oldtag ) {
				$result .= '; ';							// next item with same tag
			}
			else {
				$result .= '<tr style="border-bottom:0 none; border-top:0 none; border-left: 0 none; border-right: 0 none; "><td class="wppa-exif-label wppa-box-text wppa-td" style="'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >';						// Open new line
				$result .= esc_js( __( $wppaexiflabels[$newtag] ) );
				$result .= '</td><td class="wppa-exif-value wppa-box-text wppa-td" style="'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-td' ).'" >';
			}
			$result .= esc_js( trim( __( wppa_format_exif( $exifline['tag'], $exifline['description'] ) ) ) );
			$oldtag = $newtag;
		}	
		if ( $oldtag != '' ) $result .= '</td></tr>';	// Close last line
		$result .= '</tbody></table></div>';
	}
	if ( ! $count ) {
		$result = '<div id="exifcontent-'.$wppa['mocc'].'" >'.__a( 'No EXIF data' ).'</div>';
	}
	
	return ( $result );
}

// Display the album name ( on a thumbnail display ) either on top or at the bottom of the thumbnail area
function wppa_album_name( $key ) {
global $wppa;
global $wpdb;

	if ( $wppa['is_upldr'] ) return;
	if ( $wppa['is_comten'] ) return;
	if ( $wppa['is_lasten'] ) return;
	if ( $wppa['is_featen'] ) return;
	if ( $wppa['supersearch'] ) return;
	if ( $wppa['searchstring'] ) return;
	if ( $wppa['is_tag'] ) return;
	if ( strlen( $wppa['start_album'] ) > '0' && ! wppa_is_int( $wppa['start_album'] ) ) return; // Album enumeration
	
	$result = '';
	if ( wppa_opt( 'wppa_albname_on_thumbarea' ) == $key && $wppa['current_album'] ) {
		$name = wppa_get_album_name( $wppa['current_album'] );
		if ( $key == 'top' ) {
			$result .= '<h3 id="wppa-albname-'.$wppa['mocc'].'" class="wppa-box-text wppa-black" style="padding-right:6px; margin:0; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-black' ).'" >'.$name.'</h3><div style="clear:both" ></div>';
		}
		if ( $key == 'bottom' ) {
			$result .= '<h3 id="wppa-albname-b-'.$wppa['mocc'].'" class="wppa-box-text wppa-black" style="clear:both; padding-right:6px; margin:0; '.__wcs( 'wppa-box-text' ).__wcs( 'wppa-black' ).'" >'.$name.'</h3>';
		}
	}
	$wppa['out'] .= $result;
}

// Display the album description ( on a thumbnail display ) either on top or at the bottom of the thumbnail area
function wppa_album_desc( $key ) {
global $wppa;
global $wpdb;

	if ( $wppa['is_upldr'] ) return;
	if ( $wppa['is_comten'] ) return;
	if ( $wppa['is_lasten'] ) return;
	if ( $wppa['is_featen'] ) return;
	if ( $wppa['supersearch'] ) return;
	if ( $wppa['searchstring'] ) return;
	if ( $wppa['is_tag'] ) return;
	if ( strlen( $wppa['start_album'] ) > '0' && ! wppa_is_int( $wppa['start_album'] ) ) return; // Album enumeration
	
	$result = '';
	if ( wppa_opt( 'wppa_albdesc_on_thumbarea' ) == $key && $wppa['current_album'] ) {
		$desc = wppa_get_album_desc( $wppa['current_album'] );
		if ( $key == 'top' ) {
			$result .= '<div id="wppa-albdesc-'.$wppa['mocc'].'" class="wppa-box-text wppa-black" style="padding-right:6px;'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-black' ).'" >'.$desc.'</div><div style="clear:both" ></div>';
		}
		if ( $key == 'bottom' ) {
			$result .= '<div id="wppa-albdesc-b-'.$wppa['mocc'].'" class="wppa-box-text wppa-black" style="clear:both; padding-right:6px;'.__wcs( 'wppa-box-text' ).__wcs( 'wppa-black' ).'" >'.$desc.'</div>';
		}
	}
	$wppa['out'] .= $result;
}

// The auto age links
function wppa_auto_page_links( $where ) {
global $wppa;
global $wpdb;

	$m = $where == 'bottom' ? 'margin-top:8px;' : '';
	$mustwhere = wppa_opt( 'wppa_auto_page_links' );
	if ( ( $mustwhere == 'top' || $mustwhere == 'both' ) && ( $where == 'top' ) || ( ( $mustwhere == 'bottom' || $mustwhere == 'both' ) && ( $where == 'bottom' ) ) ) {
		$wppa['out'] .= '
			<div id="prevnext1-'.$wppa['mocc'].'" class="wppa-box wppa-nav wppa-nav-text" style="text-align: center; '.__wcs( 'wppa-box' ).__wcs( 'wppa-nav' ).__wcs( 'wppa-nav-text' ).$m.'">';
		$photo = $wppa['single_photo'];
		$thumb = wppa_cache_thumb( $photo );
		$album = $thumb['album'];
		$photos = $wpdb->get_results( $wpdb->prepare( "SELECT `id`, `page_id` FROM `".WPPA_PHOTOS."` WHERE `album` = %s ".wppa_get_photo_order( $album ), $album ), ARRAY_A );
		wppa_dbg_q( 'Q-Ppag' );
		$prevpag = '0';
		$nextpag = '0';
		$curpag  = get_the_ID();
		$count = count( $photos );
		$count_ = $count - 1;
		$current = '0';
		if ( $photos ) {
			foreach ( array_keys( $photos ) as $idx ) {
				if ( $photos[$idx]['page_id'] == $curpag ) {
					if ( $idx != '0' ) $prevpag = wppa_get_the_auto_page( $photos[$idx-1]['id'] ); // ['page_id'];
					if ( $idx != $count_ ) $nextpag = wppa_get_the_auto_page( $photos[$idx+1]['id'] ); // ['page_id'];
					$current = $idx;
				}
			}
		}
		
		if ( $prevpag ) {
			$wppa['out'] .= '
			<a href="'.get_permalink( $prevpag ).'" style="float:left" >'.__( '< Previous', 'wppa' ).'</a>';
		}
		else {
			$wppa['out'] .= '
			<span style="visibility:hidden" >'.__( '< Previous', 'wppa' ).'</span>';
		}
		$wppa['out'] .= ++$current.'/'.$count;
		if ( $nextpag ) {
			$wppa['out'] .= '
			<a href="'.get_permalink( $nextpag ).'" style="float:right" >'.__( 'Next >', 'wppa' ).'</a>';
		}
		else {
			$wppa['out'] .= '
			<span style="visibility:hidden" >'.__( 'Next >', 'wppa' ).'</span>';
		}

		$wppa['out'] .= '
			</div><div style="clear:both"></div>';
	}
}

// The bestof box
function wppa_bestof_box ( $args ) {
global $wppa;

	wppa_container ( 'open' );
	$wppa['out'] .= wppa_nltab ( '+' ).'<div id="wppa-bestof-'.$wppa['mocc'].'" class="wppa-box wppa-bestof" style="'.__wcs( 'wppa-box' ).__wcs( 'wppa-bestof' ).'">';
		$wppa['out'] .= wppa_bestof_html( $args, false );
	$wppa['out'] .= wppa_nltab ( '-' ).'<div style="clear:both; height:4px;"></div></div>';
	wppa_container ( 'close' );
}

// The Bestof html
function wppa_bestof_html( $args, $widget = true ) {

	// Copletify args
	$args = wp_parse_args( ( array ) $args, array( 	'page' 			=> '0',
													'count' 		=> '1',
													'sortby' 		=> 'maxratingcount', 
													'display' 		=> 'photo',
													'period' 		=> 'thisweek',
													'maxratings'	=> 'yes',
													'meanrat' 		=> 'yes',
													'ratcount' 		=> 'yes',
													'linktype' 		=> 'none',
													'size' 			=> wppa_opt( 'wppa_widget_width' ),
													'fontsize' 		=> wppa_opt( 'wppa_fontsize_widget_thumb' ),
													'lineheight' 	=> wppa_opt( 'wppa_fontsize_widget_thumb' ) * 1.5,
													'height' 		=> '200'
											 ) );
											
	// Make args into seperate vars
	extract ( $args );
	
	// Validate args
	if ( ! in_array( $sortby, array ( 'maxratingcount', 'meanrating', 'ratingcount' ) ) ) wppa_dbg_msg ( 'Invalid arg sortby "'.$sortby.'" must be "maxratingcount", "meanrating" or "ratingcount"', 'red', 'force' );
	if ( ! in_array( $display, array ( 'photo', 'owner' ) ) ) wppa_dbg_msg ( 'Invalid arg display "'.$display.'" must be "photo" or "owner"', 'red', 'force' );
	if ( ! in_array( $period, array ( 'lastweek', 'thisweek', 'lastmonth', 'thismonth', 'lastyear', 'thisyear' ) ) ) wppa_dbg_msg ( 'Invalid arg period "'.$period.'" must be "lastweek", "thisweek", "lastmonth", "thismonth", "lastyear" or "thisyear"', 'red', 'force' );
	if ( ! $widget ) $size = $height;
	
	$result = '';
	
	$data = wppa_get_the_bestof( $count, $period, $sortby, $display );
			
	if ( $display == 'photo' ) {
		if ( is_array( $data ) ) {
			foreach ( array_keys( $data ) as $id ) {
				$thumb = wppa_cache_thumb( $id );
				if ( $thumb ) {
					$imgsize		= array( wppa_get_photox( $id ), wppa_get_photoy( $id ) );
					if ( $widget ) {
						$maxw 		= $size;
						$maxh 		= round ( $maxw * $imgsize['1'] / $imgsize['0'] );
					}
					else {
						$maxh 		= $size;
						$maxw 		= round ( $maxh * $imgsize['0'] / $imgsize['1'] );
					}
					$totalh 		= $maxh + $lineheight;
					if ( $maxratings == 'yes' ) $totalh += $lineheight;
					if ( $meanrat == 'yes' ) 	$totalh += $lineheight;
					if ( $ratcount == 'yes' ) 	$totalh += $lineheight;

					if ( $widget ) $clear = 'clear:both; '; else $clear = '';
					$result .= "\n".'<div class="wppa-widget" style="'.$clear.'width:'.$maxw.'px; height:'.$totalh.'px; margin:4px; display:inline; text-align:center; float:left;">'; 
				
						// The medal if at the top
						$result .= wppa_get_medal_html_a( array( 'id' => $id, 'size' => 'M', 'where' => 'top' ) );

						// The link if any
						if ( $linktype != 'none' ) {
							switch ( $linktype ) {
								case 'owneralbums':
									$href = wppa_get_permalink( $page ).'wppa-cover=1&amp;wppa-owner='.$thumb['owner'].'&amp;wppa-occur=1';
									$title = __a( 'See the authors albums', 'wppa' );
									break;
								case 'ownerphotos':
									$href = wppa_get_permalink( $page ).'wppa-cover=0&amp;wppa-owner='.$thumb['owner'].'&photos-only&amp;wppa-occur=1';
									$title = __a( 'See the authors photos', 'wppa' );
									break;
								case 'upldrphotos':
									$href = wppa_get_permalink( $page ).'wppa-cover=0&amp;wppa-upldr='.$thumb['owner'].'&amp;wppa-occur=1';
									$title = __a( 'See all the authors photos', 'wppa' );
									break;
							}
							$result .= '<a href="'.wppa_convert_to_pretty( $href ).'" title="'.$title.'" >';
						}
						
						// The image
						$result .= '<img style="height:'.$maxh.'px; width:'.$maxw.'px;" src="'.wppa_fix_poster_ext( wppa_get_photo_url( $id, '', $maxw, $maxh ), $id ).'" '.wppa_get_imgalt( $id ).'/>';
						
						// The /link
						if ( $linktype != 'none' ) {
							$result .= '</a>';
						}
						
						// The medal if near the bottom
						$result .= wppa_get_medal_html_a( array( 'id' => $id, 'size' => 'M', 'where' => 'bot' ) );

						// The subtitles
						$result .= "\n\t".'<div style="font-size:'.$fontsize.'px; line-height:'.$lineheight.'px; position:absolute; width:'.$maxw.'px; ">';
							$result .= sprintf( __a( 'Photo by: %s' ), $data[$id]['user'] ).'<br />';
							if ( $maxratings 	== 'yes' ) $result .= sprintf( __a( 'Max ratings: %s.' ), $data[$id]['maxratingcount'] ).'<br />';
							if ( $ratcount 		== 'yes' ) $result .= sprintf( __a( 'Votes: %s.' ), $data[$id]['ratingcount'] ).'<br />';
							if ( $meanrat  		== 'yes' ) $result .= sprintf( __a( 'Mean value: %4.2f.' ), $data[$id]['meanrating'] ).'<br />';
						$result .= '</div>';
						$result .= '<div style="clear:both" ></div>';
						
					$result .= "\n".'</div>';
				}
				else {	// No image
					$result .= '<div>'.sprintf( __a( 'Photo %s not found.' ), $id ).'</div>';
				}
			}
		}	
		else {
			$result .= $data;	// No array, print message
		}
	}
	else {	// Display = owner
		if ( is_array( $data ) ) {
			$result .= '<ul>';
			foreach ( array_keys( $data ) as $author ) {
				$result .= '<li>';
				// The link if any
				if ( $linktype != 'none' ) {
					switch ( $linktype ) {
						case 'owneralbums':
							$href = wppa_get_permalink( $page ).'wppa-cover=1&amp;wppa-owner='.$data[$author]['owner'].'&amp;wppa-occur=1';
							$title = __a( 'See the authors albums', 'wppa' );
							break;
						case 'ownerphotos':
							$href = wppa_get_permalink( $page ).'wppa-cover=0&amp;wppa-owner='.$data[$author]['owner'].'&amp;photos-only&amp;wppa-occur=1';
							$title = __a( 'See the authors photos', 'wppa' );
							break;
						case 'upldrphotos':
							$href = wppa_get_permalink( $page ).'wppa-cover=0&amp;wppa-upldr='.$data[$author]['owner'].'&amp;wppa-occur=1';
							$title = __a( 'See all the authors photos', 'wppa' );
							break;
					}
					$result .= '<a href="'.$href.'" title="'.$title.'" >';
				}
				
				// The name
				$result .= $author;

				// The /link
				if ( $linktype != 'none' ) {
					$result .= '</a>';
				}
				
				$result .= '<br/>';
				
				// The subtitles
				$result .= "\n\t".'<div style="font-size:'.wppa_opt( 'wppa_fontsize_widget_thumb' ).'px; line-height:'.$lineheight.'px; ">';
							if ( $maxratings 	== 'yes' ) $result .= sprintf( __a( 'Max ratings: %s.' ), $data[$author]['maxratingcount'] ).'<br />';
							if ( $ratcount 		== 'yes' ) $result .= sprintf( __a( 'Votes: %s.' ), $data[$author]['ratingcount'] ).'<br />';
							if ( $meanrat  		== 'yes' ) $result .= sprintf( __a( 'Mean value: %4.2f.' ), $data[$author]['meanrating'] ).'<br />';
				
				$result .= '</div>';
				$result .= '</li>';
			}
			$result .= '</ul>';
		}
		else {
			$result .= $data;	// No array, print message
		}
	}
	
	return $result;
}