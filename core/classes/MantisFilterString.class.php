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
 * Base class that implements basic filter functionality
 * and integration with MantisBT.
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterString extends MantisFilter {
	public function __construct( $p_field_name, $p_value=null ) {
		$this->field = $p_field_name;
		$this->title = $p_field_name . '_label';
		$this->filter_value = $p_value;
	}
	/**
	 *	Get and normalize any POST/GET value(s) sent for this field
	 *	Assign the result to the value member if no value is sent, use
	 *	the existing value as the default.
	 *	@return bool true if value is valid, false if not
	 */
	public function processGPC() {
		$this->filter_value = gpc_get_string( $this->field, $this->filter_value );
	}

	/**
	 * Build the SQL query elements 'join', 'where', and 'params'
	 * as used by MantisBugFilter to create the filter query.
	 * @param multi Filter field input
	 * @return array Keyed-array with query elements; see developer guide
	 */
	public function query() {
		return $this->filter_value;
	}
	public function display() {
		return array( array( 'label'=>$this->filter_value, 'value'=>string_html_specialchars( $this->filter_value ) ) );
	}
}
