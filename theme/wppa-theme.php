<?php 
/* wppa_theme.php
* Package: wp-photo-album-plus
*
* display the albums/photos/slideshow in a page or post
* Version 5.4.7
*/
function wppa_theme() {

global $wppa_version; $wppa_version = '5-4-00';		// The version number of this file
global $wppa;
global $wppa_opt;
global $wppa_show_statistics;						// Can be set to true by a custom page template

	$curpage = wppa_get_curpage();					// Get the page # we are on when pagination is on, or 1
	$didsome = false;								// Required initializations for pagination
	$n_album_pages = '0';							// "
	$n_thumb_pages = '0';							// "

	// Open container
	wppa_container( 'open' );
	
	// Show statistics if set so by the page template
	if ( $wppa_show_statistics ) wppa_statistics();	

	// Display breadcrumb navigation only if it is set in the settings page
	wppa_breadcrumb( 'optional' );	

	
	if ( wppa_page( 'albums' ) ) {													// Page 'Albums' requested
	
		// Get the albums and the thumbs and the number of pages for each set
		$albums = wppa_get_albums();												// Get the albums
		$n_album_pages = wppa_get_npages( 'albums', $albums );						// Get the number of album pages
		
		if ( $wppa_opt['wppa_thumbtype'] != 'none' ) {
			$thumbs = wppa_get_thumbs();											// Get the Thumbs
		} else $thumbs = false;
		$n_thumb_pages = wppa_get_npages( 'thumbs', $thumbs );						// Get the number of thumb pages
		if ( $n_thumb_pages == '0' ) $thumbs = false;								// No pages: no thumbs. Maybe want covers only

		// Get total number of pages
		if ( ! wppa_is_pagination() ) $totpag = '1';								// If both pagination is off, there is only one page
		else $totpag = $n_album_pages + $n_thumb_pages;	

		// Make pagelinkbar if requested on top
		if ( $wppa_opt['wppa_pagelink_pos'] == 'top' || $wppa_opt['wppa_pagelink_pos'] == 'both' ) {
			wppa_page_links( $totpag, $curpage );
		}
		
		// Process the albums
		if ( $albums ) {	
			$counter_albums = '0';
			wppa_album_list( 'open' );												// Open Albums sub-container
				foreach ( $albums as $album ) { 									// Loop the albums
					$counter_albums++;
					if ( wppa_onpage( 'albums', $counter_albums, $curpage ) ) {
						wppa_album_cover( $album['id'] );							// Show the cover
						$didsome = true;
					} // End if on page
				}
			wppa_album_list( 'close' );												// Close Albums sub-container
		}	// If albums
 
		if ( $didsome && wppa_is_pagination() ) $thumbs = false;					// Pag on and didsome: force a pagebreak by faking no thumbs
		if ( count( $thumbs ) <= wppa_get_mincount() ) $thumbs = false;				// Less than treshold value
		
		// Process the thumbs
		if ( $thumbs ) {	
			$counter_thumbs = '0';
			if ( wppa_opt('wppa_thumbtype') == 'ascovers' || 
				 wppa_opt('wppa_thumbtype') == 'ascovers-mcr' ) {					// Do the thumbs As covers
				wppa_thumb_list( 'open' );											// Open Thumblist sub-container
				foreach ( $thumbs as $tt ) :  global $thumb; $thumb = $tt; 			// Loop the Thumbs
					$counter_thumbs++;
					if ( wppa_onpage( 'thumbs', $counter_thumbs, $curpage - $n_album_pages ) ) {
						$didsome = true;
						wppa_thumb_ascover( $thumb['id'] );							// Show Thumb as cover
					} // End if on page
				endforeach; 
				wppa_thumb_list( 'close' );											// Close Thumblist sub-container
			}	// As covers
			else {																	// Do the thumbs As default
				wppa_thumb_area( 'open' );											// Open Thumbarea sub-container
				wppa_popup();														// Prepare Popup box
				wppa_album_name( 'top' );											// Optionally display album name
				wppa_album_desc( 'top' );											// Optionally display album description
				foreach ( $thumbs as $tt ) :  global $thumb; $thumb = $tt; 			// Loop the Thumbs
					$counter_thumbs++;
					if ( wppa_onpage( 'thumbs', $counter_thumbs, $curpage - $n_album_pages ) ) {
						$didsome = true;
						wppa_thumb_default( $thumb['id'] );							// Show Thumb as default
					}	// End if on page
				endforeach; 
				wppa_album_name( 'bottom' );										// Optionally display album name
				wppa_album_desc( 'bottom' );										// Optionally display album description
				wppa_thumb_area( 'close' );											// Close Thumbarea sub-container
			}	// As default
		}	// If thumbs
	
		// Make pagelinkbar if requested on bottom
		if ( $wppa_opt['wppa_pagelink_pos'] == 'bottom' || $wppa_opt['wppa_pagelink_pos'] == 'both' ) {
			wppa_page_links( $totpag, $curpage );
		}
		
		// Empty results?
		if ( ! $didsome ) {
			if ( $wppa['photos_only'] ) {
				$wppa['out'] .= wppa_errorbox( __a( 'No photos found matching your search criteria.', 'wppa_theme' ) );
			}
			elseif ( $wppa['albums_only'] ) {
				$wppa['out'] .= wppa_errorbox( __a( 'No albums found matching your search criteria.', 'wppa_theme' ) );
			}
			else {
				$wppa['out'] .= wppa_errorbox( __a( 'No albums or photos found matching your search criteria.', 'wppa_theme' ) );
			}
		}
	} // wppa_page( 'albums' )
	
	elseif ( wppa_page( 'slide' ) || wppa_page( 'single' ) ) {						// Page 'Slideshow' or 'Single' in browsemode requested
		$thumbs = wppa_get_thumbs();
		wppa_dbg_msg( 'From theme: #thumbs='.( $thumbs ? count( $thumbs ) : '0' ) );
		if ( $thumbs ) {
			wppa_the_slideshow();													// Producs all the html required for the slideshow
			wppa_run_slidecontainer( 'slideshow' );									// Fill in the photo array and display it.
		}
		else {
			$wppa['out'] .= wppa_errorbox( __a( 'No photos found matching your search criteria.', 'wppa_theme' ) );
		}
	} // wppa_page( 'slide' )
	
	// Close container
	wppa_container( 'close' );
}
