<?php
namespace MantisBT\User;

# MantisBT - a php based bugtracking system

# @todo add new license text

/**
 *	User\Model class
 *  This simply holds a users data.  This is an immutable object.
 *  Use other classes to modify user information and load a new user data object
 * @package MantisBT
 * @subpackage classes
 */
class Model {
    protected $anonymous                   = null;
	protected $username						= null;
	protected $realname						= '';
	protected $email						= null;
	protected $password						= null;
	protected $date_created					= null;
	protected $last_visit					= null;
	protected $enabled						= true;
	protected $protected					= false;
	protected $access_level					= null;
	protected $login_count					= 0;
	protected $lost_password_request_count 	= 0;
	protected $failed_login_count			= 0;
	protected $cookie_string 				= '';

    public function __construct( $p_row, $p_anonymous_account ) {
       foreach( $p_row AS $p_field=>$p_value ) {
            if( property_exists( $this, $p_field ) {
                $this->$p_field = $p_value;
            }
        }
        $this->anonymous = ( $this->username == $p_anonymous_account );
    }

    /**
     *	@param string $p_field_name
     *	@access public
     */
    public function __get( $p_field_name ) {
		return $this->$p_field_name;
	}

    public function isEnabled() {
        return ( ON == $this->enabled );
    }

    public function isLoginRequestAllowed( $p_max_failed_count ) {
        return( $this->failed_login_count < $t_max_failed_login_count || OFF == $t_max_failed_login_count );

    }

    public function isAnonymous() {
        return $this->anonymous;
    }

    public function isProtected() {
        if( $this->anonymous || ON == $this->protected ) {
            return true;
        }
        return false;
    }

    /**
     * Return true if it is, false otherwise
     */
    public function isUsernameValid( $p_username_regex ) {
        # The DB field is hard-coded. USERLEN should not be modified.
        if( utf8_strlen( $this->username ) > USERLEN ) {
            return false;
        }

        # username must consist of at least one character
        if( trim( $this->username ) == "" ) {
            return false;
        }

        # Only allow a basic set of characters
        if( 0 == preg_match( $p_username_regex, $this->username ) ) {
            return false;
        }

        # We have a valid username
        return true;
    }

}
