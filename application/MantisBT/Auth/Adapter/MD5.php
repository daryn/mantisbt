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

class MD5 extends AdapterAbstract implements AdapterInterface {
    /**
     * Encrypt and return the plain password given
     *
     * When generating a new password, no salt should be passed in.
     * When encrypting a password to compare to a stored password, the stored
     *  password should be passed in as salt.  If the auth method is CRYPT then
     *  crypt() will extract the appropriate portion of the stored password as its salt
     *
     * @param string $p_password
     * @param string $p_salt salt, defaults to null
     * @param string $p_method logon method, defaults to null (use config login method)
     * @return string processed password, maximum PASSLEN chars in length
     * @access public
     */
     public function processPlainPassword( $p_password, $p_salt = null, $p_method = null ) {
        $t_processed_password = md5( $p_password );

        parent::processPlainPassword( $t_processed_password );
    }
}
