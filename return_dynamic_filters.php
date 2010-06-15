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
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses custom_field_api.php
 * @uses error_api.php
 * @uses filter_constants_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'error_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );

auth_ensure_user_authenticated();

compress_enable();

$t_filter = MantisBugFilter::loadCurrent();
$t_view_type = $t_filter->getField('_view_type');
$t_view_type->processGPC();

/**
 * Prepend headers to the dynamic filter forms that are sent as the response from this page.
 */
function return_dynamic_filters_prepend_headers() {
	if ( !headers_sent() ) {
		header( 'Content-Type: text/html; charset=utf-8' );
	}
}
$t_use_javascript = ( ON == config_get( 'use_javascript' ) ? true : false );
$t_dhtml_filters = ( ON == config_get( 'dhtml_filters' ) ? true : false );

$f_filter_target = gpc_get_string( 'filter_target' );
$t_field = $t_filter->getField( $f_filter_target );

if( $t_field ) {
	return_dynamic_filters_prepend_headers();
	include( 'templates/filter/' . $t_field->template . '.tpl.php' );
}
