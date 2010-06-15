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
 * Mantis Bug Filter
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

class MantisBugFilter {
	private static $field_classes = array(
		'_version'=>'MantisFilterCookieVersion',
		'_view_type'=>'MantisFilterViewType',
		'_source_query_id'=>'MantisFilterInt',
		'show_version_like'=>'MantisFilterString',

		FILTER_PROPERTY_SEARCH=>'MantisFilterSearch',
		FILTER_PROPERTY_HIGHLIGHT_CHANGED=>'MantisFilterChanged',
		FILTER_PROPERTY_STICKY=>'MantisFilterSticky',
		FILTER_PROPERTY_PROJECT_ID=>'MantisFilterProject',
		FILTER_PROPERTY_CATEGORY_ID=>'MantisFilterCategory',
		FILTER_PROPERTY_SORT=>'MantisFilterSort',
		FILTER_PROPERTY_ISSUES_PER_PAGE=>'MantisFilterPerPage',
		FILTER_PROPERTY_DATE_SUBMITTED=>'MantisFilterDate',
		FILTER_PROPERTY_DUE_DATE=>'MantisFilterDate',
		FILTER_PROPERTY_LAST_UPDATED=>'MantisFilterDate',
		FILTER_PROPERTY_RELATIONSHIP_TYPE=>'MantisFilterRelationship',
		FILTER_PROPERTY_RELATIONSHIP_BUG=>'MantisFilterString',
		FILTER_PROPERTY_TAG_STRING=>'MantisFilterTags',
		FILTER_PROPERTY_REPORTER_ID=>'MantisFilterUserReporter',
		FILTER_PROPERTY_HANDLER_ID=>'MantisFilterUserHandler',
		FILTER_PROPERTY_NOTE_USER_ID=>'MantisFilterUserNoteBy',
		FILTER_PROPERTY_MONITOR_USER_ID=>'MantisFilterUserMonitor',
		FILTER_PROPERTY_STATUS=>'MantisFilterEnumStatus',
		FILTER_PROPERTY_HIDE_STATUS=>'MantisFilterHideStatus',
		FILTER_PROPERTY_SEVERITY=>'MantisFilterEnum',
		FILTER_PROPERTY_RESOLUTION=>'MantisFilterEnum',
		FILTER_PROPERTY_PRIORITY=>'MantisFilterEnumPriority',
		FILTER_PROPERTY_VIEW_STATE=>'MantisFilterEnumViewState',
		FILTER_PROPERTY_VERSION=>'MantisFilterVersion',
		FILTER_PROPERTY_TARGET_VERSION=>'MantisFilterVersion',
		FILTER_PROPERTY_FIXED_IN_VERSION=>'MantisFilterVersion',
		FILTER_PROPERTY_BUILD=>'MantisFilterBuild',
		FILTER_PROPERTY_PROFILE_ID=>'MantisFilterProfile',
		FILTER_PROPERTY_PLATFORM=>'MantisFilterPlatform',
		FILTER_PROPERTY_OS=>'MantisFilterOS',
		FILTER_PROPERTY_OS_BUILD=>'MantisFilterOSBuild',
	);

	private $id = 0;
	private $user_id = 0;
	private $project_id = 0;
	private $is_public = false;
	private $name = '';
	private $filter_string = '';
 
	private $tables = array();
	private $fields = array();
	private $filter_user_id = null;

	private $page_number;
	private $page_count;
	private $bug_count;

	private $select_clauses = array();
	private $from_clauses = array();	
	private $join_clauses = array();	
	private $where_clauses = array();	
	private $where_params = array();	
	private $order_clauses = array();	
	
	private static $current = null;
	private static $temporary = null;
	private static $_cacheById = null;
	private static $_cacheAvailableById = null;

	public function __construct() {
		$this->tables = array(
			'bug'=>db_get_table( 'bug' ),
			'bug_text'=>db_get_table( 'bug_text' ),
			'bugnote'=>db_get_table( 'bugnote' ),
			'category'=>db_get_table( 'category' ),
			'cf_string'=>db_get_table( 'custom_field_string' ),
			'bugnote_text'=>db_get_table( 'bugnote_text' ),
			'project'=>db_get_table( 'project' ),
			'bug_monitor'=>db_get_table( 'bug_monitor' ),
			'bug_relationship'=>db_get_table( 'bug_relationship' ),
			'bug_tag'=>db_get_table( 'bug_tag' ),
			'user'=>db_get_table( 'user' ),
			'filters'=>db_get_table( 'filters' ),
		);
	}
	
	public function __get( $p_field_name ) {
		if( array_key_exists( $p_field_name, $this->fields ) ) {
			$t_field = $this->fields[$p_field_name];
			return $t_field->filter_value;
		} 

		return $this->$p_field_name;
	}

	public function __set( $p_field_name, $p_field_value ) {
		$this->$p_field_name = $p_field_value;
	}

	/**
	 *	Pass a reference to this object to each field so they can access other fields when
	 *	there are dependencies.
	 *	@param string $p_field_name The name of the field
	 */
	private function setBugFilter( $p_field_name ) {
		$t_field = $this->getField( $p_field_name );
		if ( $t_field ) {
			$t_field->setBugFilter( $this );
			$this->fields[$p_field_name] = $t_field;
		}
	}

	/**
	 *	Take an assoc array of field/values and assign to
	 *	the project members.
	 */
	public function setFields( $p_row ) {
		foreach( $p_row AS $t_key=>$t_value ) {
			if( property_exists( $this, $t_key ) ) {
				$this->$t_key = $t_value;
			}
		}
	}

	/**
	 *	Check to see if a valid field exists
	 *	@param string field_name
	 *	@return bool whether or not the field exists
	 */
	public function fieldExists( $p_field_name ) {
		if( array_key_exists( $p_field_name, $this->fields ) && $this->fields[ $p_field_name ] ) {
			return true;
		} else if( array_key_exists( $p_field_name, $this->fields['custom_fields'] ) && $this->fields['custom_fields'][ $p_field_name ] ) {
			return true;
		} else if( array_key_exists( $p_field_name, $this->fields['plugin_fields'] ) && $this->fields['plugin_fields'][ $p_field_name ] ) {
			return true;
		}
		return false;
	}

	/**
	 *	Add a field to the fields array.
	 *	@param string $p_key The name of the field key
	 *	@param object $p_field_object The field object
	 *	@param mixed $p_type The type of the field (custom, plugin, or null)
	 */
	public function addField( $p_key, $p_field_object, $p_type=null ) {
		if ( !is_null( $p_type ) ) {
			$this->fields[$p_type][$p_key] = $p_field_object;
		} else {
			$this->fields[$p_key] = $p_field_object;
		}
	}

	/**
	 *	Get a filter field object.  Check for standard, custom, and plugin fields.
	 *	Return a field object. 
	 *	@param string $p_field_name The name of the field to retrieve
	 *	@return array An individual field object or throw an error when the field is not found
	 */
	public function getField( $p_field_name ) {
		if( array_key_exists( $p_field_name, $this->fields ) ) {
			return $this->fields[$p_field_name];
		} else if ( array_key_exists( $p_field_name, $this->fields['custom_fields'] ) ) {
			return $this->fields['custom_fields'][$p_field_name];
		} else if ( array_key_exists( $p_field_name, $this->fields['plugin_fields'] ) ) {
			return $this->fields['plugin_fields'][$p_field_name];
		} else {
			throw new Exception( "Field $p_field_name not found." );
		}
	}

	/**
	 *	Return an array of fields.
	 *	@param string $p_type The type of field to search for (either custom field, plugin field, or standard)
	 *	@return array An array of field objects
	 */
	public function getFields( $p_type=null ) {
		if( !is_null( $p_type ) && array_key_exists( $p_type, $this->fields ) ) {
			return $this->fields[$p_type];
		} else {
			return $this->fields;
		}
	}

	/**
	 *	Be sure all configured fields have valid values.  validation for individual
	 *	fields is run on instantiation and when processing GPC vars.
	 */
	public function validate() {
		# First load any missing default values as some fields depend on others
		if( !array_key_exists( 'custom_fields', $this->fields ) ) {
			$this->fields['custom_fields'] = array();
		}
		if( !array_key_exists( 'plugin_fields', $this->fields ) ) {
			$this->fields['plugin_fields'] = array();
		}
		foreach( self::$field_classes AS $t_field_name=>$t_class ) {
			if( class_exists( $t_class ) && is_subclass_of( $t_class, 'MantisFilter' ) ) {
				if( array_key_exists( $t_field_name, $this->fields ) ) {
					$t_field = $this->fields[$t_field_name]; 
				} else {
					# load a default value for the field
					$t_field = new $t_class( $t_field_name );
				}
				$t_field->setBugFilter( $this );
				$this->fields[$t_field_name] = $t_field;
			}
		}
		$t_custom_fields = self::loadCustomFieldFilters(); 
		foreach( $t_custom_fields AS $t_field_name=>$t_field ) {
			if( array_key_exists( $t_field_name, $this->fields['custom_fields'] ) ) {
				$t_field = $this->fields['custom_fields'][$t_field_name]; 
			}
			$t_field->setBugFilter( $this );
			$this->addField( $t_field_name, $t_field, 'custom_fields' );
		}
		$t_plugin_fields = self::loadPluginFilters();
		foreach( $t_plugin_fields AS $t_field_name=>$t_field ) {
			if( array_key_exists( $t_field_name, $this->fields['plugin_fields'] ) ) {
				$t_field = $this->fields['plugin_fields'][$t_field_name]; 
			}
			$t_field->setBugFilter( $this );
			$this->addField( $t_field_name, $t_field, 'plugin_fields' );
		}

		# after all the fields are loaded, validate them
		foreach( $this->fields AS $t_field_name=>$t_field ) {
			if( $t_field_name == 'custom_fields' || $t_field_name == 'plugin_fields' ) {
				foreach( $t_field AS $t_name=>$t_cf_plugin_field ) {
					if( method_exists( 'validate', $t_cf_plugin_field ) ) {
						$t_cf_plugin_field->validate();
						$this->addField( $t_name, $t_cf_plugin_field, $t_field_name );
					}
				}
			} else {
				$t_field->validate();
				$this->fields[$t_field_name] = $t_field;
			}
		}
	}

	public function validateStored() {
		# We can't have a blank name
		if ( is_blank( $this->name ) ) {
			throw new Exception( lang_get( 'query_blank_name' ) );
		}
		// mantis_filters_table.name has a length of 64. Not allowing longer.
		if ( !$this->isNameValidLength() ) {
			throw new Exception( lang_get( 'query_name_too_long' ) );
		}

		# Check and make sure they don't already have a
		# query with the same name
		$t_query_arr = self::getAvailable();
		foreach( $t_query_arr as $t_id => $t_bug_filter )   {
			if ( $this->name == $t_bug_filter->name ) {
				throw new Exception( lang_get( 'query_dupe_name' ) );
			}
		}
	}

	/**
	 *	The type indicates whether the filter should be completely updated
	 *	or whether to just update specific fields.  
	 *	Check for Post/Get params for the specified fields using the processGPC 
	 *	functions of the individual fields.
	 *	@param int $p_type The type of action to take
	 */
	public function processGPC( $p_type ) {
		switch( $p_type ) {
			case 7:
				# stored queries
				$this->name = strip_tags( gpc_get_string( 'query_name' ) );
				$this->is_public = gpc_get_bool( 'is_public' );
				$f_all_projects = gpc_get_bool( 'all_projects' );
				if( $f_all_projects ) {
					$this->project_id = 0;
				} else {
					$this->project_id = helper_get_current_project();
				}
			break;
			case 2:
				log_event( LOG_FILTERING, 'view_all_set.php: Set the sort order and direction.' );
				# We only need to set those fields that we are overriding 
				$this->fields[FILTER_PROPERTY_SORT]->processGPC();
			break;
			case 5:
				log_event( LOG_FILTERING, 'view_all_set.php: Search Text' );
				$this->fields[FILTER_PROPERTY_SEARCH]->processGPC();
			break;
			case 6:
				log_event( LOG_FILTERING, 'view_all_set.php: View state (simple/advanced)' );
				$this->fields['_view_type']->processGPC();
			break;
			case 1:
			default:
				# update all the fields
				log_event( LOG_FILTERING, 'view_all_set.php: Update filters' );
				foreach( $this->fields AS $t_field_name=>$t_field ) {
					if( $t_field_name == 'custom_fields' ) {
						foreach( $this->fields['custom_fields']  AS $t_field_name=>$t_field ) {
							if( method_exists( $t_field, 'processGPC' ) ) {
								# this does not work for plugin fields...yet
								$t_field->processGPC();
								$this->fields['custom_fields'][$t_field_name] = $t_field;
							}
						}	
					} else if ( $t_field_name == 'plugin_fields' ) {
						foreach( $this->fields['plugin_fields']  AS $t_field_name=>$t_field ) {
							if( method_exists( $t_field, 'processGPC' ) ) {
								# this does not work for plugin fields...yet
								$t_field->processGPC();
								$this->fields['plugin_fields'][$t_field_name] = $t_field;
							}
						}
					} else {
						if( method_exists( $t_field, 'processGPC' ) ) {
							$t_field->processGPC();
							$this->fields[$t_field_name] = $t_field;
						}
					}
				}
			break;
		}
	}

	/**
	 * @param $p_count
	 * @param $p_per_page
	 * @return int
	 */
	public function issuesPerPage( $p_per_page=null ) {
		$t_per_page = $this->getField(FILTER_PROPERTY_ISSUES_PER_PAGE);

		$p_per_page = (( NULL == $p_per_page ) ? (int) $t_per_page->filter_value : $p_per_page );
		$p_per_page = (( 0 == $p_per_page || -1 == $p_per_page ) ? $this->bug_count : $p_per_page );

		return (int) abs( $p_per_page );
	}

	/**
	 *	@return bool true if sticky issues is on, false if not
	 */
	public function showSticky() {
		$t_sticky = $this->getField(FILTER_PROPERTY_STICKY);
		return $t_sticky->filter_value;	
	}

	/**
	 *	Use $p_count and $p_per_page to determine how many pages to split this list up into.
	 *	For the sake of consistency have at least one page, even if it is empty.
	 *	@param $p_count
	 *	@param $p_per_page
	 *	@return $t_page_count
	 */
	public function pageCount() {
		$t_page_count = ceil( $this->bug_count / $this->per_page );
		if( $t_page_count < 1 ) {
			$t_page_count = 1;
    	}
		return $t_page_count;
	}

	/**
	 *  Checks to make sure $p_page_number isn't past the last page.
	 *  and that $p_page_number isn't before the first page
	 */
	public function validatePageNumber() {
		if( $this->page_number > $this->page_count ) {
			$this->page_number = $this->page_count;
		}
		if( $this->page_number < 1 ) {
			$this->page_number = 1;
		}
	}

	/**
	 *	Figure out the offset into the db query, offset is which record to start querying from
	 *	@return int
	 */
	function queryOffset() {
		return(( (int) $this->page_number -1 ) * (int) $this->per_page );
	}

	public function addQueryElement( $p_type, $p_value ) {
			$this->{$p_type}[] = $p_value;
	}

	/**
	 *	The addTableJoin function allows a filter field to join against any table(s) it chooses,
	 *	even nested joins if necessary.
	 *	@see MantisFilterUser::query()
	 *	@param string $p_table The name of the table to be joined to, the left side of the join
	 *	@param string $p_join_table The name of the second table in the join , the right side of the join
	 *	@param string $p_join_string The sql join string
	 */
	public function addTableJoin( $p_table, $p_join_table, $p_join_string ) {
		$this->join_clauses[$p_table][$p_join_table][] = $p_join_string; 
	}

	/**
	 *	Recursively get the joins for the specified table
	 *	@param string $p_table The top level table to retrieve joins for.
	 *	@return string An sql table join string
	 */
	public function getTableJoins( $p_table, $p_join_clauses ) {
		$t_join_string = '';
		if( is_array( $p_join_clauses ) && array_key_exists( $p_table, $p_join_clauses ) ) {
			# t_key is a nested table
			foreach( $p_join_clauses[$p_table] AS $t_key=>$t_join_arr ) {
				$t_join_string .= ' ' . $this->getTableJoins( $t_key, $t_join_arr ) . "\n";
			}
		} else {
			if( is_array( $p_join_clauses ) ) {
				foreach( $p_join_clauses AS $t_key=>$t_join_arr ) {
					# this is an array of join strings
					if( is_numeric( $t_key ) ) {
						return join( ' ', $p_join_clauses );
					} else {
						# the requested table is not in the array.
						return '';
					}
				}
			}
		}
		return $t_join_string;
	}

	/**
	 *	Remove any duplicate values in certain elements of query_clauses
	 *	Do not loop over query clauses as some keys may contain valid duplicate values.
	 *	We only need unique values for the base query elements select, from, and join
	 *	'where' and 'where_params' key should not have duplicates as that is handled earlier and applying
	 *	array_unique here could cause problems with the query.
	 */
	public function uniqueQueryClauses() {
		$this->select_clauses = array_unique( $this->select_clauses );
		$this->from_clauses = array_unique( $this->from_clauses );
		$this->join_clauses = $this->uniqueJoinClauses( $this->join_clauses );
	}

	public function uniqueJoinClauses( $p_join_clauses ) {
		$t_joins = array();
		if( is_array( $p_join_clauses ) ) {
			foreach( $p_join_clauses AS $t_table => $t_clauses ) {
				if( is_numeric( $t_table ) ) {
					# p_join_clauses is an array of join strings without a table name. unique the entire thing and
					# skip the foreach
					$t_joins = array_unique( $p_join_clauses );
					break;
				} else if( is_array( $t_clauses ) ) {
					# The key is another table name, get unique nested joins.
					$t_joins[$t_table] = $this->uniqueJoinClauses( $t_clauses );
				}
			}
		}
		return $t_joins;
	}

	/**
	 *  Build a query with the query clauses array, query for bug count and return the result
	 *	@param array $p_query_clauses
	 *	@return int
	 */
	function getBugCount() {
		$this->uniqueQueryClauses();
		$t_select_string = "SELECT Count( DISTINCT {$this->tables['bug']}.id ) as idcnt ";
		$t_from_string = " FROM ";
		$t_first = true;

		foreach( $this->from_clauses AS $t_table ) {
			if( !$t_first ) {
				$t_from_string .= ", ";
			}
			$t_from_string .= "$t_table";
			$t_from_string .= $this->getTableJoins( $t_table, $this->join_clauses );
			$t_first = false;
		}

		$t_where_string = (( count( $this->where_clauses ) > 0 ) ? 'WHERE ' . implode( ' AND ', $this->where_clauses ) : '' );
		$t_result = db_query_bound( "$t_select_string $t_from_string $t_join_string $t_where_string", $this->where_params );

		return db_result( $t_result );
	}

	/**	
	 *	return 
	 */
	public function execute( $p_per_page=null, $p_user_id = null ) {
		log_event( LOG_FILTERING, 'START NEW FILTER QUERY' );
		$t_report_bug_threshold = config_get( 'report_bug_threshold' );
		#	execute the filter as though this user is acting.
		#	normally this will be the current user but in some
		#	cases it may be useful to view a query as a different
		#	user, particularly for management
		if( null === $p_user_id ) {
			$t_filter_user_id = auth_get_current_user_id();
		} else {
			$t_filter_user_id = $p_user_id;
		}
    	$this->filter_user_id = db_prepare_int( $t_filter_user_id );

		$this->where_clauses = array(
			"{$this->tables['project']}.enabled = " . db_param(),
			"{$this->tables['project']}.id = {$this->tables['bug']}.project_id",
		);
		$this->where_params = array( 1, );
		$this->select_clauses = array(
			"{$this->tables['bug']}.*",
		);

		$this->from_clauses[] = $this->tables['project'];
		$this->from_clauses[] = $this->tables['bug'];
		foreach( $this->fields AS $t_field_name=>$t_field ) {
			if( $t_field_name == 'custom_fields' || $t_field_name == 'plugin_fields' ) {
				foreach( $t_field AS $t_cf_plugin_name=>$t_cf_plugin_field ) {
					if( method_exists( $t_cf_plugin_field, 'query' ) ) {	
						$t_cf_plugin_field->query();
					}
				}
			} else {
				$t_field->query();
			}
		}

		# Get the total number of bugs that meet the criteria.
		$this->bug_count = $this->getBugCount();

		if( 0 == $this->bug_count ) {
			return array();
		}
		$this->per_page = $this->issuesPerPage( $p_per_page );
		$this->page_count = ( is_null( $this->page_count ) ? $this->pageCount() : $this->page_count );
		$this->validatePageNumber();
		$t_query_clauses = $this->uniqueQueryClauses();
		$t_select_string = "SELECT DISTINCT " . implode( ', ', $this->select_clauses );

		$t_from_string = " FROM ";
		$t_first = true;
		foreach( $this->from_clauses AS $t_table ) {
			if( !$t_first ) {
				$t_from_string .= ",";
			}
			$t_from_string .= "$t_table ";
			$t_from_string .= $this->getTableJoins( $t_table, $this->join_clauses );
			$t_first = false;
		}

		$t_order_string = " ORDER BY " . implode( ', ', $this->order_clauses );
		$t_where_string = count( $this->where_clauses ) > 0 ? 'WHERE ' . implode( ' AND ', $this->where_clauses ) : '';
		$t_result = db_query_bound( "$t_select_string $t_from_string $t_join_string $t_where_string $t_order_string", $this->where_params, $this->per_page, $this->queryOffset() );
		$t_row_count = db_num_rows( $t_result );

		$t_id_array_lastmod = array();
		for( $i = 0;$i < $t_row_count;$i++ ) {
			$t_row = db_fetch_array( $t_result );
			$t_id_array_lastmod[] = (int) $t_row['id'];
			$t_rows[] = $t_row;
		}

		if( $t_rows ) {
			return $this->cacheBugRows( $t_rows, $t_id_array_lastmod );
		} else {
			return false;
		}
	}

	/**
	 *	Cache the filter results with bugnote stats for later use
	 *	@param array $p_rows results of the filter query
	 *	@param array $p_id_array_lastmod array of bug ids
	 *	@return array
	 */
	public function cacheBugRows( $p_rows, $p_id_array_lastmod ) {
		$t_bugnote_table = db_get_table( 'bugnote' );

		$t_id_array_lastmod = array_unique( $p_id_array_lastmod );
		$t_where_string = "WHERE $t_bugnote_table.bug_id in (" . implode( ", ", $t_id_array_lastmod ) . ')';
		$t_query = "SELECT DISTINCT bug_id,MAX(last_modified) as last_modified, COUNT(last_modified) as count FROM $t_bugnote_table $t_where_string GROUP BY bug_id";

		# perform query
		$t_result = db_query_bound( $t_query );
		$t_row_count = db_num_rows( $t_result );
		for( $i = 0;$i < $t_row_count;$i++ ) {
			$t_row = db_fetch_array( $t_result );
			$t_stats[$t_row['bug_id']] = $t_row;
		}

		$t_rows = array();
		foreach( $p_rows as $t_row ) {
			if( !isset( $t_stats[$t_row['id']] ) ) {
				$t_rows[] = bug_row_to_object( bug_cache_database_result( $t_row, false ) );
			} else {
				$t_rows[] = bug_row_to_object( bug_cache_database_result( $t_row, $t_stats[ $t_row['id'] ] ) );
			}
		}
		return $t_rows;
	}

	/**
	 *  Get a permalink for the current active filter.  The results of using these fields by other users
	 *  can be inconsistent with the original results due to fields like "Myself", "Current Project",
	 *  and due to access level.
	 *	The calling script should add the type parameter if necessary.
	 *	@return string the search.php?xxxx or an empty string if no criteria applied.
	 */
	public function getUrl() {
		foreach( $this->fields AS $t_field_name=>$t_field ) {
			if( $t_field_name=='custom_fields' || $t_field_name=='plugin_fields' ) {
				foreach( $t_field AS $t_cf_plugin_field ) {
					if( is_subclass_of( 'MantisFilter', $t_cf_plugin_field ) ) {
						$t_url = $t_cf_plugin_field->urlEncodeField();
						if( !empty( $t_url ) ) {
							$t_query[] = $t_url;
						}
					}
				}
			} else {
				$t_url = $t_field->urlEncodeField();
				if( !empty( $t_url ) ) {
					$t_query[] = $t_url;
				}
			}
		}
		if( count( $t_query ) > 0 ) {
			$t_query_str = implode( $t_query, '&' );
			$t_url = config_get( 'path' ) . 'view_all_set.php?' . $t_query_str;
		} else {
			$t_url = '';
		}
		return $t_url;
	}

	/**
	 *	Deserialize filter string
	 *	@param string $p_serialized_filter
	 *	@return mixed $t_filter array
	 */
	public static function deserialize( $p_serialized_filter ) {
		if( is_blank( $p_serialized_filter ) ) {
			return false;
		}

		# check to see if new cookie is needed
		$t_setting_arr = explode( '#', $p_serialized_filter, 2 );
		if(( $t_setting_arr[0] == 'v1' ) || ( $t_setting_arr[0] == 'v2' ) || ( $t_setting_arr[0] == 'v3' ) || ( $t_setting_arr[0] == 'v4' ) ) {
			# these versions can't be salvaged, they are too old to update
			return false;
		}

		# We shouldn't need to do this anymore, as filters from v5 onwards should cope with changing
		# filter indices dynamically
		$t_filter_array = array();
		if( isset( $t_setting_arr[1] ) ) {
			$t_filter_array = unserialize( $t_setting_arr[1] );
		} else {
			return false;
		}
    	return $t_filter_array;
	}

	/**
	 *	Serialize the values for each field in the filter and return the 
	 *	serialized string.
	 *	@return string Serialized string of filter settings
	 */
	public function getSerializedSettings() {
		$t_arr = array();
		foreach( $this->fields AS $t_key=>$t_field) {
			if( $t_key == 'custom_fields' || $t_key == 'plugin_fields' ) {
				foreach( $t_field AS $t_cf_plugin_name=>$t_cf_plugin_field ) {
					$t_arr[$t_key][$t_cf_plugin_name] = $t_cf_plugin_field->filter_value;
				}
			} else {
				$t_arr[$t_key] = $t_field->filter_value;
			}
		}
		return serialize( $t_arr );
	}

	/**
	 *	Add a filter to the database for the current user
	 *	@param int $p_project_id
	 *	@param bool $p_is_public
	 *	@param string $p_name
	 *	@return int
	 *	@todo analyze this for project id when multiple projects are selected in the filter
	 *	@todo analyze this for saving as a different user than current
	 */
	public function saveCurrent( $p_project_id = null ) {
		if( is_null( $p_project_id ) ) {
			$p_project_id = is_array( $this->project_id ) ? $this->project_id[0] : $this->project_id;
		}
		$t_user_id = auth_get_current_user_id();
		$t_filters_table = db_get_table( 'filters' );
		$t_cookie_version = config_get( 'cookie_version' );
		$t_settings_serialized = $this->getSerializedSettings();

		$t_filter_string = $t_cookie_version . '#' . $t_settings_serialized;

		# check that the user can save non current filters (if required)
		if(( ALL_PROJECTS <= $p_project_id ) && ( !is_blank( $this->name ) ) && ( !access_has_project_level( config_get( 'stored_query_create_threshold' ) ) ) ) {
			return -1;
		}

		# ensure that we're not making this filter public if we're not allowed
		if( $this->is_public && !access_has_project_level( config_get( 'stored_query_create_shared_threshold' ) ) ) {
			$this->is_public = false;
		}

		# Do I need to update or insert this value?
		$t_query = "SELECT id FROM $t_filters_table WHERE user_id=" . db_param() . " AND project_id=" . db_param() . " AND name=" . db_param();
		$t_result = db_query_bound( $t_query, array( $t_user_id, $p_project_id, $this->name ) );

		if( db_num_rows( $t_result ) > 0 ) {
			$t_row = db_fetch_array( $t_result );
			$t_query = "UPDATE $t_filters_table SET is_public=" . db_param() . ", filter_string=" . db_param() . " WHERE id=" . db_param();
			db_query_bound( $t_query, Array( $this->is_public, $t_filter_string, $t_row['id'] ) );
			return $t_row['id'];
		} else {
			$t_query = "INSERT INTO $t_filters_table ( user_id, project_id, is_public, name, filter_string ) VALUES
				( " . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
			db_query_bound( $t_query, Array( $t_user_id, $p_project_id, $this->is_public, $this->name, $t_filter_string ) );

			# Recall the query, we want the filter ID
			$t_query = "SELECT id FROM $t_filters_table WHERE user_id=" . db_param() . " AND project_id=" . db_param() . " AND name=" . db_param();
			$t_result = db_query_bound( $t_query, Array( $t_user_id, $p_project_id, $this->name ) );
			if( db_num_rows( $t_result ) > 0 ) {
				$t_row = db_fetch_array( $t_result );
				return $t_row['id'];
			}
			return -1;
		}
	}

	/**
	 *	Check for valid name length. This is for named stored queries
	 *	@param str $p_name
	 *	@return bool true when under max_length (64) and false when over
	 */
	public function isNameValidLength() {
		if( utf8_strlen( $this->name ) > 64 ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 *	The filter string stores the _source_query_id which may
	 *	not always match the rest of the filter.  This parameter
	 *	should be ignored for the comparison.
	 *	@param string $p_filter_string serialized filter string
	 *	@return bool true if the filters match, false otherwise
	 */
	public function filterStringExists( $p_filter_obj ) {
		$t_filter_arr = self::deserialize( $p_filter_obj->filter_string );
		$t_current_filter_arr = self::deserialize( $this->filter_string );
		unset( $t_filter_arr['_source_query_id'] );
		unset( $t_current_filter_arr['_source_query_id'] );
		$t_diff = array_diff( $t_current_filter_arr, $t_filter_arr );
# @TODO: these almost never match because of two issues
# 	1) when date queries are not enabled, the filter api automatically inserts
#		dates for the current month.  When a query is stored, this default date
#		is stored with the query even though it is not used.
#	2) When only one sort parameter is specified, the filter api adds last_updated
#		but it does not seem to be stored that way in the db. at least there is something
#		screwy about how the sorting is different when applying a stored query and then 
#		recovering it after a cookie serialization.
#	OK, so what's happening is that when clicking the save button, the filter is submitted
#	to view_all_set but the f_type value is 1 so it updates the array.  This may or may not
#	be desired depending on the circumstance.  determine how to update an existing stored 
#	query with new values...

		return ( empty( $t_diff ) ? true:false );	
	}

	/**
	 *	Get the filter id stored for the current project and user.
	 *	@param int $p_project_id
	 *	@param int $p_user_id
	 *	@return mixed The id if found or null; 
	 */
	public static function getIdByCurrentProjectUser( $p_project_id, $p_user_id = null ) {
			$t_filters_table = db_get_table( 'filters' );
			$c_project_id = $p_project_id * -1;

			if( null === $p_user_id ) {
					$c_user_id = auth_get_current_user_id();
			} else {
					$c_user_id = db_prepare_int( $p_user_id );
			}

			# we store current filters for each project with a special project index
			$t_query = "SELECT *
					FROM $t_filters_table
					WHERE user_id=" . db_param() . "
					AND project_id=" . db_param() . "
					AND name=" . db_param();
			$t_result = db_query_bound( $t_query, Array( $c_user_id, $c_project_id, '' ) );

			if( db_num_rows( $t_result ) > 0 ) {
					$t_row = db_fetch_array( $t_result );
					return $t_row['id'];
			}

			return null;
	}

	/**
	 *  This function returns the stored query object that is
	 *  tied to the unique id parameter. If the user doesn't
	 *  have permission to see this filter, the function
	 *  returns null
	 *	@param int $p_filter_id
	 *	@param int $p_user_id
	 *	@return mixed
	 */
	public static function getById( $p_filter_id, $p_user_id = null ) {
		if( !isset( self::$_cacheById[$p_filter_id] ) ) {
			$t_filters_table = db_get_table( 'filters' );
			$t_user_id = ( null === $p_user_id ? auth_get_current_user_id() : $p_user_id );

			$t_query = 'SELECT * FROM ' . $t_filters_table . ' WHERE id=' . db_param() . ' AND ( user_id=' . db_param() . ' OR is_public= ' . db_param() . ')';
			$t_result = db_query_bound( $t_query, Array( $p_filter_id, $t_user_id, true ) );

			if( db_num_rows( $t_result ) > 0 ) {
				$t_row = db_fetch_array( $t_result );
				$t_has_stored_query_use_threshold = access_has_project_level( config_get( 'stored_query_use_threshold', $t_row['project_id'], $t_user_id ) );

				# if this is a stored query, check that the user has access
				if(( ALL_PROJECTS <= $t_row['project_id'] ) && ( !is_blank( $t_row['name'] ) ) && ( !$t_has_stored_query_use_threshold ) ) {
					return null;
				}

				$t_filter_arr = self::deserialize( $t_row['filter_string'] );

				$t_sq = self::loadArr( $t_filter_arr );
				$t_sq->setFields( $t_row );
				self::$_cacheById[$p_filter_id] = $t_sq; 
			} else {
				self::$_cacheById[$p_filter_id] = false; 
			}
		}
		
		if( self::$_cacheById[$p_filter_id] === false ) {
			return null;
		}
		return self::$_cacheById[$p_filter_id];
	} 

	/**
	 *	Get a list of stored filters available for the current project and user.
	 *	The list should not include 'current' filters (filters with no name).
	 *	@param int $p_project_id
	 *	@param int $p_user_id
	 *	@return mixed
	 */
	public static function getAvailable( $p_project_id = null, $p_user_id = null ) {
		if( !self::$_cacheAvailableById ) {
			$t_filters_table = db_get_table( 'filters' );
			$t_overall_query_arr = array();

			$t_project_id = ( null === $p_project_id ? helper_get_current_project() : db_prepare_int( $p_project_id ) );
			$t_user_id = ( null === $p_user_id ? auth_get_current_user_id() : db_prepare_int( $p_user_id ) );

			# If the user doesn't have access rights to stored queries, just return
			# This check is for the current project
			if( !access_has_project_level( config_get( 'stored_query_use_threshold' ) ) ) {
				return $t_overall_query_arr;
			}

			# Get the list of available queries. By sorting such that public queries are
			# first, we can override any query that has the same name as a private query
			# with that private one
			$t_query = "SELECT * FROM $t_filters_table
						WHERE ( project_id = " . db_param() . "
						OR project_id = 0 )
						AND name != ''
						ORDER BY is_public DESC, name ASC";
			$t_result = db_query_bound( $t_query, Array( $t_project_id ) );
			$t_query_count = db_num_rows( $t_result );

			for( $i = 0;$i < $t_query_count;$i++ ) {
				$t_row = db_fetch_array( $t_result );
				$t_has_stored_query_use_threshold = access_has_project_level( config_get( 'stored_query_use_threshold', $t_row['project_id'], $t_user_id ) );
				/**
				 *	'Current' filters are saved with a negative project id value
				 *	This condition checks that the record is for a stored query rather
				 *	than a 'current' filter
				 *	This check against has_stored_query_use is for the project listed in the db row.
				 */
				if( ALL_PROJECTS <= $t_row['project_id'] && $t_has_stored_query_use_threshold ) {
					if(( $t_row['user_id'] == $t_user_id ) || db_prepare_bool( $t_row['is_public'] ) ) {
						$t_sq = new MantisBugFilter();
						$t_sq->setFields( $t_row );
						$t_overall_query_arr[$t_row['id']] = $t_sq;
					}
				}
			}
			#$t_overall_query_arr = array_unique( $t_overall_query_arr );

			uasort( $t_overall_query_arr, 'MantisBugFilter::sortByName' );
			self::$_cacheAvailableById = $t_overall_query_arr;
		}
		return self::$_cacheAvailableById;
	}

	/**
	 *	Check if the current user has permissions to delete the stored query
	 *	@param $p_filter_id
	 *	@return bool
	 */
	public function canDelete() {
		$t_filters_table = $this->tables['filters'];
		$t_user = MantisUser::getCurrent();

		# Administrators can delete any filter
		if( $t_user->isAdministrator() ) {
			return true;
		}

		$t_query = "SELECT id
			FROM $t_filters_table
			WHERE id=" . db_param() . "
				AND user_id=" . db_param() . "
				AND project_id!=" . db_param();

		$t_result = db_query_bound( $t_query, Array( $this->id, $t_user->id, -1 ) );

		if( db_num_rows( $t_result ) > 0 ) {
        	return true;
    	}

    	return false;
	}

	/**
	 *	Delete the filter
	 *	@return bool
	 */
	public function delete() {
		if( !$this->canDelete() ) {
			return false;
		}

		$t_query = 'DELETE FROM ' . $this->tables['filters'] . ' WHERE id=' . db_param();
		$t_result = db_query_bound( $t_query, Array( $this->id ) );

		if( db_affected_rows( $t_result ) > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 *	Load a new MantisBugFilter object with the data specified in p_filter_arr.
	 *	@param array $p_filter_arr An array of filter fields and values.
	 *	@return object A MantisBugFilter object
	 */
	public static function loadArr( $p_filter_arr ) {
		$t_filter = new MantisBugFilter();

		$t_plugin_fields = self::loadPluginFilters();
		# @todo figure out passing in the current projects selected in the filter( we may not know here as we haven't processed gpc yet)
		$t_custom_fields = self::loadCustomFieldFilters();
		foreach( $p_filter_arr AS $t_name=>$t_values ) {
			if( is_array( self::$field_classes ) && array_key_exists( $t_name, self::$field_classes ) ) {
				$t_class = self::$field_classes[$t_name];

				if( class_exists( $t_class ) && is_subclass_of( $t_class, 'MantisFilter' ) ) {
					$t_field = new $t_class( $t_name, $t_values );
					$t_filter->addField( $t_name, $t_field );
				} else {
					throw new Exception( "'$t_name' is not a valid filter field" );
				}
			} else if( $t_name == 'plugin_fields' && is_array( $t_plugin_fields ) ) { #&& array_key_exists( $t_name, $t_plugin_fields ) )
				# load the plugin field values from the filter into the objects
				foreach( $t_values AS $t_plugin_name=>$t_plugin_values ) {
					if( count( $t_plugin_values ) == 1 && is_null( $t_plugin_values[0] ) ) {
						# don't add the value if it is null
						continue;
					}
					# only add values for plugin fields in the plugin fields array.
					# filters may have old values or values for fields which the user
					# no longer has access to.  Just discard those values.
					# @todo check to be sure this doesn't override the stored query values
					# when the user doesn't have write access
					if( array_key_exists( $t_plugin_name, $t_plugin_fields ) ) {
						$t_field = $t_plugin_fields[$t_plugin_name];
						$t_field->filter_value = $t_plugin_values;
						$t_filter->addField( $t_plugin_name, $t_field, 'plugin_fields' );
					}
				}
			} else if( $t_name == 'custom_fields' && is_array( $t_custom_fields ) ) {
				# load the custom field values from the filter into the objects
				foreach( $t_values AS $t_cf_name=>$t_cf_values ) {
					if( count( $t_cf_values ) == 1 && is_null( $t_cf_values[0] ) ) {
						# don't add the value if it is null
						continue;
					}
					# only add values for custom fields in the custom fields array.
					# filters may have old values or values for fields which the user
					# no longer has access to.  Just discard those values.
					# @todo check to be sure this doesn't override the stored query values
					# when the user doesn't have write access
					if( array_key_exists( $t_cf_name, $t_custom_fields ) ) {
						$t_field = $t_custom_fields[$t_cf_name];
						$t_field->filter_value = $t_cf_values;
						$t_filter->addField( $t_cf_name, $t_field, 'custom_fields' );
					}
				}
			} else {
			
			}
		}
		return $t_filter;
	}

	/**
	 *	@param mixed $p_project_id array of project ids to add custom field filters for
	 *	@return array An array of filterable custom fields
	 */
	public static function loadCustomFieldFilters( $p_project_id = null ) {
		static $s_field_array = null;

		if ( is_null( $s_field_array ) ) {
			$s_field_array = array();

			$t_project_id = is_null( $p_project_id ) ? helper_get_current_project() : $p_project_id;
			$t_filter_by_custom_fields = ( ON == config_get( 'filter_by_custom_fields' ) ? true : false );
			if( $t_filter_by_custom_fields ) {
				# @todo custom fields retrieved are only those accessible by the current user.  Modify
				# to allow an authorized user to view custom fields at the level of another user as long 
				# as the second user has the same or lower permissions ( ie manager is watching a developers queu )
				$t_custom_fields = custom_field_get_linked_ids( $t_project_id );
        		foreach( $t_custom_fields as $t_cfid ) {
					$t_field_info = custom_field_cache_row( $t_cfid, true );
					if( $t_field_info['filter_by'] ) {
						$t_current_user_access_level = current_user_get_access_level(); # @todo this needs to be for the filter user
						if( $t_field_info['access_level_r'] <= $t_current_user_access_level ) {
							$t_field_name = 'custom_field_' . $t_field_info['id'];
							switch( $t_field_info['type'] ) {
								case CUSTOM_FIELD_TYPE_DATE:
									$t_filter_object = new MantisFilterCustomFieldDate( $t_field_info );
								break;
								case CUSTOM_FIELD_TYPE_CHECKBOX:
   		                     	case CUSTOM_FIELD_TYPE_MULTILIST:
								default:
									$t_filter_object = new MantisFilterCustomField( $t_field_info );
								break;
							}
							$s_field_array[ $t_field_name ] = $t_filter_object;
						}
					}
				}
			}
		}

		return $s_field_array;
	}

	/**
	 *	Allow plugins to define a set of class-based filters, and register/load
	 *	them here to be used by the rest of filter_api.
	 *	@return array Mapping of field name to filter object
	 */
	public static function loadPluginFilters() {
		static $s_field_array = null;

		if ( is_null( $s_field_array ) ) {
			$s_field_array = array();

			$t_all_plugin_filters = event_signal( 'EVENT_FILTER_FIELDS' );
			foreach( $t_all_plugin_filters as $t_plugin => $t_plugin_filters ) {
				foreach( $t_plugin_filters as $t_callback => $t_plugin_filter_array ) {
					if ( is_array( $t_plugin_filter_array ) ) {
						foreach( $t_plugin_filter_array as $t_filter_class ) {
							if ( class_exists( $t_filter_class ) && is_subclass_of( $t_filter_class, 'MantisFilter' ) ) {
								$t_filter_object = new $t_filter_class();
								$s_field_array[ $t_filter_object->field] = $t_filter_object;
							}
						}
					}
				}
			}
		}
		return $s_field_array;
	}

	/**
	 *	Returns the issue filter for the current user
	 *	@return Active issue filter for current user or false if no filter is currently defined.
	 *	@access public
	*/
	public static function loadCurrent() {
		if ( null == self::$current ) {
			$f_filter_string = gpc_get_string( 'filter', '' );

			# if filter string is not blank this is a temporary filter
			# and should be loaded from a token
			if( !is_blank( $f_filter_string ) ) {
				$t_filter = self::loadByToken( $f_filter_string );
			} else {
				# otherwise, check for a php cookie
				$t_filter = self::loadByCookie();
			}

			# it's possible the filter is too old. try to load the old way.
			if( !$t_filter ) {
				# can't think of a time when this shouldn't be the current project.
				$t_project_id = helper_get_current_project();
				$t_view_all_cookie_id = self::getIdByCurrentProjectUser( $t_project_id );
				$t_stored_filter = self::getById( $t_view_all_cookie_id );
				$t_filter_arr = self::deserialize( $t_stored_filter );
				if( $t_filter_arr ) {
					$t_filter = self::loadArr( $t_filter_arr );
				}
			}
			# still no filter?  load the default
			if( !$t_filter ) {
				$t_filter = self::loadDefault();
			}
			$t_filter->validate();

			self::$current = $t_filter;
		}

		return self::$current;
	} 

	/**
	 *	Get a MantisBugFilter for My View page.
	 *	@param string $p_name The name of the filter to retrieve.
	 *	@return object A MantisBugFilter object
	 */
	public static function loadTemporaryFilter( $p_name ) {
		if( is_null( self::$temporary[$p_name] ) ) {
			$t_update_bug_threshold = config_get( 'update_bug_threshold' );
			$t_bug_resolved_status_threshold = config_get( 'bug_resolved_status_threshold' );
			$t_hide_status_default = config_get( 'hide_status_default' );
			$t_default_show_changed = config_get( 'default_show_changed' );
			$t_my_view_bug_count = config_get( 'my_view_bug_count' );

			$t_filter = MantisBugFilter::loadDefault();
			$t_per_page = $t_filter->getField( FILTER_PROPERTY_ISSUES_PER_PAGE );
			switch( $p_name ) {
			    case 'verify':
   	    		 	$t_reporter = $t_filter->getField( FILTER_PROPERTY_REPORTER_ID );
			        $t_reporter->filter_value = array( META_FILTER_MYSELF );
   	    		 	$t_status = $t_filter->getField( FILTER_PROPERTY_STATUS );
			        $t_status->filter_value = array( $t_bug_resolved_status_threshold );
			    break;
			    case 'my_comments':
			        $t_note_user = $t_filter->getField( FILTER_PROPERTY_NOTE_USER_ID );
   	    		 	$t_note_user->filter_value = array( META_FILTER_MYSELF );
			    break;
			    case 'feedback':
   	    		 	$t_reporter = $t_filter->getField( FILTER_PROPERTY_REPORTER_ID );
			        $t_reporter->filter_value = array( META_FILTER_MYSELF );
   	    		 	$t_status = $t_filter->getField( FILTER_PROPERTY_STATUS );
			        $t_status->filter_value = array( config_get( 'bug_feedback_status' ) );
			    break;
			    case 'assigned':
   	    		 	$t_handler = $t_filter->getField( FILTER_PROPERTY_HANDLER_ID );
			        $t_handler->filter_value = array( META_FILTER_MYSELF );
			        $t_hide_status = $t_filter->getField( FILTER_PROPERTY_HIDE_STATUS );
   	    		 	$t_hide_status->filter_value = $t_bug_resolved_status_threshold;
			    break;
			    case 'unassigned':
			        $t_handler = $t_filter->getField( FILTER_PROPERTY_HANDLER_ID );
   	    		 	$t_handler->filter_value = array( META_FILTER_NONE );
			    break;
			    case 'reported':
			        $t_reporter = $t_filter->getField( FILTER_PROPERTY_REPORTER_ID );
			        $t_reporter->filter_value = array( META_FILTER_MYSELF );
			    break;
			    case 'resolved':
			        $t_status = $t_filter->getField( FILTER_PROPERTY_STATUS );
			        $t_status->filter_value = array( $t_bug_resolved_status_threshold );
			    break;
			    case 'recent_mod':
			        $t_hide_status = $t_filter->getField( FILTER_PROPERTY_HIDE_STATUS );
   	    		 	$t_hide_status->filter_value = META_FILTER_NONE;
			    break;
			    case 'monitored':
   	    		 	$t_monitor = $t_filter->getField( FILTER_PROPERTY_MONITOR_USER_ID );
			        $t_monitor->filter_value = array( META_FILTER_MYSELF );
			    break;
			}

			$t_per_page->filter_value = $t_my_view_bug_count;
			$t_filter->validate();
			self::$temporary[$p_name] = $t_filter;
		}

		return self::$temporary[$p_name];
	}

	/**
	 *	Check if the filter cookie exists and is of the correct version.
	 *	@return bool
	 */
	public static function loadByCookie() {
		$t_view_all_cookie_id = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );
		$t_view_all_cookie = self::getById( $t_view_all_cookie_id );
		# check to see if the cookie does not exist

		if( is_blank( $t_view_all_cookie ) ) {
        	return false;
		} else {
			return $t_view_all_cookie;
		}
	}

	/**
	 *	Tokens are used for storing temporary filters between pages.
	 *	If token is numeric, retrieve the stored string and load the object
	 *	If token is a serialized string deserialize it and load the object
	 *	@param mixed $p_filter A filter id or serialized string.
	 *	@return object A MantisFilterObject
	 */
	public static function loadByToken( $p_filter ) {
		if( is_numeric( $p_filter ) ) {
			$t_token = token_get_value( TOKEN_FILTER );
			if( null != $t_token ) {
				$t_filter_arr = unserialize( $t_token );
				
			}
		} else {
			$t_filter_arr = unserialize( $p_filter );
		}
		return self::loadArr( $t_filter_arr );
	}

	/**
	 *	Load a filter object with defaults as specified in the fields.
	 *	Include custom and plugin fields
	 *	@return object A MantisBugFilter object
	 */
	public static function loadDefault() {
		$t_filter = new MantisBugFilter();
		# @todo figure out passing in the current projects selected in the filter( we may not know here as we haven't processed gpc yet)
		$t_custom_fields = self::loadCustomFieldFilters();
		$t_plugin_fields = self::loadPluginFilters();

		foreach( self::$field_classes AS $t_name=>$t_class ) {
			if( class_exists( $t_class ) && is_subclass_of( $t_class, 'MantisFilter' ) ) {
				$t_field = new $t_class( $t_name );
				$t_filter->addField( $t_name, $t_field ); 
			}
		}
		if( is_array( $t_custom_fields ) ) {
			foreach( $t_custom_fields AS $t_name=>$t_field ) {
				$t_filter->addField( $t_name, $t_field, 'custom_fields' );
			}
		}
		if( is_array( $t_plugin_fields ) ) {
			foreach( $t_plugin_fields AS $t_name=>$t_field ) {
				$t_filter->addField( $t_name, $t_field, 'plugin_fields' );
			}
		}
		return $t_filter;
	}

	/**
	 *  Function to compare two stored query objects by name.
	 *  @param obj $a stored query object
	 *  @param obj $b stored query object
	 *  @return int result of comparison
	 */
	public static function sortByName( $a, $b ) {
		if( $a->name == $b->name ) {
			return 0;
		}
		return ($a->name < $b->name ) ? -1 : 1;
	}
}
