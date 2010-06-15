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
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses error_api.php
 * @uses filter_constants_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses logging_api.php
 * @uses print_api.php
 * @uses tokens_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'error_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'logging_api.php' );
require_api( 'print_api.php' );
require_api( 'tokens_api.php' );
require_api( 'utility_api.php' );

auth_ensure_user_authenticated();

$f_type					= gpc_get_int( 'type', -1 );
$f_source_query_id		= gpc_get_int( 'source_query_id', -1 );
$f_print				= gpc_get_bool( 'print' );
$f_temp_filter			= gpc_get_bool( 'temporary' );
$f_save_query_button 	= gpc_get_string( 'save_query_button', '');

if ( ( $f_type == 3 ) && ( $f_source_query_id == -1 ) ) {
	$f_type = 0;
}

# Clear the source query id.  Since we have entered new filter criteria.
#$t_setting_arr['_source_query_id'] = '';
switch ( $f_type ) {
	# This is when we want to copy another query from the
	# database over the top of our current one
	case '3':
		log_event( LOG_FILTERING, 'view_all_set.php: Copy another query from database' );
		try {
			$t_filter = MantisBugFilter::getById( $f_source_query_id );
			# Store the source query id to select the correct filter in the drop down
			# and to fix improperly saved id's in the serialized string.
			$t_source_field = $t_filter->getField( '_source_query_id' );
			$t_source_field->filter_value = $f_source_query_id;
		} catch( Exception $e ) {
			gpc_clear_cookie( 'view_all_cookie' );
			error_proceed_url( 'view_all_set.php?type=0' );
			trigger_error( ERROR_FILTER_TOO_OLD, ERROR );
			exit; # stop here
		}
		break;

	case '0': # this achieves the same thing as generalising the filter. Not sure why we needed both.
	case '4': # Generalise the filter
		log_event( LOG_FILTERING, 'view_all_set.php: Generalise the filter' );
		$t_filter = MantisBugFilter::loadDefault();
		break;
	default:
		try {
			$t_filter = MantisBugFilter::loadCurrent();
			# the user is applying changes to a query without requesting to manage an
			# existing filter.  Do NOT overwrite it!
			$t_source_field = $t_filter->getField( '_source_query_id' );
			$t_source_field->filter_value = null;
		} catch( Exception $e ) {
			if ( !in_array( $f_type, array( 0, 1, 3 ) ) ) {
				gpc_clear_cookie( 'view_all_cookie' );
				error_proceed_url( 'view_all_set.php?type=0' );
				trigger_error( ERROR_FILTER_TOO_OLD, ERROR );
				exit; # stop here
			}
		}

		# these are handled by the object
		# 1 - Update filters,(default)
		# 2 - Set the sort order and direction,
		# 5 - Just set the search string value
		# 6 - Just set the view_state (simple / advanced) value
		$t_filter->processGPC( $f_type );
		break;
}

$t_filter->validate();

# If only using a temporary filter, don't store it in the database
if ( !$f_temp_filter ) {
	# Store the filter string in the database: its the current filter, so some values won't change
	$t_project_id = helper_get_current_project();
	$t_project_id = ( $t_project_id * -1 );

	$t_row_id = $t_filter->saveCurrent( $t_project_id );

	# set cookie values
	gpc_set_cookie( config_get( 'view_all_cookie' ), $t_row_id, time()+config_get( 'cookie_time_length' ), config_get( 'cookie_path' ) );
}

# redirect to print_all or view_all page
if ( $f_print ) {
	$t_redirect_url = 'print_all_bug_page.php';
} else if( $f_save_query_button ) {
	$t_redirect_url = 'query_store_page.php';
} else {
	$t_redirect_url = 'view_all_bug_page.php';
}

if ( $f_temp_filter ) {
	$t_settings_serialized = $t_filter->getSerializedSettings();
	$t_token_id = token_set( TOKEN_FILTER, $t_settings_serialized );
	$t_redirect_url = $t_redirect_url . '?filter=' . $t_token_id . '&temporary=y';
	html_meta_redirect( $t_redirect_url, 0 );
} else {
	print_header_redirect( $t_redirect_url );
}
