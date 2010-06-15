<?php
# MantisBT - a php based bugtracking system

# Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.

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
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 *	Class for handling display and conversion of dates
 */
class MantisDate {
	private $value;
	private $format;

	/**
     * Initialize a date object.
     * @param string|int date
     */
    function __construct( $p_value=0, $p_format=null ) {
		if ( is_numeric( $p_value ) ) {
			$this->value = $p_value;
		} else {
			$this->value = strtotime( $p_value );
		}
		switch( $p_format ) {
			case 'complete':
				$this->format = config_get( 'complete_date_format' );
			break;
			case 'normal':
				$this->format = config_get( 'normal_date_format' );
			break;
			case !null:
				$this->format = $p_format;
			break;
			default:
				$this->format = config_get( 'short_date_format' );
			break;
		}
    }

	public function __get( $p_field_name ) {
		return $this->$p_field_name;
	}

	/**
	 *	This magic function is only called magically in an echo/print context
	 *	until php 5.2.0.  After 5.2.0 print/echo is not required.
	 */
	public function __toString() {
		if ( $this->value == 0 ) {
			return '';
		} else {
			return date( $this->format, $this->value);
		}
    }
}
