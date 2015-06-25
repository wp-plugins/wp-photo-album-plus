<?php 
/* wppa_theme.php
* Package: wp-photo-album-plus
*
* display the albums/photos/slideshow in a page or post
* Version 6.1.15
*/
function wppa_theme() {

global $wppa_version; $wppa_version = '6-1-15-000';		// The version number of this file
global $wppa;
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
		
		if ( wppa_opt( 'thumbtype' ) != 'none' ) {
			$thumbs = wppa_get_thumbs();											// Get the Thumbs
		} else $thumbs = false;
		
		$wanted_empty = wppa_is_wanted_empty( $thumbs );							// See if we need to display an empty thumbnail area

		$n_thumb_pages = wppa_get_npages( 'thumbs', $thumbs );						// Get the number of thumb pages
		if ( $n_thumb_pages == '0' && ! $wanted_empty ) $thumbs = false;			// No pages: no thumbs. Maybe want covers only
		if ( $wanted_empty ) $n_thumb_pages = '1';

		// Get total number of pages
		if ( ! wppa_is_pagination() ) $totpag = '1';								// If both pagination is off, there is only one page
		else $totpag = $n_album_pages + $n_thumb_pages;	

		// Make pagelinkbar if requested on top
		if ( wppa_opt( 'pagelink_pos' ) == 'top' || wppa_opt( 'pagelink_pos' ) == 'both' ) {
			wppa_page_links( $totpag, $curpage );
		}
		
		// Process the albums
		if ( ! wppa_switch( 'wppa_thumbs_first' ) ) {
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
		}
		
		if ( $didsome && wppa_is_pagination() ) $thumbs = false;						// Pag on and didsome: force a pagebreak by faking no thumbs
		if ( count( $thumbs ) <= wppa_get_mincount() && ! $wanted_empty ) $thumbs = false;			// Less than treshold value
		if ( wppa_switch( 'wppa_thumbs_first' ) && $curpage > $n_thumb_pages ) $thumbs = false; 	// If thumbs done, do not display an empty thumbarea
		
		// Process the thumbs
		if ( $thumbs || $wanted_empty ) 
		if ( ! $wanted_empty || ! wppa_switch( 'thumbs_first' ) || wppa_get_curpage() == '1' ) 
		if ( ! $wanted_empty || wppa_switch( 'thumbs_first' ) || wppa_get_curpage() == $totpag ) {
			
			// Init
			$counter_thumbs = '0';
			
			// As covers
			if ( wppa_opt('wppa_thumbtype') == 'ascovers' || 
				 wppa_opt('wppa_thumbtype') == 'ascovers-mcr' ) {					// Do the thumbs As covers
				wppa_thumb_list( 'open' );											// Open Thumblist sub-container
				$relpage = wppa_switch( 'wppa_thumbs_first' ) ? $curpage : $curpage - $n_album_pages;
				foreach ( $thumbs as $tt ) :  global $thumb; $thumb = $tt; 			// Loop the Thumbs
					$counter_thumbs++;
					if ( wppa_onpage( 'thumbs', $counter_thumbs, $relpage ) ) {
						$didsome = true;
						wppa_thumb_ascover( $thumb['id'] );							// Show Thumb as cover
					} // End if on page
				endforeach; 
				wppa_thumb_list( 'close' );											// Close Thumblist sub-container
			}	// As covers

			// Masonry vertical
			elseif ( wppa_opt('wppa_thumbtype') == 'masonry-v' ) {					// Masonry
			
				// The header
				wppa_thumb_area( 'open' );											// Open Thumbarea sub-container
				wppa_popup();														// Prepare Popup box
				wppa_album_name( 'top' );											// Optionally display album name
				wppa_album_desc( 'top' );											// Optionally display album description
				
				// Init
				$relpage 	= wppa_switch( 'wppa_thumbs_first' ) ? $curpage : $curpage - $n_album_pages;
				$cont_width = wppa_get_container_width();
				$count_cols = ceil( $cont_width / ( wppa_opt( 'wppa_thumbsize' ) + wppa_opt( 'wppa_tn_margin' ) ) );
				$correction = wppa_opt( 'wppa_tn_margin' ) * ( $cont_width / $count_cols ) / 100;
				
				// Init the table
				wppa_out( '<table class="wppa-masonry" style="margin-top:3px;" ><tbody class="wppa-masonry" ><tr class="wppa-masonry" >' );
				
				// Init the columns
				$col_headers 	= array();
				$col_contents 	= array();
				$col_heights 	= array();
				$col_widths 	= array();
				
				for ( $col = 0; $col < $count_cols; $col++ ) {
					$col_headers[$col] 	= '';
					$col_contents[$col] = '';
					$col_heights[$col] 	= 0;
					$col_widths[$col] 	= 100;
				}

				// Process the thumbnails
				$col = '0';
				if ( $thumbs ) foreach ( $thumbs as $tt ) { 	
					$id = $tt['id'];
					$counter_thumbs++;
					if ( wppa_onpage( 'thumbs', $counter_thumbs, $relpage ) ) {
						$col_contents[$col] .= wppa_get_thumb_masonry( $id );
						$col_heights[$col] 	+= ( $correction + wppa_get_thumby( $id ) ) / ( $correction + wppa_get_thumbx( $id ) ) * $col_widths[$col];
						$col += '1';
						if ( $col == $count_cols ) {
							$col = '0';
						}
						$didsome = true;
					}
				}
				
				// Find longest column
				$long = 0;
				for ( $col = 0; $col < $count_cols; $col++ ) {
					if ( $col_heights[$col] > $long ) $long = $col_heights[$col];
				}
				
				// Adjust column widths to resize lengths to equal lengths
				for ( $col = 0; $col < $count_cols; $col++ ) {
					if ( $col_heights[$col] ) {
						$col_widths[$col] = $long / $col_heights[$col] * $col_widths[$col];
					}
				}
				
				// Adjust column widths to total 100
				$wide = 0;
				for ( $col = 0; $col < $count_cols; $col++ ) {
					$wide += $col_widths[$col];
				}
				for ( $col = 0; $col < $count_cols; $col++ ) {
					$col_widths[$col] = $col_widths[$col] * 100 / $wide;
				}				
				
				// Make column headers
				for ( $col = 0; $col < $count_cols; $col++ ) {
					$col_headers[$col] = '<td style="width: '.$col_widths[$col].'%; vertical-align:top;" class="wppa-masonry" >';
				}
				
				// Add the columns to the output stream
				for ( $col = 0; $col < $count_cols; $col++ ) {
					wppa_out( $col_headers[$col] );
					wppa_out( $col_contents[$col] );
					wppa_out( '</td>' );
				}
				
				// Close the table
				wppa_out( '</tr></tbody></table>' );

				// The footer
				wppa_album_name( 'bottom' );										// Optionally display album name
				wppa_album_desc( 'bottom' );										// Optionally display album description
				wppa_thumb_area( 'close' );											// Close Thumbarea sub-container
			}	// Masonry-v
			
			// Masonry horizontal
			elseif ( wppa_opt('wppa_thumbtype') == 'masonry-h' ) {					// Masonry
			
				// The header
				wppa_thumb_area( 'open' );											// Open Thumbarea sub-container
				wppa_popup();														// Prepare Popup box
				wppa_album_name( 'top' );											// Optionally display album name
				wppa_album_desc( 'top' );											// Optionally display album description
				
				// Init
				$relpage 	= wppa_switch( 'wppa_thumbs_first' ) ? $curpage : $curpage - $n_album_pages;
				$cont_width = wppa_get_container_width( 'netto' );
				$correction = wppa_opt( 'wppa_tn_margin' );

				// Init the table
				wppa_out( '<table class="wppa-masonry" style="margin-top:3px;" ><tbody class="wppa-masonry" >' );
				
				// Process the thumbnails
				$row_content 		= '';
				$row_width 			= 0;
				$target_row_height 	= wppa_opt( 'wppa_thumbsize' ) * 0.75 + $correction;
				$rw_count 			= 0;
				$tr_count 			= '1';
				$done_count 		= 0;
				$last 				= false;
				$max_row_height 	= $target_row_height * 0.8; 	// Init keep track for last
				if ( $thumbs ) foreach ( $thumbs as $tt ) { 
					$id = $tt['id'];
					$counter_thumbs++;
					if ( wppa_onpage( 'thumbs', $counter_thumbs, $relpage ) ) {
						$row_content 	.= wppa_get_thumb_masonry( $tt['id'] );
						$rw_count 		+= 1;
						$row_width 		+= wppa_get_thumbratioxy( $id ) * ( $target_row_height - $correction );
						$didsome 		= true;
					}
					$done_count 	+= 1;
					$last 			= $done_count == count( $thumbs );
					if ( $row_width > $cont_width || $last ) {
						$tot_marg 		= $rw_count * $correction;
						$row_height 	= $row_width ? ( ( $target_row_height - $correction ) * ( $cont_width - '3' - $tot_marg ) / ( $row_width ) + $correction )  : '0';
						if ( ! $last ) {
							$max_row_height = max( $max_row_height, $row_height );
						}
						if ( $last && $row_height > wppa_get_thumby( $id ) ) {
							$row_height = $max_row_height;
						}
						$row_height_p 	= $row_height / $cont_width * 100;
						wppa_out( 	'<tr class="wppa-masonry" >' .
										'<td style="border:none;padding:0;margin:0" >' .
											'<div' .
												' id="wppa-mas-h-'.$tr_count.'-'.wppa( 'mocc' ).'"' .
												' style="height:'.$row_height.'px;"' .
												' class="wppa-masonry"' .
												' data-height-perc="'.$row_height_p.'"' .
												' >');
						wppa_out( $row_content );
						wppa_out( '</div></td></tr>' );
						$row_content 	= '';
						$row_width 		= 0;
						$row_height 	= wppa_opt( 'wppa_thumbsize' );
						$rw_count 		= 0;
						$tr_count 		+= '1';
					}
				}
				wppa_out( '</tbody></table>' );

				// The footer
				wppa_album_name( 'bottom' );										// Optionally display album name
				wppa_album_desc( 'bottom' );										// Optionally display album description
				wppa_thumb_area( 'close' );											// Close Thumbarea sub-container
				
			}	// Masonry-h
			
			// Default
			elseif ( wppa_opt('wppa_thumbtype') == 'default' ) {					// Do the thumbs As default
			
				// The header
				wppa_thumb_area( 'open' );											// Open Thumbarea sub-container
				wppa_popup();														// Prepare Popup box
				wppa_album_name( 'top' );											// Optionally display album name
				wppa_album_desc( 'top' );											// Optionally display album description
				
				// Init
				$relpage = wppa_switch( 'wppa_thumbs_first' ) ? $curpage : $curpage - $n_album_pages;
				
				// Process the thumbnails
				if ( $thumbs ) foreach ( $thumbs as $tt ) {
					$counter_thumbs++;
					if ( wppa_onpage( 'thumbs', $counter_thumbs, $relpage ) ) {
						$didsome = true;
						wppa_thumb_default( $tt['id'] );							// Show Thumb as default
					}	// End if on page
				}
				
				// The footer
				wppa_album_name( 'bottom' );										// Optionally display album name
				wppa_album_desc( 'bottom' );										// Optionally display album description
				wppa_thumb_area( 'close' );											// Close Thumbarea sub-container
			}	// As default
			
			// Unimplemented thumbnail type
			else {
				wppa_out( 'Unimplemented thumbnail type' );
			}
		}	// If thumbs

		if ( $didsome && wppa_is_pagination() ) $albums = false;					// Pag on and didsome: force a pagebreak by faking no albums
		if ( ! wppa_is_pagination() ) $n_thumb_pages = '0';							// Still on page one

		// Process the albums
		if ( wppa_switch( 'wppa_thumbs_first' ) ) {
			if ( $albums ) {	
				$counter_albums = '0';
				wppa_album_list( 'open' );												// Open Albums sub-container
					foreach ( $albums as $album ) { 									// Loop the albums
						$counter_albums++;
						if ( wppa_onpage( 'albums', $counter_albums, $curpage - $n_thumb_pages ) ) {
							wppa_album_cover( $album['id'] );							// Show the cover
							$didsome = true;
						} // End if on page
					}
				wppa_album_list( 'close' );												// Close Albums sub-container
			}	// If albums
		}
		
		// Make pagelinkbar if requested on bottom
		if ( wppa_opt( 'pagelink_pos' ) == 'bottom' || wppa_opt( 'pagelink_pos' ) == 'both' ) {
			wppa_page_links( $totpag, $curpage );
		}
		
		// Empty results?
		if ( ! $didsome && ! $wanted_empty ) {
			if ( wppa( 'photos_only' ) ) {
				wppa_out( wppa_errorbox( __a( 'No photos found matching your search criteria.', 'wppa_theme' ) ) );
			}
			elseif ( wppa( 'albums_only' ) ) {
				wppa_out( wppa_errorbox( __a( 'No albums found matching your search criteria.', 'wppa_theme' ) ) );
			}
			else {
				wppa_out( wppa_errorbox( __a( 'No albums or photos found matching your search criteria.', 'wppa_theme' ) ) );
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
			wppa_out( wppa_errorbox( __a( 'No photos found matching your search criteria.', 'wppa_theme' ) ) );
		}
	} // wppa_page( 'slide' )
	
	// Close container
	wppa_container( 'close' );
}

function wppa_is_wanted_empty( $thumbs ) {

	if ( ! wppa_switch( 'show_empty_thumblist' ) ) return false;							// Feature not enabled
	if ( is_array( $thumbs ) && count( $thumbs ) > wppa_get_mincount() ) return false;		// Album is not empty
	if ( wppa_is_virtual() ) return false; 													// wanted empty only on real albums
	if ( wppa( 'albums_only' ) ) return false;												// Explicitly no thumbs
	
//	if ( wppa_switch( 'thumbs_first' ) && wppa_get_curpage() != '1' ) return false;			// Only on page 1 if thumbs first
	
	wppa( 'current_album', wppa( 'start_album' ) );											// Make sure upload knows the album
	
	return true;
}
