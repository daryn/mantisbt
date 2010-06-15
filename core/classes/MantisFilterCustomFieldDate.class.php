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
 * MantisFilterCustomFieldDate
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
class MantisFilterCustomFieldDate extends MantisFilterDate {
	protected $field_info = null;
	protected $default_sort_direction = 'DESC';

	public function __construct( $p_field_info, $p_value = null ) {
		parent::__construct( 'custom_field_' . $p_field_info['id'], $p_value );
		$this->field_info = $p_field_info;
		# override title
		$this->title = $p_field_info['name'] . ':';
		$this->column_title = $p_field_info['name'];
	}

	public function query() {
		$t_filter = $this->bug_filter;
		$t_cf_id = $this->field_info['id'];
		$t_alias = 'cf_string' . $t_cf_id; 
		if( $this->start_date->value || $this->end_date->value ) {
			$t_join_string = "LEFT JOIN {$t_filter->tables['cf_string']} $t_alias ON {$t_filter->tables['bug']}.id  = $t_alias.bug_id AND $t_alias.field_id=$t_cf_id";
			$t_filter->addTableJoin( $t_filter->tables['bug'], $t_alias, $t_join_string ); 
		}
	
		if( $this->start_date->value && $this->end_date->value ) {
			$t_filter->addQueryElement( 'where_params', $this->start_date->value );
			$t_filter->addQueryElement( 'where_params', $this->end_date->value );
			$t_filter->addQueryElement( 'where_clauses', "( $t_alias.value BETWEEN " . db_param() . ' AND ' . db_param() . ' )' );
		} else if( $this->start_date->value ) {
			$t_filter->addQueryElement( 'where_params', $this->start_date->value );
			$t_filter->addQueryElement( 'where_clauses', "( $t_alias.value >= " . db_param() . ' )' );
		} else if ( $this->end_date->value ) {
			$t_filter->addQueryElement( 'where_params', $this->end_date->value );
			$t_filter->addQueryElement( 'where_clauses', "( $t_alias.value <= " . db_param() . ' )' );
		}
	}

	/** 
     */
    public function sortParams() {
        $t_filter = $this->bug_filter;
        $t_sort = $t_filter->getField( FILTER_PROPERTY_SORT );
        $t_pos = array_search( $this->field, $t_sort->sort_field );
        $t_dir = $t_sort->sort_direction[$t_pos];

        $t_cf_id = $this->field_info['id'];
        $t_alias = 'cf_string' . $t_cf_id;

        $t_join_string = "LEFT JOIN {$t_filter->tables['cf_string']} $t_alias ON {$t_filter->tables['bug']}.id  = $t_alias.bug_id AND $t_alias.field_id=$t_cf_id";
		$t_filter->addTableJoin( $t_filter->tables['bug'], $t_alias, $t_join_string ); 
        $t_filter->addQueryElement( 'select_clauses', "$t_alias.value");
        $t_filter->addQueryElement( 'order_clauses', "$t_alias.value $t_dir" );
    }
}
