var begin_form = '';
var form_fields = new Array();
var serialized_form_fields = new Array();
$j(document).ready(function(){
	/* events to highlight changes in the current filter */
	var i = 0;
	$j('[name=bug_filters]').find('input').each(function() {
		var formname = $j(this).parent('form').attr('name');
		if( formname != 'list_queries_open' && formname != 'open_queries' && formname != 'save_query' ) {
			// serialize the field and add it to an array

			if( $j.inArray($j(this).attr('name'),form_fields) == -1 ) {
				form_fields[i] = $j(this).attr('name');
				i++;
			}
		}
	});
	$j.each( form_fields, function (index, value) {
		serialized_form_fields[value] = $j('[name=bug_filter]').find('[name='+value+']').serialize();
	});

	/* Set up events to modify the form css to show when a stored query has been modified */
	begin_form = $j('[name=bug_filter]').serialize();

	$j('[:input').live("change", function() {
		filter_highlight_changes($j(this));
	});
	$j(':checkbox').live("click", function() {
		filter_highlight_changes($j(this));
	});

	/* submit filter when changing to a stored query */
	$j('#source_query_id').change( function() {
		$j('#stored_queries').submit();
	});

	/* Events for populating filter fields */
	$j('.filter-link').live( 'click', function( event ) {
		event.preventDefault();
		var page = 'return_dynamic_filters.php';
		var link_url = $j(this).attr('href');
		var toLoad = link_url.replace('view_all_bug_page.php', page );
		var link_text = $j(this).attr('text');
		$j(this).parent().text( link_text );
		var loadingString = $j('#loading_string').text();
		// get the target
		var target = '#' + $j(this).attr('id') + '_target';
		$j(target).text(loadingString);
		// load the target with the field data
		$j(target).load(toLoad);
	});
});

function filter_toggle_field_changed(field) {
	var field_type = field.attr('type');
	var starting_value = serialized_form_fields[field.attr('name')];
	var current_value = field.serialize();

	// unchecked boxes start as undefined but if checked and then unchecked it
	// is no longer undefined so the comparison breaks.  Reset it to undefined.
	if( field_type=='checkbox' && current_value == '') {
		current_value = undefined;
	}
	if( current_value != starting_value ) {
		// field is changed
		filter_field_dirty(field);
	} else {
		// field is not changed
		filter_field_clean(field);
	}
}

function filter_highlight_changes(item) {
	filter_toggle_field_changed( item );

	/* Check if form is different that started with */
	var changed_form = $j('[name=bug_filter]').serialize();
	if( begin_form == changed_form ) {
		filter_clean_all();
	}
}

function filter_named_filter_clean() {
	/* be sure it's clean whether it's stored filter or not */
	var selected_text = $j('[name=source_query_id] option:selected').html();
	if( selected_text.charAt(0) == '*' ) {
		$j('[name=source_query_id]').removeClass('tainted');
		var reset_text = selected_text.substring(2,selected_text.length);
		$j('[name=source_query_id] option:selected').html(reset_text);
	}
}

function filter_named_filter_dirty() {
	var stored_query_id = $j('[name=source_query_id]').val();
	if( stored_query_id == -1 ) {
		/* Only make it dirty if it's a stored filter */
		return;
	}
	/* stored query in filter is tainted */
	var selected_text = $j('[name=source_query_id] option:selected').html();
	if( selected_text.charAt(0) != '*' ) {
		$j('[name=source_query_id] option:selected').prepend('* ');
		$j('[name=source_query_id]').addClass('tainted');
	}
}

function filter_field_clean( item ) {
	item.parent().removeClass('tainted');
}
function filter_field_dirty( item ) {
	if( !item.parent().hasClass('tainted') ) {
		filter_named_filter_dirty();
		item.parent().addClass('tainted');
	}
}

function filter_clean_all() {
	filter_named_filter_clean();
	$j('.tainted').each(function() {
		$j(this).removeClass('tainted');
	});
}
