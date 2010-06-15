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
 * MantisFilterChanged
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org

/**
 * Class that implements functionality to highlight recently updated records
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterChanged extends MantisFilterInt {
	public function __construct( $p_field_name, $p_filter_input=null ) {
		$this->default = config_get( 'default_show_changed' );
		parent::__construct( $p_field_name, $p_filter_input);
		$this->title = 'changed_label';
		$this->column_title = 'changed';
		$this->size = 3;
	}

	/**
	 *	Encodes a field and it's value for the filter URL.
	 *	@return string url encoded string
	 */
	public function urlEncodeField() {
		if( !$this->isAny() ) {
			if( $this->filter_value != config_get( 'default_show_changed' ) ) {
				return urlencode( $this->field ) . '=' . urlencode( $this->filter_value );
			}
		} else {
			return '';
		}
	}
}
