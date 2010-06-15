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
class MantisFilterUserHandler extends MantisFilterUser {
	public function __construct( $p_field_name, $p_filter_input=null ) {
		parent::__construct( $p_field_name, $p_filter_input );

		$t_project_id = helper_get_current_project();
		# default overrides for enum fields
		if( access_has_project_level( config_get( 'view_handler_threshold' ), $t_project_id ) ) {
			$this->has_none = true;
			$this->title = 'assigned_to_label';
			$this->column_title = 'assigned_to';
		} else {
			return false;
		}
	}

	/**
	 *	A keyed-array of possible filter options excluding meta filter options (any, none, myself, etc)
	 *	@return array Filter options keyed by value=>display
	 */
	public function options( $p_project_id = null ) {
		return parent::options( $p_project_id );
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
			$t_display['values'] = array( array( 'name'=>$this->field, 'value'=>string_attribute( META_FILTER_ANY ) ), );
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
					# @todo check that access check is needed.  There are several cases where it prevents access when it probably shouldn't
					if( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) {
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
}
