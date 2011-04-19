<?php
namespace MantisBT\Auth\Adapter;

# MantisBT - A PHP based bugtracking system

# @todo needs new license header

/**
 * Authentication API
 *
 * @package CoreAPI
 * @subpackage AuthenticationAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

class Basic extends AdapterAbstract implements AdapterInterface {
    /**
     * prepare/override the username provided from logon form (if necessary)
     * @todo when we rewrite authentication api for plugins, this should be merged with prepare_password and return some object
     * @param string $p_username
     * @return string prepared username
     * @access public
     */
    public function $this->prepareUsername( $p_username ) {
        return $_SERVER['REMOTE_USER'];
    }

    /**
     * prepare/override the password provided from logon form (if necessary)
     * @todo when we rewrite authentication api for plugins, this should be merged with prepare_username and return some object
     * @param string $p_password
     * @return string prepared password
     * @access public
     */
    public function preparePassword( $p_password ) {
        return $_SERVER['PHP_AUTH_PW'];
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
     * @todo has user dependency
     */
    public function authenticate( $p_username, $p_password, $p_perm_login = false ) {
        $t_user_id = user_get_id_by_name( $p_username );

        if ( false === $t_user_id ) {
            $t_user_id = $this->autoCreate( $p_username, $p_password );
            if( !$t_user_id ) {
                return false;
            }
        }
        return parent::authenticate( $p_username, $p_password, $p_perm_login );
    }

    /**
     * Check for authentication tokens, and display re-authentication page if needed.
     * Currently, if using BASIC authentication method
     * this function will always "authenticate" the user (do nothing).
     *
     * @return bool
     * @access public
     */
    public function reauthenticate() {
        return true;
    }
}
