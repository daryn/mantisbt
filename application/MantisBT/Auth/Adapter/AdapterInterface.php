<?php
namespace MantisBT\Auth\Adapter;

# MantisBT - A PHP based bugtracking system

# @todo add new license text

/**
 * Authentication API
 *
 * @package CoreAPI
 * @subpackage AuthenticationAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

interface AdapterInterface {
    /**
     * Check that there is a user logged-in and authenticated
     * If the user's account is disabled they will be logged out
     * If there is no user logged in, redirect to the login page
     * If parameter is given it is used as a URL to redirect to following
     * successful login.  If none is given, the URL of the current page is used
     * @param string $p_return_page Page to redirect to following successful logon, defaults to current page
     * @access public
     */
    public function ensureUserAuthenticated( $p_return_page = '' );

    /**
     * Return true if there is a currently logged in and authenticated user, false otherwise
     *
     * @param boolean auto-login anonymous user
     * @return bool
     * @access public
     */
    public function isUserAuthenticated();

    /**
     * prepare/override the username provided from logon form (if necessary)
     * @todo when we rewrite authentication api for plugins, this should be merged with prepare_password and return some object
     * @param string $p_username
     * @return string prepared username
     * @access public
     */
    public function $this->prepareUsername( $p_username );

    /**
     * prepare/override the password provided from logon form (if necessary)
     * @todo when we rewrite authentication api for plugins, this should be merged with prepare_username and return some object
     * @param string $p_password
     * @return string prepared password
     * @access public
     */
    public function preparePassword( $p_password );

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
    public function attemptLogin( $p_username, $p_password, $p_perm_login = false );

    /**
     * Allows scripts to login using a login name or ( login name + password )
     * @param string $p_username username
     * @param string $p_password username
     * @return bool indicates if authentication was successful
     * @access public
     */
    public function attemptScriptLogin( $p_username, $p_password = null );

    /**
     * Logout the current user and remove any remaining cookies from their browser
     * Returns true on success, false otherwise
     * @access public
     */
    public function logout();

    /**
     * Identicates whether to bypass logon form e.g. when using http auth
     * @return bool
     * @access public
     */
    public function automaticLogonBypassForm();

    /**
     * Return true if the password for the user id given matches the given
     * password (taking into account the global login method)
     * @param int $p_user_id User id to check password against
     * @param string $p_test_password Password
     * @return bool indicating whether password matches given the user id
     * @access public
     */
    public function doesPasswordMatch( $p_user_id, $p_test_password );

    /**
     * Encrypt and return the plain password given, as appropriate for the current
     *  global login method.
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
     public function processPlainPassword( $p_password, $p_salt = null, $p_method = null );

    /**
     * Generate a random 16 character password.
     * @todo Review use of $p_email within mantis
     * @param string $p_email unused
     * @return string 16 character random password
     * @access public
     */
    public function generateRandomPassword( $p_email );

    /**
     * Generate a confirmation code to validate password reset requests.
     * @param int $p_user_id User ID to generate a confirmation code for
     * @return string Confirmation code (384bit) encoded according to the base64 with URI safe alphabet approach described in RFC4648
     * @access public
     */
    public function generateConfirmHash( $p_user_id );

    /**
     * Set login cookies for the user
     * If $p_perm_login is true, a long-term cookie is created
     * @param int $p_user_id user id
     * @param bool $p_perm_login indicates whether to generate a long-term cookie
     * @access public
     */
    public function setCookies( $p_user_id, $p_perm_login = false );

    /**
     * Clear login cookies, return true if they were cleared
     * @return bool indicating whether cookies were cleared
     * @access public
     */
    public function clearCookies();

    /**
     * Generate a random and unique string to use as the identifier for the login
     * cookie.
     * @return string Random and unique 384bit cookie string of encoded according to the base64 with URI safe alphabet approach described in RFC4648
     * @access public
     */
    public function generateUniqueCookieString();

    /**
     * Return true if the cookie login identifier is unique, false otherwise
     * @param string $p_cookie_string
     * @return bool indicating whether cookie string is unique
     * @access public
     */
    public function isCookieStringUnique( $p_cookie_string );

    /**
     * Return the current user login cookie string,
     * note that the cookie cached by a script login superceeds the cookie provided by
     *  the browser. This shouldn't normally matter, except that the password verification uses
     *  this routine to bypass the normal authentication, and can get confused when a normal user
     *  logs in, then runs the verify script. the act of fetching config variables may get the wrong
     *  userid.
     * if no user is logged in and anonymous login is enabled, returns cookie for anonymous user
     * otherwise returns '' (an empty string)
     *
     * @param boolean auto-login anonymous user
     * @return string current user login cookie string
     * @access public
     */
    public function getCurrentUserCookie( $p_login_anonymous=true );

    /**
     * Set authentication tokens for secure session.
     * @param integer User ID
     * @access public
     */
    public function setTokens( $p_user_id );

    /**
     * Check for authentication tokens, and display re-authentication page if needed.
     * Currently, if using BASIC or HTTP authentication methods, or if logged in anonymously,
     * this function will always "authenticate" the user (do nothing).
     *
     * @return bool
     * @access public
     */
    public function reauthenticate();

    /**
     * Generate the intermediate authentication page.
     * @param integer User ID
     * @param string Username
     * @return bool
     * @access public
     */
    public function reauthenticatePage( $p_user_id, $p_username );

    /**
     * is cookie valid?
     * @param string $p_cookie_string
     * @return bool
     * @access public
     */
    public function isCookieValid( $p_cookie_string );

    /**
     * Retrieve user id of current user
     * @return int user id
     * @access public
     */
    public function getCurrentUserId();
}
