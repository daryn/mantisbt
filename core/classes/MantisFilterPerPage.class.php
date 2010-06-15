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
class MantisFilterPerPage extends MantisFilterInt {
	public function __construct( $p_field_name, $p_filter_input=null ) {
		$this->default = config_get( 'default_limit_view' );
		parent::__construct( $p_field_name, $p_filter_input );
		$this->title = 'show_label';
		$this->title = 'show';
		$this->size = 3;
	}

	/**
	 *	Encodes a field and it's value for the filter URL.
	 *	@return string url encoded string
	 */
	public function urlEncodeField() {
		if( $this->filter_value != config_get( 'default_limit_view' ) ) {
			return urlencode( $this->field ) . '=' . urlencode( $this->filter_value );
		} else {
			return '';
		}
	}
}
