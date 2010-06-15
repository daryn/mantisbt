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
class MantisFilterUser extends MantisFilterMultiInt {
	protected $has_myself = true;

	public function __get( $p_field ) {
		switch( $p_field ) {
			case 'values':
				foreach( $this->filter_value AS $t_key ) {
					if( $t_key > 0 ) {
						$t_users[$t_key] = MantisUser::getById( $t_key );
					}
				}
				return $t_users;
			break;
			default:
				return parent::__get( $p_field );
			break;
		}
	}
	/**
	 *	A keyed-array of possible filter options excluding meta filter options (any, none, myself, etc)
	 *	@return array Filter options keyed by value=>display
	 */
	public function options( $p_project_id = null ) {
		if( null === $p_project_id ) {
			$p_project_id = helper_get_current_project();
		}
		$t_options = MantisUser::getDistinctUserOptionList( $this->field, $p_project_id ); 

		foreach( $t_options AS $t_key=>$t_user ) {
			$t_user->selected = in_array( $t_user->id, $this->filter_value );
			$t_options[$t_key] = array( 'value'=>$t_key, 'label'=>$t_user->name, 'selected'=>$t_user->selected );
		}
		return $t_options;
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
					$t_values[] = array( 'name'=>$this->field, 'value'=>string_attribute( META_FILTER_MYSELF ) );
					$t_labels[] = string_display( lang_get( 'myself' ) );
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
	 *  Checks the supplied value to see if it is a MYSELF value.
	 *  @param string $p_field_value - The value to check.
	 *  @return bool true for "MYSELF" values and false for others.
	 */
	public function isMyself( $p_value ) {
		return( META_FILTER_MYSELF == $p_value ? TRUE : FALSE );
	}

	public function query() {
		$t_filter = $this->bug_filter;
    	if( !$this->isAny() ) {
        	$t_clauses = array();
        	foreach( $this->filter_value as $t_current ) {
            	if( $this->isNone( $t_current ) ) {
                	array_push( $t_clauses, "0" );
            	} else {
                	if( $this->isMyself( $t_current ) ) {
                    	array_push( $t_clauses, $t_filter->filter_user_id );
                	} else {
                    	array_push( $t_clauses, $t_current );
                	}
            	}
        	}

        	if( 1 < count( $t_clauses ) ) {
            	$t_query = "( {$t_filter->tables['bug']}.{$this->field} IN (" . implode( ', ', $t_clauses ) . ") )";
        	} else {
            	$t_query = "( {$t_filter->tables['bug']}.{$this->field} = {$t_clauses[0]} )";
        	}

        	log_event( LOG_FILTERING, $this->field . ' query = ' . $t_query );
			$t_filter->addQueryElement('where_clauses', $t_query );
    	} else {
        	log_event( LOG_FILTERING, "no {$this->field} query" );
    	}
	}

	/**
	 *	@todo modify to sort by last name when configured
	 */	
	public function sortParams() {
		$t_filter = $this->bug_filter;
		$t_sort = $t_filter->getField( FILTER_PROPERTY_SORT );
		$t_pos = array_search( $this->field, $t_sort->sort_field );
		$t_dir = $t_sort->sort_direction[$t_pos];

		$t_show_realname = ( ON == config_get( 'show_realname') ? true : false );
		$t_sort_by_last_name = ( ON == config_get( 'sort_by_last_name' ) );

		if( $t_show_realname ) {
			$t_sort_field = 'realname';
		} else {
			$t_sort_field = 'username';
		}	

		$t_alias = $this->field . '_user';
		$t_filter->addQueryElement( 'select_clauses', "$t_alias.$t_sort_field");
		$t_join_string = "LEFT JOIN {$t_filter->tables['user']} $t_alias ON {$t_filter->tables['bug']}.{$this->field} = $t_alias.id ";
		$t_filter->addQueryElement( 'join_clauses', $t_join_string );

		$t_filter->addQueryElement( 'order_clauses', "$t_alias.$t_sort_field $t_dir" );
	}
}
