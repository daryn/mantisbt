<?php
namespace MantisBT\Auth;

# MantisBT - A PHP based bugtracking system

# @todo needs new license header
# @todo this was basically copied from Zend_Auth_Result so probably should be refactored or assigned accordingly.

/**
 * Authentication API
 *
 * @package CoreAPI
 * @subpackage AuthenticationAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

class Result implements ResultInterface {
    const FAILURE = 0;
    const SUCCESS = 1;
    const FAILURE_IDENTITY_NOT_FOUND = -1;

    protected $code;
    protected $identity;
    protected $messages;

    public function __construct( $p_code, $p_identity, $p_messages ) {
        $this->code = $p_code;
        $this->identity = $p_identity;
        $this->messages = $p_messages;
    }

    public function isValid() {
        return ($this->code > 0) ? true : false;
    }

    public function getCode() {
        return $this->code;
    }

    public function getIdentity( ) {
        return $this->identity;
    }

    public function getMessages() {
        return $this->messages;
    }
}
