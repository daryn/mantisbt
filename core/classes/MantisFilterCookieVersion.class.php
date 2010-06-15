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
 * MantisFilterInt
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org

/**
 * Class that implements integer filter functionality
 * and integration with MantisBT.
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterCookieVersion extends MantisFilterString {
	/**
	 */
	public function __construct( $p_field_name, $p_filter_input=null ) {
		$this->field = $p_field_name;
		$this->title = '';
		$this->default = config_get( 'cookie_version' );
		$t_invalid_versions = array( 'v1', 'v2', 'v3', 'v4' );
		if( in_array( $p_value, $t_invalid_versions ) ) {
			throw new Exception( ERROR_FILTER_TOO_OLD );
		}
		$this->filter_value = $p_filter_input;
	}

	/**
	 */
	public function validate() {
		if( is_null( $this->filter_value ) ) {
			$this->filter_value = $this->default;
		}

		return true;
	}
}
