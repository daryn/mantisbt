<?php
namespace MantisBT\Session;

# MantisBT - A PHP based bugtracking system

# @todo add new license text

/**
 * interface for a MantisBT session handler.
 * @package MantisBT
 * @subpackage classes
 */
interface SessionInterface {
	/**
	 * Constructor
	 */
	public function __construct();

    public function validate();

	/**
	 * get session data
	 * @param string $p_name
	 * @param mixed $p_default
	 */
	public function get( $p_name, $p_default = null );

	/**
	 * set session data
	 * @param string $p_name
	 * @param mixed $p_value
	 */
	public function set( $p_name, $p_value );

	/**
	 * delete session data
	 * @param string $p_name
	 */
	public function delete( $p_name );

	/**
	 * destroy session
	 */
	public function destroy();

	public function clean();

	public function validate();
}
