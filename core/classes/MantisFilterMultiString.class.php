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
 * MantisFilterMutliString
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org

/**
 * Class that implements filter functionality for multi string fields
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterMultiString extends MantisFilter {
	public function __construct( $p_field_name, $p_filter_input=null ) {
		$this->field = $p_field_name;
		$this->title = $p_field_name . '_label';
		$this->column_title = $p_field_name;
		$this->template = 'select';
		if( is_null( $p_filter_input ) ) {
			$this->filter_value = array( $this->default );
		} else if ( is_array( $p_filter_input ) ) {
			$this->filter_value = $p_filter_input;
		} else {
			$this->filter_value = array( $p_filter_input );
		}
	}
	/**
	 *	Get and normalize any POST/GET value(s) sent for this field
	 *	Assign the result to the value member if no value is sent, use
	 *	the existing value as the default.  
	 */
	public function processGPC() {
		if ( is_array( gpc_get( $this->field, $this->filter_value ) ) ) { 
			$this->filter_value = gpc_get_string_array( $this->field, $this->filter_value );
		} else {
			$f_value = gpc_get_string( $this->field, $this->filter_value );
			$this->filter_value = array( $f_value );
		}
	}

	public function validate() {
		# default overrides for enum fields
		if( is_null( $this->filter_value ) ) {
			$this->filter_value = array( $this->default );
		} else if ( !is_array( $this->filter_value ) ) {
			$this->filter_value = array( $this->filter_value );
		}
		return true;
	}

	public function query() {
		$t_filter = $this->bug_filter;
		if( $this->has_any && $this->isAny() ) {
			return;
		}
		$t_clauses = array();
		foreach( $this->filter_value as $t_filter_member ) {
			$t_filter_member = stripslashes( $t_filter_member );
			if( $this->isNone( $t_filter_member ) ) {
				array_push( $t_clauses, '' );
			} else {
				array_push( $t_clauses, $t_filter_member );
			}
		}
		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_filter->addQueryElement( 'where_params', $t_clause );
			}
			$t_filter->addQueryElement( 'where_clauses', "( {$t_filter->tables['bug']}.{$this->field} IN (" . implode( ', ', $t_where_tmp ) . ") )" );
		} else {
			$t_filter->addQueryElement( 'where_params', $t_clauses[0] );
			$t_filter->addQueryElement( 'where_clauses', "( {$t_filter->tables['bug']}.{$this->field} =" . db_param() . " )" );
		}
	}

	/**
	 *  Returns array of template values for a multi-value filter field.
	 *  @return array An assoc array of labels and values to display in the filter
	 */
	public function display() {
		if( $this->has_any && $this->isAny() ) {
			$t_display['values'] = array(
				array( 'name'=>$this->field, 'value'=>string_attribute( META_FILTER_ANY ) ),
			);
			$t_display['labels'] = string_display( lang_get( 'any' ) ); 
		} else {
			$t_values = array();
			$t_labels = array();
			$this->filter_value = is_array( $this->filter_value ) ? $this->filter_value : array( $this->filter_value );
			foreach( $this->filter_value as $t_current ) {
				$t_current = stripslashes( $t_current );
				if( $this->has_none && $this->isNone( $t_current ) ) {
					$t_values[] = array( 'name'=>$this->field, 'value'=>string_attribute( META_FILTER_NONE ) );
					$t_labels[] = string_display( lang_get( 'none' ) );
				} else {
					$t_values[] = array( 'name'=>$this->field, 'value'=>string_attribute( $t_current ) );
					$t_labels[] = string_display( $t_current );
				}
			}
			$t_display['values'] = $t_values; 
			$t_display['labels'] = $t_labels; 
		}
		return $t_display;
	}
	
	/**
	 *	Encodes a field and it's value for the filter URL.  This handles the URL encoding
	 *	and arrays.
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
			# it should always be an array, but just in case...
			$t_query_array[] = urlencode( $this->field . '[]' ) . '=' . urlencode( $this->filter_value );
		}
		return implode( $t_query_array, '&' );
	}

	public function htmlFieldName() {
		return string_attribute( $this->field ) . '[]';
	}

}
