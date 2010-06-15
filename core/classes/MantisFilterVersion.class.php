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
 * Class that implements filter functionality for version fields
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterVersion extends MantisFilterMultiString {
	/**
	 *	Is the field allowed a none option
	 */
	protected $has_none = true;

	public function __construct( $p_field_name, $p_filter_input=null ) {
		parent::__construct( $p_field_name, $p_filter_input);
		$t_project_id = helper_get_current_project();
		$this->enabled = version_should_show_product_version( $t_project_id );
   
        # default overrides for enum fields
		if( $this->field == FILTER_PROPERTY_VERSION ) {
        	$this->title = 'product_version_label';
        	$this->column_title = 'product_version';
		}
    }

	/**
	 *  Returns array of template values for a multi-value filter field.
	 *  @return array An assoc array of labels and values to display in the filter
	 */
	public function display() {
		$t_project_field = $this->bug_filter->getField(FILTER_PROPERTY_PROJECT_ID);
		$t_project_count = count( $t_project_field->filter_value );

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
	 *	Get a list of version options based on the selected/accessible projects.
	 *	Since this is for filtering, the query now retrieves only versions actually
	 *	selected for bugs.  Other versions are irrelevant as there are no records for
	 *	them in the bug table.
	 *	@return array Filter options keyed by value=>display
	 */
	public function options() {
		$t_filter = $this->bug_filter;
		$t_project_field = $t_filter->getField(FILTER_PROPERTY_PROJECT_ID);
		$t_project_count = count( $t_project_field->filter_value );

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
            $t_tmp_ids = project_hierarchy_inheritance( $t_id );
			if( $t_tmp_ids ) {
				# remove ALL_PROJECTS from the inheritance list
				$t_all_pos = array_search( ALL_PROJECTS, $t_tmp_ids );
				if( $t_all_pos !== false ) {
					unset( $t_tmp_ids[$t_all_pos] );
				}
            	$t_project_ids = array_merge( $t_project_ids, $t_tmp_ids );
			}
        }

        $t_subproject_ids = array();
        foreach( $t_project_ids as $t_project_id ) {
			$t_tmp_ids = current_user_get_all_accessible_subprojects( $t_project_id );
			if( $t_tmp_ids ) {
            	$t_subproject_ids = array_merge( $t_subproject_ids, $t_tmp_ids );
			}
        }
        $t_project_ids = array_merge( $t_project_ids, $t_subproject_ids );
		if( count( $t_project_ids ) == 1 ) {
			if( $t_project_ids[0] == ALL_PROJECTS ) {
				$t_project_str = '';
			} else {
				$t_project_str = 'WHERE project_id=' . db_param();
				$t_params[] = $t_project_ids[0];
			}
		} else {
			foreach( $t_project_ids AS $t_id ) {
				$t_where[] = db_param();
				$t_params[] = $t_id;
			}
			$t_project_str = 'WHERE project_id in ( ' . join( ', ', $t_where ) . ' )' ;
		}
		$t_versions = array();

		$t_sql = "SELECT DISTINCT {$this->field} AS version FROM {$t_filter->tables['bug']} $t_project_str ORDER BY version";
		$t_result = db_query_bound( $t_sql, $t_params );
		while ( $t_row = db_fetch_array( $t_result ) ) {
			if( !empty( $t_row['version'] ) ) {
				$t_selected = in_array( $t_row['version'], $this->filter_value ) ? true : false;
				$t_options[] = array( 'value'=>$t_row['version'], 'label'=>string_shorten( $t_row['version'], $t_max_length ), 'selected'=>$t_selected );
			}
		}
		return $t_options;
	}

	/** 
     */
    public function sortParams() {
        $t_filter = $this->bug_filter;
        $t_sort = $t_filter->getField( FILTER_PROPERTY_SORT );
        $t_pos = array_search( $this->field, $t_sort->sort_field );
        $t_dir = $t_sort->sort_direction[$t_pos];

        $t_filter->addQueryElement( 'order_clauses', "{$t_filter->tables['bug']}.{$this->field} $t_dir" );
    }
}
