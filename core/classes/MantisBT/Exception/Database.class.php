<?php
namespace MantisBT\Exception;
use Exception;

/**
 * @todo Remove globals
 */
class Database extends ExceptionAbstract {
    public function __construct( $p_message = '', $p_code = 0, \Exception $p_previous = null ) {
		/**
         * If we have some form of database exception, assume that the database don't want to treat
		 * the database as connected in the exception handler anymore
		 */
		global $g_db_connected;
		$g_db_connected = false;

		parent::__construct( $p_message, $p_code, $p_previous );
	}
}
