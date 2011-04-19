<?php
namespace MantisBT\Session;

# MantisBT - A PHP based bugtracking system

# @todo add new license text

/**
 * Implementation of MantisBT\SessionInteface using
 * standard PHP sessions stored on the server's filesystem according
 * to PHP's session.* settings in 'php.ini'.
 * @package MantisBT
 * @subpackage classes
 */
class PHP implements SessionInterface {
	protected $id;
    private $config = null;

	/**
	 * Constructor
	 */
	function __construct( $p_config, $p_session_id=null ) {
#		global $g_cookie_secure_flag_enabled;
#		global $g_cookie_httponly_flag_enabled;

		$this->key = hash( 'whirlpool', 'session_key' . config_get_global( 'crypto_master_salt' ), false );

		# Save session information where specified or with PHP's default
		$t_session_save_path = config_get_global( 'session_save_path' );
		if( $t_session_save_path ) {
			session_save_path( $t_session_save_path );
		}

		# Handle session cookie and caching
		session_cache_limiter( 'private_no_expire' );
		if ( $g_cookie_httponly_flag_enabled ) {
			# The HttpOnly cookie flag is only supported in PHP >= 5.2.0
			session_set_cookie_params( 0, config_get( 'cookie_path' ), config_get( 'cookie_domain' ), $g_cookie_secure_flag_enabled, $g_cookie_httponly_flag_enabled );
		} else {
			session_set_cookie_params( 0, config_get( 'cookie_path' ), config_get( 'cookie_domain' ), $g_cookie_secure_flag_enabled );
		}

		# Handle existent session ID
		if ( !is_null( $p_session_id ) ) {
			session_id( $p_session_id );
		}

		# Initialize the session
		session_start();
		$this->id = session_id();

		# Initialize the keyed session store
		if ( !isset( $_SESSION[ $this->key ] ) ) {
			$_SESSION[ $this->key ] = array();
		}
	}

	/**
	 * get session data
	 * @param string $p_name
	 * @param mixed $p_default
	 */
	function get( $p_name, $p_default=null ) {
		if ( isset( $_SESSION[ $this->key ][ $p_name ] ) ) {
			return unserialize( $_SESSION[ $this->key ][ $p_name ] );
		}

		if( func_num_args() > 1 ) {
			return $p_default;
		}

		error_parameters( $p_name );
		trigger_error( ERROR_SESSION_VAR_NOT_FOUND, ERROR );
	}

	/**
	 * set session data
	 * @param string $p_name
	 * @param mixed $p_value
	 */
	function set( $p_name, $p_value ) {
		$_SESSION[ $this->key ][ $p_name ] = serialize( $p_value );
	}

	/**
	 * delete session data
	 * @param string $p_name
	 */
	function delete( $p_name ) {
		unset( $_SESSION[ $this->key ][ $p_name ] );
	}

	/**
	 * destroy session
	 */
	function destroy() {
		if( isset( $_COOKIE[session_name()] ) && !headers_sent() ) {
			gpc_set_cookie( session_name(), '', time() - 42000 );
		}

		unset( $_SESSION[ $this->key ] );
	}
}
