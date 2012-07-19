/* wppa-tinymce.js
* Pachkage: wp-photo-album-plus
*
*
* Version 4.6.11
*
*/


// closure to avoid namespace collision
(function(){
	// creates the plugin
	tinymce.create('tinymce.plugins.mygallery', {
		// creates control instances based on the control's id.
		// our button's id is "mygallery_button"
		createControl : function(id, controlManager) {
			if (id == 'mygallery_button') {
				// creates the button
				var button = controlManager.createButton('mygallery_button', {
					title : 'WPPA+ Gallery Shortcode', 				// title of the button
					image : wppaImageDirectory+'albumnew32.png',  	// path to the button's image
					onclick : function() {
						// triggers the thickbox
						var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
						W = W - 80;
						H = H - 84;
						H = 500;	// does not work, height is changed auto, can't find where
						tb_show( 'WPPA+ Gallery Shortcode', '#TB_inline?width=' + W + '&height=' + H + '&inlineId=mygallery-form' );
					}
				});
				return button;
			}
			return null;
		}
	});
	
	// registers the plugin. DON'T MISS THIS STEP!!!
	tinymce.PluginManager.add('mygallery', tinymce.plugins.mygallery);
	
	// executes this when the DOM is ready
	jQuery(function(){
		// creates a form to be displayed everytime the button is clicked
		// you should achieve this using AJAX instead of direct html code like this
		var xmlhttp = wppaGetXmlHttp();				// located in wppa-admin-scripts.js
		// wppa-ajax.php calls wppa_make_tinymce_dialog(); which is located in wppa-tinymce.php

		var url = wppaAjaxUrl+'?action=wppa&wppa-action=tinymcedialog';			
		xmlhttp.open("GET",url,true);
		xmlhttp.send();
		xmlhttp.onreadystatechange=function() {
			if  (xmlhttp.readyState == 4 && xmlhttp.status!=404 ) {
				var formtext = xmlhttp.responseText;

				var form = jQuery(formtext);
		
				var table = form.find('table');
				form.appendTo('body').hide();
		
				// handles the click event of the submit button
				form.find('#mygallery-submit').click(function(){
					
					var type 	= table.find('#mygallery-type').val();
					var temp	= table.find('#mygallery-album').val().split('|');
					var album 	= temp[0];
					var size 	= table.find('#mygallery-size').val();
					var align	= table.find('#mygallery-align').val();
					var photo 	= parseInt(table.find('#mygallery-photo').val());
					
					// Sinitize input
					if (size == 0) {}								// Ok, use default
					else if (size == parseInt(size) && size > 0) {}	// Ok, positive number
					else if (size == 'auto') {}						// Ok, auto
					else {
						alert('Sorry, you made a mistake\n\nSize must be a positive number or auto\n\nPlease try again');
						return;
					}

					// Check for inconsistencies
					if ( type == 'cover' ) {
						if ( album == '#topten' || album == '#lasten' || album == '#all' ) {
							alert('Sorry, you made a mistake\n\nA --- special --- selection has no album cover\n\nPlease try again');
							return;
						}
					}
					if ( type != 'photo' && type != 'mphoto' )	{
						if ( album == 0 ) {
							alert('Sorry, you made a mistake\n\nPlease select an album\n\nPlease try again');
							return;
						}
					}
					if ( type == 'photo' || type == 'mphoto' )	{
						if ( photo == 0 ) {
							alert('Sorry, you made a mistake\n\nPlease select a photo\n\nPlease try again');
							return;
						}
					}
					
					// Make the shortcode
					var shortcode = '%%wppa%%';
					if ( type == 'photo' || type == 'mphoto' )				
						shortcode += ' %%'+type+'='+photo+'%%';
					else
						shortcode += ' %%'+type+'='+album+'%%';
						
					if ( size != 0 )
						shortcode += ' %%size='+size+'%%';
					
					if ( align != 'none' )
						shortcode += ' %%align='+align+'%%';			
					
					// shortcode += ' ';
					
					// inserts the shortcode into the active editor
					tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
					
					// closes Thickbox
					tb_remove();
				});
			}
		}
	});
})()

function wppaTinyMcePhotoPreview(id) {
	jQuery('#mygallery-photo-preview').html('<img src="'+wppaThumbDirectory+id+'" style="max-width:600px; max-height:150px; margin-top:3px;" />');
}

function wppaTinyMceAlbumPreview(id) {
	var html = '';
	var temp = id.split('|');
	var count = temp.length - 1;
	
	if (count > 0) for (var i = 1; i <= count; i++) {
		if ( temp[i] != '' ) html += '<img src="'+wppaThumbDirectory+temp[i]+'" style="max-width:75px; max-height:75px; margin:2px;" />';
	}
	else {
		html = '<br /><br /><br />'+wppaNoPreview;
	}
	jQuery('#mygallery-album-preview').html(html);
}
