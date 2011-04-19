<?php
namespace MantisBT\Auth;

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
interface ResultInterface {
    /**
     * isValid
     * @return bool
     * @access public
     */
    public function isValid();
    public function getCode();
    public function getIdentity();
    public function getMessages();

}
