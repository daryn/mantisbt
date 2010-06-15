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
 * MantisFilterSticky
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org

/**
 * class that implements sticky rows filter functionality
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterSticky extends MantisFilterBool {
	public function __construct( $p_field_name, $p_filter_input=null ) {
		parent::__construct( $p_field_name, $p_filter_input );
		$this->has_any = false;
		$this->default = gpc_string_to_bool( config_get( 'show_sticky_issues' ) );
	}

	public function query() {
		# don't do anything.  sticky is just for sorting
		return;
	}
}
