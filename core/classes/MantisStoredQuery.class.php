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
 *	Class to handle stored queries.
 *	@package MantisBT
 *	@subpackage classes
 */
class MantisStoredQuery {
	protected $id = 0;
	protected $user_id=0;
	protected $project_id;
	protected $original_project_id; # used to update a filter with a different project
	protected $is_public = false;
	protected $access_level = null;
	protected $name='';
	protected $filter_string;
	protected $overwrite = false;
	protected $all_projects = false;
	protected $temporary = false;

	protected static $_cacheById = array();
	protected static $_cacheNamesById = array();
	/**
	 *	A list of public, private, and default ids (may be a string in case of a default filter) available to the current user by project
	 */
	protected static $_cacheAvailableByProjectByUser = array();
	protected static $_cacheDefaultsByName = array();
	/**
	 *	A list of current filters by project and user.
	 */
	public static $_cacheIdsByProjectByUser = array();

	public function __get( $p_field ) {
		switch( $p_field ) {
			case 'name':
				return string_display( $this->name );
			break;
			default:
				return $this->$p_field;
			break;
		}
	}

	public function __set( $p_field, $p_value ) {
		switch( $p_field ) {
			default:
				$this->$p_field = $p_value;
			break;
		}
	}

	/**
	 *	Grab post/get vars, validate and assign as necessary.
	 *	@param bool $p_is_named True if user is saving a named filter. False if filter is a 'current' filter
	 */
	public function process( $p_is_named = false ) {
		$this->is_public = false;
		$this->overwrite = true;
		$this->original_project_id = $this->project_id;
		$this->filter_string = gpc_get_string('filter_string', $this->filter_string );

		if( !$this->user_id || !$p_is_named ) {
			# overwrite the user because this is a 'current' filter.
			$this->user_id = auth_get_current_user_id();
		}
		$t_project_id = helper_get_current_project();

		if( $p_is_named ) {
			$this->name = strip_tags( gpc_get_string('query_name', $this->name ) );
			# We can't have a blank name
			if ( is_blank( $this->name ) ) {
				trigger_error( ERROR_FILTER_EMPTY_NAME, ERROR );
			}

			# mantis_filters_table.name has a length of 64. Not allowing longer.
			if( utf8_strlen( $this->name ) > 64 ) {
				trigger_error( ERROR_FILTER_NAME_TOO_LONG, ERROR );
			}

			$this->overwrite = gpc_get_bool('overwrite_query' );
			if( $this->overwrite ) {
				$this->id = gpc_get_int('source_query_id', $this->id );
			} else {
				$this->id = 0;

				# If not overwriting, make sure they don't already have a query with the same name
				$t_stored_queries = self::getAvailable();
				if( count( $t_stored_queries ) > 0 ) {
					foreach( $t_stored_queries as $t_id => $t_query_arr ) {
						# default filters have a string key and can be ignored for this
						if( is_numeric( $t_id ) ) {
							foreach( $t_query_arr AS $t_name ) {
								if ( $this->name === $t_name ) {
									trigger_error( ERROR_FILTER_NAME_DUPLICATE, ERROR );
								}
							}
						}
					}
				}
			}
			$this->all_projects = gpc_get_bool('all_projects' );
			if( $this->all_projects ) {
				$this->project_id = ALL_PROJECTS;
			} else {
				$this->project_id = $t_project_id;
			}

			# ensure that we're not making this filter public if we're not allowed
			if( access_has_project_level( config_get( 'stored_query_create_shared_threshold' ) ) ) {
				$this->is_public = gpc_get_bool('is_public' );
				# Handle the access level
				if( $this->is_public ) {
					$this->access_level = gpc_get_int('access_level', $this->access_level );
				}
			}
		} else {
			# don't overwrite an existing stored query if this is a current filter
			$this->name = '';
			$this->project_id = $t_project_id;
			$this->original_project_id = $t_project_id;
			$this->id = self::getCurrentFilterIdByProjectByUser( $this->project_id, $this->user_id );
		}
	}

	/**
	 *	Add filter to the database for the current user
	 *	@return int
	 */
	public function save() {
		# don't save temporary filters at all
		if( $this->temporary ) {
			return false;
		}
		if( $this->name == '' || ( $this->name != '' && self::canUpdate( $this->id ) ) ) {
			if( $this->name == '' && !$this->id ) {
				$this->id = self::getCurrentFilterIdByProjectByUser( $this->project_id, $this->user_id );
			}

			# update the stored query info
			$t_filter_table = db_get_table( 'filters' );

			if( $this->id ) {
				# UPDATE
				if( $this->all_projects ) {
					$t_set[] = "project_id=" . db_param();
					$t_params[] = ALL_PROJECTS;
				} else {
					$t_set[] = "project_id=" . db_param();
					$t_params[] = ( $this->name == '' ?  ( $this->project_id * -1 ) : $this->project_id );
				}

				$t_set[] = "name=" . db_param();
				$t_params[] = $this->name;

				$t_set[] = "access_level=" . db_param();
				$t_params[] = $this->access_level;

				$t_set[] = "is_public=" . db_param();
				$t_params[] = $this->is_public;

				if( $this->overwrite ) {
					$t_set[] = "filter_string=" . db_param();
					$t_params[] = $this->filter_string;
				}

				$t_params[] = $this->id;
				$t_params[] = $this->user_id;
				$t_params[] = ( $this->name == '' ?  ( $this->original_project_id * -1 ) : $this->original_project_id );# must be the original project id!
				$t_query = "UPDATE $t_filter_table SET " . join( ", ", $t_set ) . " WHERE id=" . db_param() . " AND user_id=" . db_param() . " AND project_id=" . db_param();
			} else {
				#INSERT
				$t_fields[] = "project_id";
				if( $this->all_projects ) {
					$t_params[] = ALL_PROJECTS;
				} else {
					$t_params[] = ( $this->name == '' ?  ( $this->project_id * -1 ) : $this->project_id );
				}

				$t_fields[] = "user_id";
				$t_params[] = $this->user_id;

				$t_fields[] = "name";
				$t_params[] = $this->name;

				$t_fields[] = "is_public";
				$t_params[] = $this->is_public;

				$t_fields[] = "access_level";
				$t_params[] = $this->access_level;

				$t_fields[] = "filter_string";
				$t_params[] = $this->filter_string;

				$t_values = array_fill( 0, count( $t_fields ), db_param() );
				$t_query = "INSERT INTO $t_filter_table ( " . join( ", ", $t_fields ) . " ) VALUES ( " . join( ', ', $t_values ) . " )";
			}
			$t_result = db_query_bound( $t_query, $t_params );

			if( !$this->id ) {
				$this->id = db_insert_id( $t_filter_table );
				# update with the id
				$t_filter_arr = filter_deserialize( $this->filter_string );
				$t_filter_arr['_source_query_id'] = $this->id;
				$this->filter_string = self::getCookie( $t_filter_arr );
				$this->overwrite = true;
				$this->original_project_id = $this->project_id;
				$this->save();
			}
			if( $this->name != '') {
				# the filter was successfully updated so set the preferences for it now
				# we need to do it here so that we have a valid id for new stored filters
				$t_prefs = new MantisStoredQueryPreferences( $this->project_id );
				$f_query_in_list = gpc_get_bool( 'mylist' );
				$f_query_in_myview = gpc_get_bool( 'myview' );

				if( $f_query_in_list && !$t_prefs->myListHasFilter( $this->id ) ) {
					$t_prefs->addMyListFilter( $this->id );
				} else if( !$f_query_in_list && $t_prefs->myListHasFilter( $this->id ) ) {
					$t_prefs->removeMyListFilter( $this->id );
				}
				if( $f_query_in_myview && !$t_prefs->myViewHasFilter( $this->id ) ) {
					$t_prefs->addMyViewFilter( $this->id );
				} else if( !$f_query_in_myview && $t_prefs->myViewHasFilter( $this->id ) ) {
					$t_prefs->removeMyViewFilter( $this->id );
				}
				$t_prefs->saveMyList();
				$t_prefs->saveMyView();

				# update cached filter name
				self::$_cacheNamesById[$this->id] = $this->name;
			}

			# update cached id for current filter for project and user
			self::$_cacheIdsByProjectByUser[$this->user_id][abs( $this->project_id )] = $this->id;
			return $this->id;
		} else {
			trigger_error( ERROR_FILTER_STORE_ERROR, ERROR );
		}
	}

	/**
	 *  This function returns a boolean indicating whether
	 *  or not the user has permission to see this filter
	 *  @param int $p_user_id
	 *  @return boolean
	 */
	public function hasAccess( $p_user_id=null ) {
		if( null === $p_user_id ) {
			$t_user_id = auth_get_current_user_id();
		} else {
			$t_user_id = $p_user_id;
		}

		$t_has_use_permission = access_has_project_level( config_get( 'stored_query_use_threshold', $this->project_id, $t_user_id ) );
		if( $t_has_use_permission && ( $this->user_id == $t_user_id || ( $this->is_public && access_has_project_level( $this->access_level, $this->project_id, $t_user_id ) ) ) ) {
			# the user can always access their own
			return true;
		} else if( is_blank( $this->name ) && $this->user_id == $t_user_id ) {
			# it's a current filter owned by the user
			return true;
		}
		# the user does not have required permission
		return false;
	}

	/**
	 *	User must have permissions to create a stored query for the project.
	 *	User may update if the stored query is public and the user created the query or the user is an administrator.
	 *	Otherwise the user must have created the query in order to update it.
	 *	@return bool True if user can update the filter. False if they can't
	 */
	public static function canUpdate( $p_filter_id, $p_user_id=null ) {
		if( null === $p_user_id ) {
			$t_user_id = auth_get_current_user_id();
		} else {
			$t_user_id = $p_user_id;
		}

		if( user_is_administrator( $t_user_id ) ) {
			return true;
		}

		$t_filter = self::getById( $p_filter_id );

		$t_has_create_permission = access_has_project_level( config_get( 'stored_query_create_threshold' ) );
		if( $t_has_create_permission ) {
			if( ( $t_filter->user_id == $t_user_id ) || ( !$t_filter->user_id ) ) {
				# the user can always access their own whether it's public or not
				#  or the current user is creating a new filter
				return true;
			} else {
				# non administrator users can only edit their own filters
				return false;
			}
		} else if( is_blank( $t_filter->name ) && ( $t_filter->user_id == $t_user_id || !$t_filter->user_id ) ) {
			# it's a current filter owned by the user or a new current filter is being saved
			return true;
		}

		# the user does not have required permission
		return false;
	}

	/**
	 *	Static functions querying the database hand off individual rows to this function
	 *	to handle populating the data members for the object instance.
	 *	@param array An associative array of fields from the database. Must be one record.
	 */
	private function setFields( $t_row ) {
		$this->id = $t_row['id'];
		$this->user_id = $t_row['user_id'];
		$this->project_id = abs( $t_row['project_id'] );
		$this->is_public = $t_row['is_public'];
		$this->access_level = $t_row['access_level'];
		$this->name = $t_row['name'];
		$this->filter_string = $t_row['filter_string'];
	}

	/**
	 *	Return a url to apply the filter on the view all bug page
	 *	@return string
	 */
	public function getUrl() {
		$t_filter_arr = filter_deserialize( $this->filter_string );
		if( $this->temporary ) {
			return filter_get_temporary_url( $t_filter_arr );
		} else {
			return 'view_all_set.php?type=3&source_query_id=' . $this->id;
		}
	}

	/**
	 *	Serialize the arr into a filter cookie string
	 *	@param array $p_filter_arr
	 *	@return string The serialized cookie string
	 */
	public static function getCookie( $p_filter_arr ) {
		$t_cookie_version = config_get( 'cookie_version' );
		$t_settings_serialized = serialize( $p_filter_arr );
		$t_cookie_string = $t_cookie_version . '#' . $t_settings_serialized;
		return $t_cookie_string;
	}

	public static function getNamedDefault( $p_filter_name ) {
		if( !array_key_exists( $p_filter_name, self::$_cacheDefaultsByName ) ) {
			$t_current_user_id = auth_get_current_user_id();
			$t_project_id = helper_get_current_project();

			# Check permissions to access default filters
			if( current_user_is_anonymous() && in_array( $p_filter_name, array('reported', 'feedback', 'verify', 'monitored', 'assigned' ) ) ) {
				self::$_cacheDefaultsByName[$p_filter_name] = false;
				return false;
			} else if( in_array( $p_filter_name, array('reported', 'feedback', 'verify' ) ) && !access_has_project_level( config_get( 'report_bug_threshold' ), $t_project_id, $t_current_user_id ) ) {
				self::$_cacheDefaultsByName[$p_filter_name] = false;
				return false;
			} else if ( $p_filter_name == 'monitored' && ( !access_has_project_level( config_get( 'monitor_bug_threshold' ), $t_project_id, $t_current_user_id ) ) ) {
				self::$_cacheDefaultsByName[$p_filter_name] = false;
				return false;
			} else if ( $p_filter_name == 'assigned' && ( !access_has_project_level( config_get( 'handle_bug_threshold' ), $t_project_id, $t_current_user_id ) ) ) {
				# don't display "Assigned to Me" bugs to users that bugs can't be assigned to
				self::$_cacheDefaultsByName[$p_filter_name] = false;
				return false;
			}

			# use the default as the base for each filter, then only override specific fields
			$t_filter = filter_get_default();
			$t_update_bug_threshold = config_get( 'update_bug_threshold' );
			$t_bug_resolved_status_threshold = config_get( 'bug_resolved_status_threshold' );
			$t_hide_status_default = config_get( 'hide_status_default' );
			$t_default_show_changed = config_get( 'default_show_changed' );
			switch( $p_filter_name ) {
				case 'assigned':
					$t_filter[FILTER_PROPERTY_HANDLER_ID] = array( '0' => $t_current_user_id );
					$t_filter[FILTER_PROPERTY_HIDE_STATUS] = array( '0' => $t_bug_resolved_status_threshold );
				break;
				case 'recent_mod':
					$t_filter[FILTER_PROPERTY_HIDE_STATUS] = array( '0' => META_FILTER_NONE );
				break;
				case 'reported':
					$t_filter[FILTER_PROPERTY_REPORTER_ID] = array( '0' => $t_current_user_id );
				break;
				case 'feedback':
					$t_filter[FILTER_PROPERTY_STATUS] = array( '0' => config_get( 'bug_feedback_status' ) );
					$t_filter[FILTER_PROPERTY_REPORTER_ID] = array( '0' => $t_current_user_id  );
				break;
				case 'verify':
					$t_filter[FILTER_PROPERTY_STATUS] = array( '0' => $t_bug_resolved_status_threshold );
					$t_filter[FILTER_PROPERTY_REPORTER_ID] = array( '0' => $t_current_user_id );
				break;
				case 'resolved':
					$t_filter[FILTER_PROPERTY_STATUS] = array( '0' => $t_bug_resolved_status_threshold );
					$t_filter[FILTER_PROPERTY_HIDE_STATUS] = array( '0' => META_FILTER_NONE );
				break;
				case 'unassigned':
					$t_filter[FILTER_PROPERTY_HANDLER_ID] = array( '0' => META_FILTER_NONE );
				break;
				case 'monitored':
					$t_filter[FILTER_PROPERTY_MONITOR_USER_ID] = array( '0' => $t_current_user_id );
				break;
				case 'my_comments':
					$t_filter[FILTER_PROPERTY_NOTE_USER_ID] = array( '0' => META_FILTER_MYSELF );
				break;
			}
			$t_filter_string = self::getCookie( $t_filter );
			$t_named_filter = new MantisStoredQuery();
			$t_data = array( 'user_id'=>$t_current_user_id, 'project_id'=>$t_project_id, 'is_public'=>true,'name'=>lang_get( 'my_view_title_' . $p_filter_name ), 'filter_string'=>$t_filter_string, 'access_level'=>'default' );
			$t_named_filter->setFields( $t_data );
			$t_named_filter->temporary = true;
			self::$_cacheDefaultsByName[$p_filter_name] = $t_named_filter;
		}
		return self::$_cacheDefaultsByName[$p_filter_name];
	}

	/**
	 *	@param int $p_project_id
	 *	@param int $p_user_id
	 *	@return int
	 */
	public static function getCurrentFilterIdByProjectByUser( $p_project_id, $p_user_id=null ) {
		if( null === $p_user_id ) {
			$c_user_id = auth_get_current_user_id();
		} else {
			$c_user_id = db_prepare_int( $p_user_id );
		}

		if( !array_key_exists( $c_user_id, self::$_cacheIdsByProjectByUser ) ||
				( array_key_exists( $c_user_id, self::$_cacheIdsByProjectByUser ) &&
				  !array_key_exists( $p_project_id, self::$_cacheIdsByProjectByUser[$c_user_id] ) ) ) {

			$t_filters_table = db_get_table( 'filters' );
			$c_project_id = db_prepare_int( $p_project_id );
			$c_project_id = $c_project_id * -1;

			# we store current filters for each project with a special project index
			$t_query = "SELECT *
						FROM $t_filters_table
						WHERE user_id=" . db_param() . "
						AND project_id=" . db_param() . "
						AND name=" . db_param();
			$t_result = db_query_bound( $t_query, array( $c_user_id, $c_project_id, '' ) );

			if( db_num_rows( $t_result ) > 0 ) {
				$t_row = db_fetch_array( $t_result );
				self::$_cacheIdsByProjectByUser[$c_user_id][$p_project_id] = $t_row['id'];
			} else {
				return null;
			}
		}

		return self::$_cacheIdsByProjectByUser[$c_user_id][$p_project_id];
	}

	/**
	 *	Get the current filter based on the id stored in the session.
	 *	@return mixed returns false if no current id is found, a MantisStoredQuery object if found, or null if no permission
	 *	@see MantisStoredQuery::getById
	 */
	public static function getCurrent() {
		$f_id = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );
		if( $f_id != '' ) {
			return self::getById( $f_id );
		} else {
			return false;
		}
	}

	/**
	 *	@param int $p_id The filter id
	 *	@param int $p_user_id
	 *	@return mixed MantisStoredQuery object, null if no access, false if not found
	 */
	public static function getById( $p_id, $p_user_id=null ) {
		if( null === $p_user_id ) {
			$t_user_id = auth_get_current_user_id();
		} else {
			$t_user_id = $p_user_id;
		}

		if( !array_key_exists( $p_id, self::$_cacheById ) ) {
			#  load it up
			$t_filter_table = db_get_table( 'filters' );

			$t_query = "SELECT * FROM $t_filter_table WHERE id=" . db_param();
			$t_result = db_query_bound( $t_query, array( $p_id ) );

			if ( db_num_rows( $t_result ) < 1 ) {
				return false;
			}

			$t_row = db_fetch_array( $t_result );
			$t_stored_query = new MantisStoredQuery();
			$t_stored_query->setFields( $t_row );

			self::$_cacheById[$p_id] = $t_stored_query;
		}
		if( self::$_cacheById[$p_id]->hasAccess($t_user_id) ) {
			return self::$_cacheById[$p_id];
		} else {
			return null;
		}
	}

	/**
	 *	Get an associative array of filter names, ids, and access level by project
	 *	@param int $p_project_id
	 *	@param int $p_user_id
	 *	@return array An array of filter names.  May be empty.
	 */
	public static function getAvailable( $p_project_id=null, $p_user_id=null ) {
		$t_filters_table = db_get_table( 'filters' );
		$t_filters = array();

		if( null === $p_project_id ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = db_prepare_int( $p_project_id );
		}

		if( null === $p_user_id ) {
			$t_user_id = auth_get_current_user_id();
		} else {
			$t_user_id = db_prepare_int( $p_user_id );
		}

		if( !array_key_exists( $t_project_id, self::$_cacheAvailableByProjectByUser )
				|| ( array_key_exists( $t_project_id, self::$_cacheAvailableByProjectByUser ) && !array_key_exists( $t_user_id, self::$_cacheAvailableByProjectByUser[$t_project_id] ) ) ) {
			$t_user_access_level = user_get_access_level( $t_user_id, $t_project_id );

			# If the user doesn't have access rights to stored queries, just return
			if( $t_project_id != ALL_PROJECTS && !access_has_project_level( config_get( 'stored_query_use_threshold' ), $t_project_id, $t_user_id ) ) {
				self::$_cacheAvailableByProjectByUser[$t_project_id][$t_user_id] = array();
				return self::$_cacheAvailableByProjectByUser[$t_project_id][$t_user_id];
			}

			# Get the list of available queries. By sorting such that public queries are
			# first, we can override any query that has the same name as a private query
			# with that private one
			$t_query = "SELECT id, name, access_level FROM $t_filters_table
					WHERE ";

			$t_query .= 'name !=' . db_param();
			$t_params[] = '';

			# if All Projects is selected, just display all filters.  Otherwise, display only filters
			# for the current project and those for All Projects.
			if( $t_project_id != 0 ) {
				$t_query .= ' AND ( project_id=' . db_param() . ' OR project_id= ' . db_param() . ' )';
				$t_params[] = abs( $t_project_id );
				$t_params[] = ALL_PROJECTS;
			}

			$t_query .= ' AND ( ( is_public=' . db_param() ." AND access_level<=" . db_param() . ' )';
			$t_params[] = true;
			$t_params[] = $t_user_access_level;


			$t_query .= ' OR ( user_id=' . db_param();
			$t_params[] = $t_user_id;
			$t_query .= " AND access_level IS NULL ) )";

			$t_query .= " ORDER BY is_public DESC, access_level ASC, name ASC";
			$t_result = db_query_bound( $t_query, $t_params );
			$t_query_count = db_num_rows( $t_result );

			for( $i = 0; $i < $t_query_count;$i++ ) {
				$t_row = db_fetch_array( $t_result );
				$t_row['access_level'] = ( is_null( $t_row['access_level'] ) ? 0 : $t_row['access_level'] );
				$t_filters[$t_row['id']] = $t_row;
			}

			ksort( $t_filters );
			$t_default_filters = config_get( 'my_view_boxes' );
			asort ($t_default_filters );
			reset ($t_default_filters );
			foreach( $t_default_filters AS $t_id => $t_pos ) {
				if( $t_pos ) {
					$t_filters[$t_id] = array( 'id'=>$t_id, 'name'=>lang_get( 'my_view_title_' . $t_id ), 'access_level'=>'default' );
				}
			}
			self::$_cacheAvailableByProjectByUser[$t_project_id][$t_user_id] = $t_filters;
		}
		return self::$_cacheAvailableByProjectByUser[$t_project_id][$t_user_id];
	}

	/**
	 *	Return an array of filters grouped by access level
	 *	@param int $p_project_id The project
	 *	@return array An array of filter names grouped by access level and indexed by id
	 *	@see MantisStoredQuery::getAvailable
	 */
	public static function getAvailableByAccessLevel( $p_project_id = null ) {
		if( null === $p_project_id ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = db_prepare_int( $p_project_id );
		}
		$t_filters = array();
		$t_available = self::getAvailable( $t_project_id );
		if( count( $t_available ) > 0 ) {
			foreach( $t_available AS $t_row ) {
				$t_filters[$t_row['access_level']][$t_row['id']] = $t_row['name'];
			}
		}
		return $t_filters;
	}

	/**
	 *	Get an array of filter names available for the project.  The keys should be the id
	 *	@param int $p_project_id The project
	 *	@return array Array of filter names indexed by id ( may be a string for default filters )
	 */
	public static function getAvailableById( $p_project_id=null ) {
		if( null === $p_project_id ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = db_prepare_int( $p_project_id );
		}
		$t_filters = array();
		$t_available = self::getAvailable( $t_project_id );
		if( count( $t_available ) > 0 ) {
			foreach( $t_available AS $t_row ) {
				$t_filters[$t_row['id']] = $t_row['name'];
			}
		}
		return $t_filters;
	}

	/**
	 *	Return whether or not a filter is available to the user for the given project.
	 *	@param mixed $p_filter_id An id or string to identify the filter
	 *	@param int $p_project_id The project id
	 *	@bool
	 */
	public static function isFilterAvailable( $p_filter_id, $p_project_id = null ) {
		$t_is_available = false;
		if ( null === $p_project_id ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = $p_project_id;
		}

		$t_available = self::getAvailable( $t_project_id );

		return array_key_exists( $p_filter_id, $t_available );
	}

	/**
	 *	Return an array of filters grouped by access level for display on the my view page.
	 *	@param int $p_project_id The project
	 *	@param int $p_user_id The user
	 *	@see MantisStoredQuery::isFilterAvailable
	 *	@see MantisStoredQuery::getNamedDefault
	 *	@see MantisStoredQuery::getById
	 */
	public static function getMyViewFilters( $p_project_id=null, $p_user_id=null ) {
		if ( null === $p_project_id ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = $p_project_id;
		}

		if ( null === $p_user_id ) {
			$t_user_id = auth_get_current_user_id();
		} else {
			$t_user_id = $p_user_id;
		}

		$t_myview_ids = config_get( 'myview_filters', array(), $t_user_id, $t_project_id );
		if( is_array( $t_myview_ids ) && count( $t_myview_ids ) > 0 ) {
			foreach( $t_myview_ids AS $t_id ) {
				$t_sort_arr[$t_id] = self::getNameById( $t_id );
			}
		}
		if( is_array( $t_sort_arr ) ) {
			natcasesort( $t_sort_arr );
		}
		$t_filters = array();
		if( count( $t_sort_arr ) > 0 ) {
			foreach( $t_sort_arr AS $t_key=>$t_name ) {
				if( self::isFilterAvailable( $t_key, $t_project_id ) ) {
					if( is_numeric( $t_key ) ) {
						$t_filter = self::getById( $t_key );
					} else {
						$t_filter = self::getNamedDefault( $t_key );
					}
					if( $t_filter ) {
						$t_filters[$t_key] = $t_filter;
					}
				}
			}
		} else {
			# load up the defaults
			$t_available = self::getAvailable( $t_project_id );
			if( count( $t_available ) > 0 ) {
				foreach( $t_available AS $t_key=>$t_name ) {
					if( !is_numeric( $t_key ) ) {
						$t_filter = self::getNamedDefault( $t_key );
						if( $t_filter ) {
							$t_filters[$t_key] = $t_filter;
						}
					}
				}
			}
		}
		return $t_filters;
	}

	/**
	 *	Return an array of filters to display in the menu list on the bug filter.
	 *	@param int $p_project_id The project
	 *	@param int $p_user_id The user
	 *	@see MantisStoredQuery::isFilterAvailable
	 */
	public static function getListForMenu( $p_project_id = null, $p_user_id = null ) {
		if ( null === $p_project_id ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = $p_project_id;
		}

		if ( null === $p_user_id ) {
			$t_user_id = auth_get_current_user_id();
		} else {
			$t_user_id = $p_user_id;
		}
		$t_menu_ids = config_get( 'mylist_filters', array(), $t_user_id, $t_project_id );
		$t_filters = array();
		if( count( $t_menu_ids ) > 0 ) {
			foreach( $t_menu_ids AS $t_key ) {
				if( self::isFilterAvailable( $t_key, $t_project_id ) ) {
					# only use actual stored queries. no defaults in the menu
					if( is_numeric( $t_key ) ) {
						$t_filter = self::getById( $t_key );
						$t_filters[$t_key] = $t_filter->name;
					}
				}
			}
		} else {
			# load up the whole list (minus defaults)
			$t_available = self::getAvailable( $t_project_id );
			if( count( $t_available ) > 0 ) {
				foreach( $t_available AS $t_key=>$t_name ) {
					if( is_numeric( $t_key ) ) {
						$t_filter = self::getById( $t_key );
						#$t_filters[$t_filter->access_level][$t_key] = $t_filter;
						$t_filters[$t_key] = $t_filter->name;
					}
				}
			}
		}
		if( is_array( $t_filters ) ) {
			$t_filter = natcasesort( $t_filters );
		}
		return $t_filters;
	}

	/**
	 *  Query for the filter name using the filter id
	 *	@param int $p_filter_id
	 *	@return string
	 */
	public static function getNameById( $p_filter_id ) {
		if( !array_key_exists( $p_filter_id, self::$_cacheNamesById ) ) {
			if( is_numeric( $p_filter_id ) ) {
				$t_filters_table = db_get_table( 'filters' );

				$t_query = 'SELECT * FROM ' . $t_filters_table . ' WHERE id=' . db_param();
				$t_result = db_query_bound( $t_query, array( $p_filter_id ) );

				if( db_num_rows( $t_result ) > 0 ) {
					$t_row = db_fetch_array( $t_result );
					if( $t_row['user_id'] != auth_get_current_user_id() ) {
						if( $t_row['is_public'] != true ) {
							return null;
						}
					}
					self::$_cacheNamesById[$p_filter_id] = $t_row['name'];
				} else {
					return null;
				}
			} else {
				self::$_cacheNamesById[$p_filter_id] = lang_get( 'my_view_title_' . $p_filter_id );
			}
		}

		return self::$_cacheNamesById[$p_filter_id];
	}

	/**
	 *	Check if the current user has permissions to delete the stored query
	 *	@param $p_filter_id
	 *	@return bool
	 */
	public static function canDelete( $p_filter_id ) {
		$t_user_id = auth_get_current_user_id();

		# Administrators can delete any filter
		if( user_is_administrator( $t_user_id ) ) {
			return true;
		}
		$t_filter = self::getById( $p_filter_id );
		if( $t_filter && $t_filter->user_id==$t_user_id ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  Delete the filter specified by $p_filter_id
	 *	@param $p_filter_id
	 *	@return bool
	 */
	public static function delete( $p_filter_id ) {
		$t_filters_table = db_get_table( 'filters' );
		$c_filter_id = db_prepare_int( $p_filter_id );
		$t_user_id = auth_get_current_user_id();

		if( !self::canDelete( $c_filter_id  ) ) {
			return false;
		}

		$t_query = 'DELETE FROM ' . $t_filters_table . ' WHERE id=' . db_param();
		$t_result = db_query_bound( $t_query, Array( $c_filter_id ) );

		if( db_affected_rows( $t_result ) > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 *	Delete all the unnamed filters. Requires administrator permission
	 */
	public static function deleteAllCurrent() {
		if( user_is_administrator( auth_get_current_user_id() ) ) {
			$t_filters_table = db_get_table( 'filters' );
			$t_all_id = ALL_PROJECTS;

			$t_query = "DELETE FROM $t_filters_table
					WHERE project_id<=" . db_param() . "
					AND name=" . db_param();
			$t_result = db_query_bound( $t_query, array( $t_all_id, '' ) );
		} else {
			return false;
		}
	}
}
