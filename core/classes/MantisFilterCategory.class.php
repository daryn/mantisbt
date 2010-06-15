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
 * MantisFilterCategory
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org

/**
 * Class that implements filter functionality for category field
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterCategory extends MantisFilterMultiString {
	public function __construct( $p_field_name, $p_filter_input=null ) {
		parent::__construct( $p_field_name, $p_filter_input );
		$this->title = 'category_label';
		$this->column_title = 'category';
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
				$t_values[] = array( 'name'=>$this->field, 'value'=>string_attribute( $t_current ) );
				$t_labels[] = string_display( $t_current );
			}
			$t_display['values'] = $t_values; 
			$t_display['labels'] = $t_labels; 
		}
		return $t_display;
	}

	/**
	 *	Get the list of categories for all projects/subprojects selected in the filter
	 */
	public function options() {
		$t_project_field = $this->bug_filter->getField(FILTER_PROPERTY_PROJECT_ID);
		$t_project_count = count( $t_project_field->filter_value );
		$t_category_table = db_get_table( 'category' );
		$t_project_table = db_get_table( 'project' );

		$t_filter_project_ids = $t_project_field->filter_value;
		if( is_array( $t_filter_project_ids ) ) {
			$t_key = array_search( ALL_PROJECTS, $t_filter_project_ids );
			if( $t_key !== false ) {
				# unset everything else. It's all projects
				$t_filter_project_ids = array( helper_get_current_project() );
			} else {
				$t_key = array_search( META_FILTER_CURRENT, $t_filter_project_ids );
				if( $t_key !== false ) {
					# need the actual id to get the categories
					$t_filter_project_ids[$t_key] = helper_get_current_project();
				}
			}
		} else if ( META_FILTER_CURRENT == $t_filter_project_ids ) {
			$t_filter_project_ids = array( helper_get_current_project() );
		}
		$t_project_ids = array();
		foreach( $t_filter_project_ids AS $t_id ) {
			$t_project_ids = array_merge( $t_project_ids, project_hierarchy_inheritance( $t_id ) );
		}

		$t_subproject_ids = array();
		foreach( $t_project_ids as $t_project_id ) {
			$t_subproject_ids = array_merge( $t_subproject_ids, current_user_get_all_accessible_subprojects( $t_project_id ) );
		}

		$t_project_ids = array_merge( $t_project_ids, $t_subproject_ids );
		$t_project_where = ' project_id IN ( ' . implode( ', ', $t_project_ids ) . ' ) ';

		# grab all categories in the project category table
		# if multiple projects selected, display project with the category
		$t_cat_arr = array();
		$t_query = "SELECT DISTINCT name FROM $t_category_table WHERE $t_project_where ORDER BY name";
		$t_result = db_query( $t_query );
		$t_options = array();
		while( $t_category = db_fetch_array( $t_result ) ) {
			$t_selected = in_array( $t_category['name'], $this->filter_value, true );
			$t_options[] = array( 'value'=>string_attribute( $t_category['name']), 'label'=>string_html_entities( $t_category['name']), 'selected'=>$t_selected );
		}
		return $t_options;
	}

	public function query() {
		if( !$this->isAny() ) {
			$t_filter = $this->bug_filter;
			$t_clauses = array();
			foreach( $this->filter_value as $t_filter_member ) {
				if( !$this->isNone( $t_filter_member ) ) {
					array_push( $t_clauses, $t_filter_member );
				}
			}

			if( 1 < count( $t_clauses ) ) {
				$t_where_tmp = array();
				foreach( $t_clauses as $t_clause ) {
					$t_where_tmp[] = db_param();
					$t_filter->addQueryElement( 'where_params', $t_clause );
				}
				$t_where_string = "( {$t_filter->tables['bug']}.category_id in ( SELECT id FROM {$t_filter->tables['category']} WHERE name in (" . implode( ', ', $t_where_tmp ) . ") ) )";
				$t_filter->addQueryElement( 'where_clauses', $t_where_string );
			} else {
				$t_filter->addQueryElement( 'where_params', $t_clauses[0] );
				$t_where_string = "( {$t_filter->tables['bug']}.category_id in ( SELECT id FROM {$t_filter->tables['category']} WHERE name=" . db_param() . ") )";
				$t_filter->addQueryElement( 'where_clauses', $t_where_string );
			}
		}
	}
}
