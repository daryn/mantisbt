<?php
namespace MantisBT;

# MantisBT - a php based bugtracking system

# @todo needs new license text

/**
 * Class to hold the request configuration and objects with request scope lifetime
 * @package MantisBT
 * @subpackage classes
 */
class ApplicationScope {
    /**
     * application environment (production, development, testing, offline ) 
     */
    protected $auth = null;
    protected $config = null;
    protected $environment = null;
    protected $session = null;
    protected $translate = null;
    protected $userDb = null;

    protected $userCache = null;

    protected $requestTime = null;

    /**
     * Construct an ApplicationScope object
     * @param array $p_args
     */
    public function __construct( $p_environment, Config\DB $p_config_db, Session $p_session, Auth $p_auth, Translate $p_translate, User\Cache $p_user_cache ) {
        $this->requestTime = microtime( true );
        $this->environment = $p_environment;

        $this->config = $p_config_db;
        $this->session = $p_session;
        $this->auth = $p_auth;
        $this->translate = $p_translate;
        $this->userCache = $p_user_cache;
    }

    public function getEnvironment() {
        return $this->environment;
    }

    /**
     * @return $_auth
     */
    public function getAuth() {
        return $this->auth;
    }

    /**
     * @return $_config
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * @return $_lang
     */
    public function getTranslator() {
        return $this->translate;
    }

    /**
     * @return $_db
     */
    public function getDb() {
        if( !$this->db ) {
        }
        return $this->userDb;
    }

    /**
     * @return $_userDb
     */
    public function getUserDb() {
        return $this->userDb;
    }

    /**
     * @return $_userCache
     */
    public function getUserCache() {
        return $this->userCache;
    }
}
