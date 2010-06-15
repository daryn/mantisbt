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
 * MantisFilterCustomField
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org

/**
 * Class that implements filter functionality for custom fields
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterCustomField extends MantisFilterMultiString {
	protected $field_info = null;

	public function __construct( $p_field_info, $p_filter_input=null ) {
		parent::__construct( 'custom_field_' . $p_field_info['id'], $p_filter_input );
		$this->field_info = $p_field_info;
		$this->title = $p_field_info['name'] . ':';
		$this->column_title = $p_field_info['name'];
	}

   /** 
     * For list type filters, define a keyed-array of possible
     * filter options excluding any meta options,
     * @return array Filter options keyed by value=>display
     */
    public function options() {
		$t_values = custom_field_distinct_values( $this->field_info );

        foreach ( $t_values as $t_key=>$t_value ) { 
            if( is_array( $this->filter_value ) ) { 
                $t_selected = in_array( $t_value, $this->filter_value );
            } else {
                $t_selected = ( $t_value == $this->filter_value ? true : false );
            }   
            $t_option = array( 'label'=>string_html_entities($t_value), 'value'=>string_attribute($t_value), 'selected'=>$t_selected );
            $t_options[$t_key] = $t_option;
        }   
        return $t_options;
    }

	public function query() {
		$t_filter = $this->bug_filter;

		if( !$this->isAny() ) {
			$t_cf_id = $this->field_info['id'];

			$t_alias = 'cf_string' . $t_cf_id;

			# We need to filter each joined table or the result query will explode in dimensions
			# Each custom field will result in a exponential growth like Number_of_Issues^Number_of_Custom_Fields
			# and only after this process ends (if it is able to) the result query will be filtered
			# by the WHERE clause and by the DISTINCT clause
			$t_join_string = "LEFT JOIN {$t_filter->tables['cf_string']} $t_alias ON {$t_filter->tables['bug']}.id  = $t_alias.bug_id AND $t_alias.field_id=$t_cf_id";
			$t_filter->addTableJoin( $t_filter->tables['bug'], $t_alias, $t_join_string );
			$t_clauses = array();
			foreach( $this->filter_value as $t_filter_member ) {
				$t_filter_member = stripslashes( $t_filter_member );
				if( $this->isNone( $t_filter_member ) ) {
					# coerce filter value if selecting META_FILTER_NONE so it will match empty fields
					$t_filter_member = '';
					# but also add those _not_ present in the custom field string table
					array_push( $t_clauses, "{$t_filter->tables['bug']}.id NOT IN (SELECT bug_id FROM {$t_filter->tables['cf_string']} WHERE field_id=$t_cf_id)" );
				}
				switch( $this->field_info['type'] ) {
					case CUSTOM_FIELD_TYPE_CHECKBOX:
					case CUSTOM_FIELD_TYPE_MULTILIST:
						$t_filter->addQueryElement( 'where_params', '%|' . $t_filter_member . '|%' );
						array_push( $t_clauses, db_helper_like( "$t_alias.value" ) );
					break;
					default:
						$t_filter->addQueryElement( 'where_params', $t_filter_member );
						array_push( $t_clauses, "$t_alias.value=" . db_param() );
				}
			}
			$t_filter->addQueryElement( 'where_clauses', '(' . implode( ' OR ', $t_clauses ) . ')' );
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
