<?php
# MantisBT - A PHP based bugtracking system

# Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.

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
 *	Base class that implements basic filter functionality
 *	and integration with MantisBT.
 *	@package MantisBT
 *	@subpackage classes
 */
abstract class MantisFilter {

	/**
	 * Field name, as used in the form element and processing.
	 */
	protected $field = null;

	/**
	 *	Filter title is a localisation string for displaying to the user.
	 *	The title value does contain punctuation ( Reporter: )
	 */
	protected $title = null;

	/**
	 *	Filter column title is a localisation string for displaying to the user.
	 *	The column title value should not contain punctuation ( Reporter )
	 */
	protected $column_title = null;

	/**
	 * Filter type, as defined in core/constant_inc.php
	 */
	protected $type = null;

	/**
	 * Default filter value, used for non-list filter types.
	 */
	protected $default = META_FILTER_ANY;
	protected $default_sort_direction = 'ASC';

	/**
	 * Form element size, used for non-boolean filter types.
	 */
	protected $size = 10;

	/**
	 *	Is the field allowed an any option
	 */
	protected $has_any = true;

	/**
	 *	Is the field allowed a none option
	 */
	protected $has_none = false;

	/**
	 *	@bool $enabled Used to indicate whether or not the field should be displayed in the filter
	 */
	protected $enabled = true;

	/**
	 *	The name of the html template to use for the field.
	 *	Do not include the .tpl.php extensions.
	 */
	protected $template = 'textbox';

	/**
	 *	The selected value(s) of the field
	 */
	protected $filter_value = null;

	/**
	 *	A reference to the bug filter object
	 */
	protected $bug_filter = null;

	public function __get( $p_field ) {
		switch( $p_field ) {
			case 'column_title':
				if( !is_null( $this->column_title ) ) {
					return lang_get_defaulted( $this->column_title );
					break;
				}
			case 'title':
				return lang_get_defaulted( $this->title );
			break;
			default:
				return $this->$p_field;
			break;
		}
	}

	public function __set( $p_field, $p_value ) {
		switch( $p_field ) {
			default:
				$this->$p_field = $p_value;
			break;
		}
	}

	/**
	 *	Validate the filter input, returning true if input is
	 *	valid, or returning false if invalid.  Invalid inputs will
	 *	be replaced with the filter's default value.
	 *	@return boolean Input valid (true) or invalid (false)
	 */
	public function validate() {
		if( is_null( $this->filter_value ) ) {
			$this->filter_value = $this->default;
		}
		return true;
	}

	/**
	 *	Build the SQL query elements 'join', 'where', and 'params'
	 *	as used by MantisBugFilter to create the filter query.
	 *	@see MantisFilterBug::addQueryElement
	 */
	abstract function query();

	/**
	 *	Add the sort query params to the bug filter in the position indicated
	 *	by the sort field element.
	 *	@see MantisFilterBug::getField
	 *	@see MantisFilterBug::addQueryElement
	 */
	public function sortParams() {
		$t_filter = $this->bug_filter;
		$t_sort = $t_filter->getField( FILTER_PROPERTY_SORT );
		$t_pos = array_search( $this->field, $t_sort->sort_field );
		$t_dir = $t_sort->sort_direction[$t_pos];

		$t_filter->addQueryElement( 'order_clauses', "{$t_filter->tables['bug']}.$this->field $t_dir" );
	}

	/**
	 *	Returns array of template values for a multi-value filter field.
	 *	Array params are:
	 *		We use a multi-dimensional array for hidden values because some filter properties require multiple fields. ( tags, relationships )
	 *		$t_display['values'][] = array(
	 *				['name'] = the name of the hidden html field,
	 *				['value'] = the value to be stored in the hidden field
	 *			)
	 *		$t_display['labels'][] = an array of localized values for display to the user.
	 *  @return $t_display array An assoc array of labels and values to display in the filter
	 */
	abstract function display();

	/**
	 *	Get the post/get value for the field,
	 *	assign the result to the filter_value member
	 */
	abstract function processGPC();

	/**
	 * For list type filters, define a keyed-array of possible
	 * filter options, not including an 'any' value.
	 * @return array Filter options keyed by value=>display
	 */
	public function options() {}

	/**
	 *	Encodes a field and it's value for the filter URL.  This handles the URL encoding
	 *	and must be overridden for arrays and filter properties with multiple input fields.
	 *	@return string url encoded string
	 */
	public function urlEncodeField() {
		if( $this->isAny() ) {
			return '';
		}
		$t_query_array = array();
		if( is_array( $this->filter_value ) ) {
			$t_count = count( $this->filter_value );
			if( $t_count > 1 ) {
				foreach( $this->filter_value as $t_value ) {
					$t_query_array[] = urlencode( $this->field . '[]' ) . '=' . urlencode( $t_value );
				}
			} else if( $t_count == 1 ) {
				$t_query_array[] = urlencode( $this->field . '[]' ) . '=' . urlencode( $this->filter_value[0] );
			}
		} else {
			$t_query_array[] = urlencode( $this->field ) . '=' . urlencode( $this->filter_value );
		}
		if( !is_null( $t_query_array ) ) {
			return implode( $t_query_array, '&' );
		}
	}

	/**
	 *	htmlFieldName allows different field types to override the
	 *	html field name where necessary.
	 *	@return string html field name for this field.
	 */
	public function htmlFieldName() {
		return string_attribute( $this->field );
	}

	/**
	 *  Checks the field value to see if it is an ANY value.
	 *  @return bool true for "ANY" values and false for others.  "ANY" means filter criteria not active.
	 */
	public function isAny( $p_values = null ) {
		if( !$this->has_any ) {
			return false;
		}

		if( is_null( $p_values ) ) {
			$t_values = $this->filter_value;
		} else {
			$t_values = $p_values;
		}
		if( is_array( $t_values ) ) {
			if( count( $t_values ) == 0 ) {
				return true;
			}

			foreach( $t_values as $t_value ) {
				if(( META_FILTER_ANY == $t_value ) && ( is_numeric( $t_value ) ) ) {
					return true;
				}
			}
		} else {
			if( is_string( $t_values ) && is_blank( $t_values ) ) {
				return true;
			}

			if( is_bool( $t_values ) && !$t_values ) {
				return true;
			}

			if(( META_FILTER_ANY == $t_values ) && ( is_numeric( $t_values ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 *  Checks the field value to see if it is a NONE value.
	 *  @return bool true for "NONE" values and false for others.
	 *  @todo is a check for these necessary?  if ( ( $t_filter_value === 'none' ) || ( $t_filter_value === '[none]' ) )
	 *      previous installations may have stored string values 'none' or '[none']  do we need to continue supporting these?
	 */
	public function isNone( $p_values=null ) {
		if( is_null( $p_values ) ) {
			$t_values = $this->filter_value;
		} else {
			$t_values = $p_values;
		}
		if( is_array( $t_values ) ) {
			foreach( $t_values as $t_value ) {
				if(( META_FILTER_NONE == $t_value ) && ( is_numeric( $t_value ) ) ) {
					return true;
				}
			}
		} else {
			if( is_string( $t_values ) && is_blank( $t_values ) ) {
				return false;
			}
			if(( META_FILTER_NONE == $t_values ) && ( is_numeric( $t_values ) ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 *	Store a reference to the bug filter to access dependant fields and
	 *	add query params.
	 *	@param p_bug_filter A reference to the MantisBugFilter object
	 */
	public function setBugFilter( $p_bug_filter ) {
		if( $p_bug_filter instanceof MantisBugFilter ) {
			$this->bug_filter = $p_bug_filter;
		}
	}
}

