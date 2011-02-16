<?php
# MantisBT - A PHP based bugtracking system

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
 * Configuration API
 *
 * @package CoreAPI
 * @subpackage ConfigurationAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses helper_api.php
 * @uses utility_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'helper_api.php' );
require_api( 'utility_api.php' );

# ## Configuration API ###
# ------------------
# Retrieves the value of a config option
#  This function will return one of (in order of preference):
#    1. value from cache
#    2. value from database
#     looks for specified config_id + current user + current project.
#     if not found, config_id + current user + all_project
#     if not found, config_id + default user + current project
#     if not found, config_id + default user + all_project.
#    3.use GLOBAL[config_id]
function config_get( $p_option, $p_default = null, $p_user = null, $p_project = null ) {
	return MantisConfig::get( $p_option, $p_default, $p_user, $p_project );
}

# ----------------------
# force config variable from a global to avoid recursion
function config_get_global( $p_option, $p_default = null ) {
	return MantisConfig::getGlobal( $p_option, $p_default );
}

# ------------------
# Retrieves the access level needed to change a config value
function config_get_access( $p_option, $p_user = null, $p_project = null ) {
	return MantisConfig::getAccess( $p_option, $p_user, $p_project );
}

# ------------------
# Returns true if the specified config option exists (ie. a
#  value or default can be found), false otherwise
function config_is_set( $p_option, $p_user = null, $p_project = null ) {
	return MantisConfig::isConfigSet( $p_option, $p_user, $p_project );
}

# ------------------
# Sets the value of the given config option to the given value
#  If the config option does not exist, an ERROR is triggered
function config_set( $p_option, $p_value, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = DEFAULT_ACCESS_LEVEL ) {
	return MantisConfig::set(  $p_option, $p_value, $p_user, $p_project, $p_access );
}

# ------------------
# Sets the value of the given config option in the global namespace.
#  Does *not* persist the value between sessions. If override set to
#  false, then the value will only be set if not already existent.
function config_set_global( $p_option, $p_value, $p_override = true ) {
	return MantisConfig::setGlobal( $p_option, $p_value, $p_override );
}

# ------------------
# Sets the value of the given config option to the given value
#  If the config option does not exist, an ERROR is triggered
function config_set_cache( $p_option, $p_value, $p_type, $p_user = NO_USER, $p_project = ALL_PROJECTS, $p_access = DEFAULT_ACCESS_LEVEL ) {
	return MantisConfig::setCache( $p_option, $p_value, $p_type, $p_user, $p_project, $p_access );
}

# ------------------
# Checks if the specific configuration option can be set in the database, otherwise it can only be set
# in the configuration file (config_inc.php / config_defaults_inc.php).
function config_can_set_in_database( $p_option ) {
	return MantisConfig::canSetInDatabase( $p_option );
}

# ------------------
# Checks if the specific configuration option can be deleted from the database.
function config_can_delete( $p_option ) {
	return( utf8_strtolower( $p_option ) != 'database_version' );
}

# ------------------
# delete the config entry
function config_delete( $p_option, $p_user = ALL_USERS, $p_project = ALL_PROJECTS ) {
	MantisConfig:configDelete( $p_option, $p_user, $p_project );
}

/**
 * Delete the specified option for the specified user.across all projects.
 * @param $p_option - The configuration option to be deleted.
 * @param $p_user_id - The user id
 */
function config_delete_for_user( $p_option, $p_user_id ) {
	if( !MantisConfig::canDelete( $p_option ) ) {
		return;
	}

	MantisConfig::deleteForUser( $p_option, $p_user_id );
}

# ------------------
# delete the config entry
function config_delete_project( $p_project = ALL_PROJECTS ) {
	MantisConfig::deleteProject( $p_project );
}

# ------------------
# delete the config entry from the cache
# @@@ to be used sparingly
function config_flush_cache( $p_option = '', $p_user = ALL_USERS, $p_project = ALL_PROJECTS ) {
	MantisConfig::flushCache( $p_option, $p_user, $p_project );
}

# ------------------
# Checks if an obsolete configuration variable is still in use.  If so, an error
# will be generated and the script will exit.  This is called from admin_check.php.
function config_obsolete( $p_var, $p_replace ) {
	MantisConfig::obsolete( $p_var, $p_replace );
}

# ------------------
# check for recursion in defining config variables
# If there is a %text% in the returned value, re-evaluate the "text" part and replace
#  the string
function config_eval( $p_value ) {
	return MantisConfig::configEval( $p_value );
}

# list of configuration variable which may expose webserver details and shouldn't be
# exposed to users or webservices
function config_is_private( $p_config_var ) {
	return MantisConfig::isPrivate( $p_config_var );
}
