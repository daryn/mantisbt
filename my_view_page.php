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
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses category_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 */

/**
 * MantisBT Core API's
 */
require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'category_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'user_api.php' );
require_css( 'status_config.php' );
require_css( 'myview.css' );

auth_ensure_user_authenticated();

$t_current_user_id = auth_get_current_user_id();

# Improve performance by caching category data in one pass
category_get_all_rows( helper_get_current_project() );

compress_enable();

# don't index my view page
html_robots_noindex();

html_page_top1( lang_get( 'my_view_link' ) );

if ( current_user_get_pref( 'refresh_delay' ) > 0 ) {
	html_meta_redirect( 'my_view_page.php', current_user_get_pref( 'refresh_delay' )*60 );
}

html_page_top2();

print_recently_visited();

$f_page_number		= gpc_get_int( 'page_number', 1 );

$t_per_page = config_get( 'my_view_bug_count' );
$t_bug_count = null;
$t_page_count = null;

$t_status_legend_position = config_get( 'status_legend_position' );

if ( $t_status_legend_position == STATUS_LEGEND_POSITION_TOP || $t_status_legend_position == STATUS_LEGEND_POSITION_BOTH ) {
	html_status_legend();
}

$t_boxes_position = config_get( 'my_view_boxes_fixed_position' );

define( 'MY_VIEW_INC_ALLOW', true );

$t_filters = MantisStoredQuery::getMyViewFilters();
$t_box_count = count( $t_filters );
$t_middle = ceil( $t_box_count/2 ) - 1;

$i=0;
foreach( $t_filters AS $t_filter ) {
	if ( ON == $t_boxes_position ) {
		$t_box_css = ( $i%2 ? 'rightcol-fixed' : 'leftcol-fixed' );
	} else {
		if( $i==0 ){
			echo '<div class="leftcol">';
		}
	}

	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'my_view_inc.php' );

	if ( OFF == $t_boxes_position && $i == $t_middle ) {
			echo '</div>';
			echo '<div class="rightcol">';
	}
	if ( OFF == $t_boxes_position && $i == ( $t_box_count-1 ) ) {
		echo '</div>';
	}
	$i++;
}

# Close the box groups depending on the layout mode and whether an empty cell
# is required to pad the number of cells in the last row to the full width of
# the table.
if ( ON == $t_boxes_position && $t_counter == $t_number_of_boxes && 1 == $t_counter%2 ) {
	echo '<td class="myview-right-col"></td></tr>';
} else if ( OFF == $t_boxes_position && $t_counter == $t_number_of_boxes ) {
	echo '</td></tr>';
}

if ( $t_status_legend_position == STATUS_LEGEND_POSITION_BOTTOM || $t_status_legend_position == STATUS_LEGEND_POSITION_BOTH ) {
	html_status_legend();
}
html_page_bottom();
