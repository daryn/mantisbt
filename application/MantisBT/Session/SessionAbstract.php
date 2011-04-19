<?php
namespace MantisBT\Session;

# MantisBT - A PHP based bugtracking system

# @todo add new license text

/**
 * Abstract session class
 *
 * Handles user/browser sessions in an extendable manner. New session handlers
 * can be added and configured without affecting how the API is used. Calls to
 * session_*() are appropriately directed at the session handler class as
 * chosen in config_inc.php.
 *
 * @package CoreAPI
 * @subpackage SessionAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Abstract interface for a MantisBT session handler.
 * @package MantisBT
 * @subpackage classes
 */
abstract class SessionAbstract {
	protected $id;

	/**
	 * Constructor
	 */
	abstract function __construct();

	/**
	 * get session data
	 * @param string $p_name
	 * @param mixed $p_default
	 */
	abstract function get( $p_name, $p_default = null );

	/**
	 * set session data
	 * @param string $p_name
	 * @param mixed $p_value
	 */
	abstract function set( $p_name, $p_value );

	/**
	 * delete session data
	 * @param string $p_name
	 */
	abstract function delete( $p_name );

	/**
	 * destroy session
	 */
	abstract function destroy();

    /**
     * Validate the legitimacy of a session.
     * Checks may include last-known IP address, or more.
     * Triggers an error when the session is invalid.
     */
    public function validate() {
        $t_user_ip = '';
        if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $t_user_ip = trim( $_SERVER['REMOTE_ADDR'] );
	    }

        if ( is_null( $t_last_ip = $this->get( 'last_ip', null ) ) ) {
		    # First session usage
		    $this->set( 'last_ip', $t_user_ip );
	    } else {
		    # Check a continued session request
		    if ( $t_user_ip != $t_last_ip ) {
			    $this->clean();

			    trigger_error( ERROR_SESSION_NOT_VALID, WARNING );

			    $t_url = config_get_global( 'path' ) . config_get_global( 'default_home_page' );
			    echo "\t<meta http-equiv=\"Refresh\" content=\"4;URL=$t_url\" />\n";

			    die();
		    }
	    }
    }
}
