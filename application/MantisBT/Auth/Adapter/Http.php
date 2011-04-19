<?php
namespace MantisBT\Auth\Adapter;

# MantisBT - A PHP based bugtracking system

# @todo Needs new license header

/**
 * HTTP Authentication
 *
 * @package CoreAPI
 * @subpackage AuthenticationAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
class Http extends AdapterAbstract implements AdapterInterface {

    /**
     * prepare/override the username provided from logon form (if necessary)
     * @todo when we rewrite authentication api for plugins, this should be merged with prepare_password and return some object
     * @param string $p_username
     * @return string prepared username
     * @access public
     * @remove httpPrompt to templates
     */
    public function $this->prepareUsername( $p_username ) {
        if( !$this->httpIsLogoutPending() ) {
            if( isset( $_SERVER['PHP_AUTH_USER'] ) ) {
                $f_username = $_SERVER['PHP_AUTH_USER'];
            }
        } else {
            $this->httpSetLogoutPending( false );
            $this->httpPrompt();

            /* calls exit */
            return;
        }
        return $f_username;
    }

    /**
     * prepare/override the password provided from logon form (if necessary)
     * @todo when we rewrite authentication api for plugins, this should be merged with prepare_username and return some object
     * @param string $p_password
     * @return string prepared password
     * @access public
     */
    public function preparePassword( $p_password ) {
        if( !$this->httpIsLogoutPending() ) {
            /* this will never get hit - see $this->prepareUsername */
            if( isset( $_SERVER['PHP_AUTH_PW'] ) ) {
                $f_password = $_SERVER['PHP_AUTH_PW'];
            }
        } else {
            $this->httpSetLogoutPending( false );
            $this->httpPrompt();

            /* calls exit */
            return;
        }
        return $f_password;
    }

    /**
     * Attempt to login the user with the given password
     * If the user fails validation, false is returned
     * If the user passes validation, the cookies are set and
     * true is returned.  If $p_perm_login is true, the long-term
     * cookie is created.
     * @param string $p_username a prepared username
     * @param string $p_password a prepared password
     * @param bool $p_perm_login whether to create a long-term cookie
     * @return bool indicates if authentication was successful
     * @access public
     */
    public function authenticate( $p_username, $p_password, $p_perm_login = false ) {
        $t_user_id = user_get_id_by_name( $p_username );

        if ( false === $t_user_id ) {
            return false;
        }

        return parent::authenticate( $p_username, $p_password, $p_perm_login );
    }

    /**
     * Identicates whether to bypass logon form e.g. when using http auth
     * @return bool
     * @access public
     */
    public function automaticLogonBypassForm() {
        return true;
    }

    /**
     * Check for authentication tokens, and display re-authentication page if needed.
     * Currently, if using HTTP authentication methods,
     * this function will always "authenticate" the user (do nothing).
     *
     * @return bool
     * @access public
     */
    public function reauthenticate() {
        return true;
    }

    /**
     *
     * @access public
     * @todo has print dependency
     * @todo has lang dependency
     */
    public function httpPrompt() {
    	header( 'HTTP/1.0 401 Authorization Required' );
    	header( 'WWW-Authenticate: Basic realm="' . lang_get( 'http_auth_realm' ) . '"' );
    	header( 'status: 401 Unauthorized' );

    	echo '<p class="center error-msg">' . error_string( ERROR_ACCESS_DENIED ) . '</p>';
    	print_bracket_link( 'main_page.php', lang_get( 'proceed' ) );

    	exit;
    }

    /**
     * @param bool $p_pending
     * @access public
     * @todo has config dependency
     * @todo has gpc dependency
     */
    public function httpSetLogoutPending( $p_pending ) {
    	$t_cookie_name = config_get( 'logout_cookie' );

    	if( $p_pending ) {
    		gpc_set_cookie( $t_cookie_name, '1', false );
    	} else {
    		$t_cookie_path = config_get( 'cookie_path' );
    		gpc_clear_cookie( $t_cookie_name, $t_cookie_path );
    	}
    }

    /**
     * @return bool
     * @access public
     * @todo has config dependency
     * @todo has gpc dependency
     */
    public function httpIsLogoutPending() {
    	$t_cookie_name = config_get( 'logout_cookie' );
    	$t_cookie = gpc_get_cookie( $t_cookie_name, '' );

    	return( $t_cookie > '' );
    }
}
