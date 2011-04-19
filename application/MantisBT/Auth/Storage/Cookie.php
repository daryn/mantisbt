<?php
namespace MantisBT\Auth\Storage;

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
class Cookie implements StorageInterface {
    protected $path = null;
    protected $name = null;
    protected $anonymousAccount = null;
    protected $allowAnonymousLogin = null;

    public function __construct( Config $p_config ) {
        $this->path = $p_config->cookie_path;
        $this->name = $p_config->string_cookie;
        $this->anonymousAccount = $p_config->anonymous_account;
        $this->allowAnonymousLogin = $p_config->allow_anonymous_login;
    }
    
    /**
     * isEmpty
     * @return bool
     * @access public
     */
    public function isEmpty() {
        return ( $this->read() == "" );
    }

    public function read() {
        # fetch user cookie
        $t_cookie = trim( $this->request->getCookie( $this->name, '' ) );

        # if cookie not found, and anonymous login enabled, use cookie of anonymous account.
        if( $t_cookie == "" ) {
            if( $p_login_anonymous && ON == $this->allowAnonymousLogin ) {
                if( $this->anonymousUserCookieString === null ) {
                    # get anonymous information
                    $t_user = $this->userDb->getByUsername( $this->anonymousAccount );
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

    public function write( $p_contents ) {
        $t_user = $this->userDb->getById( $p_user_id );

        if( $p_perm_login ) {
            # set permanent cookie (1 year)
            $this->request->setCookie( $this->name, $t_user->cookie_string, true );
        } else {
            # set temp cookie, cookie dies after browser closes
            $this->request->setCookie( $this->name, $t_user->cookie_string, false );
        }
    }

    public function clear() {
        $t_cookies_cleared = false;
        $this->cookieIsValid = null;

        # clear cookie, if not logged in from script
        if( $this->scriptLoginCookie == null ) {
            $this->request->clearCookie( $this->name, $this->path );
            $t_cookies_cleared = true;
        } else {
            $this->scriptLoginCookie = null;
        }
        return $t_cookies_cleared;
    }
}
