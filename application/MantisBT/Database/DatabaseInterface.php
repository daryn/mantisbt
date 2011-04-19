<?php
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
interface DatabaseInterface {
    /**
     * Contructor
     */
    public function __construct( $p_table_previx, $p_table_suffix );

    /**
     * Destructor
     */
    public function __destruct();

    /**
     * Detects if all needed PHP stuff installed.
     * Note: can be used before connect()
     * @return mixed true if ok, string if something
     */
    public function driverInstalled();

    /**
     * Returns database driver type
     * Note: can be used before connect()
     * @return string db type mysql, pgsql, sqlsrv
     */
    public function getDbType();

    /**
     * Diagnose database and tables, this function is used
     * to verify database and driver settings, db engine types, etc.
     *
     * @return string null means everything ok, string means problem found.
     */
    public function diagnose();

    /**
     * Connect to db
     * Must be called before other methods.
     * @param string $dbhost
     * @param string $dbuser
     * @param string $dbpass
     * @param string $dbname
     * @param mixed $prefix string means moodle db prefix, false used for external databases where prefix not used
     * @param array $dboptions driver specific options
     * @return bool true
     * @throws dml_connection_exception if error
     */
    public function connect( $p_dbhost, $p_dbuser, $p_dbpass, $p_dbname, array $p_dboptions=null);

    /**
     * Attempt to create the database
     * @param string $dbhost
     * @param string $dbuser
     * @param string $dbpass
     * @param string $dbname
     *
     * @return bool success
     */
    public function createDatabase($p_dbhost, $p_dbuser, $p_dbpass, $p_dbname, array $p_dboptions=null);

    /**
     * Close database connection and release all resources
     * and memory (especially circular memory references).
     * Do NOT use connect() again, create a new instance if needed.
     * @return void
     */
    public function dispose();

    /**
     * Returns database server info array
     * @return array
     */
    public function getServerInfo();

    /**
     * Returns last error reported by database engine.
     * @return string error message
     */
    public function getLastError();

    /**
     * Return tables in database WITHOUT current prefix
     * @return array of table names in lowercase and without prefix
     */
    public function getTables($p_usecache=true);

    /**
     * Return table indexes - everything lowercased
     * @return array of arrays
     */
    public function getIndexes($p_table);

    /**
     * Returns detailed information about columns in table. This information is cached internally.
     * @param string $table name
     * @param bool $usecache
     * @return array of database_column_info objects indexed with column names
     */
    public function getColumns($p_table, $p_usecache=true);

    /**
     * Reset internal column details cache
     * @param string $table - empty means all, or one if name of table given
     * @return void
     */
    public function resetCaches();

    public function getInsertId( $p_table, $p_field );

    /**
     * Attempt to change db encoding toUTF-8 if possible
     * @return bool success
     */
    public function changeDbEncoding();

    /**
     * Enable/disable debugging mode
     * @param bool $p_state
     * @return void
     */
    public function setDebug( $p_state );

    /**
     * Returns debug status
     * @return bool $state
     */
    public function getDebug();

    /**
     * Execute general sql query. Should be used only when no other method suitable.
     * Do NOT use this to make changes in db structure, use database_manager::execute_sql() instead!
     * @param string $p_sql query
     * @param array $p_params query parameters
     * @return bool true
     * @throws MantisBT\Exception\Database if error
     */
    public function execute($p_sql, array $p_params=null);

    /**
     * @param string $p_sql query
	 * @param int $p_limit Number of results to return
	 * @param int $p_offset offset query results for paging
     * @param array $p_params query parameters
     * @return bool true
     * @throws MantisBT\Exception\Database if error
     */
    public function selectLimit( $p_sql, $p_limit, $p_offset, array $p_arr_parms = null );
	
    /**
     * Returns number of queries done by this database
     * @return int
     */
    public function perfGetQueries();
}
