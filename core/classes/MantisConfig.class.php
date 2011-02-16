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
 * MantisConfig
 *
 * @package MantisBT 
 * @subpackage classes 
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses helper_api.php
 * @uses utility_api.php
 */
require_api( 'authentication_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'helper_api.php' );
require_api( 'utility_api.php' );

class MantisConfig {
	protected static $cache;
	protected static $cacheAccess;
	protected static $cacheBypassLookup;
	protected static $cacheCanSetInDatabase;
	protected static $cacheDBTableExists;
	protected static $cacheEval;
	protected static $cacheFilled;
	protected static $cacheProject;
	protected static $cacheUser;

	/**
	 * Retrieves the value of a config option
	 *  This function will return one of (in order of preference):
	 *    1. value from cache
	 *    2. value from database
	 *     looks for specified config_id + current user + current project.
	 *     if not found, config_id + current user + all_project
	 *     if not found, config_id + default user + current project
	 *     if not found, config_id + default user + all_project.
	 *    3.use GLOBAL[config_id]
	 */
	public static function get( $p_option, $p_default = null, $p_user = null, $p_project = null ) {
		global $g_project_override;

		# bypass table lookup for certain options
		$t_bypass_lookup = !self::canSetInDatabase( $p_option );

		if( !$t_bypass_lookup ) {
			if( $g_project_override != null ) {
				$p_project = $g_project_override;
			}
			if( !self::$cacheDBTableExists ) {
				$t_config_table = db_get_table( 'config' );
				self::$cacheDBTableExists = ( TRUE === db_is_connected() ) && db_table_exists( $t_config_table );
			}

			if( self::$cacheDBTableExists ) {
				# prepare the user's list
				$t_users = array();
				if( null === $p_user ) {
					if( !isset( self::$cacheUser ) ) {
						$t_users[] = auth_is_user_authenticated() ? auth_get_current_user_id() : ALL_USERS;
						if( !in_array( ALL_USERS, $t_users ) ) {
							$t_users[] = ALL_USERS;
						}
						self::$cacheUser = $t_users;
					} else {
						$t_users = self::$cacheUser;
					}
				} else {
					$t_users[] = $p_user;
					if( !in_array( ALL_USERS, $t_users ) ) {
						$t_users[] = ALL_USERS;
					}
				}

				# prepare the projects list
				$t_projects = array();
				if(( null === $p_project ) ) {
					if( !isset( self::$cacheProject ) ) {
						$t_projects[] = auth_is_user_authenticated() ? helper_get_current_project() : ALL_PROJECTS;
						if( !in_array( ALL_PROJECTS, $t_projects ) ) {
							$t_projects[] = ALL_PROJECTS;
						}
						self::$cacheProject = $t_projects;
					} else {
						$t_projects = self::$cacheProject;
					}
				} else {
					$t_projects[] = $p_project;
					if( !in_array( ALL_PROJECTS, $t_projects ) ) {
						$t_projects[] = ALL_PROJECTS;
					}
				}

				if( !self::$cacheFilled ) {
					$t_config_table = db_get_table( 'config' );
					$query = "SELECT config_id, user_id, project_id, type, value, access_reqd FROM $t_config_table";
					$result = db_query_bound( $query );
					while( false <> ( $row = db_fetch_array( $result ) ) ) {
						$t_config = $row['config_id'];
						$t_user = $row['user_id'];
						$t_project = $row['project_id'];
						self::$cache[$t_config][$t_user][$t_project] = $row['type'] . ';' . $row['value'];
						self::$cacheAccess[$t_config][$t_user][$t_project] = $row['access_reqd'];
					}
					self::$cacheFilled = true;
				}

				if( isset( self::$cache[$p_option] ) ) {
					$t_found = false;
					reset( $t_users );
					while(( list(, $t_user ) = each( $t_users ) ) && !$t_found ) {
						reset( $t_projects );
						while(( list(, $t_project ) = each( $t_projects ) ) && !$t_found ) {
							if( isset( self::$cache[$p_option][$t_user][$t_project] ) ) {
								$t_value = self::$cache[$p_option][$t_user][$t_project];
								$t_found = true;
							}
						}
					}

					if( $t_found ) {
						list( $t_type, $t_raw_value ) = explode( ';', $t_value, 2 );

						switch( $t_type ) {
							case CONFIG_TYPE_FLOAT:
								$t_value = (float) $t_raw_value;
								break;
							case CONFIG_TYPE_INT:
								$t_value = (int) $t_raw_value;
								break;
							case CONFIG_TYPE_COMPLEX:
								$t_value = unserialize( $t_raw_value );
								break;
							case CONFIG_TYPE_STRING:
							default:
								$t_value = self::configEval( $t_raw_value );
						}
						return $t_value;
					}
				}
			}
		}
		return self::getGlobal( $p_option, $p_default );
	}

	/**
	 * Force config variable from a global to avoid recursion
	 *	@param string $p_option
	 *	@param mixed $p_default
	 *	@return mixed
	 */
	public static function getGlobal( $p_option, $p_default = null ) {
		if( isset( $GLOBALS['g_' . $p_option] ) ) {
			if( !isset( self::$cacheEval['g_' . $p_option] ) ) {
				$t_value = self::configEval( $GLOBALS['g_' . $p_option] );
				self::$cacheEval['g_' . $p_option] = $t_value;
			} else {
				$t_value = self::$cacheEval['g_' . $p_option];
			}
			return $t_value;
		} else {
			# unless we were allowing for the option not to exist by passing
			#  a default, trigger a WARNING
			if( null === $p_default ) {
				error_parameters( $p_option );
				trigger_error( ERROR_CONFIG_OPT_NOT_FOUND, WARNING );
			}
			return $p_default;
		}
	}

	/**
	 * Retrieves the access level needed to change a config value
	 *	@param string $p_option
	 *	@param array $p_user
	 *	@param array $p_project
	 */
	public static function getAccess( $p_option, $p_user = null, $p_project = null ) {
		if( !self::$cacheFilled ) {
			$t = self::get( $p_option, -1, $p_user, $p_project );
		}

		# prepare the user's list
		$t_users = array();
		if(( null === $p_user ) && ( auth_is_user_authenticated() ) ) {
			$t_users[] = auth_get_current_user_id();
		} else if( !in_array( $p_user, $t_users ) ) {
			$t_users[] = $p_user;
		}
		$t_users[] = ALL_USERS;

		# prepare the projects list
		$t_projects = array();
		if(( null === $p_project ) && ( auth_is_user_authenticated() ) ) {
			$t_selected_project = helper_get_current_project();
			if( ALL_PROJECTS <> $t_selected_project ) {
				$t_projects[] = $t_selected_project;
			}
		} else if( !in_array( $p_project, $t_projects ) ) {
			$t_projects[] = $p_project;
		}

		$t_found = false;
		if( isset( self::$cache[$p_option] ) ) {
			reset( $t_users );
			while(( list(, $t_user ) = each( $t_users ) ) && !$t_found ) {
				reset( $t_projects );
				while(( list(, $t_project ) = each( $t_projects ) ) && !$t_found ) {
					if( isset( self::$cache[$p_option][$t_user][$t_project] ) ) {
						$t_access = self::$cacheAccess[$p_option][$t_user][$t_project];
						$t_found = true;
					}
				}
			}
		}

		return $t_found ? $t_access : self::getGlobal( 'admin_site_threshold' );
	}

	/**
	 * Returns true if the specified config option exists (ie. a
	 * value or default can be found), false otherwise
	 *	@param string $p_option
	 *	@param array $p_user
	 *	@param array $p_project
	 */
	public static function isConfigSet( $p_option, $p_user = null, $p_project = null ) {

		if( !self::$cacheFilled ) {
			$t = self::get( $p_option, -1, $p_user, $p_project );
		}

		# prepare the user's list
		$t_users = array(
			ALL_USERS,
		);
		if(( null === $p_user ) && ( auth_is_user_authenticated() ) ) {
			$t_users[] = auth_get_current_user_id();
		} else if( !in_array( $p_user, $t_users ) ) {
			$t_users[] = $p_user;
		}
		$t_users[] = ALL_USERS;

		# prepare the projects list
		$t_projects = array(
			ALL_PROJECTS,
		);
		if(( null === $p_project ) && ( auth_is_user_authenticated() ) ) {
			$t_selected_project = helper_get_current_project();
			if( ALL_PROJECTS <> $t_selected_project ) {
				$t_projects[] = $t_selected_project;
			}
		} else if( !in_array( $p_project, $t_projects ) ) {
			$t_projects[] = $p_project;
		}

		$t_found = false;
		reset( $t_users );
		while(( list(, $t_user ) = each( $t_users ) ) && !$t_found ) {
			reset( $t_projects );
			while(( list(, $t_project ) = each( $t_projects ) ) && !$t_found ) {
				if( isset( self::$cache[$p_option][$t_user][$t_project] ) ) {
					$t_found = true;
				}
			}
		}

		if( $t_found ) {
			return true;
		}

		return isset( $GLOBALS['g_' . $p_option] );
	}

	/**
	 * Sets the value of the given config option to the given value
	 *  If the config option does not exist, an ERROR is triggered
	 */
	public static function set( $p_option, $p_value, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = DEFAULT_ACCESS_LEVEL ) {
		if( $p_access == DEFAULT_ACCESS_LEVEL ) {
			$p_access = config_get_global( 'admin_site_threshold' );
		}
		if( is_array( $p_value ) || is_object( $p_value ) ) {
			$t_type = CONFIG_TYPE_COMPLEX;
			$c_value = serialize( $p_value );
		} else if( is_float( $p_value ) ) {
			$t_type = CONFIG_TYPE_FLOAT;
			$c_value = (float) $p_value;
		} else if( is_int( $p_value ) || is_numeric( $p_value ) ) {
			$t_type = CONFIG_TYPE_INT;
			$c_value = db_prepare_int( $p_value );
		} else {
			$t_type = CONFIG_TYPE_STRING;
			$c_value = $p_value;
		}

		if( self::canSetInDatabase( $p_option ) ) {
			$c_option = $p_option;
			$c_user = db_prepare_int( $p_user );
			$c_project = db_prepare_int( $p_project );
			$c_access = db_prepare_int( $p_access );

			$t_config_table = db_get_table( 'config' );
			$query = "SELECT COUNT(*) from $t_config_table
					WHERE config_id = " . db_param() . " AND
						project_id = " . db_param() . " AND
						user_id = " . db_param();
			$result = db_query_bound( $query, Array( $c_option, $c_project, $c_user ) );

			$t_params = Array();
			if( 0 < db_result( $result ) ) {
				$t_set_query = "UPDATE $t_config_table
					SET value=" . db_param() . ", type=" . db_param() . ", access_reqd=" . db_param() . "
					WHERE config_id = " . db_param() . " AND
						project_id = " . db_param() . " AND
						user_id = " . db_param();
				$t_params = Array(
					$c_value,
					$t_type,
					$c_access,
					$c_option,
					$c_project,
					$c_user,
				);
			} else {
				$t_set_query = "INSERT INTO $t_config_table
						( value, type, access_reqd, config_id, project_id, user_id )
						VALUES
						(" . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ',' . db_param() . ' )';
				$t_params = Array(
					$c_value,
					$t_type,
					$c_access,
					$c_option,
					$c_project,
					$c_user,
				);
			}

			$result = db_query_bound( $t_set_query, $t_params );
		}

		config_set_cache( $p_option, $c_value, $t_type, $p_user, $p_project, $p_access );

		return true;
	}

	/**
	 * Sets the value of the given config option in the global namespace.
	 * Does *not* persist the value between sessions. If override set to
	 * false, then the value will only be set if not already existent.
	 *	@param string $p_option
	 *	@param mixed $p_value
	 *	@param bool $p_override
	 */
	function setGlobal( $p_option, $p_value, $p_override = true ) {
		if( $p_override || !isset( $GLOBALS['g_' . $p_option] ) ) {
			$GLOBALS['g_' . $p_option] = $p_value;
			unset( self::$cacheEval['g_' . $p_option] );
		}

		return true;
	}

	/**
	 * Sets the value of the given config option to the given value
	 *  If the config option does not exist, an ERROR is triggered
	 *	@param string $p_option
	 *	@param mixed $p_value
	 *	@param $p_type
	 *	@param int $p_user
	 *	@param int $p_project
	 *	@param int $p_access
	 */
	public static function setCache( $p_option, $p_value, $p_type, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = DEFAULT_ACCESS_LEVEL ) {
		if( $p_access == DEFAULT_ACCESS_LEVEL ) {
			$p_access = self::getGlobal( 'admin_site_threshold' );
		}

		self::$cache[$p_option][$p_user][$p_project] = $p_type . ';' . $p_value;
		self::$cacheAccess[$p_option][$p_user][$p_project] = $p_access;

		return true;
	}

	/**
	 * Checks if the specific configuration option can be deleted from the database.
	 *	@param string $p_option
	 *	@bool
	 */
	public static function canDelete( $p_option ) {
		return( utf8_strtolower( $p_option ) != 'database_version' );
	}

	/**
	 * Checks if the specific configuration option can be set in the database, otherwise it can only be set
	 * in the configuration file (config_inc.php / config_defaults_inc.php).
	 *	@param string $p_option
	 *	@return bool
	 */
	public static function canSetInDatabase( $p_option ) {
		if( isset( self::$cacheBypassLookup[$p_option] ) ) {
			return !self::$cacheBypassLookup[$p_option];
		}

		# bypass table lookup for certain options
		if( self::$cacheCanSetInDatabase == '' ) {
			self::$cacheCanSetInDatabase = '/' . implode( '|', self::getGlobal( 'global_settings' ) ) . '/';
		}
		$t_bypass_lookup = ( 0 < preg_match( self::$cacheCanSetInDatabase, $p_option ) );

		self::$cacheBypassLookup[$p_option] = $t_bypass_lookup;

		return !$t_bypass_lookup;
	}

	/**
	 * delete the config entry
	 */
	public static function configDelete( $p_option, $p_user = ALL_USERS, $p_project = ALL_PROJECTS ) {
		# bypass table lookup for certain options
		$t_bypass_lookup = !self::canSetInDatabase( $p_option );

		if(( !$t_bypass_lookup ) && ( TRUE === db_is_connected() ) && ( db_table_exists( db_get_table( 'config' ) ) ) ) {
			if( !self::canDelete( $p_option ) ) {
				return;
			}

			$t_config_table = db_get_table( 'config' );

			$c_user = db_prepare_int( $p_user );
			$c_project = db_prepare_int( $p_project );
			$query = "DELETE FROM $t_config_table
				WHERE config_id = " . db_param() . " AND
					project_id=" . db_param() . " AND
					user_id=" . db_param();

			$result = @db_query_bound( $query, Array( $p_option, $c_project, $c_user ) );
		}

		self::flushCache( $p_option, $p_user, $p_project );
	}

	/**
	 * Delete the specified option for the specified user.across all projects.
	 * @param $p_option - The configuration option to be deleted.
	 * @param $p_user_id - The user id
	 */
	public static function deleteForUser( $p_option, $p_user_id ) {
		if( !self::canDelete( $p_option ) ) {
			return;
		}

		$t_config_table = db_get_table( 'config' );
		$c_user_id = db_prepare_int( $p_user_id );

		# Delete the corresponding bugnote texts
		$query = "DELETE FROM $t_config_table
					WHERE config_id=" . db_param() . " AND user_id=" . db_param();
		db_query_bound( $query, array( $p_option, $c_user_id ) );
	}

	/**
	 * delete the config entry
	 * @param $p_project_id
	 */
	public static function deleteProject( $p_project_id = ALL_PROJECTS ) {
		$t_config_table = db_get_table( 'config' );
		$c_project = db_prepare_int( $p_project );
		$query = "DELETE FROM $t_config_table
				WHERE project_id=" . db_param();

		$result = @db_query_bound( $query, Array( $c_project ) );

		# flush cache here in case some of the deleted configs are in use.
		self::flushCache();
	}

	/**
	 * delete the config entry from the cache
	 * @@@ to be used sparingly
	 *	@param string $p_option
	 *	@param int $p_user_id
	 *	@param int $p_project_id
	 */
	public static function flushCache( $p_option = '', $p_user_id = ALL_USERS, $p_project_id = ALL_PROJECTS ) {

		if( '' !== $p_option ) {
			unset( self::$cache[$p_option][$p_user][$p_project] );
			unset( self::$cacheAccess[$p_option][$p_user][$p_project] );
		} else {
			unset( self::$cache );
			unset( self::$cacheAccess );
			self::$cacheFilled = false;
		}
	}

	/**
	 * Checks if an obsolete configuration variable is still in use.  If so, an error
	 * will be generated and the script will exit.  This is called from admin_check.php.
	 *	@param $p_var
	 *	@param $p_replace
	 */
	public static function obsolete( $p_var, $p_replace ) {
		# @@@ we could trigger a WARNING here, once we have errors that can
		#     have extra data plugged into them (we need to give the old and
		#     new config option names in the warning text)

		if( self::isConfigSet( $p_var ) ) {
			$t_description = '<p><strong>Warning:</strong> The configuration option <tt>$g_' . $p_var . '</tt> is now obsolete</p>';
			if( is_array( $p_replace ) ) {
				$t_info = 'please see the following options: <ul>';
				foreach( $p_replace as $t_option ) {
					$t_info .= '<li>$g_' . $t_option . '</li>';
				}
				$t_info .= '</ul>';
			} else if( !is_blank( $p_replace ) ) {
				$t_info = 'please use <tt>$g_' . $p_replace . '</tt> instead.';
			} else {
				$t_info = '';
			}

			print_test_warn_row( $t_description, false, $t_info );
		}
	}

	/**
	 * check for recursion in defining config variables
	 * If there is a %text% in the returned value, re-evaluate the "text" part and replace
	 * the string
	 *	@param mixed $p_value
	 *	@return string evaluated string
	 */
	public static function configEval( $p_value ) {
		$t_value = $p_value;
		if( !empty( $t_value ) && is_string( $t_value ) && !is_numeric( $t_value ) ) {
			if( 0 < preg_match_all( '/(?:^|[^\\\\])(%([^%]+)%)/U', $t_value, $t_matches ) ) {
				$t_count = count( $t_matches[0] );
				for( $i = 0;$i < $t_count;$i++ ) {
					# $t_matches[0][$i] is the matched string including the delimiters
					# $t_matches[1][$i] is the target parameter string
					$t_repl = self::get( $t_matches[2][$i] );
					$t_value = str_replace( $t_matches[1][$i], $t_repl, $t_value );
				}
			}
			$t_value = str_replace( '\\%', '%', $t_value );
		}
		return $t_value;
	}

	/**
	 * list of configuration variable which may expose webserver details and shouldn't be
	 * exposed to users or webservices
	 *	@param string $p_config_var The name of the configuration to check
	 *	@bool
	 */
	public static function isPrivate( $p_config_var ) {
		switch( $p_config_var ) {
			case 'hostname':
			case 'db_username':
			case 'db_password':
			case 'database_name':
			case 'db_schema':
			case 'db_type':
			case 'master_crypto_salt':
			case 'smtp_host':
			case 'smtp_username':
			case 'smtp_password':
			case 'smtp_connection_mode':
			case 'smtp_port':
			case 'email_send_using_cronjob':
			case 'absolute_path':
			case 'core_path':
			case 'class_path':
			case 'library_path':
			case 'language_path':
			case 'use_iis':
			case 'session_save_path':
			case 'session_handler':
			case 'session_validation':
			case 'global_settings':
			case 'system_font_folder':
			case 'phpMailer_method':
			case 'default_avatar':
			case 'file_upload_ftp_server':
			case 'file_upload_ftp_user':
			case 'file_upload_ftp_pass':
			case 'attachments_file_permissions':
			case 'file_upload_method':
			case 'absolute_path_default_upload_folder':
			case 'ldap_server':
			case 'plugin_path':
			case 'ldap_root_dn':
			case 'ldap_organization':
			case 'ldap_uid_field':
			case 'ldap_bind_dn':
			case 'ldap_bind_passwd':
			case 'use_ldap_email':
			case 'ldap_protocol_version':
			case 'login_method':
			case 'cookie_path':
			case 'cookie_domain':
			case 'bottom_include_page':
			case 'top_include_page':
			case 'css_include_file':
			case 'css_rtl_include_file':
			case 'meta_include_file':
			case 'log_level':
			case 'log_destination':
			case 'dot_tool':
			case 'neato_tool':
			case 'twitter_username':
			case 'twitter_password':
				return true;
		}

		return false;
	}
}
