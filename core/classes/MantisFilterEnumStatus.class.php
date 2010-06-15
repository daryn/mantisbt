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
 * Class that implements filter functionality for enumerated lists
 * @package MantisBT
 * @subpackage classes
 */
class MantisFilterEnumStatus extends MantisFilterEnum {
	/**
	 *	Get and normalize any POST/GET value(s) sent for this field
	 *	Assign the result to the value member if no value is sent, use
	 *	the existing value as the default.  
	 */
	public function processGPC() {
		$t_is_hide_status_submitted = gpc_isset( FILTER_PROPERTY_HIDE_STATUS );
		$t_is_status_submitted = gpc_isset( FILTER_PROPERTY_STATUS );
		$t_view_type_field = $this->bug_filter->getField( '_view_type' );
		$t_current_value = $this->filter_value;
		parent::processGPC();
		$t_new_value = $this->filter_value;
		$t_view_type = $t_view_type_field->filter_value;
		if( $t_view_type == 'simple' ) {
			$t_hide_status = $this->bug_filter->getField( FILTER_PROPERTY_HIDE_STATUS );
		}
		if( $t_view_type == 'simple' && count( $t_current_value ) > 1 && in_array( $t_new_value, $t_current_value ) ) {
			# the filter changed from advanced to simple and the value is in the previous set
			# since we may not know yet whether hide status is changed, just leave the value as 
			# the old set and do not mark as changed. validation will catch and override if hide 
			# status has really changed.
			$this->filter_value = $t_current_value;
			$this->__set( 'changed', false );
		} else if( $t_current_value != $t_new_value ) {
			$this->__set( 'changed', true );
		} else {
			$this->__set( 'changed', false );
		}
	}

	public function validate() {
		parent::validate();

		/**
		 *  Remove any statuses that should be excluded by the hide_status field
		 */
		$t_view_type = $this->bug_filter->getField( '_view_type' );
		$t_hide_status = $this->bug_filter->getField( FILTER_PROPERTY_HIDE_STATUS );

		if( $t_view_type->filter_value == "advanced" ) {
			if( $t_hide_status->filter_value > 0 && $this->isAny() ) {
				$t_statuses = MantisEnum::getValues( config_get( 'status_enum_string' ) );
				if( is_array( $t_statuses ) ) {
					foreach( $t_statuses as $t_key=>$t_val ) {
						if( $t_val < $t_hide_status->filter_value ) {
							$t_keep_statuses[$t_key] = $t_val;
						}
        			}
					$this->filter_value = $t_keep_statuses;
				}
    		}
		} else if( ( $t_hide_status->changed === true || count( $this->filter_value ) > 1 ) && !$t_hide_status->isNone() ) {
			$this->filter_value = array( META_FILTER_ANY );
		}

		return true;
	}

	/**
	 *	Modified usage of status/hide status.  Previously the filter would try to resolve differences 
	 *	between status and hide status fields.  After consideration it seems a better solution is to 
	 *	allow the hide status field on the advanced filter as a single select dropdown and only make it
	 *	active if the status field is any.  This way the two fields are mutually exclusive.	
	 *	 The previous query also ignored statuses which are no longer available.  This is sub-optimal as 
	 *	any bugs with old statuses could never show up in any query and may not be resolved.  Leaving old 
	 *	statuses in the query would allow a user to access the bug and update to a valid status.
	 */
	public function query() {
		$t_filter = $this->bug_filter;
		# show / hide status
		# take a list of all available statuses then remove the ones that we want hidden, then make sure
		# the ones we want shown are still available
		$t_desired_statuses = array();
		$t_available_statuses = MantisEnum::getValues( config_get( 'status_enum_string' ) );

		# if status isAny then don't do anything. let hide status take care of it.	
		if( !$this->isAny() ) {
			$t_clauses = array();
			foreach( $this->filter_value as $t_status ) {
				array_push( $t_clauses, $t_status );
			}
			if( 1 < count( $t_clauses ) ) {
				$t_where_tmp = array();
				foreach( $t_clauses as $t_clause ) {
					$t_where_tmp[] = db_param();
					$t_filter->addQueryElement( 'where_params', $t_clause );
				}
				$t_filter->addQueryElement( 'where_clauses', "( {$t_filter->tables['bug']}.status in (" . implode( ', ', $t_where_tmp ) . ") )" );
			} else {
				$t_filter->addQueryElement( 'where_params', $t_clauses[0] );
				$t_filter->addQueryElement( 'where_clauses', "( {$t_filter->tables['bug']}.status=" . db_param() . " )" );
        	}
		}
	}
}
