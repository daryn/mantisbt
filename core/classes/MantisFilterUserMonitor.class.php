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
 * Class that implements filter functionality for user fields
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterUserMonitor extends MantisFilterUser {

	public function __construct( $p_field_name, $p_filter_input=null ) {
		parent::__construct( $p_field_name, $p_filter_input );

		# default overrides for enum fields
		$this->title = 'monitored_by_label';
	}
	/**
	 *	A keyed-array of possible filter options excluding meta filter options (any, none, myself, etc)
	 *	@return array Filter options keyed by value=>display
	 */
	public function options( $p_project_id = null ) {
		$t_threshold = config_get( 'show_monitor_list_threshold' );
		$t_has_project_level = access_has_project_level( $t_threshold );
		if( $t_has_project_level ) {
			return parent::options( $p_project_id );	
		}
	}

	/**
	 *  Returns array of template values for a multi-value user field.
	 *  @param  string $p_field_name
	 *  @param mixed $p_field_value
	 *  @param bool $p_check_none Whether or not to compare for a none value. Some fields use it, some don't.
	 *  @return array An assoc array of labels and values to display in the filter
	 */
	public function display() {
		$t_display_arr = array();
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
				} else if( $this->has_myself && $this->isMyself( $t_current ) ) {
					if( access_has_project_level( config_get( 'monitor_bug_threshold' ) ) ) { # @todo may not be needed at all...this will be different for each user field
						$t_values[] = array( 'name'=>$this->field, 'value'=>string_attribute( META_FILTER_MYSELF ) );
						$t_labels[] = string_display( lang_get( 'myself' ) );
					}
				} else {
					$t_values[] = array( 'name'=>$this->field, 'value'=>string_attribute( $t_current ) );
					$t_labels[] = string_display( user_get_name( $t_current ) );
				}
			}
			$t_display['values'] = $t_values; 
			$t_display['labels'] = $t_labels; 
		}
		return $t_display;
	}

	public function query() {
		$t_filter = $this->bug_filter;
		# users monitoring a bug
		if( !$this->isAny() ) {
			$t_clauses = array();
			$t_table_name = 'user_monitor';
			$t_filter->addTableJoin( $t_filter->tables['bug'], $t_filter->tables['bug_monitor'], "LEFT JOIN {$t_filter->tables['bug_monitor']} $t_table_name ON {$t_filter->tables['bug']}.id = $t_table_name.bug_id" ); 
			foreach( $this->filter_value as $t_filter_member ) {
				if( $this->isMyself( $t_filter_member ) ) {
					array_push( $t_clauses, $t_filter->filter_user_id );
				} else {
					array_push( $t_clauses, $t_filter_member );
				}
			}
			if( 1 < count( $t_clauses ) ) {
				$t_where_tmp = array();
				foreach( $t_clauses as $t_clause ) {
					$t_filter->addQueryElement('where_params', $t_clause );
					$t_where_tmp[] = db_param();
            	}
				$t_filter->addQueryElement('where_clauses', "( $t_table_name.user_id in (" . implode( ', ', $t_where_tmp ) . ") )" );
			} else {
				$t_filter->addQueryElement('where_params', $t_clauses[0] );
				$t_filter->addQueryElement('where_clauses', "( $t_table_name.user_id=" . db_param() . " )" );
			}
		}
	}
}
