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
 *	Class to handle stored query preferences.
 *	@package MantisBT
 *	@subpackage classes
 */
class MantisStoredQueryPreferences {
	protected $user_id=0;
	protected $project_id=0;
	protected $myview_filters = array();
	protected $mylist_filters = array();
	protected $myview_defaults = array();

	public function __construct( $p_project_id=null, $p_user_id=null ) {
		if( is_null( $p_project_id ) ) {
			$this->project_id = helper_get_current_project();
		} else {
			$this->project_id = abs( $p_project_id );
		}
		if( is_null( $p_user_id ) ) {
			$this->user_id = auth_get_current_user_id();
		} else {
			$this->user_id = $p_user_id;
		}
		# load up the user preferences
		$this->myview_filters = config_get( 'myview_filters', $this->myview_filters, $this->user_id, $this->project_id );
		$this->mylist_filters = config_get( 'mylist_filters', $this->mylist_filters, $this->user_id, $this->project_id );
	}

	public function saveMyView() {
		if ( serialize( config_get( 'myview_filters', '', $this->user_id, $this->project_id ) ) !== serialize( $this->myview_filters ) ) {
			config_set( 'myview_filters', $this->myview_filters, $this->user_id, $this->project_id );
		}
	}
	public function saveMyList() {
		if ( serialize( config_get( 'mylist_filters', '', $this->user_id, $this->project_id ) ) !== serialize( $this->mylist_filters ) ) {
			config_set( 'mylist_filters', $this->mylist_filters, $this->user_id, $this->project_id );
		}
	}
	public function myViewHasFilter( $p_filter_id ) {
		if( is_array( $this->myview_filters ) ) {
			return in_array( $p_filter_id, $this->myview_filters );
		} else {
			return false;
		}
	}
	public function myListHasFilter( $p_filter_id ) {
		if( is_array( $this->mylist_filters ) ) {
			return in_array( $p_filter_id, $this->mylist_filters );
		} else {
			return false;
		}
	}
	public function addMyViewFilter( $p_query_id ) {
		$this->myview_filters[] = $p_query_id;
	}
	public function addMyListFilter( $p_query_id ) {
		$this->mylist_filters[] = $p_query_id;
	}
	public function removeMyViewFilter( $p_query_id ) {
		$t_key = array_search( $p_query_id, $this->myview_filters );	
		unset( $this->myview_filters[$t_key] );
	}
	public function removeMyListFilter( $p_query_id ) {
		$t_key = array_search( $p_query_id, $this->mylist_filters );	
		unset( $this->mylist_filters[$t_key] );
	}
}
