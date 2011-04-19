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

class AdapterAbstract {
    protected $config = null;

    protected $user = null;

    protected $request = null;
    /**
     * @access private $currentUserId
     */
    protected $currentUserId = null;

    /**
     * @access private string $scriptLoginCookie
     */
    protected $scriptLoginCookie = null;

    /**
     * @global bool $automaticAnonymousLogin
     */
    protected $automaticAnonymousLogin = null;

    /**
     * @global array $anonymousUserCookieString
     */
    protected $anonymousUserCookieString = null;

    /**
     * @access protected array $cookieIsValid
     */
    protected $cookieIsValid = null;

    public function __construct( $p_config, $p_user, $p_request, $p_login_anonymous ) {
        $this->config = $p_config;
        $this->user = $p_user;
        $this->request = $p_request;
        $this->automaticAnonymousLogin = $p_login_anonymous;
    }

    /**
     * Check that there is a user logged-in and authenticated
     * If the user's account is disabled they will be logged out
     * If there is no user logged in, redirect to the login page
     * If parameter is given it is used as a URL to redirect to following
     * successful login.  If none is given, the URL of the current page is used
     * @param string $p_return_page Page to redirect to following successful logon, defaults to current page
     * @access public
     * @todo has print dependency
     * @todo has string dependency
     * @todo has utility dependency
     */
    public function ensureUserAuthenticated( $p_return_page = '' ) {
        # if logged in
        if( $this->isUserAuthenticated() ) {
            # check for access enabled
            #  This also makes sure the cookie is valid
            if( OFF == current_user_get_field( 'enabled' ) ) {
                print_header_redirect( 'logout_page.php' );
            }
        } else {
            # not logged in
            if( is_blank( $p_return_page ) ) {
                if( !isset( $_SERVER['REQUEST_URI'] ) ) {
                    $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
                }
                $p_return_page = $_SERVER['REQUEST_URI'];
            }
            $p_return_page = string_url( $p_return_page );
            print_header_redirect( 'login_page.php?return=' . $p_return_page );
        }
    }

    /**
     * Return true if there is a currently logged in and authenticated user, false otherwise
     *
     * @param boolean auto-login anonymous user
     * @return bool
     * @access public
     */
    public function isUserAuthenticated() {
        if( is_null( $this->cookieIsValid ) ) {
            $this->cookieIsValid = $this->isCookieValid( $this->getCurrentUserCookie( $this->automaticAnonymousLogin ) );
        }
        return $this->cookieIsValid;
    }

    /**
     * prepare/override the username provided from logon form (if necessary)
     * @todo when we rewrite authentication api for plugins, this should be merged with prepare_password and return some object
     * @param string $p_username
     * @return string prepared username
     * @access public
     */
    public function $this->prepareUsername( $p_username ) {
        return $p_username;
    }

    /**
     * prepare/override the password provided from logon form (if necessary)
     * @todo when we rewrite authentication api for plugins, this should be merged with prepare_username and return some object
     * @param string $p_password
     * @return string prepared password
     * @access public
     */
    public function preparePassword( $p_password ) {
        return $p_password;
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
     * @todo has ldap dependency
     */
    public function authenticate( $p_username, $p_password, $p_perm_login = false ) {
        ## @todo for BASIC and LDAP the user may have been auto created, ensure the data is correct
        $t_user = $this->userDb->getByUsername( $p_username );

        if ( false === $this->user ) {
            return false;
        }

## @todo HTTP_AUTH, LDAP AUTH, and BASIC_AUTH call should come back to here.
        # check for disabled account
        if( !$this->user->isEnabled() ) {
            return false;
        }

        # max. failed login attempts achieved...
        if( !$this->user->isLoginRequestAllowed( $this->config->max_failed_login_count ) ) {
            return false;
        }

        # check for anonymous login
        if( OFF == $this->config->allow_anonymous_login || !$this->user->isAnonymous() ) {
            # anonymous login didn't work, so check the password

            if( !$this->doesPasswordMatch( $this->user->id, $p_password ) ) {
                return false;
            }
        }

        # set the cookies
        $this->storage->write( $this->user->id, $p_perm_login );
        $this->setTokens( $this->user->id );

        return true;
    }

    public function autoCreate( $p_username, $p_password ) {
        # attempt to create the user
        $t_row['username'] = $p_username;
        $t_row['password'] = md5( $p_password );
        $t_cookie_string = $this->userDb->create( $this, $this->config, $t_row );

        if ( false === $t_cookie_string ) {
            # it didn't work
            return false;
        }

        # ok, we created the user, get the row again
        $t_user = $this->userDb->getByUsername( $p_username );

        if( false === $t_user ) {
            # uh oh, something must be really wrong
            # @@@ trigger an error here?
            return false;
        }
        return $t_user->id;
    }

    /**
     * Allows scripts to login using a login name or ( login name + password )
     * @param string $p_username username
     * @param string $p_password username
     * @return bool indicates if authentication was successful
     * @access public
     */
    public function attemptScriptLogin() {
        if( false === $this->user ) {
            return false;
        }

        # check for disabled account
        if( !$this->user->isEnabled() ) {
            return false;
        }

        # validate password if supplied
        $t_password = $this->request->get( 'password' );
        if( null !== $t_password ) {
            if( !$this->doesPasswordMatch( $this->user->id, $t_password ) ) {
                return false;
            }
        }

        # ok, we're good to login now
        # With cases like RSS feeds and MantisConnect there is a login per operation, hence, there is no
        # real significance of incrementing login count.
        # increment login count
        # user_increment_login_count( $t_user_id );
        # set the cookies
        $this->scriptLoginCookie = $this->user->cookie_string;

        # cache user id for future reference
        $this->currentUserId = $this->user->id;

        return true;
    }

    /**
     * Logout the current user and remove any remaining cookies from their browser
     * Returns true on success, false otherwise
     * @access public
     * @todo has user dependency
     * @todo has helper dependency
     * @todo has session dependency
     */
    public function logout() {
        # clear cached userid
        $this->userDb->cache->clear( $this->currentUserId );
        $this->currentUserId = null;
        $this->cookieIsValid = null;

        # clear cookies, if they were set
        if( $this->clearCookies() ) {
            helper_clear_pref_cookies();
        }

        if( $this instanceof Http ) {
            $this->httpSetLogoutPending( true );
        }

        session_clean();
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
     * @todo has user dependency
     * @todo has utf8 dependency
     */
    public function doesPasswordMatch( $p_user_id, $p_test_password ) {
        $t_configured_login_method = $this->config->login_method;
        $t_user = $this->userDb->getById( $p_user_id );
        $t_password = $t_user_id->password;

        $t_login_methods = array(
            MD5,
            CRYPT,
            PLAIN,
        );
        foreach( $t_login_methods as $t_login_method ) {
            # pass the stored password in as the salt
            if( $this->processPlainPassword( $p_test_password, $t_password, $t_login_method ) == $t_password ) {

                # Do not support migration to PLAIN, since this would be a crazy thing to do.
                # Also if we do, then a user will be able to login by providing the MD5 value
                # that is copied from the database.  See #8467 for more details.
                if( $t_configured_login_method != PLAIN && $t_login_method == PLAIN ) {
                    continue;
                }

                # Check for migration to another login method and test whether the password was encrypted
                # with our previously insecure implemention of the CRYPT method
                if(( $t_login_method != $t_configured_login_method ) || (( CRYPT == $t_configured_login_method ) && utf8_substr( $t_password, 0, 2 ) == utf8_substr( $p_test_password, 0, 2 ) ) ) {
                    $this->userDb->setPassword( $p_user_id, $p_test_password, true );
                }
                return true;
            }
        }
        return false;
    }

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
     * @todo has utf8 dependency
     * @todo review whether this should be split to other classes
     */
     public function processPlainPassword( $p_password, $p_salt = null, $p_method = null ) {
        $t_login_method = $this->config->login_method;
        if( $p_method !== null ) {
            $t_login_method = $p_method;
        }

        switch( $t_login_method ) {
            case CRYPT:
                # a null salt is the same as no salt, which causes a salt to be generated
                # otherwise, use the salt given
                $t_processed_password = crypt( $p_password, $p_salt );
                break;
            case BASIC_AUTH:
            case PLAIN:
            default:
                $t_processed_password = $p_password;
                break;
        }

## MD5 enters here
        # cut this off to PASSLEN cahracters which the largest possible string in the database
        return utf8_substr( $t_processed_password, 0, PASSLEN );
    }

    /**
     * Generate a random 16 character password.
     * @todo Review use of $p_email within mantis
     * @param string $p_email unused
     * @return string 16 character random password
     * @access public
     * @todo has crypto dependency
     */
    public function generateRandomPassword( $p_email ) {
        # !TODO: create memorable passwords?
        return crypto_generate_uri_safe_nonce( 16 );
    }

    /**
     * Generate a confirmation code to validate password reset requests.
     * @param string $p_password Password string to generate a confirmation code for
     * @param string $p_last_visit Last visit date used to create hash 
     * @return string Confirmation code (384bit) encoded according to the base64 with URI safe alphabet approach described in RFC4648
     * @access public
     */
    public function generateConfirmHash( $p_password, $p_last_visit ) {
        $t_confirm_hash_raw = hash( 'whirlpool', 'confirm_hash' . $this->config->getGlobal( 'crypto_master_salt' ) . $p_password . $p_last_visit, true );
        # Note: We truncate the last 8 bits from the hash output so that base64
        # encoding can be performed without any trailing padding.
        $t_confirm_hash_base64_encoded = base64_encode( substr( $t_confirm_hash_raw, 0, 63 ) );
        $t_confirm_hash = strtr( $t_confirm_hash_base64_encoded, '+/', '-_' );

        return $t_confirm_hash;
    }

    /**
     * Clear login cookies, return true if they were cleared
     * @return bool indicating whether cookies were cleared
     * @access public
     */
    public function clearCookies() {
        $t_cookies_cleared = false;
        $this->cookieIsValid = null;

        # clear cookie, if not logged in from script
        if( $this->scriptLoginCookie == null ) {
            $t_cookie_name = $this->config->string_cookie;
            $t_cookie_path = $this->config->cookie_path;

            $this->request->clearCookie( $t_cookie_name, $t_cookie_path );
            $t_cookies_cleared = true;
        } else {
            $this->scriptLoginCookie = null;
        }
        return $t_cookies_cleared;
    }

    /**
     * Generate a random and unique string to use as the identifier for the login
     * cookie.
     * @return string Random and unique 384bit cookie string of encoded according to the base64 with URI safe alphabet approach described in RFC4648
     * @access public
     * @todo has crypto dependency
     */
    public function generateUniqueCookieString() {
        do {
            $t_cookie_string = crypto_generate_uri_safe_nonce( 64 );
        }
        while( !$this->userDb->isCookieStringUnique( $t_cookie_string ) );

        return $t_cookie_string;
    }

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
    public function getCurrentUserCookie( $p_login_anonymous=true ) {
        # if logging in via a script, return that cookie
        if( $this->scriptLoginCookie !== null ) {
            return $this->scriptLoginCookie;
        }

        # fetch user cookie
        $t_cookie_name = $this->config->string_cookie;
        $t_cookie = trim( $this->request->getCookie( $t_cookie_name, '' ) );

        # if cookie not found, and anonymous login enabled, use cookie of anonymous account.
        if( $t_cookie == "" ) {
            if( $p_login_anonymous && ON == $this->config->allow_anonymous_login ) {
                if( $this->anonymousUserCookieString === null ) {
                    # get anonymous information
                    $t_user = $this->userDb->getByUsername( $this->config->anonymous_account );
                    if( $t_user instanceof User\Model ) {
                        $this->anonymousUserCookieString = $t_user->cookie_string;
                        $this->currentUserId = $t_user->id;
                    }
                } else {
                    $t_cookie = $this->anonymousUserCookieString;
                }
            }
        }

        return $t_cookie;
    }

    /**
     * Set authentication tokens for secure session.
     * @param integer User ID
     * @access public
     * @todo has token dependency
     */
    public function setTokens( $p_user_id ) {
        $t_auth_token = token_get( TOKEN_AUTHENTICATED, $p_user_id );
        if( null == $t_auth_token ) {
            token_set( TOKEN_AUTHENTICATED, true, $this->config->getGlobal( 'reauthentication_expiry' ), $p_user_id );
        } else {
            token_touch( $t_auth_token['id'], $this->config->getGlobal( 'reauthentication_expiry' ) );
        }
    }

    /**
     * Check for authentication tokens, and display re-authentication page if needed.
     * Currently, if using BASIC or HTTP authentication methods, or if logged in anonymously,
     * this function will always "authenticate" the user (do nothing).
     *
     * @return bool
     * @access public
     * @todo has token dependency
     */
    public function reauthenticate() {
        if( $this->config->getGlobal( 'reauthentication' ) == OFF ) {
            return true;
        }

        $t_auth_token = token_get( TOKEN_AUTHENTICATED );
        if( null != $t_auth_token ) {
            token_touch( $t_auth_token['id'], $this->config->getGlobal( 'reauthentication_expiry' ) );
            return true;
        } else {
            $t_anon_account = $this->config->anonymous_account;
            $t_anon_allowed = $this->config->allow_anonymous_login;

            $t_user = $this->userDb->getByUserId( $this->getCurrentUserId() );

            # check for anonymous login
            if( ON == $t_anon_allowed && $t_anon_account == $t_user->username ) {
                return true;
            }

            return $this->reauthenticatePage( $t_user->id, $t_user->username );
        }
    }

    /**
     * Generate the intermediate authentication page.
     * @param integer User ID
     * @param string Username
     * @return bool
     * @access public
     * @todo remove to controller
     */
    public function reauthenticatePage( $p_user_id, $p_username ) {
        $t_error = false;

        if( true == $this->request->getBool( '_authenticate' ) ) {
            $f_password = $this->request->getString( 'password', '' );

            if( $this->attemptLogin( $p_username, $f_password ) ) {
                $this->setTokens( $p_user_id );
                return true;
            } else {
                $t_error = true;
            }
        }

        html_page_top();

        ?>
    <div class="important-msg">
    <?php
        echo lang_get( 'reauthenticate_message' );
        if( $t_error != false ) {
            echo '<br /><span class="error-msg">', lang_get( 'login_error' ), '</span>';
        }
    ?>
    </div>
    <div id="reauth-div" class="form-container">
        <form id="reauth-form" method="post" action="">
            <fieldset>
                <legend><span><?php echo lang_get( 'reauthenticate_title' ); ?></span></legend>

            <?php
                # CSRF protection not required here - user needs to enter password
                # (confirmation step) before the form is accepted.
                print_hidden_inputs( $_POST );
                print_hidden_inputs( $_GET );
            ?>

                <input type="hidden" name="_authenticate" value="1" />
                <div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
                    <label for="username"><span><?php echo lang_get( 'username' );?></span></label>
                    <span class="input"><input id="username" type="text" disabled="disabled" size="32" maxlength="<?php echo USERLEN;?>" value="<?php echo string_attribute( $p_username );?>" /></span>
                    <span class="label-style"></span>
                </div>
                <div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
                    <label for="password"><span><?php echo lang_get( 'password' );?></span></label>
                    <span class="input"><input id="password" type="password" name="password" size="16" maxlength="<?php echo PASSLEN;?>" class="autofocus" /></span>
                    <span class="label-style"></span>
                </div>
                <span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'login_button' );?>" /></span>
            </fieldset>
        </form>
    </div>

    <?php
        html_page_bottom();
        exit;
    }

    /**
     * is cookie valid?
     * @param string $p_cookie_string
     * @return bool
     * @access public
     */
    public function isCookieValid( $p_cookie_string ) {
        # fail if cookie is blank
        if( '' === $p_cookie_string ) {
            return false;
        }

        # succeeed if user has already been authenticated
        if( null !== $this->currentUserId ) {
            return true;
        }

        $t_user = $this->userDb->getByCookie( $p_cookie_string );
        if( $t_user instanceof User\Model ) {
            # return true if a matching cookie was found
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieve user id of current user
     * @return int user id
     * @access public
     * @todo has access dependency
     */
    public function getCurrentUserId() {
    	if( null !== $this->currentUserId ) {
    		return $this->currentUserId;
    	}

    	$t_cookie_string = $this->getCurrentUserCookie();

        $t_user = $this->userDb->getByCookie( $t_cookie_string );
        if( $t_user instanceof User\Model ) {
    	    $this->currentUserId = $t_user->id;
    	    return $t_user->id;
        } else {
    		$this->clearCookies();
    		access_denied();
    		exit();
        }
    }
}
