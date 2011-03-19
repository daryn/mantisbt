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
 * Factory class for building database objects.
 * @package MantisBT
 * @subpackage classes
 */
class DatabaseFactory {
    /**
     * Loads and returns a database instance with the specified type and library.
     * @param string $type database type of the driver (e.g. pdo_pgsql)
     * @return MantisBT\Database driver object or null if error
     */
    public function create( $p_type ) {
		$t_type = explode( '_', $p_type );
		switch( strtolower( $t_type[0] ) ) {
			case 'pdo':
				$t_driver_type = 'PDO';
		}
        $classname = __NAMESPACE__ . '\\' . $t_driver_type . ucfirst($t_type[1]);
        $t_db = new $classname();
        if( $t_db instanceof DatabaseInterface ) {
            # require the db class to implement the correct interface
            return $t_db;
        } else {
            throw new ERR\Database( 'Unsupported Database Type' );
        }
    }
}
