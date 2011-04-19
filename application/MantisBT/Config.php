<?php
namespace MantisBT;

# MantisBT - A PHP based bugtracking system

# @todo needs new license text

/**
 * MantisBT\Config
 *
 * @package MantisBT
 * @subpackage classes
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

class Config {
    protected $db = null;

    protected $global = array();

#protected static $cacheAccess;
#protected static $cacheBypassLookup;
#protected static $cacheCanSetInDatabase;
#protected static $cacheDBTableExists;
#protected static $cacheEval;
#protected static $cacheFilled;
#protected static $cacheProject;
#protected static $cacheUser;

    private $configFound = false;

    public function __construct( $p_config_file, Database\DatabaseInterface $p_db, CacheInterface $p_cache ) {
        $this->db = $p_db;
        $this->cache = $p_cache;
    }

	public function __get( $p_field_name ) {
		return $this->get( $p_field_name );
	}

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
	 *	@param string $p_option
	 *	@param mixed $p_default
	 *	@param int $p_user
	 *	@param int $p_project
	 *	@return mixed
     *  @todo has database dependency
     *  @todo has auth dependency
	 */
	public function get( $p_option, $p_default = null, $p_user = null, $p_project = null ) {
		global $g_project_override;

		if( $g_project_override != null ) {
				$p_project = $g_project_override;
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

#			if( !self::$cacheFilled ) {
#				$t_config_table = db_get_table( 'config' );
#				$query = "SELECT config_id, user_id, project_id, type, value, access_reqd FROM $t_config_table";
#				$result = db_query_bound( $query );
#				while( false <> ( $row = db_fetch_array( $result ) ) ) {
#					$t_config = $row['config_id'];
#					$t_user = $row['user_id'];
#					$t_project = $row['project_id'];
#					self::$cache[$t_config][$t_user][$t_project] = $row['type'] . ';' . $row['value'];
#					self::$cacheAccess[$t_config][$t_user][$t_project] = $row['access_reqd'];
#				}
#				self::$cacheFilled = true;
#			}

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
							$t_value = $this->configEval( $t_raw_value );
					}
					return $t_value;
				}
			}
		}
		#return $this->getGlobal( $p_option, $p_default );
	}

	/**
	 * Force config variable from a global to avoid recursion
	 *	@param string $p_option
	 *	@param mixed $p_default
	 *	@return mixed
     *  @todo throw an error rather than trigger
	 */
	public function getGlobal( $p_option, $p_default = null ) {
        #if( array_key_exists( $p_option, $this->cache ) )...

		if( isset( $GLOBALS['g_' . $p_option] ) ) {
			if( !isset( self::$cacheEval['g_' . $p_option] ) ) {
				$t_value = $this->configEval( $GLOBALS['g_' . $p_option] );
				self::$cacheEval['g_' . $p_option] = $t_value;
			} else {
				$t_value = self::$cacheEval['g_' . $p_option];
		    }
			return $this->cache[$p_option];
		} else {
			# unless we were allowing for the option not to exist by passing
			#  a default, trigger a WARNING
			if( null === $p_default ) {
				#error_parameters( $p_option );
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
	 *	@return int access level
     *  @todo has auth dependency
     *  @todo has helper dependency
	 */
	public function getAccess( $p_option, $p_user = null, $p_project = null ) {
		if( !self::$cacheFilled ) {
			$t = $this->get( $p_option, -1, $p_user, $p_project );
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

		return $t_found ? $t_access : $this->getGlobal( 'admin_site_threshold' );
	}

	/**
	 * Returns true if the specified config option exists (ie. a
	 * value or default can be found), false otherwise
	 *	@param string $p_option
	 *	@param array $p_user
	 *	@param array $p_project
	 *	@return bool
     *  @todo has auth dependency
     *  @todo has helper dependency
	 */
	public function isConfigSet( $p_option, $p_user = null, $p_project = null ) {

		if( !self::$cacheFilled ) {
			$t = $this->get( $p_option, -1, $p_user, $p_project );
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
	 * Sets the value of the given config option in the global namespace.
	 * Does *not* persist the value between sessions. If override set to
	 * false, then the value will only be set if not already existent.
	 *	@param string $p_option
	 *	@param mixed $p_value
	 *	@param bool $p_override
	 *	@return true
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
	 *	@param int $p_user Optional
	 *	@param int $p_project Optional
	 *	@param int $p_access Optional
	 *	@return true
	 */
	public function setCache( $p_option, $p_value, $p_type, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = DEFAULT_ACCESS_LEVEL ) {
		if( $p_access == DEFAULT_ACCESS_LEVEL ) {
			$p_access = $this->getGlobal( 'admin_site_threshold' );
		}

		self::$cache[$p_option][$p_user][$p_project] = $p_type . ';' . $p_value;
		self::$cacheAccess[$p_option][$p_user][$p_project] = $p_access;

		return true;
	}

	/**
	 * Checks if an obsolete configuration variable is still in use.  If so, an error
	 * will be generated and the script will exit.  This is called from admin_check.php.
	 *	@param $p_var
	 *	@param $p_replace
     *  @todo this should throw an error rather than print
	 */
	public function obsolete( $p_var, $p_replace ) {
		# @@@ we could trigger a WARNING here, once we have errors that can
		#     have extra data plugged into them (we need to give the old and
		#     new config option names in the warning text)

		if( $this->isConfigSet( $p_var ) ) {
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
}
