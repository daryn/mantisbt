<?php
use MantisBT\Exception as ERR;
namespace MantisBT\Database;

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
abstract class PDOAbstract extends DatabaseAbstract {
    protected $pdb;
    protected $lastError = null;

    public function __construct( $p_table_prefix, $p_table_suffix ) {
        parent::__construct( $p_table_prefix, $p_table_suffix );
    }

    public function connect( $p_dbhost, $p_dbuser, $p_dbpass, $p_dbname, array $p_dboptions=null) {
        $t_driverstatus = $this->driverInstalled();

        if ($t_driverstatus !== true) {
#			error_parameters( 0, 'PHP Support for database is not enabled' );
#			trigger_error( ERROR_DB_CONNECT_FAILED, ERROR );

            #throw new ERR\Database('DatabaseDriverProblem', $t_driverstatus );
            throw new ERR\Database( 'DatabaseDriverProblem', ERROR_DB_CONNECT_FAILED );
        }

		$this->dbhost = $p_dbhost;
		$this->dbuser = $p_dbuser;
		$this->dbpass = $p_dbpass;
		$this->dbname = $p_dbname;

        try{
            $this->pdb = new \PDO($this->getDsn(), $this->dbuser, $this->dbpass, $this->getPdoOptions());
            // generic PDO settings to match adodb's default; subclasses can change this in configure_dbconnection
            $this->pdb->setAttribute( \PDO::ATTR_CASE, \PDO::CASE_LOWER);
            $this->pdb->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->configureDbConnection();
            return true;
        } catch ( \PDOException $ex ) {
            throw new ERR\Database( $ex->getMessage(), ERROR_DB_QUERY_FAILED );
            return false;
        }
    }

    /**
     * Returns the driver-dependent DSN for PDO based on members stored by connect.
     * Must be called after connect (or after $dbname, $dbhost, etc. members have been set).
     * @return string driver-dependent DSN
     */
    abstract protected function getDsn();

    /**
     * Returns the driver-dependent connection attributes for PDO based on members stored by connect.
     * Must be called after $dbname, $dbhost, etc. members have been set.
     * @return array A key=>value array of PDO driver-specific connection options
     */
    protected function getPdoOptions() {
        return array( \PDO::ATTR_PERSISTENT => !empty( $this->dboptions['dbpersist'] ) );
    }

    protected function configureDbConnection() {        
    }

    /**
     * Returns general database library name
     * Note: can be used before connect()
     * @return string db type pdo, native
     */
    protected function getDbLibrary() {
        return 'pdo';
    }

    /**
     * Returns localised database type name
     * Note: can be used before connect()
     * @return string
     */
    public function getName() {
        return get_string('pdo'.$this->get_dbtype(), 'install');
    }

    /**
     * Returns database server info array
     * @return array
     */
    public function getServerInfo() {
        $t_result = array();
        try {
            $t_result['information'] = $this->pdb->getAttribute( \PDO::ATTR_SERVER_INFO );
        } catch( \PDOException $ex ) {}
        try {
            $t_result['version'] = $this->pdb->getAttribute( \PDO::ATTR_SERVER_VERSION );
        } catch( \PDOException $ex ) {}
        return $t_result;
    }

	public function getInsertId( $p_table, $p_field ) {
		if ($t_id = $this->pdb->lastInsertId()) {
			return (int)$t_id;
		}
	}
	
    /**
     * Returns last error reported by database engine.
     * @return string error message
     */
    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Function to print/save/ignore debugging messages related to SQL queries.
     */
    protected function debugQuery($p_sql, $p_params = null) {
        echo '<hr /> (', $this->getDbType(), '): ',  htmlentities($p_sql);
        if($p_params) {
            echo ' (parameters ';
            print_r($p_params);
            echo ')';
        }
        echo '<hr />';
    }

    /**
     * Execute general sql query. Should be used only when no other method suitable.
     * Do NOT use this to make changes in db structure, use database_manager::execute_sql() instead!
     * @param string $p_sql query
     * @param array $p_params query parameters
     * @return bool success
     */
    public function execute($p_sql, array $p_params=null) {
        list($t_sql, $t_params) = $this->fixSqlParams($p_sql, $p_params);

        $t_result = true;
        $this->queryStart($t_sql, $t_params);

        try {
            $sth = $this->pdb->prepare($t_sql);
            $sth->execute($t_params);
        } catch ( \PDOException $ex ) {
            $this->lastError = $ex->getMessage();
            $t_result = false;
        }

        $this->queryEnd($t_result);
        return $t_result == true ? $sth : false;
    }

    protected function queryStart($p_sql, array $p_params=null) {
        $this->lastError = null;
        parent::queryStart($p_sql, $p_params);
    }
}
