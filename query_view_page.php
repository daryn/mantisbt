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
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses filter_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses rss_api.php
 */

/**
 * MantisBT Core API's
 */
require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'filter_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'rss_api.php' );
require_js( 'queryStore.js' );

auth_ensure_user_authenticated();

$t_stored_queries = MantisStoredQuery::getAvailableByAccessLevel();

# Special case: if we've deleted our last query, we have nothing to show here.
# If i'm on the view all bug page and # click manage filters, I get redirectred
# to the view all bug page with no indication why it didn't work.
if ( count( $t_stored_queries ) < 1 ) {
	error_parameters( helper_get_current_project() );
	trigger_error( ERROR_FILTER_NO_STORED_QUERIES_FOUND_FOR_PROJECT, WARNING );
}

compress_enable();

html_page_top();

$t_rss_enabled = config_get( 'rss_enabled' );
$t_filter_preferences = new MantisStoredQueryPreferences();

define( 'QUERY_LIST_INC_ALLOW', true );

$t_access_levels_enum_string = config_get( 'access_levels_enum_string' );
$t_enum_values = MantisEnum::getValues( $t_access_levels_enum_string );
ksort( $t_stored_queries );
foreach( $t_stored_queries AS $t_access_level=>$t_query_arr ) {
	natcasesort( $t_query_arr );
	if( $t_access_level === 0 ) {
		$t_list = 'private';
		$t_access_label = ucfirst( lang_get( 'private' ) );
	} else if( $t_access_level == 'default' ) {
		$t_list = 'default';
		$t_access_label = lang_get( 'default_queries' );
	} else {
		$t_list = MantisEnum::getLabel( $t_access_levels_enum_string, $t_access_level) ;
		$t_access_label = ucfirst( MantisEnum::getLocalizedLabel( $t_access_levels_enum_string, lang_get( 'access_levels_enum_string' ), $t_access_level) );
	}
	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'query_list_inc.php' );
}
html_page_bottom();
