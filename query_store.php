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
 * @uses compress_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'query_store' );

auth_ensure_user_authenticated();
compress_enable();

$f_query_name = strip_tags( gpc_get_string( 'query_name' ) );

$t_query_redirect_url = 'query_store_page.php';

try {
	$t_bug_filter = MantisBugFilter::getById( gpc_get_cookie( config_get( 'view_all_cookie' ), '' ) );
	$t_bug_filter->processGPC(7);
	$t_bug_filter->validateStored();
} catch( Exception $e ) {
	$t_query_redirect_url = $t_query_redirect_url . '?error_msg=' . urlencode( $e->msg() );
	print_header_redirect( $t_query_redirect_url );
	exit;
}

# When inserting a filter, we need to update the _source_query_id element of the query string after we insert to
# prevent it from getting out of sync with the actual database id.  It is possible to filter by an existing stored query,
# modify the filter and then save as a new record.  In this case the id for the original query is getting saved with the new query.
# inserting, then updating the serialized string with the new id prevents this problem.
$t_new_row_id = $t_bug_filter->saveCurrent();
$t_source_query_field = $t_bug_filter->getField('_source_query_id');
$t_source_query_field->filter_value = $t_new_row_id;
# update with the correct id in the filter string.
$t_new_row_id = $t_bug_filter->saveCurrent();

form_security_purge( 'query_store' );

if ( $t_new_row_id == -1 ) {
	$t_query_redirect_url = $t_query_redirect_url . '?error_msg='
		. urlencode( lang_get( 'query_store_error' ) );
	print_header_redirect( $t_query_redirect_url );
} else {
	print_header_redirect( 'view_all_bug_page.php' );
}
