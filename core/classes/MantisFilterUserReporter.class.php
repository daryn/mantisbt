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
class MantisFilterUserReporter extends MantisFilterUser {
	public function __construct( $p_field_name, $p_filter_input=null ) {
		parent::__construct( $p_field_name, $p_filter_input );
		$this->title = 'reporter_label';
		$this->column_title = 'reporter';
	}

	/**
	 *	A keyed-array of possible filter options excluding meta filter options (any, none, myself, etc)
	 *	@return array Filter options keyed by value=>display
	 */
	public function options( $p_project_id = null ) {
		# if current user is a reporter, and limited reports set to ON, only display that name
		# @@@ thraxisp - access_has_project_level checks greater than or equal to,
		# this assumed that there aren't any holes above REPORTER where the limit would apply
		if( ( ON === config_get( 'limit_reporters' ) ) && ( !access_has_project_level( REPORTER + 1 ) ) ) {
			$t_id = auth_get_current_user_id();
			$t_username = user_get_field( $t_id, 'username' );
			$t_realname = user_get_field( $t_id, 'realname' );
			$t_display_name = string_attribute( $t_username );
			if(( isset( $t_realname ) ) && ( $t_realname > '' ) && ( ON == config_get( 'show_realname' ) ) ) {
				$t_display_name = string_attribute( $t_realname );
			}
			$t_options[$t_id] = $t_display_name;
			return $t_options;
		}

		return parent::options( $p_project_id );
	}

	/**
	 *  Returns array of template values for a multi-value user field.
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
				} else if( $this->has_myself && $this->isMyself( $t_current ) ) {
					# @todo this access check may not be needed at all
					if( access_has_project_level( config_get( 'report_bug_threshold' ) ) ) {
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

	/**
	 *	@todo evaluate project id check.  It should probably check the actual filter projects as it
	 *		is possible to filter by multiple projects which may not be the current project at all.
	 */
	public function query() {
		parent::query();

		$t_filter = $this->bug_filter;
		$t_project_id = helper_get_current_project();
    	# limit reporter
    	# @@@ thraxisp - access_has_project_level checks greater than or equal to,
    	#   this assumed that there aren't any holes above REPORTER where the limit would apply
    	#
		if( ( ON === config_get( 'limit_reporters' ) ) && ( !access_has_project_level( REPORTER + 1, $t_project_id, $t_filter->filter_user_id ) ) ) {
        	$t_where_params[] = $t_filter->filter_user_id;
        	array_push( $t_where_clauses, "( {$t_filter->tables['bug']}.reporter_id=" . db_param() . ')' );
    	}
	}
}
