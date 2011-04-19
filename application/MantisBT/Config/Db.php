<?php
namespace MantisBT\Config;

# MantisBT - a php based bugtracking system

# @todo add new license text

/**
 *	Config\Db class to handle loading and modifying user data.
 * @package MantisBT
 * @subpackage classes
 */
class Db {
    protected $db = null;
    protected $cache = null;
    protected $table = null;
    protected $configFound = false;

    /**
     *  @todo these are temporary until i figure out how to handle global and private configs
     */
    protected static $global = array(
    	'global_settings',
    	'admin_checks',
    	'allow_signup',
    	'anonymous',
    	'compress_html',
    	'content_expire',
    	'cookie',
    	'crypto_master_salt',
    	'custom_headers',
    	'database_name',
    	'^db_',
    	'display_errors',
    	'form_security_',
    	'hostname',
    	'html_valid_tags',
    	'language',
    	'login_method',
    	'plugins_enabled',
    	'plugins_installed',
    	'session_',
    	'show_detailed_errors',
    	'show_queries_',
    	'stop_on_errors',
    	'use_javascript',
    	'version_suffix',
    	'[^_]file[(_(?!threshold))$]',
    	'[^_]path[_$]',
    	'_page$',
    	'_table$',
	    '_url$',
    );

    protected static $globalRegex = null;

    protected static $private = array(
        'hostname',
		'db_username',
		'db_password',
		'database_name',
		'db_schema',
		'db_type',
		'master_crypto_salt',
		'smtp_host',
		'smtp_username',
		'smtp_password',
		'smtp_connection_mode',
		'smtp_port',
		'email_send_using_cronjob',
		'absolute_path',
		'core_path',
		'class_path',
		'library_path',
		'language_path',
		'session_save_path',
		'session_handler',
		'session_validation',
		'global_settings',
		'system_font_folder',
		'phpMailer_method',
		'default_avatar',
		'file_upload_ftp_server',
		'file_upload_ftp_user',
		'file_upload_ftp_pass',
		'attachments_file_permissions',
		'file_upload_method',
		'absolute_path_default_upload_folder',
		'ldap_server',
		'plugin_path',
		'ldap_root_dn',
		'ldap_organization',
		'ldap_uid_field',
		'ldap_bind_dn',
		'ldap_bind_passwd',
		'use_ldap_email',
		'ldap_protocol_version',
		'login_method',
		'cookie_path',
		'cookie_domain',
		'bottom_include_page',
		'top_include_page',
		'css_include_file',
		'css_rtl_include_file',
		'meta_include_file',
		'log_level',
		'log_destination',
		'dot_tool',
		'neato_tool',
		'twitter_username',
        'twitter_password',
    );

    public function __construct( $p_db, $p_cache ) {
        # @todo check for interface compliance
        $this->db = $p_db;
        $this->table = $p_db->getTable( 'config' );
        if( $p_cache instanceof CacheInterface ) {
            $this->cache = $p_cache;
        }
		self::$globalRegex = '/' . implode( '|', self::$global ) . '/';

        # @todo temporary, remove this...
        $this->loadDefaultsFromFile();
        $this->configFound = $this->loadCustomFromFile();
        $this->cache->setFull( true );
print_r( $this );
exit;
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
	 */
	public function get( $p_option, $p_default = null, $p_user = null, $p_project = null ) {
	#	global $g_project_override;

	#	if( $g_project_override != null ) {
	#			$p_project = $g_project_override;
	#	}

        $t_config = $this->cache->search( $p_option, $p_user, $p_project );
		if( $t_config ) {
			switch( $t_config->type ) {
				case CONFIG_TYPE_FLOAT:
					$t_value = (float) $t_config->value;
					break;
				case CONFIG_TYPE_INT:
					$t_value = (int) $t_config->value;
					break;
				case CONFIG_TYPE_COMPLEX:
					$t_value = unserialize( $t_config->value );
					break;
				case CONFIG_TYPE_STRING:
				default:
					$t_value = $this->configEval( $t_config->value );
			}
			return $t_config->value;
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

		$t_value = $this->configEval( $p_option );
		return $this->cache[$p_option];


			# unless we were allowing for the option not to exist by passing
			#  a default, trigger a WARNING
			if( null === $p_default ) {
				#error_parameters( $p_option );
				trigger_error( ERROR_CONFIG_OPT_NOT_FOUND, WARNING );
			}
			return $p_default;
		#}
	}

	/**
	 * check for recursion in defining config variables
	 * If there is a %text% in the returned value, re-evaluate the "text" part and replace
	 * the string
	 *	@param mixed $p_value
	 *	@return string evaluated string
	 */
	public function configEval( $p_value ) {
		$t_value = $p_value;
		if( !empty( $t_value ) && is_string( $t_value ) && !is_numeric( $t_value ) ) {
			if( 0 < preg_match_all( '/(?:^|[^\\\\])(%([^%]+)%)/U', $t_value, $t_matches ) ) {
				$t_count = count( $t_matches[0] );
				for( $i = 0;$i < $t_count;$i++ ) {
					# $t_matches[0][$i] is the matched string including the delimiters
					# $t_matches[1][$i] is the target parameter string
					$t_repl = $this->get( $t_matches[2][$i] );
					$t_value = str_replace( $t_matches[1][$i], $t_repl, $t_value );
				}
			}
			$t_value = str_replace( '\\%', '%', $t_value );
		}
		return $t_value;
	}

	/**
	 * Sets the value of the given config option to the given value
	 *  If the config option does not exist, an ERROR is triggered
	 *	@param string $p_option
	 *	@param mixed $p_value
	 *	@param int $p_user Optional. Default is no user
	 *	@param int $p_project Optional. Default is all projects
	 *	@param int $p_access Optional. Default is configured admin threshold
	 *	@return true if successful or triggers an error if not
     *  @todo  has database dependency
	 */
	public function set( $p_option, $p_value, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = DEFAULT_ACCESS_LEVEL ) {
		if( $p_access == DEFAULT_ACCESS_LEVEL ) {
			$p_access = $this->getGlobal( 'admin_site_threshold' );
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

		if( $this->canSetInDatabase( $p_option ) ) {
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

		$this->setCache( $p_option, $c_value, $t_type, $p_user, $p_project, $p_access );

		return true;
	}

	/**
	 * delete the config entry
	 *	@param string $p_option
	 *	@param int $p_user Optional
	 *	@param int $p_project Optional
	 */
	public function delete( $p_option, $p_user = ALL_USERS, $p_project = ALL_PROJECTS ) {
		# bypass table lookup for certain options
		$t_bypass_lookup = !$this->canSetInDatabase( $p_option );

		if( !$this->canDelete( $p_option ) ) {
			return;
		}

		$t_query = "DELETE FROM {$this->table}
			WHERE config_id = " . $this->db->param() . " AND
				project_id=" . $this->db->param() . " AND
				user_id=" . $this->db->param();

		$t_result = @$this->db->queryBound( $t_query, Array( $p_option, $p_project, $p_user ) );

        # flush
        $this->cache->clear( $p_option, $p_user, $p_project );
	}

	/**
	 * Delete the specified option for the specified user.across all projects.
	 * @param $p_option - The configuration option to be deleted.
	 * @param $p_user_id - The user id
	 */
	public function deleteForUser( $p_option, $p_user_id ) {
		if( !$this->canDelete( $p_option ) ) {
			return;
		}

		# Delete the corresponding bugnote texts
		$t_query = "DELETE FROM {$this->table}
					WHERE config_id=" . $this->db->param() . " AND user_id=" . $this->db->param();
		$this->db->queryBound( $t_query, array( $p_option, $p_user_id ) );
	}

	/**
	 * delete the config entry
	 * @param $p_project_id
	 */
	public function deleteProject( $p_project_id = ALL_PROJECTS ) {
		$c_project = db_prepare_int( $p_project );
		$t_query = "DELETE FROM {$this->table}
				WHERE project_id=" . $this->db->param();

		$t_result = @$this->db->queryBound( $t_query, array( $p_project ) );

		# flush cache here in case some of the deleted configs are in use. maybe just the project cache?
        $this->cache->clear();
	}

	/**
	 * Checks if the specific configuration option can be deleted from the database.
     * @todo modify so that it can't delete any defaults
	 *	@param string $p_option
	 *	@bool
	 */
	public function canDelete( $p_option ) {
		return( utf8_strtolower( $p_option ) != 'database_version' );
	}

    public function loadDefaultsFromDb() {
        $t_query = "SELECT config_id, user_id, project_id, type, value, access_reqd FROM {$this->table}";
        $t_result = $this->dbQueryBound( $t_query );
        while( false <> ( $t_row = $this->db->fetchArray( $t_result ) ) ) {
            $t_row['_global'] = $this->isGlobal( $t_row['config_id'] );
            $t_row['_private'] = $this->isPrivate( $t_row['config_id'] );
            $t_config = new Model( $t_row );
            $this->cache->add( $t_config );
        }
    }

    public function loadDefaultsFromFile( $p_file=null ) {
        if( is_null( $p_file ) ) {
            # ensure these are not global vars
            # Include default configuration settings
            include( CONFIG_PATH . DIRECTORY_SEPARATOR . 'config_defaults_inc.php' );
        } else if( file_exists( $p_file ) ) {
            include $p_file;
        }
        $t_vars = get_defined_vars();

        $t_row['config_id'] = 'protocol';
        # new db setting for defaults.  should never be overwritten except by the install script
        $t_row['project_id'] = -1;
        $t_row['user_id'] = ALL_USERS;
        $t_row['access_reqd'] = ADMINISTRATOR;
        $t_row['type'] = '';
        $t_row['value'] = $t_protocol;
        $t_row['_global'] = $this->isGlobal( 'protocol' );
        $t_row['_private'] = $this->isPrivate( 'protocol');

        $t_config = new Model( $t_row );
        $this->cache->add( $t_config );

        foreach( $t_vars AS $t_key=>$t_value ) {
            if( strpos( $t_key, 'g_' ) === 0 ) {
                $t_config_id = substr( $t_key, 2 );
                $t_row['config_id'] = $t_config_id;
                $t_row['project_id'] = -1;
                $t_row['user_id'] = ALL_USERS;
                $t_row['access_reqd'] = ADMINISTRATOR;
                $t_row['type'] = '';
                $t_row['value'] = $t_value;
                $t_row['_global'] = $this->isGlobal( $t_config_id );
                $t_row['_private'] = $this->isPrivate( $t_config_id );

                $t_config = new Model( $t_row );
                $this->cache->add( $t_config );
            }
        }
    }

    public function loadCustomFromFile( $p_config_file=null ) {
        if( is_null( $p_config_file ) ) {
            $p_config_file = CONFIG_PATH . DIRECTORY_SEPARATOR . 'config_inc.php';
        }

        $t_protocol = $this->cache->search('protocol', ALL_USERS, -1 )->value;
        # config_inc may not be present if this is a new install
        if ( file_exists( $p_config_file ) ) {
            $t_config = array();
            include( $p_config_file );
            $t_vars = get_defined_vars();

            # overwrite defaults
            foreach( $t_vars AS $t_key=>$t_value ) {
                if( strpos( 'g_', $t_key ) !== false ) {
                    $t_config_id = str_replace( 'g_', '', $t_key );
                    $t_row['config_id'] = $t_config_id;
                    $t_row['project_id'] = ALL_PROJECTS;
                    $t_row['user_id'] = ALL_USERS;
                    $t_row['access_reqd'] = ADMINISTRATOR;
                    $t_row['type'] = '';
                    $t_row['value'] = $t_value;
                    $t_row['_global'] = $this->isGlobal( $t_config_id );
                    $t_row['_private'] = $this->isPrivate( $t_config_id );
                    $t_config = new Model( $t_row );
                    $this->cache->add( $t_config );
                }
            }
	        return true;
        } else {
            return false;
        }
    }

	/**
	 * list of configuration variable which may expose webserver details and shouldn't be
	 * exposed to users or webservices
	 *	@param string $p_config_var The name of the configuration to check
	 *	@return bool
	 */
	public function isPrivate( $p_config_var ) {
        return in_array( $p_config_var, self::$private );
	}

	/**
	 * Checks if the specific configuration option can be set in the database, otherwise it can only be set
	 * in the configuration file (config_inc.php / config_defaults_inc.php).
	 *	@param string $p_option
	 *	@return bool
	 */
	public function isGlobal( $p_option ) {
		return !( 0 < preg_match( self::$globalRegex, $p_option ) );
	}
}
