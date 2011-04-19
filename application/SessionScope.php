<?php
namespace MantisBT;

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
 * Class to hold the application configuration and objects with an application scope lifetime
 * @package MantisBT
 * @subpackage classes
 */
class SessionScope {
    /**
     * Array of application scope variables
     */
    private $args;

    /**
     * Construct an SessionScope object
     * @param array $p_args
     */
    public function __construct( $p_args ) {
        $this->args = $p_args;
    }

    function set( $p_key, $p_value ) {
        $_SESSION[$p_key] = $p_value;
    }

    /**
     * @return array $args
     */
    public function getArgs() {
        return $this->args;
    }
}
