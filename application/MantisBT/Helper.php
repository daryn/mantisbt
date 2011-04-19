<?php
namespace MantisBT;

# MantisBT - a php based bugtracking system

# @todo add new license text

/**
 * Top level application helper class
 * @package MantisBT
 * @subpackage classes
 */
class Helper {
    private $application = null;

    /**
     * Construct a MainHelper object
     */
    public function __construct( ApplicationScope $p_application ) {
        $this->application = $p_application ;
    }

    public function bootstrap() {
        /**
         * Before doing anything... check if MantisBT is down for maintenance
         *
         *   To make MantisBT 'offline' simply create a file called
         *   'mantis_offline.php' in the MantisBT root directory.
         *   Users are redirected to that file if it exists.
         *   If you have to test MantisBT while it's offline, add the
         *   parameter 'mbadmin=1' to the URL.
         */
        if ( file_exists( 'mantis_offline.php' ) && !isset( $_GET['mbadmin'] ) ) {
        	include( 'mantis_offline.php' );
        	exit;
        }
    }

    /**
     * Application logic here
     * @return null 
     */
    public function run() {
        $t_auth = $this->application->getAuthenticator();
        $t_front = MBT\Injector:injectController( $this->application );

#        require_api( 'print_api.php' );
#        require_api( 'authentication_api.php' );

#        if ( auth_is_user_authenticated() ) {
#	        print_header_redirect( config_get( 'default_home_page' ) );
#        } else {
#	        print_header_redirect( 'login_page.php' );
#        }
    }
}
