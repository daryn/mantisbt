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
 * Icon API
 *
 * @package CoreAPI
 * @subpackage IconAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses helper_api.php
 * @uses utility_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'helper_api.php' );
require_api( 'utility_api.php' );

/**
 * gets the status icon
 * @param string $p_icon
 * @return string html img tag containing status icon
 * @access public
 */
function icon_get_status_icon( $p_icon ) {
	$t_icon_path = config_get( 'icon_path' );
	$t_status_icon_arr = config_get( 'status_icon_arr' );
	$t_priotext = get_enum_element( 'priority', $p_icon );
	if( isset( $t_status_icon_arr[$p_icon] ) && !is_blank( $t_status_icon_arr[$p_icon] ) ) {
		return "<img src=\"$t_icon_path$t_status_icon_arr[$p_icon]\" alt=\"\" title=\"$t_priotext\" />";
	} else {
		return "&nbsp;";
	}
}

/**
 * prints the status icon
 * @param string $p_icon
 * @return null
 * @access public
 */
function print_status_icon( $p_icon ) {
	echo icon_get_status_icon( $p_icon );
}

/**
 * The input $p_dir is either ASC or DESC
 * @param int $p_dir
 * @return null
 * @access public
 */
#function print_sort_icon( $p_dir, $p_sort_by, $p_field ) {
function print_sort_icon( $p_dir ) {
	$t_icon_path = config_get( 'icon_path' );
	$t_sort_icon_arr = config_get( 'sort_icon_arr' );

	if(( 'DESC' == $p_dir ) || ( DESCENDING == $p_dir ) ) {
		$t_dir = DESCENDING;
	} else {
		$t_dir = ASCENDING;
	}

	$t_none = NONE;
	if( !is_blank( $t_sort_icon_arr[$t_dir] ) ) {
		echo "<img src=\"$t_icon_path$t_sort_icon_arr[$t_dir]\" alt=\"\" />";
	} else {
		echo "<img src=\"$t_icon_path$t_status_icon_arr[$t_none]\" alt=\"\" />";
	}
}

