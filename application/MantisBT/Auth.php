<?php
namespace MantisBT\Auth\Storage;

# MantisBT - A PHP based bugtracking system

# @todo add new license text
# @todo this was basically copied from Zend_Auth so probably needs the license attribution there.

/**
 * Authentication API
 *
 * @package CoreAPI
 * @subpackage AuthenticationAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

class Auth {
    /**
     * storage handler
     */
    protected $storage = null;

    /**
     *  Removed the Zend singleton pattern.
     */
    protected function __construct( StorageInterface $p_storage ) {
        $this->storage = $p_storage;
    }

    /**
     * Authenticates against the supplied adapter
     *
     * @param  Auth\Adapter\AdapterInterface $p_adapter
     * @return Auth\Result
     */
    public function authenticate( Auth\Adapter\AdapterInterface $p_adapter) {
        $t_result = $p_adapter->authenticate();

        /**
         * Ensure storage has clean state
         */
        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($t_result->isValid()) {
            $this->storage->write( $t_result->getIdentity());
        }

        return $t_result;
    }

    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return boolean
     */
    public function hasIdentity() {
        return !$this->storage->isEmpty();
    }

    /**
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    public function getIdentity() {
        if ( $this->storage->isEmpty() ) {
            return null;
        }

        return $this->storage->read();
    }

    /**
     * Clears the identity from persistent storage
     *
     * @return void
     */
    public function clearIdentity() {
        $this->storage->clear();
    }
}
