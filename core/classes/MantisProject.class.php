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
 *	A class that handles MantisBT Projects.
 *
 *	@package	CoreAPI
 *	@subpackage classes
 */
class MantisProject {
	private $id;
	private $name;
	private $status;
	private $enabled;
	private $view_state;
	private $access_min;
	private $file_path;
	private $description;
	private $category_id;
	private $inherit_global;	

	private $selected = false;

	private static $current_project = null;
	
	/**
	 *	@access private
	 */
	private static $_cacheById = array();

	/**
	 *	@access private
	 */
	private static $_cacheAccessibleByUserId = array();

	public function __get( $p_field_name ) {
		switch( $p_field_name ) {
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
			if( property_exists( $this, $t_key ) ) {
				$this->$t_key = $t_value;
			}
		}
	}

	public static function getCurrentProject() {
		if( !self::$current_project ) {
			$t_cookie_name = config_get( 'project_cookie' );

			$t_project_id = gpc_get_cookie( $t_cookie_name, null );
			if( null === $t_project_id ) {
				$t_pref = user_pref_get( auth_get_current_user_id(), ALL_PROJECTS, false );
				$t_project_id = $t_pref->default_project;
			} else {
				$t_project_id = explode( ';', $t_project_id );
				$t_project_id = $t_project_id[count( $t_project_id ) - 1];
			}

			try {
				$t_project = self::getById( $t_project_id );
				if( !$t_project->enabled || !access_has_project_level( VIEWER, $t_project_id ) ) {
					return ALL_PROJECTS;
				}
			} catch( Exception $e ) {
				# project doesn't exist
				return ALL_PROJECTS;
			}

			self::$current_project = $t_project; 
		}
		return self::$current_project;
	}

	public static function getAccessibleByUserId( $p_user_id, $p_show_disabled = false ) {
		if( is_array( self::$_cacheAccessibleByUserId ) && array_key_exists( $p_user_id, self::$_cacheAccessibleByUserId ) && auth_get_current_user_id() == $p_user_id && false == $p_show_disabled ) {
			return self::$_cacheAccessibleByUserId[$p_user_id];
		}
		$t_project_ids = user_get_accessible_projects( $p_user_id );
		foreach( $t_project_ids AS $t_id ) {
			$t_projects[$t_id] = self::getById( $t_id );
		}
		self::$_cacheAccessibleByUserId[$p_user_id] = $t_projects;
		return $t_projects; 
	}

	/**
	 *  Return a cached copy of a project.  If it is not cached
	 *	query the database for the project.  An error is thrown
	 *	if no project is found in the database.  The caller should
	 *	catch and handle the error as appropriate for the context.
 	 *	@param int $p_project_id
 	 *	@return associate array indexed by id.
	 */
	public static function getById( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );
		if( !isset( self::$_cacheById[$c_project_id] ) ) {
			# load up the project 
			$t_project_table = db_get_table( 'project' );
			$t_query = 'SELECT * FROM ' . $t_project_table . ' WHERE id=' . db_param();
			$t_result = db_query_bound( $t_query, Array( $c_project_id ) );

			if( 0 == db_num_rows( $t_result ) ) {
				throw new Exception( ERROR_PROJECT_NOT_FOUND );
			}

			$t_row = db_fetch_array( $t_result );
			$t_project = new MantisProject();
			$t_project->setFields( $t_row );	
	
			# assign it
			self::$_cacheById[$c_project_id] = $t_project;
		}
		return self::$_cacheById[$c_project_id];
	}
}
