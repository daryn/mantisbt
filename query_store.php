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
 * @uses filter_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

/**
 * MantisBT Core API's
 */
require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'filter_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'query_store' );

auth_ensure_user_authenticated();
compress_enable();

try {
	$f_save_new = gpc_get_bool( 'save_new_query' );
	if( $f_save_new ) {
		$t_stored_query = MantisStoredQuery::getCurrent();
	} else {
		$f_id = gpc_get_int('source_query_id', 0 );
		if( $f_id ) {
			$t_stored_query = MantisStoredQuery::getById( $f_id );
		} else {
			$t_stored_query = new MantisStoredQuery();
		}
	}
	$t_is_named = true;
	$t_stored_query->process( $t_is_named );
	$t_stored_query->save();

	gpc_set_cookie( config_get( 'view_all_cookie' ), $t_stored_query->id, time()+config_get( 'cookie_time_length' ), config_get( 'cookie_path' ) );
	form_security_purge( 'query_store' );
	print_header_redirect( 'view_all_set.php?type=3&source_query_id=' . $t_stored_query->id );
} catch( Exception $e ) {
	echo $e;
}
