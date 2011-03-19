<?php
namespace MantisBT\Exception;

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
 * Factory class for building exception objects.
 * @package MantisBT
 * @subpackage classes
 */
class ExceptionFactory {
    /**
     * Loads and returns a database instance with the specified type and library.
     * @param string $type exception type of the driver (e.g. database )
     * @return MantisBT\Exception driver object or null if error
     */
    public function create( $p_type ) {
        switch( $p_type ) {
            case 'database':

            break;
        }
        return new $classname();
    }
}
