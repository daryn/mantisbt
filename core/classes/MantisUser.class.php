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
 * @package MantisBT
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 *	A class that handles MantisBT Filters.
 *
 *	@package	CoreAPI
 *	@subpackage classes
 */
class MantisUser {
	private $id;
	private $username;
	private $realname;
	private $email;
	private $password;
	private $enabled;
	private $protected;
	private $access_level;
	private $login_count;
	private $lost_password_request_count;
	private $failed_login_count;
	private $cookie_string;
	private $last_visit;
	private $date_created;

	private $selected = false;

	private static $current = null;

	
	/**
	 *	@access private
	 */
	private static $_cacheUsersById = array();

	public function __get( $p_field_name ) {
		switch( $p_field_name ) {
			case 'name':
				if( ON == config_get( 'show_realname' ) ) {
					if( is_blank( $this->realname ) ) {
						return $this->username;
					} else {
						/**
						 *	duplicate_realname was never implemented @todo do we want to?
						 */
						if( isset( $row['duplicate_realname'] ) && ( ON == $row['duplicate_realname'] ) ) {
							return $this->realname . ' (' . $this->username . ')';
						} else {
							return $this->realname;
						}
					}
				} else {
					return $this->username;
				}
			break;
			default:
				return $this->$p_field_name;
			break;
		}
	}

	public function __set( $p_field_name, $p_field_value ) {
		switch( $p_field_name ) {
			default:
				$this->$p_field_name = $p_field_value;
			break;
		}

	}

	private function setFields( $p_row ) {
		foreach( $p_row AS $t_key=>$t_value ) {
			# only assign the field names
			if( !is_int( $t_key ) ) {
				$this->$t_key = $t_value;
			}
		}
	}

	/**
	 *	@return bool true if the user has access of ADMINISTRATOR or higher, false otherwise
	 */
	public function isAdministrator() {
		if( $this->access_level >= config_get_global( 'admin_site_threshold' ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function getCurrent() {
		if( !self::$current) {
			$t_current_id = auth_get_current_user_id();
			self::$current = self::getById( $t_current_id );
		}
		return self::$current;
	}

	/**
	 *  Return a cached copy of a user.  If it is not cached
	 *	query the database for the user.  An error is thrown
	 *	if no user is found in the database.  The caller should
	 *	catch and handle the error as appropriate for the context.
 	 *	@param int $p_user_id
 	 *	@return associate array indexed by id.
	 */
	public static function getById( $p_user_id ) {
		$c_user_id = db_prepare_int( $p_user_id );
		if( isset( self::$_cacheUsersById[$c_user_id] ) ) {
			return self::$_cacheUsersById[$c_user_id];
		}

		# load up the user 
		$t_user_table = db_get_table( 'user' );
		$t_query = 'SELECT * FROM ' . $t_user_table . ' WHERE id=' . db_param();
		$t_result = db_query_bound( $t_query, Array( $c_user_id ) );

		if( 0 == db_num_rows( $t_result ) ) {
			throw new Exception( ERROR_USER_NOT_FOUND );
		}

		$t_row = db_fetch_array( $t_result );

		$t_user = new MantisUser();
		$t_user->setFields( $t_row );	
	
		# assign it
		self::$_cacheUsersById[$p_id] = $t_user;

		return $t_user;
	}

	/**
	 *	This differs from the original behavior which excluded users 
	 *	based on access level for the field requested.  This prevented querying
	 *	for bugs acted on by users who are no longer associated with the project.
	 *	It also included users who had never reported, handled, or monitored a bug
	 *	simply because they had permissions.  This only includes users who actually 
	 *	have associated records.
	 *
	 *	Get a sorted distinct array of users by field and project
	 *	@param string $p_field the name of the user field to query.
	 *	@param int $p_project_id a project id to limit list by.
	 *	@param bool $p_exclude_disabled If true do not include disabled users in the result. This is the default behavior. 
	 *	@return mixed result array
	 *	@access public
	 */
	public static function getDistinctUserOptionList( $p_field_name='reporter_id', $p_project_id = null, $p_exclude_disabled=true ) {
		# load up the user 
		$t_user_table = db_get_table( 'user' );
		$t_bug_table = db_get_table( 'bug' );
		$t_param = array();

		if( $p_field_name == 'monitor_user_id' ) {
			$t_bug_monitor_table = db_get_table( 'bug_monitor' );
			#$t_query = "SELECT DISTINCT u.id, u.username, u.realname FROM $t_user_table u, $t_bug_monitor_table m, $t_bug_table b WHERE u.id=m.user_id AND m.bug_id=b.id";
			$t_query = "SELECT DISTINCT u.* FROM $t_user_table u, $t_bug_monitor_table m, $t_bug_table b WHERE u.id=m.user_id AND m.bug_id=b.id";
		} else if( $p_field_name == 'note_user_id' ) {
			$t_bugnote_table = db_get_table( 'bugnote' );
			$t_query = "SELECT DISTINCT u.* FROM $t_user_table u, $t_bugnote_table bn, $t_bug_table b WHERE u.id=bn.reporter_id AND bn.bug_id=b.id";
		} else {
			switch( $p_field_name ) {
				case 'handler_id':
					$t_join_field='b.handler_id';
				break;
				default:
					$t_join_field='b.reporter_id';
				break;
			}
			$t_query = "SELECT DISTINCT u.* FROM $t_user_table u, $t_bug_table b WHERE u.id=$t_join_field";
		}

		if( $p_project_id > 0 ) {
			$t_query .= ' AND b.project_id=' . db_param();
			$t_param[] = $p_project_id;
		}

		if( $p_exclude_disabled ) {
			$t_query .= ' AND u.enabled =' . db_param();
			$t_param[] = true;
		}
		$t_result = db_query_bound( $t_query, $t_param );

		if( 0 == db_num_rows( $t_result ) ) {
			throw new Exception( ERROR_USER_NOT_FOUND );
		}


		$t_show_realname = ( ON == config_get( 'show_realname' ) );
		$t_sort_by_last_name = ( ON == config_get( 'sort_by_last_name' ) );
		$t_display = array();
		$t_sort = array();
		while( $t_user = db_fetch_array( $t_result ) ) {
			$t_user_object = new MantisUser();
			$t_user_object->setFields( $t_user );

        	$t_user_name = string_attribute( $t_user['username'] );
        	$t_sort_name = utf8_strtolower( $t_user_name );
        	if( $t_show_realname && ( $t_user['realname'] <> '' ) ) {
            	$t_user_name = string_attribute( $t_user['realname'] );
            	if( $t_sort_by_last_name ) {
                	$t_sort_name_bits = explode( ' ', utf8_strtolower( $t_user_name ), 2 );
                	$t_sort_name = ( isset( $t_sort_name_bits[1] ) ? $t_sort_name_bits[1] . ', ' : '' ) . $t_sort_name_bits[0];
            	} else {
                	$t_sort_name = utf8_strtolower( $t_user_name );
            	}
        	}
        	$t_display[] = $t_user_name;
        	$t_sort[] = $t_sort_name;
			$t_users[] = $t_user_object;
    	}
    	array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );
    	$t_count = count( $t_sort );
    	for( $i = 0;$i < $t_count;$i++ ) {
        	$t_user_obj = $t_users[$i];
        	$t_options[$t_user_obj->id] = $t_user_obj; 
    	}
		return $t_options;
	}
}
