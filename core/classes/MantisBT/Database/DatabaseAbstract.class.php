<?php
namespace MantisBT\Database;
use MantisBT\Exception as ERR;

# MantisBT - a php based bugtracking system

# Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Abstract database driver class.
 * @package MantisBT
 * @subpackage classes
 */
abstract class DatabaseAbstract implements DatabaseInterface {
    /**
	 * array - cache of column info 
	 */
    protected $columns = array(); 
    /**
	 * array - cache of table info 
	 */
    protected $tables  = null;

    /** 
	 * string - db host name 
	 */
    protected $dbhost;
    /** 
	 * string - db host user 
	 */
    protected $dbuser;
    /** 
	 * string - db host password 
	 */
    protected $dbpass;
    /** 
	 * string - db name 
	 */
    protected $dbname;
    /** 
	 * string - db dsn
	 */
    protected $dbdsn;
	
    /** @var array Database or driver specific options, such as sockets or TCPIP db connections */
    protected $dboptions;

    /** @var int Database query counter (performance counter).*/
    protected $queries = 0;

    /** @var bool Debug level */
    protected $debug  = false;

    /**
     * Contructor
     */
    public function __construct() {
    }

    /**
     * Destructor
     */
    public function __destruct() {
        $this->dispose();
    }

    /**
     * Diagnose database and tables, this function is used
     * to verify database and driver settings, db engine types, etc.
     *
     * @return string null means everything ok, string means problem found.
     */
    public function diagnose() {
        return null;
    }

    /**
     * Attempt to create the database
     * @param string $dbhost
     * @param string $dbuser
     * @param string $dbpass
     * @param string $dbname
     *
     * @return bool success
     */
    public function createDatabase( $p_dbhost, $p_dbuser, $p_dbpass, $p_dbname, array $p_dboptions=null ) {
        return false;
    }

    /**
     * Close database connection and release all resources
     * and memory (especially circular memory references).
     * Do NOT use connect() again, create a new instance if needed.
     * @return void
     */
    public function dispose() {
        $this->columns = array();
        $this->tables  = null;
    }

    /**
     * Called before each db query.
     * @param string $sql
     * @param array array of parameters
     * @param int $type type of query
     * @param mixed $extrainfo driver specific extra information
     * @return void
     */
    protected function queryStart($p_sql, array $p_params=null ) {
        $this->last_sql       = $p_sql;
        $this->last_params    = $p_params;
        $this->last_time      = microtime(true);

		$this->queries++;
    }

    /**
     * Called immediately after each db query.
     * @param mixed db specific result
     * @return void
     */
    protected function queryEnd($p_result) {
        if ($p_result !== false) {
            return;
        }
    }

    /**
     * Reset internal column details cache
     * @param string $table - empty means all, or one if name of table given
     * @return void
     */
    public function resetCaches() {
        $this->columns = array();
        $this->tables  = null;
    }

    /**
     * Attempt to change db encoding toUTF-8 if possible
     * @return bool success
     */
    public function changeDbEncoding() {
        return false;
    }

    /**
     * Enable/disable debugging mode
     * @param bool $state
     * @return void
     */
    public function setDebug( $p_state ) {
        $this->debug = $p_state;
    }

    /**
     * Returns debug status
     * @return bool $state
     */
    public function getDebug() {
        return $this->debug;
    }

    /**
     * Returns number of queries done by this database
     * @return int
     */
    public function perfGetQueries() {
        return $this->queries;
    }

	/**
     * Normalizes sql query parameters and verifies parameters.
     * @param string $p_sql query or part of it
     * @param array $p_params query parameters
     * @return array (p_sql, p_params, type of p_params)
     */
    protected function fixSqlParams( $p_sql, array $p_params=null) {
        $p_params = (array)$p_params; # mke null array if needed

        # cast booleans to 1/0 int
        foreach ( $p_params as $t_key => $t_value ) {
            $p_params[$t_key] = is_bool($t_value) ? (int)$t_value : $t_value;
        }

        $t_count = substr_count($p_sql, '?');

        if (!$t_count) {
			return array($p_sql, array() );
        }

		if ($t_count == count($p_params)) {
			return array($p_sql, array_values($p_params)); # 0-based array required
		}

		$a = new stdClass;
		$a->expected = $t_count;
		$a->actual = count($params);
		$a->sql = $p_sql;
		$a->params = $params;
		throw new ERR\Database( ERROR_DB_QUERY_FAILED, $a );
    }
}
