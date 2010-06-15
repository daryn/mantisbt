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
class MantisFilterInt extends MantisFilter {
	public function __construct( $p_field_name, $p_filter_input = null ) {
		$this->field = $p_field_name;
		$this->title = $p_field_name . '_label';
		$this->column_title = $p_field_name . '_label';
		if( is_null( $p_filter_input ) ) {
			$this->filter_value = $this->default;
		} else {
			$this->filter_value = $p_filter_input;
        }
	}
	/**
	 *	Get and normalize any POST/GET value(s) sent for this field
	 *	Assign the result to the value member if no value is sent, use
	 *	the existing value as the default.  
	 */
	public function processGPC() {
		$this->filter_value = gpc_get_int( $this->field, $this->filter_value );
	}

	public function query() {
		return $this->filter_value;
	}

	public function display() {
		$t_display['values'] = array( 'name'=>$this->field, 'value'=>string_attribute( $this->filter_value ) );
		$t_display['labels'] = string_html_entities( $this->filter_value );
		return $t_display;
	}
}
