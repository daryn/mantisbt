/*
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2010  MantisBT Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.
 *
 * --------------------------------------------------------
 * $Id$
 * --------------------------------------------------------
 */

/*
 * Collapsible element functions
 */
var g_collapse_clear = 1;

// global code to determine how to set visibility
var a = navigator.userAgent.indexOf("MSIE");
var style_display;

if (a!= -1) {
	style_display = 'block';
} else {
	style_display = 'table-row';
}
style_display = 'block';

$(document).ready( function() {
	/* Add datepicker for fields with .datepicker class */
	$(function(){
		$('.datepicker').live('click', function() {
			$(this).datepicker({showOn:'focus'}).focus();
		});
	});

	/* Global Tag change event added only if #tag_select exists */
	$('#tag_select').live('change', function() {
		var selected_tag = $('#tag_select option:selected').text();
		tag_string_append( selected_tag );
	});

	$('.collapse-open').show();
	$('.collapse-closed').hide();
	$('.collapse-link').click( function(event) {
		event.preventDefault();
		var id = $(this).attr('id');
		var t_pos = id.indexOf('_closed_link' );
		if( t_pos == -1 ) {
			t_pos = id.indexOf('_open_link' );
		}
		var t_div = id.substring(0, t_pos );
		ToggleDiv( t_div );
		if( t_div == 'filter' ) {
			setFilterCollapse();
		}
	});

	$('input[type=text].autocomplete').autocomplete({
		source: function(request, callback) {
			var fieldName = $(this).attr('element').attr('id');
			var postData = {};
			postData['entrypoint']= fieldName + '_get_with_prefix';
			postData[fieldName] = request.term;
			$.getJSON('xmlhttprequest.php', postData, function(data) {
				var results = [];
				$.each(data, function(i, value) {
					var item = {};
					item.label = $('<div/>').text(value).html();
					item.value = value;
					results.push(item);
				});
				callback(results);
			});
		}
	});

	$('input.autofocus:first, select.autofocus:first, textarea.autofocus:first').focus();

	var stopwatch = {
		timerID: null,
		elapsedTime: 0,
		tick: function() {
			this.elapsedTime += 1000;
			var seconds = Math.floor(this.elapsedTime / 1000) % 60;
			var minutes = Math.floor(this.elapsedTime / 60000) % 60;
			var hours = Math.floor(this.elapsedTime / 3600000) % 60;
			if (seconds < 10) {
				seconds = '0' + seconds;
			}
			if (minutes < 10) {
				minutes = '0' + minutes;
			}
			if (hours < 10) {
				hours = '0' + hours;
			}
			$('input[type=text].stopwatch_time').attr('value', hours + ':' + minutes + ':' + seconds);
			this.start();
		},
		reset: function() {
			this.stop();
			this.elapsedTime = 0;
			$('input[type=text].stopwatch_time').attr('value', '00:00:00');
		},
		start: function() {
			this.stop();
			var self = this;
			this.timerID = window.setTimeout(function() {
				self.tick();
			}, 1000);
		},
		stop: function() {
			if (typeof this.timerID == 'number') {
				window.clearTimeout(this.timerID);
				delete this.timerID;
			}
		}
	}
	$('input[type=button].stopwatch_toggle').click(function() {
		if (stopwatch.elapsedTime == 0) {
			stopwatch.stop();
			stopwatch.start();
			$('input[type=button].stopwatch_toggle').attr('value', translations['time_tracking_stopwatch_stop']);
		} else if (typeof stopwatch.timerID == 'number') {
			stopwatch.stop();
			$('input[type=button].stopwatch_toggle').attr('value', translations['time_tracking_stopwatch_start']);
		} else {
			stopwatch.start();
			$('input[type=button].stopwatch_toggle').attr('value', translations['time_tracking_stopwatch_stop']);
		}
	});
	$('input[type=button].stopwatch_reset').click(function() {
		stopwatch.reset();
		$('input[type=button].stopwatch_toggle').attr('value', translations['time_tracking_stopwatch_start']);
	});
	setFilterCollapse();
});

function setFilterCollapse() {
	var t_filter_display = $j('#filter_open').css('display');
	if( t_filter_display == 'none' ) {
		t_src = 'images/plus.png';
		t_alt = '+';
	} else {
		t_src = 'images/minus.png';
		t_alt = '-';
	}
	$j('#filter_closed_link').children('img').attr('src', t_src );
	$j('#filter_closed_link').children('img').attr('alt', t_alt );
}

/*
 * String manipulation
 */
function Trim( p_string ) {
	if (typeof p_string != "string") {
		return p_string;
	}

	var t_string = p_string;
	var t_ch = '';

	// Trim beginning spaces

	t_ch = t_string.substring( 0, 1 );
	while ( t_ch == " " ) {
		t_string = t_string.substring( 1, t_string.length );
		t_ch = t_string.substring( 0, 1 );
	}

	// Trim trailing spaces

	t_ch = t_string.substring( t_string.length-1, t_string.length );
	while ( t_ch == " " ) {
		t_string = t_string.substring( 0, t_string.length-1 );
		t_ch = t_string.substring( t_string.length-1, t_string.length );
	}

	return t_string;
}

/*
 * Cookie functions
 */
function GetCookie( p_cookie ) {
	var t_cookie_name = "MANTIS_" + p_cookie;
	var t_cookies = document.cookie;

	t_cookies = t_cookies.split( ";" );

	var i = 0;
	while( i < t_cookies.length ) {
		var t_cookie = t_cookies[ i ];

		t_cookie = t_cookie.split( "=" );

		if ( Trim( t_cookie[ 0 ] ) == t_cookie_name ) {
			return( t_cookie[ 1 ] );
		}
		i++;
	}

	return -1;
}

function SetCookie( p_cookie, p_value ) {
	var t_cookie_name = "MANTIS_" + p_cookie;
	var t_expires = new Date();

	t_expires.setTime( t_expires.getTime() + (365 * 24 * 60 * 60 * 1000));

	document.cookie = t_cookie_name + "=" + p_value + "; expires=" + t_expires.toUTCString() + ";";
}

function ToggleDiv( p_div ) {
	t_open_div = '#' + p_div + "_open";
	t_closed_div = '#' + p_div + "_closed";

	t_cookie = GetCookie( "collapse_settings" );
	if ( 1 == g_collapse_clear ) {
		t_cookie = "";
		g_collapse_clear = 0;
	}
	var t_open_display = $(t_open_div).css('display');
	$(t_open_div).toggle();

	if( $(t_closed_div).length ) {
		$(t_closed_div).toggle();
	}

	if ( t_open_display == "none" ) {
		t_cookie = t_cookie + "|" + p_div + ",1";
	} else {
		t_cookie = t_cookie + "|" + p_div + ",0";
	}

	SetCookie( "collapse_settings", t_cookie );
}

/* Check checkboxes */
function checkall( p_formname, p_state) {
	var t_elements = (eval("document." + p_formname + ".elements"));

	for (var i = 0; i < t_elements.length; i++) {
		if(t_elements[i].type == 'checkbox') {
			t_elements[i].checked = p_state;
		}
	}
}

function setDisplay(idTag, state)
{
	if(!document.getElementById(idTag)) alert('SetDisplay(): id '+idTag+' is empty');
	// change display visibility
	if ( state != 0 ) {
		document.getElementById(idTag).style.display = style_display;
	} else {
		document.getElementById(idTag).style.display = 'none';
	}
}

function toggleDisplay(idTag)
{
	setDisplay( idTag, (document.getElementById(idTag).style.display == 'none')?1:0 );
}

/* Append a tag name to the tag input box, with repect for tag separators, etc */
function tag_string_append( p_string ) {
	t_tag_separator = $('#tag_separator').val();
	t_tag_string = $('#tag_string');
	t_tag_select = $('#tag_select');

	if ( Trim( p_string ) == '' ) { return; }

	if ( t_tag_string.val() != '' ) {
		t_tag_string.val( t_tag_string.val() + t_tag_separator + p_string );
	} else {
		t_tag_string.val( t_tag_string.val() + p_string );
	}
	t_tag_select.val(0);
}
