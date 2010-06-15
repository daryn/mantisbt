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
 * MantisFilterDate
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
class MantisFilterDate extends MantisFilterInt {
	protected $start_date = null;
	protected $end_date = null;
	protected $default_sort_direction = 'DESC';

	public function __construct( $p_field_name, $p_value = null ) {
		$this->field = $p_field_name;
		$this->title = $this->field . '_label';
		$this->column_title = $this->field;
		$this->template = 'date';

		if( $p_value && is_array( $p_value ) && array_key_exists( 'start_date', $p_value ) ) {
			$t_start_date = $p_value['start_date'];
			$t_end_date = $p_value['end_date'];
		}

		if( is_null( $t_start_date) ) {
			$this->start_date = new MantisDate( $this->default );
		} else {
			$this->start_date = new MantisDate( $t_start_date );
        }
		if( is_null( $t_end_date) ) {
			$this->end_date = new MantisDate( $this->default );
		} else {
			$this->end_date = new MantisDate( $t_end_date );
        }
		$this->filter_value = array( FILTER_PROPERTY_START_DATE=>$this->start_date->value, FILTER_PROPERTY_END_DATE=>$this->end_date->value );
	}

	public function __set( $p_field, $p_value ) {
		switch( $p_field ) {
			case 'filter_value':
				if( $p_value && is_array( $p_value ) && array_key_exists( 'start_date', $p_value ) ) {
					$t_start_date = $p_value['start_date'];
					$t_end_date = $p_value['end_date'];
				}
				if( is_null( $t_start_date) ) {
					$this->start_date = new MantisDate( $this->default );
				} else {
					$this->start_date = new MantisDate( $t_start_date );
				}
				if( is_null( $t_end_date) ) {
					$this->end_date = new MantisDate( $this->default );
				} else {
					$this->end_date = new MantisDate( $t_end_date );
				}
				$this->filter_value = array( FILTER_PROPERTY_START_DATE=>$this->start_date->value, FILTER_PROPERTY_END_DATE=>$this->end_date->value );
			#	$this->filter_value = array( $this->field . '_' . FILTER_PROPERTY_START_DATE=>$this->start_date->value, $this->field . '_' . FILTER_PROPERTY_END_DATE=>$this->end_date->value );
			break;
			default:
				parent::__set( $p_field, $p_value );
			break;
		}
	}

	/**
	 *	Get and normalize any POST/GET value(s) sent for this field
	 *	Assign the result to the value member if no value is sent, use
	 *	the existing value as the default.  
	 */
	public function processGPC() {
		$t_start_date = gpc_get_string( $this->field . '_' . FILTER_PROPERTY_START_DATE, 0 );
		if( $t_start_date ) {
			$t_start_date .= ' 00:00:00';
		}

		$this->start_date = new MantisDate( $t_start_date );
		$t_end_date = gpc_get_string( $this->field . '_' . FILTER_PROPERTY_END_DATE, 0 );
		if( $t_end_date ) {
			$t_end_date .= ' 23:59:59';
		}
		$this->end_date = new MantisDate( $t_end_date );

		$this->filter_value = array( FILTER_PROPERTY_START_DATE=>$this->start_date->value, FILTER_PROPERTY_END_DATE=>$this->end_date->value );
	}

	public function display() {

		$t_display['values'] = array( 
			$this->htmlStartDateFieldName()=>string_attribute( $this->start_date->__toString() ),
			$this->htmlEndDateFieldName()=>string_attribute( $this->end_date->__toString() ),
		);
		if( 86399 == ( $this->end_date->value - $this->start_date->value ) ) {
			$t_display['labels'] = lang_get( 'on_date' ) . ' ' . string_html_entities( $this->start_date->__toString() );
		} else if( $this->start_date->value && $this->end_date->value ) {
			$t_display['labels'] = string_html_entities( $this->start_date->__toString() ) . ' - ' .  string_html_entities( $this->end_date->__toString() );
		} else if( $this->start_date->value ) {
			$t_display['labels'] = lang_get_defaulted('on_or_after_date' ) . ' ' . string_html_entities( $this->start_date->__toString() );
		} else if( $this->end_date->value ) {
			$t_display['labels'] = lang_get_defaulted('on_or_before_date' ) . ' ' . string_html_entities( $this->end_date->__toString() );
		}

		return $t_display;
	}

	public function query() {
		$t_filter = $this->bug_filter;
		if( $this->start_date->value && $this->end_date->value ) {
			$t_filter->addQueryElement( 'where_params', $this->start_date->value );
			$t_filter->addQueryElement( 'where_params', $this->end_date->value );
			$t_filter->addQueryElement( 'where_clauses', "( {$t_filter->tables['bug']}.{$this->field} BETWEEN " . db_param() . ' AND ' . db_param() . ' )' );
		} else if( $this->start_date->value ) {
			$t_filter->addQueryElement( 'where_params', $this->start_date->value );
			$t_filter->addQueryElement( 'where_clauses', "( {$t_filter->tables['bug']}.{$this->field} >= " . db_param() . ' )' );
		} else if ( $this->end_date->value ) {
			$t_filter->addQueryElement( 'where_params', $this->end_date->value );
			$t_filter->addQueryElement( 'where_clauses', "( {$t_filter->tables['bug']}.{$this->field} <= " . db_param() . ' )' );
		}
	}

	public function htmlStartDateFieldName() {
		return string_attribute( $this->field . '_' . FILTER_PROPERTY_START_DATE );
	}

	public function htmlEndDateFieldName() {
		return string_attribute( $this->field . '_' . FILTER_PROPERTY_END_DATE );
	}
}
