<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 *  Prints the filter selection area for both the bug list view screen and
 *  the bug list print screen.
 */
static $s_use_javascript = null;
static $s_dhtml_filters = null;
$f_load_filters = gpc_get_bool( 'load_filters' );
if( is_null( $s_use_javascript ) ) {
	if( ON == config_get( 'use_javascript' )  ) {
		$s_use_javascript = true;
	} else {
		$s_use_javascript = false;
	}
}
if( is_null( $s_dhtml_filters ) ) {
	if( !$f_load_filters && ON == config_get( 'dhtml_filters' )  ) {
		$s_dhtml_filters = true;
	} else {
		$s_dhtml_filters = false;
	}
}

$t_loading_string = lang_get('loading');
$t_page_number = (int) $p_page_number;

$t_tdclass = 'small-caption';
$t_trclass = ' class="row-category2" ';
$t_action = 'view_all_set.php';

if( $t_for_screen == false ) {
	$t_tdclass = 'print';
	$t_trclass = '';
}

$t_current_user_access_level = current_user_get_access_level();
$t_accessible_custom_fields_ids = array();
$t_accessible_custom_fields_names = array();
$t_accessible_custom_fields_values = array();
$t_num_custom_rows = 0;
$t_per_row = 0;
$t_filter_by_custom_fields = ( ON == config_get( 'filter_by_custom_fields' ) ? true : false );

$t_show_product_version =  version_should_show_product_version( $t_project_id );
$t_show_build = $t_show_product_version && ( config_get( 'enable_product_build' ) == ON );
$t_view_filters = config_get( 'view_filters' );
$t_filters_url = 'return_dynamic_filters.php?for_screen=' . $t_for_screen;
$t_filters_url = $t_filters_url . '&amp;target_field=';

if( $s_dhtml_filters ) {
	$f_switch_view_link = 'view_all_set.php?type=6&view_type=';
} else {
	$f_switch_view_link = 'view_filters_page.php?view_type=';
}
if(( SIMPLE_ONLY != $t_view_filters ) && ( ADVANCED_ONLY != $t_view_filters ) ) {
	$t_view_type = $t_filter->getField( '_view_type' );
	if( 'advanced' == $t_view_type->filter_value ) {
		$f_switch_view_link .= 'simple';
		$t_switch_view_label =  lang_get( 'simple_filters' );
	} else {
		$f_switch_view_link .= 'advanced';
		$t_switch_view_label =  lang_get( 'advanced_filters' );
	}
}
$t_stored_queries_arr = array();
$t_stored_queries_arr = MantisBugFilter::getAvailable();
$t_reset_query_label = lang_get( 'reset_query' );
$t_use_query_label = lang_get( 'use_query' );
$t_open_queries_label = lang_get( 'open_queries' );

# overload handler_id setting if user isn't supposed to see them (ref #6189)
if( !access_has_project_level( config_get( 'view_handler_threshold' ), $t_project_id ) ) {
	$t_filter[FILTER_PROPERTY_HANDLER_ID] = array(
		META_FILTER_ANY,
	);
}
$t_enable_profiles = ( ON == config_get( 'enable_profiles' ) ? true : false );

if( access_has_project_level( config_get( 'create_permalink_threshold' ) ) ) {
	$t_permalink_url = 'permalink_page.php?url=' . urlencode( $t_filter->getUrl() );
	$t_permalink_label = lang_get( 'create_filter_link' );
}

/**
 *	 these are the current default filter fields
 *	@todo modify to load these dynamically, allow additional fields
 *		to be dynamically added/removed
 */
$t_search_field 			= $t_filter->getField( FILTER_PROPERTY_SEARCH );
$t_project_field 			= $t_filter->getField( FILTER_PROPERTY_PROJECT_ID );
$t_category_field 			= $t_filter->getField( FILTER_PROPERTY_CATEGORY_ID );
$t_reporter_field 			= $t_filter->getField( FILTER_PROPERTY_REPORTER_ID );
$t_monitored_by_field 		= $t_filter->getField( FILTER_PROPERTY_MONITOR_USER_ID );
$t_handler_field 			= $t_filter->getField( FILTER_PROPERTY_HANDLER_ID );
$t_severity_field 			= $t_filter->getField( FILTER_PROPERTY_SEVERITY );
$t_resolution_field 		= $t_filter->getField( FILTER_PROPERTY_RESOLUTION );
$t_profile_field 			= $t_filter->getField( FILTER_PROPERTY_PROFILE_ID );
$t_status_field 			= $t_filter->getField( FILTER_PROPERTY_STATUS );
$t_hide_status_field 		= $t_filter->getField( FILTER_PROPERTY_HIDE_STATUS );
$t_product_build_field 		= $t_filter->getField( FILTER_PROPERTY_BUILD );
$t_version_field 			= $t_filter->getField( FILTER_PROPERTY_VERSION );
$t_fixed_in_version_field 	= $t_filter->getField( FILTER_PROPERTY_FIXED_IN_VERSION );
$t_priority_field 			= $t_filter->getField( FILTER_PROPERTY_PRIORITY );
$t_target_version_field 	= $t_filter->getField( FILTER_PROPERTY_TARGET_VERSION );
$t_issues_per_page_field 	= $t_filter->getField( FILTER_PROPERTY_ISSUES_PER_PAGE );
$t_view_state_field 		= $t_filter->getField( FILTER_PROPERTY_VIEW_STATE );
$t_sticky_field 			= $t_filter->getField( FILTER_PROPERTY_STICKY );
$t_changed_field 			= $t_filter->getField( FILTER_PROPERTY_HIGHLIGHT_CHANGED );
$t_date_submitted_field 	= $t_filter->getField( FILTER_PROPERTY_DATE_SUBMITTED );
$t_due_date_field 			= $t_filter->getField( FILTER_PROPERTY_DUE_DATE );
$t_last_updated_field 		= $t_filter->getField( FILTER_PROPERTY_LAST_UPDATED );
$t_relationships_field 		= $t_filter->getField( FILTER_PROPERTY_RELATIONSHIP_TYPE );
$t_platform_field 			= $t_filter->getField( FILTER_PROPERTY_PLATFORM );
$t_os_field 				= $t_filter->getField( FILTER_PROPERTY_OS );
$t_os_build_field 			= $t_filter->getField( FILTER_PROPERTY_OS_BUILD );
$t_tags_field 				= $t_filter->getField( FILTER_PROPERTY_TAG_STRING );
$t_note_by_field 			= $t_filter->getField( FILTER_PROPERTY_NOTE_USER_ID );
$t_sort_field 				= $t_filter->getField( FILTER_PROPERTY_SORT );
$t_filter_cols = config_get( 'filter_custom_fields_per_row' );
try{
	$t_custom_fields = $t_filter->getFields( 'custom_fields' );
	$t_plugin_fields = $t_filter->getFields( 'plugin_fields' );
} catch( Exception $e ) {
	# don't do anything if no custom/plugin fields are found
}

/**
 *	This is a quick and easy way to maintain the current filter layout while 
 *	simplifying the printing of the dynamic filter links.
 *	@todo Modify or remove this for a more robust dynamic filter using templates. 
 *	@param object $p_field A MantisFilter object
 *	@param bool $p_for_screen
 *	@param int $p_colspan The number of columns to span for this field
 */
function dhtmlLink( $p_field, $p_for_screen, $p_colspan=1 ) { 
    global $s_dhtml_filters, $s_use_javascript;

    $t_filters_url = 'view_all_bug_page.php?load_filters=1&amp;for_screen=' . $p_for_screen;
    $t_filters_url = $t_filters_url . '&amp;filter_target=';
	$t_colspan = $p_colspan > 1 ? " colspan=\"$p_colspan\"" : '';
	echo '<td class="small-caption"', $t_colspan, '>';
    if( ( $p_field && $p_field->enabled ) ) {
        if( $s_use_javascript && $s_dhtml_filters ) {
            echo '<a href="', $t_filters_url, $p_field->field, '" id="', $p_field->field, '_filter"', 'class="filter-link">', $p_field->title, '</a>';
        } else {
            echo string_html_entities( $p_field->title );
        }
    } else {
		echo '&nbsp;';
	}
	echo '</td>';
}

/**
 *	This is a quick and easy way to maintain the current filter layout while 
 *	simplifying the printing of the selected options and the hidden form fields.
 *	@todo Modify or remove this for a more robust dynamic filter using templates. 
 *	@param object $p_field A MantisFilter object
 *	@param int $p_colspan The number of columns to span for this field
 */
function filterField( $p_field, $p_colspan=1 ) {
    global $s_dhtml_filters, $s_use_javascript;
	static $t_view_type = null;

	$t_colspan = $p_colspan > 1 ? " colspan=\"$p_colspan\"" : '';
    if( ( $p_field && $p_field->enabled ) ) {
		if( is_null( $t_view_type ) ) {
			$t_view_type = $p_field->bug_filter->getField('_view_type');
		}		
		echo '<td id="', $p_field->field, '_filter_target"', $t_colspan, ' class="small-caption">';
        # load all the fields( no dhtml )
        if( !$s_dhtml_filters ) {
			$t_field = $p_field;
            include( 'templates/filter/' . $p_field->template . '.tpl.php' );
        } else {
            $t_display = $p_field->display();
			if ( array_key_exists( 'values', $t_display ) && is_array( $t_display['values'] ) ) {
				foreach( $t_display['values'] AS $t_field ) {
            		echo '<input type="hidden" value="', $t_field['value'], '" name="', $t_field['name'], '" />';
				}
			}
			if ( array_key_exists( 'labels', $t_display ) ) {
            	$t_count = count( $t_display['labels'] );
				if( $t_count > 1 ) {
            		$i = 0;
					foreach( $t_display['labels'] AS $t_label ) {
                    	echo $t_label;
                    	echo ( $i < $t_count ?  '<br />' : '' );
                    	$i++;
					}
				} else if( is_array( $t_display['labels'] ) ) {
                    echo $t_display['labels'][0];
				} else {
                    echo $t_display['labels'];
				}
        	}
    	}
		echo '</td>';
	} else {
		echo '<td', $t_colspan, '>&nbsp;</td>';
	}
}
