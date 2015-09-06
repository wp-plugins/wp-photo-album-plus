// wppa-utils.js
//
// conatins common vars and functions
// 
var wppaJsUtilsVersion = '6.2.10';
var wppaDebug;

// Trim 
// @1 string to be trimmed
// @2 character, string, or array of characters or strings to trim off, 
//    default: trim spaces, tabs and newlines
function wppaTrim( str, arg ) {

	var result;
	
	result = wppaTrimLeft( str, arg );
	result = wppaTrimRight( result, arg );
	
	return result;
}

// Trim left
// @1 string to be trimmed
// @2 character, string, or array of characters or strings to trim off, 
//    default: trim spaces, tabs and newlines
function wppaTrimLeft( str, arg ) {

	var result;
	var strlen;
	var arglen;
	var argcount;
	var i;
	var done;
	var oldStr, newStr;
	
	switch ( typeof ( arg ) ) {
		case 'string':
			result = str;
			strlen = str.length;
			arglen = arg.length;
			while ( strlen >= arglen && result.substr( 0, arglen ) == arg ) {
				result = result.substr( arglen );
				strlen = result.length;
			}
			break;
		case 'object':
			done = false;
			newStr = str;
			while ( ! done ) {
				i = 0;
				oldStr = newStr;
				while ( i < arg.length ) {
					newStr = wppaTrimLeft( newStr, arg[i] );
					i++;
				}
				done = ( oldStr == newStr );
			}
			result = newStr;
			break;
		default:
			return str.replace( /^\s\s*/, '' );
	}

	return result;
}

// Trim right
// @1 string to be trimmed
// @2 character, string, or array of characters or strings to trim off, 
//    default: trim spaces, tabs and newlines
function wppaTrimRight( str, arg ) {

	var result;
	var strlen;
	var arglen;
	var argcount;
	var i;
	var done;
	var oldStr, newStr;
	
	switch ( typeof ( arg ) ) {
		case 'string':
			result = str;
			strlen = str.length;
			arglen = arg.length;
			while ( strlen >= arglen && result.substr( strlen - arglen ) == arg ) {
				result = result.substr( 0, strlen - arglen );
				strlen = result.length;
			}
			break;
		case 'object':
			done = false;
			newStr = str;
			while ( ! done ) {
				i = 0;
				oldStr = newStr;
				while ( i < arg.length ) {
					newStr = wppaTrimRight( newStr, arg[i] );
					i++;
				}
				done = ( oldStr == newStr );
			}
			result = newStr;
			break;
		default:
			return str.replace( /\s\s*$/, '' );
	}
	
	return result;
}

// Console logging
function wppaConsoleLog( arg, force ) {

	if ( typeof( console ) != 'undefined' && ( wppaDebug || force == 'force' ) ) {
		console.log( arg );
	}
}

// Say we're in
wppaConsoleLog( 'wppa-utils.js version '+wppaJsUtilsVersion+' loaded.', 'force' );