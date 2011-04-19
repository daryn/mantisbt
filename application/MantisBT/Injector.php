<?php
namespace MantisBT;

# MantisBT - a php based bugtracking system

# @todo needs new license text

/**
 * Class to hold factory methods for injecting dependencies.
 * Each static method is responsible for providing the constructor parameters for the object it is instantiating.
 * @package MantisBT
 * @subpackage classes
 */
class Injector {
    public static function buildApplication() {
        require_once( APPLICATION_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'constant_inc.php' );
        # Load user-defined constants (if required)
        if ( file_exists( CONFIG_PATH . DIRECTORY_SEPARATOR . 'custom_constants_inc.php' ) ) {
	        require_once( CONFIG_PATH . DIRECTORY_SEPARATOR . 'custom_constants_inc.php' );
        }

        try {
            # @todo part of config depends on the db so this must be fixed first
            $t_db = self::injectDatabase();
            $t_config_db = self::injectConfigDb( $t_db, self::injectConfigCache() );
            $t_user_cache = self::injectUserCache();
            $t_auth = self::injectAuth( self::injectAuthStorage( $t_config_db ) );
#            $t_auth_adapter = self::injectAuthAdapter( $t_config_db, self::injectUserDb( $t_db, $t_user_cache, $t_config_db->anonymous_account ) );

            $t_application = new ApplicationScope(
                APPLICATION_ENV,
                $t_config_db,
                $t_db,
                self::injectSession( $t_config_db->session_handler, $t_config_db->getGlobal( 'session_validation' ) ),
                $t_auth,
                self::injectTranslate( $t_config_db ),
                $t_user_cache
            );
        } catch ( Exception\Database $e ) {
            print $e;
            exit;
        } catch ( Exception $e ) {
            exit;
        }
    }

    public static function injectHelper( \ApplicationScope $application ) {
        return new Helper( $application );
    }

    /**
     * Loads and returns a database instance with the specified type and library.
     * @param string $type database type of the driver (e.g. pdo_pgsql)
     * @return MantisBT\Database driver object or null if error
     */
    public static function injectDatabase() {
        $t_ini = parse_ini_file( CONFIG_PATH . DIRECTORY_SEPARATOR . "db.ini", true );
		$t_type = explode( '_', $t_ini['db_type'] );

		switch( strtolower( $t_type[0] ) ) {
			case 'pdo':
				$t_driver_type = 'PDO';
		}
        $classname = __NAMESPACE__ . '\\Database\\' . $t_driver_type . ucfirst($t_type[1]);
        $t_db = new $classname( $t_ini['db_table_prefix'], $t_ini['db_table_suffix'] );
        $t_db->connect( $t_ini['hostname'], $t_ini['db_username'], $t_ini['db_password'], $t_ini['database_name'], $t_ini['dboptions'] );

        if( $t_db instanceof Database\DatabaseInterface ) {
            # require the db class to implement the correct interface
            return $t_db;
        } else {
            throw new Exception\Database( 'Unsupported Database Type: ' . $t_driver_type  );
        }
    }

    public static function injectConfigDb( Database\DatabaseInterface $p_db, $p_config_cache ) {
        # @todo probably should use application->_environment to load config

        # Allow an environment variable (defined in an Apache vhost for example) # to specify a config file to load to override other local settings
        $t_config = getenv( 'MANTIS_CONFIG' );
        if ( !$t_config ) {
            $t_config = CONFIG_PATH . DIRECTORY_SEPARATOR . 'custom.php';
        }

        return new Config\Db( $p_db, $p_config_cache );
    }

    public static function injectUserDb( $p_db, User\Cache $p_user_cache, $p_anonymous_account ) {
        return new User\Db( $p_db, $p_user_cache(), $p_anonymous_account );
    }

    public static function injectConfigCache() {
        return new Config\Cache();
    }

    public static function injectUserCache() {
        return new User\Cache();
    }

    public static function injectTranslator( Config $p_config ) {
        return new Translate( $p_config );
    }

    public static function injectAuth( Config $p_config, User\Db $p_user_db ) {
        return new Auth( );
    }

    public static function injectAuthStorage( Config $p_config ) {
        return new Auth\Storage\Cookie( $p_config );
    }

    public static function injectAuthAdapter( Config $p_config, User\Db $p_user_db ) {
        switch( $p_config->login_method ) {
            case HTTP_AUTH:
                return new Auth\Adapter\Http( $p_config, $p_user_db );
            break;
            case BASIC_AUTH:
                return new Auth\Adapter\Basic( $p_config, $p_user_db );
            break;
            case LDAP:
                return new Auth\Adapter\Ldap( $p_config, $p_user_db );
            break;
            case MD5:
                return new Auth\Adapter\MD5( $p_config, $p_user_db );
            break;
            case PLAIN:
            case CRYPT:
            Default:
                return new Auth( $p_config, $p_user_db );
            break;
        }
    }

    public static function injectRequest( ApplicationScope $p_application ) {
        $t_config = $p_application->getConfig();
        return new Request( $t_config->cookie_path, $t_config->cookie_domain, $t_config->cookie_time_length );
    }

    public static function injectController( ApplicationScope $p_application ) {
        return new Controller( $p_application->getRequest() );
    }

    public static function injectSession( $p_session_handler, $p_session_validation ) {
	    switch( utf8_strtolower( $p_session_handler ) ) {
		    case 'php':
		    	return new PHP( $p_session_id );
		    break;
    		case 'db':
    			# Not yet implemented
    		case 'memcached':
    			# Not yet implemented
    		default:
    			trigger_error( ERROR_SESSION_HANDLER_INVALID, ERROR );
    		break;
    	}

    	if ( ON == $p_session_validation && session_get( 'secure_session', false ) ) {
    		session_validate( $g_session );
    	}
    
    }

}
