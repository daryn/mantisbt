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

class Ldap extends AdapterAbstract implements AdapterInterface {
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
     * @todo has ldap dependency
     */
    public function authenticate( $p_username, $p_password, $p_perm_login = false ) {
        $t_user_id = user_get_id_by_name( $p_username );

        if ( false === $t_user_id ) {
            if ( ldap_authenticate_by_username( $p_username, $p_password ) ) {
                $t_auto_create = true;
                $t_user_id = $this->autoCreate( $p_username, $p_password );
                if( !$t_user_id ) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return parent::authenticate( $p_username, $p_password, $p_perm_login );
    }

    /**
     * Identicates whether to bypass logon form e.g. when using http auth
     * @return bool
     * @access public
     */
    public function automaticLogonBypassForm() {
        return false;
    }

    /**
     * Return true if the password for the user id given matches the given
     * password (taking into account the global login method)
     * @param int $p_user_id User id to check password against
     * @param string $p_test_password Password
     * @return bool indicating whether password matches given the user id
     * @access public
     * @todo has ldap dependency
     */
    public function doesPasswordMatch( $p_user_id, $p_test_password ) {
        return ldap_authenticate( $p_user_id, $p_test_password );
    }
}
